<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Item;
class ItemController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'description' => 'required|string|max:50',
            'class_id' => 'required',
            'category_id' => 'required',
            'uom' => 'required',
            'costing_method' => 'required',
            'vendor_id' => 'required',
            'sales_acc' => 'required',
            'cost_of_sales_acc' => 'required',
            'inventory_acc' => 'required',
            'unit_cost' => 'required',
            'price_level1' => 'required',
            'price_level2' => 'required',
            'price_level3' => 'required',
            'discount_amt' => 'required',
            'discount_presentage' => 'required',
            'reorder_qty' => 'required',
            'minimum_qty' => 'required',
            'barcode' => 'required',
            'kot' => 'required',
            'bot' => 'required',
            'custom1' => 'required',
            'custom2' => 'required',
            'custom3' => 'required',
            'custom4' => 'required',
            'custom5' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Item::create([
            'code' => $request->code,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'category_id' => $request->category_id,
            'uom' => $request->uom,
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
            'kot' => $request->kot,
            'bot' => $request->bot,
            'custom1' => $request->custom1,
            'custom2' => $request->custom2,
            'custom3' => $request->custom3,
            'custom4' => $request->custom4,
            'custom5' => $request->custom5,
        ]);

        return response()->json(['message' => 'Item created successfully'], 201);
    }


    public function update(Request $request, $id)
   {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'code' => 'required|string',
            'description' => 'required|string|max:50',
            'class_id' => 'required',
            'category_id' => 'required',
            'uom' => 'required',
            'costing_method' => 'required',
            'vendor_id' => 'required',
            'sales_acc' => 'required',
            'cost_of_sales_acc' => 'required',
            'inventory_acc' => 'required',
            'unit_cost' => 'required',
            'price_level1' => 'required',
            'price_level2' => 'required',
            'price_level3' => 'required',
            'discount_amt' => 'required',
            'discount_presentage' => 'required',
            'reorder_qty' => 'required',
            'minimum_qty' => 'required',
            'barcode' => 'required',
            'kot' => 'required',
            'bot' => 'required',
            'custom1' => 'required',
            'custom2' => 'required',
            'custom3' => 'required',
            'custom4' => 'required',
            'custom5' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Find the ItemCategory by ID
    $itemCategory = Item::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Item not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
        'code' => $request->code,
        'description' => $request->description,
        'class_id' => $request->class_id,
        'category_id' => $request->category_id,
        'uom' => $request->uom,
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
        'kot' => $request->kot,
        'bot' => $request->bot,
        'custom1' => $request->custom1,
        'custom2' => $request->custom2,
        'custom3' => $request->custom3,
        'custom4' => $request->custom4,
        'custom5' => $request->custom5,
    ]);

    return response()->json(['message' => 'Item updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Item::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

      return response()->json(['message' => 'Item deleted successfully'], 200);
  }

  public function getAll()
  {
      // Retrieve all ItemCategories
      $itemCategories = Item::all();

      return response()->json(['item' => $itemCategories], 200);
  }

  public function getById($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Item::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item not found'], 404);
      }

      return response()->json(['item' => $itemCategory], 200);
  }
}
