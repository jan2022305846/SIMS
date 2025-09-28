@extends('layouts.app')

@section('title', 'Low Stock Alert Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exclamation-triangle fa-sm text-warning"></i>
            Low Stock Alert Report
        </h1>
        <div class="d-none d-sm-inline-block">
            <button class="btn btn-sm btn-primary shadow-sm" onclick="window.print()">
                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left fa-sm"></i> Back to Reports
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.low-stock-alert') }}" class="row">
                <div class="col-md-4">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter fa-sm mr-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('reports.low-stock-alert') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo fa-sm mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockItems->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockItems->where('current_stock', '<=', 0)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Value at Risk
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($lowStockItems->sum(function($item) { return $item->current_stock * $item->unit_price; }), 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Low Stock Items</h6>
        </div>
        <div class="card-body">
            @if($lowStockItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Minimum Stock</th>
                                <th>Unit Price</th>
                                <th>Total Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockItems as $item)
                                <tr class="{{ $item->current_stock <= 0 ? 'table-danger' : 'table-warning' }}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $item->current_stock <= 0 ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $item->current_stock }}
                                        </span>
                                    </td>
                                    <td>{{ $item->minimum_stock }}</td>
                                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($item->current_stock * $item->unit_price, 2) }}</td>
                                    <td>
                                        @if($item->current_stock <= 0)
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @else
                                            <span class="badge badge-warning">Low Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @can('update', $item)
                                            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-success">All Items Are Well Stocked!</h4>
                    <p class="text-muted">No items are currently low on stock or out of stock.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "searching": true,
        "paging": true,
        "info": true,
        "responsive": true,
        "order": [[ 3, "asc" ]] // Sort by current stock ascending
    });
});
</script>
@endsection