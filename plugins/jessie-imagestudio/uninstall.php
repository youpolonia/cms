<?php
/**
 * Uninstall — Jessie Image Studio
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling Jessie Image Studio...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['imagestudio_images', 'imagestudio_jobs'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ Jessie Image Studio uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
