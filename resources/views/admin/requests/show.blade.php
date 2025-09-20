@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <div>
                        <h2 class="h3 fw-semibold text-dark mb-0">
                            <i class="fas fa-eye me-2 text-warning"></i>
                            Request Details
                        </h2>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('requests.manage') }}" class="text-decoration-none">
                                        <i class="fas fa-clipboard-list me-1"></i>Requests
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Request #{{ $request->id }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        @if($request->isFulfilled() || $request->isClaimed())
                            <a href="{{ route('requests.claim-slip', $request) }}" class="btn btn-warning fw-bold" target="_blank">
                                <i class="fas fa-print me-1"></i>Print Claim Slip
                            </a>
                        @endif
                        <a href="{{ route('requests.manage') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Main Request Details -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Request Information
                                </h5>
                            </div>
                            <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Requester Details</h6>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-white fa-lg"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $request->user->name }}</h5>
                                            <p class="text-muted mb-0">{{ $request->user->email }}</p>
                                            <small class="text-muted">{{ ucfirst($request->user->role) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request Status</h6>
                                <div class="mb-3">
                                    <span class="badge fs-6 px-3 py-2
                                        @switch($request->workflow_status)
                                            @case('pending') bg-warning @break
                                            @case('approved_by_admin') bg-success @break
                                            @case('ready_for_pickup') bg-info text-white @break
                                            @case('fulfilled') bg-purple text-white @break
                                            @case('claimed') bg-secondary @break
                                            @default bg-danger @break
                                        @endswitch">
                                        {{ $request->getStatusDisplayName() }}
                                    </span>
                                    <div class="mt-2">
                                        <span class="badge
                                            @switch($request->priority)
                                                @case('low') bg-success @break
                                                @case('normal') bg-primary @break
                                                @case('high') bg-warning @break
                                                @case('urgent') bg-danger @break
                                            @endswitch">
                                            {{ ucfirst($request->priority) }} Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Item Details</h6>
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h5 class="mb-2">{{ $request->item->name }}</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Requested Quantity</small>
                                                <div class="fw-bold fs-5">{{ $request->quantity }} {{ $request->item->unit ?? 'pcs' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Available Stock</small>
                                                <div class="fw-bold fs-5 {{ $request->item->current_stock < $request->quantity ? 'text-danger' : 'text-success' }}">
                                                    {{ $request->item->current_stock }} {{ $request->item->unit ?? 'pcs' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request Details</h6>
                                <div class="mb-3">
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Department:</strong></div>
                                        <div class="col-7">{{ $request->department }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Request Date:</strong></div>
                                        <div class="col-7">{{ $request->request_date ? $request->request_date->format('M j, Y g:i A') : 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Needed Date:</strong></div>
                                        <div class="col-7">
                                            {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}
                                            @if($request->needed_date && $request->needed_date->isPast() && !$request->isClaimed())
                                                <span class="badge bg-warning ms-1">Overdue</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($request->claim_slip_number)
                                        <div class="row mb-2">
                                            <div class="col-5"><strong>Claim Slip:</strong></div>
                                            <div class="col-7">
                                                <code>{{ $request->claim_slip_number }}</code>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Purpose</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $request->purpose }}
                                </div>
                            </div>
                        </div>

                        @if($request->attachments && count($request->attachments) > 0)
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted mb-2">Attachments</h6>
                                    <div class="list-group list-group-flush">
                                        @foreach($request->attachments as $attachment)
                                            <div class="list-group-item bg-light d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-paperclip me-2"></i>
                                                    {{ $attachment['filename'] }}
                                                    <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 1) }} KB)</small>
                                                </div>
                                                <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Workflow Timeline & Actions -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                @if(auth()->user()->isAdmin())
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Admin Actions -->
                            @if($request->canBeApprovedByAdmin())
                                <form method="POST" action="{{ route('requests.approve-admin', $request) }}" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-2"></i>Approve Request
                                    </button>
                                </form>
                            @else
                                @if($request->workflow_status === 'declined_by_admin')
                                    <div class="alert alert-danger mb-2">
                                        <i class="fas fa-ban me-2"></i>
                                        <strong>This request was declined</strong>
                                        @if($request->admin_notes)
                                            <br><small>Reason: {{ $request->admin_notes }}</small>
                                        @endif
                                    </div>
                                @elseif($request->workflow_status === 'approved_by_admin')
                                    <div class="alert alert-success mb-2">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>This request has already been approved</strong>
                                    </div>
                                @elseif(in_array($request->workflow_status, ['fulfilled', 'claimed']))
                                    <div class="alert alert-info mb-2">
                                        <i class="fas fa-check-double me-2"></i>
                                        <strong>This request has been completed</strong>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>This request cannot be approved at this stage</strong>
                                    </div>
                                @endif
                            @endif
                            
                            @if($request->canBeFulfilled())
                                <div class="mb-3">
                                    <label for="item_barcode" class="form-label fw-medium">Scan Item Barcode</label>
                                    <div class="input-group">
                                        <input type="text" name="item_barcode" id="item_barcode" 
                                               class="form-control" placeholder="Scan or enter item barcode" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="scan-item-barcode-btn" title="Scan Barcode">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="manual-item-barcode-btn" title="Manual Entry">
                                            <i class="fas fa-keyboard"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Scan the item's barcode to verify and display item details
                                        </small>
                                    </div>
                                </div>
                                
                                <div id="scanned-item-details" class="mb-3" style="display: none;">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>Item Verified
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="item-details-content">
                                                <!-- Item details will be populated here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="{{ route('requests.fulfill', $request) }}" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="scanned_barcode" id="scanned_barcode_input">
                                    <button type="submit" class="btn btn-primary w-100" id="fulfill-btn" disabled>
                                        <i class="fas fa-box me-2"></i>Fulfill Request
                                    </button>
                                </form>
                            @endif
                            
                            @if($request->canBeClaimed())
                                <div class="mb-3">
                                    <label for="claim_barcode" class="form-label fw-medium">Scan Claim Slip Barcode</label>
                                    <div class="input-group">
                                        <input type="text" name="claim_barcode" id="claim_barcode" 
                                               class="form-control" placeholder="Scan claim slip barcode" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="scan-claim-barcode-btn" title="Scan Barcode">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="manual-claim-barcode-btn" title="Manual Entry">
                                            <i class="fas fa-keyboard"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Scan the claim slip barcode to verify and mark as claimed
                                        </small>
                                    </div>
                                </div>
                                
                                <div id="verified-claim-details" class="mb-3" style="display: none;">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="fas fa-check-circle me-2"></i>Claim Slip Verified
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="claim-details-content">
                                                <!-- Claim details will be populated here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="{{ route('requests.claim', $request) }}" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="scanned_barcode" id="scanned_claim_barcode_input">
                                    <button type="submit" class="btn btn-secondary w-100" id="claim-btn" disabled>
                                        <i class="fas fa-handshake me-2"></i>Mark as Claimed
                                    </button>
                                </form>
                            @else
                                @if($request->workflow_status === 'claimed')
                                    <div class="alert alert-success mb-2">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>This request has already been claimed</strong>
                                    </div>
                                @elseif($request->workflow_status === 'approved_by_admin')
                                    <div class="alert alert-info mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>Waiting for faculty to generate claim slip</strong>
                                        <br><small>Faculty will generate a claim slip and visit the supply office to pick up items.</small>
                                    </div>
                                @elseif($request->workflow_status !== 'ready_for_pickup')
                                    <div class="alert alert-warning mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>This request cannot be claimed yet</strong>
                                        <br><small>Request must be approved and claim slip generated first.</small>
                                    </div>
                                @endif
                            @endif
                            
                            @if(!$request->isDeclined() && !$request->isClaimed())
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#declineModal" id="declineBtn">
                                    <i class="fas fa-times me-2"></i>Decline Request
                                </button>
                            @endif
                        </div>
                    </div>
                @endif                <!-- Workflow Timeline -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Workflow Timeline
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Request Submitted -->
                            <div class="timeline-item completed">
                                <div class="timeline-marker bg-success">
                                    <i class="fas fa-paper-plane text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Request Submitted</h6>
                                    <p class="mb-1 text-muted small">{{ $request->request_date ? $request->request_date->format('M j, Y g:i A') : 'N/A' }}</p>
                                    <p class="mb-0 small">Request created by {{ $request->user->name }}</p>
                                </div>
                            </div>

                            <!-- Admin Approval -->
                            <div class="timeline-item {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : ($request->workflow_status === 'declined_by_admin' ? 'declined' : '') }}">
                                <div class="timeline-marker {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->workflow_status === 'declined_by_admin' ? 'bg-danger' : 'bg-secondary') }}">
                                    <i class="fas {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'fa-shield-check' : ($request->workflow_status === 'declined_by_admin' ? 'fa-times' : 'fa-shield-alt') }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Admin Approval</h6>
                                    @if($request->admin_approval_date)
                                        <p class="mb-1 text-success small">{{ $request->admin_approval_date->format('M j, Y g:i A') }}</p>
                                        <p class="mb-0 small">Approved by {{ $request->adminApprover->name ?? 'Administrator' }}</p>
                                    @elseif($request->workflow_status === 'declined_by_admin')
                                        <p class="mb-1 text-danger small">Declined</p>
                                        @if($request->admin_notes)
                                            <p class="mb-0 small text-muted">"{{ $request->admin_notes }}"</p>
                                        @endif
                                    @else
                                        <p class="mb-0 text-muted small">Pending admin approval</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Claim Slip Generation -->
                            <div class="timeline-item {{ in_array($request->workflow_status, ['ready_for_pickup', 'claimed']) ? 'completed' : '' }}">
                                <div class="timeline-marker {{ in_array($request->workflow_status, ['ready_for_pickup', 'claimed']) ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ in_array($request->workflow_status, ['ready_for_pickup', 'claimed']) ? 'fa-ticket-alt' : 'fa-ticket-alt' }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Claim Slip Generation</h6>
                                    @if($request->claim_slip_number && $request->workflow_status !== 'approved_by_admin')
                                        <p class="mb-1 text-success small">Generated by faculty</p>
                                        <p class="mb-0 small">Claim slip: <code>{{ $request->claim_slip_number }}</code></p>
                                    @else
                                        <p class="mb-0 text-muted small">Waiting for faculty to generate claim slip</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Item Claimed -->
                            <div class="timeline-item {{ $request->workflow_status === 'claimed' ? 'completed' : '' }}">
                                <div class="timeline-marker {{ $request->workflow_status === 'claimed' ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ $request->workflow_status === 'claimed' ? 'fa-handshake' : 'fa-hand-paper' }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Item Claimed</h6>
                                    @if($request->claimed_date)
                                        <p class="mb-1 text-success small">{{ $request->claimed_date->format('M j, Y g:i A') }}</p>
                                        <p class="mb-0 small">Marked as claimed by {{ $request->claimedBy->name ?? 'Administrator' }}</p>
                                    @else
                                        <p class="mb-0 text-muted small">Waiting for faculty to pick up items</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
@if(!$request->isDeclined() && !$request->isClaimed() && auth()->user()->isAdmin())
    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineModalLabel">Decline Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('requests.decline', $request) }}" id="declineForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone. The request will be permanently declined.
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for declining <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Please provide a detailed reason for declining this request..." required></textarea>
                            <div class="form-text">This reason will be visible to the requestor.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="declineSubmitBtn">
                            <i class="fas fa-ban me-2"></i>Decline Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif



<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -42px;
        top: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #dee2e6;
    }
    
    .timeline-item.completed .timeline-content {
        border-left-color: #28a745;
    }
    
    .timeline-item.declined .timeline-content {
        border-left-color: #dc3545;
        background: #f8d7da;
    }

    /* Modal improvements */
    .modal {
        z-index: 1060 !important;
    }
    
    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 5000);
        });
    </script>
