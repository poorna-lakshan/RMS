<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use App\Http\Controllers\Inventory\StockController;
use App\Http\Controllers\Main\DefaultController;
use App\Models\Inventory\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\TransferDetail;
use App\Models\Inventory\TransferHeader;
use App\Helpers\LogHelper;
class TransferController extends Controller
{
    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('trn');

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
            'from_ware_house_id' => 'required|integer',
            'to_ware_house_id' => 'required|integer',
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
                'from_ware_house_id' => $request->from_ware_house_id,
                'to_ware_house_id' => $request->to_ware_house_id,
                'description' => $request->description,
                'total_cost' => $request->total_cost ?? 0,
                'net_total' => $request->net_total ?? 0,
                'user' => $request->vat ?? 0,
                'is_void' => $request->is_void ?? 0,
            ];

            $grn = TransferHeader::updateOrCreate(
                ['id' => $request->id ?? 0],
                $headerData
            );

            $items = $request->items;
            $stockController = new StockController();
            foreach ($items as $item) {
                TransferDetail::create([
                    'transfer_id' => $grn->id,
                    'line_no' => $item['line_no'],
                    'item_id' => $item['id'],
                    'from_qoh' => $item['from_qoh'] ?? 0,
                    'to_qoh' => $item['to_qoh'] ?? 0,
                    'qty' => $item['qty'] ?? 0,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'price_level1' => $item['price_level1'] ?? 0,
                    'price_level2' => $item['price_level2'] ?? 0,
                    'price_level3' => $item['price_level3'] ?? 0,
                    'line_cost' => $item['line_cost'] ?? 0,
                    'line_price_level1' => $item['line_price_level1'] ?? 0,
                    'is_void' => 0,
                ]);

               $status =  $stockController->TransferBatch(
                    $grn->code,
                    'TRN',
                    $request->from_ware_house_id,
                       $request->to_ware_house_id,
                    $item['id'],
                    $item['qty'],
                    false,
                );


             

            }

            // 6️⃣ Update code if new GRN
            if (!$request->id) {
                $df = new DefaultController();
                $df->UpdateCode('trn');
            }

            DB::commit();

            LogHelper::log(
                auth()->user()->name,
                $request->id ? 'update' : 'create',
                'Transfer Note',
                $request->id ? 'update' : 'create' . $grn->id
            );

            return response()->json([
                'message' => $request->id ? 'Transfer Note updated successfully' : 'Transfer Note created successfully',
                'trn_id' => $grn->id
            ], $request->id ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create/update Transfer Note: ' . $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = TransferHeader::with('from_warehouse', 'to_warehouse')->get();

        LogHelper::log(
            auth()->user()->name,
            'view',
            'Transfer Note',
            'View Transfer Note: ' . $itemCategories
        );

        return response()->json($itemCategories);
    }

    public function getById($id)
    {
        try {
            $grn = TransferHeader::with([
                'from_warehouse',
                'to_warehouse',
                'details.items.skus'
            ])->findOrFail($id);

            LogHelper::log(
                auth()->user()->name,
                'view',
                'Transfer Note',
                'View Transfer Note: ' . $grn
            );

            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ajs not found' . $e], 404);
        }
    }
}
