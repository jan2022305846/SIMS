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

            return response()->json([
                'success' => true,
                'item' => $item->load('category'),
                'redirect' => route('items.show', $item->id),
                'scan_logged' => true
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
}
