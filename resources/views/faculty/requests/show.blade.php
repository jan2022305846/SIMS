@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-eye me-2 text-warning"></i>
            Request Details
        </h2>
        <div class="d-flex gap-2">
            @if($request->isReadyForPickup() || $request->isFulfilled() || $request->isClaimed())
                <a href="{{ route('requests.claim-slip', $request) }}" class="btn btn-warning fw-bold" target="_blank">
                    <i class="fas fa-print me-1"></i>Print Claim Slip
                </a>
                <a href="{{ route('faculty.requests.download-claim-slip', $request) }}" class="btn btn-success fw-bold">
                    <i class="fas fa-download me-1"></i>Download PDF
                </a>
            @endif
            <a href="{{ route('faculty.requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to My Requests
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="container">
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
                                <h6 class="text-muted mb-2">Request Status</h6>
                                <div class="mb-3">
                                    <span class="badge fs-6 px-3 py-2
                                        @switch($request->workflow_status)
                                            @case('pending') bg-warning @break
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
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request Timeline</h6>
                                <div class="mb-3">
                                    <div class="small">
                                        <div class="mb-1"><strong>Submitted:</strong> {{ $request->request_date ? $request->request_date->format('M j, Y g:i A') : 'N/A' }}</div>
                                        <div class="mb-1"><strong>Needed by:</strong> {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</div>
                                        @if($request->fulfilled_date)
                                            <div class="mb-1 text-success"><strong>Ready for pickup:</strong> {{ $request->fulfilled_date->format('M j, Y') }}</div>
                                        @endif
                                        @if($request->claimed_date)
                                            <div class="mb-1 text-success"><strong>Completed:</strong> {{ $request->claimed_date->format('M j, Y') }}</div>
                                        @endif
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

            <!-- Actions & Timeline -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($request->canGenerateClaimSlip())
                            <form action="{{ route('faculty.requests.generate-claim-slip', $request) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-ticket-alt me-2"></i>Generate Claim Slip
                                </button>
                            </form>
                        @endif

                        @if($request->isReadyForPickup() || $request->isFulfilled() || $request->isClaimed())
                            <a href="{{ route('requests.claim-slip', $request) }}"
                               class="btn btn-warning w-100 mb-2"
                               target="_blank">
                                <i class="fas fa-print me-2"></i>Print Claim Slip
                            </a>
                        @endif

                        @if($request->canBeAcknowledgedByRequester())
                            <a href="{{ route('faculty.requests.acknowledgment.show', $request) }}"
                               class="btn btn-success w-100 mb-2">
                                <i class="fas fa-signature me-2"></i>Acknowledge Receipt
                            </a>
                        @endif

                        @if($request->isPending())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Your request is being reviewed. You'll be notified once it's approved.
                            </div>
                        @elseif($request->isApprovedByAdmin())
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Your request has been approved! Click "Generate Claim Slip" to prepare for pickup.
                            </div>
                        @elseif($request->isReadyForPickup())
                            <div class="alert alert-info">
                                <i class="fas fa-ticket-alt me-2"></i>
                                Claim slip generated. Print it and visit the supply office to pick up your items.
                            </div>
                        @elseif($request->isFulfilled())
                            <div class="alert alert-warning">
                                <i class="fas fa-box-open me-2"></i>
                                Your items are ready for pickup! Please acknowledge receipt when you collect them.
                            </div>
                        @elseif($request->isClaimed())
                            <div class="alert alert-secondary">
                                <i class="fas fa-handshake me-2"></i>
                                Request completed successfully.
                            </div>
                        @elseif($request->isDeclined())
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                This request was declined. Please check with your department head for details.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Workflow Timeline -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Request Timeline
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
                                    <p class="mb-0 small">Request created successfully</p>
                                </div>
                            </div>

                            <!-- Admin Approval -->
                            <div class="timeline-item {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : ($request->workflow_status === 'declined' ? 'declined' : '') }}">
                                <div class="timeline-marker {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->workflow_status === 'declined' ? 'bg-danger' : 'bg-secondary') }}">
                                    <i class="fas {{ in_array($request->workflow_status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'fa-shield-check' : ($request->workflow_status === 'declined' ? 'fa-times' : 'fa-shield-alt') }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Admin Approval</h6>
                                    @if($request->admin_approval_date)
                                        <p class="mb-1 text-success small">{{ $request->admin_approval_date->format('M j, Y g:i A') }}</p>
                                        <p class="mb-0 small">Approved by {{ $request->adminApprover->name ?? 'Administrator' }}</p>
                                    @elseif($request->workflow_status === 'declined')
                                        <p class="mb-1 text-danger small">Declined</p>
                                        @if($request->admin_notes)
                                            <p class="mb-0 small text-muted">"{{ $request->admin_notes }}"</p>
                                        @endif
                                    @else
                                        <p class="mb-0 text-muted small">Waiting for admin approval</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Claim Slip Generation -->
                            <div class="timeline-item {{ in_array($request->workflow_status, ['ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : '' }}">
                                <div class="timeline-marker {{ in_array($request->workflow_status, ['ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ in_array($request->workflow_status, ['ready_for_pickup', 'fulfilled', 'claimed']) ? 'fa-ticket-alt' : 'fa-ticket-alt' }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Claim Slip Generation</h6>
                                    @if($request->claim_slip_number && $request->workflow_status !== 'approved_by_admin')
                                        <p class="mb-1 text-success small">Generated</p>
                                        <p class="mb-0 small">Claim slip: <code>{{ $request->claim_slip_number }}</code></p>
                                    @else
                                        <p class="mb-0 text-muted small">Waiting for claim slip generation</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Item Pickup -->
                            <div class="timeline-item {{ $request->workflow_status === 'claimed' ? 'completed' : '' }}">
                                <div class="timeline-marker {{ $request->workflow_status === 'claimed' ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ $request->workflow_status === 'claimed' ? 'fa-handshake' : 'fa-hand-paper' }} text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Item Pickup & Claim</h6>
                                    @if($request->claimed_date)
                                        <p class="mb-1 text-success small">{{ $request->claimed_date->format('M j, Y g:i A') }}</p>
                                        <p class="mb-0 small">Items claimed and stock updated</p>
                                    @else
                                        <p class="mb-0 text-muted small">Visit supply office with printed claim slip</p>
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
@endsection

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
</style>