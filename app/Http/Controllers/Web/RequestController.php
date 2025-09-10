<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as SupplyRequest;
use App\Models\Item;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Request Controller for managing supply requests
 * 
 * @method User user() Get the authenticated user with proper type hints
 */

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyRequest::with(['user', 'item', 'officeHeadApprover', 'adminApprover'])
            ->latest();

        // Filter based on user role
        /** @var User $user */
        $user = Auth::user();
        if ($user->role === 'faculty') {
            // Faculty can only see their own requests
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'office_head') {
            // Office heads can see pending requests and requests they've approved
            $query->where(function($q) use ($user) {
                $q->where('workflow_status', 'pending')
                  ->orWhere('approved_by_office_head_id', $user->id);
            });
        }
        // Admins can see all requests (no filter needed)

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', "%{$searchTerm}%")
                             ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                ->orWhereHas('item', function($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%");
                })
                ->orWhere('purpose', 'like', "%{$searchTerm}%")
                ->orWhere('claim_slip_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter with support for declined status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'declined') {
                $query->where(function($q) {
                    $q->where('workflow_status', 'declined_by_office_head')
                      ->orWhere('workflow_status', 'declined_by_admin');
                });
            } else {
                $query->where('workflow_status', $request->status);
            }
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }

        $requests = $query->paginate(15);

        return view('admin.requests.index', compact('requests'));
    }

    public function create()
    {
        $items = Item::where('current_stock', '>', 0)->get();
        $departments = [
            'IT Department',
            'HR Department', 
            'Finance Department',
            'Operations Department',
            'Marketing Department',
            'Engineering Department'
        ];

        return view('admin.requests.create', compact('items', 'departments'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'department' => 'required|string|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx', // 5MB max per file
        ]);

        // Check stock availability
        $item = Item::findOrFail($validatedData['item_id']);
        if ($item->current_stock < $validatedData['quantity']) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->current_stock . ' available)']);
        }

        DB::beginTransaction();
        try {
            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('request-attachments', $filename, 'public');
                    $attachments[] = [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                    ];
                }
            }

            $supplyRequest = SupplyRequest::create([
                'user_id' => Auth::id(),
                'item_id' => $validatedData['item_id'],
                'quantity' => $validatedData['quantity'],
                'purpose' => $validatedData['purpose'],
                'needed_date' => $validatedData['needed_date'],
                'department' => $validatedData['department'],
                'priority' => $validatedData['priority'],
                'workflow_status' => 'pending',
                'status' => 'pending', // Keep for backwards compatibility
                'request_date' => now(),
                'attachments' => $attachments,
            ]);

            // Create log entry
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'description' => 'Created request for ' . $validatedData['quantity'] . ' ' . $item->name . ' (Priority: ' . $validatedData['priority'] . ')',
                'timestamp' => now(),
            ]);

            DB::commit();

            return redirect()->route('requests.show', $supplyRequest)
                ->with('success', 'Request submitted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to submit request: ' . $e->getMessage()]);
        }
    }

    public function show(SupplyRequest $request)
    {
        $request->load(['user', 'item', 'officeHeadApprover', 'adminApprover', 'fulfilledBy', 'claimedBy']);

        // Check permissions
        /** @var User $user */
        $user = Auth::user();
        if ($user->role === 'faculty' && $request->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this request.');
        }

        return view('admin.requests.show', compact('request'));
    }

    public function edit(SupplyRequest $request)
    {
        // Only allow editing if request is still pending and user is the requester or admin
        /** @var User $user */
        $user = Auth::user();
        if (!$request->isPending() || ($user->role !== 'admin' && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be edited.');
        }

        $items = Item::where('current_stock', '>', 0)->get();
        $departments = [
            'IT Department',
            'HR Department', 
            'Finance Department',
            'Operations Department',
            'Marketing Department',
            'Engineering Department'
        ];

        return view('admin.requests.edit', compact('request', 'items', 'departments'));
    }

    public function update(Request $updateRequest, SupplyRequest $request)
    {
        // Check permissions
        /** @var User $user */
        $user = Auth::user();
        if (!$request->isPending() || ($user->role !== 'admin' && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be updated.');
        }

        $validatedData = $updateRequest->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'department' => 'required|string|max:100',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        // Check stock availability
        $item = Item::findOrFail($validatedData['item_id']);
        if ($item->current_stock < $validatedData['quantity']) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->current_stock . ' available)']);
        }

        $request->update($validatedData);

        // Create log entry
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'description' => 'Updated request for ' . $item->name,
            'timestamp' => now(),
        ]);

        return redirect()->route('requests.show', $request)
            ->with('success', 'Request updated successfully!');
    }

    public function approveByOfficeHead(Request $request, SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'office_head' && $user->role !== 'admin') {
            abort(403, 'Only office heads can approve requests at this stage.');
        }

        if (!$supplyRequest->canBeApprovedByOfficeHead()) {
            return back()->withErrors(['error' => 'This request cannot be approved at this stage.']);
        }

        $validatedData = $request->validate([
            'office_head_notes' => 'nullable|string|max:500',
        ]);

        $supplyRequest->approveByOfficeHead(Auth::user(), $validatedData['office_head_notes'] ?? null);

        // Create log entry
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'approve',
            'description' => 'Approved request by office head for ' . $supplyRequest->item->name,
            'timestamp' => now(),
        ]);

        return back()->with('success', 'Request approved by office head successfully!');
    }

    public function approveByAdmin(SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can approve requests at this stage.');
        }

        if (!$supplyRequest->canBeApprovedByAdmin()) {
            return back()->withErrors(['error' => 'This request cannot be approved at this stage.']);
        }

        $supplyRequest->approveByAdmin(Auth::user());

        // Create log entry
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'approve',
            'description' => 'Approved request by admin for ' . $supplyRequest->item->name,
            'timestamp' => now(),
        ]);

        return back()->with('success', 'Request approved by administrator successfully!');
    }

    public function fulfill(SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can fulfill requests.');
        }

        if (!$supplyRequest->canBeFulfilled()) {
            return back()->withErrors(['error' => 'This request cannot be fulfilled at this stage.']);
        }

        // Check stock availability one more time
        if ($supplyRequest->item->current_stock < $supplyRequest->quantity) {
            return back()->withErrors(['error' => 'Insufficient stock to fulfill this request.']);
        }

        DB::beginTransaction();
        try {
            $supplyRequest->fulfill(Auth::user());

            // Create log entry
            Log::create([
                'user_id' => Auth::id(),
                'action' => 'fulfill',
                'description' => 'Fulfilled request for ' . $supplyRequest->quantity . ' ' . $supplyRequest->item->name . '. Claim slip: ' . $supplyRequest->claim_slip_number,
                'timestamp' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Request fulfilled successfully! Claim slip number: ' . $supplyRequest->claim_slip_number);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to fulfill request: ' . $e->getMessage()]);
        }
    }

    public function markAsClaimed(SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can mark requests as claimed.');
        }

        if (!$supplyRequest->canBeClaimed()) {
            return back()->withErrors(['error' => 'This request cannot be marked as claimed.']);
        }

        $supplyRequest->markAsClaimed(Auth::user());

        // Create log entry
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'claim',
            'description' => 'Marked request as claimed for ' . $supplyRequest->item->name,
            'timestamp' => now(),
        ]);

        return back()->with('success', 'Request marked as claimed successfully!');
    }

    public function decline(Request $request, SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($user->role !== 'office_head' && $user->role !== 'admin') {
            abort(403, 'Only office heads and administrators can decline requests.');
        }

        // Check if request can be declined
        if ($supplyRequest->isDeclined() || $supplyRequest->isClaimed() || $supplyRequest->isFulfilled()) {
            return back()->withErrors(['error' => 'This request cannot be declined at this stage.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $supplyRequest->decline(Auth::user(), $validatedData['reason']);

        // Create log entry
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'decline',
            'description' => 'Declined request for ' . $supplyRequest->item->name . '. Reason: ' . $validatedData['reason'],
            'timestamp' => now(),
        ]);

        return back()->with('success', 'Request declined successfully!');
    }

    public function destroy(SupplyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Only allow deletion if request is pending and user is the requester or admin
        if (!$request->isPending() || ($user->role !== 'admin' && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be deleted.');
        }

        // Create log entry before deletion
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'description' => 'Deleted request for ' . $request->item->name,
            'timestamp' => now(),
        ]);

        $request->delete();

        return redirect()->route('requests.index')
            ->with('success', 'Request deleted successfully!');
    }

    public function printClaimSlip(SupplyRequest $request)
    {
        if (!$request->isFulfilled() && !$request->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        return view('admin.requests.claim-slip', compact('request'));
    }

    // Legacy methods for backwards compatibility
    public function manage(Request $request)
    {
        return $this->index($request);
    }

    public function myRequests(Request $request)
    {
        return $this->index($request);
    }

    public function updateStatus(Request $httpRequest, SupplyRequest $request)
    {
        // Legacy method - redirect to appropriate new workflow methods
        $status = $httpRequest->input('status');
        
        /** @var User $user */
        $user = Auth::user();
        
        if ($status === 'approved') {
            if ($request->canBeApprovedByOfficeHead() && $user->role === 'office_head') {
                return $this->approveByOfficeHead($httpRequest, $request);
            } elseif ($request->canBeApprovedByAdmin() && $user->role === 'admin') {
                return $this->approveByAdmin($request);
            }
        } elseif ($status === 'rejected') {
            return $this->decline($httpRequest, $request);
        }

        return back()->withErrors(['error' => 'Invalid status update.']);
    }
}
