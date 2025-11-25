@extends('layouts.app')

@section('styles')
<style>
/* Modal improvements - ensure proper z-index stacking */
.modal-backdrop {
    z-index: 1040 !important;
}
.modal {
    z-index: 1055 !important; /* Higher than backdrop */
}

/* Ensure modal content is above backdrop */
.modal-content,
.modal-header,
.modal-body,
.modal-footer {
    z-index: 1060 !important; /* Even higher */
    position: relative !important;
}

/* Ensure modal dialog is properly positioned */
.modal-dialog {
    z-index: 1056 !important;
    position: relative !important;
}

/* Remove display none from modal - let Bootstrap handle it */
#disposeItemsModal {
    /* display: none; */ /* Remove this - Bootstrap handles visibility */
}
#disposeItemsModal.show {
    display: block;
}

/* Ensure modal is clickable and not blocked */
.modal-backdrop.show {
    opacity: 0.5 !important;
    pointer-events: none !important; /* Allow clicks to pass through to modal */
}

/* Make sure modal content receives pointer events */
.modal-content {
    pointer-events: auto !important;
}

/* Additional modal fixes */
.modal.show .modal-dialog {
    transform: none !important;
    z-index: 1056 !important;
}

.modal.show {
    z-index: 1055 !important;
    display: block !important;
}

/* Ensure buttons and inputs are clickable */
.modal button,
.modal input,
.modal label,
.modal a {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 1060 !important;
}

/* Force modal content to be fully interactive */
#disposeItemsModal.modal.show .modal-content {
    z-index: 1060 !important;
    pointer-events: auto !important;
    position: relative !important;
}

#disposeItemsModal.modal.show .modal-backdrop {
    z-index: 1050 !important;
}

/* Ensure form elements are clickable */
#disposeItemsModal input,
#disposeItemsModal button,
#disposeItemsModal label,
#disposeItemsModal textarea {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 1070 !important;
}

/* Loading states */
.btn:disabled {
    opacity: 0.65;
}

/* Toast positioning */
.toast-container {
    z-index: 1070; /* Above modals */
}

/* Ensure modal content is properly styled */
.modal-content {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    position: relative; /* Ensure proper positioning */
}

/* Disposal specific styles */
.disposal-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    background-color: #fff;
}

