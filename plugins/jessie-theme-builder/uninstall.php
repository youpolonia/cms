<?php
/**
 * Uninstall — JessieThemeBuilder
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieThemeBuilder...\n";

try {
    $pdo = db();

    // This plugin uses shared tables — no tables to drop
    echo "  No dedicated tables to remove.\n";

    echo "\n✅ JessieThemeBuilder uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
