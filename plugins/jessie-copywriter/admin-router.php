<?php
/**
 * Copywriter Admin Router — /admin/copywriter/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/admin/copywriter/?#', '', $uri);
$path = trim($path, '/');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }

$pdo = \core\Database::connection();

switch ($path) {
    case '':
    case 'dashboard':
        $totalContent = (int)$pdo->query("SELECT COUNT(*) FROM copywriter_content")->fetchColumn();
        $totalBrands = (int)$pdo->query("SELECT COUNT(*) FROM copywriter_brands WHERE status='active'")->fetchColumn();
        $totalBatches = (int)$pdo->query("SELECT COUNT(*) FROM copywriter_batches")->fetchColumn();
        $completed = (int)$pdo->query("SELECT COUNT(*) FROM copywriter_content WHERE status='completed'")->fetchColumn();
        $recentContent = $pdo->query("SELECT c.*, u.email FROM copywriter_content c LEFT JOIN saas_users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM copywriter_content c WHERE c.user_id = u.id) as content_count, (SELECT COUNT(*) FROM copywriter_brands b WHERE b.user_id = u.id AND b.status='active') as brand_count FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php';
        break;

    case 'content':
        $content = $pdo->query("SELECT c.*, u.email FROM copywriter_content c LEFT JOIN saas_users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/content.php';
        break;

    default:
        http_response_code(404);
        echo '<h1>404</h1><p>Copywriter page not found.</p>';
}
