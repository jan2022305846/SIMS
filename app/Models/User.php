<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string|null $department
 * @property string|null $username
 * @property string $school_id
 * @property string|null $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property int|null $office_id
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @method bool isAdmin()
 * @method bool isOfficeHead()
 * @method bool isFaculty()
 * @method bool canRequestForOffice()
 * @method bool canApproveRequests()
 * @method bool canScanQR()
 * 
 * Note: There is NO 'position' property - use 'department' instead
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'department',
        'username',
        'school_id',
        'email',
        'password',
        'role',
        'office_id',
        'must_set_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function custodiedItems()
    {
        return $this->hasMany(Item::class, 'custodian_id');
    }

    /**
     * Get the office this user belongs to
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get offices where this user is the office head
     */
    public function managedOffices()
    {
        return $this->hasMany(Office::class, 'office_head_id');
    }

    /**
     * Get items currently held by this user
     */
    public function heldItems()
    {
        return $this->hasMany(Item::class, 'current_holder_id');
    }

    // Role helper methods
    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an office head
     */
    public function isOfficeHead(): bool
    {
        return $this->role === 'office_head';
    }

    /**
     * Check if user is faculty
     */
    public function isFaculty(): bool
    {
        return $this->role === 'faculty';
    }

    /**
     * Check if user can request for office
     */
    public function canRequestForOffice(): bool
    {
        return $this->isAdmin() || $this->isOfficeHead();
    }

    /**
     * Check if user can approve requests
     */
    public function canApproveRequests(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can scan QR codes
     */
    public function canScanQR(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user is the head of a specific office
     */
    public function isHeadOf(Office $office): bool
    {
        return $office->office_head_id === $this->id;
    }

    /**
     * Check if user belongs to a specific office
     */
    public function belongsToOffice(Office $office): bool
    {
        return $this->office_id === $office->id;
    }

    /**
     * Check if user must set their password
     */
    public function mustSetPassword(): bool
    {
        return $this->must_set_password;
    }

    /**
     * Mark that user has set their password
     */
    public function markPasswordAsSet(): void
    {
        $this->update(['must_set_password' => false]);
    }
}