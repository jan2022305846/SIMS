#!/bin/bash

# Quick fix to create missing activity_logs table
echo "🔧 QUICK FIX: Creating missing activity_logs table"

# Run the specific migration for activity_logs
echo "📋 Creating activity_logs table..."

php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    // Check if activity_logs table already exists
    \$result = \$pdo->query('SHOW TABLES LIKE \"activity_logs\"');
    if (\$result->rowCount() > 0) {
        echo '✅ activity_logs table already exists' . PHP_EOL;
        exit(0);
    }
    
    // Create activity_logs table manually
    \$sql = 'CREATE TABLE activity_logs (
        id bigint unsigned not null auto_increment primary key,
        log_name varchar(191) null,
        description text not null,
        subject_type varchar(191) null,
        subject_id bigint unsigned null,
        causer_type varchar(191) null,
        causer_id bigint unsigned null,
        properties json null,
        batch_uuid varchar(191) null,
        event varchar(191) null,
        ip_address varchar(191) null,
        user_agent varchar(191) null,
        created_at timestamp null,
        KEY activity_logs_log_name_index (log_name),
        KEY activity_logs_subject_type_index (subject_type),
        KEY activity_logs_subject_id_index (subject_id),
        KEY activity_logs_causer_type_index (causer_type),
        KEY activity_logs_causer_id_index (causer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    
    \$pdo->exec(\$sql);
    echo '✅ activity_logs table created successfully' . PHP_EOL;
    
    // Also create any other missing tables if needed
    \$missingTables = [];
    
    // Check for other common tables
    \$requiredTables = [
        'offices' => 'CREATE TABLE offices (
            id bigint unsigned not null auto_increment primary key,
            name varchar(191) not null,
            code varchar(50) not null unique,
            description text null,
            location varchar(191) null,
            office_head_id bigint unsigned null,
            created_at timestamp null,
            updated_at timestamp null
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        
        'item_scan_logs' => 'CREATE TABLE item_scan_logs (
            id bigint unsigned not null auto_increment primary key,
            item_id bigint unsigned not null,
            user_id bigint unsigned null,
            scan_type varchar(50) not null,
            location varchar(191) null,
            metadata json null,
            created_at timestamp null,
            updated_at timestamp null
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    ];
    
    foreach (\$requiredTables as \$tableName => \$createSQL) {
        \$result = \$pdo->query(\"SHOW TABLES LIKE '$tableName'\");
        if (\$result->rowCount() == 0) {
            \$pdo->exec(\$createSQL);
            echo \"✅ Created missing table: $tableName\" . PHP_EOL;
        }
    }
    
    echo '🎉 All required tables are now available' . PHP_EOL;
    
} catch (Exception \$e) {
    echo '❌ Error creating tables: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

# Update migration tracking
echo "📋 Updating migration tracking..."
php -r "
try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    
    // Record the activity_logs migration
    \$stmt = \$pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
    \$stmt->execute(['2025_09_04_225548_create_activity_logs_table']);
    
    if (\$stmt->fetchColumn() == 0) {
        \$insertStmt = \$pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        \$insertStmt->execute(['2025_09_04_225548_create_activity_logs_table', 2]);
        echo '📝 Recorded activity_logs migration in tracking' . PHP_EOL;
    }
    
    echo '✅ Migration tracking updated' . PHP_EOL;
} catch (Exception \$e) {
    echo '⚠️  Migration tracking update failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "🎯 Quick fix completed! Activity logging should now work."