.disposal-item.selected {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.disposal-item .form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

.condition-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 fw-semibold text-dark mb-0">
                    <i class="fas fa-times-circle me-2 text-danger"></i>
                    Item Disposal
                </h2>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('items.index') }}" class="btn btn-secondary fw-bold">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Items
                    </a>
                    <button type="button" class="btn btn-danger fw-bold" id="disposeSelectedBtn" disabled>
                        <i class="fas fa-trash me-1"></i>
                        Dispose Selected Items
                    </button>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Item Disposal:</strong> This page shows non-consumable items that are marked as "Needs Repair".
                Select items to permanently dispose of them from the system. A DOCX disposal report will be generated and downloaded automatically.
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Search and Filter -->
                    <form id="searchForm" method="GET" action="{{ route('items.disposal') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control"
                                           name="search"
                                           placeholder="Search items..."
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                        @if(request()->hasAny(['search', 'category']))
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('items.disposal') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i>
                                            Clear Filters
                                        </a>
                                        @if($items->total() > 0)
                                            <span class="text-muted small">
                                                {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} items
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>

                    <!-- Disposal Form -->
                    <form id="disposalForm" method="POST" action="{{ route('items.process-disposal') }}">
                        @csrf

                        <!-- Items List -->
                        <div class="table-responsive position-relative">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" width="50">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-tag me-1"></i>Item Details
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-folder me-1"></i>Category
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-map-marker-alt me-1"></i>Location
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-wrench me-1"></i>Condition
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-calendar me-1"></i>Last Updated
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               class="form-check-input item-checkbox"
                                               name="item_ids[]"
                                               value="{{ $item->id }}"
                                               id="item_{{ $item->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="fas fa-tools text-danger"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item->name }}</div>
                                                <div class="text-muted small">{{ $item->brand ?? 'No brand specified' }}</div>
                                                @if($item->description)
                                                    <div class="text-muted small">{{ Str::limit($item->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $item->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $item->location ?? 'Not specified' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger condition-badge">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ $item->condition }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $item->updated_at->format('M j, Y g:i A') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="bg-light rounded-circle p-4 mb-3">
                                                <i class="fas fa-check-circle fa-3x text-success"></i>
                                            </div>
                                            <h5 class="text-muted mb-1">No Items Need Disposal</h5>
                                            <p class="text-muted mb-0">All non-consumable items are in good condition.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Loading overlay -->
                    <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-none" style="background: rgba(255,255,255,0.8); z-index: 1000;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 text-muted">Processing disposal...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($items->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} items
                        </div>
                        <nav aria-label="Items pagination">
                            {{ $items->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @else
                    @if($items->total() > 0)
                        <div class="text-center mt-4 text-muted">
                            Showing all {{ $items->total() }} items
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Disposal Modal -->
<div class="modal fade" id="disposeItemsModal" tabindex="-1" style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 1060 !important;">
    <div class="modal-dialog" style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; z-index: 1070 !important; margin: 0 !important; pointer-events: auto !important; width: 90% !important; max-width: 600px !important;">
        <div class="modal-content" style="border: none !important; border-radius: 20px !important; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important; pointer-events: auto !important; position: relative !important; z-index: 1080 !important; background-color: white !important;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>
                    Confirm Item Disposal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The selected items will be permanently deleted from the system.
                </div>

                <div id="selectedItemsList" class="mb-3">
                    <!-- Selected items will be populated here by JavaScript -->
                </div>

                <div class="mb-3">
                    <label for="disposal_reason" class="form-label fw-bold">
                        Disposal Reason <span class="text-danger">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="disposal_reason"
                        name="disposal_reason"
                        rows="3"
                        placeholder="Please provide a reason for disposing of these items..."
                        required
                    ></textarea>
                    <div class="form-text">This reason will be included in the disposal report.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="disposalForm" class="btn btn-danger" id="confirmDisposeBtn">
                    <i class="fas fa-trash me-1"></i>
                    Dispose Items & Generate Report
                </button>
            </div>
        </div>
    </div>
</div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const disposeSelectedBtn = document.getElementById('disposeSelectedBtn');
    const disposalForm = document.getElementById('disposalForm');
    const disposeItemsModal = document.getElementById('disposeItemsModal');
    const selectedItemsList = document.getElementById('selectedItemsList');
    const confirmDisposeBtn = document.getElementById('confirmDisposeBtn');

    let modalInstance = null;

    // Initialize modal
    if (disposeItemsModal) {
        modalInstance = new bootstrap.Modal(disposeItemsModal, {
            backdrop: 'static',
            keyboard: false
        });
    }

    // Handle select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateDisposeButton();
        });
    }

    // Handle individual checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateDisposeButton();

            // Update select all checkbox state
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === itemCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < itemCheckboxes.length;
        });
    });

    // Update dispose button state
    function updateDisposeButton() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        disposeSelectedBtn.disabled = checkedBoxes.length === 0;
    }

    // Handle dispose selected button
    if (disposeSelectedBtn) {
        disposeSelectedBtn.addEventListener('click', function() {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');

            if (selectedItems.length === 0) {
                alert('Please select at least one item to dispose.');
                return;
            }

            // Populate modal with selected items
            let itemsHtml = '<div class="selected-items-list">';
            itemsHtml += '<h6 class="mb-3">Items to be disposed:</h6>';
            itemsHtml += '<ul class="list-group">';

            selectedItems.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const itemName = row.querySelector('.fw-semibold').textContent;
                const itemBrand = row.querySelector('.text-muted.small').textContent;
                const categoryBadge = row.querySelector('.badge');

                itemsHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${itemName}</strong>
                            <br><small class="text-muted">${itemBrand}</small>
                        </div>
                        <span class="badge bg-primary">${categoryBadge ? categoryBadge.textContent.trim() : 'N/A'}</span>
                    </li>
                `;
            });

            itemsHtml += '</ul>';
            itemsHtml += `<div class="mt-3 text-muted small">Total: ${selectedItems.length} item(s)</div>`;
            itemsHtml += '</div>';

            selectedItemsList.innerHTML = itemsHtml;

            // Show modal
            if (modalInstance) {
                modalInstance.show();
            }
        });
    }

    // Handle form submission
    if (disposalForm) {
        disposalForm.addEventListener('submit', function(e) {
            // Show loading overlay
            document.getElementById('loading-overlay').classList.remove('d-none');

            // Disable form elements
            confirmDisposeBtn.disabled = true;
            confirmDisposeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

            // Close modal
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }

    // Auto-submit form on select change
    const categoryFilter = document.querySelector('select[name="category"]');
    const searchInput = document.querySelector('input[name="search"]');
    const form = document.getElementById('searchForm');

    // Add event listeners with null checks
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function(e) {
            e.stopPropagation();
            setTimeout(() => form.submit(), 50);
        });
    }

    // Auto-submit on search with debounce
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                form.submit();
            }, 1000); // 1 second delay
        });

        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                form.submit();
            }
        });
    }

    // Show loading on form submission
    if (form) {
        form.addEventListener('submit', function() {
            document.getElementById('loading-overlay').classList.remove('d-none');
        });
    }
});
</script>
@endsection