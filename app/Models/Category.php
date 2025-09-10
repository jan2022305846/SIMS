<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'type'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}

