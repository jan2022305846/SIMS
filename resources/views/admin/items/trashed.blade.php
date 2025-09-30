@extends('layouts.app')

@section('content')
<div class="container-fluid min-vh-100 py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header Section -->
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-1 d-flex align-items-center">
                            <i class="fas fa-trash-alt me-3 text-danger fs-3"></i>
                            Deleted Items (Trash)
                        </h1>
                        <p class="text-muted mb-0">Manage soft-deleted items that can be restored</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('items.index') }}"
                           class="btn btn-outline-primary fw-semibold">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Active Items
                        </a>
                        <a href="{{ route('items.create') }}"
                           class="btn btn-success fw-semibold">
                            <i class="fas fa-plus me-2"></i>
                            Add New Item
                        </a>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('items.trashed') }}" class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="fas fa-search me-1"></i>Search Items
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="search"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search by name, brand, description, or barcode...">
                            </div>
                            <div class="col-md-4">
                                <label for="category" class="form-label fw-semibold">
                                    <i class="fas fa-folder me-1"></i>Filter by Category
                                </label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle text-info me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-semibold mb-2">About Deleted Items</h6>
                            <p class="mb-1">These items have been soft-deleted and can be restored to active status. All item data and relationships are preserved.</p>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Items are permanently deleted after 30 days or when manually force-deleted.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Items Table Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="fas fa-list me-2 text-muted"></i>
                                Trashed Items
                                @if($items->total() > 0)
                                    <span class="badge bg-danger ms-2">{{ $items->total() }}</span>
                                @endif
                            </h5>
                            @if(request()->hasAny(['search', 'category']))
                                <a href="{{ route('items.trashed') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($items->count() > 0)
                            <!-- Bulk Actions Bar -->
                            <div class="bg-light border-bottom p-3 d-none" id="bulk-actions-bar">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="me-3 fw-semibold text-muted">
                                            <span id="selected-count">0</span> item(s) selected
                                        </span>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                    class="btn btn-success btn-sm fw-semibold"
                                                    onclick="bulkRestore()"
                                                    id="bulk-restore-btn"
                                                    disabled>
                                                <i class="fas fa-undo me-1"></i>
                                                Restore Selected
                                            </button>
                                            <button type="button"
                                                    class="btn btn-danger btn-sm fw-semibold"
                                                    onclick="bulkDelete()"
                                                    id="bulk-delete-btn"
                                                    disabled>
                                                <i class="fas fa-trash-alt me-1"></i>
                                                Delete Selected
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            onclick="clearSelection()">
                                        <i class="fas fa-times me-1"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="border-0 fw-semibold ps-4" style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="select-all"
                                                           onchange="toggleSelectAll()">
                                                    <label class="form-check-label visually-hidden" for="select-all">
                                                        Select all items
                                                    </label>
                                                </div>
                                            </th>
                                            <th scope="col" class="border-0 fw-semibold">
                                                <i class="fas fa-tag me-2 text-muted"></i>Item Details
                                            </th>
                                            <th scope="col" class="border-0 fw-semibold">
                                                <i class="fas fa-folder me-2 text-muted"></i>Category
                                            </th>
                                            <th scope="col" class="border-0 fw-semibold">
                                                <i class="fas fa-calendar-times me-2 text-muted"></i>Deleted
                                            </th>
                                            <th scope="col" class="border-0 fw-semibold text-center">
                                                <i class="fas fa-cogs me-2 text-muted"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr class="table-secondary bg-opacity-25">
                                                <td class="ps-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input item-checkbox"
                                                               type="checkbox"
                                                               id="item-{{ $item->id }}"
                                                               value="{{ $item->id }}"
                                                               onchange="updateBulkActions()">
                                                        <label class="form-check-label visually-hidden" for="item-{{ $item->id }}">
                                                            Select {{ $item->name }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                                                            <i class="fas fa-trash text-danger fs-5"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 fw-semibold text-decoration-line-through text-muted">
                                                                {{ $item->name }}
                                                            </h6>
                                                            <div class="text-muted small mb-1">
                                                                <i class="fas fa-building me-1"></i>
                                                                {{ $item->brand ?? 'No brand specified' }}
                                                            </div>
                                                            @if($item->description)
                                                                <div class="text-muted small mb-1">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    {{ Str::limit($item->description, 60) }}
                                                                </div>
                                                            @endif
                                                            <div class="text-muted small">
                                                                <i class="fas fa-barcode me-1"></i>
                                                                <strong>Barcode:</strong> {{ $item->barcode ?? 'N/A' }}
                                                                @if($item->current_stock > 0)
                                                                    <span class="ms-2 badge bg-warning text-dark">
                                                                        <i class="fas fa-boxes me-1"></i>{{ $item->current_stock }} in stock
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary bg-opacity-75 text-white px-3 py-2 fw-semibold">
                                                        {{ $item->category->name ?? 'Uncategorized' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-muted">
                                                        <div class="fw-semibold">
                                                            <i class="fas fa-calendar-times me-1"></i>
                                                            {{ $item->deleted_at ? $item->deleted_at->format('M j, Y') : 'Unknown' }}
                                                        </div>
                                                        <div class="small">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ $item->deleted_at ? $item->deleted_at->diffForHumans() : '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form method="POST"
                                                              action="{{ route('items.restore', $item->id) }}"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to restore \"{{ $item->name }}\"? The item will become active again.')">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-outline-success btn-sm fw-semibold"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Restore this item to active status">
                                                                <i class="fas fa-undo me-1"></i>
                                                                <span class="d-none d-lg-inline">Restore</span>
                                                            </button>
                                                        </form>
                                                        <form method="POST"
                                                              action="{{ route('items.force-delete', $item->id) }}"
                                                              class="d-inline ms-1"
                                                              onsubmit="return confirm('⚠️ WARNING: This action CANNOT be undone!\n\nAre you sure you want to PERMANENTLY DELETE \"{{ $item->name }}\"?\n\nThis will completely remove the item from the database and cannot be recovered.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-outline-danger btn-sm fw-semibold"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Permanently delete this item (cannot be undone)">
                                                                <i class="fas fa-trash-alt me-1"></i>
                                                                <span class="d-none d-lg-inline">Delete</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center p-4">
                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                    </div>
                                </div>
                                <h4 class="text-success mb-3 fw-bold">Trash is Empty</h4>
                                <p class="text-muted mb-4 fs-5">
                                    @if(request()->hasAny(['search', 'category']))
                                        No deleted items match your search criteria.
                                    @else
                                        No deleted items found. All items are currently active.
                                    @endif
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('items.index') }}" class="btn btn-primary btn-lg fw-semibold">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        View Active Items
                                    </a>
                                    @if(request()->hasAny(['search', 'category']))
                                        <a href="{{ route('items.trashed') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-times me-2"></i>
                                            Clear Filters
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Pagination -->
                    @if($items->hasPages())
                        <div class="card-footer bg-light">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <div class="text-muted small">
                                    <i class="fas fa-list me-1"></i>
                                    Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ $items->total() }} deleted items
                                    @if(request('search'))
                                        (filtered by: "{{ request('search') }}")
                                    @endif
                                </div>
                                <nav aria-label="Trashed items pagination">
                                    {{ $items->links('pagination::bootstrap-5') }}
                                </nav>
                            </div>
                        </div>
                    @elseif($items->total() > 0)
                        <div class="card-footer bg-light text-center text-muted small">
                            <i class="fas fa-check me-1"></i>
                            Showing all {{ $items->total() }} deleted items
                        </div>
                    @endif
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

    // Auto-submit search form on enter (optional enhancement)
    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.target.closest('form').submit();
        }
    });
});

