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
            'statistics' => $this->getStatistics($user),
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
}