# Tech Stack Documentation

## Project Overview
Todo List Tracker Application - A full-featured task management system built on a traditional LAMP stack.

## Operating System
- **Ubuntu 24.04 LTS** (Note: Ubuntu 24.3 doesn't exist; using 24.04 LTS which is the latest stable release)

## Server Stack (LAMP)

### Web Server
- **Apache 2.4.x**
  - mod_rewrite enabled
  - .htaccess support enabled
  - Virtual hosts configured

### Database
- **MySQL 8.0.x** or **MariaDB 10.11.x**
  - InnoDB storage engine
  - UTF-8 (utf8mb4) character set

### Backend Language
- **PHP 8.3.x**
  - Required Extensions:
    - mysqli or PDO_MySQL
    - json
    - mbstring
    - openssl
  - Configuration:
    - error_reporting enabled for development
    - display_errors off for production
    - session handling enabled

## Frontend Technologies

### CSS Framework
- **Bootstrap 5.3.x**
  - Source: https://getbootstrap.com/
  - Use CDN or local installation
  - Includes Popper.js for tooltips and popovers
  - Responsive grid system
  - Component library (cards, modals, forms, buttons, etc.)

### JavaScript Libraries

#### jQuery
- **jQuery 3.7.x** (required for Bootstrap)
  - Slim build acceptable if not using AJAX features
  - Used primarily for Bootstrap component functionality

#### HTMX
- **HTMX 2.x** (latest version)
  - For dynamic HTML updates without full page reloads
  - AJAX requests with HTML responses
  - Progressive enhancement approach
  - Attributes: hx-get, hx-post, hx-put, hx-delete, hx-target, hx-swap

#### Alpine.js
- **Alpine.js 3.x** (latest version)
  - Lightweight JavaScript framework
  - For interactive UI components
  - Client-side state management
  - Directives: x-data, x-show, x-if, x-for, x-model, x-on

## Application Architecture

### Directory Structure
```
/var/www/html/todo-app/
├── index.php
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── api/
│   ├── tasks.php
│   ├── create.php
│   ├── update.php
│   └── delete.php
├── assets/
│   ├── css/
│   │   └── custom.css
│   ├── js/
│   │   └── app.js
│   └── images/
└── .htaccess
```

### Database Schema
```sql
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Integration Strategy

### HTMX + PHP
- PHP endpoints return HTML fragments
- HTMX handles DOM updates without full page reloads
- Use hx-target to specify where responses should be inserted
- Use hx-swap to control how content is swapped

### Alpine.js + Bootstrap
- Alpine.js manages component state (modals, dropdowns)
- Bootstrap provides styling and layout
- Use Alpine for custom interactive behaviors
- Bootstrap JavaScript for complex components (carousels, etc.)

### jQuery + Bootstrap
- jQuery required for Bootstrap's JavaScript components
- Bootstrap JS depends on jQuery for event handling
- Use jQuery for Bootstrap component initialization

## Development Environment Setup

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName todo-app.local
    DocumentRoot /var/www/html/todo-app
    
    <Directory /var/www/html/todo-app>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/todo-app-error.log
    CustomLog ${APACHE_LOG_DIR}/todo-app-access.log combined
</VirtualHost>
```

### .htaccess Configuration
```apache
RewriteEngine On
RewriteBase /

# Remove .php extension
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.*)$ $1.php [L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

## CDN Links (for reference)

### Bootstrap 5.3
```html
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
```

### jQuery
```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
```

### HTMX
```html
<script src="https://unpkg.com/htmx.org@2.0.0"></script>
```

### Alpine.js
```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

## Security Considerations

### PHP Security
- Use prepared statements for all database queries
- Validate and sanitize all user inputs
- Implement CSRF protection
- Use secure session handling
- Set appropriate file permissions (644 for files, 755 for directories)

### Database Security
- Use separate database user with minimal privileges
- Store credentials outside web root
- Use environment variables for sensitive data

### Frontend Security
- Escape output to prevent XSS
- Use HTTPS in production
- Implement Content Security Policy

## Best Practices

### Code Organization
- Separate concerns (presentation, business logic, data access)
- Use PHP includes for reusable components
- Keep API endpoints RESTful
- Use meaningful naming conventions

### HTMX Patterns
- Return HTML fragments from server
- Use hx-indicator for loading states
- Implement proper error handling with hx-on
- Use hx-confirm for destructive actions

### Alpine.js Patterns
- Keep state management simple and local
- Use x-data for component initialization
- Avoid complex logic in templates
- Use Alpine for UI interactions, HTMX for data fetching

### Performance
- Minimize database queries
- Use appropriate indexes
- Enable browser caching
- Compress assets
- Use CDN for libraries

## Testing Checklist
- [ ] Apache serving PHP files correctly
- [ ] Database connection established
- [ ] Bootstrap styles loading
- [ ] jQuery functionality working
- [ ] HTMX requests sending/receiving properly
- [ ] Alpine.js reactivity functioning
- [ ] CRUD operations working
- [ ] Responsive design on mobile devices
- [ ] Cross-browser compatibility

## Documentation References
- Bootstrap: https://getbootstrap.com/docs/5.3/
- HTMX: https://htmx.org/docs/
- Alpine.js: https://alpinejs.dev/
- PHP: https://www.php.net/manual/en/
- MySQL: https://dev.mysql.com/doc/