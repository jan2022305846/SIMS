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

# Check if we need to populate the database with initial data
echo "🌱 Checking if database needs to be seeded..."
php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    // Check if users table has any data
    \$userCount = \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo 'Found ' . \$userCount . ' users in database' . PHP_EOL;
    
    if (\$userCount == 0) {
        echo '🌱 No users found - database needs seeding' . PHP_EOL;
        exit(1);
    } else {
        echo '✅ Users exist - skipping seeding' . PHP_EOL;
        exit(0);
    }
} catch (Exception \$e) {
    echo '⚠️  Unable to check user count: ' . \$e->getMessage() . PHP_EOL;
    echo '🌱 Will attempt seeding to be safe' . PHP_EOL;
    exit(1);
}
"

# Run seeders if database is empty
if [ $? -eq 1 ]; then
    echo "🌱 Running database seeders..."
    
    # Run specific seeders in order
    echo "👤 Creating admin users..."
    if php artisan db:seed --class=AdminUserSeeder --force; then
        echo "✅ Admin users created successfully"
        echo "📋 Login credentials:"
        echo "   Username: admin"
        echo "   Password: password"
        echo "   Email: admin@ustp.edu.ph"
    else
        echo "⚠️  AdminUserSeeder failed, but continuing..."
    fi
    
    echo "🏢 Creating sample categories..."
    if php artisan db:seed --class=CategoryTypeSeeder --force; then
        echo "✅ Categories created successfully"
    else
        echo "⚠️  CategoryTypeSeeder failed, but continuing..."
    fi
    
    echo "📦 Creating sample data..."
    if php artisan db:seed --class=TestDataSeeder --force; then
        echo "✅ Sample data created successfully"
    else
        echo "⚠️  TestDataSeeder failed, but continuing..."
    fi
    
    echo "🎉 Database seeding completed!"
else
    echo "✅ Database already populated - skipping seeders"
fi

echo "✅ Application ready - database already configured!"
echo "🌐 Starting Apache web server..."

# Start Apache in foreground
exec apache2-foreground
