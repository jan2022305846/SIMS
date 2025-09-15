{{-- resources/views/admin/search/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Global Search')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Global Search</h1>
            <p class="text-muted">Search across all sections of the system</p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="searchQuery">Search Query</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="searchQuery" 
                                       name="query" 
                                       placeholder="Enter your search terms..."
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <!-- Search Suggestions -->
                            <div id="searchSuggestions" class="list-group position-absolute" style="z-index: 1000; width: calc(100% - 15px); display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-outline-secondary" data-toggle="collapse" data-target="#advancedFilters">
                                    <i class="fas fa-filter"></i> Advanced Filters
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="exportResults()" id="exportBtn" disabled>
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="collapse" id="advancedFilters">
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Search Sections</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[]" value="users" id="section_users" checked>
                                    <label class="form-check-label" for="section_users">Users</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[]" value="items" id="section_items" checked>
                                    <label class="form-check-label" for="section_items">Items</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[]" value="categories" id="section_categories" checked>
                                    <label class="form-check-label" for="section_categories">Categories</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[]" value="requests" id="section_requests" checked>
                                    <label class="form-check-label" for="section_requests">Requests</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sections[]" value="activity_logs" id="section_logs">
                                    <label class="form-check-label" for="section_logs">Activity Logs</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateFrom">Date Range</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="filters[date_from]" id="dateFrom">
                                        <small class="text-muted">From</small>
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control" name="filters[date_to]" id="dateTo">
                                        <small class="text-muted">To</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="statusFilter">Status Filter</label>
                                <select class="form-control" name="filters[status]" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="fulfilled">Fulfilled</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    <div id="searchResults" style="display: none;">
        <!-- Results Summary -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 id="resultsTitle">Search Results</h5>
                        <p id="resultsSummary" class="text-muted"></p>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortResults('relevance')">
                                <i class="fas fa-sort"></i> Relevance
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortResults('date')">
                                <i class="fas fa-calendar"></i> Date
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortResults('type')">
                                <i class="fas fa-tags"></i> Type
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Tabs -->
        <ul class="nav nav-tabs" id="resultsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">
                    All Results <span id="allCount" class="badge badge-secondary">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab">
                    Users <span id="usersCount" class="badge badge-secondary">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="items-tab" data-toggle="tab" href="#items" role="tab">
                    Items <span id="itemsCount" class="badge badge-secondary">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="categories-tab" data-toggle="tab" href="#categories" role="tab">
                    Categories <span id="categoriesCount" class="badge badge-secondary">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="requests-tab" data-toggle="tab" href="#requests" role="tab">
                    Requests <span id="requestsCount" class="badge badge-secondary">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="logs-tab" data-toggle="tab" href="#activity_logs" role="tab">
                    Activity <span id="logsCount" class="badge badge-secondary">0</span>
                </a>
            </li>
        </ul>

        <!-- Results Content -->
        <div class="tab-content" id="resultsTabContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <div id="allResults"></div>
            </div>
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div id="usersResults"></div>
            </div>
            <div class="tab-pane fade" id="items" role="tabpanel">
                <div id="itemsResults"></div>
            </div>
            <div class="tab-pane fade" id="categories" role="tabpanel">
                <div id="categoriesResults"></div>
            </div>
            <div class="tab-pane fade" id="requests" role="tabpanel">
                <div id="requestsResults"></div>
            </div>
            <div class="tab-pane fade" id="activity_logs" role="tabpanel">
                <div id="activity_logsResults"></div>
            </div>
        </div>
    </div>

    <!-- No Results -->
    <div id="noResults" class="card" style="display: none;">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5>No Results Found</h5>
            <p class="text-muted">Try adjusting your search terms or filters</p>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Search Results</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="form-group">
                            <label>Export Format</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" value="csv" id="format_csv" checked>
                                <label class="form-check-label" for="format_csv">CSV</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" value="json" id="format_json">
                                <label class="form-check-label" for="format_json">JSON</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" value="excel" id="format_excel">
                                <label class="form-check-label" for="format_excel">Excel</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Include Sections</label>
                            <div id="exportSections"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="performExport()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentResults = {};
let currentQuery = '';
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    // Search form submission
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    // Search suggestions
    document.getElementById('searchQuery').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                getSuggestions(query);
            }, 300);
        } else {
            hideSuggestions();
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#searchQuery') && !e.target.closest('#searchSuggestions')) {
            hideSuggestions();
        }
    });
});

function performSearch() {
    const formData = new FormData(document.getElementById('searchForm'));
    const query = formData.get('query');
    
    if (!query || query.trim().length < 2) {
        toastr.error('Please enter at least 2 characters to search');
        return;
    }

    currentQuery = query;
    
    // Show loading
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('noResults').style.display = 'none';
    toastr.info('Searching...');

    // Prepare search data
    const searchData = {
        query: query,
        sections: formData.getAll('sections[]'),
        filters: {}
    };

    // Add filters
    const filterElements = document.querySelectorAll('[name^="filters["]');
    filterElements.forEach(element => {
        if (element.value) {
            const filterName = element.name.match(/filters\[([^\]]+)\]/)[1];
            searchData.filters[filterName] = element.value;
        }
    });

    fetch('{{ route("admin.search.api") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentResults = data.results;
            displayResults(data);
            toastr.success(`Found ${data.total_results} results`);
        } else {
            toastr.error(data.message || 'Search failed');
            showNoResults();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Search error occurred');
        showNoResults();
    });
}

