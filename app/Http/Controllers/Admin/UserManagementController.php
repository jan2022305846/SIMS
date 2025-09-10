<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of users with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = User::with(['office']);

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('school_id', 'like', "%{$search}%");
            });
        }

        // Sort users
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Get user statistics for admin overview
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'users_by_role' => User::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role'),
            'users_by_department' => User::selectRaw('department, COUNT(*) as count')
                ->whereNotNull('department')
                ->groupBy('department')
                ->get()
                ->pluck('count', 'department'),
            'recent_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'school_id' => 'required|string|max:20|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,office_head,faculty',
            'department' => 'required|string|max:100',
            'office_id' => 'nullable|exists:offices,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'school_id' => $request->school_id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department' => $request->department,
            'office_id' => $request->office_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user->load('office')
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->load(['office'])
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'school_id' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => 'required|in:admin,office_head,faculty',
            'department' => 'required|string|max:100',
            'office_id' => 'nullable|exists:offices,id',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'name', 'username', 'school_id', 'email', 'role', 'department', 'office_id'
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->fresh()->load('office')
        ]);
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete the last admin user'
            ], 422);
        }

        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Get available offices for user assignment
     */
    public function getOffices()
    {
        $offices = Office::orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $offices
        ]);
    }
}
