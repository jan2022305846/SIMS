@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Welcome Card with QR Scanner - Horizontal Layout -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <!-- Welcome Section -->
                            <div class="col-lg-6">
                                <div class="pe-lg-4">
                                    <h2 class="fw-bold mb-3" style="color: #1a1851;">
                                        <i class="fas fa-tachometer-alt me-2 text-warning"></i>
                                        Welcome back, {{ Auth::user()->name }}!
                                    </h2>
                                    <div class="mb-3">
                                        @if(Auth::user()->role === 'admin')
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                <i class="fas fa-shield-alt me-1"></i>
                                                Admin Dashboard
                                            </span>
                                        @else
                                            <span class="badge bg-info fs-6 px-3 py-2">
                                                <i class="fas fa-user-graduate me-1"></i>
                                                Faculty Dashboard
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-muted mb-0">
                                        {{ Auth::user()->role === 'admin' 
                                            ? 'Manage inventory, users, and monitor system activities.' 
                                            : 'Browse items, create requests, and track your submissions.' }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- QR Scanner Section -->
                            <div class="col-lg-6">
                                <div class="ps-lg-4 border-start border-2 border-light">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-qrcode fa-2x text-warning me-3"></i>
                                        <div>
                                            <h4 class="h5 fw-bold mb-1">QR Code Scanner</h4>
                                            <p class="text-muted small mb-0">Scan QR codes for instant item access</p>
                                        </div>
                                    </div>
                                    
                                    <div id="qr-scanner-container">
                                        <div class="text-center mb-3">
                                            <button id="start-scanner-btn" class="btn btn-warning btn-lg fw-semibold">
                                                <i class="fas fa-camera me-2"></i>
                                                Start Scanner
                                            </button>
                                        </div>
                                        <div id="qr-reader" style="display: none; width: 100%; max-width: 300px; margin: 15px auto;"></div>
                                        <div id="scan-result" class="mt-3" style="display: none;">
                                            <div class="alert alert-success d-flex align-items-center">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <div>
                                                    <strong class="small">Scan Successful!</strong>
                                                    <div id="scan-details" class="small mt-1"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards - Enhanced and Bigger -->
                <div class="row g-3">
                    @if(Auth::user()->role === 'admin')
                        <!-- Total Items Card -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-box fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Items</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $totalItems ?? 0 }}</h2>
                                            <small class="text-success">
                                                <i class="fas fa-arrow-up me-1"></i>Active inventory
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Users Card -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-users fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Users</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $totalUsers ?? 0 }}</h2>
                                            <small class="text-info">
                                                <i class="fas fa-user-check me-1"></i>Registered members
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-clock fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending Requests</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $pendingRequests ?? 0 }}</h2>
                                            <small class="text-warning">
                                                <i class="fas fa-hourglass-half me-1"></i>Awaiting approval
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Low Stock Items Card -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-danger bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-exclamation-triangle fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Low Stock Alert</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $lowStockItems ?? 0 }}</h2>
                                            <small class="text-danger">
                                                <i class="fas fa-arrow-down me-1"></i>Need restocking
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Faculty User Stats -->
                        <!-- My Requests Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-file-alt fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">My Requests</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $myRequests ?? 0 }}</h2>
                                            <small class="text-info">
                                                <i class="fas fa-list me-1"></i>Total submitted
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Requests Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-clock fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Pending</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $myPendingRequests ?? 0 }}</h2>
                                            <small class="text-warning">
                                                <i class="fas fa-hourglass-half me-1"></i>Under review
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Requests Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-check-circle fa-lg text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Approved</h6>
                                            <h2 class="fw-bold mb-1" style="color: #1a1851; font-size: 2rem;">{{ $myApprovedRequests ?? 0 }}</h2>
                                            <small class="text-success">
                                                <i class="fas fa-thumbs-up me-1"></i>Ready for pickup
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner JavaScript - Direct inclusion -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
console.log('🔍 QR Scanner script loading...');

let html5QrCode;
let scannerStarted = false;

// Make sure DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Dashboard QR Scanner: DOM loaded');
    
    const startBtn = document.getElementById('start-scanner-btn');
    if (startBtn) {
        console.log('✅ Dashboard QR Scanner: Start button found');
        startBtn.addEventListener('click', function() {
            console.log('🚀 Dashboard QR Scanner: Start button clicked!');
            startQRScanner();
        });
    } else {
        console.error('❌ Dashboard QR Scanner: Start button not found');
    }
    
    // Also test if the button exists and is clickable
    if (startBtn) {
        console.log('Button element:', startBtn);
        console.log('Button classes:', startBtn.className);
        console.log('Button ID:', startBtn.id);
    }
});

