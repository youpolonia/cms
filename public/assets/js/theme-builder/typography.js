/**
 * Theme Builder 3.0 - Typography JavaScript
 * 
 * Provides Typography settings functionality.
 * Extends TB object with typography methods.
 * 
 * @package ThemeBuilder
 * @version 3.0
 */

// Font list for typography
const TB_FONTS = [
    { value: '', label: 'Default' },
    { value: 'system-ui, -apple-system, sans-serif', label: 'System UI' },
    { value: 'Arial, Helvetica, sans-serif', label: 'Arial' },
    { value: 'Helvetica Neue, Helvetica, sans-serif', label: 'Helvetica Neue' },
    { value: 'Georgia, serif', label: 'Georgia' },
    { value: 'Times New Roman, Times, serif', label: 'Times New Roman' },
    { value: 'Verdana, Geneva, sans-serif', label: 'Verdana' },
    { value: 'Trebuchet MS, sans-serif', label: 'Trebuchet MS' },
    { value: 'Tahoma, Geneva, sans-serif', label: 'Tahoma' },
    { value: 'Palatino Linotype, Palatino, serif', label: 'Palatino' },
    { value: 'Courier New, monospace', label: 'Courier New' },
    { value: 'Lucida Console, Monaco, monospace', label: 'Lucida Console' },
    { value: 'Inter, sans-serif', label: 'Inter' },
    { value: 'Roboto, sans-serif', label: 'Roboto' },
    { value: 'Open Sans, sans-serif', label: 'Open Sans' },
    { value: 'Lato, sans-serif', label: 'Lato' },
    { value: 'Montserrat, sans-serif', label: 'Montserrat' },
    { value: 'Poppins, sans-serif', label: 'Poppins' },
    { value: 'Playfair Display, serif', label: 'Playfair Display' },
    { value: 'Merriweather, serif', label: 'Merriweather' }
];

const TB_FONT_WEIGHTS = [
    { value: '', label: 'Default' },
    { value: '100', label: '100 - Thin' },
    { value: '200', label: '200 - Extra Light' },
    { value: '300', label: '300 - Light' },
    { value: '400', label: '400 - Normal' },
    { value: '500', label: '500 - Medium' },
    { value: '600', label: '600 - Semi Bold' },
    { value: '700', label: '700 - Bold' },
    { value: '800', label: '800 - Extra Bold' },
    { value: '900', label: '900 - Black' }
];

