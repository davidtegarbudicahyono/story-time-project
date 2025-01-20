<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    // Fetch all categories
    public function index()
    {
        $categories = DB::table('categories')->get();

        return response()->json([
            'message' => 'Categories fetched successfully',
            'categories' => $categories,
        ], 200);
    }

    // Store a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'name' => $request->name,
            'category_id' => $categoryId,
        ], 201);
    }

    // Show a category by ID
    public function show($id)
    {
        $category = DB::table('categories')->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Category fetched successfully',
            'category' => $category,
        ], 200);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = DB::table('categories')->where('id', $id)->exists();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        DB::table('categories')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
        ], 200);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = DB::table('categories')->where('id', $id)->exists();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        DB::table('categories')->where('id', $id)->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