function startQRScanner() {
    console.log('🔍 Dashboard QR Scanner: Starting scanner');
    
    // Prevent multiple scanners from starting
    if (scannerStarted) {
        console.log('⚠️ Scanner already running, ignoring start request');
        return;
    }
    
    const qrReaderElement = document.getElementById('qr-reader');
    const startBtn = document.getElementById('start-scanner-btn');
    
    if (!qrReaderElement) {
        console.error('❌ QR reader element not found');
        alert('Scanner element not found. Please refresh the page.');
        return;
    }
    
    // Show the scanner area
    qrReaderElement.style.display = 'block';
    startBtn.innerHTML = `
        <div class="spinner-border spinner-border-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Starting Camera...
    `;
    startBtn.disabled = true;
    
    // Initialize the QR code scanner
    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        console.log('✅ Html5Qrcode initialized');
        
        // Get cameras first
        Html5Qrcode.getCameras().then(devices => {
            console.log('📷 Cameras found:', devices.length);
            
            if (devices && devices.length) {
                // Use the first camera (usually back camera on mobile)
                const cameraId = devices[0].id;
                console.log('📷 Using camera:', cameraId);
                
                html5QrCode.start(
                    cameraId, // Use camera ID instead of facingMode
                    {
                        fps: 10,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            // Square QR box with 70% of the smaller dimension
                            let minEdgePercentage = 0.7;
                            let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                            return {
                                width: qrboxSize,
                                height: qrboxSize
                            };
                        }
                    },
                    (decodedText, decodedResult) => {
                        console.log('🎯 QR Code detected:', decodedText);
                        // Stop scanning
                        html5QrCode.stop().then(() => {
                            scannerStarted = false;
                            processQRScan(decodedText);
                        });
                    },
                    (errorMessage) => {
                        // Handle scan error (don't show too many errors)
                        // console.log(`QR Code scan error: ${errorMessage}`);
                    }
                ).then(() => {
                    console.log('✅ Scanner started successfully');
                    scannerStarted = true;
                    // Scanner started successfully - update button to stop
                    startBtn.innerHTML = `
                        <i class="fas fa-stop me-2"></i>
                        Stop Scanner
                    `;
                    startBtn.disabled = false;
                    startBtn.className = 'btn btn-danger btn-lg fw-semibold';
                    
                    // Replace the button with a new one that has stop functionality
                    const newBtn = startBtn.cloneNode(true);
                    startBtn.parentNode.replaceChild(newBtn, startBtn);
                    newBtn.addEventListener('click', stopScanner);
                    
                }).catch(err => {
                    console.error('❌ Unable to start QR scanner:', err);
                    alert('Unable to start QR scanner: ' + err.message + '\n\nPlease ensure camera permissions are granted.');
                    resetScanner();
                });
            } else {
                console.error('❌ No cameras found');
                alert('No cameras found. Please ensure you have a camera connected.');
                resetScanner();
            }
        }).catch(err => {
            console.error('❌ Error getting cameras:', err);
            alert('Error accessing camera: ' + err.message + '\n\nPlease grant camera permissions.');
            resetScanner();
        });
        
    } catch (error) {
        console.error('❌ Error initializing scanner:', error);
        alert('Error initializing scanner: ' + error.message);
        resetScanner();
    }
}

function stopScanner() {
    console.log('🛑 Stopping scanner');
    
    if (html5QrCode && scannerStarted) {
        html5QrCode.stop().then(() => {
            console.log('✅ Scanner stopped successfully');
            scannerStarted = false;
            resetScanner();
        }).catch(err => {
            console.error('❌ Error stopping scanner:', err);
            scannerStarted = false; // Force reset even if stop fails
            resetScanner();
        });
    } else {
        console.log('⚠️ Scanner was not running, just resetting');
        scannerStarted = false;
        resetScanner();
    }
}

function processQRScan(qrData) {
    console.log('🔄 Processing QR scan:', qrData);
    const startBtn = document.getElementById('start-scanner-btn');
    startBtn.innerHTML = `
        <div class="spinner-border spinner-border-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Processing...
    `;
    startBtn.disabled = true;

    fetch('{{ route("dashboard.scan-qr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            qr_data: qrData
        })
    })
    .then(response => {
        console.log('📡 Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('📡 Response data:', data);
        if (data.success) {
            showScanResult(data.data);
            if (data.data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.data.redirect_url;
                }, 3000);
            }
        } else {
            alert('Scan failed: ' + data.message);
            resetScanner();
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        alert('Error processing scan. Please try again.');
        resetScanner();
    });
}

