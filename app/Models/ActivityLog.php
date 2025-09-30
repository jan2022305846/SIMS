<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
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
        'event',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'collection',
    ];

    const UPDATED_AT = null;

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getExtraProperty(string $propertyName, mixed $defaultValue = null): mixed
    {
        return $this->properties->get($propertyName, $defaultValue);
    }

    public function changes(): Collection
    {
        return $this->properties->get('attributes', collect());
    }

    public function getChangesAttribute(): Collection
    {
        return $this->getExtraProperty('attributes', collect());
    }

    // Scopes
    public function scopeInLog(Builder $query, ...$logNames): Builder
    {
        if (is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForBatch(Builder $query, string $batchUuid): Builder
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    // Static methods for logging
    public static function log(string $description): self
    {
        $activityLog = new static;
        
        $activityLog->description = $description;
        $activityLog->log_name = 'default';
        
        if (Auth::check()) {
            $user = Auth::user();
            $activityLog->causer_type = get_class($user);
            $activityLog->causer_id = $user->id;
        }

        $activityLog->ip_address = request()->ip();
        $activityLog->user_agent = request()->userAgent();

        return $activityLog;
    }

    public function performedOn(Model $model): self
    {
        $this->subject_type = $model->getMorphClass();
        $this->subject_id = $model->getKey();

        return $this;
    }

    public function causedBy(Model $model): self
    {
        $this->causer_type = $model->getMorphClass();
        $this->causer_id = $model->getKey();

        return $this;
    }

    public function withProperties($properties): self
    {
        $this->properties = collect($properties);

        return $this;
    }

    public function withProperty(string $key, $value): self
    {
        $properties = $this->properties ?? collect();
        
        $this->properties = $properties->put($key, $value);

        return $this;
    }

    public function inLog(string $logName): self
    {
        $this->log_name = $logName;

        return $this;
    }

    public function withEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    // Helper methods for different activity types
    public static function logUserActivity(string $description, ?Model $user = null): void
    {
        static::log($description)
            ->inLog('user_activity')
            ->causedBy($user ?? Auth::user())
            ->save();
    }

    public static function logItemActivity(string $description, Model $item, ?string $event = null): void
    {
        static::log($description)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent($event ?? 'updated')
            ->save();
    }

    public static function logRequestActivity(string $description, Model $request, ?string $event = null, array $properties = []): void
    {
        static::log($description)
            ->inLog('request_workflow')
            ->performedOn($request)
            ->withEvent($event ?? 'updated')
            ->withProperties($properties)
            ->save();
    }

    public static function logSystemActivity(string $description, array $properties = []): void
    {
        static::log($description)
            ->inLog('system')
            ->withProperties($properties)
            ->save();
    }

    // Display helpers
    public function getFormattedDescriptionAttribute(): string
    {
        $description = $this->description;
        
        // Replace placeholders with actual values
        if ($this->causer) {
            $description = str_replace('{causer}', $this->causer->name ?? 'System', $description);
        }
        
        if ($this->subject) {
            $subjectName = $this->subject->name ?? $this->subject->title ?? "#{$this->subject->id}";
            $description = str_replace('{subject}', $subjectName, $description);
        }
        
        return $description;
    }

    public function getEventColorClass(): string
    {
        return match($this->event) {
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'approved' => 'bg-emerald-100 text-emerald-800',
            'rejected', 'declined' => 'bg-red-100 text-red-800',
            'fulfilled' => 'bg-purple-100 text-purple-800',
            'claimed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getLogNameColorClass(): string
    {
        return match($this->log_name) {
            'user_activity' => 'bg-blue-100 text-blue-800',
            'item_management' => 'bg-green-100 text-green-800',
            'request_workflow' => 'bg-purple-100 text-purple-800',
            'system' => 'bg-gray-100 text-gray-800',
            default => 'bg-indigo-100 text-indigo-800',
        };
    }
}
