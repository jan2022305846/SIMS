<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Category;
use App\Models\RequestAcknowledgment;
use App\Models\ItemScanLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get comprehensive dashboard data based on user role
     */
    public function getDashboardData($user): array
    {
        $cacheKey = "dashboard_data_{$user->role}_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            if (in_array($user->role, ['admin', 'office_head'])) {
                return $this->getAdminDashboardData($user);
            }
            
            return $this->getFacultyDashboardData($user);
        });
    }

    /**
     * Get admin/office_head dashboard data
     */
    private function getAdminDashboardData($user): array
    {
        return [
            'statistics' => $this->getStatistics($user),
            'low_stock_items' => $this->getLowStockItems(10, $user),
            'pending_requests' => $this->getPendingRequests($user, 10),
            'recent_activities' => $this->getRecentActivities($user, 10),
            'expiring_items' => $this->getExpiringItems(10, $user),
            'quick_actions' => $this->getQuickActions($user),
            'system_health' => $this->getSystemHealth(),
            'stock_overview' => $this->getStockOverview(),
            'workflow_overview' => $this->getWorkflowOverview($user),
            'notifications' => $this->getNotifications($user),
            'weekly_trends' => $this->getWeeklyRequestTrends(),
            'top_categories' => $this->getTopCategories(),
            'most_requested_items' => $this->getMostRequestedItems()
        ];
    }

    /**
     * Get faculty dashboard data
     */
    private function getFacultyDashboardData($user): array
    {
        return [
            'my_statistics' => $this->getUserStatistics($user),
            'my_requests' => $this->getUserRequests($user, 10),
            'available_items' => $this->getAvailableItemsCount(),
            'recent_activities' => $this->getRecentActivities($user, 10, 'mine'),
            'quick_actions' => $this->getQuickActions($user),
            'notifications' => $this->getNotifications($user),
            'request_status_summary' => $this->getUserRequestStatusSummary($user)
        ];
    }

    /**
     * Get comprehensive statistics
     */
    public function getStatistics($user): array
    {
        return [
            'items' => [
                'total' => Item::count(),
                'active' => Item::where('current_stock', '>', 0)->count(),
                'low_stock' => Item::whereRaw('current_stock <= minimum_stock')->count(),
                'out_of_stock' => Item::where('current_stock', 0)->count(),
                'total_value' => Item::sum(DB::raw('current_stock * unit_price'))
            ],
            'requests' => [
                'total' => SupplyRequest::count(),
                'pending' => SupplyRequest::where('status', 'pending')->count(),
                'approved' => SupplyRequest::where('status', 'approved')->count(),
                'completed' => SupplyRequest::where('status', 'completed')->count(),
                'this_month' => SupplyRequest::whereMonth('created_at', Carbon::now()->month)->count(),
                'today' => SupplyRequest::whereDate('created_at', today())->count()
            ],
            'users' => [
                'total' => User::count(),
                'active_today' => ActivityLog::whereDate('created_at', today())
                    ->distinct('causer_id')->count('causer_id'),
                'faculty' => User::where('role', 'faculty')->count(),
                'staff' => User::whereIn('role', ['admin', 'office_head'])->count()
            ],
            'activities' => [
                'total_today' => ActivityLog::whereDate('created_at', today())->count(),
                'scans_today' => ItemScanLog::whereDate('created_at', today())->count(),
                'acknowledgments_today' => RequestAcknowledgment::whereDate('created_at', today())->count()
            ]
        ];
    }

    /**
     * Get user-specific statistics
     */
    public function getUserStatistics($user): array
    {
        return [
            'my_requests' => [
                'total' => SupplyRequest::where('user_id', $user->id)->count(),
                'pending' => SupplyRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approved' => SupplyRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
                'completed' => SupplyRequest::where('user_id', $user->id)->where('status', 'completed')->count(),
                'this_month' => SupplyRequest::where('user_id', $user->id)
                    ->whereMonth('created_at', Carbon::now()->month)->count()
            ],
            'recent_activity' => ActivityLog::where('causer_id', $user->id)
                ->whereDate('created_at', today())->count()
        ];
    }

        /**
     * Get items that are low on stock.
     * 
     * @param int|null $limit Optional limit on results
     * @param User|null $user Optional user context for URL generation
     * @return Collection<Item> Items with added properties: stock_percentage, stock_status, url
     */
    public function getLowStockItems($limit = null, $user = null): Collection
    {
        $query = Item::with(['category'])
            ->whereRaw('current_stock <= minimum_stock')
            ->orderByRaw('(current_stock / GREATEST(minimum_stock, 1))')
            ->orderBy('current_stock');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($item) use ($user) {
            $stockPercentage = $item->minimum_stock > 0 
                ? ($item->current_stock / $item->minimum_stock) * 100 
                : 0;

            // Add calculated properties to the model instance using setAttribute
            $item->setAttribute('stock_percentage', round($stockPercentage, 1));
            $item->setAttribute('stock_status', $stockPercentage <= 25 ? 'critical' : 'low');
            
            // Generate appropriate URL based on user role
            if ($user && $user->role === 'faculty') {
                $item->setAttribute('url', route('faculty.items.show', $item->id));
            } else {
                $item->setAttribute('url', route('items.show', $item->id));
            }
            
            return $item;
        });
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests($user, $limit = null): Collection
    {
        $query = SupplyRequest::with(['user', 'items'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        // Filter based on user role
        if ($user->role === 'faculty') {
            $query->where('user_id', $user->id);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($request) use ($user) {
            return [
                'id' => $request->id,
                'user_name' => $request->user->name,
                'department' => $request->user->department ?? 'N/A',
                'purpose' => $request->purpose,
                'priority' => $request->priority,
                'items_count' => $request->items->count(),
                'created_at' => $request->created_at,
                'days_pending' => $request->created_at->diffInDays(now()),
                'url' => $user->role === 'faculty' 
                    ? route('faculty.requests.show', $request->id)
                    : route('requests.details', $request->id)
            ];
        });
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($user, $limit = 10, $filter = 'all'): Collection
    {
        $query = ActivityLog::with(['causer'])
            ->orderBy('created_at', 'desc');

        if ($filter === 'mine') {
            $query->where('causer_id', $user->id);
        } elseif ($filter === 'important') {
            $query->whereIn('event', ['created', 'deleted', 'acknowledged', 'approved', 'rejected']);
        }

        return $query->limit($limit)->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'description' => $activity->description,
                'event' => $activity->event,
                'causer_name' => $activity->causer->name ?? 'System',
                'causer_role' => $activity->causer->role ?? 'system',
                'created_at' => $activity->created_at,
                'time_ago' => $activity->created_at->diffForHumans(),
                'properties' => $activity->properties,
                'log_name' => $activity->log_name
            ];
        });
    }

    /**
     * Get expiring items
     * 
     * @param int|null $limit Optional limit on results
     * @param User|null $user Optional user context for URL generation
     * @return Collection<Item> Items with added properties: days_until_expiry, status, url
     */
    public function getExpiringItems($limit = null, $user = null): Collection
    {
        $query = Item::with(['category'])
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(90)])
            ->orderBy('expiry_date');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($item) use ($user) {
            $daysUntilExpiry = Carbon::parse($item->expiry_date)->diffInDays(now(), false);
            $status = $daysUntilExpiry <= 0 ? 'expired' : 
                     ($daysUntilExpiry <= 30 ? 'expiring_soon' : 'expiring_later');

            // Add calculated properties to the model instance using setAttribute
            $item->setAttribute('days_until_expiry', abs($daysUntilExpiry));
            $item->setAttribute('status', $status);
            
            // Generate appropriate URL based on user role
            if ($user && $user->role === 'faculty') {
                $item->setAttribute('url', route('faculty.items.show', $item->id));
            } else {
                $item->setAttribute('url', route('items.show', $item->id));
            }
            
            return $item;
        });
    }

    /**
     * Get quick actions based on user role
     */
    public function getQuickActions($user): array
    {
        $actions = [];

        if (in_array($user->role, ['admin', 'office_head'])) {
            $actions = [
                [
                    'title' => 'Add New Item',
                    'description' => 'Add a new item to inventory',
                    'icon' => 'fas fa-plus-circle',
                    'url' => route('items.create'),
                    'color' => 'primary'
                ],
                [
                    'title' => 'Process Requests',
                    'description' => 'Review and approve pending requests',
                    'icon' => 'fas fa-clipboard-check',
                    'url' => route('requests.index'),
                    'color' => 'success',
                    'badge' => SupplyRequest::where('status', 'pending')->count()
                ],
                [
                    'title' => 'Generate Reports',
                    'description' => 'View detailed reports and analytics',
                    'icon' => 'fas fa-chart-bar',
                    'url' => route('reports'),
                    'color' => 'info'
                ],
                [
                    'title' => 'Scan QR Code',
                    'description' => 'Scan item QR code for quick access',
                    'icon' => 'fas fa-qrcode',
                    'url' => '#',
                    'color' => 'warning',
                    'action' => 'scan-qr'
                ]
            ];
        } else {
            $actions = [
                [
                    'title' => 'New Request',
                    'description' => 'Submit a new supply request',
                    'icon' => 'fas fa-plus-circle',
                    'url' => route('faculty.requests.create'),
                    'color' => 'primary'
                ],
                [
                    'title' => 'My Requests',
                    'description' => 'View your request history',
                    'icon' => 'fas fa-list',
                    'url' => route('faculty.requests.index'),
                    'color' => 'info'
                ],
                [
                    'title' => 'Browse Items',
                    'description' => 'Browse available inventory',
                    'icon' => 'fas fa-boxes',
                    'url' => route('faculty.items.index'),
                    'color' => 'success'
                ]
            ];
        }

        return $actions;
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        return [
            'database_status' => $this->checkDatabaseHealth(),
            'storage_status' => $this->checkStorageHealth(),
            'recent_errors' => $this->getRecentErrors(),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Get stock overview by category
     */
    public function getStockOverview(): Collection
    {
        return Category::withCount('items')
            ->with(['items' => function ($query) {
                $query->select('category_id', 
                    DB::raw('SUM(current_stock) as total_stock'),
                    DB::raw('SUM(current_stock * unit_price) as total_value'),
                    DB::raw('COUNT(CASE WHEN current_stock <= minimum_stock THEN 1 END) as low_stock_count')
                )->groupBy('category_id');
            }])
            ->get()
            ->map(function ($category) {
                $item = $category->items->first();
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'total_items' => $category->getAttribute('items_count'),
                    'total_stock' => $item ? $item->getAttribute('total_stock') : 0,
                    'total_value' => $item ? $item->getAttribute('total_value') : 0,
                    'low_stock_count' => $item ? $item->getAttribute('low_stock_count') : 0
                ];
            });
    }

    /**
     * Get workflow overview
     */
    public function getWorkflowOverview($user): array
    {
        $baseQuery = SupplyRequest::query();
        
        if ($user->role === 'faculty') {
            $baseQuery->where('user_id', $user->id);
        }

        return [
            'pending_approval' => (clone $baseQuery)->where('workflow_status', 'pending')->count(),
            'ready_for_pickup' => (clone $baseQuery)->where('workflow_status', 'ready_for_pickup')->count(),
            'acknowledged' => (clone $baseQuery)->where('workflow_status', 'acknowledged')->count(),
            'completed' => (clone $baseQuery)->where('workflow_status', 'completed')->count(),
            'average_processing_time' => $this->getAverageProcessingTime($user)
        ];
    }

    /**
     * Get notifications for user
     */
    public function getNotifications($user): array
    {
        $notifications = [];

        if (in_array($user->role, ['admin', 'office_head'])) {
            // Low stock notifications
            $lowStockCount = Item::whereRaw('current_stock <= minimum_stock')->count();
            if ($lowStockCount > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Low Stock Alert',
                    'message' => "{$lowStockCount} items are running low on stock",
                    'url' => route('items.index', ['filter' => 'low_stock']),
                    'created_at' => now()
                ];
            }

            // Pending requests notifications
            $pendingCount = SupplyRequest::where('status', 'pending')->count();
            if ($pendingCount > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Pending Requests',
                    'message' => "{$pendingCount} requests need your attention",
                    'url' => route('requests.index', ['status' => 'pending']),
                    'created_at' => now()
                ];
            }
        }

        return $notifications;
    }

    /**
     * Get weekly request trends
     */
    public function getWeeklyRequestTrends(): array
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trends[] = [
                'date' => $date->format('M j'),
                'count' => SupplyRequest::whereDate('created_at', $date)->count()
            ];
        }
        return $trends;
    }

    /**
     * Get top categories by item count
     */
    public function getTopCategories($limit = 5): Collection
    {
        return Category::withCount('items')
            ->orderBy('items_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get most requested items
     */
    public function getMostRequestedItems($limit = 5): Collection
    {
        return Item::withCount(['requests' => function ($query) {
            $query->whereMonth('created_at', Carbon::now()->month);
        }])
        ->orderBy('requests_count', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get user requests
     */
    public function getUserRequests($user, $limit = null): Collection
    {
        $query = SupplyRequest::with(['items'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get available items count
     */
    public function getAvailableItemsCount(): int
    {
        return Item::where('current_stock', '>', 0)->count();
    }

    /**
     * Get user request status summary
     */
    public function getUserRequestStatusSummary($user): array
    {
        return [
            'pending' => SupplyRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => SupplyRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'completed' => SupplyRequest::where('user_id', $user->id)->where('status', 'completed')->count(),
            'rejected' => SupplyRequest::where('user_id', $user->id)->where('status', 'rejected')->count()
        ];
    }

    /**
     * Helper methods for system health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkStorageHealth(): array
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = (($totalSpace - $diskSpace) / $totalSpace) * 100;
            
            return [
                'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
                'used_percentage' => round($usedPercentage, 2),
                'free_space' => $this->formatBytes($diskSpace)
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Unable to check storage'];
        }
    }

    private function getRecentErrors(): array
    {
        // This would typically check error logs
        return [];
    }

    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time' => '150ms',
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'cache_hit_rate' => '95%'
        ];
    }

    private function getAverageProcessingTime($user): string
    {
        $avgHours = SupplyRequest::where('status', 'completed')
            ->when($user->role === 'faculty', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours');

        return $avgHours ? round($avgHours, 1) . ' hours' : 'N/A';
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache($user = null): void
    {
        if ($user) {
            Cache::forget("dashboard_data_{$user->role}_{$user->id}");
        } else {
            Cache::flush();
        }
    }
}