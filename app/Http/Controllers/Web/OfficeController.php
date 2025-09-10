<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::with(['officeHead', 'users'])
                        ->withCount(['users', 'faculty', 'officeHeads'])
                        ->paginate(10);
        
        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::whereIn('role', ['admin', 'office_head'])
                    ->orderBy('name')
                    ->get();
        
        return view('admin.offices.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:offices,code',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'office_head_id' => 'nullable|exists:users,id',
        ]);

        Office::create($validatedData);

        return redirect()->route('offices.index')
                        ->with('success', 'Office created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        $office->load(['officeHead', 'users', 'faculty', 'officeHeads']);
        $stats = $office->getStats();
        
        return view('admin.offices.show', compact('office', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        $users = User::whereIn('role', ['admin', 'office_head'])
                    ->orderBy('name')
                    ->get();
        
        return view('admin.offices.edit', compact('office', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:offices,code,' . $office->id,
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'office_head_id' => 'nullable|exists:users,id',
        ]);

        $office->update($validatedData);

        return redirect()->route('offices.show', $office)
                        ->with('success', 'Office updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        // Check if office has users
        if ($office->users()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete office that has assigned users. Please reassign users first.'
            ]);
        }

        $office->delete();

        return redirect()->route('offices.index')
                        ->with('success', 'Office deleted successfully.');
    }

    /**
     * Assign users to office
     */
    public function assignUsers(Request $request, Office $office)
    {
        $validatedData = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $validatedData['user_ids'])
            ->update(['office_id' => $office->id]);

        return back()->with('success', 'Users assigned to office successfully.');
    }
}
