# Development Database Synchronization Guide

## Problem Solved
This guide solves the database schema drift problem between local development and production environments that causes errors like:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'workflow_status' in 'where clause'
```

## Quick Start

### 1. Sync Your Local Database with Production
```bash
# Full synchronization (recommended for new developers)
./database-sync.sh sync

# Or step by step:
./database-sync.sh backup   # Backup current local DB
./database-sync.sh import   # Import production structure
./database-sync.sh migrate  # Apply new migrations
```

### 2. Regular Development Workflow
```bash
# Before starting development work
./database-sync.sh verify

# After creating new migrations
php artisan migrate

# Before committing (ensure migrations work on production-like schema)
./database-sync.sh sync
php artisan migrate:rollback --step=1  # Test rollback
php artisan migrate                     # Test migration again
```

## File Structure
```
resources/db/
├── production_backup.sql     # Latest production export (update this regularly)
├── local_backup_*.sql       # Automatic local backups
└── sql12798069.sql          # Original production export (keep for reference)

database/migrations/         # All migration files
database-sync.sh            # Synchronization script
```

## Database Sync Script Commands

| Command | Description |
|---------|-------------|
| `./database-sync.sh sync` | Full sync: backup → import → migrate |
| `./database-sync.sh backup` | Backup current local database |
| `./database-sync.sh import` | Import production database structure |
| `./database-sync.sh migrate` | Run pending migrations |
| `./database-sync.sh verify` | Check database structure |

## Production Update Process

### When You Deploy New Migrations:

1. **Local Testing:**
   ```bash
   ./database-sync.sh sync     # Start with production schema
   php artisan migrate         # Test your migrations
   ```

2. **Commit and Deploy:**
   ```bash
   git add .
   git commit -m "feat: Add new feature with migration"
   git push
   ```

3. **After Production Deployment:**
   - Download new production database export
   - Replace `resources/db/production_backup.sql`
   - Share with team: "Production updated, please run `./database-sync.sh sync`"

### For Team Members:

When someone updates production schema:
```bash
git pull                      # Get latest code
./database-sync.sh sync      # Sync with new production schema
```

## Best Practices

### ✅ Do This:
- Always test migrations on production-like schema before deploying
- Keep production database exports updated in `resources/db/`
- Use the sync script when switching between branches with different migrations
- Backup before major changes: `./database-sync.sh backup`

### ❌ Avoid This:
- Don't rely on `php artisan migrate:fresh` - it won't catch production schema differences
- Don't create migrations that assume columns exist without checking
- Don't ignore database structure differences between environments

## Migration Safety

### Production-Safe Migration Template:
```php
public function up(): void
{
    // Always check if column/table exists first
    if (!Schema::hasColumn('table_name', 'column_name')) {
        Schema::table('table_name', function (Blueprint $table) {
            $table->string('column_name')->nullable();
        });
    }
}

public function down(): void
{
    if (Schema::hasColumn('table_name', 'column_name')) {
        Schema::table('table_name', function (Blueprint $table) {
            $table->dropColumn('column_name');
        });
    }
}
```

## Troubleshooting

### "Column not found" errors in production:
1. Check if migration ran: `php artisan migrate:status`
2. Ensure `RUN_MIGRATIONS=true` in production environment
3. Manually run specific migration if needed

### Local database out of sync:
```bash
./database-sync.sh sync
```

### Migration conflicts:
```bash
php artisan migrate:status    # Check what's pending
./database-sync.sh import     # Reset to production state
php artisan migrate           # Apply only new migrations
```

### MySQL connection issues:
```bash
sudo /opt/lampp/lampp startmysql
./database-sync.sh verify
```

## Environment-Specific Configurations

### Local Development (.env.local):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supply_api
DB_USERNAME=root
DB_PASSWORD=
```

### Production (.env.production):
```env
DB_CONNECTION=mysql
DB_HOST=sql12.freesqldatabase.com
DB_PORT=3306
DB_DATABASE=sql12798069
DB_USERNAME=sql12798069
DB_PASSWORD=HekRxArZvq
RUN_MIGRATIONS=true
```

## Team Workflow Example

### Developer A creates new feature:
```bash
git checkout -b feature/new-reports
./database-sync.sh sync                    # Start with production schema
php artisan make:migration add_report_columns
# ... develop feature ...
php artisan migrate                         # Test migration
git commit -m "feat: Add new reports feature"
git push
```

### Developer B gets the changes:
```bash
git pull origin main
./database-sync.sh sync                    # Sync with latest production
php artisan migrate                         # Apply new migrations
```

This ensures everyone works with the same database structure and prevents production surprises! 🎉
