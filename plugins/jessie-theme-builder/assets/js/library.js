/**
 * JTB Template Library JavaScript
 * Handles browsing, filtering, and using templates
 */

(function() {
    'use strict';

    const Library = {
        templates: [],
        categories: [],
        currentFilters: {
            category: '',
            types: ['page', 'section'],
            sources: ['premade', 'user'],
            search: ''
        },
        selectedTemplate: null,
        csrfToken: window.JTB_CSRF_TOKEN || '',
        embedMode: window.JTB_EMBED_MODE || false,
        templateType: window.JTB_TEMPLATE_TYPE || '',
        isThemeBuilderMode: window.JTB_IS_THEME_BUILDER_MODE || false,

        init() {
            // For Theme Builder mode, load theme builder layouts
            if (this.isThemeBuilderMode) {
                this.loadThemeBuilderLayouts();
            } else {
                this.loadCategories();
                this.loadTemplates().then(() => {
                    // Auto-seed if no templates exist
                    if (this.templates.length === 0) {
                        this.seedTemplates();
                    }
                });
            }
            this.bindEvents();
        },

        /**
         * Load Theme Builder specific layouts (headers, footers)
         */
        async loadThemeBuilderLayouts() {
            this.showLoading(true);

            try {
                const url = `/api/jtb/library-theme-builder?type=${encodeURIComponent(this.templateType)}`;
                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    this.templates = data.layouts || [];
                    this.renderTemplates();
                    this.updateCounts(this.templates.length);
                } else {
                    throw new Error(data.error || 'Failed to load layouts');
                }
            } catch (error) {
                console.error('Failed to load theme builder layouts:', error);
                this.showError('Failed to load layouts');
            } finally {
                this.showLoading(false);
            }
        },

        async seedTemplates() {
            try {
                const response = await fetch('/api/jtb/library-seed', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });
                const data = await response.json();

                if (data.success && data.seeded > 0) {
                    this.showNotification(`Added ${data.seeded} starter templates!`, 'success');
                    this.loadCategories();
                    this.loadTemplates();
                }
            } catch (error) {
                console.error('Failed to seed templates:', error);
            }
        },

        // ========================================
        // Data Loading
        // ========================================

        async loadCategories() {
            try {
                const response = await fetch('/api/jtb/library-categories');
                const data = await response.json();

                if (data.success) {
                    this.categories = data.categories;
                    this.renderCategories();
                }
            } catch (error) {
                console.error('Failed to load categories:', error);
            }
        },

        async loadTemplates() {
            this.showLoading(true);

            try {
                const params = new URLSearchParams();

                if (this.currentFilters.category) {
                    params.append('category', this.currentFilters.category);
                }

                if (this.currentFilters.search) {
                    params.append('search', this.currentFilters.search);
                }

                // Build type filter
                const types = this.currentFilters.types;
                if (types.length === 1) {
                    params.append('type', types[0]);
                }

                // Build source filter
                const sources = this.currentFilters.sources;
                if (sources.length === 1) {
                    if (sources[0] === 'premade') {
                        params.append('premade', '1');
                    } else {
                        params.append('premade', '0');
                    }
                }

                const url = '/api/jtb/library' + (params.toString() ? '?' + params.toString() : '');
                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    this.templates = data.templates;
                    this.renderTemplates();
                    this.updateCounts(data.total);
                } else {
                    throw new Error(data.error || 'Failed to load templates');
                }
            } catch (error) {
                console.error('Failed to load templates:', error);
                this.showError('Failed to load templates');
            } finally {
                this.showLoading(false);
            }
        },

        // ========================================
        // Rendering
        // ========================================

        renderCategories() {
            const container = document.getElementById('categoryList');
            if (!container) return;

            // Keep "All" category, add loaded ones
            let html = `
                <li class="category-item">
                    <div class="category-link ${!this.currentFilters.category ? 'active' : ''}" data-category="">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span>All Templates</span>
                        <span class="category-count" id="countAll">${this.categories[0]?.template_count || 0}</span>
                    </div>
                </li>
            `;

            // Skip first "All" pseudo-category
            this.categories.slice(1).forEach(cat => {
                html += `
                    <li class="category-item">
                        <div class="category-link ${this.currentFilters.category === cat.slug ? 'active' : ''}" data-category="${cat.slug}">
                            ${this.getCategoryIcon(cat.icon)}
                            <span>${cat.name}</span>
                            <span class="category-count">${cat.template_count || 0}</span>
                        </div>
                    </li>
                `;
            });

            container.innerHTML = html;
            this.bindCategoryEvents();
        },

        getCategoryIcon(iconName) {
            const icons = {
                'layout': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>',
                'briefcase': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
                'grid': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
                'file-text': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
                'shopping-cart': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
                'tool': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
                'clock': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                'mail': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
                'users': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'layers': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>'
            };
            return icons[iconName] || icons['grid'];
        },

        renderTemplates() {
            const container = document.getElementById('templateGrid');
            const emptyState = document.getElementById('emptyState');

            if (!container) return;

            if (this.templates.length === 0) {
                container.innerHTML = '';
                if (emptyState) emptyState.style.display = 'block';
                return;
            }

            if (emptyState) emptyState.style.display = 'none';

            let html = '';
            this.templates.forEach(template => {
                html += this.renderTemplateCard(template);
            });

            container.innerHTML = html;
            this.bindTemplateEvents();
        },

        renderTemplateCard(template) {
            const thumbnail = template.thumbnail
                ? `<img src="${template.thumbnail}" alt="${template.name}">`
                : `<div class="template-thumb-placeholder">
                       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                           <rect x="3" y="3" width="18" height="18" rx="2"/>
                           <line x1="3" y1="9" x2="21" y2="9"/>
                           <line x1="9" y1="21" x2="9" y2="9"/>
                       </svg>
                   </div>`;

            const categoryName = this.categories.find(c => c.slug === template.category_slug)?.name || template.category_slug || 'Uncategorized';

            return `
                <div class="template-card ${template.is_featured ? 'featured' : ''}" data-id="${template.id}">
                    <div class="template-thumb">
                        ${thumbnail}
                        <div class="template-overlay">
                            <button class="btn btn-primary preview-btn" data-id="${template.id}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Preview
                            </button>
                            <button class="btn btn-secondary use-btn" data-id="${template.id}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Use Template
                            </button>
                        </div>
                    </div>
                    <div class="template-info">
                        <div class="template-name">${template.name}</div>
                        <div class="template-meta">
                            <span>${categoryName}</span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                ${template.downloads || 0}
                            </span>
                            ${template.is_premade
                                ? '<span class="template-badge premade">Premade</span>'
                                : '<span class="template-badge">My Template</span>'
                            }
                        </div>
                    </div>
                </div>
            `;
        },

        updateCounts(total) {
            const countAll = document.getElementById('countAll');
            if (countAll) {
                countAll.textContent = total;
            }
        },

        showLoading(show) {
            const container = document.getElementById('templateGrid');
            if (!container) return;

            if (show) {
                container.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
            }
        },

        // ========================================
        // Events
        // ========================================

        bindEvents() {
            // Search
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let timeout;
                searchInput.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.currentFilters.search = searchInput.value;
                        this.loadTemplates();
                    }, 300);
                });
            }

            // Type filters
            document.getElementById('filterPages')?.addEventListener('change', (e) => {
                this.updateTypeFilter('page', e.target.checked);
            });
            document.getElementById('filterSections')?.addEventListener('change', (e) => {
                this.updateTypeFilter('section', e.target.checked);
            });

            // Source filters
            document.getElementById('filterPremade')?.addEventListener('change', (e) => {
                this.updateSourceFilter('premade', e.target.checked);
            });
            document.getElementById('filterMine')?.addEventListener('change', (e) => {
                this.updateSourceFilter('user', e.target.checked);
            });

            // Reset filters
            document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
                this.resetFilters();
            });

            // Import button
            document.getElementById('importBtn')?.addEventListener('click', () => {
                document.getElementById('importModal')?.classList.add('open');
            });

            // Close modals
            document.getElementById('closePreview')?.addEventListener('click', () => {
                this.closePreviewModal();
            });
            document.getElementById('closeImport')?.addEventListener('click', () => {
                document.getElementById('importModal')?.classList.remove('open');
            });

            // Modal overlays
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('open');
                    }
                });
            });

            // Import zone
            this.bindImportZone();

            // Preview modal actions
            document.getElementById('useTemplateBtn')?.addEventListener('click', () => {
                this.useTemplate(this.selectedTemplate);
            });
            document.getElementById('exportBtn')?.addEventListener('click', () => {
                this.exportTemplate(this.selectedTemplate);
            });
            document.getElementById('duplicateBtn')?.addEventListener('click', () => {
                this.duplicateTemplate(this.selectedTemplate);
            });

            // Keyboard
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closePreviewModal();
                    document.getElementById('importModal')?.classList.remove('open');
                }
            });
        },

        bindCategoryEvents() {
            document.querySelectorAll('.category-link').forEach(link => {
                link.addEventListener('click', () => {
                    const category = link.dataset.category;
                    this.currentFilters.category = category;

                    // Update active state
                    document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');

                    this.loadTemplates();
                });
            });
        },

        bindTemplateEvents() {
            // Preview buttons
            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // For Theme Builder, IDs are strings; for regular library, IDs are integers
                    const id = this.isThemeBuilderMode ? btn.dataset.id : parseInt(btn.dataset.id);
                    this.previewTemplate(id);
                });
            });

            // Use buttons
            document.querySelectorAll('.use-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const id = this.isThemeBuilderMode ? btn.dataset.id : parseInt(btn.dataset.id);
                    this.useTemplate(id);
                });
            });
        },

        bindImportZone() {
            const zone = document.getElementById('importZone');
            const fileInput = document.getElementById('importFile');

            if (!zone || !fileInput) return;

            zone.addEventListener('click', () => fileInput.click());

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    this.importFile(files[0]);
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    this.importFile(fileInput.files[0]);
                }
            });
        },

        updateTypeFilter(type, checked) {
            if (checked) {
                if (!this.currentFilters.types.includes(type)) {
                    this.currentFilters.types.push(type);
                }
            } else {
                this.currentFilters.types = this.currentFilters.types.filter(t => t !== type);
            }
            this.loadTemplates();
        },

        updateSourceFilter(source, checked) {
            if (checked) {
                if (!this.currentFilters.sources.includes(source)) {
                    this.currentFilters.sources.push(source);
                }
            } else {
                this.currentFilters.sources = this.currentFilters.sources.filter(s => s !== source);
            }
            this.loadTemplates();
        },

        resetFilters() {
            this.currentFilters = {
                category: '',
                types: ['page', 'section'],
                sources: ['premade', 'user'],
                search: ''
            };

            // Reset UI
            document.getElementById('searchInput').value = '';
            document.getElementById('filterPages').checked = true;
            document.getElementById('filterSections').checked = true;
            document.getElementById('filterPremade').checked = true;
            document.getElementById('filterMine').checked = true;
            document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
            document.querySelector('.category-link[data-category=""]')?.classList.add('active');

            this.loadTemplates();
        },

        // ========================================
        // Actions
        // ========================================

        async previewTemplate(id) {
            try {
                let template;

                // For Theme Builder mode, layouts are already loaded - find by ID
                if (this.isThemeBuilderMode) {
                    template = this.templates.find(t => t.id === id || t.id === String(id));
                    if (!template) {
                        throw new Error('Layout not found');
                    }
                } else {
                    const response = await fetch(`/api/jtb/library-get/${id}`);
                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error);
                    }
                    template = data.template;
                }

                this.selectedTemplate = id;
                document.getElementById('previewTitle').textContent = template.name;

                // Use server-side rendered preview or inline preview for theme builder
                const iframe = document.getElementById('previewFrame');
                if (this.isThemeBuilderMode) {
                    // Generate preview HTML inline for Theme Builder layouts
                    iframe.srcdoc = this.generatePreviewHtml(template.content);
                } else {
                    iframe.src = `/api/jtb/library-preview/${id}`;
                }

                document.getElementById('previewModal')?.classList.add('open');
            } catch (error) {
                console.error('Preview error:', error);
                this.showNotification('Failed to load preview', 'error');
            }
        },

        generatePreviewHtml(content) {
            // Fallback preview - normally uses server-side renderer
            return `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        body {
                            margin: 0;
                            padding: 40px;
                            font-family: 'Inter', sans-serif;
                            background: #f8fafc;
                            color: #1f2937;
                        }
                        .preview-notice {
                            text-align: center;
                            padding: 60px;
                            color: #6b7280;
                        }
                    </style>
                </head>
                <body>
                    <div class="preview-notice">
                        <p>Template preview will be rendered here</p>
                        <p>Content: ${JSON.stringify(content).substring(0, 100)}...</p>
                    </div>
                </body>
                </html>
            `;
        },

        closePreviewModal() {
            document.getElementById('previewModal')?.classList.remove('open');
            this.selectedTemplate = null;
        },

        async useTemplate(id) {
            if (!id) return;

            try {
                let template;

                // For Theme Builder mode, layouts are already loaded - find by ID
                if (this.isThemeBuilderMode) {
                    template = this.templates.find(t => t.id === id || t.id === String(id));
                    if (!template) {
                        throw new Error('Layout not found');
                    }
                } else {
                    // For regular library, fetch from API
                    const response = await fetch(`/api/jtb/library-get/${id}`);
                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error);
                    }
                    template = data.template;
                }

                if (this.embedMode) {
                    // In embed mode, post message to parent
                    window.parent.postMessage({
                        type: 'jtb-library-select',
                        template: template
                    }, '*');
                } else {
                    // In standalone mode, redirect to builder with template
                    // Store template in sessionStorage
                    sessionStorage.setItem('jtb-import-template', JSON.stringify(template.content));
                    window.location.href = '/admin/jessie-theme-builder';
                }

                this.closePreviewModal();
            } catch (error) {
                console.error('Use template error:', error);
                this.showNotification('Failed to use template', 'error');
            }
        },

        async exportTemplate(id) {
            if (!id) return;
            window.location.href = `/api/jtb/library-export/${id}`;
        },

        async duplicateTemplate(id) {
            if (!id) return;

            try {
                const response = await fetch('/api/jtb/library-duplicate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({ id })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('Template duplicated!', 'success');
                    this.loadTemplates();
                    this.closePreviewModal();
                } else {
                    throw new Error(data.error);
                }
            } catch (error) {
                console.error('Duplicate error:', error);
                this.showNotification('Failed to duplicate template', 'error');
            }
        },

        async importFile(file) {
            if (!file.name.endsWith('.json')) {
                this.showNotification('Please select a JSON file', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', this.csrfToken);

            try {
                const response = await fetch('/api/jtb/library-import', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(`Template "${data.name}" imported!`, 'success');
                    document.getElementById('importModal')?.classList.remove('open');
                    this.loadTemplates();
                } else {
                    throw new Error(data.error);
                }
            } catch (error) {
                console.error('Import error:', error);
                this.showNotification('Failed to import template: ' + error.message, 'error');
            }
        },

        // ========================================
        // Notifications
        // ========================================

        showNotification(message, type = 'info') {
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;

            document.body.appendChild(notification);

            requestAnimationFrame(() => {
                notification.classList.add('show');
            });

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        },

        showError(message) {
            this.showNotification(message, 'error');
        }
    };

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        Library.init();
    });

    // Expose globally for embed communication
    window.JTBLibrary = Library;

})();
