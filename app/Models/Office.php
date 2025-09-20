<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $location
 * @property int|null $office_head_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'location',
        'office_head_id',
    ];

    /**
     * Get the office head
     */
    public function officeHead()
    {
        return $this->belongsTo(User::class, 'office_head_id');
    }

    /**
     * Get all users in this office
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get items located in this office
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'location', 'name');
    }
}