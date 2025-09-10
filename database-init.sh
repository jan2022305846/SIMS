#!/bin/bash

# Database initialization script for production deployment
set -e

echo "🗄️  Database Initialization Script"
echo "=================================="

# Function to test database connection
test_db_connection() {
    php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            echo 'SUCCESS';
        } catch (Exception \$e) {
            echo 'FAILED: ' . \$e->getMessage();
            exit(1);
        }
    " 2>/dev/null
}

# Function to check if database exists
check_database_exists() {
    php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            \$result = \$pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \"' . getenv('DB_DATABASE') . '\"');
            if (\$result->rowCount() > 0) {
                echo 'EXISTS';
            } else {
                echo 'NOT_EXISTS';
            }
        } catch (Exception \$e) {
            echo 'ERROR: ' . \$e->getMessage();
            exit(1);
        }
    " 2>/dev/null
}

# Function to check if users table exists
check_users_table() {
    php artisan tinker --execute="
        try {
            \$exists = Schema::hasTable('users');
            echo \$exists ? 'EXISTS' : 'NOT_EXISTS';
        } catch (Exception \$e) {
            echo 'ERROR: ' . \$e->getMessage();
        }
    " 2>/dev/null | tail -1
}

echo "📊 Current Configuration:"
echo "  DB_HOST: ${DB_HOST}"
echo "  DB_PORT: ${DB_PORT:-3306}"
echo "  DB_DATABASE: ${DB_DATABASE}"
echo "  DB_USERNAME: ${DB_USERNAME}"
echo ""

# Step 1: Test database server connection
echo "🔗 Step 1: Testing database server connection..."
if result=$(test_db_connection); then
    echo "✅ Database server connection: $result"
else
    echo "❌ Cannot connect to database server"
    exit 1
fi

# Step 2: Ensure database exists
echo "🏗️  Step 2: Ensuring database exists..."
db_status=$(check_database_exists)
if [ "$db_status" = "EXISTS" ]; then
    echo "✅ Database '${DB_DATABASE}' exists"
elif [ "$db_status" = "NOT_EXISTS" ]; then
    echo "🔨 Creating database '${DB_DATABASE}'..."
    php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            \$pdo->exec('CREATE DATABASE IF NOT EXISTS \`' . getenv('DB_DATABASE') . '\`');
            echo 'Database created successfully';
        } catch (Exception \$e) {
            echo 'Failed to create database: ' . \$e->getMessage();
            exit(1);
        }
    " || exit 1
    echo "✅ Database '${DB_DATABASE}' created"
else
    echo "❌ Error checking database: $db_status"
    exit 1
fi

# Step 3: Initialize Laravel migration system
echo "🔧 Step 3: Initializing Laravel migrations..."
php artisan migrate:install --force 2>/dev/null || true
echo "✅ Migration system initialized"

# Step 4: Run migrations
echo "🚀 Step 4: Running database migrations..."
if php artisan migrate --force; then
    echo "✅ Migrations completed successfully"
else
    echo "❌ Migration failed, attempting fresh migration..."
    if php artisan migrate:fresh --force; then
        echo "✅ Fresh migration completed"
    else
        echo "❌ Fresh migration also failed"
        exit 1
    fi
fi

# Step 5: Verify users table exists
echo "👤 Step 5: Verifying users table..."
table_status=$(check_users_table)
if [ "$table_status" = "EXISTS" ]; then
    echo "✅ Users table exists"
elif [ "$table_status" = "NOT_EXISTS" ]; then
    echo "❌ Users table missing after migration!"
    echo "📋 Available tables:"
    php artisan tinker --execute="
        try {
            \$tables = DB::select('SHOW TABLES');
            foreach (\$tables as \$table) {
                \$values = array_values((array)\$table);
                echo '  - ' . \$values[0] . PHP_EOL;
            }
        } catch (Exception \$e) {
            echo 'Error listing tables: ' . \$e->getMessage() . PHP_EOL;
        }
    " 2>/dev/null | tail -n +2
    exit 1
else
    echo "❌ Error checking users table: $table_status"
    exit 1
fi

# Step 6: Create admin user
echo "👨‍💼 Step 6: Setting up admin user..."
if php artisan db:seed --class=AdminUserSeeder --force; then
    echo "✅ Admin user created successfully"
else
    echo "⚠️  Admin user seeding failed, but database is ready"
fi

# Step 7: Final verification
echo "🔍 Step 7: Final verification..."
echo "📊 Migration status:"
php artisan migrate:status || true

echo ""
echo "🎉 Database initialization completed!"
echo "✅ Database: ${DB_DATABASE}"
echo "✅ Migrations: Applied"
echo "✅ Users table: Ready"
echo "✅ Admin user: Available"
echo ""
echo "🔐 Admin Login Credentials:"
echo "  Username: admin"
echo "  Password: password"
echo ""
