<?php
require_once __DIR__ . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
/**
 * NOT LARAVEL. DO NOT use Schema::, up(), down(), Artisan, Composer, Illuminate, or CLI.
 * This runner supports DRY-RUN (default) and EXECUTE modes.
 * Invocable via: require_once __DIR__ . '/migrate.php';
 */

// Mode control - must be 'dry-run' or 'execute'
$mode = 'dry-run'; // Default to safe DRY-RUN mode

// 0) Load existing DB connection (must define $pdo as PDO). DO NOT modify credentials.
// Config already loaded above with DEV_MODE check

// Validate PDO presence without altering it.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "DRY-RUN: ERROR – PDO connection \$pdo not available from config.\n";
    return;
}

// 1) Prepare paths
$baseDir = __DIR__ . '/includes/migrations';
$registryFile = $baseDir . '/migration_registry.php';
$logFile = $baseDir . '/migrations_log.json';

// 2) Load registry (ordered list of basenames)
if (!file_exists($registryFile)) {
    echo "DRY-RUN: ERROR – migration_registry.php not found.\n";
    return;
}
$regBase = realpath($baseDir);
$regTarget = realpath($registryFile);
if (!$regTarget || !str_starts_with($regTarget, $regBase . DIRECTORY_SEPARATOR) || !is_file($regTarget)) {
    error_log("SECURITY: blocked dynamic include: migration_registry.php");
    echo "DRY-RUN: ERROR – invalid registry path.\n";
    return;
}
$registry = require_once $regTarget;
if (!is_array($registry)) {
    echo "DRY-RUN: ERROR – registry must return an array of basenames.\n";
    return;
}

// 3) Require base and each migration file (no autoloader)
$abstract = $baseDir . '/AbstractMigration.php';
if (!file_exists($abstract)) {
    echo "DRY-RUN: ERROR – AbstractMigration.php not found.\n";
    return;
}
$absBase = realpath($baseDir);
$absTarget = realpath($abstract);
if (!$absTarget || !str_starts_with($absTarget, $absBase . DIRECTORY_SEPARATOR) || !is_file($absTarget)) {
    error_log("SECURITY: blocked dynamic include: AbstractMigration.php");
    echo "DRY-RUN: ERROR – invalid abstract path.\n";
    return;
}
require_once $absTarget;

foreach ($registry as $basename) {
    $pathLower = $baseDir . '/' . strtolower($basename);
    $path = file_exists($pathLower) ? $pathLower : $baseDir . '/' . $basename;
    if (!file_exists($path)) {
        echo "DRY-RUN: WARNING – missing migration file: {$basename}\n";
        continue;
    }
    $migBase = realpath($baseDir);
    $migTarget = realpath($path);
    if (!$migTarget || !str_starts_with($migTarget, $migBase . DIRECTORY_SEPARATOR) || !is_file($migTarget)) {
        error_log("SECURITY: blocked dynamic include: migration {$basename}");
        echo "DRY-RUN: WARNING – invalid migration path: {$basename}\n";
        continue;
    }
    require_once $migTarget;
}

// 4) Load executed log (JSON array of basenames)
$executed = [];
if (file_exists($logFile)) {
    $json = file_get_contents($logFile);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $executed = $data;
    }
}

// 5) Compute pending
$pending = [];
foreach ($registry as $basename) {
    if (!in_array($basename, $executed, true)) {
        $pending[] = $basename;
    }
}

// 6) Process based on mode
if ($mode === 'dry-run') {
    // DRY-RUN output only (no execution, no logging, no SQL)
    if (empty($pending)) {
        echo "DRY-RUN: Nothing to run. All migrations are logged.\n";
    } else {
        echo "DRY-RUN: Migrations pending: [ " . implode(', ', $pending) . " ]\n";
    }
} elseif ($mode === 'execute') {
    // EXECUTE mode - run migrations in transaction
    try {
        $pdo->beginTransaction();
        $executedMigrations = [];
        
        foreach ($pending as $basename) {
            $className = str_replace('.php', '', $basename);
            if (!class_exists($className)) {
                throw new Exception("Migration class {$className} not found");
            }
            
            $migration = new $className();
            if (!method_exists($migration, 'execute')) {
                throw new Exception("Migration {$className} missing execute(PDO) method");
            }
            
            if ($migration->execute($pdo) !== true) {
                throw new Exception("Migration {$className} failed");
            }
            
            $executedMigrations[] = $basename;
        }
        
        // Atomically update log file
        $lockFile = $logFile . '.lock';
        $lock = fopen($lockFile, 'w+');
        if (!flock($lock, LOCK_EX)) {
            throw new Exception("Could not obtain file lock for migrations log");
        }
        
        $newLog = array_merge($executed, $executedMigrations);
        file_put_contents($logFile, json_encode($newLog, JSON_PRETTY_PRINT));
        flock($lock, LOCK_UN);
        fclose($lock);
        @unlink($lockFile);
        
        $pdo->commit();
        echo "EXECUTE: Applied migrations: [ " . implode(', ', $executedMigrations) . " ]\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "EXECUTE: Rolled back due to error: " . $e->getMessage() . "\n";
    }
} else {
    echo "ERROR: Invalid mode '{$mode}'. Must be 'dry-run' or 'execute'.\n";
}

// Keep includable: no exit();
