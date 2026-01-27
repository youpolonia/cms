<?php
/**
 * Admin Layout Template v2.0
 * Modern SaaS-style UI with Dark/Light mode
 */
$username = \Core\Session::getAdminUsername() ?? 'Admin';
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = strtok($currentPath, '?') ?: '/';

function isActive(string $path): string {
    global $currentPath;
    if ($currentPath === null) return '';
    if ($path === '/admin' && ($currentPath === '/admin' || $currentPath === '/admin/dashboard')) {
        return 'active';
    }
    return strpos($currentPath, $path) === 0 && $path !== '/admin' ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> - Jessie AI-CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Load theme before render to prevent flash
        (function() {
            const saved = localStorage.getItem('cms-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
    <style>
    /* ============================================
       CSS VARIABLES - LIGHT THEME
       ============================================ */
    :root, [data-theme="light"] {
        --color-bg-primary: #ffffff;
        --color-bg-secondary: #f8fafc;
        --color-bg-tertiary: #f1f5f9;
        
        --color-sidebar-bg: #0f172a;
        --color-sidebar-hover: rgba(255,255,255,0.08);
        --color-sidebar-active: rgba(255,255,255,0.12);
        --color-sidebar-text: #94a3b8;
        --color-sidebar-text-active: #ffffff;
        
        --color-text-primary: #0f172a;
        --color-text-secondary: #475569;
        --color-text-muted: #94a3b8;
        
        --color-border: #e2e8f0;
        --color-border-light: #f1f5f9;
        
        --color-accent: #6366f1;
        --color-accent-hover: #4f46e5;
        --color-accent-muted: rgba(99, 102, 241, 0.1);
        
        --color-success: #10b981;
        --color-success-bg: #d1fae5;
        --color-success-text: #065f46;
        --color-warning: #f59e0b;
        --color-warning-bg: #fef3c7;
        --color-warning-text: #92400e;
        --color-danger: #ef4444;
        --color-danger-bg: #fee2e2;
        --color-danger-text: #991b1b;
        
        --color-input-bg: #ffffff;
    }

    /* ============================================
       CSS VARIABLES - DARK THEME
       ============================================ */
    [data-theme="dark"] {
        --color-bg-primary: #1e1e2e;
        --color-bg-secondary: #181825;
        --color-bg-tertiary: #313244;
        
        --color-sidebar-bg: #11111b;
        --color-sidebar-hover: rgba(255,255,255,0.06);
        --color-sidebar-active: rgba(255,255,255,0.1);
        --color-sidebar-text: #a6adc8;
        --color-sidebar-text-active: #cdd6f4;
        
        --color-text-primary: #cdd6f4;
        --color-text-secondary: #a6adc8;
        --color-text-muted: #6c7086;
        
        --color-border: #313244;
        --color-border-light: #45475a;
        
        --color-accent: #89b4fa;
        --color-accent-hover: #b4befe;
        --color-accent-muted: rgba(137, 180, 250, 0.15);
        
        --color-success: #a6e3a1;
        --color-success-bg: rgba(166, 227, 161, 0.15);
        --color-success-text: #a6e3a1;
        --color-warning: #f9e2af;
        --color-warning-bg: rgba(249, 226, 175, 0.15);
        --color-warning-text: #f9e2af;
        --color-danger: #f38ba8;
        --color-danger-bg: rgba(243, 139, 168, 0.15);
        --color-danger-text: #f38ba8;
        
        --color-input-bg: #313244;
    }

    /* ============================================
       COMMON VARIABLES
       ============================================ */
    :root {
        --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        --sidebar-width: 260px;
        --topbar-height: 60px;
        
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        
        --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    /* ============================================
       RESET & BASE
       ============================================ */
    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html {
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
    }

    body {
        font-family: var(--font-sans);
        font-size: 14px;
        line-height: 1.5;
        color: var(--color-text-primary);
        background: var(--color-bg-secondary);
        transition: background 0.2s ease, color 0.2s ease;
    }

    a {
        color: var(--color-accent);
        text-decoration: none;
    }

    a:hover {
        color: var(--color-accent-hover);
    }

    /* ============================================
       LAYOUT
       ============================================ */
    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

    .admin-sidebar {
        width: var(--sidebar-width);
        background: var(--color-sidebar-bg);
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        z-index: 100;
        overflow: hidden;
        transition: background 0.2s ease;
    }

    .admin-main {
        flex: 1;
        margin-left: var(--sidebar-width);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .admin-topbar {
        height: var(--topbar-height);
        background: var(--color-bg-primary);
        border-bottom: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        position: sticky;
        top: 0;
        z-index: 50;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .admin-content {
        flex: 1;
        padding: 24px;
        max-width: 1400px;
        width: 100%;
    }

    /* ============================================
       SIDEBAR
       ============================================ */
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        text-decoration: none;
    }

    .sidebar-logo-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .sidebar-nav {
        flex: 1;
        padding: 16px 0;
        overflow-y: auto;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
    }

    .nav-section {
        margin-bottom: 8px;
    }

    .nav-section-title {
        padding: 8px 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-sidebar-text);
        opacity: 0.6;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        font-size: 14px;
        color: var(--color-sidebar-text);
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .nav-item:hover {
        background: var(--color-sidebar-hover);
        color: var(--color-sidebar-text-active);
    }

    .nav-item.active {
        background: var(--color-sidebar-active);
        color: var(--color-sidebar-text-active);
    }

    .nav-item-icon {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }

    .nav-item-text {
        flex: 1;
    }

    .nav-badge {
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        border-radius: 10px;
        background: var(--color-accent);
        color: #fff;
    }

    .nav-badge.new {
        background: var(--color-success);
    }

    .nav-submenu {
        display: none;
        padding-left: 32px;
    }

    .nav-submenu.open {
        display: block;
    }

    .nav-submenu .nav-item {
        padding: 8px 20px;
        font-size: 13px;
    }

    .nav-toggle::after {
        content: '›';
        margin-left: auto;
        font-size: 16px;
        transition: transform 0.15s ease;
    }

    .nav-toggle.open::after {
        transform: rotate(90deg);
    }

    .sidebar-footer {
        padding: 16px 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        margin: -8px;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .user-menu:hover {
        background: var(--color-sidebar-hover);
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
    }

    .user-name {
        font-size: 14px;
        font-weight: 500;
        color: #fff;
    }

    .user-role {
        font-size: 12px;
        color: var(--color-sidebar-text);
    }

    /* ============================================
       TOPBAR
       ============================================ */
    .topbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .topbar-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-text-primary);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .topbar-badge {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
        background: var(--color-warning-bg);
        color: var(--color-warning-text);
    }

    /* Theme Toggle */
    .theme-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--color-bg-tertiary);
        border: 1px solid var(--color-border);
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .theme-toggle:hover {
        border-color: var(--color-accent);
    }

    .theme-toggle-icon {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .theme-toggle-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--color-text-secondary);
    }

    [data-theme="dark"] .theme-toggle-icon.sun { display: inline; }
    [data-theme="dark"] .theme-toggle-icon.moon { display: none; }
    [data-theme="light"] .theme-toggle-icon.sun { display: none; }
    [data-theme="light"] .theme-toggle-icon.moon { display: inline; }

    /* ============================================
       BUTTONS
       ============================================ */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 16px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        line-height: 1;
        border-radius: var(--radius-sm);
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-primary {
        background: var(--color-accent);
        color: #fff;
        border-color: var(--color-accent);
    }

    .btn-primary:hover:not(:disabled) {
        background: var(--color-accent-hover);
        border-color: var(--color-accent-hover);
        color: #fff;
    }

    .btn-secondary {
        background: var(--color-bg-primary);
        color: var(--color-text-primary);
        border-color: var(--color-border);
    }

    .btn-secondary:hover:not(:disabled) {
        background: var(--color-bg-tertiary);
        color: var(--color-text-primary);
    }

    .btn-ghost {
        background: transparent;
        color: var(--color-text-secondary);
    }

    .btn-ghost:hover:not(:disabled) {
        background: var(--color-bg-tertiary);
        color: var(--color-text-primary);
    }

    .btn-danger {
        background: var(--color-danger-bg);
        color: var(--color-danger);
    }

    .btn-danger:hover:not(:disabled) {
        background: var(--color-danger);
        color: #fff;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
    }

    .btn-icon {
        padding: 8px;
        width: 36px;
        height: 36px;
    }

    .btn-icon.btn-sm {
        width: 32px;
        height: 32px;
        padding: 6px;
    }

    /* ============================================
       CARDS
       ============================================ */
    .card {
        background: var(--color-bg-primary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--color-text-primary);
    }

    .card-body {
        padding: 20px;
    }

    .card-footer {
        padding: 16px 20px;
        border-top: 1px solid var(--color-border);
        background: var(--color-bg-secondary);
    }

    /* ============================================
       FORMS
       ============================================ */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: var(--color-text-primary);
        margin-bottom: 6px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 12px;
        font-family: inherit;
        font-size: 14px;
        color: var(--color-text-primary);
        background: var(--color-input-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 3px var(--color-accent-muted);
    }

    .form-input::placeholder {
        color: var(--color-text-muted);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    /* ============================================
       TABLES
       ============================================ */
    .table-container {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-text-muted);
        background: var(--color-bg-secondary);
        border-bottom: 1px solid var(--color-border);
    }

    .table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--color-border-light);
        vertical-align: middle;
    }

    .table tbody tr {
        transition: background 0.15s ease;
    }

    .table tbody tr:hover {
        background: var(--color-bg-secondary);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ============================================
       BADGES
       ============================================ */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 20px;
    }

    .badge-default {
        background: var(--color-bg-tertiary);
        color: var(--color-text-secondary);
    }

    .badge-success {
        background: var(--color-success-bg);
        color: var(--color-success-text);
    }

    .badge-warning {
        background: var(--color-warning-bg);
        color: var(--color-warning-text);
    }

    .badge-danger {
        background: var(--color-danger-bg);
        color: var(--color-danger-text);
    }

    .badge-dot::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ============================================
       ALERTS
       ============================================ */
    .alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        border-radius: var(--radius-md);
        font-size: 14px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: var(--color-success-bg);
        color: var(--color-success-text);
    }

    .alert-warning {
        background: var(--color-warning-bg);
        color: var(--color-warning-text);
    }

    .alert-danger {
        background: var(--color-danger-bg);
        color: var(--color-danger-text);
    }

    .alert-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
    }

    .alert-content {
        flex: 1;
    }

    /* ============================================
       STATS
       ============================================ */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--color-bg-primary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 20px;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .stat-header {
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .stat-icon.primary {
        background: var(--color-accent-muted);
    }

    .stat-icon.success {
        background: var(--color-success-bg);
    }

    .stat-icon.warning {
        background: var(--color-warning-bg);
    }

    .stat-icon.danger {
        background: var(--color-danger-bg);
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text-primary);
    }

    .stat-label {
        font-size: 14px;
        color: var(--color-text-muted);
        margin-top: 4px;
    }

    .stat-change {
        font-size: 13px;
        margin-top: 8px;
    }

    .stat-change.positive {
        color: var(--color-success);
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 48px 24px;
        text-align: center;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 8px;
    }

    .empty-state-description {
        font-size: 14px;
        color: var(--color-text-muted);
        max-width: 300px;
        margin-bottom: 24px;
    }

    /* ============================================
       UTILITIES
       ============================================ */
    .text-muted { color: var(--color-text-muted); }
    .text-success { color: var(--color-success); }
    .text-warning { color: var(--color-warning); }
    .text-danger { color: var(--color-danger); }

    .mb-4 { margin-bottom: 16px; }
    .mb-6 { margin-bottom: 24px; }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 1024px) {
        .admin-sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .admin-sidebar.open {
            transform: translateX(0);
        }

        .admin-main {
            margin-left: 0;
        }

        .sidebar-toggle {
            display: block;
        }
    }

    @media (max-width: 640px) {
        .admin-content {
            padding: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .theme-toggle-label {
            display: none;
        }
    }

    /* Hide mobile toggle on desktop */
    #sidebar-toggle {
        display: none;
    }

    @media (max-width: 1024px) {
        #sidebar-toggle {
            display: flex;
        }
    }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/admin" class="sidebar-logo">
                    <span class="sidebar-logo-icon">🤖</span>
                    <span>Jessie</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="/admin" class="nav-item <?= isActive('/admin') ?>">
                        <span class="nav-item-icon">📊</span>
                        <span class="nav-item-text">Dashboard</span>
                    </a>
                </div>
                
                <!-- Content -->
                <div class="nav-section">
                    <div class="nav-section-title">Content</div>
                    <a href="/admin/pages" class="nav-item <?= isActive('/admin/pages') ?>">
                        <span class="nav-item-icon">📄</span>
                        <span class="nav-item-text">Pages</span>
                    </a>
                    <a href="/admin/articles" class="nav-item <?= isActive('/admin/articles') ?>">
                        <span class="nav-item-icon">📝</span>
                        <span class="nav-item-text">Articles</span>
                    </a>
                    <a href="/admin/categories" class="nav-item <?= isActive('/admin/categories') ?>">
                        <span class="nav-item-icon">📁</span>
                        <span class="nav-item-text">Categories</span>
                    </a>
                    <a href="/admin/media" class="nav-item <?= isActive('/admin/media') ?>">
                        <span class="nav-item-icon">🖼️</span>
                        <span class="nav-item-text">Media</span>
                    </a>
                    <button class="nav-item nav-toggle" data-target="content-more">
                        <span class="nav-item-icon">📦</span>
                        <span class="nav-item-text">More</span>
                    </button>
                    <div id="content-more" class="nav-submenu">
                        <a href="/admin/menus" class="nav-item">Menus</a>
                        <a href="/admin/widgets" class="nav-item">Widgets</a>
                        <a href="/admin/content" class="nav-item">Content Blocks</a>
                        <a href="/admin/comments_approve.php" class="nav-item">Comments</a>
                    </div>
                </div>
                
                <!-- SEO -->
                <div class="nav-section">
                    <div class="nav-section-title">SEO</div>
                    <a href="/admin/ai-seo-assistant.php" class="nav-item <?= isActive('/admin/ai-seo-assistant') ?>">
                        <span class="nav-item-icon">🎯</span>
                        <span class="nav-item-text">SEO Assistant</span>
                        <span class="nav-badge">AI</span>
                    </a>
                    <a href="/admin/ai-seo-pages.php" class="nav-item <?= isActive('/admin/ai-seo-pages') ?>">
                        <span class="nav-item-icon">📊</span>
                        <span class="nav-item-text">SEO Pages</span>
                    </a>
                    <a href="/admin/ai-seo-keywords.php" class="nav-item <?= isActive('/admin/ai-seo-keywords') ?>">
                        <span class="nav-item-icon">🔑</span>
                        <span class="nav-item-text">Keywords</span>
                    </a>
                    <button class="nav-item nav-toggle" data-target="seo-more">
                        <span class="nav-item-icon">📈</span>
                        <span class="nav-item-text">More SEO</span>
                    </button>
                    <div id="seo-more" class="nav-submenu">
                        <a href="/admin/ai-seo-dashboard.php" class="nav-item">Dashboard</a>
                        <a href="/admin/ai-seo-reports.php" class="nav-item">Reports</a>
                        <a href="/admin/ai-seo-competitors.php" class="nav-item">Competitors</a>
                        <a href="/admin/ai-seo-schema.php" class="nav-item">Schema</a>
                        <a href="/admin/ai-seo-linking.php" class="nav-item">Internal Links</a>
                        <a href="/admin/seo-redirects.php" class="nav-item">Redirects</a>
                        <a href="/admin/seo-sitemap.php" class="nav-item">Sitemap</a>
                    </div>
                </div>
                
                <!-- AI Tools -->
                <div class="nav-section">
                    <div class="nav-section-title">AI Tools</div>
                    <a href="/admin/ai-copywriter.php" class="nav-item <?= isActive('/admin/ai-copywriter') ?>">
                        <span class="nav-item-icon">✍️</span>
                        <span class="nav-item-text">Copywriter</span>
                        <span class="nav-badge new">NEW</span>
                    </a>
                    <a href="/admin/ai-content-rewrite.php" class="nav-item <?= isActive('/admin/ai-content-rewrite') ?>">
                        <span class="nav-item-icon">🔄</span>
                        <span class="nav-item-text">Rewriter</span>
                    </a>
                    <a href="/admin/ai-images.php" class="nav-item <?= isActive('/admin/ai-images') ?>">
                        <span class="nav-item-icon">🎨</span>
                        <span class="nav-item-text">AI Images</span>
                    </a>
                    <a href="/admin/ai-translate.php" class="nav-item <?= isActive('/admin/ai-translate') ?>">
                        <span class="nav-item-icon">🌍</span>
                        <span class="nav-item-text">Translate</span>
                    </a>
                    <button class="nav-item nav-toggle" data-target="ai-more">
                        <span class="nav-item-icon">🤖</span>
                        <span class="nav-item-text">More AI</span>
                    </button>
                    <div id="ai-more" class="nav-submenu">
                        <a href="/admin/ai-designer" class="nav-item">🎨 AI Designer 4.0</a>
                        <a href="/admin/ai-theme-builder.php" class="nav-item">Theme Builder (Legacy)</a>
                        <a href="/admin/ai-landing.php" class="nav-item">Landing Pages</a>
                        <a href="/admin/ai-forms.php" class="nav-item">AI Forms</a>
                        <a href="/admin/ai-workflow-generator.php" class="nav-item">Workflows</a>
                        <a href="/admin/ai-settings.php" class="nav-item">AI Settings</a>
                    </div>
                </div>
                
                <!-- Marketing -->
                <div class="nav-section">
                    <div class="nav-section-title">Marketing</div>
                    <a href="/admin/email-campaigns.php" class="nav-item <?= isActive('/admin/email-campaigns') ?>">
                        <span class="nav-item-icon">📧</span>
                        <span class="nav-item-text">Email Campaigns</span>
                    </a>
                    <a href="/admin/analytics" class="nav-item <?= isActive('/admin/analytics') ?>">
                        <span class="nav-item-icon">📈</span>
                        <span class="nav-item-text">Analytics</span>
                    </a>
                </div>
                
                <!-- Appearance -->
                <div class="nav-section">
                    <div class="nav-section-title">Appearance</div>
                    <a href="/admin/themes.php" class="nav-item <?= isActive('/admin/themes') ?>">
                        <span class="nav-item-icon">🎨</span>
                        <span class="nav-item-text">Themes</span>
                    </a>
                    <a href="/admin/theme-builder.php" class="nav-item <?= isActive('/admin/theme-builder') ?>">
                        <span class="nav-item-icon">🛠️</span>
                        <span class="nav-item-text">Theme Builder</span>
                    </a>
                    <a href="/admin/jessie-theme-builder" class="nav-item <?= isActive('/admin/jessie-theme-builder') ?>">
                        <span class="nav-item-icon">🏗️</span>
                        <span class="nav-item-text">Page Builder</span>
                    </a>
                    <a href="/admin/plugins-marketplace.php" class="nav-item <?= isActive('/admin/plugins') ?>">
                        <span class="nav-item-icon">🧩</span>
                        <span class="nav-item-text">Plugins</span>
                    </a>
                </div>
                
                <!-- System -->
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <a href="/admin/users" class="nav-item <?= isActive('/admin/users') ?>">
                        <span class="nav-item-icon">👥</span>
                        <span class="nav-item-text">Users</span>
                    </a>
                    <a href="/admin/settings" class="nav-item <?= isActive('/admin/settings') ?>">
                        <span class="nav-item-icon">⚙️</span>
                        <span class="nav-item-text">Settings</span>
                    </a>
                    <a href="/admin/security" class="nav-item <?= isActive('/admin/security') ?>">
                        <span class="nav-item-icon">🔒</span>
                        <span class="nav-item-text">Security</span>
                    </a>
                    <button class="nav-item nav-toggle" data-target="system-more">
                        <span class="nav-item-icon">🔧</span>
                        <span class="nav-item-text">More</span>
                    </button>
                    <div id="system-more" class="nav-submenu">
                        <a href="/admin/backup" class="nav-item">Backups</a>
                        <a href="/admin/logs" class="nav-item">Logs</a>
                        <a href="/admin/extensions" class="nav-item">Extensions</a>
                        <a href="/admin/modules" class="nav-item">Modules</a>
                        <a href="/admin/migrations" class="nav-item">Migrations</a>
                        <a href="/admin/maintenance" class="nav-item">Maintenance</a>
                        <a href="/admin/scheduler.php" class="nav-item">Scheduler</a>
                    </div>
                </div>
                
                <!-- Integrations -->
                <div class="nav-section">
                    <div class="nav-section-title">Integrations</div>
                    <a href="/admin/n8n-workflow-bindings.php" class="nav-item <?= isActive('/admin/n8n') ?>">
                        <span class="nav-item-icon">🔗</span>
                        <span class="nav-item-text">n8n Workflows</span>
                    </a>
                    <a href="/admin/automation-rules.php" class="nav-item <?= isActive('/admin/automation') ?>">
                        <span class="nav-item-icon">⚡</span>
                        <span class="nav-item-text">Automation</span>
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-menu">
                    <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
                    <div>
                        <div class="user-name"><?= esc($username) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
                <a href="/admin/logout" class="btn btn-ghost btn-sm" style="width: 100%; margin-top: 12px; justify-content: center; color: var(--color-sidebar-text);">
                    Sign Out
                </a>
            </div>
        </aside>
        
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="btn btn-ghost btn-icon btn-sm" id="sidebar-toggle" onclick="toggleSidebar()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12h18M3 6h18M3 18h18"/>
                        </svg>
                    </button>
                    <h1 class="topbar-title"><?= esc($title ?? 'Dashboard') ?></h1>
                </div>
                <div class="topbar-right">
                    <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()">
                        <span class="theme-toggle-icon sun">☀️</span>
                        <span class="theme-toggle-icon moon">🌙</span>
                        <span class="theme-toggle-label">Theme</span>
                    </button>
                    <span class="topbar-badge">DEV</span>
                    <a href="/" target="_blank" class="btn btn-ghost btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        View Site
                    </a>
                </div>
            </header>
            
            <div class="admin-content">
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <div class="alert-content"><?= esc($success) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <div class="alert-content"><?= esc($error) ?></div>
                </div>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <script>
    // Theme Toggle
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('cms-theme', next);
    }
    
    // Toggle submenus
    document.querySelectorAll('.nav-toggle').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById(this.dataset.target);
            if (target) {
                target.classList.toggle('open');
                this.classList.toggle('open');
            }
        });
    });
    
    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        if (window.innerWidth <= 1024 && 
            sidebar && toggle &&
            !sidebar.contains(e.target) && 
            !toggle.contains(e.target) &&
            sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
        }
    });
    
    // Auto-open submenu if current page is inside it
    document.querySelectorAll('.nav-submenu .nav-item.active').forEach(item => {
        const submenu = item.closest('.nav-submenu');
        if (submenu) {
            submenu.classList.add('open');
            const toggle = submenu.previousElementSibling;
            if (toggle && toggle.classList.contains('nav-toggle')) {
                toggle.classList.add('open');
            }
        }
    });
    </script>
</body>
</html>
