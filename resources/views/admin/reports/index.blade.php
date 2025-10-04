@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h3 fw-semibold text-dark mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Inventory Reports Dashboard
                    </h2>
                    <div class="d-flex gap-2 align-items-center">
                        <!-- Period Selector -->
                        <div class="btn-group" role="group" id="periodSelector">
                            <button type="button" class="btn btn-primary period-btn active" data-period="monthly">
                                <i class="fas fa-calendar-alt me-1"></i>Monthly
                            </button>
                            <button type="button" class="btn btn-outline-primary period-btn" data-period="quarterly">
                                <i class="fas fa-calendar-plus me-1"></i>Quarterly
                            </button>
                            <button type="button" class="btn btn-outline-primary period-btn" data-period="annual">
                                <i class="fas fa-calendar me-1"></i>Annual
                            </button>
                        </div>
                        
                        <!-- Period Selection Dropdown -->
                        <div class="dropdown" id="periodDropdown">
                            <select class="form-select" id="periodSelect" style="min-width: 180px;">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <!-- Download Button -->
                        <button class="btn btn-success" id="downloadBtn">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </button>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading report data...</p>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4" id="summaryCards">
                    <div class="col-md-3">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0" id="totalItems">0</h4>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-up fa-2x text-success mb-2"></i>
                                <h4 class="mb-0" id="totalAdded">0</h4>
                                <small class="text-muted">Items Added/Restocked</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0" id="totalReleased">0</h4>
                                <small class="text-muted">Items Released/Claimed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-warehouse fa-2x text-info mb-2"></i>
                                <h4 class="mb-0" id="currentStock">0</h4>
                                <small class="text-muted">Current Stock</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            <span id="chartTitle">Monthly Inventory Movement</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="inventoryChart" style="height: 400px;"></canvas>
                    </div>
                </div>

                <!-- Detailed Report Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            <span id="tableTitle">Monthly Inventory Report</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="searchTable" placeholder="Search items..." style="width: 200px;">
                            <button class="btn btn-outline-secondary btn-sm" id="exportTableBtn">
                                <i class="fas fa-file-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="inventoryTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th class="text-center">Items Released/Claimed</th>
                                        <th class="text-center">Total Quantity (Start + Added)</th>
                                        <th class="text-center">Remaining Stock</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Empty State -->
                        <div id="emptyState" class="text-center py-5" style="display: none;">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Data Available</h5>
                            <p class="text-muted">No inventory data found for the selected period.</p>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Table pagination" class="mt-3">
                            <ul class="pagination justify-content-center" id="tablePagination">
                                <!-- Pagination will be populated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- QR Scan Analytics Section -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-qrcode me-2 text-info"></i>
                            QR Scan Analytics
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- QR Period Selection -->
                            <select class="form-select form-select-sm" id="qrPeriodSelect" style="min-width: 150px;">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                            <input type="text" class="form-control form-control-sm" id="searchQRTable" placeholder="Search scans..." style="width: 180px;">
                            <button class="btn btn-outline-success btn-sm" id="downloadQRBtn">
                                <i class="fas fa-download me-1"></i>Download QR Logs PDF
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="exportQRTableBtn">
                                <i class="fas fa-file-excel me-1"></i>Export Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- QR Summary Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card border-info h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-qrcode fa-2x text-info mb-2"></i>
                                        <h6 class="mb-0" id="totalScans">0</h6>
                                        <small class="text-muted">Total Scans</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-boxes fa-2x text-success mb-2"></i>
                                        <h6 class="mb-0" id="uniqueItems">0</h6>
                                        <small class="text-muted">Unique Items Scanned</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users fa-2x text-warning mb-2"></i>
                                        <h6 class="mb-0" id="activeUsers">0</h6>
                                        <small class="text-muted">Active Scanners</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                                        <h6 class="mb-0" id="todayScans">0</h6>
                                        <small class="text-muted">Today's Scans</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Scan Logs Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="qrScanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Item Scanned</th>
                                        <th>User</th>
                                        <th>Scanner Type</th>
                                        <th>Location</th>
                                        <th>IP Address</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="qrTableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- QR Empty State -->
                        <div id="qrEmptyState" class="text-center py-5" style="display: none;">
                            <i class="fas fa-qrcode fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No QR Scan Data</h5>
                            <p class="text-muted">No QR scan logs found for the selected period.</p>
                        </div>
                        
                        <!-- QR Pagination -->
                        <nav aria-label="QR Table pagination" class="mt-3">
                            <ul class="pagination justify-content-center" id="qrTablePagination">
                                <!-- Pagination will be populated by JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.period-btn.active {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: white !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge-stock {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

#inventoryChart {
    max-height: 400px;
}
</style>

<script>
// Global variables
let currentPeriod = 'monthly';
let currentSelection = null;
let inventoryChart = null;
let currentData = [];
let currentPage = 1;
const itemsPerPage = 10;

// QR Scan Analytics variables
let currentQRPeriod = 'monthly';
let currentQRSelection = null;
let currentQRData = [];
let currentQRPage = 1;
const qrItemsPerPage = 15;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializePeriodDropdown();
    initializeQRPeriodDropdown();
    loadReportData();
    loadQRData();
});

