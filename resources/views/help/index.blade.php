@extends('layouts.app')

@section('title', 'Help & Documentation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-question-circle me-2 text-primary"></i>
                                Help & Documentation
                            </h2>
                            <p class="text-muted mb-0">Find answers and learn how to use the Supply Office system effectively</p>
                        </div>
                        <div>
                            <!-- Search Box -->
                            <div class="input-group" style="width: 300px;">
                                <input type="text" id="helpSearch" class="form-control" placeholder="Search help topics...">
                                <button class="btn btn-outline-secondary" type="button" onclick="searchHelp()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Quick Start Guide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(auth()->user()->role === 'faculty')
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Browse Available Items</h6>
                                    <p class="text-muted small mb-0">Explore the inventory to see what supplies are available for request.</p>
                                    <a href="{{ route('items.browse') }}" class="btn btn-sm btn-outline-primary mt-2">Start Browsing</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Submit a Request</h6>
                                    <p class="text-muted small mb-0">Create a new supply request with the items you need.</p>
                                    <a href="{{ route('faculty.requests.create') }}" class="btn btn-sm btn-outline-success mt-2">New Request</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Track Your Requests</h6>
                                    <p class="text-muted small mb-0">Monitor the status of your submitted requests.</p>
                                    <a href="{{ route('requests.my') }}" class="btn btn-sm btn-outline-info mt-2">My Requests</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Digital Acknowledgment</h6>
                                    <p class="text-muted small mb-0">Provide digital signature when receiving items.</p>
                                    <a href="{{ route('help.show', 'acknowledgment-process') }}" class="btn btn-sm btn-outline-warning mt-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">1</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Manage Inventory</h6>
                                    <p class="text-muted small mb-0">Add, edit, and organize inventory items efficiently.</p>
                                    <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-primary mt-2">Manage Items</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">2</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Process Requests</h6>
                                    <p class="text-muted small mb-0">Review and approve pending supply requests.</p>
                                    <a href="{{ route('requests.manage') }}" class="btn btn-sm btn-outline-success mt-2">View Requests</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">3</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Generate Reports</h6>
                                    <p class="text-muted small mb-0">Access comprehensive reports and analytics.</p>
                                    <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-info mt-2">View Reports</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <span class="fw-bold">4</span>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Monitor System</h6>
                                    <p class="text-muted small mb-0">Track activities and system health.</p>
                                    <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-warning mt-2">Activity Logs</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Help Sections -->
            <div class="row">
                @foreach($helpSections as $sectionKey => $section)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="{{ $section['icon'] }} text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $section['title'] }}</h5>
                                    <small class="text-muted">{{ $section['description'] }}</small>
                                </div>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                @foreach($section['topics'] as $topicKey => $topicTitle)
                                <a href="{{ route('help.show', $topicKey) }}" class="list-group-item list-group-item-action border-0 px-0 py-2">
                                    <i class="fas fa-chevron-right me-2 text-muted small"></i>
                                    {{ $topicTitle }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Additional Resources -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Additional Resources
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <i class="fas fa-video fs-2 text-primary mb-2"></i>
                                <h6>Video Tutorials</h6>
                                <p class="text-muted small">Coming soon - Watch step-by-step video guides</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <i class="fas fa-download fs-2 text-success mb-2"></i>
                                <h6>User Manual</h6>
                                <p class="text-muted small">Download the complete user manual (PDF)</p>
                                <button class="btn btn-sm btn-outline-success">Download</button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <i class="fas fa-envelope fs-2 text-info mb-2"></i>
                                <h6>Contact Support</h6>
                                <p class="text-muted small">Need personalized help? Contact our support team</p>
                                <button class="btn btn-sm btn-outline-info">Contact Us</button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <i class="fas fa-comments fs-2 text-warning mb-2"></i>
                                <h6>FAQ</h6>
                                <p class="text-muted small">Find answers to frequently asked questions</p>
                                <button class="btn btn-sm btn-outline-warning">View FAQ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Help search functionality
let searchTimeout;

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
    const resultsDiv = document.getElementById('searchResults');
    const contentDiv = document.getElementById('searchResultsContent');
    
    if (data.results.length === 0) {
        contentDiv.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fs-3 mb-3"></i>
                <p>No results found for "${data.query}"</p>
                <small>Try different keywords or browse the sections below</small>
            </div>
        `;
    } else {
        const resultsHtml = data.results.map(result => `
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">
                            <a href="${result.url}" class="text-decoration-none">${result.title}</a>
                        </h6>
                        <p class="text-muted small mb-1">${result.description}</p>
                        <small class="text-primary">${result.section}</small>
                    </div>
                    <a href="${result.url}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            </div>
        `).join('');
        
        contentDiv.innerHTML = `
            <div class="mb-3">
                <small class="text-muted">Found ${data.count} result(s) for "${data.query}"</small>
            </div>
            ${resultsHtml}
        `;
    }
    
    resultsDiv.style.display = 'block';
}

function hideSearchResults() {
    document.getElementById('searchResults').style.display = 'none';
}

// Clear search when clicking outside
document.addEventListener('click', function(e) {
    const searchBox = document.getElementById('helpSearch');
    const resultsDiv = document.getElementById('searchResults');
    
    if (!searchBox.contains(e.target) && !resultsDiv.contains(e.target)) {
        if (searchBox.value.trim() === '') {
            hideSearchResults();
        }
    }
});
</script>

@endsection