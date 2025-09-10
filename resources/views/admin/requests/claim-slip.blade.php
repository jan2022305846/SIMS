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
            padding: 20px;
            background: #f8f9fa;
        }
        
        .claim-slip {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #fcb315;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #fcb315;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .title {
            font-size: 32px;
            font-weight: bold;
            color: #fcb315;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .claim-number {
            font-size: 18px;
            color: #6c757d;
            font-weight: 600;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
            margin-right: 10px;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
        }
        
        .item-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #fcb315;
            margin: 15px 0;
        }
        
        .item-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .quantity-box {
            background: #fcb315;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            margin: 10px 0;
        }
        
        .purpose-section {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-fulfilled {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .status-claimed {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        @media print {
            body {
                background: white;
                padding: 10px;
            }
            
            .claim-slip {
                box-shadow: none;
                border: 2px solid #fcb315;
                margin: 0;
                padding: 30px;
            }
            
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fcb315;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #e09e00;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Claim Slip
    </button>
    
    <div class="claim-slip">
        <div class="header">
            <div class="logo">SUPPLY OFFICE MANAGEMENT SYSTEM</div>
            <div class="title">Official Claim Slip</div>
            <div class="claim-number">{{ $request->claim_slip_number }}</div>
            <div style="margin-top: 10px;">
                <span class="status-badge {{ $request->isClaimed() ? 'status-claimed' : 'status-fulfilled' }}">
                    {{ $request->isClaimed() ? 'CLAIMED' : 'READY FOR PICKUP' }}
                </span>
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
                        <span class="info-label">Department:</span>
                        <span class="info-value">{{ $request->department }}</span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Request Date:</span>
                        <span class="info-value">{{ $request->request_date ? $request->request_date->format('F j, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Needed Date:</span>
                        <span class="info-value">{{ $request->needed_date ? $request->needed_date->format('F j, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Priority:</span>
                        <span class="info-value">{{ strtoupper($request->priority) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Item Details</div>
            <div class="item-details">
                <div class="item-name">{{ $request->item->name }}</div>
                <div class="info-item">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ $request->item->description ?? 'N/A' }}</span>
                </div>
                <div style="text-align: center; margin: 15px 0;">
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

        <div class="section">
            <div class="section-title">Fulfillment Information</div>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Fulfilled By:</span>
                        <span class="info-value">{{ $request->fulfilledBy->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fulfilled Date:</span>
                        <span class="info-value">{{ $request->fulfilled_date ? $request->fulfilled_date->format('F j, Y g:i A') : 'N/A' }}</span>
                    </div>
                </div>
                <div>
                    @if($request->isClaimed())
                        <div class="info-item">
                            <span class="info-label">Claimed By:</span>
                            <span class="info-value">{{ $request->claimedBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Claimed Date:</span>
                            <span class="info-value">{{ $request->claimed_date ? $request->claimed_date->format('F j, Y g:i A') : 'N/A' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Requestor's Signature</div>
                <div style="margin-top: 5px; font-size: 14px;">{{ $request->user->name }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Supply Officer's Signature</div>
                <div style="margin-top: 5px; font-size: 14px;">{{ $request->fulfilledBy->name ?? '_______________' }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Date Received</div>
                <div style="margin-top: 5px; font-size: 14px;">___/___/______</div>
            </div>
        </div>

        <div class="footer">
            <p><strong>IMPORTANT NOTES:</strong></p>
            <p>• Please present this claim slip when collecting your items</p>
            <p>• Items must be collected within 5 working days of fulfillment</p>
            <p>• For questions, contact the Supply Office</p>
            <p style="margin-top: 20px; font-size: 12px;">
                Generated on {{ now()->format('F j, Y \a\t g:i A') }} | 
                Supply Office Management System
            </p>
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
