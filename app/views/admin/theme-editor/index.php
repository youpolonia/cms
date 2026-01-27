<?php
/**
 * Theme Editor 2.0 - Visual Theme Customizer
 * Professional WordPress/Shopify-like theme editor
 * DO NOT add closing ?> tag
 */

$title = 'Theme Editor';
$hideAdminLayout = true;

function te_esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$colors = $themeData['colors'] ?? [];
$typography = $themeData['typography'] ?? [];
$header = $themeData['header'] ?? [];
$buttons = $themeData['buttons'] ?? [];
$layout = $themeData['layout'] ?? [];
$effects = $themeData['effects'] ?? [];
$customCSS = $themeData['customCSS'] ?? '';
$info = $themeData['info'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Theme: <?= te_esc($info['name'] ?? $themeName) ?> - CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
    :root {
        --bg: #0f0f12;
        --bg-panel: #1a1a21;
        --bg-card: #23232d;
        --bg-hover: #2d2d3a;
        --text: #ffffff;
        --text-secondary: #a0a0b0;
        --text-muted: #606070;
        --primary: #8b5cf6;
        --primary-hover: #9d6fff;
        --accent: #ec4899;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --border: #2d2d3a;
        --radius: 8px;
        --radius-lg: 12px;
        --transition: 150ms ease;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); font-size: 13px; overflow: hidden; }
    .editor-container { display: grid; grid-template-columns: 360px 1fr; grid-template-rows: 56px 1fr; height: 100vh; }
    .toolbar { grid-column: 1 / -1; background: var(--bg-panel); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 16px; }
    .toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 8px; }
    .toolbar-back { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); color: var(--text-secondary); text-decoration: none; font-weight: 500; transition: all var(--transition); }
    .toolbar-back:hover { background: var(--bg-hover); color: var(--text); border-color: var(--primary); }
    .toolbar-title { font-weight: 600; font-size: 14px; margin-left: 8px; }
    .toolbar-title span { color: var(--text-muted); font-weight: 400; }
    .device-toggle { display: flex; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 4px; }
    .device-btn { padding: 6px 12px; background: transparent; border: none; border-radius: 4px; color: var(--text-muted); font-size: 16px; cursor: pointer; transition: all var(--transition); }
    .device-btn:hover { color: var(--text-secondary); }
    .device-btn.active { background: var(--primary); color: white; }
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); color: var(--text-secondary); font-size: 13px; font-weight: 500; cursor: pointer; transition: all var(--transition); text-decoration: none; }
    .btn:hover { background: var(--bg-hover); color: var(--text); }
    .btn.primary { background: var(--primary); border-color: var(--primary); color: white; }
    .btn.primary:hover { background: var(--primary-hover); }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .sidebar { background: var(--bg-panel); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
    .sidebar-tabs { display: flex; border-bottom: 1px solid var(--border); background: var(--bg-card); }
    .sidebar-tab { flex: 1; padding: 12px 8px; background: transparent; border: none; border-bottom: 2px solid transparent; color: var(--text-muted); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer; }
    .sidebar-tab:hover { color: var(--text-secondary); }
    .sidebar-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
    .sidebar-content { flex: 1; overflow-y: auto; }
    .sidebar-content::-webkit-scrollbar { width: 6px; }
    .sidebar-content::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .panel { border-bottom: 1px solid var(--border); }
    .panel-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: transparent; border: none; width: 100%; color: var(--text); font-size: 13px; font-weight: 600; cursor: pointer; transition: background var(--transition); }
    .panel-header:hover { background: var(--bg-hover); }
    .panel-header .arrow { font-size: 10px; transition: transform var(--transition); }
    .panel.open .panel-header .arrow { transform: rotate(180deg); }
    .panel-body { display: none; padding: 0 16px 16px; }
    .panel.open .panel-body { display: block; }
    .control-group { margin-bottom: 16px; }
    .control-label { display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 8px; }
    .control-label .hint { font-weight: 400; color: var(--text-muted); font-size: 11px; }
    .control-input { width: 100%; padding: 10px 12px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); color: var(--text); font-size: 13px; font-family: inherit; transition: all var(--transition); }
    .control-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2); }
    textarea.control-input { min-height: 100px; resize: vertical; font-family: 'JetBrains Mono', monospace; font-size: 12px; }
    select.control-input { cursor: pointer; }
    .color-control { display: flex; gap: 8px; align-items: center; }
    .color-preview { width: 40px; height: 40px; border-radius: var(--radius); border: 2px solid var(--border); cursor: pointer; position: relative; overflow: hidden; flex-shrink: 0; }
    .color-preview input[type="color"] { position: absolute; inset: -10px; width: 60px; height: 60px; cursor: pointer; border: none; }
    .color-control .control-input { flex: 1; font-family: 'JetBrains Mono', monospace; text-transform: uppercase; }
    .slider-control { display: flex; align-items: center; gap: 12px; }
    .slider-control input[type="range"] { flex: 1; -webkit-appearance: none; height: 6px; background: var(--bg-card); border-radius: 3px; cursor: pointer; }
    .slider-control input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; background: var(--primary); border-radius: 50%; cursor: pointer; }
    .slider-value { min-width: 50px; padding: 6px 10px; background: var(--bg-card); border-radius: 4px; text-align: center; font-size: 12px; font-family: 'JetBrains Mono', monospace; }
    .toggle-control { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; }
    .toggle-switch { width: 44px; height: 24px; background: var(--bg-card); border-radius: 12px; position: relative; cursor: pointer; transition: background var(--transition); }
    .toggle-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; background: var(--text-muted); border-radius: 50%; transition: all var(--transition); }
    .toggle-switch.active { background: var(--primary); }
    .toggle-switch.active::after { left: 23px; background: white; }
    .presets-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 16px; }
    .preset-item { padding: 10px; background: var(--bg-card); border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all var(--transition); }
    .preset-item:hover { border-color: var(--primary); }
    .preset-item.active { border-color: var(--primary); background: rgba(139, 92, 246, 0.1); }
    .preset-colors { display: flex; gap: 4px; margin-bottom: 6px; }
    .preset-color { width: 20px; height: 20px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); }
    .preset-name { font-size: 11px; font-weight: 500; color: var(--text-secondary); }
    .preview-area { background: var(--bg); display: flex; flex-direction: column; overflow: hidden; }
    .preview-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; overflow: auto; background-image: radial-gradient(circle at 1px 1px, var(--border) 1px, transparent 0); background-size: 20px 20px; }
    .preview-frame-container { background: white; border-radius: var(--radius-lg); box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 0 1px var(--border); overflow: hidden; transition: width 0.3s ease; }
    .preview-frame-container.desktop { width: 100%; max-width: 1200px; }
    .preview-frame-container.tablet { width: 768px; }
    .preview-frame-container.mobile { width: 375px; }
    .preview-frame { width: 100%; height: 70vh; border: none; display: block; }
    .status-bar { display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; background: var(--bg-panel); border-top: 1px solid var(--border); font-size: 12px; color: var(--text-muted); }
    .status-indicator { display: flex; align-items: center; gap: 6px; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); }
    .status-dot.saving { background: var(--primary); animation: pulse 1s infinite; }
    .status-dot.warning { background: var(--warning); }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 8px; }
    .toast { padding: 12px 16px; background: var(--bg-panel); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 4px 20px rgba(0,0,0,0.3); display: flex; align-items: center; gap: 10px; animation: slideIn 0.2s ease; }
    .toast.success { border-color: var(--success); }
    .toast.error { border-color: var(--danger); }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .divider { height: 1px; background: var(--border); margin: 16px 0; }
    .section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
    </style>
