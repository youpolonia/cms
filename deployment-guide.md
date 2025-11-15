# CMS Deployment Guide

## 1. Server Requirements
- PHP 8.1+ with following extensions:
  - PDO (with MySQL driver)
  - OpenSSL
  - JSON
  - Mbstring
  - Fileinfo
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache 2.4+ or Nginx 1.18+)
- 100MB disk space minimum
- FTP access for deployment

## 2. Installation Steps

### FTP Deployment
1. Upload all files to your web server via FTP
2. Set proper permissions:
   - `chmod 755` for directories
   - `chmod 644` for files
3. Create writable directories:
   - `storage/app/private`
   - `storage/app/public`
   - `storage/cache`

### Initial Setup
1. Copy `.env.example` to `.env`
2. Configure database settings in `.env`:
   ```ini
   DB_HOST=localhost
   DB_NAME=cms_production
   DB_USER=username
   DB_PASS=password
   DB_PORT=3306
   ```

## 3. Configuration

### Database Setup
1. Create database with UTF8mb4 encoding
2. Run initial migrations:
   ```php
   php run_migrations.php
   ```

### Tenant Configuration
1. Add tenants to `tenants` table:
   ```sql
   INSERT INTO tenants (id, name, domain, created_at) 
   VALUES (UUID(), 'Primary Tenant', 'example.com', NOW());
   ```

### System Settings
Configure in `config.php`:
```php
// Enable/disable modules
$config['modules'] = [
    'auth' => true,
    'content' => true,
    'analytics' => true
];

// Set default timezone
date_default_timezone_set('UTC');
```

## 4. Troubleshooting

### Common Issues
**Database Connection Errors**
- Verify credentials in `.env`
- Check MySQL user has proper permissions
- Ensure PDO MySQL extension is installed

**File Permission Issues**
- Verify storage directories are writable
- Check owner/group matches web server user

**Tenant Isolation Problems**
- Confirm all tables have `tenant_id` column
- Verify UUID format in tenant records

### Log Files
Check for errors in:
- `logs/system.log`
- `logs/database.log`
- Web server error logs

## Support
For additional help, contact support@example.com with:
- Error messages
- Screenshots of issues
- Steps to reproduce