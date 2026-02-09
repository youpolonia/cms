/**
 * JTB Builder JavaScript
 * Main builder functionality
 */

(function() {
    'use strict';

    // Global JTB object
    window.JTB = window.JTB || {};

    // ========================================
    // Debug Mode
    // ========================================

    // Set to true to enable console logging (can be overridden in browser console)
    JTB.DEBUG = false;

    // Debug logging helper
    JTB.log = function(...args) {
        if (JTB.DEBUG) {
            // debug disabled
        }
    };

    JTB.warn = function(...args) {
        if (JTB.DEBUG) {
            console.warn('[JTB]', ...args);
        }
    };

    JTB.error = function(...args) {
        // Errors always logged
        console.error('[JTB]', ...args);
    };

    // ========================================
    // Configuration
    // ========================================

    JTB.config = {
        apiUrl: '/api/jtb',
        postId: null,
        csrfToken: null,
        modules: {},
        templateType: null, // 'header', 'footer', 'body', etc. - for Theme Builder
        breakpoints: {
            desktop: 1200,
            tablet: 980,
            phone: 767
        }
    };

    // ========================================
    // State
    // ========================================

    JTB.state = {
        content: null,
        selectedModule: null,
        selectedType: null,
        selectedIndexes: null,
        isDirty: false,
        currentDevice: 'desktop',
        history: [],
        historyIndex: -1
    };

    // ========================================
    // Initialization
    // ========================================

    JTB.init = function(options) {
        // Merge options
        Object.assign(JTB.config, options);

        // Check if modules and content are already provided (e.g., from template editor)
        const modulesProvided = options.modules && Object.keys(options.modules).length > 0;
        const contentProvided = options.content !== undefined;

        // Build initialization promise chain
        let initPromise = Promise.resolve();

        if (modulesProvided) {
            // Use provided modules
            JTB.config.modules = options.modules;
            // Extract categories from modules
            JTB.config.categories = JTB.extractCategories(options.modules);
        } else {
            // Load modules from API
            initPromise = initPromise.then(() => JTB.loadModules());
        }

        if (contentProvided) {
            // Use provided content
            JTB.state.content = options.content || JTB.getEmptyContent();
        } else if (JTB.config.postId) {
            // Load content from API only if postId is set
            initPromise = initPromise.then(() => JTB.loadContent());
        } else {
            // No postId, use empty content
            JTB.state.content = JTB.getEmptyContent();
        }

        // Complete initialization
        initPromise
            .then(() => {
                // Verify Feather Icons availability
                JTB.verifyFeatherIcons();

                JTB.renderCanvas();
                JTB.bindEvents();
                JTB.saveHistory();

                JTB.log('Builder initialized successfully');
            })
            .catch(error => {
                JTB.error('Init Error:', error);
                JTB.showNotification('Failed to initialize builder', 'error');
            });
    };

    /**
     * Verify Feather Icons are loaded
     */
    JTB.verifyFeatherIcons = function() {
        if (typeof JTB.getFeatherIcon !== 'function') {
            JTB.warn('Feather Icons not loaded - falling back to emoji icons');
            return false;
        }

        // Test icon generation
        const testIcon = JTB.getFeatherIcon('check', 16);
        if (!testIcon || testIcon.indexOf('<svg') === -1) {
            JTB.warn('Feather Icons function exists but not returning valid SVG');
            return false;
        }

        JTB.log('Feather Icons verified successfully');
        return true;
    };

    /**
     * Extract categories from modules object
     */
    JTB.extractCategories = function(modules) {
        const categories = {};
        const categoryLabels = {
            'structure': 'Structure',
            'content': 'Content',
            'media': 'Media',
            'interactive': 'Interactive',
            'forms': 'Forms',
            'blog': 'Blog',
            'fullwidth': 'Fullwidth',
            // Theme Builder categories (split by purpose)
            'header': 'Header',
            'footer': 'Footer',
            'dynamic': 'Dynamic Content',
            // Legacy - keep for backwards compatibility
            'theme': 'Theme Builder',
            'other': 'Other'
        };

        for (const slug in modules) {
            if (modules.hasOwnProperty(slug)) {
                const mod = modules[slug];
                const cat = mod.category || 'other';
                if (!categories[cat]) {
                    categories[cat] = {
                        name: cat,
                        label: categoryLabels[cat] || cat.charAt(0).toUpperCase() + cat.slice(1),
                        modules: []
                    };
                }
                categories[cat].modules.push(slug);
            }
        }
        return categories;
    };

    // ========================================
    // API Methods
    // ========================================

    JTB.api = {
        get: function(endpoint) {
            return fetch(JTB.config.apiUrl + endpoint, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .catch(error => {
                console.error('[JTB API GET] ' + endpoint + ':', error.message);
                JTB.notify && JTB.notify('Connection error: ' + error.message, 'error');
                return { success: false, error: error.message };
            });
        },

        post: function(endpoint, data) {
            const formData = new FormData();

            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    formData.append(key, data[key]);
                }
            }

            formData.append('csrf_token', JTB.config.csrfToken);

            return fetch(JTB.config.apiUrl + endpoint, {
                method: 'POST',
                credentials: 'include',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .catch(error => {
                console.error('[JTB API POST] ' + endpoint + ':', error.message);
                JTB.notify && JTB.notify('Connection error: ' + error.message, 'error');
                return { success: false, error: error.message };
            });
        }
    };

    // ========================================
    // Data Loading
    // ========================================

    JTB.loadModules = function() {
        return JTB.api.get('/modules').then(response => {
            if (response.success) {
                JTB.config.modules = response.data.modules;
                JTB.config.categories = response.data.categories;
            } else {
                throw new Error(response.error || 'Failed to load modules');
            }
        });
    };

    JTB.loadContent = function() {
        // Build URL with type parameter
        const postType = JTB.config.postType || 'page';
        const url = '/load/' + JTB.config.postId + '?type=' + postType;

        return JTB.api.get(url).then(response => {
            if (response.success) {
                JTB.state.content = response.data.content;

                // Store original content info for import modal
                JTB.state.hasJtbContent = response.data.has_content;
                JTB.state.originalContent = response.data.original_content;
                JTB.state.postType = response.data.post_type || postType;
            } else {
                throw new Error(response.error || 'Failed to load content');
            }
        });
    };

    JTB.saveContent = function() {
        const contentJson = JSON.stringify(JTB.state.content);

        return JTB.api.post('/save', {
            post_id: JTB.config.postId,
            content: contentJson
        }).then(response => {
            if (response.success) {
                JTB.state.isDirty = false;
                JTB.showNotification('Content saved successfully', 'success');
            } else {
                throw new Error(response.error || 'Failed to save content');
            }
        }).catch(error => {
            JTB.showNotification('Failed to save: ' + error.message, 'error');
        });
    };

    /**
     * Get current content
     */
    JTB.getContent = function() {
        return JTB.state.content || JTB.getEmptyContent();
    };

    // ========================================
    // Content Structure Helpers
    // ========================================

    JTB.getEmptyContent = function() {
        return {
            version: '1.0',
            content: []
        };
    };

    JTB.generateId = function(prefix) {
        return prefix + '_' + Math.random().toString(36).substr(2, 9);
    };

    JTB.createSection = function() {
        return {
            type: 'section',
            id: JTB.generateId('section'),
            attrs: {
                fullwidth: false,
                inner_width: 1200
            },
            children: [JTB.createRow('1')]
        };
    };

    /**
     * Parse column widths from columns string (e.g., "1_2,1_2" -> ["50%", "50%"])
     * Format: "1" = full width, "1_2" = 50%, "1_3" = 33.33%, "1_4" = 25%, etc.
     */
    JTB.parseColumnWidths = function(columnsStr) {
        if (!columnsStr || columnsStr === '1') {
            return ['100%'];
        }

        const columnMap = {
            '1': '100%',
            '1_2': '50%',
            '1_3': '33.333%',
            '2_3': '66.666%',
            '1_4': '25%',
            '3_4': '75%',
            '1_5': '20%',
            '2_5': '40%',
            '3_5': '60%',
            '4_5': '80%',
            '1_6': '16.666%',
            '5_6': '83.333%'
        };

        return columnsStr.split(',').map(col => columnMap[col.trim()] || '100%');
    };

    JTB.createRow = function(columns) {
        const row = {
            type: 'row',
            id: JTB.generateId('row'),
            attrs: {
                columns: columns || '1',
                column_gap: 30,
                equal_heights: true
            },
            children: []
        };

        // Create columns based on structure
        const colCount = (columns || '1').split(',').length;
        for (let i = 0; i < colCount; i++) {
            row.children.push(JTB.createColumn());
        }

        return row;
    };

    JTB.createColumn = function() {
        return {
            type: 'column',
            id: JTB.generateId('column'),
            attrs: {},
            children: []
        };
    };

    JTB.createModule = function(type) {
        const moduleConfig = JTB.config.modules[type];
        if (!moduleConfig) return null;

        // Start with default styles from JTB_Default_Styles (via API)
        const attrs = moduleConfig.defaults ? { ...moduleConfig.defaults } : {};

        // Override with field definitions defaults (for fields not in defaults)
        if (moduleConfig.fields && moduleConfig.fields.content) {
            for (const fieldName in moduleConfig.fields.content) {
                const field = moduleConfig.fields.content[fieldName];
                if (field.default !== undefined && attrs[fieldName] === undefined) {
                    attrs[fieldName] = field.default;
                }
            }
        }

        return {
            type: type,
            id: JTB.generateId(type),
            attrs: attrs,
            children: []
        };
    };

    // ========================================
    // Canvas Rendering
    // ========================================

    JTB.renderCanvas = function() {
        const canvas = document.querySelector('.jtb-canvas-inner');
        if (!canvas) return;

        canvas.innerHTML = '';

        const sections = JTB.state.content.content || [];

        if (sections.length === 0) {
            canvas.innerHTML = JTB.renderEmptyState();
        } else {
            sections.forEach((section, index) => {
                canvas.appendChild(JTB.renderSectionEditor(section, index));
            });
        }

        // Add section button
        canvas.appendChild(JTB.createAddSectionButton());
    };

    /**
     * Update only the preview of a specific element without rebuilding the entire canvas
     * This is more efficient for live editing
     */
    JTB.updateModulePreview = function() {
        if (!JTB.state.selectedModule || !JTB.state.selectedIndexes) {
            JTB.log('updateModulePreview: No selected module or indexes');
            return;
        }

        const type = JTB.state.selectedType;
        const indexes = JTB.state.selectedIndexes;

        // Get the actual module from content tree to ensure we have latest data
        let module;
        if (type === 'module') {
            module = JTB.state.content.content[indexes.sectionIndex]
                ?.children[indexes.rowIndex]
                ?.children[indexes.columnIndex]
                ?.children[indexes.moduleIndex];
        } else if (type === 'section') {
            module = JTB.state.content.content[indexes.sectionIndex];
        } else if (type === 'row') {
            module = JTB.state.content.content[indexes.sectionIndex]
                ?.children[indexes.rowIndex];
        }

        if (!module) {
            JTB.log('updateModulePreview: Module not found in content tree');
            return;
        }

        const moduleId = module.id;
        JTB.log('updateModulePreview called:', { type, moduleId, attrs: module.attrs });

        if (type === 'module') {
            // Update module preview
            const moduleElement = document.querySelector(`.jtb-module-editor[data-id="${moduleId}"]`);

            if (moduleElement) {
                const preview = moduleElement.querySelector('.jtb-module-preview');
                if (preview) {
                    // Update HTML content
                    preview.innerHTML = JTB.getModulePreview(module);

                    // Apply design styles
                    const designStyles = JTB.getDesignStyles(module.attrs);
                    preview.style.cssText = designStyles || '';
                }
            }
        } else if (type === 'section') {
            // Update section visual appearance
            const sectionElement = document.querySelector(`.jtb-section-editor[data-id="${moduleId}"]`);
            if (sectionElement) {
                const designStyles = JTB.getDesignStyles(module.attrs);
                sectionElement.style.cssText = designStyles || '';
            }
        } else if (type === 'row') {
            // Update row visual appearance
            const rowElement = document.querySelector(`.jtb-row-editor[data-id="${moduleId}"]`);
            if (rowElement) {
                const designStyles = JTB.getDesignStyles(module.attrs);
                rowElement.style.cssText = designStyles || '';
            }
        }
    };

    JTB.renderEmptyState = function() {
        return `
            <div class="jtb-empty-state">
                <div class="jtb-empty-state-icon">📄</div>
                <div class="jtb-empty-state-title">Start Building Your Page</div>
                <div class="jtb-empty-state-text">Click the button below to add your first section</div>
            </div>
        `;
    };

    JTB.renderSectionEditor = function(section, sectionIndex) {
        const div = document.createElement('div');
        div.className = 'jtb-section-editor';
        div.dataset.id = section.id;
        div.dataset.index = sectionIndex;

        // Apply design styles to section
        const sectionStyles = JTB.getDesignStyles(section.attrs);
        if (sectionStyles) {
            div.style.cssText = sectionStyles;
        }

        // Toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'jtb-section-toolbar';
        toolbar.innerHTML = `
            <span class="jtb-module-icon">${JTB.getModuleIcon('section', 16)}</span>
            <span class="jtb-module-name">Section</span>
            <div class="jtb-toolbar-actions">
                <button class="jtb-toolbar-btn" data-action="settings" title="Settings">${JTB.getToolbarIcon('settings')}</button>
                <button class="jtb-toolbar-btn" data-action="duplicate" title="Duplicate">${JTB.getToolbarIcon('duplicate')}</button>
                <button class="jtb-toolbar-btn delete" data-action="delete" title="Delete">${JTB.getToolbarIcon('delete')}</button>
            </div>
        `;
        div.appendChild(toolbar);

        // Rows
        const rows = section.children || [];
        rows.forEach((row, rowIndex) => {
            div.appendChild(JTB.renderRowEditor(row, sectionIndex, rowIndex));
        });

        // Add row button
        const addRowBtn = document.createElement('button');
        addRowBtn.className = 'jtb-add-row-btn';
        addRowBtn.innerHTML = '+ Add Row';
        addRowBtn.onclick = () => JTB.openColumnPicker({ sectionIndex });
        div.appendChild(addRowBtn);

        // Bind toolbar events
        JTB.bindToolbarEvents(toolbar, 'section', { sectionIndex });

        // Click on section toolbar to open settings
        toolbar.addEventListener('click', (e) => {
            if (e.target.closest('.jtb-toolbar-btn')) return;
            JTB.openSettings('section', { sectionIndex });
        });

        // Right-click context menu
        div.addEventListener('contextmenu', (e) => {
            // Only trigger if clicking directly on section (not on child elements)
            if (e.target.closest('.jtb-row-editor') || e.target.closest('.jtb-module-editor')) return;
            JTB.showContextMenu(e, 'section', { sectionIndex }, section);
        });

        return div;
    };

    JTB.renderRowEditor = function(row, sectionIndex, rowIndex) {
        const div = document.createElement('div');
        div.className = 'jtb-row-editor';
        div.dataset.id = row.id;
        div.dataset.sectionIndex = sectionIndex;
        div.dataset.rowIndex = rowIndex;

        // Apply design styles to row
        const rowStyles = JTB.getDesignStyles(row.attrs);
        if (rowStyles) {
            div.style.cssText = rowStyles;
        }

        // Toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'jtb-row-toolbar';
        toolbar.innerHTML = `
            <span class="jtb-module-icon">${JTB.getModuleIcon('row', 14)}</span>
            <span class="jtb-module-name">Row</span>
            <div class="jtb-toolbar-actions">
                <button class="jtb-toolbar-btn" data-action="columns" title="Change Columns">${JTB.getToolbarIcon('columns')}</button>
                <button class="jtb-toolbar-btn" data-action="settings" title="Settings">${JTB.getToolbarIcon('settings')}</button>
                <button class="jtb-toolbar-btn" data-action="duplicate" title="Duplicate">${JTB.getToolbarIcon('duplicate')}</button>
                <button class="jtb-toolbar-btn delete" data-action="delete" title="Delete">${JTB.getToolbarIcon('delete')}</button>
            </div>
        `;
        div.appendChild(toolbar);

        // Columns container
        const columnsContainer = document.createElement('div');
        columnsContainer.className = 'jtb-columns-container';
        columnsContainer.style.display = 'flex';
        columnsContainer.style.gap = '15px';

        // Parse column widths from row.attrs.columns (e.g., "1_2,1_2" or "1_3,1_3,1_3")
        const columnWidths = JTB.parseColumnWidths(row.attrs?.columns || '1');

        const columns = row.children || [];
        columns.forEach((column, columnIndex) => {
            const width = columnWidths[columnIndex] || '100%';
            columnsContainer.appendChild(JTB.renderColumnEditor(column, { sectionIndex, rowIndex, columnIndex }, width));
        });

        div.appendChild(columnsContainer);

        // Bind toolbar events
        JTB.bindToolbarEvents(toolbar, 'row', { sectionIndex, rowIndex });

        // Click on row toolbar to open settings
        toolbar.addEventListener('click', (e) => {
            if (e.target.closest('.jtb-toolbar-btn')) return;
            JTB.openSettings('row', { sectionIndex, rowIndex });
        });

        // Right-click context menu
        div.addEventListener('contextmenu', (e) => {
            // Only trigger if clicking directly on row (not on child elements)
            if (e.target.closest('.jtb-module-editor')) return;
            e.stopPropagation();
            JTB.showContextMenu(e, 'row', { sectionIndex, rowIndex }, row);
        });

        return div;
    };

    JTB.renderColumnEditor = function(column, indexes, width) {
        const div = document.createElement('div');
        div.className = 'jtb-column-editor';
        div.dataset.id = column.id;
        // Use calculated width from row's columns attribute, or flex:1 as fallback
        if (width && width !== '100%') {
            div.style.width = width;
            div.style.flexShrink = '0';
        } else {
            div.style.flex = '1';
        }

        // Modules
        const modules = column.children || [];
        modules.forEach((module, moduleIndex) => {
            div.appendChild(JTB.renderModuleEditor(module, { ...indexes, moduleIndex }));
        });

        // Add module button
        const addModuleBtn = document.createElement('button');
        addModuleBtn.className = 'jtb-add-module-btn';
        addModuleBtn.innerHTML = '+ Add Module';
        addModuleBtn.onclick = () => JTB.openModulePicker(indexes);
        div.appendChild(addModuleBtn);

        // Drag and drop
        div.addEventListener('dragover', (e) => {
            e.preventDefault();
            div.classList.add('drag-over');
        });

        div.addEventListener('dragleave', () => {
            div.classList.remove('drag-over');
        });

        div.addEventListener('drop', (e) => {
            e.preventDefault();
            div.classList.remove('drag-over');
            JTB.handleModuleDrop(e, indexes);
        });

        return div;
    };

    /**
     * Generate inline CSS styles from module's design attributes
     * This is used for real-time preview on canvas
     */
    JTB.getDesignStyles = function(attrs, skipDefaults = true) {
        if (!attrs) return '';

        const styles = [];

        // Helper to check if value is user-defined (not from defaults)
        // Skip common default background colors that shouldn't be applied in editor preview
        const defaultBgColors = ['#ffffff', '#fff', 'white', '#0f172a', '#1e293b', 'transparent', ''];
        const isUserDefinedBg = (color) => {
            if (!color) return false;
            if (skipDefaults && defaultBgColors.includes(color.toLowerCase())) return false;
            return true;
        };

        // Background - only apply if explicitly set by user (not default)
        if (attrs.background_color && isUserDefinedBg(attrs.background_color)) {
            styles.push(`background-color: ${attrs.background_color}`);
        }
        if (attrs.background_image) {
            styles.push(`background-image: url('${attrs.background_image}')`);
            styles.push('background-size: cover');
            styles.push('background-position: center');
        }
        if (attrs.background_gradient) {
            styles.push(`background: ${attrs.background_gradient}`);
        }

        // Text color
        if (attrs.text_color) {
            styles.push(`color: ${attrs.text_color}`);
        }

        // Padding - support both object format {top,right,bottom,left} and individual props
        if (attrs.padding && typeof attrs.padding === 'object') {
            const p = attrs.padding;
            const unit = (v) => typeof v === 'number' ? `${v}px` : v;
            styles.push(`padding: ${unit(p.top || 0)} ${unit(p.right || 0)} ${unit(p.bottom || 0)} ${unit(p.left || 0)}`);
        } else {
            if (attrs.padding_top) styles.push(`padding-top: ${attrs.padding_top}`);
            if (attrs.padding_right) styles.push(`padding-right: ${attrs.padding_right}`);
            if (attrs.padding_bottom) styles.push(`padding-bottom: ${attrs.padding_bottom}`);
            if (attrs.padding_left) styles.push(`padding-left: ${attrs.padding_left}`);
        }

        // Margin - support both object format {top,right,bottom,left} and individual props
        if (attrs.margin && typeof attrs.margin === 'object') {
            const m = attrs.margin;
            const unit = (v) => typeof v === 'number' ? `${v}px` : v;
            styles.push(`margin: ${unit(m.top || 0)} ${unit(m.right || 0)} ${unit(m.bottom || 0)} ${unit(m.left || 0)}`);
        } else {
            if (attrs.margin_top) styles.push(`margin-top: ${attrs.margin_top}`);
            if (attrs.margin_right) styles.push(`margin-right: ${attrs.margin_right}`);
            if (attrs.margin_bottom) styles.push(`margin-bottom: ${attrs.margin_bottom}`);
            if (attrs.margin_left) styles.push(`margin-left: ${attrs.margin_left}`);
        }

        // Border
        if (attrs.border_width && attrs.border_width !== '0px') {
            styles.push(`border-width: ${attrs.border_width}`);
            styles.push(`border-style: ${attrs.border_style || 'solid'}`);
            if (attrs.border_color) styles.push(`border-color: ${attrs.border_color}`);
        }
        // Border radius - support both object format {top_left,top_right,bottom_right,bottom_left} and string
        if (attrs.border_radius) {
            if (typeof attrs.border_radius === 'object') {
                const br = attrs.border_radius;
                const unit = (v) => typeof v === 'number' ? `${v}px` : v;
                styles.push(`border-radius: ${unit(br.top_left || 0)} ${unit(br.top_right || 0)} ${unit(br.bottom_right || 0)} ${unit(br.bottom_left || 0)}`);
            } else {
                styles.push(`border-radius: ${attrs.border_radius}`);
            }
        }

        // Box shadow
        if (attrs.box_shadow_horizontal || attrs.box_shadow_vertical || attrs.box_shadow_blur) {
            const h = attrs.box_shadow_horizontal || '0px';
            const v = attrs.box_shadow_vertical || '0px';
            const blur = attrs.box_shadow_blur || '0px';
            const spread = attrs.box_shadow_spread || '0px';
            const color = attrs.box_shadow_color || 'rgba(0,0,0,0.3)';
            styles.push(`box-shadow: ${h} ${v} ${blur} ${spread} ${color}`);
        }

        // Typography - add px unit if value is numeric
        if (attrs.font_size) {
            const fs = typeof attrs.font_size === 'number' ? `${attrs.font_size}px` : attrs.font_size;
            styles.push(`font-size: ${fs}`);
        }
        if (attrs.font_weight) styles.push(`font-weight: ${attrs.font_weight}`);
        if (attrs.font_family) styles.push(`font-family: ${attrs.font_family}`);
        if (attrs.line_height) styles.push(`line-height: ${attrs.line_height}`);
        if (attrs.letter_spacing) {
            const ls = typeof attrs.letter_spacing === 'number' ? `${attrs.letter_spacing}px` : attrs.letter_spacing;
            styles.push(`letter-spacing: ${ls}`);
        }
        if (attrs.text_align) styles.push(`text-align: ${attrs.text_align}`);

        // Transforms
        const transforms = [];
        if (attrs.transform_scale && attrs.transform_scale !== '1') {
            transforms.push(`scale(${attrs.transform_scale})`);
        }
        if (attrs.transform_rotate && attrs.transform_rotate !== '0deg') {
            transforms.push(`rotate(${attrs.transform_rotate})`);
        }
        if (attrs.transform_skew_x && attrs.transform_skew_x !== '0deg') {
            transforms.push(`skewX(${attrs.transform_skew_x})`);
        }
        if (attrs.transform_skew_y && attrs.transform_skew_y !== '0deg') {
            transforms.push(`skewY(${attrs.transform_skew_y})`);
        }
        if (transforms.length > 0) {
            styles.push(`transform: ${transforms.join(' ')}`);
        }

        // Filters - match PHP naming: filter_hue_rotate, filter_saturate, filter_brightness, etc.
        // Values are numbers, need to add units
        const filters = [];

        // hue-rotate: default 0, unit deg
        if (attrs.filter_hue_rotate !== undefined && attrs.filter_hue_rotate !== null && attrs.filter_hue_rotate != 0) {
            filters.push(`hue-rotate(${attrs.filter_hue_rotate}deg)`);
        }

        // saturate: default 100, unit %
        if (attrs.filter_saturate !== undefined && attrs.filter_saturate !== null && attrs.filter_saturate != 100) {
            filters.push(`saturate(${attrs.filter_saturate}%)`);
        }

        // brightness: default 100, unit %
        if (attrs.filter_brightness !== undefined && attrs.filter_brightness !== null && attrs.filter_brightness != 100) {
            filters.push(`brightness(${attrs.filter_brightness}%)`);
        }

        // contrast: default 100, unit %
        if (attrs.filter_contrast !== undefined && attrs.filter_contrast !== null && attrs.filter_contrast != 100) {
            filters.push(`contrast(${attrs.filter_contrast}%)`);
        }

        // invert: default 0, unit %
        if (attrs.filter_invert !== undefined && attrs.filter_invert !== null && attrs.filter_invert != 0) {
            filters.push(`invert(${attrs.filter_invert}%)`);
        }

        // sepia: default 0, unit %
        if (attrs.filter_sepia !== undefined && attrs.filter_sepia !== null && attrs.filter_sepia != 0) {
            filters.push(`sepia(${attrs.filter_sepia}%)`);
        }

        // blur: default 0, unit px
        if (attrs.filter_blur !== undefined && attrs.filter_blur !== null && attrs.filter_blur != 0) {
            filters.push(`blur(${attrs.filter_blur}px)`);
        }

        // grayscale: default 0, unit %
        if (attrs.filter_grayscale !== undefined && attrs.filter_grayscale !== null && attrs.filter_grayscale != 0) {
            filters.push(`grayscale(${attrs.filter_grayscale}%)`);
        }

        // opacity: default 100, unit %
        if (attrs.filter_opacity !== undefined && attrs.filter_opacity !== null && attrs.filter_opacity != 100) {
            filters.push(`opacity(${attrs.filter_opacity}%)`);
        }

        if (filters.length > 0) {
            styles.push(`filter: ${filters.join(' ')}`);
        }

        // Width/Height (for some modules)
        if (attrs.width) styles.push(`width: ${attrs.width}`);
        if (attrs.max_width) styles.push(`max-width: ${attrs.max_width}`);
        if (attrs.min_height) styles.push(`min-height: ${attrs.min_height}`);

        return styles.join('; ');
    };

    JTB.renderModuleEditor = function(module, indexes) {
        const moduleConfig = JTB.config.modules[module.type];
        const moduleName = moduleConfig ? moduleConfig.name : module.type;
        const moduleIcon = moduleConfig ? moduleConfig.icon : '📦';

        const div = document.createElement('div');
        div.className = 'jtb-module-editor';
        div.dataset.id = module.id;
        div.dataset.type = module.type;
        div.draggable = true;

        // Toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'jtb-module-toolbar';
        toolbar.innerHTML = `
            <div class="jtb-toolbar-actions">
                <button class="jtb-toolbar-btn" data-action="move" title="Move">${JTB.getToolbarIcon('move')}</button>
                <button class="jtb-toolbar-btn" data-action="settings" title="Settings">${JTB.getToolbarIcon('settings')}</button>
                <button class="jtb-toolbar-btn" data-action="duplicate" title="Duplicate">${JTB.getToolbarIcon('duplicate')}</button>
                <button class="jtb-toolbar-btn delete" data-action="delete" title="Delete">${JTB.getToolbarIcon('delete')}</button>
            </div>
            <div class="jtb-module-name-row">
                <span class="jtb-module-icon">${JTB.getModuleIcon(module.type, 14)}</span>
                <span class="jtb-module-name">${moduleName}</span>
            </div>
        `;
        div.appendChild(toolbar);

        // Preview with design styles applied
        const preview = document.createElement('div');
        preview.className = 'jtb-module-preview';
        preview.innerHTML = JTB.getModulePreview(module);

        // Apply design styles from module attributes
        const designStyles = JTB.getDesignStyles(module.attrs);
        if (designStyles) {
            preview.style.cssText = designStyles;
        }

        div.appendChild(preview);

        // Bind events
        JTB.bindToolbarEvents(toolbar, 'module', indexes);

        // Click on module to open settings
        div.addEventListener('click', (e) => {
            // Don't trigger if clicking on toolbar buttons
            if (e.target.closest('.jtb-toolbar-btn')) return;
            e.stopPropagation();
            JTB.openSettings('module', indexes);
        });

        // Drag events
        div.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                type: 'move',
                indexes: indexes
            }));
            div.style.opacity = '0.5';
        });

        div.addEventListener('dragend', () => {
            div.style.opacity = '1';
        });

        // Right-click context menu
        div.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();
            JTB.showContextMenu(e, 'module', indexes, module);
        });

        return div;
    };

    JTB.getModuleIcon = function(type, size) {
        size = size || 18;

        // Map module types to Feather icon names
        const iconMap = {
            section: 'box',
            row: 'columns',
            column: 'sidebar',
            text: 'type',
            heading: 'hash',
            image: 'image',
            button: 'square',
            blurb: 'message-square',
            divider: 'minus',
            cta: 'zap',
            code: 'code',
            testimonial: 'message-circle',
            team_member: 'user',
            pricing_table: 'dollar-sign',
            social_follow: 'share-2',
            accordion: 'list',
            tabs: 'folder',
            toggle: 'toggle-left',
            video: 'video',
            audio: 'volume-2',
            gallery: 'grid',
            slider: 'layers',
            map: 'map-pin',
            contact_form: 'mail',
            login: 'log-in',
            signup: 'user-plus',
            search: 'search',
            blog: 'file-text',
            portfolio: 'briefcase',
            post_slider: 'film',
            number_counter: 'hash',
            circle_counter: 'circle',
            bar_counter: 'bar-chart-2',
            countdown: 'clock',
            sidebar: 'sidebar',
            comments: 'message-circle',
            shop: 'shopping-cart',
            post_navigation: 'arrow-left-right',
            fullwidth_header: 'layout',
            fullwidth_image: 'image',
            fullwidth_slider: 'layers',
            fullwidth_menu: 'menu',
            fullwidth_map: 'map',
            fullwidth_code: 'terminal',
            fullwidth_portfolio: 'briefcase',
            fullwidth_post_slider: 'film',
            fullwidth_post_title: 'file-text',
            // Theme Builder modules
            menu: 'menu',
            site_logo: 'home',
            search_form: 'search',
            post_title: 'file-text',
            post_content: 'align-left',
            social_icons: 'share-2',
            // Additional
            icon: 'star',
            featured_image: 'image',
            post_excerpt: 'align-left',
            post_meta: 'info',
            author_box: 'user',
            related_posts: 'grid',
            archive_title: 'archive',
            breadcrumbs: 'chevrons-right',
            archive_posts: 'list'
        };

        const iconName = iconMap[type] || 'box';

        // Use Feather icons if available
        if (typeof JTB.getFeatherIcon === 'function') {
            return JTB.getFeatherIcon(iconName, size, 2);
        }

        // Fallback to emoji if Feather icons not loaded
        const emojiFallback = {
            box: '📦', columns: '▤', sidebar: '▥', type: '📝', hash: '🔤',
            image: '🖼️', square: '🔘', 'message-square': '💬', minus: '➖',
            zap: '📢', code: '💻', 'message-circle': '💭', user: '👤',
            'dollar-sign': '💰', 'share-2': '📱', list: '📋', folder: '📑',
            'toggle-left': '🔀', video: '🎬', 'volume-2': '🔊', grid: '🖼️',
            layers: '🎠', 'map-pin': '🗺️', mail: '✉️', 'log-in': '🔐',
            'user-plus': '📝', search: '🔍', 'file-text': '📰', briefcase: '💼',
            film: '🎬', clock: '⏱️', 'shopping-cart': '🛒', layout: '🎯',
            menu: '☰', terminal: '💻', map: '🗺️', 'bar-chart-2': '📊',
            circle: '⭕', home: '🏠', 'align-left': '📄', info: 'ℹ️',
            star: '⭐', archive: '📁', 'chevrons-right': '»'
        };

        return emojiFallback[iconName] || '📦';
    };

    /**
     * Get toolbar action icon (settings, duplicate, delete, move, columns)
     */
    JTB.getToolbarIcon = function(action, size) {
        size = size || 14;

        const iconMap = {
            settings: 'settings',
            duplicate: 'copy',
            delete: 'trash-2',
            move: 'move',
            columns: 'columns',
            edit: 'edit-2',
            close: 'x'
        };

        const iconName = iconMap[action] || 'circle';

        if (typeof JTB.getFeatherIcon === 'function') {
            return JTB.getFeatherIcon(iconName, size, 2);
        }

        // Fallback emojis
        const emojiFallback = {
            settings: '⚙️',
            duplicate: '📋',
            delete: '🗑️',
            move: '↕️',
            columns: '⊞',
            edit: '✏️',
            close: '✖'
        };

        return emojiFallback[action] || '•';
    };

    JTB.getModulePreview = function(module) {
        const type = module.type;
        const attrs = module.attrs || {};

        switch (type) {
            // ========================================
            // CONTENT MODULES
            // ========================================
            case 'text':
                return `<div class="jtb-preview-text">${attrs.content || 'Enter your text here...'}</div>`;

            case 'heading':
                const level = attrs.level || 'h2';
                const headingText = attrs.text || 'Your Heading Here';
                return `<${level} class="jtb-preview-heading">${JTB.escapeHtml(headingText)}</${level}>`;

            case 'image':
                if (attrs.src) {
                    return `<img src="${JTB.escapeHtml(attrs.src)}" alt="${JTB.escapeHtml(attrs.alt || '')}" style="max-width: 100%; height: auto;">`;
                }
                return '<div class="jtb-preview-placeholder">No image selected</div>';

            case 'button':
                const btnText = attrs.text || 'Click Here';
                const btnStyle = attrs.button_style || 'solid';
                return `<div style="text-align: ${attrs.align || 'left'}"><span class="jtb-preview-button jtb-button-${btnStyle}">${JTB.escapeHtml(btnText)}</span></div>`;

            case 'blurb':
                const blurbTitle = attrs.title || 'Your Title Here';
                const blurbContent = attrs.content || '<p>Your content goes here.</p>';
                const blurbImage = attrs.image || '';
                const blurbIconColor = attrs.icon_color || '#2ea3f2';
                let blurbHtml = '<div class="jtb-preview-blurb">';
                if (blurbImage) {
                    blurbHtml += `<div class="jtb-preview-blurb-image"><img src="${JTB.escapeHtml(blurbImage)}" style="max-width: 80px;"></div>`;
                } else if (attrs.use_icon && attrs.font_icon) {
                    const blurbIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon(attrs.font_icon || 'star', 32) : '⬡';
                    blurbHtml += `<div class="jtb-preview-blurb-icon" style="color: ${blurbIconColor};"><span class="jtb-icon" style="stroke: ${blurbIconColor};">${blurbIcon}</span></div>`;
                }
                blurbHtml += `<div class="jtb-preview-blurb-title">${JTB.escapeHtml(blurbTitle)}</div>`;
                blurbHtml += `<div class="jtb-preview-blurb-content">${blurbContent}</div>`;
                blurbHtml += '</div>';
                return blurbHtml;

            case 'icon':
                const iconName = attrs.icon || 'star';
                const iconSize = attrs.size || 48;
                const iconColor = attrs.color || '#89b4fa';
                const iconSvg = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon(iconName, iconSize) : '⭐';
                return `<div class="jtb-preview-icon" style="text-align: ${attrs.align || 'center'}; color: ${iconColor};">${iconSvg}</div>`;

            case 'divider':
                const dividerStyle = attrs.style || 'solid';
                const dividerColor = attrs.color || '#333';
                return `<hr class="jtb-preview-divider" style="border-style: ${dividerStyle}; border-color: ${JTB.escapeHtml(dividerColor)};">`;

            case 'cta':
                const ctaTitle = attrs.title || 'Call To Action';
                const ctaBtnText = attrs.button_text || 'Click Here';
                return `<div class="jtb-preview-cta"><strong>${JTB.escapeHtml(ctaTitle)}</strong> <span class="jtb-preview-button">${JTB.escapeHtml(ctaBtnText)}</span></div>`;

            case 'code':
                return `<div class="jtb-preview-code"><pre>${JTB.escapeHtml((attrs.content || '// Code here...').substring(0, 100))}</pre></div>`;

            case 'testimonial':
                const testimonialBody = attrs.body || 'Testimonial text here...';
                const testimonialAuthor = attrs.author || 'Author Name';
                const testimonialImg = attrs.portrait_url || '';
                return `<div class="jtb-preview-testimonial">
                    ${testimonialImg ? `<img src="${JTB.escapeHtml(testimonialImg)}" class="jtb-testimonial-img" style="width:60px;height:60px;border-radius:50%;margin-bottom:10px;">` : ''}
                    <blockquote>"${JTB.escapeHtml(testimonialBody)}"</blockquote>
                    <cite>— ${JTB.escapeHtml(testimonialAuthor)}</cite>
                </div>`;

            case 'team_member':
                const memberName = attrs.name || 'Team Member';
                const memberPosition = attrs.position || 'Position';
                const memberImg = attrs.image_url || '';
                return `<div class="jtb-preview-team">
                    ${memberImg ? `<img src="${JTB.escapeHtml(memberImg)}" style="width:80px;height:80px;border-radius:50%;margin-bottom:10px;">` : '<div class="jtb-team-avatar">👤</div>'}
                    <strong>${JTB.escapeHtml(memberName)}</strong><br>
                    <small>${JTB.escapeHtml(memberPosition)}</small>
                </div>`;

            case 'pricing_table':
                const pricingTitle = attrs.title || 'Plan';
                const pricingPrice = attrs.price || '$0';
                const pricingPeriod = attrs.period || '/mo';
                const pricingFeatured = attrs.featured ? 'jtb-pricing-featured' : '';
                return `<div class="jtb-preview-pricing ${pricingFeatured}">
                    <div class="jtb-pricing-header"><strong>${JTB.escapeHtml(pricingTitle)}</strong></div>
                    <div class="jtb-pricing-price"><span class="jtb-price-amount">${JTB.escapeHtml(pricingPrice)}</span><span class="jtb-price-period">${JTB.escapeHtml(pricingPeriod)}</span></div>
                    <div class="jtb-pricing-btn"><span class="jtb-preview-button">${JTB.escapeHtml(attrs.button_text || 'Get Started')}</span></div>
                </div>`;

            case 'social_follow':
                return `<div class="jtb-preview-social-follow">
                    <span class="jtb-social-btn">📘 Facebook</span>
                    <span class="jtb-social-btn">🐦 Twitter</span>
                    <span class="jtb-social-btn">📷 Instagram</span>
                </div>`;

            case 'comments':
                return `<div class="jtb-preview-comments">
                    <div class="jtb-comments-header">💬 Comments</div>
                    <div class="jtb-comments-desc">Post comments will appear here</div>
                </div>`;

            case 'sidebar':
                const sidebarName = attrs.sidebar || 'default';
                return `<div class="jtb-preview-sidebar">
                    <div class="jtb-sidebar-icon">📋</div>
                    <div class="jtb-sidebar-title">Sidebar: ${JTB.escapeHtml(sidebarName)}</div>
                </div>`;

            case 'countdown':
                const countdownDate = attrs.date || '2025-12-31';
                return `<div class="jtb-preview-countdown">
                    <div class="jtb-countdown-blocks">
                        <div class="jtb-countdown-block"><span class="jtb-countdown-num">00</span><span class="jtb-countdown-label">Days</span></div>
                        <div class="jtb-countdown-block"><span class="jtb-countdown-num">00</span><span class="jtb-countdown-label">Hours</span></div>
                        <div class="jtb-countdown-block"><span class="jtb-countdown-num">00</span><span class="jtb-countdown-label">Mins</span></div>
                        <div class="jtb-countdown-block"><span class="jtb-countdown-num">00</span><span class="jtb-countdown-label">Secs</span></div>
                    </div>
                    <div class="jtb-countdown-target">Target: ${JTB.escapeHtml(countdownDate)}</div>
                </div>`;

            case 'post_navigation':
                return `<div class="jtb-preview-post-nav">
                    <div class="jtb-post-nav-prev">← Previous Post</div>
                    <div class="jtb-post-nav-next">Next Post →</div>
                </div>`;

            case 'shop':
                const shopColumns = attrs.columns || 4;
                return `<div class="jtb-preview-shop">
                    <div class="jtb-shop-header">🛒 WooCommerce Products</div>
                    <div class="jtb-shop-grid" style="display:grid;grid-template-columns:repeat(${shopColumns},1fr);gap:10px;">
                        <div class="jtb-shop-item">📦</div>
                        <div class="jtb-shop-item">📦</div>
                        <div class="jtb-shop-item">📦</div>
                        <div class="jtb-shop-item">📦</div>
                    </div>
                </div>`;

            // ========================================
            // COUNTER MODULES
            // ========================================
            case 'number_counter':
                const numCounterNumber = attrs.number || '100';
                const numCounterTitle = attrs.title || 'Counter';
                return `<div class="jtb-preview-counter jtb-counter-number">
                    <span class="jtb-counter-value">${JTB.escapeHtml(numCounterNumber)}</span>
                    <span class="jtb-counter-title">${JTB.escapeHtml(numCounterTitle)}</span>
                </div>`;

            case 'circle_counter':
                const circlePercent = attrs.percent || 75;
                const circleTitle = attrs.title || 'Progress';
                return `<div class="jtb-preview-counter jtb-counter-circle">
                    <div class="jtb-circle-ring">
                        <svg viewBox="0 0 100 100" width="80" height="80">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#313244" stroke-width="8"/>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#89b4fa" stroke-width="8"
                                stroke-dasharray="${circlePercent * 2.83} 283" stroke-linecap="round" transform="rotate(-90 50 50)"/>
                        </svg>
                        <span class="jtb-circle-percent">${circlePercent}%</span>
                    </div>
                    <span class="jtb-counter-title">${JTB.escapeHtml(circleTitle)}</span>
                </div>`;

            case 'bar_counter':
                const barPercent = attrs.percent || 75;
                const barTitle = attrs.title || 'Skill';
                return `<div class="jtb-preview-counter jtb-counter-bar">
                    <div class="jtb-bar-header">
                        <span class="jtb-bar-title">${JTB.escapeHtml(barTitle)}</span>
                        <span class="jtb-bar-percent">${barPercent}%</span>
                    </div>
                    <div class="jtb-bar-track">
                        <div class="jtb-bar-fill" style="width:${barPercent}%"></div>
                    </div>
                </div>`;

            // ========================================
            // INTERACTIVE MODULES
            // ========================================
            case 'accordion':
                return `<div class="jtb-preview-accordion">
                    <div class="jtb-accordion-item">
                        <div class="jtb-accordion-header">▶ Accordion Item 1</div>
                    </div>
                    <div class="jtb-accordion-item">
                        <div class="jtb-accordion-header">▶ Accordion Item 2</div>
                    </div>
                    <div class="jtb-accordion-item">
                        <div class="jtb-accordion-header">▶ Accordion Item 3</div>
                    </div>
                </div>`;

            case 'accordion_item':
                const accTitle = attrs.title || 'Accordion Item';
                return `<div class="jtb-preview-accordion-item">
                    <div class="jtb-accordion-header">▶ ${JTB.escapeHtml(accTitle)}</div>
                    <div class="jtb-accordion-content">${attrs.content || 'Accordion content...'}</div>
                </div>`;

            case 'tabs':
                return `<div class="jtb-preview-tabs">
                    <div class="jtb-tabs-nav">
                        <button class="jtb-tab-btn active">Tab 1</button>
                        <button class="jtb-tab-btn">Tab 2</button>
                        <button class="jtb-tab-btn">Tab 3</button>
                    </div>
                    <div class="jtb-tabs-content">Tab content will appear here</div>
                </div>`;

            case 'tabs_item':
                const tabTitle = attrs.title || 'Tab Item';
                return `<div class="jtb-preview-tabs-item">
                    <div class="jtb-tab-title">📑 ${JTB.escapeHtml(tabTitle)}</div>
                    <div class="jtb-tab-content">${attrs.content || 'Tab content...'}</div>
                </div>`;

            case 'toggle':
                const toggleTitle = attrs.title || 'Toggle Title';
                const toggleOpen = attrs.open ? 'open' : '';
                return `<div class="jtb-preview-toggle ${toggleOpen}">
                    <div class="jtb-toggle-header">
                        <span class="jtb-toggle-icon">${attrs.open ? '▼' : '▶'}</span>
                        <span class="jtb-toggle-title">${JTB.escapeHtml(toggleTitle)}</span>
                    </div>
                    ${attrs.open ? `<div class="jtb-toggle-content">${attrs.content || 'Toggle content...'}</div>` : ''}
                </div>`;

            // ========================================
            // MEDIA MODULES
            // ========================================
            case 'video':
                if (attrs.src) {
                    // Check if YouTube or Vimeo
                    const isYoutube = attrs.src.includes('youtube.com') || attrs.src.includes('youtu.be');
                    const isVimeo = attrs.src.includes('vimeo.com');
                    const videoIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('play-circle', 48) : '▶';
                    return `<div class="jtb-preview-video">
                        <div class="jtb-video-overlay">${videoIcon}</div>
                        <div class="jtb-video-info">${isYoutube ? 'YouTube' : isVimeo ? 'Vimeo' : 'Video'}: ${JTB.escapeHtml(attrs.src.substring(0, 40))}...</div>
                    </div>`;
                }
                return '<div class="jtb-preview-placeholder">No video URL</div>';

            case 'audio':
                const audioIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('volume-2', 24) : '🔊';
                return `<div class="jtb-preview-audio">
                    ${audioIcon}
                    <div class="jtb-audio-controls">
                        <div class="jtb-audio-play">▶</div>
                        <div class="jtb-audio-progress"><div class="jtb-audio-bar"></div></div>
                        <div class="jtb-audio-time">0:00</div>
                    </div>
                </div>`;

            case 'map':
                const mapIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('map-pin', 32) : '📍';
                const mapAddress = attrs.address || 'Enter address...';
                return `<div class="jtb-preview-map">
                    ${mapIcon}
                    <div class="jtb-map-address">${JTB.escapeHtml(mapAddress)}</div>
                </div>`;

            case 'gallery':
                // Support both gallery_images (PHP field name) and images (legacy)
                let galleryImages = attrs.gallery_images || attrs.images || [];
                // Parse if it's a JSON string
                if (typeof galleryImages === 'string') {
                    try { galleryImages = JSON.parse(galleryImages); } catch(e) { galleryImages = []; }
                }
                const galleryCols = attrs.columns || 3;
                const galleryIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('grid', 32) : '';
                if (Array.isArray(galleryImages) && galleryImages.length > 0) {
                    let galleryHtml = `<div class="jtb-preview-gallery-grid" style="display:grid;grid-template-columns:repeat(${galleryCols},1fr);gap:8px;">`;
                    galleryImages.slice(0, 6).forEach(img => {
                        // Support both string URLs and object format {url: '...'}
                        const imgUrl = typeof img === 'string' ? img : (img.url || img.src || '');
                        if (imgUrl) {
                            galleryHtml += `<div class="jtb-gallery-thumb"><img src="${JTB.escapeHtml(imgUrl)}" style="width:100%;height:60px;object-fit:cover;border-radius:4px;"></div>`;
                        }
                    });
                    if (galleryImages.length > 6) {
                        galleryHtml += `<div class="jtb-gallery-more">+${galleryImages.length - 6} more</div>`;
                    }
                    galleryHtml += '</div>';
                    return galleryHtml;
                }
                return `<div class="jtb-preview-gallery jtb-gallery-empty">
                    <div class="jtb-gallery-icon">${galleryIcon}</div>
                    <div class="jtb-gallery-text">Gallery - Add images</div>
                </div>`;

            case 'slider':
                const sliderImages = attrs.images || [];
                if (sliderImages.length > 0) {
                    return `<div class="jtb-preview-slider">
                        <img src="${JTB.escapeHtml(sliderImages[0])}" style="width:100%;height:150px;object-fit:cover;border-radius:4px;">
                        <div class="jtb-slider-nav">
                            <span class="jtb-slider-prev">‹</span>
                            <span class="jtb-slider-dots">${sliderImages.map((_, i) => i === 0 ? '●' : '○').join(' ')}</span>
                            <span class="jtb-slider-next">›</span>
                        </div>
                    </div>`;
                }
                const sliderIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('layers', 32) : '';
                return `<div class="jtb-preview-slider-empty">
                    <div class="jtb-slider-icon">${sliderIcon}</div>
                    <div class="jtb-slider-text">Slider - Add slides</div>
                </div>`;

            case 'slider_item':
                if (attrs.image) {
                    return `<div class="jtb-preview-slider-item">
                        <img src="${JTB.escapeHtml(attrs.image)}" style="width:100%;height:100px;object-fit:cover;">
                        ${attrs.heading ? `<div class="jtb-slide-caption">${JTB.escapeHtml(attrs.heading)}</div>` : ''}
                    </div>`;
                }
                return '<div class="jtb-preview-slider-item jtb-placeholder">Slide Item</div>';

            case 'video_slider':
                const videoSliderIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('film', 32) : '';
                return `<div class="jtb-preview-video-slider">
                    <div class="jtb-video-slider-icon">${videoSliderIcon}</div>
                    <div class="jtb-video-slider-text">Video Slider</div>
                </div>`;

            case 'video_slider_item':
                return `<div class="jtb-preview-video-slider-item">
                    <span class="jtb-video-play">▶</span>
                    <span>Video Slide</span>
                </div>`;

            // ========================================
            // FORM MODULES
            // ========================================
            case 'contact_form':
                return `<div class="jtb-preview-form jtb-form-contact">
                    <div class="jtb-form-field"><input type="text" placeholder="Your Name" readonly></div>
                    <div class="jtb-form-field"><input type="email" placeholder="Your Email" readonly></div>
                    <div class="jtb-form-field"><textarea placeholder="Your Message" rows="3" readonly></textarea></div>
                    <div class="jtb-form-btn"><span class="jtb-preview-button">Send Message</span></div>
                </div>`;

            case 'login':
                const loginIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('log-in', 18) : '';
                return `<div class="jtb-preview-form jtb-form-login">
                    <div class="jtb-form-title">${loginIcon} Login</div>
                    <div class="jtb-form-field"><input type="text" placeholder="Username" readonly></div>
                    <div class="jtb-form-field"><input type="password" placeholder="Password" readonly></div>
                    <div class="jtb-form-btn"><span class="jtb-preview-button">Login</span></div>
                </div>`;

            case 'signup':
                const signupIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('user-plus', 18) : '';
                return `<div class="jtb-preview-form jtb-form-signup">
                    <div class="jtb-form-title">${signupIcon} Sign Up</div>
                    <div class="jtb-form-field"><input type="text" placeholder="Username" readonly></div>
                    <div class="jtb-form-field"><input type="email" placeholder="Email" readonly></div>
                    <div class="jtb-form-field"><input type="password" placeholder="Password" readonly></div>
                    <div class="jtb-form-btn"><span class="jtb-preview-button">Register</span></div>
                </div>`;

            case 'search':
                const searchBtnIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('search', 16) : '';
                return `<div class="jtb-preview-form jtb-form-search">
                    <div class="jtb-search-wrapper">
                        <input type="text" placeholder="${JTB.escapeHtml(attrs.placeholder || 'Search...')}" readonly>
                        <button class="jtb-search-btn">${searchBtnIcon}</button>
                    </div>
                </div>`;

            // ========================================
            // BLOG MODULES
            // ========================================
            case 'blog':
                const blogCols = attrs.columns || 3;
                const blogPostIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('file-text', 24) : '';
                return `<div class="jtb-preview-blog">
                    <div class="jtb-blog-grid" style="display:grid;grid-template-columns:repeat(${blogCols},1fr);gap:15px;">
                        <div class="jtb-blog-card">
                            <div class="jtb-blog-thumb">${blogPostIcon}</div>
                            <div class="jtb-blog-title">Post Title</div>
                        </div>
                        <div class="jtb-blog-card">
                            <div class="jtb-blog-thumb">${blogPostIcon}</div>
                            <div class="jtb-blog-title">Post Title</div>
                        </div>
                        <div class="jtb-blog-card">
                            <div class="jtb-blog-thumb">${blogPostIcon}</div>
                            <div class="jtb-blog-title">Post Title</div>
                        </div>
                    </div>
                </div>`;

            case 'portfolio':
                const portfolioCols = attrs.columns || 3;
                const portfolioIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('image', 24) : '';
                return `<div class="jtb-preview-portfolio">
                    <div class="jtb-portfolio-grid" style="display:grid;grid-template-columns:repeat(${portfolioCols},1fr);gap:10px;">
                        <div class="jtb-portfolio-item">${portfolioIcon}</div>
                        <div class="jtb-portfolio-item">${portfolioIcon}</div>
                        <div class="jtb-portfolio-item">${portfolioIcon}</div>
                    </div>
                </div>`;

            case 'post_slider':
                const postSliderIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('layers', 32) : '';
                return `<div class="jtb-preview-post-slider">
                    <div class="jtb-post-slider-icon">${postSliderIcon}</div>
                    <div class="jtb-post-slider-text">Post Slider</div>
                    <div class="jtb-post-slider-nav">‹ ● ● ● ›</div>
                </div>`;

            // ========================================
            // FULLWIDTH MODULES
            // ========================================
            case 'fullwidth_header':
                const fwTitle = attrs.title || 'Your Title Goes Here';
                const fwSubheading = attrs.subheading || '';
                const fwContent = attrs.content || '';
                const fwBtn1 = attrs.button_one_text || '';
                const fwBtn2 = attrs.button_two_text || '';
                const fwBg = attrs.background_image ? `background-image:url('${JTB.escapeHtml(attrs.background_image)}');background-size:cover;` : '';
                let fwHtml = `<div class="jtb-preview-fullwidth-header" style="${fwBg}">`;
                fwHtml += `<h1>${JTB.escapeHtml(fwTitle)}</h1>`;
                if (fwSubheading) fwHtml += `<p class="jtb-fw-subheading">${JTB.escapeHtml(fwSubheading)}</p>`;
                if (fwContent) fwHtml += `<div class="jtb-fw-content">${fwContent}</div>`;
                if (fwBtn1 || fwBtn2) {
                    fwHtml += `<div class="jtb-fw-buttons">`;
                    if (fwBtn1) fwHtml += `<span class="jtb-preview-button">${JTB.escapeHtml(fwBtn1)}</span>`;
                    if (fwBtn2) fwHtml += `<span class="jtb-preview-button jtb-button-outline">${JTB.escapeHtml(fwBtn2)}</span>`;
                    fwHtml += `</div>`;
                }
                fwHtml += `</div>`;
                return fwHtml;

            case 'fullwidth_image':
                if (attrs.src) {
                    return `<div class="jtb-preview-fullwidth-image"><img src="${JTB.escapeHtml(attrs.src)}" alt="${JTB.escapeHtml(attrs.alt || '')}" style="width: 100%; height: auto;"></div>`;
                }
                const fwImageIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('image', 32) : '';
                return `<div class="jtb-preview-fullwidth-image jtb-placeholder">${fwImageIcon} Fullwidth Image - No image selected</div>`;

            case 'fullwidth_slider':
                const fwSliderIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('layers', 32) : '';
                return `<div class="jtb-preview-fullwidth-slider">
                    <div class="jtb-fw-slider-icon">${fwSliderIcon}</div>
                    <div class="jtb-fw-slider-text">Fullwidth Slider</div>
                    <div class="jtb-fw-slider-nav">‹ ● ● ● ›</div>
                </div>`;

            case 'fullwidth_slider_item':
                if (attrs.image) {
                    return `<div class="jtb-preview-fw-slider-item">
                        <img src="${JTB.escapeHtml(attrs.image)}" style="width:100%;height:120px;object-fit:cover;">
                        ${attrs.heading ? `<div class="jtb-fw-slide-heading">${JTB.escapeHtml(attrs.heading)}</div>` : ''}
                    </div>`;
                }
                return '<div class="jtb-preview-fw-slider-item jtb-placeholder">Fullwidth Slide Item</div>';

            case 'fullwidth_menu':
                const fwMenuIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('menu', 20) : '☰';
                return `<div class="jtb-preview-fullwidth-menu">
                    <div class="jtb-fw-menu-logo">LOGO</div>
                    <nav class="jtb-fw-menu-nav">Home • About • Services • Contact</nav>
                    <div class="jtb-fw-menu-icon">${fwMenuIcon}</div>
                </div>`;

            case 'fullwidth_portfolio':
                const fwPortfolioIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('image', 24) : '';
                return `<div class="jtb-preview-fullwidth-portfolio">
                    <div class="jtb-fw-portfolio-grid">
                        <div class="jtb-fw-portfolio-item">${fwPortfolioIcon}</div>
                        <div class="jtb-fw-portfolio-item">${fwPortfolioIcon}</div>
                        <div class="jtb-fw-portfolio-item">${fwPortfolioIcon}</div>
                        <div class="jtb-fw-portfolio-item">${fwPortfolioIcon}</div>
                    </div>
                </div>`;

            case 'fullwidth_post_slider':
                const fwPostSliderIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('layers', 32) : '';
                return `<div class="jtb-preview-fullwidth-post-slider">
                    <div class="jtb-fw-post-slider-icon">${fwPostSliderIcon}</div>
                    <div class="jtb-fw-post-slider-text">Fullwidth Post Slider</div>
                </div>`;

            case 'fullwidth_post_title':
                const fwPostTitle = attrs.title || 'Dynamic Post Title';
                return `<div class="jtb-preview-fullwidth-post-title">
                    <h1>${JTB.escapeHtml(fwPostTitle)}</h1>
                    <div class="jtb-fw-post-meta">By Author · Date · Category</div>
                </div>`;

            case 'fullwidth_code':
                return `<div class="jtb-preview-fullwidth-code">
                    <div class="jtb-fw-code-header">CODE</div>
                    <pre>${JTB.escapeHtml((attrs.content || '// Fullwidth code block...').substring(0, 100))}</pre>
                </div>`;

            case 'fullwidth_map':
                const fwMapIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('map-pin', 32) : '📍';
                return `<div class="jtb-preview-fullwidth-map">
                    ${fwMapIcon}
                    <div class="jtb-fw-map-text">Fullwidth Map</div>
                    <div class="jtb-fw-map-address">${JTB.escapeHtml(attrs.address || 'Enter address...')}</div>
                </div>`;

            // ========================================
            // THEME BUILDER MODULES
            // ========================================
            case 'menu':
                const menuLogo = attrs.logo || '';
                const menuStyle = attrs.menu_style || 'logo_left';
                const menuLogoHtml = menuLogo
                    ? `<img src="${JTB.escapeHtml(menuLogo)}" alt="Logo" class="jtb-menu-logo-img" style="max-height:40px;">`
                    : '<div class="jtb-menu-logo-placeholder">LOGO</div>';
                const searchIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('search', 16) : '🔍';
                const cartIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('shopping-cart', 16) : '🛒';
                return `<div class="jtb-preview-menu jtb-menu-style-${menuStyle}">
                    <div class="jtb-menu-logo">${menuLogoHtml}</div>
                    <nav class="jtb-menu-nav">
                        <a href="#">Home</a>
                        <a href="#">About</a>
                        <a href="#">Services</a>
                        <a href="#">Contact</a>
                    </nav>
                    <div class="jtb-menu-icons">
                        ${attrs.show_search_icon !== false ? `<span class="jtb-menu-icon">${searchIcon}</span>` : ''}
                        ${attrs.show_cart_icon ? `<span class="jtb-menu-icon">${cartIcon}</span>` : ''}
                    </div>
                </div>`;

            case 'site_logo':
                const siteLogo = attrs.logo || '';
                const logoAlign = attrs.alignment || 'left';
                if (siteLogo) {
                    return `<div class="jtb-preview-site-logo jtb-align-${logoAlign}">
                        <img src="${JTB.escapeHtml(siteLogo)}" alt="${JTB.escapeHtml(attrs.logo_alt || 'Logo')}" style="max-width: ${attrs.logo_width || 150}px;">
                    </div>`;
                }
                const logoIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('image', 24) : '🏷️';
                return `<div class="jtb-preview-site-logo jtb-preview-site-logo-empty jtb-align-${logoAlign}">
                    ${logoIcon}
                    <span class="jtb-logo-text">Upload a Logo</span>
                </div>`;

            case 'search_form':
                const searchFormIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('search', 16) : '🔍';
                const searchType = attrs.display_type || 'full_form';
                if (searchType === 'icon_only') {
                    return `<div class="jtb-preview-search jtb-search-icon-only">${searchFormIcon}</div>`;
                }
                return `<div class="jtb-preview-search jtb-search-full">
                    <input type="text" placeholder="${JTB.escapeHtml(attrs.placeholder || 'Search...')}" class="jtb-search-input" readonly>
                    ${attrs.show_button !== false ? `<button class="jtb-search-button">${searchFormIcon}</button>` : ''}
                </div>`;

            case 'post_title':
                const titleLevel = attrs.title_level || 'h1';
                const titleAlign = attrs.text_alignment || 'left';
                return `<div class="jtb-preview-post-title jtb-align-${titleAlign}">
                    ${attrs.show_title !== false ? `<${titleLevel} class="jtb-post-title-heading">Dynamic Post Title</${titleLevel}>` : ''}
                    ${attrs.show_meta !== false ? `<div class="jtb-post-title-meta">By <a href="#">Author</a> · Jan 24, 2026 · <a href="#">Category</a></div>` : ''}
                </div>`;

            case 'post_content':
                const contentIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('file-text', 32) : '📄';
                return `<div class="jtb-preview-post-content">
                    ${contentIcon}
                    <div class="jtb-post-content-title">Post Content</div>
                    <div class="jtb-post-content-desc">Dynamic post content will display here</div>
                </div>`;

            case 'featured_image':
                if (attrs.src) {
                    return `<div class="jtb-preview-featured-image">
                        <img src="${JTB.escapeHtml(attrs.src)}" style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;">
                    </div>`;
                }
                const featuredIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('image', 48) : '🖼️';
                return `<div class="jtb-preview-featured-image jtb-placeholder">
                    ${featuredIcon}
                    <div>Featured Image</div>
                </div>`;

            case 'post_excerpt':
                return `<div class="jtb-preview-post-excerpt">
                    <p>This is where the post excerpt will appear. It provides a brief summary of the post content to give readers a preview of what the article is about...</p>
                </div>`;

            case 'post_meta':
                const metaIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('info', 16) : 'ℹ️';
                return `<div class="jtb-preview-post-meta">
                    ${metaIcon} By <strong>Author</strong> · 📅 Jan 24, 2026 · 📁 Category · 💬 5 Comments
                </div>`;

            case 'author_box':
                const authorIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('user', 40) : '👤';
                return `<div class="jtb-preview-author-box">
                    <div class="jtb-author-avatar">${authorIcon}</div>
                    <div class="jtb-author-info">
                        <div class="jtb-author-name">Author Name</div>
                        <div class="jtb-author-bio">Author biography and description goes here. This is a brief introduction to the post author.</div>
                    </div>
                </div>`;

            case 'related_posts':
                return `<div class="jtb-preview-related-posts">
                    <h4>Related Posts</h4>
                    <div class="jtb-related-grid">
                        <div class="jtb-related-item">📄 Related Post 1</div>
                        <div class="jtb-related-item">📄 Related Post 2</div>
                        <div class="jtb-related-item">📄 Related Post 3</div>
                    </div>
                </div>`;

            case 'archive_title':
                return `<div class="jtb-preview-archive-title">
                    <h1>Archive: Category Name</h1>
                    <p class="jtb-archive-desc">Archive description or category description will appear here.</p>
                </div>`;

            case 'breadcrumbs':
                const homeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('home', 14) : '🏠';
                return `<div class="jtb-preview-breadcrumbs">
                    ${homeIcon} Home › Category › <strong>Current Page</strong>
                </div>`;

            case 'archive_posts':
                return `<div class="jtb-preview-archive-posts">
                    <div class="jtb-archive-grid">
                        <div class="jtb-archive-item">📄 Post Title 1</div>
                        <div class="jtb-archive-item">📄 Post Title 2</div>
                        <div class="jtb-archive-item">📄 Post Title 3</div>
                        <div class="jtb-archive-item">📄 Post Title 4</div>
                    </div>
                </div>`;

            case 'social_icons':
                const socialAlign = attrs.alignment || 'center';
                const fbIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('facebook', 20) : '📘';
                const twIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('twitter', 20) : '🐦';
                const igIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('instagram', 20) : '📷';
                const liIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('linkedin', 20) : '💼';
                const ytIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('youtube', 20) : '📺';
                return `<div class="jtb-preview-social-icons jtb-align-${socialAlign}">
                    <span class="jtb-social-icon">${fbIcon}</span>
                    <span class="jtb-social-icon">${twIcon}</span>
                    <span class="jtb-social-icon">${igIcon}</span>
                    <span class="jtb-social-icon">${liIcon}</span>
                    <span class="jtb-social-icon">${ytIcon}</span>
                </div>`;

            default:
                const moduleName = type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const defaultIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('box', 24) : '📦';
                return `<div class="jtb-preview-placeholder">${defaultIcon} ${moduleName}</div>`;
        }
    };

    // ========================================
    // Content Manipulation
    // ========================================

    JTB.addSection = function() {
        // Ensure content structure exists
        if (!JTB.state.content) {
            JTB.state.content = JTB.getEmptyContent();
        }
        if (!JTB.state.content.content) {
            JTB.state.content.content = [];
        }

        const section = JTB.createSection();
        JTB.state.content.content.push(section);
        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.duplicateSection = function(sectionIndex) {
        const section = JTB.state.content.content[sectionIndex];
        const duplicate = JSON.parse(JSON.stringify(section));
        JTB.regenerateIds(duplicate);
        JTB.state.content.content.splice(sectionIndex + 1, 0, duplicate);
        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.deleteSection = function(sectionIndex) {
        if (!JTB.state.content?.content) {
            console.error("[JTB] Cannot delete: content is undefined");
            return;
        }
        if (sectionIndex < 0 || sectionIndex >= JTB.state.content.content.length) {
            console.error("[JTB] Invalid section index:", sectionIndex);
            return;
        }
        if (!confirm("Delete this section?")) return;
        // console.log removed
        // console.log removed

        JTB.state.content.content.splice(sectionIndex, 1);
        JTB.markDirty();
        JTB.renderCanvas();
        JTB.Settings.close();
        // console.log removed
    };

    JTB.addRow = function(sectionIndex, columns) {
        const row = JTB.createRow(columns);
        JTB.state.content.content[sectionIndex].children.push(row);
        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.duplicateRow = function(sectionIndex, rowIndex) {
        const row = JTB.state.content.content[sectionIndex].children[rowIndex];
        const duplicate = JSON.parse(JSON.stringify(row));
        JTB.regenerateIds(duplicate);
        JTB.state.content.content[sectionIndex].children.splice(rowIndex + 1, 0, duplicate);
        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.deleteRow = function(sectionIndex, rowIndex) {
        if (confirm('Are you sure you want to delete this row?')) {
            JTB.state.content.content[sectionIndex].children.splice(rowIndex, 1);
            JTB.markDirty();
            JTB.renderCanvas();
            JTB.Settings.close();
        }
    };

    JTB.addModule = function(indexes, type) {
        const module = JTB.createModule(type);
        if (!module) return;

        const column = JTB.state.content.content[indexes.sectionIndex]
            .children[indexes.rowIndex]
            .children[indexes.columnIndex];

        column.children.push(module);
        JTB.markDirty();
        JTB.renderCanvas();
        JTB.closeModal();
    };

    JTB.duplicateModule = function(indexes) {
        const column = JTB.state.content.content[indexes.sectionIndex]
            .children[indexes.rowIndex]
            .children[indexes.columnIndex];

        const module = column.children[indexes.moduleIndex];
        const duplicate = JSON.parse(JSON.stringify(module));
        JTB.regenerateIds(duplicate);
        column.children.splice(indexes.moduleIndex + 1, 0, duplicate);
        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.deleteModule = function(indexes) {
        if (confirm('Are you sure you want to delete this module?')) {
            const column = JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex];

            column.children.splice(indexes.moduleIndex, 1);
            JTB.markDirty();
            JTB.renderCanvas();
            JTB.Settings.close();
        }
    };

    JTB.regenerateIds = function(element) {
        if (element.id) {
            element.id = JTB.generateId(element.type || 'element');
        }
        if (element.children && Array.isArray(element.children)) {
            element.children.forEach(child => JTB.regenerateIds(child));
        }
    };

    JTB.changeRowColumns = function(indexes, columns) {
        const row = JTB.state.content.content[indexes.sectionIndex].children[indexes.rowIndex];
        const oldColumns = row.children || [];
        const newColCount = columns.split(',').length;

        row.attrs.columns = columns;

        // Adjust columns
        while (row.children.length < newColCount) {
            row.children.push(JTB.createColumn());
        }

        // Move modules from removed columns to last column
        while (row.children.length > newColCount) {
            const removed = row.children.pop();
            if (removed.children && removed.children.length > 0) {
                const lastCol = row.children[row.children.length - 1];
                lastCol.children = lastCol.children.concat(removed.children);
            }
        }

        JTB.markDirty();
        JTB.renderCanvas();
        JTB.closeModal();
    };

    // ========================================
    // History
    // ========================================

    JTB.saveHistory = function() {
        const state = JSON.stringify(JTB.state.content);

        // Skip if content hasn't changed
        if (JTB.state.history.length > 0 && JTB.state.history[JTB.state.historyIndex] === state) {
            return;
        }

        // Remove future states if we're not at the end
        if (JTB.state.historyIndex < JTB.state.history.length - 1) {
            JTB.state.history = JTB.state.history.slice(0, JTB.state.historyIndex + 1);
        }

        JTB.state.history.push(state);
        JTB.state.historyIndex = JTB.state.history.length - 1;

        // Limit history to 50 states
        if (JTB.state.history.length > 50) {
            JTB.state.history.shift();
            JTB.state.historyIndex--;
        }

        // Update button states
        JTB.updateHistoryButtons();
    };

    JTB.undo = function() {
        if (!JTB.canUndo()) {
            JTB.showNotification('Nothing to undo', 'info');
            return;
        }

        JTB.state.historyIndex--;
        JTB.state.content = JSON.parse(JTB.state.history[JTB.state.historyIndex]);
        JTB.renderCanvas();
        JTB.Settings.close();
        JTB.updateHistoryButtons();

        const undoSteps = JTB.state.historyIndex;
        JTB.showNotification(`Undo (${undoSteps} step${undoSteps !== 1 ? 's' : ''} remaining)`, 'info');
    };

    JTB.redo = function() {
        if (!JTB.canRedo()) {
            JTB.showNotification('Nothing to redo', 'info');
            return;
        }

        JTB.state.historyIndex++;
        JTB.state.content = JSON.parse(JTB.state.history[JTB.state.historyIndex]);
        JTB.renderCanvas();
        JTB.Settings.close();
        JTB.updateHistoryButtons();

        const redoSteps = JTB.state.history.length - 1 - JTB.state.historyIndex;
        JTB.showNotification(`Redo (${redoSteps} step${redoSteps !== 1 ? 's' : ''} remaining)`, 'info');
    };

    JTB.canUndo = function() {
        return JTB.state.historyIndex > 0;
    };

    JTB.canRedo = function() {
        return JTB.state.historyIndex < JTB.state.history.length - 1;
    };

    JTB.updateHistoryButtons = function() {
        const undoBtn = document.querySelector('[data-action="undo"]');
        const redoBtn = document.querySelector('[data-action="redo"]');

        if (undoBtn) {
            undoBtn.disabled = !JTB.canUndo();
            undoBtn.classList.toggle('disabled', !JTB.canUndo());
            undoBtn.title = JTB.canUndo()
                ? `Undo (${JTB.state.historyIndex} steps)`
                : 'Nothing to undo';
        }

        if (redoBtn) {
            redoBtn.disabled = !JTB.canRedo();
            redoBtn.classList.toggle('disabled', !JTB.canRedo());
            const redoSteps = JTB.state.history.length - 1 - JTB.state.historyIndex;
            redoBtn.title = JTB.canRedo()
                ? `Redo (${redoSteps} steps)`
                : 'Nothing to redo';
        }
    };

    JTB.markDirty = function() {
        JTB.state.isDirty = true;
        JTB.saveHistory();
    };

    // ========================================
    // UI Helpers
    // ========================================

    JTB.createAddSectionButton = function() {
        const btn = document.createElement('button');
        btn.className = 'jtb-add-section-btn';
        btn.innerHTML = '+ Add Section';
        btn.onclick = () => JTB.addSection();
        return btn;
    };

    JTB.showNotification = function(message, type) {
        const container = document.querySelector('.jtb-notifications');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = 'jtb-notification ' + (type || 'info');
        notification.textContent = message;

        container.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };

    // ========================================
    // Event Bindings
    // ========================================

    JTB.bindEvents = function() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's') {
                    e.preventDefault();
                    JTB.saveContent();
                } else if (e.key === 'z') {
                    e.preventDefault();
                    if (e.shiftKey) {
                        JTB.redo();
                    } else {
                        JTB.undo();
                    }
                } else if (e.key === 'y') {
                    e.preventDefault();
                    JTB.redo();
                }
            }
        });

        // Beforeunload warning
        window.addEventListener('beforeunload', (e) => {
            if (JTB.state.isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Header buttons
        document.querySelectorAll('.jtb-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const device = btn.dataset.device;
                JTB.setPreviewDevice(device);
            });
        });

        const saveBtn = document.querySelector('[data-action="save"]');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => JTB.saveContent());
        }

        const undoBtn = document.querySelector('[data-action="undo"]');
        if (undoBtn) {
            undoBtn.addEventListener('click', () => JTB.undo());
        }

        const redoBtn = document.querySelector('[data-action="redo"]');
        if (redoBtn) {
            redoBtn.addEventListener('click', () => JTB.redo());
        }

        const previewBtn = document.querySelector('[data-action="preview"]');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => JTB.openPreview());
        }
    };

    // ========================================
    // Preview
    // ========================================

    JTB.openPreview = function() {
        JTB.showNotification('Generating preview...', 'info');

        const contentJson = JSON.stringify(JTB.state.content);

        JTB.api.post('/render', { content: contentJson })
            .then(response => {
                if (response.success) {
                    JTB.showPreviewModal(response.html, response.css);
                } else {
                    throw new Error(response.error || 'Failed to generate preview');
                }
            })
            .catch(error => {
                JTB.showNotification('Preview failed: ' + error.message, 'error');
            });
    };

    JTB.showPreviewModal = function(html, css) {
        const overlay = document.createElement('div');
        overlay.className = 'jtb-preview-overlay';

        const modal = document.createElement('div');
        modal.className = 'jtb-preview-modal';

        // Header
        const header = document.createElement('div');
        header.className = 'jtb-preview-modal-header';
        header.innerHTML = `
            <span class="jtb-preview-modal-title">Preview</span>
            <div class="jtb-preview-modal-devices">
                <button class="jtb-preview-device-btn active" data-width="100%">${typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('monitor', 16) : '🖥️'}</button>
                <button class="jtb-preview-device-btn" data-width="768px">${typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('tablet', 16) : '📱'}</button>
                <button class="jtb-preview-device-btn" data-width="375px">${typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('smartphone', 16) : '📲'}</button>
            </div>
            <button class="jtb-preview-modal-close">${typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×'}</button>
        `;
        modal.appendChild(header);

        // Content
        const content = document.createElement('div');
        content.className = 'jtb-preview-modal-content';

        const iframe = document.createElement('iframe');
        iframe.className = 'jtb-preview-iframe';
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';
        iframe.style.backgroundColor = '#fff';

        content.appendChild(iframe);
        modal.appendChild(content);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Write content to iframe
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Preview</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
                <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css">
                <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/animations.css">
                <style>${css || ''}</style>
            </head>
            <body>
                <div class="jtb-content">
                    ${html}
                </div>
            </body>
            </html>
        `);
        iframeDoc.close();

        // Bind events
        header.querySelector('.jtb-preview-modal-close').addEventListener('click', () => {
            overlay.remove();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
            }
        });

        // Device buttons
        header.querySelectorAll('.jtb-preview-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                header.querySelectorAll('.jtb-preview-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                iframe.style.width = btn.dataset.width;
            });
        });

        // ESC to close
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    };

    JTB.bindToolbarEvents = function(toolbar, type, indexes) {
        toolbar.querySelectorAll('.jtb-toolbar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;

                switch (action) {
                    case 'settings':
                        JTB.openSettings(type, indexes);
                        break;
                    case 'duplicate':
                        if (type === 'section') JTB.duplicateSection(indexes.sectionIndex);
                        else if (type === 'row') JTB.duplicateRow(indexes.sectionIndex, indexes.rowIndex);
                        else if (type === 'module') JTB.duplicateModule(indexes);
                        break;
                    case 'delete':
                        if (type === 'section') JTB.deleteSection(indexes.sectionIndex);
                        else if (type === 'row') JTB.deleteRow(indexes.sectionIndex, indexes.rowIndex);
                        else if (type === 'module') JTB.deleteModule(indexes);
                        break;
                    case 'columns':
                        JTB.openColumnPicker(indexes);
                        break;
                }
            });
        });
    };

    JTB.setPreviewDevice = function(device) {
        JTB.state.currentDevice = device;

        const canvas = document.querySelector('.jtb-canvas');
        const canvasInner = document.querySelector('.jtb-canvas-inner');

        // Remove all device classes, then add the correct one
        canvas.classList.remove('jtb-preview-desktop', 'jtb-preview-tablet', 'jtb-preview-phone');
        canvas.classList.add('jtb-preview-' + device);

        // Update active button state
        document.querySelectorAll('.jtb-device-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.device === device);
        });

        // Scroll canvas to top for better UX when switching devices
        if (canvas) {
            canvas.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add device width attribute for CSS/JS reference
        if (canvasInner) {
            const widths = { desktop: 1200, tablet: 768, phone: 375 };
            canvasInner.setAttribute('data-device-width', widths[device] || 1200);
        }

        // Trigger resize event for any listeners
        window.dispatchEvent(new CustomEvent('jtb:deviceChange', {
            detail: { device: device, width: JTB.config.breakpoints[device] }
        }));
    };

    // ========================================
    // Pickers (Modals)
    // ========================================

    JTB.openModulePicker = function(indexes) {
        const modules = JTB.config.modules;
        const categories = JTB.config.categories || {};
        const templateType = JTB.config.templateType;

        // Get primary category based on template type
        const primaryCategory = JTB.getPrimaryCategory(templateType);

        // Get category order based on template type
        const categoryOrder = JTB.getCategoryOrder(templateType);

        const closeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×';
        let html = '<div class="jtb-modal-header"><span class="jtb-modal-title">Add Module</span><button class="jtb-modal-close">' + closeIcon + '</button></div>';
        html += '<div class="jtb-modal-body">';

        // Category tabs (skip 'structure' as those modules are added via dedicated buttons)
        html += '<div class="jtb-category-tabs">';
        html += '<button class="jtb-category-tab active" data-category="all">All</button>';

        // Add primary category first if it exists
        if (primaryCategory && categories[primaryCategory]) {
            html += `<button class="jtb-category-tab" data-category="${primaryCategory}">${categories[primaryCategory].label}</button>`;
        }

        // Add other categories in order
        categoryOrder.forEach(catKey => {
            if (catKey === 'structure' || catKey === primaryCategory) return;
            if (categories[catKey]) {
                html += `<button class="jtb-category-tab" data-category="${catKey}">${categories[catKey].label}</button>`;
            }
        });
        html += '</div>';

        // Sort modules: primary category first
        // Filter out structure modules and child modules (child modules are added automatically)
        const sortedSlugs = Object.keys(modules)
            .filter(slug => {
                if (['section', 'row', 'column'].includes(slug)) return false;
                if (modules[slug].is_child) return false; // Hide child modules
                return true;
            })
            .sort((a, b) => {
                const catA = modules[a].category || 'content';
                const catB = modules[b].category || 'content';

                // Primary category first
                if (catA === primaryCategory && catB !== primaryCategory) return -1;
                if (catB === primaryCategory && catA !== primaryCategory) return 1;

                // Then by category order
                const orderA = categoryOrder.indexOf(catA);
                const orderB = categoryOrder.indexOf(catB);
                if (orderA !== -1 && orderB !== -1 && orderA !== orderB) return orderA - orderB;

                // Then by name
                return modules[a].name.localeCompare(modules[b].name);
            });

        // Module grid
        html += '<div class="jtb-module-grid">';
        sortedSlugs.forEach(slug => {
            const module = modules[slug];
            html += `
                <div class="jtb-module-item" data-type="${slug}" data-category="${module.category}">
                    <span class="jtb-module-item-icon">${JTB.getModuleIcon(slug)}</span>
                    <span class="jtb-module-item-name">${module.name}</span>
                </div>
            `;
        });
        html += '</div>';
        html += '</div>';

        const modal = JTB.showModal(html);

        // Bind events on the modal element directly
        if (modal) {
            modal.querySelectorAll('.jtb-module-item').forEach(item => {
                item.addEventListener('click', () => {
                    JTB.addModule(indexes, item.dataset.type);
                });
            });

            modal.querySelectorAll('.jtb-category-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const category = tab.dataset.category;

                    modal.querySelectorAll('.jtb-category-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    modal.querySelectorAll('.jtb-module-item').forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        }
    };

    /**
     * Get primary category for template type
     */
    JTB.getPrimaryCategory = function(templateType) {
        const primaryMap = {
            'header': 'header',
            'footer': 'footer',
            'body': 'dynamic',
            'single': 'dynamic',
            'archive': 'dynamic',
            '404': 'content',
            'search': 'dynamic'
        };
        return primaryMap[templateType] || 'content';
    };

    /**
     * Get category order for template type
     */
    JTB.getCategoryOrder = function(templateType) {
        const orderMap = {
            'header': ['header', 'content', 'media', 'interactive', 'fullwidth'],
            'footer': ['footer', 'content', 'media', 'interactive', 'fullwidth'],
            'body': ['dynamic', 'content', 'media', 'interactive', 'forms', 'blog', 'fullwidth'],
            'single': ['dynamic', 'content', 'media', 'interactive', 'forms', 'blog', 'fullwidth'],
            'archive': ['dynamic', 'content', 'media', 'interactive', 'blog', 'fullwidth'],
            '404': ['content', 'media', 'interactive', 'fullwidth'],
            'search': ['dynamic', 'content', 'media', 'interactive', 'fullwidth']
        };
        return orderMap[templateType] || ['content', 'media', 'interactive', 'forms', 'blog', 'fullwidth', 'header', 'footer', 'dynamic'];
    };

    JTB.openColumnPicker = function(indexes) {
        // Full column structure options (Divi-style)
        const columns = [
            // Row 1: Basic equal columns
            { value: '1', label: '1 Column', cols: [1] },
            { value: '1_2,1_2', label: '2 Equal', cols: [1, 1] },
            { value: '1_3,1_3,1_3', label: '3 Equal', cols: [1, 1, 1] },
            { value: '1_4,1_4,1_4,1_4', label: '4 Equal', cols: [1, 1, 1, 1] },
            { value: '1_5,1_5,1_5,1_5,1_5', label: '5 Equal', cols: [1, 1, 1, 1, 1] },
            { value: '1_6,1_6,1_6,1_6,1_6,1_6', label: '6 Equal', cols: [1, 1, 1, 1, 1, 1] },

            // Row 2: Two columns - asymmetric
            { value: '2_3,1_3', label: '2/3 + 1/3', cols: [2, 1] },
            { value: '1_3,2_3', label: '1/3 + 2/3', cols: [1, 2] },
            { value: '3_4,1_4', label: '3/4 + 1/4', cols: [3, 1] },
            { value: '1_4,3_4', label: '1/4 + 3/4', cols: [1, 3] },
            { value: '4_5,1_5', label: '4/5 + 1/5', cols: [4, 1] },
            { value: '1_5,4_5', label: '1/5 + 4/5', cols: [1, 4] },

            // Row 3: Two columns - more asymmetric
            { value: '5_6,1_6', label: '5/6 + 1/6', cols: [5, 1] },
            { value: '1_6,5_6', label: '1/6 + 5/6', cols: [1, 5] },
            { value: '2_5,3_5', label: '2/5 + 3/5', cols: [2, 3] },
            { value: '3_5,2_5', label: '3/5 + 2/5', cols: [3, 2] },

            // Row 4: Three columns - asymmetric
            { value: '1_4,1_2,1_4', label: '1/4 + 1/2 + 1/4', cols: [1, 2, 1] },
            { value: '1_2,1_4,1_4', label: '1/2 + 1/4 + 1/4', cols: [2, 1, 1] },
            { value: '1_4,1_4,1_2', label: '1/4 + 1/4 + 1/2', cols: [1, 1, 2] },
            { value: '1_5,3_5,1_5', label: '1/5 + 3/5 + 1/5', cols: [1, 3, 1] },
            { value: '1_6,2_3,1_6', label: '1/6 + 2/3 + 1/6', cols: [1, 4, 1] },
            { value: '1_6,1_6,2_3', label: '1/6 + 1/6 + 2/3', cols: [1, 1, 4] },

            // Row 5: Three columns - more variations
            { value: '2_3,1_6,1_6', label: '2/3 + 1/6 + 1/6', cols: [4, 1, 1] },
            { value: '1_5,1_5,3_5', label: '1/5 + 1/5 + 3/5', cols: [1, 1, 3] },
            { value: '3_5,1_5,1_5', label: '3/5 + 1/5 + 1/5', cols: [3, 1, 1] },
            { value: '1_4,1_4,1_4,1_4', label: '4 Equal', cols: [1, 1, 1, 1] },

            // Row 6: Four columns - asymmetric
            { value: '1_2,1_6,1_6,1_6', label: '1/2 + 1/6×3', cols: [3, 1, 1, 1] },
            { value: '1_6,1_6,1_6,1_2', label: '1/6×3 + 1/2', cols: [1, 1, 1, 3] },
            { value: '1_4,1_4,1_4,1_4', label: '1/4×4', cols: [1, 1, 1, 1] },
            { value: '1_5,1_5,1_5,2_5', label: '1/5×3 + 2/5', cols: [1, 1, 1, 2] },
            { value: '2_5,1_5,1_5,1_5', label: '2/5 + 1/5×3', cols: [2, 1, 1, 1] },
            { value: '1_6,1_3,1_3,1_6', label: '1/6 + 1/3 + 1/3 + 1/6', cols: [1, 2, 2, 1] }
        ];

        const colCloseIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×';
        let html = '<div class="jtb-modal-header"><span class="jtb-modal-title">Choose Column Structure</span><button class="jtb-modal-close">' + colCloseIcon + '</button></div>';
        html += '<div class="jtb-modal-body">';
        html += '<div class="jtb-column-picker">';

        columns.forEach(col => {
            html += `<div class="jtb-column-option" data-columns="${col.value}" title="${col.label}">`;
            html += '<div class="jtb-column-preview">';
            col.cols.forEach(size => {
                html += `<div class="jtb-column-preview-col" style="flex: ${size}"></div>`;
            });
            html += '</div></div>';
        });

        html += '</div></div>';

        const modal = JTB.showModal(html);

        // Bind events on the modal element directly
        if (modal) {
            modal.querySelectorAll('.jtb-column-option').forEach(option => {
                option.addEventListener('click', () => {
                    const columnsValue = option.dataset.columns;
                    if (indexes.rowIndex !== undefined) {
                        JTB.changeRowColumns(indexes, columnsValue);
                    } else {
                        JTB.addRow(indexes.sectionIndex, columnsValue);
                        JTB.closeModal();
                    }
                });
            });
        }
    };

    JTB.showModal = function(content) {
        // Remove existing builder modal
        JTB.closeModal();

        const overlay = document.createElement('div');
        overlay.className = 'jtb-modal-overlay';
        overlay.id = 'jtb-builder-modal';
        overlay.innerHTML = `<div class="jtb-modal">${content}</div>`;

        document.body.appendChild(overlay);

        // Add 'show' class after a brief delay to trigger CSS transition
        requestAnimationFrame(() => {
            overlay.classList.add('show');
        });

        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                JTB.closeModal();
            }
        });

        // Close button
        const closeBtn = overlay.querySelector('.jtb-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => JTB.closeModal());
        }

        // Return the overlay element so callers can bind events
        return overlay;
    };

    JTB.closeModal = function() {
        const overlay = document.getElementById('jtb-builder-modal');
        if (overlay) {
            // Remove 'show' class for fade-out animation
            overlay.classList.remove('show');
            // Wait for animation to complete before removing
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.remove();
                }
            }, 300);
        }
    };

    // ========================================
    // Settings
    // ========================================

    JTB.openSettings = function(type, indexes) {
        let element, moduleConfig;

        if (type === 'section') {
            element = JTB.state.content.content[indexes.sectionIndex];
            moduleConfig = JTB.config.modules.section;
        } else if (type === 'row') {
            element = JTB.state.content.content[indexes.sectionIndex].children[indexes.rowIndex];
            moduleConfig = JTB.config.modules.row;
        } else if (type === 'module') {
            element = JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex]
                .children[indexes.moduleIndex];
            moduleConfig = JTB.config.modules[element.type];
        }

        if (!element || !moduleConfig) return;

        JTB.state.selectedModule = element;
        JTB.state.selectedType = type;
        JTB.state.selectedIndexes = indexes;

        JTB.Settings.render(moduleConfig, element);
    };

    // ========================================
    // Drag & Drop
    // ========================================

    JTB.handleModuleDrop = function(e, targetIndexes) {
        // Check for module-type (from sidebar drag)
        const moduleType = e.dataTransfer.getData('module-type');
        if (moduleType) {
            // Add new module from sidebar
            JTB.addModule(targetIndexes, moduleType);
            return;
        }

        // Check for text/plain (internal move)
        const data = e.dataTransfer.getData('text/plain');
        if (!data) return;

        try {
            const dragData = JSON.parse(data);

            if (dragData.type === 'move') {
                const sourceIndexes = dragData.indexes;

                // Get module
                const sourceColumn = JTB.state.content.content[sourceIndexes.sectionIndex]
                    .children[sourceIndexes.rowIndex]
                    .children[sourceIndexes.columnIndex];

                const module = sourceColumn.children.splice(sourceIndexes.moduleIndex, 1)[0];

                // Insert into target
                const targetColumn = JTB.state.content.content[targetIndexes.sectionIndex]
                    .children[targetIndexes.rowIndex]
                    .children[targetIndexes.columnIndex];

                targetColumn.children.push(module);

                JTB.markDirty();
                JTB.renderCanvas();
            }
        } catch (err) {
            // If not JSON, treat as module type string (fallback)
            if (typeof data === 'string' && data.length > 0 && !data.startsWith('{')) {
                JTB.addModule(targetIndexes, data);
            } else {
                console.error('Drop error:', err);
            }
        }
    };

    // ========================================
    // Utilities
    // ========================================

    JTB.escapeHtml = function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    // ========================================
    // Copy/Paste Styles
    // ========================================

    JTB.clipboard = {
        styles: null,
        module: null
    };

    /**
     * Copy styles from current element
     */
    JTB.copyStyles = function() {
        if (!JTB.state.selectedModule) {
            JTB.showNotification('No element selected', 'warning');
            return;
        }

        const attrs = JTB.state.selectedModule.attrs || {};

        // Extract style-related attributes (design fields)
        const styleAttrs = {};
        const styleKeys = [
            // Background
            'background_type', 'background_color', 'background_color__hover',
            'background_gradient_type', 'background_gradient_direction', 'background_gradient_stops',
            'background_gradient_start', 'background_gradient_end',
            'background_image', 'background_size', 'background_position', 'background_repeat',
            // Spacing
            'margin', 'padding',
            // Border
            'border_width', 'border_style', 'border_color', 'border_radius',
            // Box Shadow
            'box_shadow_style', 'box_shadow_horizontal', 'box_shadow_vertical',
            'box_shadow_blur', 'box_shadow_spread', 'box_shadow_color',
            // Typography
            'font_family', 'font_size', 'font_weight', 'font_style',
            'text_transform', 'text_decoration', 'line_height', 'letter_spacing',
            'text_color', 'text_color__hover', 'text_align',
            'text_shadow_style', 'text_shadow_horizontal', 'text_shadow_vertical',
            'text_shadow_blur', 'text_shadow_color',
            // Transform
            'transform_scale', 'transform_rotate', 'transform_translate_x', 'transform_translate_y',
            'transform_skew_x', 'transform_skew_y', 'transform_origin',
            // Filters
            'filter_brightness', 'filter_contrast', 'filter_saturation',
            'filter_blur', 'filter_hue_rotate', 'filter_invert', 'filter_sepia', 'filter_opacity',
            // Animation
            'animation_style', 'animation_duration', 'animation_delay',
            'animation_intensity', 'animation_direction', 'animation_repeat'
        ];

        // Also include responsive variants
        const responsiveSuffixes = ['__tablet', '__phone', '__hover'];

        styleKeys.forEach(key => {
            if (attrs[key] !== undefined) {
                styleAttrs[key] = JSON.parse(JSON.stringify(attrs[key]));
            }
            responsiveSuffixes.forEach(suffix => {
                const responsiveKey = key + suffix;
                if (attrs[responsiveKey] !== undefined) {
                    styleAttrs[responsiveKey] = JSON.parse(JSON.stringify(attrs[responsiveKey]));
                }
            });
        });

        JTB.clipboard.styles = styleAttrs;
        JTB.showNotification('Styles copied', 'success');
    };

    /**
     * Paste styles to current element
     */
    JTB.pasteStyles = function() {
        if (!JTB.state.selectedModule) {
            JTB.showNotification('No element selected', 'warning');
            return;
        }

        if (!JTB.clipboard.styles) {
            JTB.showNotification('No styles in clipboard', 'warning');
            return;
        }

        // Merge clipboard styles into current element attrs
        if (!JTB.state.selectedModule.attrs) {
            JTB.state.selectedModule.attrs = {};
        }

        Object.assign(JTB.state.selectedModule.attrs, JSON.parse(JSON.stringify(JTB.clipboard.styles)));

        JTB.markDirty();
        JTB.renderCanvas();
        JTB.showNotification('Styles pasted', 'success');

        // Re-open settings panel to show updated values
        if (JTB.state.selectedType && JTB.state.selectedIndexes) {
            JTB.openSettings(JTB.state.selectedType, JTB.state.selectedIndexes);
        }
    };

    /**
     * Copy entire module
     */
    JTB.copyModule = function() {
        if (!JTB.state.selectedModule) {
            JTB.showNotification('No element selected', 'warning');
            return;
        }

        JTB.clipboard.module = JSON.parse(JSON.stringify(JTB.state.selectedModule));
        JTB.showNotification('Module copied', 'success');
    };

    /**
     * Paste module after current position
     */
    JTB.pasteModule = function() {
        if (!JTB.clipboard.module) {
            JTB.showNotification('No module in clipboard', 'warning');
            return;
        }

        if (!JTB.state.selectedIndexes) {
            JTB.showNotification('Select a location first', 'warning');
            return;
        }

        const newModule = JSON.parse(JSON.stringify(JTB.clipboard.module));
        // Generate new ID
        newModule.id = JTB.clipboard.module.type + '_' + Math.random().toString(36).substr(2, 9);

        const indexes = JTB.state.selectedIndexes;
        const column = JTB.state.content.content[indexes.sectionIndex]
            .children[indexes.rowIndex]
            .children[indexes.columnIndex];

        // Insert after current position
        const insertIndex = (indexes.moduleIndex !== undefined ? indexes.moduleIndex + 1 : column.children.length);
        column.children.splice(insertIndex, 0, newModule);

        JTB.markDirty();
        JTB.renderCanvas();
        JTB.showNotification('Module pasted', 'success');
    };

    // ========================================
    // Right-Click Context Menu
    // ========================================

    JTB.contextMenu = {
        element: null,
        targetElement: null,
        targetType: null,
        targetIndexes: null
    };

    /**
     * Initialize context menu
     */
    JTB.initContextMenu = function() {
        // Create context menu element
        const menu = document.createElement('div');
        menu.className = 'jtb-context-menu';
        menu.id = 'jtb-context-menu';
        menu.style.display = 'none';
        document.body.appendChild(menu);
        JTB.contextMenu.element = menu;

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) {
                JTB.hideContextMenu();
            }
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                JTB.hideContextMenu();
            }
        });
    };

    /**
     * Show context menu for element
     */
    JTB.showContextMenu = function(e, type, indexes, element) {
        e.preventDefault();

        JTB.contextMenu.targetElement = element;
        JTB.contextMenu.targetType = type;
        JTB.contextMenu.targetIndexes = indexes;

        const menu = JTB.contextMenu.element;
        if (!menu) return;

        // Build menu items based on type
        let menuItems = [];

        // Helper function for context menu icons
        const getIcon = (name, size) => {
            size = size || 16;
            const iconMap = {
                'settings': 'settings',
                'add-row': 'plus',
                'duplicate': 'copy',
                'copy': 'clipboard',
                'paste': 'clipboard',
                'move-up': 'chevron-up',
                'move-down': 'chevron-down',
                'delete': 'trash-2',
                'edit-columns': 'columns',
                'copy-styles': 'droplet',
                'paste-styles': 'droplet'
            };
            if (typeof JTB.getFeatherIcon === 'function') {
                return JTB.getFeatherIcon(iconMap[name] || 'circle', size, 2);
            }
            return '';
        };

        // Common items
        menuItems.push({ label: 'Settings', icon: getIcon('settings'), action: 'settings' });
        menuItems.push({ type: 'divider' });

        if (type === 'section') {
            menuItems.push({ label: 'Add Row', icon: getIcon('add-row'), action: 'add-row' });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Duplicate Section', icon: getIcon('duplicate'), action: 'duplicate' });
            menuItems.push({ label: 'Copy Section', icon: getIcon('copy'), action: 'copy' });
            menuItems.push({ label: 'Move Up', icon: getIcon('move-up'), action: 'move-up' });
            menuItems.push({ label: 'Move Down', icon: getIcon('move-down'), action: 'move-down' });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Delete Section', icon: getIcon('delete'), action: 'delete', danger: true });
        } else if (type === 'row') {
            menuItems.push({ label: 'Edit Columns', icon: getIcon('edit-columns'), action: 'edit-columns' });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Duplicate Row', icon: getIcon('duplicate'), action: 'duplicate' });
            menuItems.push({ label: 'Copy Row', icon: getIcon('copy'), action: 'copy' });
            menuItems.push({ label: 'Move Up', icon: getIcon('move-up'), action: 'move-up' });
            menuItems.push({ label: 'Move Down', icon: getIcon('move-down'), action: 'move-down' });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Delete Row', icon: getIcon('delete'), action: 'delete', danger: true });
        } else if (type === 'module') {
            menuItems.push({ label: 'Copy Styles', icon: getIcon('copy-styles'), action: 'copy-styles' });
            menuItems.push({ label: 'Paste Styles', icon: getIcon('paste-styles'), action: 'paste-styles', disabled: !JTB.clipboard.styles });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Duplicate Module', icon: getIcon('duplicate'), action: 'duplicate' });
            menuItems.push({ label: 'Copy Module', icon: getIcon('copy'), action: 'copy' });
            menuItems.push({ label: 'Paste Module', icon: getIcon('paste'), action: 'paste', disabled: !JTB.clipboard.module });
            menuItems.push({ label: 'Move Up', icon: getIcon('move-up'), action: 'move-up' });
            menuItems.push({ label: 'Move Down', icon: getIcon('move-down'), action: 'move-down' });
            menuItems.push({ type: 'divider' });
            menuItems.push({ label: 'Delete Module', icon: getIcon('delete'), action: 'delete', danger: true });
        }

        // Render menu
        menu.innerHTML = menuItems.map(item => {
            if (item.type === 'divider') {
                return '<div class="jtb-context-menu-divider"></div>';
            }
            const classes = ['jtb-context-menu-item'];
            if (item.danger) classes.push('danger');
            if (item.disabled) classes.push('disabled');
            return `<div class="${classes.join(' ')}" data-action="${item.action}">
                <span class="jtb-context-menu-icon">${item.icon}</span>
                <span class="jtb-context-menu-label">${item.label}</span>
            </div>`;
        }).join('');

        // Bind click events
        menu.querySelectorAll('.jtb-context-menu-item:not(.disabled)').forEach(item => {
            item.addEventListener('click', () => {
                JTB.handleContextMenuAction(item.dataset.action);
                JTB.hideContextMenu();
            });
        });

        // Position menu
        const x = e.clientX;
        const y = e.clientY;
        const menuWidth = 200;
        const menuHeight = menu.offsetHeight || 300;

        let posX = x;
        let posY = y;

        // Keep menu in viewport
        if (x + menuWidth > window.innerWidth) {
            posX = x - menuWidth;
        }
        if (y + menuHeight > window.innerHeight) {
            posY = y - menuHeight;
        }

        menu.style.left = posX + 'px';
        menu.style.top = posY + 'px';
        menu.style.display = 'block';
    };

    /**
     * Hide context menu
     */
    JTB.hideContextMenu = function() {
        if (JTB.contextMenu.element) {
            JTB.contextMenu.element.style.display = 'none';
        }
    };

    /**
     * Handle context menu action
     */
    JTB.handleContextMenuAction = function(action) {
        const type = JTB.contextMenu.targetType;
        const indexes = JTB.contextMenu.targetIndexes;

        // First select the element
        JTB.state.selectedType = type;
        JTB.state.selectedIndexes = indexes;

        // Get the element
        let element;
        if (type === 'section') {
            element = JTB.state.content.content[indexes.sectionIndex];
        } else if (type === 'row') {
            element = JTB.state.content.content[indexes.sectionIndex].children[indexes.rowIndex];
        } else if (type === 'module') {
            element = JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex]
                .children[indexes.moduleIndex];
        }
        JTB.state.selectedModule = element;

        switch (action) {
            case 'settings':
                JTB.openSettings(type, indexes);
                break;

            case 'copy-styles':
                JTB.copyStyles();
                break;

            case 'paste-styles':
                JTB.pasteStyles();
                break;

            case 'duplicate':
                JTB.duplicateElement(type, indexes);
                break;

            case 'copy':
                JTB.copyModule();
                break;

            case 'paste':
                JTB.pasteModule();
                break;

            case 'delete':
                JTB.deleteElement(type, indexes);
                break;

            case 'move-up':
                JTB.moveElement(type, indexes, -1);
                break;

            case 'move-down':
                JTB.moveElement(type, indexes, 1);
                break;

            case 'add-row':
                if (type === 'section') {
                    JTB.addRow(indexes.sectionIndex);
                }
                break;

            case 'edit-columns':
                if (type === 'row') {
                    JTB.editRowColumns(indexes);
                }
                break;
        }
    };

    /**
     * Duplicate element
     */
    JTB.duplicateElement = function(type, indexes) {
        if (type === 'section') {
            const section = JTB.state.content.content[indexes.sectionIndex];
            const newSection = JSON.parse(JSON.stringify(section));
            newSection.id = 'section_' + Math.random().toString(36).substr(2, 9);
            // Also update child IDs
            JTB.regenerateIds(newSection);
            JTB.state.content.content.splice(indexes.sectionIndex + 1, 0, newSection);
        } else if (type === 'row') {
            const section = JTB.state.content.content[indexes.sectionIndex];
            const row = section.children[indexes.rowIndex];
            const newRow = JSON.parse(JSON.stringify(row));
            newRow.id = 'row_' + Math.random().toString(36).substr(2, 9);
            JTB.regenerateIds(newRow);
            section.children.splice(indexes.rowIndex + 1, 0, newRow);
        } else if (type === 'module') {
            const column = JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex];
            const module = column.children[indexes.moduleIndex];
            const newModule = JSON.parse(JSON.stringify(module));
            newModule.id = module.type + '_' + Math.random().toString(36).substr(2, 9);
            column.children.splice(indexes.moduleIndex + 1, 0, newModule);
        }

        JTB.markDirty();
        JTB.renderCanvas();
        JTB.showNotification('Element duplicated', 'success');
    };

    /**
     * Move element up or down
     */
    JTB.moveElement = function(type, indexes, direction) {
        if (type === 'section') {
            const sections = JTB.state.content.content;
            const newIndex = indexes.sectionIndex + direction;
            if (newIndex < 0 || newIndex >= sections.length) return;
            const [section] = sections.splice(indexes.sectionIndex, 1);
            sections.splice(newIndex, 0, section);
        } else if (type === 'row') {
            const rows = JTB.state.content.content[indexes.sectionIndex].children;
            const newIndex = indexes.rowIndex + direction;
            if (newIndex < 0 || newIndex >= rows.length) return;
            const [row] = rows.splice(indexes.rowIndex, 1);
            rows.splice(newIndex, 0, row);
        } else if (type === 'module') {
            const modules = JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex].children;
            const newIndex = indexes.moduleIndex + direction;
            if (newIndex < 0 || newIndex >= modules.length) return;
            const [module] = modules.splice(indexes.moduleIndex, 1);
            modules.splice(newIndex, 0, module);
        }

        JTB.markDirty();
        JTB.renderCanvas();
    };

    /**
     * Delete element
     */
    JTB.deleteElement = function(type, indexes) {
        if (type === 'section') {
            JTB.state.content.content.splice(indexes.sectionIndex, 1);
        } else if (type === 'row') {
            JTB.state.content.content[indexes.sectionIndex].children.splice(indexes.rowIndex, 1);
        } else if (type === 'module') {
            JTB.state.content.content[indexes.sectionIndex]
                .children[indexes.rowIndex]
                .children[indexes.columnIndex]
                .children.splice(indexes.moduleIndex, 1);
        }

        JTB.state.selectedModule = null;
        JTB.state.selectedType = null;
        JTB.state.selectedIndexes = null;

        JTB.markDirty();
        JTB.renderCanvas();
        JTB.closeSettingsPanel();
        JTB.showNotification('Element deleted', 'success');
    };

    /**
     * Regenerate IDs for element and children
     */
    JTB.regenerateIds = function(element) {
        if (element.id) {
            const type = element.type || element.id.split('_')[0];
            element.id = type + '_' + Math.random().toString(36).substr(2, 9);
        }
        if (element.children && Array.isArray(element.children)) {
            element.children.forEach(child => JTB.regenerateIds(child));
        }
    };

    /**
     * Close settings panel
     */
    JTB.closeSettingsPanel = function() {
        const panel = document.querySelector('.jtb-settings-panel');
        if (panel) {
            panel.classList.remove('active');
        }
    };

    /**
     * Edit row columns layout
     */
    JTB.editRowColumns = function(indexes) {
        // Open settings for the row with columns field focused
        JTB.openSettings('row', indexes);
    };

    // Initialize context menu when builder loads
    const originalInit = JTB.init;
    JTB.init = function(options) {
        originalInit.call(JTB, options);
        JTB.initContextMenu();
    };

    // ========================================
    // Keyboard Shortcuts Enhancement
    // ========================================

    // Add copy/paste shortcuts to existing keyboard handler
    const originalBindEvents = JTB.bindEvents;
    JTB.bindEvents = function() {
        originalBindEvents.call(JTB);

        // Additional keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'c' && e.shiftKey) {
                    // Ctrl+Shift+C = Copy styles
                    e.preventDefault();
                    JTB.copyStyles();
                } else if (e.key === 'v' && e.shiftKey) {
                    // Ctrl+Shift+V = Paste styles
                    e.preventDefault();
                    JTB.pasteStyles();
                } else if (e.key === 'd') {
                    // Ctrl+D = Duplicate
                    e.preventDefault();
                    if (JTB.state.selectedType && JTB.state.selectedIndexes) {
                        JTB.duplicateElement(JTB.state.selectedType, JTB.state.selectedIndexes);
                    }
                }
            }

            // Delete key
            if (e.key === 'Delete' && JTB.state.selectedType && JTB.state.selectedIndexes) {
                // Only delete if not focused on input/textarea
                if (!['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    JTB.deleteElement(JTB.state.selectedType, JTB.state.selectedIndexes);
                }
            }
        });
    };

    // ========================================
    // Template Library Integration
    // ========================================

    /**
     * Open template library modal
     * @param {string} templateType - Optional: 'header', 'footer', 'body' for Theme Builder context
     */
    JTB.openLibrary = function(templateType) {
        // Detect template type from Template Editor context if not provided
        if (!templateType && typeof JTBTemplateEditor !== 'undefined' && JTBTemplateEditor.templateType) {
            templateType = JTBTemplateEditor.templateType;
        }

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'jtb-library-overlay';
        overlay.id = 'jtb-library-overlay';

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'jtb-library-modal';

        // Determine title based on context
        let title = 'Template Library';
        if (templateType === 'header') title = 'Header Layouts';
        else if (templateType === 'footer') title = 'Footer Layouts';
        else if (templateType === 'body') title = 'Body Layouts';

        // Header
        const libCloseIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×';
        const header = document.createElement('div');
        header.className = 'jtb-library-modal-header';
        header.innerHTML = `
            <span class="jtb-library-modal-title">${title}</span>
            <button class="jtb-library-modal-close">${libCloseIcon}</button>
        `;
        modal.appendChild(header);

        // Content (iframe)
        const content = document.createElement('div');
        content.className = 'jtb-library-modal-content';

        // Build URL with template type parameter
        let iframeSrc = '/admin/jtb/library?embed=1';
        if (templateType) {
            iframeSrc += '&template_type=' + encodeURIComponent(templateType);
        }

        const iframe = document.createElement('iframe');
        iframe.src = iframeSrc;
        iframe.className = 'jtb-library-iframe';
        iframe.id = 'jtb-library-iframe';

        content.appendChild(iframe);
        modal.appendChild(content);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Bind close events
        header.querySelector('.jtb-library-modal-close').addEventListener('click', () => {
            JTB.closeLibrary();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                JTB.closeLibrary();
            }
        });

        // Listen for messages from iframe
        window.addEventListener('message', JTB.handleLibraryMessage);
    };

    /**
     * Close template library modal
     */
    JTB.closeLibrary = function() {
        const overlay = document.getElementById('jtb-library-overlay');
        if (overlay) {
            overlay.remove();
        }
        window.removeEventListener('message', JTB.handleLibraryMessage);
    };

    /**
     * Handle messages from library iframe
     */
    JTB.handleLibraryMessage = function(event) {
        if (event.data && event.data.type === 'jtb-library-select') {
            const template = event.data.template;
            JTB.insertLibraryContent(template.content);
            JTB.closeLibrary();
        }
    };

    /**
     * Insert content from library template
     */
    JTB.insertLibraryContent = function(templateContent) {
        if (!templateContent) return;

        // Regenerate IDs to avoid conflicts
        const content = JTB.regenerateContentIds(templateContent);

        // Get elements to insert
        let elements = [];

        if (content.content && Array.isArray(content.content)) {
            elements = content.content;
        } else if (Array.isArray(content)) {
            elements = content;
        } else if (content.type) {
            elements = [content];
        }

        if (elements.length === 0) {
            JTB.showNotification('No content to insert', 'warning');
            return;
        }

        // Insert each element
        elements.forEach(element => {
            if (element.type === 'section') {
                JTB.state.content.content.push(element);
            } else {
                // Wrap non-sections in a section
                JTB.state.content.content.push({
                    type: 'section',
                    id: 'section_' + JTB.generateId(),
                    attrs: {},
                    children: [{
                        type: 'row',
                        id: 'row_' + JTB.generateId(),
                        attrs: { columns: '1' },
                        children: [{
                            type: 'column',
                            id: 'column_' + JTB.generateId(),
                            attrs: {},
                            children: [element]
                        }]
                    }]
                });
            }
        });

        JTB.renderCanvas();
        JTB.markDirty();
        JTB.showNotification('Template content added!', 'success');
    };

    /**
     * Regenerate all IDs in content to avoid conflicts
     */
    JTB.regenerateContentIds = function(content) {
        if (!content) return content;

        const clone = JSON.parse(JSON.stringify(content));

        const regenerate = (element) => {
            if (element.id) {
                const prefix = element.id.split('_')[0] || 'el';
                element.id = prefix + '_' + JTB.generateId();
            }

            if (element.children && Array.isArray(element.children)) {
                element.children.forEach(child => regenerate(child));
            }

            return element;
        };

        if (clone.content && Array.isArray(clone.content)) {
            clone.content = clone.content.map(el => regenerate(el));
        } else if (Array.isArray(clone)) {
            return clone.map(el => regenerate(el));
        } else if (clone.type) {
            return regenerate(clone);
        }

        return clone;
    };

    // ========================================
    // Layout Library (Pages & Sections OR Theme Builder)
    // ========================================

    JTB.layoutLibraryData = null;
    JTB.layoutLibraryCategories = null;
    JTB.layoutLibraryTab = 'pages';
    JTB.layoutLibraryCategory = 'all';
    JTB.layoutLibraryIsThemeBuilder = false;
    JTB.layoutLibraryTemplateType = '';

    /**
     * Open Layout Library modal
     */
    JTB.openLayoutGallery = function() {
        // Detect Theme Builder context
        const isThemeBuilder = typeof JTBTemplateEditor !== 'undefined' && JTBTemplateEditor.templateType;
        const templateType = isThemeBuilder ? JTBTemplateEditor.templateType : '';

        JTB.layoutLibraryIsThemeBuilder = isThemeBuilder;
        JTB.layoutLibraryTemplateType = templateType;

        // Set initial tab based on context
        if (isThemeBuilder) {
            JTB.layoutLibraryTab = templateType; // 'header', 'footer', or 'body'
        } else {
            JTB.layoutLibraryTab = 'pages';
        }
        JTB.layoutLibraryCategory = 'all';

        const overlay = document.createElement('div');
        overlay.className = 'jtb-modal-overlay';
        overlay.id = 'jtb-layout-gallery-overlay';

        const modal = document.createElement('div');
        modal.className = 'jtb-modal jtb-layout-library-modal';

        const closeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×';

        // Build tabs based on context
        let tabsHtml = '';
        let titleText = 'Layout Library';

        if (isThemeBuilder) {
            // Theme Builder mode - show theme builder tabs
            titleText = templateType.charAt(0).toUpperCase() + templateType.slice(1) + ' Layouts';
            tabsHtml = `
                <button class="jtb-tab-btn ${templateType === 'header' ? 'active' : ''}" data-tab="header">Header Layouts</button>
                <button class="jtb-tab-btn ${templateType === 'footer' ? 'active' : ''}" data-tab="footer">Footer Layouts</button>
                <button class="jtb-tab-btn ${templateType === 'body' ? 'active' : ''}" data-tab="body">Body Layouts</button>
            `;
        } else {
            // Page Builder mode - show page/section tabs
            tabsHtml = `
                <button class="jtb-tab-btn active" data-tab="pages">Page Layouts</button>
                <button class="jtb-tab-btn" data-tab="sections">Section Layouts</button>
            `;
        }

        modal.innerHTML = `
            <div class="jtb-modal-header">
                <span class="jtb-modal-title">${titleText}</span>
                <button class="jtb-modal-close">${closeIcon}</button>
            </div>
            <div class="jtb-layout-library-tabs">
                ${tabsHtml}
            </div>
            <div class="jtb-layout-library-sidebar">
                <div class="jtb-category-list" id="jtbCategoryList"></div>
            </div>
            <div class="jtb-modal-content">
                <div class="jtb-loading"><div class="jtb-spinner"></div><p>Loading layouts...</p></div>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        requestAnimationFrame(() => overlay.classList.add('active'));

        // Events
        modal.querySelector('.jtb-modal-close').addEventListener('click', JTB.closeLayoutGallery);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) JTB.closeLayoutGallery(); });

        // Tab switching
        modal.querySelectorAll('.jtb-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.querySelectorAll('.jtb-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                JTB.layoutLibraryTab = btn.dataset.tab;
                JTB.layoutLibraryCategory = 'all';

                // For Theme Builder, reload data for new tab
                if (JTB.layoutLibraryIsThemeBuilder) {
                    JTB.loadLayoutLibrary();
                } else {
                    JTB.renderLayoutLibraryCategories();
                    JTB.renderLayoutLibraryGrid();
                }
            });
        });

        // Load data
        JTB.loadLayoutLibrary();
    };

    /**
     * Load layout library data from API
     */
    JTB.loadLayoutLibrary = async function() {
        try {
            let url = '/api/jtb/layout-library';

            // For Theme Builder, use different API
            if (JTB.layoutLibraryIsThemeBuilder) {
                url = '/api/jtb/library-theme-builder?type=' + encodeURIComponent(JTB.layoutLibraryTab);
            }

            const response = await fetch(url);
            const data = await response.json();

            if (!data.success) throw new Error(data.error);

            if (JTB.layoutLibraryIsThemeBuilder) {
                // Transform Theme Builder data to expected format
                JTB.layoutLibraryData = {
                    header: [],
                    footer: [],
                    body: []
                };

                // Put layouts in appropriate tab
                if (data.layouts) {
                    data.layouts.forEach(layout => {
                        const type = layout.type || JTB.layoutLibraryTab;
                        if (!JTB.layoutLibraryData[type]) {
                            JTB.layoutLibraryData[type] = [];
                        }
                        JTB.layoutLibraryData[type].push(layout);
                    });
                }

                JTB.layoutLibraryCategories = {
                    header: { all: 'All Headers' },
                    footer: { all: 'All Footers' },
                    body: { all: 'All Body Layouts' }
                };
            } else {
                JTB.layoutLibraryData = data.layouts;
                JTB.layoutLibraryCategories = data.categories;
            }

            JTB.renderLayoutLibraryCategories();
            JTB.renderLayoutLibraryGrid();
        } catch (error) {
            console.error('Failed to load layout library:', error);
            document.querySelector('.jtb-layout-library-modal .jtb-modal-content').innerHTML =
                '<p class="jtb-error">Failed to load layouts</p>';
        }
    };

    /**
     * Render category sidebar
     */
    JTB.renderLayoutLibraryCategories = function() {
        const list = document.getElementById('jtbCategoryList');
        if (!list || !JTB.layoutLibraryCategories) return;

        const cats = JTB.layoutLibraryCategories[JTB.layoutLibraryTab] || {};

        list.innerHTML = Object.entries(cats).map(([key, label]) => `
            <button class="jtb-category-btn ${key === JTB.layoutLibraryCategory ? 'active' : ''}" data-cat="${key}">
                ${label}
            </button>
        `).join('');

        list.querySelectorAll('.jtb-category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                list.querySelectorAll('.jtb-category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                JTB.layoutLibraryCategory = btn.dataset.cat;
                JTB.renderLayoutLibraryGrid();
            });
        });
    };

    /**
     * Render layout grid
     */
    JTB.renderLayoutLibraryGrid = function() {
        const container = document.querySelector('.jtb-layout-library-modal .jtb-modal-content');
        if (!container || !JTB.layoutLibraryData) return;

        const layouts = JTB.layoutLibraryData[JTB.layoutLibraryTab] || [];
        const filtered = JTB.layoutLibraryCategory === 'all'
            ? layouts
            : layouts.filter(l => l.category === JTB.layoutLibraryCategory);

        if (filtered.length === 0) {
            container.innerHTML = '<p class="jtb-empty">No layouts in this category</p>';
            return;
        }

        container.innerHTML = `
            <div class="jtb-layout-library-grid">
                ${filtered.map(layout => `
                    <div class="jtb-layout-card" data-id="${layout.id}">
                        <div class="jtb-layout-card-preview">
                            ${JTB.renderLayoutPreview(layout.content)}
                        </div>
                        <div class="jtb-layout-card-info">
                            <span class="jtb-layout-card-name">${JTB.escapeHtml(layout.name)}</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        container.querySelectorAll('.jtb-layout-card').forEach(card => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                const layout = filtered.find(l => l.id === id);
                if (layout) {
                    JTB.applyLayoutContent(layout.content);
                    JTB.closeLayoutGallery();
                }
            });
        });
    };

    /**
     * Render layout preview - shows section structure
     */
    JTB.renderLayoutPreview = function(content) {
        if (!content || !content.content) return '<div class="jtb-preview-empty"></div>';

        const sections = content.content.slice(0, 6); // Max 6 sections in preview
        return sections.map(section => {
            const rows = (section.children || []).slice(0, 3); // Max 3 rows per section
            return `
                <div class="jtb-preview-section">
                    ${rows.map(row => {
                        const cols = row.children || [];
                        return `
                            <div class="jtb-preview-row">
                                ${cols.map(col => {
                                    const width = JTB.getColPercent(col.attrs?.width || '1');
                                    return `<div class="jtb-preview-col" style="width:${width}%"></div>`;
                                }).join('')}
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }).join('');
    };

    /**
     * Get column width as percentage
     */
    JTB.getColPercent = function(width) {
        const map = {
            '1': 100, '1/2': 50, '1/3': 33.33, '2/3': 66.66,
            '1/4': 25, '3/4': 75, '1/5': 20, '2/5': 40, '3/5': 60,
            '1/6': 16.66, '5/6': 83.33
        };
        return map[width] || 100;
    };

    /**
     * Apply layout content to canvas
     */
    JTB.applyLayoutContent = function(content) {
        if (!content || !content.content) return;

        // Deep clone and regenerate IDs
        const cloned = JTB.regenerateContentIds(content);
        const sections = cloned.content || [];

        sections.forEach(section => {
            JTB.state.content.content.push(section);
        });

        JTB.renderCanvas();
        JTB.markDirty();
        JTB.showNotification('Layout applied!', 'success');
    };

    /**
     * Close layout library modal
     */
    JTB.closeLayoutGallery = function() {
        const overlay = document.getElementById('jtb-layout-gallery-overlay');
        if (overlay) overlay.remove();
        JTB.layoutLibraryData = null;
    };

    /**
     * Apply layout to canvas
     */
    JTB.applyLayout = function(layoutContent) {
        if (!layoutContent) return;

        // Regenerate IDs
        const content = JTB.regenerateContentIds(layoutContent);

        // Get elements
        let elements = [];
        if (content.content && Array.isArray(content.content)) {
            elements = content.content;
        } else if (Array.isArray(content)) {
            elements = content;
        } else if (content.type) {
            elements = [content];
        }

        if (elements.length === 0) {
            JTB.showNotification('No content in layout', 'warning');
            return;
        }

        // Insert elements
        elements.forEach(element => {
            if (element.type === 'section') {
                JTB.state.content.content.push(element);
            } else if (element.type === 'row') {
                // Wrap row in section
                JTB.state.content.content.push({
                    type: 'section',
                    id: 'section_' + JTB.generateId(),
                    attrs: {},
                    children: [element]
                });
            }
        });

        JTB.renderCanvas();
        JTB.markDirty();
        JTB.showNotification('Layout applied!', 'success');
    };

    /**
     * Save current selection to library
     */
    JTB.saveToLibrary = function(type, indexes) {
        const element = JTB.getElement(type, indexes);
        if (!element) {
            JTB.showNotification('No element selected', 'error');
            return;
        }

        // Show save dialog
        const name = prompt('Enter a name for this template:', element.attrs?.admin_label || type + ' Template');
        if (!name) return;

        const data = {
            name: name,
            description: '',
            template_type: type === 'section' ? 'section' : 'page',
            content: {
                version: '1.0',
                content: [element]
            }
        };

        JTB.api.post('/library-save', data)
            .then(response => {
                if (response.success) {
                    JTB.showNotification('Saved to library!', 'success');
                } else {
                    throw new Error(response.error);
                }
            })
            .catch(error => {
                JTB.showNotification('Failed to save: ' + error.message, 'error');
            });
    };

    /**
     * Check for imported template from sessionStorage
     */
    JTB.checkImportedTemplate = function() {
        const imported = sessionStorage.getItem('jtb-import-template');
        if (imported) {
            sessionStorage.removeItem('jtb-import-template');
            try {
                const content = JSON.parse(imported);
                JTB.insertLibraryContent(content);
            } catch (e) {
                console.error('Failed to parse imported template:', e);
            }
        }
    };

    // Override createAddSectionButton to include Library button
    const originalCreateAddSectionButton = JTB.createAddSectionButton;
    JTB.createAddSectionButton = function() {
        const container = document.createElement('div');
        container.className = 'jtb-add-section-container';

        // Add Section button
        const addBtn = document.createElement('button');
        addBtn.className = 'jtb-add-section-btn';
        addBtn.innerHTML = '+ Add Section';
        addBtn.onclick = () => JTB.addSection();

        // Choose Layout button (page & section layouts)
        const layoutBtn = document.createElement('button');
        layoutBtn.className = 'jtb-from-library-btn';
        layoutBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <line x1="3" y1="9" x2="21" y2="9"/>
                <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            Choose Layout
        `;
        layoutBtn.onclick = () => JTB.openLayoutGallery();

        // From Library button
        const libraryBtn = document.createElement('button');
        libraryBtn.className = 'jtb-from-library-btn';
        libraryBtn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
            From Library
        `;
        libraryBtn.onclick = () => JTB.openLibrary();

        container.appendChild(addBtn);
        container.appendChild(layoutBtn);
        container.appendChild(libraryBtn);

        return container;
    };

    // Add "Save to Library" to context menu
    const originalInitContextMenu = JTB.initContextMenu;
    if (originalInitContextMenu) {
        JTB.initContextMenu = function() {
            originalInitContextMenu.call(JTB);

            // Extend context menu with library option
            const originalShowContextMenu = JTB.showContextMenu;
            JTB.showContextMenu = function(e, type, indexes) {
                originalShowContextMenu.call(JTB, e, type, indexes);

                // Add save to library option
                const menu = document.querySelector('.jtb-context-menu');
                if (menu && (type === 'section' || type === 'row')) {
                    const divider = document.createElement('div');
                    divider.className = 'jtb-context-menu-divider';

                    const saveItem = document.createElement('div');
                    saveItem.className = 'jtb-context-menu-item';
                    saveItem.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                        </svg>
                        Save to Library
                    `;
                    saveItem.onclick = () => {
                        JTB.saveToLibrary(type, indexes);
                        JTB.hideContextMenu();
                    };

                    menu.appendChild(divider);
                    menu.appendChild(saveItem);
                }
            };
        };
    }

    // Check for imported template on init
    const originalInitForLibrary = JTB.init;
    JTB.init = function(options) {
        originalInitForLibrary.call(JTB, options);
        // Check after a short delay to ensure canvas is ready
        setTimeout(() => {
            JTB.checkImportedTemplate();
            // Check for content import modal
            JTB.checkContentImport();
        }, 500);
    };

    // ========================================
    // Content Import System
    // ========================================

    /**
     * Check if we should show the import modal
     */
    JTB.checkContentImport = function() {
        // Only show if:
        // 1. No JTB content exists
        // 2. Original content exists
        if (!JTB.state.hasJtbContent && JTB.state.originalContent && JTB.state.originalContent.has_content) {
            JTB.showImportModal();
        }
    };

    /**
     * Show the content import modal
     */
    JTB.showImportModal = async function() {
        const originalContent = JTB.state.originalContent;
        const contentSize = JTB.formatFileSize(originalContent.content_length || 0);
        const postType = JTB.state.postType === 'article' ? 'article' : 'page';
        const isArticle = postType === 'article';

        // Fetch available layouts for articles
        let layoutsHtml = '';
        if (isArticle) {
            try {
                const response = await fetch(JTB.config.apiUrl + '/article-layouts', {
                    method: 'GET',
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.success && data.data.layouts) {
                    const layouts = data.data.layouts;
                    layoutsHtml = `
                        <div class="jtb-import-layouts" id="jtb-import-layouts">
                            <h4 class="jtb-import-layouts-title">Choose Layout</h4>
                            <div class="jtb-layout-grid">
                                ${Object.entries(layouts).map(([key, layout], index) => `
                                    <label class="jtb-layout-option ${index === 0 ? 'selected' : ''}">
                                        <input type="radio" name="import_layout" value="${key}" ${index === 0 ? 'checked' : ''}>
                                        <div class="jtb-layout-preview jtb-layout-preview-${key}">
                                            <div class="jtb-layout-preview-inner"></div>
                                        </div>
                                        <div class="jtb-layout-info">
                                            <span class="jtb-layout-name">${layout.name}</span>
                                            <span class="jtb-layout-desc">${layout.description}</span>
                                        </div>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
            } catch (e) {
                console.warn('Failed to load article layouts', e);
            }
        }

        const modalHtml = `
            <div class="jtb-import-modal-overlay" id="jtb-import-modal">
                <div class="jtb-import-modal ${isArticle ? 'jtb-import-modal-wide' : ''}">
                    <div class="jtb-import-modal-header">
                        <h3>Import Existing Content</h3>
                        <button type="button" class="jtb-import-modal-close" onclick="JTB.closeImportModal()">&times;</button>
                    </div>
                    <div class="jtb-import-modal-body">
                        <p class="jtb-import-modal-intro">
                            This ${postType} has existing content (${contentSize}).
                            How would you like to proceed?
                        </p>

                        <div class="jtb-import-options">
                            <label class="jtb-import-option">
                                <input type="radio" name="import_method" value="modules" checked>
                                <div class="jtb-import-option-content">
                                    <span class="jtb-import-option-title">Import as Modules</span>
                                    <span class="jtb-import-option-recommended">Recommended</span>
                                    <span class="jtb-import-option-desc">
                                        Auto-detect headings, text, images and convert to JTB modules.
                                        Some formatting may be adjusted.
                                    </span>
                                </div>
                            </label>

                            <label class="jtb-import-option">
                                <input type="radio" name="import_method" value="code">
                                <div class="jtb-import-option-content">
                                    <span class="jtb-import-option-title">Import as Code Block</span>
                                    <span class="jtb-import-option-desc">
                                        Keep exact HTML in a single Code module.
                                        Preserves 100% of original formatting.
                                    </span>
                                </div>
                            </label>

                            <label class="jtb-import-option">
                                <input type="radio" name="import_method" value="fresh">
                                <div class="jtb-import-option-content">
                                    <span class="jtb-import-option-title">Start Fresh</span>
                                    <span class="jtb-import-option-desc">
                                        Ignore existing content and start with empty canvas.
                                    </span>
                                </div>
                            </label>
                        </div>

                        ${layoutsHtml}
                    </div>
                    <div class="jtb-import-modal-footer">
                        <button type="button" class="jtb-btn jtb-btn-secondary" onclick="JTB.closeImportModal()">Cancel</button>
                        <button type="button" class="jtb-btn jtb-btn-primary" onclick="JTB.executeImport()" id="jtb-import-btn">
                            <span class="jtb-import-btn-text">Continue</span>
                            <span class="jtb-import-btn-loading" style="display: none;">Importing...</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add modal to document
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Bind escape key
        document.addEventListener('keydown', JTB.handleImportEscape);

        // Bind layout selection highlighting
        if (isArticle) {
            JTB.bindLayoutSelection();
        }

        // Bind method change to show/hide layouts
        JTB.bindMethodChange();
    };

    /**
     * Bind layout selection highlighting
     */
    JTB.bindLayoutSelection = function() {
        const layoutOptions = document.querySelectorAll('.jtb-layout-option input[type="radio"]');
        layoutOptions.forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.jtb-layout-option').forEach(opt => opt.classList.remove('selected'));
                this.closest('.jtb-layout-option').classList.add('selected');
            });
        });
    };

    /**
     * Bind import method change to show/hide layouts section
     */
    JTB.bindMethodChange = function() {
        const methodInputs = document.querySelectorAll('input[name="import_method"]');
        const layoutsSection = document.getElementById('jtb-import-layouts');

        if (!layoutsSection) return;

        methodInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === 'modules') {
                    layoutsSection.style.display = 'block';
                } else {
                    layoutsSection.style.display = 'none';
                }
            });
        });
    };

    /**
     * Close import modal
     */
    JTB.closeImportModal = function() {
        const modal = document.getElementById('jtb-import-modal');
        if (modal) {
            modal.remove();
        }
        document.removeEventListener('keydown', JTB.handleImportEscape);
    };

    /**
     * Handle escape key for import modal
     */
    JTB.handleImportEscape = function(e) {
        if (e.key === 'Escape') {
            JTB.closeImportModal();
        }
    };

    /**
     * Execute the import based on selected method
     */
    JTB.executeImport = async function() {
        const selected = document.querySelector('input[name="import_method"]:checked');
        if (!selected) return;

        const method = selected.value;
        const btn = document.getElementById('jtb-import-btn');
        const btnText = btn.querySelector('.jtb-import-btn-text');
        const btnLoading = btn.querySelector('.jtb-import-btn-loading');

        // Get selected layout for articles
        let layout = 'classic';
        const layoutInput = document.querySelector('input[name="import_layout"]:checked');
        if (layoutInput) {
            layout = layoutInput.value;
        }

        // Show loading state
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        btn.disabled = true;

        try {
            // Call parse-content API
            const response = await fetch(JTB.config.apiUrl + '/parse-content', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': JTB.config.csrfToken
                },
                body: JSON.stringify({
                    post_id: JTB.config.postId,
                    type: JTB.state.postType || 'page',
                    method: method,
                    layout: layout
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update content
                JTB.state.content = data.data.content;
                JTB.state.isDirty = true;

                // Close modal
                JTB.closeImportModal();

                // Re-render canvas
                JTB.renderCanvas();

                // Show notification
                if (method === 'fresh') {
                    JTB.showNotification('Started with empty canvas', 'success');
                } else if (method === 'code') {
                    JTB.showNotification('Content imported as code block', 'success');
                } else {
                    const count = data.data.modules_count || 0;
                    const layoutName = data.data.layout ? ` (${data.data.layout} layout)` : '';
                    JTB.showNotification(`Imported ${count} module${count !== 1 ? 's' : ''}${layoutName}`, 'success');

                    // Show warnings if any
                    if (data.data.warnings && data.data.warnings.length > 0) {
                        setTimeout(() => {
                            data.data.warnings.forEach(warning => {
                                JTB.showNotification(warning, 'warning');
                            });
                        }, 1500);
                    }
                }
            } else {
                throw new Error(data.error || 'Import failed');
            }
        } catch (error) {
            JTB.showNotification('Import failed: ' + error.message, 'error');

            // Reset button state
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            btn.disabled = false;
        }
    };

    /**
     * Format file size in human readable format
     */
    JTB.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    };

})();
