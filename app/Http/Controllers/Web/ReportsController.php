<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Request as SupplyRequest;
use App\Models\User;
use App\Models\Log;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Check if status column exists (MySQL 5.5 compatible)
     * Cache the result to avoid repeated queries
     */
    private function hasWorkflowColumn()
    {
        static $hasColumn = null;
        
        if ($hasColumn === null) {
            try {
                // Use a simple query that works with MySQL 5.5
                $result = DB::select("SHOW COLUMNS FROM requests LIKE 'status'");
                $hasColumn = count($result) > 0;
            } catch (\Exception $e) {
                // If there's any error, assume column doesn't exist
                $hasColumn = false;
            }
        }
        
        return $hasColumn;
    }

    /**
     * Get appropriate status query based on available columns
     */
    private function getRequestsByStatus($query, $status)
    {
        switch ($status) {
            case 'pending':
                return $query->where('status', 'pending');
            case 'fulfilled':
                return $query->where('status', 'fulfilled');
            case 'approved':
                return $query->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed']);
            case 'declined':
                return $query->where('status', 'declined');
            default:
                return $query;
        }
    }

    public function index(Request $request)
    {
        // Handle period parameter for dashboard functionality
        $period = $request->get('period', 'monthly');

        // Check if status column exists
        if (!$this->hasWorkflowColumn()) {
            $data = [
                'total_requests' => 0,
                'completed_requests' => 0,
                'pending_requests' => 0,
                'items_requested' => 0,
                'chart_data' => [],
            ];
            $message = 'Dashboard temporarily unavailable while database is being updated.';
        } else {
            $data = $this->getReportData($period);
            $message = null;
        }

        // Add unique users count to data
        $dateRange = $this->getDateRangeFromPeriod($period);
        $data['unique_users'] = SupplyRequest::whereBetween('created_at', [$dateRange['from'], $dateRange['to']])
            ->distinct('user_id')
            ->count('user_id');

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.index', compact('data', 'period'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('reports-dashboard-' . $period . '-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.index', compact('data', 'period', 'message'));
    }

    /**
     * Get report data based on period
     */
    private function getReportData($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'monthly':
                return $this->getMonthlyReportData($now);
            case 'quarterly':
                return $this->getQuarterlyReportData($now);
            case 'annually':
                return $this->getAnnualReportData($now);
            default:
                return $this->getMonthlyReportData($now);
        }
    }

    private function getMonthlyReportData($date)
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        
        // Get last 12 months for chart
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $date->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $date->copy()->subMonths($i)->endOfMonth();
            
            $chartData[] = [
                'date' => $monthStart->format('M Y'),
                'requests' => SupplyRequest::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'value' => SupplyRequest::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->with('item')->get()->sum(function($req) {
                        return $req->quantity * ($req->item->unit_price ?? 0);
                    })
            ];
        }
        
        $monthRequests = SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'period' => 'Monthly',
            'current_date' => $startDate->format('F Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $monthRequests->count(),
                'fulfilled_requests' => $monthRequests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'pending_requests' => $monthRequests->where('status', 'pending')->count(),
                'total_value' => $monthRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $monthRequests->load(['user', 'item', 'item.category'])
        ];
    }

    private function getQuarterlyReportData($date)
    {
        $startDate = $date->copy()->startOfQuarter();
        $endDate = $date->copy()->endOfQuarter();
        
        // Get last 4 quarters for chart
        $chartData = [];
        for ($i = 3; $i >= 0; $i--) {
            $quarterStart = $date->copy()->subQuarters($i)->startOfQuarter();
            $quarterEnd = $date->copy()->subQuarters($i)->endOfQuarter();
            
            $chartData[] = [
                'date' => 'Q' . $quarterStart->quarter . ' ' . $quarterStart->year,
                'requests' => SupplyRequest::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('created_at', [$quarterStart, $quarterEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'value' => SupplyRequest::whereBetween('created_at', [$quarterStart, $quarterEnd])
                    ->with('item')->get()->sum(function($req) {
                        return $req->quantity * ($req->item->unit_price ?? 0);
                    })
            ];
        }
        
        $quarterRequests = SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'period' => 'Quarterly',
            'current_date' => 'Q' . $startDate->quarter . ' ' . $startDate->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $quarterRequests->count(),
                'fulfilled_requests' => $quarterRequests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'pending_requests' => $quarterRequests->where('status', 'pending')->count(),
                'total_value' => $quarterRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $quarterRequests->load(['user', 'item', 'item.category'])
        ];
    }

    private function getAnnualReportData($date)
    {
        $startDate = $date->copy()->startOfYear();
        $endDate = $date->copy()->endOfYear();
        
        // Get all requests for the last 5 years in one query
        $requestsQuery = SupplyRequest::selectRaw('YEAR(requests.created_at) as year, COUNT(*) as total_requests, SUM(CASE WHEN status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, 0 as total_value')
            ->whereBetween('requests.created_at', [$date->copy()->subYears(4)->startOfYear(), $endDate])
            ->groupByRaw('YEAR(requests.created_at)')
            ->orderByRaw('YEAR(requests.created_at)');
        
        $requestsData = $requestsQuery->get()->keyBy('year');
        
        // Build chart data for last 5 years
        $chartData = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = $date->copy()->subYears($i)->year;
            
            $data = $requestsData->get($year);
            
            $chartData[] = [
                'date' => (string)$year,
                'requests' => $data ? (int)$data->total_requests : 0,
                'disbursements' => $data ? (int)$data->fulfilled_requests : 0,
                'value' => $data ? (float)$data->total_value : 0
            ];
        }
        
        // Current year requests for display
        $yearRequests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Calculate current year summary
        $yearSummary = SupplyRequest::selectRaw('COUNT(*) as total_requests, SUM(CASE WHEN status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests, 0 as total_value')
            ->whereBetween('requests.created_at', [$startDate, $endDate]);
        
        $summaryData = $yearSummary->first();
        
        return [
            'period' => 'Annual',
            'current_date' => (string)$date->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => (int)($summaryData->total_requests ?? 0),
                'fulfilled_requests' => (int)($summaryData->fulfilled_requests ?? 0),
                'pending_requests' => (int)($summaryData->pending_requests ?? 0),
                'total_value' => (float)($summaryData->total_value ?? 0),
            ],
            'records' => $yearRequests
        ];
    }

    public function inventorySummary(Request $request)
    {
        $query = Consumable::with('category');

        // Apply filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereRaw('quantity <= min_stock');
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'adequate':
                    $query->where('quantity', '>', 'min_stock');
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        switch ($sortBy) {
            case 'quantity':
                $query->orderBy('quantity', 'desc');
                break;
            case 'category':
                $query->join('categories', 'consumables.category_id', '=', 'categories.id')
                      ->orderBy('categories.name')
                      ->select('consumables.*');
                break;
            default:
                $query->orderBy('name');
        }

        // Paginate results
        $items = $query->paginate(25);

        // Get all categories for filter dropdown
        $categories = Category::all();

        // Calculate summary statistics
        $allConsumables = Consumable::all(); // Get all consumables for summary stats
        $allNonConsumables = NonConsumable::all(); // Get all non-consumables for summary stats
        $summary = [
            'total_items' => $allConsumables->count() + $allNonConsumables->count(),
            'low_stock_items' => $allConsumables->whereRaw('quantity <= min_stock')->count() + $allNonConsumables->whereRaw('quantity <= min_stock')->count(),
            'out_of_stock_items' => $allConsumables->where('quantity', '<=', 0)->count() + $allNonConsumables->where('quantity', '<=', 0)->count(),
            'adequate_stock_items' => $allConsumables->whereRaw('quantity > min_stock')->count() + $allNonConsumables->whereRaw('quantity > min_stock')->count(),
        ];

        // Category statistics for chart
        $categoryStats = Category::with(['consumables', 'nonConsumables'])->get()->map(function($category) {
            return [
                'name' => $category->name,
                'items_count' => $category->consumables->count() + $category->nonConsumables->count(),
            ];
        })->filter(function($category) {
            return $category['items_count'] > 0;
        });

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.inventory-summary', compact('items', 'summary', 'request'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('inventory-summary-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.inventory-summary', compact('items', 'categories', 'summary', 'categoryStats'));
    }

    public function lowStockAlert(Request $request)
    {
        $consumablesQuery = Consumable::with('category')
            ->whereRaw('quantity <= min_stock OR quantity <= 5');

        $nonConsumablesQuery = NonConsumable::with('category')
            ->whereRaw('quantity <= min_stock OR quantity <= 5');

        if ($request->category_id) {
            $consumablesQuery->where('category_id', $request->category_id);
            $nonConsumablesQuery->where('category_id', $request->category_id);
        }

        $lowStockConsumables = $consumablesQuery->orderBy('quantity', 'asc')->get();
        $lowStockNonConsumables = $nonConsumablesQuery->orderBy('quantity', 'asc')->get();

        $lowStockItems = collect([...$lowStockConsumables, ...$lowStockNonConsumables])->sortBy('quantity');
        $categories = Category::all();

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.low-stock-alert', compact('lowStockItems'));
            return $pdf->download('low-stock-alert-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.low-stock-alert', compact('lowStockItems', 'categories'));
    }

    public function requestAnalytics(Request $request)
    {
        // Handle period parameter for consistent filtering with main reports
        $period = $request->get('period', 'monthly');

        // Get report data based on period
        $data = $this->getRequestAnalyticsReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.request-analytics', compact('data'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('request-analytics-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.request-analytics', compact('data', 'period'));
    }

    public function userActivityReport(Request $request)
    {
        // Handle period parameter for consistent filtering with main reports
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $logs = Log::with('user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $activityStats = [
            'total_activities' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'activities_by_action' => $logs->groupBy('action')->map->count(),
            'most_active_users' => $logs->groupBy('user_id')->map(function($userLogs) {
                return [
                    'user' => $userLogs->first()->user,
                    'activity_count' => $userLogs->count(),
                    'last_activity' => $userLogs->first()->created_at
                ];
            })->sortByDesc('activity_count')->take(10),
            'daily_activity' => $logs->groupBy(function($log) {
                return Carbon::parse($log->created_at)->format('Y-m-d');
            })->map->count(),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.user-activity', compact('logs', 'activityStats', 'dateFrom', 'dateTo'));
            return $pdf->download('user-activity-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.user-activity', compact('logs', 'activityStats', 'dateFrom', 'dateTo', 'period'));
    }

    /**
     * QR Code Scan Analytics Report
     */
    public function qrScanAnalytics(Request $request)
    {
        // Handle period parameter for consistent filtering with main reports
        $period = $request->get('period', 'monthly');

        // Get report data based on period
        $data = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.qr-scan-analytics', compact('data'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('qr-scan-analytics-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.qr-scan-analytics', compact('data', 'period'));
    }

    /**
     * QR Code Scan History for Specific Item
     */
    public function itemScanHistory(Request $request, $itemId)
    {
        // Try to find the item in both Consumable and NonConsumable models
        $item = Consumable::find($itemId) ?? NonConsumable::find($itemId);

        if (!$item) {
            abort(404, 'Item not found');
        }

        // Handle period parameter for consistent filtering with main reports
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $scanLogs = \App\Models\ItemScanLog::with('user')
            ->where('item_id', $itemId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $frequencyAnalysis = \App\Models\ItemScanLog::getScanFrequencyAnalysis($itemId);

        $analytics = [
            'total_scans' => $scanLogs->count(),
            'first_scan' => $scanLogs->isNotEmpty() ? $scanLogs->last()->created_at : null,
            'last_scan' => $scanLogs->isNotEmpty() ? $scanLogs->first()->created_at : null,
            'unique_users' => $scanLogs->pluck('user_id')->unique()->filter()->count(),
            'scans_by_location' => $scanLogs->whereNotNull('location')
                ->groupBy('location')
                ->map->count(),
            'frequency_analysis' => $frequencyAnalysis,
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.item-scan-history', compact('item', 'scanLogs', 'analytics', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('item-scan-history-' . $item->id . '-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.item-scan-history', compact('item', 'scanLogs', 'analytics', 'dateFrom', 'dateTo', 'period'));
    }

    /**
     * QR Code Scan Alerts Report
     */
    public function scanAlerts(Request $request)
    {
        $alerts = \App\Models\ItemScanLog::getScanAlerts();

        $unscannedItems = [
            '30_days' => \App\Models\ItemScanLog::getUnscannedItems(30),
            '60_days' => \App\Models\ItemScanLog::getUnscannedItems(60),
            '90_days' => \App\Models\ItemScanLog::getUnscannedItems(90),
        ];

        $stats = [
            'total_alerts' => count($alerts),
            'unscanned_30_days' => $unscannedItems['30_days']->count(),
            'unscanned_60_days' => $unscannedItems['60_days']->count(),
            'unscanned_90_days' => $unscannedItems['90_days']->count(),
            'unusual_activity_count' => isset($alerts['unusual_scan_activity']) ? $alerts['unusual_scan_activity']->count() : 0,
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.scan-alerts', compact('alerts', 'unscannedItems', 'stats'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('scan-alerts-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.scan-alerts', compact('alerts', 'unscannedItems', 'stats'));
    }

    // Helper methods for analytics calculations
    private function calculateAverageProcessingTime($requests)
    {
        $processedRequests = $requests->filter(function($request) {
            return $request->admin_approval_date && $request->created_at;
        });

        if ($processedRequests->isEmpty()) {
            return 0;
        }

        $totalProcessingTime = $processedRequests->sum(function($request) {
            return Carbon::parse($request->admin_approval_date)
                ->diffInHours(Carbon::parse($request->created_at));
        });

        return round($totalProcessingTime / $processedRequests->count(), 2);
    }

    private function getMostRequestedItems($requests)
    {
        return $requests->groupBy('item_id')
            ->map(function($itemRequests) {
                return [
                    'item' => $itemRequests->first()->item,
                    'total_quantity' => $itemRequests->sum('quantity'),
                    'request_count' => $itemRequests->count()
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(10);
    }

    private function getRequestsByDepartment($requests)
    {
        return $requests->groupBy('department')->map->count();
    }

    private function getRequestsByPriority($requests)
    {
        return $requests->groupBy('priority')->map->count();
    }

    private function getMonthlyTrend($dateFrom, $dateTo)
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $trend = [];

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();

            $trend[$start->format('Y-m')] = SupplyRequest::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            
            $start->addMonth();
        }

        return $trend;
    }

    /**
     * Monthly summary report
     */
    public function monthlySummary(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        $monthlyData = [
            'requests' => SupplyRequest::with(['user', 'item', 'item.category'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
            'inventory_changes' => collect([
                ...Consumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
                ...NonConsumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
            ]),
            'low_stock_items' => collect([
                ...Consumable::with('category')->whereRaw('quantity <= min_stock')->get(),
                ...NonConsumable::with('category')->whereRaw('quantity <= min_stock')->get(),
            ]),
        ];

        $analytics = [
            'total_requests' => $monthlyData['requests']->count(),
            'approved_requests' => $monthlyData['requests']->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'monthly_value' => $monthlyData['requests']->sum(function($req) {
                return $req->quantity * 0; // unit_price not available in normalized tables
            }),
            'weekly_breakdown' => $this->getMonthlyWeeklyBreakdown($startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($monthlyData['requests']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.monthly-summary', compact('monthlyData', 'analytics', 'month', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('monthly-summary-' . $month . '.pdf');
        }

        return view('admin.reports.monthly-summary', compact('monthlyData', 'analytics', 'month', 'startDate', 'endDate'));
    }

    /**
     * Quarterly summary report
     */
    public function quarterlySummary(Request $request)
    {
        $quarter = $request->quarter ?? Carbon::now()->format('Y-Q');
        $year = explode('-', $quarter)[0];
        $quarterNum = explode('-', $quarter)[1];
        
        $startDate = Carbon::createFromDate($year, ($quarterNum - 1) * 3 + 1, 1)->startOfQuarter();
        $endDate = Carbon::createFromDate($year, ($quarterNum - 1) * 3 + 1, 1)->endOfQuarter();
        
        $quarterlyData = [
            'requests' => SupplyRequest::with(['user', 'item', 'item.category'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
            'inventory_changes' => collect([
                ...Consumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
                ...NonConsumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
            ]),
            'low_stock_items' => collect([
                ...Consumable::with('category')->whereRaw('quantity <= min_stock')->get(),
                ...NonConsumable::with('category')->whereRaw('quantity <= min_stock')->get(),
            ]),
        ];

        $analytics = [
            'total_requests' => $quarterlyData['requests']->count(),
            'approved_requests' => $quarterlyData['requests']->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'monthly_breakdown' => $this->getQuarterlyMonthlyBreakdown($startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($quarterlyData['requests']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.quarterly-summary', compact('quarterlyData', 'analytics', 'quarter', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('quarterly-summary-' . $quarter . '.pdf');
        }

        return view('admin.reports.quarterly-summary', compact('quarterlyData', 'analytics', 'quarter', 'startDate', 'endDate'));
    }

    /**
     * Annual summary report
     */
    public function annualSummary(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        $annualData = [
            'requests' => SupplyRequest::with(['user', 'item', 'item.category'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
            'inventory_changes' => collect([
                ...Consumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
                ...NonConsumable::with('category')->whereBetween('updated_at', [$startDate, $endDate])->get(),
            ]),
            'low_stock_items' => collect([
                ...Consumable::with('category')->whereRaw('quantity <= min_stock')->get(),
                ...NonConsumable::with('category')->whereRaw('quantity <= min_stock')->get(),
            ]),
        ];

        $analytics = [
            'total_requests' => $annualData['requests']->count(),
            'approved_requests' => $annualData['requests']->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'quarterly_breakdown' => $this->getAnnualQuarterlyBreakdown($year),
            'department_performance' => $this->getDepartmentPerformance($annualData['requests']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.annual-summary', compact('annualData', 'analytics', 'year', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('annual-summary-' . $year . '.pdf');
        }

        return view('admin.reports.annual-summary', compact('annualData', 'analytics', 'year', 'startDate', 'endDate'));
    }
    private function getWeeklyBreakdown($startDate, $endDate)
    {
        $breakdown = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $dayRequests = SupplyRequest::whereDate('created_at', $current)->count();
            $dayDisbursements = SupplyRequest::whereDate('updated_at', $current)
                ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
                ->count();
                
            $breakdown[$current->format('Y-m-d')] = [
                'requests' => $dayRequests,
                'disbursements' => $dayDisbursements,
                'day_name' => $current->format('l')
            ];
            
            $current->addDay();
        }

        return $breakdown;
    }

    private function getDailyTrend($startDate, $endDate)
    {
        $trend = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $trend[$current->format('Y-m-d')] = SupplyRequest::whereDate('created_at', $current)->count();
            $current->addDay();
        }

        return $trend;
    }

    private function getMonthlyWeeklyBreakdown($startDate, $endDate)
    {
        $breakdown = [];
        $current = Carbon::parse($startDate);
        $weekNumber = 1;

        while ($current <= $endDate) {
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd = $current->copy()->endOfWeek();
            
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate;
            }

            $weekRequests = SupplyRequest::whereBetween('created_at', [$weekStart, $weekEnd])->count();
            
            $breakdown['Week ' . $weekNumber] = [
                'requests' => $weekRequests,
                'start' => $weekStart->format('M d'),
                'end' => $weekEnd->format('M d')
            ];

            $current = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        return $breakdown;
    }

    private function getQuarterlyMonthlyBreakdown($startDate, $endDate)
    {
        $breakdown = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $monthRequests = SupplyRequest::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->count();
            
            $monthDisbursements = SupplyRequest::whereYear('updated_at', $current->year)
                ->whereMonth('updated_at', $current->month)
                ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
                ->count();
                
            $breakdown[$current->format('M Y')] = [
                'requests' => $monthRequests,
                'disbursements' => $monthDisbursements,
            ];
            
            $current->addMonth();
        }

        return $breakdown;
    }

    private function getAnnualQuarterlyBreakdown($year)
    {
        $breakdown = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterStart = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfQuarter();
            $quarterEnd = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->endOfQuarter();
            
            $breakdown['Q' . $quarter . ' ' . $year] = [
                'requests' => SupplyRequest::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('updated_at', [$quarterStart, $quarterEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count()
            ];
        }
        
        return $breakdown;
    }

    private function getAnnualMonthlyBreakdown($year)
    {
        $breakdown = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            $breakdown[Carbon::createFromDate($year, $month, 1)->format('M')] = [
                'requests' => SupplyRequest::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('updated_at', [$monthStart, $monthEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count()
            ];
        }
        
        return $breakdown;
    }

    private function getTopDepartments($year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        return SupplyRequest::whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('department')
            ->selectRaw('department, count(*) as request_count')
            ->orderBy('request_count', 'desc')
            ->take(10)
            ->get();
    }

    private function getInventoryGrowth($year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        return [
            'items_added' => Consumable::whereBetween('created_at', [$startDate, $endDate])->count() + NonConsumable::whereBetween('created_at', [$startDate, $endDate])->count(),
            'value_added' => Consumable::whereBetween('created_at', [$startDate, $endDate])->sum('total_value') + NonConsumable::whereBetween('created_at', [$startDate, $endDate])->sum('total_value'),
            'categories_added' => Category::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];
    }

    private function getAnnualFinancialSummary($year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        $requests = SupplyRequest::with('item')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        return [
            'total_requested_value' => $requests->sum(function($req) {
                return $req->quantity * 0; // unit_price not available in normalized tables
            }),
            'total_disbursed_value' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
                ->sum(function($req) {
                    return $req->quantity * 0; // unit_price not available in normalized tables
                }),
            'current_inventory_value' => Consumable::sum('total_value') + NonConsumable::sum('total_value'),
        ];
    }

    /**
     * AJAX endpoint for dashboard data
     */
    public function dashboardData(Request $request)
    {
        // Check if status column exists
        if (!$this->hasWorkflowColumn()) {
            return response()->json([
                'period' => 'Daily',
                'current_date' => Carbon::now()->format('F j, Y'),
                'chart_data' => [],
                'summary' => [
                    'total_requests' => 0,
                    'fulfilled_requests' => 0,
                    'pending_requests' => 0,
                    'total_value' => 0,
                ],
                'records' => [],
                'message' => 'Dashboard data temporarily unavailable while database is being updated.'
            ]);
        }

        $period = $request->get('period', 'monthly');
        $data = $this->getReportData($period);
        
        // Add QR scan statistics to dashboard data
        $scanStats = \App\Models\ItemScanLog::getScanStats();
        $data['qr_scan_stats'] = [
            'total_scans_today' => \App\Models\ItemScanLog::whereDate('created_at', Carbon::today())->count(),
            'total_scans' => $scanStats['total_scans'],
            'unique_items_scanned' => $scanStats['unique_items_scanned'],
            'unscanned_items_30_days' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
        ];
        
        return response()->json($data);
    }

    /**
     * Get QR scan report data based on period
     */
    private function getQrScanReportData($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'monthly':
                return $this->getQrScanMonthlyReportData($now);
            case 'quarterly':
                return $this->getQrScanQuarterlyReportData($now);
            case 'annually':
                return $this->getQrScanAnnualReportData($now);
            default:
                return $this->getQrScanMonthlyReportData($now);
        }
    }

    private function getQrScanAnnualReportData($date)
    {
        $startDate = $date->copy()->startOfYear();
        $endDate = $date->copy()->endOfYear();

        // Get all scans for the last 5 years in one query
        $scansQuery = \App\Models\ItemScanLog::selectRaw('YEAR(created_at) as year, COUNT(*) as total_scans')
            ->whereBetween('created_at', [$date->copy()->subYears(4)->startOfYear(), $endDate])
            ->groupByRaw('YEAR(created_at)')
            ->orderByRaw('YEAR(created_at)');

        $scansData = $scansQuery->get()->keyBy('year');

        // Build chart data for last 5 years
        $chartData = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = $date->copy()->subYears($i)->year;

            $data = $scansData->get($year);

            $chartData[] = [
                'date' => (string)$year,
                'scans' => $data ? (int)$data->total_scans : 0,
            ];
        }

        // Current year scans for display
        $yearScans = \App\Models\ItemScanLog::with(['item', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get scan statistics for current year
        $scanStats = \App\Models\ItemScanLog::getScanStats($startDate->toDateString(), $endDate->toDateString());

        return [
            'period' => 'Annual',
            'current_date' => (string)$date->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_scans' => $scanStats['total_scans'],
                'unique_items_scanned' => $scanStats['unique_items_scanned'],
                'unique_users_scanning' => $scanStats['unique_users_scanning'],
                'unscanned_items' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
            ],
            'analytics' => [
                'scans_by_scanner_type' => $scanStats['scans_by_scanner_type'],
                'most_scanned_items' => $scanStats['most_scanned_items'],
                'scans_by_location' => $scanStats['scans_by_location'],
                'daily_scan_trend' => $this->getDailyScanTrend($startDate->toDateString(), $endDate->toDateString()),
                'scan_frequency_analysis' => $this->getOverallScanFrequency(),
                'scan_alerts' => \App\Models\ItemScanLog::getScanAlerts(),
            ],
            'records' => $yearScans
        ];
    }

    private function getQrScanMonthlyReportData($date)
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // Get all scans for the last 12 months in one query
        $scansQuery = \App\Models\ItemScanLog::selectRaw('DATE(created_at) as date, COUNT(*) as total_scans')
            ->whereBetween('created_at', [$date->copy()->subMonths(11)->startOfMonth(), $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)');

        $scansData = $scansQuery->get()->groupBy(function($item) {
            $date = Carbon::parse($item->date);
            return $date->format('Y-m');
        });

        // Build chart data for last 12 months
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $date->copy()->subMonths($i)->startOfMonth();
            $monthKey = $monthStart->format('Y-m');

            $monthData = $scansData->get($monthKey, collect());
            $totalScans = $monthData->sum('total_scans');

            $chartData[] = [
                'date' => $monthStart->format('M Y'),
                'scans' => (int)$totalScans,
            ];
        }

        // Current month scans for display
        $monthScans = \App\Models\ItemScanLog::with(['item', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get scan statistics for current month
        $scanStats = \App\Models\ItemScanLog::getScanStats($startDate->toDateString(), $endDate->toDateString());

        return [
            'period' => 'Monthly',
            'current_date' => $startDate->format('F Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_scans' => $scanStats['total_scans'],
                'unique_items_scanned' => $scanStats['unique_items_scanned'],
                'unique_users_scanning' => $scanStats['unique_users_scanning'],
                'unscanned_items' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
            ],
            'analytics' => [
                'scans_by_scanner_type' => $scanStats['scans_by_scanner_type'],
                'most_scanned_items' => $scanStats['most_scanned_items'],
                'scans_by_location' => $scanStats['scans_by_location'],
                'daily_scan_trend' => $this->getDailyScanTrend($startDate->toDateString(), $endDate->toDateString()),
                'scan_frequency_analysis' => $this->getOverallScanFrequency(),
                'scan_alerts' => \App\Models\ItemScanLog::getScanAlerts(),
            ],
            'records' => $monthScans
        ];
    }

    private function getQrScanQuarterlyReportData($date)
    {
        $startDate = $date->copy()->startOfQuarter();
        $endDate = $date->copy()->endOfQuarter();

        // Get all scans for the last 4 quarters in one query
        $scansQuery = \App\Models\ItemScanLog::selectRaw('DATE(created_at) as date, COUNT(*) as total_scans')
            ->whereBetween('created_at', [$date->copy()->subQuarters(3)->startOfQuarter(), $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)');

        $scansData = $scansQuery->get()->groupBy(function($item) {
            $date = Carbon::parse($item->date);
            return $date->format('Y') . '-Q' . $date->quarter;
        });

        // Build chart data for last 4 quarters
        $chartData = [];
        for ($i = 3; $i >= 0; $i--) {
            $quarterStart = $date->copy()->subQuarters($i)->startOfQuarter();
            $quarterKey = $quarterStart->format('Y') . '-Q' . $quarterStart->quarter;

            $quarterData = $scansData->get($quarterKey, collect());
            $totalScans = $quarterData->sum('total_scans');

            $chartData[] = [
                'date' => 'Q' . $quarterStart->quarter . ' ' . $quarterStart->year,
                'scans' => (int)$totalScans,
            ];
        }

        // Current quarter scans for display
        $quarterScans = \App\Models\ItemScanLog::with(['item', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get scan statistics for current quarter
        $scanStats = \App\Models\ItemScanLog::getScanStats($startDate->toDateString(), $endDate->toDateString());

        return [
            'period' => 'Quarterly',
            'current_date' => 'Q' . $startDate->quarter . ' ' . $startDate->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_scans' => $scanStats['total_scans'],
                'unique_items_scanned' => $scanStats['unique_items_scanned'],
                'unique_users_scanning' => $scanStats['unique_users_scanning'],
                'unscanned_items' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
            ],
            'analytics' => [
                'scans_by_scanner_type' => $scanStats['scans_by_scanner_type'],
                'most_scanned_items' => $scanStats['most_scanned_items'],
                'scans_by_location' => $scanStats['scans_by_location'],
                'daily_scan_trend' => $this->getDailyScanTrend($startDate->toDateString(), $endDate->toDateString()),
                'scan_frequency_analysis' => $this->getOverallScanFrequency(),
                'scan_alerts' => \App\Models\ItemScanLog::getScanAlerts(),
            ],
            'records' => $quarterScans
        ];
    }

    /**
     * Get daily scan trend for QR analytics
     */
    private function getDailyScanTrend($dateFrom, $dateTo)
    {
        return \App\Models\ItemScanLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as scan_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('scan_count', 'date');
    }

    /**
     * Get overall scan frequency analysis
     */
    private function getOverallScanFrequency()
    {
        $allItems = collect([
            ...Consumable::all(),
            ...NonConsumable::all(),
        ]);
        $frequencyData = [];

        foreach ($allItems as $item) {
            $analysis = \App\Models\ItemScanLog::getScanFrequencyAnalysis($item->id);
            if ($analysis && $analysis['total_scans'] > 0) {
                $frequencyData[] = [
                    'item' => $item,
                    'analysis' => $analysis
                ];
            }
        }

        // Sort by average days between scans (items scanned less frequently first)
        usort($frequencyData, function($a, $b) {
            return $b['analysis']['average_days_between_scans'] <=> $a['analysis']['average_days_between_scans'];
        });

        return array_slice($frequencyData, 0, 20); // Top 20 items by scan frequency
    }

    /**
     * Get date range based on period
     */
    private function getDateRangeFromPeriod($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'monthly':
                return [
                    'from' => $now->copy()->startOfMonth()->toDateString(),
                    'to' => $now->copy()->endOfMonth()->toDateString()
                ];
            case 'quarterly':
                return [
                    'from' => $now->copy()->startOfQuarter()->toDateString(),
                    'to' => $now->copy()->endOfQuarter()->toDateString()
                ];
            case 'annually':
                return [
                    'from' => $now->copy()->startOfYear()->toDateString(),
                    'to' => $now->copy()->endOfYear()->toDateString()
                ];
            default:
                return [
                    'from' => $now->copy()->startOfMonth()->toDateString(),
                    'to' => $now->copy()->endOfMonth()->toDateString()
                ];
        }
    }

    /**
     * Get request analytics report data based on period
     */
    private function getRequestAnalyticsReportData($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'monthly':
                return $this->getRequestAnalyticsMonthlyReportData($now);
            case 'quarterly':
                return $this->getRequestAnalyticsQuarterlyReportData($now);
            case 'annually':
                return $this->getRequestAnalyticsAnnualReportData($now);
            default:
                return $this->getRequestAnalyticsMonthlyReportData($now);
        }
    }

    private function getRequestAnalyticsAnnualReportData($date)
    {
        $startDate = $date->copy()->startOfYear();
        $endDate = $date->copy()->endOfYear();

        // Get all requests for the last 5 years in one query
        $requestsQuery = SupplyRequest::selectRaw('YEAR(created_at) as year, COUNT(*) as total_requests')
            ->whereBetween('created_at', [$date->copy()->subYears(4)->startOfYear(), $endDate])
            ->groupByRaw('YEAR(created_at)')
            ->orderByRaw('YEAR(created_at)');

        $requestsData = $requestsQuery->get()->keyBy('year');

        // Build chart data for last 5 years
        $chartData = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = $date->copy()->subYears($i)->year;

            $data = $requestsData->get($year);

            $chartData[] = [
                'date' => (string)$year,
                'requests' => $data ? (int)$data->total_requests : 0,
            ];
        }

        // Current year requests for display
        $requests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Analytics calculations
        $analytics = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'declined_requests' => $requests->where('status', 'declined')->count(),
            'fulfilled_requests' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
            'average_processing_time' => $this->calculateAverageProcessingTime($requests),
            'most_requested_items' => $this->getMostRequestedItems($requests),
            'requests_by_department' => $this->getRequestsByDepartment($requests),
            'requests_by_priority' => $this->getRequestsByPriority($requests),
            'monthly_trend' => $this->getMonthlyTrend($startDate->toDateString(), $endDate->toDateString()),
        ];

        $departments = SupplyRequest::distinct()->pluck('department')->filter();

        return [
            'period' => 'Annual',
            'current_date' => (string)$date->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $analytics['total_requests'],
                'approved_requests' => $analytics['approved_requests'],
                'pending_requests' => $analytics['pending_requests'],
                'declined_requests' => $analytics['declined_requests'],
            ],
            'analytics' => $analytics,
            'departments' => $departments,
            'records' => $requests
        ];
    }

    private function getRequestAnalyticsMonthlyReportData($date)
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // Get all requests for the last 12 months in one query
        $requestsQuery = SupplyRequest::selectRaw('DATE(created_at) as date, COUNT(*) as total_requests')
            ->whereBetween('created_at', [$date->copy()->subMonths(11)->startOfMonth(), $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)');

        $requestsData = $requestsQuery->get()->groupBy(function($item) {
            $date = Carbon::parse($item->date);
            return $date->format('Y-m');
        });

        // Build chart data for last 12 months
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $date->copy()->subMonths($i)->startOfMonth();
            $monthKey = $monthStart->format('Y-m');

            $monthData = $requestsData->get($monthKey, collect());
            $totalRequests = $monthData->sum('total_requests');

            $chartData[] = [
                'date' => $monthStart->format('M Y'),
                'requests' => (int)$totalRequests,
            ];
        }

        // Current month requests for display
        $requests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Analytics calculations
        $analytics = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'declined_requests' => $requests->where('status', 'declined')->count(),
            'fulfilled_requests' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
            'average_processing_time' => $this->calculateAverageProcessingTime($requests),
            'most_requested_items' => $this->getMostRequestedItems($requests),
            'requests_by_department' => $this->getRequestsByDepartment($requests),
            'requests_by_priority' => $this->getRequestsByPriority($requests),
            'monthly_trend' => $this->getMonthlyTrend($startDate->toDateString(), $endDate->toDateString()),
        ];

        $departments = SupplyRequest::distinct()->pluck('department')->filter();

        return [
            'period' => 'Monthly',
            'current_date' => $startDate->format('F Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $analytics['total_requests'],
                'approved_requests' => $analytics['approved_requests'],
                'pending_requests' => $analytics['pending_requests'],
                'declined_requests' => $analytics['declined_requests'],
            ],
            'analytics' => $analytics,
            'departments' => $departments,
            'records' => $requests
        ];
    }

    private function getRequestAnalyticsQuarterlyReportData($date)
    {
        $startDate = $date->copy()->startOfQuarter();
        $endDate = $date->copy()->endOfQuarter();

        // Get all requests for the last 4 quarters in one query
        $requestsQuery = SupplyRequest::selectRaw('DATE(created_at) as date, COUNT(*) as total_requests')
            ->whereBetween('created_at', [$date->copy()->subQuarters(3)->startOfQuarter(), $endDate])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)');

        $requestsData = $requestsQuery->get()->groupBy(function($item) {
            $date = Carbon::parse($item->date);
            return $date->format('Y') . '-Q' . $date->quarter;
        });

        // Build chart data for last 4 quarters
        $chartData = [];
        for ($i = 3; $i >= 0; $i--) {
            $quarterStart = $date->copy()->subQuarters($i)->startOfQuarter();
            $quarterKey = $quarterStart->format('Y') . '-Q' . $quarterStart->quarter;

            $quarterData = $requestsData->get($quarterKey, collect());
            $totalRequests = $quarterData->sum('total_requests');

            $chartData[] = [
                'date' => 'Q' . $quarterStart->quarter . ' ' . $quarterStart->year,
                'requests' => (int)$totalRequests,
            ];
        }

        // Current quarter requests for display
        $requests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        // Analytics calculations
        $analytics = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->whereIn('status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'declined_requests' => $requests->where('status', 'declined')->count(),
            'fulfilled_requests' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
            'average_processing_time' => $this->calculateAverageProcessingTime($requests),
            'most_requested_items' => $this->getMostRequestedItems($requests),
            'requests_by_department' => $this->getRequestsByDepartment($requests),
            'requests_by_priority' => $this->getRequestsByPriority($requests),
            'monthly_trend' => $this->getMonthlyTrend($startDate->toDateString(), $endDate->toDateString()),
        ];

        $departments = SupplyRequest::distinct()->pluck('department')->filter();

        return [
            'period' => 'Quarterly',
            'current_date' => 'Q' . $startDate->quarter . ' ' . $startDate->year,
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $analytics['total_requests'],
                'approved_requests' => $analytics['approved_requests'],
                'pending_requests' => $analytics['pending_requests'],
                'declined_requests' => $analytics['declined_requests'],
            ],
            'analytics' => $analytics,
            'departments' => $departments,
            'records' => $requests
        ];
    }

    private function getDepartmentPerformance($requests)
    {
        return $requests->groupBy('department')
            ->map(function($deptRequests) {
                $total = $deptRequests->count();
                $fulfilled = $deptRequests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count();
                $pending = $deptRequests->where('status', 'pending')->count();
                
                return [
                    'total_requests' => $total,
                    'fulfilled_requests' => $fulfilled,
                    'pending_requests' => $pending,
                    'fulfillment_rate' => $total > 0 ? round(($fulfilled / $total) * 100, 2) : 0
                ];
            })
            ->sortByDesc('total_requests');
    }
}
