/**
 * Theme Builder 3.0 - Element Design System Part 3
 * Animation, Position, Visibility (device-based show/hide)
 */

// ═══════════════════════════════════════════════════════════════════════════
// 9. ANIMATION SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementAnimationSection = function(settings, elementKey, state) {
    const enabled = settings.animation_enabled || false;
    const animType = settings.animation_type || 'fadeIn';
    const duration = settings.animation_duration || '0.6';
    const delay = settings.animation_delay || '0';
    const easing = settings.animation_easing || 'ease-out';
    const iterationCount = settings.animation_iteration || '1';
    const direction = settings.animation_direction || 'normal';
    const fillMode = settings.animation_fill_mode || 'both';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">✨</span>';
    html += '<span class="tb-modal-section-title">Animation</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Enable toggle
    html += '<div class="tb-modal-control-row">';
    html += '<label>Enable Animation</label>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalElementSetting(\'animation_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Animation Type
        html += '<div class="tb-modal-control-row">';
        html += '<label>Animation Type</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'animation_type\', this.value)">';

        const animations = {
            'Fade': ['fadeIn', 'fadeInUp', 'fadeInDown', 'fadeInLeft', 'fadeInRight', 'fadeOut'],
            'Slide': ['slideInUp', 'slideInDown', 'slideInLeft', 'slideInRight'],
            'Zoom': ['zoomIn', 'zoomInUp', 'zoomInDown', 'zoomOut'],
            'Bounce': ['bounceIn', 'bounceInUp', 'bounceInDown', 'bounceInLeft', 'bounceInRight'],
            'Flip': ['flipInX', 'flipInY'],
            'Rotate': ['rotateIn', 'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight'],
            'Attention': ['pulse', 'shake', 'swing', 'wobble', 'heartBeat', 'flash', 'rubberBand'],
            'Special': ['lightSpeedIn', 'jackInTheBox', 'rollIn']
        };

        Object.entries(animations).forEach(([group, anims]) => {
            html += '<optgroup label="' + group + '">';
            anims.forEach(anim => {
                html += '<option value="' + anim + '"' + (animType === anim ? ' selected' : '') + '>' + anim + '</option>';
            });
            html += '</optgroup>';
        });
        html += '</select></div>';

        // Duration
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Duration: <span class="tb-modal-slider-value">' + duration + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0.1" max="3" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalElementSetting(\'animation_duration\', this.value)">';
        html += '</div>';

        // Delay
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Delay: <span class="tb-modal-slider-value">' + delay + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="2" step="0.1" value="' + delay + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalElementSetting(\'animation_delay\', this.value)">';
        html += '</div>';

        // Easing
        html += '<div class="tb-modal-control-row">';
        html += '<label>Easing</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'animation_easing\', this.value)">';
        const easings = [
            ['ease', 'Ease'],
            ['ease-in', 'Ease In'],
            ['ease-out', 'Ease Out'],
            ['ease-in-out', 'Ease In Out'],
            ['linear', 'Linear'],
            ['cubic-bezier(0.68, -0.55, 0.265, 1.55)', 'Back'],
            ['cubic-bezier(0.175, 0.885, 0.32, 1.275)', 'Elastic'],
            ['cubic-bezier(0.25, 0.46, 0.45, 0.94)', 'Quad Out'],
            ['cubic-bezier(0.6, 0.04, 0.98, 0.335)', 'Circ In']
        ];
        easings.forEach(e => {
            html += '<option value="' + e[0] + '"' + (easing === e[0] ? ' selected' : '') + '>' + e[1] + '</option>';
        });
        html += '</select></div>';

        // Iteration Count
        html += '<div class="tb-modal-control-row">';
        html += '<label>Repeat</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'animation_iteration\', this.value)">';
        html += '<option value="1"' + (iterationCount === '1' ? ' selected' : '') + '>Once</option>';
        html += '<option value="2"' + (iterationCount === '2' ? ' selected' : '') + '>Twice</option>';
        html += '<option value="3"' + (iterationCount === '3' ? ' selected' : '') + '>3 times</option>';
        html += '<option value="infinite"' + (iterationCount === 'infinite' ? ' selected' : '') + '>Infinite</option>';
        html += '</select></div>';

        // Direction
        html += '<div class="tb-modal-control-row">';
        html += '<label>Direction</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'animation_direction\', this.value)">';
        html += '<option value="normal"' + (direction === 'normal' ? ' selected' : '') + '>Normal</option>';
        html += '<option value="reverse"' + (direction === 'reverse' ? ' selected' : '') + '>Reverse</option>';
        html += '<option value="alternate"' + (direction === 'alternate' ? ' selected' : '') + '>Alternate</option>';
        html += '<option value="alternate-reverse"' + (direction === 'alternate-reverse' ? ' selected' : '') + '>Alternate Reverse</option>';
        html += '</select></div>';

        // Fill Mode
        html += '<div class="tb-modal-control-row">';
        html += '<label>Fill Mode</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'animation_fill_mode\', this.value)">';
        html += '<option value="none"' + (fillMode === 'none' ? ' selected' : '') + '>None</option>';
        html += '<option value="forwards"' + (fillMode === 'forwards' ? ' selected' : '') + '>Forwards</option>';
        html += '<option value="backwards"' + (fillMode === 'backwards' ? ' selected' : '') + '>Backwards</option>';
        html += '<option value="both"' + (fillMode === 'both' ? ' selected' : '') + '>Both</option>';
        html += '</select></div>';

        // Scroll Trigger
        const scrollTrigger = settings.animation_scroll_trigger || false;
        html += '<div class="tb-modal-control-row">';
        html += '<label>Trigger on Scroll</label>';
        html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (scrollTrigger ? 'checked' : '') + ' onchange="TB.updateModalElementSetting(\'animation_scroll_trigger\', this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
        html += '</div>';

        // Preview button
        html += '<button type="button" class="tb-modal-preview-btn" onclick="TB.previewElementAnimation()">▶ Preview Animation</button>';
    }

    html += '</div></div>';
    return html;
};

