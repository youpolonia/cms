<?php
/**
 * CMS Frontend Visual Editor v3
 * 
 * Features:
 *   1. Inline text editing (contenteditable) on data-ts elements
 *   2. Image picking via JTB Media Gallery (upload/library/Pexels/AI)
 *   3. Link URL editing (popup) on data-ts-href elements
 *   4. Tabbed side panel: Content tab (grouped field editing) + Style tab (CSS controls)
 *   5. Professional CSS editing controls (typography, spacing, background, border, effects)
 *   6. Visual box model diagram (Chrome DevTools style)
 *   7. CSS override storage and rendering for all visitors
 *   8. Floating save bar with change counter
 *   9. Catppuccin Mocha dark theme
 * 
 * Saves via /api/theme-studio/save and /api/theme-studio/upload endpoints.
 */

if (!function_exists('cms_visual_editor_toolbar_button')) {
    function cms_visual_editor_toolbar_button(): string {
        return <<<'HTML'
    <a href="#" id="cms-ve-toggle" class="tb-edit" title="Visual Editor — edit content directly on this page" onclick="event.preventDefault(); cmsVE.toggle();">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        <span id="cms-ve-label">✏️ Edit</span>
    </a>
    <span class="tb-sep"></span>
HTML;
    }
}

if (!function_exists('cms_visual_editor_dependencies')) {
    /**
     * Renders the JTB Media Gallery modal + scripts on the frontend.
     * Called in the injection pipeline.
     */
    function cms_visual_editor_dependencies(): string {
        if (!function_exists('cms_is_admin_logged_in') || !cms_is_admin_logged_in()) {
            return '';
        }

        $html = '';

        // Media Gallery CSS
        $mgCss = '/plugins/jessie-theme-builder/assets/css/media-gallery.css';
        if (file_exists(\CMS_ROOT . $mgCss)) {
            $html .= '<link rel="stylesheet" href="' . $mgCss . '">' . "\n";
        }

        // Media Gallery Modal HTML
        $mgPath = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-media-gallery.php';
        if (file_exists($mgPath)) {
            require_once $mgPath;
            $csrf = $_SESSION['csrf_token'] ?? '';
            $pexelsKey = '';
            try {
                $pdo = \core\Database::connection();
                $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key'");
                $stmt->execute();
                $pexelsKey = $stmt->fetchColumn() ?: '';
            } catch (\Throwable $e) {}
            ob_start();
            jtb_render_media_gallery_modal($csrf, $pexelsKey);
            $html .= ob_get_clean();
        }

        // Media Gallery JS
        $mgJs = '/plugins/jessie-theme-builder/assets/js/media-gallery.js';
        if (file_exists(\CMS_ROOT . $mgJs)) {
            $html .= '<script src="' . $mgJs . '"></script>' . "\n";
        }

        return $html;
    }
}

if (!function_exists('cms_ve_render_style_overrides')) {
    /**
     * Generate a <style> block from saved CSS overrides (_ve_styles section).
     * This is rendered for ALL visitors (not just admin).
     * Called from generate_studio_css_overrides() in theme-customizer.php or directly in layout.
     */
    function cms_ve_render_style_overrides(): string {
        $themeSlug = function_exists('get_active_theme') ? get_active_theme() : '';
        if (!$themeSlug) return '';

        $customs = [];
        if (function_exists('_theme_load_customizations')) {
            $customs = _theme_load_customizations($themeSlug);
        } elseif (function_exists('theme_get_all')) {
            $customs = theme_get_all($themeSlug);
        }

        if (empty($customs['_ve_styles'])) return '';

        $css = '';
        foreach ($customs['_ve_styles'] as $tsKey => $jsonStr) {
            if (empty($jsonStr)) continue;
            $props = json_decode($jsonStr, true);
            if (!is_array($props) || empty($props)) continue;

            $rules = '';
            foreach ($props as $prop => $val) {
                $safeProp = preg_replace('/[^a-z0-9-]/', '', strtolower($prop));
                $safeVal = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
                $rules .= "    {$safeProp}: {$safeVal};\n";
            }
            if ($rules) {
                $safeTsKey = htmlspecialchars($tsKey, ENT_QUOTES, 'UTF-8');
                $css .= "[data-ts=\"{$safeTsKey}\"] {\n{$rules}}\n";
            }
        }

        if (!$css) return '';
        return '<style id="cms-ve-overrides">' . "\n" . $css . '</style>' . "\n";
    }
}

if (!function_exists('cms_visual_editor_assets')) {
    function cms_visual_editor_assets(): string {
        if (!function_exists('cms_is_admin_logged_in') || !cms_is_admin_logged_in()) {
            return '';
        }

        $csrf = $_SESSION['csrf_token'] ?? '';
        $theme = function_exists('get_active_theme') ? get_active_theme() : '';

        // Build schema JSON for the side panel
        $schemaJson = '{}';
        if (function_exists('theme_get_schema')) {
            $schema = theme_get_schema($theme ?: null);
            $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE);
        }
        $valuesJson = '{}';
        if (function_exists('theme_get_all')) {
            $vals = theme_get_all($theme ?: null);
            $valuesJson = json_encode($vals, JSON_UNESCAPED_UNICODE);
        }

        return <<<ASSETS
<!-- CMS Visual Editor v3 -->
<style>
/* ══════════════════════════════════════════════════════
   VISUAL EDITOR v3 — Catppuccin Mocha Dark Theme
   ══════════════════════════════════════════════════════ */

/* ── CSS Variables ─────────────────────────────────── */
:root {
    --ve-bg: #1e1e2e;
    --ve-bg-deep: #181825;
    --ve-surface: #313244;
    --ve-border: #45475a;
    --ve-text: #cdd6f4;
    --ve-subtext: #a6adc8;
    --ve-muted: #6c7086;
    --ve-blue: #89b4fa;
    --ve-green: #a6e3a1;
    --ve-red: #f38ba8;
    --ve-mauve: #cba6f7;
    --ve-peach: #fab387;
    --ve-yellow: #f9e2af;
    --ve-font: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', sans-serif;
    --ve-panel-w: 380px;
    --ve-radius: 8px;
    --ve-radius-sm: 6px;
    --ve-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ── Edit Mode Indicators ──────────────────────────── */
body.cms-ve-active [data-ts],
body.cms-ve-active [data-ts-bg] {
    cursor: pointer !important;
    transition: outline 0.15s, box-shadow 0.15s !important;
}
body.cms-ve-active [data-ts]:hover {
    outline: 2px dashed rgba(137,180,250,0.5) !important;
    outline-offset: 3px !important;
}
body.cms-ve-active [data-ts-bg]:hover {
    outline: 2px dashed rgba(166,227,161,0.5) !important;
    outline-offset: 3px !important;
}
body.cms-ve-active [data-ts].cms-ve-editing {
    outline: 2px solid rgba(137,180,250,0.9) !important;
    outline-offset: 3px !important;
    min-height: 1em;
}
body.cms-ve-active [data-ts].cms-ve-style-selected {
    outline: 2px solid var(--ve-mauve) !important;
    outline-offset: 3px !important;
}

/* ── Field Label Tooltip ───────────────────────────── */
.cms-ve-label {
    position: absolute;
    background: var(--ve-bg);
    color: var(--ve-blue);
    font: 600 10px/1 var(--ve-font);
    padding: 3px 7px;
    border-radius: 3px;
    z-index: 99990;
    pointer-events: none;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    opacity: 0;
    transition: opacity 0.15s;
}
.cms-ve-label.img { color: var(--ve-green); }
.cms-ve-label.visible { opacity: 1; }

/* ── Save Bar ──────────────────────────────────────── */
#cms-ve-bar {
    position: fixed;
    bottom: -60px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--ve-bg);
    border: 1px solid var(--ve-border);
    border-radius: 12px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 99999;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    font: 13px var(--ve-font);
    color: var(--ve-text);
    transition: bottom 0.3s cubic-bezier(0.4,0,0.2,1);
}
#cms-ve-bar.visible { bottom: 24px; }
#cms-ve-bar .ve-count { color: var(--ve-subtext); font-size: 12px; }
#cms-ve-bar button {
    border: none; border-radius: 8px; padding: 8px 20px;
    font: 600 13px var(--ve-font); cursor: pointer; transition: all 0.15s;
}
#cms-ve-bar .ve-save { background: var(--ve-green); color: var(--ve-bg); }
#cms-ve-bar .ve-save:hover { filter: brightness(1.1); transform: translateY(-1px); }
#cms-ve-bar .ve-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
#cms-ve-bar .ve-cancel { background: transparent; color: var(--ve-red); border: 1px solid var(--ve-border); }
#cms-ve-bar .ve-cancel:hover { background: rgba(243,139,168,0.1); border-color: var(--ve-red); }

/* ── Undo/Redo buttons ────────────────────────────── */
#cms-ve-bar .ve-undo-redo { display: flex; gap: 4px; margin-right: 4px; }
#cms-ve-bar .ve-ur-btn {
    background: var(--ve-surface); color: var(--ve-text); border: 1px solid var(--ve-border);
    border-radius: 6px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
    cursor: pointer; padding: 0; transition: all 0.15s; font-size: 15px;
}
#cms-ve-bar .ve-ur-btn:hover:not(:disabled) { background: var(--ve-border); border-color: var(--ve-blue); }
#cms-ve-bar .ve-ur-btn:disabled { opacity: 0.3; cursor: not-allowed; }
#cms-ve-bar .ve-ur-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }

/* ── Image Button (corner — doesn't block text clicks) */
.cms-ve-img-ov {
    position: absolute;
    top: 8px; right: 8px;
    width: auto; height: auto;
    background: rgba(0,0,0,0.75);
    display: flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    opacity: 0; transition: opacity 0.2s;
    z-index: 10; cursor: pointer;
    pointer-events: none;
    backdrop-filter: blur(4px);
}
body.cms-ve-active [data-ts-bg]:hover .cms-ve-img-ov,
body.cms-ve-active .cms-ve-img-ov:hover { opacity: 1; pointer-events: auto; }
.cms-ve-img-ov i { font-size: 14px; color: rgba(255,255,255,0.9); }
.cms-ve-img-ov span { font: 600 11px var(--ve-font); color: rgba(255,255,255,0.8); }

/* Background image picker in Style panel */
.ve-bg-img-wrap { width: 100%; }
.ve-bg-img-preview {
    width: 100%; height: 80px; border-radius: var(--ve-radius);
    background: var(--ve-surface); border: 1px dashed var(--ve-border);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; margin-bottom: 6px;
}
.ve-bg-img-preview img { width: 100%; height: 100%; object-fit: cover; }
.ve-bg-img-empty { color: var(--ve-muted); font-size: 11px; }
.ve-bg-img-actions { display: flex; gap: 6px; }
.ve-bg-choose, .ve-bg-clear {
    padding: 5px 10px; font: 600 11px var(--ve-font);
    border-radius: var(--ve-radius); cursor: pointer;
    transition: all var(--ve-transition);
}
.ve-bg-choose { flex: 1; background: var(--ve-surface); border: 1px solid var(--ve-border); color: var(--ve-text); }
.ve-bg-choose:hover { border-color: var(--ve-blue); color: var(--ve-blue); }
.ve-bg-clear { background: transparent; border: 1px solid var(--ve-border); color: var(--ve-red); }
.ve-bg-clear:hover { border-color: var(--ve-red); }

/* ── Slider + Number combo ──────────────────── */
.ve-slider-num {
    display: flex; align-items: center; gap: 8px; flex: 1;
}
.ve-slider-num input[type="range"] {
    flex: 1; height: 4px;
    -webkit-appearance: none; appearance: none;
    background: var(--ve-surface); border-radius: 2px; outline: none;
}
.ve-slider-num input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none; width: 12px; height: 12px;
    background: var(--ve-blue); border-radius: 50%; cursor: pointer;
    border: 2px solid var(--ve-bg);
}
.ve-slider-num input[type="range"]::-moz-range-thumb {
    width: 12px; height: 12px;
    background: var(--ve-blue); border-radius: 50%; cursor: pointer;
    border: 2px solid var(--ve-bg);
}
.ve-slider-num .ve-sn-num {
    width: 54px; flex-shrink: 0;
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius);
    padding: 4px 6px; font: 12px var(--ve-font); text-align: center;
    outline: none; -moz-appearance: textfield;
}
.ve-slider-num .ve-sn-num:focus { border-color: var(--ve-blue); }
.ve-slider-num .ve-sn-num::-webkit-inner-spin-button { -webkit-appearance: none; }
.ve-unit-tag {
    font-size: 10px; color: var(--ve-muted); font-weight: 600;
    min-width: 18px; text-align: center; flex-shrink: 0;
}
.ve-auto-btn {
    padding: 4px 8px; font: 600 10px var(--ve-font);
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-muted); border-radius: var(--ve-radius);
    cursor: pointer; transition: all var(--ve-transition); flex-shrink: 0;
}
.ve-auto-btn.active { background: var(--ve-blue); color: var(--ve-bg); border-color: var(--ve-blue); }
.ve-auto-btn:hover { border-color: var(--ve-blue); }

/* Corner slider (margin/padding/radius rows) */
.ve-corner-slider {
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 4px;
}
.ve-corner-slider .ve-corner-label {
    width: 16px; text-align: center; font-size: 12px;
    color: var(--ve-muted); flex-shrink: 0;
}

/* ── Filter/Transform rows ─────────────────── */
.ve-filter-grid { display: flex; flex-direction: column; gap: 8px; }
.ve-filter-row {
    display: flex; align-items: center; gap: 8px;
}
.ve-filter-row .ve-filter-lbl {
    width: 70px; font-size: 11px; color: var(--ve-subtext); flex-shrink: 0;
}

/* Section-level click hint */
body.cms-ve-active section:hover,
body.cms-ve-active [class*="section"]:hover {
    cursor: pointer;
}

/* ── Side Panel ────────────────────────────────────── */
#cms-ve-panel {
    position: fixed;
    top: 36px;
    right: calc(-1 * var(--ve-panel-w) - 20px);
    width: var(--ve-panel-w);
    height: calc(100vh - 36px);
    background: var(--ve-bg);
    border-left: 1px solid var(--ve-border);
    z-index: 99995;
    display: flex;
    flex-direction: column;
    transition: right 0.3s cubic-bezier(0.4,0,0.2,1);
    font: 13px var(--ve-font);
    color: var(--ve-text);
    box-shadow: -4px 0 20px rgba(0,0,0,0.3);
}
#cms-ve-panel.open { right: 0; }

/* ── Panel Header ──────────────────────────────────── */
.vep-header {
    flex-shrink: 0;
    background: var(--ve-bg-deep);
    padding: 12px 16px;
    border-bottom: 1px solid var(--ve-surface);
    display: flex; align-items: center; justify-content: space-between;
}
.vep-header h3 {
    font-size: 14px; font-weight: 700; margin: 0;
    display: flex; align-items: center; gap: 8px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.vep-close {
    background: none; border: none; color: var(--ve-muted); cursor: pointer;
    font-size: 18px; padding: 4px 8px; border-radius: var(--ve-radius-sm);
    transition: all 0.15s; flex-shrink: 0;
}
.vep-close:hover { color: var(--ve-red); background: rgba(243,139,168,0.1); }

/* ── Tabs ──────────────────────────────────────────── */
.vep-tabs {
    flex-shrink: 0;
    display: flex;
    background: var(--ve-bg-deep);
    border-bottom: 1px solid var(--ve-surface);
    padding: 0 12px;
}
.vep-tab {
    flex: 1; padding: 10px 0;
    background: none; border: none; border-bottom: 2px solid transparent;
    color: var(--ve-muted); font: 600 12px var(--ve-font);
    cursor: pointer; transition: all var(--ve-transition);
    display: flex; align-items: center; justify-content: center; gap: 6px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.vep-tab:hover { color: var(--ve-subtext); }
.vep-tab.active {
    color: var(--ve-blue);
    border-bottom-color: var(--ve-blue);
}
.vep-tab svg { width: 14px; height: 14px; }

/* ── Tab Content ───────────────────────────────────── */
.vep-tab-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}
.vep-tab-content::-webkit-scrollbar { width: 6px; }
.vep-tab-content::-webkit-scrollbar-track { background: transparent; }
.vep-tab-content::-webkit-scrollbar-thumb { background: var(--ve-border); border-radius: 3px; }
.vep-tab-pane { display: none; }
.vep-tab-pane.active { display: block; }

/* ── Content Tab Fields ────────────────────────────── */
.vep-group {
    border-bottom: 1px solid var(--ve-surface);
}
.vep-group:last-child { border-bottom: none; }
.vep-group-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px;
    cursor: pointer;
    user-select: none;
    transition: background var(--ve-transition);
}
.vep-group-header:hover { background: rgba(49,50,68,0.5); }
.vep-group-label {
    font-size: 11px; font-weight: 700; color: var(--ve-subtext);
    text-transform: uppercase; letter-spacing: 0.5px;
    display: flex; align-items: center; gap: 6px;
}
.vep-group-count {
    font-size: 9px; font-weight: 500; color: var(--ve-muted);
    background: var(--ve-surface); padding: 1px 6px; border-radius: 8px;
}
.vep-group-chevron {
    color: var(--ve-muted);
    transition: transform 0.25s ease;
    font-size: 10px;
}
.vep-group.collapsed .vep-group-chevron { transform: rotate(-90deg); }
.vep-group-body {
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.2s ease;
    max-height: 2000px;
    opacity: 1;
}
.vep-group.collapsed .vep-group-body {
    max-height: 0;
    opacity: 0;
}

.vep-field { padding: 8px 16px; }
.vep-field-label {
    font-size: 11px; font-weight: 600; color: var(--ve-subtext);
    margin-bottom: 5px; display: flex; align-items: center; gap: 6px;
}
.vep-field-label .vep-type {
    font-size: 9px; font-weight: 500; color: var(--ve-muted);
    background: var(--ve-surface); padding: 1px 5px; border-radius: 3px;
    text-transform: lowercase; letter-spacing: 0;
}
.vep-field input[type="text"],
.vep-field textarea {
    width: 100%; box-sizing: border-box;
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius-sm); padding: 8px 10px;
    font: 13px var(--ve-font); outline: none; transition: border-color 0.15s;
}
.vep-field input:focus, .vep-field textarea:focus { border-color: var(--ve-blue); }
.vep-field textarea { min-height: 60px; resize: vertical; line-height: 1.5; }
.vep-field input[type="color"] {
    width: 36px; height: 32px; border: 1px solid var(--ve-border);
    border-radius: 4px; cursor: pointer; background: var(--ve-surface); padding: 2px;
}
.vep-field .vep-color-row { display: flex; gap: 8px; align-items: center; }
.vep-field .vep-color-hex { flex: 1; }

.vep-field .vep-toggle { display: flex; align-items: center; gap: 8px; }
.vep-field .vep-toggle input[type="checkbox"] {
    width: 36px; height: 20px; appearance: none; -webkit-appearance: none;
    background: var(--ve-border); border-radius: 10px; position: relative;
    cursor: pointer; transition: background 0.2s; flex-shrink: 0; border: none;
}
.vep-field .vep-toggle input:checked { background: var(--ve-green); }
.vep-field .vep-toggle input::after {
    content: ''; position: absolute; top: 2px; left: 2px;
    width: 16px; height: 16px; background: white; border-radius: 50%;
    transition: transform 0.2s;
}
.vep-field .vep-toggle input:checked::after { transform: translateX(16px); }

.vep-img-preview {
    width: 100%; aspect-ratio: 16/9; border-radius: var(--ve-radius);
    background: var(--ve-surface); border: 1px dashed var(--ve-border);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; cursor: pointer; transition: border-color 0.2s;
    margin-bottom: 6px;
}
.vep-img-preview:hover { border-color: var(--ve-blue); }
.vep-img-preview img { width: 100%; height: 100%; object-fit: cover; }
.vep-img-preview .vep-img-empty { color: var(--ve-muted); font-size: 12px; text-align: center; }
.vep-img-actions { display: flex; gap: 6px; }
.vep-img-actions button {
    flex: 1; padding: 6px; font: 600 11px var(--ve-font);
    border-radius: var(--ve-radius-sm); cursor: pointer; transition: all 0.15s;
}
.vep-img-choose {
    background: var(--ve-surface); border: 1px solid var(--ve-border); color: var(--ve-text);
}
.vep-img-choose:hover { border-color: var(--ve-blue); color: var(--ve-blue); }
.vep-img-clear {
    background: transparent; border: 1px solid var(--ve-border); color: var(--ve-red);
}
.vep-img-clear:hover { border-color: var(--ve-red); }

/* ── Style Tab ─────────────────────────────────────── */
.vep-style-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 40px 20px; text-align: center;
    color: var(--ve-muted);
}
.vep-style-empty svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.4; }
.vep-style-empty p { margin: 4px 0; font-size: 13px; }
.vep-style-empty .hint { font-size: 11px; margin-top: 8px; }

.vep-style-target {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px;
    background: var(--ve-bg-deep);
    border-bottom: 1px solid var(--ve-surface);
    font-size: 11px; color: var(--ve-subtext);
}
.vep-style-target code {
    background: var(--ve-surface); padding: 2px 8px; border-radius: 4px;
    font-family: 'SF Mono', 'Fira Code', monospace; font-size: 11px;
    color: var(--ve-mauve);
}
.vep-style-target .vep-style-reset {
    margin-left: auto; background: none; border: none;
    color: var(--ve-red); font-size: 11px; cursor: pointer;
    padding: 2px 6px; border-radius: 4px; transition: all 0.15s;
}
.vep-style-target .vep-style-reset:hover { background: rgba(243,139,168,0.1); }

/* ── Collapsible Style Groups ──────────────────────── */
.vep-style-group {
    border-bottom: 1px solid var(--ve-surface);
}
.vep-style-group:last-child { border-bottom: none; }
.vep-style-group-header {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; cursor: pointer; user-select: none;
    transition: background var(--ve-transition);
    font-size: 12px; font-weight: 600; color: var(--ve-subtext);
}
.vep-style-group-header:hover { background: rgba(49,50,68,0.5); }
.vep-style-group-header .sg-icon { width: 14px; height: 14px; flex-shrink: 0; }
.vep-style-group-header .sg-chevron {
    margin-left: auto; font-size: 10px; color: var(--ve-muted);
    transition: transform 0.25s ease;
}
.vep-style-group.collapsed .sg-chevron { transform: rotate(-90deg); }
.vep-style-group-body {
    padding: 4px 16px 12px;
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.2s ease, padding 0.3s ease;
    max-height: 1200px; opacity: 1;
}
.vep-style-group.collapsed .vep-style-group-body {
    max-height: 0; opacity: 0; padding-top: 0; padding-bottom: 0;
}

