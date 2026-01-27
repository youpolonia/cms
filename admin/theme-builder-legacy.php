<?php
/**
 * Visual Theme Builder v3.0 - Divi-like Page Builder
 * Professional drag & drop editor with advanced styling
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_layout') {
        $name = preg_replace('/[^a-z0-9_-]/i', '', $_POST['name'] ?? 'untitled');
        $data = $_POST['data'] ?? '[]';
        
        $dir = CMS_ROOT . '/themes/layouts';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        if (file_put_contents($dir . '/' . $name . '.json', $data)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Save failed']);
        }
        exit;
    }
    
    if ($action === 'load_layouts') {
        $layouts = [];
        $dir = CMS_ROOT . '/themes/layouts';
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.json') as $file) {
                $layouts[] = [
                    'name' => basename($file, '.json'),
                    'modified' => filemtime($file)
                ];
            }
        }
        echo json_encode(['success' => true, 'layouts' => $layouts]);
        exit;
    }
    
    if ($action === 'load_layout') {
        $name = preg_replace('/[^a-z0-9_-]/i', '', $_POST['name'] ?? '');
        $file = CMS_ROOT . '/themes/layouts/' . $name . '.json';
        if (file_exists($file)) {
            echo json_encode(['success' => true, 'data' => file_get_contents($file)]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not found']);
        }
        exit;
    }
    
    echo json_encode(['success' => false]);
    exit;
}

$csrf = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Builder - CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg-dark: #0f0f12;
        --bg-panel: #1a1a21;
        --bg-card: #23232d;
        --bg-hover: #2d2d3a;
        --bg-active: #3d3d4d;
        --text-primary: #ffffff;
        --text-secondary: #a0a0b0;
        --text-muted: #606070;
        --accent: #7c3aed;
        --accent-hover: #8b5cf6;
        --accent-glow: rgba(124, 58, 237, 0.3);
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --border: #2d2d3a;
        --border-light: #3d3d4d;
    }
    
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-dark);
        color: var(--text-primary);
        font-size: 13px;
        overflow: hidden;
    }
    
    /* Main Layout */
    .builder {
        display: grid;
        grid-template-columns: 72px 280px 1fr 320px;
        grid-template-rows: 56px 1fr;
        height: 100vh;
    }
    
    /* Toolbar */
    .toolbar {
        grid-column: 1 / -1;
        background: var(--bg-panel);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        z-index: 100;
    }
    
    .toolbar-left, .toolbar-center, .toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .toolbar-back {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 18px;
        margin-right: 12px;
        transition: all 0.15s;
    }
    
    .toolbar-back:hover {
        background: var(--bg-hover);
        border-color: var(--accent);
        color: var(--text-primary);
    }
    
    .toolbar-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 16px;
        color: var(--text-primary);
        text-decoration: none;
        margin-right: 24px;
    }
    
    .toolbar-logo-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--accent), #ec4899);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .tb-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: transparent;
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .tb-btn:hover {
        background: var(--bg-hover);
        border-color: var(--border-light);
        color: var(--text-primary);
    }
    
    .tb-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    
    .tb-btn.primary:hover {
        background: var(--accent-hover);
    }
    
    .device-toggle {
        display: flex;
        background: var(--bg-card);
        border-radius: 6px;
        padding: 4px;
    }
    
    .device-btn {
        padding: 6px 12px;
        background: transparent;
        border: none;
        border-radius: 4px;
        color: var(--text-muted);
        font-size: 16px;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .device-btn:hover { color: var(--text-secondary); }
    .device-btn.active { background: var(--accent); color: white; }
    
    .zoom-control {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        background: var(--bg-card);
        border-radius: 6px;
    }
    
    .zoom-control button {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 4px;
    }
    
    .zoom-control span {
        font-size: 12px;
        min-width: 40px;
        text-align: center;
    }
    
    /* Sidebar Icons */
    .sidebar-icons {
        background: var(--bg-panel);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 0;
        gap: 4px;
    }
    
    .sidebar-icon {
        width: 48px;
        height: 48px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        background: transparent;
        border: none;
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 18px;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .sidebar-icon span {
        font-size: 9px;
        font-weight: 500;
    }
    
    .sidebar-icon:hover {
        background: var(--bg-hover);
        color: var(--text-secondary);
    }
    
    .sidebar-icon.active {
        background: var(--accent);
        color: white;
    }
    
    .sidebar-divider {
        width: 32px;
        height: 1px;
        background: var(--border);
        margin: 8px 0;
    }
    
    /* Left Panel */
    .panel-left {
        background: var(--bg-panel);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .panel-header {
        padding: 16px;
        border-bottom: 1px solid var(--border);
    }
    
    .panel-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
    }
    
    .panel-search {
        position: relative;
    }
    
    .panel-search input {
        width: 100%;
        padding: 10px 12px 10px 36px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 13px;
    }
    
    .panel-search input:focus {
        outline: none;
        border-color: var(--accent);
    }
    
    .panel-search::before {
        content: '🔍';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        opacity: 0.5;
    }
    
    .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }
    
    .panel-body::-webkit-scrollbar { width: 6px; }
    .panel-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
    
    /* Block Categories */
    .block-category {
        margin-bottom: 20px;
    }
    
    .category-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .category-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }
    
    .blocks-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .block-item {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px 8px;
        text-align: center;
        cursor: grab;
        transition: all 0.15s;
    }
    
    .block-item:hover {
        border-color: var(--accent);
        background: var(--bg-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    .block-item:active { cursor: grabbing; }
    
    .block-item .icon {
        font-size: 24px;
        margin-bottom: 6px;
        display: block;
    }
    
    .block-item .label {
        font-size: 11px;
        font-weight: 500;
        color: var(--text-secondary);
    }
    
    /* Canvas */
    .canvas-area {
        background: var(--bg-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px;
        overflow: auto;
        position: relative;
    }
    
    .canvas-area::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: 
            radial-gradient(circle at 1px 1px, var(--border) 1px, transparent 0);
        background-size: 24px 24px;
        opacity: 0.3;
    }
    
    .canvas {
        position: relative;
        background: white;
        border-radius: 8px;
        box-shadow: 0 0 0 1px var(--border), 0 20px 50px rgba(0,0,0,0.4);
        overflow: hidden;
        transition: width 0.3s ease;
        z-index: 1;
    }
    
    .canvas.desktop { width: 1200px; }
    .canvas.tablet { width: 768px; }
    .canvas.mobile { width: 375px; }
    
    .canvas-inner {
        min-height: 600px;
        position: relative;
    }
    
    .empty-canvas {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        color: #666;
        text-align: center;
        padding: 40px;
    }
    
    .empty-canvas .icon {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    
    .empty-canvas h3 {
        font-size: 18px;
        margin-bottom: 8px;
        color: #333;
    }
    
    .empty-canvas p {
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .empty-canvas .templates-hint {
        display: flex;
        gap: 12px;
    }
    
    .template-quick {
        padding: 12px 20px;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.15s;
    }
    
    .template-quick:hover {
        background: var(--accent-hover);
    }
    
    /* Placed Blocks */
    .placed-block {
        position: relative;
        transition: all 0.15s;
    }
    
    .placed-block::before {
        content: '';
        position: absolute;
        inset: 0;
        border: 2px solid transparent;
        pointer-events: none;
        transition: all 0.15s;
        z-index: 10;
    }
    
    .placed-block:hover::before {
        border-color: var(--accent);
    }
    
    .placed-block.selected::before {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px var(--accent-glow);
    }
    
    .block-toolbar {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translate(-50%, -100%);
        display: none;
        gap: 2px;
        padding: 4px;
        background: var(--bg-panel);
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 100;
    }
    
    .placed-block:hover .block-toolbar,
    .placed-block.selected .block-toolbar {
        display: flex;
    }
    
    .block-tool {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        border-radius: 6px;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .block-tool:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
    
    .block-tool.danger:hover {
        background: var(--danger);
        color: white;
    }
    
    .block-label {
        position: absolute;
        top: 0;
        left: 0;
        padding: 4px 8px;
        background: var(--accent);
        color: white;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        display: none;
        z-index: 10;
    }
    
    .placed-block:hover .block-label,
    .placed-block.selected .block-label {
        display: block;
    }
    
    /* Right Panel - Settings */
    .panel-right {
        background: var(--bg-panel);
        border-left: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .settings-tabs {
        display: flex;
        border-bottom: 1px solid var(--border);
    }
    
    .settings-tab {
        flex: 1;
        padding: 14px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .settings-tab:hover {
        color: var(--text-secondary);
    }
    
    .settings-tab.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
    }
    
    .settings-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }
    
    .settings-section {
        margin-bottom: 24px;
    }
    
    .section-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
    }
    
    .setting-row {
        margin-bottom: 14px;
    }
    
    .setting-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }
    
    .setting-input {
        width: 100%;
        padding: 10px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-primary);
        font-size: 13px;
        transition: all 0.15s;
    }
    
    .setting-input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-glow);
    }
    
    .setting-input::placeholder {
        color: var(--text-muted);
    }
    
    select.setting-input {
        cursor: pointer;
    }
    
    textarea.setting-input {
        min-height: 80px;
        resize: vertical;
    }
    
    /* Color Picker */
    .color-picker {
        display: flex;
        gap: 8px;
    }
    
    .color-preview {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 2px solid var(--border);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .color-preview input {
        position: absolute;
        inset: -10px;
        width: 60px;
        height: 60px;
        cursor: pointer;
        opacity: 0;
    }
    
    .color-picker .setting-input {
        flex: 1;
        font-family: monospace;
    }
    
    /* Gradient Picker */
    .gradient-picker {
        background: var(--bg-card);
        border-radius: 8px;
        padding: 12px;
    }
    
    .gradient-preview {
        height: 40px;
        border-radius: 6px;
        margin-bottom: 12px;
    }
    
    .gradient-stops {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .gradient-type {
        display: flex;
        gap: 8px;
    }
    
    .gradient-type button {
        flex: 1;
        padding: 8px;
        background: var(--bg-hover);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 11px;
    }
    
    .gradient-type button.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    
    /* Spacing Control */
    .spacing-control {
        background: var(--bg-card);
        border-radius: 8px;
        padding: 16px;
    }
    
    .spacing-visual {
        position: relative;
        width: 100%;
        aspect-ratio: 16/10;
        margin-bottom: 12px;
    }
    
    .spacing-outer {
        position: absolute;
        inset: 0;
        background: rgba(249, 226, 175, 0.2);
        border: 1px dashed var(--warning);
        border-radius: 4px;
    }
    
    .spacing-inner {
        position: absolute;
        inset: 25%;
        background: rgba(166, 227, 161, 0.2);
        border: 1px dashed var(--success);
        border-radius: 4px;
    }
    
    .spacing-center {
        position: absolute;
        inset: 35%;
        background: var(--bg-hover);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: var(--text-muted);
    }
    
    .spacing-inputs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    
    .spacing-input {
        text-align: center;
    }
    
    .spacing-input label {
        display: block;
        font-size: 10px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    
    .spacing-input input {
        width: 100%;
        padding: 8px;
        background: var(--bg-hover);
        border: 1px solid var(--border);
        border-radius: 4px;
        color: var(--text-primary);
        text-align: center;
        font-size: 12px;
    }
    
    /* Typography Controls */
    .font-family-select {
        display: grid;
        gap: 8px;
    }
    
    .font-option {
        padding: 10px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .font-option:hover {
        border-color: var(--accent);
    }
    
    .font-option.active {
        border-color: var(--accent);
        background: var(--accent-glow);
    }
    
    .font-option .name {
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .font-option .preview {
        font-size: 16px;
    }
    
    /* Slider */
    .slider-control {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .slider-control input[type="range"] {
        flex: 1;
        -webkit-appearance: none;
        height: 6px;
        background: var(--bg-card);
        border-radius: 3px;
    }
    
    .slider-control input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        background: var(--accent);
        border-radius: 50%;
        cursor: pointer;
    }
    
    .slider-control .value {
        min-width: 50px;
        padding: 6px 10px;
        background: var(--bg-card);
        border-radius: 4px;
        text-align: center;
        font-size: 12px;
        font-family: monospace;
    }
    
    /* Toggle */
    .toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
    }
    
    .toggle-switch {
        width: 44px;
        height: 24px;
        background: var(--bg-card);
        border-radius: 12px;
        position: relative;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 18px;
        height: 18px;
        background: var(--text-muted);
        border-radius: 50%;
        transition: all 0.2s;
    }
    
    .toggle-switch.active {
        background: var(--accent);
    }
    
    .toggle-switch.active::after {
        left: 23px;
        background: white;
    }
    
    /* Responsive Tabs */
    .responsive-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 12px;
    }
    
    .responsive-tab {
        flex: 1;
        padding: 8px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-muted);
        font-size: 14px;
        cursor: pointer;
        text-align: center;
        transition: all 0.15s;
    }
    
    .responsive-tab:hover {
        border-color: var(--border-light);
    }
    
    .responsive-tab.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    
    /* Layers Panel */
    .layers-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .layer-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .layer-item:hover {
        border-color: var(--accent);
    }
    
    .layer-item.active {
        border-color: var(--accent);
        background: var(--accent-glow);
    }
    
    .layer-icon {
        font-size: 16px;
    }
    
    .layer-name {
        flex: 1;
        font-size: 12px;
        font-weight: 500;
    }
    
    .layer-actions {
        display: flex;
        gap: 4px;
    }
    
    .layer-action {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        border-radius: 4px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 12px;
    }
    
    .layer-action:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
    
    /* Quick Actions Footer */
    .panel-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 8px;
    }
    
    .footer-btn {
        flex: 1;
        padding: 10px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .footer-btn:hover {
        border-color: var(--accent);
        color: var(--text-primary);
    }
    
    .footer-btn.primary {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }
    
    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-overlay.active { display: flex; }
    
    .modal {
        background: var(--bg-panel);
        border-radius: 16px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .modal-title {
        font-size: 18px;
        font-weight: 600;
    }
    
    .modal-close {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        border-radius: 6px;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 20px;
    }
    
    .modal-close:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
    
    .modal-body {
        flex: 1;
        padding: 24px;
        overflow-y: auto;
    }
    
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    
    /* Templates Grid */
    .templates-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .template-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .template-card:hover {
        border-color: var(--accent);
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    
    .template-preview {
        height: 120px;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .template-info {
        padding: 12px;
    }
    
    .template-name {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .template-desc {
        font-size: 11px;
        color: var(--text-muted);
    }
    
    /* Toast */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1000;
    }
    
    .toast {
        background: var(--bg-panel);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px 20px;
        margin-top: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }
    
    .toast.success { border-left: 3px solid var(--success); }
    .toast.error { border-left: 3px solid var(--danger); }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* Block Styles */
    .b-hero {
        padding: 80px 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
    }
    .b-hero h1 { font-size: 48px; font-weight: 700; margin-bottom: 16px; }
    .b-hero p { font-size: 20px; opacity: 0.9; margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto; }
    .b-hero .btn { display: inline-block; padding: 16px 40px; background: white; color: #667eea; border-radius: 8px; font-weight: 600; font-size: 16px; text-decoration: none; }
    
    .b-features { padding: 80px 60px; background: #f8fafc; }
    .b-features h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
    .b-features .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
    .b-features .item { text-align: center; padding: 32px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .b-features .item .icon { font-size: 48px; margin-bottom: 16px; }
    .b-features .item h3 { font-size: 20px; margin-bottom: 8px; color: #1e293b; }
    .b-features .item p { color: #64748b; font-size: 15px; }
    
    .b-text { padding: 60px; }
    .b-text h2 { font-size: 32px; margin-bottom: 16px; color: #1e293b; }
    .b-text p { font-size: 16px; line-height: 1.8; color: #475569; }
    
    .b-image { padding: 40px; }
    .b-image img { width: 100%; border-radius: 12px; }
    
    .b-cta { padding: 80px 60px; background: #1e293b; color: white; text-align: center; }
    .b-cta h2 { font-size: 36px; margin-bottom: 16px; }
    .b-cta p { font-size: 18px; opacity: 0.8; margin-bottom: 32px; }
    .b-cta .btn { display: inline-block; padding: 16px 40px; background: var(--accent); color: white; border-radius: 8px; font-weight: 600; text-decoration: none; }
    
    .b-pricing { padding: 80px 60px; }
    .b-pricing h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
    .b-pricing .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .b-pricing .card { border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; text-align: center; }
    .b-pricing .card.featured { border-color: var(--accent); box-shadow: 0 8px 30px rgba(124,58,237,0.2); }
    .b-pricing .card h3 { font-size: 20px; margin-bottom: 8px; }
    .b-pricing .card .price { font-size: 48px; font-weight: 700; color: #1e293b; margin: 16px 0; }
    .b-pricing .card .price span { font-size: 16px; font-weight: 400; color: #64748b; }
    .b-pricing .card ul { list-style: none; margin: 24px 0; text-align: left; }
    .b-pricing .card li { padding: 8px 0; border-bottom: 1px solid #f1f5f9; color: #475569; }
    .b-pricing .card .btn { display: block; padding: 14px; background: var(--accent); color: white; border-radius: 8px; font-weight: 600; text-decoration: none; }
    
    .b-testimonials { padding: 80px 60px; background: #f8fafc; }
    .b-testimonials h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
    .b-testimonials .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
    .b-testimonials .card { background: white; padding: 32px; border-radius: 16px; }
    .b-testimonials .card p { font-size: 16px; font-style: italic; color: #475569; margin-bottom: 24px; line-height: 1.7; }
    .b-testimonials .card .author { display: flex; align-items: center; gap: 12px; }
    .b-testimonials .card .avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--accent); }
    .b-testimonials .card .name { font-weight: 600; color: #1e293b; }
    .b-testimonials .card .role { font-size: 13px; color: #64748b; }
    
    .b-header { padding: 20px 60px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
    .b-header .logo { font-size: 24px; font-weight: 700; color: #1e293b; }
    .b-header nav { display: flex; gap: 32px; }
    .b-header nav a { color: #475569; text-decoration: none; font-weight: 500; }
    .b-header .btn { padding: 10px 24px; background: var(--accent); color: white; border-radius: 6px; text-decoration: none; font-weight: 500; }
    
    .b-footer { padding: 60px; background: #1e293b; color: white; }
    .b-footer .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; }
    .b-footer h4 { font-size: 16px; margin-bottom: 20px; }
    .b-footer ul { list-style: none; }
    .b-footer li { margin-bottom: 12px; }
    .b-footer a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 14px; }
    .b-footer a:hover { color: white; }
    
    .b-gallery { padding: 60px; }
    .b-gallery .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .b-gallery img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; }
    
    .b-contact { padding: 80px 60px; }
    .b-contact h2 { font-size: 36px; margin-bottom: 32px; color: #1e293b; }
    .b-contact .form { max-width: 500px; }
    .b-contact input, .b-contact textarea { width: 100%; padding: 14px 16px; margin-bottom: 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; }
    .b-contact button { padding: 14px 32px; background: var(--accent); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
    
    .b-spacer { height: 60px; }
    .b-divider { padding: 20px 60px; }
    .b-divider hr { border: none; border-top: 1px solid #e2e8f0; }
    
    /* Editable */
    [contenteditable="true"]:hover { outline: 2px dashed var(--accent); outline-offset: 2px; }
    [contenteditable="true"]:focus { outline: 2px solid var(--accent); outline-offset: 2px; }
    </style>
</head>
<body>
    <div class="builder">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <a href="/admin" class="toolbar-back" title="Back to Dashboard">←</a>
                <a href="/admin" class="toolbar-logo">
                    <div class="toolbar-logo-icon">🎨</div>
                    Theme Builder
                </a>
                <button class="tb-btn" onclick="undo()">↶</button>
                <button class="tb-btn" onclick="redo()">↷</button>
            </div>
            <div class="toolbar-center">
                <div class="device-toggle">
                    <button class="device-btn active" data-device="desktop" title="Desktop">🖥️</button>
                    <button class="device-btn" data-device="tablet" title="Tablet">📱</button>
                    <button class="device-btn" data-device="mobile" title="Mobile">📲</button>
                </div>
                <div class="zoom-control">
                    <button onclick="zoomOut()">−</button>
                    <span id="zoom-level">100%</span>
                    <button onclick="zoomIn()">+</button>
                </div>
            </div>
            <div class="toolbar-right">
                <button class="tb-btn" onclick="preview()">👁️ Preview</button>
                <button class="tb-btn" onclick="showSaveModal()">💾 Save</button>
                <button class="tb-btn" onclick="exportHTML()">📤 Export</button>
                <button class="tb-btn primary" onclick="publish()">🚀 Publish</button>
            </div>
        </div>
        
        <!-- Sidebar Icons -->
        <div class="sidebar-icons">
            <button class="sidebar-icon active" data-panel="blocks" title="Blocks">
                <span>📦</span>
                <span>Blocks</span>
            </button>
            <button class="sidebar-icon" data-panel="sections" title="Sections">
                <span>📑</span>
                <span>Sections</span>
            </button>
            <button class="sidebar-icon" data-panel="templates" title="Templates">
                <span>📄</span>
                <span>Templates</span>
            </button>
            <div class="sidebar-divider"></div>
            <button class="sidebar-icon" data-panel="layers" title="Layers">
                <span>📚</span>
                <span>Layers</span>
            </button>
            <button class="sidebar-icon" data-panel="global" title="Global">
                <span>🎨</span>
                <span>Global</span>
            </button>
        </div>
        
        <!-- Left Panel -->
        <div class="panel-left">
            <div class="panel-header">
                <div class="panel-title" id="panel-title">Blocks</div>
                <div class="panel-search">
                    <input type="text" placeholder="Search..." id="block-search">
                </div>
            </div>
            <div class="panel-body" id="panel-content">
                <!-- Blocks content loaded dynamically -->
            </div>
        </div>
        
        <!-- Canvas -->
        <div class="canvas-area">
            <div class="canvas desktop" id="canvas">
                <div class="canvas-inner" id="canvas-inner">
                    <div class="empty-canvas" id="empty-canvas">
                        <div class="icon">🎨</div>
                        <h3>Start Building</h3>
                        <p>Drag blocks from the left panel or choose a template</p>
                        <div class="templates-hint">
                            <button class="template-quick" onclick="loadTemplate('saas')">SaaS Landing</button>
                            <button class="template-quick" onclick="loadTemplate('agency')">Agency</button>
                            <button class="template-quick" onclick="loadTemplate('portfolio')">Portfolio</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel -->
        <div class="panel-right">
            <div class="settings-tabs">
                <button class="settings-tab active" data-tab="content">Content</button>
                <button class="settings-tab" data-tab="design">Design</button>
                <button class="settings-tab" data-tab="advanced">Advanced</button>
            </div>
            <div class="settings-body" id="settings-body">
                <div class="empty-settings" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                    <div style="font-size: 32px; margin-bottom: 12px;">👆</div>
                    <p>Select a block to edit its settings</p>
                </div>
            </div>
            <div class="panel-footer">
                <button class="footer-btn" onclick="duplicateSelected()">⧉ Duplicate</button>
                <button class="footer-btn" onclick="deleteSelected()">🗑️ Delete</button>
            </div>
        </div>
    </div>
    
    <!-- Save Modal -->
    <div class="modal-overlay" id="save-modal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Save Layout</div>
                <button class="modal-close" onclick="closeSaveModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="setting-row">
                    <div class="setting-label">Layout Name</div>
                    <input type="text" class="setting-input" id="save-name" placeholder="my-landing-page">
                </div>
            </div>
            <div class="modal-footer">
                <button class="tb-btn" onclick="closeSaveModal()">Cancel</button>
                <button class="tb-btn primary" onclick="saveLayout()">Save Layout</button>
            </div>
        </div>
    </div>
    
    <!-- Export Modal -->
    <div class="modal-overlay" id="export-modal">
        <div class="modal" style="max-width: 800px;">
            <div class="modal-header">
                <div class="modal-title">Export HTML</div>
                <button class="modal-close" onclick="closeExportModal()">×</button>
            </div>
            <div class="modal-body">
                <textarea id="export-code" style="width:100%; height:300px; background:var(--bg-card); border:1px solid var(--border); border-radius:8px; padding:16px; color:var(--text-primary); font-family:monospace; font-size:12px; resize:none;"></textarea>
            </div>
            <div class="modal-footer">
                <button class="tb-btn" onclick="copyCode()">📋 Copy</button>
                <button class="tb-btn primary" onclick="downloadCode()">⬇️ Download</button>
            </div>
        </div>
    </div>
    
    <!-- Templates Modal -->
    <div class="modal-overlay" id="templates-modal">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <div class="modal-title">Choose Template</div>
                <button class="modal-close" onclick="closeTemplatesModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="templates-grid" id="templates-grid"></div>
            </div>
        </div>
    </div>
    
    <div class="toast-container" id="toast-container"></div>
    
    <script>
    const csrf = '<?= $csrf ?>';
    let selectedBlock = null;
    let history = [];
    let historyIndex = -1;
    let zoom = 100;
    
    // Block definitions
    const blocks = {
        // Layout
        section: { icon: '☐', label: 'Section', category: 'layout' },
        columns2: { icon: '▥', label: '2 Columns', category: 'layout' },
        columns3: { icon: '▦', label: '3 Columns', category: 'layout' },
        spacer: { icon: '↕', label: 'Spacer', category: 'layout' },
        divider: { icon: '─', label: 'Divider', category: 'layout' },
        // Basic
        heading: { icon: 'H', label: 'Heading', category: 'basic' },
        text: { icon: '¶', label: 'Text', category: 'basic' },
        image: { icon: '🖼️', label: 'Image', category: 'basic' },
        button: { icon: '▢', label: 'Button', category: 'basic' },
        video: { icon: '▶️', label: 'Video', category: 'basic' },
        icon: { icon: '★', label: 'Icon', category: 'basic' },
        // Content
        hero: { icon: '🦸', label: 'Hero', category: 'content' },
        features: { icon: '✨', label: 'Features', category: 'content' },
        cta: { icon: '📢', label: 'CTA', category: 'content' },
        pricing: { icon: '💰', label: 'Pricing', category: 'content' },
        testimonials: { icon: '💬', label: 'Testimonials', category: 'content' },
        gallery: { icon: '🖼️', label: 'Gallery', category: 'content' },
        faq: { icon: '❓', label: 'FAQ', category: 'content' },
        team: { icon: '👥', label: 'Team', category: 'content' },
        // Forms
        contact: { icon: '✉️', label: 'Contact', category: 'forms' },
        newsletter: { icon: '📧', label: 'Newsletter', category: 'forms' },
        // Structure
        header: { icon: '▔', label: 'Header', category: 'structure' },
        footer: { icon: '▁', label: 'Footer', category: 'structure' },
    };
    
    // Block HTML templates
    const blockHTML = {
        hero: `<div class="b-hero"><h1 contenteditable="true">Build Amazing Websites</h1><p contenteditable="true">Create stunning pages with our visual builder. No coding required.</p><a href="#" class="btn" contenteditable="true">Get Started</a></div>`,
        features: `<div class="b-features"><h2 contenteditable="true">Why Choose Us</h2><div class="grid"><div class="item"><div class="icon">⚡</div><h3 contenteditable="true">Lightning Fast</h3><p contenteditable="true">Optimized for speed and performance.</p></div><div class="item"><div class="icon">🔒</div><h3 contenteditable="true">Secure</h3><p contenteditable="true">Enterprise-grade security built-in.</p></div><div class="item"><div class="icon">🎨</div><h3 contenteditable="true">Beautiful</h3><p contenteditable="true">Stunning designs out of the box.</p></div></div></div>`,
        text: `<div class="b-text"><h2 contenteditable="true">Your Heading</h2><p contenteditable="true">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p></div>`,
        image: `<div class="b-image"><img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800" alt=""></div>`,
        cta: `<div class="b-cta"><h2 contenteditable="true">Ready to Get Started?</h2><p contenteditable="true">Join thousands of satisfied customers today.</p><a href="#" class="btn" contenteditable="true">Start Free Trial</a></div>`,
        pricing: `<div class="b-pricing"><h2 contenteditable="true">Simple Pricing</h2><div class="grid"><div class="card"><h3 contenteditable="true">Starter</h3><div class="price">$9<span>/mo</span></div><ul><li contenteditable="true">5 Projects</li><li contenteditable="true">10GB Storage</li><li contenteditable="true">Email Support</li></ul><a href="#" class="btn">Choose</a></div><div class="card featured"><h3 contenteditable="true">Pro</h3><div class="price">$29<span>/mo</span></div><ul><li contenteditable="true">Unlimited Projects</li><li contenteditable="true">100GB Storage</li><li contenteditable="true">Priority Support</li></ul><a href="#" class="btn">Choose</a></div><div class="card"><h3 contenteditable="true">Enterprise</h3><div class="price">$99<span>/mo</span></div><ul><li contenteditable="true">Everything</li><li contenteditable="true">1TB Storage</li><li contenteditable="true">24/7 Support</li></ul><a href="#" class="btn">Choose</a></div></div></div>`,
        testimonials: `<div class="b-testimonials"><h2 contenteditable="true">What People Say</h2><div class="grid"><div class="card"><p contenteditable="true">"This is the best tool I've ever used. It completely transformed our workflow."</p><div class="author"><div class="avatar"></div><div><div class="name" contenteditable="true">John Doe</div><div class="role" contenteditable="true">CEO, Company</div></div></div></div><div class="card"><p contenteditable="true">"Incredible value. We saved hundreds of hours on our projects."</p><div class="author"><div class="avatar"></div><div><div class="name" contenteditable="true">Jane Smith</div><div class="role" contenteditable="true">Designer</div></div></div></div></div></div>`,
        header: `<div class="b-header"><div class="logo" contenteditable="true">Logo</div><nav><a href="#" contenteditable="true">Home</a><a href="#" contenteditable="true">Features</a><a href="#" contenteditable="true">Pricing</a><a href="#" contenteditable="true">Contact</a></nav><a href="#" class="btn" contenteditable="true">Sign Up</a></div>`,
        footer: `<div class="b-footer"><div class="grid"><div><h4 contenteditable="true">Company</h4><ul><li><a href="#" contenteditable="true">About</a></li><li><a href="#" contenteditable="true">Careers</a></li><li><a href="#" contenteditable="true">Contact</a></li></ul></div><div><h4 contenteditable="true">Product</h4><ul><li><a href="#" contenteditable="true">Features</a></li><li><a href="#" contenteditable="true">Pricing</a></li><li><a href="#" contenteditable="true">API</a></li></ul></div><div><h4 contenteditable="true">Resources</h4><ul><li><a href="#" contenteditable="true">Docs</a></li><li><a href="#" contenteditable="true">Blog</a></li><li><a href="#" contenteditable="true">Support</a></li></ul></div><div><h4 contenteditable="true">Legal</h4><ul><li><a href="#" contenteditable="true">Privacy</a></li><li><a href="#" contenteditable="true">Terms</a></li></ul></div></div></div>`,
        gallery: `<div class="b-gallery"><div class="grid"><img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=400" alt=""><img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400" alt=""><img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=400" alt=""><img src="https://images.unsplash.com/photo-1504639725590-34d0984388bd?w=400" alt=""></div></div>`,
        contact: `<div class="b-contact"><h2 contenteditable="true">Get in Touch</h2><div class="form"><input type="text" placeholder="Your Name"><input type="email" placeholder="Your Email"><textarea rows="4" placeholder="Your Message"></textarea><button>Send Message</button></div></div>`,
        spacer: `<div class="b-spacer"></div>`,
        divider: `<div class="b-divider"><hr></div>`,
        heading: `<div class="b-text"><h2 contenteditable="true" style="margin:0;">Your Heading Here</h2></div>`,
        button: `<div style="padding:20px;"><a href="#" class="btn" style="display:inline-block;padding:14px 32px;background:var(--accent);color:white;border-radius:8px;text-decoration:none;font-weight:600;" contenteditable="true">Click Here</a></div>`,
    };
    
    // Templates
    const templates = {
        saas: ['header', 'hero', 'features', 'pricing', 'testimonials', 'cta', 'footer'],
        agency: ['header', 'hero', 'features', 'gallery', 'testimonials', 'contact', 'footer'],
        portfolio: ['header', 'hero', 'gallery', 'contact', 'footer'],
    };
    
    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        loadPanelContent('blocks');
        initDragDrop();
        saveHistory();
    });
    
    // Panel switching
    document.querySelectorAll('.sidebar-icon').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sidebar-icon').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadPanelContent(btn.dataset.panel);
        });
    });
    
    function loadPanelContent(panel) {
        const title = document.getElementById('panel-title');
        const content = document.getElementById('panel-content');
        
        title.textContent = panel.charAt(0).toUpperCase() + panel.slice(1);
        
        if (panel === 'blocks') {
            content.innerHTML = renderBlocksPanel();
            initBlockDrag();
        } else if (panel === 'sections') {
            content.innerHTML = renderSectionsPanel();
        } else if (panel === 'templates') {
            content.innerHTML = renderTemplatesPanel();
        } else if (panel === 'layers') {
            content.innerHTML = renderLayersPanel();
        } else if (panel === 'global') {
            content.innerHTML = renderGlobalPanel();
        }
    }
    
    function renderBlocksPanel() {
        const categories = {
            layout: 'Layout',
            basic: 'Basic',
            content: 'Content',
            forms: 'Forms',
            structure: 'Structure'
        };
        
        let html = '';
        for (const [cat, label] of Object.entries(categories)) {
            const catBlocks = Object.entries(blocks).filter(([k, v]) => v.category === cat);
            if (catBlocks.length === 0) continue;
            
            html += `<div class="block-category"><div class="category-title">${label}</div><div class="blocks-grid">`;
            for (const [key, block] of catBlocks) {
                html += `<div class="block-item" draggable="true" data-block="${key}"><div class="icon">${block.icon}</div><div class="label">${block.label}</div></div>`;
            }
            html += `</div></div>`;
        }
        return html;
    }
    
    function renderSectionsPanel() {
        const sections = [
            { id: 'hero-gradient', name: 'Hero - Gradient', preview: 'linear-gradient(135deg, #667eea, #764ba2)' },
            { id: 'hero-image', name: 'Hero - Image BG', preview: 'linear-gradient(135deg, #1e293b, #334155)' },
            { id: 'features-icons', name: 'Features - Icons', preview: '#f8fafc' },
            { id: 'pricing-cards', name: 'Pricing - Cards', preview: '#ffffff' },
            { id: 'testimonials-grid', name: 'Testimonials', preview: '#f8fafc' },
            { id: 'cta-dark', name: 'CTA - Dark', preview: '#1e293b' },
        ];
        
        let html = '';
        for (const s of sections) {
            html += `<div class="template-card" onclick="addSection('${s.id}')"><div class="template-preview" style="background:${s.preview};height:80px;"></div><div class="template-info"><div class="template-name">${s.name}</div></div></div>`;
        }
        return `<div class="templates-grid" style="gap:12px;">${html}</div>`;
    }
    
    function renderTemplatesPanel() {
        const tpls = [
            { id: 'saas', name: 'SaaS Landing', desc: 'Modern SaaS landing page', preview: 'linear-gradient(135deg, #667eea, #764ba2)' },
            { id: 'agency', name: 'Agency', desc: 'Creative agency website', preview: 'linear-gradient(135deg, #1e293b, #475569)' },
            { id: 'portfolio', name: 'Portfolio', desc: 'Personal portfolio', preview: 'linear-gradient(135deg, #000, #333)' },
        ];
        
        let html = '';
        for (const t of tpls) {
            html += `<div class="template-card" onclick="loadTemplate('${t.id}')"><div class="template-preview" style="background:${t.preview};"></div><div class="template-info"><div class="template-name">${t.name}</div><div class="template-desc">${t.desc}</div></div></div>`;
        }
        return `<div class="templates-grid">${html}</div>`;
    }
    
    function renderLayersPanel() {
        const blocks = document.querySelectorAll('.placed-block');
        if (blocks.length === 0) {
            return '<div style="text-align:center;padding:40px;color:var(--text-muted);">No blocks yet</div>';
        }
        
        let html = '<div class="layers-list">';
        blocks.forEach((block, i) => {
            const type = block.dataset.type || 'Block';
            const isSelected = block === selectedBlock ? 'active' : '';
            html += `<div class="layer-item ${isSelected}" onclick="selectBlockByIndex(${i})"><span class="layer-icon">${blocks[type]?.icon || '📦'}</span><span class="layer-name">${type}</span><div class="layer-actions"><button class="layer-action" onclick="moveBlock(${i},-1)">↑</button><button class="layer-action" onclick="moveBlock(${i},1)">↓</button></div></div>`;
        });
        html += '</div>';
        return html;
    }
    
    function renderGlobalPanel() {
        return `
            <div class="settings-section">
                <div class="section-title">Colors</div>
                <div class="setting-row">
                    <div class="setting-label">Primary Color</div>
                    <div class="color-picker">
                        <div class="color-preview" style="background:#7c3aed;"><input type="color" value="#7c3aed" onchange="updateGlobal('primary',this.value)"></div>
                        <input type="text" class="setting-input" value="#7c3aed">
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-label">Secondary Color</div>
                    <div class="color-picker">
                        <div class="color-preview" style="background:#1e293b;"><input type="color" value="#1e293b" onchange="updateGlobal('secondary',this.value)"></div>
                        <input type="text" class="setting-input" value="#1e293b">
                    </div>
                </div>
            </div>
            <div class="settings-section">
                <div class="section-title">Typography</div>
                <div class="setting-row">
                    <div class="setting-label">Heading Font</div>
                    <select class="setting-input">
                        <option>Inter</option>
                        <option>Poppins</option>
                        <option>Roboto</option>
                        <option>Playfair Display</option>
                    </select>
                </div>
                <div class="setting-row">
                    <div class="setting-label">Body Font</div>
                    <select class="setting-input">
                        <option>Inter</option>
                        <option>Open Sans</option>
                        <option>Roboto</option>
                    </select>
                </div>
            </div>
        `;
    }
    
    // Drag & Drop
    function initBlockDrag() {
        document.querySelectorAll('.block-item').forEach(item => {
            item.addEventListener('dragstart', e => {
                e.dataTransfer.setData('block-type', item.dataset.block);
            });
        });
    }
    
    function initDragDrop() {
        const canvas = document.getElementById('canvas-inner');
        
        canvas.addEventListener('dragover', e => {
            e.preventDefault();
            canvas.style.background = 'rgba(124,58,237,0.05)';
        });
        
        canvas.addEventListener('dragleave', () => {
            canvas.style.background = '';
        });
        
        canvas.addEventListener('drop', e => {
            e.preventDefault();
            canvas.style.background = '';
            
            const type = e.dataTransfer.getData('block-type');
            if (type) addBlock(type);
        });
    }
    
    function addBlock(type) {
        const html = blockHTML[type];
        if (!html) return;
        
        document.getElementById('empty-canvas')?.remove();
        
        const block = document.createElement('div');
        block.className = 'placed-block';
        block.dataset.type = type;
        block.innerHTML = `
            <div class="block-label">${type}</div>
            <div class="block-toolbar">
                <button class="block-tool" onclick="moveBlockUp(this)" title="Move Up">↑</button>
                <button class="block-tool" onclick="moveBlockDown(this)" title="Move Down">↓</button>
                <button class="block-tool" onclick="duplicateBlock(this)" title="Duplicate">⧉</button>
                <button class="block-tool" onclick="openBlockSettings(this)" title="Settings">⚙️</button>
                <button class="block-tool danger" onclick="deleteBlock(this)" title="Delete">×</button>
            </div>
            ${html}
        `;
        
        block.addEventListener('click', e => {
            if (!e.target.closest('.block-toolbar')) {
                selectBlock(block);
            }
        });
        
        document.getElementById('canvas-inner').appendChild(block);
        selectBlock(block);
        saveHistory();
    }
    
    function selectBlock(block) {
        document.querySelectorAll('.placed-block').forEach(b => b.classList.remove('selected'));
        block.classList.add('selected');
        selectedBlock = block;
        loadBlockSettings(block);
    }
    
    function loadBlockSettings(block) {
        const type = block.dataset.type;
        const body = document.getElementById('settings-body');
        
        body.innerHTML = `
            <div class="settings-section">
                <div class="section-title">Background</div>
                <div class="setting-row">
                    <div class="setting-label">Type</div>
                    <select class="setting-input" onchange="updateBlockBgType(this.value)">
                        <option value="color">Solid Color</option>
                        <option value="gradient">Gradient</option>
                        <option value="image">Image</option>
                    </select>
                </div>
                <div class="setting-row">
                    <div class="setting-label">Color</div>
                    <div class="color-picker">
                        <div class="color-preview" id="bg-preview" style="background:#ffffff;"><input type="color" value="#ffffff" onchange="updateBlockStyle('background',this.value);document.getElementById('bg-preview').style.background=this.value;"></div>
                        <input type="text" class="setting-input" value="#ffffff" onchange="updateBlockStyle('background',this.value)">
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="section-title">Spacing</div>
                <div class="responsive-tabs">
                    <button class="responsive-tab active">🖥️</button>
                    <button class="responsive-tab">📱</button>
                    <button class="responsive-tab">📲</button>
                </div>
                <div class="spacing-control">
                    <div class="spacing-visual">
                        <div class="spacing-outer"></div>
                        <div class="spacing-inner"></div>
                        <div class="spacing-center">Content</div>
                    </div>
                    <div class="spacing-inputs">
                        <div class="spacing-input"><input type="number" value="0" onchange="updateBlockStyle('paddingTop',this.value+'px')"><label>Top</label></div>
                        <div class="spacing-input"><input type="number" value="0" onchange="updateBlockStyle('paddingRight',this.value+'px')"><label>Right</label></div>
                        <div class="spacing-input"><input type="number" value="0" onchange="updateBlockStyle('paddingBottom',this.value+'px')"><label>Bottom</label></div>
                        <div class="spacing-input"><input type="number" value="0" onchange="updateBlockStyle('paddingLeft',this.value+'px')"><label>Left</label></div>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="section-title">Border</div>
                <div class="setting-row">
                    <div class="setting-label">Radius</div>
                    <div class="slider-control">
                        <input type="range" min="0" max="50" value="0" oninput="updateBlockStyle('borderRadius',this.value+'px');this.nextElementSibling.textContent=this.value+'px'">
                        <span class="value">0px</span>
                    </div>
                </div>
                <div class="setting-row">
                    <div class="setting-label">Width</div>
                    <div class="slider-control">
                        <input type="range" min="0" max="10" value="0" oninput="updateBlockStyle('borderWidth',this.value+'px');updateBlockStyle('borderStyle','solid');this.nextElementSibling.textContent=this.value+'px'">
                        <span class="value">0px</span>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="section-title">Effects</div>
                <div class="setting-row">
                    <div class="setting-label">Box Shadow</div>
                    <select class="setting-input" onchange="updateBlockStyle('boxShadow',this.value)">
                        <option value="none">None</option>
                        <option value="0 4px 6px rgba(0,0,0,0.1)">Small</option>
                        <option value="0 10px 25px rgba(0,0,0,0.15)">Medium</option>
                        <option value="0 20px 50px rgba(0,0,0,0.2)">Large</option>
                    </select>
                </div>
                <div class="toggle">
                    <span>Animation on Scroll</span>
                    <div class="toggle-switch" onclick="this.classList.toggle('active')"></div>
                </div>
            </div>
        `;
    }
    
    function updateBlockStyle(prop, value) {
        if (!selectedBlock) return;
        const content = selectedBlock.querySelector('[class^="b-"]') || selectedBlock.children[2];
        if (content) content.style[prop] = value;
    }
    
    function moveBlockUp(btn) {
        const block = btn.closest('.placed-block');
        const prev = block.previousElementSibling;
        if (prev && prev.classList.contains('placed-block')) {
            block.parentNode.insertBefore(block, prev);
            saveHistory();
        }
    }
    
    function moveBlockDown(btn) {
        const block = btn.closest('.placed-block');
        const next = block.nextElementSibling;
        if (next) {
            block.parentNode.insertBefore(next, block);
            saveHistory();
        }
    }
    
    function duplicateBlock(btn) {
        const block = btn.closest('.placed-block');
        const clone = block.cloneNode(true);
        clone.classList.remove('selected');
        clone.addEventListener('click', e => {
            if (!e.target.closest('.block-toolbar')) selectBlock(clone);
        });
        block.parentNode.insertBefore(clone, block.nextSibling);
        saveHistory();
        toast('Block duplicated');
    }
    
    function deleteBlock(btn) {
        const block = btn.closest('.placed-block');
        block.remove();
        selectedBlock = null;
        
        if (!document.querySelector('.placed-block')) {
            document.getElementById('canvas-inner').innerHTML = `<div class="empty-canvas" id="empty-canvas"><div class="icon">🎨</div><h3>Start Building</h3><p>Drag blocks from the left panel or choose a template</p></div>`;
        }
        
        document.getElementById('settings-body').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);"><div style="font-size:32px;margin-bottom:12px;">👆</div><p>Select a block to edit</p></div>';
        saveHistory();
        toast('Block deleted');
    }
    
    function duplicateSelected() {
        if (selectedBlock) {
            const btn = selectedBlock.querySelector('.block-tool');
            if (btn) duplicateBlock(btn);
        }
    }
    
    function deleteSelected() {
        if (selectedBlock && confirm('Delete this block?')) {
            selectedBlock.remove();
            selectedBlock = null;
            saveHistory();
        }
    }
    
    // Templates
    function loadTemplate(id) {
        const tpl = templates[id];
        if (!tpl) return;
        
        const canvas = document.getElementById('canvas-inner');
        canvas.innerHTML = '';
        
        tpl.forEach(type => {
            const html = blockHTML[type];
            if (!html) return;
            
            const block = document.createElement('div');
            block.className = 'placed-block';
            block.dataset.type = type;
            block.innerHTML = `<div class="block-label">${type}</div><div class="block-toolbar"><button class="block-tool" onclick="moveBlockUp(this)">↑</button><button class="block-tool" onclick="moveBlockDown(this)">↓</button><button class="block-tool" onclick="duplicateBlock(this)">⧉</button><button class="block-tool" onclick="openBlockSettings(this)">⚙️</button><button class="block-tool danger" onclick="deleteBlock(this)">×</button></div>${html}`;
            block.addEventListener('click', e => {
                if (!e.target.closest('.block-toolbar')) selectBlock(block);
            });
            canvas.appendChild(block);
        });
        
        saveHistory();
        toast('Template loaded');
    }
    
    // Device switching
    document.querySelectorAll('.device-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('canvas').className = 'canvas ' + btn.dataset.device;
        });
    });
    
    // Zoom
    function zoomIn() {
        if (zoom < 150) {
            zoom += 10;
            document.getElementById('canvas').style.transform = `scale(${zoom/100})`;
            document.getElementById('zoom-level').textContent = zoom + '%';
        }
    }
    
    function zoomOut() {
        if (zoom > 50) {
            zoom -= 10;
            document.getElementById('canvas').style.transform = `scale(${zoom/100})`;
            document.getElementById('zoom-level').textContent = zoom + '%';
        }
    }
    
    // History
    function saveHistory() {
        const state = document.getElementById('canvas-inner').innerHTML;
        history = history.slice(0, historyIndex + 1);
        history.push(state);
        historyIndex = history.length - 1;
    }
    
    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            document.getElementById('canvas-inner').innerHTML = history[historyIndex];
            reattachEvents();
            toast('Undo');
        }
    }
    
    function redo() {
        if (historyIndex < history.length - 1) {
            historyIndex++;
            document.getElementById('canvas-inner').innerHTML = history[historyIndex];
            reattachEvents();
            toast('Redo');
        }
    }
    
    function reattachEvents() {
        document.querySelectorAll('.placed-block').forEach(block => {
            block.addEventListener('click', e => {
                if (!e.target.closest('.block-toolbar')) selectBlock(block);
            });
        });
    }
    
    // Settings tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });
    
    // Save/Export
    function showSaveModal() { document.getElementById('save-modal').classList.add('active'); }
    function closeSaveModal() { document.getElementById('save-modal').classList.remove('active'); }
    
    function saveLayout() {
        const name = document.getElementById('save-name').value || 'untitled';
        const data = document.getElementById('canvas-inner').innerHTML;
        
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `ajax=1&action=save_layout&name=${encodeURIComponent(name)}&data=${encodeURIComponent(data)}&csrf_token=${csrf}`
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                toast('Layout saved!', 'success');
                closeSaveModal();
            } else {
                toast(res.error || 'Save failed', 'error');
            }
        });
    }
    
    // Block CSS for export
    const blockCSS = `
:root { --accent: #7c3aed; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1e293b; }
img { max-width: 100%; height: auto; }
a { text-decoration: none; }

.b-hero { padding: 80px 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; }
.b-hero h1 { font-size: 48px; font-weight: 700; margin-bottom: 16px; }
.b-hero p { font-size: 20px; opacity: 0.9; margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto; }
.b-hero .btn { display: inline-block; padding: 16px 40px; background: white; color: #667eea; border-radius: 8px; font-weight: 600; font-size: 16px; transition: transform 0.2s; }
.b-hero .btn:hover { transform: translateY(-2px); }

.b-features { padding: 80px 60px; background: #f8fafc; }
.b-features h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
.b-features .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; max-width: 1200px; margin: 0 auto; }
.b-features .item { text-align: center; padding: 32px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
.b-features .item:hover { transform: translateY(-4px); }
.b-features .item .icon { font-size: 48px; margin-bottom: 16px; }
.b-features .item h3 { font-size: 20px; margin-bottom: 8px; color: #1e293b; }
.b-features .item p { color: #64748b; font-size: 15px; }

.b-text { padding: 60px; max-width: 800px; margin: 0 auto; }
.b-text h2 { font-size: 32px; margin-bottom: 16px; color: #1e293b; }
.b-text p { font-size: 16px; line-height: 1.8; color: #475569; }

.b-image { padding: 40px; }
.b-image img { width: 100%; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }

.b-cta { padding: 80px 60px; background: #1e293b; color: white; text-align: center; }
.b-cta h2 { font-size: 36px; margin-bottom: 16px; }
.b-cta p { font-size: 18px; opacity: 0.8; margin-bottom: 32px; }
.b-cta .btn { display: inline-block; padding: 16px 40px; background: var(--accent); color: white; border-radius: 8px; font-weight: 600; transition: transform 0.2s; }
.b-cta .btn:hover { transform: translateY(-2px); }

.b-pricing { padding: 80px 60px; }
.b-pricing h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
.b-pricing .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1000px; margin: 0 auto; }
.b-pricing .card { border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; text-align: center; background: white; transition: transform 0.2s, box-shadow 0.2s; }
.b-pricing .card:hover { transform: translateY(-4px); box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.b-pricing .card.featured { border-color: var(--accent); box-shadow: 0 8px 30px rgba(124,58,237,0.2); }
.b-pricing .card h3 { font-size: 20px; margin-bottom: 8px; }
.b-pricing .card .price { font-size: 48px; font-weight: 700; color: #1e293b; margin: 16px 0; }
.b-pricing .card .price span { font-size: 16px; font-weight: 400; color: #64748b; }
.b-pricing .card ul { list-style: none; margin: 24px 0; text-align: left; }
.b-pricing .card li { padding: 8px 0; border-bottom: 1px solid #f1f5f9; color: #475569; }
.b-pricing .card .btn { display: block; padding: 14px; background: var(--accent); color: white; border-radius: 8px; font-weight: 600; transition: background 0.2s; }
.b-pricing .card .btn:hover { background: #6d28d9; }

.b-testimonials { padding: 80px 60px; background: #f8fafc; }
.b-testimonials h2 { text-align: center; font-size: 36px; margin-bottom: 48px; color: #1e293b; }
.b-testimonials .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; max-width: 900px; margin: 0 auto; }
.b-testimonials .card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.b-testimonials .card p { font-size: 16px; font-style: italic; color: #475569; margin-bottom: 24px; line-height: 1.7; }
.b-testimonials .card .author { display: flex; align-items: center; gap: 12px; }
.b-testimonials .card .avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #ec4899); }
.b-testimonials .card .name { font-weight: 600; color: #1e293b; }
.b-testimonials .card .role { font-size: 13px; color: #64748b; }

.b-header { padding: 20px 60px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; background: white; }
.b-header .logo { font-size: 24px; font-weight: 700; color: #1e293b; }
.b-header nav { display: flex; gap: 32px; }
.b-header nav a { color: #475569; font-weight: 500; transition: color 0.2s; }
.b-header nav a:hover { color: var(--accent); }
.b-header .btn { padding: 10px 24px; background: var(--accent); color: white; border-radius: 6px; font-weight: 500; transition: background 0.2s; }
.b-header .btn:hover { background: #6d28d9; }

.b-footer { padding: 60px; background: #1e293b; color: white; }
.b-footer .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px; max-width: 1200px; margin: 0 auto; }
.b-footer h4 { font-size: 16px; margin-bottom: 20px; }
.b-footer ul { list-style: none; }
.b-footer li { margin-bottom: 12px; }
.b-footer a { color: rgba(255,255,255,0.7); font-size: 14px; transition: color 0.2s; }
.b-footer a:hover { color: white; }

.b-gallery { padding: 60px; }
.b-gallery .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; max-width: 1200px; margin: 0 auto; }
.b-gallery img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; transition: transform 0.2s; }
.b-gallery img:hover { transform: scale(1.05); }

.b-contact { padding: 80px 60px; }
.b-contact h2 { font-size: 36px; margin-bottom: 32px; color: #1e293b; }
.b-contact .form { max-width: 500px; }
.b-contact input, .b-contact textarea { width: 100%; padding: 14px 16px; margin-bottom: 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; font-family: inherit; transition: border-color 0.2s; }
.b-contact input:focus, .b-contact textarea:focus { outline: none; border-color: var(--accent); }
.b-contact button { padding: 14px 32px; background: var(--accent); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.b-contact button:hover { background: #6d28d9; }

.b-spacer { height: 60px; }
.b-divider { padding: 20px 60px; }
.b-divider hr { border: none; border-top: 1px solid #e2e8f0; }

@media (max-width: 768px) {
    .b-hero { padding: 60px 24px; }
    .b-hero h1 { font-size: 32px; }
    .b-hero p { font-size: 16px; }
    .b-features { padding: 60px 24px; }
    .b-features .grid { grid-template-columns: 1fr; }
    .b-pricing .grid { grid-template-columns: 1fr; }
    .b-testimonials .grid { grid-template-columns: 1fr; }
    .b-header { padding: 16px 24px; }
    .b-header nav { display: none; }
    .b-footer .grid { grid-template-columns: repeat(2, 1fr); }
    .b-gallery .grid { grid-template-columns: repeat(2, 1fr); }
}
`;

    function exportHTML() {
        let html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
${blockCSS}
    </style>
</head>
<body>
`;
        
        document.querySelectorAll('.placed-block').forEach(block => {
            const clone = block.cloneNode(true);
            clone.querySelector('.block-toolbar')?.remove();
            clone.querySelector('.block-label')?.remove();
            clone.querySelectorAll('[contenteditable]').forEach(el => el.removeAttribute('contenteditable'));
            html += clone.innerHTML + '\n';
        });
        
        html += `</body>
</html>`;
        
        document.getElementById('export-code').value = html;
        document.getElementById('export-modal').classList.add('active');
    }
    
    function closeExportModal() { document.getElementById('export-modal').classList.remove('active'); }
    
    function copyCode() {
        document.getElementById('export-code').select();
        document.execCommand('copy');
        toast('Copied!');
    }
    
    function downloadCode() {
        const html = document.getElementById('export-code').value;
        const blob = new Blob([html], {type: 'text/html'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'page.html';
        a.click();
    }
    
    function preview() {
        let html = generatePreviewHTML();
        const win = window.open('', '_blank');
        win.document.write(html);
        win.document.close();
    }
    
    function generatePreviewHTML() {
        let html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>${blockCSS}</style>
</head>
<body>`;
        document.querySelectorAll('.placed-block').forEach(block => {
            const clone = block.cloneNode(true);
            clone.querySelector('.block-toolbar')?.remove();
            clone.querySelector('.block-label')?.remove();
            clone.querySelectorAll('[contenteditable]').forEach(el => el.removeAttribute('contenteditable'));
            html += clone.innerHTML;
        });
        html += `</body></html>`;
        return html;
    }
    
    function publish() {
        toast('Publish feature coming soon!');
    }
    
    // Toast
    function toast(msg, type = 'success') {
        const container = document.getElementById('toast-container');
        const t = document.createElement('div');
        t.className = 'toast ' + type;
        t.innerHTML = `<span>${type === 'success' ? '✓' : '✗'}</span><span>${msg}</span>`;
        container.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
    </script>
</body>
</html>
