<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false; // Only has created_at
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
