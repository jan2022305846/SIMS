@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-trash me-2 text-danger"></i>
                        Deleted Items (Trash)
                    </h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('items.index') }}"
                           class="btn btn-primary fw-bold">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Items
                        </a>
                        <a href="{{ route('items.create') }}"
                           class="btn btn-warning fw-bold">
                            <i class="fas fa-plus me-1"></i>
                            Add New Item
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Info Alert -->
                        <div class="alert alert-info border-0 bg-light">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <div>
                                    <strong>Deleted Items:</strong> These items have been soft-deleted and can be restored.
                                    Items are permanently deleted after 30 days or when manually force-deleted.
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive position-relative">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">
                                            <i class="fas fa-tag me-1"></i>Item Details
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-folder me-1"></i>Category
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-calendar-times me-1"></i>Deleted At
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-cogs me-1"></i>Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr class="table-secondary">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-decoration-line-through">{{ $item->name }}</div>
                                                        <div class="text-muted small">{{ $item->brand ?? 'No brand specified' }}</div>
                                                        @if($item->description)
                                                            <div class="text-muted small">{{ Str::limit($item->description, 50) }}</div>
                                                        @endif
                                                        <div class="text-muted small">
                                                            <strong>Barcode:</strong> {{ $item->barcode ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                                    {{ $item->category->name ?? 'Uncategorized' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $item->deleted_at ? $item->deleted_at->format('M d, Y H:i') : 'Unknown' }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $item->deleted_at ? $item->deleted_at->diffForHumans() : '' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <form method="POST" action="{{ route('items.restore', $item->id) }}"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to restore this item?')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn btn-outline-success"
                                                                data-bs-toggle="tooltip"
                                                                title="Restore Item">
                                                            <i class="fas fa-undo"></i>
                                                            <span class="d-none d-sm-inline ms-1">Restore</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                                    </div>
                                                    <h5 class="text-success mb-1">Trash is Empty</h5>
                                                    <p class="text-muted mb-0">No deleted items found. All items are currently active.</p>
                                                    <a href="{{ route('items.index') }}" class="btn btn-primary mt-3">
                                                        <i class="fas fa-arrow-left me-1"></i>
                                                        Back to Items
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($items->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} deleted items
                                </div>
                                <nav aria-label="Trashed items pagination">
                                    {{ $items->links('pagination::bootstrap-4') }}
                                </nav>
                            </div>
                        @else
                            @if($items->total() > 0)
                                <div class="text-center mt-4 text-muted">
                                    Showing all {{ $items->total() }} deleted items
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection