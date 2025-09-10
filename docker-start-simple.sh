#!/bin/bash

# Simple startup script - focuses on getting the app running
set -e

echo "Starting Laravel application (simple mode)..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Check for Vite manifest and create a minimal one if missing
if [ ! -f "/var/www/html/public/build/manifest.json" ]; then
    echo "Vite manifest not found, creating minimal fallback..."
    mkdir -p /var/www/html/public/build
    echo '{}' > /var/www/html/public/build/manifest.json
    echo "Created fallback manifest.json"
fi

# Always try to run migrations in production
echo "Initializing database..."

# Wait for database to be ready
echo "Waiting for database connection..."
for i in {1..30}; do
    if php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            echo 'Database connection successful';
            exit(0);
        } catch (Exception \$e) {
            echo 'Database connection failed: ' . \$e->getMessage();
            exit(1);
        }
    " 2>/dev/null; then
        echo "✅ Database connection established!"
        break
    else
        echo "⏳ Waiting for database... (attempt $i/30)"
        sleep 2
    fi
    
    if [ $i -eq 30 ]; then
        echo "❌ Database connection timeout after 60 seconds"
        echo "Database config: HOST=${DB_HOST}, PORT=${DB_PORT}, DB=${DB_DATABASE}, USER=${DB_USERNAME}"
        exit 1
    fi
done

# Create database if it doesn't exist
echo "Ensuring database exists..."
php -r "
    try {
        \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`' . getenv('DB_DATABASE') . '\`');
        echo 'Database ' . getenv('DB_DATABASE') . ' ready';
    } catch (Exception \$e) {
        echo 'Failed to create database: ' . \$e->getMessage();
        exit(1);
    }
" || {
    echo "❌ Failed to ensure database exists"
    exit 1
}

# Force run migrations - this will create migration table if needed
echo "🔄 Running database migrations..."
php artisan migrate:install --force 2>/dev/null || true
php artisan migrate --force || {
    echo "❌ Migration failed, checking details..."
    php artisan migrate:status || true
    echo "Attempting to continue..."
}

# Check if migrations actually worked
echo "🔍 Verifying migrations..."
if php artisan migrate:status | grep -q "users"; then
    echo "✅ Users table migration confirmed"
else
    echo "❌ Users table migration failed - forcing fresh migration"
    php artisan migrate:refresh --force --seed || {
        echo "❌ Fresh migration also failed, but continuing..."
    }
fi

# Create admin user
echo "👤 Setting up admin user..."
php artisan db:seed --class=AdminUserSeeder --force || {
    echo "⚠️  Admin seeder failed, but continuing startup..."
}

# Clear caches in development, cache in production
if [ "$APP_DEBUG" = "true" ] || [ "$APP_ENV" != "production" ]; then
    echo "Development mode - clearing all caches..."
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
    php artisan cache:clear || true
else
    echo "Production mode - caching configuration..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "Laravel application setup completed!"

# Start Apache in foreground
echo "Starting Apache server..."
exec apache2-foreground
