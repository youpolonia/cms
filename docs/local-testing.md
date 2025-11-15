# Local Testing Guide (Migration Manager)

This project is framework-free and FTP-only. For browser testing, ensure your web server serves the CMS root directory.

## 1) URL vs. DocumentRoot

- If your DocumentRoot **is the CMS root** (e.g., `/var/www/html/cms`), open:
  ```
  http://localhost:8000/admin/migration_manager.php
  ```

- If your DocumentRoot **is above the CMS root** (e.g., `/var/www/html`), open:
  ```
  http://localhost:8000/cms/admin/migration_manager.php
  ```

## 2) Built-in PHP Server

When using PHP's built-in server (`php -S`):
```bash
# From CMS root directory:
php -S localhost:8000 -t public/
```

Then access:
```
http://localhost:8000/admin/migration_manager.php
```

## 3) Port Conflicts

If you get "Address already in use" for port 8000:
1. Find and kill the process:
   ```bash
   lsof -i :8000
   kill -9 [PID]
   ```
2. Or use a different port:
   ```bash
   php -S localhost:9000 -t public/
   ```

## 4) Diagnostic Tools

Use these for troubleshooting:
- `admin/_migration_diag.php` - Shows path checks and dry-run output
- `admin/migration_manager.php` - Main interface for migrations

## 5) Testing Workflow

1. First run diagnostics:
   ```
   http://localhost:8000/admin/_migration_diag.php
   ```
2. Verify all paths show "yes" for exists/readable
3. Check dry-run output looks correct
4. Test actual migrations in the manager

## 6) Common Issues

**404 Errors:**
- Verify DocumentRoot matches your URL path
- Check file permissions (should be readable by web server)

**CSRF Errors:**
- Ensure you're logged into the admin area
- Refresh the page to get a new CSRF token

**Dry-run vs Execute:**
- Dry-run shows what would happen
- Execute makes actual database changes