<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'item_id',
        'item_type',
        'quantity',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationships
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    // Helper methods
    public function isConsumable()
    {
        return $this->item_type === 'consumable';
    }

    public function isNonConsumable()
    {
        return $this->item_type === 'non_consumable';
    }

    public function isAvailable()
    {
        return $this->status === 'available' || $this->status === null;
    }

    public function isReserved()
    {
        return $this->status === 'reserved';
    }

    public function isUnavailable()
    {
        return $this->status === 'unavailable';
    }

    public function getItemName()
    {
        return $this->item ? $this->item->name : 'Unknown Item';
    }

    public function getItemCode()
    {
        return $this->item ? ($this->item->item_code ?? $this->item->product_code ?? 'N/A') : 'N/A';
    }

    public function getAvailableStock()
    {
        return $this->item ? $this->item->quantity : 0;
    }

    public function hasSufficientStock()
    {
        return $this->getAvailableStock() >= $this->quantity;
    }
}