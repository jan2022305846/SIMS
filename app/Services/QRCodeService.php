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
     * @param string $itemCode The item code/barcode
     * @return string Base64 encoded QR code SVG
     */
    public function generateItemQRCode(int $itemId, string $itemName, ?string $itemCode = null): string
    {
        $options = new QROptions([
            'version'    => 5,
            'outputType' => 'svg',
            'eccLevel'   => EccLevel::L,
            'scale'      => 10,
        ]);

        // Create QR code data with item information
        $qrData = [
            'type' => 'item',
            'id' => $itemId,
            'name' => $itemName,
            'code' => $itemCode,
            'url' => config('app.url', '') . '/items/' . $itemId
        ];

        $qrcode = new QRCode($options);
        $qrCodeSvg = $qrcode->render(json_encode($qrData));

        return base64_encode($qrCodeSvg);
    }

    /**
     * Generate QR code URL for an item
     *
     * @param int $itemId The item ID
     * @return string QR code data URL
     */
    public function getItemQRCodeDataUrl(int $itemId, string $itemName, ?string $itemCode = null): string
    {
        $base64 = $this->generateItemQRCode($itemId, $itemName, $itemCode);
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
            $data = json_decode($qrData, true);
            
            if (!is_array($data) || !isset($data['type']) || $data['type'] !== 'item') {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
