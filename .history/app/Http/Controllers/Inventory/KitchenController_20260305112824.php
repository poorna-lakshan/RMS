<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\Kitchen;
use App\Helpers\LogHelper;
use App\Models\default\DeletedRows;
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
                'printer_name' => $request->printer_name,
            ]
        );

         LogHelper::log(
            auth()->user()->name,
            $request->id ? 'update' : 'create',
            'kitchen',
            $request->id ? 'update' : 'create' .'kitchen'. $request->code
        );

        if($request->id==0){
            return response()->json(['message' => 'Kitchen created successfully'], 201);
        }
        else{
            return response()->json(['message' => 'Kitchen updated successfully'], 200);
        }

    }
public function update(Request $request, $id)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'code' => 'required|string|max:50',
        'name' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the kitchen record
    $kitchen = Kitchen::find($id);

    if (!$kitchen) {
        return response()->json([
            'success' => false,
            'message' => 'Kitchen not found'
        ], 404);
    }

    try {
        // Update the kitchen
        $kitchen->update([
            'code' => $request->code,
            'name' => $request->name,
            'printer_name' => $request->printer_name,
            'is_sync' => 0
        ]);

         LogHelper::log(
            auth()->user()->name,
            'update',
            'kitchen',
            'update kitchen: ' . $request->code
        );

        return response()->json([
            'success' => true,
            'message' => 'Kitchen updated successfully',
            'data' => $kitchen
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update kitchen',
            'error' => $e->getMessage()
        ], 500);
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

      $user = DeletedRows::create([
            'type' => 'Kitchen',
            'key' => $id,
        ]);

       LogHelper::log(
            auth()->user()->name,
            'delete',
            'kitchen',
            'delete kitchen: ' . $Kitchen
        );

      return response()->json(['message' => 'Kitchen deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $Kitchen = Kitchen::all();

       LogHelper::log(
            auth()->user()->name,
            'view',
            'kitchen',
            'view: ' . $Kitchen
        );

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

      LogHelper::log(
            auth()->user()->name,
            'view',
            'kitchen',
            'view: ' . $Kitchen
        );

      return response()->json($Kitchen);
  }

}

