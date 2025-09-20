<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property string|null $barcode
 * @property string|null $qr_code_data
 * @property int $quantity
 * @property int|null $price
 * @property string|null $brand
 * @property string|null $supplier
 * @property \Carbon\Carbon|null $warranty_date
 * @property int $minimum_stock
 * @property int $maximum_stock
 * @property int $current_stock
 * @property float $unit_price
 * @property float $total_value
 * @property int|null $current_holder_id
 * @property \Carbon\Carbon|null $assigned_at
 * @property string|null $assignment_notes
 * @property string|null $unit
 * @property string $location
 * @property string $condition
 * @property string $qr_code
 * @property \Carbon\Carbon|null $expiry_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Item extends Model
{
    use HasFactory, SoftDeletes;
    
    // Add this line to explicitly enable timestamps
    public $timestamps = true;
    
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'barcode',
        'qr_code_data',
        'quantity',
        'price',
        'brand',
        'supplier',
        'warranty_date',
        'minimum_stock',
        'maximum_stock',
        'current_stock',
        'unit_price',
        'total_value',
        'current_holder_id',
        'assigned_at',
        'assignment_notes',
        'unit',
        'location',
        'condition',
        'qr_code',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'warranty_date' => 'datetime', 
        'assigned_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get the current holder of this item
     */
    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    /**
     * Get scan logs for this item
     */
    public function scanLogs()
    {
        return $this->hasMany(ItemScanLog::class);
    }

    /**
     * Generate QR code for this item
     *
     * @return string Base64 encoded QR code
     */
    public function generateQRCode(): string
    {
        return app(\App\Services\QRCodeService::class)->generateItemQRCode(
            $this->id,
            $this->name,
            $this->barcode
        );
    }

    /**
     * Get QR code data URL for display
     *
     * @return string QR code data URL
     */
    public function getQRCodeDataUrl(): string
    {
        return app(\App\Services\QRCodeService::class)->getItemQRCodeDataUrl(
            $this->id,
            $this->name,
            $this->barcode
        );
    }

    /**
     * Check if item is low in stock
     * 
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Check if item is out of stock
     * 
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    /**
     * Get stock status
     * 
     * @return string
     */
    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        } elseif ($this->isLowStock()) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get stock level percentage
     * 
     * @return float
     */
    public function getStockPercentage(): float
    {
        if ($this->maximum_stock <= 0) {
            return 0;
        }
        return ($this->current_stock / $this->maximum_stock) * 100;
    }

    /**
     * Update total value based on current stock and unit price
     */
    public function updateTotalValue(): void
    {
        $this->total_value = $this->current_stock * $this->unit_price;
        $this->save();
    }

    /**
     * Assign item to a user
     */
    public function assignTo(User $user, ?string $notes = null): void
    {
        $this->current_holder_id = $user->id;
        $this->assigned_at = now();
        $this->assignment_notes = $notes;
        $this->save();
    }

    /**
     * Unassign item (return to inventory)
     */
    public function unassign(): void
    {
        $this->current_holder_id = null;
        $this->assigned_at = null;
        $this->assignment_notes = null;
        $this->save();
    }

    /**
     * Check if item is currently assigned to someone
     */
    public function isAssigned(): bool
    {
        return !is_null($this->current_holder_id);
    }

    /**
     * Get holder name
     */
    public function getHolderNameAttribute(): ?string
    {
        return $this->currentHolder?->name;
    }
}