function showScanResult(data) {
    const resultDiv = document.getElementById('scan-result');
    const detailsDiv = document.getElementById('scan-details');
    
    detailsDiv.innerHTML = `
        <div><strong>Item:</strong> ${data.item.name}</div>
        <div><strong>Category:</strong> ${data.item.category.name}</div>
        <div><strong>Stock:</strong> ${data.item.current_stock || 'N/A'}</div>
        <div><strong>Scanned at:</strong> ${data.scan_time}</div>
        <div class="mt-2 small">Redirecting to item details in 3 seconds...</div>
    `;
    
    resultDiv.style.display = 'block';
    
    // Hide scanner
    document.getElementById('qr-reader').style.display = 'none';
}

function resetScanner() {
    console.log('🔄 Resetting scanner');
    const startBtn = document.getElementById('start-scanner-btn');
    startBtn.innerHTML = `
        <i class="fas fa-camera me-2"></i>
        Start Scanner
    `;
    startBtn.disabled = false;
    startBtn.className = 'btn btn-warning btn-lg fw-semibold';
    
    // Clear all event listeners by cloning the button
    const newStartBtn = startBtn.cloneNode(true);
    startBtn.parentNode.replaceChild(newStartBtn, startBtn);
    
    // Add only the start scanner event listener
    newStartBtn.addEventListener('click', function() {
        console.log('🚀 Start scanner clicked (after reset)');
        startQRScanner();
    });
    
    document.getElementById('qr-reader').style.display = 'none';
    document.getElementById('scan-result').style.display = 'none';
    scannerStarted = false;
}

// Add some CSS for better mobile experience with Bootstrap
const style = document.createElement('style');
style.textContent = `
    #qr-reader {
        border: 2px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    #qr-reader video {
        border-radius: 0.25rem;
    }
    @media (max-width: 576px) {
        #qr-reader {
            max-width: 100% !important;
            width: 100% !important;
        }
    }
`;
document.head.appendChild(style);

// Test if HTML5QRCode library is loaded
if (typeof Html5Qrcode === 'undefined') {
    console.error('❌ HTML5QRCode library not loaded on dashboard');
} else {
    console.log('✅ HTML5QRCode library loaded successfully on dashboard');
}
</script>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode;
let scannerStarted = false;

// Make sure DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard QR Scanner: DOM loaded');
    
    const startBtn = document.getElementById('start-scanner-btn');
    if (startBtn) {
        console.log('Dashboard QR Scanner: Start button found');
        startBtn.addEventListener('click', function() {
            console.log('Dashboard QR Scanner: Start button clicked');
            startQRScanner();
        });
    } else {
        console.error('Dashboard QR Scanner: Start button not found');
    }
});

function startQRScanner() {
    console.log('Dashboard QR Scanner: Starting scanner');
    
    const qrReaderElement = document.getElementById('qr-reader');
    const startBtn = document.getElementById('start-scanner-btn');
    
    if (!qrReaderElement) {
        console.error('QR reader element not found');
        alert('Scanner element not found. Please refresh the page.');
        return;
    }
    
    // Show the scanner area
    qrReaderElement.style.display = 'block';
    startBtn.innerHTML = `
        <div class="spinner-border spinner-border-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Starting Camera...
    `;
    startBtn.disabled = true;
    
    // Initialize the QR code scanner
    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        console.log('Html5Qrcode initialized');
        
        // Get cameras first
        Html5Qrcode.getCameras().then(devices => {
            console.log('Cameras found:', devices.length);
            
            if (devices && devices.length) {
                // Use the first camera (usually back camera on mobile)
                const cameraId = devices[0].id;
                
                html5QrCode.start(
                    cameraId, // Use camera ID instead of facingMode
                    {
                        fps: 10,
                        qrbox: function(viewfinderWidth, viewfinderHeight) {
                            // Square QR box with 70% of the smaller dimension
                            let minEdgePercentage = 0.7;
                            let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                            return {
                                width: qrboxSize,
                                height: qrboxSize
                            };
                        }
                    },
                    (decodedText, decodedResult) => {
                        console.log('QR Code detected:', decodedText);
                        // Stop scanning
                        html5QrCode.stop().then(() => {
                            scannerStarted = false;
                            processQRScan(decodedText);
                        });
                    },
                    (errorMessage) => {
                        // Handle scan error (don't show too many errors)
                        // console.log(`QR Code scan error: ${errorMessage}`);
                    }
                ).then(() => {
                    console.log('Scanner started successfully');
                    scannerStarted = true;
                    // Scanner started successfully
                    startBtn.innerHTML = `
                        <i class="fas fa-stop me-2"></i>
                        Stop Scanner
                    `;
                    startBtn.disabled = false;
                    startBtn.className = 'btn btn-danger btn-lg fw-semibold';
                    startBtn.onclick = stopScanner;
                }).catch(err => {
                    console.error('Unable to start QR scanner:', err);
                    alert('Unable to start QR scanner: ' + err.message + '\n\nPlease ensure camera permissions are granted.');
                    resetScanner();
                });
            } else {
                console.error('No cameras found');
                alert('No cameras found. Please ensure you have a camera connected.');
                resetScanner();
            }
        }).catch(err => {
            console.error('Error getting cameras:', err);
            alert('Error accessing camera: ' + err.message + '\n\nPlease grant camera permissions.');
            resetScanner();
        });
        
    } catch (error) {
        console.error('Error initializing scanner:', error);
        alert('Error initializing scanner: ' + error.message);
        resetScanner();
    }
}

