<?php

namespace App\Observers;

use App\Models\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class RequestObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(Request $request): void
    {
        ActivityLog::log('New supply request submitted')
            ->inLog('request_workflow')
            ->performedOn($request)
            ->withEvent('created')
            ->withProperties([
                'request_id' => $request->id,
                'requester' => $request->user->name,
                'item' => $request->item->name,
                'quantity' => $request->quantity,
                'purpose' => $request->purpose,
                'department' => $request->department,
                'priority' => $request->priority,
                'needed_date' => $request->needed_date?->format('Y-m-d')
            ])
            ->save();
    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(Request $request): void
    {
        $changes = $request->getChanges();
        $original = $request->getOriginal();
        
        // Track workflow changes
        $importantChanges = [];
        $workflowFields = ['status', 'workflow_status', 'quantity_approved'];
        
        foreach ($workflowFields as $field) {
            if (isset($changes[$field])) {
                $importantChanges[$field] = [
                    'from' => $original[$field] ?? null,
                    'to' => $changes[$field]
                ];
            }
        }

        // Generate specific description based on status changes
        $description = 'Request updated';
        
        if (isset($changes['status'])) {
            switch ($changes['status']) {
                case 'approved_by_office_head':
                    $description = 'Request approved by Office Head';
                    break;
                case 'approved_by_admin':
                    $description = 'Request approved by Admin';
                    break;
                case 'fulfilled':
                    $description = 'Request fulfilled and ready for pickup';
                    break;
                case 'claimed':
                    $description = 'Request claimed by requester';
                    break;
                case 'completed':
                    $description = 'Request completed';
                    break;
                case 'declined':
                    $description = 'Request declined';
                    break;
                default:
                    $description = 'Request status changed to ' . $changes['status'];
            }
        }
        
        if (isset($changes['quantity_approved'])) {
            $description .= ' (Approved quantity: ' . $changes['quantity_approved'] . ')';
        }

        ActivityLog::log($description)
            ->inLog('request_workflow')
            ->performedOn($request)
            ->withEvent('updated')
            ->withProperties([
                'request_id' => $request->id,
                'requester' => $request->user->name,
                'item' => $request->item->name,
                'changes' => $importantChanges,
                'approver' => isset($changes['approved_by_admin_id']) ? 'Admin' : 
                             (isset($changes['approved_by_office_head_id']) ? 'Office Head' : null),
                'notes' => $changes['admin_notes'] ?? $changes['office_head_notes'] ?? null
            ])
            ->save();
    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(Request $request): void
    {
        ActivityLog::log('Supply request deleted')
            ->inLog('request_workflow')
            ->performedOn($request)
            ->withEvent('deleted')
            ->withProperties([
                'request_id' => $request->id,
                'requester' => $request->user->name,
                'item' => $request->item->name,
                'quantity' => $request->quantity,
                'final_status' => $request->status,
                'reason' => 'Request deleted by admin'
            ])
            ->save();
    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(Request $request): void
    {
        ActivityLog::log('Supply request restored')
            ->inLog('request_workflow')
            ->performedOn($request)
            ->withEvent('restored')
            ->withProperties([
                'request_id' => $request->id,
                'requester' => $request->user->name,
                'item' => $request->item->name
            ])
            ->save();
    }
}
