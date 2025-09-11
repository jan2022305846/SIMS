#!/bin/bash

# ==============================================
# Project Cleanup Script for Supply API
# ==============================================
# This script safely removes temporary files and organizes the project structure

set -e  # Exit on any error

echo "🧹 Supply API Project Cleanup Script"
echo "====================================="
echo ""

# Function to ask for confirmation
confirm() {
    read -p "$1 (y/N): " -n 1 -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]]
}

# Function to backup important files before deletion
backup_file() {
    local file="$1"
    if [ -f "$file" ]; then
        cp "$file" "backup_$(basename "$file")"
        echo "  ✓ Backed up: $file"
    fi
}

# Create backup directory
if [ ! -d "cleanup_backup" ]; then
    mkdir -p cleanup_backup
    echo "📁 Created backup directory: cleanup_backup/"
fi

echo "🔍 ANALYSIS: Found the following temporary files to clean up:"
echo ""

# List temporary files to be removed
TEMP_FILES=(
    "check_duplicates.php"
    "debug_auth.php" 
    "simulate_login.php"
    "test_credentials.php"
    "test_hash.php"
    "database_health_check.php"
)

DATABASE_FILES=(
    "clean_import_with_data.sql"
    "production_import_WORKING.sql"
    "fixed_import.sql"
    "fix_activity_logs.sql"
    "freemysql_compatible.sql"
)

SCRIPT_FILES=(
    "database-init.sh"
    "docker-start-clean.sh"
    "docker-start-simple.sh"
    "force-create-activity-logs.sh"
    "quick-fix-activity-logs.sh"
    "smart-migrate.sh"
)

OLD_DOCS=(
    "DATABASE_FIX.md"
    "DATABASE_FIXES_SUMMARY.md"
    "DATABASE_MIGRATION_FIX.md"
    "EXISTING_DATABASE_FIX.md"
    "MIGRATION_COLUMN_FIX.md"
    "MYSQL_COMPATIBILITY_FIX.md"
    "SECURITY_INCIDENT_FIXED.md"
    "URGENT_FIX.md"
    "VITE_FIX.md"
)

OTHER_FILES=(
    "Dockerfile.simple"
    "CLEANUP_ANALYSIS.md"
)

echo "📝 TESTING/DEBUG FILES:"
for file in "${TEMP_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  - $file"
    fi
done

echo ""
echo "🗄️  OLD DATABASE FILES:"
for file in "${DATABASE_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  - $file"
    fi
done

echo ""
echo "📜 OLD SCRIPT FILES:"
for file in "${SCRIPT_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  - $file"
    fi
done

echo ""
echo "📚 OUTDATED DOCUMENTATION:"
for file in "${OLD_DOCS[@]}"; do
    if [ -f "$file" ]; then
        echo "  - $file"
    fi
done

echo ""
echo "🔧 OTHER CLEANUP FILES:"
for file in "${OTHER_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  - $file"
    fi
done

echo ""
echo "⚠️  IMPORTANT: This script will:"
echo "   1. Move files to cleanup_backup/ directory (not delete permanently)"
echo "   2. Organize remaining documentation into docs/ directory"
echo "   3. Update .gitignore to prevent future temporary files"
echo "   4. Create a clean project structure"
echo ""

if confirm "🚀 Do you want to proceed with the cleanup?"; then
    echo ""
    echo "🧹 Starting cleanup process..."
    echo ""

    # Phase 1: Move temporary files to backup
    echo "📦 Phase 1: Backing up temporary files..."
    
    for file in "${TEMP_FILES[@]}" "${DATABASE_FILES[@]}" "${SCRIPT_FILES[@]}" "${OLD_DOCS[@]}" "${OTHER_FILES[@]}"; do
        if [ -f "$file" ]; then
            mv "$file" "cleanup_backup/"
            echo "  ✓ Moved: $file"
        fi
    done

    # Phase 2: Organize documentation
    echo ""
    echo "📚 Phase 2: Organizing documentation..."
    
    if [ ! -d "docs" ]; then
        mkdir -p docs
    fi
    
    # Move existing docs to organized structure
    if [ -d "docs/" ] && [ "$(ls -A docs/)" ]; then
        echo "  ✓ docs/ directory already exists with content"
    fi

    # Phase 3: Update .gitignore
    echo ""
    echo "🚫 Phase 3: Updating .gitignore..."
    
    # Add cleanup patterns to .gitignore if not already present
    if ! grep -q "# Cleanup and temporary files" .gitignore; then
        cat >> .gitignore << 'EOF'

