<?php
/**
 * Uninstall — Jessie Email Marketing
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling Jessie Email Marketing...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['em_lists', 'em_subscribers', 'em_campaigns', 'em_templates', 'em_events', 'em_automations'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ Jessie Email Marketing uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
