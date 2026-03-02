<?php
/**
 * Uninstall — Jessie AI Copywriter
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling Jessie AI Copywriter...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['copywriter_brands', 'copywriter_content', 'copywriter_batches'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ Jessie AI Copywriter uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