</head>
<body>
<div class="editor-container">
    <div class="toolbar">
        <div class="toolbar-left">
            <a href="/admin/themes" class="toolbar-back"><span>←</span> Back</a>
            <div class="toolbar-title"><?= te_esc($info['name'] ?? $themeName) ?> <span>/ Theme Editor</span></div>
        </div>
        <div class="toolbar-center">
            <div class="device-toggle">
                <button class="device-btn active" data-device="desktop" title="Desktop">🖥️</button>
                <button class="device-btn" data-device="tablet" title="Tablet">📱</button>
                <button class="device-btn" data-device="mobile" title="Mobile">📱</button>
            </div>
        </div>
        <div class="toolbar-right">
            <button class="btn" id="btn-undo" disabled>↩️ Undo</button>
            <button class="btn" id="btn-redo" disabled>↪️ Redo</button>
            <a href="/?preview_theme=<?= urlencode($themeName) ?>" target="_blank" class="btn">👁️ Preview</a>
            <button class="btn primary" id="btn-save">💾 Save</button>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-tabs">
            <button class="sidebar-tab active" data-tab="design">Design</button>
            <button class="sidebar-tab" data-tab="advanced">Advanced</button>
            <button class="sidebar-tab" data-tab="info">Info</button>
        </div>
        <div class="sidebar-content">
            <div class="tab-panel active" data-panel="design">
                <!-- Colors Panel -->
                <div class="panel open">
                    <button class="panel-header"><span>🎨 Colors</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="section-title">Presets</div>
                        <div class="presets-grid">
                            <?php foreach ($presets as $i => $preset): ?>
                            <div class="preset-item" data-preset="<?= $i ?>">
                                <div class="preset-colors">
                                    <?php foreach (array_slice($preset['colors'], 0, 4) as $c): ?>
                                    <div class="preset-color" style="background: <?= te_esc($c) ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="preset-name"><?= te_esc($preset['name']) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="divider"></div>
                        <div class="section-title">Custom Colors</div>
                        <?php 
                        $colorFields = [
                            'primary' => 'Primary',
                            'secondary' => 'Secondary',
                            'accent' => 'Accent',
                            'background' => 'Background',
                            'surface' => 'Surface',
                            'text' => 'Text',
                            'textMuted' => 'Text Muted',
                            'border' => 'Border'
                        ];
                        $defaultColors = [
                            'primary' => '#8b5cf6',
                            'secondary' => '#6366f1',
                            'accent' => '#ec4899',
                            'background' => '#0f0f12',
                            'surface' => '#1a1a21',
                            'text' => '#ffffff',
                            'textMuted' => '#a0a0b0',
                            'border' => '#2d2d3a'
                        ];
                        foreach ($colorFields as $key => $label): 
                            $val = $colors[$key] ?? $defaultColors[$key];
                        ?>
                        <div class="control-group">
                            <label class="control-label"><?= $label ?></label>
                            <div class="color-control">
                                <div class="color-preview" style="background: <?= te_esc($val) ?>">
                                    <input type="color" data-color="<?= $key ?>" value="<?= te_esc($val) ?>">
                                </div>
                                <input type="text" class="control-input" data-color-text="<?= $key ?>" value="<?= te_esc($val) ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Typography Panel -->
                <div class="panel">
                    <button class="panel-header"><span>🔤 Typography</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <label class="control-label">Body Font</label>
                            <select class="control-input" data-setting="typography.fontFamily">
                                <?php foreach ($fonts as $font): ?>
                                <option value="<?= te_esc($font['name']) ?>" <?= ($typography['fontFamily'] ?? 'Inter') === $font['name'] ? 'selected' : '' ?>><?= te_esc($font['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Base Font Size <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="12" max="20" value="<?= te_esc($typography['baseFontSize'] ?? '16') ?>" data-setting="typography.baseFontSize">
                                <span class="slider-value"><?= te_esc($typography['baseFontSize'] ?? '16') ?>px</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Line Height</label>
                            <div class="slider-control">
                                <input type="range" min="1.2" max="2" step="0.1" value="<?= te_esc($typography['lineHeight'] ?? '1.6') ?>" data-setting="typography.lineHeight">
                                <span class="slider-value"><?= te_esc($typography['lineHeight'] ?? '1.6') ?></span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">H1 Size <span class="hint">rem</span></label>
                            <div class="slider-control">
                                <input type="range" min="2" max="5" step="0.25" value="<?= te_esc($typography['h1Size'] ?? '3') ?>" data-setting="typography.h1Size">
                                <span class="slider-value"><?= te_esc($typography['h1Size'] ?? '3') ?>rem</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Buttons Panel -->
                <div class="panel">
                    <button class="panel-header"><span>🔘 Buttons</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <label class="control-label">Border Radius <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="0" max="24" value="<?= te_esc($buttons['borderRadius'] ?? '8') ?>" data-setting="buttons.borderRadius">
                                <span class="slider-value"><?= te_esc($buttons['borderRadius'] ?? '8') ?>px</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="toggle-control">
                                <span class="control-label" style="margin:0">Uppercase</span>
                                <div class="toggle-switch <?= !empty($buttons['uppercase']) ? 'active' : '' ?>" data-setting="buttons.uppercase"></div>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="toggle-control">
                                <span class="control-label" style="margin:0">Shadow</span>
                                <div class="toggle-switch <?= ($buttons['shadow'] ?? true) ? 'active' : '' ?>" data-setting="buttons.shadow"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Layout Panel -->
                <div class="panel">
                    <button class="panel-header"><span>📏 Layout</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <label class="control-label">Container Width <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="960" max="1600" step="40" value="<?= te_esc($layout['containerWidth'] ?? '1200') ?>" data-setting="layout.containerWidth">
                                <span class="slider-value"><?= te_esc($layout['containerWidth'] ?? '1200') ?>px</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Section Spacing <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="40" max="160" step="10" value="<?= te_esc($layout['sectionSpacing'] ?? '100') ?>" data-setting="layout.sectionSpacing">
                                <span class="slider-value"><?= te_esc($layout['sectionSpacing'] ?? '100') ?>px</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Border Radius <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="0" max="24" value="<?= te_esc($layout['borderRadius'] ?? '12') ?>" data-setting="layout.borderRadius">
                                <span class="slider-value"><?= te_esc($layout['borderRadius'] ?? '12') ?>px</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Header Panel -->
                <div class="panel">
                    <button class="panel-header"><span>📐 Header</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <label class="control-label">Height <span class="hint">px</span></label>
                            <div class="slider-control">
                                <input type="range" min="56" max="100" value="<?= te_esc($header['height'] ?? '72') ?>" data-setting="header.height">
                                <span class="slider-value"><?= te_esc($header['height'] ?? '72') ?>px</span>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="toggle-control">
                                <span class="control-label" style="margin:0">Sticky Header</span>
                                <div class="toggle-switch <?= ($header['sticky'] ?? true) ? 'active' : '' ?>" data-setting="header.sticky"></div>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="toggle-control">
                                <span class="control-label" style="margin:0">Blur Effect</span>
                                <div class="toggle-switch <?= ($header['blur'] ?? true) ? 'active' : '' ?>" data-setting="header.blur"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-panel" data-panel="advanced">
                <div class="panel open">
                    <button class="panel-header"><span>💻 Custom CSS</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <textarea class="control-input" id="custom-css" placeholder="/* Your custom CSS */" style="min-height:300px"><?= te_esc($customCSS) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-panel" data-panel="info">
                <div class="panel open">
                    <button class="panel-header"><span>ℹ️ Theme Info</span><span class="arrow">▼</span></button>
                    <div class="panel-body">
                        <div class="control-group">
                            <label class="control-label">Theme Name</label>
                            <input type="text" class="control-input" id="info-name" value="<?= te_esc($info['name'] ?? $themeName) ?>">
                        </div>
                        <div class="control-group">
                            <label class="control-label">Description</label>
                            <textarea class="control-input" id="info-description" style="min-height:80px"><?= te_esc($info['description'] ?? '') ?></textarea>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Version</label>
                            <input type="text" class="control-input" id="info-version" value="<?= te_esc($info['version'] ?? '1.0.0') ?>">
                        </div>
                        <div class="control-group">
                            <label class="control-label">Author</label>
                            <input type="text" class="control-input" id="info-author" value="<?= te_esc($info['author'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="preview-area">
        <div class="preview-wrapper">
            <div class="preview-frame-container desktop">
                <iframe src="/?preview_theme=<?= urlencode($themeName) ?>" class="preview-frame" id="preview-frame"></iframe>
            </div>
        </div>
        <div class="status-bar">
            <div class="status-indicator">
                <span class="status-dot" id="status-dot"></span>
                <span id="status-text">Ready</span>
            </div>
            <div>Theme: <strong><?= te_esc($themeName) ?></strong> | Press <kbd>Ctrl+S</kbd> to save</div>
        </div>
    </div>
</div>
<div class="toast-container" id="toast-container"></div>

<script>
const ThemeEditor = {
    themeName: '<?= te_esc($themeName) ?>',
    presets: <?= json_encode($presets) ?>,
    history: [],
    historyIndex: -1,
    hasChanges: false,
    state: {
        colors: <?= json_encode($colors) ?>,
        typography: <?= json_encode($typography) ?>,
        header: <?= json_encode($header) ?>,
        buttons: <?= json_encode($buttons) ?>,
        layout: <?= json_encode($layout) ?>,
        effects: <?= json_encode($effects) ?>,
        customCSS: <?= json_encode($customCSS) ?>,
        info: <?= json_encode($info) ?>
    },
    
    init() {
        this.saveState();
        this.bindEvents();
        this.updatePreview();
    },
    
    bindEvents() {
        document.querySelectorAll('.sidebar-tab').forEach(tab => {
            tab.addEventListener('click', () => this.switchTab(tab.dataset.tab));
        });
        document.querySelectorAll('.panel-header').forEach(header => {
            header.addEventListener('click', () => header.closest('.panel').classList.toggle('open'));
        });
        document.querySelectorAll('.device-btn').forEach(btn => {
            btn.addEventListener('click', () => this.setDevice(btn.dataset.device));
        });
        document.querySelectorAll('input[data-color]').forEach(input => {
            input.addEventListener('input', (e) => this.setColor(input.dataset.color, e.target.value));
        });
        document.querySelectorAll('input[data-color-text]').forEach(input => {
            input.addEventListener('change', (e) => {
                if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
                    this.setColor(input.dataset.colorText, e.target.value);
                }
            });
        });
        document.querySelectorAll('.preset-item').forEach(item => {
            item.addEventListener('click', () => this.applyPreset(parseInt(item.dataset.preset)));
        });
        document.querySelectorAll('input[type="range"]').forEach(slider => {
            slider.addEventListener('input', (e) => {
                const value = e.target.value;
                const valueDisplay = slider.nextElementSibling;
                if (valueDisplay) {
                    const unit = valueDisplay.textContent.replace(/[\d.]+/, '');
                    valueDisplay.textContent = value + unit;
                }
                this.setSetting(slider.dataset.setting, value);
            });
        });
        document.querySelectorAll('select[data-setting]').forEach(select => {
            select.addEventListener('change', (e) => this.setSetting(select.dataset.setting, e.target.value));
        });
        document.querySelectorAll('.toggle-switch').forEach(toggle => {
            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
                this.setSetting(toggle.dataset.setting, toggle.classList.contains('active'));
            });
        });
        document.getElementById('custom-css').addEventListener('input', (e) => {
            this.state.customCSS = e.target.value;
            this.markChanged();
            this.updatePreview();
        });
        ['name', 'description', 'version', 'author'].forEach(field => {
            const input = document.getElementById('info-' + field);
            if (input) input.addEventListener('input', (e) => { this.state.info[field] = e.target.value; this.markChanged(); });
        });
        document.getElementById('btn-save').addEventListener('click', () => this.save());
        document.getElementById('btn-undo').addEventListener('click', () => this.undo());
        document.getElementById('btn-redo').addEventListener('click', () => this.redo());
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's') { e.preventDefault(); this.save(); }
                else if (e.key === 'z') { e.preventDefault(); if (e.shiftKey) this.redo(); else this.undo(); }
                else if (e.key === 'y') { e.preventDefault(); this.redo(); }
            }
        });
        window.addEventListener('beforeunload', (e) => { if (this.hasChanges) { e.preventDefault(); e.returnValue = ''; } });
    },
    
    switchTab(tabId) {
        document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelector(`.sidebar-tab[data-tab="${tabId}"]`).classList.add('active');
        document.querySelector(`.tab-panel[data-panel="${tabId}"]`).classList.add('active');
    },
    
    setDevice(device) {
        document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.device-btn[data-device="${device}"]`).classList.add('active');
        document.querySelector('.preview-frame-container').className = 'preview-frame-container ' + device;
    },
    
    setColor(name, value) {
        this.state.colors[name] = value;
        const colorInput = document.querySelector(`input[data-color="${name}"]`);
        const textInput = document.querySelector(`input[data-color-text="${name}"]`);
        const preview = colorInput?.closest('.color-preview');
        if (colorInput) colorInput.value = value;
        if (textInput) textInput.value = value;
        if (preview) preview.style.background = value;
        this.markChanged();
        this.updatePreview();
    },
    
    setSetting(path, value) {
        const parts = path.split('.');
        let obj = this.state;
        for (let i = 0; i < parts.length - 1; i++) {
            if (!obj[parts[i]]) obj[parts[i]] = {};
            obj = obj[parts[i]];
        }
        obj[parts[parts.length - 1]] = value;
        this.markChanged();
        this.updatePreview();
    },
    
    applyPreset(index) {
        const preset = this.presets[index];
        if (!preset) return;
        Object.keys(preset.colors).forEach(key => this.setColor(key, preset.colors[key]));
        document.querySelectorAll('.preset-item').forEach((item, i) => item.classList.toggle('active', i === index));
        this.toast('Preset: ' + preset.name, 'success');
    },
    
    markChanged() {
        this.hasChanges = true;
        this.saveState();
        this.updateButtons();
    },
    
    saveState() {
        this.history = this.history.slice(0, this.historyIndex + 1);
        this.history.push(JSON.stringify(this.state));
        this.historyIndex = this.history.length - 1;
        if (this.history.length > 50) { this.history.shift(); this.historyIndex--; }
        this.updateButtons();
    },
    
    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            this.state = JSON.parse(this.history[this.historyIndex]);
            this.refreshUI();
            this.updatePreview();
            this.updateButtons();
        }
    },
    
    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            this.state = JSON.parse(this.history[this.historyIndex]);
            this.refreshUI();
            this.updatePreview();
            this.updateButtons();
        }
    },
    
    refreshUI() {
        Object.keys(this.state.colors).forEach(key => {
            const colorInput = document.querySelector(`input[data-color="${key}"]`);
            const textInput = document.querySelector(`input[data-color-text="${key}"]`);
            const preview = colorInput?.closest('.color-preview');
            if (colorInput) colorInput.value = this.state.colors[key];
            if (textInput) textInput.value = this.state.colors[key];
            if (preview) preview.style.background = this.state.colors[key];
        });
    },
    
    updateButtons() {
        document.getElementById('btn-undo').disabled = this.historyIndex <= 0;
        document.getElementById('btn-redo').disabled = this.historyIndex >= this.history.length - 1;
    },
    
    updatePreview() {
        const css = this.generateCSS();
        const frame = document.getElementById('preview-frame');
        try {
            const frameDoc = frame.contentDocument || frame.contentWindow.document;
            let styleEl = frameDoc.getElementById('theme-editor-styles');
            if (!styleEl) {
                styleEl = frameDoc.createElement('style');
                styleEl.id = 'theme-editor-styles';
                frameDoc.head.appendChild(styleEl);
            }
            styleEl.textContent = css;
        } catch (e) { console.warn('Cannot access iframe'); }
    },
    
    generateCSS() {
        const c = this.state.colors;
        const t = this.state.typography;
        const b = this.state.buttons;
        const l = this.state.layout;
        const h = this.state.header;
        let css = `:root {
    --color-primary: ${c.primary || '#8b5cf6'};
    --color-secondary: ${c.secondary || '#6366f1'};
    --color-accent: ${c.accent || '#ec4899'};
    --color-background: ${c.background || '#0f0f12'};
    --color-surface: ${c.surface || '#1a1a21'};
    --color-text: ${c.text || '#ffffff'};
    --color-text-muted: ${c.textMuted || '#a0a0b0'};
--color-border: ${c.border || '#2d2d3a'};
    --font-family: '${t.fontFamily || 'Inter'}', sans-serif;
    --font-size-base: ${t.baseFontSize || '16'}px;
    --line-height: ${t.lineHeight || '1.6'};
    --h1-size: ${t.h1Size || '3'}rem;
    --container-width: ${l.containerWidth || '1200'}px;
    --section-spacing: ${l.sectionSpacing || '100'}px;
    --border-radius: ${l.borderRadius || '12'}px;
    --btn-radius: ${b.borderRadius || '8'}px;
    --header-height: ${h.height || '72'}px;
}
body { font-family: var(--font-family); font-size: var(--font-size-base); line-height: var(--line-height); color: var(--color-text); background: var(--color-background); }
h1 { font-size: var(--h1-size); }`;
        if (this.state.customCSS) css += '\n' + this.state.customCSS;
        return css;
    },
    
    async save() {
        this.setStatus('saving', 'Saving...');
        try {
            const response = await fetch(`/admin/theme-editor/${this.themeName}/save`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(this.state)
            });
            const result = await response.json();
            if (result.success) {
                this.hasChanges = false;
                this.setStatus('success', 'Saved');
                this.toast('Theme saved!', 'success');
                document.getElementById('preview-frame').src = document.getElementById('preview-frame').src;
            } else {
                this.setStatus('error', 'Error');
                this.toast(result.error || 'Failed', 'error');
            }
        } catch (e) {
            this.setStatus('error', 'Error');
            this.toast('Network error', 'error');
        }
    },
    
    setStatus(type, text) {
        const dot = document.getElementById('status-dot');
        dot.className = 'status-dot';
        if (type === 'saving') dot.classList.add('saving');
        else if (type === 'error') dot.classList.add('warning');
        document.getElementById('status-text').textContent = text;
    },
    
    toast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.innerHTML = `<span>${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span> ${message}`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
};

document.addEventListener('DOMContentLoaded', () => ThemeEditor.init());
</script>
</body>
</html>
