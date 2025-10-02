<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\NonConsumable;
use App\Models\ItemScanLog;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class QRCodeController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display QR code scanner interface
     */
    public function scanner(): View
    {
        return view('qr.scanner');
    }

    /**
     * Display QR code test page
     */
    public function test(): View
    {
        // Get some sample items for testing from both consumables and non-consumables
        $consumables = Consumable::with('category')->take(3)->get();
        $nonConsumables = NonConsumable::with('category')->take(2)->get();
        $items = $consumables->merge($nonConsumables);
        return view('qr.test', compact('items'));
    }

    /**
     * Simple QR scanner test page
     */
    public function simpleTest(): View
    {
        return view('qr.simple-test');
    }

    /**
     * Generate QR code for an item
     */
    public function generate($id): JsonResponse
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        try {
            $qrCodeDataUrl = $item->getQRCodeDataUrl();

            // Update the item's QR code data
            $qrData = [
                'type' => 'item',
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->product_code,
                'url' => route('items.show', $item->id)
            ];

            $item->qr_code_data = json_encode($qrData);
            $item->save();

            return response()->json([
                'success' => true,
                'qr_code' => $qrCodeDataUrl,
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process scanned QR code data
     */
    public function scan(Request $request): JsonResponse
    {
        try {
            $qrData = $request->input('qr_data');
            
            $parsedData = $this->qrCodeService->parseQRCode($qrData);
            
            if (!$parsedData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            // Try to find the item in consumables first
            $item = Consumable::find($parsedData['id']);

            // If not found in consumables, try non-consumables
            if (!$item) {
                $item = NonConsumable::find($parsedData['id']);
                $itemType = 'non_consumable';
            } else {
                $itemType = 'consumable';
            }
            
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Log the scan in item_scan_logs table (as per ERD requirement)
            ItemScanLog::logScan($item->id, $itemType, 'inventory_check', [
                'location' => $request->input('location'), // Optional scan location
                'notes' => json_encode([
                    'scanner_type' => 'admin', // As per ERD, only admin can scan
                    'scan_data' => [
                        'raw_qr_data' => $qrData,
                        'parsed_data' => $parsedData,
                        'scan_method' => $request->input('scan_method', 'camera'), // camera or manual
                    ]
                ])
            ]);

            // Prepare enhanced item data with holder and assignment information
            $itemData = $item->load('category', 'currentHolder');
            $enhancedData = [
                'id' => $itemData->id,
                'name' => $itemData->name,
                'description' => $itemData->description,
                'product_code' => $itemData->product_code,
                'category' => $itemData->category?->name,
                'category_type' => $itemData->category?->type,
                'quantity' => $itemData->quantity,
                'brand' => $itemData->brand,
                'location' => $itemData->location ?? 'Supply Office',
                'condition' => $itemData->condition ?? 'Good',
                'is_non_consumable' => $item instanceof NonConsumable,
                'holder' => ($item instanceof NonConsumable && $itemData->currentHolder) ? [
                    'id' => $itemData->currentHolder->id,
                    'name' => $itemData->currentHolder->name,
                    'email' => $itemData->currentHolder->email,
                    'office' => $itemData->currentHolder->office?->name ?? 'Not assigned',
                ] : null,
                'status' => $this->getItemStatus($itemData),
                'last_scan' => $itemData->scanLogs()->latest()->first()?->created_at?->diffForHumans(),
                'total_scans' => $itemData->scanLogs()->count(),
            ];

            return response()->json([
                'success' => true,
                'item' => $enhancedData,
                'redirect' => route('items.show', $item->id),
                'scan_logged' => true,
                'message' => $this->getScanMessage($itemData)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download QR code as image
     */
    public function download($id)
    {
        // Try to find the item in consumables first
        $item = Consumable::find($id);

        // If not found in consumables, try non-consumables
        if (!$item) {
            $item = NonConsumable::find($id);
        }

        // If still not found, return 404
        if (!$item) {
            return back()->with('error', 'Item not found');
        }

        try {
            $qrCodeBase64 = $item->generateQRCode();
            $qrCodeSvg = base64_decode($qrCodeBase64);

            $filename = 'qr-code-' . $item->id . '-' . Str::slug($item->name) . '.svg';

            return response($qrCodeSvg)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download QR code: ' . $e->getMessage());
        }
    }

    /**
     * Get comprehensive item status
     */
    private function getItemStatus($item): array
    {
        $status = [];

        // Stock status - different logic for consumables vs non-consumables
        if (method_exists($item, 'isOutOfStock')) {
            if ($item->isOutOfStock()) {
                $status['stock'] = 'Out of Stock';
                $status['stock_class'] = 'danger';
            } elseif (method_exists($item, 'isLowStock') && $item->isLowStock()) {
                $status['stock'] = 'Low Stock';
                $status['stock_class'] = 'warning';
            } else {
                $status['stock'] = 'In Stock';
                $status['stock_class'] = 'success';
            }
        } else {
            // For non-consumables, check quantity
            if ($item->quantity <= 0) {
                $status['stock'] = 'Out of Stock';
                $status['stock_class'] = 'danger';
            } elseif ($item->quantity <= $item->min_stock) {
                $status['stock'] = 'Low Stock';
                $status['stock_class'] = 'warning';
            } else {
                $status['stock'] = 'In Stock';
                $status['stock_class'] = 'success';
            }
        }

        // Assignment status - only for non-consumables
        if ($item instanceof NonConsumable) {
            if ($item->current_holder_id) {
                $status['assignment'] = 'Assigned';
                $status['assignment_class'] = 'info';
            } else {
                $status['assignment'] = 'Available';
                $status['assignment_class'] = 'success';
            }
        } else {
            $status['assignment'] = 'N/A';
            $status['assignment_class'] = 'secondary';
        }

        // Condition status
        switch ($item->condition ?? 'Good') {
            case 'New':
                $status['condition'] = 'New';
                $status['condition_class'] = 'success';
                break;
            case 'Good':
                $status['condition'] = 'Good';
                $status['condition_class'] = 'primary';
                break;
            case 'Fair':
                $status['condition'] = 'Fair';
                $status['condition_class'] = 'warning';
                break;
            case 'Poor':
                $status['condition'] = 'Poor';
                $status['condition_class'] = 'danger';
                break;
            default:
                $status['condition'] = $item->condition ?? 'Good';
                $status['condition_class'] = 'secondary';
        }

        // Overall status
        if ($item instanceof NonConsumable && !$item->current_holder_id) {
            $status['overall'] = 'Available';
            $status['overall_class'] = 'success';
        } elseif ($item instanceof NonConsumable && $item->current_holder_id) {
            $status['overall'] = 'In Use';
            $status['overall_class'] = 'info';
        } else {
            $status['overall'] = 'Consumable';
            $status['overall_class'] = 'primary';
        }

        return $status;
    }

    /**
     * Get scan result message
     */
    private function getScanMessage($item): string
    {
        if ($item instanceof NonConsumable) {
            if ($item->current_holder_id) {
                return "Non-consumable item scanned. Currently assigned at {$item->location}.";
            } else {
                return "Non-consumable item scanned. Available for assignment at {$item->location}.";
            }
        } else {
            return "Consumable item scanned. {$item->quantity} remaining in stock.";
        }
    }
}
