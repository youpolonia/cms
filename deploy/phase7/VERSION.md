# Phase 7 Deployment Package

**Version:** 7.0.0
**Date:** 2025-05-22
**Status:** Release

## Contents:
- **ContentLifecycleManager:** Core service for managing content versions, scheduling, and lifecycle events. ([`services/ContentLifecycleManager.php`](services/ContentLifecycleManager.php))
- **GDPR SQL Procedures:**
    - MySQL: [`deploy/phase7/database/procedures/mysql/gdpr_data_purge.sql`](deploy/phase7/database/procedures/mysql/gdpr_data_purge.sql)
    - SQL Server: [`deploy/phase7/database/procedures/sqlserver/gdpr_data_purge.sql`](deploy/phase7/database/procedures/sqlserver/gdpr_data_purge.sql)
- **Versioned Migration Files (Phase 7):**
    - [`database/migrations/phase7/2025_05_22_162800_create_content_schedules_table.php`](database/migrations/phase7/2025_05_22_162800_create_content_schedules_table.php)
    - [`database/migrations/phase7/2025_05_22_162930_add_lifecycle_columns_to_content_versions_table.php`](database/migrations/phase7/2025_05_22_162930_add_lifecycle_columns_to_content_versions_table.php)
    - [`database/migrations/phase7/2025_05_22_163000_create_content_lifecycle_logs_table.php`](database/migrations/phase7/2025_05_22_163000_create_content_lifecycle_logs_table.php)
- **Verification Script:** [`deploy/phase7/scripts/verify_deployment.php`](deploy/phase7/scripts/verify_deployment.php) - Validates key components of the deployment.
- **Atomic Deployment Pattern:** This `VERSION.md` file serves as a marker. The deployment process should create a new versioned directory (e.g., `releases/7.0.0`) and then update a symbolic link (e.g., `current_release`) to point to this new directory once verification passes.

## Deployment Steps (Conceptual for FTP):
1. Create a new directory on the server: `releases/phase7_YYYYMMDD_HHMMSS` (e.g., `releases/phase7_20250522_170000`).
2. Upload all contents of this package (excluding this `VERSION.md` if preferred, or include it) into the new versioned directory.
3. Run the verification script: `php releases/phase7_YYYYMMDD_HHMMSS/scripts/verify_deployment.php`.
4. If verification passes:
    a. (Optional) Backup the current live application directory.
    b. Update the symbolic link (or rename directories if symlinks are not available/reliable on shared hosting) that points your web server's document root to this new versioned directory. For example, if `public_html/cms` is live, rename `public_html/cms` to `public_html/cms_old_timestamp` and then rename `releases/phase7_YYYYMMDD_HHMMSS` to `public_html/cms`.
5. If verification fails, do not switch the live version. Investigate logs from `verify_deployment.php`.

## Rollback (Conceptual for FTP):
1. If the new version causes issues, revert the symbolic link (or directory rename) to point back to the previous stable versioned directory.
2. Investigate issues based on error logs.

This package is designed for FTP deployment on shared hosting, avoiding CLI tools like Composer or npm.