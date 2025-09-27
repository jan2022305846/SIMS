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
     * Display trashed items.
     */
    public function trashed()
    {
        $items = Item::onlyTrashed()->with('category')->paginate(10);
        return view('admin.items.trashed', compact('items'));
    }

    /**
     * Restore a trashed item.
     */
    public function restore($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();

        return redirect()->route('items.trashed')
            ->with('success', 'Item restored successfully.');
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
     * Update item location.
     */
    public function updateLocation(Request $request, Item $item)
    {
        $request->validate([
            'location' => 'required|string|max:255',
        ]);

        $oldLocation = $item->location;
        $item->update(['location' => $request->location]);

        return redirect()->route('items.show', $item)
            ->with('success', "Item location updated from '{$oldLocation}' to '{$request->location}'.");
    }
}
