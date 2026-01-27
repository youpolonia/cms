<?php
declare(strict_types=1);

/**
 * Migration: Create users table
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // Skip if table already exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->fetch()) {
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NULL,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','editor','user') DEFAULT 'user',
            status ENUM('active','inactive','banned') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS users");
    }
};