function displayResults(data) {
    const results = data.results;
    const totalResults = data.total_results;

    if (totalResults === 0) {
        showNoResults();
        return;
    }

    // Update summary
    document.getElementById('resultsTitle').textContent = `Search Results for "${data.query}"`;
    document.getElementById('resultsSummary').textContent = `Found ${totalResults} results`;

    // Update tab counts
    const allResults = [];
    Object.keys(results).forEach(section => {
        const count = results[section] ? results[section].length : 0;
        document.getElementById(section + 'Count').textContent = count;
        
        if (results[section]) {
            allResults.push(...results[section]);
        }
    });
    
    document.getElementById('allCount').textContent = allResults.length;

    // Display results for each section
    displaySectionResults('all', allResults);
    Object.keys(results).forEach(section => {
        if (results[section]) {
            displaySectionResults(section, results[section]);
        }
    });

    // Show results
    document.getElementById('searchResults').style.display = 'block';
    document.getElementById('exportBtn').disabled = false;

    hideSuggestions();
}

function displaySectionResults(section, items) {
    const container = document.getElementById(section + 'Results');
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="card"><div class="card-body text-center py-4"><p class="text-muted">No results found in this section</p></div></div>';
        return;
    }

    let html = '<div class="card-deck">';
    
    items.forEach((item, index) => {
        if (index > 0 && index % 3 === 0) {
            html += '</div><div class="card-deck mt-3">';
        }
        
        html += `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">
                                <a href="${item.url}" class="text-decoration-none">
                                    ${item.title}
                                </a>
                                <span class="badge badge-${getTypeColor(item.type)} ml-2">${item.type.replace('_', ' ')}</span>
                            </h6>
                            <p class="card-subtitle text-muted mb-2">${item.subtitle}</p>
                            <p class="card-text small">${item.description}</p>
                        </div>
                    </div>
                    <div class="mt-2">
                        ${formatMetadata(item.meta)}
                    </div>
                    <small class="text-muted">${formatDate(item.created_at)}</small>
                </div>
            </div>`;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function formatMetadata(meta) {
    if (!meta) return '';
    
    let html = '';
    Object.keys(meta).forEach(key => {
        if (meta[key] && key !== 'properties') {
            html += `<span class="badge badge-light mr-1">${key.replace('_', ' ')}: ${meta[key]}</span>`;
        }
    });
    
    return html;
}

function getTypeColor(type) {
    const colors = {
        'user': 'primary',
        'item': 'success',
        'category': 'info',
        'request': 'warning',
        'activity_log': 'secondary'
    };
    return colors[type] || 'secondary';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showNoResults() {
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('exportBtn').disabled = true;
}

function getSuggestions(query) {
    fetch('{{ route("admin.search.suggestions") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ query: query })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySuggestions(data.suggestions);
        }
    })
    .catch(error => {
        console.error('Error getting suggestions:', error);
    });
}

function displaySuggestions(suggestions) {
    const container = document.getElementById('searchSuggestions');
    let html = '';
    
    const allSuggestions = [];
    Object.keys(suggestions).forEach(section => {
        suggestions[section].forEach(suggestion => {
            if (!allSuggestions.includes(suggestion)) {
                allSuggestions.push(suggestion);
            }
        });
    });
    
    allSuggestions.slice(0, 5).forEach(suggestion => {
        html += `<button type="button" class="list-group-item list-group-item-action" onclick="selectSuggestion('${suggestion}')">${suggestion}</button>`;
    });
    
    if (html) {
        container.innerHTML = html;
        container.style.display = 'block';
    } else {
        hideSuggestions();
    }
}

function selectSuggestion(suggestion) {
    document.getElementById('searchQuery').value = suggestion;
    hideSuggestions();
    performSearch();
}

function hideSuggestions() {
    document.getElementById('searchSuggestions').style.display = 'none';
}

function sortResults(sortBy) {
    // Implementation for sorting results
    toastr.info(`Sorting by ${sortBy}...`);
    // You would implement the actual sorting logic here
}

function exportResults() {
    // Populate export sections based on current results
    const sections = Object.keys(currentResults).filter(section => currentResults[section] && currentResults[section].length > 0);
    
    let html = '';
    sections.forEach(section => {
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="export_sections[]" value="${section}" id="export_${section}" checked>
                <label class="form-check-label" for="export_${section}">
                    ${section.replace('_', ' ').toUpperCase()} (${currentResults[section].length} items)
                </label>
            </div>`;
    });
    
    document.getElementById('exportSections').innerHTML = html;
    $('#exportModal').modal('show');
}

function performExport() {
    const formData = new FormData(document.getElementById('exportForm'));
    const format = formData.get('format');
    const sections = formData.getAll('export_sections[]');
    
    if (sections.length === 0) {
        toastr.error('Please select at least one section to export');
        return;
    }

    const exportData = {
        query: currentQuery,
        sections: sections,
        format: format
    };

    fetch('{{ route("admin.search.export") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(exportData)
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Export failed');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `search_results_${format}.${format === 'json' ? 'json' : 'csv'}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        toastr.success('Export completed successfully');
        $('#exportModal').modal('hide');
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Export failed');
    });
}
</script>
@endsection