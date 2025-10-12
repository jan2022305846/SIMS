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
        $query = SupplyRequest::with(['user', 'item', 'adminApprover'])
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
                $query->where('status', 'declined');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(15);

        return view('admin.requests.index', compact('requests'));
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
        Log::info('Faculty request submission attempt', [
            'user_id' => Auth::id(),
            'user_is_admin' => $currentUser->isAdmin(),
            'method' => $request->method(),
            'all_data' => $request->all(),
            'has_csrf' => $request->has('_token'),
            'csrf_token' => $request->input('_token') ? 'present' : 'missing',
            'session_id' => $request->session()->getId(),
        ]);

        $validatedData = $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:consumable,non_consumable',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'nullable|exists:offices,id', // Made nullable since it's auto-populated for faculty
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx', // 5MB max per file
        ]);

        Log::info('Validation passed', ['validated_data' => $validatedData]);

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
            Log::warning('Stock validation failed', [
                'item_id' => $item->id,
                'requested' => $validatedData['quantity'],
                'available' => $item->quantity
            ]);
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->quantity . ' available)']);
        }

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

            Log::info('Creating supply request record', [
                'user_id' => Auth::id(),
                'item_id' => $validatedData['item_id'],
                'quantity' => $validatedData['quantity']
            ]);
            $supplyRequest = SupplyRequest::create([
                'user_id' => Auth::id(),
                'item_id' => $validatedData['item_id'],
                'item_type' => $item instanceof Consumable ? 'consumable' : 'non_consumable',
                'quantity' => $validatedData['quantity'],
                'purpose' => $validatedData['purpose'],
                'needed_date' => $validatedData['needed_date'],
                'office_id' => $validatedData['office_id'] ?? Auth::user()->office_id,
                'priority' => $validatedData['priority'],
                'status' => 'pending',
                'attachments' => $attachments,
            ]);
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
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('Database query error during request creation', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Database error occurred. Please try again.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Unexpected error during request creation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'validated_data' => $validatedData
            ]);
            return back()->withErrors(['error' => 'Failed to submit request: ' . $e->getMessage()]);
        }
    }

    public function show(SupplyRequest $supplyRequest)
    {
        $supplyRequest->load(['user', 'item', 'adminApprover', 'office']);

        // Check permissions
        /** @var User $user */
        $user = Auth::user();

        // Check if user is authenticated and has permission
        if (!$user || (!$user->isAdmin() && $supplyRequest->user_id !== $user->id)) {
            abort(403, 'Unauthorized access to this request.');
        }

        // Return appropriate view based on user role
        if (!$user->isAdmin()) {
            return view('faculty.requests.show', compact('supplyRequest'));
        }

        return view('admin.requests.show', ['supplyRequest' => $supplyRequest]);
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

        return view('admin.requests.edit', compact('supplyRequest', 'items', 'offices'));
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

    public function approveByAdmin($requestId)
    {
        // Manual model loading since route model binding had issues
        $supplyRequest = SupplyRequest::find($requestId);

        if (!$supplyRequest) {
            return back()->withErrors(['error' => 'Request not found.']);
        }

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

    public function markAsClaimed(Request $httpRequest, $requestId)
    {
        // Manual model loading since route model binding had issues
        $supplyRequest = SupplyRequest::find($requestId);

        if (!$supplyRequest) {
            return back()->withErrors(['error' => 'Request not found.']);
        }

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

    public function decline(Request $request, SupplyRequest $supplyRequest)
    {
        Log::info('Decline method called', [
            'request_id' => $supplyRequest->id,
            'user_id' => Auth::id(),
            'form_data' => $request->all()
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

        $validatedData = $request->validate([
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

    public function destroy($requestId)
    {
        // Manual model loading since route model binding had issues
        $supplyRequest = SupplyRequest::find($requestId);

        if (!$supplyRequest) {
            return back()->withErrors(['error' => 'Request not found.']);
        }

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
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can view claim slips
        if (!$user || !($user->isAdmin() || $supplyRequest->user_id === $user->id)) {
            abort(403, 'You are not authorized to view claim slips for this request.');
        }

        if (!$supplyRequest->isApprovedByAdmin() && !$supplyRequest->isReadyForPickup() && !$supplyRequest->isFulfilled() && !$supplyRequest->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        // Generate QR code for the claim slip number
        $qrCode = new \chillerlan\QRCode\QRCode();
        $qrCodeData = $supplyRequest->claim_slip_number;
        $qrCodeImage = $qrCode->render($qrCodeData);

        return view('admin.requests.claim-slip', compact('supplyRequest', 'qrCodeImage'));
    }

    // Legacy methods for backwards compatibility
    public function manage(Request $request)
    {
        return $this->index($request);
    }

    public function myRequests(Request $request)
    {
        $query = SupplyRequest::with(['user', 'item', 'adminApprover'])
            ->latest();

        // Faculty can only see their own requests
        $query->where('user_id', Auth::id());

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('item', function($itemQuery) use ($searchTerm) {
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

    public function downloadClaimSlip(SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can download claim slips for fulfilled/claimed requests
        if (!$user || !($user->isAdmin() || $supplyRequest->user_id === $user->id)) {
            abort(403, 'You are not authorized to download claim slips for this request.');
        }

        if (!$supplyRequest->isApprovedByAdmin() && !$supplyRequest->isReadyForPickup() && !$supplyRequest->isFulfilled() && !$supplyRequest->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        // Generate QR code for the claim slip number
        $qrCode = new \chillerlan\QRCode\QRCode();
        $qrCodeData = $supplyRequest->claim_slip_number;
        $qrCodeImage = $qrCode->render($qrCodeData);

        // Generate PDF with QR code
        $pdf = Pdf::loadView('admin.requests.claim-slip-pdf', compact('supplyRequest', 'qrCodeImage'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('claim-slip-' . $supplyRequest->claim_slip_number . '.pdf');
    }
}
