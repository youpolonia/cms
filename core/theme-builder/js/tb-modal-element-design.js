/**
 * Theme Builder 3.0 - Comprehensive Element Design System
 * Provides ALL design options for inner elements (not just wrapper)
 *
 * Design Categories:
 * 1. Typography - Font family, size, weight, line-height, letter-spacing, text-transform
 * 2. Spacing - Padding & Margin with link buttons
 * 3. Background - Color, gradient, image
 * 4. Border - Width (4 sides), style, color, radius (4 corners)
 * 5. Shadow - Box shadow with all controls
 * 6. Hover Effects - Colors, transforms, transitions
 * 7. Transform - Scale, rotate, skew, translate
 * 8. Filters - Blur, brightness, contrast, saturation, grayscale
 * 9. Animation - Type, duration, delay, easing
 * 10. Position - Z-index, position type
 * 11. Visibility - Show/hide per device (desktop, tablet, mobile)
 */

// ═══════════════════════════════════════════════════════════════════════════
// INJECT ELEMENT DESIGN CSS
// ═══════════════════════════════════════════════════════════════════════════
(function() {
    if (document.getElementById('tb-element-design-css')) return;
    const css = `
    /* Element Info Banner */
    .tb-element-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .tb-element-info strong { background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; text-transform: capitalize; }

    /* Visibility Devices */
    .tb-modal-visibility-devices { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    .tb-modal-visibility-device { background: var(--tb-bg-secondary, #f1f5f9); border-radius: 8px; padding: 16px 12px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; transition: all 0.2s ease; }
    .tb-modal-visibility-device:hover { background: var(--tb-bg-tertiary, #e2e8f0); }
    .tb-modal-device-icon { font-size: 24px; line-height: 1; }
    .tb-modal-device-label { font-size: 12px; font-weight: 600; color: var(--tb-text-primary, #1e293b); text-transform: uppercase; letter-spacing: 0.5px; }
    .tb-modal-device-status { font-size: 10px; color: var(--tb-text-muted, #64748b); background: rgba(0,0,0,0.05); padding: 2px 8px; border-radius: 10px; }

    /* Position Grid */
    .tb-modal-position-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px; }
    .tb-modal-position-item { display: flex; flex-direction: column; gap: 4px; }
    .tb-modal-position-item label { font-size: 11px; color: var(--tb-text-muted, #64748b); text-transform: uppercase; letter-spacing: 0.5px; }

    /* Preset Grid */
    .tb-modal-preset-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 8px; }
    .tb-modal-preset-btn { background: var(--tb-bg-secondary, #f1f5f9); border: 1px solid var(--tb-border, #e2e8f0); border-radius: 6px; padding: 8px 12px; font-size: 12px; font-weight: 500; color: var(--tb-text-primary, #1e293b); cursor: pointer; transition: all 0.15s ease; }
    .tb-modal-preset-btn:hover { background: var(--tb-primary, #3b82f6); color: #fff; border-color: var(--tb-primary, #3b82f6); }
    .tb-modal-preset-btn.active { background: var(--tb-primary, #3b82f6); color: #fff; border-color: var(--tb-primary, #3b82f6); }

    /* Two Column Layout */
    .tb-modal-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .tb-modal-two-col .tb-modal-control-row { margin-bottom: 0; }

    /* Z-Index Presets */
    .tb-modal-zindex-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .tb-modal-zindex-presets .tb-modal-preset-btn { padding: 4px 10px; font-size: 11px; min-width: 40px; }

    /* Element Spacing Link Buttons */
    .tb-element-spacing .tb-modal-spacing-link-btn { position: absolute; width: 24px; height: 24px; border-radius: 50%; background: var(--tb-bg-secondary, #f1f5f9); border: 1px solid var(--tb-border, #e2e8f0); cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease; z-index: 10; }
    .tb-element-spacing .tb-modal-spacing-link-btn:hover, .tb-element-spacing .tb-modal-spacing-link-btn.linked { background: var(--tb-primary, #3b82f6); border-color: var(--tb-primary, #3b82f6); color: #fff; }

    /* Border Link Buttons */
    .tb-element-border, .tb-element-radius { position: relative; }
    .tb-modal-border-link-btn, .tb-modal-radius-link-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 24px; height: 24px; border-radius: 50%; background: var(--tb-bg-secondary, #f1f5f9); border: 1px solid var(--tb-border, #e2e8f0); cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease; z-index: 10; }
    .tb-modal-border-link-btn:hover, .tb-modal-radius-link-btn:hover, .tb-modal-border-link-btn.linked, .tb-modal-radius-link-btn.linked { background: var(--tb-primary, #3b82f6); border-color: var(--tb-primary, #3b82f6); color: #fff; }

    /* Preview & Reset Buttons */
    .tb-modal-preview-btn { width: 100%; padding: 10px 16px; margin-top: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .tb-modal-preview-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .tb-modal-reset-btn { width: 100%; padding: 8px 16px; margin-top: 12px; background: var(--tb-bg-secondary, #f1f5f9); color: var(--tb-text-secondary, #475569); border: 1px solid var(--tb-border, #e2e8f0); border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.15s ease; }
    .tb-modal-reset-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

    /* Info Box Small */
    .tb-modal-info-small { padding: 8px 12px; font-size: 11px; background: var(--tb-bg-secondary, #f1f5f9); border-radius: 6px; display: flex; align-items: center; gap: 8px; }
    .tb-modal-info-small .tb-modal-info-icon { font-size: 14px; }

    /* Element Design Button Groups */
    .tb-element-design .tb-modal-btn-group { display: flex; gap: 4px; }
    .tb-element-design .tb-modal-btn-opt { flex: 1; padding: 8px 12px; background: var(--tb-bg-secondary, #f1f5f9); border: 1px solid var(--tb-border, #e2e8f0); border-radius: 4px; font-size: 14px; cursor: pointer; transition: all 0.15s ease; }
    .tb-element-design .tb-modal-btn-opt:hover { background: var(--tb-bg-tertiary, #e2e8f0); }
    .tb-element-design .tb-modal-btn-opt.active { background: var(--tb-primary, #3b82f6); color: #fff; border-color: var(--tb-primary, #3b82f6); }

    /* Color Picker Enhancement */
    .tb-element-design .tb-modal-color-picker { display: flex; gap: 8px; align-items: center; }
    .tb-element-design .tb-modal-color-picker input[type="color"] { width: 40px; height: 36px; border: 1px solid var(--tb-border, #e2e8f0); border-radius: 6px; padding: 2px; cursor: pointer; }
    .tb-element-design .tb-modal-color-text { flex: 1; min-width: 100px; }

    /* Animation Keyframes */
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes zoomIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
    @keyframes slideInUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    @keyframes slideInDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
    @keyframes bounceIn { 0% { opacity: 0; transform: scale(0.3); } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); } 20%, 40%, 60%, 80% { transform: translateX(5px); } }
    @keyframes flipInX { from { opacity: 0; transform: perspective(400px) rotateX(90deg); } to { opacity: 1; transform: perspective(400px) rotateX(0); } }
    @keyframes flipInY { from { opacity: 0; transform: perspective(400px) rotateY(90deg); } to { opacity: 1; transform: perspective(400px) rotateY(0); } }
    @keyframes rotateIn { from { opacity: 0; transform: rotate(-200deg); } to { opacity: 1; transform: rotate(0); } }
    @keyframes heartBeat { 0% { transform: scale(1); } 14% { transform: scale(1.3); } 28% { transform: scale(1); } 42% { transform: scale(1.3); } 70% { transform: scale(1); } }
    @keyframes wobble { 0% { transform: translateX(0); } 15% { transform: translateX(-25%) rotate(-5deg); } 30% { transform: translateX(20%) rotate(3deg); } 45% { transform: translateX(-15%) rotate(-3deg); } 60% { transform: translateX(10%) rotate(2deg); } 75% { transform: translateX(-5%) rotate(-1deg); } 100% { transform: translateX(0); } }

    /* Responsive */
    @media (max-width: 768px) { .tb-modal-visibility-devices { grid-template-columns: 1fr; } .tb-modal-preset-grid { grid-template-columns: repeat(2, 1fr); } .tb-modal-two-col { grid-template-columns: 1fr; } }
    `;
    const style = document.createElement('style');
    style.id = 'tb-element-design-css';
    style.textContent = css;
    document.head.appendChild(style);
})();

