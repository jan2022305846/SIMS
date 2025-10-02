<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
    ];

    /**
     * Get all users in this office
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get non-consumable items located in this office
     */
    public function nonConsumableItems()
    {
        return $this->hasMany(NonConsumable::class, 'location', 'name');
    }

    /**
     * Get all items (consumables and non-consumables) located in this office
     */
    public function items()
    {
        // For backward compatibility, return non-consumable items
        // Consumables don't have location-based relationships
        return $this->nonConsumableItems();
    }
}