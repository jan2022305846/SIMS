<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as SupplyRequest;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Log as ActivityLog;
use App\Models\ActivityLog as RequestActivityLog;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

/**
 * Request Controller for managing supply requests
 * 
 * @method User user() Get the authenticated user with proper type hints
 */

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplyRequest::with(['user', 'adminApprover']);
        
        // Load requestItems separately due to morphTo eager loading issue
        $query->with('requestItems');

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

        // Calculate stats from the entire dataset before pagination
        $statsQuery = clone $query;
        $statsQuery->with('requestItems');
        $allRequests = $statsQuery->get();
        
        $stats = [
            'total_requests' => $allRequests->count(),
            'pending' => $allRequests->where('status', 'pending')->count(),
            'approved_by_admin' => $allRequests->where('status', 'approved_by_admin')->count(),
            'fulfilled' => $allRequests->where('status', 'fulfilled')->count(),
            'claimed' => $allRequests->where('status', 'claimed')->count(),
            'total_items_claimed' => $allRequests->where('status', 'claimed')->sum(function($request) {
                return $request->requestItems->sum('quantity');
            }),
        ];

        // Order by most recent first (updated_at desc for most recently modified, then by id desc for consistent ordering)
        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        $requests = $query->paginate(15);
        
        // Load itemable relationships manually for each request
        foreach ($requests as $supplyRequest) {
            if ($supplyRequest->requestItems->count() > 0) {
                // Manually load itemable relationships for each request item
                foreach ($supplyRequest->requestItems as $requestItem) {
                    if ($requestItem->item_type === 'consumable') {
                        $itemable = Consumable::find($requestItem->item_id);
                    } elseif ($requestItem->item_type === 'non_consumable') {
                        $itemable = NonConsumable::find($requestItem->item_id);
                    } else {
                        $itemable = null;
                    }
                    $requestItem->setRelation('itemable', $itemable);
                }
            }
        }
        
        $offices = \App\Models\Office::orderBy('name')->get();

        return view('admin.requests.index', compact('requests', 'offices', 'stats'));
    }

    public function create()
    {
        $consumables = Consumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'consumable';
            return $item;
        });
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });
        $items = $consumables->concat($nonConsumables);

        return view('faculty.requests.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'request_type' => 'required|in:single,bulk',
            'purpose' => 'required|string|max:500',
            'needed_date' => 'required|date|after_or_equal:today',
            'office_id' => 'nullable|exists:offices,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'attachments.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Additional validation based on request type
        if ($request->input('request_type') === 'bulk') {
            $additionalRules = [
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|integer',
                'items.*.item_type' => 'required|in:consumable,non_consumable',
                'items.*.quantity' => 'required|integer|min:1',
            ];
        } else {
            $additionalRules = [
                'item_id' => 'required|integer',
                'item_type' => 'required|in:consumable,non_consumable',
                'quantity' => 'required|integer|min:1',
            ];
        }

        $additionalValidated = $request->validate($additionalRules);
        $validatedData = array_merge($validatedData, $additionalValidated);

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
                    Logger::warning('Stock validation failed', [
                        'item_id' => $item->id,
                        'requested' => $validatedData['quantity'],
                        'available' => $item->quantity
                    ]);
                                     new \Exception("Requested quantity exceeds available stock ({$item->quantity} available)");
                }

                $supplyRequest->requestItems()->create([
                    'item_id' => $validatedData['item_id'],
                    'item_type' => $validatedData['item_type'],
                    'quantity' => $validatedData['quantity'],
                    'status' => 'available',
                ]);
            }

            DB::commit();

            // Notify admin about new pending request
            \App\Services\NotificationService::notifyNewPendingRequest($supplyRequest);

            // Clear dashboard cache for the user who created the request
            $dashboardService = app(DashboardService::class);
            $dashboardService->clearCache(Auth::user());

            return redirect()->route('faculty.requests.show', $supplyRequest)
                ->with('success', 'Request submitted successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            Logger::error('Model not found during request creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Required data not found. Please try again.']);
        } catch (\Exception $e) {
            DB::rollback();
            Logger::error('Unexpected error during request creation', [
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
        $supplyRequest->load(['user.office', 'adminApprover', 'office']);
        
        // Load requestItems with their itemable relationships manually
        $supplyRequest->load('requestItems');
        if ($supplyRequest->requestItems->count() > 0) {
            // Manually load itemable relationships for each request item
            foreach ($supplyRequest->requestItems as $requestItem) {
                if ($requestItem->item_type === 'consumable') {
                    $itemable = Consumable::find($requestItem->item_id);
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $itemable = NonConsumable::find($requestItem->item_id);
                } else {
                    $itemable = null;
                }
                $requestItem->setRelation('itemable', $itemable);
            }
        }

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

        $consumables = Consumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'consumable';
            return $item;
        });
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });
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

        // Clear dashboard cache for both admin and faculty user
        $dashboardService = app(DashboardService::class);
        $dashboardService->clearCache(Auth::user()); // Admin
        $dashboardService->clearCache($supplyRequest->user); // Faculty

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

        // Load requestItems relationship if not already loaded
        if (!$supplyRequest->relationLoaded('requestItems')) {
            $supplyRequest->load('requestItems.itemable');
        }

        // Get the first request item for validation
        $firstRequestItem = $supplyRequest->requestItems->first();
        if (!$firstRequestItem || !$firstRequestItem->itemable) {
            Logger::error('Fulfill failed: Request item or itemable is null', [
                'request_id' => $supplyRequest->id,
                'firstRequestItem_exists' => $firstRequestItem ? true : false,
                'itemable_exists' => $firstRequestItem && $firstRequestItem->itemable ? true : false,
                'requestItems_count' => $supplyRequest->requestItems->count(),
            ]);
            return back()->withErrors(['error' => 'Request item not found.']);
        }

        // Check stock availability one more time with null check
        if ($firstRequestItem->itemable && $firstRequestItem->itemable->quantity < $firstRequestItem->quantity) {
            Logger::warning('Fulfill failed: Insufficient stock', [
                'request_id' => $supplyRequest->id,
                'item_id' => $firstRequestItem->itemable->id ?? 'N/A',
                'item_name' => $firstRequestItem->itemable->name ?? 'Unknown Item',
                'available' => $firstRequestItem->itemable->quantity ?? 0,
                'requested' => $firstRequestItem->quantity,
            ]);
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

            if ($scannedItem->id !== ($firstRequestItem->itemable->id ?? null)) {
                return back()->withErrors(['error' => 'Scanned item does not match the requested item. Please scan the correct item.']);
            }
        }

        DB::beginTransaction();
        try {
            $supplyRequest->fulfill(Auth::user());

            DB::commit();

            // Clear dashboard cache for the user whose request was fulfilled
            $dashboardService = app(DashboardService::class);
            $dashboardService->clearCache($supplyRequest->user);

            return back()->with('success', 'Request fulfilled successfully! Claim slip number: ' . $supplyRequest->claim_slip_number);

        } catch (\Exception $e) {
            DB::rollback();
            Logger::error('Fulfill transaction failed', [
                'request_id' => $supplyRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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

        // Load requestItems relationship if not already loaded
        if (!$supplyRequest->relationLoaded('requestItems')) {
            $supplyRequest->load('requestItems');
        }

        // Manually load itemable relationships for each request item
        foreach ($supplyRequest->requestItems as $requestItem) {
            if (!$requestItem->relationLoaded('itemable')) {
                $morphClass = match($requestItem->item_type) {
                    'consumable' => Consumable::class,
                    'non_consumable' => NonConsumable::class,
                    default => null,
                };

                if ($morphClass && class_exists($morphClass)) {
                    $itemable = $morphClass::find($requestItem->item_id);
                    $requestItem->setRelation('itemable', $itemable);
                } else {
                    $requestItem->setRelation('itemable', null);
                }
            }
        }

        // Check that all request items have valid itemable relationships
        $invalidItems = $supplyRequest->requestItems->filter(function ($requestItem) {
            return !$requestItem->itemable;
        });

        if ($invalidItems->count() > 0) {
            Logger::error('MarkAsClaimed failed: Some request items have null itemable', [
                'request_id' => $supplyRequest->id,
                'invalid_items_count' => $invalidItems->count(),
                'total_items_count' => $supplyRequest->requestItems->count(),
                'invalid_item_ids' => $invalidItems->pluck('id')->toArray(),
            ]);
            return back()->withErrors(['error' => 'Some requested items are no longer available. Please contact the supply office.']);
        }

        // Note: Stock availability was already checked during approval
        // For fulfilled requests, stock has been reserved, so we skip the check

        // Note: QR verification is handled separately via AJAX
        // The scanned_barcode field now contains QR data, not item barcodes

        DB::beginTransaction();
        try {
            $supplyRequest->markAsClaimed(Auth::user());

            DB::commit();

            // Clear dashboard cache for the user whose request was claimed
            $dashboardService = app(DashboardService::class);
            $dashboardService->clearCache($supplyRequest->user);

            $stockMessage = $supplyRequest->requestItems->contains(function ($item) {
                return $item->isConsumable();
            }) ? ' Stock has been updated.' : '';
            return back()->with('success', 'Request marked as claimed successfully!' . $stockMessage);

        } catch (\Exception $e) {
            DB::rollback();
            Logger::error('MarkAsClaimed transaction failed', [
                'request_id' => $supplyRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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

        // Load requestItems relationship if not already loaded
        if (!$supplyRequest->relationLoaded('requestItems')) {
            $supplyRequest->load('requestItems.itemable');
        }

        // Get the first request item for validation
        $firstRequestItem = $supplyRequest->requestItems->first();
        if (!$firstRequestItem || !$firstRequestItem->itemable) {
            Logger::error('CompleteAndClaim failed: Request item or itemable is null', [
                'request_id' => $supplyRequest->id,
                'firstRequestItem_exists' => $firstRequestItem ? true : false,
                'itemable_exists' => $firstRequestItem && $firstRequestItem->itemable ? true : false,
                'requestItems_count' => $supplyRequest->requestItems->count(),
            ]);
            return back()->withErrors(['error' => 'Request item not found.']);
        }

        // Check stock availability one more time
        if ($firstRequestItem->itemable && $firstRequestItem->itemable->quantity < $firstRequestItem->quantity) {
            Logger::warning('CompleteAndClaim failed: Insufficient stock', [
                'request_id' => $supplyRequest->id,
                'item_id' => $firstRequestItem->itemable->id ?? 'N/A',
                'item_name' => $firstRequestItem->itemable->name ?? 'Unknown Item',
                'available' => $firstRequestItem->itemable->quantity ?? 0,
                'requested' => $firstRequestItem->quantity,
            ]);
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

            if ($scannedItem->id !== ($firstRequestItem->itemable->id ?? null)) {
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
            if ($firstRequestItem->isConsumable() && $firstRequestItem->itemable) {
                $firstRequestItem->itemable->quantity -= $firstRequestItem->quantity;
                $firstRequestItem->itemable->save();
            }

            DB::commit();

            // Clear dashboard cache for the user whose request was completed and claimed
            $dashboardService = app(DashboardService::class);
            $dashboardService->clearCache($supplyRequest->user);

            return back()->with('success', 'Request completed and claimed successfully! Claim slip: ' . $claimSlipNumber);

        } catch (\Exception $e) {
            DB::rollback();
            Logger::error('CompleteAndClaim transaction failed', [
                'request_id' => $supplyRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to complete request: ' . $e->getMessage()]);
        }
    }    public function decline(Request $httpRequest, SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        Logger::info('Decline method called', [
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

        Logger::info('Declining request', [
            'request_id' => $supplyRequest->id,
            'current_status' => $supplyRequest->status,
            'reason' => $validatedData['reason']
        ]);

        $supplyRequest->load('user');
        $supplyRequest->decline(Auth::user(), $validatedData['reason']);

        // Check if the status was actually updated
        $supplyRequest->refresh();
        Logger::info('Request status after decline', [
            'request_id' => $supplyRequest->id,
            'new_status' => $supplyRequest->status,
            'notes' => $supplyRequest->notes
        ]);

        // Clear dashboard cache for the user whose request was declined
        $dashboardService = app(DashboardService::class);
        $dashboardService->clearCache($supplyRequest->user);

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

        // Load requestItems relationship if not already loaded
        if (!$supplyRequest->relationLoaded('requestItems')) {
            $supplyRequest->load('requestItems.itemable');
        }

        $supplyRequest->delete();

        // Clear dashboard cache for the user whose request was deleted
        $dashboardService = app(DashboardService::class);
        $dashboardService->clearCache($supplyRequest->user);

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->route('requests.manage')
                ->with('success', 'Request deleted successfully!');
        } else {
            return redirect()->route('faculty.requests.index')
                ->with('success', 'Request deleted successfully!');
        }
    }

    public function downloadClaimSlip(SupplyRequest $supplyRequest)
    {
        /** @var \App\Models\Request $supplyRequest */
        /** @var User $user */
        $user = Auth::user();

        // Only faculty and admin can download claim slips
        if (!$user || !($user->isAdmin() || $supplyRequest->user_id === $user->id)) {
            abort(403, 'You are not authorized to download claim slips for this request.');
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

        // Generate DOCX
        return $this->generateClaimSlipDocx($supplyRequest, $qrCodeImage);
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

        // Load relationships needed for the view
        $supplyRequest->load(['user.office', 'adminApprover']);

        return view('admin.requests.claim-slip', [
            'request' => $supplyRequest,
            'qrCodeImage' => $qrCodeImage
        ]);
    }

    /**
     * Generate DOCX claim slip with clean, professional styling
     */
    private function generateClaimSlipDocx(SupplyRequest $supplyRequest, string $qrCodeImage)
    {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            throw new \Exception('PHP ZipArchive extension is required for DOCX export. Please contact your administrator.');
        }

        $phpWord = new PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Supply Office System');
        $properties->setCompany('Supply Office');
        $properties->setTitle('Claim Slip - ' . $supplyRequest->claim_slip_number);
        $properties->setDescription('Official Claim Slip Document');
        $properties->setCategory('Claim Slips');
        $properties->setLastModifiedBy('System');
        $properties->setCreated(time());
        $properties->setModified(time());

        // Add a section with standard margins
        $section = $phpWord->addSection([
            'marginLeft' => 720,    // 0.5 inch
            'marginRight' => 720,   // 0.5 inch
            'marginTop' => 720,     // 0.5 inch
            'marginBottom' => 720,  // 0.5 inch
        ]);

        // ===== HEADER =====
        $section->addText('SIMS OFFICIAL CLAIM SLIP', [
            'bold' => true,
            'size' => 16,
            'allCaps' => true,
            'color' => '000000'
        ], ['alignment' => 'center']);

        $section->addText($supplyRequest->claim_slip_number, [
            'size' => 12,
            'color' => '6c757d',
            'bold' => true
        ], ['alignment' => 'center']);

        $statusText = $supplyRequest->isClaimed() ? 'CLAIMED' : 'READY FOR PICKUP';
        $section->addText($statusText, [
            'size' => 10,
            'bold' => true,
            'allCaps' => true,
            'color' => $supplyRequest->isClaimed() ? 'ffffff' : '000000',
            'bgColor' => $supplyRequest->isClaimed() ? '000000' : 'ffffff'
        ], ['alignment' => 'center']);

        $section->addTextBreak(2);

        // ===== REQUESTER INFORMATION =====
        $section->addText('REQUESTER INFORMATION', [
            'bold' => true,
            'size' => 12,
            'allCaps' => true,
            'color' => '2c3e50'
        ]);

        $section->addText('Name: ' . $supplyRequest->user->name, ['size' => 11, 'bold' => true]);
        $section->addText('Email: ' . $supplyRequest->user->email, ['size' => 11, 'bold' => true]);
        $section->addText('Office: ' . ($supplyRequest->user->office->name ?? 'N/A'), ['size' => 11, 'bold' => true]);
        $section->addText('Request Date: ' . ($supplyRequest->created_at ? $supplyRequest->created_at->format('M j, Y') : 'N/A'), ['size' => 11, 'bold' => true]);
        $section->addText('Needed Date: ' . ($supplyRequest->needed_date ? $supplyRequest->needed_date->format('M j, Y') : 'N/A'), ['size' => 11, 'bold' => true]);
        $section->addText('Priority: ' . strtoupper($supplyRequest->priority), ['size' => 11, 'bold' => true]);

        $section->addTextBreak(1);

        // ===== PURPOSE =====
        $section->addText('PURPOSE', [
            'bold' => true,
            'size' => 12,
            'allCaps' => true,
            'color' => '2c3e50'
        ]);

        $section->addText($supplyRequest->purpose, ['size' => 11]);

        $section->addTextBreak(1);

        // ===== ITEM DETAILS =====
        $section->addText('ITEM DETAILS', [
            'bold' => true,
            'size' => 12,
            'allCaps' => true,
            'color' => '2c3e50'
        ]);

        if ($supplyRequest->requestItems && $supplyRequest->requestItems->count() > 0) {
            foreach ($supplyRequest->requestItems as $requestItem) {
                $itemName = $requestItem->itemable ? $requestItem->itemable->name : 'Item Not Found';
                $quantity = $requestItem->quantity;
                $unit = $requestItem->itemable ? ($requestItem->itemable->unit ?? 'pcs') : 'pcs';

                $section->addText('• ' . $itemName . ' - ' . $quantity . ' ' . $unit, ['size' => 11]);
            }
        } else {
            $section->addText('• No Items Found - 0 pcs', ['size' => 11]);
        }

        $section->addTextBreak(1);

        // ===== VERIFICATION =====
        $section->addText('VERIFICATION', [
            'bold' => true,
            'size' => 12,
            'allCaps' => true,
            'color' => '2c3e50'
        ], ['alignment' => 'center']);

        $section->addText('Present this claim slip at the supply office for verification.', [
            'size' => 10,
            'italic' => true
        ], ['alignment' => 'center']);

        $section->addTextBreak(2);

        // ===== SIGNATURES =====
        $signatureTable = $section->addTable();
        $signatureTable->addRow();

        // Requestor's signature
        $sig1Cell = $signatureTable->addCell(2000, ['valign' => 'bottom']);
        $sig1Cell->addText('_______________________________', ['size' => 10], ['alignment' => 'center']);
        $sig1Cell->addText('Requestor\'s Signature', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $sig1Cell->addText($supplyRequest->user->name, ['size' => 10], ['alignment' => 'center']);

        // Supply officer's signature
        $sig2Cell = $signatureTable->addCell(2000, ['valign' => 'bottom']);
        $sig2Cell->addText('_______________________________', ['size' => 10], ['alignment' => 'center']);
        $sig2Cell->addText('Supply Officer\'s Signature', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $sig2Cell->addText($supplyRequest->fulfilledBy->name ?? '_______________', ['size' => 10], ['alignment' => 'center']);

        // Date received
        $sig3Cell = $signatureTable->addCell(2000, ['valign' => 'bottom']);
        $sig3Cell->addText('_______________________________', ['size' => 10], ['alignment' => 'center']);
        $sig3Cell->addText('Date Received', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $sig3Cell->addText('___/___/______', ['size' => 10], ['alignment' => 'center']);

        $section->addTextBreak(1);

        // ===== IMPORTANT NOTES =====
        $section->addText('IMPORTANT NOTES:', ['bold' => true, 'size' => 10]);

        $notesList = [
            'Present this claim slip when collecting your items',
            'Items must be collected within 5 working days',
            'Keep this document for your records'
        ];

        foreach ($notesList as $note) {
            $section->addText('• ' . $note, ['size' => 10]);
        }

        $section->addTextBreak(2);

        // ===== FOOTER =====
        $section->addText('Generated: ' . now()->format('M j, Y g:i A') . ' | Claim Slip: ' . $supplyRequest->claim_slip_number, [
            'size' => 9,
            'color' => '6c757d'
        ], ['alignment' => 'center']);

        $section->addText('Supply Office Management System - Official Document', [
            'size' => 8,
            'color' => '6c757d'
        ], ['alignment' => 'center']);

        $filename = 'claim-slip-' . $supplyRequest->claim_slip_number . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $phpWord->save($tempFile, 'Word2007');

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    // Legacy methods for backwards compatibility
    public function manage(Request $request)
    {
        return $this->index($request);
    }

    public function myRequests(Request $request)
    {
        $query = SupplyRequest::with(['user', 'adminApprover']);
        
        // Load requestItems separately due to morphTo eager loading issue
        $query->with('requestItems');

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

        // Order by most recent first
        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        $requests = $query->paginate(15);
        
        // Load itemable relationships manually for each request
        foreach ($requests as $supplyRequest) {
            if ($supplyRequest->requestItems->count() > 0) {
                // Manually load itemable relationships for each request item
                foreach ($supplyRequest->requestItems as $requestItem) {
                    if ($requestItem->item_type === 'consumable') {
                        $itemable = Consumable::find($requestItem->item_id);
                    } elseif ($requestItem->item_type === 'non_consumable') {
                        $itemable = NonConsumable::find($requestItem->item_id);
                    } else {
                        $itemable = null;
                    }
                    $requestItem->setRelation('itemable', $itemable);
                }
            }
        }

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

        $consumables = Consumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'consumable';
            return $item;
        });
        $nonConsumables = NonConsumable::where('quantity', '>', 0)->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });
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

        Logger::info('Cancelling faculty request', [
            'request_id' => $supplyRequest->id,
            'current_status' => $supplyRequest->status,
            'reason' => $validatedData['reason'] ?? null
        ]);

        $supplyRequest->load('user');
        $supplyRequest->cancel($user, $validatedData['reason'] ?? null);

        // Check if the status was actually updated
        $supplyRequest->refresh();
        Logger::info('Request status after cancellation', [
            'request_id' => $supplyRequest->id,
            'new_status' => $supplyRequest->status,
            'notes' => $supplyRequest->notes
        ]);

        // Clear dashboard cache for the user who cancelled their request
        $dashboardService = app(DashboardService::class);
        $dashboardService->clearCache($user);

        return redirect()->route('faculty.requests.index')->with('success', 'Request cancelled successfully!');
    }

    public function verifyClaimSlipQR(Request $httpRequest)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Accept JSON data directly from request body
        // Try both all() and json()->all() to handle different request parsing scenarios
        $qrData = $httpRequest->all();
        if (empty($qrData) || !isset($qrData['qr_data'])) {
            $qrData = $httpRequest->json()->all();
        }
        
        Logger::info('QR verification request received', [
            'raw_data' => $qrData,
            'content_type' => $httpRequest->header('Content-Type'),
            'request_content' => $httpRequest->getContent()
        ]);

        // If qr_data is provided as string, try to decode it
        if (isset($qrData['qr_data']) && is_string($qrData['qr_data'])) {
            $decodedData = json_decode($qrData['qr_data'], true);
            if ($decodedData) {
                $qrData = $decodedData;
            } else {
                // If it's not valid JSON, treat it as a manual claim slip number entry
                $claimSlipNumber = $qrData['qr_data'];
                $requestId = $qrData['request_id'] ?? null;

                if (!$requestId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Request ID is required for manual verification'
                    ], 400);
                }

                try {
                    $request = SupplyRequest::findOrFail($requestId);

                    if ($request->claim_slip_number === $claimSlipNumber) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Claim slip verified successfully',
                            'data' => [
                                'claim_slip_number' => $request->claim_slip_number,
                                'request_id' => $request->id,
                                'user_name' => $request->user->name ?? 'Unknown User',
                                'department' => $request->user->office->name ?? 'N/A',
                                'items_count' => $request->requestItems ? $request->requestItems->count() : 1,
                                'total_quantity' => $request->requestItems ? $request->requestItems->sum('quantity') : $request->quantity,
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Claim slip number does not match this request'
                        ], 400);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to verify claim slip'
                    ], 500);
                }
            }
        }
        // If qr_data is provided as an array/object (parsed JSON), use it directly
        elseif (isset($qrData['qr_data']) && is_array($qrData['qr_data'])) {
            $qrData = $qrData['qr_data'];
        }
        
        Logger::info('QR data after parsing', ['parsed_qr_data' => $qrData]);        // Validate required fields for full QR data
        Logger::info('About to validate QR data fields', ['qr_data' => $qrData]);
        $requiredFields = ['type', 'claim_slip_number', 'request_id', 'user_id', 'timestamp', 'hash'];
        foreach ($requiredFields as $field) {
            if (!isset($qrData[$field]) || empty($qrData[$field])) {
                Logger::error('Missing or empty required field in QR data', [
                    'missing_field' => $field,
                    'field_value' => $qrData[$field] ?? 'NOT_SET',
                    'available_fields' => array_keys($qrData),
                    'qr_data' => $qrData
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'QR code missing required verification data'
                ], 400);
            }
        }

        if ($qrData['type'] !== 'claim_slip') {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid QR code type'
            ], 400);
        }

        try {
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
            $request = SupplyRequest::findOrFail($qrData['request_id']);
            
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
                    'department' => $request->user->office->name ?? 'N/A',
                    'items_count' => $request->requestItems ? $request->requestItems->count() : 1,
                    'total_quantity' => $request->requestItems ? $request->requestItems->sum('quantity') : $request->quantity,
                ]
            ]);

        } catch (\Exception $e) {
            Logger::error('QR code verification error', [
                'error' => $e->getMessage(),
                'qr_data' => $qrData,
                'request_id' => $qrData['request_id'] ?? null
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Failed to verify QR code'
            ], 500);
        }
    }
}
