@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-qrcode me-2 text-primary"></i>
                        QR Code Scanner
                    </h2>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>

                <!-- Instructions -->
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-lg me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">How to use the scanner</h6>
                            <p class="mb-0">Click "Start Scanner" and position the QR code within the scanner frame. The scanner will automatically detect and process the code.</p>
                        </div>
                    </div>
                </div>

                <!-- Scanner Section -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-camera me-2"></i>
                                    Camera Scanner
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <!-- Scanner Area -->
                                <div id="qr-reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
                                
                                <!-- Scanner Controls -->
                                <div class="mt-3">
                                    <button id="start-scanner" class="btn btn-success me-2">
                                        <i class="fas fa-play me-1"></i>Start Scanner
                                    </button>
                                    <button id="stop-scanner" class="btn btn-danger" disabled>
                                        <i class="fas fa-stop me-1"></i>Stop Scanner
                                    </button>
                                </div>
                                
                                <!-- Status -->
                                <div id="scanner-status" class="mt-3 text-muted"></div>
                                
                                <!-- Loading Spinner -->
                                <div id="loading-spinner" class="mt-3 d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Initializing camera...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Input Section -->
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-keyboard me-2"></i>
                                    Manual Input
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">If the camera scanner isn't working, you can manually paste the QR code data here:</p>
                                <div class="input-group">
                                    <input type="text" id="manual-qr-input" class="form-control" 
                                           placeholder="Paste QR code data here...">
                                    <button id="process-manual-qr" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Process
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-8">
                        <!-- Success Results -->
                        <div id="scan-results" class="card border-success d-none">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Scan Successful!
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="scan-results-content"></div>
                                <div class="mt-3">
                                    <button id="view-item" class="btn btn-primary me-2">
                                        <i class="fas fa-eye me-1"></i>View Item Details
                                    </button>
                                    <button id="scan-another" class="btn btn-outline-secondary">
                                        <i class="fas fa-qrcode me-1"></i>Scan Another
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Error Results -->
                        <div id="error-message" class="card border-danger d-none">
                            <div class="card-header bg-danger text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error
                                </h5>
                            </div>
                            <div class="card-body">
                                <p id="error-text" class="text-danger mb-0"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner JavaScript -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// Debug: Test if script is loading
