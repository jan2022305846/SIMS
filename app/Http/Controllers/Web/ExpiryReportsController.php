<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpiryReportsController extends Controller
{
    /**
     * Display expiry reports dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $categoryId = $request->get('category_id');
        $timeframe = $request->get('timeframe', '30'); // days
        $status = $request->get('status', 'all');
        
        // Base query for items with expiry dates
        $query = Item::with(['category'])
            ->whereNotNull('expiry_date');
        
        // Apply category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Apply status filter
        $now = Carbon::now();
        switch ($status) {
            case 'expired':
                $query->where('expiry_date', '<', $now);
                break;
            case 'expiring_soon':
                $query->whereBetween('expiry_date', [$now, $now->copy()->addDays($timeframe)]);
                break;
            case 'fresh':
                $query->where('expiry_date', '>', $now->copy()->addDays($timeframe));
                break;
            // 'all' - no additional filter
        }
        
        // Get items with expiry calculations
        $items = $query->get()->map(function ($item) use ($now) {
            $expiryDate = Carbon::parse($item->expiry_date);
            $daysUntilExpiry = $now->diffInDays($expiryDate, false);
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $item->category->name,
                'category_id' => $item->category_id,
                'current_stock' => $item->current_stock,
                'unit_price' => $item->unit_price,
                'total_value' => $item->current_stock * $item->unit_price,
                'expiry_date' => $expiryDate,
                'days_until_expiry' => $daysUntilExpiry,
                'is_expired' => $daysUntilExpiry < 0,
                'status' => $this->getExpiryStatus($daysUntilExpiry),
                'location' => $item->location,
                'brand' => $item->brand,
            ];
        });
        
        // Sort by expiry date (most urgent first)
        $items = $items->sortBy('days_until_expiry');
        
        // Get summary statistics
        $stats = $this->getExpiryStats();
        
        // Get categories for filter
        $categories = Category::orderBy('name')->get();
        
        return view('admin.reports.expiry', compact('items', 'stats', 'categories'));
    }
    
    /**
     * Get detailed expiry statistics
     */
    public function getExpiryStats()
    {
        $now = Carbon::now();
        
        // Get all items with expiry dates
        $allExpiryItems = Item::whereNotNull('expiry_date')->get();
        
        $stats = [
            'total_items_with_expiry' => $allExpiryItems->count(),
            'expired_items' => $allExpiryItems->where('expiry_date', '<', $now)->count(),
            'expiring_this_week' => $allExpiryItems->whereBetween('expiry_date', [$now, $now->copy()->addDays(7)])->count(),
            'expiring_this_month' => $allExpiryItems->whereBetween('expiry_date', [$now, $now->copy()->addDays(30)])->count(),
            'expiring_this_quarter' => $allExpiryItems->whereBetween('expiry_date', [$now, $now->copy()->addDays(90)])->count(),
            'fresh_items' => $allExpiryItems->where('expiry_date', '>', $now->copy()->addDays(90))->count(),
        ];
        
        // Calculate total value at risk (expired + expiring soon)
        $atRiskItems = $allExpiryItems->filter(function ($item) use ($now) {
            return $item->expiry_date <= $now->copy()->addDays(30);
        });
        
        $stats['value_at_risk'] = $atRiskItems->sum(function ($item) {
            return $item->current_stock * $item->unit_price;
        });
        
        // Get expiry trends by month (next 12 months)
        $monthlyExpiry = [];
        for ($i = 0; $i < 12; $i++) {
            $monthStart = $now->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $count = $allExpiryItems->whereBetween('expiry_date', [$monthStart, $monthEnd])->count();
            $monthlyExpiry[] = [
                'month' => $monthStart->format('M Y'),
                'count' => $count
            ];
        }
        
        $stats['monthly_expiry'] = $monthlyExpiry;
        
        return $stats;
    }
    
    /**
     * Get expiry status based on days until expiry
     */
    private function getExpiryStatus($daysUntilExpiry)
    {
        if ($daysUntilExpiry < 0) {
            return 'expired';
        } elseif ($daysUntilExpiry <= 7) {
            return 'critical';
        } elseif ($daysUntilExpiry <= 30) {
            return 'warning';
        } elseif ($daysUntilExpiry <= 90) {
            return 'caution';
        } else {
            return 'fresh';
        }
    }
    
    /**
     * Export expiry report to CSV
     */
    public function exportCsv(Request $request)
    {
        $items = $this->getFilteredExpiryItems($request);
        
        $filename = 'expiry_report_' . Carbon::now()->format('Y_m_d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Item Name',
                'Category',
                'Current Stock',
                'Unit Price',
                'Total Value',
                'Expiry Date',
                'Days Until Expiry',
                'Status',
                'Location',
                'Brand'
            ]);
            
            // CSV data
            foreach ($items as $item) {
                fputcsv($file, [
                    $item['name'],
                    $item['category'],
                    $item['current_stock'],
                    number_format($item['unit_price'], 2),
                    number_format($item['total_value'], 2),
                    $item['expiry_date']->format('Y-m-d'),
                    $item['days_until_expiry'],
                    ucfirst($item['status']),
                    $item['location'],
                    $item['brand']
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get filtered expiry items (helper for export)
     */
    private function getFilteredExpiryItems(Request $request)
    {
        // Reuse the same filtering logic from index method
        $categoryId = $request->get('category_id');
        $timeframe = $request->get('timeframe', '30');
        $status = $request->get('status', 'all');
        
        $query = Item::with(['category'])->whereNotNull('expiry_date');
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $now = Carbon::now();
        switch ($status) {
            case 'expired':
                $query->where('expiry_date', '<', $now);
                break;
            case 'expiring_soon':
                $query->whereBetween('expiry_date', [$now, $now->copy()->addDays($timeframe)]);
                break;
            case 'fresh':
                $query->where('expiry_date', '>', $now->copy()->addDays($timeframe));
                break;
        }
        
        return $query->get()->map(function ($item) use ($now) {
            $expiryDate = Carbon::parse($item->expiry_date);
            $daysUntilExpiry = $now->diffInDays($expiryDate, false);
            
            return [
                'name' => $item->name,
                'category' => $item->category->name,
                'current_stock' => $item->current_stock,
                'unit_price' => $item->unit_price,
                'total_value' => $item->current_stock * $item->unit_price,
                'expiry_date' => $expiryDate,
                'days_until_expiry' => $daysUntilExpiry,
                'status' => $this->getExpiryStatus($daysUntilExpiry),
                'location' => $item->location,
                'brand' => $item->brand,
            ];
        })->sortBy('days_until_expiry');
    }
}
