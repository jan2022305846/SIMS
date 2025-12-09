@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <div>
                        <h1 class="h2 mb-1 text-dark fw-bold">{{ $user->name }}</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user me-1"></i>
                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }} Account
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        @can('admin')
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                Edit User
                            </a>
                        @endcan
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Users
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- User Information -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-circle me-2"></i>
                                    User Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Full Name</p>
                                        <p class="h6 mb-0">{{ $user->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Username</p>
                                        <p class="h6 mb-0">{{ $user->username }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Email Address</p>
                                        <p class="h6 mb-0">{{ $user->email }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Type</p>
                                        <span class="badge {{ $user->isAdmin() ? 'bg-danger' : 'bg-primary' }} fs-6">
                                            {{ $user->isAdmin() ? 'Admin' : 'Faculty' }}
                                        </span>
                                    </div>
                                    @if($user->office)
                                    <div class="col-md-6">
                                        <p class="text-muted small mb-1">Office</p>
                                        <p class="h6 mb-0">{{ $user->office->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Account Statistics -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Account Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 text-center">
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-primary mb-1">{{ $user->requests->count() }}</div>
                                        <p class="text-muted small mb-0">Total Requests</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-success mb-1">{{ $user->requests->whereIn('status', ['claimed', 'returned'])->count() }}</div>
                                        <p class="text-muted small mb-0">Completed Requests</p>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h3 fw-bold text-warning mb-1">{{ $user->requests->where('status', 'pending')->count() }}</div>
                                        <p class="text-muted small mb-0">Pending Requests</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        @if($requests->count() > 0)
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history me-2"></i>
                                        Released Items History
                                    </h5>

                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Period Selection Buttons -->
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary btn-sm user-period-btn active" data-period="monthly">
                                                <i class="fas fa-calendar-alt me-1"></i>Monthly
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm user-period-btn" data-period="annual">
                                                <i class="fas fa-calendar me-1"></i>Annual
                                            </button>
                                        </div>

                                        <!-- Period Selection Dropdown -->
                                        <select class="form-select form-select-sm" id="userPeriodSelect" style="min-width: 150px;">
                                            <!-- Options will be populated by JavaScript -->
                                        </select>

                                        <!-- Export Button -->
                                        <a href="#" id="exportUserReleasedBtn" class="btn btn-success btn-sm">
                                            <i class="fas fa-download me-1"></i>
                                            Export DOCX
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="releasedItemsTable">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody id="releasedItemsTableBody">
                                            @foreach($requests->whereIn('status', ['claimed', 'returned']) as $request)
                                            @foreach($request->requestItems as $requestItem)
                                            @php
                                                $itemName = 'N/A';
                                                $unit = 'pcs';
                                                if ($requestItem->item_type && $requestItem->item_id) {
                                                    if ($requestItem->item_type === 'consumable') {
                                                        $item = \App\Models\Consumable::find($requestItem->item_id);
                                                        if ($item) {
                                                            $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                                                            $unit = $item->unit ?: 'pcs';
                                                        }
                                                    } elseif ($requestItem->item_type === 'non_consumable') {
                                                        $item = \App\Models\NonConsumable::find($requestItem->item_id);
                                                        if ($item) {
                                                            $itemName = $item->name ?: 'Item ID: ' . $requestItem->item_id;
                                                            $unit = $item->unit ?: 'pcs';
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $request->updated_at->format('M j, Y') }}</td>
                                                <td>{{ $itemName }}</td>
                                                <td>{{ $requestItem->quantity }} {{ $unit }}</td>
                                            </tr>
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Empty State -->
                                <div id="releasedItemsEmptyState" class="text-center py-5" style="display: none;">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Released Items</h5>
                                    <p class="text-muted">No items have been released for the selected period.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Account Details -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Account Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Joined:</span>
                                    <span class="fw-medium">{{ $user->created_at->format('M j, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Last Updated:</span>
                                    <span class="fw-medium">{{ $user->updated_at->format('M j, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        @can('admin')
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-1"></i>
                                        Edit User
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i>
                                                Delete User
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="btn btn-secondary" title="You cannot delete your own account">
                                            <i class="fas fa-trash me-1"></i>
                                            Delete User
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-period-btn.active {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: white !important;
}
</style>

<script>
// Global variables for user released items filtering
let currentUserPeriod = 'monthly';
let currentUserSelection = null;
const userId = {{ $user->id }};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeUserPeriodDropdown();

    // Period button click handlers
    document.querySelectorAll('.user-period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            switchUserPeriod(period);
        });
    });

    // Period select change handler
    document.getElementById('userPeriodSelect').addEventListener('change', function() {
        currentUserSelection = this.value;
        loadUserReleasedItems();
    });

    // Export button handler
    document.getElementById('exportUserReleasedBtn').addEventListener('click', function(e) {
        e.preventDefault();
        exportUserReleasedItems();
    });
});

// Switch period type
function switchUserPeriod(period) {
    currentUserPeriod = period;

    // Update button states
    document.querySelectorAll('.user-period-btn').forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    document.querySelector(`[data-period="${period}"]`).classList.add('active', 'btn-primary');
    document.querySelector(`[data-period="${period}"]`).classList.remove('btn-outline-primary');

    // Update dropdown options
    initializeUserPeriodDropdown();

    // Load new data
    loadUserReleasedItems();
}

// Initialize period dropdown based on current period
function initializeUserPeriodDropdown() {
    const select = document.getElementById('userPeriodSelect');
    select.innerHTML = '';

    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();

    if (currentUserPeriod === 'monthly') {
        // Show all 12 months for current year
        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        months.forEach((monthName, index) => {
            const monthValue = `${currentYear}-${String(index + 1).padStart(2, '0')}`;
            const option = new Option(`${monthName} ${currentYear}`, monthValue);
            // Select current month by default
            if (index === currentMonth) {
                option.selected = true;
            }
            select.appendChild(option);
        });
    } else if (currentUserPeriod === 'annual') {
        // Show years from current-2 to current (3 years total)
        const startYear = currentYear - 2;
        const endYear = currentYear;

        for (let year = startYear; year <= endYear; year++) {
            const option = new Option(year.toString(), year.toString());
            // Select current year by default
            if (year === currentYear) {
                option.selected = true;
            }
            select.appendChild(option);
        }
    }

    // Set currentUserSelection to the selected value
    currentUserSelection = select.value;
}

// Load user released items based on period
async function loadUserReleasedItems() {
    try {
        const url = `/admin/api/users/${userId}/released-items?period=${currentUserPeriod}&selection=${currentUserSelection}`;

        const response = await fetch(url, {
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // Update table
        updateReleasedItemsTable(data.items);

        // Update badge count - REMOVED: badge no longer exists

    } catch (error) {
        console.error('Error loading released items:', error);
        // Show empty state on error
        document.getElementById('releasedItemsTable').style.display = 'none';
        document.getElementById('releasedItemsEmptyState').style.display = 'block';
    }
}

// Update released items table
function updateReleasedItemsTable(items) {
    const tbody = document.getElementById('releasedItemsTableBody');
    const table = document.getElementById('releasedItemsTable');
    const emptyState = document.getElementById('releasedItemsEmptyState');

    if (items.length === 0) {
        table.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    table.style.display = 'table';
    emptyState.style.display = 'none';

    tbody.innerHTML = items.map(item => `
        <tr>
            <td>${item.date}</td>
            <td>${item.item_name}</td>
            <td>${item.quantity}</td>
        </tr>
    `).join('');
}

// Export user released items
function exportUserReleasedItems() {
    const params = new URLSearchParams({
        period: currentUserPeriod,
        selection: currentUserSelection
    });

    const url = `/admin/users/${userId}/export-released-items?${params.toString()}`;
    window.open(url, '_blank');
}
</script>
@endsection