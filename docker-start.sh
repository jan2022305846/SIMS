#!/bin/bash

# Laravel Docker Startup Script for Production
set -e

echo "🚀 Starting Laravel application in Docker..."

# Wait for database to be ready (if DB_HOST is set)
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for database connection..."
    timeout=60
    count=0
    
    while ! nc -z "$DB_HOST" "${DB_PORT:-3306}" && [ $count -lt $timeout ]; do
        echo "   Database not ready, waiting... ($count/$timeout)"
        sleep 2
        count=$((count + 1))
    done
    
    if [ $count -eq $timeout ]; then
        echo "❌ Database connection timeout!"
        exit 1
    fi
    
    echo "✅ Database connection established!"
fi

# Set APP_KEY if not already set
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY not set, generating one..."
    export APP_KEY=$(php artisan key:generate --show)
    echo "✅ Generated APP_KEY"
fi

# Create storage directories and set permissions
echo "📁 Setting up storage directories..."
mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations if RUN_MIGRATIONS is true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🔄 Running database migrations..."
    php artisan migrate --force
    echo "✅ Migrations completed"
fi

# Clear and cache configuration for production
echo "🔧 Optimizing Laravel for production..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache configuration and routes for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Laravel optimization completed"

# Create a health check endpoint info
echo "🔍 Health check available at: /debug/health"
echo "ℹ️  Application info at: /debug/info"

# Start Apache in foreground
echo "🌐 Starting Apache web server..."
apache2-foreground