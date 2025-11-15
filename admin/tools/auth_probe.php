<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/database.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); echo 'Forbidden'; exit; }
error_reporting(E_ALL); ini_set('display_errors','1'); header('Content-Type: text/plain; charset=UTF-8');
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
