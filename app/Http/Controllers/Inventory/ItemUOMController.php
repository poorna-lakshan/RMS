<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Inventory\ItemUOM;
use App\Helpers\LogHelper;
class ItemUOMController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = ItemUOM::create([
            'description' => $request->description,
            'conversion_factor' => $request->factor
        ]);

         LogHelper::log(
            auth()->user()->name,
            'create',
            'ItemUOM',
            'Create UOM: ' . $request->description
        );


        return response()->json(['message' => 'Item UOM created successfully'], 201);
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
        $itemCategory = ItemUOM::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Item Category not found'], 404);
        }

        // Update the ItemCategory
        $itemCategory->update([
            'description' => $request->description,
            'conversion_factor' => $request->factor,
        ]);

         LogHelper::log(
            auth()->user()->name,
            'update',
            'ItemUOM',
            'Update UOM: ' . $request->description
        );

        return response()->json(['message' => 'Item UOM updated successfully'], 200);
    }


    public function delete($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = ItemUOM::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Item UOM not found'], 404);
        }

        // Delete the ItemCategory
        $itemCategory->delete();

         LogHelper::log(
            auth()->user()->name,
            'delete',
            'ItemUOM',
            'Delete UOM: ' . $itemCategory
        );

        return response()->json(['message' => 'Item UOM deleted successfully'], 200);
    }

    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = ItemUOM::all();

         LogHelper::log(
            auth()->user()->name,
            'view',
            'ItemUOM',
            'View UOM: ' . $itemCategories
        );

        return response()->json($itemCategories);
    }

    public function show($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = ItemClass::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Item UOM not found'], 404);
        }

        LogHelper::log(
            auth()->user()->name,
            'view',
            'ItemUOM',
            'View UOM: ' . $itemCategory
        );
        return response()->json(['item UOM' => $itemCategory], 200);
    }

}
