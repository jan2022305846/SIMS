<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if the notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if the notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Get the URL for the notification (if applicable).
     */
    public function getUrlAttribute(): ?string
    {
        $data = $this->data ?? [];

        switch ($this->type) {
            case 'pending_request':
                return isset($data['request_id']) ? route('requests.show', $data['request_id']) : null;
            case 'low_stock':
                return isset($data['item_id']) ? route('items.show', [$data['item_id'], $data['item_type'] ?? 'consumable']) : null;
            case 'approved':
            case 'claimed':
            case 'declined':
                return isset($data['request_id']) ? route('faculty.requests.show', $data['request_id']) : null;
            default:
                return null;
        }
    }

    /**
     * Get the icon class for the notification type.
     */
    public function getIconAttribute(): string
    {
        switch ($this->type) {
            case 'pending_request':
                return 'fas fa-clock';
            case 'low_stock':
                return 'fas fa-exclamation-triangle';
            case 'approved':
                return 'fas fa-check-circle';
            case 'claimed':
                return 'fas fa-box-open';
            case 'declined':
                return 'fas fa-times-circle';
            default:
                return 'fas fa-bell';
        }
    }

    /**
     * Get the color class for the notification type.
     */
    public function getColorAttribute(): string
    {
        switch ($this->type) {
            case 'pending_request':
                return 'warning';
            case 'low_stock':
                return 'danger';
            case 'approved':
                return 'success';
            case 'claimed':
                return 'info';
            case 'declined':
                return 'danger';
            default:
                return 'primary';
        }
    }
}
