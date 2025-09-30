<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as SupplyRequest;
use App\Models\Item;
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
        Log::info('Faculty request submission attempt', [
            'user_id' => Auth::id(),
            'user_is_admin' => Auth::user()->isAdmin(),
            'method' => $request->method(),
            'all_data' => $request->all(),
            'has_csrf' => $request->has('_token'),
            'csrf_token' => $request->input('_token') ? 'present' : 'missing',
            'session_id' => $request->session()->getId(),
        ]);

        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'nullable|exists:offices,id', // Made nullable since it's auto-populated for faculty
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx', // 5MB max per file
        ]);

        Log::info('Validation passed', ['validated_data' => $validatedData]);

        // Check stock availability
        $item = Item::findOrFail($validatedData['item_id']);
        if ($item->quantity < $validatedData['quantity']) {
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
                'item_type' => $item instanceof \App\Models\Consumable ? 'consumable' : 'non_consumable',
                'quantity' => $validatedData['quantity'],
                'purpose' => $validatedData['purpose'],
                'needed_date' => $validatedData['needed_date'],
                'office_id' => $validatedData['office_id'] ?? Auth::user()->office_id,
                'priority' => $validatedData['priority'],
                'status' => 'pending',
                'attachments' => $attachments,
            ]);
            Log::info('Supply request created successfully', ['request_id' => $supplyRequest->id]);

            // Create log entry
            Log::info('Creating activity log entry');
            RequestActivityLog::logRequestActivity(
                'Created request for {subject}',
                $supplyRequest,
                'created',
                [
                    'quantity' => $validatedData['quantity'],
                    'item_name' => $item->name,
                    'priority' => $validatedData['priority'],
                    'purpose' => $validatedData['purpose']
                ]
            );
            Log::info('Activity log created successfully');

            DB::commit();
            Log::info('Transaction committed successfully');

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

    public function show(SupplyRequest $request)
    {
        $request->load(['user', 'item', 'adminApprover', 'office']);

        // Check permissions
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin() && $request->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this request.');
        }

        // Return appropriate view based on user role
        if (!$user->isAdmin()) {
            return view('faculty.requests.show', compact('request'));
        }

        return view('admin.requests.show', compact('request'));
    }

    public function edit(SupplyRequest $request)
    {
        // Only allow editing if request is still pending and user is the requester or admin
        /** @var User $user */
        $user = Auth::user();
        if (!$request->isPending() || (!$user->isAdmin() && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be edited.');
        }

        $consumables = Consumable::where('quantity', '>', 0)->get();
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get();
        $items = $consumables->concat($nonConsumables);
        $offices = \App\Models\Office::all();

        return view('admin.requests.edit', compact('request', 'items', 'offices'));
    }

    public function update(Request $updateRequest, SupplyRequest $request)
    {
        // Check permissions
        /** @var User $user */
        $user = Auth::user();
        if (!$request->isPending() || (!$user->isAdmin() && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be updated.');
        }

        $validatedData = $updateRequest->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'required|exists:offices,id',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        // Check stock availability
        $item = Item::findOrFail($validatedData['item_id']);
        if ($item->quantity < $validatedData['quantity']) {
            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock (' . $item->quantity . ' available)']);
        }

        $request->update($validatedData);

        // Create log entry
        RequestActivityLog::logRequestActivity(
            'Updated request for {subject}',
            $request,
            'updated',
            ['item_name' => $item->name]
        );

        return redirect()->route('requests.show', $request)
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
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can approve requests.');
        }

        if (!$supplyRequest->canBeApprovedByAdmin()) {
            return back()->withErrors(['error' => 'This request cannot be approved at this stage.']);
        }

        $supplyRequest->approveByAdmin(Auth::user());

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        // Create log entry with null check
        $itemName = $supplyRequest->item ? $supplyRequest->item->name : 'Unknown Item';
        RequestActivityLog::logRequestActivity(
            'Approved request by admin for {subject}',
            $supplyRequest,
            'approved',
            ['item_name' => $itemName]
        );

        return back()->with('success', 'Request approved by administrator successfully!');
    }

    public function fulfill(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
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
            $scannedItem = Item::where('barcode', $scannedBarcode)
                ->orWhere('qr_code', $scannedBarcode)
                ->first();

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

            // Load item relationship if not already loaded
            if (!$supplyRequest->relationLoaded('item')) {
                $supplyRequest->load('item');
            }

            // Create log entry with null check
            $itemName = $supplyRequest->item ? $supplyRequest->item->name : 'Unknown Item';
            $logDetails = 'Fulfilled request for ' . $supplyRequest->quantity . ' ' . $itemName . '. Claim slip: ' . $supplyRequest->claim_slip_number;
            
            if ($httpRequest->filled('scanned_barcode')) {
                $logDetails .= ' (Verified with barcode scan)';
            }
            
            RequestActivityLog::logRequestActivity(
                $logDetails,
                $supplyRequest,
                'fulfilled',
                [
                    'quantity' => $supplyRequest->quantity,
                    'item_name' => $itemName,
                    'claim_slip_number' => $supplyRequest->claim_slip_number,
                    'scanned_barcode' => $httpRequest->filled('scanned_barcode')
                ]
            );

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
        if (!$user->isAdmin()) {
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
            $scannedItem = Item::where('barcode', $scannedBarcode)
                ->orWhere('qr_code', $scannedBarcode)
                ->first();

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

            // Create log entry with null check
            $itemName = $supplyRequest->item ? $supplyRequest->item->name : 'Unknown Item';
            $logDetails = 'Marked request as claimed for ' . $supplyRequest->quantity . ' ' . $itemName . '. Claim slip: ' . $supplyRequest->claim_slip_number;
            
            if ($httpRequest->filled('scanned_barcode')) {
                $logDetails .= ' (Verified with barcode scan)';
            }
            
            RequestActivityLog::logRequestActivity(
                $logDetails,
                $supplyRequest,
                'claimed',
                [
                    'quantity' => $supplyRequest->quantity,
                    'item_name' => $itemName,
                    'claim_slip_number' => $supplyRequest->claim_slip_number,
                    'scanned_barcode' => $httpRequest->filled('scanned_barcode')
                ]
            );

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
        if (!$user->isAdmin()) {
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
            $scannedItem = Item::where('barcode', $scannedBarcode)
                ->orWhere('qr_code', $scannedBarcode)
                ->first();

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

            // Create log entry with null check
            $itemName = $supplyRequest->item ? $supplyRequest->item->name : 'Unknown Item';
            $logDetails = 'Completed and claimed request for ' . $supplyRequest->quantity . ' ' . $itemName . '. Claim slip: ' . $claimSlipNumber;

            if ($httpRequest->filled('scanned_barcode')) {
                $logDetails .= ' (Verified with barcode scan)';
            }

            RequestActivityLog::logRequestActivity(
                $logDetails,
                $supplyRequest,
                'fulfilled',
                [
                    'quantity' => $supplyRequest->quantity,
                    'item_name' => $itemName,
                    'claim_slip_number' => $claimSlipNumber,
                    'scanned_barcode' => $httpRequest->filled('scanned_barcode')
                ]
            );

            DB::commit();

            return back()->with('success', 'Request completed and claimed successfully! Claim slip: ' . $claimSlipNumber);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to complete request: ' . $e->getMessage()]);
        }
    }

    public function decline(Request $request, SupplyRequest $supplyRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can decline requests.');
        }

        // Check if request can be declined
        if ($supplyRequest->isDeclined() || $supplyRequest->isClaimed() || $supplyRequest->isFulfilled()) {
            return back()->withErrors(['error' => 'This request cannot be declined at this stage.']);
        }

        $validatedData = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $supplyRequest->decline(Auth::user(), $validatedData['reason']);

        // Load item relationship if not already loaded
        if (!$supplyRequest->relationLoaded('item')) {
            $supplyRequest->load('item');
        }

        // Create log entry with null check
        $itemName = $supplyRequest->item ? $supplyRequest->item->name : 'Unknown Item';
        RequestActivityLog::logRequestActivity(
            'Declined request for {subject}',
            $supplyRequest,
            'declined',
            [
                'item_name' => $itemName,
                'reason' => $validatedData['reason']
            ]
        );

        return back()->with('success', 'Request declined successfully!');
    }

    public function destroy(SupplyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Only allow deletion if request is pending and user is the requester or admin
        if (!$request->isPending() || (!$user->isAdmin() && $request->user_id !== $user->id)) {
            abort(403, 'This request cannot be deleted.');
        }

        // Load item relationship if not already loaded
        if (!$request->relationLoaded('item')) {
            $request->load('item');
        }

        // Create log entry before deletion with null check
        $itemName = $request->item ? $request->item->name : 'Unknown Item';
        RequestActivityLog::logRequestActivity(
            'Deleted request for {subject}',
            $request,
            'deleted',
            ['item_name' => $itemName]
        );

        $request->delete();

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->route('requests.manage')
                ->with('success', 'Request deleted successfully!');
        } else {
            return redirect()->route('faculty.requests.index')
                ->with('success', 'Request deleted successfully!');
        }
    }

    public function printClaimSlip(SupplyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can view claim slips
        if (($user->isAdmin() && $user->isAdmin()) || 
            (!$user->isAdmin() && $request->user_id !== $user->id)) {
            abort(403, 'You are not authorized to view claim slips for this request.');
        }

        if (!$request->isApprovedByAdmin() && !$request->isReadyForPickup() && !$request->isFulfilled() && !$request->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        // Generate QR code for the claim slip number
        $qrCode = new \chillerlan\QRCode\QRCode();
        $qrCodeData = $request->claim_slip_number;
        $qrCodeImage = $qrCode->render($qrCodeData);

        return view('admin.requests.claim-slip', compact('request', 'qrCodeImage'));
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

    public function generateClaimSlip(SupplyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Only faculty can generate claim slips for their own approved requests
        if ($user->isAdmin() || $request->user_id !== $user->id) {
            abort(403, 'You are not authorized to generate claim slips for this request.');
        }

        if (!$request->canGenerateClaimSlip()) {
            return back()->withErrors(['error' => 'This request is not eligible for claim slip generation.']);
        }

        $request->generateClaimSlip();

        // Create log entry
        RequestActivityLog::logRequestActivity(
            'Generated claim slip for request: {subject}',
            $request,
            'updated',
            ['claim_slip_number' => $request->claim_slip_number]
        );

        // Redirect back to request details page
        return redirect()->route('faculty.requests.show', $request)->with('success', 'Claim slip generated successfully! You can now print it and pick up your items from the supply office.');
    }

    public function downloadClaimSlip(SupplyRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can download claim slips for fulfilled/claimed requests
        if (($user->isAdmin() && $user->isAdmin()) || 
            (!$user->isAdmin() && $request->user_id !== $user->id)) {
            abort(403, 'You are not authorized to download claim slips for this request.');
        }

        if (!$request->isApprovedByAdmin() && !$request->isReadyForPickup() && !$request->isFulfilled() && !$request->isClaimed()) {
            abort(404, 'Claim slip not available for this request.');
        }

        // Generate QR code for the claim slip number
        $qrCode = new \chillerlan\QRCode\QRCode();
        $qrCodeData = $request->claim_slip_number;
        $qrCodeImage = $qrCode->render($qrCodeData);

        // Generate PDF with QR code
        $pdf = Pdf::loadView('admin.requests.claim-slip-pdf', compact('request', 'qrCodeImage'))
            ->setPaper('a4', 'portrait');

        // Create log entry
        RequestActivityLog::logRequestActivity(
            'Downloaded claim slip PDF for request: {subject}',
            $request,
            'updated',
            ['claim_slip_number' => $request->claim_slip_number]
        );

        return $pdf->download('claim-slip-' . $request->claim_slip_number . '.pdf');
    }
}
