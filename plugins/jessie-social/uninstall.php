<?php
/**
 * Uninstall — Jessie Social Media Scheduler
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling Jessie Social Media Scheduler...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['social_accounts', 'social_posts', 'social_templates', 'social_analytics'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ Jessie Social Media Scheduler uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
