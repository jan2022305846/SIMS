@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Simple QR Scanner Test</h3>
                    </div>
                    <div class="card-body text-center">
                        <p>This is a simple test to see if the button works at all.</p>
                        
                        <button id="test-button" class="btn btn-success btn-lg me-3">
                            <i class="fas fa-play me-1"></i>Test Button
                        </button>
                        
                        <button id="start-scanner" class="btn btn-primary btn-lg">
                            <i class="fas fa-camera me-1"></i>Start Scanner
                        </button>
                        
                        <div id="status" class="mt-4 alert alert-info">
                            Click the buttons above to test functionality.
                        </div>
                        
                        <div id="qr-reader" style="width: 300px; margin: 20px auto; border: 2px solid #ddd;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
console.log('Script loaded');

// Test immediately when page loads
window.addEventListener('load', function() {
    console.log('Page loaded');
    
    const testButton = document.getElementById('test-button');
    const startButton = document.getElementById('start-scanner');
    const status = document.getElementById('status');
    
    console.log('Test button:', testButton);
    console.log('Start button:', startButton);
    
    // Simple test button
    testButton.onclick = function() {
        console.log('Test button clicked!');
        status.innerHTML = '<strong>Test button works!</strong> ✅';
        status.className = 'mt-4 alert alert-success';
    };
    
    // QR Scanner button
    startButton.onclick = function() {
        console.log('Start scanner clicked!');
        status.innerHTML = '<strong>Scanner button clicked!</strong> Now testing camera...';
        status.className = 'mt-4 alert alert-warning';
        
        // Test HTML5QRCode
        if (typeof Html5Qrcode === 'undefined') {
            status.innerHTML = '<strong>Error:</strong> HTML5QRCode library not loaded!';
            status.className = 'mt-4 alert alert-danger';
            return;
        }
        
        try {
            const html5QrCode = new Html5Qrcode("qr-reader");
            
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    status.innerHTML = `<strong>Success!</strong> Found ${devices.length} camera(s). Starting scanner...`;
                    status.className = 'mt-4 alert alert-success';
                    
                    // Prefer back camera on mobile devices
                    let cameraId = devices[0].id;
                    
                    // Look for back/environment camera on mobile
                    for (let device of devices) {
                        if (device.label && (
                            device.label.toLowerCase().includes('back') ||
                            device.label.toLowerCase().includes('environment') ||
                            device.label.toLowerCase().includes('rear')
                        )) {
                            cameraId = device.id;
                            console.log('Found back camera:', device.label);
                            break;
                        }
                    }
                    
                    html5QrCode.start(
                        cameraId,
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            status.innerHTML = `<strong>QR Code Detected:</strong> ${decodedText}`;
                            status.className = 'mt-4 alert alert-success';
                            html5QrCode.stop();
                        },
                        (errorMessage) => {
                            // Silent - this happens during scanning
                        }
                    ).catch(err => {
                        status.innerHTML = `<strong>Scanner Error:</strong> ${err.message}`;
                        status.className = 'mt-4 alert alert-danger';
                    });
                } else {
                    status.innerHTML = '<strong>No cameras found!</strong> Make sure you have a camera and grant permissions.';
                    status.className = 'mt-4 alert alert-danger';
                }
            }).catch(err => {
                status.innerHTML = `<strong>Camera Access Error:</strong> ${err.message}`;
                status.className = 'mt-4 alert alert-danger';
            });
            
        } catch (error) {
            status.innerHTML = `<strong>Scanner Init Error:</strong> ${error.message}`;
            status.className = 'mt-4 alert alert-danger';
        }
    };
    
    // Also add event listeners
    testButton.addEventListener('click', function() {
        console.log('Test button event listener fired');
    });
    
    startButton.addEventListener('click', function() {
        console.log('Start button event listener fired');
    });
});
</script>
@endsection