/* ── Style Controls Common ─────────────────────────── */
.ve-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.ve-row:last-child { margin-bottom: 0; }
.ve-label {
    font-size: 11px; color: var(--ve-muted); min-width: 70px; flex-shrink: 0;
}

/* Number input with stepper */
.ve-num-wrap {
    display: flex; align-items: stretch; border: 1px solid var(--ve-border);
    border-radius: var(--ve-radius-sm); overflow: hidden;
    background: var(--ve-surface); flex: 1; max-width: 120px; height: 30px;
}
.ve-num-wrap input[type="number"] {
    width: 100%; min-width: 0; border: none; background: transparent;
    color: var(--ve-text); font: 12px var(--ve-font); padding: 0 8px;
    text-align: center; outline: none;
    -moz-appearance: textfield;
}
.ve-num-wrap input[type="number"]::-webkit-outer-spin-button,
.ve-num-wrap input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; }
.ve-num-step {
    display: flex; align-items: center; justify-content: center;
    width: 24px; background: transparent; border: none; border-left: 1px solid var(--ve-border);
    color: var(--ve-muted); cursor: pointer; font-size: 12px;
    transition: all 0.1s; flex-shrink: 0; padding: 0;
}
.ve-num-step:first-child { border-left: none; border-right: 1px solid var(--ve-border); }
.ve-num-step:hover { background: rgba(137,180,250,0.1); color: var(--ve-blue); }

/* Unit selector */
.ve-unit-sel {
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-muted); font: 11px var(--ve-font); padding: 0 6px;
    border-radius: var(--ve-radius-sm); cursor: pointer; height: 30px;
    outline: none; min-width: 42px;
}
.ve-unit-sel:focus { border-color: var(--ve-blue); }
.ve-font-sel { font-size: 13px; height: 34px; color: var(--ve-text); }
.ve-font-sel option { padding: 4px 8px; font-size: 13px; }
.ve-font-sel optgroup { font-size: 11px; color: var(--ve-muted); font-style: normal; }

/* Button group */
.ve-btn-group {
    display: flex; border: 1px solid var(--ve-border); border-radius: var(--ve-radius-sm);
    overflow: hidden; flex: 1;
}
.ve-btn-group button {
    flex: 1; background: transparent; border: none; border-right: 1px solid var(--ve-border);
    color: var(--ve-muted); font: 12px var(--ve-font); padding: 5px 0; cursor: pointer;
    transition: all 0.15s; display: flex; align-items: center; justify-content: center;
    min-height: 30px;
}
.ve-btn-group button:last-child { border-right: none; }
.ve-btn-group button:hover { background: rgba(137,180,250,0.08); color: var(--ve-subtext); }
.ve-btn-group button.active { background: var(--ve-blue); color: var(--ve-bg); }
.ve-btn-group button svg { width: 14px; height: 14px; }

/* Color row */
.ve-color-row { display: flex; gap: 6px; align-items: center; flex: 1; }
.ve-color-row input[type="color"] {
    width: 30px; height: 30px; border: 1px solid var(--ve-border);
    border-radius: var(--ve-radius-sm); cursor: pointer; background: var(--ve-surface);
    padding: 2px; flex-shrink: 0;
}
.ve-color-row input[type="text"] {
    flex: 1; background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius-sm); padding: 0 8px;
    font: 12px 'SF Mono', 'Fira Code', monospace; outline: none; height: 30px; box-sizing: border-box;
}
.ve-color-row input[type="text"]:focus { border-color: var(--ve-blue); }

/* ── Box Model Diagram ─────────────────────────────── */
.ve-boxmodel {
    position: relative;
    margin: 4px 0 8px;
}
.ve-bm-layer {
    border: 1px dashed;
    border-radius: 4px;
    position: relative;
    display: flex; align-items: center; justify-content: center;
    min-height: 40px;
}
.ve-bm-margin {
    border-color: var(--ve-peach);
    background: rgba(250,179,135,0.05);
    padding: 16px;
}
.ve-bm-padding {
    border-color: var(--ve-green);
    background: rgba(166,227,161,0.05);
    padding: 16px;
    width: 100%; box-sizing: border-box;
}
.ve-bm-element {
    border-color: var(--ve-blue);
    background: rgba(137,180,250,0.08);
    width: 100%; box-sizing: border-box;
    padding: 6px;
    text-align: center; font-size: 10px; color: var(--ve-muted);
}
.ve-bm-label {
    position: absolute; font-size: 9px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.ve-bm-margin > .ve-bm-label { top: 1px; left: 6px; color: var(--ve-peach); }
.ve-bm-padding > .ve-bm-label { top: 1px; left: 6px; color: var(--ve-green); }
.ve-bm-input {
    position: absolute; width: 36px; height: 20px;
    background: transparent; border: none; border-bottom: 1px dashed;
    color: inherit; font: 11px var(--ve-font); text-align: center;
    outline: none; padding: 0;
    -moz-appearance: textfield;
}
.ve-bm-input::-webkit-outer-spin-button,
.ve-bm-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.ve-bm-input:focus { border-bottom-style: solid; }
.ve-bm-margin > .ve-bm-input { border-color: var(--ve-peach); color: var(--ve-peach); }
.ve-bm-padding > .ve-bm-input { border-color: var(--ve-green); color: var(--ve-green); }
.ve-bm-input.bm-top { top: 1px; left: 50%; transform: translateX(-50%); }
.ve-bm-input.bm-right { right: 4px; top: 50%; transform: translateY(-50%); }
.ve-bm-input.bm-bottom { bottom: 1px; left: 50%; transform: translateX(-50%); }
.ve-bm-input.bm-left { left: 4px; top: 50%; transform: translateY(-50%); }

/* Link toggle for uniform values */
.ve-link-toggle {
    width: 24px; height: 24px; border-radius: 50%;
    background: transparent; border: 1px solid var(--ve-border);
    color: var(--ve-muted); cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: 10px;
    transition: all 0.15s; flex-shrink: 0; padding: 0;
}
.ve-link-toggle.active { background: var(--ve-blue); color: var(--ve-bg); border-color: var(--ve-blue); }

/* ── 4-corner inputs ───────────────────────────────── */
.ve-corners {
    display: grid; grid-template-columns: 1fr 1fr; gap: 4px; flex: 1;
}
.ve-corners .ve-corner-input {
    display: flex; align-items: center; gap: 4px;
}
.ve-corners .ve-corner-input input {
    width: 100%; background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius-sm); padding: 4px 6px;
    font: 11px var(--ve-font); text-align: center; outline: none; height: 28px;
    box-sizing: border-box;
    -moz-appearance: textfield;
}
.ve-corners .ve-corner-input input::-webkit-outer-spin-button,
.ve-corners .ve-corner-input input::-webkit-inner-spin-button { -webkit-appearance: none; }
.ve-corners .ve-corner-input input:focus { border-color: var(--ve-blue); }
.ve-corners .ve-corner-label { font-size: 9px; color: var(--ve-muted); width: 12px; text-align: center; }

/* ── Range slider ──────────────────────────────────── */
.ve-range-wrap {
    display: flex; align-items: center; gap: 8px; flex: 1;
}
.ve-range-wrap input[type="range"] {
    flex: 1; -webkit-appearance: none; appearance: none;
    height: 4px; border-radius: 2px; background: var(--ve-surface);
    outline: none;
}
.ve-range-wrap input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none; width: 14px; height: 14px; border-radius: 50%;
    background: var(--ve-blue); cursor: pointer; border: 2px solid var(--ve-bg);
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
}
.ve-range-wrap .ve-range-val {
    font: 11px var(--ve-font); color: var(--ve-subtext); min-width: 32px; text-align: right;
}

/* ── Shadow row ────────────────────────────────────── */
.ve-shadow-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 6px;
}
.ve-shadow-grid .ve-shadow-field {
    display: flex; flex-direction: column; gap: 2px;
}
.ve-shadow-grid .ve-shadow-field label {
    font-size: 9px; color: var(--ve-muted); text-transform: uppercase;
}
.ve-shadow-grid .ve-shadow-field input {
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius-sm); padding: 4px 6px;
    font: 11px var(--ve-font); text-align: center; outline: none; height: 28px;
    box-sizing: border-box;
    -moz-appearance: textfield;
}
.ve-shadow-grid .ve-shadow-field input::-webkit-outer-spin-button,
.ve-shadow-grid .ve-shadow-field input::-webkit-inner-spin-button { -webkit-appearance: none; }
.ve-shadow-grid .ve-shadow-field input:focus { border-color: var(--ve-blue); }

/* ── Panel Save Row ────────────────────────────────── */
.vep-save-row {
    flex-shrink: 0;
    padding: 12px 16px;
    background: var(--ve-bg-deep);
    border-top: 1px solid var(--ve-surface);
    display: flex; gap: 8px;
}
.vep-save-btn {
    flex: 1; padding: 10px; border: none; border-radius: var(--ve-radius);
    background: var(--ve-blue); color: var(--ve-bg); font: 700 13px var(--ve-font);
    cursor: pointer; transition: all 0.15s;
}
.vep-save-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
.vep-save-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }

/* ── Link Edit Popup ───────────────────────────────── */
.cms-ve-link-popup {
    position: absolute; background: var(--ve-bg); border: 1px solid var(--ve-border);
    border-radius: 10px; padding: 12px; z-index: 99998;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    display: flex; gap: 8px; align-items: center;
    font: 13px var(--ve-font);
}
.cms-ve-link-popup input {
    background: var(--ve-surface); border: 1px solid var(--ve-border); color: var(--ve-text);
    border-radius: var(--ve-radius-sm); padding: 6px 10px; font: 13px var(--ve-font);
    width: 260px; outline: none;
}
.cms-ve-link-popup input:focus { border-color: var(--ve-blue); }
.cms-ve-link-popup button {
    background: var(--ve-blue); color: var(--ve-bg); border: none; border-radius: var(--ve-radius-sm);
    padding: 6px 12px; font: 600 12px var(--ve-font); cursor: pointer;
}

/* Highlight active section */
body.cms-ve-active .cms-ve-section-highlight {
    outline: 2px solid rgba(203,166,247,0.4) !important;
    outline-offset: 6px !important;
}

/* ── AI Inline Buttons (Content tab) ───────────────── */
.ve-ai-btn {
    background: transparent; border: 1px solid var(--ve-border); color: var(--ve-mauve);
    border-radius: 4px; width: 24px; height: 24px; display: flex; align-items: center;
    justify-content: center; cursor: pointer; font-size: 12px; padding: 0;
    transition: all 0.15s; flex-shrink: 0; position: relative;
}
.ve-ai-btn:hover { background: rgba(203,166,247,0.15); border-color: var(--ve-mauve); }
.ve-ai-btn.loading { pointer-events: none; opacity: 0.6; }
.ve-ai-btn.loading::after {
    content: ''; width: 12px; height: 12px; border: 2px solid var(--ve-mauve);
    border-top-color: transparent; border-radius: 50%; position: absolute;
    animation: cmsSpin 0.6s linear infinite;
}
.ve-ai-btn.loading > * { visibility: hidden; }

/* AI Dropdown */
.ve-ai-dropdown {
    position: fixed;
    background: var(--ve-bg); border: 1px solid var(--ve-border); border-radius: var(--ve-radius);
    box-shadow: 0 8px 24px rgba(0,0,0,0.5); z-index: 999999; min-width: 200px;
    padding: 4px; font: 13px var(--ve-font);
}
.ve-ai-dropdown-item {
    display: flex; align-items: center; gap: 8px; padding: 8px 12px;
    color: var(--ve-text); cursor: pointer; border-radius: var(--ve-radius-sm);
    border: none; background: none; width: 100%; text-align: left;
    font: 13px var(--ve-font); transition: background 0.1s;
}
.ve-ai-dropdown-item:hover { background: var(--ve-surface); }
.ve-ai-dropdown-item .ai-icon { width: 18px; text-align: center; flex-shrink: 0; }
.ve-ai-dropdown-sep { height: 1px; background: var(--ve-border); margin: 4px 8px; }

/* AI Variants picker */
.ve-ai-variants {
    background: var(--ve-bg-deep); border: 1px solid var(--ve-border); border-radius: var(--ve-radius);
    padding: 8px; margin-top: 6px;
}
.ve-ai-variants-title { font: 600 11px var(--ve-font); color: var(--ve-mauve); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.ve-ai-variant-item {
    display: flex; align-items: flex-start; gap: 8px; padding: 8px;
    border-radius: var(--ve-radius-sm); cursor: pointer; transition: background 0.1s;
    border: 1px solid transparent; margin-bottom: 4px;
}
.ve-ai-variant-item:hover { background: var(--ve-surface); border-color: var(--ve-border); }
.ve-ai-variant-item.selected { background: rgba(137,180,250,0.1); border-color: var(--ve-blue); }
.ve-ai-variant-radio {
    width: 16px; height: 16px; border: 2px solid var(--ve-border); border-radius: 50%;
    flex-shrink: 0; margin-top: 2px; display: flex; align-items: center; justify-content: center;
}
.ve-ai-variant-item.selected .ve-ai-variant-radio { border-color: var(--ve-blue); }
.ve-ai-variant-item.selected .ve-ai-variant-radio::after { content: ''; width: 8px; height: 8px; background: var(--ve-blue); border-radius: 50%; }
.ve-ai-variant-text { font: 13px var(--ve-font); color: var(--ve-text); line-height: 1.4; }

/* AI field row wrapper */
.ve-field-ai-row { display: flex; align-items: flex-start; gap: 6px; }
.ve-field-ai-row > :first-child { flex: 1; min-width: 0; }

/* ── AI Style Chat (Style tab) ─────────────────────── */
.ve-ai-style-chat {
    background: var(--ve-bg-deep); border: 1px solid var(--ve-border);
    border-radius: var(--ve-radius); padding: 10px; margin-bottom: 12px;
}
.ve-ai-style-label { font: 600 11px var(--ve-font); color: var(--ve-mauve); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.ve-ai-style-input-row { display: flex; gap: 6px; }
.ve-ai-style-input {
    flex: 1; background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: var(--ve-radius-sm); padding: 8px 10px;
    font: 13px var(--ve-font); outline: none; resize: none; min-height: 34px; max-height: 80px;
}
.ve-ai-style-input:focus { border-color: var(--ve-mauve); }
.ve-ai-style-input::placeholder { color: var(--ve-muted); }
.ve-ai-style-send {
    background: var(--ve-mauve); color: var(--ve-bg); border: none; border-radius: var(--ve-radius-sm);
    width: 34px; height: 34px; cursor: pointer; display: flex; align-items: center;
    justify-content: center; flex-shrink: 0; transition: all 0.15s;
}
.ve-ai-style-send:hover { filter: brightness(1.15); transform: translateY(-1px); }
.ve-ai-style-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
.ve-ai-style-send svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }

/* Quick suggestions */
.ve-ai-suggestions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
.ve-ai-suggestion {
    background: var(--ve-surface); border: 1px solid var(--ve-border); color: var(--ve-subtext);
    border-radius: 12px; padding: 4px 10px; font: 12px var(--ve-font); cursor: pointer;
    transition: all 0.15s; white-space: nowrap;
}
.ve-ai-suggestion:hover { border-color: var(--ve-mauve); color: var(--ve-mauve); background: rgba(203,166,247,0.08); }

/* AI result message */
.ve-ai-style-result {
    margin-top: 8px; padding: 8px; border-radius: var(--ve-radius-sm);
    font: 12px var(--ve-font); line-height: 1.4;
}
.ve-ai-style-result.success { background: rgba(166,227,161,0.1); color: var(--ve-green); border: 1px solid rgba(166,227,161,0.2); }
.ve-ai-style-result.error { background: rgba(243,139,168,0.1); color: var(--ve-red); border: 1px solid rgba(243,139,168,0.2); }
.ve-ai-style-result .applied-props { margin-top: 4px; font: 11px monospace; color: var(--ve-subtext); }

/* Generate all section button */
.ve-ai-generate-section {
    display: flex; align-items: center; gap: 8px; width: 100%; padding: 10px;
    background: rgba(203,166,247,0.08); border: 1px dashed var(--ve-mauve);
    border-radius: var(--ve-radius); color: var(--ve-mauve); cursor: pointer;
    font: 600 12px var(--ve-font); transition: all 0.15s; margin-top: 12px;
}
.ve-ai-generate-section:hover { background: rgba(203,166,247,0.15); border-style: solid; }
.ve-ai-generate-section:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Drag & Drop Section Reorder ───────────────────── */
.ve-drag-handle {
    position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
    background: rgba(30,30,46,0.92); backdrop-filter: blur(8px);
    border: 1px solid var(--ve-border); border-radius: 8px;
    padding: 6px 16px; display: flex; align-items: center; gap: 10px;
    cursor: grab; z-index: 99990; opacity: 0; pointer-events: none;
    transition: opacity 0.2s; font: 600 12px var(--ve-font); color: var(--ve-text);
    box-shadow: 0 4px 16px rgba(0,0,0,0.4);
}
.ve-drag-handle:active { cursor: grabbing; }
.ve-drag-handle .ve-drag-icon { color: var(--ve-muted); font-size: 14px; }
.ve-drag-handle .ve-drag-label { white-space: nowrap; }
.ve-drag-handle .ve-drag-arrows { display: flex; gap: 2px; margin-left: 4px; }
.ve-drag-handle .ve-drag-arrow {
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: 4px; width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 12px; transition: all 0.15s; padding: 0;
}
.ve-drag-handle .ve-drag-arrow:hover { background: var(--ve-border); border-color: var(--ve-blue); }
.ve-drag-handle .ve-drag-toggle {
    background: none; border: 1px solid var(--ve-border); color: var(--ve-green);
    border-radius: 4px; width: 24px; height: 24px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; transition: all 0.15s; padding: 0;
}
.ve-drag-handle .ve-drag-toggle.disabled { color: var(--ve-red); opacity: 0.6; }
.ve-drag-handle .ve-drag-toggle:hover { background: var(--ve-surface); }

body.cms-ve-active .ve-drag-handle.visible {
    opacity: 1; pointer-events: auto;
}
body.cms-ve-active section.ve-dragging { opacity: 0.4; transition: opacity 0.2s; }
body.cms-ve-active section.ve-drag-over { box-shadow: inset 0 -3px 0 0 var(--ve-blue) !important; }
body.cms-ve-active section.ve-drag-over-top { box-shadow: inset 0 3px 0 0 var(--ve-blue) !important; }
body.cms-ve-active section.ve-section-hidden { opacity: 0.3; filter: grayscale(0.8); }

/* Drop zone indicator */
.ve-drop-indicator {
    height: 4px; background: var(--ve-blue); border-radius: 2px;
    margin: 0; pointer-events: none; transition: all 0.15s;
    box-shadow: 0 0 8px rgba(137,180,250,0.4);
}

/* ── Block Drag & Drop ─────────────────────────────── */
.ve-block-handle {
    position: absolute; top: 4px; left: 4px;
    background: rgba(30,30,46,0.92); backdrop-filter: blur(8px);
    border: 1px solid var(--ve-border); border-radius: 6px;
    padding: 4px 8px; display: flex; align-items: center; gap: 6px;
    cursor: grab; z-index: 99989; opacity: 0; pointer-events: none;
    transition: opacity 0.15s; font: 600 11px var(--ve-font); color: var(--ve-subtext);
    box-shadow: 0 3px 12px rgba(0,0,0,0.4);
}
.ve-block-handle:active { cursor: grabbing; }
.ve-block-handle .ve-bh-grip { color: var(--ve-muted); font-size: 12px; }
.ve-block-handle .ve-bh-label { white-space: nowrap; max-width: 100px; overflow: hidden; text-overflow: ellipsis; }
.ve-block-handle .ve-bh-arrows { display: flex; gap: 2px; }
.ve-block-handle .ve-bh-arrow {
    background: var(--ve-surface); border: 1px solid var(--ve-border);
    color: var(--ve-text); border-radius: 3px; width: 20px; height: 20px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 10px; padding: 0; transition: all 0.15s;
}
.ve-block-handle .ve-bh-arrow:hover { background: var(--ve-border); border-color: var(--ve-peach); color: var(--ve-peach); }
body.cms-ve-active .ve-block-handle.visible { opacity: 1; pointer-events: auto; }
body.cms-ve-active .ve-block-dragging { opacity: 0.35; transition: opacity 0.15s; }
body.cms-ve-active .ve-block-drop-before { box-shadow: inset 0 3px 0 0 var(--ve-peach) !important; }
body.cms-ve-active .ve-block-drop-after { box-shadow: inset 0 -3px 0 0 var(--ve-peach) !important; }
/* Override overflow on header elements so block handles are not clipped */
body.cms-ve-active #siteHeader,
body.cms-ve-active #siteHeader [class*="-header-inner"],
body.cms-ve-active #siteHeader [class*="-nav"],
body.cms-ve-active #siteHeader nav {
    overflow: visible !important;
}
/* NOTE: do NOT force position:relative on block children — breaks absolute-positioned elements like hero-bg, overlays */
</style>

<!-- Side Panel -->
<div id="cms-ve-panel">
    <div class="vep-header">
        <h3 id="vep-title">Section</h3>
        <button class="vep-close" onclick="cmsVE.closePanel()" title="Close">✕</button>
    </div>
    <div class="vep-tabs">
        <button class="vep-tab active" data-tab="content" onclick="cmsVE._switchTab('content')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Content
        </button>
        <button class="vep-tab" data-tab="style" onclick="cmsVE._switchTab('style')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Style
        </button>
    </div>
    <div class="vep-tab-content">
        <div id="vep-content-pane" class="vep-tab-pane active"></div>
        <div id="vep-style-pane" class="vep-tab-pane">
            <div class="vep-style-empty" id="vep-style-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <p>Click an element to style it</p>
                <p class="hint">Select any element with a data-ts attribute on the page</p>
            </div>
            <div id="vep-style-controls" style="display:none;"></div>
        </div>
    </div>
    <div class="vep-save-row">
        <button class="vep-save-btn" onclick="cmsVE.savePanel()">💾 Save Changes</button>
    </div>
</div>

<!-- Save Bar -->
<div id="cms-ve-bar">
    <div class="ve-undo-redo">
        <button class="ve-ur-btn" id="cms-ve-undo" onclick="cmsVE.undo()" disabled title="Undo (Ctrl+Z)">
            <svg viewBox="0 0 24 24"><path d="M3 10h10a5 5 0 0 1 0 10H9"/><polyline points="7 14 3 10 7 6"/></svg>
        </button>
        <button class="ve-ur-btn" id="cms-ve-redo" onclick="cmsVE.redo()" disabled title="Redo (Ctrl+Shift+Z)">
            <svg viewBox="0 0 24 24"><path d="M21 10H11a5 5 0 0 0 0 10h4"/><polyline points="17 14 21 10 17 6"/></svg>
        </button>
    </div>
    <span class="ve-count" id="cms-ve-count">0 changes</span>
    <button class="ve-save" id="cms-ve-save" onclick="cmsVE.save()" disabled>💾 Save All</button>
    <button class="ve-cancel" onclick="cmsVE.cancel()">Cancel</button>