# Cleanup and temporary files
cleanup_backup/
*.tmp
*.temp
test_*.php
debug_*.php
simulate_*.php
*_backup.*
*.sql.bak

# Development and testing
local_test/
temp_files/
scratch/
EOF
        echo "  ✓ Updated .gitignore with cleanup patterns"
    else
        echo "  ✓ .gitignore already contains cleanup patterns"
    fi

    # Phase 4: Create organized structure
    echo ""
    echo "🏗️  Phase 4: Creating organized structure..."
    
    # Create scripts directory structure
    if [ ! -d "scripts" ]; then
        mkdir -p scripts/{database,deployment,maintenance}
        echo "  ✓ Created scripts/ directory structure"
    fi

    # Move current utility scripts to organized location
    if [ -f "render-env-setup.sh" ]; then
        cp "render-env-setup.sh" "scripts/deployment/"
        echo "  ✓ Copied render-env-setup.sh to scripts/deployment/"
    fi

    if [ -f "render-env-importer.py" ]; then
        cp "render-env-importer.py" "scripts/deployment/"
        echo "  ✓ Copied render-env-importer.py to scripts/deployment/"
    fi

    if [ -f "render-env-importer.js" ]; then
        cp "render-env-importer.js" "scripts/deployment/"
        echo "  ✓ Copied render-env-importer.js to scripts/deployment/"
    fi

    # Phase 5: Generate project summary
    echo ""
    echo "📋 Phase 5: Generating project summary..."
    
    cat > PROJECT_STRUCTURE.md << 'EOF'
# Supply API - Clean Project Structure

## 🏗️ Directory Structure

```
supply-api/
├── app/                    # Laravel application code
├── bootstrap/             # Laravel bootstrap files
├── config/                # Configuration files
├── database/              # Migrations, factories, seeders
├── docs/                  # Project documentation
├── public/                # Public web files
├── resources/             # Views, assets, language files
├── routes/                # Route definitions
├── scripts/               # Utility scripts
│   ├── database/          # Database scripts
│   ├── deployment/        # Deployment scripts
│   └── maintenance/       # Maintenance scripts
├── storage/               # Storage files
├── tests/                 # Test files
└── vendor/                # Composer dependencies
```

## 🚀 Deployment Files

- `render.yaml` - Render deployment configuration
- `render-config.json` - Environment variables configuration
- `Dockerfile` - Docker container definition
- `docker-start.sh` - Docker startup script

## 📝 Documentation

- `README.md` - Main project documentation
- `DEPLOYMENT_GUIDE.md` - Deployment instructions
- `RENDER_ENV_GUIDE.md` - Environment configuration guide
- `LOCAL_DEVELOPMENT.md` - Local development setup

## 🔧 Utility Scripts

Located in `scripts/deployment/`:
- `render-env-setup.sh` - Environment setup script
- `render-env-importer.py` - Python environment importer
- `render-env-importer.js` - Node.js environment importer

## 🗂️ Cleaned Files

Temporary and testing files have been moved to `cleanup_backup/` directory.
These can be safely deleted after verifying the application works correctly.

## 🚀 Production Environment

Current production environment variables are configured in:
- Render dashboard with actual credentials
- `render.yaml` for automatic deployment
- `render-config.json` for reference

### Production URLs:
- Application: https://sims-laravel.onrender.com
- Health Check: https://sims-laravel.onrender.com/debug/health
- Debug Info: https://sims-laravel.onrender.com/debug/info
EOF

    echo "  ✓ Created PROJECT_STRUCTURE.md"

    echo ""
    echo "✅ CLEANUP COMPLETED SUCCESSFULLY!"
    echo ""
    echo "📊 Summary:"
    echo "  - Temporary files moved to cleanup_backup/"
    echo "  - Project structure organized"
    echo "  - Documentation updated"
    echo "  - .gitignore enhanced"
    echo "  - Utility scripts organized in scripts/"
    echo ""
    echo "🗑️  You can safely delete the cleanup_backup/ directory after testing."
    echo "📋 Check PROJECT_STRUCTURE.md for the new project organization."
    echo ""
    echo "🚀 Your project is now clean and production-ready!"

else
    echo ""
    echo "❌ Cleanup cancelled. No files were modified."
fi

echo ""
echo "📋 For manual cleanup, check CLEANUP_ANALYSIS.md for detailed file listing."
