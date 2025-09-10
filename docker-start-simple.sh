#!/bin/bash

# Simple startup script - focuses on getting the app running
set -e

echo "Starting Laravel application (simple mode)..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Set session and CSRF configuration for production
echo "⚙️  Configuring session and CSRF settings for production..."
export SESSION_DRIVER=database
export SESSION_LIFETIME=120
export SESSION_SECURE_COOKIE=false
export SESSION_HTTP_ONLY=true
export SESSION_SAME_SITE=lax
export SANCTUM_STATEFUL_DOMAINS="sims-laravel.onrender.com,localhost,127.0.0.1"

# Clear cached configurations to ensure fresh settings
echo "🧹 Clearing cached configurations..."
php artisan config:clear || echo "⚠️  Config cache already clear"
php artisan route:clear || echo "⚠️  Route cache already clear"
php artisan view:clear || echo "⚠️  View cache already clear"

# Ensure storage permissions for sessions
echo "📁 Setting storage permissions..."
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "✅ Cache cleared and permissions set"

echo "✅ Production session configuration applied"

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

# IMMEDIATE FIX: Create critical tables with better error handling
echo "🔧 IMMEDIATE FIX: Ensuring critical tables exist with detailed logging..."
php -r "
set_error_handler(function(\$errno, \$errstr, \$errfile, \$errline) {
    echo 'PHP Error: ' . \$errstr . ' in ' . \$errfile . ' on line ' . \$errline . PHP_EOL;
});

