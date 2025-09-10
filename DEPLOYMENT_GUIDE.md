# Laravel Deployment Guide for Render + FreeMySQLDatabase

## Quick Fix for 500 Error

The most common causes of 500 errors in Laravel production deployments:

### 1. **IMMEDIATE STEPS - Set These Environment Variables in Render:**

```bash
# CRITICAL - Set these first
APP_KEY=base64:mekbdLOze95Lwp/oKEamke9p7EMDfL7tKS1JchrZfDk=
APP_ENV=production
APP_DEBUG=true  # Set to true initially for debugging, then false later
APP_URL=https://your-app-name.onrender.com

# Database (Get these from FreeMySQLDatabase)
DB_CONNECTION=mysql
DB_HOST=your-freemysql-host.freemysqlhosting.net
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=debug
```

### 2. **Deploy Steps:**

1. **Update your repository** with the new Dockerfile and docker-start.sh
2. **Set environment variables** in Render dashboard
3. **Deploy** from Render dashboard
4. **Check health** at: `https://your-app.onrender.com/debug/health`

### 3. **Troubleshooting 500 Errors:**

#### A. **Check the health endpoint first:**
```
https://your-app-name.onrender.com/debug/health
```

#### B. **Common Issues & Solutions:**

**Issue: "No application encryption key"**
```bash
# Solution: Set APP_KEY in Render environment variables
APP_KEY=base64:mekbdLOze95Lwp/oKEamke9p7EMDfL7tKS1JchrZfDk=
```

**Issue: "Database connection failed"**
```bash
# Solution: Verify FreeMySQLDatabase credentials
DB_HOST=sql12.freemysqlhosting.net  # Example host
DB_PORT=3306
DB_DATABASE=sql12123456  # Your actual database name
DB_USERNAME=sql12123456  # Your actual username
DB_PASSWORD=your-password  # Your actual password
```

**Issue: "Permission denied" on storage****
```bash
# Solution: The new Dockerfile fixes this automatically
# But you can verify with the health check endpoint
```

**Issue: "Route not found" or "Symfony\Component\HttpKernel\Exception\NotFoundHttpException"**
```bash
# Solution: Clear route cache
# This is handled in docker-start.sh, but you might need:
APP_DEBUG=true  # Temporarily enable debug mode
```

### 4. **Environment Variables Checklist for Render:**

**Required Variables:**
- [x] `APP_KEY` - Generated encryption key
- [x] `APP_ENV=production`
- [x] `APP_DEBUG=false` (after debugging is complete)
- [x] `APP_URL` - Your Render app URL
- [x] `DB_HOST` - FreeMySQLDatabase host
- [x] `DB_DATABASE` - Your database name
- [x] `DB_USERNAME` - Your database username  
- [x] `DB_PASSWORD` - Your database password
- [x] `SESSION_DRIVER=database`
- [x] `CACHE_STORE=database`
- [x] `LOG_CHANNEL=stderr`

**Optional but Recommended:**
- `APP_TIMEZONE=Asia/Manila`
- `LOG_LEVEL=error` (for production)
- `SESSION_LIFETIME=120`

### 5. **Debugging Steps:**

1. **Enable debug mode temporarily:**
   ```bash
   APP_DEBUG=true
   ```

2. **Check the health endpoint:**
   ```
   GET https://your-app.onrender.com/debug/health
   ```

3. **Check Render logs:**
   - Go to Render dashboard
   - Click on your service
   - Click "Logs" tab
   - Look for PHP errors or Laravel exceptions

4. **Common log patterns to look for:**
   ```
   "No application encryption key"
   "SQLSTATE[HY000] [2002] Connection refused"
   "Permission denied"
   "Class 'App\\Http\\Controllers\\..."
   ```

### 6. **FreeMySQLDatabase Setup:**

1. Go to https://www.freemysqlhosting.net/
2. Create a free account
3. Create a new database
4. Note down:
   - Server: `sql12.freemysqlhosting.net`
   - Name: `sql12XXXXXX`
   - Username: `sql12XXXXXX`  
   - Password: `your-password`
   - Port: `3306`

### 7. **Production Optimizations (After fixing 500 error):**

Once your app is working, set these for better performance:

```bash
APP_DEBUG=false
LOG_LEVEL=error
APP_ENV=production
```

### 8. **Health Check Endpoints:**

- **Health Check:** `/debug/health` - Full system diagnostics
- **Quick Info:** `/debug/info` - Basic app information

### 9. **If You Still Get 500 Errors:**

1. **Check Render build logs** - Look for composer install failures
2. **Verify all environment variables** are set correctly
3. **Test database connection** separately using a MySQL client
4. **Check PHP version compatibility** (should be 8.2)
5. **Verify file permissions** using the health endpoint

### 10. **Emergency Debugging:**

If all else fails, temporarily add this to your environment variables:
```bash
APP_DEBUG=true
LOG_LEVEL=debug
LOG_CHANNEL=stderr
```

Then check Render logs for detailed error messages.

## Files Changed:

1. `Dockerfile` - Updated for production Apache deployment
2. `docker-start.sh` - Runtime setup and health checks  
3. `.env.production` - Production environment template
4. `routes/debug.php` - Health check endpoints
5. `routes/web.php` - Include debug routes when needed

Deploy these changes and set the environment variables in Render dashboard!
