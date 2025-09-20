<?php

namespace App\Http\Controllers;

use App\Models\Item;
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
        // Get some sample items for testing
        $items = Item::with('category')->take(5)->get();
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
    public function generate(Item $item): JsonResponse
    {
        try {
            $qrCodeDataUrl = $item->getQRCodeDataUrl();
            
            // Update the item's QR code data
            $qrData = [
                'type' => 'item',
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->barcode,
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

            $item = Item::find($parsedData['id']);
            
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Log the scan in item_scan_logs table (as per ERD requirement)
            ItemScanLog::logScan($item, [
                'location' => $request->input('location'), // Optional scan location
                'scanner_type' => 'admin', // As per ERD, only admin can scan
                'scan_data' => [
                    'raw_qr_data' => $qrData,
                    'parsed_data' => $parsedData,
                    'scan_method' => $request->input('scan_method', 'camera'), // camera or manual
                ]
            ]);

            // Prepare enhanced item data with holder and assignment information
            $itemData = $item->load('category', 'currentHolder');
            $enhancedData = [
                'id' => $itemData->id,
                'name' => $itemData->name,
                'description' => $itemData->description,
                'barcode' => $itemData->barcode,
                'category' => $itemData->category?->name,
                'category_type' => $itemData->category?->type,
                'current_stock' => $itemData->current_stock,
                'unit' => $itemData->unit,
                'location' => $itemData->location,
                'condition' => $itemData->condition,
                'is_non_consumable' => $itemData->category?->type === 'non-consumable',
                'holder' => $itemData->currentHolder ? [
                    'id' => $itemData->currentHolder->id,
                    'name' => $itemData->currentHolder->name,
                    'email' => $itemData->currentHolder->email,
                    'office' => $itemData->currentHolder->office?->name ?? 'Not assigned',
                    'role' => $itemData->currentHolder->role,
                ] : null,
                'assignment' => $itemData->isAssigned() ? [
                    'assigned_at' => $itemData->assigned_at?->format('M d, Y H:i'),
                    'assigned_at_human' => $itemData->assigned_at?->diffForHumans(),
                    'notes' => $itemData->assignment_notes,
                ] : null,
                'status' => $this->getItemStatus($itemData),
                'last_scan' => $itemData->scanLogs()->latest()->first()?->scanned_at?->diffForHumans(),
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
    public function download(Item $item)
    {
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
    private function getItemStatus(Item $item): array
    {
        $status = [];

        // Stock status
        if ($item->isOutOfStock()) {
            $status['stock'] = 'Out of Stock';
            $status['stock_class'] = 'danger';
        } elseif ($item->isLowStock()) {
            $status['stock'] = 'Low Stock';
            $status['stock_class'] = 'warning';
        } else {
            $status['stock'] = 'In Stock';
            $status['stock_class'] = 'success';
        }

        // Assignment status
        if ($item->isAssigned()) {
            $status['assignment'] = 'Assigned';
            $status['assignment_class'] = 'info';
        } else {
            $status['assignment'] = 'Available';
            $status['assignment_class'] = 'success';
        }

        // Condition status
        switch ($item->condition) {
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
                $status['condition'] = $item->condition;
                $status['condition_class'] = 'secondary';
        }

        // Overall status
        if ($item->isOutOfStock()) {
            $status['overall'] = 'Unavailable';
            $status['overall_class'] = 'danger';
        } elseif ($item->isAssigned()) {
            $status['overall'] = 'In Use';
            $status['overall_class'] = 'info';
        } else {
            $status['overall'] = 'Available';
            $status['overall_class'] = 'success';
        }

        return $status;
    }

    /**
     * Get scan result message
     */
    private function getScanMessage(Item $item): string
    {
        if ($item->category?->type === 'non-consumable') {
            if ($item->isAssigned()) {
                return "Non-consumable item scanned. Currently assigned to {$item->currentHolder->name} at {$item->location}.";
            } else {
                return "Non-consumable item scanned. Available for assignment at {$item->location}.";
            }
        } else {
            return "Item scanned successfully. {$item->current_stock} {$item->unit} remaining in stock.";
        }
    }
}