// ═══════════════════════════════════════════════════════════════════════════
// ELEMENT SETTINGS HELPER - Gets settings for current element/state
// ═══════════════════════════════════════════════════════════════════════════
// FIX: Wrapper now reads from mod.design directly (unified with sidebar)
// ═══════════════════════════════════════════════════════════════════════════
TB.getElementSettings = function(elementKey, state) {
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return {};

    // ═══════════════════════════════════════════════════════════════════════
    // WRAPPER: Read from mod.design directly (same as sidebar)
    // ═══════════════════════════════════════════════════════════════════════
    if (elementKey === 'wrapper') {
        const design = mod.design || {};
        const settings = mod.settings || {};

        if (state === 'normal') {
            // Normal state: merge design and settings, design takes precedence
            return { ...settings, ...design };
        } else if (state === 'hover') {
            // Hover state: extract _hover suffixed properties
            const hoverSettings = {};
            for (const [key, value] of Object.entries(design)) {
                if (key.endsWith('_hover')) {
                    // Remove the _hover suffix to get the base property name
                    const baseKey = key.replace(/_hover$/, '');
                    hoverSettings[baseKey] = value;
                }
            }
            // Also check settings for backward compatibility
            for (const [key, value] of Object.entries(settings)) {
                if (key.endsWith('_hover')) {
                    const baseKey = key.replace(/_hover$/, '');
                    if (!(baseKey in hoverSettings)) {
                        hoverSettings[baseKey] = value;
                    }
                }
            }
            return hoverSettings;
        }
        return { ...settings, ...design };
    }

    // ═══════════════════════════════════════════════════════════════════════
    // INNER ELEMENTS: Read from mod.design.elements[elementKey][state]
    // ═══════════════════════════════════════════════════════════════════════
    return mod.design?.elements?.[elementKey]?.[state] || {};
};

