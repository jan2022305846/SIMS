#!/bin/bash

# Quick Cache Clear Script for Render Production
echo "🔧 Clearing Laravel caches for production fix..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Re-cache for production
echo "⚡ Re-caching for production..."
php artisan config:cache
php artisan route:cache

echo "✅ Cache operations completed!"
echo "🚀 Ready for production deployment"