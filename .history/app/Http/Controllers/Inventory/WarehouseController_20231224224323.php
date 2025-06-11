<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Warehouse;
class WarehouseController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'price_level' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Warehouse::create([
            'name' => $request->name,
            'price_level' => $request->price_level,
        ]);

        return response()->json(['message' => 'Warehouse created successfully'], 201);
    }


    public function update(Request $request, $id)
   {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:50',
        'price_level' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Find the ItemCategory by ID
    $itemCategory = Warehouse::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Warehouse not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
        'name' => $request->name,
        'price_level' => $request->price_level,
    ]);

    return response()->json(['message' => 'Warehouse updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Warehouse::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Warehouse not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

      return response()->json(['message' => 'Warehouse deleted successfully'], 200);
  }

  public function getAll()
  {
      // Retrieve all ItemCategories
      $itemCategories = Warehouse::all();

      return response()->json(['Warehouse' => $itemCategories], 200);
  }

  public function getById($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Warehouse::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Warehouse not found'], 404);
      }

      return response()->json(['Warehouse' => $itemCategory], 200);
  }

}

