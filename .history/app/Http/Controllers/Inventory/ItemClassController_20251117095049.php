<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\ItemClass;
use App\Helpers\LogHelper;
class ItemClassController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = ItemClass::create([
            'description' => $request->description,
        ]);

         LogHelper::log(
            auth()->user()->name,
            'create',
            'ItemClass',
            'Create Class: ' . $request->description
        );

        return response()->json(['message' => 'Item Class created successfully'], 201);
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
    $itemCategory = ItemClass::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Item Category not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
        'description' => $request->description,
    ]);

     LogHelper::log(
            auth()->user()->name,
            'update',
            'ItemClass',
            'Update Class: ' . $request->description
        );

    return response()->json(['message' => 'Item Category updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = ItemClass::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item Category not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

      LogHelper::log(
            auth()->user()->name,
            'delete',
            'ItemClass',
            'Delete Class: ' . $itemCategory
        );

      return response()->json(['message' => 'Item Category deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $itemCategories = ItemClass::all();

      LogHelper::log(
            auth()->user()->name,
            'view',
            'ItemClass',
            'View Class: ' . $itemCategories
        );


      return response()->json($itemCategories);
  }

  public function show($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = ItemClass::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Item Category not found'], 404);
      }

      LogHelper::log(
            auth()->user()->name,
            'view',
            'ItemClass',
            'View Class: ' . $itemCategory
        );

      return response()->json(['itemCategory' => $itemCategory], 200);
  }

}

