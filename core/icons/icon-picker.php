<?php
/**
 * Centralized Icon Picker System
 * 
 * Multi-library icon picker using inline SVG (no CDN dependencies)
 * Libraries: Lucide (line), Heroicons (solid), Emoji
 * 
 * Usage in Theme Builder:
 *   require_once CMS_ROOT . '/core/icons/icon-picker.php';
 *   tb_render_icon_picker_assets();  // In <head> or before </body>
 *   tb_render_icon_picker_modal();   // Before </body>
 * 
 * JS API:
 *   TB.openIconPicker(callback)  - Opens picker, calls callback(iconValue) on select
 *   TB.renderIcon(iconValue, size)  - Returns HTML for icon
 * 
 * Icon Value Formats:
 *   lucide:home    - Lucide line icon
 *   hero:home      - Heroicons solid icon
 *   🏠             - Emoji (no prefix)
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

/**
 * Render CSS for Icon Picker
 */
function tb_render_icon_picker_css(): string {
    return <<<'CSS'
/* Icon Picker Modal */
.tb-icon-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
}
.tb-icon-modal-overlay.active {
    opacity: 1;
    visibility: visible;
}
.tb-icon-modal {
    background: var(--tb-bg, #1e1e2e);
    border: 1px solid var(--tb-border, #45475a);
    border-radius: 12px;
    width: 700px;
    max-width: 95vw;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}
.tb-icon-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--tb-border, #45475a);
}
.tb-icon-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--tb-text, #cdd6f4);
}
.tb-icon-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: var(--tb-text-muted, #6c7086);
    font-size: 24px;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tb-icon-modal-close:hover {
    background: var(--tb-surface, #181825);
    color: var(--tb-text, #cdd6f4);
}
.tb-icon-modal-body {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 16px 20px;
}
.tb-icon-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.tb-icon-tab {
    padding: 8px 16px;
    background: var(--tb-surface, #181825);
    border: 1px solid var(--tb-border, #45475a);
    border-radius: 6px;
    color: var(--tb-text-muted, #6c7086);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.tb-icon-tab:hover {
    background: var(--tb-surface-2, #313244);
    color: var(--tb-text, #cdd6f4);
}
.tb-icon-tab.active {
    background: var(--tb-accent, #89b4fa);
    border-color: var(--tb-accent, #89b4fa);
    color: #1e1e2e;
}
.tb-icon-search {
    margin-bottom: 12px;
}
.tb-icon-search input {
    width: 100%;
    padding: 10px 14px;
    background: var(--tb-surface, #181825);
    border: 1px solid var(--tb-border, #45475a);
    border-radius: 8px;
    color: var(--tb-text, #cdd6f4);
    font-size: 14px;
}
.tb-icon-search input:focus {
    outline: none;
    border-color: var(--tb-accent, #89b4fa);
}
.tb-icon-categories {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.tb-icon-category {
    padding: 4px 10px;
    background: var(--tb-surface, #181825);
    border: 1px solid var(--tb-border, #45475a);
    border-radius: 4px;
    color: var(--tb-text-muted, #6c7086);
    font-size: 12px;
    cursor: pointer;
}
.tb-icon-category:hover,
.tb-icon-category.active {
    background: var(--tb-accent, #89b4fa);
    border-color: var(--tb-accent, #89b4fa);
    color: #1e1e2e;
}
.tb-icon-grid {
    flex: 1;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
    gap: 6px;
    align-content: start;
    padding: 4px;
}
.tb-icon-option {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--tb-surface, #181825);
    border: 1px solid var(--tb-border, #45475a);
    border-radius: 6px;
    cursor: pointer;
    color: var(--tb-text, #cdd6f4);
    transition: all 0.15s;
}
.tb-icon-option:hover {
    background: var(--tb-accent, #89b4fa);
    border-color: var(--tb-accent, #89b4fa);
    color: #1e1e2e;
    transform: scale(1.1);
}
.tb-icon-option svg {
    width: 20px;
    height: 20px;
}
.tb-icon-option.lucide svg {
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
}
.tb-icon-option.heroicons svg {
    fill: currentColor;
}
.tb-icon-option.emoji {
    font-size: 20px;
}
CSS;
}

/**
 * Render Icon Picker Modal HTML
 */
function tb_render_icon_picker_modal(): void {
    echo <<<'HTML'
<!-- Centralized Icon Picker Modal -->
<div class="tb-icon-modal-overlay" id="tbIconPickerModal">
    <div class="tb-icon-modal">
        <div class="tb-icon-modal-header">
            <h3>🎨 Select Icon</h3>
            <button class="tb-icon-modal-close" onclick="TB.closeIconPicker()">×</button>
        </div>
        <div class="tb-icon-modal-body">
            <div class="tb-icon-tabs">
                <button class="tb-icon-tab active" onclick="TB.switchIconStyle('lucide')">Lucide</button>
                <button class="tb-icon-tab" onclick="TB.switchIconStyle('heroicons')">Solid</button>
                <button class="tb-icon-tab" onclick="TB.switchIconStyle('emoji')">Emoji</button>
            </div>
            <div class="tb-icon-search">
                <input type="text" id="tbIconSearchInput" placeholder="Search icons..." oninput="TB.filterIcons(this.value)">
            </div>
            <div class="tb-icon-categories" id="tbIconCategories"></div>
            <div class="tb-icon-grid" id="tbIconGrid"></div>
        </div>
    </div>
</div>
HTML;
}

/**
 * Render Icon Picker Assets (CSS + JS)
 */
function tb_render_icon_picker_assets(): void {
    echo '<style>' . tb_render_icon_picker_css() . '</style>';
}
