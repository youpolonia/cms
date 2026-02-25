<?php
/**
 * Image Studio Admin Router — /admin/imagestudio/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/admin/imagestudio/?#', '', $uri);
$path = trim($path, '/');

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['admin_id'])) { header('Location: /admin/login'); exit; }

$pdo = \core\Database::connection();

switch ($path) {
    case '':
    case 'dashboard':
        $totalImages = (int)$pdo->query("SELECT COUNT(*) FROM imagestudio_images")->fetchColumn();
        $totalJobs = (int)$pdo->query("SELECT COUNT(*) FROM imagestudio_jobs")->fetchColumn();
        $byType = $pdo->query("SELECT type, COUNT(*) as cnt FROM imagestudio_images GROUP BY type ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
        $totalCredits = (int)$pdo->query("SELECT COALESCE(SUM(credits_used),0) FROM imagestudio_images")->fetchColumn();
        $recentImages = $pdo->query("SELECT i.*, u.email FROM imagestudio_images i LEFT JOIN saas_users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT 12")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case 'users':
        $users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM imagestudio_images i WHERE i.user_id = u.id) as image_count, (SELECT COALESCE(SUM(credits_used),0) FROM imagestudio_images i2 WHERE i2.user_id = u.id) as credits_spent FROM saas_users u ORDER BY u.id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/users.php';
        break;

    case 'images':
        $images = $pdo->query("SELECT i.*, u.email FROM imagestudio_images i LEFT JOIN saas_users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        require_once __DIR__ . '/views/admin/images.php';
        break;

    default:
        http_response_code(404);
        echo '<h1>404</h1><p>Image Studio page not found.</p>';
}
