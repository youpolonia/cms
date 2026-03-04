<?php
/**
 * Legacy Admin Header — now uses unified topbar_nav.php for consistency & responsive
 */
require_once __DIR__ . '/../../config.php';
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(dirname(__DIR__))); }

$username = $_SESSION['admin_username'] ?? 'Admin';
$pageTitle = $pageTitle ?? 'Dashboard';

if (!function_exists('esc_h')) {
    function esc_h($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
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
        --bg-primary: #ffffff; --bg-secondary: #f8fafc; --bg-tertiary: #f1f5f9;
        --text-primary: #0f172a; --text-secondary: #475569; --text-muted: #94a3b8;
        --border: #e2e8f0; --accent: #6366f1; --accent-hover: #4f46e5;
        --accent-muted: rgba(99,102,241,0.1);
        --success: #10b981; --success-bg: #d1fae5; --warning: #f59e0b; --warning-bg: #fef3c7;
        --danger: #ef4444; --danger-bg: #fee2e2; --card-bg: #ffffff;
    }
    [data-theme="dark"] {
        --bg-primary: #1e1e2e; --bg-secondary: #181825; --bg-tertiary: #313244;
        --text-primary: #cdd6f4; --text-secondary: #a6adc8; --text-muted: #6c7086;
        --border: #313244; --accent: #89b4fa; --accent-hover: #b4befe;
        --accent-muted: rgba(137,180,250,0.15);
        --success: #a6e3a1; --success-bg: rgba(166,227,161,0.15); --warning: #f9e2af; --warning-bg: rgba(249,226,175,0.15);
        --danger: #f38ba8; --danger-bg: rgba(243,139,168,0.15); --card-bg: #1e1e2e;
    }
    :root { --font: 'Inter',-apple-system,BlinkMacSystemFont,sans-serif; --radius: 8px; --radius-lg: 12px; }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{font-size:16px;-webkit-font-smoothing:antialiased}
    body{font-family:var(--font);font-size:14px;line-height:1.5;color:var(--text-primary);background:var(--bg-secondary);min-height:100vh}
    a{color:var(--accent);text-decoration:none} a:hover{color:var(--accent-hover)}
    .main-content{max-width:1600px;margin:0 auto;padding:24px}
    .container{max-width:1400px;margin:0 auto}
    h1{font-size:1.5rem;font-weight:600;margin-bottom:1rem;color:var(--text-primary)}
    h2{font-size:1.25rem;font-weight:600;margin-bottom:.75rem;color:var(--text-primary)}
    .muted{color:var(--text-muted);font-size:.875rem}
    .alert{padding:.875rem 1rem;border-radius:var(--radius);margin-bottom:1rem;font-size:.875rem}
    .alert-success,.alert.success{background:var(--success-bg);color:var(--success);border:1px solid var(--success)}
    .alert-error,.alert.error{background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger)}
    .alert-warning{background:var(--warning-bg);color:var(--warning);border:1px solid var(--warning)}
    .card{background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-lg);margin-bottom:1.5rem}
    .card-header{padding:1rem 1.25rem;border-bottom:1px solid var(--border)}
    .card-title{font-size:1rem;font-weight:600} .card-body{padding:1.25rem}
    .btn,button[type="submit"]{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;font-size:.875rem;font-weight:500;border-radius:var(--radius);border:none;cursor:pointer;text-decoration:none;transition:all .2s}
    .btn.primary,.btn-primary,button[type="submit"]{background:var(--accent);color:#fff}
    .btn.primary:hover,.btn-primary:hover,button[type="submit"]:hover{background:var(--accent-hover)}
    .btn-secondary{background:var(--bg-tertiary);color:var(--text-primary);border:1px solid var(--border)}
    .btn-danger{background:var(--danger-bg);color:var(--danger)} .btn-sm{padding:.375rem .75rem;font-size:.8125rem}
    table{width:100%;border-collapse:collapse}
    th,td{padding:.75rem 1rem;text-align:left;border-bottom:1px solid var(--border)}
    th{font-weight:600;color:var(--text-secondary);font-size:.8125rem;text-transform:uppercase}
    input,select,textarea{padding:.5rem .75rem;font-size:.875rem;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg-primary);color:var(--text-primary);width:100%}
    input:focus,select:focus,textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-muted)}
    label{display:block;margin-bottom:.5rem;font-weight:500;color:var(--text-secondary)}
    .form-group{margin-bottom:1rem}
    </style>
    <?php if (file_exists(CMS_ROOT . '/assets/css/theme-custom.css')): ?>
    <link rel="stylesheet" href="/assets/css/theme-custom.css?v=<?= filemtime(CMS_ROOT . '/assets/css/theme-custom.css') ?>">
    <?php endif; ?>
</head>
<body>
    <?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
    <main class="main-content">
