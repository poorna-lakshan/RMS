<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchasing\Vendor;
class VendorController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Vendor::create([
            'code' => $request->code,
            'name' => $request->name,
            'contact' => $request->contact,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'ref_name' => $request->ref_name,
            'ref_contact' => $request->ref_contact,
            'company_name' => $request->company_name,
            'other_name' => $request->other_name,
            'custom1' => $request->custom1,
            'custom2' => $request->custom2,
            'custom3' => $request->custom3,
            'custom4' => $request->custom4,
            'custom5' => $request->custom5,
        ]);

        return response()->json(['message' => 'Vendor created successfully'], 201);
    }


    public function update(Request $request, $id)
   {
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'code' => 'required|string|max:50',
        'name' => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Find the ItemCategory by ID
    $itemCategory = Vendor::find($id);

    // Check if the ItemCategory exists
    if (!$itemCategory) {
        return response()->json(['error' => 'Vendor not found'], 404);
    }

    // Update the ItemCategory
    $itemCategory->update([
            'code' => $request->code,
            'name' => $request->name,
            'contact' => $request->contact,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'ref_name' => $request->ref_name,
            'ref_contact' => $request->ref_contact,
            'company_name' => $request->company_name,
            'other_name' => $request->other_name,
            'custom1' => $request->custom1,
            'custom2' => $request->custom2,
            'custom3' => $request->custom3,
            'custom4' => $request->custom4,
            'custom5' => $request->custom5,
    ]);

    return response()->json(['message' => 'Vendor updated successfully'], 200);
  }


  public function delete($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Vendor::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Vendor not found'], 404);
      }

      // Delete the ItemCategory
      $itemCategory->delete();

      return response()->json(['message' => 'Vendor deleted successfully'], 200);
  }

  public function getAll()
  {
      // Retrieve all ItemCategories
      $itemCategories = Vendor::all();

      return response()->json($itemCategories);
  }

  public function getById($id)
  {
      // Find the ItemCategory by ID
      $itemCategory = Vendor::find($id);

      // Check if the ItemCategory exists
      if (!$itemCategory) {
          return response()->json(['error' => 'Vendor not found'], 404);
      }

      return response()->json($itemCategory);
  }

}

