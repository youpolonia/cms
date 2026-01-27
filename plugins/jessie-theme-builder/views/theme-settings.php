<?php
/**
 * JTB Theme Settings View
 * Global theme settings panel - Jessie AI-CMS Style
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Variables from controller:
// $settings - current settings
// $defaults - default settings
// $groupLabels - group labels
// $csrfToken - CSRF token
// $pluginUrl - plugin URL
// $fontOptions - font select options

$esc = function($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
};

// Flash message
$message = $_SESSION['jtb_message'] ?? null;
unset($_SESSION['jtb_message']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Settings - Jessie Theme Builder</title>
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

    /* MAIN LAYOUT */
    .main-layout {
        display: flex;
        min-height: calc(100vh - 64px);
    }

    /* SIDEBAR */
    .sidebar {
        width: 260px;
        background: var(--bg-primary);
        border-right: 1px solid var(--border);
        padding: 24px 0;
        flex-shrink: 0;
        position: sticky;
        top: 64px;
        height: calc(100vh - 64px);
        overflow-y: auto;
    }
    .sidebar-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        padding: 0 20px 12px;
    }
    .sidebar-nav {
        list-style: none;
    }
    .jtb-settings-nav-item {
        margin-bottom: 2px;
    }
    .jtb-settings-nav-item a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-secondary);
        transition: all 0.15s;
    }
    .jtb-settings-nav-item a:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .jtb-settings-nav-item.active a {
        background: var(--accent-muted);
        color: var(--accent);
    }
    .jtb-settings-nav-item svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .sidebar-actions {
        padding: 20px;
        border-top: 1px solid var(--border);
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* MAIN CONTENT */
    .main-content {
        flex: 1;
        padding: 32px;
        max-width: 1000px;
    }

    /* PAGE HEADER */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
    }
    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .page-header-actions {
        display: flex;
        gap: 12px;
    }

    /* MESSAGE */
    .message {
        padding: 14px 20px;
        border-radius: var(--radius);
        margin-bottom: 24px;
        font-size: 14px;
    }
    .message-success {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid var(--success);
    }
    .message-error {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    /* SETTINGS SECTION */
    .jtb-settings-section {
        display: none;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }
    .jtb-settings-section.active {
        display: block;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-tertiary);
    }
    .section-header h2 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .section-body {
        padding: 24px;
    }
    .section-subheading {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 24px 0 16px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
    }
    .section-subheading:first-child {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    /* SETTINGS GRID */
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    /* FIELD */
    .jtb-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .jtb-field.jtb-field-full {
        grid-column: 1 / -1;
    }
    .jtb-field label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
    }

    /* COLOR FIELD */
    .jtb-color-input-wrapper {
        display: flex;
        gap: 8px;
    }
    .jtb-color-input-wrapper input[type="color"] {
        width: 48px;
        height: 40px;
        padding: 4px;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--bg-tertiary);
        cursor: pointer;
    }
    .jtb-color-input-wrapper input[type="text"] {
        flex: 1;
        padding: 8px 12px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }
    .jtb-color-input-wrapper input:focus {
        outline: none;
        border-color: var(--accent);
    }

    /* RANGE FIELD */
    .jtb-range-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .jtb-range-wrapper input[type="range"] {
        flex: 1;
        height: 6px;
        background: var(--bg-tertiary);
        border-radius: 3px;
        appearance: none;
        cursor: pointer;
    }
    .jtb-range-wrapper input[type="range"]::-webkit-slider-thumb {
        appearance: none;
        width: 18px;
        height: 18px;
        background: var(--accent);
        border-radius: 50%;
        cursor: pointer;
    }
    .jtb-range-value {
        min-width: 60px;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }

    /* SELECT FIELD */
    .jtb-field select {
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        cursor: pointer;
    }
    .jtb-field select:focus {
        outline: none;
        border-color: var(--accent);
    }

    /* TEXT FIELD */
    .jtb-field input[type="text"] {
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }
    .jtb-field input[type="text"]:focus {
        outline: none;
        border-color: var(--accent);
    }

    /* TOGGLE FIELD */
    .jtb-toggle-label {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }
    .jtb-toggle-label input {
        display: none;
    }
    .jtb-toggle-switch {
        width: 44px;
        height: 24px;
        background: var(--bg-tertiary);
        border: 1px solid var(--border);
        border-radius: 12px;
        position: relative;
        transition: all 0.2s;
    }
    .jtb-toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 18px;
        height: 18px;
        background: var(--text-muted);
        border-radius: 50%;
        transition: all 0.2s;
    }
    .jtb-toggle-label input:checked + .jtb-toggle-switch {
        background: var(--accent);
        border-color: var(--accent);
    }
    .jtb-toggle-label input:checked + .jtb-toggle-switch::after {
        left: 22px;
        background: #fff;
    }
    .jtb-toggle-text {
        font-size: 14px;
        color: var(--text-primary);
    }

    /* BUTTONS */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 500;
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.15s;
        border: none;
        text-decoration: none;
        font-family: inherit;
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
    .btn-ghost {
        background: transparent;
        color: var(--text-muted);
    }
    .btn-ghost:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }
    .btn-block {
        width: 100%;
        justify-content: center;
    }

    /* BUTTON PREVIEW */
    .button-preview {
        padding: 30px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        margin-bottom: 24px;
        text-align: center;
    }
    .preview-button {
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    /* MODAL */
    .jtb-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .jtb-modal-overlay.open {
        display: flex;
    }
    .jtb-modal {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 480px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    }
    .jtb-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
    }
    .jtb-modal-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .jtb-modal-close {
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
    }
    .jtb-modal-close:hover {
        background: var(--bg-tertiary);
        color: var(--text-primary);
    }
    .jtb-modal-body {
        padding: 24px;
    }
    .jtb-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        background: var(--bg-secondary);
    }

    /* PREVIEW PANEL */
    .jtb-preview-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 50%;
        height: 100vh;
        background: var(--bg-primary);
        border-left: 1px solid var(--border);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }
    .jtb-preview-panel.open {
        transform: translateX(0);
    }
    .jtb-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: var(--bg-tertiary);
        border-bottom: 1px solid var(--border);
    }
    .jtb-preview-header h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .jtb-preview-frame {
        flex: 1;
        width: 100%;
        border: none;
        background: #fff;
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
    @media (max-width: 900px) {
        .main-layout {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
            top: 0;
            border-right: none;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-nav {
            display: flex;
            flex-wrap: wrap;
            padding: 0 12px;
        }
        .jtb-settings-nav-item a {
            padding: 8px 12px;
        }
        .settings-grid {
            grid-template-columns: 1fr;
        }
        .jtb-preview-panel {
            width: 100%;
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
                <a href="/admin/jtb/templates" class="nav-link">Theme Builder</a>
                <a href="/admin/jtb/library" class="nav-link">Library</a>
                <a href="/admin/jtb/theme-settings" class="nav-link active">Theme Settings</a>
                <a href="/admin/jtb/global-modules" class="nav-link">Global Modules</a>
            </nav>

            <div class="topbar-right">
                <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">
                    <span id="themeIcon">🌙</span>
                </button>
            </div>
        </div>
    </header>

    <div class="main-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-title">Settings Groups</div>
            <ul class="sidebar-nav">
                <?php foreach ($groupLabels as $groupKey => $groupLabel): ?>
                <li class="jtb-settings-nav-item<?= $groupKey === 'colors' ? ' active' : '' ?>">
                    <a href="#<?= $esc($groupKey) ?>" data-group="<?= $esc($groupKey) ?>">
                        <?= getGroupIcon($groupKey) ?>
                        <span><?= $esc($groupLabel) ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="sidebar-actions">
                <button type="button" class="btn btn-secondary btn-sm btn-block" id="exportBtn">Export Settings</button>
                <button type="button" class="btn btn-secondary btn-sm btn-block" id="importBtn">Import Settings</button>
                <button type="button" class="btn btn-danger btn-sm btn-block" id="resetAllBtn">Reset All</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Theme Settings</h1>
                <div class="page-header-actions">
                    <button type="button" class="btn btn-secondary" id="previewBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Preview
                    </button>
                    <button type="button" class="btn btn-primary" id="saveBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Settings
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="message message-<?= $esc($message['type']) ?>">
                <?= $esc($message['text']) ?>
            </div>
            <?php endif; ?>

            <form id="themeSettingsForm" method="post">
                <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">

                <!-- Colors Section -->
                <section class="jtb-settings-section active" id="section-colors" data-group="colors">
                    <div class="section-header">
                        <h2>🎨 Colors</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="colors">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderColorField('primary_color', 'Primary Color', $settings['colors']['primary_color'] ?? '#7c3aed') ?>
                            <?= renderColorField('secondary_color', 'Secondary Color', $settings['colors']['secondary_color'] ?? '#1e1b4b') ?>
                            <?= renderColorField('accent_color', 'Accent Color', $settings['colors']['accent_color'] ?? '#10b981') ?>
                            <?= renderColorField('text_color', 'Text Color', $settings['colors']['text_color'] ?? '#1f2937') ?>
                            <?= renderColorField('text_light_color', 'Light Text', $settings['colors']['text_light_color'] ?? '#6b7280') ?>
                            <?= renderColorField('heading_color', 'Heading Color', $settings['colors']['heading_color'] ?? '#111827') ?>
                            <?= renderColorField('link_color', 'Link Color', $settings['colors']['link_color'] ?? '#7c3aed') ?>
                            <?= renderColorField('link_hover_color', 'Link Hover', $settings['colors']['link_hover_color'] ?? '#5b21b6') ?>
                            <?= renderColorField('background_color', 'Background', $settings['colors']['background_color'] ?? '#ffffff') ?>
                            <?= renderColorField('surface_color', 'Surface', $settings['colors']['surface_color'] ?? '#f9fafb') ?>
                            <?= renderColorField('border_color', 'Border', $settings['colors']['border_color'] ?? '#e5e7eb') ?>
                            <?= renderColorField('success_color', 'Success', $settings['colors']['success_color'] ?? '#10b981') ?>
                            <?= renderColorField('warning_color', 'Warning', $settings['colors']['warning_color'] ?? '#f59e0b') ?>
                            <?= renderColorField('error_color', 'Error', $settings['colors']['error_color'] ?? '#ef4444') ?>
                            <?= renderColorField('info_color', 'Info', $settings['colors']['info_color'] ?? '#3b82f6') ?>
                        </div>
                    </div>
                </section>

                <!-- Typography Section -->
                <section class="jtb-settings-section" id="section-typography" data-group="typography">
                    <div class="section-header">
                        <h2>🔤 Typography</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="typography">Reset</button>
                    </div>
                    <div class="section-body">
                        <h3 class="section-subheading">Body Text</h3>
                        <div class="settings-grid">
                            <?= renderSelectField('body_font', 'Body Font', $settings['typography']['body_font'] ?? 'Inter', $fontOptions, 'typography') ?>
                            <?= renderRangeField('body_size', 'Body Size', $settings['typography']['body_size'] ?? 16, 12, 24, 'px', 'typography') ?>
                            <?= renderSelectField('body_weight', 'Body Weight', $settings['typography']['body_weight'] ?? '400', getWeightOptions(), 'typography') ?>
                            <?= renderRangeField('body_line_height', 'Line Height', $settings['typography']['body_line_height'] ?? 1.6, 1, 2.5, '', 'typography', 0.1) ?>
                        </div>

                        <h3 class="section-subheading">Headings</h3>
                        <div class="settings-grid">
                            <?= renderSelectField('heading_font', 'Heading Font', $settings['typography']['heading_font'] ?? 'Inter', $fontOptions, 'typography') ?>
                            <?= renderSelectField('heading_weight', 'Heading Weight', $settings['typography']['heading_weight'] ?? '700', getWeightOptions(), 'typography') ?>
                            <?= renderRangeField('heading_line_height', 'Line Height', $settings['typography']['heading_line_height'] ?? 1.2, 1, 2, '', 'typography', 0.1) ?>
                            <?= renderRangeField('heading_letter_spacing', 'Letter Spacing', $settings['typography']['heading_letter_spacing'] ?? -0.02, -0.1, 0.2, 'em', 'typography', 0.01) ?>
                        </div>

                        <h3 class="section-subheading">Heading Sizes</h3>
                        <div class="settings-grid">
                            <?= renderRangeField('h1_size', 'H1 Size', $settings['typography']['h1_size'] ?? 48, 24, 96, 'px', 'typography') ?>
                            <?= renderRangeField('h2_size', 'H2 Size', $settings['typography']['h2_size'] ?? 36, 20, 72, 'px', 'typography') ?>
                            <?= renderRangeField('h3_size', 'H3 Size', $settings['typography']['h3_size'] ?? 28, 18, 56, 'px', 'typography') ?>
                            <?= renderRangeField('h4_size', 'H4 Size', $settings['typography']['h4_size'] ?? 24, 16, 48, 'px', 'typography') ?>
                            <?= renderRangeField('h5_size', 'H5 Size', $settings['typography']['h5_size'] ?? 20, 14, 36, 'px', 'typography') ?>
                            <?= renderRangeField('h6_size', 'H6 Size', $settings['typography']['h6_size'] ?? 18, 12, 28, 'px', 'typography') ?>
                        </div>
                    </div>
                </section>

                <!-- Layout Section -->
                <section class="jtb-settings-section" id="section-layout" data-group="layout">
                    <div class="section-header">
                        <h2>📐 Layout</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="layout">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderRangeField('content_width', 'Content Width', $settings['layout']['content_width'] ?? 1200, 800, 1920, 'px', 'layout') ?>
                            <?= renderRangeField('gutter_width', 'Gutter Width', $settings['layout']['gutter_width'] ?? 30, 10, 60, 'px', 'layout') ?>
                            <?= renderRangeField('section_padding_top', 'Section Padding Top', $settings['layout']['section_padding_top'] ?? 80, 0, 200, 'px', 'layout') ?>
                            <?= renderRangeField('section_padding_bottom', 'Section Padding Bottom', $settings['layout']['section_padding_bottom'] ?? 80, 0, 200, 'px', 'layout') ?>
                            <?= renderRangeField('row_gap', 'Row Gap', $settings['layout']['row_gap'] ?? 30, 0, 100, 'px', 'layout') ?>
                            <?= renderRangeField('column_gap', 'Column Gap', $settings['layout']['column_gap'] ?? 30, 0, 100, 'px', 'layout') ?>
                        </div>
                    </div>
                </section>

                <!-- Buttons Section -->
                <section class="jtb-settings-section" id="section-buttons" data-group="buttons">
                    <div class="section-header">
                        <h2>🔘 Buttons</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="buttons">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="button-preview">
                            <button type="button" class="preview-button" id="buttonPreview">Preview Button</button>
                        </div>

                        <div class="settings-grid">
                            <?= renderColorField('button_bg_color', 'Background', $settings['buttons']['button_bg_color'] ?? '#7c3aed', 'buttons') ?>
                            <?= renderColorField('button_text_color', 'Text Color', $settings['buttons']['button_text_color'] ?? '#ffffff', 'buttons') ?>
                            <?= renderColorField('button_border_color', 'Border Color', $settings['buttons']['button_border_color'] ?? '#7c3aed', 'buttons') ?>
                            <?= renderRangeField('button_border_width', 'Border Width', $settings['buttons']['button_border_width'] ?? 0, 0, 5, 'px', 'buttons') ?>
                            <?= renderRangeField('button_border_radius', 'Border Radius', $settings['buttons']['button_border_radius'] ?? 8, 0, 50, 'px', 'buttons') ?>
                            <?= renderRangeField('button_padding_tb', 'Padding TB', $settings['buttons']['button_padding_tb'] ?? 12, 4, 30, 'px', 'buttons') ?>
                            <?= renderRangeField('button_padding_lr', 'Padding LR', $settings['buttons']['button_padding_lr'] ?? 24, 8, 60, 'px', 'buttons') ?>
                            <?= renderRangeField('button_font_size', 'Font Size', $settings['buttons']['button_font_size'] ?? 16, 12, 24, 'px', 'buttons') ?>
                            <?= renderSelectField('button_font_weight', 'Font Weight', $settings['buttons']['button_font_weight'] ?? '600', getWeightOptions(), 'buttons') ?>
                            <?= renderSelectField('button_text_transform', 'Transform', $settings['buttons']['button_text_transform'] ?? 'none', getTransformOptions(), 'buttons') ?>
                        </div>

                        <h3 class="section-subheading">Hover State</h3>
                        <div class="settings-grid">
                            <?= renderColorField('button_hover_bg', 'Hover Background', $settings['buttons']['button_hover_bg'] ?? '#5b21b6', 'buttons') ?>
                            <?= renderColorField('button_hover_text', 'Hover Text', $settings['buttons']['button_hover_text'] ?? '#ffffff', 'buttons') ?>
                            <?= renderColorField('button_hover_border', 'Hover Border', $settings['buttons']['button_hover_border'] ?? '#5b21b6', 'buttons') ?>
                            <?= renderRangeField('button_transition', 'Transition', $settings['buttons']['button_transition'] ?? 0.2, 0, 1, 's', 'buttons', 0.1) ?>
                        </div>
                    </div>
                </section>

                <!-- Forms Section -->
                <section class="jtb-settings-section" id="section-forms" data-group="forms">
                    <div class="section-header">
                        <h2>📝 Forms</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="forms">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderColorField('input_bg_color', 'Input Background', $settings['forms']['input_bg_color'] ?? '#ffffff', 'forms') ?>
                            <?= renderColorField('input_text_color', 'Input Text', $settings['forms']['input_text_color'] ?? '#1f2937', 'forms') ?>
                            <?= renderColorField('input_border_color', 'Input Border', $settings['forms']['input_border_color'] ?? '#d1d5db', 'forms') ?>
                            <?= renderRangeField('input_border_width', 'Border Width', $settings['forms']['input_border_width'] ?? 1, 0, 5, 'px', 'forms') ?>
                            <?= renderRangeField('input_border_radius', 'Border Radius', $settings['forms']['input_border_radius'] ?? 6, 0, 20, 'px', 'forms') ?>
                            <?= renderRangeField('input_padding_tb', 'Padding TB', $settings['forms']['input_padding_tb'] ?? 10, 4, 20, 'px', 'forms') ?>
                            <?= renderRangeField('input_padding_lr', 'Padding LR', $settings['forms']['input_padding_lr'] ?? 14, 8, 30, 'px', 'forms') ?>
                            <?= renderRangeField('input_font_size', 'Font Size', $settings['forms']['input_font_size'] ?? 16, 12, 20, 'px', 'forms') ?>
                            <?= renderColorField('input_focus_border_color', 'Focus Border', $settings['forms']['input_focus_border_color'] ?? '#7c3aed', 'forms') ?>
                            <?= renderColorField('placeholder_color', 'Placeholder', $settings['forms']['placeholder_color'] ?? '#9ca3af', 'forms') ?>
                            <?= renderColorField('label_color', 'Label Color', $settings['forms']['label_color'] ?? '#374151', 'forms') ?>
                            <?= renderRangeField('label_font_size', 'Label Size', $settings['forms']['label_font_size'] ?? 14, 12, 18, 'px', 'forms') ?>
                        </div>
                    </div>
                </section>

                <!-- Header Section -->
                <section class="jtb-settings-section" id="section-header" data-group="header">
                    <div class="section-header">
                        <h2>🔝 Header</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="header">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderColorField('header_bg_color', 'Background', $settings['header']['header_bg_color'] ?? '#ffffff', 'header') ?>
                            <?= renderColorField('header_text_color', 'Text Color', $settings['header']['header_text_color'] ?? '#1f2937', 'header') ?>
                            <?= renderRangeField('header_height', 'Height', $settings['header']['header_height'] ?? 80, 50, 150, 'px', 'header') ?>
                            <?= renderRangeField('header_padding_lr', 'Padding LR', $settings['header']['header_padding_lr'] ?? 30, 0, 60, 'px', 'header') ?>
                            <?= renderRangeField('logo_height', 'Logo Height', $settings['header']['logo_height'] ?? 50, 20, 100, 'px', 'header') ?>
                        </div>

                        <h3 class="section-subheading">Sticky Header</h3>
                        <div class="settings-grid">
                            <?= renderToggleField('header_sticky', 'Enable Sticky', $settings['header']['header_sticky'] ?? false, 'header') ?>
                            <?= renderColorField('header_sticky_bg', 'Sticky Background', $settings['header']['header_sticky_bg'] ?? '#ffffff', 'header') ?>
                            <?= renderRangeField('logo_height_sticky', 'Sticky Logo Height', $settings['header']['logo_height_sticky'] ?? 40, 20, 80, 'px', 'header') ?>
                        </div>

                        <h3 class="section-subheading">Transparent Header</h3>
                        <div class="settings-grid">
                            <?= renderToggleField('header_transparent', 'Enable Transparent', $settings['header']['header_transparent'] ?? false, 'header') ?>
                            <?= renderColorField('header_transparent_text', 'Transparent Text', $settings['header']['header_transparent_text'] ?? '#ffffff', 'header') ?>
                        </div>
                    </div>
                </section>

                <!-- Menu Section -->
                <section class="jtb-settings-section" id="section-menu" data-group="menu">
                    <div class="section-header">
                        <h2>☰ Menu</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="menu">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderSelectField('menu_font_family', 'Font Family', $settings['menu']['menu_font_family'] ?? 'inherit', $fontOptions, 'menu') ?>
                            <?= renderRangeField('menu_font_size', 'Font Size', $settings['menu']['menu_font_size'] ?? 16, 12, 20, 'px', 'menu') ?>
                            <?= renderSelectField('menu_font_weight', 'Font Weight', $settings['menu']['menu_font_weight'] ?? '500', getWeightOptions(), 'menu') ?>
                            <?= renderSelectField('menu_text_transform', 'Transform', $settings['menu']['menu_text_transform'] ?? 'none', getTransformOptions(), 'menu') ?>
                            <?= renderColorField('menu_link_color', 'Link Color', $settings['menu']['menu_link_color'] ?? '#1f2937', 'menu') ?>
                            <?= renderColorField('menu_link_hover_color', 'Link Hover', $settings['menu']['menu_link_hover_color'] ?? '#7c3aed', 'menu') ?>
                            <?= renderColorField('menu_link_active_color', 'Link Active', $settings['menu']['menu_link_active_color'] ?? '#7c3aed', 'menu') ?>
                            <?= renderRangeField('menu_link_padding_tb', 'Link Padding TB', $settings['menu']['menu_link_padding_tb'] ?? 10, 4, 20, 'px', 'menu') ?>
                            <?= renderRangeField('menu_link_padding_lr', 'Link Padding LR', $settings['menu']['menu_link_padding_lr'] ?? 16, 8, 30, 'px', 'menu') ?>
                        </div>

                        <h3 class="section-subheading">Dropdown</h3>
                        <div class="settings-grid">
                            <?= renderColorField('dropdown_bg_color', 'Background', $settings['menu']['dropdown_bg_color'] ?? '#ffffff', 'menu') ?>
                            <?= renderColorField('dropdown_text_color', 'Text Color', $settings['menu']['dropdown_text_color'] ?? '#1f2937', 'menu') ?>
                            <?= renderColorField('dropdown_hover_bg', 'Hover Background', $settings['menu']['dropdown_hover_bg'] ?? '#f3f4f6', 'menu') ?>
                            <?= renderRangeField('dropdown_border_radius', 'Border Radius', $settings['menu']['dropdown_border_radius'] ?? 8, 0, 20, 'px', 'menu') ?>
                        </div>

                        <h3 class="section-subheading">Mobile</h3>
                        <div class="settings-grid">
                            <?= renderRangeField('mobile_breakpoint', 'Breakpoint', $settings['menu']['mobile_breakpoint'] ?? 980, 768, 1200, 'px', 'menu') ?>
                            <?= renderColorField('mobile_menu_bg', 'Mobile Background', $settings['menu']['mobile_menu_bg'] ?? '#ffffff', 'menu') ?>
                            <?= renderColorField('mobile_menu_text', 'Mobile Text', $settings['menu']['mobile_menu_text'] ?? '#1f2937', 'menu') ?>
                            <?= renderColorField('hamburger_color', 'Hamburger Color', $settings['menu']['hamburger_color'] ?? '#1f2937', 'menu') ?>
                        </div>
                    </div>
                </section>

                <!-- Footer Section -->
                <section class="jtb-settings-section" id="section-footer" data-group="footer">
                    <div class="section-header">
                        <h2>🔚 Footer</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="footer">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderColorField('footer_bg_color', 'Background', $settings['footer']['footer_bg_color'] ?? '#1f2937', 'footer') ?>
                            <?= renderColorField('footer_text_color', 'Text Color', $settings['footer']['footer_text_color'] ?? '#d1d5db', 'footer') ?>
                            <?= renderColorField('footer_heading_color', 'Heading Color', $settings['footer']['footer_heading_color'] ?? '#ffffff', 'footer') ?>
                            <?= renderColorField('footer_link_color', 'Link Color', $settings['footer']['footer_link_color'] ?? '#d1d5db', 'footer') ?>
                            <?= renderColorField('footer_link_hover_color', 'Link Hover', $settings['footer']['footer_link_hover_color'] ?? '#ffffff', 'footer') ?>
                            <?= renderRangeField('footer_padding_top', 'Padding Top', $settings['footer']['footer_padding_top'] ?? 60, 0, 120, 'px', 'footer') ?>
                            <?= renderRangeField('footer_padding_bottom', 'Padding Bottom', $settings['footer']['footer_padding_bottom'] ?? 60, 0, 120, 'px', 'footer') ?>
                            <?= renderSelectField('footer_columns', 'Columns', $settings['footer']['footer_columns'] ?? '4', ['2' => '2', '3' => '3', '4' => '4', '5' => '5'], 'footer') ?>
                        </div>

                        <h3 class="section-subheading">Copyright</h3>
                        <div class="settings-grid">
                            <?= renderColorField('copyright_bg_color', 'Background', $settings['footer']['copyright_bg_color'] ?? '#111827', 'footer') ?>
                            <?= renderColorField('copyright_text_color', 'Text Color', $settings['footer']['copyright_text_color'] ?? '#9ca3af', 'footer') ?>
                            <?= renderRangeField('copyright_padding_tb', 'Padding', $settings['footer']['copyright_padding_tb'] ?? 20, 10, 40, 'px', 'footer') ?>
                            <?= renderTextField('copyright_text', 'Copyright Text', $settings['footer']['copyright_text'] ?? '© {year} {site_name}. All rights reserved.', 'footer') ?>
                        </div>
                    </div>
                </section>

                <!-- Blog Section -->
                <section class="jtb-settings-section" id="section-blog" data-group="blog">
                    <div class="section-header">
                        <h2>📰 Blog</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="blog">Reset</button>
                    </div>
                    <div class="section-body">
                        <div class="settings-grid">
                            <?= renderSelectField('blog_layout', 'Layout', $settings['blog']['blog_layout'] ?? 'grid', ['grid' => 'Grid', 'list' => 'List', 'masonry' => 'Masonry'], 'blog') ?>
                            <?= renderSelectField('blog_columns', 'Columns', $settings['blog']['blog_columns'] ?? '3', ['2' => '2', '3' => '3', '4' => '4'], 'blog') ?>
                            <?= renderRangeField('blog_gap', 'Gap', $settings['blog']['blog_gap'] ?? 30, 10, 60, 'px', 'blog') ?>
                            <?= renderColorField('post_card_bg', 'Card Background', $settings['blog']['post_card_bg'] ?? '#ffffff', 'blog') ?>
                            <?= renderRangeField('post_card_border_radius', 'Card Radius', $settings['blog']['post_card_border_radius'] ?? 12, 0, 30, 'px', 'blog') ?>
                        </div>

                        <h3 class="section-subheading">Post Card Options</h3>
                        <div class="settings-grid">
                            <?= renderToggleField('show_featured_image', 'Show Featured Image', $settings['blog']['show_featured_image'] ?? true, 'blog') ?>
                            <?= renderToggleField('show_date', 'Show Date', $settings['blog']['show_date'] ?? true, 'blog') ?>
                            <?= renderToggleField('show_author', 'Show Author', $settings['blog']['show_author'] ?? true, 'blog') ?>
                            <?= renderToggleField('show_categories', 'Show Categories', $settings['blog']['show_categories'] ?? true, 'blog') ?>
                            <?= renderToggleField('show_excerpt', 'Show Excerpt', $settings['blog']['show_excerpt'] ?? true, 'blog') ?>
                            <?= renderRangeField('excerpt_length', 'Excerpt Length', $settings['blog']['excerpt_length'] ?? 150, 50, 300, '', 'blog') ?>
                            <?= renderToggleField('show_read_more', 'Show Read More', $settings['blog']['show_read_more'] ?? true, 'blog') ?>
                            <?= renderTextField('read_more_text', 'Read More Text', $settings['blog']['read_more_text'] ?? 'Read More', 'blog') ?>
                        </div>
                    </div>
                </section>

                <!-- Responsive Section -->
                <section class="jtb-settings-section" id="section-responsive" data-group="responsive">
                    <div class="section-header">
                        <h2>📱 Responsive</h2>
                        <button type="button" class="btn btn-sm btn-ghost reset-group-btn" data-group="responsive">Reset</button>
                    </div>
                    <div class="section-body">
                        <h3 class="section-subheading">Breakpoints</h3>
                        <div class="settings-grid">
                            <?= renderRangeField('tablet_breakpoint', 'Tablet Breakpoint', $settings['responsive']['tablet_breakpoint'] ?? 980, 768, 1200, 'px', 'responsive') ?>
                            <?= renderRangeField('phone_breakpoint', 'Phone Breakpoint', $settings['responsive']['phone_breakpoint'] ?? 767, 480, 800, 'px', 'responsive') ?>
                        </div>

                        <h3 class="section-subheading">Tablet Adjustments</h3>
                        <div class="settings-grid">
                            <?= renderRangeField('h1_size_tablet', 'H1 Size', $settings['responsive']['h1_size_tablet'] ?? 36, 24, 60, 'px', 'responsive') ?>
                            <?= renderRangeField('h2_size_tablet', 'H2 Size', $settings['responsive']['h2_size_tablet'] ?? 28, 20, 48, 'px', 'responsive') ?>
                            <?= renderRangeField('body_size_tablet', 'Body Size', $settings['responsive']['body_size_tablet'] ?? 15, 12, 20, 'px', 'responsive') ?>
                            <?= renderRangeField('section_padding_tablet', 'Section Padding', $settings['responsive']['section_padding_tablet'] ?? 60, 20, 100, 'px', 'responsive') ?>
                        </div>

                        <h3 class="section-subheading">Phone Adjustments</h3>
                        <div class="settings-grid">
                            <?= renderRangeField('h1_size_phone', 'H1 Size', $settings['responsive']['h1_size_phone'] ?? 28, 20, 48, 'px', 'responsive') ?>
                            <?= renderRangeField('h2_size_phone', 'H2 Size', $settings['responsive']['h2_size_phone'] ?? 24, 18, 36, 'px', 'responsive') ?>
                            <?= renderRangeField('body_size_phone', 'Body Size', $settings['responsive']['body_size_phone'] ?? 14, 12, 18, 'px', 'responsive') ?>
                            <?= renderRangeField('section_padding_phone', 'Section Padding', $settings['responsive']['section_padding_phone'] ?? 40, 20, 80, 'px', 'responsive') ?>
                        </div>
                    </div>
                </section>

            </form>
        </main>
    </div>

    <!-- Import Modal -->
    <div class="jtb-modal-overlay" id="importModal">
        <div class="jtb-modal">
            <div class="jtb-modal-header">
                <h3 class="jtb-modal-title">Import Settings</h3>
                <button type="button" class="jtb-modal-close">&times;</button>
            </div>
            <form action="/admin/jtb/theme-settings?action=import" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">
                <div class="jtb-modal-body">
                    <div class="jtb-field">
                        <label>Select JSON File</label>
                        <input type="file" name="import_file" accept=".json" required style="margin-top:8px;">
                    </div>
                    <p style="color:var(--text-muted);font-size:13px;margin-top:12px;">This will replace all current theme settings.</p>
                </div>
                <div class="jtb-modal-footer">
                    <button type="button" class="btn btn-secondary jtb-modal-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Panel -->
    <div class="jtb-preview-panel" id="previewPanel">
        <div class="jtb-preview-header">
            <h3>Live Preview</h3>
            <button type="button" class="btn btn-ghost btn-sm" id="closePreview">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Close
            </button>
        </div>
        <iframe id="previewFrame" class="jtb-preview-frame"></iframe>
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
        document.addEventListener('DOMContentLoaded', () => {
            const theme = document.documentElement.getAttribute('data-theme');
            document.getElementById('themeIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
        });

        window.JTB_CSRF_TOKEN = '<?= $esc($csrfToken) ?>';
        window.JTB_SETTINGS = <?= json_encode($settings) ?>;
        window.JTB_DEFAULTS = <?= json_encode($defaults) ?>;
    </script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/theme-settings.js"></script>
</body>
</html>
<?php

// Helper functions for rendering fields

function renderColorField(string $name, string $label, string $value, string $group = 'colors'): string
{
    $esc = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    return <<<HTML
<div class="jtb-field jtb-field-color">
    <label>{$esc($label)}</label>
    <div class="jtb-color-input-wrapper">
        <input type="color" name="{$esc($group)}[{$esc($name)}]" value="{$esc($value)}" data-group="{$esc($group)}" data-key="{$esc($name)}">
        <input type="text" class="jtb-color-text" value="{$esc($value)}" data-group="{$esc($group)}" data-key="{$esc($name)}">
    </div>
</div>
HTML;
}

function renderRangeField(string $name, string $label, $value, $min, $max, string $unit, string $group, float $step = 1): string
{
    $esc = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    return <<<HTML
<div class="jtb-field jtb-field-range">
    <label>{$esc($label)}</label>
    <div class="jtb-range-wrapper">
        <input type="range" name="{$esc($group)}[{$esc($name)}]" value="{$esc($value)}" min="{$min}" max="{$max}" step="{$step}" data-group="{$esc($group)}" data-key="{$esc($name)}">
        <span class="jtb-range-value">{$esc($value)}{$esc($unit)}</span>
    </div>
</div>
HTML;
}

function renderSelectField(string $name, string $label, string $value, array $options, string $group): string
{
    $esc = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    $optionsHtml = '';
    foreach ($options as $optValue => $optLabel) {
        if (strpos($optValue, '_') === 0) {
            $optionsHtml .= "<option disabled>{$esc($optLabel)}</option>";
        } else {
            $selected = $value == $optValue ? ' selected' : '';
            $optionsHtml .= "<option value=\"{$esc($optValue)}\"{$selected}>{$esc($optLabel)}</option>";
        }
    }
    return <<<HTML
<div class="jtb-field jtb-field-select">
    <label>{$esc($label)}</label>
    <select name="{$esc($group)}[{$esc($name)}]" data-group="{$esc($group)}" data-key="{$esc($name)}">
        {$optionsHtml}
    </select>
</div>
HTML;
}

function renderToggleField(string $name, string $label, bool $value, string $group): string
{
    $esc = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    $checked = $value ? ' checked' : '';
    return <<<HTML
<div class="jtb-field jtb-field-toggle">
    <label class="jtb-toggle-label">
        <input type="checkbox" name="{$esc($group)}[{$esc($name)}]" value="1"{$checked} data-group="{$esc($group)}" data-key="{$esc($name)}">
        <span class="jtb-toggle-switch"></span>
        <span class="jtb-toggle-text">{$esc($label)}</span>
    </label>
</div>
HTML;
}

function renderTextField(string $name, string $label, string $value, string $group): string
{
    $esc = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    return <<<HTML
<div class="jtb-field jtb-field-text jtb-field-full">
    <label>{$esc($label)}</label>
    <input type="text" name="{$esc($group)}[{$esc($name)}]" value="{$esc($value)}" data-group="{$esc($group)}" data-key="{$esc($name)}">
</div>
HTML;
}

function getWeightOptions(): array
{
    return [
        '100' => 'Thin (100)',
        '200' => 'Extra Light (200)',
        '300' => 'Light (300)',
        '400' => 'Regular (400)',
        '500' => 'Medium (500)',
        '600' => 'Semi Bold (600)',
        '700' => 'Bold (700)',
        '800' => 'Extra Bold (800)',
        '900' => 'Black (900)'
    ];
}

function getTransformOptions(): array
{
    return [
        'none' => 'None',
        'uppercase' => 'UPPERCASE',
        'lowercase' => 'lowercase',
        'capitalize' => 'Capitalize'
    ];
}

function getGroupIcon(string $group): string
{
    $icons = [
        'colors' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="10.5" r="2.5"/><circle cx="8.5" cy="7.5" r="2.5"/><circle cx="6.5" cy="12.5" r="2.5"/><path d="M12 22c5.5 0 10-4.5 10-10S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10z"/></svg>',
        'typography' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
        'layout' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>',
        'buttons' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="8" width="18" height="8" rx="2"/></svg>',
        'forms' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        'header' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg>',
        'menu' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'footer' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="15" x2="21" y2="15"/></svg>',
        'blog' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
        'responsive' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg>'
    ];
    return $icons[$group] ?? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
}
