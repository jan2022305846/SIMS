@extends('layouts.app')

@section('title', 'Digital Receipt - Request #' . $request->id)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Print/Download Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
                <a href="{{ route('requests.acknowledgment.show', $request) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Acknowledgment
                </a>
                
                <div>
                    <button onclick="window.print()" class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>
                    <a href="{{ route('requests.acknowledgment.download', $request) }}" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </a>
                </div>
            </div>

            <!-- Receipt Card -->
            <div class="card border-0 shadow-sm" id="receipt">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center border-bottom pb-4 mb-4">
                        <h2 class="text-primary mb-1">DIGITAL ACKNOWLEDGMENT RECEIPT</h2>
                        <p class="text-muted mb-0">Supply Office Management System</p>
                        <small class="text-muted">{{ config('app.name', 'Supply Office') }}</small>
                    </div>

                    <!-- Receipt Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 140px;">Receipt Number:</td>
                                    <td class="text-primary fw-bold">{{ $acknowledgment->receipt_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Request ID:</td>
                                    <td>#{{ $request->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date Issued:</td>
                                    <td>{{ $acknowledgment->created_at->format('F d, Y - h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted d-block">Verification Hash:</small>
                                <code class="small">{{ substr($acknowledgment->verification_hash, 0, 16) }}...</code>
                            </div>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3">Request Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 140px;">Requested By:</td>
                                        <td>{{ $request->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Department:</td>
                                        <td>{{ $request->user->department ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Purpose:</td>
                                        <td>{{ $request->purpose }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Priority:</td>
                                        <td>
                                            <span class="badge bg-{{ $request->priority === 'high' ? 'danger' : ($request->priority === 'medium' ? 'warning' : 'info') }}">
                                                {{ ucfirst($request->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 140px;">Date Requested:</td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date Needed:</td>
                                        <td>{{ $request->date_needed ? \Carbon\Carbon::parse($request->date_needed)->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td><span class="badge bg-{{ $request->status_color }}">{{ $request->status_label }}</span></td>
                                    </tr>
                                    @if($request->notes)
                                    <tr>
                                        <td class="fw-bold">Notes:</td>
                                        <td>{{ $request->notes }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3">Items Received</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th class="text-center">Qty Requested</th>
                                        <th class="text-center">Qty Received</th>
                                        <th>Unit</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $item->name }}</td>
                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $item->pivot->quantity_requested }}</td>
                                        <td class="text-center text-success fw-bold">{{ $item->pivot->quantity_approved ?? $item->pivot->quantity_requested }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->description ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Acknowledgment Details -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3">Acknowledgment Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 150px;">Acknowledged By:</td>
                                        <td>{{ $acknowledgment->acknowledgedBy->name }}</td>
                                    </tr>
                                    @if($acknowledgment->witnessed_by)
                                    <tr>
                                        <td class="fw-bold">Witnessed By:</td>
                                        <td>{{ $acknowledgment->witnessedBy->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="fw-bold">Condition Received:</td>
                                        <td>
                                            <span class="badge bg-{{ $acknowledgment->received_condition === 'excellent' ? 'success' : ($acknowledgment->received_condition === 'good' ? 'primary' : ($acknowledgment->received_condition === 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($acknowledgment->received_condition) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date Acknowledged:</td>
                                        <td>{{ $acknowledgment->created_at->format('F d, Y - h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if($acknowledgment->signature_path)
                                <div class="text-center">
                                    <p class="fw-bold mb-2">Digital Signature:</p>
                                    <div class="border rounded p-2 bg-light d-inline-block">
                                        <img src="{{ Storage::url($acknowledgment->signature_path) }}" 
                                             alt="Digital Signature" 
                                             style="max-height: 100px; max-width: 200px;">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($acknowledgment->notes)
                        <div class="mt-3">
                            <p class="fw-bold mb-1">Additional Notes:</p>
                            <div class="bg-light p-3 rounded">
                                {{ $acknowledgment->notes }}
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($acknowledgment->gps_coordinates)
                    <!-- Location Information -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2 mb-3">Location Information</h5>
                        @php
                            $location = json_decode($acknowledgment->gps_coordinates, true);
                        @endphp
                        @if(isset($location['latitude']) && isset($location['longitude']))
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold" style="width: 120px;">Latitude:</td>
                                        <td>{{ $location['latitude'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Longitude:</td>
                                        <td>{{ $location['longitude'] }}</td>
                                    </tr>
                                    @if(isset($location['accuracy']))
                                    <tr>
                                        <td class="fw-bold">Accuracy:</td>
                                        <td>±{{ round($location['accuracy']) }}m</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Location data captured at the time of acknowledgment for verification purposes.
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Verification Information -->
                    <div class="bg-light p-4 rounded mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Verification & Security
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-2"><strong>Verification Hash:</strong></p>
                                <code class="small">{{ $acknowledgment->verification_hash }}</code>
                                <p class="text-muted small mt-2 mb-0">
                                    This hash can be used to verify the integrity and authenticity of this acknowledgment.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('requests.acknowledgment.verify', $request) }}" 
                                   class="btn btn-outline-primary btn-sm d-print-none">
                                    <i class="fas fa-check-circle me-1"></i> Verify Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center border-top pt-4 mt-4">
                        <p class="text-muted small mb-1">
                            This is a digitally generated receipt from the Supply Office Management System.
                        </p>
                        <p class="text-muted small mb-0">
                            Generated on {{ now()->format('F d, Y - h:i A') }} | 
                            <a href="{{ route('requests.acknowledgment.verify', $request) }}" class="text-decoration-none d-print-none">
                                Verify Authenticity
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .d-print-none {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        max-width: 100% !important;
        padding: 0 !important;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .badge {
        border: 1px solid #000 !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
}
</style>

@endsection
