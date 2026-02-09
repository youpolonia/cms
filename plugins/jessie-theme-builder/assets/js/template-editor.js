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

        // Filter modules based on template type
        const filteredModules = this.filterModulesByTemplateType(window.JTB_MODULES || {}, data.type);

        // Initialize the main builder with template content
        if (typeof JTB !== 'undefined' && typeof JTB.init === 'function') {
            JTB.init({
                postId: null, // Templates don't use post_id
                content: data.content,
                modules: filteredModules,
                templateType: data.type // Pass template type to builder
            });
        }

        // Initialize conditions builder
        if (typeof JTBConditionsBuilder !== 'undefined') {
            JTBConditionsBuilder.init(data.conditions || [], window.JTB_PAGE_TYPES || {});
        }

        this.bindEvents();
    },

    /**
     * Get allowed categories for each template type
     */
    getAllowedCategories(templateType) {
        const categoryMap = {
            'header': ['header', 'content', 'media', 'interactive'],
            'footer': ['footer', 'content', 'media', 'interactive'],
            'body': ['dynamic', 'content', 'media', 'interactive', 'forms', 'blog'],
            'single': ['dynamic', 'content', 'media', 'interactive', 'forms', 'blog'],
            'archive': ['dynamic', 'content', 'media', 'interactive', 'blog'],
            '404': ['content', 'media', 'interactive'],
            'search': ['dynamic', 'content', 'media', 'interactive']
        };

        // Default categories if template type not found
        return categoryMap[templateType] || ['content', 'media', 'interactive', 'forms', 'blog', 'header', 'footer', 'dynamic'];
    },

    /**
     * Filter modules based on template type
     */
    filterModulesByTemplateType(modules, templateType) {
        const allowedCategories = this.getAllowedCategories(templateType);
        const filteredModules = {};

        // Always include structure modules
        const structureModules = ['section', 'row', 'column'];

        for (const slug in modules) {
            if (modules.hasOwnProperty(slug)) {
                const module = modules[slug];
                const category = module.category || 'content';

                // Include structure modules always
                if (structureModules.includes(slug)) {
                    filteredModules[slug] = module;
                    continue;
                }

                // Include if category is allowed for this template type
                if (allowedCategories.includes(category)) {
                    filteredModules[slug] = module;
                }
            }
        }

        return filteredModules;
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

        // Get default status (with null check)
        const isDefaultEl = document.getElementById('isDefault');
        const isDefault = isDefaultEl ? isDefaultEl.checked : false;

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

        // Build iframe content with base module styles
        const pluginUrl = JTB?.pluginUrl || '/plugins/jessie-theme-builder';
        const iframeContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
                <link rel="stylesheet" href="${pluginUrl}/assets/css/frontend.css">
                <link rel="stylesheet" href="${pluginUrl}/assets/css/jtb-base-modules.css">
                <link rel="stylesheet" href="${pluginUrl}/assets/css/animations.css">
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
    },

    /**
     * Apply AI-generated layout to canvas
     * Called by JTB_AI_Template.applyToCanvas()
     */
    applyLayout(layout) {
        if (!layout || !layout.sections) {
            console.error('Invalid layout data');
            return false;
        }

        // Use JTB.Builder.setContent if available
        if (typeof JTB !== 'undefined' && JTB.setContent) {
            JTB.setContent({ version: '1.0', content: layout.sections });
            return true;
        }

        // Fallback: use JTB.init with new content
        if (typeof JTB !== 'undefined' && JTB.init) {
            JTB.init({
                postId: null,
                content: { version: '1.0', content: layout.sections },
                modules: window.JTB_MODULES || {},
                templateType: this.templateType
            });
            return true;
        }

        console.error('JTB builder not available');
        return false;
    }
};

// ==========================================================================
// AI PANEL INTEGRATION FOR TEMPLATE EDITOR
// Updated: 2026-02-04 - Now uses full ai-panel.js (same as Page Builder)
// ==========================================================================

// JTB_AI_Template is now a thin wrapper that delegates to JTB_AI
// This maintains backward compatibility with any code that references JTB_AI_Template
const JTB_AI_Template = {
    /**
     * Toggle panel - delegates to JTB_AI
     */
    togglePanel() {
        if (typeof JTB_AI !== 'undefined' && JTB_AI.toggle) {
            JTB_AI.toggle();
        }
    },

    /**
     * Close panel - delegates to JTB_AI
     */
    closePanel() {
        if (typeof JTB_AI !== 'undefined' && JTB_AI.close) {
            JTB_AI.close();
        }
    },

    /**
     * Generate - delegates to JTB_AI
     * Note: JTB_AI now handles template vs page mode automatically
     */
    generate() {
        // JTB_AI handles generation through its event system
        const generateBtn = document.getElementById('jtb-ai-generate-btn');
        if (generateBtn) {
            generateBtn.click();
        }
    },

    /**
     * Apply to canvas - delegates to JTB_AI insert function
     */
    applyToCanvas() {
        const insertBtn = document.getElementById('jtb-ai-preview-insert');
        if (insertBtn) {
            insertBtn.click();
        }
    }
};
