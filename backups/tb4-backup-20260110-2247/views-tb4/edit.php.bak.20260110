<?php
/**
 * TB4 Visual Builder - Main Editor View
 *
 * Full-screen visual page builder interface
 * Pure PHP - NO React/Vue/npm
 *
 * @package TB4
 * @version 1.0.0
 */

// Ensure we have CMS root defined
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 5));
}

// Load TB4 module registry
require_once CMS_ROOT . '/core/tb4/init.php';

// Get page ID from controller (passed via extract())
// Variables available from controller: $page_id, $pageId, $page, $content
$page_id = (int)($page_id ?? $pageId ?? $_GET['id'] ?? 0);

// Use $page and $content passed from controller
// Only set defaults if not already set by controller
if (!isset($page) || !is_array($page)) {
    $page = [];
}

// Ensure content has proper structure
if (!isset($content) || !is_array($content)) {
    $content = ['sections' => []];
} elseif (!isset($content['sections'])) {
    $content['sections'] = [];
}

// Get available modules from registry
$registry = \Core\TB4\ModuleRegistry::getInstance();
$available_modules = $registry->getModulesForJson();

// Page metadata
$page_title = esc($page['title'] ?? 'New Page');
$page_slug = esc($page['slug'] ?? '');
$page_status = $page['status'] ?? 'draft';

