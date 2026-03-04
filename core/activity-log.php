<?php
/**
 * Admin Activity Log
 * Tracks admin actions for audit trail
 * 
 * Usage: cms_log_activity('pages', 'Updated page "About Us"', $pageId);
 */

function cms_log_activity(string $category, string $description, ?int $entityId = null, ?string $entityType = null): void
{
    try {
        $pdo = db();

        // Ensure table exists (only once per request)
        static $tableChecked = false;
        if (!$tableChecked) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT DEFAULT NULL,
                admin_username VARCHAR(100) DEFAULT '',
                category VARCHAR(50) NOT NULL,
                description TEXT NOT NULL,
                entity_id INT DEFAULT NULL,
                entity_type VARCHAR(50) DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent VARCHAR(500) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_admin (admin_id),
                INDEX idx_category (category),
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $tableChecked = true;
        }

        $adminId = null;
        $adminUsername = '';
        if (class_exists('\Core\Session')) {
            $adminId = \Core\Session::getAdminId();
            $adminUsername = \Core\Session::getAdminUsername() ?? '';
        }

        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, admin_username, category, description, entity_id, entity_type, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $adminId,
            substr($adminUsername, 0, 100),
            substr($category, 0, 50),
            $description,
            $entityId,
            $entityType ? substr($entityType, 0, 50) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (\Exception $e) {
        // Silently fail — logging should never break the app
    }
}

/**
 * Cleanup old log entries (keep last 90 days)
 */
function cms_cleanup_activity_log(int $daysToKeep = 90): int
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM admin_activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$daysToKeep]);
        return $stmt->rowCount();
    } catch (\Exception $e) {
        return 0;
    }
}
