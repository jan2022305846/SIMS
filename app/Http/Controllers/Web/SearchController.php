<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Request as SupplyRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Display global search page
     */
    public function index()
    {
        return view('admin.search.index');
    }

    /**
     * Perform global search across all sections
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
            'sections' => 'sometimes|array',
            'filters' => 'sometimes|array'
        ]);

        $query = trim($request->input('query'));
        $sections = $request->input('sections', ['users', 'items', 'categories', 'requests']);
        $filters = $request->input('filters', []);

        $results = [];

        try {
            // Search in selected sections
            if (in_array('users', $sections)) {
                $results['users'] = $this->searchUsers($query, $filters);
            }

            if (in_array('items', $sections)) {
                $results['items'] = $this->searchItems($query, $filters);
            }

            if (in_array('categories', $sections)) {
                $results['categories'] = $this->searchCategories($query, $filters);
            }

            if (in_array('requests', $sections)) {
                $results['requests'] = $this->searchRequests($query, $filters);
            }

            if (in_array('activity_logs', $sections)) {
                $results['activity_logs'] = $this->searchActivityLogs($query, $filters);
            }

            // Log search activity
            ActivityLog::log("Global search performed: '{$query}'")
                ->inLog('search')
                ->withProperties([
                    'query' => $query,
                    'sections' => $sections,
                    'filters' => $filters,
                    'result_counts' => array_map(function($items) {
                        return count($items);
                    }, $results)
                ])
                ->save();

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'total_results' => array_sum(array_map('count', $results))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quick search suggestions
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:100'
        ]);

        $query = trim($request->input('query'));
        $suggestions = [];

        try {
            // Get suggestions from various sources
            $suggestions['users'] = $this->getUserSuggestions($query);
            $suggestions['items'] = $this->getItemSuggestions($query);
            $suggestions['categories'] = $this->getCategorySuggestions($query);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'suggestions' => []
            ]);
        }
    }

    /**
     * Export search results
     */
    public function export(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'sections' => 'required|array',
            'format' => 'required|in:csv,json,excel'
        ]);

        $query = $request->input('query');
        $sections = $request->input('sections');
        $format = $request->input('format');

        try {
            $results = [];

            // Gather all search results
            foreach ($sections as $section) {
                switch ($section) {
                    case 'users':
                        $results['users'] = $this->searchUsers($query, []);
                        break;
                    case 'items':
                        $results['items'] = $this->searchItems($query, []);
                        break;
                    case 'categories':
                        $results['categories'] = $this->searchCategories($query, []);
                        break;
                    case 'requests':
                        $results['requests'] = $this->searchRequests($query, []);
                        break;
                }
            }

            // Generate export based on format
            $filename = 'search_results_' . date('Y_m_d_H_i_s');
            
            switch ($format) {
                case 'json':
                    return $this->exportAsJson($results, $filename);
                case 'csv':
                    return $this->exportAsCsv($results, $filename);
                case 'excel':
                    return $this->exportAsExcel($results, $filename);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users
     */
    private function searchUsers($query, $filters = [])
    {
        $users = User::query();

        // Apply search terms
        $users->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('employee_id', 'LIKE', "%{$query}%")
              ->orWhere('department', 'LIKE', "%{$query}%")
              ->orWhere('office', 'LIKE', "%{$query}%");
        });

        // Apply filters
        if (isset($filters['role']) && !empty($filters['role'])) {
            $users->where('role', $filters['role']);
        }

        if (isset($filters['department']) && !empty($filters['department'])) {
            $users->where('department', $filters['department']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $users->whereNotNull('email_verified_at');
            } else {
                $users->whereNull('email_verified_at');
            }
        }

        return $users->limit(50)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'type' => 'user',
                'title' => $user->name,
                'subtitle' => $user->email,
                'description' => $user->department . ' - ' . $user->office,
                'meta' => [
                    'employee_id' => $user->employee_id,
                    'role' => $user->role,
                    'department' => $user->department,
                    'office' => $user->office
                ],
                'url' => route('admin.users.show', $user->id),
                'created_at' => $user->created_at
            ];
        });
    }

    /**
     * Search items
     */
    private function searchItems($query, $filters = [])
    {
        $items = Item::with('category');

        // Apply search terms
        $items->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%")
              ->orWhere('item_code', 'LIKE', "%{$query}%")
              ->orWhere('unit', 'LIKE', "%{$query}%")
              ->orWhereHas('category', function($categoryQuery) use ($query) {
                  $categoryQuery->where('name', 'LIKE', "%{$query}%");
              });
        });

        // Apply filters
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $items->where('category_id', $filters['category_id']);
        }

        if (isset($filters['stock_status']) && !empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $items->where('current_stock', '>', 0);
                    break;
                case 'low_stock':
                    $items->whereRaw('current_stock <= minimum_stock AND current_stock > 0');
                    break;
                case 'out_of_stock':
                    $items->where('current_stock', 0);
                    break;
            }
        }

        return $items->limit(50)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'type' => 'item',
                'title' => $item->name,
                'subtitle' => $item->item_code,
                'description' => $item->description,
                'meta' => [
                    'category' => $item->category->name ?? 'Uncategorized',
                    'current_stock' => $item->current_stock,
                    'minimum_stock' => $item->minimum_stock,
                    'unit' => $item->unit,
                    'stock_status' => $this->getStockStatus($item)
                ],
                'url' => route('admin.items.show', $item->id),
                'created_at' => $item->created_at
            ];
        });
    }

    /**
     * Search categories
     */
    private function searchCategories($query, $filters = [])
    {
        $categories = Category::withCount('items');

        // Apply search terms
        $categories->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%");
        });

        return $categories->limit(50)->get()->map(function($category) {
            return [
                'id' => $category->id,
                'type' => 'category',
                'title' => $category->name,
                'subtitle' => 'Category',
                'description' => $category->description,
                'meta' => [
                    'items_count' => $category->items_count,
                    'created_at' => $category->created_at->format('M d, Y')
                ],
                'url' => route('admin.categories.show', $category->id),
                'created_at' => $category->created_at
            ];
        });
    }

    /**
     * Search requests
     */
    private function searchRequests($query, $filters = [])
    {
        $requests = SupplyRequest::with(['user', 'item']);

        // Apply search terms
        $requests->where(function($q) use ($query) {
            $q->where('purpose', 'LIKE', "%{$query}%")
              ->orWhere('status', 'LIKE', "%{$query}%")
              ->orWhereHas('user', function($userQuery) use ($query) {
                  $userQuery->where('name', 'LIKE', "%{$query}%")
                           ->orWhere('email', 'LIKE', "%{$query}%");
              })
              ->orWhereHas('item', function($itemQuery) use ($query) {
                  $itemQuery->where('name', 'LIKE', "%{$query}%")
                           ->orWhere('item_code', 'LIKE', "%{$query}%");
              });
        });

        // Apply filters
        if (isset($filters['status']) && !empty($filters['status'])) {
            $requests->where('status', $filters['status']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $requests->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $requests->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $requests->limit(50)->get()->map(function($request) {
            return [
                'id' => $request->id,
                'type' => 'request',
                'title' => $request->item->name ?? 'Unknown Item',
                'subtitle' => 'Request by ' . ($request->user->name ?? 'Unknown User'),
                'description' => $request->purpose,
                'meta' => [
                    'status' => $request->status,
                    'quantity' => $request->quantity,
                    'user' => $request->user->name ?? 'Unknown',
                    'requested_date' => $request->created_at->format('M d, Y'),
                    'status_class' => $this->getStatusClass($request->status)
                ],
                'url' => route('admin.requests.show', $request->id),
                'created_at' => $request->created_at
            ];
        });
    }

    /**
     * Search activity logs
     */
    private function searchActivityLogs($query, $filters = [])
    {
        $logs = ActivityLog::with('causer');

        // Apply search terms
        $logs->where(function($q) use ($query) {
            $q->where('description', 'LIKE', "%{$query}%")
              ->orWhere('log_name', 'LIKE', "%{$query}%")
              ->orWhereJsonContains('properties', $query)
              ->orWhereHas('causer', function($userQuery) use ($query) {
                  $userQuery->where('name', 'LIKE', "%{$query}%");
              });
        });

        // Apply filters
        if (isset($filters['log_name']) && !empty($filters['log_name'])) {
            $logs->where('log_name', $filters['log_name']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $logs->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $logs->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $logs->limit(50)->get()->map(function($log) {
            return [
                'id' => $log->id,
                'type' => 'activity_log',
                'title' => $log->description,
                'subtitle' => 'Activity Log - ' . ucwords(str_replace('_', ' ', $log->log_name)),
                'description' => 'By ' . ($log->causer->name ?? 'System'),
                'meta' => [
                    'log_name' => $log->log_name,
                    'causer' => $log->causer->name ?? 'System',
                    'date' => $log->created_at->format('M d, Y H:i'),
                    'properties' => $log->properties
                ],
                'url' => '#',
                'created_at' => $log->created_at
            ];
        });
    }

    /**
     * Get user suggestions
     */
    private function getUserSuggestions($query)
    {
        return User::where('name', 'LIKE', "%{$query}%")
                   ->orWhere('email', 'LIKE', "%{$query}%")
                   ->limit(5)
                   ->pluck('name')
                   ->toArray();
    }

    /**
     * Get item suggestions
     */
    private function getItemSuggestions($query)
    {
        return Item::where('name', 'LIKE', "%{$query}%")
                   ->orWhere('item_code', 'LIKE', "%{$query}%")
                   ->limit(5)
                   ->pluck('name')
                   ->toArray();
    }

    /**
     * Get category suggestions
     */
    private function getCategorySuggestions($query)
    {
        return Category::where('name', 'LIKE', "%{$query}%")
                       ->limit(5)
                       ->pluck('name')
                       ->toArray();
    }

    /**
     * Helper method to get stock status
     */
    private function getStockStatus($item)
    {
        if ($item->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($item->current_stock <= $item->minimum_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Helper method to get status CSS class
     */
    private function getStatusClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'approved':
                return 'info';
            case 'fulfilled':
                return 'success';
            case 'rejected':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Export methods
     */
    private function exportAsJson($results, $filename)
    {
        $json = json_encode($results, JSON_PRETTY_PRINT);
        
        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
    }

    private function exportAsCsv($results, $filename)
    {
        $csv = '';
        $header = ['Type', 'Title', 'Subtitle', 'Description', 'URL', 'Created At'];
        $csv .= implode(',', $header) . "\n";

        foreach ($results as $section => $items) {
            foreach ($items as $item) {
                $row = [
                    $item['type'],
                    '"' . str_replace('"', '""', $item['title']) . '"',
                    '"' . str_replace('"', '""', $item['subtitle']) . '"',
                    '"' . str_replace('"', '""', $item['description']) . '"',
                    $item['url'],
                    $item['created_at']
                ];
                $csv .= implode(',', $row) . "\n";
            }
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
    }

    private function exportAsExcel($results, $filename)
    {
        // For now, return CSV format (Excel can read CSV)
        // In the future, you could integrate a library like PhpSpreadsheet
        return $this->exportAsCsv($results, $filename);
    }
}