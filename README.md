# TodoTracker - SaaS Task Management Application

A comprehensive, feature-rich todo list and task management application built with the LAMP stack (Linux, Apache, MySQL, PHP). TodoTracker provides an intuitive interface with multiple views including dashboard, kanban board, calendar, and list/grid layouts.

## 🚀 Features

- **User Authentication**: Secure registration, login, email verification, and password reset
- **Multi-View Task Management**: Dashboard, Kanban board, Calendar, List/Grid views
- **Task Organization**: Status tracking (Pending, In Progress, Completed), Priority levels (Low, Medium, High)
- **Categories & Tags**: Custom user-defined categories with color coding
- **Smart Filtering**: Filter by status, priority, due date, and categories
- **Search Functionality**: Real-time search across task titles and descriptions
- **Responsive Design**: Mobile-first design using Bootstrap 5.3
- **Modern UI**: Built with Bootstrap 5, HTMX, Alpine.js, and jQuery
- **Secure**: Password hashing, session management, CSRF protection, SQL injection prevention

## 📋 Prerequisites

Before installing TodoTracker, ensure you have the following:

- **Ubuntu 24.04 LTS** (or compatible Linux distribution)
- **Apache 2.4.x** with mod_rewrite enabled
- **MySQL 8.0+** or **MariaDB 10.11+**
- **PHP 8.3.x** with required extensions
- **Composer** (optional, for dependency management)
- **Git** (for version control)

## 🛠️ Installation

### Step 1: Install LAMP Stack on Ubuntu 24.04

#### 1.1 Update System Packages

```bash
sudo apt update
sudo apt upgrade -y
```

#### 1.2 Install Apache Web Server

```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2

# Enable required Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2

# Verify Apache is running
sudo systemctl status apache2
```

#### 1.3 Install MySQL/MariaDB

**Option A: MySQL**
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation
sudo mysql_secure_installation
```

**Option B: MariaDB**
```bash
sudo apt install mariadb-server -y
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Secure MariaDB installation
sudo mysql_secure_installation
```

#### 1.4 Install PHP 8.3 and Required Extensions

```bash
# Add PHP repository (if needed)
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mysql \
    php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
    php8.3-gd php8.3-intl php8.3-bcmath -y

# Install Apache PHP module
sudo apt install libapache2-mod-php8.3 -y

# Restart Apache
sudo systemctl restart apache2

# Verify PHP installation
php -v
```

### Step 2: Create MySQL Database and User

```bash
# Login to MySQL
sudo mysql -u root -p

# Or for local development with specific port (like MAMP):
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889
```

```sql
-- Create database
CREATE DATABASE todo_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (if not already exists)
CREATE USER 'vibe_templates'@'localhost' IDENTIFIED BY 'YouTubeDemo123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON todo_tracker.* TO 'vibe_templates'@'localhost';
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
EXIT;
```

### Step 3: Clone and Configure Application

#### 3.1 Clone Repository

```bash
# Clone to your web directory
cd /var/www/html
sudo git clone https://github.com/yourusername/YouTube-SaaS-Project.git todo-app
cd todo-app

# Or for local development:
cd /Users/edhonour/YouTube-SaaS-Project
```

#### 3.2 Set File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/html/todo-app
# Or for local development:
sudo chown -R $USER:$USER .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make specific directories writable
chmod -R 775 html/assets/images
chmod -R 775 logs
```

#### 3.3 Configure Environment Variables

```bash
# Copy example environment file
cp .env.example .env

# Edit with your actual values
nano .env
```

Update the following in `.env`:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=todo_tracker
DB_USER=vibe_templates
DB_PASS=YouTubeDemo123!
```

### Step 4: Import Database Schema

#### 4.1 Import via Command Line

```bash
# Import schema
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889 todo_tracker < html/schema.sql

# Or with sudo:
sudo mysql -u vibe_templates -p todo_tracker < html/schema.sql
```

#### 4.2 Import via PHP Script

```bash
cd html
php test-db-connection.php
# Follow prompts to test connection and import schema
```

#### 4.3 Verify Tables Created

```bash
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889 todo_tracker -e "SHOW TABLES;"
```

You should see:
- users
- tasks
- categories
- task_categories
- sessions
- password_resets
- login_attempts

### Step 5: Configure Apache Virtual Host

#### 5.1 Create Virtual Host Configuration

```bash
sudo nano /etc/apache2/sites-available/todo-app.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName todo-app.local
    ServerAlias www.todo-app.local
    DocumentRoot /var/www/html/todo-app/html

    <Directory /var/www/html/todo-app/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/todo-app-error.log
    CustomLog ${APACHE_LOG_DIR}/todo-app-access.log combined
</VirtualHost>
```

#### 5.2 Enable Site and Restart Apache

```bash
# Enable the site
sudo a2ensite todo-app.conf

# Disable default site (optional)
sudo a2dissite 000-default.conf

# Test Apache configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

#### 5.3 Update /etc/hosts (for local development)

```bash
sudo nano /etc/hosts
```

Add:
```
127.0.0.1    todo-app.local
```

### Step 6: Create .htaccess File

Create `html/.htaccess`:

```apache
RewriteEngine On
RewriteBase /

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.*)$ $1.php [L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 7: Test Installation

#### 7.1 Test Database Connection

```bash
cd html
php test-db-connection.php
```

#### 7.2 Access Application

Open your browser and navigate to:
- **Production**: `http://todo-app.local`
- **Local Development**: `http://localhost:8888/html/` (adjust port as needed)

