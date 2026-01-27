<?php
/**
 * Template Manager View
 * Theme Builder Dashboard - Jessie AI-CMS Style
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Variables available: $pluginUrl, $templates, $counts, $csrfToken
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Builder - Jessie CMS</title>
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
    }
    .page-header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .page-header-left p {
        color: var(--text-muted);
        font-size: 15px;
    }
    .page-header-right {
        display: flex;
        gap: 12px;
    }

    /* FILTER TABS */
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 32px;
        padding: 4px;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        width: fit-content;
    }
    .filter-tab {
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
        background: transparent;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .filter-tab:hover {
        color: var(--text-primary);
        background: var(--bg-tertiary);
    }
    .filter-tab.active {
        color: #fff;
        background: var(--accent);
    }

    /* TEMPLATE SECTIONS */
    .template-section {
        margin-bottom: 40px;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title .count {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-muted);
    }

    /* TEMPLATE GRID */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    /* TEMPLATE CARD */
    .template-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        transition: all 0.2s;
    }
    .template-card:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .template-preview {
        height: 160px;
        background: var(--bg-tertiary);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .template-preview svg {
        width: 100%;
        max-width: 200px;
        height: auto;
        color: var(--text-muted);
        opacity: 0.5;
    }
    .template-info {
        padding: 16px;
        border-top: 1px solid var(--border);
    }
    .template-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .template-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .template-badge {
        display: inline-block;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        text-transform: uppercase;
    }
    .template-badge.default {
        background: var(--success-bg);
        color: var(--success);
    }
    .template-badge.conditions {
        background: var(--accent-muted);
        color: var(--accent);
    }
    .template-actions {
        display: flex;
        gap: 8px;
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: var(--bg-secondary);
    }

    /* ADD NEW CARD */
    .template-card.add-new {
        border-style: dashed;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 280px;
    }
    .template-card.add-new:hover {
        border-color: var(--accent);
        background: var(--accent-muted);
    }
    .add-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--bg-tertiary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: var(--text-muted);
        margin-bottom: 12px;
        transition: all 0.2s;
    }
    .template-card.add-new:hover .add-icon {
        background: var(--accent);
        color: #fff;
    }
    .add-text {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    /* BUTTONS */
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
        font-family: inherit;
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
    .btn-danger {
        background: transparent;
        color: var(--danger);
        border: 1px solid var(--danger);
    }
    .btn-danger:hover {
        background: var(--danger-bg);
    }
    .btn-sm {
        padding: 6px 10px;
        font-size: 12px;
    }

    /* MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .modal {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 480px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .modal-title {
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
        background: transparent;
        border: none;
        color: var(--text-muted);
        font-size: 24px;
        cursor: pointer;
        border-radius: var(--radius);
        transition: all 0.15s;
    }
    .modal-close:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .modal-body {
        padding: 24px;
    }
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        background: var(--bg-secondary);
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }

    /* FORM FIELDS */
    .field {
        margin-bottom: 20px;
    }
    .field:last-child {
        margin-bottom: 0;
    }
    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .input-text,
    .input-select {
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        transition: all 0.15s;
    }
    .input-text:focus,
    .input-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-muted);
    }
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--accent);
    }
    .checkbox-label {
        font-size: 14px;
        color: var(--text-secondary);
    }

    /* NOTIFICATIONS */
    .notifications {
        position: fixed;
        top: 80px;
        right: 24px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .notification {
        padding: 14px 20px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    }
    .notification.success {
        border-left: 4px solid var(--success);
    }
    .notification.error {
        border-left: 4px solid var(--danger);
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .filter-tabs {
            flex-wrap: wrap;
            width: 100%;
        }
        .template-grid {
            grid-template-columns: 1fr;
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
                <a href="/admin" class="nav-link">Dashboard</a>
                <a href="/admin/jessie-theme-builder" class="nav-link">Page Builder</a>
                <a href="/admin/jtb/templates" class="nav-link active">Theme Builder</a>
                <a href="/admin/jtb/library" class="nav-link">Library</a>
                <a href="/admin/jtb/theme-settings" class="nav-link">Theme Settings</a>
                <a href="/admin/jtb/global-modules" class="nav-link">Global Modules</a>
            </nav>

            <div class="topbar-right">
                <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="themeIcon">🌙</span>
                </button>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Theme Builder</h1>
                <p>Create and manage header, footer, and body templates for your site</p>
            </div>
            <div class="page-header-right">
                <button class="btn btn-primary" onclick="JTBTemplateManager.showCreateModal()">
                    + New Template
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" data-type="all">
                All (<?= (int) $counts['total'] ?>)
            </button>
            <button class="filter-tab" data-type="header">
                Headers (<?= (int) $counts['header'] ?>)
            </button>
            <button class="filter-tab" data-type="footer">
                Footers (<?= (int) $counts['footer'] ?>)
            </button>
            <button class="filter-tab" data-type="body">
                Body (<?= (int) $counts['body'] ?>)
            </button>
        </div>

        <!-- Template Sections -->
        <?php foreach (['header', 'footer', 'body'] as $type): ?>
        <section class="template-section" data-type="<?= $type ?>">
            <h2 class="section-title">
                <?php if ($type === 'header'): ?>🔝<?php elseif ($type === 'footer'): ?>🔚<?php else: ?>📄<?php endif; ?>
                <?= ucfirst($type) ?> Templates
                <span class="count">(<?= count($templates[$type] ?? []) ?>)</span>
            </h2>

            <div class="template-grid">
                <?php if (!empty($templates[$type])): ?>
                    <?php foreach ($templates[$type] as $template): ?>
                    <div class="template-card" data-id="<?= (int) $template['id'] ?>" data-type="<?= htmlspecialchars($type) ?>">
                        <div class="template-preview">
                            <?php if ($type === 'header'): ?>
                                <svg viewBox="0 0 100 40" fill="currentColor">
                                    <rect x="5" y="15" width="20" height="10" rx="2"/>
                                    <rect x="30" y="17" width="15" height="6" rx="1"/>
                                    <rect x="48" y="17" width="15" height="6" rx="1"/>
                                    <rect x="66" y="17" width="15" height="6" rx="1"/>
                                    <rect x="85" y="15" width="10" height="10" rx="2"/>
                                </svg>
                            <?php elseif ($type === 'footer'): ?>
                                <svg viewBox="0 0 100 40" fill="currentColor">
                                    <rect x="5" y="5" width="25" height="30" rx="2"/>
                                    <rect x="35" y="5" width="25" height="30" rx="2"/>
                                    <rect x="65" y="5" width="30" height="30" rx="2"/>
                                </svg>
                            <?php else: ?>
                                <svg viewBox="0 0 100 60" fill="currentColor">
                                    <rect x="5" y="5" width="90" height="15" rx="2"/>
                                    <rect x="5" y="25" width="60" height="30" rx="2"/>
                                    <rect x="70" y="25" width="25" height="30" rx="2"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="template-info">
                            <h3 class="template-name"><?= htmlspecialchars($template['name']) ?></h3>
                            <div class="template-badges">
                                <?php if ($template['is_default']): ?>
                                    <span class="template-badge default">Default</span>
                                <?php endif; ?>
                                <?php if (!empty($template['conditions'])): ?>
                                    <span class="template-badge conditions"><?= count($template['conditions']) ?> condition(s)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="template-actions">
                            <a href="/admin/jtb/template/edit/<?= (int) $template['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <button class="btn btn-sm btn-secondary" onclick="JTBTemplateManager.duplicateTemplate(<?= (int) $template['id'] ?>)">Duplicate</button>
                            <?php if (!$template['is_default']): ?>
                                <button class="btn btn-sm btn-secondary" onclick="JTBTemplateManager.setDefault(<?= (int) $template['id'] ?>)">Set Default</button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="JTBTemplateManager.deleteTemplate(<?= (int) $template['id'] ?>, '<?= htmlspecialchars(addslashes($template['name'])) ?>')">Delete</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Add New Card -->
                <div class="template-card add-new" onclick="JTBTemplateManager.showCreateModal('<?= $type ?>')">
                    <div class="add-icon">+</div>
                    <div class="add-text">Add New <?= ucfirst($type) ?></div>
                </div>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <!-- Create Template Modal -->
    <div class="modal-overlay" id="createModal" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Create New Template</h3>
                <button class="modal-close" onclick="JTBTemplateManager.hideCreateModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label class="field-label">Template Name</label>
                    <input type="text" id="newTemplateName" class="input-text" placeholder="My Template">
                </div>
                <div class="field">
                    <label class="field-label">Template Type</label>
                    <select id="newTemplateType" class="input-select">
                        <option value="header">Header</option>
                        <option value="footer">Footer</option>
                        <option value="body">Body Template</option>
                    </select>
                </div>
                <div class="field">
                    <label class="checkbox-item">
                        <input type="checkbox" id="newTemplateDefault">
                        <span class="checkbox-label">Set as default for this type</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="JTBTemplateManager.hideCreateModal()">Cancel</button>
                <button class="btn btn-primary" onclick="JTBTemplateManager.createTemplate()">Create Template</button>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="notifications" id="notifications"></div>

    <script>
        // Theme toggle
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('cms-theme', next);
            document.getElementById('themeIcon').textContent = next === 'dark' ? '🌙' : '☀️';
        }
        // Set initial icon
        document.addEventListener('DOMContentLoaded', () => {
            const theme = document.documentElement.getAttribute('data-theme');
            document.getElementById('themeIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
        });

        // CSRF token for API calls
        window.JTB_CSRF_TOKEN = '<?= htmlspecialchars($csrfToken ?? '') ?>';
    </script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/template-manager.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            JTBTemplateManager.init();

            // Filter tabs functionality
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const type = this.dataset.type;

                    // Update active state
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Show/hide sections
                    document.querySelectorAll('.template-section').forEach(section => {
                        if (type === 'all' || section.dataset.type === type) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
