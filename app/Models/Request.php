<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

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
        // Return a relationship that points to the first request item's itemable
        // This maintains backward compatibility with existing code
        return $this->hasOneThrough(
            related: Model::class,
            through: RequestItem::class,
            firstKey: 'id',
            secondKey: 'item_id',
            localKey: 'id',
            secondLocalKey: 'item_id'
        )->whereRaw('1 = 1'); // This will be filtered by the morph type in the query
    }

    // Override __get to handle the item property
    public function __get($key)
    {
        if ($key === 'item') {
            // Return the first item from requestItems
            // Check if requestItems relationship is loaded
            if ($this->relationLoaded('requestItems')) {
                $firstRequestItem = $this->requestItems->first();
                return $firstRequestItem ? $firstRequestItem->itemable : null;
            } else {
                // Load the first request item with its itemable relationship
                $firstRequestItem = $this->requestItems()->with('itemable')->first();
                return $firstRequestItem ? $firstRequestItem->itemable : null;
            }
        }

        return parent::__get($key);
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
        // This method is deprecated - faculty now generate claim slips directly
        // Keeping for backwards compatibility but should not be used in new workflow
        return false;
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
        NotificationService::notifyRequestApproved($this);
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
        // Ensure requestItems with itemable relationships are loaded
        if (!$this->relationLoaded('requestItems')) {
            $this->load('requestItems.itemable');
        } elseif (!$this->requestItems->first() || !$this->requestItems->first()->relationLoaded('itemable')) {
            $this->requestItems->load('itemable');
        }

        // Generate claim slip number
        $claimSlipNumber = 'CS-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);

        $this->update([
            'status' => 'fulfilled',
            'claim_slip_number' => $claimSlipNumber,
        ]);

        // Note: Stock deduction moved to markAsClaimed to avoid double deduction
        // Stock was already reserved in approveByAdmin, now it's ready for pickup
    }

    public function markAsClaimed(User $user)
    {
        // Note: requestItems with itemable relationships should already be loaded by the controller
        // to handle the custom morphTo relationships properly

        // Process stock movements and create transaction logs
        foreach ($this->requestItems as $requestItem) {
            // For consumables: deduct from inventory and log the transaction
            if ($requestItem->isConsumable() && $requestItem->itemable) {
                // Actually deduct stock now (previously was done during approval)
                $requestItem->itemable->decrement('quantity', $requestItem->quantity);

                // Log the stock transaction for reporting
                \App\Models\Log::create([
                    'user_id' => $this->user_id, // The faculty member who requested
                    'item_id' => $requestItem->item_id,
                    'action' => 'claimed', // New action type for claimed items
                    'details' => "Claimed from request #{$this->id} (Claim Slip: {$this->claim_slip_number})",
                    'quantity' => $requestItem->quantity,
                    'notes' => "Processed by admin: {$user->name}",
                ]);
            }

            // For non-consumables, update the current holder
            if ($requestItem->isNonConsumable() && $requestItem->itemable) {
                $requestItem->itemable->current_holder_id = $this->user_id; // Assign to the faculty member
                $requestItem->itemable->save();

                // Log the assignment for reporting
                \App\Models\Log::create([
                    'user_id' => $this->user_id,
                    'item_id' => $requestItem->item_id,
                    'action' => 'assigned', // New action type for assigned non-consumables
                    'details' => "Assigned from request #{$this->id} (Claim Slip: {$this->claim_slip_number})",
                    'quantity' => 1, // Non-consumables are assigned individually
                    'notes' => "Assigned to faculty member, processed by admin: {$user->name}",
                ]);
            }
        }

        $this->update([
            'status' => 'claimed',
        ]);

        // Notify the faculty user
        NotificationService::notifyRequestClaimed($this);
    }

    public function decline(User $user, ?string $reason = null)
    {
        Log::info('Request decline method called', [
            'request_id' => $this->id,
            'current_status' => $this->status,
            'reason' => $reason,
            'user_id' => $user->id
        ]);

        $result = $this->update([
            'status' => 'declined',
            'notes' => $reason,
        ]);

        Log::info('Request update result', [
            'request_id' => $this->id,
            'update_result' => $result,
            'new_status' => $this->status,
            'new_notes' => $this->notes
        ]);

        // Notify the faculty user
        NotificationService::notifyRequestDeclined($this, $reason ?? 'No reason provided');
    }

    public function cancel(User $user, ?string $reason = null)
    {
        Log::info('Request cancel method called', [
            'request_id' => $this->id,
            'current_status' => $this->status,
            'reason' => $reason,
            'user_id' => $user->id
        ]);

        $result = $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        Log::info('Request update result', [
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
        // Ensure requestItems with itemable relationships are loaded
        if (!$this->relationLoaded('requestItems')) {
            $this->load('requestItems.itemable');
        } elseif (!$this->requestItems->first() || !$this->requestItems->first()->relationLoaded('itemable')) {
            $this->requestItems->load('itemable');
        }

        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->isConsumable() && $requestItem->itemable && $requestItem->itemable->quantity < $requestItem->quantity) {
                return false;
            }
        }
        return true;
    }

    public function getStockIssues()
    {
        // Ensure requestItems with itemable relationships are loaded
        if (!$this->relationLoaded('requestItems')) {
            $this->load('requestItems.itemable');
        } elseif (!$this->requestItems->first() || !$this->requestItems->first()->relationLoaded('itemable')) {
            $this->requestItems->load('itemable');
        }

        $issues = [];
        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->isConsumable() && $requestItem->itemable && $requestItem->itemable->quantity < $requestItem->quantity) {
                $issues[] = [
                    'item_name' => $requestItem->getItemName(),
                    'requested' => $requestItem->quantity,
                    'available' => $requestItem->itemable->quantity,
                ];
            }
        }
        return $issues;
    }

    public function reserveStockForItems()
    {
        // Ensure requestItems with itemable relationships are loaded
        if (!$this->relationLoaded('requestItems')) {
            $this->load('requestItems.itemable');
        } elseif (!$this->requestItems->first() || !$this->requestItems->first()->relationLoaded('itemable')) {
            $this->requestItems->load('itemable');
        }

        foreach ($this->requestItems as $requestItem) {
            if ($requestItem->hasSufficientStock()) {
                // Create stock reservation record (but don't deduct stock yet)
                // Stock will be deducted when the request is actually claimed
                \App\Models\StockReservation::create([
                    'request_item_id' => $requestItem->id,
                    'item_id' => $requestItem->item_id,
                    'item_type' => $requestItem->item_type,
                    'quantity_reserved' => $requestItem->quantity,
                    'reserved_until' => now()->addDays(7), // 7 days reservation
                    'status' => 'active',
                ]);

                // Mark as reserved (but don't deduct stock)
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

