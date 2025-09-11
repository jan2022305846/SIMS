#!/bin/bash

# Environment Variables Import Script for Render
# This script helps you set up all required environment variables in Render

echo "==================================================="
echo "Environment Variables Setup for Render Deployment"
echo "==================================================="
echo ""

echo "STEP 1: Generate Application Key"
echo "Run this command locally and copy the output:"
echo "php artisan key:generate --show"
echo ""

echo "STEP 2: Copy these environment variables to your Render dashboard:"
echo "(Go to your Render service > Environment > Add Environment Variable)"
echo ""

echo "=== CRITICAL VARIABLES (MUST BE SET) ==="
cat << 'EOF'
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
DB_HOST=your-database-host.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
EOF

echo ""
echo "=== APPLICATION CONFIGURATION ==="
cat << 'EOF'
APP_NAME=USTP Supply Office
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Manila
APP_URL=https://your-app-name.onrender.com
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12
EOF

echo ""
echo "=== LOGGING CONFIGURATION ==="
cat << 'EOF'
LOG_CHANNEL=stderr
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
EOF

echo ""
echo "=== DATABASE CONFIGURATION ==="
cat << 'EOF'
DB_CONNECTION=mysql
EOF

echo ""
echo "=== SESSION CONFIGURATION ==="
cat << 'EOF'
SESSION_DRIVER=database
SESSION_LIFETIME=240
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.onrender.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
EOF

echo ""
echo "=== CACHE & QUEUE CONFIGURATION ==="
cat << 'EOF'
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
CACHE_PREFIX=
EOF

echo ""
echo "=== MAIL CONFIGURATION ==="
cat << 'EOF'
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=USTP Supply Office
EOF

echo ""
echo "=== REDIS CONFIGURATION (Optional) ==="
cat << 'EOF'
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
MEMCACHED_HOST=127.0.0.1
EOF

echo ""
echo "=== AWS CONFIGURATION (Optional) ==="
cat << 'EOF'
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
EOF

echo ""
echo "=== FRONTEND CONFIGURATION ==="
cat << 'EOF'
VITE_APP_NAME=USTP Supply Office
EOF

echo ""
echo "==================================================="
echo "STEP 3: Database Setup Instructions"
echo "==================================================="
echo ""
echo "For FreeMySQLDatabase.net:"
echo "1. Go to https://www.freemysqlhosting.net/"
echo "2. Create a free account"
echo "3. Create a new database"
echo "4. Use the provided credentials in the DB_* variables above"
echo ""

echo "==================================================="
echo "STEP 4: Render Deployment Instructions"
echo "==================================================="
echo ""
echo "1. Connect your GitHub repository to Render"
echo "2. Choose 'Web Service'"
echo "3. Set Build Command: (leave empty - Docker handles this)"
echo "4. Set Start Command: (leave empty - Docker handles this)"
echo "5. Add all environment variables listed above"
echo "6. Deploy!"
echo ""

echo "==================================================="
echo "STEP 5: Post-Deployment Health Check"
echo "==================================================="
echo ""
echo "After deployment, check these URLs:"
echo "- https://your-app-name.onrender.com/debug/health"
echo "- https://your-app-name.onrender.com/debug/info"
echo ""

echo "Script completed! Copy the environment variables to Render dashboard."