console.log('QR Scanner script loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    let html5QrCode = null;
    let scannerStarted = false;

    // Get DOM elements
    const startButton = document.getElementById('start-scanner');
    const stopButton = document.getElementById('stop-scanner');
    const statusDiv = document.getElementById('scanner-status');
    const resultsDiv = document.getElementById('scan-results');
    const errorDiv = document.getElementById('error-message');
    const manualInput = document.getElementById('manual-qr-input');
    const processManualButton = document.getElementById('process-manual-qr');
    const loadingSpinner = document.getElementById('loading-spinner');

    // Debug: Check if elements exist
    console.log('Start button:', startButton);
    console.log('Stop button:', stopButton);
    console.log('HTML5Qrcode available:', typeof Html5Qrcode !== 'undefined');

    // Start scanner function
    function startScanner() {
        console.log('Starting scanner...');
        
        // Quick test - change button text to show it's working
        startButton.textContent = 'Starting...';
        startButton.disabled = true;
        
        showLoading(true);
        hideMessages();
        
        // Initialize Html5Qrcode if not already done
        if (!html5QrCode) {
            try {
                html5QrCode = new Html5Qrcode("qr-reader");
                console.log('Html5Qrcode initialized successfully');
            } catch (error) {
                console.error('Error initializing Html5Qrcode:', error);
                showError('Failed to initialize scanner: ' + error.message);
                startButton.textContent = 'Start Scanner';
                startButton.disabled = false;
                showLoading(false);
                return;
            }
        }

        // Get cameras and start scanning
        Html5Qrcode.getCameras().then(devices => {
            console.log('Cameras found:', devices.length);
            
            if (devices && devices.length) {
                // Prefer back camera on mobile devices
                let cameraId = devices[0].id;
                
                // Look for back/environment camera on mobile
                // Try multiple patterns to find the back camera
                for (let device of devices) {
                    if (device.label) {
                        const label = device.label.toLowerCase();
                        // Check for various back camera naming patterns
                        if (label.includes('back') ||
                            label.includes('environment') ||
                            label.includes('rear') ||
                            label.includes('world') ||
                            (label.includes('camera') && label.includes('1')) ||
                            (label.includes('cam') && label.includes('1'))) {
                            cameraId = device.id;
                            console.log('Found back camera:', device.label);
                            break;
                        }
                    }
                }
                
                console.log('Using camera:', cameraId);
                
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };
                
                html5QrCode.start(
                    cameraId,
                    config,
                    (decodedText, decodedResult) => {
                        console.log('QR Code detected:', decodedText);
                        processQRCode(decodedText);
                        stopScanner();
                    },
                    (errorMessage) => {
                        // Handle scan errors silently - this happens frequently while scanning
                        // console.log('Scan error (normal):', errorMessage);
                    }
                ).then(() => {
                    console.log('Scanner started successfully');
                    scannerStarted = true;
                    startButton.innerHTML = '<i class="fas fa-play me-1"></i>Start Scanner';
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    showLoading(false);
                    statusDiv.textContent = 'Scanner active. Position QR code in the frame.';
                    statusDiv.className = 'mt-3 text-success';
                }).catch(err => {
                    console.error('Error starting scanner:', err);
                    showLoading(false);
                    startButton.innerHTML = '<i class="fas fa-play me-1"></i>Start Scanner';
                    startButton.disabled = false;
                    showError('Failed to start scanner: ' + err.message);
                });
            } else {
                showLoading(false);
                startButton.innerHTML = '<i class="fas fa-play me-1"></i>Start Scanner';
                startButton.disabled = false;
                showError('No cameras found. Please ensure you have a camera connected and grant camera permissions.');
            }
        }).catch(err => {
            console.error('Error accessing cameras:', err);
            showLoading(false);
            startButton.innerHTML = '<i class="fas fa-play me-1"></i>Start Scanner';
            startButton.disabled = false;
            showError('Error accessing camera: ' + err.message + '. Please grant camera permissions and refresh the page.');
        });
    }

    // Stop scanner function
    function stopScanner() {
        if (html5QrCode && scannerStarted) {
            html5QrCode.stop().then(() => {
                console.log('Scanner stopped');
                scannerStarted = false;
                startButton.disabled = false;
                stopButton.disabled = true;
                statusDiv.textContent = 'Scanner stopped.';
                statusDiv.className = 'mt-3 text-muted';
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }
    }

    // Process QR code data
    function processQRCode(qrData) {
        console.log('Processing QR data:', qrData);
        hideMessages();
        statusDiv.textContent = 'Processing QR code...';
        statusDiv.className = 'mt-3 text-info';

        fetch('{{ route("qr.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                qr_data: qrData,
                location: 'QR Scanner Page',
                scan_method: 'camera'
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            statusDiv.textContent = '';
            
            if (data.success) {
                showScanResults(data.item, data.redirect);
            } else {
                showError(data.message || 'Failed to process QR code.');
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            statusDiv.textContent = '';
            showError('Network error: ' + error.message);
        });
    }

    // Show scan results
    function showScanResults(item, redirectUrl) {
        hideMessages();
        
        const resultsContent = document.getElementById('scan-results-content');
        
        let holderInfo = '';
        let assignmentInfo = '';
        let statusBadges = '';
        
        // Holder information
        if (item.holder) {
            holderInfo = `
                <div class="col-md-6">
                    <h6 class="fw-bold">Current Holder</h6>
                    <p><strong>Name:</strong> ${item.holder.name}</p>
                    <p><strong>Email:</strong> ${item.holder.email}</p>
                    <p><strong>Office:</strong> ${item.holder.office}</p>
                    <p><strong>Role:</strong> ${item.holder.role}</p>
                </div>
            `;
        }
        
        // Assignment information
        if (item.assignment) {
            assignmentInfo = `
                <div class="col-md-6">
                    <h6 class="fw-bold">Assignment Details</h6>
                    <p><strong>Assigned:</strong> ${item.assignment.assigned_at}</p>
                    <p><strong>Duration:</strong> ${item.assignment.assigned_at_human}</p>
                    ${item.assignment.notes ? `<p><strong>Notes:</strong> ${item.assignment.notes}</p>` : ''}
                </div>
            `;
        }
        
        // Status badges
        if (item.status) {
            statusBadges = `
                <div class="mt-3">
                    <span class="badge bg-${item.status.stock_class} me-2">${item.status.stock}</span>
                    <span class="badge bg-${item.status.assignment_class} me-2">${item.status.assignment}</span>
                    <span class="badge bg-${item.status.condition_class} me-2">${item.status.condition}</span>
                    <span class="badge bg-${item.status.overall_class}">${item.status.overall}</span>
                </div>
            `;
        }
        
        resultsContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Item Information</h6>
                    <p><strong>Name:</strong> ${item.name}</p>
                    <p><strong>Category:</strong> ${item.category || 'N/A'} ${item.category_type ? `(${item.category_type})` : ''}</p>
                    <p><strong>Location:</strong> ${item.location || 'Not specified'}</p>
                    <p><strong>Condition:</strong> ${item.condition || 'Good'}</p>
                    ${item.is_non_consumable ? '<p><em class="text-info">Non-consumable item</em></p>' : ''}
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Stock & Activity</h6>
                    <p><strong>Current Stock:</strong> ${item.current_stock || 0} ${item.unit || 'units'}</p>
                    <p><strong>Last Scan:</strong> ${item.last_scan || 'Never'}</p>
                    <p><strong>Total Scans:</strong> ${item.total_scans || 0}</p>
                    ${statusBadges}
                </div>
            </div>
            ${holderInfo || assignmentInfo ? `
                <hr>
                <div class="row">
                    ${holderInfo}
                    ${assignmentInfo}
                </div>
            ` : ''}
        `;
        
        // Set up view item button
        document.getElementById('view-item').onclick = function() {
            window.location.href = redirectUrl;
        };
        
        resultsDiv.classList.remove('d-none');
    }

    // Show error message
    function showError(message) {
        hideMessages();
        document.getElementById('error-text').textContent = message;
        errorDiv.classList.remove('d-none');
    }

    // Hide all messages
    function hideMessages() {
        resultsDiv.classList.add('d-none');
        errorDiv.classList.add('d-none');
    }

    // Show/hide loading
    function showLoading(show) {
        if (show) {
            loadingSpinner.classList.remove('d-none');
            startButton.disabled = true;
        } else {
            loadingSpinner.classList.add('d-none');
            if (!scannerStarted) {
                startButton.disabled = false;
            }
        }
    }

    // Event listeners with debugging
    if (startButton) {
        startButton.addEventListener('click', function() {
            console.log('Start button clicked');
            startScanner();
        });
        console.log('Start button event listener added');
    } else {
        console.error('Start button not found!');
    }
    
    if (stopButton) {
        stopButton.addEventListener('click', function() {
            console.log('Stop button clicked');
            stopScanner();
        });
    }
    
    if (processManualButton) {
        processManualButton.addEventListener('click', function() {
            const qrData = manualInput.value.trim();
            if (qrData) {
                console.log('Processing manual input:', qrData);
                processQRCode(qrData);
            } else {
                showError('Please enter QR code data.');
            }
        });
    }
    
    document.getElementById('scan-another').addEventListener('click', function() {
        console.log('Scan another clicked');
        hideMessages();
        manualInput.value = '';
        startScanner();
    });

    // Test if HTML5 QR Code library is loaded
    if (typeof Html5Qrcode === 'undefined') {
        console.error('HTML5 QR Code library not loaded');
        showError('QR Scanner library failed to load. Please refresh the page.');
    } else {
        console.log('QR Scanner library loaded successfully');
        statusDiv.textContent = 'QR Scanner ready. Click "Start Scanner" to begin.';
        statusDiv.className = 'mt-3 text-muted';
    }

    // Test button functionality immediately
    console.log('Testing button click detection...');
    if (startButton) {
        startButton.onclick = function() {
            console.log('Button clicked via onclick!');
            alert('Button works! Now starting scanner...');
            startScanner();
        };
    }
});
</script>
@endsection
