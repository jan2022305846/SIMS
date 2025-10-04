@extends('layouts.app')

@section('title', 'Help & Documentation')

@section('styles')
<style>
/* Custom styles based on item/index.blade.php approach */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.hover-bg-light {
    transition: background-color 0.2s ease;
}

.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}

.search-container {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .search-container .input-group {
        width: 100% !important;
    }
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h3 fw-semibold text-dark mb-0">
                    <i class="fas fa-question-circle me-2 text-primary"></i>
                    Help & Documentation
                </h2>
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Search Box -->
                    <div class="search-container">
                        <div class="input-group">
                            <input type="text" id="helpSearch" class="form-control" placeholder="Search help topics...">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchHelp()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="searchResultsDropdown" class="search-results d-none">
                            <!-- Search results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Results (Hidden initially) -->
            <div id="searchResults" class="card border-0 shadow-sm mb-4" style="display: none;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        Search Results
                    </h5>
                </div>
                <div class="card-body" id="searchResultsContent">
                    <!-- Search results will be populated here -->
                </div>
            </div>

            <!-- Quick Start Guide -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Quick Start Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(auth()->user()->role === 'faculty')
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Browse Items</h6>
                                    <p class="text-muted small mb-0">Explore available supplies and learn about item details.</p>
                                    <a href="{{ route('help.show', 'browse-items') }}" class="btn btn-sm btn-outline-primary mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Create Request</h6>
                                    <p class="text-muted small mb-0">Submit supply requests with clear requirements.</p>
                                    <a href="{{ route('help.show', 'create-request') }}" class="btn btn-sm btn-outline-success mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Track Status</h6>
                                    <p class="text-muted small mb-0">Monitor request progress and approval status.</p>
                                    <a href="{{ route('help.show', 'track-request') }}" class="btn btn-sm btn-outline-info mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Use QR Codes</h6>
                                    <p class="text-muted small mb-0">Scan QR codes for quick item verification.</p>
                                    <a href="{{ route('help.show', 'qr-scanning') }}" class="btn btn-sm btn-outline-warning mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Manage Items</h6>
                                    <p class="text-muted small mb-0">Add, edit, and organize inventory efficiently.</p>
                                    <a href="{{ route('help.show', 'add-item') }}" class="btn btn-sm btn-outline-primary mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Process Requests</h6>
                                    <p class="text-muted small mb-0">Review, approve, and fulfill supply requests.</p>
                                    <a href="{{ route('help.show', 'process-requests') }}" class="btn btn-sm btn-outline-success mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">View Reports</h6>
                                    <p class="text-muted small mb-0">Access analytics and generate comprehensive reports.</p>
                                    <a href="{{ route('help.show', 'dashboard-analytics') }}" class="btn btn-sm btn-outline-info mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Monitor System</h6>
                                    <p class="text-muted small mb-0">Track QR scans, user activity, and system health.</p>
                                    <a href="{{ route('help.show', 'qr-scan-analytics') }}" class="btn btn-sm btn-outline-warning mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Popular Topics & Stats -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2 text-warning"></i>
                                Popular Topics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(auth()->user()->role === 'faculty')
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'create-request') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-plus-circle text-success fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">How to Create a Request</h6>
                                                <small class="text-muted">Step-by-step guide to submitting requests</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'track-request') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-eye text-info fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Tracking Your Requests</h6>
                                                <small class="text-muted">Monitor request status and progress</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'qr-scanning') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-qrcode text-primary fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">QR Code Scanning</h6>
                                                <small class="text-muted">Quick item verification and lookup</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'request-status') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-info-circle text-warning fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Understanding Request Status</h6>
                                                <small class="text-muted">What different statuses mean</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @else
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'process-requests') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-tasks text-success fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Processing Requests</h6>
                                                <small class="text-muted">Review and approve supply requests</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'dashboard-analytics') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-chart-bar text-info fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Dashboard Analytics</h6>
                                                <small class="text-muted">Understanding key metrics and insights</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'stock-management') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-boxes text-primary fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Stock Management</h6>
                                                <small class="text-muted">Monitor and maintain inventory levels</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('help.show', 'qr-scan-analytics') }}" class="text-decoration-none">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <i class="fas fa-chart-line text-warning fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-1">QR Scan Analytics</h6>
                                                <small class="text-muted">Monitor scanning activities and trends</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2 text-info"></i>
                                Help Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="display-4 text-primary fw-bold">{{ collect($helpSections)->sum(function($section) { return count($section['topics']); }) }}</div>
                                <small class="text-muted">Total Help Topics</small>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <div class="h4 text-success mb-0">{{ count($helpSections) }}</div>
                                        <small class="text-muted">Sections</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-info mb-0">{{ auth()->user()->role === 'faculty' ? '4' : '5' }}</div>
                                    <small class="text-muted">For Your Role</small>
                                </div>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Use the search bar above to quickly find specific topics, or browse the sections below.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach($helpSections as $sectionKey => $section)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="{{ $section['icon'] }} text-primary fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="mb-1">{{ $section['title'] }}</h5>
                                        <span class="badge bg-primary">{{ count($section['topics']) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $section['description'] }}</small>
                                </div>
                            </div>

                            <div class="list-group list-group-flush">
                                @foreach($section['topics'] as $topicKey => $topicTitle)
                                <a href="{{ route('help.show', $topicKey) }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-chevron-right me-2 text-muted small"></i>
                                        {{ $topicTitle }}
                                    </span>
                                    <i class="fas fa-external-link-alt text-muted small"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Additional Resources -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Additional Resources & Support
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-video fs-2 text-primary mb-2"></i>
                                <h6>Video Tutorials</h6>
                                <p class="text-muted small mb-2">Coming soon - Watch step-by-step video guides</p>
                                <span class="badge bg-secondary">Coming Soon</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-download fs-2 text-success mb-2"></i>
                                <h6>Quick Reference</h6>
                                <p class="text-muted small mb-2">Download printable quick reference guides</p>
                                <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>Print Guide
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-envelope fs-2 text-info mb-2"></i>
                                <h6>Contact Support</h6>
                                <p class="text-muted small mb-2">Need help? Contact the supply office team</p>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <i class="fas fa-envelope me-1"></i>Contact Us
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="text-center p-3 border rounded h-100">
                                <i class="fas fa-keyboard fs-2 text-warning mb-2"></i>
                                <h6>Keyboard Shortcuts</h6>
                                <p class="text-muted small mb-2">Learn useful keyboard shortcuts</p>
                                <a href="{{ route('help.show', 'navigation') }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-keyboard me-1"></i>View Shortcuts
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Modal -->
                    <div class="modal fade" id="contactModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Contact Support</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>For technical support or questions about the Supply Office system:</p>
                                    <div class="mb-3">
                                        <strong>Supply Office:</strong><br>
                                        Email: supply@ustp.edu.ph<br>
                                        Phone: (088) 123-4567<br>
                                        Office Hours: Mon-Fri, 8:00 AM - 5:00 PM
                                    </div>
                                    <div class="mb-3">
                                        <strong>IT Support:</strong><br>
                                        Email: it-support@ustp.edu.ph<br>
                                        Phone: (088) 123-4568<br>
                                        Emergency: (088) 123-4569
                                    </div>
                                    <div class="alert alert-info">
                                        <small><i class="fas fa-info-circle me-1"></i>
                                        For urgent issues, please call the emergency line. Response time: 1-2 hours during office hours.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
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

    // Help search functionality
    let searchTimeout;
    const searchInput = document.getElementById('helpSearch');
    const searchResults = document.getElementById('searchResultsDropdown');

    document.getElementById('helpSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchHelp(query);
            }, 300);
        } else {
            hideSearchResults();
        }
    });

    document.getElementById('helpSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchHelp(this.value.trim());
        }
    });

    document.getElementById('helpSearch').addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            searchHelp(this.value.trim());
        }
    });

    function searchHelp(query = null) {
        if (!query) {
            query = document.getElementById('helpSearch').value.trim();
        }

        if (query.length < 2) {
            hideSearchResults();
            return;
        }

        fetch(`{{ route('help.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    function displaySearchResults(data) {
        if (data.results.length === 0) {
            searchResults.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="fas fa-search fs-3 mb-3"></i>
                    <p class="mb-1">No results found for "${data.query}"</p>
                    <small>Try different keywords or browse the sections below</small>
                </div>
            `;
        } else {
            const resultsHtml = data.results.map(result => `
                <a href="${result.url}" class="dropdown-item d-flex justify-content-between align-items-center py-2 px-3">
                    <div>
                        <div class="fw-medium">${result.title}</div>
                        <small class="text-muted">${result.description}</small>
                        <div class="small text-primary">${result.section}</div>
                    </div>
                    <i class="fas fa-external-link-alt text-muted"></i>
                </a>
            `).join('');

            searchResults.innerHTML = `
                <div class="dropdown-header px-3 py-2 bg-light fw-medium">
                    <i class="fas fa-search me-2"></i>
                    Found ${data.count} result(s) for "${data.query}"
                </div>
                ${resultsHtml}
            `;
        }

        searchResults.classList.remove('d-none');
    }

    function hideSearchResults() {
        searchResults.classList.add('d-none');
    }

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        const searchContainer = document.querySelector('.search-container');

        if (!searchContainer.contains(e.target)) {
            hideSearchResults();
        }
    });
});
</script>

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

@endsection