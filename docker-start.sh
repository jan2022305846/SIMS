#!/bin/bash

# Exit on any error
set -e

echo "Starting Laravel application setup..."

# Function to test database connection using PHP instead of nc
test_database_connection() {
    php -r "
    try {
        \$host = getenv('DB_HOST') ?: 'localhost';
        \$port = getenv('DB_PORT') ?: '3306';
        \$dbname = getenv('DB_DATABASE') ?: 'test';
        \$username = getenv('DB_USERNAME') ?: 'root';
        \$password = getenv('DB_PASSWORD') ?: '';
        
        \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$dbname\", \$username, \$password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo 'SUCCESS';
    } catch (Exception \$e) {
        echo 'FAILED: ' . \$e->getMessage();
        exit(1);
    }
    "
}

# Wait for database if DB_HOST is set and not localhost
if [ ! -z "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "Testing database connection to $DB_HOST:$DB_PORT..."
    
    # Try for up to 60 seconds
    max_attempts=12
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        echo "Database connection attempt $attempt/$max_attempts..."
        
        if result=$(test_database_connection 2>&1); then
            if [[ "$result" == *"SUCCESS"* ]]; then
                echo "Database connection successful!"
                break
            fi
        fi
        
        if [ $attempt -eq $max_attempts ]; then
            echo "Failed to connect to database after $max_attempts attempts."
            echo "Last error: $result"
            echo "Proceeding anyway - application will handle database errors gracefully."
            break
        fi
        
        echo "Database not ready yet, waiting 5 seconds..."
        sleep 5
        ((attempt++))
    done
else
    echo "Using local database or DB_HOST not set, skipping connection test."
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
if [ ! -z "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "Running database migrations..."
    
    # Try migrations but don't fail if they don't work
    if php artisan migrate --force 2>&1; then
        echo "Database migrations completed successfully."
    else
        echo "Database migrations failed, but continuing startup..."
        echo "You may need to run migrations manually later."
    fi
else
    echo "Skipping migrations - database not configured or using local database."
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel application setup completed!"

# Start Apache in foreground
echo "Starting Apache server..."
apache2-foreground
