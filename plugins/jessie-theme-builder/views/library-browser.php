<?php
/**
 * Template Library Browser
 * Jessie AI-CMS Style
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Variables: $pluginUrl, $csrfToken, $embedMode (optional), $templateType (optional: header/footer/body)
$embedMode = $embedMode ?? false;
$templateType = $templateType ?? ($_GET['template_type'] ?? '');
$isThemeBuilderMode = in_array($templateType, ['header', 'footer', 'body']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isThemeBuilderMode ? ucfirst($templateType) . ' Layouts' : 'Template Library'; ?> - Jessie CMS</title>
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

    <?php if (!$embedMode): ?>
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
        background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .logo-icon svg {
        width: 20px;
        height: 20px;
        color: #fff;
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
    <?php endif; ?>

    /* MAIN LAYOUT */
    .library-layout {
        display: flex;
        min-height: <?php echo $embedMode ? '100vh' : 'calc(100vh - 64px)'; ?>;
    }

    /* SIDEBAR */
    .library-sidebar {
        width: 260px;
        background: var(--bg-primary);
        border-right: 1px solid var(--border);
        padding: 24px;
        flex-shrink: 0;
        overflow-y: auto;
    }
    .sidebar-section {
        margin-bottom: 28px;
    }
    .sidebar-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        margin-bottom: 12px;
    }
    .category-list {
        list-style: none;
    }
    .category-item {
        margin-bottom: 4px;
    }
    .category-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        border-radius: var(--radius);
        transition: all 0.15s;
        cursor: pointer;
    }
    .category-link:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .category-link.active {
        background: var(--accent-muted);
        color: var(--accent);
    }
    .category-link svg {
        width: 18px;
        height: 18px;
        opacity: 0.7;
    }
    .category-count {
        margin-left: auto;
        font-size: 12px;
        color: var(--text-muted);
        background: var(--bg-tertiary);
        padding: 2px 8px;
        border-radius: 10px;
    }

    /* FILTER CHECKBOXES */
    .filter-group {
        margin-top: 8px;
    }
    .filter-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        font-size: 13px;
        color: var(--text-secondary);
        cursor: pointer;
        border-radius: var(--radius);
        transition: all 0.15s;
    }
    .filter-checkbox:hover {
        background: var(--bg-tertiary);
    }
    .filter-checkbox input {
        width: 16px;
        height: 16px;
        accent-color: var(--accent);
    }

    /* MAIN CONTENT */
    .library-main {
        flex: 1;
        padding: 24px 32px;
        overflow-y: auto;
    }

    /* HEADER */
    .library-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .library-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .library-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* SEARCH */
    .search-box {
        position: relative;
        width: 280px;
    }
    .search-box input {
        width: 100%;
        padding: 10px 16px 10px 42px;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 13px;
        color: var(--text-primary);
        transition: all 0.15s;
    }
    .search-box input:focus {
        outline: none;
        border-color: var(--accent);
    }
    .search-box input::placeholder {
        color: var(--text-muted);
    }
    .search-box svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--text-muted);
    }

    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 500;
        border-radius: var(--radius);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }
    .btn svg {
        width: 16px;
        height: 16px;
    }
    .btn-primary {
        background: var(--accent);
        color: #fff;
    }
    .btn-primary:hover {
        background: var(--accent-hover);
    }
    .btn-secondary {
        background: var(--bg-tertiary);
        border-color: var(--border);
        color: var(--text-primary);
    }
    .btn-secondary:hover {
        border-color: var(--accent);
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    /* TEMPLATE GRID */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    /* TEMPLATE CARD */
    .template-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all 0.2s;
        position: relative;
    }
    .template-card:hover {
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .template-card.featured {
        border-color: var(--warning);
    }
    .template-card.featured::before {
        content: '';
        position: absolute;
        top: 12px;
        right: 12px;
        width: 24px;
        height: 24px;
        background: var(--warning);
        border-radius: 50%;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* THUMBNAIL */
    .template-thumb {
        height: 180px;
        background: var(--bg-tertiary);
        position: relative;
        overflow: hidden;
    }
    .template-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .template-thumb-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .template-thumb-placeholder svg {
        width: 48px;
        height: 48px;
        color: var(--text-muted);
        opacity: 0.4;
    }

    /* HOVER OVERLAY */
    .template-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .template-card:hover .template-overlay {
        opacity: 1;
    }
    .template-overlay .btn {
        min-width: 140px;
        justify-content: center;
    }

    /* INFO */
    .template-info {
        padding: 16px;
        border-top: 1px solid var(--border);
    }
    .template-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .template-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: var(--text-muted);
    }
    .template-meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .template-meta svg {
        width: 14px;
        height: 14px;
    }
    .template-badge {
        display: inline-block;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
        background: var(--accent-muted);
        color: var(--accent);
    }
    .template-badge.premade {
        background: var(--success-bg);
        color: var(--success);
    }

    /* EMPTY STATE */
    .empty-state {
        text-align: center;
        padding: 80px 40px;
    }
    .empty-state svg {
        width: 80px;
        height: 80px;
        color: var(--text-muted);
        opacity: 0.5;
        margin-bottom: 20px;
    }
    .empty-state h3 {
        font-size: 1.25rem;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .empty-state p {
        color: var(--text-muted);
        margin-bottom: 24px;
    }

    /* LOADING */
    .loading-spinner {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px;
    }
    .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--border);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* PREVIEW MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.8);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }
    .modal-overlay.open {
        display: flex;
    }
    .modal-content {
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        max-width: 1200px;
        max-height: 90vh;
        width: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
    }
    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
    }
    .modal-close {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        color: var(--text-secondary);
        transition: all 0.15s;
    }
    .modal-close:hover {
        background: var(--danger-bg);
        color: var(--danger);
    }
    .modal-body {
        flex: 1;
        overflow: auto;
    }
    .modal-body iframe {
        width: 100%;
        min-height: 600px;
        height: 4000px;
        border: none;
        display: block;
    }
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-top: 1px solid var(--border);
    }
    .modal-footer-left {
        display: flex;
        gap: 12px;
    }

    /* IMPORT MODAL */
    .import-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius-lg);
        padding: 40px;
        text-align: center;
        margin: 24px;
        transition: all 0.2s;
    }
    .import-zone.dragover {
        border-color: var(--accent);
        background: var(--accent-muted);
    }
    .import-zone input[type="file"] {
        display: none;
    }
    .import-zone svg {
        width: 48px;
        height: 48px;
        color: var(--text-muted);
        margin-bottom: 16px;
    }
    .import-zone p {
        color: var(--text-secondary);
        margin-bottom: 16px;
    }

    /* NOTIFICATION */
    .notification {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 14px 24px;
        border-radius: var(--radius);
        font-size: 14px;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
    }
    .notification.show {
        transform: translateX(0);
    }
    .notification.success {
        background: var(--success);
        color: #fff;
    }
    .notification.error {
        background: var(--danger);
        color: #fff;
    }
    </style>
