<?php
/**
 * Admin Layout - Topbar Only (No Sidebar)
 * Uses centralized topbar_nav.php for navigation
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); }
require_once CMS_ROOT . '/core/session.php';

$username = \Core\Session::getAdminUsername() ?? 'Admin';

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= function_exists("wl_admin_title") ? wl_admin_title($title ?? "Admin") : esc($title ?? "Admin") . " - Jessie AI-CMS" ?></title>
    <?php if (function_exists("wl_accent_css")) echo wl_accent_css(); ?>
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

    /* MAIN */
    .main-content {
        max-width: 1600px;
        margin: 0 auto;
        padding: 32px 24px;
    }
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* CARDS */
    .card {
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
    }
    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }
    .card-title { font-size: 16px; font-weight: 600; }
    .card-body { padding: 20px; }

    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 500;
        border-radius: var(--radius);
        border: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-primary {
        background: var(--accent);
        color: #fff;
    }
    .btn-primary:hover {
        background: var(--accent-hover);
        color: #fff;
    }
    .btn-secondary {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }
    .btn-danger {
        background: rgba(243, 139, 168, 0.2);
        color: #f38ba8;
        border: 1px solid rgba(243, 139, 168, 0.4);
    }
    .btn-danger:hover {
        background: rgba(243, 139, 168, 0.35);
        border-color: #f38ba8;
    }
    .btn-ghost {
        background: transparent;
        color: var(--text-secondary);
    }
    .btn-sm { padding: 6px 12px; font-size: 13px; }

    /* STATS */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 12px;
    }
    .stat-icon.primary { background: var(--accent-muted); }
    .stat-icon.success { background: var(--success-bg); }
    .stat-icon.warning { background: var(--warning-bg); }
    .stat-icon.danger { background: var(--danger-bg); }
    .stat-value { font-size: 32px; font-weight: 700; }
    .stat-label { font-size: 14px; color: var(--text-muted); margin-top: 4px; }
    .stat-change { font-size: 13px; color: var(--success); margin-top: 8px; }

    /* BADGES */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 20px;
    }
    .badge-success { background: var(--success-bg); color: var(--success); }
    .badge-warning { background: var(--warning-bg); color: var(--warning); }
    .badge-danger { background: var(--danger-bg); color: var(--danger); }

    /* TABLES */
    .table { width: 100%; border-collapse: collapse; }
    .table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        background: var(--bg-secondary);
        border-bottom: 1px solid var(--border);
    }
    .table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
    }
    .table tbody tr:hover { background: var(--bg-secondary); }

    /* FORMS */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-muted);
    }


    /* Inline Help Banners */
    .inline-help {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        background: rgba(99,102,241,.08);
        border: 1px solid rgba(99,102,241,.2);
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-secondary, #b4befe);
    }
    .inline-help-icon { font-size: 20px; flex-shrink: 0; line-height: 1.3; }
    .inline-help strong { color: var(--text-primary, #cdd6f4); }
    .inline-help a { color: #89b4fa; text-decoration: underline; }
    .inline-help a:hover { color: #b4befe; }
    .inline-help .inline-help-close {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text-muted, #6c7086);
        cursor: pointer;
        font-size: 16px;
        padding: 0 4px;
        line-height: 1;
        flex-shrink: 0;
    }
    .inline-help .inline-help-close:hover { color: var(--text-primary, #cdd6f4); }

    /* Inline Help Tooltips */
    .tip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        color: var(--text-muted, #94a3b8);
        background: var(--bg-tertiary, #313244);
        border: 1px solid var(--border, #45475a);
        border-radius: 50%;
        cursor: help;
        position: relative;
        vertical-align: middle;
        margin-left: 4px;
        transition: color .15s, border-color .15s, background .15s;
        user-select: none;
        flex-shrink: 0;
    }
    .tip:hover {
        color: var(--accent, #6366f1);
        border-color: var(--accent, #6366f1);
        background: var(--accent-muted, rgba(99,102,241,.1));
    }
    .tip::before { content: "?"; }
    .tip-text {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        z-index: 9999;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        width: max-content;
        max-width: 280px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.5;
        color: #e2e8f0;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.3);
        pointer-events: none;
        transition: opacity .15s, visibility .15s;
        white-space: normal;
        text-align: left;
    }
    .tip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #334155;
    }
    .tip:hover .tip-text {
        visibility: visible;
        opacity: 1;
    }
    .tip.tip-down .tip-text {
        bottom: auto;
        top: calc(100% + 8px);
    }
    .tip.tip-down .tip-text::after {
        top: auto;
        bottom: 100%;
        border-top-color: transparent;
        border-bottom-color: #334155;
    }
    </style>
    <?php if (file_exists(CMS_ROOT . '/assets/css/theme-custom.css')): ?>
    <link rel="stylesheet" href="/assets/css/theme-custom.css?v=<?= filemtime(CMS_ROOT . '/assets/css/theme-custom.css') ?>">
    <?php endif; ?>
</head>
<body>
    <?php 
    // Include the ONE centralized topbar navigation
    require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; 
    ?>

    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
</body>
</html>