// Preview animation
TB.previewElementAnimation = function() {
    const preview = document.getElementById('tb-modal-preview');
    if (!preview) return;

    const settings = this.getElementSettings(this.modalState.currentElement, this.modalState.currentState);
    const animType = settings.animation_type || 'fadeIn';
    const duration = settings.animation_duration || '0.6';
    const easing = settings.animation_easing || 'ease-out';

    // Remove existing animation classes
    preview.style.animation = 'none';
    preview.offsetHeight; // Trigger reflow

    // Apply animation
    preview.style.animation = animType + ' ' + duration + 's ' + easing;

    // Remove after completion
    setTimeout(() => {
        preview.style.animation = '';
    }, parseFloat(duration) * 1000 + 100);
};

// ═══════════════════════════════════════════════════════════════════════════
// 10. POSITION SECTION FOR ELEMENTS
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementPositionSection = function(settings, elementKey, state) {
    const posType = settings.position || 'static';
    const zIndex = settings.z_index || 'auto';
    const display = settings.display || '';
    const overflow = settings.overflow || 'visible';
    const showOffsets = ['relative', 'absolute', 'fixed', 'sticky'].includes(posType);

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">📍</span>';
    html += '<span class="tb-modal-section-title">Position & Layout</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Display
    html += '<div class="tb-modal-control-row">';
    html += '<label>Display</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'display\', this.value)">';
    ['', 'block', 'inline', 'inline-block', 'flex', 'inline-flex', 'grid', 'none'].forEach(d => {
        const label = d === '' ? 'Default' : d;
        html += '<option value="' + d + '"' + (display === d ? ' selected' : '') + '>' + label + '</option>';
    });
    html += '</select></div>';

    // Position Type
    html += '<div class="tb-modal-control-row">';
    html += '<label>Position</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'position\', this.value); TB.renderModalDesignSettings()">';
    ['static', 'relative', 'absolute', 'fixed', 'sticky'].forEach(p => {
        html += '<option value="' + p + '"' + (posType === p ? ' selected' : '') + '>' + p.charAt(0).toUpperCase() + p.slice(1) + '</option>';
    });
    html += '</select></div>';

    // Position Offsets (when not static)
    if (showOffsets) {
        html += '<div class="tb-modal-subsection-title">Position Offsets</div>';
        html += '<div class="tb-modal-position-grid">';
        ['top', 'right', 'bottom', 'left'].forEach(side => {
            const val = settings['position_' + side] || '';
            html += '<div class="tb-modal-position-item">';
            html += '<label>' + side.charAt(0).toUpperCase() + side.slice(1) + '</label>';
            html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(val) + '" placeholder="auto" onchange="TB.updateModalElementSetting(\'position_' + side + '\', this.value)">';
            html += '</div>';
        });
        html += '</div>';
    }

    // Z-Index
    html += '<div class="tb-modal-control-row">';
    html += '<label>Z-Index</label>';
    html += '<input type="text" class="tb-modal-input" value="' + zIndex + '" placeholder="auto" onchange="TB.updateModalElementSetting(\'z_index\', this.value)">';
    html += '</div>';

    // Z-Index presets
    html += '<div class="tb-modal-zindex-presets">';
    ['-1', '0', '1', '10', '100', '999', 'auto'].forEach(z => {
        html += '<button type="button" class="tb-modal-preset-btn' + (zIndex === z ? ' active' : '') + '" onclick="TB.updateModalElementSetting(\'z_index\', \'' + z + '\'); TB.refreshZIndexUI(this)">' + z + '</button>';
    });
    html += '</div>';

    // Overflow
    html += '<div class="tb-modal-control-row">';
    html += '<label>Overflow</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'overflow\', this.value)">';
    ['visible', 'hidden', 'scroll', 'auto', 'clip'].forEach(o => {
        html += '<option value="' + o + '"' + (overflow === o ? ' selected' : '') + '>' + o.charAt(0).toUpperCase() + o.slice(1) + '</option>';
    });
    html += '</select></div>';

    // Width and Height
    html += '<div class="tb-modal-subsection-title">Dimensions</div>';

    const width = settings.width || '';
    const height = settings.height || '';
    const minWidth = settings.min_width || '';
    const maxWidth = settings.max_width || '';
    const minHeight = settings.min_height || '';
    const maxHeight = settings.max_height || '';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Width</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(width) + '" placeholder="auto" onchange="TB.updateModalElementSetting(\'width\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Height</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(height) + '" placeholder="auto" onchange="TB.updateModalElementSetting(\'height\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-two-col">';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Min Width</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(minWidth) + '" placeholder="0" onchange="TB.updateModalElementSetting(\'min_width\', this.value)">';
    html += '</div>';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Max Width</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(maxWidth) + '" placeholder="none" onchange="TB.updateModalElementSetting(\'max_width\', this.value)">';
    html += '</div>';
    html += '</div>';

    html += '<div class="tb-modal-two-col">';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Min Height</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(minHeight) + '" placeholder="0" onchange="TB.updateModalElementSetting(\'min_height\', this.value)">';
    html += '</div>';
    html += '<div class="tb-modal-control-row">';
    html += '<label>Max Height</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(maxHeight) + '" placeholder="none" onchange="TB.updateModalElementSetting(\'max_height\', this.value)">';
    html += '</div>';
    html += '</div>';

    html += '</div></div>';
    return html;
};

// Refresh Z-Index UI
TB.refreshZIndexUI = function(clickedBtn) {
    document.querySelectorAll('.tb-modal-zindex-presets .tb-modal-preset-btn').forEach(btn => btn.classList.remove('active'));
    if (clickedBtn) clickedBtn.classList.add('active');
};

// ═══════════════════════════════════════════════════════════════════════════
// 11. VISIBILITY SECTION FOR ELEMENTS (Device-based show/hide)
// ═══════════════════════════════════════════════════════════════════════════
TB.renderElementVisibilitySection = function(settings, elementKey, state) {
    const hideDesktop = settings.hide_desktop || false;
    const hideTablet = settings.hide_tablet || false;
    const hideMobile = settings.hide_mobile || false;
    const visibility = settings.visibility || 'visible';
    const pointerEvents = settings.pointer_events || 'auto';
    const opacity = settings.opacity || '1';
    const cursor = settings.cursor || '';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">👁️</span>';
    html += '<span class="tb-modal-section-title">Visibility</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // ─────────────────────────────────────────────────────────────────
    // DEVICE VISIBILITY
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Show/Hide per Device</div>';

    html += '<div class="tb-modal-visibility-devices">';

    // Desktop
    html += '<div class="tb-modal-visibility-device">';
    html += '<div class="tb-modal-device-icon">🖥️</div>';
    html += '<div class="tb-modal-device-label">Desktop</div>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (hideDesktop ? '' : 'checked') + ' onchange="TB.updateModalElementSetting(\'hide_desktop\', !this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
    html += '<div class="tb-modal-device-status">' + (hideDesktop ? 'Hidden' : 'Visible') + '</div>';
    html += '</div>';

    // Tablet
    html += '<div class="tb-modal-visibility-device">';
    html += '<div class="tb-modal-device-icon">📱</div>';
    html += '<div class="tb-modal-device-label">Tablet</div>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (hideTablet ? '' : 'checked') + ' onchange="TB.updateModalElementSetting(\'hide_tablet\', !this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
    html += '<div class="tb-modal-device-status">' + (hideTablet ? 'Hidden' : 'Visible') + '</div>';
    html += '</div>';

    // Mobile
    html += '<div class="tb-modal-visibility-device">';
    html += '<div class="tb-modal-device-icon">📲</div>';
    html += '<div class="tb-modal-device-label">Mobile</div>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (hideMobile ? '' : 'checked') + ' onchange="TB.updateModalElementSetting(\'hide_mobile\', !this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
    html += '<div class="tb-modal-device-status">' + (hideMobile ? 'Hidden' : 'Visible') + '</div>';
    html += '</div>';

    html += '</div>';

    // Breakpoint info
    html += '<div class="tb-modal-info-box tb-modal-info-small">';
    html += '<span class="tb-modal-info-icon">ℹ️</span>';
    html += '<span>Desktop: >1024px | Tablet: 768-1024px | Mobile: <768px</span>';
    html += '</div>';

    // ─────────────────────────────────────────────────────────────────
    // CSS VISIBILITY OPTIONS
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">CSS Visibility</div>';

    // Visibility property
    html += '<div class="tb-modal-control-row">';
    html += '<label>Visibility</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'visibility\', this.value)">';
    ['visible', 'hidden', 'collapse'].forEach(v => {
        html += '<option value="' + v + '"' + (visibility === v ? ' selected' : '') + '>' + v.charAt(0).toUpperCase() + v.slice(1) + '</option>';
    });
    html += '</select></div>';

    // Opacity
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Opacity: <span class="tb-modal-slider-value">' + opacity + '</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="1" step="0.05" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value" onchange="TB.updateModalElementSetting(\'opacity\', this.value)">';
    html += '</div>';

    // Pointer Events
    html += '<div class="tb-modal-control-row">';
    html += '<label>Pointer Events</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'pointer_events\', this.value)">';
    ['auto', 'none', 'all'].forEach(p => {
        html += '<option value="' + p + '"' + (pointerEvents === p ? ' selected' : '') + '>' + p + '</option>';
    });
    html += '</select></div>';

    // Cursor
    html += '<div class="tb-modal-control-row">';
    html += '<label>Cursor</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalElementSetting(\'cursor\', this.value)">';
    const cursors = ['', 'pointer', 'default', 'move', 'grab', 'grabbing', 'not-allowed', 'wait', 'progress', 'help', 'crosshair', 'text', 'zoom-in', 'zoom-out'];
    cursors.forEach(c => {
        const label = c === '' ? 'Default' : c;
        html += '<option value="' + c + '"' + (cursor === c ? ' selected' : '') + '>' + label + '</option>';
    });
    html += '</select></div>';

    // ─────────────────────────────────────────────────────────────────
    // QUICK VISIBILITY ACTIONS
    // ─────────────────────────────────────────────────────────────────
    html += '<div class="tb-modal-subsection-title">Quick Actions</div>';
    html += '<div class="tb-modal-preset-grid">';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.setElementVisibilityPreset(\'all\')">Show All</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.setElementVisibilityPreset(\'desktop-only\')">Desktop Only</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.setElementVisibilityPreset(\'mobile-only\')">Mobile Only</button>';
    html += '<button type="button" class="tb-modal-preset-btn" onclick="TB.setElementVisibilityPreset(\'hide-all\')">Hide All</button>';
    html += '</div>';

    html += '</div></div>';
    return html;
};

// Set visibility preset
TB.setElementVisibilityPreset = function(preset) {
    const presets = {
        'all': { hide_desktop: false, hide_tablet: false, hide_mobile: false },
        'desktop-only': { hide_desktop: false, hide_tablet: true, hide_mobile: true },
        'tablet-only': { hide_desktop: true, hide_tablet: false, hide_mobile: true },
        'mobile-only': { hide_desktop: true, hide_tablet: true, hide_mobile: false },
        'hide-all': { hide_desktop: true, hide_tablet: true, hide_mobile: true }
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
// ADDITIONAL HELPER: Refresh Modal Control UI
// ═══════════════════════════════════════════════════════════════════════════
TB.refreshModalControlUI = function(clickedBtn) {
    if (clickedBtn) {
        const group = clickedBtn.closest('.tb-modal-btn-group');
        if (group) {
            group.querySelectorAll('.tb-modal-btn-opt').forEach(btn => btn.classList.remove('active'));
            clickedBtn.classList.add('active');
        }
    }
};

// ═══════════════════════════════════════════════════════════════════════════
// RGBA to HEX converter (if not already defined)
// ═══════════════════════════════════════════════════════════════════════════
if (!TB.rgbaToHex) {
    TB.rgbaToHex = function(rgba) {
        if (!rgba) return '#000000';
        if (rgba.startsWith('#')) return rgba;

        const match = rgba.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        if (match) {
            const r = parseInt(match[1]).toString(16).padStart(2, '0');
            const g = parseInt(match[2]).toString(16).padStart(2, '0');
            const b = parseInt(match[3]).toString(16).padStart(2, '0');
            return '#' + r + g + b;
        }
        return '#000000';
    };
}

console.log('TB Modal Element Design System loaded - Part 3 (Animation, Position, Visibility)');
