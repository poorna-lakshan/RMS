<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Purchasing\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemSku;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Helpers\LogHelper;
use App\Models\default\DeletedRows;
class ItemController extends Controller
{

    public function create(Request $request)
    {
        // 1. Validate request
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'description' => [
                'required',
                'string',
                'max:50',
                // Only enforce unique when creating, ignore current record when updating
                Rule::unique('item', 'description')->ignore($request->id),
            ],
            'class_id' => 'required|integer',
            'category_id' => 'required|integer',
            'type' => 'required|string',
            'uom_id' => 'required|integer',
            'costing_method' => 'required|string',
            'unit_cost' => 'required|numeric',
            'price_level1' => 'required|numeric',
            'price_level2' => 'required|numeric',
            'price_level3' => 'required|numeric',
            'discount_amt' => 'required|numeric',
            'discount_presentage' => 'required|numeric',
            'reorder_qty' => 'required|numeric',
            'minimum_qty' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'sub_category' => 'nullable|string',
            'vendor_id' => 'nullable|integer',
            'sales_acc' => 'nullable|string',
            'cost_of_sales_acc' => 'nullable|string',
            'inventory_acc' => 'nullable|string',
            'barcode' => 'nullable|string',
            'kitchen_id' => 'nullable|integer',
            'custom1' => 'nullable|string',
            'custom2' => 'nullable|string',
            'custom3' => 'nullable|string',
            'custom4' => 'nullable|string',
            'custom5' => 'nullable|string',
            'warehouses' => 'nullable|string', // JSON string
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // 2. Decode warehouse data
        $warehouses = json_decode($request->input('warehouses'), true) ?? [];

        DB::beginTransaction();
        try {
            // 3. Map request fields to database columns
            $data = [
                'code' => $request->code,
                'description' => $request->description,
                'class_id' => $request->class_id,
                'category_id' => $request->category_id,
                'sub_category' => $request->sub_category,
                'type' => $request->type,
                'uom_id' => $request->uom_id,
                'costing_method' => $request->costing_method,
                'vendor_id' => $request->vendor_id,
                'sales_acc' => $request->sales_acc,
                'cost_of_sales_acc' => $request->cost_of_sales_acc,
                'inventory_acc' => $request->inventory_acc,
                'unit_cost' => $request->unit_cost,
                'price_level1' => $request->price_level1,
                'price_level2' => $request->price_level2,
                'price_level3' => $request->price_level3,
                'discount_amt' => $request->discount_amt,
                'discount_presentage' => $request->discount_presentage,
                'reorder_qty' => $request->reorder_qty,
                'minimum_qty' => $request->minimum_qty,
                'barcode' => $request->barcode,
                'kitchen_id' => $request->kitchen_id,
                'custom1' => $request->custom1,
                'custom2' => $request->custom2,
                'custom3' => $request->custom3,
                'custom4' => $request->custom4,
                'custom5' => $request->custom5,
                'is_sync' => 0,
            ];

            // 4. Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('images/items', 'public');
            }

            // 5. Create or update the item using updateOrCreate
            $item = Item::updateOrCreate(
                ['id' => $request->id ?? 0], // match by ID if updating, 0 will create new if null
                $data
            );

            $warehouses = json_decode($request->input('warehouses'), true) ?? [];

            $existingPivots = $item->warehouses()->withPivot('qty')->get();
            $existingIds = $existingPivots->pluck('id')->toArray();

            $userWarehouseIds = collect($warehouses)->pluck('id')->filter()->toArray();

            foreach ($warehouses as $wh) {
                $warehouseId = $wh['id'] ?? null;
                $qty = $wh['qty'] ?? 0;
                if (!$warehouseId)
                    continue;

                if (!in_array($warehouseId, $existingIds)) {
                    // Not in DB → insert
                    $item->warehouses()->attach($warehouseId, [
                        'qty' => $qty,
                        'avg_cost' => $request->unit_cost ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            foreach ($existingPivots as $pivot) {
                if (!in_array($pivot->id, $userWarehouseIds) && $pivot->pivot->qty == 0) {
                    $item->warehouses()->detach($pivot->id);
                }
            }

            $skus = json_decode($request->input('skus'), true) ?? [];

            // Get all existing SKUs for this item
            $existingSkus = ItemSku::where('item_id', $item->id)->get();

            // Collect the incoming uom_ids (or ids)
            $incomingIds = collect($skus)->pluck('id')->toArray();

            // 1️⃣ Delete only the SKUs that are not in the new request
            ItemSku::where('item_id', $item->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            // 2️⃣ Update or insert SKUs
            foreach ($skus as $skuData) {
                ItemSku::updateOrCreate(
                    [
                        'id' => $skuData['id'] ?? null, // if exists → update; else → create
                    ],
                    [
                        'item_id' => $item->id,
                        'uom_id' => $skuData['uom_id'],
                        'sku' => $skuData['sku'],
                        'qty_per_sku' => $skuData['qty_per_sku'],
                    ]
                );
            }

            // 7. Update codegen only if creating new item
            if (!$request->id) {
                $df = new DefaultController();
                $df->UpdateCode('item');
            }


            DB::commit();

            LogHelper::log(
                auth()->user()->name,
                $request->id ? 'update' : 'create',
                'Item',
                $request->id ? 'update' : 'create' . $request->description
            );

            return response()->json([
                'message' => $request->id ? 'Item updated successfully' : 'Item created successfully',
                'item_id' => $item->id
            ], $request->id ? 200 : 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create/update item: ' . $e->getMessage()
            ], 500);
        }
    }




    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // 1. Find the item
            $item = Item::findOrFail($id);

            // 2. Check if item exists in orders
            $hasOrders = DB::table('order_detail')
                ->where('item_id', $id)
                ->exists();

            // 3. Check if item exists in invoices
            $hasInvoices = DB::table('invoice_detail')
                ->where('item_id', $id)
                ->exists();

            if ($hasOrders || $hasInvoices) {
                return response()->json([
                    'error' => 'Cannot delete item. It is used in orders or invoices.'
                ], 400);
            }

            // 4. Detach warehouse links
            $item->warehouses()->detach();

            // 5. Delete item image
            if ($item->image) {
                \Storage::disk('public')->delete($item->image);
            }

            // 6. Delete item
            $item->delete();

            // 7. Track deleted rows
            DeletedRows::create([
                'type' => 'Item',
                'key' => $id,
            ]);

            DB::commit();

            LogHelper::log(
                auth()->user()->name,
                'delete',
                'Item',
                'Delete Item: ' . $item->name
            );

            return response()->json([
                'message' => 'Item deleted successfully'
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Failed to delete item',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {

        $items = Item::with(['category', 'uom', 'itemClass', 'warehouses', 'skus'])->get();
        $items->each->append('image_url');
        LogHelper::log(
            auth()->user()->name,
            'view',
            'Item',
            'View Item'
        );
        return response()->json($items);
    }



    public function getDefault()
    {
        $df = new DefaultController();
        $item_code = $df->generateCode('item');

        $itm_cat = new ItemCategoryController();
        $item_categories = $itm_cat->index()->getData(true);

        $itm_class = new ItemClassController();
        $item_classes = $itm_class->index()->getData(true);

        $itm_uom = new ItemUOMController();
        $itm_uom = $itm_uom->index()->getData(true);

        $kitchen = new KitchenController();
        $kitchen_list = $kitchen->index()->getData(true);

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);

        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);

        $item_sub_categories = Item::select('sub_category')
            ->distinct()
            ->whereNotNull('sub_category')
            ->pluck('sub_category')
            ->map(function ($sub_category) {
                return ['name' => $sub_category];
            })
            ->toArray();

        return response()->json([
            'item_code' => $item_code,
            'categories' => $item_categories,
            'classes' => $item_classes,
            'UOM' => $itm_uom,
            'kitchen_list' => $kitchen_list,
            'warehouse_list' => $warehouse_list,
            'vendor_list' => $vendor_list,
            'item_sub_categories' => $item_sub_categories,
        ]);
    }

    public function getById($id)
    {
        // Load item with relationships
        $item = Item::with(['warehouses', 'kitchans', 'skus'])->find($id);

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Add image_url accessor
        $item->append('image_url');

        LogHelper::log(
            auth()->user()->name,
            'view',
            'Item',
            'View Item: ' . $item
        );

        return response()->json(['item' => $item], 200);
    }
}
