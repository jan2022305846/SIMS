<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NonConsumable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'product_code',
        'quantity',
        'unit',
        'brand',
        'min_stock',
        'max_stock',
        'current_holder_id',
        'location',
        'condition',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'current_holder_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'item_id')->where('item_type', 'non_consumable');
    }

    public function requestItems(): MorphMany
    {
        return $this->morphMany(RequestItem::class, 'itemable');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ItemScanLog::class, 'item_id');
    }

    public function latestScanLog()
    {
        return $this->hasOne(ItemScanLog::class, 'item_id')->latestOfMany();
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
            'non_consumable',
            $this->product_code
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
            'non_consumable',
            $this->product_code
        );
    }

    /**
     * Check if item is out of stock
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Check if item is low on stock
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock && $this->quantity > 0;
    }

    /**
     * Get stock percentage relative to minimum stock
     *
     * @return float
     */
    public function getStockPercentage(): float
    {
        if ($this->min_stock <= 0) {
            return $this->quantity > 0 ? 100.0 : 0.0;
        }

        return min(($this->quantity / $this->min_stock) * 100, 100.0);
    }

    /**
     * Get stock status as string
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
     * Check if item is assigned to a user
     *
     * @return bool
     */
    public function isAssigned(): bool
    {
        return !is_null($this->current_holder_id);
    }
}
