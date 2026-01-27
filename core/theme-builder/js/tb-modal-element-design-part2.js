/**
 * Theme Builder 3.0 - Element Design System Part 2
 * Shadow, Hover Effects, Transform, Filters, Animation, Position, Visibility
 */

// ═══════════════════════════════════════════════════════════════════════════
// 5. BOX SHADOW SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementShadowSection = function(settings, elementKey, state) {
    const enabled = settings.box_shadow_enabled || false;
    const h = parseInt(settings.box_shadow_h) || 0;
    const v = parseInt(settings.box_shadow_v) || 4;
    const blur = parseInt(settings.box_shadow_blur) || 10;
    const spread = parseInt(settings.box_shadow_spread) || 0;
    const color = settings.box_shadow_color || 'rgba(0,0,0,0.1)';
    const inset = settings.box_shadow_inset || false;

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🌫️</span>';
    html += '<span class="tb-modal-section-title">Box Shadow</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Enable toggle
    html += '<div class="tb-modal-control-row">';
    html += '<label>Enable Shadow</label>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalElementSetting(\'box_shadow_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Horizontal
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Horizontal: <span class="tb-modal-slider-value">' + h + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + h + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'box_shadow_h\', this.value)">';
        html += '</div>';

        // Vertical
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Vertical: <span class="tb-modal-slider-value">' + v + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + v + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'box_shadow_v\', this.value)">';
        html += '</div>';

        // Blur
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Blur: <span class="tb-modal-slider-value">' + blur + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'box_shadow_blur\', this.value)">';
        html += '</div>';

        // Spread
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Spread: <span class="tb-modal-slider-value">' + spread + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + spread + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'box_shadow_spread\', this.value)">';
        html += '</div>';

        // Color
        html += '<div class="tb-modal-control-row">';
        html += '<label>Shadow Color</label>';
        html += this.renderElementColorPicker('box_shadow_color', color);
        html += '</div>';

        // Inset
        html += '<div class="tb-modal-control-row">';
        html += '<label>Inset Shadow</label>';
        html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (inset ? 'checked' : '') + ' onchange="TB.updateModalElementSetting(\'box_shadow_inset\', this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
        html += '</div>';

        // Shadow presets
        html += '<div class="tb-modal-subsection-title">Presets</div>';
        html += '<div class="tb-modal-preset-grid">';
        const presets = [
            { name: 'None', values: { enabled: false } },
            { name: 'Subtle', values: { h: 0, v: 1, blur: 3, spread: 0, color: 'rgba(0,0,0,0.1)' } },
            { name: 'Small', values: { h: 0, v: 2, blur: 6, spread: 0, color: 'rgba(0,0,0,0.15)' } },
            { name: 'Medium', values: { h: 0, v: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.15)' } },
            { name: 'Large', values: { h: 0, v: 8, blur: 24, spread: 0, color: 'rgba(0,0,0,0.2)' } },
            { name: 'Glow', values: { h: 0, v: 0, blur: 20, spread: 5, color: 'rgba(59,130,246,0.5)' } }
        ];
        presets.forEach(p => {
            html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementShadowPreset(' + JSON.stringify(p.values).replace(/"/g, '&quot;') + ')">' + p.name + '</button>';
        });
        html += '</div>';
    }

    html += '</div></div>';
    return html;
};

// Apply shadow preset
TB.applyElementShadowPreset = function(preset) {
    if (preset.enabled === false) {
        this.updateModalElementSetting('box_shadow_enabled', false);
    } else {
        this.updateModalElementSetting('box_shadow_enabled', true);
        this.updateModalElementSetting('box_shadow_h', preset.h);
        this.updateModalElementSetting('box_shadow_v', preset.v);
        this.updateModalElementSetting('box_shadow_blur', preset.blur);
        this.updateModalElementSetting('box_shadow_spread', preset.spread);
        this.updateModalElementSetting('box_shadow_color', preset.color);
    }
    this.renderModalDesignSettings();
};

