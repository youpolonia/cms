<?php
/**
 * SEO Writer Admin Router — /admin/seowriter/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/admin/seowriter/?#', '', $uri);
$path = trim($path, '/');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }

$pdo = \core\Database::connection();

switch ($path) {
    case '':
    case 'dashboard':
        $totalProjects = (int)$pdo->query("SELECT COUNT(*) FROM seowriter_projects")->fetchColumn();
        $totalContent = (int)$pdo->query("SELECT COUNT(*) FROM seowriter_content")->fetchColumn();
        $totalAudits = (int)$pdo->query("SELECT COUNT(*) FROM seowriter_audits")->fetchColumn();
        $avgScore = (int)$pdo->query("SELECT COALESCE(AVG(seo_score),0) FROM seowriter_content WHERE seo_score > 0")->fetchColumn();
        $recentContent = $pdo->query("SELECT c.*, u.email FROM seowriter_content c LEFT JOIN saas_users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM seowriter_content c WHERE c.user_id = u.id) as content_count, (SELECT COUNT(*) FROM seowriter_projects p WHERE p.user_id = u.id) as project_count FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php';
        break;

    case 'content':
        $content = $pdo->query("SELECT c.*, u.email FROM seowriter_content c LEFT JOIN saas_users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/content.php';
        break;

    default:
        http_response_code(404);
        echo '<h1>404</h1><p>SEO Writer page not found.</p>';
}