// Security tokens
$csrf_token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TB4 Builder - <?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tb4/design-system.css">
    <link rel="stylesheet" href="/assets/tb4/css/builder.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
    /* ═══════════════════════════════════════════════════════════════════
       TB4 BUILDER - CORE LAYOUT STYLES
       Full-screen builder with no admin sidebar
       ═══════════════════════════════════════════════════════════════════ */

    :root {
        --tb4-toolbar-height: 56px;
        --tb4-sidebar-width: 300px;
        --tb4-bottombar-height: 48px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        height: 100%;
        overflow: hidden;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        color: var(--tb4-text-primary);
        background: var(--tb4-bg-secondary);
    }

    /* ═══════════════════════════════════════════════════════════════════
       TOP TOOLBAR
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-toolbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--tb4-toolbar-height);
        background: var(--tb4-sidebar-bg, #1e293b);
        border-bottom: 1px solid var(--tb4-sidebar-border, #334155);
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 12px;
        z-index: 1000;
    }

    .tb4-toolbar-section {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tb4-toolbar-left { flex: 0 0 auto; }
    .tb4-toolbar-center { flex: 1; justify-content: center; }
    .tb4-toolbar-right { flex: 0 0 auto; }

    .tb4-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--tb4-sidebar-text-active, #f1f5f9);
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
    }

    .tb4-logo-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--tb4-primary-500), var(--tb4-primary-700));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 12px;
    }

    .tb4-page-title-input {
        background: rgba(255,255,255,0.08);
        border: 1px solid transparent;
        color: var(--tb4-sidebar-text-active, #f1f5f9);
        font-size: 15px;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: 6px;
        text-align: center;
        min-width: 240px;
        transition: all 0.15s;
    }

    .tb4-page-title-input:hover {
        border-color: rgba(255,255,255,0.15);
    }

    .tb4-page-title-input:focus {
        outline: none;
        border-color: var(--tb4-primary-500);
        background: rgba(255,255,255,0.12);
    }

    .tb4-device-switcher {
        display: flex;
        background: rgba(255,255,255,0.05);
        border-radius: 6px;
        padding: 2px;
    }

    .tb4-device-btn {
        padding: 6px 10px;
        background: transparent;
        border: none;
        color: var(--tb4-sidebar-text, #94a3b8);
        cursor: pointer;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }

    .tb4-device-btn:hover {
        color: var(--tb4-sidebar-text-active, #f1f5f9);
    }

    .tb4-device-btn.active {
        background: var(--tb4-primary-600);
        color: white;
    }

    .tb4-divider {
        width: 1px;
        height: 28px;
        background: var(--tb4-sidebar-border, #334155);
        margin: 0 4px;
    }

    .tb4-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
        background: rgba(255,255,255,0.08);
        color: var(--tb4-sidebar-text, #94a3b8);
        text-decoration: none;
    }

    .tb4-btn:hover {
        background: rgba(255,255,255,0.12);
        color: var(--tb4-sidebar-text-active, #f1f5f9);
    }

    .tb4-btn-icon {
        padding: 8px;
        min-width: 36px;
    }

    .tb4-btn-primary {
        background: var(--tb4-primary-600);
        color: white;
    }

    .tb4-btn-primary:hover {
        background: var(--tb4-primary-700);
    }
    
    /* Save Dropdown */
    .tb4-save-dropdown {
        position: relative;
        display: inline-flex;
    }
    .tb4-save-main {
        border-radius: 6px 0 0 6px;
        padding-right: 12px;
    }
    .tb4-save-toggle {
        border-radius: 0 6px 6px 0;
        padding: 8px 6px;
        border-left: 1px solid rgba(255,255,255,0.2);
        min-width: auto;
    }
    .tb4-save-toggle:hover {
        background: var(--tb4-primary-800, #1e40af);
    }
    .tb4-save-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 4px;
        background: #1e293b;
        border: 1px solid #475569;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        min-width: 180px;
        z-index: 1000;
        overflow: hidden;
    }
    .tb4-save-menu.show {
        display: block;
    }
    .tb4-save-option {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 12px 16px;
        background: transparent;
        border: none;
        color: #e2e8f0;
        font-size: 14px;
        cursor: pointer;
        text-align: left;
        transition: background 0.15s;
    }
    .tb4-save-option:hover {
        background: #2563eb;
        color: #ffffff;
    }
    .tb4-save-option i {
        color: #94a3b8;
    }
    .tb4-save-option:hover i {
        color: #ffffff;
    }

    .tb4-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .tb4-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .tb4-status-badge.draft {
        background: var(--tb4-warning-bg);
        color: var(--tb4-warning-text);
    }

    .tb4-status-badge.published {
        background: var(--tb4-success-bg);
        color: var(--tb4-success-text);
    }

    /* ═══════════════════════════════════════════════════════════════════
       MAIN WORKSPACE
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-builder {
        position: fixed;
        top: var(--tb4-toolbar-height);
        left: 0;
        right: 0;
        bottom: var(--tb4-bottombar-height);
    }

    .tb4-workspace {
        display: flex;
        height: 100%;
    }

    /* ═══════════════════════════════════════════════════════════════════
       LEFT SIDEBAR - Tabbed (Modules / Layers / Settings) - DARK THEME
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-sidebar {
        width: var(--tb4-sidebar-width);
        background: #1e293b;
        border-right: 1px solid #334155;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .tb4-sidebar-tabs {
        display: flex;
        border-bottom: 1px solid #334155;
        background: #0f172a;
    }

    .tb4-sidebar-tab {
        flex: 1;
        padding: 12px 8px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        color: #64748b;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .tb4-sidebar-tab:hover {
        color: #94a3b8;
        background: #1e293b;
    }

    .tb4-sidebar-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        background: #1e293b;
    }

    .tb4-sidebar-tab-icon {
        width: 20px;
        height: 20px;
    }

    .tb4-sidebar-content {
        flex: 1;
        overflow: hidden;
        position: relative;
        background: #1e293b;
    }

    .tb4-panel {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        overflow-y: auto;
        padding: 16px;
        display: none;
        background: #1e293b;
    }

    .tb4-panel.active {
        display: block;
    }

    /* Modules Panel - DARK THEME */
    .tb4-modules-search {
        width: 100%;
        padding: 10px 12px 10px 36px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        color: #e2e8f0;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .tb4-modules-search::placeholder {
        color: #64748b;
    }

    .tb4-modules-search:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .tb4-search-wrapper {
        position: relative;
    }

    .tb4-search-wrapper .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #64748b;
    }

    .tb4-module-category {
        margin-bottom: 20px;
    }

    .tb4-category-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 10px;
        padding: 0 4px;
    }

    .tb4-modules-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .tb4-module-item {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 14px 8px;
        text-align: center;
        cursor: grab;
        transition: all 0.15s;
    }

    .tb4-module-item:hover {
        border-color: #3b82f6;
        background: #1e3a5f;
    }

    .tb4-module-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }

    .tb4-module-icon {
        width: 28px;
        height: 28px;
        color: #94a3b8;
    }

    .tb4-module-name {
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Layers Panel - DARK THEME */
    .tb4-layers-list {
        list-style: none;
    }

    .tb4-layer-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        margin-bottom: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-layer-item:hover {
        border-color: #3b82f6;
    }

    .tb4-layer-item.selected {
        border-color: #3b82f6;
        background: #1e3a5f;
    }

    .tb4-layer-item.section {
        font-weight: 600;
    }

    .tb4-layer-item.row {
        margin-left: 16px;
    }

    .tb4-layer-item.module {
        margin-left: 32px;
    }

    .tb4-layer-icon {
        width: 16px;
        height: 16px;
        color: #64748b;
    }

    .tb4-layer-name {
        flex: 1;
        font-size: 13px;
        color: #e2e8f0;
    }

    .tb4-layer-actions {
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: opacity 0.15s;
    }

    .tb4-layer-item:hover .tb4-layer-actions {
        opacity: 1;
    }

    .tb4-layer-action {
        padding: 4px;
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        border-radius: 4px;
    }

    .tb4-layer-action:hover {
        background: #334155;
        color: #e2e8f0;
    }

    /* Settings Panel Empty State - DARK THEME */
    .tb4-settings-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #64748b;
        text-align: center;
        padding: 24px;
    }

    .tb4-settings-empty-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 12px;
        opacity: 0.4;
    }

    .tb4-settings-empty-text {
        font-size: 14px;
        line-height: 1.5;
        color: #94a3b8;
    }

    /* Settings Tabs - DARK THEME */
    .tb4-settings-content {
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #1e293b;
    }

    .tb4-settings-tabs {
        display: flex;
        gap: 4px;
        padding: 8px;
        background: #0f172a;
        border-bottom: 1px solid #334155;
    }

    .tb4-settings-tab {
        flex: 1;
        padding: 8px 12px;
        background: transparent;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-settings-tab:hover {
        background: #334155;
        color: #e2e8f0;
    }

    .tb4-settings-tab.active {
        background: #3b82f6;
        color: white;
    }

    .tb4-settings-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background: #1e293b;
    }

    .tb4-settings-tab-content {
        display: none;
    }

    .tb4-settings-tab-content.active {
        display: block;
    }

    .tb4-settings-group {
        margin-bottom: 20px;
    }

    .tb4-settings-group h4 {
        font-size: 13px;
        font-weight: 600;
        color: #e2e8f0;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #334155;
    }

    .tb4-field {
        margin-bottom: 16px;
    }

    .tb4-label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .tb4-input,
    .tb4-select,
    .tb4-textarea {
        width: 100%;
        padding: 8px 12px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        font-size: 13px;
        color: #e2e8f0;
        transition: border-color 0.15s;
    }

    .tb4-input:focus,
    .tb4-select:focus,
    .tb4-textarea:focus {
        outline: none;
        border-color: #3b82f6;
    }

    /* Range slider */
    .tb4-range {
        width: 100%;
        height: 6px;
        background: #334155;
        border-radius: 3px;
        appearance: none;
        cursor: pointer;
    }

    .tb4-range::-webkit-slider-thumb {
        appearance: none;
        width: 16px;
        height: 16px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
    }

    .tb4-range-value {
        display: inline-block;
        min-width: 40px;
        text-align: center;
        color: #94a3b8;
        font-size: 12px;
    }

    /* Spacing control */
    .tb4-spacing-control {
        margin-bottom: 16px;
    }

    .tb4-spacing-inputs {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .tb4-spacing-input {
        width: 60px;
        padding: 6px 8px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 4px;
        font-size: 12px;
        color: #e2e8f0;
        text-align: center;
    }

    .tb4-spacing-link {
        padding: 4px 8px;
        background: #334155;
        border: none;
        border-radius: 4px;
        color: #94a3b8;
        cursor: pointer;
    }

    .tb4-spacing-link:hover {
        background: #475569;
        color: #e2e8f0;
    }

    /* Layout picker */
    .tb4-layout-picker {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tb4-layout-option {
        padding: 8px 12px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-layout-option:hover {
        border-color: #3b82f6;
        color: #e2e8f0;
    }

    .tb4-layout-option.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .tb4-settings-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 16px;
        border-bottom: 1px solid #334155;
        margin-bottom: 16px;
    }

    .tb4-settings-title {
        font-size: 16px;
        font-weight: 600;
        color: #e2e8f0;
    }

    .tb4-setting-group {
        margin-bottom: 16px;
    }

    .tb4-setting-label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .tb4-setting-input {
        width: 100%;
        padding: 10px 12px;
        background: var(--tb4-bg-secondary);
        border: 1px solid var(--tb4-border-default);
        border-radius: 6px;
        color: var(--tb4-text-primary);
        font-size: 13px;
        transition: border-color 0.15s;
    }

    .tb4-setting-input:focus {
        outline: none;
        border-color: var(--tb4-primary-500);
    }

    .tb4-setting-input[type="color"] {
        padding: 4px;
        height: 42px;
        cursor: pointer;
    }

    .tb4-settings-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 16px;
    }

    .tb4-settings-tab {
        flex: 1;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        background: var(--tb4-bg-secondary);
        border: 1px solid var(--tb4-border-default);
        border-radius: 6px;
        color: var(--tb4-text-secondary);
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-settings-tab:hover {
        border-color: var(--tb4-primary-400);
    }

    .tb4-settings-tab.active {
        background: var(--tb4-primary-600);
        border-color: var(--tb4-primary-600);
        color: white;
    }

    /* ═══════════════════════════════════════════════════════════════════
       CANVAS AREA - Catppuccin Mocha Colors
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-canvas-wrapper {
        flex: 1;
        background: #1e1e2e; /* Catppuccin Mocha base */
        background-image: radial-gradient(circle, #45475a 1px, transparent 1px);
        background-size: 20px 20px;
        overflow: auto;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 24px;
    }

    .tb4-canvas {
        background: #313244; /* Catppuccin Mocha surface0 */
        min-height: calc(100vh - var(--tb4-toolbar-height) - var(--tb4-bottombar-height) - 48px);
        border-radius: 8px;
        border: 1px solid #45475a; /* Catppuccin Mocha surface2 */
        box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        transition: width 0.3s ease;
        overflow: hidden;
    }

    .tb4-canvas[data-device="desktop"] {
        width: 100%;
        max-width: 1200px;
    }

    .tb4-canvas[data-device="tablet"] {
        width: 768px;
    }

    .tb4-canvas[data-device="mobile"] {
        width: 375px;
    }

    /* Responsive modules inside columns */
    .tb4-canvas[data-device="tablet"] .tb4-module,
    .tb4-canvas[data-device="mobile"] .tb4-module {
        max-width: 100%;
        overflow: hidden;
    }

    .tb4-canvas[data-device="tablet"] .tb4-module > *,
    .tb4-canvas[data-device="mobile"] .tb4-module > * {
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Search module responsive */
    .tb4-canvas[data-device="tablet"] .tb4-search-preview,
    .tb4-canvas[data-device="mobile"] .tb4-search-preview {
        flex-direction: column;
        gap: 8px;
    }
    .tb4-canvas[data-device="tablet"] .tb4-search-preview input,
    .tb4-canvas[data-device="tablet"] .tb4-search-preview button,
    .tb4-canvas[data-device="mobile"] .tb4-search-preview input,
    .tb4-canvas[data-device="mobile"] .tb4-search-preview button {
        width: 100% !important;
        flex: none !important;
    }

    /* Signup module responsive */
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview {
        flex-direction: column;
        gap: 8px;
    }
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview input,
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview button {
        width: 100% !important;
        flex: none !important;
    }

    /* Gallery responsive */
    .tb4-canvas[data-device="mobile"] .tb4-gallery-grid {
        grid-template-columns: 1fr !important;
    }
    .tb4-canvas[data-device="tablet"] .tb4-gallery-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* Social links responsive */
    .tb4-canvas[data-device="mobile"] .tb4-social-links {
        flex-wrap: wrap;
        justify-content: center;
    }

    /* ========================================
       RESPONSIVE STYLES FOR ALL 43+ MODULES
       ======================================== */

    /* General module content overflow */
    .tb4-canvas[data-device="tablet"] .tb4-module-content,
    .tb4-canvas[data-device="mobile"] .tb4-module-content {
        max-width: 100% !important;
        overflow-x: hidden;
    }

    .tb4-canvas[data-device="tablet"] .tb4-module-content > div,
    .tb4-canvas[data-device="mobile"] .tb4-module-content > div {
        max-width: 100% !important;
        box-sizing: border-box;
    }

    /* Hero module */
    .tb4-canvas[data-device="mobile"] .tb4-module-hero h1,
    .tb4-canvas[data-device="mobile"] .tb4-module-hero h2 {
        font-size: 1.5em !important;
    }

    /* CTA module */
    .tb4-canvas[data-device="mobile"] .tb4-cta-preview {
        padding: 15px !important;
    }
    .tb4-canvas[data-device="mobile"] .tb4-cta-preview h3 {
        font-size: 1.2em !important;
    }

    /* Testimonial module */
    .tb4-canvas[data-device="mobile"] .tb4-testimonial-preview {
        padding: 15px !important;
    }

    /* Team module */
    .tb4-canvas[data-device="mobile"] .tb4-team-preview {
        text-align: center;
    }

    /* Blurb module */
    .tb4-canvas[data-device="mobile"] .tb4-blurb-preview {
        flex-direction: column !important;
        text-align: center;
    }

    /* Blog module */
    .tb4-canvas[data-device="mobile"] .tb4-blog-preview {
        grid-template-columns: 1fr !important;
    }
    .tb4-canvas[data-device="tablet"] .tb4-blog-preview {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* Portfolio module */
    .tb4-canvas[data-device="mobile"] .tb4-portfolio-preview {
        grid-template-columns: 1fr !important;
    }
    .tb4-canvas[data-device="tablet"] .tb4-portfolio-preview {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* Pricing module */
    .tb4-canvas[data-device="mobile"] .tb4-pricing-preview {
        padding: 15px !important;
    }

    /* Slider modules */
    .tb4-canvas[data-device="mobile"] .tb4-slider-preview,
    .tb4-canvas[data-device="mobile"] .tb4-fw-slider-preview,
    .tb4-canvas[data-device="mobile"] .tb4-post-slider-preview,
    .tb4-canvas[data-device="mobile"] .tb4-fw-post-slider-preview {
        min-height: 200px !important;
    }

    /* Countdown module */
    .tb4-canvas[data-device="mobile"] .tb4-countdown-preview {
        flex-wrap: wrap;
        gap: 10px !important;
    }
    .tb4-canvas[data-device="mobile"] .tb4-countdown-preview > div {
        min-width: 60px !important;
    }

    /* Contact form */
    .tb4-canvas[data-device="mobile"] .tb4-contact-preview input,
    .tb4-canvas[data-device="mobile"] .tb4-contact-preview textarea,
    .tb4-canvas[data-device="mobile"] .tb4-contact-preview button {
        width: 100% !important;
    }

    /* Login form */
    .tb4-canvas[data-device="mobile"] .tb4-module-login input,
    .tb4-canvas[data-device="mobile"] .tb4-module-login button {
        width: 100% !important;
    }

    /* Map modules */
    .tb4-canvas[data-device="mobile"] .tb4-map-preview,
    .tb4-canvas[data-device="mobile"] .tb4-fw-map-preview {
        min-height: 200px !important;
    }

    /* Menu modules */
    .tb4-canvas[data-device="mobile"] .tb4-fw-menu-preview {
        flex-direction: column !important;
        align-items: center;
    }
    .tb4-canvas[data-device="mobile"] .tb4-fw-menu-preview a {
        padding: 10px !important;
    }

    /* Fullwidth header */
    .tb4-canvas[data-device="mobile"] .tb4-fw-header-preview {
        padding: 20px !important;
    }
    .tb4-canvas[data-device="mobile"] .tb4-fw-header-preview h1 {
        font-size: 1.5em !important;
    }

    /* Toggle module */
    .tb4-canvas[data-device="mobile"] .tb4-toggle-preview {
        padding: 10px !important;
    }

    /* Code module */
    .tb4-canvas[data-device="mobile"] .tb4-module-code pre,
    .tb4-canvas[data-device="mobile"] .tb4-module-fw_code pre {
        font-size: 12px !important;
        overflow-x: auto;
    }

    /* Audio module */
    .tb4-canvas[data-device="mobile"] .tb4-module-audio > div {
        padding: 15px !important;
    }

    /* Video module */
    .tb4-canvas[data-device="mobile"] .tb4-video-preview {
        min-height: 150px !important;
    }

    /* Image modules */
    .tb4-canvas[data-device="mobile"] .tb4-image-preview img,
    .tb4-canvas[data-device="mobile"] .tb4-fw-image-preview img {
        max-width: 100% !important;
        height: auto !important;
    }

    /* Icon module */
    .tb4-canvas[data-device="mobile"] .tb4-module-icon {
        text-align: center;
    }

    /* Quote module */
    .tb4-canvas[data-device="mobile"] .tb4-module-quote blockquote {
        padding: 15px !important;
        font-size: 1em !important;
    }

    /* Post navigation */
    .tb4-canvas[data-device="mobile"] .tb4-module-post_nav > div {
        flex-direction: column !important;
        gap: 10px;
    }

    /* Comments */
    .tb4-canvas[data-device="mobile"] .tb4-module-comments > div {
        padding: 10px !important;
    }

    /* Number/Circle counters */
    .tb4-canvas[data-device="mobile"] .tb4-module-number,
    .tb4-canvas[data-device="mobile"] .tb4-module-circle {
        text-align: center;
    }
    .tb4-canvas[data-device="mobile"] .tb4-module-number span,
    .tb4-canvas[data-device="mobile"] .tb4-module-circle span {
        font-size: 2em !important;
    }

    /* Progress bar */
    .tb4-canvas[data-device="mobile"] .tb4-module-progress {
        padding: 10px !important;
    }

    /* Accordion */
    .tb4-canvas[data-device="mobile"] .tb4-module-accordion > div {
        padding: 10px !important;
    }

    /* Tabs */
    .tb4-canvas[data-device="mobile"] .tb4-module-tabs > div {
        flex-direction: column !important;
    }

    /* Spacer responsive */
    .tb4-canvas[data-device="mobile"] .tb4-module-spacer > div {
        height: 30px !important;
    }

    /* Divider */
    .tb4-canvas[data-device="mobile"] .tb4-module-divider hr {
        margin: 15px 0 !important;
    }

    /* Form fields general */
    .tb4-canvas[data-device="mobile"] .tb4-module-form input,
    .tb4-canvas[data-device="mobile"] .tb4-module-form select,
    .tb4-canvas[data-device="mobile"] .tb4-module-form textarea,
    .tb4-canvas[data-device="mobile"] .tb4-module-form button {
        width: 100% !important;
        margin-bottom: 10px !important;
    }

    /* Fullwidth portfolio */
    .tb4-canvas[data-device="mobile"] .tb4-fw-portfolio-preview {
        grid-template-columns: 1fr !important;
    }
    .tb4-canvas[data-device="tablet"] .tb4-fw-portfolio-preview {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* Canvas Drop Zone - Catppuccin Mocha Colors */
    .tb4-canvas-dropzone {
        min-height: 200px;
        border: 2px dashed #585b70; /* Catppuccin overlay0 */
        border-radius: 8px;
        margin: 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #a6adc8; /* Catppuccin subtext0 */
        transition: all 0.15s;
    }

    .tb4-canvas-dropzone:hover,
    .tb4-canvas-dropzone.drag-over {
        border-color: #89b4fa; /* Catppuccin blue */
        background: rgba(137, 180, 250, 0.1);
    }

    .tb4-dropzone-icon {
        width: 48px;
        height: 48px;
        opacity: 0.4;
    }

    .tb4-dropzone-text {
        font-size: 14px;
        font-weight: 500;
    }

    .tb4-dropzone-hint {
        font-size: 12px;
        opacity: 0.7;
    }

    /* Canvas Sections */
    .tb4-canvas-section {
        position: relative;
        border: 1px solid transparent;
        transition: border-color 0.15s;
    }

    .tb4-canvas-section:hover {
        border-color: var(--tb4-primary-300);
    }

    .tb4-canvas-section.selected {
        border-color: var(--tb4-primary-500);
    }

    .tb4-section-controls {
        position: absolute;
        top: -1px;
        left: 50%;
        transform: translateX(-50%) translateY(-100%);
        display: none;
        background: var(--tb4-primary-600);
        border-radius: 4px 4px 0 0;
        padding: 4px 8px;
        gap: 4px;
    }

    .tb4-canvas-section:hover .tb4-section-controls,
    .tb4-canvas-section.selected .tb4-section-controls {
        display: flex;
    }

    .tb4-section-control {
        padding: 4px;
        background: transparent;
        border: none;
        color: white;
        cursor: pointer;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tb4-section-control:hover {
        background: rgba(255,255,255,0.2);
    }

    /* ═══════════════════════════════════════════════════════════════════
       BOTTOM BAR
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-bottombar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: var(--tb4-bottombar-height);
        background: var(--tb4-bg-primary);
        border-top: 1px solid var(--tb4-border-default);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        z-index: 1000;
    }

    .tb4-bottombar-section {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tb4-zoom-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tb4-zoom-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tb4-bg-secondary);
        border: 1px solid var(--tb4-border-default);
        border-radius: 4px;
        color: var(--tb4-text-secondary);
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-zoom-btn:hover {
        background: var(--tb4-bg-tertiary);
        border-color: var(--tb4-primary-400);
    }

    .tb4-zoom-value {
        font-size: 12px;
        font-weight: 500;
        color: var(--tb4-text-secondary);
        min-width: 44px;
        text-align: center;
    }

    .tb4-responsive-btns {
        display: flex;
        gap: 4px;
    }

    .tb4-responsive-btn {
        padding: 6px 10px;
        background: var(--tb4-bg-secondary);
        border: 1px solid var(--tb4-border-default);
        border-radius: 4px;
        color: var(--tb4-text-tertiary);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        transition: all 0.15s;
    }

    .tb4-responsive-btn:hover {
        border-color: var(--tb4-primary-400);
        color: var(--tb4-text-primary);
    }

    .tb4-responsive-btn.active {
        background: var(--tb4-primary-600);
        border-color: var(--tb4-primary-600);
        color: white;
    }

    .tb4-page-info {
        font-size: 12px;
        color: var(--tb4-text-tertiary);
    }

    .tb4-page-info strong {
        color: var(--tb4-text-secondary);
    }

    /* ═══════════════════════════════════════════════════════════════════
       TOAST NOTIFICATIONS
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-toast {
        position: fixed;
        bottom: calc(var(--tb4-bottombar-height) + 16px);
        right: 16px;
        padding: 12px 20px;
        background: var(--tb4-neutral-800);
        color: white;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s;
        z-index: 2000;
    }

    .tb4-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .tb4-toast.success {
        background: var(--tb4-success);
    }

    .tb4-toast.error {
        background: var(--tb4-error);
    }

    /* ═══════════════════════════════════════════════════════════════════
       LAYOUT PICKER MODAL - Dark Theme
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-layout-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }

    .tb4-layout-modal.active {
        display: flex;
    }

    .tb4-layout-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .tb4-layout-modal-content {
        position: relative;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        animation: modalSlideIn 0.2s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .tb4-layout-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: #0f172a;
        border-bottom: 1px solid #334155;
    }

    .tb4-layout-modal-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #f1f5f9;
        margin: 0;
    }

    .tb4-layout-modal-close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: transparent;
        border: none;
        border-radius: 6px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-layout-modal-close:hover {
        background: #334155;
        color: #f1f5f9;
    }

    .tb4-layout-modal-body {
        padding: 20px;
        overflow-y: auto;
        max-height: calc(80vh - 60px);
    }

    .tb4-layout-group {
        margin-bottom: 20px;
    }

    .tb4-layout-group:last-child {
        margin-bottom: 0;
    }

    .tb4-layout-group-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 12px;
    }

    .tb4-layout-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tb4-layout-choice {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 12px;
        background: #0f172a;
        border: 2px solid #334155;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s;
        min-width: 100px;
    }

    .tb4-layout-choice:hover {
        border-color: #3b82f6;
        background: #1e3a5f;
    }

    .tb4-layout-choice:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }

    .tb4-layout-preview {
        display: flex;
        gap: 3px;
        width: 80px;
        height: 40px;
        background: #334155;
        border-radius: 4px;
        padding: 4px;
        overflow: hidden;
    }

    .tb4-layout-col {
        background: #3b82f6;
        border-radius: 2px;
        height: 100%;
        transition: background-color 0.15s;
    }

    .tb4-layout-choice:hover .tb4-layout-col {
        background: #60a5fa;
    }

    .tb4-layout-label {
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
    }

    .tb4-layout-choice:hover .tb4-layout-label {
        color: #e2e8f0;
    }

    /* ═══════════════════════════════════════════════════════════════════
       CANVAS SECTIONS & ROWS - Enhanced styling
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-section {
        position: relative;
        padding: 20px;
        border: 2px dashed transparent;
        margin: 16px;
        border-radius: 8px;
        transition: all 0.15s;
        background: rgba(255, 255, 255, 0.02);
    }

    .tb4-section:hover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.05);
    }

    .tb4-section.tb4-selected {
        border-color: #3b82f6;
        border-style: solid;
        background: rgba(59, 130, 246, 0.08);
    }

    .tb4-section-inner {
        min-height: 60px;
    }

    .tb4-element-actions {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        display: none;
        background: #3b82f6;
        border-radius: 6px;
        padding: 4px 8px;
        gap: 4px;
        z-index: 100;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .tb4-section:hover > .tb4-element-actions,
    .tb4-row:hover > .tb4-element-actions,
    .tb4-module:hover > .tb4-element-actions {
        display: flex;
    }

    .tb4-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: transparent;
        border: none;
        border-radius: 4px;
        color: white;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.15s;
    }

    .tb4-action-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .tb4-action-delete:hover {
        background: #ef4444;
    }

    .tb4-add-row-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        margin-top: 12px;
        background: rgba(59, 130, 246, 0.1);
        border: 2px dashed #3b82f6;
        border-radius: 6px;
        color: #3b82f6;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-add-row-btn:hover {
        background: rgba(59, 130, 246, 0.2);
        border-style: solid;
    }

    .tb4-row {
        position: relative;
        display: flex;
        gap: 12px;
        padding: 12px;
        margin-bottom: 12px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px dashed #475569;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .tb4-row:hover {
        border-color: #60a5fa;
        background: rgba(96, 165, 250, 0.05);
    }

    .tb4-row.tb4-selected {
        border-color: #60a5fa;
        border-style: solid;
    }

    .tb4-row-inner {
        display: flex;
        gap: 12px;
        width: 100%;
    }

    .tb4-column {
        position: relative;
        min-height: 80px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px dashed #475569;
        border-radius: 4px;
        transition: all 0.15s;
        box-sizing: border-box;
    }

    .tb4-column:hover {
        border-color: #818cf8;
        background: rgba(129, 140, 248, 0.05);
    }

    .tb4-column-inner {
        padding: 12px;
        min-height: 60px;
    }

    /* Column width classes based on data-width attribute */
    .tb4-column[data-width="1"] {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .tb4-column[data-width="1_2"] {
        flex: 0 0 calc(50% - 6px);
        max-width: calc(50% - 6px);
    }

    .tb4-column[data-width="1_3"] {
        flex: 0 0 calc(33.333% - 8px);
        max-width: calc(33.333% - 8px);
    }

    .tb4-column[data-width="2_3"] {
        flex: 0 0 calc(66.666% - 4px);
        max-width: calc(66.666% - 4px);
    }

    .tb4-column[data-width="1_4"] {
        flex: 0 0 calc(25% - 9px);
        max-width: calc(25% - 9px);
    }

    .tb4-column[data-width="3_4"] {
        flex: 0 0 calc(75% - 3px);
        max-width: calc(75% - 3px);
    }

    /* Column widths based on data-col-width attribute (percentage values) */
    /* Full width */
    .tb4-column[data-col-width="100"] { flex: 0 0 100%; max-width: 100%; }
    
    /* Half widths */
    .tb4-column[data-col-width="50"] { flex: 0 0 calc(50% - 6px); max-width: calc(50% - 6px); }
    
    /* Thirds */
    .tb4-column[data-col-width="33.33"] { flex: 0 0 calc(33.33% - 8px); max-width: calc(33.33% - 8px); }
    .tb4-column[data-col-width="66.67"] { flex: 0 0 calc(66.67% - 4px); max-width: calc(66.67% - 4px); }
    
    /* Quarters */
    .tb4-column[data-col-width="25"] { flex: 0 0 calc(25% - 9px); max-width: calc(25% - 9px); }
    .tb4-column[data-col-width="75"] { flex: 0 0 calc(75% - 3px); max-width: calc(75% - 3px); }
    
    /* Fifths */
    .tb4-column[data-col-width="20"] { flex: 0 0 calc(20% - 10px); max-width: calc(20% - 10px); }
    .tb4-column[data-col-width="40"] { flex: 0 0 calc(40% - 7px); max-width: calc(40% - 7px); }
    .tb4-column[data-col-width="60"] { flex: 0 0 calc(60% - 5px); max-width: calc(60% - 5px); }
    .tb4-column[data-col-width="80"] { flex: 0 0 calc(80% - 2px); max-width: calc(80% - 2px); }

    /* =============================================
       RESPONSIVE COLUMN STACKING - MUST BE AFTER BASE STYLES
       ============================================= */
    .tb4-canvas[data-device="tablet"] .tb4-row-inner {
        flex-wrap: wrap;
    }
    .tb4-canvas[data-device="tablet"] .tb4-column {
        flex: 0 0 calc(50% - 6px) !important;
        max-width: calc(50% - 6px) !important;
    }

    .tb4-canvas[data-device="mobile"] .tb4-row-inner {
        flex-direction: column;
    }
    .tb4-canvas[data-device="mobile"] .tb4-column {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .tb4-module-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60px;
        color: #64748b;
        font-size: 12px;
        border: 1px dashed #475569;
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.15s;
    }

    .tb4-module-placeholder:hover {
        border-color: #60a5fa;
        background: rgba(96, 165, 250, 0.1);
        color: #60a5fa;
    }

    .tb4-module-placeholder.drag-over {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .tb4-add-module-zone {
        min-height: 40px;
        margin-top: 8px;
        opacity: 0.6;
    }

    .tb4-add-module-zone:hover {
        opacity: 1;
    }

    /* Module content styles */
    .tb4-module {
        position: relative;
        padding: 12px;
        margin-bottom: 8px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid transparent;
        border-radius: 4px;
        transition: all 0.15s;
    }

    .tb4-module:hover {
        border-color: #60a5fa;
        background: rgba(96, 165, 250, 0.05);
    }

    .tb4-module.tb4-selected {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .tb4-module-content {
        color: #e2e8f0;
    }

    /* Icon module preview */
    .tb4-icon-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60px;
    }

    .tb4-icon-preview svg {
        display: block;
        max-width: 100%;
        height: auto;
    }

    /* Ensure icon module stays in container */
    .tb4-module-icon .tb4-module-content,
    .tb4-module[data-module-type="icon"] .tb4-module-content {
        position: relative;
        overflow: visible;
    }

    /* Icon picker modal */
    .tb4-icon-picker-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }

    .tb4-icon-picker-modal .tb4-modal-content {
        background: #1e293b;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .tb4-icon-picker-modal .tb4-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #334155;
    }

    .tb4-icon-picker-modal .tb4-modal-header h3 {
        margin: 0;
        color: #f1f5f9;
        font-size: 16px;
    }

    .tb4-icon-picker-modal .tb4-modal-close {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .tb4-icon-picker-modal .tb4-modal-close:hover {
        color: #f1f5f9;
    }

    .tb4-icon-picker-modal .tb4-modal-body {
        padding: 20px;
        overflow-y: auto;
        max-height: calc(80vh - 120px);
    }

    .tb4-icon-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    }

    .tb4-icon-option {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 1;
        background: #334155;
        border: 2px solid transparent;
        border-radius: 6px;
        cursor: pointer;
        padding: 8px;
        transition: all 0.15s;
    }

    .tb4-icon-option:hover {
        background: #475569;
        border-color: #60a5fa;
    }

    .tb4-icon-option svg {
        width: 24px;
        height: 24px;
        color: #e2e8f0;
    }

    .tb4-icon-custom {
        display: flex;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #334155;
    }

    .tb4-icon-custom .tb4-input {
        flex: 1;
    }

    .tb4-module-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        color: white;
        opacity: 0.8;
        margin-right: 8px;
    }

    /* Text module */
    .tb4-text-content {
        line-height: 1.6;
    }

    .tb4-text-content p {
        margin: 0 0 1em 0;
    }

    /* Button module */
    .tb4-button {
        display: inline-block;
        padding: 10px 20px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: background 0.15s;
    }

    .tb4-button:hover {
        background: #2563eb;
    }

    .tb4-button-secondary {
        background: #475569;
    }

    .tb4-button-secondary:hover {
        background: #334155;
    }

    /* Divider module */
    .tb4-divider {
        border: none;
        border-top: 1px solid #475569;
        margin: 16px 0;
    }

    .tb4-divider-dashed {
        border-top-style: dashed;
    }

    .tb4-divider-dotted {
        border-top-style: dotted;
    }

    /* Blurb module */
    .tb4-blurb {
        text-align: center;
        padding: 16px;
    }

    .tb4-blurb-icon {
        font-size: 32px;
        color: #3b82f6;
        margin-bottom: 12px;
    }

    .tb4-blurb-title {
        font-size: 18px;
        font-weight: 600;
        color: #f1f5f9;
        margin: 0 0 8px 0;
    }

    .tb4-blurb-text {
        font-size: 14px;
        color: #94a3b8;
        margin: 0;
        line-height: 1.5;
    }

    /* Hero module */
    .tb4-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 8px;
        overflow: hidden;
    }

    .tb4-hero__overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        pointer-events: none;
    }

    .tb4-hero__container {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 800px;
        padding: 40px 20px;
        text-align: center;
    }

    .tb4-hero__title {
        font-size: 32px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 12px 0;
        line-height: 1.2;
    }

    .tb4-hero__subtitle {
        font-size: 18px;
        color: #e2e8f0;
        margin: 0 0 16px 0;
        line-height: 1.4;
    }

    .tb4-hero__description {
        font-size: 14px;
        color: #cbd5e1;
        margin: 0 0 24px 0;
        line-height: 1.6;
    }

    .tb4-hero__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }

    .tb4-hero__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tb4-hero__btn--primary {
        background: #2563eb;
        color: #ffffff;
        border: none;
    }

    .tb4-hero__btn--primary:hover {
        background: #1d4ed8;
    }

    .tb4-hero__btn--secondary {
        background: transparent;
        color: #ffffff;
        border: 2px solid #ffffff;
    }

    .tb4-hero__btn--secondary:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* CTA module */
    .tb4-cta {
        position: relative;
        display: block;
        border-radius: 12px;
        overflow: hidden;
    }

    .tb4-cta__container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .tb4-cta__container--horizontal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
    }

    .tb4-cta__container--stacked {
        text-align: center;
    }

    .tb4-cta__content {
        flex: 1;
        min-width: 280px;
    }

    .tb4-cta__title {
        font-size: 28px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 8px 0;
        line-height: 1.3;
    }

    .tb4-cta__description {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        line-height: 1.6;
    }

    .tb4-cta__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .tb4-cta__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tb4-cta__btn--primary {
        background: #ffffff;
        color: #2563eb;
        border: none;
    }

    .tb4-cta__btn--primary:hover {
        background: #f1f5f9;
        transform: translateY(-1px);
    }

    .tb4-cta__btn--secondary {
        background: transparent;
        color: #ffffff;
        border: 2px solid #ffffff;
    }

    .tb4-cta__btn--secondary:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Testimonial module */
    .tb4-testimonial {
        position: relative;
        display: block;
    }

    .tb4-testimonial--card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .tb4-testimonial--minimal {
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .tb4-testimonial--large-quote {
        text-align: center;
    }

    .tb4-testimonial__quote {
        margin-bottom: 16px;
    }

    .tb4-testimonial__text {
        font-size: 18px;
        line-height: 1.6;
        color: #374151;
        margin: 0;
    }

    .tb4-testimonial__marks {
        font-family: Georgia, serif;
        font-size: 48px;
        line-height: 1;
        color: #3b82f6;
        opacity: 0.8;
    }

    .tb4-testimonial__marks--open {
        margin-right: 4px;
    }

    .tb4-testimonial__marks--close {
        margin-left: 4px;
    }

    .tb4-testimonial--large-quote .tb4-testimonial__marks {
        display: block;
        margin: 0 auto 16px auto;
    }

    .tb4-testimonial--large-quote .tb4-testimonial__marks--close {
        margin: 16px auto 0 auto;
    }

    .tb4-testimonial__rating {
        display: flex;
        gap: 4px;
        margin-top: 16px;
    }

    .tb4-testimonial--large-quote .tb4-testimonial__rating {
        justify-content: center;
    }

    .tb4-testimonial__author {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 24px;
    }

    .tb4-testimonial--large-quote .tb4-testimonial__author {
        justify-content: center;
    }

    .tb4-testimonial__avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .tb4-testimonial__avatar--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #3b82f6;
        color: white;
        font-weight: 600;
        font-size: 24px;
    }

    .tb4-testimonial__info {
        text-align: left;
    }

    .tb4-testimonial--large-quote .tb4-testimonial__info {
        text-align: center;
    }

    .tb4-testimonial__name {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .tb4-testimonial__title {
        font-size: 14px;
        color: #6b7280;
        margin: 4px 0 0 0;
    }

    .tb4-testimonial__link {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }

    .tb4-testimonial:hover {
        cursor: pointer;
    }

    .tb4-testimonial:has(.tb4-testimonial__link):hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease;
    }

    /* Gallery module */
    .tb4-gallery {
        position: relative;
        display: block;
    }

    .tb4-gallery--empty {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        background: rgba(0, 0, 0, 0.1);
        border: 2px dashed #475569;
        border-radius: 8px;
        color: #94a3b8;
    }

    .tb4-gallery__grid {
        display: grid;
        width: 100%;
    }

    .tb4-gallery__item {
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .tb4-gallery__image-wrapper {
        position: relative;
        overflow: hidden;
    }

    .tb4-gallery__image {
        display: block;
        width: 100%;
        height: auto;
        transition: transform 0.3s ease;
    }

    .tb4-gallery__overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0);
        transition: background 0.3s ease;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        pointer-events: none;
    }

    .tb4-gallery__caption {
        width: 100%;
        text-align: center;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    /* Hover effects */
    .tb4-gallery--hover-zoom .tb4-gallery__item:hover .tb4-gallery__image {
        transform: scale(1.1);
    }

    .tb4-gallery--hover-darken .tb4-gallery__item:hover .tb4-gallery__overlay {
        background: rgba(0, 0, 0, 0.4);
    }

    .tb4-gallery--hover-caption-slide .tb4-gallery__item:hover .tb4-gallery__overlay {
        background: rgba(0, 0, 0, 0.3);
    }

    /* Caption visibility */
    .tb4-gallery--captions-on-hover .tb4-gallery__item:hover .tb4-gallery__caption {
        transform: translateY(0) !important;
        opacity: 1 !important;
    }

    .tb4-gallery--captions-below .tb4-gallery__caption {
        position: relative;
        transform: none;
        opacity: 1;
    }

    /* Carousel specific */
    .tb4-gallery--carousel .tb4-gallery__grid {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .tb4-gallery--carousel .tb4-gallery__grid::-webkit-scrollbar {
        display: none;
    }

    .tb4-gallery--carousel .tb4-gallery__item {
        flex-shrink: 0;
        scroll-snap-align: start;
    }

    .tb4-gallery__nav {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
    }

    .tb4-gallery__nav-prev,
    .tb4-gallery__nav-next {
        padding: 8px 16px;
        background: #334155;
        border: none;
        border-radius: 6px;
        color: #e2e8f0;
        cursor: pointer;
        transition: background 0.2s;
    }

    .tb4-gallery__nav-prev:hover,
    .tb4-gallery__nav-next:hover {
        background: #475569;
    }

    .tb4-gallery__pagination {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tb4-gallery__dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #475569;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .tb4-gallery__dot.active,
    .tb4-gallery__dot:hover {
        background: #3b82f6;
    }

    .tb4-gallery__page-num {
        min-width: 28px;
        padding: 4px 8px;
        background: #475569;
        border: none;
        border-radius: 4px;
        color: #ffffff;
        font-size: 12px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .tb4-gallery__page-num.active,
    .tb4-gallery__page-num:hover {
        background: #3b82f6;
    }

    /* Lightbox */
    .tb4-lightbox {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 100000;
        background: rgba(0, 0, 0, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .tb4-lightbox.active {
        opacity: 1;
        visibility: visible;
    }

    .tb4-lightbox__content {
        position: relative;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .tb4-lightbox__image {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 4px;
    }

    .tb4-lightbox__caption {
        margin-top: 16px;
        color: #ffffff;
        font-size: 16px;
        text-align: center;
        max-width: 600px;
    }

    .tb4-lightbox__close {
        position: absolute;
        top: -40px;
        right: 0;
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        border-radius: 50%;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .tb4-lightbox__close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .tb4-lightbox__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        border-radius: 50%;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .tb4-lightbox__nav:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .tb4-lightbox__nav--prev {
        left: 20px;
    }

    .tb4-lightbox__nav--next {
        right: 20px;
    }

    .tb4-lightbox__counter {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        color: #94a3b8;
        font-size: 14px;
    }

    /* Video module */
    .tb4-video {
        position: relative;
        display: block;
    }

    .tb4-video--empty,
    .tb4-video--error {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        background: rgba(0, 0, 0, 0.1);
        border: 2px dashed #475569;
        border-radius: 8px;
        color: #94a3b8;
        padding: 20px;
        text-align: center;
    }

    .tb4-video--error {
        border-color: #ef4444;
        color: #fca5a5;
    }

    .tb4-video__wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #0f172a;
    }

    .tb4-video__poster-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .tb4-video__poster {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tb4-video__poster-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1e293b, #0f172a);
    }

    .tb4-video__overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .tb4-video__overlay:hover {
        background: rgba(0, 0, 0, 0.5) !important;
    }

    .tb4-video__play-btn {
        background: none;
        border: none;
        cursor: pointer;
        transition: transform 0.2s ease;
        z-index: 10;
    }

    .tb4-video__play-btn:hover {
        transform: scale(1.1);
    }

    .tb4-video__play-btn:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 4px;
        border-radius: 50%;
    }

    .tb4-video__play-icon {
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
    }

    .tb4-video__iframe,
    .tb4-video__player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .tb4-video__caption {
        margin-top: 12px;
        line-height: 1.5;
    }

    /* Video loading state */
    .tb4-video--loading .tb4-video__overlay::after {
        content: '';
        position: absolute;
        width: 48px;
        height: 48px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: tb4-video-spin 0.8s linear infinite;
    }

    @keyframes tb4-video-spin {
        to { transform: rotate(360deg); }
    }

    /* Video responsive adjustments */
    @media (max-width: 768px) {
        .tb4-video__play-icon--default {
            width: 60px;
            height: 60px;
        }
        .tb4-video__play-icon--youtube {
            width: 54px;
            height: 38px;
        }
        .tb4-video__play-icon--minimal {
            width: 48px;
            height: 48px;
        }
    }

    /* Audio module */
    .tb4-audio {
        position: relative;
        display: block;
    }

    .tb4-audio--empty,
    .tb4-audio--error {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100px;
        background: rgba(0, 0, 0, 0.1);
        border: 2px dashed #475569;
        border-radius: 12px;
        color: #94a3b8;
        padding: 20px;
        text-align: center;
    }

    .tb4-audio--error {
        border-color: #ef4444;
        color: #fca5a5;
    }

    .tb4-audio__wrapper {
        position: relative;
        width: 100%;
    }

    .tb4-audio__header {
        display: flex;
        gap: 16px;
        align-items: center;
        margin-bottom: 16px;
    }

    .tb4-audio__cover {
        flex-shrink: 0;
        overflow: hidden;
    }

    .tb4-audio__cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tb4-audio__cover--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tb4-audio__info {
        flex: 1;
        min-width: 0;
    }

    .tb4-audio__title {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tb4-audio__artist,
    .tb4-audio__album {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tb4-audio__controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tb4-audio__play-btn {
        flex-shrink: 0;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, opacity 0.2s;
    }

    .tb4-audio__play-btn:hover {
        transform: scale(1.05);
    }

    .tb4-audio__play-btn:active {
        transform: scale(0.95);
    }

    .tb4-audio__play-btn:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }

    .tb4-audio--playing .tb4-audio__play-icon {
        display: none;
    }

    .tb4-audio--playing .tb4-audio__pause-icon {
        display: block !important;
    }

    .tb4-audio__progress-container {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tb4-audio__progress {
        flex: 1;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        cursor: pointer;
        position: relative;
    }

    .tb4-audio__progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.1s;
        pointer-events: none;
    }

    .tb4-audio__progress-handle {
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 12px;
        height: 12px;
        border-radius: 50%;
        opacity: 0;
        transition: opacity 0.2s;
        pointer-events: none;
    }

    .tb4-audio__progress:hover .tb4-audio__progress-handle {
        opacity: 1;
    }

    .tb4-audio__time-current,
    .tb4-audio__time-duration {
        font-size: 12px;
        min-width: 40px;
        font-variant-numeric: tabular-nums;
    }

    .tb4-audio__volume {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tb4-audio__volume-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        display: flex;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .tb4-audio__volume-btn:hover {
        opacity: 1;
    }

    .tb4-audio__volume-slider {
        width: 60px;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
        cursor: pointer;
    }

    .tb4-audio__volume-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e2e8f0;
        cursor: pointer;
    }

    .tb4-audio__volume-slider::-moz-range-thumb {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e2e8f0;
        cursor: pointer;
        border: none;
    }

    .tb4-audio__download-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        display: flex;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .tb4-audio__download-btn:hover {
        opacity: 1;
    }

    .tb4-audio__minimal {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tb4-audio__embed {
        display: block;
        width: 100%;
    }

    .tb4-audio__description {
        line-height: 1.5;
    }

    /* Audio responsive adjustments */
    @media (max-width: 768px) {
        .tb4-audio__header {
            gap: 12px;
        }

        .tb4-audio__volume {
            display: none;
        }

        .tb4-audio__controls {
            gap: 8px;
        }

        .tb4-audio__time-current,
        .tb4-audio__time-duration {
            font-size: 11px;
            min-width: 32px;
        }
    }

    @media (max-width: 480px) {
        .tb4-audio--card .tb4-audio__header {
            flex-direction: column;
            align-items: flex-start;
        }

        .tb4-audio--minimal .tb4-audio__title {
            display: none;
        }
    }

    /* Image placeholder */
    .tb4-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100px;
        background: rgba(0, 0, 0, 0.2);
        border: 1px dashed #475569;
        border-radius: 4px;
        color: #64748b;
        cursor: pointer;
    }

    .tb4-image-placeholder:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .tb4-add-row {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        color: #64748b;
        font-size: 13px;
        cursor: pointer;
        border: 2px dashed #475569;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .tb4-add-row:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        background: rgba(59, 130, 246, 0.05);
    }

    /* Empty state styling */
    .tb4-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        color: #a6adc8;
        text-align: center;
        padding: 40px;
    }

    .tb4-empty-icon {
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .tb4-empty-state p {
        margin-bottom: 20px;
        font-size: 14px;
    }

    /* Drag over states */
    .drag-over {
        border-color: #3b82f6 !important;
        background: rgba(59, 130, 246, 0.15) !important;
        box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
    }

    /* Enhanced drop zone styling for module placeholders */
    .tb4-module-placeholder[data-drop-zone="module"],
    .tb4-column-inner[data-drop-zone="module"] {
        position: relative;
        min-height: 60px;
        transition: all 0.2s ease;
    }

    .tb4-module-placeholder[data-drop-zone="module"]:hover,
    .tb4-column-inner[data-drop-zone="module"]:hover {
        border-color: #60a5fa;
        background: rgba(96, 165, 250, 0.08);
    }

    /* Active dragging state for modules in sidebar */
    .tb4-module-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }

    /* Visual feedback when dragging over canvas */
    .tb4-canvas.drag-active .tb4-module-placeholder {
        border-color: #60a5fa;
        background: rgba(96, 165, 250, 0.1);
    }

    /* Pulsing animation for empty drop zones during drag */
    @keyframes pulse-border {
        0%, 100% { border-color: #475569; }
        50% { border-color: #60a5fa; }
    }

    .tb4-canvas.drag-active .tb4-module-placeholder[data-drop-zone] {
        animation: pulse-border 1.5s ease-in-out infinite;
    }

    /* Ensure drop zones capture pointer events */
    [data-drop-zone] {
        pointer-events: auto !important;
    }

    [data-drop-zone] * {
        pointer-events: auto;
    }
    </style>
</head>
<body>
    <!-- TOP TOOLBAR -->
    <div class="tb4-toolbar" id="tb4-toolbar">
        <div class="tb4-toolbar-section tb4-toolbar-left">
            <a href="/admin/tb4-builder" class="tb4-btn tb4-btn-icon" title="Exit Builder">
                <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
            </a>
            <div class="tb4-divider"></div>
            <a href="/admin/tb4-builder" class="tb4-logo">
                <div class="tb4-logo-icon">TB</div>
                <span>Theme Builder 4</span>
            </a>
        </div>

        <div class="tb4-toolbar-section tb4-toolbar-center">
            <input type="text"
                   class="tb4-page-title-input"
                   id="pageTitle"
                   value="<?= $page_title ?>"
                   placeholder="Page Title">
            <span class="tb4-status-badge <?= $page_status ?>"><?= ucfirst($page_status) ?></span>
            <div class="tb4-divider"></div>
            <div class="tb4-device-switcher">
                <button class="tb4-device-btn active" data-device="desktop" title="Desktop" onclick="TB4Builder.setDevice('desktop')">
                    <i data-lucide="monitor" style="width:18px;height:18px;"></i>
                </button>
                <button class="tb4-device-btn" data-device="tablet" title="Tablet" onclick="TB4Builder.setDevice('tablet')">
                    <i data-lucide="tablet" style="width:18px;height:18px;"></i>
                </button>
                <button class="tb4-device-btn" data-device="mobile" title="Mobile" onclick="TB4Builder.setDevice('mobile')">
                    <i data-lucide="smartphone" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </div>

        <div class="tb4-toolbar-section tb4-toolbar-right">
            <button class="tb4-btn tb4-btn-icon" id="btnUndo" data-action="undo" title="Undo (Ctrl+Z)" disabled>
                <i data-lucide="undo-2" style="width:18px;height:18px;"></i>
            </button>
            <button class="tb4-btn tb4-btn-icon" id="btnRedo" data-action="redo" title="Redo (Ctrl+Y)" disabled>
                <i data-lucide="redo-2" style="width:18px;height:18px;"></i>
            </button>
            <div class="tb4-divider"></div>
            <button class="tb4-btn" id="btnPreview" data-action="preview" title="Preview Page">
                <i data-lucide="eye" style="width:16px;height:16px;"></i>
                Preview
            </button>
            <!-- Save Dropdown -->
            <div class="tb4-save-dropdown" id="saveDropdown">
                <button class="tb4-btn tb4-btn-primary tb4-save-main" id="btnSave" onclick="TB4Builder.save()">
                    <i data-lucide="save" style="width:16px;height:16px;"></i>
                    <span id="saveButtonText"><?= $page_status === "published" ? "Update" : "Save Draft" ?></span>
                </button>
                <button class="tb4-btn tb4-btn-primary tb4-save-toggle" onclick="TB4Builder.toggleSaveMenu()" title="More options">
                    <i data-lucide="chevron-down" style="width:14px;height:14px;"></i>
                </button>
                <div class="tb4-save-menu" id="saveMenu">
                    <button class="tb4-save-option" onclick="TB4Builder.saveAs('draft')">
                        <i data-lucide="file-edit" style="width:16px;height:16px;"></i>
                        Save as Draft
                    </button>
                    <button class="tb4-save-option" onclick="TB4Builder.saveAs('published')">
                        <i data-lucide="globe" style="width:16px;height:16px;"></i>
                        Save & Publish
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN BUILDER -->
    <div class="tb4-builder" data-page-id="<?= $page_id ?>">
        <div class="tb4-workspace">
            <!-- LEFT SIDEBAR -->
            <div class="tb4-sidebar" id="tb4-sidebar">
                <div class="tb4-sidebar-tabs">
                    <button class="tb4-sidebar-tab active" data-sidebar-tab="modules">
                        <i data-lucide="layout-grid" class="tb4-sidebar-tab-icon"></i>
                        Modules
                    </button>
                    <button class="tb4-sidebar-tab" data-sidebar-tab="layers">
                        <i data-lucide="layers" class="tb4-sidebar-tab-icon"></i>
                        Layers
                    </button>
                    <button class="tb4-sidebar-tab" data-sidebar-tab="settings">
                        <i data-lucide="settings" class="tb4-sidebar-tab-icon"></i>
                        Settings
                    </button>
                </div>

                <div class="tb4-sidebar-content">
                    <!-- MODULES PANEL -->
                    <div class="tb4-panel tb4-modules-panel active" data-sidebar-content="modules" id="tb4-module-list">
                        <div class="tb4-search-wrapper">
                            <i data-lucide="search" class="search-icon"></i>
                            <input type="text"
                                   class="tb4-modules-search"
                                   id="moduleSearch"
                                   placeholder="Search modules...">
                        </div>

                        <!-- Structure Modules -->
                        <div class="tb4-module-category" data-category="structure">
                            <div class="tb4-category-title">Structure</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_section">
                                    <i data-lucide="square" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Section</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_row">
                                    <i data-lucide="columns" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Row</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_column">
                                    <i data-lucide="panel-left" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Column</div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Modules -->
                        <div class="tb4-module-category" data-category="content">
                            <div class="tb4-category-title">Content</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_text">
                                    <i data-lucide="type" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Text</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_image">
                                    <i data-lucide="image" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Image</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_button">
                                    <i data-lucide="mouse-pointer-click" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Button</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_divider">
                                    <i data-lucide="minus" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Divider</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_blurb">
                                    <i data-lucide="message-square" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Blurb</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_cta">
                                    <i data-lucide="megaphone" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">CTA</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_hero">
                                    <i data-lucide="layout-template" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Hero</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_testimonial">
                                    <i data-lucide="quote" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Testimonial</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_team">
                                    <i data-lucide="users" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Team Member</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_code">
                                    <i data-lucide="code" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Code</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_icon">
                                    <i data-lucide="shapes" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Icon</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_social">
                                    <i data-lucide="share-2" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Social Links</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_search">
                                    <i data-lucide="search" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Search</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_login">
                                    <i data-lucide="log-in" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Login</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_signup">
                                    <i data-lucide="mail" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Email Signup</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_number">
                                    <i data-lucide="hash" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Number Counter</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_circle">
                                    <i data-lucide="circle-dot" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Circle Counter</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_countdown">
                                    <i data-lucide="timer" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Countdown</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_progress">
                                    <i data-lucide="minus" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Progress Bar</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_blog">
                                    <i data-lucide="newspaper" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Blog</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_portfolio">
                                    <i data-lucide="layout-grid" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Portfolio</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_post_slider">
                                    <i data-lucide="gallery-horizontal" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Post Slider</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_post_title">
                                    <i data-lucide="heading" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Post Title</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_post_content">
                                    <i data-lucide="file-text" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Post Content</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_comments">
                                    <i data-lucide="message-circle" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Comments</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_post_nav">
                                    <i data-lucide="arrow-left-right" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Post Navigation</div>
                                </div>
                            </div>
                        </div>

                        <!-- Media Modules -->
                        <div class="tb4-module-category" data-category="media">
                            <div class="tb4-category-title">Media</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_gallery">
                                    <i data-lucide="grid-3x3" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Gallery</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_video">
                                    <i data-lucide="play-circle" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Video</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_audio">
                                    <i data-lucide="volume-2" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Audio</div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Modules -->
                        <div class="tb4-module-category" data-category="interactive">
                            <div class="tb4-category-title">Interactive</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_toggle">
                                    <i data-lucide="chevrons-down-up" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Toggle</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_accordion">
                                    <i data-lucide="list" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Accordion</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_tabs">
                                    <i data-lucide="layout-list" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Tabs</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_slider">
                                    <i data-lucide="images" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Slider</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_contact">
                                    <i data-lucide="mail" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Contact Form</div>
                                </div>
                            </div>
                        </div>

                        <!-- Commerce Modules -->
                        <div class="tb4-module-category" data-category="commerce">
                            <div class="tb4-category-title">Commerce</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_pricing">
                                    <i data-lucide="credit-card" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Pricing Table</div>
                                </div>
                            </div>
                        </div>

                        <!-- Fullwidth Modules -->
                        <div class="tb4-module-category" data-category="fullwidth">
                            <div class="tb4-category-title">Fullwidth</div>
                            <div class="tb4-modules-grid">
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_header">
                                    <i data-lucide="layout-template" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Header</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_image">
                                    <i data-lucide="image" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Image</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_slider">
                                    <i data-lucide="gallery-horizontal" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Slider</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_map">
                                    <i data-lucide="map-pin" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Map</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_menu">
                                    <i data-lucide="menu" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Menu</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_portfolio">
                                    <i data-lucide="layout-grid" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Portfolio</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_post_slider">
                                    <i data-lucide="newspaper" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Post Slider</div>
                                </div>
                                <div class="tb4-module-item" draggable="true" data-module-type="tb4_fw_code">
                                    <i data-lucide="terminal" class="tb4-module-icon"></i>
                                    <div class="tb4-module-name">Fullwidth Code</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LAYERS PANEL -->
                    <div class="tb4-panel tb4-layers-panel" data-sidebar-content="layers">
                        <ul class="tb4-layers-list" id="layersList">
                            <!-- Layers populated by JavaScript -->
                        </ul>
                        <div class="tb4-settings-empty" id="layersEmpty">
                            <i data-lucide="layers" class="tb4-settings-empty-icon"></i>
                            <div class="tb4-settings-empty-text">
                                No layers yet.<br>
                                Add a section to get started.
                            </div>
                        </div>
                    </div>

                    <!-- SETTINGS PANEL -->
                    <div class="tb4-panel tb4-settings-panel" data-sidebar-content="settings" id="tb4-settings-panel">
                        <div class="tb4-settings-empty" id="settingsEmpty">
                            <i data-lucide="mouse-pointer" class="tb4-settings-empty-icon"></i>
                            <div class="tb4-settings-empty-text">
                                Select an element<br>
                                to edit its settings
                            </div>
                        </div>
                        <div id="settingsContent" class="tb4-settings-content" style="display:none;">
                            <div class="tb4-settings-tabs">
                                <button class="tb4-settings-tab active" data-settings-tab="content">Content</button>
                                <button class="tb4-settings-tab" data-settings-tab="design">Design</button>
                                <button class="tb4-settings-tab" data-settings-tab="advanced">Advanced</button>
                            </div>
                            <div class="tb4-settings-body">
                                <div class="tb4-settings-tab-content active" data-settings-content="content">
                                    <!-- Content settings -->
                                </div>
                                <div class="tb4-settings-tab-content" data-settings-content="design">
                                    <!-- Design settings -->
                                </div>
                                <div class="tb4-settings-tab-content" data-settings-content="advanced">
                                    <!-- Advanced settings -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CANVAS -->
            <div class="tb4-canvas-wrapper">
                <div class="tb4-canvas" id="tb4-canvas" data-device="desktop">
                    <div class="tb4-canvas-dropzone" id="mainDropZone" data-drop-zone="section">
                        <i data-lucide="plus-circle" class="tb4-dropzone-icon"></i>
                        <div class="tb4-dropzone-text">Drag a Section here to begin</div>
                        <div class="tb4-dropzone-hint">or click to add a section</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM BAR -->
    <div class="tb4-bottombar">
        <div class="tb4-bottombar-section">
            <div class="tb4-responsive-btns">
                <button class="tb4-responsive-btn active" data-device="desktop" onclick="TB4Builder.setDevice('desktop')">
                    <i data-lucide="monitor" style="width:14px;height:14px;"></i>
                    Desktop
                </button>
                <button class="tb4-responsive-btn" data-device="tablet" onclick="TB4Builder.setDevice('tablet')">
                    <i data-lucide="tablet" style="width:14px;height:14px;"></i>
                    Tablet
                </button>
                <button class="tb4-responsive-btn" data-device="mobile" onclick="TB4Builder.setDevice('mobile')">
                    <i data-lucide="smartphone" style="width:14px;height:14px;"></i>
                    Mobile
                </button>
            </div>
        </div>

        <div class="tb4-bottombar-section">
            <div class="tb4-page-info">
                Page ID: <strong><?= $page_id ?></strong>
                <?php if ($page_slug): ?>
                | Slug: <strong>/<?= $page_slug ?></strong>
                <?php endif; ?>
            </div>
        </div>

        <div class="tb4-bottombar-section">
            <div class="tb4-zoom-controls">
                <button class="tb4-zoom-btn" id="zoomOut" title="Zoom Out">
                    <i data-lucide="minus" style="width:14px;height:14px;"></i>
                </button>
                <span class="tb4-zoom-value" id="tb4-zoom-display">100%</span>
                <button class="tb4-zoom-btn" id="zoomIn" title="Zoom In">
                    <i data-lucide="plus" style="width:14px;height:14px;"></i>
                </button>
                <button class="tb4-zoom-btn" id="zoomReset" title="Reset Zoom">
                    <i data-lucide="maximize" style="width:14px;height:14px;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="tb4-toast" id="toast"></div>

    <!-- LAYOUT PICKER MODAL -->
    <div class="tb4-layout-modal" id="layoutPickerModal">
        <div class="tb4-layout-modal-backdrop"></div>
        <div class="tb4-layout-modal-content">
            <div class="tb4-layout-modal-header">
                <h3>Choose Row Layout</h3>
                <button type="button" class="tb4-layout-modal-close" onclick="TB4Builder.hideLayoutPicker()">
                    <i data-lucide="x" style="width:20px;height:20px;"></i>
                </button>
            </div>
            <div class="tb4-layout-modal-body">
                <!-- 1 Column -->
                <div class="tb4-layout-group">
                    <div class="tb4-layout-group-title">1 Column</div>
                    <div class="tb4-layout-options">
                        <button type="button" class="tb4-layout-choice" data-layout="1" title="100%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:100%"></div>
                            </div>
                            <span class="tb4-layout-label">100%</span>
                        </button>
                    </div>
                </div>

                <!-- 2 Columns -->
                <div class="tb4-layout-group">
                    <div class="tb4-layout-group-title">2 Columns</div>
                    <div class="tb4-layout-options">
                        <button type="button" class="tb4-layout-choice" data-layout="1/2-1/2" title="50% + 50%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:50%"></div>
                                <div class="tb4-layout-col" style="width:50%"></div>
                            </div>
                            <span class="tb4-layout-label">50 / 50</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="1/3-2/3" title="33% + 67%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:33%"></div>
                                <div class="tb4-layout-col" style="width:67%"></div>
                            </div>
                            <span class="tb4-layout-label">33 / 67</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="2/3-1/3" title="67% + 33%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:67%"></div>
                                <div class="tb4-layout-col" style="width:33%"></div>
                            </div>
                            <span class="tb4-layout-label">67 / 33</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="1/4-3/4" title="25% + 75%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:75%"></div>
                            </div>
                            <span class="tb4-layout-label">25 / 75</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="3/4-1/4" title="75% + 25%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:75%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                            </div>
                            <span class="tb4-layout-label">75 / 25</span>
                        </button>
                    </div>
                </div>

                <!-- 3 Columns -->
                <div class="tb4-layout-group">
                    <div class="tb4-layout-group-title">3 Columns</div>
                    <div class="tb4-layout-options">
                        <button type="button" class="tb4-layout-choice" data-layout="1/3-1/3-1/3" title="33% + 33% + 33%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:33%"></div>
                                <div class="tb4-layout-col" style="width:33%"></div>
                                <div class="tb4-layout-col" style="width:33%"></div>
                            </div>
                            <span class="tb4-layout-label">33 / 33 / 33</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="1/4-1/2-1/4" title="25% + 50% + 25%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:50%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                            </div>
                            <span class="tb4-layout-label">25 / 50 / 25</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="1/2-1/4-1/4" title="50% + 25% + 25%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:50%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                            </div>
                            <span class="tb4-layout-label">50 / 25 / 25</span>
                        </button>
                        <button type="button" class="tb4-layout-choice" data-layout="1/4-1/4-1/2" title="25% + 25% + 50%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:50%"></div>
                            </div>
                            <span class="tb4-layout-label">25 / 25 / 50</span>
                        </button>
                    </div>
                </div>

                <!-- 4 Columns -->
                <div class="tb4-layout-group">
                    <div class="tb4-layout-group-title">4 Columns</div>
                    <div class="tb4-layout-options">
                        <button type="button" class="tb4-layout-choice" data-layout="1/4-1/4-1/4-1/4" title="25% + 25% + 25% + 25%">
                            <div class="tb4-layout-preview">
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                                <div class="tb4-layout-col" style="width:25%"></div>
                            </div>
                            <span class="tb4-layout-label">25 / 25 / 25 / 25</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TB4 DATA -->
    <script>
    window.TB4 = {
        pageId: <?= $page_id ?>,
        pageStatus: '<?= $page_status ?>',
        content: <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>,
        csrfToken: '<?= $csrf_token ?>',
        apiUrl: '/admin/api/tb4.php',
        modules: <?= json_encode($available_modules, JSON_UNESCAPED_UNICODE) ?>
    };
    </script>

    <!-- Initialize Lucide Icons with retry -->
    <script>
        function initLucideIcons(retries = 10) {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
                console.log('[TB4] Lucide icons initialized');
            } else if (retries > 0) {
                console.log('[TB4] Waiting for Lucide...', retries);
                setTimeout(() => initLucideIcons(retries - 1), 100);
            } else {
                console.error('[TB4] Failed to load Lucide icons');
            }
        }
        
        // Try immediately
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => initLucideIcons());
        } else {
            initLucideIcons();
        }
        
        // Also try on window load as fallback
        window.addEventListener('load', () => initLucideIcons());
    </script>

    <!-- External Builder JS -->
    <script src="/assets/tb4/js/builder.js" defer></script>

    <!-- Builder initialization handled by external builder.js -->

    <style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Social Links Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-social {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .tb4-social--empty {
        padding: 20px;
        text-align: center;
    }

    .tb4-social__item {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .tb4-social__item:focus {
        outline: 2px solid currentColor;
        outline-offset: 4px;
    }

    .tb4-social__item:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 4px;
    }

    .tb4-social__icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .tb4-social__svg {
        transition: all 0.2s ease;
    }

    .tb4-social__label {
        transition: all 0.2s ease;
    }

    /* Shape Variants */
    .tb4-social__item--circle {
        border-radius: 50%;
    }

    .tb4-social__item--rounded-square {
        border-radius: 8px;
    }

    .tb4-social__item--square {
        border-radius: 4px;
    }

    /* Hover Effects */
    .tb4-social__item--lift:hover {
        transform: translateY(-3px);
    }

    .tb4-social__item--grow:hover .tb4-social__icon-wrapper {
        transform: scale(1.15);
    }

    .tb4-social__item--glow:hover {
        filter: drop-shadow(0 0 8px currentColor);
    }

    /* Brand Color Variants (for reference in editor) */
    .tb4-social__item--facebook .tb4-social__svg { stroke: #1877F2; }
    .tb4-social__item--twitter .tb4-social__svg { stroke: #000000; }
    .tb4-social__item--instagram .tb4-social__svg { stroke: #E4405F; }
    .tb4-social__item--linkedin .tb4-social__svg { stroke: #0A66C2; }
    .tb4-social__item--youtube .tb4-social__svg { stroke: #FF0000; }
    .tb4-social__item--tiktok .tb4-social__svg { stroke: #000000; }
    .tb4-social__item--pinterest .tb4-social__svg { stroke: #E60023; }
    .tb4-social__item--github .tb4-social__svg { stroke: #181717; }
    .tb4-social__item--dribbble .tb4-social__svg { stroke: #EA4C89; }
    .tb4-social__item--behance .tb4-social__svg { stroke: #1769FF; }
    .tb4-social__item--discord .tb4-social__svg { stroke: #5865F2; }
    .tb4-social__item--telegram .tb4-social__svg { stroke: #26A5E4; }
    .tb4-social__item--whatsapp .tb4-social__svg { stroke: #25D366; }

    /* Social icon hover opacity */
    .tb4-social__item:hover .tb4-social__svg {
        opacity: 0.8;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Number Counter Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-number-counter {
        padding: 20px;
    }

    .tb4-number-preview {
        text-align: center;
        padding: 20px;
    }

    .tb4-number-value {
        font-size: 48px;
        font-weight: 700;
        color: #2563eb;
        line-height: 1.2;
    }

    .tb4-number-prefix,
    .tb4-number-suffix {
        font-size: 36px;
        color: #2563eb;
    }

    .tb4-number-num {
        display: inline-block;
    }

    .tb4-number-title {
        font-size: 16px;
        color: #6b7280;
        margin-top: 8px;
        font-weight: 500;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Circle Counter Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-circle-preview {
        text-align: center;
        padding: 20px;
    }

    .tb4-circle-preview svg {
        display: block;
        margin: 0 auto;
    }

    .tb4-circle-number {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
    }

    .tb4-circle-title {
        font-size: 14px;
        color: #6b7280;
        margin-top: 8px;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Countdown Timer Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-countdown-preview {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        padding: 20px;
        flex-wrap: wrap;
    }

    .tb4-countdown-unit {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: #f3f4f6;
        padding: 16px 20px;
        border-radius: 8px;
        min-width: 80px;
    }

    .tb4-countdown-number {
        font-size: 36px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .tb4-countdown-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tb4-countdown-separator {
        font-size: 36px;
        font-weight: 700;
        color: #d1d5db;
        align-self: flex-start;
        margin-top: 16px;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Progress Bar Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-progress-preview {
        padding: 16px;
    }

    .tb4-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .tb4-progress-label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }

    .tb4-progress-percent {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .tb4-progress-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tb4-progress-track {
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
        flex-grow: 1;
        position: relative;
    }

    .tb4-progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.5s ease;
    }

    .tb4-progress-bar.striped {
        background-image: linear-gradient(
            45deg,
            rgba(255,255,255,0.15) 25%,
            transparent 25%,
            transparent 50%,
            rgba(255,255,255,0.15) 50%,
            rgba(255,255,255,0.15) 75%,
            transparent 75%,
            transparent
        );
        background-size: 1rem 1rem;
    }

    .tb4-progress-percent-inside {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Pricing Table Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-pricing-preview {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        position: relative;
        max-width: 320px;
        margin: 0 auto;
    }

    .tb4-pricing-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: #2563eb;
        color: #fff;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .tb4-pricing-plan {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
    }

    .tb4-pricing-description {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 20px 0;
    }

    .tb4-pricing-price {
        margin-bottom: 24px;
    }

    .tb4-pricing-currency {
        font-size: 24px;
        font-weight: 600;
        color: #111827;
        vertical-align: top;
    }

    .tb4-pricing-amount {
        font-size: 48px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .tb4-pricing-period {
        font-size: 16px;
        color: #6b7280;
    }

    .tb4-pricing-features {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
        text-align: left;
    }

    .tb4-pricing-features li {
        padding: 8px 0;
        color: #374151;
        font-size: 14px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tb4-pricing-features li:last-child {
        border-bottom: none;
    }

    .tb4-feature-icon {
        color: #10b981;
        font-weight: bold;
    }

    .tb4-pricing-button {
        display: block;
        width: 100%;
        padding: 12px 24px;
        background: #2563eb;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        transition: background 0.2s;
    }

    .tb4-pricing-button:hover {
        background: #1d4ed8;
    }

    .tb4-pricing-preview.featured {
        border-color: #2563eb;
        box-shadow: 0 10px 40px rgba(37, 99, 235, 0.15);
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Blog Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-blog-preview {
        width: 100%;
    }

    .tb4-blog-grid {
        display: grid;
        gap: 24px;
    }

    .tb4-blog-grid.cols-1 {
        grid-template-columns: 1fr;
    }

    .tb4-blog-grid.cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .tb4-blog-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .tb4-blog-grid.cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .tb4-blog-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .tb4-blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }

    .tb4-blog-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f3f4f6;
    }

    .tb4-blog-image-placeholder {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .tb4-blog-content {
        padding: 20px;
    }

    .tb4-blog-category {
        display: inline-block;
        padding: 4px 10px;
        background: #2563eb;
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
        margin-bottom: 12px;
    }

    .tb4-blog-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px 0;
        line-height: 1.3;
    }

    .tb4-blog-title:hover {
        color: #2563eb;
    }

    .tb4-blog-excerpt {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.6;
        margin: 0 0 16px 0;
    }

    .tb4-blog-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 12px;
    }

    .tb4-blog-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .tb4-blog-read-more {
        color: #2563eb;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .tb4-blog-read-more:hover {
        text-decoration: underline;
    }

    /* List layout */
    .tb4-blog-grid.layout-list {
        grid-template-columns: 1fr;
    }

    .tb4-blog-grid.layout-list .tb4-blog-card {
        display: flex;
        flex-direction: row;
    }

    .tb4-blog-grid.layout-list .tb4-blog-image,
    .tb4-blog-grid.layout-list .tb4-blog-image-placeholder {
        width: 300px;
        min-width: 300px;
        height: auto;
        min-height: 200px;
    }

    .tb4-blog-grid.layout-list .tb4-blog-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Portfolio Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-portfolio-preview {
        width: 100%;
    }

    .tb4-portfolio-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
        justify-content: center;
    }

    .tb4-portfolio-filter-btn {
        padding: 8px 20px;
        background: #f3f4f6;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tb4-portfolio-filter-btn:hover {
        background: #e5e7eb;
    }

    .tb4-portfolio-filter-btn.active {
        background: #2563eb;
        color: #ffffff;
    }

    .tb4-portfolio-grid {
        display: grid;
        gap: 16px;
    }

    .tb4-portfolio-grid.cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .tb4-portfolio-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .tb4-portfolio-grid.cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .tb4-portfolio-item {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        cursor: pointer;
    }

    .tb4-portfolio-item.ratio-square {
        aspect-ratio: 1/1;
    }

    .tb4-portfolio-item.ratio-landscape {
        aspect-ratio: 4/3;
    }

    .tb4-portfolio-item.ratio-portrait {
        aspect-ratio: 3/4;
    }

    .tb4-portfolio-item.ratio-wide {
        aspect-ratio: 16/9;
    }

    .tb4-portfolio-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s;
    }

    .tb4-portfolio-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.3);
        transition: transform 0.4s;
    }

    .tb4-portfolio-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tb4-portfolio-item:hover .tb4-portfolio-overlay {
        opacity: 1;
    }

    .tb4-portfolio-item:hover .tb4-portfolio-image,
    .tb4-portfolio-item:hover .tb4-portfolio-image-placeholder {
        transform: scale(1.1);
    }

    .tb4-portfolio-icon {
        width: 48px;
        height: 48px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        margin-bottom: 16px;
    }

    .tb4-portfolio-title {
        font-size: 18px;
        font-weight: 600;
        color: #ffffff;
        margin: 0 0 4px 0;
        text-align: center;
        padding: 0 16px;
    }

    .tb4-portfolio-category {
        font-size: 12px;
        color: rgba(255,255,255,0.8);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .tb4-portfolio-description {
        font-size: 13px;
        color: rgba(255,255,255,0.7);
        margin: 8px 0 0 0;
        text-align: center;
        padding: 0 16px;
        line-height: 1.4;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Post Slider Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-post-slider-preview {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .tb4-post-slider-container {
        position: relative;
        overflow: hidden;
    }

    .tb4-post-slider-track {
        display: flex;
        transition: transform 0.5s ease;
    }

    .tb4-post-slide {
        flex-shrink: 0;
        padding: 0 12px;
        box-sizing: border-box;
    }

    .tb4-post-slide-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        height: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .tb4-post-slide-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }

    .tb4-post-slide-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        position: relative;
    }

    .tb4-post-slide-category {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 10px;
        background: #2563eb;
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
    }

    .tb4-post-slide-content {
        padding: 20px;
    }

    .tb4-post-slide-meta {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    .tb4-post-slide-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
        line-height: 1.4;
    }

    .tb4-post-slide-excerpt {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
        margin: 0;
    }

    .tb4-post-slider-arrows {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        display: flex;
        justify-content: space-between;
        padding: 0 8px;
        pointer-events: none;
        z-index: 10;
    }

    .tb4-post-slider-arrow {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        color: #374151;
        border: 1px solid #e5e7eb;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-size: 18px;
    }

    .tb4-post-slider-arrow:hover {
        background: #f9fafb;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .tb4-post-slider-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
    }

    .tb4-post-slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #d1d5db;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tb4-post-slider-dot.active {
        background: #2563eb;
        width: 24px;
        border-radius: 4px;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Post Title Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-post-title-preview {
        width: 100%;
    }

    .tb4-post-title-wrapper {
        display: flex;
        flex-direction: column;
    }

    .tb4-post-title-wrapper.meta-above {
        flex-direction: column-reverse;
    }

    .tb4-post-title-heading {
        font-size: 36px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 16px 0;
        line-height: 1.2;
    }

    .tb4-post-title-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        font-size: 14px;
        color: #6b7280;
    }

    .tb4-post-title-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tb4-post-title-category {
        display: inline-block;
        padding: 4px 10px;
        background: #2563eb;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
    }

    .tb4-post-title-author {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tb4-post-title-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 12px;
    }

    .tb4-post-title-author-name {
        color: #2563eb;
        font-weight: 500;
    }

    .tb4-post-title-separator {
        color: #d1d5db;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Post Content Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-post-content-preview {
        width: 100%;
    }

    .tb4-post-content-wrapper {
        max-width: 100%;
    }

    .tb4-post-content-wrapper.columns-2 {
        column-count: 2;
    }

    .tb4-post-content-wrapper.columns-3 {
        column-count: 3;
    }

    .tb4-post-content-body {
        font-size: 16px;
        line-height: 1.8;
        color: #374151;
    }

    .tb4-post-content-body p {
        margin: 0 0 24px 0;
    }

    .tb4-post-content-body h2 {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin: 32px 0 16px 0;
    }

    .tb4-post-content-body h3 {
        font-size: 22px;
        font-weight: 600;
        color: #111827;
        margin: 28px 0 12px 0;
    }

    .tb4-post-content-body h4 {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 24px 0 12px 0;
    }

    .tb4-post-content-body a {
        color: #2563eb;
        text-decoration: underline;
    }

    .tb4-post-content-body blockquote {
        margin: 24px 0;
        padding: 20px 24px;
        background: #f9fafb;
        border-left: 4px solid #2563eb;
        font-style: italic;
        color: #4b5563;
    }

    .tb4-post-content-body ul,
    .tb4-post-content-body ol {
        margin: 16px 0;
        padding-left: 24px;
    }

    .tb4-post-content-body li {
        margin-bottom: 8px;
    }

    .tb4-post-content-body.drop-cap p:first-of-type::first-letter {
        float: left;
        font-size: 64px;
        line-height: 1;
        font-weight: 700;
        margin-right: 12px;
        color: #2563eb;
    }

    .tb4-post-content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 24px 0;
    }

    .tb4-post-content-body pre {
        background: #1f2937;
        color: #e5e7eb;
        padding: 20px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 24px 0;
    }

    .tb4-post-content-body code {
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 14px;
    }

    .tb4-post-content-body pre code {
        background: transparent;
        padding: 0;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Comments Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-comments-preview {
        width: 100%;
    }

    .tb4-comments-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 24px 0;
    }

    .tb4-comments-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .tb4-comment {
        display: flex;
        gap: 16px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .tb4-comment-avatar {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 600;
        font-size: 16px;
    }

    .tb4-comment-avatar.rounded {
        border-radius: 8px;
    }

    .tb4-comment-avatar.square {
        border-radius: 0;
    }

    .tb4-comment-body {
        flex: 1;
        min-width: 0;
    }

    .tb4-comment-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .tb4-comment-author {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }

    .tb4-comment-date {
        font-size: 13px;
        color: #9ca3af;
    }

    .tb4-comment-content {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .tb4-comment-reply {
        font-size: 13px;
        color: #2563eb;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .tb4-comment-reply:hover {
        text-decoration: underline;
    }

    .tb4-comment-nested {
        margin-left: 48px;
        margin-top: 20px;
    }

    .tb4-comments-form {
        margin-top: 40px;
        padding: 24px;
        background: #f9fafb;
        border-radius: 12px;
    }

    .tb4-comments-form-title {
        font-size: 20px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 8px 0;
    }

    .tb4-comments-form-desc {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 20px 0;
    }

    .tb4-comments-form-fields {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .tb4-comments-form-row {
        display: flex;
        gap: 16px;
    }

    .tb4-comments-form-field {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tb4-comments-form-label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }

    .tb4-comments-form-input,
    .tb4-comments-form-textarea {
        padding: 12px 16px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .tb4-comments-form-input:focus,
    .tb4-comments-form-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .tb4-comments-form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .tb4-comments-form-submit {
        padding: 12px 24px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        align-self: flex-start;
        transition: background-color 0.2s;
    }

    .tb4-comments-form-submit:hover {
        background: #1d4ed8;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Post Navigation Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-post-nav-preview {
        width: 100%;
    }

    .tb4-post-nav-wrapper {
        display: flex;
        gap: 24px;
    }

    .tb4-post-nav-wrapper.layout-stacked {
        flex-direction: column;
    }

    .tb4-post-nav-wrapper.layout-minimal {
        justify-content: space-between;
        align-items: center;
    }

    .tb4-post-nav-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .tb4-post-nav-item:hover {
        background: #f9fafb;
    }

    .tb4-post-nav-item.nav-prev {
        flex-direction: row;
    }

    .tb4-post-nav-item.nav-next {
        flex-direction: row-reverse;
        text-align: right;
    }

    .tb4-post-nav-arrow {
        flex-shrink: 0;
        color: #9ca3af;
    }

    .tb4-post-nav-thumb {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .tb4-post-nav-content {
        flex: 1;
        min-width: 0;
    }

    .tb4-post-nav-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .tb4-post-nav-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tb4-post-nav-item:hover .tb4-post-nav-title {
        color: #2563eb;
    }

    .tb4-post-nav-divider {
        width: 1px;
        background: #e5e7eb;
        align-self: stretch;
    }

    .tb4-post-nav-wrapper.layout-stacked .tb4-post-nav-divider {
        width: 100%;
        height: 1px;
    }

    /* Minimal layout */
    .tb4-post-nav-wrapper.layout-minimal .tb4-post-nav-item {
        flex: 0 0 auto;
        padding: 12px 20px;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Search Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-search {
        width: 100%;
    }

    .tb4-search__form {
        display: flex;
        gap: 8px;
        align-items: stretch;
    }

    .tb4-search__form.layout-stacked {
        flex-direction: column;
    }

    .tb4-search__form.layout-fullwidth {
        width: 100%;
    }

    .tb4-search__form.layout-fullwidth .tb4-search__input {
        flex: 1;
    }

    .tb4-search__input {
        flex: 1;
        min-width: 0;
        border: 1px solid #ccc;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .tb4-search__input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .tb4-search__input::placeholder {
        color: #9ca3af;
    }

    .tb4-search__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s, border-color 0.2s;
    }

    .tb4-search__button:hover {
        filter: brightness(1.1);
    }

    .tb4-search__button:active {
        transform: scale(0.98);
    }

    .tb4-search__button:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .tb4-search__icon {
        flex-shrink: 0;
    }

    .tb4-search__button svg {
        width: 18px;
        height: 18px;
    }

    /* Search Alignment */
    .tb4-search.align-center {
        display: flex;
        justify-content: center;
    }

    .tb4-search.align-right {
        display: flex;
        justify-content: flex-end;
    }

    /* Search Empty State (for builder preview) */
    .tb4-search--empty {
        padding: 20px;
        text-align: center;
        background: #f3f4f6;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        color: #6b7280;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Login Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-login-preview {
        background: #fff;
        padding: 24px;
        border-radius: 8px;
        max-width: 300px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .tb4-login-preview h3 {
        margin: 0 0 16px 0;
        color: #111827;
        font-size: 18px;
        font-weight: 600;
        text-align: center;
    }
    .tb4-login-preview .tb4-login-field {
        margin-bottom: 12px;
    }
    .tb4-login-preview label {
        display: block;
        margin-bottom: 4px;
        font-size: 13px;
        color: #374151;
        font-weight: 500;
    }
    .tb4-login-preview input[type="text"],
    .tb4-login-preview input[type="password"] {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: #f9fafb;
        box-sizing: border-box;
        font-size: 14px;
    }
    .tb4-login-preview .tb4-login-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 12px 0;
        font-size: 12px;
    }
    .tb4-login-preview .tb4-login-remember {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
    }
    .tb4-login-preview .tb4-login-forgot {
        color: #2563eb;
        text-decoration: none;
    }
    .tb4-login-preview .tb4-login-button {
        width: 100%;
        padding: 10px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Email Signup Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-signup-preview {
        width: 100%;
    }
    .tb4-signup-wrapper {
        width: 100%;
    }
    .tb4-signup-container {
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
    }
    .tb4-signup-title {
        font-size: 32px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 16px 0;
    }
    .tb4-signup-description {
        font-size: 16px;
        color: #6b7280;
        margin: 0 0 32px 0;
        line-height: 1.6;
    }
    .tb4-signup-subscriber-count {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 24px;
    }
    .tb4-signup-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .tb4-signup-form.layout-inline {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .tb4-signup-form.layout-inline input {
        flex: 1;
        min-width: 200px;
    }
    .tb4-signup-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .tb4-signup-input-icon {
        position: absolute;
        left: 14px;
        color: #9ca3af;
        pointer-events: none;
    }
    .tb4-signup-input {
        width: 100%;
        padding: 14px 18px;
        padding-left: 44px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        color: #111827;
        transition: all 0.2s;
    }
    .tb4-signup-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    .tb4-signup-input::placeholder {
        color: #9ca3af;
    }
    .tb4-signup-btn {
        padding: 14px 28px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tb4-signup-btn:hover {
        background: #1d4ed8;
    }
    .tb4-signup-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        text-align: left;
        font-size: 14px;
        color: #6b7280;
        margin-top: 8px;
    }
    .tb4-signup-checkbox input {
        margin-top: 3px;
    }
    .tb4-signup-privacy {
        font-size: 13px;
        color: #9ca3af;
        margin: 20px 0 0 0;
    }
    .tb4-signup-privacy svg {
        display: inline;
        vertical-align: middle;
        margin-right: 4px;
    }
    .tb4-signup-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        align-items: stretch;
        overflow: hidden;
    }
    .tb4-signup-split-image {
        background-size: cover;
        background-position: center;
        min-height: 400px;
    }
    .tb4-signup-split-content {
        padding: 60px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .tb4-signup-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 48px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Toggle Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-toggle-preview {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .tb4-toggle-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 18px;
        background: #f3f4f6;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tb4-toggle-header:hover {
        background: #e5e7eb;
    }
    .tb4-toggle-title {
        font-weight: 600;
        color: #111827;
        font-size: 15px;
        margin: 0;
    }
    .tb4-toggle-icon {
        color: #6b7280;
        font-size: 18px;
        transition: transform 0.3s;
    }
    .tb4-toggle-icon.open {
        transform: rotate(180deg);
    }
    .tb4-toggle-content {
        padding: 16px 18px;
        background: #fff;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.6;
        border-top: 1px solid #e5e7eb;
    }
    .tb4-toggle-content.collapsed {
        display: none;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Accordion Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-accordion-preview {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .tb4-accordion-item {
        border-bottom: 1px solid #e5e7eb;
    }
    .tb4-accordion-item:last-child {
        border-bottom: none;
    }
    .tb4-accordion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: #f9fafb;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tb4-accordion-header:hover {
        background: #f3f4f6;
    }
    .tb4-accordion-item.active .tb4-accordion-header {
        background: #e5e7eb;
    }
    .tb4-accordion-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
    }
    .tb4-accordion-icon {
        color: #6b7280;
        font-size: 14px;
        transition: transform 0.3s;
    }
    .tb4-accordion-item.active .tb4-accordion-icon {
        transform: rotate(180deg);
    }
    .tb4-accordion-item.active .tb4-accordion-icon.plus {
        transform: rotate(0deg);
    }
    .tb4-accordion-content {
        padding: 16px;
        background: #ffffff;
    }
    .tb4-accordion-body {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.6;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Accordion Item Module Styles (Child Module)
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-accordion-item-preview {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }
    .tb4-accordion-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f9fafb;
        cursor: pointer;
    }
    .tb4-accordion-item-title {
        font-weight: 600;
        color: #1f2937;
    }
    .tb4-accordion-item-content {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        color: #4b5563;
        font-size: 14px;
    }
    .tb4-accordion-item-preview:not(.open) .tb4-accordion-item-content {
        display: none;
    }
    .tb4-accordion-item-preview.open .tb4-accordion-item-header {
        background: #eff6ff;
    }
    .tb4-accordion-item-preview.open .tb4-accordion-item-title {
        color: #2563eb;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Tabs Item Module Styles (Child Module)
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-tabs-item-preview {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }
    .tb4-tabs-item-tab {
        display: inline-flex;
        align-items: center;
        padding: 10px 16px;
        background: #f3f4f6;
        font-weight: 500;
        font-size: 14px;
        color: #374151;
        border-bottom: 2px solid #2563eb;
    }
    .tb4-tabs-item-content {
        padding: 16px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.6;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Slider Item Module Styles (Child Module)
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-slider-item-preview {
        min-height: 200px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .tb4-slider-item-content {
        padding: 24px;
        z-index: 1;
    }
    .tb4-slider-item-heading {
        margin: 0 0 8px 0;
        font-size: 24px;
        font-weight: 700;
        color: #ffffff;
    }
    .tb4-slider-item-sub {
        margin: 0 0 16px 0;
        font-size: 14px;
        color: #e5e7eb;
    }
    .tb4-slider-item-btn {
        display: inline-block;
        padding: 10px 24px;
        background: #2563eb;
        color: #ffffff;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Tabs Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-tabs-preview {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .tb4-tabs-nav {
        display: flex;
        background: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
    }
    .tb4-tabs-nav.stretch .tb4-tab-btn {
        flex: 1;
        text-align: center;
    }
    .tb4-tabs-nav.center {
        justify-content: center;
    }
    .tb4-tabs-nav.right {
        justify-content: flex-end;
    }
    .tb4-tab-btn {
        padding: 12px 24px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        position: relative;
        transition: all 0.2s;
    }
    .tb4-tab-btn:hover {
        color: #374151;
        background: rgba(0,0,0,0.03);
    }
    .tb4-tab-btn.active {
        color: #111827;
        background: #ffffff;
    }
    .tb4-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #2563eb;
    }
    .tb4-tabs-content {
        padding: 24px;
        background: #ffffff;
    }
    .tb4-tab-panel {
        display: none;
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
    }
    .tb4-tab-panel.active {
        display: block;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Slider Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-slider-preview {
        position: relative;
        overflow: hidden;
        border-radius: 0;
    }
    .tb4-slider-track {
        display: flex;
        transition: transform 0.5s ease;
    }
    .tb4-slide {
        min-width: 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
    }
    .tb4-slide-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
    }
    .tb4-slide-content {
        position: relative;
        z-index: 2;
        padding: 40px;
        max-width: 800px;
    }
    .tb4-slide-title {
        font-size: 36px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 16px 0;
    }
    .tb4-slide-text {
        font-size: 16px;
        color: #e5e7eb;
        margin: 0 0 24px 0;
        line-height: 1.6;
    }
    .tb4-slide-button {
        display: inline-block;
        padding: 12px 28px;
        background: #2563eb;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
    }
    .tb4-slider-arrows {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        display: flex;
        justify-content: space-between;
        padding: 0 16px;
        pointer-events: none;
    }
    .tb4-slider-arrow {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(0,0,0,0.3);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
        transition: background 0.2s;
        font-size: 20px;
    }
    .tb4-slider-arrow:hover {
        background: rgba(0,0,0,0.5);
    }
    .tb4-slider-dots {
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    .tb4-slider-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tb4-slider-dot.active {
        background: #ffffff;
    }

    /* ═══════════════════════════════════════════════════════════════════
       TB4 Contact Form Module Styles
       ═══════════════════════════════════════════════════════════════════ */
    .tb4-contact-preview {
        background: #ffffff;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .tb4-contact-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
    }
    .tb4-contact-description {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 24px 0;
    }
    .tb4-contact-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .tb4-contact-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .tb4-contact-label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }
    .tb4-contact-label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .tb4-contact-input,
    .tb4-contact-textarea {
        padding: 12px 16px;
        background: #f9fafb;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        color: #111827;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .tb4-contact-input:focus,
    .tb4-contact-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .tb4-contact-textarea {
        resize: vertical;
        min-height: 120px;
    }
    .tb4-contact-button {
        padding: 14px 32px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .tb4-contact-button:hover {
        background: #1d4ed8;
    }
    .tb4-contact-button.full-width {
        width: 100%;
    }

    /* Fullwidth Header Module */
    .tb4-fw-header-preview {
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .tb4-fw-header-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    .tb4-fw-header-overlay {
        position: absolute;
        inset: 0;
    }
    .tb4-fw-header-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        padding: 60px 40px;
    }
    .tb4-fw-header-logo {
        max-width: 120px;
        height: auto;
    }
    .tb4-fw-header-title {
        font-size: 56px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 24px 0;
        line-height: 1.2;
    }
    .tb4-fw-header-subtitle {
        font-size: 20px;
        color: rgba(255,255,255,0.9);
        margin: 0 0 40px 0;
        line-height: 1.6;
    }
    .tb4-fw-header-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .tb4-fw-header-btn {
        display: inline-block;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tb4-fw-header-btn-primary {
        background: #2563eb;
        color: #ffffff;
    }
    .tb4-fw-header-btn-secondary {
        background: transparent;
        color: #ffffff;
        border: 2px solid #ffffff;
    }
    .tb4-fw-header-scroll {
        position: absolute;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%);
        color: rgba(255,255,255,0.7);
        animation: tb4FwHeaderBounce 2s infinite;
    }
    @keyframes tb4FwHeaderBounce {
        0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
        40% { transform: translateX(-50%) translateY(-10px); }
        60% { transform: translateX(-50%) translateY(-5px); }
    }

    /* Fullwidth Image Module */
    .tb4-fw-image-preview {
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .tb4-fw-image-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    .tb4-fw-image-img {
        width: 100%;
        display: block;
        object-fit: cover;
        object-position: center;
    }
    .tb4-fw-image-placeholder {
        width: 100%;
        background: linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }
    .tb4-fw-image-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        padding: 40px;
    }
    .tb4-fw-image-overlay.pos-center {
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .tb4-fw-image-overlay.pos-bottom-left {
        align-items: flex-start;
        justify-content: flex-end;
    }
    .tb4-fw-image-overlay.pos-bottom-center {
        align-items: center;
        justify-content: flex-end;
        text-align: center;
    }
    .tb4-fw-image-overlay.pos-bottom-right {
        align-items: flex-end;
        justify-content: flex-end;
        text-align: right;
    }
    .tb4-fw-image-overlay.pos-top-left {
        align-items: flex-start;
        justify-content: flex-start;
    }
    .tb4-fw-image-overlay.pos-top-center {
        align-items: center;
        justify-content: flex-start;
        text-align: center;
    }
    .tb4-fw-image-overlay.pos-top-right {
        align-items: flex-end;
        justify-content: flex-start;
        text-align: right;
    }
    .tb4-fw-image-overlay-title {
        font-size: 32px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 12px 0;
    }
    .tb4-fw-image-overlay-text {
        font-size: 16px;
        color: rgba(255,255,255,0.9);
        margin: 0;
        line-height: 1.6;
    }
    .tb4-fw-image-caption {
        padding: 16px;
        background: #f9fafb;
        font-size: 14px;
        color: #6b7280;
    }
    .tb4-fw-image-wrapper.hover-zoom:hover .tb4-fw-image-img,
    .tb4-fw-image-wrapper.hover-zoom:hover .tb4-fw-image-placeholder {
        transform: scale(1.05);
    }
    .tb4-fw-image-wrapper .tb4-fw-image-img,
    .tb4-fw-image-wrapper .tb4-fw-image-placeholder {
        transition: all 0.4s ease;
    }
    .tb4-fw-image-wrapper.hover-brighten:hover .tb4-fw-image-img {
        filter: brightness(1.1);
    }
    .tb4-fw-image-wrapper.hover-darken:hover .tb4-fw-image-img {
        filter: brightness(0.8);
    }

    /* Fullwidth Slider Module */
    .tb4-fw-slider-preview {
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .tb4-fw-slider-container {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    .tb4-fw-slider-track {
        display: flex;
        transition: transform 0.5s ease;
    }
    .tb4-fw-slider-slide {
        flex: 0 0 100%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb4-fw-slider-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    .tb4-fw-slider-overlay {
        position: absolute;
        inset: 0;
    }
    .tb4-fw-slider-content {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 60px;
    }
    .tb4-fw-slider-title {
        font-size: 56px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 20px 0;
        line-height: 1.2;
    }
    .tb4-fw-slider-subtitle {
        font-size: 20px;
        color: rgba(255,255,255,0.9);
        margin: 0 0 32px 0;
        line-height: 1.6;
    }
    .tb4-fw-slider-btn {
        display: inline-block;
        padding: 16px 32px;
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .tb4-fw-slider-btn:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }
    .tb4-fw-slider-arrows {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        display: flex;
        justify-content: space-between;
        padding: 0 20px;
        pointer-events: none;
        z-index: 10;
    }
    .tb4-fw-slider-arrow {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(0,0,0,0.3);
        border: none;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
        transition: all 0.2s;
    }
    .tb4-fw-slider-arrow:hover {
        background: rgba(0,0,0,0.5);
    }
    .tb4-fw-slider-dots {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }
    .tb4-fw-slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tb4-fw-slider-dot:hover {
        background: rgba(255,255,255,0.8);
    }
    .tb4-fw-slider-dot.active {
        background: #ffffff;
    }

    /* Fullwidth Map Module */
    .tb4-fw-map-preview {
        width: 100%;
        position: relative;
    }
    .tb4-fw-map-container {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    .tb4-fw-map-placeholder {
        width: 100%;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #4f46e5;
        gap: 16px;
    }
    .tb4-fw-map-placeholder-icon {
        width: 80px;
        height: 80px;
        background: rgba(79, 70, 229, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb4-fw-map-placeholder-text {
        font-size: 18px;
        font-weight: 600;
    }
    .tb4-fw-map-placeholder-address {
        font-size: 14px;
        opacity: 0.8;
    }
    .tb4-fw-map-info {
        position: absolute;
        background: #ffffff;
        padding: 32px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        z-index: 10;
    }
    .tb4-fw-map-info.pos-left {
        top: 50%;
        left: 32px;
        transform: translateY(-50%);
    }
    .tb4-fw-map-info.pos-right {
        top: 50%;
        right: 32px;
        transform: translateY(-50%);
    }
    .tb4-fw-map-info.pos-bottom-left {
        bottom: 32px;
        left: 32px;
    }
    .tb4-fw-map-info.pos-bottom-right {
        bottom: 32px;
        right: 32px;
    }
    .tb4-fw-map-title {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 20px 0;
    }
    .tb4-fw-map-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }
    .tb4-fw-map-item:last-child {
        margin-bottom: 0;
    }
    .tb4-fw-map-item-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        color: #2563eb;
        margin-top: 2px;
    }
    .tb4-fw-map-item-content {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.5;
    }
    .tb4-fw-map-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 20px 0;
    }
    .tb4-fw-map-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        margin-top: 20px;
    }
    .tb4-fw-map-btn:hover {
        background: #1d4ed8;
    }

    /* Fullwidth Menu Module */
    .tb4-fw-menu-preview {
        width: 100%;
    }
    .tb4-fw-menu-wrapper {
        width: 100%;
    }
    .tb4-fw-menu-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 16px 24px;
        box-sizing: border-box;
        position: relative;
    }
    .tb4-fw-menu-container.layout-logo-center {
        flex-wrap: wrap;
        justify-content: center;
    }
    .tb4-fw-menu-container.layout-menu-center .tb4-fw-menu-nav {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
    .tb4-fw-menu-logo a {
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    .tb4-fw-menu-logo img {
        max-height: 48px;
        width: auto;
    }
    .tb4-fw-menu-logo-text {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }
    .tb4-fw-menu-nav {
        display: flex;
        align-items: center;
        gap: 32px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .tb4-fw-menu-nav li a {
        font-size: 15px;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        transition: color 0.2s;
        padding: 8px 0;
        position: relative;
    }
    .tb4-fw-menu-nav li a:hover {
        color: #2563eb;
    }
    .tb4-fw-menu-nav li a.active {
        color: #2563eb;
    }
    .tb4-fw-menu-nav li a.active.style-underline::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: currentColor;
    }
    .tb4-fw-menu-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .tb4-fw-menu-search {
        width: 40px;
        height: 40px;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .tb4-fw-menu-search:hover {
        background: #f3f4f6;
    }
    .tb4-fw-menu-cta {
        display: inline-block;
        padding: 10px 20px;
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .tb4-fw-menu-cta:hover {
        background: #1d4ed8;
    }
    .tb4-fw-menu-hamburger {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        width: 32px;
        height: 32px;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
    }
    .tb4-fw-menu-hamburger span {
        display: block;
        width: 100%;
        height: 2px;
        background: #374151;
        border-radius: 1px;
    }

    /* Fullwidth Portfolio Module */
    .tb4-fw-portfolio-preview {
        width: 100%;
    }
    .tb4-fw-portfolio-container {
        width: 100%;
    }
    .tb4-fw-portfolio-header {
        text-align: center;
        margin-bottom: 32px;
    }
    .tb4-fw-portfolio-title {
        font-size: 36px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px 0;
    }
    .tb4-fw-portfolio-subtitle {
        font-size: 18px;
        color: #6b7280;
        margin: 0;
    }
    .tb4-fw-portfolio-filter {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 48px;
    }
    .tb4-fw-portfolio-filter-btn {
        padding: 10px 24px;
        background: transparent;
        border: none;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        border-radius: 24px;
        transition: all 0.2s;
    }
    .tb4-fw-portfolio-filter-btn:hover,
    .tb4-fw-portfolio-filter-btn.active {
        background: #2563eb;
        color: #ffffff;
    }
    .tb4-fw-portfolio-grid {
        display: grid;
        width: 100%;
    }
    .tb4-fw-portfolio-item {
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .tb4-fw-portfolio-item-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .tb4-fw-portfolio-item-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        opacity: 0;
        transition: all 0.3s ease;
        padding: 20px;
    }
    .tb4-fw-portfolio-item:hover .tb4-fw-portfolio-item-overlay {
        opacity: 1;
    }
    .tb4-fw-portfolio-item:hover .tb4-fw-portfolio-item-image {
        transform: scale(1.05);
    }
    .tb4-fw-portfolio-item-icon {
        margin-bottom: 16px;
    }
    .tb4-fw-portfolio-item-title {
        font-size: 18px;
        font-weight: 600;
        color: #ffffff;
        margin: 0 0 8px 0;
    }
    .tb4-fw-portfolio-item-category {
        font-size: 14px;
        color: rgba(255,255,255,0.8);
        margin: 0;
    }
    .tb4-fw-portfolio-load-more {
        text-align: center;
        margin-top: 48px;
    }
    .tb4-fw-portfolio-load-more button {
        padding: 14px 32px;
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tb4-fw-portfolio-load-more button:hover {
        background: #1d4ed8;
    }

    /* Fullwidth Post Slider Module */
    .tb4-fw-post-slider-preview {
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .tb4-fw-post-slider-container {
        position: relative;
        width: 100%;
    }
    .tb4-fw-post-slider-track {
        display: flex;
        transition: transform 0.5s ease;
    }
    .tb4-fw-post-slider-slide {
        flex: 0 0 100%;
        position: relative;
    }
    .tb4-fw-post-slider-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }
    .tb4-fw-post-slider-overlay {
        position: absolute;
        inset: 0;
    }
    .tb4-fw-post-slider-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 60px;
        box-sizing: border-box;
    }
    .tb4-fw-post-slider-category {
        display: inline-block;
        padding: 6px 14px;
        background: #2563eb;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 4px;
        margin-bottom: 20px;
        align-self: flex-start;
    }
    .tb4-fw-post-slider-title {
        font-size: 42px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
        margin: 0 0 20px 0;
    }
    .tb4-fw-post-slider-excerpt {
        font-size: 18px;
        color: rgba(255,255,255,0.9);
        line-height: 1.6;
        margin: 0 0 24px 0;
    }
    .tb4-fw-post-slider-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 14px;
        color: rgba(255,255,255,0.8);
        margin-bottom: 32px;
    }
    .tb4-fw-post-slider-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb4-fw-post-slider-btn {
        display: inline-block;
        padding: 14px 28px;
        background: #ffffff;
        color: #111827;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        align-self: flex-start;
        transition: all 0.2s;
    }
    .tb4-fw-post-slider-arrows {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        display: flex;
        justify-content: space-between;
        padding: 0 24px;
        pointer-events: none;
        z-index: 10;
    }
    .tb4-fw-post-slider-arrow {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: none;
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: auto;
        transition: all 0.2s;
        backdrop-filter: blur(4px);
    }
    .tb4-fw-post-slider-arrow:hover {
        background: rgba(255,255,255,0.3);
    }
    .tb4-fw-post-slider-dots {
        position: absolute;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }
    .tb4-fw-post-slider-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tb4-fw-post-slider-dot.active {
        background: #ffffff;
        transform: scale(1.2);
    }

    /* Fullwidth Code Module */
    .tb4-fw-code-preview {
        width: 100%;
    }
    .tb4-fw-code-wrapper {
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }
    .tb4-fw-code-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .tb4-fw-code-title {
        font-size: 13px;
        font-weight: 500;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb4-fw-code-dots {
        display: flex;
        gap: 6px;
    }
    .tb4-fw-code-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .tb4-fw-code-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tb4-fw-code-badge {
        padding: 4px 10px;
        background: rgba(255,255,255,0.1);
        color: #9ca3af;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 4px;
    }
    .tb4-fw-code-copy {
        padding: 6px 12px;
        background: rgba(255,255,255,0.1);
        color: #9ca3af;
        border: none;
        font-size: 12px;
        font-weight: 500;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .tb4-fw-code-copy:hover {
        background: rgba(255,255,255,0.15);
        color: #ffffff;
    }
    .tb4-fw-code-body {
        display: flex;
        overflow: auto;
    }
    .tb4-fw-code-lines {
        padding: 24px 0;
        text-align: right;
        user-select: none;
        border-right: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    .tb4-fw-code-line-num {
        padding: 0 16px;
        font-size: 14px;
        line-height: 1.6;
        color: #6b7280;
    }
    .tb4-fw-code-content {
        flex: 1;
        padding: 24px;
        overflow-x: auto;
    }
    .tb4-fw-code-pre {
        margin: 0;
        font-family: 'Fira Code', 'JetBrains Mono', monospace;
        font-size: 14px;
        line-height: 1.6;
    }
    .tb4-fw-code-line {
        display: block;
        padding: 0 4px;
        margin: 0 -4px;
    }
    .tb4-fw-code-line.highlighted {
        background: rgba(255,255,0,0.1);
    }
    </style>

    <!-- Gallery Lightbox & Carousel JavaScript -->
    <script>
    (function() {
        'use strict';

        // Lightbox singleton
        const TB4Lightbox = {
            element: null,
            images: [],
            currentIndex: 0,

            init() {
                if (this.element) return;
                this.createLightbox();
                this.bindEvents();
            },

            createLightbox() {
                this.element = document.createElement('div');
                this.element.className = 'tb4-lightbox';
                this.element.innerHTML = `
                    <button type="button" class="tb4-lightbox__nav tb4-lightbox__nav--prev" aria-label="Previous">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <div class="tb4-lightbox__content">
                        <button type="button" class="tb4-lightbox__close" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                        <img class="tb4-lightbox__image" src="" alt="">
                        <div class="tb4-lightbox__caption"></div>
                        <div class="tb4-lightbox__counter"></div>
                    </div>
                    <button type="button" class="tb4-lightbox__nav tb4-lightbox__nav--next" aria-label="Next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                `;
                document.body.appendChild(this.element);
            },

            bindEvents() {
                // Close button
                this.element.querySelector('.tb4-lightbox__close').addEventListener('click', () => this.close());

                // Navigation
                this.element.querySelector('.tb4-lightbox__nav--prev').addEventListener('click', () => this.prev());
                this.element.querySelector('.tb4-lightbox__nav--next').addEventListener('click', () => this.next());

                // Close on backdrop click
                this.element.addEventListener('click', (e) => {
                    if (e.target === this.element) this.close();
                });

                // Keyboard navigation
                document.addEventListener('keydown', (e) => {
                    if (!this.element.classList.contains('active')) return;
                    if (e.key === 'Escape') this.close();
                    if (e.key === 'ArrowLeft') this.prev();
                    if (e.key === 'ArrowRight') this.next();
                });

                // Listen for lightbox triggers
                document.addEventListener('click', (e) => {
                    const trigger = e.target.closest('.tb4-gallery__lightbox-trigger');
                    if (trigger) {
                        e.preventDefault();
                        const gallery = trigger.closest('.tb4-gallery');
                        const triggers = gallery.querySelectorAll('.tb4-gallery__lightbox-trigger');
                        this.images = Array.from(triggers).map(t => ({
                            src: t.dataset.src,
                            alt: t.dataset.alt || '',
                            caption: t.dataset.caption || ''
                        }));
                        const index = Array.from(triggers).indexOf(trigger);
                        this.open(index);
                    }
                });
            },

            open(index) {
                this.currentIndex = index;
                this.updateContent();
                this.element.classList.add('active');
                document.body.style.overflow = 'hidden';
            },

            close() {
                this.element.classList.remove('active');
                document.body.style.overflow = '';
            },

            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                this.updateContent();
            },

            next() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
                this.updateContent();
            },

            updateContent() {
                const img = this.images[this.currentIndex];
                const imgEl = this.element.querySelector('.tb4-lightbox__image');
                const captionEl = this.element.querySelector('.tb4-lightbox__caption');
                const counterEl = this.element.querySelector('.tb4-lightbox__counter');

                imgEl.src = img.src;
                imgEl.alt = img.alt;
                captionEl.textContent = img.caption;
                captionEl.style.display = img.caption ? 'block' : 'none';
                counterEl.textContent = `${this.currentIndex + 1} / ${this.images.length}`;

                // Hide navigation if only one image
                const navPrev = this.element.querySelector('.tb4-lightbox__nav--prev');
                const navNext = this.element.querySelector('.tb4-lightbox__nav--next');
                const showNav = this.images.length > 1;
                navPrev.style.display = showNav ? 'flex' : 'none';
                navNext.style.display = showNav ? 'flex' : 'none';
                counterEl.style.display = showNav ? 'block' : 'none';
            }
        };

        // Gallery Carousel Controller
        const TB4GalleryCarousel = {
            init(gallery) {
                const grid = gallery.querySelector('.tb4-gallery__grid');
                const prevBtn = gallery.querySelector('.tb4-gallery__nav-prev');
                const nextBtn = gallery.querySelector('.tb4-gallery__nav-next');
                const dots = gallery.querySelectorAll('.tb4-gallery__dot, .tb4-gallery__page-num');
                const items = gallery.querySelectorAll('.tb4-gallery__item');
                const autoplay = gallery.dataset.autoplay === 'true';
                const autoplaySpeed = parseInt(gallery.dataset.autoplaySpeed) || 5000;

                let currentSlide = 0;
                let autoplayInterval = null;

                const updateSlide = (index) => {
                    currentSlide = index;
                    const item = items[index];
                    if (item) {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
                    }
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('active', i === index);
                        if (dot.classList.contains('tb4-gallery__dot')) {
                            dot.style.background = i === index ? '#3b82f6' : '#475569';
                        } else {
                            dot.style.background = i === index ? '#3b82f6' : '#475569';
                        }
                    });
                };

                if (prevBtn) {
                    prevBtn.addEventListener('click', () => {
                        currentSlide = (currentSlide - 1 + items.length) % items.length;
                        updateSlide(currentSlide);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', () => {
                        currentSlide = (currentSlide + 1) % items.length;
                        updateSlide(currentSlide);
                    });
                }

                dots.forEach((dot, i) => {
                    dot.addEventListener('click', () => updateSlide(i));
                });

                if (autoplay && items.length > 1) {
                    autoplayInterval = setInterval(() => {
                        currentSlide = (currentSlide + 1) % items.length;
                        updateSlide(currentSlide);
                    }, autoplaySpeed);

                    gallery.addEventListener('mouseenter', () => clearInterval(autoplayInterval));
                    gallery.addEventListener('mouseleave', () => {
                        autoplayInterval = setInterval(() => {
                            currentSlide = (currentSlide + 1) % items.length;
                            updateSlide(currentSlide);
                        }, autoplaySpeed);
                    });
                }
            }
        };

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            TB4Lightbox.init();

            // Initialize carousels
            document.querySelectorAll('.tb4-gallery--carousel').forEach(gallery => {
                TB4GalleryCarousel.init(gallery);
            });
        });

        // Re-initialize when new galleries are added to the DOM (for builder)
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('tb4-gallery--carousel')) {
                                TB4GalleryCarousel.init(node);
                            }
                            node.querySelectorAll && node.querySelectorAll('.tb4-gallery--carousel').forEach(gallery => {
                                TB4GalleryCarousel.init(gallery);
                            });
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    })();

    // ═══════════════════════════════════════════════════════════════════
    // TB4 Video Lazy Loading & Play Button Controller
    // ═══════════════════════════════════════════════════════════════════
    (function() {
        'use strict';

        const TB4Video = {
            /**
             * Initialize video module
             */
            init(videoEl) {
                if (!videoEl || videoEl.dataset.tb4VideoInit) return;
                videoEl.dataset.tb4VideoInit = 'true';

                const isLazy = videoEl.dataset.lazy === 'true';
                if (!isLazy) return; // Non-lazy videos are already rendered

                const playBtn = videoEl.querySelector('.tb4-video__play-btn');
                const overlay = videoEl.querySelector('.tb4-video__overlay');

                if (playBtn) {
                    playBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.loadVideo(videoEl);
                    });
                }

                if (overlay) {
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            this.loadVideo(videoEl);
                        }
                    });
                }

                // Keyboard accessibility
                if (playBtn) {
                    playBtn.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.loadVideo(videoEl);
                        }
                    });
                }
            },

            /**
             * Load and display the actual video
             */
            loadVideo(videoEl) {
                const wrapper = videoEl.querySelector('.tb4-video__wrapper');
                const posterContainer = videoEl.querySelector('.tb4-video__poster-container');
                if (!wrapper) return;

                const source = videoEl.dataset.videoSource;
                const embedUrl = videoEl.dataset.embedUrl;
                const videoUrl = videoEl.dataset.videoUrl;
                const autoplay = videoEl.dataset.autoplay === 'true';
                const loop = videoEl.dataset.loop === 'true';
                const controls = videoEl.dataset.controls === 'true';
                const muted = videoEl.dataset.muted === 'true';
                const title = videoEl.querySelector('.tb4-video__play-btn')?.getAttribute('aria-label')?.replace('Play video: ', '') || 'Video';

                // Add loading state
                videoEl.classList.add('tb4-video--loading');

                let mediaElement;

                if (source === 'self_hosted') {
                    // Create HTML5 video element
                    mediaElement = document.createElement('video');
                    mediaElement.className = 'tb4-video__player';
                    mediaElement.title = title;
                    mediaElement.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;';

                    if (controls) mediaElement.controls = true;
                    if (loop) mediaElement.loop = true;
                    if (muted) mediaElement.muted = true;
                    mediaElement.playsInline = true;
                    mediaElement.autoplay = true; // Always autoplay on click

                    const sourceEl = document.createElement('source');
                    sourceEl.src = videoUrl;
                    sourceEl.type = 'video/mp4';
                    mediaElement.appendChild(sourceEl);

                    mediaElement.addEventListener('loadeddata', () => {
                        videoEl.classList.remove('tb4-video--loading');
                    });

                    mediaElement.addEventListener('error', () => {
                        videoEl.classList.remove('tb4-video--loading');
                        console.error('TB4 Video: Failed to load video', videoUrl);
                    });
                } else {
                    // Create iframe for YouTube/Vimeo
                    mediaElement = document.createElement('iframe');
                    mediaElement.className = 'tb4-video__iframe';
                    mediaElement.title = title;
                    mediaElement.frameBorder = '0';
                    mediaElement.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
                    mediaElement.allowFullscreen = true;
                    mediaElement.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;';

                    // Add autoplay to embed URL since user clicked
                    let finalUrl = embedUrl;
                    if (!finalUrl.includes('autoplay=1')) {
                        finalUrl += (finalUrl.includes('?') ? '&' : '?') + 'autoplay=1';
                    }
                    // For YouTube, also add mute for autoplay if not already there
                    if (source === 'youtube' && !finalUrl.includes('mute=1')) {
                        finalUrl += '&mute=1';
                    }

                    mediaElement.src = finalUrl;

                    mediaElement.addEventListener('load', () => {
                        videoEl.classList.remove('tb4-video--loading');
                    });
                }

                // Remove poster and add video
                if (posterContainer) {
                    posterContainer.remove();
                }
                wrapper.appendChild(mediaElement);

                // Mark as loaded
                videoEl.classList.remove('tb4-video--lazy');
                videoEl.classList.add('tb4-video--loaded');
            },

            /**
             * Initialize all videos on the page
             */
            initAll() {
                document.querySelectorAll('.tb4-video--lazy').forEach(video => {
                    this.init(video);
                });
            }
        };

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            TB4Video.initAll();
        });

        // Re-initialize when new videos are added to the DOM (for builder)
        if (typeof MutationObserver !== 'undefined') {
            const videoObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('tb4-video--lazy')) {
                                TB4Video.init(node);
                            }
                            node.querySelectorAll && node.querySelectorAll('.tb4-video--lazy').forEach(video => {
                                TB4Video.init(video);
                            });
                        }
                    });
                });
            });
            videoObserver.observe(document.body, { childList: true, subtree: true });
        }

        // Expose globally for manual initialization
        window.TB4Video = TB4Video;

        /**
         * TB4 Audio Player Controller
         * Handles custom audio player functionality for self-hosted audio
         */
        const TB4Audio = {
            /**
             * Format time in mm:ss
             */
            formatTime(seconds) {
                if (isNaN(seconds) || seconds === Infinity) return '0:00';
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            },

            /**
             * Initialize a single audio player
             */
            init(audioEl) {
                if (!audioEl || audioEl.dataset.tb4AudioInit === 'true') return;

                const audio = audioEl.querySelector('.tb4-audio__element');
                if (!audio) return; // No audio element (probably embed)

                const playBtn = audioEl.querySelector('.tb4-audio__play-btn');
                const playIcon = audioEl.querySelector('.tb4-audio__play-icon');
                const pauseIcon = audioEl.querySelector('.tb4-audio__pause-icon');
                const progress = audioEl.querySelector('.tb4-audio__progress');
                const progressBar = audioEl.querySelector('.tb4-audio__progress-bar');
                const progressHandle = audioEl.querySelector('.tb4-audio__progress-handle');
                const timeCurrent = audioEl.querySelector('.tb4-audio__time-current');
                const timeDuration = audioEl.querySelector('.tb4-audio__time-duration');
                const volumeBtn = audioEl.querySelector('.tb4-audio__volume-btn');
                const volumeSlider = audioEl.querySelector('.tb4-audio__volume-slider');

                // Play/Pause toggle
                if (playBtn) {
                    playBtn.addEventListener('click', () => {
                        if (audio.paused) {
                            // Pause all other audio players first
                            document.querySelectorAll('.tb4-audio__element').forEach(other => {
                                if (other !== audio && !other.paused) {
                                    other.pause();
                                    other.closest('.tb4-audio')?.classList.remove('tb4-audio--playing');
                                }
                            });
                            audio.play();
                            audioEl.classList.add('tb4-audio--playing');
                            playBtn.setAttribute('aria-label', 'Pause');
                        } else {
                            audio.pause();
                            audioEl.classList.remove('tb4-audio--playing');
                            playBtn.setAttribute('aria-label', 'Play');
                        }
                    });
                }

                // Update progress bar on time update
                audio.addEventListener('timeupdate', () => {
                    if (audio.duration) {
                        const percent = (audio.currentTime / audio.duration) * 100;
                        if (progressBar) progressBar.style.width = percent + '%';
                        if (progressHandle) progressHandle.style.left = percent + '%';
                        if (timeCurrent) timeCurrent.textContent = this.formatTime(audio.currentTime);
                    }
                });

                // Update duration when loaded
                audio.addEventListener('loadedmetadata', () => {
                    if (timeDuration) timeDuration.textContent = this.formatTime(audio.duration);
                });

                // Also try to get duration from durationchange event
                audio.addEventListener('durationchange', () => {
                    if (timeDuration && audio.duration && audio.duration !== Infinity) {
                        timeDuration.textContent = this.formatTime(audio.duration);
                    }
                });

                // Click on progress bar to seek
                if (progress) {
                    progress.addEventListener('click', (e) => {
                        const rect = progress.getBoundingClientRect();
                        const percent = (e.clientX - rect.left) / rect.width;
                        audio.currentTime = percent * audio.duration;
                    });

                    // Drag to seek
                    let isDragging = false;
                    progress.addEventListener('mousedown', (e) => {
                        isDragging = true;
                        const rect = progress.getBoundingClientRect();
                        const percent = (e.clientX - rect.left) / rect.width;
                        audio.currentTime = Math.max(0, Math.min(percent, 1)) * audio.duration;
                    });

                    document.addEventListener('mousemove', (e) => {
                        if (isDragging && progress) {
                            const rect = progress.getBoundingClientRect();
                            const percent = (e.clientX - rect.left) / rect.width;
                            audio.currentTime = Math.max(0, Math.min(percent, 1)) * audio.duration;
                        }
                    });

                    document.addEventListener('mouseup', () => {
                        isDragging = false;
                    });
                }

                // Volume control
                if (volumeSlider) {
                    volumeSlider.addEventListener('input', (e) => {
                        audio.volume = parseFloat(e.target.value);
                        audio.muted = audio.volume === 0;
                    });
                }

                // Mute toggle
                if (volumeBtn) {
                    volumeBtn.addEventListener('click', () => {
                        audio.muted = !audio.muted;
                        if (volumeSlider) {
                            volumeSlider.value = audio.muted ? 0 : audio.volume || 1;
                        }
                    });
                }

                // Reset on ended
                audio.addEventListener('ended', () => {
                    audioEl.classList.remove('tb4-audio--playing');
                    if (playBtn) playBtn.setAttribute('aria-label', 'Play');
                    if (progressBar) progressBar.style.width = '0%';
                    if (progressHandle) progressHandle.style.left = '0%';
                    if (timeCurrent) timeCurrent.textContent = '0:00';
                });

                // Mark as initialized
                audioEl.dataset.tb4AudioInit = 'true';
            },

            /**
             * Initialize all audio players on the page
             */
            initAll() {
                document.querySelectorAll('.tb4-audio--self-hosted').forEach(audio => {
                    this.init(audio);
                });
            }
        };

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            TB4Audio.initAll();
        });

        // Re-initialize when new audio players are added to the DOM (for builder)
        if (typeof MutationObserver !== 'undefined') {
            const audioObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('tb4-audio--self-hosted')) {
                                TB4Audio.init(node);
                            }
                            node.querySelectorAll && node.querySelectorAll('.tb4-audio--self-hosted').forEach(audio => {
                                TB4Audio.init(audio);
                            });
                        }
                    });
                });
            });
            audioObserver.observe(document.body, { childList: true, subtree: true });
        }

        // Expose globally for manual initialization
        window.TB4Audio = TB4Audio;
    })();

    // Toggle Module Click Handler
    (function() {
        'use strict';

        document.addEventListener('click', function(e) {
            var header = e.target.closest('.tb4-toggle-header');
            if (header) {
                var toggle = header.closest('.tb4-toggle-preview');
                if (!toggle) return;

                var content = toggle.querySelector('.tb4-toggle-content');
                var icon = header.querySelector('.tb4-toggle-icon');

                if (content) {
                    content.classList.toggle('collapsed');
                }
                if (icon) {
                    icon.classList.toggle('open');
                    // Update icon text for plus/minus
                    if (icon.textContent === '+') {
                        icon.textContent = '−';
                    } else if (icon.textContent === '−') {
                        icon.textContent = '+';
                    }
                }
            }
        });
    })();

    // Accordion Module Click Handler
    (function() {
        'use strict';

        document.addEventListener('click', function(e) {
            var header = e.target.closest('.tb4-accordion-header');
            if (header) {
                var item = header.closest('.tb4-accordion-item');
                var accordion = header.closest('.tb4-accordion-preview');
                if (!item || !accordion) return;

                var isActive = item.classList.contains('active');
                var content = item.querySelector('.tb4-accordion-content');
                var icon = header.querySelector('span:last-child');

                // Check if behavior is single (close others first)
                var behavior = accordion.getAttribute('data-behavior') || 'single';
                if (behavior === 'single' && !isActive) {
                    // Close all other items
                    accordion.querySelectorAll('.tb4-accordion-item.active').forEach(function(otherItem) {
                        otherItem.classList.remove('active');
                        var otherContent = otherItem.querySelector('.tb4-accordion-content');
                        var otherIcon = otherItem.querySelector('.tb4-accordion-header span:last-child');
                        if (otherContent) otherContent.style.display = 'none';
                        if (otherIcon) {
                            otherIcon.style.transform = '';
                            if (otherIcon.textContent === '−') otherIcon.textContent = '+';
                        }
                    });
                }

                // Toggle current item
                item.classList.toggle('active');
                if (content) {
                    content.style.display = item.classList.contains('active') ? '' : 'none';
                }
                if (icon) {
                    if (item.classList.contains('active')) {
                        icon.style.transform = 'rotate(180deg)';
                        if (icon.textContent === '+') icon.textContent = '−';
                    } else {
                        icon.style.transform = '';
                        if (icon.textContent === '−') icon.textContent = '+';
                    }
                }
            }
        });
    })();
    </script>
    
    <!-- Initialize Lucide Icons -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
            console.log('[TB4] Lucide icons initialized on DOMContentLoaded');
        } else {
            console.error('[TB4] Lucide library not loaded! Check CSP and network.');
        }
    });
    // Fallback - try again after window load
    window.addEventListener('load', function() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
            console.log('[TB4] Lucide icons re-initialized on window.load');
        }
    });
    </script>
</body>
</html>
