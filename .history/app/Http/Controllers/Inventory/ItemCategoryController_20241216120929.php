<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\ItemCategory;
class ItemCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = ItemCategory::create([
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Item Category created successfully'], 201);
    }


    public function update(Request $request, $id)
   {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'description' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Find the ItemCategory by ID
    $itemCategory = ItemCategory::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Item Category not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
        'description' => $request->description,
    ]);

    return response()->json(['message' => 'Item Category updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = ItemCategory::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item Category not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

      return response()->json(['message' => 'Item Category deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $itemCategories = ItemCategory::all();

      return response()->json($categories);
  }

  public function show($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = ItemCategory::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item Category not found'], 404);
      }

      return response()->json(['itemCategory' => $itemCategory], 200);
  }

}