// Event Listeners
function initializeEventListeners() {
    // Period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.getAttribute('data-period');
            switchPeriod(period);
        });
    });
    
    // Period dropdown
    document.getElementById('periodSelect').addEventListener('change', function() {
        currentSelection = this.value;
        loadReportData();
    });
    
    // Download button
    document.getElementById('downloadBtn').addEventListener('click', downloadReport);
    
    // Export table button
    document.getElementById('exportTableBtn').addEventListener('click', exportToExcel);
    
    // Search functionality
    document.getElementById('searchTable').addEventListener('input', function() {
        filterTable(this.value);
    });
    
    // QR Scan Analytics Event Listeners
    document.getElementById('qrPeriodSelect').addEventListener('change', function() {
        currentQRSelection = this.value;
        loadQRData();
    });
    
    document.getElementById('downloadQRBtn').addEventListener('click', downloadQRReport);
    document.getElementById('exportQRTableBtn').addEventListener('click', exportQRToExcel);
    
    document.getElementById('searchQRTable').addEventListener('input', function() {
        filterQRTable(this.value);
    });
}

// Switch period type
function switchPeriod(period) {
    currentPeriod = period;
    
    // Update button states
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    document.querySelector(`[data-period="${period}"]`).classList.add('active', 'btn-primary');
    document.querySelector(`[data-period="${period}"]`).classList.remove('btn-outline-primary');
    
    // Update dropdown options
    initializePeriodDropdown();
    
    // Update titles
    updateTitles();
    
    // Load new data
    loadReportData();
}

// Initialize period dropdown based on current period
function initializePeriodDropdown() {
    const select = document.getElementById('periodSelect');
    select.innerHTML = '';
    
    const currentDate = new Date();
    
    if (currentPeriod === 'monthly') {
        // Generate last 12 months
        for (let i = 0; i < 12; i++) {
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const value = date.toISOString().substring(0, 7); // YYYY-MM
            const text = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const option = new Option(text, value);
            if (i === 0) option.selected = true;
            select.appendChild(option);
        }
    } else if (currentPeriod === 'quarterly') {
        // Generate quarters for current and previous year
        const currentYear = currentDate.getFullYear();
        const quarters = [
            { value: `${currentYear}-Q4`, text: `Q4 ${currentYear} (Oct-Dec)` },
            { value: `${currentYear}-Q3`, text: `Q3 ${currentYear} (Jul-Sep)` },
            { value: `${currentYear}-Q2`, text: `Q2 ${currentYear} (Apr-Jun)` },
            { value: `${currentYear}-Q1`, text: `Q1 ${currentYear} (Jan-Mar)` },
            { value: `${currentYear-1}-Q4`, text: `Q4 ${currentYear-1} (Oct-Dec)` },
            { value: `${currentYear-1}-Q3`, text: `Q3 ${currentYear-1} (Jul-Sep)` },
            { value: `${currentYear-1}-Q2`, text: `Q2 ${currentYear-1} (Apr-Jun)` },
            { value: `${currentYear-1}-Q1`, text: `Q1 ${currentYear-1} (Jan-Mar)` }
        ];
        
        quarters.forEach((quarter, index) => {
            const option = new Option(quarter.text, quarter.value);
            if (index === 0) option.selected = true;
            select.appendChild(option);
        });
    } else if (currentPeriod === 'annual') {
        // Generate last 5 years
        for (let i = 0; i < 5; i++) {
            const year = currentDate.getFullYear() - i;
            const option = new Option(year.toString(), year.toString());
            if (i === 0) option.selected = true;
            select.appendChild(option);
        }
    }
    
    currentSelection = select.value;
}

