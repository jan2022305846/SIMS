<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Request as SupplyRequest;
use App\Models\Consumable;
use App\Models\NonConsumable;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public static function create(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Notify admin about new pending request.
     */
    public static function notifyNewPendingRequest(SupplyRequest $request): void
    {
        $admins = User::whereRaw('id = 6')->get(); // Single admin system - user ID 6

        foreach ($admins as $admin) {
            // Only create notification if the request has an associated user
            $userName = $request->user ? $request->user->name : 'Unknown User';
            self::create(
                $admin,
                'pending_request',
                'New Pending Request',
                "New request from {$userName} for {$request->quantity} {$request->item->name}",
                [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'item_id' => $request->item_id,
                    'item_type' => $request->item_type,
                    'quantity' => $request->quantity,
                ]
            );
        }
    }

    /**
     * Notify admin about low stock items.
     */
    public static function notifyLowStockAlerts(): void
    {
        $admins = User::whereRaw('id = 6')->get(); // Single admin system - user ID 6

        // Check consumables
        $lowStockConsumables = Consumable::whereColumn('quantity', '<=', 'min_stock')->get();
        foreach ($lowStockConsumables as $item) {
            foreach ($admins as $admin) {
                self::create(
                    $admin,
                    'low_stock',
                    'Low Stock Alert',
                    "{$item->name} is running low (Current: {$item->quantity}, Minimum: {$item->min_stock})",
                    [
                        'item_id' => $item->id,
                        'item_type' => 'consumable',
                        'current_stock' => $item->quantity,
                        'min_stock' => $item->min_stock,
                    ]
                );
            }
        }

        // Check non-consumables
        $lowStockNonConsumables = NonConsumable::whereColumn('quantity', '<=', 'min_stock')->get();
        foreach ($lowStockNonConsumables as $item) {
            foreach ($admins as $admin) {
                self::create(
                    $admin,
                    'low_stock',
                    'Low Stock Alert',
                    "{$item->name} is running low (Current: {$item->quantity}, Minimum: {$item->min_stock})",
                    [
                        'item_id' => $item->id,
                        'item_type' => 'non_consumable',
                        'current_stock' => $item->quantity,
                        'min_stock' => $item->min_stock,
                    ]
                );
            }
        }
    }

    /**
     * Notify faculty when request is approved.
     */
    public static function notifyRequestApproved(SupplyRequest $request): void
    {
        // Only create notification if the request has an associated user
        if ($request->user) {
            self::create(
                $request->user,
                'approved',
                'Request Approved',
                "Your request for {$request->quantity} {$request->item->name} has been approved",
                [
                    'request_id' => $request->id,
                    'item_id' => $request->item_id,
                    'item_type' => $request->item_type,
                    'quantity' => $request->quantity,
                ]
            );

            // Send email notification
            $request->user->notify(new \App\Notifications\RequestApproved($request));
        }
    }

    /**
     * Notify faculty when request is marked as claimed.
     */
    public static function notifyRequestClaimed(SupplyRequest $request): void
    {
        // Only create notification if the request has an associated user
        if ($request->user) {
            self::create(
                $request->user,
                'claimed',
                'Request Claimed',
                "Your request for {$request->quantity} {$request->item->name} has been marked as claimed",
                [
                    'request_id' => $request->id,
                    'item_id' => $request->item_id,
                    'item_type' => $request->item_type,
                    'quantity' => $request->quantity,
                    'claim_slip_number' => $request->claim_slip_number,
                ]
            );

            // Send email notification
            $request->user->notify(new \App\Notifications\RequestClaimed($request));
        }
    }

    /**
     * Notify faculty when request is declined.
     */
    public static function notifyRequestDeclined(SupplyRequest $request, string $reason): void
    {
        // Only create notification if the request has an associated user
        if ($request->user) {
            self::create(
                $request->user,
                'declined',
                'Request Declined',
                "Your request for {$request->quantity} {$request->item->name} has been declined. Reason: {$reason}",
                [
                    'request_id' => $request->id,
                    'item_id' => $request->item_id,
                    'item_type' => $request->item_type,
                    'quantity' => $request->quantity,
                    'reason' => $reason,
                ]
            );

            // Send email notification
            $request->user->notify(new \App\Notifications\RequestDeclined($request, $reason));
        }
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Mark a specific notification as read.
     */
    public static function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Get unread notifications count for a user.
     */
    public static function getUnreadCount(User $user): int
    {
        return $user->unreadNotificationsCount();
    }
}