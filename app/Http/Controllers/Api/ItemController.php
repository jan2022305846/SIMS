<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
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

            // Try to find item by barcode field first (for physical barcodes/QR codes)
            $item = Item::with(['category'])
                ->where('barcode', $code)
                ->first();

            Log::info('Barcode search result', [
                'code' => $code,
                'found_by_barcode' => $item ? true : false,
                'item_name' => $item ? $item->name : null
            ]);

            // If not found by barcode, try qr_code field (for system-generated codes)
            if (!$item && Schema::hasColumn('items', 'qr_code')) {
                $item = Item::with(['category'])
                    ->where('qr_code', $code)
                    ->first();

                Log::info('QR code search result', [
                    'code' => $code,
                    'found_by_qr_code' => $item ? true : false,
                    'item_name' => $item ? $item->name : null
                ]);
            }

            // If still not found, try item_code field for backward compatibility
            if (!$item && Schema::hasColumn('items', 'item_code')) {
                $item = Item::with(['category'])
                    ->where('item_code', $code)
                    ->first();

                Log::info('Item code search result', [
                    'code' => $code,
                    'found_by_item_code' => $item ? true : false,
                    'item_name' => $item ? $item->name : null
                ]);
            }

            if ($item) {
                Log::info('Item found successfully', [
                    'code' => $code,
                    'item_id' => $item->id,
                    'item_name' => $item->name
                ]);

                return response()->json([
                    'success' => true,
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->qr_code, // Return qr_code as item_code for consistency
                        'barcode' => $item->barcode,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'current_stock' => $item->current_stock,
                        'unit' => $item->unit,
                        'location' => $item->location,
                        'condition' => $item->condition,
                        'brand' => $item->brand,
                        'supplier' => $item->supplier,
                        'warranty_date' => $item->warranty_date?->format('Y-m-d'),
                        'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                        'category' => $item->category,
                        'current_holder' => $item->currentHolder ? [
                            'id' => $item->currentHolder->id,
                            'name' => $item->currentHolder->name,
                            'email' => $item->currentHolder->email,
                        ] : null,
                        'assigned_at' => $item->assigned_at?->format('Y-m-d H:i:s'),
                        'assignment_notes' => $item->assignment_notes,
                        'is_assigned' => $item->isAssigned(),
                        'stock_status' => $item->getStockStatus(),
                        'stock_percentage' => $item->getStockPercentage(),
                        'status' => $item->status
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

            // Try to find item by barcode field first
            $item = Item::with(['category'])
                ->where('barcode', $barcode)
                ->first();

            // If not found by barcode, try qr_code field
            if (!$item && Schema::hasColumn('items', 'qr_code')) {
                $item = Item::with(['category'])
                    ->where('qr_code', $barcode)
                    ->first();
            }

            // If still not found, try item_code field
            if (!$item && Schema::hasColumn('items', 'item_code')) {
                $item = Item::with(['category'])
                    ->where('item_code', $barcode)
                    ->first();
            }

            if ($item) {
                return response()->json([
                    'success' => true,
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'barcode' => $item->barcode,
                        'qr_code' => $item->qr_code ?? null,
                        'item_code' => $item->item_code ?? null,
                        'description' => $item->description,
                        'current_stock' => $item->current_stock,
                        'unit' => $item->unit,
                        'location' => $item->location,
                        'condition' => $item->condition,
                        'brand' => $item->brand,
                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ] : null,
                        'minimum_stock' => $item->minimum_stock,
                        'stock_status' => $item->getStockStatus(),
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