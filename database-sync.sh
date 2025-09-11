#!/bin/bash

# Database Synchronization Script
# This script helps keep local development database in sync with production

set -e

echo "🔄 Database Synchronization Script"
echo "=================================="

# Configuration
LOCAL_DB="supply_api"
BACKUP_DIR="resources/db"
PRODUCTION_DUMP="$BACKUP_DIR/production_backup.sql"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if MySQL is running
check_mysql() {
    if ! /opt/lampp/bin/mysql -u root -e "SELECT 1;" &> /dev/null; then
        print_error "MySQL is not running or not accessible"
        print_step "Starting MySQL with LAMPP..."
        sudo /opt/lampp/lampp startmysql
    fi
}

# Backup current local database
backup_local() {
    print_step "Backing up current local database..."
    mkdir -p "$BACKUP_DIR"
    /opt/lampp/bin/mysqldump -u root "$LOCAL_DB" > "$BACKUP_DIR/local_backup_$(date +%Y%m%d_%H%M%S).sql"
    print_success "Local database backed up"
}

# Import production database
import_production() {
    if [ ! -f "$PRODUCTION_DUMP" ]; then
        print_error "Production dump file not found: $PRODUCTION_DUMP"
        echo "Please place your production database export in: $PRODUCTION_DUMP"
        exit 1
    fi
    
    print_step "Dropping current local database..."
    /opt/lampp/bin/mysql -u root -e "DROP DATABASE IF EXISTS $LOCAL_DB;"
    
    print_step "Creating fresh database..."
    /opt/lampp/bin/mysql -u root -e "CREATE DATABASE $LOCAL_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    print_step "Importing production data..."
    /opt/lampp/bin/mysql -u root "$LOCAL_DB" < "$PRODUCTION_DUMP"
    
    print_success "Production database imported successfully"
}

# Run migrations
run_migrations() {
    print_step "Running pending migrations..."
    
    # Check if there are pending migrations
    if php artisan migrate:status | grep -q "Pending"; then
        print_warning "Found pending migrations, running them..."
        php artisan migrate --force
        print_success "Migrations completed"
    else
        print_success "No pending migrations found"
    fi
}

# Verify database structure
verify_structure() {
    print_step "Verifying database structure..."
    
    # Check if key tables exist
    TABLES=("users" "items" "requests" "categories")
    for table in "${TABLES[@]}"; do
        if /opt/lampp/bin/mysql -u root -e "DESCRIBE $LOCAL_DB.$table;" &> /dev/null; then
            print_success "✓ Table '$table' exists"
        else
            print_error "✗ Table '$table' missing"
        fi
    done
    
    # Check if workflow_status column exists
    if /opt/lampp/bin/mysql -u root -e "DESCRIBE $LOCAL_DB.requests;" | grep -q "workflow_status"; then
        print_success "✓ workflow_status column exists"
    else
        print_warning "⚠ workflow_status column missing (will be added by migrations)"
    fi
}

# Show usage
show_usage() {
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  sync        - Full synchronization (backup + import + migrate)"
    echo "  backup      - Backup current local database only"
    echo "  import      - Import production database only"
    echo "  migrate     - Run pending migrations only"
    echo "  verify      - Verify database structure"
    echo "  help        - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 sync                    # Full sync with production"
    echo "  $0 import                  # Import production data only"
    echo "  $0 migrate                 # Run migrations only"
}

# Main execution
main() {
    case "${1:-sync}" in
        "sync")
            check_mysql
            backup_local
            import_production
            run_migrations
            verify_structure
            print_success "🎉 Database synchronization completed!"
            ;;
        "backup")
            check_mysql
            backup_local
            ;;
        "import")
            check_mysql
            import_production
            ;;
        "migrate")
            run_migrations
            ;;
        "verify")
            check_mysql
            verify_structure
            ;;
        "help"|"-h"|"--help")
            show_usage
            ;;
        *)
            print_error "Unknown command: $1"
            show_usage
            exit 1
            ;;
    esac
}

# Run main function with all arguments
main "$@"
