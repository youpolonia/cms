<?php
/**
 * Cleanup Temporary Database Scripts
 * Removes sensitive utility files after database setup
 */

$filesToRemove = [
    __DIR__ . '/reset_mysql_password.php',
    __DIR__ . '/setup_cms_database.php',
    __FILE__ // This script will delete itself too
];

foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Removed: $file\n";
    }
}

echo "Cleanup completed. All temporary scripts removed.\n";
