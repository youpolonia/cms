/**
 * TB4 Visual Builder - Main Application
 *
 * Vanilla JavaScript ES6+ page builder
 * No frameworks, no jQuery, no transpilation needed
 *
 * @version 1.0.0
 */

// Debug logging for builder initialization
console.log('[TB4] builder.js loaded');
console.log('[TB4] TB4 config:', window.TB4);

const TB4Builder = {
    // ==========================================================================
    // STATE MANAGEMENT
    // ==========================================================================

    state: {
        content: {
            sections: []
        },
        selected: null,
        selectedType: null, // 'section', 'row', 'column', 'module'
        device: 'desktop',
        zoom: 100,
        history: [],
        historyIndex: -1,
        maxHistory: 50,
        isDirty: false,
        isLoading: false,
        autoSaveTimer: null,
        autoSaveInterval: 60000, // 60 seconds
        pendingRowSectionId: null,
        pendingChangeLayoutRowId: null
    },

    // Debounce timer for renderCanvas to prevent focus loss during typing
    _renderCanvasDebounceTimer: null,

    // ==========================================================================
    // CONFIGURATION
    // ==========================================================================

    config: {
        apiUrl: '',
        csrfToken: '',
        pageId: null,
        contentType: 'page',
        modules: {}
    },

    // ==========================================================================
    // DOM REFERENCES
    // ==========================================================================

    dom: {
        canvas: null,
        sidebar: null,
        settingsPanel: null,
        toolbar: null,
        moduleList: null,
        deviceButtons: null,
        zoomDisplay: null
    },

    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================

    /**
     * Initialize the builder
     */
    init() {
        console.log('[TB4] init() called');

        // Load config from global TB4 object
        if (window.TB4) {
            this.config.apiUrl = window.TB4.apiUrl || '';
            this.config.csrfToken = window.TB4.csrfToken || '';
            this.config.pageId = window.TB4.pageId || null;
            this.config.contentType = window.TB4.contentType || 'page';
            this.config.modules = window.TB4.modules || {};
            console.log('[TB4] Config loaded:', this.config);
        } else {
            console.error('[TB4] window.TB4 not found!');
        }

        // Cache DOM elements
        this.cacheDom();

        // Initialize subsystems
        this.initCanvas();
        this.initSidebar();
        this.initToolbar();
        this.initSettingsPanel();
        this.initDragDrop();
        this.initKeyboardShortcuts();
        this.initLayoutPicker();

        // Listen for Settings Sidebar save events
        document.addEventListener('tb4-ss:save', (e) => {
            console.log('[TB4] Received tb4-ss:save event:', e.detail);
            this.updateModuleFromSidebar(e.detail);
        });
        
        // Listen for Settings Sidebar live preview events
        document.addEventListener('tb4-ss:liveUpdate', (e) => {
            this.updateModuleFromSidebar(e.detail, true); // true = skip toast
        });

        // Load existing content if available (check both 'content' and 'initialContent' for compatibility)
        if (window.TB4) {
            const contentData = window.TB4.content || window.TB4.initialContent;
            if (contentData && (contentData.sections || Array.isArray(contentData))) {
                console.log('[TB4] Loading content from window.TB4:', contentData);
                this.loadContent(contentData);
            }
        }

        // Start auto-save
        this.startAutoSave();

        // Refresh Lucide icons
        this.refreshLucideIcons();

        // Dispatch ready event
        document.dispatchEvent(new CustomEvent('tb4:ready', { detail: this }));

        // Run self-test for debugging
        this._runSelfTest();

        console.log('[TB4] Builder initialized successfully');
    },

    /**
     * Cache DOM element references
     */
    cacheDom() {
        this.dom.canvas = document.getElementById('tb4-canvas');
        this.dom.sidebar = document.getElementById('tb4-sidebar');
        this.dom.settingsPanel = document.getElementById('tb4-settings-panel');
        this.dom.toolbar = document.getElementById('tb4-toolbar');
        this.dom.moduleList = document.getElementById('tb4-module-list');
        this.dom.deviceButtons = document.querySelectorAll('[data-device]');
        this.dom.zoomDisplay = document.getElementById('tb4-zoom-display');

        // Debug: Log found elements
        console.log('[TB4] DOM elements cached:', {
            canvas: !!this.dom.canvas,
            sidebar: !!this.dom.sidebar,
            settingsPanel: !!this.dom.settingsPanel,
            toolbar: !!this.dom.toolbar,
            moduleList: !!this.dom.moduleList,
            deviceButtons: this.dom.deviceButtons.length,
            zoomDisplay: !!this.dom.zoomDisplay
        });
    },

    // ==========================================================================
    // CANVAS MANAGEMENT
    // ==========================================================================

    /**
     * Initialize the canvas
     */
    initCanvas() {
        if (!this.dom.canvas) return;

        // Click handler for selection
        this.dom.canvas.addEventListener('click', (e) => {
            const element = e.target.closest('[data-tb4-id]');
            if (element) {
                e.stopPropagation();
                this.selectElement(element.dataset.tb4Id, element.dataset.tb4Type);
            } else {
                this.deselectAll();
            }
        });

        // Double-click for inline editing
        this.dom.canvas.addEventListener('dblclick', (e) => {
            const module = e.target.closest('[data-tb4-type="module"]');
            if (module) {
                this.enableInlineEdit(module);
            }
        });
    },

    /**
     * Render the canvas content
     */
    renderCanvas() {
        if (!this.dom.canvas) return;

        const content = this.state.content;
        let html = '';

        if (content.sections && content.sections.length > 0) {
            content.sections.forEach(section => {
                html += this.renderSection(section);
            });
        }

        // Add empty state if no content
        if (!html) {
            html = this.renderEmptyState();
        }

        this.dom.canvas.innerHTML = html;

        // Re-initialize drag targets
        this.initCanvasDropZones();

        // Re-select if something was selected
        if (this.state.selected) {
            const el = this.dom.canvas.querySelector(`[data-tb4-id="${this.state.selected}"]`);
            if (el) {
                el.classList.add('tb4-selected');
            }
        }

        // Re-initialize Lucide icons after dynamic content render
        this.refreshLucideIcons();
    },

    /**
     * Render empty canvas state
     */
    renderEmptyState() {
        return `
            <div class="tb4-empty-state" data-drop-zone="section">
                <div class="tb4-empty-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <p>Drag a section here to start building</p>
                <button type="button" class="tb4-btn tb4-btn-primary" onclick="TB4Builder.addSection()">
                    Add Section
                </button>
            </div>
        `;
    },

    /**
     * Render a section
     */
    renderSection(section) {
        const styles = this.buildInlineStyles(section.settings || {});
        const classes = ['tb4-section'];

        if (section.settings?.cssClass) {
            classes.push(section.settings.cssClass);
        }

        let rowsHtml = '';
        if (section.rows && section.rows.length > 0) {
            section.rows.forEach(row => {
                rowsHtml += this.renderRow(row);
            });
        } else {
            rowsHtml = `<div class="tb4-add-row" data-drop-zone="row" data-section-id="${section.id}">
                <span>+ Add Row</span>
            </div>`;
        }

        return `
            <div class="${classes.join(' ')}"
                 data-tb4-id="${section.id}"
                 data-tb4-type="section"
                 style="${styles}">
                <div class="tb4-element-actions">
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${section.id}', 'up')" title="Move Up">↑</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${section.id}', 'down')" title="Move Down">↓</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.duplicateElement('${section.id}')" title="Duplicate">⧉</button>
                    <button type="button" class="tb4-action-btn tb4-action-delete" onclick="TB4Builder.deleteElement('${section.id}')" title="Delete">×</button>
                </div>
                <div class="tb4-section-inner">
                    ${rowsHtml}
                </div>
                <div class="tb4-add-row-btn" onclick="TB4Builder.showLayoutPicker('${section.id}')">+ Add Row</div>
            </div>
        `;
    },

    /**
     * Render a row
     */
    renderRow(row) {
        const styles = this.buildInlineStyles(row.settings || {});
        const classes = ['tb4-row'];
        const layout = row.layout || '1';

        classes.push(`tb4-row-${layout.replace(/\//g, '-')}`);

        let columnsHtml = '';
        if (row.columns && row.columns.length > 0) {
            row.columns.forEach(column => {
                columnsHtml += this.renderColumn(column);
            });
        }

        return `
            <div class="${classes.join(' ')}"
                 data-tb4-id="${row.id}"
                 data-tb4-type="row"
                 data-layout="${layout}"
                 style="${styles}">
                <div class="tb4-element-actions">
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.showLayoutPickerForRow('${row.id}')" title="Change Layout">⊞</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${row.id}', 'up')" title="Move Up">↑</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${row.id}', 'down')" title="Move Down">↓</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.duplicateElement('${row.id}')" title="Duplicate">⧉</button>
                    <button type="button" class="tb4-action-btn tb4-action-delete" onclick="TB4Builder.deleteElement('${row.id}')" title="Delete">×</button>
                </div>
                <div class="tb4-row-inner">
                    ${columnsHtml}
                </div>
            </div>
        `;
    },

    /**
     * Render a column
     */
    renderColumn(column) {
        const styles = this.buildInlineStyles(column.settings || {});
        const classes = ['tb4-column'];
        const width = column.width || 100;

        let modulesHtml = '';
        if (column.modules && column.modules.length > 0) {
            column.modules.forEach(module => {
                modulesHtml += this.renderModule(module);
            });
        } else {
            modulesHtml = `<div class="tb4-module-placeholder" data-drop-zone="module" data-column-id="${column.id}">
                <span>Drop module here</span>
            </div>`;
        }

        return `
            <div class="${classes.join(' ')}"
                 data-tb4-id="${column.id}"
                 data-tb4-type="column"
                 style="flex: 0 0 ${width}%; max-width: ${width}%; ${styles}">
                <div class="tb4-column-inner" data-drop-zone="module" data-column-id="${column.id}">
                    ${modulesHtml}
                </div>
            </div>
        `;
    },

    /**
     * Render a module
     */
    renderModule(module) {
        const styles = this.buildInlineStyles(module.settings || {});
        // Normalize module type - remove tb4_ prefix if present
        const moduleType = (module.type || '').replace(/^tb4_/, '');
        const classes = ['tb4-module', `tb4-module-${moduleType}`];

        if (module.settings?.cssClass) {
            classes.push(module.settings.cssClass);
        }

        const moduleConfig = this.config.modules[moduleType] || {};
        const content = this.renderModuleContent(module);

        return `
            <div class="${classes.join(' ')}"
                 data-tb4-id="${module.id}"
                 data-tb4-type="module"
                 data-module-type="${module.type}"
                 style="${styles}">
                <div class="tb4-element-actions">
                    <span class="tb4-module-label">${moduleConfig.name || module.type}</span>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${module.id}', 'up')" title="Move Up">↑</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.moveElement('${module.id}', 'down')" title="Move Down">↓</button>
                    <button type="button" class="tb4-action-btn" onclick="TB4Builder.duplicateElement('${module.id}')" title="Duplicate">⧉</button>
                    <button type="button" class="tb4-action-btn tb4-action-delete" onclick="TB4Builder.deleteElement('${module.id}')" title="Delete">×</button>
                </div>
                <div class="tb4-module-content">
                    ${content}
                </div>
            </div>
        `;
    },

    /**
     * Render module content based on type
     */
    renderModuleContent(module) {
        const data = module.content || {};
        // Normalize module type - remove tb4_ prefix if present
        const moduleType = (module.type || '').replace(/^tb4_/, '');

        switch (moduleType) {
            case 'text':
                // Support both 'text' and 'content' field names
                const textContent = data.text || data.content || '';
                const heading = data.heading || '';
                const headingTag = data.tag || data.heading_level || 'h2';
                const textAlign = data.align || data.text_align || 'left';
                
                // Check if content is default/placeholder
                const isDefaultContent = !textContent || !textContent.trim() || 
                    textContent === '<p>Click to edit text...</p>' || 
                    textContent === '<p>Your content goes here. Click to edit.</p>';
                
                // If we have heading OR non-default content, show real content
                if (heading || !isDefaultContent) {
                    let html = '';
                    if (heading) {
                        html += `<${headingTag} class="tb4-text-heading" style="color:#111827;text-align:${textAlign};margin:0 0 12px 0;">${this.escapeHtml(heading)}</${headingTag}>`;
                    }
                    // Show content (use default text if empty but heading exists)
                    const displayContent = isDefaultContent ? '<p>Your content goes here. Click to edit.</p>' : textContent;
                    html += `<div class="tb4-text-content" style="color:#374151;text-align:${textAlign};">${displayContent}</div>`;
                    return html;
                }
                
                // No heading and default content - show placeholder
                return `<div class="tb4-text-content" style="color:#6b7280;">
                    <p style="margin:0 0 12px 0;">This is sample text content. You can edit this in the settings panel to add your own paragraphs, headings, and formatted text.</p>
                    <p style="margin:0;">Double-click to enable inline editing, or use the Content tab in Settings.</p>
                </div>`;

            case 'heading':
                const tag = data.tag || 'h2';
                return `<${tag} class="tb4-heading">${data.text || 'Heading'}</${tag}>`;

            case 'image':
                if (data.src) {
                    return `<img src="${this.escapeHtml(data.src)}" alt="${this.escapeHtml(data.alt || '')}" class="tb4-image" style="max-width:100%;height:auto;display:block;"/>`;
                }
                return `<div class="tb4-image-placeholder" style="aspect-ratio:16/10;background:linear-gradient(135deg,#e5e7eb,#f3f4f6);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #d1d5db;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin-bottom:12px;">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="m21 15-5-5L5 21"/>
                    </svg>
                    <span style="color:#6b7280;font-weight:500;">Image</span>
                    <span style="color:#9ca3af;font-size:12px;margin-top:4px;">Select in settings</span>
                </div>`;

            case 'button':
                const btnStyle = data.style || 'primary';
                return `<a href="${this.escapeHtml(data.url || '#')}" class="tb4-button tb4-button-${btnStyle}">${data.text || 'Button'}</a>`;

            case 'video':
                if (data.video_url || data.url) {
                    return `<div class="tb4-video-wrapper">${this.renderVideoEmbed(data.video_url || data.url)}</div>`;
                }
                return `<div class="tb4-video-placeholder" style="aspect-ratio:16/9;background:linear-gradient(135deg,#1f2937,#374151);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;">
                    <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    <span style="font-size:16px;font-weight:500;">Video Player</span>
                    <span style="font-size:13px;opacity:0.7;margin-top:4px;">Add URL in settings</span>
                </div>`;

            case 'divider':
                const divStyle = data.style || 'solid';
                return `<hr class="tb4-divider tb4-divider-${divStyle}"/>`;

            case 'spacer':
                const height = data.height || 50;
                return `<div class="tb4-spacer" style="height: ${height}px;"></div>`;

            case 'html':
                return `<div class="tb4-html-content">${data.html || '<!-- Custom HTML -->'}</div>`;

            case 'gallery':
                return this.renderGalleryContent(data);

            case 'accordion':
                return this.renderAccordionContent(data);

            case 'tabs':
                return this.renderTabsContent(data);

            case 'icon':
                const iconName = data.icon_name || 'star';
                // Handle icon_size with or without px suffix
                let iconSize = data.icon_size || '48';
                if (typeof iconSize === 'string' && iconSize.endsWith('px')) {
                    iconSize = iconSize.replace('px', '');
                }
                const iconColor = data.icon_color || '#6b7280';
                const iconPath = this.getIconSvgPath(iconName);
                console.log('[TB4] Icon render:', { iconName, iconSize, iconColor, hasPath: !!iconPath });
                return `<div class="tb4-icon-preview" style="text-align:center;padding:20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="${iconSize}" height="${iconSize}" viewBox="0 0 24 24" fill="none" stroke="${iconColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        ${iconPath}
                    </svg>
                </div>`;

            case 'list':
                return this.renderListContent(data);

            case 'quote':
                return `<blockquote class="tb4-quote">
                    <p>${data.text || 'Quote text...'}</p>
                    ${data.author ? `<cite>— ${this.escapeHtml(data.author)}</cite>` : ''}
                </blockquote>`;

            case 'form':
                return `<div class="tb4-form-preview" style="padding:24px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
                    <h4 style="margin:0 0 16px 0;color:#111827;font-size:18px;">Contact Form</h4>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <input type="text" placeholder="Your Name" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;background:white;"/>
                        <input type="email" placeholder="Email Address" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;background:white;"/>
                        <textarea placeholder="Your Message" rows="3" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;background:white;resize:none;"></textarea>
                        <button type="button" style="padding:12px 24px;background:#3b82f6;color:white;border:none;border-radius:6px;font-weight:500;cursor:pointer;">Send Message</button>
                    </div>
                </div>`;

            case 'cta':
                return `<div class="tb4-cta" style="padding:40px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;text-align:center;color:white;">
                    <h3 class="tb4-cta-title" style="margin:0 0 12px 0;font-size:28px;font-weight:700;">${this.escapeHtml(data.title || 'Call to Action')}</h3>
                    <p class="tb4-cta-text" style="margin:0 0 24px 0;font-size:16px;opacity:0.9;max-width:500px;margin-left:auto;margin-right:auto;">${this.escapeHtml(data.description || data.text || 'Take action today!')}</p>
                    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                        <a href="${this.escapeHtml(data.button_url || '#')}" class="tb4-cta-button" style="padding:14px 28px;background:white;color:#2563eb;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.button_text || 'Get Started')}</a>
                        ${data.secondary_button_text ? `<a href="${this.escapeHtml(data.secondary_button_url || '#')}" style="padding:14px 28px;background:transparent;color:white;border:2px solid white;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.secondary_button_text)}</a>` : ''}
                    </div>
                </div>`;

            case 'testimonial':
                return `<div class="tb4-testimonial" style="padding:32px;background:white;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                    ${data.rating ? `<div style="margin-bottom:16px;color:#f59e0b;font-size:20px;">${'★'.repeat(Math.min(5, parseInt(data.rating) || 0))}${'☆'.repeat(5 - Math.min(5, parseInt(data.rating) || 0))}</div>` : ''}
                    <blockquote class="tb4-testimonial-quote" style="margin:0 0 20px 0;font-size:18px;line-height:1.6;color:#374151;font-style:italic;">"${this.escapeHtml(data.quote || 'Customer testimonial...')}"</blockquote>
                    <div class="tb4-testimonial-author" style="display:flex;align-items:center;gap:12px;">
                        ${data.author_image ? `<img src="${this.escapeHtml(data.author_image)}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">` : `<div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:18px;">${(data.author_name || 'C').charAt(0).toUpperCase()}</div>`}
                        <div>
                            <div style="font-weight:600;color:#111827;">${this.escapeHtml(data.author_name || 'Customer Name')}</div>
                            ${data.author_title || data.author_company ? `<div style="font-size:14px;color:#6b7280;">${this.escapeHtml(data.author_title || '')}${data.author_title && data.author_company ? ' at ' : ''}${this.escapeHtml(data.author_company || '')}</div>` : ''}
                        </div>
                    </div>
                </div>`;

            case 'audio':
                if (data.src || data.audio_url) {
                    return `<div class="tb4-audio">
                        <audio controls src="${this.escapeHtml(data.src || data.audio_url)}"></audio>
                        ${data.title ? `<p class="tb4-audio-title">${this.escapeHtml(data.title)}</p>` : ''}
                    </div>`;
                }
                return `<div class="tb4-audio-placeholder" style="padding:24px;background:linear-gradient(135deg,#1e1e1e,#2d2d2d);border-radius:12px;">
                    <div style="display:flex;align-items:center;gap:16px;">
                        <div style="width:56px;height:56px;background:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <div style="flex:1;">
                            <div style="color:white;font-weight:500;margin-bottom:8px;">${this.escapeHtml(data.title || 'Audio Track')}</div>
                            <div style="height:4px;background:#404040;border-radius:2px;position:relative;">
                                <div style="position:absolute;left:0;top:0;width:35%;height:100%;background:#3b82f6;border-radius:2px;"></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:12px;color:#888;">
                                <span>1:23</span><span>3:45</span>
                            </div>
                        </div>
                    </div>
                </div>`;

            case 'social': {
                let socialIconSize = data.icon_size || '24';
                let socialIconColor = data.icon_color || '#6b7280';
                let socialGap = data.gap || '12px';
                let socialStyle = data.style || 'simple'; // simple, rounded, filled

                let socialIcons = {
                    facebook: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                    twitter: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                    instagram: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
                    linkedin: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
                    youtube: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>'
                };

                let hasSocial = data.facebook || data.twitter || data.instagram || data.linkedin || data.youtube;
                
                if (!hasSocial) {
                    // Show all icons as preview
                    let previewHtml = '<div class="tb4-social-preview" style="display:flex;gap:' + socialGap + ';justify-content:center;padding:20px;">';
                    Object.keys(socialIcons).forEach(function(key) {
                        previewHtml += '<a href="#" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#f3f4f6;border-radius:8px;color:' + socialIconColor + ';">';
                        previewHtml += '<div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons[key] + '</div>';
                        previewHtml += '</a>';
                    });
                    previewHtml += '</div>';
                    return previewHtml;
                }

                let socialHtml = '<div class="tb4-social" style="display:flex;gap:' + socialGap + ';justify-content:center;padding:16px;">';
                if (data.facebook) socialHtml += '<a href="' + this.escapeHtml(data.facebook) + '" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#1877f2;border-radius:8px;color:white;"><div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons.facebook + '</div></a>';
                if (data.twitter) socialHtml += '<a href="' + this.escapeHtml(data.twitter) + '" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#000000;border-radius:8px;color:white;"><div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons.twitter + '</div></a>';
                if (data.instagram) socialHtml += '<a href="' + this.escapeHtml(data.instagram) + '" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border-radius:8px;color:white;"><div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons.instagram + '</div></a>';
                if (data.linkedin) socialHtml += '<a href="' + this.escapeHtml(data.linkedin) + '" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#0a66c2;border-radius:8px;color:white;"><div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons.linkedin + '</div></a>';
                if (data.youtube) socialHtml += '<a href="' + this.escapeHtml(data.youtube) + '" style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#ff0000;border-radius:8px;color:white;"><div style="width:' + socialIconSize + 'px;height:' + socialIconSize + 'px;">' + socialIcons.youtube + '</div></a>';
                socialHtml += '</div>';
                return socialHtml;
            }

            case 'hero':
                return `<div class="tb4-hero" style="padding:60px 20px;text-align:center;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:12px;">
                    <h1 class="tb4-hero-title" style="margin:0 0 12px 0;font-size:42px;font-weight:700;">${this.escapeHtml(data.title || 'Hero Title')}</h1>
                    <p class="tb4-hero-subtitle" style="margin:0 0 16px 0;font-size:20px;opacity:0.9;">${this.escapeHtml(data.subtitle || 'Hero subtitle text')}</p>
                    ${data.description ? `<p style="margin:0 0 24px 0;font-size:16px;opacity:0.8;max-width:600px;margin-left:auto;margin-right:auto;">${this.escapeHtml(data.description)}</p>` : ''}
                    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                        ${data.button_text ? `<a href="${this.escapeHtml(data.button_url || '#')}" class="tb4-hero-button" style="padding:14px 28px;background:white;color:#667eea;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.button_text)}</a>` : ''}
                        ${data.secondary_button_text ? `<a href="${this.escapeHtml(data.secondary_button_url || '#')}" style="padding:14px 28px;background:transparent;color:white;border:2px solid white;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.secondary_button_text)}</a>` : ''}
                    </div>
                </div>`;

            case 'team':
                return `<div class="tb4-team">
                    <div class="tb4-team-member" style="text-align:center;padding:24px;background:#f9fafb;border-radius:12px;">
                        ${(data.photo || data.image) ? `<img src="${this.escapeHtml(data.photo || data.image)}" alt="" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 16px auto;display:block;border:4px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.1);">` : '<div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);margin:0 auto 16px auto;display:flex;align-items:center;justify-content:center;font-size:48px;color:white;font-weight:bold;">' + (data.name ? data.name.charAt(0).toUpperCase() : 'T') + '</div>'}
                        <h4 class="tb4-team-name" style="margin:0 0 4px 0;font-size:18px;font-weight:600;color:#111827;">${this.escapeHtml(data.name || 'Team Member')}</h4>
                        <p class="tb4-team-role" style="margin:0 0 12px 0;color:#6b7280;font-size:14px;">${this.escapeHtml(data.position || data.role || 'Role / Position')}</p>
                        ${data.bio ? `<p style="margin:0 0 12px 0;color:#4b5563;font-size:14px;line-height:1.5;">${this.escapeHtml(data.bio)}</p>` : ''}
                        <div style="display:flex;gap:12px;justify-content:center;">
                            ${data.facebook_url ? `<a href="${this.escapeHtml(data.facebook_url)}" style="color:#6b7280;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>` : ''}
                            ${data.twitter_url ? `<a href="${this.escapeHtml(data.twitter_url)}" style="color:#6b7280;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>` : ''}
                            ${data.linkedin_url ? `<a href="${this.escapeHtml(data.linkedin_url)}" style="color:#6b7280;"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>` : ''}
                        </div>
                    </div>
                </div>`;

            case 'code':
                return `<div class="tb4-code">
                    <pre style="background:#1e1e1e;color:#d4d4d4;padding:16px;border-radius:4px;overflow-x:auto;"><code>${this.escapeHtml(data.code || '// Your code here')}</code></pre>
                </div>`;

            case 'blurb':
                const blurbIconName = data.icon || 'star';
                const blurbIconColor = data.icon_color || '#3b82f6';
                const blurbIconPath = this.getIconSvgPath(blurbIconName);
                const blurbAlign = data.text_align || 'left';
                return `<div class="tb4-blurb" style="text-align:${blurbAlign};">
                    <div class="tb4-blurb-icon" style="margin-bottom:16px;${blurbAlign === 'center' ? 'display:flex;justify-content:center;' : ''}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="${blurbIconColor}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            ${blurbIconPath}
                        </svg>
                    </div>
                    <h3 class="tb4-blurb-title" style="margin:0 0 8px 0;font-size:20px;font-weight:600;color:#111827;">${this.escapeHtml(data.title || 'Blurb Title')}</h3>
                    <p class="tb4-blurb-text" style="margin:0;color:#4b5563;line-height:1.6;">${this.escapeHtml(data.content || data.text || 'Blurb description text...')}</p>
                </div>`;

            case 'slider_item': {
                let slideHeading = data.heading || 'Slide Title';
                let slideSub = data.subheading || '';
                let slideBg = data.background_color || '#1e3a5f';
                let slideBtn = data.button_text || 'Learn More';
                return '<div class="tb4-slider-item-preview" style="background:' + slideBg + ';">' +
                    '<div class="tb4-slider-item-content">' +
                        '<h3 class="tb4-slider-item-heading">' + this.escapeHtml(slideHeading) + '</h3>' +
                        (slideSub ? '<p class="tb4-slider-item-sub">' + this.escapeHtml(slideSub) + '</p>' : '') +
                        '<span class="tb4-slider-item-btn">' + this.escapeHtml(slideBtn) + '</span>' +
                    '</div>' +
                '</div>';
            }

            // Login module
            case 'login':
                return `<div class="tb4-login-preview" style="padding:20px;background:#f9fafb;border-radius:8px;">
                    <div class="tb4-login-form" style="display:flex;flex-direction:column;gap:10px;max-width:300px;margin:0 auto;">
                        <input type="text" placeholder="Username" disabled style="padding:8px;border:1px solid #d1d5db;border-radius:4px;"/>
                        <input type="password" placeholder="Password" disabled style="padding:8px;border:1px solid #d1d5db;border-radius:4px;"/>
                        <button type="button" style="padding:10px;background:#3b82f6;color:white;border:none;border-radius:4px;cursor:pointer;">Login</button>
                    </div>
                </div>`;

            // Search module
            case 'search': {
                let srchPlaceholder = data.placeholder || 'Search...';
                let srchBtnText = data.button_text || '';
                let srchShowIcon = data.show_icon !== 'false' && data.show_icon !== false;
                let srchBtnContent = srchShowIcon ? '🔍' : '';
                if (srchBtnText) srchBtnContent = srchBtnText + (srchShowIcon ? ' 🔍' : '');
                
                return `<div class="tb4-search-preview" style="display:flex;gap:8px;padding:10px;">
                    <input type="text" placeholder="${this.escapeHtml(srchPlaceholder)}" disabled style="flex:1;padding:12px;border:1px solid #d1d5db;border-radius:6px;font-size:15px;color:#374151;background:#fff;"/>
                    <button type="button" style="padding:12px 20px;background:#3b82f6;color:white;border:none;border-radius:6px;font-weight:500;">${srchBtnContent || 'Search'}</button>
                </div>`;
            }

            // Signup module
            case 'signup':
                return `<div class="tb4-signup-preview" style="display:flex;gap:8px;padding:10px;background:#f3f4f6;border-radius:8px;">
                    <input type="email" placeholder="Enter your email" disabled style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:4px;"/>
                    <button type="button" style="padding:10px 20px;background:#10b981;color:white;border:none;border-radius:4px;">${this.escapeHtml(data.button_text || 'Subscribe')}</button>
                </div>`;

            // Signup Item module
            case 'signup_item':
                const suType = data.field_type || 'email';
                const suLabel = this.escapeHtml(data.field_label || 'Email Address');
                const suPlaceholder = this.escapeHtml(data.placeholder || 'Enter your email');
                const suRequired = data.required === 'yes' ? '<span style="color:#ef4444;margin-left:2px;">*</span>' : '';
                const suShowLabel = data.show_label !== 'no';
                if (suType === 'checkbox') {
                    const suCheckText = this.escapeHtml(data.checkbox_text || 'I agree to receive marketing emails');
                    return `<div class="tb4-signup-item-preview" style="padding:12px;background:#f9fafb;border-radius:8px;">
                        <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;color:#374151;">
                            <input type="checkbox" disabled style="margin-top:3px;"/>
                            <span style="font-size:14px;">${suCheckText}${suRequired}</span>
                        </label>
                    </div>`;
                }
                return `<div class="tb4-signup-item-preview" style="padding:12px;background:#f9fafb;border-radius:8px;">
                    ${suShowLabel ? `<label style="display:block;margin-bottom:6px;font-weight:500;font-size:14px;color:#374151;">${suLabel}${suRequired}</label>` : ''}
                    <input type="text" placeholder="${suPlaceholder}" disabled style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;background:white;"/>
                </div>`;

            // Blog module
            case 'blog': {
                let blogPostsCount = parseInt(data.posts_count) || 6;
                let blogLayout = data.layout || 'grid';
                let blogColumns = data.columns || '3';
                let blogShowImage = data.show_image !== 'no';
                let blogShowTitle = data.show_title !== 'no';
                let blogShowExcerpt = data.show_excerpt !== 'no';
                let blogShowDate = data.show_date !== 'no';
                let blogShowAuthor = data.show_author !== 'no';
                let blogShowCategory = data.show_category !== 'no';
                let blogShowReadMore = data.show_read_more !== 'no';
                let blogReadMoreText = data.read_more_text || 'Read More';

                let blogCardBg = data.card_background || '#ffffff';
                let blogCardBorder = data.card_border_color || '#e5e7eb';
                let blogCardRadius = data.card_border_radius || '12px';
                let blogCardShadowVal = data.card_shadow || 'sm';
                let blogGap = data.gap || '24px';
                let blogImageHeight = data.image_height || '200px';
                let blogContentPadding = data.content_padding || '20px';
                let blogTitleColor = data.title_color || '#111827';
                let blogTitleSize = data.title_font_size || '20px';
                let blogExcerptColor = data.excerpt_color || '#6b7280';
                let blogMetaColor = data.meta_color || '#9ca3af';
                let blogCategoryBg = data.category_bg_color || '#2563eb';
                let blogCategoryColor = data.category_text_color || '#ffffff';
                let blogReadMoreColor = data.read_more_color || '#2563eb';

                let blogShadowMap = {
                    'none': 'none',
                    'sm': '0 1px 3px rgba(0,0,0,0.1)',
                    'md': '0 4px 6px rgba(0,0,0,0.1)',
                    'lg': '0 10px 15px rgba(0,0,0,0.1)'
                };
                let blogShadow = blogShadowMap[blogCardShadowVal] || blogShadowMap['sm'];

                // Sample posts for preview (real posts loaded on frontend)
                let blogSamplePosts = [
                    {title: 'Getting Started with Web Design', excerpt: 'Learn the fundamentals of modern web design and create stunning websites.', category: 'Design', author: 'John Doe', date: 'Jan 5, 2026'},
                    {title: 'Top 10 SEO Tips for 2026', excerpt: 'Discover the latest SEO strategies to improve your website ranking.', category: 'Marketing', author: 'Jane Smith', date: 'Jan 4, 2026'},
                    {title: 'The Future of AI in Business', excerpt: 'Explore how artificial intelligence is transforming businesses.', category: 'Technology', author: 'Mike Johnson', date: 'Jan 3, 2026'},
                    {title: 'Building a Strong Brand', excerpt: 'A guide to creating a memorable brand identity for your business.', category: 'Branding', author: 'Sarah Williams', date: 'Jan 2, 2026'},
                    {title: 'E-commerce Best Practices', excerpt: 'Essential tips for running a successful online store.', category: 'E-commerce', author: 'Tom Brown', date: 'Jan 1, 2026'},
                    {title: 'Social Media Marketing', excerpt: 'Effective strategies to grow your following and increase engagement.', category: 'Marketing', author: 'Lisa Davis', date: 'Dec 31, 2025'}
                ];

                let layoutClass = blogLayout === 'list' ? ' layout-list' : '';
                let gridStyle = blogLayout === 'list' ? 'display:flex;flex-direction:column;' : 'display:grid;grid-template-columns:repeat(' + blogColumns + ',1fr);';

                let blogHtml = '<div class="tb4-blog-preview">';
                blogHtml += '<div class="tb4-blog-grid' + layoutClass + '" style="' + gridStyle + 'gap:' + blogGap + ';">';

                for (let bi = 0; bi < Math.min(blogPostsCount, blogSamplePosts.length); bi++) {
                    let post = blogSamplePosts[bi];
                    let cardStyle = 'background:' + blogCardBg + ';border:1px solid ' + blogCardBorder + ';border-radius:' + blogCardRadius + ';box-shadow:' + blogShadow + ';overflow:hidden;';
                    
                    if (blogLayout === 'list') {
                        cardStyle += 'display:flex;flex-direction:row;';
                    }

                    blogHtml += '<div class="tb4-blog-card" style="' + cardStyle + '">';

                    if (blogShowImage) {
                        let imgStyle = 'width:100%;height:' + blogImageHeight + ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;position:relative;';
                        if (blogLayout === 'list') {
                            imgStyle = 'width:250px;min-width:250px;height:auto;min-height:180px;background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;position:relative;';
                        }
                        blogHtml += '<div class="tb4-blog-image" style="' + imgStyle + '">';
                        blogHtml += '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                        if (blogShowCategory) {
                            blogHtml += '<span style="position:absolute;top:12px;left:12px;background:' + blogCategoryBg + ';color:' + blogCategoryColor + ';padding:4px 10px;border-radius:4px;font-size:11px;font-weight:500;">' + post.category + '</span>';
                        }
                        blogHtml += '</div>';
                    }

                    blogHtml += '<div class="tb4-blog-content" style="padding:' + blogContentPadding + ';flex:1;">';
                    
                    if (blogShowTitle) {
                        blogHtml += '<h3 style="margin:0 0 8px 0;font-size:' + blogTitleSize + ';font-weight:600;color:' + blogTitleColor + ';line-height:1.3;">' + post.title + '</h3>';
                    }
                    
                    if (blogShowDate || blogShowAuthor) {
                        blogHtml += '<div style="display:flex;gap:12px;margin-bottom:10px;font-size:13px;color:' + blogMetaColor + ';">';
                        if (blogShowDate) blogHtml += '<span>' + post.date + '</span>';
                        if (blogShowAuthor) blogHtml += '<span>by ' + post.author + '</span>';
                        blogHtml += '</div>';
                    }
                    
                    if (blogShowExcerpt) {
                        blogHtml += '<p style="margin:0 0 12px 0;font-size:14px;color:' + blogExcerptColor + ';line-height:1.5;">' + post.excerpt + '</p>';
                    }
                    
                    if (blogShowReadMore) {
                        blogHtml += '<a href="#" style="color:' + blogReadMoreColor + ';font-size:14px;font-weight:500;text-decoration:none;">' + blogReadMoreText + ' →</a>';
                    }
                    
                    blogHtml += '</div></div>';
                }

                blogHtml += '</div></div>';
                return blogHtml;
            }

            // Portfolio module
            case 'portfolio': {
                const portfolioId = 'pf_' + Math.random().toString(36).substr(2, 9);
                let portfolioCount = parseInt(data.items_count) || 6;
                let portfolioColumns = data.columns || '3';
                let portfolioShowTitle = data.show_title !== 'no';
                let portfolioShowCategory = data.show_category !== 'no';
                let portfolioHoverEffect = data.hover_effect || 'fade';
                let portfolioShowFilter = data.show_filter === 'yes';

                let portfolioGap = data.gap || '16px';
                let portfolioRadius = data.item_border_radius || '12px';
                let portfolioOverlay = data.overlay_color || 'rgba(0,0,0,0.7)';
                let portfolioTitleColor = data.title_color || '#ffffff';
                let portfolioTitleSize = data.title_font_size || '18px';
                let portfolioCategoryColor = data.category_color || 'rgba(255,255,255,0.8)';
                let portfolioFilterBg = data.filter_bg_color || '#f3f4f6';
                let portfolioFilterActiveBg = data.filter_active_bg || '#2563eb';
                let portfolioFilterText = data.filter_text_color || '#374151';
                let portfolioFilterActiveText = data.filter_active_text || '#ffffff';

                let portfolioSamples = [
                    {title: 'Brand Identity Design', category: 'Branding', color: '#667eea'},
                    {title: 'E-commerce Website', category: 'Web Design', color: '#f59e0b'},
                    {title: 'Mobile App UI', category: 'UI/UX', color: '#10b981'},
                    {title: 'Marketing Campaign', category: 'Marketing', color: '#ef4444'},
                    {title: 'Product Photography', category: 'Photography', color: '#8b5cf6'},
                    {title: 'Corporate Video', category: 'Video', color: '#06b6d4'},
                    {title: 'Social Media Kit', category: 'Branding', color: '#ec4899'},
                    {title: 'Landing Page', category: 'Web Design', color: '#84cc16'},
                    {title: 'Dashboard Design', category: 'UI/UX', color: '#f97316'}
                ];

                let portfolioHtml = `<div class="tb4-portfolio-preview" id="${portfolioId}" data-active-bg="${portfolioFilterActiveBg}" data-active-text="${portfolioFilterActiveText}" data-bg="${portfolioFilterBg}" data-text="${portfolioFilterText}">`;

                if (portfolioShowFilter) {
                    portfolioHtml += '<div class="tb4-portfolio-filter" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px;justify-content:center;">';
                    portfolioHtml += `<button onclick="TB4Builder.portfolioFilter(this)" class="active" style="padding:8px 20px;background:${portfolioFilterActiveBg};border:none;border-radius:6px;font-size:14px;font-weight:500;color:${portfolioFilterActiveText};cursor:pointer;">All</button>`;
                    ['Branding', 'Web Design', 'UI/UX'].forEach(function(cat) {
                        portfolioHtml += `<button onclick="TB4Builder.portfolioFilter(this)" style="padding:8px 20px;background:${portfolioFilterBg};border:none;border-radius:6px;font-size:14px;font-weight:500;color:${portfolioFilterText};cursor:pointer;">${cat}</button>`;
                    });
                    portfolioHtml += '</div>';
                }

                portfolioHtml += '<div class="tb4-portfolio-grid" style="display:grid;grid-template-columns:repeat(' + portfolioColumns + ',1fr);gap:' + portfolioGap + ';">';

                for (let pi = 0; pi < Math.min(portfolioCount, portfolioSamples.length); pi++) {
                    let item = portfolioSamples[pi];
                    
                    portfolioHtml += '<div class="tb4-portfolio-item" style="position:relative;overflow:hidden;border-radius:' + portfolioRadius + ';cursor:pointer;aspect-ratio:1/1;">';
                    
                    portfolioHtml += '<div class="tb4-portfolio-image" style="width:100%;height:100%;background:linear-gradient(135deg,' + item.color + ' 0%,' + item.color + '99 100%);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);transition:transform 0.4s;">';
                    portfolioHtml += '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                    portfolioHtml += '</div>';

                    portfolioHtml += '<div class="tb4-portfolio-overlay" style="position:absolute;inset:0;background:' + portfolioOverlay + ';display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;">';
                    
                    portfolioHtml += '<div style="width:48px;height:48px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:16px;">';
                    portfolioHtml += '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>';
                    portfolioHtml += '</div>';

                    if (portfolioShowTitle) {
                        portfolioHtml += '<h4 style="font-size:' + portfolioTitleSize + ';font-weight:600;color:' + portfolioTitleColor + ';margin:0 0 4px 0;text-align:center;padding:0 16px;">' + item.title + '</h4>';
                    }
                    if (portfolioShowCategory) {
                        portfolioHtml += '<span style="font-size:12px;color:' + portfolioCategoryColor + ';text-transform:uppercase;letter-spacing:1px;">' + item.category + '</span>';
                    }

                    portfolioHtml += '</div></div>';
                }

                portfolioHtml += '</div></div>';
                portfolioHtml += '<style>.tb4-portfolio-item:hover .tb4-portfolio-overlay{opacity:1!important}.tb4-portfolio-item:hover .tb4-portfolio-image{transform:scale(1.1)}</style>';

                return portfolioHtml;
            }

            // Post Slider module
            case 'post_slider': {
                const psSliderId = 'ps_' + Math.random().toString(36).substr(2, 9);
                let psPostsCount = parseInt(data.posts_count) || 4;
                let psVisiblePosts = parseInt(data.visible_posts) || 3;
                let psShowImage = data.show_image !== 'no';
                let psShowTitle = data.show_title !== 'no';
                let psShowExcerpt = data.show_excerpt !== 'no';
                let psShowDate = data.show_date !== 'no';
                let psShowCategory = data.show_category !== 'no';
                let psShowArrows = data.show_arrows !== 'no';
                let psShowDots = data.show_dots !== 'no';

                let psCardBg = data.card_background || '#ffffff';
                let psCardRadius = data.card_border_radius || '12px';
                let psCardShadowVal = data.card_shadow || 'sm';
                let psGap = data.gap || '24px';
                let psImageHeight = data.image_height || '200px';
                let psTitleColor = data.title_color || '#111827';
                let psTitleSize = data.title_font_size || '18px';
                let psExcerptColor = data.excerpt_color || '#6b7280';
                let psMetaColor = data.meta_color || '#9ca3af';
                let psCategoryBg = data.category_bg || '#2563eb';
                let psCategoryColor = data.category_color || '#ffffff';
                let psArrowColor = data.arrow_color || '#374151';
                let psArrowBg = data.arrow_bg || '#ffffff';
                let psDotColor = data.dot_color || '#d1d5db';
                let psDotActive = data.dot_active_color || '#2563eb';

                let psShadowMap = {
                    'none': 'none',
                    'sm': '0 1px 3px rgba(0,0,0,0.1)',
                    'md': '0 4px 6px rgba(0,0,0,0.1)',
                    'lg': '0 10px 15px rgba(0,0,0,0.1)'
                };
                let psShadow = psShadowMap[psCardShadowVal] || psShadowMap['sm'];

                let psSamplePosts = [
                    {title: 'Getting Started with Web Design', excerpt: 'Learn the fundamentals of modern web design.', category: 'Design', date: 'Jan 5, 2026'},
                    {title: 'Top 10 SEO Tips for 2026', excerpt: 'Discover the latest SEO strategies.', category: 'Marketing', date: 'Jan 4, 2026'},
                    {title: 'The Future of AI in Business', excerpt: 'How AI is transforming businesses.', category: 'Technology', date: 'Jan 3, 2026'},
                    {title: 'Building a Strong Brand', excerpt: 'Create a memorable brand identity.', category: 'Branding', date: 'Jan 2, 2026'}
                ];

                let psHtml = `<div class="tb4-post-slider-preview" id="${psSliderId}" data-current="0" data-total="${Math.ceil(psPostsCount / psVisiblePosts)}" style="position:relative;padding:20px 0;">`;
                
                if (psShowArrows) {
                    psHtml += `<button onclick="TB4Builder.postSliderPrev('${psSliderId}')" style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:${psArrowBg};border:1px solid #e5e7eb;color:${psArrowColor};cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.1);z-index:10;">◀</button>`;
                    psHtml += `<button onclick="TB4Builder.postSliderNext('${psSliderId}')" style="position:absolute;right:0;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:${psArrowBg};border:1px solid #e5e7eb;color:${psArrowColor};cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.1);z-index:10;">▶</button>`;
                }

                psHtml += '<div class="tb4-post-slider-track" style="display:flex;gap:' + psGap + ';padding:0 50px;overflow:hidden;">';

                for (let psi = 0; psi < Math.min(psVisiblePosts, psSamplePosts.length); psi++) {
                    let post = psSamplePosts[psi];
                    psHtml += '<div class="tb4-post-slider-card" style="flex:1;background:' + psCardBg + ';border-radius:' + psCardRadius + ';box-shadow:' + psShadow + ';overflow:hidden;">';
                    
                    if (psShowImage) {
                        psHtml += '<div style="height:' + psImageHeight + ';background:linear-gradient(135deg,#e5e7eb,#f3f4f6);display:flex;align-items:center;justify-content:center;color:#9ca3af;position:relative;">';
                        psHtml += '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                        if (psShowCategory) {
                            psHtml += '<span style="position:absolute;top:10px;left:10px;background:' + psCategoryBg + ';color:' + psCategoryColor + ';padding:4px 10px;border-radius:4px;font-size:11px;font-weight:500;">' + post.category + '</span>';
                        }
                        psHtml += '</div>';
                    }

                    psHtml += '<div style="padding:16px;">';
                    if (psShowDate) {
                        psHtml += '<div style="font-size:12px;color:' + psMetaColor + ';margin-bottom:8px;">' + post.date + '</div>';
                    }
                    if (psShowTitle) {
                        psHtml += '<h4 style="margin:0 0 8px 0;font-size:' + psTitleSize + ';font-weight:600;color:' + psTitleColor + ';line-height:1.3;">' + post.title + '</h4>';
                    }
                    if (psShowExcerpt) {
                        psHtml += '<p style="margin:0;font-size:14px;color:' + psExcerptColor + ';line-height:1.5;">' + post.excerpt + '</p>';
                    }
                    psHtml += '</div></div>';
                }

                psHtml += '</div>';

                if (psShowDots) {
                    psHtml += '<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;">';
                    for (let di = 0; di < Math.ceil(psPostsCount / psVisiblePosts); di++) {
                        let dotBg = di === 0 ? psDotActive : psDotColor;
                        psHtml += '<span style="width:10px;height:10px;border-radius:50%;background:' + dotBg + ';cursor:pointer;"></span>';
                    }
                    psHtml += '</div>';
                }

                psHtml += '</div>';
                return psHtml;
            }

            // Post Content module
            case 'post_content':
                return `<div class="tb4-post-content-preview" style="padding:20px;background:#fafafa;border-left:4px solid #3b82f6;">
                    <p style="color:#6b7280;margin:0;">Post content will appear here...</p>
                </div>`;

            // Post Title module
            case 'post_title': {
                let ptTitle = data.title || 'Post Title';
                let ptTag = data.title_tag || 'h1';
                let ptColor = data.title_color || '#1f2937';
                let ptSize = data.title_font_size || '32px';
                let ptAlign = data.text_align || 'left';
                let ptShowMeta = data.show_meta !== 'no';
                let ptShowCategory = data.show_category !== 'no';
                
                let metaHtml = '';
                if (ptShowMeta) {
                    metaHtml = `<div style="display:flex;gap:16px;margin-top:12px;color:#6b7280;font-size:14px;">
                        ${ptShowCategory ? '<span style="background:#dbeafe;color:#1d4ed8;padding:4px 12px;border-radius:4px;font-size:12px;">Category</span>' : ''}
                        <span>📅 January 9, 2026</span>
                        <span>👤 Author Name</span>
                    </div>`;
                }
                
                return `<div class="tb4-post-title-preview" style="padding:20px;text-align:${ptAlign};">
                    <${ptTag} style="margin:0;color:${ptColor};font-size:${ptSize};font-weight:700;line-height:1.2;">${this.escapeHtml(ptTitle)}</${ptTag}>
                    ${metaHtml}
                </div>`;
            }

            // Post Navigation module
            case 'post_nav':
                return `<div class="tb4-post-nav-preview" style="display:flex;justify-content:space-between;padding:15px;background:#f3f4f6;border-radius:8px;">
                    <span style="color:#3b82f6;">← Previous Post</span>
                    <span style="color:#3b82f6;">Next Post →</span>
                </div>`;

            // Comments module
            case 'comments':
                return `<div class="tb4-comments-preview" style="padding:20px;background:#f9fafb;border-radius:8px;">
                    <div class="tb4-comment" style="display:flex;gap:10px;align-items:center;color:#6b7280;">
                        <span style="font-size:24px;">💬</span>
                        <span>Comments Section (${data.count || 0} comments)</span>
                    </div>
                </div>`;

            // Number Counter module
            case 'number':
                return `<div class="tb4-number-preview" style="text-align:center;padding:24px;">
                    <div class="tb4-number-value" style="font-size:56px;font-weight:bold;color:#3b82f6;">${this.escapeHtml(data.prefix || '')}${this.escapeHtml(data.number || '100')}${this.escapeHtml(data.suffix || '')}</div>
                    <div class="tb4-number-title" style="color:#6b7280;margin-top:8px;font-size:16px;">${this.escapeHtml(data.title || 'Counter')}</div>
                </div>`;

            // Circle Counter module
            case 'circle':
                return `<div class="tb4-circle-preview" style="text-align:center;padding:20px;">
                    <div class="tb4-circle-ring" style="width:100px;height:100px;border:8px solid #e5e7eb;border-top-color:#3b82f6;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:bold;">${data.percent || '75'}%</div>
                    <div class="tb4-circle-title" style="color:#6b7280;margin-top:12px;">${this.escapeHtml(data.title || 'Progress')}</div>
                </div>`;

            // Countdown module
            case 'countdown':
                return `<div class="tb4-countdown-preview" style="display:flex;justify-content:center;gap:16px;padding:20px;background:#1f2937;border-radius:8px;">
                    <div style="text-align:center;color:white;"><div style="font-size:32px;font-weight:bold;">00</div><div style="font-size:12px;">DAYS</div></div>
                    <div style="font-size:32px;color:white;">:</div>
                    <div style="text-align:center;color:white;"><div style="font-size:32px;font-weight:bold;">00</div><div style="font-size:12px;">HOURS</div></div>
                    <div style="font-size:32px;color:white;">:</div>
                    <div style="text-align:center;color:white;"><div style="font-size:32px;font-weight:bold;">00</div><div style="font-size:12px;">MINS</div></div>
                    <div style="font-size:32px;color:white;">:</div>
                    <div style="text-align:center;color:white;"><div style="font-size:32px;font-weight:bold;">00</div><div style="font-size:12px;">SECS</div></div>
                </div>`;

            // Progress Bar module
            case 'progress': {
                const progressPercent = data.percent || 50;
                return `<div class="tb4-progress-preview" style="padding:10px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:14px;color:#374151;">${this.escapeHtml(data.title || 'Progress')}</span>
                        <span style="font-size:14px;color:#6b7280;">${progressPercent}%</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                        <div style="width:${progressPercent}%;height:100%;background:#3b82f6;border-radius:4px;"></div>
                    </div>
                </div>`;
            }

            // Pricing module
            case 'pricing':
                return `<div class="tb4-pricing-preview" style="text-align:center;padding:32px;background:#fff;border:2px solid #e5e7eb;border-radius:16px;">
                    <div style="font-size:22px;font-weight:700;color:#1f2937;margin-bottom:8px;">${this.escapeHtml(data.plan_name || data.title || 'Professional')}</div>
                    ${data.description ? `<div style="font-size:14px;color:#6b7280;margin-bottom:16px;">${this.escapeHtml(data.description)}</div>` : ''}
                    <div style="font-size:52px;font-weight:bold;color:#3b82f6;margin:16px 0;"><span style="font-size:24px;vertical-align:top;">${this.escapeHtml(data.currency || '$')}</span>${this.escapeHtml(data.price || '49')}<span style="font-size:16px;font-weight:normal;color:#6b7280;">${this.escapeHtml(data.period || '/month')}</span></div>
                    <button style="padding:14px 32px;background:#3b82f6;color:white;border:none;border-radius:8px;font-weight:600;width:100%;cursor:pointer;">${this.escapeHtml(data.button_text || 'Get Started')}</button>
                </div>`;

            // Toggle module
            case 'toggle': {
                let tglTitle = data.title || 'Click to expand';
                let tglContent = data.content || 'This is the expanded content that appears when the toggle is opened.';
                let tglOpen = data.default_state === 'open' || data.default_state === 'opened';
                let tglIcon = data.icon_type === 'plus' ? (tglOpen ? '−' : '+') : (tglOpen ? '▼' : '▶');
                let tglHeaderBg = data.header_bg_color || '#f9fafb';
                let tglTitleColor = data.title_color || '#111827';
                let tglContentBg = data.content_bg_color || '#ffffff';
                let tglContentColor = data.content_color || '#374151';
                
                return `<div class="tb4-toggle-preview" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                    <div class="tb4-toggle-header" style="padding:16px;background:${tglHeaderBg};cursor:pointer;display:flex;align-items:center;gap:12px;" onclick="TB4Builder.toggleToggle(this)">
                        <span class="tb4-toggle-icon" style="transition:transform 0.2s;font-size:14px;color:#6b7280;">${tglIcon}</span>
                        <span style="font-weight:600;color:${tglTitleColor};flex:1;">${this.escapeHtml(tglTitle)}</span>
                        <span style="color:#9ca3af;font-size:12px;">Click to ${tglOpen ? 'collapse' : 'expand'}</span>
                    </div>
                    <div class="tb4-toggle-content" style="${tglOpen ? 'max-height:500px;' : 'max-height:0;'}overflow:hidden;transition:max-height 0.3s;background:${tglContentBg};">
                        <div style="padding:16px;color:${tglContentColor};line-height:1.6;">${this.escapeHtml(tglContent)}</div>
                    </div>
                </div>`;
            }

            // Contact Form module
            case 'contact':
                return `<div class="tb4-contact-preview" style="padding:32px;background:#f9fafb;border-radius:12px;">
                    ${data.form_title ? `<h3 style="margin:0 0 8px 0;font-size:24px;color:#111827;">${this.escapeHtml(data.form_title)}</h3>` : ''}
                    ${data.form_description ? `<p style="margin:0 0 20px 0;color:#6b7280;">${this.escapeHtml(data.form_description)}</p>` : ''}
                    <div style="display:flex;flex-direction:column;gap:12px;max-width:400px;">
                        <input placeholder="${this.escapeHtml(data.name_placeholder || 'Your Name')}" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;background:white;"/>
                        <input placeholder="${this.escapeHtml(data.email_placeholder || 'Your Email')}" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;background:white;"/>
                        <textarea placeholder="${this.escapeHtml(data.message_placeholder || 'Your Message')}" disabled style="padding:12px;border:1px solid #d1d5db;border-radius:6px;min-height:100px;background:white;resize:none;"></textarea>
                        <button type="button" style="padding:14px;background:#3b82f6;color:white;border:none;border-radius:6px;font-weight:600;">${this.escapeHtml(data.submit_text || 'Send Message')}</button>
                    </div>
                </div>`;

            // Slider module
            case 'slider': {
                const sliderId = 'slider_' + Math.random().toString(36).substr(2, 9);
                let slSlides = [];
                for (let si = 1; si <= 5; si++) {
                    const slTitle = data[`slide${si}_title`];
                    const slText = data[`slide${si}_text`];
                    const slImage = data[`slide${si}_image`];
                    if (slTitle || slText || slImage) {
                        slSlides.push({
                            title: slTitle || `Slide ${si}`,
                            text: slText || '',
                            image: slImage || ''
                        });
                    }
                }
                
                if (slSlides.length === 0) {
                    slSlides = [
                        { title: 'Welcome to Our Website', text: 'Create stunning websites with our powerful page builder.', image: '' },
                        { title: 'Amazing Features', text: 'Discover all the tools you need to build your dream website.', image: '' },
                        { title: 'Get Started Today', text: 'Join thousands of satisfied customers worldwide.', image: '' }
                    ];
                }

                const slBgColor = data.background_color || '#4f46e5';
                
                let slHtml = `<div class="tb4-slider-preview" id="${sliderId}" data-current="0" data-total="${slSlides.length}" style="position:relative;padding:60px 20px;background:linear-gradient(135deg,${slBgColor},${slBgColor}cc);color:white;text-align:center;border-radius:8px;overflow:hidden;">`;
                slHtml += '<div class="tb4-slider-track" style="display:flex;transition:transform 0.3s ease;">';
                slSlides.forEach((slide, idx) => {
                    slHtml += `<div class="tb4-slide" data-index="${idx}" style="min-width:100%;${idx > 0 ? 'display:none;' : ''}">`;
                    slHtml += `<h3 style="margin:0 0 10px 0;font-size:24px;">${this.escapeHtml(slide.title)}</h3>`;
                    slHtml += `<p style="margin:0;opacity:0.9;">${this.escapeHtml(slide.text)}</p>`;
                    slHtml += '</div>';
                });
                slHtml += '</div>';
                slHtml += `<div style="display:flex;justify-content:center;align-items:center;gap:20px;margin-top:20px;">`;
                slHtml += `<button onclick="TB4Builder.sliderPrev('${sliderId}')" style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);border:none;color:white;cursor:pointer;font-size:18px;">◀</button>`;
                slHtml += `<span class="tb4-slider-counter" style="font-size:14px;opacity:0.8;">Slide 1 of ${slSlides.length}</span>`;
                slHtml += `<button onclick="TB4Builder.sliderNext('${sliderId}')" style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.2);border:none;color:white;cursor:pointer;font-size:18px;">▶</button>`;
                slHtml += '</div></div>';
                return slHtml;
            }

            // Fullwidth Header module
            case 'fw_header':
                return `<div class="tb4-fw-header-preview" style="padding:80px 20px;background:linear-gradient(135deg,#1e3a5f,#2563eb);color:white;text-align:center;">
                    <h1 style="margin:0 0 16px 0;font-size:42px;font-weight:700;">${this.escapeHtml(data.title || 'Fullwidth Header')}</h1>
                    <p style="margin:0 0 24px 0;font-size:20px;opacity:0.9;max-width:600px;margin-left:auto;margin-right:auto;">${this.escapeHtml(data.subtitle || 'Subtitle text here')}</p>
                    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                        <a href="${this.escapeHtml(data.button_one_url || '#')}" style="padding:14px 28px;background:white;color:#1e3a5f;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.button_one_text || 'Get Started')}</a>
                        ${data.show_button_two !== 'no' ? `<a href="${this.escapeHtml(data.button_two_url || '#')}" style="padding:14px 28px;background:transparent;color:white;border:2px solid white;border-radius:8px;font-weight:600;text-decoration:none;">${this.escapeHtml(data.button_two_text || 'Learn More')}</a>` : ''}
                    </div>
                </div>`;

            // Fullwidth Image module
            case 'fw_image':
                if (data.image_url || data.src) {
                    return `<div class="tb4-fw-image-preview"><img src="${this.escapeHtml(data.image_url || data.src)}" alt="${this.escapeHtml(data.alt_text || '')}" style="width:100%;height:auto;display:block;"/>${data.show_caption === 'yes' && data.caption ? `<p style="text-align:center;color:#6b7280;font-size:14px;margin:12px 0 0 0;">${this.escapeHtml(data.caption)}</p>` : ''}</div>`;
                }
                return `<div class="tb4-fw-image-preview" style="padding:80px 20px;background:linear-gradient(135deg,#e5e7eb,#f3f4f6);text-align:center;border-radius:12px;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                    <p style="margin:12px 0 0 0;color:#6b7280;font-weight:500;">Fullwidth Image</p>
                </div>`;

            // Fullwidth Slider module
            case 'fw_slider': {
                const fwsSliderId = 'fws_' + Math.random().toString(36).substr(2, 9);
                let fwsHeight = data.slider_height || '500px';
                let fwsShowArrows = data.show_arrows !== 'no';
                let fwsShowDots = data.show_dots !== 'no';

                let fwsSlides = [
                    {title: 'Welcome to Our Website', subtitle: 'Discover amazing products', gradient: 'linear-gradient(135deg, #059669, #10b981)', btn: 'Get Started'},
                    {title: 'Special Summer Sale', subtitle: 'Up to 50% off selected items', gradient: 'linear-gradient(135deg, #dc2626, #f87171)', btn: 'Shop Now'},
                    {title: 'New Collection 2026', subtitle: 'Explore the latest trends', gradient: 'linear-gradient(135deg, #7c3aed, #a78bfa)', btn: 'View Collection'}
                ];

                let fwsHtml = `<div class="tb4-fw-slider-preview" id="${fwsSliderId}" data-current="0" data-total="${fwsSlides.length}" style="position:relative;width:100%;height:${fwsHeight};overflow:hidden;">`;
                
                // Slides
                fwsSlides.forEach((slide, idx) => {
                    fwsHtml += `<div class="tb4-fws-slide" data-index="${idx}" style="position:absolute;inset:0;background:${slide.gradient};display:${idx === 0 ? 'flex' : 'none'};flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px;">`;
                    fwsHtml += `<h2 style="color:white;font-size:42px;font-weight:700;margin:0 0 16px 0;">${slide.title}</h2>`;
                    fwsHtml += `<p style="color:rgba(255,255,255,0.9);font-size:20px;margin:0 0 24px 0;">${slide.subtitle}</p>`;
                    fwsHtml += `<button style="padding:14px 32px;background:white;color:#111;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;">${slide.btn}</button>`;
                    fwsHtml += '</div>';
                });

                if (fwsShowArrows) {
                    fwsHtml += '<div style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 24px;pointer-events:none;z-index:10;">';
                    fwsHtml += `<button onclick="TB4Builder.fwSliderNav('${fwsSliderId}', -1)" style="width:50px;height:50px;border-radius:50%;background:rgba(0,0,0,0.3);border:none;color:white;cursor:pointer;pointer-events:auto;font-size:24px;">◀</button>`;
                    fwsHtml += `<button onclick="TB4Builder.fwSliderNav('${fwsSliderId}', 1)" style="width:50px;height:50px;border-radius:50%;background:rgba(0,0,0,0.3);border:none;color:white;cursor:pointer;pointer-events:auto;font-size:24px;">▶</button>`;
                    fwsHtml += '</div>';
                }

                if (fwsShowDots) {
                    fwsHtml += `<div class="tb4-fws-dots" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);display:flex;gap:10px;z-index:10;">`;
                    for (let i = 0; i < fwsSlides.length; i++) {
                        let dotBg = i === 0 ? 'white' : 'rgba(255,255,255,0.5)';
                        fwsHtml += `<span onclick="TB4Builder.fwSliderGoTo('${fwsSliderId}', ${i})" style="width:10px;height:10px;border-radius:50%;background:${dotBg};cursor:pointer;"></span>`;
                    }
                    fwsHtml += '</div>';
                }

                fwsHtml += '</div>';
                return fwsHtml;
            }

            // Fullwidth Map module
            case 'fw_map':
                return `<div class="tb4-fw-map-preview" style="width:100%;height:400px;background:linear-gradient(135deg,#d1fae5,#a7f3d0);position:relative;overflow:hidden;">
                    <div style="position:absolute;inset:0;opacity:0.2;">
                        <svg width="100%" height="100%" preserveAspectRatio="none" viewBox="0 0 800 400">
                            <path d="M0,200 Q200,100 400,200 T800,200" stroke="#059669" fill="none" stroke-width="3"/>
                            <path d="M0,250 Q200,150 400,250 T800,250" stroke="#059669" fill="none" stroke-width="2"/>
                            <path d="M0,300 Q200,200 400,300 T800,300" stroke="#059669" fill="none" stroke-width="2"/>
                            <rect x="100" y="150" width="80" height="60" fill="#d1fae5" stroke="#059669" stroke-width="2"/>
                            <rect x="300" y="100" width="100" height="80" fill="#d1fae5" stroke="#059669" stroke-width="2"/>
                            <rect x="550" y="180" width="120" height="70" fill="#d1fae5" stroke="#059669" stroke-width="2"/>
                            <circle cx="200" cy="280" r="15" fill="#10b981"/>
                            <circle cx="500" cy="150" r="12" fill="#10b981"/>
                            <circle cx="700" cy="300" r="10" fill="#10b981"/>
                        </svg>
                    </div>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <div style="width:60px;height:60px;background:#dc2626;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
                            <div style="width:20px;height:20px;background:white;border-radius:50%;transform:rotate(45deg);"></div>
                        </div>
                        <div style="margin-top:16px;background:white;padding:12px 24px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.15);text-align:center;">
                            <div style="color:#111827;font-weight:600;font-size:16px;">Fullwidth Map</div>
                            <div style="color:#6b7280;font-size:13px;margin-top:4px;">${this.escapeHtml(data.address || 'Set location in settings')}</div>
                        </div>
                    </div>
                </div>`;

            // Fullwidth Menu module
            case 'fw_menu': {
                let fwmBg = data.background_color || '#ffffff';
                let fwmTextColor = data.menu_item_color || '#374151';
                let fwmHoverColor = data.menu_item_hover_color || '#2563eb';
                let fwmLogo = data.logo_text || 'YourBrand';
                let fwmLogoColor = data.logo_text_color || '#111827';
                let fwmCtaText = data.cta_text || 'Get Started';
                let fwmCtaBg = data.cta_bg_color || '#2563eb';
                let fwmShowCta = data.show_cta_button !== 'no';

                // Build menu items from menu_item1_text, menu_item2_text, etc.
                let menuItems = [];
                for (let i = 1; i <= 6; i++) {
                    let text = data['menu_item' + i + '_text'];
                    let url = data['menu_item' + i + '_url'] || '#';
                    if (text) {
                        menuItems.push({ text: text, url: url });
                    }
                }
                // Fallback if no items defined
                if (menuItems.length === 0) {
                    menuItems = [
                        { text: 'Home', url: '/' },
                        { text: 'About', url: '/about' },
                        { text: 'Services', url: '/services' },
                        { text: 'Contact', url: '/contact' }
                    ];
                }

                let fwmHtml = '<div class="tb4-fw-menu-preview" style="background:' + fwmBg + ';padding:0 40px;border-bottom:1px solid #e5e7eb;">';
                fwmHtml += '<div style="display:flex;align-items:center;justify-content:space-between;height:70px;max-width:1200px;margin:0 auto;">';
                
                // Logo
                if (data.logo_image) {
                    fwmHtml += '<a href="' + this.escapeHtml(data.logo_url || '/') + '"><img src="' + this.escapeHtml(data.logo_image) + '" alt="Logo" style="max-height:48px;"></a>';
                } else {
                    fwmHtml += '<a href="' + this.escapeHtml(data.logo_url || '/') + '" style="font-size:24px;font-weight:700;color:' + fwmLogoColor + ';text-decoration:none;">' + this.escapeHtml(fwmLogo) + '</a>';
                }
                
                // Menu items
                fwmHtml += '<nav style="display:flex;gap:32px;">';
                menuItems.forEach((item, idx) => {
                    let itemStyle = 'color:' + fwmTextColor + ';text-decoration:none;font-size:15px;font-weight:500;transition:color 0.2s;';
                    if (idx === 0) itemStyle += 'color:' + fwmHoverColor + ';';
                    fwmHtml += '<a href="' + this.escapeHtml(item.url) + '" style="' + itemStyle + '">' + this.escapeHtml(item.text) + '</a>';
                });
                fwmHtml += '</nav>';

                // CTA button
                if (fwmShowCta) {
                    fwmHtml += '<a href="' + this.escapeHtml(data.cta_url || '#') + '" style="padding:10px 20px;background:' + fwmCtaBg + ';color:white;border:none;border-radius:6px;font-weight:500;text-decoration:none;">' + this.escapeHtml(fwmCtaText) + '</a>';
                }

                fwmHtml += '</div></div>';
                return fwmHtml;
            }

            // Fullwidth Portfolio module
            case 'fw_portfolio': {
                const fwpId = 'fwp_' + Math.random().toString(36).substr(2, 9);
                let fwpCount = parseInt(data.items_count) || 8;
                let fwpCols = parseInt(data.columns) || 4;
                let fwpShowFilter = data.show_filter !== 'no';
                let fwpShowTitle = data.show_title !== 'no';
                let fwpShowCategory = data.show_category !== 'no';
                let fwpGap = data.gap || '4px';
                let fwpOverlay = data.overlay_color || 'rgba(0,0,0,0.7)';
                let fwpTitleColor = data.title_color || '#ffffff';
                let fwpCategoryColor = data.category_color || 'rgba(255,255,255,0.8)';
                let fwpFilterBg = data.filter_bg || '#f3f4f6';
                let fwpFilterActive = data.filter_active_bg || '#2563eb';

                let fwpSamples = [
                    {title: 'Brand Identity', category: 'Branding', color: '#667eea'},
                    {title: 'E-commerce Site', category: 'Web Design', color: '#f59e0b'},
                    {title: 'Mobile App', category: 'UI/UX', color: '#10b981'},
                    {title: 'Marketing Kit', category: 'Marketing', color: '#ef4444'},
                    {title: 'Photography', category: 'Photo', color: '#8b5cf6'},
                    {title: 'Video Production', category: 'Video', color: '#06b6d4'},
                    {title: 'Social Media', category: 'Branding', color: '#ec4899'},
                    {title: 'Landing Page', category: 'Web Design', color: '#84cc16'}
                ];

                let fwpHtml = `<div class="tb4-fw-portfolio-preview" id="${fwpId}" data-active-bg="${fwpFilterActive}" data-bg="${fwpFilterBg}" style="width:100%;background:#f9fafb;padding:40px 0;">`;

                if (fwpShowFilter) {
                    fwpHtml += '<div class="tb4-fwp-filter" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:24px;">';
                    fwpHtml += `<button onclick="TB4Builder.portfolioFilter(this)" class="active" style="padding:10px 24px;background:${fwpFilterActive};color:white;border:none;border-radius:6px;font-weight:500;cursor:pointer;">All</button>`;
                    ['Branding', 'Web Design', 'UI/UX'].forEach(function(cat) {
                        fwpHtml += `<button onclick="TB4Builder.portfolioFilter(this)" style="padding:10px 24px;background:${fwpFilterBg};color:#374151;border:none;border-radius:6px;font-weight:500;cursor:pointer;">${cat}</button>`;
                    });
                    fwpHtml += '</div>';
                }

                fwpHtml += '<div style="display:grid;grid-template-columns:repeat(' + fwpCols + ',1fr);gap:' + fwpGap + ';">';

                for (let i = 0; i < Math.min(fwpCount, fwpSamples.length); i++) {
                    let item = fwpSamples[i];
                    fwpHtml += '<div class="tb4-fwp-item" style="position:relative;aspect-ratio:1/1;overflow:hidden;cursor:pointer;">';
                    fwpHtml += '<div style="width:100%;height:100%;background:linear-gradient(135deg,' + item.color + ',' + item.color + 'aa);display:flex;align-items:center;justify-content:center;transition:transform 0.4s;">';
                    fwpHtml += '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                    fwpHtml += '</div>';
                    fwpHtml += '<div class="tb4-fwp-overlay" style="position:absolute;inset:0;background:' + fwpOverlay + ';display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;">';
                    fwpHtml += '<div style="width:48px;height:48px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">';
                    fwpHtml += '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>';
                    fwpHtml += '</div>';
                    if (fwpShowTitle) fwpHtml += '<h4 style="color:' + fwpTitleColor + ';font-size:16px;font-weight:600;margin:0 0 4px 0;">' + item.title + '</h4>';
                    if (fwpShowCategory) fwpHtml += '<span style="color:' + fwpCategoryColor + ';font-size:12px;text-transform:uppercase;">' + item.category + '</span>';
                    fwpHtml += '</div></div>';
                }

                fwpHtml += '</div></div>';
                fwpHtml += '<style>.tb4-fwp-item:hover .tb4-fwp-overlay{opacity:1!important}.tb4-fwp-item:hover>div:first-child{transform:scale(1.1)}</style>';
                return fwpHtml;
            }

            // Fullwidth Post Slider module
            case 'fw_post_slider': {
                const fwpsSliderId = 'fwps_' + Math.random().toString(36).substr(2, 9);
                let fwpsCount = parseInt(data.posts_count) || 5;
                let fwpsShowTitle = data.show_title !== 'no';
                let fwpsShowExcerpt = data.show_excerpt !== 'no';
                let fwpsShowDate = data.show_date !== 'no';
                let fwpsShowCategory = data.show_category !== 'no';
                let fwpsShowArrows = data.show_arrows !== 'no';
                let fwpsShowDots = data.show_dots !== 'no';
                let fwpsHeight = data.slider_height || '500px';
                let fwpsTitleColor = data.title_color || '#ffffff';
                let fwpsTitleSize = data.title_font_size || '36px';
                let fwpsExcerptColor = data.excerpt_color || 'rgba(255,255,255,0.9)';
                let fwpsCatBg = data.category_bg || '#2563eb';
                let fwpsOverlay = data.overlay_color || 'rgba(0,0,0,0.5)';

                let fwpsPosts = [
                    {title: 'The Ultimate Guide to Modern Web Design', excerpt: 'Discover the latest design patterns shaping the future.', category: 'Design', date: 'Jan 5, 2026', gradient: 'linear-gradient(135deg, #1e3a8a, #7c3aed)'},
                    {title: 'Building Scalable Cloud Applications', excerpt: 'Learn cloud-native architecture for millions of users.', category: 'Technology', date: 'Jan 4, 2026', gradient: 'linear-gradient(135deg, #065f46, #10b981)'},
                    {title: 'Marketing Strategies for 2026', excerpt: 'Stay ahead with cutting-edge marketing techniques.', category: 'Marketing', date: 'Jan 3, 2026', gradient: 'linear-gradient(135deg, #9f1239, #f43f5e)'}
                ];

                let fwpsHtml = `<div class="tb4-fw-post-slider-preview" id="${fwpsSliderId}" data-current="0" data-total="${fwpsPosts.length}" style="position:relative;width:100%;height:${fwpsHeight};overflow:hidden;">`;
                
                // Slides
                fwpsPosts.forEach((post, idx) => {
                    fwpsHtml += `<div class="tb4-fwps-slide" data-index="${idx}" style="position:absolute;inset:0;background:${post.gradient};display:${idx === 0 ? 'block' : 'none'};">`;
                    fwpsHtml += `<div style="position:absolute;inset:0;background:${fwpsOverlay};"></div>`;
                    fwpsHtml += '<div style="position:relative;z-index:2;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;">';
                    
                    if (fwpsShowCategory) {
                        fwpsHtml += `<span style="display:inline-block;background:${fwpsCatBg};color:white;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;text-transform:uppercase;margin-bottom:16px;">${post.category}</span>`;
                    }
                    if (fwpsShowTitle) {
                        fwpsHtml += `<h2 style="color:${fwpsTitleColor};font-size:${fwpsTitleSize};font-weight:700;margin:0 0 16px 0;max-width:800px;">${post.title}</h2>`;
                    }
                    if (fwpsShowExcerpt) {
                        fwpsHtml += `<p style="color:${fwpsExcerptColor};font-size:18px;margin:0 0 24px 0;max-width:600px;">${post.excerpt}</p>`;
                    }
                    if (fwpsShowDate) {
                        fwpsHtml += `<span style="color:rgba(255,255,255,0.7);font-size:14px;">${post.date}</span>`;
                    }
                    
                    fwpsHtml += '</div></div>';
                });

                // Arrows
                if (fwpsShowArrows) {
                    fwpsHtml += '<div style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 24px;pointer-events:none;z-index:10;">';
                    fwpsHtml += `<button onclick="TB4Builder.fwSliderNav('${fwpsSliderId}', -1)" style="width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);border:none;color:white;cursor:pointer;pointer-events:auto;font-size:20px;">◀</button>`;
                    fwpsHtml += `<button onclick="TB4Builder.fwSliderNav('${fwpsSliderId}', 1)" style="width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,0.2);backdrop-filter:blur(4px);border:none;color:white;cursor:pointer;pointer-events:auto;font-size:20px;">▶</button>`;
                    fwpsHtml += '</div>';
                }

                // Dots
                if (fwpsShowDots) {
                    fwpsHtml += `<div class="tb4-fwps-dots" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);display:flex;gap:10px;z-index:10;">`;
                    for (let i = 0; i < fwpsPosts.length; i++) {
                        let dotStyle = i === 0 ? 'background:white;transform:scale(1.2);' : 'background:rgba(255,255,255,0.5);';
                        fwpsHtml += `<span onclick="TB4Builder.fwSliderGoTo('${fwpsSliderId}', ${i})" style="width:10px;height:10px;border-radius:50%;${dotStyle}cursor:pointer;transition:all 0.2s;"></span>`;
                    }
                    fwpsHtml += '</div>';
                }

                fwpsHtml += '</div>';
                return fwpsHtml;
            }

            // Fullwidth Code module
            case 'fw_code':
                return `<div class="tb4-fw-code-preview" style="background:#1e1e1e;padding:20px;">
                    <pre style="margin:0;color:#d4d4d4;overflow-x:auto;"><code>${this.escapeHtml(data.code || '// Fullwidth Code Block')}</code></pre>
                </div>`;

            // Structure elements (not modules, but handle gracefully)
            case 'column':
            case 'section':
            case 'row':
                return ''; // Structure elements don't need module content

            case 'bar_counter':
                return `<div class="tb4-bar-counter-preview" style="padding:20px;">
                    <div style="margin-bottom:12px;"><span style="font-size:14px;color:#374151;">Progress 1</span><div style="height:8px;background:#e5e7eb;border-radius:4px;margin-top:4px;"><div style="width:75%;height:100%;background:#3b82f6;border-radius:4px;"></div></div></div>
                    <div style="margin-bottom:12px;"><span style="font-size:14px;color:#374151;">Progress 2</span><div style="height:8px;background:#e5e7eb;border-radius:4px;margin-top:4px;"><div style="width:50%;height:100%;background:#10b981;border-radius:4px;"></div></div></div>
                    <div><span style="font-size:14px;color:#374151;">Progress 3</span><div style="height:8px;background:#e5e7eb;border-radius:4px;margin-top:4px;"><div style="width:90%;height:100%;background:#f59e0b;border-radius:4px;"></div></div></div>
                </div>`;

            case 'bar_counter_item':
                const bciTitle = this.escapeHtml(data.title || 'Progress');
                const bciPercent = parseInt(data.percent) || 75;
                const bciBarColor = data.bar_color || '#3b82f6';
                const bciBgColor = data.background_color || '#e5e7eb';
                const bciShowPercent = data.show_percent !== 'no';
                const bciHeight = parseInt(data.bar_height) || 8;
                return `<div class="tb4-bar-counter-item-preview" style="padding:12px;background:#f9fafb;border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:14px;font-weight:500;color:#374151;">${bciTitle}</span>
                        ${bciShowPercent ? `<span style="font-size:14px;color:#6b7280;">${bciPercent}%</span>` : ''}
                    </div>
                    <div style="height:${bciHeight}px;background:${bciBgColor};border-radius:${bciHeight/2}px;overflow:hidden;">
                        <div style="width:${bciPercent}%;height:100%;background:${bciBarColor};border-radius:${bciHeight/2}px;"></div>
                    </div>
                </div>`;

            case 'filter_portfolio':
                return `<div class="tb4-filter-portfolio-preview" style="padding:20px;">
                    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                        <button style="padding:8px 16px;background:#3b82f6;color:white;border:none;border-radius:6px;font-weight:500;">All</button>
                        <button style="padding:8px 16px;background:#f3f4f6;color:#374151;border:none;border-radius:6px;">Design</button>
                        <button style="padding:8px 16px;background:#f3f4f6;color:#374151;border:none;border-radius:6px;">Development</button>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;"></div>
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#f59e0b,#fbbf24);border-radius:8px;"></div>
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#10b981,#34d399);border-radius:8px;"></div>
                    </div>
                </div>`;

            case 'shop':
                return `<div class="tb4-shop-preview" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:20px;">
                    <div style="background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#e5e7eb,#f3f4f6);"></div>
                        <div style="padding:12px;"><div style="font-weight:600;color:#111827;">Product 1</div><div style="color:#3b82f6;font-weight:700;margin-top:4px;">$49.00</div></div>
                    </div>
                    <div style="background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#e5e7eb,#f3f4f6);"></div>
                        <div style="padding:12px;"><div style="font-weight:600;color:#111827;">Product 2</div><div style="color:#3b82f6;font-weight:700;margin-top:4px;">$79.00</div></div>
                    </div>
                    <div style="background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <div style="aspect-ratio:1;background:linear-gradient(135deg,#e5e7eb,#f3f4f6);"></div>
                        <div style="padding:12px;"><div style="font-weight:600;color:#111827;">Product 3</div><div style="color:#3b82f6;font-weight:700;margin-top:4px;">$99.00</div></div>
                    </div>
                </div>`;

            case 'video_slider':
                return `<div class="tb4-video-slider-preview" style="position:relative;aspect-ratio:16/9;background:linear-gradient(135deg,#1f2937,#374151);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <div style="text-align:center;color:white;">
                        <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <div style="font-weight:500;">Video Slider</div>
                        <div style="font-size:12px;opacity:0.7;margin-top:4px;">Slide 1 of 3</div>
                    </div>
                    <button style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.2);border:none;color:white;cursor:pointer;">◀</button>
                    <button style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.2);border:none;color:white;cursor:pointer;">▶</button>
                </div>`;

            case 'video_slider_item':
                const vsiSource = data.video_source || 'youtube';
                const vsiTitle = this.escapeHtml(data.video_title || 'Video Title');
                const vsiThumb = data.thumbnail || '';
                const vsiBgStyle = vsiThumb ? `background-image:url(${vsiThumb});background-size:cover;background-position:center;` : 'background:linear-gradient(135deg,#1f2937,#374151);';
                return `<div class="tb4-video-slider-item-preview" style="position:relative;aspect-ratio:16/9;${vsiBgStyle}border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <div style="text-align:center;color:white;">
                        <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;backdrop-filter:blur(4px);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <div style="font-weight:500;text-shadow:0 1px 2px rgba(0,0,0,0.5);">${vsiTitle}</div>
                        <div style="font-size:11px;opacity:0.7;margin-top:4px;text-transform:uppercase;">${vsiSource}</div>
                    </div>
                </div>`;

            case 'contact_item':
                const ciType = data.field_type || 'text';
                const ciLabel = this.escapeHtml(data.field_label || 'Field Label');
                const ciPlaceholder = this.escapeHtml(data.placeholder || '');
                const ciRequired = data.required === 'yes' ? '<span style="color:#ef4444;margin-left:4px;">*</span>' : '';
                let ciInput = '';
                if (ciType === 'textarea') {
                    ciInput = `<textarea placeholder="${ciPlaceholder}" disabled style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;min-height:80px;background:#f9fafb;"></textarea>`;
                } else if (ciType === 'select') {
                    ciInput = `<select disabled style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;background:#f9fafb;"><option>${ciPlaceholder || 'Select...'}</option></select>`;
                } else if (ciType === 'checkbox' || ciType === 'radio') {
                    ciInput = `<label style="display:flex;align-items:center;gap:8px;color:#374151;"><input type="${ciType}" disabled/> ${ciPlaceholder || 'Option'}</label>`;
                } else {
                    ciInput = `<input type="${ciType}" placeholder="${ciPlaceholder}" disabled style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;background:#f9fafb;"/>`;
                }
                return `<div class="tb4-contact-item-preview" style="padding:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                    <label style="display:block;margin-bottom:6px;font-weight:500;color:#374151;">${ciLabel}${ciRequired}</label>
                    ${ciInput}
                </div>`;

            case 'social_item':
                const siNetwork = data.network || 'facebook';
                const siIcons = {facebook:'facebook',twitter:'twitter',instagram:'instagram',linkedin:'linkedin',youtube:'youtube',tiktok:'music-2',pinterest:'pin',github:'github',dribbble:'dribbble',behance:'pen-tool',discord:'message-circle',twitch:'twitch',telegram:'send',whatsapp:'phone'};
                const siIcon = siIcons[siNetwork] || 'link';
                const siColors = {facebook:'#1877f2',twitter:'#1da1f2',instagram:'#e4405f',linkedin:'#0a66c2',youtube:'#ff0000',tiktok:'#000000',pinterest:'#bd081c',github:'#333333',dribbble:'#ea4c89',behance:'#1769ff',discord:'#5865f2',twitch:'#9146ff',telegram:'#0088cc',whatsapp:'#25d366'};
                const siColor = siColors[siNetwork] || '#6b7280';
                return `<div class="tb4-social-item-preview" style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:50%;background:${siColor};color:white;">
                    <i data-lucide="${siIcon}" style="width:24px;height:24px;"></i>
                </div>`;

            case 'map': {
                const mapProvider = data.map_provider || 'google';
                const mapHeight = parseInt(data.map_height) || 400;
                const mapLat = data.map_center_lat || '51.5074';
                const mapLng = data.map_center_lng || '-0.1278';
                const mapZoom = parseInt(data.zoom_level) || 12;
                return `<div class="tb4-map-preview" style="position:relative;height:${Math.min(mapHeight, 300)}px;background:linear-gradient(135deg,#d1d5db,#e5e7eb);border-radius:8px;overflow:hidden;">
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#6b7280;">
                        <div style="width:48px;height:48px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                            <i data-lucide="map-pin" style="width:24px;height:24px;color:#ef4444;"></i>
                        </div>
                        <div style="font-weight:600;color:#374151;">${mapProvider.charAt(0).toUpperCase() + mapProvider.slice(1)} Map</div>
                        <div style="font-size:12px;margin-top:4px;">Lat: ${mapLat}, Lng: ${mapLng}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;">Zoom: ${mapZoom} • Parent module</div>
                    </div>
                    <div style="position:absolute;top:8px;right:8px;background:white;padding:4px 8px;border-radius:4px;font-size:10px;color:#6b7280;box-shadow:0 1px 3px rgba(0,0,0,0.1);">+ Add Pin</div>
                </div>`;
            }

            case 'map_item': {
                const miTitle = this.escapeHtml(data.pin_title || 'Location');
                const miAddress = this.escapeHtml(data.pin_address || '');
                const miLat = data.pin_lat || '51.5074';
                const miLng = data.pin_lng || '-0.1278';
                const miColor = data.pin_color || '#ef4444';
                return `<div class="tb4-map-item-preview" style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="width:36px;height:36px;border-radius:50%;background:${miColor};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="map-pin" style="width:20px;height:20px;color:white;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;color:#111827;">${miTitle}</div>
                        ${miAddress ? `<div style="font-size:13px;color:#6b7280;margin-top:2px;">${miAddress}</div>` : ''}
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Lat: ${miLat}, Lng: ${miLng}</div>
                    </div>
                </div>`;
            }

            case 'menu':
                const menuStyle = data.menu_style || 'horizontal';
                const menuAlign = data.menu_alignment || 'left';
                const alignMap = {left:'flex-start',center:'center',right:'flex-end',justified:'space-between'};
                const menuJustify = alignMap[menuAlign] || 'flex-start';
                const menuIsVert = menuStyle === 'vertical';
                const menuItems = ['Home', 'About', 'Services', 'Portfolio', 'Contact'];
                const menuItemsHtml = menuItems.map(item => `<a href="#" style="color:#374151;text-decoration:none;font-weight:500;padding:8px ${menuIsVert ? '0' : '12px'};transition:color 0.2s;">${item}</a>`).join('');
                return `<div class="tb4-menu-preview" style="padding:16px;background:#f9fafb;border-radius:8px;">
                    <nav style="display:flex;flex-direction:${menuIsVert ? 'column' : 'row'};justify-content:${menuJustify};gap:${menuIsVert ? '4px' : '8px'};flex-wrap:wrap;">
                        ${menuItemsHtml}
                    </nav>
                    <div style="margin-top:8px;font-size:11px;color:#9ca3af;text-align:center;">Menu: ${menuStyle} • ${menuAlign}</div>
                </div>`;

            case 'sidebar':
                const sbId = data.sidebar_id || 'default';
                const sbNames = {default:'Default Sidebar',blog:'Blog Sidebar',shop:'Shop Sidebar',footer_1:'Footer Area 1',footer_2:'Footer Area 2',footer_3:'Footer Area 3'};
                const sbName = sbNames[sbId] || 'Sidebar';
                const sbShowTitle = data.show_title !== 'no';
                const sbSpacing = parseInt(data.widget_spacing) || 30;
                const sbWidgets = [{title:'Search',icon:'search'},{title:'Recent Posts',icon:'file-text'},{title:'Categories',icon:'folder'}];
                const sbWidgetsHtml = sbWidgets.map(w => `<div style="background:#f3f4f6;padding:16px;border-radius:6px;">
                    ${sbShowTitle ? `<div style="font-weight:600;color:#111827;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><i data-lucide="${w.icon}" style="width:14px;height:14px;"></i>${w.title}</div>` : ''}
                    <div style="color:#9ca3af;font-size:12px;">Widget content</div>
                </div>`).join('');
                return `<div class="tb4-sidebar-preview" style="padding:16px;background:#f9fafb;border-radius:8px;">
                    <div style="font-size:11px;color:#6b7280;margin-bottom:12px;text-align:center;padding:6px;background:#e5e7eb;border-radius:4px;">${sbName}</div>
                    <div style="display:flex;flex-direction:column;gap:${Math.min(sbSpacing, 16)}px;">
                        ${sbWidgetsHtml}
                    </div>
                </div>`;

            default:
                console.warn('[TB4] Unknown module type:', moduleType);
                return `<div class="tb4-unknown-module" style="padding:20px;background:#fef2f2;border:1px dashed #fca5a5;border-radius:8px;text-align:center;color:#dc2626;">
                    <strong>Unknown module:</strong> ${moduleType}
                </div>`;
        }
    },

    /**
     * Update module from Settings Sidebar save
     * @param {Object} detail - Event detail from tb4-ss:save
     */
    updateModuleFromSidebar(detail, silent = false) {
        if (!silent) console.log('[TB4] updateModuleFromSidebar:', detail);
        const { moduleId, settings } = detail;
        
        if (!moduleId || !settings) {
            console.warn('[TB4] Invalid save data');
            return;
        }

        // Find the module in state
        const module = this.findElement(moduleId);
        if (!module) {
            console.warn('[TB4] Module not found:', moduleId);
            return;
        }

        // Update module content based on settings
        if (!module.content) module.content = {};
        
        // Map settings to content structure based on field names
        if (settings.heading !== undefined) module.content.heading = settings.heading;
        if (settings.content !== undefined) {
            module.content.text = settings.content;
            module.content.content = settings.content; // Store in both for compatibility
        }
        if (settings.text !== undefined) module.content.text = settings.text;
        if (settings.heading_level !== undefined) module.content.tag = settings.heading_level;
        if (settings.text_align !== undefined) module.content.align = settings.text_align;
        if (settings.button_text !== undefined) module.content.text = settings.button_text;
        if (settings.button_url !== undefined) module.content.url = settings.button_url;
        if (settings.image_src !== undefined) module.content.src = settings.image_src;
        if (settings.image_alt !== undefined) module.content.alt = settings.image_alt;
        if (settings.src !== undefined) module.content.src = settings.src;
        if (settings.alt !== undefined) module.content.alt = settings.alt;
        if (settings.url !== undefined) module.content.url = settings.url;

        // Store all settings for reference
        module.settings = { ...module.settings, ...settings };

        console.log('[TB4] Module updated:', module);

        // Re-render just this module on canvas
        this.rerenderModuleOnCanvas(moduleId);

        // Mark as dirty for auto-save
        this.state.isDirty = true;

        // Show toast notification (skip for live preview)
        if (!silent) this.showToast('Module updated', 'success');
    },

    /**
     * Re-render a single module on canvas
     * @param {string} moduleId - Module ID to re-render
     */
    rerenderModuleOnCanvas(moduleId) {
        const module = this.findElement(moduleId);
        if (!module) return;

        const moduleEl = this.dom.canvas?.querySelector(`[data-tb4-id="${moduleId}"]`);
        if (!moduleEl) {
            console.warn('[TB4] Module element not found on canvas:', moduleId);
            return;
        }

        // Generate new HTML
        const newHtml = this.renderModule(module);
        
        // Create temp container to parse HTML
        const temp = document.createElement('div');
        temp.innerHTML = newHtml;
        const newEl = temp.firstElementChild;

        // Preserve selection state
        if (moduleEl.classList.contains('tb4-selected')) {
            newEl.classList.add('tb4-selected');
        }

        // Replace old element with new one
        moduleEl.replaceWith(newEl);

        // Refresh Lucide icons
        this.refreshLucideIcons();

        console.log('[TB4] Module re-rendered:', moduleId);
    },

    /**
     * Render gallery content
     */
    renderGalleryContent(data) {
        const images = data.images || [];
        const columns = data.columns || 3;
        const gap = data.gap || '8px';
        
        if (images.length === 0) {
            // Show sample gallery preview
            let html = '<div class="tb4-gallery-preview" style="display:grid;grid-template-columns:repeat(' + columns + ',1fr);gap:' + gap + ';">';
            const colors = ['#667eea', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4'];
            for (let i = 0; i < 6; i++) {
                html += '<div style="aspect-ratio:1/1;background:linear-gradient(135deg,' + colors[i] + ',' + colors[i] + 'aa);border-radius:8px;display:flex;align-items:center;justify-content:center;">';
                html += '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                html += '</div>';
            }
            html += '</div>';
            return html;
        }

        let html = '<div class="tb4-gallery" style="display:grid;grid-template-columns:repeat(' + columns + ',1fr);gap:' + gap + ';">';
        images.forEach(img => {
            html += `<div class="tb4-gallery-item" style="overflow:hidden;border-radius:8px;"><img src="${this.escapeHtml(img.src)}" alt="${this.escapeHtml(img.alt || '')}" style="width:100%;height:100%;object-fit:cover;"/></div>`;
        });
        html += '</div>';
        return html;
    },

    /**
     * Render accordion content
     */
    renderAccordionContent(data) {
        // Build items from PHP format: item1_title, item1_content, item2_title, etc.
        const items = [];
        for (let i = 1; i <= 5; i++) {
            const title = data[`item${i}_title`];
            const content = data[`item${i}_content`];
            if (title || content) {
                items.push({
                    title: title || `Accordion Item ${i}`,
                    content: content || 'Content here...'
                });
            }
        }
        
        // Fallback if no items found
        if (items.length === 0) {
            items.push(
                { title: 'What is your return policy?', content: 'We offer a 30-day money-back guarantee on all purchases.' },
                { title: 'How long does shipping take?', content: 'Standard shipping takes 5-7 business days.' },
                { title: 'Do you offer support?', content: 'Yes! We offer 24/7 customer support via email and live chat.' }
            );
        }
        
        let html = '<div class="tb4-accordion" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">';
        items.forEach((item, i) => {
            const isOpen = i === 0;
            html += `
                <div class="tb4-accordion-item${isOpen ? ' open' : ''}" style="border-bottom:1px solid #e5e7eb;">
                    <div class="tb4-accordion-header" style="padding:16px;background:${isOpen ? '#e5e7eb' : '#f9fafb'};cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-weight:600;color:#111827;" onclick="TB4Builder.toggleAccordion(this)">
                        <span>${this.escapeHtml(item.title)}</span>
                        <span style="transition:transform 0.2s;${isOpen ? 'transform:rotate(180deg);' : ''}">▼</span>
                    </div>
                    <div class="tb4-accordion-content" style="padding:${isOpen ? '16px' : '0 16px'};max-height:${isOpen ? '500px' : '0'};overflow:hidden;transition:max-height 0.3s,padding 0.3s;color:#6b7280;">
                        <div style="padding:${isOpen ? '0' : '16px 0'};">${item.content}</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    },

    /**
     * Toggle accordion item
     */
    toggleAccordion(header) {
        const item = header.parentElement;
        if (!item) return;
        
        const isOpen = item.classList.contains('open');
        const content = item.querySelector('.tb4-accordion-content');
        const arrow = header.querySelector('span:last-child');
        
        if (isOpen) {
            item.classList.remove('open');
            header.style.background = '#f9fafb';
            if (content) {
                content.style.maxHeight = '0';
                content.style.padding = '0 16px';
            }
            if (arrow) arrow.style.transform = '';
        } else {
            item.classList.add('open');
            header.style.background = '#e5e7eb';
            if (content) {
                content.style.maxHeight = '500px';
                content.style.padding = '16px';
            }
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    },

    /**
     * Render tabs content
     */
    renderTabsContent(data) {
        // Build tabs from PHP format: tab1_title, tab1_content, tab2_title, etc.
        const tabs = [];
        for (let i = 1; i <= 5; i++) {
            const title = data[`tab${i}_title`];
            const content = data[`tab${i}_content`];
            if (title || content) {
                tabs.push({
                    title: title || `Tab ${i}`,
                    content: content || 'Tab content...'
                });
            }
        }
        
        // Fallback if no tabs found
        if (tabs.length === 0) {
            tabs.push({ title: 'Tab 1', content: 'Tab content...' });
        }
        
        let navHtml = '<div class="tb4-tabs-nav" style="display:flex;border-bottom:2px solid #e5e7eb;">';
        let contentHtml = '<div class="tb4-tabs-content">';

        tabs.forEach((tab, i) => {
            const active = i === 0;
            const activeStyle = active ? 'border-bottom:2px solid #2563eb;color:#2563eb;margin-bottom:-2px;' : 'color:#6b7280;';
            navHtml += `<button class="tb4-tab-btn${active ? ' active' : ''}" style="padding:12px 20px;background:none;border:none;font-weight:600;cursor:pointer;${activeStyle}" onclick="TB4Builder.switchTab(this, ${i})">${this.escapeHtml(tab.title)}</button>`;
            contentHtml += `<div class="tb4-tab-pane" style="padding:20px;color:#374151;${active ? '' : 'display:none;'}">${tab.content}</div>`;
        });

        navHtml += '</div>';
        contentHtml += '</div>';

        return `<div class="tb4-tabs" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">${navHtml}${contentHtml}</div>`;
    },

    /**
     * Render list content
     */
    renderListContent(data) {
        const items = data.items || ['List item 1', 'List item 2'];
        const type = data.type || 'ul';

        let html = `<${type} class="tb4-list">`;
        items.forEach(item => {
            html += `<li>${this.escapeHtml(item)}</li>`;
        });
        html += `</${type}>`;
        return html;
    },

    /**
     * Render video embed
     */
    renderVideoEmbed(url) {
        // YouTube
        const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
        if (ytMatch) {
            return `<iframe src="https://www.youtube.com/embed/${ytMatch[1]}" frameborder="0" allowfullscreen></iframe>`;
        }

        // Vimeo
        const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            return `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}" frameborder="0" allowfullscreen></iframe>`;
        }

        // Direct video
        return `<video src="${this.escapeHtml(url)}" controls></video>`;
    },

    // ==========================================================================
    // SIDEBAR MANAGEMENT
    // ==========================================================================

    /**
     * Initialize the sidebar
     */
    initSidebar() {
        if (!this.dom.sidebar) return;

        // Render module list
        this.renderModuleList();

        // Tab switching
        this.dom.sidebar.querySelectorAll('[data-sidebar-tab]').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchSidebarTab(tab.dataset.sidebarTab);
            });
        });
    },

    /**
     * Render the module list in sidebar
     */
    renderModuleList() {
        if (!this.dom.moduleList) {
            console.log('[TB4] renderModuleList: moduleList element not found');
            return;
        }

        // Check if modules are already hardcoded in HTML (from PHP)
        const existingModules = this.dom.moduleList.querySelectorAll('[data-module-type]');
        
        if (existingModules.length > 0) {
            console.log('[TB4] renderModuleList: Using existing hardcoded modules from PHP (' + existingModules.length + ' modules)');
        } else {
            // No hardcoded modules - generate from config
            const categories = this.categorizeModules();
            const hasConfigModules = Object.keys(categories).length > 0;

            console.log('[TB4] renderModuleList: Generating from config, hasConfigModules =', hasConfigModules);

            if (hasConfigModules) {
                let html = '';

                Object.entries(categories).forEach(([category, modules]) => {
                    html += `
                        <div class="tb4-module-category">
                            <div class="tb4-category-title">${category}</div>
                            <div class="tb4-modules-grid">
                    `;

                    modules.forEach(mod => {
                        const iconName = (mod.icon || 'box').replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
                        html += `
                            <div class="tb4-module-item"
                                 draggable="true"
                                 data-module-type="${mod.type}"
                                 title="${this.escapeHtml(mod.description || mod.name)}">
                                <i data-lucide="${iconName}" class="tb4-module-icon"></i>
                                <div class="tb4-module-name">${mod.name}</div>
                            </div>
                        `;
                    });

                    html += '</div></div>';
                });

                this.dom.moduleList.innerHTML = html;
                
                // Refresh Lucide icons with retry
                this.refreshLucideIcons();
            }
        }

        // Add drag handlers to ALL modules (dynamic or hardcoded)
        const moduleItems = this.dom.moduleList.querySelectorAll('[data-module-type]');
        console.log('[TB4] renderModuleList: Found', moduleItems.length, 'module items');

        moduleItems.forEach(item => {
            item.addEventListener('dragstart', (e) => this.handleModuleDragStart(e));
            item.addEventListener('dragend', (e) => this.handleModuleDragEnd(e));
        });
    },

    /**
     * Categorize modules for sidebar display
     */
    categorizeModules() {
        const categories = {
            'Basic': [],
            'Media': [],
            'Layout': [],
            'Advanced': []
        };

        Object.entries(this.config.modules).forEach(([slug, config]) => {
            // Skip child modules - they shouldn't appear in sidebar
            if (config.type === 'child') {
                return;
            }
            const cat = config.category || 'Basic';
            if (!categories[cat]) {
                categories[cat] = [];
            }
            // Use slug for module type, spread config but preserve slug as 'type' for drag/drop
            categories[cat].push({ ...config, type: slug });
        });

        // Remove empty categories
        Object.keys(categories).forEach(cat => {
            if (categories[cat].length === 0) {
                delete categories[cat];
            }
        });

        return categories;
    },

    /**
     * Switch sidebar tab
     */
    switchSidebarTab(tabName) {
        // Update tab buttons
        this.dom.sidebar.querySelectorAll('[data-sidebar-tab]').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.sidebarTab === tabName);
        });

        // Update tab content
        this.dom.sidebar.querySelectorAll('[data-sidebar-content]').forEach(content => {
            content.classList.toggle('active', content.dataset.sidebarContent === tabName);
        });
    },

    // ==========================================================================
    // TOOLBAR MANAGEMENT
    // ==========================================================================

    /**
     * Initialize the toolbar
     */
    initToolbar() {
        if (!this.dom.toolbar) return;

        // Device switcher
        this.dom.deviceButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.setDevice(btn.dataset.device);
            });
        });

        // Zoom controls
        const zoomIn = this.dom.toolbar.querySelector('[data-action="zoom-in"]');
        const zoomOut = this.dom.toolbar.querySelector('[data-action="zoom-out"]');
        const zoomReset = this.dom.toolbar.querySelector('[data-action="zoom-reset"]');

        if (zoomIn) zoomIn.addEventListener('click', () => this.adjustZoom(10));
        if (zoomOut) zoomOut.addEventListener('click', () => this.adjustZoom(-10));
        if (zoomReset) zoomReset.addEventListener('click', () => this.setZoom(100));

        // Undo/Redo
        const undoBtn = this.dom.toolbar.querySelector('[data-action="undo"]');
        const redoBtn = this.dom.toolbar.querySelector('[data-action="redo"]');

        if (undoBtn) undoBtn.addEventListener('click', () => this.undo());
        if (redoBtn) redoBtn.addEventListener('click', () => this.redo());

        // Save button
        const saveBtn = this.dom.toolbar.querySelector('[data-action="save"]');
        if (saveBtn) saveBtn.addEventListener('click', () => this.save());

        // Preview button
        const previewBtn = this.dom.toolbar.querySelector('[data-action="preview"]');
        if (previewBtn) previewBtn.addEventListener('click', () => this.preview());
    },

    /**
     * Set device preview mode
     */
    setDevice(device) {
        this.state.device = device;
        console.log('[TB4] setDevice called:', device);

        // Update buttons
        this.dom.deviceButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.device === device);
        });

        // Update canvas - both class and data attribute for CSS compatibility
        if (this.dom.canvas) {
            this.dom.canvas.className = `tb4-canvas tb4-device-${device}`;
            this.dom.canvas.setAttribute('data-device', device);
            console.log('[TB4] Canvas updated - class:', this.dom.canvas.className, 'data-device:', this.dom.canvas.getAttribute('data-device'));
            
            // Update column styles based on device
            this.updateColumnsForDevice(device);
        }
    },

    /**
     * Update column widths for responsive device
     * Note: CSS handles responsive via [data-device] selectors, this is just for logging
     */
    updateColumnsForDevice(device) {
        console.log('[TB4] Device changed to:', device, '- CSS will handle responsive');
    },

    /**
     * Adjust zoom level
     */
    adjustZoom(delta) {
        this.setZoom(this.state.zoom + delta);
    },

    /**
     * Set zoom level
     */
    setZoom(level) {
        this.state.zoom = Math.max(25, Math.min(200, level));

        if (this.dom.canvas) {
            this.dom.canvas.style.transform = `scale(${this.state.zoom / 100})`;
        }

        if (this.dom.zoomDisplay) {
            this.dom.zoomDisplay.textContent = `${this.state.zoom}%`;
        }
    },

    // ==========================================================================
    // SETTINGS PANEL
    // ==========================================================================

    /**
     * Initialize the settings panel
     */
    initSettingsPanel() {
        if (!this.dom.settingsPanel) return;

        // Tab switching
        this.dom.settingsPanel.querySelectorAll('[data-settings-tab]').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchSettingsTab(tab.dataset.settingsTab);
            });
        });
    },

    /**
     * Show settings for selected element
     */
    showSettings(elementId, elementType) {
        console.log('[TB4] showSettings called:', { elementId, elementType });
        
        if (!this.dom.settingsPanel) {
            console.warn('[TB4] No settings panel found');
            return;
        }

        const element = this.findElement(elementId);
        console.log('[TB4] Found element:', element);
        
        if (!element) {
            console.warn('[TB4] Element not found in state:', elementId);
            return;
        }

        this.dom.settingsPanel.classList.add('active');

        // Show settings content, hide empty state
        const settingsEmpty = document.getElementById('settingsEmpty');
        const settingsContent = document.getElementById('settingsContent');
        if (settingsEmpty) settingsEmpty.style.display = 'none';
        if (settingsContent) settingsContent.style.display = '';

        // Switch to Settings tab in sidebar
        document.querySelectorAll('[data-sidebar-tab]').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.sidebarTab === 'settings');
        });
        document.querySelectorAll('[data-sidebar-content]').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.sidebarContent === 'settings');
        });

        // Generate form based on element type
        const form = this.generateSettingsForm(element, elementType);
        console.log('[TB4] Generated form:', { contentLength: form.content?.length, designLength: form.design?.length });
        
        const contentTab = this.dom.settingsPanel.querySelector('[data-settings-content="content"]');
        const designTab = this.dom.settingsPanel.querySelector('[data-settings-content="design"]');
        const advancedTab = this.dom.settingsPanel.querySelector('[data-settings-content="advanced"]');

        if (contentTab) contentTab.innerHTML = form.content;
        if (designTab) designTab.innerHTML = form.design;
        if (advancedTab) advancedTab.innerHTML = form.advanced;

        // Reset to Content tab
        this.switchSettingsTab('content');

        // Initialize TB4Fields professional controls
        console.log('[TB4] TB4Fields available:', typeof TB4Fields !== 'undefined');
        if (typeof TB4Fields !== 'undefined') {
            console.log('[TB4] Initializing TB4Fields components for all tabs...');
            console.log('[TB4] Content tab exists:', !!contentTab);
            console.log('[TB4] Design tab exists:', !!designTab);
            console.log('[TB4] Advanced tab exists:', !!advancedTab);

            if (contentTab) {
                console.log('[TB4] --- Initializing CONTENT tab ---');
                this.initFieldComponents(contentTab);
            }
            if (designTab) {
                console.log('[TB4] --- Initializing DESIGN tab ---');
                this.initFieldComponents(designTab);
            }
            if (advancedTab) {
                console.log('[TB4] --- Initializing ADVANCED tab ---');
                this.initFieldComponents(advancedTab);
            }
            console.log('[TB4] All tabs initialized');
        } else {
            console.warn('[TB4] TB4Fields not loaded - falling back to basic controls');
        }

        // Bind form events
        this.bindSettingsEvents();

        // Initialize design/advanced panel interactions (collapsible sections, responsive inputs, etc.)
        this.initSettingsPanelInteractions();

        // Initialize advanced field instances (Animation, Typography, Custom CSS)
        this.initAdvancedFieldInstances(element, elementType);
    },

    /**
     * Initialize advanced field instances (TB4AnimationField, TB4TypographyField, TB4CustomCSSField)
     * These are complex field components that need to be instantiated after the DOM is ready
     */
    initAdvancedFieldInstances(element, elementType) {
        // Clean up any previous instances
        this.destroyAdvancedFieldInstances();

        // Store references to field instances
        this._advancedFieldInstances = {
            animation: null,
            typography: {},
            customCss: null
        };

        const self = this;

        // Initialize Animation Field
        const animationContainer = document.getElementById('tb4-animation-field-container');
        if (animationContainer && typeof TB4AnimationField !== 'undefined') {
            let currentValue = {};
            try {
                currentValue = JSON.parse(animationContainer.dataset.currentValue || '{}');
            } catch (e) {
                console.warn('[TB4] Failed to parse animation value:', e);
            }

            this._advancedFieldInstances.animation = new TB4AnimationField(animationContainer, {
                name: 'animation',
                value: currentValue,
                onChange: (value) => {
                    console.log('[TB4] Animation field changed:', value);
                    self.updateAdvancedSetting('animation', value);
                }
            });
            console.log('[TB4] Animation field initialized');
        }

        // Initialize Typography Fields (for modules with typography_fields config)
        if (elementType === 'module') {
            const moduleType = (element.type || '').replace(/^tb4_/, '');
            const moduleConfig = this.config.modules[moduleType] || {};
            const typographyFields = moduleConfig.typography_fields || [];

            typographyFields.forEach(tf => {
                const container = document.getElementById(`tb4-typography-${tf.key}-container`);
                if (container && typeof TB4TypographyField !== 'undefined') {
                    let currentValue = {};
                    try {
                        currentValue = JSON.parse(container.dataset.currentValue || '{}');
                    } catch (e) {
                        console.warn(`[TB4] Failed to parse typography value for ${tf.key}:`, e);
                    }

                    this._advancedFieldInstances.typography[tf.key] = new TB4TypographyField(container, {
                        name: `${tf.key}_typography`,
                        value: currentValue,
                        onChange: (value) => {
                            console.log(`[TB4] Typography field ${tf.key} changed:`, value);
                            self.updateDesignSetting(`${tf.key}_typography`, value);
                        }
                    });
                    console.log(`[TB4] Typography field ${tf.key} initialized`);
                }
            });
        }

        // Initialize Custom CSS Field (for modules with custom_css_fields config)
        const customCssContainer = document.getElementById('tb4-custom-css-field-container');
        if (customCssContainer && typeof TB4CustomCSSField !== 'undefined') {
            let cssTargets = [];
            let currentValue = {};
            try {
                cssTargets = JSON.parse(customCssContainer.dataset.cssTargets || '[]');
                currentValue = JSON.parse(customCssContainer.dataset.currentValue || '{}');
            } catch (e) {
                console.warn('[TB4] Failed to parse custom CSS config:', e);
            }

            this._advancedFieldInstances.customCss = new TB4CustomCSSField(customCssContainer, {
                name: 'custom_css',
                targets: cssTargets,
                value: currentValue,
                onChange: (value) => {
                    console.log('[TB4] Custom CSS field changed:', value);
                    self.updateAdvancedSetting('custom_css', value);
                }
            });
            console.log('[TB4] Custom CSS field initialized');
        }
    },

    /**
     * Clean up advanced field instances when settings panel changes
     */
    destroyAdvancedFieldInstances() {
        if (this._advancedFieldInstances) {
            // Destroy animation field
            if (this._advancedFieldInstances.animation && this._advancedFieldInstances.animation.destroy) {
                this._advancedFieldInstances.animation.destroy();
            }

            // Destroy typography fields
            Object.values(this._advancedFieldInstances.typography || {}).forEach(field => {
                if (field && field.destroy) {
                    field.destroy();
                }
            });

            // Destroy custom CSS field
            if (this._advancedFieldInstances.customCss && this._advancedFieldInstances.customCss.destroy) {
                this._advancedFieldInstances.customCss.destroy();
            }

            this._advancedFieldInstances = null;
        }
    },

    /**
     * Update advanced settings (animation, custom_css) on the selected element
     */
    updateAdvancedSetting(key, value) {
        if (!this.state.selected) return;

        const element = this.findElement(this.state.selected);
        if (!element) return;

        // Initialize advanced object if needed
        if (!element.advanced) {
            element.advanced = {};
        }

        element.advanced[key] = value;

        this.state.isDirty = true;
        // Debounce renderCanvas to prevent focus loss during typing
        clearTimeout(this._renderCanvasDebounceTimer);
        this._renderCanvasDebounceTimer = setTimeout(() => {
            this.renderCanvas();
            this.pushHistory();
        }, 300);
    },

    /**
     * Update design settings (typography) on the selected element
     */
    updateDesignSetting(key, value) {
        if (!this.state.selected) return;

        const element = this.findElement(this.state.selected);
        if (!element) return;

        // Initialize design object if needed
        if (!element.design) {
            element.design = {};
        }

        element.design[key] = value;

        this.state.isDirty = true;
        // Debounce renderCanvas to prevent focus loss during typing
        clearTimeout(this._renderCanvasDebounceTimer);
        this._renderCanvasDebounceTimer = setTimeout(() => {
            this.renderCanvas();
            this.pushHistory();
        }, 300);
    },

    /**
     * Hide settings panel
     */
    hideSettings() {
        // Clean up advanced field instances before hiding
        this.destroyAdvancedFieldInstances();

        if (this.dom.settingsPanel) {
            this.dom.settingsPanel.classList.remove('active');
        }
        // Hide settings content, show empty state
        const settingsEmpty = document.getElementById('settingsEmpty');
        const settingsContent = document.getElementById('settingsContent');
        if (settingsEmpty) settingsEmpty.style.display = '';
        if (settingsContent) settingsContent.style.display = 'none';
    },

    /**
     * Switch settings tab
     */
    switchSettingsTab(tabName) {
        if (!this.dom.settingsPanel) return;

        // Update tab buttons
        this.dom.settingsPanel.querySelectorAll('[data-settings-tab]').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.settingsTab === tabName);
        });

        // Update tab content
        this.dom.settingsPanel.querySelectorAll('[data-settings-content]').forEach(content => {
            content.classList.toggle('active', content.dataset.settingsContent === tabName);
        });
    },

    /**
     * Generate settings form for element
     */
    generateSettingsForm(element, type) {
        const form = {
            content: '',
            design: '',
            advanced: ''
        };

        // Content settings (varies by type)
        if (type === 'module') {
            form.content = this.generateModuleContentForm(element);
        } else if (type === 'section') {
            form.content = this.generateSectionContentForm(element);
        } else if (type === 'row') {
            form.content = this.generateRowContentForm(element);
        } else if (type === 'column') {
            form.content = this.generateColumnContentForm(element);
        }

        // Design settings (common)
        form.design = this.generateDesignForm(element, type);

        // Advanced settings (common)
        form.advanced = this.generateAdvancedForm(element, type);

        return form;
    },

    /**
     * Generate module content form
     */
    generateModuleContentForm(module) {
        // Normalize module type - remove tb4_ prefix if present
        const moduleType = (module.type || '').replace(/^tb4_/, '');
        const moduleConfig = this.config.modules[moduleType] || {};
        const fieldsObj = moduleConfig.fields || {};
        const content = module.content || {};

        // Convert fields object to array with name property
        const fields = Object.entries(fieldsObj).map(([name, config]) => ({
            name: name,
            ...config
        }));

        console.log('[TB4] generateModuleContentForm:', { 
            originalType: module.type, 
            normalizedType: moduleType, 
            hasConfig: !!this.config.modules[moduleType],
            fieldsCount: fields.length,
            fieldNames: fields.map(f => f.name)
        });

        if (fields.length === 0) {
            return `<div class="tb4-settings-group">
                <h4>${moduleConfig.name || moduleType} Content</h4>
                <p style="color:#6b7280;font-size:13px;">No content fields available for this module.</p>
            </div>`;
        }

        let html = `<div class="tb4-settings-group">
            <h4>${moduleConfig.name || moduleType} Content</h4>`;

        fields.forEach(field => {
            html += this.renderSettingsField(field, content[field.name]);
        });

        html += '</div>';
        return html;
    },

    /**
     * Generate section content form
     */
    generateSectionContentForm(section) {
        return `
            <div class="tb4-settings-group">
                <h4>Section Settings</h4>
                ${this.renderSettingsField({
                    name: 'containerWidth',
                    type: 'select',
                    label: 'Container Width',
                    options: [
                        { value: 'full', label: 'Full Width' },
                        { value: 'boxed', label: 'Boxed' },
                        { value: 'narrow', label: 'Narrow' }
                    ]
                }, section.settings?.containerWidth || 'boxed')}
            </div>
        `;
    },

    /**
     * Generate row content form
     */
    generateRowContentForm(row) {
        return `
            <div class="tb4-settings-group">
                <h4>Row Layout</h4>
                <div class="tb4-layout-picker">
                    ${this.renderLayoutOptions(row.layout || '1')}
                </div>
            </div>
        `;
    },

    /**
     * Generate column content form
     */
    generateColumnContentForm(column) {
        return `
            <div class="tb4-settings-group">
                <h4>Column Settings</h4>
                ${this.renderSettingsField({
                    name: 'width',
                    type: 'range',
                    label: 'Width (%)',
                    min: 10,
                    max: 100,
                    step: 5
                }, column.width || 100)}
            </div>
        `;
    },

    /**
     * Generate design form (common styles) - Expanded version
     */
    generateDesignForm(element, type) {
        const settings = element.settings || {};

        // Typography section
        const typographyContent = `
            ${this.renderSettingsField({
                name: 'fontFamily',
                type: 'select',
                label: 'Font Family',
                options: [
                    { value: '', label: 'Default' },
                    { value: 'Arial, sans-serif', label: 'Arial' },
                    { value: 'Helvetica, sans-serif', label: 'Helvetica' },
                    { value: 'Georgia, serif', label: 'Georgia' },
                    { value: 'Times New Roman, serif', label: 'Times New Roman' },
                    { value: '"Roboto", sans-serif', label: 'Roboto (Google)' },
                    { value: '"Open Sans", sans-serif', label: 'Open Sans (Google)' },
                    { value: '"Lato", sans-serif', label: 'Lato (Google)' },
                    { value: '"Montserrat", sans-serif', label: 'Montserrat (Google)' },
                    { value: '"Poppins", sans-serif', label: 'Poppins (Google)' },
                    { value: '"Playfair Display", serif', label: 'Playfair Display (Google)' },
                    { value: '"Merriweather", serif', label: 'Merriweather (Google)' },
                    { value: '"Source Sans Pro", sans-serif', label: 'Source Sans Pro (Google)' },
                    { value: '"Oswald", sans-serif', label: 'Oswald (Google)' },
                    { value: '"Raleway", sans-serif', label: 'Raleway (Google)' },
                    { value: '"Inter", sans-serif', label: 'Inter (Google)' },
                    { value: 'inherit', label: 'Inherit' }
                ]
            }, settings.fontFamily)}
            ${this.renderResponsiveInput('fontSize', 'Font Size', settings.fontSize, 'px')}
            ${this.renderSettingsField({
                name: 'fontWeight',
                type: 'select',
                label: 'Font Weight',
                options: [
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
                ]
            }, settings.fontWeight)}
            ${this.renderSettingsField({ name: 'lineHeight', type: 'text', label: 'Line Height', description: 'e.g., 1.5, 24px, 150%' }, settings.lineHeight)}
            ${this.renderSettingsField({ name: 'letterSpacing', type: 'text', label: 'Letter Spacing', description: 'e.g., 1px, 0.05em' }, settings.letterSpacing)}
            ${this.renderSettingsField({ name: 'textColor', type: 'color', label: 'Text Color' }, settings.textColor)}
            <div class="tb4-field tb4-field-buttons">
                <label class="tb4-label">Text Align</label>
                <div class="tb4-btn-group tb4-text-align-group" data-field="textAlign">
                    <button type="button" class="tb4-btn tb4-btn-sm ${settings.textAlign === 'left' ? 'active' : ''}" data-value="left" title="Left">⬅</button>
                    <button type="button" class="tb4-btn tb4-btn-sm ${settings.textAlign === 'center' ? 'active' : ''}" data-value="center" title="Center">⬌</button>
                    <button type="button" class="tb4-btn tb4-btn-sm ${settings.textAlign === 'right' ? 'active' : ''}" data-value="right" title="Right">➡</button>
                    <button type="button" class="tb4-btn tb4-btn-sm ${settings.textAlign === 'justify' ? 'active' : ''}" data-value="justify" title="Justify">☰</button>
                </div>
                <input type="hidden" name="textAlign" value="${settings.textAlign || ''}"/>
            </div>
        `;

        // Background section with tabs
        const bgType = settings.backgroundType || 'color';
        const backgroundContent = `
            <div class="tb4-tabs tb4-bg-tabs">
                <div class="tb4-tab-buttons">
                    <button type="button" class="tb4-tab-btn ${bgType === 'color' ? 'active' : ''}" data-tab="bg-color" onclick="TB4Builder.switchBgTab(this, 'bg-color')">Color</button>
                    <button type="button" class="tb4-tab-btn ${bgType === 'gradient' ? 'active' : ''}" data-tab="bg-gradient" onclick="TB4Builder.switchBgTab(this, 'bg-gradient')">Gradient</button>
                    <button type="button" class="tb4-tab-btn ${bgType === 'image' ? 'active' : ''}" data-tab="bg-image" onclick="TB4Builder.switchBgTab(this, 'bg-image')">Image</button>
                </div>
                <input type="hidden" name="backgroundType" value="${bgType}"/>
                <div class="tb4-tab-content" data-tab-content="bg-color" style="${bgType !== 'color' ? 'display:none' : ''}">
                    ${this.renderSettingsField({ name: 'backgroundColor', type: 'color', label: 'Background Color' }, settings.backgroundColor)}
                </div>
                <div class="tb4-tab-content" data-tab-content="bg-gradient" style="${bgType !== 'gradient' ? 'display:none' : ''}">
                    ${this.renderSettingsField({
                        name: 'gradientType',
                        type: 'select',
                        label: 'Gradient Type',
                        options: [
                            { value: 'linear', label: 'Linear' },
                            { value: 'radial', label: 'Radial' }
                        ]
                    }, settings.gradientType || 'linear')}
                    ${this.renderSettingsField({
                        name: 'gradientDirection',
                        type: 'select',
                        label: 'Direction',
                        options: [
                            { value: 'to bottom', label: 'Top to Bottom' },
                            { value: 'to top', label: 'Bottom to Top' },
                            { value: 'to right', label: 'Left to Right' },
                            { value: 'to left', label: 'Right to Left' },
                            { value: 'to bottom right', label: 'Top-Left to Bottom-Right' },
                            { value: 'to bottom left', label: 'Top-Right to Bottom-Left' },
                            { value: 'to top right', label: 'Bottom-Left to Top-Right' },
                            { value: 'to top left', label: 'Bottom-Right to Top-Left' }
                        ]
                    }, settings.gradientDirection || 'to bottom')}
                    ${this.renderSettingsField({ name: 'gradientStartColor', type: 'color', label: 'Start Color' }, settings.gradientStartColor || '#ffffff')}
                    ${this.renderSettingsField({ name: 'gradientEndColor', type: 'color', label: 'End Color' }, settings.gradientEndColor || '#000000')}
                </div>
                <div class="tb4-tab-content" data-tab-content="bg-image" style="${bgType !== 'image' ? 'display:none' : ''}">
                    ${this.renderSettingsField({ name: 'backgroundImage', type: 'image', label: 'Background Image' }, settings.backgroundImage)}
                    ${this.renderSettingsField({
                        name: 'backgroundSize',
                        type: 'select',
                        label: 'Size',
                        options: [
                            { value: 'cover', label: 'Cover' },
                            { value: 'contain', label: 'Contain' },
                            { value: 'auto', label: 'Auto' },
                            { value: '100% 100%', label: 'Stretch' }
                        ]
                    }, settings.backgroundSize || 'cover')}
                    ${this.renderSettingsField({
                        name: 'backgroundPosition',
                        type: 'select',
                        label: 'Position',
                        options: [
                            { value: 'center center', label: 'Center' },
                            { value: 'top center', label: 'Top' },
                            { value: 'bottom center', label: 'Bottom' },
                            { value: 'center left', label: 'Left' },
                            { value: 'center right', label: 'Right' },
                            { value: 'top left', label: 'Top Left' },
                            { value: 'top right', label: 'Top Right' },
                            { value: 'bottom left', label: 'Bottom Left' },
                            { value: 'bottom right', label: 'Bottom Right' }
                        ]
                    }, settings.backgroundPosition || 'center center')}
                    ${this.renderSettingsField({
                        name: 'backgroundRepeat',
                        type: 'select',
                        label: 'Repeat',
                        options: [
                            { value: 'no-repeat', label: 'No Repeat' },
                            { value: 'repeat', label: 'Repeat' },
                            { value: 'repeat-x', label: 'Repeat X' },
                            { value: 'repeat-y', label: 'Repeat Y' }
                        ]
                    }, settings.backgroundRepeat || 'no-repeat')}
                    ${this.renderSettingsField({ name: 'backgroundOverlayColor', type: 'color', label: 'Overlay Color' }, settings.backgroundOverlayColor)}
                    ${this.renderSettingsField({ name: 'backgroundOverlayOpacity', type: 'range', label: 'Overlay Opacity', min: 0, max: 100, step: 5 }, settings.backgroundOverlayOpacity || 0)}
                </div>
            </div>
        `;

        // Spacing section - Divi-style combined margin/padding visual editor
        const spacingContent = this.renderCombinedSpacingBox(settings);

        // Border section - Visual controls with per-side inputs
        const borderContent = this.renderBorderSettingsVisual(settings);

        // Box Shadow section - Live preview with sliders
        const shadowContent = this.renderBoxShadowSettingsVisual(settings);

        // Sizing section
        const sizingContent = `
            <div class="tb4-sizing-row">
                ${this.renderSettingsField({ name: 'width', type: 'text', label: 'Width', description: 'e.g., 100%, 500px, auto' }, settings.width)}
                ${this.renderSettingsField({
                    name: 'widthUnit',
                    type: 'select',
                    label: 'Unit',
                    options: [
                        { value: 'px', label: 'px' },
                        { value: '%', label: '%' },
                        { value: 'vw', label: 'vw' },
                        { value: 'auto', label: 'auto' }
                    ]
                }, settings.widthUnit || 'px')}
            </div>
            ${this.renderSettingsField({ name: 'maxWidth', type: 'text', label: 'Max Width', description: 'e.g., 1200px, 100%' }, settings.maxWidth)}
            <div class="tb4-sizing-row">
                ${this.renderSettingsField({ name: 'height', type: 'text', label: 'Height' }, settings.height)}
                ${this.renderSettingsField({
                    name: 'heightUnit',
                    type: 'select',
                    label: 'Unit',
                    options: [
                        { value: 'px', label: 'px' },
                        { value: '%', label: '%' },
                        { value: 'vh', label: 'vh' },
                        { value: 'auto', label: 'auto' }
                    ]
                }, settings.heightUnit || 'auto')}
            </div>
            ${this.renderSettingsField({ name: 'minHeight', type: 'text', label: 'Min Height' }, settings.minHeight)}
            ${this.renderSettingsField({ name: 'maxHeight', type: 'text', label: 'Max Height' }, settings.maxHeight)}
        `;

        // Animation section container (for TB4AnimationField integration)
        const animationSectionContent = `
            <div id="tb4-animation-field-container" class="tb4-advanced-field-container"
                 data-current-value='${JSON.stringify(element.advanced?.animation || {})}'></div>
        `;

        // Typography sections container for modules - will be populated by TB4TypographyField instances
        // Module config can define typography_fields: [{ key: 'title', label: 'Title Typography' }, ...]
        let typographyFieldsContent = '';
        if (type === 'module') {
            const moduleType = (element.type || '').replace(/^tb4_/, '');
            const moduleConfig = this.config.modules[moduleType] || {};
            const typographyFields = moduleConfig.typography_fields || [];

            if (typographyFields.length > 0) {
                typographyFieldsContent = typographyFields.map(tf => `
                    <div class="tb4-typography-field-section" data-typography-key="${tf.key}">
                        <h5 class="tb4-section-subtitle">${this.escapeHtml(tf.label || tf.key)}</h5>
                        <div id="tb4-typography-${tf.key}-container" class="tb4-advanced-field-container"
                             data-typography-key="${tf.key}"
                             data-current-value='${JSON.stringify(element.design?.[tf.key + '_typography'] || {})}'></div>
                    </div>
                `).join('');
            }
        }

        // Overlay section (::before pseudo-element)
        const overlayContent = `
            ${this.renderSettingsField({
                name: 'overlayEnabled',
                type: 'checkbox',
                label: 'Enable Overlay'
            }, settings.overlayEnabled)}
            <div class="tb4-overlay-settings ${!settings.overlayEnabled ? 'tb4-disabled' : ''}">
                ${this.renderSettingsField({ name: 'overlayColor', type: 'color', label: 'Overlay Color' }, settings.overlayColor || 'rgba(0,0,0,0.5)')}
                ${this.renderSettingsField({ name: 'overlayOpacity', type: 'range', label: 'Opacity (%)', min: 0, max: 100, step: 5 }, settings.overlayOpacity || 50)}
                ${this.renderSettingsField({
                    name: 'overlayBlendMode',
                    type: 'select',
                    label: 'Blend Mode',
                    options: [
                        { value: 'normal', label: 'Normal' },
                        { value: 'multiply', label: 'Multiply' },
                        { value: 'screen', label: 'Screen' },
                        { value: 'overlay', label: 'Overlay' },
                        { value: 'darken', label: 'Darken' },
                        { value: 'lighten', label: 'Lighten' },
                        { value: 'color-dodge', label: 'Color Dodge' },
                        { value: 'color-burn', label: 'Color Burn' },
                        { value: 'hard-light', label: 'Hard Light' },
                        { value: 'soft-light', label: 'Soft Light' },
                        { value: 'difference', label: 'Difference' },
                        { value: 'exclusion', label: 'Exclusion' },
                        { value: 'hue', label: 'Hue' },
                        { value: 'saturation', label: 'Saturation' },
                        { value: 'color', label: 'Color' },
                        { value: 'luminosity', label: 'Luminosity' }
                    ]
                }, settings.overlayBlendMode || 'normal')}
                ${this.renderSettingsField({
                    name: 'overlayGradient',
                    type: 'checkbox',
                    label: 'Use Gradient Overlay'
                }, settings.overlayGradient)}
                <div class="tb4-overlay-gradient ${!settings.overlayGradient ? 'tb4-disabled' : ''}">
                    ${this.renderSettingsField({ name: 'overlayGradientStart', type: 'color', label: 'Gradient Start' }, settings.overlayGradientStart || '#000000')}
                    ${this.renderSettingsField({ name: 'overlayGradientEnd', type: 'color', label: 'Gradient End' }, settings.overlayGradientEnd || 'transparent')}
                    ${this.renderSettingsField({
                        name: 'overlayGradientDirection',
                        type: 'select',
                        label: 'Gradient Direction',
                        options: [
                            { value: 'to bottom', label: 'Top to Bottom' },
                            { value: 'to top', label: 'Bottom to Top' },
                            { value: 'to right', label: 'Left to Right' },
                            { value: 'to left', label: 'Right to Left' },
                            { value: 'to bottom right', label: 'Diagonal ↘' },
                            { value: 'to bottom left', label: 'Diagonal ↙' }
                        ]
                    }, settings.overlayGradientDirection || 'to bottom')}
                </div>
            </div>
        `;

        // Text Shadow section
        const textShadowContent = `
            ${this.renderSettingsField({
                name: 'textShadowEnabled',
                type: 'checkbox',
                label: 'Enable Text Shadow'
            }, settings.textShadowEnabled)}
            <div class="tb4-text-shadow-settings ${!settings.textShadowEnabled ? 'tb4-disabled' : ''}">
                ${this.renderSettingsField({ name: 'textShadowHorizontal', type: 'range', label: 'Horizontal (px)', min: -50, max: 50, step: 1 }, settings.textShadowHorizontal || 2)}
                ${this.renderSettingsField({ name: 'textShadowVertical', type: 'range', label: 'Vertical (px)', min: -50, max: 50, step: 1 }, settings.textShadowVertical || 2)}
                ${this.renderSettingsField({ name: 'textShadowBlur', type: 'range', label: 'Blur (px)', min: 0, max: 50, step: 1 }, settings.textShadowBlur || 4)}
                ${this.renderSettingsField({ name: 'textShadowColor', type: 'color', label: 'Shadow Color' }, settings.textShadowColor || 'rgba(0,0,0,0.3)')}
            </div>
        `;

        // Build the design form with Animation and module-specific Typography sections
        let designForm = `
            ${this.renderCollapsibleSection('Typography', typographyContent, false)}
            ${this.renderCollapsibleSection('Background', backgroundContent, true)}
            ${this.renderCollapsibleSection('Overlay', overlayContent, false)}
            ${this.renderCollapsibleSection('Spacing', spacingContent, true)}
            ${this.renderCollapsibleSection('Border', borderContent, false)}
            ${this.renderCollapsibleSection('Box Shadow', shadowContent, false)}
            ${this.renderCollapsibleSection('Text Shadow', textShadowContent, false)}
            ${this.renderCollapsibleSection('Sizing', sizingContent, false)}
            ${this.renderCollapsibleSection('Animation', animationSectionContent, false)}
        `;

        // Add module-specific typography fields if available
        if (typographyFieldsContent) {
            designForm += this.renderCollapsibleSection('Element Typography', typographyFieldsContent, false);
        }

        return designForm;
    },

    /**
     * Generate advanced form - Expanded version
     */
    generateAdvancedForm(element, type) {
        const settings = element.settings || {};

        // Position section
        const positionContent = `
            ${this.renderSettingsField({
                name: 'position',
                type: 'select',
                label: 'Position',
                options: [
                    { value: '', label: 'Default (Static)' },
                    { value: 'relative', label: 'Relative' },
                    { value: 'absolute', label: 'Absolute' },
                    { value: 'fixed', label: 'Fixed' },
                    { value: 'sticky', label: 'Sticky' }
                ]
            }, settings.position)}
            <div class="tb4-position-offsets ${!settings.position || settings.position === 'static' ? 'tb4-disabled' : ''}">
                <div class="tb4-offset-grid">
                    ${this.renderSettingsField({ name: 'positionTop', type: 'text', label: 'Top', description: 'e.g., 10px, auto' }, settings.positionTop)}
                    ${this.renderSettingsField({ name: 'positionRight', type: 'text', label: 'Right' }, settings.positionRight)}
                    ${this.renderSettingsField({ name: 'positionBottom', type: 'text', label: 'Bottom' }, settings.positionBottom)}
                    ${this.renderSettingsField({ name: 'positionLeft', type: 'text', label: 'Left' }, settings.positionLeft)}
                </div>
                ${this.renderSettingsField({ name: 'zIndex', type: 'number', label: 'Z-Index', min: -9999, max: 9999 }, settings.zIndex)}
            </div>
        `;

        // Transform section
        const transformContent = `
            <div class="tb4-transform-grid">
                ${this.renderSettingsField({ name: 'translateX', type: 'text', label: 'Translate X', description: 'e.g., 10px, 50%' }, settings.translateX || '0')}
                ${this.renderSettingsField({ name: 'translateY', type: 'text', label: 'Translate Y' }, settings.translateY || '0')}
            </div>
            ${this.renderSettingsField({ name: 'rotate', type: 'number', label: 'Rotate (deg)', min: -360, max: 360 }, settings.rotate || 0)}
            <div class="tb4-transform-grid">
                ${this.renderSettingsField({ name: 'scaleX', type: 'number', label: 'Scale X', min: 0, max: 5, step: 0.1 }, settings.scaleX || 1)}
                ${this.renderSettingsField({ name: 'scaleY', type: 'number', label: 'Scale Y', min: 0, max: 5, step: 0.1 }, settings.scaleY || 1)}
            </div>
            <div class="tb4-transform-grid">
                ${this.renderSettingsField({ name: 'skewX', type: 'number', label: 'Skew X (deg)', min: -90, max: 90 }, settings.skewX || 0)}
                ${this.renderSettingsField({ name: 'skewY', type: 'number', label: 'Skew Y (deg)', min: -90, max: 90 }, settings.skewY || 0)}
            </div>
            ${this.renderSettingsField({
                name: 'transformOrigin',
                type: 'select',
                label: 'Transform Origin',
                options: [
                    { value: 'center center', label: 'Center' },
                    { value: 'top left', label: 'Top Left' },
                    { value: 'top center', label: 'Top Center' },
                    { value: 'top right', label: 'Top Right' },
                    { value: 'center left', label: 'Center Left' },
                    { value: 'center right', label: 'Center Right' },
                    { value: 'bottom left', label: 'Bottom Left' },
                    { value: 'bottom center', label: 'Bottom Center' },
                    { value: 'bottom right', label: 'Bottom Right' }
                ]
            }, settings.transformOrigin || 'center center')}
        `;

        // CSS Filters section
        const filtersContent = `
            ${this.renderSettingsField({ name: 'filterBlur', type: 'range', label: 'Blur (px)', min: 0, max: 20, step: 1 }, settings.filterBlur || 0)}
            ${this.renderSettingsField({ name: 'filterBrightness', type: 'range', label: 'Brightness (%)', min: 0, max: 200, step: 5 }, settings.filterBrightness || 100)}
            ${this.renderSettingsField({ name: 'filterContrast', type: 'range', label: 'Contrast (%)', min: 0, max: 200, step: 5 }, settings.filterContrast || 100)}
            ${this.renderSettingsField({ name: 'filterGrayscale', type: 'range', label: 'Grayscale (%)', min: 0, max: 100, step: 5 }, settings.filterGrayscale || 0)}
            ${this.renderSettingsField({ name: 'filterSaturate', type: 'range', label: 'Saturate (%)', min: 0, max: 200, step: 5 }, settings.filterSaturate || 100)}
            ${this.renderSettingsField({ name: 'filterHueRotate', type: 'range', label: 'Hue Rotate (deg)', min: 0, max: 360, step: 5 }, settings.filterHueRotate || 0)}
            ${this.renderSettingsField({ name: 'filterInvert', type: 'range', label: 'Invert (%)', min: 0, max: 100, step: 5 }, settings.filterInvert || 0)}
            ${this.renderSettingsField({ name: 'filterSepia', type: 'range', label: 'Sepia (%)', min: 0, max: 100, step: 5 }, settings.filterSepia || 0)}
            ${this.renderSettingsField({ name: 'opacity', type: 'range', label: 'Opacity (%)', min: 0, max: 100, step: 5 }, settings.opacity || 100)}
        `;

        // Animation section - expanded
        const animationContent = `
            ${this.renderSettingsField({
                name: 'animation',
                type: 'select',
                label: 'Animation Type',
                options: [
                    { value: '', label: 'None' },
                    { value: 'fadeIn', label: 'Fade In' },
                    { value: 'fadeInUp', label: 'Fade In Up' },
                    { value: 'fadeInDown', label: 'Fade In Down' },
                    { value: 'fadeInLeft', label: 'Fade In Left' },
                    { value: 'fadeInRight', label: 'Fade In Right' },
                    { value: 'slideUp', label: 'Slide Up' },
                    { value: 'slideDown', label: 'Slide Down' },
                    { value: 'slideLeft', label: 'Slide Left' },
                    { value: 'slideRight', label: 'Slide Right' },
                    { value: 'zoomIn', label: 'Zoom In' },
                    { value: 'zoomOut', label: 'Zoom Out' },
                    { value: 'bounce', label: 'Bounce' },
                    { value: 'pulse', label: 'Pulse' },
                    { value: 'shake', label: 'Shake' },
                    { value: 'flip', label: 'Flip' },
                    { value: 'rotateIn', label: 'Rotate In' },
                    { value: 'swing', label: 'Swing' },
                    { value: 'rubberBand', label: 'Rubber Band' },
                    { value: 'wobble', label: 'Wobble' }
                ]
            }, settings.animation)}
            ${this.renderSettingsField({ name: 'animationDuration', type: 'number', label: 'Duration (ms)', min: 0, max: 10000, step: 100 }, settings.animationDuration || 1000)}
            ${this.renderSettingsField({ name: 'animationDelay', type: 'number', label: 'Delay (ms)', min: 0, max: 5000, step: 100 }, settings.animationDelay || 0)}
            ${this.renderSettingsField({
                name: 'animationEasing',
                type: 'select',
                label: 'Easing',
                options: [
                    { value: 'ease', label: 'Ease' },
                    { value: 'linear', label: 'Linear' },
                    { value: 'ease-in', label: 'Ease In' },
                    { value: 'ease-out', label: 'Ease Out' },
                    { value: 'ease-in-out', label: 'Ease In Out' },
                    { value: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)', label: 'Bounce' },
                    { value: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)', label: 'Back' },
                    { value: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)', label: 'Smooth' }
                ]
            }, settings.animationEasing || 'ease')}
            ${this.renderSettingsField({
                name: 'animationIterations',
                type: 'select',
                label: 'Iterations',
                options: [
                    { value: '1', label: 'Once' },
                    { value: '2', label: 'Twice' },
                    { value: '3', label: '3 Times' },
                    { value: 'infinite', label: 'Infinite' }
                ]
            }, settings.animationIterations || '1')}
            <div class="tb4-field tb4-field-checkbox">
                <label class="tb4-label">
                    <input type="checkbox" name="animationOnScroll" class="tb4-checkbox" ${settings.animationOnScroll ? 'checked' : ''}/>
                    Trigger on scroll into view
                </label>
            </div>
        `;

        // CSS section - basic fields
        const cssContent = `
            ${this.renderSettingsField({ name: 'cssClass', type: 'text', label: 'CSS Classes', description: 'Space-separated class names' }, settings.cssClass)}
            ${this.renderSettingsField({ name: 'cssId', type: 'text', label: 'CSS ID', description: 'Unique identifier (no spaces)' }, settings.cssId)}
            ${this.renderSettingsField({ name: 'customCss', type: 'textarea', label: 'Custom CSS', rows: 8, description: 'Use "selector" or "&" for this element' }, settings.customCss)}
        `;

        // Custom CSS Field section (for TB4CustomCSSField integration with module-specific targets)
        // This stores data for later - the actual field initialization happens in initAdvancedFieldInstances()
        this._currentElementForAdvanced = element;
        this._currentTypeForAdvanced = type;

        // Visibility section
        const visibilityContent = `
            <div class="tb4-visibility-grid">
                <div class="tb4-field tb4-field-checkbox">
                    <label class="tb4-label">
                        <input type="checkbox" name="hideOnDesktop" class="tb4-checkbox" ${settings.hideOnDesktop ? 'checked' : ''}/>
                        <span class="tb4-visibility-icon">🖥️</span> Hide on Desktop
                    </label>
                    <p class="tb4-field-desc">≥1200px</p>
                </div>
                <div class="tb4-field tb4-field-checkbox">
                    <label class="tb4-label">
                        <input type="checkbox" name="hideOnTablet" class="tb4-checkbox" ${settings.hideOnTablet ? 'checked' : ''}/>
                        <span class="tb4-visibility-icon">📱</span> Hide on Tablet
                    </label>
                    <p class="tb4-field-desc">768px - 1199px</p>
                </div>
                <div class="tb4-field tb4-field-checkbox">
                    <label class="tb4-label">
                        <input type="checkbox" name="hideOnMobile" class="tb4-checkbox" ${settings.hideOnMobile ? 'checked' : ''}/>
                        <span class="tb4-visibility-icon">📲</span> Hide on Mobile
                    </label>
                    <p class="tb4-field-desc">&lt;768px</p>
                </div>
            </div>
            ${this.renderSettingsField({
                name: 'overflow',
                type: 'select',
                label: 'Overflow',
                options: [
                    { value: '', label: 'Default (Visible)' },
                    { value: 'hidden', label: 'Hidden' },
                    { value: 'scroll', label: 'Scroll' },
                    { value: 'auto', label: 'Auto' }
                ]
            }, settings.overflow)}
            ${this.renderSettingsField({
                name: 'cursor',
                type: 'select',
                label: 'Cursor',
                options: [
                    { value: '', label: 'Default' },
                    { value: 'pointer', label: 'Pointer' },
                    { value: 'text', label: 'Text' },
                    { value: 'move', label: 'Move' },
                    { value: 'not-allowed', label: 'Not Allowed' },
                    { value: 'grab', label: 'Grab' },
                    { value: 'crosshair', label: 'Crosshair' },
                    { value: 'zoom-in', label: 'Zoom In' },
                    { value: 'zoom-out', label: 'Zoom Out' }
                ]
            }, settings.cursor)}
        `;

        // Transition section
        const transitionContent = `
            ${this.renderSettingsField({
                name: 'transitionEnabled',
                type: 'checkbox',
                label: 'Enable Transitions'
            }, settings.transitionEnabled)}
            <div class="tb4-transition-settings ${!settings.transitionEnabled ? 'tb4-disabled' : ''}">
                ${this.renderSettingsField({
                    name: 'transitionProperty',
                    type: 'select',
                    label: 'Property',
                    options: [
                        { value: 'all', label: 'All Properties' },
                        { value: 'transform', label: 'Transform' },
                        { value: 'opacity', label: 'Opacity' },
                        { value: 'background', label: 'Background' },
                        { value: 'color', label: 'Color' },
                        { value: 'border', label: 'Border' },
                        { value: 'box-shadow', label: 'Box Shadow' },
                        { value: 'filter', label: 'Filter' }
                    ]
                }, settings.transitionProperty || 'all')}
                ${this.renderSettingsField({ name: 'transitionDuration', type: 'range', label: 'Duration (ms)', min: 0, max: 2000, step: 50 }, settings.transitionDuration || 300)}
                ${this.renderSettingsField({
                    name: 'transitionTiming',
                    type: 'select',
                    label: 'Timing Function',
                    options: [
                        { value: 'ease', label: 'Ease' },
                        { value: 'linear', label: 'Linear' },
                        { value: 'ease-in', label: 'Ease In' },
                        { value: 'ease-out', label: 'Ease Out' },
                        { value: 'ease-in-out', label: 'Ease In Out' },
                        { value: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)', label: 'Bounce' },
                        { value: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)', label: 'Back' }
                    ]
                }, settings.transitionTiming || 'ease')}
                ${this.renderSettingsField({ name: 'transitionDelay', type: 'range', label: 'Delay (ms)', min: 0, max: 1000, step: 50 }, settings.transitionDelay || 0)}
            </div>
        `;

        // Motion section (scroll-based effects)
        const motionContent = `
            ${this.renderSettingsField({
                name: 'motionEnabled',
                type: 'checkbox',
                label: 'Enable Scroll Effects'
            }, settings.motionEnabled)}
            <div class="tb4-motion-settings ${!settings.motionEnabled ? 'tb4-disabled' : ''}">
                ${this.renderSettingsField({
                    name: 'motionType',
                    type: 'select',
                    label: 'Effect Type',
                    options: [
                        { value: 'parallax', label: 'Parallax' },
                        { value: 'fade', label: 'Fade on Scroll' },
                        { value: 'scale', label: 'Scale on Scroll' },
                        { value: 'rotate', label: 'Rotate on Scroll' },
                        { value: 'blur', label: 'Blur on Scroll' }
                    ]
                }, settings.motionType || 'parallax')}
                ${this.renderSettingsField({ name: 'motionSpeed', type: 'range', label: 'Speed', min: -100, max: 100, step: 5 }, settings.motionSpeed || 50)}
                ${this.renderSettingsField({
                    name: 'motionDirection',
                    type: 'select',
                    label: 'Direction',
                    options: [
                        { value: 'vertical', label: 'Vertical' },
                        { value: 'horizontal', label: 'Horizontal' },
                        { value: 'both', label: 'Both' }
                    ]
                }, settings.motionDirection || 'vertical')}
                ${this.renderSettingsField({ name: 'motionStartOffset', type: 'range', label: 'Start Offset (%)', min: 0, max: 100, step: 5 }, settings.motionStartOffset || 0)}
                ${this.renderSettingsField({ name: 'motionEndOffset', type: 'range', label: 'End Offset (%)', min: 0, max: 100, step: 5 }, settings.motionEndOffset || 100)}
            </div>
        `;

        // Custom CSS container for modules (TB4CustomCSSField integration)
        let customCssFieldContent = '';
        if (type === 'module') {
            const moduleType = (element.type || '').replace(/^tb4_/, '');
            const moduleConfig = this.config.modules[moduleType] || {};
            const cssTargets = moduleConfig.custom_css_fields || [
                { key: 'wrapper', label: 'Module Wrapper', selector: '.tb4-module' },
                { key: 'content', label: 'Module Content', selector: '.tb4-module-content' }
            ];

            customCssFieldContent = `
                <div id="tb4-custom-css-field-container" class="tb4-advanced-field-container"
                     data-css-targets='${JSON.stringify(cssTargets)}'
                     data-current-value='${JSON.stringify(element.advanced?.custom_css || {})}'></div>
            `;
        }

        // Build the advanced form
        let advancedForm = `
            ${this.renderCollapsibleSection('Position', positionContent, false)}
            ${this.renderCollapsibleSection('Transform', transformContent, false)}
            ${this.renderCollapsibleSection('Transition', transitionContent, false)}
            ${this.renderCollapsibleSection('Motion', motionContent, false)}
            ${this.renderCollapsibleSection('CSS Filters', filtersContent, false)}
            ${this.renderCollapsibleSection('Animation', animationContent, true)}
            ${this.renderCollapsibleSection('CSS', cssContent, true)}
        `;

        // Add Custom CSS Field section for modules
        if (customCssFieldContent) {
            advancedForm += this.renderCollapsibleSection('Custom CSS', customCssFieldContent, false);
        }

        advancedForm += this.renderCollapsibleSection('Visibility', visibilityContent, false);

        return advancedForm;
    },

    /**
     * Render a settings field using TB4Fields professional controls
     * @param {Object} field - Field configuration
     * @param {*} value - Current field value
     * @returns {string} HTML string for the field
     */
    renderSettingsField(field, value) {
        const id = `tb4-field-${field.name}`;
        const escapedValue = value !== undefined ? this.escapeHtml(String(value)) : '';

        // Use TB4Fields professional controls if available
        if (typeof TB4Fields !== 'undefined') {
            switch (field.type) {
                case 'color':
                    return TB4Fields.renderColorPicker(field.name, value, field);

                case 'range':
                    return TB4Fields.renderRangeSlider(field.name, value, field);

                case 'number':
                    // Use range slider for number fields with defined min/max
                    return TB4Fields.renderRangeSlider(field.name, value, {
                        ...field,
                        showInput: true  // Show numeric input alongside slider
                    });

                case 'toggle':
                    return TB4Fields.renderToggle(field.name, value, field);

                case 'checkbox':
                    // Use toggle switch for checkboxes
                    return TB4Fields.renderToggle(field.name, value, field);

                case 'select':
                    return TB4Fields.renderSelect(field.name, value, field);

                case 'button-group':
                case 'buttongroup':
                    return TB4Fields.renderButtonGroup(field.name, value, field);

                case 'textAlign':
                case 'text-align':
                    return TB4Fields.renderTextAlign(field.name, value, field);

                case 'spacing':
                    return TB4Fields.renderSpacingBox(field.name, value, field);
            }
        }

        // Fallback to original implementation for non-supported types
        let input = '';

        switch (field.type) {
            case 'text':
                input = `<input type="text" id="${id}" name="${field.name}" value="${escapedValue}" class="tb4-input"/>`;
                break;

            case 'textarea':
                input = `<textarea id="${id}" name="${field.name}" class="tb4-textarea" rows="${field.rows || 3}">${escapedValue}</textarea>`;
                break;

            case 'number':
                input = `<input type="number" id="${id}" name="${field.name}" value="${escapedValue}"
                         class="tb4-input" min="${field.min || 0}" max="${field.max || 9999}" step="${field.step || 1}"/>`;
                break;

            case 'range':
                input = `<input type="range" id="${id}" name="${field.name}" value="${value || field.min || 0}"
                         class="tb4-range" min="${field.min || 0}" max="${field.max || 100}" step="${field.step || 1}"/>
                         <span class="tb4-range-value">${value || field.min || 0}</span>`;
                break;

            case 'select':
                let optionsHtml = '';
                const rawOptions = field.options || {};

                // Handle both array and object formats
                if (Array.isArray(rawOptions)) {
                    optionsHtml = rawOptions.map(opt =>
                        `<option value="${this.escapeHtml(opt.value)}" ${opt.value === value ? 'selected' : ''}>${this.escapeHtml(opt.label)}</option>`
                    ).join('');
                } else if (typeof rawOptions === 'object') {
                    optionsHtml = Object.entries(rawOptions).map(([optValue, optLabel]) =>
                        `<option value="${this.escapeHtml(optValue)}" ${optValue === value ? 'selected' : ''}>${this.escapeHtml(optLabel)}</option>`
                    ).join('');
                }

                input = `<select id="${id}" name="${field.name}" class="tb4-select">${optionsHtml}</select>`;
                break;

            case 'checkbox':
                input = `<input type="checkbox" id="${id}" name="${field.name}" class="tb4-checkbox" ${value ? 'checked' : ''}/>`;
                break;

            case 'color':
                input = `<input type="color" id="${id}" name="${field.name}" value="${escapedValue || '#000000'}" class="tb4-color"/>
                         <input type="text" name="${field.name}_text" value="${escapedValue}" class="tb4-input tb4-color-text" placeholder="#000000"/>`;
                break;

            case 'upload':
            case 'image':
                input = `<div class="tb4-image-picker">
                    <input type="hidden" id="${id}" name="${field.name}" value="${escapedValue}"/>
                    <div class="tb4-image-preview">${value ? `<img src="${this.escapeHtml(escapedValue)}" style="max-width:100%;height:auto;border-radius:8px;"/>` : '<span style="color:#6b7280;font-size:13px;">No image selected</span>'}</div>
                    <div class="tb4-image-buttons" style="display:flex;gap:8px;margin-top:8px;">
                        <button type="button" class="tb4-btn tb4-btn-sm tb4-btn-primary" onclick="TB4Builder.openMediaPicker('${field.name}')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                            Choose Image
                        </button>
                        <button type="button" class="tb4-btn tb4-btn-sm tb4-btn-danger" onclick="TB4Builder.clearImage('${field.name}')" style="${value ? '' : 'display:none;'}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            Remove
                        </button>
                    </div>
                </div>`;
                break;

            case 'wysiwyg':
                input = `<div class="tb4-wysiwyg" id="${id}" name="${field.name}" contenteditable="true">${value || ''}</div>`;
                break;

            case 'icon':
                const iconPath = value ? this.getIconSvgPath(value) : '';
                input = `<div class="tb4-icon-picker">
                    <div class="tb4-icon-picker-row" style="display:flex;gap:8px;align-items:center;">
                        <div class="tb4-icon-preview" style="width:40px;height:40px;border:1px solid var(--tb4-border, #3f3f46);border-radius:6px;display:flex;align-items:center;justify-content:center;background:var(--tb4-bg-darker, #18181b);">
                            ${iconPath ? `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${iconPath}</svg>` : '<span style="color:#6b7280;font-size:10px;">Icon</span>'}
                        </div>
                        <input type="text" id="${id}" name="${field.name}" value="${escapedValue}" class="tb4-input" placeholder="e.g., star, heart, check" style="flex:1;"/>
                        <button type="button" class="tb4-btn tb4-btn-sm" onclick="TB4Builder.openIconPicker('${field.name}')">Browse</button>
                    </div>
                    <p class="tb4-field-desc" style="margin-top:4px;font-size:11px;color:#6b7280;">Enter Lucide icon name or click Browse</p>
                </div>`;
                break;

            default:
                input = `<input type="text" id="${id}" name="${field.name}" value="${escapedValue}" class="tb4-input"/>`;
        }

        return `
            <div class="tb4-field tb4-field-${field.type}">
                <label for="${id}" class="tb4-label">${field.label || field.name}</label>
                ${input}
                ${field.description ? `<p class="tb4-field-desc">${field.description}</p>` : ''}
            </div>
        `;
    },

    /**
     * Initialize TB4Fields components after rendering settings panel
     * Call this method after inserting settings HTML into the DOM
     * @param {HTMLElement} container - The settings panel container
     */
    initFieldComponents(container) {
        if (typeof TB4Fields === 'undefined') {
            console.warn('[TB4] TB4Fields not available - using fallback controls');
            return;
        }

        const fieldElements = container.querySelectorAll('[data-tb4-field]');
        console.log('[TB4] initFieldComponents: Found', fieldElements.length, 'field elements to initialize');

        fieldElements.forEach((el, index) => {
            const fieldType = el.dataset.tb4Field;
            const fieldName = el.dataset.fieldName || 'unknown';
            try {
                TB4Fields.init(el);
                console.log('[TB4] Initialized field', index + 1, ':', fieldType, '(' + fieldName + ')');
            } catch (error) {
                console.error('[TB4] Failed to initialize field', fieldName, ':', error);
            }
        });

        // Set up change handlers to sync with builder state
        const self = this;
        fieldElements.forEach(fieldEl => {
            const instance = TB4Fields.getInstance(fieldEl);
            if (instance) {
                const fieldName = fieldEl.dataset.fieldName;
                const originalOnChange = instance.onChange;
                instance.onChange = (value) => {
                    if (originalOnChange) originalOnChange(value);
                    if (self.selectedElement && fieldName) {
                        console.log('[TB4] Field changed:', fieldName, '=', value);
                        self.updateElementSetting(fieldName, value);
                    }
                };
            }
        });

        console.log('[TB4] initFieldComponents complete');
    },

    /**
     * Render spacing control (padding/margin)
     */
    renderSpacingControl(name, value) {
        const v = value || { top: 0, right: 0, bottom: 0, left: 0 };

        return `
            <div class="tb4-spacing-control" data-spacing="${name}">
                <label class="tb4-label">${name.charAt(0).toUpperCase() + name.slice(1)}</label>
                <div class="tb4-spacing-inputs">
                    <input type="number" name="${name}Top" value="${v.top || 0}" class="tb4-spacing-input" placeholder="Top"/>
                    <input type="number" name="${name}Right" value="${v.right || 0}" class="tb4-spacing-input" placeholder="Right"/>
                    <input type="number" name="${name}Bottom" value="${v.bottom || 0}" class="tb4-spacing-input" placeholder="Bottom"/>
                    <input type="number" name="${name}Left" value="${v.left || 0}" class="tb4-spacing-input" placeholder="Left"/>
                    <button type="button" class="tb4-btn tb4-btn-sm tb4-spacing-link" title="Link values">🔗</button>
                </div>
            </div>
        `;
    },

    /**
     * Render a collapsible section (accordion style)
     * @param {string} title - Section header title
     * @param {string} content - HTML content for the section body
     * @param {boolean} defaultOpen - Whether section is expanded by default
     * @returns {string} HTML for collapsible section
     */
    renderCollapsibleSection(title, content, defaultOpen = false) {
        const uniqueId = 'tb4-section-' + title.toLowerCase().replace(/[^a-z0-9]/g, '-') + '-' + Math.random().toString(36).substr(2, 9);

        return `
            <div class="tb4-collapsible-section ${defaultOpen ? 'tb4-section-open' : ''}" data-section-id="${uniqueId}">
                <div class="tb4-section-header" onclick="TB4Builder.toggleSection('${uniqueId}')">
                    <span class="tb4-section-icon">${defaultOpen ? '▼' : '▶'}</span>
                    <span class="tb4-section-title">${title}</span>
                </div>
                <div class="tb4-section-body" style="${defaultOpen ? '' : 'display: none;'}">
                    ${content}
                </div>
            </div>
        `;
    },

    /**
     * Toggle collapsible section visibility
     * @param {string} sectionId - The section identifier
     */
    toggleSection(sectionId) {
        const section = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (!section) return;

        const body = section.querySelector('.tb4-section-body');
        const icon = section.querySelector('.tb4-section-icon');
        const isOpen = section.classList.contains('tb4-section-open');

        if (isOpen) {
            section.classList.remove('tb4-section-open');
            body.style.display = 'none';
            icon.textContent = '▶';
        } else {
            section.classList.add('tb4-section-open');
            body.style.display = '';
            icon.textContent = '▼';
        }
    },

    /**
     * Render responsive input with desktop/tablet/mobile controls
     * @param {string} name - Field name
     * @param {string} label - Display label
     * @param {object|string} value - Current value(s)
     * @param {string} unit - CSS unit (px, %, em, etc.)
     * @returns {string} HTML for responsive input
     */
    renderResponsiveInput(name, label, value, unit = 'px') {
        // Normalize value to object format
        let values = { desktop: '', tablet: '', mobile: '' };
        if (typeof value === 'object' && value !== null) {
            values = { ...values, ...value };
        } else if (value !== undefined && value !== null) {
            values.desktop = value;
        }

        return `
            <div class="tb4-field tb4-field-responsive" data-responsive-field="${name}">
                <div class="tb4-field-header">
                    <label class="tb4-label">${label}</label>
                    <div class="tb4-responsive-toggle">
                        <button type="button" class="tb4-responsive-btn active" data-device="desktop" title="Desktop">🖥️</button>
                        <button type="button" class="tb4-responsive-btn" data-device="tablet" title="Tablet">📱</button>
                        <button type="button" class="tb4-responsive-btn" data-device="mobile" title="Mobile">📲</button>
                    </div>
                </div>
                <div class="tb4-responsive-inputs">
                    <div class="tb4-responsive-input active" data-device="desktop">
                        <input type="text" name="${name}Desktop" value="${this.escapeHtml(String(values.desktop || ''))}"
                               class="tb4-input" placeholder="Desktop"/>
                        <span class="tb4-unit">${unit}</span>
                    </div>
                    <div class="tb4-responsive-input" data-device="tablet" style="display: none;">
                        <input type="text" name="${name}Tablet" value="${this.escapeHtml(String(values.tablet || ''))}"
                               class="tb4-input" placeholder="Tablet (inherits desktop)"/>
                        <span class="tb4-unit">${unit}</span>
                    </div>
                    <div class="tb4-responsive-input" data-device="mobile" style="display: none;">
                        <input type="text" name="${name}Mobile" value="${this.escapeHtml(String(values.mobile || ''))}"
                               class="tb4-input" placeholder="Mobile (inherits tablet)"/>
                        <span class="tb4-unit">${unit}</span>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Render linked spacing control with per-side inputs and link toggle
     * @param {string} name - Base field name (e.g., 'padding', 'margin', 'borderRadius')
     * @param {string} label - Display label
     * @param {object} values - Current values { top, right, bottom, left } or { topLeft, topRight, etc. for corners }
     * @param {string} unit - CSS unit
     * @param {number} min - Minimum value
     * @param {number} max - Maximum value
     * @returns {string} HTML for linked spacing control
     */
    renderLinkedSpacingControl(name, label, values, unit = 'px', min = 0, max = 999) {
        // Determine if this is for corners (borderRadius) or sides
        const isCorners = name.toLowerCase().includes('radius');

        let v;
        if (isCorners) {
            v = values || { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 };
            // Also support simpler format
            if (v.top !== undefined) {
                v = { topLeft: v.top || 0, topRight: v.right || 0, bottomRight: v.bottom || 0, bottomLeft: v.left || 0 };
            }
        } else {
            v = values || { top: 0, right: 0, bottom: 0, left: 0 };
        }

        // Determine if all values are the same (linked)
        const allValues = isCorners
            ? [v.topLeft, v.topRight, v.bottomRight, v.bottomLeft]
            : [v.top, v.right, v.bottom, v.left];
        const isLinked = allValues.every(val => val === allValues[0]);

        if (isCorners) {
            return `
                <div class="tb4-linked-spacing-control tb4-corner-control ${isLinked ? 'tb4-linked' : ''}" data-linked-spacing="${name}">
                    <div class="tb4-linked-header">
                        <label class="tb4-label">${label}</label>
                        <button type="button" class="tb4-btn tb4-btn-sm tb4-link-toggle ${isLinked ? 'active' : ''}"
                                onclick="TB4Builder.toggleLinkedSpacing('${name}')" title="Link all values">
                            🔗
                        </button>
                    </div>
                    <div class="tb4-corner-grid">
                        <div class="tb4-corner-input tb4-corner-tl">
                            <input type="number" name="${name}TopLeft" value="${v.topLeft || 0}"
                                   class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="TL"/>
                            <span class="tb4-corner-label">↖</span>
                        </div>
                        <div class="tb4-corner-input tb4-corner-tr">
                            <input type="number" name="${name}TopRight" value="${v.topRight || 0}"
                                   class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="TR"/>
                            <span class="tb4-corner-label">↗</span>
                        </div>
                        <div class="tb4-corner-input tb4-corner-bl">
                            <input type="number" name="${name}BottomLeft" value="${v.bottomLeft || 0}"
                                   class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="BL"/>
                            <span class="tb4-corner-label">↙</span>
                        </div>
                        <div class="tb4-corner-input tb4-corner-br">
                            <input type="number" name="${name}BottomRight" value="${v.bottomRight || 0}"
                                   class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="BR"/>
                            <span class="tb4-corner-label">↘</span>
                        </div>
                    </div>
                    <span class="tb4-unit-label">${unit}</span>
                </div>
            `;
        }

        return `
            <div class="tb4-linked-spacing-control ${isLinked ? 'tb4-linked' : ''}" data-linked-spacing="${name}">
                <div class="tb4-linked-header">
                    <label class="tb4-label">${label}</label>
                    <button type="button" class="tb4-btn tb4-btn-sm tb4-link-toggle ${isLinked ? 'active' : ''}"
                            onclick="TB4Builder.toggleLinkedSpacing('${name}')" title="Link all values">
                        🔗
                    </button>
                </div>
                <div class="tb4-spacing-grid">
                    <div class="tb4-spacing-row tb4-spacing-top">
                        <input type="number" name="${name}Top" value="${v.top || 0}"
                               class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="Top"/>
                    </div>
                    <div class="tb4-spacing-row tb4-spacing-middle">
                        <input type="number" name="${name}Left" value="${v.left || 0}"
                               class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="Left"/>
                        <div class="tb4-spacing-preview">
                            <div class="tb4-spacing-box"></div>
                        </div>
                        <input type="number" name="${name}Right" value="${v.right || 0}"
                               class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="Right"/>
                    </div>
                    <div class="tb4-spacing-row tb4-spacing-bottom">
                        <input type="number" name="${name}Bottom" value="${v.bottom || 0}"
                               class="tb4-input tb4-spacing-input" min="${min}" max="${max}" placeholder="Bottom"/>
                    </div>
                </div>
                <span class="tb4-unit-label">${unit}</span>
            </div>
        `;
    },

    /**
     * Toggle linked spacing mode - when linked, all values sync
     * @param {string} name - The control identifier
     */
    toggleLinkedSpacing(name) {
        const control = document.querySelector(`[data-linked-spacing="${name}"]`);
        if (!control) return;

        const isLinked = control.classList.contains('tb4-linked');
        const button = control.querySelector('.tb4-link-toggle');
        const inputs = control.querySelectorAll('.tb4-spacing-input');

        if (isLinked) {
            // Unlink
            control.classList.remove('tb4-linked');
            button.classList.remove('active');
            inputs.forEach(input => {
                input.removeEventListener('input', this._linkedInputHandler);
            });
        } else {
            // Link - sync all values to the first input's value
            control.classList.add('tb4-linked');
            button.classList.add('active');
            const firstValue = inputs[0].value;
            inputs.forEach(input => {
                input.value = firstValue;
            });

            // Store handler reference for removal later
            this._linkedInputHandler = (e) => {
                inputs.forEach(input => {
                    input.value = e.target.value;
                });
                this.handleSettingChange(e);
            };

            inputs.forEach(input => {
                input.addEventListener('input', this._linkedInputHandler);
            });
        }
    },

    /**
     * Render Combined Spacing Box (Divi-style margin/padding visual editor)
     * Creates nested boxes: orange outer = margin, green inner = padding
     * @param {object} settings - Current element settings with margin/padding values
     * @returns {string} HTML for combined spacing box
     */
    renderCombinedSpacingBox(settings) {
        const margin = settings.margin || {};
        const padding = settings.padding || {};

        // Get values with defaults (parse to numbers for sliders)
        const mt = parseInt(margin.top) || 0;
        const mr = parseInt(margin.right) || 0;
        const mb = parseInt(margin.bottom) || 0;
        const ml = parseInt(margin.left) || 0;
        const pt = parseInt(padding.top) || 0;
        const pr = parseInt(padding.right) || 0;
        const pb = parseInt(padding.bottom) || 0;
        const pl = parseInt(padding.left) || 0;

        // Check if values are linked
        const marginLinked = mt === mr && mr === mb && mb === ml;
        const paddingLinked = pt === pr && pr === pb && pb === pl;

        return `
            <div class="tb4-spacing-box-container" data-spacing-control>
                <!-- Visual Box -->
                <div class="tb4-spacing-visual-box">
                    <div class="tb4-spacing-box-outer">
                        <div class="tb4-spacing-label-margin">MARGIN</div>
                        <button type="button" class="tb4-spacing-link-btn tb4-spacing-link-margin ${marginLinked ? 'linked' : ''}"
                                onclick="TB4Builder.toggleSpacingLink('margin')" title="Link margin values">🔗</button>

                        <input type="text" class="tb4-spacing-box-input tb4-margin-top-display"
                               name="marginTop" value="${mt}" placeholder="0"
                               data-spacing-type="margin" data-spacing-side="top"
                               oninput="TB4Builder.updateSpacingFromBox(this)">
                        <input type="text" class="tb4-spacing-box-input tb4-margin-right-display"
                               name="marginRight" value="${mr}" placeholder="0"
                               data-spacing-type="margin" data-spacing-side="right"
                               oninput="TB4Builder.updateSpacingFromBox(this)">
                        <input type="text" class="tb4-spacing-box-input tb4-margin-bottom-display"
                               name="marginBottom" value="${mb}" placeholder="0"
                               data-spacing-type="margin" data-spacing-side="bottom"
                               oninput="TB4Builder.updateSpacingFromBox(this)">
                        <input type="text" class="tb4-spacing-box-input tb4-margin-left-display"
                               name="marginLeft" value="${ml}" placeholder="0"
                               data-spacing-type="margin" data-spacing-side="left"
                               oninput="TB4Builder.updateSpacingFromBox(this)">

                        <div class="tb4-spacing-box-inner">
                            <div class="tb4-spacing-label-padding">PADDING</div>
                            <button type="button" class="tb4-spacing-link-btn tb4-spacing-link-padding ${paddingLinked ? 'linked' : ''}"
                                    onclick="TB4Builder.toggleSpacingLink('padding')" title="Link padding values">🔗</button>

                            <input type="text" class="tb4-spacing-box-input tb4-padding-top-display"
                                   name="paddingTop" value="${pt}" placeholder="0"
                                   data-spacing-type="padding" data-spacing-side="top"
                                   oninput="TB4Builder.updateSpacingFromBox(this)">
                            <input type="text" class="tb4-spacing-box-input tb4-padding-right-display"
                                   name="paddingRight" value="${pr}" placeholder="0"
                                   data-spacing-type="padding" data-spacing-side="right"
                                   oninput="TB4Builder.updateSpacingFromBox(this)">
                            <input type="text" class="tb4-spacing-box-input tb4-padding-bottom-display"
                                   name="paddingBottom" value="${pb}" placeholder="0"
                                   data-spacing-type="padding" data-spacing-side="bottom"
                                   oninput="TB4Builder.updateSpacingFromBox(this)">
                            <input type="text" class="tb4-spacing-box-input tb4-padding-left-display"
                                   name="paddingLeft" value="${pl}" placeholder="0"
                                   data-spacing-type="padding" data-spacing-side="left"
                                   oninput="TB4Builder.updateSpacingFromBox(this)">

                            <div class="tb4-spacing-content-center">CONTENT</div>
                        </div>
                    </div>
                </div>

                <!-- Range Sliders Below Visual Box -->
                <div class="tb4-spacing-sliders">
                    <div class="tb4-spacing-sliders-section tb4-spacing-margin-sliders">
                        <div class="tb4-spacing-sliders-title">Margin</div>
                        <div class="tb4-spacing-slider-row">
                            <label>Top</label>
                            <input type="range" class="tb4-range" name="marginTopRange" value="${mt}" min="-100" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'marginTop', 'margin', 'top')">
                            <span class="tb4-slider-value">${mt}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Right</label>
                            <input type="range" class="tb4-range" name="marginRightRange" value="${mr}" min="-100" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'marginRight', 'margin', 'right')">
                            <span class="tb4-slider-value">${mr}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Bottom</label>
                            <input type="range" class="tb4-range" name="marginBottomRange" value="${mb}" min="-100" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'marginBottom', 'margin', 'bottom')">
                            <span class="tb4-slider-value">${mb}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Left</label>
                            <input type="range" class="tb4-range" name="marginLeftRange" value="${ml}" min="-100" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'marginLeft', 'margin', 'left')">
                            <span class="tb4-slider-value">${ml}px</span>
                        </div>
                    </div>

                    <div class="tb4-spacing-sliders-section tb4-spacing-padding-sliders">
                        <div class="tb4-spacing-sliders-title">Padding</div>
                        <div class="tb4-spacing-slider-row">
                            <label>Top</label>
                            <input type="range" class="tb4-range" name="paddingTopRange" value="${pt}" min="0" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'paddingTop', 'padding', 'top')">
                            <span class="tb4-slider-value">${pt}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Right</label>
                            <input type="range" class="tb4-range" name="paddingRightRange" value="${pr}" min="0" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'paddingRight', 'padding', 'right')">
                            <span class="tb4-slider-value">${pr}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Bottom</label>
                            <input type="range" class="tb4-range" name="paddingBottomRange" value="${pb}" min="0" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'paddingBottom', 'padding', 'bottom')">
                            <span class="tb4-slider-value">${pb}px</span>
                        </div>
                        <div class="tb4-spacing-slider-row">
                            <label>Left</label>
                            <input type="range" class="tb4-range" name="paddingLeftRange" value="${pl}" min="0" max="200" step="1"
                                   oninput="TB4Builder.syncSpacingFromSlider(this, 'paddingLeft', 'padding', 'left')">
                            <span class="tb4-slider-value">${pl}px</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Toggle spacing link for margin or padding in combined box
     * @param {string} type - 'margin' or 'padding'
     */
    toggleSpacingLink(type) {
        const container = document.querySelector('[data-spacing-control]');
        if (!container) return;

        const linkBtn = container.querySelector(`.tb4-spacing-link-${type}`);
        const inputs = container.querySelectorAll(`input[data-spacing-type="${type}"]`);

        if (!linkBtn || inputs.length === 0) return;

        const isLinked = linkBtn.classList.contains('linked');

        if (isLinked) {
            // Unlink
            linkBtn.classList.remove('linked');
            inputs.forEach(input => {
                input.removeEventListener('input', this._spacingLinkHandler);
            });
        } else {
            // Link - sync all values to first input
            linkBtn.classList.add('linked');
            const firstValue = inputs[0].value;
            inputs.forEach(input => {
                input.value = firstValue;
            });

            // Store handler for this type
            this._spacingLinkHandler = (e) => {
                const newValue = e.target.value;
                inputs.forEach(input => {
                    input.value = newValue;
                });
                this.handleSettingChange(e);
            };

            inputs.forEach(input => {
                input.addEventListener('input', this._spacingLinkHandler);
            });
        }
    },

    /**
     * Sync spacing input from range slider
     * @param {HTMLInputElement} slider - The range slider
     * @param {string} inputName - Name of the text input to sync
     * @param {string} type - 'margin' or 'padding'
     * @param {string} side - 'top', 'right', 'bottom', 'left'
     */
    syncSpacingFromSlider(slider, inputName, type, side) {
        const container = slider.closest('[data-spacing-control]');
        if (!container) return;

        const value = slider.value;

        // Update the text input in visual box
        const textInput = container.querySelector(`input[name="${inputName}"]`);
        if (textInput) {
            textInput.value = value;
        }

        // Update slider value display
        const valueDisplay = slider.nextElementSibling;
        if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
            valueDisplay.textContent = value + 'px';
        }

        // Check if linked and sync other values
        const linkBtn = container.querySelector(`.tb4-spacing-link-${type}`);
        if (linkBtn && linkBtn.classList.contains('linked')) {
            const allSliders = container.querySelectorAll(`input[name^="${type}"][name$="Range"]`);
            const allTextInputs = container.querySelectorAll(`input[data-spacing-type="${type}"]`);

            allSliders.forEach(inp => {
                inp.value = value;
                const display = inp.nextElementSibling;
                if (display && display.classList.contains('tb4-slider-value')) {
                    display.textContent = value + 'px';
                }
            });

            allTextInputs.forEach(inp => {
                if (inp.type !== 'range') {
                    inp.value = value;
                }
            });
        }

        // Trigger settings change
        if (textInput) {
            this.handleSettingChange({ target: textInput });
        }
    },

    /**
     * Update spacing from visual box input
     * @param {HTMLInputElement} input - The text input in visual box
     */
    updateSpacingFromBox(input) {
        const container = input.closest('[data-spacing-control]');
        if (!container) return;

        const type = input.dataset.spacingType;
        const side = input.dataset.spacingSide;
        const value = parseInt(input.value) || 0;
        const name = input.name;

        // Find and update corresponding range slider
        const slider = container.querySelector(`input[name="${name}Range"]`);
        if (slider) {
            slider.value = value;
            const valueDisplay = slider.nextElementSibling;
            if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
                valueDisplay.textContent = value + 'px';
            }
        }

        // Check if linked
        const linkBtn = container.querySelector(`.tb4-spacing-link-${type}`);
        if (linkBtn && linkBtn.classList.contains('linked')) {
            // Sync all inputs of this type
            const allTextInputs = container.querySelectorAll(`input[data-spacing-type="${type}"]`);
            const allSliders = container.querySelectorAll(`input[name^="${type}"][name$="Range"]`);

            allTextInputs.forEach(inp => {
                if (inp.type !== 'range') {
                    inp.value = value;
                }
            });

            allSliders.forEach(slider => {
                slider.value = value;
                const display = slider.nextElementSibling;
                if (display && display.classList.contains('tb4-slider-value')) {
                    display.textContent = value + 'px';
                }
            });
        }

        // Trigger settings change
        this.handleSettingChange({ target: input });
    },

    /**
     * Sync border width input from range slider
     * @param {HTMLInputElement} slider - The range slider
     * @param {string} side - 'top', 'right', 'bottom', 'left'
     */
    syncBorderWidth(slider, side) {
        const container = slider.closest('[data-border-control]');
        if (!container) return;

        const value = slider.value;
        const sideCapitalized = side.charAt(0).toUpperCase() + side.slice(1);

        // Update the text input in visual box
        const textInput = container.querySelector(`input[name="borderWidth${sideCapitalized}"]`);
        if (textInput) {
            textInput.value = value;
        }

        // Update slider value display
        const valueDisplay = slider.nextElementSibling;
        if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
            valueDisplay.textContent = value + 'px';
        }

        // Check if linked and sync other values
        const linkBtn = container.querySelector('.tb4-border-link-width');
        if (linkBtn && linkBtn.classList.contains('linked')) {
            const sides = ['Top', 'Right', 'Bottom', 'Left'];
            sides.forEach(s => {
                const inp = container.querySelector(`input[name="borderWidth${s}"]`);
                const sldr = container.querySelector(`input[name="borderWidth${s}Range"]`);
                if (inp) inp.value = value;
                if (sldr) {
                    sldr.value = value;
                    const disp = sldr.nextElementSibling;
                    if (disp && disp.classList.contains('tb4-slider-value')) {
                        disp.textContent = value + 'px';
                    }
                }
            });
        }

        // Trigger settings change
        if (textInput) {
            this.handleSettingChange({ target: textInput });
        }
    },

    /**
     * Update border width slider from text input
     * @param {HTMLInputElement} input - The text input
     */
    updateBorderWidthFromInput(input) {
        const container = input.closest('[data-border-control]');
        if (!container) return;

        const side = input.dataset.borderSide;
        const sideCapitalized = side.charAt(0).toUpperCase() + side.slice(1);
        const value = parseInt(input.value) || 0;

        // Find and update corresponding range slider
        const slider = container.querySelector(`input[name="borderWidth${sideCapitalized}Range"]`);
        if (slider) {
            slider.value = value;
            const valueDisplay = slider.nextElementSibling;
            if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
                valueDisplay.textContent = value + 'px';
            }
        }

        // Check if linked
        const linkBtn = container.querySelector('.tb4-border-link-width');
        if (linkBtn && linkBtn.classList.contains('linked')) {
            const sides = ['Top', 'Right', 'Bottom', 'Left'];
            sides.forEach(s => {
                const inp = container.querySelector(`input[name="borderWidth${s}"]`);
                const sldr = container.querySelector(`input[name="borderWidth${s}Range"]`);
                if (inp) inp.value = value;
                if (sldr) {
                    sldr.value = value;
                    const disp = sldr.nextElementSibling;
                    if (disp && disp.classList.contains('tb4-slider-value')) {
                        disp.textContent = value + 'px';
                    }
                }
            });
        }

        // Trigger settings change
        this.handleSettingChange({ target: input });
    },

    /**
     * Sync border radius input from range slider
     * @param {HTMLInputElement} slider - The range slider
     * @param {string} corner - 'topLeft', 'topRight', 'bottomRight', 'bottomLeft'
     */
    syncBorderRadius(slider, corner) {
        const container = slider.closest('[data-border-control]');
        if (!container) return;

        const value = slider.value;
        const cornerCapitalized = corner.charAt(0).toUpperCase() + corner.slice(1);

        // Update the text input in visual box
        const textInput = container.querySelector(`input[name="borderRadius${cornerCapitalized}"]`);
        if (textInput) {
            textInput.value = value;
        }

        // Update slider value display
        const valueDisplay = slider.nextElementSibling;
        if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
            valueDisplay.textContent = value + 'px';
        }

        // Check if linked and sync other values
        const linkBtn = container.querySelector('.tb4-border-link-radius');
        if (linkBtn && linkBtn.classList.contains('linked')) {
            const corners = ['TopLeft', 'TopRight', 'BottomRight', 'BottomLeft'];
            corners.forEach(c => {
                const inp = container.querySelector(`input[name="borderRadius${c}"]`);
                const sldr = container.querySelector(`input[name="borderRadius${c}Range"]`);
                if (inp) inp.value = value;
                if (sldr) {
                    sldr.value = value;
                    const disp = sldr.nextElementSibling;
                    if (disp && disp.classList.contains('tb4-slider-value')) {
                        disp.textContent = value + 'px';
                    }
                }
            });
        }

        // Trigger settings change
        if (textInput) {
            this.handleSettingChange({ target: textInput });
        }
    },

    /**
     * Update border radius slider from text input
     * @param {HTMLInputElement} input - The text input
     */
    updateBorderRadiusFromInput(input) {
        const container = input.closest('[data-border-control]');
        if (!container) return;

        const corner = input.dataset.borderCorner;
        const cornerCapitalized = corner.charAt(0).toUpperCase() + corner.slice(1);
        const value = parseInt(input.value) || 0;

        // Find and update corresponding range slider
        const slider = container.querySelector(`input[name="borderRadius${cornerCapitalized}Range"]`);
        if (slider) {
            slider.value = value;
            const valueDisplay = slider.nextElementSibling;
            if (valueDisplay && valueDisplay.classList.contains('tb4-slider-value')) {
                valueDisplay.textContent = value + 'px';
            }
        }

        // Check if linked
        const linkBtn = container.querySelector('.tb4-border-link-radius');
        if (linkBtn && linkBtn.classList.contains('linked')) {
            const corners = ['TopLeft', 'TopRight', 'BottomRight', 'BottomLeft'];
            corners.forEach(c => {
                const inp = container.querySelector(`input[name="borderRadius${c}"]`);
                const sldr = container.querySelector(`input[name="borderRadius${c}Range"]`);
                if (inp) inp.value = value;
                if (sldr) {
                    sldr.value = value;
                    const disp = sldr.nextElementSibling;
                    if (disp && disp.classList.contains('tb4-slider-value')) {
                        disp.textContent = value + 'px';
                    }
                }
            });
        }

        // Trigger settings change
        this.handleSettingChange({ target: input });
    },

    /**
     * Toggle border link for width or radius
     * @param {string} type - 'width' or 'radius'
     */
    toggleBorderLink(type) {
        const container = document.querySelector('[data-border-control]');
        if (!container) return;

        const linkBtn = container.querySelector(`.tb4-border-link-${type}`);
        if (!linkBtn) return;

        const isLinked = linkBtn.classList.contains('linked');

        if (isLinked) {
            // Unlink
            linkBtn.classList.remove('linked');
        } else {
            // Link - sync all values to first input
            linkBtn.classList.add('linked');

            if (type === 'width') {
                const firstValue = container.querySelector('input[name="borderWidthTop"]')?.value || 0;
                const sides = ['Top', 'Right', 'Bottom', 'Left'];
                sides.forEach(s => {
                    const inp = container.querySelector(`input[name="borderWidth${s}"]`);
                    const sldr = container.querySelector(`input[name="borderWidth${s}Range"]`);
                    if (inp) inp.value = firstValue;
                    if (sldr) {
                        sldr.value = firstValue;
                        const disp = sldr.nextElementSibling;
                        if (disp && disp.classList.contains('tb4-slider-value')) {
                            disp.textContent = firstValue + 'px';
                        }
                    }
                });
            } else {
                const firstValue = container.querySelector('input[name="borderRadiusTopLeft"]')?.value || 0;
                const corners = ['TopLeft', 'TopRight', 'BottomRight', 'BottomLeft'];
                corners.forEach(c => {
                    const inp = container.querySelector(`input[name="borderRadius${c}"]`);
                    const sldr = container.querySelector(`input[name="borderRadius${c}Range"]`);
                    if (inp) inp.value = firstValue;
                    if (sldr) {
                        sldr.value = firstValue;
                        const disp = sldr.nextElementSibling;
                        if (disp && disp.classList.contains('tb4-slider-value')) {
                            disp.textContent = firstValue + 'px';
                        }
                    }
                });
            }
        }

        // Re-initialize Lucide icons for the link icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    },

    /**
     * Switch background tab (Color/Gradient/Image)
     * Called from onclick on tab buttons
     * @param {HTMLElement} btn - The clicked button
     * @param {string} tabId - Tab identifier (bg-color, bg-gradient, bg-image)
     */
    switchBgTab(btn, tabId) {
        const tabsContainer = btn.closest('.tb4-tabs');
        if (!tabsContainer) return;

        const hiddenInput = tabsContainer.querySelector('input[name="backgroundType"]');

        // Update button states
        tabsContainer.querySelectorAll('.tb4-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update hidden input value (remove 'bg-' prefix)
        if (hiddenInput) {
            hiddenInput.value = tabId.replace('bg-', '');
        }

        // Show/hide content panels
        tabsContainer.querySelectorAll('.tb4-tab-content').forEach(content => {
            content.style.display = content.dataset.tabContent === tabId ? '' : 'none';
        });

        // Trigger settings change to save
        if (hiddenInput) {
            this.handleSettingChange({ target: hiddenInput });
        }
    },

    /**
     * Render Border Settings with Visual Controls
     * Visual border width/style/color/radius controls
     * @param {object} settings - Current element settings
     * @returns {string} HTML for border controls
     */
    renderBorderSettingsVisual(settings) {
        const bw = settings.borderWidth || { top: 0, right: 0, bottom: 0, left: 0 };
        const bs = settings.borderStyle || 'none';
        const bc = settings.borderColor || '#334155';
        const br = settings.borderRadius || { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 };

        // Normalize values
        const borderWidthTop = typeof bw === 'object' ? (bw.top || 0) : bw;
        const borderWidthRight = typeof bw === 'object' ? (bw.right || 0) : bw;
        const borderWidthBottom = typeof bw === 'object' ? (bw.bottom || 0) : bw;
        const borderWidthLeft = typeof bw === 'object' ? (bw.left || 0) : bw;

        const radiusTL = typeof br === 'object' ? (br.topLeft || 0) : br;
        const radiusTR = typeof br === 'object' ? (br.topRight || 0) : br;
        const radiusBR = typeof br === 'object' ? (br.bottomRight || 0) : br;
        const radiusBL = typeof br === 'object' ? (br.bottomLeft || 0) : br;

        return `
            <div class="tb4-border-controls-grid" data-border-control>
                <!-- Border Style -->
                <div class="tb4-field">
                    <label class="tb4-label">Border Style</label>
                    <select name="borderStyle" class="tb4-select">
                        <option value="none" ${bs === 'none' ? 'selected' : ''}>None</option>
                        <option value="solid" ${bs === 'solid' ? 'selected' : ''}>Solid</option>
                        <option value="dashed" ${bs === 'dashed' ? 'selected' : ''}>Dashed</option>
                        <option value="dotted" ${bs === 'dotted' ? 'selected' : ''}>Dotted</option>
                        <option value="double" ${bs === 'double' ? 'selected' : ''}>Double</option>
                    </select>
                </div>

                <!-- Border Color -->
                <div class="tb4-field">
                    <label class="tb4-label">Border Color</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="color" name="borderColor" value="${bc}" class="tb4-color" style="width:50px;height:36px;padding:2px;border-radius:4px;border:1px solid #334155;cursor:pointer">
                        <input type="text" name="borderColorText" value="${bc}" class="tb4-input" style="flex:1" placeholder="#000000">
                    </div>
                </div>

                <!-- Border Width Visual Box with Sliders -->
                <div class="tb4-field">
                    <label class="tb4-label">Border Width</label>
                    <div class="tb4-border-width-visual">
                        <div class="tb4-border-width-box">
                            <input type="number" name="borderWidthTop" value="${borderWidthTop}" min="0" max="50"
                                   class="tb4-border-box-input tb4-bw-top" data-border-type="width" data-border-side="top"
                                   oninput="TB4Builder.updateBorderWidthFromInput(this)">
                            <input type="number" name="borderWidthRight" value="${borderWidthRight}" min="0" max="50"
                                   class="tb4-border-box-input tb4-bw-right" data-border-type="width" data-border-side="right"
                                   oninput="TB4Builder.updateBorderWidthFromInput(this)">
                            <input type="number" name="borderWidthBottom" value="${borderWidthBottom}" min="0" max="50"
                                   class="tb4-border-box-input tb4-bw-bottom" data-border-type="width" data-border-side="bottom"
                                   oninput="TB4Builder.updateBorderWidthFromInput(this)">
                            <input type="number" name="borderWidthLeft" value="${borderWidthLeft}" min="0" max="50"
                                   class="tb4-border-box-input tb4-bw-left" data-border-type="width" data-border-side="left"
                                   oninput="TB4Builder.updateBorderWidthFromInput(this)">
                            <div class="tb4-border-center">WIDTH</div>
                            <button type="button" class="tb4-border-link-btn tb4-border-link-width" onclick="TB4Builder.toggleBorderLink('width')" title="Link all values">
                                <i data-lucide="link-2" style="width:12px;height:12px"></i>
                            </button>
                        </div>
                        <!-- Border Width Sliders -->
                        <div class="tb4-border-sliders">
                            <div class="tb4-border-slider-row">
                                <label>Top</label>
                                <input type="range" class="tb4-range" name="borderWidthTopRange" value="${borderWidthTop}" min="0" max="50" step="1"
                                       oninput="TB4Builder.syncBorderWidth(this, 'top')">
                                <span class="tb4-slider-value">${borderWidthTop}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Right</label>
                                <input type="range" class="tb4-range" name="borderWidthRightRange" value="${borderWidthRight}" min="0" max="50" step="1"
                                       oninput="TB4Builder.syncBorderWidth(this, 'right')">
                                <span class="tb4-slider-value">${borderWidthRight}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Bottom</label>
                                <input type="range" class="tb4-range" name="borderWidthBottomRange" value="${borderWidthBottom}" min="0" max="50" step="1"
                                       oninput="TB4Builder.syncBorderWidth(this, 'bottom')">
                                <span class="tb4-slider-value">${borderWidthBottom}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Left</label>
                                <input type="range" class="tb4-range" name="borderWidthLeftRange" value="${borderWidthLeft}" min="0" max="50" step="1"
                                       oninput="TB4Builder.syncBorderWidth(this, 'left')">
                                <span class="tb4-slider-value">${borderWidthLeft}px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Border Radius Visual Box with Sliders -->
                <div class="tb4-field">
                    <label class="tb4-label">Border Radius</label>
                    <div class="tb4-border-radius-visual">
                        <div class="tb4-border-radius-box">
                            <input type="number" name="borderRadiusTopLeft" value="${radiusTL}" min="0" max="200"
                                   class="tb4-border-box-input tb4-br-tl" data-border-type="radius" data-border-corner="topLeft"
                                   oninput="TB4Builder.updateBorderRadiusFromInput(this)">
                            <input type="number" name="borderRadiusTopRight" value="${radiusTR}" min="0" max="200"
                                   class="tb4-border-box-input tb4-br-tr" data-border-type="radius" data-border-corner="topRight"
                                   oninput="TB4Builder.updateBorderRadiusFromInput(this)">
                            <input type="number" name="borderRadiusBottomRight" value="${radiusBR}" min="0" max="200"
                                   class="tb4-border-box-input tb4-br-br" data-border-type="radius" data-border-corner="bottomRight"
                                   oninput="TB4Builder.updateBorderRadiusFromInput(this)">
                            <input type="number" name="borderRadiusBottomLeft" value="${radiusBL}" min="0" max="200"
                                   class="tb4-border-box-input tb4-br-bl" data-border-type="radius" data-border-corner="bottomLeft"
                                   oninput="TB4Builder.updateBorderRadiusFromInput(this)">
                            <div class="tb4-border-center">RADIUS</div>
                            <button type="button" class="tb4-border-link-btn tb4-border-link-radius" onclick="TB4Builder.toggleBorderLink('radius')" title="Link all values">
                                <i data-lucide="link-2" style="width:12px;height:12px"></i>
                            </button>
                        </div>
                        <!-- Border Radius Sliders -->
                        <div class="tb4-border-sliders">
                            <div class="tb4-border-slider-row">
                                <label>Top-L</label>
                                <input type="range" class="tb4-range" name="borderRadiusTopLeftRange" value="${radiusTL}" min="0" max="200" step="1"
                                       oninput="TB4Builder.syncBorderRadius(this, 'topLeft')">
                                <span class="tb4-slider-value">${radiusTL}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Top-R</label>
                                <input type="range" class="tb4-range" name="borderRadiusTopRightRange" value="${radiusTR}" min="0" max="200" step="1"
                                       oninput="TB4Builder.syncBorderRadius(this, 'topRight')">
                                <span class="tb4-slider-value">${radiusTR}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Bot-R</label>
                                <input type="range" class="tb4-range" name="borderRadiusBottomRightRange" value="${radiusBR}" min="0" max="200" step="1"
                                       oninput="TB4Builder.syncBorderRadius(this, 'bottomRight')">
                                <span class="tb4-slider-value">${radiusBR}px</span>
                            </div>
                            <div class="tb4-border-slider-row">
                                <label>Bot-L</label>
                                <input type="range" class="tb4-range" name="borderRadiusBottomLeftRange" value="${radiusBL}" min="0" max="200" step="1"
                                       oninput="TB4Builder.syncBorderRadius(this, 'bottomLeft')">
                                <span class="tb4-slider-value">${radiusBL}px</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Render Box Shadow Settings with Live Preview
     * @param {object} settings - Current element settings
     * @returns {string} HTML for box shadow controls
     */
    renderBoxShadowSettingsVisual(settings) {
        const enabled = settings.boxShadowEnabled || false;
        const h = settings.boxShadowH ?? 0;
        const v = settings.boxShadowV ?? 4;
        const blur = settings.boxShadowBlur ?? 10;
        const spread = settings.boxShadowSpread ?? 0;
        const color = settings.boxShadowColor || 'rgba(0,0,0,0.15)';
        const inset = settings.boxShadowInset || false;

        // Build shadow string for preview
        const shadowStr = enabled
            ? `${inset ? 'inset ' : ''}${h}px ${v}px ${blur}px ${spread}px ${color}`
            : 'none';

        return `
            <div class="tb4-shadow-controls-container">
                <!-- Enable Toggle -->
                <div class="tb4-field tb4-field-checkbox" style="margin-bottom:16px">
                    <label class="tb4-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="boxShadowEnabled" class="tb4-checkbox" ${enabled ? 'checked' : ''}>
                        <span>Enable Box Shadow</span>
                    </label>
                </div>

                <!-- Preview Box -->
                <div class="tb4-shadow-preview-box" data-shadow-preview style="box-shadow:${shadowStr}">
                    Preview
                </div>

                <!-- Shadow Controls -->
                <div class="${!enabled ? 'tb4-disabled' : ''}" data-shadow-controls>
                    <div class="tb4-control-row slider">
                        <label>Horizontal <span class="tb4-slider-value" data-value-display="boxShadowH">${h}px</span></label>
                        <input type="range" name="boxShadowH" value="${h}" min="-50" max="50" class="tb4-range"
                               oninput="TB4Builder.updateShadowPreview()">
                    </div>

                    <div class="tb4-control-row slider">
                        <label>Vertical <span class="tb4-slider-value" data-value-display="boxShadowV">${v}px</span></label>
                        <input type="range" name="boxShadowV" value="${v}" min="-50" max="50" class="tb4-range"
                               oninput="TB4Builder.updateShadowPreview()">
                    </div>

                    <div class="tb4-control-row slider">
                        <label>Blur <span class="tb4-slider-value" data-value-display="boxShadowBlur">${blur}px</span></label>
                        <input type="range" name="boxShadowBlur" value="${blur}" min="0" max="100" class="tb4-range"
                               oninput="TB4Builder.updateShadowPreview()">
                    </div>

                    <div class="tb4-control-row slider">
                        <label>Spread <span class="tb4-slider-value" data-value-display="boxShadowSpread">${spread}px</span></label>
                        <input type="range" name="boxShadowSpread" value="${spread}" min="-50" max="50" class="tb4-range"
                               oninput="TB4Builder.updateShadowPreview()">
                    </div>

                    <div class="tb4-control-row">
                        <label>Color</label>
                        <input type="text" name="boxShadowColor" value="${color}" class="tb4-input" placeholder="rgba(0,0,0,0.15)"
                               oninput="TB4Builder.updateShadowPreview()">
                    </div>

                    <div class="tb4-field tb4-field-checkbox" style="margin-top:12px">
                        <label class="tb4-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="boxShadowInset" class="tb4-checkbox" ${inset ? 'checked' : ''}
                                   onchange="TB4Builder.updateShadowPreview()">
                            <span>Inset Shadow</span>
                        </label>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Update shadow preview in real-time
     * Called when any shadow control changes
     */
    updateShadowPreview() {
        const preview = document.querySelector('[data-shadow-preview]');
        const controls = document.querySelector('[data-shadow-controls]');
        if (!preview || !controls) return;

        const h = controls.querySelector('[name="boxShadowH"]')?.value || 0;
        const v = controls.querySelector('[name="boxShadowV"]')?.value || 4;
        const blur = controls.querySelector('[name="boxShadowBlur"]')?.value || 10;
        const spread = controls.querySelector('[name="boxShadowSpread"]')?.value || 0;
        const color = controls.querySelector('[name="boxShadowColor"]')?.value || 'rgba(0,0,0,0.15)';
        const inset = controls.querySelector('[name="boxShadowInset"]')?.checked || false;
        const enabled = document.querySelector('[name="boxShadowEnabled"]')?.checked;

        // Update value displays
        document.querySelector('[data-value-display="boxShadowH"]').textContent = h + 'px';
        document.querySelector('[data-value-display="boxShadowV"]').textContent = v + 'px';
        document.querySelector('[data-value-display="boxShadowBlur"]').textContent = blur + 'px';
        document.querySelector('[data-value-display="boxShadowSpread"]').textContent = spread + 'px';

        // Update preview
        if (enabled) {
            const shadowStr = `${inset ? 'inset ' : ''}${h}px ${v}px ${blur}px ${spread}px ${color}`;
            preview.style.boxShadow = shadowStr;
        } else {
            preview.style.boxShadow = 'none';
        }
    },

    /**
     * Initialize responsive input toggle handlers
     * Called after settings panel is rendered
     */
    initResponsiveInputs() {
        document.querySelectorAll('.tb4-responsive-toggle').forEach(toggle => {
            toggle.querySelectorAll('.tb4-responsive-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const device = e.target.dataset.device;
                    const field = e.target.closest('.tb4-field-responsive');

                    // Update button states
                    field.querySelectorAll('.tb4-responsive-btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');

                    // Show/hide inputs
                    field.querySelectorAll('.tb4-responsive-input').forEach(input => {
                        input.style.display = input.dataset.device === device ? '' : 'none';
                        input.classList.toggle('active', input.dataset.device === device);
                    });
                });
            });
        });
    },

    /**
     * Initialize background tab switching
     * Called after settings panel is rendered
     */
    initBackgroundTabs() {
        document.querySelectorAll('.tb4-bg-tabs').forEach(tabs => {
            tabs.querySelectorAll('.tb4-tab-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const tabId = e.target.dataset.tab;
                    const tabsContainer = e.target.closest('.tb4-tabs');
                    const hiddenInput = tabsContainer.querySelector('input[name="backgroundType"]');

                    // Update button states
                    tabsContainer.querySelectorAll('.tb4-tab-btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');

                    // Update hidden input value
                    if (hiddenInput) {
                        hiddenInput.value = tabId.replace('bg-', '');
                    }

                    // Show/hide content
                    tabsContainer.querySelectorAll('.tb4-tab-content').forEach(content => {
                        content.style.display = content.dataset.tabContent === tabId ? '' : 'none';
                    });

                    // Trigger change to save settings
                    this.handleSettingChange({ target: hiddenInput });
                });
            });
        });
    },

    /**
     * Initialize text align button group handlers
     * Called after settings panel is rendered
     */
    initTextAlignButtons() {
        document.querySelectorAll('.tb4-text-align-group').forEach(group => {
            const hiddenInput = group.parentElement.querySelector('input[name="textAlign"]');

            group.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    // Update button states
                    group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');

                    // Update hidden input
                    if (hiddenInput) {
                        hiddenInput.value = e.target.dataset.value;
                        this.handleSettingChange({ target: hiddenInput });
                    }
                });
            });
        });
    },

    /**
     * Initialize all design/advanced panel interactions
     * Should be called after settings panel HTML is rendered
     */
    initSettingsPanelInteractions() {
        this.initResponsiveInputs();
        this.initBackgroundTabs();
        this.initTextAlignButtons();

        // Initialize box shadow enable toggle
        document.querySelectorAll('input[name="boxShadowEnabled"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const controls = e.target.closest('.tb4-collapsible-section').querySelector('[data-shadow-controls]');
                if (controls) {
                    controls.classList.toggle('tb4-disabled', !e.target.checked);
                }
            });
        });

        // Initialize position type toggle
        document.querySelectorAll('select[name="position"]').forEach(select => {
            select.addEventListener('change', (e) => {
                const offsets = e.target.closest('.tb4-section-body').querySelector('.tb4-position-offsets');
                if (offsets) {
                    const hasPosition = e.target.value && e.target.value !== 'static';
                    offsets.classList.toggle('tb4-disabled', !hasPosition);
                }
            });
        });
    },

    /**
     * Render layout picker options
     */
    renderLayoutOptions(current) {
        const layouts = [
            { value: '1', label: '1 Column', icon: '▮' },
            { value: '1/2-1/2', label: '2 Equal', icon: '▮▮' },
            { value: '1/3-2/3', label: '1/3 - 2/3', icon: '▯▮' },
            { value: '2/3-1/3', label: '2/3 - 1/3', icon: '▮▯' },
            { value: '1/4-3/4', label: '1/4 - 3/4', icon: '▯▮' },
            { value: '3/4-1/4', label: '3/4 - 1/4', icon: '▮▯' },
            { value: '1/3-1/3-1/3', label: '3 Equal', icon: '▮▮▮' },
            { value: '1/2-1/4-1/4', label: '1/2 - 1/4 - 1/4', icon: '▮▯▯' },
            { value: '1/4-1/4-1/2', label: '1/4 - 1/4 - 1/2', icon: '▯▯▮' },
            { value: '1/4-1/2-1/4', label: '1/4 - 1/2 - 1/4', icon: '▯▮▯' },
            { value: '1/4-1/4-1/4-1/4', label: '4 Equal', icon: '▮▮▮▮' }
        ];

        return layouts.map(layout => `
            <button type="button"
                    class="tb4-layout-option ${layout.value === current ? 'active' : ''}"
                    data-layout="${layout.value}"
                    title="${layout.label}">
                <span class="tb4-layout-icon">${layout.icon}</span>
            </button>
        `).join('');
    },

    /**
     * Bind settings form events
     */
    bindSettingsEvents() {
        if (!this.dom.settingsPanel) return;

        // Input changes
        this.dom.settingsPanel.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('change', (e) => this.handleSettingChange(e));
            input.addEventListener('input', (e) => {
                // Real-time preview for certain types
                if (e.target.type === 'range' || e.target.type === 'color') {
                    this.handleSettingChange(e);
                }
            });
        });

        // Range value display
        this.dom.settingsPanel.querySelectorAll('.tb4-range').forEach(range => {
            range.addEventListener('input', (e) => {
                const display = e.target.nextElementSibling;
                if (display && display.classList.contains('tb4-range-value')) {
                    display.textContent = e.target.value;
                }
            });
        });

        // Layout picker
        this.dom.settingsPanel.querySelectorAll('.tb4-layout-option').forEach(btn => {
            btn.addEventListener('click', () => {
                this.changeRowLayout(this.state.selected, btn.dataset.layout);
            });
        });

        // Color picker sync (both directions)
        this.dom.settingsPanel.querySelectorAll('.tb4-color').forEach(picker => {
            const textInput = picker.nextElementSibling;
            if (!textInput || !textInput.classList.contains('tb4-color-text')) return;

            picker.addEventListener('input', (e) => {
                textInput.value = e.target.value;
            });

            textInput.addEventListener('input', (e) => {
                const val = e.target.value;
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                    picker.value = val;
                }
            });

            textInput.addEventListener('change', (e) => {
                const val = e.target.value;
                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                    picker.value = val;
                    this.handleSettingChange({ target: picker });
                }
            });
        });

        // WYSIWYG (contenteditable) support
        this.dom.settingsPanel.querySelectorAll('.tb4-wysiwyg[contenteditable="true"]').forEach(wysiwyg => {
            const fieldName = wysiwyg.getAttribute('name') || wysiwyg.dataset.fieldName;
            if (!fieldName) return;

            let debounceTimer = null;
            const saveWysiwyg = () => {
                const value = wysiwyg.innerHTML;
                console.log('[TB4] WYSIWYG change:', fieldName, '=', value.substring(0, 50) + '...');
                this.handleSettingChange({
                    target: {
                        name: fieldName,
                        value: value,
                        type: 'wysiwyg'
                    }
                });
            };

            wysiwyg.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(saveWysiwyg, 500);
            });

            wysiwyg.addEventListener('blur', () => {
                clearTimeout(debounceTimer);
                saveWysiwyg();
            });
        });
    },

    /**
     * Handle setting change
     */
    handleSettingChange(e) {
        if (!this.state.selected) return;

        const element = this.findElement(this.state.selected);
        if (!element) return;

        const name = e.target.name;
        let value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;

        // Handle spacing controls
        if (name.match(/^(padding|margin)(Top|Right|Bottom|Left)$/)) {
            const prop = name.match(/^(padding|margin)/)[1];
            const side = name.match(/(Top|Right|Bottom|Left)$/)[1].toLowerCase();

            if (!element.settings) element.settings = {};
            if (!element.settings[prop]) element.settings[prop] = {};
            element.settings[prop][side] = parseInt(value) || 0;
        }
        // Handle content fields for modules
        else if (this.state.selectedType === 'module') {
            const moduleConfig = this.config.modules[element.type] || {};
            
            // Determine if this is a content field
            // Fields can be object { fieldName: {...} } or array [{ name: fieldName, ...}]
            let isContentField = false;
            if (moduleConfig.fields) {
                if (Array.isArray(moduleConfig.fields)) {
                    isContentField = moduleConfig.fields.some(f => f.name === name);
                } else if (typeof moduleConfig.fields === 'object') {
                    // Fields is an object - check if name exists as key
                    isContentField = name in moduleConfig.fields;
                }
            }
            
            console.log('[TB4] handleSettingChange:', { name, value, moduleType: element.type, isContentField });

            if (isContentField) {
                if (!element.content) element.content = {};
                element.content[name] = value;
            } else {
                if (!element.settings) element.settings = {};
                element.settings[name] = value;
            }
        }
        // Handle settings fields
        else {
            if (!element.settings) element.settings = {};
            element.settings[name] = value;
        }

        this.state.isDirty = true;
        // Debounce renderCanvas to prevent focus loss during typing
        clearTimeout(this._renderCanvasDebounceTimer);
        this._renderCanvasDebounceTimer = setTimeout(() => {
            this.renderCanvas();
            this.pushHistory();
        }, 300);
    },

    /**
     * Update a single element setting programmatically
     * Called from TB4Fields onChange handlers
     * @param {string} name - Setting name
     * @param {*} value - New value
     */
    updateElementSetting(name, value) {
        if (!this.state.selected) {
            console.warn('[TB4] updateElementSetting: No element selected');
            return;
        }

        const element = this.findElement(this.state.selected);
        if (!element) {
            console.warn('[TB4] updateElementSetting: Element not found:', this.state.selected);
            return;
        }

        console.log('[TB4] updateElementSetting:', name, '=', value);

        // Handle spacing controls (margin/padding with sides)
        if (name.match(/^(padding|margin)(Top|Right|Bottom|Left)$/)) {
            const prop = name.match(/^(padding|margin)/)[1];
            const side = name.match(/(Top|Right|Bottom|Left)$/)[1].toLowerCase();

            if (!element.settings) element.settings = {};
            if (!element.settings[prop]) element.settings[prop] = {};
            element.settings[prop][side] = parseInt(value) || 0;
        }
        // Handle border width (individual sides)
        else if (name.match(/^borderWidth(Top|Right|Bottom|Left)$/)) {
            const side = name.match(/(Top|Right|Bottom|Left)$/)[1].toLowerCase();
            if (!element.settings) element.settings = {};
            if (!element.settings.borderWidth || typeof element.settings.borderWidth !== 'object') {
                element.settings.borderWidth = { top: 0, right: 0, bottom: 0, left: 0 };
            }
            element.settings.borderWidth[side] = parseInt(value) || 0;
        }
        // Handle border radius (individual corners)
        else if (name.match(/^borderRadius(TopLeft|TopRight|BottomRight|BottomLeft)$/)) {
            const corner = name.replace('borderRadius', '');
            const cornerKey = corner.charAt(0).toLowerCase() + corner.slice(1);
            if (!element.settings) element.settings = {};
            if (!element.settings.borderRadius || typeof element.settings.borderRadius !== 'object') {
                element.settings.borderRadius = { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 };
            }
            element.settings.borderRadius[cornerKey] = parseInt(value) || 0;
        }
        // Handle content fields for modules
        else if (this.state.selectedType === 'module') {
            const moduleConfig = this.config.modules[element.type] || {};
            let isContentField = false;

            if (moduleConfig.fields) {
                if (Array.isArray(moduleConfig.fields)) {
                    isContentField = moduleConfig.fields.some(f => f.name === name);
                } else if (typeof moduleConfig.fields === 'object') {
                    isContentField = name in moduleConfig.fields;
                }
            }

            if (isContentField) {
                if (!element.content) element.content = {};
                element.content[name] = value;
            } else {
                if (!element.settings) element.settings = {};
                element.settings[name] = value;
            }
        }
        // Default: store in settings
        else {
            if (!element.settings) element.settings = {};
            element.settings[name] = value;
        }

        this.state.isDirty = true;
        // Debounce renderCanvas to prevent focus loss during typing
        clearTimeout(this._renderCanvasDebounceTimer);
        this._renderCanvasDebounceTimer = setTimeout(() => {
            this.renderCanvas();
            this.pushHistory();
        }, 300);
    },

    // ==========================================================================
    // DRAG AND DROP
    // ==========================================================================

    /**
     * Initialize drag and drop
     */
    initDragDrop() {
        // Global drag events
        document.addEventListener('dragover', (e) => {
            e.preventDefault();
        });
    },

    /**
     * Initialize canvas drop zones
     */
    initCanvasDropZones() {
        if (!this.dom.canvas) return;

        // Drop zones
        this.dom.canvas.querySelectorAll('[data-drop-zone]').forEach(zone => {
            zone.addEventListener('dragover', (e) => this.handleDragOver(e));
            zone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
            zone.addEventListener('drop', (e) => this.handleDrop(e));
        });

        // Existing elements (for reordering)
        this.dom.canvas.querySelectorAll('[data-tb4-type]').forEach(el => {
            el.setAttribute('draggable', 'true');
            el.addEventListener('dragstart', (e) => this.handleElementDragStart(e));
            el.addEventListener('dragend', (e) => this.handleElementDragEnd(e));
        });
    },

    /**
     * Handle module drag start from sidebar
     */
    handleModuleDragStart(e) {
        const moduleType = e.target.dataset.moduleType;
        e.dataTransfer.setData('text/plain', JSON.stringify({
            action: 'add-module',
            type: moduleType
        }));
        e.dataTransfer.effectAllowed = 'copy';
        e.target.classList.add('dragging');
    },

    /**
     * Handle module drag end from sidebar
     */
    handleModuleDragEnd(e) {
        e.target.classList.remove('dragging');
        this.clearDropHighlights();
    },

    /**
     * Handle element drag start (reordering)
     */
    handleElementDragStart(e) {
        const element = e.target.closest('[data-tb4-id]');
        if (!element) return;

        e.dataTransfer.setData('text/plain', JSON.stringify({
            action: 'move',
            id: element.dataset.tb4Id,
            type: element.dataset.tb4Type
        }));
        e.dataTransfer.effectAllowed = 'move';
        element.classList.add('dragging');

        // Prevent drag on child elements
        e.stopPropagation();
    },

    /**
     * Handle element drag end (reordering)
     */
    handleElementDragEnd(e) {
        const element = e.target.closest('[data-tb4-id]');
        if (element) {
            element.classList.remove('dragging');
        }
        this.clearDropHighlights();
    },

    /**
     * Handle drag over drop zone
     */
    handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();

        const zone = e.target.closest('[data-drop-zone]');
        if (zone) {
            zone.classList.add('drag-over');
            e.dataTransfer.dropEffect = 'copy';
        }
    },

    /**
     * Handle drag leave drop zone
     */
    handleDragLeave(e) {
        const zone = e.target.closest('[data-drop-zone]');
        if (zone && !zone.contains(e.relatedTarget)) {
            zone.classList.remove('drag-over');
        }
    },

    /**
     * Handle drop
     */
    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        this.clearDropHighlights();

        const zone = e.target.closest('[data-drop-zone]');
        if (!zone) return;

        let data;
        try {
            data = JSON.parse(e.dataTransfer.getData('text/plain'));
        } catch {
            return;
        }

        const zoneType = zone.dataset.dropZone;

        if (data.action === 'add-module') {
            if (zoneType === 'module') {
                const columnId = zone.dataset.columnId;
                this.addModule(columnId, data.type);
            } else if (zoneType === 'section') {
                // Create section -> row -> column -> module
                const section = this.addSection();
                const row = this.addRowWithLayout(section.id, '1');
                this.addModule(row.columns[0].id, data.type);
            }
        } else if (data.action === 'move') {
            this.handleMoveElement(data.id, data.type, zone);
        }
    },

    /**
     * Handle moving an element to a new location
     */
    handleMoveElement(elementId, elementType, dropZone) {
        // For now, just re-render (complex reordering logic would go here)
        this.renderCanvas();
    },

    /**
     * Clear all drop zone highlights
     */
    clearDropHighlights() {
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
    },

    // ==========================================================================
    // ELEMENT OPERATIONS
    // ==========================================================================

    /**
     * Add a new section
     */
    addSection(afterSectionId = null) {
        console.log('[TB4] addSection() called');

        // Ensure state.content exists
        if (!this.state.content) {
            this.state.content = {
                version: '1.0',
                sections: []
            };
        }

        // Ensure sections array exists
        if (!this.state.content.sections) {
            this.state.content.sections = [];
        }

        const section = {
            id: this.generateId(),
            type: 'section',
            settings: {
                containerWidth: 'boxed'
            },
            rows: []
        };

        if (afterSectionId) {
            const index = this.state.content.sections.findIndex(s => s.id === afterSectionId);
            if (index !== -1) {
                this.state.content.sections.splice(index + 1, 0, section);
            } else {
                this.state.content.sections.push(section);
            }
        } else {
            this.state.content.sections.push(section);
        }

        console.log('[TB4] Section added:', section.id);
        console.log('[TB4] Total sections:', this.state.content.sections.length);

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();
        this.selectElement(section.id, 'section');

        return section;
    },

    /**
     * Add a new row to a section with specified layout
     */
    addRowWithLayout(sectionId, layout = '1') {
        const section = this.findElement(sectionId);
        if (!section || !section.rows) return null;

        const columns = this.createColumnsFromLayout(layout);

        const row = {
            id: this.generateId(),
            type: 'row',
            layout: layout,
            settings: {},
            columns: columns
        };

        section.rows.push(row);

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();
        this.selectElement(row.id, 'row');

        return row;
    },

    /**
     * Create columns from layout string
     */
    createColumnsFromLayout(layout) {
        const widths = {
            '1': [100],
            '1/2-1/2': [50, 50],
            '1/3-2/3': [33.33, 66.67],
            '2/3-1/3': [66.67, 33.33],
            '1/4-3/4': [25, 75],
            '3/4-1/4': [75, 25],
            '1/3-1/3-1/3': [33.33, 33.33, 33.33],
            '1/2-1/4-1/4': [50, 25, 25],
            '1/4-1/4-1/2': [25, 25, 50],
            '1/4-1/2-1/4': [25, 50, 25],
            '1/4-1/4-1/4-1/4': [25, 25, 25, 25]
        };

        const colWidths = widths[layout] || [100];

        return colWidths.map(width => ({
            id: this.generateId(),
            type: 'column',
            width: width,
            settings: {},
            modules: []
        }));
    },

    /**
     * Change row layout
     */
    changeRowLayout(rowId, newLayout) {
        const row = this.findElement(rowId);
        if (!row) return;

        // Collect all existing modules
        const existingModules = [];
        if (row.columns) {
            row.columns.forEach(col => {
                if (col.modules) {
                    existingModules.push(...col.modules);
                }
            });
        }

        // Create new columns
        row.columns = this.createColumnsFromLayout(newLayout);
        row.layout = newLayout;

        // Redistribute modules to first column
        if (existingModules.length > 0 && row.columns.length > 0) {
            row.columns[0].modules = existingModules;
        }

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();

        // Update settings panel if row is selected
        if (this.state.selected === rowId) {
            this.showSettings(rowId, 'row');
        }
    },

    /**
     * Add a module to a column
     */
    addModule(columnId, moduleType) {
        const column = this.findElement(columnId);
        if (!column) return null;

        if (!column.modules) {
            column.modules = [];
        }

        const moduleConfig = this.config.modules[moduleType] || {};
        const defaultContent = {};

        // Set default values from module config
        // Fields can be an object (from PHP) or array
        if (moduleConfig.fields) {
            if (Array.isArray(moduleConfig.fields)) {
                moduleConfig.fields.forEach(field => {
                    if (field.default !== undefined) {
                        defaultContent[field.name] = field.default;
                    }
                });
            } else if (typeof moduleConfig.fields === 'object') {
                // Handle fields as object: { fieldName: { label, type, default } }
                Object.entries(moduleConfig.fields).forEach(([fieldName, fieldConfig]) => {
                    if (fieldConfig && fieldConfig.default !== undefined) {
                        defaultContent[fieldName] = fieldConfig.default;
                    }
                });
            }
        }

        const module = {
            id: this.generateId(),
            type: moduleType,
            content: defaultContent,
            settings: {}
        };

        column.modules.push(module);

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();
        this.selectElement(module.id, 'module');

        return module;
    },

    /**
     * Select an element
     */
    selectElement(elementId, elementType) {
        // Deselect previous
        this.deselectAll();

        this.state.selected = elementId;
        this.state.selectedType = elementType;

        // Add visual selection
        const el = this.dom.canvas?.querySelector(`[data-tb4-id="${elementId}"]`);
        if (el) {
            el.classList.add('tb4-selected');
        }

        // Show settings in LEFT panel (for sections/rows only - modules use right sidebar)
        if (elementType !== 'module') {
            this.showSettings(elementId, elementType);
        }

        // Open Settings Sidebar for selected module (RIGHT panel only)
        if (elementType === 'module' && typeof TB4SettingsSidebar !== 'undefined') {
            const moduleData = this.findElement(elementId);
            if (moduleData) {
                TB4SettingsSidebar.open(moduleData.type, elementId, moduleData);
            }
        }

        // Dispatch event
        document.dispatchEvent(new CustomEvent('tb4:select', {
            detail: { id: elementId, type: elementType }
        }));
    },

    /**
     * Deselect all elements
     */
    deselectAll() {
        this.state.selected = null;
        this.state.selectedType = null;

        this.dom.canvas?.querySelectorAll('.tb4-selected').forEach(el => {
            el.classList.remove('tb4-selected');
        });

        this.hideSettings();

        // Close Settings Sidebar when deselecting
        if (typeof TB4SettingsSidebar !== 'undefined' && TB4SettingsSidebar.state.isOpen) {
            TB4SettingsSidebar.close();
        }
    },

    /**
     * Delete an element
     */
    deleteElement(elementId) {
        const result = this.removeElementFromContent(this.state.content.sections, elementId);

        if (result) {
            if (this.state.selected === elementId) {
                this.deselectAll();
            }

            this.state.isDirty = true;
            this.renderCanvas();
            this.pushHistory();
        }
    },

    /**
     * Recursively remove element from content
     */
    removeElementFromContent(items, elementId) {
        if (!items) return false;

        for (let i = 0; i < items.length; i++) {
            if (items[i].id === elementId) {
                items.splice(i, 1);
                return true;
            }

            // Check nested elements
            if (items[i].rows && this.removeElementFromContent(items[i].rows, elementId)) {
                return true;
            }
            if (items[i].columns && this.removeElementFromContent(items[i].columns, elementId)) {
                return true;
            }
            if (items[i].modules && this.removeElementFromContent(items[i].modules, elementId)) {
                return true;
            }
        }

        return false;
    },

    /**
     * Duplicate an element
     */
    duplicateElement(elementId) {
        const element = this.findElement(elementId);
        if (!element) return;

        const clone = this.deepClone(element);
        this.regenerateIds(clone);

        // Find parent and insert after
        const parent = this.findParent(elementId);
        if (parent) {
            let array;
            if (element.type === 'section') {
                array = this.state.content.sections;
            } else if (element.type === 'row') {
                array = parent.rows;
            } else if (element.type === 'column') {
                array = parent.columns;
            } else if (element.type === 'module') {
                array = parent.modules;
            }

            if (array) {
                const index = array.findIndex(item => item.id === elementId);
                if (index !== -1) {
                    array.splice(index + 1, 0, clone);
                }
            }
        }

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();
        this.selectElement(clone.id, clone.type);
    },

    /**
     * Move an element up or down
     */
    moveElement(elementId, direction) {
        const parent = this.findParent(elementId);
        if (!parent) {
            // It's a section
            const index = this.state.content.sections.findIndex(s => s.id === elementId);
            if (index !== -1) {
                const newIndex = direction === 'up' ? index - 1 : index + 1;
                if (newIndex >= 0 && newIndex < this.state.content.sections.length) {
                    const section = this.state.content.sections.splice(index, 1)[0];
                    this.state.content.sections.splice(newIndex, 0, section);
                    this.state.isDirty = true;
                    this.renderCanvas();
                    this.pushHistory();
                }
            }
            return;
        }

        const element = this.findElement(elementId);
        if (!element) return;

        let array;
        if (element.rows !== undefined) { // It's a section
            array = this.state.content.sections;
        } else if (element.columns !== undefined) { // It's a row
            array = parent.rows;
        } else if (element.modules !== undefined) { // It's a column
            array = parent.columns;
        } else { // It's a module
            array = parent.modules;
        }

        if (!array) return;

        const index = array.findIndex(item => item.id === elementId);
        if (index === -1) return;

        const newIndex = direction === 'up' ? index - 1 : index + 1;
        if (newIndex < 0 || newIndex >= array.length) return;

        // Swap
        const temp = array[index];
        array[index] = array[newIndex];
        array[newIndex] = temp;

        this.state.isDirty = true;
        this.renderCanvas();
        this.pushHistory();
    },

    /**
     * Enable inline editing for a module
     */
    enableInlineEdit(moduleElement) {
        const moduleId = moduleElement.dataset.tb4Id;
        const module = this.findElement(moduleId);
        if (!module) return;

        // Only enable for text-based modules
        const editableTypes = ['text', 'heading', 'quote'];
        if (!editableTypes.includes(module.type)) return;

        const contentEl = moduleElement.querySelector('.tb4-module-content');
        if (!contentEl) return;

        contentEl.contentEditable = true;
        contentEl.focus();

        const saveInline = () => {
            contentEl.contentEditable = false;

            if (module.type === 'text') {
                module.content.text = contentEl.innerHTML;
            } else if (module.type === 'heading') {
                const heading = contentEl.querySelector('.tb4-heading');
                if (heading) {
                    module.content.text = heading.textContent;
                }
            } else if (module.type === 'quote') {
                const quote = contentEl.querySelector('p');
                if (quote) {
                    module.content.text = quote.textContent;
                }
            }

            this.state.isDirty = true;
            this.pushHistory();
        };

        contentEl.addEventListener('blur', saveInline, { once: true });
        contentEl.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                contentEl.blur();
            }
        });
    },

    // ==========================================================================
    // KEYBOARD SHORTCUTS
    // ==========================================================================

    /**
     * Initialize keyboard shortcuts
     */
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ignore if typing in input
            if (e.target.matches('input, textarea, [contenteditable="true"]')) {
                return;
            }

            // Ctrl/Cmd + S: Save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.save();
            }

            // Ctrl/Cmd + Z: Undo
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                this.undo();
            }

            // Ctrl/Cmd + Y or Ctrl/Cmd + Shift + Z: Redo
            if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
                e.preventDefault();
                this.redo();
            }

            // Delete/Backspace: Remove selected
            if ((e.key === 'Delete' || e.key === 'Backspace') && this.state.selected) {
                e.preventDefault();
                this.deleteElement(this.state.selected);
            }

            // Ctrl/Cmd + D: Duplicate
            if ((e.ctrlKey || e.metaKey) && e.key === 'd' && this.state.selected) {
                e.preventDefault();
                this.duplicateElement(this.state.selected);
            }

            // Escape: Deselect
            if (e.key === 'Escape') {
                this.deselectAll();
            }
        });
    },

    // ==========================================================================
    // HISTORY (UNDO/REDO)
    // ==========================================================================

    /**
     * Push current state to history
     */
    pushHistory() {
        // Remove any future history if we're not at the end
        if (this.state.historyIndex < this.state.history.length - 1) {
            this.state.history = this.state.history.slice(0, this.state.historyIndex + 1);
        }

        // Add current state
        const snapshot = this.deepClone(this.state.content);
        this.state.history.push(snapshot);

        // Limit history size
        if (this.state.history.length > this.state.maxHistory) {
            this.state.history.shift();
        }

        this.state.historyIndex = this.state.history.length - 1;
        this.updateHistoryButtons();
    },

    /**
     * Undo last change
     */
    undo() {
        if (this.state.historyIndex <= 0) return;

        this.state.historyIndex--;
        this.state.content = this.deepClone(this.state.history[this.state.historyIndex]);
        this.state.isDirty = true;
        this.renderCanvas();
        this.updateHistoryButtons();

        // Update settings if something is selected
        if (this.state.selected) {
            this.showSettings(this.state.selected, this.state.selectedType);
        }
    },

    /**
     * Redo last undone change
     */
    redo() {
        if (this.state.historyIndex >= this.state.history.length - 1) return;

        this.state.historyIndex++;
        this.state.content = this.deepClone(this.state.history[this.state.historyIndex]);
        this.state.isDirty = true;
        this.renderCanvas();
        this.updateHistoryButtons();

        // Update settings if something is selected
        if (this.state.selected) {
            this.showSettings(this.state.selected, this.state.selectedType);
        }
    },

    /**
     * Update undo/redo button states
     */
    updateHistoryButtons() {
        const undoBtn = this.dom.toolbar?.querySelector('[data-action="undo"]');
        const redoBtn = this.dom.toolbar?.querySelector('[data-action="redo"]');

        if (undoBtn) {
            undoBtn.disabled = this.state.historyIndex <= 0;
        }
        if (redoBtn) {
            redoBtn.disabled = this.state.historyIndex >= this.state.history.length - 1;
        }
    },

    // ==========================================================================
    // API INTEGRATION
    // ==========================================================================

    /**
     * Save content to server
     */
    async save() {
        if (this.state.isLoading) return;

        this.state.isLoading = true;
        this.updateSaveButton('Saving...');

        try {
            const response = await fetch(`${this.config.apiUrl}?action=save_page_content`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    page_id: this.config.pageId,
                    content_type: this.config.contentType,
                    content: this.state.content,
                    csrf_token: this.config.csrfToken
                })
            });

            const result = await response.json();

            if (result.success) {
                this.state.isDirty = false;
                this.showNotification('Saved successfully', 'success');
            } else {
                throw new Error(result.message || 'Save failed');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('Failed to save: ' + error.message, 'error');
        } finally {
            this.state.isLoading = false;
            this.updateSaveButton('Save');
        }
    },

    /**
     * Load content from server or provided data
     */
    loadContent(content) {
        console.log('[TB4] loadContent() called with:', typeof content);

        if (typeof content === 'string') {
            try {
                content = JSON.parse(content);
            } catch {
                console.error('Failed to parse content JSON');
                return;
            }
        }

        // Ensure content is a valid object with sections array
        if (!content || typeof content !== 'object') {
            content = { sections: [] };
        }

        // Ensure sections array exists
        if (!content.sections || !Array.isArray(content.sections)) {
            content.sections = [];
        }

        this.state.content = content;
        this.state.isDirty = false;
        this.state.history = [this.deepClone(this.state.content)];
        this.state.historyIndex = 0;

        console.log('[TB4] Content loaded with', this.state.content.sections.length, 'sections');

        this.renderCanvas();
        this.updateHistoryButtons();
    },

    // =========================================================================
    // CONTENT GETTERS (for Layout Library)
    // =========================================================================

    /**
     * Get full page content
     */
    getContent() {
        return this.deepClone(this.state.content);
    },

    /**
     * Get currently selected section data
     */
    getSelectedSection() {
        if (this.state.selectedType !== 'section' || !this.state.selected) {
            // If no section selected, return first section or null
            if (this.state.content.sections && this.state.content.sections.length > 0) {
                return this.deepClone(this.state.content.sections[0]);
            }
            return null;
        }
        const section = this.state.content.sections.find(s => s.id === this.state.selected);
        return section ? this.deepClone(section) : null;
    },

    /**
     * Get currently selected row data
     */
    getSelectedRow() {
        if (this.state.selectedType !== 'row' || !this.state.selected) {
            return null;
        }
        const element = this.findElement(this.state.selected);
        return element ? this.deepClone(element) : null;
    },

    /**
     * Get currently selected module data
     */
    getSelectedModule() {
        if (this.state.selectedType !== 'module' || !this.state.selected) {
            return null;
        }
        const element = this.findElement(this.state.selected);
        return element ? this.deepClone(element) : null;
    },

    /**
     * Insert content from library (section/row/module)
     */
    insertContent(content, type) {
        console.log('[TB4] insertContent() called with type:', type);

        if (type === 'section' && content) {
            // Generate new IDs to avoid conflicts
            const newSection = this.deepClone(content);
            newSection.id = 's_' + this.generateId();
            this.regenerateIds(newSection);

            if (!this.state.content.sections) {
                this.state.content.sections = [];
            }
            this.state.content.sections.push(newSection);
            this.renderCanvas();
            this.pushHistory();
            return newSection;
        }

        if (type === 'row' && content && this.state.selectedType === 'section') {
            const section = this.findElement(this.state.selected);
            if (section && section.rows) {
                const newRow = this.deepClone(content);
                newRow.id = 'r_' + this.generateId();
                this.regenerateIds(newRow);
                section.rows.push(newRow);
                this.renderCanvas();
                this.pushHistory();
                return newRow;
            }
        }

        if (type === 'module' && content && this.state.selectedType === 'column') {
            const column = this.findElement(this.state.selected);
            if (column && column.modules) {
                const newModule = this.deepClone(content);
                newModule.id = 'm_' + this.generateId();
                column.modules.push(newModule);
                this.renderCanvas();
                this.pushHistory();
                return newModule;
            }
        }

        return null;
    },

    startAutoSave() {
        if (this.state.autoSaveTimer) {
            clearInterval(this.state.autoSaveTimer);
        }

        this.state.autoSaveTimer = setInterval(() => {
            if (this.state.isDirty && !this.state.isLoading) {
                this.saveDraft();
            }
        }, this.state.autoSaveInterval);
    },

    /**
     * Save draft to server
     */
    async saveDraft() {
        try {
            await fetch(`${this.config.apiUrl}?action=save_draft`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    page_id: this.config.pageId,
                    content: this.state.content,
                    csrf_token: this.config.csrfToken
                })
            });
            console.log('Draft auto-saved');
        } catch (error) {
            console.error('Auto-save failed:', error);
        }
    },

    /**
     * Preview the page
     */
    preview() {
        // Open preview in new window
        const previewUrl = `${this.config.apiUrl}/preview?id=${this.config.pageId}`;
        window.open(previewUrl, '_blank');
    },

    /**
     * Update save button text
     */
    updateSaveButton(text) {
        // Try #saveButtonText span first (edit.php structure)
        const textSpan = document.getElementById('saveButtonText');
        if (textSpan) {
            textSpan.textContent = text;
            return;
        }
        // Fallback to data-action selector
        const saveBtn = this.dom.toolbar?.querySelector('[data-action="save"]');
        if (saveBtn) {
            saveBtn.textContent = text;
        }
    },

    // ==========================================================================
    // MEDIA PICKER
    // ==========================================================================

    /**
     * Open media picker
     */
    openMediaPicker(fieldName) {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'tb4-modal tb4-media-picker-modal';
        modal.innerHTML = `
            <div class="tb4-modal-content">
                <div class="tb4-modal-header">
                    <h3>Select Image</h3>
                    <button type="button" class="tb4-modal-close" onclick="this.closest('.tb4-modal').remove()">×</button>
                </div>
                <div class="tb4-modal-body">
                    <div class="tb4-media-tabs">
                        <button type="button" class="tb4-media-tab active" data-tab="upload">Upload</button>
                        <button type="button" class="tb4-media-tab" data-tab="library">Media Library</button>
                        <button type="button" class="tb4-media-tab" data-tab="url">External URL</button>
                    </div>
                    <div class="tb4-media-content" data-content="upload">
                        <div class="tb4-upload-area">
                            <input type="file" accept="image/*" id="tb4-image-upload"/>
                            <label for="tb4-image-upload">
                                <span>Drop image here or click to upload</span>
                            </label>
                        </div>
                    </div>
                    <div class="tb4-media-content" data-content="library" style="display:none;">
                        <div class="tb4-media-grid" id="tb4-media-grid">
                            <p>Loading media library...</p>
                        </div>
                    </div>
                    <div class="tb4-media-content" data-content="url" style="display:none;">
                        <input type="text" class="tb4-input" placeholder="Enter image URL" id="tb4-image-url"/>
                        <button type="button" class="tb4-btn tb4-btn-primary" onclick="TB4Builder.insertImageUrl('${fieldName}')">Insert</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Tab switching
        modal.querySelectorAll('.tb4-media-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                modal.querySelectorAll('.tb4-media-tab').forEach(t => t.classList.remove('active'));
                modal.querySelectorAll('.tb4-media-content').forEach(c => c.style.display = 'none');
                tab.classList.add('active');
                modal.querySelector(`[data-content="${tab.dataset.tab}"]`).style.display = 'block';

                if (tab.dataset.tab === 'library') {
                    this.loadMediaLibrary();
                }
            });
        });

        // File upload
        const fileInput = modal.querySelector('#tb4-image-upload');
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.uploadImage(e.target.files[0], fieldName, modal);
            }
        });

        // Store current field name
        modal.dataset.fieldName = fieldName;
    },

    /**
     * Load media library
     */
    async loadMediaLibrary() {
        const grid = document.getElementById('tb4-media-grid');
        if (!grid) return;

        try {
            const response = await fetch(`${this.config.apiUrl}/media?limit=50`, {
                headers: {
                    'X-CSRF-Token': this.config.csrfToken
                }
            });
            const result = await response.json();

            if (result.success && result.data) {
                let html = '';
                result.data.forEach(item => {
                    html += `
                        <div class="tb4-media-item" onclick="TB4Builder.selectMediaItem('${item.url}')">
                            <img src="${this.escapeHtml(item.thumbnail || item.url)}" alt="${this.escapeHtml(item.name)}"/>
                        </div>
                    `;
                });
                grid.innerHTML = html || '<p>No images found</p>';
            }
        } catch (error) {
            grid.innerHTML = '<p>Failed to load media library</p>';
        }
    },

    /**
     * Upload an image
     */
    async uploadImage(file, fieldName, modal) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', this.config.csrfToken);

        try {
            const response = await fetch(`${this.config.apiUrl}/upload`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: formData
            });

            const result = await response.json();

            if (result.success && result.url) {
                this.setFieldValue(fieldName, result.url);
                modal.remove();
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            this.showNotification('Upload failed: ' + error.message, 'error');
        }
    },

    /**
     * Select media item from library
     */
    selectMediaItem(url) {
        const modal = document.querySelector('.tb4-media-picker-modal');
        if (modal) {
            const fieldName = modal.dataset.fieldName;
            this.setFieldValue(fieldName, url);
            modal.remove();
        }
    },

    /**
     * Insert image from URL
     */
    insertImageUrl(fieldName) {
        const urlInput = document.getElementById('tb4-image-url');
        if (urlInput && urlInput.value) {
            this.setFieldValue(fieldName, urlInput.value);
            document.querySelector('.tb4-media-picker-modal')?.remove();
        }
    },

    /**
     * Clear image field
     */
    clearImage(fieldName) {
        this.setFieldValue(fieldName, '');
    },

    /**
     * Set a field value and trigger update
     */
    setFieldValue(fieldName, value) {
        const input = this.dom.settingsPanel?.querySelector(`[name="${fieldName}"]`);
        if (!input) {
            console.warn('[TB4] setFieldValue: Input not found for field:', fieldName);
            return;
        }

        input.value = value;
        console.log('[TB4] setFieldValue:', fieldName, '=', value);

        // Update image preview if it's an image/upload field
        const container = input.closest('.tb4-image-picker');
        if (container) {
            const preview = container.querySelector('.tb4-image-preview');
            if (preview) {
                if (value) {
                    preview.innerHTML = `<img src="${this.escapeHtml(value)}" style="max-width:100%;height:auto;border-radius:8px;"/>`;
                } else {
                    preview.innerHTML = '<span style="color:#6b7280;font-size:13px;">No image selected</span>';
                }
            }
            const removeBtn = container.querySelector('.tb4-btn-danger');
            if (removeBtn) {
                removeBtn.style.display = value ? '' : 'none';
            }
        }

        // Update icon preview if it's an icon field
        const iconContainer = input.closest('.tb4-icon-picker');
        if (iconContainer) {
            const preview = iconContainer.querySelector('.tb4-icon-preview');
            if (preview && value) {
                const iconPath = this.getIconSvgPath(value);
                preview.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${iconPath}</svg>`;
            }
        }

        input.dispatchEvent(new Event('change', { bubbles: true }));
    },

    // ==========================================================================
    // ICON PICKER
    // ==========================================================================

    /**
     * Open icon picker - uses Lucide icons matching getIconSvgPath()
     */
    openIconPicker(fieldName) {
        // Lucide icon names matching getIconSvgPath()
        const icons = [
            'star', 'heart', 'check', 'x', 'plus', 'minus',
            'search', 'menu', 'settings', 'home', 'user',
            'arrow-right', 'arrow-left', 'mail', 'phone', 'map-pin',
            'globe', 'clock', 'calendar', 'link', 'eye',
            'bell', 'lock', 'shield', 'check-circle', 'alert-circle',
            'info', 'zap', 'award', 'coffee', 'smile',
            'rocket', 'flame', 'lightbulb', 'target', 'image',
            'video', 'music', 'play', 'share', 'download', 'upload'
        ];

        const self = this;
        const modal = document.createElement('div');
        modal.className = 'tb4-modal tb4-icon-picker-modal';
        modal.innerHTML = `
            <div class="tb4-modal-content">
                <div class="tb4-modal-header">
                    <h3>Select Icon</h3>
                    <button type="button" class="tb4-modal-close" onclick="this.closest('.tb4-modal').remove()">×</button>
                </div>
                <div class="tb4-modal-body">
                    <div class="tb4-icon-grid">
                        ${icons.map(icon => `
                            <button type="button" class="tb4-icon-option" data-icon="${icon}" onclick="TB4Builder.selectIcon('${fieldName}', '${icon}')" title="${icon}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    ${self.getIconSvgPath(icon)}
                                </svg>
                            </button>
                        `).join('')}
                    </div>
                    <div class="tb4-icon-custom">
                        <input type="text" class="tb4-input" placeholder="Or enter icon name (e.g., star, heart)" id="tb4-custom-icon"/>
                        <button type="button" class="tb4-btn tb4-btn-sm" onclick="TB4Builder.selectIcon('${fieldName}', document.getElementById('tb4-custom-icon').value)">Use</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    },

    /**
     * Select an icon
     */
    selectIcon(fieldName, iconClass) {
        this.setFieldValue(fieldName, iconClass);
        document.querySelector('.tb4-icon-picker-modal')?.remove();
    },

    // ==========================================================================
    // UTILITY FUNCTIONS
    // ==========================================================================

    /**
     * Generate unique ID
     */
    generateId() {
        return 'tb4_' + Date.now().toString(36) + '_' + Math.random().toString(36).substr(2, 9);
    },

    /**
     * Deep clone an object
     */
    deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    },

    /**
     * Regenerate IDs for cloned element
     */
    regenerateIds(element) {
        element.id = this.generateId();

        if (element.rows) {
            element.rows.forEach(row => this.regenerateIds(row));
        }
        if (element.columns) {
            element.columns.forEach(col => this.regenerateIds(col));
        }
        if (element.modules) {
            element.modules.forEach(mod => this.regenerateIds(mod));
        }
    },

    /**
     * Find element by ID in content tree
     */
    findElement(elementId) {
        return this.searchElement(this.state.content.sections, elementId);
    },

    /**
     * Recursively search for element
     */
    searchElement(items, elementId) {
        if (!items) return null;

        for (const item of items) {
            if (item.id === elementId) return item;

            if (item.rows) {
                const found = this.searchElement(item.rows, elementId);
                if (found) return found;
            }
            if (item.columns) {
                const found = this.searchElement(item.columns, elementId);
                if (found) return found;
            }
            if (item.modules) {
                const found = this.searchElement(item.modules, elementId);
                if (found) return found;
            }
        }

        return null;
    },

    /**
     * Find parent of element
     */
    findParent(elementId) {
        return this.searchParent(this.state.content.sections, elementId, null);
    },

    /**
     * Recursively search for parent
     */
    searchParent(items, elementId, parent) {
        if (!items) return null;

        for (const item of items) {
            if (item.id === elementId) return parent;

            if (item.rows) {
                const found = this.searchParent(item.rows, elementId, item);
                if (found) return found;
            }
            if (item.columns) {
                const found = this.searchParent(item.columns, elementId, item);
                if (found) return found;
            }
            if (item.modules) {
                const found = this.searchParent(item.modules, elementId, item);
                if (found) return found;
            }
        }

        return null;
    },

    /**
     * Switch tab in tabs module preview
     */
    switchTab(btn, index) {
        const tabsContainer = btn.closest('.tb4-tabs');
        if (!tabsContainer) return;
        
        // Update nav buttons
        tabsContainer.querySelectorAll('.tb4-tab-btn').forEach((b, i) => {
            b.classList.toggle('active', i === index);
            b.style.borderBottom = i === index ? '2px solid #2563eb' : 'none';
            b.style.color = i === index ? '#2563eb' : '#6b7280';
            b.style.marginBottom = i === index ? '-2px' : '0';
        });
        
        // Update content panes
        tabsContainer.querySelectorAll('.tb4-tab-pane').forEach((pane, i) => {
            pane.style.display = i === index ? '' : 'none';
        });
    },

    /**
     * Slider navigation - go to previous slide
     */
    sliderPrev(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        let current = parseInt(slider.dataset.current) || 0;
        const total = parseInt(slider.dataset.total) || 1;
        
        current = (current - 1 + total) % total;
        this.goToSlide(slider, current);
    },

    /**
     * Slider navigation - go to next slide
     */
    sliderNext(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        let current = parseInt(slider.dataset.current) || 0;
        const total = parseInt(slider.dataset.total) || 1;
        
        current = (current + 1) % total;
        this.goToSlide(slider, current);
    },

    /**
     * Go to specific slide
     */
    goToSlide(slider, index) {
        slider.dataset.current = index;
        
        // Update slides visibility
        slider.querySelectorAll('.tb4-slide').forEach((slide, i) => {
            slide.style.display = i === index ? '' : 'none';
        });
        
        // Update counter
        const counter = slider.querySelector('.tb4-slider-counter');
        if (counter) {
            counter.textContent = `Slide ${index + 1} of ${slider.dataset.total}`;
        }
    },

    /**
     * Post slider navigation - previous
     */
    postSliderPrev(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        let current = parseInt(slider.dataset.current) || 0;
        const total = parseInt(slider.dataset.total) || 1;
        
        current = (current - 1 + total) % total;
        slider.dataset.current = current;
        
        // Update dots
        slider.querySelectorAll('.tb4-post-slider-preview > div:last-child > span').forEach((dot, i) => {
            dot.style.background = i === current ? '#2563eb' : '#d1d5db';
        });
    },

    /**
     * Post slider navigation - next
     */
    postSliderNext(sliderId) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        let current = parseInt(slider.dataset.current) || 0;
        const total = parseInt(slider.dataset.total) || 1;
        
        current = (current + 1) % total;
        slider.dataset.current = current;
        
        // Update dots
        slider.querySelectorAll('.tb4-post-slider-preview > div:last-child > span').forEach((dot, i) => {
            dot.style.background = i === current ? '#2563eb' : '#d1d5db';
        });
    },

    /**
     * Fullwidth slider navigation - previous
     */
    fwSliderPrev(sliderId) {
        this.sliderPrev(sliderId);
    },

    /**
     * Fullwidth slider navigation - next  
     */
    fwSliderNext(sliderId) {
        this.sliderNext(sliderId);
    },

    /**
     * Toggle module - toggle open/close
     */
    toggleToggle(header) {
        const wrapper = header.parentElement;
        if (!wrapper) return;
        
        const content = wrapper.querySelector('.tb4-toggle-content');
        const icon = header.querySelector('.tb4-toggle-icon');
        
        if (!content) return;
        
        const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
        
        if (isOpen) {
            content.style.maxHeight = '0';
            content.style.padding = '0 16px';
            if (icon) icon.style.transform = '';
        } else {
            content.style.maxHeight = '500px';
            content.style.padding = '0 16px';
            if (icon) icon.style.transform = 'rotate(90deg)';
        }
    },

    /**
     * Fullwidth slider navigation (prev/next)
     */
    fwSliderNav(sliderId, direction) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        let current = parseInt(slider.dataset.current) || 0;
        const total = parseInt(slider.dataset.total) || 1;
        
        current = (current + direction + total) % total;
        this.fwSliderGoTo(sliderId, current);
    },

    /**
     * Fullwidth slider - go to specific slide
     */
    fwSliderGoTo(sliderId, index) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        
        slider.dataset.current = index;
        
        // Update slides visibility
        slider.querySelectorAll('[data-index]').forEach((slide, i) => {
            slide.style.display = i === index ? (slide.classList.contains('tb4-fwps-slide') ? 'block' : 'flex') : 'none';
        });
        
        // Update dots
        const dotsContainer = slider.querySelector('.tb4-fws-dots, .tb4-fwps-dots');
        if (dotsContainer) {
            dotsContainer.querySelectorAll('span').forEach((dot, i) => {
                if (i === index) {
                    dot.style.background = 'white';
                    dot.style.transform = 'scale(1.2)';
                } else {
                    dot.style.background = 'rgba(255,255,255,0.5)';
                    dot.style.transform = '';
                }
            });
        }
    },

    /**
     * Portfolio filter - switch active filter button
     */
    portfolioFilter(btn) {
        const container = btn.closest('.tb4-portfolio-preview, .tb4-fw-portfolio-preview');
        if (!container) return;
        
        const activeBg = container.dataset.activeBg || '#2563eb';
        const activeText = container.dataset.activeText || '#ffffff';
        const normalBg = container.dataset.bg || '#f3f4f6';
        const normalText = container.dataset.text || '#374151';
        
        // Update all buttons in the filter
        const filterContainer = btn.parentElement;
        filterContainer.querySelectorAll('button').forEach(b => {
            b.classList.remove('active');
            b.style.background = normalBg;
            b.style.color = normalText;
        });
        
        // Activate clicked button
        btn.classList.add('active');
        btn.style.background = activeBg;
        btn.style.color = activeText;
    },

    /**
     * Refresh Lucide icons with retry mechanism
     */
    refreshLucideIcons(retries = 5) {
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
            console.log('[TB4] Lucide icons refreshed');
        } else if (retries > 0) {
            setTimeout(() => this.refreshLucideIcons(retries - 1), 100);
        }
    },

    /**
     * Escape HTML entities
     */
    escapeHtml(str) {
        if (typeof str !== 'string') return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Get Lucide icon SVG path by name
     * Subset of icons matching the PHP iconmodule.php
     */
    getIconSvgPath(name) {
        const icons = {
            'heart': '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            'star': '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'check': '<path d="M20 6 9 17l-5-5"/>',
            'x': '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'plus': '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'minus': '<path d="M5 12h14"/>',
            'search': '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'menu': '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
            'settings': '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            'home': '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'user': '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'arrow-right': '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
            'arrow-left': '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'mail': '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'phone': '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
            'map-pin': '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'globe': '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
            'clock': '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'calendar': '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
            'link': '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
            'eye': '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            'bell': '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
            'lock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'shield': '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'alert-circle': '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'info': '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
            'zap': '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'award': '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'coffee': '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/>',
            'smile': '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
            'rocket': '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
            'flame': '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
            'lightbulb': '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
            'target': '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
            'image': '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'video': '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            'music': '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
            'play': '<polygon points="5 3 19 12 5 21 5 3"/>',
            'share': '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>',
            'download': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
            'upload': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>'
        };
        return icons[name] || icons['star'];
    },

    /**
     * Build inline styles from settings
     */
    buildInlineStyles(settings) {
        const styles = [];

        // Background
        if (settings.backgroundColor) {
            styles.push(`background-color: ${settings.backgroundColor}`);
        }
        if (settings.backgroundImage) {
            styles.push(`background-image: url('${settings.backgroundImage}')`);
            styles.push('background-size: cover');
            styles.push('background-position: center');
        }
        if (settings.backgroundType === 'gradient' && settings.gradientStart && settings.gradientEnd) {
            const dir = settings.gradientDirection || '180deg';
            styles.push(`background: linear-gradient(${dir}, ${settings.gradientStart}, ${settings.gradientEnd})`);
        }

        // Padding
        if (settings.padding) {
            const p = settings.padding;
            if (typeof p === 'object') {
                styles.push(`padding: ${p.top || 0}px ${p.right || 0}px ${p.bottom || 0}px ${p.left || 0}px`);
            } else {
                styles.push(`padding: ${p}px`);
            }
        }

        // Margin
        if (settings.margin) {
            const m = settings.margin;
            if (typeof m === 'object') {
                styles.push(`margin: ${m.top || 0}px ${m.right || 0}px ${m.bottom || 0}px ${m.left || 0}px`);
            } else {
                styles.push(`margin: ${m}px`);
            }
        }

        // Border
        if (settings.borderStyle && settings.borderStyle !== 'none') {
            styles.push(`border-style: ${settings.borderStyle}`);

            // Border width
            if (settings.borderWidth) {
                const bw = settings.borderWidth;
                if (typeof bw === 'object') {
                    styles.push(`border-width: ${bw.top || 0}px ${bw.right || 0}px ${bw.bottom || 0}px ${bw.left || 0}px`);
                } else {
                    styles.push(`border-width: ${bw}px`);
                }
            }

            // Border color
            if (settings.borderColor) {
                styles.push(`border-color: ${settings.borderColor}`);
            }
        }

        // Border radius
        if (settings.borderRadius) {
            const br = settings.borderRadius;
            if (typeof br === 'object') {
                styles.push(`border-radius: ${br.topLeft || 0}px ${br.topRight || 0}px ${br.bottomRight || 0}px ${br.bottomLeft || 0}px`);
            } else {
                styles.push(`border-radius: ${br}px`);
            }
        }

        // Box Shadow
        if (settings.boxShadowEnabled) {
            const h = settings.boxShadowH || 0;
            const v = settings.boxShadowV || 4;
            const blur = settings.boxShadowBlur || 10;
            const spread = settings.boxShadowSpread || 0;
            const color = settings.boxShadowColor || 'rgba(0,0,0,0.15)';
            const inset = settings.boxShadowInset ? 'inset ' : '';
            styles.push(`box-shadow: ${inset}${h}px ${v}px ${blur}px ${spread}px ${color}`);
        }

        // Opacity
        if (settings.opacity !== undefined && settings.opacity !== 100) {
            styles.push(`opacity: ${settings.opacity / 100}`);
        }

        // Position
        if (settings.position && settings.position !== 'static') {
            styles.push(`position: ${settings.position}`);
            if (settings.positionTop) styles.push(`top: ${settings.positionTop}`);
            if (settings.positionRight) styles.push(`right: ${settings.positionRight}`);
            if (settings.positionBottom) styles.push(`bottom: ${settings.positionBottom}`);
            if (settings.positionLeft) styles.push(`left: ${settings.positionLeft}`);
            if (settings.zIndex) styles.push(`z-index: ${settings.zIndex}`);
        }

        // Transform
        const transforms = [];
        if (settings.translateX && settings.translateX !== '0') transforms.push(`translateX(${settings.translateX})`);
        if (settings.translateY && settings.translateY !== '0') transforms.push(`translateY(${settings.translateY})`);
        if (settings.rotate && settings.rotate !== 0) transforms.push(`rotate(${settings.rotate}deg)`);
        if (settings.scaleX && settings.scaleX !== 1) transforms.push(`scaleX(${settings.scaleX})`);
        if (settings.scaleY && settings.scaleY !== 1) transforms.push(`scaleY(${settings.scaleY})`);
        if (settings.skewX && settings.skewX !== 0) transforms.push(`skewX(${settings.skewX}deg)`);
        if (settings.skewY && settings.skewY !== 0) transforms.push(`skewY(${settings.skewY}deg)`);
        if (transforms.length > 0) {
            styles.push(`transform: ${transforms.join(' ')}`);
            if (settings.transformOrigin) {
                styles.push(`transform-origin: ${settings.transformOrigin}`);
            }
        }

        // CSS Filters
        const filters = [];
        if (settings.filterBlur && settings.filterBlur !== 0) filters.push(`blur(${settings.filterBlur}px)`);
        if (settings.filterBrightness && settings.filterBrightness !== 100) filters.push(`brightness(${settings.filterBrightness}%)`);
        if (settings.filterContrast && settings.filterContrast !== 100) filters.push(`contrast(${settings.filterContrast}%)`);
        if (settings.filterGrayscale && settings.filterGrayscale !== 0) filters.push(`grayscale(${settings.filterGrayscale}%)`);
        if (settings.filterSaturate && settings.filterSaturate !== 100) filters.push(`saturate(${settings.filterSaturate}%)`);
        if (settings.filterHueRotate && settings.filterHueRotate !== 0) filters.push(`hue-rotate(${settings.filterHueRotate}deg)`);
        if (settings.filterInvert && settings.filterInvert !== 0) filters.push(`invert(${settings.filterInvert}%)`);
        if (settings.filterSepia && settings.filterSepia !== 0) filters.push(`sepia(${settings.filterSepia}%)`);
        if (filters.length > 0) {
            styles.push(`filter: ${filters.join(' ')}`);
        }

        // Overflow
        if (settings.overflow) {
            styles.push(`overflow: ${settings.overflow}`);
        }

        // Cursor
        if (settings.cursor) {
            styles.push(`cursor: ${settings.cursor}`);
        }

        return styles.join('; ');
    },

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `tb4-notification tb4-notification-${type}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove after delay
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    /**
     * Get export data
     */
    getExportData() {
        return {
            version: '1.0',
            content: this.state.content,
            timestamp: new Date().toISOString()
        };
    },

    /**
     * Import data
     */

    // ==========================================================================
    // LAYOUT PICKER
    // ==========================================================================

    /**
     * Show layout picker modal for adding new row
     */
    showLayoutPicker(sectionId) {
        this.state.pendingRowSectionId = sectionId;
        this.state.pendingChangeLayoutRowId = null;
        const modal = document.getElementById('layoutPickerModal');
        if (modal) {
            modal.classList.add('active');
        }
    },

    /**
     * Show layout picker modal for changing existing row layout
     */
    showLayoutPickerForRow(rowId) {
        this.state.pendingRowSectionId = null;
        this.state.pendingChangeLayoutRowId = rowId;
        const modal = document.getElementById('layoutPickerModal');
        if (modal) {
            modal.classList.add('active');
        }
    },

    /**
     * Hide layout picker modal
     */
    hideLayoutPicker() {
        const modal = document.getElementById('layoutPickerModal');
        if (modal) {
            modal.classList.remove('active');
        }
        this.state.pendingRowSectionId = null;
        this.state.pendingChangeLayoutRowId = null;
    },

    /**
     * Initialize layout picker click handlers
     */
    initLayoutPicker() {
        const modal = document.getElementById('layoutPickerModal');
        if (!modal) return;

        // Close on backdrop click
        const backdrop = modal.querySelector('.tb4-layout-modal-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', () => this.hideLayoutPicker());
        }

        // Handle layout choice clicks
        modal.querySelectorAll('.tb4-layout-choice').forEach(btn => {
            btn.addEventListener('click', () => {
                const layout = btn.dataset.layout;
                if (this.state.pendingRowSectionId) {
                    // Adding new row
                    this.addRowWithLayout(this.state.pendingRowSectionId, layout);
                } else if (this.state.pendingChangeLayoutRowId) {
                    // Changing existing row layout
                    this.changeRowLayout(this.state.pendingChangeLayoutRowId, layout);
                }
                this.hideLayoutPicker();
            });
        });

        console.log('[TB4] Layout picker initialized');
    },

    importData(data) {
        if (data && data.content) {
            this.loadContent(data.content);
            this.pushHistory();
        }
    },

    /**
     * Self-test method to verify all critical components are loaded
     * Runs on initialization to help debug issues
     */
    _runSelfTest() {
        console.log('[TB4] Running self-test...');
        console.log('[TB4] TB4Builder loaded:', typeof TB4Builder !== 'undefined');
        console.log('[TB4] TB4Fields loaded:', typeof TB4Fields !== 'undefined');
        console.log('[TB4] TB4Icons loaded:', typeof TB4Icons !== 'undefined');

        if (typeof TB4Fields !== 'undefined') {
            console.log('[TB4] TB4Fields methods available:', Object.keys(TB4Fields).filter(k => typeof TB4Fields[k] === 'function'));
        }

        // Check critical methods exist
        const criticalMethods = ['switchBgTab', 'syncSpacingFromSlider', 'updateSpacingFromBox', 'syncBorderWidth', 'updateBorderWidthFromInput', 'toggleSpacingLink', 'updateShadowPreview'];
        criticalMethods.forEach(method => {
            if (typeof this[method] !== 'function') {
                console.error('[TB4] MISSING CRITICAL METHOD:', method);
            } else {
                console.log('[TB4] Method OK:', method);
            }
        });

        console.log('[TB4] Self-test complete');
    }
};

// ==========================================================================
// AUTO-INITIALIZE
// ==========================================================================

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TB4Builder.init());
} else {
    TB4Builder.init();
}

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', (e) => {
    if (TB4Builder.state.isDirty) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TB4Builder;
}
