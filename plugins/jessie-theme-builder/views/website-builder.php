<?php
/**
 * Website Builder - Unified Theme Builder
 * Build entire website in one interface: header + body + footer
 *
 * Based on builder.php UI - proven, working design
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */
namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Data from controller
$siteName = $website['name'] ?? 'My Website';

// Pexels API key
$pexelsApiKey = '';
try {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($result && !empty($result['value'])) {
        $pexelsApiKey = $result['value'];
    }
} catch (\Exception $e) {}

$esc = function($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
};

// Default header/footer
$defaultHeader = null;
$defaultFooter = null;
foreach ($headers as $h) {
    if (!empty($h['is_default']) || !empty($h['is_active'])) {
        $defaultHeader = $h;
        break;
    }
}
if (!$defaultHeader && !empty($headers)) {
    $defaultHeader = $headers[0];
}
foreach ($footers as $f) {
    if (!empty($f['is_default']) || !empty($f['is_active'])) {
        $defaultFooter = $f;
        break;
    }
}
if (!$defaultFooter && !empty($footers)) {
    $defaultFooter = $footers[0];
}
?>
<!DOCTYPE html>
<html lang="en" class="jtb-builder-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Website Builder - <?= $esc($siteName) ?></title>
    <!-- Build: <?= date('Y-m-d H:i:s') ?> -->

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Builder CSS -->
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/builder.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/frontend.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/media-gallery.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/jtb-base-modules.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/ai-panel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $esc($pluginUrl) ?>/assets/css/ai-multiagent.css?v=<?= time() ?>">

    <style>
        /* Website Builder specific styles */
        .jtb-wb-sidebar {
            width: 280px;
            background: var(--jtb-bg-secondary, #1e1e32);
            border-right: 1px solid var(--jtb-border, #2d2d44);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .jtb-wb-sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--jtb-border, #2d2d44);
            font-weight: 600;
            color: var(--jtb-text, #e0e0e0);
        }

        .jtb-wb-sidebar-content {
            flex: 1;
            overflow-y: auto;
        }

        .jtb-wb-section {
            border-bottom: 1px solid var(--jtb-border, #2d2d44);
        }

        .jtb-wb-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }

        .jtb-wb-section-header:hover {
            background: rgba(255,255,255,0.03);
        }

        .jtb-wb-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--jtb-text-muted, #888);
        }

        .jtb-wb-section-title .count {
            font-weight: 400;
            opacity: 0.7;
        }

        .jtb-wb-section-toggle {
            color: var(--jtb-text-muted, #888);
            transition: transform 0.2s;
        }

        .jtb-wb-section.collapsed .jtb-wb-section-toggle {
            transform: rotate(-90deg);
        }

        .jtb-wb-section.collapsed .jtb-wb-section-list {
            display: none;
        }

        .jtb-wb-section-list {
            padding: 4px 8px 12px;
        }

        .jtb-wb-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin: 2px 0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
            color: var(--jtb-text, #e0e0e0);
            font-size: 13px;
        }

        .jtb-wb-item:hover {
            background: rgba(255,255,255,0.05);
        }

        .jtb-wb-item.active {
            background: var(--jtb-accent, #7c3aed);
            color: #fff;
        }

        .jtb-wb-item-icon {
            width: 18px;
            height: 18px;
            opacity: 0.7;
        }

        .jtb-wb-item.active .jtb-wb-item-icon {
            opacity: 1;
        }

        .jtb-wb-item-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .jtb-wb-item-slug {
            font-size: 11px;
            color: var(--jtb-text-muted, #888);
            opacity: 0.7;
        }

        .jtb-wb-item.active .jtb-wb-item-slug {
            color: rgba(255,255,255,0.7);
        }

        .jtb-wb-badge {
            font-size: 9px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            background: var(--jtb-accent, #7c3aed);
            color: #fff;
            text-transform: uppercase;
        }

        .jtb-wb-add-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 6px;
            cursor: pointer;
            color: var(--jtb-text-muted, #888);
            font-size: 13px;
            transition: all 0.15s;
        }

        .jtb-wb-add-item:hover {
            background: rgba(255,255,255,0.05);
            color: var(--jtb-accent, #7c3aed);
        }

        /* Region indicators */
        .jtb-wb-region-indicator {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 10;
            padding: 4px 10px;
            background: rgba(0,0,0,0.7);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .jtb-canvas-inner:hover .jtb-wb-region-indicator {
            opacity: 1;
        }

        .jtb-wb-region-indicator.active {
            background: var(--jtb-accent, #7c3aed);
            opacity: 1;
        }

        /* AI Generate Button */
        .jtb-wb-ai-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 16px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #3b82f6 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .jtb-wb-ai-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .jtb-wb-ai-btn svg {
            width: 18px;
            height: 18px;
        }

        /* Context selector in header */
        .jtb-context-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: var(--jtb-bg-secondary, #1e1e32);
            border: 1px solid var(--jtb-border, #2d2d44);
            border-radius: 6px;
            color: var(--jtb-text, #e0e0e0);
            font-size: 13px;
        }

        .jtb-context-selector select {
            background: transparent;
            border: none;
            color: inherit;
            font-size: inherit;
            cursor: pointer;
            outline: none;
        }

        .jtb-context-selector option {
            background: var(--jtb-bg-secondary, #1e1e32);
        }
    </style>
</head>
<body class="jtb-builder-page">

    <div class="jtb-builder">

        <!-- Header -->
        <header class="jtb-header">
            <div class="jtb-header-left">
                <a href="/admin" class="jtb-logo" title="Back to Admin">
                    <img src="/public/assets/images/jessie-logo.svg" alt="Jessie" width="32" height="32">
                </a>
                <a href="/admin/jtb/templates" class="jtb-back-btn" title="Back to Templates">← Back</a>
                <span class="jtb-header-title">Website Builder: <strong><?= $esc($siteName) ?></strong></span>
            </div>

            <div class="jtb-header-center">
                <div class="jtb-device-switcher">
                    <button class="jtb-device-btn active" data-device="desktop" title="Desktop">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </button>
                    <button class="jtb-device-btn" data-device="tablet" title="Tablet">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </button>
                    <button class="jtb-device-btn" data-device="phone" title="Phone">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                            <line x1="12" y1="18" x2="12.01" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="jtb-header-right">
                <button class="jtb-btn" data-action="undo" title="Undo">↩️ Undo</button>
                <button class="jtb-btn" data-action="redo" title="Redo">↪️ Redo</button>
                <button class="jtb-btn" onclick="WB.openPreview()" title="Preview">👁️ Preview</button>
                <button class="jtb-btn jtb-btn-primary" data-action="save" title="Save">💾 Save</button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="jtb-main">

            <!-- Left Sidebar - Site Map -->
            <aside class="jtb-wb-sidebar">
                <div class="jtb-wb-sidebar-header">
                    📁 Site Structure
                </div>

                <div class="jtb-wb-sidebar-content">
                    <!-- Headers Section -->
                    <div class="jtb-wb-section" data-section="headers">
                        <div class="jtb-wb-section-header">
                            <span class="jtb-wb-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="6" rx="1"></rect></svg>
                                Headers
                                <span class="count"><?= count($headers) ?></span>
                            </span>
                            <svg class="jtb-wb-section-toggle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="jtb-wb-section-list">
                            <?php foreach ($headers as $h): ?>
                            <div class="jtb-wb-item<?= ($defaultHeader && $defaultHeader['id'] == $h['id']) ? ' active' : '' ?>"
                                 data-type="header"
                                 data-id="<?= $h['id'] ?>">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="6" rx="1"></rect></svg>
                                <span class="jtb-wb-item-name"><?= $esc($h['name']) ?></span>
                                <?php if (!empty($h['is_default']) || !empty($h['is_active'])): ?>
                                <span class="jtb-wb-badge">Default</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <div class="jtb-wb-add-item" data-action="add-header">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Header
                            </div>
                        </div>
                    </div>

                    <!-- Page Templates Section (AI Generated) -->
                    <div class="jtb-wb-section" data-section="body-templates">
                        <div class="jtb-wb-section-header">
                            <span class="jtb-wb-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                                Page Templates
                                <span class="count"><?= count($bodyTemplates) ?></span>
                            </span>
                            <svg class="jtb-wb-section-toggle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="jtb-wb-section-list">
                            <?php foreach ($bodyTemplates as $t): ?>
                            <div class="jtb-wb-item" data-type="body" data-id="<?= $t['id'] ?>">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line></svg>
                                <span class="jtb-wb-item-name"><?= $esc($t['name']) ?></span>
                            </div>
                            <?php endforeach; ?>
                            <div class="jtb-wb-add-item" data-action="add-body">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Page Template
                            </div>
                        </div>
                    </div>

                    <!-- Pages Section -->
                    <div class="jtb-wb-section collapsed" data-section="pages">
                        <div class="jtb-wb-section-header">
                            <span class="jtb-wb-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                Pages
                                <span class="count"><?= count($pages) ?></span>
                            </span>
                            <svg class="jtb-wb-section-toggle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="jtb-wb-section-list">
                            <?php foreach ($pages as $p): ?>
                            <div class="jtb-wb-item" data-type="page" data-id="<?= $p['id'] ?>">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                                <span class="jtb-wb-item-name"><?= $esc($p['title']) ?></span>
                                <span class="jtb-wb-item-slug">/<?= $esc($p['slug']) ?></span>
                            </div>
                            <?php endforeach; ?>
                            <div class="jtb-wb-add-item" data-action="add-page">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Page
                            </div>
                        </div>
                    </div>

                    <!-- Footers Section -->

                    <div class="jtb-wb-section" data-section="footers">
                        <div class="jtb-wb-section-header">
                            <span class="jtb-wb-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="15" width="18" height="6" rx="1"></rect></svg>
                                Footers
                                <span class="count"><?= count($footers) ?></span>
                            </span>
                            <svg class="jtb-wb-section-toggle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="jtb-wb-section-list">
                            <?php foreach ($footers as $f): ?>
                            <div class="jtb-wb-item<?= ($defaultFooter && $defaultFooter['id'] == $f['id']) ? ' active' : '' ?>"
                                 data-type="footer"
                                 data-id="<?= $f['id'] ?>">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="15" width="18" height="6" rx="1"></rect></svg>
                                <span class="jtb-wb-item-name"><?= $esc($f['name']) ?></span>
                                <?php if (!empty($f['is_default']) || !empty($f['is_active'])): ?>
                                <span class="jtb-wb-badge">Default</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <div class="jtb-wb-add-item" data-action="add-footer">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Add Footer
                            </div>
                        </div>
                    </div>

                    <!-- Theme Settings Section -->
                    <div class="jtb-wb-section collapsed" data-section="settings">
                        <div class="jtb-wb-section-header">
                            <span class="jtb-wb-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                Theme Settings
                            </span>
                            <svg class="jtb-wb-section-toggle" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="jtb-wb-section-list">
                            <div class="jtb-wb-item" data-type="settings" data-id="colors">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="2.5"></circle><circle cx="6.5" cy="17.5" r="2.5"></circle><circle cx="17.5" cy="17.5" r="2.5"></circle></svg>
                                <span class="jtb-wb-item-name">Colors</span>
                            </div>
                            <div class="jtb-wb-item" data-type="settings" data-id="typography">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
                                <span class="jtb-wb-item-name">Typography</span>
                            </div>
                            <div class="jtb-wb-item" data-type="settings" data-id="layout">
                                <svg class="jtb-wb-item-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                <span class="jtb-wb-item-name">Layout</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Generate Button -->
                <button class="jtb-wb-ai-btn" data-action="ai-generate">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                    Generate with AI
                </button>
            </aside>

            <!-- Canvas -->
            <div class="jtb-canvas jtb-preview-desktop">
                <div class="jtb-canvas-inner">
                    <div class="jtb-loading">
                        <div class="jtb-spinner"></div>
                        <p>Loading builder...</p>
                    </div>
                </div>
            </div>

            <!-- Settings Panel (Right Sidebar) -->
            <aside class="jtb-settings-panel">
                <div class="jtb-settings-empty">
                    <div class="jtb-empty-icon">⚙️</div>
                    <p>Select an item from the sidebar to edit</p>
                </div>
            </aside>

        </main>

    </div>

    <!-- Notifications -->
    <div class="jtb-notifications"></div>

    <!-- Media Gallery Modal -->
    <?php
    require_once dirname(__DIR__) . '/includes/jtb-media-gallery.php';
    jtb_render_media_gallery_modal($csrfToken, $pexelsApiKey);
    ?>


    <!-- JavaScript -->
    <script src="<?= $esc($pluginUrl) ?>/assets/js/feather-icons.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/builder.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/settings-panel.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/fields.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/media-gallery.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/ai-panel-render.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/ai-panel.js?v=<?= time() ?>"></script>
    <script src="<?= $esc($pluginUrl) ?>/assets/js/ai-multiagent.js?v=<?= time() ?>"></script>

    <script>
    <script>
    // AI Provider/Model management — loaded dynamically from API
    const WB_AI = {
        aiProvider: 'anthropic',
        aiModel: 'claude-sonnet-4-20250514',
        availableProviders: {},
        _loaded: false,

        async loadModels() {
            if (this._loaded) return;
            try {
                const resp = await fetch('/api/theme-studio/ai/models');
                const data = await resp.json();
                if (data.success && data.providers) {
                    this.availableProviders = {};
                    data.providers.forEach(p => {
                        const models = {};
                        (p.models || []).forEach(m => {
                            models[m.id] = m.name + (m.tier_badge ? ' (' + m.tier_badge + ')' : '');
                        });
                        this.availableProviders[p.id] = {
                            name: p.name,
                            default: p.default_model || Object.keys(models)[0] || '',
                            models: models
                        };
                    });
                    const firstProvider = data.providers[0];
                    if (firstProvider) {
                        this.aiProvider = firstProvider.id;
                        this.aiModel = firstProvider.default_model || firstProvider.models?.[0]?.id || '';
                    }
                    this._loaded = true;
                    console.log('[WB_AI] Models loaded dynamically:', Object.keys(this.availableProviders));
                }
            } catch (err) {
                console.error('[WB_AI] Failed to load models:', err);
                this.availableProviders = { 'anthropic': { name: 'Anthropic Claude', default: 'claude-sonnet-4-20250514', models: { 'claude-sonnet-4-20250514': 'Claude Sonnet 4' } } };
                this._loaded = true;
            }
        },

        handleProviderChange(provider) {
            this.aiProvider = provider;
            const providerConfig = this.availableProviders[provider];
            if (providerConfig) {
                this.aiModel = providerConfig.default;
            }
        }
    };
    WB_AI.loadModels();
    </script>
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Website Builder State (exposed on window for inline onclick handlers)
        const WB = window.WB = {
            currentType: 'header',
            currentId: <?= $defaultHeader ? $defaultHeader['id'] : 'null' ?>,
            headers: <?= json_encode($headers) ?>,
            footers: <?= json_encode($footers) ?>,
            pages: <?= json_encode($pages) ?>,
            bodyTemplates: <?= json_encode($bodyTemplates) ?>,
            csrfToken: '<?= $esc($csrfToken) ?>',
            generatedWebsite: null,

            openPreview() {
                const h = this.headers.find(x => x.is_default || x.is_active) || this.headers[0];
                const f = this.footers.find(x => x.is_default || x.is_active) || this.footers[0];
                let url = '/preview/website?';
                if (h) url += 'header=' + h.id + '&';
                if (f) url += 'footer=' + f.id + '&';
                if (this.currentType === 'page' && this.currentId) url += 'page=' + this.currentId;
                else if (this.currentType === 'body' && this.currentId) url += 'body=' + this.currentId;
                window.open(url, '_blank');
            },

            init() {
                this.bindEvents();
                // Load default header
                if (this.currentId) {
                    this.loadContent(this.currentType, this.currentId);
                }
            },

            bindEvents() {
                // Sidebar section toggles
                document.querySelectorAll('.jtb-wb-section-header').forEach(header => {
                    header.addEventListener('click', () => {
                        header.closest('.jtb-wb-section').classList.toggle('collapsed');
                    });
                });

                // Sidebar item clicks
                document.querySelectorAll('.jtb-wb-item[data-type]').forEach(item => {
                    item.addEventListener('click', () => {
                        const type = item.dataset.type;
                        const id = parseInt(item.dataset.id);

                        if (type === 'settings') {
                            // Open theme settings
                            window.open('/admin/jtb/theme-settings', '_blank');
                            return;
                        }

                        this.switchContext(type, id);
                    });
                });

                // Add buttons
                document.querySelectorAll('.jtb-wb-add-item').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const action = btn.dataset.action;
                        this.handleAdd(action);
                    });
                });

                // AI Generate Website button
                const aiBtn = document.querySelector('[data-action="ai-generate"]');
                console.log('[WB] AI Button found:', aiBtn);
                if (aiBtn) {
                    aiBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('[WB] AI Button clicked - opening Multi-Agent!');
                        // Open Multi-Agent modal directly (skip old modal)
                        if (typeof JTB_MultiAgent !== 'undefined' && JTB_MultiAgent.openModal) {
                            JTB_MultiAgent.openModal();
                        } else {
                            console.error('[WB] JTB_MultiAgent not loaded!');
                            alert('Multi-Agent module not loaded. Please refresh the page.');
                        }
                    });
                }

            },

            async switchContext(type, id) {
                // Save current if dirty
                if (JTB && JTB.isDirty && JTB.isDirty()) {
                    const save = confirm('You have unsaved changes. Save before switching?');
                    if (save) {
                        await JTB.save();
                    }
                }

                // Update active state in sidebar
                document.querySelectorAll('.jtb-wb-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`.jtb-wb-item[data-type="${type}"][data-id="${id}"]`)?.classList.add('active');

                // Update state
                this.currentType = type;
                this.currentId = id;

                // Load content
                this.loadContent(type, id);
            },

            async loadContent(type, id) {
                const canvas = document.querySelector('.jtb-canvas-inner');
                canvas.innerHTML = '<div class="jtb-loading"><div class="jtb-spinner"></div><p>Loading...</p></div>';

                try {
                    let endpoint, postId;

                    if (type === 'page') {
                        // Load page content
                        endpoint = `/api/jtb/load/${id}`;
                        postId = id;
                    } else {
                        // Load template content
                        endpoint = `/api/jtb/template-get/${id}`;
                        postId = null;
                    }

                    const response = await fetch(endpoint);
                    const data = await response.json();

                    if (data.success || data.ok) {
                        let content;
                        if (type === 'page') {
                            content = data.data?.content || { version: '1.0', content: [] };
                        } else {
                            content = data.template?.content || { version: '1.0', content: [] };
                        }

                        // Re-initialize JTB with new content
                        JTB.config.postId = postId;
                        JTB.config.templateId = type !== 'page' ? id : null;
                        JTB.config.templateType = type !== 'page' ? type : null;
                        JTB.state.content = content;
                        JTB.renderCanvas();

                        // Update header title
                        const titleEl = document.querySelector('.jtb-header-title strong');
                        if (titleEl) {
                            const itemName = document.querySelector(`.jtb-wb-item[data-type="${type}"][data-id="${id}"] .jtb-wb-item-name`)?.textContent || 'Untitled';
                            titleEl.textContent = itemName;
                        }
                    } else {
                        throw new Error(data.error || 'Failed to load content');
                    }
                } catch (err) {
                    console.error('Load error:', err);
                    canvas.innerHTML = `<div class="jtb-error" style="padding:40px;text-align:center;color:#f87171;">
                        <p>Failed to load content</p>
                        <p style="font-size:12px;opacity:0.7;">${err.message}</p>
                    </div>`;
                }
            },

            async handleAdd(action) {
                const typeMap = {
                    'add-header': 'header',
                    'add-footer': 'footer',
                    'add-body': 'body',
                    'add-page': 'page'
                };

                const type = typeMap[action];
                if (!type) return;

                if (type === 'page') {
                    // Redirect to CMS page creation
                    window.location.href = '/admin/pages/new';
                    return;
                }

                const name = prompt(`Enter name for new ${type}:`);
                if (!name) return;

                try {
                    const response = await fetch('/api/jtb/template-save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            name: name,
                            type: type,
                            content: { version: '1.0', content: [] }
                        })
                    });

                    const data = await response.json();

                    if (data.success || data.ok) {
                        // Reload page to show new template
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'Failed to create template'));
                    }
                } catch (err) {
                    console.error('Create error:', err);
                    alert('Failed to create template');
                }
            },

        };

        // Initialize JTB first
        JTB.init({
            postId: <?= $defaultHeader ? 'null' : 'null' ?>,
            templateId: <?= $defaultHeader ? $defaultHeader['id'] : 'null' ?>,
            templateType: 'header',
            csrfToken: '<?= $esc($csrfToken) ?>',
            apiUrl: '/api/jtb',
            pexelsApiKey: '<?= $esc($pexelsApiKey) ?>'
        });

        // Override JTB.saveContent to handle templates
        // IMPORTANT: Save button calls saveContent(), not save()!
        if (typeof JTB.saveContent === 'function') {
            const originalSaveContent = JTB.saveContent.bind(JTB);
            JTB.saveContent = async function() {
                // For pages, use original save (calls /api/jtb/save)
                if (WB.currentType === 'page') {
                    // Set postId for page save
                    JTB.config.postId = WB.currentId;
                    return originalSaveContent();
                }

                // For templates (header/footer/body), use template-save API
                const templateId = WB.currentId;
                const templateType = WB.currentType;

                if (!templateId || !templateType) {
                    JTB.showNotification('No template selected', 'error');
                    return;
                }

                // Find template name from arrays
                let templateName = 'Untitled';
                let templates = [];
                if (templateType === 'header') templates = WB.headers;
                else if (templateType === 'footer') templates = WB.footers;
                else if (templateType === 'body') templates = WB.bodyTemplates;

                const template = templates.find(t => parseInt(t.id) === parseInt(templateId));
                if (template) {
                    templateName = template.name;
                }

                // Save as template
                try {
                    JTB.showNotification('Saving...', 'info');

                    const response = await fetch('/api/jtb/template-save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': WB.csrfToken
                        },
                        body: JSON.stringify({
                            id: templateId,
                            name: templateName,
                            type: templateType,
                            content: JTB.state.content
                        })
                    });

                    const data = await response.json();

                    if (data.success || data.ok) {
                        JTB.showNotification('Template saved!', 'success');
                        JTB.state.lastSavedContent = JSON.stringify(JTB.state.content);
                    } else {
                        throw new Error(data.error || 'Save failed');
                    }
                } catch (err) {
                    console.error('Save error:', err);
                    JTB.showNotification('Save failed: ' + err.message, 'error');
                }
            };
        } // end if JTB.saveContent exists

        // Initialize Website Builder
        WB.init();

        // Initialize AI Panel
        if (typeof JTB_AI !== 'undefined') {
            JTB_AI.init({
                csrfToken: '<?= $esc($csrfToken) ?>',
                apiUrl: '/api/jtb/ai',
                pageId: null
            });
        }

        // Initialize Multi-Agent AI
        if (typeof JTB_AI_MultiAgent !== 'undefined') {
            JTB_AI_MultiAgent.init({
                csrfToken: '<?= $esc($csrfToken) ?>',
                apiUrl: '/api/jtb/ai'
            });

            // Connect Multi-Agent button to open modal

            // Apply generated website to builder
            JTB_AI_MultiAgent.onApply = async (website) => {
                console.log('[WB] ====== APPLY CALLBACK START ======');
                console.log('[WB] website object:', website);
                console.log('[WB] website.header:', website?.header);
                console.log('[WB] website.header.sections:', website?.header?.sections);
                console.log('[WB] website.footer:', website?.footer);
                console.log('[WB] website.pages:', website?.pages);
                console.log('[WB] WB.headers:', WB.headers);
                console.log('[WB] WB.footers:', WB.footers);
                console.log('[WB] WB.pages:', WB.pages);

                if (!website) {
                    alert('No website data received from AI');
                    return;
                }

                // Check if JTB canvas is available
                if (!JTB || typeof JTB.renderCanvas !== 'function') {
                    console.error('[WB] JTB not available');
                    alert('Builder not ready. Please reload the page and try again.');
                    return;
                }

                // Store the generated website for later use
                WB.generatedWebsite = website;

                let appliedCount = 0;
                let messages = [];

                // HEADER
                if (website.header && website.header.sections && website.header.sections.length > 0) {
                    if (WB.headers && WB.headers.length > 0) {
                        // Apply to first header
                        WB.currentId = WB.headers[0].id;
                        WB.currentType = 'header';
                        JTB.config.templateId = WB.headers[0].id;
                        JTB.config.templateType = 'header';
                        JTB.state.content = { version: '1.0', content: website.header.sections };
                        JTB.renderCanvas();
                        appliedCount++;
                        messages.push(`Header applied to "${WB.headers[0].name || 'Header #' + WB.headers[0].id}"`);
                        console.log('[WB] Header applied, sections:', website.header.sections.length);
                    } else {
                        messages.push('⚠️ No header template exists - create one first in Theme Builder');
                        console.warn('[WB] No headers available to apply to');
                    }
                } else {
                    console.log('[WB] No header in website data');
                }

                // FOOTER
                if (website.footer && website.footer.sections && website.footer.sections.length > 0) {
                    if (WB.footers && WB.footers.length > 0) {
                        // Store for later (we can only edit one thing at a time)
                        WB._pendingFooter = {
                            id: WB.footers[0].id,
                            content: { version: '1.0', content: website.footer.sections }
                        };
                        messages.push(`Footer ready - click on footer in sidebar to apply`);
                        console.log('[WB] Footer prepared, sections:', website.footer.sections.length);
                    } else {
                        messages.push('⚠️ No footer template exists - create one first');
                        console.warn('[WB] No footers available');
                    }
                }

                // PAGES
                if (website.pages && Object.keys(website.pages).length > 0) {
                    const pageKeys = Object.keys(website.pages);
                    
                    if (WB.pages && WB.pages.length > 0) {
                        // Store pages mapping for later
                        WB._pendingPages = {};
                        
                        pageKeys.forEach((pageKey, index) => {
                            const pageData = website.pages[pageKey];
                            if (pageData.sections && pageData.sections.length > 0) {
                                // Try to match by slug or use by index
                                let targetPage = WB.pages.find(p => 
                                    p.slug === pageKey || 
                                    p.slug === pageKey.toLowerCase() ||
                                    p.title?.toLowerCase() === pageKey.toLowerCase()
                                );
                                
                                if (!targetPage && index < WB.pages.length) {
                                    targetPage = WB.pages[index];
                                }
                                
                                if (targetPage) {
                                    WB._pendingPages[targetPage.id] = {
                                        pageKey: pageKey,
                                        content: { version: '1.0', content: pageData.sections }
                                    };
                                    messages.push(`Page "${pageKey}" → "${targetPage.title || targetPage.slug}"`);
                                }
                            }
                        });
                        
                        // Apply first page immediately if no header was applied
                        if (appliedCount === 0 && Object.keys(WB._pendingPages).length > 0) {
                            const firstPageId = Object.keys(WB._pendingPages)[0];
                            const firstPageData = WB._pendingPages[firstPageId];
                            WB.currentId = parseInt(firstPageId);
                            WB.currentType = 'page';
                            JTB.config.postId = parseInt(firstPageId);
                            JTB.config.templateId = null;
                            JTB.config.templateType = null;
                            JTB.state.content = firstPageData.content;
                            JTB.renderCanvas();
                            appliedCount++;
                            console.log('[WB] First page applied:', firstPageData.pageKey);
                        }
                    } else {
                        messages.push(`⚠️ No pages exist in CMS - create pages first`);
                        pageKeys.forEach(key => {
                            messages.push(`   Generated: "${key}" (${website.pages[key].sections?.length || 0} sections)`);
                        });
                    }
                }

                // Update sidebar selection
                if (WB.currentId && WB.currentType) {
                    document.querySelectorAll('.jtb-wb-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    const activeItem = document.querySelector(`.jtb-wb-item[data-type="${WB.currentType}"][data-id="${WB.currentId}"]`);
                    if (activeItem) {
                        activeItem.classList.add('active');
                    }
                }

                // Show result
                console.log('[WB] ====== APPLY COMPLETE ======');
                console.log('[WB] Applied:', appliedCount);
                console.log('[WB] Messages:', messages);

                if (appliedCount > 0) {
                    JTB.showNotification('Website applied! Click sidebar items to see all parts. Don\'t forget to save.', 'success');
                } else if (messages.length > 0) {
                    alert('Website generated but could not apply:\n\n' + messages.join('\n') + '\n\nCreate templates/pages first, then try again.');
                } else {
                    alert('No content was generated. Please try again.');
                }
            };

            // Override switchContext to apply pending content
            const originalSwitchContext = WB.switchContext.bind(WB);
            WB.switchContext = async function(type, id) {
                await originalSwitchContext(type, id);
                
                // Check if we have pending content for this item
                if (type === 'footer' && WB._pendingFooter && WB._pendingFooter.id === id) {
                    JTB.state.content = WB._pendingFooter.content;
                    JTB.renderCanvas();
                    console.log('[WB] Pending footer applied');
                    delete WB._pendingFooter;
                } else if (type === 'page' && WB._pendingPages && WB._pendingPages[id]) {
                    JTB.state.content = WB._pendingPages[id].content;
                    JTB.renderCanvas();
                    console.log('[WB] Pending page applied:', WB._pendingPages[id].pageKey);
                    delete WB._pendingPages[id];
                }
            };

        }

    });
    </script>

    <!-- Multi-Agent Modal HTML -->
    <div class="jtb-multiagent-overlay" id="jtb-multiagent-overlay"></div>
    <div class="jtb-multiagent-modal" id="jtb-multiagent-modal" role="dialog" aria-modal="true" aria-labelledby="ma-modal-title">
        <!-- Header -->
        <div class="jtb-multiagent-header">
            <div class="jtb-multiagent-header-left">
                <div class="jtb-multiagent-logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                    <span>AI Website Builder</span>
                </div>
                <span class="jtb-multiagent-badge">Multi-Agent</span>
            </div>
            <div class="jtb-multiagent-header-right">
                <div class="jtb-multiagent-phase-indicator" id="ma-phase-indicator">
                    <span class="phase-dot"></span>
                    <span id="ma-phase-text">Ready</span>
                </div>
                <button class="jtb-multiagent-close" id="ma-close-btn" aria-label="Close modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="jtb-multiagent-body">
            <!-- PHASE: INPUT -->
            <div class="jtb-multiagent-phase jtb-ma-phase-input active" id="ma-phase-input">
                <div class="jtb-ma-input-container">
                    <div class="jtb-ma-input-header">
                        <h2 id="ma-modal-title">Describe Your Website</h2>
                        <p>Tell us about your business and we'll create a preview mockup you can iterate on before building.</p>
                    </div>

                    <!-- Business Description -->
                    <div class="jtb-ma-form-group">
                        <label for="jtb-ma-prompt">Business Description <span class="required">*</span></label>
                        <textarea id="jtb-ma-prompt" class="jtb-ma-textarea" rows="4" placeholder="Example: TechFlow is a project management SaaS for remote teams. We help startups organize tasks, track time, and collaborate in real-time. Key features: Kanban boards, time tracking, team chat, 50+ integrations."></textarea>
                        <span class="hint">Include company name, target audience, and main features/services</span>
                    </div>

                    <!-- Industry & Style -->
                    <div class="jtb-ma-form-row">
                        <div class="jtb-ma-form-group">
                            <label for="jtb-ma-industry">Industry</label>
                            <div class="jtb-ma-select-wrapper">
                                <select id="jtb-ma-industry" class="jtb-ma-select">
                                    <option value="technology">Technology / SaaS</option>
                                    <option value="agency">Agency / Creative</option>
                                    <option value="ecommerce">E-commerce / Retail</option>
                                    <option value="healthcare">Healthcare / Medical</option>
                                    <option value="finance">Finance / Fintech</option>
                                    <option value="education">Education / E-learning</option>
                                    <option value="realestate">Real Estate</option>
                                    <option value="restaurant">Restaurant / Food</option>
                                    <option value="fitness">Fitness / Wellness</option>
                                    <option value="legal">Legal / Consulting</option>
                                    <option value="nonprofit">Nonprofit / NGO</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="jtb-ma-form-group">
                            <label for="jtb-ma-style">Visual Style</label>
                            <div class="jtb-ma-select-wrapper">
                                <select id="jtb-ma-style" class="jtb-ma-select">
                                    <option value="modern">Modern & Clean</option>
                                    <option value="minimal">Minimal & Simple</option>
                                    <option value="bold">Bold & Vibrant</option>
                                    <option value="elegant">Elegant & Luxury</option>
                                    <option value="playful">Playful & Fun</option>
                                    <option value="corporate">Corporate & Professional</option>
                                    <option value="dark">Dark & Dramatic</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pages -->
                    <div class="jtb-ma-form-group">
                        <label>Pages to Generate</label>
                        <div class="jtb-ma-checkbox-group">
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-home" value="home" checked>
                                <label for="ma-page-home">Home</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-about" value="about" checked>
                                <label for="ma-page-about">About</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-services" value="services" checked>
                                <label for="ma-page-services">Services</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-contact" value="contact" checked>
                                <label for="ma-page-contact">Contact</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-pricing" value="pricing">
                                <label for="ma-page-pricing">Pricing</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-blog" value="blog">
                                <label for="ma-page-blog">Blog</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-faq" value="faq">
                                <label for="ma-page-faq">FAQ</label>
                            </div>
                            <div class="jtb-ma-checkbox-item">
                                <input type="checkbox" id="ma-page-team" value="team">
                                <label for="ma-page-team">Team</label>
                            </div>
                        </div>
                    </div>

                    <!-- AI Provider & Model -->
                    <div class="jtb-ma-form-row">
                        <div class="jtb-ma-form-group">
                            <label for="jtb-ma-provider">AI Provider</label>
                            <div class="jtb-ma-select-wrapper">
                                <select id="jtb-ma-provider" class="jtb-ma-select">
                                    <!-- Populated from WB_AI.availableProviders -->
                                </select>
                            </div>
                        </div>
                        <div class="jtb-ma-form-group">
                            <label for="jtb-ma-model">AI Model</label>
                            <div class="jtb-ma-select-wrapper">
                                <select id="jtb-ma-model" class="jtb-ma-select">
                                    <!-- Updated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="jtb-ma-form-group">
                        <label for="jtb-ma-language">Content Language <span class="jtb-ma-optional">(optional)</span></label>
                        <div class="jtb-ma-select-wrapper">
                            <select id="jtb-ma-language" class="jtb-ma-select">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                    </div>

                    <!-- Brand Kit (collapsible) -->
                    <div class="jtb-ma-form-group jtb-ma-optional-section">
                        <label class="jtb-ma-toggle-label" data-toggle="ma-brandkit-section">
                            <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            <span>Brand Kit</span>
                            <span class="jtb-ma-optional">(optional - extract colors from existing site)</span>
                        </label>
                        <div id="ma-brandkit-section" class="jtb-ma-collapsible">
                            <input type="text" id="jtb-ma-brand-url" class="jtb-ma-input" placeholder="https://your-website.com or https://example.com/logo.png">
                            <button type="button" class="jtb-ma-btn-secondary" id="ma-extract-brand-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                Extract Brand Kit
                            </button>
                            <div id="ma-brand-result" class="jtb-ma-result-box"></div>
                        </div>
                    </div>

                    <!-- Competitor Inspiration (collapsible) -->
                    <div class="jtb-ma-form-group jtb-ma-optional-section">
                        <label class="jtb-ma-toggle-label" data-toggle="ma-competitor-section">
                            <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            <span>Competitor Inspiration</span>
                            <span class="jtb-ma-optional">(optional - analyze & create better)</span>
                        </label>
                        <div id="ma-competitor-section" class="jtb-ma-collapsible">
                            <input type="text" id="jtb-ma-competitor-url" class="jtb-ma-input" placeholder="https://competitor-website.com">
                            <span class="hint">AI will analyze structure and create something unique and better</span>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <button type="button" class="jtb-ma-generate-btn" id="ma-generate-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                        Generate Mockup Preview
                    </button>
                </div>
            </div>

            <!-- PHASE: GENERATING -->
            <div class="jtb-multiagent-phase jtb-ma-phase-generating" id="ma-phase-generating">
                <div class="jtb-ma-generating-animation">
                    <div class="jtb-ma-generating-circle"></div>
                    <div class="jtb-ma-generating-circle"></div>
                    <div class="jtb-ma-generating-circle"></div>
                    <svg class="jtb-ma-generating-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                </div>
                <div class="jtb-ma-generating-text">
                    <h3>Designing Your Website</h3>
                    <p>AI is creating a visual mockup based on your description...</p>
                </div>
                <div class="jtb-ma-generating-status">
                    <span class="status-text">
                        <span class="status-dot"></span>
                        <span id="ma-generating-status">Analyzing requirements...</span>
                    </span>
                </div>
            </div>

            <!-- PHASE: MOCKUP PREVIEW -->
            <div class="jtb-multiagent-phase jtb-ma-phase-mockup" id="ma-phase-mockup">
                <!-- Preview Area -->
                <div class="jtb-ma-preview-area">
                    <div class="jtb-ma-preview-toolbar">
                        <div class="jtb-ma-device-switcher">
                            <button class="jtb-ma-device-btn active" data-device="desktop" title="Desktop">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                                    <path d="M8 21h8M12 17v4"/>
                                </svg>
                            </button>
                            <button class="jtb-ma-device-btn" data-device="tablet" title="Tablet">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="4" y="2" width="16" height="20" rx="2"/>
                                    <path d="M12 18h.01"/>
                                </svg>
                            </button>
                            <button class="jtb-ma-device-btn" data-device="phone" title="Phone">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="2" width="14" height="20" rx="2"/>
                                    <path d="M12 18h.01"/>
                                </svg>
                            </button>
                        </div>
                        <div class="jtb-ma-preview-info">
                            <span class="page-label">Viewing:</span>
                            <span class="page-name" id="ma-current-page">Home</span>
                        </div>
                        <div class="jtb-ma-preview-actions">
                            <button class="jtb-ma-preview-btn" id="ma-refresh-preview" title="Refresh">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 4v6h-6M1 20v-6h6"/>
                                    <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                                </svg>
                            </button>
                            <button class="jtb-ma-preview-btn" id="ma-fullscreen-preview" title="Fullscreen">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="jtb-ma-preview-container">
                        <div class="jtb-ma-preview-frame-wrapper" data-device="desktop">
                            <iframe id="ma-preview-frame" class="jtb-ma-preview-frame"></iframe>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="jtb-ma-sidebar">
                    <div class="jtb-ma-sidebar-header">
                        <h3>Refine Your Design</h3>
                        <div class="jtb-ma-sidebar-tabs">
                            <button class="jtb-ma-sidebar-tab active" data-tab="iterate">Iterate</button>
                            <button class="jtb-ma-sidebar-tab" data-tab="structure">Structure</button>
                        </div>
                    </div>
                    <div class="jtb-ma-sidebar-content">
                        <!-- Iteration Panel -->
                        <div class="jtb-ma-iteration-panel" id="ma-tab-iterate">
                            <div class="jtb-ma-iteration-input-wrapper">
                                <textarea id="ma-iteration-input" class="jtb-ma-iteration-input" placeholder="Describe changes you'd like...&#10;e.g., 'Make the hero section darker with a gradient background'"></textarea>
                            </div>
                            <button class="jtb-ma-iteration-btn" id="ma-iterate-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 4v6h-6M1 20v-6h6"/>
                                    <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                                </svg>
                                Update Mockup
                            </button>

                            <!-- Suggestions -->
                            <div class="jtb-ma-suggestions">
                                <div class="jtb-ma-suggestions-title">Quick suggestions:</div>
                                <div class="jtb-ma-suggestions-list">
                                    <div class="jtb-ma-suggestion-item" data-suggestion="Add a testimonials section after features">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                        Add testimonials section
                                    </div>
                                    <div class="jtb-ma-suggestion-item" data-suggestion="Make the color scheme more vibrant">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                                        Make colors more vibrant
                                    </div>
                                    <div class="jtb-ma-suggestion-item" data-suggestion="Change hero to dark background with light text">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                                        Dark hero background
                                    </div>
                                    <div class="jtb-ma-suggestion-item" data-suggestion="Add FAQ section before contact">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/></svg>
                                        Add FAQ section
                                    </div>
                                </div>
                            </div>

                            <!-- History -->
                            <div class="jtb-ma-history" id="ma-history-container" style="display:none;">
                                <div class="jtb-ma-history-title">Change history:</div>
                                <div class="jtb-ma-history-list" id="ma-history-list"></div>
                            </div>
                        </div>

                        <!-- Structure Panel -->
                        <div class="jtb-ma-structure-panel" id="ma-tab-structure">
                            <div class="jtb-ma-structure-tree" id="ma-structure-tree">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>
                    <div class="jtb-ma-sidebar-footer">
                        <button class="jtb-ma-action-btn secondary" id="ma-restart-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                            Start Over
                        </button>
                        <button class="jtb-ma-action-btn primary" id="ma-accept-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                            Accept & Build
                        </button>
                    </div>
                </div>
            </div>

            <!-- PHASE: BUILDING -->
            <div class="jtb-multiagent-phase jtb-ma-phase-building" id="ma-phase-building">
                <div class="jtb-ma-building-container">
                    <div class="jtb-ma-building-header">
                        <h2>Building Your Website</h2>
                        <p>Converting mockup to fully editable components...</p>
                    </div>

                    <div class="jtb-ma-progress-bar">
                        <div class="jtb-ma-progress-fill" id="ma-progress-fill" style="width: 0%"></div>
                    </div>

                    <div class="jtb-ma-steps-list" id="ma-steps-list">
                        <!-- Steps populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- PHASE: MAPPING (Save to CMS) -->
            <div class="jtb-multiagent-phase jtb-ma-phase-mapping" id="ma-phase-mapping">
                <div class="jtb-ma-mapping-header">
                    <h2>💾 Save to CMS</h2>
                    <p>Map generated content to your site structure</p>
                </div>
                
                <div class="jtb-ma-mapping-content">
                    <div class="jtb-ma-mapping-group">
                        <h4>📋 Header</h4>
                        <div class="jtb-ma-mapping-row">
                            <span class="mapping-label">Generated Header</span>
                            <select id="ma-map-header" class="jtb-ma-mapping-select">
                                <option value="new">Create New Header</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="jtb-ma-mapping-group">
                        <h4>📄 Pages</h4>
                        <div id="ma-mapping-pages-list"></div>
                    </div>
                    
                    <div class="jtb-ma-mapping-group">
                        <h4>📋 Footer</h4>
                        <div class="jtb-ma-mapping-row">
                            <span class="mapping-label">Generated Footer</span>
                            <select id="ma-map-footer" class="jtb-ma-mapping-select">
                                <option value="new">Create New Footer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="jtb-ma-mapping-options">
                        <label>
                            <input type="checkbox" id="ma-clear-existing" checked>
                            Clear existing content in selected targets
                        </label>
                    </div>
                </div>
                
                <div class="jtb-ma-mapping-actions">
                    <button class="jtb-ma-btn secondary" id="ma-mapping-back">← Back</button>
                    <button class="jtb-ma-btn primary" id="ma-mapping-save">💾 Save to CMS</button>
                </div>
            </div>

            <!-- PHASE: DONE -->
            <div class="jtb-multiagent-phase jtb-ma-phase-done" id="ma-phase-done">
                <div class="jtb-ma-done-icon">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
                <div class="jtb-ma-done-text">
                    <h2>Website Generated!</h2>
                    <p>Your website has been built and is ready to edit in the builder.</p>
                </div>
                <div class="jtb-ma-done-stats">
                    <div class="jtb-ma-done-stat">
                        <div class="stat-value" id="ma-stat-pages">4</div>
                        <div class="stat-label">Pages</div>
                    </div>
                    <div class="jtb-ma-done-stat">
                        <div class="stat-value" id="ma-stat-sections">24</div>
                        <div class="stat-label">Sections</div>
                    </div>
                    <div class="jtb-ma-done-stat">
                        <div class="stat-value" id="ma-stat-modules">86</div>
                        <div class="stat-label">Modules</div>
                    </div>
                </div>
                <div class="jtb-ma-done-actions">
                    <button class="jtb-ma-done-btn secondary" id="ma-done-close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                        Close
                    </button>
                    <button class="jtb-ma-done-btn primary" id="ma-done-apply">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        Apply to Builder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container for Multi-Agent -->
    <div class="jtb-ma-toast-container" id="ma-toast-container"></div>

</body>
</html>
