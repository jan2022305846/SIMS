@extends('layouts.app')

@section('title', 'Custom Report Builder')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line fa-sm text-gray-600"></i>
            Custom Report Builder
        </h1>
        <div class="d-none d-sm-inline-block">
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Report Configuration -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Configuration</h6>
        </div>
        <div class="card-body">
            <form id="customReportForm" method="POST" action="{{ route('reports.custom-report') }}">
                @csrf
                <div class="row">
                    <!-- Report Type -->
                    <div class="col-md-6 mb-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select name="report_type" id="report_type" class="form-control" required>
                            <option value="">Select Report Type</option>
                            <option value="inventory">Inventory Report</option>
                            <option value="requests">Requests Report</option>
                            <option value="usage">Usage Analysis</option>
                            <option value="financial">Financial Summary</option>
                            <option value="user_activity">User Activity</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="{{ request('end_date', now()->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <!-- Dynamic Filters -->
                <div id="dynamicFilters"></div>

                <!-- Output Options -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="format" class="form-label">Output Format</label>
                        <select name="format" id="format" class="form-control">
                            <option value="html">View in Browser</option>
                            <option value="pdf">PDF Download</option>
                            <option value="csv">CSV Export</option>
                            <option value="excel">Excel Export</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="group_by" class="form-label">Group By</label>
                        <select name="group_by" id="group_by" class="form-control">
                            <option value="">No Grouping</option>
                            <option value="category">Category</option>
                            <option value="user">User</option>
                            <option value="office">Office</option>
                            <option value="date">Date</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sort_by" class="form-label">Sort By</label>
                        <select name="sort_by" id="sort_by" class="form-control">
                            <option value="date">Date</option>
                            <option value="name">Name</option>
                            <option value="quantity">Quantity</option>
                            <option value="value">Value</option>
                        </select>
                    </div>
                </div>

                <!-- Include Options -->
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Include in Report</label>
                        <div class="form-check-inline">
                            <input type="checkbox" name="include_charts" id="include_charts" class="form-check-input" checked>
                            <label class="form-check-label" for="include_charts">Charts & Graphs</label>
                        </div>
                        <div class="form-check-inline">
                            <input type="checkbox" name="include_summary" id="include_summary" class="form-check-input" checked>
                            <label class="form-check-label" for="include_summary">Summary Statistics</label>
                        </div>
                        <div class="form-check-inline">
                            <input type="checkbox" name="include_details" id="include_details" class="form-check-input" checked>
                            <label class="form-check-label" for="include_details">Detailed Data</label>
                        </div>
                    </div>
                </div>

                <!-- Generate Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-bar fa-sm mr-2"></i>
                        Generate Report
                    </button>
                    <button type="button" id="previewBtn" class="btn btn-outline-primary">
                        <i class="fas fa-eye fa-sm mr-2"></i>
                        Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Preview/Results -->
    <div id="reportResults" class="card shadow mb-4" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Preview</h6>
        </div>
        <div class="card-body">
            <div id="reportContent">
                <!-- Report content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Quick Templates -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Quick Report Templates</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-left-primary h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary">Monthly Inventory</h6>
                            <p class="text-sm text-gray-600 mb-3">Complete inventory status for the current month</p>
                            <button class="btn btn-sm btn-primary" onclick="loadTemplate('monthly_inventory')">
                                Use Template
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-left-success h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success">Request Analytics</h6>
                            <p class="text-sm text-gray-600 mb-3">Analyze request patterns and trends</p>
                            <button class="btn btn-sm btn-success" onclick="loadTemplate('request_analytics')">
                                Use Template
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-left-info h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-info">Financial Summary</h6>
                            <p class="text-sm text-gray-600 mb-3">Financial overview and cost analysis</p>
                            <button class="btn btn-sm btn-info" onclick="loadTemplate('financial_summary')">
                                Use Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle report type change to show dynamic filters
    $('#report_type').change(function() {
        const reportType = $(this).val();
        loadDynamicFilters(reportType);
    });

    // Handle form submission
    $('#customReportForm').submit(function(e) {
        e.preventDefault();
        generateReport();
    });

    // Preview button
    $('#previewBtn').click(function() {
        generateReport(true);
    });
});

