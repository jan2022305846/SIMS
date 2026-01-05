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
        'adjusted_quantity',
        'adjustment_reason',
        'item_status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'adjusted_quantity' => 'integer',
    ];

    // Relationships
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function consumable()
    {
        return $this->belongsTo(\App\Models\Consumable::class, 'item_id');
    }

    public function nonConsumable()
    {
        return $this->belongsTo(\App\Models\NonConsumable::class, 'item_id');
    }

    public function itemable()
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    // Override the morphTo resolution to handle our custom types
    protected function getMorphedModel($type)
    {
        return match($type) {
            'consumable' => \App\Models\Consumable::class,
            'non_consumable' => \App\Models\NonConsumable::class,
            default => null,
        };
    }

    // Override the __get method to ensure proper morphTo resolution
    public function __get($key)
    {
        if ($key === 'itemable') {
            if (!$this->relationLoaded('itemable')) {
                $morphClass = $this->getMorphedModel($this->item_type);
                if ($morphClass && class_exists($morphClass)) {
                    $related = $morphClass::find($this->item_id);
                    $this->setRelation('itemable', $related);
                } else {
                    $this->setRelation('itemable', null);
                }
            }
            return $this->getRelation('itemable');
        }

        return parent::__get($key);
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
        // Ensure itemable is loaded if not already
        if (!$this->relationLoaded('itemable')) {
            $this->load('itemable');
        }
        return $this->itemable ? $this->itemable->name : 'Unknown Item';
    }

    public function getItemCode()
    {
        // Ensure itemable is loaded if not already
        if (!$this->relationLoaded('itemable')) {
            $this->load('itemable');
        }
        return $this->itemable ? ($this->itemable->item_code ?? $this->itemable->product_code ?? 'N/A') : 'N/A';
    }

    public function getAvailableStock()
    {
        // Ensure itemable is loaded if not already
        if (!$this->relationLoaded('itemable')) {
            $this->load('itemable');
        }
        return $this->itemable ? $this->itemable->quantity : 0;
    }

    public function hasSufficientStock()
    {
        return $this->getAvailableStock() >= $this->quantity;
    }
    public function getFinalQuantity()
    {
        return $this->adjusted_quantity ?? $this->quantity;
    }

    public function isAdjusted()
    {
        return $this->adjusted_quantity !== null && $this->adjusted_quantity !== $this->quantity;
    }

    public function getAdjustmentText()
    {
        if (!$this->isAdjusted()) {
            return null;
        }
        
        $original = $this->quantity;
        $adjusted = $this->adjusted_quantity;
        
        if ($adjusted > $original) {
            return "Increased from {$original} to {$adjusted}";
        } elseif ($adjusted < $original) {
            return "Reduced from {$original} to {$adjusted}";
        }
        
        return null;
    }

    public function canBeAdjusted()
    {
        return $this->item_status === 'pending';
    }

    public function approve($reason = null)
    {
        $this->item_status = 'approved';
        if ($reason) {
            $this->adjustment_reason = $reason;
        }
        $this->save();
    }

    public function decline($reason = null)
    {
        $this->item_status = 'declined';
        if ($reason) {
            $this->adjustment_reason = $reason;
        }
        $this->save();
    }
}