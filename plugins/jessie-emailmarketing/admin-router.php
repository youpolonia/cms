<?php
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/admin/emailmarketing/?#', '', $uri), '/');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }
$pdo = \core\Database::connection();

switch ($path) {
    case '': case 'dashboard':
        $totalLists = (int)$pdo->query("SELECT COUNT(*) FROM em_lists WHERE status='active'")->fetchColumn();
        $totalSubs = (int)$pdo->query("SELECT COUNT(*) FROM em_subscribers WHERE status='active'")->fetchColumn();
        $totalCampaigns = (int)$pdo->query("SELECT COUNT(*) FROM em_campaigns")->fetchColumn();
        $totalSent = (int)$pdo->query("SELECT COALESCE(SUM(total_sent),0) FROM em_campaigns")->fetchColumn();
        $recent = $pdo->query("SELECT c.*, u.email as owner FROM em_campaigns c LEFT JOIN saas_users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php'; break;
    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM em_campaigns c WHERE c.user_id = u.id) as campaign_count, (SELECT COUNT(*) FROM em_lists l WHERE l.user_id = u.id AND l.status='active') as list_count FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php'; break;
    default: http_response_code(404); echo '<h1>404</h1>';
}
