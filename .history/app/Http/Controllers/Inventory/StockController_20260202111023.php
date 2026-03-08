<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemActivity;
use App\Models\Inventory\ItemBatch;
use App\Models\Inventory\BOMHeader;
use App\Models\Inventory\BOMDetail;
use App\Http\Controllers\Main\DefaultController;
class StockController extends Controller
{
    public function updateStockAndActivity(
        $reference_no,
        $trans_type,
        $warehouseId,
        $itemId,
        $qty,
        $doc_reference,
        $unitCost = null,
        $isNegativeAllowed = true
    ) {
        $item = Item::findOrFail($itemId);


        $warehouse = $item->warehouses()
            ->where('ware_house_id', $warehouseId)
            ->first();

        $existing_qty = $warehouse ? $warehouse->pivot->qty : 0;

        // Calculate new quantity
        if ($doc_reference) {
            $new_qty = $existing_qty + $qty;

        } else {
            $new_qty = $existing_qty - $qty;

        }

        // Check if negative stock is allowed
        if (!$isNegativeAllowed && $new_qty < 0) {
            throw new \Exception("Insufficient stock for item: {$item->description}");
        }
        // Handle stock update based on quantity
        if ($qty > 0) {

            // Positive quantity - adding stock

            // Check for negative batches
            $negativeBatches = ItemBatch::where('item_id', $itemId)
                ->where('ware_house_id', $warehouseId)
                ->where('qty', '<', 0)
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            $remainingQty = $qty;
            $hasNegative = $negativeBatches->isNotEmpty();

            // 1️⃣ First, consume negative batches if they exist
            if ($hasNegative) {
                foreach ($negativeBatches as $negativeBatch) {
                    if ($remainingQty <= 0)
                        break;

                    $negativeQty = abs($negativeBatch->qty);

                    if ($remainingQty >= $negativeQty) {
                        // Fully consume negative batch
                        $remainingQty -= $negativeQty;
                        $negativeBatch->qty = 0;
                        $negativeBatch->is_consumed = true;
                        $negativeBatch->notes = 'Negative stock consumed by ' . $trans_type . ' ' . $reference_no;
                    } else {
                        // Partially consume negative batch
                        $negativeBatch->qty += $remainingQty; // Add positive to negative
                        $remainingQty = 0;
                        $negativeBatch->notes = 'Partially consumed by ' . $trans_type . ' ' . $reference_no;
                    }
                    $negativeBatch->save();
                }
            }

            // 2️⃣ Handle remaining positive quantity
            if ($remainingQty > 0) {
                // Option 1: Add to last batch with SAME unit cost
                $lastBatch = ItemBatch::where('item_id', $itemId)
                    ->where('ware_house_id', $warehouseId)
                    ->orderBy('received_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastBatch) {
                    // Update existing batch (same cost)
                    $lastBatch->qty += $remainingQty;
                    $lastBatch->notes = $lastBatch->notes
                        ? $lastBatch->notes . '; Additional ' . $remainingQty . ' from ' . $reference_no
                        : 'Additional ' . $remainingQty . ' from ' . $reference_no;
                    $lastBatch->save();


                }
            }

        } else {
            // Negative quantity - consuming stock
            $consumedQty = abs($qty);
            $this->consumeFifoStock($itemId, $warehouseId, $consumedQty, $reference_no, $trans_type);
        }

        // Calculate average cost from remaining batches
        $avgCost = $this->calculateFifoAvgCost($itemId, $warehouseId);

        // Update warehouse stock
        $item->warehouses()->syncWithoutDetaching([
            $warehouseId => [
                'qty' => $new_qty,
                'avg_cost' => $avgCost
            ]
        ]);

        // Create activity record
        ItemActivity::create([
            'doc_type' => 1,
            'reference_no' => $reference_no,
            'grn_no' => '',
            'item_id' => $item->id,
            'ware_house_id' => $warehouseId,
            'date' => now(),
            'trans_type' => $trans_type,
            'doc_reference' => $doc_reference,
            'qty' => $qty,
            'unit_cost' => $avgCost,
            'retail_price' => $item->price_level1,
            'price_level1' => $item->price_level1,
            'price_level2' => $item->price_level2,
            'price_level3' => $item->price_level3,
            'qoh' => $new_qty,
            'is_void' => 0,
        ]);

        return $avgCost;
    }

