/**
 * Theme Builder 3.0 - Helper Functions Module
 * Contains all update, handle, toggle, and utility functions
 * Part of TB 3.0 modularization - ETAP 6
 */

// ═══════════════════════════════════════════════════════════
// MODULE STYLES BUILDER (from Design panel settings)
// ═══════════════════════════════════════════════════════════
TB.getModuleStyles = function(design) {
    if (!design) return '';
    let moduleStyles = '';

    // Margin (responsive or unified)
    const mT = design.margin_top || design.marginTop || '';
    const mR = design.margin_right || design.marginRight || '';
    const mB = design.margin_bottom || design.marginBottom || '';
    const mL = design.margin_left || design.marginLeft || '';
    if (mT || mR || mB || mL) {
        moduleStyles += 'margin:' + (mT || '0') + ' ' + (mR || '0') + ' ' + (mB || '0') + ' ' + (mL || '0') + ';';
    } else if (design.margin) {
        moduleStyles += 'margin:' + design.margin + ';';
    }

    // Padding (responsive or unified)
    const pT = design.padding_top || design.paddingTop || '';
    const pR = design.padding_right || design.paddingRight || '';
    const pB = design.padding_bottom || design.paddingBottom || '';
    const pL = design.padding_left || design.paddingLeft || '';
    if (pT || pR || pB || pL) {
        moduleStyles += 'padding:' + (pT || '0') + ' ' + (pR || '0') + ' ' + (pB || '0') + ' ' + (pL || '0') + ';';
    } else if (design.padding) {
        moduleStyles += 'padding:' + design.padding + ';';
    }

    // Background
    if (design.backgroundColor && design.backgroundColor !== '#ffffff') {
        moduleStyles += 'background-color:' + design.backgroundColor + ';';
    }

    // Text alignment
    if (design.textAlign) {
        moduleStyles += 'text-align:' + design.textAlign + ';';
    }

    // Border width
    const bwT = design.border_width_top || '';
    const bwR = design.border_width_right || '';
    const bwB = design.border_width_bottom || '';
    const bwL = design.border_width_left || '';
    if (bwT || bwR || bwB || bwL) {
        moduleStyles += 'border-width:' + (bwT || '0') + ' ' + (bwR || '0') + ' ' + (bwB || '0') + ' ' + (bwL || '0') + ';';
    } else if (design.borderWidth) {
        moduleStyles += 'border-width:' + design.borderWidth + ';';
    }

    // Border style
    if (design.border_style) {
        moduleStyles += 'border-style:' + design.border_style + ';';
    } else if (bwT || bwR || bwB || bwL || design.borderWidth) {
        moduleStyles += 'border-style:solid;';
    }

    // Border color
    if (design.border_color) {
        moduleStyles += 'border-color:' + design.border_color + ';';
    } else if (design.borderColor) {
        moduleStyles += 'border-color:' + design.borderColor + ';';
    }

    // Border radius - support both naming conventions:
    // Modal uses: border_radius_tl/tr/br/bl
    // Legacy uses: border_radius_top_left/top_right/bottom_right/bottom_left
    const brTL = design.border_radius_tl || design.border_radius_top_left || '';
    const brTR = design.border_radius_tr || design.border_radius_top_right || '';
    const brBR = design.border_radius_br || design.border_radius_bottom_right || '';
    const brBL = design.border_radius_bl || design.border_radius_bottom_left || '';
    if (brTL || brTR || brBR || brBL) {
        const tl = (parseInt(brTL) || 0) + 'px';
        const tr = (parseInt(brTR) || 0) + 'px';
        const br = (parseInt(brBR) || 0) + 'px';
        const bl = (parseInt(brBL) || 0) + 'px';
        moduleStyles += 'border-radius:' + tl + ' ' + tr + ' ' + br + ' ' + bl + ';';
    } else if (design.borderRadius) {
        moduleStyles += 'border-radius:' + design.borderRadius + ';';
    }

    // Box shadow - support both naming conventions:
    // Modal uses: box_shadow_h, box_shadow_v
    // Legacy uses: box_shadow_horizontal, box_shadow_vertical
    if (design.box_shadow_enabled) {
        const bsH = parseInt(design.box_shadow_h) || parseInt(design.box_shadow_horizontal) || 0;
        const bsV = parseInt(design.box_shadow_v) || parseInt(design.box_shadow_vertical) || 4;
        const bsBlur = parseInt(design.box_shadow_blur) || 10;
        const bsSpread = parseInt(design.box_shadow_spread) || 0;
        const bsColor = design.box_shadow_color || 'rgba(0,0,0,0.1)';
        const bsInset = design.box_shadow_inset ? 'inset ' : '';
        moduleStyles += 'box-shadow:' + bsInset + bsH + 'px ' + bsV + 'px ' + bsBlur + 'px ' + bsSpread + 'px ' + bsColor + ';';
    }

    // Transform - support both transform_scale_x/y (modal) and transform_scale (legacy)
    const transformParts = [];
    // Scale - support both separate X/Y (modal) and combined (legacy)
    const scaleX = design.transform_scale_x !== undefined ? parseInt(design.transform_scale_x) : 100;
    const scaleY = design.transform_scale_y !== undefined ? parseInt(design.transform_scale_y) : 100;
    if (scaleX !== 100 || scaleY !== 100) {
        transformParts.push('scale(' + (scaleX / 100) + ', ' + (scaleY / 100) + ')');
    } else if (design.transform_scale && design.transform_scale !== '1' && design.transform_scale !== 1) {
        transformParts.push('scale(' + design.transform_scale + ')');
    }
    // Rotate
    if (design.transform_rotate && design.transform_rotate !== '0' && design.transform_rotate !== 0) {
        transformParts.push('rotate(' + parseInt(design.transform_rotate) + 'deg)');
    }
    // Skew - support combined skew for modal
    const skewX = parseInt(design.transform_skew_x) || 0;
    const skewY = parseInt(design.transform_skew_y) || 0;
    if (skewX !== 0 || skewY !== 0) {
        transformParts.push('skew(' + skewX + 'deg, ' + skewY + 'deg)');
    }
    // Translate
    const tx = parseInt(design.transform_translate_x) || 0;
    const ty = parseInt(design.transform_translate_y) || 0;
    if (tx !== 0 || ty !== 0) {
        transformParts.push('translate(' + tx + 'px, ' + ty + 'px)');
    }
    if (transformParts.length > 0) {
        moduleStyles += 'transform:' + transformParts.join(' ') + ';';
    }

    // Transform origin
    if (design.transform_origin) {
        moduleStyles += 'transform-origin:' + design.transform_origin + ';';
    }

    // Filter - support both naming conventions (modal uses filter_saturation, legacy uses filter_saturate)
    const filterParts = [];
    if (design.filter_blur && design.filter_blur !== '0' && design.filter_blur !== 0) {
        filterParts.push('blur(' + parseInt(design.filter_blur) + 'px)');
    }
    if (design.filter_brightness && design.filter_brightness !== '100' && design.filter_brightness !== 100) {
        filterParts.push('brightness(' + parseInt(design.filter_brightness) + '%)');
    }
    if (design.filter_contrast && design.filter_contrast !== '100' && design.filter_contrast !== 100) {
        filterParts.push('contrast(' + parseInt(design.filter_contrast) + '%)');
    }
    // Support both filter_saturation (modal) and filter_saturate (legacy)
    const saturation = design.filter_saturation || design.filter_saturate;
    if (saturation && saturation !== '100' && saturation !== 100) {
        filterParts.push('saturate(' + parseInt(saturation) + '%)');
    }
    if (design.filter_grayscale && design.filter_grayscale !== '0' && design.filter_grayscale !== 0) {
        filterParts.push('grayscale(' + parseInt(design.filter_grayscale) + '%)');
    }
    if (design.filter_hue_rotate && design.filter_hue_rotate !== '0' && design.filter_hue_rotate !== 0) {
        filterParts.push('hue-rotate(' + parseInt(design.filter_hue_rotate) + 'deg)');
    }
    if (design.filter_invert && design.filter_invert !== '0' && design.filter_invert !== 0) {
        filterParts.push('invert(' + parseInt(design.filter_invert) + '%)');
    }
    if (design.filter_sepia && design.filter_sepia !== '0' && design.filter_sepia !== 0) {
        filterParts.push('sepia(' + parseInt(design.filter_sepia) + '%)');
    }
    // Filter opacity (added for modal compatibility)
    if (design.filter_opacity && design.filter_opacity !== '100' && design.filter_opacity !== 100) {
        filterParts.push('opacity(' + parseInt(design.filter_opacity) + '%)');
    }
    if (filterParts.length > 0) {
        moduleStyles += 'filter:' + filterParts.join(' ') + ';';
    }

    // Position
    if (design.position && design.position !== 'static') {
        moduleStyles += 'position:' + design.position + ';';
        if (design.position_top) moduleStyles += 'top:' + design.position_top + ';';
        if (design.position_right) moduleStyles += 'right:' + design.position_right + ';';
        if (design.position_bottom) moduleStyles += 'bottom:' + design.position_bottom + ';';
        if (design.position_left) moduleStyles += 'left:' + design.position_left + ';';
    }

    // Z-index
    if (design.z_index && design.z_index !== 'auto') {
        moduleStyles += 'z-index:' + design.z_index + ';';
    }

    // Opacity
    if (design.opacity && design.opacity !== '1' && design.opacity !== 1) {
        moduleStyles += 'opacity:' + design.opacity + ';';
    }

    // Transition
    moduleStyles += 'transition:all 0.3s ease;';

    return moduleStyles;
};

