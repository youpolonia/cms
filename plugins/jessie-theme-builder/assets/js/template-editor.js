/**
 * JTB Template Editor
 * Extended builder functionality for templates
 */

const JTBTemplateEditor = {
    templateId: null,
    templateType: null,
    templateName: null,
    isNew: true,
    conditionsPanelOpen: false,

    /**
     * Initialize template editor
     */
    init(data) {
        this.templateId = data.id;
        this.templateType = data.type;
        this.templateName = data.name;
        this.isNew = data.isNew;

        // Initialize the main builder with template content
        if (typeof JTB !== 'undefined' && typeof JTB.init === 'function') {
            JTB.init({
                postId: null, // Templates don't use post_id
                content: data.content,
                modules: window.JTB_MODULES || {}
            });
        }

        // Initialize conditions builder
        if (typeof JTBConditionsBuilder !== 'undefined') {
            JTBConditionsBuilder.init(data.conditions || [], window.JTB_PAGE_TYPES || {});
        }

        this.bindEvents();
        this.renderModulesList();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Template name input
        const nameInput = document.getElementById('templateName');
        if (nameInput) {
            nameInput.addEventListener('change', () => {
                this.templateName = nameInput.value.trim();
            });
        }

        // Device switcher
        document.querySelectorAll('.jtb-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.jtb-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const device = btn.dataset.device;
                this.setPreviewDevice(device);
            });
        });

        // Category tabs for modules
        document.querySelectorAll('#categoryTabs .jtb-category-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('#categoryTabs .jtb-category-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                this.filterModules(tab.dataset.category);
            });
        });

        // Module search
        const search = document.getElementById('moduleSearch');
        if (search) {
            search.addEventListener('input', () => {
                this.searchModules(search.value);
            });
        }
    },

    /**
     * Render modules list in sidebar
     */
    renderModulesList() {
        const container = document.getElementById('modulesList');
        if (!container) return;

        const modules = window.JTB_MODULES || {};

        container.innerHTML = '';

        Object.keys(modules).forEach(slug => {
            const module = modules[slug];

            const item = document.createElement('div');
            item.className = 'jtb-module-list-item';
            item.dataset.slug = slug;
            item.dataset.category = module.category || 'content';
            item.draggable = true;

            // Get icon - use JTB.getModuleIcon if available, otherwise map common icons
            const icon = this.getModuleIcon(slug, module.icon);

            item.innerHTML = `
                <span class="jtb-module-list-icon">${icon}</span>
                <span class="jtb-module-list-name">${this.escapeHtml(module.name)}</span>
            `;

            // Drag events
            item.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('module-type', slug);
                e.dataTransfer.effectAllowed = 'copy';
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('dragging');
            });

            // Click to add - open module picker or add directly to last section
            item.addEventListener('click', () => {
                this.addModuleFromSidebar(slug);
            });

            container.appendChild(item);
        });
    },

    /**
     * Get module icon (emoji) from slug or icon name
     */
    getModuleIcon(slug, iconName) {
        // First check if JTB has getModuleIcon
        if (typeof JTB !== 'undefined' && typeof JTB.getModuleIcon === 'function') {
            return JTB.getModuleIcon(slug);
        }

        // Fallback icon mapping
        const iconMap = {
            // Structure
            'section': '📦', 'row': '▤', 'column': '▥',
            // Theme
            'menu': '☰', 'site_logo': '🏷️', 'search_form': '🔍', 'search': '🔍',
            'post_title': '📰', 'post_content': '📄', 'social_icons': '📱',
            // Content
            'text': '📝', 'heading': '🔤', 'image': '🖼️', 'button': '🔘',
            'blurb': '💬', 'divider': '➖', 'cta': '📢', 'code': '💻',
            'testimonial': '💭', 'team_member': '👤', 'pricing_table': '💰',
            'social_follow': '📱', 'accordion': '📋', 'tabs': '📑',
            'toggle': '🔀', 'video': '🎬', 'audio': '🔊', 'gallery': '🖼️',
            'slider': '🎠', 'map': '🗺️', 'contact_form': '✉️', 'login': '🔐',
            'signup': '📝', 'blog': '📰', 'portfolio': '💼',
            'number_counter': '🔢', 'circle_counter': '⭕', 'bar_counter': '📊',
            'progress-bar': '📊', 'countdown': '⏱️', 'sidebar': '📌',
            'comments': '💬', 'shop': '🛒', 'post_navigation': '↔️',
            // Fullwidth
            'fullwidth_header': '🎯', 'fullwidth_image': '🖼️',
            'fullwidth_slider': '🎠', 'fullwidth_menu': '☰'
        };

        return iconMap[slug] || iconMap[iconName] || '📦';
    },

    /**
     * Add module from sidebar click
     */
    addModuleFromSidebar(moduleType) {
        if (typeof JTB === 'undefined') return;

        // Check if there's any content
        const content = JTB.state.content;
        if (!content || !content.content || content.content.length === 0) {
            // No sections - create one first, then add row and module
            JTB.addSection();
            // After adding section, add the module to first column
            setTimeout(() => {
                if (JTB.state.content.content.length > 0) {
                    const section = JTB.state.content.content[0];
                    if (section.children && section.children.length > 0) {
                        const row = section.children[0];
                        if (row.children && row.children.length > 0) {
                            JTB.addModule([0, 0, 0], moduleType);
                        }
                    }
                }
            }, 100);
        } else {
            // Find last section, last row, last column
            const lastSectionIdx = content.content.length - 1;
            const lastSection = content.content[lastSectionIdx];

            if (lastSection.children && lastSection.children.length > 0) {
                const lastRowIdx = lastSection.children.length - 1;
                const lastRow = lastSection.children[lastRowIdx];

                if (lastRow.children && lastRow.children.length > 0) {
                    const lastColIdx = lastRow.children.length - 1;
                    JTB.addModule([lastSectionIdx, lastRowIdx, lastColIdx], moduleType);
                }
            }
        }
    },

    /**
     * Filter modules by category
     */
    filterModules(category) {
        document.querySelectorAll('.jtb-module-list-item').forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    },

    /**
     * Search modules
     */
    searchModules(query) {
        const q = query.toLowerCase();

        document.querySelectorAll('.jtb-module-list-item').forEach(item => {
            const name = item.querySelector('.jtb-module-list-name').textContent.toLowerCase();
            const slug = item.dataset.slug.toLowerCase();

            if (name.includes(q) || slug.includes(q)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    },

    /**
     * Toggle conditions panel
     */
    toggleConditions() {
        const panel = document.getElementById('conditionsPanel');
        const moduleSettings = document.getElementById('moduleSettings');

        this.conditionsPanelOpen = !this.conditionsPanelOpen;

        if (this.conditionsPanelOpen) {
            panel.style.display = 'flex';
            moduleSettings.style.display = 'none';
        } else {
            panel.style.display = 'none';
            moduleSettings.style.display = 'block';
        }
    },

    /**
     * Set preview device
     */
    setPreviewDevice(device) {
        const canvas = document.getElementById('canvas');

        canvas.classList.remove('jtb-preview-desktop', 'jtb-preview-tablet', 'jtb-preview-phone');
        canvas.classList.add('jtb-preview-' + device);
    },

    /**
     * Save template
     */
    async save() {
        const name = document.getElementById('templateName').value.trim();

        if (!name) {
            this.showNotification('Please enter a template name', 'error');
            return;
        }

        // Get content from builder
        let content = { version: '1.0', content: [] };
        if (typeof JTB !== 'undefined' && typeof JTB.getContent === 'function') {
            content = JTB.getContent();
        }

        // Get conditions
        let conditions = [];
        if (typeof JTBConditionsBuilder !== 'undefined') {
            conditions = JTBConditionsBuilder.getConditionsData();
        }

        // Get default status
        const isDefault = document.getElementById('isDefault').checked;

        const data = {
            name: name,
            type: this.templateType,
            content: content,
            conditions: conditions,
            is_default: isDefault
        };

        if (this.templateId) {
            data.id = this.templateId;
        }

        try {
            const response = await fetch('/api/jtb/template-save', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Template saved', 'success');

                // Update template ID if new
                if (!this.templateId && result.template_id) {
                    this.templateId = result.template_id;
                    this.isNew = false;

                    // Update URL without reload
                    window.history.replaceState({}, '', '/admin/jtb/template/edit/' + result.template_id);
                }
            } else {
                this.showNotification(result.error || 'Failed to save template', 'error');
            }
        } catch (error) {
            this.showNotification('Error saving template', 'error');
            console.error(error);
        }
    },

    /**
     * Preview template
     */
    async preview() {
        // Get content from builder
        let content = { version: '1.0', content: [] };
        if (typeof JTB !== 'undefined' && typeof JTB.getContent === 'function') {
            content = JTB.getContent();
        }

        if (!content.content || content.content.length === 0) {
            this.showNotification('Nothing to preview. Add some content first.', 'warning');
            return;
        }

        try {
            // Fetch rendered HTML/CSS from API
            const response = await fetch('/api/jtb/template-preview', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({ content: content })
            });

            const result = await response.json();

            if (result.success) {
                this.showPreviewModal(result.html, result.css);
            } else {
                this.showNotification(result.error || 'Preview failed', 'error');
            }
        } catch (error) {
            this.showNotification('Error generating preview', 'error');
            console.error(error);
        }
    },

    /**
     * Show preview modal
     */
    showPreviewModal(html, css) {
        // Remove existing modal if any
        let modal = document.getElementById('previewModal');
        if (modal) {
            modal.remove();
        }

        // Create modal
        const modalHtml = `
            <div class="jtb-modal-overlay" id="previewModal">
                <div class="jtb-modal jtb-modal-preview">
                    <div class="jtb-modal-header">
                        <h3 class="jtb-modal-title">Template Preview</h3>
                        <div class="jtb-preview-devices">
                            <button class="jtb-preview-device-btn active" data-width="100%">Desktop</button>
                            <button class="jtb-preview-device-btn" data-width="768px">Tablet</button>
                            <button class="jtb-preview-device-btn" data-width="375px">Phone</button>
                        </div>
                        <button class="jtb-modal-close" onclick="JTBTemplateEditor.closePreviewModal()">&times;</button>
                    </div>
                    <div class="jtb-modal-body">
                        <div class="jtb-preview-frame-wrapper">
                            <iframe id="previewIframe" class="jtb-preview-iframe"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        modal = document.getElementById('previewModal');
        const iframe = document.getElementById('previewIframe');

        // Build iframe content
        const iframeContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    * { box-sizing: border-box; }
                    body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fff; }
                    img { max-width: 100%; height: auto; }
                    ${css}
                </style>
            </head>
            <body>
                ${html}
            </body>
            </html>
        `;

        iframe.srcdoc = iframeContent;

        // Device buttons
        modal.querySelectorAll('.jtb-preview-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.querySelectorAll('.jtb-preview-device-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                iframe.style.width = btn.dataset.width;
            });
        });

        setTimeout(() => modal.classList.add('show'), 10);
    },

    /**
     * Close preview modal
     */
    closePreviewModal() {
        const modal = document.getElementById('previewModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 300);
        }
    },

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const container = document.getElementById('notifications');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `jtb-notification ${type}`;
        notification.textContent = message;

        container.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 10);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    /**
     * Escape HTML
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};