// ═══════════════════════════════════════════════════════════════════════════
// 6. HOVER EFFECTS SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementHoverSection = function(settings, elementKey, state) {
    const enabled = settings.hover_enabled || false;
    const duration = settings.transition_duration || '0.3';
    const easing = settings.transition_easing || 'ease';

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">👆</span>';
    html += '<span class="tb-modal-section-title">Hover Effects</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Enable toggle
    html += '<div class="tb-modal-control-row">';
    html += '<label>Enable Hover Effects</label>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalElementSetting(\'hover_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Transition Duration
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Duration: <span class="tb-modal-slider-value">' + duration + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="2" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalElementSetting(\'transition_duration\', this.value)">';
        html += '</div>';

        // Transition Easing
        html += '<div class="tb-modal-control-row">';
        html += '<label>Easing</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'transition_easing\', this.value)">';
        ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear', 'cubic-bezier(0.4, 0, 0.2, 1)'].forEach(e => {
            const label = e.startsWith('cubic') ? 'Material Design' : e;
            html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + label + '</option>';
        });
        html += '</select></div>';

        // ─────────────────────────────────────────────────────────────────
        // HOVER COLORS
        // ─────────────────────────────────────────────────────────────────
        html += '<div class="tb-modal-subsection-title">Colors on Hover</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>Text Color</label>';
        html += this.renderElementColorPicker('hover_color', settings.hover_color || '');
        html += '</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>Background</label>';
        html += this.renderElementColorPicker('hover_background', settings.hover_background || '');
        html += '</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>Border Color</label>';
        html += this.renderElementColorPicker('hover_border_color', settings.hover_border_color || '');
        html += '</div>';

        // ─────────────────────────────────────────────────────────────────
        // HOVER TRANSFORM
        // ─────────────────────────────────────────────────────────────────
        html += '<div class="tb-modal-subsection-title">Transform on Hover</div>';

        const hoverScale = settings.hover_scale || '100';
        const hoverTranslateY = settings.hover_translate_y || '0';
        const hoverRotate = settings.hover_rotate || '0';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Scale: <span class="tb-modal-slider-value">' + hoverScale + '%</span></label>';
        html += '<input type="range" class="tb-modal-range" min="50" max="150" value="' + hoverScale + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'hover_scale\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Move Y: <span class="tb-modal-slider-value">' + hoverTranslateY + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + hoverTranslateY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'hover_translate_y\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Rotate: <span class="tb-modal-slider-value">' + hoverRotate + '°</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-45" max="45" value="' + hoverRotate + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'hover_rotate\', this.value)">';
        html += '</div>';

        // ─────────────────────────────────────────────────────────────────
        // HOVER SHADOW
        // ─────────────────────────────────────────────────────────────────
        html += '<div class="tb-modal-subsection-title">Shadow on Hover</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>Box Shadow</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'hover_shadow\', this.value)">';
        html += '<option value="">No change</option>';
        html += '<option value="0 4px 6px rgba(0,0,0,0.1)"' + (settings.hover_shadow === '0 4px 6px rgba(0,0,0,0.1)' ? ' selected' : '') + '>Subtle lift</option>';
        html += '<option value="0 10px 20px rgba(0,0,0,0.15)"' + (settings.hover_shadow === '0 10px 20px rgba(0,0,0,0.15)' ? ' selected' : '') + '>Medium lift</option>';
        html += '<option value="0 20px 40px rgba(0,0,0,0.2)"' + (settings.hover_shadow === '0 20px 40px rgba(0,0,0,0.2)' ? ' selected' : '') + '>Large lift</option>';
        html += '<option value="0 0 20px rgba(59,130,246,0.5)"' + (settings.hover_shadow === '0 0 20px rgba(59,130,246,0.5)' ? ' selected' : '') + '>Blue glow</option>';
        html += '<option value="none"' + (settings.hover_shadow === 'none' ? ' selected' : '') + '>Remove shadow</option>';
        html += '</select></div>';

        // ─────────────────────────────────────────────────────────────────
        // HOVER OPACITY
        // ─────────────────────────────────────────────────────────────────
        const hoverOpacity = settings.hover_opacity || '1';
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Opacity: <span class="tb-modal-slider-value">' + hoverOpacity + '</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="1" step="0.1" value="' + hoverOpacity + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value" onchange="TB.updateModalElementSetting(\'hover_opacity\', this.value)">';
        html += '</div>';

        // ─────────────────────────────────────────────────────────────────
        // HOVER PRESETS
        // ─────────────────────────────────────────────────────────────────
        html += '<div class="tb-modal-subsection-title">Quick Presets</div>';
        html += '<div class="tb-modal-preset-grid">';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'lift\')">Lift Up</button>';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'grow\')">Grow</button>';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'shrink\')">Shrink</button>';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'glow\')">Glow</button>';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'fade\')">Fade</button>';
        html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementHoverPreset(\'reset\')">Reset</button>';
        html += '</div>';
    }

    html += '</div></div>';
    return html;
};

