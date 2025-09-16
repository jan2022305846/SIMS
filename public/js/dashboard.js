let html5QrCode;
let scannerStarted = false;

// Dashboard QR Scanner Implementation
const DashboardQR = {
    init: function() {
        console.log('🔍 Dashboard QR Scanner: Initializing...');
        
        const startBtn = document.getElementById('start-scanner-btn');
        if (startBtn) {
            console.log('✅ Dashboard QR Scanner: Start button found');
            startBtn.addEventListener('click', this.startScanner.bind(this));
        } else {
            console.error('❌ Dashboard QR Scanner: Start button not found');
        }
    },

    startScanner: function() {
        console.log('🔍 Dashboard QR Scanner: Starting scanner');
        
        // Prevent multiple scanners from starting
        if (scannerStarted) {
            console.log('⚠️ Scanner already running, ignoring start request');
            return;
        }
        
        const qrReaderElement = document.getElementById('qr-reader');
        const startBtn = document.getElementById('start-scanner-btn');
        
        if (!qrReaderElement || !startBtn) {
            console.error('❌ Required elements not found');
            alert('Scanner elements not found. Please refresh the page.');
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
                            console.log('📷 Found back camera:', device.label);
                            break;
                        }
                    }
                    
                    console.log('📷 Using camera:', cameraId);
                    
                    html5QrCode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: function(viewfinderWidth, viewfinderHeight) {
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
                            html5QrCode.stop().then(() => {
                                scannerStarted = false;
                                this.processQRScan(decodedText);
                            });
                        },
                        (errorMessage) => {
                            // Silent error handling
                        }
                    ).then(() => {
                        console.log('✅ Scanner started successfully');
                        scannerStarted = true;
                        
                        startBtn.innerHTML = `
                            <i class="fas fa-stop me-2"></i>
                            Stop Scanner
                        `;
                        startBtn.disabled = false;
                        startBtn.className = 'btn btn-danger btn-lg fw-semibold';
                        
                        // Replace event listener
                        const newBtn = startBtn.cloneNode(true);
                        startBtn.parentNode.replaceChild(newBtn, startBtn);
                        newBtn.addEventListener('click', this.stopScanner.bind(this));
                        
                    }).catch(err => {
                        console.error('❌ Unable to start QR scanner:', err);
                        alert('Unable to start QR scanner: ' + err.message + '\n\nPlease ensure camera permissions are granted.');
                        this.resetScanner();
                    });
                } else {
                    console.error('❌ No cameras found');
                    alert('No cameras found. Please ensure you have a camera connected.');
                    this.resetScanner();
                }
            }).catch(err => {
                console.error('❌ Error getting cameras:', err);
                alert('Error accessing camera: ' + err.message + '\n\nPlease grant camera permissions.');
                this.resetScanner();
            });
            
        } catch (error) {
            console.error('❌ Error initializing scanner:', error);
            alert('Error initializing scanner: ' + error.message);
            this.resetScanner();
        }
    },

    stopScanner: function() {
        console.log('🛑 Stopping scanner');
        
        if (html5QrCode && scannerStarted) {
            html5QrCode.stop().then(() => {
                console.log('✅ Scanner stopped successfully');
                scannerStarted = false;
                this.resetScanner();
            }).catch(err => {
                console.error('❌ Error stopping scanner:', err);
                scannerStarted = false;
                this.resetScanner();
            });
        } else {
            console.log('⚠️ Scanner was not running, just resetting');
            scannerStarted = false;
            this.resetScanner();
        }
    },

    processQRScan: function(qrData) {
        console.log('🔄 Processing QR scan:', qrData);
        const startBtn = document.getElementById('start-scanner-btn');
        
        if (startBtn) {
            startBtn.innerHTML = `
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Processing...
            `;
            startBtn.disabled = true;
        }

        // Get CSRF token from meta tag or form
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value;

        fetch('/dashboard/scan-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                this.showScanResult(data.data);
                if (data.data.redirect_url) {
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 3000);
                }
            } else {
                alert('Scan failed: ' + data.message);
                this.resetScanner();
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            alert('Error processing scan. Please try again.');
            this.resetScanner();
        });
    },

    showScanResult: function(data) {
        const resultDiv = document.getElementById('scan-result');
        const detailsDiv = document.getElementById('scan-details');
        
        if (resultDiv && detailsDiv) {
            detailsDiv.innerHTML = `
                <div><strong>Item:</strong> ${data.item.name}</div>
                <div><strong>Category:</strong> ${data.item.category.name}</div>
                <div><strong>Stock:</strong> ${data.item.current_stock || 'N/A'}</div>
                <div><strong>Scanned at:</strong> ${data.scan_time}</div>
                <div class="mt-2 small">Redirecting to item details in 3 seconds...</div>
            `;
            
            resultDiv.style.display = 'block';
        }
        
        // Hide scanner
        const qrReader = document.getElementById('qr-reader');
        if (qrReader) {
            qrReader.style.display = 'none';
        }
    },

    resetScanner: function() {
        console.log('🔄 Resetting scanner');
        const startBtn = document.getElementById('start-scanner-btn');
        
        if (startBtn) {
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
            newStartBtn.addEventListener('click', this.startScanner.bind(this));
        }
        
        const qrReader = document.getElementById('qr-reader');
        const scanResult = document.getElementById('scan-result');
        
        if (qrReader) qrReader.style.display = 'none';
        if (scanResult) scanResult.style.display = 'none';
        
        scannerStarted = false;
    }
};

// Dashboard animations and interactions
const DashboardUI = {
    init: function() {
        this.initStatCards();
        this.initWelcomeAnimation();
    },

    initStatCards: function() {
        const statCards = document.querySelectorAll('.hover-lift');
        
        statCards.forEach((card, index) => {
            // Staggered entrance animation
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    },

    initWelcomeAnimation: function() {
        const welcomeCard = document.querySelector('.welcome-section');
        if (welcomeCard) {
            welcomeCard.style.opacity = '0';
            welcomeCard.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                welcomeCard.style.transition = 'all 0.8s ease';
                welcomeCard.style.opacity = '1';
                welcomeCard.style.transform = 'translateY(0)';
            }, 200);
        }
    }
};

// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard JavaScript initialized');
    
    // Check for HTML5QRCode library
    if (typeof Html5Qrcode === 'undefined') {
        console.error('❌ HTML5QRCode library not loaded');
    } else {
        console.log('✅ HTML5QRCode library loaded successfully');
        DashboardQR.init();
    }
    
    // Initialize UI components
    DashboardUI.init();
});

// Export for global access
window.DashboardQR = DashboardQR;
window.DashboardUI = DashboardUI;
