#!/bin/bash

# Production Logout Fix Deployment Script
echo "🔧 Fixing logout functionality for production..."

# Clear all Laravel caches
echo "📦 Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Optimize for production (but don't cache routes to avoid issues)
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan view:cache

# Verify routes are correctly registered
echo "🔍 Verifying logout routes..."
php artisan route:list | grep logout

# Test permissions on key files
echo "🔐 Checking file permissions..."
ls -la routes/web.php
ls -la app/Http/Controllers/Auth/CustomLoginController.php

# Create a simple route test file for production debugging
cat > public/test-logout-routes.php << 'EOF'
<?php
// Simple test to check if logout routes are accessible
// Access this via: https://your-domain.com/test-logout-routes.php

header('Content-Type: text/plain');
echo "Laravel Logout Route Test\n";
echo "========================\n\n";

// Check if Laravel autoloader exists
if (!file_exists('../vendor/autoload.php')) {
    echo "❌ Laravel vendor directory not found\n";
    exit;
}

try {
    require_once '../vendor/autoload.php';
    $app = require_once '../bootstrap/app.php';
    
    echo "✅ Laravel loaded successfully\n";
    
    // Get the router
    $router = $app['router'];
    $routes = $router->getRoutes();
    
    echo "\nLogout routes found:\n";
    $found = false;
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'logout')) {
            echo "- " . implode('|', $route->methods()) . " /" . $route->uri() . " -> " . $route->getActionName() . "\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "❌ No logout routes found!\n";
    }
    
    echo "\nController test:\n";
    if (class_exists('App\Http\Controllers\Auth\CustomLoginController')) {
        echo "✅ CustomLoginController exists\n";
        $reflection = new ReflectionClass('App\Http\Controllers\Auth\CustomLoginController');
        if ($reflection->hasMethod('logout')) {
            echo "✅ logout method exists\n";
        } else {
            echo "❌ logout method missing\n";
        }
    } else {
        echo "❌ CustomLoginController not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed. Delete this file when done debugging.\n";
EOF

echo "✅ Created test file: public/test-logout-routes.php"

echo ""
echo "🚀 Deployment steps completed!"
echo ""
echo "📋 Next steps for production:"
echo "1. Upload the updated files to your production server"
echo "2. Run this script on your production server"
echo "3. Access https://your-domain.com/test-logout-routes.php to verify routes"
echo "4. Test the logout functionality"
echo "5. Delete the test file when done: rm public/test-logout-routes.php"
echo ""
echo "💡 If you still get 'Method Not Allowed':"
echo "   - Check your .htaccess file for rewrite rules"
echo "   - Verify your hosting provider supports Laravel routing"
echo "   - Check server error logs for more details"
