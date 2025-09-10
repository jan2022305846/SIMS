<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Models\Item;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RequestManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of requests with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = RequestModel::with(['user', 'item.category']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })->orWhereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        // Sort requests
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $requests = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Get request statistics for admin overview
     */
    public function statistics()
    {
        $stats = [
            'total_requests' => RequestModel::count(),
            'pending_requests' => RequestModel::where('status', 'pending')->count(),
            'approved_requests' => RequestModel::where('status', 'approved')->count(),
            'declined_requests' => RequestModel::where('status', 'declined')->count(),
            'requests_today' => RequestModel::whereDate('created_at', today())->count(),
            'requests_this_week' => RequestModel::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'requests_by_priority' => RequestModel::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get()
                ->pluck('count', 'priority'),
            'requests_by_status' => RequestModel::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
            'avg_processing_time' => RequestModel::whereNotNull('processed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours')
                ->value('avg_hours') ?? 0,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Display the specified request with detailed information
     */
    public function show(RequestModel $request)
    {
        $request->load(['user', 'item.category', 'processedBy']);
        
        return response()->json([
            'status' => 'success',
            'data' => $request
        ]);
    }

    /**
     * Approve a request
     */
    public function approve(Request $request, RequestModel $requestModel)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:500',
            'approved_quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($requestModel->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending requests can be approved'
            ], 422);
        }

        // Check if requested item has sufficient stock
        $item = $requestModel->item;
        $requestedQuantity = $request->get('approved_quantity', $requestModel->quantity);

        if ($item->current_stock < $requestedQuantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock available. Current stock: ' . $item->current_stock
            ], 422);
        }

        // Update request status
        $requestModel->update([
            'status' => 'approved',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $request->admin_notes,
            'quantity_approved' => $requestedQuantity,
        ]);

        // Update item stock for consumable items
        if ($item->category->type === 'consumable') {
            $item->decrement('current_stock', $requestedQuantity);
        } else {
            // For non-consumable items, assign to requester
            $item->update(['current_holder_id' => $requestModel->user_id]);
        }

        // Log the approval
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'request.approved',
            'description' => "Request #{$requestModel->id} approved for {$requestModel->user->name}. Item: {$item->name}, Quantity: {$requestedQuantity}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request approved successfully',
            'data' => $requestModel->fresh()->load(['user', 'item.category', 'processedBy'])
        ]);
    }

    /**
     * Decline a request
     */
    public function decline(Request $request, RequestModel $requestModel)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($requestModel->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending requests can be declined'
            ], 422);
        }

        // Update request status
        $requestModel->update([
            'status' => 'declined',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Log the decline
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'request.declined',
            'description' => "Request #{$requestModel->id} declined for {$requestModel->user->name}. Reason: {$request->admin_notes}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Request declined successfully',
            'data' => $requestModel->fresh()->load(['user', 'item.category', 'processedBy'])
        ]);
    }

    /**
     * Bulk approve multiple requests
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:requests,id',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requests = RequestModel::whereIn('id', $request->request_ids)
            ->where('status', 'pending')
            ->with(['item.category', 'user'])
            ->get();

        $successCount = 0;
        $failedRequests = [];

        foreach ($requests as $requestModel) {
            try {
                $item = $requestModel->item;
                
                // Check stock availability
                if ($item->current_stock < $requestModel->quantity) {
                    $failedRequests[] = [
                        'id' => $requestModel->id,
                        'reason' => 'Insufficient stock'
                    ];
                    continue;
                }

                // Update request
                $requestModel->update([
                    'status' => 'approved',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                    'admin_notes' => $request->admin_notes ?? 'Bulk approved',
                    'quantity_approved' => $requestModel->quantity,
                ]);

                // Update item stock/assignment
                if ($item->category->type === 'consumable') {
                    $item->decrement('current_stock', $requestModel->quantity);
                } else {
                    $item->update(['current_holder_id' => $requestModel->user_id]);
                }

                $successCount++;
            } catch (\Exception $e) {
                $failedRequests[] = [
                    'id' => $requestModel->id,
                    'reason' => 'Processing error'
                ];
            }
        }

        // Log bulk approval
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'request.bulk_approved',
            'description' => "Bulk approval completed. {$successCount} requests approved, " . count($failedRequests) . " failed",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Bulk approval completed. {$successCount} requests approved",
            'data' => [
                'success_count' => $successCount,
                'failed_requests' => $failedRequests
            ]
        ]);
    }

    /**
     * Get pending requests that need attention
     */
    public function getPendingRequests()
    {
        $pendingRequests = RequestModel::with(['user', 'item.category'])
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user' => $request->user->name,
                    'item' => $request->item->name,
                    'category' => $request->item->category->name,
                    'quantity' => $request->quantity,
                    'priority' => $request->priority,
                    'purpose' => $request->purpose,
                    'created_at' => $request->created_at->format('Y-m-d H:i:s'),
                    'days_pending' => $request->created_at->diffInDays(now()),
                    'available_stock' => $request->item->current_stock,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $pendingRequests
        ]);
    }

    /**
     * Get request history for a specific user
     */
    public function getUserRequestHistory(User $user)
    {
        $requests = RequestModel::with(['item.category', 'processedBy'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }
}
