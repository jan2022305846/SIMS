<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Request as RequestModel;
use App\Models\ActivityLog;
use App\Models\ItemScanLog;
use App\Models\Category;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Admin Dashboard - Main overview with statistics, QR scanning capability, and activity overview
     */
    public function dashboard()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'statistics' => $this->getBasicStatistics(),
                'qr_scanner' => $this->getDashboardQRScannerData(),
                'recent_activities' => $this->getRecentActivities(),
                'monthly_data' => $this->getMonthlyData(),
                'dashboard_widgets' => [
                    'qr_scanner_enabled' => true,
                    'show_scan_history' => true,
                    'show_scanner_stats' => true,
                    'show_alert_items' => true,
                ]
            ]
        ]);
    }

    /**
     * Get basic system statistics
     */
    private function getBasicStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'total_items' => Item::count(),
            'total_requests' => RequestModel::count(),
            'pending_requests' => RequestModel::where('status', 'pending')->count(),
            'low_stock_items' => Item::where('current_stock', '<=', 10)->count(),
            'expiring_items' => Item::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                ->count(),
        ];
    }

    /**
     * Get QR scanner related data for dashboard
     */
    private function getDashboardQRScannerData(): array
    {
        return [
            'recent_scans' => $this->getRecentScans(),
            'scanner_statistics' => $this->getQRScannerStats(),
            'scanner_ready' => true,
            'items_needing_attention' => $this->getItemsNeedingAttention(),
        ];
    }

    /**
     * Get recent QR scans
     */
    private function getRecentScans()
    {
        return ItemScanLog::with(['user', 'item.category'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'item_name' => $scan->item->name,
                    'item_id' => $scan->item->id,
                    'category' => $scan->item->category->name,
                    'scanned_by' => $scan->user->name ?? 'System',
                    'scanned_at' => $scan->scanned_at->format('M d, Y h:i A'),
                    'location' => $scan->location,
                    'scanner_type' => $scan->scanner_type,
                ];
            });
    }

    /**
     * Get QR scanner statistics
     */
    private function getQRScannerStats(): array
    {
        return [
            'total_scans_today' => ItemScanLog::whereDate('scanned_at', today())->count(),
            'total_scans_week' => ItemScanLog::whereBetween('scanned_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'unique_items_scanned_today' => ItemScanLog::whereDate('scanned_at', today())
                ->distinct('item_id')->count(),
            'most_active_scanner' => ItemScanLog::with('user')
                ->select('user_id', DB::raw('count(*) as scan_count'))
                ->whereDate('scanned_at', today())
                ->groupBy('user_id')
                ->orderBy('scan_count', 'desc')
                ->first(),
        ];
    }

    /**
     * Get items needing attention for dashboard
     */
    private function getItemsNeedingAttention(): array
    {
        return [
            'low_stock' => $this->getDashboardLowStockItems(),
            'expiring_soon' => $this->getDashboardExpiringSoonItems(),
        ];
    }

    /**
     * Get low stock items for dashboard
     */
    private function getDashboardLowStockItems()
    {
        return Item::with(['category'])
            ->where('current_stock', '<=', 10)
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'current_stock' => $item->current_stock,
                    'alert_type' => 'low_stock',
                ];
            });
    }

    /**
     * Get items expiring soon for dashboard
     */
    private function getDashboardExpiringSoonItems()
    {
        return Item::with(['category'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>', Carbon::now())
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name,
                    'expiry_date' => $item->expiry_date->format('Y-m-d'),
                    'days_until_expiry' => Carbon::now()->diffInDays($item->expiry_date, false),
                    'alert_type' => 'expiring_soon',
                ];
            });
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        return ActivityLog::with('causer')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get monthly statistics data
     */
    private function getMonthlyData(): array
    {
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'requests' => RequestModel::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'items_added' => Item::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'scans_performed' => ItemScanLog::whereMonth('scanned_at', $date->month)
                    ->whereYear('scanned_at', $date->year)
                    ->count(),
            ];
        }
        return $monthlyData;
    }

    /**
     * Get system statistics for dashboard widgets
     */
    public function getStatistics()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'office_heads' => User::where('role', 'office_head')->count(),
                'faculty' => User::where('role', 'faculty')->count(),
                'active_today' => ActivityLog::whereDate('created_at', today())
                    ->distinct('user_id')
                    ->count('user_id'),
            ],
            'inventory' => [
                'total_items' => Item::count(),
                'consumable_items' => Item::whereHas('category', function($q) {
                    $q->where('type', 'consumable');
                })->count(),
                'non_consumable_items' => Item::whereHas('category', function($q) {
                    $q->where('type', 'non-consumable');
                })->count(),
                'low_stock' => Item::where('current_stock', '<=', 10)->count(),
                'expiring_soon' => Item::whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                    ->count(),
            ],
            'requests' => [
                'total' => RequestModel::count(),
                'pending' => RequestModel::where('status', 'pending')->count(),
                'approved' => RequestModel::where('status', 'approved')->count(),
                'declined' => RequestModel::where('status', 'declined')->count(),
                'today' => RequestModel::whereDate('created_at', today())->count(),
            ],
            'activity' => [
                'total_scans_today' => ItemScanLog::whereDate('created_at', today())->count(),
                'total_activities_today' => ActivityLog::whereDate('created_at', today())->count(),
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Get low stock items that need attention
     */
    public function getLowStockItems()
    {
        $lowStockItems = Item::with(['category', 'currentHolder'])
            ->where('current_stock', '<=', 10)
            ->orderBy('current_stock', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lowStockItems
        ]);
    }

    /**
     * Get items expiring soon
     */
    public function getExpiringItems()
    {
        $expiringItems = Item::with(['category', 'currentHolder'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $expiringItems
        ]);
    }

    /**
     * Quick QR Code scanning for embedded dashboard scanner
     */
    public function quickScan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Find item by QR code or barcode
            $item = Item::where('qr_code', $request->qr_data)
                       ->orWhere('barcode', $request->qr_data)
                       ->first();

            if (!$item) {
                // Try to parse JSON QR data
                $qrData = json_decode($request->qr_data, true);
                if ($qrData && isset($qrData['id'])) {
                    $item = Item::find($qrData['id']);
                }
            }

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found'
                ], 404);
            }

            // Log the scan
            ItemScanLog::logScan($item, [
                'scanner_type' => 'dashboard_quick_scan',
                'location' => 'Admin Dashboard',
                'scan_data' => [
                    'raw_qr_data' => $request->qr_data,
                    'scan_method' => 'dashboard_widget',
                ],
            ]);

            // Get basic item info with alerts for quick display
            $alerts = [];
            if ($item->current_stock <= 10) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Low Stock: ' . $item->current_stock . ' remaining'
                ];
            }
            
            if ($item->expiry_date && $item->expiry_date <= Carbon::now()->addDays(30)) {
                $daysUntilExpiry = Carbon::now()->diffInDays($item->expiry_date, false);
                if ($daysUntilExpiry < 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'message' => 'Expired ' . abs($daysUntilExpiry) . ' days ago'
                    ];
                } else {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => 'Expires in ' . $daysUntilExpiry . ' days'
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'category' => $item->category->name,
                        'current_stock' => $item->current_stock,
                        'location' => $item->location,
                        'current_holder' => $item->currentHolder->name ?? 'Available',
                        'barcode' => $item->barcode,
                    ],
                    'alerts' => $alerts,
                    'scan_timestamp' => now()->format('H:i:s'),
                    'quick_scan' => true,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Scan failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * QR Code scanning for admin dashboard
     */
    public function scanQRCode(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'location' => 'nullable|string|max:255',
            'scan_method' => 'nullable|in:camera,manual,barcode_scanner',
        ]);

        try {
            // Find item by QR code or barcode
            $item = Item::where('qr_code', $request->qr_data)
                       ->orWhere('barcode', $request->qr_data)
                       ->first();

            if (!$item) {
                // Try to parse JSON QR data
                $qrData = json_decode($request->qr_data, true);
                if ($qrData && isset($qrData['id'])) {
                    $item = Item::find($qrData['id']);
                }
            }

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found. Please check the QR code or barcode.'
                ], 404);
            }

            // Log the scan with detailed information
            ItemScanLog::logScan($item, [
                'scanner_type' => 'admin_dashboard',
                'location' => $request->location ?? 'Admin Dashboard',
                'scan_data' => [
                    'raw_qr_data' => $request->qr_data,
                    'scan_method' => $request->scan_method ?? 'camera',
                    'admin_scan' => true,
                ],
            ]);

            // Load item with relationships
            $itemData = $item->load(['category', 'currentHolder']);
            
            // Get item scan history (last 10 scans)
            $scanHistory = ItemScanLog::where('item_id', $item->id)
                ->with('user')
                ->orderBy('scanned_at', 'desc')
                ->limit(10)
                ->get();

            // Check for alerts
            $alerts = [];
            if ($item->current_stock <= 10) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'Low Stock Alert: Only ' . $item->current_stock . ' units remaining'
                ];
            }
            
            if ($item->expiry_date && $item->expiry_date <= Carbon::now()->addDays(30)) {
                $daysUntilExpiry = Carbon::now()->diffInDays($item->expiry_date, false);
                if ($daysUntilExpiry < 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'message' => 'Item Expired: ' . abs($daysUntilExpiry) . ' days ago'
                    ];
                } else {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => 'Expiring Soon: ' . $daysUntilExpiry . ' days remaining'
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'item' => $itemData,
                    'scan_logged' => true,
                    'scan_history' => $scanHistory,
                    'alerts' => $alerts,
                    'scan_timestamp' => now()->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process QR scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get QR scanner interface data for admin dashboard
     */
    public function getQRScannerData()
    {
        // Get recent scans for quick reference
        $recentScans = ItemScanLog::with(['item.category', 'user'])
            ->orderBy('scanned_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'item_name' => $scan->item->name,
                    'category' => $scan->item->category->name,
                    'scanned_by' => $scan->user->name ?? 'System',
                    'scanned_at' => $scan->scanned_at->format('M d, Y h:i A'),
                    'location' => $scan->location,
                ];
            });

        // Get scanning statistics
        $scanStats = [
            'total_scans_today' => ItemScanLog::whereDate('scanned_at', today())->count(),
            'total_scans_week' => ItemScanLog::whereBetween('scanned_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'most_scanned_items' => ItemScanLog::with('item')
                ->select('item_id', DB::raw('count(*) as scan_count'))
                ->groupBy('item_id')
                ->orderBy('scan_count', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($scan) {
                    /** @var \stdClass $scan */
                    return [
                        'item_name' => $scan->item->name,
                        'scan_count' => $scan->scan_count,
                    ];
                }),
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'recent_scans' => $recentScans,
                'scan_statistics' => $scanStats,
                'scanner_ready' => true,
            ]
        ]);
    }

    /**
     * Generate QR code for an item via admin dashboard
     */
    public function generateQRCode(Request $request, Item $item)
    {
        try {
            // Generate QR code data
            $qrData = [
                'type' => 'item',
                'id' => $item->id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'category' => $item->category->name,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

            // Update item's QR code field
            $item->update([
                'qr_code' => json_encode($qrData),
                'qr_code_data' => json_encode($qrData),
            ]);

            // Get QR code as base64 image
            $qrCodeDataUrl = $item->getQRCodeDataUrl();

            // Log the QR code generation
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'qr_code.generated',
                'description' => "QR code generated for item '{$item->name}' (ID: {$item->id})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'qr_code_url' => $qrCodeDataUrl,
                    'qr_data' => $qrData,
                    'item' => $item->load('category'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk generate QR codes for multiple items
     */
    public function bulkGenerateQRCodes(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:items,id',
        ]);

        $items = Item::whereIn('id', $request->item_ids)->with('category')->get();
        $successCount = 0;
        $results = [];

        foreach ($items as $item) {
            try {
                $qrData = [
                    'type' => 'item',
                    'id' => $item->id,
                    'name' => $item->name,
                    'barcode' => $item->barcode,
                    'category' => $item->category->name,
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ];

                $item->update([
                    'qr_code' => json_encode($qrData),
                    'qr_code_data' => json_encode($qrData),
                ]);

                $results[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'status' => 'success',
                    'qr_data' => $qrData,
                ];

                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Log bulk QR code generation
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'qr_code.bulk_generated',
            'description' => "Bulk QR code generation completed. {$successCount} QR codes generated successfully.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Bulk QR code generation completed. {$successCount} out of " . count($items) . " QR codes generated successfully.",
            'data' => [
                'success_count' => $successCount,
                'total_count' => count($items),
                'results' => $results,
            ]
        ]);
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'security.password_changed',
            'description' => 'Admin password changed successfully',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Admin logout with activity logging
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log logout activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'auth.logout',
            'description' => 'Admin logged out',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Revoke current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}
