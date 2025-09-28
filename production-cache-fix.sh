#!/bin/bash

# Production Cache Fix Script for Render
# This script clears all Laravel caches and runs database migrations

set -e

echo "🔧 Applying production fixes for faculty requests..."

# Run database migrations to ensure schema is up to date
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear all caches to ensure SESSION_DOMAIN=null takes effect
echo "📦 Clearing all Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear additional caches that might be affected
echo "🧹 Clearing additional caches..."
php artisan event:clear
php artisan optimize:clear

# Re-cache for production performance
echo "⚡ Re-caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Production fixes applied successfully!"
echo "🚀 Faculty request submissions should now work"
echo "🔍 Check application logs for successful request creation"