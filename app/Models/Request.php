<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Carbon\Carbon|null $return_date
 * @property string|null $purpose
 * @property \Carbon\Carbon|null $needed_date
 * @property int|null $approved_by_admin_id
 * @property int|null $office_id
 * @property string $priority
 * @property string|null $claim_slip_number
 * @property array|null $attachments
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'return_date',
        'purpose',
        'needed_date',
        'approved_by_admin_id',
        'office_id',
        'priority',
        'claim_slip_number',
        'attachments',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'datetime',
        'needed_date' => 'datetime',
        'attachments' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }

    // Legacy relationship for backwards compatibility
    public function item()
    {
        return $this->requestItems()->first()?->item();
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_admin_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    // Workflow Methods
    public function canBeApprovedByAdmin()
    {
        return $this->status === 'pending';
    }

    public function canBeFulfilled()
    {
        return $this->status === 'approved_by_admin';
    }

    public function canGenerateClaimSlip()
    {
        return $this->status === 'approved_by_admin';
    }

    public function canBeClaimed()
    {
        return in_array($this->status, ['fulfilled']);
    }

    public function approveByAdmin(User $user)
    {
        $this->update([
            'status' => 'approved_by_admin',
            'approved_by_admin_id' => $user->id,
        ]);

        // Handle stock reservations for all request items
        $this->reserveStockForItems();

        // Notify the faculty user
        \App\Services\NotificationService::notifyRequestApproved($this);
    }

    public function generateClaimSlip()
    {
        // Generate claim slip number
        $claimSlipNumber = 'CS-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);

        $this->update([
            'status' => 'fulfilled',
            'claim_slip_number' => $claimSlipNumber,
        ]);
    }

    public function fulfill(User $user)
    {
        // Generate claim slip number
        $claimSlipNumber = 'CS-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);

        $this->update([
            'status' => 'fulfilled',
            'claim_slip_number' => $claimSlipNumber,
        ]);

        // Update item stock for all request items - only for consumables
        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->isConsumable()) {
                $requestItem->item->quantity -= $requestItem->quantity;
                $requestItem->item->save();
            }
        }
    }

    public function markAsClaimed(User $user)
    {
        // Process each request item
        foreach ($this->requestItems as $requestItem) {
            // Reduce stock for consumables
            if ($requestItem->isConsumable()) {
                $requestItem->item->quantity -= $requestItem->quantity;
                $requestItem->item->save();
            }

            // For non-consumables, update the current holder
            if ($requestItem->isNonConsumable()) {
                $requestItem->item->current_holder_id = $user->id;
                $requestItem->item->save();
            }
        }

        $this->update([
            'status' => 'claimed',
        ]);

        // Notify the faculty user
        \App\Services\NotificationService::notifyRequestClaimed($this);
    }

    public function decline(User $user, ?string $reason = null)
    {
        \Illuminate\Support\Facades\Log::info('Request decline method called', [
            'request_id' => $this->id,
            'current_status' => $this->status,
            'reason' => $reason,
            'user_id' => $user->id
        ]);

        $result = $this->update([
            'status' => 'declined',
            'notes' => $reason,
        ]);

        \Illuminate\Support\Facades\Log::info('Request update result', [
            'request_id' => $this->id,
            'update_result' => $result,
            'new_status' => $this->status,
            'new_notes' => $this->notes
        ]);

        // Notify the faculty user
        \App\Services\NotificationService::notifyRequestDeclined($this, $reason ?? 'No reason provided');
    }

    public function cancel(User $user, ?string $reason = null)
    {
        \Illuminate\Support\Facades\Log::info('Request cancel method called', [
            'request_id' => $this->id,
            'current_status' => $this->status,
            'reason' => $reason,
            'user_id' => $user->id
        ]);

        $result = $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        \Illuminate\Support\Facades\Log::info('Request update result', [
            'request_id' => $this->id,
            'update_result' => $result,
            'new_status' => $this->status,
            'new_notes' => $this->notes
        ]);

        // Notify the faculty user (though they initiated it, might be useful for logging)
        // \App\Services\NotificationService::notifyRequestCancelled($this, $reason ?? 'No reason provided');
    }

    // Status Helper Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApprovedByAdmin()
    {
        return $this->status === 'approved_by_admin';
    }

    public function isReadyForPickup()
    {
        return $this->status === 'fulfilled';
    }

    public function isFulfilled()
    {
        return $this->status === 'fulfilled';
    }

    public function isClaimed()
    {
        return $this->status === 'claimed';
    }

    public function isDeclined()
    {
        return $this->status === 'declined';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getStatusColorClass()
    {
        return match($this->status) {
            'pending' => 'bg-primary text-white', // Light background with subtle blue text - visible but not saturated
            'approved_by_admin' => 'bg-success text-white', // Green
            'fulfilled' => 'bg-primary text-white', // Blue
            'claimed' => 'bg-dark text-white', // Dark gray
            'declined' => 'bg-danger text-white', // Red
            'cancelled' => 'bg-secondary text-white', // Gray background - more visible
            'returned' => 'bg-warning text-dark', // Yellow with dark text for returned
            default => 'bg-light text-dark', // Light background with dark text
        };
    }

    public function getStatusDisplayName()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'approved_by_admin' => 'Approved',
            'fulfilled' => 'Ready',
            'claimed' => 'Claimed',
            'declined' => 'Declined',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getPriorityColorClass()
    {
        return match($this->priority) {
            'low' => 'bg-green-100 text-green-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Helper methods for bulk requests
    public function getTotalItems()
    {
        return $this->requestItems->sum('quantity');
    }

    public function getUniqueItemsCount()
    {
        return $this->requestItems->count();
    }

    public function hasSufficientStock()
    {
        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->isConsumable() && $requestItem->item->quantity < $requestItem->quantity) {
                return false;
            }
        }
        return true;
    }

    public function getStockIssues()
    {
        $issues = [];
        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->isConsumable() && $requestItem->item->quantity < $requestItem->quantity) {
                $issues[] = [
                    'item_name' => $requestItem->getItemName(),
                    'requested' => $requestItem->quantity,
                    'available' => $requestItem->item->quantity,
                ];
            }
        }
        return $issues;
    }

    public function reserveStockForItems()
    {
        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->hasSufficientStock()) {
                // Reserve stock
                \App\Models\StockReservation::create([
                    'request_item_id' => $requestItem->id,
                    'item_id' => $requestItem->item_id,
                    'item_type' => $requestItem->item_type,
                    'quantity_reserved' => $requestItem->quantity,
                    'reserved_until' => now()->addDays(7), // 7 days reservation
                    'status' => 'active',
                ]);

                // Deduct from available stock
                if ($requestItem->isConsumable()) {
                    $requestItem->item->decrement('quantity', $requestItem->quantity);
                }

                $requestItem->update(['status' => 'reserved']);
            } else {
                // Mark as unavailable
                $requestItem->update(['status' => 'unavailable']);
            }
        }
    }

    public function getAvailableItems()
    {
        return $this->requestItems->where('status', 'reserved');
    }

    public function getUnavailableItems()
    {
        return $this->requestItems->where('status', 'unavailable');
    }

    public function hasPartialFulfillment()
    {
        return $this->getAvailableItems()->count() > 0 && $this->getUnavailableItems()->count() > 0;
    }

    // Scopes
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeByOffice($query, $officeId)
    {
        return $query->where('office_id', $officeId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}

