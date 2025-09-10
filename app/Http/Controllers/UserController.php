<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController
{
    public function index()
    {
        return response()->json(User::where('role', 'faculty')->get());
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|string|unique:users,email|email',
            'department' => 'required|string',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,faculty'
        ]);
        
        $user = User::create([
            'name' => $fields['name'],
            'username' => $fields['username'],
            'email' => $fields['email'],
            'department' => $fields['department'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role']
        ]);
        
        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $fields = $request->validate([
            'name' => 'sometimes|string',
            'username' => [
                'sometimes', 
                'string', 
                Rule::unique('users')->ignore($user->id)
            ],
            'email' => [
                'sometimes', 
                'string', 
                'email', 
                Rule::unique('users')->ignore($user->id)
            ],
            'department' => 'sometimes|string',
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,faculty'
        ]);
        
        if (isset($fields['password'])) {
            $fields['password'] = Hash::make($fields['password']);
        }
        
        $user->update($fields);
        
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}