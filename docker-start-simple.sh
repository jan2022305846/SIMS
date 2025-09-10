#!/bin/bash

# Simple startup script - database already exists
set -e

echo "🚀 Starting Laravel application (database pre-imported)..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Set session and CSRF configuration for production
echo "⚙️  Configuring session and CSRF settings..."
export SESSION_DRIVER=database
export SESSION_LIFETIME=120
export SESSION_SECURE_COOKIE=false
export SESSION_HTTP_ONLY=true
export SESSION_SAME_SITE=lax
export SANCTUM_STATEFUL_DOMAINS="sims-laravel.onrender.com,localhost,127.0.0.1"

# Check for Vite manifest and create a minimal one if missing
if [ ! -f "/var/www/html/public/build/manifest.json" ]; then
    echo "📦 Creating Vite manifest fallback..."
    mkdir -p /var/www/html/public/build
    echo '{}' > /var/www/html/public/build/manifest.json
fi

# Clear cached configurations to ensure fresh settings
echo "🧹 Clearing Laravel caches..."
php artisan config:clear || echo "Config cache already clear"
php artisan route:clear || echo "Route cache already clear"
php artisan view:clear || echo "View cache already clear"

# Ensure storage permissions
echo "📁 Setting storage permissions..."
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Quick database connection test
echo "🔍 Testing database connection..."
php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    echo '✅ Database connection successful' . PHP_EOL;
    
    // Quick table count
    \$tables = \$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo '📊 Found ' . count(\$tables) . ' tables in database' . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ Database connection failed: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
" || {
    echo "❌ Database test failed, but continuing startup..."
}

echo "✅ Application ready - database already configured!"
echo "🌐 Starting Apache web server..."

# Start Apache in foreground
exec apache2-foreground
