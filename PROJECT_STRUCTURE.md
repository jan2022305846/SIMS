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
