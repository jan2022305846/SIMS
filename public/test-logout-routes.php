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
