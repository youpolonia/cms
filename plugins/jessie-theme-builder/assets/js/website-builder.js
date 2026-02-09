/**
 * JTB Website Builder
 * Unified interface for building complete websites
 * Extends JTB (Page Builder) for full website management
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */
window.JTB_Website = {
    config: {
        csrfToken: null,
        apiUrl: '/api/jtb',
        website: null,
        headers: [],
        footers: [],
        pages: [],
        bodyTemplates: [],
        themeSettings: {},
        modules: []
    },

    state: {
        activeContext: null,  // {type: 'header'|'footer'|'body'|'page'|'settings', id: number|string}
        activeRegion: null,   // 'header'|'body'|'footer'
        isDirty: false,
        loadedContent: {},    // Cache: {header_33: {...}, page_1: {...}, ...}
        isLoading: false,
        aiGenerating: false
    },

    /**
     * Initialize Website Builder
     */
    init(config) {
        Object.assign(this.config, config);
        this.bindEvents();
        this.loadDefaultView();
        // console.log removed
    },

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Site Map item clicks
        document.querySelectorAll('.jtb-sitemap-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const type = item.dataset.type;
                const id = item.dataset.id;
                this.switchContext(type, type === 'settings' ? id : parseInt(id));
            });
        });

        // Add buttons
        document.querySelectorAll('.jtb-sitemap-add').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = btn.dataset.action;
                this.handleAddAction(action);
            });
        });

        // Region clicks (for quick switch when in preview mode)
        document.querySelectorAll('.jtb-canvas-region').forEach(region => {
            region.addEventListener('dblclick', (e) => {
                if (region.classList.contains('is-preview')) {
                    const regionType = region.dataset.region;
                    this.activateRegionFromCanvas(regionType);
                }
            });
        });

        // Header buttons
        document.querySelector('[data-action="save"]')?.addEventListener('click', () => this.saveAll());
        document.querySelector('[data-action="preview"]')?.addEventListener('click', () => this.openPreview());
        document.querySelector('[data-action="undo"]')?.addEventListener('click', () => this.undo());
        document.querySelector('[data-action="redo"]')?.addEventListener('click', () => this.redo());

        // Device switcher
        document.querySelectorAll('.jtb-device-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const device = btn.dataset.device;
                this.setPreviewDevice(device);
            });
        });

        // AI Generate Website button
        document.querySelector('[data-action="ai-generate-website"]')?.addEventListener('click', () => {
            this.showAIGenerateModal();
        });

        // AI Generate Website confirm
        document.querySelector('[data-action="generate-website"]')?.addEventListener('click', () => {
            this.generateWebsiteWithAI();
        });

        // Modal close buttons
        document.querySelectorAll('[data-action="close-modal"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.jtb-modal');
                if (modal) this.closeModal(modal);
            });
        });

        // Create modal confirm
        document.querySelector('[data-action="confirm-create"]')?.addEventListener('click', () => {
            this.confirmCreate();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's') {
                    e.preventDefault();
                    this.saveAll();
                } else if (e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                } else if ((e.key === 'z' && e.shiftKey) || e.key === 'y') {
                    e.preventDefault();
                    this.redo();
                }
            }
        });

        // Mark dirty on JTB content change
        if (typeof JTB !== 'undefined') {
            const originalMarkDirty = JTB.markDirty?.bind(JTB);
            if (originalMarkDirty) {
                JTB.markDirty = () => {
                    originalMarkDirty();
                    this.state.isDirty = true;
                };
            }
        }
    },

    /**
     * Load default view - show default header first
     */
    async loadDefaultView() {
        // Find default templates
        const defaultHeader = this.config.headers.find(h => h.is_default);
        const defaultFooter = this.config.footers.find(f => f.is_default);

        // Load previews for header and footer regions
        if (defaultHeader) {
            await this.loadRegionPreview('header', defaultHeader.id);
        }
        if (defaultFooter) {
            await this.loadRegionPreview('footer', defaultFooter.id);
        }

        // Auto-select default header for editing
        if (defaultHeader) {
            this.switchContext('header', defaultHeader.id);
        }
    },

    /**
     * Switch editing context
     */
    async switchContext(type, id) {
        // Save current if dirty
        if (this.state.isDirty && this.state.activeContext) {
            const shouldSave = confirm('You have unsaved changes. Save before switching?');
            if (shouldSave) {
                await this.saveCurrent();
            }
        }

        // Update state
        this.state.activeContext = { type, id };
        this.state.isDirty = false;

        // Update sitemap UI - highlight active item
        document.querySelectorAll('.jtb-sitemap-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.type === type && item.dataset.id == id) {
                item.classList.add('active');
            }
        });

        // Determine which canvas region to activate
        let region = type;
        if (type === 'page' || type === 'body') region = 'body';
        if (type === 'settings') {
            this.showSettingsPanel(id);
            return;
        }

        this.activateRegion(region);

        // Load content into JTB builder
        await this.loadContentForContext(type, id);
    },

    /**
     * Activate a canvas region
     */
    activateRegion(regionType) {
        this.state.activeRegion = regionType;

        document.querySelectorAll('.jtb-canvas-region').forEach(r => {
            r.classList.remove('is-active', 'is-preview');
            if (r.dataset.region === regionType) {
                r.classList.add('is-active');
            } else {
                r.classList.add('is-preview');
            }
        });
    },

    /**
     * Activate region from canvas double-click
     */
    activateRegionFromCanvas(regionType) {
        // Find the appropriate item in sitemap
        let item = null;
        if (regionType === 'header') {
            item = this.config.headers.find(h => h.is_default) || this.config.headers[0];
            if (item) this.switchContext('header', item.id);
        } else if (regionType === 'footer') {
            item = this.config.footers.find(f => f.is_default) || this.config.footers[0];
            if (item) this.switchContext('footer', item.id);
        } else if (regionType === 'body') {
            item = this.config.bodyTemplates.find(t => t.is_default) || this.config.pages[0];
            if (item) {
                const type = this.config.bodyTemplates.includes(item) ? 'body' : 'page';
                this.switchContext(type, item.id);
            }
        }
    },

    /**
     * Load content for a specific context
     */
    async loadContentForContext(type, id) {
        const cacheKey = `${type}_${id}`;

        // Check cache
        if (this.state.loadedContent[cacheKey]) {
            this.applyContentToBuilder(this.state.loadedContent[cacheKey], type);
            return;
        }

        this.showLoading(true);

        try {
            let endpoint;
            if (type === 'header' || type === 'footer' || type === 'body') {
                endpoint = `${this.config.apiUrl}/template-get/${id}`;
            } else if (type === 'page') {
                endpoint = `${this.config.apiUrl}/load/${id}`;
            }

            const response = await fetch(endpoint);
            const data = await response.json();

            if (data.success) {
                const content = type === 'page'
                    ? (data.data?.content || { version: '1.0', content: [] })
                    : (data.template?.content || { version: '1.0', content: [] });

                this.state.loadedContent[cacheKey] = content;
                this.applyContentToBuilder(content, type);
            } else {
                this.showNotification('Failed to load content', 'error');
            }
        } catch (err) {
            console.error('[JTB_Website] Failed to load content:', err);
            this.showNotification('Failed to load content', 'error');
        } finally {
            this.showLoading(false);
        }
    },

    /**
     * Apply content to JTB Page Builder
     */
    applyContentToBuilder(content, contextType) {
        if (typeof JTB === 'undefined') {
            console.error('[JTB_Website] JTB not available');
            return;
        }

        // Ensure content has proper structure
        if (!content || !content.content) {
            content = { version: '1.0', content: [] };
        }

        // Set JTB state
        JTB.state.content = content;
        JTB.config.templateType = contextType;

        // Render to active region
        const regionContent = document.querySelector(`#jtb-${this.state.activeRegion}-content`);
        if (regionContent) {
            JTB.renderCanvas(regionContent);
        }
    },

    /**
     * Load preview for a region (non-editable)
     */
    async loadRegionPreview(regionType, templateId) {
        try {
            const response = await fetch(`${this.config.apiUrl}/template-preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({ template_id: templateId })
            });

            const data = await response.json();
            if (data.success && data.html) {
                const regionContent = document.querySelector(`#jtb-${regionType}-content`);
                if (regionContent) {
                    regionContent.innerHTML = data.html;
                    // Add CSS if provided
                    if (data.css) {
                        const styleId = `jtb-preview-${regionType}-css`;
                        let styleEl = document.getElementById(styleId);
                        if (!styleEl) {
                            styleEl = document.createElement('style');
                            styleEl.id = styleId;
                            document.head.appendChild(styleEl);
                        }
                        styleEl.textContent = data.css;
                    }
                }
            }
        } catch (err) {
            console.error(`[JTB_Website] Failed to load ${regionType} preview:`, err);
        }
    },

    /**
     * Save current context
     */
    async saveCurrent() {
        const ctx = this.state.activeContext;
        if (!ctx || !JTB?.state?.content) return false;

        const content = JTB.state.content;

        try {
            let endpoint, body;

            if (ctx.type === 'header' || ctx.type === 'footer' || ctx.type === 'body') {
                endpoint = `${this.config.apiUrl}/template-save`;
                body = { id: ctx.id, content: content };
            } else if (ctx.type === 'page') {
                endpoint = `${this.config.apiUrl}/save`;
                body = { post_id: ctx.id, content: JSON.stringify(content) };
            } else {
                return false;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (data.success) {
                this.state.isDirty = false;
                // Update cache
                const cacheKey = `${ctx.type}_${ctx.id}`;
                this.state.loadedContent[cacheKey] = content;
                // Update preview if not active region
                if (ctx.type !== 'page') {
                    this.loadRegionPreview(ctx.type, ctx.id);
                }
                return true;
            } else {
                this.showNotification(data.error || 'Save failed', 'error');
                return false;
            }
        } catch (err) {
            console.error('[JTB_Website] Save failed:', err);
            this.showNotification('Save failed', 'error');
            return false;
        }
    },

    /**
     * Save all changes
     */
    async saveAll() {
        this.showLoading(true);
        const success = await this.saveCurrent();
        this.showLoading(false);

        if (success) {
            this.showNotification('Changes saved!', 'success');
        }
    },

    /**
     * Handle add action from sitemap
     */
    handleAddAction(action) {
        const type = action.replace('add-', '');
        this.state.createType = type;

        // Set modal title
        const titles = {
            'header': 'Create New Header',
            'footer': 'Create New Footer',
            'body': 'Create New Body Template',
            'page': 'Create New Page'
        };

        document.getElementById('jtb-create-modal-title').textContent = titles[type] || 'Create New';
        document.getElementById('jtb-create-name').value = '';

        this.openModal(document.getElementById('jtb-create-modal'));
    },

    /**
     * Confirm create action
     */
    async confirmCreate() {
        const name = document.getElementById('jtb-create-name').value.trim();
        if (!name) {
            this.showNotification('Please enter a name', 'warning');
            return;
        }

        const type = this.state.createType;
        this.showLoading(true);

        try {
            let endpoint, body;

            if (type === 'page') {
                // Create CMS page
                endpoint = `${this.config.apiUrl}/create-post`;
                body = { title: name, type: 'page' };
            } else {
                // Create template
                endpoint = `${this.config.apiUrl}/template-save`;
                body = {
                    name: name,
                    type: type,
                    content: { version: '1.0', content: [] }
                };
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (data.success) {
                // Add to config
                const newItem = data.template || data.page;

                if (type === 'header') this.config.headers.push(newItem);
                else if (type === 'footer') this.config.footers.push(newItem);
                else if (type === 'body') this.config.bodyTemplates.push(newItem);
                else if (type === 'page') this.config.pages.push(newItem);

                // Update sitemap
                this.updateSitemap(type);

                // Close modal and switch to new item
                this.closeModal(document.getElementById('jtb-create-modal'));
                this.switchContext(type, newItem.id);

                this.showNotification(`${name} created!`, 'success');
            } else {
                this.showNotification(data.error || 'Creation failed', 'error');
            }
        } catch (err) {
            console.error('[JTB_Website] Create failed:', err);
            this.showNotification('Creation failed', 'error');
        } finally {
            this.showLoading(false);
        }
    },

    /**
     * Update sitemap after adding new item
     */
    updateSitemap(type) {
        const sectionMap = {
            'header': 'header',
            'footer': 'footer',
            'body': 'templates',
            'page': 'pages'
        };

        const section = document.querySelector(`[data-section="${sectionMap[type]}"] .jtb-sitemap-list`);
        if (!section) return;

        const items = type === 'page' ? this.config.pages :
                      type === 'header' ? this.config.headers :
                      type === 'footer' ? this.config.footers :
                      this.config.bodyTemplates;

        // Rebuild list (keep add button at end)
        const addBtn = section.querySelector('.jtb-sitemap-add');
        const fragment = document.createDocumentFragment();

        items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'jtb-sitemap-item' + (item.is_default ? ' is-default' : '');
            li.dataset.type = type;
            li.dataset.id = item.id;

            li.innerHTML = `
                <span class="jtb-item-name">${this.escapeHtml(item.name || item.title)}</span>
                ${item.is_default ? '<span class="jtb-badge jtb-badge-success">Default</span>' : ''}
                ${item.slug ? `<span class="jtb-item-slug">/${this.escapeHtml(item.slug)}</span>` : ''}
            `;

            li.addEventListener('click', () => this.switchContext(type, item.id));
            fragment.appendChild(li);
        });

        // Clear and rebuild
        section.querySelectorAll('.jtb-sitemap-item').forEach(el => el.remove());
        section.insertBefore(fragment, addBtn);
    },

    /**
     * Show settings panel for theme settings
     */
    showSettingsPanel(settingGroup) {
        // Redirect to theme settings page with specific group
        window.location.href = `/admin/jtb/theme-settings#${settingGroup}`;
    },

    /**
     * Set preview device
     */
    setPreviewDevice(device) {
        // Update buttons
        document.querySelectorAll('.jtb-device-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.device === device);
        });

        // Update canvas class
        const canvas = document.getElementById('jtb-canvas');
        if (canvas) {
            canvas.classList.remove('jtb-preview-desktop', 'jtb-preview-tablet', 'jtb-preview-phone');
            canvas.classList.add(`jtb-preview-${device}`);
        }

        // Update JTB if available
        if (typeof JTB !== 'undefined' && JTB.setPreviewDevice) {
            JTB.setPreviewDevice(device);
        }
    },

    /**
     * Open full preview
     */
    openPreview() {
        const defaultHeader = this.config.headers.find(h => h.is_default);
        const defaultFooter = this.config.footers.find(f => f.is_default);

        let url = '/preview/website?';
        if (defaultHeader) url += `header=${defaultHeader.id}&`;
        if (defaultFooter) url += `footer=${defaultFooter.id}&`;

        // Add current page/body if editing one
        if (this.state.activeContext) {
            const ctx = this.state.activeContext;
            if (ctx.type === 'page') url += `page=${ctx.id}`;
            else if (ctx.type === 'body') url += `body=${ctx.id}`;
        }

        window.open(url, '_blank');
    },

    /**
     * Undo
     */
    undo() {
        if (typeof JTB !== 'undefined' && JTB.undo) {
            JTB.undo();
        }
    },

    /**
     * Redo
     */
    redo() {
        if (typeof JTB !== 'undefined' && JTB.redo) {
            JTB.redo();
        }
    },

    // =========================================================================
    // AI WEBSITE GENERATION
    // =========================================================================

    /**
     * Show AI Generate Website modal
     */
    showAIGenerateModal() {
        this.openModal(document.getElementById('jtb-ai-website-modal'));
    },

    /**
     * Generate entire website with AI
     * AI receives module schemas and generates everything - no hardcoded patterns
     */
    async generateWebsiteWithAI() {
        const prompt = document.getElementById('jtb-ai-website-prompt').value.trim();
        if (!prompt) {
            this.showNotification('Please describe your website', 'warning');
            return;
        }

        const industry = document.getElementById('jtb-ai-website-industry').value;
        const style = document.getElementById('jtb-ai-website-style').value;

        // Get selected pages
        const pageCheckboxes = document.querySelectorAll('input[name="pages"]:checked');
        const pages = Array.from(pageCheckboxes).map(cb => cb.value);

        if (pages.length === 0) {
            this.showNotification('Please select at least one page', 'warning');
            return;
        }

        this.state.aiGenerating = true;
        this.closeModal(document.getElementById('jtb-ai-website-modal'));
        this.showLoading(true, 'Generating your website with AI...');

        try {
            // Call unified AI endpoint - AI decides everything based on prompt
            const response = await fetch(`${this.config.apiUrl}/ai/generate-website`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    prompt: prompt,
                    industry: industry || null,
                    style: style,
                    pages: pages
                })
            });

            const data = await response.json();

            if (data.success && data.website) {
                await this.applyGeneratedWebsite(data.website);
                this.showNotification('Website generated successfully!', 'success');
            } else {
                this.showNotification(data.error || 'AI generation failed', 'error');
            }
        } catch (err) {
            console.error('[JTB_Website] AI generation failed:', err);
            this.showNotification('AI generation failed', 'error');
        } finally {
            this.state.aiGenerating = false;
            this.showLoading(false);
        }
    },

    /**
     * Apply AI-generated website content
     */
    async applyGeneratedWebsite(website) {
        // Save generated header
        if (website.header?.content) {
            const headerName = 'AI Generated Header';
            await this.saveGeneratedTemplate('header', headerName, website.header.content, true);
        }

        // Save generated footer
        if (website.footer?.content) {
            const footerName = 'AI Generated Footer';
            await this.saveGeneratedTemplate('footer', footerName, website.footer.content, true);
        }

        // Save generated pages
        if (website.pages) {
            for (const [slug, pageData] of Object.entries(website.pages)) {
                await this.saveGeneratedPage(pageData.title || slug, slug, pageData.content);
            }
        }

        // Apply theme settings if provided
        if (website.themeSettings) {
            await this.applyThemeSettings(website.themeSettings);
        }

        // Reload page to show all changes
        window.location.reload();
    },

    /**
     * Save a generated template
     */
    async saveGeneratedTemplate(type, name, content, setDefault = false) {
        try {
            const response = await fetch(`${this.config.apiUrl}/template-save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    name: name,
                    type: type,
                    content: { version: '1.0', content: content },
                    is_default: setDefault ? 1 : 0
                })
            });

            const data = await response.json();
            return data.success ? data.template : null;
        } catch (err) {
            console.error(`[JTB_Website] Failed to save ${type}:`, err);
            return null;
        }
    },

    /**
     * Save a generated page
     */
    async saveGeneratedPage(title, slug, content) {
        try {
            // First create the page
            const createResponse = await fetch(`${this.config.apiUrl}/create-post`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    title: title,
                    slug: slug,
                    type: 'page'
                })
            });

            const createData = await createResponse.json();

            if (createData.success && createData.page?.id) {
                // Then save JTB content
                await fetch(`${this.config.apiUrl}/save`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.config.csrfToken
                    },
                    body: JSON.stringify({
                        post_id: createData.page.id,
                        content: JSON.stringify({ version: '1.0', content: content })
                    })
                });

                return createData.page;
            }
        } catch (err) {
            console.error(`[JTB_Website] Failed to save page ${title}:`, err);
        }
        return null;
    },

    /**
     * Apply theme settings
     */
    async applyThemeSettings(settings) {
        try {
            await fetch(`${this.config.apiUrl}/theme-settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify(settings)
            });
        } catch (err) {
            console.error('[JTB_Website] Failed to apply theme settings:', err);
        }
    },

    // =========================================================================
    // UI HELPERS
    // =========================================================================

    /**
     * Show/hide loading overlay
     */
    showLoading(show, message = 'Loading...') {
        const overlay = document.getElementById('jtb-loading');
        if (overlay) {
            overlay.style.display = show ? 'flex' : 'none';
            const text = overlay.querySelector('.jtb-loading-text');
            if (text) text.textContent = message;
        }
        this.state.isLoading = show;
    },

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const container = document.getElementById('jtb-notifications');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `jtb-notification jtb-notification-${type}`;
        notification.innerHTML = `
            <span>${this.escapeHtml(message)}</span>
            <button class="jtb-notification-close">&times;</button>
        `;

        notification.querySelector('.jtb-notification-close').addEventListener('click', () => {
            notification.remove();
        });

        container.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('jtb-notification-fade');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    },

    /**
     * Open modal
     */
    openModal(modal) {
        if (modal) {
            modal.classList.add('is-open');
            document.body.classList.add('jtb-modal-open');
        }
    },

    /**
     * Close modal
     */
    closeModal(modal) {
        if (modal) {
            modal.classList.remove('is-open');
            document.body.classList.remove('jtb-modal-open');
        }
    },

    /**
     * Escape HTML
     */
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};
