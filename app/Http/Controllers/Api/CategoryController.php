<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all()->map(function ($category) {
            $category->image_url = $category->image ? Storage::url($category->image) : null;
            return $category;
        });

        return response()->json($categories);
    }

    // Show a specific category
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Store a new category
    public function store(Request $request)
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'Category create successfully.'], 200);
    }


    public function update(Request $request, $id)
{
    $category = Category::findOrFail($id);


    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        // Store the new image
        $category->image = $request->file('image')->store('categories', 'public');
    }

    // Update category properties
    $category->name = $request->name;
    $category->description = $request->description;

    // Save the updated category
    $category->save();

    return response()->json(['message' => 'Category update successfully.'], 200);
}




    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.'], 200);
    }
}
