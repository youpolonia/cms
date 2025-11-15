# CMS Deployment Guide

This guide outlines the steps to deploy updates to the CMS, particularly in a shared hosting environment where full CI/CD automation might be limited.

## Prerequisites

1.  **Git Access:** You need to be able to pull the latest code onto your server. This might be via SSH, a Git client, or your hosting provider's control panel.
2.  **PHP CLI Access:** The deployment script (`deploy.php`) is designed to be run via the PHP Command Line Interface (CLI).
3.  **Database Credentials:** Ensure your `config/database.php` file has the correct database credentials for the target environment.

## Deployment Steps

### 1. Backup Your Site (Recommended)

Before any deployment, it's highly recommended to back up your current site files and database.

### 2. Pull Latest Code

Connect to your server and navigate to the CMS root directory. Pull the latest changes from your Git repository.

```bash
cd /path/to/your/cms
git pull origin main # Or your relevant branch
```

If you do not have direct shell access to run `git pull`, use your hosting provider's interface or an FTP/SFTP client to upload the updated files. Ensure you overwrite existing files and upload any new ones. **Be careful not to overwrite your `config/database.php` or other environment-specific configuration files unless they are part of the update.**

### 3. Run the Deployment Script

Once the latest code is in place, run the `deploy.php` script from the CMS root directory using PHP CLI:

```bash
php scripts/deployment/deploy.php
```

This script will perform the following actions:
*   **Run Database Migrations:** It will apply any pending database schema changes.
*   **Clear Cache:** It will attempt to clear known file-based caches (e.g., in `storage/framework/cache/data/`).

### 4. Verify Application

After the script completes, thoroughly test your CMS to ensure everything is working as expected:
*   Check the frontend and admin panel.
*   Test key functionalities.
*   Review server error logs for any new issues.

## Troubleshooting

*   **Permissions:** Ensure the `storage/framework/cache/` and `storage/logs/` directories (and their subdirectories) are writable by the web server and PHP CLI user.
*   **PHP Version:** Ensure your server's PHP CLI version meets the project requirements (PHP 8.1+).
*   **Database Connection:** If migrations fail, double-check your `config/database.php` settings and ensure the database user has the necessary permissions (CREATE, ALTER, DROP, INSERT, SELECT, UPDATE, DELETE).
*   **Script Errors:** If the `deploy.php` script encounters errors, it will output messages to the console and may log more details in `storage/logs/error.log`.

## Manual Cache Clearing (If Needed)

If the automated cache clearing in the script is insufficient or if you suspect caching issues, you may need to manually clear caches. This typically involves deleting files from:
*   `storage/framework/cache/data/`
*   `storage/framework/views/` (if compiled views are stored there)

Always leave `.gitkeep` files intact.

## Future Enhancements (CI/CD Foundation)

This basic deployment script and guide form the foundation. Future improvements could involve:
*   More sophisticated cache management.
*   Hooks for pre/post deployment tasks.
*   Integration with version tagging.
*   If server environment allows, more automated `git pull` or webhook-triggered deployments.