<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'USTP Supply Office') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700|poppins:400,500,600,700|roboto:300,400,500,700" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    @auth
    <!-- Navbar CSS for authenticated users only -->
    <style>
        body {
            margin: 0;
            padding: 0;
            padding-top: 64px; /* Fixed navbar height */
        }
        
        .navbar-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1050 !important; /* Bootstrap's navbar z-index */
            width: 100% !important;
            height: 64px !important;
        }
        
        .content-wrapper {
            min-height: calc(100vh - 64px);
            padding: 0;
            margin: 0;
        }
        
        /* Fix for centered layouts */
        .h-100.d-flex.align-items-center {
            min-height: calc(100vh - 64px) !important;
        }
        
        /* Dashboard Card Hover Effects */
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
        }
        
        /* Smooth animations for stat cards */
        .card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        /* Enhanced gradient backgrounds */
        .bg-gradient {
            background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.8) 100%);
        }
        
        .bg-primary.bg-gradient {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .bg-success.bg-gradient {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        
        .bg-warning.bg-gradient {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }
        
        .bg-danger.bg-gradient {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        
        .bg-info.bg-gradient {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        
        /* Icon rotation on card hover */
        .hover-lift:hover .fas {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        
        /* Number animation */
        .hover-lift:hover h2 {
            color: #007bff !important;
            transition: color 0.3s ease;
        }
        
        /* Border radius enhancements */
        .card {
            border-radius: 12px;
        }
        
        /* Welcome card enhancements */
        .border-start {
            border-width: 4px !important;
            border-color: #ffc107 !important;
        }
        
        /* QR Scanner button animations */
        #start-scanner-btn {
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        #start-scanner-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
        }
    </style>
    @else
    <!-- Guest/Login page CSS -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        main {
            margin-top: 0 !important;
        }
        .min-h-screen {
            min-height: 100vh !important;
        }
    </style>
    @endauth
</head>
<body class="bg-light">
    @auth
            <!-- Navigation -->
            <div class="navbar-container">
                <nav class="navbar navbar-expand-lg navbar-dark shadow" style="background: linear-gradient(to right, #1a1851, #0d4a77);">
                    <div class="container-fluid">
                        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}" style="color: #fcb315; font-family: 'Montserrat';">
                            SIMS
                        </a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                                       href="{{ route('dashboard') }}" 
                                       style="{{ request()->routeIs('dashboard') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                        Dashboard
                                    </a>
                                </li>

                                @if(auth()->user()->role === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" 
                                           href="{{ route('users.index') }}"
                                           style="{{ request()->routeIs('users.*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Users
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}" 
                                           href="{{ route('items.index') }}"
                                           style="{{ request()->routeIs('items.*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Items
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" 
                                           href="{{ route('requests.manage') }}"
                                           style="{{ request()->routeIs('requests.*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Requests
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports*') || request()->routeIs('activity-logs*') ? 'active' : '' }}" 
                                           href="{{ route('reports') }}"
                                           style="{{ request()->routeIs('reports*') || request()->routeIs('activity-logs*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Reports
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role === 'office_head')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('items.browse') ? 'active' : '' }}" 
                                           href="{{ route('items.browse') }}"
                                           style="{{ request()->routeIs('items.browse') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Browse Items
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('office-head.requests*') ? 'active' : '' }}" 
                                           href="{{ route('office-head.requests') }}"
                                           style="{{ request()->routeIs('office-head.requests*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Office Requests
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('requests.my') ? 'active' : '' }}" 
                                           href="{{ route('requests.my') }}"
                                           style="{{ request()->routeIs('requests.my') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            My Requests
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}" 
                                           href="{{ route('reports') }}"
                                           style="{{ request()->routeIs('reports*') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Reports
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role === 'faculty')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('items.browse') ? 'active' : '' }}" 
                                           href="{{ route('items.browse') }}"
                                           style="{{ request()->routeIs('items.browse') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            Browse Items
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('requests.my') ? 'active' : '' }}" 
                                           href="{{ route('requests.my') }}"
                                           style="{{ request()->routeIs('requests.my') ? 'color: #fcb315 !important; border-bottom: 2px solid #fcb315;' : 'color: white;' }}">
                                            My Requests
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            <!-- User Menu -->
                            <div class="d-flex align-items-center">
                                <span class="text-white me-3 small">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- Page Heading -->
            @hasSection('header')
                <header class="bg-white shadow-sm">
                    <div class="container py-3">
                        @yield('header')
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="content-wrapper">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="container mt-3">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="container mt-3">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <span>{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        @else
            <!-- Guest Content -->
            <main>
                @yield('content')
            </main>
        @endauth

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    
    <script>
        // The mobile menu should now work with Bootstrap's built-in collapse functionality
        // No custom JavaScript needed as Bootstrap handles the toggle automatically
    </script>

    <!-- Additional Scripts Stack -->
    @stack('scripts')
</body>
</html>
