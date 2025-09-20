<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::with(['officeHead', 'users', 'items'])->paginate(10);

        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availableHeads = User::whereIn('role', ['admin', 'office_head'])
                             ->where('status', 'active')
                             ->orderBy('name')
                             ->get();

        return view('admin.offices.create', compact('availableHeads'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:offices,code',
            'office_head_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        Office::create($request->all());

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        $office->load(['officeHead', 'users', 'items.currentHolder', 'items.category']);

        return view('admin.offices.show', compact('office'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        $availableHeads = User::whereIn('role', ['admin', 'office_head'])
                             ->where('status', 'active')
                             ->orderBy('name')
                             ->get();

        return view('admin.offices.edit', compact('office', 'availableHeads'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('offices')->ignore($office->id)],
            'office_head_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $office->update($request->all());

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        // Check if office has users or items assigned
        if ($office->users()->count() > 0 || $office->items()->count() > 0) {
            return redirect()->route('admin.offices.index')
                           ->with('error', 'Cannot delete office that has users or items assigned to it.');
        }

        $office->delete();

        return redirect()->route('admin.offices.index')
                        ->with('success', 'Office deleted successfully.');
    }
}