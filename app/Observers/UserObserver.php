<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActivityLog::log('Created new user account: ' . $user->name)
            ->inLog('user_management')
            ->performedOn($user)
            ->withEvent('created')
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department
            ])
            ->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        $original = $user->getOriginal();
        
        // Don't log password updates for security
        if (isset($changes['password'])) {
            unset($changes['password']);
        }
        
        // Track important changes
        $importantChanges = [];
        $significantFields = ['name', 'email', 'role', 'department', 'status'];
        
        foreach ($significantFields as $field) {
            if (isset($changes[$field])) {
                $importantChanges[$field] = [
                    'from' => $original[$field] ?? null,
                    'to' => $changes[$field]
                ];
            }
        }

        if (!empty($importantChanges)) {
            $description = 'Updated user profile: ' . $user->name;
            
            // Add specific change descriptions
            if (isset($changes['role'])) {
                $description .= ' (Role: ' . ($original['role'] ?? 'unknown') . ' → ' . $changes['role'] . ')';
            }
            
            if (isset($changes['status'])) {
                $description .= ' (Status: ' . ($original['status'] ?? 'unknown') . ' → ' . $changes['status'] . ')';
            }

            ActivityLog::log($description)
                ->inLog('user_management')
                ->performedOn($user)
                ->withEvent('updated')
                ->withProperties([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'changes' => $importantChanges
                ])
                ->save();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        ActivityLog::log('Deleted user account: ' . $user->name)
            ->inLog('user_management')
            ->performedOn($user)
            ->withEvent('deleted')
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department
            ])
            ->save();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        ActivityLog::log('Restored user account: ' . $user->name)
            ->inLog('user_management')
            ->performedOn($user)
            ->withEvent('restored')
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email
            ])
            ->save();
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        ActivityLog::log('Permanently deleted user account: ' . $user->name)
            ->inLog('user_management')
            ->performedOn($user)
            ->withEvent('force_deleted')
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'warning' => 'This action cannot be undone'
            ])
            ->save();
    }
}
