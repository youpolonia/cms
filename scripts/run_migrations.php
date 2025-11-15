<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';

// Simple CLI Migration Runner for Pure PHP CMS

// --- Configuration & Setup ---
define('CMS_ROOT', dirname(__DIR__)); // Define CMS_ROOT for scripts

// Define CONFIG_PATH as literal (required for require_once at line 34)
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', __DIR__ . '/../config/database.php');
}

// Explicitly require_once the Database class needed by some migrations
$databaseClassPath = CMS_ROOT . '/includes/database/database.php';
if (file_exists($databaseClassPath)) {
    $dbBase = realpath(CMS_ROOT . '/includes/database');
    $dbTarget = realpath($databaseClassPath);
    if (!$dbTarget || !str_starts_with($dbTarget, $dbBase . DIRECTORY_SEPARATOR) || !is_file($dbTarget)) {
        error_log("SECURITY: blocked dynamic include: database.php");
        echo "Error: Invalid database class path.\n";
        exit(1);
    }
    require_once $dbTarget;
} else {
    echo "Warning: Essential Database class not found at {$databaseClassPath}. Migrations using it will fail.\n";
}

define('MIGRATIONS_PATH', __DIR__ . '/../database/migrations');
// Legacy path deprecated. DB is centralized in core/database.php (single source of truth)
define('RUNNING_MIGRATIONS', true); // Define a constant for migration scripts to check

if (!file_exists(CONFIG_PATH)) {
    echo "Error: Database configuration file not found at " . CONFIG_PATH . "\n";
    exit(1);
}

// Validate CONFIG_PATH before requiring
$cfg = realpath(CONFIG_PATH);
if (!$cfg || !is_file($cfg)) {
    echo "Error: Invalid database configuration path.\n";
    http_response_code(500);
    exit(1);
}
$dbConfigs = require_once $cfg;

