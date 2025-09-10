#!/bin/bash

# Simple startup script - focuses on getting the app running
set -e

echo "Starting Laravel application (simple mode)..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Check if we should run migrations
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$APP_DEBUG" = "true" ]; then
    echo "Checking database and running migrations..."
    
    # Test database connection first
    if php artisan migrate:status >/dev/null 2>&1; then
        echo "Database connected successfully."
        
        # Run migrations if needed
        echo "Running migrations..."
        php artisan migrate --force || {
            echo "Migration failed, but continuing startup..."
        }
    else
        echo "Cannot connect to database or migrations table doesn't exist."
        echo "You may need to check your database configuration."
    fi
else
    echo "Skipping migrations (set RUN_MIGRATIONS=true to enable)."
fi

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
