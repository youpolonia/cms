<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/database.php';
csrf_boot();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST', true, 405);
    exit;
}
csrf_validate_or_403();
$pdo = \core\Database::connection();
$table = 'admins';
$pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(190) NOT NULL UNIQUE,
  `email` VARCHAR(190) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$u = isset($_POST['u']) ? trim((string)$_POST['u']) : '';
$p = isset($_POST['p']) ? (string)$_POST['p'] : '';
$e = isset($_POST['e']) ? trim((string)$_POST['e']) : null;
if ($u === '' || $p === '') { http_response_code(400); echo 'error'; exit; }
$desc = $pdo->query("DESCRIBE `admins`")->fetchAll(\PDO::FETCH_ASSOC);
$cols = array_column($desc, 'Field');
if (!in_array('username', $cols, true) || !in_array('password_hash', $cols, true)) { http_response_code(500); echo 'error'; exit; }
$hash = password_hash($p, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM `admins` WHERE `username` = :u LIMIT 1");
$stmt->execute([':u' => $u]);
$id = $stmt->fetchColumn();
if ($id) {
    $sql = "UPDATE `admins` SET `password_hash`=:p" . ($e ? ",`email`=:e" : "") . " WHERE `id`=:id";
    $stmt = $pdo->prepare($sql);
    $args = [':p' => $hash, ':id' => $id];
    if ($e) { $args[':e'] = $e; }
    $stmt->execute($args);
    echo 'updated';
    exit;
}
$sql = "INSERT INTO `admins` (`username`,`password_hash`" . ($e ? ",`email`" : "") . ") VALUES (:u,:p" . ($e ? ",:e" : "") . ")";
$stmt = $pdo->prepare($sql);
$args = [':u' => $u, ':p' => $hash];
if ($e) { $args[':e'] = $e; }
$stmt->execute($args);
echo 'created';
exit;
