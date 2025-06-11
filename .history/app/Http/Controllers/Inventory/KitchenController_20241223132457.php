<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Kitchen;

class KitchenController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $Kitchen = Kitchen::find($request->id);

        // Check if the ItemCategory exists
        if (!$Kitchen&&$request->id!=0) {
            return response()->json(['error' => 'Kitchen not found'], 404);
        }

        $user = Kitchen::updateOrCreate(
            ['id' => $request->id],  // This is the condition to check for an existing record.
            [
                'code' => $request->code,
                'name' => $request->name,
            ]
        );

        if($request->id==0){
            return response()->json(['message' => 'Kitchen created successfully'], 201);
        }
        else{
            return response()->json(['message' => 'Kitchen updated successfully'], 200);
        }

    }

  public function delete($id)
  {
      // Find the ItemCategory by ID
      $Kitchen = Kitchen::find($id);

      // Check if the ItemCategory exists
      if (!$Kitchen) {
          return response()->json(['error' => 'Kitchen not found'], 404);
      }

      // Delete the ItemCategory
      $Kitchen->delete();

      return response()->json(['message' => 'Kitchen deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $Kitchen = Kitchen::all();

      return response()->json($Kitchen);
  }

  public function show($id)
  {
      // Find the ItemCategory by ID
      $Kitchen = Kitchen::find($id);

      // Check if the ItemCategory exists
      if (!$Kitchen) {
          return response()->json(['error' => 'Kitchen not found'], 404);
      }

      return response()->json($Warehouse);
  }

}

