<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Reports Index/Dashboard
     */
    public function index(Request $request)
    {
        // Get overall statistics
        $stats = [
            'total_items' => \App\Models\Consumable::count() + \App\Models\NonConsumable::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_requests' => \App\Models\Request::count(),
            'pending_requests' => \App\Models\Request::where('status', 'pending')->count(),
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::count(), // All users are considered active in this system
        ];

        // Get recent QR scan activity
        $recentScans = \App\Models\ItemScanLog::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get QR scan statistics
        $qrStats = [
            'total_scans_today' => \App\Models\ItemScanLog::whereDate('created_at', today())->count(),
            'total_scans_this_week' => \App\Models\ItemScanLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_scans_this_month' => \App\Models\ItemScanLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'unique_items_scanned' => \App\Models\ItemScanLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->distinct('item_id')->count(),
            'active_scanners' => \App\Models\ItemScanLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->distinct('user_id')->count(),
        ];

        return view('admin.reports.index', compact('stats', 'recentScans', 'qrStats'));
    }

    /**
     * Inventory Summary Report
     */
    public function inventorySummary(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get inventory data
        $consumables = \App\Models\Consumable::with('category')->get();
        $nonConsumables = \App\Models\NonConsumable::with('category')->get();

        $inventoryStats = [
            'total_consumables' => $consumables->count(),
            'total_non_consumables' => $nonConsumables->count(),
            'total_value_consumables' => $consumables->sum(function($item) {
                return $item->quantity * $item->unit_cost;
            }),
            'total_value_non_consumables' => $nonConsumables->sum(function($item) {
                return $item->quantity * $item->unit_cost;
            }),
            'low_stock_consumables' => $consumables->where('quantity', '<=', 10)->count(),
            'low_stock_non_consumables' => $nonConsumables->where('quantity', '<=', 5)->count(),
            'expiring_soon' => $consumables->where('expiration_date', '<=', now()->addDays(30))->count(),
        ];

        // Get QR scan data for the period
        $qrScanData = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.inventory-summary', compact('consumables', 'nonConsumables', 'inventoryStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('inventory-summary-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.inventory-summary', compact('consumables', 'nonConsumables', 'inventoryStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'));
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStockAlert(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get low stock items
        $lowStockConsumables = \App\Models\Consumable::with('category')
            ->where('quantity', '<=', 10)
            ->orderBy('quantity', 'asc')
            ->get();

        $lowStockNonConsumables = \App\Models\NonConsumable::with('category')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity', 'asc')
            ->get();

        $expiringSoon = \App\Models\Consumable::with('category')
            ->where('expiration_date', '<=', now()->addDays(30))
            ->where('expiration_date', '>=', now())
            ->orderBy('expiration_date', 'asc')
            ->get();

        $alertStats = [
            'low_stock_consumables_count' => $lowStockConsumables->count(),
            'low_stock_non_consumables_count' => $lowStockNonConsumables->count(),
            'expiring_soon_count' => $expiringSoon->count(),
            'critical_stock' => $lowStockConsumables->where('quantity', '<=', 5)->count() + $lowStockNonConsumables->where('quantity', '<=', 2)->count(),
        ];

        // Get QR scan data for the period
        $qrScanData = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.low-stock-alert', compact('lowStockConsumables', 'lowStockNonConsumables', 'expiringSoon', 'alertStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('low-stock-alert-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.low-stock-alert', compact('lowStockConsumables', 'lowStockNonConsumables', 'expiringSoon', 'alertStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'));
    }

    /**
     * Request Analytics Report
     */
    public function requestAnalytics(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get request data
        $requests = \App\Models\Request::with(['user', 'items'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $requestStats = [
            'total_requests' => $requests->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'approved_requests' => $requests->where('status', 'approved')->count(),
            'completed_requests' => $requests->where('status', 'completed')->count(),
            'declined_requests' => $requests->where('status', 'declined')->count(),
            'requests_by_status' => $requests->groupBy('status')->map->count(),
            'requests_by_user' => $requests->groupBy('user_id')->map(function($userRequests) {
                return [
                    'user' => $userRequests->first()->user,
                    'count' => $userRequests->count(),
                    'completed' => $userRequests->where('status', 'completed')->count(),
                ];
            })->sortByDesc('count')->take(10),
            'average_processing_time' => $this->calculateAverageProcessingTime($requests),
        ];

        // Get QR scan data for the period
        $qrScanData = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.request-analytics', compact('requests', 'requestStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('request-analytics-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.request-analytics', compact('requests', 'requestStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'));
    }

    /**
     * User Activity Report
     */
    public function userActivityReport(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get user activity data
        $users = \App\Models\User::with(['requests', 'scanLogs'])->get();

        $userActivity = $users->map(function($user) use ($dateFrom, $dateTo) {
            $userRequests = $user->requests()->whereBetween('created_at', [$dateFrom, $dateTo])->get();
            $userScans = $user->scanLogs()->whereBetween('created_at', [$dateFrom, $dateTo])->get();

            return [
                'user' => $user,
                'total_requests' => $userRequests->count(),
                'completed_requests' => $userRequests->where('status', 'completed')->count(),
                'total_scans' => $userScans->count(),
                'unique_items_scanned' => $userScans->pluck('item_id')->unique()->count(),
                'last_activity' => max(
                    $userRequests->max('created_at'),
                    $userScans->max('created_at')
                ),
            ];
        })->filter(function($activity) {
            return $activity['total_requests'] > 0 || $activity['total_scans'] > 0;
        })->sortByDesc(function($activity) {
            return $activity['total_requests'] + $activity['total_scans'];
        });

        // Build userStats structure expected by the view
        $userStats = [
            'total_users' => $users->count(),
            'active_users' => $userActivity->count(),
            'total_scans' => $userActivity->pluck('total_scans')->sum(),
            'avg_scans_per_user' => $userActivity->count() > 0 ? round($userActivity->pluck('total_scans')->sum() / $userActivity->count(), 1) : 0,
            'most_active_users' => $userActivity->take(10)->map(function($activity) {
                return [
                    'name' => $activity['user']->name,
                    'role' => $activity['user']->role,
                    'scan_count' => $activity['total_scans'],
                    'last_activity' => $activity['last_activity'] ? Carbon::parse($activity['last_activity']) : null,
                ];
            }),
            'recent_activities' => \App\Models\ItemScanLog::with(['user', 'item'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->orderBy('created_at', 'desc')
                ->take(15)
                ->get()
                ->map(function($scan) {
                    return [
                        'user_name' => $scan->user->name,
                        'item_name' => $scan->item ? ($scan->item->name ?? 'Unknown Item') : 'Unknown Item',
                        'created_at' => $scan->created_at,
                    ];
                }),
            'users_by_role' => $users->groupBy('role')->map(function($roleUsers) use ($dateFrom, $dateTo) {
                $roleScans = \App\Models\ItemScanLog::whereIn('user_id', $roleUsers->pluck('id'))
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count();
                return [
                    'total_users' => $roleUsers->count(),
                    'active_users' => $roleUsers->filter(function($user) use ($dateFrom, $dateTo) {
                        return $user->scanLogs()->whereBetween('created_at', [$dateFrom, $dateTo])->exists() ||
                               $user->requests()->whereBetween('created_at', [$dateFrom, $dateTo])->exists();
                    })->count(),
                    'total_scans' => $roleScans,
                ];
            }),
            'user_registration_trends' => $users->groupBy(function($user) {
                return $user->created_at->format('Y-m');
            })->map(function($monthUsers) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', collect($monthUsers)->first()->created_at->format('Y-m'))->startOfMonth(),
                    'count' => $monthUsers->count(),
                ];
            })->sortBy('month')->values(),
            'inactive_users' => $users->filter(function($user) use ($dateFrom, $dateTo) {
                return !$user->scanLogs()->whereBetween('created_at', [$dateFrom, $dateTo])->exists() &&
                       !$user->requests()->whereBetween('created_at', [$dateFrom, $dateTo])->exists();
            })->map(function($user) {
                $lastScan = $user->scanLogs()->latest('created_at')->first();
                $lastRequest = $user->requests()->latest('created_at')->first();
                $lastActivity = $lastScan && $lastRequest ?
                    max($lastScan->created_at, $lastRequest->created_at) :
                    ($lastScan ? $lastScan->created_at : ($lastRequest ? $lastRequest->created_at : null));
                return [
                    'name' => $user->name,
                    'role' => $user->role,
                    'last_activity' => $lastActivity,
                ];
            })->sortByDesc('last_activity'),
            'activity_distribution' => [
                'active' => $userActivity->where('total_scans', '>', 10)->count(),
                'moderate' => $userActivity->whereBetween('total_scans', [5, 10])->count(),
                'low' => $userActivity->whereBetween('total_scans', [1, 4])->count(),
                'inactive' => $users->count() - $userActivity->count(),
            ],
        ];

        // Get QR scan data for the period
        $qrScanData = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.user-activity', compact('userStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('user-activity-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.user-activity', compact(
            'userStats',
            'qrScanData',
            'period',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Dashboard Data (API endpoint for dashboard widgets)
     */
    public function dashboardData(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $dashboardData = [
            'inventory_overview' => [
                'total_items' => \App\Models\Consumable::count() + \App\Models\NonConsumable::count(),
                'low_stock_items' => \App\Models\Consumable::where('quantity', '<=', 10)->count() + \App\Models\NonConsumable::where('quantity', '<=', 5)->count(),
                'expiring_soon' => \App\Models\Consumable::where('expiration_date', '<=', now()->addDays(30))->count(),
            ],
            'request_overview' => [
                'total_requests' => \App\Models\Request::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'pending_requests' => \App\Models\Request::where('status', 'pending')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'completed_requests' => \App\Models\Request::where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'qr_scan_overview' => [
                'total_scans' => \App\Models\ItemScanLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'unique_items_scanned' => \App\Models\ItemScanLog::whereBetween('created_at', [$dateFrom, $dateTo])->distinct('item_id')->count(),
                'active_users' => \App\Models\ItemScanLog::whereBetween('created_at', [$dateFrom, $dateTo])->distinct('user_id')->count(),
            ],
            'recent_activity' => [
                'recent_requests' => \App\Models\Request::with('user')->orderBy('created_at', 'desc')->take(5)->get(),
                'recent_scans' => \App\Models\ItemScanLog::with(['user', 'item'])->orderBy('created_at', 'desc')->take(5)->get(),
            ],
        ];

        return response()->json($dashboardData);
    }

    /**
     * Calculate average processing time for requests
     */
    private function calculateAverageProcessingTime($requests)
    {
        $completedRequests = $requests->where('status', 'completed');

        if ($completedRequests->isEmpty()) {
            return 0;
        }

        $totalProcessingTime = 0;
        $count = 0;

        foreach ($completedRequests as $request) {
            if ($request->updated_at && $request->created_at) {
                $processingTime = $request->updated_at->diffInHours($request->created_at);
                $totalProcessingTime += $processingTime;
                $count++;
            }
        }

        return $count > 0 ? round($totalProcessingTime / $count, 1) : 0;
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
    public function itemScanHistory(Request $request, $itemId)
    {
        // Try to find the item in both Consumable and NonConsumable models
        $item = \App\Models\Consumable::find($itemId) ?? \App\Models\NonConsumable::find($itemId);

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
            ...\App\Models\Consumable::all(),
            ...\App\Models\NonConsumable::all(),
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
     * Monthly QR Scan Report
     */
    public function monthlySummary(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $monthlyScanData = [
            'scans' => \App\Models\ItemScanLog::with(['item', 'user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(),
        ];

        $analytics = [
            'total_scans' => $monthlyScanData['scans']->count(),
            'unique_items_scanned' => $monthlyScanData['scans']->pluck('item_id')->unique()->count(),
            'unique_users_scanning' => $monthlyScanData['scans']->pluck('user_id')->unique()->filter()->count(),
            'scans_by_location' => $monthlyScanData['scans']->whereNotNull('location')
                ->groupBy('location')
                ->map->count(),
            'weekly_breakdown' => $this->getMonthlyWeeklyScanBreakdown($startDate, $endDate),
            'most_scanned_items' => $this->getMostScannedItems($monthlyScanData['scans']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.monthly-scan-summary', compact('monthlyScanData', 'analytics', 'month', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('monthly-scan-summary-' . $month . '.pdf');
        }

        return view('admin.reports.monthly-scan-summary', compact('monthlyScanData', 'analytics', 'month', 'startDate', 'endDate'));
    }

    /**
     * Quarterly QR Scan Report
     */
    public function quarterlySummary(Request $request)
    {
        $quarter = $request->quarter ?? Carbon::now()->format('Y-Q');
        $year = explode('-', $quarter)[0];
        $quarterNum = explode('-', $quarter)[1];

        $startDate = Carbon::createFromDate($year, ($quarterNum - 1) * 3 + 1, 1)->startOfQuarter();
        $endDate = Carbon::createFromDate($year, ($quarterNum - 1) * 3 + 1, 1)->endOfQuarter();

        $quarterlyScanData = [
            'scans' => \App\Models\ItemScanLog::with(['item', 'user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(),
        ];

        $analytics = [
            'total_scans' => $quarterlyScanData['scans']->count(),
            'unique_items_scanned' => $quarterlyScanData['scans']->pluck('item_id')->unique()->count(),
            'unique_users_scanning' => $quarterlyScanData['scans']->pluck('user_id')->unique()->filter()->count(),
            'monthly_breakdown' => $this->getQuarterlyMonthlyScanBreakdown($startDate, $endDate),
            'most_scanned_items' => $this->getMostScannedItems($quarterlyScanData['scans']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.quarterly-scan-summary', compact('quarterlyScanData', 'analytics', 'quarter', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('quarterly-scan-summary-' . $quarter . '.pdf');
        }

        return view('admin.reports.quarterly-scan-summary', compact('quarterlyScanData', 'analytics', 'quarter', 'startDate', 'endDate'));
    }

    /**
     * Annual QR Scan Report
     */
    public function annualSummary(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $annualScanData = [
            'scans' => \App\Models\ItemScanLog::with(['item', 'user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get(),
        ];

        $analytics = [
            'total_scans' => $annualScanData['scans']->count(),
            'unique_items_scanned' => $annualScanData['scans']->pluck('item_id')->unique()->count(),
            'unique_users_scanning' => $annualScanData['scans']->pluck('user_id')->unique()->filter()->count(),
            'quarterly_breakdown' => $this->getAnnualQuarterlyScanBreakdown($year),
            'most_scanned_items' => $this->getMostScannedItems($annualScanData['scans']),
        ];

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.annual-scan-summary', compact('annualScanData', 'analytics', 'year', 'startDate', 'endDate'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('annual-scan-summary-' . $year . '.pdf');
        }

        return view('admin.reports.annual-scan-summary', compact('annualScanData', 'analytics', 'year', 'startDate', 'endDate'));
    }

    /**
     * Get most scanned items from scan logs
     */
    private function getMostScannedItems($scans)
    {
        return $scans->groupBy('item_id')
            ->map(function($itemScans) {
                return [
                    'item' => $itemScans->first()->item,
                    'total_scans' => $itemScans->count(),
                    'unique_users' => $itemScans->pluck('user_id')->unique()->filter()->count(),
                    'last_scan' => $itemScans->first()->created_at
                ];
            })
            ->sortByDesc('total_scans')
            ->take(10);
    }

    /**
     * Get monthly weekly scan breakdown
     */
    private function getMonthlyWeeklyScanBreakdown($startDate, $endDate)
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

            $weekScans = \App\Models\ItemScanLog::whereBetween('created_at', [$weekStart, $weekEnd])->count();

            $breakdown['Week ' . $weekNumber] = [
                'scans' => $weekScans,
                'start' => $weekStart->format('M d'),
                'end' => $weekEnd->format('M d')
            ];

            $current = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        return $breakdown;
    }

    /**
     * Get quarterly monthly scan breakdown
     */
    private function getQuarterlyMonthlyScanBreakdown($startDate, $endDate)
    {
        $breakdown = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            $monthScans = \App\Models\ItemScanLog::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->count();

            $breakdown[$current->format('M Y')] = [
                'scans' => $monthScans,
            ];

            $current->addMonth();
        }

        return $breakdown;
    }

    /**
     * Get annual quarterly scan breakdown
     */
    private function getAnnualQuarterlyScanBreakdown($year)
    {
        $breakdown = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterStart = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->startOfQuarter();
            $quarterEnd = Carbon::createFromDate($year, ($quarter - 1) * 3 + 1, 1)->endOfQuarter();

            $breakdown['Q' . $quarter . ' ' . $year] = [
                'scans' => \App\Models\ItemScanLog::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
            ];
        }

        return $breakdown;
    }
}
