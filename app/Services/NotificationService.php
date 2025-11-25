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
            
            // Get item names for the notification
            $itemNames = self::getItemNamesForRequest($request);
            $itemSummary = count($itemNames) === 1 ? $itemNames[0] : count($itemNames) . ' items';
            
            self::create(
                $admin,
                'pending_request',
                'New Pending Request',
                "New request from {$userName} for {$itemSummary}",
                [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'item_names' => $itemNames,
                    'total_quantity' => $request->getTotalItems(),
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
            // Get item names for the notification
            $itemNames = self::getItemNamesForRequest($request);
            $itemSummary = count($itemNames) === 1 ? $itemNames[0] : count($itemNames) . ' items';
            
            self::create(
                $request->user,
                'approved',
                'Request Approved',
                "Your request for {$itemSummary} has been approved",
                [
                    'request_id' => $request->id,
                    'item_names' => $itemNames,
                    'total_quantity' => $request->getTotalItems(),
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
            // Get item names for the notification
            $itemNames = self::getItemNamesForRequest($request);
            $itemSummary = count($itemNames) === 1 ? $itemNames[0] : count($itemNames) . ' items';
            
            self::create(
                $request->user,
                'claimed',
                'Request Claimed',
                "Your request for {$itemSummary} has been marked as claimed",
                [
                    'request_id' => $request->id,
                    'item_names' => $itemNames,
                    'total_quantity' => $request->getTotalItems(),
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
            // Get item names for the notification
            $itemNames = self::getItemNamesForRequest($request);
            $itemSummary = count($itemNames) === 1 ? $itemNames[0] : count($itemNames) . ' items';
            
            self::create(
                $request->user,
                'declined',
                'Request Declined',
                "Your request for {$itemSummary} has been declined. Reason: {$reason}",
                [
                    'request_id' => $request->id,
                    'item_names' => $itemNames,
                    'total_quantity' => $request->getTotalItems(),
                    'reason' => $reason,
                ]
            );

            // Send email notification
            $request->user->notify(new \App\Notifications\RequestDeclined($request, $reason));
        }
    }

    /**
     * Get item names for a request (handles both single and bulk requests)
     */
    private static function getItemNamesForRequest(SupplyRequest $request): array
    {
        // Ensure requestItems are loaded with itemable relationships
        if (!$request->relationLoaded('requestItems')) {
            $request->load('requestItems');
        }

        // Manually load itemable relationships for each request item
        foreach ($request->requestItems as $requestItem) {
            if (!$requestItem->relationLoaded('itemable')) {
                if ($requestItem->item_type === 'consumable') {
                    $itemable = \App\Models\Consumable::find($requestItem->item_id);
                } elseif ($requestItem->item_type === 'non_consumable') {
                    $itemable = \App\Models\NonConsumable::find($requestItem->item_id);
                } else {
                    $itemable = null;
                }
                $requestItem->setRelation('itemable', $itemable);
            }
        }

        $itemNames = [];
        foreach ($request->requestItems as $requestItem) {
            $itemName = $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item';
            $itemNames[] = $itemName;
        }

        return $itemNames;
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