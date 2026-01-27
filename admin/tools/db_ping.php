<?php
declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');

require_once CMS_ROOT . '/core/database.php';

try {
    // Ensure class is loaded
    if (!class_exists('\\core\\Database')) {
        // Best-effort fallback to forwarder (added in this patch)
        $alt = __DIR__ . '/../../includes/core/database.php';
        if (file_exists($alt)) {
            require_once $alt;
        }
    }
    if (!class_exists('\\core\\Database')) {
        throw new \RuntimeException('Missing class \\core\\Database after includes');
    }

    $pdo = \core\Database::connection();
    if (!$pdo instanceof \PDO) {
        throw new \RuntimeException('Database::connection() did not return PDO');
    }
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->query('SELECT 1');

    // Probe for pages table(s)
    $found = [];
    foreach (['pages','cms_pages'] as $t) {
        $s = $pdo->prepare("SHOW TABLES LIKE :t");
        $s->execute([':t'=>$t]);
        if ($s->fetchColumn()) { $found[] = $t; }
    }

    echo "OK\n";
    echo "database_connection: alive\n";
    echo "pages_tables: " . (empty($found) ? 'none' : implode(',', $found)) . "\n";
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[db_ping] '.$e->getMessage());
    echo "[db_ping][dev] " . $e->getMessage();
}
