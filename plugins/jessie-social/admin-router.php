<?php
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/admin/social/?#', '', $uri), '/');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }
$pdo = \core\Database::connection();

switch ($path) {
    case '': case 'dashboard':
        $totalPosts = (int)$pdo->query("SELECT COUNT(*) FROM social_posts")->fetchColumn();
        $published = (int)$pdo->query("SELECT COUNT(*) FROM social_posts WHERE status='published'")->fetchColumn();
        $scheduled = (int)$pdo->query("SELECT COUNT(*) FROM social_posts WHERE status='scheduled'")->fetchColumn();
        $accounts = (int)$pdo->query("SELECT COUNT(*) FROM social_accounts WHERE status='active'")->fetchColumn();
        $recent = $pdo->query("SELECT p.*, u.email FROM social_posts p LEFT JOIN saas_users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php'; break;
    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM social_posts p WHERE p.user_id = u.id) as post_count, (SELECT COUNT(*) FROM social_accounts a WHERE a.user_id = u.id AND a.status='active') as account_count FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php'; break;
    default: http_response_code(404); echo '<h1>404</h1>';
}
