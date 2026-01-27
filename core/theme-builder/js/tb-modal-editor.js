/**
 * Theme Builder 3.0 - Modal Editor
 * Full-screen modal editor for comprehensive module editing
 * Part of Inner Elements Styling System
 */

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS (define if not already present)
// ═══════════════════════════════════════════════════════════
if (!TB.formatPropertyLabel) {
    TB.formatPropertyLabel = function(prop) {
        return prop.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    };
}

// Compatibility alias: escapeHtml -> escHtml (inline TB uses escHtml)
if (!TB.escapeHtml && TB.escHtml) {
    TB.escapeHtml = TB.escHtml;
} else if (!TB.escapeHtml) {
    TB.escapeHtml = function(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };
}

if (!TB.getPropertyPlaceholder) {
    TB.getPropertyPlaceholder = function(prop) {
        const placeholders = {
            font_size: '16',
            padding: '15',
            margin_bottom: '10',
            border_radius: '4',
            line_height: '1.6',
            gap: '20',
            width: '100',
            height: 'auto'
        };
        return placeholders[prop] || '';
    };
}

// Define elementSchemas if not already present (minimal fallback)
if (!TB.elementSchemas) {
    TB.elementSchemas = {
        toggle: {
            header: { label: 'Header', states: ['normal', 'hover', 'active'], properties: ['background', 'color', 'font_size', 'padding'] },
            content: { label: 'Content', states: ['normal'], properties: ['background', 'color', 'padding'] },
            icon: { label: 'Icon', states: ['normal', 'active'], properties: ['color', 'font_size'] }
        },
        accordion: {
            header: { label: 'Header', states: ['normal', 'hover', 'active'], properties: ['background', 'color', 'font_size', 'padding'] },
            content: { label: 'Content', states: ['normal'], properties: ['background', 'color', 'padding'] }
        },
        tabs: {
            nav: { label: 'Navigation', states: ['normal'], properties: ['background', 'padding'] },
            tab_button: { label: 'Tab Button', states: ['normal', 'hover', 'active'], properties: ['background', 'color', 'padding'] },
            content: { label: 'Content', states: ['normal'], properties: ['background', 'color', 'padding'] }
        },
        button: {
            button: { label: 'Button', states: ['normal', 'hover', 'active'], properties: ['background', 'color', 'font_size', 'padding', 'border_radius'] }
        },
        text: {
            paragraph: { label: 'Paragraph', states: ['normal'], properties: ['color', 'font_size', 'line_height'] },
            link: { label: 'Link', states: ['normal', 'hover'], properties: ['color'] }
        },
        heading: {
            heading: { label: 'Heading', states: ['normal'], properties: ['color', 'font_size', 'font_weight'] }
        },
        image: {
            container: { label: 'Container', states: ['normal', 'hover'], properties: ['border_radius', 'box_shadow', 'border'] },
            image: { label: 'Image', states: ['normal', 'hover'], properties: ['opacity', 'filter'] },
            caption: { label: 'Caption', states: ['normal', 'hover'], properties: ['color', 'font_size', 'font_weight', 'text_align', 'margin'] },
            overlay: { label: 'Overlay', states: ['normal', 'hover'], properties: ['background', 'opacity'] }
        },
        hero: {
            title: { label: 'Title', states: ['normal'], properties: ['color', 'font_size', 'font_weight'] },
            subtitle: { label: 'Subtitle', states: ['normal'], properties: ['color', 'font_size'] },
            button: { label: 'Button', states: ['normal', 'hover'], properties: ['background', 'color', 'padding', 'border_radius'] }
        },
        form: {
            label: { label: 'Label', states: ['normal'], properties: ['color', 'font_size'] },
            input: { label: 'Input', states: ['normal', 'focus'], properties: ['background', 'border', 'padding'] },
            submit: { label: 'Submit Button', states: ['normal', 'hover'], properties: ['background', 'color', 'padding'] }
        }
    };
}

// ═══════════════════════════════════════════════════════════
// MODAL STATE
// ═══════════════════════════════════════════════════════════
TB.modalState = {
    isOpen: false,
    sIdx: null,
    rIdx: null,
    cIdx: null,
    mIdx: null,
    currentTab: 'content',
    currentElement: 'wrapper',
    currentState: 'normal',
    originalModule: null
};

// ═══════════════════════════════════════════════════════════
// OPEN MODAL
// ═══════════════════════════════════════════════════════════
TB.openModuleModal = function(sIdx, rIdx, cIdx, mIdx, event) {
    console.log('TB.openModuleModal called:', sIdx, rIdx, cIdx, mIdx);

    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    console.log('Module found:', mod);
    if (!mod) {
        console.error('Module not found at path:', sIdx, rIdx, cIdx, mIdx);
        return;
    }

    // Store original for cancel
    this.modalState.originalModule = JSON.parse(JSON.stringify(mod));
    this.modalState.sIdx = sIdx;
    this.modalState.rIdx = rIdx;
    this.modalState.cIdx = cIdx;
    this.modalState.mIdx = mIdx;
    this.modalState.currentTab = 'content';
    this.modalState.currentElement = 'wrapper';
    this.modalState.currentState = 'normal';
    this.modalState.isOpen = true;

    // IMPORTANT: Also set selectedElement so renderContentSettings works correctly
    // The content settings functions use this.selectedElement to get indices
    this.selectedElement = { type: 'module', sIdx, rIdx, cIdx, mIdx };

    // Create and show modal
    this.createModalHTML(mod);
    this.renderModalContent();

    // Add body class to prevent scrolling
    document.body.classList.add('tb-modal-open');
};

