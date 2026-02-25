<?php
/**
 * SaaS Core Admin Router — /admin/saas/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/admin/saas/?#', '', $uri);
$path = trim($path, '/');

// Admin auth required
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }

switch ($path) {
    case '':
    case 'dashboard':
        require_once __DIR__ . '/includes/class-saas-credits.php';
        $credits = new \Plugins\JessieSaasCore\SaasCredits();
        $pdo = \core\Database::connection();
        
        $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM saas_users")->fetchColumn();
        $activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM saas_users WHERE last_login > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
        $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM saas_transactions WHERE type = 'charge' AND status = 'completed'")->fetchColumn();
        $todayUsage = (int)$pdo->query("SELECT COUNT(*) FROM saas_api_usage WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        
        $recentUsers = $pdo->query("SELECT id, email, name, plan, credits_remaining, created_at FROM saas_users ORDER BY id DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        $usageByService = $pdo->query("SELECT service, COUNT(*) as requests, SUM(credits_used) as credits FROM saas_api_usage WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY service ORDER BY requests DESC")->fetchAll(\PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;
        
    case 'users':
        $pdo = \core\Database::connection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $total = (int)$pdo->query("SELECT COUNT(*) FROM saas_users")->fetchColumn();
        $users = $pdo->query("SELECT * FROM saas_users ORDER BY id DESC LIMIT $perPage OFFSET $offset")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php';
        break;
        
    case 'plans':
        $pdo = \core\Database::connection();
        $plans = $pdo->query("SELECT * FROM saas_plans ORDER BY service, sort_order")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/plans.php';
        break;
    
    case 'revenue':
        $pdo = \core\Database::connection();
        $transactions = $pdo->query("SELECT t.*, u.email FROM saas_transactions t JOIN saas_users u ON t.user_id = u.id ORDER BY t.id DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        $monthlyRevenue = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as txns FROM saas_transactions WHERE type = 'charge' AND status = 'completed' GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/revenue.php';
        break;
        
    default:
        http_response_code(404);
        echo '<h1>404</h1><p>SaaS page not found.</p>';
}
