@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="mark-selected-read">
                            <i class="fas fa-check me-1"></i>Mark Selected Read
                        </button>
                        <button class="btn btn-outline-danger btn-sm" id="delete-selected">
                            <i class="fas fa-trash me-1"></i>Delete Selected
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="mark-all-read-page">
                            <i class="fas fa-check-double me-1"></i>Mark All Read
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="status-filter" class="form-label">Status</label>
                            <select class="form-select form-select-sm" id="status-filter" name="status">
                                <option value="">All Notifications</option>
                                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread Only</option>
                                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read Only</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="type-filter" class="form-label">Type</label>
                            <select class="form-select form-select-sm" id="type-filter" name="type">
                                <option value="">All Types</option>
                                <option value="pending_request" {{ request('type') === 'pending_request' ? 'selected' : '' }}>Pending Request</option>
                                <option value="low_stock" {{ request('type') === 'low_stock' ? 'selected' : '' }}>Low Stock Alert</option>
                                <option value="approved" {{ request('type') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="fulfilled" {{ request('type') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                                <option value="claimed" {{ request('type') === 'claimed' ? 'selected' : '' }}>Claimed</option>
                                <option value="declined" {{ request('type') === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Search notifications...">
                        </div>
                    </div>

                    <!-- Select All Checkbox -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label" for="select-all">
                                Select All Notifications
                            </label>
                        </div>
                    </div>

                    <!-- Notifications Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <input class="form-check-input" type="checkbox" id="select-all-header">
                                    </th>
                                    <th width="50">Status</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr class="{{ $notification->read_at ? '' : 'table-light fw-semibold' }}">
                                        <td>
                                            <input class="form-check-input notification-checkbox" type="checkbox"
                                                   value="{{ $notification->id }}" data-id="{{ $notification->id }}">
                                        </td>
                                        <td>
                                            @if($notification->read_at)
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-check me-1"></i>Read
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-envelope me-1"></i>Unread
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $notification->color ?? 'secondary' }}">
                                                <i class="fas fa-{{ $notification->icon ?? 'bell' }} me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $notification->title }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="{{ $notification->message }}">
                                                {{ $notification->message }}
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $notification->created_at->format('M j, Y g:i A') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if(!$notification->read_at)
                                                    <button class="btn btn-outline-primary btn-sm mark-read-btn"
                                                            data-id="{{ $notification->id }}" title="Mark as Read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                @if($notification->url)
                                                    <a href="{{ $notification->url }}" class="btn btn-outline-info btn-sm" title="View Related">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <button class="btn btn-outline-danger btn-sm delete-notification-btn"
                                                        data-id="{{ $notification->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted mb-1">No notifications found</h5>
                                                <p class="text-muted mb-0">Try adjusting your filters or check back later.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
                            </div>
                            <nav aria-label="Notifications pagination">
                                {{ $notifications->withQueryString()->links() }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const selectAllHeaderCheckbox = document.getElementById('select-all-header');
    const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
    const markSelectedReadBtn = document.getElementById('mark-selected-read');
    const deleteSelectedBtn = document.getElementById('delete-selected');
    const markAllReadPageBtn = document.getElementById('mark-all-read-page');
    const statusFilter = document.getElementById('status-filter');
    const typeFilter = document.getElementById('type-filter');
    const searchInput = document.getElementById('search');

    // Select All functionality
    function updateSelectAllState() {
        const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
        const totalBoxes = notificationCheckboxes.length;

        selectAllCheckbox.checked = checkedBoxes.length === totalBoxes && totalBoxes > 0;
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalBoxes;
        selectAllHeaderCheckbox.checked = selectAllCheckbox.checked;
        selectAllHeaderCheckbox.indeterminate = selectAllCheckbox.indeterminate;
    }

    selectAllCheckbox.addEventListener('change', function() {
        notificationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectAllState();
    });

    selectAllHeaderCheckbox.addEventListener('change', function() {
        notificationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectAllState();
    });

    notificationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllState);
    });

    // Mark selected as read
    markSelectedReadBtn.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select notifications to mark as read.');
            return;
        }

        if (confirm(`Mark ${selectedIds.length} notification(s) as read?`)) {
            markNotificationsAsRead(selectedIds);
        }
    });

    // Delete selected
    deleteSelectedBtn.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.notification-checkbox:checked'))
            .map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select notifications to delete.');
            return;
        }

        if (confirm(`Delete ${selectedIds.length} notification(s)? This action cannot be undone.`)) {
            deleteNotifications(selectedIds);
        }
    });

    // Mark all as read (page)
    markAllReadPageBtn.addEventListener('click', function() {
        if (confirm('Mark all notifications on this page as read?')) {
            const allIds = Array.from(notificationCheckboxes).map(cb => cb.value);
            markNotificationsAsRead(allIds);
        }
    });

    // Individual mark as read buttons
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            markNotificationsAsRead([notificationId]);
        });
    });

    // Individual delete buttons
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.getAttribute('data-id');
            if (confirm('Delete this notification? This action cannot be undone.')) {
                deleteNotifications([notificationId]);
            }
        });
    });

    // Filter functionality
    function applyFilters() {
        const params = new URLSearchParams(window.location.search);

        if (statusFilter.value) {
            params.set('status', statusFilter.value);
        } else {
            params.delete('status');
        }

        if (typeFilter.value) {
            params.set('type', typeFilter.value);
        } else {
            params.delete('type');
        }

        if (searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        } else {
            params.delete('search');
        }

        // Reset to page 1 when filtering
        params.set('page', '1');

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    statusFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);

    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    // API functions
    function markNotificationsAsRead(notificationIds) {
        Promise.all(notificationIds.map(id =>
            fetch(`/notifications/${id}/read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
        ))
        .then(() => {
            location.reload(); // Refresh to update the UI
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
            alert('Error updating notifications. Please try again.');
        });
    }

    function deleteNotifications(notificationIds) {
        Promise.all(notificationIds.map(id =>
            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
        ))
        .then(() => {
            location.reload(); // Refresh to update the UI
        })
        .catch(error => {
            console.error('Error deleting notifications:', error);
            alert('Error deleting notifications. Please try again.');
        });
    }
});
</script>
@endpush