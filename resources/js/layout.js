/**
 * ==========================================
 * SUPPLY OFFICE - LAYOUT JAVASCRIPT
 * Dark Mode & Mobile Navigation
 * ==========================================
 */

// Dark Mode Toggle Functionality
function toggleDarkMode() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Add a subtle animation to the toggle button
    const toggleBtn = document.querySelector('.dark-mode-toggle');
    if (toggleBtn) {
        toggleBtn.style.transform = 'scale(0.9)';
        setTimeout(() => {
            toggleBtn.style.transform = 'scale(1)';
        }, 150);
    }
}

// Initialize theme on page load
function initializeTheme() {
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('theme');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const theme = savedTheme || systemTheme;
    
    html.setAttribute('data-theme', theme);
}

// Listen for system theme changes
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            const html = document.documentElement;
            html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
        }
    });
}

// Mobile sidebar toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}

// Close sidebar when clicking outside on mobile
function handleOutsideClick(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768 && sidebar) {
        if (!sidebar.contains(event.target) && !toggleBtn?.contains(event.target)) {
            sidebar.classList.remove('mobile-open');
        }
    }
}

// Handle window resize
function handleWindowResize() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove('mobile-open');
    }
}

// Initialize layout functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Layout JavaScript initialized');
    
    // Initialize theme
    initializeTheme();
    
    // Add event listeners
    document.addEventListener('click', handleOutsideClick);
    window.addEventListener('resize', handleWindowResize);
    
    // Test dark mode toggle button
    const darkModeBtn = document.querySelector('.dark-mode-toggle');
    if (darkModeBtn) {
        console.log('✅ Dark mode toggle button found');
    }
    
    // Test mobile toggle button
    const mobileToggleBtn = document.querySelector('.mobile-menu-toggle');
    if (mobileToggleBtn) {
        console.log('✅ Mobile toggle button found');
    }
});

// Utility functions for theme management
const ThemeManager = {
    getCurrentTheme: function() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    },
    
    setTheme: function(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    },
    
    toggleTheme: function() {
        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
        return newTheme;
    },
    
    isSystemDarkMode: function() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
};

// Mobile navigation utilities
const MobileNav = {
    open: function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.add('mobile-open');
        }
    },
    
    close: function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.remove('mobile-open');
        }
    },
    
    toggle: function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('mobile-open');
        }
    },
    
    isOpen: function() {
        const sidebar = document.getElementById('sidebar');
        return sidebar ? sidebar.classList.contains('mobile-open') : false;
    }
};

// Export for global access
window.ThemeManager = ThemeManager;
window.MobileNav = MobileNav;
