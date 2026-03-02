<?php
/**
 * Uninstall — JessieLms
 * Removes all plugin data and tables. This action is irreversible.
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/db.php';

echo "Uninstalling JessieLms...\n";

try {
    $pdo = db();

    // Drop plugin tables
    $tables = ['lms_courses', 'lms_lessons', 'lms_quizzes', 'lms_enrollments', 'lms_progress', 'lms_reviews', 'lms_certificates'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "  Dropped table: $table\n";
    }

    echo "\n✅ JessieLms uninstalled successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
