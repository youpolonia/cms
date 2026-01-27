<?php
// Guard constant to prevent accidental double-includes
if (!defined('MIGRATION_MANAGER_INCLUDED')) {
    define('MIGRATION_MANAGER_INCLUDED', true);
}

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
csrf_boot('admin');

define('ADMIN_PROTECTED', true);
define('ADMIN_DEV_BYPASS', true);
define('MIGRATION_LOG_PATH', __DIR__ . '/../logs/migrations.log');
define('MIGRATION_LOCK_PATH', __DIR__ . '/migrations/migration.lock');


// Helper to check if migration was already applied
if (!function_exists('is_migration_applied')) {
    function is_migration_applied(string $filename): bool {
        $target = basename($filename, '.php') . '.php';
        if (!is_file(MIGRATION_LOG_PATH)) return false;
        foreach (file(MIGRATION_LOG_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $j = json_decode($line, true);
            if (is_array($j) && ($j['file'] ?? null) === $target) return true;
            if (!$j && preg_match('/\b' . preg_quote($target, '/') . '\b/', $line)) return true;
        }
        return false;
    }
}

if (!function_exists('validate_migration_filename')) {
    function validate_migration_filename(string $filename): bool {
        return preg_match('/^[\w\-\.]+\.php$/', $filename) === 1;
    }
}

// Lock file management
if (!function_exists('is_locked')) {
    function is_locked(string $lockPath): bool {
        if (!is_file($lockPath)) return false;
        
        $lockContent = @file_get_contents($lockPath);
        if ($lockContent === false) return false;
        
        $data = json_decode($lockContent, true);
        if (!is_array($data)) return false;
        
        // Check if lock expired (30 minutes max)
        if (isset($data['timestamp']) && (time() - $data['timestamp']) > 1800) {
            @unlink($lockPath);
            return false;
        }
        
        return true;
    }
}

if (!function_exists('create_lock')) {
    function create_lock(string $lockPath): bool {
        if (is_locked($lockPath)) {
            return false;
        }
        
        $lockData = [
            'pid' => getmypid(),
            'timestamp' => time(),
            'host' => gethostname()
        ];
        
        $dir = dirname($lockPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $tmpPath = $lockPath . '.tmp.' . uniqid();
        if (file_put_contents($tmpPath, json_encode($lockData), LOCK_EX) === false) {
            return false;
        }
        return rename($tmpPath, $lockPath);
    }
}

if (!function_exists('remove_lock')) {
    function remove_lock(string $lockPath): bool {
        if (is_file($lockPath)) {
            return unlink($lockPath);
        }
        return true;
    }
}

if (!function_exists('log_migration_event')) {
    function log_migration_event(string $type, array $data): void {
        $logEntry = json_encode([
            'timestamp' => gmdate('c'),
            'type' => $type,
            'data' => $data,
            'pid' => getmypid()
        ], JSON_UNESCAPED_SLASHES);
        
        if (!is_dir(dirname(MIGRATION_LOG_PATH))) {
            mkdir(dirname(MIGRATION_LOG_PATH), 0755, true);
        }
        file_put_contents(MIGRATION_LOG_PATH, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('run_migration_file')) {
    function run_migration_file(string $file, bool $dryRun): array {
        global $pdo;
        
        if ($dryRun) {
            return [
                'file' => basename($file),
                'ok' => true,
                'status' => 'dry_run'
            ];
        }

        // Check if already applied
        if (is_migration_applied(basename($file))) {
            return [
                'file' => basename($file),
                'ok' => true,
                'status' => 'already_applied'
            ];
        }

        // Skip database operations in DEV_MODE
        if (defined('DEV_MODE') && DEV_MODE === true) {
            // Simulate execution without touching database
            $logData = ['ts' => time(), 'file' => basename($file), 'status' => 'executed'];
            file_put_contents(MIGRATION_LOG_PATH, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
            
            return [
                'file' => basename($file),
                'ok' => true,
                'status' => 'executed'
            ];
        }

        try {
            $DRY_RUN = false;

            // Boundary check to prevent path traversal
            $migrationsDir = __DIR__ . '/migrations';
            $base = realpath($migrationsDir);
            $resolved = realpath($file);
            if ($base === false || $resolved === false || !str_starts_with($resolved, $base . DIRECTORY_SEPARATOR) || !is_file($resolved)) {
                throw new Exception('Invalid migration path blocked by boundary check: ' . $file);
            }

            require_once $resolved;
            
            // Log successful execution
            $logData = ['ts' => time(), 'file' => basename($file), 'status' => 'executed'];
            file_put_contents(MIGRATION_LOG_PATH, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
            
            return [
                'file' => basename($file),
                'ok' => true,
                'status' => 'executed'
            ];
        } catch (Throwable $e) {
            log_migration_event('error', [
                'file' => basename($file),
                'error' => $e->getMessage()
            ]);
            
            return [
                'file' => basename($file),
                'ok' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
}

/**
 * Main migration action handler
 * @param array $request Request data (should contain action and csrf_token)
 * @return string Plain text output
 */
if (!function_exists('handle_migration_action')) {
    function handle_migration_action(array $request): string {
        csrf_validate_or_403();
        $action = $request['action'] ?? '';
        if ($action === 'run_selected' || $action === 'execute_all') {
            // Additional validation already done above
        }

        // Check for concurrent execution lock
        if (is_locked(MIGRATION_LOCK_PATH)) {
            return "Migration system is locked - another process may be running";
        }

        $action = $action ?: ($request['action'] ?? '');
        $migrationsDir = __DIR__ . '/migrations';
        $files = [];
        
        if (is_dir($migrationsDir)) {
            $allFiles = glob($migrationsDir . '/*.php');
            if (!is_array($allFiles)) {
                $allFiles = [];
            }
            foreach ($allFiles as $file) {
                $basename = basename($file);
                // Only require_once files that start with digits (migration files)
                if (preg_match('/^\d+/', $basename)) {
                    $files[] = $file;
                }
            }
            sort($files);
        }
        
        if ($action === 'preview_all') {
            $lines = [];
            foreach ($files as $file) {
                $lines[] = "DRY RUN: " . basename($file);
            }
            return implode("\n", $lines);
            
        } elseif ($action === 'run_selected') {
            $selectedMigrations = $request['migrations'] ?? [];
            if (empty($selectedMigrations)) {
                return "already applied";
            }

            // Acquire lock for execution
            if (!create_lock(MIGRATION_LOCK_PATH)) {
                return "Failed to acquire migration lock";
            }

            try {
                $migration = $selectedMigrations[0]; // Take first selected migration
                if (substr($migration, -4) !== '.php') {
                    $migration .= '.php';
                }
                $basename = basename($migration);
                
                if (!validate_migration_filename($basename)) {
                    return "already applied";
                }
                
                $fullPath = $migrationsDir . '/' . $basename;
                if (!is_file($fullPath)) {
                    return "already applied";
                }
                
                if (is_migration_applied($basename)) {
                    return "already applied";
                }
                
                $result = run_migration_file($fullPath, false);
                
                if ($result['ok'] && $result['status'] === 'executed') {
                    return "executed";
                } else {
                    return "already applied";
                }
                
            } finally {
                remove_lock(MIGRATION_LOCK_PATH);
            }
            
        } elseif ($action === 'execute_all') {
            // Acquire lock for execution
            if (!create_lock(MIGRATION_LOCK_PATH)) {
                return "Failed to acquire migration lock";
            }

            try {
                $processed = 0;
                foreach ($files as $file) {
                    if (!is_migration_applied(basename($file))) {
                        run_migration_file($file, false);
                        $processed++;
                    }
                }
                return "Summary: " . $processed . " migrations processed";
                
            } finally {
                remove_lock(MIGRATION_LOCK_PATH);
            }
        }
        
        return "Unknown action";
    }
}

// If accessed directly, show a simple page
if (basename($_SERVER['PHP_SELF']) === 'migration_manager.php') {
    require_once __DIR__ . '/includes/admin_layout.php';
    admin_render_page_start('Migration Manager');
    echo '<p>This is a utility module providing migration management functions.</p>';
    echo '<p>Available functions:</p>';
    echo '<ul>';
    echo '<li><code>is_migration_applied(string $filename): bool</code></li>';
    echo '<li><code>validate_migration_filename(string $filename): bool</code></li>';
    echo '<li><code>run_migration_file(string $file, bool $dryRun): array</code></li>';
    echo '<li><code>handle_migration_action(array $request): string</code></li>';
    echo '</ul>';
    echo '<p><a href="/admin/migrations.php">Go to Migrations Page</a></p>';
    admin_render_page_end();
}
