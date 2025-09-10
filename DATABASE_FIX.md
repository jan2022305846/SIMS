# DATABASE SETUP - Fix "Table 'users' doesn't exist" Error

## 🚀 **IMMEDIATE FIX (Recommended):**

### **Step 1: Add Environment Variable in Render**
1. Go to **Render Dashboard** → Your Service → **Environment** tab
2. **Add new environment variable:**
   ```
   Key: RUN_MIGRATIONS
   Value: true
   ```
3. **Save Changes**

### **Step 2: Redeploy**
1. Click **"Manual Deploy"** → **"Deploy latest commit"**
2. **Watch the logs** - you should see:
   ```
   Checking database and running migrations...
   Database connected successfully.
   Running migrations...
   Migrated: 0001_01_01_000000_create_users_table
   Migrated: 2025_09_03_213339_add_school_id_to_users_table
   ... (more migrations)
   Creating admin user and seeding database...
   Seeded: AdminUserSeeder
   ```

### **Step 3: Test Login**
After successful deployment, try logging in with:
- **Username:** `admin001` or `admin`
- **Password:** `password`

## 📋 **What This Will Create:**

### **Database Tables:**
- ✅ `users` - User accounts and authentication
- ✅ `sessions` - User session management  
- ✅ `categories` - Item categories
- ✅ `items` - Inventory items
- ✅ `requests` - Supply requests
- ✅ `logs` - Activity logging
- ✅ `offices` - Office management
- ✅ Plus all other tables for full functionality

### **Initial Users Created:**
- **Admin User:**
  - Username: `admin`
  - School ID: `ADMIN001` 
  - Email: `admin@ustp.edu.ph`
  - Password: `password`
  - Role: `admin`

- **Faculty User (for testing):**
  - Username: `faculty1`
  - School ID: `FAC001`
  - Email: `faculty@ustp.edu.ph` 
  - Password: `password`
  - Role: `faculty`

## 🔧 **Alternative: Manual Database Setup**

If automatic migrations fail, you can manually create tables via FreeMySQLDatabase phpMyAdmin:

### **1. Core Tables SQL:**

```sql
-- Users table
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `school_id` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','faculty') NOT NULL DEFAULT 'faculty',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_school_id_unique` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert admin user
INSERT INTO `users` (`name`, `username`, `school_id`, `email`, `password`, `role`, `department`, `created_at`, `updated_at`) VALUES
('Admin User', 'admin', 'ADMIN001', 'admin@ustp.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Supply Office', NOW(), NOW());

-- Sessions table  
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **2. Login Credentials:**
- **Username:** `admin`
- **Password:** `password` (the hash above is bcrypt for 'password')

## 🩺 **Troubleshooting:**

### **If migration fails:**
1. Check Render logs for specific error messages
2. Verify FreeMySQLDatabase credentials are correct
3. Try the manual SQL setup above

### **If login still fails after migration:**
1. Check `/debug/health` endpoint for database connection status
2. Verify admin user was created with correct credentials
3. Check that `school_id` field exists and has correct value

### **If you can't access /debug/health:**
1. Make sure `APP_DEBUG=true` is set temporarily
2. Check Render logs for any other errors

## 📞 **Login Information After Setup:**

- **URL:** `https://sims-laravel.onrender.com`
- **Admin Username:** `admin` or `admin001`
- **Admin Password:** `password`

**Once you log in successfully, you can change the password and create more users! 🚀**
