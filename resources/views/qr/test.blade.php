@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-qrcode me-2 text-success"></i>
                        QR Code Test Page
                    </h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('qr.scanner') }}" class="btn btn-primary">
                            <i class="fas fa-camera me-1"></i>Go to Scanner
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- QR Code Generator -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Generate Test QR Code
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Generate a QR code for testing the scanner functionality.</p>
                                
                                <!-- Sample Item Selection -->
                                <div class="mb-3">
                                    <label for="test-item" class="form-label">Select Test Item:</label>
                                    <select id="test-item" class="form-select">
                                        <option value="">Choose an item...</option>
                                        @if(isset($items) && $items->count() > 0)
                                            @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }} (ID: {{ $item->id }})</option>
                                            @endforeach
                                        @else
                                            <option value="test">Test Item (Sample)</option>
                                        @endif
                                    </select>
                                </div>

                                <button onclick="generateTestQR()" class="btn btn-success">
                                    <i class="fas fa-qrcode me-1"></i>Generate QR Code
                                </button>

                                <!-- QR Code Display -->
                                <div id="qr-display" class="mt-4 text-center d-none">
                                    <h6 class="fw-bold">Generated QR Code:</h6>
                                    <div id="qr-image-container" class="mb-3"></div>
                                    <div id="qr-data-container" class="p-3 bg-light rounded">
                                        <small class="text-muted">QR Data:</small>
                                        <pre id="qr-data-text" class="mt-2 mb-0 small"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    How to Test
                                </h5>
                            </div>
                            <div class="card-body">
                                <ol class="list-group list-group-numbered list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">Generate QR Code</div>
                                            Select an item and click "Generate QR Code"
                                        </div>
                                        <span class="badge bg-primary rounded-pill">1</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">Open Scanner</div>
                                            Click "Go to Scanner" button above
                                        </div>
                                        <span class="badge bg-primary rounded-pill">2</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">Start Scanner</div>
                                            Click "Start Scanner" and allow camera access
                                        </div>
                                        <span class="badge bg-primary rounded-pill">3</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">Scan QR Code</div>
                                            Point camera at the QR code displayed here
                                        </div>
                                        <span class="badge bg-primary rounded-pill">4</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">View Results</div>
                                            Scanner should show item details
                                        </div>
                                        <span class="badge bg-primary rounded-pill">5</span>
                                    </li>
                                </ol>

                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> Make sure to allow camera permissions when prompted by your browser.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Test Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-keyboard me-2"></i>
                                    Manual Test (Alternative)
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">If camera scanning doesn't work, you can copy the QR data and paste it manually in the scanner:</p>
                                <div class="row">
                                    <div class="col-md-8">
                                        <textarea id="manual-test-data" class="form-control" rows="3" 
                                                  placeholder="QR data will appear here after generating a QR code..." readonly></textarea>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <button onclick="copyQRData()" class="btn btn-outline-primary w-100" id="copy-btn" disabled>
                                            <i class="fas fa-copy me-1"></i>Copy QR Data
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Copy this data and paste it in the "Manual Input" section of the scanner page.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateTestQR() {
    const itemSelect = document.getElementById('test-item');
    const selectedValue = itemSelect.value;
    
    if (!selectedValue) {
        alert('Please select an item first.');
        return;
    }

    // Show loading
    document.getElementById('qr-display').classList.add('d-none');
    
    if (selectedValue === 'test') {
        // Generate sample QR data
        const sampleData = {
            type: 'item',
            id: 999,
            name: 'Test Item',
            code: 'TEST001',
            url: window.location.origin + '/items/999'
        };
        
        displayQRCode('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iIzMzMzMzMyIvPjx0ZXh0IHg9IjEwMCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+U2FtcGxlIFFSIENvZGU8L3RleHQ+PC9zdmc+', JSON.stringify(sampleData, null, 2));
    } else {
        // Generate real QR code for selected item
        fetch(`/qr/generate/${selectedValue}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayQRCode(data.qr_code, data.qr_data);
            } else {
                alert('Failed to generate QR code: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error generating QR code: ' + error.message);
        });
    }
}

function displayQRCode(qrImageUrl, qrData) {
    document.getElementById('qr-image-container').innerHTML = 
        `<img src="${qrImageUrl}" alt="Generated QR Code" class="img-fluid border rounded" style="max-width: 200px;">`;
    
    document.getElementById('qr-data-text').textContent = qrData;
    document.getElementById('manual-test-data').value = qrData;
    document.getElementById('copy-btn').disabled = false;
    document.getElementById('qr-display').classList.remove('d-none');
}

function copyQRData() {
    const textarea = document.getElementById('manual-test-data');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        navigator.clipboard.writeText(textarea.value).then(() => {
            const btn = document.getElementById('copy-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        });
    } catch (err) {
        // Fallback for older browsers
        document.execCommand('copy');
        alert('QR data copied to clipboard!');
    }
}
</script>
@endsection