// --- Database Connection ---
try {
    $connectionName = $dbConfigs['default_connection'] ?? 'mysql';
    $config = $dbConfigs[$connectionName] ?? null;

    if (!$config) {
        throw new \Exception("Database configuration for '{$connectionName}' not found.");
    }

    $dbHost = $config['host'] ?? 'localhost';
    $dbName = $config['database'] ?? 'cms_database';
    $dbUser = $config['username'] ?? 'cms_user';
    $dbPass = $config['password'] ?? ''; // Default to empty string if not set
    $dbCharset = $config['charset'] ?? 'utf8mb4';

    $pdo = \core\Database::connection();
} catch (\PDOException $e) {
    echo "Error: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// --- Migrations Table Setup ---
try {
    $pdo->prepare("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ")->execute();
} catch (\PDOException $e) {
    echo "Error: Could not create migrations table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration runner initialized. PDO connection successful. Migrations table checked/created.\n";

// Initialize the static Database connection if the class exists
if (class_exists('Includes\Database\Database')) {
    try {
        // Use the same config as the main PDO connection for consistency
        $staticDbConfig = $dbConfigs[$connectionName] ?? null;
        if ($staticDbConfig) {
            \Includes\Database\Database::connect($staticDbConfig);
            echo "Static Database class connected successfully.\n";
        } else {
            echo "Warning: Configuration for static Database class not found. Migrations relying on it may fail.\n";
        }
    } catch (\Throwable $e) {
        echo "Warning: Failed to connect static Database class: " . $e->getMessage() . ". Migrations relying on it may fail.\n";
    }
}

// --- Get Applied Migrations ---
$stmt = $pdo->query("SELECT migration FROM migrations ORDER BY migration ASC");
$appliedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

$ignoredMigrationFiles = [
    '2025_05_15_000000_create_migrations_table.php', // Redundant and uses SchemaBuilder
];

// --- Scan for Migration Files ---
$allMigrationFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(MIGRATIONS_PATH, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if ($file->isFile() && (strtolower($file->getExtension()) === 'php' || strtolower($file->getExtension()) === 'sql')) {
        // Use path relative to MIGRATIONS_PATH as the identifier
        $relativePath = $iterator->getSubPathName(); // Corrected: Call on the iterator, not the SplFileInfo object
        $allMigrationFiles[] = $relativePath;
    }
}
sort($allMigrationFiles); // Initial sort (good for files within the same directory level)

// Custom sort to prioritize phased migrations (phase1, phase2, etc.) then root files
usort($allMigrationFiles, function ($a, $b) {
    $aPhase = 0; $bPhase = 0;
    $aIsPhased = preg_match('#^phase(\d+)/#i', $a, $matchesA);
    $bIsPhased = preg_match('#^phase(\d+)/#i', $b, $matchesB);

    if ($aIsPhased) $aPhase = (int)$matchesA[1];
    if ($bIsPhased) $bPhase = (int)$matchesB[1];

    if ($aIsPhased && !$bIsPhased) return -1; // Phased 'a' comes before non-phased 'b'
    if (!$aIsPhased && $bIsPhased) return 1;  // Phased 'b' comes before non-phased 'a'
    
    // If both are phased, sort by phase number, then by name
    if ($aIsPhased && $bIsPhased) {
        if ($aPhase !== $bPhase) {
            return $aPhase <=> $bPhase;
        }
    }
    // If both non-phased, or same phase, sort by full path string (already somewhat done by initial sort)
    return strcmp($a, $b);
});

// --- Determine and Run New Migrations ---
$newMigrationsRun = 0;

// Array of critical base migrations to force run if they were previously marked applied but might be missing
$forceRunMigrations = [
    '0000_create_core_auth_tables.php', // users table
    'phase1/0001_create_workers_table.php', // workers table (dependency for worker_metrics, worker_notifications)
    'phase1/0003_create_worker_notifications.php', // worker_notifications table
    '2025_05_16_021300_create_versions_table.php', // versions table (dependency for version_content and version_metadata)
    'phase6/0002_create_versions_table.php', // Critical for Phase 6 FKs, ensures 'versions' table exists
    '2025_05_16_021400_create_version_metadata_table.php', // version_metadata table
    '2025_05_16_021500_create_version_content_table.php', // version_content table
    'phase2/0001_create_clients_table.php', // clients table (dependency for client_category_mapping)
    'phase2/0004_create_workflow_states_table.php', // workflow_states table
    'phase2/0005_create_content_workflow_table.php', // content_workflow table
    'phase2/0006_create_content_workflow_history_table.php', // content_workflow_history table
    'phase3/0002_create_notification_categories_table.php', // notification_categories (dependency for notifications)
    'phase3/0001_create_notifications_table.php', // notifications table
    'phase6/0004_create_workflow_steps_table.php', // workflow_steps table (dependency for workflow_transitions)
    'phase6/0009_create_workflow_conditions_table.php', // workflow_conditions table (dependency for workflow_condition_evaluations)
    '2025_05_15_191501_create_workflows_table.php', // workflows table (dependency for workflow_steps)
    'phase4/0001_create_distribution_channels_table.php', // distribution_channels table
    'phase4/0002_create_distribution_rules_table.php', // distribution_rules table
    'phase6/0005_create_workflow_transitions.php', // workflow_transitions table
    'phase6/0005_create_workflow_step_transitions_table.php', // workflow_step_transitions table
    'phase6/0006_create_workflow_logs.php', // workflow_logs table
    'phase6/0007_create_workflow_executions_table.php', // workflow_executions table
    'phase6/0006_create_workflow_step_logs_table.php', // workflow_step_logs table
    'phase6/0008_create_workflow_triggers_table.php', // workflow_triggers table
    'phase6/0010_create_workflow_condition_evaluations_table.php', // workflow_condition_evaluations table
    'phase6/0011_create_workflow_action_definitions_table.php', // workflow_action_definitions table
    'phase6/0012_create_workflow_action_executions_table.php', // workflow_action_executions table
    'phase6/0013_create_workflow_variables_table.php', // workflow_variables table
    'phase6/0013_create_workflow_webhooks_table.php', // workflow_webhooks table
    'phase6/0014_create_workflow_api_endpoints_table.php', // workflow_api_endpoints table
    'phase6/0014_create_workflow_variable_history_table.php', // workflow_variable_history table
    'phase6/0015_create_workflow_notifications_table.php', // workflow_notifications table
    'phase6/0015_create_workflow_schedules_table.php', // workflow_schedules table
    '2025_05_17_000000_create_batch_jobs_table.php', // batch_jobs table
    'phase5/0001_create_content_schedules_table.php', // Ensure this is re-run to pick up column type changes
    'phase2/0005_create_scheduled_notifications_table.php', // scheduled_notifications table (dependency for workflow_monitoring)
    'phase1/0006_create_core_content_tables.php', // contents table, ensure it's forced after users/sites
    // Add other critical base table migrations here if needed
];

// --- Pre-run critical dependency migrations if necessary ---
$criticalPrerequisites = [
    '2025_05_15_000000_create_sites_table.php', // Must run first: sites table, dependency for users and content_items
    '0000_create_core_auth_tables.php', // Must run second: users table (depends on sites, provides for content_items)
    'phase1/0006_create_core_content_tables.php', // Must run third: content_items (depends on users and sites)
    // Phase 4 dependencies for Phase 1's distribution_schedules (if any, check dependencies)
    'phase4/0001_create_distribution_channels_table.php',
    'phase4/0002_create_distribution_rules_table.php',
    // Phase 3 dependencies
    'phase3/0002_create_notification_categories_table.php', // Must run before notifications table
];

foreach ($criticalPrerequisites as $critMigrationFile) {
    $isCritApplied = in_array($critMigrationFile, $appliedMigrations);
    $isCritForced = in_array($critMigrationFile, $forceRunMigrations);

    if (!$isCritApplied || $isCritForced) {
        echo "Pre-processing critical prerequisite migration: {$critMigrationFile}...\n";
        $critFilePath = MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $critMigrationFile;
        if (!file_exists($critFilePath)) {
            echo "Error: Critical prerequisite migration file not found: {$critFilePath}\n";
            exit(1);
        }

        $critForeignKeysDisabled = false;
        try {
            if ($isCritForced && $isCritApplied) {
                $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
                $critForeignKeysDisabled = true;
                echo "Temporarily disabled FOREIGN_KEY_CHECKS for forced pre-run of {$critMigrationFile}\n";
            }

            // Handle class-based or array-based critical prerequisites
            $critBase = realpath(MIGRATIONS_PATH);
            $critTarget = realpath($critFilePath);
            if (!$critTarget || !str_starts_with($critTarget, $critBase . DIRECTORY_SEPARATOR) || !is_file($critTarget)) {
                error_log("SECURITY: blocked dynamic include: critical migration");
                throw new Exception("Invalid critical migration path: {$critMigrationFile}");
            }
            $migrationData = require_once $critTarget;

            if (is_array($migrationData) && isset($migrationData['up']) && is_callable($migrationData['up'])) {
                // Array-based migration with 'up' closure
                $migrationData['migrate']($pdo);
            } elseif (is_object($migrationData) && method_exists($migrationData, 'up')) {
                // Anonymous class instance returned
                $migrationData->migrate($pdo);
            } else {
                // Try to detect named class
                $critContent = file_get_contents($critFilePath);
                if (preg_match('/class\s+(\w+)\s*{/', $critContent, $critMatches)) {
                    $critClassName = $critMatches[1];
                    // Ensure class is loaded if not already (require_once might have been done by main loop for non-critical)
                    if (!class_exists($critClassName, false)) {
                         // We require it here again just in case, as pre-reqs run before main loop's require_once
                        $loadBase = realpath(MIGRATIONS_PATH);
                        $loadTarget = realpath($critFilePath);
                        if (!$loadTarget || !str_starts_with($loadTarget, $loadBase . DIRECTORY_SEPARATOR) || !is_file($loadTarget)) {
                            error_log("SECURITY: blocked dynamic include: crit-class-load");
                            throw new Exception("Invalid critical migration path for class load: {$critMigrationFile}");
                        }
                        require_once $loadTarget; // Use require_once to prevent multiple inclusions
                    }
                    if (class_exists($critClassName)) {
                        // Check for static up method first
                        if (method_exists($critClassName, 'up') && (new ReflectionMethod($critClassName, 'up'))->isStatic()) {
                            call_user_func([$critClassName, 'migrate'], $pdo);
                        } else {
                            // Fallback to instance method if static 'up' not found or not static
                            $critInstance = new $critClassName();
                            if (method_exists($critInstance, 'up')) {
                                $critInstance->migrate($pdo);
                            } else {
                                throw new Exception("Critical migration class {$critClassName} has no callable 'up' method.");
                            }
                        }
                    } else {
                        throw new Exception("Could not find class {$critClassName} for critical migration {$critMigrationFile}.");
                    }
                } else {
                    throw new Exception("Critical migration {$critMigrationFile} is not in an expected class or array format for pre-run.");
                }
            }

            if ($critForeignKeysDisabled) {
                $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
                echo "Re-enabled FOREIGN_KEY_CHECKS for {$critMigrationFile}\n";
            }

            if (!$isCritApplied) {
                $critBatch = 1; // Or a specific pre-run batch number
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$critMigrationFile, $critBatch]);
                // Add to $appliedMigrations so the main loop correctly skips it unless forced again
                $appliedMigrations[] = $critMigrationFile;
                echo "Successfully applied AND RECORDED critical prerequisite: {$critMigrationFile}\n";
            } else {
                echo "Successfully RE-APPLIED (forced) critical prerequisite (already recorded): {$critMigrationFile}\n";
            }
        } catch (\Throwable $e) {
            if ($critForeignKeysDisabled) {
                try { $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute(); } catch (\Exception $_e) {}
            }
            echo "Error pre-processing critical migration {$critMigrationFile}: " . $e->getMessage() . "\n";
            echo "Trace: " . $e->getTraceAsString() . "\n";
            exit(1);
        }
    }
}
// --- End of Pre-run critical dependency migrations ---