// ═══════════════════════════════════════════════════════════════════════════
// ELEMENT UPDATE FUNCTION - Updates element setting with proper path
// ═══════════════════════════════════════════════════════════════════════════
// FIX: Wrapper settings now save to mod.design directly (like sidebar)
// Inner elements still save to mod.design.elements[element][state]
// This unifies Modal and Sidebar data paths!
// ═══════════════════════════════════════════════════════════════════════════
TB.updateModalElementSetting = function(key, value) {
    const { sIdx, rIdx, cIdx, mIdx, currentElement, currentState } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    // ═══════════════════════════════════════════════════════════════════════
    // WRAPPER: Save directly to mod.design (same as sidebar)
    // This ensures Modal and Sidebar use the same data paths!
    // ═══════════════════════════════════════════════════════════════════════
    if (currentElement === 'wrapper') {
        if (!mod.design) mod.design = {};

        if (currentState === 'normal') {
            // Normal state: save directly to mod.design
            if (value === '' || value === null || value === undefined) {
                delete mod.design[key];
            } else {
                mod.design[key] = value;
            }
        } else if (currentState === 'hover') {
            // Hover state: save to mod.design with _hover suffix (matches sidebar)
            const hoverKey = key + '_hover';
            if (value === '' || value === null || value === undefined) {
                delete mod.design[hoverKey];
            } else {
                mod.design[hoverKey] = value;
            }
        }

        this.updateModalPreview();
        this.saveToHistory();
        this.renderCanvas();
        return;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // INNER ELEMENTS: Save to mod.design.elements[element][state]
    // ═══════════════════════════════════════════════════════════════════════
    if (!mod.design) mod.design = {};
    if (!mod.design.elements) mod.design.elements = {};
    if (!mod.design.elements[currentElement]) mod.design.elements[currentElement] = {};
    if (!mod.design.elements[currentElement][currentState]) mod.design.elements[currentElement][currentState] = {};

    if (value === '' || value === null || value === undefined) {
        delete mod.design.elements[currentElement][currentState][key];
        // Clean up empty objects
        if (Object.keys(mod.design.elements[currentElement][currentState]).length === 0) {
            delete mod.design.elements[currentElement][currentState];
        }
        if (Object.keys(mod.design.elements[currentElement]).length === 0) {
            delete mod.design.elements[currentElement];
        }
    } else {
        mod.design.elements[currentElement][currentState][key] = value;
    }

    this.updateModalPreview();
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════════════════════
// SECTION DEFINITIONS - Maps design sections to their properties
// ═══════════════════════════════════════════════════════════════════════════
TB.SECTION_PROPERTIES = {
    typography: [
        'color', 'font_size', 'font_weight', 'font_family', 'line_height',
        'letter_spacing', 'text_transform', 'text_decoration', 'text_align', 'text_shadow'
    ],
    spacing: [
        'padding', 'margin', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
        'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'gap'
    ],
    background: [
        'background', 'background_color', 'background_image', 'background_gradient', 'opacity'
    ],
    border: [
        'border', 'border_width', 'border_style', 'border_color', 'border_radius',
        'border_radius_tl', 'border_radius_tr', 'border_radius_br', 'border_radius_bl'
    ],
    box_shadow: [
        'box_shadow', 'box_shadow_enabled', 'box_shadow_h', 'box_shadow_v',
        'box_shadow_blur', 'box_shadow_spread', 'box_shadow_color', 'box_shadow_inset'
    ],
    hover: [
        'hover_background', 'hover_color', 'hover_border_color'
    ],
    transform: [
        'transform', 'transform_scale', 'transform_rotate', 'transform_translate', 'transform_skew'
    ],
    filters: [
        'filter', 'filter_blur', 'filter_brightness', 'filter_contrast',
        'filter_grayscale', 'filter_saturate', 'filter_hue_rotate'
    ],
    animation: [
        'animation', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing'
    ],
    position: [
        'position', 'top', 'right', 'bottom', 'left', 'z_index'
    ],
    visibility: [
        'visibility', 'display', 'hide_on_mobile', 'hide_on_tablet', 'hide_on_desktop'
    ]
};

// ═══════════════════════════════════════════════════════════════════════════
// SECTION-BASED RENDERING (New Divi-style Mode)
// ═══════════════════════════════════════════════════════════════════════════
TB.renderSectionBased = function(elementDef, settings, elementKey, state, isTextElement) {
    console.log('🔧 Using section-based rendering (new mode)');

    const sections = elementDef.sections || {};
    let html = '';

    // ═══════════════════════════════════════════════════════════════════════
    // 1. TYPOGRAPHY SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.typography?.enabled && isTextElement) {
        html += this.renderElementTypographySection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 2. SPACING SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.spacing?.enabled) {
        html += this.renderElementSpacingSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 3. BACKGROUND SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.background?.enabled) {
        html += this.renderElementBackgroundSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 4. BORDER SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.border?.enabled) {
        html += this.renderElementBorderSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 5. BOX SHADOW SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.box_shadow?.enabled) {
        html += this.renderElementShadowSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 6. HOVER EFFECTS SECTION (only for normal state)
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.hover?.enabled && state === 'normal') {
        html += this.renderElementHoverSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 7. TRANSFORM SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.transform?.enabled) {
        html += this.renderElementTransformSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 8. FILTERS SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.filters?.enabled) {
        html += this.renderElementFiltersSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 9. ANIMATION SECTION (only for normal state)
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.animation?.enabled && state === 'normal') {
        html += this.renderElementAnimationSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 10. POSITION SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.position?.enabled) {
        html += this.renderElementPositionSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 11. VISIBILITY SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (sections.visibility?.enabled) {
        html += this.renderElementVisibilitySection(settings, elementKey, state);
    }

    return html;
};

// ═══════════════════════════════════════════════════════════════════════════
// PROPERTY-BASED RENDERING (Legacy/Fallback Mode)
// ═══════════════════════════════════════════════════════════════════════════
TB.renderPropertyBased = function(elementDef, settings, elementKey, state, isTextElement) {
    console.log('🔧 Using property-based rendering (legacy mode)');

    const allowedProps = elementDef?.properties || [];
    const useSchemaFiltering = allowedProps.length > 0;

    // Helper to check if property group is allowed
    const isAllowed = (props) => {
        if (!useSchemaFiltering) return true; // No schema = show all
        return props.some(p => allowedProps.includes(p));
    };

    let html = '';

    // ═══════════════════════════════════════════════════════════════════════
    // 1. TYPOGRAPHY SECTION (always shown for elements that support text)
    // ═══════════════════════════════════════════════════════════════════════
    if (isTextElement && isAllowed(['color', 'font_size', 'font_weight', 'font_family', 'line_height', 'letter_spacing', 'text_transform', 'text_decoration', 'text_align', 'text_shadow'])) {
        html += this.renderElementTypographySection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 2. SPACING SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['padding', 'margin', 'padding_top', 'padding_right', 'padding_bottom', 'padding_left', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'gap'])) {
        html += this.renderElementSpacingSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 3. BACKGROUND SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['background', 'background_color', 'background_image', 'background_gradient', 'opacity'])) {
        html += this.renderElementBackgroundSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 4. BORDER SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['border', 'border_width', 'border_style', 'border_color', 'border_radius'])) {
        html += this.renderElementBorderSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 5. BOX SHADOW SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['box_shadow', 'box_shadow_h', 'box_shadow_v', 'box_shadow_blur', 'box_shadow_spread', 'box_shadow_color'])) {
        html += this.renderElementShadowSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 6. HOVER EFFECTS SECTION (only for normal state)
    // ═══════════════════════════════════════════════════════════════════════
    if (state === 'normal' && isAllowed(['hover_background', 'hover_color', 'hover_border_color'])) {
        html += this.renderElementHoverSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 7. TRANSFORM SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['transform', 'transform_scale', 'transform_rotate', 'transform_translate', 'transform_skew'])) {
        html += this.renderElementTransformSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 8. FILTERS SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['filter', 'filter_blur', 'filter_brightness', 'filter_contrast', 'filter_grayscale', 'filter_saturate'])) {
        html += this.renderElementFiltersSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 9. ANIMATION SECTION (only for normal state)
    // ═══════════════════════════════════════════════════════════════════════
    if (state === 'normal' && isAllowed(['animation', 'animation_type', 'animation_duration', 'animation_delay'])) {
        html += this.renderElementAnimationSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 10. POSITION SECTION
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['position', 'top', 'right', 'bottom', 'left', 'z_index'])) {
        html += this.renderElementPositionSection(settings, elementKey, state);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 11. VISIBILITY SECTION (device-based show/hide)
    // ═══════════════════════════════════════════════════════════════════════
    if (isAllowed(['visibility', 'display', 'hide_on_mobile', 'hide_on_tablet', 'hide_on_desktop'])) {
        html += this.renderElementVisibilitySection(settings, elementKey, state);
    }

    return html;
};

