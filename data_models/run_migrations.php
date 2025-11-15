<?php
/**
 * Pure PHP Migration Runner
 */

require_once __DIR__ . '/../core/bootstrap.php';

$migrations = [
    '2025_05_17_000000_create_batch_jobs_table',
    '2025_05_17_000001_create_batch_job_items_table'
];

$connection = \core\Database::connection();

foreach ($migrations as $migration) {
    require_once __DIR__."/migrations/$migration.php";
    $class = str_replace('_', '', ucwords($migration, '_'));
    $migration = new $class();
    
    try {
        echo "Running migration: $migration\n";
        $migration->migrate($connection);
        echo "Migration completed successfully\n";
    } catch (Exception $e) {
        echo "Migration failed: ".$e->getMessage()."\n";
        exit(1);
    }
}

echo "All migrations completed successfully\n";
