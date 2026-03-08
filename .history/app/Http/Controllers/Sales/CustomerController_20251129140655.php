<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sales\Customer;
class CustomerController extends Controller
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

        $customer = Customer::create([
            'code' => $request->code,
            'name' => $request->name,
            'contact' => $request->contact,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'email' => $request->email,
            'vat_no' => $request->vat_no,
            'type' => $request->type,
            'custom1' => $request->custom1,
            'custom2' => $request->custom2,
            'custom3' => $request->custom3,
            'custom4' => $request->custom4,
            'custom5' => $request->custom5,
        ]);

        return response()->json([
            'message' => 'Customer created successfully',
            'id' => $customer->id,       // 🔥 return new ID
            'customer' => $customer      // optional full object
        ], 201);
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
        $itemCategory = Customer::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Update the ItemCategory
        $itemCategory->update([
            'code' => $request->code,
            'name' => $request->name,
            'contact' => $request->contact,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'email' => $request->email,
            'vat_no' => $request->vat_no,
            'type' => $request->type,
            'custom1' => $request->custom1,
            'custom2' => $request->custom2,
            'custom3' => $request->custom3,
            'custom4' => $request->custom4,
            'custom5' => $request->custom5,
        ]);

        return response()->json(['message' => 'Customer updated successfully'], 200);
    }
    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = Customer::all();

        return response()->json($itemCategories);
    }

    public function delete($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = Customer::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Delete the ItemCategory
        $itemCategory->delete();

        return response()->json(['message' => 'Customer deleted successfully'], 200);
    }

    public function getAll()
    {
        // Retrieve all ItemCategories
        $itemCategories = Customer::all();

        return response()->json(['Vendor' => $itemCategories], 200);
    }

    public function getById($id)
    {
        // Find the ItemCategory by ID
        $itemCategory = Customer::find($id);

        // Check if the ItemCategory exists
        if (!$itemCategory) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json(['Customer' => $itemCategory], 200);
    }
}
