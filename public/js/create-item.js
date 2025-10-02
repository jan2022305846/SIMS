// Create Item Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Create item form loaded');
    
    // Load QuaggaJS dynamically for barcode scanning (optional)
    try {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
        script.onload = function() {
            console.log('QuaggaJS loaded successfully');
            initializeBarcodeScanner();
        };
        script.onerror = function() {
            console.warn('Failed to load QuaggaJS, barcode scanning disabled');
            // Continue without barcode scanning
        };
        document.head.appendChild(script);
    } catch (error) {
        console.warn('Error loading QuaggaJS:', error);
    }

    // Dynamic form behavior based on item type
    const itemTypeSelect = document.getElementById('item_type');
    const productDetailsSection = document.getElementById('product-details-section');
    const nonConsumableFields = document.getElementById('non-consumable-fields');
    const locationInput = document.getElementById('location');
    const conditionSelect = document.getElementById('condition');

    if (!itemTypeSelect) {
        console.error('Item type select not found');
        return;
    }

    function updateFormVisibility() {
        const selectedType = itemTypeSelect.value;
        console.log('Item type changed to:', selectedType);

        if (selectedType) {
            // Show product details section
            if (productDetailsSection) {
                productDetailsSection.classList.remove('d-none');
            }

            if (selectedType === 'non_consumable') {
                // Show non-consumable specific fields
                if (nonConsumableFields) nonConsumableFields.classList.remove('d-none');

                // Make location and condition required
                if (locationInput) locationInput.setAttribute('required', 'required');
                if (conditionSelect) conditionSelect.setAttribute('required', 'required');
            } else {
                // Hide non-consumable specific fields
                if (nonConsumableFields) nonConsumableFields.classList.add('d-none');

                // Remove required attributes
                if (locationInput) locationInput.removeAttribute('required');
                if (conditionSelect) conditionSelect.removeAttribute('required');
            }
        } else {
            // Hide product details section when no type is selected
            if (productDetailsSection) productDetailsSection.classList.add('d-none');
            if (nonConsumableFields) nonConsumableFields.classList.add('d-none');

            // Remove required attributes
            if (locationInput) locationInput.removeAttribute('required');
            if (conditionSelect) conditionSelect.removeAttribute('required');
        }
    }

    // Listen for item type changes
    itemTypeSelect.addEventListener('change', updateFormVisibility);

    // Initialize form visibility on page load
    updateFormVisibility();
});

function initializeBarcodeScanner() {
    try {
        console.log('Initializing barcode scanner');
        
        let scannerActive = false;
        let scannerContainer = null;

        const scanBtn = document.getElementById('scan-barcode-btn');
        const manualBtn = document.getElementById('manual-barcode-btn');
        const barcodeInput = document.getElementById('product_code');

        if (!scanBtn || !manualBtn || !barcodeInput) {
            console.warn('Barcode scanner elements not found, skipping initialization');
            return;
        }

        // Scan barcode button
        scanBtn.addEventListener('click', function() {
            if (scannerActive) {
                stopScanner();
            } else {
                startScanner();
            }
        });

        // Manual entry button
        manualBtn.addEventListener('click', function() {
            stopScanner();
            barcodeInput.focus();
            barcodeInput.placeholder = "Enter barcode manually";
        });

        function startScanner() {
            try {
                scannerActive = true;

                // Create scanner container
                scannerContainer = document.createElement('div');
                scannerContainer.id = 'barcode-scanner-container';
                scannerContainer.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 9999;
                    background: white;
                    border: 2px solid #007bff;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                    width: 400px;
                    max-width: 90vw;
                `;

                scannerContainer.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Barcode Scanner</h5>
                        <button type="button" class="btn-close" id="close-scanner"></button>
                    </div>
                    <div id="scanner-viewport" style="width: 100%; height: 300px; border: 1px solid #ddd;"></div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">Position barcode in front of camera</small>
                    </div>
                `;

                document.body.appendChild(scannerContainer);

                // Initialize Quagga
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#scanner-viewport'),
                        constraints: {
                            width: 640,
                            height: 480,
                            facingMode: "environment" // Use back camera on mobile
                        }
                    },
                    locator: {
                        patchSize: "medium",
                        halfSample: true
                    },
                    numOfWorkers: 2,
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "code_39_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "codabar_reader"
                        ]
                    },
                    locate: true
                }, function(err) {
                    if (err) {
                        console.error('Error initializing camera:', err);
                        alert('Error initializing camera: ' + err.message);
                        stopScanner();
                        return;
                    }
                    Quagga.start();
                });

                // Handle barcode detection
                Quagga.onDetected(function(result) {
                    const code = result.codeResult.code;
                    barcodeInput.value = code;
                    stopScanner();

                    // Show success message
                    showToast('Barcode scanned successfully!', 'success');
                });

                // Close scanner button
                document.getElementById('close-scanner').addEventListener('click', stopScanner);
            } catch (error) {
                console.error('Error starting scanner:', error);
                stopScanner();
            }
        }

        function stopScanner() {
            try {
                scannerActive = false;

                if (scannerContainer) {
                    document.body.removeChild(scannerContainer);
                    scannerContainer = null;
                }

                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                }

                scanBtn.innerHTML = '<i class="fas fa-qrcode"></i>';
                scanBtn.classList.remove('btn-danger');
                scanBtn.classList.add('btn-outline-primary');
                scanBtn.title = 'Scan Barcode';
            } catch (error) {
                console.error('Error stopping scanner:', error);
            }
        }

        function showToast(message, type = 'info') {
            try {
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                toast.style.cssText = `
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    min-width: 300px;
                `;
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 3000);
            } catch (error) {
                console.error('Error showing toast:', error);
            }
        }
    } catch (error) {
        console.error('Error initializing barcode scanner:', error);
    }
}