<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Admin Activity Log Viewer
 */
class ActivityLogController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $category = $request->get('category') ?? '';
        $search = trim($request->get('q') ?? '');

        $where = '1=1';
        $params = [];

        if ($category) {
            $where .= ' AND category = ?';
            $params[] = $category;
        }
        if ($search) {
            $where .= ' AND (description LIKE ? OR admin_username LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Ensure table exists
        try {
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
        } catch (\Exception $e) {}

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM admin_activity_log WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $stmt = $pdo->prepare("SELECT * FROM admin_activity_log WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get unique categories for filter
        $cats = $pdo->query("SELECT DISTINCT category FROM admin_activity_log ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);

        render('admin/activity-log', [
            'logs' => $logs,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'categories' => $cats,
            'selectedCategory' => $category,
            'search' => $search,
        ]);
    }

    public function clear(Request $request): void
    {
        $days = max(1, (int)$request->post('days', '90'));
        require_once CMS_ROOT . '/core/activity-log.php';
        $deleted = cms_cleanup_activity_log($days);
        Session::flash('success', "Cleared {$deleted} log entries older than {$days} days.");
        Response::redirect('/admin/activity-log');
    }
}
