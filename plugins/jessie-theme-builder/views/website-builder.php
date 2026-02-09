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

    <!-- AI Website Generator Modal - ULTRA Professional Version -->
    <div id="jtb-ai-website-modal" class="jtb-modal" style="display:none;">
        <div class="jtb-modal-backdrop"></div>
        <div class="jtb-modal-content jtb-ai-modal-large">
            <div class="jtb-modal-header">
                <h2>
                    <div class="ai-header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5"></path>
                            <path d="M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <div class="ai-header-text">
                        <span id="ai-modal-title">AI Website Generator</span>
                        <span class="ai-modal-subtitle">Create a complete website in seconds</span>
                    </div>
                </h2>
                <div class="jtb-modal-header-actions">
                    <button class="jtb-btn jtb-btn-sm jtb-btn-ghost" id="ai-back-to-form-btn" style="display:none;" data-action="back-to-form">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                    <button class="jtb-modal-close" data-action="close-ai-modal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="jtb-modal-body">
                <!-- STEP 1: Form -->
                <div class="jtb-ai-form" id="ai-form-step">
                    <!-- Business Description -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">💼</div>
                            <div class="section-text">
                                <h4>Tell us about your business</h4>
                                <p>The more details you provide, the better the AI can design your website</p>
                            </div>
                        </div>
                        <div class="jtb-field-group">
                            <textarea id="ai-website-prompt" rows="4" placeholder="Example: We are TechFlow, a project management SaaS for remote teams. We help companies organize tasks, track time, and collaborate in real-time. Our main features include Kanban boards, time tracking, team chat, and integrations with 50+ tools. Our target audience is startups and small businesses looking for an affordable yet powerful solution."></textarea>
                            <div class="prompt-tips">
                                <span class="tip-item">💡 Include your company name</span>
                                <span class="tip-item">🎯 Describe your target audience</span>
                                <span class="tip-item">✨ List key features/services</span>
                            </div>
                        </div>
                    </div>

                    <!-- Industry & Style -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">🎨</div>
                            <div class="section-text">
                                <h4>Design preferences</h4>
                                <p>Choose your industry and visual style</p>
                            </div>
                        </div>
                        <div class="jtb-field-row">
                            <div class="jtb-field-group">
                                <label><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg> Industry</label>
                                <div class="jtb-select-wrapper">
                                    <select id="ai-website-industry">
                                        <option value="technology" data-icon="💻">Technology / SaaS</option>
                                        <option value="agency" data-icon="🎨">Agency / Creative</option>
                                        <option value="ecommerce" data-icon="🛒">E-commerce / Retail</option>
                                        <option value="healthcare" data-icon="🏥">Healthcare / Medical</option>
                                        <option value="finance" data-icon="💰">Finance / Fintech</option>
                                        <option value="education" data-icon="📚">Education / E-learning</option>
                                        <option value="realestate" data-icon="🏠">Real Estate</option>
                                        <option value="restaurant" data-icon="🍽️">Restaurant / Food</option>
                                        <option value="fitness" data-icon="💪">Fitness / Wellness</option>
                                        <option value="legal" data-icon="⚖️">Legal / Consulting</option>
                                        <option value="nonprofit" data-icon="❤️">Nonprofit / NGO</option>
                                        <option value="other" data-icon="🌐">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="jtb-field-group">
                                <label><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg> Visual Style</label>
                                <div class="jtb-select-wrapper">
                                    <select id="ai-website-style">
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
                        <!-- Style Preview -->
                        <div class="style-preview-bar" id="style-preview-bar">
                            <div class="style-colors">
                                <div class="color-dot" data-color="primary" style="background:#7c3aed"></div>
                                <div class="color-dot" data-color="secondary" style="background:#3b82f6"></div>
                                <div class="color-dot" data-color="accent" style="background:#10b981"></div>
                            </div>
                            <span class="style-label">Modern color palette</span>
                        </div>
                    </div>

                    <!-- 8.1 Brand Kit Extraction -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">🎯</div>
                            <div class="section-text">
                                <h4>Brand Kit <span style="font-size:10px;color:#6366f1;font-weight:400;">(optional)</span></h4>
                                <p>Paste your existing website or logo URL to extract colors, fonts, and style</p>
                            </div>
                        </div>
                        <div class="jtb-field-group">
                            <input type="text" id="ai-brand-url" placeholder="https://your-website.com or https://example.com/logo.png" style="width:100%;">
                            <div class="prompt-tips">
                                <span class="tip-item">🌐 Paste website URL for full analysis</span>
                                <span class="tip-item">🖼️ Or paste logo image URL</span>
                            </div>
                            <button type="button" class="jtb-btn jtb-btn-sm" id="extract-brand-btn" style="margin-top:8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                Extract Brand Kit
                            </button>
                            <div id="brand-kit-result" style="display:none;margin-top:12px;padding:12px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);border-radius:8px;"></div>
                        </div>
                    </div>

                    <!-- 8.2 Competitor Analysis -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">🔍</div>
                            <div class="section-text">
                                <h4>Competitor Inspiration <span style="font-size:10px;color:#6366f1;font-weight:400;">(optional)</span></h4>
                                <p>Generate a website inspired by (but unique from) a competitor</p>
                            </div>
                        </div>
                        <div class="jtb-field-group">
                            <input type="text" id="ai-competitor-url" placeholder="https://competitor-website.com" style="width:100%;">
                            <div class="prompt-tips">
                                <span class="tip-item">🏆 AI will analyze structure and create something better</span>
                            </div>
                        </div>
                    </div>

                    <!-- 8.5 Multi-language -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">🌍</div>
                            <div class="section-text">
                                <h4>Language <span style="font-size:10px;color:#6366f1;font-weight:400;">(optional)</span></h4>
                                <p>Generate website content in a specific language</p>
                            </div>
                        </div>
                        <div class="jtb-field-row">
                            <div class="jtb-field-group">
                                <div class="jtb-select-wrapper">
                                    <select id="ai-website-language">
                                        <option value="">English (default)</option>
                                        <option value="pl">Polish / Polski</option>
                                        <option value="de">German / Deutsch</option>
                                        <option value="fr">French / Français</option>
                                        <option value="es">Spanish / Español</option>
                                        <option value="it">Italian / Italiano</option>
                                        <option value="pt">Portuguese / Português</option>
                                        <option value="nl">Dutch / Nederlands</option>
                                        <option value="sv">Swedish / Svenska</option>
                                        <option value="ru">Russian / Русский</option>
                                        <option value="uk">Ukrainian / Українська</option>
                                        <option value="cs">Czech / Čeština</option>
                                        <option value="ja">Japanese / 日本語</option>
                                        <option value="ko">Korean / 한국어</option>
                                        <option value="zh">Chinese / 中文</option>
                                        <option value="ar">Arabic / العربية</option>
                                        <option value="tr">Turkish / Türkçe</option>
                                        <option value="hi">Hindi / हिन्दी</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 8.6 Generation Mode -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">⚡</div>
                            <div class="section-text">
                                <h4>Generation Mode</h4>
                                <p>Choose how the website is generated</p>
                            </div>
                        </div>
                        <div class="jtb-field-group">
                            <div class="generation-mode-cards" style="display:flex;gap:12px;">
                                <label class="mode-card" style="flex:1;display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(255,255,255,0.05);border:2px solid rgba(99,102,241,0.5);border-radius:10px;cursor:pointer;">
                                    <input type="radio" name="gen_mode" value="standard" checked style="display:none;">
                                    <div class="mode-icon" style="font-size:24px;">🚀</div>
                                    <div class="mode-info">
                                        <strong style="color:#fff;font-size:13px;">Standard</strong><br>
                                        <span style="font-size:11px;color:#888;">Full website in one request (~15s)</span>
                                    </div>
                                </label>
                                <label class="mode-card" style="flex:1;display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(255,255,255,0.05);border:2px solid rgba(255,255,255,0.1);border-radius:10px;cursor:pointer;">
                                    <input type="radio" name="gen_mode" value="progressive" style="display:none;">
                                    <div class="mode-icon" style="font-size:24px;">📈</div>
                                    <div class="mode-info">
                                        <strong style="color:#fff;font-size:13px;">Progressive</strong><br>
                                        <span style="font-size:11px;color:#888;">Watch it grow in quality (4 stages)</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- AI Provider & Model (same structure as ai-panel.php) -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">🤖</div>
                            <div class="section-text">
                                <h4>AI Provider & Model</h4>
                                <p>Choose which AI engine powers the generation</p>
                            </div>
                        </div>
                        <div class="jtb-field-row">
                            <div class="jtb-ai-field">
                                <label>AI Provider</label>
                                <select id="ai-provider-select" onchange="WB_AI.handleProviderChange(this.value)">
                                    <option value="anthropic" selected>Anthropic Claude (Recommended)</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="deepseek">DeepSeek</option>
                                </select>
                            </div>
                            <div class="jtb-ai-field">
                                <label>AI Model</label>
                                <select id="ai-model-select">
                                    <option value="claude-opus-4-5-20251101" selected>Claude Opus 4.5 (Best)</option>
                                    <option value="claude-sonnet-4-20250514">Claude Sonnet 4</option>
                                    <option value="claude-3-5-sonnet-20241022">Claude 3.5 Sonnet</option>
                                    <option value="claude-3-haiku-20240307">Claude 3 Haiku (Fast)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pages Selection -->
                    <div class="jtb-ai-section">
                        <div class="jtb-ai-section-header">
                            <div class="section-icon">📄</div>
                            <div class="section-text">
                                <h4>Pages to generate</h4>
                                <p>Select which pages AI should create for your website</p>
                            </div>
                            <div class="section-action">
                                <button type="button" class="jtb-btn-link" id="toggle-all-pages">Select All</button>
                            </div>
                        </div>
                        <div class="jtb-pages-grid">
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="home" checked>
                                <div class="card-content">
                                    <span class="card-icon">🏠</span>
                                    <span class="card-name">Home</span>
                                    <span class="card-desc">Hero, features, CTA</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="about" checked>
                                <div class="card-content">
                                    <span class="card-icon">👥</span>
                                    <span class="card-name">About</span>
                                    <span class="card-desc">Story, team, values</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="services" checked>
                                <div class="card-content">
                                    <span class="card-icon">⚡</span>
                                    <span class="card-name">Services</span>
                                    <span class="card-desc">What you offer</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="contact" checked>
                                <div class="card-content">
                                    <span class="card-icon">📧</span>
                                    <span class="card-name">Contact</span>
                                    <span class="card-desc">Form, map, info</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="pricing">
                                <div class="card-content">
                                    <span class="card-icon">💎</span>
                                    <span class="card-name">Pricing</span>
                                    <span class="card-desc">Plans & features</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="faq">
                                <div class="card-content">
                                    <span class="card-icon">❓</span>
                                    <span class="card-name">FAQ</span>
                                    <span class="card-desc">Common questions</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="blog">
                                <div class="card-content">
                                    <span class="card-icon">📝</span>
                                    <span class="card-name">Blog</span>
                                    <span class="card-desc">Articles listing</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="team">
                                <div class="card-content">
                                    <span class="card-icon">🧑‍💼</span>
                                    <span class="card-name">Team</span>
                                    <span class="card-desc">Team members</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                            <label class="jtb-page-card">
                                <input type="checkbox" name="pages" value="portfolio">
                                <div class="card-content">
                                    <span class="card-icon">🖼️</span>
                                    <span class="card-name">Portfolio</span>
                                    <span class="card-desc">Work showcase</span>
                                </div>
                                <div class="card-check"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                            </label>
                        </div>
                        <div class="pages-summary" id="pages-summary">
                            <span class="summary-icon">📊</span>
                            <span class="summary-text"><strong id="selected-pages-count">4</strong> pages selected • Estimated generation time: <strong>~15 seconds</strong></span>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Progress -->
                <div id="ai-website-progress" class="jtb-ai-progress" style="display:none;">
                    <div class="jtb-ai-progress-animation">
                        <div class="jtb-ai-orb"></div>
                        <div class="jtb-ai-rings">
                            <div class="ring ring-1"></div>
                            <div class="ring ring-2"></div>
                            <div class="ring ring-3"></div>
                        </div>
                    </div>
                    <h3>Generating Your Website...</h3>
                    <p id="ai-website-status">Connecting to AI...</p>
                    <div class="jtb-ai-progress-steps">
                        <div class="step" data-step="connect"><span class="icon">○</span> Connecting</div>
                        <div class="step" data-step="generate"><span class="icon">○</span> Generating</div>
                        <div class="step" data-step="process"><span class="icon">○</span> Processing</div>
                        <div class="step" data-step="complete"><span class="icon">○</span> Complete</div>
                    </div>
                </div>

                <!-- STEP 3: Preview Result -->
                <div id="ai-website-result" style="display:none;">
                    <!-- Stats Bar -->
                    <div class="jtb-ai-stats-bar">
                        <div class="stat">
                            <span class="stat-value" id="ai-stat-sections">0</span>
                            <span class="stat-label">Sections</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="ai-stat-modules">0</span>
                            <span class="stat-label">Modules</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="ai-stat-pages">0</span>
                            <span class="stat-label">Pages</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="ai-stat-time">0s</span>
                            <span class="stat-label">Generated in</span>
                        </div>
                    </div>

                    <!-- Preview Tabs -->
                    <div class="jtb-ai-preview-tabs">
                        <button class="tab active" data-preview="overview">📊 Overview</button>
                        <button class="tab" data-preview="header">🏠 Header</button>
                        <button class="tab" data-preview="footer">🦶 Footer</button>
                        <button class="tab" data-preview="pages">📄 Pages</button>
                        <button class="tab" data-preview="theme">🎨 Theme</button>
                        <button class="tab" data-preview="json">{ } JSON</button>
                    </div>

                    <!-- Preview Content -->
                    <div class="jtb-ai-preview-content">
                        <!-- Overview Tab -->
                        <div class="preview-panel active" data-panel="overview">
                            <!-- FULL PREVIEW BUTTON -->
                            <div class="full-preview-banner">
                                <div class="preview-banner-content">
                                    <div class="preview-banner-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </div>
                                    <div class="preview-banner-text">
                                        <h4>See Your Website</h4>
                                        <p>Preview how your generated website will look in the browser</p>
                                    </div>
                                </div>
                                <button class="jtb-btn jtb-btn-preview" id="open-full-preview-btn">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                    Open Full Preview
                                </button>
                            </div>

                            <!-- Live Preview Iframe in Overview -->
                            <div class="overview-live-preview">
                                <div class="preview-toolbar">
                                    <div class="preview-device-buttons">
                                        <button class="device-btn active" data-device="desktop" title="Desktop">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                        </button>
                                        <button class="device-btn" data-device="tablet" title="Tablet">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        </button>
                                        <button class="device-btn" data-device="mobile" title="Mobile">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                        </button>
                                    </div>
                                    <div class="preview-page-selector">
                                        <select id="preview-page-select">
                                            <option value="home">Home Page</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="preview-frame-wrapper" id="preview-frame-wrapper">
                                    <div class="preview-frame-loading" id="preview-loading">
                                        <div class="spinner"></div>
                                        <span>Rendering preview...</span>
                                    </div>
                                    <iframe id="overview-preview-iframe" sandbox="allow-same-origin allow-scripts"></iframe>
                                </div>
                            </div>

                            <!-- Summary Cards -->
                            <div class="jtb-ai-overview-grid">
                                <div class="overview-card">
                                    <div class="card-icon">🏠</div>
                                    <div class="card-info">
                                        <h4>Header</h4>
                                        <p id="overview-header-info">-</p>
                                    </div>
                                    <div class="card-status">✅</div>
                                </div>
                                <div class="overview-card">
                                    <div class="card-icon">🦶</div>
                                    <div class="card-info">
                                        <h4>Footer</h4>
                                        <p id="overview-footer-info">-</p>
                                    </div>
                                    <div class="card-status">✅</div>
                                </div>
                                <div id="overview-pages-list" class="overview-pages"></div>
                            </div>
                        </div>

                        <!-- Header Tab -->
                        <div class="preview-panel" data-panel="header">
                            <div class="preview-section-header">
                                <h4>Header Template</h4>
                                <span class="badge" id="header-sections-count">0 sections</span>
                            </div>
                            <div class="preview-render" id="preview-header-render"></div>
                            <div class="preview-structure" id="preview-header-structure"></div>
                        </div>

                        <!-- Footer Tab -->
                        <div class="preview-panel" data-panel="footer">
                            <div class="preview-section-header">
                                <h4>Footer Template</h4>
                                <span class="badge" id="footer-sections-count">0 sections</span>
                            </div>
                            <div class="preview-render" id="preview-footer-render"></div>
                            <div class="preview-structure" id="preview-footer-structure"></div>
                        </div>

                        <!-- Pages Tab -->
                        <div class="preview-panel" data-panel="pages">
                            <div class="pages-nav" id="pages-nav"></div>
                            <div class="pages-content" id="pages-content">
                                <p class="empty-state">Select a page from the tabs above</p>
                            </div>
                        </div>

                        <!-- Theme Tab -->
                        <div class="preview-panel" data-panel="theme">
                            <div class="theme-preview">
                                <div class="theme-section">
                                    <h4>Colors</h4>
                                    <div class="color-swatches" id="theme-colors"></div>
                                </div>
                                <div class="theme-section">
                                    <h4>Typography</h4>
                                    <div class="typography-preview" id="theme-typography"></div>
                                </div>
                            </div>
                        </div>

                        <!-- JSON Tab -->
                        <div class="preview-panel" data-panel="json">
                            <div class="json-toolbar">
                                <button class="jtb-btn jtb-btn-sm" id="copy-json-btn">📋 Copy JSON</button>
                                <button class="jtb-btn jtb-btn-sm" id="download-json-btn">⬇️ Download</button>
                            </div>
                            <pre class="json-view" id="preview-json"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="jtb-modal-footer">
                <div class="footer-left">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <span>Powered by AI • Results may vary</span>
                </div>
                <div class="footer-right">
                    <button class="jtb-btn" data-action="close-ai-modal">Cancel</button>
                    <button class="jtb-btn jtb-btn-secondary" id="ai-multiagent-btn" data-action="open-multiagent" title="Preview & Iterate before building">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        Multi-Agent (Preview)
                    </button>
                    <button class="jtb-btn jtb-btn-primary jtb-btn-glow" id="ai-website-generate-btn" data-action="generate-website">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                        Generate Website
                    </button>
                    <button class="jtb-btn jtb-btn-secondary" id="ai-website-regenerate-btn" data-action="regenerate-website" style="display:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                        Regenerate
                    </button>
                    <button class="jtb-btn jtb-btn-secondary" id="ai-website-translate-btn" data-action="translate-website" style="display:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        Translate
                    </button>
                    <button class="jtb-btn jtb-btn-secondary" id="ai-website-variants-btn" data-action="generate-variants" style="display:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        A/B Variants
                    </button>
                    <button class="jtb-btn jtb-btn-success" id="ai-website-apply-btn" data-action="apply-website" style="display:none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Apply to Site
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* AI Website Modal Styles - ULTRA Professional Version */
    /* Using #jtb-ai-website-modal for higher specificity over builder.css */
    #jtb-ai-website-modal.jtb-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        transform: none !important;
    }
    #jtb-ai-website-modal .jtb-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.85);
        backdrop-filter: blur(12px);
    }
    #jtb-ai-website-modal .jtb-modal-content {
        position: relative;
        background: linear-gradient(180deg, #1a1a2e 0%, #16162a 100%);
        border-radius: 20px;
        width: 800px !important;
        max-width: 90vw !important;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 30px 100px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.05), inset 0 1px 0 rgba(255,255,255,0.05);
    }
    #jtb-ai-website-modal .jtb-ai-modal-large {
        width: 800px !important;
        max-width: 90vw !important;
        max-height: 90vh;
    }
    #jtb-ai-website-modal .jtb-ai-modal-large.show-preview {
        width: 1200px !important;
        max-width: 95vw !important;
        height: 90vh;
    }

    /* Modal Header */
    #jtb-ai-website-modal .jtb-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
        background: linear-gradient(90deg, rgba(124,58,237,0.1) 0%, rgba(59,130,246,0.1) 100%);
    }
    #jtb-ai-website-modal .jtb-modal-header h2 {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
    }
    .ai-header-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(124,58,237,0.4);
    }
    .ai-header-icon svg {
        color: #fff;
    }
    .ai-header-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    #ai-modal-title {
        font-size: 17px;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.3px;
    }
    .ai-modal-subtitle {
        font-size: 12px;
        color: var(--jtb-text-muted, #888);
        font-weight: 400;
    }
    .jtb-modal-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .jtb-btn-ghost {
        background: rgba(255,255,255,0.05) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .jtb-btn-ghost:hover {
        background: rgba(255,255,255,0.1) !important;
    }
    #jtb-ai-website-modal .jtb-modal-close {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.05);
        border: none;
        color: var(--jtb-text-muted, #888);
        cursor: pointer;
        border-radius: 10px;
        transition: all 0.2s;
    }
    #jtb-ai-website-modal .jtb-modal-close:hover {
        background: rgba(239,68,68,0.2);
        color: #ef4444;
    }

    /* Modal Body */
    #jtb-ai-website-modal .jtb-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        max-height: calc(92vh - 160px);
    }
    #jtb-ai-website-modal .jtb-modal-body::-webkit-scrollbar {
        width: 8px;
    }
    #jtb-ai-website-modal .jtb-modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    #jtb-ai-website-modal .jtb-modal-body::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 4px;
    }
    #jtb-ai-website-modal .jtb-modal-body::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.2);
    }

    /* Modal Footer */
    #jtb-ai-website-modal .jtb-modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid rgba(255,255,255,0.06);
        background: rgba(0,0,0,0.3);
    }
    #jtb-ai-website-modal .jtb-modal-footer .footer-left {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--jtb-text-muted);
    }
    #jtb-ai-website-modal .jtb-modal-footer .footer-right {
        display: flex;
        gap: 10px;
    }

    /* Form Container */
    .jtb-ai-form {
        width: 100%;
    }

    /* Form Sections */
    .jtb-ai-section {
        padding: 24px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        width: 100%;
        box-sizing: border-box;
    }
    .jtb-ai-section:last-child {
        border-bottom: none;
    }
    .jtb-ai-section-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }
    .jtb-ai-section-header .section-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(124,58,237,0.1);
        border-radius: 10px;
        font-size: 18px;
        flex-shrink: 0;
    }
    .jtb-ai-section-header .section-text {
        flex: 1;
    }
    .jtb-ai-section-header .section-text h4 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 600;
        color: #fff;
    }
    .jtb-ai-section-header .section-text p {
        margin: 0;
        font-size: 13px;
        color: var(--jtb-text-muted);
    }
    .jtb-ai-section-header .section-action {
        flex-shrink: 0;
    }
    .jtb-btn-link {
        background: none;
        border: none;
        color: var(--jtb-accent);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .jtb-btn-link:hover {
        background: rgba(124,58,237,0.1);
    }

    /* Form Fields */
    .jtb-field-group {
        margin-bottom: 0;
    }
    .jtb-field-group label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--jtb-text-muted, #888);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .jtb-field-group label svg {
        opacity: 0.7;
    }
    .jtb-field-group textarea {
        width: 100%;
        padding: 16px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        color: #fff;
        font-size: 14px;
        font-family: inherit;
        line-height: 1.6;
        resize: vertical;
        transition: all 0.2s;
    }
    .jtb-field-group textarea::placeholder {
        color: rgba(255,255,255,0.3);
    }
    .jtb-field-group textarea:focus {
        outline: none;
        border-color: var(--jtb-accent, #7c3aed);
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }
    .prompt-tips {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
    }
    .prompt-tips .tip-item {
        font-size: 11px;
        color: var(--jtb-text-muted);
        background: rgba(255,255,255,0.03);
        padding: 6px 10px;
        border-radius: 6px;
    }

    /* Select Wrapper */
    .jtb-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .jtb-select-wrapper {
        position: relative;
    }
    .jtb-select-wrapper select {
        width: 100%;
        padding: 14px 16px;
        padding-right: 40px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        color: #fff;
        font-size: 14px;
        font-family: inherit;
        cursor: pointer;
        appearance: none;
        transition: all 0.2s;
    }
    .jtb-select-wrapper select:focus {
        outline: none;
        border-color: var(--jtb-accent);
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }
    .jtb-select-wrapper::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid rgba(255,255,255,0.5);
        pointer-events: none;
    }

    /* AI Provider/Model Fields (matching ai-panel.css) */
    .jtb-ai-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .jtb-ai-field label {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.5);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .jtb-ai-field select {
        width: 100%;
        padding: 10px 12px;
        padding-right: 40px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        font-size: 14px;
        color: #cdd6f4;
        background: rgba(0,0,0,0.3);
        cursor: pointer;
        transition: all 0.15s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a6adc8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        font-family: inherit;
    }
    .jtb-ai-field select:focus {
        outline: none;
        border-color: var(--jtb-accent, #7c3aed);
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }

    /* Style Preview Bar */
    .style-preview-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
        padding: 12px 16px;
        background: rgba(0,0,0,0.2);
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.04);
    }
    .style-colors {
        display: flex;
        gap: 6px;
    }
    .style-colors .color-dot {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 2px solid rgba(255,255,255,0.1);
        transition: transform 0.2s;
    }
    .style-colors .color-dot:hover {
        transform: scale(1.1);
    }
    .style-label {
        font-size: 12px;
        color: var(--jtb-text-muted);
    }

    /* Pages Grid */
    .jtb-pages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
        width: 100%;
    }
    .jtb-page-card {
        position: relative;
        cursor: pointer;
        width: 100%;
    }
    .jtb-page-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .jtb-page-card .card-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px 16px;
        min-height: 110px;
        background: rgba(0,0,0,0.2);
        border: 2px solid rgba(255,255,255,0.06);
        border-radius: 12px;
        transition: all 0.2s;
    }
    .jtb-page-card:hover .card-content {
        border-color: rgba(124,58,237,0.3);
        background: rgba(124,58,237,0.05);
    }
    .jtb-page-card input:checked + .card-content {
        border-color: var(--jtb-accent);
        background: rgba(124,58,237,0.1);
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }
    .jtb-page-card .card-icon {
        font-size: 22px;
    }
    .jtb-page-card .card-name {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
    }
    .jtb-page-card .card-desc {
        font-size: 10px;
        color: var(--jtb-text-muted);
        text-align: center;
    }
    .jtb-page-card .card-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--jtb-accent);
        border-radius: 50%;
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.2s;
    }
    .jtb-page-card .card-check svg {
        color: #fff;
    }
    .jtb-page-card input:checked ~ .card-check {
        opacity: 1;
        transform: scale(1);
    }

    /* Pages Summary */
    .pages-summary {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 16px;
        padding: 12px 16px;
        background: linear-gradient(90deg, rgba(124,58,237,0.1), rgba(59,130,246,0.1));
        border-radius: 10px;
        border: 1px solid rgba(124,58,237,0.2);
    }
    .pages-summary .summary-icon {
        font-size: 16px;
    }
    .pages-summary .summary-text {
        font-size: 12px;
        color: var(--jtb-text-muted);
    }
    .pages-summary .summary-text strong {
        color: #fff;
    }

    /* Legacy checkbox style for fallback */
    .jtb-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: var(--jtb-bg-tertiary, #16162a);
        border-radius: 6px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.15s;
        border: 1px solid transparent;
    }
    .jtb-checkbox-item:hover {
        background: rgba(124,58,237,0.15);
        border-color: rgba(124,58,237,0.3);
    }
    .jtb-checkbox-item input {
        accent-color: var(--jtb-accent, #7c3aed);
    }

    /* Progress Animation */
    .jtb-ai-progress {
        text-align: center;
        padding: 60px 20px;
    }
    .jtb-ai-progress h3 {
        margin: 0 0 8px;
        font-size: 20px;
        color: var(--jtb-text);
    }
    .jtb-ai-progress p {
        color: var(--jtb-text-muted);
        margin: 0 0 30px;
    }
    .jtb-ai-progress-animation {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 30px;
    }
    .jtb-ai-orb {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        margin: -20px 0 0 -20px;
        background: linear-gradient(135deg, #7c3aed, #3b82f6);
        border-radius: 50%;
        animation: orb-pulse 2s ease-in-out infinite;
    }
    .jtb-ai-rings .ring {
        position: absolute;
        top: 50%;
        left: 50%;
        border: 2px solid rgba(124,58,237,0.3);
        border-radius: 50%;
        animation: ring-expand 2s ease-out infinite;
    }
    .ring-1 { width: 60px; height: 60px; margin: -30px 0 0 -30px; animation-delay: 0s; }
    .ring-2 { width: 80px; height: 80px; margin: -40px 0 0 -40px; animation-delay: 0.4s; }
    .ring-3 { width: 100px; height: 100px; margin: -50px 0 0 -50px; animation-delay: 0.8s; }
    @keyframes orb-pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(124,58,237,0.5); }
        50% { transform: scale(1.1); box-shadow: 0 0 40px rgba(124,58,237,0.8); }
    }
    @keyframes ring-expand {
        0% { transform: scale(0.5); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .jtb-ai-progress-steps {
        display: flex;
        justify-content: center;
        gap: 24px;
    }
    .jtb-ai-progress-steps .step {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--jtb-text-muted);
        transition: color 0.3s;
    }
    .jtb-ai-progress-steps .step.active { color: var(--jtb-accent); }
    .jtb-ai-progress-steps .step.done { color: #10b981; }
    .jtb-ai-progress-steps .step.done .icon { content: '✓'; }

    /* Stats Bar */
    .jtb-ai-stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        padding: 16px;
        background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(59,130,246,0.1));
        border-radius: 12px;
        margin-bottom: 20px;
        border: 1px solid rgba(124,58,237,0.2);
    }
    .jtb-ai-stats-bar .stat {
        text-align: center;
    }
    .jtb-ai-stats-bar .stat-value {
        display: block;
        font-size: 28px;
        font-weight: 700;
        color: var(--jtb-accent);
        line-height: 1.2;
    }
    .jtb-ai-stats-bar .stat-label {
        font-size: 11px;
        color: var(--jtb-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Preview Tabs */
    .jtb-ai-preview-tabs {
        display: flex;
        gap: 4px;
        padding: 4px;
        background: var(--jtb-bg-tertiary);
        border-radius: 10px;
        margin-bottom: 16px;
    }
    .jtb-ai-preview-tabs .tab {
        flex: 1;
        padding: 10px 12px;
        background: transparent;
        border: none;
        border-radius: 8px;
        color: var(--jtb-text-muted);
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .jtb-ai-preview-tabs .tab:hover {
        background: rgba(255,255,255,0.05);
        color: var(--jtb-text);
    }
    .jtb-ai-preview-tabs .tab.active {
        background: var(--jtb-accent);
        color: #fff;
    }

    /* Preview Panels */
    .jtb-ai-preview-content {
        background: var(--jtb-bg-tertiary);
        border-radius: 12px;
        min-height: 300px;
        overflow: hidden;
    }
    .preview-panel {
        display: none;
        padding: 20px;
    }
    .preview-panel.active {
        display: block;
    }

    /* Overview Grid */
    .jtb-ai-overview-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .overview-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: var(--jtb-bg-secondary);
        border-radius: 10px;
        border: 1px solid var(--jtb-border);
    }
    .overview-card .card-icon {
        font-size: 24px;
    }
    .overview-card .card-info h4 {
        margin: 0 0 4px;
        font-size: 14px;
        color: var(--jtb-text);
    }
    .overview-card .card-info p {
        margin: 0;
        font-size: 12px;
        color: var(--jtb-text-muted);
    }
    .overview-card .card-status {
        margin-left: auto;
        font-size: 18px;
    }
    .overview-pages {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 8px;
    }
    .overview-pages .page-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: var(--jtb-bg-secondary);
        border-radius: 8px;
        border: 1px solid var(--jtb-border);
    }
    .overview-pages .page-card .page-icon { font-size: 16px; }
    .overview-pages .page-card .page-name { font-size: 13px; color: var(--jtb-text); flex: 1; }
    .overview-pages .page-card .page-sections { font-size: 11px; color: var(--jtb-text-muted); }

    /* Section Header */
    .preview-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .preview-section-header h4 {
        margin: 0;
        font-size: 14px;
        color: var(--jtb-text);
    }
    .preview-section-header .badge {
        padding: 4px 10px;
        background: var(--jtb-accent);
        color: #fff;
        font-size: 11px;
        border-radius: 20px;
    }

    /* Preview Render */
    .preview-render {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        min-height: 100px;
        color: #333;
    }
    .preview-structure {
        font-family: monospace;
        font-size: 12px;
        background: rgba(0,0,0,0.3);
        border-radius: 8px;
        padding: 16px;
        max-height: 200px;
        overflow: auto;
    }
    .preview-structure .module-item {
        padding: 6px 12px;
        margin: 4px 0;
        background: rgba(124,58,237,0.1);
        border-radius: 4px;
        border-left: 3px solid var(--jtb-accent);
    }

    /* Pages Nav */
    .pages-nav {
        display: flex;
        gap: 8px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--jtb-border);
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .pages-nav .page-tab {
        padding: 8px 16px;
        background: var(--jtb-bg-secondary);
        border: 1px solid var(--jtb-border);
        border-radius: 6px;
        color: var(--jtb-text-muted);
        font-size: 12px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .pages-nav .page-tab:hover {
        border-color: var(--jtb-accent);
        color: var(--jtb-text);
    }
    .pages-nav .page-tab.active {
        background: var(--jtb-accent);
        border-color: var(--jtb-accent);
        color: #fff;
    }

    /* Theme Preview */
    .theme-preview {
        display: grid;
        gap: 24px;
    }
    .theme-section h4 {
        margin: 0 0 12px;
        font-size: 13px;
        color: var(--jtb-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .color-swatches {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .color-swatch {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .color-swatch .swatch {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        border: 2px solid rgba(255,255,255,0.1);
    }
    .color-swatch .label {
        font-size: 10px;
        color: var(--jtb-text-muted);
        text-transform: capitalize;
    }
    .color-swatch .value {
        font-size: 10px;
        color: var(--jtb-text-muted);
        font-family: monospace;
    }
    .typography-preview .font-item {
        padding: 12px;
        background: var(--jtb-bg-secondary);
        border-radius: 8px;
        margin-bottom: 8px;
    }
    .typography-preview .font-label {
        font-size: 11px;
        color: var(--jtb-text-muted);
        margin-bottom: 4px;
    }
    .typography-preview .font-sample {
        font-size: 18px;
        color: var(--jtb-text);
    }
    .typography-preview .font-sizes {
        display: flex;
        gap: 12px;
        margin-top: 6px;
    }
    .typography-preview .font-sizes span {
        font-size: 11px;
        color: var(--jtb-text-muted);
        background: rgba(255,255,255,0.05);
        padding: 2px 8px;
        border-radius: 4px;
    }
    .spacing-preview {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .spacing-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 10px;
        background: var(--jtb-bg-secondary);
        border-radius: 8px;
        min-width: 120px;
    }
    .spacing-item .label {
        font-size: 11px;
        color: var(--jtb-text-muted);
    }
    .spacing-item .value {
        font-size: 14px;
        color: var(--jtb-text);
        font-family: monospace;
    }

    /* Full Preview Banner */
    .full-preview-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        background: linear-gradient(135deg, rgba(124,58,237,0.15) 0%, rgba(59,130,246,0.15) 100%);
        border: 1px solid rgba(124,58,237,0.3);
        border-radius: 16px;
        margin-bottom: 20px;
    }
    .preview-banner-content {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .preview-banner-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7c3aed, #3b82f6);
        border-radius: 14px;
        color: #fff;
    }
    .preview-banner-text h4 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 700;
        color: #fff;
    }
    .preview-banner-text p {
        margin: 0;
        font-size: 13px;
        color: var(--jtb-text-muted);
    }
    .jtb-btn-preview {
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
        color: #fff !important;
        padding: 14px 24px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(124,58,237,0.4);
        transition: all 0.3s !important;
    }
    .jtb-btn-preview:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(124,58,237,0.5);
    }

    /* Overview Live Preview */
    .overview-live-preview {
        background: rgba(0,0,0,0.2);
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .preview-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .preview-device-buttons {
        display: flex;
        gap: 6px;
    }
    .device-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: var(--jtb-text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }
    .device-btn:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .device-btn.active {
        background: var(--jtb-accent);
        border-color: var(--jtb-accent);
        color: #fff;
    }
    .preview-page-selector select {
        padding: 10px 16px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #fff;
        font-size: 13px;
        cursor: pointer;
    }
    .preview-frame-wrapper {
        position: relative;
        width: 100%;
        height: 400px;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s;
    }
    .preview-frame-wrapper.device-tablet {
        width: 768px;
        max-width: 100%;
        margin: 0 auto;
    }
    .preview-frame-wrapper.device-mobile {
        width: 375px;
        max-width: 100%;
        margin: 0 auto;
    }
    .preview-frame-wrapper iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    .preview-frame-loading {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: #fff;
        z-index: 10;
    }
    .preview-frame-loading .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e5e7eb;
        border-top-color: #7c3aed;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    .preview-frame-loading span {
        font-size: 13px;
        color: #6b7280;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Legacy preview styles */
    .preview-iframe-container {
        position: relative;
        width: 100%;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .preview-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 10px 14px;
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 8px;
    }
    .preview-label .label-text {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #10b981;
        font-weight: 600;
    }
    .preview-label .label-info {
        font-size: 11px;
        color: var(--jtb-text-muted);
    }

    /* JSON View */
    .json-toolbar {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }
    .json-view {
        background: #0d1117;
        color: #c9d1d9;
        padding: 16px;
        border-radius: 8px;
        font-size: 11px;
        line-height: 1.5;
        max-height: 400px;
        overflow: auto;
        margin: 0;
    }

    /* Buttons */
    .jtb-btn-sm {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    .jtb-btn-glow {
        position: relative;
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 50%, #3b82f6 100%) !important;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        transition: all 0.3s !important;
    }
    .jtb-btn-glow:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
    }
    .jtb-btn-glow svg {
        animation: pulse-subtle 2s ease-in-out infinite;
    }
    @keyframes pulse-subtle {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .jtb-btn-success {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    .jtb-btn-success:hover {
        background: linear-gradient(135deg, #059669, #047857) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }
    .jtb-btn-secondary {
        background: rgba(255,255,255,0.05) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
    }
    .jtb-btn-secondary:hover {
        background: rgba(255,255,255,0.1) !important;
    }
    #jtb-ai-website-modal .jtb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s;
    }
    #jtb-ai-website-modal .jtb-btn svg {
        flex-shrink: 0;
    }
    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--jtb-text-muted);
    }

    /* Responsive for modal */
    @media (max-width: 768px) {
        .jtb-pages-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .jtb-field-row {
            grid-template-columns: 1fr;
        }
        #jtb-ai-website-modal .jtb-modal-footer {
            flex-direction: column;
            gap: 12px;
        }
        #jtb-ai-website-modal .jtb-modal-footer .footer-left,
        #jtb-ai-website-modal .jtb-modal-footer .footer-right {
            width: 100%;
            justify-content: center;
        }
    }
    </style>

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
    // AI Provider/Model management (mirrors ai-panel.js JTB_AI provider system)
    const WB_AI = {
        aiProvider: 'anthropic',
        aiModel: 'claude-opus-4-5-20251101',

        availableProviders: {
            'anthropic': {
                name: 'Anthropic Claude',
                default: 'claude-opus-4-5-20251101',
                models: {
                    'claude-opus-4-5-20251101': 'Claude Opus 4.5 (Best for Themes)',
                    'claude-sonnet-4-5-20250929': 'Claude Sonnet 4.5 (Best Balance)',
                    'claude-sonnet-4-20250514': 'Claude Sonnet 4',
                    'claude-haiku-4-5-20251001': 'Claude Haiku 4.5 (Fastest)'
                }
            },
            'openai': {
                name: 'OpenAI',
                default: 'gpt-4o',
                models: {
                    'gpt-5.2': 'GPT-5.2 (Flagship Thinking)',
                    'gpt-5': 'GPT-5 (General/Agentic)',
                    'gpt-4.1': 'GPT-4.1 (Coding/JSON)',
                    'gpt-4.1-mini': 'GPT-4.1 Mini (Fast)',
                    'gpt-4o': 'GPT-4o (Legacy)',
                    'gpt-4o-mini': 'GPT-4o Mini (Legacy Fast)'
                }
            },

            'deepseek': {
                name: 'DeepSeek',
                default: 'deepseek-v3',
                models: {
                    'deepseek-v3': 'DeepSeek V3 (Fast & Smart)',
                    'deepseek-r1': 'DeepSeek R1 (Reasoning)',
                    'deepseek-coder-v3': 'DeepSeek Coder V3'
                }
            }
        },

        initProviderSelection() {
            const providerSelect = document.getElementById('ai-provider-select');
            const modelSelect = document.getElementById('ai-model-select');
            if (!providerSelect || !modelSelect) return;

            providerSelect.value = this.aiProvider;
            this.updateModelOptions(this.aiProvider);
            modelSelect.value = this.aiModel;

            modelSelect.addEventListener('change', () => {
                this.aiModel = modelSelect.value;
                console.log('[WB_AI] Model changed to:', this.aiModel);
            });
        },

        handleProviderChange(provider) {
            this.aiProvider = provider;
            this.updateModelOptions(provider);

            const providerConfig = this.availableProviders[provider];
            if (providerConfig) {
                this.aiModel = providerConfig.default;
                const modelSelect = document.getElementById('ai-model-select');
                if (modelSelect) modelSelect.value = this.aiModel;
            }
            console.log('[WB_AI] Provider changed to:', provider, 'Model:', this.aiModel);
        },

        updateModelOptions(provider) {
            const modelSelect = document.getElementById('ai-model-select');
            if (!modelSelect) return;

            const providerConfig = this.availableProviders[provider];
            if (!providerConfig) return;

            modelSelect.innerHTML = '';
            Object.entries(providerConfig.models).forEach(([value, label]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;
                if (value === providerConfig.default) option.selected = true;
                modelSelect.appendChild(option);
            });
        }
    };
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
            generationStartTime: null,
            extractedBrandKit: null,
            _currentVariants: null,

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

                // AI Modal events
                document.querySelectorAll('[data-action="close-ai-modal"]').forEach(btn => {
                    btn.addEventListener('click', () => this.hideAIModal());
                });
                document.querySelector('.jtb-modal-backdrop')?.addEventListener('click', () => this.hideAIModal());
                document.querySelector('[data-action="generate-website"]')?.addEventListener('click', () => this.generateWebsite());
                document.querySelector('[data-action="regenerate-website"]')?.addEventListener('click', () => this.resetAIModal());
                document.querySelector('[data-action="apply-website"]')?.addEventListener('click', () => this.applyGeneratedWebsite());
                document.querySelector('[data-action="back-to-form"]')?.addEventListener('click', () => this.resetAIModal());

                // NEW: Brand Kit extraction button
                const brandBtn = document.getElementById('extract-brand-btn'); if (brandBtn && !brandBtn._bound) { brandBtn.addEventListener('click', () => this.extractBrandKit()); brandBtn._bound = true; }

                // NEW: Translate button
                document.querySelector('[data-action="translate-website"]')?.addEventListener('click', () => this.translateWebsite());

                // NEW: A/B Variants button
                document.querySelector('[data-action="generate-variants"]')?.addEventListener('click', () => this.generateHeroVariants());

                // NEW: Generation mode card selection
                document.querySelectorAll('.mode-card').forEach(card => {
                    card.addEventListener('click', () => {
                        document.querySelectorAll('.mode-card').forEach(c => {
                            c.style.borderColor = 'rgba(255,255,255,0.1)';
                        });
                        card.style.borderColor = 'rgba(99,102,241,0.5)';
                    });
                });

                // AI Provider & Model - initialize
                WB_AI.initProviderSelection();
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

            // AI Website Generation Methods - Professional Version
            generatedWebsite: null,
            generationStartTime: null,

            // Style palettes for preview
            stylePalettes: {
                modern: { primary: '#7c3aed', secondary: '#3b82f6', accent: '#10b981', label: 'Modern color palette' },
                minimal: { primary: '#18181b', secondary: '#71717a', accent: '#a1a1aa', label: 'Minimal grayscale' },
                bold: { primary: '#dc2626', secondary: '#f59e0b', accent: '#7c3aed', label: 'Bold & vibrant' },
                elegant: { primary: '#0d9488', secondary: '#1e3a5f', accent: '#d97706', label: 'Elegant & refined' },
                playful: { primary: '#ec4899', secondary: '#8b5cf6', accent: '#06b6d4', label: 'Playful & fun' },
                corporate: { primary: '#1e40af', secondary: '#374151', accent: '#059669', label: 'Corporate professional' },
                dark: { primary: '#6366f1', secondary: '#8b5cf6', accent: '#22d3ee', label: 'Dark & dramatic' }
            },

            showAIModal() {
                console.log('[WB] showAIModal called');
                const modal = document.getElementById('jtb-ai-website-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    this.resetAIModal();
                    this.initAIFormHandlers();
                    this.updatePagesCount();
                    this.updateStylePreview();
                }
            },

            initAIFormHandlers() {
                // Style change - update preview
                const styleSelect = document.getElementById('ai-website-style');
                if (styleSelect && !styleSelect._bound) {
                    styleSelect.addEventListener('change', () => this.updateStylePreview());
                    styleSelect._bound = true;
                }

                // Pages checkboxes - update count
                document.querySelectorAll('input[name="pages"]').forEach(cb => {
                    if (!cb._bound) {
                        cb.addEventListener('change', () => this.updatePagesCount());
                        cb._bound = true;
                    }
                });

                // Toggle all pages button
                const toggleBtn = document.getElementById('toggle-all-pages');
                if (toggleBtn && !toggleBtn._bound) {
                    toggleBtn.addEventListener('click', () => this.toggleAllPages());
                    toggleBtn._bound = true;
                }
            },

            updateStylePreview() {
                const style = document.getElementById('ai-website-style')?.value || 'modern';
                const palette = this.stylePalettes[style] || this.stylePalettes.modern;
                const bar = document.getElementById('style-preview-bar');
                if (bar) {
                    bar.querySelector('.color-dot[data-color="primary"]').style.background = palette.primary;
                    bar.querySelector('.color-dot[data-color="secondary"]').style.background = palette.secondary;
                    bar.querySelector('.color-dot[data-color="accent"]').style.background = palette.accent;
                    bar.querySelector('.style-label').textContent = palette.label;
                }
            },

            updatePagesCount() {
                const checked = document.querySelectorAll('input[name="pages"]:checked').length;
                const countEl = document.getElementById('selected-pages-count');
                const toggleBtn = document.getElementById('toggle-all-pages');
                if (countEl) {
                    countEl.textContent = checked;
                }
                if (toggleBtn) {
                    const total = document.querySelectorAll('input[name="pages"]').length;
                    toggleBtn.textContent = checked === total ? 'Deselect All' : 'Select All';
                }
            },

            toggleAllPages() {
                const checkboxes = document.querySelectorAll('input[name="pages"]');
                const allChecked = document.querySelectorAll('input[name="pages"]:checked').length === checkboxes.length;
                checkboxes.forEach(cb => cb.checked = !allChecked);
                this.updatePagesCount();
            },

            hideAIModal() {
                document.getElementById('jtb-ai-website-modal').style.display = 'none';
            },

            resetAIModal() {
                // Show form, hide other states
                document.getElementById('ai-form-step').style.display = 'block';
                document.getElementById('ai-website-progress').style.display = 'none';
                document.getElementById('ai-website-result').style.display = 'none';

                // Show/hide buttons
                document.getElementById('ai-website-generate-btn').style.display = 'inline-flex';
                document.getElementById('ai-website-regenerate-btn').style.display = 'none';
                document.getElementById('ai-website-apply-btn').style.display = 'none';
                document.getElementById('ai-back-to-form-btn').style.display = 'none';

                // NEW: Hide translate and variants buttons
                const translateBtn = document.querySelector('[data-action="translate-website"]');
                const variantsBtn = document.querySelector('[data-action="generate-variants"]');
                if (translateBtn) translateBtn.style.display = 'none';
                if (variantsBtn) variantsBtn.style.display = 'none';

                // Reset title
                document.getElementById('ai-modal-title').textContent = 'Generate Entire Website with AI';

                // Remove large preview class
                document.querySelector('.jtb-ai-modal-large')?.classList.remove('show-preview');

                // Reset progress steps
                document.querySelectorAll('.jtb-ai-progress-steps .step').forEach(s => {
                    s.classList.remove('active', 'done');
                    s.querySelector('.icon').textContent = '○';
                });

                // NEW: Reset brand kit data
                this.extractedBrandKit = null;
                const brandResult = document.getElementById('brand-kit-result');
                if (brandResult) {
                    brandResult.innerHTML = '';
                    brandResult.style.display = 'none';
                }
                // Reset brand URL input
                const brandUrlInput = document.getElementById('ai-brand-url');
                if (brandUrlInput) brandUrlInput.value = '';
                // Reset competitor URL input
                const competitorUrlInput = document.getElementById('ai-competitor-url');
                if (competitorUrlInput) competitorUrlInput.value = '';
                // Reset language selector to English
                const langSelect = document.getElementById('ai-website-language');
                if (langSelect) langSelect.value = 'en';
                // Reset generation mode to standard
                const standardMode = document.querySelector('input[name="gen_mode"][value="standard"]');
                if (standardMode) standardMode.checked = true;

                // NEW: Reset variants overlay if exists
                document.getElementById('jtb-variants-overlay')?.remove();
                this._currentVariants = null;
            },

            setProgressStep(stepName, status = 'active') {
                const steps = ['connect', 'generate', 'process', 'complete'];
                const stepIndex = steps.indexOf(stepName);

                document.querySelectorAll('.jtb-ai-progress-steps .step').forEach((el, i) => {
                    const step = el.dataset.step;
                    if (i < stepIndex) {
                        el.classList.remove('active');
                        el.classList.add('done');
                        el.querySelector('.icon').textContent = '✓';
                    } else if (step === stepName) {
                        el.classList.add(status);
                        el.querySelector('.icon').textContent = status === 'done' ? '✓' : '●';
                    } else {
                        el.classList.remove('active', 'done');
                        el.querySelector('.icon').textContent = '○';
                    }
                });
            },

            async generateWebsite() {
                const prompt = document.getElementById('ai-website-prompt').value.trim();
                if (!prompt) {
                    alert('Please describe your business first');
                    return;
                }

                const industry = document.getElementById('ai-website-industry').value;
                const style = document.getElementById('ai-website-style').value;

                // Get selected pages
                const pages = [];
                document.querySelectorAll('input[name="pages"]:checked').forEach(cb => {
                    pages.push(cb.value);
                });

                if (pages.length === 0) {
                    alert('Please select at least one page to generate');
                    return;
                }

                // NEW: Get competitor URL (8.2)
                const competitorUrl = document.getElementById('ai-competitor-url')?.value?.trim() || '';

                // NEW: Get generation mode (8.6) - standard or progressive
                const genModeRadio = document.querySelector('input[name="gen_mode"]:checked');
                const genMode = genModeRadio ? genModeRadio.value : 'standard';

                // NEW: Determine action based on competitor URL and generation mode
                let action = 'generate';
                if (competitorUrl) {
                    action = 'competitor';
                } else if (genMode === 'progressive') {
                    action = 'progressive';
                }

                // Show progress
                document.getElementById('ai-form-step').style.display = 'none';
                document.getElementById('ai-website-progress').style.display = 'block';
                document.getElementById('ai-website-result').style.display = 'none';
                document.getElementById('ai-website-generate-btn').style.display = 'none';
                document.getElementById('ai-back-to-form-btn').style.display = 'inline-flex';

                const statusEl = document.getElementById('ai-website-status');
                this.generationStartTime = Date.now();

                try {
                    // Get selected provider & model from WB_AI state
                    const selectedProvider = WB_AI.aiProvider;
                    const selectedModel = WB_AI.aiModel;
                    const providerConfig = WB_AI.availableProviders[selectedProvider];
                    const providerLabel = providerConfig ? providerConfig.name : selectedProvider;

                    // Step 1: Connect
                    this.setProgressStep('connect', 'active');
                    statusEl.textContent = `Connecting to ${providerLabel} (${selectedModel})...`;
                    await new Promise(r => setTimeout(r, 500));

                    // Step 2: Generate
                    this.setProgressStep('generate', 'active');

                    if (action === 'competitor') {
                        statusEl.textContent = `${providerLabel} is analyzing competitor & designing yours...`;
                    } else if (action === 'progressive') {
                        statusEl.textContent = `Stage 1/4: ${providerLabel} generating skeleton...`;
                    } else {
                        statusEl.textContent = `${providerLabel} is designing your website...`;
                    }

                    // Build request body
                    const requestBody = {
                        action: action,
                        prompt: prompt,
                        industry: industry,
                        style: style,
                        pages: pages,
                        provider: selectedProvider,
                        model: selectedModel
                    };

                    // Add competitor URL if present (8.2)
                    if (competitorUrl) {
                        requestBody.url = competitorUrl;
                    }

                    // Add brand kit data if extracted (8.1)
                    if (this.extractedBrandKit) {
                        requestBody.brand_kit = this.extractedBrandKit;
                        // Override style colors with brand kit colors (colors is object with primary/secondary/accent/all_extracted)
                        if (this.extractedBrandKit.colors) {
                            requestBody.brand_colors = this.extractedBrandKit.colors;
                        }
                        if (this.extractedBrandKit.fonts) {
                            requestBody.brand_fonts = this.extractedBrandKit.fonts;
                        }
                    }

                    const response = await fetch('/api/jtb/ai/generate-website', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify(requestBody)
                    });

                    const data = await response.json();

                    // Handle progressive mode stages display
                    if (action === 'progressive' && data.success && data.stages) {
                        // Progressive mode returns stages (each stage has: stage, label, description, website, time_ms)
                        this.setProgressStep('process', 'active');
                        for (let i = 0; i < data.stages.length; i++) {
                            const stage = data.stages[i];
                            statusEl.textContent = `Stage ${stage.stage || (i + 1)}/${data.stages.length}: ${stage.label || stage.name || 'Processing'}...`;
                            await new Promise(r => setTimeout(r, 400));
                        }

                        this.generatedWebsite = data.final_website;
                        if (!this.generatedWebsite) {
                            // Fallback: use the last stage's website if final_website is null
                            const lastStage = data.stages[data.stages.length - 1];
                            this.generatedWebsite = lastStage?.website || {};
                        }
                        this.generatedWebsite._stats = data.stats || {};
                        this.generatedWebsite._stages = data.stages;

                    } else if (data.success && data.website) {
                        // Standard/competitor mode
                        this.setProgressStep('process', 'active');
                        statusEl.textContent = 'Processing generated content...';
                        await new Promise(r => setTimeout(r, 300));

                        this.generatedWebsite = data.website;
                        this.generatedWebsite._stats = data.stats || {};

                        // Save competitor analysis if present (8.2)
                        if (data.competitor_analysis) {
                            this.generatedWebsite._competitor_analysis = data.competitor_analysis;
                        }
                    } else {
                        throw new Error(data.error || 'Generation failed');
                    }

                    // DEBUG: Log full JSON to console for export
                    console.log('=== FULL GENERATED WEBSITE JSON ===');
                    console.log(JSON.stringify(this.generatedWebsite, null, 2));

                    // Step 4: Complete
                    this.setProgressStep('complete', 'done');
                    statusEl.textContent = 'Website generated successfully!';
                    await new Promise(r => setTimeout(r, 500));

                    // Show results
                    this.showGeneratedPreview();

                    // NEW: Auto-translate if language is selected (8.5)
                    const selectedLang = document.getElementById('ai-website-language')?.value;
                    if (selectedLang && selectedLang !== 'en' && selectedLang !== '') {
                        this.translateWebsite(selectedLang);
                    }

                } catch (err) {
                    console.error('AI Generation error:', err);
                    statusEl.textContent = 'Error: ' + err.message;
                    document.getElementById('ai-website-generate-btn').style.display = 'inline-flex';

                    // Keep progress visible so user can see error
                    setTimeout(() => {
                        document.getElementById('ai-form-step').style.display = 'block';
                        document.getElementById('ai-website-progress').style.display = 'none';
                    }, 3000);
                }
            },

            showGeneratedPreview() {
                const website = this.generatedWebsite;
                const elapsedTime = ((Date.now() - this.generationStartTime) / 1000).toFixed(1);

                // Calculate stats
                let totalSections = 0;
                let totalModules = 0;
                let totalPages = 0;

                const countModules = (sections) => {
                    if (!sections) return { sections: 0, modules: 0 };
                    let s = sections.length;
                    let m = 0;
                    const countChildren = (items) => {
                        items.forEach(item => {
                            if (item.type && !['section', 'row', 'column'].includes(item.type)) m++;
                            if (item.children) countChildren(item.children);
                        });
                    };
                    countChildren(sections);
                    return { sections: s, modules: m };
                };

                if (website.header?.sections) {
                    const hs = countModules(website.header.sections);
                    totalSections += hs.sections;
                    totalModules += hs.modules;
                }
                if (website.footer?.sections) {
                    const fs = countModules(website.footer.sections);
                    totalSections += fs.sections;
                    totalModules += fs.modules;
                }
                if (website.pages) {
                    for (const page of Object.values(website.pages)) {
                        totalPages++;
                        if (page.sections) {
                            const ps = countModules(page.sections);
                            totalSections += ps.sections;
                            totalModules += ps.modules;
                        }
                    }
                }

                // Update stats
                document.getElementById('ai-stat-sections').textContent = totalSections;
                document.getElementById('ai-stat-modules').textContent = totalModules;
                document.getElementById('ai-stat-pages').textContent = totalPages;
                document.getElementById('ai-stat-time').textContent = elapsedTime + 's';

                // Update overview
                const headerSections = website.header?.sections?.length || 0;
                const footerSections = website.footer?.sections?.length || 0;
                document.getElementById('overview-header-info').textContent = `${headerSections} section(s)`;
                document.getElementById('overview-footer-info').textContent = `${footerSections} section(s)`;

                // Build pages list
                const pagesHtml = [];
                if (website.pages) {
                    for (const [slug, page] of Object.entries(website.pages)) {
                        const ps = page.sections?.length || 0;
                        pagesHtml.push(`
                            <div class="page-card">
                                <span class="page-icon">📄</span>
                                <span class="page-name">${page.title || slug}</span>
                                <span class="page-sections">${ps}s</span>
                            </div>
                        `);
                    }
                }
                document.getElementById('overview-pages-list').innerHTML = pagesHtml.join('');

                // Header preview with live render
                document.getElementById('header-sections-count').textContent = `${headerSections} section(s)`;
                this.renderLivePreview('header', website.header?.sections || []);
                document.getElementById('preview-header-structure').innerHTML = this.renderStructureTree(website.header?.sections || []);

                // Footer preview with live render
                document.getElementById('footer-sections-count').textContent = `${footerSections} section(s)`;
                this.renderLivePreview('footer', website.footer?.sections || []);
                document.getElementById('preview-footer-structure').innerHTML = this.renderStructureTree(website.footer?.sections || []);

                // Pages nav
                const pagesNav = [];
                if (website.pages) {
                    for (const [slug, page] of Object.entries(website.pages)) {
                        pagesNav.push(`<button class="page-tab" data-page="${slug}">${page.title || slug}</button>`);
                    }
                }
                document.getElementById('pages-nav').innerHTML = pagesNav.join('');

                // Bind page tabs
                document.querySelectorAll('#pages-nav .page-tab').forEach(tab => {
                    tab.addEventListener('click', () => {
                        document.querySelectorAll('#pages-nav .page-tab').forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');
                        this.showPagePreview(tab.dataset.page);
                    });
                });

                // Auto-select first page tab
                const firstPageTab = document.querySelector('#pages-nav .page-tab');
                if (firstPageTab) {
                    firstPageTab.classList.add('active');
                    this.showPagePreview(firstPageTab.dataset.page);
                }

                // Theme preview
                this.renderThemePreview();

                // JSON view
                document.getElementById('preview-json').textContent = JSON.stringify(website, null, 2);

                // Setup tabs
                this.setupPreviewTabs();

                // Setup page selector for overview preview
                this.setupOverviewPreview();

                // Show result with larger modal
                document.querySelector('.jtb-ai-modal-large')?.classList.add('show-preview');
                document.getElementById('ai-website-progress').style.display = 'none';
                document.getElementById('ai-website-result').style.display = 'block';
                document.getElementById('ai-website-regenerate-btn').style.display = 'inline-flex';
                document.getElementById('ai-website-apply-btn').style.display = 'inline-flex';

                // NEW: Show translate and variants buttons
                const translateBtn = document.querySelector('[data-action="translate-website"]');
                const variantsBtn = document.querySelector('[data-action="generate-variants"]');
                if (translateBtn) translateBtn.style.display = 'inline-flex';
                if (variantsBtn) variantsBtn.style.display = 'inline-flex';

                // Update title with translation badge if translated
                let title = 'Generated Website Preview';
                if (website._translated_to) {
                    title += ` (${website._translated_to})`;
                }
                if (website._competitor_analysis) {
                    title = 'Competitor-Inspired Website Preview';
                }
                if (website._stages) {
                    title = 'Progressive Website Preview';
                }
                document.getElementById('ai-modal-title').textContent = title;

                // Render the overview live preview
                this.renderOverviewPreview('home');
            },

            // Setup overview preview controls
            setupOverviewPreview() {
                const website = this.generatedWebsite;

                // Populate page selector
                const select = document.getElementById('preview-page-select');
                if (select && website.pages) {
                    select.innerHTML = '';
                    for (const [slug, page] of Object.entries(website.pages)) {
                        const option = document.createElement('option');
                        option.value = slug;
                        option.textContent = page.title || slug;
                        select.appendChild(option);
                    }

                    // Bind change event
                    select.addEventListener('change', () => {
                        this.renderOverviewPreview(select.value);
                    });
                }

                // Bind device buttons
                document.querySelectorAll('.preview-device-buttons .device-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.preview-device-buttons .device-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');

                        const wrapper = document.getElementById('preview-frame-wrapper');
                        wrapper.classList.remove('device-tablet', 'device-mobile');
                        if (btn.dataset.device === 'tablet') wrapper.classList.add('device-tablet');
                        if (btn.dataset.device === 'mobile') wrapper.classList.add('device-mobile');
                    });
                });

                // Bind full preview button
                document.getElementById('open-full-preview-btn')?.addEventListener('click', () => {
                    this.openFullPreview();
                });
            },

            // Render preview in overview iframe
            async renderOverviewPreview(pageSlug) {
                const website = this.generatedWebsite;
                const iframe = document.getElementById('overview-preview-iframe');
                const loading = document.getElementById('preview-loading');

                if (!iframe || !website) return;

                // Show loading
                loading.style.display = 'flex';

                // Combine header + page content + footer
                const allSections = [
                    ...(website.header?.sections || []),
                    ...(website.pages?.[pageSlug]?.sections || []),
                    ...(website.footer?.sections || [])
                ];

                try {
                    const response = await fetch('/api/jtb/render', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            content: { version: '1.0', content: allSections }
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.html) {
                        const iframeHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: #fff; color: #333; }
        img { max-width: 100%; height: auto; }
    </style>
    ${data.css ? '<style>' + data.css + '</style>' : ''}
</head>
<body>${data.html}</body>
</html>`;

                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(iframeHtml);
                        iframeDoc.close();

                        // Hide loading after iframe loads
                        iframe.onload = () => {
                            loading.style.display = 'none';
                        };

                        // Fallback hide loading
                        setTimeout(() => loading.style.display = 'none', 1000);
                    }
                } catch (err) {
                    console.error('Preview error:', err);
                    loading.innerHTML = '<span style="color:#ef4444;">Failed to load preview</span>';
                }
            },

            // Open full preview in new window
            async openFullPreview() {
                console.log('[PREVIEW] openFullPreview called');
                const website = this.generatedWebsite;
                console.log('[PREVIEW] generatedWebsite:', website);

                if (!website) {
                    alert('No generated website data found');
                    return;
                }

                // Get current selected page
                const pageSlug = document.getElementById('preview-page-select')?.value || 'home';
                const pageTitle = website.pages?.[pageSlug]?.title || 'Home';
                console.log('[PREVIEW] pageSlug:', pageSlug, 'pageTitle:', pageTitle);

                // Debug structure
                console.log('[PREVIEW] Header sections:', website.header?.sections);
                console.log('[PREVIEW] Page sections:', website.pages?.[pageSlug]?.sections);
                console.log('[PREVIEW] Footer sections:', website.footer?.sections);

                // Combine all sections
                const allSections = [
                    ...(website.header?.sections || []),
                    ...(website.pages?.[pageSlug]?.sections || []),
                    ...(website.footer?.sections || [])
                ];
                console.log('[PREVIEW] Combined sections count:', allSections.length);
                console.log('[PREVIEW] allSections:', JSON.stringify(allSections, null, 2).substring(0, 1000));

                if (allSections.length === 0) {
                    alert('No sections found to preview');
                    return;
                }

                // First render via API (before opening window)
                try {
                    const requestBody = {
                        content: { version: '1.0', content: allSections }
                    };
                    console.log('[PREVIEW] Request body:', JSON.stringify(requestBody).substring(0, 500));

                    const response = await fetch('/api/jtb/render', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify(requestBody)
                    });

                    console.log('[PREVIEW] Response status:', response.status);
                    const data = await response.json();
                    console.log('[PREVIEW] Render response:', data);

                    if (data.success && data.html) {
                        // Open window and write content immediately
                        const previewWindow = window.open('', '_blank');
                        if (!previewWindow) {
                            alert('Please allow popups to see the preview');
                            return;
                        }

                        const fullHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Preview - ${pageTitle}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: #fff; }
        img { max-width: 100%; height: auto; }
    </style>
    ${data.css ? '<style>' + data.css + '</style>' : ''}
</head>
<body>
${data.html}
</body>
</html>`;

                        previewWindow.document.open();
                        previewWindow.document.write(fullHtml);
                        previewWindow.document.close();
                    } else {
                        alert('Failed to render preview: ' + (data.error || 'Unknown error'));
                    }
                } catch (err) {
                    console.error('Preview error:', err);
                    alert('Failed to render preview: ' + err.message);
                }
            },

            // Live preview rendering via API
            async renderLivePreview(target, sections) {
                const containerId = `preview-${target}-render`;
                const container = document.getElementById(containerId);
                if (!container) return;

                // Show loading state
                container.innerHTML = `
                    <div class="preview-iframe-container">
                        <div class="preview-iframe-loading">
                            <div class="spinner"></div>
                        </div>
                    </div>
                `;

                try {
                    // Call render API
                    const response = await fetch('/api/jtb/render', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            content: {
                                version: '1.0',
                                content: sections
                            }
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.html) {
                        // Create iframe with rendered HTML
                        const iframeHtml = `
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <link rel="preconnect" href="https://fonts.googleapis.com">
                                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                                <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
                                <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css">
                                <style>
                                    * { box-sizing: border-box; }
                                    body {
                                        margin: 0;
                                        padding: 0;
                                        font-family: 'Inter', sans-serif;
                                        background: #fff;
                                        color: #333;
                                    }
                                    img { max-width: 100%; height: auto; }
                                </style>
                                ${data.css ? '<style>' + data.css + '</style>' : ''}
                            </head>
                            <body>
                                ${data.html}
                            </body>
                            </html>
                        `;

                        container.innerHTML = `
                            <div class="preview-label">
                                <span class="label-text">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Live Preview
                                </span>
                                <span class="label-info">${sections.length} section(s)</span>
                            </div>
                            <div class="preview-iframe-container">
                                <iframe id="iframe-${target}" sandbox="allow-same-origin"></iframe>
                            </div>
                        `;

                        // Write to iframe
                        const iframe = document.getElementById(`iframe-${target}`);
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        iframeDoc.open();
                        iframeDoc.write(iframeHtml);
                        iframeDoc.close();
                    } else {
                        container.innerHTML = `<div class="empty-state">Preview unavailable</div>`;
                    }
                } catch (err) {
                    console.error('Preview render error:', err);
                    container.innerHTML = `<div class="empty-state">Failed to render preview</div>`;
                }
            },

            renderStructureTree(sections) {
                if (!sections || !sections.length) return '<p class="empty-state">No sections</p>';

                const renderItem = (item, depth = 0) => {
                    const indent = '  '.repeat(depth);
                    const type = item.type || 'unknown';
                    const icon = type === 'section' ? '📦' : type === 'row' ? '⬜' : type === 'column' ? '▫️' : '🔹';
                    let html = `<div class="module-item" style="margin-left:${depth * 16}px">${icon} ${type}`;

                    // Show key attrs
                    if (item.attrs) {
                        if (item.attrs.text) html += ` <small>"${item.attrs.text.substring(0, 30)}..."</small>`;
                        if (item.attrs.title) html += ` <small>"${item.attrs.title.substring(0, 30)}..."</small>`;
                        if (item.attrs.columns) html += ` <small>[${item.attrs.columns}]</small>`;
                    }
                    html += '</div>';

                    if (item.children) {
                        item.children.forEach(child => {
                            html += renderItem(child, depth + 1);
                        });
                    }
                    return html;
                };

                return sections.map(s => renderItem(s, 0)).join('');
            },

            async showPagePreview(slug) {
                const page = this.generatedWebsite?.pages?.[slug];
                if (!page) return;

                const container = document.getElementById('pages-content');
                container.innerHTML = `
                    <div class="preview-section-header">
                        <h4>${page.title || slug}</h4>
                        <span class="badge">${page.sections?.length || 0} section(s)</span>
                    </div>
                    <div id="preview-page-render"></div>
                    <div class="preview-structure">${this.renderStructureTree(page.sections || [])}</div>
                `;

                // Render live preview for this page
                await this.renderLivePreview('page', page.sections || []);
            },

            renderThemePreview() {
                const settings = this.generatedWebsite?.theme_settings || {};
                const colors = settings.colors || {};
                const typography = settings.typography || {};
                const spacing = settings.spacing || {};

                // Colors
                const colorSwatches = [];
                for (const [name, value] of Object.entries(colors)) {
                    const label = name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    colorSwatches.push(`
                        <div class="color-swatch">
                            <div class="swatch" style="background:${value}"></div>
                            <span class="label">${label}</span>
                            <span class="value">${value}</span>
                        </div>
                    `);
                }
                document.getElementById('theme-colors').innerHTML = colorSwatches.join('') || '<p>No colors defined</p>';

                // Typography
                const fontItems = [];
                if (typography.heading_font) {
                    fontItems.push(`
                        <div class="font-item">
                            <div class="font-label">Heading Font</div>
                            <div class="font-sample" style="font-family:'${typography.heading_font}',sans-serif; font-weight:${typography.heading_weight || '700'}; font-size: 24px;">
                                ${typography.heading_font}
                            </div>
                            <div class="font-sizes">
                                ${typography.h1_size ? `<span>H1: ${typography.h1_size}</span>` : ''}
                                ${typography.h2_size ? `<span>H2: ${typography.h2_size}</span>` : ''}
                                ${typography.h3_size ? `<span>H3: ${typography.h3_size}</span>` : ''}
                            </div>
                        </div>
                    `);
                }
                if (typography.body_font) {
                    fontItems.push(`
                        <div class="font-item">
                            <div class="font-label">Body Font</div>
                            <div class="font-sample" style="font-family:'${typography.body_font}',sans-serif; font-weight:${typography.body_weight || '400'}; font-size: 16px;">
                                ${typography.body_font} — The quick brown fox jumps over the lazy dog.
                            </div>
                            ${typography.body_size ? `<div class="font-sizes"><span>Body: ${typography.body_size}</span></div>` : ''}
                        </div>
                    `);
                }
                document.getElementById('theme-typography').innerHTML = fontItems.join('') || '<p>No typography defined</p>';

                // Spacing (if exists)
                if (Object.keys(spacing).length > 0) {
                    const spacingHtml = Object.entries(spacing).map(([key, val]) => {
                        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        return `<div class="spacing-item"><span class="label">${label}</span><span class="value">${val}</span></div>`;
                    }).join('');

                    const spacingSection = document.createElement('div');
                    spacingSection.className = 'theme-section';
                    spacingSection.innerHTML = `<h4>Spacing</h4><div class="spacing-preview">${spacingHtml}</div>`;

                    const themePreview = document.querySelector('.theme-preview');
                    if (themePreview) themePreview.appendChild(spacingSection);
                }
            },

            setupPreviewTabs() {
                document.querySelectorAll('.jtb-ai-preview-tabs .tab').forEach(tab => {
                    tab.addEventListener('click', () => {
                        // Update tabs
                        document.querySelectorAll('.jtb-ai-preview-tabs .tab').forEach(t => t.classList.remove('active'));
                        tab.classList.add('active');

                        // Update panels
                        const panel = tab.dataset.preview;
                        document.querySelectorAll('.preview-panel').forEach(p => p.classList.remove('active'));
                        document.querySelector(`.preview-panel[data-panel="${panel}"]`)?.classList.add('active');
                    });
                });

                // Copy JSON button
                document.getElementById('copy-json-btn')?.addEventListener('click', () => {
                    navigator.clipboard.writeText(JSON.stringify(this.generatedWebsite, null, 2));
                    JTB.showNotification('JSON copied to clipboard!', 'success');
                });

                // Download JSON button
                document.getElementById('download-json-btn')?.addEventListener('click', () => {
                    const blob = new Blob([JSON.stringify(this.generatedWebsite, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'generated-website.json';
                    a.click();
                    URL.revokeObjectURL(url);
                });
            },

            // =============================================
            // 8.1: Brand Kit Extraction
            // =============================================
            async extractBrandKit() {
                const urlInput = document.getElementById('ai-brand-url');
                const url = urlInput?.value?.trim();
                if (!url) {
                    alert('Please enter a website URL to extract brand kit from');
                    urlInput?.focus();
                    return;
                }

                const btn = document.getElementById('extract-brand-btn');
                const resultContainer = document.getElementById('brand-kit-result');
                const originalText = btn.textContent;

                btn.disabled = true;
                btn.textContent = 'Extracting...';
                resultContainer.style.display = 'block';
                resultContainer.innerHTML = '<div style="color:rgba(255,255,255,0.5);font-size:12px;padding:8px 0;">Analyzing website...</div>';

                try {
                    const response = await fetch('/api/jtb/ai/generate-website', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            action: 'brand-kit',
                            url: url
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.brand_kit) {
                        this.extractedBrandKit = data.brand_kit;

                        // Render brand kit result
                        let html = '<div style="margin-top:8px;">';

                        // Colors - PHP returns {primary, secondary, accent, all_extracted}
                        const colorsObj = data.brand_kit.colors;
                        if (colorsObj) {
                            // Build color array from object or use all_extracted
                            const colorArr = colorsObj.all_extracted || [colorsObj.primary, colorsObj.secondary, colorsObj.accent].filter(Boolean);
                            if (colorArr.length > 0) {
                                html += '<div style="margin-bottom:8px;"><span style="font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;">Colors</span><div style="display:flex;gap:4px;margin-top:4px;">';
                                colorArr.slice(0, 8).forEach(color => {
                                    html += `<div style="width:28px;height:28px;border-radius:4px;background:${color};border:1px solid rgba(255,255,255,0.15);" title="${color}"></div>`;
                                });
                                html += '</div></div>';
                            }
                        }

                        // Fonts - PHP returns {heading, body, all_extracted}
                        const fontsObj = data.brand_kit.fonts;
                        if (fontsObj) {
                            const fontArr = fontsObj.all_extracted || [fontsObj.heading, fontsObj.body].filter(Boolean);
                            if (fontArr.length > 0) {
                                html += '<div style="margin-bottom:8px;"><span style="font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;">Fonts</span><div style="margin-top:4px;font-size:12px;color:rgba(255,255,255,0.8);">';
                                html += fontArr.slice(0, 4).join(', ');
                                html += '</div></div>';
                            }
                        }

                        // Logo
                        if (data.brand_kit.logo_url) {
                            html += '<div style="margin-bottom:8px;"><span style="font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;">Logo</span>';
                            html += `<div style="margin-top:4px;"><img src="${data.brand_kit.logo_url}" style="max-height:32px;max-width:120px;background:#fff;padding:4px;border-radius:4px;" alt="Logo"></div>`;
                            html += '</div>';
                        }

                        // Style
                        if (data.brand_kit.style) {
                            html += `<div><span style="font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;">Detected Style</span><div style="margin-top:4px;font-size:12px;color:#818cf8;">${data.brand_kit.style}</div></div>`;
                        }

                        html += '<div style="margin-top:8px;font-size:11px;color:#34d399;">✓ Brand kit will be applied to generation</div>';
                        html += '</div>';

                        resultContainer.innerHTML = html;
                        JTB.showNotification('Brand kit extracted successfully!', 'success');
                    } else {
                        throw new Error(data.error || 'Extraction failed');
                    }
                } catch (err) {
                    console.error('Brand kit extraction error:', err);
                    resultContainer.innerHTML = `<div style="color:#f87171;font-size:12px;padding:8px 0;">Error: ${err.message}</div>`;
                    JTB.showNotification('Brand kit extraction failed: ' + err.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            },

            // =============================================
            // 8.5: Multi-language Translation
            // =============================================
            async translateWebsite(forceLang = null) {
                if (!this.generatedWebsite) {
                    alert('Please generate a website first');
                    return;
                }

                // Get target language
                let language = forceLang;
                if (!language) {
                    language = document.getElementById('ai-website-language')?.value;
                    if (!language || language === 'en') {
                        // Show a quick language picker dialog
                        language = prompt('Enter target language (e.g., Polish, German, Spanish, French, Japanese):');
                        if (!language) return;
                    }
                }

                // Show progress
                const translateBtn = document.querySelector('[data-action="translate-website"]');
                if (translateBtn) {
                    translateBtn.disabled = true;
                    translateBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">🔄 Translating...</span>';
                }

                JTB.showNotification(`Translating website to ${language}...`, 'info');

                try {
                    // Strip internal metadata before sending to API (save tokens)
                    const websiteClean = {};
                    for (const [key, val] of Object.entries(this.generatedWebsite)) {
                        if (!key.startsWith('_')) {
                            websiteClean[key] = val;
                        }
                    }

                    const response = await fetch('/api/jtb/ai/generate-website', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            action: 'translate',
                            website: websiteClean,
                            language: language
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.website) {
                        // Keep stats and metadata from original
                        const stats = this.generatedWebsite._stats;
                        const stages = this.generatedWebsite._stages;
                        const competitor = this.generatedWebsite._competitor_analysis;

                        this.generatedWebsite = data.website;
                        this.generatedWebsite._stats = stats;
                        this.generatedWebsite._stages = stages;
                        this.generatedWebsite._competitor_analysis = competitor;
                        this.generatedWebsite._translated_to = data.language || language;

                        // Refresh preview
                        this.showGeneratedPreview();

                        JTB.showNotification(`Website translated to ${data.language || language}!`, 'success');
                    } else {
                        throw new Error(data.error || 'Translation failed');
                    }
                } catch (err) {
                    console.error('Translation error:', err);
                    JTB.showNotification('Translation failed: ' + err.message, 'error');
                } finally {
                    if (translateBtn) {
                        translateBtn.disabled = false;
                        translateBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">🌐 Translate</span>';
                    }
                }
            },

            // =============================================
            // 8.3: A/B Variants (Hero section)
            // =============================================
            async generateHeroVariants() {
                if (!this.generatedWebsite) {
                    alert('Please generate a website first');
                    return;
                }

                const variantsBtn = document.querySelector('[data-action="generate-variants"]');
                if (variantsBtn) {
                    variantsBtn.disabled = true;
                    variantsBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">🔄 Generating variants...</span>';
                }

                JTB.showNotification('Generating hero section variants...', 'info');

                // Build context from current website
                const context = {
                    industry: document.getElementById('ai-website-industry')?.value || 'general',
                    style: document.getElementById('ai-website-style')?.value || 'modern',
                    prompt: document.getElementById('ai-website-prompt')?.value || '',
                    current_hero: this.generatedWebsite.pages?.home?.sections?.[0] || null
                };

                try {
                    const response = await fetch('/api/jtb/ai/generate-website', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            action: 'variants',
                            section_type: 'hero',
                            context: context,
                            count: 3
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.variants?.length > 0) {
                        this.showVariantsPicker(data.variants);
                    } else {
                        throw new Error(data.error || 'No variants generated');
                    }
                } catch (err) {
                    console.error('Variants error:', err);
                    JTB.showNotification('Variants generation failed: ' + err.message, 'error');
                } finally {
                    if (variantsBtn) {
                        variantsBtn.disabled = false;
                        variantsBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;">🔀 A/B Variants</span>';
                    }
                }
            },

            // Show variant picker overlay
            async showVariantsPicker(variants) {
                // Create overlay
                let overlay = document.getElementById('jtb-variants-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'jtb-variants-overlay';
                    document.body.appendChild(overlay);
                }

                let html = `
                    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:100000;overflow-y:auto;padding:40px 20px;">
                        <div style="max-width:1200px;margin:0 auto;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                                <h2 style="color:#fff;margin:0;font-size:20px;">🔀 Choose Hero Section Variant</h2>
                                <button onclick="document.getElementById('jtb-variants-overlay').remove();" style="background:rgba(255,255,255,0.1);border:none;color:#fff;padding:8px 16px;border-radius:6px;cursor:pointer;">✕ Close</button>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px;">
                `;

                for (let i = 0; i < variants.length; i++) {
                    const v = variants[i];
                    const label = v.label || `Variant ${i + 1}`;
                    const variantId = v.id || `v${i + 1}`;

                    html += `
                        <div class="variant-card" style="background:rgba(255,255,255,0.05);border:2px solid rgba(255,255,255,0.1);border-radius:12px;overflow:hidden;cursor:pointer;transition:all 0.2s;" data-variant-idx="${i}" onmouseover="this.style.borderColor='rgba(99,102,241,0.5)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                            <div style="padding:16px;border-bottom:1px solid rgba(255,255,255,0.1);">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <h3 style="color:#fff;margin:0;font-size:15px;">${label}</h3>
                                    <span style="font-size:11px;color:rgba(255,255,255,0.4);">${variantId}</span>
                                </div>
                            </div>
                            <div style="height:250px;overflow:hidden;position:relative;">
                                <iframe id="variant-iframe-${i}" style="width:200%;height:600px;border:none;transform:scale(0.5);transform-origin:top left;pointer-events:none;" title="Variant ${i + 1}"></iframe>
                            </div>
                            <div style="padding:12px 16px;text-align:center;">
                                <button onclick="WB.applyVariant(${i})" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;padding:10px 24px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;width:100%;">
                                    ✓ Use This Variant
                                </button>
                            </div>
                        </div>
                    `;
                }

                html += `</div></div></div>`;
                overlay.innerHTML = html;

                // Store variants for later use
                this._currentVariants = variants;

                // Render each variant in its iframe
                for (let i = 0; i < variants.length; i++) {
                    const sections = variants[i].sections || [];
                    if (sections.length === 0) continue;

                    try {
                        const response = await fetch('/api/jtb/render', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.csrfToken
                            },
                            body: JSON.stringify({
                                content: { version: '1.0', content: sections }
                            })
                        });

                        const data = await response.json();
                        if (data.success && data.html) {
                            const iframe = document.getElementById(`variant-iframe-${i}`);
                            if (iframe) {
                                const doc = iframe.contentDocument || iframe.contentWindow.document;
                                doc.open();
                                doc.write(`<!DOCTYPE html><html><head>
                                    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
                                    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css">
                                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
                                    <style>* { box-sizing: border-box; } body { margin:0;padding:0;font-family:'Inter',sans-serif;background:#fff;color:#333; } img { max-width:100%;height:auto; }</style>
                                    ${data.css ? '<style>' + data.css + '</style>' : ''}
                                </head><body>${data.html}</body></html>`);
                                doc.close();
                            }
                        }
                    } catch (err) {
                        console.error(`Variant ${i} render error:`, err);
                    }
                }
            },

            // Apply selected variant to generated website
            applyVariant(index) {
                if (!this._currentVariants || !this._currentVariants[index]) return;

                const variant = this._currentVariants[index];

                // Replace first section (hero) on home page
                if (this.generatedWebsite.pages?.home?.sections && variant.sections?.length > 0) {
                    this.generatedWebsite.pages.home.sections[0] = variant.sections[0];
                    JTB.showNotification(`Applied variant: ${variant.label || 'Variant ' + (index + 1)}`, 'success');
                }

                // Remove overlay
                document.getElementById('jtb-variants-overlay')?.remove();

                // Refresh preview
                this.showGeneratedPreview();
            },

            // =============================================
            // 8.4: Content Regeneration (per-element)
            // =============================================
            async regenerateModuleContent(moduleType, currentAttrs, instruction) {
                if (!instruction) {
                    instruction = prompt('How should we improve this content?\n(e.g., "make more professional", "shorten", "add more emotion")');
                    if (!instruction) return null;
                }

                try {
                    const response = await fetch('/api/jtb/ai/generate-website', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            action: 'regenerate-content',
                            module_type: moduleType,
                            attrs: currentAttrs,
                            instruction: instruction,
                            context: {
                                industry: document.getElementById('ai-website-industry')?.value || 'general',
                                style: document.getElementById('ai-website-style')?.value || 'modern'
                            }
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.attrs) {
                        JTB.showNotification(`Content regenerated (${data.changed_fields?.length || 0} fields updated)`, 'success');
                        return data.attrs;
                    } else {
                        throw new Error(data.error || 'Regeneration failed');
                    }
                } catch (err) {
                    console.error('Regenerate error:', err);
                    JTB.showNotification('Regeneration failed: ' + err.message, 'error');
                    return null;
                }
            },

            async applyGeneratedWebsite() {
                if (!this.generatedWebsite) {
                    alert('No website generated');
                    return;
                }

                const apply = confirm('This will update your header, footer, and create/update pages. Continue?');
                if (!apply) return;

                try {
                    // Apply header
                    if (this.generatedWebsite.header && this.headers.length > 0) {
                        const headerId = this.headers[0].id;
                        await fetch('/api/jtb/template-save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.csrfToken
                            },
                            body: JSON.stringify({
                                id: headerId,
                                content: {
                                    version: '1.0',
                                    content: this.generatedWebsite.header.sections || []
                                }
                            })
                        });
                    }

                    // Apply footer
                    if (this.generatedWebsite.footer && this.footers.length > 0) {
                        const footerId = this.footers[0].id;
                        await fetch('/api/jtb/template-save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.csrfToken
                            },
                            body: JSON.stringify({
                                id: footerId,
                                content: {
                                    version: '1.0',
                                    content: this.generatedWebsite.footer.sections || []
                                }
                            })
                        });
                    }

                    // Apply pages via save-website API
                    if (this.generatedWebsite.pages && this.generatedWebsite.pages.length > 0) {
                        const saveResponse = await fetch('/api/jtb/ai/save-website', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.csrfToken
                            },
                            body: JSON.stringify({
                                session_id: this.multiAgentSessionId || '',
                                pages: this.generatedWebsite.pages,
                                clear_existing: false
                            })
                        });
                        const saveResult = await saveResponse.json();
                        if (!saveResult.success) {
                            console.warn('[WB] Pages save warning:', saveResult.error);
                        }
                    }

                    JTB.showNotification('Website applied successfully!', 'success');
                    this.hideAIModal();

                    // Reload to see changes
                    window.location.reload();

                } catch (err) {
                    console.error('Apply error:', err);
                    JTB.showNotification('Failed to apply: ' + err.message, 'error');
                }
            }
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
            const multiAgentBtn = document.getElementById('ai-multiagent-btn');
            if (multiAgentBtn) {
                multiAgentBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Close the standard AI modal
                    const aiModal = document.getElementById('jtb-ai-website-modal');
                    if (aiModal) aiModal.style.display = 'none';

                    // Get form values from standard AI modal
                    const prompt = document.getElementById('ai-website-prompt')?.value || '';
                    const industry = document.getElementById('ai-website-industry')?.value || 'technology';
                    const style = document.getElementById('ai-website-style')?.value || 'modern';
                    const pages = Array.from(document.querySelectorAll('.page-checkbox:checked')).map(cb => cb.value);

                    // Pre-fill multi-agent form
                    if (prompt) {
                        const maPrompt = document.getElementById('jtb-ma-prompt');
                        if (maPrompt) maPrompt.value = prompt;
                    }
                    const maIndustry = document.getElementById('jtb-ma-industry');
                    if (maIndustry) maIndustry.value = industry;
                    const maStyle = document.getElementById('jtb-ma-style');
                    if (maStyle) maStyle.value = style;
                    if (pages.length > 0) {
                        document.querySelectorAll('.jtb-ma-checkbox-item input[type="checkbox"]').forEach(cb => {
                            cb.checked = pages.includes(cb.value);
                        });
                    }

                    // Open Multi-Agent modal
                    JTB_AI_MultiAgent.openModal();
                });
            }

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
