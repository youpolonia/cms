<?php
/**
 * Theme Builder 4.0 - Visual Builder (Pure PHP)
 * Three-panel layout: Modules | Canvas | Settings
 * NO React, NO npm build - pure PHP like Divi
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 5)); }

// Load TB4 module registry
require_once CMS_ROOT . '/core/tb4/init.php';
$registry = \Core\TB4\ModuleRegistry::getInstance();
$allModules = $registry->getModulesForJson();

$pageTitle = esc($page['title'] ?? 'New Page');
$pageId = (int)($page['id'] ?? $pageId ?? 0);
$pageSlug = esc($page['slug'] ?? '');
$pageStatus = $page['status'] ?? 'draft';
$contentJson = json_encode($content ?? ['sections' => []], JSON_UNESCAPED_UNICODE);
$modulesJson = json_encode($allModules, JSON_UNESCAPED_UNICODE);
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TB 4.0 - <?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tb4/design-system.css">
    <style>
    :root {
        --tb4-sidebar-width: 280px;
        --tb4-toolbar-height: 56px;
        --tb4-settings-width: 320px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { 
        height: 100%; 
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: var(--tb4-text-primary);
        background: var(--tb4-bg-secondary);
    }
    
    /* ═══════════════════════════════════════════════════════════
       TOOLBAR
       ═══════════════════════════════════════════════════════════ */
    .tb4-toolbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--tb4-toolbar-height);
        background: var(--tb4-sidebar-bg);
        border-bottom: 1px solid var(--tb4-sidebar-border);
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 16px;
        z-index: 1000;
    }
    .tb4-toolbar-left, .tb4-toolbar-center, .tb4-toolbar-right {
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
        gap: 8px;
        font-weight: 600;
        font-size: 15px;
        color: var(--tb4-sidebar-text-active);
    }
    .tb4-logo-icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, var(--tb4-primary-500), var(--tb4-primary-700));
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 12px;
    }
    
    .tb4-page-title {
        background: rgba(255,255,255,0.1);
        border: 1px solid transparent;
        color: var(--tb4-sidebar-text-active);
        font-size: 15px;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        text-align: center;
        min-width: 200px;
    }
    .tb4-page-title:hover { border-color: var(--tb4-sidebar-border); }
    .tb4-page-title:focus {
        outline: none;
        border-color: var(--tb4-primary-500);
        background: rgba(255,255,255,0.15);
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
        background: var(--tb4-sidebar-bg-hover);
        color: var(--tb4-sidebar-text);
    }
    .tb4-btn:hover { background: var(--tb4-sidebar-bg-active); color: var(--tb4-sidebar-text-active); }
    .tb4-btn-primary { background: var(--tb4-primary-600); color: white; }
    .tb4-btn-primary:hover { background: var(--tb4-primary-700); }
    .tb4-btn-icon {
        padding: 8px;
        background: transparent;
    }
    .tb4-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .tb4-divider {
        width: 1px;
        height: 24px;
        background: var(--tb4-sidebar-border);
        margin: 0 4px;
    }
    
    /* ═══════════════════════════════════════════════════════════
       MAIN LAYOUT
       ═══════════════════════════════════════════════════════════ */
    .tb4-main {
        position: fixed;
        top: var(--tb4-toolbar-height);
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
    }
    
    /* ═══════════════════════════════════════════════════════════
       LEFT PANEL - MODULES
       ═══════════════════════════════════════════════════════════ */
    .tb4-panel-left {
        width: var(--tb4-sidebar-width);
        background: var(--tb4-sidebar-bg);
        border-right: 1px solid var(--tb4-sidebar-border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .tb4-panel-header {
        padding: 16px;
        border-bottom: 1px solid var(--tb4-sidebar-border);
    }
    .tb4-panel-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--tb4-sidebar-text);
    }
    .tb4-search {
        width: 100%;
        padding: 8px 12px;
        background: var(--tb4-sidebar-bg-hover);
        border: 1px solid var(--tb4-sidebar-border);
        border-radius: 6px;
        color: var(--tb4-sidebar-text-active);
        font-size: 13px;
        margin-top: 12px;
    }
    .tb4-search:focus {
        outline: none;
        border-color: var(--tb4-primary-500);
    }
    .tb4-search::placeholder { color: var(--tb4-sidebar-text); }
    .tb4-panel-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px;
    }
    
    .tb4-module-category {
        margin-bottom: 20px;
    }
    .tb4-category-title {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--tb4-sidebar-text);
        margin-bottom: 10px;
        padding: 0 4px;
    }
    .tb4-modules-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .tb4-module-item {
        background: var(--tb4-sidebar-bg-hover);
        border: 1px solid var(--tb4-sidebar-border);
        border-radius: 8px;
        padding: 14px 8px;
        text-align: center;
        cursor: grab;
        transition: all 0.15s;
        overflow: hidden;
    }
    .tb4-module-item:hover {
        border-color: var(--tb4-primary-500);
        background: var(--tb4-sidebar-bg-active);
    }
    .tb4-module-item.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    .tb4-module-icon {
        font-size: 22px;
        margin-bottom: 6px;
        color: var(--tb4-sidebar-text-active);
    }
    .tb4-module-name {
        font-size: 10px;
        font-weight: 500;
        color: var(--tb4-sidebar-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    /* ═══════════════════════════════════════════════════════════
       CENTER - CANVAS
       ═══════════════════════════════════════════════════════════ */
    .tb4-canvas-wrapper {
        flex: 1;
        background: var(--tb4-bg-secondary);
        overflow: auto;
        display: flex;
        flex-direction: column;
    }
    .tb4-canvas-toolbar {
        padding: 12px 16px;
        background: var(--tb4-bg-primary);
        border-bottom: 1px solid var(--tb4-border-default);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tb4-viewport-btns {
        display: flex;
        gap: 4px;
    }
    .tb4-viewport-btn {
        padding: 6px 10px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 4px;
        color: var(--tb4-text-tertiary);
        cursor: pointer;
        font-size: 16px;
    }
    .tb4-viewport-btn:hover { background: var(--tb4-bg-tertiary); }
    .tb4-viewport-btn.active {
        background: var(--tb4-bg-tertiary);
        border-color: var(--tb4-border-default);
        color: var(--tb4-text-primary);
    }
    
    .tb4-canvas {
        flex: 1;
        padding: 24px;
        overflow: auto;
    }
    .tb4-canvas-inner {
        background: var(--tb4-bg-primary);
        min-height: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        margin: 0 auto;
        transition: width 0.3s;
        overflow: hidden;
    }
    .tb4-canvas-inner.desktop { width: 100%; max-width: 1200px; }
    .tb4-canvas-inner.tablet { width: 768px; }
    .tb4-canvas-inner.mobile { width: 375px; }
    
    /* Canvas Drop Zones */
    .tb4-drop-zone {
        min-height: 150px;
        border: 2px dashed var(--tb4-border-default);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--tb4-text-tertiary);
        margin: 24px;
        transition: all 0.15s;
        gap: 12px;
    }
    .tb4-drop-zone:hover, .tb4-drop-zone.drag-over {
        border-color: var(--tb4-primary-500);
        background: var(--tb4-primary-50);
    }
    .tb4-drop-zone-icon {
        font-size: 36px;
        opacity: 0.5;
    }
    .tb4-drop-zone-text {
        font-size: 14px;
        font-weight: 500;
    }
    
    /* ═══════════════════════════════════════════════════════════
       RIGHT PANEL - SETTINGS
       ═══════════════════════════════════════════════════════════ */
    .tb4-panel-right {
        width: var(--tb4-settings-width);
        background: var(--tb4-bg-primary);
        border-left: 1px solid var(--tb4-border-default);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .tb4-panel-right .tb4-panel-header {
        border-bottom: 1px solid var(--tb4-border-default);
        background: var(--tb4-bg-tertiary);
    }
    .tb4-panel-right .tb4-panel-title {
        color: var(--tb4-text-secondary);
    }
    .tb4-settings-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--tb4-text-tertiary);
        padding: 24px;
        text-align: center;
    }
    .tb4-settings-empty-icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.3;
    }
    .tb4-settings-empty-text {
        font-size: 14px;
    }
    
    .tb4-setting-group {
        margin-bottom: 20px;
    }
    .tb4-setting-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--tb4-text-secondary);
        margin-bottom: 6px;
        display: block;
    }
    .tb4-setting-input {
        width: 100%;
        padding: 8px 10px;
        background: var(--tb4-bg-secondary);
        border: 1px solid var(--tb4-border-default);
        border-radius: 6px;
        color: var(--tb4-text-primary);
        font-size: 13px;
    }
    .tb4-setting-input:focus {
        outline: none;
        border-color: var(--tb4-primary-500);
    }
    
    /* Status badge */
    .tb4-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 500;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .tb4-status-badge.draft {
        background: var(--tb4-warning-bg);
        color: var(--tb4-warning-text);
    }
    .tb4-status-badge.published {
        background: var(--tb4-success-bg);
        color: var(--tb4-success-text);
    }
    
    /* Module icon mapping */
    .icon-Section::before { content: "▭"; }
    .icon-Row::before { content: "☰"; }
    .icon-Column::before { content: "▥"; }
    .icon-Text::before { content: "T"; }
    .icon-Image::before { content: "🖼"; }
    .icon-Button::before { content: "⬚"; }
    .icon-Divider::before { content: "—"; }
    .icon-Blurb::before { content: "✦"; }
    .icon-Hero::before { content: "⬛"; }
    .icon-CTA::before { content: "📢"; }
    .icon-Quote::before { content: """; }
    .icon-Users::before { content: "👥"; }
    .icon-Code::before { content: "</>"; font-size: 16px; }
    .icon-Gallery::before { content: "🖼"; }
    .icon-Video::before { content: "▶"; }
    .icon-Audio::before { content: "🔊"; }
    </style>
</head>
<body>
    <!-- TOOLBAR -->
    <div class="tb4-toolbar">
        <div class="tb4-toolbar-left">
            <a href="/admin/tb4-builder" class="tb4-btn tb4-btn-icon" title="Back to Pages">←</a>
            <div class="tb4-divider"></div>
            <div class="tb4-logo">
                <div class="tb4-logo-icon">TB</div>
                <span>Theme Builder 4.0</span>
            </div>
        </div>
        <div class="tb4-toolbar-center">
            <input type="text" class="tb4-page-title" id="pageTitle" value="<?= $pageTitle ?>">
            <span class="tb4-status-badge <?= $pageStatus ?>"><?= $pageStatus ?></span>
        </div>
        <div class="tb4-toolbar-right">
            <button class="tb4-btn" id="btnUndo" title="Undo">↩</button>
            <button class="tb4-btn" id="btnRedo" title="Redo">↪</button>
            <div class="tb4-divider"></div>
            <button class="tb4-btn" id="btnPreview">Preview</button>
            <button class="tb4-btn tb4-btn-primary" id="btnSave">Save</button>
        </div>
    </div>
    
    <!-- MAIN LAYOUT -->
    <div class="tb4-main">
        <!-- LEFT PANEL - MODULES -->
        <div class="tb4-panel-left">
            <div class="tb4-panel-header">
                <div class="tb4-panel-title">Modules</div>
                <input type="text" class="tb4-search" id="moduleSearch" placeholder="Search modules...">
            </div>
            <div class="tb4-panel-body" id="modulesPanel">
                <!-- Structure Modules -->
                <div class="tb4-module-category" data-category="structure">
                    <div class="tb4-category-title">Structure</div>
                    <div class="tb4-modules-grid">
                        <div class="tb4-module-item" draggable="true" data-module="tb4_section">
                            <div class="tb4-module-icon icon-Section"></div>
                            <div class="tb4-module-name">Section</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_row">
                            <div class="tb4-module-icon icon-Row"></div>
                            <div class="tb4-module-name">Row</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_column">
                            <div class="tb4-module-icon icon-Column"></div>
                            <div class="tb4-module-name">Column</div>
                        </div>
                    </div>
                </div>
                <!-- Content Modules -->
                <div class="tb4-module-category" data-category="content">
                    <div class="tb4-category-title">Content</div>
                    <div class="tb4-modules-grid">
                        <div class="tb4-module-item" draggable="true" data-module="tb4_text">
                            <div class="tb4-module-icon icon-Text"></div>
                            <div class="tb4-module-name">Text</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_image">
                            <div class="tb4-module-icon icon-Image"></div>
                            <div class="tb4-module-name">Image</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_button">
                            <div class="tb4-module-icon icon-Button"></div>
                            <div class="tb4-module-name">Button</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_divider">
                            <div class="tb4-module-icon icon-Divider"></div>
                            <div class="tb4-module-name">Divider</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_blurb">
                            <div class="tb4-module-icon icon-Blurb"></div>
                            <div class="tb4-module-name">Blurb</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_hero">
                            <div class="tb4-module-icon icon-Hero"></div>
                            <div class="tb4-module-name">Hero</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_cta">
                            <div class="tb4-module-icon icon-CTA"></div>
                            <div class="tb4-module-name">CTA</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_testimonial">
                            <div class="tb4-module-icon icon-Quote"></div>
                            <div class="tb4-module-name">Testimonial</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_team">
                            <div class="tb4-module-icon icon-Users"></div>
                            <div class="tb4-module-name">Team Member</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_code">
                            <div class="tb4-module-icon icon-Code"></div>
                            <div class="tb4-module-name">Code</div>
                        </div>
                    </div>
                </div>
                <!-- Media Modules -->
                <div class="tb4-module-category" data-category="media">
                    <div class="tb4-category-title">Media</div>
                    <div class="tb4-modules-grid">
                        <div class="tb4-module-item" draggable="true" data-module="tb4_gallery">
                            <div class="tb4-module-icon icon-Gallery"></div>
                            <div class="tb4-module-name">Gallery</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_video">
                            <div class="tb4-module-icon icon-Video"></div>
                            <div class="tb4-module-name">Video</div>
                        </div>
                        <div class="tb4-module-item" draggable="true" data-module="tb4_audio">
                            <div class="tb4-module-icon icon-Audio"></div>
                            <div class="tb4-module-name">Audio</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CENTER - CANVAS -->
        <div class="tb4-canvas-wrapper">
            <div class="tb4-canvas-toolbar">
                <div class="tb4-viewport-btns">
                    <button class="tb4-viewport-btn active" data-viewport="desktop" title="Desktop">🖥</button>
                    <button class="tb4-viewport-btn" data-viewport="tablet" title="Tablet">📱</button>
                    <button class="tb4-viewport-btn" data-viewport="mobile" title="Mobile">📲</button>
                </div>
                <span style="color:var(--tb4-text-tertiary);font-size:12px;">
                    Page ID: <?= $pageId ?>
                </span>
            </div>
            <div class="tb4-canvas">
                <div class="tb4-canvas-inner desktop" id="canvas">
                    <div class="tb4-drop-zone" id="mainDropZone">
                        <div class="tb4-drop-zone-icon">+</div>
                        <div class="tb4-drop-zone-text">Drag a Section here to begin</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- RIGHT PANEL - SETTINGS -->
        <div class="tb4-panel-right">
            <div class="tb4-panel-header">
                <div class="tb4-panel-title">Settings</div>
            </div>
            <div class="tb4-settings-empty" id="settingsPanel">
                <div class="tb4-settings-empty-icon">⚙</div>
                <div class="tb4-settings-empty-text">Select an element<br>to edit its settings</div>
            </div>
        </div>
    </div>
    
    <!-- DATA -->
    <script>
        window.TB4 = {
            pageId: <?= $pageId ?>,
            csrfToken: <?= json_encode($csrfToken) ?>,
            content: <?= $contentJson ?>,
            modules: <?= $modulesJson ?>,
            apiBase: '/admin/theme-builder'
        };
    </script>
    
    <!-- INLINE JAVASCRIPT -->
    <script>
    (function() {
        'use strict';
        
        // State
        let selectedElement = null;
        let draggedModule = null;
        
        // Elements
        const canvas = document.getElementById('canvas');
        const mainDropZone = document.getElementById('mainDropZone');
        const settingsPanel = document.getElementById('settingsPanel');
        const moduleItems = document.querySelectorAll('.tb4-module-item');
        const viewportBtns = document.querySelectorAll('.tb4-viewport-btn');
        const canvasInner = document.querySelector('.tb4-canvas-inner');
        const moduleSearch = document.getElementById('moduleSearch');
        
        // Viewport switching
        viewportBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewportBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                canvasInner.className = 'tb4-canvas-inner ' + btn.dataset.viewport;
            });
        });
        
        // Module search filter
        moduleSearch.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            moduleItems.forEach(item => {
                const name = item.querySelector('.tb4-module-name').textContent.toLowerCase();
                item.style.display = name.includes(query) ? 'block' : 'none';
            });
        });
        
        // Drag and Drop
        moduleItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                draggedModule = item.dataset.module;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'copy';
            });
            
            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
                draggedModule = null;
            });
        });
        
        mainDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            mainDropZone.classList.add('drag-over');
        });
        
        mainDropZone.addEventListener('dragleave', () => {
            mainDropZone.classList.remove('drag-over');
        });
        
        mainDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            mainDropZone.classList.remove('drag-over');
            
            if (draggedModule) {
                addModuleToCanvas(draggedModule);
            }
        });
        
        function addModuleToCanvas(moduleSlug) {
            const moduleData = window.TB4.modules[moduleSlug];
            if (!moduleData) return;
            
            const wrapper = document.createElement('div');
            wrapper.className = 'tb4-canvas-module';
            wrapper.dataset.module = moduleSlug;
            wrapper.style.cssText = 'padding:20px;border:1px solid var(--tb4-border-default);margin:16px;border-radius:8px;cursor:pointer;transition:all 0.15s;';
            wrapper.innerHTML = '<div style="text-align:center;color:var(--tb4-text-secondary);"><strong>' + moduleData.name + '</strong><br><small>Click to edit</small></div>';
            
            wrapper.addEventListener('click', () => selectElement(wrapper, moduleSlug));
            
            // Insert before drop zone or replace it
            if (mainDropZone.parentNode === canvas) {
                canvas.insertBefore(wrapper, mainDropZone);
            }
            
            // Remove drop zone if we have content
            if (canvas.querySelectorAll('.tb4-canvas-module').length > 0) {
                mainDropZone.style.display = 'none';
            }
            
            selectElement(wrapper, moduleSlug);
        }
        
        function selectElement(element, moduleSlug) {
            // Deselect previous
            if (selectedElement) {
                selectedElement.style.borderColor = 'var(--tb4-border-default)';
            }
            
            selectedElement = element;
            element.style.borderColor = 'var(--tb4-primary-500)';
            
            // Show settings
            const moduleData = window.TB4.modules[moduleSlug];
            if (moduleData) {
                renderSettings(moduleData);
            }
        }
        
        function renderSettings(moduleData) {
            let html = '<div style="padding:16px;">';
            html += '<h3 style="margin:0 0 16px;font-size:16px;font-weight:600;">' + moduleData.name + '</h3>';
            
            // Content fields
            Object.keys(moduleData.fields).forEach(key => {
                const field = moduleData.fields[key];
                html += '<div class="tb4-setting-group">';
                html += '<label class="tb4-setting-label">' + field.label + '</label>';
                
                if (field.type === 'text' || field.type === 'upload') {
                    html += '<input type="text" class="tb4-setting-input" value="' + (field.default || '') + '">';
                } else if (field.type === 'textarea' || field.type === 'wysiwyg') {
                    html += '<textarea class="tb4-setting-input" style="min-height:80px;">' + (field.default || '') + '</textarea>';
                } else if (field.type === 'select') {
                    html += '<select class="tb4-setting-input">';
                    Object.keys(field.options || {}).forEach(optKey => {
                        html += '<option value="' + optKey + '">' + field.options[optKey] + '</option>';
                    });
                    html += '</select>';
                } else if (field.type === 'color') {
                    html += '<input type="color" class="tb4-setting-input" value="' + (field.default || '#000000') + '" style="height:40px;padding:4px;">';
                } else if (field.type === 'toggle') {
                    html += '<input type="checkbox" ' + (field.default ? 'checked' : '') + '>';
                } else {
                    html += '<input type="text" class="tb4-setting-input" value="' + (field.default || '') + '">';
                }
                
                html += '</div>';
            });
            
            // Advanced section
            html += '<details style="margin-top:20px;"><summary style="cursor:pointer;font-weight:500;margin-bottom:12px;">Advanced Settings</summary>';
            Object.keys(moduleData.advanced).forEach(key => {
                const field = moduleData.advanced[key];
                html += '<div class="tb4-setting-group">';
                html += '<label class="tb4-setting-label">' + field.label + '</label>';
                html += '<input type="text" class="tb4-setting-input" value="' + (field.default || '') + '">';
                html += '</div>';
            });
            html += '</details>';
            
            html += '</div>';
            
            settingsPanel.innerHTML = html;
        }
        
        // Save button
        document.getElementById('btnSave').addEventListener('click', async () => {
            const btn = document.getElementById('btnSave');
            btn.disabled = true;
            btn.textContent = 'Saving...';
            
            try {
                const response = await fetch(window.TB4.apiBase + '/api/save-page/' + window.TB4.pageId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.TB4.csrfToken
                    },
                    body: JSON.stringify({
                        title: document.getElementById('pageTitle').value,
                        content: window.TB4.content
                    })
                });
                
                if (response.ok) {
                    btn.textContent = 'Saved!';
                    setTimeout(() => { btn.textContent = 'Save'; }, 2000);
                } else {
                    throw new Error('Save failed');
                }
            } catch (err) {
                alert('Error saving: ' + err.message);
                btn.textContent = 'Save';
            }
            
            btn.disabled = false;
        });
        
        // Preview button
        document.getElementById('btnPreview').addEventListener('click', () => {
            window.open('/preview/' + window.TB4.pageId, '_blank');
        });
        
        console.log('TB 4.0 Builder initialized with', Object.keys(window.TB4.modules).length, 'modules');
    })();
    </script>
</body>
</html>
