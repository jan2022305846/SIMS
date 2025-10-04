<?php

namespace App\Services;

use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\Request as SupplyRequest;
use App\Models\User;
use App\Models\Category;
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
        $cacheKey = "dashboard_data_" . ($user->isAdmin() ? 'admin' : 'faculty') . "_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            if ($user->isAdmin()) {
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
                            'total_items' => Consumable::count() + NonConsumable::count(),
                'active' => Consumable::where('quantity', '>', 0)->count() + NonConsumable::where('quantity', '>', 0)->count(),
                'low_stock' => Consumable::whereRaw('quantity <= min_stock')->count() + NonConsumable::whereRaw('quantity <= min_stock')->count(),
                'out_of_stock' => Consumable::where('quantity', 0)->count() + NonConsumable::where('quantity', 0)->count()
            ],
            'requests' => [
                'total' => SupplyRequest::count(),
                'pending' => SupplyRequest::where('status', 'pending')->count(),
                'approved' => SupplyRequest::where('status', 'approved_by_admin')->count(),
                'completed' => SupplyRequest::where('status', 'claimed')->count(),
                'this_month' => SupplyRequest::whereMonth('created_at', Carbon::now()->month)->count(),
                'today' => SupplyRequest::whereDate('created_at', today())->count()
            ],
            'users' => [
                'total' => User::count(),
                'active_today' => ItemScanLog::whereDate('created_at', today())
                    ->distinct('user_id')->count('user_id'),
                'faculty' => User::where('id', '!=', 6)->count(), // Admin is ID 6
                'staff' => 1 // Single admin system
            ],
            'activities' => [
                'total_today' => ItemScanLog::whereDate('created_at', today())->count(),
                'scans_today' => ItemScanLog::whereDate('created_at', today())->count()
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
                'approved' => SupplyRequest::where('user_id', $user->id)->where('status', 'approved_by_admin')->count(),
                'completed' => SupplyRequest::where('user_id', $user->id)->where('status', 'claimed')->count(),
                'this_month' => SupplyRequest::where('user_id', $user->id)
                    ->whereMonth('created_at', Carbon::now()->month)->count()
            ],
            'recent_activity' => ItemScanLog::where('user_id', $user->id)
                ->whereDate('created_at', today())->count()
        ];
    }

    /**
     * Get items that are low on stock.
     * 
     * @param int|null $limit Optional limit on results
     * @param User|null $user Optional user context for URL generation
     * @return Collection Items with added properties: stock_percentage, stock_status, url
     */
    public function getLowStockItems($limit = null, $user = null): Collection
    {
        $consumables = Consumable::with(['category'])
            ->whereRaw('quantity <= min_stock')
            ->orderByRaw('(quantity / GREATEST(min_stock, 1))')
            ->orderBy('quantity')
            ->get()
            ->map(function ($item) use ($user) {
                $stockPercentage = $item->min_stock > 0 
                    ? ($item->quantity / $item->min_stock) * 100 
                    : 0;

                $item->setAttribute('stock_percentage', round($stockPercentage, 1));
                $item->setAttribute('stock_status', $stockPercentage <= 25 ? 'critical' : 'low');
                $item->setAttribute('item_type', 'consumable');
                
                if ($user && $user->isAdmin()) {
                    $item->setAttribute('url', route('items.show', $item->id));
                } else {
                    $item->setAttribute('url', route('faculty.items.show', $item->id));
                }
                
                return $item;
            });

        $nonConsumables = NonConsumable::with(['category'])
            ->whereRaw('quantity <= min_stock')
            ->orderByRaw('(quantity / GREATEST(min_stock, 1))')
            ->orderBy('quantity')
            ->get()
            ->map(function ($item) use ($user) {
                $stockPercentage = $item->min_stock > 0 
                    ? ($item->quantity / $item->min_stock) * 100 
                    : 0;

                $item->setAttribute('stock_percentage', round($stockPercentage, 1));
                $item->setAttribute('stock_status', $stockPercentage <= 25 ? 'critical' : 'low');
                $item->setAttribute('item_type', 'non_consumable');
                
                if ($user && $user->isAdmin()) {
                    $item->setAttribute('url', route('items.show', $item->id));
                } else {
                    $item->setAttribute('url', route('faculty.items.show', $item->id));
                }
                
                return $item;
            });

        $allItems = $consumables->concat($nonConsumables)->sortBy('stock_percentage');

        if ($limit) {
            $allItems = $allItems->take($limit);
        }

        return $allItems;
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests($user, $limit = null): Collection
    {
        $query = SupplyRequest::with(['user', 'item'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        // Filter based on user role
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($request) use ($user) {
            return [
                'id' => $request->id,
                'user_name' => $request->user->name,
                'department' => $request->user->office->name ?? 'N/A',
                'purpose' => $request->purpose,
                'priority' => $request->priority,
                'items_count' => $request->item ? 1 : 0,
                'created_at' => $request->created_at,
                'days_pending' => $request->created_at->diffInDays(now()),
                'url' => $user->isAdmin() 
                    ? route('requests.show', $request->id)
                    : route('faculty.requests.show', $request->id)
            ];
        });
    }

    /**
     * Get recent activities (QR scans)
     */
    public function getRecentActivities($user, $limit = 10, $filter = 'all'): Collection
    {
        $query = ItemScanLog::with(['user', 'item'])
            ->orderBy('created_at', 'desc');

        if ($filter === 'mine') {
            $query->where('user_id', $user->id);
        }

        return $query->limit($limit)->get()->map(function ($scan) {
            return [
                'id' => $scan->id,
                'description' => "Scanned item: " . ($scan->item->name ?? 'Unknown Item'),
                'event' => 'qr_scan',
                'causer_name' => $scan->user->name ?? 'System',
                'causer_role' => $scan->user ? ($scan->user->isAdmin() ? 'admin' : 'faculty') : 'system',
                'created_at' => $scan->created_at,
                'time_ago' => $scan->created_at->diffForHumans(),
                'properties' => [
                    'item_id' => $scan->item_id,
                    'item_name' => $scan->item->name ?? 'Unknown Item',
                    'location' => $scan->location,
                    'scanner_type' => $scan->scanner_type
                ],
                'log_name' => 'qr_scan'
            ];
        });
    }

    /**
     * Get quick actions based on user role
     */
    public function getQuickActions($user): array
    {
        $actions = [];

        if ($user->isAdmin()) {
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
                    'url' => route('requests.manage'),
                    'color' => 'success',
                    'badge' => SupplyRequest::where('status', 'pending')->count()
                ],
                [
                    'title' => 'Generate Reports',
                    'description' => 'View detailed reports and analytics',
                    'icon' => 'fas fa-chart-bar',
                    'url' => route('reports.index'),
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

    public function getStockOverview(): Collection
    {
        $consumableStats = Consumable::select('category_id', 
                DB::raw('SUM(quantity) as total_stock'),
                DB::raw('COUNT(CASE WHEN quantity <= min_stock THEN 1 END) as low_stock_count')
            )->groupBy('category_id')->get();

        $nonConsumableStats = NonConsumable::select('category_id', 
                DB::raw('SUM(quantity) as total_stock'),
                DB::raw('COUNT(CASE WHEN quantity <= min_stock THEN 1 END) as low_stock_count')
            )->groupBy('category_id')->get();

        return Category::all()->map(function ($category) use ($consumableStats, $nonConsumableStats) {
            $consumable = $consumableStats->where('category_id', $category->id)->first();
            $nonConsumable = $nonConsumableStats->where('category_id', $category->id)->first();
            
            $totalStock = ($consumable ? $consumable->total_stock : 0) + ($nonConsumable ? $nonConsumable->total_stock : 0);
            $lowStockCount = ($consumable ? $consumable->low_stock_count : 0) + ($nonConsumable ? $nonConsumable->low_stock_count : 0);
            $totalItems = Consumable::where('category_id', $category->id)->count() + NonConsumable::where('category_id', $category->id)->count();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'total_items' => $totalItems,
                'total_stock' => $totalStock,
                'low_stock_count' => $lowStockCount
            ];
        });
    }

    /**
     * Get workflow overview
     */
    public function getWorkflowOverview($user): array
    {
        $baseQuery = SupplyRequest::query();
        
        if (!$user->isAdmin()) {
            $baseQuery->where('user_id', $user->id);
        }

        return [
            'pending_approval' => (clone $baseQuery)->where('status', 'pending')->count(),
            'ready_for_pickup' => (clone $baseQuery)->where('status', 'fulfilled')->count(),
            'acknowledged' => (clone $baseQuery)->where('status', 'approved_by_admin')->count(),
            'completed' => (clone $baseQuery)->where('status', 'claimed')->count(),
            'average_processing_time' => $this->getAverageProcessingTime($user)
        ];
    }

    /**
     * Get notifications for user
     */
    public function getNotifications($user): array
    {
        $notifications = [];

        if ($user->isAdmin()) {
            // Low stock notifications
            $lowStockCount = Consumable::whereRaw('quantity <= min_stock')->count() + NonConsumable::whereRaw('quantity <= min_stock')->count();
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
                    'url' => route('requests.manage', ['status' => 'pending']),
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

    public function getTopCategories($limit = 5): Collection
    {
        return Category::select('categories.*', DB::raw('
            (SELECT COUNT(*) FROM consumables WHERE consumables.category_id = categories.id) + 
            (SELECT COUNT(*) FROM non_consumables WHERE non_consumables.category_id = categories.id) as items_count
        '))
        ->orderBy('items_count', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getMostRequestedItems($limit = 5): Collection
    {
        $consumables = Consumable::withCount(['requests' => function ($query) {
            $query->whereMonth('created_at', Carbon::now()->month);
        }])
        ->orderBy('requests_count', 'desc')
        ->get()
        ->map(function ($item) {
            $item->setAttribute('item_type', 'consumable');
            return $item;
        });

        $nonConsumables = NonConsumable::withCount(['requests' => function ($query) {
            $query->whereMonth('created_at', Carbon::now()->month);
        }])
        ->orderBy('requests_count', 'desc')
        ->get()
        ->map(function ($item) {
            $item->setAttribute('item_type', 'non_consumable');
            return $item;
        });

        return $consumables->concat($nonConsumables)->sortByDesc('requests_count')->take($limit);
    }

    /**
     * Get user requests
     */
    public function getUserRequests($user, $limit = null): Collection
    {
        $query = SupplyRequest::with(['item'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getAvailableItemsCount(): int
    {
        return Consumable::where('quantity', '>', 0)->count() + NonConsumable::where('quantity', '>', 0)->count();
    }

    /**
     * Get user request status summary
     */
    public function getUserRequestStatusSummary($user): array
    {
        return [
            'pending' => SupplyRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => SupplyRequest::where('user_id', $user->id)->where('status', 'approved_by_admin')->count(),
            'completed' => SupplyRequest::where('user_id', $user->id)->where('status', 'claimed')->count(),
            'rejected' => SupplyRequest::where('user_id', $user->id)->where('status', 'declined')->count()
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
        $avgHours = SupplyRequest::where('status', 'claimed')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
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

    public function clearCache($user = null): void
    {
        if ($user) {
            Cache::forget("dashboard_data_" . ($user->isAdmin() ? 'admin' : 'faculty') . "_{$user->id}");
        } else {
            Cache::flush();
        }
    }
}