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
                                            @case('approved_by_office_head') bg-info @break
                                            @case('approved_by_admin') bg-success @break
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
                @if(auth()->user()->isAdmin() || (auth()->user()->isOfficeHead() && $request->canBeApprovedByOfficeHead()))
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(auth()->user()->isAdmin())
                                <!-- Admin Actions -->
                                @if($request->canBeApprovedByAdmin())
                                    <form method="POST" action="{{ route('requests.approve-admin', $request) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check me-2"></i>Approve Request
                                        </button>
                                    </form>
                                @endif
                                
                                @if($request->canBeFulfilled())
                                    <form method="POST" action="{{ route('requests.fulfill', $request) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-box me-2"></i>Fulfill Request
                                        </button>
                                    </form>
                                @endif
                                
                                @if($request->canBeClaimed())
                                    <form method="POST" action="{{ route('requests.claim', $request) }}" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary w-100">
                                            <i class="fas fa-handshake me-2"></i>Mark as Claimed
                                        </button>
                                    </form>
                                @endif
                                
                                @if(!$request->isDeclined() && !$request->isClaimed())
                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#declineModal" id="declineBtn">
                                        <i class="fas fa-times me-2"></i>Decline Request
                                    </button>
                                @endif
                            @elseif(auth()->user()->isOfficeHead())
                                <!-- Office Head Actions -->
                                @if($request->canBeApprovedByOfficeHead())
                                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                                        <i class="fas fa-check me-2"></i>Approve Request
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#declineModal">
                                        <i class="fas fa-times me-2"></i>Decline Request
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Workflow Timeline -->
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

                            <!-- Office Head Approval -->
                            <div class="timeline-item {{ in_array($request->workflow_status, ['approved_by_office_head', 'approved_by_admin', 'fulfilled', 'claimed']) ? 'completed' : ($request->workflow_status === 'declined_by_office_head' ? 'declined' : '') }}">
                                <div class="timeline-marker {{ in_array($request->workflow_status, ['approved_by_office_head', 'approved_by_admin', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->workflow_status === 'declined_by_office_head' ? 'bg-danger' : 'bg-secondary') }}">
                                    <i class="fas {{ in_array($request->workflow_status, ['approved_by_office_head', 'approved_by_admin', 'fulfilled', 'claimed']) ? 'fa-user-check' : ($request->workflow_status === 'declined_by_office_head' ? 'fa-times' : 'fa-user-clock') }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Office Head Review</h6>
                                    @if($request->office_head_approval_date)
                                        <p class="mb-1 text-success small">{{ $request->office_head_approval_date->format('M j, Y g:i A') }}</p>
                                        <p class="mb-0 small">Approved by {{ $request->officeHeadApprover->name ?? 'Office Head' }}</p>
                                        @if($request->office_head_notes)
                                            <p class="mb-0 small text-muted fst-italic">"{{ $request->office_head_notes }}"</p>
                                        @endif
                                    @elseif($request->workflow_status === 'declined_by_office_head')
                                        <p class="mb-1 text-danger small">Declined</p>
                                        @if($request->admin_notes)
                                            <p class="mb-0 small text-muted">"{{ $request->admin_notes }}"</p>
                                        @endif
                                    @else
                                        <p class="mb-0 text-muted small">Pending office head approval</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Admin Approval -->
                            @if(!in_array($request->workflow_status, ['declined_by_office_head']))
                                <div class="timeline-item {{ in_array($request->workflow_status, ['approved_by_admin', 'fulfilled', 'claimed']) ? 'completed' : ($request->workflow_status === 'declined_by_admin' ? 'declined' : '') }}">
                                    <div class="timeline-marker {{ in_array($request->workflow_status, ['approved_by_admin', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->workflow_status === 'declined_by_admin' ? 'bg-danger' : 'bg-secondary') }}">
                                        <i class="fas {{ in_array($request->workflow_status, ['approved_by_admin', 'fulfilled', 'claimed']) ? 'fa-shield-check' : ($request->workflow_status === 'declined_by_admin' ? 'fa-times' : 'fa-shield-alt') }} text-white"></i>
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

                                <!-- Request Fulfilled -->
                                <div class="timeline-item {{ in_array($request->workflow_status, ['fulfilled', 'claimed']) ? 'completed' : '' }}">
                                    <div class="timeline-marker {{ in_array($request->workflow_status, ['fulfilled', 'claimed']) ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="fas {{ in_array($request->workflow_status, ['fulfilled', 'claimed']) ? 'fa-box' : 'fa-box-open' }} text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Request Fulfilled</h6>
                                        @if($request->fulfilled_date)
                                            <p class="mb-1 text-success small">{{ $request->fulfilled_date->format('M j, Y g:i A') }}</p>
                                            <p class="mb-1 small">Fulfilled by {{ $request->fulfilledBy->name ?? 'Administrator' }}</p>
                                            @if($request->claim_slip_number)
                                                <p class="mb-0 small">Claim slip: <code>{{ $request->claim_slip_number }}</code></p>
                                            @endif
                                        @else
                                            <p class="mb-0 text-muted small">Pending fulfillment</p>
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
                                            <p class="mb-0 text-muted small">Waiting for item to be claimed</p>
                                        @endif
                                    </div>
                                </div>
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

<!-- Office Head Approval Modal -->
@if(auth()->user()->isOfficeHead() && $request->canBeApprovedByOfficeHead())
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('requests.approve-office-head', $request) }}">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve this request?</p>
                        <div class="mb-3">
                            <label for="office_head_notes" class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="office_head_notes" name="office_head_notes" rows="3" placeholder="Add any notes or comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Decline Modal -->
@if(!$request->isDeclined() && !$request->isClaimed() && (auth()->user()->isAdmin() || (auth()->user()->isOfficeHead() && $request->canBeApprovedByOfficeHead())))
    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineModalLabel">Decline Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ auth()->user()->isAdmin() ? route('requests.decline', $request) : route('requests.decline-office-head', $request) }}" id="declineForm">
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
    <script>
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
</script>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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