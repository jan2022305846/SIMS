<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as SupplyRequest;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Log as ActivityLog;
use App\Models\ActivityLog as RequestActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Request Controller for managing supply requests
 * 
 * @method User user() Get the authenticated user with proper type hints
 */

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyRequest::with(['user', 'adminApprover', 'requestItems.itemable'])
            ->latest();

        // Filter based on user role
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            // Faculty can only see their own requests
            $query->where('user_id', $user->id);
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
                ->orWhereHas('requestItems.itemable', function($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%");
                })
                ->orWhere('purpose', 'like', "%{$searchTerm}%")
                ->orWhere('claim_slip_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter with support for declined status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'declined') {
                $query->where('status', 'declined');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Apply office filter
        if ($request->has('office') && $request->office) {
            $query->where('office_id', $request->office);
        }

        $requests = $query->paginate(15);
        $offices = \App\Models\Office::orderBy('name')->get();

        return view('admin.requests.index', compact('requests', 'offices'));
    }

    public function create()
    {
        $consumables = Consumable::where('quantity', '>', 0)->get();
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get();
        $items = $consumables->concat($nonConsumables);

        return view('faculty.requests.create', compact('items'));
    }

    public function store(Request $request)
    {
        // Debug logging
        /** @var User $currentUser */
        $currentUser = Auth::user();
        Log::info('Faculty bulk request submission attempt', [
            'user_id' => Auth::id(),
            'user_is_admin' => $currentUser->isAdmin(),
            'method' => $request->method(),
            'all_data' => $request->all(),
            'has_csrf' => $request->has('_token'),
            'csrf_token' => $request->input('_token') ? 'present' : 'missing',
            'session_id' => $request->session()->getId(),
        ]);

        $validatedData = $request->validate([
            'request_type' => 'required|in:single,bulk',
            'items' => 'required_if:request_type,bulk|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.item_type' => 'required|in:consumable,non_consumable',
            'items.*.quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'nullable|exists:offices,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            // Legacy single item fields for backward compatibility
            'item_id' => 'required_if:request_type,single|integer',
            'item_type' => 'required_if:request_type,single|in:consumable,non_consumable',
            'quantity' => 'required_if:request_type,single|integer|min:1',
        ]);

        Log::info('Validation passed', ['validated_data' => $validatedData]);

        DB::beginTransaction();
        try {
            Log::info('Starting database transaction');

            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                Log::info('Processing file uploads', ['file_count' => count($request->file('attachments'))]);
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
                Log::info('File uploads completed', ['attachments_count' => count($attachments)]);
            }

            // Create the main request
            $supplyRequest = SupplyRequest::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'purpose' => $validatedData['purpose'],
                'needed_date' => $validatedData['needed_date'],
                'office_id' => $validatedData['office_id'] ?? Auth::user()->office_id,
                'priority' => $validatedData['priority'],
                'attachments' => $attachments,
            ]);

            // Handle items based on request type
            if ($validatedData['request_type'] === 'bulk') {
                // Create request items for bulk request
                foreach ($validatedData['items'] as $itemData) {
                    // Validate stock availability for consumables
                    $item = null;
                    if ($itemData['item_type'] === 'consumable') {
                        $item = Consumable::findOrFail($itemData['item_id']);
                        if ($item->quantity < $itemData['quantity']) {
                            throw new \Exception("Insufficient stock for {$item->name}. Available: {$item->quantity}, Requested: {$itemData['quantity']}");
                        }
                    } elseif ($itemData['item_type'] === 'non_consumable') {
                        $item = NonConsumable::findOrFail($itemData['item_id']);
                    }

                    $supplyRequest->requestItems()->create([
                        'item_id' => $itemData['item_id'],
                        'item_type' => $itemData['item_type'],
                        'quantity' => $itemData['quantity'],
                        'status' => 'available',
                    ]);
                }
            } else {
                // Legacy single item request
                $item = null;
                if ($validatedData['item_type'] === 'consumable') {
                    $item = Consumable::findOrFail($validatedData['item_id']);
                } elseif ($validatedData['item_type'] === 'non_consumable') {
                    $item = NonConsumable::findOrFail($validatedData['item_id']);
                }

                // Check stock availability (only for consumables)
                if ($validatedData['item_type'] === 'consumable' && $item->quantity < $validatedData['quantity']) {
                    Log::warning('Stock validation failed', [
                        'item_id' => $item->id,
                        'requested' => $validatedData['quantity'],
                        'available' => $item->quantity
                    ]);
                    throw new \Exception("Requested quantity exceeds available stock ({$item->quantity} available)");
                }

                $supplyRequest->requestItems()->create([
                    'item_id' => $validatedData['item_id'],
                    'item_type' => $validatedData['item_type'],
                    'quantity' => $validatedData['quantity'],
                    'status' => 'available',
                ]);
            }

            Log::info('Supply request created successfully', ['request_id' => $supplyRequest->id]);

            DB::commit();
            Log::info('Transaction committed successfully');

            // Notify admin about new pending request
            \App\Services\NotificationService::notifyNewPendingRequest($supplyRequest);

            return redirect()->route('faculty.requests.show', $supplyRequest)
                ->with('success', 'Request submitted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            Log::error('Model not found during request creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Required data not found. Please try again.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Unexpected error during request creation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData
            ]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(SupplyRequest $supplyRequest)
    {
        $supplyRequest->load(['user', 'item', 'adminApprover', 'office', 'requestItems.itemable']);

        // Check permissions
        /** @var User $user */
        $user = Auth::user();

        // Check if user is authenticated and has permission
        if (!$user || (!$user->isAdmin() && $supplyRequest->user_id !== $user->id)) {
            abort(403, 'Unauthorized access to this request.');
        }

        // Return appropriate view based on user role
        if (!$user->isAdmin()) {
            return view('faculty.requests.show', ['request' => $supplyRequest]);
        }

        return view('admin.requests.show', ['request' => $supplyRequest]);
    }

    public function edit(SupplyRequest $supplyRequest)
    {
        // Only allow editing if request is still pending and user is the requester or admin
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$supplyRequest->isPending() || (!$user->isAdmin() && $supplyRequest->user_id !== $user->id)) {
            abort(403, 'This request cannot be edited.');
        }

        $consumables = Consumable::where('quantity', '>', 0)->get();
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get();
        $items = $consumables->concat($nonConsumables);
        $offices = \App\Models\Office::all();

        return view('admin.requests.edit', ['request' => $supplyRequest, 'items' => $items, 'offices' => $offices]);
    }

    public function update(Request $updateRequest, SupplyRequest $supplyRequest)
    {
        // Check permissions
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$supplyRequest->isPending() || (!$user->isAdmin() && $supplyRequest->user_id !== $user->id)) {
            abort(403, 'This request cannot be updated.');
        }

        $validatedData = $updateRequest->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:consumable,non_consumable',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'required|exists:offices,id',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        // Check stock availability
        $item = null;
        if ($validatedData['item_type'] === 'consumable') {
            $item = Consumable::findOrFail($validatedData['item_id']);
        } elseif ($validatedData['item_type'] === 'non_consumable') {
            $item = NonConsumable::findOrFail($validatedData['item_id']);
        }

        if (!$item) {
            return back()->withErrors(['item_id' => 'Selected item not found.']);
        }

        // Check stock availability (only for consumables)
        if ($validatedData['item_type'] === 'consumable' && $item->quantity < $validatedData['quantity']) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->quantity . ' available)']);
        }

        $supplyRequest->update($validatedData);

        return redirect()->route('requests.show', $supplyRequest)
            ->with('success', 'Request updated successfully!');
    }

    public function approveByAdmin(SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can approve requests.');
        }

        if (!$supplyRequest->canBeApprovedByAdmin()) {
            return back()->withErrors(['error' => 'This request cannot be approved at this stage.']);
        }

        $supplyRequest->approveByAdmin(Auth::user());

        return back()->with('success', 'Request approved by administrator successfully!');
    }

    public function fulfill(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can fulfill requests.');
        }

        if (!$supplyRequest->canBeFulfilled()) {
            return back()->withErrors(['error' => 'This request cannot be fulfilled at this stage.']);
        }

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        // Check stock availability one more time with null check
        if (!$supplyRequest->item || $supplyRequest->item->quantity < $supplyRequest->quantity) {
            return back()->withErrors(['error' => 'Insufficient stock to fulfill this request or item not found.']);
        }

        // Verify scanned barcode if provided
        if ($httpRequest->filled('scanned_barcode')) {
            $scannedBarcode = $httpRequest->scanned_barcode;
            
            // Check if the scanned barcode matches the requested item
            $scannedItem = Consumable::where('product_code', $scannedBarcode)->first() 
                ?? NonConsumable::where('product_code', $scannedBarcode)->first();

            if (!$scannedItem) {
                return back()->withErrors(['error' => 'Scanned barcode does not match any item in the system.']);
            }

            if ($scannedItem->id !== $supplyRequest->item->id) {
                return back()->withErrors(['error' => 'Scanned item does not match the requested item. Please scan the correct item.']);
            }
        }

        DB::beginTransaction();
        try {
            $supplyRequest->fulfill(Auth::user());

            DB::commit();

            return back()->with('success', 'Request fulfilled successfully! Claim slip number: ' . $supplyRequest->claim_slip_number);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to fulfill request: ' . $e->getMessage()]);
        }
    }

    public function markAsClaimed(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can mark requests as claimed.');
        }

        if (!$supplyRequest->canBeClaimed()) {
            return back()->withErrors(['error' => 'This request cannot be marked as claimed.']);
        }

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        // Check stock availability one more time (only for consumables)
        if ($supplyRequest->item_type === 'consumable') {
            if (!$supplyRequest->item || $supplyRequest->item->quantity < $supplyRequest->quantity) {
                return back()->withErrors(['error' => 'Insufficient stock to fulfill this request.']);
            }
        }

        // Verify scanned barcode if provided
        if ($httpRequest->filled('scanned_barcode')) {
            $scannedBarcode = $httpRequest->scanned_barcode;
            
            // Check if the scanned barcode matches the requested item
            $scannedItem = Consumable::where('product_code', $scannedBarcode)->first() 
                ?? NonConsumable::where('product_code', $scannedBarcode)->first();

            if (!$scannedItem) {
                return back()->withErrors(['error' => 'Scanned barcode does not match any item in the system.']);
            }

            if ($scannedItem->id !== $supplyRequest->item->id) {
                return back()->withErrors(['error' => 'Scanned item does not match the requested item. Please scan the correct item.']);
            }
        }

        DB::beginTransaction();
        try {
            $supplyRequest->markAsClaimed(Auth::user());

            DB::commit();

            $stockMessage = $supplyRequest->item_type === 'consumable' ? ' Stock has been updated.' : '';
            return back()->with('success', 'Request marked as claimed successfully!' . $stockMessage);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to mark request as claimed: ' . $e->getMessage()]);
        }
    }

    public function completeAndClaim(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can complete requests.');
        }

        if ($supplyRequest->status !== 'approved_by_admin') {
            return back()->withErrors(['error' => 'This request cannot be completed at this stage.']);
        }

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        // Check stock availability one more time
        if (!$supplyRequest->item || $supplyRequest->item->current_stock < $supplyRequest->quantity) {
            return back()->withErrors(['error' => 'Insufficient stock to complete this request.']);
        }

        // Verify scanned barcode if provided
        if ($httpRequest->filled('scanned_barcode')) {
            $scannedBarcode = $httpRequest->scanned_barcode;

            // Check if the scanned barcode matches the requested item
            $scannedItem = Consumable::where('product_code', $scannedBarcode)->first() 
                ?? NonConsumable::where('product_code', $scannedBarcode)->first();

            if (!$scannedItem) {
                return back()->withErrors(['error' => 'Scanned barcode does not match any item in the system.']);
            }

            if ($scannedItem->id !== $supplyRequest->item->id) {
                return back()->withErrors(['error' => 'Scanned item does not match the requested item. Please scan the correct item.']);
            }
        }

        DB::beginTransaction();
        try {
            // Generate claim slip number
            $claimSlipNumber = 'CS-' . date('Y') . '-' . str_pad($supplyRequest->id, 6, '0', STR_PAD_LEFT);

            // Complete the request in one step: fulfill and claim
            $supplyRequest->update([
                'status' => 'claimed',
                'claim_slip_number' => $claimSlipNumber,
            ]);

            // Update item stock (reduce stock when completing the request)
            if ($supplyRequest->item_type === 'consumable') {
                $supplyRequest->item->quantity -= $supplyRequest->quantity;
                $supplyRequest->item->save();
            }

            DB::commit();

            return back()->with('success', 'Request completed and claimed successfully! Claim slip: ' . $claimSlipNumber);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to complete request: ' . $e->getMessage()]);
        }
    }

    public function decline(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        Log::info('Decline method called', [
            'request_id' => $supplyRequest->id,
            'user_id' => Auth::id(),
            'form_data' => $httpRequest->all()
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only administrators can decline requests.');
        }

        // Check if request can be declined
        if ($supplyRequest->isDeclined() || $supplyRequest->isClaimed() || $supplyRequest->isFulfilled()) {
            return back()->withErrors(['error' => 'This request cannot be declined at this stage.']);
        }

        $validatedData = $httpRequest->validate([
            'reason' => 'required|string|max:500',
        ]);

        Log::info('Declining request', [
            'request_id' => $supplyRequest->id,
            'current_status' => $supplyRequest->status,
            'reason' => $validatedData['reason']
        ]);

        $supplyRequest->load('user');
        $supplyRequest->decline(Auth::user(), $validatedData['reason']);

        // Check if the status was actually updated
        $supplyRequest->refresh();
        Log::info('Request status after decline', [
            'request_id' => $supplyRequest->id,
            'new_status' => $supplyRequest->status,
            'notes' => $supplyRequest->notes
        ]);

        return redirect()->route('requests.manage')->with('success', 'Request declined successfully!');
    }

    public function destroy(SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();

        // Only allow deletion if request is pending and user is the requester or admin
        if (!$user || !$supplyRequest->isPending() || (!$user->isAdmin() && $supplyRequest->user_id !== $user->id)) {
            abort(403, 'This request cannot be deleted.');
        }

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        $supplyRequest->delete();

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->route('requests.manage')
                ->with('success', 'Request deleted successfully!');
        } else {
            return redirect()->route('faculty.requests.index')
                ->with('success', 'Request deleted successfully!');
        }
    }

    public function printClaimSlip(SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can view claim slips
        if (!$user || !($user->isAdmin() || $supplyRequest->user_id === $user->id)) {
            abort(403, 'You are not authorized to view claim slips for this request.');
        }

        if (!$supplyRequest->isApprovedByAdmin() && !$supplyRequest->isReadyForPickup() && !$supplyRequest->isFulfilled() && !$supplyRequest->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        // Generate secure QR code data with multiple verification points
        $qrCodeData = [
            'type' => 'claim_slip',
            'claim_slip_number' => $supplyRequest->claim_slip_number,
            'request_id' => $supplyRequest->id,
            'user_id' => $supplyRequest->user_id,
            'timestamp' => $supplyRequest->updated_at->timestamp,
            'hash' => hash('sha256', $supplyRequest->claim_slip_number . $supplyRequest->id . $supplyRequest->user_id . config('app.key'))
        ];

        $qrCode = new \chillerlan\QRCode\QRCode();
        $qrCodeImage = $qrCode->render(json_encode($qrCodeData));

        return view('admin.requests.claim-slip', ['request' => $supplyRequest, 'qrCodeImage' => $qrCodeImage]);
    }

    // Legacy methods for backwards compatibility
    public function manage(Request $request)
    {
        return $this->index($request);
    }

    public function myRequests(Request $request)
    {
        $query = SupplyRequest::with(['user', 'adminApprover', 'requestItems.itemable'])
            ->latest();

        // Faculty can only see their own requests
        $query->where('user_id', Auth::id());

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('requestItems.itemable', function($itemQuery) use ($searchTerm) {
                    $itemQuery->where('name', 'like', "%{$searchTerm}%");
                })
                ->orWhere('purpose', 'like', "%{$searchTerm}%")
                ->orWhere('claim_slip_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            if ($request->status === 'declined') {
                $query->where('status', 'declined');
            } else {
                $query->where('status', $request->status);
            }
        }

        $requests = $query->paginate(15);

        return view('faculty.requests.index', compact('requests'));
    }

    public function generateClaimSlip(SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();
        
        // Only faculty can generate claim slips for their own approved requests
        if (!$user || ($user->isAdmin() || $supplyRequest->user_id !== $user->id)) {
            abort(403, 'You are not authorized to generate claim slips for this request.');
        }

        if (!$supplyRequest->canGenerateClaimSlip()) {
            return back()->withErrors(['error' => 'This request is not eligible for claim slip generation.']);
        }

        $supplyRequest->generateClaimSlip();

        // Redirect back to request details page
        return redirect()->route('faculty.requests.show', $supplyRequest)->with('success', 'Claim slip generated successfully! You can now print it and pick up your items from the supply office.');
    }

    public function editFaculty(SupplyRequest $supplyRequest)
    {
        // Only allow editing if request is still pending and belongs to the current faculty user
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$supplyRequest->isPending() || $supplyRequest->user_id !== $user->id) {
            abort(403, 'You can only edit your own pending requests.');
        }

        $consumables = Consumable::where('quantity', '>', 0)->get();
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get();
        $items = $consumables->concat($nonConsumables);

        return view('faculty.requests.edit', ['request' => $supplyRequest, 'items' => $items]);
    }

    public function updateFaculty(Request $updateRequest, SupplyRequest $supplyRequest)
    {
        // Check permissions - only faculty can update their own pending requests
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$supplyRequest->isPending() || $supplyRequest->user_id !== $user->id) {
            abort(403, 'You can only update your own pending requests.');
        }

        $validatedData = $updateRequest->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:consumable,non_consumable',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
        ]);

        // Check stock availability
        $item = null;
        if ($validatedData['item_type'] === 'consumable') {
            $item = Consumable::findOrFail($validatedData['item_id']);
        } elseif ($validatedData['item_type'] === 'non_consumable') {
            $item = NonConsumable::findOrFail($validatedData['item_id']);
        }

        if (!$item) {
            return back()->withErrors(['item_id' => 'Selected item not found.']);
        }

        // Check stock availability (only for consumables)
        if ($validatedData['item_type'] === 'consumable' && $item->quantity < $validatedData['quantity']) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->quantity . ' available)']);
        }

        $supplyRequest->update([
            'item_id' => $validatedData['item_id'],
            'item_type' => $item instanceof Consumable ? 'consumable' : 'non_consumable',
            'quantity' => $validatedData['quantity'],
            'purpose' => $validatedData['purpose'],
        ]);

        return redirect()->route('faculty.requests.show', $supplyRequest)
            ->with('success', 'Request updated successfully!');
    }

    public function cancelFaculty(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        // Check permissions - only faculty can cancel their own pending requests
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$supplyRequest->isPending() || $supplyRequest->user_id !== $user->id) {
            abort(403, 'You can only cancel your own pending requests.');
        }

        $validatedData = $httpRequest->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        Log::info('Cancelling faculty request', [
            'request_id' => $supplyRequest->id,
            'current_status' => $supplyRequest->status,
            'reason' => $validatedData['reason'] ?? null
        ]);

        $supplyRequest->load('user');
        $supplyRequest->cancel($user, $validatedData['reason'] ?? null);

        // Check if the status was actually updated
        $supplyRequest->refresh();
        Log::info('Request status after cancellation', [
            'request_id' => $supplyRequest->id,
            'new_status' => $supplyRequest->status,
            'notes' => $supplyRequest->notes
        ]);

        return redirect()->route('faculty.requests.index')->with('success', 'Request cancelled successfully!');
    }

    public function verifyClaimSlipQR(Request $httpRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validatedData = $httpRequest->validate([
            'qr_data' => 'required|string',
            'request_id' => 'required|integer|exists:requests,id',
        ]);

        try {
            // Parse QR code data
            $qrData = json_decode($validatedData['qr_data'], true);
            
            if (!$qrData || !isset($qrData['type']) || $qrData['type'] !== 'claim_slip') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            // Verify required fields
            $requiredFields = ['claim_slip_number', 'request_id', 'user_id', 'timestamp', 'hash'];
            foreach ($requiredFields as $field) {
                if (!isset($qrData[$field])) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'QR code missing required verification data'
                    ], 400);
                }
            }

            // Verify hash
            $expectedHash = hash('sha256', 
                $qrData['claim_slip_number'] . 
                $qrData['request_id'] . 
                $qrData['user_id'] . 
                config('app.key')
            );

            if (!hash_equals($expectedHash, $qrData['hash'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'QR code verification failed - invalid or tampered data'
                ], 400);
            }

            // Check if QR data matches the current request
            $request = SupplyRequest::findOrFail($validatedData['request_id']);
            
            if ($qrData['claim_slip_number'] !== $request->claim_slip_number ||
                $qrData['request_id'] != $request->id ||
                $qrData['user_id'] != $request->user_id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'QR code does not match this request'
                ], 400);
            }

            // Check if QR code is not too old (optional - prevent replay attacks)
            $qrTimestamp = $qrData['timestamp'];
            $currentTimestamp = now()->timestamp;
            $maxAge = 24 * 60 * 60; // 24 hours
            
            if (($currentTimestamp - $qrTimestamp) > $maxAge) {
                return response()->json([
                    'success' => false, 
                    'message' => 'QR code has expired. Please generate a new claim slip.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Claim slip verified successfully',
                'data' => [
                    'claim_slip_number' => $request->claim_slip_number,
                    'request_id' => $request->id,
                    'user_name' => $request->user->name ?? 'Unknown User',
                    'department' => $request->department ?? 'N/A',
                    'items_count' => $request->request_items ? $request->request_items->count() : 1,
                    'total_quantity' => $request->request_items ? $request->request_items->sum('quantity') : $request->quantity,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('QR code verification error', [
                'error' => $e->getMessage(),
                'qr_data' => $validatedData['qr_data'],
                'request_id' => $validatedData['request_id']
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Failed to verify QR code'
            ], 500);
        }
    }
}