function loadDynamicFilters(reportType) {
    const filtersContainer = $('#dynamicFilters');
    filtersContainer.empty();

    let filtersHtml = '<div class="row">';

    switch (reportType) {
        case 'inventory':
            filtersHtml += `
                <div class="col-md-6 mb-3">
                    <label for="category_filter" class="form-label">Category</label>
                    <select name="category_filter" id="category_filter" class="form-control">
                        <option value="">All Categories</option>
                        <!-- Categories will be loaded via AJAX -->
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock_status_filter" class="form-label">Stock Status</label>
                    <select name="stock_status_filter" id="stock_status_filter" class="form-control">
                        <option value="">All Items</option>
                        <option value="low_stock">Low Stock Only</option>
                        <option value="out_of_stock">Out of Stock Only</option>
                        <option value="adequate">Adequate Stock Only</option>
                    </select>
                </div>
            `;
            // Load categories via AJAX
            loadCategories();
            break;

        case 'requests':
            filtersHtml += `
                <div class="col-md-4 mb-3">
                    <label for="status_filter" class="form-label">Request Status</label>
                    <select name="status_filter" id="status_filter" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="requester_filter" class="form-label">Requester</label>
                    <select name="requester_filter" id="requester_filter" class="form-control">
                        <option value="">All Users</option>
                        <!-- Users will be loaded via AJAX -->
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="office_filter" class="form-label">Office</label>
                    <select name="office_filter" id="office_filter" class="form-control">
                        <option value="">All Offices</option>
                        <!-- Offices will be loaded via AJAX -->
                    </select>
                </div>
            `;
            // Load users and offices via AJAX
            loadUsers();
            loadOffices();
            break;

        case 'usage':
            filtersHtml += `
                <div class="col-md-6 mb-3">
                    <label for="usage_metric" class="form-label">Usage Metric</label>
                    <select name="usage_metric" id="usage_metric" class="form-control">
                        <option value="frequency">Request Frequency</option>
                        <option value="quantity">Total Quantity</option>
                        <option value="value">Total Value</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="min_requests" class="form-label">Minimum Requests</label>
                    <input type="number" name="min_requests" id="min_requests" class="form-control" 
                           placeholder="Filter by minimum request count" min="0">
                </div>
            `;
            break;

        case 'financial':
            filtersHtml += `
                <div class="col-md-6 mb-3">
                    <label for="cost_center" class="form-label">Cost Center</label>
                    <select name="cost_center" id="cost_center" class="form-control">
                        <option value="">All Cost Centers</option>
                        <option value="operations">Operations</option>
                        <option value="administration">Administration</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="currency_format" class="form-label">Currency Format</label>
                    <select name="currency_format" id="currency_format" class="form-control">
                        <option value="php">Philippine Peso (₱)</option>
                        <option value="usd">US Dollar ($)</option>
                    </select>
                </div>
            `;
            break;

        case 'user_activity':
            filtersHtml += `
                <div class="col-md-6 mb-3">
                    <label for="activity_type" class="form-label">Activity Type</label>
                    <select name="activity_type" id="activity_type" class="form-control">
                        <option value="">All Activities</option>
                        <option value="login">Login Events</option>
                        <option value="request">Request Activities</option>
                        <option value="approval">Approval Activities</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_role" class="form-label">User Role</label>
                    <select name="user_role" id="user_role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="office_head">Office Head</option>
                        <option value="faculty">Faculty</option>
                    </select>
                </div>
            `;
            break;
    }

    filtersHtml += '</div>';
    filtersContainer.html(filtersHtml);
}

function generateReport(preview = false) {
    const formData = new FormData($('#customReportForm')[0]);
    
    if (preview) {
        formData.append('preview', '1');
    }

    // Show loading
    if (preview) {
        $('#reportResults').show();
        $('#reportContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Generating report...</p></div>');
    }

    $.ajax({
        url: $('#customReportForm').attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (preview) {
                $('#reportContent').html(response.html);
            } else {
                // Handle different format responses
                if (response.format === 'html') {
                    $('#reportResults').show();
                    $('#reportContent').html(response.html);
                } else {
                    // For downloads, create a temporary link
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.download = response.filename;
                    link.click();
                }
            }
        },
        error: function(xhr) {
            alert('Error generating report. Please try again.');
            console.error(xhr.responseText);
        }
    });
}

function loadTemplate(templateType) {
    // Reset form
    $('#customReportForm')[0].reset();
    
    // Load template-specific settings
    switch (templateType) {
        case 'monthly_inventory':
            $('#report_type').val('inventory').trigger('change');
            $('#start_date').val('{{ now()->startOfMonth()->format("Y-m-d") }}');
            $('#end_date').val('{{ now()->endOfMonth()->format("Y-m-d") }}');
            $('#group_by').val('category');
            $('#sort_by').val('name');
            break;
            
        case 'request_analytics':
            $('#report_type').val('requests').trigger('change');
            $('#start_date').val('{{ now()->subDays(30)->format("Y-m-d") }}');
            $('#end_date').val('{{ now()->format("Y-m-d") }}');
            $('#group_by').val('date');
            $('#sort_by').val('date');
            break;
            
        case 'financial_summary':
            $('#report_type').val('financial').trigger('change');
            $('#start_date').val('{{ now()->startOfMonth()->format("Y-m-d") }}');
            $('#end_date').val('{{ now()->endOfMonth()->format("Y-m-d") }}');
            $('#group_by').val('category');
            $('#sort_by').val('value');
            break;
    }
}

// Helper functions for loading data via AJAX
function loadCategories() {
    $.get('/api/api-categories', function(data) {
        const select = $('#category_filter');
        data.forEach(function(category) {
            select.append(`<option value="${category.id}">${category.name}</option>`);
        });
    }).fail(function() {
        console.warn('Could not load categories');
    });
}

function loadUsers() {
    $.get('/api/api-users', function(data) {
        const select = $('#requester_filter');
        data.forEach(function(user) {
            select.append(`<option value="${user.id}">${user.name}</option>`);
        });
    }).fail(function() {
        console.warn('Could not load users');
    });
}

function loadOffices() {
    $.get('/api/offices', function(data) {
        const select = $('#office_filter');
        data.forEach(function(office) {
            select.append(`<option value="${office.id}">${office.name}</option>`);
        });
    }).fail(function() {
        console.warn('Could not load offices');
    });
}
</script>
@endpush
@endsection