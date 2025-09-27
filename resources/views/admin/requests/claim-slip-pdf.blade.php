<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Slip - {{ $request->claim_slip_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #ffffff;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }

        .claim-slip {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 2px solid #fcb315;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #fcb315;
        }

        .logo {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #fcb315;
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
            border-bottom: 2px solid #e9ecef;
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
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #fcb315;
            margin: 12px 0;
        }

        .item-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .quantity-box {
            background: #fcb315;
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
            background: #e3f2fd;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
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
            border: 2px solid #fcb315;
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
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-claimed {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .important-notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 12px;
            margin-top: 20px;
        }

        .notes-title {
            font-weight: bold;
            color: #856404;
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
    </style>
</head>
<body>
    <div class="claim-slip">
        <div class="header">
            <div class="logo">SUPPLY OFFICE MANAGEMENT SYSTEM</div>
            <div class="title">Official Claim Slip</div>
            <div class="claim-number">{{ $request->claim_slip_number }}</div>
            <div style="margin-top: 8px;">
                <span class="status-badge {{ $request->isClaimed() ? 'status-claimed' : 'status-fulfilled' }}">
                    {{ $request->isClaimed() ? 'CLAIMED' : 'READY FOR PICKUP' }}
                </span>
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
                        <span class="info-label">Department:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->department }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Request Date:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</span>
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
            <div class="section-title">Item Details</div>
            <div class="item-details">
                <div class="item-name">{{ $request->item->name }}</div>
                <div class="info-row" style="display: table; width: 100%;">
                    <div style="display: table-cell; padding: 4px 0;">
                        <span class="info-label">Description:</span>
                    </div>
                    <div style="display: table-cell; padding: 4px 0;">
                        <span class="info-value">{{ $request->item->description ?? 'N/A' }}</span>
                    </div>
                </div>
                <div style="text-align: center; margin: 12px 0;">
                    <div class="quantity-box">
                        Quantity: {{ $request->quantity }} {{ $request->item->unit ?? 'pcs' }}
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

        <div class="qr-section">
            <div class="qr-title">Scan QR Code for Verification</div>
            <img src="{{ $qrCodeImage }}" alt="QR Code" class="qr-code">
            <div class="qr-note">
                Scan this code at the supply office for quick verification
            </div>
        </div>

        <div class="section">
            <div class="section-title">Fulfillment Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Fulfilled By:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->fulfilledBy->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-cell">
                        <span class="info-label">Fulfilled Date:</span>
                    </div>
                    <div class="info-cell">
                        <span class="info-value">{{ $request->fulfilled_date ? $request->fulfilled_date->format('M j, Y g:i A') : 'N/A' }}</span>
                    </div>
                </div>
                @if($request->isClaimed())
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">Claimed By:</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-value">{{ $request->claimedBy->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">Claimed Date:</span>
                        </div>
                        <div class="info-cell">
                            <span class="info-value">{{ $request->claimed_date ? $request->claimed_date->format('M j, Y g:i A') : 'N/A' }}</span>
                        </div>
                    </div>
                @endif
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
                    <div style="margin-top: 3px; font-size: 10px;">___/___/______</div>
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

        <div class="footer">
            <p><strong>Generated on:</strong> {{ now()->format('F j, Y \a\t g:i A') }} |
            <strong>Claim Slip:</strong> {{ $request->claim_slip_number }}</p>
            <p style="margin-top: 5px; font-size: 9px;">
                Supply Office Management System - Official Document
            </p>
        </div>
    </div>
</body>
</html>