// Update titles based on current period
function updateTitles() {
    const chartTitle = document.getElementById('chartTitle');
    const tableTitle = document.getElementById('tableTitle');
    
    const titles = {
        monthly: 'Monthly Inventory Movement',
        quarterly: 'Quarterly Inventory Movement',
        annual: 'Annual Inventory Movement'
    };
    
    chartTitle.textContent = titles[currentPeriod];
    tableTitle.textContent = titles[currentPeriod].replace('Movement', 'Report');
}

// Load report data
async function loadReportData() {
    showLoading(true);
    
    try {
        // Simulate API call - replace with actual endpoint
        const response = await fetch(`/api/reports/inventory-data?period=${currentPeriod}&selection=${currentSelection}`);
        const data = await response.json();
        
        // Update summary cards
        updateSummaryCards(data.summary);
        
        // Update chart
        updateChart(data.chartData);
        
        // Update table
        currentData = data.tableData || [];
        updateTable();
        
    } catch (error) {
        console.error('Error loading report data:', error);
        // Use mock data for demo
        loadMockData();
    } finally {
        showLoading(false);
    }
}

// Load mock data for demonstration
function loadMockData() {
    const mockSummary = {
        totalItems: 15,
        totalAdded: 250,
        totalReleased: 180,
        currentStock: 420
    };
    
    const mockChartData = {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [
            {
                label: 'Items Added',
                data: [60, 80, 45, 65],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            },
            {
                label: 'Items Released',
                data: [40, 55, 35, 50],
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2
            }
        ]
    };
    
    const mockTableData = [
        { name: 'Ballpen', released: 20, totalQuantity: 50, remaining: 30, category: 'Office Supplies' },
        { name: 'Notebook', released: 15, totalQuantity: 40, remaining: 25, category: 'Office Supplies' },
        { name: 'Printer Paper', released: 5, totalQuantity: 30, remaining: 25, category: 'Office Supplies' },
        { name: 'Stapler', released: 3, totalQuantity: 15, remaining: 12, category: 'Office Equipment' },
        { name: 'Whiteboard Marker', released: 12, totalQuantity: 25, remaining: 13, category: 'Office Supplies' }
    ];
    
    updateSummaryCards(mockSummary);
    updateChart(mockChartData);
    currentData = mockTableData;
    updateTable();
}

// Update summary cards
function updateSummaryCards(summary) {
    document.getElementById('totalItems').textContent = summary.totalItems || 0;
    document.getElementById('totalAdded').textContent = summary.totalAdded || 0;
    document.getElementById('totalReleased').textContent = summary.totalReleased || 0;
    document.getElementById('currentStock').textContent = summary.currentStock || 0;
}

// Update chart
function updateChart(chartData) {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    
    if (inventoryChart) {
        inventoryChart.destroy();
    }
    
    inventoryChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            animation: {
                duration: 750
            }
        }
    });
}

// Update table
function updateTable(filteredData = null) {
    const data = filteredData || currentData;
    const tbody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (data.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);
    
    tbody.innerHTML = paginatedData.map(item => `
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-box text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">${item.name}</div>
                        <small class="text-muted">${item.category || 'General'}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-danger badge-stock">${item.released}</span>
            </td>
            <td class="text-center">
                <span class="badge bg-success badge-stock">${item.totalQuantity}</span>
            </td>
            <td class="text-center">
                <span class="badge ${item.remaining < 10 ? 'bg-warning' : 'bg-info'} badge-stock">${item.remaining}</span>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary" onclick="viewItemDetails('${item.name}')">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        </tr>
    `).join('');
    
    updatePagination(data.length);
}

