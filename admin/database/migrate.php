<?php
declare(strict_types=1);
use Core\Config;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', CMS_ROOT . '/logs/migration_errors.log'); // Log to a file in CMS_ROOT/logs
// Clear the log file at the start of each request for easier debugging of the current run
if (file_exists(CMS_ROOT . '/logs/migration_errors.log')) {
    unlink(CMS_ROOT . '/logs/migration_errors.log');
}

// Define CMS_ROOT if not already defined, for direct script access
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/../../'));
}
// Prevent direct access unless it's CLI or a POST request
if ( (php_sapi_name() !== 'cli' && (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST')) || !defined('CMS_ROOT') ) {
    if (php_sapi_name() !== 'cli') {
        header('HTTP/1.1 403 Forbidden');
    }
    exit('Direct access forbidden');
}

// Verify admin authentication
// session_start();
// if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Content-Type: application/json');
//     echo json_encode(['error' => 'Authentication required']);
//     exit;
// }

require_once __DIR__ . '/../../core/config.php';

// Instantiate and load configuration
$config = new Config();
// DB config is centralized in core/database.php (legacy file removed)

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
}

try {
    require_once __DIR__ . '/../../config.php';
    $dbHost = $config->get('host', (defined('DB_HOST') ? DB_HOST : 'localhost'));
    $dbName = $config->get('database', 'cms_database');
    $dbUser = $config->get('username', 'cms_user');
    $dbPass = $config->get('password', ''); // Provide a default if necessary
    $dbCharset = $config->get('charset', 'utf8mb4');
    $dbOptions = $config->get('options', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);


    require_once __DIR__ . '/../../core/database.php';
    $db = \core\Database::connection();

    // Create migrations table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) UNIQUE,
        batch INT DEFAULT 1,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Get existing migrations
    $executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

    // Determine the next batch number
    $latestBatch = $db->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
    $nextBatch = ($latestBatch === false || $latestBatch === null) ? 1 : (int)$latestBatch + 1;

    // Scan migration files
    $migrationDir = CMS_ROOT . '/database/migrations'; // Use CMS_ROOT for robust path
    $scandirResult = scandir($migrationDir);
    if ($scandirResult === false) {
        throw new \RuntimeException("Failed to scan migration directory: {$migrationDir}");
    }
    $files = array_diff($scandirResult, ['..', '.']);
    $newMigrations = [];
    
    $db->beginTransaction();
    
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && !in_array($file, $executed)) {
            $migrationPath = $migrationDir.'/'.$file;
            if (!is_file($migrationPath) || !is_readable($migrationPath)) {
                // Log or handle error: migration file not found or not readable
                error_log("Migration file not found or not readable: " . $migrationPath);
                continue;
            }
            $migration = require_once $migrationPath;
            
            if (is_object($migration) && method_exists($migration, 'migrate')) {
                $migration->migrate($db);
                $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$file, $nextBatch]);
                $newMigrations[] = $file;
            }
        }
    }
    
    $db->commit();

    echo json_encode([
        'success' => true,
        'executed' => $newMigrations,
        'count' => count($newMigrations)
    ]);

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
    }
    echo json_encode([
        'error' => 'Migration failed',
        'details' => $e->getMessage()
    ]);
}
