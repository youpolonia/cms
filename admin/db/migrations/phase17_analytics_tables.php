<?php
/**
 * Phase 17 Analytics Tables Migration
 * Creates content_engagement_monthly and analytics_monthly_summary tables
 */

// Load database configuration
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

try {
    // Create database connection (centralized)
    require_once __DIR__ . '/../../../core/database.php';
    $pdo = \core\Database::connection();
    
    // Begin transaction
    $pdo->beginTransaction();

    // Create content_engagement_monthly table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS content_engagement_monthly (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            content_id BIGINT UNSIGNED NOT NULL,
            tenant_id BIGINT UNSIGNED NOT NULL,
            date DATE NOT NULL,
            views INT UNSIGNED DEFAULT 0,
            likes INT UNSIGNED DEFAULT 0,
            comments INT UNSIGNED DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_content_tenant_date (content_id, tenant_id, date),
            INDEX idx_tenant_date (tenant_id, date),
            CONSTRAINT fk_engagement_content FOREIGN KEY (content_id) 
                REFERENCES content(id) ON DELETE CASCADE,
            CONSTRAINT fk_engagement_tenant FOREIGN KEY (tenant_id) 
                REFERENCES tenants(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET." COLLATE=".DB_COLLATION."
    ");

    // Create analytics_monthly_summary table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS analytics_monthly_summary (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            content_id BIGINT UNSIGNED NOT NULL,
            tenant_id BIGINT UNSIGNED NOT NULL,
            month DATE NOT NULL,
            total_views INT UNSIGNED DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET." COLLATE=".DB_COLLATION."
            total_likes INT UNSIGNED DEFAULT 0,
            total_comments INT UNSIGNED DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_content_tenant_month (content_id, tenant_id, month),
            INDEX idx_tenant_month (tenant_id, month),
            CONSTRAINT fk_summary_content FOREIGN KEY (content_id) 
                REFERENCES content(id) ON DELETE CASCADE,
            CONSTRAINT fk_summary_tenant FOREIGN KEY (tenant_id) 
                REFERENCES tenants(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET={$dbConfig['charset']} COLLATE={$dbConfig['collation']}
    ");

    // Commit transaction
    $pdo->commit();
    
    // Log success
    file_put_contents(__DIR__ . '/../../../logs/migrations.log', 
        date('Y-m-d H:i:s') . " - Successfully created phase17 analytics tables\n", 
        FILE_APPEND);
} catch (PDOException $e) {
    // Rollback on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    file_put_contents(__DIR__ . '/../../../logs/migrations.log', 
        date('Y-m-d H:i:s') . " - Database error\n", 
        FILE_APPEND);
    
    error_log('Database error');
    http_response_code(500);
    exit;
}
