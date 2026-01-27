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

// Verbose diagnostics only in DEV to surface 500 root cause
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=UTF-8');

require_once CMS_ROOT . '/core/database.php';
// Fallback include path if class not found (literal path, require_once only)
if (!class_exists('\\core\\Database') && file_exists(CMS_ROOT . '/includes/core/database.php')) {
    require_once CMS_ROOT . '/includes/core/database.php';
}
if (!class_exists('\\core\\Database')) {
    http_response_code(500);
    echo "[seed_pages][dev] Missing class \\core\\Database after includes";
    exit;
}

try {
    // Connection smoke test
    $pdo = \core\Database::connection();
    if (!$pdo instanceof \PDO) {
        throw new \RuntimeException('Database::connection() did not return PDO');
    }
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo->query('SELECT 1'); // quick sanity check

    // Detect pages table
    $table = null;
    foreach (['pages','cms_pages'] as $t) {
        $s = $pdo->prepare("SHOW TABLES LIKE :t");
        $s->execute([':t'=>$t]);
        if ($s->fetchColumn()) { $table = $t; break; }
    }
    if (!$table) {
        http_response_code(404);
        echo "[seed_pages][dev] Pages table missing (looked for: pages, cms_pages)";
        exit;
    }

    $now = date('Y-m-d H:i:s');
    $rows = [
        ['slug'=>'about','title'=>'About','content'=>'<p>About page — seeded.</p>','status'=>'published'],
        ['slug'=>'contact','title'=>'Contact','content'=>'<p>Contact page — seeded.</p>','status'=>'published'],
    ];

    $updated = 0; $inserted = 0;
    foreach ($rows as $r) {
        $u = $pdo->prepare("UPDATE `$table` SET title=:title, content=:content, status=:status, updated_at=:ua WHERE slug=:slug");
        $u->execute([
            ':title'=>$r['title'], ':content'=>$r['content'], ':status'=>$r['status'],
            ':ua'=>$now, ':slug'=>$r['slug'],
        ]);
        if ($u->rowCount() === 0) {
            $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(\PDO::FETCH_COLUMN);
            $hasCreated = in_array('created_at', $cols, true);
            $hasUpdated = in_array('updated_at', $cols, true);
            $sql = "INSERT INTO `$table` (slug,title,content,status"
                 . ($hasCreated ? ",created_at" : "")
                 . ($hasUpdated ? ",updated_at" : "")
                 . ") VALUES (:slug,:title,:content,:status"
                 . ($hasCreated ? ",:ca" : "")
                 . ($hasUpdated ? ",:ua" : "")
                 . ")";
            $i = $pdo->prepare($sql);
            $i->execute([
                ':slug'=>$r['slug'], ':title'=>$r['title'], ':content'=>$r['content'], ':status'=>$r['status'],
                ':ca'=>$now, ':ua'=>$now,
            ]);
            $inserted++;
        } else {
            $updated++;
        }
    }
    echo "OK pages={$table} updated={$updated} inserted={$inserted}";
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[seed_pages] '.$e->getMessage());
    if (defined('DEV_MODE') && DEV_MODE === true) {
        echo "[seed_pages][dev] " . $e->getMessage();
    } else {
        echo 'Error';
    }
}
