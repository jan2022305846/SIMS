<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $request_id
 * @property int $acknowledged_by
 * @property int|null $witnessed_by
 * @property string|null $signature_data
 * @property string|null $signature_type
 * @property \Carbon\Carbon $acknowledged_at
 * @property string|null $acknowledgment_notes
 * @property array|null $items_received
 * @property string|null $photo_path
 * @property string|null $photo_original_name
 * @property string $receipt_number
 * @property array|null $receipt_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $location_data
 * @property string $verification_hash
 * @property bool $is_verified
 * @property \Carbon\Carbon|null $verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Request $request
 * @property-read \App\Models\User $acknowledgedBy
 * @property-read \App\Models\User|null $witnessedBy
 * @property-read string|null $photo_url
 * @property-read string|null $signature_image
 */
class RequestAcknowledgment extends Model
{
    protected $fillable = [
        'request_id',
        'acknowledged_by',
        'witnessed_by',
        'signature_data',
        'signature_type',
        'acknowledged_at',
        'acknowledgment_notes',
        'items_received',
        'photo_path',
        'photo_original_name',
        'receipt_number',
        'receipt_data',
        'ip_address',
        'user_agent',
        'location_data',
        'verification_hash',
        'is_verified',
        'verified_at'
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'verified_at' => 'datetime',
        'items_received' => 'array',
        'receipt_data' => 'array',
        'location_data' => 'array',
        'is_verified' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function witnessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by');
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $lastReceipt = self::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastReceipt ? (int)substr($lastReceipt->receipt_number, -4) + 1 : 1;
        
        return 'REC-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate verification hash
     */
    public function generateVerificationHash()
    {
        $data = [
            'request_id' => $this->request_id,
            'acknowledged_by' => $this->acknowledged_by,
            'acknowledged_at' => $this->acknowledged_at,
            'receipt_number' => $this->receipt_number,
            'items_received' => $this->items_received
        ];
        
        return hash('sha256', json_encode($data) . config('app.key'));
    }

    /**
     * Verify the acknowledgment integrity
     */
    public function verifyIntegrity()
    {
        return $this->verification_hash === $this->generateVerificationHash();
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }

    /**
     * Get signature image data URL
     */
    public function getSignatureImageAttribute()
    {
        if ($this->signature_data) {
            return 'data:image/png;base64,' . $this->signature_data;
        }
        return null;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($acknowledgment) {
            if (!$acknowledgment->receipt_number) {
                $acknowledgment->receipt_number = self::generateReceiptNumber();
            }
            
            if (!$acknowledgment->acknowledged_at) {
                $acknowledgment->acknowledged_at = Carbon::now();
            }
            
            $acknowledgment->verification_hash = $acknowledgment->generateVerificationHash();
            $acknowledgment->verified_at = Carbon::now();
        });

        static::updating(function ($acknowledgment) {
            $acknowledgment->verification_hash = $acknowledgment->generateVerificationHash();
        });
    }
}
