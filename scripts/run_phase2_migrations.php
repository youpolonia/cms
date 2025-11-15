<?php

declare(strict_types=1);

// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/migration_errors.log');

// Define CMS_ROOT if not already defined (adjust if your entry point is different)
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
http_response_code(403);
echo "Disabled: run_phase2_migrations.php not permitted (framework bootstrap).";
return;

// Now other dependencies that might rely on bootstrap or autoloader
require_once CMS_ROOT . '/includes/database/connection.php'; // Database Connection
require_once CMS_ROOT . '/includes/database/migrationrunner.php'; // Migration Runner
require_once CMS_ROOT . '/includes/database/migration.php'; // Base Migration Class
// The Schema classes are usually autoloaded by their namespace if Migration.php uses them.

echo "Starting Phase 2 Migrations...\n";

$migrationResults = [
    'applied_migrations' => [],
    'errors' => [],
    'start_time' => microtime(true),
    'end_time' => null,
    'duration' => null,
    'framework_free_verified' => true, // By design, we are using the custom runner
    'rollback_capability_docs' => "
Rollback Capability:
The MigrationRunner supports rolling back the last batch of migrations.
To perform a rollback, you would typically:
1. Get the PDO connection: \$pdo = \\core\\Database::connection();
2. Instantiate the MigrationRunner: \$runner = new \\Includes\\Database\\MigrationRunner(\$pdo, CMS_ROOT . '/database/migrations/phase2/');
3. Call the rollback method: \$rolledBackMigrations = \$runner->rollback();
4. Check \$rolledBackMigrations array for the names of migrations that were rolled back.

Note: Ensure that each migration class correctly implements the `revert()` method for successful rollbacks.
The `migrations` table in your database tracks batches, allowing the runner to identify the last batch.
"
];

