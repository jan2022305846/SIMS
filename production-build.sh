#!/bin/bash

echo "🏗️  Building USTP Supply Office for Production"
echo "=============================================="

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo "📦 Installing Node.js dependencies..."
    npm install
fi

if [ ! -d "vendor" ]; then
    echo "🎼 Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Build assets for production
echo "⚡ Building assets for production..."
npm run build

# Optimize Laravel for production
echo "🚀 Optimizing Laravel for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Production build complete!"
echo "📁 Built assets are in: public/build/"
echo "🚀 Ready for deployment to Render!"
echo ""
echo "📝 Next steps:"
echo "   1. git add ."
echo "   2. git commit -m 'Production build'"
echo "   3. git push origin main"
echo "   4. Deploy to Render server"
