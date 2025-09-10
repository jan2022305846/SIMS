<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController
{
    public function index()
    {
        return response()->json(
            Log::with('user')->latest('created_at')->get()
        );
    }
    
    public function store(Request $request)
    {
        $fields = $request->validate([
            'action' => 'required|string',
            'description' => 'required|string',
        ]);
        
        $log = Log::create([
            'user_id' => $request->user()->id,
            'action' => $fields['action'],
            'description' => $fields['description'],
            'created_at' => $fields['created_at'],
            'timestamp' => now(),
        ]);
        
        return response()->json($log->load('user'), 201);
    }
}