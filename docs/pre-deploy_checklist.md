# Pre-Deployment Checklist

## Security Audit
- [ ] **Run security audit tool**
  ```
  Navigate to: /admin/tools/security_audit.php
  ```
- [ ] **Verify all security counters show 0**
  - Forbidden calls: 0
  - Autoloaders: 0
  - Dynamic includes: 0
  - CSRF issues: 0
  - Public test endpoints: 0
  - Trailing PHP tags: 0
- [ ] **Review JSON output for details** (if any issues found)
  ```
  /admin/tools/security_audit.php?format=json
  ```
- [ ] **Fix all reported issues** before proceeding

## Configuration
- [ ] **Set DEV_MODE to false**
  ```php
  // In /config.php or root config
  define('DEV_MODE', false);
  ```
- [ ] **Verify .devflag file is removed** (if used)
  ```bash
  rm /var/www/html/cms/.devflag
  ```
- [ ] **Check error reporting disabled in production**
  ```php
  error_reporting(0);
  ini_set('display_errors', 0);
  ```

## Directory Security
- [ ] **Confirm admin/.htaccess has deny block**
  ```apache
  Order Deny,Allow
  Deny from all
  <FilesMatch "\.(php)$">
      Allow from all
  </FilesMatch>
  ```
- [ ] **Verify sensitive directories are protected**
  - `/admin/auth/`
  - `/includes/`
  - `/core/`
  - `/config/`
  - `/logs/`

## Secrets and Credentials
- [ ] **Rotate all production secrets**
  - Database passwords
  - API keys
  - Session secrets
  - CSRF tokens salt
  - Encryption keys
- [ ] **Verify no credentials in git history**
  ```bash
  git log --all --full-history -- config.php
  ```
- [ ] **Confirm .gitignore includes sensitive files**
  - `config.php`
  - `.env`
  - `*.key`
  - `auth/.admin_auth`
  - `cookies.txt`

## Cache and Temporary Files
- [ ] **Clear all cache directories**
  ```bash
  rm -rf cache/*
  rm -rf tmp/*
  ```
- [ ] **Remove debug/test files**
  ```bash
  find . -name "debug_*.php" -type f
  find . -name "test_*.php" -type f
  ```
- [ ] **Clear session data** (optional, if appropriate)
  ```bash
  rm -rf storage/framework/sessions/*
  ```

## Database
- [ ] **Run pending migrations**
  ```
  php migrate.php
  ```
- [ ] **Backup production database**
  ```bash
  mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
  ```
- [ ] **Verify database user has minimum required privileges**
  - No DROP, CREATE USER, or GRANT permissions in production

## File Permissions
- [ ] **Set correct file permissions**
  ```bash
  find . -type f -exec chmod 644 {} \;
  find . -type d -exec chmod 755 {} \;
  ```
- [ ] **Restrict config file permissions**
  ```bash
  chmod 600 config.php
  chmod 600 admin/auth/.admin_auth
  ```
- [ ] **Verify uploads directory is writable**
  ```bash
  chmod 775 uploads/
  ```

## Web Server Configuration
- [ ] **Verify .htaccess rules are active**
  ```apache
  # Test: access /admin/auth/.admin_auth should return 403
  ```
- [ ] **Confirm HTTPS is enforced** (if applicable)
  ```apache
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
  ```
- [ ] **Check PHP version meets requirements** (PHP 7.4+ recommended)

## Logging and Monitoring
- [ ] **Verify error logs are writable**
  ```bash
  touch logs/error.log
  chmod 664 logs/error.log
  ```
- [ ] **Clear old log files** (optional)
  ```bash
  > logs/error.log
  ```
- [ ] **Set up log rotation** (if not already configured)

## Testing in Staging
- [ ] **Deploy to staging environment first**
- [ ] **Test all critical functionality**
  - User login/logout
  - Content creation/editing
  - Form submissions (CSRF validation)
  - File uploads
  - Admin panel access
- [ ] **Run security audit in staging**
- [ ] **Check for PHP errors in logs**

## Final Verification
- [ ] **All security issues resolved** (audit tool shows 0 across all categories)
- [ ] **DEV_MODE = false** confirmed
- [ ] **Secrets rotated** and secure
- [ ] **Backups created** and verified
- [ ] **Test endpoints removed or gated**
- [ ] **Documentation updated** with deployment notes
- [ ] **Team notified** of deployment schedule

## Post-Deployment
- [ ] **Monitor error logs** for first 24 hours
- [ ] **Verify HTTPS certificate** is valid
- [ ] **Test critical user flows** in production
- [ ] **Check performance metrics**
- [ ] **Document any issues** for future deployments
