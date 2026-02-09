/**
 * Website Editor - Click-to-edit functionality
 * 
 * @package JessieThemeBuilder
 * @since 2026-02-08
 */

(function() {
    'use strict';

    const WE = {
        config: window.WE_CONFIG || {},
        iframe: null,
        sidebar: null,
        currentElement: null,
        
        init: function() {
            this.iframe = document.getElementById('we-iframe');
            this.sidebar = document.getElementById('we-panel-content');
            
            if (!this.iframe) {
                console.error('[WE] iframe not found');
                return;
            }
            
            this.bindEvents();
            this.loadSession();
            
            // console.log removed
        },
        
        bindEvents: function() {
            // Page selector
            const pageSelect = document.getElementById('we-page-select');
            if (pageSelect) {
                pageSelect.addEventListener('change', (e) => this.loadPage(e.target.value));
            }
            
            // Save button
            const saveBtn = document.getElementById('we-save-btn');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.save());
            }
            
            // Preview button
            const previewBtn = document.getElementById('we-preview-btn');
            if (previewBtn) {
                previewBtn.addEventListener('click', () => this.preview());
            }
            
            // iframe load
            this.iframe.addEventListener('load', () => this.onIframeLoad());
        },
        
        loadSession: function() {
            if (!this.config.sessionId) {
                this.setStatus('No session ID', 'error');
                return;
            }
            
            this.setStatus('Loading session...');
            
            fetch(`${this.config.apiBase}/ai/multi-agent?action=get-session&session_id=${this.config.sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.pages) {
                        this.populatePageSelect(data.pages);
                        this.setStatus('Session loaded', 'success');
                    } else {
                        this.setStatus('Failed to load session', 'error');
                    }
                })
                .catch(err => {
                    console.error('[WE] Session load error:', err);
                    this.setStatus('Error loading session', 'error');
                });
        },
        
        populatePageSelect: function(pages) {
            const select = document.getElementById('we-page-select');
            if (!select) return;
            
            select.innerHTML = '<option value="">Select a page...</option>';
            
            pages.forEach((page, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = page.name || `Page ${index + 1}`;
                select.appendChild(option);
            });
        },
        
        loadPage: function(pageIndex) {
            if (pageIndex === '') return;
            
            this.setStatus('Loading page...');
            
            // Load page preview into iframe
            const previewUrl = `${this.config.apiBase}/ai/multi-agent?action=preview-page&session_id=${this.config.sessionId}&page=${pageIndex}`;
            this.iframe.src = previewUrl;
        },
        
        onIframeLoad: function() {
            this.setStatus('Ready', 'success');
            this.setupClickToEdit();
        },
        
        setupClickToEdit: function() {
            try {
                const iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                
                // Add click listeners to editable elements
                const editables = iframeDoc.querySelectorAll('[data-jtb-module]');
                editables.forEach(el => {
                    el.style.cursor = 'pointer';
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.selectElement(el);
                    });
                    
                    // Hover effect
                    el.addEventListener('mouseenter', () => {
                        el.style.outline = '2px dashed #6366f1';
                    });
                    el.addEventListener('mouseleave', () => {
                        if (el !== this.currentElement) {
                            el.style.outline = '';
                        }
                    });
                });
            } catch (e) {
                console.warn('[WE] Cannot access iframe content (CORS):', e);
            }
        },
        
        selectElement: function(el) {
            // Deselect previous
            if (this.currentElement) {
                this.currentElement.style.outline = '';
            }
            
            this.currentElement = el;
            el.style.outline = '2px solid #6366f1';
            
            // Show settings in sidebar
            const moduleType = el.dataset.jtbModule;
            const panelTitle = document.getElementById('we-panel-title');
            if (panelTitle) {
                panelTitle.textContent = moduleType || 'Element';
            }
            
            this.showElementSettings(el);
        },
        
        showElementSettings: function(el) {
            const moduleType = el.dataset.jtbModule;
            const moduleId = el.dataset.jtbId;
            
            this.sidebar.innerHTML = `
                <div style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 12px;">
                    <div style="color: #94a3b8; font-size: 12px;">Module Type</div>
                    <div style="color: #fff; font-weight: 500;">${moduleType || 'Unknown'}</div>
                </div>
                <div style="color: #94a3b8; text-align: center; padding: 20px;">
                    <p style="margin-bottom: 12px;">Use the Page Builder for detailed editing</p>
                    <a href="/admin/jessie-theme-builder" style="color: #6366f1; text-decoration: none; font-size: 13px;">Open Page Builder →</a>
                </div>
            `;
        },
        
        save: function() {
            if (!this.config.sessionId) {
                this.setStatus('No session to save', 'error');
                return;
            }

            if (!confirm('Save this website to CMS? This will create/update templates.')) return;

            this.setStatus('Saving...');

            fetch(`${this.config.apiBase}/ai/save-website`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.config.csrfToken
                },
                body: JSON.stringify({
                    session_id: this.config.sessionId,
                    clear_existing: false,
                    mapping: {}
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.setStatus(`Saved! (${data.data?.saved_count || 0} items)`, 'success');
                } else {
                    this.setStatus(data.error || 'Save failed', 'error');
                }
            })
            .catch(err => {
                console.error('[WE] Save error:', err);
                this.setStatus('Save failed', 'error');
            });
        },
        
        preview: function() {
            // Open preview in new tab
            if (this.config.sessionId) {
                window.open(`/preview?session=${this.config.sessionId}`, '_blank');
            }
        },
        
        setStatus: function(text, type = '') {
            const status = document.getElementById('we-status');
            if (status) {
                status.textContent = text;
                status.className = 'we-status' + (type ? ' ' + type : '');
            }
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => WE.init());
    } else {
        WE.init();
    }

})();