</head>
<body>

<?php if (!$embedMode): ?>
<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-inner">
        <a href="/admin" class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <span>Jessie</span>
        </a>

        <nav class="nav-main">
            <a href="/admin" class="nav-link">Dashboard</a>
            <a href="/admin/jessie-theme-builder" class="nav-link">Page Builder</a>
            <a href="/admin/jtb/templates" class="nav-link">Theme Builder</a>
            <a href="/admin/jtb/library" class="nav-link active">Library</a>
            <a href="/admin/jtb/theme-settings" class="nav-link">Theme Settings</a>
            <a href="/admin/jtb/global-modules" class="nav-link">Global Modules</a>
        </nav>

        <div class="topbar-right">
            <button class="theme-btn" id="themeToggle" title="Toggle theme">
                <span class="theme-icon-dark">&#9790;</span>
                <span class="theme-icon-light" style="display:none">&#9788;</span>
            </button>
        </div>
    </div>
</header>
<?php endif; ?>

<!-- MAIN LAYOUT -->
<div class="library-layout">
    <!-- SIDEBAR -->
    <aside class="library-sidebar">
        <?php if ($isThemeBuilderMode): ?>
        <!-- Theme Builder Mode - Simplified sidebar -->
        <div class="sidebar-section">
            <div class="sidebar-title"><?= ucfirst($templateType) ?> Layouts</div>
            <ul class="category-list" id="categoryList">
                <li class="category-item">
                    <div class="category-link active" data-category="">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?php if ($templateType === 'header'): ?>
                            <rect x="3" y="3" width="18" height="6" rx="1"/>
                            <rect x="3" y="12" width="18" height="9" rx="1" opacity="0.3"/>
                            <?php elseif ($templateType === 'footer'): ?>
                            <rect x="3" y="3" width="18" height="9" rx="1" opacity="0.3"/>
                            <rect x="3" y="15" width="18" height="6" rx="1"/>
                            <?php else: ?>
                            <rect x="3" y="3" width="18" height="18" rx="1"/>
                            <?php endif; ?>
                        </svg>
                        <span>All <?= ucfirst($templateType) ?>s</span>
                        <span class="category-count" id="countAll">0</span>
                    </div>
                </li>
            </ul>
        </div>
        <?php else: ?>
        <!-- Page Builder Mode - Full sidebar -->
        <div class="sidebar-section">
            <div class="sidebar-title">Categories</div>
            <ul class="category-list" id="categoryList">
                <li class="category-item">
                    <div class="category-link active" data-category="">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span>All Templates</span>
                        <span class="category-count" id="countAll">0</span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Type</div>
            <div class="filter-group">
                <label class="filter-checkbox">
                    <input type="checkbox" id="filterPages" checked>
                    <span>Full Pages</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" id="filterSections" checked>
                    <span>Sections</span>
                </label>
            </div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Source</div>
            <div class="filter-group">
                <label class="filter-checkbox">
                    <input type="checkbox" id="filterPremade" checked>
                    <span>Premade Templates</span>
                </label>
                <label class="filter-checkbox">
                    <input type="checkbox" id="filterMine" checked>
                    <span>My Templates</span>
                </label>
            </div>
        </div>
        <?php endif; ?>
    </aside>

    <!-- MAIN -->
    <main class="library-main">
        <div class="library-header">
            <h1 class="library-title"><?php echo $isThemeBuilderMode ? ucfirst($templateType) . ' Layouts' : 'Template Library'; ?></h1>
            <div class="library-actions">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search <?php echo $isThemeBuilderMode ? $templateType . ' layouts' : 'templates'; ?>...">
                </div>
                <?php if (!$isThemeBuilderMode): ?>
                <button class="btn btn-secondary" id="importBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Import
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- TEMPLATES GRID -->
        <div id="templateGrid" class="template-grid">
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div id="emptyState" class="empty-state" style="display: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <path d="M22 6l-10 7L2 6"/>
            </svg>
            <h3>No templates found</h3>
            <p>Try adjusting your filters or search query</p>
            <button class="btn btn-primary" id="resetFiltersBtn">Reset Filters</button>
        </div>
    </main>
