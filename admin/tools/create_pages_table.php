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

    $table = null;
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    foreach (['pages','cms_pages'] as $t) {
        if (in_array($t, $tables, true)) {
            $table = $t;
            break;
        }
    }
    if ($table) {
        echo "OK table_exists={$table}\n";
        exit;
    }

    $pdo->exec("
        CREATE TABLE `pages` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `slug` VARCHAR(191) NOT NULL UNIQUE,
          `title` VARCHAR(255) NOT NULL,
          `content` MEDIUMTEXT NOT NULL,
          `status` VARCHAR(32) NOT NULL DEFAULT 'published',
          `created_at` DATETIME NULL,
          `updated_at` DATETIME NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $now = date('Y-m-d H:i:s');
    $ins = $pdo->prepare("INSERT INTO `pages` (slug,title,content,status,created_at,updated_at)
                          VALUES (:slug,:title,:content,'published',:ca,:ua)");
    $ins->execute([
        ':slug'=>'about',
        ':title'=>'About',
        ':content'=>'<p>About page — initial content.</p>',
        ':ca'=>$now, ':ua'=>$now
    ]);
    $ins->execute([
        ':slug'=>'contact',
        ':title'=>'Contact',
        ':content'=>'<p>Contact page — initial content.</p>',
        ':ca'=>$now, ':ua'=>$now
    ]);

    echo "OK created=pages seeded=2\n";
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[create_pages_table] '.$e->getMessage());
    echo "[create_pages_table][dev] ".$e->getMessage();
}
