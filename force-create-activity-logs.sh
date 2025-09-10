#!/bin/bash

echo "🚨 FORCE CREATE ACTIVITY_LOGS TABLE"
echo "=================================="

# This script forcibly creates the activity_logs table
php -r "
echo '🔗 Connecting to database...' . PHP_EOL;

try {
    \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE') . ';port=' . (getenv('DB_PORT') ?: '3306'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo '✅ Connected to database: ' . getenv('DB_DATABASE') . PHP_EOL;
    
    // Drop existing table if it exists (force recreation)
    \$pdo->exec('DROP TABLE IF EXISTS activity_logs');
    echo '🗑️  Dropped existing activity_logs table if it existed' . PHP_EOL;
    
    // Create the table
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
    
    \$pdo->exec(\$sql);
    echo '✅ activity_logs table created!' . PHP_EOL;
    
    // Verify it exists
    \$result = \$pdo->query('SHOW TABLES LIKE \"activity_logs\"');
    if (\$result->rowCount() > 0) {
        echo '✅ VERIFIED: activity_logs table exists!' . PHP_EOL;
        
        // Show table structure
        echo '📋 Table structure:' . PHP_EOL;
        \$structure = \$pdo->query('DESCRIBE activity_logs')->fetchAll();
        foreach (\$structure as \$column) {
            echo '  ' . \$column['Field'] . ' (' . \$column['Type'] . ')' . PHP_EOL;
        }
    } else {
        echo '❌ ERROR: Table verification failed!' . PHP_EOL;
        exit(1);
    }
    
    // Update migrations table
    \$migrationExists = \$pdo->query('SHOW TABLES LIKE \"migrations\"')->rowCount() > 0;
    if (\$migrationExists) {
        // Remove old migration record
        \$pdo->exec('DELETE FROM migrations WHERE migration = \"2025_09_04_225548_create_activity_logs_table\"');
        
        // Add new migration record
        \$insertStmt = \$pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        \$insertStmt->execute(['2025_09_04_225548_create_activity_logs_table', 999]);
        echo '📝 Updated migration tracking' . PHP_EOL;
    }
    
    echo '🎉 SUCCESS: activity_logs table is ready!' . PHP_EOL;
    
} catch (Exception \$e) {
    echo '❌ ERROR: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

echo "🎯 Force creation completed!"
