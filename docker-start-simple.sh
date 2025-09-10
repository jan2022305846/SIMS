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

# Handle database migrations with better error handling
echo "🔄 Running database migrations..."

# Check if migrations table exists and create if needed
php artisan migrate:install 2>/dev/null || true

# Get current migration status
echo "📊 Checking migration status..."
MIGRATION_STATUS=$(php artisan migrate:status --no-interaction 2>&1 || echo "FAILED")

if echo "$MIGRATION_STATUS" | grep -q "No migrations found" || echo "$MIGRATION_STATUS" | grep -q "FAILED"; then
    echo "🆕 No migrations found or status check failed, running fresh migration..."
    if php artisan migrate:fresh --force 2>&1; then
        echo "✅ Fresh migration completed successfully"
    else
        echo "❌ Fresh migration failed, attempting regular migration..."
        php artisan migrate --force 2>&1 || {
            echo "❌ All migration attempts failed"
            echo "🔍 Current database state:"
            php -r "
                try {
                    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
                    \$tables = \$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                    echo 'Tables: ' . implode(', ', \$tables) . PHP_EOL;
                } catch (Exception \$e) {
                    echo 'Cannot check database state: ' . \$e->getMessage() . PHP_EOL;
                }
            " || echo "Database check failed"
        }
    fi
elif echo "$MIGRATION_STATUS" | grep -q "users.*DONE\|users.*✓"; then
    echo "✅ Migrations appear to be completed already"
else
    echo "🔄 Running pending migrations..."
    if php artisan migrate --force 2>&1; then
        echo "✅ Migrations completed successfully"
    else
        echo "⚠️  Migration issues detected, but continuing..."
    fi
fi

# Verify critical tables exist
echo "🔍 Verifying critical database tables..."
php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    // Check if users table exists
    \$result = \$pdo->query('SHOW TABLES LIKE \"users\"');
    if (\$result->rowCount() > 0) {
        echo '✅ Users table exists' . PHP_EOL;
        
        // Check users table structure
        \$columns = \$pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_COLUMN);
        echo '📋 Users table columns: ' . implode(', ', \$columns) . PHP_EOL;
        
        \$hasSchoolId = in_array('school_id', \$columns);
        \$hasRole = in_array('role', \$columns);
        
        if (\$hasSchoolId && \$hasRole) {
            echo '✅ Users table has all required columns' . PHP_EOL;
        } else {
            echo '⚠️  Users table missing some columns (school_id: ' . (\$hasSchoolId ? 'yes' : 'no') . ', role: ' . (\$hasRole ? 'yes' : 'no') . ')' . PHP_EOL;
        }
    } else {
        echo '❌ Users table does not exist!' . PHP_EOL;
        exit(1);
    }
} catch (Exception \$e) {
    echo '❌ Database verification failed: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
" || {
    echo "❌ Database verification failed, but continuing..."
}

# Create admin user with better error handling
echo "👤 Setting up admin user..."
if php artisan db:seed --class=AdminUserSeeder --force 2>&1; then
    echo "✅ Admin user created successfully"
else
    echo "⚠️  Admin seeder failed, attempting manual admin creation..."
    
    # Try to create admin user manually if seeder fails
    php -r "
    try {
        \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        
        // Check if admin user already exists
        \$stmt = \$pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        \$stmt->execute(['admin']);
        
        if (\$stmt->fetchColumn() == 0) {
            // Get table columns to build appropriate insert
            \$columns = \$pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_COLUMN);
            
            \$data = [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@ustp.edu.ph',
                'password' => password_hash('password', PASSWORD_DEFAULT)
            ];
            
            if (in_array('school_id', \$columns)) \$data['school_id'] = 'ADMIN001';
            if (in_array('role', \$columns)) \$data['role'] = 'admin';
            if (in_array('department', \$columns)) \$data['department'] = 'Supply Office';
            if (in_array('created_at', \$columns)) \$data['created_at'] = date('Y-m-d H:i:s');
            if (in_array('updated_at', \$columns)) \$data['updated_at'] = date('Y-m-d H:i:s');
            
            \$placeholders = ':' . implode(', :', array_keys(\$data));
            \$columns_str = implode(', ', array_keys(\$data));
            
            \$stmt = \$pdo->prepare(\"INSERT INTO users (\$columns_str) VALUES (\$placeholders)\");
            \$stmt->execute(\$data);
            
            echo '✅ Admin user created manually' . PHP_EOL;
        } else {
            echo '✅ Admin user already exists' . PHP_EOL;
        }
    } catch (Exception \$e) {
        echo '❌ Manual admin creation failed: ' . \$e->getMessage() . PHP_EOL;
    }
    " || echo "❌ All admin user creation attempts failed"
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
