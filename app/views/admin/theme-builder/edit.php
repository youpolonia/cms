<?php
/**
 * Theme Builder 3.0 - Visual Builder (Full Screen)
 * Three-panel layout: Modules | Canvas | Settings
 */
// DEBUG - check what variables are available
file_put_contents('/tmp/edit_debug.log', date('Y-m-d H:i:s')." EDIT.PHP loaded\n", FILE_APPEND);
file_put_contents('/tmp/edit_debug.log', "modulesJson isset: ".(isset($modulesJson)?'YES':'NO')."\n", FILE_APPEND);
file_put_contents('/tmp/edit_debug.log', "modulesJson length: ".strlen($modulesJson ?? '')."\n", FILE_APPEND);
file_put_contents('/tmp/edit_debug.log', "modules isset: ".(isset($modules)?'YES':'NO')."\n", FILE_APPEND);

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 5)); }

$pageTitle = esc($page['title'] ?? 'New Page');
$pageId = (int)($page['id'] ?? $pageId ?? 0);
$pageSlug = esc($page['slug'] ?? '');
$pageStatus = $page['status'] ?? 'draft';
// Use pre-encoded JSON from controller if available, otherwise encode from arrays
if (!isset($contentJson) || empty($contentJson)) {
    $contentJson = json_encode($content ?? ['sections' => []], JSON_UNESCAPED_UNICODE);
}
if (!isset($modulesJson) || empty($modulesJson)) {
    $modulesJson = json_encode($modules ?? [], JSON_UNESCAPED_UNICODE);
}
if (!isset($categoriesJson) || empty($categoriesJson)) {
    $categoriesJson = json_encode($categories ?? [], JSON_UNESCAPED_UNICODE);
}
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Builder - <?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icon Libraries for Icon Picker -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/core/theme-builder/css/components.css?v=20260104g">
    <style>
    :root {
        --tb-bg: #1e1e2e;
        --tb-bg-secondary: #181825;
        --tb-surface: #181825;
        --tb-surface-2: #313244;
        --tb-border: #45475a;
        --tb-text: #cdd6f4;
        --tb-text-muted: #6c7086;
        --tb-accent: #89b4fa;
        --tb-accent-hover: #b4befe;
        --tb-success: #a6e3a1;
        --tb-warning: #f9e2af;
        --tb-danger: #f38ba8;
        --tb-panel-width: 280px;
        --tb-toolbar-height: 56px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { 
        height: 100%; 
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: var(--tb-text);
        background: var(--tb-surface-2);
    }
    
    /* ═══════════════════════════════════════════════════════════
       TOOLBAR
       ═══════════════════════════════════════════════════════════ */
    .tb-toolbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--tb-toolbar-height);
        background: var(--tb-surface);
        border-bottom: 1px solid var(--tb-border);
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 16px;
        z-index: 1000;
    }
    .tb-toolbar-left, .tb-toolbar-center, .tb-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-toolbar-left { flex: 0 0 auto; }
    .tb-toolbar-center { flex: 1; justify-content: center; }
    .tb-toolbar-right { flex: 0 0 auto; }
    
    .tb-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 15px;
    }
    .tb-logo svg { opacity: 0.8; }
    
    .tb-page-title {
        background: transparent;
        border: 1px solid transparent;
        color: var(--tb-text);
        font-size: 15px;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        text-align: center;
        min-width: 200px;
    }
    .tb-page-title:hover { border-color: var(--tb-border); }
    .tb-page-title:focus {
        outline: none;
        border-color: var(--tb-accent);
        background: var(--tb-surface-2);
    }
    
    .tb-btn {
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
        background: var(--tb-surface-2);
        color: var(--tb-text);
    }
    .tb-btn:hover { background: var(--tb-border); }
    .tb-btn-primary { background: var(--tb-accent); color: #1e1e2e; }
    .tb-btn-primary:hover { background: var(--tb-accent-hover); }
    .tb-btn-icon {
        padding: 8px;
        background: transparent;
    }
    .tb-btn-icon:hover { background: var(--tb-surface-2); }
    .tb-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .tb-btn-ai {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        margin-top: 8px;
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .tb-btn-ai:hover { opacity: 0.9; transform: translateY(-1px); }
    .tb-btn-ai:disabled { opacity: 0.6; cursor: wait; transform: none; }
    .tb-btn-media { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; transition: all 0.2s; flex: 0 0 auto; }
    .tb-btn-media:hover { opacity: 0.9; transform: translateY(-1px); }

    /* Media Gallery Modal */
    .tb-media-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 110000; background: rgba(0,0,0,0.7); align-items: center; justify-content: center; }
    .tb-media-modal.active { display: flex; }
    .tb-media-dialog { background: var(--tb-surface); border-radius: 12px; width: 90%; max-width: 900px; max-height: 80vh; display: flex; flex-direction: column; border: 1px solid var(--tb-border); }
    .tb-media-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--tb-border); display: flex; justify-content: space-between; align-items: center; }
    .tb-media-header h3 { font-size: 1rem; font-weight: 600; color: var(--tb-text); margin: 0; }
    .tb-media-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--tb-text-muted); }
    .tb-media-close:hover { color: var(--tb-text); }
    .tb-media-body { flex: 1; overflow-y: auto; padding: 1.5rem; }
    .tb-media-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--tb-border); display: flex; justify-content: flex-end; gap: 0.75rem; }

    .tb-media-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--tb-border); padding-bottom: 0.75rem; }
    .tb-media-tab { padding: 0.625rem 1rem; background: transparent; color: var(--tb-text-muted); border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; }
    .tb-media-tab:hover { color: var(--tb-text); background: rgba(255,255,255,0.05); }
    .tb-media-tab.active { color: var(--tb-accent); background: rgba(137, 180, 250, 0.15); }
    .tb-media-tab-content { display: none; }
    .tb-media-tab-content.active { display: block; }

    .tb-upload-area { border: 2px dashed var(--tb-border); border-radius: 8px; padding: 2rem; text-align: center; transition: all 0.2s; cursor: pointer; }
    .tb-upload-area:hover, .tb-upload-area.dragover { border-color: var(--tb-accent); background: rgba(137, 180, 250, 0.05); }
    .tb-upload-area input { display: none; }

    .tb-media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.75rem; }
    .tb-media-item { aspect-ratio: 1; border: 2px solid var(--tb-border); border-radius: 6px; overflow: hidden; cursor: pointer; transition: all 0.2s; position: relative; }
    .tb-media-item:hover { border-color: var(--tb-accent); }
    .tb-media-item.selected { border-color: var(--tb-accent); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.3); }
    .tb-media-item img { width: 100%; height: 100%; object-fit: cover; }
    .tb-media-filename { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.25rem; background: rgba(0,0,0,0.7); color: white; font-size: 0.6rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .tb-stock-search { display: flex; gap: 0.75rem; margin-bottom: 1rem; }
    .tb-stock-search input { flex: 1; padding: 0.625rem 1rem; background: var(--tb-surface-2); border: 1px solid var(--tb-border); border-radius: 6px; color: var(--tb-text); }
    .tb-stock-search input:focus { outline: none; border-color: var(--tb-accent); }
    .tb-stock-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; }
    .tb-stock-item { aspect-ratio: 4/3; border-radius: 6px; overflow: hidden; cursor: pointer; position: relative; border: 1px solid var(--tb-border); transition: all 0.2s; }
    .tb-stock-item:hover { border-color: var(--tb-accent); }
    .tb-stock-item.selected { border-color: var(--tb-accent); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.3); }
    .tb-stock-item img { width: 100%; height: 100%; object-fit: cover; }
    .tb-stock-credit { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.25rem 0.5rem; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; font-size: 0.6rem; }
    .tb-stock-loading { text-align: center; padding: 2rem; color: var(--tb-text-muted); }

    /* ═══════════════════════════════════════════════════════════
       LAYOUT LIBRARY MODAL (Divi-style integration)
       ═══════════════════════════════════════════════════════════ */
    .tb-library-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        background: rgba(0,0,0,0.75);
        align-items: center;
        justify-content: center;
    }
    .tb-library-modal.active { display: flex; }
    .tb-library-dialog {
        background: var(--tb-surface);
        border-radius: 12px;
        width: 90%;
        max-width: 1100px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--tb-border);
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    .tb-library-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--tb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, rgba(137, 180, 250, 0.1), rgba(137, 180, 250, 0.05));
    }
    .tb-library-header h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--tb-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .tb-library-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--tb-text-muted);
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        transition: all 0.15s;
    }
    .tb-library-close:hover { color: var(--tb-danger); background: rgba(243, 139, 168, 0.1); }
    .tb-library-toolbar {
        padding: 0.75rem 1.5rem;
        border-bottom: 1px solid var(--tb-border);
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .tb-library-search {
        flex: 1;
        min-width: 200px;
        padding: 0.5rem 1rem;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 0.875rem;
    }
    .tb-library-search:focus { outline: none; border-color: var(--tb-accent); }
    .tb-library-filter {
        padding: 0.5rem 0.75rem;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 0.875rem;
        cursor: pointer;
    }
    .tb-library-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }
    .tb-library-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    .tb-library-card {
        background: var(--tb-surface-2);
        border: 2px solid var(--tb-border);
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tb-library-card:hover { border-color: var(--tb-accent); transform: translateY(-2px); }
    .tb-library-card.selected { border-color: var(--tb-accent); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.3); }
    .tb-library-thumbnail {
        height: 140px;
        background: linear-gradient(135deg, var(--tb-bg) 0%, var(--tb-surface-2) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--tb-text-muted);
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-library-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
    .tb-library-info { padding: 0.875rem; }
    .tb-library-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--tb-text);
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tb-library-meta {
        display: flex;
        gap: 0.75rem;
        font-size: 0.75rem;
        color: var(--tb-text-muted);
    }
    .tb-library-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.5rem;
        background: rgba(137, 180, 250, 0.15);
        border-radius: 4px;
        font-size: 0.7rem;
        color: var(--tb-accent);
    }
    .tb-library-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--tb-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--tb-surface);
    }
    .tb-library-actions { display: flex; gap: 0.75rem; }
    .tb-library-loading {
        text-align: center;
        padding: 3rem;
        color: var(--tb-text-muted);
    }
    .tb-library-empty {
        text-align: center;
        padding: 3rem;
        color: var(--tb-text-muted);
    }
    /* Page selector within layout */
    .tb-library-pages {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--tb-border);
        background: rgba(137, 180, 250, 0.05);
    }
    .tb-library-pages-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tb-text-muted);
        margin-bottom: 0.5rem;
    }
    .tb-library-pages-list {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .tb-library-page-btn {
        padding: 0.375rem 0.75rem;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-library-page-btn:hover { border-color: var(--tb-accent); }
    .tb-library-page-btn.active {
        background: var(--tb-accent);
        color: var(--tb-bg);
        border-color: var(--tb-accent);
    }
    /* Insert mode selector */
    .tb-insert-mode {
        display: flex;
        gap: 0.5rem;
        margin-right: auto;
    }
    .tb-insert-mode label {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.75rem;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-insert-mode label:hover { border-color: var(--tb-accent); }
    .tb-insert-mode input[type="radio"] { accent-color: var(--tb-accent); }
    .tb-insert-mode input[type="radio"]:checked + span { color: var(--tb-accent); }

    .tb-ai-gen-form { display: flex; flex-direction: column; gap: 1rem; }
    .tb-ai-gen-prompt { padding: 0.75rem; background: var(--tb-surface-2); border: 1px solid var(--tb-border); border-radius: 6px; color: var(--tb-text); min-height: 80px; resize: vertical; font-family: inherit; }
    .tb-ai-gen-prompt:focus { outline: none; border-color: var(--tb-accent); }
    .tb-ai-gen-options { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    .tb-ai-gen-options select { padding: 0.5rem 0.75rem; background: var(--tb-surface-2); border: 1px solid var(--tb-border); border-radius: 6px; color: var(--tb-text); }
    .tb-ai-gen-preview { margin-top: 1rem; }
    .tb-ai-gen-status { text-align: center; padding: 2rem; color: var(--tb-text-muted); }
    .tb-spinner { width: 30px; height: 30px; border: 3px solid var(--tb-border); border-top-color: var(--tb-accent); border-radius: 50%; animation: tb-spin 1s linear infinite; margin: 0 auto 1rem; }
    @keyframes tb-spin { to { transform: rotate(360deg); } }
    .tb-btn-ai.loading::after {
        content: '';
        width: 12px;
        height: 12px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: tb-spin 0.8s linear infinite;
    }
    @keyframes tb-spin { to { transform: rotate(360deg); } }

    .tb-divider {
        width: 1px;
        height: 24px;
        background: var(--tb-border);
        margin: 0 4px;
    }
    
    /* ═══════════════════════════════════════════════════════════
       MAIN LAYOUT
       ═══════════════════════════════════════════════════════════ */
    .tb-main {
        position: fixed;
        top: var(--tb-toolbar-height);
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
    }
    
    /* ═══════════════════════════════════════════════════════════
       LEFT PANEL - MODULES
       ═══════════════════════════════════════════════════════════ */
    .tb-panel-left {
        width: var(--tb-panel-width);
        background: var(--tb-surface);
        border-right: 1px solid var(--tb-border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .tb-panel-header {
        padding: 16px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-panel-title {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tb-text-muted);
    }
    .tb-search {
        width: 100%;
        padding: 8px 12px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 13px;
        margin-top: 12px;
    }
    .tb-search:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    .tb-panel-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px;
    }
    
    .tb-module-category {
        margin-bottom: 16px;
    }
    .tb-category-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tb-text-muted);
        margin-bottom: 8px;
        padding: 0 4px;
    }
    .tb-modules-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .tb-module-item {
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        padding: 12px 8px;
        text-align: center;
        cursor: grab;
        transition: all 0.15s;
        overflow: hidden;
    }
    .tb-module-item:hover {
        border-color: var(--tb-accent);
        background: var(--tb-surface-2);
    }
    .tb-module-item.dragging {
        opacity: 0.5;
    }
    .tb-module-icon {
        font-size: 20px;
        margin-bottom: 6px;
        pointer-events: none;
    }
    .tb-module-name {
        font-size: 10px;
        font-weight: 500;
        color: var(--tb-text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        pointer-events: none;
    }
    
    /* ═══════════════════════════════════════════════════════════
       CENTER - CANVAS
       ═══════════════════════════════════════════════════════════ */
    .tb-canvas-wrapper {
        flex: 1;
        background: var(--tb-surface-2);
        overflow: auto;
        display: flex;
        flex-direction: column;
    }
    .tb-canvas-toolbar {
        padding: 12px 16px;
        background: var(--tb-surface);
        border-bottom: 1px solid var(--tb-border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-viewport-btns {
        display: flex;
        gap: 4px;
    }
    .tb-viewport-btn {
        padding: 6px 10px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 4px;
        color: var(--tb-text-muted);
        cursor: pointer;
    }
    .tb-viewport-btn:hover { background: var(--tb-surface-2); }
    .tb-viewport-btn.active {
        background: var(--tb-surface-2);
        border-color: var(--tb-border);
        color: var(--tb-text);
    }
    
    .tb-canvas {
        flex: 1;
        padding: 24px;
        overflow: auto;
    }
    .tb-canvas-inner {
        background: var(--tb-surface-2);
        min-height: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        margin: 0 auto;
        transition: width 0.3s;
        overflow: hidden;
    }
    .tb-canvas-inner.desktop { width: 100%; max-width: 1200px; }
    .tb-canvas-inner.tablet { width: 768px; }
    .tb-canvas-inner.mobile { width: 375px; }
    
    /* Canvas Drop Zones */
    .tb-drop-zone {
        min-height: 120px;
        border: 2px dashed var(--tb-border);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--tb-text-muted);
        margin: 16px;
        transition: all 0.15s;
    }
    .tb-drop-zone:hover, .tb-drop-zone.drag-over {
        border-color: var(--tb-accent);
        background: rgba(137, 180, 250, 0.1);
    }
    .tb-drop-zone-text {
        text-align: center;
    }
    .tb-drop-zone-icon {
        font-size: 32px;
        margin-bottom: 8px;
        opacity: 0.5;
    }
    
    /* ═══════════════════════════════════════════════════════════
       RIGHT PANEL - SETTINGS
       ═══════════════════════════════════════════════════════════ */
    }
    .tb-setting-group {
        margin-bottom: 20px;
    }
    .tb-setting-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--tb-text-muted);
        margin-bottom: 6px;
    }
    .tb-setting-input {
        width: 100%;
        padding: 8px 10px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 13px;
    }
    .tb-setting-input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }

    /* ═══════════════════════════════════════════════════════════
       SPACING BOX UI (Divi-style)
       ═══════════════════════════════════════════════════════════ */
    .tb-spacing-box {
        position: relative;
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
    }
    .tb-spacing-box-outer {
        position: relative;
        background: rgba(249, 226, 175, 0.15);
        border: 2px dashed rgba(249, 226, 175, 0.5);
        border-radius: 8px;
        padding: 24px;
    }
    .tb-spacing-box-inner {
        position: relative;
        background: rgba(166, 227, 161, 0.15);
        border: 2px dashed rgba(166, 227, 161, 0.5);
        border-radius: 6px;
        padding: 20px;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-spacing-box-content {
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 11px;
        color: var(--tb-text-muted);
    }
    .tb-spacing-label {
        position: absolute;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 6px;
        border-radius: 3px;
    }
    .tb-spacing-label-margin {
        top: 4px;
        left: 8px;
        background: rgba(249, 226, 175, 0.3);
        color: #c9a227;
    }
    .tb-spacing-label-padding {
        top: 4px;
        left: 8px;
        background: rgba(166, 227, 161, 0.3);
        color: #40a02b;
    }
    .tb-spacing-input-wrap {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .tb-spacing-input-wrap input {
        width: 44px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-spacing-input-wrap input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    .tb-spacing-input-wrap .tb-spacing-unit {
        font-size: 10px;
        color: var(--tb-text-muted);
    }
    /* Margin inputs positioning */
    .tb-spacing-margin-top { top: -12px; left: 50%; transform: translateX(-50%); }
    .tb-spacing-margin-right { right: -12px; top: 50%; transform: translateY(-50%); }
    .tb-spacing-margin-bottom { bottom: -12px; left: 50%; transform: translateX(-50%); }
    .tb-spacing-margin-left { left: -12px; top: 50%; transform: translateY(-50%); }
    /* Padding inputs positioning */
    .tb-spacing-padding-top { top: -12px; left: 50%; transform: translateX(-50%); }
    .tb-spacing-padding-right { right: -12px; top: 50%; transform: translateY(-50%); }
    .tb-spacing-padding-bottom { bottom: -12px; left: 50%; transform: translateX(-50%); }
    .tb-spacing-padding-left { left: -12px; top: 50%; transform: translateY(-50%); }
    /* Link button */
    .tb-spacing-link-btn {
        position: absolute;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        color: var(--tb-text-muted);
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .tb-spacing-link-btn:hover {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-spacing-link-btn.linked {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-spacing-link-margin { top: 50%; right: 50%; transform: translate(50%, -50%); }
    .tb-spacing-link-padding { top: 50%; left: 50%; transform: translate(-50%, -50%); }
    /* Collapsible section */
    .tb-spacing-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-spacing-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: var(--tb-surface-2);
        cursor: pointer;
        user-select: none;
    }
    .tb-spacing-section-header:hover {
        background: var(--tb-border);
    }
    .tb-spacing-section-title {
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-spacing-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-spacing-section.collapsed .tb-spacing-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-spacing-section-body {
        padding: 12px;
        display: block;
    }
    .tb-spacing-section.collapsed .tb-spacing-section-body {
        display: none;
    }

    /* ═══════════════════════════════════════════════════════════
       BORDER BOX UI (Divi-style per-side controls)
       ═══════════════════════════════════════════════════════════ */
    .tb-border-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-border-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: var(--tb-surface-2);
        cursor: pointer;
        user-select: none;
    }
    .tb-border-section-header:hover {
        background: var(--tb-border);
    }
    .tb-border-section-title {
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-border-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-border-section.collapsed .tb-border-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-border-section-body {
        padding: 12px;
        display: block;
    }
    .tb-border-section.collapsed .tb-border-section-body {
        display: none;
    }
    .tb-border-box {
        position: relative;
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
    }
    .tb-border-box-outer {
        position: relative;
        background: rgba(180, 190, 254, 0.1);
        border: 2px dashed rgba(180, 190, 254, 0.4);
        border-radius: 8px;
        padding: 28px;
    }
    .tb-border-box-inner {
        position: relative;
        background: var(--tb-surface);
        border: 2px solid rgba(180, 190, 254, 0.6);
        border-radius: 6px;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-border-box-content {
        font-size: 11px;
        color: var(--tb-text-muted);
        text-align: center;
        padding: 8px;
    }
    .tb-border-label {
        position: absolute;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 6px;
        border-radius: 3px;
        top: 4px;
        left: 8px;
        background: rgba(180, 190, 254, 0.3);
        color: #7c7fea;
    }
    .tb-border-input-wrap {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .tb-border-input-wrap input {
        width: 44px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-border-input-wrap input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    /* Border width inputs positioning */
    .tb-border-width-top { top: -12px; left: 50%; transform: translateX(-50%); }
    .tb-border-width-right { right: -12px; top: 50%; transform: translateY(-50%); }
    .tb-border-width-bottom { bottom: -12px; left: 50%; transform: translateX(-50%); }
    .tb-border-width-left { left: -12px; top: 50%; transform: translateY(-50%); }
    /* Border link button */
    .tb-border-link-btn {
        position: absolute;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        color: var(--tb-text-muted);
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .tb-border-link-btn:hover {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-border-link-btn.linked {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    /* Border radius box */
    .tb-radius-box {
        position: relative;
        width: 100%;
        padding: 8px;
        margin-bottom: 12px;
    }
    .tb-radius-box-visual {
        position: relative;
        width: 100%;
        height: 100px;
        background: var(--tb-surface);
        border: 2px solid rgba(245, 194, 231, 0.6);
        border-radius: 8px;
        transition: border-radius 0.2s;
    }
    .tb-radius-corner {
        position: absolute;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-radius-corner input {
        width: 36px;
        padding: 4px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 10px;
        text-align: center;
    }
    .tb-radius-corner input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    .tb-radius-tl { top: -18px; left: -18px; }
    .tb-radius-tr { top: -18px; right: -18px; }
    .tb-radius-br { bottom: -18px; right: -18px; }
    .tb-radius-bl { bottom: -18px; left: -18px; }
    .tb-radius-link-btn {
        position: absolute;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        color: var(--tb-text-muted);
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .tb-radius-link-btn:hover {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-radius-link-btn.linked {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-radius-label {
        position: absolute;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 6px;
        border-radius: 3px;
        top: 4px;
        left: 8px;
        background: rgba(245, 194, 231, 0.3);
        color: #d5679d;
    }
    /* Border style dropdown row */
    .tb-border-style-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
    }
    .tb-border-style-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 50px;
    }
    .tb-border-style-row select {
        flex: 1;
        padding: 6px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    /* Border color row */
    .tb-border-color-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
    }
    .tb-border-color-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 50px;
    }
    .tb-border-color-row input[type="color"] {
        width: 36px;
        height: 28px;
        padding: 2px;
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
    }
    .tb-border-color-row input[type="text"] {
        flex: 1;
        padding: 6px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }

    /* ═══════════════════════════════════════════════════════════
       BOX SHADOW UI (Divi-style controls)
       ═══════════════════════════════════════════════════════════ */
    .tb-shadow-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-shadow-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: var(--tb-surface-2);
        cursor: pointer;
        user-select: none;
    }
    .tb-shadow-section-header:hover {
        background: var(--tb-border);
    }
    .tb-shadow-section-title {
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-shadow-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-shadow-section.collapsed .tb-shadow-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-shadow-section-body {
        padding: 12px;
        display: block;
    }
    .tb-shadow-section.collapsed .tb-shadow-section-body {
        display: none;
    }
    /* Shadow Row - used by renderBoxShadowSettings */
    .tb-shadow-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-shadow-row:last-child {
        border-bottom: none;
    }
    .tb-shadow-row label {
        font-size: 12px;
        color: var(--tb-text);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-shadow-row label span {
        color: var(--tb-accent);
        font-weight: 600;
        min-width: 50px;
    }
    .tb-shadow-row input[type="range"] {
        flex: 1;
        max-width: 120px;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: var(--tb-border);
        border-radius: 2px;
    }
    .tb-shadow-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: var(--tb-accent);
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-shadow-row input[type="color"] {
        width: 36px;
        height: 28px;
        padding: 2px;
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
    }
    .tb-shadow-preview-box {
        width: 60px;
        height: 60px;
        background: #ffffff;
        border-radius: 8px;
        margin: 0 auto 16px;
        transition: box-shadow 0.2s ease;
    }
    .tb-shadow-controls {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .tb-shadow-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-shadow-control-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 80px;
        flex-shrink: 0;
    }
    .tb-shadow-control-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: var(--tb-border);
        border-radius: 2px;
        outline: none;
    }
    .tb-shadow-control-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: var(--tb-accent);
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-shadow-control-row input[type="range"]::-moz-range-thumb {
        width: 14px;
        height: 14px;
        background: var(--tb-accent);
        border-radius: 50%;
        cursor: pointer;
        border: none;
    }
    .tb-shadow-control-row input[type="number"] {
        width: 54px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-shadow-control-row input[type="color"] {
        width: 36px;
        height: 28px;
        padding: 2px;
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
    }
    .tb-shadow-control-row input[type="text"] {
        flex: 1;
        padding: 6px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    .tb-shadow-control-row select {
        flex: 1;
        padding: 6px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    .tb-shadow-enable-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid var(--tb-border);
        margin-bottom: 12px;
    }
    .tb-shadow-enable-row label {
        font-size: 12px;
        color: var(--tb-text);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-shadow-enable-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .tb-shadow-inset-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-top: 8px;
        border-top: 1px solid var(--tb-border);
        margin-top: 12px;
    }
    .tb-shadow-inset-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-shadow-inset-row input[type="checkbox"] {
        width: 14px;
        height: 14px;
        cursor: pointer;
    }
    .tb-shadow-preset-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--tb-border);
        margin-bottom: 12px;
    }
    .tb-shadow-preset-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 50px;
    }
    .tb-shadow-disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    /* ═══════════════════════════════════════════════════════════
       HOVER STATE SYSTEM (Divi-style hover editing)
       ═══════════════════════════════════════════════════════════ */
    .tb-state-toggle {
        display: flex;
        gap: 0;
        margin-bottom: 16px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .tb-state-btn {
        flex: 1;
        padding: 10px 16px;
        background: transparent;
        border: none;
        color: var(--tb-text-muted);
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .tb-state-btn:first-child {
        border-right: 1px solid var(--tb-border);
    }
    .tb-state-btn:hover {
        background: var(--tb-surface-2);
        color: var(--tb-text);
    }
    .tb-state-btn.active {
        background: var(--tb-accent);
        color: #1e1e2e;
    }
    .tb-state-btn.active.hover-state {
        background: #f9e2af;
        color: #1e1e2e;
    }
    .tb-state-indicator {
        font-size: 14px;
    }
    .tb-hover-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        background: #f9e2af;
        color: #1e1e2e;
        border-radius: 4px;
        font-size: 10px;
        margin-left: 4px;
        vertical-align: middle;
    }
    .tb-hover-section {
        border: 1px solid rgba(249, 226, 175, 0.3);
        border-radius: 10px;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(249, 226, 175, 0.1);
        background: rgba(30, 30, 46, 0.6);
    }
    .tb-hover-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: linear-gradient(135deg, rgba(249, 226, 175, 0.2), rgba(249, 226, 175, 0.08));
        cursor: pointer;
        user-select: none;
        border-bottom: 1px solid rgba(249, 226, 175, 0.2);
    }
    .tb-hover-section-header:hover {
        background: linear-gradient(135deg, rgba(249, 226, 175, 0.3), rgba(249, 226, 175, 0.15));
    }
    .tb-hover-section-title {
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #f9e2af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .tb-hover-section-toggle {
        font-size: 12px;
        color: #f9e2af;
        transition: transform 0.2s;
        opacity: 0.7;
    }
    .tb-hover-section.collapsed .tb-hover-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-hover-section-body {
        padding: 16px;
        display: block;
        background: rgba(30, 30, 46, 0.4);
    }
    .tb-hover-section.collapsed .tb-hover-section-body {
        display: none;
    }
    .tb-hover-enable-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid var(--tb-border);
        margin-bottom: 12px;
    }
    .tb-hover-enable-row label {
        font-size: 12px;
        color: var(--tb-text);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-hover-enable-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #f9e2af;
    }
    .tb-hover-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .tb-hover-control-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 100px;
        flex-shrink: 0;
    }
    .tb-hover-control-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: var(--tb-border);
        border-radius: 2px;
        outline: none;
    }
    .tb-hover-control-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: #f9e2af;
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-hover-control-row input[type="number"],
    .tb-hover-control-row input[type="text"] {
        width: 70px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-hover-control-row input[type="color"] {
        width: 36px;
        height: 28px;
        padding: 2px;
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
    }
    .tb-hover-control-row select {
        flex: 1;
        padding: 6px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    .tb-hover-colors-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }
    .tb-hover-color-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .tb-hover-color-item label {
        font-size: 10px;
        color: var(--tb-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .tb-hover-color-input {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    .tb-hover-color-input input[type="color"] {
        width: 32px;
        height: 26px;
        padding: 1px;
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
    }
    .tb-hover-color-input input[type="text"] {
        flex: 1;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 10px;
    }
    .tb-hover-transform-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        border-bottom: 1px solid rgba(249, 226, 175, 0.2);
    }
    .tb-hover-transform-row:last-child {
        border-bottom: none;
    }
    .tb-hover-transform-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 90px;
    }
    /* Toggle Switch - Modern iOS-style */
    .tb-toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }
    .tb-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .tb-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        transition: 0.3s;
        border-radius: 24px;
    }
    .tb-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        bottom: 2px;
        background: var(--tb-text-muted);
        transition: 0.3s;
        border-radius: 50%;
    }
    .tb-toggle-switch input:checked + .tb-toggle-slider {
        background: linear-gradient(135deg, #a6e3a1, #94e2d5);
        border-color: #a6e3a1;
    }
    .tb-toggle-switch input:checked + .tb-toggle-slider:before {
        transform: translateX(20px);
        background: #1e1e2e;
    }
    .tb-toggle-switch input:focus + .tb-toggle-slider {
        box-shadow: 0 0 0 2px rgba(166, 227, 161, 0.3);
    }
    /* Hover Row & Subsection Styles */
    .tb-hover-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(249, 226, 175, 0.1);
    }
    .tb-hover-row:last-child {
        border-bottom: none;
    }
    .tb-hover-row label {
        font-size: 12px;
        font-weight: 500;
        color: #cdd6f4;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 80px;
    }
    .tb-hover-row label span {
        color: #f9e2af;
        font-weight: 700;
        min-width: 55px;
        font-size: 13px;
    }
    .tb-hover-row input[type="range"] {
        flex: 1;
        max-width: 140px;
        height: 6px;
        -webkit-appearance: none;
        appearance: none;
        background: linear-gradient(90deg, rgba(249, 226, 175, 0.3), rgba(249, 226, 175, 0.15));
        border-radius: 3px;
        outline: none;
        cursor: pointer;
    }
    .tb-hover-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        background: linear-gradient(135deg, #f9e2af, #f5c211);
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(249, 226, 175, 0.4);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .tb-hover-row input[type="range"]::-webkit-slider-thumb:hover {
        transform: scale(1.15);
        box-shadow: 0 3px 10px rgba(249, 226, 175, 0.6);
    }
    .tb-hover-row select {
        padding: 8px 12px;
        background: rgba(249, 226, 175, 0.08);
        border: 1px solid rgba(249, 226, 175, 0.25);
        border-radius: 6px;
        color: #cdd6f4;
        font-size: 12px;
        min-width: 100px;
    }
    .tb-hover-subsection {
        margin: 12px 0;
        border: 1px solid rgba(249, 226, 175, 0.25);
        border-radius: 8px;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(249, 226, 175, 0.08) 0%, rgba(30, 30, 46, 0.95) 100%);
    }
    .tb-hover-subsection-title {
        font-size: 11px;
        font-weight: 700;
        color: #f9e2af;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 10px 14px;
        margin: 0;
        background: linear-gradient(135deg, rgba(249, 226, 175, 0.18), rgba(249, 226, 175, 0.08));
        border-bottom: 1px solid rgba(249, 226, 175, 0.2);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-hover-subsection-content {
        padding: 14px;
    }
    .tb-hover-subsection .tb-hover-row {
        padding: 10px 14px;
    }
    .tb-hover-subsection .tb-hover-row:first-of-type {
        padding-top: 14px;
    }
    .tb-hover-subsection .tb-hover-row:last-child {
        padding-bottom: 14px;
        border-bottom: none;
    }
    .tb-hover-preview-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 8px 12px;
        background: linear-gradient(135deg, rgba(249, 226, 175, 0.2), rgba(249, 226, 175, 0.1));
        border: 1px solid rgba(249, 226, 175, 0.3);
        border-radius: 6px;
        color: #f9e2af;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.15s;
    }
    .tb-hover-preview-btn:hover {
        background: linear-gradient(135deg, rgba(249, 226, 175, 0.3), rgba(249, 226, 175, 0.2));
    }
    .tb-hover-preview-btn.active {
        background: #f9e2af;
        color: #1e1e2e;
    }
    .tb-hover-disabled {
        opacity: 0.4;
        pointer-events: none;
    }
    /* Editing state indicator in canvas */
    .tb-canvas.hover-editing .tb-module-preview {
        outline: 2px dashed rgba(249, 226, 175, 0.5);
        outline-offset: 2px;
    }

    /* ═══════════════════════════════════════════════════════════
       TRANSFORM SECTION STYLES (Divi-style transforms)
       ═══════════════════════════════════════════════════════════ */
    .tb-transform-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-transform-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: linear-gradient(135deg, rgba(137, 180, 250, 0.15), rgba(137, 180, 250, 0.08));
        cursor: pointer;
        user-select: none;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-transform-section-header:hover {
        background: linear-gradient(135deg, rgba(137, 180, 250, 0.25), rgba(137, 180, 250, 0.12));
    }
    .tb-transform-section-title {
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #89b4fa;
    }
    .tb-transform-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-transform-section.collapsed .tb-transform-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-transform-section-body {
        padding: 12px;
        display: block;
    }
    .tb-transform-section.collapsed .tb-transform-section-body {
        display: none;
    }
    .tb-transform-subsection {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(137, 180, 250, 0.15);
    }
    .tb-transform-subsection:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .tb-transform-subsection-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-transform-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .tb-transform-control-row:last-child {
        margin-bottom: 0;
    }
    .tb-transform-control-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 80px;
        flex-shrink: 0;
    }
    .tb-transform-control-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: var(--tb-border);
        border-radius: 2px;
        outline: none;
    }
    .tb-transform-control-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: #89b4fa;
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-transform-control-row input[type="number"] {
        width: 60px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-transform-control-row .tb-unit-label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 20px;
    }
    /* Scale Link Toggle */
    .tb-scale-link-toggle {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        padding: 6px 8px;
        background: rgba(137, 180, 250, 0.1);
        border-radius: 4px;
        cursor: pointer;
        user-select: none;
        font-size: 11px;
        color: var(--tb-text-muted);
    }
    .tb-scale-link-toggle:hover {
        background: rgba(137, 180, 250, 0.15);
    }
    .tb-scale-link-toggle input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #89b4fa;
    }
    .tb-scale-link-toggle.linked {
        background: rgba(137, 180, 250, 0.2);
        color: #89b4fa;
    }
    /* Transform Origin Grid */
    .tb-transform-origin-wrapper {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .tb-transform-origin-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4px;
        width: 90px;
        height: 90px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        padding: 6px;
    }
    .tb-transform-origin-point {
        width: 100%;
        aspect-ratio: 1;
        background: var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-transform-origin-point:hover {
        background: rgba(137, 180, 250, 0.4);
    }
    .tb-transform-origin-point.active {
        background: #89b4fa;
        box-shadow: 0 0 8px rgba(137, 180, 250, 0.5);
    }
    .tb-transform-origin-point::after {
        content: '';
        width: 6px;
        height: 6px;
        background: var(--tb-text-muted);
        border-radius: 50%;
        opacity: 0.5;
    }
    .tb-transform-origin-point.active::after {
        background: #1e1e2e;
        opacity: 1;
    }
    .tb-transform-origin-value {
        font-size: 11px;
        color: var(--tb-text-muted);
        text-align: center;
        padding: 4px 8px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
    }
    .tb-transform-origin-custom {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }
    .tb-transform-origin-custom input {
        width: 60px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-transform-origin-custom label {
        font-size: 10px;
        color: var(--tb-text-muted);
        display: flex;
        flex-direction: column;
        gap: 2px;
        align-items: center;
    }
    /* Transform Reset Button */
    .tb-transform-reset-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 8px 12px;
        background: rgba(137, 180, 250, 0.1);
        border: 1px solid rgba(137, 180, 250, 0.2);
        border-radius: 6px;
        color: #89b4fa;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.15s;
    }
    .tb-transform-reset-btn:hover {
        background: rgba(137, 180, 250, 0.2);
    }
    /* Transform badge for modules */
    .tb-transform-badge {
        font-size: 10px;
        padding: 2px 4px;
        background: rgba(137, 180, 250, 0.2);
        border-radius: 3px;
        margin-left: 4px;
        vertical-align: middle;
    }

    /* ═══════════════════════════════════════════════════════════
       FILTER SECTION STYLES (Divi-style CSS filters)
       ═══════════════════════════════════════════════════════════ */
    .tb-filter-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-filter-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.08));
        cursor: pointer;
        user-select: none;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-filter-section-header:hover {
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.25), rgba(249, 115, 22, 0.12));
    }
    .tb-filter-section-title {
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #f97316;
    }
    .tb-filter-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-filter-section.collapsed .tb-filter-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-filter-section-body {
        padding: 12px;
        display: block;
    }
    .tb-filter-section.collapsed .tb-filter-section-body {
        display: none;
    }
    .tb-filter-preset-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(249, 115, 22, 0.15);
    }
    .tb-filter-preset-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 50px;
        flex-shrink: 0;
    }
    .tb-filter-preset-row select {
        flex: 1;
        padding: 6px 10px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        cursor: pointer;
    }
    .tb-filter-preset-row select:hover {
        border-color: #f97316;
    }
    .tb-filter-subsection {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(249, 115, 22, 0.15);
    }
    .tb-filter-subsection:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .tb-filter-subsection-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-filter-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .tb-filter-control-row:last-child {
        margin-bottom: 0;
    }
    .tb-filter-control-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 80px;
        flex-shrink: 0;
    }
    .tb-filter-control-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: var(--tb-border);
        border-radius: 2px;
        outline: none;
    }
    .tb-filter-control-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: #f97316;
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-filter-control-row input[type="range"]::-moz-range-thumb {
        width: 14px;
        height: 14px;
        background: #f97316;
        border-radius: 50%;
        cursor: pointer;
        border: none;
    }
    .tb-filter-control-row input[type="number"] {
        width: 60px;
        padding: 4px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
        text-align: center;
    }
    .tb-filter-control-row .tb-unit-label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 20px;
    }
    /* Hue Rotate Color Wheel */
    .tb-hue-wheel-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tb-hue-wheel {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: conic-gradient(red, yellow, lime, aqua, blue, magenta, red);
        position: relative;
        flex-shrink: 0;
    }
    .tb-hue-wheel::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 8px;
        height: 8px;
        background: var(--tb-surface);
        border: 2px solid #fff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .tb-hue-indicator {
        position: absolute;
        top: 0;
        left: 50%;
        width: 4px;
        height: 4px;
        background: var(--tb-surface-2);
        border-radius: 50%;
        transform-origin: 50% 10px;
        box-shadow: 0 0 3px rgba(0,0,0,0.5);
    }
    /* Filter Preview Thumbnail */
    .tb-filter-preview {
        width: 100%;
        height: 60px;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 12px;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-filter-preview-content {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-size: 11px;
        font-weight: 500;
    }
    .tb-filter-preview-icon {
        font-size: 24px;
    }
    .tb-filter-preview-label {
        position: absolute;
        bottom: 4px;
        right: 6px;
        font-size: 9px;
        color: rgba(255,255,255,0.7);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    /* Filter Reset Button */
    .tb-filter-reset-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 8px 12px;
        background: rgba(249, 115, 22, 0.1);
        border: 1px solid rgba(249, 115, 22, 0.2);
        border-radius: 6px;
        color: #f97316;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.15s;
    }
    .tb-filter-reset-btn:hover {
        background: rgba(249, 115, 22, 0.2);
    }
    /* Filter badge for modules */
    .tb-filter-badge {
        font-size: 10px;
        padding: 2px 4px;
        background: rgba(249, 115, 22, 0.2);
        border-radius: 3px;
        margin-left: 4px;
        vertical-align: middle;
        color: #f97316;
    }

    /* ═══════════════════════════════════════════════════════════
       POSITION SECTION STYLES (Divi-style positioning)
       ═══════════════════════════════════════════════════════════ */
    .tb-position-section {
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .tb-position-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(168, 85, 247, 0.08));
        cursor: pointer;
        user-select: none;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-position-section-header:hover {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.25), rgba(168, 85, 247, 0.12));
    }
    .tb-position-section-title {
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #a855f7;
    }
    .tb-position-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-position-section.collapsed .tb-position-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-position-section-body {
        padding: 12px;
        display: block;
    }
    .tb-position-section.collapsed .tb-position-section-body {
        display: none;
    }
    .tb-position-type-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(168, 85, 247, 0.15);
    }
    .tb-position-type-row label {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .tb-position-type-row select {
        width: 100%;
        padding: 8px 12px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 12px;
        cursor: pointer;
    }
    .tb-position-type-row select:hover {
        border-color: #a855f7;
    }
    .tb-position-info {
        font-size: 10px;
        color: var(--tb-text-muted);
        padding: 8px;
        background: rgba(168, 85, 247, 0.08);
        border-radius: 4px;
        line-height: 1.4;
    }
    .tb-position-info code {
        background: rgba(168, 85, 247, 0.15);
        padding: 1px 4px;
        border-radius: 3px;
        font-family: monospace;
        font-size: 10px;
    }
    .tb-position-warning {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 10px;
        color: #f59e0b;
        padding: 8px 10px;
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: 6px;
        margin-bottom: 12px;
    }
    .tb-position-warning-icon {
        font-size: 14px;
        flex-shrink: 0;
    }
    /* Position Offset Box (visual box model style) */
    .tb-position-offset-box {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(168, 85, 247, 0.15);
    }
    .tb-position-offset-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-position-offset-visual {
        position: relative;
        width: 100%;
        height: 140px;
        background: var(--tb-surface);
        border: 2px dashed rgba(168, 85, 247, 0.3);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-position-offset-center {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.3), rgba(168, 85, 247, 0.15));
        border: 2px solid rgba(168, 85, 247, 0.5);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #a855f7;
        font-weight: 600;
    }
    .tb-position-offset-input {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .tb-position-offset-input.top {
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        flex-direction: column;
    }
    .tb-position-offset-input.right {
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        flex-direction: row-reverse;
    }
    .tb-position-offset-input.bottom {
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        flex-direction: column-reverse;
    }
    .tb-position-offset-input.left {
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
    }
    .tb-position-offset-input input {
        width: 50px;
        padding: 4px 6px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 10px;
        text-align: center;
    }
    .tb-position-offset-input input:focus {
        border-color: #a855f7;
        outline: none;
    }
    .tb-position-offset-input input.has-value {
        border-color: #a855f7;
        background: rgba(168, 85, 247, 0.1);
    }
    .tb-position-offset-input select {
        width: 40px;
        padding: 4px 2px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 9px;
    }
    .tb-position-offset-label {
        font-size: 9px;
        color: var(--tb-text-muted);
        text-transform: uppercase;
    }
    /* Z-Index Section */
    .tb-zindex-section {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(168, 85, 247, 0.15);
    }
    .tb-zindex-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-zindex-input-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .tb-zindex-input-row input {
        flex: 1;
        padding: 8px 12px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 12px;
        text-align: center;
    }
    .tb-zindex-input-row input:focus {
        border-color: #a855f7;
        outline: none;
    }
    .tb-zindex-presets {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }
    .tb-zindex-preset {
        padding: 4px 8px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text-muted);
        font-size: 10px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-zindex-preset:hover {
        border-color: #a855f7;
        color: #a855f7;
    }
    .tb-zindex-preset.active {
        background: rgba(168, 85, 247, 0.15);
        border-color: #a855f7;
        color: #a855f7;
    }
    /* Sticky Options */
    .tb-sticky-section {
        margin-bottom: 12px;
    }
    .tb-sticky-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tb-sticky-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .tb-sticky-control-row label {
        font-size: 11px;
        color: var(--tb-text-muted);
        min-width: 100px;
        flex-shrink: 0;
    }
    .tb-sticky-control-row input {
        flex: 1;
        padding: 6px 10px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    .tb-sticky-control-row input:focus {
        border-color: #a855f7;
        outline: none;
    }
    /* Quick Position Actions */
    .tb-position-quick-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tb-position-quick-btn {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 6px 10px;
        background: rgba(168, 85, 247, 0.1);
        border: 1px solid rgba(168, 85, 247, 0.2);
        border-radius: 4px;
        color: #a855f7;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-position-quick-btn:hover {
        background: rgba(168, 85, 247, 0.2);
    }
    /* Position badge for modules */
    .tb-position-badge {
        font-size: 10px;
        padding: 2px 4px;
        background: rgba(168, 85, 247, 0.2);
        border-radius: 3px;
        margin-left: 4px;
        vertical-align: middle;
        color: #a855f7;
    }
    /* Positioned module canvas indicators */
    .tb-module.tb-positioned {
        position: relative;
    }
    .tb-module.tb-positioned::before {
        content: '';
        position: absolute;
        inset: -2px;
        border: 2px dashed rgba(168, 85, 247, 0.5);
        border-radius: 6px;
        pointer-events: none;
        z-index: 1;
    }
    .tb-module.tb-position-absolute::before,
    .tb-module.tb-position-fixed::before {
        border-color: rgba(245, 158, 11, 0.6);
        animation: tb-position-pulse 2s ease-in-out infinite;
    }
    @keyframes tb-position-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .tb-zindex-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        min-width: 20px;
        height: 20px;
        background: #a855f7;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 600;
        color: #fff;
        z-index: 10;
        padding: 0 4px;
    }
    .tb-position-disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    }
    
    /* ═══════════════════════════════════════════════════════════
       SECTION/ROW/COLUMN/MODULE ELEMENTS
       ═══════════════════════════════════════════════════════════ */
    .tb-section {
        background: var(--tb-bg-secondary);
        position: relative;
        padding: 48px 16px 20px 16px;
        border: 1px solid var(--tb-border);
        transition: all 0.15s;
        margin-top: 8px;
    }
    .tb-section:hover { border-color: rgba(137, 180, 250, 0.3); }
    .tb-section.selected { border-color: var(--tb-accent); }
    
    .tb-row {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 8px 16px;
        margin-bottom: 16px;
        background: transparent;
        border-radius: 8px;
        border: 1px dashed transparent;
        transition: border-color 0.2s;
    }
    .tb-row:hover {
        border-color: rgba(137, 180, 250, 0.3);
    }
    .tb-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 0;
        opacity: 1;
        transition: opacity 0.2s;
        pointer-events: auto;
    }
    .tb-row:hover .tb-row-header {
        opacity: 1;
        pointer-events: auto;
    }
    .tb-row-columns {
        display: flex;
        gap: 16px;
    }
    .tb-layout-selector {
        display: flex;
        align-items: center;
        gap: 4px;
        background: var(--tb-surface);
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid var(--tb-border);
    }
    .tb-layout-btn {
        width: 36px;
        height: 24px;
        padding: 2px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 4px;
        cursor: pointer;
        color: var(--tb-text-muted);
        transition: all 0.15s;
    }
    .tb-layout-btn:hover {
        background: var(--tb-surface-2);
        color: var(--tb-text);
        border-color: var(--tb-border);
    }
    .tb-layout-btn.active {
        background: var(--tb-accent);
        color: #fff;
        border-color: var(--tb-accent);
    }
    .tb-layout-btn svg {
        width: 100%;
        height: 100%;
    }
    .tb-layout-divider {
        width: 1px;
        height: 16px;
        background: var(--tb-border);
        margin: 0 4px;
    }
    .tb-row-actions {
        display: flex;
        gap: 4px;
    }
    .tb-row-action-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        cursor: pointer;
        color: var(--tb-text-muted);
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .tb-row-action-btn:hover {
        background: #f87171;
        border-color: #f87171;
        color: #fff;
    }
    
    .tb-column {
        flex: 1;
        min-height: 60px;
        padding: 8px;
        background: var(--tb-bg-tertiary); border: 2px dashed var(--tb-border);
        border-radius: 4px;
    }
    .tb-column:hover { border-color: var(--tb-accent); }
    
    .tb-module {
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        
        
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-module:hover {
        border-color: var(--tb-accent);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .tb-module.selected {
        border-color: var(--tb-accent);
        box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.2);
    }
    
    /* Element Controls */
    .tb-element-controls {
        position: absolute;
        top: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        padding: 4px;
        gap: 4px;
        z-index: 100;
    }
    .tb-section:hover .tb-element-controls,
    .tb-section.selected .tb-element-controls {
        display: flex;
    }
    .tb-control-btn {
        padding: 4px 8px;
        background: transparent;
        border: none;
        color: var(--tb-text-muted);
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
    }
    .tb-control-btn:hover {
        background: var(--tb-surface-2);
        color: var(--tb-text);
    }
    .tb-control-btn.danger:hover { color: var(--tb-danger); }
    
    /* Toast Notifications */
    .tb-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    }
    .tb-toast.success { border-color: var(--tb-success); }
    .tb-toast.error { border-color: var(--tb-danger); }
    @keyframes slideIn {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    /* Loading Overlay */
    .tb-loading {
        position: fixed;
        inset: 0;
        background: rgba(30, 30, 46, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    .tb-loading.hidden { display: none; }
    .tb-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--tb-border);
        border-top-color: var(--tb-accent);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
        /* Drag & Drop Enhancements */
    .tb-module-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    
    .tb-drop-target {
        box-shadow: 0 0 0 2px var(--tb-accent);
    }
    
    .tb-column.tb-drag-over {
        background: rgba(137, 180, 250, 0.1);
        border-color: var(--tb-accent) !important;
    }
    
    .tb-insertion-line {
        height: 3px;
        background: var(--tb-accent);
        margin: 4px 0;
        border-radius: 2px;
        animation: pulse 1s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .tb-column-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 12px;
        color: var(--tb-text-muted);
        font-size: 12px;
        gap: 8px;
    }
    
    .tb-column-empty-icon {
        font-size: 24px;
        opacity: 0.5;
    }
    
    .tb-module.tb-dragging {
        opacity: 0.4;
        transform: rotate(2deg);
    }
    
    .tb-module-header {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px;
        background: var(--tb-surface-2);
        border-radius: 4px 4px 0 0;
        margin: -12px -12px 8px -12px;
        border-bottom: 1px solid var(--tb-border);
    }
    
    .tb-module-type-icon {
        font-size: 14px;
    }
    
    .tb-module-type-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--tb-text-muted);
        flex: 1;
    }
    .tb-module-header .tb-hover-badge {
        font-size: 10px;
        padding: 2px 4px;
        margin-left: 4px;
        animation: tb-hover-pulse 2s ease-in-out infinite;
    }
    @keyframes tb-hover-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .tb-module.tb-has-hover {
        box-shadow: 0 0 0 1px rgba(249, 226, 175, 0.3);
    }
    .tb-module.tb-hover-preview-active .tb-module-preview {
        outline: 3px solid #f9e2af !important;
        outline-offset: -3px;
    }

    /* ═══════════════════════════════════════════════════════════
       ANIMATION SYSTEM - CSS Keyframes
       ═══════════════════════════════════════════════════════════ */

    /* Animation badge for modules with animations enabled */
    .tb-module.tb-has-animation {
        box-shadow: 0 0 0 1px rgba(137, 180, 250, 0.3);
    }
    .tb-module-header .tb-animation-badge {
        font-size: 10px;
        padding: 2px 4px;
        margin-left: 4px;
        animation: tb-animation-pulse 2s ease-in-out infinite;
        background: rgba(137, 180, 250, 0.2);
        border-radius: 3px;
    }
    @keyframes tb-animation-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Animation Section Styles */
    .tb-animation-section {
        margin-top: 16px;
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .tb-animation-section.collapsed .tb-animation-section-body {
        display: none;
    }
    .tb-animation-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px;
        background: var(--tb-surface-2);
        cursor: pointer;
        user-select: none;
    }
    .tb-animation-section-header:hover {
        background: var(--tb-surface-2);
    }
    .tb-animation-section-title {
        font-weight: 600;
        font-size: 12px;
        color: var(--tb-accent);
    }
    .tb-animation-section-toggle {
        font-size: 10px;
        color: var(--tb-text-muted);
        transition: transform 0.2s;
    }
    .tb-animation-section.collapsed .tb-animation-section-toggle {
        transform: rotate(-90deg);
    }
    .tb-animation-section-body {
        padding: 12px;
        background: var(--tb-surface);
    }

    /* Animation Enable Row */
    .tb-animation-enable-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--tb-border);
        margin-bottom: 12px;
    }
    .tb-animation-enable-row label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        cursor: pointer;
    }
    .tb-animation-enable-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* Animation Controls */
    .tb-animation-controls.tb-animation-disabled {
        opacity: 0.4;
        pointer-events: none;
    }
    .tb-animation-control-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .tb-animation-control-row label {
        min-width: 80px;
        font-size: 11px;
        color: var(--tb-text-muted);
    }
    .tb-animation-control-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        background: var(--tb-surface-2);
        border-radius: 2px;
    }
    .tb-animation-control-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 14px;
        height: 14px;
        background: var(--tb-accent);
        border-radius: 50%;
        cursor: pointer;
    }
    .tb-animation-control-row input[type="number"],
    .tb-animation-control-row select {
        width: 80px;
        padding: 4px 6px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 11px;
    }
    .tb-animation-control-row select {
        width: 120px;
    }

    /* Animation Subsections */
    .tb-animation-subsection {
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-animation-subsection:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .tb-animation-subsection-title {
        font-size: 11px;
        font-weight: 600;
        color: var(--tb-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 10px;
    }

    /* Animation Preview Button */
    .tb-animation-preview-btn {
        width: 100%;
        padding: 10px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 6px;
        color: #fff;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 12px;
        transition: all 0.15s;
    }
    .tb-animation-preview-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    .tb-animation-preview-btn.playing {
        background: linear-gradient(135deg, #f38ba8 0%, #eb6f92 100%);
    }

    /* Animation Preview Panel */
    .tb-animation-preview-panel {
        margin-top: 12px;
        padding: 12px;
        background: var(--tb-surface-2);
        border-radius: 8px;
        text-align: center;
    }
    .tb-animation-preview-box {
        width: 80px;
        height: 80px;
        margin: 0 auto 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .tb-animation-preview-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-size: 11px;
        color: var(--tb-text-muted);
    }
    .tb-animation-preview-controls label {
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }
    .tb-animation-speed-select {
        padding: 2px 6px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text);
        font-size: 10px;
    }

    /* ═══════════════════════════════════════════════════════════
       ENTRANCE ANIMATION KEYFRAMES
       ═══════════════════════════════════════════════════════════ */

    /* Fade Animations */
    @keyframes tb-fade {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes tb-fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes tb-fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes tb-fadeInDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes tb-fadeInLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes tb-fadeInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* Slide Animations */
    @keyframes tb-slideInUp {
        from { transform: translateY(100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes tb-slideInDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    @keyframes tb-slideInLeft {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes tb-slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Zoom Animations */
    @keyframes tb-zoomIn {
        from { opacity: 0; transform: scale(0.3); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes tb-zoomInUp {
        from { opacity: 0; transform: scale(0.3) translateY(100px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    @keyframes tb-zoomInDown {
        from { opacity: 0; transform: scale(0.3) translateY(-100px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Bounce Animations */
    @keyframes tb-bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }
    @keyframes tb-bounceInUp {
        0% { opacity: 0; transform: translateY(100px); }
        60% { opacity: 1; transform: translateY(-20px); }
        80% { transform: translateY(10px); }
        100% { transform: translateY(0); }
    }
    @keyframes tb-bounceInDown {
        0% { opacity: 0; transform: translateY(-100px); }
        60% { opacity: 1; transform: translateY(20px); }
        80% { transform: translateY(-10px); }
        100% { transform: translateY(0); }
    }

    /* Flip Animations */
    @keyframes tb-flipInX {
        0% { opacity: 0; transform: perspective(400px) rotateX(90deg); }
        40% { transform: perspective(400px) rotateX(-20deg); }
        60% { opacity: 1; transform: perspective(400px) rotateX(10deg); }
        80% { transform: perspective(400px) rotateX(-5deg); }
        100% { transform: perspective(400px) rotateX(0deg); }
    }
    @keyframes tb-flipInY {
        0% { opacity: 0; transform: perspective(400px) rotateY(90deg); }
        40% { transform: perspective(400px) rotateY(-20deg); }
        60% { opacity: 1; transform: perspective(400px) rotateY(10deg); }
        80% { transform: perspective(400px) rotateY(-5deg); }
        100% { transform: perspective(400px) rotateY(0deg); }
    }

    /* Roll Animation */
    @keyframes tb-rollIn {
        from { opacity: 0; transform: translateX(-100%) rotate(-120deg); }
        to { opacity: 1; transform: translateX(0) rotate(0deg); }
    }

    /* Special Animations */
    @keyframes tb-lightSpeedIn {
        0% { opacity: 0; transform: translateX(100%) skewX(-30deg); }
        60% { opacity: 1; transform: skewX(20deg); }
        80% { transform: skewX(-5deg); }
        100% { transform: translateX(0) skewX(0deg); }
    }
    @keyframes tb-jackInTheBox {
        0% { opacity: 0; transform: scale(0.1) rotate(30deg); transform-origin: center bottom; }
        50% { transform: rotate(-10deg); }
        70% { transform: rotate(3deg); }
        100% { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    /* Animation utility class for triggering */
    .tb-animate-active {
        animation-fill-mode: both;
    }
    .tb-animate-paused {
        animation-play-state: paused;
    }

    .tb-module-actions {
        display: flex;
        gap: 2px;
        opacity: 0;
        transition: opacity 0.15s;
    }
    
    .tb-module:hover .tb-module-actions {
        opacity: 1;
    }
    
    .tb-module-action-btn {
        padding: 2px 6px;
        background: transparent;
        border: none;
        color: var(--tb-text-muted);
        cursor: pointer;
        border-radius: 3px;
        font-size: 12px;
    }

    .tb-module-action-btn:hover {
        background: var(--tb-surface);
        color: var(--tb-text);
    }

    .tb-module-action-btn.danger:hover {
        background: var(--tb-danger);
        color: #dc2626;
    }

    /* PROMINENT EDIT BUTTON - Primary editing action */
    .tb-module-action-btn.tb-edit-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        transition: all 0.2s ease;
    }
    .tb-module-action-btn.tb-edit-btn:hover {
        background: linear-gradient(135deg, #059669, #047857);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
    }

    /* Delete button styling */
    .tb-module-action-btn.tb-delete-btn:hover {
        background: var(--tb-danger, #ef4444);
        color: #fff;
    }
    
    .tb-module-preview {
        min-height: 20px;
        overflow: hidden;
    }
    
    .tb-row-controls {
        position: absolute;
        right: -60px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
        flex-direction: column;
        gap: 4px;
    }
    
    .tb-row:hover .tb-row-controls {
        display: flex;
    }
    
    .tb-row {
        position: relative;
    }
    
    .tb-control-btn-small {
        padding: 2px 6px;
        font-size: 10px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        color: var(--tb-text-muted);
        cursor: pointer;
        border-radius: 3px;
    }
    
    .tb-control-btn-small:hover {
        background: var(--tb-surface-2);
        color: var(--tb-text);
    }
    
    .tb-main-drop-zone.drag-over {
        border-color: var(--tb-accent);
        background: rgba(137, 180, 250, 0.1);
    }
    
    .tb-add-section-zone.drag-over {
        border-color: var(--tb-accent);
        background: rgba(137, 180, 250, 0.1);
    }

    /* ═══════════════════════════════════════════════════════════
       ICON PICKER MODAL
       ═══════════════════════════════════════════════════════════ */
    .tb-icon-picker-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 110001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    .tb-icon-picker-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .tb-icon-picker-modal {
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 12px;
        width: 600px;
        max-width: 95vw;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        transform: scale(0.95);
        transition: transform 0.2s ease;
        overflow: hidden;
    }
    .tb-icon-picker-header,
    .tb-icon-picker-tabs,
    .tb-icon-picker-search {
        flex-shrink: 0;
    }
    .tb-icon-picker-overlay.active .tb-icon-picker-modal {
        transform: scale(1);
    }
    .tb-icon-picker-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-icon-picker-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--tb-text);
    }
    .tb-icon-picker-close {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        color: var(--tb-text-muted);
        font-size: 20px;
        cursor: pointer;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .tb-icon-picker-close:hover {
        background: var(--tb-surface);
        color: var(--tb-text);
    }
    .tb-icon-picker-tabs {
        display: flex;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--tb-border);
        overflow: visible;
        background: var(--tb-surface-2);
    }
    .tb-icon-picker-tab {
        padding: 8px 16px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 6px;
        color: var(--tb-text-muted);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s;
    }
    .tb-icon-picker-tab:hover {
        background: var(--tb-surface);
        color: var(--tb-text);
    }
    .tb-icon-picker-tab.active {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-icon-picker-search {
        padding: 12px 20px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-icon-picker-search input {
        width: 100%;
        padding: 10px 14px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        color: var(--tb-text);
        font-size: 14px;
    }
    .tb-icon-picker-search input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    .tb-icon-picker-search input::placeholder {
        color: var(--tb-text-muted);
    }
    .tb-icon-picker-grid {
        flex: 1;
        overflow-y: auto;
        padding: 16px 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
        gap: 8px;
        align-content: start;
    }
    .tb-icon-picker-item {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 20px;
        color: var(--tb-text);
        transition: all 0.15s;
    }
    .tb-icon-picker-item:hover {
        background: var(--tb-accent);
        border-color: var(--tb-accent);
        color: #1e1e2e;
        transform: scale(1.1);
    }
    .tb-icon-picker-item i {
        font-size: 20px;
    }
    .tb-icon-picker-item .material-icons,
    .tb-icon-picker-item .material-icons-outlined {
        font-size: 24px;
    }
    .tb-icon-picker-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px 20px;
        color: var(--tb-text-muted);
    }

    /* New Icon Picker styles */
    .tb-icon-picker-tabs .tb-icon-tab {
        padding: 10px 24px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        color: var(--tb-text-muted);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        box-shadow: none;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
    }
    .tb-icon-picker-tabs .tb-icon-tab:hover {
        background: transparent;
        color: var(--tb-text);
        border-bottom-color: var(--tb-text-muted);
    }
    .tb-icon-picker-tabs .tb-icon-tab.active {
        background: transparent;
        color: var(--tb-accent);
        border-bottom-color: var(--tb-accent);
    }
    .tb-icon-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px 20px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-icon-category { display: inline-block; margin: 4px;
        padding: 4px 10px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 4px;
        color: var(--tb-text-muted);
        font-size: 12px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tb-icon-category:hover {
        background: var(--tb-surface-2);
        color: var(--tb-text);
    }
    .tb-icon-category.active {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-icon-option {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        cursor: pointer;
        color: var(--tb-text);
        transition: all 0.15s;
    }
    .tb-icon-option:hover {
        background: var(--tb-accent);
        border-color: var(--tb-accent);
        color: #1e1e2e;
        transform: scale(1.05);
    }
    .tb-icon-option svg {
        width: 28px;
        height: 28px;
    }
    .tb-icon-option i {
        font-size: 26px;
        line-height: 1;
    }
    .tb-icon-option.emoji {
        font-size: 26px;
    }
    .tb-icon-picker-preview {
        padding: 12px 20px;
        border-top: 1px solid var(--tb-border);
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--tb-surface);
    }
    .tb-icon-picker-preview-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        font-size: 24px;
    }
    .tb-icon-picker-preview-info {
        flex: 1;
    }
    .tb-icon-picker-preview-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--tb-text);
    }
    .tb-icon-picker-preview-code {
        font-size: 12px;
        color: var(--tb-text-muted);
        font-family: monospace;
    }

    /* ═══════════════════════════════════════════════════════════
       FONT PICKER MODAL
       ═══════════════════════════════════════════════════════════ */
    .tb-font-picker-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 110001;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
    }
    .tb-font-picker-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .tb-font-picker-modal {
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 12px;
        width: 650px;
        max-width: 95vw;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        transform: scale(0.95);
        transition: transform 0.2s ease;
    }
    .tb-font-picker-overlay.active .tb-font-picker-modal {
        transform: scale(1);
    }
    .tb-font-picker-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-font-picker-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--tb-text);
    }
    .tb-font-picker-close {
        width: 32px;
        height: 32px;
        border: none;
        background: transparent;
        color: var(--tb-text-muted);
        font-size: 20px;
        cursor: pointer;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .tb-font-picker-close:hover {
        background: var(--tb-surface);
        color: var(--tb-text);
    }
    .tb-font-picker-preview-box {
        padding: 20px;
        background: var(--tb-surface);
        border-bottom: 1px solid var(--tb-border);
        text-align: center;
    }
    .tb-font-picker-preview-text {
        font-size: 24px;
        color: var(--tb-text);
        padding: 16px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        min-height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tb-font-picker-tabs {
        display: flex;
        gap: 4px;
        padding: 12px 20px;
        border-bottom: 1px solid var(--tb-border);
        overflow-x: auto;
    }
    .tb-font-picker-tab {
        padding: 8px 16px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 6px;
        color: var(--tb-text-muted);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.15s;
    }
    .tb-font-picker-tab:hover {
        background: var(--tb-surface);
        color: var(--tb-text);
    }
    .tb-font-picker-tab.active {
        background: var(--tb-accent);
        color: #1e1e2e;
        border-color: var(--tb-accent);
    }
    .tb-font-picker-search {
        padding: 12px 20px;
        border-bottom: 1px solid var(--tb-border);
    }
    .tb-font-picker-search input {
        width: 100%;
        padding: 10px 14px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        color: var(--tb-text);
        font-size: 14px;
    }
    .tb-font-picker-search input:focus {
        outline: none;
        border-color: var(--tb-accent);
    }
    .tb-font-picker-list {
        flex: 1;
        overflow-y: auto;
        padding: 12px 20px;
    }
    .tb-font-picker-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: var(--tb-surface);
        border: 1px solid var(--tb-border);
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 8px;
        transition: all 0.15s;
    }
    .tb-font-picker-item:hover {
        background: var(--tb-surface-2);
        border-color: var(--tb-accent);
    }
    .tb-font-picker-item.selected {
        background: rgba(137, 180, 250, 0.15);
        border-color: var(--tb-accent);
    }
    .tb-font-picker-item-name {
        flex: 1;
        font-size: 18px;
        color: var(--tb-text);
    }
    .tb-font-picker-item-category {
        font-size: 11px;
        color: var(--tb-text-muted);
        background: var(--tb-surface-2);
        padding: 4px 8px;
        border-radius: 4px;
    }
    .tb-font-picker-footer {
        padding: 16px 20px;
        border-top: 1px solid var(--tb-border);
        display: flex;
        gap: 12px;
        align-items: center;
        background: var(--tb-surface);
    }
    .tb-font-picker-options {
        display: flex;
        gap: 12px;
        flex: 1;
    }
    .tb-font-picker-option {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .tb-font-picker-option label {
        font-size: 11px;
        color: var(--tb-text-muted);
        text-transform: uppercase;
    }
    .tb-font-picker-option select,
    .tb-font-picker-option input {
        padding: 6px 10px;
        background: var(--tb-surface-2);
        border: 1px solid var(--tb-border);
        border-radius: 6px;
        color: var(--tb-text);
        font-size: 13px;
        min-width: 80px;
    }
    .tb-font-picker-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--tb-text-muted);
    }

    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="tb-loading hidden" id="tb-loading">
        <div class="tb-spinner"></div>
    </div>

    <!-- Toolbar -->
    <header class="tb-toolbar">
        <div class="tb-toolbar-left">
            <a href="/admin/theme-builder" class="tb-btn tb-btn-icon" title="Exit Builder">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <span class="tb-divider"></span>
            <div class="tb-logo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
                </svg>
                Theme Builder
            </div>
        </div>
        
        <div class="tb-toolbar-center">
            <input type="text" class="tb-page-title" id="page-title" value="<?= $pageTitle ?>" placeholder="Page Title">
        </div>
        
        <div class="tb-toolbar-right">
            <button type="button" class="tb-btn tb-btn-icon" id="btn-undo" title="Undo" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7v6h6M3 13c0-4.97 4.03-9 9-9a9 9 0 0 1 6.36 2.64"/>
                </svg>
            </button>
            <button type="button" class="tb-btn tb-btn-icon" id="btn-redo" title="Redo" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 7v6h-6M21 13c0-4.97-4.03-9-9-9a9 9 0 0 0-6.36 2.64"/>
                </svg>
            </button>
            <span class="tb-divider"></span>
            <button type="button" class="tb-btn" id="btn-preview" title="Preview">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                Preview
            </button>
            <button type="button" class="tb-btn" id="btn-settings" title="Page Settings">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
            <span class="tb-divider"></span>
            <button type="button" class="tb-btn" id="btn-load-library" title="Load from Layout Library">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    <path d="M12 11v6M9 14h6"/>
                </svg>
                Library
            </button>
            <span class="tb-divider"></span>
            <select id="page-status-select" class="tb-btn" style="padding:8px 12px;border-radius:6px;background:var(--tb-bg-tertiary);color:var(--tb-text-primary);border:1px solid var(--tb-border);cursor:pointer;margin-right:8px">
                <option value="draft"<?= $pageStatus === "draft" ? " selected" : "" ?>>Draft</option>
                <option value="published"<?= $pageStatus === "published" ? " selected" : "" ?>>Published</option>
            </select>
            <button type="button" class="tb-btn tb-btn-primary" id="btn-save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                Save
            </button>
        </div>
    </header>
    
    <!-- Main Layout -->
    <div class="tb-main">
        <!-- Left Panel - Modules -->
        <aside class="tb-panel-left">
            <div class="tb-panel-header">
                <div class="tb-panel-title">Modules</div>
                <input type="text" class="tb-search" id="module-search" placeholder="Search modules...">
            </div>
            <div class="tb-panel-body" id="modules-panel">
                <!-- Module categories will be populated by JS -->
            </div>
        </aside>
        
        <!-- Center - Canvas -->
        <main class="tb-canvas-wrapper">
            <div class="tb-canvas-toolbar">
                <div class="tb-viewport-btns">
                    <button type="button" class="tb-viewport-btn active" data-viewport="desktop" title="Desktop">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </button>
                    <button type="button" class="tb-viewport-btn" data-viewport="tablet" title="Tablet">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>
                        </svg>
                    </button>
                    <button type="button" class="tb-viewport-btn" data-viewport="mobile" title="Mobile">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>
                        </svg>
                    </button>
                </div>
                <span style="flex:1"></span>
                <button type="button" class="tb-btn" id="btn-add-section">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Section
                </button>
            </div>
            <div class="tb-canvas" id="tb-canvas">
                <!-- Dynamic hover styles for modules -->
                <style id="tb-hover-styles">/* Hover styles will be generated dynamically */</style>
                <!-- Dynamic animation styles for modules -->
                <style id="tb-animation-styles">/* Animation styles will be generated dynamically */</style>
                <div class="tb-canvas-inner desktop" id="canvas-inner">
                    <!-- Canvas content will be rendered by JS -->
                    <div class="tb-drop-zone" id="main-drop-zone">
                        <div class="tb-drop-zone-text">
                            <div class="tb-drop-zone-icon">📦</div>
                            <div>Drag modules here or click "Add Section" to start building</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Right Panel - Settings -->
        </aside>
    </div>
    
    <!-- Hidden data -->
    <input type="hidden" id="page-id" value="<?= $pageId ?>">
    <input type="hidden" id="page-slug" value="<?= $pageSlug ?>">
    <input type="hidden" id="page-status" value="<?= $pageStatus ?>">
    <input type="hidden" id="csrf-token" value="<?= $csrfToken ?>">
    <script>
    // ═══════════════════════════════════════════════════════════
    // THEME BUILDER 3.0 - MODULAR ARCHITECTURE
    // ═══════════════════════════════════════════════════════════

    const MODULE_ICONS = {
        // Content
        text: '📝', heading: '📰', image: '🖼️', button: '🔘', divider: '➖',
        spacer: '⬜', video: '🎬', audio: '🎵', code: '💻', html: '🔧',
        gallery: '🖼️', list: '📋', quote: '💬', icon: '⭐',
        // Interactive
        accordion: '📁', toggle: '📂', tabs: '📑', map: '🗺️',
        // Marketing
        cta: '📢', countdown: '⏰', testimonial: '💭', pricing: '💰',
        blurb: '💡', hero: '🦸', slider: '🎠', blog: '📰',
        // Team & Stats
        team: '👥', counter: '🔢', circle_counter: '⭕', bar_counters: '📊',
        // Forms
        contact_form: '📧', form: '📧', login: '🔐', signup: '📝', search: '🔍',
        // Social
        social: '🔗', social_follow: '🔗',
        // Navigation
        menu: '🍽️', sidebar: '📑', breadcrumbs: '🔗', logo: '🏷️',
        // Dynamic/Blog
        post_title: '📌', post_content: '📄', post_excerpt: '📝',
        featured_image: '🖼️', post_meta: 'ℹ️', author_box: '👤',
        related_posts: '📚', posts_navigation: '↔️', comments: '💬',
        // Portfolio & Sliders
        portfolio: '🎨', post_slider: '📰', video_slider: '🎬',
        // Fullwidth modules
        fullwidth_code: '💻', fullwidth_image: '🖼️', fullwidth_map: '🗺️',
        fullwidth_menu: '📑', fullwidth_slider: '🎠', fullwidth_header: '🦸',
        fullwidth_portfolio: '🎨', fullwidth_post_slider: '📰',
        // Legacy/Other
        progress: '📊', features: '✨', stats: '📈', faq: '❓',
        timeline: '📅', logo_grid: '🏢', before_after: '🔄',
        hotspots: '📍', table: '📋'
    };

    window.TB = {
        pageId: parseInt(document.getElementById('page-id').value) || 0,
        csrfToken: document.getElementById('csrf-token').value,
        content: <?= $contentJson ?>,
        modules: <?= $modulesJson ?>,
        categories: <?= $categoriesJson ?>,
        themeColors: <?= $themeColorsJson ?? '{}' ?>,
        selectedElement: null,
        history: [],
        historyIndex: -1,
        draggedModule: null,
        draggedElement: null,
        dragOverColumn: null,
        insertionIndex: -1,
        currentDevice: 'desktop',
        currentTab: 'content',
        currentState: 'normal',
        hoverStyles: {},
        hoverPreviewActive: false,
        fontawesomeIcons: []
    };
    </script>

    <!-- Theme Builder 3.0 Modular JS - v20260104h: Fixed modal preview filters/transforms -->
    <script src="/core/theme-builder/js/tb-core.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-helpers.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-modules-preview.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-modules-content.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-modules-design.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-structure.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-events.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-render.js?v=20260104L"></script>
    <script src="/core/theme-builder/js/tb-library.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-modal-editor.js?v=20260105E"></script>

    <!-- Comprehensive Element Design System -->
    <script src="/core/theme-builder/js/tb-element-schemas.js?v=20260105E"></script>
    <script src="/core/theme-builder/js/tb-modal-element-design.js?v=20260105G"></script>
    <script src="/core/theme-builder/js/tb-modal-element-design-part2.js?v=20260104k"></script>
    <script src="/core/theme-builder/js/tb-modal-element-design-part3.js?v=20260104k"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        TB.init();
        if (typeof TB.initMediaGalleryEvents === 'function') {
            TB.initMediaGalleryEvents();
        }
    });
    </script>

    <!-- Media Gallery Modal -->
    <div class="tb-media-modal" id="tb-media-modal">
        <div class="tb-media-dialog">
            <div class="tb-media-header">
                <h3>📁 Media Library</h3>
                <button type="button" class="tb-media-close" onclick="TB.closeMediaGallery()">×</button>
            </div>
            <div class="tb-media-body">
                <div class="tb-media-tabs">
                    <button type="button" class="tb-media-tab active" data-tab="upload">📤 Upload</button>
                    <button type="button" class="tb-media-tab" data-tab="library">🖼️ Library</button>
                    <button type="button" class="tb-media-tab" data-tab="stock">📷 Stock Photos</button>
                    <button type="button" class="tb-media-tab" data-tab="ai">✨ AI Generate</button>
                </div>

                <div class="tb-media-tab-content active" id="tb-media-tab-upload">
                    <div class="tb-upload-area" id="tb-upload-area">
                        <input type="file" id="tb-media-upload" accept="image/*">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📤</div>
                        <div>Drop image here or <label for="tb-media-upload" style="color: var(--tb-accent); cursor: pointer;">browse</label></div>
                    </div>
                    <div id="tb-upload-progress" style="display: none;">
                        <div style="background: var(--tb-border); border-radius: 4px; overflow: hidden;">
                            <div id="tb-upload-bar" style="height: 4px; background: var(--tb-accent); width: 0%; transition: width 0.3s;"></div>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--tb-text-muted); margin-top: 0.5rem;">Uploading...</p>
                    </div>
                </div>

                <div class="tb-media-tab-content" id="tb-media-tab-library">
                    <div class="tb-media-grid" id="tb-media-grid">
                        <?php
                        $mediaDir = dirname(CMS_APP) . '/uploads/media/';
                        if (is_dir($mediaDir)) {
                            $files = scandir($mediaDir, SCANDIR_SORT_DESCENDING);
                            $count = 0;
                            foreach ($files as $file) {
                                if ($file === '.' || $file === '..' || $file === 'thumbs' || is_dir($mediaDir . $file)) continue;
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) continue;
                                echo '<div class="tb-media-item" data-url="/uploads/media/' . htmlspecialchars($file) . '"><img src="/uploads/media/' . htmlspecialchars($file) . '" alt=""><div class="tb-media-filename">' . htmlspecialchars($file) . '</div></div>';
                                if (++$count >= 100) break;
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="tb-media-tab-content" id="tb-media-tab-stock">
                    <div class="tb-stock-search">
                        <input type="text" id="tb-stock-search-input" placeholder="Search free stock photos (Pexels)...">
                        <button type="button" class="tb-btn tb-btn-primary" onclick="TB.searchStockPhotos()">🔍 Search</button>
                    </div>
                    <div id="tb-stock-results">
                        <div class="tb-stock-loading">
                            <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">📷</p>
                            <p>Search for beautiful free photos from Pexels</p>
                        </div>
                    </div>
                </div>

                <div class="tb-media-tab-content" id="tb-media-tab-ai">
                    <div class="tb-ai-gen-form">
                        <label style="font-weight: 500;">Describe the image you want to create:</label>
                        <textarea class="tb-ai-gen-prompt" id="tb-ai-image-prompt" placeholder="A futuristic cityscape at sunset with flying cars..."></textarea>
                        <div class="tb-ai-gen-options">
                            <select id="tb-ai-image-style">
                                <option value="photorealistic">📸 Photorealistic</option>
                                <option value="digital-art">🎨 Digital Art</option>
                                <option value="illustration">✏️ Illustration</option>
                                <option value="3d-render">🧊 3D Render</option>
                            </select>
                            <select id="tb-ai-image-size">
                                <option value="1024x1024">Square (1024×1024)</option>
                                <option value="1792x1024">Landscape (1792×1024)</option>
                                <option value="1024x1792">Portrait (1024×1792)</option>
                            </select>
                            <button type="button" class="tb-btn tb-btn-ai" onclick="TB.generateAiImage()">✨ Generate</button>
                        </div>
                    </div>
                    <div class="tb-ai-gen-preview" id="tb-ai-gen-preview">
                        <div class="tb-ai-gen-status">
                            <p style="font-size: 2rem; margin-bottom: 0.5rem;">🎨</p>
                            <p>Describe your image and click Generate</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tb-media-footer">
                <button type="button" class="tb-btn" onclick="TB.closeMediaGallery()">Cancel</button>
                <button type="button" class="tb-btn tb-btn-primary" onclick="TB.selectMediaFromGallery()" id="tb-media-select-btn" disabled>Select Image</button>
            </div>
        </div>
    </div>

    <!-- Layout Library Modal (Divi-style) -->
    <div class="tb-library-modal" id="tb-library-modal">
        <div class="tb-library-dialog">
            <div class="tb-library-header">
                <h3>📚 Load from Layout Library</h3>
                <button type="button" class="tb-library-close" onclick="TB.closeLibrary()">×</button>
            </div>
            <div class="tb-library-toolbar">
                <input type="text" class="tb-library-search" id="tb-library-search" placeholder="Search layouts...">
                <select class="tb-library-filter" id="tb-library-category">
                    <option value="">All Categories</option>
                    <option value="business">Business</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="ecommerce">E-Commerce</option>
                    <option value="blog">Blog</option>
                    <option value="landing">Landing Page</option>
                    <option value="agency">Agency</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="education">Education</option>
                </select>
                <button type="button" class="tb-btn" onclick="TB.loadLibraryLayouts()">🔄 Refresh</button>
            </div>
            <div class="tb-library-body" id="tb-library-body">
                <div class="tb-library-loading">
                    <div class="tb-spinner"></div>
                    <p>Loading layouts...</p>
                </div>
            </div>
            <div class="tb-library-pages" id="tb-library-pages" style="display: none;">
                <div class="tb-library-pages-label">Select page from this layout:</div>
                <div class="tb-library-pages-list" id="tb-library-pages-list"></div>
            </div>
            <div class="tb-library-footer">
                <div class="tb-insert-mode">
                    <label>
                        <input type="radio" name="insert-mode" value="append" checked>
                        <span>➕ Append sections</span>
                    </label>
                    <label>
                        <input type="radio" name="insert-mode" value="replace">
                        <span>🔄 Replace all sections</span>
                    </label>
                </div>
                <div class="tb-library-actions">
                    <button type="button" class="tb-btn" onclick="TB.closeLibrary()">Cancel</button>
                    <button type="button" class="tb-btn tb-btn-primary" id="tb-library-insert-btn" onclick="TB.insertFromLibrary()" disabled>
                        Insert Sections
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div class="tb-icon-picker-overlay" id="tb-icon-picker-overlay" onclick="if(event.target === this) TB.closeIconPicker()">
        <div class="tb-icon-picker-modal">
            <div class="tb-icon-picker-header">
                <h3>Select Icon</h3>
                <button class="tb-icon-picker-close" onclick="TB.closeIconPicker()">&times;</button>
            </div>
            <div class="tb-icon-picker-tabs">
                <button class="tb-icon-tab active" onclick="TB.switchIconStyle('fontawesome')">Font Awesome</button>
                <button class="tb-icon-tab" onclick="TB.switchIconStyle('lucide')">Lucide</button>
                <button class="tb-icon-tab" onclick="TB.switchIconStyle('emoji')">Emoji</button>
            </div>
            <div class="tb-icon-picker-search">
                <input type="text" id="iconSearchInput" placeholder="Search icons..." oninput="TB.filterIcons(this.value)">
            </div>
            <div class="tb-icon-picker-grid" id="iconGrid"></div>
            <div class="tb-icon-picker-preview" id="icon-picker-preview" style="display:none">
                <div class="tb-icon-picker-preview-icon" id="icon-preview-display"></div>
                <div class="tb-icon-picker-preview-info">
                    <div class="tb-icon-picker-preview-name" id="icon-preview-name"></div>
                    <div class="tb-icon-picker-preview-code" id="icon-preview-code"></div>
                </div>
                <button class="tb-btn tb-btn-primary" onclick="TB.confirmIconSelection()">Select</button>
            </div>
        </div>
    </div>



</body>
</html>
