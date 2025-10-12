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
            <!-- Notifications Bell -->
            <div class="notification-bell position-relative me-3">
                <button class="btn btn-link position-relative p-2 notification-bell-btn" id="notification-toggle" title="Notifications">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill notification-badge d-none" id="notification-badge">
                        <span id="notification-count">0</span>
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown position-absolute bg-white border rounded shadow-lg d-none" id="notification-dropdown" style="right: 0; top: 100%; width: 350px; max-height: 400px; z-index: 1050; overflow: hidden;">
                    <div class="notification-header d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                        <h6 class="mb-0 fw-bold">Notifications</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-danger" id="delete-all-notifications">Delete All</button>
                            <button class="btn btn-sm btn-outline-primary" id="mark-all-read">Mark All Read</button>
                        </div>
                    </div>
                    
                    <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                        <div class="notification-item p-3 border-bottom text-center text-muted" id="no-notifications">
                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                            <div>No notifications</div>
                        </div>
                        
                        <!-- Notifications will be loaded here -->
                        <div id="notification-items"></div>
                    </div>
                    
                    <div class="notification-footer p-2 border-top bg-light text-center">
                        <a href="{{ route('notifications.index') }}" class="text-decoration-none small" id="view-all-notifications">View All Notifications</a>
                    </div>
                </div>
            </div>

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
        const dateString = `${month} ${day}, ${year}`;
        
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

// Notification System
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.getElementById('notification-toggle');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationBadge = document.getElementById('notification-badge');
    const notificationCount = document.getElementById('notification-count');
    const notificationItems = document.getElementById('notification-items');
    const noNotifications = document.getElementById('no-notifications');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const deleteAllBtn = document.getElementById('delete-all-notifications');
    const viewAllLink = document.getElementById('view-all-notifications');
    
    let isDropdownOpen = false;
    
    // Toggle notification dropdown
    notificationToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleDropdown();
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        markAllNotificationsAsRead();
    });
    
    // Delete all notifications
    deleteAllBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
            deleteAllNotifications();
        }
    });
    
    // View all notifications link
    viewAllLink.addEventListener('click', function(e) {
        closeDropdown();
    });
    
    function toggleDropdown() {
        if (isDropdownOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }
    
    function openDropdown() {
        notificationDropdown.classList.remove('d-none');
        isDropdownOpen = true;
        loadNotifications();
    }
    
    function closeDropdown() {
        notificationDropdown.classList.add('d-none');
        isDropdownOpen = false;
    }
    
    function updateNotificationCount(count) {
        if (count > 0) {
            notificationBadge.classList.remove('d-none');
            notificationCount.textContent = count > 99 ? '99+' : count;
        } else {
            notificationBadge.classList.add('d-none');
        }
    }
    
    function loadNotifications() {
        fetch('/notifications/data?limit=5', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count);
            renderNotifications(data.notifications.data);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
    }
    
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationItems.innerHTML = '';
            noNotifications.style.display = 'block';
            return;
        }
        
        noNotifications.style.display = 'none';
        
        const html = notifications.map(notification => {
            const isUnread = !notification.read_at;
            const unreadClass = isUnread ? 'bg-light' : '';
            const timeAgo = formatTimeAgo(new Date(notification.created_at));
            
            return `
                <div class="notification-item p-3 border-bottom ${unreadClass}" data-id="${notification.id}">
                    <div class="d-flex align-items-start">
                        <div class="notification-icon me-3">
                            <i class="${notification.icon} text-${notification.color} fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 fw-bold">${notification.title}</h6>
                                <small class="text-muted">${timeAgo}</small>
                            </div>
                            <p class="mb-2 small text-muted">${notification.message}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                ${notification.url ? `<a href="${notification.url}" class="btn btn-sm btn-outline-primary">View</a>` : ''}
                                <div class="d-flex gap-1">
                                    ${isUnread ? `<button class="btn btn-sm btn-link text-decoration-none mark-read-btn" data-id="${notification.id}">Mark Read</button>` : ''}
                                    @if(auth()->user()->isAdmin())
                                    <button class="btn btn-sm btn-link text-danger text-decoration-none delete-notification-btn" data-id="${notification.id}" title="Delete Notification">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        notificationItems.innerHTML = html;
        
        // Add event listeners for mark as read buttons
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-id');
                markNotificationAsRead(notificationId);
            });
        });
        
        // Add event listeners for delete buttons
        document.querySelectorAll('.delete-notification-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this notification?')) {
                    deleteNotification(notificationId);
                }
            });
        });
    }
    
    function deleteNotification(notificationId) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count);
            loadNotifications(); // Reload to update UI
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
    
    function markAllNotificationsAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count);
            loadNotifications(); // Reload to update UI
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }
    
    function deleteAllNotifications() {
        fetch('/notifications', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count);
            loadNotifications(); // Reload to update UI
        })
        .catch(error => {
            console.error('Error deleting all notifications:', error);
        });
    }
    
    function formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    }
    
    // Load notification count on page load
    loadNotifications();
    
    // Refresh notification count every 30 seconds
    setInterval(() => {
        fetch('/notifications/unread-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.unread_count);
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
    }, 30000);
});
</script>