// ═══════════════════════════════════════════════════════════
// CREATE MODAL HTML
// ═══════════════════════════════════════════════════════════
TB.createModalHTML = function(mod) {
    console.log('TB.createModalHTML called for:', mod.type);

    // Remove existing modal if any
    const existing = document.getElementById('tb-module-modal');
    if (existing) existing.remove();

    const icon = this.getModuleIcon ? this.getModuleIcon(mod.type) : '📦';
    const moduleName = (this.modules && this.modules[mod.type]) ? this.modules[mod.type].name : mod.type;
    const schema = this.elementSchemas[mod.type];

    // Build element buttons - Wrapper is always first
    let elementButtons = '<button type="button" class="tb-modal-element-btn active" data-element="wrapper">Wrapper</button>';
    if (schema) {
        for (const [key, def] of Object.entries(schema)) {
            // Skip wrapper - it's already added above
            if (key === 'wrapper') continue;
            elementButtons += '<button type="button" class="tb-modal-element-btn" data-element="' + key + '">' + this.escapeHtml(def.label) + '</button>';
        }
    }

    const html = `
    <div id="tb-module-modal" class="tb-modal-overlay">
        <div class="tb-modal-container">
            <div class="tb-modal-header">
                <div class="tb-modal-title">${icon} ${this.escapeHtml(moduleName)} Settings</div>
                <button type="button" class="tb-modal-close" onclick="TB.confirmCloseModal()" title="Close (will ask to save if changes)">&times;</button>
            </div>

            <div class="tb-modal-tabs">
                <button type="button" class="tb-modal-tab active" data-tab="content" onclick="TB.switchModalTab('content')">Content</button>
                <button type="button" class="tb-modal-tab" data-tab="design" onclick="TB.switchModalTab('design')">Design</button>
                <button type="button" class="tb-modal-tab" data-tab="advanced" onclick="TB.switchModalTab('advanced')">Advanced</button>
            </div>

            <div class="tb-modal-device-toggle">
                <span class="tb-modal-device-label">DEVICE</span>
                <button type="button" class="tb-modal-device-btn active" data-device="desktop" onclick="TB.setModalDevice('desktop')" title="Desktop Preview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </button>
                <button type="button" class="tb-modal-device-btn" data-device="tablet" onclick="TB.setModalDevice('tablet')" title="Tablet Preview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                </button>
                <button type="button" class="tb-modal-device-btn" data-device="mobile" onclick="TB.setModalDevice('mobile')" title="Mobile Preview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="tb-modal-body">
                <div class="tb-modal-preview-section">
                    <div class="tb-modal-preview-label">Live Preview</div>
                    <div id="tb-modal-preview" class="tb-modal-preview"></div>
                </div>

                <div class="tb-modal-settings-section">
                    <div id="tb-modal-design-controls" class="tb-modal-design-controls" style="display:none;">
                        <div class="tb-modal-element-selector">
                            <div class="tb-modal-selector-label">Element:</div>
                            <div class="tb-modal-element-buttons">
                                ${elementButtons}
                            </div>
                        </div>

                        <div id="tb-modal-state-selector" class="tb-modal-state-selector">
                            <div class="tb-modal-selector-label">State:</div>
                            <div class="tb-modal-state-buttons">
                                <button type="button" class="tb-modal-state-btn active" data-state="normal" onclick="TB.switchModalState('normal')">Normal</button>
                                <button type="button" class="tb-modal-state-btn" data-state="hover" onclick="TB.switchModalState('hover')">Hover</button>
                                <button type="button" class="tb-modal-state-btn" data-state="active" onclick="TB.switchModalState('active')">Active</button>
                            </div>
                        </div>
                    </div>

                    <div id="tb-modal-settings-content" class="tb-modal-settings-content"></div>
                </div>
            </div>

            <div class="tb-modal-footer">
                <button type="button" class="tb-modal-btn tb-modal-btn-cancel" onclick="TB.cancelModuleModal()">Cancel</button>
                <button type="button" class="tb-modal-btn tb-modal-btn-save" onclick="TB.saveModuleModal()">Save Changes</button>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', html);

    // Bind element button clicks
    document.querySelectorAll('.tb-modal-element-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            console.log('🔘 Element button clicked!');
            console.log('  - e.target:', e.target);
            console.log('  - e.target.dataset.element:', e.target.dataset.element);
            
            const element = e.target.dataset.element;
            document.querySelectorAll('.tb-modal-element-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            this.modalState.currentElement = element;
            this.modalState.currentState = 'normal';
            
            console.log('  - modalState.currentElement NOW:', this.modalState.currentElement);
            
            this.updateStateSelector();
            this.renderModalDesignSettings();
        });
    });

    // IMPORTANT: Clicking outside the modal should NOT close it (prevents losing changes)
    // User must explicitly click Cancel or Save
    // The overlay click is intentionally not bound to closeModuleModal()

    // Close on Escape key - show confirmation
    document.addEventListener('keydown', this.handleModalKeydown);
};

// Confirmation dialog for unsaved changes
TB.confirmCloseModal = function() {
    // Check if there are unsaved changes
    const { sIdx, rIdx, cIdx, mIdx, originalModule } = this.modalState;
    const currentModule = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];

    const hasChanges = JSON.stringify(currentModule) !== JSON.stringify(originalModule);

    if (hasChanges) {
        if (confirm('You have unsaved changes. Discard them?')) {
            this.cancelModuleModal();
        }
        // If user clicks "Cancel" on confirm dialog, modal stays open
    } else {
        this.closeModuleModal();
    }
};

TB.handleModalKeydown = function(e) {
    if (e.key === 'Escape' && TB.modalState.isOpen) {
        // Use confirmation dialog instead of closing directly
        TB.confirmCloseModal();
    }
};

// ═══════════════════════════════════════════════════════════
// UPDATE STATE SELECTOR
// ═══════════════════════════════════════════════════════════
TB.updateStateSelector = function() {
    const { sIdx, rIdx, cIdx, mIdx, currentElement } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    const stateSelector = document.getElementById('tb-modal-state-selector');
    if (!stateSelector) return;

    // Get available states for current element
    let states = ['normal'];
    if (currentElement === 'wrapper') {
        states = ['normal', 'hover'];
    } else {
        const schema = this.elementSchemas[mod.type];
        if (schema && schema[currentElement]) {
            states = schema[currentElement].states || ['normal'];
        }
    }

    // Build state buttons
    let html = '<div class="tb-modal-selector-label">State:</div><div class="tb-modal-state-buttons">';
    states.forEach((state, idx) => {
        const isActive = idx === 0 ? ' active' : '';
        const label = state.charAt(0).toUpperCase() + state.slice(1);
        html += '<button type="button" class="tb-modal-state-btn' + isActive + '" data-state="' + state + '" onclick="TB.switchModalState(\'' + state + '\')">' + label + '</button>';
    });
    html += '</div>';

    stateSelector.innerHTML = html;
    this.modalState.currentState = states[0];
};

// ═══════════════════════════════════════════════════════════
// SWITCH MODAL TAB
// ═══════════════════════════════════════════════════════════
TB.switchModalTab = function(tab) {
    this.modalState.currentTab = tab;

    // Update tab buttons
    document.querySelectorAll('.tb-modal-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.tab === tab);
    });

    // Show/hide design controls
    const designControls = document.getElementById('tb-modal-design-controls');
    if (designControls) {
        designControls.style.display = tab === 'design' ? 'block' : 'none';
    }

    this.renderModalContent();
};

// ═══════════════════════════════════════════════════════════
// SWITCH MODAL STATE
// ═══════════════════════════════════════════════════════════
TB.switchModalState = function(state) {
    this.modalState.currentState = state;

    // Update state buttons
    document.querySelectorAll('.tb-modal-state-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.state === state);
    });

    this.renderModalDesignSettings();
};

// ═══════════════════════════════════════════════════════════
// RENDER MODAL CONTENT
// ═══════════════════════════════════════════════════════════
TB.renderModalContent = function() {
    const { sIdx, rIdx, cIdx, mIdx, currentTab } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    const container = document.getElementById('tb-modal-settings-content');
    if (!container) return;

    let html = '';

    switch (currentTab) {
        case 'content':
            html = this.renderModalContentSettings(mod);
            break;
        case 'design':
            html = this.renderModalDesignSettingsHTML(mod);
            break;
        case 'advanced':
            html = this.renderModalAdvancedSettings(mod);
            break;
    }

    container.innerHTML = html;
    this.updateModalPreview();
};

// ═══════════════════════════════════════════════════════════
// RENDER MODAL CONTENT SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderModalContentSettings = function(mod) {
    // Reuse existing content settings renderer with indices from modalState
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    if (typeof this.renderContentSettings === 'function') {
        return this.renderContentSettings(mod, sIdx, rIdx, cIdx, mIdx);
    }
    // Fallback simple content display
    return '<div class="tb-modal-setting"><p>Content settings not available. Use sidebar panel.</p></div>';
};

// ═══════════════════════════════════════════════════════════
// RENDER MODAL DESIGN SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderModalDesignSettingsHTML = function(mod) {
    const { currentElement, currentState } = this.modalState;
    let html = '<div class="tb-modal-design-section">';

    if (currentElement === 'wrapper') {
        html += this.renderModalWrapperSettings(mod);
    } else {
        html += this.renderModalElementSettings(mod, currentElement, currentState);
    }

    html += '</div>';
    return html;
};

TB.renderModalDesignSettings = function() {
    const { sIdx, rIdx, cIdx, mIdx, currentElement, currentState } = this.modalState;
    console.log('🎨 renderModalDesignSettings called');
    console.log('  - currentElement:', currentElement);
    console.log('  - currentState:', currentState);
    
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    const container = document.getElementById('tb-modal-settings-content');
    if (!container) return;

    let html = '<div class="tb-modal-design-section">';

    if (currentElement === 'wrapper') {
        console.log('  → Rendering WRAPPER settings');
        html += this.renderModalWrapperSettings(mod);
    } else {
        console.log('  → Rendering ELEMENT settings for:', currentElement);
        console.log('  → Checking if renderModalElementSettings exists:', typeof this.renderModalElementSettings);
        
        try {
            const elementHTML = this.renderModalElementSettings(mod, currentElement, currentState);
            console.log('  → renderModalElementSettings returned:', elementHTML ? 'HTML (' + elementHTML.length + ' chars)' : 'null/undefined');
            html += elementHTML;
        } catch (err) {
            console.error('  ❌ ERROR in renderModalElementSettings:', err);
            html += '<div class="tb-modal-error">Error: ' + err.message + '</div>';
        }
    }

    html += '</div>';
    container.innerHTML = html;
    this.updateModalPreview();
};

// ═══════════════════════════════════════════════════════════
// RENDER WRAPPER SETTINGS - COMPREHENSIVE VERSION
// Includes ALL design tools from sidebar panel
// ═══════════════════════════════════════════════════════════
// FIX: Merge settings AND design for reading current values
// This ensures Modal and Sidebar show the same values
// ═══════════════════════════════════════════════════════════
TB.renderModalWrapperSettings = function(mod) {
    // Merge both settings and design - design takes precedence (unified with sidebar)
    const settings = { ...(mod.settings || {}), ...(mod.design || {}) };
    const { sIdx, rIdx, cIdx, mIdx, currentState } = this.modalState;
    const type = mod.type || 'text';
    const hasTypography = this.textModules && this.textModules.includes(type);

    let html = '<div class="tb-modal-design-full">';

    if (currentState === 'normal') {
        // ═══════════════════════════════════════════════════════════
        // TYPOGRAPHY SECTION (for text modules)
        // ═══════════════════════════════════════════════════════════
        if (hasTypography) {
            html += this.renderModalTypographySection(mod, settings);
        }

        // ═══════════════════════════════════════════════════════════
        // SPACING SECTION (Visual Box Model)
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalSpacingSection(settings);

        // ═══════════════════════════════════════════════════════════
        // BACKGROUND SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalBackgroundSection(settings);

        // ═══════════════════════════════════════════════════════════
        // LAYOUT SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalLayoutSection(settings);

        // ═══════════════════════════════════════════════════════════
        // BORDER SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalBorderSection(settings);

        // ═══════════════════════════════════════════════════════════
        // BOX SHADOW SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalBoxShadowSection(settings);

        // ═══════════════════════════════════════════════════════════
        // HOVER EFFECTS SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalHoverSection(settings);

        // ═══════════════════════════════════════════════════════════
        // FILTERS & TRANSFORMS REMOVED FROM WRAPPER
        // These belong in Element Settings only (Image, Title, etc.)
        // Wrapper should only control: spacing, background, border, shadow, position
        // ═══════════════════════════════════════════════════════════

        // ═══════════════════════════════════════════════════════════
        // POSITION SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalPositionSection(settings);

        // ═══════════════════════════════════════════════════════════
        // ANIMATION SECTION
        // ═══════════════════════════════════════════════════════════
        html += this.renderModalAnimationSection(settings);

    } else if (currentState === 'hover') {
        // Hover state - show hover-specific settings
        html += this.renderModalHoverStateSettings(settings);
    }

    html += '</div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// TYPOGRAPHY SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalTypographySection = function(mod, settings) {
    const typography = settings.typography_default || {};

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
    html += '<select class="tb-modal-select" onchange="TB.updateModalTypography(\'font_family\', this.value)">';
    const fonts = [['', 'Default'], ['system-ui, -apple-system, sans-serif', 'System UI'], ['Arial, Helvetica, sans-serif', 'Arial'],
        ['Georgia, serif', 'Georgia'], ['Times New Roman, Times, serif', 'Times New Roman'], ['Inter, sans-serif', 'Inter'],
        ['Roboto, sans-serif', 'Roboto'], ['Open Sans, sans-serif', 'Open Sans'], ['Poppins, sans-serif', 'Poppins']];
    fonts.forEach(f => { html += '<option value="' + f[0] + '"' + (typography.font_family === f[0] ? ' selected' : '') + '>' + f[1] + '</option>'; });
    html += '</select></div>';

    // Font Size
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Size</label>';
    html += '<div class="tb-modal-input-with-unit">';
    html += '<input type="number" class="tb-modal-input" value="' + (parseInt(typography.font_size) || '') + '" placeholder="16" min="8" max="200" onchange="TB.updateModalTypographyWithUnit(\'font_size\', this.value, this.nextElementSibling.value)">';
    html += '<select class="tb-modal-unit-select" onchange="TB.updateModalTypographyWithUnit(\'font_size\', this.previousElementSibling.value, this.value)">';
    const sizeUnit = typography.font_size ? typography.font_size.toString().replace(/[0-9.]/g, '') || 'px' : 'px';
    html += '<option value="px"' + (sizeUnit === 'px' ? ' selected' : '') + '>px</option>';
    html += '<option value="em"' + (sizeUnit === 'em' ? ' selected' : '') + '>em</option>';
    html += '<option value="rem"' + (sizeUnit === 'rem' ? ' selected' : '') + '>rem</option>';
    html += '</select></div></div>';

    // Font Weight
    html += '<div class="tb-modal-control-row">';
    html += '<label>Font Weight</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalTypography(\'font_weight\', this.value)">';
    [['', 'Default'], ['400', '400 - Normal'], ['500', '500 - Medium'], ['600', '600 - Semi Bold'], ['700', '700 - Bold']].forEach(w => {
        html += '<option value="' + w[0] + '"' + (typography.font_weight === w[0] ? ' selected' : '') + '>' + w[1] + '</option>';
    });
    html += '</select></div>';

    // Line Height
    html += '<div class="tb-modal-control-row">';
    html += '<label>Line Height</label>';
    html += '<input type="number" class="tb-modal-input" value="' + (parseFloat(typography.line_height) || '') + '" placeholder="1.6" min="0.5" max="5" step="0.1" onchange="TB.updateModalTypography(\'line_height\', this.value)">';
    html += '</div>';

    // Text Color
    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Color</label>';
    html += this.renderModalColorPicker('typo_color', typography.color || '', 'TB.updateModalTypography(\'color\', VALUE)');
    html += '</div>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// SPACING SECTION (Visual Box Model)
// ═══════════════════════════════════════════════════════════
TB.renderModalSpacingSection = function(settings) {
    const mTop = parseInt(settings.marginTop) || parseInt(settings.margin_top) || 0;
    const mRight = parseInt(settings.marginRight) || parseInt(settings.margin_right) || 0;
    const mBottom = parseInt(settings.marginBottom) || parseInt(settings.margin_bottom) || 0;
    const mLeft = parseInt(settings.marginLeft) || parseInt(settings.margin_left) || 0;
    const pTop = parseInt(settings.paddingTop) || parseInt(settings.padding_top) || 0;
    const pRight = parseInt(settings.paddingRight) || parseInt(settings.padding_right) || 0;
    const pBottom = parseInt(settings.paddingBottom) || parseInt(settings.padding_bottom) || 0;
    const pLeft = parseInt(settings.paddingLeft) || parseInt(settings.padding_left) || 0;

    // Get linked states
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
    html += '<div class="tb-modal-spacing-box-outer">';
    html += '<span class="tb-modal-spacing-label-outer">MARGIN</span>';
    html += '<div class="tb-modal-spacing-input-top"><input type="number" value="' + mTop + '" min="-500" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'margin\', \'Top\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-right"><input type="number" value="' + mRight + '" min="-500" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'margin\', \'Right\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-bottom"><input type="number" value="' + mBottom + '" min="-500" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'margin\', \'Bottom\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-left"><input type="number" value="' + mLeft + '" min="-500" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'margin\', \'Left\', this.value)"></div>';
    // Link button for margin (positioned in center of margin area)
    html += '<button type="button" class="tb-modal-spacing-link-btn tb-modal-spacing-link-margin' + (marginLinked ? ' linked' : '') + '" onclick="TB.toggleModalSpacingLink(\'margin\')" title="Link all margin values">' + (marginLinked ? '🔗' : '⛓️') + '</button>';

    // Padding box (inner)
    html += '<div class="tb-modal-spacing-box-inner">';
    html += '<span class="tb-modal-spacing-label-inner">PADDING</span>';
    html += '<div class="tb-modal-spacing-input-top"><input type="number" value="' + pTop + '" min="0" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'padding\', \'Top\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-right"><input type="number" value="' + pRight + '" min="0" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'padding\', \'Right\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-bottom"><input type="number" value="' + pBottom + '" min="0" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'padding\', \'Bottom\', this.value)"></div>';
    html += '<div class="tb-modal-spacing-input-left"><input type="number" value="' + pLeft + '" min="0" max="500" placeholder="0" onchange="TB.updateModalSpacingValue(\'padding\', \'Left\', this.value)"></div>';
    // Link button for padding (positioned in center of padding area)
    html += '<button type="button" class="tb-modal-spacing-link-btn tb-modal-spacing-link-padding' + (paddingLinked ? ' linked' : '') + '" onclick="TB.toggleModalSpacingLink(\'padding\')" title="Link all padding values">' + (paddingLinked ? '🔗' : '⛓️') + '</button>';
    html += '<div class="tb-modal-spacing-content-box">Content</div>';
    html += '</div>'; // inner
    html += '</div>'; // outer
    html += '</div>'; // visual

    html += '</div></div>';
    return html;
};

// Helper to get current module from modal state
TB.getModuleFromModalState = function() {
    if (!this.modalState) return null;
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    return this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx] || null;
};

// Toggle spacing link for margin or padding
TB.toggleModalSpacingLink = function(type) {
    if (!this.modalState) return;
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.settings || Array.isArray(mod.settings)) mod.settings = {};

    const linkKey = type + '_linked';
    const isLinked = !mod.settings[linkKey];
    mod.settings[linkKey] = isLinked;

    // When linking, sync all values to the top value
    if (isLinked) {
        const topKey = type + 'Top';
        const topValue = mod.settings[topKey] || '0px';
        mod.settings[type + 'Right'] = topValue;
        mod.settings[type + 'Bottom'] = topValue;
        mod.settings[type + 'Left'] = topValue;
    }

    // Update button appearance immediately
    const btn = document.querySelector('.tb-modal-spacing-link-' + type);
    if (btn) {
        btn.classList.toggle('linked', isLinked);
        btn.textContent = isLinked ? '🔗' : '⛓️';
    }

    // Refresh the spacing inputs in the modal
    this.refreshModalSpacingInputs(type, mod);

    // Update preview and save
    if (typeof this.updateModalPreview === 'function') {
        this.updateModalPreview();
    }
    this.saveToHistory();
    this.renderCanvas();
};

// Update spacing value with linked support
TB.updateModalSpacingValue = function(type, side, value) {
    if (!this.modalState) return;
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.settings || Array.isArray(mod.settings)) mod.settings = {};

    // Convert to px if not already
    if (value !== '' && value !== null && value !== undefined) {
        const numVal = parseFloat(value);
        if (!isNaN(numVal) && !String(value).includes('px')) {
            value = numVal + 'px';
        }
    } else {
        value = '0px';
    }

    const isLinked = mod.settings[type + '_linked'];

    if (isLinked) {
        // Update all 4 sides
        mod.settings[type + 'Top'] = value;
        mod.settings[type + 'Right'] = value;
        mod.settings[type + 'Bottom'] = value;
        mod.settings[type + 'Left'] = value;
        // Refresh all inputs to show the same value
        this.refreshModalSpacingInputs(type, mod);
    } else {
        // Update just this side
        mod.settings[type + side] = value;
    }

    // Update preview and save
    if (typeof this.updateModalPreview === 'function') {
        this.updateModalPreview();
    }
    this.saveToHistory();
    this.renderCanvas();
};

// Refresh all spacing inputs in modal for a given type (margin or padding)
TB.refreshModalSpacingInputs = function(type, mod) {
    // If mod not passed, try to get it
    if (!mod) {
        mod = this.getModuleFromModalState();
    }
    if (!mod || !mod.settings) return;

    const boxClass = type === 'margin' ? '.tb-modal-spacing-box-outer' : '.tb-modal-spacing-box-inner';
    const box = document.querySelector(boxClass);
    if (!box) return;

    ['Top', 'Right', 'Bottom', 'Left'].forEach(side => {
        // Use :scope > to only get DIRECT children (not nested padding inputs for margin case)
        const input = box.querySelector(':scope > .tb-modal-spacing-input-' + side.toLowerCase() + ' input');
        if (input) {
            const val = parseInt(mod.settings[type + side]) || 0;
            input.value = val;
        }
    });
};

// ═══════════════════════════════════════════════════════════
// BACKGROUND SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalBackgroundSection = function(settings) {
    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🎨</span>';
    html += '<span class="tb-modal-section-title">Background</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Background Color</label>';
    html += this.renderModalColorPicker('backgroundColor', settings.backgroundColor || '', 'TB.updateModalModuleSetting(\'backgroundColor\', VALUE)');
    html += '</div>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// LAYOUT SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalLayoutSection = function(settings) {
    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">📏</span>';
    html += '<span class="tb-modal-section-title">Layout</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Alignment</label>';
    html += '<div class="tb-modal-btn-group">';
    ['left', 'center', 'right', 'justify'].forEach(align => {
        const icons = { left: '◀', center: '◆', right: '▶', justify: '≡' };
        html += '<button type="button" class="tb-modal-btn-opt' + (settings.textAlign === align ? ' active' : '') + '" onclick="TB.updateModalModuleSetting(\'textAlign\', \'' + align + '\'); TB.refreshModalControlUI(this)" title="' + align + '">' + icons[align] + '</button>';
    });
    html += '</div></div>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// BORDER SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalBorderSection = function(settings) {
    const bwTop = parseInt(settings.border_width_top) || 0;
    const bwRight = parseInt(settings.border_width_right) || 0;
    const bwBottom = parseInt(settings.border_width_bottom) || 0;
    const bwLeft = parseInt(settings.border_width_left) || 0;
    const borderStyle = settings.border_style || settings.borderStyle || 'none';
    const borderColor = settings.border_color || settings.borderColor || '#e2e8f0';
    const brTL = parseInt(settings.border_radius_tl) || parseInt(settings.borderRadius) || 0;
    const brTR = parseInt(settings.border_radius_tr) || parseInt(settings.borderRadius) || 0;
    const brBR = parseInt(settings.border_radius_br) || parseInt(settings.borderRadius) || 0;
    const brBL = parseInt(settings.border_radius_bl) || parseInt(settings.borderRadius) || 0;

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🔲</span>';
    html += '<span class="tb-modal-section-title">Border</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Border Width (visual box)
    html += '<div class="tb-modal-subsection-title">Border Width</div>';
    html += '<div class="tb-modal-border-box">';
    html += '<div class="tb-modal-border-input top"><input type="number" value="' + bwTop + '" min="0" max="50" onchange="TB.updateModalModuleSetting(\'border_width_top\', this.value)"></div>';
    html += '<div class="tb-modal-border-input right"><input type="number" value="' + bwRight + '" min="0" max="50" onchange="TB.updateModalModuleSetting(\'border_width_right\', this.value)"></div>';
    html += '<div class="tb-modal-border-input bottom"><input type="number" value="' + bwBottom + '" min="0" max="50" onchange="TB.updateModalModuleSetting(\'border_width_bottom\', this.value)"></div>';
    html += '<div class="tb-modal-border-input left"><input type="number" value="' + bwLeft + '" min="0" max="50" onchange="TB.updateModalModuleSetting(\'border_width_left\', this.value)"></div>';
    html += '<div class="tb-modal-border-center">W</div>';
    html += '</div>';

    // Border Style
    html += '<div class="tb-modal-control-row">';
    html += '<label>Style</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'border_style\', this.value)">';
    ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge'].forEach(s => {
        html += '<option value="' + s + '"' + (borderStyle === s ? ' selected' : '') + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
    });
    html += '</select></div>';

    // Border Color
    html += '<div class="tb-modal-control-row">';
    html += '<label>Color</label>';
    html += this.renderModalColorPicker('border_color', borderColor, 'TB.updateModalModuleSetting(\'border_color\', VALUE)');
    html += '</div>';

    // Border Radius (visual box)
    html += '<div class="tb-modal-subsection-title">Border Radius</div>';
    html += '<div class="tb-modal-radius-box">';
    html += '<div class="tb-modal-radius-input tl"><input type="number" value="' + brTL + '" min="0" max="500" onchange="TB.updateModalModuleSetting(\'border_radius_tl\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input tr"><input type="number" value="' + brTR + '" min="0" max="500" onchange="TB.updateModalModuleSetting(\'border_radius_tr\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input br"><input type="number" value="' + brBR + '" min="0" max="500" onchange="TB.updateModalModuleSetting(\'border_radius_br\', this.value)"></div>';
    html += '<div class="tb-modal-radius-input bl"><input type="number" value="' + brBL + '" min="0" max="500" onchange="TB.updateModalModuleSetting(\'border_radius_bl\', this.value)"></div>';
    html += '<div class="tb-modal-radius-center">R</div>';
    html += '</div>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// BOX SHADOW SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalBoxShadowSection = function(settings) {
    const enabled = settings.box_shadow_enabled || false;
    const h = parseInt(settings.box_shadow_horizontal) || 0;
    const v = parseInt(settings.box_shadow_vertical) || 4;
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
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalModuleSetting(\'box_shadow_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Horizontal
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Horizontal: <span class="tb-modal-slider-value">' + h + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + h + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'box_shadow_horizontal\', this.value)">';
        html += '</div>';

        // Vertical
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Vertical: <span class="tb-modal-slider-value">' + v + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + v + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'box_shadow_vertical\', this.value)">';
        html += '</div>';

        // Blur
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Blur: <span class="tb-modal-slider-value">' + blur + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'box_shadow_blur\', this.value)">';
        html += '</div>';

        // Spread
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Spread: <span class="tb-modal-slider-value">' + spread + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + spread + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'box_shadow_spread\', this.value)">';
        html += '</div>';

        // Color
        html += '<div class="tb-modal-control-row">';
        html += '<label>Color</label>';
        html += '<input type="color" class="tb-modal-color-only" value="' + (this.rgbaToHex ? this.rgbaToHex(color) : '#000000') + '" onchange="TB.updateModalShadowColor(this.value)">';
        html += '</div>';

        // Inset
        html += '<div class="tb-modal-control-row">';
        html += '<label>Inset Shadow</label>';
        html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (inset ? 'checked' : '') + ' onchange="TB.updateModalModuleSetting(\'box_shadow_inset\', this.checked)"><span class="tb-modal-toggle-slider"></span></label>';
        html += '</div>';
    }

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// HOVER EFFECTS SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalHoverSection = function(settings) {
    const enabled = settings.hover_enabled || false;
    const duration = settings.hover_transition_duration || '0.3';
    const easing = settings.hover_transition_easing || 'ease';

    let html = '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">👆</span>';
    html += '<span class="tb-modal-section-title">Hover Effects</span>';
    html += '<span class="tb-modal-section-toggle">▼</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    // Enable toggle
    html += '<div class="tb-modal-control-row">';
    html += '<label>Enable Hover</label>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalModuleSetting(\'hover_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        // Duration
        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Duration: <span class="tb-modal-slider-value">' + duration + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="2" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalModuleSetting(\'hover_transition_duration\', this.value)">';
        html += '</div>';

        // Easing
        html += '<div class="tb-modal-control-row">';
        html += '<label>Easing</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'hover_transition_easing\', this.value)">';
        ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'].forEach(e => {
            html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + e + '</option>';
        });
        html += '</select></div>';

        // Hover Colors
        html += '<div class="tb-modal-subsection-title">Colors on Hover</div>';
        html += '<div class="tb-modal-control-row">';
        html += '<label>Text Color</label>';
        html += this.renderModalColorPicker('text_color_hover', settings.text_color_hover || '', 'TB.updateModalModuleSetting(\'text_color_hover\', VALUE)');
        html += '</div>';
        html += '<div class="tb-modal-control-row">';
        html += '<label>Background</label>';
        html += this.renderModalColorPicker('background_color_hover', settings.background_color_hover || '', 'TB.updateModalModuleSetting(\'background_color_hover\', VALUE)');
        html += '</div>';
        html += '<div class="tb-modal-control-row">';
        html += '<label>Border Color</label>';
        html += this.renderModalColorPicker('border_color_hover', settings.border_color_hover || '', 'TB.updateModalModuleSetting(\'border_color_hover\', VALUE)');
        html += '</div>';

        // Hover Transform
        html += '<div class="tb-modal-subsection-title">Transform on Hover</div>';
        const scaleX = settings.transform_scale_x_hover || '100';
        const translateY = settings.transform_translate_y_hover || '0';
        const opacity = settings.opacity_hover || '1';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Scale: <span class="tb-modal-slider-value">' + scaleX + '%</span></label>';
        html += '<input type="range" class="tb-modal-range" min="50" max="150" value="' + scaleX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'transform_scale_x_hover\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Move Y: <span class="tb-modal-slider-value">' + translateY + 'px</span></label>';
        html += '<input type="range" class="tb-modal-range" min="-50" max="50" value="' + translateY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'transform_translate_y_hover\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Opacity: <span class="tb-modal-slider-value">' + opacity + '</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="1" step="0.1" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value" onchange="TB.updateModalModuleSetting(\'opacity_hover\', this.value)">';
        html += '</div>';
    }

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// TRANSFORM SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalTransformSection = function(settings) {
    const scaleX = settings.transform_scale_x || '100';
    const scaleY = settings.transform_scale_y || '100';
    const rotateZ = settings.transform_rotate_z || '0';
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

    // Scale
    html += '<div class="tb-modal-subsection-title">Scale</div>';
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Scale X: <span class="tb-modal-slider-value">' + scaleX + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + scaleX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'transform_scale_x\', this.value)">';
    html += '</div>';
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Scale Y: <span class="tb-modal-slider-value">' + scaleY + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + scaleY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'transform_scale_y\', this.value)">';
    html += '</div>';

    // Rotate
    html += '<div class="tb-modal-subsection-title">Rotate</div>';
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Rotate: <span class="tb-modal-slider-value">' + rotateZ + '°</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-180" max="180" value="' + rotateZ + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'°\'" onchange="TB.updateModalModuleSetting(\'transform_rotate_z\', this.value)">';
    html += '</div>';

    // Translate
    html += '<div class="tb-modal-subsection-title">Translate</div>';
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Move X: <span class="tb-modal-slider-value">' + translateX + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-200" max="200" value="' + translateX + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'transform_translate_x\', this.value)">';
    html += '</div>';
    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Move Y: <span class="tb-modal-slider-value">' + translateY + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="-200" max="200" value="' + translateY + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'transform_translate_y\', this.value)">';
    html += '</div>';

    // Origin
    html += '<div class="tb-modal-subsection-title">Transform Origin</div>';
    html += '<div class="tb-modal-transform-origin-grid">';
    const points = [['left','top'],['center','top'],['right','top'],['left','center'],['center','center'],['right','center'],['left','bottom'],['center','bottom'],['right','bottom']];
    points.forEach(p => {
        const val = p[0] + ' ' + p[1];
        html += '<div class="tb-modal-origin-point' + (origin === val ? ' active' : '') + '" onclick="TB.updateModalModuleSetting(\'transform_origin\', \'' + val + '\'); TB.refreshOriginUI(this)"></div>';
    });
    html += '</div>';

    // Reset
    html += '<button type="button" class="tb-modal-reset-btn" onclick="TB.resetModalTransforms()">↺ Reset</button>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// FILTERS SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalFiltersSection = function(settings) {
    console.log('🔍 renderModalFiltersSection called, settings:', settings);
    console.log('  - filter_blur:', settings.filter_blur);
    console.log('  - filter_brightness:', settings.filter_brightness);
    
    const blur = settings.filter_blur || '0';
    const brightness = settings.filter_brightness || '100';
    const contrast = settings.filter_contrast || '100';
    const saturation = settings.filter_saturation || '100';
    const grayscale = settings.filter_grayscale || '0';
    const opacity = settings.filter_opacity || '100';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">🎨</span>';
    html += '<span class="tb-modal-section-title">Filters</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Blur: <span class="tb-modal-slider-value">' + blur + 'px</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="20" value="' + blur + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'px\'" onchange="TB.updateModalModuleSetting(\'filter_blur\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Brightness: <span class="tb-modal-slider-value">' + brightness + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + brightness + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'filter_brightness\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Contrast: <span class="tb-modal-slider-value">' + contrast + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + contrast + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'filter_contrast\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Saturation: <span class="tb-modal-slider-value">' + saturation + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="200" value="' + saturation + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'filter_saturation\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Grayscale: <span class="tb-modal-slider-value">' + grayscale + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + grayscale + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'filter_grayscale\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-control-row slider">';
    html += '<label>Opacity: <span class="tb-modal-slider-value">' + opacity + '%</span></label>';
    html += '<input type="range" class="tb-modal-range" min="0" max="100" value="' + opacity + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'%\'" onchange="TB.updateModalModuleSetting(\'filter_opacity\', this.value)">';
    html += '</div>';

    html += '<button type="button" class="tb-modal-reset-btn" onclick="TB.resetModalFilters()">↺ Reset</button>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// POSITION SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalPositionSection = function(settings) {
    const posType = settings.position || 'static';
    const zIndex = settings.z_index || 'auto';
    const showOffsets = ['relative', 'absolute', 'fixed', 'sticky'].includes(posType);

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">📍</span>';
    html += '<span class="tb-modal-section-title">Position</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Position Type</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'position\', this.value); TB.renderModalDesignSettings()">';
    ['static', 'relative', 'absolute', 'fixed', 'sticky'].forEach(p => {
        html += '<option value="' + p + '"' + (posType === p ? ' selected' : '') + '>' + p.charAt(0).toUpperCase() + p.slice(1) + '</option>';
    });
    html += '</select></div>';

    if (showOffsets) {
        html += '<div class="tb-modal-position-offsets">';
        ['top', 'right', 'bottom', 'left'].forEach(side => {
            const val = settings['position_' + side] || '';
            html += '<div class="tb-modal-control-row">';
            html += '<label>' + side.charAt(0).toUpperCase() + side.slice(1) + '</label>';
            html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(val) + '" placeholder="auto" onchange="TB.updateModalModuleSetting(\'position_' + side + '\', this.value)">';
            html += '</div>';
        });
        html += '</div>';
    }

    html += '<div class="tb-modal-control-row">';
    html += '<label>Z-Index</label>';
    html += '<input type="text" class="tb-modal-input" value="' + zIndex + '" placeholder="auto" onchange="TB.updateModalModuleSetting(\'z_index\', this.value)">';
    html += '</div>';

    html += '<div class="tb-modal-zindex-presets">';
    ['-1', '0', '1', '10', '100', 'auto'].forEach(z => {
        html += '<button type="button" class="tb-modal-preset-btn' + (zIndex === z ? ' active' : '') + '" onclick="TB.updateModalModuleSetting(\'z_index\', \'' + z + '\'); TB.refreshModalControlUI(this)">' + z + '</button>';
    });
    html += '</div>';

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// ANIMATION SECTION
// ═══════════════════════════════════════════════════════════
TB.renderModalAnimationSection = function(settings) {
    const enabled = settings.animation_enabled || false;
    const animType = settings.animation_type || 'fadeIn';
    const duration = settings.animation_duration || '0.6';
    const delay = settings.animation_delay || '0';
    const easing = settings.animation_easing || 'ease-out';

    let html = '<div class="tb-modal-design-section-card collapsed">';
    html += '<div class="tb-modal-section-header" onclick="TB.toggleModalSection(this)">';
    html += '<span class="tb-modal-section-icon">✨</span>';
    html += '<span class="tb-modal-section-title">Animation</span>';
    html += '<span class="tb-modal-section-toggle">▶</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Enable Animation</label>';
    html += '<label class="tb-modal-toggle-switch"><input type="checkbox" ' + (enabled ? 'checked' : '') + ' onchange="TB.updateModalModuleSetting(\'animation_enabled\', this.checked); TB.renderModalDesignSettings()"><span class="tb-modal-toggle-slider"></span></label>';
    html += '</div>';

    if (enabled) {
        html += '<div class="tb-modal-control-row">';
        html += '<label>Animation Type</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'animation_type\', this.value)">';
        ['fadeIn', 'fadeInUp', 'fadeInDown', 'fadeInLeft', 'fadeInRight', 'zoomIn', 'slideInUp', 'slideInDown', 'bounceIn'].forEach(t => {
            html += '<option value="' + t + '"' + (animType === t ? ' selected' : '') + '>' + t + '</option>';
        });
        html += '</select></div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Duration: <span class="tb-modal-slider-value">' + duration + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0.1" max="3" step="0.1" value="' + duration + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalModuleSetting(\'animation_duration\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row slider">';
        html += '<label>Delay: <span class="tb-modal-slider-value">' + delay + 's</span></label>';
        html += '<input type="range" class="tb-modal-range" min="0" max="2" step="0.1" value="' + delay + '" oninput="this.previousElementSibling.querySelector(\'.tb-modal-slider-value\').textContent=this.value+\'s\'" onchange="TB.updateModalModuleSetting(\'animation_delay\', this.value)">';
        html += '</div>';

        html += '<div class="tb-modal-control-row">';
        html += '<label>Easing</label>';
        html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'animation_easing\', this.value)">';
        ['ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear'].forEach(e => {
            html += '<option value="' + e + '"' + (easing === e ? ' selected' : '') + '>' + e + '</option>';
        });
        html += '</select></div>';

        html += '<button type="button" class="tb-modal-preview-btn" onclick="TB.previewModalAnimation()">▶ Preview Animation</button>';
    }

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// HOVER STATE SETTINGS (shown when state = hover)
// ═══════════════════════════════════════════════════════════
TB.renderModalHoverStateSettings = function(settings) {
    let html = '<div class="tb-modal-design-full">';
    html += '<div class="tb-modal-info-box">';
    html += '<span class="tb-modal-info-icon">💡</span>';
    html += '<span>Configure hover-specific styles. These apply when users hover over this module.</span>';
    html += '</div>';

    html += '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header">';
    html += '<span class="tb-modal-section-icon">🎨</span>';
    html += '<span class="tb-modal-section-title">Hover Colors</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body" style="display:block">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Background</label>';
    html += this.renderModalColorPicker('hoverBackgroundColor', settings.hoverBackgroundColor || '', 'TB.updateModalModuleSetting(\'hoverBackgroundColor\', VALUE)');
    html += '</div>';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Border Color</label>';
    html += this.renderModalColorPicker('hoverBorderColor', settings.hoverBorderColor || '', 'TB.updateModalModuleSetting(\'hoverBorderColor\', VALUE)');
    html += '</div>';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Text Color</label>';
    html += this.renderModalColorPicker('hoverTextColor', settings.hoverTextColor || '', 'TB.updateModalModuleSetting(\'hoverTextColor\', VALUE)');
    html += '</div>';

    html += '</div></div>';

    html += '<div class="tb-modal-design-section-card">';
    html += '<div class="tb-modal-section-header">';
    html += '<span class="tb-modal-section-icon">🔄</span>';
    html += '<span class="tb-modal-section-title">Hover Transform</span>';
    html += '</div>';
    html += '<div class="tb-modal-section-body" style="display:block">';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Transform Effect</label>';
    html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'hoverTransform\', this.value)">';
    html += '<option value="">None</option>';
    html += '<option value="translateY(-2px)"' + (settings.hoverTransform === 'translateY(-2px)' ? ' selected' : '') + '>Lift Up</option>';
    html += '<option value="translateY(-5px)"' + (settings.hoverTransform === 'translateY(-5px)' ? ' selected' : '') + '>Lift Up (More)</option>';
    html += '<option value="scale(1.02)"' + (settings.hoverTransform === 'scale(1.02)' ? ' selected' : '') + '>Scale Up (2%)</option>';
    html += '<option value="scale(1.05)"' + (settings.hoverTransform === 'scale(1.05)' ? ' selected' : '') + '>Scale Up (5%)</option>';
    html += '<option value="scale(0.98)"' + (settings.hoverTransform === 'scale(0.98)' ? ' selected' : '') + '>Scale Down</option>';
    html += '</select></div>';

    html += '<div class="tb-modal-control-row">';
    html += '<label>Hover Box Shadow</label>';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(settings.hoverBoxShadow || '') + '" placeholder="0 4px 8px rgba(0,0,0,0.15)" onchange="TB.updateModalModuleSetting(\'hoverBoxShadow\', this.value)">';
    html += '</div>';

    html += '</div></div>';
    html += '</div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS FOR MODAL DESIGN
// ═══════════════════════════════════════════════════════════
TB.toggleModalSection = function(header) {
    const section = header.closest('.tb-modal-design-section-card');
    const body = section.querySelector('.tb-modal-section-body');
    const toggle = header.querySelector('.tb-modal-section-toggle');

    if (section.classList.contains('collapsed')) {
        section.classList.remove('collapsed');
        body.style.display = 'block';
        toggle.textContent = '▼';
    } else {
        section.classList.add('collapsed');
        body.style.display = 'none';
        toggle.textContent = '▶';
    }
};

TB.updateModalTypography = function(prop, value) {
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.settings) mod.settings = {};
    if (!mod.settings.typography_default) mod.settings.typography_default = {};
    mod.settings.typography_default[prop] = value;
    this.updateModalPreview();
};

TB.updateModalTypographyWithUnit = function(prop, value, unit) {
    const fullValue = value ? value + (unit || 'px') : '';
    this.updateModalTypography(prop, fullValue);
};

TB.updateModalShadowColor = function(hexColor) {
    const rgba = this.hexToRgba ? this.hexToRgba(hexColor, 0.1) : hexColor;
    this.updateModalModuleSetting('box_shadow_color', rgba);
};

TB.resetModalTransforms = function() {
    const props = ['transform_scale_x', 'transform_scale_y', 'transform_rotate_z', 'transform_translate_x', 'transform_translate_y', 'transform_skew_x', 'transform_skew_y'];
    props.forEach(p => this.updateModalModuleSetting(p, null));
    this.updateModalModuleSetting('transform_origin', 'center center');
    this.renderModalDesignSettings();
};

TB.resetModalFilters = function() {
    const props = ['filter_blur', 'filter_brightness', 'filter_contrast', 'filter_saturation', 'filter_grayscale', 'filter_opacity'];
    props.forEach(p => this.updateModalModuleSetting(p, null));
    this.renderModalDesignSettings();
};

TB.previewModalAnimation = function() {
    const preview = document.getElementById('modal-preview-mod');
    if (!preview) return;
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod?.settings?.animation_enabled) return;
    const s = mod.settings;
    preview.style.animation = 'none';
    preview.offsetHeight; // trigger reflow
    preview.style.animation = 'tb-' + (s.animation_type || 'fadeIn') + ' ' + (s.animation_duration || '0.6') + 's ' + (s.animation_easing || 'ease-out') + ' ' + (s.animation_delay || '0') + 's both';
    setTimeout(() => { preview.style.animation = ''; }, 3000);
};

TB.refreshModalControlUI = function(btn) {
    const group = btn.closest('.tb-modal-btn-group, .tb-modal-zindex-presets');
    if (group) {
        group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
};

TB.refreshOriginUI = function(point) {
    const grid = point.closest('.tb-modal-transform-origin-grid');
    if (grid) {
        grid.querySelectorAll('.tb-modal-origin-point').forEach(p => p.classList.remove('active'));
        point.classList.add('active');
    }
};

// ═══════════════════════════════════════════════════════════
// RENDER ELEMENT SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderModalElementSettings = function(mod, elementKey, state) {
    console.log('🔧 renderModalElementSettings called');
    console.log('  - mod.type:', mod.type);
    console.log('  - elementKey:', elementKey);
    console.log('  - state:', state);
    
    const schema = this.elementSchemas[mod.type];
    if (!schema || !schema[elementKey]) {
        console.log('  ❌ No schema found for', mod.type, elementKey);
        return '<div class="tb-modal-no-settings">No element settings available</div>';
    }

    const elementDef = schema[elementKey];
    console.log('  - elementDef.properties:', elementDef.properties);
    
    const elements = mod.design?.elements || {};
    const elementStyles = elements[elementKey] || {};
    const stateStyles = elementStyles[state] || {};

    let html = '<div class="tb-modal-settings-grid">';

    elementDef.properties.forEach(prop => {
        html += this.renderModalPropertyInput(elementKey, state, prop, stateStyles[prop] || '');
    });

    html += '</div>';
    return html;
};

TB.renderModalPropertyInput = function(elementKey, state, prop, value) {
    const label = this.formatPropertyLabel(prop);
    const callback = 'TB.updateModalElementStyle(\'' + elementKey + '\', \'' + state + '\', \'' + prop + '\', VALUE)';

    let html = '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">' + label + '</div>';
    html += '<div class="tb-modal-setting-control">';

    // Handle different property types
    if (prop === 'background' || prop === 'color' || prop === 'border_color' || prop === 'stroke' || prop === 'fill') {
        html += this.renderModalColorPicker(prop, value, callback);
    } else if (prop === 'font_size' || prop === 'padding' || prop === 'margin_bottom' || prop === 'border_radius' || prop === 'line_height' || prop === 'gap' || prop === 'width' || prop === 'height' || prop === 'min_height' || prop === 'max_width' || prop === 'max_height' || prop === 'stroke_width') {
        html += this.renderModalSizeInput(prop, value, callback);
    } else if (prop === 'font_weight') {
        html += '<select class="tb-modal-select" onchange="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<option value="">Default</option>';
        html += '<option value="400"' + (value === '400' ? ' selected' : '') + '>400 - Normal</option>';
        html += '<option value="500"' + (value === '500' ? ' selected' : '') + '>500 - Medium</option>';
        html += '<option value="600"' + (value === '600' ? ' selected' : '') + '>600 - Semi Bold</option>';
        html += '<option value="700"' + (value === '700' ? ' selected' : '') + '>700 - Bold</option>';
        html += '</select>';
    } else if (prop === 'text_transform') {
        html += '<select class="tb-modal-select" onchange="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<option value="">None</option>';
        html += '<option value="uppercase"' + (value === 'uppercase' ? ' selected' : '') + '>Uppercase</option>';
        html += '<option value="lowercase"' + (value === 'lowercase' ? ' selected' : '') + '>Lowercase</option>';
        html += '<option value="capitalize"' + (value === 'capitalize' ? ' selected' : '') + '>Capitalize</option>';
        html += '</select>';
    } else if (prop === 'text_decoration') {
        html += '<select class="tb-modal-select" onchange="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<option value="">None</option>';
        html += '<option value="underline"' + (value === 'underline' ? ' selected' : '') + '>Underline</option>';
        html += '<option value="line-through"' + (value === 'line-through' ? ' selected' : '') + '>Line Through</option>';
        html += '</select>';
    } else if (prop === 'font_style') {
        html += '<select class="tb-modal-select" onchange="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<option value="">Normal</option>';
        html += '<option value="italic"' + (value === 'italic' ? ' selected' : '') + '>Italic</option>';
        html += '</select>';
    } else if (prop === 'opacity') {
        html += '<input type="range" class="tb-modal-range" min="0" max="1" step="0.1" value="' + (value || '1') + '" oninput="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<span class="tb-modal-range-value">' + (value || '1') + '</span>';
    } else if (prop === 'transform') {
        html += '<select class="tb-modal-select" onchange="' + callback.replace('VALUE', 'this.value') + '">';
        html += '<option value="">None</option>';
        html += '<option value="rotate(180deg)"' + (value === 'rotate(180deg)' ? ' selected' : '') + '>Rotate 180°</option>';
        html += '<option value="rotate(90deg)"' + (value === 'rotate(90deg)' ? ' selected' : '') + '>Rotate 90°</option>';
        html += '<option value="scale(1.1)"' + (value === 'scale(1.1)' ? ' selected' : '') + '>Scale Up</option>';
        html += '</select>';
    } else {
        // Generic text input
        html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(value) + '" onchange="' + callback.replace('VALUE', 'this.value') + '">';
    }

    html += '</div></div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// MODAL INPUT HELPERS
// ═══════════════════════════════════════════════════════════
TB.renderModalColorPicker = function(prop, value, callback) {
    const id = 'modal-color-' + prop + '-' + Date.now();
    return '<div class="tb-modal-color-picker">' +
        '<input type="color" id="' + id + '" value="' + (value || '#000000') + '" onchange="' + callback.replace('VALUE', 'this.value') + '">' +
        '<input type="text" class="tb-modal-input tb-modal-color-text" value="' + this.escapeHtml(value) + '" placeholder="#000000" onchange="document.getElementById(\'' + id + '\').value = this.value; ' + callback.replace('VALUE', 'this.value') + '">' +
        '</div>';
};

TB.renderModalSizeInput = function(prop, value, callback) {
    const numValue = value ? parseFloat(value) : '';
    const unit = value ? (value.toString().match(/[a-z%]+$/i)?.[0] || 'px') : 'px';

    return '<div class="tb-modal-size-input">' +
        '<input type="number" class="tb-modal-input" value="' + numValue + '" placeholder="' + this.getPropertyPlaceholder(prop) + '" onchange="' + callback.replace('VALUE', 'this.value + this.nextElementSibling.value') + '">' +
        '<select class="tb-modal-select tb-modal-unit-select" onchange="' + callback.replace('VALUE', 'this.previousElementSibling.value + this.value') + '">' +
        '<option value="px"' + (unit === 'px' ? ' selected' : '') + '>px</option>' +
        '<option value="em"' + (unit === 'em' ? ' selected' : '') + '>em</option>' +
        '<option value="rem"' + (unit === 'rem' ? ' selected' : '') + '>rem</option>' +
        '<option value="%"' + (unit === '%' ? ' selected' : '') + '>%</option>' +
        '</select></div>';
};

TB.renderModalSpacingInputs = function(type, settings, callback) {
    const top = settings[type + 'Top'] || '';
    const right = settings[type + 'Right'] || '';
    const bottom = settings[type + 'Bottom'] || '';
    const left = settings[type + 'Left'] || '';

    return '<div class="tb-modal-spacing-grid">' +
        '<div class="tb-modal-spacing-row">' +
        '<div></div>' +
        '<input type="text" class="tb-modal-input tb-modal-spacing-input" value="' + this.escapeHtml(top) + '" placeholder="Top" onchange="' + callback + '(\'' + type + 'Top\', this.value)">' +
        '<div></div>' +
        '</div>' +
        '<div class="tb-modal-spacing-row">' +
        '<input type="text" class="tb-modal-input tb-modal-spacing-input" value="' + this.escapeHtml(left) + '" placeholder="Left" onchange="' + callback + '(\'' + type + 'Left\', this.value)">' +
        '<div class="tb-modal-spacing-center">' + type.charAt(0).toUpperCase() + '</div>' +
        '<input type="text" class="tb-modal-input tb-modal-spacing-input" value="' + this.escapeHtml(right) + '" placeholder="Right" onchange="' + callback + '(\'' + type + 'Right\', this.value)">' +
        '</div>' +
        '<div class="tb-modal-spacing-row">' +
        '<div></div>' +
        '<input type="text" class="tb-modal-input tb-modal-spacing-input" value="' + this.escapeHtml(bottom) + '" placeholder="Bottom" onchange="' + callback + '(\'' + type + 'Bottom\', this.value)">' +
        '<div></div>' +
        '</div>' +
        '</div>';
};

// ═══════════════════════════════════════════════════════════
// RENDER ADVANCED SETTINGS
// ═══════════════════════════════════════════════════════════
TB.renderModalAdvancedSettings = function(mod) {
    const advanced = mod.advanced || {};
    // Merge settings and design - design takes precedence
    const settings = { ...(mod.settings || {}), ...(mod.design || {}) };

    let html = '<div class="tb-modal-settings-grid">';

    // CSS ID
    html += '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">CSS ID</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(advanced.css_id || '') + '" placeholder="my-element" onchange="TB.updateModalAdvanced(\'css_id\', this.value)">';
    html += '</div></div>';

    // CSS Class
    html += '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">CSS Class</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<input type="text" class="tb-modal-input" value="' + this.escapeHtml(advanced.css_class || '') + '" placeholder="my-class another-class" onchange="TB.updateModalAdvanced(\'css_class\', this.value)">';
    html += '</div></div>';

    // Custom CSS
    html += '<div class="tb-modal-setting-group full-width">';
    html += '<div class="tb-modal-setting-label">Custom CSS</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<textarea class="tb-modal-textarea" rows="6" placeholder=".my-class { color: red; }" onchange="TB.updateModalAdvanced(\'custom_css\', this.value)">' + this.escapeHtml(advanced.custom_css || '') + '</textarea>';
    html += '</div></div>';

    // Z-Index
    html += '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">Z-Index</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<input type="number" class="tb-modal-input" value="' + (settings.z_index || '') + '" placeholder="auto" onchange="TB.updateModalModuleSetting(\'z_index\', this.value)">';
    html += '</div></div>';

    // Visibility
    html += '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">Visibility</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'visibility\', this.value)">';
    html += '<option value="">Visible</option>';
    html += '<option value="hidden"' + (settings.visibility === 'hidden' ? ' selected' : '') + '>Hidden</option>';
    html += '</select></div></div>';

    // Animation
    html += '<div class="tb-modal-setting-group">';
    html += '<div class="tb-modal-setting-label">Animation</div>';
    html += '<div class="tb-modal-setting-control">';
    html += '<select class="tb-modal-select" onchange="TB.updateModalModuleSetting(\'animation\', this.value)">';
    html += '<option value="">None</option>';
    html += '<option value="fadeIn"' + (settings.animation === 'fadeIn' ? ' selected' : '') + '>Fade In</option>';
    html += '<option value="slideUp"' + (settings.animation === 'slideUp' ? ' selected' : '') + '>Slide Up</option>';
    html += '<option value="slideDown"' + (settings.animation === 'slideDown' ? ' selected' : '') + '>Slide Down</option>';
    html += '<option value="slideLeft"' + (settings.animation === 'slideLeft' ? ' selected' : '') + '>Slide Left</option>';
    html += '<option value="slideRight"' + (settings.animation === 'slideRight' ? ' selected' : '') + '>Slide Right</option>';
    html += '<option value="zoomIn"' + (settings.animation === 'zoomIn' ? ' selected' : '') + '>Zoom In</option>';
    html += '</select></div></div>';

    html += '</div>';
    return html;
};

// ═══════════════════════════════════════════════════════════
// UPDATE FUNCTIONS
// ═══════════════════════════════════════════════════════════
// FIX: Design properties now save to mod.design (unified with sidebar)
// Content properties still save to mod.settings
// ═══════════════════════════════════════════════════════════

// List of design property prefixes - these go to mod.design
const TB_DESIGN_PREFIXES = [
    'transform_', 'filter_', 'box_shadow_', 'border_', 'hover_',
    'animation_', 'position_', 'opacity_hover', 'text_color_hover',
    'background_color_hover', 'border_color_hover', 'z_index',
    'padding_', 'margin_', 'background', 'textAlign', 'visibility'
];

TB.updateModalModuleSetting = function(key, value) {
    console.log('📝 updateModalModuleSetting:', key, '=', value);
    
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    // ═══════════════════════════════════════════════════════════
    // Determine if this is a design property or content property
    // ═══════════════════════════════════════════════════════════
    const isDesignProp = TB_DESIGN_PREFIXES.some(prefix =>
        key.startsWith(prefix) || key === prefix
    );

    if (isDesignProp) {
        // DESIGN PROPERTIES: Save to mod.design (same as sidebar)
        if (!mod.design) mod.design = {};
        if (value === '' || value === null || value === undefined) {
            delete mod.design[key];
        } else {
            mod.design[key] = value;
        }
        console.log('  ✅ Saved to mod.design, mod.design:', JSON.stringify(mod.design));
    } else {
        // CONTENT PROPERTIES: Save to mod.settings
        if (!mod.settings) mod.settings = {};
        if (value === '' || value === null || value === undefined) {
            delete mod.settings[key];
        } else {
            mod.settings[key] = value;
        }
        console.log('  ✅ Saved to mod.settings, mod.settings:', JSON.stringify(mod.settings));
    }

    this.updateModalPreview();
    this.saveToHistory();
    this.renderCanvas();
};

TB.updateModalElementStyle = function(elementKey, state, prop, value) {
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    // Ensure design.elements structure exists
    if (!mod.design) mod.design = {};
    if (!mod.design.elements) mod.design.elements = {};
    if (!mod.design.elements[elementKey]) mod.design.elements[elementKey] = {};
    if (!mod.design.elements[elementKey][state]) mod.design.elements[elementKey][state] = {};

    // Set or remove value
    if (value === '' || value === null || value === undefined) {
        delete mod.design.elements[elementKey][state][prop];
        // Clean up empty objects
        if (Object.keys(mod.design.elements[elementKey][state]).length === 0) {
            delete mod.design.elements[elementKey][state];
        }
        if (Object.keys(mod.design.elements[elementKey]).length === 0) {
            delete mod.design.elements[elementKey];
        }
        if (Object.keys(mod.design.elements).length === 0) {
            delete mod.design.elements;
        }
    } else {
        mod.design.elements[elementKey][state][prop] = value;
    }

    this.updateModalPreview();
};

TB.updateModalAdvanced = function(key, value) {
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    if (!mod.advanced) mod.advanced = {};

    if (value === '' || value === null || value === undefined) {
        delete mod.advanced[key];
    } else {
        mod.advanced[key] = value;
    }

    this.updateModalPreview();
};

// ═══════════════════════════════════════════════════════════
// GET CLEAN MODULE PREVIEW CONTENT (without wrapper)
// ═══════════════════════════════════════════════════════════
TB.getModulePreviewContent = function(mod) {
    const type = mod.type || 'text';
    const content = mod.content || {};
    const icon = this.getModuleIcon ? this.getModuleIcon(type) : '📦';

    // Simple preview for common module types
    switch (type) {
        case 'hero':
            const heroTitle = content.title || 'Welcome to Our Site';
            const heroSubtitle = content.subtitle || 'We build amazing digital experiences';
            const heroBgColor = content.background_color || '#1e1e2e';
            const heroBgImage = content.bg_image || content.background_image || '';
            const heroOverlayOpacity = content.overlay_opacity !== undefined ? content.overlay_opacity : 0.5;
            const heroTextColor = content.text_color || '#ffffff';
            const heroAlign = content.alignment || 'center';
            const heroBtnText = content.button_text || 'Get Started';
            let heroBgStyle = heroBgImage
                ? 'background:linear-gradient(rgba(0,0,0,' + heroOverlayOpacity + '),rgba(0,0,0,' + heroOverlayOpacity + ')),url(' + this.escapeHtml(heroBgImage) + ') center/cover no-repeat'
                : 'background:' + heroBgColor;
            return '<div class="tb-hero-preview" style="' + heroBgStyle + ';min-height:180px;padding:30px 20px;display:flex;flex-direction:column;justify-content:center;align-items:' + heroAlign + ';text-align:' + heroAlign + ';border-radius:8px">' +
                '<h1 class="tb-hero-title" style="margin:0 0 10px 0;font-size:24px;font-weight:700;color:' + heroTextColor + '">' + this.escapeHtml(heroTitle) + '</h1>' +
                '<p class="tb-hero-subtitle" style="margin:0 0 16px 0;font-size:14px;color:' + heroTextColor + ';opacity:0.9">' + this.escapeHtml(heroSubtitle) + '</p>' +
                '<button class="tb-hero-button" style="padding:10px 20px;background:#0073e6;color:#fff;border:none;border-radius:4px;font-size:14px">' + this.escapeHtml(heroBtnText) + '</button></div>';

        case 'text':
            return content.text
                ? '<p style="color:#333;margin:0">' + this.escapeHtml(content.text.substring(0, 150)) + (content.text.length > 150 ? '...' : '') + '</p>'
                : '<p style="color:#94a3b8;font-style:italic">Click to add text...</p>';

        case 'heading':
            const tag = content.tag || 'h2';
            return '<' + tag + ' style="margin:0;color:#333">' + this.escapeHtml(content.text || 'Heading') + '</' + tag + '>';

        case 'image':
            if (content.src) {
                let imgHtml = '<div class="tb-image" style="text-align:' + (content.alignment || 'center') + '">';
                imgHtml += '<div class="tb-image-container" style="display:inline-block;position:relative">';
                imgHtml += '<img class="tb-image-img" src="' + this.escapeHtml(content.src) + '" alt="' + this.escapeHtml(content.alt || '') + '" style="max-width:100%;height:auto;display:block">';
                if (content.show_overlay) {
                    imgHtml += '<div class="tb-image-overlay" style="position:absolute;inset:0;pointer-events:none"></div>';
                }
                imgHtml += '</div>';
                if (content.caption) {
                    imgHtml += '<p class="tb-image-caption" style="margin-top:8px">' + this.escapeHtml(content.caption) + '</p>';
                }
                imgHtml += '</div>';
                return imgHtml;
            }
            return '<div style="background:#e2e8f0;height:80px;display:flex;align-items:center;justify-content:center;border-radius:4px;color:#64748b">🖼 Click to add image</div>';

        case 'button':
            const btnStyle = content.style || 'primary';
            const btnBg = btnStyle === 'secondary' ? '#64748b' : (btnStyle === 'outline' ? 'transparent' : '#6366f1');
            const btnBorder = btnStyle === 'outline' ? '2px solid #6366f1' : 'none';
            const btnColor = btnStyle === 'outline' ? '#6366f1' : '#fff';
            return '<button style="background:' + btnBg + ';color:' + btnColor + ';border:' + btnBorder + ';padding:10px 20px;border-radius:6px;font-weight:500">' + this.escapeHtml(content.text || 'Button') + '</button>';

        case 'cta':
            const ctaTitle = content.title || 'Ready to start?';
            const ctaText = content.subtitle || content.text || 'Get started today';
            const ctaBtnText = content.button_text || 'Get Started';
            const ctaBg = content.background_color || '#6366f1';
            return '<div class="tb-cta-preview" style="background:' + ctaBg + ';padding:24px;border-radius:12px;text-align:center">' +
                '<h3 class="tb-cta-title" style="margin:0 0 8px;font-size:20px;color:#fff">' + this.escapeHtml(ctaTitle) + '</h3>' +
                '<p class="tb-cta-subtitle" style="margin:0 0 16px;color:rgba(255,255,255,0.9)">' + this.escapeHtml(ctaText) + '</p>' +
                '<button class="tb-cta-button" style="background:#fff;color:' + ctaBg + ';border:none;padding:10px 20px;border-radius:6px;font-weight:600">' + this.escapeHtml(ctaBtnText) + '</button></div>';

        case 'testimonial':
            const testQuote = content.quote || content.text || 'Great service!';
            const testAuthor = content.author || 'John Doe';
            const testRole = content.role || 'CEO';
            const testImage = content.avatar || content.image || '';
            let testHtml = '<div class="tb-testimonial-preview" style="background:#f8fafc;padding:16px;border-radius:8px;text-align:center">';
            if (testImage) {
                testHtml += '<img class="tb-testimonial-image" src="' + this.escapeHtml(testImage) + '" style="width:50px;height:50px;border-radius:50%;object-fit:cover;margin-bottom:10px">';
            } else {
                testHtml += '<div style="width:50px;height:50px;background:#e2e8f0;border-radius:50%;margin:0 auto 10px;display:flex;align-items:center;justify-content:center">👤</div>';
            }
            testHtml += '<p class="tb-testimonial-quote" style="color:#475569;font-style:italic;margin:0 0 8px">"' + this.escapeHtml(testQuote.substring(0, 60)) + (testQuote.length > 60 ? '...' : '') + '"</p>';
            testHtml += '<div class="tb-testimonial-author" style="font-weight:600;color:#333">' + this.escapeHtml(testAuthor) + '</div>';
            testHtml += '<div class="tb-testimonial-role" style="font-size:12px;color:#64748b">' + this.escapeHtml(testRole) + '</div></div>';
            return testHtml;

        default:
            // For other module types, use renderModulePreview which returns just the preview content
            if (typeof this.renderModulePreview === 'function') {
                return this.renderModulePreview(mod);
            }
            return '<div style="color:#94a3b8;text-align:center;padding:20px">' + icon + ' ' + type + ' module</div>';
    }
};

// ═══════════════════════════════════════════════════════════
// LIVE PREVIEW
// ═══════════════════════════════════════════════════════════
TB.updateModalPreview = function() {
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;

    const previewContainer = document.getElementById('tb-modal-preview');
    if (!previewContainer) return;

    // Generate preview HTML - use getModulePreviewContent or renderModulePreview
    let preview = '';
    if (typeof this.getModulePreviewContent === 'function') {
        preview = this.getModulePreviewContent(mod);
    } else if (typeof this.renderModulePreview === 'function') {
        // renderModulePreview now returns just the preview content (no wrapper)
        preview = this.renderModulePreview(mod);
    } else {
        preview = '<div style="color:#94a3b8;text-align:center;padding:20px">' + (this.getModuleIcon ? this.getModuleIcon(mod.type) : '📦') + ' ' + mod.type + '</div>';
    }

    // Generate element CSS
    let css = '';
    if (mod.design?.elements) {
        css = this.generateModalPreviewCSS(mod);
    }

    // Add wrapper styles - merge settings AND design (design takes precedence)
    const settings = { ...(mod.settings || {}), ...(mod.design || {}) };
    let wrapperStyles = '';

    // Background
    if (settings.background_color) wrapperStyles += 'background-color:' + settings.background_color + ';';
    if (settings.backgroundColor) wrapperStyles += 'background-color:' + settings.backgroundColor + ';';

    // Padding
    const pT = settings.padding_top || settings.paddingTop;
    const pR = settings.padding_right || settings.paddingRight;
    const pB = settings.padding_bottom || settings.paddingBottom;
    const pL = settings.padding_left || settings.paddingLeft;
    if (pT || pR || pB || pL) {
        wrapperStyles += 'padding:' + (pT || '0') + ' ' + (pR || '0') + ' ' + (pB || '0') + ' ' + (pL || '0') + ';';
    }

    // Margin
    const mT = settings.margin_top || settings.marginTop;
    const mR = settings.margin_right || settings.marginRight;
    const mB = settings.margin_bottom || settings.marginBottom;
    const mL = settings.margin_left || settings.marginLeft;
    if (mT || mR || mB || mL) {
        wrapperStyles += 'margin:' + (mT || '0') + ' ' + (mR || '0') + ' ' + (mB || '0') + ' ' + (mL || '0') + ';';
    }

    // Border Width
    const bwT = settings.border_width_top;
    const bwR = settings.border_width_right;
    const bwB = settings.border_width_bottom;
    const bwL = settings.border_width_left;
    if (bwT || bwR || bwB || bwL) {
        wrapperStyles += 'border-width:' + (bwT || '0') + ' ' + (bwR || '0') + ' ' + (bwB || '0') + ' ' + (bwL || '0') + ';';
        wrapperStyles += 'border-style:' + (settings.border_style || 'solid') + ';';
    } else if (settings.border_width || settings.borderWidth) {
        wrapperStyles += 'border-width:' + (settings.border_width || settings.borderWidth) + ';';
        wrapperStyles += 'border-style:' + (settings.border_style || settings.borderStyle || 'solid') + ';';
    }
    if (settings.border_color || settings.borderColor) {
        wrapperStyles += 'border-color:' + (settings.border_color || settings.borderColor) + ';';
    }

    // Border Radius
    const brTL = settings.border_radius_tl;
    const brTR = settings.border_radius_tr;
    const brBR = settings.border_radius_br;
    const brBL = settings.border_radius_bl;
    if (brTL || brTR || brBR || brBL) {
        wrapperStyles += 'border-radius:' + (brTL || '0') + 'px ' + (brTR || '0') + 'px ' + (brBR || '0') + 'px ' + (brBL || '0') + 'px;';
    } else if (settings.borderRadius) {
        wrapperStyles += 'border-radius:' + settings.borderRadius + ';';
    }

    // Box Shadow
    if (settings.box_shadow_enabled) {
        const bsH = parseInt(settings.box_shadow_h) || 0;
        const bsV = parseInt(settings.box_shadow_v) || 4;
        const bsBlur = parseInt(settings.box_shadow_blur) || 10;
        const bsSpread = parseInt(settings.box_shadow_spread) || 0;
        const bsColor = settings.box_shadow_color || 'rgba(0,0,0,0.1)';
        const bsInset = settings.box_shadow_inset ? 'inset ' : '';
        wrapperStyles += 'box-shadow:' + bsInset + bsH + 'px ' + bsV + 'px ' + bsBlur + 'px ' + bsSpread + 'px ' + bsColor + ';';
    } else if (settings.boxShadow) {
        wrapperStyles += 'box-shadow:' + settings.boxShadow + ';';
    }

    // =================================================================
    // FILTERS - Critical for modal preview to show filter effects
    // =================================================================
    const filterParts = [];
    if (settings.filter_blur && parseInt(settings.filter_blur) !== 0) {
        filterParts.push('blur(' + parseInt(settings.filter_blur) + 'px)');
    }
    if (settings.filter_brightness && parseInt(settings.filter_brightness) !== 100) {
        filterParts.push('brightness(' + parseInt(settings.filter_brightness) + '%)');
    }
    if (settings.filter_contrast && parseInt(settings.filter_contrast) !== 100) {
        filterParts.push('contrast(' + parseInt(settings.filter_contrast) + '%)');
    }
    // Support both filter_saturation (modal) and filter_saturate (legacy)
    const saturation = settings.filter_saturation || settings.filter_saturate;
    if (saturation && parseInt(saturation) !== 100) {
        filterParts.push('saturate(' + parseInt(saturation) + '%)');
    }
    if (settings.filter_grayscale && parseInt(settings.filter_grayscale) !== 0) {
        filterParts.push('grayscale(' + parseInt(settings.filter_grayscale) + '%)');
    }
    if (settings.filter_sepia && parseInt(settings.filter_sepia) !== 0) {
        filterParts.push('sepia(' + parseInt(settings.filter_sepia) + '%)');
    }
    if (settings.filter_hue_rotate && parseInt(settings.filter_hue_rotate) !== 0) {
        filterParts.push('hue-rotate(' + parseInt(settings.filter_hue_rotate) + 'deg)');
    }
    if (settings.filter_invert && parseInt(settings.filter_invert) !== 0) {
        filterParts.push('invert(' + parseInt(settings.filter_invert) + '%)');
    }
    if (settings.filter_opacity && parseInt(settings.filter_opacity) !== 100) {
        filterParts.push('opacity(' + parseInt(settings.filter_opacity) + '%)');
    }
    if (filterParts.length > 0) {
        wrapperStyles += 'filter:' + filterParts.join(' ') + ';';
    }

    // =================================================================
    // TRANSFORMS - Critical for modal preview to show transform effects
    // =================================================================
    const transformParts = [];
    // Scale - support both transform_scale_x/y (modal) and transform_scale (legacy)
    const scaleX = settings.transform_scale_x !== undefined ? parseInt(settings.transform_scale_x) : 100;
    const scaleY = settings.transform_scale_y !== undefined ? parseInt(settings.transform_scale_y) : 100;
    if (scaleX !== 100 || scaleY !== 100) {
        transformParts.push('scale(' + (scaleX / 100) + ', ' + (scaleY / 100) + ')');
    } else if (settings.transform_scale && parseFloat(settings.transform_scale) !== 1) {
        transformParts.push('scale(' + settings.transform_scale + ')');
    }
    // Rotate
    if (settings.transform_rotate && parseInt(settings.transform_rotate) !== 0) {
        transformParts.push('rotate(' + parseInt(settings.transform_rotate) + 'deg)');
    }
    // Skew
    const skewX = parseInt(settings.transform_skew_x) || 0;
    const skewY = parseInt(settings.transform_skew_y) || 0;
    if (skewX !== 0 || skewY !== 0) {
        transformParts.push('skew(' + skewX + 'deg, ' + skewY + 'deg)');
    }
    // Translate
    const tx = parseInt(settings.transform_translate_x) || 0;
    const ty = parseInt(settings.transform_translate_y) || 0;
    if (tx !== 0 || ty !== 0) {
        transformParts.push('translate(' + tx + 'px, ' + ty + 'px)');
    }
    if (transformParts.length > 0) {
        wrapperStyles += 'transform:' + transformParts.join(' ') + ';';
    }
    if (settings.transform_origin) {
        wrapperStyles += 'transform-origin:' + settings.transform_origin + ';';
    }

    // Opacity
    if (settings.opacity && parseFloat(settings.opacity) !== 1) {
        wrapperStyles += 'opacity:' + settings.opacity + ';';
    }

    // Text alignment
    if (settings.textAlign) {
        wrapperStyles += 'text-align:' + settings.textAlign + ';';
    }

    // Transition for smooth preview
    wrapperStyles += 'transition:all 0.3s ease;';

    previewContainer.innerHTML = '<style>' + css + '</style><div class="tb-modal-preview-module" id="modal-preview-mod" style="' + wrapperStyles + '">' + preview + '</div>';
};

TB.generateModalPreviewCSS = function(mod) {
    const elements = mod.design?.elements || {};
    const type = mod.type || '';
    const elementMap = this.getElementMap(type);

    // Debug: log element CSS generation
    if (Object.keys(elements).length > 0) {
        console.log('[TB.generateModalPreviewCSS] Generating CSS for module type:', type);
        console.log('[TB.generateModalPreviewCSS] Elements to style:', Object.keys(elements));
        console.log('[TB.generateModalPreviewCSS] Element map:', elementMap);
    }

    let css = '';

    for (const [elementKey, states] of Object.entries(elements)) {
        const selector = elementMap[elementKey];
        if (!selector) {
            console.warn('[TB.generateModalPreviewCSS] No selector found for element:', elementKey);
            continue;
        }

        for (const [state, styles] of Object.entries(states)) {
            let stateSelector = '';
            if (state === 'hover') stateSelector = ':hover';
            else if (state === 'active') stateSelector = '.active, .is-active, [aria-expanded="true"]';
            else if (state === 'focus') stateSelector = ':focus';

            // Handle multiple selectors
            const selectors = selector.split(',').map(s => {
                const trimmed = s.trim();
                if (state === 'active') {
                    return trimmed.split(',').map(ss => '#modal-preview-mod ' + ss.trim() + stateSelector).join(', ');
                }
                return '#modal-preview-mod ' + trimmed + stateSelector;
            }).join(', ');

            // Build composite CSS properties
            const cssProps = this.buildCompositeCSS(styles);

            if (Object.keys(cssProps).length === 0) continue;

            css += selectors + ' {\n';
            for (const [cssProp, val] of Object.entries(cssProps)) {
                css += '  ' + cssProp + ': ' + val + ' !important;\n';
            }
            css += '}\n';
        }
    }

    return css;
};

// Build composite CSS properties from individual values
TB.buildCompositeCSS = function(styles) {
    const result = {};

    // Debug: log input styles
    console.log('[TB.buildCompositeCSS] Input styles:', styles);

    // BOX SHADOW
    if (styles.box_shadow_enabled || styles.box_shadow_h !== undefined || styles.box_shadow_blur !== undefined) {
        const h = parseInt(styles.box_shadow_h) || 0;
        const v = parseInt(styles.box_shadow_v) || 4;
        const blur = parseInt(styles.box_shadow_blur) || 10;
        const spread = parseInt(styles.box_shadow_spread) || 0;
        const color = styles.box_shadow_color || 'rgba(0,0,0,0.1)';
        const inset = styles.box_shadow_inset ? 'inset ' : '';
        
        if (styles.box_shadow_enabled) {
            result['box-shadow'] = inset + h + 'px ' + v + 'px ' + blur + 'px ' + spread + 'px ' + color;
        }
    }
    
    // BORDER RADIUS
    const hasBR = styles.border_radius_tl !== undefined || styles.border_radius_tr !== undefined ||
                  styles.border_radius_br !== undefined || styles.border_radius_bl !== undefined;
    if (hasBR) {
        const tl = (parseInt(styles.border_radius_tl) || 0) + 'px';
        const tr = (parseInt(styles.border_radius_tr) || 0) + 'px';
        const br = (parseInt(styles.border_radius_br) || 0) + 'px';
        const bl = (parseInt(styles.border_radius_bl) || 0) + 'px';
        result['border-radius'] = tl + ' ' + tr + ' ' + br + ' ' + bl;
    }
    
    // BORDER WIDTH
    const hasBW = styles.border_width_top !== undefined || styles.border_width_right !== undefined ||
                  styles.border_width_bottom !== undefined || styles.border_width_left !== undefined;
    if (hasBW) {
        const top = (parseInt(styles.border_width_top) || 0) + 'px';
        const right = (parseInt(styles.border_width_right) || 0) + 'px';
        const bottom = (parseInt(styles.border_width_bottom) || 0) + 'px';
        const left = (parseInt(styles.border_width_left) || 0) + 'px';
        result['border-width'] = top + ' ' + right + ' ' + bottom + ' ' + left;
        result['border-style'] = styles.border_style || 'solid';
    }
    
    // MARGIN
    const hasM = styles.margin_top !== undefined || styles.margin_right !== undefined ||
                 styles.margin_bottom !== undefined || styles.margin_left !== undefined;
    if (hasM) {
        const top = styles.margin_top || '0px';
        const right = styles.margin_right || '0px';
        const bottom = styles.margin_bottom || '0px';
        const left = styles.margin_left || '0px';
        result['margin'] = top + ' ' + right + ' ' + bottom + ' ' + left;
    }
    
    // PADDING
    const hasP = styles.padding_top !== undefined || styles.padding_right !== undefined ||
                 styles.padding_bottom !== undefined || styles.padding_left !== undefined;
    if (hasP) {
        const top = styles.padding_top || '0px';
        const right = styles.padding_right || '0px';
        const bottom = styles.padding_bottom || '0px';
        const left = styles.padding_left || '0px';
        result['padding'] = top + ' ' + right + ' ' + bottom + ' ' + left;
    }
    
    // FILTERS
    const filterParts = [];
    if (styles.filter_blur && parseInt(styles.filter_blur) !== 0) filterParts.push('blur(' + parseInt(styles.filter_blur) + 'px)');
    if (styles.filter_brightness && parseInt(styles.filter_brightness) !== 100) filterParts.push('brightness(' + parseInt(styles.filter_brightness) + '%)');
    if (styles.filter_contrast && parseInt(styles.filter_contrast) !== 100) filterParts.push('contrast(' + parseInt(styles.filter_contrast) + '%)');
    if (styles.filter_saturation && parseInt(styles.filter_saturation) !== 100) filterParts.push('saturate(' + parseInt(styles.filter_saturation) + '%)');
    if (styles.filter_grayscale && parseInt(styles.filter_grayscale) !== 0) filterParts.push('grayscale(' + parseInt(styles.filter_grayscale) + '%)');
    if (styles.filter_sepia && parseInt(styles.filter_sepia) !== 0) filterParts.push('sepia(' + parseInt(styles.filter_sepia) + '%)');
    if (styles.filter_hue_rotate && parseInt(styles.filter_hue_rotate) !== 0) filterParts.push('hue-rotate(' + parseInt(styles.filter_hue_rotate) + 'deg)');
    if (styles.filter_invert && parseInt(styles.filter_invert) !== 0) filterParts.push('invert(' + parseInt(styles.filter_invert) + '%)');
    // FIX: filter_opacity was missing - add it to the filter string
    if (styles.filter_opacity && parseInt(styles.filter_opacity) !== 100) filterParts.push('opacity(' + parseInt(styles.filter_opacity) + '%)');
    if (filterParts.length > 0) {
        result['filter'] = filterParts.join(' ');
    }
    
    // TRANSFORM
    // FIX: Property names must match what's saved by the modal:
    // - transform_scale_x, transform_scale_y (not transform_scale)
    // - transform_translate_x, transform_translate_y (lowercase, not camelCase)
    // - transform_skew_x, transform_skew_y (not transform_skewX/Y)
    const transformParts = [];
    const scaleX = styles.transform_scale_x !== undefined ? parseInt(styles.transform_scale_x) : 100;
    const scaleY = styles.transform_scale_y !== undefined ? parseInt(styles.transform_scale_y) : 100;
    if (scaleX !== 100 || scaleY !== 100) {
        transformParts.push('scale(' + (scaleX / 100) + ', ' + (scaleY / 100) + ')');
    }
    if (styles.transform_rotate && parseInt(styles.transform_rotate) !== 0) {
        transformParts.push('rotate(' + parseInt(styles.transform_rotate) + 'deg)');
    }
    const skewX = parseInt(styles.transform_skew_x) || 0;
    const skewY = parseInt(styles.transform_skew_y) || 0;
    if (skewX !== 0 || skewY !== 0) {
        transformParts.push('skew(' + skewX + 'deg, ' + skewY + 'deg)');
    }
    const tx = parseInt(styles.transform_translate_x) || 0;
    const ty = parseInt(styles.transform_translate_y) || 0;
    if (tx !== 0 || ty !== 0) {
        transformParts.push('translate(' + tx + 'px, ' + ty + 'px)');
    }
    if (transformParts.length > 0) {
        result['transform'] = transformParts.join(' ');
    }
    if (styles.transform_origin) {
        result['transform-origin'] = styles.transform_origin;
    }
    
    // Copy simple CSS properties (not composite parts)
    const compositeParts = [
        'box_shadow_enabled', 'box_shadow_h', 'box_shadow_v', 'box_shadow_blur',
        'box_shadow_spread', 'box_shadow_color', 'box_shadow_inset',
        'border_radius_tl', 'border_radius_tr', 'border_radius_br', 'border_radius_bl', 'border_radius_linked',
        'border_width_top', 'border_width_right', 'border_width_bottom', 'border_width_left', 'border_width_linked',
        'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'margin_linked',
        'padding_top', 'padding_right', 'padding_bottom', 'padding_left', 'padding_linked',
        'filter_blur', 'filter_brightness', 'filter_contrast', 'filter_saturation',
        'filter_grayscale', 'filter_sepia', 'filter_hue_rotate', 'filter_invert', 'filter_opacity',
        // FIX: Use correct property names that match what the modal saves
        'transform_scale_x', 'transform_scale_y', 'transform_rotate',
        'transform_translate_x', 'transform_translate_y',
        'transform_skew_x', 'transform_skew_y', 'transform_origin',
        'hover_enabled', 'animation_enabled', 'scroll_trigger_enabled', 'scroll_trigger_point', 'scroll_animate_once',
        'background_type', 'gradient_type', 'hover_color', 'hover_border_color', 'hover_background'
    ];
    
    for (const [prop, val] of Object.entries(styles)) {
        if (compositeParts.includes(prop)) continue;
        if (val === '' || val === null || val === undefined) continue;

        const cssProp = this.toCssProperty(prop);
        result[cssProp] = val;
    }

    // Debug: log generated CSS properties
    if (Object.keys(result).length > 0) {
        console.log('[TB.buildCompositeCSS] Generated CSS properties:', result);
    }

    return result;
};

TB.getElementMap = function(type) {
    // Define CSS selectors for each module's elements
    const maps = {
        toggle: {
            header: '.tb-toggle-header',
            content: '.tb-toggle-content',
            icon: '.tb-toggle-icon',
            item: '.tb-toggle-item'
        },
        accordion: {
            header: '.tb-accordion-header',
            content: '.tb-accordion-content',
            icon: '.tb-accordion-icon'
        },
        tabs: {
            nav: '.tb-tabs-nav',
            tab_button: '.tb-tab-btn',
            content: '.tb-tab-panel'
        },
        button: {
            button: '.tb-button, .tb-btn'
        },
        text: {
            paragraph: 'p',
            link: 'a'
        },
        heading: {
            heading: 'h1, h2, h3, h4, h5, h6',
            underline: '.tb-heading-underline'
        },
        image: {
            wrapper: '.tb-image',
            container: '.tb-image-container',
            image: '.tb-image-img',
            caption: '.tb-image-caption',
            overlay: '.tb-image-overlay'
        },
        gallery: {
            image: '.tb-gallery-item img',
            caption: '.tb-gallery-caption',
            overlay: '.tb-gallery-overlay',
            grid: '.tb-gallery-grid'
        },
        list: {
            item: 'li',
            bullet: 'li::marker',
            icon: '.tb-list-icon'
        },
        quote: {
            quote: 'blockquote, .tb-quote-text',
            author: '.tb-quote-author',
            icon: '.tb-quote-icon',
            border: '.tb-quote-border'
        },
        hero: {
            container: '.tb-hero-container',
            overlay: '.tb-hero-overlay',
            content: '.tb-hero-content',
            title: '.tb-hero-title',
            subtitle: '.tb-hero-subtitle',
            description: '.tb-hero-description',
            button: '.tb-hero-button, .tb-hero-btn',
            button_secondary: '.tb-hero-button-secondary'
        },
        cta: {
            title: '.tb-cta-title',
            subtitle: '.tb-cta-subtitle',
            button: '.tb-cta-button'
        },
        blurb: {
            icon: '.tb-blurb-icon',
            title: '.tb-blurb-title',
            text: '.tb-blurb-text'
        },
        testimonial: {
            quote: '.tb-testimonial-quote',
            author: '.tb-testimonial-author',
            role: '.tb-testimonial-role',
            image: '.tb-testimonial-image',
            icon: '.tb-testimonial-icon'
        },
        team: {
            image: '.tb-team-image',
            name: '.tb-team-name',
            role: '.tb-team-role',
            bio: '.tb-team-bio',
            social: '.tb-team-social a'
        },
        pricing: {
            header: '.tb-pricing-header',
            title: '.tb-pricing-title',
            price: '.tb-pricing-price',
            period: '.tb-pricing-period',
            features: '.tb-pricing-features li',
            feature_icon: '.tb-pricing-feature-icon',
            button: '.tb-pricing-button',
            badge: '.tb-pricing-badge'
        },
        form: {
            label: 'label',
            input: 'input, select',
            textarea: 'textarea',
            submit: 'button[type="submit"], input[type="submit"]',
            error: '.tb-form-error',
            success: '.tb-form-success'
        },
        social: {
            icon: '.tb-social-icon',
            container: '.tb-social-container'
        },
        slider: {
            slide: '.tb-slide',
            title: '.tb-slide-title',
            text: '.tb-slide-text',
            button: '.tb-slide-button',
            nav: '.tb-slider-nav',
            dots: '.tb-slider-dots span'
        },
        menu: {
            item: '.tb-menu-item',
            submenu: '.tb-submenu',
            submenu_item: '.tb-submenu-item',
            icon: '.tb-menu-icon'
        },
        blog: {
            card: '.tb-post-card',
            image: '.tb-post-image',
            title: '.tb-post-title',
            meta: '.tb-post-meta',
            excerpt: '.tb-post-excerpt',
            button: '.tb-post-button',
            category: '.tb-post-category'
        },
        table: {
            table: 'table',
            header: 'thead tr, th',
            row: 'tbody tr',
            cell: 'td',
            stripe: 'tbody tr:nth-child(even)'
        },
        code: {
            container: '.tb-code-container',
            code: 'code, pre',
            header: '.tb-code-header',
            copy_button: '.tb-code-copy',
            line_numbers: '.tb-line-numbers'
        },
        alert: {
            container: '.tb-alert',
            icon: '.tb-alert-icon',
            title: '.tb-alert-title',
            text: '.tb-alert-text',
            close: '.tb-alert-close'
        }
    };

    return maps[type] || {};
};

TB.toCssProperty = function(prop) {
    return prop.replace(/_/g, '-');
};

// ═══════════════════════════════════════════════════════════
// DEVICE TOGGLE FOR MODAL
// ═══════════════════════════════════════════════════════════
TB.setModalDevice = function(device) {
    if (!['desktop', 'tablet', 'mobile'].includes(device)) return;
    
    // Update current device
    this.currentDevice = device;
    
    // Update device toggle buttons in modal
    document.querySelectorAll('.tb-modal-device-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.device === device);
    });
    
    // Update modal preview container class for responsive styling
    const preview = document.getElementById('tb-modal-preview');
    if (preview) {
        preview.className = 'tb-modal-preview tb-device-' + device;
    }
    
    // Get current module for re-rendering settings
    const { sIdx, rIdx, cIdx, mIdx } = this.modalState;
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    
    // Re-render current settings to show responsive values
    const currentTab = this.modalState.currentTab || 'content';
    if (currentTab === 'design') {
        this.renderModalDesignSettings();
    } else if (currentTab === 'content') {
        this.renderModalContentSettings(mod);
    }
    
    // Update preview
    this.updateModalPreview();
};

// ═══════════════════════════════════════════════════════════
// CLOSE / SAVE / CANCEL
// ═══════════════════════════════════════════════════════════
TB.closeModuleModal = function() {
    const modal = document.getElementById('tb-module-modal');
    if (modal) modal.remove();

    document.body.classList.remove('tb-modal-open');
    document.removeEventListener('keydown', this.handleModalKeydown);

    this.modalState.isOpen = false;
    this.modalState.originalModule = null;
};

TB.cancelModuleModal = function() {
    // Restore original module
    const { sIdx, rIdx, cIdx, mIdx, originalModule } = this.modalState;
    if (originalModule) {
        this.content.sections[sIdx].rows[rIdx].columns[cIdx].modules[mIdx] = originalModule;
    }

    this.closeModuleModal();
    this.renderCanvas();
};

TB.saveModuleModal = function() {
    // Mark as dirty and save to history (optional methods)
    if (typeof this.markDirty === 'function') this.markDirty();
    if (typeof this.saveToHistory === 'function') this.saveToHistory();

    this.closeModuleModal();
    if (typeof this.renderCanvas === 'function') this.renderCanvas();
    if (typeof this.refreshPreview === 'function') this.refreshPreview();
};

// ═══════════════════════════════════════════════════════════
console.log('TB 3.0: tb-modal-editor.js loaded');