@endif

@if(session('error') || $errors->any())
    <!-- Temporarily disabled error display for debugging -->
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') ?? $errors->first() }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 5000);
});
</script> --}}
@endif

@push('scripts')
<script>
// Include QuaggaJS library for barcode scanning
document.addEventListener('DOMContentLoaded', function() {
    // Load QuaggaJS dynamically
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
    script.onload = function() {
        initializeClaimBarcodeScanner();
    };
    document.head.appendChild(script);
    
    // Initialize modal management
    initializeModalManagement();
    
    const declineForm = document.getElementById('declineForm');
    const declineSubmitBtn = document.getElementById('declineSubmitBtn');
    
    if (declineForm && declineSubmitBtn) {
        declineForm.addEventListener('submit', function() {
            // Show loading state
            declineSubmitBtn.disabled = true;
            declineSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        });
    }
});

function initializeItemBarcodeScanner() {
    let scannerActive = false;
    let scannerContainer = null;

    const scanBtn = document.getElementById('scan-item-barcode-btn');
    const manualBtn = document.getElementById('manual-item-barcode-btn');
    const barcodeInput = document.getElementById('item_barcode');
    const scannedBarcodeInput = document.getElementById('scanned_barcode_input');
    const fulfillBtn = document.getElementById('fulfill-btn');
    const itemDetailsDiv = document.getElementById('scanned-item-details');
    const itemDetailsContent = document.getElementById('item-details-content');

    if (!scanBtn || !manualBtn || !barcodeInput) return; // Exit if elements don't exist

    // Scan barcode button
    scanBtn.addEventListener('click', function() {
        if (scannerActive) {
            stopItemScanner();
        } else {
            startItemScanner();
        }
    });

    // Manual entry button
    manualBtn.addEventListener('click', function() {
        stopItemScanner();
        barcodeInput.readOnly = false;
        barcodeInput.placeholder = "Enter barcode manually";
        barcodeInput.focus();
        
        // Add manual input handler
        barcodeInput.addEventListener('input', handleManualBarcodeInput);
    });

    function handleManualBarcodeInput() {
        const barcode = barcodeInput.value.trim();
        if (barcode.length > 0) {
            verifyAndDisplayItem(barcode);
        } else {
            hideItemDetails();
        }
    }

    function startItemScanner() {
        scannerActive = true;
        barcodeInput.readOnly = true;
        
        // Create scanner container
        scannerContainer = document.createElement('div');
        scannerContainer.id = 'item-barcode-scanner-container';
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
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan Item Barcode</h5>
                <button type="button" class="btn-close" id="close-item-scanner"></button>
            </div>
            <div id="item-scanner-viewport" style="width: 100%; height: 300px; border: 1px solid #ddd;"></div>
            <div class="mt-3 text-center">
                <small class="text-muted">Position item barcode in front of camera</small>
            </div>
        `;

        document.body.appendChild(scannerContainer);

        // Initialize Quagga
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#item-scanner-viewport'),
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
                stopItemScanner();
                return;
            }
            Quagga.start();
        });

        // Handle barcode detection
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            barcodeInput.value = code;
            scannedBarcodeInput.value = code;
            verifyAndDisplayItem(code);
            stopItemScanner();
            
            // Show success message
            showToast('Barcode scanned successfully!', 'success');
        });

        // Close scanner button
        document.getElementById('close-item-scanner').addEventListener('click', stopItemScanner);
    }

    function stopItemScanner() {
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

    function verifyAndDisplayItem(barcode) {
        // Show loading state
        itemDetailsContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Verifying item...</div>
            </div>
        `;
        itemDetailsDiv.style.display = 'block';

        // Make AJAX request to verify item
        fetch(`/api/items/verify-barcode/${barcode}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayItemDetails(data.item);
                fulfillBtn.disabled = false;
                scannedBarcodeInput.value = barcode;
            } else {
                showItemError('Item not found or invalid barcode');
                fulfillBtn.disabled = true;
                scannedBarcodeInput.value = '';
            }
        })
        .catch(error => {
            console.error('Error verifying barcode:', error);
            showItemError('Error verifying barcode. Please try again.');
            fulfillBtn.disabled = true;
            scannedBarcodeInput.value = '';
        });
    }

    function displayItemDetails(item) {
        itemDetailsContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-tag me-1"></i>Item Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${item.name}</p>
                    <p class="mb-1"><strong>Barcode:</strong> <code>${item.barcode || 'N/A'}</code></p>
                    <p class="mb-1"><strong>Brand:</strong> ${item.brand || 'N/A'}</p>
                    <p class="mb-1"><strong>Category:</strong> ${item.category ? item.category.name : 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-boxes me-1"></i>Stock Information</h6>
                    <p class="mb-1"><strong>Current Stock:</strong> ${item.current_stock} ${item.unit || 'pcs'}</p>
                    <p class="mb-1"><strong>Minimum Stock:</strong> ${item.minimum_stock || 'N/A'}</p>
                    <p class="mb-1"><strong>Location:</strong> ${item.location || 'N/A'}</p>
                    <p class="mb-1"><strong>Condition:</strong> ${item.condition || 'N/A'}</p>
                </div>
            </div>
            ${item.description ? `<div class="mt-2"><strong>Description:</strong> ${item.description}</div>` : ''}
        `;
    }

    function showItemError(message) {
        itemDetailsContent.innerHTML = `
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        itemDetailsDiv.style.display = 'block';
    }

    function hideItemDetails() {
        itemDetailsDiv.style.display = 'none';
        scannedBarcodeInput.value = '';
    }

    function displayItemDetails(item) {
        itemDetailsContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-tag me-1"></i>Item Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${item.name}</p>
                    <p class="mb-1"><strong>Barcode:</strong> <code>${item.barcode || 'N/A'}</code></p>
                    <p class="mb-1"><strong>Brand:</strong> ${item.brand || 'N/A'}</p>
                    <p class="mb-1"><strong>Category:</strong> ${item.category ? item.category.name : 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-boxes me-1"></i>Stock Information</h6>
                    <p class="mb-1"><strong>Current Stock:</strong> ${item.current_stock} ${item.unit || 'pcs'}</p>
                    <p class="mb-1"><strong>Minimum Stock:</strong> ${item.minimum_stock || 'N/A'}</p>
                    <p class="mb-1"><strong>Location:</strong> ${item.location || 'N/A'}</p>
                    <p class="mb-1"><strong>Condition:</strong> ${item.condition || 'N/A'}</p>
                </div>
            </div>
            ${item.description ? `<div class="mt-2"><strong>Description:</strong> ${item.description}</div>` : ''}
        `;
    }

    function showItemError(message) {
        itemDetailsContent.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        itemDetailsDiv.style.display = 'block';
    }

    function hideItemDetails() {
        itemDetailsDiv.style.display = 'none';
        fulfillBtn.disabled = true;
        scannedBarcodeInput.value = '';
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

function initializeClaimBarcodeScanner() {
    let scannerActive = false;
    let scannerContainer = null;

    const scanBtn = document.getElementById('scan-claim-barcode-btn');
    const manualBtn = document.getElementById('manual-claim-barcode-btn');
    const barcodeInput = document.getElementById('claim_barcode');
    const scannedBarcodeInput = document.getElementById('scanned_claim_barcode_input');
    const claimBtn = document.getElementById('claim-btn');
    const claimDetailsDiv = document.getElementById('verified-claim-details');
    const claimDetailsContent = document.getElementById('claim-details-content');

    if (!scanBtn || !manualBtn || !barcodeInput) return; // Exit if elements don't exist

    // Scan barcode button
    scanBtn.addEventListener('click', function() {
        if (scannerActive) {
            stopClaimScanner();
        } else {
            startClaimScanner();
        }
    });

    // Manual entry button
    manualBtn.addEventListener('click', function() {
        stopClaimScanner();
        barcodeInput.readOnly = false;
        barcodeInput.placeholder = "Enter claim slip number manually";
        barcodeInput.focus();
        
        // Add manual input handler
        barcodeInput.addEventListener('input', handleManualClaimInput);
    });

    function handleManualClaimInput() {
        const claimSlip = barcodeInput.value.trim();
        if (claimSlip.length > 0) {
            verifyAndDisplayClaim(claimSlip);
        } else {
            hideClaimDetails();
        }
    }

    function startClaimScanner() {
        scannerActive = true;
        barcodeInput.readOnly = true;
        
        // Create scanner container
        scannerContainer = document.createElement('div');
        scannerContainer.id = 'claim-barcode-scanner-container';
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
                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Scan Claim Slip Barcode</h5>
                <button type="button" class="btn-close" id="close-claim-scanner"></button>
            </div>
            <div id="claim-scanner-viewport" style="width: 100%; height: 300px; border: 1px solid #ddd;"></div>
            <div class="mt-3 text-center">
                <small class="text-muted">Position claim slip barcode in front of camera</small>
            </div>
        `;

        document.body.appendChild(scannerContainer);

        // Initialize Quagga
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#claim-scanner-viewport'),
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
                stopClaimScanner();
                return;
            }
            Quagga.start();
        });

        // Handle barcode detection
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            barcodeInput.value = code;
            scannedBarcodeInput.value = code;
            verifyAndDisplayClaim(code);
            stopClaimScanner();
            
            // Show success message
            showToast('Claim slip scanned successfully!', 'success');
        });

        // Close scanner button
        document.getElementById('close-claim-scanner').addEventListener('click', stopClaimScanner);
    }

    function stopClaimScanner() {
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

    function verifyAndDisplayClaim(claimSlipNumber) {
        // Show loading state
        claimDetailsContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Verifying claim slip...</div>
            </div>
        `;
        claimDetailsDiv.style.display = 'block';

        // Check if the scanned claim slip matches this request
        if (claimSlipNumber === '{{ $request->claim_slip_number }}') {
            displayClaimDetails();
            claimBtn.disabled = false;
            scannedBarcodeInput.value = claimSlipNumber;
        } else {
            showClaimError('Claim slip does not match this request. Please scan the correct claim slip.');
            claimBtn.disabled = true;
            scannedBarcodeInput.value = '';
        }
    }

    function displayClaimDetails() {
        claimDetailsContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-ticket-alt me-1"></i>Claim Slip Details</h6>
                    <p class="mb-1"><strong>Claim Slip Number:</strong> <code>{{ $request->claim_slip_number }}</code></p>
                    <p class="mb-1"><strong>Requester:</strong> {{ $request->user->name }}</p>
                    <p class="mb-1"><strong>Department:</strong> {{ $request->department }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-box me-1"></i>Item Information</h6>
                    <p class="mb-1"><strong>Item:</strong> {{ $request->item->name }}</p>
                    <p class="mb-1"><strong>Quantity:</strong> {{ $request->quantity }} {{ $request->item->unit ?? 'pcs' }}</p>
                    <p class="mb-1"><strong>Purpose:</strong> {{ Str::limit($request->purpose, 30) }}</p>
                </div>
            </div>
            <div class="mt-2">
                <strong>Status:</strong> <span class="badge bg-success">Verified - Ready for pickup</span>
            </div>
        `;
    }

    function showClaimError(message) {
        claimDetailsContent.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        claimDetailsDiv.style.display = 'block';
    }

    function hideClaimDetails() {
        claimDetailsDiv.style.display = 'none';
        scannedBarcodeInput.value = '';
        claimBtn.disabled = true;
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

// Comprehensive modal backdrop management
function initializeModalManagement() {
    let cleanupInterval;
    let modalOpen = false;
    
    // Clean up on page load
    forceCleanupModalState();
    
    // Override Bootstrap modal show method to ensure clean state
    const originalModalShow = bootstrap.Modal.prototype.show;
    bootstrap.Modal.prototype.show = function() {
        forceCleanupModalState();
        modalOpen = true;
        // Stop periodic cleanup when modal is open
        if (cleanupInterval) {
            clearInterval(cleanupInterval);
        }
        return originalModalShow.call(this);
    };
    
    // Override Bootstrap modal hide method to ensure clean state
    const originalModalHide = bootstrap.Modal.prototype.hide;
    bootstrap.Modal.prototype.hide = function() {
        const result = originalModalHide.call(this);
        modalOpen = false;
        // Clean up after a short delay to allow Bootstrap to finish
        setTimeout(() => {
            forceCleanupModalState();
            // Restart periodic cleanup when modal is closed
            cleanupInterval = setInterval(() => {
                if (!modalOpen) {
                    forceCleanupModalState();
                }
            }, 5000);
        }, 100);
        return result;
    };
    
    // Intercept modal trigger clicks
    document.addEventListener('click', function(e) {
        const modalTrigger = e.target.closest('[data-bs-toggle="modal"]');
        if (modalTrigger) {
            forceCleanupModalState();
        }
    });
    
    // Start periodic cleanup initially
    cleanupInterval = setInterval(() => {
        if (!modalOpen) {
            forceCleanupModalState();
        }
    }, 5000);
}

// Force cleanup of all modal-related elements and state
function forceCleanupModalState() {
    // Remove all modal backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        if (backdrop.parentNode) {
            backdrop.parentNode.removeChild(backdrop);
        }
    });
    
    // Reset body modal state
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    // Ensure no modals are stuck in show state
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = '';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
    });
    
    // Clean up any orphaned modal dialogs
    const modalDialogs = document.querySelectorAll('.modal-dialog');
    modalDialogs.forEach(dialog => {
        dialog.style.transform = '';
    });
}
</script>
@endpush

@endsection