// ═══════════════════════════════════════════════════════════
// ADVANCED SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderAdvancedSettings = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const settings = mod.design || {};
    let html = '';
    
    html += '<div class="tb-setting-group"><div class="tb-setting-label">CSS Class</div>';
    html += '<input type="text" class="tb-setting-input" value="' + (settings.cssClass || '') + '" placeholder="custom-class" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'cssClass\',this.value)"></div>';
    
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Custom ID</div>';
    html += '<input type="text" class="tb-setting-input" value="' + (settings.customId || '') + '" placeholder="element-id" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'customId\',this.value)"></div>';
    
    html += '<div class="tb-setting-group"><div class="tb-setting-label">Custom CSS</div>';
    html += '<textarea class="tb-setting-input" rows="4" style="font-family:monospace;font-size:11px" placeholder="color: red;" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'customCss\',this.value)">' + (settings.customCss || '') + '</textarea></div>';
    
    return html;
};

// ═══════════════════════════════════════════════════════════
// CORE UPDATE FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.updateModuleContent = function(sIdx, rIdx, cIdx, mIdx, key, value) {
    console.log('📝 updateModuleContent called:', sIdx, rIdx, cIdx, mIdx, key, value);
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) { console.log('❌ Module not found!'); return; }
    if (!mod.content) mod.content = {};
    mod.content[key] = value;
    console.log('✅ Content updated, mod.content:', JSON.stringify(mod.content));
    console.log('Calling saveToHistory...');
    this.saveToHistory();
    console.log('Calling renderCanvas...');
    this.renderCanvas();
    console.log('Calling selectModule...');
    this.selectModule(sIdx, rIdx, cIdx, mIdx);

    // ═══════════════════════════════════════════════════════════
    // FIX: Update modal preview if modal is open (for image selection, etc.)
    // ═══════════════════════════════════════════════════════════
    if (this.modalState && typeof this.updateModalPreview === 'function') {
        console.log('Updating modal preview after content change');
        this.updateModalPreview();
        // Also refresh the content settings tab if currently on content tab
        const contentTab = document.querySelector('.tb-modal-tab[data-tab="content"]');
        if (contentTab && contentTab.classList.contains('active')) {
            const settingsContainer = document.getElementById('tb-modal-settings');
            if (settingsContainer && typeof this.renderContentSettings === 'function') {
                settingsContainer.innerHTML = this.renderContentSettings(mod);
            }
        }
    }
    console.log('updateModuleContent complete');
};

