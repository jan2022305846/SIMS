<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventoryManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display inventory overview with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'currentHolder']);

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'low_stock':
                    $query->where('current_stock', '<=', 10);
                    break;
                case 'expiring_soon':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<=', Carbon::now()->addDays(30));
                    break;
                case 'expired':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<', Carbon::now());
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', 0);
                    break;
                case 'available':
                    $query->where('current_stock', '>', 0);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('current_holder_id')) {
            $query->where('current_holder_id', $request->current_holder_id);
        }

        // Sort items
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Handle special sorting cases
        if ($sortField === 'category_name') {
            $query->join('categories', 'items.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $sortDirection)
                  ->select('items.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $items = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function statistics()
    {
        $stats = [
            'total_items' => Item::count(),
            'total_value' => Item::sum('unit_price'),
            'low_stock_items' => Item::where('current_stock', '<=', 10)->count(),
            'out_of_stock_items' => Item::where('current_stock', 0)->count(),
            'expiring_soon' => Item::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->count(),
            'expired_items' => Item::whereNotNull('expiry_date')
                ->where('expiry_date', '<', Carbon::now())
                ->count(),
            'items_by_category' => Item::join('categories', 'items.category_id', '=', 'categories.id')
                ->selectRaw('categories.name as category, COUNT(*) as count')
                ->groupBy('categories.id', 'categories.name')
                ->get()
                ->pluck('count', 'category'),
            'items_with_holders' => Item::whereNotNull('current_holder_id')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Get low stock items that need attention
     */
    public function getLowStockItems()
    {
        $lowStockItems = Item::with(['category', 'currentHolder'])
            ->where('current_stock', '<=', 10)
            ->orderBy('current_stock', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'current_stock' => $item->current_stock,
                    'unit_price' => $item->unit_price,
                    'total_value' => $item->current_stock * $item->unit_price,
                    'current_holder' => $item->currentHolder->name ?? 'Available',
                    'last_updated' => $item->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $lowStockItems
        ]);
    }

    /**
     * Get items expiring soon
     */
    public function getExpiringItems()
    {
        $expiringItems = Item::with(['category', 'currentHolder'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'expiry_date' => $item->expiry_date,
                    'days_until_expiry' => Carbon::now()->diffInDays($item->expiry_date, false),
                    'current_stock' => $item->current_stock,
                    'current_holder' => $item->currentHolder->name ?? 'Available',
                    'is_expired' => $item->expiry_date < Carbon::now(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $expiringItems
        ]);
    }

    /**
     * Update item stock quantity
     */
    public function updateStock(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'current_stock' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldQuantity = $item->current_stock;
        $item->update(['current_stock' => $request->current_stock]);

        // Log the stock change
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'inventory.stock_updated',
            'description' => "Stock updated for '{$item->name}' from {$oldQuantity} to {$request->current_stock}. Reason: {$request->reason}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Stock quantity updated successfully',
            'data' => $item->fresh()
        ]);
    }

    /**
     * Transfer item to different holder
     */
    public function transferItem(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'new_holder_id' => 'nullable|exists:users,id',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldHolder = $item->currentHolder;
        $newHolder = $request->new_holder_id ? User::find($request->new_holder_id) : null;

        $item->update(['current_holder_id' => $request->new_holder_id]);

        // Log the transfer
        $description = "Item '{$item->name}' transferred from " . 
                      ($oldHolder ? $oldHolder->name : 'Available') . 
                      " to " . 
                      ($newHolder ? $newHolder->name : 'Available') . 
                      ". Reason: {$request->reason}";

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'inventory.item_transferred',
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item transferred successfully',
            'data' => $item->fresh()->load('currentHolder')
        ]);
    }

    /**
     * Restore deleted item (soft delete recovery)
     */
    public function restoreItem($itemId)
    {
        $item = Item::onlyTrashed()->find($itemId);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deleted item not found'
            ], 404);
        }

        $item->restore();

        // Log the restoration
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'inventory.item_restored',
            'description' => "Item '{$item->name}' has been restored from deletion",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item restored successfully',
            'data' => $item->load(['category', 'currentHolder'])
        ]);
    }

    /**
     * Get deleted items that can be restored
     */
    public function getDeletedItems()
    {
        $deletedItems = Item::onlyTrashed()
            ->with(['category'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $deletedItems
        ]);
    }

    /**
     * Get available users for item assignment
     */
    public function getAvailableHolders()
    {
        $users = User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'department']);

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Bulk update multiple items
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
            'action' => 'required|in:delete,transfer,update_category',
            'new_holder_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $items = Item::whereIn('id', $request->item_ids)->get();
        $successCount = 0;

        foreach ($items as $item) {
            try {
                switch ($request->action) {
                    case 'delete':
                        $item->delete();
                        $successCount++;
                        break;

                    case 'transfer':
                        $item->update(['current_holder_id' => $request->new_holder_id]);
                        $successCount++;
                        break;

                    case 'update_category':
                        $item->update(['category_id' => $request->category_id]);
                        $successCount++;
                        break;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Log bulk action
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'inventory.bulk_action',
            'description' => "Bulk {$request->action} performed on {$successCount} items",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Bulk action completed successfully on {$successCount} items"
        ]);
    }
}
