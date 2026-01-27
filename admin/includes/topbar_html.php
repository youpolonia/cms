<?php
/**
 * Admin Topbar HTML for Legacy Modules
 * Usage: require_once CMS_ROOT . '/admin/includes/topbar_html.php';
 * Set $pageTitle before including
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/../..'));
}

$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = strtok($currentPath, '?') ?: '/';

function isActiveLegacyNav(string $path): string {
    global $currentPath;
    if ($path === '/admin' && ($currentPath === '/admin' || $currentPath === '/admin/dashboard')) {
        return 'active';
    }
    return strpos($currentPath, $path) === 0 && $path !== '/admin' ? 'active' : '';
}

$pageTitle = $pageTitle ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Jessie AI-CMS</title>
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
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .nav-main {
        display: flex;
        align-items: center;
        gap: 4px;
        flex: 1;
    }
    .nav-main > a, .nav-main > .nav-dropdown { height: 40px; display: inline-flex; align-items: center; }
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
        background: none;
        border: none;
        height: 40px;
    }
    .nav-link:hover { background: var(--bg-tertiary); color: var(--text-primary); }
    .nav-link.active { background: var(--accent-muted); color: var(--accent); }

    .nav-dropdown { position: relative; }
    .nav-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        min-width: 200px;
        padding: 8px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.2s;
        z-index: 200;
    }
    .nav-dropdown:hover .nav-dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    .nav-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        font-size: 13px;
        color: var(--text-secondary);
        border-radius: 6px;
    }
    .nav-dropdown-menu a:hover { background: var(--bg-tertiary); color: var(--text-primary); }
    .nav-dropdown-menu .divider { height: 1px; background: var(--border); margin: 8px 0; }

    .nav-right { display: flex; align-items: center; gap: 12px; }
    .theme-toggle {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        background: var(--bg-tertiary);
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        font-size: 18px;
        color: var(--text-secondary);
        transition: all 0.15s;
    }
    .theme-toggle:hover { background: var(--border); color: var(--text-primary); }
    .user-menu {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px 6px 6px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.15s;
    }
    .user-menu:hover { background: var(--border); }
    .user-avatar {
        width: 28px; height: 28px;
        background: var(--accent);
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 600; color: white;
    }
    .user-name { font-size: 13px; font-weight: 500; color: var(--text-primary); }

    .main-content { max-width: 1600px; margin: 0 auto; padding: 24px; }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="/admin/dashboard" class="logo">
                <span class="logo-icon">🤖</span>
                <span>Jessie AI-CMS</span>
            </a>
            <nav class="nav-main">
                <a href="/admin/dashboard" class="nav-link <?= isActiveLegacyNav('/admin/dashboard') ?>">📊 Dashboard</a>
                
                <div class="nav-dropdown">
                    <button class="nav-link <?= isActiveLegacyNav('/admin/pages') || isActiveLegacyNav('/admin/articles') ? 'active' : '' ?>">📄 Content ▾</button>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/pages">📄 Pages</a>
                        <a href="/admin/articles">📰 Articles</a>
                        <a href="/admin/categories">🏷️ Categories</a>
                        <a href="/admin/media">🖼️ Media</a>
                    </div>
                </div>

                <div class="nav-dropdown">
                    <button class="nav-link <?= strpos($currentPath, '/admin/ai-') === 0 ? 'active' : '' ?>">🤖 AI Tools ▾</button>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/ai-content-creator">✨ Content Creator</a>
                        <a href="/admin/ai-copywriter">📝 Copywriter</a>
                        <a href="/admin/ai-images">🎨 Image Generator</a>
                        <div class="divider"></div>
                        <a href="/admin/ai-seo-assistant">🎯 SEO Assistant</a>
                        <a href="/admin/ai-seo-pages">📊 SEO Pages</a>
                        <a href="/admin/ai-seo-keywords">🔑 Keywords</a>
                        <div class="divider"></div>
                        <a href="/admin/ai-settings">⚙️ AI Settings</a>
                    </div>
                </div>

                <div class="nav-dropdown">
                    <button class="nav-link <?= isActiveLegacyNav('/admin/themes') || isActiveLegacyNav('/admin/theme-builder') || isActiveLegacyNav('/admin/tb4') ? 'active' : '' ?>">🎨 Design ▾</button>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/themes">🎨 Themes</a>
                        <a href="/admin/theme-builder">🔧 Theme Builder</a>
                        <a href="/admin/tb4">🏗️ TB4 Builder</a>
                    </div>
                </div>

                <a href="/admin/settings" class="nav-link <?= isActiveLegacyNav('/admin/settings') ?>">⚙️ Settings</a>
            </nav>
            <div class="nav-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">🌙</button>
                <a href="/admin/logout" class="user-menu">
                    <span class="user-avatar"><?= strtoupper(substr($adminUsername, 0, 1)) ?></span>
                    <span class="user-name"><?= htmlspecialchars($adminUsername) ?></span>
                </a>
            </div>
        </div>
    </header>
    <main class="main-content">
    <script>
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('cms-theme', next);
        document.querySelector('.theme-toggle').textContent = next === 'dark' ? '🌙' : '☀️';
    }
    document.querySelector('.theme-toggle').textContent = 
        document.documentElement.getAttribute('data-theme') === 'dark' ? '🌙' : '☀️';
    </script>
