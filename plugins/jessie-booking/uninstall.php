<?php
/**
 * Uninstall — JessieBooking
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieBooking...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['booking_services', 'booking_staff', 'booking_appointments', 'booking_slots'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ JessieBooking uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
