<?php
/**
 * Admin Topbar for Legacy Modules
 * Include this file at the start of any legacy admin page
 * Usage: require_once __DIR__ . '/includes/topbar.php';
 * Then call: render_admin_header('Page Title');
 * At end: render_admin_footer();
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/../..'));
}

function get_admin_username(): string {
    return $_SESSION['admin_username'] ?? $_SESSION['cms_admin_username'] ?? 'Admin';
}

function is_nav_active(string $path): string {
    $current = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    if ($path === '/admin' && ($current === '/admin' || $current === '/admin/dashboard')) {
        return 'active';
    }
    return strpos($current, $path) === 0 && $path !== '/admin' ? 'active' : '';
}

function render_admin_header(string $title = 'Admin', bool $fullWidth = false): void {
    $username = get_admin_username();
    $maxWidth = $fullWidth ? 'none' : '1600px';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Jessie AI-CMS</title>
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
        max-width: <?= $maxWidth ?>;
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
    }
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
    }
    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px 6px 6px;
        background: var(--bg-tertiary);
        border-radius: 24px;
        cursor: pointer;
        text-decoration: none;
    }
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
    }
    .user-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
    }

    .main-content {
        max-width: <?= $maxWidth ?>;
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
        text-decoration: none;
    }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-primary:hover { background: var(--accent-hover); color: #fff; }
    .btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
    .btn-success { background: var(--success); color: #fff; }
    .btn-danger { background: var(--danger); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 13px; }
    .btn-ai { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; }

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

    .alert {
        padding: 12px 16px;
        border-radius: var(--radius);
        margin-bottom: 16px;
    }
    .alert-success { background: var(--success-bg); color: var(--success); border: 1px solid var(--success); }
    .alert-warning { background: var(--warning-bg); color: var(--warning); border: 1px solid var(--warning); }
    .alert-danger { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); }

    @media (max-width: 900px) {
        .nav-main { display: none; }
    }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="/admin" class="logo">
                <span class="logo-icon">🤖</span>
                <span>Jessie</span>
            </a>
            
            <nav class="nav-main">
                <a href="/admin" class="nav-link <?= is_nav_active('/admin') ?>">📊 Dashboard</a>
                
                <div class="nav-dropdown">
                    <span class="nav-link">📄 Content ▾</span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/pages" class="nav-dropdown-item">📄 Pages</a>
                        <a href="/admin/articles" class="nav-dropdown-item">📝 Articles</a>
                        <a href="/admin/categories" class="nav-dropdown-item">📁 Categories</a>
                        <a href="/admin/media" class="nav-dropdown-item">🖼️ Media</a>
                    </div>
                </div>
                
                <div class="nav-dropdown">
                    <span class="nav-link">🎯 SEO ▾ <span class="nav-badge">AI</span></span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/ai-seo-assistant" class="nav-dropdown-item">🎯 SEO Assistant</a>
                        <a href="/admin/ai-seo-pages" class="nav-dropdown-item">📊 SEO Pages</a>
                        <a href="/admin/ai-seo-keywords" class="nav-dropdown-item">🔑 Keywords</a>
                        <a href="/admin/ai-seo-dashboard" class="nav-dropdown-item">📈 SEO Dashboard</a>
                        <a href="/admin/ai-seo-reports" class="nav-dropdown-item">📋 Reports</a>
                        <a href="/admin/ai-seo-competitors" class="nav-dropdown-item">🏆 Competitors</a>
                        <a href="/admin/ai-seo-linking" class="nav-dropdown-item">🔗 Internal Links</a>
                    </div>
                </div>
                
                <div class="nav-dropdown">
                    <span class="nav-link">🤖 AI Tools ▾</span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/ai-content-creator" class="nav-dropdown-item">✍️ Content Creator</a>
                        <a href="/admin/ai-copywriter" class="nav-dropdown-item">📝 Copywriter</a>
                        <a href="/admin/ai-content-rewrite" class="nav-dropdown-item">🔄 Rewriter</a>
                        <a href="/admin/ai-images" class="nav-dropdown-item">🎨 AI Images</a>
                        <a href="/admin/ai-translate" class="nav-dropdown-item">🌍 Translate</a>
                        <a href="/admin/ai-theme-builder" class="nav-dropdown-item">🎭 Theme Builder</a>
                        <a href="/admin/ai-settings" class="nav-dropdown-item">⚙️ AI Settings</a>
                    </div>
                </div>
                
                <div class="nav-dropdown">
                    <span class="nav-link">📢 Marketing ▾</span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/email-campaigns" class="nav-dropdown-item">📧 Campaigns</a>
                        <a href="/admin/analytics" class="nav-dropdown-item">📈 Analytics</a>
                    </div>
                </div>
                
                <div class="nav-dropdown">
                    <span class="nav-link">🎨 Appearance ▾</span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/themes" class="nav-dropdown-item">🎨 Themes</a>
                        <a href="/admin/theme-builder" class="nav-dropdown-item">🛠️ Theme Builder</a>
                        <a href="/admin/tb4" class="nav-dropdown-item">🏗️ TB4 Builder</a>
                    </div>
                </div>
                
                <div class="nav-dropdown">
                    <span class="nav-link">⚙️ System ▾</span>
                    <div class="nav-dropdown-menu">
                        <a href="/admin/users" class="nav-dropdown-item">👥 Users</a>
                        <a href="/admin/settings" class="nav-dropdown-item">⚙️ Settings</a>
                        <a href="/admin/extensions" class="nav-dropdown-item">🔌 Extensions</a>
                        <a href="/admin/logs" class="nav-dropdown-item">📋 Logs</a>
                        <a href="/admin/backup" class="nav-dropdown-item">💾 Backups</a>
                    </div>
                </div>
            </nav>
            
            <div class="topbar-right">
                <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="theme-icon">🌙</span>
                </button>
                <a href="/admin/logout" class="user-menu">
                    <span class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></span>
                    <span class="user-name"><?= htmlspecialchars($username) ?></span>
                </a>
            </div>
        </div>
    </header>
    
    <main class="main-content">
<?php
}

function render_admin_footer(): void {
?>
    </main>
    
    <script>
    function toggleTheme() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('cms-theme', next);
        document.getElementById('theme-icon').textContent = next === 'dark' ? '🌙' : '☀️';
    }
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        document.getElementById('theme-icon').textContent = theme === 'dark' ? '🌙' : '☀️';
    });
    </script>
</body>
</html>
<?php
}
