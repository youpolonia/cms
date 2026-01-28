/**
 * JTB Fields JavaScript
 * Field rendering for settings panel
 */

(function() {
    'use strict';

    window.JTB = window.JTB || {};

    // ========================================
    // Fields Namespace
    // ========================================

    JTB.Fields = {};

    // ========================================
    // Main Render Method
    // ========================================

    JTB.Fields.render = function(type, name, config, value) {
        const method = 'render' + type.charAt(0).toUpperCase() + type.slice(1);

        if (typeof JTB.Fields[method] === 'function') {
            return JTB.Fields[method](name, config, value);
        }

        return JTB.Fields.renderText(name, config, value);
    };

    // ========================================
    // Field Render Methods
    // ========================================

    JTB.Fields.renderText = function(name, config, value) {
        const placeholder = JTB.Fields.esc(config.placeholder || '');
        const val = JTB.Fields.esc(value || config.default || '');

        return `
            <input type="text"
                class="jtb-input-text"
                name="${JTB.Fields.esc(name)}"
                value="${val}"
                placeholder="${placeholder}"
                data-field="${JTB.Fields.esc(name)}">
        `;
    };

    JTB.Fields.renderTextarea = function(name, config, value) {
        const rows = config.rows || 4;
        const placeholder = JTB.Fields.esc(config.placeholder || '');
        const val = JTB.Fields.esc(value || config.default || '');

        return `
            <textarea
                class="jtb-input-textarea"
                name="${JTB.Fields.esc(name)}"
                rows="${rows}"
                placeholder="${placeholder}"
                data-field="${JTB.Fields.esc(name)}">${val}</textarea>
        `;
    };

    JTB.Fields.renderRichtext = function(name, config, value) {
        const content = value || config.default || '';

        // Get Feather icons for toolbar
        const boldIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('bold', 14) : '<strong>B</strong>';
        const italicIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('italic', 14) : '<em>I</em>';
        const underlineIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('underline', 14) : '<u>U</u>';
        const listIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('list', 14) : '•';
        const listOrderedIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('hash', 14) : '1.';
        const linkIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('link', 14) : '🔗';
        const unlinkIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('link-2', 14) : '❌';

        return `
            <div class="jtb-richtext-wrapper" data-field="${JTB.Fields.esc(name)}">
                <div class="jtb-richtext-toolbar">
                    <button type="button" class="jtb-richtext-btn" data-command="bold" title="Bold">${boldIcon}</button>
                    <button type="button" class="jtb-richtext-btn" data-command="italic" title="Italic">${italicIcon}</button>
                    <button type="button" class="jtb-richtext-btn" data-command="underline" title="Underline">${underlineIcon}</button>
                    <span class="jtb-toolbar-separator"></span>
                    <button type="button" class="jtb-richtext-btn" data-command="insertUnorderedList" title="Bullet List">${listIcon}</button>
                    <button type="button" class="jtb-richtext-btn" data-command="insertOrderedList" title="Numbered List">${listOrderedIcon}</button>
                    <span class="jtb-toolbar-separator"></span>
                    <button type="button" class="jtb-richtext-btn" data-command="createLink" title="Insert Link">${linkIcon}</button>
                    <button type="button" class="jtb-richtext-btn" data-command="unlink" title="Remove Link">${unlinkIcon}</button>
                </div>
                <div class="jtb-richtext-content" contenteditable="true">${content}</div>
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(content)}">
            </div>
        `;
    };

    JTB.Fields.renderSelect = function(name, config, value) {
        const options = config.options || {};
        const currentValue = value !== undefined ? value : (config.default || '');
        const dynamicOptions = config.dynamic_options || null;

        // Add data attribute for dynamic loading
        let dataAttrs = `data-field="${JTB.Fields.esc(name)}"`;
        if (dynamicOptions) {
            dataAttrs += ` data-dynamic-options="${JTB.Fields.esc(dynamicOptions)}"`;
            dataAttrs += ` data-current-value="${JTB.Fields.esc(currentValue)}"`;
        }

        let html = `<select class="jtb-input-select" name="${JTB.Fields.esc(name)}" ${dataAttrs}>`;

        // For dynamic options, show loading state initially
        if (dynamicOptions && Object.keys(options).length === 0) {
            html += `<option value="">Loading...</option>`;
        } else {
            for (const optValue in options) {
                const optLabel = options[optValue];
                const selected = optValue == currentValue ? ' selected' : '';
                html += `<option value="${JTB.Fields.esc(optValue)}"${selected}>${JTB.Fields.esc(optLabel)}</option>`;
            }
        }

        html += '</select>';

        return html;
    };

    /**
     * Load dynamic options for select fields
     * Called after panel is rendered
     */
    JTB.Fields.loadDynamicOptions = function(container) {
        const dynamicSelects = container.querySelectorAll('select[data-dynamic-options]');

        dynamicSelects.forEach(select => {
            const endpoint = select.dataset.dynamicOptions;
            const currentValue = select.dataset.currentValue || '';

            // Fetch options from API
            fetch(JTB.config.apiUrl + '/' + endpoint, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    // Clear loading state
                    select.innerHTML = '<option value="">-- Select --</option>';

                    // Add options from API
                    data.data.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.label;
                        if (opt.value === currentValue) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    // Trigger change event if value was pre-selected
                    if (currentValue) {
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else {
                    select.innerHTML = '<option value="">No options available</option>';
                }
            })
            .catch(error => {
                console.error('Failed to load dynamic options:', error);
                select.innerHTML = '<option value="">Error loading options</option>';
            });
        });
    };

    JTB.Fields.renderToggle = function(name, config, value) {
        const checked = value !== undefined ? value : (config.default || false);
        const checkedAttr = checked ? ' checked' : '';

        return `
            <label class="jtb-toggle-switch">
                <input type="checkbox"
                    name="${JTB.Fields.esc(name)}"
                    value="1"${checkedAttr}
                    data-field="${JTB.Fields.esc(name)}">
                <span class="jtb-toggle-slider"></span>
            </label>
        `;
    };

    JTB.Fields.renderRange = function(name, config, value) {
        const min = config.min !== undefined ? config.min : 0;
        const max = config.max !== undefined ? config.max : 100;
        const step = config.step || 1;
        const unit = config.unit || '';
        const currentValue = value !== undefined ? value : (config.default !== undefined ? config.default : min);

        // Calculate fill percentage for gradient
        const percent = ((currentValue - min) / (max - min)) * 100;

        return `
            <div class="jtb-range-wrapper" data-min="${min}" data-max="${max}">
                <input type="range"
                    class="jtb-input-range"
                    name="${JTB.Fields.esc(name)}_range"
                    min="${min}"
                    max="${max}"
                    step="${step}"
                    value="${currentValue}"
                    data-field="${JTB.Fields.esc(name)}"
                    style="background: linear-gradient(to right, var(--accent) 0%, var(--accent) ${percent}%, var(--bg-tertiary) ${percent}%, var(--bg-tertiary) 100%);">
                <input type="number"
                    class="jtb-input-number"
                    name="${JTB.Fields.esc(name)}"
                    min="${min}"
                    max="${max}"
                    step="${step}"
                    value="${currentValue}"
                    data-field="${JTB.Fields.esc(name)}">
                ${unit ? `<span class="jtb-range-unit">${JTB.Fields.esc(unit)}</span>` : ''}
            </div>
        `;
    };

    JTB.Fields.renderColor = function(name, config, value) {
        const currentValue = value || config.default || '#000000';
        const supportsAlpha = config.alpha !== false; // Default true

        // Parse color to get hex and alpha
        const colorData = JTB.Fields.parseColor(currentValue);

        return `
            <div class="jtb-color-wrapper ${supportsAlpha ? 'jtb-color-with-alpha' : ''}" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden"
                    class="jtb-input-color-value"
                    name="${JTB.Fields.esc(name)}"
                    value="${JTB.Fields.esc(currentValue)}"
                    data-field="${JTB.Fields.esc(name)}">
                <div class="jtb-color-picker-trigger" title="Click to pick color">
                    <div class="jtb-color-preview" style="background-color: ${JTB.Fields.esc(currentValue)};"></div>
                </div>
                <input type="text"
                    class="jtb-input-color-text"
                    value="${JTB.Fields.esc(currentValue)}"
                    placeholder="#000000 or rgba(...)">
                <div class="jtb-color-picker-popup" style="display: none;">
                    <div class="jtb-color-picker-saturation">
                        <div class="jtb-saturation-gradient"></div>
                        <div class="jtb-saturation-pointer"></div>
                    </div>
                    <div class="jtb-color-picker-hue">
                        <div class="jtb-hue-slider"></div>
                        <div class="jtb-hue-pointer"></div>
                    </div>
                    ${supportsAlpha ? `
                    <div class="jtb-color-picker-alpha">
                        <div class="jtb-alpha-gradient" style="--alpha-color: ${colorData.hex}"></div>
                        <div class="jtb-alpha-slider"></div>
                        <div class="jtb-alpha-pointer"></div>
                    </div>
                    ` : ''}
                    <div class="jtb-color-picker-inputs">
                        <div class="jtb-color-input-group">
                            <input type="text" class="jtb-color-hex-input" value="${colorData.hex}" maxlength="7">
                            <label>HEX</label>
                        </div>
                        <div class="jtb-color-input-group">
                            <input type="number" class="jtb-color-r-input" value="${colorData.r}" min="0" max="255">
                            <label>R</label>
                        </div>
                        <div class="jtb-color-input-group">
                            <input type="number" class="jtb-color-g-input" value="${colorData.g}" min="0" max="255">
                            <label>G</label>
                        </div>
                        <div class="jtb-color-input-group">
                            <input type="number" class="jtb-color-b-input" value="${colorData.b}" min="0" max="255">
                            <label>B</label>
                        </div>
                        ${supportsAlpha ? `
                        <div class="jtb-color-input-group">
                            <input type="number" class="jtb-color-a-input" value="${Math.round(colorData.a * 100)}" min="0" max="100" step="1">
                            <label>A%</label>
                        </div>
                        ` : ''}
                    </div>
                    <div class="jtb-color-picker-presets">
                        <div class="jtb-color-preset" data-color="#000000" style="background:#000000"></div>
                        <div class="jtb-color-preset" data-color="#ffffff" style="background:#ffffff"></div>
                        <div class="jtb-color-preset" data-color="#ff0000" style="background:#ff0000"></div>
                        <div class="jtb-color-preset" data-color="#00ff00" style="background:#00ff00"></div>
                        <div class="jtb-color-preset" data-color="#0000ff" style="background:#0000ff"></div>
                        <div class="jtb-color-preset" data-color="#ffff00" style="background:#ffff00"></div>
                        <div class="jtb-color-preset" data-color="#ff00ff" style="background:#ff00ff"></div>
                        <div class="jtb-color-preset" data-color="#00ffff" style="background:#00ffff"></div>
                        <div class="jtb-color-preset" data-color="#7c3aed" style="background:#7c3aed"></div>
                        <div class="jtb-color-preset" data-color="#f59e0b" style="background:#f59e0b"></div>
                        <div class="jtb-color-preset" data-color="#10b981" style="background:#10b981"></div>
                        <div class="jtb-color-preset" data-color="#ef4444" style="background:#ef4444"></div>
                        <div class="jtb-color-preset" data-color="transparent" style="background:linear-gradient(45deg, #ccc 25%, transparent 25%, transparent 75%, #ccc 75%), linear-gradient(45deg, #ccc 25%, transparent 25%, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 4px 4px;"></div>
                    </div>
                </div>
            </div>
        `;
    };

    // Color utility functions
    JTB.Fields.parseColor = function(color) {
        if (!color) return { hex: '#000000', r: 0, g: 0, b: 0, a: 1 };

        // Handle transparent
        if (color === 'transparent') {
            return { hex: '#000000', r: 0, g: 0, b: 0, a: 0 };
        }

        // Handle rgba/rgb
        const rgbaMatch = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
        if (rgbaMatch) {
            const r = parseInt(rgbaMatch[1]);
            const g = parseInt(rgbaMatch[2]);
            const b = parseInt(rgbaMatch[3]);
            const a = rgbaMatch[4] !== undefined ? parseFloat(rgbaMatch[4]) : 1;
            const hex = JTB.Fields.rgbToHex(r, g, b);
            return { hex, r, g, b, a };
        }

        // Handle hex
        let hex = color;
        if (hex.length === 4) {
            // Expand #RGB to #RRGGBB
            hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
        }

        const rgb = JTB.Fields.hexToRgb(hex);
        return { hex, r: rgb.r, g: rgb.g, b: rgb.b, a: 1 };
    };

    JTB.Fields.hexToRgb = function(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 0, g: 0, b: 0 };
    };

    JTB.Fields.rgbToHex = function(r, g, b) {
        const toHex = (n) => {
            const hex = Math.max(0, Math.min(255, Math.round(n))).toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        };
        return '#' + toHex(r) + toHex(g) + toHex(b);
    };

    JTB.Fields.rgbToHsl = function(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }
        return { h: h * 360, s: s * 100, l: l * 100 };
    };

    JTB.Fields.hslToRgb = function(h, s, l) {
        h /= 360; s /= 100; l /= 100;
        let r, g, b;

        if (s === 0) {
            r = g = b = l;
        } else {
            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }
        return { r: Math.round(r * 255), g: Math.round(g * 255), b: Math.round(b * 255) };
    };

    JTB.Fields.formatColor = function(r, g, b, a) {
        if (a === 0) return 'transparent';
        if (a === 1 || a === undefined) return JTB.Fields.rgbToHex(r, g, b);
        return `rgba(${r}, ${g}, ${b}, ${a})`;
    };

    JTB.Fields.renderUpload = function(name, config, value) {
        const accept = config.accept || 'image/*';
        const currentValue = value || '';

        let previewHtml;
        let removeBtn = '';

        if (currentValue) {
            previewHtml = `<img src="${JTB.Fields.esc(currentValue)}" alt="Preview">`;
            removeBtn = '<button type="button" class="jtb-btn jtb-upload-remove">Remove</button>';
        } else {
            previewHtml = '<div class="jtb-upload-placeholder">No image selected</div>';
        }

        return `
            <div class="jtb-upload-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(currentValue)}">
                <div class="jtb-upload-preview">${previewHtml}</div>
                <div class="jtb-upload-buttons">
                    <button type="button" class="jtb-btn jtb-upload-btn" data-accept="${JTB.Fields.esc(accept)}">Choose Image</button>
                    ${removeBtn}
                </div>
            </div>
        `;
    };

    JTB.Fields.renderSpacing = function(name, config, value) {
        const unit = config.unit || 'px';
        const max = config.max || 100;
        const values = value || {};

        const topVal = values.top !== undefined ? values.top : 0;
        const rightVal = values.right !== undefined ? values.right : 0;
        const bottomVal = values.bottom !== undefined ? values.bottom : 0;
        const leftVal = values.left !== undefined ? values.left : 0;

        const linkIcon = typeof JTB !== 'undefined' && typeof JTB.getFeatherIcon === 'function'
            ? JTB.getFeatherIcon('link', 14, 2)
            : '🔗';
        const unlinkIcon = typeof JTB !== 'undefined' && typeof JTB.getFeatherIcon === 'function'
            ? JTB.getFeatherIcon('link-2', 14, 2)
            : '⛓️';

        return `
            <div class="jtb-spacing-control" data-field="${JTB.Fields.esc(name)}" data-max="${max}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(values))}">

                <!-- Visual Box Model -->
                <div class="jtb-spacing-box-model">
                    <!-- Top -->
                    <div class="jtb-spacing-side jtb-spacing-top">
                        <input type="number" class="jtb-spacing-value" data-side="top" value="${topVal}" min="0" max="${max}">
                    </div>

                    <!-- Middle Row: Left + Center + Right -->
                    <div class="jtb-spacing-middle">
                        <div class="jtb-spacing-side jtb-spacing-left">
                            <input type="number" class="jtb-spacing-value" data-side="left" value="${leftVal}" min="0" max="${max}">
                        </div>

                        <div class="jtb-spacing-center">
                            <button type="button" class="jtb-spacing-link-btn" title="Link all values">
                                <span class="jtb-link-icon">${linkIcon}</span>
                                <span class="jtb-unlink-icon" style="display:none">${unlinkIcon}</span>
                            </button>
                        </div>

                        <div class="jtb-spacing-side jtb-spacing-right">
                            <input type="number" class="jtb-spacing-value" data-side="right" value="${rightVal}" min="0" max="${max}">
                        </div>
                    </div>

                    <!-- Bottom -->
                    <div class="jtb-spacing-side jtb-spacing-bottom">
                        <input type="number" class="jtb-spacing-value" data-side="bottom" value="${bottomVal}" min="0" max="${max}">
                    </div>
                </div>

                <!-- Slider for quick adjustment -->
                <div class="jtb-spacing-slider-row">
                    <span class="jtb-spacing-label">All sides</span>
                    <input type="range" class="jtb-spacing-all-slider" min="0" max="${max}" value="0">
                    <span class="jtb-spacing-unit">${JTB.Fields.esc(unit)}</span>
                </div>
            </div>
        `;
    };

    JTB.Fields.renderUrl = function(name, config, value) {
        const currentValue = value || config.default || '';

        return `
            <div class="jtb-url-wrapper">
                <input type="url"
                    class="jtb-input-url"
                    name="${JTB.Fields.esc(name)}"
                    value="${JTB.Fields.esc(currentValue)}"
                    placeholder="https://"
                    data-field="${JTB.Fields.esc(name)}">
                <button type="button" class="jtb-url-options" title="Link Options">${typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('settings', 14, 2) : '⚙️'}</button>
            </div>
        `;
    };

    JTB.Fields.renderIcon = function(name, config, value) {
        const currentValue = value || '';

        let previewHtml;

        if (currentValue) {
            // Use Feather Icons if available
            if (typeof JTB !== 'undefined' && typeof JTB.getFeatherIcon === 'function') {
                previewHtml = JTB.getFeatherIcon(currentValue, 24, 2);
            } else {
                previewHtml = `<span class="jtb-icon">${currentValue.charAt(0).toUpperCase()}</span>`;
            }
        } else {
            previewHtml = '<span class="jtb-icon-placeholder">No icon</span>';
        }

        return `
            <div class="jtb-icon-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(currentValue)}">
                <div class="jtb-icon-preview">${previewHtml}</div>
                <button type="button" class="jtb-btn jtb-icon-choose">Choose Icon</button>
            </div>
        `;
    };

    // Alias for icon_select type (used in modules like Blurb)
    JTB.Fields.renderIcon_select = JTB.Fields.renderIcon;

    JTB.Fields.renderCode = function(name, config, value) {
        const language = config.language || 'html';
        const currentValue = value || config.default || '';
        const rows = config.rows || 10;

        // Get Feather icon for fullscreen
        const maximizeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('maximize-2', 14) : '⛶';

        return `
            <div class="jtb-code-wrapper" data-field="${JTB.Fields.esc(name)}" data-language="${JTB.Fields.esc(language)}">
                <div class="jtb-code-header">
                    <span class="jtb-code-language">${JTB.Fields.esc(language.toUpperCase())}</span>
                    <button type="button" class="jtb-code-fullscreen" title="Fullscreen">${maximizeIcon}</button>
                </div>
                <textarea
                    class="jtb-input-code"
                    name="${JTB.Fields.esc(name)}"
                    rows="${rows}"
                    spellcheck="false">${JTB.Fields.esc(currentValue)}</textarea>
            </div>
        `;
    };

    JTB.Fields.renderDate = function(name, config, value) {
        const currentValue = value || config.default || '';

        return `
            <div class="jtb-date-wrapper">
                <input type="date"
                    class="jtb-input-date"
                    name="${JTB.Fields.esc(name)}"
                    value="${JTB.Fields.esc(currentValue)}"
                    data-field="${JTB.Fields.esc(name)}">
            </div>
        `;
    };

    JTB.Fields.renderDatetime = function(name, config, value) {
        const currentValue = value || config.default || '';

        return `
            <div class="jtb-datetime-wrapper">
                <input type="datetime-local"
                    class="jtb-input-datetime"
                    name="${JTB.Fields.esc(name)}"
                    value="${JTB.Fields.esc(currentValue)}"
                    data-field="${JTB.Fields.esc(name)}">
            </div>
        `;
    };

    JTB.Fields.renderNumber = function(name, config, value) {
        const min = config.min !== undefined ? config.min : '';
        const max = config.max !== undefined ? config.max : '';
        const step = config.step || 1;
        const currentValue = value !== undefined ? value : (config.default !== undefined ? config.default : '');

        return `
            <input type="number"
                class="jtb-input-number-field"
                name="${JTB.Fields.esc(name)}"
                value="${currentValue}"
                ${min !== '' ? `min="${min}"` : ''}
                ${max !== '' ? `max="${max}"` : ''}
                step="${step}"
                data-field="${JTB.Fields.esc(name)}">
        `;
    };

    JTB.Fields.renderGallery = function(name, config, value) {
        const images = value || [];

        // Get Feather icon for remove
        const removeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 12) : '×';
        const plusIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('plus', 14) : '+';

        let imagesHtml = '';
        if (Array.isArray(images) && images.length > 0) {
            images.forEach((img, index) => {
                imagesHtml += `
                    <div class="jtb-gallery-item" data-index="${index}">
                        <img src="${JTB.Fields.esc(img)}" alt="">
                        <button type="button" class="jtb-gallery-remove" title="Remove">${removeIcon}</button>
                    </div>
                `;
            });
        }

        return `
            <div class="jtb-gallery-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(images))}">
                <div class="jtb-gallery-items">${imagesHtml}</div>
                <div class="jtb-gallery-actions">
                    <button type="button" class="jtb-btn jtb-gallery-add">${plusIcon} Add Images</button>
                </div>
            </div>
        `;
    };

    JTB.Fields.renderRepeater = function(name, config, value) {
        const fields = config.fields || {};
        const items = value || [];
        const addButtonText = config.add_button || 'Add Item';

        // Get Feather icon for add
        const plusIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('plus', 14) : '+';

        let itemsHtml = '';
        if (Array.isArray(items)) {
            items.forEach((item, index) => {
                itemsHtml += JTB.Fields.renderRepeaterItem(name, fields, item, index);
            });
        }

        return `
            <div class="jtb-repeater-wrapper" data-field="${JTB.Fields.esc(name)}">
                <div class="jtb-repeater-items">${itemsHtml}</div>
                <button type="button" class="jtb-btn jtb-repeater-add">${plusIcon} ${JTB.Fields.esc(addButtonText)}</button>
            </div>
        `;
    };

    JTB.Fields.renderRepeaterItem = function(parentName, fields, values, index) {
        // Get Feather icons for repeater item controls
        const dragIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('menu', 14) : '☰';
        const toggleIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('chevron-down', 14) : '▼';
        const removeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 14) : '×';

        let fieldsHtml = '';

        for (const fieldName in fields) {
            const fieldConfig = fields[fieldName];
            const fieldValue = values ? values[fieldName] : undefined;
            const fullName = `${parentName}[${index}][${fieldName}]`;

            fieldsHtml += `
                <div class="jtb-repeater-field">
                    <label>${JTB.Fields.esc(fieldConfig.label || fieldName)}</label>
                    ${JTB.Fields.render(fieldConfig.type || 'text', fullName, fieldConfig, fieldValue)}
                </div>
            `;
        }

        return `
            <div class="jtb-repeater-item" data-index="${index}">
                <div class="jtb-repeater-item-header">
                    <span class="jtb-repeater-drag-handle">${dragIcon}</span>
                    <span class="jtb-repeater-item-title">Item ${index + 1}</span>
                    <button type="button" class="jtb-repeater-toggle">${toggleIcon}</button>
                    <button type="button" class="jtb-repeater-remove">${removeIcon}</button>
                </div>
                <div class="jtb-repeater-item-content">${fieldsHtml}</div>
            </div>
        `;
    };

    JTB.Fields.renderCheckbox = function(name, config, value) {
        const checked = value !== undefined ? value : (config.default || false);
        const checkedAttr = checked ? ' checked' : '';
        const label = config.checkbox_label || '';

        return `
            <label class="jtb-checkbox-wrapper">
                <input type="checkbox"
                    class="jtb-input-checkbox"
                    name="${JTB.Fields.esc(name)}"
                    value="1"${checkedAttr}
                    data-field="${JTB.Fields.esc(name)}">
                <span class="jtb-checkbox-label">${JTB.Fields.esc(label)}</span>
            </label>
        `;
    };

    JTB.Fields.renderRadio = function(name, config, value) {
        const options = config.options || {};
        const currentValue = value !== undefined ? value : (config.default || '');

        let html = '<div class="jtb-radio-wrapper">';

        for (const optValue in options) {
            const optLabel = options[optValue];
            const checked = optValue == currentValue ? ' checked' : '';
            const id = `${name}_${optValue}`.replace(/[^a-zA-Z0-9]/g, '_');

            html += `
                <label class="jtb-radio-item" for="${id}">
                    <input type="radio"
                        id="${id}"
                        name="${JTB.Fields.esc(name)}"
                        value="${JTB.Fields.esc(optValue)}"${checked}
                        data-field="${JTB.Fields.esc(name)}">
                    <span class="jtb-radio-label">${JTB.Fields.esc(optLabel)}</span>
                </label>
            `;
        }

        html += '</div>';

        return html;
    };

    JTB.Fields.renderButtonGroup = function(name, config, value) {
        const options = config.options || {};
        const currentValue = value !== undefined ? value : (config.default || '');

        let html = '<div class="jtb-button-group-wrapper" data-field="' + JTB.Fields.esc(name) + '">';
        html += '<input type="hidden" name="' + JTB.Fields.esc(name) + '" value="' + JTB.Fields.esc(currentValue) + '">';

        for (const optValue in options) {
            const optLabel = options[optValue];
            const active = optValue == currentValue ? ' jtb-active' : '';

            html += `
                <button type="button"
                    class="jtb-button-group-btn${active}"
                    data-value="${JTB.Fields.esc(optValue)}">
                    ${JTB.Fields.esc(optLabel)}
                </button>
            `;
        }

        html += '</div>';

        return html;
    };

    JTB.Fields.renderBoxShadow = function(name, config, value) {
        const defaults = {
            horizontal: 0,
            vertical: 4,
            blur: 12,
            spread: 0,
            color: 'rgba(0,0,0,0.15)',
            inset: false
        };

        const vals = value ? { ...defaults, ...value } : defaults;
        const shadowStyle = `${vals.inset ? 'inset ' : ''}${vals.horizontal}px ${vals.vertical}px ${vals.blur}px ${vals.spread}px ${vals.color}`;

        return `
            <div class="jtb-box-shadow-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(vals))}">

                <!-- Live Preview -->
                <div class="jtb-shadow-preview-area">
                    <div class="jtb-shadow-preview-box" style="box-shadow: ${shadowStyle};">
                        <span>Shadow</span>
                    </div>
                </div>

                <div class="jtb-shadow-controls">
                    <!-- X Offset -->
                    <div class="jtb-shadow-row">
                        <label>X Offset</label>
                        <input type="range" class="jtb-shadow-h" min="-50" max="50" value="${vals.horizontal}">
                        <input type="number" class="jtb-shadow-h-num" min="-50" max="50" value="${vals.horizontal}">
                        <span class="jtb-shadow-unit">px</span>
                    </div>

                    <!-- Y Offset -->
                    <div class="jtb-shadow-row">
                        <label>Y Offset</label>
                        <input type="range" class="jtb-shadow-v" min="-50" max="50" value="${vals.vertical}">
                        <input type="number" class="jtb-shadow-v-num" min="-50" max="50" value="${vals.vertical}">
                        <span class="jtb-shadow-unit">px</span>
                    </div>

                    <!-- Blur -->
                    <div class="jtb-shadow-row">
                        <label>Blur</label>
                        <input type="range" class="jtb-shadow-blur" min="0" max="100" value="${vals.blur}">
                        <input type="number" class="jtb-shadow-blur-num" min="0" max="100" value="${vals.blur}">
                        <span class="jtb-shadow-unit">px</span>
                    </div>

                    <!-- Spread -->
                    <div class="jtb-shadow-row">
                        <label>Spread</label>
                        <input type="range" class="jtb-shadow-spread" min="-50" max="50" value="${vals.spread}">
                        <input type="number" class="jtb-shadow-spread-num" min="-50" max="50" value="${vals.spread}">
                        <span class="jtb-shadow-unit">px</span>
                    </div>

                    <!-- Color -->
                    <div class="jtb-shadow-row jtb-shadow-color-row">
                        <label>Color</label>
                        <div class="jtb-shadow-color-picker">
                            <input type="color" class="jtb-shadow-color-input" value="${JTB.Fields.esc(vals.color.startsWith('rgba') ? '#000000' : vals.color)}">
                            <input type="text" class="jtb-shadow-color" value="${JTB.Fields.esc(vals.color)}" placeholder="rgba(0,0,0,0.15)">
                        </div>
                    </div>

                    <!-- Inset Toggle -->
                    <div class="jtb-shadow-row jtb-shadow-inset-row">
                        <label>Inset</label>
                        <label class="jtb-toggle-switch jtb-shadow-inset-toggle">
                            <input type="checkbox" class="jtb-shadow-inset"${vals.inset ? ' checked' : ''}>
                            <span class="jtb-toggle-slider"></span>
                        </label>
                        <span class="jtb-shadow-inset-label">${vals.inset ? 'Inner shadow' : 'Outer shadow'}</span>
                    </div>
                </div>
            </div>
        `;
    };

    JTB.Fields.renderBorder = function(name, config, value) {
        const defaults = {
            width: 1,
            style: 'solid',
            color: '#313244',
            radius: 8
        };

        const vals = value ? { ...defaults, ...value } : defaults;

        return `
            <div class="jtb-border-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(vals))}">

                <!-- Live Preview -->
                <div class="jtb-border-preview-box">
                    <div class="jtb-border-preview-inner" style="border: ${vals.width}px ${vals.style} ${vals.color}; border-radius: ${vals.radius}px;">
                        Preview
                    </div>
                </div>

                <div class="jtb-border-controls">
                    <!-- Width with slider -->
                    <div class="jtb-border-row">
                        <label>Width</label>
                        <div class="jtb-border-slider-group">
                            <input type="range" class="jtb-border-width-slider" min="0" max="20" value="${vals.width}">
                            <input type="number" class="jtb-border-width" min="0" max="20" value="${vals.width}">
                            <span class="jtb-unit">px</span>
                        </div>
                    </div>

                    <!-- Style -->
                    <div class="jtb-border-row">
                        <label>Style</label>
                        <div class="jtb-border-style-btns">
                            <button type="button" class="jtb-border-style-btn${vals.style === 'solid' ? ' active' : ''}" data-style="solid" title="Solid">━</button>
                            <button type="button" class="jtb-border-style-btn${vals.style === 'dashed' ? ' active' : ''}" data-style="dashed" title="Dashed">┄</button>
                            <button type="button" class="jtb-border-style-btn${vals.style === 'dotted' ? ' active' : ''}" data-style="dotted" title="Dotted">┈</button>
                            <button type="button" class="jtb-border-style-btn${vals.style === 'double' ? ' active' : ''}" data-style="double" title="Double">═</button>
                            <button type="button" class="jtb-border-style-btn${vals.style === 'none' ? ' active' : ''}" data-style="none" title="None">✕</button>
                        </div>
                        <input type="hidden" class="jtb-border-style" value="${vals.style}">
                    </div>

                    <!-- Color -->
                    <div class="jtb-border-row">
                        <label>Color</label>
                        <div class="jtb-border-color-group">
                            <input type="color" class="jtb-border-color" value="${JTB.Fields.esc(vals.color)}">
                            <input type="text" class="jtb-border-color-text" value="${JTB.Fields.esc(vals.color)}" placeholder="#000000">
                        </div>
                    </div>

                    <!-- Radius with slider -->
                    <div class="jtb-border-row">
                        <label>Radius</label>
                        <div class="jtb-border-slider-group">
                            <input type="range" class="jtb-border-radius-slider" min="0" max="100" value="${vals.radius}">
                            <input type="number" class="jtb-border-radius" min="0" max="100" value="${vals.radius}">
                            <span class="jtb-unit">px</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    JTB.Fields.renderFont = function(name, config, value) {
        const defaults = {
            family: 'inherit',
            size: 16,
            weight: '400',
            style: 'normal',
            lineHeight: 1.5,
            letterSpacing: 0
        };

        const vals = value ? { ...defaults, ...value } : defaults;

        const fonts = [
            'inherit', 'Inter', 'Roboto', 'Open Sans', 'Montserrat', 'Poppins',
            'Arial', 'Georgia', 'Times New Roman', 'Verdana', 'Helvetica'
        ];

        let fontOptions = '';
        fonts.forEach(font => {
            const selected = vals.family === font ? ' selected' : '';
            const displayName = font === 'inherit' ? 'Theme Default' : font;
            fontOptions += `<option value="${JTB.Fields.esc(font)}"${selected} style="font-family: ${font}">${JTB.Fields.esc(displayName)}</option>`;
        });

        // Weight buttons
        const weights = ['300', '400', '500', '600', '700'];
        const weightLabels = { '300': 'Light', '400': 'Regular', '500': 'Medium', '600': 'Semi', '700': 'Bold' };

        return `
            <div class="jtb-font-wrapper" data-field="${JTB.Fields.esc(name)}">
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(vals))}">

                <!-- Font Preview -->
                <div class="jtb-font-preview" style="font-family: ${vals.family}; font-size: ${vals.size}px; font-weight: ${vals.weight}; font-style: ${vals.style}; line-height: ${vals.lineHeight}; letter-spacing: ${vals.letterSpacing}px;">
                    The quick brown fox
                </div>

                <div class="jtb-font-controls">
                    <!-- Family -->
                    <div class="jtb-font-row jtb-font-family-row">
                        <label>Family</label>
                        <select class="jtb-font-family-select">${fontOptions}</select>
                    </div>

                    <!-- Size with slider -->
                    <div class="jtb-font-row">
                        <label>Size</label>
                        <div class="jtb-font-slider-group">
                            <input type="range" class="jtb-font-size-slider" min="8" max="120" value="${vals.size}">
                            <input type="number" class="jtb-font-size-input" min="8" max="200" value="${vals.size}">
                            <span class="jtb-font-unit">px</span>
                        </div>
                    </div>

                    <!-- Weight buttons -->
                    <div class="jtb-font-row">
                        <label>Weight</label>
                        <div class="jtb-font-weight-btns">
                            ${weights.map(w => `
                                <button type="button" class="jtb-font-weight-btn${vals.weight === w ? ' active' : ''}" data-weight="${w}" style="font-weight: ${w}">${weightLabels[w]}</button>
                            `).join('')}
                        </div>
                        <input type="hidden" class="jtb-font-weight-select" value="${vals.weight}">
                    </div>

                    <!-- Style toggle -->
                    <div class="jtb-font-row">
                        <label>Style</label>
                        <div class="jtb-font-style-btns">
                            <button type="button" class="jtb-font-style-btn${vals.style === 'normal' ? ' active' : ''}" data-style="normal">Normal</button>
                            <button type="button" class="jtb-font-style-btn${vals.style === 'italic' ? ' active' : ''}" data-style="italic"><em>Italic</em></button>
                        </div>
                        <input type="hidden" class="jtb-font-style-select" value="${vals.style}">
                    </div>

                    <!-- Line Height -->
                    <div class="jtb-font-row">
                        <label>Line Height</label>
                        <div class="jtb-font-slider-group">
                            <input type="range" class="jtb-line-height-slider" min="0.5" max="3" step="0.1" value="${vals.lineHeight}">
                            <input type="number" class="jtb-line-height-input" min="0.5" max="5" step="0.1" value="${vals.lineHeight}">
                        </div>
                    </div>

                    <!-- Letter Spacing -->
                    <div class="jtb-font-row">
                        <label>Spacing</label>
                        <div class="jtb-font-slider-group">
                            <input type="range" class="jtb-letter-spacing-slider" min="-5" max="20" step="0.5" value="${vals.letterSpacing}">
                            <input type="number" class="jtb-letter-spacing-input" min="-5" max="20" step="0.1" value="${vals.letterSpacing}">
                            <span class="jtb-font-unit">px</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    JTB.Fields.renderAlign = function(name, config, value) {
        const currentValue = value || config.default || 'left';

        // Use Feather icons for alignment
        const alignLeftIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('align-left', 16) : '⬅';
        const alignCenterIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('align-center', 16) : '⬌';
        const alignRightIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('align-right', 16) : '➡';
        const alignJustifyIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('align-justify', 16) : '≡';

        const defaultOptions = {
            'left': alignLeftIcon,
            'center': alignCenterIcon,
            'right': alignRightIcon
        };

        // If config has justify option, add it
        const options = config.options || defaultOptions;

        // Map text options to icons if needed
        const iconMap = {
            'left': alignLeftIcon,
            'center': alignCenterIcon,
            'right': alignRightIcon,
            'justify': alignJustifyIcon
        };

        let html = `<div class="jtb-align-wrapper" data-field="${JTB.Fields.esc(name)}">`;
        html += `<input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(currentValue)}">`;

        for (const optValue in options) {
            // Use icon from map if available, otherwise use config value
            const optLabel = iconMap[optValue] || options[optValue];
            const active = optValue == currentValue ? ' jtb-active' : '';

            html += `
                <button type="button"
                    class="jtb-align-btn${active}"
                    data-value="${JTB.Fields.esc(optValue)}"
                    title="${JTB.Fields.esc(optValue)}">
                    ${optLabel}
                </button>
            `;
        }

        html += '</div>';

        return html;
    };

    JTB.Fields.renderMultiSelect = function(name, config, value) {
        const options = config.options || {};
        const selectedValues = value || config.default || [];

        let html = `<div class="jtb-multiselect-wrapper" data-field="${JTB.Fields.esc(name)}">`;
        html += `<input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(selectedValues))}">`;

        for (const optValue in options) {
            const optLabel = options[optValue];
            const checked = selectedValues.includes(optValue) ? ' checked' : '';
            const id = `${name}_${optValue}`.replace(/[^a-zA-Z0-9]/g, '_');

            html += `
                <label class="jtb-multiselect-item" for="${id}">
                    <input type="checkbox"
                        id="${id}"
                        value="${JTB.Fields.esc(optValue)}"${checked}
                        class="jtb-multiselect-checkbox">
                    <span class="jtb-multiselect-label">${JTB.Fields.esc(optLabel)}</span>
                </label>
            `;
        }

        html += '</div>';

        return html;
    };

    // ========================================
    // Gradient Field
    // ========================================

    JTB.Fields.renderGradient = function(name, config, value) {
        // Default stops
        const defaultStops = [
            { color: '#ffffff', position: 0 },
            { color: '#000000', position: 100 }
        ];

        const stops = value || config.default || defaultStops;

        let stopsHtml = '';
        stops.forEach((stop, index) => {
            stopsHtml += JTB.Fields.renderGradientStop(name, stop, index);
        });

        // Generate preview gradient
        const previewGradient = JTB.Fields.generateGradientPreview(stops);

        return `
            <div class="jtb-gradient-field" data-field="${JTB.Fields.esc(name)}">
                <div class="jtb-gradient-preview" style="background: ${previewGradient};"></div>
                <div class="jtb-gradient-stops">
                    ${stopsHtml}
                </div>
                <button type="button" class="jtb-gradient-add-stop">
                    <span>+</span> Add Color Stop
                </button>
                <input type="hidden" name="${JTB.Fields.esc(name)}" value="${JTB.Fields.esc(JSON.stringify(stops))}">
            </div>
        `;
    };

    JTB.Fields.renderGradientStop = function(name, stop, index) {
        // Get Feather icon for remove
        const removeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 12) : '×';

        return `
            <div class="jtb-gradient-stop" data-index="${index}">
                <input type="color"
                    class="jtb-gradient-stop-color"
                    value="${JTB.Fields.esc(stop.color)}"
                    data-stop-index="${index}"
                    data-prop="color">
                <input type="number"
                    class="jtb-gradient-stop-position"
                    value="${stop.position}"
                    min="0"
                    max="100"
                    placeholder="%"
                    data-stop-index="${index}"
                    data-prop="position">
                <span>%</span>
                <button type="button" class="jtb-gradient-stop-remove" data-stop-index="${index}" title="Remove stop">${removeIcon}</button>
            </div>
        `;
    };

    JTB.Fields.generateGradientPreview = function(stops) {
        if (!stops || stops.length === 0) {
            return 'transparent';
        }

        // Sort stops by position
        const sortedStops = [...stops].sort((a, b) => a.position - b.position);

        const stopStrings = sortedStops.map(s => `${s.color} ${s.position}%`);
        return `linear-gradient(90deg, ${stopStrings.join(', ')})`;
    };

    // Initialize gradient field events
    JTB.Fields.initGradientField = function(container) {
        const field = container.querySelector('.jtb-gradient-field');
        if (!field) return;

        const fieldName = field.dataset.field;
        const hiddenInput = field.querySelector(`input[name="${fieldName}"]`);
        const stopsContainer = field.querySelector('.jtb-gradient-stops');
        const preview = field.querySelector('.jtb-gradient-preview');

        // Function to get current stops from DOM
        const getStops = () => {
            const stops = [];
            field.querySelectorAll('.jtb-gradient-stop').forEach((stopEl) => {
                const color = stopEl.querySelector('.jtb-gradient-stop-color').value;
                const position = parseInt(stopEl.querySelector('.jtb-gradient-stop-position').value) || 0;
                stops.push({ color, position });
            });
            return stops;
        };

        // Function to update hidden input and preview
        const updateValue = () => {
            const stops = getStops();
            hiddenInput.value = JSON.stringify(stops);
            preview.style.background = JTB.Fields.generateGradientPreview(stops);

            // Directly call JTB.Settings.setValue for live preview update
            if (typeof JTB.Settings !== 'undefined' && typeof JTB.Settings.setValue === 'function') {
                JTB.Settings.setValue(fieldName, stops);
            }
        };

        // Bind color/position change events
        field.addEventListener('input', (e) => {
            if (e.target.matches('.jtb-gradient-stop-color') || e.target.matches('.jtb-gradient-stop-position')) {
                updateValue();
            }
        });

        // Remove stop
        field.addEventListener('click', (e) => {
            if (e.target.matches('.jtb-gradient-stop-remove')) {
                const stops = getStops();
                if (stops.length <= 2) {
                    alert('Minimum 2 color stops required');
                    return;
                }
                const index = parseInt(e.target.dataset.stopIndex);
                e.target.closest('.jtb-gradient-stop').remove();

                // Re-index remaining stops
                field.querySelectorAll('.jtb-gradient-stop').forEach((stopEl, newIndex) => {
                    stopEl.dataset.index = newIndex;
                    stopEl.querySelectorAll('[data-stop-index]').forEach(input => {
                        input.dataset.stopIndex = newIndex;
                    });
                });

                updateValue();
            }
        });

        // Add stop
        field.querySelector('.jtb-gradient-add-stop').addEventListener('click', () => {
            const stops = getStops();
            const lastStop = stops[stops.length - 1];
            const newPosition = Math.min(100, (lastStop?.position || 50) + 10);

            const newStopHtml = JTB.Fields.renderGradientStop(fieldName, {
                color: '#888888',
                position: newPosition
            }, stops.length);

            stopsContainer.insertAdjacentHTML('beforeend', newStopHtml);
            updateValue();
        });
    };

    // ========================================
    // Utilities
    // ========================================

    JTB.Fields.esc = function(str) {
        if (str === null || str === undefined) return '';
        if (typeof str !== 'string') str = String(str);

        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

})();
