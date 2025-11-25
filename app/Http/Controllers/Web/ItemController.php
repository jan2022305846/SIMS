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
                // Only show consumables - apply all filters to consumables query
                $consumables = $consumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'consumable';
                    return $item;
                });
                $nonConsumables = collect(); // Empty collection for non-consumables
            } elseif ($request->type === 'non_consumable') {
                // Only show non-consumables - apply all filters to non-consumables query
                $consumables = collect(); // Empty collection for consumables
                $nonConsumables = $nonConsumablesQuery->get()->map(function ($item) {
                    $item->item_type = 'non_consumable';
                    return $item;
                });
            } else {
                // Show all types - apply all filters to both queries
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
            // No type filter - default to showing only consumables
            $consumables = $consumablesQuery->get()->map(function ($item) {
                $item->item_type = 'consumable';
                return $item;
            });
            $nonConsumables = collect(); // Empty collection for non-consumables
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
            'unit' => 'required|string|max:50',
            'product_code' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'brand' => 'nullable|string|max:255',
        ];

        // Add conditional validation for non-consumables
        if ($request->item_type === 'non_consumable') {
            $rules['location'] = 'required|string|max:255';
            $rules['condition'] = 'required|in:New,Good,Fair,Needs Repair';
            // Remove stock management requirements for non-consumables
            unset($rules['quantity'], $rules['min_stock'], $rules['max_stock']);
        } else {
            // For consumables, stock management fields are required
            $rules['quantity'] = 'required|integer|min:0';
            $rules['min_stock'] = 'required|integer|min:0';
        }

        $request->validate($rules);

        $data = $request->all();
        
        // Generate unique QR code
        $data['qr_code'] = Str::uuid();

        // Set default values for non-consumable items
        if ($request->item_type === 'non_consumable') {
            $data['quantity'] = 1;
            $data['min_stock'] = 0;
            $data['max_stock'] = 1;
        }

        // Handle product_code: auto-generate if empty or duplicate
        if (empty($data['product_code'])) {
            // Generate a unique product code if none provided
            do {
                $data['product_code'] = 'PC-' . strtoupper(Str::random(8));
            } while (
                Consumable::where('product_code', $data['product_code'])->exists() ||
                NonConsumable::where('product_code', $data['product_code'])->exists()
            );
        } else {
            // Check if the provided product_code already exists
            $existsInConsumables = Consumable::where('product_code', $data['product_code'])->exists();
            $existsInNonConsumables = NonConsumable::where('product_code', $data['product_code'])->exists();
            
            if ($existsInConsumables || $existsInNonConsumables) {
                // Auto-generate a new unique product code
                do {
                    $data['product_code'] = 'PC-' . strtoupper(Str::random(8));
                } while (
                    Consumable::where('product_code', $data['product_code'])->exists() ||
                    NonConsumable::where('product_code', $data['product_code'])->exists()
                );
            }
        }

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
        $type = request('type'); // Get the type from query parameter

        // If type is specified, search the appropriate table first
        if ($type === 'consumable') {
            $item = Consumable::with('category')->find($id);
            if (!$item) {
                // If not found in consumables, try non-consumables as fallback
                $item = NonConsumable::with('category')->find($id);
            }
        } elseif ($type === 'non_consumable') {
            $item = NonConsumable::with('category')->find($id);
            if (!$item) {
                // If not found in non-consumables, try consumables as fallback
                $item = Consumable::with('category')->find($id);
            }
        } else {
            // No type specified - use the original logic
            // First try to find in consumables
            $consumableItem = Consumable::with('category')->find($id);

            // Then try to find in non-consumables
            $nonConsumableItem = NonConsumable::with('category')->find($id);

            // Determine which item to show based on availability and context
            if ($consumableItem && $nonConsumableItem) {
                // Both exist - this shouldn't happen in a properly normalized database
                // but if it does, prioritize based on some logic (e.g., most recently updated)
                $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
            } elseif ($consumableItem) {
                $item = $consumableItem;
            } elseif ($nonConsumableItem) {
                $item = $nonConsumableItem;
            } else {
                abort(404, 'Item not found');
            }
        }

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
        $type = request('type'); // Get the type from query parameter

        // If type is specified, search the appropriate table first
        if ($type === 'consumable') {
            $item = Consumable::find($id);
            if (!$item) {
                // If not found in consumables, try non-consumables as fallback
                $item = NonConsumable::find($id);
            }
        } elseif ($type === 'non_consumable') {
            $item = NonConsumable::find($id);
            if (!$item) {
                // If not found in non-consumables, try consumables as fallback
                $item = Consumable::find($id);
            }
        } else {
            // No type specified - use the original logic
            // First try to find in consumables
            $consumableItem = Consumable::find($id);

            // Then try to find in non-consumables
            $nonConsumableItem = NonConsumable::find($id);

            // Determine which item to edit based on availability
            if ($consumableItem && $nonConsumableItem) {
                // Both exist - prioritize based on most recently updated
                $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
            } elseif ($consumableItem) {
                $item = $consumableItem;
            } elseif ($nonConsumableItem) {
                $item = $nonConsumableItem;
            } else {
                abort(404, 'Item not found');
            }
        }

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
        $type = request('type'); // Get the type from query parameter

        // If type is specified, search the appropriate table first
        if ($type === 'consumable') {
            $item = Consumable::find($id);
            if (!$item) {
                // If not found in consumables, try non-consumables as fallback
                $item = NonConsumable::find($id);
            }
        } elseif ($type === 'non_consumable') {
            $item = NonConsumable::find($id);
            if (!$item) {
                // If not found in non-consumables, try consumables as fallback
                $item = Consumable::find($id);
            }
        } else {
            // No type specified - use the original logic
            // First try to find in consumables
            $consumableItem = Consumable::find($id);

            // Then try to find in non-consumables
            $nonConsumableItem = NonConsumable::find($id);

            // Determine which item to update based on availability
            if ($consumableItem && $nonConsumableItem) {
                // Both exist - prioritize based on most recently updated
                $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
            } elseif ($consumableItem) {
                $item = $consumableItem;
            } elseif ($nonConsumableItem) {
                $item = $nonConsumableItem;
            } else {
                abort(404, 'Item not found');
            }
        }

        if (!$item) {
            abort(404, 'Item not found');
        }

        $isConsumable = $item instanceof Consumable;

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit' => 'required|string|max:50',
            'product_code' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'add_quantity' => 'nullable|integer|min:0',
        ];

        // Add conditional validation for non-consumables
        if (!$isConsumable) {
            $rules['location'] = 'required|string|max:255';
            $rules['condition'] = 'required|in:New,Good,Fair,Needs Repair';
            // Remove stock management requirements for non-consumables
            unset($rules['min_stock'], $rules['max_stock'], $rules['add_quantity']);
        } else {
            // For consumables, stock management fields are required
            $rules['min_stock'] = 'required|integer|min:0';
        }

        $request->validate($rules);

        // Prepare update data (exclude add_quantity)
        $updateData = $request->except(['add_quantity']);

        // Update the item
        $item->update($updateData);

        // Handle stock addition if provided
        if ($request->filled('add_quantity') && $request->add_quantity > 0) {
            $item->increment('quantity', $request->add_quantity);
            $message = 'Item updated successfully. Added ' . $request->add_quantity . ' ' . $item->unit . ' to stock.';
        } else {
            $message = 'Item updated successfully.';
        }

        return redirect()->route('items.show', $item->id . '?type=' . ($item instanceof Consumable ? 'consumable' : 'non_consumable'))
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // First try to find in consumables
        $consumableItem = Consumable::find($id);

        // Then try to find in non-consumables
        $nonConsumableItem = NonConsumable::find($id);

        // Determine which item to delete based on availability
        if ($consumableItem && $nonConsumableItem) {
            // Both exist - prioritize based on most recently updated
            $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
        } elseif ($consumableItem) {
            $item = $consumableItem;
        } elseif ($nonConsumableItem) {
            $item = $nonConsumableItem;
        } else {
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

        // Filter out assigned non-consumable items (they shouldn't be requestable)
        $nonConsumables = $nonConsumables->filter(function ($item) {
            return !$item->isAssigned();
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
        // Get low stock consumables only - items where quantity <= min_stock (or <= 10 if min_stock not set)
        // Non-consumable items are excluded since they always have quantity 1 and don't need low stock monitoring
        $consumables = Consumable::with('category')
            ->whereRaw('quantity <= COALESCE(min_stock, 10)')
            ->get()
            ->map(function ($item) {
                $item->item_type = 'consumable';
                $item->current_stock = $item->quantity;
                $item->minimum_stock = $item->min_stock;
                return $item;
            });

        // Sort results by quantity (lowest first)
        $allItems = $consumables->sortBy('quantity');

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
     * Display items available for disposal.
     *
     * Shows non-consumable items with "Needs Repair" condition that can be disposed.
     * Items in this list can be permanently deleted from the system.
     *
     * @param Request $request The HTTP request instance
     * @return \Illuminate\View\View
     */
    public function disposal(Request $request)
    {
        // Get non-consumable items with "Needs Repair" condition
        $disposalItemsQuery = NonConsumable::with('category')
            ->where('condition', 'Needs Repair');

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $disposalItemsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $disposalItemsQuery->where('category_id', $request->category);
        }

        // Get results and add type indicator
        $disposalItems = $disposalItemsQuery->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            return $item;
        });

        // Sort by name
        $disposalItems = $disposalItems->sortBy('name');

        // Manual pagination
        $perPage = 15;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $disposalItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $disposalItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        $items->appends(request()->query());

        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();

        return view('admin.items.disposal', compact('items', 'categories'));
    }

    /**
     * Display trashed (soft-deleted) items.
     *
     * Shows items that have been soft-deleted and can be restored or permanently deleted.
     * Items are displayed with their deletion information and restoration options.
     *
     * @param Request $request The HTTP request instance
     * @return \Illuminate\View\View
     */
    public function trashed(Request $request)
    {
        // Get trashed items from both consumables and non-consumables
        $trashedConsumablesQuery = Consumable::onlyTrashed()->with('category');
        $trashedNonConsumablesQuery = NonConsumable::onlyTrashed()->with('category');

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $trashedConsumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $searchTerm . '%');
            });
            $trashedNonConsumablesQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('product_code', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $trashedConsumablesQuery->where('category_id', $request->category);
            $trashedNonConsumablesQuery->where('category_id', $request->category);
        }

        // Get results and add type indicators and additional attributes
        $trashedConsumables = $trashedConsumablesQuery->get()->map(function ($item) {
            $item->item_type = 'consumable';
            $item->current_stock = $item->quantity;
            $item->barcode = $item->product_code;
            return $item;
        });

        $trashedNonConsumables = $trashedNonConsumablesQuery->get()->map(function ($item) {
            $item->item_type = 'non_consumable';
            $item->current_stock = $item->quantity;
            $item->barcode = $item->product_code;
            return $item;
        });

        // Merge and sort by deletion date (newest first)
        $allTrashedItems = $trashedConsumables->merge($trashedNonConsumables)
            ->sortByDesc('deleted_at');

        // Manual pagination
        $perPage = 15;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $allTrashedItems->slice($offset, $perPage);

        // Create a LengthAwarePaginator
        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $allTrashedItems->count(),
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
     * Process disposal of selected items.
     *
     * Permanently deletes selected non-consumable items with "Needs Repair" condition
     * and generates a DOCX disposal report.
     *
     * @param Request $request The HTTP request instance
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function processDisposal(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer'
        ]);

        try {
            $itemIds = $request->item_ids;

            // Find items that are eligible for disposal (non-consumable with "Needs Repair" condition)
            $eligibleItems = NonConsumable::whereIn('id', $itemIds)
                ->where('condition', 'Needs Repair')
                ->get();

            if ($eligibleItems->isEmpty()) {
                return redirect()->route('items.disposal')
                    ->with('error', 'No eligible items found for disposal.');
            }

            $disposedCount = 0;
            $skippedCount = count($itemIds) - $eligibleItems->count();
            $disposedItems = [];

            // Store disposal data for DOCX generation
            foreach ($eligibleItems as $item) {
                $disposedItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand,
                    'product_code' => $item->product_code,
                    'category' => $item->category ? $item->category->name : 'Uncategorized',
                    'location' => $item->location,
                    'condition' => $item->condition,
                    'quantity' => $item->quantity,
                    'description' => $item->description,
                    'disposed_at' => now()->format('Y-m-d H:i:s'),
                    'disposed_by' => Auth::user()->name
                ];

                // Log the disposal
                Log::warning('Item disposed permanently', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_type' => 'non_consumable',
                    'condition' => $item->condition,
                    'disposed_by' => Auth::id(),
                    'disposed_at' => now(),
                    'warning' => 'Item permanently deleted from disposal process'
                ]);

                // Permanently delete the item
                $item->forceDelete();
                $disposedCount++;
            }

            // Generate DOCX disposal report
            $docxFile = $this->generateDisposalReport($disposedItems);

            // Build success message
            $message = "Successfully disposed of {$disposedCount} item" . ($disposedCount > 1 ? 's' : '') . '.';
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} item" . ($skippedCount > 1 ? 's were' : ' was') . ' skipped (not eligible for disposal).';
            }

            return response()->download($docxFile)->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Failed to process item disposal', [
                'item_ids' => $request->item_ids ?? [],
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('items.disposal')
                ->with('error', 'An error occurred during disposal. Please try again.');
        }
    }

    /**
     * Generate DOCX disposal report.
     *
     * Creates a DOCX document listing all disposed items with their details.
     *
     * @param array $disposedItems Array of disposed item data
     * @return string Path to the generated DOCX file
     */
    private function generateDisposalReport($disposedItems)
    {
        try {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();

            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('Supply Office System');
            $properties->setCompany('Supply Office');
            $properties->setTitle('Item Disposal Report');
            $properties->setDescription('Report of permanently disposed items');
            $properties->setSubject('Item Disposal');

            // Add a section
            $section = $phpWord->addSection();

            // Add header
            $header = $section->addHeader();
            $header->addText('SUPPLY OFFICE SYSTEM', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
            $header->addText('ITEM DISPOSAL REPORT', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
            $header->addText('Generated on: ' . now()->format('F j, Y \a\t g:i A'), ['size' => 10], ['alignment' => 'center']);
            $header->addText('', [], []); // Empty line

            // Add summary
            $section->addText('DISPOSAL SUMMARY', ['bold' => true, 'size' => 12], ['alignment' => 'left']);
            $section->addText('Total Items Disposed: ' . count($disposedItems), ['size' => 11]);
            $section->addText('Disposal Date: ' . now()->format('F j, Y'), ['size' => 11]);
            $section->addText('Processed By: ' . Auth::user()->name, ['size' => 11]);
            $section->addText('', [], []); // Empty line

            // Create table for disposed items
            $table = $section->addTable([
                'borderSize' => 6,
                'borderColor' => '000000',
                'cellMargin' => 80,
                'alignment' => 'left'
            ]);

            // Add table header
            $table->addRow();
            $table->addCell(1000)->addText('No.', ['bold' => true, 'size' => 10]);
            $table->addCell(2500)->addText('Item Name', ['bold' => true, 'size' => 10]);
            $table->addCell(1500)->addText('Brand', ['bold' => true, 'size' => 10]);
            $table->addCell(1500)->addText('Product Code', ['bold' => true, 'size' => 10]);
            $table->addCell(1500)->addText('Category', ['bold' => true, 'size' => 10]);
            $table->addCell(1500)->addText('Location', ['bold' => true, 'size' => 10]);
            $table->addCell(1200)->addText('Condition', ['bold' => true, 'size' => 10]);
            $table->addCell(800)->addText('Qty', ['bold' => true, 'size' => 10]);

            // Add table rows
            $counter = 1;
            foreach ($disposedItems as $item) {
                $table->addRow();
                $table->addCell(1000)->addText($counter++, ['size' => 9]);
                $table->addCell(2500)->addText($item['name'], ['size' => 9]);
                $table->addCell(1500)->addText($item['brand'] ?? 'N/A', ['size' => 9]);
                $table->addCell(1500)->addText($item['product_code'] ?? 'N/A', ['size' => 9]);
                $table->addCell(1500)->addText($item['category'], ['size' => 9]);
                $table->addCell(1500)->addText($item['location'] ?? 'N/A', ['size' => 9]);
                $table->addCell(1200)->addText($item['condition'], ['size' => 9]);
                $table->addCell(800)->addText($item['quantity'], ['size' => 9]);
            }

            // Add footer notes
            $section->addText('', [], []); // Empty line
            $section->addText('IMPORTANT NOTES:', ['bold' => true, 'size' => 11]);
            $section->addText('• All listed items have been permanently removed from the system database.', ['size' => 10]);
            $section->addText('• Items were disposed due to "Needs Repair" condition.', ['size' => 10]);
            $section->addText('• This action cannot be undone.', ['size' => 10]);
            $section->addText('• Please retain this document for audit purposes.', ['size' => 10]);

            // Generate filename with timestamp
            $filename = 'item_disposal_report_' . now()->format('Y-m-d_H-i-s') . '.docx';
            $filePath = storage_path('app/temp/' . $filename);

            // Ensure temp directory exists
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Save the document
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($filePath);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Failed to generate disposal report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            throw $e;
        }
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
                'unit' => $item->unit ?? 'pcs',
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
        // First try to find in consumables
        $consumableItem = Consumable::find($id);

        // Then try to find in non-consumables
        $nonConsumableItem = NonConsumable::find($id);

        // Determine which item to assign based on availability
        if ($consumableItem && $nonConsumableItem) {
            // Both exist - prioritize based on most recently updated
            $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
        } elseif ($consumableItem) {
            $item = $consumableItem;
        } elseif ($nonConsumableItem) {
            $item = $nonConsumableItem;
        } else {
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
        // First try to find in consumables
        $consumableItem = Consumable::find($id);

        // Then try to find in non-consumables
        $nonConsumableItem = NonConsumable::find($id);

        // Determine which item to assign based on availability
        if ($consumableItem && $nonConsumableItem) {
            // Both exist - prioritize based on most recently updated
            $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
        } elseif ($consumableItem) {
            $item = $consumableItem;
        } elseif ($nonConsumableItem) {
            $item = $nonConsumableItem;
        } else {
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

        return redirect()->route('items.show', $item->id . '?type=' . ($item instanceof Consumable ? 'consumable' : 'non_consumable'))
            ->with('success', "Item '{$item->name}' has been assigned to {$user->name}.");
    }

    /**
     * Unassign item from current holder.
     */
    public function unassign($id)
    {
        $type = request('type'); // Get the type from query parameter

        // If type is specified, search the appropriate table first
        if ($type === 'consumable') {
            $item = Consumable::find($id);
            if (!$item) {
                // If not found in consumables, try non-consumables as fallback
                $item = NonConsumable::find($id);
            }
        } elseif ($type === 'non_consumable') {
            $item = NonConsumable::find($id);
            if (!$item) {
                // If not found in non-consumables, try consumables as fallback
                $item = Consumable::find($id);
            }
        } else {
            // No type specified - use the original logic
            // First try to find in consumables
            $consumableItem = Consumable::find($id);

            // Then try to find in non-consumables
            $nonConsumableItem = NonConsumable::find($id);

            // Determine which item to unassign based on availability
            if ($consumableItem && $nonConsumableItem) {
                // Both exist - prioritize based on most recently updated
                $item = $consumableItem->updated_at > $nonConsumableItem->updated_at ? $consumableItem : $nonConsumableItem;
            } elseif ($consumableItem) {
                $item = $consumableItem;
            } elseif ($nonConsumableItem) {
                $item = $nonConsumableItem;
            } else {
                abort(404, 'Item not found');
            }
        }

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
            return redirect()->route('items.show', $item->id . '?type=' . ($item instanceof Consumable ? 'consumable' : 'non_consumable'))
                ->with('error', 'Consumable items cannot be assigned to users.');
        }

        return redirect()->route('items.show', $item->id . '?type=' . ($item instanceof Consumable ? 'consumable' : 'non_consumable'))
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

    public function import(Request $request){
        $request->validate([
            'csv_file' => 'required|file|mime:csv,txt|max:2048'
        ]);

        $file = $request->file('csv_file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data);

        $errors = [];
        $rowNumber = 1; // Start from 1 since header is already shifted

        foreach ($data as $row){
            $rowNumber++;
            $rowData = array_combine($header, $row);

            // Validate required fields
            if (!isset($rowData['name']) || empty($rowData['name'])) {
                $errors[] = "Row $rowNumber: Name is required";
                continue;
            }
            if (!isset($rowData['item_type']) || !in_array($rowData['item_type'], ['consumable', 'non_consumable'])) {
                $errors[] = "Row $rowNumber: Item type must be 'consumable' or 'non_consumable'";
                continue;
            }

            try{
                $itemData = [
                    'name' => $rowData['name'],
                    'category_id' => $rowData['category_id'] ?? null,
                    'description' => $rowData['description'] ?? null,
                    'quantity' => (int)($rowData['quantity'] ?? 1),
                    'unit' => $rowData['unit'] ?? null,
                    'brand' => $rowData['brand'] ?? null,
                    'min_stock' => (int)($rowData['min_stock'] ?? 0),
                    'max_stock' => (int)($rowData['max_stock'] ?? 0),
                ];

                if ($rowData['item_type'] === 'consumable') {
                    // Check if consumable already exists
                    $item = Consumable::where('name', $rowData['name'])->first();
                    if($item){
                        $item->increment('quantity', $itemData['quantity']);
                    }else{
                        Consumable::create($itemData);
                    }
                } else { // non_consumable
                    $itemData['location'] = $rowData['location'] ?? null;
                    $itemData['condition'] = $rowData['condition'] ?? null;

                    // Check if non-consumable already exists
                    $item = NonConsumable::where('name', $rowData['name'])->first();
                    if($item){
                        $item->increment('quantity', $itemData['quantity']);
                    }else{
                        NonConsumable::create($itemData);
                    }
                }
            }catch(\Exception $e){
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        if ($errors){
            return back()->withErrors($errors);
        }

        return back()->with('success','Items imported Successfully');
    }

    public function downloadTemplate()
    {
        $headers = ['name', 'item_type', 'category_id', 'quantity', 'unit', 'brand', 'min_stock', 'max_stock', 'description', 'location', 'condition'];
        $content = implode(',', $headers) . "\n";
        // Add sample row for consumable
        $sampleConsumable = ['Sample Consumable Item', 'consumable', '1', '10', 'pieces', 'Brand A', '5', '50', 'Sample description', '', ''];
        $content .= implode(',', $sampleConsumable) . "\n";
        // Add sample row for non-consumable
        $sampleNonConsumable = ['Sample Non-Consumable Item', 'non_consumable', '1', '1', 'unit', 'Brand B', '0', '1', 'Sample description', 'Office A', 'Good'];
        $content .= implode(',', $sampleNonConsumable) . "\n";
        return response($content)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename="items_template.csv"');
    }
}
