<?php
/**
 * Legacy Admin Header - Topbar Layout (matches MVC)
 * DO NOT add closing ?> tag
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/menu_renderer.php';
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(dirname(__DIR__))); }

$username = $_SESSION['admin_username'] ?? 'Admin';
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$currentPath = strtok($currentPath, '?');

function isActiveNav(string $path): string {
    global $currentPath;
    if ($path === '/admin' && ($currentPath === '/admin' || $currentPath === '/admin/' || $currentPath === '/admin/dashboard')) {
        return 'active';
    }
    return strpos($currentPath, $path) === 0 && $path !== '/admin' ? 'active' : '';
}

function esc_h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc_h($pageTitle) ?> - Jessie AI-CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function() {
            const saved = localStorage.getItem('cms-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
    <style>
    :root, [data-theme="light"] {
        --bg-primary: #ffffff;
        --bg-secondary: #f8fafc;
        --bg-tertiary: #f1f5f9;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --border: #e2e8f0;
        --accent: #6366f1;
        --accent-hover: #4f46e5;
        --accent-muted: rgba(99, 102, 241, 0.1);
        --success: #10b981;
        --success-bg: #d1fae5;
        --warning: #f59e0b;
        --warning-bg: #fef3c7;
        --danger: #ef4444;
        --danger-bg: #fee2e2;
        --card-bg: #ffffff;
        --primary: #6366f1;
        --primary-dark: #4f46e5;
    }
    [data-theme="dark"] {
        --bg-primary: #1e1e2e;
        --bg-secondary: #181825;
        --bg-tertiary: #313244;
        --text-primary: #cdd6f4;
        --text-secondary: #a6adc8;
        --text-muted: #6c7086;
        --border: #313244;
        --accent: #89b4fa;
        --accent-hover: #b4befe;
        --accent-muted: rgba(137, 180, 250, 0.15);
        --success: #a6e3a1;
        --success-bg: rgba(166, 227, 161, 0.15);
        --warning: #f9e2af;
        --warning-bg: rgba(249, 226, 175, 0.15);
        --danger: #f38ba8;
        --danger-bg: rgba(243, 139, 168, 0.15);
        --card-bg: #1e1e2e;
        --primary: #89b4fa;
        --primary-dark: #b4befe;
    }
    :root {
        --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        --radius: 8px;
        --radius-lg: 12px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 16px; -webkit-font-smoothing: antialiased; }
    body {
        font-family: var(--font);
        font-size: 14px;
        line-height: 1.5;
        color: var(--text-primary);
        background: var(--bg-secondary);
        min-height: 100vh;
    }
    a { color: var(--accent); text-decoration: none; }
    a:hover { color: var(--accent-hover); }

    /* TOPBAR */
    .topbar {
        background: var(--bg-primary);
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .topbar-inner {
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 24px;
        height: 64px;
        display: flex;
        align-items: center;
        gap: 24px;
    }
    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        text-decoration: none;
        flex-shrink: 0;
    }
    .logo-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--accent), var(--accent-hover));
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        overflow: hidden;
    }
    .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* NAV */
    .nav-main {
        display: flex;
        align-items: center;
        gap: 4px;
        flex: 1;
    }
    .nav-main > a,
    .nav-main > .nav-dropdown {
        height: 40px;
        display: inline-flex;
        align-items: center;
    }
    .nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        border-radius: var(--radius);
        transition: all 0.15s;
        cursor: pointer;
        line-height: 1;
        height: 36px;
        white-space: nowrap;
    }
    .nav-link:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .nav-link.active {
        background: var(--accent-muted);
        color: var(--accent);
    }
    .nav-dropdown {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .nav-dropdown > .nav-link {
        height: 36px;
    }
    .nav-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        max-height: 70vh;
        overflow-y: auto;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 8px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        transition: all 0.15s;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 200;
    }
    .nav-dropdown:hover .nav-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(4px);
    }
    .nav-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: var(--text-secondary);
        border-radius: var(--radius);
        transition: all 0.15s;
    }
    .nav-dropdown-item:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .nav-badge {
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        border-radius: 10px;
        background: var(--accent);
        color: #fff;
        line-height: 1;
    }

    /* TOPBAR RIGHT */
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .theme-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        cursor: pointer;
        font-size: 18px;
        transition: all 0.15s;
    }
    .theme-btn:hover {
        border-color: var(--accent);
        background: var(--bg-primary);
    }
    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px 6px 6px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        cursor: pointer;
    }
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--accent-hover));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        color: #fff;
    }
    .user-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
    }

    /* MAIN CONTENT */
    .main-content {
        max-width: 1600px;
        margin: 0 auto;
        padding: 24px;
    }

    /* LEGACY STYLES */
    .container { max-width: 1400px; margin: 0 auto; }
    h1 { font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-primary); }
    h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-primary); }
    .muted { color: var(--text-muted); font-size: 0.875rem; }
    .alert { padding: 0.875rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.875rem; }
    .alert-success, .alert.success { background: var(--success-bg); color: var(--success); border: 1px solid var(--success); }
    .alert-error, .alert.error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); }
    .alert-warning { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning); }
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1.5rem; }
    .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
    .card-title { font-size: 1rem; font-weight: 600; }
    .card-body { padding: 1.25rem; }
    .btn, button[type="submit"] { 
        display: inline-flex; align-items: center; gap: 0.5rem; 
        padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; 
        border-radius: var(--radius); border: none; cursor: pointer; 
        text-decoration: none; transition: all 0.2s; 
    }
    .btn.primary, .btn-primary, button[type="submit"] { background: var(--accent); color: #fff; }
    .btn.primary:hover, .btn-primary:hover, button[type="submit"]:hover { background: var(--accent-hover); }
    .btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
    .btn-danger { background: var(--danger-bg); color: var(--danger); }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border); }
    th { font-weight: 600; color: var(--text-secondary); font-size: 0.8125rem; text-transform: uppercase; }
    input, select, textarea {
        padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--border);
        border-radius: var(--radius); background: var(--bg-primary); color: var(--text-primary);
        width: 100%;
    }
    input:focus, select:focus, textarea:focus {
        outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-muted);
    }
    label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); }
    .form-group { margin-bottom: 1rem; }
    </style>
    <?php if (file_exists(__DIR__ . '/../../assets/css/theme-custom.css')): ?>
    <link rel="stylesheet" href="/assets/css/theme-custom.css?v=<?= filemtime(__DIR__ . '/../../assets/css/theme-custom.css') ?>">
    <?php endif; ?>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="/admin" class="logo">
                <span class="logo-icon"><img src="/assets/images/jessie-logo.svg" alt="Jessie" width="36" height="36"></span>
                <span>Jessie</span>
            </a>
            
            <nav class="nav-main">
                <?= renderAdminNav() ?>
            </nav>
            
            <div class="topbar-right">
                <button class="theme-btn" id="theme-toggle" title="Toggle theme">
                    <span class="theme-icon">🌙</span>
                </button>
                <div class="nav-dropdown">
                    <div class="user-menu">
                        <span class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></span>
                        <span class="user-name"><?= esc_h($username) ?></span>
                    </div>
                    <div class="nav-dropdown-menu" style="right: 0; left: auto;">
                        <a href="/admin/profile" class="nav-dropdown-item">👤 Profile</a>
                        <a href="/admin/settings" class="nav-dropdown-item">⚙️ Settings</a>
                        <a href="/admin/logout" class="nav-dropdown-item">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
