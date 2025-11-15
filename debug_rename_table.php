<?php
// Debug script to rename user_role table to user_roles

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo 'Configuration error';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden in production";
    return;
}

require_once __DIR__ . '/core/database.php';

// Create connection
$pdo = \core\Database::connection();

// Check connection
if (!$pdo) {
    die("Connection failed\n");
}

echo "Connected to database successfully\n";

// Execute the rename query
$sql = "RENAME TABLE user_role TO user_roles";
try {
    $pdo->exec($sql);
    echo "Table renamed successfully\n";
} catch (PDOException $e) {
    echo "Error renaming table: " . $e->getMessage() . "\n";
}

echo "Operation completed\n";
