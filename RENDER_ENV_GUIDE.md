# Render Environment Variables Configuration

This directory contains several files to help you easily configure environment variables for Render deployment:

## Files Created:

### 1. `render.yaml`
- **Purpose**: Infrastructure as Code configuration for Render
- **Usage**: Place this file in your repository root. Render will automatically read and apply the configuration when you deploy
- **Features**: 
  - Defines your web service configuration
  - Sets non-sensitive environment variables automatically
  - Includes comprehensive comments for sensitive variables

### 2. `render-config.json`
- **Purpose**: JSON format configuration for easy copying to Render dashboard
- **Usage**: Reference this file when manually setting environment variables in Render dashboard
- **Features**:
  - Structured format with all variables
  - Separates sensitive vs non-sensitive variables
  - Includes step-by-step instructions

### 3. `render-env-setup.sh`
- **Purpose**: Interactive script to display all environment variables
- **Usage**: Run `bash render-env-setup.sh` to see formatted output
- **Features**:
  - Organized by category (Database, Cache, Mail, etc.)
  - Includes setup instructions
  - Shows post-deployment health check URLs

### 4. `.env.production` (Updated)
- **Purpose**: Production environment template
- **Usage**: Reference for production configuration values
- **Features**: Complete production-ready environment configuration

## How to Use:

### Option 1: Automatic (Recommended)
1. Commit the `render.yaml` file to your repository
2. Connect your repository to Render
3. Render will automatically read the configuration
4. Manually set only the sensitive variables in Render dashboard:
   - `APP_KEY`
   - Database credentials (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
   - Email credentials (if using email features)

### Option 2: Manual
1. Copy values from `render-config.json` or run `render-env-setup.sh`
2. Add each environment variable in Render dashboard
3. Deploy your service

## Quick Setup Commands:

```bash
# Make the setup script executable
chmod +x render-env-setup.sh

# View all environment variables with instructions
./render-env-setup.sh

# Generate your APP_KEY
php artisan key:generate --show
```

## Required Sensitive Variables:

You MUST set these manually in Render dashboard:

1. **APP_KEY**: Generate with `php artisan key:generate --show`
2. **DB_HOST**: Your database host (e.g., from FreeMySQLDatabase)
3. **DB_DATABASE**: Your database name
4. **DB_USERNAME**: Your database username
5. **DB_PASSWORD**: Your database password

## Optional Sensitive Variables:

Set these if you need email functionality:

- **MAIL_USERNAME**: Your email address
- **MAIL_PASSWORD**: Your email app password
- **MAIL_FROM_ADDRESS**: Your from email address

## Database Providers:

### FreeMySQLDatabase.net (Free)
1. Go to https://www.freemysqlhosting.net/
2. Create account and database
3. Use provided credentials in environment variables

### Other Options:
- **PlanetScale** (MySQL-compatible)
- **Railway** (PostgreSQL/MySQL)
- **Supabase** (PostgreSQL)
- **AWS RDS** (MySQL/PostgreSQL)

## Post-Deployment:

After successful deployment, verify your application at:
- `https://your-app-name.onrender.com/debug/health` - Health check
- `https://your-app-name.onrender.com/debug/info` - App information

## Troubleshooting:

If you get 500 errors:
1. Check Render logs for specific error messages
2. Verify all required environment variables are set
3. Ensure database connection is working
4. Temporarily set `APP_DEBUG=true` for detailed error messages

## Support:

For issues with this configuration, check:
1. Render documentation: https://render.com/docs
2. Laravel deployment guides
3. Your specific database provider's documentation
