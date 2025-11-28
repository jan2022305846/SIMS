<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @guest
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">
    @endguest

    <title>{{ config('app.name')}}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logos/USTP Logo against Light Background.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logos/USTP Logo against Light Background.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700|poppins:400,500,600,700|roboto:300,400,500,700" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Dark Mode Script - Load BEFORE page renders to prevent flash -->
    <script>
        // Apply dark mode immediately if it's stored in localStorage
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    
    <!-- Page-specific CSS -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light @auth authenticated @else guest @endauth">
    @auth
        <!-- Include Sidebar Partial -->
        @include('partials.sidebar')
        
        <!-- Include Header Partial -->
        @include('partials.header')

        <!-- Main Content Area -->
        <main class="main-content">
            @yield('header')
            @yield('content')
        </main>

        <!-- Include Footer Partial -->
        @include('partials.footer')

    @else
        <!-- Guest Content (Login, Register, etc.) -->
        <main class="guest-main">
            @yield('content')
        </main>
    @endauth

    <!-- Bootstrap JavaScript -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    @auth
    <!-- Session Timeout Management -->
    <script>
        (function() {
            let inactivityTimer;
            let inactivityLimit = 2 * 60 * 1000; // Default: 2 minutes in milliseconds
            let lastActivity = Date.now();
            
            // Function to get session lifetime from server or use default
            function getSessionLifetime() {
                // Check if we have a stored session lifetime from login
                const storedLifetime = sessionStorage.getItem('session_lifetime');
                if (storedLifetime) {
                    return parseInt(storedLifetime) * 1000; // Convert to milliseconds
                }
                return inactivityLimit; // Default fallback
            }
            
            // Function to reset inactivity timer
            function resetInactivityTimer() {
                lastActivity = Date.now();
                inactivityLimit = getSessionLifetime();
                
                // Clear existing timers
                clearTimeout(inactivityTimer);
                
                // Set new inactivity timer (logout directly without warning)
                inactivityTimer = setTimeout(logoutNow, inactivityLimit);
            }
            
            // Function to logout immediately
            window.logoutNow = function() {
                window.location.href = '/logout';
            };
            
            // Activity event listeners
            const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
            activityEvents.forEach(event => {
                document.addEventListener(event, resetInactivityTimer, true);
            });
            
            // Start the inactivity timer
            resetInactivityTimer();
            
            // Periodic session refresh (every 30 seconds when active)
            setInterval(() => {
                if (Date.now() - lastActivity < inactivityLimit) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        fetch('/dashboard', {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                            }
                        }).catch(error => {
                            console.log('Session refresh failed:', error);
                        });
                    }
                }
            }, 30000); // 30 seconds
            
        })();
    </script>
    @endauth
    
    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
