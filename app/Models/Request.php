<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property int $quantity
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
        'item_id',
        'item_type',
        'quantity',
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

    public function item()
    {
        return $this->morphTo();
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

        // Update item stock - only for consumables
        if ($this->item_type === 'consumable') {
            $this->item->quantity -= $this->quantity;
            $this->item->save();
        }
    }

    public function markAsClaimed(User $user)
    {
        // Reduce stock for consumables
        if ($this->item_type === 'consumable') {
            $this->item->quantity -= $this->quantity;
            $this->item->save();
        }

        // For non-consumables, update the current holder
        if ($this->item_type === 'non_consumable') {
            $this->item->current_holder_id = $user->id;
            $this->item->save();
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

    public function getStatusColorClass()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved_by_admin' => 'bg-green-100 text-green-800',
            'fulfilled' => 'bg-purple-100 text-purple-800',
            'claimed' => 'bg-gray-100 text-gray-800',
            'declined' => 'bg-red-100 text-red-800',
            'returned' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusDisplayName()
    {
        return match($this->status) {
            'pending' => 'Pending Admin Review',
            'approved_by_admin' => 'Admin Approved',
            'fulfilled' => 'Ready for Pickup',
            'claimed' => 'Claimed',
            'declined' => 'Declined by Admin',
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

