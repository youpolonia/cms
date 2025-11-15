<?php
require_once __DIR__.'/../core/database.php';

try {
    $pdo = \core\Database::connection();

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `cms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Verify permissions
    $stmt = $pdo->query("SHOW GRANTS FOR 'cms_user'@'%'");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Database created successfully\n";
    echo "User grants:\n".implode("\n", $grants)."\n";
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Database creation failed\n";
}
