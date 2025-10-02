<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Consumable;
use App\Models\NonConsumable;
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
        // Get consumables
        $consumablesQuery = Consumable::with('category');
        // Get non-consumables
        $nonConsumablesQuery = NonConsumable::with('category');

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $consumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
            $nonConsumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Handle category filter
        if ($request->filled('category')) {
            $consumablesQuery->where('category_id', $request->category);
            $nonConsumablesQuery->where('category_id', $request->category);
        }

        // Handle stock filter
        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'low':
                    $consumablesQuery->whereRaw('quantity <= COALESCE(min_stock, 10)');
                    $nonConsumablesQuery->whereRaw('quantity <= COALESCE(min_stock, 10)');
                    break;
                case 'in-stock':
                    $consumablesQuery->whereRaw('quantity > COALESCE(min_stock, 10)');
                    $nonConsumablesQuery->whereRaw('quantity > COALESCE(min_stock, 10)');
                    break;
                case 'out-of-stock':
                    $consumablesQuery->where('quantity', '<=', 0);
                    $nonConsumablesQuery->where('quantity', '<=', 0);
                    break;
            }
        }

        // Handle type filter
        if ($request->filled('type')) {
            if ($request->type === 'consumable') {
                $nonConsumables = collect(); // Only show consumables
                $consumables = $consumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'consumable';
                    return $item;
                });
            } elseif ($request->type === 'non_consumable') {
                $consumables = collect(); // Only show non-consumables
                $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'non_consumable';
                    return $item;
                });
            } else {
                // Show all types
                $consumables = $consumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'consumable';
                    return $item;
                });
                $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'non_consumable';
                    return $item;
                });
            }
        } else {
            // Get paginated results
            $consumables = $consumablesQuery->get()->map(function ($item) {
                $item->item_type = 'consumable';
                return $item;
            });
            $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
                $item->item_type = 'non_consumable';
                return $item;
            });
        }

        // Combine and sort results
        $allItems = $consumables->concat($nonConsumables)->sortBy('name');

        // Manual pagination
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items->appends($request->query());
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
        $rules = [
            'name' => 'required|string|max:255',
            'item_type' => 'required|in:consumable,non_consumable',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'product_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('consumables')->whereNull('deleted_at'),
                Rule::unique('non_consumables')->whereNull('deleted_at')
            ],
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:255',
        ];

        // Add conditional validation for non-consumables
        if ($request->item_type === 'non_consumable') {
            $rules['location'] = 'required|string|max:255';
            $rules['condition'] = 'required|in:New,Good,Fair,Needs Repair';
        }

        $request->validate($rules);

        $data = $request->all();
        
        // Generate unique QR code
        $data['qr_code'] = Str::uuid();

        // Create item in the appropriate table based on item_type
        if ($request->item_type === 'consumable') {
            $item = Consumable::create($data);
        } else {
            $item = NonConsumable::create($data);
        }

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::with('category')->find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::with('category')->find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        // Add item type for the view
        $item->item_type = $item instanceof Consumable ? 'consumable' : 'non_consumable';

        // Return different views based on user role
        return view('admin.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $categories = Category::all();
        return view('admin.items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $isConsumable = $item instanceof Consumable;

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'product_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique($isConsumable ? 'consumables' : 'non_consumables')->whereNull('deleted_at')->ignore($item->id)
            ],
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:255',
        ];

        // Add conditional validation for non-consumables
        if (!$isConsumable) {
            $rules['location'] = 'required|string|max:255';
            $rules['condition'] = 'required|in:New,Good,Fair,Needs Repair';
        }

        $request->validate($rules);

        $item->update($request->all());

        return redirect()->route('items.show', $item)
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    /**
     * Display items for browsing (faculty view).
     */
    public function browse(Request $request)
    {
        // Get consumables
        $consumablesQuery = Consumable::with('category');
        // Get non-consumables
        $nonConsumablesQuery = NonConsumable::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $consumablesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
            $nonConsumablesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $consumablesQuery->where('category_id', $request->category);
            $nonConsumablesQuery->where('category_id', $request->category);
        }

        // Get results and add type indicators
        $consumables = $consumablesQuery->get()->map(function ($item) {
            $item->item_type = 'consumable';
            return $item;
        });
        $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });

        // Combine results
        $allItems = $consumables->concat($nonConsumables)->sortBy('name');

        // Manual pagination
        $perPage = 12;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        // Build clean query parameters (remove empty values)
        $cleanQuery = array_filter($request->only(['search', 'category']), function($value) {
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
        // Get consumables
        $consumablesQuery = Consumable::with(['category']);
        // Get non-consumables
        $nonConsumablesQuery = NonConsumable::with(['category']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $consumablesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
            $nonConsumablesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $consumablesQuery->where('category_id', $request->category);
            $nonConsumablesQuery->where('category_id', $request->category);
        }

        // Get results and add type indicators
        $consumables = $consumablesQuery->get()->map(function ($item) {
            $item->item_type = 'consumable';
            $item->stock_percentage = $item->max_stock > 0 ? ($item->quantity / $item->max_stock) * 100 : 0;
            return $item;
        });
        $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            $item->stock_percentage = $item->max_stock > 0 ? ($item->quantity / $item->max_stock) * 100 : 0;
            return $item;
        });

        // Combine results
        $allItems = $consumables->concat($nonConsumables);

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        if ($sortField === 'category_name') {
            $allItems = $allItems->sortBy(function ($item) use ($sortDirection) {
                $categoryName = $item->category ? $item->category->name : '';
                return $sortDirection === 'desc' ? strtoupper($categoryName) : $categoryName;
            });
        } else {
            $allItems = $allItems->sortBy($sortField, SORT_REGULAR, $sortDirection === 'desc');
        }

        // Manual pagination
        $perPage = 20;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items->appends($request->query());

        // Get summary statistics
        $totalItems = Consumable::count() + NonConsumable::count();
        $availableItems = Consumable::where('quantity', '>', 0)->count() + NonConsumable::where('quantity', '>', 0)->count();
        $lowStockItems = Consumable::whereRaw('quantity <= min_stock')->where('quantity', '>', 0)->count() +
                        NonConsumable::whereRaw('quantity <= min_stock')->where('quantity', '>', 0)->count();
        $outOfStockItems = Consumable::where('quantity', '<=', 0)->count() + NonConsumable::where('quantity', '<=', 0)->count();

        // Get all categories and locations for filters
        $categories = Category::orderBy('name')->get();

        return view('admin.items.summary', compact(
            'items', 'categories',
            'totalItems', 'availableItems', 'lowStockItems', 'outOfStockItems'
        ));
    }

    /**
     * Display low stock items.
     */
    public function lowStock()
    {
        // Get low stock consumables
        $consumables = Consumable::with('category')
            ->whereRaw('quantity <= min_stock')
            ->orWhere('quantity', '<=', 10)
            ->get()
            ->map(function ($item) {
                $item->item_type = 'consumable';
                return $item;
            });

        // Get low stock non-consumables
        $nonConsumables = NonConsumable::with('category')
            ->whereRaw('quantity <= min_stock')
            ->orWhere('quantity', '<=', 10)
            ->get()
            ->map(function ($item) {
                $item->item_type = 'non_consumable';
                return $item;
            });

        // Combine and sort results
        $allItems = $consumables->concat($nonConsumables)->sortBy('quantity');

        // Manual pagination
        $perPage = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allItems->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('admin.items.low-stock', compact('items'));
    }

    /**
     * Restock an item.
     */
    public function restock(Request $request, $id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $request->validate([
            'additional_quantity' => 'required|integer|min:1'
        ]);

        $item->increment('quantity', $request->additional_quantity);

        return redirect()->back()
            ->with('success', "Successfully added {$request->additional_quantity} units to {$item->name}.");
    }

    /**
     * Display expiring items.
     */
    public function expiringSoon()
    {
        // Since expiry_date was removed during database normalization,
        // return empty collection
        $items = collect();

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
        // Query trashed consumables with their category relationships
        $consumablesQuery = Consumable::onlyTrashed()->with('category');
        // Query trashed non-consumables with their category relationships
        $nonConsumablesQuery = NonConsumable::onlyTrashed()->with('category');

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $consumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
            $nonConsumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $consumablesQuery->where('category_id', $request->category);
            $nonConsumablesQuery->where('category_id', $request->category);
        }

        // Get results and add type indicators
        $consumables = $consumablesQuery->get()->map(function ($item) {
            $item->item_type = 'consumable';
            return $item;
        });
        $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });

        // Combine results and sort by deletion date (most recently deleted first)
        $allItems = $consumables->concat($nonConsumables)->sortByDesc('deleted_at');

        // Manual pagination
        $perPage = 15;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items->appends(request()->query());

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
            // Try to find the trashed item in consumables first
            $item = Consumable::onlyTrashed()->find($id);

            // If not found in consumables, try non-consumables
            if (!$item) {
                $item = NonConsumable::onlyTrashed()->find($id);
            }

            // If still not found, return error
            if (!$item) {
                return redirect()->route('items.trashed')
                    ->with('error', 'The item you are trying to restore was not found in the trash.');
            }

            // Store item name for success message
            $itemName = $item->name;

            // Restore the item
            $item->restore();

            // Log the restoration activity
            Log::info('Item restored from trash', [
                'item_id' => $item->id,
                'item_name' => $itemName,
                'item_type' => $item instanceof Consumable ? 'consumable' : 'non_consumable',
                'restored_by' => Auth::id(),
                'restored_at' => now()
            ]);

            return redirect()->route('items.trashed')
                ->with('success', "Item '{$itemName}' has been successfully restored and is now active.");

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

        // Search in consumables first
        $item = Consumable::with('category')
            ->where('product_code', $barcode)
            ->first();

        // If not found in consumables, search in non-consumables
        if (!$item) {
            $item = NonConsumable::with('category')
                ->where('product_code', $barcode)
                ->first();
        }

        Log::info('Item lookup result', ['found' => $item ? true : false, 'item_id' => $item ? $item->id : null]);

        if (!$item) {
            Log::warning('Item not found for barcode', ['barcode' => $barcode]);
            return response()->json([
                'success' => false,
                'message' => 'Item not found with this barcode.'
            ], 404);
        }

        // Add item type
        $item->item_type = $item instanceof Consumable ? 'consumable' : 'non_consumable';

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'product_code' => $item->product_code,
                'brand' => $item->brand,
                'category' => $item->category ? $item->category->name : 'N/A',
                'quantity' => $item->quantity,
                'min_stock' => $item->min_stock,
                'location' => $item->location ?? 'N/A',
                'condition' => $item->condition ?? 'N/A',
                'description' => $item->description,
                'item_type' => $item->item_type,
            ]
        ]);
    }

    /**
     * Show assignment form for an item.
     */
    public function showAssignForm($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $users = \App\Models\User::orderBy('name')->get();
        $offices = \App\Models\Office::orderBy('name')->get();

        return view('admin.items.assign', compact('item', 'users', 'offices'));
    }

    /**
     * Assign item to a user.
     */
    public function assign(Request $request, $id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);

        // For non-consumables, update the current_holder_id
        if ($item instanceof NonConsumable) {
            $item->update([
                'current_holder_id' => $user->id,
                'location' => $request->filled('location') ? $request->location : $item->location
            ]);
        } else {
            // For consumables, just update location if provided
            if ($request->filled('location')) {
                $item->update(['location' => $request->location]);
            }
        }

        return redirect()->route('items.show', $item)
            ->with('success', "Item '{$item->name}' has been assigned to {$user->name}.");
    }

    /**
     * Unassign item from current holder.
     */
    public function unassign($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            abort(404, 'Item not found');
        }

        // For non-consumables, check if assigned and unassign
        if ($item instanceof NonConsumable) {
            if (!$item->current_holder_id) {
                return redirect()->back()
                    ->with('error', 'Item is not currently assigned to anyone.');
            }

            $holderName = $item->currentHolder->name;
            $item->update(['current_holder_id' => null]);
        } else {
            // For consumables, just return success (they don't have holders)
            return redirect()->route('items.show', $item)
                ->with('error', 'Consumable items cannot be assigned to users.');
        }

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
            'item_ids.*' => 'required|integer'
        ]);

        try {
            $itemIds = $request->item_ids;

            // Find trashed items in both models
            $trashedConsumables = Consumable::onlyTrashed()->whereIn('id', $itemIds)->get();
            $trashedNonConsumables = NonConsumable::onlyTrashed()->whereIn('id', $itemIds)->get();
            $trashedItems = $trashedConsumables->merge($trashedNonConsumables);

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
            // Try to find the trashed item in consumables first
            $item = Consumable::onlyTrashed()->find($id);

            // If not found in consumables, try non-consumables
            if (!$item) {
                $item = NonConsumable::onlyTrashed()->find($id);
            }

            // If still not found, return error
            if (!$item) {
                return redirect()->route('items.trashed')
                    ->with('error', 'The item you are trying to delete was not found in the trash.');
            }

            // Store item name for logging and messages
            $itemName = $item->name;
            $itemType = $item instanceof Consumable ? 'consumable' : 'non_consumable';

            // Log the permanent deletion (before actually deleting)
            Log::warning('Item permanently deleted from trash', [
                'item_id' => $item->id,
                'item_name' => $itemName,
                'item_type' => $itemType,
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
                'warning' => 'This action cannot be undone'
            ]);

            // Permanently delete the item
            $item->forceDelete();

            return redirect()->route('items.trashed')
                ->with('success', "Item '{$itemName}' has been permanently deleted and cannot be recovered.");

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
            'item_ids.*' => 'required|integer'
        ]);

        try {
            $itemIds = $request->item_ids;

            // Find trashed items in both models
            $trashedConsumables = Consumable::onlyTrashed()->whereIn('id', $itemIds)->get();
            $trashedNonConsumables = NonConsumable::onlyTrashed()->whereIn('id', $itemIds)->get();
            $trashedItems = $trashedConsumables->merge($trashedNonConsumables);

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
                $itemType = $item instanceof Consumable ? 'consumable' : 'non_consumable';

                // Log each deletion
                Log::warning('Item permanently deleted from trash (bulk operation)', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_type' => $itemType,
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
