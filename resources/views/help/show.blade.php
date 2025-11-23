@extends('layouts.app')

@section('title', $helpContent['title'] . ' - Help')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('help.index') }}">Help</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $helpContent['title'] }}</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="mb-2">{{ $helpContent['title'] }}</h2>
                            <p class="text-muted mb-0">{{ $helpContent['description'] }}</p>
                            
                            <!-- Tags -->
                            <div class="mt-3">
                                @foreach($helpContent['tags'] as $tag)
                                <span class="badge bg-light text-dark me-1">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('help.index') }}" class="btn btn-outline-secondary mb-2">
                                <i class="fas fa-arrow-left me-1"></i> Back to Help
                            </a>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="shareHelp()">
                                    <i class="fas fa-share me-1"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="row">
                <div class="col-lg-9">
                    <!-- Main Content -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            @foreach($helpContent['content'] as $section)
                                @if($section['type'] === 'text')
                                <div class="mb-4">
                                    <p class="fs-6 lh-lg">{{ $section['content'] }}</p>
                                </div>
                                
                                @elseif($section['type'] === 'image')
                                <div class="mb-4 text-center">
                                    <img src="{{ $section['src'] }}" alt="{{ $section['alt'] }}" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                                    <div class="mt-2">
                                        <small class="text-muted">{{ $section['alt'] }}</small>
                                    </div>
                                </div>
                                
                                @elseif($section['type'] === 'steps')
                                <div class="mb-4">
                                    <h5 class="mb-3">{{ $section['title'] }}</h5>
                                    <div class="list-group">
                                        @foreach($section['steps'] as $index => $step)
                                        <div class="list-group-item border-0 px-0">
                                            <div class="d-flex align-items-start">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; min-width: 30px;">
                                                    <small class="fw-bold">{{ $index + 1 }}</small>
                                                </div>
                                                <div class="pt-1">
                                                    <p class="mb-0">{{ $step }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                @elseif($section['type'] === 'warning')
                                <div class="alert alert-warning mb-4" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                                        <div>
                                            <strong>Important:</strong> {{ $section['content'] }}
                                        </div>
                                    </div>
                                </div>
                                
                                @elseif($section['type'] === 'info')
                                <div class="alert alert-info mb-4" role="alert">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-info-circle me-3 mt-1"></i>
                                        <div>
                                            {{ $section['content'] }}
                                        </div>
                                    </div>
                                </div>
                                
                                @elseif($section['type'] === 'tips')
                                <div class="mb-4">
                                    <h5 class="mb-3">{{ $section['title'] }}</h5>
                                    <div class="bg-light rounded p-3">
                                        @foreach($section['tips'] as $tip)
                                        <div class="d-flex align-items-start mb-2">
                                            <i class="fas fa-lightbulb text-warning me-2 mt-1"></i>
                                            <small>{{ $tip }}</small>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                @elseif($section['type'] === 'list')
                                <div class="mb-4">
                                    <h5 class="mb-3">{{ $section['title'] }}</h5>
                                    <ul class="list-group list-group-flush">
                                        @foreach($section['items'] as $item)
                                        <li class="list-group-item border-0 px-0">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ $item }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-3">
                    <!-- Quick Navigation -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                On This Page
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="pageNavigation">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Related Topics -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-link me-2"></i>
                                Related Topics
                            </h6>
                        </div>
                        <div class="card-body">
                            @php
                                $relatedTopics = [
                                    'dashboard-overview' => ['navigation', 'user-profile'],
                                    'add-item' => ['edit-item', 'categories', 'stock-management'],
                                    'create-request' => ['track-request', 'request-status'],
                                    'process-requests' => ['approval-workflow']
                                ];
                                $topicKey = isset($topic) ? $topic : '';
                                $currentRelated = array_key_exists($topicKey, $relatedTopics) ? $relatedTopics[$topicKey] : [];
                            @endphp
                            
                            @if(count($currentRelated) > 0)
                                @foreach($currentRelated as $relatedTopic)
                                <a href="{{ route('help.show', $relatedTopic) }}" class="d-block text-decoration-none p-2 rounded hover-bg-light mb-1">
                                    <i class="fas fa-chevron-right me-2 text-muted small"></i>
                                    <small>{{ ucwords(str_replace('-', ' ', $relatedTopic)) }}</small>
                                </a>
                                @endforeach
                            @else
                                <small class="text-muted">No related topics available</small>
                            @endif
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment me-2"></i>
                                Was this helpful?
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="btn-group mb-3" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="submitFeedback('yes')">
                                    <i class="fas fa-thumbs-up me-1"></i> Yes
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="submitFeedback('no')">
                                    <i class="fas fa-thumbs-down me-1"></i> No
                                </button>
                            </div>
                            <div>
                                <textarea class="form-control" rows="3" placeholder="Additional feedback (optional)" id="feedbackText"></textarea>
                                <button class="btn btn-sm btn-primary mt-2" onclick="submitDetailedFeedback()">Submit Feedback</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .d-print-none {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .col-lg-3 {
        display: none !important;
    }
    
    .col-lg-9 {
        width: 100% !important;
        max-width: 100% !important;
    }
}

.hover-bg-light:hover {
    background-color: #f8f9fa !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    generatePageNavigation();
});

function generatePageNavigation() {
    const headings = document.querySelectorAll('.card-body h5');
    const navigation = document.getElementById('pageNavigation');
    
    if (headings.length === 0) {
        navigation.innerHTML = '<div class="p-3 text-muted small">No sections found</div>';
        return;
    }
    
    let navHtml = '';
    headings.forEach((heading, index) => {
        const id = `section-${index}`;
        heading.id = id;
        
        navHtml += `
            <a href="#${id}" class="list-group-item list-group-item-action border-0 py-2">
                <small>${heading.textContent}</small>
            </a>
        `;
    });
    
    navigation.innerHTML = navHtml;
}

function shareHelp() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $helpContent["title"] }} - Help',
            text: '{{ $helpContent["description"] }}',
            url: window.location.href
        });
    } else {
        // Fallback: Copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            // Show temporary notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success';
            toast.innerHTML = '<i class="fas fa-check me-2"></i>URL copied to clipboard!';
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        });
    }
}

function submitFeedback(rating) {
    // Here you would typically send the feedback to your server
    console.log('Feedback:', rating);
    
    // Show thank you message
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 m-3 alert alert-info';
    toast.innerHTML = '<i class="fas fa-heart me-2"></i>Thank you for your feedback!';
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function submitDetailedFeedback() {
    const feedback = document.getElementById('feedbackText').value;
    
    // Here you would send detailed feedback to your server
    console.log('Detailed feedback:', feedback);
    
    // Clear the textarea and show thank you
    document.getElementById('feedbackText').value = '';
    submitFeedback('detailed');
}

// Smooth scrolling for navigation links
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});
</script>

@endsection