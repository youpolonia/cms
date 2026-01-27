<?php
declare(strict_types=1);

/**
 * Migration: Tenant Isolation
 * Creates core tables with tenant support
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // Skip if tables already exist (for safety)
        $stmt = $pdo->query("SHOW TABLES LIKE 'tenants'");
        if ($stmt->fetch()) {
            return; // Already migrated
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            domain VARCHAR(255) UNIQUE NOT NULL,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS tenant_metrics (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL,
            storage_used BIGINT DEFAULT 0,
            api_calls INT DEFAULT 0,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS tenant_metrics");
        $pdo->exec("DROP TABLE IF EXISTS tenants");
    }
};
