<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
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
     * Check if workflow_status column exists (MySQL 5.5 compatible)
     * Cache the result to avoid repeated queries
     */
    private function hasWorkflowColumn()
    {
        static $hasColumn = null;
        
        if ($hasColumn === null) {
            try {
                // Use a simple query that works with MySQL 5.5
                $result = DB::select("SHOW COLUMNS FROM requests LIKE 'workflow_status'");
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
                return $query->where('workflow_status', 'pending');
            case 'fulfilled':
                return $query->where('workflow_status', 'fulfilled');
            case 'approved':
                return $query->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed']);
            case 'declined':
                return $query->whereIn('workflow_status', ['declined_by_office_head', 'declined_by_admin']);
            default:
                return $query;
        }
    }

    public function index(Request $request)
    {
        // Handle period parameter for dashboard functionality
        $period = $request->get('period', 'daily');

        // Check if workflow_status column exists
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

        // Get basic stats for the stats cards
        $stats = [
            'total_items' => Item::count(),
            'low_stock_items' => Item::whereRaw('current_stock <= minimum_stock')->count(),
            'total_requests_this_month' => SupplyRequest::whereMonth('created_at', Carbon::now()->month)->count(),
            'pending_requests' => $this->getRequestsByStatus(SupplyRequest::query(), 'pending')->count(),
            'total_value' => Item::sum('total_value'),
            'categories_count' => Category::count(),
        ];

        return view('admin.reports.index', compact('stats', 'data', 'period', 'message'));
    }

    /**
     * Get report data based on period
     */
    private function getReportData($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'daily':
                return $this->getDailyReportData($now);
            case 'weekly': 
                return $this->getWeeklyReportData($now);
            case 'annually':
                return $this->getAnnualReportData($now);
            default:
                return $this->getDailyReportData($now);
        }
    }

    private function getDailyReportData($date)
    {
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();
        
        // Get last 7 days for chart - use single query with date grouping
        $chartData = [];
        
        // Get all requests for the last 7 days in one query
        $requestsQuery = SupplyRequest::selectRaw('DATE(requests.created_at) as date, COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$date->copy()->subDays(6)->startOfDay(), $endDate])
            ->groupByRaw('DATE(requests.created_at)')
            ->orderByRaw('DATE(requests.created_at)');
        
        $requestsData = $requestsQuery->get()->keyBy('date');
        
        // Build chart data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $checkDate = $date->copy()->subDays($i);
            $dateKey = $checkDate->format('Y-m-d');
            
            $data = $requestsData->get($dateKey);
            
            $chartData[] = [
                'date' => $checkDate->format('M j'),
                'requests' => $data ? (int)$data->total_requests : 0,
                'disbursements' => $data ? (int)$data->fulfilled_requests : 0,
                'value' => $data ? (float)$data->total_value : 0
            ];
        }
        
        // Today's data - get all records for display
        $todayRequests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Calculate today's summary using the same efficient query
        $todaySummary = SupplyRequest::selectRaw('COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(CASE WHEN workflow_status IN ("pending") THEN 1 ELSE 0 END) as pending_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate]);
        
        $summaryData = $todaySummary->first();
        
        return [
            'period' => 'Daily',
            'current_date' => $date->format('F j, Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => (int)($summaryData->total_requests ?? 0),
                'fulfilled_requests' => (int)($summaryData->fulfilled_requests ?? 0),
                'pending_requests' => (int)($summaryData->pending_requests ?? 0),
                'total_value' => (float)($summaryData->total_value ?? 0),
            ],
            'records' => $todayRequests
        ];
    }

    private function getWeeklyReportData($date)
    {
        $startDate = $date->copy()->startOfWeek();
        $endDate = $date->copy()->endOfWeek();
        
        // Get all requests for the last 8 weeks in one query
        $requestsQuery = SupplyRequest::selectRaw('DATE(requests.created_at) as date, COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$date->copy()->subWeeks(7)->startOfWeek(), $endDate])
            ->groupByRaw('DATE(requests.created_at)')
            ->orderByRaw('DATE(requests.created_at)');
        
        $requestsData = $requestsQuery->get()->groupBy(function($item) {
            $date = Carbon::parse($item->date);
            return $date->format('Y-W') . sprintf('%02d', $date->weekOfYear);
        });
        
        // Build chart data for last 8 weeks
        $chartData = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $date->copy()->subWeeks($i)->startOfWeek();
            $weekKey = $weekStart->format('Y-W') . sprintf('%02d', $weekStart->weekOfYear);
            
            $weekData = $requestsData->get($weekKey, collect());
            $totalRequests = $weekData->sum('total_requests');
            $fulfilledRequests = $weekData->sum('fulfilled_requests');
            $totalValue = $weekData->sum('total_value');
            
            $chartData[] = [
                'date' => $weekStart->format('M j') . ' - ' . $weekStart->copy()->endOfWeek()->format('M j'),
                'requests' => (int)$totalRequests,
                'disbursements' => (int)$fulfilledRequests,
                'value' => (float)$totalValue
            ];
        }
        
        // Current week requests for display
        $weekRequests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        // Calculate current week summary
        $weekSummary = SupplyRequest::selectRaw('COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(CASE WHEN workflow_status = "pending" THEN 1 ELSE 0 END) as pending_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
            ->whereBetween('requests.created_at', [$startDate, $endDate]);
        
        $summaryData = $weekSummary->first();
        
        return [
            'period' => 'Weekly',
            'current_date' => $startDate->format('M j') . ' - ' . $endDate->format('M j, Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => (int)($summaryData->total_requests ?? 0),
                'fulfilled_requests' => (int)($summaryData->fulfilled_requests ?? 0),
                'pending_requests' => (int)($summaryData->pending_requests ?? 0),
                'total_value' => (float)($summaryData->total_value ?? 0),
            ],
            'records' => $weekRequests
        ];
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
                'pending_requests' => $monthRequests->where('workflow_status', 'pending')->count(),
                'total_value' => $monthRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $monthRequests->load(['user', 'item', 'item.category'])
        ];
    }

    private function getAnnualReportData($date)
    {
        $startDate = $date->copy()->startOfYear();
        $endDate = $date->copy()->endOfYear();
        
        // Get all requests for the last 5 years in one query
        $requestsQuery = SupplyRequest::selectRaw('YEAR(requests.created_at) as year, COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
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
        $yearSummary = SupplyRequest::selectRaw('COUNT(*) as total_requests, SUM(CASE WHEN workflow_status IN ("approved_by_admin", "fulfilled", "claimed") THEN 1 ELSE 0 END) as fulfilled_requests, SUM(CASE WHEN workflow_status = "pending" THEN 1 ELSE 0 END) as pending_requests, SUM(requests.quantity * COALESCE(items.unit_price, 0)) as total_value')
            ->leftJoin('items', 'requests.item_id', '=', 'items.id')
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
        $query = Item::with('category');

        // Apply filters
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('current_stock <= minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('current_stock', '>', 0);
                    break;
            }
        }

        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $items = $query->orderBy('name')->get();
        $categories = Category::all();

        $totals = [
            'total_items' => $items->count(),
            'total_value' => $items->sum('total_value'),
            'average_value' => $items->avg('total_value') ?? 0,
            'low_stock_count' => $items->where('current_stock', '<=', 'minimum_stock')->count(),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.inventory-summary', compact('items', 'totals', 'request'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('inventory-summary-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.inventory-summary', compact('items', 'categories', 'totals', 'request'));
    }

    public function lowStockAlert(Request $request)
    {
        $query = Item::with('category')
            ->whereRaw('current_stock <= minimum_stock OR current_stock <= 5');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $lowStockItems = $query->orderBy('current_stock', 'asc')->get();
        $categories = Category::all();

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.low-stock-alert', compact('lowStockItems'));
            return $pdf->download('low-stock-alert-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.low-stock-alert', compact('lowStockItems', 'categories'));
    }

    public function requestAnalytics(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $query = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->department) {
            $query->where('department', $request->department);
        }

        if ($request->status) {
            $query->where('workflow_status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        // Analytics calculations
        $analytics = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_requests' => $requests->where('workflow_status', 'pending')->count(),
            'declined_requests' => $requests->whereIn('workflow_status', ['declined_by_office_head', 'declined_by_admin'])->count(),
            'fulfilled_requests' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
            'average_processing_time' => $this->calculateAverageProcessingTime($requests),
            'most_requested_items' => $this->getMostRequestedItems($requests),
            'requests_by_department' => $this->getRequestsByDepartment($requests),
            'requests_by_priority' => $this->getRequestsByPriority($requests),
            'monthly_trend' => $this->getMonthlyTrend($dateFrom, $dateTo),
        ];

        $departments = SupplyRequest::distinct()->pluck('department')->filter();

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.request-analytics', compact('requests', 'analytics', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('request-analytics-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.request-analytics', compact('requests', 'analytics', 'departments', 'dateFrom', 'dateTo'));
    }

    public function departmentReport(Request $request)
    {
        $departments = SupplyRequest::distinct()->pluck('department')->filter();
        $selectedDepartment = $request->department;

        if (!$selectedDepartment) {
            return view('admin.reports.department-report', compact('departments'));
        }

        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $requests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->where('department', $selectedDepartment)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $departmentStats = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_requests' => $requests->where('workflow_status', 'pending')->count(),
            'total_value_requested' => $requests->sum(function($req) {
                return $req->quantity * $req->item->unit_price;
            }),
            'most_active_users' => $requests->groupBy('user_id')->map(function($userRequests) {
                return [
                    'user' => $userRequests->first()->user,
                    'count' => $userRequests->count()
                ];
            })->sortByDesc('count')->take(5),
            'most_requested_categories' => $requests->groupBy('item.category_id')->map(function($categoryRequests) {
                return [
                    'category' => $categoryRequests->first()->item->category,
                    'count' => $categoryRequests->count()
                ];
            })->sortByDesc('count')->take(5),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.department-report', compact('requests', 'departmentStats', 'selectedDepartment', 'dateFrom', 'dateTo'));
            return $pdf->download('department-report-' . $selectedDepartment . '-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.department-report', compact('departments', 'requests', 'departmentStats', 'selectedDepartment', 'dateFrom', 'dateTo'));
    }

    public function financialSummary(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfYear()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        // Inventory value
        $totalInventoryValue = Item::sum('total_value');
        $lowStockValue = Item::whereRaw('current_stock <= minimum_stock')->sum('total_value');

        // Request values
        $requestsInPeriod = SupplyRequest::with('item')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $totalRequestedValue = $requestsInPeriod->sum(function($request) {
            return $request->quantity * $request->item->unit_price;
        });

        $approvedRequestsValue = $requestsInPeriod
            ->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])
            ->sum(function($request) {
                return $request->quantity * $request->item->unit_price;
            });

        // Category-wise analysis
        $categoryAnalysis = Category::with('items')->get()->map(function($category) {
            return [
                'category' => $category,
                'total_items' => $category->items->count(),
                'total_value' => $category->items->sum('total_value'),
                'low_stock_items' => $category->items->where('current_stock', '<=', 'minimum_stock')->count(),
            ];
        })->sortByDesc('total_value');

        $financialData = [
            'total_inventory_value' => $totalInventoryValue,
            'low_stock_value' => $lowStockValue,
            'total_requested_value' => $totalRequestedValue,
            'approved_requests_value' => $approvedRequestsValue,
            'utilization_rate' => $totalInventoryValue > 0 ? ($approvedRequestsValue / $totalInventoryValue) * 100 : 0,
            'category_analysis' => $categoryAnalysis,
            'monthly_trends' => $this->getMonthlyFinancialTrends($dateFrom, $dateTo),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.financial-summary', compact('financialData', 'dateFrom', 'dateTo'));
            return $pdf->download('financial-summary-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.financial-summary', compact('financialData', 'dateFrom', 'dateTo'));
    }

    public function userActivityReport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $logs = Log::with('user')
            ->whereBetween('timestamp', [$dateFrom, $dateTo])
            ->orderBy('timestamp', 'desc')
            ->get();

        $activityStats = [
            'total_activities' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'activities_by_action' => $logs->groupBy('action')->map->count(),
            'most_active_users' => $logs->groupBy('user_id')->map(function($userLogs) {
                return [
                    'user' => $userLogs->first()->user,
                    'activity_count' => $userLogs->count(),
                    'last_activity' => $userLogs->first()->timestamp
                ];
            })->sortByDesc('activity_count')->take(10),
            'daily_activity' => $logs->groupBy(function($log) {
                return Carbon::parse($log->timestamp)->format('Y-m-d');
            })->map->count(),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.user-activity', compact('logs', 'activityStats', 'dateFrom', 'dateTo'));
            return $pdf->download('user-activity-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.user-activity', compact('logs', 'activityStats', 'dateFrom', 'dateTo'));
    }

    /**
     * QR Code Scan Analytics Report
     */
    public function qrScanAnalytics(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $scanStats = \App\Models\ItemScanLog::getScanStats($dateFrom, $dateTo);

        $scanLogs = \App\Models\ItemScanLog::with(['item', 'user'])
            ->whereBetween('scanned_at', [$dateFrom, $dateTo])
            ->orderBy('scanned_at', 'desc')
            ->get();

        $analytics = [
            'total_scans' => $scanStats['total_scans'],
            'unique_items_scanned' => $scanStats['unique_items_scanned'],
            'unique_users_scanning' => $scanStats['unique_users_scanning'],
            'scans_by_scanner_type' => $scanStats['scans_by_scanner_type'],
            'most_scanned_items' => $scanStats['most_scanned_items'],
            'scans_by_location' => $scanStats['scans_by_location'],
            'daily_scan_trend' => $this->getDailyScanTrend($dateFrom, $dateTo),
            'scan_frequency_analysis' => $this->getOverallScanFrequency(),
            'unscanned_items' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
            'scan_alerts' => \App\Models\ItemScanLog::getScanAlerts(),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.qr-scan-analytics', compact('scanLogs', 'analytics', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('qr-scan-analytics-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.qr-scan-analytics', compact('scanLogs', 'analytics', 'dateFrom', 'dateTo'));
    }

    /**
     * QR Code Scan History for Specific Item
     */
    public function itemScanHistory(Request $request, $itemId)
    {
        $item = \App\Models\Item::findOrFail($itemId);

        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $scanLogs = \App\Models\ItemScanLog::with('user')
            ->where('item_id', $itemId)
            ->whereBetween('scanned_at', [$dateFrom, $dateTo])
            ->orderBy('scanned_at', 'desc')
            ->get();

        $frequencyAnalysis = \App\Models\ItemScanLog::getScanFrequencyAnalysis($itemId);

        $analytics = [
            'total_scans' => $scanLogs->count(),
            'first_scan' => $scanLogs->isNotEmpty() ? $scanLogs->last()->scanned_at : null,
            'last_scan' => $scanLogs->isNotEmpty() ? $scanLogs->first()->scanned_at : null,
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

        return view('admin.reports.item-scan-history', compact('item', 'scanLogs', 'analytics', 'dateFrom', 'dateTo'));
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

    private function getMonthlyFinancialTrends($dateFrom, $dateTo)
    {
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $trends = [];

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();

            $monthRequests = SupplyRequest::with('item')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();

            $trends[$start->format('Y-m')] = [
                'requested_value' => $monthRequests->sum(function($req) {
                    return $req->quantity * $req->item->unit_price;
                }),
                'approved_value' => $monthRequests->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])
                    ->sum(function($req) {
                        return $req->quantity * $req->item->unit_price;
                    }),
            ];
            
            $start->addMonth();
        }

        return $trends;
    }

    /**
     * Daily transactions report
     */
    public function dailyTransactions(Request $request)
    {
        $date = $request->date ?? Carbon::today()->toDateString();
        
        $transactions = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_requests' => $transactions->count(),
            'approved_today' => $transactions->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'pending_today' => $transactions->where('workflow_status', 'pending')->count(),
            'total_value' => $transactions->sum(function($req) {
                return $req->quantity * ($req->item->unit_price ?? 0);
            }),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.daily-transactions', compact('transactions', 'stats', 'date'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('daily-transactions-' . $date . '.pdf');
        }

        return view('admin.reports.daily-transactions', compact('transactions', 'stats', 'date'));
    }

    /**
     * Daily disbursement report
     */
    public function dailyDisbursement(Request $request)
    {
        $date = $request->date ?? Carbon::today()->toDateString();
        
        $disbursements = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereDate('updated_at', $date)
            ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'total_disbursed' => $disbursements->count(),
            'total_value' => $disbursements->sum(function($req) {
                return $req->quantity * ($req->item->unit_price ?? 0);
            }),
            'total_quantity' => $disbursements->sum('quantity'),
            'unique_items' => $disbursements->pluck('item_id')->unique()->count(),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.daily-disbursement', compact('disbursements', 'stats', 'date'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('daily-disbursement-' . $date . '.pdf');
        }

        return view('admin.reports.daily-disbursement', compact('disbursements', 'stats', 'date'));
    }

    /**
     * Weekly summary report
     */
    public function weeklySummary(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfWeek()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfWeek()->toDateString();
        
        $weeklyData = [
            'requests' => SupplyRequest::with(['user', 'item', 'item.category'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
            'disbursements' => SupplyRequest::with(['user', 'item', 'item.category'])
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
                ->get(),
            'new_items' => Item::with('category')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(),
        ];

        $stats = [
            'total_requests' => $weeklyData['requests']->count(),
            'total_disbursements' => $weeklyData['disbursements']->count(),
            'new_items_added' => $weeklyData['new_items']->count(),
            'total_request_value' => $weeklyData['requests']->sum(function($req) {
                return $req->quantity * ($req->item->unit_price ?? 0);
            }),
            'daily_breakdown' => $this->getWeeklyBreakdown($startDate, $endDate),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.weekly-summary', compact('weeklyData', 'stats', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('weekly-summary-' . $startDate . '-to-' . $endDate . '.pdf');
        }

        return view('admin.reports.weekly-summary', compact('weeklyData', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Weekly requests report
     */
    public function weeklyRequests(Request $request)
    {
        $startDate = $request->start_date ?? Carbon::now()->startOfWeek()->toDateString();
        $endDate = $request->end_date ?? Carbon::now()->endOfWeek()->toDateString();
        
        $requests = SupplyRequest::with(['user', 'item', 'item.category'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $analytics = [
            'total_requests' => $requests->count(),
            'by_department' => $requests->groupBy('department')->map->count(),
            'by_status' => $requests->groupBy('workflow_status')->map->count(),
            'by_priority' => $requests->groupBy('priority')->map->count(),
            'most_requested' => $this->getMostRequestedItems($requests),
            'daily_trend' => $this->getDailyTrend($startDate, $endDate),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.weekly-requests', compact('requests', 'analytics', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('weekly-requests-' . $startDate . '-to-' . $endDate . '.pdf');
        }

        return view('admin.reports.weekly-requests', compact('requests', 'analytics', 'startDate', 'endDate'));
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
            'inventory_changes' => Item::with('category')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->get(),
            'low_stock_items' => Item::with('category')
                ->whereRaw('current_stock <= minimum_stock')
                ->get(),
        ];

        $analytics = [
            'total_requests' => $monthlyData['requests']->count(),
            'approved_requests' => $monthlyData['requests']->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
            'monthly_value' => $monthlyData['requests']->sum(function($req) {
                return $req->quantity * ($req->item->unit_price ?? 0);
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
     * Annual summary report
     */
    public function annualSummary(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        
        $annualData = [
            'total_requests' => SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_items' => Item::count(),
            'total_value' => Item::sum('total_value'),
            'requests_by_month' => $this->getAnnualMonthlyBreakdown($year),
            'top_departments' => $this->getTopDepartments($year),
            'inventory_growth' => $this->getInventoryGrowth($year),
            'financial_summary' => $this->getAnnualFinancialSummary($year),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.annual-summary', compact('annualData', 'year'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('annual-summary-' . $year . '.pdf');
        }

        return view('admin.reports.annual-summary', compact('annualData', 'year'));
    }

    // Helper methods for new reports
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

    private function getDepartmentPerformance($requests)
    {
        return $requests->groupBy('department')->map(function($deptRequests) {
            return [
                'total' => $deptRequests->count(),
                'approved' => $deptRequests->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count(),
                'pending' => $deptRequests->where('workflow_status', 'pending')->count(),
                'approval_rate' => $deptRequests->count() > 0 ? 
                    ($deptRequests->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed'])->count() / $deptRequests->count()) * 100 : 0
            ];
        });
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
            'items_added' => Item::whereBetween('created_at', [$startDate, $endDate])->count(),
            'value_added' => Item::whereBetween('created_at', [$startDate, $endDate])->sum('total_value'),
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
                return $req->quantity * ($req->item->unit_price ?? 0);
            }),
            'total_disbursed_value' => $requests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })
                ->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            'current_inventory_value' => Item::sum('total_value'),
        ];
    }

    /**
     * AJAX endpoint for dashboard data
     */
    public function dashboardData(Request $request)
    {
        // Check if workflow_status column exists
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

        $period = $request->get('period', 'daily');
        $data = $this->getReportData($period);
        
        // Add QR scan statistics to dashboard data
        $scanStats = \App\Models\ItemScanLog::getScanStats();
        $data['qr_scan_stats'] = [
            'total_scans_today' => \App\Models\ItemScanLog::whereDate('scanned_at', Carbon::today())->count(),
            'total_scans' => $scanStats['total_scans'],
            'unique_items_scanned' => $scanStats['unique_items_scanned'],
            'unscanned_items_30_days' => \App\Models\ItemScanLog::getUnscannedItems(30)->count(),
        ];
        
        return response()->json($data);
    }

    /**
     * Export daily report as CSV
     */
    public function dailyCsv(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $data = $this->getDailyReportData(Carbon::parse($date));

        $filename = 'daily-report-' . $date . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Date', 'Requests', 'Fulfilled', 'Pending', 'Total Value']);

            // Chart data
            foreach ($data['chart_data'] as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['requests'],
                    $row['disbursements'],
                    $data['summary']['pending_requests'] ?? 0,
                    number_format($row['value'], 2)
                ]);
            }

            // Summary row
            fputcsv($file, []);
            fputcsv($file, ['Summary', '', '', '', '']);
            fputcsv($file, ['Total Requests', $data['summary']['total_requests'], '', '', '']);
            fputcsv($file, ['Fulfilled Requests', $data['summary']['fulfilled_requests'], '', '', '']);
            fputcsv($file, ['Pending Requests', $data['summary']['pending_requests'], '', '', '']);
            fputcsv($file, ['Total Value', '', '', '', number_format($data['summary']['total_value'], 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export weekly report as CSV
     */
    public function weeklyCsv(Request $request)
    {
        $date = $request->get('date', Carbon::now()->toDateString());
        $data = $this->getWeeklyReportData(Carbon::parse($date));

        $filename = 'weekly-report-' . Carbon::parse($date)->startOfWeek()->format('Y-m-d') . '-to-' . Carbon::parse($date)->endOfWeek()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Week', 'Requests', 'Fulfilled', 'Pending', 'Total Value']);

            // Chart data
            foreach ($data['chart_data'] as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['requests'],
                    $row['disbursements'],
                    $data['summary']['pending_requests'] ?? 0,
                    number_format($row['value'], 2)
                ]);
            }

            // Summary row
            fputcsv($file, []);
            fputcsv($file, ['Summary', '', '', '', '']);
            fputcsv($file, ['Total Requests', $data['summary']['total_requests'], '', '', '']);
            fputcsv($file, ['Fulfilled Requests', $data['summary']['fulfilled_requests'], '', '', '']);
            fputcsv($file, ['Pending Requests', $data['summary']['pending_requests'], '', '', '']);
            fputcsv($file, ['Total Value', '', '', '', number_format($data['summary']['total_value'], 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export annual report as CSV
     */
    public function annualCsv(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $data = $this->getAnnualReportData(Carbon::createFromDate($year, 1, 1));

        $filename = 'annual-report-' . $year . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data, $year) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Year', 'Requests', 'Fulfilled', 'Pending', 'Total Value']);

            // Chart data
            foreach ($data['chart_data'] as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['requests'],
                    $row['disbursements'],
                    $data['summary']['pending_requests'] ?? 0,
                    number_format($row['value'], 2)
                ]);
            }

            // Summary row
            fputcsv($file, []);
            fputcsv($file, ['Summary', '', '', '', '']);
            fputcsv($file, ['Total Requests', $data['summary']['total_requests'], '', '', '']);
            fputcsv($file, ['Fulfilled Requests', $data['summary']['fulfilled_requests'], '', '', '']);
            fputcsv($file, ['Pending Requests', $data['summary']['pending_requests'], '', '', '']);
            fputcsv($file, ['Total Value', '', '', '', number_format($data['summary']['total_value'], 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get daily scan trend for QR analytics
     */
    private function getDailyScanTrend($dateFrom, $dateTo)
    {
        return \App\Models\ItemScanLog::whereBetween('scanned_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as scan_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('scan_count', 'date');
    }

    /**
     * Get overall scan frequency analysis
     */
    private function getOverallScanFrequency()
    {
        $allItems = \App\Models\Item::all();
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
}