    private function createBatch($itemId, $warehouseId, $qty, $unitCost, $referenceNo, $referenceType)
    {
        // Check if there's negative stock to consume first
        $item = Item::find($itemId);
        $warehouse = $item->warehouses()
            ->where('ware_house_id', $warehouseId)
            ->first();

        $existingQty = $warehouse ? $warehouse->pivot->qty : 0;
        $df = new DefaultController();
        if ($existingQty < 0) {
            // There's negative stock, consume from this new batch
            $negativeQty = abs($existingQty);

            if ($qty >= $negativeQty) {
                // Entire negative stock can be consumed
                $remainingQty = $qty - $negativeQty;

                if ($remainingQty > 0) {
                    // Create batch with remaining quantity
                    ItemBatch::create([
                        'item_id' => $itemId,
                        'ware_house_id' => $warehouseId,
                        'batch_no' => $df->generateCode('bat'),
                        'qty' => $remainingQty,
                        'unit_cost' => $unitCost,
                        'received_date' => now(),
                        'reference_no' => $referenceNo,
                        'reference_type' => $referenceType,
                        'is_consumed' => false
                    ]);
                    $df->UpdateCode('bat');
                }
                // No batch created for consumed negative stock
            } else {
                // Only part of negative stock is consumed
                $remainingNegative = $negativeQty - $qty;
                // No batch created as all new stock consumed negative
            }
        } else {
            // No negative stock, create full batch
            ItemBatch::create([
                'item_id' => $itemId,
                'ware_house_id' => $warehouseId,
                'batch_no' => $df->generateCode('bat'),
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'received_date' => now(),
                'reference_no' => $referenceNo,
                'reference_type' => $referenceType,
                'is_consumed' => false
            ]);
            $df->UpdateCode('bat');
        }
    }

