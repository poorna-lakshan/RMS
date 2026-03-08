<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemActivity;
use App\Models\Inventory\ItemSku;
use App\Models\Inventory\ItemBatch;
use App\Models\Purchasing\GRNDetail;
use App\Models\Purchasing\GRNHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class GRNController extends Controller
{
    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('grn');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);


        $item_list = Item::with(['category', 'itemClass', 'warehouses', 'skus'])->whereIn('class_id', [1, 2])->get();

        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);


        return response()->json([
            'grn_code' => $grn_code,
            'item_list' => $item_list,
            'warehouse_list' => $warehouse_list,
            'vendor_list' => $vendor_list,
        ]);
    }


    public function create(Request $request)
    {
        // 1️⃣ Validate request
        $validator = Validator::make($request->all(), [
            'grn_code' => 'required|string',
            'date' => 'required|date',
            'vendor_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If updating (ID exists)
        if ($request->id) {
            // Get latest GRN ID
            $latestId = GRNHeader::max('id');

            // If trying to edit older GRN
            if ($request->id != $latestId) {
                return response()->json([
                    'error' => 'You can only update the most recent GRN.'
                ], 500);
            }
        }

        DB::beginTransaction();
        try {
            // 2️⃣ Prepare header data
            $headerData = [
                'code' => $request->grn_code,
                'date' => $request->date,
                'vendor_id' => $request->vendor_id,
                'ware_house_id' => $request->warehouse_id,
                'sup_inv' => $request->supplier_invoice_no,
                'total_line_discount' => $request->item_discount_total ?? 0,
                'discount_pra' => $request->discount ?? 0,
                'nbt' => $request->nbt ?? 0,
                'vat' => $request->vat ?? 0,
                'is_confirm' => $request->is_confirm ?? 0,
                'is_void' => $request->is_void ?? 0,
                'discount' => $request->overall_discount_amount ?? 0,
                'gross_total' => $request->subtotal ?? 0,
                'net_total' => $request->total ?? 0,
            ];

            // 3️⃣ Create or update header
            $grn = GRNHeader::updateOrCreate(
                ['id' => $request->id ?? 0],
                $headerData
            );

            // 4️⃣ Delete old details and restore -ve stock if updating
            if ($request->id) {
                $oldDetails = GRNDetail::where('gen_id', $request->id)->get();

                foreach ($oldDetails as $old) {
                    $item = Item::find($old->item_id);
                    if ($item) {
                        // Get SKU conversion factor
                        $sku = null;
                        if ($old->sku_id) {
                            $sku = ItemSku::find($old->sku_id);
                        }
                        $conversion = ($sku && $sku->qty_per_sku > 0) ? floatval($sku->qty_per_sku) : 1;

                        // Convert old qty to base unit
                        $oldBaseQty = $old->qty * $conversion;

                        // 🔄 FIFO: Remove old ItemBatch records for this GRN
                        ItemBatch::where('item_id', $old->item_id)
                            ->where('ware_house_id', $request->warehouse_id)
                            ->where('reference_no', $grn->code)
                            ->where('reference_type', 'GRN')
                            ->delete();

                        // Update warehouse stock (subtract old qty)
                        $pivot = $item->warehouses()->where('ware_house_id', $request->warehouse_id)->first();
                        if ($pivot) {
                            $existingQty = $pivot->pivot->qty;
                            $newQty = $existingQty - $oldBaseQty;

                            $item->warehouses()->updateExistingPivot($request->warehouse_id, [
                                'qty' => $newQty,
                                // For FIFO, avg_cost might not be needed or can be calculated from batches
                                'avg_cost' => $this->calculateFifoAvgCost($old->item_id, $request->warehouse_id),
                            ]);
                        }

                        // Delete old ItemActivity records for this GRN
                        ItemActivity::where('grn_no', $grn->code)
                            ->where('item_id', $old->item_id)
                            ->where('ware_house_id', $request->warehouse_id)
                            ->delete();
                    }
                }

                // Remove old GRN detail records
                GRNDetail::where('gen_id', $grn->id)->delete();
            }

            // 5️⃣ Insert GRN details with FIFO batches
            $items = $request->items;
            foreach ($items as $itemData) {
                // Create GRN detail
                $grnDetail = GRNDetail::create([
                    'gen_id' => $grn->id,
                    'item_id' => $itemData['id'],
                    'sku_id' => $itemData['selectedSku'] ?? null,
                    'qoh' => $itemData['stock'] ?? 0,
                    'qty' => $itemData['qty'] ?? 0,
                    'discount_pra' => $itemData['discount_type'] === '%' ? $itemData['discount_value'] : 0,
                    'discount' => $itemData['discount_type'] === 'Fixed' ? $itemData['discount_value'] : 0,
                    'unit_cost' => $itemData['unit_cost'] ?? 0,
                    'price_level1' => $itemData['price_level1'] ?? 0,
                    'price_level2' => $itemData['price_level2'] ?? 0,
                    'price_level3' => $itemData['price_level3'] ?? 0,
                    'free_qty' => $itemData['free_qty'] ?? 0,
                    'total_cost' => $itemData['line_total'] ?? 0,
                    'expire_date' => $itemData['expire_date'] == '' ? null : $itemData['expire_date'],
                ]);

                $item = Item::find($itemData['id']);
                if ($item) {
                    // Determine SKU conversion
                    $conversion = 1;
                    if (!empty($itemData['selectedSku'])) {
                        $sku = ItemSku::find($itemData['selectedSku']);
                        if ($sku && $sku->qty_per_sku > 0) {
                            $conversion = floatval($sku->qty_per_sku);
                        }
                    }

                    // Convert to base units
                    $newBaseQty = ($itemData['qty'] ?? 0) * $conversion;

                    // Calculate discounted unit cost
                    $unitCost = floatval($itemData['unit_cost'] ?? 0);
                    $itemDiscountType = $itemData['discount_type'] ?? null;
                    $itemDiscountValue = floatval($itemData['discount_value'] ?? 0);

                    // Apply item-level discount
                    if ($itemDiscountType === '%') {
                        $unitCost -= ($unitCost * ($itemDiscountValue / 100));
                    } elseif ($itemDiscountType === 'Fixed') {
                        $unitCost -= $itemDiscountValue;
                    }

                    // Apply main GRN discount (% always)
                    $mainDiscount = floatval($request->discount ?? 0);
                    if ($mainDiscount > 0) {
                        $unitCost -= ($unitCost * ($mainDiscount / 100));
                    }

                    // Convert to base unit cost
                    $baseUnitCost = $conversion > 0 ? $unitCost / $conversion : $unitCost;

                    $df = new DefaultController();
                    // 🔄 FIFO: Create batch records
                    $batchQty = $newBaseQty;
                    $batchData = [
                        'item_id' => $itemData['id'],
                        'ware_house_id' => $request->warehouse_id,
                        'batch_no' => $df->generateCode('bat'),
                        'expiry_date' => !empty($itemData['expire_date']) ? $itemData['expire_date'] : null,
                        'qty' => $batchQty,
                        'unit_cost' => $baseUnitCost,
                        'received_date' => $request->date,
                        'reference_no' => $grn->code,
                        'reference_type' => 'GRN',
                        'notes' => '',
                        'is_consumed' => false
                    ];

                    // If there's negative stock, consume from oldest batches first
                    $pivot = $item->warehouses()->where('ware_house_id', $request->warehouse_id)->first();
                    $existingQty = $pivot ? $pivot->pivot->qty : 0;

                    if ($existingQty < 0) {
                        // There's negative stock to consume first
                        $negativeQty = abs($existingQty);

                        if ($batchQty >= $negativeQty) {
                            // 1. Consume all negative stock
                            $batchQty -= $negativeQty; // 10 - 1 = 9

                            // 2. Create batch with remaining quantity
                            if ($batchQty > 0) {
                                $batchData['qty'] = $batchQty; // 9
                                ItemBatch::create($batchData);
                            }

                            // 3. Update warehouse stock: -1 + 10 = 9
                            $newWarehouseQty = $existingQty + ($batchQty + $negativeQty);
                            // Explanation: -1 + (9 + 1) = -1 + 10 = 9
                        } else {
                            // New stock is less than negative stock (e.g., -5 and GRN 3)
                            // Consume part of negative stock
                            $remainingNegative = $negativeQty - $batchQty; // 5 - 3 = 2 remaining negative

                            // No batch created (all new stock consumed negative)
                            $batchData['qty'] = 0;

                            // Update warehouse stock: -5 + 3 = -2
                            $newWarehouseQty = $existingQty + $batchQty;
                            // -5 + 3 = -2

                          
                        }
                    } else {
                        // No negative stock, create full batch
                        ItemBatch::create($batchData);
                        $newWarehouseQty = $existingQty + $batchQty;
                    }

                    // Update warehouse stock
                    $avgCost = $this->calculateFifoAvgCost($itemData['id'], $request->warehouse_id);

                    if ($pivot) {
                        $item->warehouses()->updateExistingPivot($request->warehouse_id, [
                            'qty' => $newWarehouseQty,
                            'avg_cost' => $avgCost,
                        ]);
                    } else {
                        $item->warehouses()->attach($request->warehouse_id, [
                            'qty' => $newWarehouseQty,
                            'avg_cost' => $avgCost,
                        ]);
                    }

                    // Update ItemActivity
                    ItemActivity::create([
                        'doc_type' => 1, // 1 = GRN
                        'ware_house_id' => $request->warehouse_id,
                        'reference_no' => $grn->code,
                        'date' => $grn->date,
                        'trans_type' => 'grn',
                        'doc_reference' => 1,
                        'item_id' => $itemData['id'],
                        'qty' => $batchQty,
                        'unit_cost' => $baseUnitCost,
                        'retail_price' => $itemData['price_level1'] ?? 0,
                        'qoh' => $newWarehouseQty,
                        'price_level1' => $itemData['price_level1'] ?? 0,
                        'price_level2' => $itemData['price_level2'] ?? 0,
                        'price_level3' => $itemData['price_level3'] ?? 0,
                        'grn_no' => $grn->code,
                        'is_void' => $request->is_void ?? 0,
                    ]);

                    // Update item unit_cost (optional for FIFO)
                    $item->unit_cost = $baseUnitCost;
                    $item->save();

                    if (!$request->id) {
                        $df->UpdateCode('bat');
                    }
                }
            }

            // 6️⃣ Update code if new GRN
            if (!$request->id) {
                $df = new DefaultController();
                $df->UpdateCode('grn');
            }

            DB::commit();

            return response()->json([
                'message' => $request->id ? 'GRN updated successfully' : 'GRN created successfully',
                'grn_id' => $grn->id
            ], $request->id ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create/update GRN: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // Helper function to calculate FIFO average cost
    private function calculateFifoAvgCost($itemId, $warehouseId)
    {
        $totalValue = 0;
        $totalQty = 0;

        $batches = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('qty', '>', 0)
            ->where('is_consumed', false)
            ->get();

        foreach ($batches as $batch) {
            $totalValue += $batch->qty * $batch->unit_cost;
            $totalQty += $batch->qty;
        }

        return $totalQty > 0 ? $totalValue / $totalQty : 0;
    }

    // Helper function to generate batch number
    private function generateBatchNumber($itemId, $warehouseId)
    {
        $timestamp = now()->format('YmdHis');
        $count = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->count();

        return "BATCH-{$itemId}-{$warehouseId}-" . ($count + 1) . "-{$timestamp}";
    }

    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = GRNHeader::with('vendor', 'warehouse')->get();

        return response()->json($itemCategories);
    }

    public function getById($id)
    {
        try {
            $grn = GRNHeader::with([
                'vendor',
                'warehouse',
                'details.item.skus',
                'details.sku'
            ])->findOrFail($id);

            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['error' => 'GRN not found'], 404);
        }
    }




}