foreach ($allMigrationFiles as $migrationFile) {
    if (in_array($migrationFile, $appliedMigrations) && !in_array($migrationFile, $forceRunMigrations)) {
        echo "Skipping already applied (and not forced): {$migrationFile}\n";
        continue; // Skip already applied migration unless it's in the force run list
    }

    if (in_array(basename($migrationFile), $ignoredMigrationFiles)) {
        echo "Skipping ignored migration: {$migrationFile}\n";
        // Optionally, still record it as "applied" (or "skipped") to prevent re-processing attempts
        // For now, just skipping. If it needs to be marked, we'd insert into migrations table here.
        continue;
    }

    echo "Applying migration: {$migrationFile}...\n";
    $filePath = MIGRATIONS_PATH . DIRECTORY_SEPARATOR . $migrationFile;
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    $wasOriginallyApplied = in_array($migrationFile, $appliedMigrations); // Check if it was marked applied at script start
    $isForcedToRun = in_array($migrationFile, $forceRunMigrations); // Check if it's in our force list

    try {
        $foreignKeysDisabled = false;
        if ($isForcedToRun && $wasOriginallyApplied) {
            // This is a forced re-run of a migration that was already marked as applied.
            // These are the migrations we modified to require_once "DROP TABLE IF EXISTS".
            // Disable FK checks to allow dropping tables referenced by other tables that might still exist.
            $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();
            $foreignKeysDisabled = true;
            echo "Temporarily disabled FOREIGN_KEY_CHECKS for forced re-run of {$migrationFile}\n";
        }
        
        // Transaction removed due to potential DDL auto-commit issues in MySQL
        // Each operation will now run more independently.

        if ($fileExtension === 'sql') {
            $sqlScript = file_get_contents($filePath);
            if ($sqlScript === false) {
                throw new Exception("Could not read SQL file: {$filePath}");
            }
            $pdo->prepare($sqlScript)->execute();
        } elseif ($fileExtension === 'php') {
            // For PHP files, we need to handle different patterns
            // Pattern 1: Simple script expecting global $db (less ideal)
            // Pattern 2: Class with up(PDO $db) method (preferred)

            // Attempt to detect class structure first
            $content = file_get_contents($filePath);
            
            // Check for "return new class" pattern first, as it's more specific for anonymous classes
            if (preg_match('/return\s+new\s+class\s*{/i', $content)) {
                $fileBase = realpath(MIGRATIONS_PATH);
                $fileTarget = realpath($filePath);
                if (!$fileTarget || !str_starts_with($fileTarget, $fileBase . DIRECTORY_SEPARATOR) || !is_file($fileTarget)) {
                    error_log("SECURITY: blocked dynamic include: migration");
                    throw new Exception("Invalid migration path: {$migrationFile}");
                }
                $instance = require_once $fileTarget; // 'require_once' will return the anonymous class instance
                if (is_object($instance) && method_exists($instance, 'up')) {
                    $instance->migrate($pdo);
                } else {
                    throw new Exception("Migration file {$migrationFile} returns an invalid object or an object without an 'up' method.");
                }
            } elseif (preg_match('/class\s+(\w+)\s*{/', $content, $matches)) { // Then check for named classes
                $className = $matches[1];
                $namedBase = realpath(MIGRATIONS_PATH);
                $namedTarget = realpath($filePath);
                if (!$namedTarget || !str_starts_with($namedTarget, $namedBase . DIRECTORY_SEPARATOR) || !is_file($namedTarget)) {
                    error_log("SECURITY: blocked dynamic include: named-class-migration");
                    throw new Exception("Invalid migration path for named class: {$migrationFile}");
                }
                require_once $namedTarget; // Include the file - it should define the class
                if (class_exists($className)) {
                    $instance = new $className();
                    if (method_exists($instance, 'up')) {
                        $instance->up($pdo); // Pass PDO instance
                    } else {
                        throw new Exception("Migration class {$className} in {$migrationFile} does not have an 'up' method.");
                    }
                } else {
                    throw new Exception("Could not find class {$className} in {$migrationFile} after including.");
                }
            } else {
                // Fallback: Assume it's a script expecting a global $db
                // This is less safe and might need refactoring of those migration files
                // For now, we make $pdo available globally for such scripts.
                $fallbackBase = realpath(MIGRATIONS_PATH);
                $fallbackTarget = realpath($filePath);
                if (!$fallbackTarget || !str_starts_with($fallbackTarget, $fallbackBase . DIRECTORY_SEPARATOR) || !is_file($fallbackTarget)) {
                    error_log("SECURITY: blocked dynamic include: fallback-script-migration");
                    throw new Exception("Invalid migration path for fallback script: {$migrationFile}");
                }
                require_once $fallbackTarget;      // Execute the script
            }
        }

        if ($foreignKeysDisabled) {
            $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
            echo "Re-enabled FOREIGN_KEY_CHECKS for {$migrationFile}\n";
        }

        // Record migration
        // Note: $wasOriginallyApplied was defined at the start of the try block
        if (!$wasOriginallyApplied) {
            $currentBatch = 1;
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationFile, $currentBatch]);
            echo "Successfully applied AND RECORDED migration: {$migrationFile}\n";
        } else {
            // This implies it was $isForcedToRun and $wasOriginallyApplied.
            echo "Successfully RE-APPLIED (forced) migration (already recorded): {$migrationFile}\n";
        }
        $newMigrationsRun++;
    } catch (\Throwable $e) {
        if ($foreignKeysDisabled) { // Check if FKs were disabled in this try block
            // Attempt to re-enable FK checks even if an error occurred during the migration logic
            try {
                $pdo->prepare("SET FOREIGN_KEY_CHECKS=1")->execute();
                echo "Re-enabled FOREIGN_KEY_CHECKS after error for {$migrationFile}\n";
            } catch (\Exception $_e) {
                // Log this failure but don't let it mask the original migration error
                error_log("Failed to re-enable FOREIGN_KEY_CHECKS after error for {$migrationFile}: {$_e->getMessage()}");
            }
        }
        echo "Error applying migration {$migrationFile}: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
        exit(1); // Stop on first error
    }
}

if ($newMigrationsRun > 0) {
    echo "All new migrations applied successfully ({$newMigrationsRun} migrations).\n";
} else {
    echo "No new migrations to apply. Database is up to date.\n";
}

exit(0);
