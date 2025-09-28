<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with('category');

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Handle category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Handle stock filter
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'low':
                    $query->whereRaw('quantity <= COALESCE(minimum_stock, 10)');
                    break;
                case 'in-stock':
                    $query->whereRaw('quantity > COALESCE(minimum_stock, 10)');
                    break;
                case 'out-of-stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        $items = $query->orderBy('name')->paginate(15)->appends(request()->query());
        $categories = Category::all();
        
        return view('admin.items.index', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.items.create', compact('categories'));
    }

        /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('items')->whereNull('deleted_at')
            ],
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:New,Good,Fair,Needs Repair',
            'brand' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'warranty_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        // Set quantity to current_stock for backward compatibility
        $data = $request->all();
        $data['quantity'] = $request->current_stock;
        
        // Generate unique QR code
        $data['qr_code'] = Str::uuid();

        $item = Item::create($data);
        
        // Update total value if unit price is provided
        if ($item->unit_price && $item->current_stock) {
            $item->updateTotalValue();
        }

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load('category');
        
        // Return different views based on user role
        return view('admin.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $categories = Category::all();
        return view('admin.items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('items')->whereNull('deleted_at')->ignore($item->id)
            ],
            'current_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:New,Good,Fair,Needs Repair',
            'brand' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'warranty_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
        ]);

        // Update quantity to current_stock for backward compatibility
        $data = $request->all();
        $data['quantity'] = $request->current_stock;

        $item->update($data);
        
        // Update total value if unit price is provided
        if ($item->unit_price && $item->current_stock) {
            $item->updateTotalValue();
        }

        return redirect()->route('items.show', $item)
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Display items for browsing (faculty view).
     */
    public function browse(Request $request)
    {
        $query = Item::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->paginate(12);

        // Build clean query parameters (remove empty values)
        $cleanQuery = array_filter($request->only(['search', 'category_id']), function($value) {
            return $value !== null && $value !== '';
        });

        $items->appends($cleanQuery);
        $categories = Category::all();

        return view('faculty.items.browse', compact('items', 'categories'));
    }

    /**
     * Display items summary for availability checking.
     */
    public function summary(Request $request)
    {
        $query = Item::with(['category'])
            ->selectRaw('*, (current_stock / NULLIF(maximum_stock, 0) * 100) as stock_percentage');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('current_stock', '>', 0)
                          ->whereRaw('current_stock > minimum_stock');
                    break;
                case 'low_stock':
                    $query->whereRaw('current_stock <= minimum_stock')
                          ->where('current_stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'critical':
                    $query->whereRaw('current_stock <= (minimum_stock * 0.5)');
                    break;
            }
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'category_name') {
            $query->join('categories', 'items.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $sortDirection)
                  ->select('items.*', 'categories.name as category_name');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $items = $query->paginate(20)->appends($request->query());
        
        // Get summary statistics
        $totalItems = Item::count();
        $availableItems = Item::where('current_stock', '>', 0)->count();
        $lowStockItems = Item::whereRaw('current_stock <= minimum_stock')->where('current_stock', '>', 0)->count();
        $outOfStockItems = Item::where('current_stock', '<=', 0)->count();
        
        // Get all categories and locations for filters
        $categories = Category::orderBy('name')->get();
        $locations = Item::distinct()->orderBy('location')->pluck('location')->filter();
        
        return view('admin.items.summary', compact(
            'items', 'categories', 'locations',
            'totalItems', 'availableItems', 'lowStockItems', 'outOfStockItems'
        ));
    }

    /**
     * Display low stock items.
     */
    public function lowStock()
    {
        $items = Item::with('category')
            ->whereRaw('quantity <= minimum_stock')
            ->orWhere('quantity', '<=', 10)
            ->paginate(10);

        return view('admin.items.low-stock', compact('items'));
    }

    /**
     * Restock an item.
     */
    public function restock(Request $request, Item $item)
    {
        $request->validate([
            'additional_quantity' => 'required|integer|min:1'
        ]);

        $item->increment('quantity', $request->additional_quantity);
        $item->increment('current_stock', $request->additional_quantity);

        // Update total value if unit price exists
        if ($item->unit_price) {
            $item->updateTotalValue();
        }

        return redirect()->back()
            ->with('success', "Successfully added {$request->additional_quantity} units to {$item->name}.");
    }

    /**
     * Display expiring items.
     */
    public function expiringSoon()
    {
        $items = Item::with('category')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->paginate(10);

        return view('admin.items.expiring-soon', compact('items'));
    }

    /**
     * Display trashed (soft-deleted) items.
     *
     * Shows items that have been soft-deleted and can be restored.
     * Items in trash maintain their relationships and can be recovered.
     *
     * @param Request $request The HTTP request instance
     * @return \Illuminate\View\View
     */
    public function trashed(Request $request)
    {
        // Query trashed items with their category relationships
        $query = Item::onlyTrashed()->with('category');

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('barcode', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Order by deletion date (most recently deleted first)
        $query->orderBy('deleted_at', 'desc');

        // Paginate results
        $items = $query->paginate(15)->appends(request()->query());

        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();

        return view('admin.items.trashed', compact('items', 'categories'));
    }

    /**
     * Restore a trashed (soft-deleted) item.
     *
     * Restores an item from the trash, making it active again.
     * All relationships and data are preserved during restoration.
     *
     * @param int $id The ID of the item to restore
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        try {
            // Find the trashed item
            $item = Item::onlyTrashed()->findOrFail($id);

            // Store item name for success message
            $itemName = $item->name;

            // Restore the item
            $item->restore();

            // Log the restoration activity
            Log::info('Item restored from trash', [
                'item_id' => $item->id,
                'item_name' => $itemName,
                'restored_by' => Auth::id(),
                'restored_at' => now()
            ]);

            return redirect()->route('items.trashed')
                ->with('success', "Item '{$itemName}' has been successfully restored and is now active.");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Attempted to restore non-existent trashed item', [
                'item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'The item you are trying to restore was not found in the trash.');

        } catch (\Exception $e) {
            Log::error('Failed to restore item from trash', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'An error occurred while restoring the item. Please try again.');
        }
    }

    /**
     * Verify barcode and return item details.
     */
    public function verifyBarcode($barcode)
    {
        Log::info('Verify barcode request', ['barcode' => $barcode]);

        $item = Item::with('category')
            ->where('barcode', $barcode)
            ->orWhere('qr_code', $barcode)
            ->first();

        Log::info('Item lookup result', ['found' => $item ? true : false, 'item_id' => $item ? $item->id : null]);

        if (!$item) {
            Log::warning('Item not found for barcode', ['barcode' => $barcode]);
            return response()->json([
                'success' => false,
                'message' => 'Item not found with this barcode.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'brand' => $item->brand,
                'category' => $item->category ? $item->category->name : 'N/A',
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
                'unit' => $item->unit,
                'location' => $item->location,
                'condition' => $item->condition,
                'description' => $item->description,
            ]
        ]);
    }

    /**
     * Show assignment form for an item.
     */
    public function showAssignForm(Item $item)
    {
        $users = \App\Models\User::orderBy('name')->get();
        $offices = \App\Models\Office::orderBy('name')->get();
        
        return view('admin.items.assign', compact('item', 'users', 'offices'));
    }

    /**
     * Assign item to a user.
     */
    public function assign(Request $request, Item $item)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        
        $item->assignTo($user, $request->notes);
        
        // Update location if provided
        if ($request->filled('location')) {
            $item->update(['location' => $request->location]);
        }

        return redirect()->route('items.show', $item)
            ->with('success', "Item '{$item->name}' has been assigned to {$user->name}.");
    }

    /**
     * Unassign item from current holder.
     */
    public function unassign(Item $item)
    {
        if (!$item->isAssigned()) {
            return redirect()->back()
                ->with('error', 'Item is not currently assigned to anyone.');
        }

        $holderName = $item->currentHolder->name;
        $item->unassign();

        return redirect()->route('items.show', $item)
            ->with('success', "Item '{$item->name}' has been returned from {$holderName}.");
    }

    /**
     * Restore multiple trashed items at once.
     *
     * Allows bulk restoration of soft-deleted items.
     * Validates that all provided IDs exist in trash before restoration.
     *
     * @param Request $request The HTTP request instance containing item IDs
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:items,id'
        ]);

        try {
            $itemIds = $request->item_ids;

            // Find trashed items only
            $trashedItems = Item::onlyTrashed()->whereIn('id', $itemIds)->get();

            if ($trashedItems->isEmpty()) {
                return redirect()->route('items.trashed')
                    ->with('error', 'No valid items found in trash to restore.');
            }

            $restoredCount = 0;
            $skippedCount = count($itemIds) - $trashedItems->count();
            $restoredNames = [];

            // Restore each valid trashed item
            foreach ($trashedItems as $item) {
                $item->restore();
                $restoredNames[] = $item->name;
                $restoredCount++;
            }

            // Log the bulk restoration activity
            Log::info('Bulk item restoration from trash', [
                'restored_count' => $restoredCount,
                'skipped_count' => $skippedCount,
                'item_ids' => $itemIds,
                'restored_by' => Auth::id(),
                'restored_at' => now()
            ]);

            // Build success message
            $message = "Successfully restored {$restoredCount} item" . ($restoredCount > 1 ? 's' : '') . '.';
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} item" . ($skippedCount > 1 ? 's were' : ' was') . ' skipped (not found in trash).';
            }

            return redirect()->route('items.trashed')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to perform bulk item restoration', [
                'item_ids' => $request->item_ids ?? [],
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'An error occurred during bulk restoration. Please try again or restore items individually.');
        }
    }
    /**
     * Permanently delete a trashed item.
     *
     * WARNING: This action cannot be undone. The item will be completely removed
     * from the database along with all its relationships and history.
     *
     * @param int $id The ID of the item to permanently delete
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        try {
            // Find the trashed item
            $item = Item::onlyTrashed()->findOrFail($id);

            // Store item name for logging and messages
            $itemName = $item->name;

            // Log the permanent deletion (before actually deleting)
            Log::warning('Item permanently deleted from trash', [
                'item_id' => $item->id,
                'item_name' => $itemName,
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
                'warning' => 'This action cannot be undone'
            ]);

            // Permanently delete the item
            $item->forceDelete();

            return redirect()->route('items.trashed')
                ->with('success', "Item '{$itemName}' has been permanently deleted and cannot be recovered.");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Attempted to permanently delete non-existent trashed item', [
                'item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'The item you are trying to delete was not found in the trash.');

        } catch (\Exception $e) {
            Log::error('Failed to permanently delete item from trash', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'An error occurred while deleting the item. Please try again.');
        }
    }

    /**
     * Permanently delete multiple trashed items at once.
     *
     * WARNING: This action cannot be undone. All selected items will be completely
     * removed from the database along with all their relationships and history.
     *
     * @param Request $request The HTTP request instance containing item IDs
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:items,id'
        ]);

        try {
            $itemIds = $request->item_ids;

            // Find trashed items only
            $trashedItems = Item::onlyTrashed()->whereIn('id', $itemIds)->get();

            if ($trashedItems->isEmpty()) {
                return redirect()->route('items.trashed')
                    ->with('error', 'No valid items found in trash to delete.');
            }

            $deletedCount = 0;
            $skippedCount = count($itemIds) - $trashedItems->count();
            $deletedNames = [];

            // Permanently delete each valid trashed item
            foreach ($trashedItems as $item) {
                $deletedNames[] = $item->name;

                // Log each deletion
                Log::warning('Item permanently deleted from trash (bulk operation)', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now(),
                    'warning' => 'This action cannot be undone'
                ]);

                $item->forceDelete();
                $deletedCount++;
            }

            // Build success message
            $message = "Successfully permanently deleted {$deletedCount} item" . ($deletedCount > 1 ? 's' : '') . '.';
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} item" . ($skippedCount > 1 ? 's were' : ' was') . ' skipped (not found in trash).';
            }
            $message .= ' This action cannot be undone.';

            return redirect()->route('items.trashed')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to perform bulk permanent deletion', [
                'item_ids' => $request->item_ids ?? [],
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.trashed')
                ->with('error', 'An error occurred during permanent deletion. No items were deleted.');
        }
    }
}
