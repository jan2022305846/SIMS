<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use Illuminate\Support\Facades\Log;

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
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'item_name' => optional($scan->item)->name ?? 'Unknown Item',
                    'user_name' => optional($scan->user)->name ?? 'Unknown User',
                    'action' => $scan->action,
                    'created_at' => $scan->created_at,
                    'item_type' => $scan->item_type,
                ];
            });

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
        $requests = \App\Models\Request::with(['user', 'requestItems'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        // Manually load itemable relationships for each request
        foreach ($requests as $request) {
            if ($request->requestItems->count() > 0) {
                foreach ($request->requestItems as $requestItem) {
                    if ($requestItem->item_type === 'consumable') {
                        $itemable = \App\Models\Consumable::find($requestItem->item_id);
                    } elseif ($requestItem->item_type === 'non_consumable') {
                        $itemable = \App\Models\NonConsumable::find($requestItem->item_id);
                    } else {
                        $itemable = null;
                    }
                    $requestItem->setRelation('itemable', $itemable);
                }
            }
        }

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
     * Get inventory data for reports dashboard (API endpoint)
     */
    public function getInventoryData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        Log::info('getInventoryData called', ['period' => $period, 'selection' => $selection]);

        // Get date range based on period and selection
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        Log::info('Date range calculated', ['from' => $dateFrom, 'to' => $dateTo]);

        // Get inventory statistics (consumables only for stock management)
        $consumables = \App\Models\Consumable::all();

        $summary = [
            'totalItems' => $consumables->count(), // Only consumables
            'totalAdded' => $this->getItemsAddedInPeriod($dateFrom, $dateTo),
            'totalReleased' => $this->getItemsReleasedInPeriod($dateFrom, $dateTo),
            'currentStock' => $consumables->sum('quantity'), // Only consumable stock
        ];

        // Get chart data based on period
        $chartData = $this->getInventoryChartData($period, $selection);

        // Get table data
        $tableData = $this->getInventoryTableData($dateFrom, $dateTo);

        return response()->json([
            'summary' => $summary,
            'chartData' => $chartData,
            'tableData' => $tableData,
        ]);
    }

    /**
     * Get QR scan data for reports dashboard (API endpoint)
     * Only returns scans for non-consumable items for monitoring and tracking purposes
     */
    public function getQrScanData(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get date range based on period and selection
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get QR scan statistics - only for non-consumable items
        $scans = \App\Models\ItemScanLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $summary = [
            'totalScans' => $scans->count(),
            'uniqueItems' => $scans->pluck('item_id')->unique()->count(),
            'activeUsers' => $scans->pluck('user_id')->unique()->filter()->count(),
            'todayScans' => \App\Models\ItemScanLog::whereDate('created_at', today())
                ->count(),
        ];

        // Get scan logs with relationships - only non-consumable items, limit to top 10 recent
        $scanLogs = \App\Models\ItemScanLog::with(['item', 'user', 'office'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->limit(10) // Top 10 recent scans only
            ->get()
            ->map(function($scan) {
                $item = $scan->item;
                // Get current location from the item itself (non-consumable items have location)
                $currentLocation = 'N/A';
                if ($item && $item instanceof \App\Models\NonConsumable) {
                    $currentLocation = $item->location ?? 'N/A';
                }

                return [
                    'timestamp' => $scan->created_at->format('Y-m-d H:i:s'),
                    'item' => $item ? $item->name : 'Unknown Item',
                    'user' => $scan->user ? $scan->user->name : 'Unknown User',
                    'scanner_type' => 'webcam', // Default, could be enhanced
                    'location' => $currentLocation, // Current location of the scanned item
                    'ip_address' => 'N/A', // Not stored in current model
                    'item_id' => $scan->item_id,
                    'user_id' => $scan->user_id,
                    'action' => $scan->action,
                    'notes' => $scan->notes,
                    'item_type' => 'non_consumable', // Since only non-consumables are scanned
                    'id' => $scan->id,
                ];
            });

        return response()->json([
            'summary' => $summary,
            'scanLogs' => $scanLogs,
        ]);
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
     * Stock Transactions Report
     */
    public function stockTransactions(Request $request)
    {
        $period = $request->get('period', 'monthly');

        // Get date range based on period
        $dateRange = $this->getDateRangeFromPeriod($period);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        // Get stock transaction logs
        $stockTransactions = \App\Models\Log::with(['user'])
            ->whereIn('action', ['claimed', 'assigned'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                // Get item details
                $item = null;
                $itemType = 'unknown';
                
                // Try to find the item in consumables first, then non-consumables
                $consumable = \App\Models\Consumable::find($log->item_id);
                if ($consumable) {
                    $item = $consumable;
                    $itemType = 'consumable';
                } else {
                    $nonConsumable = \App\Models\NonConsumable::find($log->item_id);
                    if ($nonConsumable) {
                        $item = $nonConsumable;
                        $itemType = 'non_consumable';
                    }
                }

                return [
                    'id' => $log->id,
                    'date' => $log->created_at,
                    'user_name' => $log->user ? $log->user->name : 'Unknown User',
                    'item_name' => $item ? $item->name : 'Unknown Item',
                    'item_type' => $itemType,
                    'action' => $log->action,
                    'quantity' => $log->quantity,
                    'details' => $log->details,
                    'notes' => $log->notes,
                ];
            });

        $transactionStats = [
            'total_transactions' => $stockTransactions->count(),
            'total_items_claimed' => $stockTransactions->where('action', 'claimed')->sum('quantity'),
            'total_items_assigned' => $stockTransactions->where('action', 'assigned')->count(),
            'unique_users' => $stockTransactions->pluck('user_name')->unique()->count(),
            'consumable_transactions' => $stockTransactions->where('item_type', 'consumable')->count(),
            'non_consumable_transactions' => $stockTransactions->where('item_type', 'non_consumable')->count(),
        ];

        // Get QR scan data for the period
        $qrScanData = $this->getQrScanReportData($period);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.stock-transactions', compact('stockTransactions', 'transactionStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');

            return $pdf->download('stock-transactions-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.stock-transactions', compact('stockTransactions', 'transactionStats', 'qrScanData', 'period', 'dateFrom', 'dateTo'));
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
                $firstScan = $itemScans->first();
                return [
                    'item' => $firstScan->item ?? null,
                    'total_scans' => $itemScans->count(),
                    'unique_users' => $itemScans->pluck('user_id')->unique()->filter()->count(),
                    'last_scan' => $firstScan->created_at
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

    /**
     * Get date range based on period and selection
     */
    private function getDateRangeFromPeriodAndSelection($period, $selection)
    {
        switch ($period) {
            case 'monthly':
                $date = Carbon::createFromFormat('Y-m', $selection);
                return [
                    'from' => $date->copy()->startOfMonth()->toDateString(),
                    'to' => $date->copy()->endOfMonth()->toDateString()
                ];
            case 'quarterly':
                // Handle quarterly based on the quarter selection (Q1, Q2, Q3, Q4)
                // For quarterly reports, we need to determine which quarter the selection represents
                // If selection is just "Q1", "Q2", etc., use current year
                // If selection contains year info, parse it accordingly
                $currentYear = Carbon::now()->year;

                if (preg_match('/Q(\d)/', $selection, $matches)) {
                    $quarter = (int) $matches[1];
                    $year = $currentYear; // Default to current year

                    // Calculate quarter start and end dates
                    $quarterStartMonth = ($quarter - 1) * 3 + 1; // Q1=1, Q2=4, Q3=7, Q4=10
                    $startDate = Carbon::createFromDate($year, $quarterStartMonth, 1)->startOfMonth();
                    $endDate = Carbon::createFromDate($year, $quarterStartMonth + 2, 1)->endOfMonth();

                    return [
                        'from' => $startDate->toDateString(),
                        'to' => $endDate->toDateString()
                    ];
                }
                // Fallback to current quarter
                $now = Carbon::now();
                return [
                    'from' => $now->copy()->startOfQuarter()->toDateString(),
                    'to' => $now->copy()->endOfQuarter()->toDateString()
                ];
            case 'annual':
                $date = Carbon::createFromFormat('Y', $selection);
                return [
                    'from' => $date->copy()->startOfYear()->toDateString(),
                    'to' => $date->copy()->endOfYear()->toDateString()
                ];
            default:
                return $this->getDateRangeFromPeriod($period);
        }
    }

    /**
     * Get items added in period
     */
    private function getItemsAddedInPeriod($dateFrom, $dateTo)
    {
        // For now, we'll use item creation dates as "added" dates
        // In a real system, you might have separate stock adjustment logs
        $consumablesAdded = \App\Models\Consumable::whereBetween('created_at', [$dateFrom, $dateTo])->sum('quantity');
        $nonConsumablesAdded = \App\Models\NonConsumable::whereBetween('created_at', [$dateFrom, $dateTo])->count(); // Count items, not quantity for non-consumables

        return $consumablesAdded + $nonConsumablesAdded;
    }

    /**
     * Get total items released (claimed) - overall count
     */
    private function getTotalItemsReleased()
    {
        // Count total items claimed through requests
        // Sum quantities from RequestItem relationships
        return \App\Models\RequestItem::whereHas('request', function($query) {
                $query->where('status', 'claimed');
            })
            ->where('item_type', 'consumable')
            ->sum('quantity');
    }

    /**
     * Get items released in period
     */
    private function getItemsReleasedInPeriod($dateFrom, $dateTo)
    {
        // Count items claimed through requests within the date range
        // Sum quantities from RequestItem instead of Request model
        return \App\Models\Request::where('status', 'claimed')
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->with('requestItems')
            ->get()
            ->sum(function($request) {
                return $request->requestItems->sum('quantity');
            });
    }

    /**
     * Get inventory chart data based on period
     */
    private function getInventoryChartData($period, $selection)
    {
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        switch ($period) {
            case 'monthly':
                return $this->getMonthlyInventoryChartData($dateFrom, $dateTo);
            case 'quarterly':
                return $this->getQuarterlyInventoryChartData($dateFrom, $dateTo);
            case 'annual':
                return $this->getAnnualInventoryChartData($dateFrom, $dateTo);
            default:
                return $this->getMonthlyInventoryChartData($dateFrom, $dateTo);
        }
    }

    /**
     * Get monthly inventory chart data
     */
    private function getMonthlyInventoryChartData($dateFrom, $dateTo)
    {
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $chartData = [];
        $current = $startDate->copy()->startOfWeek();

        // Get initial stock at the beginning of the period
        $initialStock = \App\Models\Consumable::sum('quantity');

        while ($current <= $endDate) {
            $weekEnd = $current->copy()->endOfWeek();
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate;
            }

            // Calculate remaining stock at the end of this week
            // This is a simplified calculation - in a real system you'd track stock changes over time
            $releasedThisWeek = \App\Models\RequestItem::whereHas('request', function($query) use ($current, $weekEnd) {
                    $query->where('status', 'claimed')
                          ->whereBetween('updated_at', [$current, $weekEnd]);
                })
                ->where('item_type', 'consumable')
                ->sum('quantity');

            // For now, assume stock decreases by released amount each week
            // In a real system, you'd need proper stock movement tracking
            $remainingStock = $initialStock - $releasedThisWeek;

            $chartData[] = [
                'date' => 'Week ' . $current->weekOfMonth,
                'remaining' => (int)$remainingStock,
                'released' => (int)$releasedThisWeek,
            ];

            $current = $weekEnd->copy()->addDay();
            $initialStock = $remainingStock; // Update for next iteration
        }

        return $chartData;
    }

    /**
     * Get quarterly inventory chart data
     */
    private function getQuarterlyInventoryChartData($dateFrom, $dateTo)
    {
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $chartData = [];
        $current = $startDate->copy();

        // Get initial stock at the beginning of the period
        $initialStock = \App\Models\Consumable::sum('quantity');

        while ($current <= $endDate) {
            $monthEnd = $current->copy()->endOfMonth();

            // Calculate remaining stock at the end of this month
            $releasedThisMonth = \App\Models\RequestItem::whereHas('request', function($query) use ($current, $monthEnd) {
                    $query->where('status', 'claimed')
                          ->whereBetween('updated_at', [$current, $monthEnd]);
                })
                ->where('item_type', 'consumable')
                ->sum('quantity');

            // For now, assume stock decreases by released amount each month
            $remainingStock = $initialStock - $releasedThisMonth;

            $chartData[] = [
                'date' => $current->format('M Y'),
                'remaining' => (int)$remainingStock,
                'released' => (int)$releasedThisMonth,
            ];

            $current->addMonth();
            $initialStock = $remainingStock; // Update for next iteration
        }

        return $chartData;
    }

    /**
     * Get annual inventory chart data
     */
    private function getAnnualInventoryChartData($dateFrom, $dateTo)
    {
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $chartData = [];
        $current = $startDate->copy();

        // Get initial stock at the beginning of the period
        $initialStock = \App\Models\Consumable::sum('quantity');

        while ($current <= $endDate) {
            $quarterEnd = $current->copy()->endOfQuarter();

            // Calculate remaining stock at the end of this quarter
            $releasedThisQuarter = \App\Models\RequestItem::whereHas('request', function($query) use ($current, $quarterEnd) {
                    $query->where('status', 'claimed')
                          ->whereBetween('updated_at', [$current, $quarterEnd]);
                })
                ->where('item_type', 'consumable')
                ->sum('quantity');

            // For now, assume stock decreases by released amount each quarter
            $remainingStock = $initialStock - $releasedThisQuarter;

            $chartData[] = [
                'date' => 'Q' . $current->quarter . ' ' . $current->year,
                'remaining' => (int)$remainingStock,
                'released' => (int)$releasedThisQuarter,
            ];

            $current->addQuarter();
            $initialStock = $remainingStock; // Update for next iteration
        }

        return $chartData;
    }

    /**
     * Get inventory table data (consumables only for stock management)
     * Total Quantity = Remaining Stock + Released Quantity
     */
    private function getInventoryTableData($dateFrom, $dateTo)
    {
        // Get only consumable items (non-consumables don't need stock management)
        $consumables = \App\Models\Consumable::with('category')->get();

        $tableData = [];

        // Process only consumables
        foreach ($consumables as $item) {
            // Count how many were released/claimed in this period
            // Sum quantities from RequestItem relationships for this specific consumable item
            $releasedInPeriod = \App\Models\RequestItem::whereHas('request', function($query) use ($dateFrom, $dateTo) {
                    $query->where('status', 'claimed')
                          ->whereBetween('updated_at', [$dateFrom, $dateTo]);
                })
                ->where('item_type', 'consumable')
                ->where('item_id', $item->id)
                ->sum('quantity');

            // Calculate total quantity: Remaining Stock + Released Quantity
            $remainingStock = $item->quantity;
            $totalQuantity = $remainingStock + $releasedInPeriod;

            $tableData[] = [
                'name' => $item->name,
                'released' => $releasedInPeriod,
                'totalQuantity' => $totalQuantity,
                'remaining' => $remainingStock,
            ];
        }

        return $tableData;
    }

    /**
     * Download report PDF or DOCX
     */
    public function downloadReport(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');
        $format = $request->get('format', 'pdf');

        // Get data for the report
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $data = [
            'period' => $period,
            'selection' => $selection,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => [
                'totalItems' => \App\Models\Consumable::count(), // Only consumables for stock management
                'totalAdded' => $this->getItemsAddedInPeriod($dateFrom, $dateTo),
                'totalReleased' => $this->getItemsReleasedInPeriod($dateFrom, $dateTo),
                'currentStock' => \App\Models\Consumable::sum('quantity'), // Only consumable stock
            ],
            'chartData' => $this->getInventoryChartData($period, $selection),
            'tableData' => $this->getInventoryTableData($dateFrom, $dateTo),
        ];

        if ($format === 'docx') {
            try {
                return $this->generateDocxReport($data);
            } catch (\Exception $e) {
                // Fallback to PDF if DOCX generation fails
                Log::warning('DOCX report generation failed, falling back to PDF: ' . $e->getMessage());
                
                $pdf = Pdf::loadView('admin.reports.pdf.inventory-report', compact('data'))
                    ->setPaper('a4', 'landscape');

                $filename = 'inventory-report-' . $period . '-' . $selection . '.pdf';
                return $pdf->download($filename)->withHeaders([
                    'X-Fallback-Message' => 'DOCX export failed. PDF version downloaded instead.'
                ]);
            }
        } else {
            // Default to PDF
            $pdf = Pdf::loadView('admin.reports.pdf.inventory-report', compact('data'))
                ->setPaper('a4', 'landscape');

            $filename = 'inventory-report-' . $period . '-' . $selection . '.pdf';
            return $pdf->download($filename);
        }
    }

    /**
     * Generate DOCX report with ZipArchive check
     */
    private function generateDocxReport($data)
    {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            throw new \Exception('PHP ZipArchive extension is required for DOCX export. Please contact your administrator.');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Supply Office System');
        $properties->setCompany('Supply Office');
        $properties->setTitle('Inventory Report');
        $properties->setDescription('Monthly Inventory Report');
        $properties->setCategory('Reports');
        $properties->setLastModifiedBy('System');
        $properties->setCreated(time());
        $properties->setModified(time());

        // Add a section
        $section = $phpWord->addSection();

        // Add title
        $section->addTitle('Inventory Report', 1);
        $section->addText('Period: ' . ucfirst($data['period']) . ' - ' . $data['selection']);
        $section->addText('Report Date: ' . date('F j, Y'));
        $section->addTextBreak(1);

        // Add summary
        $section->addTitle('Summary', 2);
        $summary = $data['summary'];
        $section->addText('Total Items: ' . $summary['totalItems']);
        $section->addText('Items Added/Restocked: ' . $summary['totalAdded']);
        $section->addText('Items Released/Claimed: ' . $summary['totalReleased']);
        $section->addText('Current Stock: ' . $summary['currentStock']);
        $section->addTextBreak(1);

        // Add calculation explanation
        $section->addTitle('Calculation Method', 2);
        $section->addText('Total Quantity = Remaining Stock + Released Quantity');
        $section->addText('• Remaining Stock: Current quantity available in inventory');
        $section->addText('• Released Quantity: Items claimed/released during the selected period');
        $section->addText('• Total Quantity: Combined total representing inventory movement');
        $section->addTextBreak(1);

        // Add table data
        $section->addTitle('Inventory Details', 2);

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(4000)->addText('Item Name');
        $table->addCell(1750)->addText('Released/Claimed');
        $table->addCell(1750)->addText('Total Quantity');
        $table->addCell(1750)->addText('Remaining Stock');

        foreach ($data['tableData'] as $item) {
            $table->addRow();
            $table->addCell(4000)->addText($item['name']);
            $table->addCell(1750)->addText($item['released']);
            $table->addCell(1750)->addText($item['totalQuantity']);
            $table->addCell(1750)->addText($item['remaining']);
        }

        $filename = 'inventory-report-' . $data['period'] . '-' . $data['selection'] . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $phpWord->save($tempFile, 'Word2007');

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Generate QR Scan Logs DOCX report
     */
    private function generateQrScanLogsDocx($data)
    {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            throw new \Exception('PHP ZipArchive extension is required for DOCX export. Please contact your administrator.');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Supply Office System');
        $properties->setCompany('Supply Office');
        $properties->setTitle('QR Scan Logs Report');
        $properties->setDescription('QR Code Scan Logs Report');
        $properties->setCategory('Reports');
        $properties->setLastModifiedBy('System');
        $properties->setCreated(time());
        $properties->setModified(time());

        // Add a section
        $section = $phpWord->addSection();

        // Add title
        $section->addTitle('QR Scan Logs Report', 1);
        $section->addText('Period: ' . ucfirst($data['period']) . ' - ' . $data['selection']);
        $section->addText('Report Date: ' . date('F j, Y'));
        $section->addTextBreak(1);

        // Add summary
        $section->addTitle('Summary', 2);
        $summary = $data['summary'];
        $section->addText('Total Scans: ' . $summary['totalScans']);
        $section->addText('Unique Items Scanned: ' . $summary['uniqueItems']);
        $section->addText('Active Users: ' . $summary['activeUsers']);
        $section->addTextBreak(1);

        // Add scan logs table
        $section->addTitle('Scan Logs Details', 2);

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(1500)->addText('Timestamp');
        $table->addCell(2000)->addText('Item Scanned');
        $table->addCell(1500)->addText('User');
        $table->addCell(1500)->addText('Action');
        $table->addCell(1500)->addText('Location');
        $table->addCell(2000)->addText('Notes');

        foreach ($data['scans'] as $scan) {
            $table->addRow();
            $table->addCell(1500)->addText($scan->created_at->format('Y-m-d H:i:s'));
            $table->addCell(2000)->addText($scan->item ? $scan->item->name : 'Unknown Item');
            $table->addCell(1500)->addText($scan->user ? $scan->user->name : 'Unknown User');
            $table->addCell(1500)->addText($this->formatScanAction($scan->action));
            $table->addCell(1500)->addText($scan->office ? $scan->office->name : 'N/A');
            $table->addCell(2000)->addText($scan->notes ?: 'No notes');
        }

        $filename = 'qr-scan-logs-' . $data['period'] . '-' . $data['selection'] . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $phpWord->save($tempFile, 'Word2007');

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Format scan action for display
     */
    private function formatScanAction($action)
    {
        $actionMap = [
            'inventory_check' => 'Inventory Check',
            'updated' => 'Updated',
            'item_claim' => 'Item Claim',
            'item_fulfill' => 'Item Fulfill',
            'stock_adjustment' => 'Stock Adjustment'
        ];

        return $actionMap[$action] ?? ucwords(str_replace('_', ' ', $action));
    }

    /**
     * Export report to Excel
     */
    public function exportReport(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get data for export
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $data = $this->getInventoryTableData($dateFrom, $dateTo);

        // Create CSV content (consumables only, no category column)
        $csv = "Item Name,Items Released/Claimed,Total Quantity (Remaining + Released),Remaining Stock\n";
        $csv .= "Calculation: Total Quantity = Remaining Stock + Released Quantity\n\n";

        foreach ($data as $item) {
            $csv .= '"' . $item['name'] . '",' . $item['released'] . ',' . $item['totalQuantity'] . ',' . $item['remaining'] . "\n";
        }

        $filename = 'inventory-report-' . $period . '-' . $selection . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Download QR scan logs DOCX
     */
    public function downloadQrScanLogs(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get data for the report
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $scans = \App\Models\ItemScanLog::with(['item', 'user', 'office'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'period' => $period,
            'selection' => $selection,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'scans' => $scans,
            'summary' => [
                'totalScans' => $scans->count(),
                'uniqueItems' => $scans->pluck('item_id')->unique()->count(),
                'activeUsers' => $scans->pluck('user_id')->unique()->filter()->count(),
            ],
        ];

        try {
            return $this->generateQrScanLogsDocx($data);
        } catch (\Exception $e) {
            // Fallback to CSV if DOCX generation fails
            Log::warning('QR Scan Logs DOCX generation failed, falling back to CSV: ' . $e->getMessage());
            
            // Create CSV content
            $csv = "Timestamp,Item Scanned,User,Action,Location,Notes\n";

            foreach ($scans as $scan) {
                $csv .= '"' . $scan->created_at->format('Y-m-d H:i:s') . '",';
                $csv .= '"' . ($scan->item ? $scan->item->name : 'Unknown Item') . '",';
                $csv .= '"' . ($scan->user ? $scan->user->name : 'Unknown User') . '",';
                $csv .= '"' . $this->formatScanAction($scan->action) . '",';
                $csv .= '"' . ($scan->office ? $scan->office->name : 'N/A') . '",';
                $csv .= '"' . ($scan->notes ?: 'No notes') . '"' . "\n";
            }

            $filename = 'qr-scan-logs-' . $period . '-' . $selection . '.csv';

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('X-Fallback-Message', 'DOCX export failed. CSV version downloaded instead.');
        }
    }

    /**
     * Export QR scan logs to Excel
     */
    public function exportQrScanLogs(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $selection = $request->get('selection');

        // Get data for export
        $dateRange = $this->getDateRangeFromPeriodAndSelection($period, $selection);
        $dateFrom = $dateRange['from'];
        $dateTo = $dateRange['to'];

        $scans = \App\Models\ItemScanLog::with(['item', 'user', 'office'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        // Create CSV content
        $csv = "Timestamp,Item Scanned,User,Scanner Type,Location,IP Address\n";

        foreach ($scans as $scan) {
            $csv .= '"' . $scan->created_at->format('Y-m-d H:i:s') . '",';
            $csv .= '"' . ($scan->item ? $scan->item->name : 'Unknown Item') . '",';
            $csv .= '"' . ($scan->user ? $scan->user->name : 'Unknown User') . '",';
            $csv .= '"webcam",'; // Default scanner type
            $csv .= '"' . ($scan->office ? $scan->office->name : 'N/A') . '",';
            $csv .= '"N/A"' . "\n"; // IP address not stored
        }

        $filename = 'qr-scan-logs-' . $period . '-' . $selection . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