#### 7.3 Default Demo Account

The schema includes a demo account for testing:
- **Email**: demo@todotracker.com
- **Password**: Demo123!

## 📁 Project Structure

```
YouTube-SaaS-Project/
├── .env.example              # Environment variables template
├── CLAUDE.md                 # AI assistant instructions
├── README.md                 # This file
├── requirements.md           # Detailed requirements
├── tech-stack.md            # Technology stack documentation
├── design-notes.md          # UI/UX design guidelines
├── html/                    # Web root directory
│   ├── index.php           # Main application entry point
│   ├── schema.sql          # Database schema
│   ├── test-db-connection.php  # Database test script
│   ├── config/             # Configuration files
│   │   └── database.php    # Database connection class
│   ├── includes/           # Reusable PHP components
│   │   ├── header.php      # Common header
│   │   └── footer.php      # Common footer
│   ├── api/                # API endpoints for CRUD operations
│   │   ├── tasks.php       # Task operations
│   │   ├── create.php      # Create task
│   │   ├── update.php      # Update task
│   │   └── delete.php      # Delete task
│   ├── assets/             # Static assets
│   │   ├── css/            # Stylesheets
│   │   │   └── custom.css  # Custom styles
│   │   ├── js/             # JavaScript files
│   │   │   └── app.js      # Main application JS
│   │   └── images/         # Images and uploads
│   └── .htaccess          # Apache rewrite rules
├── docs/                   # Documentation
│   └── activity.md        # Development activity log
└── tasks/                  # Task tracking
    └── todo.md            # Todo list

```

## 🔧 Configuration

### Database Configuration

Edit `html/config/database.php` to update database credentials:

```php
private $host = '127.0.0.1';
private $port = '8889';
private $db_name = 'todo_tracker';
private $username = 'vibe_templates';
private $password = 'YouTubeDemo123!';
```

### PHP Configuration

Recommended `php.ini` settings:

```ini
upload_max_filesize = 5M
post_max_size = 8M
max_execution_time = 30
memory_limit = 128M
display_errors = Off  # In production
error_reporting = E_ALL
date.timezone = America/New_York
```

## 🔒 Security Considerations

1. **Change Default Credentials**: Update database password and create new admin account
2. **Use HTTPS**: Install SSL certificate (Let's Encrypt recommended)
3. **Regular Updates**: Keep Apache, MySQL, and PHP updated
4. **File Permissions**: Ensure proper permissions (644 for files, 755 for directories)
5. **Environment Variables**: Never commit `.env` to version control
6. **Input Validation**: All user input is sanitized and validated
7. **SQL Injection**: All queries use prepared statements
8. **XSS Protection**: All output is properly escaped
9. **CSRF Protection**: CSRF tokens implemented for state-changing operations
10. **Session Security**: Secure, HTTPOnly cookies with proper expiration

## 🧪 Testing the Database Connection

A test script is provided to verify database connectivity:

```bash
cd html
php test-db-connection.php
```

This script will:
- Test database connection
- Check if database exists
- List existing tables
- Optionally run schema.sql to create/reset database

## 📚 Technology Stack

- **Backend**: PHP 8.3
- **Database**: MySQL 8.0 / MariaDB 10.11
- **Web Server**: Apache 2.4
- **Frontend Framework**: Bootstrap 5.3
- **AJAX Library**: HTMX 2.x
- **JavaScript Framework**: Alpine.js 3.x
- **DOM Manipulation**: jQuery 3.7

## 📖 Usage

### Creating Tasks
1. Click "Add Task" button or use quick-add input
2. Fill in task details (title required)
3. Set priority, due date, and categories
4. Save task

### Managing Tasks
- **Dashboard**: View statistics and recent tasks
- **Kanban Board**: Drag tasks between status columns
- **Calendar**: View tasks by due date
- **List/Grid**: Filter, sort, and search tasks

### Categories
1. Navigate to Categories section
2. Create custom categories with colors
3. Assign categories to tasks
4. Filter tasks by category

## 🐛 Troubleshooting

### Apache Issues

```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/todo-app-error.log
```

### MySQL Connection Issues

```bash
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889
```

### Permission Issues

```bash
# Reset permissions
sudo chown -R www-data:www-data /var/www/html/todo-app
find /var/www/html/todo-app -type d -exec chmod 755 {} \;
find /var/www/html/todo-app -type f -exec chmod 644 {} \;
```

### PHP Errors

```bash
# Check PHP error logs
sudo tail -f /var/log/apache2/error.log

# Test PHP
php -v
php -m  # List installed modules
```

## 🤝 Contributing

This is a project developed for educational purposes. Feel free to fork and customize for your needs.

## 📄 License

See LICENSE file for details.

## 📧 Support

For issues and questions, please check:
1. Error logs in `/var/log/apache2/`
2. PHP configuration: `php -i | grep error`
3. MySQL logs: `sudo tail -f /var/log/mysql/error.log`

## 🎯 Next Steps

After successful installation:

1. ✅ Test database connection
2. ✅ Login with demo account
3. ✅ Create your first task
4. ✅ Explore different views (Dashboard, Kanban, Calendar)
5. ✅ Create custom categories
6. ✅ Set up email notifications (optional)
7. ✅ Customize theme and preferences
8. ✅ Create your own admin account
9. ✅ Delete or deactivate demo account

## 📝 Version

**Version**: 1.0.0
**Last Updated**: October 20, 2025

---

Built with ❤️ using the LAMP stack
