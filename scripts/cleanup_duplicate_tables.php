<?php

// Configuration
$dryRun = false; // Set to false for actual execution
$backupDir = 'database/backups/pre-cleanup-' . date('Ymd_His');

// Identify tables to preserve (most recent migration for each)
$preserveTables = [
    'users' => '2025_05_02_123950_create_test_users_table.php',
    'analytics_exports' => '2025_04_30_001200_create_analytics_exports_table.php',
    'content_version_diffs' => '2025_04_25_000000_create_content_version_diffs_table.php',
    'moderation_queue' => '2025_05_01_012900_create_moderation_queue_table.php',
    'moderation_analytics' => '2025_05_01_015400_create_moderation_analytics_table.php',
    'analytics_snapshots' => '2025_04_30_001100_create_analytics_snapshots_table.php',
    'version_analytics' => '2025_05_02_213900_create_version_analytics_table.php'
];

// Generate SQL with transaction safety
$sql = [];
$sql[] = "START TRANSACTION;";
$sql[] = "-- Backup commands (uncomment if needed)";
$sql[] = "-- CREATE DATABASE IF NOT EXISTS backup_db;";
$sql[] = "-- CREATE TABLE backup_db.migrations_backup AS SELECT * FROM migrations;";

foreach ($preserveTables as $table => $keepMigration) {
    $sql[] = "-- Preserving $table from $keepMigration";
    $sql[] = "-- DROP redundant duplicates for $table";
}

$sql[] = "\n-- Cleanup migrations table";
$sql[] = "DELETE FROM migrations WHERE migration LIKE '2025_04_30%' AND migration != '2025_04_30_000000_create_version_comparisons_table';";
$sql[] = "DELETE FROM migrations WHERE migration LIKE '2025_05_%' AND migration NOT IN (
    '2025_05_02_123950_create_test_users_table',
    '2025_05_01_012900_create_moderation_queue_table',
    '2025_05_01_015400_create_moderation_analytics_table',
    '2025_05_02_213900_create_version_analytics_table'
);";

if ($dryRun) {
    $sql[] = "-- ROLLBACK; -- Uncomment to execute";
    $sql[] = "-- COMMIT; -- Uncomment to execute changes";
} else {
    $sql[] = "COMMIT;";
}

file_put_contents('database/cleanup/duplicate_tables_cleanup.sql', implode("\n", $sql));

echo "Generated cleanup SQL script at database/cleanup/duplicate_tables_cleanup.sql\n";
echo "Configuration:\n";
echo "- Dry run: " . ($dryRun ? "ENABLED (will rollback)" : "DISABLED (will commit changes)") . "\n";
echo "- Backup dir: $backupDir\n";
echo "Review carefully before executing in production!\n";
