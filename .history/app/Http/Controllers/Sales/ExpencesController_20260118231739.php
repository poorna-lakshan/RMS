<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\ExpencesCategory;
use App\Models\Sales\Expences;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogHelper;
class ExpencesController extends Controller
{

    //Start ExpencesCategory
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        if ($request->id != 0) {

            $ExpencesCategory = ExpencesCategory::find($request->id);

            // Check if the ItemCategory exists
            if (!$ExpencesCategory) {
                return response()->json(['error' => 'Expences Categorynot found'], 404);
            }

            // Update the ItemCategory
            $ExpencesCategory->update([
                'description' => $request->description,
            ]);

        } else {
            $user = ExpencesCategory::create([
                'description' => $request->description,
            ]);
        }

        LogHelper::log(
            auth()->user()->name,
            'create',
            'ExpencesCategory',
            'Create category: ' . $request->description
        );


        if ($request->id != 0) {
            return response()->json(['message' => 'Expences Category updated successfully'], 201);

        } else {
            return response()->json(['message' => 'Expences Category created successfully'], 201);

        }
    }


    public function deleteCategory($id)
    {
        // Find the ItemCategory by ID
        $ExpencesCategory = ExpencesCategory::find($id);

        // Check if the ItemCategory exists
        if (!$ExpencesCategory) {
            return response()->json(['error' => 'Expences Category not found'], 404);
        }

        // Delete the ItemCategory
        $ExpencesCategory->delete();

        LogHelper::log(
            auth()->user()->name,
            'delete',
            'ExpencesCategory',
            'Delete category: ' . $ExpencesCategory
        );

        return response()->json(['message' => 'Expences Category deleted successfully'], 200);
    }


    public function indexCategory()
    {
        // Retrieve all ItemCategories
        $ExpencesCategory = ExpencesCategory::all();

        LogHelper::log(
            auth()->user()->name,
            'view',
            'ExpencesCategory',
            'View category: ' . $ExpencesCategory
        );

        return response()->json($ExpencesCategory);
    }


    //End ExpencesCategory


    //Start Expences

    //End Expences
}