TB.updateModuleSetting = function(sIdx, rIdx, cIdx, mIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// TYPOGRAPHY FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.updateTypography = function(sIdx, rIdx, cIdx, mIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    if (!mod.design.typography) mod.design.typography = {};
    mod.design.typography[key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateTypographyWithUnit = function(sIdx, rIdx, cIdx, mIdx, key, value, unit) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    if (!mod.design.typography) mod.design.typography = {};
    if (value === '' || value === null || value === undefined) {
        mod.design.typography[key] = '';
    } else {
        mod.design.typography[key] = value + (unit || '');
    }
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.getTypographyStyles = function(settings) {
    const typography = settings?.typography || {};
    let styles = '';
    if (typography.font_family) styles += 'font-family:' + typography.font_family + ';';
    if (typography.font_size) styles += 'font-size:' + typography.font_size + ';';
    if (typography.font_weight) styles += 'font-weight:' + typography.font_weight + ';';
    if (typography.line_height) styles += 'line-height:' + typography.line_height + ';';
    if (typography.letter_spacing) styles += 'letter-spacing:' + typography.letter_spacing + ';';
    if (typography.text_transform) styles += 'text-transform:' + typography.text_transform + ';';
    if (typography.color) styles += 'color:' + typography.color + ';';
    return styles;
};

TB.getElementTypographyStyles = function(settings, element) {
    const key = 'typography_' + element;
    const typography = settings?.[key] || {};
    let styles = '';
    if (typography.font_family) styles += 'font-family:' + typography.font_family + ';';
    if (typography.font_size) styles += 'font-size:' + typography.font_size + ';';
    if (typography.font_weight) styles += 'font-weight:' + typography.font_weight + ';';
    if (typography.line_height) styles += 'line-height:' + typography.line_height + ';';
    if (typography.letter_spacing) styles += 'letter-spacing:' + typography.letter_spacing + ';';
    if (typography.text_transform) styles += 'text-transform:' + typography.text_transform + ';';
    if (typography.color) styles += 'color:' + typography.color + ';';
    return styles;
};

TB.updateTypographyElement = function(sIdx, rIdx, cIdx, mIdx, element, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    const typoKey = 'typography_' + element;
    if (!mod.design[typoKey]) mod.design[typoKey] = {};
    mod.design[typoKey][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateTypographyElementWithUnit = function(sIdx, rIdx, cIdx, mIdx, element, key, value, unit) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    const typoKey = 'typography_' + element;
    if (!mod.design[typoKey]) mod.design[typoKey] = {};
    if (value === '' || value === null || value === undefined) {
        mod.design[typoKey][key] = '';
    } else {
        mod.design[typoKey][key] = value + (unit || '');
    }
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// Responsive typography update
TB.updateResponsiveTypographyWithUnit = function(sIdx, rIdx, cIdx, mIdx, element, key, value, unit) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    const typoKey = 'typography_' + element;
    if (!mod.design[typoKey]) mod.design[typoKey] = {};

    // Determine the actual key based on current device
    let actualKey = key;
    if (this.currentDevice !== 'desktop') {
        actualKey = key + '_' + this.currentDevice;
    }

    if (value === '' || value === null || value === undefined) {
        mod.design[typoKey][actualKey] = '';
    } else {
        mod.design[typoKey][actualKey] = value + (unit || '');
    }
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleTypographySection = function(headerEl) {
    const section = headerEl.closest('.tb-typography-section');
    const content = section.querySelector('.tb-typography-content');
    const toggle = section.querySelector('.tb-typography-toggle');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        toggle.textContent = '▼';
    } else {
        content.style.display = 'none';
        toggle.textContent = '▶';
    }
};

// ═══════════════════════════════════════════════════════════
// SPACING FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.updateSpacing = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    if (value !== '' && value !== null && value !== undefined) {
        const numVal = parseFloat(value);
        if (!isNaN(numVal) && !String(value).includes('px')) {
            value = numVal + 'px';
        }
    }

    mod.design[property] = value || '0px';
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleSpacingLink = function(sIdx, rIdx, cIdx, mIdx, type) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const linkKey = type + '_linked';
    mod.design[linkKey] = !mod.design[linkKey];

    if (mod.design[linkKey]) {
        const topValue = mod.design[type + '_top'] || '0px';
        mod.design[type + '_right'] = topValue;
        mod.design[type + '_bottom'] = topValue;
        mod.design[type + '_left'] = topValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.handleSpacingChange = function(sIdx, rIdx, cIdx, mIdx, type, side, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const linkKey = type + '_linked';
    const isLinked = mod.design[linkKey];

    if (value !== '' && value !== null && value !== undefined) {
        const numVal = parseFloat(value);
        if (!isNaN(numVal) && !String(value).includes('px')) {
            value = numVal + 'px';
        }
    } else {
        value = '0px';
    }

    if (isLinked) {
        mod.design[type + '_top'] = value;
        mod.design[type + '_right'] = value;
        mod.design[type + '_bottom'] = value;
        mod.design[type + '_left'] = value;
    } else {
        mod.design[type + '_' + side] = value;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleSpacingSection = function(element) {
    const section = element.closest('.tb-spacing-section');
    if (section) {
        section.classList.toggle('collapsed');
    }
};

// Responsive spacing helpers
TB.getResponsiveSpacingValue = function(settings, type, side) {
    const baseKey = type + '_' + side;
    if (this.currentDevice === 'desktop') {
        return parseInt(settings[baseKey]) || 0;
    }
    const deviceKey = baseKey + '_' + this.currentDevice;
    const deviceValue = settings[deviceKey];
    if (deviceValue !== undefined && deviceValue !== '') {
        return parseInt(deviceValue) || 0;
    }
    return parseInt(settings[baseKey]) || 0;
};

TB.isResponsiveSpacingLinked = function(settings, type) {
    if (this.currentDevice === 'desktop') {
        return settings[type + '_linked'] || false;
    }
    const deviceLinkKey = type + '_linked_' + this.currentDevice;
    if (settings[deviceLinkKey] !== undefined) {
        return settings[deviceLinkKey];
    }
    return settings[type + '_linked'] || false;
};

TB.handleResponsiveSpacingChange = function(sIdx, rIdx, cIdx, mIdx, type, side, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const isLinked = this.isResponsiveSpacingLinked(mod.design, type);

    if (value !== '' && value !== null && value !== undefined) {
        const numVal = parseFloat(value);
        if (!isNaN(numVal) && !String(value).includes('px')) {
            value = numVal + 'px';
        }
    } else {
        value = '0px';
    }

    const suffix = this.currentDevice === 'desktop' ? '' : '_' + this.currentDevice;

    if (isLinked) {
        mod.design[type + '_top' + suffix] = value;
        mod.design[type + '_right' + suffix] = value;
        mod.design[type + '_bottom' + suffix] = value;
        mod.design[type + '_left' + suffix] = value;
    } else {
        mod.design[type + '_' + side + suffix] = value;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleResponsiveSpacingLink = function(sIdx, rIdx, cIdx, mIdx, type) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const suffix = this.currentDevice === 'desktop' ? '' : '_' + this.currentDevice;
    const linkKey = type + '_linked' + suffix;

    mod.design[linkKey] = !mod.design[linkKey];

    if (mod.design[linkKey]) {
        const topKey = type + '_top' + suffix;
        const topValue = mod.design[topKey] || '0px';
        mod.design[type + '_right' + suffix] = topValue;
        mod.design[type + '_bottom' + suffix] = topValue;
        mod.design[type + '_left' + suffix] = topValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.getDeviceIcon = function(device) {
    switch(device) {
        case 'tablet': return '📱';
        case 'mobile': return '📱';
        default: return '🖥️';
    }
};

// ═══════════════════════════════════════════════════════════
// BORDER FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.toggleBorderSection = function(element) {
    const section = element.closest('.tb-border-section');
    if (section) {
        section.classList.toggle('collapsed');
    }
};

TB.toggleBorderWidthLink = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    mod.design.border_width_linked = !mod.design.border_width_linked;

    if (mod.design.border_width_linked) {
        const topValue = mod.design.border_width_top || '0px';
        mod.design.border_width_right = topValue;
        mod.design.border_width_bottom = topValue;
        mod.design.border_width_left = topValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleBorderRadiusLink = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    mod.design.border_radius_linked = !mod.design.border_radius_linked;

    if (mod.design.border_radius_linked) {
        const tlValue = mod.design.border_radius_top_left || '0px';
        mod.design.border_radius_top_right = tlValue;
        mod.design.border_radius_bottom_right = tlValue;
        mod.design.border_radius_bottom_left = tlValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateBorder = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.handleBorderWidthChange = function(sIdx, rIdx, cIdx, mIdx, side, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const numValue = parseInt(value) || 0;
    const pxValue = numValue + 'px';

    if (mod.design.border_width_linked) {
        mod.design.border_width_top = pxValue;
        mod.design.border_width_right = pxValue;
        mod.design.border_width_bottom = pxValue;
        mod.design.border_width_left = pxValue;
    } else {
        mod.design['border_width_' + side] = pxValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.handleBorderRadiusChange = function(sIdx, rIdx, cIdx, mIdx, corner, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const numValue = parseInt(value) || 0;
    const pxValue = numValue + 'px';

    if (mod.design.border_radius_linked) {
        mod.design.border_radius_tl = pxValue;
        mod.design.border_radius_tr = pxValue;
        mod.design.border_radius_br = pxValue;
        mod.design.border_radius_bl = pxValue;
    } else {
        mod.design['border_radius_' + corner] = pxValue;
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.toggleBorderLink = function(sIdx, rIdx, cIdx, mIdx, type) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const linkKey = type === 'width' ? 'border_width_linked' : 'border_radius_linked';
    const isLinked = !mod.design[linkKey];
    mod.design[linkKey] = isLinked;

    if (isLinked) {
        if (type === 'width') {
            const values = [
                parseInt(mod.design.border_width_top) || 0,
                parseInt(mod.design.border_width_right) || 0,
                parseInt(mod.design.border_width_bottom) || 0,
                parseInt(mod.design.border_width_left) || 0
            ];
            const syncValue = values.find(v => v > 0) || 0;
            const pxValue = syncValue + 'px';
            mod.design.border_width_top = pxValue;
            mod.design.border_width_right = pxValue;
            mod.design.border_width_bottom = pxValue;
            mod.design.border_width_left = pxValue;
        } else {
            const values = [
                parseInt(mod.design.border_radius_tl) || 0,
                parseInt(mod.design.border_radius_tr) || 0,
                parseInt(mod.design.border_radius_br) || 0,
                parseInt(mod.design.border_radius_bl) || 0
            ];
            const syncValue = values.find(v => v > 0) || 0;
            const pxValue = syncValue + 'px';
            mod.design.border_radius_tl = pxValue;
            mod.design.border_radius_tr = pxValue;
            mod.design.border_radius_br = pxValue;
            mod.design.border_radius_bl = pxValue;
        }
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// BOX SHADOW FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.toggleShadowSection = function(element) {
    const section = element.closest('.tb-shadow-section');
    if (section) {
        section.classList.toggle('collapsed');
    }
};

TB.updateBoxShadow = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    if (property === 'box_shadow_enabled' || property === 'box_shadow_inset') {
        mod.design[property] = value === true || value === 'true';
    } else if (property === 'box_shadow_color') {
        mod.design[property] = value;
    } else {
        const numValue = parseInt(value) || 0;
        mod.design[property] = numValue + 'px';
    }

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateBoxShadowSlider = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const numValue = parseInt(value) || 0;
    mod.design[property] = numValue + 'px';

    const controlsContainer = document.getElementById('shadow-controls-' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx);
    if (controlsContainer) {
        const rows = controlsContainer.querySelectorAll('.tb-shadow-control-row');
        rows.forEach(row => {
            const range = row.querySelector('input[type="range"]');
            const number = row.querySelector('input[type="number"]');
            if (range && number && parseInt(range.value) === numValue) {
                number.value = numValue;
            }
        });

        const previewBox = document.getElementById('shadow-preview-' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx);
        if (previewBox) {
            previewBox.style.boxShadow = this.getBoxShadowCSS(mod.design);
        }
    }

    clearTimeout(this.shadowUpdateTimer);
    this.shadowUpdateTimer = setTimeout(() => {
        this.saveToHistory();
        this.renderCanvas();
    }, 150);
};

TB.updateBoxShadowColor = function(sIdx, rIdx, cIdx, mIdx, hexColor) {
    const rgba = this.hexToRgba(hexColor, 0.1);
    this.updateBoxShadow(sIdx, rIdx, cIdx, mIdx, 'box_shadow_color', rgba);
};

TB.applyBoxShadowPreset = function(sIdx, rIdx, cIdx, mIdx, preset) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design || Array.isArray(mod.design)) mod.design = {};

    const presets = {
        'none': { h: 0, v: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        'subtle': { h: 0, v: 2, blur: 4, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },
        'medium': { h: 0, v: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.15)', inset: false },
        'large': { h: 0, v: 10, blur: 30, spread: 0, color: 'rgba(0,0,0,0.2)', inset: false },
        'sharp': { h: 0, v: 2, blur: 8, spread: 0, color: 'rgba(0,0,0,0.25)', inset: false },
        'soft': { h: 0, v: 20, blur: 50, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },
        'inset_subtle': { h: 0, v: 2, blur: 4, spread: 0, color: 'rgba(0,0,0,0.1)', inset: true }
    };

    if (preset && presets[preset]) {
        const p = presets[preset];
        mod.design.box_shadow_horizontal = p.h + 'px';
        mod.design.box_shadow_vertical = p.v + 'px';
        mod.design.box_shadow_blur = p.blur + 'px';
        mod.design.box_shadow_spread = p.spread + 'px';
        mod.design.box_shadow_color = p.color;
        mod.design.box_shadow_inset = p.inset;

        if (preset !== 'none') {
            mod.design.box_shadow_enabled = true;
        }

        this.saveToHistory();
        this.renderCanvas();
        this.selectModule(sIdx, rIdx, cIdx, mIdx);
    }
};

TB.getBoxShadowCSS = function(settings) {
    if (!settings || !settings.box_shadow_enabled) {
        return 'none';
    }

    const h = parseInt(settings.box_shadow_horizontal) || 0;
    const v = parseInt(settings.box_shadow_vertical) || 4;
    const blur = parseInt(settings.box_shadow_blur) || 10;
    const spread = parseInt(settings.box_shadow_spread) || 0;
    const color = settings.box_shadow_color || 'rgba(0,0,0,0.1)';
    const inset = settings.box_shadow_inset ? 'inset ' : '';

    return inset + h + 'px ' + v + 'px ' + blur + 'px ' + spread + 'px ' + color;
};

TB.shadowUpdateTimer = null;

// ═══════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.rgbaToHex = function(rgba) {
    if (!rgba || rgba.startsWith('#')) return rgba || '#000000';
    const match = rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!match) return '#000000';
    const r = parseInt(match[1]).toString(16).padStart(2, '0');
    const g = parseInt(match[2]).toString(16).padStart(2, '0');
    const b = parseInt(match[3]).toString(16).padStart(2, '0');
    return '#' + r + g + b;
};

TB.hexToRgba = function(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
};

TB.escapeHtml = function(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

TB.renderColorPicker = function(label, value, onchangeCallback, defaultColor) {
    const color = value || defaultColor || '#333333';
    const hexColor = color.startsWith('#') ? color : this.rgbaToHex(color);
    
    let html = '<div class="tb-setting-group"><div class="tb-setting-label">' + this.escapeHtml(label) + '</div>';
    html += '<div class="tb-color-picker-wrap" style="display:flex;gap:8px;align-items:center">';
    html += '<input type="color" class="tb-color-swatch" value="' + hexColor + '" style="width:40px;height:32px;border:none;cursor:pointer;border-radius:4px" onchange="' + onchangeCallback.replace('VALUE', 'this.value') + ';this.nextElementSibling.value=this.value">';
    html += '<input type="text" class="tb-setting-input" value="' + this.escapeHtml(color) + '" style="flex:1" onchange="' + onchangeCallback.replace('VALUE', 'this.value') + '">';
    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// SLIDER SLIDE MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addSliderSlide = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'slider') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.slides) mod.content.slides = [];
    const newSlideNum = mod.content.slides.length + 1;
    mod.content.slides.push({
        title: 'Slide ' + newSlideNum,
        text: 'Description for slide ' + newSlideNum,
        image: '',
        button_text: 'Learn More',
        button_url: '#'
    });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeSliderSlide = function(sIdx, rIdx, cIdx, mIdx, slideIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'slider' || !mod.content?.slides) return;
    if (mod.content.slides.length <= 1) return;
    mod.content.slides.splice(slideIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateSliderSlide = function(sIdx, rIdx, cIdx, mIdx, slideIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'slider' || !mod.content?.slides?.[slideIdx]) return;
    mod.content.slides[slideIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveSliderSlide = function(sIdx, rIdx, cIdx, mIdx, slideIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'slider' || !mod.content?.slides) return;
    const newIdx = slideIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.slides.length) return;
    const slides = mod.content.slides;
    [slides[slideIdx], slides[newIdx]] = [slides[newIdx], slides[slideIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// ACCORDION ITEM MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addAccordionItem = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'accordion') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.items) mod.content.items = [];
    const newItemNum = mod.content.items.length + 1;
    mod.content.items.push({
        title: 'Accordion Item ' + newItemNum,
        content: 'Content for accordion item ' + newItemNum
    });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeAccordionItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'accordion' || !mod.content?.items) return;
    if (mod.content.items.length <= 1) return;
    mod.content.items.splice(itemIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateAccordionItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'accordion' || !mod.content?.items?.[itemIdx]) return;
    mod.content.items[itemIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveAccordionItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'accordion' || !mod.content?.items) return;
    const newIdx = itemIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.items.length) return;
    const items = mod.content.items;
    [items[itemIdx], items[newIdx]] = [items[newIdx], items[itemIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// TOGGLE MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addToggleItem = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.items) mod.content.items = [];
    const newItemNum = mod.content.items.length + 1;
    mod.content.items.push({
        title: 'Toggle Item ' + newItemNum,
        content: 'Content for toggle item ' + newItemNum
    });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeToggleItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle' || !mod.content?.items) return;
    if (mod.content.items.length <= 1) return;
    mod.content.items.splice(itemIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateToggleItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle' || !mod.content?.items?.[itemIdx]) return;
    mod.content.items[itemIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// TABS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addTab = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'tabs') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.tabs) mod.content.tabs = [];
    const newTabNum = mod.content.tabs.length + 1;
    mod.content.tabs.push({
        title: 'Tab ' + newTabNum,
        content: 'Content for tab ' + newTabNum
    });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeTab = function(sIdx, rIdx, cIdx, mIdx, tabIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'tabs' || !mod.content?.tabs) return;
    if (mod.content.tabs.length <= 1) return;
    mod.content.tabs.splice(tabIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateTab = function(sIdx, rIdx, cIdx, mIdx, tabIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'tabs' || !mod.content?.tabs?.[tabIdx]) return;
    mod.content.tabs[tabIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveTab = function(sIdx, rIdx, cIdx, mIdx, tabIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'tabs' || !mod.content?.tabs) return;
    const newIdx = tabIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.tabs.length) return;
    const tabs = mod.content.tabs;
    [tabs[tabIdx], tabs[newIdx]] = [tabs[newIdx], tabs[tabIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// LIST ITEM MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addListItem = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'list') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.items || !Array.isArray(mod.content.items)) mod.content.items = [];

    // Normalize to strings first
    mod.content.items = mod.content.items.map(item =>
        typeof item === 'string' ? item : (item?.text || '')
    );

    const newNum = mod.content.items.length + 1;
    mod.content.items.push('New item ' + newNum);
    console.log('➕ addListItem: items now =', mod.content.items);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeListItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'list' || !mod.content?.items) return;
    if (mod.content.items.length <= 1) return;

    // Normalize to strings first
    mod.content.items = mod.content.items.map(item =>
        typeof item === 'string' ? item : (item?.text || '')
    );

    mod.content.items.splice(itemIdx, 1);
    console.log('➖ removeListItem: items now =', mod.content.items);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateListItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, value) {
    console.log('📝 updateListItem called:', {sIdx, rIdx, cIdx, mIdx, itemIdx, value});
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) { console.log('❌ Module not found!'); return; }
    if (mod.type !== 'list') { console.log('❌ Not a list module:', mod.type); return; }

    // Initialize content and items if not exist
    if (!mod.content) mod.content = {};
    if (!mod.content.items || !Array.isArray(mod.content.items)) {
        console.log('⚠️ Items not array, initializing...');
        mod.content.items = [];
    }

    // Normalize items to strings if needed (convert from legacy object format)
    mod.content.items = mod.content.items.map(item =>
        typeof item === 'string' ? item : (item?.text || '')
    );

    // Ensure items array has at least itemIdx+1 elements
    while (mod.content.items.length <= itemIdx) {
        mod.content.items.push('');
    }

    mod.content.items[itemIdx] = value;
    console.log('✅ Updated items:', mod.content.items);

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveListItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'list' || !mod.content?.items) return;
    const newIdx = itemIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.items.length) return;
    const items = mod.content.items;
    [items[itemIdx], items[newIdx]] = [items[newIdx], items[itemIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// PRICING FEATURES MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addPricingFeature = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'pricing') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.features) mod.content.features = [];
    mod.content.features.push({ text: 'New feature', included: true });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removePricingFeature = function(sIdx, rIdx, cIdx, mIdx, featureIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'pricing' || !mod.content?.features) return;
    mod.content.features.splice(featureIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updatePricingFeature = function(sIdx, rIdx, cIdx, mIdx, featureIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'pricing' || !mod.content?.features?.[featureIdx]) return;
    mod.content.features[featureIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// GALLERY IMAGE MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addGalleryImage = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'gallery') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.images) mod.content.images = [];
    mod.content.images.push({ src: '', alt: '', caption: '' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeGalleryImage = function(sIdx, rIdx, cIdx, mIdx, imageIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'gallery' || !mod.content?.images) return;
    mod.content.images.splice(imageIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateGalleryImage = function(sIdx, rIdx, cIdx, mIdx, imageIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'gallery' || !mod.content?.images?.[imageIdx]) return;
    mod.content.images[imageIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveGalleryImage = function(sIdx, rIdx, cIdx, mIdx, imageIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'gallery' || !mod.content?.images) return;
    const newIdx = imageIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.images.length) return;
    const images = mod.content.images;
    [images[imageIdx], images[newIdx]] = [images[newIdx], images[imageIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// SOCIAL NETWORKS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addSocialNetwork = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.networks) mod.content.networks = [];
    mod.content.networks.push({ platform: 'facebook', url: '#' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeSocialNetwork = function(sIdx, rIdx, cIdx, mIdx, netIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social' || !mod.content?.networks) return;
    mod.content.networks.splice(netIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateSocialNetwork = function(sIdx, rIdx, cIdx, mIdx, netIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social' || !mod.content?.networks?.[netIdx]) return;
    mod.content.networks[netIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// SOCIAL FOLLOW NETWORKS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addSocialFollowNetwork = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social_follow') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.networks) mod.content.networks = [];
    mod.content.networks.push({ platform: 'facebook', url: '#', enabled: true });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeSocialFollowNetwork = function(sIdx, rIdx, cIdx, mIdx, netIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social_follow' || !mod.content?.networks) return;
    mod.content.networks.splice(netIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateSocialFollowNetwork = function(sIdx, rIdx, cIdx, mIdx, netIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'social_follow' || !mod.content?.networks?.[netIdx]) return;
    mod.content.networks[netIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// MENU ITEMS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addMenuItem = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'menu') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.items) mod.content.items = [];
    mod.content.items.push({ text: 'New Link', url: '#' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeMenuItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'menu' || !mod.content?.items) return;
    mod.content.items.splice(itemIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateMenuItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'menu' || !mod.content?.items?.[itemIdx]) return;
    mod.content.items[itemIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveMenuItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'menu' || !mod.content?.items) return;
    const newIdx = itemIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.items.length) return;
    const items = mod.content.items;
    [items[itemIdx], items[newIdx]] = [items[newIdx], items[itemIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// BAR COUNTERS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addBarCounter = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'bar_counters') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.bars) mod.content.bars = [];
    mod.content.bars.push({ label: 'New Skill', percent: 75, color: '#0073e6' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeBarCounter = function(sIdx, rIdx, cIdx, mIdx, barIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'bar_counters' || !mod.content?.bars) return;
    mod.content.bars.splice(barIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateBarCounter = function(sIdx, rIdx, cIdx, mIdx, barIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'bar_counters' || !mod.content?.bars?.[barIdx]) return;
    mod.content.bars[barIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveBarCounter = function(sIdx, rIdx, cIdx, mIdx, barIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'bar_counters' || !mod.content?.bars) return;
    const newIdx = barIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.bars.length) return;
    const bars = mod.content.bars;
    [bars[barIdx], bars[newIdx]] = [bars[newIdx], bars[barIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// TOGGLE ITEMS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addToggleItem = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.items) mod.content.items = [];
    const newNum = mod.content.items.length + 1;
    mod.content.items.push({ title: 'Toggle ' + newNum, content: 'Content for toggle ' + newNum });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeToggleItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle' || !mod.content?.items) return;
    if (mod.content.items.length <= 1) return;
    mod.content.items.splice(itemIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateToggleItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle' || !mod.content?.items?.[itemIdx]) return;
    mod.content.items[itemIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveToggleItem = function(sIdx, rIdx, cIdx, mIdx, itemIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'toggle' || !mod.content?.items) return;
    const newIdx = itemIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.items.length) return;
    const items = mod.content.items;
    [items[itemIdx], items[newIdx]] = [items[newIdx], items[itemIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// HERO BUTTONS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addHeroButton = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'hero') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.buttons) mod.content.buttons = [];
    mod.content.buttons.push({ text: 'Button', url: '#', style: 'primary' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeHeroButton = function(sIdx, rIdx, cIdx, mIdx, btnIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'hero' || !mod.content?.buttons) return;
    mod.content.buttons.splice(btnIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateHeroButton = function(sIdx, rIdx, cIdx, mIdx, btnIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'hero' || !mod.content?.buttons?.[btnIdx]) return;
    mod.content.buttons[btnIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// VIDEO SLIDER MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addVideoSlide = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'video_slider') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.videos) mod.content.videos = [];
    const newNum = mod.content.videos.length + 1;
    mod.content.videos.push({ title: 'Video ' + newNum, url: '', thumbnail: '' });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeVideoSlide = function(sIdx, rIdx, cIdx, mIdx, videoIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'video_slider' || !mod.content?.videos) return;
    if (mod.content.videos.length <= 1) return;
    mod.content.videos.splice(videoIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateVideoSlide = function(sIdx, rIdx, cIdx, mIdx, videoIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'video_slider' || !mod.content?.videos?.[videoIdx]) return;
    mod.content.videos[videoIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveVideoSlide = function(sIdx, rIdx, cIdx, mIdx, videoIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'video_slider' || !mod.content?.videos) return;
    const newIdx = videoIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.videos.length) return;
    const videos = mod.content.videos;
    [videos[videoIdx], videos[newIdx]] = [videos[newIdx], videos[videoIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// FORM FIELDS MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addFormField = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'contact_form') return;
    if (!mod.content) mod.content = {};
    if (!mod.content.fields) mod.content.fields = [];
    mod.content.fields.push({ type: 'text', label: 'New Field', name: 'field_' + Date.now(), required: false });
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.removeFormField = function(sIdx, rIdx, cIdx, mIdx, fieldIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'contact_form' || !mod.content?.fields) return;
    mod.content.fields.splice(fieldIdx, 1);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateFormField = function(sIdx, rIdx, cIdx, mIdx, fieldIdx, key, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'contact_form' || !mod.content?.fields?.[fieldIdx]) return;
    mod.content.fields[fieldIdx][key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.moveFormField = function(sIdx, rIdx, cIdx, mIdx, fieldIdx, direction) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod || mod.type !== 'contact_form' || !mod.content?.fields) return;
    const newIdx = fieldIdx + direction;
    if (newIdx < 0 || newIdx >= mod.content.fields.length) return;
    const fields = mod.content.fields;
    [fields[fieldIdx], fields[newIdx]] = [fields[newIdx], fields[fieldIdx]];
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// AI CONTENT GENERATION
// ═══════════════════════════════════════════════════════════
TB.handleAIGenerate = async function(type, field, sIdx, rIdx, cIdx, mIdx, button) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    const context = prompt('Describe what you want to generate:');
    if (!context) return;

    if (button) {
        button.disabled = true;
        button.textContent = '⏳';
    }

    try {
        const response = await fetch('/admin/theme-builder/ai/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                type: type,
                field: field,
                context: context,
                current_content: mod.content?.[field] || ''
            })
        });

        const data = await response.json();
        if (data.success && data.content) {
            this.updateModuleContent(sIdx, rIdx, cIdx, mIdx, field, data.content);
            this.showToast('Content generated!', 'success');
        } else {
            this.showToast(data.error || 'Generation failed', 'error');
        }
    } catch (err) {
        console.error('AI Generate error:', err);
        this.showToast('Network error', 'error');
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = '✨ AI';
        }
    }
};

// ═══════════════════════════════════════════════════════════
// MODULE DESIGN FUNCTIONS
// ═══════════════════════════════════════════════════════════

TB.updateModuleDesign = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// TRANSFORM FUNCTIONS
// ═══════════════════════════════════════════════════════════

TB.toggleTransformSection = function(el) {
    const section = el.closest('.tb-transform-section') || el.parentElement;
    if (section) {
        section.classList.toggle('collapsed');
        const body = section.querySelector('.tb-transform-section-body');
        if (body) body.style.display = section.classList.contains('collapsed') ? 'none' : 'block';
    }
};

TB.setTransformOrigin = function(sIdx, rIdx, cIdx, mIdx, x, y) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design.transform_origin = x + ' ' + y;
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.resetTransforms = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    
    // Reset all transform properties
    mod.design.rotate = '0deg';
    mod.design.scale_x = '1';
    mod.design.scale_y = '1';
    mod.design.skew_x = '0deg';
    mod.design.skew_y = '0deg';
    mod.design.translate_x = '0px';
    mod.design.translate_y = '0px';
    mod.design.transform_origin = 'center center';
    
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateTransform = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// FILTER FUNCTIONS
// ═══════════════════════════════════════════════════════════

TB.toggleFilterSection = function(el) {
    const section = el.closest('.tb-filter-section') || el.parentElement;
    if (section) {
        section.classList.toggle('collapsed');
        const body = section.querySelector('.tb-filter-section-body');
        if (body) body.style.display = section.classList.contains('collapsed') ? 'none' : 'block';
    }
};

TB.resetFilters = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    
    // Reset all filter properties
    mod.design.filter_blur = '0px';
    mod.design.filter_brightness = '100%';
    mod.design.filter_contrast = '100%';
    mod.design.filter_saturate = '100%';
    mod.design.filter_grayscale = '0%';
    mod.design.filter_sepia = '0%';
    mod.design.filter_hue_rotate = '0deg';
    mod.design.filter_invert = '0%';
    
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

TB.updateFilter = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// POSITION FUNCTIONS
// ═══════════════════════════════════════════════════════════

TB.togglePositionSection = function(el) {
    const section = el.closest('.tb-position-section') || el.parentElement;
    if (section) {
        section.classList.toggle('collapsed');
        const body = section.querySelector('.tb-position-section-body');
        if (body) body.style.display = section.classList.contains('collapsed') ? 'none' : 'block';
    }
};

TB.updatePosition = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// ANIMATION FUNCTIONS
// ═══════════════════════════════════════════════════════════

TB.toggleAnimationSection = function(el) {
    const section = el.closest('.tb-animation-section') || el.parentElement;
    if (section) {
        section.classList.toggle('collapsed');
        const body = section.querySelector('.tb-animation-section-body');
        if (body) body.style.display = section.classList.contains('collapsed') ? 'none' : 'block';
    }
};

TB.previewAnimation = function(sIdx, rIdx, cIdx, mIdx) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    
    const moduleEl = document.querySelector('[data-module-path="' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx + '"]');
    if (!moduleEl) return;
    
    const animation = mod.design?.animation || 'fadeIn';
    const duration = mod.design?.animation_duration || '1s';
    const easing = mod.design?.animation_easing || 'ease-out';
    
    // Remove and re-add animation to replay
    moduleEl.style.animation = 'none';
    moduleEl.offsetHeight; // Trigger reflow
    moduleEl.style.animation = animation + ' ' + duration + ' ' + easing;
    
    // Reset after animation completes
    setTimeout(() => {
        moduleEl.style.animation = '';
    }, parseFloat(duration) * 1000 + 100);
};

TB.updateAnimation = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.design) mod.design = {};
    mod.design[property] = value;
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// END OF TB-HELPERS.JS
// ═══════════════════════════════════════════════════════════
console.log('TB 3.0: tb-helpers.js loaded');
