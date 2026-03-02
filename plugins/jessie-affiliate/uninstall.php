<?php
/**
 * Uninstall — JessieAffiliate
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieAffiliate...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['affiliate_programs', 'affiliate_affiliates', 'affiliate_conversions', 'affiliate_payouts', 'affiliate_links'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ JessieAffiliate uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
