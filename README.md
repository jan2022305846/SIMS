# 🏢 Supply Office Management System (SIMS)

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.42.1-red?style=for-the-badge&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Docker-Ready-brightgreen?style=for-the-badge&logo=docker" alt="Docker Ready">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

A comprehensive Laravel-based **Supply Office Management System** designed for educational institutions and organizations to efficiently manage inventory, track supply requests, and maintain detailed records of office supplies and equipment.

## 📋 Table of Contents

- [Features](#-features)
- [System Architecture](#-system-architecture)
- [Installation](#-installation)
- [Deployment](#-deployment)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

### 🎯 Core Functionality

#### **Inventory Management**
- ✅ **Item Catalog** - Comprehensive item database with categories, descriptions, and specifications
- ✅ **Stock Tracking** - Real-time inventory levels with minimum stock alerts
- ✅ **QR Code Integration** - Generate and scan QR codes for easy item identification
- ✅ **Asset Tracking** - Track non-consumable items and their current holders
- ✅ **Categories & Classification** - Organize items by type (consumable, non-consumable, equipment)

#### **Request Management Workflow**
- ✅ **Multi-Stage Approval** - Office Head → Admin approval workflow
- ✅ **Priority Levels** - Low, Normal, High, Urgent priority classification
- ✅ **Request History** - Complete audit trail of all requests and approvals
- ✅ **Bulk Operations** - Approve/decline multiple requests simultaneously
- ✅ **Attachment Support** - Upload supporting documents with requests

#### **User Management & Roles**
- ✅ **Role-Based Access Control** - Admin, Office Head, Faculty roles
- ✅ **Office Management** - Organize users by departments/offices
- ✅ **User Profiles** - Comprehensive user information and permissions
- ✅ **Multi-Tenant Support** - School ID-based user organization

#### **Reporting & Analytics**
- ✅ **Inventory Reports** - Stock levels, valuation, and analytics
- ✅ **Request Analytics** - Request trends, approval rates, and statistics
- ✅ **Activity Logs** - Detailed system activity tracking
- ✅ **PDF Generation** - Professional reports with charts and summaries
- ✅ **Low Stock Alerts** - Automated notifications for restocking

#### **Advanced Features**
- ✅ **QR Code Scanner** - Built-in camera scanner for mobile devices
- ✅ **Activity Logging** - Comprehensive audit trail with user actions
- ✅ **Search & Filtering** - Advanced search across all modules
- ✅ **Responsive Design** - Mobile-friendly interface
- ✅ **Real-time Updates** - Live status updates and notifications

## 🏗️ System Architecture

### **Backend Stack**
- **Framework**: Laravel 11.42.1
- **Language**: PHP 8.2
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **PDF Generation**: DomPDF
- **QR Codes**: QR Code Generator

### **Frontend Stack**
- **Build Tool**: Vite
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Vanilla JS with Alpine.js components
- **Icons**: Heroicons

### **Deployment**
- **Containerization**: Docker with Apache
- **Local Server**: XAMPP/LAMPP or similar
- **Database**: MySQL/MariaDB
- **Asset Compilation**: Node.js 18.x

### **Database Schema**
```
📊 Core Tables:
├── users (Admin, Office Head, Faculty)
├── offices (Departments/Units)
├── categories (Item classifications)
├── items (Supply catalog)
├── requests (Supply requests with workflow)
├── activity_logs (System audit trail)
├── item_scan_logs (QR code scan tracking)
└── logs (Legacy activity tracking)
```

## 🚀 Installation

### **Local Development Setup**

#### **Prerequisites**
- PHP 8.2+
- Composer 2.6+
- Node.js 18.x+
- MySQL 8.0+
- Git

#### **Step 1: Clone Repository**
```bash
git clone https://github.com/jan2022305846/SIMS.git
cd SIMS
```

#### **Step 2: Install Dependencies**
```bash
# PHP dependencies
composer install

# Node.js dependencies  
npm install
```

#### **Step 3: Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supply_office_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### **Step 4: Database Setup**
```bash
# Run migrations
php artisan migrate

# Seed database with admin user and sample data
php artisan db:seed --class=AdminUserSeeder
```

#### **Step 5: Build Assets**
```bash
# Development build
npm run dev

# Production build
npm run build
```

#### **Step 6: Start Development Server**
```bash
php artisan serve
```

Visit `http://localhost:8000` and login with:
- **Username**: `admin`
- **Password**: `password`

## 🌐 Deployment

### **Local Server Deployment**

#### **Step 1: Server Requirements**
Ensure your local server has:
- **PHP 8.1+** with required extensions
- **MySQL/MariaDB 5.7+**
- **Node.js 18.x+** for asset compilation
- **Composer** for PHP dependencies
- **Git** for version control

#### **Step 2: Upload Project Files**
1. Upload the project files to your web server's document root
2. Set proper file permissions:
```bash
# Set ownership to web server user (e.g., www-data, apache, or nobody)
sudo chown -R www-data:www-data /path/to/project

# Set proper permissions
sudo chmod -R 755 /path/to/project
sudo chmod -R 775 /path/to/project/storage
sudo chmod -R 775 /path/to/project/bootstrap/cache
```

#### **Step 3: Environment Configuration**
1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Update `.env` with your local server settings:
```bash
# Application
APP_NAME="Supply Office Management System"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Manila
APP_URL=http://your-local-server.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=supply_office_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Session (adjust for local server)
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```

#### **Step 4: Install Dependencies**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build production assets
npm run build
```

#### **Step 5: Database Setup**
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
```

#### **Step 6: Optimize for Production**
```bash
# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set storage permissions
php artisan storage:link
```

#### **Step 7: Web Server Configuration**
Configure your web server (Apache/Nginx) to point to the `public` directory:

**Apache (.htaccess is included):**
```apache
<VirtualHost *:80>
    ServerName your-local-server.com
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name your-local-server.com;
    root /path/to/project/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### **Docker Deployment (Alternative)**
If using Docker for local deployment:

```bash
# Build and run with Docker
docker build -t supply-office .
docker run -d -p 80:80 --name supply-office supply-office
```

### **Post-Deployment Checklist**
- ✅ Application loads without errors
- ✅ Database connection works
- ✅ Admin user can login
- ✅ File uploads work (storage permissions)
- ✅ Email notifications (if configured)
- ✅ QR code scanning functionality

## 👥 Usage

### **User Roles & Permissions**

#### **🔐 Admin**
- Full system access and configuration
- User management and role assignment
- Complete inventory management
- Request approval (final stage)
- System reports and analytics
- QR code generation and scanning

#### **👨‍💼 Office Head**
- View and manage office inventory
- First-stage request approval
- Office user management
- Department-specific reports
- QR code scanning

#### **👩‍🏫 Faculty**
- Submit supply requests
- View personal request history
- Browse available inventory
- Update request information

### **Core Workflows**

#### **📝 Supply Request Process**
1. **Faculty** submits request with details:
   - Item selection from catalog
   - Quantity needed
   - Purpose and priority
   - Required date
   - Supporting documents

2. **Office Head** reviews and approves:
   - Verify request validity
   - Check budget/policy compliance
   - Add notes and recommendations

3. **Admin** final approval:
   - Check stock availability
   - Process fulfillment
   - Update inventory levels
   - Generate pickup notifications

#### **📦 Inventory Management**
1. **Add New Items**:
   - Basic information (name, description, category)
   - Stock details (quantity, minimum stock, unit price)
   - Classification (consumable/non-consumable)
   - Generate QR codes

2. **Stock Management**:
   - Track current stock levels
   - Set minimum stock thresholds
   - Receive low stock alerts
   - Update stock quantities

3. **Item Tracking**:
   - QR code scanning for quick access
   - Track item locations and holders
   - Maintain usage history
   - Generate item reports

## 🔌 API Documentation

### **Authentication**
All API endpoints require authentication via Laravel Sanctum:

```javascript
// Login to get token
POST /api/login
{
    "email": "admin@example.com",
    "password": "password"
}

// Use token in subsequent requests
Authorization: Bearer your_token_here
```

### **Core Endpoints**

#### **Inventory Management**
```javascript
// Get all items with filtering
GET /api/admin/inventory?category_id=1&status=low_stock

// Add new item
POST /api/admin/inventory
{
    "name": "Whiteboard Marker",
    "category_id": 1,
    "current_stock": 50,
    "minimum_stock": 10,
    "unit_price": 2.50
}

// Update item
PUT /api/admin/inventory/{id}

// Delete item
DELETE /api/admin/inventory/{id}
```

#### **Request Management**
```javascript
// Get requests with filtering
GET /api/admin/requests?status=pending&priority=high

// Approve request
POST /api/admin/requests/{id}/approve
{
    "admin_notes": "Approved for immediate fulfillment",
    "approved_quantity": 5
}

// Bulk approve requests
POST /api/admin/requests/bulk-approve
{
    "request_ids": [1, 2, 3],
    "admin_notes": "Bulk approved"
}
```

#### **User Management**
```javascript
// Get all users
GET /api/admin/users?role=faculty&office_id=1

// Create user
POST /api/admin/users
{
    "name": "John Doe",
    "email": "john@example.com",
    "role": "faculty",
    "office_id": 1
}
```

#### **Reports**
```javascript
// Generate inventory report
GET /api/admin/reports/inventory?format=pdf&category_id=1

// Get system statistics
GET /api/admin/reports/stats
```

### **QR Code Integration**
```javascript
// Scan QR code
POST /api/admin/scan-qr
{
    "qr_data": "item_123",
    "location": "Office A"
}

// Generate QR code
GET /api/admin/items/{id}/qr-code
```

## 🔒 Security

### **Security Features**
- ✅ **Authentication**: Laravel Sanctum token-based auth
- ✅ **Authorization**: Role-based access control (RBAC)
- ✅ **Data Validation**: Comprehensive input validation
- ✅ **CSRF Protection**: Built-in CSRF token validation
- ✅ **SQL Injection Prevention**: Eloquent ORM with parameterized queries
- ✅ **Password Security**: Bcrypt hashing with salt
- ✅ **Activity Logging**: Complete audit trail of user actions

### **Security Best Practices**
```bash
# Environment Variables
- Never commit .env files to version control
- Use strong, unique APP_KEY
- Secure database credentials
- HTTPS in production

# User Management
- Strong password requirements
- Regular password updates
- Role-based permissions
- Session management

# Data Protection
- Input sanitization
- Output encoding
- File upload restrictions
- Regular security updates
```

### **Recent Security Updates**
- 🔐 **Git Guardian Integration**: Automated secret detection
- 🔐 **Enhanced .gitignore**: Prevent accidental secret commits
- 🔐 **Secure Key Generation**: Automated APP_KEY rotation
- 🔐 **Documentation Sanitization**: Removed hardcoded secrets

## 🛠️ Development

### **Project Structure**
```
📁 supply-api/
├── 📁 app/
│   ├── 📁 Http/Controllers/
│   │   ├── 📁 Admin/          # Admin panel controllers
│   │   ├── 📁 Auth/           # Authentication controllers
│   │   └── 📁 Web/            # Web interface controllers
│   ├── 📁 Models/             # Eloquent models
│   └── 📁 Services/           # Business logic services
├── 📁 database/
│   ├── 📁 migrations/         # Database schema changes
│   ├── 📁 seeders/           # Sample data generators
│   └── 📁 factories/         # Model factories for testing
├── 📁 resources/
│   ├── 📁 views/             # Blade templates
│   ├── 📁 css/               # Stylesheets
│   └── 📁 js/                # JavaScript assets
├── 📁 routes/                # Route definitions
├── 📁 storage/               # Application storage
├── 📁 tests/                 # PHPUnit tests
├── 🐳 Dockerfile            # Docker configuration
├── 📜 docker-start-simple.sh # Production startup script
└── 📜 database-init.sh       # Database initialization
```

### **Key Components**

#### **Models**
- `User` - System users with role-based permissions
- `Office` - Departments and organizational units  
- `Category` - Item classification system
- `Item` - Supply catalog with QR code support
- `Request` - Multi-stage approval workflow
- `ActivityLog` - Comprehensive audit trail

#### **Controllers**
- `AdminController` - Dashboard and QR scanning
- `InventoryManagementController` - Item management
- `RequestManagementController` - Request workflow
- `UserManagementController` - User administration
- `ReportsController` - Analytics and reporting

#### **Services**
- `QRCodeService` - QR code generation and scanning
- Authentication services via Laravel Sanctum
- PDF generation with DomPDF

### **Testing**
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Generate test coverage
php artisan test --coverage
```

### **Code Style**
The project follows PSR-12 coding standards:
```bash
# Check code style
./vendor/bin/phpcs

# Fix code style issues
./vendor/bin/phpcbf
```

## 📈 Monitoring & Maintenance

### **Health Checks**
Monitor application health via the debug endpoint:
```bash
# Check system status
GET /debug/health

Response:
{
    "status": "healthy",
    "database": "connected",
    "users_table": "exists", 
    "admin_user": "exists",
    "timestamp": "2025-09-11T10:30:00Z"
}
```

### **Log Monitoring**
```bash
# Application logs
tail -f storage/logs/laravel.log

# Activity logs
SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100;

# QR scan logs  
SELECT * FROM item_scan_logs ORDER BY created_at DESC LIMIT 50;
```

### **Performance Optimization**
```bash
# Cache configuration
php artisan config:cache

# Cache routes  
php artisan route:cache

# Optimize autoloader
composer dump-autoload --optimize

# Database optimization
php artisan migrate:status
php artisan db:seed --class=OptimizationSeeder
```

## 🤝 Contributing

We welcome contributions to improve the Supply Office Management System!

### **Development Setup**
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Run tests: `php artisan test`
5. Commit changes: `git commit -m 'Add amazing feature'`
6. Push to branch: `git push origin feature/amazing-feature`
7. Submit a Pull Request

### **Contribution Guidelines**
- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Use descriptive commit messages
- Ensure backward compatibility

### **Bug Reports**
Please include:
- Laravel and PHP versions
- Detailed steps to reproduce
- Expected vs actual behavior
- Error messages and logs
- System configuration details

## 📄 License

This project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

```
MIT License

Copyright (c) 2025 Supply Office Management System

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Support

For support and questions:
- 📧 **Email**: [support@sims-project.com](mailto:support@sims-project.com)
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/jan2022305846/SIMS/issues)
- 📚 **Documentation**: [Project Wiki](https://github.com/jan2022305846/SIMS/wiki)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/jan2022305846/SIMS/discussions)

---

<p align="center">
  <strong>Built with ❤️ for educational institutions and organizations</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/github/stars/jan2022305846/SIMS?style=social" alt="GitHub Stars">
  <img src="https://img.shields.io/github/forks/jan2022305846/SIMS?style=social" alt="GitHub Forks">
  <img src="https://img.shields.io/github/watchers/jan2022305846/SIMS?style=social" alt="GitHub Watchers">
</p>
