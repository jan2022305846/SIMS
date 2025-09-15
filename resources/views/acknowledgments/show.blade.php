@extends('layouts.app')

@section('title', 'Digital Acknowledgment - Request #' . $request->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Digital Acknowledgment</h2>
                            <p class="text-muted mb-0">Request #{{ $request->id }} - {{ $request->status_label }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6">Ready for Acknowledgment</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Request Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 140px;">Requested By:</td>
                                    <td>{{ $request->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Department:</td>
                                    <td>{{ $request->user->department ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date Requested:</td>
                                    <td>{{ $request->created_at->format('M d, Y - h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Purpose:</td>
                                    <td>{{ $request->purpose }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 140px;">Status:</td>
                                    <td><span class="badge bg-{{ $request->status_color }}">{{ $request->status_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Priority:</td>
                                    <td>
                                        <span class="badge bg-{{ $request->priority === 'high' ? 'danger' : ($request->priority === 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($request->priority) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date Needed:</td>
                                    <td>{{ $request->date_needed ? \Carbon\Carbon::parse($request->date_needed)->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Notes:</td>
                                    <td>{{ $request->notes ?? 'None' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Items to be Received</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Quantity Requested</th>
                                    <th>Quantity Approved</th>
                                    <th>Unit</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($request->items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item->name }}</td>
                                    <td>{{ $item->category->name ?? 'N/A' }}</td>
                                    <td>{{ $item->pivot->quantity_requested }}</td>
                                    <td>{{ $item->pivot->quantity_approved ?? $item->pivot->quantity_requested }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->description ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Digital Signature Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-signature me-2"></i>Digital Acknowledgment & Signature</h5>
                </div>
                <div class="card-body">
                    @if($acknowledgment)
                        <!-- Already Acknowledged -->
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fs-4 me-3"></i>
                                <div>
                                    <h5 class="mb-1">Request Already Acknowledged</h5>
                                    <p class="mb-0">This request was acknowledged on {{ $acknowledgment->created_at->format('M d, Y - h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6>Acknowledgment Details:</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="fw-bold">Acknowledged By:</td>
                                        <td>{{ $acknowledgment->acknowledgedBy->name }}</td>
                                    </tr>
                                    @if($acknowledgment->witnessed_by)
                                    <tr>
                                        <td class="fw-bold">Witnessed By:</td>
                                        <td>{{ $acknowledgment->witnessedBy->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="fw-bold">Receipt Number:</td>
                                        <td>{{ $acknowledgment->receipt_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date:</td>
                                        <td>{{ $acknowledgment->created_at->format('M d, Y - h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if($acknowledgment->signature_path)
                                <h6>Digital Signature:</h6>
                                <div class="border rounded p-2 bg-light" style="max-width: 300px;">
                                    <img src="{{ Storage::url($acknowledgment->signature_path) }}" 
                                         alt="Digital Signature" 
                                         class="img-fluid" 
                                         style="max-height: 150px;">
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('requests.acknowledgment.receipt', $request) }}" 
                               class="btn btn-primary me-2">
                                <i class="fas fa-receipt me-1"></i> View Receipt
                            </a>
                            <a href="{{ route('requests.acknowledgment.download', $request) }}" 
                               class="btn btn-outline-primary me-2">
                                <i class="fas fa-download me-1"></i> Download Receipt
                            </a>
                            <a href="{{ route('requests.acknowledgment.verify', $request) }}" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-shield-alt me-1"></i> Verify Integrity
                            </a>
                        </div>
                    @else
                        <!-- Acknowledgment Form -->
                        <form id="acknowledgmentForm" action="{{ route('requests.acknowledgment.store', $request) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Acknowledgment Information -->
                                    <h6 class="mb-3">Acknowledgment Information</h6>
                                    
                                    <div class="mb-3">
                                        <label for="acknowledged_by" class="form-label">Acknowledged By <span class="text-danger">*</span></label>
                                        <select class="form-select @error('acknowledged_by') is-invalid @enderror" 
                                                id="acknowledged_by" name="acknowledged_by" required>
                                            <option value="">Select Person</option>
                                            <option value="{{ auth()->id() }}" {{ old('acknowledged_by') == auth()->id() ? 'selected' : '' }}>
                                                {{ auth()->user()->name }} (Me)
                                            </option>
                                            @if($request->user_id !== auth()->id())
                                            <option value="{{ $request->user_id }}" {{ old('acknowledged_by') == $request->user_id ? 'selected' : '' }}>
                                                {{ $request->user->name }} (Requester)
                                            </option>
                                            @endif
                                        </select>
                                        @error('acknowledged_by')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="witnessed_by" class="form-label">Witnessed By (Optional)</label>
                                        <select class="form-select @error('witnessed_by') is-invalid @enderror" 
                                                id="witnessed_by" name="witnessed_by">
                                            <option value="">Select Witness (Optional)</option>
                                            @foreach(\App\Models\User::where('id', '!=', auth()->id())->where('role', '!=', 'faculty')->get() as $user)
                                            <option value="{{ $user->id }}" {{ old('witnessed_by') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ ucfirst($user->role) }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('witnessed_by')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="received_condition" class="form-label">Condition of Items Received <span class="text-danger">*</span></label>
                                        <select class="form-select @error('received_condition') is-invalid @enderror" 
                                                id="received_condition" name="received_condition" required>
                                            <option value="">Select Condition</option>
                                            <option value="excellent" {{ old('received_condition') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                            <option value="good" {{ old('received_condition') == 'good' ? 'selected' : '' }}>Good</option>
                                            <option value="fair" {{ old('received_condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                            <option value="damaged" {{ old('received_condition') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                        </select>
                                        @error('received_condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Additional Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="3" 
                                                  placeholder="Any additional comments or observations...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="photo_evidence" class="form-label">Photo Evidence (Optional)</label>
                                        <input type="file" class="form-control @error('photo_evidence') is-invalid @enderror" 
                                               id="photo_evidence" name="photo_evidence" accept="image/*">
                                        <div class="form-text">Upload a photo of the received items (JPG, PNG, max 5MB)</div>
                                        @error('photo_evidence')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Digital Signature -->
                                    <h6 class="mb-3">Digital Signature <span class="text-danger">*</span></h6>
                                    
                                    <div class="mb-3">
                                        <div class="border rounded p-3 bg-light">
                                            <canvas id="signatureCanvas" 
                                                    width="400" 
                                                    height="200" 
                                                    style="border: 1px solid #ddd; background: white; width: 100%; cursor: crosshair;">
                                                Your browser does not support the canvas element.
                                            </canvas>
                                            <input type="hidden" id="signature_data" name="signature_data">
                                            @error('signature_data')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mt-2">
                                            <button type="button" id="clearSignature" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eraser me-1"></i> Clear Signature
                                            </button>
                                            <small class="text-muted align-self-center">Please sign above</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Location Information (Auto-captured) -->
                                    <h6 class="mb-3">Location Information</h6>
                                    <div class="alert alert-info">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <span id="locationStatus">Attempting to get your location...</span>
                                    </div>
                                    <input type="hidden" id="gps_coordinates" name="gps_coordinates">
                                    
                                    <!-- Terms and Conditions -->
                                    <div class="mt-4">
                                        <div class="form-check">
                                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                                   type="checkbox" id="terms_accepted" name="terms_accepted" required>
                                            <label class="form-check-label" for="terms_accepted">
                                                I acknowledge that I have received the above items in the specified condition and quantity. 
                                                This digital signature is legally binding.
                                            </label>
                                            @error('terms_accepted')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('requests.manage') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Requests
                                </a>
                                
                                <button type="submit" class="btn btn-success btn-lg" id="submitAcknowledgment" disabled>
                                    <i class="fas fa-signature me-2"></i> Submit Digital Acknowledgment
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$acknowledgment)
<!-- JavaScript for Digital Signature -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Signature Canvas Setup
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    const signatureData = document.getElementById('signature_data');
    const submitButton = document.getElementById('submitAcknowledgment');
    const termsCheckbox = document.getElementById('terms_accepted');
    const clearButton = document.getElementById('clearSignature');
    
    let isDrawing = false;
    let hasSignature = false;
    
    // Set canvas size properly
    const rect = canvas.getBoundingClientRect();
    canvas.width = 400;
    canvas.height = 200;
    
    // Drawing functions
    function startDrawing(e) {
        isDrawing = true;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.beginPath();
        
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        const x = (e.clientX - rect.left) * scaleX;
        const y = (e.clientY - rect.top) * scaleY;
        
        ctx.moveTo(x, y);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        const x = (e.clientX - rect.left) * scaleX;
        const y = (e.clientY - rect.top) * scaleY;
        
        ctx.lineTo(x, y);
        ctx.stroke();
        
        hasSignature = true;
        updateSignatureData();
        checkFormValidity();
    }
    
    function stopDrawing() {
        isDrawing = false;
    }
    
    // Touch events for mobile
    function getTouchPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        return {
            x: (e.touches[0].clientX - rect.left) * scaleX,
            y: (e.touches[0].clientY - rect.top) * scaleY
        };
    }
    
    function touchStart(e) {
        e.preventDefault();
        isDrawing = true;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.beginPath();
        
        const touch = getTouchPos(e);
        ctx.moveTo(touch.x, touch.y);
    }
    
    function touchMove(e) {
        e.preventDefault();
        if (!isDrawing) return;
        
        const touch = getTouchPos(e);
        ctx.lineTo(touch.x, touch.y);
        ctx.stroke();
        
        hasSignature = true;
        updateSignatureData();
        checkFormValidity();
    }
    
    function touchEnd(e) {
        e.preventDefault();
        isDrawing = false;
    }
    
    // Event listeners
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    canvas.addEventListener('touchstart', touchStart);
    canvas.addEventListener('touchmove', touchMove);
    canvas.addEventListener('touchend', touchEnd);
    
    // Clear signature
    clearButton.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        signatureData.value = '';
        checkFormValidity();
    });
    
    // Update signature data
    function updateSignatureData() {
        signatureData.value = canvas.toDataURL();
    }
    
    // Check form validity
    function checkFormValidity() {
        const isValid = hasSignature && termsCheckbox.checked;
        submitButton.disabled = !isValid;
    }
    
    // Terms checkbox listener
    termsCheckbox.addEventListener('change', checkFormValidity);
    
    // Get user location
    function getUserLocation() {
        const locationStatus = document.getElementById('locationStatus');
        const gpsInput = document.getElementById('gps_coordinates');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    
                    gpsInput.value = JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        accuracy: accuracy,
                        timestamp: new Date().toISOString()
                    });
                    
                    locationStatus.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Location captured successfully (±${Math.round(accuracy)}m accuracy)`;
                },
                function(error) {
                    let errorMessage = 'Location access denied or unavailable.';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location access denied by user.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out.';
                            break;
                    }
                    locationStatus.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i> ${errorMessage}`;
                    gpsInput.value = JSON.stringify({error: errorMessage});
                }
            );
        } else {
            locationStatus.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i> Geolocation not supported by browser.';
            gpsInput.value = JSON.stringify({error: 'Geolocation not supported'});
        }
    }
    
    // Initialize location capture
    getUserLocation();
    
    // Form submission validation
    document.getElementById('acknowledgmentForm').addEventListener('submit', function(e) {
        if (!hasSignature) {
            e.preventDefault();
            alert('Please provide your digital signature before submitting.');
            return false;
        }
        
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert('Please accept the terms and conditions before submitting.');
            return false;
        }
        
        // Show loading state
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
        submitButton.disabled = true;
    });
});
</script>
@endif

@endsection
