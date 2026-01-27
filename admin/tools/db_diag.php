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

header('Content-Type: text/plain; charset=UTF-8');
echo "DB DIAG (DEV)\n";
try {
    require_once CMS_ROOT . '/core/database.php';
    $pdo = \core\Database::connection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "connection: ok\n";
    $tables = [];
    $st = $pdo->query('SHOW TABLES');
    while ($r = $st->fetch(PDO::FETCH_NUM)) { $tables[] = (string)$r[0]; }
    echo "tables: " . implode(',', $tables) . "\n";
    foreach (['admins','users'] as $t) {
        if (!in_array($t, $tables, true)) { echo "$t: missing\n"; continue; }
        $cols = array_column($pdo->query('DESCRIBE `'.$t.'`')->fetchAll(PDO::FETCH_ASSOC), 'Field');
        echo "$t.columns: " . implode(',', $cols) . "\n";
        $cnt = (int)$pdo->query('SELECT COUNT(*) FROM `'.$t.'`')->fetchColumn();
        echo "$t.count: $cnt\n";
    }
    echo "status: done\n";
} catch (PDOException $e) {
    error_log('[DB_DIAG][PDO] code='.(string)$e->getCode().' msg='.$e->getMessage());
    echo "error: db\n";
    exit;
} catch (Throwable $e) {
    error_log('[DB_DIAG][THR] '.$e->getMessage());
    echo "error: app\n";
    exit;
}
