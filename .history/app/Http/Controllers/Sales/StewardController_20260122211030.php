<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Steward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\default\DeletedRows;
class StewardController extends Controller
{
     public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Steward::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Steward created successfully'], 201);
    }


    public function update(Request $request, $id)
   {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Find the ItemCategory by ID
    $itemCategory = Steward::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Steward not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
        'name' => $request->name,
        'is_sync' => 0,
    ]);

    return response()->json(['message' => 'Steward updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Steward::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Steward not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

         $user = DeletedRows::create([
            'type' => 'Steward',
            'key' => $id,
        ]);

      return response()->json(['message' => 'Steward deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $itemCategories = Steward::all();

      return response()->json($itemCategories);
  }

  public function show($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Steward::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Steward not found'], 404);
      }

      return response()->json(['Steward' => $itemCategory], 200);
  }
}
