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
     * Check if workflow_status column exists
     */
    private function hasWorkflowColumn()
    {
        return Schema::hasColumn('requests', 'workflow_status');
    }

    /**
     * Get appropriate status query based on available columns
     */
    private function getRequestsByStatus($query, $status)
    {
        if ($this->hasWorkflowColumn()) {
            switch ($status) {
                case 'pending':
                    return $query->where('workflow_status', 'pending');
                case 'fulfilled':
                    return $query->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); });
                case 'approved':
                    return $query->whereIn('workflow_status', ['approved_by_admin', 'fulfilled', 'claimed']);
                case 'declined':
                    return $query->whereIn('workflow_status', ['declined_by_office_head', 'declined_by_admin']);
                default:
                    return $query;
            }
        } else {
            // Fallback to old status column
            switch ($status) {
                case 'pending':
                    return $query->where('status', 'pending');
                case 'fulfilled':
                    return $query->where('status', 'fulfilled');
                case 'approved':
                    return $query->where('status', 'approved');
                case 'declined':
                    return $query->where('status', 'declined');
                default:
                    return $query;
            }
        }
    }

    public function index()
    {
        $stats = [
            'total_items' => Item::count(),
            'low_stock_items' => Item::whereRaw('current_stock <= minimum_stock')->count(),
            'total_requests_this_month' => SupplyRequest::whereMonth('created_at', Carbon::now()->month)->count(),
            'pending_requests' => $this->getRequestsByStatus(SupplyRequest::query(), 'pending')->count(),
            'total_value' => Item::sum('total_value'),
            'categories_count' => Category::count(),
        ];

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Reports Dashboard with Charts
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, monthly, annually
        $data = $this->getReportData($period);
        
        return view('admin.reports.dashboard', compact('data', 'period'));
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
            case 'monthly':
                return $this->getMonthlyReportData($now);
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
        
        // Get last 7 days for chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $checkDate = $date->copy()->subDays($i);
            $dayStart = $checkDate->copy()->startOfDay();
            $dayEnd = $checkDate->copy()->endOfDay();
            
            $chartData[] = [
                'date' => $checkDate->format('M j'),
                'requests' => SupplyRequest::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'disbursements' => $this->getRequestsByStatus(
                    SupplyRequest::whereBetween('created_at', [$dayStart, $dayEnd]), 
                    'fulfilled'
                )->count(),
                'value' => SupplyRequest::whereBetween('created_at', [$dayStart, $dayEnd])
                    ->with('item')->get()->sum(function($req) {
                        return $req->quantity * ($req->item->unit_price ?? 0);
                    })
            ];
        }
        
        // Today's data
        $todayRequests = SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'period' => 'Daily',
            'current_date' => $date->format('F j, Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $todayRequests->count(),
                'fulfilled_requests' => $this->getRequestsByStatus(
                    SupplyRequest::whereBetween('created_at', [$startDate, $endDate]), 
                    'fulfilled'
                )->count(),
                'pending_requests' => $this->getRequestsByStatus(
                    SupplyRequest::whereBetween('created_at', [$startDate, $endDate]), 
                    'pending'
                )->count(),
                'total_value' => $todayRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $todayRequests->load(['user', 'item', 'item.category'])
        ];
    }

    private function getWeeklyReportData($date)
    {
        $startDate = $date->copy()->startOfWeek();
        $endDate = $date->copy()->endOfWeek();
        
        // Get last 8 weeks for chart
        $chartData = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $date->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $date->copy()->subWeeks($i)->endOfWeek();
            
            $chartData[] = [
                'date' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'requests' => SupplyRequest::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'value' => SupplyRequest::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->with('item')->get()->sum(function($req) {
                        return $req->quantity * ($req->item->unit_price ?? 0);
                    })
            ];
        }
        
        $weekRequests = SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'period' => 'Weekly',
            'current_date' => $startDate->format('M j') . ' - ' . $endDate->format('M j, Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $weekRequests->count(),
                'fulfilled_requests' => $weekRequests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'pending_requests' => $weekRequests->where('workflow_status', 'pending')->count(),
                'total_value' => $weekRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $weekRequests->load(['user', 'item', 'item.category'])
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
        
        // Get last 5 years for chart
        $chartData = [];
        for ($i = 4; $i >= 0; $i--) {
            $yearStart = $date->copy()->subYears($i)->startOfYear();
            $yearEnd = $date->copy()->subYears($i)->endOfYear();
            
            $chartData[] = [
                'date' => $yearStart->format('Y'),
                'requests' => SupplyRequest::whereBetween('created_at', [$yearStart, $yearEnd])->count(),
                'disbursements' => SupplyRequest::whereBetween('created_at', [$yearStart, $yearEnd])
                    ->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'value' => SupplyRequest::whereBetween('created_at', [$yearStart, $yearEnd])
                    ->with('item')->get()->sum(function($req) {
                        return $req->quantity * ($req->item->unit_price ?? 0);
                    })
            ];
        }
        
        $yearRequests = SupplyRequest::whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'period' => 'Annual',
            'current_date' => $startDate->format('Y'),
            'chart_data' => $chartData,
            'summary' => [
                'total_requests' => $yearRequests->count(),
                'fulfilled_requests' => $yearRequests->where(function($q) { return $this->getRequestsByStatus($q, 'fulfilled'); })->count(),
                'pending_requests' => $yearRequests->where('workflow_status', 'pending')->count(),
                'total_value' => $yearRequests->sum(function($req) {
                    return $req->quantity * ($req->item->unit_price ?? 0);
                }),
            ],
            'records' => $yearRequests->load(['user', 'item', 'item.category'])
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

    public function customReport(Request $request)
    {
        if (!$request->has('report_type')) {
            return view('admin.reports.custom-report');
        }

        // This method allows users to create custom reports by combining different data sources
        $reportType = $request->report_type;
        $dateFrom = $request->date_from ?? Carbon::now()->subMonth()->toDateString();
        $dateTo = $request->date_to ?? Carbon::now()->toDateString();

        $data = [];

        if (in_array('items', $reportType)) {
            $data['items'] = Item::with('category')->get();
        }

        if (in_array('requests', $reportType)) {
            $data['requests'] = SupplyRequest::with(['user', 'item'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();
        }

        if (in_array('users', $reportType)) {
            $data['users'] = User::with(['requests' => function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])->get();
        }

        if (in_array('logs', $reportType)) {
            $data['logs'] = Log::with('user')
                ->whereBetween('timestamp', [$dateFrom, $dateTo])
                ->get();
        }

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf.custom-report', compact('data', 'reportType', 'dateFrom', 'dateTo'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download('custom-report-' . date('Y-m-d') . '.pdf');
        }

        return view('admin.reports.custom-report', compact('data', 'reportType', 'dateFrom', 'dateTo'));
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
        $period = $request->get('period', 'daily');
        $data = $this->getReportData($period);
        
        return response()->json($data);
    }
}
