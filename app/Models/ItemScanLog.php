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
        'user_id',
        'action',
        'location_id',
        'notes',
    ];

    protected $casts = [
        // No specific casts needed for this model
    ];

    /**
     * Get the item that was scanned
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
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
    public static function logScan(Item $item, string $action = 'inventory_check', array $data = []): self
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
            'item_id' => $item->id,
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'action' => $action,
            'location_id' => $locationId,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Get scan logs for a specific item
     */
    public static function forItem(Item $item)
    {
        return self::where('item_id', $item->id)
                   ->orderBy('created_at', 'desc');
    }

    /**
     * Get recent scan logs
     */
    public static function recent($limit = 20)
    {
        return self::with('item')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit);
    }

    /**
     * Get scans for a specific date range
     */
    public static function betweenDates($startDate, $endDate)
    {
        return self::with('item')
                   ->whereBetween('created_at', [$startDate, $endDate])
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

        return [
            'total_scans' => $query->count(),
            'unique_items_scanned' => $query->distinct('item_id')->count(),
            'unique_users_scanning' => $query->distinct('user_id')->count(),
            'most_scanned_items' => $query->selectRaw('item_id, COUNT(*) as scan_count')
                ->with('item')
                ->groupBy('item_id')
                ->orderBy('scan_count', 'desc')
                ->take(10)
                ->get()
                ->map(function($scan) {
                    return [
                        'item' => $scan->item,
                        'scan_count' => $scan->scan_count
                    ];
                }),
            'scans_by_location' => $query->whereNotNull('location_id')
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
                ->pluck('count', 'office_name'),
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

        return \App\Models\Item::whereDoesntHave('scanLogs', function($query) use ($thresholdDate) {
            $query->where('created_at', '>=', $thresholdDate);
        })->get();
    }

    /**
     * Get scan frequency analysis
     */
    public static function getScanFrequencyAnalysis($itemId = null)
    {
        $query = self::query();

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        $scans = $query->orderBy('created_at')->get();

        if ($scans->isEmpty()) {
            return [];
        }

        $frequency = [];
        $previousScan = null;

        foreach ($scans as $scan) {
            if ($previousScan) {
                $daysDiff = $scan->created_at->diffInDays($previousScan->created_at);
                $frequency[] = $daysDiff;
            }
            $previousScan = $scan;
        }

        return [
            'average_days_between_scans' => !empty($frequency) ? round(array_sum($frequency) / count($frequency), 1) : 0,
            'min_days_between_scans' => !empty($frequency) ? min($frequency) : 0,
            'max_days_between_scans' => !empty($frequency) ? max($frequency) : 0,
            'total_scans' => $scans->count(),
            'first_scan' => $scans->first()->created_at,
            'last_scan' => $scans->last()->created_at,
        ];
    }

    /**
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
            ->with('item')
            ->get();

        if ($unusualScans->count() > 0) {
            $alerts['unusual_scan_activity'] = $unusualScans;
        }

        return $alerts;
    }
}