// Bulk operations functions
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCount = document.getElementById('selected-count');
    const bulkRestoreBtn = document.getElementById('bulk-restore-btn');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const selectAllCheckbox = document.getElementById('select-all');

    const count = checkedBoxes.length;
    selectedCount.textContent = count;

    if (count > 0) {
        bulkActionsBar.classList.remove('d-none');
        bulkRestoreBtn.disabled = false;
        bulkDeleteBtn.disabled = false;
    } else {
        bulkActionsBar.classList.add('d-none');
        bulkRestoreBtn.disabled = true;
        bulkDeleteBtn.disabled = true;
    }

    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.item-checkbox').length;
    if (count === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (count === totalCheckboxes) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }
}

function clearSelection() {
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('select-all');

    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;

    updateBulkActions();
}

function bulkRestore() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one item to restore.');
        return;
    }

    const itemNames = Array.from(checkedBoxes).map(cb => {
        const row = cb.closest('tr');
        const nameElement = row.querySelector('h6');
        return nameElement ? nameElement.textContent.trim() : 'Unknown item';
    });

    const confirmMessage = `Are you sure you want to restore ${checkedBoxes.length} item(s)?\n\nSelected items:\n${itemNames.join('\n')}\n\nThese items will become active again.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("items.bulk-restore") }}';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    // Add selected item IDs
    checkedBoxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'item_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }

    const itemNames = Array.from(checkedBoxes).map(cb => {
        const row = cb.closest('tr');
        const nameElement = row.querySelector('h6');
        return nameElement ? nameElement.textContent.trim() : 'Unknown item';
    });

    const confirmMessage = `⚠️ CRITICAL WARNING: This action CANNOT be undone!\n\nAre you sure you want to PERMANENTLY DELETE ${checkedBoxes.length} item(s)?\n\nSelected items:\n${itemNames.join('\n')}\n\nThese items will be completely removed from the database and cannot be recovered under any circumstances.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    // Double confirmation for bulk delete
    if (!confirm('FINAL CONFIRMATION: This will permanently delete the selected items. Are you absolutely sure?')) {
        return;
    }

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("items.bulk-force-delete") }}';

    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    // Add selected item IDs
    checkedBoxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'item_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection