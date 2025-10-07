{{-- Header Component --}}
<div class="header-container">
    <div class="header-content">
        <div class="header-left">
            <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="header-brand-section">
                <!-- USTP Logo - Light Mode -->
                <img src="{{ asset('logos/USTP Logo against Light Background.png') }}"
                     alt="USTP Logo"
                     class="ustp-logo ustp-logo-light"
                     style="max-width: 60px; height: auto; margin-right: 15px;">

                <!-- USTP Logo - Dark Mode -->
                <img src="{{ asset('logos/USTP Logo against Dark Background.png') }}"
                     alt="USTP Logo"
                     class="ustp-logo ustp-logo-dark"
                     style="max-width: 60px; height: auto; margin-right: 15px; display: none;">

                <a href="{{ route('dashboard') }}" class="header-brand">SIMS</a>
                <h1 class="header-title">Supply Office Inventory Management System</h1>
            </div>
        </div>
        <div class="header-right">
            <!-- Dark Mode Toggle -->
            <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                <div class="toggle-icon">
                    <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </div>
            </button>
            <!-- User Info Display -->              
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <span class="user-role">{{ ucfirst(Auth::user()->role) }}</span>
            </div>
            <!-- Time and Date Display -->
            <div class="time-date-display">
                <div class="current-time" id="current-time">00:00:00</div>
                <div class="current-date" id="current-date">Jan,01,2025</div>
            </div>
        </div>
    </div>
</div>

{{-- Time and Date Update Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTimeDate() {
        const now = new Date();
        
        // Format time as hh:mm:ss AM/PM (12-hour format)
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        // Convert to 12-hour format
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
        
        // Format date as M,D,Y (e.g., Sep,17,2025)
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = monthNames[now.getMonth()];
        const day = String(now.getDate()).padStart(2, '0');
        const year = now.getFullYear();
        const dateString = `${month} ${day} ${year}`;
        
        // Update the display elements
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');
        
        if (timeElement) timeElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }
    
    // Update immediately and then every second
    updateTimeDate();
    setInterval(updateTimeDate, 1000);
});
</script>
