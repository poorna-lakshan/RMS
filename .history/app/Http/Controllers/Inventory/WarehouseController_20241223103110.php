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
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:50',
            'price_level' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $Warehouse = Warehouse::find($request->id);

        // Check if the ItemCategory exists
        if (!$Warehouse&&$request->id!=0) {
            return response()->json(['error' => 'Warehouse not found'], 404);
        }

        $user = Warehouse::updateOrCreate(
            ['id' => $request->id],  // This is the condition to check for an existing record.
            [
                'code' => $request->code,
                'name' => $request->name,
                'ap_acc' => $request->ap_acc,
                'ar_acc' => $request->ar_acc,
                'cash_acc' => $request->cash_acc,
                'sales_acc' => $request->sales_acc,
                'cos_acc' => $request->cos_acc,
                'inv_acc' => $request->inv_acc,
                'price_level' => $request->price_level,
            ]
        );

        if($request->id==0){
            return response()->json(['message' => 'Warehouse created successfully'], 201);
        }
        else{
            return response()->json(['message' => 'Warehouse updated successfully'], 200);
        }

    }


   


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $Warehouse = Warehouse::find($id);

      // Check if the ItemCategory exists
      if (!$Warehouse) {
          return response()->json(['error' => 'Warehouse not found'], 404);
      }

      // Delete the ItemCategory
      $Warehouse->delete();

      return response()->json(['message' => 'Warehouse deleted successfully'], 200);
  }

  public function index()
  {
      // Retrieve all ItemCategories
      $Warehouses = Warehouse::all();

      return response()->json([$Warehouses], 200);
  }

  public function getById($id)
  {
      // Find the ItemCategory by ID
      $Warehouse = Warehouse::find($id);

      // Check if the ItemCategory exists
      if (!$Warehouse) {
          return response()->json(['error' => 'Warehouse not found'], 404);
      }

      return response()->json(['Warehouse' => $Warehouse], 200);
  }

}

