#!/bin/bash

# Exit on any error
set -e

echo "Starting Laravel application setup..."

# Wait for database if DB_HOST is set
if [ ! -z "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "Waiting for database connection..."
    timeout 60s bash -c 'until nc -z $DB_HOST $DB_PORT; do echo "Waiting for database..."; sleep 2; done'
    echo "Database is ready!"
fi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache configuration only if not in debug mode
if [ "$APP_DEBUG" != "true" ]; then
    echo "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Running in debug mode - clearing caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Run database migrations (be careful with this in production)
echo "Running database migrations..."
php artisan migrate --force

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel application setup completed!"

# Start Apache in foreground
echo "Starting Apache server..."
apache2-foreground
