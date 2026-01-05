<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OfficeItemLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'item_id',
        'item_type',
        'max_quantity',
    ];

    protected $casts = [
        'max_quantity' => 'integer',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    public function consumable()
    {
        return $this->belongsTo(Consumable::class, 'item_id');
    }

    public function nonConsumable()
    {
        return $this->belongsTo(NonConsumable::class, 'item_id');
    }
}