<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property int|null $office_id
 * @property string|null $username
 * @property string|null $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
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
 * Note: Single admin system - only user ID 6 is admin
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'office_id',
        'username',
        'email',
        'password',
        'status',
        'must_set_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_set_password' => 'boolean',
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
        // Deprecated - no custodian field in new database structure
        return collect();
    }

    /**
     * Get items currently held by this user (non-consumables only)
     */
    public function heldItems()
    {
        return $this->hasMany(NonConsumable::class, 'current_holder_id');
    }

    /**
     * Get the user's office
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    // Role helper methods
    /**
     * Check if user is an admin (single admin system - only user ID 6 is admin)
     */
    public function isAdmin(): bool
    {
        return $this->id === 6; // Denver Ian Gemino is the admin
    }

    /**
     * Check if user is faculty (all non-admin users are faculty)
     */
    public function isFaculty(): bool
    {
        return !$this->isAdmin();
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
     * Check if user must set their password
     */
    public function mustSetPassword(): bool
    {
        return $this->must_set_password;
    }

    /**
     * Get the user's scan logs
     */
    public function scanLogs()
    {
        return $this->hasMany(ItemScanLog::class);
    }    
    /**
     * Mark that user has set their password
     */
    public function markPasswordAsSet(): void
    {
        $this->update(['must_set_password' => false]);
    }
}
