<?php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/app/helpers/functions.php';

$pdo = db();
$stmt = $pdo->query('DESCRIBE menu_items');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "menu_items columns:\n";
print_r($columns);
