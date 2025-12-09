<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

/**
 * Service for generating QR codes for inventory items
 */
class QRCodeService
{
    /**
     * Generate QR code for an item
     *
     * @param int $itemId The item ID
     * @param string $itemName The item name
     * @param string $itemType The item type ('consumable' or 'non_consumable')
     * @param string|null $itemCode The item code/barcode
     * @return string Base64 encoded QR code SVG
     */
    public function generateItemQRCode(int $itemId, string $itemName, string $itemType, ?string $itemCode = null): string
    {
        $options = new QROptions([
            'version'    => 8,  // Increased to version 8 for more capacity
            'outputType' => 'svg',
            'eccLevel'   => EccLevel::L,
            'scale'      => 10,
            'imageBase64' => false, // Get raw SVG XML instead of data URL
        ]);

        // Create QR code data with item information
        $qrData = [
            'type' => 'item',
            'id' => $itemId,
            'item_type' => $itemType,
            'name' => $itemName,
            'code' => $itemCode,
            'url' => config('app.url', '') . '/items/' . $itemId . '?type=' . $itemType
        ];

        $qrcode = new QRCode($options);
        $qrCodeSvg = $qrcode->render(json_encode($qrData));

        return base64_encode($qrCodeSvg);
    }

    /**
     * Generate QR code URL for an item
     *
     * @param int $itemId The item ID
     * @param string $itemName The item name
     * @param string $itemType The item type ('consumable' or 'non_consumable')
     * @param string|null $itemCode The item code/barcode
     * @return string QR code data URL
     */
    public function getItemQRCodeDataUrl(int $itemId, string $itemName, string $itemType, ?string $itemCode = null): string
    {
        $base64 = $this->generateItemQRCode($itemId, $itemName, $itemType, $itemCode);
        return 'data:image/svg+xml;base64,' . $base64;
    }

    /**
     * Parse QR code data
     *
     * @param string $qrData JSON string from QR code
     * @return array|null Parsed data or null if invalid
     */
    public function parseQRCode(string $qrData): ?array
    {
        try {
            // First try to parse as JSON
            $data = json_decode($qrData, true);
            
            if (is_array($data) && isset($data['type']) && $data['type'] === 'item') {
                return $data;
            }

            // If JSON parsing failed or didn't contain valid item data,
            // try to parse as URL
            if (filter_var($qrData, FILTER_VALIDATE_URL)) {
                return $this->parseItemUrl($qrData);
            }

            // If not JSON or URL, try to parse as product code
            return $this->parseProductCode($qrData);
        } catch (\Exception $e) {
            // If JSON parsing failed, try URL parsing
            if (filter_var($qrData, FILTER_VALIDATE_URL)) {
                return $this->parseItemUrl($qrData);
            }
            
            // If not a URL, try product code parsing
            return $this->parseProductCode($qrData);
        }
    }

    /**
     * Parse an item URL to extract item data
     *
     * @param string $url The item URL
     * @return array|null Parsed item data or null if invalid
     */
    private function parseItemUrl(string $url): ?array
    {
        // Parse the URL
        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['path'])) {
            return null;
        }

        // Check if it's an item path
        $path = $parsedUrl['path'];
        if (!preg_match('#^/admin/items/(\d+)$#', $path, $matches)) {
            return null;
        }

        $itemId = (int) $matches[1];
        
        // Parse query parameters
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        $itemType = $queryParams['type'] ?? null;
        
        // Validate item type - must be provided for URL parsing
        if (!in_array($itemType, ['consumable', 'non_consumable'])) {
            return null; // Type must be specified in URL
        }

        return [
            'type' => 'item',
            'id' => $itemId,
            'item_type' => $itemType,
            'name' => '', // Will be filled by the controller
            'code' => '', // Will be filled by the controller
            'url' => $url
        ];
    }

    /**
     * Parse a product code to find the corresponding item
     *
     * @param string $productCode The product code
     * @return array|null Parsed item data or null if not found
     */
    private function parseProductCode(string $productCode): ?array
    {
        // Trim whitespace and validate product code format
        $productCode = trim($productCode);
        if (empty($productCode)) {
            return null;
        }

        // Look for item by product code in both tables
        $consumable = \App\Models\Consumable::where('product_code', $productCode)->first();
        $nonConsumable = \App\Models\NonConsumable::where('product_code', $productCode)->first();

        // If found in both tables, prioritize non-consumable (more specific)
        $item = $nonConsumable ?? $consumable;

        if (!$item) {
            return null;
        }

        $itemType = $item instanceof \App\Models\NonConsumable ? 'non_consumable' : 'consumable';

        return [
            'type' => 'item',
            'id' => $item->id,
            'item_type' => $itemType,
            'name' => $item->name,
            'code' => $item->product_code,
            'url' => route('items.show', [$item->id, 'type' => $itemType])
        ];
    }
}