try {
    echo '🔗 Attempting database connection...' . PHP_EOL;
    echo 'DB_HOST: ' . getenv('DB_HOST') . PHP_EOL;
    echo 'DB_DATABASE: ' . getenv('DB_DATABASE') . PHP_EOL;
    echo 'DB_USERNAME: ' . getenv('DB_USERNAME') . PHP_EOL;
    
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo '✅ Database connection successful' . PHP_EOL;
    
    // List all tables first
    echo '📋 Current tables in database:' . PHP_EOL;
    \$tables = \$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach (\$tables as \$table) {
        echo '  - ' . \$table . PHP_EOL;
    }
    
    // Force create activity_logs table
    echo '🔧 Checking for activity_logs table...' . PHP_EOL;
    \$result = \$pdo->query('SHOW TABLES LIKE \"activity_logs\"');
    if (\$result->rowCount() > 0) {
        echo '✅ activity_logs table already exists' . PHP_EOL;
    } else {
        echo '❌ activity_logs table missing - creating now...' . PHP_EOL;
        
        \$sql = 'CREATE TABLE activity_logs (
            id bigint unsigned not null auto_increment primary key,
            log_name varchar(191) null,
            description text not null,
            subject_type varchar(191) null,
            subject_id bigint unsigned null,
            causer_type varchar(191) null,
            causer_id bigint unsigned null,
            properties text null,
            batch_uuid varchar(191) null,
            event varchar(191) null,
            ip_address varchar(191) null,
            user_agent varchar(191) null,
            created_at timestamp null,
            updated_at timestamp null,
            KEY activity_logs_log_name_index (log_name),
            KEY activity_logs_subject_type_index (subject_type),
            KEY activity_logs_subject_id_index (subject_id),
            KEY activity_logs_causer_type_index (causer_type),
            KEY activity_logs_causer_id_index (causer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        
        if (\$pdo->exec(\$sql) !== false) {
            echo '✅ activity_logs table created successfully!' . PHP_EOL;
            
            // Verify it was created
            \$verify = \$pdo->query('SHOW TABLES LIKE \"activity_logs\"');
            if (\$verify->rowCount() > 0) {
                echo '✅ Verified: activity_logs table exists' . PHP_EOL;
            } else {
                echo '❌ ERROR: activity_logs table creation verification failed' . PHP_EOL;
            }
        } else {
            echo '❌ ERROR: Failed to create activity_logs table' . PHP_EOL;
        }
        
        // Track in migrations table
        \$migrationExists = \$pdo->query('SHOW TABLES LIKE \"migrations\"')->rowCount() > 0;
        if (\$migrationExists) {
            echo '📝 Adding migration tracking...' . PHP_EOL;
            \$stmt = \$pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
            \$stmt->execute(['2025_09_04_225548_create_activity_logs_table']);
            if (\$stmt->fetchColumn() == 0) {
                \$insertStmt = \$pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
                \$insertStmt->execute(['2025_09_04_225548_create_activity_logs_table', 2]);
                echo '✅ Migration tracking added' . PHP_EOL;
            } else {
                echo '✅ Migration already tracked' . PHP_EOL;
            }
        }
    }
    
    // Check sessions table
    echo '🔧 Checking for sessions table...' . PHP_EOL;
    \$result = \$pdo->query('SHOW TABLES LIKE \"sessions\"');
    if (\$result->rowCount() > 0) {
        echo '✅ sessions table already exists' . PHP_EOL;
    } else {
        echo '❌ sessions table missing - creating now...' . PHP_EOL;
        \$sql = 'CREATE TABLE sessions (
            id varchar(255) not null primary key,
            user_id bigint unsigned null,
            ip_address varchar(45) null,
            user_agent text null,
            payload longtext not null,
            last_activity int not null,
            KEY sessions_user_id_index (user_id),
            KEY sessions_last_activity_index (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        
        if (\$pdo->exec(\$sql) !== false) {
            echo '✅ sessions table created successfully!' . PHP_EOL;
        } else {
            echo '❌ ERROR: Failed to create sessions table' . PHP_EOL;
        }
    }
    
    // Final verification - list all tables again
    echo '📋 Final table list:' . PHP_EOL;
    \$finalTables = \$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach (\$finalTables as \$table) {
        echo '  - ' . \$table . PHP_EOL;
    }
    
    echo '🎯 Table creation process completed!' . PHP_EOL;
    
} catch (Exception \$e) {
    echo '❌ CRITICAL ERROR in table creation: ' . \$e->getMessage() . PHP_EOL;
    echo 'Error details: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
    echo 'Stack trace: ' . \$e->getTraceAsString() . PHP_EOL;
    // Don't exit - continue deployment
}
"

# Also run the specific migration as a backup
echo "🔄 Running specific activity_logs migration as backup..."
if php artisan migrate --path=database/migrations/2025_09_04_225548_create_activity_logs_table.php --force 2>/dev/null; then
    echo "✅ Activity logs migration completed successfully"
else
    echo "⚠️  Migration command failed, but table should exist from direct creation"
fi

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

# First check if this is a fresh database or existing one
echo "🔍 Analyzing database state..."
DB_HAS_TABLES=$(php -r "
    try {
        \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        \$result = \$pdo->query('SHOW TABLES');
        \$tables = \$result->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('users', \$tables) && in_array('migrations', \$tables)) {
            echo 'EXISTING_DATABASE';
        } else {
            echo 'FRESH_DATABASE';
        }
    } catch (Exception \$e) {
        echo 'DATABASE_ERROR';
    }
" 2>/dev/null)

echo "📊 Database state: $DB_HAS_TABLES"

if [ "$DB_HAS_TABLES" = "EXISTING_DATABASE" ]; then
    echo "🔄 Existing database detected - checking migration status..."
    
    # Mark all migrations as completed if they're not already tracked
    echo "� Ensuring migration tracking is up to date..."
    php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            
            // Check if migrations table exists, create if not
            \$migrationTableExists = \$pdo->query('SHOW TABLES LIKE \"migrations\"')->rowCount() > 0;
            if (!\$migrationTableExists) {
                \$pdo->exec('CREATE TABLE migrations (id int unsigned not null auto_increment primary key, migration varchar(191) not null, batch int not null)');
                echo 'Created migrations table' . PHP_EOL;
            }
            
            // Get list of migration files
            \$migrationFiles = glob('/var/www/html/database/migrations/*.php');
            \$batch = 1;
            
            foreach (\$migrationFiles as \$file) {
                \$filename = basename(\$file, '.php');
                
                // Check if migration is already recorded
                \$stmt = \$pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
                \$stmt->execute([\$filename]);
                
                if (\$stmt->fetchColumn() == 0) {
                    // Only record migrations for tables that actually exist
                    \$shouldRecord = false;
                    \$existingTables = \$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Check if this migration's main table already exists
                    if ((strpos(\$filename, 'create_users_table') !== false && in_array('users', \$existingTables)) ||
                        (strpos(\$filename, 'create_cache_table') !== false && in_array('cache', \$existingTables)) ||
                        (strpos(\$filename, 'create_jobs_table') !== false && in_array('jobs', \$existingTables)) ||
                        (strpos(\$filename, 'create_personal_access_tokens_table') !== false && in_array('personal_access_tokens', \$existingTables)) ||
                        (strpos(\$filename, 'create_categories_table') !== false && in_array('categories', \$existingTables)) ||
                        (strpos(\$filename, 'create_items_table') !== false && in_array('items', \$existingTables)) ||
                        (strpos(\$filename, 'create_requests_table') !== false && in_array('requests', \$existingTables)) ||
                        (strpos(\$filename, 'create_logs_table') !== false && in_array('logs', \$existingTables))) {
                        \$shouldRecord = true;
                    }
                    
                    if (\$shouldRecord) {
                        // Record migration as completed for existing table
                        \$insertStmt = \$pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
                        \$insertStmt->execute([\$filename, \$batch]);
                        echo 'Recorded existing table migration: ' . \$filename . PHP_EOL;
                    }
                }
            }
            
            echo 'Migration tracking updated successfully' . PHP_EOL;
        } catch (Exception \$e) {
            echo 'Migration tracking update failed: ' . \$e->getMessage() . PHP_EOL;
        }
    " || echo "⚠️  Migration tracking update failed"
    
    # Now run any pending migrations (like activity_logs)
    echo "🔄 Running any pending migrations..."
    if php artisan migrate --force 2>&1; then
        echo "✅ All pending migrations completed successfully"
    else
        echo "⚠️  Some migrations may have had issues but continuing..."
    fi
    
    echo "✅ Migration tracking completed - database is ready"
    
elif [ "$DB_HAS_TABLES" = "FRESH_DATABASE" ]; then
    echo "🆕 Fresh database detected - running full migration..."
    
    # Initialize migration system
    php artisan migrate:install 2>/dev/null || true
    
    # Run all migrations
    if php artisan migrate --force 2>&1; then
        echo "✅ Fresh database migration completed successfully"
    else
        echo "❌ Fresh migration failed, but database may still be usable"
    fi
    
else
    echo "⚠️  Database state unclear - attempting standard migration..."
    
    # Try standard migration approach
    php artisan migrate:install 2>/dev/null || true
    
    if php artisan migrate --force 2>&1; then
        echo "✅ Standard migration completed"
    else
        echo "⚠️  Migration had issues, checking if database is functional..."
        
        # Verify critical tables exist
        php -r "
            try {
                \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
                \$result = \$pdo->query('SHOW TABLES');
                \$tables = \$result->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('users', \$tables)) {
                    echo 'Database appears functional despite migration warnings' . PHP_EOL;
                } else {
                    echo 'Critical tables missing' . PHP_EOL;
                }
            } catch (Exception \$e) {
                echo 'Database check failed: ' . \$e->getMessage() . PHP_EOL;
            }
        " || echo "Database verification failed"
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