// Update pagination
function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const pagination = document.getElementById('tablePagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <button class="page-link" onclick="changePage(${i})">${i}</button>
                </li>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        </li>
    `;
    
    pagination.innerHTML = paginationHTML;
}

// Change page
function changePage(page) {
    currentPage = page;
    updateTable();
}

// Filter table
function filterTable(searchTerm) {
    const filteredData = currentData.filter(item => 
        item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (item.category && item.category.toLowerCase().includes(searchTerm.toLowerCase()))
    );
    currentPage = 1;
    updateTable(filteredData);
}

// Show/hide loading
function showLoading(show) {
    document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
}

// Download PDF report
function downloadReport() {
    const params = new URLSearchParams({
        period: currentPeriod,
        selection: currentSelection,
        format: 'pdf'
    });
    
    window.open(`/reports/download?${params.toString()}`, '_blank');
}

// Export to Excel
function exportToExcel() {
    const params = new URLSearchParams({
        period: currentPeriod,
        selection: currentSelection,
        format: 'excel'
    });
    
    window.open(`/reports/export?${params.toString()}`, '_blank');
}

// View item details (placeholder function)
function viewItemDetails(itemName) {
    alert(`Viewing details for: ${itemName}\n\nThis would open a modal or navigate to item details page.`);
}

// ===== QR SCAN ANALYTICS FUNCTIONS =====

// Initialize QR period dropdown
function initializeQRPeriodDropdown() {
    const select = document.getElementById('qrPeriodSelect');
    select.innerHTML = '';
    
    const currentDate = new Date();
    
    // Generate last 12 months for QR analytics
    for (let i = 0; i < 12; i++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        const value = date.toISOString().substring(0, 7); // YYYY-MM
        const text = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const option = new Option(text, value);
        if (i === 0) option.selected = true;
        select.appendChild(option);
    }
    
    currentQRSelection = select.value;
}

// Load QR scan data
async function loadQRData() {
    try {
        // Simulate API call - replace with actual endpoint
        const response = await fetch(`/api/reports/qr-scan-data?period=${currentQRPeriod}&selection=${currentQRSelection}`);
        const data = await response.json();
        
        // Update QR summary cards
        updateQRSummaryCards(data.summary);
        
        // Update QR table
        currentQRData = data.scanLogs || [];
        updateQRTable();
        
    } catch (error) {
        console.error('Error loading QR scan data:', error);
        // Use mock data for demo
        loadMockQRData();
    }
}

// Load mock QR data for demonstration
function loadMockQRData() {
    const mockQRSummary = {
        totalScans: 145,
        uniqueItems: 25,
        activeUsers: 8,
        todayScans: 12
    };
    
    const mockQRData = [
        { 
            timestamp: '2025-10-04 14:30:25', 
            item: 'Ballpen - Black', 
            user: 'John Doe', 
            scanner_type: 'mobile', 
            location: 'Office Supply Room', 
            ip_address: '192.168.1.101',
            item_id: 1,
            user_id: 5
        },
        { 
            timestamp: '2025-10-04 14:25:18', 
            item: 'A4 Bond Paper', 
            user: 'Jane Smith', 
            scanner_type: 'webcam', 
            location: 'Main Office', 
            ip_address: '192.168.1.102',
            item_id: 2,
            user_id: 3
        },
        { 
            timestamp: '2025-10-04 14:20:45', 
            item: 'Notebook', 
            user: 'Mike Johnson', 
            scanner_type: 'mobile', 
            location: 'Faculty Room', 
            ip_address: '192.168.1.103',
            item_id: 3,
            user_id: 7
        },
        { 
            timestamp: '2025-10-04 14:15:30', 
            item: 'Stapler', 
            user: 'Sarah Wilson', 
            scanner_type: 'webcam', 
            location: 'Admin Office', 
            ip_address: '192.168.1.104',
            item_id: 4,
            user_id: 2
        },
        { 
            timestamp: '2025-10-04 14:10:12', 
            item: 'Whiteboard Marker', 
            user: 'Robert Brown', 
            scanner_type: 'mobile', 
            location: 'Classroom 101', 
            ip_address: '192.168.1.105',
            item_id: 5,
            user_id: 6
        }
    ];
    
    updateQRSummaryCards(mockQRSummary);
    currentQRData = mockQRData;
    updateQRTable();
}

// Update QR summary cards
function updateQRSummaryCards(summary) {
    document.getElementById('totalScans').textContent = summary.totalScans || 0;
    document.getElementById('uniqueItems').textContent = summary.uniqueItems || 0;
    document.getElementById('activeUsers').textContent = summary.activeUsers || 0;
    document.getElementById('todayScans').textContent = summary.todayScans || 0;
}

// Update QR table
function updateQRTable(filteredData = null) {
    const data = filteredData || currentQRData;
    const tbody = document.getElementById('qrTableBody');
    const emptyState = document.getElementById('qrEmptyState');
    
    if (data.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    // Pagination
    const startIndex = (currentQRPage - 1) * qrItemsPerPage;
    const endIndex = startIndex + qrItemsPerPage;
    const paginatedData = data.slice(startIndex, endIndex);
    
    tbody.innerHTML = paginatedData.map(scan => `
        <tr>
            <td>
                <small class="fw-semibold">${formatTimestamp(scan.timestamp)}</small>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-box text-primary me-2"></i>
                    <span class="fw-semibold">${scan.item}</span>
                </div>
            </td>
            <td>
                <span class="text-muted">${scan.user}</span>
            </td>
            <td>
                <span class="badge ${scan.scanner_type === 'mobile' ? 'bg-success' : 'bg-info'} badge-sm">
                    <i class="fas ${scan.scanner_type === 'mobile' ? 'fa-mobile-alt' : 'fa-desktop'} me-1"></i>
                    ${scan.scanner_type.charAt(0).toUpperCase() + scan.scanner_type.slice(1)}
                </span>
            </td>
            <td>
                <small class="text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    ${scan.location || 'N/A'}
                </small>
            </td>
            <td>
                <small class="text-muted font-monospace">${scan.ip_address}</small>
            </td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewScanDetails(${JSON.stringify(scan).replace(/"/g, '&quot;')})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="viewItemHistory(${scan.item_id})">
                        <i class="fas fa-history"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    updateQRPagination(data.length);
}

// Update QR pagination
function updateQRPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / qrItemsPerPage);
    const pagination = document.getElementById('qrTablePagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentQRPage === 1 ? 'disabled' : ''}">
            <button class="page-link" onclick="changeQRPage(${currentQRPage - 1})" ${currentQRPage === 1 ? 'disabled' : ''}>
                <i class="fas fa-chevron-left"></i>
            </button>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentQRPage - 2 && i <= currentQRPage + 2)) {
            paginationHTML += `
                <li class="page-item ${i === currentQRPage ? 'active' : ''}">
                    <button class="page-link" onclick="changeQRPage(${i})">${i}</button>
                </li>
            `;
        } else if (i === currentQRPage - 3 || i === currentQRPage + 3) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentQRPage === totalPages ? 'disabled' : ''}">
            <button class="page-link" onclick="changeQRPage(${currentQRPage + 1})" ${currentQRPage === totalPages ? 'disabled' : ''}>
                <i class="fas fa-chevron-right"></i>
            </button>
        </li>
    `;
    
    pagination.innerHTML = paginationHTML;
}

// Change QR page
function changeQRPage(page) {
    currentQRPage = page;
    updateQRTable();
}

// Filter QR table
function filterQRTable(searchTerm) {
    const filteredData = currentQRData.filter(scan => 
        scan.item.toLowerCase().includes(searchTerm.toLowerCase()) ||
        scan.user.toLowerCase().includes(searchTerm.toLowerCase()) ||
        scan.location.toLowerCase().includes(searchTerm.toLowerCase()) ||
        scan.scanner_type.toLowerCase().includes(searchTerm.toLowerCase())
    );
    currentQRPage = 1;
    updateQRTable(filteredData);
}

// Download QR report
function downloadQRReport() {
    const params = new URLSearchParams({
        type: 'qr_scans',
        selection: currentQRSelection,
        format: 'pdf'
    });
    
    window.open(`/reports/qr-scan-logs/download?${params.toString()}`, '_blank');
}

// Export QR to Excel
function exportQRToExcel() {
    const params = new URLSearchParams({
        type: 'qr_scans',
        selection: currentQRSelection,
        format: 'excel'
    });
    
    window.open(`/reports/qr-scan-logs/export?${params.toString()}`, '_blank');
}

// Helper function to format timestamp
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// View scan details
function viewScanDetails(scan) {
    alert(`QR Scan Details:
    
Timestamp: ${scan.timestamp}
Item: ${scan.item}
User: ${scan.user}
Scanner Type: ${scan.scanner_type}
Location: ${scan.location}
IP Address: ${scan.ip_address}

This would open a detailed modal with full scan information.`);
}

// View item scan history
function viewItemHistory(itemId) {
    alert(`Item Scan History for Item ID: ${itemId}
    
This would show a detailed history of all scans for this specific item, including:
- All scan timestamps
- Users who scanned
- Locations where scanned
- Scanner types used

Would you like to navigate to the detailed item history page?`);
}
</script>
@endsection