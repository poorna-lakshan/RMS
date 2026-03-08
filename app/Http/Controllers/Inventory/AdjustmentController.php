<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\AdjustmentHeader;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Main\DefaultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemActivity;
use App\Models\Inventory\AdjustmentDetail;
use App\Helpers\LogHelper;
class AdjustmentController extends Controller
{
    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('ajs');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);


        $item_list = Item::with(['category', 'itemClass', 'warehouses', 'skus'])->whereIn('class_id', [1, 2])->get();

        return response()->json([
            'code' => $grn_code,
            'item_list' => $item_list,
            'warehouse_list' => $warehouse_list,
        ]);
    }


    public function create(Request $request)
    {
        // 1️⃣ Validate request
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'date' => 'required|date',
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
                'code' => $request->code,
                'date' => $request->date,
                'ware_house_id' => $request->warehouse_id,
                'description' => $request->description,
                'total_cost' => $request->total_cost ?? 0,
                'net_total' => $request->net_total ?? 0,
                'user' => $request->vat ?? 0,
                'is_void' => $request->is_void ?? 0,
            ];

            $grn = AdjustmentHeader::updateOrCreate(
                ['id' => $request->id ?? 0],
                $headerData
            );

            $items = $request->items;
            $stockController = new StockController();
            foreach ($items as $item) {
                AdjustmentDetail::create([
                    'adjustment_id' => $grn->id,
                    'line_no' => $item['line_no'],
                    'item_id' => $item['id'],
                    'reason' => $item['reason'],
                    'qoh' => $item['qoh'] ?? 0,
                    'qty' => $item['qty'] ?? 0,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'price_level1' => $item['price_level1'] ?? 0,
                    'price_level2' => $item['price_level2'] ?? 0,
                    'price_level3' => $item['price_level3'] ?? 0,
                    'line_cost' => $item['line_cost'] ?? 0,
                    'line_price_level1' => $item['line_price_level1'] ?? 0,
                    'is_void' => 0,
                ]);

                if ($item['qty'] < 0) {
                    $doc_reference = false;
                } else {
                    $doc_reference = true;
                }
                $stockController->updateStockAndActivity(
                    $grn->code,
                    'AJS',
                    $request->warehouse_id,
                    $item['id'],
                    $item['qty'],
                    $doc_reference,
                );

            }

            // 6️⃣ Update code if new GRN
            if (!$request->id) {
                $df = new DefaultController();
                $df->UpdateCode('ajs');
            }

            DB::commit();

            LogHelper::log(
                auth()->user()->name,
                $request->id ? 'update' : 'create',
                'Adjustment',
                $request->id ? 'update' : 'create' . $grn->id
            );

            return response()->json([
                'message' => $request->id ? 'Adjustment updated successfully' : 'Adjustment created successfully',
                'ajs_id' => $grn->id
            ], $request->id ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create/update Adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = AdjustmentHeader::with('warehouse')->orderBy('id', 'desc')->get();

          LogHelper::log(
            auth()->user()->name,
            'view',
            'Adjustment',
            'View Adjustment: ' . $itemCategories
        );

        return response()->json($itemCategories);
    }


    public function getById($id)
    {
        try {
            $grn = AdjustmentHeader::with([
                'warehouse',
                'details.items.skus'
            ])->findOrFail($id);

            LogHelper::log(
            auth()->user()->name,
            'view',
            'Adjustment',
            'View Adjustment: ' . $grn
        );
            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ajs not found' . $e], 404);
        }
    }

}
