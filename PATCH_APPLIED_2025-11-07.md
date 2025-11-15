# Patch Applied: Fix /page 500 Error - 2025-11-07

## Summary
Fixed 500 errors on `/page/{slug}`, `/about`, and `/contact` routes by creating the missing `pages` table, removing legacy redirects, and hardening dispatch & controller error handling.

## Changes Applied

### 1. Created Pages Table Tool
**File**: `/admin/tools/create_pages_table.php`
- DEV_MODE gated table creation script
- Creates `pages` table with proper schema
- Seeds initial "about" and "contact" pages
- Implements proper error handling and logging

### 2. Updated Route Configuration
**File**: `/routes_custom/web.php`
- Removed legacy redirects for `/about` and `/contact`
- Changed from 302 redirects to direct page_controller calls
- Now serves pages directly from database

**File**: `/routes.php`
- Condensed `/about` and `/contact` route definitions
- Maintains both `get()` and `router_get()` function compatibility
- Removed redirect logic

### 3. Hardened Page Controller
**File**: `/controllers/page_controller.php`
- Added try-catch error handling
- Set PDO error mode to ERRMODE_EXCEPTION
- Fixed database table detection logic (using SHOW TABLES)
- Added error logging with context
- Fixed require path to use lowercase `database.php`
- Returns 500 status with 'Error' message on exceptions

### 4. Enhanced Public Dispatch
**File**: `/public/index.php`
- Added route file loading before dispatch
- Loads both `routes.php` and `routes_custom/web.php`
- Wrapped entire dispatch in try-catch
- Added error logging for dispatch failures
- Returns 500 status with 'Error' message on exceptions

## Database Schema Created

```sql
CREATE TABLE `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(191) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'published',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Initial Data Seeded

- **about**: "About page — initial content."
- **contact**: "Contact page — initial content."

## Testing Results

All routes now return 200 OK:
- ✅ `GET /about` → 200 (serves from pages table)
- ✅ `GET /contact` → 200 (serves from pages table)
- ✅ `GET /page/about` → 200 (serves from pages table)
- ✅ `GET /page/contact` → 200 (serves from pages table)
- ✅ `GET /` → 200 (homepage still works)

## Code Standards Compliance

- ✅ Pure PHP, no frameworks
- ✅ FTP-deployable (no CLI required)
- ✅ Uses `require_once` only
- ✅ UTF-8 encoding, no BOM
- ✅ No closing `?>` tags
- ✅ Exactly one trailing newline per file
- ✅ Prepared statements for all queries
- ✅ HTML escaping via `htmlspecialchars()`
- ✅ Error logging with context

## Security Features

- DEV_MODE gate on admin tools
- Prepared statements prevent SQL injection
- HTML escaping prevents XSS
- Graceful error handling (no stack trace exposure)
- Status-based page filtering (only 'published' pages shown)
- Table name validation (whitelist approach)

## Notes

- The `pages` table supports both `pages` and `cms_pages` table names for backward compatibility
- Route loading is now explicit in `public/index.php` (bootstrap.php has it disabled)
- Error messages are generic in production to avoid information disclosure
- All database errors are logged to `/logs/php_errors.log`