// ═══════════════════════════════════════════════════════════════════════════
// MAIN RENDER FUNCTION (Dual-Mode: Sections or Properties)
// ═══════════════════════════════════════════════════════════════════════════
TB.renderModalElementSettings = function(mod, elementKey, state) {
    console.log('🔧 tb-modal-element-design.js renderModalElementSettings called');
    console.log('  - mod.type:', mod.type);
    console.log('  - elementKey:', elementKey);
    
    // Get schema to check which properties are allowed for this element
    const schema = this.elementSchemas?.[mod.type];
    const elementDef = schema?.[elementKey];
    const allowedProps = elementDef?.properties || [];
    
    console.log('  - elementDef:', elementDef);
    console.log('  - allowedProps:', allowedProps);
    
    // If schema defines specific properties, respect them
    const useSchemaFiltering = allowedProps.length > 0;
    
    const settings = this.getElementSettings(elementKey, state);
    const isTextElement = this.isTextBasedElement(mod.type, elementKey);

    let html = '<div class="tb-modal-design-full tb-element-design">';

    // Info banner for inner elements
    html += '<div class="tb-modal-info-box tb-element-info">';
    html += '<span class="tb-modal-info-icon">🎯</span>';
    html += '<span>Editing <strong>' + this.escapeHtml(elementKey) + '</strong> element';
    if (state !== 'normal') {
        html += ' in <strong>' + state + '</strong> state';
    }
    html += '</span></div>';
    
    // ═══════════════════════════════════════════════════════════════════════
    // DUAL-MODE RENDERING: Check if elementDef has sections
    // ═══════════════════════════════════════════════════════════════════════
    if (elementDef?.sections) {
        // NEW: Use section-based rendering (Divi-style)
        console.log('  → Using section-based mode');
        html += this.renderSectionBased(elementDef, settings, elementKey, state, isTextElement);
    } else {
        // LEGACY: Use property-based rendering (backward compatibility)
        console.log('  → Using property-based mode (fallback)');
        html += this.renderPropertyBased(elementDef, settings, elementKey, state, isTextElement);
    }

    html += '</div>';
    return html;
};

// Helper to determine if element supports typography
TB.isTextBasedElement = function(moduleType, elementKey) {
    const textElements = [
        'title', 'subtitle', 'heading', 'text', 'paragraph', 'description',
        'label', 'caption', 'quote', 'button', 'link', 'name', 'role',
        'price', 'currency', 'period', 'feature', 'header', 'content',
        'tab_button', 'nav_item', 'menu_item', 'author', 'date', 'category',
        'excerpt', 'meta', 'counter', 'value', 'prefix', 'suffix'
    ];
    return textElements.includes(elementKey);
};

