// Create Item Form JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Load QuaggaJS dynamically for barcode scanning
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
    script.onload = function() {
        initializeBarcodeScanner();
    };
    document.head.appendChild(script);

    // Dynamic form behavior based on item type
    const itemTypeSelect = document.getElementById('item_type');
    const productDetailsSection = document.getElementById('product-details-section');
    const locationField = document.getElementById('location-field');
    const nonConsumableFields = document.getElementById('non-consumable-fields');
    const locationInput = document.getElementById('location');
    const conditionSelect = document.getElementById('condition');

    function updateFormVisibility() {
        const selectedType = itemTypeSelect.value;

        if (selectedType) {
            // Show product details section
            productDetailsSection.classList.remove('d-none');

            if (selectedType === 'non_consumable') {
                // Show non-consumable specific fields
                locationField.classList.remove('d-none');
                nonConsumableFields.classList.remove('d-none');

                // Make location and condition required
                locationInput.setAttribute('required', 'required');
                conditionSelect.setAttribute('required', 'required');
            } else {
                // Hide non-consumable specific fields
                locationField.classList.add('d-none');
                nonConsumableFields.classList.add('d-none');

                // Remove required attributes
                locationInput.removeAttribute('required');
                conditionSelect.removeAttribute('required');
            }
        } else {
            // Hide product details section when no type is selected
            productDetailsSection.classList.add('d-none');
            locationField.classList.add('d-none');
            nonConsumableFields.classList.add('d-none');

            // Remove required attributes
            locationInput.removeAttribute('required');
            conditionSelect.removeAttribute('required');
        }
    }

    // Listen for item type changes
    itemTypeSelect.addEventListener('change', updateFormVisibility);

    // Initialize form visibility on page load
    updateFormVisibility();
});

function initializeBarcodeScanner() {
    let scannerActive = false;
    let scannerContainer = null;

    const scanBtn = document.getElementById('scan-barcode-btn');
    const manualBtn = document.getElementById('manual-barcode-btn');
    const barcodeInput = document.getElementById('product_code');

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
                console.error(err);
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
    }

    function stopScanner() {
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
    }

    function showToast(message, type = 'info') {
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
    }
}