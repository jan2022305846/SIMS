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
}
