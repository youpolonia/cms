<?php
require_once __DIR__ . '/core/bootstrap.php';
$pdo = \core\Database::connection();
$stmt = $pdo->query('SELECT * FROM menus ORDER BY id DESC LIMIT 5');
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== MENUS ===\n";
print_r($menus);