</div>

<script>
(function() {
    'use strict';
    const CSRF = '{$csrf}';
    const THEME = '{$theme}';
    const Q = t => encodeURIComponent(t);
    const API_SAVE = '/api/theme-studio/save' + (THEME ? '?theme=' + Q(THEME) : '');
    const API_UPLOAD = '/api/theme-studio/upload' + (THEME ? '?theme=' + Q(THEME) : '');
    const SCHEMA = {$schemaJson};
    const values = {$valuesJson};

    let active = false;
    const changes = {};       // "section.field" -> newValue (content changes)
    const originals = {};     // "section.field" -> originalValue
    const styleChanges = {};  // "data-ts-value" -> { "prop": "val" } (style changes)
    let hoverLabel = null;
    let panelSection = null;
    let styleTarget = null;   // currently selected element for styling
    let styleTargetKey = null; // data-ts value of style target

    /* ── Undo / Redo ──────────────────────────────────── */
    const undoStack = [];     // array of action objects
    const redoStack = [];
    const UNDO_MAX = 100;     // max history depth
    let lastAction = null;    // for coalescing rapid changes (sliders)
    let lastActionTime = 0;
    const COALESCE_MS = 400;  // merge actions on same prop within this window
    let undoBatch = null;     // when set, collect actions into batch instead of stack

    /* ── Helpers ───────────────────────────────────────── */
    const $ = s => document.querySelector(s);
    const $$ = s => document.querySelectorAll(s);
    const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    function prettyLabel(key) {
        const parts = key.split('.');
        const name = parts[parts.length - 1];
        return name.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function ensureLabel() {
        if (!hoverLabel) { hoverLabel = document.createElement('div'); hoverLabel.className = 'cms-ve-label'; document.body.appendChild(hoverLabel); }
        return hoverLabel;
    }
    function showLabel(el, text, isImg) {
        const lbl = ensureLabel();
        lbl.textContent = isImg ? '📷 ' + text : '✏️ ' + text;
        lbl.className = 'cms-ve-label' + (isImg ? ' img' : '');
        const r = el.getBoundingClientRect();
        lbl.style.left = (r.left + window.scrollX) + 'px';
        lbl.style.top = (r.top + window.scrollY - 22) + 'px';
        requestAnimationFrame(() => lbl.classList.add('visible'));
    }
    function hideLabel() { if (hoverLabel) hoverLabel.classList.remove('visible'); }

    function countAllChanges() {
        let n = Object.keys(changes).length;
        for (const k in styleChanges) {
            if (Object.keys(styleChanges[k]).length > 0) n++;
        }
        return n;
    }

    function updateBar() {
        const n = countAllChanges();
        const bar = $('#cms-ve-bar');
        $('#cms-ve-count').textContent = n + (n === 1 ? ' change' : ' changes');
        $('#cms-ve-save').disabled = n === 0;
        bar.classList.toggle('visible', active && (n > 0 || undoStack.length > 0 || redoStack.length > 0));
        updateUndoRedoButtons();
    }

    function updateUndoRedoButtons() {
        const undoBtn = $('#cms-ve-undo');
        const redoBtn = $('#cms-ve-redo');
        if (undoBtn) undoBtn.disabled = undoStack.length === 0;
        if (redoBtn) redoBtn.disabled = redoStack.length === 0;
    }

    /**
     * Push an action to the undo stack.
     * Coalesces rapid changes on the same property (e.g. slider drags).
     * Action types:
     *   { type:'content', key, oldVal, newVal }
     *   { type:'style', tsKey, prop, oldVal, newVal }
     */
    function pushUndo(action) {
        // Batch mode: collect actions instead of pushing to stack (used by AI)
        if (undoBatch !== null) {
            undoBatch.push(action);
            return;
        }
        const now = Date.now();
        // Coalesce: if same target+prop within COALESCE_MS, update newVal in-place
        if (lastAction && (now - lastActionTime) < COALESCE_MS) {
            if (action.type === 'style' && lastAction.type === 'style'
                && action.tsKey === lastAction.tsKey && action.prop === lastAction.prop) {
                lastAction.newVal = action.newVal;
                lastActionTime = now;
                return;
            }
            if (action.type === 'content' && lastAction.type === 'content'
                && action.key === lastAction.key) {
                lastAction.newVal = action.newVal;
                lastActionTime = now;
                return;
            }
        }
        undoStack.push(action);
        if (undoStack.length > UNDO_MAX) undoStack.shift();
        lastAction = action;
        lastActionTime = now;
        // Clear redo stack on new action
        redoStack.length = 0;
        updateUndoRedoButtons();
    }

    /** Start collecting undo actions into a batch */
    function beginUndoBatch() { undoBatch = []; }

    /** Flush collected actions as a single compound undo entry */
    function commitUndoBatch() {
        const actions = undoBatch;
        undoBatch = null;
        if (!actions || actions.length === 0) return;
        if (actions.length === 1) {
            // Single action — push normally
            pushUndo(actions[0]);
        } else {
            // Compound action
            undoStack.push({ type: 'batch', actions: actions });
            if (undoStack.length > UNDO_MAX) undoStack.shift();
            lastAction = null;
            redoStack.length = 0;
            updateUndoRedoButtons();
        }
    }

    function performUndo() {
        if (undoStack.length === 0) return;
        const action = undoStack.pop();
        redoStack.push(action);
        lastAction = null; // break coalesce chain

        if (action.type === 'batch') {
            // Undo all actions in reverse order
            for (let i = action.actions.length - 1; i >= 0; i--) {
                const a = action.actions[i];
                if (a.type === 'content') applyContentValue(a.key, a.oldVal, false);
                else if (a.type === 'style') applyStyleValue(a.tsKey, a.prop, a.oldVal, false);
            }
        } else if (action.type === 'content') {
            applyContentValue(action.key, action.oldVal, false);
        } else if (action.type === 'style') {
            applyStyleValue(action.tsKey, action.prop, action.oldVal, false);
        }
        updateBar();
    }

    function performRedo() {
        if (redoStack.length === 0) return;
        const action = redoStack.pop();
        undoStack.push(action);
        lastAction = null;

        if (action.type === 'batch') {
            // Redo all actions in original order
            for (const a of action.actions) {
                if (a.type === 'content') applyContentValue(a.key, a.newVal, false);
                else if (a.type === 'style') applyStyleValue(a.tsKey, a.prop, a.newVal, false);
            }
        } else if (action.type === 'content') {
            applyContentValue(action.key, action.newVal, false);
        } else if (action.type === 'style') {
            applyStyleValue(action.tsKey, action.prop, action.newVal, false);
        }
        updateBar();
    }

    /**
     * Apply a content value (used by undo/redo).
     * Updates: DOM element, changes dict, panel input.
     * If pushToUndo=false, skip pushing (we're replaying).
     */
    function applyContentValue(key, val, pushToHistory) {
        // Update DOM element
        const el = document.querySelector('[data-ts="' + key + '"]');
        if (el) el.textContent = val;

        // Update changes tracking
        const origVal = originals[key];
        if (val === origVal || val === undefined) {
            delete changes[key];
        } else {
            changes[key] = val;
        }

        // Sync panel input
        const panelInp = document.querySelector('#vep-content-pane [data-key="' + key + '"]');
        if (panelInp) panelInp.value = val || '';
    }

    /**
     * Apply a style value (used by undo/redo).
     * Updates: inline style, styleChanges dict, panel control.
     */
    function applyStyleValue(tsKey, prop, val, pushToHistory) {
        // Apply inline style — find element by data-ts, data-ts-bg, or data-ve-key (virtual keys for columns etc.)
        const el = document.querySelector('[data-ts="' + tsKey + '"]')
                || document.querySelector('[data-ts-bg="' + tsKey + '"]')
                || document.querySelector('[data-ve-key="' + tsKey + '"]');
        if (el) {
            const camelProp = prop.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
            if (val === '' || val === undefined) {
                el.style.removeProperty(prop);
            } else {
                el.style[camelProp] = val;
            }
        }

        // Update styleChanges dict
        if (!styleChanges[tsKey]) styleChanges[tsKey] = {};
        if (val === '' || val === undefined) {
            delete styleChanges[tsKey][prop];
        } else {
            styleChanges[tsKey][prop] = val;
        }
        if (Object.keys(styleChanges[tsKey]).length === 0) delete styleChanges[tsKey];

        // Sync panel controls if this element is currently selected
        if (styleTargetKey === tsKey) {
            syncStylePanel(prop, val);
        }

        syncBoxModelDiagram(prop, val || '');
    }

    /**
     * Sync a single CSS control in the Style panel after undo/redo.
     * Handles: number inputs, selects, color pickers, slider-num combos, buttons.
     */
    function syncStylePanel(prop, val) {
        const container = $('#vep-style-controls');
        if (!container) return;

        // Slider+number combos (data-sn-prop is on the child inputs, find via them)
        const snInput = container.querySelector('input[data-sn-prop="' + prop + '"]');
        if (snInput) {
            const sliderNum = snInput.closest('.ve-slider-num');
            if (sliderNum) {
                const pv = parseFloat(val) || 0;
                const range = sliderNum.querySelector('input[type="range"]');
                const num = sliderNum.querySelector('input[type="number"]');
                if (range) range.value = pv;
                if (num) num.value = pv;
                return;
            }
        }

        // Number input (data-prop)
        const numInp = container.querySelector('input[type="number"][data-prop="' + prop + '"]');
        if (numInp) { numInp.value = parseFloat(val) || 0; return; }

        // Select (data-prop)
        const sel = container.querySelector('select[data-prop="' + prop + '"]');
        if (sel) { sel.value = val || ''; return; }

        // Color picker pair
        const colorInp = container.querySelector('input[type="color"][data-prop="' + prop + '"]');
        if (colorInp) {
            colorInp.value = val || '#000000';
            const textSib = colorInp.parentElement?.querySelector('input[type="text"]');
            if (textSib) textSib.value = val || '';
            return;
        }

        // Align buttons
        const alignBtns = container.querySelectorAll('.ve-align-btn[data-prop="' + prop + '"]');
        if (alignBtns.length) {
            alignBtns.forEach(b => b.classList.toggle('active', b.dataset.val === val));
            return;
        }

        // Corner inputs (border-radius-*)
        const cornerInp = container.querySelector('.ve-corner-input[data-prop="' + prop + '"]');
        if (cornerInp) { cornerInp.value = parseFloat(val) || 0; return; }

        // Opacity slider (special: stored as decimal, slider as %)
        if (prop === 'opacity') {
            const opRange = container.querySelector('input[type="range"][data-prop="opacity"]');
            const opNum = container.querySelector('.ve-opacity-val');
            const pct = Math.round((parseFloat(val) || 1) * 100);
            if (opRange) opRange.value = pct;
            if (opNum) opNum.textContent = pct + '%';
        }
    }

    /* ── Keyboard shortcuts for Undo/Redo ──────────────── */
    document.addEventListener('keydown', function(e) {
        if (!active) return;
        if (!(e.ctrlKey || e.metaKey)) return;

        if (e.key === 'z' && !e.shiftKey) {
            e.preventDefault();
            performUndo();
        } else if (e.key === 'Z' || e.key === 'y') {
            e.preventDefault();
            performRedo();
        }
    });

    /* ── Tab Switching ─────────────────────────────────── */
    function switchTab(tab) {
        $$('.vep-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
        $$('.vep-tab-pane').forEach(p => p.classList.remove('active'));
        if (tab === 'content') $('#vep-content-pane').classList.add('active');
        else $('#vep-style-pane').classList.add('active');
    }

    /* ── Image Picking via JTB Media Gallery ───────────── */
    function pickImage(key, callback) {
        if (typeof JTB !== 'undefined' && JTB.openMediaGallery) {
            JTB.openMediaGallery(function(url) {
                const [sec, fld] = key.split('.', 2);
                trackChange(key, url);
                if (!values[sec]) values[sec] = {};
                values[sec][fld] = url;
                callback(url);
            });
        } else {
            const input = document.createElement('input');
            input.type = 'file'; input.accept = 'image/*';
            input.onchange = function() {
                const file = input.files[0];
                if (!file) return;
                const fd = new FormData();
                const [sec, fld] = key.split('.', 2);
                fd.append('file', file);
                fd.append('section', sec);
                fd.append('field', fld);
                fetch(API_UPLOAD, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.ok && data.url) {
                            trackChange(key, data.url);
                            if (!values[sec]) values[sec] = {};
                            values[sec][fld] = data.url;
                            callback(data.url);
                        } else alert('Upload failed: ' + (data.error || ''));
                    })
                    .catch(e => alert('Error: ' + e.message));
            };
            input.click();
        }
    }

    function trackChange(key, val) {
        // Record old value for undo
        const oldVal = changes[key] !== undefined ? changes[key] : (originals[key] || '');
        if (val !== oldVal) {
            pushUndo({ type: 'content', key: key, oldVal: oldVal, newVal: val });
        }
        changes[key] = val;
        updateBar();
    }

    /* ── Toggle Visibility ─────────────────────────────── */
    /**
     * When a toggle field changes, apply visibility to matching page elements.
     * Maps toggle keys to their associated data-ts selectors.
     */
    function applyToggleVisibility(key, show) {
        const toggleMap = {
            'header.show_cta':    '[data-ts="header.cta_text"]',
            'header.show_search': '[data-ts-search], .fs-search-toggle, [class*="-search-toggle"]',
            'announcement.enabled': '[class*="announce"], .ts-announcement-bar',
        };
        const selector = toggleMap[key];
        if (selector) {
            document.querySelectorAll(selector).forEach(el => {
                // Skip elements inside the edit panel
                if (el.closest('#cms-ve-panel')) return;
                el.style.display = show ? '' : 'none';
            });
        }
    }

    /* ── Link Editing ──────────────────────────────────── */
    function editLink(el) {
        const key = el.getAttribute('data-ts-href');
        if (!key) return;
        $$('.cms-ve-link-popup').forEach(p => p.remove());

        const current = el.getAttribute('href') || '';
        const popup = document.createElement('div');
        popup.className = 'cms-ve-link-popup';
        popup.innerHTML = '<input type="text" value="' + esc(current) + '" placeholder="/page/slug or https://..."><button>✓</button>';

        const r = el.getBoundingClientRect();
        popup.style.left = (r.left + window.scrollX) + 'px';
        popup.style.top = (r.bottom + window.scrollY + 6) + 'px';
        document.body.appendChild(popup);

        const inp = popup.querySelector('input');
        inp.focus(); inp.select();

        function apply() {
            const v = inp.value.trim();
            if (v !== current) {
                el.setAttribute('href', v);
                if (!originals[key]) originals[key] = current;
                trackChange(key, v);
            }
            popup.remove();
        }
        popup.querySelector('button').addEventListener('click', apply);
        inp.addEventListener('keydown', e => { if (e.key === 'Enter') apply(); if (e.key === 'Escape') popup.remove(); });
        setTimeout(() => document.addEventListener('click', function h(e) {
            if (!popup.contains(e.target)) { popup.remove(); document.removeEventListener('click', h); }
        }), 100);
    }

    /* ── Content Tab: Side Panel ───────────────────────── */
    function openPanel(sectionId, clickedKey, sectionEl) {
        const schemaDef = SCHEMA[sectionId];
        if (!schemaDef || !schemaDef.fields) return;

        // Find the element to select for style editing
        const targetEl = sectionEl
            || document.querySelector('[data-ts="' + clickedKey + '"]')
            || document.querySelector('[data-ts-bg="' + clickedKey + '"]');
        if (targetEl) selectStyleTarget(targetEl, clickedKey);

        panelSection = sectionId;
        const panel = $('#cms-ve-panel');
        const title = $('#vep-title');
        const pane = $('#vep-content-pane');

        title.innerHTML = (schemaDef.icon ? schemaDef.icon + ' ' : '') + esc(schemaDef.label || prettyLabel(sectionId));
        pane.innerHTML = '';

        const sectionVals = values[sectionId] || {};
        const fields = Object.entries(schemaDef.fields);

        // Group related fields by prefix (e.g., feature1_title, feature1_desc -> feature1)
        const groups = {};
        const ungrouped = [];
        for (const [key, def] of fields) {
            const match = key.match(/^([a-z]+\d+)[_]/i);
            if (match) {
                const gKey = match[1];
                if (!groups[gKey]) groups[gKey] = [];
                groups[gKey].push([key, def]);
            } else {
                ungrouped.push([key, def]);
            }
        }

        // Build ungrouped fields first
        if (ungrouped.length > 0) {
            const gDiv = document.createElement('div');
            gDiv.className = 'vep-group';
            let gHtml = '<div class="vep-group-header" onclick="this.parentElement.classList.toggle(\'collapsed\')">';
            gHtml += '<span class="vep-group-label">General <span class="vep-group-count">' + ungrouped.length + '</span></span>';
            gHtml += '<span class="vep-group-chevron">▼</span></div>';
            gHtml += '<div class="vep-group-body">';
            for (const [key, def] of ungrouped) {
                gHtml += renderField(sectionId, key, def, sectionVals);
            }
            gHtml += '</div>';
            gDiv.innerHTML = gHtml;
            pane.appendChild(gDiv);
        }

        // Build grouped fields
        for (const [gKey, gFields] of Object.entries(groups)) {
            const gDiv = document.createElement('div');
            gDiv.className = 'vep-group';
            let gHtml = '<div class="vep-group-header" onclick="this.parentElement.classList.toggle(\'collapsed\')">';
            gHtml += '<span class="vep-group-label">' + esc(prettyLabel(gKey)) + ' <span class="vep-group-count">' + gFields.length + '</span></span>';
            gHtml += '<span class="vep-group-chevron">▼</span></div>';
            gHtml += '<div class="vep-group-body">';
            for (const [key, def] of gFields) {
                gHtml += renderField(sectionId, key, def, sectionVals);
            }
            gHtml += '</div>';
            gDiv.innerHTML = gHtml;
            pane.appendChild(gDiv);
        }

        // If no fields at all
        if (fields.length === 0) {
            pane.innerHTML = '<div class="vep-style-empty"><p>No editable fields in this section</p></div>';
        }

        // Add "Generate all content" AI button at bottom
        const genDiv = document.createElement('div');
        genDiv.innerHTML = buildGenerateSectionButton(sectionId);
        pane.appendChild(genDiv);

        wireContentPanel(pane);
        wireAiFieldButtons(pane);
        wireGenerateSectionButton();

        panel.classList.add('open');
        highlightSection(sectionId);
    }

    function renderField(sectionId, key, def, sectionVals) {
        const fullKey = sectionId + '.' + key;
        const val = changes[fullKey] !== undefined ? changes[fullKey] : (sectionVals[key] || '');
        const label = def.label || prettyLabel(key);
        const type = def.type || 'text';
        let typeLabel = type === 'textarea' ? 'text' : type;

        let html = '<div class="vep-field">';
        html += '<div class="vep-field-label"><span>' + esc(label) + '</span><span class="vep-type">' + esc(typeLabel) + '</span></div>';

        if (type === 'image') {
            html += '<div class="vep-img-preview" id="vep-img-' + esc(key) + '">';
            html += val ? '<img src="' + esc(val) + '">' : '<div class="vep-img-empty">📷 No image set<br>Click to choose</div>';
            html += '</div>';
            html += '<div class="vep-img-actions">';
            html += '<button class="vep-img-choose" data-key="' + esc(fullKey) + '" data-field="' + esc(key) + '">📁 Choose</button>';
            html += '<button class="vep-img-clear" data-key="' + esc(fullKey) + '" data-field="' + esc(key) + '">✕ Remove</button>';
            html += '</div>';
        } else if (type === 'textarea') {
            html += '<div class="ve-field-ai-row">';
            html += '<textarea data-key="' + esc(fullKey) + '" placeholder="' + esc(def.default || '') + '">' + esc(val) + '</textarea>';
            html += '<button class="ve-ai-btn" data-ai-key="' + esc(fullKey) + '" title="AI assist" type="button">✨</button>';
            html += '</div>';
        } else if (type === 'color') {
            html += '<div class="vep-color-row">';
            html += '<input type="color" data-key="' + esc(fullKey) + '" value="' + esc(val || '#000000') + '">';
            html += '<input type="text" class="vep-color-hex" data-key="' + esc(fullKey) + '" value="' + esc(val) + '" placeholder="#hex">';
            html += '</div>';
        } else if (type === 'toggle') {
            html += '<div class="vep-toggle"><input type="checkbox" data-key="' + esc(fullKey) + '"' + (val ? ' checked' : '') + '><span>' + (val ? 'Enabled' : 'Disabled') + '</span></div>';
        } else {
            html += '<div class="ve-field-ai-row">';
            html += '<input type="text" data-key="' + esc(fullKey) + '" value="' + esc(val) + '" placeholder="' + esc(def.default || '') + '">';
            html += '<button class="ve-ai-btn" data-ai-key="' + esc(fullKey) + '" title="AI assist" type="button">✨</button>';
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function wireContentPanel(container) {
        container.querySelectorAll('input[type="text"], textarea').forEach(el => {
            if (el.closest('.vep-color-row') && el.type === 'text') return;
            // Capture original value on first focus (for undo)
            el.addEventListener('focus', () => {
                const key = el.dataset.key;
                if (key && !originals[key]) {
                    const tsEl = document.querySelector('[data-ts="' + key + '"]');
                    originals[key] = tsEl ? tsEl.textContent : el.value;
                }
            }, { once: true });
            el.addEventListener('input', () => {
                const key = el.dataset.key;
                if (!key) return;
                trackChange(key, el.value);
                const tsEl = document.querySelector('[data-ts="' + key + '"]');
                if (tsEl && !tsEl.isContentEditable) tsEl.textContent = el.value;
            });
        });

        container.querySelectorAll('.vep-color-row').forEach(row => {
            const colorInp = row.querySelector('input[type="color"]');
            const textInp = row.querySelector('input[type="text"]');
            if (!colorInp || !textInp) return;
            const key = colorInp.dataset.key;
            colorInp.addEventListener('input', () => { textInp.value = colorInp.value; trackChange(key, colorInp.value); });
            textInp.addEventListener('input', () => {
                if (/^#[0-9a-f]{3,8}$/i.test(textInp.value)) colorInp.value = textInp.value;
                trackChange(key, textInp.value);
            });
        });

        container.querySelectorAll('.vep-toggle input[type="checkbox"]').forEach(el => {
            el.addEventListener('change', () => {
                trackChange(el.dataset.key, el.checked);
                el.nextElementSibling.textContent = el.checked ? 'Enabled' : 'Disabled';
                // Apply toggle visibility to page elements
                applyToggleVisibility(el.dataset.key, el.checked);
            });
        });

        container.querySelectorAll('.vep-img-choose').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                const field = btn.dataset.field;
                pickImage(key, url => {
                    const preview = document.getElementById('vep-img-' + field);
                    if (preview) preview.innerHTML = '<img src="' + esc(url) + '">';
                    const bgEl = document.querySelector('[data-ts-bg="' + key + '"]');
                    if (bgEl) {
                        bgEl.style.background = 'url(' + url + ') center/cover no-repeat';
                        const ph = bgEl.querySelector('.img-placeholder, .cms-admin-upload-zone');
                        if (ph) ph.remove();
                    }
                });
            });
        });

        container.querySelectorAll('.vep-img-clear').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                const field = btn.dataset.field;
                trackChange(key, '');
                const preview = document.getElementById('vep-img-' + field);
                if (preview) preview.innerHTML = '<div class="vep-img-empty">📷 No image set<br>Click to choose</div>';
            });
        });
    }

    function highlightSection(sectionId) {
        $$('.cms-ve-section-highlight').forEach(el => el.classList.remove('cms-ve-section-highlight'));
        const tsEl = document.querySelector('[data-ts^="' + sectionId + '."]') ||
                     document.querySelector('[data-ts-bg^="' + sectionId + '."]');
        if (tsEl) {
            const section = tsEl.closest('section') || tsEl.closest('header') || tsEl.closest('footer') || tsEl.closest('[class*="section"]') || tsEl.parentElement;
            if (section) section.classList.add('cms-ve-section-highlight');
        }
    }

    /* ══════════════════════════════════════════════════════
       STYLE TAB — CSS Controls
       ══════════════════════════════════════════════════════ */

    function selectStyleTarget(el, overrideKey) {
        // Deselect previous
        $$('.cms-ve-style-selected').forEach(e => e.classList.remove('cms-ve-style-selected'));

        const tsKey = overrideKey || el.getAttribute('data-ts') || el.getAttribute('data-ts-bg');
        if (!tsKey) return;

        // Stamp virtual key so undo/redo can find element later
        if (overrideKey && !el.getAttribute('data-ts') && !el.getAttribute('data-ts-bg')) {
            el.setAttribute('data-ve-key', overrideKey);
        }

        styleTarget = el;
        styleTargetKey = tsKey;
        el.classList.add('cms-ve-style-selected');

        // Update element name in panel header
        const elNameEl = document.getElementById('vep-el-name');
        if (elNameEl) {
            const tag = el.tagName.toLowerCase();
            elNameEl.textContent = tsKey + ' (' + tag + ')';
        }

        renderStyleControls(el, tsKey);
        // Don't switch tab here — let the caller decide
    }

    function renderStyleControls(el, tsKey) {
        const empty = $('#vep-style-empty');
        const controls = $('#vep-style-controls');
        empty.style.display = 'none';
        controls.style.display = 'block';

        const cs = getComputedStyle(el);
        const existing = styleChanges[tsKey] || {};

        // Helper to get current value (priority: styleChanges > computedStyle)
        function cur(prop, fallback) {
            if (existing[prop] !== undefined) return existing[prop];
            try { return cs.getPropertyValue(prop) || fallback || ''; } catch(e) { return fallback || ''; }
        }

        function parseNumUnit(val, defaultUnit) {
            if (!val || val === 'normal' || val === 'none' || val === 'auto') return { num: '', unit: defaultUnit || 'px' };
            const m = String(val).match(/^(-?[\d.]+)\s*(px|rem|em|%|pt|vh|vw)?$/);
            if (m) return { num: m[1], unit: m[2] || defaultUnit || 'px' };
            return { num: val, unit: defaultUnit || 'px' };
        }

        let html = '';

        // AI Style Chat — above everything
        html += buildAiStyleChat();

        // Target indicator
        html += '<div class="vep-style-target">';
        html += '<span>Element:</span><code>' + esc(tsKey) + '</code>';
        html += '<button class="vep-style-reset" onclick="cmsVE._resetStyles()" title="Reset all styles">↺ Reset</button>';
        html += '</div>';

        // ── TYPOGRAPHY GROUP ──────────────────────────────
        html += buildStyleGroup('typography', '🔤', 'Typography', function() {
            let s = '';

            // Font family — dropdown with Google Fonts + system stacks
            const curFont = cur('font-family', '').replace(/['"]/g, '').split(',')[0].trim();
            s += '<div class="ve-row"><span class="ve-label">Font</span>';
            s += '<select class="ve-font-sel ve-unit-sel" data-prop="font-family" style="flex:1;min-width:0">';
            s += '<option value="">— Inherited —</option>';
            // System stacks
            s += '<optgroup label="System">';
            const sysFonts = [
                ['system-ui, -apple-system, sans-serif', 'System UI'],
                ['Georgia, serif', 'Georgia'],
                ['Courier New, monospace', 'Courier New'],
            ];
            for (const [val, lab] of sysFonts) {
                const sel = curFont.toLowerCase() === lab.toLowerCase() ? ' selected' : '';
                s += '<option value="' + val + '" style="font-family:' + val + '"' + sel + '>' + lab + '</option>';
            }
            s += '</optgroup>';
            // Google Fonts (curated — most popular)
            s += '<optgroup label="Sans-Serif">';
            const sansArr = ['Inter','Roboto','Open Sans','Lato','Montserrat','Poppins','Nunito','Raleway','Work Sans','DM Sans','Outfit','Plus Jakarta Sans','Manrope','Sora','Space Grotesk','Figtree','Geist'];
            for (const f of sansArr) {
                const sel = curFont.toLowerCase() === f.toLowerCase() ? ' selected' : '';
                s += '<option value="\'' + f + '\', sans-serif" style="font-family:\'' + f + '\',sans-serif"' + sel + '>' + f + '</option>';
            }
            s += '</optgroup>';
            s += '<optgroup label="Serif">';
            const serifArr = ['Playfair Display','Merriweather','Lora','Cormorant Garamond','Libre Baskerville','EB Garamond','Source Serif 4','Crimson Text','Bitter','DM Serif Display','Fraunces'];
            for (const f of serifArr) {
                const sel = curFont.toLowerCase() === f.toLowerCase() ? ' selected' : '';
                s += '<option value="\'' + f + '\', serif" style="font-family:\'' + f + '\',serif"' + sel + '>' + f + '</option>';
            }
            s += '</optgroup>';
            s += '<optgroup label="Display">';
            const dispArr = ['Oswald','Bebas Neue','Anton','Righteous','Pacifico','Lobster','Abril Fatface','Archivo Black','Josefin Sans','Comfortaa'];
            for (const f of dispArr) {
                const sel = curFont.toLowerCase() === f.toLowerCase() ? ' selected' : '';
                s += '<option value="\'' + f + '\', sans-serif" style="font-family:\'' + f + '\',sans-serif"' + sel + '>' + f + '</option>';
            }
            s += '</optgroup>';
            s += '<optgroup label="Monospace">';
            const monoArr = ['Fira Code','JetBrains Mono','Source Code Pro','IBM Plex Mono','Space Mono','Inconsolata'];
            for (const f of monoArr) {
                const sel = curFont.toLowerCase() === f.toLowerCase() ? ' selected' : '';
                s += '<option value="\'' + f + '\', monospace" style="font-family:\'' + f + '\',monospace"' + sel + '>' + f + '</option>';
            }
            s += '</optgroup>';
            s += '</select></div>';

            // Font size — slider + number
            const fs = parseNumUnit(cur('font-size', '16px'), 'px');
            s += '<div class="ve-row"><span class="ve-label">Size</span>';
            s += buildSliderNum('font-size', fs.num, 8, 120, 1, fs.unit);
            s += '</div>';

            // Font weight
            const fw = cur('font-weight', '400');
            s += '<div class="ve-row"><span class="ve-label">Weight</span>';
            s += '<div class="ve-btn-group" data-prop="font-weight">';
            const weights = [['300','Light'],['400','Regular'],['500','Medium'],['600','Semi'],['700','Bold'],['900','Black']];
            for (const [w,l] of weights) {
                s += '<button data-val="' + w + '"' + (fw === w ? ' class="active"' : '') + ' title="' + l + '">' + w + '</button>';
            }
            s += '</div></div>';

            // Color
            const col = cur('color', '#000000');
            const colHex = rgbToHex(col);
            s += '<div class="ve-row"><span class="ve-label">Color</span>';
            s += '<div class="ve-color-row">';
            s += '<input type="color" data-prop="color" value="' + colHex + '">';
            s += '<input type="text" data-prop="color" value="' + colHex + '" placeholder="#hex">';
            s += '</div></div>';

            // Text align
            const ta = cur('text-align', 'left');
            s += '<div class="ve-row"><span class="ve-label">Align</span>';
            s += '<div class="ve-btn-group" data-prop="text-align">';
            s += buildAlignBtn('left', ta);
            s += buildAlignBtn('center', ta);
            s += buildAlignBtn('right', ta);
            s += buildAlignBtn('justify', ta);
            s += '</div></div>';

            // Line height — slider
            const lh = parseNumUnit(cur('line-height', 'normal'), '');
            const lhVal = lh.num && lh.num !== 'normal' ? lh.num : '1.5';
            s += '<div class="ve-row"><span class="ve-label">Line H.</span>';
            s += buildSliderNum('line-height', lhVal, 0.5, 4, 0.1, '');
            s += '</div>';

            // Letter spacing — slider
            const ls = parseNumUnit(cur('letter-spacing', '0px'), 'px');
            const lsVal = ls.num === 'normal' ? '0' : ls.num;
            s += '<div class="ve-row"><span class="ve-label">Spacing</span>';
            s += buildSliderNum('letter-spacing', lsVal, -5, 20, 0.5, 'px');
            s += '</div>';

            // Text transform
            const tt = cur('text-transform', 'none');
            s += '<div class="ve-row"><span class="ve-label">Transform</span>';
            s += '<div class="ve-btn-group" data-prop="text-transform">';
            s += '<button data-val="none"' + (tt === 'none' ? ' class="active"' : '') + '>—</button>';
            s += '<button data-val="uppercase"' + (tt === 'uppercase' ? ' class="active"' : '') + ' title="UPPERCASE">AA</button>';
            s += '<button data-val="lowercase"' + (tt === 'lowercase' ? ' class="active"' : '') + ' title="lowercase">aa</button>';
            s += '<button data-val="capitalize"' + (tt === 'capitalize' ? ' class="active"' : '') + ' title="Capitalize">Aa</button>';
            s += '</div></div>';

            // Text decoration
            const td = cur('text-decoration-line', cur('text-decoration', 'none')).split(' ')[0];
            s += '<div class="ve-row"><span class="ve-label">Decoration</span>';
            s += '<div class="ve-btn-group" data-prop="text-decoration">';
            s += '<button data-val="none"' + (td === 'none' ? ' class="active"' : '') + '>—</button>';
            s += '<button data-val="underline"' + (td === 'underline' ? ' class="active"' : '') + ' title="Underline" style="text-decoration:underline">U</button>';
            s += '<button data-val="line-through"' + (td === 'line-through' ? ' class="active"' : '') + ' title="Strikethrough" style="text-decoration:line-through">S</button>';
            s += '</div></div>';

            return s;
        });

        // ── SPACING GROUP (Box Model) ─────────────────────
        html += buildStyleGroup('spacing', '📐', 'Spacing', function() {
            let s = '';

            // Interactive box model diagram
            const mt = parseNumUnit(cur('margin-top', '0'), 'px');
            const mr = parseNumUnit(cur('margin-right', '0'), 'px');
            const mb = parseNumUnit(cur('margin-bottom', '0'), 'px');
            const ml = parseNumUnit(cur('margin-left', '0'), 'px');
            const pt2 = parseNumUnit(cur('padding-top', '0'), 'px');
            const pr = parseNumUnit(cur('padding-right', '0'), 'px');
            const pb = parseNumUnit(cur('padding-bottom', '0'), 'px');
            const pl = parseNumUnit(cur('padding-left', '0'), 'px');

            s += '<div class="ve-boxmodel">';
            s += '<div class="ve-bm-layer ve-bm-margin">';
            s += '<span class="ve-bm-label">margin</span>';
            s += '<input type="number" class="ve-bm-input bm-top" data-prop="margin-top" value="' + (mt.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-right" data-prop="margin-right" value="' + (mr.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-bottom" data-prop="margin-bottom" value="' + (mb.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-left" data-prop="margin-left" value="' + (ml.num || 0) + '">';
            s += '<div class="ve-bm-layer ve-bm-padding">';
            s += '<span class="ve-bm-label">padding</span>';
            s += '<input type="number" class="ve-bm-input bm-top" data-prop="padding-top" value="' + (pt2.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-right" data-prop="padding-right" value="' + (pr.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-bottom" data-prop="padding-bottom" value="' + (pb.num || 0) + '">';
            s += '<input type="number" class="ve-bm-input bm-left" data-prop="padding-left" value="' + (pl.num || 0) + '">';
            s += '<div class="ve-bm-layer ve-bm-element">element</div>';
            s += '</div></div></div>';

            // Margin inputs with link toggle
            s += '<div class="ve-row" style="margin-top:8px"><span class="ve-label">Margin</span>';
            s += '<button class="ve-link-toggle" data-link="margin" onclick="cmsVE._toggleLink(this)" title="Link all sides">🔗</button>';
            s += '</div>';
            s += '<div class="ve-corners" data-link-group="margin">';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↑</span>' + buildSliderNum('margin-top', mt.num || '0', -100, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">→</span>' + buildSliderNum('margin-right', mr.num || '0', -100, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↓</span>' + buildSliderNum('margin-bottom', mb.num || '0', -100, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">←</span>' + buildSliderNum('margin-left', ml.num || '0', -100, 200, 1, 'px') + '</div>';
            s += '</div>';

            // Padding inputs with link toggle
            s += '<div class="ve-row" style="margin-top:8px"><span class="ve-label">Padding</span>';
            s += '<button class="ve-link-toggle" data-link="padding" onclick="cmsVE._toggleLink(this)" title="Link all sides">🔗</button>';
            s += '</div>';
            s += '<div class="ve-corners" data-link-group="padding">';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↑</span>' + buildSliderNum('padding-top', pt2.num || '0', 0, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">→</span>' + buildSliderNum('padding-right', pr.num || '0', 0, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↓</span>' + buildSliderNum('padding-bottom', pb.num || '0', 0, 200, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">←</span>' + buildSliderNum('padding-left', pl.num || '0', 0, 200, 1, 'px') + '</div>';
            s += '</div>';

            return s;
        });

        // ── BACKGROUND GROUP ──────────────────────────────
        html += buildStyleGroup('background', '🎨', 'Background', function() {
            let s = '';
            const bg = cur('background-color', 'transparent');
            const bgHex = rgbToHex(bg);
            s += '<div class="ve-row"><span class="ve-label">Color</span>';
            s += '<div class="ve-color-row">';
            s += '<input type="color" data-prop="background-color" value="' + (bgHex || '#ffffff') + '">';
            s += '<input type="text" data-prop="background-color" value="' + bgHex + '" placeholder="transparent">';
            s += '</div></div>';

            // Background image (skip gradients — only extract url())
            const bgImg = cur('background-image', 'none');
            const bgUrl = (bgImg !== 'none' && bgImg.startsWith('url(')) ? bgImg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '') : '';
            s += '<div class="ve-row"><span class="ve-label">Image</span>';
            s += '<div class="ve-bg-img-wrap">';
            if (bgUrl) {
                s += '<div class="ve-bg-img-preview" id="ve-bg-preview"><img src="' + esc(bgUrl) + '"></div>';
            } else {
                s += '<div class="ve-bg-img-preview" id="ve-bg-preview"><span class="ve-bg-img-empty">No image</span></div>';
            }
            s += '<div class="ve-bg-img-actions">';
            s += '<button class="ve-bg-choose" data-action="choose-bg">📁 Choose</button>';
            s += '<button class="ve-bg-clear" data-action="clear-bg">✕</button>';
            s += '</div>';
            s += '</div></div>';

            // Background size
            const bgSize = cur('background-size', 'auto');
            s += '<div class="ve-row"><span class="ve-label">Size</span>';
            s += '<select class="ve-unit-sel" data-prop="background-size" style="flex:1">';
            ['cover','contain','auto','100% 100%'].forEach(v => {
                s += '<option value="' + v + '"' + (bgSize === v ? ' selected' : '') + '>' + v + '</option>';
            });
            s += '</select></div>';

            // Background position
            const bgPos = cur('background-position', 'center center');
            s += '<div class="ve-row"><span class="ve-label">Position</span>';
            s += '<select class="ve-unit-sel" data-prop="background-position" style="flex:1">';
            ['center center','top center','bottom center','left center','right center','top left','top right','bottom left','bottom right'].forEach(v => {
                s += '<option value="' + v + '"' + (bgPos === v ? ' selected' : '') + '>' + v + '</option>';
            });
            s += '</select></div>';

            return s;
        });

        // ── BORDER GROUP ──────────────────────────────────
        html += buildStyleGroup('border', '🔲', 'Border', function() {
            let s = '';

            // Border width — slider
            const bw = parseNumUnit(cur('border-top-width', '0'), 'px');
            s += '<div class="ve-row"><span class="ve-label">Width</span>';
            s += buildSliderNum('border-width', bw.num || '0', 0, 20, 1, 'px');
            s += '</div>';

            // Border style
            const bs = cur('border-top-style', 'none');
            s += '<div class="ve-row"><span class="ve-label">Style</span>';
            s += '<select class="ve-unit-sel" data-prop="border-style" style="flex:1">';
            for (const st of ['none','solid','dashed','dotted','double']) {
                s += '<option value="' + st + '"' + (bs === st ? ' selected' : '') + '>' + st + '</option>';
            }
            s += '</select></div>';

            // Border color
            const bc = cur('border-top-color', '#000000');
            const bcHex = rgbToHex(bc);
            s += '<div class="ve-row"><span class="ve-label">Color</span>';
            s += '<div class="ve-color-row">';
            s += '<input type="color" data-prop="border-color" value="' + bcHex + '">';
            s += '<input type="text" data-prop="border-color" value="' + bcHex + '" placeholder="#hex">';
            s += '</div></div>';

            // Border radius
            const brTL = parseNumUnit(cur('border-top-left-radius', '0'), 'px');
            const brTR = parseNumUnit(cur('border-top-right-radius', '0'), 'px');
            const brBR = parseNumUnit(cur('border-bottom-right-radius', '0'), 'px');
            const brBL = parseNumUnit(cur('border-bottom-left-radius', '0'), 'px');
            s += '<div class="ve-row" style="margin-top:8px"><span class="ve-label">Radius</span>';
            s += '<button class="ve-link-toggle" data-link="border-radius" onclick="cmsVE._toggleLink(this)" title="Uniform corners">🔗</button>';
            s += '</div>';
            s += '<div class="ve-corners" data-link-group="border-radius">';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↖</span>' + buildSliderNum('border-top-left-radius', brTL.num || '0', 0, 100, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↗</span>' + buildSliderNum('border-top-right-radius', brTR.num || '0', 0, 100, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↘</span>' + buildSliderNum('border-bottom-right-radius', brBR.num || '0', 0, 100, 1, 'px') + '</div>';
            s += '<div class="ve-corner-slider"><span class="ve-corner-label">↙</span>' + buildSliderNum('border-bottom-left-radius', brBL.num || '0', 0, 100, 1, 'px') + '</div>';
            s += '</div>';

            return s;
        });

        // ── EFFECTS GROUP ─────────────────────────────────
        html += buildStyleGroup('effects', '✨', 'Effects', function() {
            let s = '';

            // Box shadow
            const shadow = cur('box-shadow', 'none');
            const sp = parseShadow(shadow);
            s += '<div style="margin-bottom:6px;font-size:11px;color:var(--ve-muted);font-weight:600">Box Shadow</div>';
            s += '<div class="ve-shadow-grid">';
            s += buildShadowField('X', 'shadow-x', sp.x);
            s += buildShadowField('Y', 'shadow-y', sp.y);
            s += buildShadowField('Blur', 'shadow-blur', sp.blur);
            s += buildShadowField('Spread', 'shadow-spread', sp.spread);
            s += '</div>';
            s += '<div class="ve-row" style="margin-top:6px"><span class="ve-label">Color</span>';
            s += '<div class="ve-color-row">';
            s += '<input type="color" data-prop="shadow-color" value="' + (sp.color || '#000000') + '">';
            s += '<input type="text" data-prop="shadow-color" value="' + (sp.color || '#000000') + '" placeholder="#hex">';
            s += '</div></div>';
            s += '<div class="ve-row"><span class="ve-label">Inset</span>';
            s += '<div class="vep-toggle"><input type="checkbox" data-prop="shadow-inset"' + (sp.inset ? ' checked' : '') + '>';
            s += '<span>' + (sp.inset ? 'Inset' : 'Outset') + '</span></div></div>';

            // Opacity
            const op = Math.round(parseFloat(cur('opacity', '1')) * 100);
            s += '<div class="ve-row" style="margin-top:12px"><span class="ve-label">Opacity</span>';
            s += '<div class="ve-range-wrap">';
            s += '<input type="range" min="0" max="100" value="' + op + '" data-prop="opacity">';
            s += '<span class="ve-range-val">' + op + '%</span>';
            s += '</div></div>';

            return s;
        });

        // ── SIZE & LAYOUT GROUP ───────────────────────────
        html += buildStyleGroup('size', '📏', 'Size & Layout', function() {
            let s = '';

            // Width
            const w = cur('width', 'auto');
            const wParsed = parseNumUnit(w, 'px');
            const wIsAuto = w === 'auto' || !w;
            s += '<div class="ve-row"><span class="ve-label">Width</span>';
            if (wIsAuto) {
                s += buildSliderNum('width', '0', 0, 1200, 1, 'px');
                s += '<button class="ve-auto-btn active" data-prop-auto="width">auto</button>';
            } else {
                s += buildSliderNum('width', wParsed.num || '0', 0, 1200, 1, wParsed.unit);
                s += '<button class="ve-auto-btn" data-prop-auto="width">auto</button>';
            }
            s += '</div>';

            // Height
            const h = cur('height', 'auto');
            const hParsed = parseNumUnit(h, 'px');
            const hIsAuto = h === 'auto' || !h;
            s += '<div class="ve-row"><span class="ve-label">Height</span>';
            if (hIsAuto) {
                s += buildSliderNum('height', '0', 0, 1200, 1, 'px');
                s += '<button class="ve-auto-btn active" data-prop-auto="height">auto</button>';
            } else {
                s += buildSliderNum('height', hParsed.num || '0', 0, 1200, 1, hParsed.unit);
                s += '<button class="ve-auto-btn" data-prop-auto="height">auto</button>';
            }
            s += '</div>';

            // Display
            const disp = cur('display', 'block');
            s += '<div class="ve-row"><span class="ve-label">Display</span>';
            s += '<div class="ve-btn-group" data-prop="display">';
            for (const d of ['block','flex','inline-block','inline','grid','none']) {
                s += '<button data-val="' + d + '"' + (disp === d ? ' class="active"' : '') + ' title="' + d + '">' + d.replace('inline-block','i-blk') + '</button>';
            }
            s += '</div></div>';

            // Flex controls (shown if display is flex)
            if (disp === 'flex' || disp === 'inline-flex') {
                const fd = cur('flex-direction', 'row');
                s += '<div class="ve-row"><span class="ve-label">Direction</span>';
                s += '<div class="ve-btn-group" data-prop="flex-direction">';
                for (const v of ['row','column','row-reverse','column-reverse']) {
                    s += '<button data-val="' + v + '"' + (fd === v ? ' class="active"' : '') + '>' + v.replace('-reverse','-rev').replace('column','col') + '</button>';
                }
                s += '</div></div>';

                const jc = cur('justify-content', 'flex-start');
                s += '<div class="ve-row"><span class="ve-label">Justify</span>';
                s += '<div class="ve-btn-group" data-prop="justify-content">';
                for (const [v,l] of [['flex-start','Start'],['center','Center'],['flex-end','End'],['space-between','Between'],['space-around','Around']]) {
                    s += '<button data-val="' + v + '"' + (jc === v ? ' class="active"' : '') + ' title="' + v + '">' + l + '</button>';
                }
                s += '</div></div>';

                const ai = cur('align-items', 'stretch');
                s += '<div class="ve-row"><span class="ve-label">Align</span>';
                s += '<div class="ve-btn-group" data-prop="align-items">';
                for (const [v,l] of [['flex-start','Start'],['center','Center'],['flex-end','End'],['stretch','Stretch'],['baseline','Base']]) {
                    s += '<button data-val="' + v + '"' + (ai === v ? ' class="active"' : '') + ' title="' + v + '">' + l + '</button>';
                }
                s += '</div></div>';

                const gap = parseNumUnit(cur('gap', '0'), 'px');
                s += '<div class="ve-row"><span class="ve-label">Gap</span>';
                s += buildSliderNum('gap', gap.num || '0', 0, 100, 1, 'px');
                s += '</div>';
            }

            // Overflow
            const ov = cur('overflow', 'visible');
            s += '<div class="ve-row"><span class="ve-label">Overflow</span>';
            s += '<div class="ve-btn-group" data-prop="overflow">';
            for (const v of ['visible','hidden','scroll','auto']) {
                s += '<button data-val="' + v + '"' + (ov === v ? ' class="active"' : '') + '>' + v + '</button>';
            }
            s += '</div></div>';

            return s;
        });

        // ── TEXT SHADOW GROUP ──────────────────────────────
        html += buildStyleGroup('text-shadow', '💫', 'Text Shadow', function() {
            let s = '';
            const ts = cur('text-shadow', 'none');
            const tsp = parseShadow(ts);
            s += '<div class="ve-shadow-grid">';
            s += buildShadowField('X', 'tshadow-x', tsp.x);
            s += buildShadowField('Y', 'tshadow-y', tsp.y);
            s += buildShadowField('Blur', 'tshadow-blur', tsp.blur);
            s += '</div>';
            s += '<div class="ve-row" style="margin-top:6px"><span class="ve-label">Color</span>';
            s += '<div class="ve-color-row">';
            s += '<input type="color" data-prop="tshadow-color" value="' + (tsp.color || '#000000') + '">';
            s += '<input type="text" data-prop="tshadow-color" value="' + (tsp.color || '#000000') + '" placeholder="#hex">';
            s += '</div></div>';
            return s;
        });

        // ── FILTERS GROUP ─────────────────────────────────
        html += buildStyleGroup('filters', '🔮', 'Filters', function() {
            let s = '';
            const filterStr = cur('filter', 'none');
            function getFilter(name, def) {
                const re = name + '[(]([0-9.]+)[)]';
                const m = filterStr.match(new RegExp(re));
                return m ? parseFloat(m[1]) : def;
            }
            s += '<div class="ve-filter-grid">';
            s += buildFilterRow('Blur', 'f-blur', getFilter('blur', 0), 0, 20, 0.5, 'px');
            s += buildFilterRow('Brightness', 'f-brightness', getFilter('brightness', 1) * 100, 0, 200, 5, '%');
            s += buildFilterRow('Contrast', 'f-contrast', getFilter('contrast', 1) * 100, 0, 200, 5, '%');
            s += buildFilterRow('Saturate', 'f-saturate', getFilter('saturate', 1) * 100, 0, 200, 5, '%');
            s += buildFilterRow('Grayscale', 'f-grayscale', getFilter('grayscale', 0) * 100, 0, 100, 5, '%');
            s += buildFilterRow('Hue', 'f-hue-rotate', getFilter('hue-rotate', 0), 0, 360, 5, 'deg');
            s += '</div>';
            return s;
        });

        // ── TRANSFORM GROUP ───────────────────────────────
        html += buildStyleGroup('transform', '🔄', 'Transform', function() {
            let s = '';
            const tfStr = cur('transform', 'none');
            function getTf(name, def) {
                const re = name + '[(]([0-9.\\-]+)[)]';
                const m = tfStr.match(new RegExp(re));
                return m ? parseFloat(m[1]) : def;
            }
            s += '<div class="ve-filter-grid">';
            s += buildFilterRow('Rotate', 'tf-rotate', getTf('rotate', 0), -180, 180, 1, 'deg');
            s += buildFilterRow('Scale', 'tf-scale', getTf('scale', 1), 0.1, 3, 0.1, '×');
            s += buildFilterRow('Skew X', 'tf-skewX', getTf('skewX', 0), -45, 45, 1, 'deg');
            s += buildFilterRow('Skew Y', 'tf-skewY', getTf('skewY', 0), -45, 45, 1, 'deg');
            s += '</div>';
            return s;
        });

        controls.innerHTML = html;
        wireStyleControls(controls);
        wireAiStyleChat();
    }

    /* ── Style Control Builders ────────────────────────── */

    function buildStyleGroup(id, icon, label, contentFn) {
        let html = '<div class="vep-style-group" data-group="' + id + '">';
        html += '<div class="vep-style-group-header" onclick="this.parentElement.classList.toggle(\'collapsed\')">';
        html += '<span class="sg-icon">' + icon + '</span>';
        html += '<span>' + label + '</span>';
        html += '<span class="sg-chevron">▼</span>';
        html += '</div>';
        html += '<div class="vep-style-group-body">';
        html += contentFn();
        html += '</div></div>';
        return html;
    }

    function buildSliderNum(prop, value, min, max, step, unit) {
        const v = parseFloat(value) || 0;
        return '<div class="ve-slider-num">' +
            '<input type="range" data-sn-prop="' + prop + '" min="' + min + '" max="' + max + '" step="' + (step||1) + '" value="' + v + '">' +
            '<input type="number" class="ve-sn-num" data-sn-prop="' + prop + '" value="' + v + '" step="' + (step||1) + '" min="' + min + '" max="' + max + '">' +
            (unit ? '<span class="ve-unit-tag">' + unit + '</span>' : '') +
            '</div>';
    }

    function buildFilterRow(label, prop, value, min, max, step, unit) {
        return '<div class="ve-filter-row">' +
            '<span class="ve-filter-lbl">' + label + '</span>' +
            buildSliderNum(prop, value, min, max, step, unit) +
            '</div>';
    }

    function buildNumInput(prop, value, step, min, max) {
        return '<div class="ve-num-wrap">' +
            '<button class="ve-num-step" data-dir="-1" data-prop="' + prop + '">−</button>' +
            '<input type="number" data-prop="' + prop + '" value="' + (value || '') + '"' +
            ' step="' + (step || 1) + '"' +
            (min !== undefined ? ' min="' + min + '"' : '') +
            (max !== undefined ? ' max="' + max + '"' : '') + '>' +
            '<button class="ve-num-step" data-dir="1" data-prop="' + prop + '">+</button>' +
            '</div>';
    }

    function buildUnitSelect(prop, currentUnit, units) {
        let html = '<select class="ve-unit-sel" data-prop-unit="' + prop + '">';
        for (const u of units) {
            html += '<option value="' + u + '"' + (currentUnit === u ? ' selected' : '') + '>' + u + '</option>';
        }
        html += '</select>';
        return html;
    }

    function buildAlignBtn(value, current) {
        const icons = {
            left: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>',
            center: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="10" x2="6" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="18" y1="18" x2="6" y2="18"/></svg>',
            right: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="7" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="7" y2="18"/></svg>',
            justify: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>'
        };
        const isStart = (current === 'start' && value === 'left') || (current === '-webkit-' + value);
        const isActive = current === value || isStart;
        return '<button data-val="' + value + '"' + (isActive ? ' class="active"' : '') + ' title="' + value + '">' + icons[value] + '</button>';
    }

    function buildCornerInput(prop, value, label) {
        return '<div class="ve-corner-input">' +
            '<span class="ve-corner-label">' + label + '</span>' +
            '<input type="number" data-prop="' + prop + '" value="' + (value || '0') + '" step="1" min="0">' +
            '</div>';
    }

    function buildShadowField(label, prop, value) {
        const min = (prop.indexOf('blur') >= 0 || prop.indexOf('spread') >= 0) ? 0 : -50;
        return '<div class="ve-shadow-field">' +
            '<label>' + label + '</label>' +
            buildSliderNum(prop, value || '0', min, 50, 1, 'px') +
            '</div>';
    }

    /* ── Style Helpers ─────────────────────────────────── */

    function rgbToHex(rgb) {
        if (!rgb || rgb === 'transparent' || rgb === 'rgba(0, 0, 0, 0)') return '';
        if (rgb.startsWith('#')) return rgb.length > 7 ? rgb.slice(0, 7) : rgb;
        const m = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (!m) return rgb;
        return '#' + [m[1],m[2],m[3]].map(x => parseInt(x).toString(16).padStart(2,'0')).join('');
    }

    function parseShadow(val) {
        const def = { x: '0', y: '0', blur: '0', spread: '0', color: '#000000', inset: false };
        if (!val || val === 'none') return def;
        const inset = val.includes('inset');
        const clean = val.replace(/inset/g, '').trim();
        // Try to parse: color then numbers or numbers then color
        const colorMatch = clean.match(/(#[0-9a-f]{3,8}|rgba?\([^)]+\))/i);
        const color = colorMatch ? rgbToHex(colorMatch[1]) : '#000000';
        const noColor = clean.replace(/(#[0-9a-f]{3,8}|rgba?\([^)]+\))/gi, '').trim();
        const nums = noColor.match(/-?[\d.]+/g) || [];
        return {
            x: nums[0] || '0',
            y: nums[1] || '0',
            blur: nums[2] || '0',
            spread: nums[3] || '0',
            color: color || '#000000',
            inset: inset
        };
    }

    function buildShadowValue(container) {
        // Try slider-num (data-sn-prop) first, fall back to data-prop
        const q = p => container.querySelector('[data-sn-prop="' + p + '"][type="range"]') || container.querySelector('[data-prop="' + p + '"]');
        const gx = q('shadow-x');
        const gy = q('shadow-y');
        const gb = q('shadow-blur');
        const gs = q('shadow-spread');
        const gc = container.querySelector('input[type="color"][data-prop="shadow-color"]');
        const gi = container.querySelector('[data-prop="shadow-inset"]');
        if (!gx) return 'none';

        const x = gx.value || '0';
        const y = gy ? gy.value || '0' : '0';
        const b = gb ? gb.value || '0' : '0';
        const sp = gs ? gs.value || '0' : '0';
        const c = gc ? gc.value : '#000000';
        const inset = gi && gi.checked;

        if (x === '0' && y === '0' && b === '0' && sp === '0') return 'none';
        return (inset ? 'inset ' : '') + x + 'px ' + y + 'px ' + b + 'px ' + sp + 'px ' + c;
    }

    // ── Google Font Loader ──
    const _loadedGFonts = new Set();
    function loadGoogleFont(family) {
        // Extract clean family name (strip quotes, fallback stack)
        const clean = family.replace(/['"]/g, '').split(',')[0].trim();
        if (!clean || _loadedGFonts.has(clean)) return;
        // Skip system/generic fonts
        const skip = ['system-ui','-apple-system','sans-serif','serif','monospace','Georgia','Courier New','inherit','initial'];
        if (skip.includes(clean)) return;
        _loadedGFonts.add(clean);
        // Check if already loaded in page
        const existing = document.querySelectorAll('link[href*="fonts.googleapis.com"]');
        for (const l of existing) { if (l.href.includes(clean.replace(/ /g, '+'))) return; }
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(clean) + ':wght@300;400;500;600;700&display=swap';
        document.head.appendChild(link);
    }

    function applyStyleProp(prop, value) {
        if (!styleTarget || !styleTargetKey) return;
        // Auto-load Google Font when font-family changes
        if (prop === 'font-family' && value) loadGoogleFont(value);

        // Record old value for undo
        const oldVal = (styleChanges[styleTargetKey] && styleChanges[styleTargetKey][prop])
            || getComputedStyle(styleTarget).getPropertyValue(prop) || '';
        if (value !== oldVal) {
            pushUndo({ type: 'style', tsKey: styleTargetKey, prop: prop, oldVal: oldVal, newVal: value });
        }

        // Apply inline for preview
        const camelProp = prop.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
        styleTarget.style[camelProp] = value;

        // Track in styleChanges
        if (!styleChanges[styleTargetKey]) styleChanges[styleTargetKey] = {};
        if (value === '' || value === undefined) {
            delete styleChanges[styleTargetKey][prop];
        } else {
            styleChanges[styleTargetKey][prop] = value;
        }

        // Clean up empty
        if (Object.keys(styleChanges[styleTargetKey]).length === 0) {
            delete styleChanges[styleTargetKey];
        }

        updateBar();

        // Sync box model diagram inputs
        syncBoxModelDiagram(prop, value);
    }

    function syncBoxModelDiagram(prop, value) {
        const bm = document.querySelector('.ve-boxmodel');
        if (!bm) return;
        if (prop.startsWith('margin-') || prop.startsWith('padding-')) {
            const diagInput = bm.querySelector('.ve-bm-input[data-prop="' + prop + '"]');
            if (diagInput) {
                const pv = parseFloat(value) || 0;
                diagInput.value = pv;
            }
        }
    }

    function getUnitForProp(prop) {
        const unitSel = document.querySelector('[data-prop-unit="' + prop + '"]');
        return unitSel ? unitSel.value : 'px';
    }

    /* ── Wire Style Controls ───────────────────────────── */

    function wireStyleControls(container) {
        // Number inputs
        container.querySelectorAll('.ve-num-wrap input[type="number"]').forEach(inp => {
            inp.addEventListener('input', () => {
                const prop = inp.dataset.prop;
                const unit = getUnitForProp(prop);
                const val = inp.value !== '' ? inp.value + unit : '';
                applyStyleProp(prop, val);
            });
        });

        // Stepper buttons
        container.querySelectorAll('.ve-num-step').forEach(btn => {
            btn.addEventListener('click', () => {
                const prop = btn.dataset.prop;
                const dir = parseInt(btn.dataset.dir);
                const inp = btn.parentElement.querySelector('input[type="number"]');
                if (!inp) return;
                const step = parseFloat(inp.step) || 1;
                const val = (parseFloat(inp.value) || 0) + dir * step;
                const min = inp.min !== '' ? parseFloat(inp.min) : -Infinity;
                const max = inp.max !== '' ? parseFloat(inp.max) : Infinity;
                inp.value = Math.min(max, Math.max(min, parseFloat(val.toFixed(2))));
                inp.dispatchEvent(new Event('input'));
            });
        });

        // Unit selectors
        container.querySelectorAll('.ve-unit-sel[data-prop-unit]').forEach(sel => {
            sel.addEventListener('change', () => {
                const prop = sel.dataset.propUnit;
                const inp = container.querySelector('.ve-num-wrap input[data-prop="' + prop + '"]');
                if (inp && inp.value !== '') {
                    applyStyleProp(prop, inp.value + sel.value);
                }
            });
        });

        // Select controls (border-style, font-family, etc.)
        container.querySelectorAll('select.ve-unit-sel[data-prop]').forEach(sel => {
            sel.addEventListener('change', () => {
                applyStyleProp(sel.dataset.prop, sel.value);
            });
        });

        // Font picker — preload Google Fonts on first focus for dropdown previews
        let _fontPreloaded = false;
        container.querySelectorAll('select.ve-font-sel').forEach(sel => {
            sel.addEventListener('focus', () => {
                if (_fontPreloaded) return;
                _fontPreloaded = true;
                const families = [];
                sel.querySelectorAll('option').forEach(opt => {
                    const f = opt.value.replace(/['"]/g, '').split(',')[0].trim();
                    if (f && !['system-ui','-apple-system','Georgia','Courier New',''].includes(f)) families.push(f);
                });
                if (families.length) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://fonts.googleapis.com/css2?'
                        + families.map(f => 'family=' + encodeURIComponent(f) + ':wght@400;700').join('&')
                        + '&display=swap';
                    document.head.appendChild(link);
                }
            });
            // Also update the select's own font to match selection
            sel.addEventListener('change', () => {
                sel.style.fontFamily = sel.value || 'inherit';
            });
            // Set initial font preview on the select itself
            if (sel.value) sel.style.fontFamily = sel.value;
        });

        // Button groups
        container.querySelectorAll('.ve-btn-group').forEach(group => {
            const prop = group.dataset.prop;
            group.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => {
                    group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    applyStyleProp(prop, btn.dataset.val);
                });
            });
        });

        // Color inputs (paired: color picker + text)
        container.querySelectorAll('.ve-color-row').forEach(row => {
            const colorInp = row.querySelector('input[type="color"]');
            const textInp = row.querySelector('input[type="text"]');
            if (!colorInp || !textInp) return;
            const prop = colorInp.dataset.prop;
            colorInp.addEventListener('input', () => {
                textInp.value = colorInp.value;
                applyStyleProp(prop, colorInp.value);
            });
            textInp.addEventListener('input', () => {
                if (/^#[0-9a-f]{3,8}$/i.test(textInp.value)) {
                    colorInp.value = textInp.value;
                }
                applyStyleProp(prop, textInp.value);
            });
        });

        // Corner slider-num link sync (margin, padding, border-radius)
        container.querySelectorAll('.ve-corners .ve-slider-num').forEach(wrap => {
            const range = wrap.querySelector('input[type="range"]');
            const num = wrap.querySelector('input[type="number"]');
            if (!range) return;
            const prop = range.dataset.snProp;

            function onCornerChange() {
                const corners = wrap.closest('.ve-corners');
                if (!corners) return;
                const linkGroup = corners.dataset.linkGroup;
                const toggle = container.querySelector('.ve-link-toggle[data-link="' + linkGroup + '"]');
                if (toggle && toggle.classList.contains('active')) {
                    // Sync all siblings
                    corners.querySelectorAll('.ve-slider-num').forEach(sibWrap => {
                        if (sibWrap === wrap) return;
                        const sibRange = sibWrap.querySelector('input[type="range"]');
                        const sibNum = sibWrap.querySelector('input[type="number"]');
                        if (sibRange) { sibRange.value = range.value; }
                        if (sibNum) { sibNum.value = range.value; }
                        const sibProp = sibRange ? sibRange.dataset.snProp : '';
                        if (sibProp) applyStyleProp(sibProp, range.value + 'px');
                    });
                }
                // Sync box model diagram
                syncBoxModelDiagram(prop, range.value + 'px');
            }

            range.addEventListener('input', onCornerChange);
            if (num) num.addEventListener('input', onCornerChange);
        });

        // Box model diagram inputs
        container.querySelectorAll('.ve-bm-input').forEach(inp => {
            inp.addEventListener('input', () => {
                const prop = inp.dataset.prop;
                const val = inp.value !== '' ? inp.value + 'px' : '';
                applyStyleProp(prop, val);

                // Sync corner inputs
                const cornerInp = container.querySelector('.ve-corners input[data-prop="' + prop + '"]');
                if (cornerInp) cornerInp.value = inp.value;
            });
        });

        // Shadow inputs
        // Shadow slider-num + inset/color → compose box-shadow
        container.querySelectorAll('.ve-shadow-grid [data-sn-prop^="shadow-"], .ve-shadow-grid input, input[data-prop="shadow-color"], [data-prop="shadow-inset"]').forEach(inp => {
            const evName = inp.type === 'checkbox' ? 'change' : 'input';
            inp.addEventListener(evName, () => {
                const shadowVal = buildShadowValue(container);
                applyStyleProp('box-shadow', shadowVal);
                if (inp.dataset.prop === 'shadow-inset') {
                    const label = inp.nextElementSibling;
                    if (label) label.textContent = inp.checked ? 'Inset' : 'Outset';
                }
            });
        });

        // Shadow color paired
        const shColorPicker = container.querySelector('input[type="color"][data-prop="shadow-color"]');
        const shColorText = container.querySelector('input[type="text"][data-prop="shadow-color"]');
        if (shColorPicker && shColorText) {
            shColorPicker.addEventListener('input', () => {
                shColorText.value = shColorPicker.value;
                const shadowVal = buildShadowValue(container);
                applyStyleProp('box-shadow', shadowVal);
            });
            shColorText.addEventListener('input', () => {
                if (/^#[0-9a-f]{3,8}$/i.test(shColorText.value)) {
                    shColorPicker.value = shColorText.value;
                }
                const shadowVal = buildShadowValue(container);
                applyStyleProp('box-shadow', shadowVal);
            });
        }

        // Opacity range
        container.querySelectorAll('input[type="range"][data-prop="opacity"]').forEach(inp => {
            inp.addEventListener('input', () => {
                const pct = parseInt(inp.value);
                const valSpan = inp.nextElementSibling;
                if (valSpan) valSpan.textContent = pct + '%';
                applyStyleProp('opacity', (pct / 100).toFixed(2));
            });
        });

        // Background image choose/clear
        container.querySelectorAll('[data-action="choose-bg"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const tsKey = styleTargetKey;
                if (!tsKey) return;
                pickImage(tsKey, url => {
                    applyStyleProp('background-image', 'url(' + url + ')');
                    applyStyleProp('background-size', 'cover');
                    applyStyleProp('background-position', 'center center');
                    const pre = document.getElementById('ve-bg-preview');
                    if (pre) pre.innerHTML = '<img src="' + esc(url) + '">';
                    // Also update on page
                    if (styleTarget) {
                        styleTarget.style.backgroundImage = 'url(' + url + ')';
                        styleTarget.style.backgroundSize = 'cover';
                    }
                });
            });
        });
        container.querySelectorAll('[data-action="clear-bg"]').forEach(btn => {
            btn.addEventListener('click', () => {
                applyStyleProp('background-image', 'none');
                const pre = document.getElementById('ve-bg-preview');
                if (pre) pre.innerHTML = '<span class="ve-bg-img-empty">No image</span>';
                if (styleTarget) styleTarget.style.backgroundImage = 'none';
            });
        });

        // Slider+Number combos (synced)
        container.querySelectorAll('.ve-slider-num').forEach(wrap => {
            const range = wrap.querySelector('input[type="range"]');
            const num = wrap.querySelector('input[type="number"]');
            if (!range || !num) return;
            const prop = range.dataset.snProp;
            const unitTag = wrap.querySelector('.ve-unit-tag');
            const unit = unitTag ? unitTag.textContent : '';
            range.addEventListener('input', () => {
                num.value = range.value;
                const suffix = (unit === '×' || unit === '') ? '' : unit;
                applyStyleProp(prop, range.value + suffix);
            });
            num.addEventListener('input', () => {
                range.value = num.value;
                const suffix = (unit === '×' || unit === '') ? '' : unit;
                applyStyleProp(prop, num.value + suffix);
            });
        });

        // Auto buttons (width/height auto toggle)
        container.querySelectorAll('.ve-auto-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const prop = btn.dataset.propAuto;
                const isAuto = btn.classList.toggle('active');
                if (isAuto) {
                    applyStyleProp(prop, 'auto');
                } else {
                    const slider = container.querySelector('[data-sn-prop="' + prop + '"][type="range"]');
                    if (slider) applyStyleProp(prop, slider.value + 'px');
                }
            });
        });

        // Filter controls → compose CSS filter string
        container.querySelectorAll('.ve-filter-row [data-sn-prop^="f-"]').forEach(() => {
            // Already handled by slider-num sync above, but we need to compose filter
        });
        // Override: compose filter from all f-* sliders
        const filterSliders = container.querySelectorAll('[data-sn-prop^="f-"]');
        if (filterSliders.length > 0) {
            const composeFilter = () => {
                const blur = container.querySelector('[data-sn-prop="f-blur"][type="range"]');
                const brightness = container.querySelector('[data-sn-prop="f-brightness"][type="range"]');
                const contrast = container.querySelector('[data-sn-prop="f-contrast"][type="range"]');
                const saturate = container.querySelector('[data-sn-prop="f-saturate"][type="range"]');
                const grayscale = container.querySelector('[data-sn-prop="f-grayscale"][type="range"]');
                const hue = container.querySelector('[data-sn-prop="f-hue-rotate"][type="range"]');
                let f = '';
                if (blur && parseFloat(blur.value) > 0) f += 'blur(' + blur.value + 'px) ';
                if (brightness && parseFloat(brightness.value) !== 100) f += 'brightness(' + (brightness.value / 100) + ') ';
                if (contrast && parseFloat(contrast.value) !== 100) f += 'contrast(' + (contrast.value / 100) + ') ';
                if (saturate && parseFloat(saturate.value) !== 100) f += 'saturate(' + (saturate.value / 100) + ') ';
                if (grayscale && parseFloat(grayscale.value) > 0) f += 'grayscale(' + (grayscale.value / 100) + ') ';
                if (hue && parseFloat(hue.value) > 0) f += 'hue-rotate(' + hue.value + 'deg) ';
                applyStyleProp('filter', f.trim() || 'none');
            };
            filterSliders.forEach(inp => inp.addEventListener('input', composeFilter));
        }

        // Transform controls → compose CSS transform string
        const tfSliders = container.querySelectorAll('[data-sn-prop^="tf-"]');
        if (tfSliders.length > 0) {
            const composeTf = () => {
                const rotate = container.querySelector('[data-sn-prop="tf-rotate"][type="range"]');
                const scale = container.querySelector('[data-sn-prop="tf-scale"][type="range"]');
                const skewX = container.querySelector('[data-sn-prop="tf-skewX"][type="range"]');
                const skewY = container.querySelector('[data-sn-prop="tf-skewY"][type="range"]');
                let t = '';
                if (rotate && parseFloat(rotate.value) !== 0) t += 'rotate(' + rotate.value + 'deg) ';
                if (scale && parseFloat(scale.value) !== 1) t += 'scale(' + scale.value + ') ';
                if (skewX && parseFloat(skewX.value) !== 0) t += 'skewX(' + skewX.value + 'deg) ';
                if (skewY && parseFloat(skewY.value) !== 0) t += 'skewY(' + skewY.value + 'deg) ';
                applyStyleProp('transform', t.trim() || 'none');
            };
            tfSliders.forEach(inp => inp.addEventListener('input', composeTf));
        }

        // Text shadow controls
        container.querySelectorAll('.ve-shadow-grid [data-sn-prop^="tshadow-"], input[data-prop="tshadow-color"]').forEach(inp => {
            inp.addEventListener('input', () => {
                const q = p => container.querySelector('[data-sn-prop="' + p + '"][type="range"]') || container.querySelector('[data-prop="' + p + '"]');
                const x = q('tshadow-x');
                const y = q('tshadow-y');
                const blur = q('tshadow-blur');
                const colorP = container.querySelector('input[type="color"][data-prop="tshadow-color"]');
                const colorT = container.querySelector('input[type="text"][data-prop="tshadow-color"]');
                if (!x || !y || !blur) return;
                const c = colorP ? colorP.value : '#000000';
                const val = (x.value||0) + 'px ' + (y.value||0) + 'px ' + (blur.value||0) + 'px ' + c;
                applyStyleProp('text-shadow', val);
            });
        });
        // Text shadow color sync
        const tscPicker = container.querySelector('input[type="color"][data-prop="tshadow-color"]');
        const tscText = container.querySelector('input[type="text"][data-prop="tshadow-color"]');
        if (tscPicker && tscText) {
            tscPicker.addEventListener('input', () => { tscText.value = tscPicker.value; tscPicker.dispatchEvent(new Event('input')); });
            tscText.addEventListener('input', () => {
                if (/^#[0-9a-f]{3,8}$/i.test(tscText.value)) tscPicker.value = tscText.value;
                tscPicker.dispatchEvent(new Event('input'));
            });
        }
    }

    /* ══════════════════════════════════════════════════════
     * AI ENGINE — Content + Style AI
     * ══════════════════════════════════════════════════════ */
    const AI_API = '/api/visual-editor/ai';

    async function aiRequest(body) {
        const resp = await fetch(AI_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        });
        return resp.json();
    }

    /**
     * Get element context for AI (tag name, section, field)
     */
    function getElementContext(tsKey) {
        const el = document.querySelector('[data-ts="' + tsKey + '"]')
                || document.querySelector('[data-ts-bg="' + tsKey + '"]')
                || document.querySelector('[data-ve-key="' + tsKey + '"]');
        const parts = tsKey.split('.');
        return {
            section: parts[0] || '',
            field: parts[1] || '',
            tag: el ? el.tagName.toLowerCase() : 'div',
            text: el ? el.textContent.trim() : '',
            el: el
        };
    }

    /**
     * Build AI ✨ button for a content field
     */
    function buildAiFieldButton(fullKey) {
        const btn = document.createElement('button');
        btn.className = 've-ai-btn';
        btn.title = 'AI assist';
        btn.type = 'button';
        btn.innerHTML = '✨';
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleAiDropdown(btn, fullKey);
        });
        return btn;
    }

    /**
     * Toggle AI dropdown for a field
     */
    function toggleAiDropdown(btn, fullKey) {
        // Close existing dropdowns
        $$('.ve-ai-dropdown').forEach(d => d.remove());

        const ctx = getElementContext(fullKey);
        const dd = document.createElement('div');
        dd.className = 've-ai-dropdown';

        const items = [
            { icon: '✍️', label: 'Rewrite', mode: 'paraphrase' },
            { icon: '🔍', label: 'SEO Optimize', mode: 'seo' },
            { icon: '📢', label: 'Make punchier', mode: 'punchy' },
            { icon: '👔', label: 'Formalize', mode: 'formal' },
            { icon: '💬', label: 'Make casual', mode: 'casual' },
            { sep: true },
            { icon: '🔄', label: '3 variants', action: 'variants' },
            { icon: '📝', label: 'Generate fresh', action: 'generate' },
        ];

        items.forEach(item => {
            if (item.sep) {
                const sep = document.createElement('div');
                sep.className = 've-ai-dropdown-sep';
                dd.appendChild(sep);
                return;
            }
            const row = document.createElement('button');
            row.className = 've-ai-dropdown-item';
            row.innerHTML = '<span class="ai-icon">' + item.icon + '</span>' + esc(item.label);
            row.addEventListener('click', async () => {
                dd.remove();
                if (item.action === 'variants') {
                    await aiGenerateVariants(btn, fullKey, ctx);
                } else if (item.action === 'generate') {
                    await aiGenerateFresh(btn, fullKey, ctx);
                } else {
                    await aiRewriteField(btn, fullKey, ctx, item.mode);
                }
            });
            dd.appendChild(row);
        });

        // Position dropdown using fixed positioning to escape overflow:hidden/auto containers
        document.body.appendChild(dd);
        const btnRect = btn.getBoundingClientRect();
        // Initially position below the button, aligned right
        dd.style.top = (btnRect.bottom + 4) + 'px';
        dd.style.left = (btnRect.right - dd.offsetWidth) + 'px';
        // If dropdown goes below viewport, flip it above the button
        const ddRect = dd.getBoundingClientRect();
        if (ddRect.bottom > window.innerHeight - 8) {
            dd.style.top = (btnRect.top - ddRect.height - 4) + 'px';
        }
        // If dropdown goes off-screen left, clamp to left edge
        if (ddRect.left < 8) {
            dd.style.left = '8px';
        }

        // Close on outside click
        setTimeout(() => document.addEventListener('click', function h(e) {
            if (!dd.contains(e.target) && e.target !== btn) { dd.remove(); document.removeEventListener('click', h); }
        }), 50);
    }

    /**
     * Rewrite a field's text
     */
    async function aiRewriteField(btn, fullKey, ctx, mode) {
        if (!ctx.text) { alert('No text to rewrite'); return; }
        btn.classList.add('loading');

        try {
            const result = await aiRequest({
                action: 'rewrite',
                text: ctx.text,
                mode: mode,
                section: ctx.section,
                field: ctx.field,
            });

            if (result.ok && result.text) {
                // Apply to element
                if (ctx.el) ctx.el.textContent = result.text;
                // Track change
                if (!originals[fullKey]) originals[fullKey] = ctx.text;
                trackChange(fullKey, result.text);
                // Sync panel input
                const inp = document.querySelector('#vep-content-pane [data-key="' + fullKey + '"]');
                if (inp) inp.value = result.text;
            } else {
                alert('AI error: ' + (result.error || 'Unknown error'));
            }
        } catch (e) {
            alert('AI request failed: ' + e.message);
        } finally {
            btn.classList.remove('loading');
        }
    }

    /**
     * Generate fresh text for a field
     */
    async function aiGenerateFresh(btn, fullKey, ctx) {
        btn.classList.add('loading');
        try {
            const result = await aiRequest({
                action: 'generate',
                section: ctx.section,
                field: ctx.field,
                element_tag: ctx.tag,
                context: document.title || '',
            });

            if (result.ok && result.text) {
                if (ctx.el) ctx.el.textContent = result.text;
                if (!originals[fullKey]) originals[fullKey] = ctx.text;
                trackChange(fullKey, result.text);
                const inp = document.querySelector('#vep-content-pane [data-key="' + fullKey + '"]');
                if (inp) inp.value = result.text;
            } else {
                alert('AI error: ' + (result.error || 'Unknown error'));
            }
        } catch (e) {
            alert('AI request failed: ' + e.message);
        } finally {
            btn.classList.remove('loading');
        }
    }

    /**
     * Generate A/B variants for a field — show radio picker
     */
    async function aiGenerateVariants(btn, fullKey, ctx) {
        if (!ctx.text) { alert('No text for variants'); return; }
        btn.classList.add('loading');

        try {
            const result = await aiRequest({
                action: 'variants',
                text: ctx.text,
                section: ctx.section,
                field: ctx.field,
                count: 3,
            });

            if (result.ok && result.variants && result.variants.length) {
                showVariantsPicker(btn, fullKey, ctx, result.variants);
            } else {
                alert('AI error: ' + (result.error || 'No variants generated'));
            }
        } catch (e) {
            alert('AI request failed: ' + e.message);
        } finally {
            btn.classList.remove('loading');
        }
    }

    /**
     * Show variant picker UI below the field
     */
    function showVariantsPicker(btn, fullKey, ctx, variants) {
        // Remove existing picker
        $$('.ve-ai-variants').forEach(v => v.remove());

        const picker = document.createElement('div');
        picker.className = 've-ai-variants';
        picker.innerHTML = '<div class="ve-ai-variants-title">✨ Pick a variant</div>';

        // Add original as first option
        const allOptions = [ctx.text, ...variants];

        allOptions.forEach((text, i) => {
            const item = document.createElement('div');
            item.className = 've-ai-variant-item' + (i === 0 ? ' selected' : '');
            item.innerHTML = '<div class="ve-ai-variant-radio"></div>'
                + '<div class="ve-ai-variant-text">' + esc(text) + (i === 0 ? ' <span style="color:var(--ve-muted)">(original)</span>' : '') + '</div>';

            item.addEventListener('click', () => {
                picker.querySelectorAll('.ve-ai-variant-item').forEach(v => v.classList.remove('selected'));
                item.classList.add('selected');
                // Apply
                if (ctx.el) ctx.el.textContent = text;
                if (i === 0) {
                    // Revert to original
                    delete changes[fullKey];
                } else {
                    if (!originals[fullKey]) originals[fullKey] = ctx.text;
                    trackChange(fullKey, text);
                }
                const inp = document.querySelector('#vep-content-pane [data-key="' + fullKey + '"]');
                if (inp) inp.value = text;
                updateBar();
            });
            picker.appendChild(item);
        });

        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 've-ai-dropdown-item';
        closeBtn.style.cssText = 'justify-content:center; margin-top:6px; color:var(--ve-muted);';
        closeBtn.textContent = '✕ Close';
        closeBtn.addEventListener('click', () => picker.remove());
        picker.appendChild(closeBtn);

        // Insert after the field row
        const fieldRow = btn.closest('.ve-field-ai-row') || btn.parentElement;
        if (fieldRow && fieldRow.parentElement) {
            fieldRow.parentElement.insertBefore(picker, fieldRow.nextSibling);
        }
    }

    /**
     * Build the AI style chat bar for the Style tab
     */
    function buildAiStyleChat() {
        const tagName = styleTarget ? styleTarget.tagName.toLowerCase() : 'element';
        const tsKey = styleTargetKey || '';

        let html = '<div class="ve-ai-style-chat">';
        html += '<div class="ve-ai-style-label">✨ AI Design Assistant</div>';
        html += '<div class="ve-ai-style-input-row">';
        html += '<textarea class="ve-ai-style-input" id="ve-ai-style-prompt" placeholder="Describe what you want..." rows="1"></textarea>';
        html += '<button class="ve-ai-style-send" id="ve-ai-style-send" title="Apply with AI">';
        html += '<svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
        html += '</button>';
        html += '</div>';

        // Context-aware quick suggestions
        const suggestions = getStyleSuggestions(tagName, tsKey);
        if (suggestions.length) {
            html += '<div class="ve-ai-suggestions">';
            suggestions.forEach(s => {
                html += '<button class="ve-ai-suggestion" data-prompt="' + esc(s) + '">' + esc(s) + '</button>';
            });
            html += '</div>';
        }

        html += '<div id="ve-ai-style-result"></div>';
        html += '</div>';
        return html;
    }

    /**
     * Get contextual style suggestions based on element type
     */
    function getStyleSuggestions(tagName, tsKey) {
        const key = tsKey.toLowerCase();
        const base = [];

        // Heading-like
        if (['h1','h2','h3','h4','h5','h6'].includes(tagName)) {
            base.push('Make bolder', 'Add text shadow', 'Gradient text');
        }
        // Paragraph-like
        else if (['p','span','li'].includes(tagName)) {
            base.push('Improve readability', 'More line spacing');
        }
        // Button/link
        else if (['a','button'].includes(tagName)) {
            base.push('Rounded pill', 'Make prominent');
        }

        // Section/layout keywords
        if (key.includes('hero')) {
            base.push('Dark gradient bg', 'More padding');
        } else if (key.includes('feature') || key.includes('service')) {
            base.push('3 column grid', 'Card style with shadow');
        } else if (key.includes('about')) {
            base.push('Swap columns', 'Add more gap');
        }

        // Layout suggestions
        base.push('Reverse layout', 'More whitespace');

        return base.slice(0, 6);
    }

    /**
     * Wire the AI style chat (call after inserting HTML)
     */
    function wireAiStyleChat() {
        const input = $('#ve-ai-style-prompt');
        const sendBtn = $('#ve-ai-style-send');
        const resultDiv = $('#ve-ai-style-result');

        if (!input || !sendBtn) return;

        async function sendStyleRequest(prompt) {
            if (!prompt.trim() || !styleTarget || !styleTargetKey) return;

            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span style="animation:cmsSpin 0.6s linear infinite;display:inline-block;">✨</span>';
            resultDiv.innerHTML = '';

            try {
                // Gather current CSS of element
                const cs = getComputedStyle(styleTarget);
                const currentCss = {};
                const relevantProps = ['color','background','background-color','font-size','font-weight',
                    'padding','margin','border-radius','box-shadow','text-shadow','opacity',
                    'display','flex-direction','grid-template-columns','gap','justify-content','align-items'];
                relevantProps.forEach(p => {
                    const v = cs.getPropertyValue(p);
                    if (v && v !== 'none' && v !== 'normal' && v !== 'auto') currentCss[p] = v;
                });
                if (styleChanges[styleTargetKey]) {
                    Object.assign(currentCss, styleChanges[styleTargetKey]);
                }

                // Build page structure context
                const currentSectionOrder = sectionMap.map(s => s.id);
                const pageStructure = sectionMap.map(s => {
                    const blocks = [];
                    s.el.querySelectorAll('.ve-block-parent').forEach(bp => {
                        Array.from(bp.children).forEach(c => {
                            if (c.classList.contains('ve-block-handle')) return;
                            const cls = c.className.split(' ').find(x => x && !x.startsWith('ve-'));
                            if (cls) blocks.push(cls);
                        });
                    });
                    const layout = getComputedStyle(s.el).display;
                    return { id: s.id, blocks: blocks, layout: layout };
                });

                const result = await aiRequest({
                    action: 'style',
                    prompt: prompt,
                    ts_key: styleTargetKey,
                    element_tag: styleTarget.tagName.toLowerCase(),
                    element_css: currentCss,
                    section_order: currentSectionOrder,
                    page_structure: pageStructure,
                });

                let messages = [];

                // Apply CSS changes to selected element (batched for single undo)
                if (result.ok && result.css && Object.keys(result.css).length > 0) {
                    beginUndoBatch();
                    const props = Object.entries(result.css);
                    props.forEach(([prop, val]) => applyStyleProp(prop, val));
                    commitUndoBatch();
                    messages.push('🎨 ' + props.length + ' style' + (props.length > 1 ? 's' : '') + ': ' + props.map(([p,v]) => p).join(', '));
                }

                // Apply layout commands
                if (result.ok && result.layout) {
                    // Section reorder
                    if (result.layout.section_order && result.layout.section_order.length > 0) {
                        const newOrder = result.layout.section_order;
                        // Rearrange sectionMap to match
                        const reordered = [];
                        newOrder.forEach(id => {
                            const entry = sectionMap.find(s => s.id === id);
                            if (entry) reordered.push(entry);
                        });
                        // Add any missing sections at the end
                        sectionMap.forEach(s => {
                            if (!reordered.find(r => r.id === s.id)) reordered.push(s);
                        });
                        sectionMap.length = 0;
                        reordered.forEach(s => sectionMap.push(s));
                        applyDomOrder();
                        saveSectionOrder();
                        messages.push('📑 Sections reordered: ' + newOrder.join(' → '));
                    }

                    // Parent CSS (layout changes on container)
                    if (result.layout.parent_css && styleTarget.parentElement) {
                        const parent = styleTarget.parentElement;
                        const parentKey = getBlockStyleKey(parent, parent.parentElement, 0) || styleTargetKey + '._parent';
                        Object.entries(result.layout.parent_css).forEach(([prop, val]) => {
                            const camel = prop.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
                            parent.style[camel] = val;
                            if (!styleChanges[parentKey]) styleChanges[parentKey] = {};
                            styleChanges[parentKey][prop] = val;
                        });
                        updateBar();
                        const pProps = Object.entries(result.layout.parent_css);
                        messages.push('📐 Container layout: ' + pProps.map(([p]) => p).join(', '));
                    }

                    // Sibling order
                    if (result.layout.sibling_order && styleTarget.parentElement) {
                        const parent = styleTarget.parentElement;
                        const siblings = Array.from(parent.children).filter(c =>
                            !c.classList.contains('ve-block-handle') && !c.classList.contains('ve-drag-handle') &&
                            c.tagName !== 'SCRIPT' && c.tagName !== 'STYLE' && c.offsetHeight > 0
                        );
                        const order = result.layout.sibling_order;
                        order.forEach((origIdx, newIdx) => {
                            if (origIdx < siblings.length) {
                                siblings[origIdx].style.order = newIdx;
                            }
                        });
                        messages.push('🔀 Blocks reordered');
                        updateBar();
                    }
                }

                // Re-render style controls FIRST (rebuilds HTML including result div)
                if (styleTarget && styleTargetKey) {
                    renderStyleControls(styleTarget, styleTargetKey);
                    wireStyleControls($('#vep-style-controls'));
                }

                // Re-query result div after re-render (old one was detached)
                const freshResultDiv = $('#ve-ai-style-result');

                if (messages.length > 0) {
                    if (freshResultDiv) freshResultDiv.innerHTML = '<div class="ve-ai-style-result success">✅ Applied:<br>'
                        + messages.map(m => esc(m)).join('<br>') + '</div>';
                } else if (result.ok) {
                    if (freshResultDiv) freshResultDiv.innerHTML = '<div class="ve-ai-style-result success">✅ No changes needed</div>';
                } else {
                    if (freshResultDiv) freshResultDiv.innerHTML = '<div class="ve-ai-style-result error">❌ '
                        + esc(result.error || 'Failed to generate styles') + '</div>';
                }
            } catch (e) {
                const errDiv = $('#ve-ai-style-result');
                if (errDiv) errDiv.innerHTML = '<div class="ve-ai-style-result error">❌ ' + esc(e.message) + '</div>';
            } finally {
                // Re-query buttons after potential re-render
                const freshSend = $('#ve-ai-style-send');
                if (freshSend) {
                    freshSend.disabled = false;
                    freshSend.innerHTML = '<svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
                }
            }
        }

        sendBtn.addEventListener('click', () => sendStyleRequest(input.value));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendStyleRequest(input.value);
            }
        });

        // Quick suggestion buttons
        $$('.ve-ai-suggestion').forEach(btn => {
            btn.addEventListener('click', () => {
                input.value = btn.dataset.prompt;
                sendStyleRequest(btn.dataset.prompt);
            });
        });
    }

    /**
     * Wire AI ✨ buttons in the content panel
     */
    function wireAiFieldButtons(container) {
        container.querySelectorAll('.ve-ai-btn[data-ai-key]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const fullKey = btn.dataset.aiKey;
                toggleAiDropdown(btn, fullKey);
            });
        });
    }

    /**
     * Build "Generate all content" button for bottom of Content tab
     */
    function buildGenerateSectionButton(sectionId) {
        return '<button class="ve-ai-generate-section" id="ve-ai-gen-section" data-section="' + esc(sectionId) + '">'
            + '✨ Generate all content for this section</button>';
    }

    /**
     * Wire the "Generate all content" button
     */
    function wireGenerateSectionButton() {
        const btn = $('#ve-ai-gen-section');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const section = btn.dataset.section;
            btn.disabled = true;
            btn.textContent = '⏳ Generating...';

            try {
                // Collect current field values from the panel for context
                const panelFields = {};
                const pane = $('#vep-content-pane');
                if (pane) {
                    pane.querySelectorAll('[data-key]').forEach(inp => {
                        const key = inp.dataset.key;
                        const field = key.startsWith(section + '.') ? key.substring(section.length + 1) : key;
                        panelFields[field] = inp.value || inp.textContent || '';
                    });
                }

                const resp = await fetch('/api/theme-studio/ai/generate-content' + (THEME ? '?theme=' + Q(THEME) : ''), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        section: section,
                        context: document.title || '',
                        fields: panelFields
                    })
                });
                const result = await resp.json();

                if (result.ok && result.content) {
                    let count = 0;
                    beginUndoBatch();
                    for (const [field, val] of Object.entries(result.content)) {
                        const fullKey = section + '.' + field;
                        const el = document.querySelector('[data-ts="' + fullKey + '"]');
                        if (el) {
                            if (!originals[fullKey]) originals[fullKey] = el.textContent;
                            el.textContent = val;
                            trackChange(fullKey, val);
                            const inp = pane ? pane.querySelector('[data-key="' + fullKey + '"]') : null;
                            if (inp) inp.value = val;
                            count++;
                        }
                    }
                    commitUndoBatch();
                    btn.textContent = '✅ Generated ' + count + ' fields!';
                    setTimeout(() => { btn.textContent = '✨ Generate all content for this section'; btn.disabled = false; }, 2000);
                } else {
                    alert('AI error: ' + (result.error || 'Failed'));
                    btn.textContent = '✨ Generate all content for this section';
                    btn.disabled = false;
                }
            } catch (e) {
                alert('AI request failed: ' + e.message);
                btn.textContent = '✨ Generate all content for this section';
                btn.disabled = false;
            }
        });
    }

    /* ══════════════════════════════════════════════════════
     * DRAG & DROP — Section Reorder
     * ══════════════════════════════════════════════════════ */
    const SECTION_API = '/api/theme-studio/sections/save' + (THEME ? '?theme=' + Q(THEME) : '');
    let draggedSection = null;
    let sectionMap = [];       // [{id, el, enabled}]
    let sectionOrderChanged = false;

    /**
     * Detect sections in DOM and build sectionMap
     */
    function detectSections() {
        sectionMap = [];

        // ── Header as editable section ──
        const headerEl = document.querySelector('header, .site-header, #siteHeader');
        if (headerEl && !headerEl.closest('#cms-admin-toolbar') && !headerEl.closest('#cms-ve-panel')) {
            sectionMap.push({ id: 'header', el: headerEl, enabled: true, fixed: true });
            headerEl.dataset.veSection = 'header';
        }

        // ── Main content sections ──
        const mainEl = document.querySelector('main') || document.body;
        const sections = mainEl.querySelectorAll(':scope > section, :scope > .section');

        sections.forEach(sec => {
            if (sec.closest('#cms-admin-toolbar') || sec.closest('#cms-ve-panel')) return;

            const firstTs = sec.querySelector('[data-ts], [data-ts-bg]');
            if (!firstTs) return;
            const tsKey = firstTs.getAttribute('data-ts') || firstTs.getAttribute('data-ts-bg') || '';
            const sectionId = tsKey.split('.')[0];
            if (!sectionId) return;

            if (sectionMap.find(s => s.id === sectionId)) return;

            sectionMap.push({ id: sectionId, el: sec, enabled: true });
            sec.dataset.veSection = sectionId;
        });

        // ── Footer as editable section ──
        const footerEl = document.querySelector('footer, .site-footer');
        if (footerEl && !footerEl.closest('#cms-admin-toolbar') && !footerEl.closest('#cms-ve-panel')) {
            sectionMap.push({ id: 'footer', el: footerEl, enabled: true, fixed: true });
            footerEl.dataset.veSection = 'footer';
        }
    }

    /**
     * Add drag handles to all detected sections
     */
    function addDragHandles() {
        sectionMap.forEach((s, idx) => {
            if (s.el.querySelector('.ve-drag-handle')) return; // already added
            if (s.fixed) return; // header/footer — not draggable
            const pos = getComputedStyle(s.el).position;
            if (pos === 'static') s.el.style.position = 'relative';

            const handle = document.createElement('div');
            handle.className = 've-drag-handle';
            handle.draggable = true;
            handle.innerHTML = '<span class="ve-drag-icon">☰</span>'
                + '<span class="ve-drag-label">' + esc(prettyLabel(s.id)) + '</span>'
                + '<div class="ve-drag-arrows">'
                + '<button class="ve-drag-arrow" data-dir="up" title="Move up">↑</button>'
                + '<button class="ve-drag-arrow" data-dir="down" title="Move down">↓</button>'
                + '<button class="ve-drag-toggle' + (s.enabled ? '' : ' disabled') + '" title="Toggle visibility">👁</button>'
                + '</div>';

            // Show/hide with delay — sticky handles
            let hideTimer = null;
            function showHandle() {
                if (!active) return;
                clearTimeout(hideTimer);
                handle.classList.add('visible');
            }
            function startHide() {
                hideTimer = setTimeout(() => handle.classList.remove('visible'), 400);
            }
            s.el.addEventListener('mouseenter', showHandle);
            s.el.addEventListener('mouseleave', startHide);
            handle.addEventListener('mouseenter', showHandle);
            handle.addEventListener('mouseleave', startHide);

            // Arrow buttons
            handle.querySelectorAll('.ve-drag-arrow').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    moveSection(s.id, btn.dataset.dir);
                });
            });

            // Toggle visibility
            handle.querySelector('.ve-drag-toggle').addEventListener('click', (e) => {
                e.stopPropagation();
                toggleSection(s.id);
            });

            // Drag events on handle
            handle.addEventListener('dragstart', (e) => {
                draggedSection = s;
                s.el.classList.add('ve-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', s.id);
            });

            handle.addEventListener('dragend', () => {
                draggedSection = null;
                s.el.classList.remove('ve-dragging');
                $$('.ve-drag-over, .ve-drag-over-top').forEach(el => {
                    el.classList.remove('ve-drag-over', 've-drag-over-top');
                });
            });

            s.el.appendChild(handle);

            // Drop zone on section
            s.el.addEventListener('dragover', (e) => {
                if (!draggedSection || draggedSection.id === s.id) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const rect = s.el.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                const isAbove = e.clientY < midY;
                s.el.classList.toggle('ve-drag-over', !isAbove);
                s.el.classList.toggle('ve-drag-over-top', isAbove);
            });

            s.el.addEventListener('dragleave', () => {
                s.el.classList.remove('ve-drag-over', 've-drag-over-top');
            });

            s.el.addEventListener('drop', (e) => {
                e.preventDefault();
                s.el.classList.remove('ve-drag-over', 've-drag-over-top');
                if (!draggedSection || draggedSection.id === s.id) return;
                const rect = s.el.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                const insertBefore = e.clientY < midY;
                reorderSection(draggedSection.id, s.id, insertBefore);
            });
        });
    }

    /**
     * Move a section up or down via arrow buttons
     */
    function moveSection(sectionId, direction) {
        const idx = sectionMap.findIndex(s => s.id === sectionId);
        if (idx === -1) return;

        const newIdx = direction === 'up' ? idx - 1 : idx + 1;
        if (newIdx < 0 || newIdx >= sectionMap.length) return;

        // Swap in array
        [sectionMap[idx], sectionMap[newIdx]] = [sectionMap[newIdx], sectionMap[idx]];

        // Swap in DOM
        const parent = sectionMap[0].el.parentElement;
        const referenceEl = direction === 'up'
            ? sectionMap[idx].el  // the one that moved down
            : sectionMap[idx].el.nextElementSibling;

        parent.insertBefore(sectionMap[newIdx].el, direction === 'up' ? sectionMap[newIdx].el : referenceEl);

        // Rebuild DOM order properly
        applyDomOrder();
        saveSectionOrder();
    }

    /**
     * Reorder after drag & drop
     */
    function reorderSection(dragId, targetId, insertBefore) {
        const dragIdx = sectionMap.findIndex(s => s.id === dragId);
        const targetIdx = sectionMap.findIndex(s => s.id === targetId);
        if (dragIdx === -1 || targetIdx === -1) return;

        // Remove dragged from array
        const [dragged] = sectionMap.splice(dragIdx, 1);

        // Find new target index (may have shifted)
        const newTargetIdx = sectionMap.findIndex(s => s.id === targetId);
        const insertIdx = insertBefore ? newTargetIdx : newTargetIdx + 1;

        sectionMap.splice(insertIdx, 0, dragged);

        applyDomOrder();
        saveSectionOrder();
    }

    /**
     * Apply sectionMap order to the DOM
     */
    function applyDomOrder() {
        if (sectionMap.length < 2) return;
        const parent = sectionMap[0].el.parentElement;
        // Re-append in order (moves elements)
        sectionMap.forEach(s => parent.appendChild(s.el));
    }

    /**
     * Toggle section visibility
     */
    function toggleSection(sectionId) {
        const entry = sectionMap.find(s => s.id === sectionId);
        if (!entry) return;
        entry.enabled = !entry.enabled;
        entry.el.classList.toggle('ve-section-hidden', !entry.enabled);
        const toggleBtn = entry.el.querySelector('.ve-drag-toggle');
        if (toggleBtn) toggleBtn.classList.toggle('disabled', !entry.enabled);
        saveSectionOrder();
    }

    /**
     * Save section order + enabled state to API
     */
    async function saveSectionOrder() {
        sectionOrderChanged = true;
        const order = sectionMap.map(s => s.id);
        const enabled = {};
        sectionMap.forEach(s => { enabled[s.id] = s.enabled; });

        try {
            const resp = await fetch(SECTION_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ order: order, enabled: enabled })
            });
            const result = await resp.json();
            if (!result.ok) console.warn('Section save failed:', result.error);
        } catch (e) {
            console.warn('Section save error:', e);
        }
    }

    /* ══════════════════════════════════════════════════════
     * BLOCK DRAG & DROP — Elements within sections
     * ══════════════════════════════════════════════════════ */
    let draggedBlock = null;
    let draggedBlockParent = null;

    /**
     * Detect block containers (flex/grid parents with multiple children)
     * and make their children draggable.
     */
    function setupBlockDrag() {
        const mainEl = document.querySelector('main') || document.body;
        const allSections = mainEl.querySelectorAll('section');

        allSections.forEach(sec => {
            if (sec.closest('#cms-admin-toolbar') || sec.closest('#cms-ve-panel')) return;
            findDraggableContainers(sec);
        });

        // Also handle header/footer
        document.querySelectorAll('header, .site-header, #siteHeader, footer, .site-footer').forEach(zone => {
            findDraggableContainers(zone);
        });
    }

    /**
     * Find draggable block containers and wire their children.
     * Strategy: any element with 2+ non-trivial children where at least 2 have data-ts content.
     * Prioritizes flex/grid but also handles block-level containers.
     */
    function findDraggableContainers(root) {
        const all = root.querySelectorAll('*');
        all.forEach(el => {
            if (el.closest('#cms-admin-toolbar') || el.closest('#cms-ve-panel') || el.closest('.ve-drag-handle')) return;
            if (el._veBlockWired) return;
            if (el.tagName === 'SECTION') return; // sections handled by section drag

            // Must have at least 2 meaningful children
            const children = Array.from(el.children).filter(c => {
                if (c.tagName === 'SCRIPT' || c.tagName === 'STYLE' || c.tagName === 'BR' || c.tagName === 'HR') return false;
                if (c.classList.contains('ve-drag-handle') || c.classList.contains('ve-block-handle')) return false;
                // Skip zero-size elements but allow ones that might be animated in
                if (c.offsetWidth === 0 && c.offsetHeight === 0 && !c.hasAttribute('data-animate') && !c.hasAttribute('data-animate-left') && !c.hasAttribute('data-animate-right')) return false;
                const cPos = getComputedStyle(c).position;
                if (cPos === 'absolute' || cPos === 'fixed') return false;
                return true;
            });

            if (children.length < 2) return;

            // At least 2 children must have editable content (data-ts somewhere inside)
            let editableCount = 0;
            for (const c of children) {
                if (c.hasAttribute('data-ts') || c.hasAttribute('data-ts-bg') ||
                    c.querySelector('[data-ts], [data-ts-bg]')) {
                    editableCount++;
                }
            }
            if (editableCount < 2) return;

            // Avoid wiring tiny inline elements (links, spans, buttons) unless they are flex/grid
            const style = getComputedStyle(el);
            const display = style.display;
            const isFlexGrid = display === 'flex' || display === 'grid' || display === 'inline-flex' || display === 'inline-grid';
            if (!isFlexGrid && el.tagName !== 'DIV' && el.tagName !== 'UL' && el.tagName !== 'OL') return;

            el._veBlockWired = true;
            el.classList.add('ve-block-parent');

            wireBlockChildren(el, children);
        });
    }

    /**
     * Wire drag handles and events on block children
     */
    function wireBlockChildren(parent, children) {
        children.forEach((child, idx) => {
            if (child.querySelector('.ve-block-handle')) return;

            // Only set relative if currently static (don't override absolute/fixed)
            const childPos = getComputedStyle(child).position;
            if (childPos === 'static') child.style.position = 'relative';

            // Get a label for this block
            const label = getBlockLabel(child, idx);

            // Create handle with arrow buttons
            const handle = document.createElement('div');
            handle.className = 've-block-handle';
            handle.draggable = true;
            handle.innerHTML = '<span class="ve-bh-grip">⠿</span>'
                + '<span class="ve-bh-label">' + esc(label) + '</span>'
                + '<div class="ve-bh-arrows">'
                + '<button class="ve-bh-arrow" data-dir="left" title="Move left">←</button>'
                + '<button class="ve-bh-arrow" data-dir="up" title="Move up">↑</button>'
                + '<button class="ve-bh-arrow" data-dir="down" title="Move down">↓</button>'
                + '<button class="ve-bh-arrow" data-dir="right" title="Move right">→</button>'
                + '</div>';

            // Arrow click handlers
            handle.querySelectorAll('.ve-bh-arrow').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    moveBlock(child, parent, btn.dataset.dir);
                });
            });

            child.appendChild(handle);

            // Show/hide with delay — sticky handles
            let bhTimer = null;
            function showBh() { if (!active) return; clearTimeout(bhTimer); handle.classList.add('visible'); }
            function hideBh() { bhTimer = setTimeout(() => handle.classList.remove('visible'), 400); }
            child.addEventListener('mouseenter', showBh);
            child.addEventListener('mouseleave', hideBh);
            handle.addEventListener('mouseenter', showBh);
            handle.addEventListener('mouseleave', hideBh);

            // Drag start
            handle.addEventListener('dragstart', (e) => {
                if (!active) { e.preventDefault(); return; }
                draggedBlock = child;
                draggedBlockParent = parent;
                child.classList.add('ve-block-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', 've-block');
            });

            handle.addEventListener('dragend', () => {
                child.classList.remove('ve-block-dragging');
                draggedBlock = null;
                draggedBlockParent = null;
                $$('.ve-block-drop-before, .ve-block-drop-after').forEach(el =>
                    el.classList.remove('ve-block-drop-before', 've-block-drop-after'));
            });

            // Drop zone on each child
            child.addEventListener('dragover', (e) => {
                if (!draggedBlock || draggedBlock === child || draggedBlockParent !== parent) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const rect = child.getBoundingClientRect();
                // Determine direction: row → left/right, column → top/bottom
                const direction = getFlexDirection(parent);
                let isBefore;
                if (direction === 'row' || direction === 'row-reverse') {
                    const midX = rect.left + rect.width / 2;
                    isBefore = e.clientX < midX;
                } else {
                    const midY = rect.top + rect.height / 2;
                    isBefore = e.clientY < midY;
                }
                child.classList.toggle('ve-block-drop-before', isBefore);
                child.classList.toggle('ve-block-drop-after', !isBefore);
            });

            child.addEventListener('dragleave', () => {
                child.classList.remove('ve-block-drop-before', 've-block-drop-after');
            });

            child.addEventListener('drop', (e) => {
                e.preventDefault();
                child.classList.remove('ve-block-drop-before', 've-block-drop-after');
                if (!draggedBlock || draggedBlock === child || draggedBlockParent !== parent) return;

                const rect = child.getBoundingClientRect();
                const direction = getFlexDirection(parent);
                let insertBefore;
                if (direction === 'row' || direction === 'row-reverse') {
                    insertBefore = e.clientX < (rect.left + rect.width / 2);
                } else {
                    insertBefore = e.clientY < (rect.top + rect.height / 2);
                }

                // Move DOM element
                if (insertBefore) {
                    parent.insertBefore(draggedBlock, child);
                } else {
                    parent.insertBefore(draggedBlock, child.nextSibling);
                }

                // Save new order via CSS order property
                saveBlockOrder(parent);
            });
        });
    }

    /**
     * Move a block via arrow buttons (left/right/up/down)
     * left/up = move before previous sibling, right/down = move after next sibling
     */
    function moveBlock(block, parent, direction) {
        const siblings = Array.from(parent.children).filter(c => {
            if (c.tagName === 'SCRIPT' || c.tagName === 'STYLE' || c.tagName === 'BR' || c.tagName === 'HR') return false;
            if (c.classList.contains('ve-drag-handle') || c.classList.contains('ve-block-handle')) return false;
            if (c.offsetWidth === 0 && c.offsetHeight === 0) return false;
            const cPos = getComputedStyle(c).position;
            if (cPos === 'absolute' || cPos === 'fixed') return false;
            return true;
        });

        const idx = siblings.indexOf(block);
        if (idx === -1) return;

        const isBefore = (direction === 'left' || direction === 'up');
        const targetIdx = isBefore ? idx - 1 : idx + 1;
        if (targetIdx < 0 || targetIdx >= siblings.length) return;

        const target = siblings[targetIdx];
        if (isBefore) {
            parent.insertBefore(block, target);
        } else {
            parent.insertBefore(block, target.nextSibling);
        }

        saveBlockOrder(parent);
    }

    /**
     * Get the flex direction of a container
     */
    function getFlexDirection(el) {
        const style = getComputedStyle(el);
        if (style.display === 'grid' || style.display === 'inline-grid') return 'row'; // treat grid as row for drop logic
        return style.flexDirection || 'row';
    }

    /**
     * Get a human-readable label for a block
     */
    function getBlockLabel(block, idx) {
        // Try data-ts of block itself or first child
        const ts = block.getAttribute('data-ts') || block.getAttribute('data-ts-bg');
        if (ts) {
            const parts = ts.split('.');
            return prettyLabel(parts[parts.length - 1]);
        }

        // Try class name
        const cls = block.className.split(' ').find(c =>
            c && !c.startsWith('ve-') && !c.startsWith('cms-') && c !== 'container'
        );
        if (cls) return prettyLabel(cls);

        // Try first heading or data-ts child
        const heading = block.querySelector('h1,h2,h3,h4,h5,h6,[data-ts]');
        if (heading) {
            const text = heading.textContent.trim();
            if (text.length > 0 && text.length < 30) return text;
            if (text.length >= 30) return text.substring(0, 25) + '…';
        }

        return 'Block ' + (idx + 1);
    }

    /**
     * After reordering, save CSS order values.
     * Uses _ve_styles with a generated key for each block.
     */
    function saveBlockOrder(parent) {
        const children = Array.from(parent.children).filter(c => {
            if (c.tagName === 'SCRIPT' || c.tagName === 'STYLE') return false;
            if (c.classList.contains('ve-drag-handle') || c.classList.contains('ve-block-handle')) return false;
            if (c.offsetHeight === 0) return false;
            const cPos = getComputedStyle(c).position;
            if (cPos === 'absolute' || cPos === 'fixed') return false;
            return true;
        });

        children.forEach((child, idx) => {
            child.style.order = idx;
            // Find a key for this block for _ve_styles persistence
            const key = getBlockStyleKey(child, parent, idx);
            if (key) {
                if (!styleChanges[key]) styleChanges[key] = {};
                styleChanges[key]['order'] = String(idx);
            }
        });

        updateBar();
    }

    /**
     * Generate a style key for a block element.
     * Priority: data-ts, data-ts-bg, first child data-ts, generated key.
     */
    function getBlockStyleKey(block, parent, idx) {
        // Direct data-ts
        const ts = block.getAttribute('data-ts') || block.getAttribute('data-ts-bg');
        if (ts) return ts;

        // First child data-ts — use its section prefix + block class
        const firstTs = block.querySelector('[data-ts], [data-ts-bg]');
        if (firstTs) {
            const tsVal = firstTs.getAttribute('data-ts') || firstTs.getAttribute('data-ts-bg');
            const sectionId = tsVal.split('.')[0];
            const cls = block.className.split(' ').find(c => c && !c.startsWith('ve-') && !c.startsWith('cms-'));
            if (cls) return sectionId + '._block_' + cls;
            return sectionId + '._block_' + idx;
        }

        return null;
    }

    /* ── Setup ─────────────────────────────────────────── */
    function setup() {
        // Auto-wire rich sub-page content — generate data-ts + SCHEMA entries per section
        document.querySelectorAll('[data-page-field="content"]').forEach(function(contentEl) {
            const pageId = contentEl.getAttribute('data-page-id') || '0';
            const pageType = contentEl.hasAttribute('data-article-id') ? 'article' : 'page';
            const sections = contentEl.querySelectorAll('section');
            if (sections.length === 0) return; // not rich content

            sections.forEach(function(sec, si) {
                const sectionId = 'page_s' + si;
                // Detect section label from first heading or class
                const firstH = sec.querySelector('h1, h2, h3');
                const sectionLabel = firstH ? firstH.textContent.trim().substring(0, 40) : ('Section ' + (si + 1));
                const fields = {};
                let fi = 0;

                const editableTags = sec.querySelectorAll('h1, h2, h3, h4, h5, h6, p, li, blockquote, figcaption, a.btn, .btn, dt, dd');
                editableTags.forEach(function(te) {
                    if (te.querySelector('img, svg, video, iframe, style')) return;
                    if (te.closest('nav, [class*="-nav"], style, script')) return;
                    const txt = te.textContent.trim();
                    if (txt.length < 2) return;

                    const tag = te.tagName.toLowerCase();
                    const isH = /^h[1-6]$/.test(tag);
                    // Always use unique keys — append fi counter for all elements
                    // (prevents duplicate data-ts when multiple h3s exist in one section)
                    const fieldKey = isH ? (fi === 0 ? tag : tag + '_' + fi) : (tag + '_' + fi);
                    const tsKey = sectionId + '.' + fieldKey;

                    te.setAttribute('data-ts', tsKey);
                    fields[fieldKey] = {
                        label: isH ? (tag.toUpperCase() + ' — ' + txt.substring(0, 30)) : (tag + ' — ' + txt.substring(0, 30)),
                        type: (tag === 'p' || tag === 'blockquote' || tag === 'li') ? 'textarea' : 'text',
                        default: txt
                    };
                    fi++;
                });

                // Background images
                const bgEls = sec.querySelectorAll('[style*="background"]');
                bgEls.forEach(function(bg, bi) {
                    const style = bg.getAttribute('style') || '';
                    const urlMatch = style.match(/url\(['"]?([^'")\s]+)/);
                    if (urlMatch) {
                        const bgKey = sectionId + '.bg_' + bi;
                        bg.setAttribute('data-ts-bg', bgKey);
                        fields['bg_' + bi] = { label: 'Background Image', type: 'image', default: urlMatch[1] };
                    }
                });

                if (Object.keys(fields).length > 0) {
                    SCHEMA[sectionId] = { label: sectionLabel, icon: '📄', fields: fields, _pageId: pageId, _pageType: pageType };
                    // Store initial values
                    if (!values[sectionId]) values[sectionId] = {};
                    for (const [k, def] of Object.entries(fields)) {
                        const tsEl = sec.querySelector('[data-ts="' + sectionId + '.' + k + '"]');
                        if (tsEl) values[sectionId][k] = tsEl.textContent.trim();
                    }
                }
            });
        });

        // Block link navigation only for editable elements in edit mode
        // Allow: nav menu, admin toolbar, VE panel, footer nav links
        document.addEventListener('click', function(e) {
            if (!active) return;
            const link = e.target.closest('a[href]');
            if (!link) return;
            // Allow: admin toolbar, VE panel, header nav, footer nav
            if (link.closest('#cms-admin-toolbar') || link.closest('#cms-ve-panel')) return;
            if (link.closest('nav') || link.closest('.main-nav') || link.closest('.header-nav') || link.closest('#headerNav') || link.closest('header')) return;
            if (link.closest('.footer-links') || link.closest('.footer-nav') || link.closest('footer')) return;
            // Only block links that are inside editable sections (have data-ts ancestors or are data-ts themselves)
            if (link.hasAttribute('data-ts') || link.hasAttribute('data-ts-href') || link.closest('[data-section]') || link.closest('[data-ts]') || link.closest('[data-ts-bg]')) {
                e.preventDefault();
            }
        }, true); // capture phase to fire before other handlers

        // Text elements — inline editing + panel
        $$('[data-ts]').forEach(el => {
            if (el.closest('#cms-admin-toolbar') || el.closest('#cms-ve-panel')) return;
            const key = el.getAttribute('data-ts');
            const [sec] = key.split('.', 2);

            el.addEventListener('mouseenter', () => { if (active) showLabel(el, key, false); });
            el.addEventListener('mouseleave', () => hideLabel());
            el.addEventListener('click', function(e) {
                if (!active) return;
                e.preventDefault();
                e.stopPropagation();

                // Open side panel for section (content tab for text elements)
                openPanel(sec, key);
                switchTab('content');

                // Handle link editing
                if (el.hasAttribute('data-ts-href')) editLink(el);

                // Enable inline editing
                if (!originals[key]) originals[key] = el.textContent;
                el.contentEditable = 'true';
                el.classList.add('cms-ve-editing');
                el.focus();

                function onBlur() {
                    el.contentEditable = 'false';
                    el.classList.remove('cms-ve-editing');
                    const newVal = el.textContent.trim();
                    if (newVal !== originals[key]) {
                        trackChange(key, newVal);
                        const panelInp = document.querySelector('#vep-content-pane [data-key="' + key + '"]');
                        if (panelInp) panelInp.value = newVal;
                    } else {
                        delete changes[key];
                    }
                    updateBar();
                    el.removeEventListener('blur', onBlur);
                }
                el.addEventListener('blur', onBlur);
                el.addEventListener('keydown', function kd(ev) {
                    if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); el.blur(); }
                    if (ev.key === 'Escape') { el.textContent = originals[key]; el.blur(); }
                });
            }, true);
        });

        // Image elements — overlay + panel
        $$('[data-ts-bg]').forEach(el => {
            if (el.closest('#cms-admin-toolbar') || el.closest('#cms-ve-panel')) return;
            const key = el.getAttribute('data-ts-bg');
            const [sec] = key.split('.', 2);

            const pos = getComputedStyle(el).position;
            if (pos === 'static') el.style.position = 'relative';

            // Small corner button instead of full overlay
            const ov = document.createElement('div');
            ov.className = 'cms-ve-img-ov';
            ov.innerHTML = '<i class="fas fa-camera"></i><span>Image</span>';
            el.appendChild(ov);

            el.addEventListener('mouseenter', () => { if (active) showLabel(el, key, true); });
            el.addEventListener('mouseleave', () => hideLabel());

            // Click on bg element → open panel (Style tab with bg controls)
            el.addEventListener('click', e => {
                if (!active) return;
                // Don't intercept clicks on child data-ts elements
                if (e.target.closest('[data-ts]') && e.target.closest('[data-ts]') !== el) return;
                e.preventDefault();
                e.stopPropagation();
                openPanel(sec, key);
                // Switch to Style tab to show background controls
                switchTab('style');
            }, true);

            // Corner button also opens panel
            ov.addEventListener('click', e => {
                if (!active) return;
                e.preventDefault();
                e.stopPropagation();
                openPanel(sec, key);
                switchTab('style');
            });
        });

        // Page/Article inline editing (data-page-field elements)
        $$('[data-page-field]').forEach(el => {
            if (el.closest('#cms-admin-toolbar') || el.closest('#cms-ve-panel')) return;
            const field = el.getAttribute('data-page-field');
            const pageId = el.getAttribute('data-page-id') || '';
            const articleId = el.getAttribute('data-article-id') || '';
            const type = articleId ? 'article' : 'page';
            const id = articleId || pageId;
            const label = type + '.' + field;
            const prettyType = type.charAt(0).toUpperCase() + type.slice(1);

            el.addEventListener('mouseenter', () => { if (active) showLabel(el, label, false); });
            el.addEventListener('mouseleave', () => hideLabel());
            el.addEventListener('click', function(e) {
                if (!active) return;

                // Don't intercept clicks on auto-wired data-ts / data-ts-bg elements —
                // their own capture-phase handlers will manage them
                if (e.target.closest('[data-ts]') || e.target.closest('[data-ts-bg]') || e.target.closest('.cms-ve-img-ov')) return;

                e.preventDefault();
                e.stopPropagation();

                // Open panel with page-specific content
                const panel = $('#cms-ve-panel');
                const title = $('#vep-title');
                const pane = $('#vep-content-pane');

                title.innerHTML = '📄 ' + esc(prettyType) + ': ' + esc(field);
                pane.innerHTML = '';

                if (field === 'title') {
                    pane.innerHTML = '<div class="vep-group"><div class="vep-group-body">'
                        + '<div class="vep-field"><label class="vep-label">Title <span class="vep-type">text</span></label>'
                        + '<div style="display:flex;gap:6px;align-items:center">'
                        + '<input type="text" data-page-input="title" value="' + esc(el.textContent.trim()) + '">'
                        + '</div></div></div></div>'
                        + '<p style="color:var(--ve-subtext);font-size:12px;padding:8px 12px;">💡 Click the title on the page to edit inline, or type here.</p>';

                    // Wire panel input
                    const inp = pane.querySelector('[data-page-input="title"]');
                    if (inp) {
                        inp.addEventListener('input', () => {
                            el.textContent = inp.value;
                            if (!originals[label]) originals[label] = el.textContent;
                            if (!changes._pageChanges) changes._pageChanges = [];
                            // Remove previous entry for same field
                            changes._pageChanges = changes._pageChanges.filter(c => !(c.type === type && c.id === parseInt(id) && c.field === field));
                            changes._pageChanges.push({ type: type, id: parseInt(id), field: field, value: inp.value });
                            pushUndo({ type: 'content', key: label, oldVal: originals[label], newVal: inp.value });
                            updateBar();
                        });
                    }
                } else if (field === 'content') {
                    // Rich content — find the CLICKED section (not always the first one)
                    const clickedSection = e.target.closest('section') || el.querySelector('section');
                    if (clickedSection) {
                        const firstTs = clickedSection.querySelector('[data-ts]');
                        if (firstTs) {
                            const tsKey = firstTs.getAttribute('data-ts');
                            const [secId] = tsKey.split('.', 2);
                            openPanel(secId, tsKey, clickedSection);
                            switchTab('content');
                            return;
                        }
                    }
                }

                // Select for style editing
                selectStyleTarget(el, label);
                panel.classList.add('open');

                if (field === 'title') {
                    switchTab('content');
                } else {
                    switchTab('style');
                }

                // For title: also enable inline editing on the element
                if (field === 'title') {
                    if (!originals[label]) originals[label] = el.textContent;
                    el.contentEditable = 'true';
                    el.classList.add('cms-ve-editing');
                    el.focus();

                    function onBlur() {
                        el.contentEditable = 'false';
                        el.classList.remove('cms-ve-editing');
                        const newVal = el.textContent.trim();
                        if (newVal !== originals[label]) {
                            if (!changes._pageChanges) changes._pageChanges = [];
                            changes._pageChanges = changes._pageChanges.filter(c => !(c.type === type && c.id === parseInt(id) && c.field === field));
                            changes._pageChanges.push({ type: type, id: parseInt(id), field: field, value: newVal });
                            pushUndo({ type: 'content', key: label, oldVal: originals[label], newVal: newVal });
                            // Sync panel input
                            const panelInp = pane.querySelector('[data-page-input="title"]');
                            if (panelInp) panelInp.value = newVal;
                            updateBar();
                        }
                        el.removeEventListener('blur', onBlur);
                    }
                    el.addEventListener('blur', onBlur);
                    el.addEventListener('keydown', function kd(ev) {
                        if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); el.blur(); }
                        if (ev.key === 'Escape') { el.textContent = originals[label]; el.blur(); }
                    });
                }
            }, true);
        });

        // Section-level click: clicking empty area opens section CSS editing
        // Only on actual <section> tags to avoid catching too many elements
        $$('section, header, footer').forEach(sec => {
            if (sec.closest('#cms-admin-toolbar') || sec.closest('#cms-ve-panel')) return;
            sec.addEventListener('click', e => {
                if (!active) return;
                if (e.target.closest('[data-ts]') || e.target.closest('[data-ts-bg]') || e.target.closest('.cms-ve-img-ov')) return;
                // For header/footer, use tag name as section ID
                let sectionId;
                const tag = sec.tagName.toLowerCase();
                if (tag === 'header') {
                    sectionId = 'header';
                } else if (tag === 'footer') {
                    sectionId = 'footer';
                } else {
                    if (e.target !== sec && !e.target.closest('section') === sec) return;
                    const firstTs = sec.querySelector('[data-ts], [data-ts-bg]');
                    if (!firstTs) return;
                    const tsKey = firstTs.getAttribute('data-ts') || firstTs.getAttribute('data-ts-bg') || '';
                    [sectionId] = tsKey.split('.', 2);
                }
                if (!sectionId) return;
                const firstTs2 = sec.querySelector('[data-ts], [data-ts-bg]');
                const tsKey2 = firstTs2 ? (firstTs2.getAttribute('data-ts') || firstTs2.getAttribute('data-ts-bg') || sectionId) : sectionId;
                e.preventDefault();
                e.stopPropagation();
                openPanel(sectionId, tsKey2, sec);
            });
        });

        // Column-level click: detect grid/flex column containers
        // Find all elements whose parent is grid/flex and that contain data-ts children
        document.querySelectorAll('[data-ts], [data-ts-bg]').forEach(tsEl => {
            let col = tsEl.parentElement;
            // Walk up max 3 levels to find a column container
            for (let i = 0; i < 3 && col; i++) {
                if (col.closest('#cms-admin-toolbar') || col.closest('#cms-ve-panel')) break;
                if (col.tagName === 'SECTION' || col.tagName === 'BODY') break;
                const parentDisplay = getComputedStyle(col.parentElement || col).display;
                if (parentDisplay === 'grid' || parentDisplay === 'flex') {
                    // This col is a column in a grid/flex layout
                    if (!col._veColWired) {
                        col._veColWired = true;
                        col.addEventListener('click', function(e) {
                            if (!active) return;
                            // Don't intercept clicks on data-ts/data-ts-bg children
                            if (e.target.closest('[data-ts]') || e.target.closest('[data-ts-bg]') || e.target.closest('.cms-ve-img-ov')) return;
                            e.preventDefault();
                            e.stopPropagation();
                            // Find section from child data-ts
                            const childTs = col.querySelector('[data-ts], [data-ts-bg]');
                            if (!childTs) return;
                            const key = childTs.getAttribute('data-ts') || childTs.getAttribute('data-ts-bg') || '';
                            const [sectionId] = key.split('.', 2);
                            if (!sectionId) return;
                            // Generate a virtual key for this column
                            const colKey = sectionId + '._col_' + Array.from(col.parentElement.children).indexOf(col);
                            openPanel(sectionId, colKey, col);
                            switchTab('style');
                        });
                        // Visual hint in edit mode
                        col.addEventListener('mouseenter', () => {
                            if (active) {
                                col.style.outline = '1px dashed rgba(203,166,247,0.4)';
                                col.style.outlineOffset = '2px';
                            }
                        });
                        col.addEventListener('mouseleave', () => {
                            col.style.outline = '';
                            col.style.outlineOffset = '';
                        });
                    }
                    break;
                }
                col = col.parentElement;
            }
        });

        // Link-only elements
        $$('[data-ts-href]:not([data-ts])').forEach(el => {
            if (el.closest('#cms-admin-toolbar') || el.closest('#cms-ve-panel')) return;
            el.addEventListener('click', e => {
                if (!active) return;
                e.preventDefault(); e.stopPropagation();
                editLink(el);
            }, true);
        });

        // Drag & drop section reorder
        detectSections();
        addDragHandles();

        // Block-level drag & drop within sections
        setupBlockDrag();
    }

    /* ── Public API ────────────────────────────────────── */
    window.cmsVE = {
        toggle() {
            active = !active;
            document.body.classList.toggle('cms-ve-active', active);
            const lbl = $('#cms-ve-label');
            if (lbl) lbl.textContent = active ? '✏️ Editing' : '✏️ Edit';
            if (!active) {
                this.closePanel();
                $$('.cms-ve-link-popup').forEach(p => p.remove());
                $$('.cms-ve-style-selected').forEach(e => e.classList.remove('cms-ve-style-selected'));
                styleTarget = null;
                styleTargetKey = null;
            }
            updateBar();
        },

        closePanel() {
            $('#cms-ve-panel').classList.remove('open');
            $$('.cms-ve-section-highlight').forEach(el => el.classList.remove('cms-ve-section-highlight'));
            panelSection = null;
        },

        savePanel() {
            this.save();
        },

        async save() {
            const btn = $('#cms-ve-save');
            const panelBtn = document.querySelector('.vep-save-btn');
            if (btn) { btn.disabled = true; btn.textContent = '⏳ Saving...'; }
            if (panelBtn) { panelBtn.textContent = '⏳ Saving...'; panelBtn.disabled = true; }

            try {
                const data = {};

                // Content changes
                for (const [key, val] of Object.entries(changes)) {
                    const [sec, fld] = key.split('.', 2);
                    if (!data[sec]) data[sec] = {};
                    data[sec][fld] = val;
                }

                // Style changes -> _ve_styles section
                if (Object.keys(styleChanges).length > 0) {
                    if (!data['_ve_styles']) data['_ve_styles'] = {};
                    for (const [tsKey, props] of Object.entries(styleChanges)) {
                        if (Object.keys(props).length > 0) {
                            data['_ve_styles'][tsKey] = JSON.stringify(props);
                        }
                    }
                }

                // Auto-wired sub-page sections (page_s*) → save as page content, not theme data
                const pageContentEls = document.querySelectorAll('[data-page-field="content"]');
                const autoPageSections = Object.keys(data).filter(k => k.startsWith('page_s'));
                if (autoPageSections.length > 0 && pageContentEls.length > 0) {
                    const contentEl = pageContentEls[0];
                    const pageId = contentEl.getAttribute('data-page-id');
                    const pageType = contentEl.hasAttribute('data-article-id') ? 'article' : 'page';
                    if (pageId && pageId !== '0') {
                        if (!changes._pageChanges) changes._pageChanges = [];
                        changes._pageChanges = changes._pageChanges.filter(c => !(c.type === pageType && c.id === parseInt(pageId) && c.field === 'content'));
                        changes._pageChanges.push({ type: pageType, id: parseInt(pageId), field: 'content', value: contentEl.innerHTML });
                    }
                    // Remove from theme data
                    autoPageSections.forEach(k => delete data[k]);
                }

                // Remove _pageChanges from data (handled separately)
                const pageChanges = changes._pageChanges || [];
                delete data['_pageChanges'];

                if (Object.keys(data).length > 0) {
                    const resp = await fetch(API_SAVE, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ data: data, label: 'Visual Editor' })
                    });
                    const result = await resp.json();
                    if (!result.ok) throw new Error(result.error || 'Save failed');
                    if (result.values) Object.assign(values, result.values);
                }

                // Save page/article changes
                for (const pc of pageChanges) {
                    const resp = await fetch('/api/visual-editor/save-page', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify(pc)
                    });
                    const result = await resp.json();
                    if (!result.ok) throw new Error(result.error || 'Page save failed');
                }

                // Clear tracked changes + undo/redo stacks
                Object.keys(changes).forEach(k => delete changes[k]);
                Object.keys(originals).forEach(k => delete originals[k]);
                Object.keys(styleChanges).forEach(k => delete styleChanges[k]);
                undoStack.length = 0;
                redoStack.length = 0;
                lastAction = null;
                updateBar();

                if (btn) { btn.textContent = '✅ Saved!'; setTimeout(() => { btn.textContent = '💾 Save All'; btn.disabled = true; }, 1500); }
                if (panelBtn) { panelBtn.textContent = '✅ Saved!'; panelBtn.disabled = false; setTimeout(() => { panelBtn.textContent = '💾 Save Changes'; }, 1500); }
            } catch (err) {
                alert('Save error: ' + err.message);
                if (btn) { btn.textContent = '💾 Save All'; btn.disabled = false; }
                if (panelBtn) { panelBtn.textContent = '💾 Save Changes'; panelBtn.disabled = false; }
            }
        },

        cancel() {
            // Restore content changes
            for (const [key, orig] of Object.entries(originals)) {
                const el = document.querySelector('[data-ts="' + key + '"]');
                if (el) el.textContent = orig;
            }

            // Restore style changes (remove inline styles)
            for (const [tsKey, props] of Object.entries(styleChanges)) {
                const el = document.querySelector('[data-ts="' + tsKey + '"]')
                        || document.querySelector('[data-ts-bg="' + tsKey + '"]')
                        || document.querySelector('[data-ve-key="' + tsKey + '"]');
                if (el) {
                    for (const prop of Object.keys(props)) {
                        el.style.removeProperty(prop);
                    }
                }
            }

            Object.keys(changes).forEach(k => delete changes[k]);
            Object.keys(originals).forEach(k => delete originals[k]);
            Object.keys(styleChanges).forEach(k => delete styleChanges[k]);
            undoStack.length = 0;
            redoStack.length = 0;
            lastAction = null;
            updateBar();
            active = false;
            document.body.classList.remove('cms-ve-active');
            const lbl = $('#cms-ve-label');
            if (lbl) lbl.textContent = '✏️ Edit';
            this.closePanel();
            $$('.cms-ve-link-popup').forEach(p => p.remove());
            $$('.cms-ve-style-selected').forEach(e => e.classList.remove('cms-ve-style-selected'));
            styleTarget = null;
            styleTargetKey = null;
        },

        undo() { performUndo(); },
        redo() { performRedo(); },

        /* Internal methods exposed for onclick handlers */
        _switchTab(tab) { switchTab(tab); },

        _toggleLink(btn) {
            btn.classList.toggle('active');
        },

        _resetStyles() {
            if (!styleTarget || !styleTargetKey) return;
            // Remove all inline styles we applied
            const props = styleChanges[styleTargetKey];
            if (props) {
                for (const prop of Object.keys(props)) {
                    styleTarget.style.removeProperty(prop);
                }
            }
            delete styleChanges[styleTargetKey];
            updateBar();
            // Re-render controls with fresh computed values
            renderStyleControls(styleTarget, styleTargetKey);
            wireStyleControls($('#vep-style-controls'));
        }
    };

    /* ── Init ──────────────────────────────────────────── */
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
    else setup();
})();
</script>
<style>@keyframes cmsSpin { to { transform: rotate(360deg); } }</style>
<!-- /CMS Visual Editor v3 -->
ASSETS;
    }
}
