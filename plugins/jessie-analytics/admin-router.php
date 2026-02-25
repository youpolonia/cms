<?php
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/admin/analytics/?#', '', $uri), '/');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }
$pdo = \core\Database::connection();

switch ($path) {
    case '': case 'dashboard':
        $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM analytics_events")->fetchColumn();
        $todayEvents = (int)$pdo->query("SELECT COUNT(*) FROM analytics_events WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $totalGoals = (int)$pdo->query("SELECT COUNT(*) FROM analytics_goals")->fetchColumn();
        $totalReports = (int)$pdo->query("SELECT COUNT(*) FROM analytics_reports")->fetchColumn();
        $topTypes = $pdo->query("SELECT event_type, COUNT(*) as cnt FROM analytics_events GROUP BY event_type ORDER BY cnt DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php'; break;
    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM analytics_events e WHERE e.user_id = u.id) as event_count, (SELECT COUNT(*) FROM analytics_goals g WHERE g.user_id = u.id) as goal_count FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php'; break;
    default: http_response_code(404); echo '<h1>404</h1>';
}
