/**
 * Dashboard JavaScript - QR Scanner and Barcode Reader
 * Handles both camera-based QR scanning and wired barcode reader input
 * Enhanced for item monitoring with detailed display
 * BUILD TEST: Version 2.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize button states
    initializeButtonStates();

    // Initialize scanning functionality
    initializeQRScanner();

    // Force cleanup of any lingering modal backdrops
    cleanupModalBackdrops();
});

/**
 * Initialize Button States
 */
function initializeButtonStates() {
    const startCameraBtn = document.getElementById('start-camera-btn');
    const startBarcodeBtn = document.getElementById('start-barcode-btn');

    // Ensure both buttons start enabled
    if (startCameraBtn) {
        startCameraBtn.disabled = false;
        startCameraBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Camera Scan';
        startCameraBtn.className = 'btn btn-warning fw-semibold';
    }

    if (startBarcodeBtn) {
        startBarcodeBtn.disabled = false;
        startBarcodeBtn.innerHTML = '<i class="fas fa-barcode me-2"></i>Barcode Reader';
        startBarcodeBtn.className = 'btn btn-outline-warning fw-semibold';
    }
}

/**
 * Initialize Camera QR Scanner
 */
function initializeQRScanner() {
    const startCameraBtn = document.getElementById('start-camera-btn');
    const startBarcodeBtn = document.getElementById('start-barcode-btn');
    const qrReader = document.getElementById('qr-reader');
    const scanResult = document.getElementById('scan-result');
    const scanDetails = document.getElementById('scan-details');

    let html5QrcodeScanner = null;
    let barcodeInputMode = false;

    // Camera scanning functionality
    if (startCameraBtn) {
        startCameraBtn.addEventListener('click', function() {
            if (html5QrcodeScanner) {
                // Stop existing scanner
                html5QrcodeScanner.clear().then(() => {
                    qrReader.style.display = 'none';
                    scanResult.style.display = 'none';
                    startCameraBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Camera Scan';
                    startCameraBtn.className = 'btn btn-warning fw-semibold';
                    startBarcodeBtn.disabled = false; // Re-enable barcode button
                    html5QrcodeScanner = null;
                    barcodeInputMode = false;
                }).catch(err => console.error('Error stopping scanner:', err));
            } else {
                // Start camera scanner
                qrReader.style.display = 'block';
                startCameraBtn.innerHTML = '<i class="fas fa-stop me-2"></i>Stop Camera';
                startCameraBtn.className = 'btn btn-danger fw-semibold';
                startBarcodeBtn.disabled = true;

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader",
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                        showTorchButtonIfSupported: true,
                        showZoomSliderIfSupported: true,
                        defaultZoomValueIfSupported: 2
                    }
                );

                html5QrcodeScanner.render(onScanSuccess, onScanError);
            }
        });
    }

    // Barcode reader functionality
    if (startBarcodeBtn) {
        startBarcodeBtn.addEventListener('click', function() {
            if (barcodeInputMode) {
                // Stop barcode input mode
                barcodeInputMode = false;
                startBarcodeBtn.innerHTML = '<i class="fas fa-barcode me-2"></i>Barcode Reader';
                startBarcodeBtn.className = 'btn btn-outline-warning fw-semibold';
                startCameraBtn.disabled = false;
                scanResult.style.display = 'none';

                // Remove the temporary input container if it exists
                const tempInputContainer = document.querySelector('.mt-3');
                if (tempInputContainer && tempInputContainer.contains(document.getElementById('temp-barcode-input'))) {
                    tempInputContainer.remove();
                }
            } else {
                // Start barcode input mode
                barcodeInputMode = true;
                startBarcodeBtn.innerHTML = '<i class="fas fa-stop me-2"></i>Stop Reader';
                startBarcodeBtn.className = 'btn btn-danger fw-semibold';
                startCameraBtn.disabled = true;

                // Create temporary input for barcode scanning
                const inputContainer = document.createElement('div');
                inputContainer.className = 'mt-3';
                inputContainer.innerHTML = `
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-barcode"></i>
                        </span>
                        <input type="text"
                               id="temp-barcode-input"
                               class="form-control"
                               placeholder="Click here and scan barcode..."
                               autocomplete="off"
                               style="font-family: monospace;">
                    </div>
                    <div class="small text-muted mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Focus on the input field above and scan any barcode
                    </div>
                `;

                qrReader.parentNode.insertBefore(inputContainer, qrReader);

                // Focus the input
                setTimeout(() => {
                    const tempInput = document.getElementById('temp-barcode-input');
                    if (tempInput) {
                        tempInput.focus();
                        initializeTempBarcodeReader(tempInput);
                    }
                }, 100);
            }
        });
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log('✅ QR Code scanned:', decodedText);

        // Stop scanner after successful scan
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            qrReader.style.display = 'none';
            startCameraBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Camera Scan';
            startCameraBtn.className = 'btn btn-warning fw-semibold';
            startBarcodeBtn.disabled = false;
            html5QrcodeScanner = null;
        }

        // Process the scanned data
        processScannedData(decodedText, 'camera');
    }

    function onScanError(errorMessage) {
        // Ignore errors during scanning
        console.log('Scan error (normal):', errorMessage);
    }
}

