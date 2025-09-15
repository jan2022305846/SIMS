<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ItemObserver
{
    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        ActivityLog::log('Created new item: ' . $item->name)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent('created')
            ->withProperties([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'category' => $item->category->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'location' => $item->location
            ])
            ->save();
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        $changes = $item->getChanges();
        $original = $item->getOriginal();
        
        // Track specific important changes
        $importantChanges = [];
        $significantFields = ['quantity', 'unit_cost', 'location', 'expiry_date', 'status'];
        
        foreach ($significantFields as $field) {
            if (isset($changes[$field])) {
                $importantChanges[$field] = [
                    'from' => $original[$field] ?? null,
                    'to' => $changes[$field]
                ];
            }
        }

        $description = 'Updated item: ' . $item->name;
        
        // Add specific change descriptions
        if (isset($changes['quantity'])) {
            $description .= ' (Stock: ' . ($original['quantity'] ?? 0) . ' → ' . $changes['quantity'] . ')';
        }
        
        if (isset($changes['status'])) {
            $description .= ' (Status: ' . ($original['status'] ?? 'unknown') . ' → ' . $changes['status'] . ')';
        }

        ActivityLog::log($description)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent('updated')
            ->withProperties([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'changes' => $importantChanges,
                'all_changes' => $changes
            ])
            ->save();
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        ActivityLog::log('Deleted item: ' . $item->name)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent('deleted')
            ->withProperties([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'category' => $item->category->name ?? 'Unknown',
                'final_quantity' => $item->quantity,
                'final_location' => $item->location
            ])
            ->save();
    }

    /**
     * Handle the Item "restored" event.
     */
    public function restored(Item $item): void
    {
        ActivityLog::log('Restored deleted item: ' . $item->name)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent('restored')
            ->withProperties([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'category' => $item->category->name ?? 'Unknown'
            ])
            ->save();
    }

    /**
     * Handle the Item "force deleted" event.
     */
    public function forceDeleted(Item $item): void
    {
        ActivityLog::log('Permanently deleted item: ' . $item->name)
            ->inLog('item_management')
            ->performedOn($item)
            ->withEvent('force_deleted')
            ->withProperties([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'warning' => 'This action cannot be undone'
            ])
            ->save();
    }
}
