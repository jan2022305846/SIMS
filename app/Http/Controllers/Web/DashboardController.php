<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\ItemScanLog;
use App\Models\Request;
use App\Models\User;
use App\Models\Category;
use App\Services\QRCodeService;
use App\Services\DashboardService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected QRCodeService $qrCodeService;
    protected DashboardService $dashboardService;

    public function __construct(QRCodeService $qrCodeService, DashboardService $dashboardService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the enhanced dashboard with comprehensive analytics.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $dashboardData = $this->dashboardService->getDashboardData($user);

        // Flatten the data structure for backward compatibility with the view
        $flattenedData = $this->flattenDashboardData($dashboardData, $user);

        if ($user->isAdmin()) {
            return view('admin.dashboard.dashboard', $flattenedData);
        } else {
            return view('faculty.dashboard.dashboard', $flattenedData);
        }
    }

    /**
     * Get low stock alerts via AJAX
     */
    public function lowStockAlerts()
    {
        $lowStockItems = $this->dashboardService->getLowStockItems();
        
        return response()->json([
            'items' => $lowStockItems,
            'count' => $lowStockItems->count(),
            'critical_count' => $lowStockItems->where('stock_status', 'critical')->count(),
            'low_count' => $lowStockItems->where('stock_status', 'low')->count()
        ]);
    }

    /**
     * Get pending requests overview via AJAX
     */
    public function pendingRequests()
    {
        $user = Auth::user();
        $pendingRequests = $this->dashboardService->getPendingRequests($user);
        
        return response()->json([
            'requests' => $pendingRequests,
            'total_count' => $pendingRequests->count(),
            'by_priority' => $pendingRequests->groupBy('priority')->map->count(),
            'by_status' => $pendingRequests->groupBy('status')->map->count()
        ]);
    }

    /**
     * Get recent activities via AJAX
     */
    public function recentActivities(HttpRequest $request)
    {
        $limit = $request->get('limit', 10);
        $filter = $request->get('filter', 'all'); // all, mine, important
        
        $activities = $this->dashboardService->getRecentActivities(Auth::user(), $limit, $filter);
        
        return response()->json([
            'activities' => $activities,
            'has_more' => $activities->count() === $limit
        ]);
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function statistics()
    {
        $user = Auth::user();
        $stats = $this->dashboardService->getStatistics($user);
        
        return response()->json($stats);
    }

    /**
     * Get quick actions based on user role
     */
    public function quickActions()
    {
        $user = Auth::user();
        $actions = $this->dashboardService->getQuickActions($user);
        
        return response()->json($actions);
    }

    /**
     * Get system health status
     */
    public function systemHealth()
    {
        // Only for admin
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }
        
        $health = $this->dashboardService->getSystemHealth();
        
        return response()->json($health);
    }

    /**
     * Get stock overview by category
     */
    public function stockOverview()
    {
        $overview = $this->dashboardService->getStockOverview();
        
        return response()->json($overview);
    }

    /**
     * Get notifications
     */
    public function notifications()
    {
        $notifications = $this->dashboardService->getNotifications(Auth::user());
        
        return response()->json($notifications);
    }

    /**
     * Handle QR code scanning from the dashboard.
     */
    public function scanQR(HttpRequest $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        try {
            // Parse the QR code data using the QRCodeService
            $parsedData = $this->qrCodeService->parseQRCode($request->qr_data);

            if (!$parsedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            // Find the item by ID from parsed data
            $item = Consumable::find($parsedData['id']) ?? NonConsumable::find($parsedData['id']);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found with this QR code'
                ], 404);
            }

            // Log the scan
            ItemScanLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'action' => 'scanned',
                'metadata' => [
                    'scanned_at' => now(),
                    'scanner' => 'dashboard',
                    'ip_address' => $request->ip(),
                    'qr_data' => $parsedData
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item scanned successfully',
                'data' => [
                    'item' => $item->load('category'),
                    'scan_time' => now()->format('Y-m-d H:i:s'),
                    'redirect_url' => route('items.show', $item->id)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scanning QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display reports page.
     */
    public function reports()
    {
        // Get data for reports
        $totalItems = Consumable::count() + NonConsumable::count();
        $totalUsers = User::count();
        $totalRequests = Request::count();
        $approvedRequests = Request::where('status', 'approved_by_admin')->count();
        $rejectedRequests = Request::where('status', 'declined')->count();
        $pendingRequests = Request::where('status', 'pending')->count();
        
        // Monthly requests data
        $monthlyRequests = Request::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month');

        // Low stock items
        $lowStockItems = Consumable::where('quantity', '<=', 10)->get()->merge(
            NonConsumable::where('quantity', '<=', 10)->get()
        );

        // Expiring items
        $expiringItems = Consumable::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->get()
            ->merge(
                NonConsumable::whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', now()->addDays(30))
                    ->get()
            );

        return view('admin.reports.index', compact(
            'totalItems', 'totalUsers', 'totalRequests', 'approvedRequests', 
            'rejectedRequests', 'pendingRequests', 'monthlyRequests',
            'lowStockItems', 'expiringItems'
        ));
    }

    /**
     * Flatten dashboard data for backward compatibility with the view
     */
    private function flattenDashboardData(array $dashboardData, $user): array
    {
        $flattened = [];
        
        // Handle admin/office_head data structure
        if ($user->isAdmin()) {
            // Extract statistics
            if (isset($dashboardData['statistics'])) {
                $stats = $dashboardData['statistics'];
                $flattened['totalItems'] = $stats['items']['total'] ?? 0;
                $flattened['totalUsers'] = $stats['users']['total'] ?? 0;
                $flattened['totalCategories'] = Category::count(); // Fallback
                $flattened['lowStockItems'] = $stats['items']['low_stock'] ?? 0;
                $flattened['pendingRequests'] = $stats['requests']['pending'] ?? 0;
            }

            // Map low stock items to expected variable name
            $flattened['lowStockAlerts'] = $dashboardData['low_stock_items'] ?? collect();
            
            // Map expiring items
            $flattened['expiringSoonItems'] = $dashboardData['expiring_items'] ?? collect();
            $flattened['expiringItems'] = $flattened['expiringSoonItems']->count();
            
            // Map other data
            $flattened['recentActivities'] = $dashboardData['recent_activities'] ?? collect();
            $flattened['pendingRequestsList'] = $dashboardData['pending_requests'] ?? collect();
            $flattened['quickActions'] = $dashboardData['quick_actions'] ?? [];
            $flattened['notifications'] = $dashboardData['notifications'] ?? [];
            $flattened['weeklyTrends'] = $dashboardData['weekly_trends'] ?? [];
            $flattened['topCategories'] = $dashboardData['top_categories'] ?? collect();
            $flattened['mostRequestedItems'] = $dashboardData['most_requested_items'] ?? collect();
        } else {
            // Handle faculty data structure
            if (isset($dashboardData['my_statistics'])) {
                $stats = $dashboardData['my_statistics'];
                $flattened['myRequests'] = $stats['my_requests']['total'] ?? 0;
                $flattened['myPendingRequests'] = $stats['my_requests']['pending'] ?? 0;
                $flattened['myApprovedRequests'] = $stats['my_requests']['approved'] ?? 0;
                $flattened['myRejectedRequests'] = 0; // Not provided by service yet
            }
            
            $flattened['myRequestsList'] = $dashboardData['my_requests'] ?? collect();
            $flattened['availableItems'] = $dashboardData['available_items'] ?? 0;
            $flattened['quickActions'] = $dashboardData['quick_actions'] ?? [];
            
            // Set default values for admin-only variables
            $flattened['totalItems'] = 0;
            $flattened['totalUsers'] = 0;
            $flattened['totalCategories'] = 0;
            $flattened['lowStockAlerts'] = collect();
            $flattened['expiringSoonItems'] = collect();
        }
        
        return $flattened;
    }
}
