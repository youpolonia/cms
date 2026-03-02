<?php
/**
 * Uninstall — JessieNewsletter
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieNewsletter...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['newsletter_lists', 'newsletter_subscribers', 'newsletter_campaigns', 'newsletter_campaign_stats', 'newsletter_automations', 'newsletter_templates'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ JessieNewsletter uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
