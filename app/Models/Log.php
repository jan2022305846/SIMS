<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property string $action
 * @property string|null $details
 * @property int $quantity
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'action',
        'details',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item associated with this log (polymorphic)
     */
    public function item()
    {
        // This would need additional logic to determine if it's consumable or non_consumable
        // For now, we'll leave it as is since the database doesn't have item_type in logs table
        return null;
    }
}
