@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
                    <h2 class="h4 fw-bold text-dark mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Report Management
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
                            <select class="form-select" id="periodSelect" style="min-width: 180px;" disabled>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <!-- Download Button -->
                        <button class="btn btn-success" id="downloadBtn">
                            <i class="fas fa-download me-1"></i>Download DOCX
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
                    <div class="col-md-4">
                        <div class="card border-primary h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0" id="totalItems">0</h4>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-danger h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                                <h4 class="mb-0" id="totalReleased">0</h4>
                                <small class="text-muted">Items Released/Claimed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-warehouse fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0" id="totalRemaining">0</h4>
                                <small class="text-muted">Items Remaining</small>
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

                <!-- Item Scan Logs Section -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list fa-2 text-info"></i>
                            Item Scan Logs
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- QR Period Selection -->
                            <select class="form-select form-select-sm" id="qrPeriodSelect" style="min-width: 150px;">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                            <button class="btn btn-outline-success btn-sm" id="downloadQRBtn">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- QR Scan Logs Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="qrScanTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Item Scanned</th>
                                        <th>Action</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody id="qrScanTableBody">
                                    <!-- Scan data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- QR Empty State -->
                        <div id="qrEmptyState" class="text-center py-5" style="display: none;">
                            <i class="fas fa-list fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Scan Data</h5>
                            <p class="text-muted">No item scan logs found for the selected period.</p>
                        </div>
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

