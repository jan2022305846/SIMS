<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController
{
    public function index()
    {   
        $perPage = request()->query('per_page', 10);
        $page = request()->query('page', 1);
        
        return response()->json(
            Item::with('category')->orderBy('name')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string',
            'description' => 'sometimes|string|nullable',
            'quantity' => 'required|integer|min:0',
            'price' => 'sometimes|numeric|nullable',
            'unit' => 'required|string',
            'location' => 'sometimes|string|nullable',
            'condition' => 'sometimes|string|nullable',
            'qr_code' => 'sometimes|string|nullable|unique:items,qr_code',
            'expiry_date' => 'sometimes|date|nullable',
        ]);
        
        $item = Item::create($fields);
        
        return response()->json($item->load('category'), 201);
    }

    public function show(Item $item)
    {
        return response()->json($item->load('category'));
    }

    public function update(Request $request, Item $item)
    {
        $fields = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string',
            'description' => 'sometimes|string|nullable',
            'quantity' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|nullable',
            'unit' => 'sometimes|string',
            'location' => 'sometimes|string|nullable',
            'condition' => 'sometimes|string|nullable',
            'qr_code' => 'sometimes|string|nullable|unique:items,qr_code,'.$item->id,
            'expiry_date' => 'sometimes|date|nullable',
        ]);
        
        $item->update($fields);
        
        return response()->json($item->load('category'));
    }

    public function destroy(Item $item)
    {
        // Check if item has associated requests
        if ($item->requests()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete item with associated requests'
            ], 400);
        }
        
        $item->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Search for items
     */
    public function search(Request $request)
    {
        $query = $request->validate([
            'term' => 'required|string|min:2',
        ]);
        
        $perPage = request()->query('per_page', 10);
        
        $items = Item::with('category')
            ->where('name', 'like', '%' . $query['term'] . '%')
            ->orWhere('description', 'like', '%' . $query['term'] . '%')
            ->orWhere('qr_code', 'like', '%' . $query['term'] . '%')
            ->paginate($perPage);
            
        return response()->json($items);
    }
    
    /**
     * Get items with low quantity
     */
    public function lowStock()
    {
        $perPage = request()->query('per_page', 10);
        
        $items = Item::with('category')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->paginate($perPage);
            
        return response()->json($items);
    }
    
    /**
     * Get items by category
     */
    public function byCategory($categoryId)
    {
        $perPage = request()->query('per_page', 10);
        
        $items = Item::with('category')
            ->where('category_id', $categoryId)
            ->orderBy('name')
            ->paginate($perPage);
            
        return response()->json($items);
    }
    
    /**
     * Get items that are about to expire
     */
    public function expiringSoon()
    {
        $perPage = request()->query('per_page', 10);
        $threshold = now()->addDays(30);
        
        $items = Item::with('category')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $threshold)
            ->where('expiry_date', '>=', now())
            ->orderBy('expiry_date')
            ->paginate($perPage);
            
        return response()->json($items);
    }

    public function trashed()
    {
        $perPage = request()->query('per_page', 10);
        
        return response()->json(
            Item::onlyTrashed()
                ->with('category')
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage)
        );
    }

    public function restore($id) {
        $item = Item::withTrashed()->findOrFail($id);
        $item->restore();
        
        return response()->json($item->load('category'));
    }
}