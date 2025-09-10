@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">QR Code Scanner</h1>
                <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>

            <!-- Scanner Instructions -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Instructions:</strong> Position the QR code within the scanner frame. The scanner will automatically detect and process the code when it's clearly visible.
                        </p>
                    </div>
                </div>
            </div>

            <!-- QR Scanner Area -->
            <div class="text-center">
                <div id="qr-scanner" class="inline-block">
                    <div id="qr-video-container" class="relative mx-auto mb-4">
                        <video id="qr-video" class="w-full max-w-md rounded-lg border-2 border-gray-300" autoplay></video>
                        <div class="absolute inset-0 border-4 border-red-500 opacity-50 rounded-lg pointer-events-none" id="qr-overlay">
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-48 h-48 border-4 border-red-500 bg-transparent"></div>
                        </div>
                    </div>
                    
                    <div class="space-x-4">
                        <button id="start-scanner" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Start Scanner
                        </button>
                        <button id="stop-scanner" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" disabled>
                            Stop Scanner
                        </button>
                    </div>
                </div>

                <!-- Manual Input Option -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Manual QR Code Input</h3>
                    <div class="flex space-x-2">
                        <input type="text" id="manual-qr-input" placeholder="Paste QR code data here..." 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <button id="process-manual-qr" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Process
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scanner Status -->
            <div id="scanner-status" class="mt-4 text-center text-gray-600"></div>

            <!-- Scan Results -->
            <div id="scan-results" class="mt-6 hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Scan Successful!</h3>
                    <div id="scan-results-content"></div>
                    <div class="mt-4">
                        <button id="view-item" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                            View Item Details
                        </button>
                        <button id="scan-another" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Scan Another
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error Messages -->
            <div id="error-message" class="mt-4 hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
                    <p id="error-text" class="text-red-700"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner JavaScript -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let html5QrCode = null;
    let scannerStarted = false;

    const startButton = document.getElementById('start-scanner');
    const stopButton = document.getElementById('stop-scanner');
    const statusDiv = document.getElementById('scanner-status');
    const resultsDiv = document.getElementById('scan-results');
    const errorDiv = document.getElementById('error-message');
    const manualInput = document.getElementById('manual-qr-input');
    const processManualButton = document.getElementById('process-manual-qr');

    // Initialize scanner
    function initScanner() {
        html5QrCode = new Html5Qrcode("qr-video-container");
    }

    // Start scanner
    function startScanner() {
        if (!html5QrCode) initScanner();

        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                const cameraId = devices[0].id;
                
                html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    (decodedText, decodedResult) => {
                        processQRCode(decodedText);
                        stopScanner();
                    },
                    (errorMessage) => {
                        // Handle scan errors silently
                    }
                ).then(() => {
                    scannerStarted = true;
                    startButton.disabled = true;
                    stopButton.disabled = false;
                    statusDiv.textContent = 'Scanner active. Position QR code in the frame.';
                }).catch(err => {
                    showError('Failed to start scanner: ' + err);
                });
            } else {
                showError('No cameras found.');
            }
        }).catch(err => {
            showError('Error accessing cameras: ' + err);
        });
    }

    // Stop scanner
    function stopScanner() {
        if (html5QrCode && scannerStarted) {
            html5QrCode.stop().then(() => {
                scannerStarted = false;
                startButton.disabled = false;
                stopButton.disabled = true;
                statusDiv.textContent = 'Scanner stopped.';
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }
    }

    // Process QR code data
    function processQRCode(qrData) {
        hideMessages();
        statusDiv.textContent = 'Processing QR code...';

        fetch('{{ route("qr.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: qrData })
        })
        .then(response => response.json())
        .then(data => {
            statusDiv.textContent = '';
            
            if (data.success) {
                showScanResults(data.item, data.redirect);
            } else {
                showError(data.message || 'Failed to process QR code.');
            }
        })
        .catch(error => {
            statusDiv.textContent = '';
            showError('Network error: ' + error.message);
        });
    }

    // Show scan results
    function showScanResults(item, redirectUrl) {
        hideMessages();
        
        const resultsContent = document.getElementById('scan-results-content');
        resultsContent.innerHTML = `
            <div class="space-y-2">
                <p><strong>Item:</strong> ${item.name}</p>
                <p><strong>Category:</strong> ${item.category.name}</p>
                <p><strong>Location:</strong> ${item.location}</p>
                <p><strong>Current Stock:</strong> ${item.current_stock || item.quantity}</p>
                <p><strong>Condition:</strong> ${item.condition}</p>
            </div>
        `;
        
        document.getElementById('view-item').onclick = function() {
            window.location.href = redirectUrl;
        };
        
        resultsDiv.classList.remove('hidden');
    }

    // Show error message
    function showError(message) {
        hideMessages();
        document.getElementById('error-text').textContent = message;
        errorDiv.classList.remove('hidden');
    }

    // Hide all messages
    function hideMessages() {
        resultsDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
    }

    // Event listeners
    startButton.addEventListener('click', startScanner);
    stopButton.addEventListener('click', stopScanner);
    
    processManualButton.addEventListener('click', function() {
        const qrData = manualInput.value.trim();
        if (qrData) {
            processQRCode(qrData);
        } else {
            showError('Please enter QR code data.');
        }
    });
    
    document.getElementById('scan-another').addEventListener('click', function() {
        hideMessages();
        manualInput.value = '';
        startScanner();
    });

    // Initialize
    initScanner();
});
</script>
@endsection
