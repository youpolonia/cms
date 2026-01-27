<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/database.php';

$db = \core\Database::connection();

// Check table structure
$stmt = $db->query("DESCRIBE tb_layout_library");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== TB_LAYOUT_LIBRARY STRUCTURE ===\n\n";
foreach ($columns as $col) {
    echo "{$col['Field']} | {$col['Type']} | {$col['Null']} | {$col['Default']}\n";
}
