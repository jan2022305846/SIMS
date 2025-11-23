<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_item_id',
        'item_id',
        'item_type',
        'quantity_reserved',
        'reserved_until',
        'status',
    ];

    protected $casts = [
        'quantity_reserved' => 'integer',
        'reserved_until' => 'datetime',
    ];

    // Relationships
    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function item()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere('reserved_until', '<', now());
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && (!$this->reserved_until || $this->reserved_until->isFuture());
    }

    public function isExpired()
    {
        return $this->status === 'expired' || ($this->reserved_until && $this->reserved_until->isPast());
    }

    public function expire()
    {
        $this->update(['status' => 'expired']);
        // Return stock to available inventory
        if ($this->item && $this->isConsumable()) {
            $this->item->increment('quantity', $this->quantity_reserved);
        }
    }

    public function fulfill()
    {
        $this->update(['status' => 'fulfilled']);
        // Stock is already deducted, no need to return it
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
        // Return stock to available inventory
        if ($this->item && $this->isConsumable()) {
            $this->item->increment('quantity', $this->quantity_reserved);
        }
    }

    private function isConsumable()
    {
        return $this->item_type === 'consumable';
    }
}
