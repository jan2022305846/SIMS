<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property int $quantity
 * @property int|null $quantity_approved
 * @property string $status
 * @property string $workflow_status
 * @property \Carbon\Carbon|null $request_date
 * @property \Carbon\Carbon|null $approval_date
 * @property \Carbon\Carbon|null $return_date
 * @property string|null $purpose
 * @property \Carbon\Carbon|null $needed_date
 * @property string|null $admin_notes
 * @property string|null $remarks
 * @property int|null $approved_by_office_head_id
 * @property int|null $approved_by_admin_id
 * @property int|null $fulfilled_by_id
 * @property int|null $claimed_by_id
 * @property \Carbon\Carbon|null $office_head_approval_date
 * @property \Carbon\Carbon|null $admin_approval_date
 * @property \Carbon\Carbon|null $fulfilled_date
 * @property \Carbon\Carbon|null $claimed_date
 * @property \Carbon\Carbon|null $processed_at
 * @property int|null $processed_by
 * @property string|null $department
 * @property string|null $office_head_notes
 * @property string $priority
 * @property string|null $claim_slip_number
 * @property array|null $attachments
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\RequestAcknowledgment|null $acknowledgment
 */
class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'quantity_approved',
        'status',
        'workflow_status',
        'request_date',
        'approval_date',
        'return_date',
        'purpose',
        'needed_date',
        'admin_notes',
        'remarks',
        'approved_by_office_head_id',
        'approved_by_admin_id',
        'fulfilled_by_id',
        'claimed_by_id',
        'office_head_approval_date',
        'admin_approval_date',
        'fulfilled_date',
        'claimed_date',
        'processed_at',
        'processed_by',
        'department',
        'office_head_notes',
        'priority',
        'claim_slip_number',
        'attachments',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approval_date' => 'datetime',
        'return_date' => 'datetime',
        'needed_date' => 'datetime',
        'office_head_approval_date' => 'datetime',
        'admin_approval_date' => 'datetime',
        'fulfilled_date' => 'datetime',
        'claimed_date' => 'datetime',
        'attachments' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function officeHeadApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_office_head_id');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'approved_by_admin_id');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by_id');
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by_id');
    }

    public function acknowledgment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RequestAcknowledgment::class);
    }

    // Workflow Methods
    public function canBeApprovedByOfficeHead()
    {
        return $this->workflow_status === 'pending';
    }

    public function canBeApprovedByAdmin()
    {
        return $this->workflow_status === 'approved_by_office_head';
    }

    public function canBeFulfilled()
    {
        return $this->workflow_status === 'approved_by_admin';
    }

    public function canBeClaimed()
    {
        return $this->workflow_status === 'fulfilled';
    }

    public function canBeAcknowledgedByRequester()
    {
        return $this->workflow_status === 'fulfilled' && !$this->acknowledgment;
    }

    public function approveByOfficeHead(User $user, ?string $notes = null)
    {
        $this->update([
            'workflow_status' => 'approved_by_office_head',
            'approved_by_office_head_id' => $user->id,
            'office_head_approval_date' => now(),
            'office_head_notes' => $notes,
        ]);
    }

    public function approveByAdmin(User $user)
    {
        $this->update([
            'workflow_status' => 'approved_by_admin',
            'approved_by_admin_id' => $user->id,
            'admin_approval_date' => now(),
        ]);
    }

    public function fulfill(User $user)
    {
        // Generate claim slip number
        $claimSlipNumber = 'CS-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'workflow_status' => 'fulfilled',
            'fulfilled_by_id' => $user->id,
            'fulfilled_date' => now(),
            'claim_slip_number' => $claimSlipNumber,
        ]);

        // Update item stock
        $this->item->current_stock -= $this->quantity;
        $this->item->save();
    }

    public function markAsClaimed(User $user)
    {
        $this->update([
            'workflow_status' => 'claimed',
            'claimed_by_id' => $user->id,
            'claimed_date' => now(),
        ]);
    }

    public function decline(User $user, ?string $reason = null)
    {
        $status = $user->isAdmin() ? 'declined_by_admin' : 'declined_by_office_head';
        
        $this->update([
            'workflow_status' => $status,
            'admin_notes' => $reason,
        ]);
    }

    // Status Helper Methods
    public function isPending()
    {
        return $this->workflow_status === 'pending';
    }

    public function isApprovedByOfficeHead()
    {
        return $this->workflow_status === 'approved_by_office_head';
    }

    public function isApprovedByAdmin()
    {
        return $this->workflow_status === 'approved_by_admin';
    }

    public function isFulfilled()
    {
        return $this->workflow_status === 'fulfilled';
    }

    public function isClaimed()
    {
        return $this->workflow_status === 'claimed';
    }

    public function isDeclined()
    {
        return in_array($this->workflow_status, ['declined_by_office_head', 'declined_by_admin']);
    }

    public function getStatusColorClass()
    {
        return match($this->workflow_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved_by_office_head' => 'bg-blue-100 text-blue-800',
            'approved_by_admin' => 'bg-green-100 text-green-800',
            'fulfilled' => 'bg-purple-100 text-purple-800',
            'claimed' => 'bg-gray-100 text-gray-800',
            'declined_by_office_head', 'declined_by_admin' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusDisplayName()
    {
        return match($this->workflow_status) {
            'pending' => 'Pending Review',
            'approved_by_office_head' => 'Office Head Approved',
            'approved_by_admin' => 'Admin Approved',
            'fulfilled' => 'Ready for Pickup',
            'claimed' => 'Claimed',
            'declined_by_office_head' => 'Declined by Office Head',
            'declined_by_admin' => 'Declined by Admin',
            default => ucfirst(str_replace('_', ' ', $this->workflow_status)),
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
        return $query->where('workflow_status', 'pending');
    }

    public function scopeApprovedByOfficeHead($query)
    {
        return $query->where('workflow_status', 'approved_by_office_head');
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('workflow_status', 'fulfilled');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}