function stopScanner() {
    console.log('Stopping scanner');
    if (html5QrCode && scannerStarted) {
        html5QrCode.stop().then(() => {
            console.log('Scanner stopped');
            scannerStarted = false;
            resetScanner();
        }).catch(err => {
            console.error('Error stopping scanner:', err);
            resetScanner();
        });
    } else {
        resetScanner();
    }
}

function processQRScan(qrData) {
    console.log('Processing QR scan:', qrData);
    const startBtn = document.getElementById('start-scanner-btn');
    startBtn.innerHTML = `
        <div class="spinner-border spinner-border-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Processing...
    `;
    startBtn.disabled = true;

    fetch('{{ route("dashboard.scan-qr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            qr_data: qrData
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showScanResult(data.data);
            if (data.data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.data.redirect_url;
                }, 3000);
            }
        } else {
            alert('Scan failed: ' + data.message);
            resetScanner();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing scan. Please try again.');
        resetScanner();
    });
}

function showScanResult(data) {
    const resultDiv = document.getElementById('scan-result');
    const detailsDiv = document.getElementById('scan-details');
    
    detailsDiv.innerHTML = `
        <div><strong>Item:</strong> ${data.item.name}</div>
        <div><strong>Category:</strong> ${data.item.category.name}</div>
        <div><strong>Stock:</strong> ${data.item.current_stock || 'N/A'}</div>
        <div><strong>Scanned at:</strong> ${data.scan_time}</div>
        <div class="mt-2 small">Redirecting to item details in 3 seconds...</div>
    `;
    
    resultDiv.style.display = 'block';
    
    // Hide scanner
    document.getElementById('qr-reader').style.display = 'none';
}

function resetScanner() {
    console.log('Resetting scanner');
    const startBtn = document.getElementById('start-scanner-btn');
    startBtn.innerHTML = `
        <i class="fas fa-camera me-2"></i>
        Start Scanner
    `;
    startBtn.disabled = false;
    startBtn.className = 'btn btn-warning btn-lg fw-semibold';
    
    // Remove the old onclick and add new event listener
    startBtn.onclick = null;
    startBtn.removeEventListener('click', stopScanner);
    startBtn.addEventListener('click', function() {
        console.log('Start scanner clicked (after reset)');
        startQRScanner();
    });
    
    document.getElementById('qr-reader').style.display = 'none';
    document.getElementById('scan-result').style.display = 'none';
    scannerStarted = false;
}

// Add some CSS for better mobile experience with Bootstrap
const style = document.createElement('style');
style.textContent = `
    #qr-reader {
        border: 2px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    #qr-reader video {
        border-radius: 0.25rem;
    }
    @media (max-width: 576px) {
        #qr-reader {
            max-width: 100% !important;
            width: 100% !important;
        }
    }
`;
document.head.appendChild(style);

// Test if HTML5QRCode library is loaded
if (typeof Html5Qrcode === 'undefined') {
    console.error('HTML5QRCode library not loaded on dashboard');
} else {
    console.log('HTML5QRCode library loaded successfully on dashboard');
}
</script>
@endpush
