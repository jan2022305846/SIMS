<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Slip</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #ffffff;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
            min-height: 100vh;
        }

        .claim-slip {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 2px solid #000;
            min-height: calc(100vh - 40px); /* Full page height minus padding */
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
            padding: 0 20px;
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
            max-height: 90px;
            max-width: 300px;
            object-fit: contain;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin: 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .claim-number {
            font-size: 14px;
            color: #6c757d;
            font-weight: 600;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 2px solid #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 4px 0;
            vertical-align: top;
        }

        .info-cell:first-child {
            width: 35%;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            color: #212529;
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
            gap: 6px;
            margin-top: 8px;
            max-height: none; /* Allow flexible height */
        }

        .item-entry {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 6px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px; /* Slightly smaller for more items */
        }

        .item-name-compact {
            font-size: 11px; /* Smaller font for more items */
            font-weight: bold;
            color: #2c3e50;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .quantity-compact {
            font-size: 10px; /* Smaller quantity badge */
            background: #000;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .quantity-box {
            background: #000;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            margin: 8px 0;
        }

        .purpose-section {
            background: #f8f8f8;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #000;
        }

        .item-qr-container {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-shrink: 0;
        }

        .item-section {
            flex: 1;
        }

        .qr-section {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 8px;
        }

        .qr-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border: 2px solid #000;
            border-radius: 6px;
        }

        .qr-note {
            font-size: 10px;
            color: #6c757d;
            margin-top: 8px;
            font-style: italic;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
            border-collapse: separate;
            border-spacing: 30px 0;
        }

        .signature-row {
            display: table-row;
        }

        .signature-cell {
            display: table-cell;
            text-align: center;
            width: 33%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 3px;
            font-weight: bold;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 11px;
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
            border-radius: 6px;
            padding: 12px;
            margin-top: 20px;
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
            margin-bottom: 5px;
        }

        .notes-list {
            margin: 0;
            padding-left: 15px;
        }

        .notes-list li {
            margin-bottom: 3px;
            font-size: 11px;
        }

        /* Print-specific styles for single-page layout */
        @media print {
            body {
                margin: 0;
                padding: 10mm;
                min-height: 100vh;
                background: white;
            }

            .claim-slip {
                border: none;
                padding: 0;
                min-height: calc(100vh - 20mm);
                page-break-inside: avoid;
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
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
                text-align: center;
                color: #6c757d;
                font-size: 10px;
                page-break-inside: avoid;
                flex-shrink: 0;
            }

            .section {
                page-break-inside: avoid;
                margin-bottom: 10px;
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

            .logo img {
                max-height: 75px;
                max-width: 250px;
            }

            /* Force single page */
            .claim-slip,
            .claim-slip * {
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }

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

            /* Hide browser-generated headers/footers */
            html, body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Ensure no content breaks */
            * {
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
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
                <div style="margin-top: 8px;">
                    <span class="status-badge {{ $request->isClaimed() ? 'status-claimed' : 'status-fulfilled' }}">
                        {{ $request->isClaimed() ? 'CLAIMED' : 'READY FOR PICKUP' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Requester Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Name:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->user->name }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Email:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->user->email }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Office:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->user->office->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Request Date:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->created_at ? $request->created_at->format('M j, Y') : 'N/A' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Needed Date:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Priority:</span>
                    </div>
                    <div class="info-cell">
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
                        @if($request->requestItems && $request->requestItems->count() > 0)
                            @foreach($request->requestItems as $requestItem)
                                <div class="item-entry">
                                    <div class="item-name-compact">{{ $requestItem->itemable ? $requestItem->itemable->name : 'Item Not Found' }}</div>
                                    <div class="quantity-compact">{{ $requestItem->quantity }} {{ $requestItem->itemable ? ($requestItem->itemable->unit ?? 'pcs') : 'pcs' }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="item-entry">
                                <div class="item-name-compact">No Items Found</div>
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
                    Scan this code at the supply office for quick verification
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="signature-row">
                <div class="signature-cell">
                    <div class="signature-line">Requestor's Signature</div>
                    <div style="margin-top: 3px; font-size: 10px;">{{ $request->user->name }}</div>
                </div>
                <div class="signature-cell">
                    <div class="signature-line">Supply Officer's Signature</div>
                    <div style="margin-top: 3px; font-size: 10px;">{{ $request->fulfilledBy->name ?? '_______________' }}</div>
                </div>
                <div class="signature-cell">
                    <div class="signature-line">Date Received</div>
                    <div style="margin-top: 3px; font-size: 10px;">MM/DD/YYYY</div>
                </div>
            </div>
        </div>

        <div class="important-notes">
            <div class="notes-title">IMPORTANT NOTES:</div>
            <ul class="notes-list">
                <li>Please present this claim slip when collecting your items</li>
                <li>Items must be collected within 5 working days of fulfillment</li>
                <li>Scan the QR code above for quick verification at the supply office</li>
                <li>For questions, contact the Supply Office</li>
            </ul>
        </div>

        <div class="spacer-footer-container">
            <div class="spacer" style="flex: 1;"></div>

            <div class="footer">
                <p><strong>Generated on:</strong> {{ now()->format('F j, Y \a\t g:i A') }} |
                <strong>Claim Slip:</strong> {{ $request->claim_slip_number }}</p>
                <p style="margin-top: 5px; font-size: 9px;">
                    Supply Office Management System - Official Document
                </p>
            </div>
        </div>
    </div>
</body>
</html>