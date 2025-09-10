<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemScanLog;
use App\Models\Request;
use App\Models\User;
use App\Services\QRCodeService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get basic stats for the dashboard
        $totalItems = Item::count();
        $lowStockItems = Item::where('current_stock', '<=', 10)->count();
        $pendingRequests = Request::where('status', 'pending')->count();
        $totalUsers = User::count();
        $recentRequests = Request::with(['user', 'item'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent QR scans for the dashboard
        $recentScans = ItemScanLog::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get scan statistics
        $totalScans = ItemScanLog::count();
        $todayScans = ItemScanLog::whereDate('created_at', today())->count();

        return view('dashboard', compact(
            'totalItems', 
            'lowStockItems', 
            'pendingRequests', 
            'totalUsers', 
            'recentRequests',
            'recentScans',
            'totalScans',
            'todayScans'
        ));
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
            $item = Item::find($parsedData['id']);

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
        $totalItems = Item::count();
        $totalUsers = User::count();
        $totalRequests = Request::count();
        $approvedRequests = Request::where('status', 'approved')->count();
        $rejectedRequests = Request::where('status', 'rejected')->count();
        $pendingRequests = Request::where('status', 'pending')->count();
        
        // Monthly requests data
        $monthlyRequests = Request::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month');

        // Low stock items
        $lowStockItems = Item::where('quantity', '<=', 10)->get();

        // Expiring items
        $expiringItems = Item::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->get();

        return view('admin.reports.index', compact(
            'totalItems', 'totalUsers', 'totalRequests', 'approvedRequests', 
            'rejectedRequests', 'pendingRequests', 'monthlyRequests',
            'lowStockItems', 'expiringItems'
        ));
    }
}