/**
 * Initialize Temporary Barcode Reader for Input Mode
 */
function initializeTempBarcodeReader(inputElement) {
    let scanBuffer = '';
    let lastScanTime = 0;
    const SCAN_TIMEOUT = 100; // ms between characters for a single scan

    inputElement.addEventListener('input', function(e) {
        const currentTime = Date.now();
        const inputValue = e.target.value;

        // If it's been more than SCAN_TIMEOUT since last input, start new scan
        if (currentTime - lastScanTime > SCAN_TIMEOUT) {
            scanBuffer = inputValue;
        } else {
            scanBuffer = inputValue;
        }

        lastScanTime = currentTime;

        // Clear any existing timeout
        if (window.barcodeTimeout) {
            clearTimeout(window.barcodeTimeout);
        }

        // Set timeout to process scan after SCAN_TIMEOUT
        window.barcodeTimeout = setTimeout(() => {
            if (scanBuffer.trim()) {
                processScannedData(scanBuffer.trim(), 'barcode');

                // Reset the interface
                const startBarcodeBtn = document.getElementById('start-barcode-btn');
                const startCameraBtn = document.getElementById('start-camera-btn');

                startBarcodeBtn.innerHTML = '<i class="fas fa-barcode me-2"></i>Barcode Reader';
                startBarcodeBtn.className = 'btn btn-outline-warning fw-semibold';
                startCameraBtn.disabled = false;

                // Remove the temporary input container
                const tempInputContainer = document.querySelector('.mt-3');
                if (tempInputContainer && tempInputContainer.contains(document.getElementById('temp-barcode-input'))) {
                    tempInputContainer.remove();
                }

                scanBuffer = '';
            }
        }, SCAN_TIMEOUT);
    });
}

/**
 * Process Scanned Data from Either Source
 */
function processScannedData(data, source) {
    const resultDiv = document.getElementById('scan-result');

    if (!resultDiv) {
        console.error('Result display element not found');
        return;
    }

    // Show loading state
    resultDiv.style.display = 'block';
    resultDiv.className = 'alert alert-info d-flex align-items-center mt-3';
    resultDiv.innerHTML = `
        <i class="fas fa-spinner fa-spin me-2"></i>
        <div>
            <strong class="small">Processing...</strong>
            <div class="small">Looking up item information</div>
        </div>
    `;

    // Send AJAX request to process QR code
    console.log('🔍 Processing QR code:', data);
    fetch('/qr/scan', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            qr_data: data
        })
    })
    .then(response => {
        console.log('📡 API Response status:', response.status);
        return response.json();
    })
    .then(responseData => {
        console.log('📦 API Response data:', responseData);
        if (responseData.success) {
            // Store data globally for modal access
            window.lastScannedItem = {
                item: responseData.item,
                redirect: responseData.redirect,
                scan_logged: responseData.scan_logged,
                message: responseData.message
            };

            // Hide the result div since we're auto-opening modal
            resultDiv.style.display = 'none';

            // Auto-open modal with detailed information
            showItemDetailsModal();
        } else {
            // Item not found - use Bootstrap alert (auto-dismiss only)
            resultDiv.className = 'alert alert-warning fade show mt-3';
            resultDiv.setAttribute('role', 'alert');
            resultDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Item Not Found</strong>
                <div class="small">${responseData.message || 'No item found with the scanned QR code'}</div>
            `;
            resultDiv.style.display = 'block';

            // Auto-hide after 3 seconds like other alerts
            setTimeout(() => {
                if (resultDiv.parentNode) {
                    resultDiv.style.display = 'none';
                }
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error looking up item:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center mt-3">
                <i class="fas fa-times-circle me-2"></i>
                <div>
                    <strong class="small">Error</strong>
                    <div class="small">Failed to lookup item. Please try again.</div>
                </div>
            </div>
        `;
    });
}

/**
 * Show Item Details Modal (Custom Implementation)
 */
