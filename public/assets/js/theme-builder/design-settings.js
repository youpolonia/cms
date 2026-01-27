/**
 * Theme Builder 3.0 - Design Settings JavaScript
 * 
 * Provides advanced design settings: Spacing, Border, Shadow, Hover, Transform, etc.
 * Extends TB object with design settings methods.
 * 
 * @package ThemeBuilder
 * @version 3.0
 */

// Extend TB object with Design Settings methods
Object.assign(TB, {

    // Combined Spacing Box (Divi-style margin/padding editor)
    renderCombinedSpacingBox(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        const mt = settings.marginTop || '';
        const mr = settings.marginRight || '';
        const mb = settings.marginBottom || '';
        const ml = settings.marginLeft || '';
        const pt = settings.paddingTop || '';
        const pr = settings.paddingRight || '';
        const pb = settings.paddingBottom || '';
        const pl = settings.paddingLeft || '';

        let html = '<div class="tb-spacing-box-container">';
        html += '<div class="tb-spacing-box-outer">';
        html += '<div class="tb-spacing-label-margin">MARGIN</div>';
        
        html += '<input type="text" class="tb-spacing-input tb-spacing-margin-top" value="' + mt + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'marginTop\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-margin-right" value="' + mr + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'marginRight\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-margin-bottom" value="' + mb + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'marginBottom\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-margin-left" value="' + ml + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'marginLeft\',this.value)">';

        html += '<div class="tb-spacing-box-inner">';
        html += '<div class="tb-spacing-label-padding">PADDING</div>';
        
        html += '<input type="text" class="tb-spacing-input tb-spacing-padding-top" value="' + pt + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'paddingTop\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-padding-right" value="' + pr + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'paddingRight\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-padding-bottom" value="' + pb + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'paddingBottom\',this.value)">';
        html += '<input type="text" class="tb-spacing-input tb-spacing-padding-left" value="' + pl + '" placeholder="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'paddingLeft\',this.value)">';

        html += '<div class="tb-spacing-content-center"></div>';
        html += '</div></div></div>';

        return html;
    },

    // Border Settings
    renderBorderSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        const bw = settings.borderWidth || '0';
        const bs = settings.borderStyle || 'solid';
        const bc = settings.borderColor || '#333333';
        const br = settings.borderRadius || '0';

        let html = '';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Border Width</div>';
        html += '<input type="text" class="tb-setting-input" value="' + bw + '" placeholder="0px" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'borderWidth\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Border Style</div>';
        html += '<select class="tb-setting-input" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'borderStyle\',this.value)">';
        ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge'].forEach(s => {
            html += '<option value="' + s + '"' + (bs === s ? ' selected' : '') + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
        });
        html += '</select></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Border Color</div>';
        html += '<div style="display:flex;gap:8px;align-items:center">';
        html += '<input type="color" class="tb-setting-input" style="width:50px;padding:2px" value="' + bc + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'borderColor\',this.value)">';
        html += '<input type="text" class="tb-setting-input" style="flex:1" value="' + bc + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'borderColor\',this.value)">';
        html += '</div></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Border Radius</div>';
        html += '<input type="text" class="tb-setting-input" value="' + br + '" placeholder="0px" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'borderRadius\',this.value)"></div>';

        return html;
    },

    // Box Shadow Settings
    renderBoxShadowSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        const sh = settings.boxShadowH || '0';
        const sv = settings.boxShadowV || '4';
        const sb = settings.boxShadowBlur || '10';
        const ss = settings.boxShadowSpread || '0';
        const sc = settings.boxShadowColor || 'rgba(0,0,0,0.1)';
        const si = settings.boxShadowInset || false;

        let html = '';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Horizontal Offset</div>';
        html += '<input type="number" class="tb-setting-input" value="' + sh + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowH\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Vertical Offset</div>';
        html += '<input type="number" class="tb-setting-input" value="' + sv + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowV\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Blur</div>';
        html += '<input type="number" class="tb-setting-input" value="' + sb + '" min="0" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowBlur\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Spread</div>';
        html += '<input type="number" class="tb-setting-input" value="' + ss + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowSpread\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Shadow Color</div>';
        html += '<input type="text" class="tb-setting-input" value="' + sc + '" placeholder="rgba(0,0,0,0.1)" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowColor\',this.value)"></div>';

        html += '<div class="tb-setting-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer">';
        html += '<input type="checkbox"' + (si ? ' checked' : '') + ' onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'boxShadowInset\',this.checked)">';
        html += '<span>Inset Shadow</span></label></div>';

        return html;
    },

    // Hover Effects Settings
    renderHoverEffectsSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        const enabled = settings.hoverEnabled || false;
        const duration = settings.hoverDuration || '0.3';
        const bgHover = settings.backgroundColorHover || '';
        const colorHover = settings.colorHover || '';
        const scaleHover = settings.scaleHover || '1';

        let html = '<div class="tb-design-section">';
        html += '<div class="tb-design-section-header" onclick="TB.toggleDesignSection(this)">';
        html += '<span>👆 Hover Effects</span><span class="tb-design-section-toggle">▼</span></div>';
        html += '<div class="tb-design-section-body">';

        html += '<div class="tb-setting-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer">';
        html += '<input type="checkbox"' + (enabled ? ' checked' : '') + ' onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hoverEnabled\',this.checked)">';
        html += '<span>Enable Hover Effects</span></label></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Transition Duration (s)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + duration + '" step="0.1" min="0" max="2" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'hoverDuration\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Background Color (Hover)</div>';
        html += '<div style="display:flex;gap:8px;align-items:center">';
        html += '<input type="color" class="tb-setting-input" style="width:50px;padding:2px" value="' + (bgHover || '#ffffff') + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'backgroundColorHover\',this.value)">';
        html += '<input type="text" class="tb-setting-input" style="flex:1" value="' + bgHover + '" placeholder="transparent" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'backgroundColorHover\',this.value)">';
        html += '</div></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Color (Hover)</div>';
        html += '<div style="display:flex;gap:8px;align-items:center">';
        html += '<input type="color" class="tb-setting-input" style="width:50px;padding:2px" value="' + (colorHover || '#000000') + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'colorHover\',this.value)">';
        html += '<input type="text" class="tb-setting-input" style="flex:1" value="' + colorHover + '" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'colorHover\',this.value)">';
        html += '</div></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Scale (Hover)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + scaleHover + '" step="0.05" min="0.5" max="2" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'scaleHover\',this.value)"></div>';

        html += '</div></div>';
        return html;
    },

    // Transform Settings
    renderTransformSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        let html = '<div class="tb-design-section collapsed">';
        html += '<div class="tb-design-section-header" onclick="TB.toggleDesignSection(this)">';
        html += '<span>🔄 Transform</span><span class="tb-design-section-toggle">▶</span></div>';
        html += '<div class="tb-design-section-body" style="display:none">';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Scale</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.transformScale || '1') + '" step="0.1" min="0" max="3" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transformScale\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Rotate (deg)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.transformRotate || '0') + '" min="-360" max="360" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transformRotate\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Translate X</div>';
        html += '<input type="text" class="tb-setting-input" value="' + (settings.transformTranslateX || '0') + '" placeholder="0px" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transformTranslateX\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Translate Y</div>';
        html += '<input type="text" class="tb-setting-input" value="' + (settings.transformTranslateY || '0') + '" placeholder="0px" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'transformTranslateY\',this.value)"></div>';

        html += '</div></div>';
        return html;
    },

    // Filter Settings (CSS Filters)
    renderFilterSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        let html = '<div class="tb-design-section collapsed">';
        html += '<div class="tb-design-section-header" onclick="TB.toggleDesignSection(this)">';
        html += '<span>🎨 Filters</span><span class="tb-design-section-toggle">▶</span></div>';
        html += '<div class="tb-design-section-body" style="display:none">';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Blur (px)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.filterBlur || '0') + '" min="0" max="100" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filterBlur\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Brightness (%)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.filterBrightness || '100') + '" min="0" max="200" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filterBrightness\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Contrast (%)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.filterContrast || '100') + '" min="0" max="200" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filterContrast\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Grayscale (%)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.filterGrayscale || '0') + '" min="0" max="100" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'filterGrayscale\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Opacity (%)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.opacity || '100') + '" min="0" max="100" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'opacity\',this.value)"></div>';

        html += '</div></div>';
        return html;
    },

    // Animation Settings
    renderAnimationSettings(sIdx, rIdx, cIdx, mIdx) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};

        const animations = [
            { value: '', label: 'None' },
            { value: 'fadeIn', label: 'Fade In' },
            { value: 'fadeInUp', label: 'Fade In Up' },
            { value: 'fadeInDown', label: 'Fade In Down' },
            { value: 'fadeInLeft', label: 'Fade In Left' },
            { value: 'fadeInRight', label: 'Fade In Right' },
            { value: 'zoomIn', label: 'Zoom In' },
            { value: 'slideInUp', label: 'Slide In Up' },
            { value: 'bounceIn', label: 'Bounce In' }
        ];

        let html = '<div class="tb-design-section collapsed">';
        html += '<div class="tb-design-section-header" onclick="TB.toggleDesignSection(this)">';
        html += '<span>✨ Animation</span><span class="tb-design-section-toggle">▶</span></div>';
        html += '<div class="tb-design-section-body" style="display:none">';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Entrance Animation</div>';
        html += '<select class="tb-setting-input" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animation\',this.value)">';
        animations.forEach(a => {
            html += '<option value="' + a.value + '"' + (settings.animation === a.value ? ' selected' : '') + '>' + a.label + '</option>';
        });
        html += '</select></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Duration (s)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.animationDuration || '0.6') + '" step="0.1" min="0.1" max="3" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animationDuration\',this.value)"></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Delay (s)</div>';
        html += '<input type="number" class="tb-setting-input" value="' + (settings.animationDelay || '0') + '" step="0.1" min="0" max="5" onchange="TB.updateModuleSetting(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'animationDelay\',this.value)"></div>';

        html += '</div></div>';
        return html;
    },

    // Helper: Toggle Design Section
    toggleDesignSection(headerEl) {
        const section = headerEl.closest('.tb-design-section');
        const body = section.querySelector('.tb-design-section-body');
        const toggle = section.querySelector('.tb-design-section-toggle');
        
        if (body.style.display === 'none') {
            body.style.display = 'block';
            toggle.textContent = '▼';
            section.classList.remove('collapsed');
        } else {
            body.style.display = 'none';
            toggle.textContent = '▶';
            section.classList.add('collapsed');
        }
    }
});
