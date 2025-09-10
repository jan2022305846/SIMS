<?php

namespace App\Http\Controllers;

use App\Models\Request as SupplyRequest;
use App\Models\Item;
use App\Models\Log;
use Illuminate\Http\Request;

class RequestController
{
    public function index()
    {
        return response()->json(
            SupplyRequest::with(['user', 'item'])->latest()->get()
        );
    }
    
    public function myRequests(Request $request)
    {
        return response()->json(
            SupplyRequest::with(['item'])
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get()
        );
    }
    
    public function store(Request $request)
    {
        $fields = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        // Check if requested quantity is available
        $item = Item::findOrFail($fields['item_id']);
        if ($item->quantity < $fields['quantity']) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock'
            ], 400);
        }
        
        $supplyRequest = SupplyRequest::create([
            'user_id' => $request->user()->id,
            'item_id' => $fields['item_id'],
            'quantity' => $fields['quantity'],
            'status' => 'pending',
            'request_date' => now(),
        ]);
        
        // Create log
        Log::create([
            'user_id' => $request->user()->id,
            'action' => 'create',
            'description' => 'Created request for ' . $fields['quantity'] . ' ' . $item->name,
            'timestamp' => now(),
        ]);
        
        return response()->json($supplyRequest->load(['user', 'item']), 201);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $fields = $request->validate([
            'status' => 'required|in:approved,declined,returned',
        ]);
        
        $supplyRequest = SupplyRequest::findOrFail($id);
        
        // Check if request is already processed
        if ($supplyRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update a processed request'
            ], 400);
        }
        
        $supplyRequest->status = $fields['status'];
        
        if ($fields['status'] === 'approved') {
            $supplyRequest->approval_date = now();
            
            // Reduce inventory quantity
            $item = Item::findOrFail($supplyRequest->item_id);
            $item->quantity -= $supplyRequest->quantity;
            $item->save();
        } else if ($fields['status'] === 'returned') {
            $supplyRequest->return_date = now();
        }
        
        $supplyRequest->save();
        
        // Create log
        Log::create([
            'user_id' => $request->user()->id,
            'action' => 'update',
            'description' => 'Updated request status to ' . $fields['status'],
            'timestamp' => now(),
        ]);
        
        return response()->json($supplyRequest->load(['user', 'item']));
    }
}