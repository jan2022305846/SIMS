<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'batch_uuid',
        'ip_address',
        'user_agent',
    ];

    // Disable automatic timestamps since the table only has created_at
    public $timestamps = false;

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the subject model (polymorphic relationship)
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the causer model (polymorphic relationship)
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Log an activity
     */
    public static function log(
        string $description,
        ?Model $subject = null,
        ?Model $causer = null,
        string $logName = 'default',
        array $properties = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->getKey() : null,
            'properties' => $properties,
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->userAgent(),
        ]);
    }

    /**
     * Get activities by log name
     */
    public static function byLogName(string $logName)
    {
        return static::where('log_name', $logName);
    }

    /**
     * Get activities by causer
     */
    public static function byCauser(Model $causer)
    {
        return static::where('causer_type', get_class($causer))
                    ->where('causer_id', $causer->getKey());
    }

    /**
     * Get activities by subject
     */
    public static function bySubject(Model $subject)
    {
        return static::where('subject_type', get_class($subject))
                    ->where('subject_id', $subject->getKey());
    }
}
