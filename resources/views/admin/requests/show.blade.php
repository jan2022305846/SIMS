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
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Name:</strong></div>
                                        <div class="col-8">{{ $request->user ? $request->user->name : 'Unknown User' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Email:</strong></div>
                                        <div class="col-8">{{ $request->user ? $request->user->email : 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Role:</strong></div>
                                        <div class="col-8">{{ $request->user ? ($request->user->isAdmin() ? 'Admin' : 'Faculty') : 'N/A' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4"><strong>Office:</strong></div>
                                        <div class="col-8">{{ $request->user->office->name ?? 'Unknown Office' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request Status</h6>
                                <div class="mb-3">
                                    <span class="badge fs-6 px-3 py-2
                                        @switch($request->status)
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
                            <div class="col-lg-8 col-md-12">
                                <h6 class="text-muted mb-2">Item Details</h6>
                                @if($request->requestItems && $request->requestItems->count() > 0)
                                    <div class="mb-3">
                                        @foreach($request->requestItems as $requestItem)
                                            <div class="card mb-3 border-light">
                                                <div class="card-body p-4">
                                                    <div class="row align-items-start g-3">
                                                        <div class="col-lg-5 col-md-6 col-sm-12">
                                                            <div class="d-flex flex-column h-100">
                                                                <strong class="mb-1">{{ $requestItem->itemable ? $requestItem->itemable->name : 'Item Not Found' }}</strong>
                                                                @if($requestItem->itemable && $requestItem->itemable->description)
                                                                    <small class="text-muted">{{ Str::limit($requestItem->itemable->description, 50) }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-7 col-md-6 col-sm-12">
                                                            <div class="d-flex flex-column gap-2 h-100">
                                                                <div class="d-flex gap-3 justify-content-center">
                                                                    <!-- Quantity Info -->
                                                                    <div>
                                                                        <div class="d-flex align-items-center">
                                                                            <small class="text-muted me-1 fw-medium">Qty:</small>
                                                                            <span class="fw-semibold me-1" id="quantity-display-{{ $requestItem->id }}">{{ $requestItem->getFinalQuantity() }}</span>
                                                                            <small class="ms-1 text-muted">{{ $requestItem->itemable ? ($requestItem->itemable->unit ?? 'pcs') : 'pcs' }}</small>
                                                                            @if($request->isPending() && $requestItem->canBeAdjusted())
                                                                                <button type="button" class="btn btn-outline-secondary btn-sm ms-2 adjust-quantity-btn" 
                                                                                        data-item-id="{{ $requestItem->id }}"
                                                                                        data-item-name="{{ $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item' }}"
                                                                                        data-current-quantity="{{ $requestItem->getFinalQuantity() }}">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                        @if($requestItem->isAdjusted())
                                                                            <small class="text-info d-block mt-1">{{ $requestItem->getAdjustmentText() }}</small>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    <!-- Limit Info -->
                                                                    <div>
                                                                        @if($requestItem->itemable)
                                                                            @php
                                                                                $limit = $requestItem->itemable->officeLimits 
                                                                                    ? $requestItem->itemable->officeLimits->where('office_id', $request->user->office->id)->first()->max_quantity ?? 'N/A' 
                                                                                    : 'N/A';
                                                                            @endphp
                                                                            <small class="text-muted">
                                                                                <span class="fw-medium">Limit:</span> {{ is_numeric($limit) ? number_format($limit) : $limit }}
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <!-- Approve/Decline Buttons -->
                                                                    @if($requestItem->item_status === 'approved')
                                                                        <span class="badge bg-success px-3 py-2">
                                                                            <i class="fas fa-check me-1"></i>Approved
                                                                        </span>
                                                                    @elseif($requestItem->item_status === 'declined')
                                                                        <span class="badge bg-danger px-3 py-2">
                                                                            <i class="fas fa-times me-1"></i>Declined
                                                                        </span>
                                                                    @else
                                                                        @if($request->isPending() && $requestItem->canBeAdjusted())
                                                                            <button type="button" 
                                                                                    class="btn btn-outline-success btn-sm px-3 approve-item-btn" 
                                                                                    data-item-id="{{ $requestItem->id }}"
                                                                                    data-item-name="{{ $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item' }}">
                                                                                <i class="fas fa-check me-1"></i>Approve
                                                                            </button>
                                                                            <button type="button" 
                                                                                    class="btn btn-outline-danger btn-sm px-3 decline-item-btn" 
                                                                                    data-item-id="{{ $requestItem->id }}"
                                                                                    data-item-name="{{ $requestItem->itemable ? $requestItem->itemable->name : 'Unknown Item' }}">
                                                                                <i class="fas fa-times me-1"></i>Decline
                                                                            </button>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($requestItem->adjustment_reason)
                                                        <hr class="my-3">
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                <strong>Adjustment Reason:</strong> {{ $requestItem->adjustment_reason }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <h6 class="text-muted mb-2">Request Details</h6>
                                <div class="mb-3">
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Office:</strong></div>
                                        <div class="col-7">{{ $request->user->office->name ?? 'Unknown Office' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Request Date:</strong></div>
                                        <div class="col-7">{{ $request->created_at ? $request->created_at->format('M j, Y') : 'N/A' }}</div>
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

                    <!-- Actions Card -->
                    @if(auth()->user()->isAdmin())
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tasks me-2"></i>Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                            <!-- Admin Actions -->
                            @if($request->canBeApprovedByAdmin())
                                @php
                                    $hasAdjustments = $request->requestItems->contains(function ($item) {
                                        return $item->isAdjusted();
                                    });
                                    $hasDeclinedItems = $request->requestItems->contains(function ($item) {
                                        return $item->item_status === 'declined';
                                    });
                                    $allItemsReviewed = $request->requestItems->every(function ($item) {
                                        return $item->item_status === 'approved' || $item->item_status === 'declined';
                                    });
                                @endphp

                                @if($allItemsReviewed)
                                    <form method="POST" action="{{ route('requests.approve-with-adjustments', $request) }}" class="mb-2">
                                        @csrf
                                        <div class="mb-2">
                                            <label for="approval_reason" class="form-label">
                                                <strong>Approval Reason {{ $hasAdjustments ? '(Required - Items Adjusted)' : '(Optional)' }}</strong>
                                            </label>
                                            <textarea class="form-control"
                                                      id="approval_reason"
                                                      name="approval_reason"
                                                      rows="3"
                                                      placeholder="Explain why items were adjusted or declined..."
                                                      {{ $hasAdjustments ? 'required' : '' }}></textarea>
                                            @if($hasAdjustments)
                                                <div class="form-text">
                                                    <small class="text-info">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Please provide a reason for the quantity adjustments made to this request.
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check me-2"></i>Approve Request
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-warning mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Review Required</strong>
                                        <br><small>Please review and approve or decline each item before approving the request.</small>
                                    </div>
                                @endif
                            @else
                                @if($request->status === 'declined_by_admin')
                                    <div class="alert alert-danger mb-2">
                                        <i class="fas fa-ban me-2"></i>
                                        <strong>This request was declined</strong>
                                        @if($request->admin_notes)
                                            <br><small>Reason: {{ $request->admin_notes }}</small>
                                        @endif
                                    </div>
                                @elseif($request->status === 'approved_by_admin')
                                    <div class="alert alert-success mb-2">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>This request has already been approved</strong>
                                    </div>
                                @elseif(in_array($request->status, ['fulfilled', 'claimed']))
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

                            @if($request->canBeClaimed())
                                <div class="mb-3">
                                    <label for="claim_barcode" class="form-label fw-medium">
                                        <i class="fas fa-ticket-alt text-primary me-1"></i>
                                        Scan Claim Slip QR Code
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="claim_barcode" id="claim_barcode"
                                               class="form-control" placeholder="Enter claim slip number manually" value="">
                                        <button type="button" class="btn btn-outline-primary" id="scan-claim-barcode-btn" title="Scan Claim Slip QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Step 1:</strong> Scan the QR code from the faculty member's printed claim slip (contains claim slip number like "CS-2025-000003")
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
                                    <button type="submit" class="btn btn-primary w-100" id="claim-btn" disabled>
                                        <i class="fas fa-handshake me-2"></i>
                                        Mark as Claimed
                                    </button>
                                </form>
                            @else
                                @if($request->status === 'claimed')
                                    <div class="alert alert-success mb-2">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>This request has already been claimed</strong>
                                    </div>
                                @elseif($request->status === 'approved_by_admin')
                                    <div class="alert alert-info mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>Waiting for faculty to generate claim slip</strong>
                                        <br><small>Faculty member needs to generate a claim slip before items can be picked up.</small>
                                    </div>
                                @elseif($request->status !== 'ready_for_pickup')
                                    <div class="alert alert-warning mb-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>This request cannot be claimed yet</strong>
                                        <br><small>Request must be approved and claim slip generated first.</small>
                                    </div>
                                @endif
                            @endif

                            @if($request->isPending())
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#declineModal" id="declineBtn">
                                    <i class="fas fa-times me-2"></i>Decline Request
                                </button>
                            @endif

                            <!-- Remarks Section -->
                            <hr class="my-3">
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-comment me-1"></i>Remarks
                                </h6>
                                @if($request->admin_notes)
                                    <div class="mb-3">
                                        <div class="bg-light p-2 rounded small">
                                            {{ $request->admin_notes }}
                                        </div>
                                    </div>
                                @endif
                                <div class="mb-2">
                                    <textarea class="form-control form-control-sm" id="admin_remarks" name="admin_remarks" rows="3" 
                                              placeholder="Add remarks..."></textarea>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="save-remarks-btn">
                                    <i class="fas fa-save me-1"></i>Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        <!-- Workflow Timeline - Full Width Below -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-route me-2"></i>
                        <h5 class="mb-0">Request Workflow Progress</h5>
                        <div class="ms-auto">
                            <small class="text-white-50">
                                <i class="fas fa-clock me-1"></i>
                                @if($request->claimed_date)
                                    Completed in {{ $request->request_date->diffInDays($request->claimed_date) + 1 }} days
                                @elseif($request->status === 'claimed')
                                    Completed
                                @else
                                    In Progress
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="workflow-timeline">
                            <!-- Step 1: Request Submitted -->
                            <div class="workflow-step completed">
                                <div class="workflow-marker bg-success">
                                    <div class="step-number">1</div>
                                    <i class="fas fa-paper-plane step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Request Submitted</h6>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check-circle me-1"></i>Completed
                                        </span>
                                    </div>
                                    <div class="step-details">
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $request->request_date ? $request->request_date->format('M j, Y g:i A') : 'N/A' }}
                                                </small>
                                            </div>
                                            <div class="col-sm-6">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $request->user ? $request->user->name : 'Unknown User' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="step-description mt-2">
                                            <small class="text-muted">
                                                @if($request->requestItems && $request->requestItems->count() > 0)
                                                    Faculty member submitted a bulk request for {{ $request->requestItems->count() }} different items ({{ $request->requestItems->sum('quantity') }} total pieces)
                                                @else
                                                    Faculty member submitted a request for {{ $request->quantity }} {{ $request->requestItems->first() && $request->requestItems->first()->itemable ? ($request->requestItems->first()->itemable->unit ?? 'pcs') : 'pcs' }} of {{ $request->requestItems->first() && $request->requestItems->first()->itemable ? $request->requestItems->first()->itemable->name : 'Unknown Item' }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Admin Approval -->
                            <div class="workflow-step {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : ($request->status === 'declined_by_admin' ? 'declined' : 'current') }}">
                                <div class="workflow-marker {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->status === 'declined_by_admin' ? 'bg-danger' : 'bg-primary') }}">
                                    <div class="step-number">2</div>
                                    <i class="fas {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'fa-shield-check' : ($request->status === 'declined_by_admin' ? 'fa-times' : 'fa-shield-alt') }} step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Admin Approval</h6>
                                        @if(in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Approved
                                            </span>
                                        @elseif($request->status === 'declined_by_admin')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="fas fa-times-circle me-1"></i>Declined
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </div>
                                    <div class="step-details">
                                        @if($request->admin_approval_date)
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        {{ $request->admin_approval_date->format('M j, Y g:i A') }}
                                                    </small>
                                                </div>
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-user-shield me-1"></i>
                                                        {{ $request->adminApprover->name ?? 'Administrator' }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="step-description mt-2">
                                                <small class="text-muted">
                                                    Request approved and ready for fulfillment
                                                </small>
                                            </div>
                                        @elseif($request->status === 'declined_by_admin')
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <strong>Declined:</strong> {{ $request->admin_notes ?? 'No reason provided' }}
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    Waiting for administrator review and approval
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Claim Slip Generation -->
                            <div class="workflow-step {{ in_array($request->status, ['ready_for_pickup', 'claimed']) ? 'completed' : (in_array($request->status, ['approved_by_admin', 'fulfilled']) ? 'current' : '') }}">
                                <div class="workflow-marker {{ in_array($request->status, ['ready_for_pickup', 'claimed']) ? 'bg-success' : (in_array($request->status, ['approved_by_admin', 'fulfilled']) ? 'bg-primary' : 'bg-secondary') }}">
                                    <div class="step-number">3</div>
                                    <i class="fas fa-ticket-alt step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Claim Slip Generation</h6>
                                        @if(in_array($request->status, ['ready_for_pickup', 'claimed']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Generated
                                            </span>
                                        @elseif(in_array($request->status, ['approved_by_admin', 'fulfilled']))
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-pause me-1"></i>Waiting
                                            </span>
                                        @endif
                                    </div>
                                    <div class="step-details">
                                        @if($request->claim_slip_number && $request->status !== 'approved_by_admin')
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-hashtag me-1"></i>
                                                        <code class="bg-light px-1 rounded">{{ $request->claim_slip_number }}</code>
                                                    </small>
                                                </div>
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-qrcode me-1"></i>
                                                        QR Code Generated
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="step-description mt-2">
                                                <small class="text-muted">
                                                    Faculty generated claim slip with QR code for pickup verification
                                                </small>
                                            </div>
                                        @elseif(in_array($request->status, ['approved_by_admin']))
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Faculty member needs to generate a claim slip to proceed with pickup
                                                </small>
                                            </div>
                                        @else
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    Claim slip generation will be available after admin approval
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Item Claimed -->
                            <div class="workflow-step {{ $request->status === 'claimed' ? 'completed' : ($request->status === 'ready_for_pickup' ? 'current' : '') }}">
                                <div class="workflow-marker {{ $request->status === 'claimed' ? 'bg-success' : ($request->status === 'ready_for_pickup' ? 'bg-primary' : 'bg-secondary') }}">
                                    <div class="step-number">4</div>
                                    <i class="fas {{ $request->status === 'claimed' ? 'fa-handshake' : 'fa-hand-paper' }} step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Item Pickup & Claim</h6>
                                        @if($request->status === 'claimed')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Completed
                                            </span>
                                        @elseif($request->status === 'ready_for_pickup')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="fas fa-clock me-1"></i>Ready for Pickup
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-pause me-1"></i>Waiting
                                            </span>
                                        @endif
                                    </div>
                                    <div class="step-details">
                                        @if($request->claimed_date)
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        {{ $request->claimed_date->format('M j, Y g:i A') }}
                                                    </small>
                                                </div>
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-user-check me-1"></i>
                                                        {{ $request->claimedBy->name ?? 'Administrator' }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="step-description mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-check-double me-1"></i>
                                                    Claim slip QR code scanned successfully for verification
                                                </small>
                                            </div>
                                        @elseif($request->status === 'ready_for_pickup')
                                            <div class="step-description">
                                                <small class="text-primary">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Ready for faculty pickup at supply office with claim slip
                                                </small>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted d-block">
                                                    <strong>Next Steps:</strong>
                                                </small>
                                                <ul class="mb-0 mt-1 small text-muted">
                                                    <li><strong>Step 1:</strong> Faculty presents claim slip QR code for verification</li>
                                                    <li><strong>Step 2:</strong> Admin verifies all items are ready and hands them over</li>
                                                    <li><strong>Step 3:</strong> Mark the request as claimed</li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    Item pickup will be available after claim slip generation
                                                </small>
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

<!-- Item Action Modal -->
<div class="modal fade" id="itemActionModal" tabindex="-1" aria-labelledby="itemActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemActionModalLabel">
                    <i class="fas fa-tasks me-2"></i>Item Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="itemActionContent" class="p-2">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmItemActionBtn">
                    <i class="fas fa-check me-2"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>



<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
    
    /* Enhanced Workflow Timeline Styles - Landscape Layout */
    .workflow-timeline {
        position: relative;
        padding-left: 80px;
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: space-between;
    }
    
    .workflow-step {
        position: relative;
        flex: 0 0 calc(25% - 22.5px); /* 4 steps per row with gap */
        min-width: 220px;
        margin-bottom: 40px;
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    /* Responsive workflow steps */
    @media (max-width: 1200px) {
        .workflow-step {
            flex: 0 0 calc(50% - 15px); /* 2 steps per row on large tablets */
        }
    }
    
    @media (max-width: 768px) {
        .workflow-timeline {
            padding-left: 60px;
            gap: 20px;
        }
        
        .workflow-step {
            flex: 0 0 100%; /* 1 step per row on mobile */
            margin-bottom: 30px;
        }
        
        .workflow-marker {
            left: -42px;
            width: 44px;
            height: 44px;
        }
        
        .step-number {
            font-size: 10px;
        }
        
        .step-icon {
            font-size: 14px;
        }
    }
    
    .workflow-step.completed {
        opacity: 1;
    }
    
    .workflow-step.current {
        opacity: 1;
        animation: pulse 2s infinite;
    }
    
    .workflow-step.declined {
        opacity: 1;
    }
    
    .workflow-marker {
        position: absolute;
        left: -52px;
        top: 0;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        transition: all 0.3s ease;
        z-index: 2;
    }
    
    /* Dark mode support for markers */
    [data-bs-theme="dark"] .workflow-marker {
        border-color: #212529;
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .workflow-step.completed .workflow-marker {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    }
    
    .workflow-step.current .workflow-marker {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        animation: pulse-ring 2s infinite;
    }
    
    .workflow-step.declined .workflow-marker {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    }
    
    .step-number {
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
        margin-bottom: 2px;
        color: inherit;
    }
    
    .step-icon {
        font-size: 16px;
        line-height: 1;
        color: inherit;
    }
    
    .workflow-content {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        border: 2px solid #f1f3f4;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        height: 100%;
        min-height: 180px;
    }
    
    /* Dark mode support for content cards */
    [data-bs-theme="dark"] .workflow-content {
        background: #343a40;
        border-color: #495057;
        color: #ffffff;
    }
    
    .workflow-step.completed .workflow-content {
        border-color: #d4edda;
        background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.1);
    }
    
    [data-bs-theme="dark"] .workflow-step.completed .workflow-content {
        border-color: #155724;
        background: linear-gradient(135deg, #1e3a1f 0%, #343a40 100%);
    }
    
    .workflow-step.current .workflow-content {
        border-color: #cce7ff;
        background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }
    
    [data-bs-theme="dark"] .workflow-step.current .workflow-content {
        border-color: #004085;
        background: linear-gradient(135deg, #1a252f 0%, #343a40 100%);
    }
    
    .workflow-step.declined .workflow-content {
        border-color: #f5c6cb;
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(220, 53, 69, 0.1);
    }
    
    [data-bs-theme="dark"] .workflow-step.declined .workflow-content {
        border-color: #721c24;
        background: linear-gradient(135deg, #3a1f1f 0%, #343a40 100%);
    }
    
    .step-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .step-title {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
    }
    
    [data-bs-theme="dark"] .step-title {
        color: #ffffff;
    }
    
    .step-details {
        color: #6c757d;
        flex-grow: 1;
    }
    
    [data-bs-theme="dark"] .step-details {
        color: #adb5bd;
    }
    
    .step-description {
        font-style: italic;
        line-height: 1.4;
        margin-top: auto;
        padding-top: 10px;
    }
    
    /* Badge improvements */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 6px;
    }
    
    /* Dark mode badge support */
    [data-bs-theme="dark"] .badge.bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.2) !important;
        color: #75b798 !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.2) !important;
        color: #ea868f !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.2) !important;
        color: #ffda6a !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.2) !important;
        color: #6ea8fe !important;
    }
    
    [data-bs-theme="dark"] .badge.bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.2) !important;
        color: #a7aeb1 !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .workflow-step {
            flex: 0 0 calc(50% - 15px); /* 2 steps per row on medium screens */
        }
    }
    
    @media (max-width: 768px) {
        .workflow-timeline {
            padding-left: 60px;
            gap: 20px;
        }
        
        .workflow-step {
            flex: 0 0 100%; /* 1 step per row on small screens */
            min-width: unset;
        }
        
        .workflow-marker {
            left: -42px;
            width: 48px;
            height: 48px;
        }
        
        .workflow-content {
            padding: 16px;
            min-height: 160px;
        }
        
        .step-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
        
        .step-title {
            font-size: 1rem;
        }
    }
    
    /* Pulse animations */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }
    
    @keyframes pulse-ring {
        0% {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3), 0 0 0 0 rgba(0, 123, 255, 0.7);
        }
        70% {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3), 0 0 0 10px rgba(0, 123, 255, 0);
        }
        100% {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3), 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }
    
    /* Progress line styling - removed for horizontal layout */
    
    /* Enhanced card styling for dark mode */
    [data-bs-theme="dark"] .card {
        background-color: #343a40;
        border-color: #495057;
    }
    
    [data-bs-theme="dark"] .card-header {
        background-color: #495057;
        border-color: #6c757d;
    }
    
    [data-bs-theme="dark"] .text-muted {
        color: #adb5bd !important;
    }
    
    [data-bs-theme="dark"] .bg-light {
        background-color: #495057 !important;
    }
    
    [data-bs-theme="dark"] .list-group-item {
        background-color: #495057;
        border-color: #6c757d;
        color: #ffffff;
    }
    
    [data-bs-theme="dark"] .list-group-item.bg-light {
        background-color: #6c757d !important;
    }
    
    /* Item display dark mode styles */
    [data-bs-theme="dark"] .card.bg-light {
        background-color: #495057 !important;
        border-color: #6c757d !important;
        color: #ffffff !important;
    }
    
    [data-bs-theme="dark"] .card.bg-light .card-body {
        color: #ffffff !important;
    }
    
    [data-bs-theme="dark"] .card.bg-light strong {
        color: #ffffff !important;
    }
    
    [data-bs-theme="dark"] .list-group-item.bg-transparent {
        color: #ffffff !important;
        background-color: transparent !important;
    }

    /* Animation keyframes */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes pulse-ring {
        0% {
            transform: scale(1);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.5);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }
    }

    /* Custom modal styling */
    #itemActionModal .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    #itemActionModal .modal-dialog {
        max-width: 500px;
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
// Include html5-qrcode library for QR code scanning
document.addEventListener('DOMContentLoaded', function() {
    // Load html5-qrcode locally
    const script = document.createElement('script');
    script.src = '{{ asset('js/html5-qrcode.min.js') }}';
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

function initializeClaimBarcodeScanner() {
    let scannerActive = false;
    let scannerContainer = null;

    const scanBtn = document.getElementById('scan-claim-barcode-btn');
    const barcodeInput = document.getElementById('claim_barcode');
    const scannedBarcodeInput = document.getElementById('scanned_claim_barcode_input');
    const claimBtn = document.getElementById('claim-btn');
    const claimDetailsDiv = document.getElementById('verified-claim-details');
    const claimDetailsContent = document.getElementById('claim-details-content');

    if (!scanBtn || !barcodeInput) return; // Exit if elements don't exist

    // Add manual input handler directly to the input field
    barcodeInput.addEventListener('input', handleManualClaimInput);

    // Scan barcode button
    scanBtn.addEventListener('click', function() {
        if (scannerActive) {
            stopClaimScanner();
        } else {
            startClaimScanner();
        }
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold"><i class="fas fa-ticket-alt me-2 text-primary"></i>Scan Claim Slip QR Code</h5>
                <button type="button" class="btn-close" id="close-claim-scanner" aria-label="Close"></button>
            </div>
            <div id="claim-scanner-viewport" style="width: 100%; height: 300px; border: 1px solid #ddd;"></div>
            <div class="mt-3 text-center">
                <small class="text-muted">Position claim slip QR code in front of camera</small>
            </div>
            <div class="mt-2 text-center">
                <small class="text-primary fw-medium">This QR code contains secure verification data for the claim slip</small>
            </div>
        `;

        document.body.appendChild(scannerContainer);

        // Initialize Html5Qrcode for QR code scanning
        if (!window.claimSlipScanner) {
            try {
                window.claimSlipScanner = new Html5Qrcode("claim-scanner-viewport");
                console.log('Html5Qrcode initialized for claim slip scanner');
            } catch (error) {
                console.error('Error initializing Html5Qrcode:', error);
                alert('Error initializing camera: ' + error.message);
                stopClaimScanner();
                return;
            }
        }

        // Get cameras and start scanning
        Html5Qrcode.getCameras().then(devices => {
            console.log('Cameras found for claim slip scanner:', devices.length);
            
            if (devices && devices.length) {
                // Prefer back camera on mobile devices
                let cameraId = devices[0].id;
                
                // Look for back/environment camera on mobile
                for (let device of devices) {
                    if (device.label) {
                        const label = device.label.toLowerCase();
                        if (label.includes('back') ||
                            label.includes('environment') ||
                            label.includes('rear') ||
                            label.includes('world') ||
                            (label.includes('camera') && label.includes('1')) ||
                            (label.includes('cam') && label.includes('1'))) {
                            cameraId = device.id;
                            console.log('Found back camera for claim slip scanner:', device.label);
                            break;
                        }
                    }
                }
                
                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };
                
                window.claimSlipScanner.start(
                    cameraId,
                    config,
                    (decodedText, decodedResult) => {
                        console.log('Claim slip QR Code detected:', decodedText);
                        barcodeInput.value = decodedText;
                        scannedBarcodeInput.value = decodedText;
                        verifyAndDisplayClaim(decodedText);
                        stopClaimScanner();
                        
                        // Show success message
                        showToast('Claim slip scanned successfully!', 'success');
                    },
                    (errorMessage) => {
                        // Handle scan errors silently - this happens frequently while scanning
                    }
                ).then(() => {
                    console.log('Claim slip scanner started successfully');
                }).catch(err => {
                    console.error('Error starting claim slip scanner:', err);
                    alert('Failed to start scanner: ' + err.message);
                    stopClaimScanner();
                });
            } else {
                alert('No cameras found. Please ensure you have a camera connected and grant camera permissions.');
                stopClaimScanner();
            }
        }).catch(err => {
            console.error('Error accessing cameras for claim slip scanner:', err);
            alert('Error accessing camera: ' + err.message + '. Please grant camera permissions and refresh the page.');
            stopClaimScanner();
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
        
        if (window.claimSlipScanner) {
            window.claimSlipScanner.stop().then(() => {
                console.log('Claim slip scanner stopped');
                window.claimSlipScanner = null;
            }).catch(err => {
                console.error('Error stopping claim slip scanner:', err);
            });
        }
        
        scanBtn.innerHTML = '<i class="fas fa-qrcode"></i>';
        scanBtn.classList.remove('btn-danger');
        scanBtn.classList.add('btn-outline-primary');
        scanBtn.title = 'Scan Barcode';
    }

    function verifyAndDisplayClaim(qrDataString) {
        // Show loading state immediately
        claimDetailsContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Verifying claim slip...</div>
            </div>
        `;
        claimDetailsDiv.style.display = 'block';

        try {
            // Parse the QR data as JSON
            const qrData = JSON.parse(qrDataString);
            console.log('Parsed QR data:', qrData);

            // Make AJAX request to verify the QR code data
            fetch(`{{ url('admin/requests/verify-claim-slip-qr') }}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    qr_data: qrData,
                    request_id: {{ $request->id }}
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayClaimDetails(data.data);
                    // Set the hidden input for the verified QR data
                    scannedBarcodeInput.value = qrDataString;
                    checkClaimButtonState(); // Check if verification is complete
                    showToast('Claim slip verified successfully!', 'success');
                } else {
                    showClaimError(data.message || 'Claim slip verification failed');
                    claimBtn.disabled = true;
                    scannedBarcodeInput.value = '';
                }
            })
            .catch(error => {
                console.error('Error verifying claim slip:', error);
                showClaimError('Error verifying claim slip. Please try again.');
                claimBtn.disabled = true;
                scannedBarcodeInput.value = '';
            });
        } catch (parseError) {
            console.error('Error parsing QR data:', parseError);
            showClaimError('Invalid QR code format. Please ensure you are scanning a valid claim slip QR code.');
            claimBtn.disabled = true;
            scannedBarcodeInput.value = '';
        }
    }

    function displayClaimDetails(verificationData) {
        claimDetailsContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-ticket-alt me-1"></i>Claim Slip Details</h6>
                    <p class="mb-1"><strong>Claim Slip Number:</strong> <code>${verificationData.claim_slip_number}</code></p>
                    <p class="mb-1"><strong>Requester:</strong> ${verificationData.user_name}</p>
                    <p class="mb-1"><strong>Office:</strong> ${verificationData.department}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success mb-2"><i class="fas fa-box me-1"></i>Request Information</h6>
                    <p class="mb-1"><strong>Items:</strong> ${verificationData.items_count} different items</p>
                    <p class="mb-1"><strong>Total Quantity:</strong> ${verificationData.total_quantity} pieces</p>
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
        checkClaimButtonState();
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


function checkClaimButtonState() {
    const claimBtn = document.getElementById('claim-btn');
    const claimDetailsDiv = document.getElementById('verified-claim-details');

    // For all requests (single and bulk), only claim slip verification is needed
    const claimSlipVerified = claimDetailsDiv.style.display !== 'none' &&
                             !claimDetailsDiv.querySelector('.alert-danger');
    claimBtn.disabled = !claimSlipVerified;
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

// Item adjustment functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentAction = null;
    let currentItemId = null;
    let currentItemName = null;
    
    const modal = new bootstrap.Modal(document.getElementById('itemActionModal'), {
        backdrop: 'static',
        keyboard: true
    });
    
    // Ensure modal is properly positioned when shown
    document.getElementById('itemActionModal').addEventListener('shown.bs.modal', function() {
        const modalElement = document.getElementById('itemActionModal');
        
        // Move modal to body for proper positioning if needed
        if (modalElement.parentNode !== document.body) {
            document.body.appendChild(modalElement);
        }

        // Ensure backdrop exists and is properly styled
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '1055';
            document.body.appendChild(backdrop);
        }

        // Apply proper modal positioning
        modalElement.style.position = 'fixed';
        modalElement.style.top = '0';
        modalElement.style.left = '0';
        modalElement.style.width = '100%';
        modalElement.style.height = '100%';
        modalElement.style.zIndex = '1055';
        modalElement.style.display = 'block';
        modalElement.style.overflow = 'hidden';
        modalElement.style.outline = '0';

        // Just ensure proper styling is applied
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.border = 'none';
            modalContent.style.borderRadius = '20px';
            modalContent.style.boxShadow = '0 20px 60px rgba(0, 0, 0, 0.3)';
        }
    });
    const modalTitle = document.getElementById('itemActionModalLabel');
    const modalContent = document.getElementById('itemActionContent');
    const confirmBtn = document.getElementById('confirmItemActionBtn');
    
    // Handle quantity adjustments
    document.querySelectorAll('.adjust-quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAction = 'adjust';
            currentItemId = this.getAttribute('data-item-id');
            currentItemName = this.getAttribute('data-item-name');
            const currentQuantity = this.getAttribute('data-current-quantity');
            
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Adjust Quantity';
            modalContent.innerHTML = `
                <div class="mb-3">
                    <label for="adjust-quantity" class="form-label">
                        <strong>Adjust quantity for "${currentItemName}"</strong>
                    </label>
                    <input type="number" class="form-control" id="adjust-quantity" value="${currentQuantity}" min="1" required>
                    <div class="form-text">Current quantity: ${currentQuantity}</div>
                </div>
                <div class="mb-3">
                    <label for="adjust-reason" class="form-label">
                        <strong>Reason for adjustment <span class="text-danger">*</span></strong>
                    </label>
                    <textarea class="form-control" id="adjust-reason" rows="3" placeholder="Please provide a detailed reason for adjusting the quantity..." required></textarea>
                    <div class="form-text">This reason will be recorded with the adjustment.</div>
                </div>
            `;
            
            confirmBtn.className = 'btn btn-primary';
            confirmBtn.innerHTML = '<i class="fas fa-save me-2"></i>Adjust Quantity';
            
            modal.show();
        });
    });
    
    // Handle item declines
    document.querySelectorAll('.decline-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAction = 'decline';
            currentItemId = this.getAttribute('data-item-id');
            currentItemName = this.getAttribute('data-item-name');
            
            modalTitle.innerHTML = '<i class="fas fa-ban me-2"></i>Decline Item';
            modalContent.innerHTML = `
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The item will be permanently declined.
                </div>
                <div class="mb-3">
                    <label for="decline-reason" class="form-label">
                        <strong>Reason for declining "${currentItemName}" <span class="text-danger">*</span></strong>
                    </label>
                    <textarea class="form-control" id="decline-reason" rows="3" placeholder="Please provide a detailed reason for declining this item..." required></textarea>
                    <div class="form-text">This reason will be visible to the requestor.</div>
                </div>
            `;
            
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-ban me-2"></i>Decline Item';
            
            modal.show();
        });
    });
    
    // Handle item approvals
    document.querySelectorAll('.approve-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentAction = 'approve';
            currentItemId = this.getAttribute('data-item-id');
            currentItemName = this.getAttribute('data-item-name');
            
            modalTitle.innerHTML = '<i class="fas fa-check me-2"></i>Approve Item';
            modalContent.innerHTML = `
                <div class="mb-3">
                    <p>Are you sure you want to approve <strong>"${currentItemName}"</strong>?</p>
                </div>
                <div class="mb-3">
                    <label for="approve-reason" class="form-label">
                        <strong>Remarks (Optional)</strong>
                    </label>
                    <textarea class="form-control" id="approve-reason" rows="3" placeholder="Optional remarks for approving this item..."></textarea>
                    <div class="form-text">These remarks will be recorded with the approval.</div>
                </div>
            `;
            
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Approve Item';
            
            modal.show();
        });
    });
    
    // Handle confirm action
    confirmBtn.addEventListener('click', function() {
        if (!currentAction || !currentItemId) return;
        
        let data = {};
        let url = '';
        
        if (currentAction === 'adjust') {
            const newQuantity = parseInt(document.getElementById('adjust-quantity').value);
            const reason = document.getElementById('adjust-reason').value.trim();
            
            if (!newQuantity || newQuantity < 1) {
                alert('Please enter a valid quantity.');
                return;
            }
            
            if (!reason) {
                alert('Reason is required for quantity adjustments.');
                return;
            }
            
            data = { quantity: newQuantity, reason: reason };
            url = `/admin/requests/{{ $request->id }}/items/${currentItemId}/adjust`;
        } else if (currentAction === 'decline') {
            const reason = document.getElementById('decline-reason').value.trim();
            
            if (!reason) {
                alert('Reason is required for declining items.');
                return;
            }
            
            data = { reason: reason };
            url = `/admin/requests/{{ $request->id }}/items/${currentItemId}/decline`;
        } else if (currentAction === 'approve') {
            const reason = document.getElementById('approve-reason').value.trim();
            
            data = { reason: reason };
            url = `/admin/requests/{{ $request->id }}/items/${currentItemId}/approve`;
        }
        
        // Show loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        
        // Send AJAX request
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                // If response is not ok, try to get the error message
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modal.hide();
                location.reload(); // Reload to show changes
            } else {
                alert('Error: ' + data.message);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = currentAction === 'adjust' ? '<i class="fas fa-save me-2"></i>Adjust Quantity' : 
                                      currentAction === 'decline' ? '<i class="fas fa-ban me-2"></i>Decline Item' : 
                                      '<i class="fas fa-check me-2"></i>Approve Item';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'An error occurred while processing the request.';
            if (error.message) {
                errorMessage += '\nDetails: ' + error.message;
            }
            alert(errorMessage);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = currentAction === 'adjust' ? '<i class="fas fa-save me-2"></i>Adjust Quantity' : 
                                  currentAction === 'decline' ? '<i class="fas fa-ban me-2"></i>Decline Item' : 
                                  '<i class="fas fa-check me-2"></i>Approve Item';
        });
    });
    
    // Reset modal state when hidden
    document.getElementById('itemActionModal').addEventListener('hidden.bs.modal', function() {
        const modalElement = document.getElementById('itemActionModal');
        
        // Reset modal positioning styles
        modalElement.style.position = '';
        modalElement.style.top = '';
        modalElement.style.left = '';
        modalElement.style.width = '';
        modalElement.style.height = '';
        modalElement.style.zIndex = '';
        modalElement.style.display = '';
        modalElement.style.overflow = '';
        modalElement.style.outline = '';
        
        const modalContent = modalElement.querySelector('.modal-content');
        
        // Reset custom styling
        if (modalContent) {
            modalContent.style.border = '';
            modalContent.style.borderRadius = '';
            modalContent.style.boxShadow = '';
        }
        
        // Reset variables
        currentAction = null;
        currentItemId = null;
        currentItemName = null;
        confirmBtn.disabled = false;
    });
});
</script>
@endpush

@endsection