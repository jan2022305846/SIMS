<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $item_id
 * @property string $action
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ItemScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'action',
        // Removed item_type and user_id as they're not used
    ];

    protected $casts = [
        // No specific casts needed for this model
    ];

    /**
     * Get the item that was scanned (only non-consumable items are scanned)
     */
    public function item()
    {
        return $this->belongsTo(\App\Models\NonConsumable::class, 'item_id');
    }

    /**
     * Create a scan log entry
     */
    public static function logScan($itemId, string $action = 'inventory_check', array $data = []): self
    {
        // Simplified logging - only track essential information
        return self::create([
            'item_id' => $itemId,
            'action' => $action,
            // Removed user_id, location_id and notes as they're not needed per system requirements
        ]);
    }

    /**
     * Get scan logs for a specific item
     */
    public static function forItem($itemId)
    {
        return self::where('item_id', $itemId)
                   ->orderBy('created_at', 'desc');
    }

    /**
     * Get recent scan logs
     */
    public static function recent($limit = 20)
    {
        return self::orderBy('created_at', 'desc')
                   ->limit($limit);
    }

    /**
     * Get scans for a specific date range
     */
    public static function betweenDates($startDate, $endDate)
    {
        return self::whereBetween('created_at', [$startDate, $endDate])
                   ->orderBy('created_at', 'desc');
    }

    /**
     * Get formatted scan time
     */
    public function getFormattedScanTimeAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get scan statistics for a specific period
     */
    public static function getScanStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalScans = $query->count();
        $uniqueItemsScanned = $query->distinct('item_id')->count();
        $uniqueUsersScanning = 1; // Only admin can monitor

        // Get most scanned items (without item relationship for now)
        $mostScannedItems = $query->selectRaw('item_id, COUNT(*) as scan_count')
            ->groupBy('item_id')
            ->orderBy('scan_count', 'desc')
            ->take(10)
            ->get()
            ->map(function($scan) {
                return [
                    'item_id' => $scan->item_id,
                    'scan_count' => $scan->scan_count
                ];
            });

        // No location tracking
        $scansByLocation = collect();

        return [
            'total_scans' => $totalScans,
            'unique_items_scanned' => $uniqueItemsScanned,
            'unique_users_scanning' => $uniqueUsersScanning,
            'most_scanned_items' => $mostScannedItems,
            'scans_by_location' => $scansByLocation,
            'scans_by_scanner_type' => [], // Placeholder for now
        ];
    }

    /**
     * Get scan activity for dashboard
     */
    public static function getDashboardScanData($days = 7)
    {
        $startDate = now()->subDays($days);

        return self::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as scan_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('scan_count', 'date');
    }

    /**
     * Get items that haven't been scanned recently
     */
    public static function getUnscannedItems($daysThreshold = 30)
    {
        $thresholdDate = now()->subDays($daysThreshold);

        // Get unscanned consumables
        $unscannedConsumables = \App\Models\Consumable::whereDoesntHave('scanLogs', function($query) use ($thresholdDate) {
            $query->where('created_at', '>=', $thresholdDate);
        })->get();

        // Get unscanned non-consumables
        $unscannedNonConsumables = \App\Models\NonConsumable::whereDoesntHave('scanLogs', function($query) use ($thresholdDate) {
            $query->where('created_at', '>=', $thresholdDate);
        })->get();

        // Combine results
        return $unscannedConsumables->merge($unscannedNonConsumables);
    }

    /**
     * Get scan frequency analysis for a specific item
     */
    public static function getScanFrequencyAnalysis($itemId)
    {
        $scans = self::where('item_id', $itemId)
            ->orderBy('created_at')
            ->get();

        if ($scans->isEmpty()) {
            return [
                'total_scans' => 0,
                'first_scan' => null,
                'last_scan' => null,
                'average_days_between_scans' => null,
                'scan_frequency' => 'never_scanned'
            ];
        }

        $totalScans = $scans->count();
        $firstScan = $scans->first()->created_at;
        $lastScan = $scans->last()->created_at;

        // Calculate average days between scans
        $averageDaysBetweenScans = null;
        if ($totalScans > 1) {
            $totalDays = $firstScan->diffInDays($lastScan);
            $averageDaysBetweenScans = $totalDays / ($totalScans - 1);
        }

        // Determine scan frequency category
        $scanFrequency = 'unknown';
        if ($averageDaysBetweenScans === null) {
            $scanFrequency = 'single_scan';
        } elseif ($averageDaysBetweenScans <= 1) {
            $scanFrequency = 'daily';
        } elseif ($averageDaysBetweenScans <= 7) {
            $scanFrequency = 'weekly';
        } elseif ($averageDaysBetweenScans <= 30) {
            $scanFrequency = 'monthly';
        } elseif ($averageDaysBetweenScans <= 90) {
            $scanFrequency = 'quarterly';
        } else {
            $scanFrequency = 'rarely';
        }

        return [
            'total_scans' => $totalScans,
            'first_scan' => $firstScan,
            'last_scan' => $lastScan,
            'average_days_between_scans' => $averageDaysBetweenScans,
            'scan_frequency' => $scanFrequency
        ];
    }    /**
     * Get scan alerts for items that need attention
     */
    public static function getScanAlerts()
    {
        $alerts = [];

        // Items not scanned in 90 days
        $unscanned90Days = self::getUnscannedItems(90);
        if ($unscanned90Days->count() > 0) {
            $alerts['not_scanned_90_days'] = $unscanned90Days;
        }

        // Items with unusual scan patterns (scanned more than 10 times in a day)
        $unusualScans = self::selectRaw('item_id, DATE(created_at) as scan_date, COUNT(*) as daily_scans')
            ->groupBy('item_id', 'scan_date')
            ->having('daily_scans', '>', 10)
            ->get();

        if ($unusualScans->count() > 0) {
            $alerts['unusual_scan_activity'] = $unusualScans;
        }

        return $alerts;
    }
}
