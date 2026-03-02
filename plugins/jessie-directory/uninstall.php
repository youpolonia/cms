<?php
/**
 * Uninstall — JessieDirectory
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieDirectory...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['directory_listings', 'directory_categories', 'directory_reviews', 'directory_claims'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ JessieDirectory uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
