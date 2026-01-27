# CURRENT TASK: Fix Layout Library Upload Error

## PROBLEM
When uploading a JSON file to Layout Library, this error appears:

Database error: SQLSTATE[01000]: Warning: 1265 Data truncated for column category at row 1

## FILES TO INVESTIGATE
1. /var/www/html/cms/app/controllers/admin/layoutlibrarycontroller.php - method upload() around lines 180-210
2. Table tb_layout_library - check structure of category column (likely ENUM)

## STEPS
1. Run: mysql -u cms_user -psecure_password_123 cms -e DESCRIBE tb_layout_library;
2. Check what values are allowed in category column (ENUM values)
3. Compare with default value general in controller (line ~196)
4. Fix controller to use correct default value from ENUM

## TEST
Upload via: http://localhost/admin/layout-library -> Upload Layout

## CMS RULES
- Pure PHP 8.1+, NO CLI commands in code, FTP-only deployment
- Database::connection() singleton pattern
- require_once only
- CSRF required for all forms