// Apply hover preset
TB.applyElementHoverPreset = function(preset) {
    const presets = {
        lift: { hover_scale: '100', hover_translate_y: '-5', hover_shadow: '0 10px 20px rgba(0,0,0,0.15)' },
        grow: { hover_scale: '105', hover_translate_y: '0', hover_shadow: '' },
        shrink: { hover_scale: '95', hover_translate_y: '0', hover_shadow: '' },
        glow: { hover_scale: '100', hover_translate_y: '0', hover_shadow: '0 0 20px rgba(59,130,246,0.5)' },
        fade: { hover_opacity: '0.7', hover_scale: '100', hover_translate_y: '0' },
        reset: { hover_scale: '100', hover_translate_y: '0', hover_rotate: '0', hover_opacity: '1', hover_shadow: '', hover_color: '', hover_background: '', hover_border_color: '' }
    };

    const values = presets[preset];
    if (values) {
        Object.entries(values).forEach(([key, value]) => {
            this.updateModalElementSetting(key, value);
        });
        this.renderModalDesignSettings();
    }
};

// ═══════════════════════════════════════════════════════════════════════════
// 7. TRANSFORM SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementTransformSection = function(settings, elementKey, state) {
    const scaleX = settings.transform_scale_x || '100';
    const scaleY = settings.transform_scale_y || '100';
    const rotateZ = settings.transform_rotate || '0';
    const skewX = settings.transform_skew_x || '0';
    const skewY = settings.transform_skew_y || '0';
    const translateX = settings.transform_translate_x || '0';
    const translateY = settings.transform_translate_y || '0';
    const origin = settings.transform_origin || 'center center';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🔄</span>';
    html += '<span class="tb-modal-section-title">Transform</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // ─────────────────────────────────────────────────────────────────
    // SCALE
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Scale</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Scale X: <span class="tb-modal-slider-value">' + scaleX + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + scaleX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'transform_scale_x\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Scale Y: <span class="tb-modal-slider-value">' + scaleY + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + scaleY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'transform_scale_y\', this.value)">';
    html += '</div>';

    // ─────────────────────────────────────────────────────────────────
    // ROTATE
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Rotate</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Rotate: <span class="tb-modal-slider-value">' + rotateZ + '°</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-180" max="180" value="' + rotateZ + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'transform_rotate\', this.value)">';
    html += '</div>';

    // ─────────────────────────────────────────────────────────────────
    // SKEW
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Skew</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Skew X: <span class="tb-modal-slider-value">' + skewX + '°</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-45" max="45" value="' + skewX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'transform_skew_x\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Skew Y: <span class="tb-modal-slider-value">' + skewY + '°</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-45" max="45" value="' + skewY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'transform_skew_y\', this.value)">';
    html += '</div>';

    // ─────────────────────────────────────────────────────────────────
    // TRANSLATE
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Translate</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Move X: <span class="tb-modal-slider-value">' + translateX + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-200" max="200" value="' + translateX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'transform_translate_x\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Move Y: <span class="tb-modal-slider-value">' + translateY + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-200" max="200" value="' + translateY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'transform_translate_y\', this.value)">';
    html += '</div>';

    // ─────────────────────────────────────────────────────────────────
    // TRANSFORM ORIGIN
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Transform Origin</div>';
    html += '<div class="tb-modal-transform-origin-grid">';
    const points = [
        ['left', 'top'], ['center', 'top'], ['right', 'top'],
        ['left', 'center'], ['center', 'center'], ['right', 'center'],
        ['left', 'bottom'], ['center', 'bottom'], ['right', 'bottom']
    ];
    points.forEach(p => {
        const val = p[0] + ' ' + p[1];
        html += '<div class="tb-modal-origin-point' + (origin === val ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'transform_origin\', \'' + val + '\'); TB.refreshOriginUI(this)" title="' + val + '"></div>';
    });
    html += '</div>';

    // Reset button
    html += '<button type="button" class="tb-modal-reset-btn" onclick="TB.resetElementTransforms()">↺ Reset Transforms</button>';

    html += '</div></div>';
    return html;
};

// Refresh origin UI
TB.refreshOriginUI = function(clickedEl) {
    document.querySelectorAll('.tb-modal-origin-point').forEach(p => p.classList.remove('active'));
    if (clickedEl) clickedEl.classList.add('active');
};

