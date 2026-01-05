<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Slip - {{ $request->claim_slip_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 10px;
            background: #f8f9fa;
            font-size: 12px;
            line-height: 1.3;
            min-height: 100vh;
        }

        .claim-slip {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 2px solid #000;
            min-height: calc(100vh - 20px);
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
            padding: 0 15px;
        }

        .header-left {
            flex-shrink: 0;
        }

        .header-right {
            text-align: right;
            flex: 1;
        }

        .logo {
            margin-bottom: 8px;
        }

        .logo img {
            max-height: 75px;
            max-width: 250px;
            object-fit: contain;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .claim-number {
            font-size: 14px;
            color: #6c757d;
            font-weight: 600;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .info-item {
            display: flex;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 80px;
            margin-right: 8px;
            font-size: 11px;
        }

        .info-value {
            color: #212529;
            flex: 1;
            font-size: 11px;
        }

        .item-details {
            background: #f8f8f8;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #000;
            margin: 8px 0;
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
            max-height: none;
        }

        .item-entry {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .item-name-compact {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .quantity-compact {
            font-size: 12px;
            background: #000;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .quantity-box {
            background: #000;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            margin: 5px 0;
        }

        .purpose-section {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 6px;
            border-left: 3px solid #000;
            font-size: 11px;
        }

        .item-qr-container {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .item-section {
            flex: 1;
        }

        .qr-section {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: #f8f8f8;
            border-radius: 6px;
            border: 2px solid #000;
        }

        .qr-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .qr-code {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            border: 1px solid #000;
            border-radius: 4px;
        }

        .qr-note {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
            font-style: italic;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 3px;
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-fulfilled {
            background: #fff;
            color: #000;
            border: 2px solid #000;
        }

        .status-claimed {
            background: #000;
            color: #fff;
            border: 2px solid #000;
        }

        .important-notes {
            background: #f8f8f8;
            border: 2px solid #000;
            border-radius: 4px;
            padding: 8px;
            margin-top: 10px;
            font-size: 10px;
        }

        .spacer-footer-container {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-top: 20px;
        }

        .notes-title {
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
        }

        .notes-list {
            margin: 0;
            padding-left: 12px;
        }

        .notes-list li {
            margin-bottom: 2px;
        }

        @media print {
            body {
                background: white;
                padding: 5px;
                min-height: 100vh;
                margin: 0;
            }

            .claim-slip {
                box-shadow: none;
                border: 1px solid #000;
                margin: 0;
                padding: 15px;
                font-size: 11px;
                page-break-inside: avoid;
                min-height: calc(100vh - 10px);
                display: flex;
                flex-direction: column;
            }

            .spacer {
                flex: 1;
                min-height: 20px;
                margin: 0;
                padding: 0;
            }

            .footer {
                margin: 0;
                padding-top: 10px;
                border-top: 1px solid #e9ecef;
                text-align: center;
                color: #6c757d;
                font-size: 10px;
                page-break-inside: avoid;
                flex-shrink: 0;
            }

            .no-print {
                display: none !important;
            }

            .qr-code {
                width: 80px;
                height: 80px;
            }

            .logo img {
                max-height: 65px;
                max-width: 200px;
            }

            .section {
                page-break-inside: avoid;
                margin-bottom: 8px;
            }

            .item-list {
                page-break-inside: avoid;
            }

            .item-qr-container {
                page-break-inside: avoid;
            }

            .signatures {
                page-break-inside: avoid;
                margin-top: 15px;
            }

            .important-notes {
                page-break-inside: avoid;
                margin-top: 10px;
            }

            /* Force single page */
            .claim-slip,
            .claim-slip * {
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }

            /* Hide browser-added content like URLs and dates */
            @page {
                margin: 0.5in;
                size: letter;
                @top-center { content: none; }
                @bottom-center { content: none; }
                @top-left { content: none; }
                @top-right { content: none; }
                @bottom-left { content: none; }
                @bottom-right { content: none; }
            }

            /* Hide any browser-generated headers/footers */
            html, body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure no content breaks */
            * {
                box-sizing: border-box;
            }
        }

        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background: #333;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Claim Slip
    </button>

    <div class="claim-slip">
        <div class="header">
            <div class="header-left">
                <div class="logo">
                    <img src="{{ asset('logos/USTP Logo against Light Background.png') }}" alt="USTP Logo">
                </div>
            </div>
            <div class="header-right">
                <div class="title">SIMS Official Claim Slip</div>
                <div class="claim-number">{{ $request->claim_slip_number }}</div>
                <div style="margin-top: 5px;">
                    <span class="status-badge {{ $request->isClaimed() ? 'status-claimed' : 'status-fulfilled' }}">
                        {{ $request->isClaimed() ? 'CLAIMED' : 'READY FOR PICKUP' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Requester Information</div>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $request->user->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $request->user->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Office:</span>
                        <span class="info-value">{{ $request->user->office->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Request Date:</span>
                        <span class="info-value">{{ $request->created_at ? $request->created_at->format('M j, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Needed Date:</span>
                        <span class="info-value">{{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Priority:</span>
                        <span class="info-value">{{ strtoupper($request->priority) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Purpose</div>
            <div class="purpose-section">
                {{ $request->purpose }}
            </div>
        </div>

        <div class="item-qr-container">
            <div class="item-section">
                <div class="section-title">Item Details</div>
                <div class="item-details">
                    <div class="item-list">
                        @php
                            $approvedItems = $request->requestItems->filter(function ($requestItem) {
                                return $requestItem->item_status === 'approved' || $requestItem->isAdjusted();
                            });
                        @endphp
                        @if($approvedItems && $approvedItems->count() > 0)
                            @foreach($approvedItems as $requestItem)
                                <div class="item-entry">
                                    <div class="item-name-compact">{{ $requestItem->itemable ? $requestItem->itemable->name : 'Item Not Found' }}</div>
                                    <div class="quantity-compact">{{ $requestItem->getFinalQuantity() }} {{ $requestItem->itemable ? ($requestItem->itemable->unit ?? 'pcs') : 'pcs' }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="item-entry">
                                <div class="item-name-compact">No approved items found</div>
                                <div class="quantity-compact">0 pcs</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="qr-section">
                <div class="qr-title">Scan QR Code for Verification</div>
                <img src="{{ $qrCodeImage }}" alt="QR Code" class="qr-code">
                <div class="qr-note">
                    Present this code at the supply office for quick verification
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Requestor's Signature</div>
                <div style="margin-top: 3px; font-size: 10px;">{{ $request->user->name }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Supply Officer's Signature</div>
                <div style="margin-top: 3px; font-size: 10px;">{{ $request->adminApprover->name ?? '_______________' }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Date Received</div>
                <div style="margin-top: 3px; font-size: 10px;">MM/DD/YYYY</div>
            </div>
        </div>

        <div class="important-notes">
            <div class="notes-title">IMPORTANT NOTES:</div>
            <ul class="notes-list">
                <li>Present this claim slip when collecting your items</li>
                <li>Items must be collected within 5 working days</li>
                <li>Scan the QR code above for verification</li>
            </ul>
        </div>

        <div class="spacer-footer-container">
            <div class="spacer" style="flex: 1;"></div>

            <div class="footer">
                <p><strong>Generated:</strong> {{ now()->format('M j, Y g:i A') }} |
                <strong>Claim Slip:</strong> {{ $request->claim_slip_number }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads if requested
        if (new URLSearchParams(window.location.search).get('print') === 'true') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