    private function consumeFifoStock($itemId, $warehouseId, $quantity, $referenceNo, $referenceType)
    {
        $remainingQty = $quantity;
        $totalCost = 0;

        // FIFO batches
        $batches = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('qty', '>', 0)
            ->orderBy('received_date')
            ->orderBy('id')
            ->get();

        $lastBatch = null;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0)
                break;
            if ($batch->qty >= $remainingQty) {
                $batch->qty -= $remainingQty;
                $totalCost += $remainingQty * $batch->unit_cost;
                $remainingQty = 0;
            } else {
                $totalCost += $batch->qty * $batch->unit_cost;
                $remainingQty -= $batch->qty;
                $batch->qty = 0;
            }
            $batch->save();
        }

        // 🔴 Negative stock handling
        if ($remainingQty > 0) {
            // Reduce last batch into negative
            $lastBatch = ItemBatch::where('item_id', $itemId)
                ->where('ware_house_id', $warehouseId)
                ->orderBy('received_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBatch) {
                // Reduce last batch into negative
                $lastBatch->qty -= $remainingQty;
                $totalCost += $remainingQty * $lastBatch->unit_cost;
                $lastBatch->save();
            }
        }

        return $totalCost;
    }


    private function calculateFifoAvgCost($itemId, $warehouseId)
    {
        $totalValue = 0;
        $totalQty = 0;

        $batches = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('qty', '>', 0)
            ->get();

        foreach ($batches as $batch) {
            $totalValue += $batch->qty * $batch->unit_cost;
            $totalQty += $batch->qty;
        }

        return $totalQty > 0 ? $totalValue / $totalQty : 0;
    }

    private function generateBatchNumber($itemId, $warehouseId)
    {
        $timestamp = now()->format('YmdHis');
        $count = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->count();

        return "BATCH-{$itemId}-{$warehouseId}-" . ($count + 1) . "-{$timestamp}";
    }

    public function consumeBOM($finishedItemId, $warehouseId, $produceQty)
    {
        if ($produceQty <= 0) {
            throw new \Exception("Produce quantity must be greater than 0.");
        }

        try {
            $bomHeader = BOMHeader::where('item_id', $finishedItemId)
                ->where('ware_house_id', $warehouseId)
                ->where('active', 1)
                ->first();

            if (!$bomHeader) {
                return 0;
            }

            $details = BOMDetail::where('bom_id', $bomHeader->id)
                ->with('alternatives.item', 'item')
                ->get();

            if ($details->isEmpty()) {
                throw new \Exception("No BOM details found for this item.");
            }

            $totalCost = 0;
            $consumedItems = [];

            foreach ($details as $detail) {
                $requiredQty = $detail->base_qty * $produceQty;

                // Track if main stock is already used
                $mainStockConsumed = false;

                // 1️⃣ Check main stock (FIFO consumption)
                $mainItemCost = $this->consumeFifoStockForBOM(
                    $detail->item_id,
                    $warehouseId,
                    $requiredQty,
                    $bomHeader->code,
                    'BOM-CONSUME'
                );

                if ($mainItemCost !== false) {
                    // Enough main stock available
                    $totalCost += $mainItemCost['total_cost'];
                    $consumedItems[] = [
                        'item_id' => $detail->item_id,
                        'qty' => $requiredQty,
                        'cost' => $mainItemCost['unit_cost'],
                        'type' => 'main'
                    ];
                    $mainStockConsumed = true;
                    continue;
                }

                // 2️⃣ Not enough main stock → check alternatives
                $foundAlt = false;

                foreach ($detail->alternatives as $alt) {
                    $altItemCost = $this->consumeFifoStockForBOM(
                        $alt->item_id,
                        $warehouseId,
                        $requiredQty,
                        $bomHeader->code,
                        'BOM-CONSUME-ALT'
                    );

                    if ($altItemCost !== false) {
                        // Enough alternative stock available
                        $totalCost += $altItemCost['total_cost'];
                        $consumedItems[] = [
                            'item_id' => $alt->item_id,
                            'qty' => $requiredQty,
                            'cost' => $altItemCost['unit_cost'],
                            'type' => 'alternative'
                        ];
                        $foundAlt = true;
                        break;
                    }
                }

                // 3️⃣ If no alternative used → use main stock (even if negative)
                if (!$foundAlt && !$mainStockConsumed) {
                    // Force consume from main stock (may go negative)
                    try {
                        $this->updateStockAndActivity(
                            reference_no: $bomHeader->code,
                            trans_type: 'BOM-CONSUME-FORCE',
                            warehouseId: $warehouseId,
                            itemId: $detail->item_id,
                            qty: -1 * $requiredQty,
                            doc_reference: $bomHeader->code,
                            unitCost: null,
                            isNegativeAllowed: true
                        );

                        // Calculate cost for forced consumption
                        $item = Item::find($detail->item_id);
                        $warehouse = $item->warehouses()
                            ->where('ware_house_id', $warehouseId)
                            ->first();

                        $avgCost = $warehouse ? $warehouse->pivot->avg_cost : 0;
                        $totalCost += $requiredQty * $avgCost;

                        $consumedItems[] = [
                            'item_id' => $detail->item_id,
                            'qty' => $requiredQty,
                            'cost' => $avgCost,
                            'type' => 'forced'
                        ];
                    } catch (\Exception $e) {
                        throw new \Exception("Insufficient stock for item {$detail->item->description} and no alternatives available.");
                    }
                }
            }


            $unitCost = $totalCost / $produceQty;

            return $unitCost;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function consumeFifoStockForBOM($itemId, $warehouseId, $requiredQty, $referenceNo, $transType)
    {
        $remainingQty = $requiredQty;
        $totalCost = 0;
        $batchesConsumed = [];

        // Get batches in FIFO order
        $batches = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('qty', '>', 0)
            ->orderBy('received_date')
            ->orderBy('id')
            ->orderBy('expiry_date')
            ->get();

        if ($batches->isEmpty()) {
            return false; // No stock available
        }

        // Check if total available stock is sufficient
        $totalAvailable = $batches->sum('qty');
        if ($totalAvailable < $requiredQty) {
            return false; // Insufficient stock
        }

        // Consume from batches
        foreach ($batches as $batch) {
            if ($remainingQty <= 0)
                break;

            if ($batch->qty >= $remainingQty) {
                // Consume from this batch
                $consumedFromBatch = $remainingQty;
                $batch->qty -= $consumedFromBatch;
                $totalCost += $consumedFromBatch * $batch->unit_cost;
                $batchesConsumed[] = [
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'qty' => $consumedFromBatch,
                    'unit_cost' => $batch->unit_cost,
                    'cost' => $consumedFromBatch * $batch->unit_cost
                ];

                // Mark batch as consumed if qty becomes 0
                if ($batch->qty == 0) {
                    $batch->is_consumed = true;
                }
                $batch->save();

                $remainingQty = 0;
            } else {
                // Consume entire batch
                $consumedFromBatch = $batch->qty;
                $totalCost += $consumedFromBatch * $batch->unit_cost;
                $remainingQty -= $consumedFromBatch;
                $batchesConsumed[] = [
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'qty' => $consumedFromBatch,
                    'unit_cost' => $batch->unit_cost,
                    'cost' => $consumedFromBatch * $batch->unit_cost
                ];

                $batch->qty = 0;
                $batch->is_consumed = true;
                $batch->save();
            }

            \Log::info("Batch consumption in BOM", [
                'batch_id' => $batch->id,
                'batch_qty_before' => $batch->qty + $batchesConsumed[count($batchesConsumed) - 1]['qty'],
                'consumed_qty' => $batchesConsumed[count($batchesConsumed) - 1]['qty'],
                'batch_qty_after' => $batch->qty,
                'remaining_to_consume' => $remainingQty
            ]);
        }

        // Verify all consumption was successful
        if ($remainingQty > 0) {
            \Log::error("BOM consumption incomplete", [
                'item_id' => $itemId,
                'required_qty' => $requiredQty,
                'consumed_qty' => $requiredQty - $remainingQty,
                'remaining_qty' => $remainingQty
            ]);
            throw new \Exception("Failed to consume all required quantity. Required: $requiredQty, Consumed: " . ($requiredQty - $remainingQty));
        }

        // Update stock activity
        $item = Item::find($itemId);
        $warehouse = $item->warehouses()
            ->where('ware_house_id', $warehouseId)
            ->first();

        $existing_qty = $warehouse ? $warehouse->pivot->qty : 0;
        $new_qty = $existing_qty - $requiredQty;

        // Calculate average cost from remaining batches
        $avgCost = $this->calculateFifoAvgCost($itemId, $warehouseId);

        // Update warehouse stock
        $item->warehouses()->syncWithoutDetaching([
            $warehouseId => [
                'qty' => $new_qty,
                'avg_cost' => $avgCost
            ]
        ]);

        // Create activity record
        ItemActivity::create([
            'doc_type' => 1,
            'reference_no' => $referenceNo,
            'grn_no' => '',
            'item_id' => $itemId,
            'ware_house_id' => $warehouseId,
            'date' => now(),
            'trans_type' => $transType,
            'doc_reference' => $referenceNo,
            'qty' => -1 * $requiredQty,
            'unit_cost' => $totalCost / $requiredQty,
            'retail_price' => $item->price_level1,
            'price_level1' => $item->price_level1,
            'price_level2' => $item->price_level2,
            'price_level3' => $item->price_level3,
            'qoh' => $new_qty,
            'is_void' => 0,
        ]);

        return [
            'total_cost' => $totalCost,
            'unit_cost' => $totalCost / $requiredQty,
            'batches_consumed' => $batchesConsumed,
            'consumed_qty' => $requiredQty,
            'new_warehouse_qty' => $new_qty
        ];
    }

    // Helper method to get batch information
    public function getItemBatches($itemId, $warehouseId)
    {
        return ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('qty', '>', 0)
            ->orderBy('received_date')
            ->orderBy('expiry_date')
            ->get();
    }

    // Method to adjust batch quantities (for corrections)
    public function adjustBatch($batchId, $newQty, $newUnitCost = null)
    {
        DB::beginTransaction();
        try {
            $batch = ItemBatch::findOrFail($batchId);
            $itemId = $batch->item_id;
            $warehouseId = $batch->ware_house_id;

            // Get item and warehouse info
            $item = Item::find($itemId);
            $warehouse = $item->warehouses()
                ->where('ware_house_id', $warehouseId)
                ->first();

            $oldQty = $batch->qty;
            $quantityDiff = $newQty - $oldQty;

            // Update batch
            $batch->qty = $newQty;
            if ($newUnitCost !== null) {
                $batch->unit_cost = $newUnitCost;
            }

            $batch->save();

            // Update warehouse stock
            $existing_qty = $warehouse->pivot->qty;
            $new_qty = $existing_qty + $quantityDiff;

            $avgCost = $this->calculateFifoAvgCost($itemId, $warehouseId);
            $item->warehouses()->syncWithoutDetaching([
                $warehouseId => [
                    'qty' => $new_qty,
                    'avg_cost' => $avgCost
                ]
            ]);

            DB::commit();

            return [
                'success' => true,
                'batch' => $batch,
                'quantity_diff' => $quantityDiff,
                'new_warehouse_qty' => $new_qty
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }





    private function AddWH($itemId, $warehouseId, $qty, $unitCost, $referenceNo, $referenceType, $batch_no)
    {
        // Check if there's negative stock to consume first
        $item = Item::find($itemId);
        $warehouse = $item->warehouses()
            ->where('ware_house_id', $warehouseId)
            ->first();

        $existingQty = $warehouse ? $warehouse->pivot->qty : 0;

        $batch = ItemBatch::where('item_id', $itemId)
            ->where('ware_house_id', $warehouseId)
            ->where('batch_no', $batch_no)
            ->first();

        if ($existingQty < 0) {
            // There's negative stock, consume from this new batch
            $negativeQty = abs($existingQty);

            if ($qty >= $negativeQty) {
                // Entire negative stock can be consumed
                $remainingQty = $qty - $negativeQty;

                if ($remainingQty > 0) {
                    // Create batch with remaining quantity

                    if ($batch) {
                        // ✅ Batch exists → update qty
                        $batch->update([
                            'qty' => $batch->qty + $qty,
                        ]);
                    } else {
                        // ✅ Batch does not exist → create
                        ItemBatch::create([
                            'item_id' => $itemId,
                            'ware_house_id' => $warehouseId,
                            'batch_no' => $batch_no,
                            'qty' => $qty,
                            'unit_cost' => $unitCost,
                            'received_date' => now(),
                            'reference_no' => $referenceNo,
                            'reference_type' => $referenceType,
                            'is_consumed' => false
                        ]);
                    }
                }
            }
        } else {

            if ($batch) {
                // ✅ Batch exists → update qty
                $batch->update([
                    'qty' => $batch->qty + $qty,
                ]);
            } else {
                // ✅ Batch does not exist → create
                ItemBatch::create([
                    'item_id' => $itemId,
                    'ware_house_id' => $warehouseId,
                    'batch_no' => $batch_no,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'received_date' => now(),
                    'reference_no' => $referenceNo,
                    'reference_type' => $referenceType,
                    'is_consumed' => false
                ]);
            }

        }
    }



    public function TransferBatch(
        $reference_no,
        $trans_type,
        $warehouseId,
        $TowarehouseId,
        $itemId,
        $qty,
        $doc_reference,
        $unitCost = null,
        $isNegativeAllowed = true
    ) {

        try {

            $remainingQty = $qty;

            // FIFO batches
            $batches = ItemBatch::where('item_id', $itemId)
                ->where('ware_house_id', $warehouseId)
                ->where('qty', '>', 0)
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();
            $totalCost = 0;
            foreach ($batches as $batch) {
                if ($remainingQty <= 0)
                    break;
                if ($batch->qty >= $remainingQty) {
                    $batch->qty -= $remainingQty;
                    $totalCost += $remainingQty * $batch->unit_cost;
                    $this->AddWH($itemId, $TowarehouseId, $remainingQty, $batch->unit_cost, $batch->reference_no, $batch->reference_type, $batch->batch_no);

                    $remainingQty = 0;
                } else {
                    $totalCost += $batch->qty * $batch->unit_cost;
                    $remainingQty -= $batch->qty;
                    $this->AddWH($itemId, $TowarehouseId, $batch->qty, $batch->unit_cost, $batch->reference_no, $batch->reference_type, $batch->batch_no);
                    $batch->qty = 0;
                }
                $batch->save();
            }

            $item = Item::findOrFail($itemId);

            $avgCost = $this->calculateFifoAvgCost($itemId, $warehouseId);
            $warehouse = $item->warehouses()
                ->where('ware_house_id', $warehouseId)
                ->first();
            $existing_qty = $warehouse->pivot->qty;
            $new_qty = $existing_qty - $qty;
            $item->warehouses()->syncWithoutDetaching([
                $warehouseId => [
                    'qty' => $new_qty,
                    'avg_cost' => $avgCost
                ]
            ]);
            // Create activity record
            ItemActivity::create([
                'doc_type' => 1,
                'reference_no' => $reference_no,
                'grn_no' => '',
                'item_id' => $item->id,
                'ware_house_id' => $warehouseId,
                'date' => now(),
                'trans_type' => $trans_type,
                'doc_reference' => false,
                'qty' => $qty,
                'unit_cost' => $avgCost,
                'retail_price' => $item->price_level1,
                'price_level1' => $item->price_level1,
                'price_level2' => $item->price_level2,
                'price_level3' => $item->price_level3,
                'qoh' => $new_qty,
                'is_void' => 0,
            ]);



            $avgCost = $this->calculateFifoAvgCost($itemId, $TowarehouseId);
            $warehouse = $item->warehouses()
                ->where('ware_house_id', $TowarehouseId)
                ->first();
            $existing_qty = $warehouse->pivot->qty;
            $new_qty = $existing_qty + $qty;
            $item->warehouses()->syncWithoutDetaching([
                $TowarehouseId => [
                    'qty' => $new_qty,
                    'avg_cost' => $avgCost
                ]
            ]);
            // Create activity record
            ItemActivity::create([
                'doc_type' => 1,
                'reference_no' => $reference_no,
                'grn_no' => '',
                'item_id' => $item->id,
                'ware_house_id' => $TowarehouseId,
                'date' => now(),
                'trans_type' => $trans_type,
                'doc_reference' => true,
                'qty' => $qty,
                'unit_cost' => $avgCost,
                'retail_price' => $item->price_level1,
                'price_level1' => $item->price_level1,
                'price_level2' => $item->price_level2,
                'price_level3' => $item->price_level3,
                'qoh' => $new_qty,
                'is_void' => 0,
            ]);
            return [
                'success' => true,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }





    }





    public function restoreBOM($finishedItemId, $warehouseId, $producedQty, $referenceNo)
    {
        $bomHeader = BOMHeader::where('item_id', $finishedItemId)
            ->where('ware_house_id', $warehouseId)
            ->where('active', 1)
            ->first();

        if (!$bomHeader) {
            return;
        }

        $details = BOMDetail::where('bom_id', $bomHeader->id)->get();

        foreach ($details as $detail) {

            $restoreQty = $detail->base_qty * $producedQty;

            // Restore component stock
            $this->updateStockAndActivity(
                reference_no: $referenceNo,
                trans_type: 'BOM-VOID',
                warehouseId: $warehouseId,
                itemId: $detail->item_id,
                qty: $restoreQty, // ADD BACK
                doc_reference: $referenceNo,
                unitCost: null,
                isNegativeAllowed: true
            );
        }
    }


}