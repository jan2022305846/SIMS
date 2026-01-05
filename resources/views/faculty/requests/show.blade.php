@extends('layouts.app')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 fw-semibold text-dark mb-0">
            <i class="fas fa-eye me-2 text-warning"></i>
            Request Details
        </h2>
        <div class="d-flex gap-2">
            @if($request->isPending())
                <button type="button" class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-times me-1"></i>Cancel Request
                </button>
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
            <!-- Request Information and Actions in Flex Layout -->
            <div class="col-lg-8 mb-4">
                <!-- Main Request Details -->
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
                                        @switch($request->status)
                                            @case('pending') bg-warning @break
                                            @case('approved_by_admin') bg-success @break
                                            @case('ready_for_pickup') bg-purple text-white @break
                                            @case('fulfilled') bg-purple text-white @break
                                            @case('claimed') bg-secondary @break
                                            @case('declined') bg-danger @break
                                            @case('cancelled') bg-secondary @break
                                            @default bg-secondary @break
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
                                        <div class="mb-1"><strong>Submitted:</strong> {{ $request->created_at ? $request->created_at->format('M j, Y') : 'N/A' }}</div>
                                        <div class="mb-1"><strong>Needed by:</strong> {{ $request->needed_date ? $request->needed_date->format('M j, Y') : 'N/A' }}</div>
                                        @if($request->status === 'fulfilled' || $request->status === 'claimed')
                                            <div class="mb-1 text-success"><strong>Ready for pickup:</strong> {{ $request->updated_at->format('M j, Y') }}</div>
                                        @endif
                                        @if($request->status === 'claimed')
                                            <div class="mb-1 text-success"><strong>Completed:</strong> {{ $request->updated_at->format('M j, Y') }}</div>
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
                                        @if($request->requestItems->count() > 0)
                                            @foreach($request->requestItems as $requestItem)
                                                <div class="mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                                                    <h5 class="mb-2">{{ $requestItem->itemable ? $requestItem->itemable->name : 'Item Not Found' }}</h5>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small class="text-muted">
                                                                @if($requestItem->item_status === 'declined')
                                                                    Requested Quantity
                                                                @elseif($requestItem->item_status === 'approved')
                                                                    @if($requestItem->isAdjusted())
                                                                        Approved Quantity
                                                                    @else
                                                                        Requested Quantity
                                                                    @endif
                                                                @else
                                                                    Requested Quantity
                                                                @endif
                                                            </small>
                                                            <div class="fw-bold fs-5">
                                                                @if($requestItem->item_status === 'declined')
                                                                    {{ $requestItem->quantity }} {{ $requestItem->itemable && $requestItem->itemable->unit ? $requestItem->itemable->unit : 'pcs' }}
                                                                @elseif($requestItem->item_status === 'approved')
                                                                    {{ $requestItem->getFinalQuantity() }} {{ $requestItem->itemable && $requestItem->itemable->unit ? $requestItem->itemable->unit : 'pcs' }}
                                                                @else
                                                                    {{ $requestItem->quantity }} {{ $requestItem->itemable && $requestItem->itemable->unit ? $requestItem->itemable->unit : 'pcs' }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Available Stock</small>
                                                            <div class="fw-bold fs-5 {{ $requestItem->itemable && $requestItem->itemable->quantity < $requestItem->quantity ? 'text-danger' : 'text-success' }}">
                                                                {{ $requestItem->itemable ? $requestItem->itemable->quantity : 'N/A' }} {{ $requestItem->itemable && $requestItem->itemable->unit ? $requestItem->itemable->unit : 'pcs' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($requestItem->item_status === 'declined')
                                                        <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded">
                                                            <small class="text-danger fw-bold"><i class="fas fa-times me-1"></i>Declined</small>
                                                            @if($requestItem->adjustment_reason)
                                                                <div class="small text-danger mt-1">{{ $requestItem->adjustment_reason }}</div>
                                                            @endif
                                                        </div>
                                                    @elseif($requestItem->isAdjusted())
                                                        <div class="mt-2 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                                                            <small class="text-warning fw-bold"><i class="fas fa-edit me-1"></i>Quantity Adjusted to {{ $requestItem->getFinalQuantity() }}</small>
                                                            @if($requestItem->adjustment_reason)
                                                                <div class="small text-warning mt-1">{{ $requestItem->adjustment_reason }}</div>
                                                            @endif
                                                        </div>
                                                    @elseif($requestItem->item_status === 'approved' && $requestItem->adjustment_reason)
                                                        <div class="mt-2 p-2 bg-success bg-opacity-10 border border-success rounded">
                                                            <small class="text-success fw-bold"><i class="fas fa-check me-1"></i>Approved</small>
                                                            <div class="small text-success mt-1">{{ $requestItem->adjustment_reason }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <h5 class="mb-2">Item Not Found</h5>
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Requested Quantity</small>
                                                    <div class="fw-bold fs-5">0 pcs</div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Available Stock</small>
                                                    <div class="fw-bold fs-5 text-muted">N/A pcs</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Request Details</h6>
                                <div class="mb-3">
                                    <div class="row mb-2">
                                        <div class="col-5"><strong>Office:</strong></div>
                                        <div class="col-7">{{ $request->user->office ? $request->user->office->name : 'N/A' }}</div>
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

            <!-- Actions Sidebar -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Flash Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error') || $errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') ?? $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if($request->canGenerateClaimSlip())
                            @if($request->isApprovedByAdmin())
                                <!-- For approved requests, show only Generate button to avoid redundancy -->
                                <form action="{{ route('faculty.requests.generate-claim-slip', $request) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-ticket-alt me-2"></i>Generate Claim Slip
                                    </button>
                                </form>
                            @else
                                <!-- For other statuses that can generate claim slip, show both buttons -->
                                <div class="d-flex gap-2 mb-2">
                                    <a href="{{ route('requests.claim-slip', $request) }}"
                                       class="btn btn-outline-info flex-fill"
                                       target="_blank">
                                        <i class="fas fa-eye me-2"></i>Preview Claim Slip
                                    </a>
                                    <form action="{{ route('faculty.requests.generate-claim-slip', $request) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-ticket-alt me-2"></i>Generate Claim Slip
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif

                        @if($request->isReadyForPickup() || $request->isFulfilled() || $request->isClaimed())
                            <a href="{{ route('requests.claim-slip', $request) }}"
                               class="btn btn-warning w-100 mb-2"
                               target="_blank">
                                <i class="fas fa-print me-2"></i>Print Claim Slip
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
                        @elseif($request->isCancelled())
                            <div class="alert alert-secondary border">
                                <i class="fas fa-ban me-2 text-dark"></i>
                                <strong class="text-dark">This request was cancelled.</strong>
                                @if($request->notes)
                                    <br><small class="text-muted"><strong>Reason:</strong> {{ $request->notes }}</small>
                                @endif
                            </div>
                        @elseif($request->isDeclined())
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                This request was declined. Please check with your department head for details.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Workflow Timeline - Full Width Below -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-route me-2"></i>
                        <h5 class="mb-0">Request Workflow Progress</h5>
                        <div class="ms-auto">
                            <small class="text-white-50">
                                <i class="fas fa-clock me-1"></i>
                                @if($request->status === 'claimed')
                                    Completed in {{ $request->created_at->diffInDays($request->updated_at) + 1 }} days
                                @elseif(in_array($request->status, ['fulfilled', 'ready_for_pickup']))
                                    In Progress
                                @elseif(in_array($request->status, ['cancelled', 'declined']))
                                    Terminated
                                @else
                                    Pending
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
                                                    {{ $request->created_at ? $request->created_at->format('M j, Y g:i A') : 'N/A' }}
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
                                                Faculty member submitted a request for {{ $request->getTotalItems() }} {{ $request->getTotalItems() === 1 ? 'item' : 'items' }} of {{ $request->getUniqueItemsCount() }} {{ $request->getUniqueItemsCount() === 1 ? 'unique item' : 'unique items' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Admin Approval -->
                            <div class="workflow-step {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : ($request->status === 'declined' ? 'declined' : ($request->status === 'cancelled' ? 'cancelled' : 'current')) }}">
                                <div class="workflow-marker {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : ($request->status === 'declined' ? 'bg-danger' : ($request->status === 'cancelled' ? 'bg-secondary' : 'bg-primary')) }}">
                                    <div class="step-number">2</div>
                                    <i class="fas {{ in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']) ? 'fa-shield-check' : ($request->status === 'declined' ? 'fa-times' : ($request->status === 'cancelled' ? 'fa-ban' : 'fa-shield-alt')) }} step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Admin Approval</h6>
                                        @if(in_array($request->status, ['approved_by_admin', 'ready_for_pickup', 'fulfilled', 'claimed']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Approved
                                            </span>
                                        @elseif($request->status === 'declined')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                <i class="fas fa-times-circle me-1"></i>Declined
                                            </span>
                                        @elseif($request->status === 'cancelled')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-ban me-1"></i>Cancelled
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </div>
                                    <div class="step-details">
                                        @if($request->approved_by_admin_id)
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        {{ $request->updated_at->format('M j, Y g:i A') }}
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
                                        @elseif($request->status === 'declined')
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <small class="text-danger d-block">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <strong>Declined:</strong> {{ $request->notes ?? 'No reason provided' }}
                                                    </small>
                                                </div>
                                            </div>
                                        @elseif($request->status === 'cancelled')
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <small class="text-dark d-block">
                                                        <i class="fas fa-ban me-1 text-danger"></i>
                                                        <strong class="text-dark">Cancelled by Faculty:</strong> {{ $request->notes ?? 'No reason provided' }}
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
                            <div class="workflow-step {{ in_array($request->status, ['ready_for_pickup', 'fulfilled', 'claimed']) ? 'completed' : (in_array($request->status, ['approved_by_admin']) ? 'current' : (in_array($request->status, ['declined', 'cancelled']) ? 'terminated' : '')) }}">
                                <div class="workflow-marker {{ in_array($request->status, ['ready_for_pickup', 'fulfilled', 'claimed']) ? 'bg-success' : (in_array($request->status, ['approved_by_admin']) ? 'bg-primary' : (in_array($request->status, ['declined', 'cancelled']) ? 'bg-secondary' : 'bg-secondary')) }}">
                                    <div class="step-number">3</div>
                                    <i class="fas fa-ticket-alt step-icon"></i>
                                </div>
                                <div class="workflow-content">
                                    <div class="step-header">
                                        <h6 class="step-title mb-1">Claim Slip Generation</h6>
                                        @if(in_array($request->status, ['ready_for_pickup', 'fulfilled', 'claimed']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-check-circle me-1"></i>Generated
                                            </span>
                                        @elseif(in_array($request->status, ['approved_by_admin']))
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @elseif(in_array($request->status, ['declined', 'cancelled']))
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-minus-circle me-1"></i>Skipped
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
                                                    Faculty needs to generate a claim slip to proceed with pickup
                                                </small>
                                            </div>
                                        @elseif(in_array($request->status, ['declined', 'cancelled']))
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    Claim slip generation not required due to {{ $request->status === 'declined' ? 'declined' : 'cancelled' }} request
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
                            <div class="workflow-step {{ $request->status === 'claimed' ? 'completed' : ($request->status === 'ready_for_pickup' ? 'current' : (in_array($request->status, ['declined', 'cancelled']) ? 'terminated' : '')) }}">
                                <div class="workflow-marker {{ $request->status === 'claimed' ? 'bg-success' : ($request->status === 'ready_for_pickup' ? 'bg-primary' : (in_array($request->status, ['declined', 'cancelled']) ? 'bg-secondary' : 'bg-secondary')) }}">
                                    <div class="step-number">4</div>
                                    <i class="fas {{ $request->status === 'claimed' ? 'fa-handshake' : (in_array($request->status, ['declined', 'cancelled']) ? 'fa-times-circle' : 'fa-hand-paper') }} step-icon"></i>
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
                                        @elseif(in_array($request->status, ['declined', 'cancelled']))
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-minus-circle me-1"></i>Cancelled
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="fas fa-pause me-1"></i>Waiting
                                            </span>
                                        @endif
                                    </div>
                                    <div class="step-details">
                                        @if($request->status === 'claimed')
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        {{ $request->updated_at->format('M j, Y g:i A') }}
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
                                                    Dual verification completed: Claim slip QR code + Item barcode scanned successfully
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
                                                    <li><strong>Step 2:</strong> Admin scans the actual item barcode for item verification</li>
                                                    <li><strong>Step 3:</strong> Items are handed over and marked as claimed</li>
                                                </ul>
                                            </div>
                                        @elseif(in_array($request->status, ['declined', 'cancelled']))
                                            <div class="step-description">
                                                <small class="text-muted">
                                                    Item pickup not available due to {{ $request->status === 'declined' ? 'declined' : 'cancelled' }} request
                                                </small>
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
@endsection

<!-- Cancel Modal -->
@if($request->isPending())
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('faculty.requests.cancel', $request) }}" id="cancelForm">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone. The request will be permanently cancelled.
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for cancelling <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Please provide a reason for cancelling this request..." required></textarea>
                            <div class="form-text">This reason will be recorded for reference.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Keep Request
                        </button>
                        <button type="submit" class="btn btn-danger" id="cancelSubmitBtn">
                            <i class="fas fa-ban me-2"></i>Cancel Request
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

    .workflow-step.cancelled {
        opacity: 1;
    }

    .workflow-step.terminated {
        opacity: 0.7;
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

    .workflow-step.cancelled .workflow-marker {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
    }

    .workflow-step.terminated .workflow-marker {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
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

    /* Dark mode support for content cards - Universal override with maximum specificity */
    [data-bs-theme="dark"] .workflow-timeline .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step.completed .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step.current .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step.declined .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step.cancelled .workflow-content,
    [data-bs-theme="dark"] .workflow-timeline .workflow-step.terminated .workflow-content,
    [data-bs-theme="dark"] div.workflow-content {
        background-color: #343a40 !important;
        background-image: none !important;
        border-color: #495057 !important;
        color: #ffffff !important;
    }

    .workflow-step.completed .workflow-content {
        border-color: #d4edda;
        background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.1);
    }

    [data-bs-theme="dark"] .workflow-step.completed .workflow-content {
        border-color: #155724 !important;
        box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2) !important;
    }

    .workflow-step.current .workflow-content {
        border-color: #cce7ff;
        background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    [data-bs-theme="dark"] .workflow-step.current .workflow-content {
        border-color: #004085 !important;
        box-shadow: 0 4px 16px rgba(0, 123, 255, 0.25) !important;
        transform: translateY(-2px);
    }

    .workflow-step.declined .workflow-content {
        border-color: #f5c6cb;
        background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(220, 53, 69, 0.1);
    }

    [data-bs-theme="dark"] .workflow-step.declined .workflow-content {
        border-color: #721c24 !important;
        box-shadow: 0 4px 16px rgba(220, 53, 69, 0.2) !important;
    }

    .workflow-step.cancelled .workflow-content {
        border-color: #d1d5db;
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(108, 117, 125, 0.1);
    }

    [data-bs-theme="dark"] .workflow-step.cancelled .workflow-content {
        border-color: #495057 !important;
        box-shadow: 0 4px 16px rgba(108, 117, 125, 0.2) !important;
    }

    .workflow-step.terminated .workflow-content {
        border-color: #d1d5db;
        background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(108, 117, 125, 0.1);
    }

    [data-bs-theme="dark"] .workflow-step.terminated .workflow-content {
        border-color: #495057 !important;
        box-shadow: 0 4px 16px rgba(108, 117, 125, 0.2) !important;
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

    [data-bs-theme="dark"] .step-description {
        color: #adb5bd;
    }

    /* Improve text readability for small elements in workflow */
    [data-bs-theme="dark"] .workflow-content small {
        color: #adb5bd !important;
    }

    [data-bs-theme="dark"] .workflow-content strong {
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .workflow-content .text-muted {
        color: #adb5bd !important;
    }

    [data-bs-theme="dark"] .workflow-content .text-primary {
        color: #6ea8fe !important;
    }

    [data-bs-theme="dark"] .workflow-content .text-success {
        color: #75b798 !important;
    }

    [data-bs-theme="dark"] .workflow-content .text-danger {
        color: #ea868f !important;
    }

    [data-bs-theme="dark"] .workflow-content .text-dark {
        color: #ffffff !important;
    }

    /* Badge improvements */
    .badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 6px;
    }

    [data-bs-theme="dark"] .workflow-content .text-dark {
        color: #ffffff !important;
    }

    /* Improve list readability in workflow */
    [data-bs-theme="dark"] .workflow-content ul li {
        color: #adb5bd;
    }

    [data-bs-theme="dark"] .workflow-content ul li strong {
        color: #ffffff;
    }

    /* Ensure all small text in workflow is readable */
    [data-bs-theme="dark"] .workflow-content small {
        color: #adb5bd !important;
    }

    /* Fix bg-light elements inside workflow in dark mode */
    [data-bs-theme="dark"] .workflow-content .bg-light,
    [data-bs-theme="dark"] .workflow-content code.bg-light {
        background-color: #495057 !important;
        color: #ffffff !important;
    }

    /* Improve badge text contrast in dark mode */
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
</style>