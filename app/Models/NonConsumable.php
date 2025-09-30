<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NonConsumable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'product_code',
        'quantity',
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
            $this->product_code
        );
    }
}
