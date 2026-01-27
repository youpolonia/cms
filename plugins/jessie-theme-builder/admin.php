<?php
/**
 * Jessie Theme Builder - Admin Entry Point
 * Access via: /admin/jessie-theme-builder
 *
 * Styled to match Jessie AI-CMS admin panel
 */

namespace JessieThemeBuilder;

// CMS_ROOT should be defined by index.php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

// Load session
require_once CMS_ROOT . '/core/session.php';

// Auth check
if (!\Core\Session::isLoggedIn()) {
    header('Location: /admin/login');
    exit;
}

// Get post ID if provided
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

// If post_id provided, redirect to proper edit URL
if ($postId > 0) {
    header('Location: /admin/jessie-theme-builder/edit/' . $postId);
    exit;
}

// Get pages from database
$db = \core\Database::connection();
$pages = $db->query("SELECT id, title, slug, status, updated_at FROM pages ORDER BY updated_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);

// Get articles from database
$articles = $db->query("SELECT id, title, slug, status, updated_at FROM articles ORDER BY updated_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);

// Count stats
$totalPages = count($pages);
$totalArticles = count($articles);
$publishedPages = count(array_filter($pages, fn($p) => $p['status'] === 'published'));
$publishedArticles = count(array_filter($articles, fn($a) => $a['status'] === 'published'));

// Get JTB templates count
$templatesCount = 0;
try {
    $stmt = $db->query("SELECT COUNT(*) FROM jtb_templates");
    $templatesCount = (int)$stmt->fetchColumn();
} catch (\Exception $e) {
    // Table may not exist yet
}

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jessie Theme Builder - Jessie AI-CMS</title>
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
        border-radius: var(--radius);
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
    .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        border-radius: var(--radius);
        transition: all 0.15s;
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

    /* MAIN CONTENT */
    .main-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px 24px;
    }

    /* PAGE HEADER */
    .page-header {
        margin-bottom: 32px;
    }
    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .page-header p {
        color: var(--text-muted);
        font-size: 15px;
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }
    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 24px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .stat-icon.pages { background: rgba(99, 102, 241, 0.15); }
    .stat-icon.posts { background: rgba(249, 115, 22, 0.15); }
    .stat-icon.templates { background: rgba(16, 185, 129, 0.15); }
    .stat-icon.media { background: rgba(139, 92, 246, 0.15); }
    .stat-content h3 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-content p {
        color: var(--text-muted);
        font-size: 14px;
        margin-bottom: 4px;
    }
    .stat-content .stat-sub {
        color: var(--success);
        font-size: 13px;
        font-weight: 500;
    }

    /* QUICK ACTIONS */
    .quick-actions {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 32px;
    }
    .quick-actions h2 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
    }
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 20px 16px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: var(--text-primary);
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s;
        text-align: center;
    }
    .action-btn:hover {
        background: var(--accent-muted);
        border-color: var(--accent);
        color: var(--accent);
    }
    .action-btn .icon {
        font-size: 28px;
    }

    /* CONTENT SECTIONS */
    .content-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .content-section {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }
    .section-header h2 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .section-header a {
        font-size: 13px;
        font-weight: 500;
    }
    .content-list {
        max-height: 400px;
        overflow-y: auto;
    }
    .content-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }
    .content-item:last-child {
        border-bottom: none;
    }
    .content-item:hover {
        background: var(--bg-tertiary);
    }
    .content-info {
        flex: 1;
        min-width: 0;
    }
    .content-info h3 {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .content-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: var(--text-muted);
    }
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 10px;
        text-transform: uppercase;
    }
    .status-badge.published {
        background: var(--success-bg);
        color: var(--success);
    }
    .status-badge.draft {
        background: var(--warning-bg);
        color: var(--warning);
    }
    .content-actions {
        display: flex;
        gap: 8px;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 500;
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.15s;
        border: none;
        text-decoration: none;
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
    .btn-secondary:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    .btn-sm {
        padding: 6px 10px;
        font-size: 12px;
    }

    /* EMPTY STATE */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-muted);
    }
    .empty-state .icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .actions-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 900px) {
        .content-sections {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="/admin" class="logo">
                <span class="logo-icon"><img src="/public/assets/images/jessie-logo.svg" alt="Jessie"></span>
                <span>Jessie</span>
            </a>

            <nav class="nav-main">
                <a href="/admin" class="nav-link">📊 Dashboard</a>
                <a href="/admin/jessie-theme-builder" class="nav-link active">🎨 Page Builder</a>
                <a href="/admin/jtb/templates" class="nav-link">📐 Theme Builder</a>
                <a href="/admin/jtb/library" class="nav-link">📚 Library</a>
                <a href="/admin/jtb/theme-settings" class="nav-link">⚙️ Theme Settings</a>
            </nav>

            <div class="topbar-right">
                <button class="theme-btn" id="theme-toggle" title="Toggle theme">
                    <span class="theme-icon">🌙</span>
                </button>
                <a href="/admin" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>🎨 Jessie Theme Builder</h1>
            <p>Create beautiful pages with our visual drag-and-drop builder</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon pages">📄</div>
                <div class="stat-content">
                    <h3><?= $totalPages ?></h3>
                    <p>Total Pages</p>
                    <span class="stat-sub"><?= $publishedPages ?> published</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon posts">📝</div>
                <div class="stat-content">
                    <h3><?= $totalArticles ?></h3>
                    <p>Total Articles</p>
                    <span class="stat-sub"><?= $publishedArticles ?> published</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon templates">📐</div>
                <div class="stat-content">
                    <h3><?= $templatesCount ?></h3>
                    <p>Theme Templates</p>
                    <span class="stat-sub">Headers, Footers, Body</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon media">🧩</div>
                <div class="stat-content">
                    <h3>68</h3>
                    <p>Builder Modules</p>
                    <span class="stat-sub">Ready to use</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <button type="button" class="action-btn" onclick="openCreateModal('page')">
                    <span class="icon">📄</span>
                    New Page
                </button>
                <button type="button" class="action-btn" onclick="openCreateModal('article')">
                    <span class="icon">📝</span>
                    New Article
                </button>
                <a href="/admin/jtb/template/edit?type=header" class="action-btn">
                    <span class="icon">🔝</span>
                    New Header
                </a>
                <a href="/admin/jtb/template/edit?type=footer" class="action-btn">
                    <span class="icon">🔚</span>
                    New Footer
                </a>
                <a href="/admin/jtb/global-modules" class="action-btn">
                    <span class="icon">🧩</span>
                    Global Modules
                </a>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="content-sections">
            <!-- Pages -->
            <div class="content-section">
                <div class="section-header">
                    <h2>📄 Recent Pages</h2>
                    <a href="/admin/pages">View All →</a>
                </div>
                <div class="content-list">
                    <?php if (empty($pages)): ?>
                    <div class="empty-state">
                        <div class="icon">📄</div>
                        <p>No pages found. Create your first page!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($pages, 0, 8) as $page): ?>
                    <div class="content-item">
                        <div class="content-info">
                            <h3><?= esc($page['title']) ?></h3>
                            <div class="content-meta">
                                <span class="status-badge <?= esc($page['status']) ?>"><?= esc($page['status']) ?></span>
                                <span>ID: <?= $page['id'] ?></span>
                            </div>
                        </div>
                        <div class="content-actions">
                            <a href="?post_id=<?= $page['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                            <a href="/page/<?= esc($page['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">👁️ View</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Posts -->
            <div class="content-section">
                <div class="section-header">
                    <h2>📝 Recent Articles</h2>
                    <a href="/admin/articles">View All →</a>
                </div>
                <div class="content-list">
                    <?php if (empty($articles)): ?>
                    <div class="empty-state">
                        <div class="icon">📝</div>
                        <p>No articles found. Create your first article!</p>
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($articles, 0, 8) as $article): ?>
                    <div class="content-item">
                        <div class="content-info">
                            <h3><?= esc($article['title']) ?></h3>
                            <div class="content-meta">
                                <span class="status-badge <?= esc($article['status']) ?>"><?= esc($article['status']) ?></span>
                                <span>ID: <?= $article['id'] ?></span>
                            </div>
                        </div>
                        <div class="content-actions">
                            <a href="?post_id=<?= $article['id'] ?>" class="btn btn-primary btn-sm">✏️ Edit</a>
                            <a href="/article/<?= esc($article['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">👁️ View</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Create Page/Article Modal -->
    <div id="create-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Create New Page</h3>
                <button type="button" class="modal-close" onclick="closeCreateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="create-form" onsubmit="handleCreate(event)">
                    <input type="hidden" id="create-type" value="page">
                    <div class="form-group">
                        <label for="create-title">Title</label>
                        <input type="text" id="create-title" placeholder="Enter title..." required autofocus>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="create-submit">
                            <span class="btn-text">Create &amp; Edit</span>
                            <span class="btn-loading" style="display: none;">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    /* Modal styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .modal-content {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 440px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .modal-header h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .modal-close {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 20px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.15s;
    }
    .modal-close:hover {
        background: var(--danger-bg);
        border-color: var(--danger);
        color: var(--danger);
    }
    .modal-body {
        padding: 24px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }
    .form-group input {
        width: 100%;
        padding: 12px 14px;
        font-size: 14px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: var(--text-primary);
        transition: all 0.15s;
    }
    .form-group input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-muted);
    }
    .form-group input::placeholder {
        color: var(--text-muted);
    }
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    .form-actions .btn {
        min-width: 100px;
    }
    </style>

    <script>
        // CSRF token for API calls
        const csrfToken = '<?= csrf_token() ?>';

        // Theme toggle
        const themeBtn = document.getElementById('theme-toggle');
        const themeIcon = themeBtn.querySelector('.theme-icon');

        function updateThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            themeIcon.textContent = theme === 'dark' ? '🌙' : '☀️';
        }

        themeBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('cms-theme', next);
            updateThemeIcon();
        });

        updateThemeIcon();

        // Create modal functions
        function openCreateModal(type) {
            const modal = document.getElementById('create-modal');
            const title = document.getElementById('modal-title');
            const typeInput = document.getElementById('create-type');
            const titleInput = document.getElementById('create-title');

            typeInput.value = type;
            title.textContent = type === 'page' ? 'Create New Page' : 'Create New Article';
            titleInput.value = '';
            modal.style.display = 'flex';

            // Focus input after modal is visible
            setTimeout(() => titleInput.focus(), 100);
        }

        function closeCreateModal() {
            document.getElementById('create-modal').style.display = 'none';
        }

        // Close modal on overlay click
        document.getElementById('create-modal').addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                closeCreateModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateModal();
            }
        });

        // Handle form submission
        async function handleCreate(e) {
            e.preventDefault();

            const type = document.getElementById('create-type').value;
            const title = document.getElementById('create-title').value.trim();
            const submitBtn = document.getElementById('create-submit');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoading = submitBtn.querySelector('.btn-loading');

            if (!title) {
                alert('Please enter a title');
                return;
            }

            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;

            try {
                const response = await fetch('/api/jtb/create-post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ type, title })
                });

                const data = await response.json();

                if (data.success) {
                    // Redirect to JTB editor
                    window.location.href = data.edit_url;
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    // Reset button state
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Create error:', error);
                alert('Failed to create. Please try again.');
                // Reset button state
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            }
        }
    </script>
</body>
</html>
