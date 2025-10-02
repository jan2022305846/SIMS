<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $item_id
 * @property int $user_id
 * @property string $action
 * @property int|null $location_id
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ItemScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'item_type',
        'user_id',
        'action',
        'location_id',
        'notes',
    ];

    protected $casts = [
        // No specific casts needed for this model
    ];

    /**
     * Get the item that was scanned (polymorphic relationship)
     */
    public function item()
    {
        if ($this->item_type === 'consumable') {
            return $this->belongsTo(\App\Models\Consumable::class, 'item_id');
        } elseif ($this->item_type === 'non_consumable') {
            return $this->belongsTo(\App\Models\NonConsumable::class, 'item_id');
        }
        return null;
    }

    /**
     * Get the user who performed the scan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the office where the scan occurred
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'location_id');
    }

    /**
     * Create a scan log entry
     */
    public static function logScan($itemId, string $itemType, string $action = 'inventory_check', array $data = []): self
    {
        $locationId = null;
        if (isset($data['location_id'])) {
            $locationId = $data['location_id'];
        } elseif (isset($data['location'])) {
            // If location name is provided, find the office by name
            $office = Office::where('name', $data['location'])->first();
            $locationId = $office ? $office->id : null;
        }

        return self::create([
            'item_id' => $itemId,
            'item_type' => $itemType,
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'action' => $action,
            'location_id' => $locationId,
            'notes' => $data['notes'] ?? null,
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
        $uniqueUsersScanning = $query->distinct('user_id')->count();

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

        // Get scans by location
        $scansByLocation = $query->whereNotNull('location_id')
            ->with('office')
            ->get()
            ->groupBy('location_id')
            ->map(function($scans, $locationId) {
                $office = $scans->first()->office;
                return [
                    'office_name' => $office ? $office->name : 'Unknown Office',
                    'count' => $scans->count()
                ];
            })
            ->sortByDesc('count')
            ->pluck('count', 'office_name');

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