</div>

<!-- PREVIEW MODAL -->
<div class="modal-overlay" id="previewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="previewTitle">Template Preview</h2>
            <button class="modal-close" id="closePreview">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <iframe id="previewFrame" src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <div class="modal-footer-left">
                <button class="btn btn-secondary" id="exportBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export JSON
                </button>
                <button class="btn btn-secondary" id="duplicateBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Duplicate
                </button>
            </div>
            <button class="btn btn-primary" id="useTemplateBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Use This Template
            </button>
        </div>
    </div>
</div>

<!-- IMPORT MODAL -->
<div class="modal-overlay" id="importModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 class="modal-title">Import Template</h2>
            <button class="modal-close" id="closeImport">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="import-zone" id="importZone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <p>Drag and drop a JSON file here, or click to browse</p>
            <input type="file" id="importFile" accept=".json,application/json">
            <button class="btn btn-secondary">Choose File</button>
        </div>
    </div>
</div>

<script>
window.JTB_CSRF_TOKEN = '<?php echo $csrfToken ?? ''; ?>';
window.JTB_EMBED_MODE = <?php echo $embedMode ? 'true' : 'false'; ?>;
window.JTB_TEMPLATE_TYPE = '<?php echo htmlspecialchars($templateType); ?>';
window.JTB_IS_THEME_BUILDER_MODE = <?php echo $isThemeBuilderMode ? 'true' : 'false'; ?>;
</script>
<script src="<?php echo $pluginUrl; ?>/assets/js/library.js?v=<?php echo time(); ?>"></script>

<?php if (!$embedMode): ?>
<script>
// Theme toggle
document.getElementById('themeToggle')?.addEventListener('click', function() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('cms-theme', next);
});
</script>
<?php endif; ?>

</body>
</html>
