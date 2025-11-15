<?php
/**
 * Basic Deployment Script for CMS
 *
 * This script is intended to be run on the server to deploy updates.
 * It handles running migrations and clearing caches.
 *
 * Usage (CLI): php scripts/deployment/deploy.php
 *
 * @package CMS
 * @subpackage Scripts\Deployment
 */

// Ensure this script is run from CLI or a trusted environment
if (php_sapi_name() !== 'cli' && !defined('CMS_DEPLOYMENT_ACCESS_GRANTED')) {
    http_response_code(403); // Forbidden
    die("This script can only be run from the command line or an authorized context.\n");
}

echo "Starting deployment...\n";

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
http_response_code(403);
echo "Disabled: deploy.php not permitted (framework bootstrap).";
return;

// --- 1. Pull Latest Code (Placeholder) ---
echo "Step 1: Pulling latest code (Manual Step Reminder)\n";
echo "--------------------------------------------------\n";
echo "Please ensure you have pulled the latest code from your Git repository.\n";
echo "e.g., git pull origin main\n";
echo "--------------------------------------------------\n\n";
// In a shared hosting environment without shell access for PHP, this step is typically manual.
// If shell_exec is available and secure, a git pull could be attempted, but it's risky.

// --- 2. Run Database Migrations ---
echo "Step 2: Running Database Migrations...\n";
try {
    require_once __DIR__ . '/../../core/database.php';
    // Required Database classes
    require_once CMS_ROOT . '/includes/database/databaseconnection.php';
    require_once CMS_ROOT . '/includes/database/migrator.php';
    require_once CMS_ROOT . '/includes/database/migration.php';
    require_once CMS_ROOT . '/includes/database/schema.php';
    require_once CMS_ROOT . '/includes/database/databaseexception.php';

    $pdo = \core\Database::connection();

    // We can specify different migration paths if needed, e.g. for phases
    // For now, using the main migrations directory.
    $migrator = new Migrator($pdo, CMS_ROOT . '/database/migrations/');
    $migrator->migrate(); // This will run all pending migrations from all subfolders too

    echo "Database migrations completed successfully.\n\n";

} catch (PDOException $e) {
    echo "DATABASE ERROR DURING MIGRATIONS: " . $e->getMessage() . "\n";
    error_log("Deployment Script - Database Migration Error: " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    echo "ERROR DURING MIGRATIONS: " . $e->getMessage() . "\n";
    error_log("Deployment Script - Migration Error: " . $e->getMessage());
    exit(1);
}

// --- 3. Clear Cache (Placeholder) ---
echo "Step 3: Clearing Cache...\n";
// Placeholder for cache clearing logic.
// This might involve deleting files from storage/framework/cache/
// or calling a specific cache clearing function if one exists.
// Example:
// if (function_exists('clear_cms_cache')) {
//     clear_cms_cache();
//     echo "Application cache cleared.\n";
// } else {
//     echo "Cache clearing function not found. Manual cache clearing may be required.\n";
// }
// For file-based cache:
$cacheDir = CMS_ROOT . '/storage/framework/cache/data'; // Assuming subfolder 'data' for actual cache files
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*'); // Get all file names
    foreach($files as $file){ // Iterate files
      if(is_file($file) && basename($file) !== '.gitkeep') {
        unlink($file); // Delete file
      }
    }
    echo "File cache in " . $cacheDir . " cleared (excluding .gitkeep).\n";
} else {
    echo "Cache directory " . $cacheDir . " not found.\n";
}
echo "Cache clearing step completed.\n\n";


// --- 4. Other Post-Deployment Tasks (Placeholder) ---
echo "Step 4: Other Post-Deployment Tasks (Placeholder)...\n";
// e.g., updating sitemap, notifying services, etc.
echo "No other post-deployment tasks configured yet.\n\n";


echo "Deployment script finished.\n";
echo "Please verify the application is working as expected.\n";
