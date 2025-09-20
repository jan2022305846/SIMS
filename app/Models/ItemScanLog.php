<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $item_id
 * @property \Carbon\Carbon $scanned_at
 * @property string|null $location
 * @property string $scanner_type
 * @property array|null $scan_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ItemScanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'scanned_at',
        'location',
        'scanner_type',
        'scan_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'scan_data' => 'array',
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
     * Create a scan log entry
     */
    public static function logScan(Item $item, array $data = []): self
    {
        return self::create([
            'item_id' => $item->id,
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'scanned_at' => now(),
            'location' => $data['location'] ?? null,
            'scanner_type' => $data['scanner_type'] ?? 'admin',
            'scan_data' => $data['scan_data'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get scan logs for a specific item
     */
    public static function forItem(Item $item)
    {
        return self::where('item_id', $item->id)
                   ->orderBy('scanned_at', 'desc');
    }

    /**
     * Get recent scan logs
     */
    public static function recent($limit = 20)
    {
        return self::with('item')
                   ->orderBy('scanned_at', 'desc')
                   ->limit($limit);
    }

    /**
     * Get scans for a specific date range
     */
    public static function betweenDates($startDate, $endDate)
    {
        return self::with('item')
                   ->whereBetween('scanned_at', [$startDate, $endDate])
                   ->orderBy('scanned_at', 'desc');
    }

    /**
     * Get formatted scan time
     */
    public function getFormattedScanTimeAttribute(): string
    {
        return $this->scanned_at->format('M d, Y h:i A');
    }

    /**
     * Get scan statistics for a specific period
     */
    public static function getScanStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('scanned_at', [$startDate, $endDate]);
        }

        return [
            'total_scans' => $query->count(),
            'unique_items_scanned' => $query->distinct('item_id')->count(),
            'unique_users_scanning' => $query->distinct('user_id')->count(),
            'scans_by_scanner_type' => $query->selectRaw('scanner_type, COUNT(*) as count')
                ->groupBy('scanner_type')
                ->pluck('count', 'scanner_type'),
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
            'scans_by_location' => $query->whereNotNull('location')
                ->selectRaw('location, COUNT(*) as count')
                ->groupBy('location')
                ->orderBy('count', 'desc')
                ->pluck('count', 'location'),
        ];
    }

    /**
     * Get scan activity for dashboard
     */
    public static function getDashboardScanData($days = 7)
    {
        $startDate = now()->subDays($days);

        return self::where('scanned_at', '>=', $startDate)
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as scan_count')
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
            $query->where('scanned_at', '>=', $thresholdDate);
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

        $scans = $query->orderBy('scanned_at')->get();

        if ($scans->isEmpty()) {
            return [];
        }

        $frequency = [];
        $previousScan = null;

        foreach ($scans as $scan) {
            if ($previousScan) {
                $daysDiff = $scan->scanned_at->diffInDays($previousScan->scanned_at);
                $frequency[] = $daysDiff;
            }
            $previousScan = $scan;
        }

        return [
            'average_days_between_scans' => !empty($frequency) ? round(array_sum($frequency) / count($frequency), 1) : 0,
            'min_days_between_scans' => !empty($frequency) ? min($frequency) : 0,
            'max_days_between_scans' => !empty($frequency) ? max($frequency) : 0,
            'total_scans' => $scans->count(),
            'first_scan' => $scans->first()->scanned_at,
            'last_scan' => $scans->last()->scanned_at,
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
        $unusualScans = self::selectRaw('item_id, DATE(scanned_at) as scan_date, COUNT(*) as daily_scans')
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