// QR Scan Analytics variables
let currentQRPeriod = 'monthly';
let currentQRSelection = null;
let currentQRData = [];

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
    
    // QR Scan Analytics Event Listeners
    document.getElementById('qrPeriodSelect').addEventListener('change', function() {
        currentQRSelection = this.value;
        // Parse the selection to get period and selection
        const [period, selection] = this.value.split('_');
        currentQRPeriod = period;
        loadQRData();
    });
    
    document.getElementById('downloadQRBtn').addEventListener('click', downloadQRReport);
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
        // For monthly, just use current month - disable dropdown
        const currentMonthValue = currentDate.toISOString().substring(0, 7);
        const currentMonthText = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        const option = new Option(currentMonthText, currentMonthValue);
        option.selected = true;
        select.appendChild(option);
        select.disabled = true;
    } else if (currentPeriod === 'quarterly') {
        // Simple Q1-Q4 options
        const quarters = [
            { value: 'Q1', text: 'Q1 (Jan-Mar)' },
            { value: 'Q2', text: 'Q2 (Apr-Jun)' },
            { value: 'Q3', text: 'Q3 (Jul-Sep)' },
            { value: 'Q4', text: 'Q4 (Oct-Dec)' }
        ];

        quarters.forEach((quarter) => {
            const option = new Option(quarter.text, quarter.value);
            // Select current quarter by default
            const currentQuarter = Math.floor(currentDate.getMonth() / 3) + 1;
            if (quarter.value === `Q${currentQuarter}`) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        select.disabled = false;
    } else if (currentPeriod === 'annual') {
        // For annual, just use current year - disable dropdown
        const currentYear = currentDate.getFullYear().toString();
        const option = new Option(currentYear, currentYear);
        option.selected = true;
        select.appendChild(option);
        select.disabled = true;
    }

    // Set currentSelection to the selected value
    currentSelection = select.value;
}

// Helper function to get quarter month names
function getQuarterMonths(quarter) {
    const quarters = {
        1: 'Jan-Mar',
        2: 'Apr-Jun',
        3: 'Jul-Sep',
        4: 'Oct-Dec'
    };
    return quarters[quarter] || '';
}

// Get default selection based on current period
function getDefaultSelection(period) {
    const currentDate = new Date();

    if (period === 'monthly') {
        return currentDate.toISOString().substring(0, 7); // Current YYYY-MM
    } else if (period === 'quarterly') {
        const currentQuarter = Math.floor(currentDate.getMonth() / 3) + 1;
        return `Q${currentQuarter}`;
    } else if (period === 'annual') {
        return currentDate.getFullYear().toString();
    }
    return null;
}

// Update titles based on current period
function updateTitles() {
    const chartTitle = document.getElementById('chartTitle');
    
    const titles = {
        monthly: 'Monthly Inventory Movement',
        quarterly: 'Quarterly Inventory Movement',
        annual: 'Annual Inventory Movement'
    };
    
    chartTitle.textContent = titles[currentPeriod];
}

// Load report data
async function loadReportData() {
    showLoading(true);
    
    // Ensure we have a valid selection
    if (!currentSelection) {
        currentSelection = getDefaultSelection(currentPeriod);
    }
    
    try {
        // Use real API data instead of mock data
        const url = `/api/reports/inventory-data?period=${currentPeriod}&selection=${currentSelection}`;
        
        const response = await fetch(url, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Update summary cards
        updateSummaryCards(data.summary);
        
        // Update chart
        updateChart(data.chartData);
        
    } catch (error) {
        console.error('Error loading report data:', error);
        // Show empty state when API fails
        updateSummaryCards({
            totalItems: 0,
            totalAdded: 0,
            totalReleased: 0,
            currentStock: 0
        });
        updateChart([]); // Pass empty array for no data
    } finally {
        showLoading(false);
    }
}


// Update summary cards
function updateSummaryCards(summary) {
    document.getElementById('totalItems').textContent = summary.totalItems || 0;
    document.getElementById('totalRemaining').textContent = summary.currentStock || 0;
    document.getElementById('totalReleased').textContent = summary.totalReleased || 0;
}

// Update chart
function updateChart(chartData) {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    
    if (inventoryChart) {
        inventoryChart.destroy();
    }
    
    // Transform the data to Chart.js format
    const labels = chartData.map(item => item.date);
    const remainingData = chartData.map(item => item.remaining || 0);
    const releasedData = chartData.map(item => item.released || 0);
    
    const chartJsData = {
        labels: labels,
        datasets: [{
            label: 'Items Remaining',
            data: remainingData,
            backgroundColor: 'rgba(255, 193, 7, 0.6)', // Warning/orange color to match the stat card
            borderColor: 'rgba(255, 193, 7, 1)',
            borderWidth: 2
        }, {
            label: 'Items Released',
            data: releasedData,
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2
        }]
    };
    
    inventoryChart = new Chart(ctx, {
        type: 'bar',
        data: chartJsData,
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

// Show/hide loading
function showLoading(show) {
    document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
}

// Download DOCX report
function downloadReport() {
    const params = new URLSearchParams({
        period: currentPeriod,
        selection: currentSelection,
        format: 'docx'
    });
    
    window.open(`/admin/reports/download?${params.toString()}`, '_blank');
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
    
    // Add monthly option - current month
    const currentMonthValue = currentDate.toISOString().substring(0, 7); // YYYY-MM
    const currentMonthText = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    const monthOption = new Option(`Monthly - ${currentMonthText}`, `monthly_${currentMonthValue}`);
    monthOption.selected = true; // Default to monthly
    select.appendChild(monthOption);
    
    // Add annual option - current year
    const currentYear = currentDate.getFullYear().toString();
    const annualOption = new Option(`Annual - ${currentYear}`, `annual_${currentYear}`);
    select.appendChild(annualOption);
    
    // Set current selection
    currentQRSelection = `monthly_${currentMonthValue}`;
}

// Load QR scan data
async function loadQRData() {
    // Parse the current selection to get period and selection
    let period = 'monthly';
    let selection = '';
    
    if (currentQRSelection) {
        const parts = currentQRSelection.split('_');
        if (parts.length === 2) {
            period = parts[0];
            selection = parts[1];
        }
    }
    
    try {
        // Use real API data instead of mock data
        const url = `/admin/api/reports/qr-scan-data?period=${period}&selection=${selection}`;
        
        const response = await fetch(url, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('QR API Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Update QR table (no summary cards in HTML)
        currentQRData = data.scanLogs || [];
        updateQRTable();
        
    } catch (error) {
        console.error('Error loading QR scan data:', error);
        // Show empty state when API fails (no mock data fallback)
        currentQRData = [];
        updateQRTable();
    }
}



// Update QR table
function updateQRTable(filteredData = null) {
    const data = filteredData || currentQRData;
    const tbody = document.getElementById('qrScanTableBody');
    const emptyState = document.getElementById('qrEmptyState');
    
    if (data.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    // Show all data (limited to 10 from backend, no pagination)
    tbody.innerHTML = data.map(scan => `
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
                <span class="badge ${getActionBadgeClass(scan.action)} badge-sm">
                    <i class="fas ${getActionIcon(scan.action)} me-1"></i>
                    ${formatAction(scan.action)}
                </span>
            </td>
            <td>
                <small class="text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    ${scan.location || 'N/A'}
                </small>
            </td>
        </tr>
    `).join('');
}

// Update QR pagination
function updateQRPagination(totalItems) {
    // No pagination - showing all items (limited to 10 from backend)
}

// Change QR page
function changeQRPage(page) {
    // No pagination functionality
}

// Filter QR table
function filterQRTable(searchTerm) {
    // No search functionality in current design
}

// Download QR report
function downloadQRReport() {
    const params = new URLSearchParams({
        type: 'qr_scans',
        selection: currentQRSelection,
        format: 'docx'
    });
    
    window.open(`/admin/reports/qr-scan-logs/download?${params.toString()}`, '_blank');
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

// Helper function to format action names
function formatAction(action) {
    const actionMap = {
        'inventory_check': 'Inventory Check',
        'updated': 'Updated',
        'item_claim': 'Item Claim',
        'item_fulfill': 'Item Fulfill',
        'stock_adjustment': 'Stock Adjustment'
    };
    return actionMap[action] || action.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

// Helper function to get action badge class
function getActionBadgeClass(action) {
    const badgeMap = {
        'inventory_check': 'bg-info',
        'updated': 'bg-warning',
        'item_claim': 'bg-success',
        'item_fulfill': 'bg-primary',
        'stock_adjustment': 'bg-warning'
    };
    return badgeMap[action] || 'bg-secondary';
}

// Helper function to get action icon
function getActionIcon(action) {
    const iconMap = {
        'inventory_check': 'fa-search',
        'updated': 'fa-edit',
        'item_claim': 'fa-hand-holding',
        'item_fulfill': 'fa-box-open',
        'stock_adjustment': 'fa-balance-scale'
    };
    return iconMap[action] || 'fa-cog';
}

// View scan details
function viewScanDetails(scan) {
    alert(`Scan Log Details:
    
ID: ${scan.id}
Timestamp: ${scan.timestamp}
Item: ${scan.item} (${scan.item_type})
Action: ${formatAction(scan.action)}
Location: ${scan.location || 'N/A'}
Notes: ${scan.notes || 'No notes'}

This would open a detailed modal with full scan information.`);
}

// View item scan history
function viewItemHistory(itemId) {
    alert(`Item Scan History for Item ID: ${itemId}
    
This would show a detailed history of all scan logs for this specific item, including:
- All scan timestamps
- Users who performed scans
- Actions performed
- Locations where scanned
- Notes from each scan

Would you like to navigate to the detailed item scan history page?`);
}
</script>
@endsection