// ═══════════════════════════════════════════════════════════════════════════
// 1. TYPOGRAPHY SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementTypographySection = function(settings, elementKey, state) {
    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">Aa</span>';
    html += '<span class="tb-modal-section-title">Typography</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Font Family
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Family</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'font_family\', this.value)">';
    const fonts = [
        ['', 'Inherit'],
        ['system-ui, -apple-system, sans-serif', 'System UI'],
        ['Arial, Helvetica, sans-serif', 'Arial'],
        ['Georgia, serif', 'Georgia'],
        ['Times New Roman, Times, serif', 'Times New Roman'],
        ['Inter, sans-serif', 'Inter'],
        ['Roboto, sans-serif', 'Roboto'],
        ['Open Sans, sans-serif', 'Open Sans'],
        ['Poppins, sans-serif', 'Poppins'],
        ['Montserrat, sans-serif', 'Montserrat'],
        ['Lato, sans-serif', 'Lato'],
        ['Playfair Display, serif', 'Playfair Display']
    ];
    fonts.forEach(f => {
        html += '<option value="' + f[0] + '"' + (settings.font_family === f[0] ? ' selected' : '') + '>' + f[1] + '</option>';
    });
    html += '</select></div>';

    // Font Size with unit
    const fontSize = parseInt(settings.font_size) || '';
    const fontSizeUnit = settings.font_size ? (settings.font_size.toString().match(/[a-z%]+$/i)?.[0] || 'px') : 'px';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Size</label>';
    html += '<div class="tb-modal-input-with-unit">';
    html += '<input type="number" class="tb-modal-input" value="' + fontSize + '" placeholder="16" min="8" max="200" onchange="TB.updateElementSizeWithUnit(\'font_size\', this.value, this.nextElementSibling.value)">';
    html += '<select class="tb-modal-unit-select" onchange="TB.updateElementSizeWithUnit(\'font_size\', this.previousElementSibling.value, this.value)">';
    html += '<option value="px"' + (fontSizeUnit === 'px' ? ' selected' : '') + '>px</option>';
    html += '<option value="em"' + (fontSizeUnit === 'em' ? ' selected' : '') + '>em</option>';
    html += '<option value="rem"' + (fontSizeUnit === 'rem' ? ' selected' : '') + '>rem</option>';
    html += '<option value="%"' + (fontSizeUnit === '%' ? ' selected' : '') + '>%</option>';
    html += '</select></div></div>';

    // Font Weight
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Weight</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'font_weight\', this.value)">';
    const weights = [['', 'Inherit'], ['100', '100 - Thin'], ['200', '200 - Extra Light'], ['300', '300 - Light'],
                    ['400', '400 - Normal'], ['500', '500 - Medium'], ['600', '600 - Semi Bold'],
                    ['700', '700 - Bold'], ['800', '800 - Extra Bold'], ['900', '900 - Black']];
    weights.forEach(w => {
        html += '<option value="' + w[0] + '"' + (settings.font_weight === w[0] ? ' selected' : '') + '>' + w[1] + '</option>';
    });
    html += '</select></div>';

    // Line Height
    html += '<div class="tb-modal-control-row">';
    html += '<label>Line Height</label>';
    html += '<input type="number" class="tb-modal-input" value="' + (settings.line_height || '') + '" placeholder="1.6" min="0.5" max="5" step="0.1" onchange="TB.updateModalElementSetting(\'line_height\', this.value)">';
    html += '</div>';

    // Letter Spacing
    const letterSpacing = parseInt(settings.letter_spacing) || '';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Letter Spacing</label>';
    html += '<div class="tb-modal-input-with-unit">';
    html += '<input type="number" class="tb-modal-input" value="' + letterSpacing + '" placeholder="0" min="-10" max="50" step="0.5" onchange="TB.updateElementSizeWithUnit(\'letter_spacing\', this.value, this.nextElementSibling.value)">';
    html += '<select class="tb-modal-unit-select" onchange="TB.updateElementSizeWithUnit(\'letter_spacing\', this.previousElementSibling.value, this.value)">';
    const lsUnit = settings.letter_spacing ? (settings.letter_spacing.toString().match(/[a-z%]+$/i)?.[0] || 'px') : 'px';
    html += '<option value="px"' + (lsUnit === 'px' ? ' selected' : '') + '>px</option>';
    html += '<option value="em"' + (lsUnit === 'em' ? ' selected' : '') + '>em</option>';
    html += '</select></div></div>';

    // Text Transform
    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Transform</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'text_transform\', this.value)">';
    html += '<option value="">None</option>';
    html += '<option value="uppercase"' + (settings.text_transform === 'uppercase' ? ' selected' : '') + '>UPPERCASE</option>';
    html += '<option value="lowercase"' + (settings.text_transform === 'lowercase' ? ' selected' : '') + '>lowercase</option>';
    html += '<option value="capitalize"' + (settings.text_transform === 'capitalize' ? ' selected' : '') + '>Capitalize</option>';
    html += '</select></div>';

    // Text Decoration
    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Decoration</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'text_decoration\', this.value)">';
    html += '<option value="">None</option>';
    html += '<option value="underline"' + (settings.text_decoration === 'underline' ? ' selected' : '') + '>Underline</option>';
    html += '<option value="line-through"' + (settings.text_decoration === 'line-through' ? ' selected' : '') + '>Line Through</option>';
    html += '<option value="overline"' + (settings.text_decoration === 'overline' ? ' selected' : '') + '>Overline</option>';
    html += '</select></div>';

    // Font Style
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Style</label>';
    html += '<div class="tb-modal-btn-group">';
    html += '<button type="button" class="tb-modal-btn-opt' + (!settings.font_style || settings.font_style === 'normal' ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'font_style\', \'normal\'); TB.refreshModalControlUI(this)" title="Normal">N</button>';
    html += '<button type="button" class="tb-modal-btn-opt' + (settings.font_style === 'italic' ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'font_style\', \'italic\'); TB.refreshModalControlUI(this)" title="Italic"><i>I</i></button>';
    html += '</div></div>';

    // Text Alignment
    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Align</label>';
    html += '<div class="tb-modal-btn-group">';
    ['left', 'center', 'right', 'justify'].forEach(align => {
        const icons = { left: '◀', center: '◆', right: '▶', justify: '≡' };
        html += '<button type="button" class="tb-modal-btn-opt' + (settings.text_align === align ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'text_align\', \'' + align + '\'); TB.refreshModalControlUI(this)" title="' + align + '">' + icons[align] + '</button>';
    });
    html += '</div></div>';

    // Text Color
    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Color</label>';
    html += this.renderElementColorPicker('color', settings.color || '');
    html += '</div>';

    html += '</div></div>';
    return html;
};

// Helper for size inputs with units
TB.updateElementSizeWithUnit = function(key, value, unit) {
    if (value === '' || value === null || value === undefined) {
        this.updateModalElementSetting(key, '');
    } else {
        this.updateModalElementSetting(key, value + (unit || 'px'));
    }
};

// ═══════════════════════════════════════════════════════════════════════════
// 2. SPACING SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementSpacingSection = function(settings, elementKey, state) {
    const mTop = parseInt(settings.margin_top) || 0;
    const mRight = parseInt(settings.margin_right) || 0;
    const mBottom = parseInt(settings.margin_bottom) || 0;
    const mLeft = parseInt(settings.margin_left) || 0;
    const pTop = parseInt(settings.padding_top) || 0;
    const pRight = parseInt(settings.padding_right) || 0;
    const pBottom = parseInt(settings.padding_bottom) || 0;
    const pLeft = parseInt(settings.padding_left) || 0;
    const marginLinked = settings.margin_linked || false;
    const paddingLinked = settings.padding_linked || false;

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">📐</span>';
    html += '<span class="tb-modal-section-title">Spacing</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Visual Box Model
    html += '<div class="tb-modal-spacing-visual">';

    // Margin box (outer)
    html += '<div class="tb-modal-spacing-box-outer tb-element-spacing">';
    html += '<span class="tb-modal-spacing-label-outer">MARGIN</span>';
    html += '<div class="tb-modal-spacing-input-top"><input type="number" value="' + mTop + '" min="-500" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'margin\', \'top\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-right"><input type="number" value="' + mRight + '" min="-500" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'margin\', \'right\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-bottom"><input type="number" value="' + mBottom + '" min="-500" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'margin\', \'bottom\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-left"><input type="number" value="' + mLeft + '" min="-500" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'margin\', \'left\', this.value)"></div>';
    html += '<button type="button" class="tb-modal-spacing-link-btn tb-modal-spacing-link-margin' + (marginLinked ? ' linked' : '') + '" data-type="margin" onclick="TB.toggleElementSpacingLink(\'margin\')" title="Link all margin values">' + (marginLinked ? '🔗' : '⛓️') + '</button>';

    // Padding box (inner)
    html += '<div class="tb-modal-spacing-box-inner">';
    html += '<span class="tb-modal-spacing-label-inner">PADDING</span>';
    html += '<div class="tb-modal-spacing-input-top"><input type="number" value="' + pTop + '" min="0" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'padding\', \'top\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-right"><input type="number" value="' + pRight + '" min="0" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'padding\', \'right\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-bottom"><input type="number" value="' + pBottom + '" min="0" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'padding\', \'bottom\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-left"><input type="number" value="' + pLeft + '" min="0" max="500" placeholder="0" onchange="TB.updateElementSpacing(\'padding\', \'left\', this.value)"></div>';
    html += '<button type="button" class="tb-modal-spacing-link-btn tb-modal-spacing-link-padding' + (paddingLinked ? ' linked' : '') + '" data-type="padding" onclick="TB.toggleElementSpacingLink(\'padding\')" title="Link all padding values">' + (paddingLinked ? '🔗' : '⛓️') + '</button>';
    html += '<div class="tb-modal-spacing-content-box">Content</div>';
    html += '</div>'; // inner
    html += '</div>'; // outer
    html += '</div>'; // visual

    html += '</div></div>';
    return html;
};

// Element spacing update
TB.updateElementSpacing = function(type, side, value) {
    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const isLinked = settings[type + '_linked'];
    const numValue = value === '' ? 0 : parseInt(value);
    const cssValue = numValue + 'px';

    if (isLinked) {
        // Update all 4 sides
        this.updateModalElementSetting(type + '_top', cssValue);
        this.updateModalElementSetting(type + '_right', cssValue);
        this.updateModalElementSetting(type + '_bottom', cssValue);
        this.updateModalElementSetting(type + '_left', cssValue);
        // Refresh inputs
        this.refreshElementSpacingInputs(type);
    } else {
        this.updateModalElementSetting(type + '_' + side, cssValue);
    }
};

// Toggle spacing link
TB.toggleElementSpacingLink = function(type) {
    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const isLinked = !settings[type + '_linked'];
    this.updateModalElementSetting(type + '_linked', isLinked);

    if (isLinked) {
        // Sync all to top value
        const topValue = settings[type + '_top'] || '0px';
        this.updateModalElementSetting(type + '_right', topValue);
        this.updateModalElementSetting(type + '_bottom', topValue);
        this.updateModalElementSetting(type + '_left', topValue);
    }

    // Update button appearance
    const btn = document.querySelector('.tb-element-spacing .tb-modal-spacing-link-btn[data-type="' + type + '"]');
    if (btn) {
        btn.classList.toggle('linked', isLinked);
        btn.textContent = isLinked ? '🔗' : '⛓️';
    }

    this.refreshElementSpacingInputs(type);
};

// Refresh spacing inputs
TB.refreshElementSpacingInputs = function(type) {
    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const boxClass = type === 'margin' ? '.tb-modal-spacing-box-outer' : '.tb-modal-spacing-box-inner';
    const box = document.querySelector('.tb-element-spacing ' + boxClass) || document.querySelector(boxClass);
    if (!box) return;

    ['top', 'right', 'bottom', 'left'].forEach(side => {
        const input = box.querySelector(':scope > .tb-modal-spacing-input-' + side + ' input');
        if (input) {
            input.value = parseInt(settings[type + '_' + side]) || 0;
        }
    });
};

// ═══════════════════════════════════════════════════════════════════════════
// 3. BACKGROUND SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementBackgroundSection = function(settings, elementKey, state) {
    const bgType = settings.background_type || 'color';

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🎨</span>';
    html += '<span class="tb-modal-section-title">Background</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Background Type
    html += '<div class="tb-modal-control-row">';
    html += '<label>Type</label>';
    html += '<div class="tb-modal-btn-group">';
    ['color', 'gradient', 'image'].forEach(type => {
        const icons = { color: '🎨', gradient: '🌈', image: '🖼️' };
        html += '<button type="button" class="tb-modal-btn-opt' + (bgType === type ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'background_type\', \'' + type + '\'); TB.renderModalDesignSettings()" title="' + type + '">' + icons[type] + '</button>';
    });
    html += '</div></div>';

    if (bgType === 'color') {
        // Background Color
        html += '<div class="tb-modal-control-row">';
        html += '<label>Background Color</label>';
        html += this.renderElementColorPicker('background_color', settings.background_color || '');
        html += '</div>';
    } else if (bgType === 'gradient') {
        // Gradient Type
        html += '<div class="tb-modal-control-row">';
        html += '<label>Gradient Type</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'gradient_type\', this.value)">';
        html += '<option value="linear"' + (settings.gradient_type !== 'radial' ? ' selected' : '') + '>Linear</option>';
        html += '<option value="radial"' + (settings.gradient_type === 'radial' ? ' selected' : '') + '>Radial</option>';
        html += '</select></div>';

        // Gradient Angle (for linear)
        if (settings.gradient_type !== 'radial') {
            html += '<div class="tb-modal-control-row slider">';
            html += '<label>Angle: <span class="tb-modal-slider-value">' + (settings.gradient_angle || 180) + '°</span></label>';
            html += '<input type="range" class="tb-modal-range" min="0" max="360" value="' + (settings.gradient_angle || 180) + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'gradient_angle\', this.value)">';
            html += '</div>';
        }

        // Gradient Colors
        html += '<div class="tb-modal-control-row">';
        html += '<label>Start Color</label>';
        html += this.renderElementColorPicker('gradient_start', settings.gradient_start || '#000000');
        html += '</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>End Color</label>';
        html += this.renderElementColorPicker('gradient_end', settings.gradient_end || '#ffffff');
        html += '</div>';
    } else if (bgType === 'image') {
        // Background Image URL
        html += '<div class="tb-modal-control-row">';
        html += '<label>Image URL</label>';
        html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(settings.background_image || '') + '" placeholder="https://..." onchange="TB.updateModalElementSetting(\'background_image\', this.value)">';
        html += '</div>';

        // Background Size
        html += '<div class="tb-modal-control-row">';
        html += '<label>Size</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'background_size\', this.value)">';
        ['cover', 'contain', 'auto', '100% 100%'].forEach(s => {
            html += '<option value="' + s + '"' + (settings.background_size === s ? ' selected' : '') + '>' + s + '</option>';
        });
        html += '</select></div>';

        // Background Position
        html += '<div class="tb-modal-control-row">';
        html += '<label>Position</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'background_position\', this.value)">';
        ['center center', 'top center', 'bottom center', 'center left', 'center right', 'top left', 'top right', 'bottom left', 'bottom right'].forEach(p => {
            html += '<option value="' + p + '"' + (settings.background_position === p ? ' selected' : '') + '>' + p + '</option>';
        });
        html += '</select></div>';

        // Background Repeat
        html += '<div class="tb-modal-control-row">';
        html += '<label>Repeat</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'background_repeat\', this.value)">';
        ['no-repeat', 'repeat', 'repeat-x', 'repeat-y'].forEach(r => {
            html += '<option value="' + r + '"' + (settings.background_repeat === r ? ' selected' : '') + '>' + r + '</option>';
        });
        html += '</select></div>';
    }

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════════════════════
// COLOR PICKER HELPER FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementColorPicker = function(prop, value) {
    const id = 'el-color-' + prop + '-' + Date.now();
    const hexValue = value ? (value.startsWith('#') ? value : this.rgbaToHex(value) || '#000000') : '#000000';

    return '<div class="tb-modal-color-picker">' +
        '<input type="color" id="' + id + '" value="' + hexValue + '" onchange="TB.updateModalElementSetting(\'' + prop + '\', this.value)">' +
        '<input type="text" class="tb-modal-input tb-modal-color-text" value="' + this.escapeHtml(value || '') + '" placeholder="#000000 or rgba()" onchange="document.getElementById(\'' + id + '\').value = this.value.startsWith(\'#\') ? this.value : \'#000000\'; TB.updateModalElementSetting(\'' + prop + '\', this.value)">' +
        '</div>';
};

// ═══════════════════════════════════════════════════════════════════════════
// 4. BORDER SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementBorderSection = function(settings, elementKey, state) {
    const bwTop = parseInt(settings.border_width_top) || 0;
    const bwRight = parseInt(settings.border_width_right) || 0;
    const bwBottom = parseInt(settings.border_width_bottom) || 0;
    const bwLeft = parseInt(settings.border_width_left) || 0;
    const borderStyle = settings.border_style || 'none';
    const borderColor = settings.border_color || '#e2e8f0';
    const brTL = parseInt(settings.border_radius_tl) || 0;
    const brTR = parseInt(settings.border_radius_tr) || 0;
    const brBR = parseInt(settings.border_radius_br) || 0;
    const brBL = parseInt(settings.border_radius_bl) || 0;
    const borderLinked = settings.border_width_linked || false;
    const radiusLinked = settings.border_radius_linked || false;

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🔲</span>';
    html += '<span class="tb-modal-section-title">Border</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Border Width (visual box)
    html += '<div class="tb-modal-subsection-title">Border Width</div>';
    html += '<div class="tb-modal-border-box tb-element-border">';
    html += '<div class="tb-modal-border-input top"><input type="number" value="' + bwTop + '" min="0" max="50" onchange="TB.updateElementBorder(\'width\', \'top\', this.value)"></div>';
    html += '<div class="tb-modal-border-input right"><input type="number" value="' + bwRight + '" min="0" max="50" onchange="TB.updateElementBorder(\'width\', \'right\', this.value)"></div>';
    html += '<div class="tb-modal-border-input bottom"><input type="number" value="' + bwBottom + '" min="0" max="50" onchange="TB.updateElementBorder(\'width\', \'bottom\', this.value)"></div>';
    html += '<div class="tb-modal-border-input left"><input type="number" value="' + bwLeft + '" min="0" max="50" onchange="TB.updateElementBorder(\'width\', \'left\', this.value)"></div>';
    html += '<button type="button" class="tb-modal-border-link-btn' + (borderLinked ? ' linked' : '') + '" onclick="TB.toggleElementBorderLink(\'width\')" title="Link all border widths">' + (borderLinked ? '🔗' : '⛓️') + '</button>';
    html += '</div>';

    // Border Style
    html += '<div class="tb-modal-control-row">';
    html += '<label>Style</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'border_style\', this.value)">';
    ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset'].forEach(s => {
        html += '<option value="' + s + '"' + (borderStyle === s ? ' selected' : '') + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
    });
    html += '</select></div>';

    // Border Color
    html += '<div class="tb-modal-control-row">';
    html += '<label>Color</label>';
    html += this.renderElementColorPicker('border_color', borderColor);
    html += '</div>';

    // Border Radius (visual box)
    html += '<div class="tb-modal-subsection-title">Border Radius</div>';
    html += '<div class="tb-modal-radius-box tb-element-radius">';
    html += '<div class="tb-modal-radius-input tl"><input type="number" value="' + brTL + '" min="0" max="500" onchange="TB.updateElementBorder(\'radius\', \'tl\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input tr"><input type="number" value="' + brTR + '" min="0" max="500" onchange="TB.updateElementBorder(\'radius\', \'tr\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input br"><input type="number" value="' + brBR + '" min="0" max="500" onchange="TB.updateElementBorder(\'radius\', \'br\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input bl"><input type="number" value="' + brBL + '" min="0" max="500" onchange="TB.updateElementBorder(\'radius\', \'bl\', this.value)"></div>';
    html += '<button type="button" class="tb-modal-radius-link-btn' + (radiusLinked ? ' linked' : '') + '" onclick="TB.toggleElementBorderLink(\'radius\')" title="Link all radii">' + (radiusLinked ? '🔗' : '⛓️') + '</button>';
    html += '</div>';

    html += '</div></div>';
    return html;
};

// Update element border
TB.updateElementBorder = function(type, side, value) {
    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const linkKey = type === 'width' ? 'border_width_linked' : 'border_radius_linked';
    const isLinked = settings[linkKey];
    const numValue = value === '' ? 0 : parseInt(value);
    const cssValue = numValue + 'px';

    const sideMap = type === 'width'
        ? { top: 'border_width_top', right: 'border_width_right', bottom: 'border_width_bottom', left: 'border_width_left' }
        : { tl: 'border_radius_tl', tr: 'border_radius_tr', br: 'border_radius_br', bl: 'border_radius_bl' };

    if (isLinked) {
        Object.values(sideMap).forEach(key => {
            this.updateModalElementSetting(key, cssValue);
        });
    } else {
        this.updateModalElementSetting(sideMap[side], cssValue);
    }
};

// Toggle border link
TB.toggleElementBorderLink = function(type) {
    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const linkKey = type === 'width' ? 'border_width_linked' : 'border_radius_linked';
    const isLinked = !settings[linkKey];
    this.updateModalElementSetting(linkKey, isLinked);
    this.renderModalDesignSettings();
};

console.log('TB Modal Element Design System loaded - Part 1 (Typography, Spacing, Background, Border)');
