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
    $pdo = \core\Database::connection();
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $tables = [];
    foreach (['admins','users'] as $t) {
        $r = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t));
        if ($r && $r->fetchColumn()) { $tables[] = $t; }
    }
    echo "tables_present: " . (empty($tables) ? "none" : implode(',', $tables)) . "\n";
    $probe = $_GET['u'] ?? null;
    if ($probe) {
        foreach ($tables as $t) {
            $q = $pdo->prepare("SELECT * FROM `$t` WHERE username=:u LIMIT 1");
            $q->execute([':u'=>$probe]);
            $row = $q->fetch(\PDO::FETCH_ASSOC);
            echo "lookup[$t][$probe]: " . ($row ? "FOUND" : "NOT_FOUND") . "\n";
            if ($row) {
                $cols = implode(',', array_keys($row));
                echo "columns[$t]: $cols\n";
            }
        }
    }
    echo "hint: /admin/tools/create_admin.php?u=admin&p=admin123 to seed/update dev user\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo "[auth_probe][dev] " . $e->getMessage();
}