// Reset transforms
TB.resetElementTransforms = function() {
    ['transform_scale_x', 'transform_scale_y', 'transform_rotate', 'transform_skew_x', 'transform_skew_y',
     'transform_translate_x', 'transform_translate_y'].forEach(key => {
        this.updateModalElementSetting(key, key.includes('scale') ? '100' : '0');
    });
    this.updateModalElementSetting('transform_origin', 'center center');
    this.renderModalDesignSettings();
};

// ═══════════════════════════════════════════════════════════════════════════
// 8. FILTERS SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementFiltersSection = function(settings, elementKey, state) {
    const blur = settings.filter_blur || '0';
    const brightness = settings.filter_brightness || '100';
    const contrast = settings.filter_contrast || '100';
    const saturation = settings.filter_saturation || '100';
    const grayscale = settings.filter_grayscale || '0';
    const sepia = settings.filter_sepia || '0';
    const hueRotate = settings.filter_hue_rotate || '0';
    const invert = settings.filter_invert || '0';
    const opacity = settings.filter_opacity || '100';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🎭</span>';
    html += '<span class="tb-modal-section-title">Filters</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Blur
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Blur: <span class="tb-modal-slider-value">' + blur + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="20" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalElementSetting(\'filter_blur\', this.value)">';
    html += '</div>';

    // Brightness
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Brightness: <span class="tb-modal-slider-value">' + brightness + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + brightness + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_brightness\', this.value)">';
    html += '</div>';

    // Contrast
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Contrast: <span class="tb-modal-slider-value">' + contrast + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + contrast + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_contrast\', this.value)">';
    html += '</div>';

    // Saturation
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Saturation: <span class="tb-modal-slider-value">' + saturation + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + saturation + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_saturation\', this.value)">';
    html += '</div>';

    // Grayscale
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Grayscale: <span class="tb-modal-slider-value">' + grayscale + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + grayscale + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_grayscale\', this.value)">';
    html += '</div>';

    // Sepia
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Sepia: <span class="tb-modal-slider-value">' + sepia + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + sepia + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_sepia\', this.value)">';
    html += '</div>';

    // Hue Rotate
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Hue Rotate: <span class="tb-modal-slider-value">' + hueRotate + '°</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="360" value="' + hueRotate + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalElementSetting(\'filter_hue_rotate\', this.value)">';
    html += '</div>';

    // Invert
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Invert: <span class="tb-modal-slider-value">' + invert + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + invert + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_invert\', this.value)">';
    html += '</div>';

    // Opacity
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Opacity: <span class="tb-modal-slider-value">' + opacity + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalElementSetting(\'filter_opacity\', this.value)">';
    html += '</div>';

    // Presets
    html += '<div class="tb-modal-subsection-title">Filter Presets</div>';
    html += '<div class="tb-modal-preset-grid">';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'vivid\')">Vivid</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'muted\')">Muted</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'vintage\')">Vintage</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'dramatic\')">Dramatic</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'bw\')">B&W</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.applyElementFilterPreset(\'reset\')">Reset</button>';
    html += '</div>';

    html += '</div></div>';
    return html;
};

// Apply filter preset
TB.applyElementFilterPreset = function(preset) {
    const presets = {
        vivid: { filter_brightness: '110', filter_contrast: '120', filter_saturation: '130' },
        muted: { filter_brightness: '95', filter_contrast: '90', filter_saturation: '70' },
        vintage: { filter_sepia: '30', filter_contrast: '110', filter_brightness: '90' },
        dramatic: { filter_contrast: '140', filter_brightness: '90', filter_saturation: '110' },
        bw: { filter_grayscale: '100', filter_contrast: '110' },
        reset: { filter_blur: '0', filter_brightness: '100', filter_contrast: '100', filter_saturation: '100', filter_grayscale: '0', filter_sepia: '0', filter_hue_rotate: '0', filter_invert: '0', filter_opacity: '100' }
    };

    const values = presets[preset];
    if (values) {
        // Reset all first if not reset preset
        if (preset !== 'reset') {
            Object.keys(presets.reset).forEach(key => {
                this.updateModalElementSetting(key, presets.reset[key]);
            });
        }
        // Apply preset values
        Object.entries(values).forEach(([key, value]) => {
            this.updateModalElementSetting(key, value);
        });
        this.renderModalDesignSettings();
    }
};

console.log('TB Modal Element Design System loaded - Part 2 (Shadow, Hover, Transform, Filters)');