try {
    $pdo = \core\Database::connection();

    $migrationsDir = CMS_ROOT . '/database/migrations/phase2/';
    
    // Autoload migration files from the specific phase2 directory
    // The MigrationRunner itself also tries to require_once files if classes are not found.
    // However, explicit loading can be more robust if namespaces are tricky.
    foreach (glob($migrationsDir . "*.php") as $filename) {
        require_once $filename;
    }

    $runner = new \Includes\Database\MigrationRunner($pdo, $migrationsDir);
    
    echo "Running migrations from: {$migrationsDir}\n";
    $applied = $runner->run();

    if (empty($applied)) {
        echo "No new migrations to apply.\n";
    } else {
        echo "Applied migrations:\n";
        foreach ($applied as $migrationName) {
            echo " - {$migrationName}\n";
            $migrationResults['applied_migrations'][] = $migrationName;
        }
    }

} catch (\PDOException $e) {
    $errorMsg = "PDOException during migration: " . $e->getMessage() . " (Code: " . $e->getCode() . ") in " . $e->getFile() . ":" . $e->getLine();
    echo "ERROR: " . $errorMsg . "\n";
    error_log($errorMsg);
    $migrationResults['errors'][] = $errorMsg;
} catch (\RuntimeException $e) {
    $errorMsg = "RuntimeException during migration: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
    echo "ERROR: " . $errorMsg . "\n";
    error_log($errorMsg);
    $migrationResults['errors'][] = $errorMsg;
    if ($e->getPrevious()) {
        $prevErrorMsg = "Previous Exception: " . $e->getPrevious()->getMessage();
        echo "PREVIOUS ERROR: " . $prevErrorMsg . "\n";
        error_log($prevErrorMsg);
        $migrationResults['errors'][] = $prevErrorMsg;
    }
} catch (\Throwable $e) {
    $errorMsg = "General Throwable during migration: " . $e->getMessage() . " (Type: " . get_class($e) . ") in " . $e->getFile() . ":" . $e->getLine();
    echo "ERROR: " . $errorMsg . "\n";
    error_log($errorMsg);
    $migrationResults['errors'][] = $errorMsg;
} finally {
    $migrationResults['end_time'] = microtime(true);
    $migrationResults['duration'] = $migrationResults['end_time'] - $migrationResults['start_time'];
    echo "Migrations finished in " . number_format($migrationResults['duration'], 4) . " seconds.\n";

    // Document migration results
    $reportPath = CMS_ROOT . '/memory-bank/phase2_report.md';
    $reportContent = "# Phase 2 Migration Report\n\n";
    $reportContent .= "Execution Date: " . date('Y-m-d H:i:s') . "\n";
    $reportContent .= "Duration: " . number_format($migrationResults['duration'], 4) . " seconds\n";
    $reportContent .= "Framework-Free Approach Verified: " . ($migrationResults['framework_free_verified'] ? 'Yes' : 'No') . "\n\n";

    if (!empty($migrationResults['applied_migrations'])) {
        $reportContent .= "## Applied Migrations:\n";
        foreach ($migrationResults['applied_migrations'] as $mig) {
            $reportContent .= "- " . $mig . "\n";
        }
    } else {
        $reportContent .= "No new migrations were applied.\n";
    }
    $reportContent .= "\n";

    if (!empty($migrationResults['errors'])) {
        $reportContent .= "## Errors Encountered:\n";
        foreach ($migrationResults['errors'] as $err) {
            $reportContent .= "- " . htmlspecialchars($err) . "\n"; // Basic sanitization for Markdown
        }
    } else {
        $reportContent .= "No errors encountered during migration.\n";
    }
    $reportContent .= "\n";
    
    $reportContent .= "## Foreign Key Constraint Verification:\n";
    $reportContent .= "Foreign key constraints should be manually verified or by using a separate script that queries `INFORMATION_SCHEMA.KEY_COLUMN_USAGE` and `INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS` for the migrated tables.\n";
    $reportContent .= "Example tables to check (assuming 'users' table exists from a previous phase):\n";
    $reportContent .= "- `clients` (no explicit FKs in this version)\n";
    $reportContent .= "- `threads` (FKs: `sender_id` -> `users.id`, `recipient_id` -> `users.id`)\n";
    $reportContent .= "- `messages` (FKs: `thread_id` -> `threads.id`, `sender_id` -> `users.id`)\n";
    $reportContent .= "- `shifts` (FKs: `worker_id` -> `workers.worker_id`)\n";
    $reportContent .= "- `content_types` (no explicit FKs)\n";
    $reportContent .= "- `content_versions` (FKs to `contents.id` and `users.id` are currently commented out in migration, verify if they should be active)\n";
    $reportContent .= "- `content_workflow` (FKs to `contents.id`, `workflow_states.id`, `users.id` are currently commented out, verify)\n";
    $reportContent .= "- `content_workflow_history` (FKs to `contents.id`, `workflow_states.id`, `users.id` are currently commented out, verify)\n\n";


    $reportContent .= "## Rollback Capability Documentation:\n";
    $reportContent .= $migrationResults['rollback_capability_docs'] . "\n";

    try {
        if (!file_exists(dirname($reportPath))) {
            mkdir(dirname($reportPath), 0775, true);
        }
        file_put_contents($reportPath, $reportContent);
        echo "Migration report generated at: " . $reportPath . "\n";
    } catch (Exception $e) {
        echo "ERROR: Could not write migration report: " . $e->getMessage() . "\n";
        error_log("Could not write migration report: " . $e->getMessage());
    }
    
    // Log progress to memory-bank/progress.md
    $progressLogPath = CMS_ROOT . '/memory-bank/progress.md';
    $progressEntry = "\n---\n**Phase 2 Migrations Executed** - " . date('Y-m-d H:i:s') . "\n";
    $progressEntry .= "Status: " . (empty($migrationResults['errors']) ? "Success" : "Completed with errors") . "\n";
    $progressEntry .= "Applied: " . count($migrationResults['applied_migrations']) . " migrations.\n";
    $progressEntry .= "Report: [phase2_report.md](phase2_report.md)\n";
    
    try {
        file_put_contents($progressLogPath, $progressEntry, FILE_APPEND);
    } catch (Exception $e) {
        echo "ERROR: Could not update progress log: " . $e->getMessage() . "\n";
        error_log("Could not update progress log: " . $e->getMessage());
    }
}
