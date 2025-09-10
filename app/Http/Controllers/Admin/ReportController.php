<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Request as RequestModel;
use App\Models\ActivityLog;
use App\Models\ItemScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Generate inventory report
     */
    public function inventoryReport(Request $request)
    {
        $request->validate([
            'format' => 'in:json,pdf',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:all,low_stock,expiring,expired',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Item::with(['category', 'currentHolder']);

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'low_stock':
                    $query->where('current_stock', '<=', 10);
                    break;
                case 'expiring':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<=', Carbon::now()->addDays(30));
                    break;
                case 'expired':
                    $query->whereNotNull('expiry_date')
                          ->where('expiry_date', '<', Carbon::now());
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $items = $query->orderBy('name')->get();

        // Calculate summary statistics
        $summary = [
            'total_items' => $items->count(),
            'total_value' => $items->sum(function($item) {
                return $item->current_stock * $item->unit_price;
            }),
            'low_stock_items' => $items->where('current_stock', '<=', 10)->count(),
            'items_with_holders' => $items->whereNotNull('current_holder_id')->count(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.inventory', compact('items', 'summary'));
            return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Generate user activity report
     */
    public function userActivityReport(Request $request)
    {
        $request->validate([
            'format' => 'in:json,pdf',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = ActivityLog::with(['user']);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $activities = $query->orderBy('created_at', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total_activities' => $activities->count(),
            'unique_users' => $activities->pluck('user_id')->unique()->count(),
            'date_range' => $dateFrom . ' to ' . $dateTo,
            'top_actions' => $activities->groupBy('action')->map(function($group) {
                return $group->count();
            })->sortDesc()->take(5),
            'activities_by_day' => $activities->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            })->map(function($group) {
                return $group->count();
            }),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.user-activity', compact('activities', 'summary'));
            return $pdf->download('user-activity-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'activities' => $activities,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Generate request history report
     */
    public function requestHistoryReport(Request $request)
    {
        $request->validate([
            'format' => 'in:json,pdf',
            'status' => 'nullable|in:pending,approved,declined',
            'user_id' => 'nullable|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = RequestModel::with(['user', 'item.category', 'processedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $requests = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $summary = [
            'total_requests' => $requests->count(),
            'approved_requests' => $requests->where('status', 'approved')->count(),
            'declined_requests' => $requests->where('status', 'declined')->count(),
            'pending_requests' => $requests->where('status', 'pending')->count(),
            'unique_requesters' => $requests->pluck('user_id')->unique()->count(),
            'total_value' => $requests->where('status', 'approved')->sum(function($request) {
                return ($request->quantity_approved ?? $request->quantity) * $request->item->unit_price;
            }),
            'avg_processing_time' => $requests->whereNotNull('processed_at')->avg(function($request) {
                return $request->created_at->diffInHours($request->processed_at);
            }),
            'date_range' => $dateFrom . ' to ' . $dateTo,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.request-history', compact('requests', 'summary'));
            return $pdf->download('request-history-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'requests' => $requests,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Generate QR scan logs report
     */
    public function scanLogsReport(Request $request)
    {
        $request->validate([
            'format' => 'in:json,pdf',
            'item_id' => 'nullable|exists:items,id',
            'user_id' => 'nullable|exists:users,id',
            'scanner_type' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = ItemScanLog::with(['item.category', 'user']);

        // Apply filters
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('scanner_type')) {
            $query->where('scanner_type', $request->scanner_type);
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $query->whereBetween('scanned_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $scanLogs = $query->orderBy('scanned_at', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total_scans' => $scanLogs->count(),
            'unique_items' => $scanLogs->pluck('item_id')->unique()->count(),
            'unique_scanners' => $scanLogs->pluck('user_id')->unique()->count(),
            'scans_by_type' => $scanLogs->groupBy('scanner_type')->map(function($group) {
                return $group->count();
            }),
            'most_scanned_items' => $scanLogs->groupBy('item.name')->map(function($group) {
                return $group->count();
            })->sortDesc()->take(5),
            'scans_by_day' => $scanLogs->groupBy(function($item) {
                return $item->scanned_at->format('Y-m-d');
            })->map(function($group) {
                return $group->count();
            }),
            'date_range' => $dateFrom . ' to ' . $dateTo,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.scan-logs', compact('scanLogs', 'summary'));
            return $pdf->download('scan-logs-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'scan_logs' => $scanLogs,
                'summary' => $summary
            ]
        ]);
    }

    /**
     * Generate comprehensive system report
     */
    public function systemReport(Request $request)
    {
        $request->validate([
            'format' => 'in:json,pdf',
        ]);

        // System Overview Statistics
        $systemStats = [
            'total_users' => User::count(),
            'total_items' => Item::count(),
            'total_requests' => RequestModel::count(),
            'total_activities' => ActivityLog::count(),
            'total_scans' => ItemScanLog::count(),
        ];

        // Recent Activity (last 30 days)
        $recentActivity = [
            'new_users' => User::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'new_items' => Item::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'recent_requests' => RequestModel::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            'recent_scans' => ItemScanLog::where('scanned_at', '>=', Carbon::now()->subDays(30))->count(),
        ];

        // Current Status
        $currentStatus = [
            'pending_requests' => RequestModel::where('status', 'pending')->count(),
            'low_stock_items' => Item::where('current_stock', '<=', 10)->count(),
            'expiring_items' => Item::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))->count(),
            'active_users_today' => ActivityLog::whereDate('created_at', today())
                ->distinct('user_id')->count(),
        ];

        $summary = [
            'system_stats' => $systemStats,
            'recent_activity' => $recentActivity,
            'current_status' => $currentStatus,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => Auth::user()->name,
        ];

        if ($request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.system-overview', compact('summary'));
            return $pdf->download('system-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }
}
