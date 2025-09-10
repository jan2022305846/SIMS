<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|unique:categories,name',
            'description' => 'sometimes|string'
        ]);
        
        $category = Category::create($fields);
        
        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $fields = $request->validate([
            'name' => 'sometimes|string|unique:categories,name,' . $category->id,
            'description' => 'sometimes|string'
        ]);
        
        $category->update($fields);
        
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        // Check if category has associated items
        if ($category->items()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated items'
            ], 400);
        }
        
        $category->delete();
        
        return response()->json(null, 204);
    }
}