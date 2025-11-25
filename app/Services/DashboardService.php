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
        ];
    }

    /**
     * Get faculty dashboard data
     */
    private function getFacultyDashboardData($user): array
    {
        return [
            'my_statistics' => $this->getFacultyStatistics($user),
            'my_requests' => $this->getMyRecentRequests($user, 5),
            'available_items' => $this->getAvailableItemsCount(),
            'quick_actions' => $this->getQuickActions($user),
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

    public function clearCache($user = null): void
    {
        if ($user) {
            Cache::forget("dashboard_data_" . ($user->isAdmin() ? 'admin' : 'faculty') . "_{$user->id}");
        } else {
            Cache::flush();
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(): Collection
    {
        $lowStockConsumables = Consumable::whereRaw('quantity <= min_stock')
            ->select('id', 'name', 'quantity', 'min_stock', 'product_code')
            ->get()
            ->map(function ($item) {
                $item->stock_status = $item->quantity === 0 ? 'critical' : 'low';
                $item->item_type = 'consumable';
                return $item;
            });

        $lowStockNonConsumables = NonConsumable::whereRaw('quantity <= min_stock')
            ->select('id', 'name', 'quantity', 'min_stock', 'product_code')
            ->get()
            ->map(function ($item) {
                $item->stock_status = $item->quantity === 0 ? 'critical' : 'low';
                $item->item_type = 'non_consumable';
                return $item;
            });

        return $lowStockConsumables->merge($lowStockNonConsumables);
    }

    /**
     * Get pending requests
     */
    public function getPendingRequests($user): Collection
    {
        $query = SupplyRequest::with(['user', 'requestItems'])
            ->where('status', 'pending');

        // If not admin, only show user's own requests
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $requests = $query->latest()->get();

        // Manually load itemable relationships for each request item
        foreach ($requests as $request) {
            foreach ($request->requestItems as $requestItem) {
                if (!$requestItem->relationLoaded('itemable')) {
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

        return $requests;
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($user, $limit = 10, $filter = 'all'): Collection
    {
        $query = ItemScanLog::with(['user', 'item'])
            ->latest();

        // Filter based on user role and filter type
        if (!$user->isAdmin()) {
            if ($filter === 'mine') {
                $query->where('user_id', $user->id);
            }
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get quick actions based on user role
     */
    public function getQuickActions($user): array
    {
        if ($user->isAdmin()) {
            return [
                [
                    'title' => 'Add New Item',
                    'description' => 'Add consumable or non-consumable items',
                    'icon' => 'fas fa-plus',
                    'url' => route('items.create'),
                    'color' => 'primary'
                ],
                [
                    'title' => 'Manage Requests',
                    'description' => 'Review and approve pending requests',
                    'icon' => 'fas fa-clipboard-list',
                    'url' => route('requests.manage'),
                    'color' => 'warning'
                ],
                [
                    'title' => 'Generate Reports',
                    'description' => 'View inventory and usage reports',
                    'icon' => 'fas fa-chart-bar',
                    'url' => route('reports.index'),
                    'color' => 'info'
                ],
                [
                    'title' => 'Scan QR Code',
                    'description' => 'Verify item details with QR scanner',
                    'icon' => 'fas fa-qrcode',
                    'url' => route('qr.scanner'),
                    'color' => 'success'
                ]
            ];
        } else {
            return [
                [
                    'title' => 'Request Item',
                    'description' => 'Submit a new item request',
                    'icon' => 'fas fa-plus',
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
                    'description' => 'Explore available items',
                    'icon' => 'fas fa-search',
                    'url' => route('faculty.items.index'),
                    'color' => 'success'
                ],
                [
                    'title' => 'Scan QR Code',
                    'description' => 'Check item details',
                    'icon' => 'fas fa-qrcode',
                    'url' => route('qr.scanner'),
                    'color' => 'warning'
                ]
            ];
        }
    }

    /**
     * Get system health status (admin only)
     */
    public function getSystemHealth(): array
    {
        $totalItems = Consumable::count() + NonConsumable::count();
        $lowStockItems = $this->getLowStockItems()->count();
        $pendingRequests = SupplyRequest::where('status', 'pending')->count();
        $totalUsers = User::count();

        // Calculate health score (0-100)
        $healthScore = 100;
        if ($lowStockItems > 0) $healthScore -= min(20, $lowStockItems * 2);
        if ($pendingRequests > 10) $healthScore -= min(20, ($pendingRequests - 10));
        if ($totalItems < 10) $healthScore -= 10;

        return [
            'score' => max(0, $healthScore),
            'status' => $healthScore >= 80 ? 'healthy' : ($healthScore >= 60 ? 'warning' : 'critical'),
            'metrics' => [
                'total_items' => $totalItems,
                'low_stock_items' => $lowStockItems,
                'pending_requests' => $pendingRequests,
                'total_users' => $totalUsers,
                'database_size' => 'N/A', // Would need DB query
                'last_backup' => 'N/A' // Would need backup tracking
            ]
        ];
    }

    /**
     * Get stock overview by category
     */
    public function getStockOverview(): array
    {
        $categories = Category::with(['consumables', 'nonConsumables'])->get();

        $overview = $categories->map(function ($category) {
            $consumables = $category->consumables;
            $nonConsumables = $category->nonConsumables;

            $totalItems = $consumables->count() + $nonConsumables->count();
            $lowStockItems = $consumables->where('quantity', '<=', DB::raw('min_stock'))->count() +
                           $nonConsumables->where('quantity', '<=', DB::raw('min_stock'))->count();
            $outOfStockItems = $consumables->where('quantity', 0)->count() +
                             $nonConsumables->where('quantity', 0)->count();

            return [
                'category' => $category->name,
                'total_items' => $totalItems,
                'in_stock' => $totalItems - $outOfStockItems,
                'low_stock' => $lowStockItems,
                'out_of_stock' => $outOfStockItems,
                'stock_percentage' => $totalItems > 0 ? round((($totalItems - $outOfStockItems) / $totalItems) * 100, 1) : 0
            ];
        });

        return [
            'categories' => $overview,
            'summary' => [
                'total_categories' => $categories->count(),
                'total_items' => $overview->sum('total_items'),
                'healthy_stock' => $overview->sum('in_stock'),
                'needs_attention' => $overview->sum('low_stock') + $overview->sum('out_of_stock')
            ]
        ];
    }

    /**
     * Get notifications for user
     */
    public function getNotifications($user): array
    {
        $unreadCount = $user->unreadNotificationsCount();
        $recentNotifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'url' => $notification->url
                ];
            });

        return [
            'unread_count' => $unreadCount,
            'notifications' => $recentNotifications,
            'has_more' => $user->notifications()->count() > 5
        ];
    }

    /**
     * Get faculty-specific statistics
     */
    private function getFacultyStatistics($user): array
    {
        $userRequests = SupplyRequest::where('user_id', $user->id);

        return [
            'my_requests' => [
                'total' => $userRequests->count(),
                'pending' => (clone $userRequests)->where('status', 'pending')->count(),
                'approved' => (clone $userRequests)->where('status', 'fulfilled')->count(), // Ready for pickup
                'completed' => (clone $userRequests)->where('status', 'claimed')->count(),
                'cancelled' => (clone $userRequests)->where('status', 'cancelled')->count(),
                'declined' => (clone $userRequests)->where('status', 'declined')->count(),
            ]
        ];
    }

    /**
     * Get user's recent requests
     */
    private function getMyRecentRequests($user, $limit = 5): Collection
    {
        $requests = SupplyRequest::with(['requestItems'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();

        // Manually load itemable relationships for each request item
        foreach ($requests as $request) {
            foreach ($request->requestItems as $requestItem) {
                if (!$requestItem->relationLoaded('itemable')) {
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

        return $requests;
    }

    /**
     * Get count of available items
     */
    private function getAvailableItemsCount(): int
    {
        return Consumable::where('quantity', '>', 0)->count() +
               NonConsumable::where('quantity', '>', 0)->count();
    }
}