function showItemDetailsModal() {
    const itemData = window.lastScannedItem;

    if (!itemData) {
        console.error('No item data available');
        return;
    }

    const modalContent = document.getElementById('customModalContent');
    const modalTitle = document.querySelector('.custom-modal-title');

    if (!modalContent || !modalTitle) {
        console.error('Modal elements not found');
        return;
    }

    // Update modal title
    modalTitle.innerHTML = `<i class="fas fa-box me-2"></i>${itemData.item.name}`;

    // Populate modal content with simplified fields
    modalContent.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Item Code</small>
                <strong class="d-block">${itemData.item.product_code || 'N/A'}</strong>
            </div>

            <div class="col-md-6">
                <small class="text-muted d-block">Category</small>
                <strong class="d-block">${itemData.item.category || 'N/A'}</strong>
            </div>

            <div class="col-md-6">
                <small class="text-muted d-block">Brand</small>
                <strong class="d-block">${itemData.item.brand || 'N/A'}</strong>
            </div>

            ${itemData.item.is_non_consumable ? `
            <div class="col-md-6">
                <small class="text-muted d-block">Status</small>
                <strong class="d-block">${itemData.item.quantity > 0 ? 'Available' : 'Assigned'}</strong>
            </div>
            ` : `
            <div class="col-md-6">
                <small class="text-muted d-block">Quantity</small>
                <strong class="d-block">${itemData.item.quantity || 0}</strong>
            </div>
            `}

            <div class="col-md-6">
                <small class="text-muted d-block">Location</small>
                <strong class="d-block">${itemData.item.location || 'Not assigned'}</strong>
            </div>

            <div class="col-md-6">
                <small class="text-muted d-block">Condition</small>
                <strong class="d-block">${itemData.item.condition || 'N/A'}</strong>
            </div>

            <div class="col-md-6">
                <small class="text-muted d-block">Current Holder</small>
                <strong class="d-block text-success">${itemData.item.holder ? itemData.item.holder.name : 'Not assigned'}</strong>
            </div>

            <div class="col-md-6">
                <small class="text-muted d-block">Last Scan</small>
                <strong class="d-block">${itemData.item.last_scan || 'Never'}</strong>
            </div>
        </div>

        <div class="mt-4 pt-3 border-top">
            <div class="d-flex gap-2 flex-wrap">
                <a href="${itemData.redirect || '#'}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i>View Details
                </a>
            </div>
        </div>
    `;

    // Show custom modal
    openCustomModal();

    // Ensure close buttons work by adding event listeners
    setTimeout(() => {
        const closeButtons = document.querySelectorAll('.custom-modal-close, .custom-modal-footer .btn-secondary');
        closeButtons.forEach(button => {
            button.addEventListener('click', closeCustomModal);
        });
    }, 100);
}

/**
 * Open Custom Modal
 */
function openCustomModal() {
    const modal = document.getElementById('customItemModal');
    if (modal) {
        modal.style.display = 'flex';
        // Trigger animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close Custom Modal
 */
function closeCustomModal() {
    const modal = document.getElementById('customItemModal');
    if (modal) {
        modal.classList.remove('show');

        // Hide after animation
        setTimeout(() => {
            modal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';

            // Clear scan result when modal closes
            const resultDiv = document.getElementById('scan-result');
            if (resultDiv) {
                resultDiv.innerHTML = '';
                resultDiv.className = '';
                resultDiv.style.cssText = 'display: none !important;';
            }
        }, 300);
    }
}

/**
 * Close modal when clicking outside or pressing ESC
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('customItemModal');
    if (modal) {
        // Click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCustomModal();
            }
        });
    }

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('customItemModal');
            if (modal && modal.classList.contains('show')) {
                closeCustomModal();
            }
        }
    });
});

// Action functions (placeholders for now)
function viewItemDetails(itemId) {
    console.log('View item details:', itemId);
    // TODO: Implement navigation to full item details page
}

function updateItemDetails(itemId) {
    // Redirect to the item detail page where admin can update condition and assignment
    window.location.href = `/items/${itemId}`;
}

function assignItem(itemId) {
    console.log('Assign item:', itemId);
    // TODO: Implement item assignment functionality
}

function returnItem(itemId) {
    console.log('Return item:', itemId);
    // TODO: Implement item return functionality
}

function logScan(itemId) {
    console.log('Log scan:', itemId);
    // TODO: Implement scan logging functionality
}

// Helper functions for badges and progress bars
function getConditionBadgeClass(condition) {
    switch (condition?.toLowerCase()) {
        case 'excellent':
        case 'new':
            return 'bg-success';
        case 'good':
            return 'bg-primary';
        case 'fair':
            return 'bg-warning';
        case 'poor':
        case 'damaged':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getStockStatusBadgeClass(status) {
    switch (status) {
        case 'In Stock':
            return 'bg-success';
        case 'Low Stock':
            return 'bg-warning';
        case 'Out of Stock':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getStockProgressClass(percentage) {
    if (percentage >= 75) return 'bg-success';
    if (percentage >= 25) return 'bg-warning';
    return 'bg-danger';
}

// Force cleanup of any lingering modal backdrops
function cleanupModalBackdrops() {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        if (backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
        }
    });

    // Reset body classes
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}