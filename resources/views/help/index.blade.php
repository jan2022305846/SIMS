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

.changelog-content {
    max-height: 400px;
    overflow-y: auto;
}

.changelog-content h3 {
    color: #198754;
    border-bottom: 2px solid #198754;
    padding-bottom: 0.5rem;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.changelog-content h4 {
    color: #0d6efd;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.changelog-content h5 {
    color: #6c757d;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.changelog-content ul {
    padding-left: 1.5rem;
}

.changelog-content li {
    margin-bottom: 0.25rem;
}

.changelog-content code {
    background-color: #f8f9fa;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.changelog-content pre {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    overflow-x: auto;
}

@media (max-width: 768px) {
    .search-container .input-group {
        width: 100% !important;
    }

    .changelog-content {
        max-height: 300px;
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

            <!-- Help Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Help Section
                    </h5>
                </div>
                <div class="card-body">
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
                </div>
            </div>

            <!-- What's New Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        What's New
                    </h5>
                </div>
                <div class="card-body">
                    <div id="changelogContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading changelog...</p>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="https://github.com/jan2022305846/SIMS/blob/main/CHANGELOG.md" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fab fa-github me-1"></i>View on GitHub
                        </a>
                    </div>
                </div>
            </div>

            <!-- Feedback Section -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Feedback
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h6 class="mb-3">Send us your feedback</h6>
                            <p class="text-muted mb-4">Found a bug or have suggestions? Help us improve the system by sending us your feedback with screenshots.</p>

                            <form id="feedbackForm" enctype="multipart/form-data">
                                <input type="hidden" id="feedbackType" name="type" value="feedback">
                                <div class="mb-3">
                                    <label for="feedbackSubject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="feedbackSubject" name="subject" placeholder="Brief description of your feedback" required>
                                </div>
                                <div class="mb-3">
                                    <label for="feedbackMessage" class="form-label">Message</label>
                                    <textarea class="form-control" id="feedbackMessage" name="message" rows="4" placeholder="Describe your feedback in detail..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="feedbackScreenshots" class="form-label">Screenshots (Optional)</label>
                                    <input type="file" class="form-control" id="feedbackScreenshots" name="screenshots[]" multiple accept="image/*">
                                    <div class="form-text">You can attach multiple image files to help illustrate your feedback.</div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="submitFeedbackBtn">
                                    <i class="fas fa-paper-plane me-1"></i>Send Feedback
                                </button>
                            </form>
                        </div>
                        <div class="col-lg-4">
                            <div class="bg-light rounded p-3">
                                <h6 class="mb-3">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    What happens next?
                                </h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        Your feedback is sent directly to our development team
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-warning me-2"></i>
                                        We typically respond within 24-48 hours
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-shield-alt text-success me-2"></i>
                                        All information is kept confidential
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-heart text-danger me-2"></i>
                                        Your input helps us improve the system
                                    </li>
                                </ul>
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

    // Load changelog from GitHub
    loadChangelog();

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

    // Load changelog from GitHub
    function loadChangelog() {
        const changelogContent = document.getElementById('changelogContent');

        // GitHub raw URL for CHANGELOG.md
        const changelogUrl = 'https://raw.githubusercontent.com/jan2022305846/SIMS/main/CHANGELOG.md';

        fetch(changelogUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load changelog');
                }
                return response.text();
            })
            .then(markdown => {
                // Convert markdown to HTML (simple conversion)
                const html = markdownToHtml(markdown);
                changelogContent.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading changelog:', error);
                changelogContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Changelog not available</strong><br>
                        Unable to load the changelog from GitHub. You can view it directly on GitHub.
                    </div>
                `;
            });
    }

    // Simple markdown to HTML converter
    function markdownToHtml(markdown) {
        let html = markdown
            // Headers
            .replace(/^### (.*$)/gim, '<h5 class="mt-4 mb-2">$1</h5>')
            .replace(/^## (.*$)/gim, '<h4 class="mt-4 mb-3">$1</h4>')
            .replace(/^# (.*$)/gim, '<h3 class="mt-4 mb-3">$1</h3>')
            // Bold
            .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
            // Lists
            .replace(/^\* (.*$)/gim, '<li>$1</li>')
            .replace(/^- (.*$)/gim, '<li>$1</li>')
            // Code blocks
            .replace(/```([\s\S]*?)```/g, '<pre class="bg-light p-2 rounded"><code>$1</code></pre>')
            // Inline code
            .replace(/`([^`]+)`/g, '<code class="bg-light px-1 rounded">$1</code>')
            // Links
            .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')
            // Line breaks
            .replace(/\n/g, '<br>');

        // Wrap lists
        html = html.replace(/(<li>.*<\/li>)+/g, '<ul class="mb-3">$&</ul>');

        return `<div class="changelog-content">${html}</div>`;
    }

    // Handle feedback form submission
    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitFeedbackBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

        const formData = new FormData(this);

        // Add CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('/feedback/submit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Feedback sent successfully!', 'success');
                this.reset();
            } else {
                showToast(data.message || 'Failed to send feedback', 'error');
            }
        })
        .catch(error => {
            console.error('Feedback submission error:', error);
            showToast('Failed to send feedback. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10000;
            min-width: 300px;
        `;
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }
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