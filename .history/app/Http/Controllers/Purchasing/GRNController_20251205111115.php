<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemActivity;
use App\Models\Inventory\ItemSku;
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

            // 4️⃣ Delete old details if updating
            if ($request->id) {
                $oldDetails = GRNDetail::where('gen_id', $request->id)->get();

                // Get main discount from GRNHeader using code
                $grnHeader = GRNHeader::where('id', $request->id)->first();
                $mainDiscountPra = $grnHeader->discount_pra ?? 0;

                foreach ($oldDetails as $old) {
                    $item = Item::find($old->item_id);
                    if ($item) {
                        $pivot = $item->warehouses()->where('ware_house_id', $request->warehouse_id)->first();
                        if ($pivot) {
                            $existingQty = $pivot->pivot->qty;
                            $existingAvgCost = $pivot->pivot->avg_cost;

                            // 🧩 Get SKU conversion factor
                            $sku = null;
                            if ($old->sku_id) {
                                $sku = ItemSku::find($old->sku_id);
                            }
                            $conversion = ($sku && $sku->qty_per_sku > 0) ? floatval($sku->qty_per_sku) : 1;

                            // 🧮 Convert old qty to base unit
                            $oldBaseQty = $old->qty * $conversion;

                            // 🔹 Apply line discount
                            $lineDiscountedUnitCost = $old->unit_cost;
                            $lineDiscountedUnitCost = max(0, $old->unit_cost - $old->discount);

                            // 🔹 Apply main discount from GRNHeader
                            $unitCostAfterMainDiscount = $lineDiscountedUnitCost * (1 - $mainDiscountPra / 100);

                            // Old cost in base unit
                            $oldBaseCost = $unitCostAfterMainDiscount / $conversion;

                            // Rollback: subtract old GRN qty and recalc avg cost
                            //$rolledBackQty = max(0, $existingQty - $oldBaseQty);
                            $rolledBackQty = $existingQty - $oldBaseQty;

                            // Weighted rollback for avg_cost
                            $rolledBackValue = ($existingQty * $existingAvgCost) - ($oldBaseQty * $oldBaseCost);
                            $rolledBackAvgCost = $rolledBackQty > 0 ? $rolledBackValue / $rolledBackQty : 0;

                            $item->warehouses()->updateExistingPivot($request->warehouse_id, [
                                'qty' => $rolledBackQty,
                                'avg_cost' => $rolledBackAvgCost,
                            ]);
                        }
                    }
                }

                // Remove old GRN detail records
                GRNDetail::where('gen_id', $grn->id)->delete();
            }



            // 5️⃣ Insert GRN details
            $items = $request->items;
            foreach ($items as $item) {
                GRNDetail::create([
                    'gen_id' => $grn->id,
                    'item_id' => $item['id'],
                    'sku_id' => $item['selectedSku'] ?? null,
                    'qoh' => $item['stock'] ?? 0,
                    'qty' => $item['qty'] ?? 0,
                    'discount_pra' => $item['discount_type'] === '%' ? $item['discount_value'] : 0,
                    'discount' => $item['discount_type'] === 'Fixed' ? $item['discount_value'] : 0,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'price_level1' => $item['price_level1'] ?? 0,
                    'price_level2' => $item['price_level2'] ?? 0,
                    'price_level3' => $item['price_level3'] ?? 0,
                    'free_qty' => $item['free_qty'] ?? 0,
                    'total_cost' => $item['line_total'] ?? 0,
                    'expire_date' => $item['expire_date'] == '' ? null : $item['expire_date'],
                ]);


                $item_data = Item::find($item['id']);
                if ($item_data) {
                    // 🧩 Determine SKU conversion
                    $conversion = 1;
                    if (!empty($item['selectedSku'])) {
                        $sku = ItemSku::find($item['selectedSku']);
                        if ($sku && $sku->qty_per_sku > 0) {
                            $conversion = floatval($sku->qty_per_sku);
                        }
                    }

                    // 🧮 Convert to base units
                    $newBaseQty = ($item['qty'] ?? 0) * $conversion;

                    // 🏷️ Calculate discounted unit cost
                    $unitCost = floatval($item['unit_cost'] ?? 0);
                    $itemDiscountType = $item['discount_type'] ?? null;
                    $itemDiscountValue = floatval($item['discount_value'] ?? 0);

                    // 1️⃣ Apply item-level discount
                    if ($itemDiscountType === '%') {
                        $unitCost -= ($unitCost * ($itemDiscountValue / 100));
                    } elseif ($itemDiscountType === 'Fixed') {
                        $unitCost -= $itemDiscountValue;
                    }

                    // 2️⃣ Apply main GRN discount (% always)
                    $mainDiscount = floatval($request->discount ?? 0);
                    if ($mainDiscount > 0) {
                        $unitCost -= ($unitCost * ($mainDiscount / 100));
                    }

                    // 3️⃣ Convert to base unit cost
                    $baseUnitCost = $conversion > 0 ? $unitCost / $conversion : $unitCost;

                    // 🔄 Update warehouse stock
                    $pivot = $item_data->warehouses()->where('ware_house_id', $request->warehouse_id)->first();
                    $final_cost=0;
                    $final_qty=0;
                    if ($pivot) {
                        $existingQty = $pivot->pivot->qty;
                        $existingAvgCost = $pivot->pivot->avg_cost;

                        // Weighted average
                        $totalQty = $existingQty + $newBaseQty;
                        if($existingQty<0){
                            $existingQty=0;
                        }
                        $avgCost = $totalQty > 0
                            ? (($existingQty * $existingAvgCost) + ($newBaseQty * $baseUnitCost)) / $totalQty
                            : $baseUnitCost;

                        $item_data->warehouses()->updateExistingPivot($request->warehouse_id, [
                            'qty' => $totalQty,
                            'avg_cost' => $avgCost,
                        ]);
                        $final_cost = $avgCost;
                         $final_qty = $totalQty;
                    } else {
                        // 🆕 New warehouse record
                        $item_data->warehouses()->attach($request->warehouse_id, [
                            'qty' => $newBaseQty,
                            'avg_cost' => $baseUnitCost,
                        ]);
                         $final_cost = $baseUnitCost;
                         $final_qty = $newBaseQty;
                    }


                    ItemActivity::updateOrCreate(
                        [
                            'grn_no' => $grn->code,
                            'item_id' => $item['id'],
                            'ware_house_id' => $request->warehouse_id,
                        ],
                        [
                            'doc_type' => 1, // 1 = GRN
                            'reference_no' => $grn->code,
                            'date' => $grn->date,
                            'trans_type' => 'grn',
                            'doc_reference' => 1,
                            'qty' => $final_qty,
                            'unit_cost' => $final_cost,
                            'retail_price' => $item['price_level1'] ?? 0,
                            'qoh' => $item['stock'],
                            'price_level1' => $item['price_level1'] ?? 0,
                            'price_level2' => $item['price_level2'] ?? 0,
                            'price_level3' => $item['price_level3'] ?? 0,
                            'is_void' => $request->is_void ?? 0,
                        ]
                    );

                    // 🏷️ Update item cost (base unit cost)
                    $item_data->unit_cost = $baseUnitCost;
                    $item_data->save();
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
                'error' => 'Failed to create/update GRN: ' . $e->getMessage()
            ], 500);
        }
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
