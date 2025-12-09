<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\ItemScanLog;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Lookup item by code (for barcode/QR scanning)
     */
    public function lookup(Request $request, string $code): JsonResponse
    {
        // Debug: Log the received code
        Log::info('Item lookup request received', [
            'code' => $code,
            'user_id' => Auth::id(),
            'headers' => $request->headers->all()
        ]);

        try {
            $item = null;

            // Search for item by product_code in both consumables and non_consumables
            $item = Consumable::with(['category'])
                ->where('product_code', $code)
                ->first();

            Log::info('Consumable search result', [
                'code' => $code,
                'found_consumable' => $item ? true : false,
                'item_name' => $item ? $item->name : null
            ]);

            // If not found in consumables, try non_consumables
            if (!$item) {
                $item = NonConsumable::with(['category'])
                    ->where('product_code', $code)
                    ->first();

                Log::info('Non-consumable search result', [
                    'code' => $code,
                    'found_non_consumable' => $item ? true : false,
                    'item_name' => $item ? $item->name : null
                ]);
            }

            if ($item) {
                Log::info('Item found successfully', [
                    'code' => $code,
                    'item_id' => $item->id,
                    'item_name' => $item->name
                ]);

                // Log the scan ONLY for non-consumable items (for custodianship tracking)
                if ($item instanceof NonConsumable) {
                    ItemScanLog::create([
                        'item_id' => $item->id,
                        'user_id' => Auth::id(),
                        'action' => 'scanned',
                        'metadata' => [
                            'scanned_at' => now(),
                            'scanner' => 'dashboard',
                            'ip_address' => $request->ip(),
                            'qr_data' => $code
                        ]
                    ]);

                    // Log QR scan activity
                    ActivityLogger::logQrScan($item, null, [
                        'scanner' => 'dashboard',
                        'qr_data' => $code
                    ]);
                }

                // Determine item type
                $itemType = $item instanceof Consumable ? 'consumable' : 'non_consumable';

                return response()->json([
                    'success' => true,
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->product_code,
                        'product_code' => $item->product_code,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'current_stock' => $item->quantity, // Use quantity as current_stock for consistency
                        'unit' => 'pieces', // Default unit
                        'brand' => $item->brand,
                        'min_stock' => $item->min_stock,
                        'max_stock' => $item->max_stock,
                        'category' => $item->category,
                        'item_type' => $itemType,
                        // Non-consumable specific fields
                        'location' => $item instanceof NonConsumable ? $item->location : null,
                        'condition' => $item instanceof NonConsumable ? $item->condition : null,
                        'current_holder' => $item instanceof NonConsumable && $item->currentHolder ? [
                            'id' => $item->currentHolder->id,
                            'name' => $item->currentHolder->name,
                            'email' => $item->currentHolder->email,
                        ] : null,
                        'is_assigned' => $item instanceof NonConsumable ? ($item->current_holder_id !== null) : false,
                        'stock_status' => $item->quantity <= $item->min_stock ? 'low' : 'normal',
                        'stock_percentage' => $item->min_stock > 0 ? min(100, ($item->quantity / $item->min_stock) * 100) : 100,
                        'status' => 'active'
                    ]
                ]);
            }

            Log::warning('Item not found', [
                'code' => $code,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Item not found',
                'code' => $code
            ], 404);

        } catch (\Exception $e) {
            Log::error('Item lookup error: ' . $e->getMessage(), [
                'code' => $code,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error looking up item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify item by barcode (for request completion)
     */
    public function verifyBarcode(Request $request, string $barcode): JsonResponse
    {
        try {
            $item = null;

            // Search for item by product_code in both consumables and non_consumables
            $item = Consumable::with(['category'])
                ->where('product_code', $barcode)
                ->first();

            // If not found in consumables, try non_consumables
            if (!$item) {
                $item = NonConsumable::with(['category'])
                    ->where('product_code', $barcode)
                    ->first();
            }

            if ($item) {
                // Determine item type
                $itemType = $item instanceof Consumable ? 'consumable' : 'non_consumable';

                return response()->json([
                    'success' => true,
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'product_code' => $item->product_code,
                        'description' => $item->description,
                        'current_stock' => $item->quantity,
                        'quantity' => $item->quantity,
                        'unit' => 'pieces', // Default unit
                        'brand' => $item->brand,
                        'min_stock' => $item->min_stock,
                        'max_stock' => $item->max_stock,
                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ] : null,
                        'item_type' => $itemType,
                        // Non-consumable specific fields
                        'location' => $item instanceof NonConsumable ? $item->location : null,
                        'condition' => $item instanceof NonConsumable ? $item->condition : null,
                        'stock_status' => $item->quantity <= $item->min_stock ? 'low' : 'normal',
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Item not found',
                'barcode' => $barcode
            ], 404);

        } catch (\Exception $e) {
            Log::error('Item verification error: ' . $e->getMessage(), [
                'barcode' => $barcode,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error verifying item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}