<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsageReportsController extends Controller
{
    /**
     * Display usage reports dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $period = $request->get('period', 'last_30_days');
        $categoryId = $request->get('category_id');
        $limit = $request->get('limit', 20);
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($period);
        
        // Get usage analytics
        $analytics = $this->getUsageAnalytics($dateRange, $categoryId, $limit);
        
        // Get categories for filter
        $categories = Category::orderBy('name')->get();
        
        // Get overall statistics
        $stats = $this->getUsageStats($dateRange);
        
        return view('admin.reports.usage', compact('analytics', 'categories', 'stats', 'period'));
    }
    
    /**
     * Get usage analytics data
     */
    private function getUsageAnalytics($dateRange, $categoryId = null, $limit = 20)
    {
        // Base query for requests within date range
        $requestQuery = SupplyRequest::with(['item.category'])
            ->whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'rejected'); // Only count approved/pending requests
        
        // Apply category filter if specified
        if ($categoryId) {
            $requestQuery->whereHas('item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        // Get most requested items
        /** @var \Illuminate\Support\Collection<int, object{item_id: int, request_count: int, total_quantity: int}> $rawResults */
        $rawResults = $requestQuery->select('item_id', DB::raw('COUNT(*) as request_count'), DB::raw('SUM(quantity_requested) as total_quantity'))
            ->groupBy('item_id')
            ->orderBy('request_count', 'desc')
            ->limit($limit)
            ->get();
            
        $mostRequested = $rawResults->map(function ($request) {
                $item = Item::with('category')->find($request->item_id);
                return [
                    'item' => $item,
                    'request_count' => $request->request_count,
                    'total_quantity' => $request->total_quantity,
                    'average_per_request' => round($request->total_quantity / $request->request_count, 2),
                    'usage_score' => $this->calculateUsageScore($request->request_count, $request->total_quantity)
                ];
            })
            ->filter(function ($item) {
                return $item['item'] !== null; // Filter out deleted items
            });
        
        // Get all items to identify least used ones
        $allItemsQuery = Item::with('category');
        if ($categoryId) {
            $allItemsQuery->where('category_id', $categoryId);
        }
        
        $allItems = $allItemsQuery->get();
        $requestedItemIds = $mostRequested->pluck('item.id')->toArray();
        
        // Get least requested items (items with no or few requests)
        $leastRequested = $allItems->filter(function ($item) use ($requestedItemIds) {
            return !in_array($item->id, $requestedItemIds);
        })->map(function ($item) use ($dateRange) {
            $requestCount = SupplyRequest::where('item_id', $item->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', '!=', 'rejected')
                ->count();
            
            $totalQuantity = SupplyRequest::where('item_id', $item->id)
                ->whereBetween('created_at', $dateRange)
                ->where('status', '!=', 'rejected')
                ->sum('quantity_requested') ?? 0;
            
            return [
                'item' => $item,
                'request_count' => $requestCount,
                'total_quantity' => $totalQuantity,
                'average_per_request' => $requestCount > 0 ? round($totalQuantity / $requestCount, 2) : 0,
                'usage_score' => $this->calculateUsageScore($requestCount, $totalQuantity),
                'days_since_last_request' => $this->getDaysSinceLastRequest($item->id, $dateRange)
            ];
        })
        ->sortBy('usage_score')
        ->take($limit);
        
        // Get usage trends (daily usage over the period)
        $usageTrends = $this->getUsageTrends($dateRange, $categoryId);
        
        // Get category usage breakdown
        $categoryUsage = $this->getCategoryUsageBreakdown($dateRange);
        
        return [
            'most_requested' => $mostRequested,
            'least_requested' => $leastRequested,
            'usage_trends' => $usageTrends,
            'category_usage' => $categoryUsage
        ];
    }
    
    /**
     * Calculate usage score based on request frequency and quantity
     */
    private function calculateUsageScore($requestCount, $totalQuantity)
    {
        // Weight: 70% request frequency, 30% total quantity
        return ($requestCount * 0.7) + ($totalQuantity * 0.3);
    }
    
    /**
     * Get days since last request for an item
     */
    private function getDaysSinceLastRequest($itemId, $dateRange)
    {
        $lastRequest = SupplyRequest::where('item_id', $itemId)
            ->where('status', '!=', 'rejected')
            ->latest('created_at')
            ->first();
        
        if (!$lastRequest) {
            return null; // Never requested
        }
        
        return Carbon::now()->diffInDays($lastRequest->created_at);
    }
    
    /**
     * Get usage trends over time
     */
    private function getUsageTrends($dateRange, $categoryId = null)
    {
        $query = SupplyRequest::whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'rejected');
        
        if ($categoryId) {
            $query->whereHas('item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        
        // Group by date
        $trends = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as request_count'),
                DB::raw('SUM(quantity_requested) as total_quantity')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($trend) {
                return [
                    'date' => Carbon::parse($trend->date)->format('M j'),
                    'request_count' => $trend->request_count,
                    'total_quantity' => $trend->total_quantity
                ];
            });
        
        return $trends;
    }
    
    /**
     * Get category usage breakdown
     */
    private function getCategoryUsageBreakdown($dateRange)
    {
        return SupplyRequest::join('items', 'requests.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->whereBetween('requests.created_at', $dateRange)
            ->where('requests.status', '!=', 'rejected')
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(*) as request_count'),
                DB::raw('SUM(requests.quantity_requested) as total_quantity')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('request_count', 'desc')
            ->get();
    }
    
    /**
     * Get overall usage statistics
     */
    private function getUsageStats($dateRange)
    {
        $totalRequests = SupplyRequest::whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'rejected')
            ->count();
        
        $totalItems = SupplyRequest::whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'rejected')
            ->distinct('item_id')
            ->count();
        
        $averageRequestsPerDay = $totalRequests / max(1, Carbon::parse($dateRange[0])->diffInDays(Carbon::parse($dateRange[1])));
        
        /** @var object{item_id: int, count: int, item: \App\Models\Item}|null $topRequestedItem */
        $topRequestedItem = SupplyRequest::with('item')
            ->whereBetween('created_at', $dateRange)
            ->where('status', '!=', 'rejected')
            ->select('item_id', DB::raw('COUNT(*) as count'))
            ->groupBy('item_id')
            ->orderBy('count', 'desc')
            ->first();
        
        return [
            'total_requests' => $totalRequests,
            'total_items_requested' => $totalItems,
            'average_requests_per_day' => round($averageRequestsPerDay, 1),
            'top_requested_item' => $topRequestedItem ? $topRequestedItem->item : null,
            'top_requested_count' => $topRequestedItem ? $topRequestedItem->count : 0
        ];
    }
    
    /**
     * Get date range based on period
     */
    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'last_7_days':
                return [$now->copy()->subDays(7), $now];
            case 'last_30_days':
                return [$now->copy()->subDays(30), $now];
            case 'last_90_days':
                return [$now->copy()->subDays(90), $now];
            case 'last_6_months':
                return [$now->copy()->subMonths(6), $now];
            case 'last_year':
                return [$now->copy()->subYear(), $now];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now];
            default:
                return [$now->copy()->subDays(30), $now];
        }
    }
    
    /**
     * Export usage report to CSV
     */
    public function exportCsv(Request $request)
    {
        $period = $request->get('period', 'last_30_days');
        $categoryId = $request->get('category_id');
        $dateRange = $this->getDateRange($period);
        
        $analytics = $this->getUsageAnalytics($dateRange, $categoryId, 100); // Get more items for export
        
        $filename = 'usage_report_' . $period . '_' . Carbon::now()->format('Y_m_d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($analytics) {
            $file = fopen('php://output', 'w');
            
            // Most Requested Items
            fputcsv($file, ['MOST REQUESTED ITEMS']);
            fputcsv($file, [
                'Item Name',
                'Category',
                'Request Count',
                'Total Quantity',
                'Average per Request',
                'Usage Score'
            ]);
            
            foreach ($analytics['most_requested'] as $item) {
                fputcsv($file, [
                    $item['item']->name,
                    $item['item']->category->name,
                    $item['request_count'],
                    $item['total_quantity'],
                    $item['average_per_request'],
                    number_format($item['usage_score'], 2)
                ]);
            }
            
            // Add empty row
            fputcsv($file, []);
            
            // Least Requested Items
            fputcsv($file, ['LEAST REQUESTED ITEMS']);
            fputcsv($file, [
                'Item Name',
                'Category',
                'Request Count',
                'Total Quantity',
                'Days Since Last Request',
                'Usage Score'
            ]);
            
            foreach ($analytics['least_requested'] as $item) {
                fputcsv($file, [
                    $item['item']->name,
                    $item['item']->category->name,
                    $item['request_count'],
                    $item['total_quantity'],
                    $item['days_since_last_request'] ?? 'Never',
                    number_format($item['usage_score'], 2)
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