// Extend TB object with Typography methods
Object.assign(TB, {
    renderTypographySettings(sIdx, rIdx, cIdx, mIdx, element, label) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        const settings = mod?.settings || {};
        const typoKey = 'typography_' + element;
        const typography = settings[typoKey] || {};

        const getTypoValue = (prop) => {
            if (this.currentDevice === 'desktop') {
                return typography[prop] || '';
            }
            const deviceKey = prop + '_' + this.currentDevice;
            const deviceValue = typography[deviceKey];
            if (deviceValue !== undefined && deviceValue !== '') {
                return deviceValue;
            }
            return typography[prop] || '';
        };

        const hasResponsive = (prop) => {
            const tabletKey = prop + '_tablet';
            const mobileKey = prop + '_mobile';
            return (typography[tabletKey] !== undefined && typography[tabletKey] !== '') ||
                   (typography[mobileKey] !== undefined && typography[mobileKey] !== '');
        };

        const deviceIcon = this.getDeviceIcon ? this.getDeviceIcon(this.currentDevice) : '🖥️';
        const deviceClass = this.currentDevice || 'desktop';

        let html = '';

        html += '<div class="tb-typography-section" data-element="' + element + '">';
        html += '<div class="tb-typography-section-header" onclick="TB.toggleTypographySection(this)">';
        html += '<div style="font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;color:var(--tb-text-muted)">' + this.escapeHtml(label) + ' Typography</div>';
        html += '<span class="tb-typography-toggle">▼</span>';
        html += '</div>';
        html += '<div class="tb-typography-content">';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Font Family</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_family\',this.value)">';
        TB_FONTS.forEach(f => {
            html += '<option value="' + f.value + '"' + (typography.font_family === f.value ? ' selected' : '') + '>' + f.label + '</option>';
        });
        html += '</select></div>';

        const fontSize = getTypoValue('font_size');
        const fontSizeHasResponsive = hasResponsive('font_size');
        html += '<div class="tb-setting-group"><div class="tb-setting-label">';
        html += '<span class="tb-device-icon ' + deviceClass + '"></span> Font Size';
        if (fontSizeHasResponsive) html += '<span class="tb-responsive-badge has-responsive">R</span>';
        html += '</div><div style="display:flex;gap:8px">';
        html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + (parseInt(fontSize) || '') + '" placeholder="16" min="8" max="200" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_size\',this.value,this.nextElementSibling.value)">';
        const sizeUnit = fontSize ? fontSize.replace(/[0-9.]/g, '') || 'px' : 'px';
        html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_size\',this.previousElementSibling.value,this.value)">';
        html += '<option value="px"' + (sizeUnit === 'px' ? ' selected' : '') + '>px</option>';
        html += '<option value="em"' + (sizeUnit === 'em' ? ' selected' : '') + '>em</option>';
        html += '<option value="rem"' + (sizeUnit === 'rem' ? ' selected' : '') + '>rem</option>';
        html += '<option value="%"' + (sizeUnit === '%' ? ' selected' : '') + '>%</option>';
        html += '</select></div></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Font Weight</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'font_weight\',this.value)">';
        TB_FONT_WEIGHTS.forEach(w => {
            html += '<option value="' + w.value + '"' + (typography.font_weight === w.value ? ' selected' : '') + '>' + w.label + '</option>';
        });
        html += '</select></div>';

        const lineHeight = getTypoValue('line_height');
        const lineHeightHasResponsive = hasResponsive('line_height');
        const lhValue = lineHeight ? parseFloat(lineHeight) : '';
        const lhUnit = lineHeight ? lineHeight.replace(/[0-9.]/g, '') || '' : '';
        html += '<div class="tb-setting-group"><div class="tb-setting-label">';
        html += '<span class="tb-device-icon ' + deviceClass + '"></span> Line Height';
        if (lineHeightHasResponsive) html += '<span class="tb-responsive-badge has-responsive">R</span>';
        html += '</div><div style="display:flex;gap:8px">';
        html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + lhValue + '" placeholder="1.6" min="0.5" max="5" step="0.1" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'line_height\',this.value,this.nextElementSibling.value)">';
        html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateResponsiveTypographyWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'line_height\',this.previousElementSibling.value,this.value)">';
        html += '<option value=""' + (lhUnit === '' ? ' selected' : '') + '>-</option>';
        html += '<option value="px"' + (lhUnit === 'px' ? ' selected' : '') + '>px</option>';
        html += '<option value="em"' + (lhUnit === 'em' ? ' selected' : '') + '>em</option>';
        html += '</select></div></div>';

        const lsValue = typography.letter_spacing ? parseFloat(typography.letter_spacing) : '';
        const lsUnit = typography.letter_spacing ? typography.letter_spacing.replace(/[0-9.-]/g, '') || 'px' : 'px';
        html += '<div class="tb-setting-group"><div class="tb-setting-label">Letter Spacing</div><div style="display:flex;gap:8px">';
        html += '<input type="number" class="tb-setting-input" style="flex:1" value="' + lsValue + '" placeholder="0" step="0.5" min="-5" max="20" onchange="TB.updateTypographyElementWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'letter_spacing\',this.value,this.nextElementSibling.value)">';
        html += '<select class="tb-setting-input" style="width:70px" onchange="TB.updateTypographyElementWithUnit(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'letter_spacing\',this.previousElementSibling.value,this.value)">';
        html += '<option value="px"' + (lsUnit === 'px' ? ' selected' : '') + '>px</option>';
        html += '<option value="em"' + (lsUnit === 'em' ? ' selected' : '') + '>em</option>';
        html += '</select></div></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Transform</div><select class="tb-setting-input" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'text_transform\',this.value)">';
        html += '<option value=""' + (!typography.text_transform ? ' selected' : '') + '>None</option>';
        html += '<option value="uppercase"' + (typography.text_transform === 'uppercase' ? ' selected' : '') + '>UPPERCASE</option>';
        html += '<option value="lowercase"' + (typography.text_transform === 'lowercase' ? ' selected' : '') + '>lowercase</option>';
        html += '<option value="capitalize"' + (typography.text_transform === 'capitalize' ? ' selected' : '') + '>Capitalize</option>';
        html += '</select></div>';

        html += '<div class="tb-setting-group"><div class="tb-setting-label">Text Color</div><div style="display:flex;gap:8px;align-items:center">';
        html += '<input type="color" class="tb-setting-input" style="width:50px;padding:2px" value="' + (typography.color || '#333333') + '" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'color\',this.value)">';
        html += '<input type="text" class="tb-setting-input" style="flex:1" value="' + (typography.color || '') + '" placeholder="#333333" onchange="TB.updateTypographyElement(' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ',\'' + element + '\',\'color\',this.value)">';
        html += '</div></div>';

        html += '</div></div>';

        return html;
    },

    toggleTypographySection(headerEl) {
        const section = headerEl.closest('.tb-typography-section');
        const content = section.querySelector('.tb-typography-content');
        const toggle = section.querySelector('.tb-typography-toggle');
        if (content.classList.contains('collapsed')) {
            content.classList.remove('collapsed');
            toggle.textContent = '▼';
        } else {
            content.classList.add('collapsed');
            toggle.textContent = '▶';
        }
    },

    updateTypographyElement(sIdx, rIdx, cIdx, mIdx, element, prop, value) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        if (!mod) return;
        if (!mod.settings) mod.settings = {};
        const typoKey = 'typography_' + element;
        if (!mod.settings[typoKey]) mod.settings[typoKey] = {};
        mod.settings[typoKey][prop] = value;
        this.isDirty = true;
        if (this.saveToHistory) this.saveToHistory();
        if (this.renderCanvas) this.renderCanvas();
    },

    updateTypographyElementWithUnit(sIdx, rIdx, cIdx, mIdx, element, prop, value, unit) {
        const finalValue = value ? value + (unit || '') : '';
        this.updateTypographyElement(sIdx, rIdx, cIdx, mIdx, element, prop, finalValue);
    },

    updateResponsiveTypographyWithUnit(sIdx, rIdx, cIdx, mIdx, element, prop, value, unit) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        if (!mod) return;
        if (!mod.settings) mod.settings = {};
        const typoKey = 'typography_' + element;
        if (!mod.settings[typoKey]) mod.settings[typoKey] = {};
        
        const finalValue = value ? value + (unit || '') : '';
        const device = this.currentDevice || 'desktop';
        
        if (device === 'desktop') {
            mod.settings[typoKey][prop] = finalValue;
        } else {
            mod.settings[typoKey][prop + '_' + device] = finalValue;
        }
        
        this.isDirty = true;
        if (this.saveToHistory) this.saveToHistory();
        if (this.renderCanvas) this.renderCanvas();
    },

    getDeviceIcon(device) {
        switch (device) {
            case 'tablet': return '📱';
            case 'mobile': return '📲';
            default: return '🖥️';
        }
    }
});
