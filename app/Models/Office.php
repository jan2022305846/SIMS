<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * Get the office head (designated user who leads this office)
     */
    public function officeHead()
    {
        return $this->belongsTo(User::class, 'office_head_id');
    }

    /**
     * Get all users belonging to this office
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all faculty members in this office
     */
    public function faculty()
    {
        return $this->hasMany(User::class)->where('role', 'faculty');
    }

    /**
     * Get all office heads in this office (including the designated one)
     */
    public function officeHeads()
    {
        return $this->hasMany(User::class)->where('role', 'office_head');
    }

    /**
     * Check if a user is the office head of this office
     */
    public function isOfficeHead(User $user): bool
    {
        return $this->office_head_id === $user->id;
    }

    /**
     * Get office statistics
     */
    public function getStats(): array
    {
        return [
            'total_users' => $this->users()->count(),
            'faculty_count' => $this->faculty()->count(),
            'office_heads_count' => $this->officeHeads()->count(),
            'has_designated_head' => !is_null($this->office_head_id),
        ];
    }
}
