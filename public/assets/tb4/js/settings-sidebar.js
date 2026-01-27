/**
 * TB4 Settings Sidebar
 * Foundation component for Theme Builder 4 module settings
 * @version 1.1.0
 * @updated 2026-01-13 - Integrated professional Border/Shadow controls from TB4Builder
 */

const TB4SettingsSidebar = {
    state: {
        isOpen: false,
        activeTab: 'content',
        device: 'desktop',
        hoverMode: false,
        moduleType: null,
        moduleId: null,
        moduleData: null
    },

    dom: {},
    listeners: {},

    icons: {
        desktop: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
        tablet: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>',
        phone: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>',
        chevronDown: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>',
        close: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        settings: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>',
        typography: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>',
        spacing: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',
        background: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        border: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>',
        animation: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
        visibility: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        code: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
        shadow: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M7 21L21 7M14 21L21 14M21 21L21 21"></path></svg>',
        sizing: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-6-6m6 6v-4.8m0 4.8h-4.8"/><path d="M3 16.2V21m0 0h4.8M3 21l6-6"/><path d="M21 7.8V3m0 0h-4.8M21 3l-6 6"/><path d="M3 7.8V3m0 0h4.8M3 3l6 6"/></svg>',
        position: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 9l7-7 7 7"/><path d="M5 15l7 7 7-7"/><path d="M9 5l-7 7 7 7"/><path d="M15 5l7 7-7 7"/></svg>',
        filters: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>',
        transform: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M3 12h18"/><rect x="4" y="4" width="16" height="16" rx="2" transform="rotate(45 12 12)"/></svg>'
    },

    init: function() {
        if (this.dom.sidebar) return this;
        this.render();
        this.cacheDom();
        this.bindEvents();
        this.injectStyles();
        return this;
    },

    cacheDom: function() {
        this.dom.sidebar = document.getElementById('tb4-settings-sidebar');
        this.dom.overlay = document.getElementById('tb4-ss-overlay');
        this.dom.header = this.dom.sidebar.querySelector('.tb4-ss-header');
        this.dom.headerIcon = this.dom.sidebar.querySelector('.tb4-ss-title-icon');
        this.dom.headerTitle = this.dom.sidebar.querySelector('.tb4-ss-header-title');
        this.dom.closeBtn = this.dom.sidebar.querySelector('.tb4-ss-close');
        this.dom.tabs = this.dom.sidebar.querySelectorAll('.tb4-ss-tab');
        this.dom.tabPanels = this.dom.sidebar.querySelectorAll('.tb4-ss-panel');
        this.dom.deviceBtns = this.dom.sidebar.querySelectorAll('.tb4-ss-device-btn');
        this.dom.hoverToggle = this.dom.sidebar.querySelector('.tb4-ss-hover-toggle input');
        this.dom.contentArea = this.dom.sidebar.querySelector('.tb4-ss-content');
        this.dom.cancelBtn = this.dom.sidebar.querySelector('.tb4-ss-cancel-btn');
        this.dom.saveBtn = this.dom.sidebar.querySelector('.tb4-ss-save-btn');
    },

    bindEvents: function() {
        var self = this;
        this.dom.closeBtn.addEventListener('click', function() { self.close(); });
        this.dom.overlay.addEventListener('click', function() { self.close(); });
        this.dom.tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                self.switchTab(this.getAttribute('data-tab'));
            });
        });
        this.dom.deviceBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                self.switchDevice(this.getAttribute('data-device'));
            });
        });
        this.dom.hoverToggle.addEventListener('change', function() {
            self.setHoverMode(this.checked);
        });
        this.dom.cancelBtn.addEventListener('click', function() { self.close(); });
        this.dom.saveBtn.addEventListener('click', function() { self.save(); });
        
        // Live preview with debounce
        var livePreviewTimer = null;
        var triggerLivePreview = function() {
            clearTimeout(livePreviewTimer);
            livePreviewTimer = setTimeout(function() {
                console.log('[TB4-SS] triggerLivePreview fired, state:', {
                    isOpen: self.state.isOpen,
                    moduleId: self.state.moduleId,
                    moduleType: self.state.moduleType
                });
                if (!self.state.isOpen || !self.state.moduleId) {
                    console.warn('[TB4-SS] Live preview aborted - no open module');
                    return;
                }
                var settings = self.collectSettings();
                console.log('[TB4-SS] Dispatching tb4-ss:liveUpdate with settings:', settings);
                self.dispatch('tb4-ss:liveUpdate', {
                    moduleType: self.state.moduleType,
                    moduleId: self.state.moduleId,
                    settings: settings
                });
            }, 250);
        };
        
        // Listen for input changes on sidebar (event delegation)
        // Include contenteditable for WYSIWYG editors
        this.dom.sidebar.addEventListener('input', function(e) {
            console.log('[TB4-SS] Input event on:', e.target.tagName, e.target.dataset?.name || e.target.name);
            if (e.target.matches('input, select, textarea, [contenteditable="true"]')) {
                console.log('[TB4-SS] Matched - triggering live preview');
                triggerLivePreview();
            }
        });
        this.dom.sidebar.addEventListener('change', function(e) {
            console.log('[TB4-SS] Change event on:', e.target.tagName, e.target.name);
            if (e.target.matches('input, select, textarea, [contenteditable="true"]')) {
                console.log('[TB4-SS] Matched change - triggering live preview');
                triggerLivePreview();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && self.state.isOpen) self.close();
        });
        this.dom.sidebar.addEventListener('click', function(e) {
            var toggleHeader = e.target.closest('.tb4-ss-toggle-header');
            if (toggleHeader) {
                var toggle = toggleHeader.closest('.tb4-ss-toggle-group');
                if (toggle) toggle.classList.toggle('is-open');
            }
        });
    },

    render: function() {
        var container = document.createElement('div');
        container.innerHTML = this.getTemplate();
        document.body.appendChild(container.querySelector('#tb4-ss-overlay'));
        document.body.appendChild(container.querySelector('#tb4-settings-sidebar'));
    },

    getTemplate: function() {
        return '<div id="tb4-ss-overlay" class="tb4-ss-overlay"></div>' +
            '<aside id="tb4-settings-sidebar" class="tb4-settings-sidebar">' +
            '<header class="tb4-ss-header">' +
            '<div class="tb4-ss-title">' +
            '<span class="tb4-ss-title-icon">' + this.icons.settings + '</span>' +
            '<span class="tb4-ss-header-title">Module Settings</span>' +
            '</div>' +
            '<button type="button" class="tb4-ss-close" aria-label="Close settings">' + this.icons.close + '</button>' +
            '</header>' +
            '<nav class="tb4-ss-tabs">' +
            '<button type="button" class="tb4-ss-tab active" data-tab="content">Content</button>' +
            '<button type="button" class="tb4-ss-tab" data-tab="design">Design</button>' +
            '<button type="button" class="tb4-ss-tab" data-tab="advanced">Advanced</button>' +
            '</nav>' +
            '<div class="tb4-ss-toolbar">' +
            '<div class="tb4-ss-devices">' +
            '<button type="button" class="tb4-ss-device-btn active" data-device="desktop" title="Desktop">' + this.icons.desktop + '</button>' +
            '<button type="button" class="tb4-ss-device-btn" data-device="tablet" title="Tablet">' + this.icons.tablet + '</button>' +
            '<button type="button" class="tb4-ss-device-btn" data-device="phone" title="Phone">' + this.icons.phone + '</button>' +
            '</div>' +
            '<label class="tb4-ss-hover-toggle"><input type="checkbox" /><span class="tb4-ss-hover-switch"></span><span>Hover</span></label>' +
            '</div>' +
            '<div class="tb4-ss-content">' +
            '<div class="tb4-ss-panel active" data-panel="content">' +
            this.createToggleGroup('content-text', 'Text Content', this.icons.typography, true) +
            this.createToggleGroup('content-media', 'Media', this.icons.background, false) +
            this.createToggleGroup('content-link', 'Link Settings', this.icons.code, false) +
            '</div>' +
            '<div class="tb4-ss-panel" data-panel="design">' +
            this.createToggleGroup('design-typography', 'Typography', this.icons.typography, true) +
            this.createToggleGroup('design-spacing', 'Spacing', this.icons.spacing, true) +
            this.createToggleGroup('design-background', 'Background', this.icons.background, true) +
            this.createToggleGroup('design-border', 'Border', this.icons.border, true) +
            this.createToggleGroup('design-shadow', 'Box Shadow', this.icons.shadow || this.icons.settings, false) +
            this.createToggleGroup('design-sizing', 'Sizing', this.icons.sizing || this.icons.settings, false) +
            this.createToggleGroup('design-position', 'Position', this.icons.position || this.icons.settings, false) +
            this.createToggleGroup('design-filters', 'CSS Filters', this.icons.filters || this.icons.settings, false) +
            this.createToggleGroup('design-transform', 'Transform', this.icons.transform || this.icons.settings, false) +
            this.createToggleGroup('design-animation', 'Animation', this.icons.animation, false) +
            '</div>' +
            '<div class="tb4-ss-panel" data-panel="advanced">' +
            this.createToggleGroup('advanced-animation', 'Animation', this.icons.animation, true) +
            this.createToggleGroup('advanced-visibility', 'Visibility', this.icons.visibility, true) +
            this.createToggleGroup('advanced-custom', 'Custom CSS/ID', this.icons.code, true) +
            '</div>' +
            '</div>' +
            '<footer class="tb4-ss-footer">' +
            '<button type="button" class="tb4-ss-btn tb4-ss-btn-secondary tb4-ss-cancel-btn">Cancel</button>' +
            '<button type="button" class="tb4-ss-btn tb4-ss-btn-primary tb4-ss-save-btn">Save Changes</button>' +
            '</footer>' +
            '</aside>';
    },

    createToggleGroup: function(id, title, icon, open) {
        var openClass = open ? ' is-open' : '';
        return '<div class="tb4-ss-toggle-group' + openClass + '" data-group="' + id + '">' +
            '<div class="tb4-ss-toggle-header">' +
            '<div class="tb4-ss-toggle-title">' +
            '<span class="tb4-ss-toggle-icon">' + icon + '</span>' +
            '<span>' + title + '</span>' +
            '</div>' +
            '<span class="tb4-ss-toggle-arrow">' + this.icons.chevronDown + '</span>' +
            '</div>' +
            '<div class="tb4-ss-toggle-body"></div>' +
            '</div>';
    },

    injectStyles: function() {
        // Skip if external CSS is loaded (check for stylesheet link)
        var externalCss = document.querySelector('link[href*="settings-sidebar.css"]');
        if (externalCss) {
            console.log('[TB4] Settings Sidebar: External CSS detected, skipping style injection');
            return;
        }
        // Also skip if styles already injected
        if (document.getElementById('tb4-ss-styles')) return;
        var styles = document.createElement('style');
        styles.id = 'tb4-ss-styles';
        styles.textContent = this.getStyles();
        document.head.appendChild(styles);
    },

    getStyles: function() {
        return '.tb4-ss-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9998;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}' +
            '.tb4-ss-overlay.is-visible{opacity:1;visibility:visible}' +
            '.tb4-settings-sidebar{position:fixed;top:0;right:0;width:360px;max-width:100%;height:100vh;background:#fff;box-shadow:-4px 0 20px rgba(0,0,0,0.15);z-index:9999;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .3s}' +
            '.tb4-settings-sidebar.is-open{transform:translateX(0)}' +
            '.tb4-ss-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-header-left{display:flex;align-items:center;gap:10px}' +
            '.tb4-ss-header-icon{display:flex;color:#6366f1}' +
            '.tb4-ss-header-title{font-size:16px;font-weight:600;color:#111827}' +
            '.tb4-ss-close-btn{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border:none;background:transparent;border-radius:8px;cursor:pointer;color:#6b7280;transition:background .2s,color .2s}' +
            '.tb4-ss-close-btn:hover{background:#f3f4f6;color:#111827}' +
            '.tb4-ss-tabs{display:flex;border-bottom:1px solid #e5e7eb;background:#fff}' +
            '.tb4-ss-tab{flex:1;padding:12px 16px;border:none;background:transparent;font-size:14px;font-weight:500;color:#6b7280;cursor:pointer;position:relative;transition:color .2s}' +
            '.tb4-ss-tab:hover{color:#111827}' +
            '.tb4-ss-tab.is-active{color:#6366f1}' +
            '.tb4-ss-tab.is-active::after{content:"";position:absolute;bottom:-1px;left:0;right:0;height:2px;background:#6366f1}' +
            '.tb4-ss-toolbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-devices{display:flex;gap:4px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:4px}' +
            '.tb4-ss-device-btn{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border:none;background:transparent;border-radius:6px;cursor:pointer;color:#9ca3af;transition:background .2s,color .2s}' +
            '.tb4-ss-device-btn:hover{color:#6b7280}' +
            '.tb4-ss-device-btn.is-active{background:#6366f1;color:#fff}' +
            '.tb4-ss-hover-toggle{display:flex;align-items:center;gap:8px;cursor:pointer}' +
            '.tb4-ss-hover-toggle input{width:36px;height:20px;-webkit-appearance:none;appearance:none;background:#e5e7eb;border-radius:10px;position:relative;cursor:pointer;transition:background .2s}' +
            '.tb4-ss-hover-toggle input::before{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,0.2);transition:transform .2s}' +
            '.tb4-ss-hover-toggle input:checked{background:#6366f1}' +
            '.tb4-ss-hover-toggle input:checked::before{transform:translateX(16px)}' +
            '.tb4-ss-hover-label{font-size:13px;font-weight:500;color:#6b7280}' +
            '.tb4-ss-content{flex:1;overflow-y:auto;padding:16px 20px}' +
            '.tb4-ss-panel{display:none}' +
            '.tb4-ss-panel.is-active{display:block}' +
            '.tb4-ss-toggle-group{border:1px solid #e5e7eb;border-radius:8px;margin-bottom:12px;overflow:hidden}' +
            '.tb4-ss-toggle-header{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f9fafb;cursor:pointer;user-select:none;transition:background .2s}' +
            '.tb4-ss-toggle-header:hover{background:#f3f4f6}' +
            '.tb4-ss-toggle-icon{display:flex;color:#6b7280}' +
            '.tb4-ss-toggle-title{flex:1;font-size:14px;font-weight:500;color:#374151}' +
            '.tb4-ss-toggle-chevron{display:flex;color:#9ca3af;transform:rotate(-90deg);transition:transform .2s}' +
            '.tb4-ss-toggle-group.is-open .tb4-ss-toggle-chevron{transform:rotate(0deg)}' +
            '.tb4-ss-toggle-content{max-height:0;overflow:hidden;transition:max-height .25s}' +
            '.tb4-ss-toggle-group.is-open .tb4-ss-toggle-content{max-height:500px}' +
            '.tb4-ss-toggle-body{padding:16px;border-top:1px solid #e5e7eb}' +
            '.tb4-ss-footer{display:flex;gap:12px;padding:16px 20px;border-top:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-cancel-btn,.tb4-ss-save-btn{flex:1;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;transition:background .2s,border-color .2s}' +
            '.tb4-ss-cancel-btn{background:#fff;border:1px solid #e5e7eb;color:#374151}' +
            '.tb4-ss-cancel-btn:hover{background:#f9fafb;border-color:#d1d5db}' +
            '.tb4-ss-save-btn{background:#6366f1;border:1px solid #6366f1;color:#fff}' +
            '.tb4-ss-save-btn:hover{background:#4f46e5;border-color:#4f46e5}';
    },

    open: function(moduleType, moduleId, moduleData) {
        this.state.moduleType = moduleType || 'module';
        this.state.moduleId = moduleId || null;
        this.state.moduleData = moduleData || {};
        this.state.isOpen = true;
        this.dom.headerTitle.textContent = this.formatModuleTitle(moduleType);
        this.dom.sidebar.classList.add('open');
        this.dom.overlay.classList.add('visible');

        // Populate fields with module data
        this.populateFields();

        this.dispatch('tb4-ss:open', {
            moduleType: this.state.moduleType,
            moduleId: this.state.moduleId,
            moduleData: this.state.moduleData
        });
        return this;
    },

    formatModuleTitle: function(type) {
        if (!type) return 'Module Settings';
        return type.charAt(0).toUpperCase() + type.slice(1).replace(/-/g, ' ') + ' Settings';
    },

    close: function() {
        this.state.isOpen = false;
        this.dom.sidebar.classList.remove('open');
        this.dom.overlay.classList.remove('visible');
        this.dispatch('tb4-ss:close', {
            moduleType: this.state.moduleType,
            moduleId: this.state.moduleId
        });
        this.state.moduleType = null;
        this.state.moduleId = null;
        this.state.moduleData = null;
        return this;
    },

    save: function() {
        var settings = this.collectSettings();
        this.dispatch('tb4-ss:save', {
            moduleType: this.state.moduleType,
            moduleId: this.state.moduleId,
            settings: settings,
            device: this.state.device,
            hoverMode: this.state.hoverMode
        });
        this.close();
        return this;
    },

    collectSettings: function() {
        var settings = {};
        var inputs = this.dom.sidebar.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            var name = input.name || input.getAttribute('data-setting');
            if (!name || name === '') return;
            if (input.type === 'checkbox') {
                settings[name] = input.checked;
            } else if (input.type === 'radio') {
                if (input.checked) settings[name] = input.value;
            } else {
                settings[name] = input.value;
            }
        });
        return settings;
    },

    switchTab: function(tabName) {
        if (this.state.activeTab === tabName) return this;
        var previousTab = this.state.activeTab;
        this.state.activeTab = tabName;
        this.dom.tabs.forEach(function(tab) {
            tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName);
        });
        this.dom.tabPanels.forEach(function(panel) {
            panel.classList.toggle('active', panel.getAttribute('data-panel') === tabName);
        });
        this.dispatch('tb4-ss:tabChange', { tab: tabName, previousTab: previousTab });
        return this;
    },

    switchDevice: function(device) {
        if (this.state.device === device) return this;
        var previousDevice = this.state.device;
        this.state.device = device;
        this.dom.deviceBtns.forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('data-device') === device);
        });
        this.dispatch('tb4-ss:deviceChange', { device: device, previousDevice: previousDevice });
        return this;
    },

    getDevice: function() { return this.state.device; },

    setHoverMode: function(enabled) {
        this.state.hoverMode = enabled;
        this.dom.hoverToggle.checked = enabled;
        this.dispatch('tb4-ss:hoverModeChange', { hoverMode: enabled });
        return this;
    },

    isHoverMode: function() { return this.state.hoverMode; },

    dispatch: function(eventName, detail) {
        var event = new CustomEvent(eventName, { bubbles: true, detail: detail || {} });
        document.dispatchEvent(event);
        if (this.listeners[eventName]) {
            this.listeners[eventName].forEach(function(callback) { callback(detail); });
        }
        return this;
    },

    on: function(eventName, callback) {
        if (!this.listeners[eventName]) this.listeners[eventName] = [];
        this.listeners[eventName].push(callback);
        return this;
    },

    off: function(eventName, callback) {
        if (!this.listeners[eventName]) return this;
        if (callback) {
            this.listeners[eventName] = this.listeners[eventName].filter(function(cb) { return cb !== callback; });
        } else {
            delete this.listeners[eventName];
        }
        return this;
    },

    getToggleGroupContent: function(groupId) {
        var sidebar = document.getElementById('tb4-settings-sidebar');
        if (!sidebar) return null;
        var toggle = sidebar.querySelector('.tb4-ss-toggle-group[data-group="' + groupId + '"]');
        return toggle ? toggle.querySelector('.tb4-ss-toggle-body') : null;
    },

    setToggleGroupContent: function(groupId, html) {
        var container = this.getToggleGroupContent(groupId);
        if (container) container.innerHTML = html;
        return this;
    },

    isOpen: function() { return this.state.isOpen; },

    getModuleInfo: function() {
        return { type: this.state.moduleType, id: this.state.moduleId, data: this.state.moduleData };
    },

    // =========================================================================
    // Field Rendering System
    // =========================================================================

    /**
     * Escape HTML entities for safe output
     * @param {string} str - String to escape
     * @returns {string} Escaped string
     */
    escapeHtml: function(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    },

    /**
     * Main field renderer dispatcher
     * @param {Object} fieldConfig - Field configuration object
     * @param {*} value - Current field value
     * @returns {string} HTML string for the field
     */
    renderField: function(fieldConfig, value) {
        if (!fieldConfig || !fieldConfig.type) {
            console.warn('[TB4] renderField: Invalid field config', fieldConfig);
            return '';
        }

        var type = fieldConfig.type.toLowerCase();
        var actualValue = (value !== undefined && value !== null) ? value : (fieldConfig.default || '');

        switch (type) {
            case 'text':
                return this.renderTextField(fieldConfig, actualValue);
            case 'textarea':
                return this.renderTextareaField(fieldConfig, actualValue);
            case 'number':
                return this.renderNumberField(fieldConfig, actualValue);
            case 'select':
                return this.renderSelectField(fieldConfig, actualValue);
            case 'checkbox':
                return this.renderCheckboxField(fieldConfig, actualValue);
            case 'yes_no':
            case 'yesno':
                return this.renderYesNoField(fieldConfig, actualValue);
            case 'color':
                return this.renderColorField(fieldConfig, actualValue);
            case 'range':
                return this.renderRangeField(fieldConfig, actualValue);
            case 'upload':
                return this.renderUploadField(fieldConfig, actualValue);
            case 'typography':
                return this.renderTypographyField(fieldConfig, actualValue);
            case 'spacing':
                return this.renderSpacingField(fieldConfig, actualValue);
            case 'boxshadow':
                return this.renderBoxShadowVisual({ design: actualValue });
            case 'border_advanced':
                return this.renderBorderVisual({ design: actualValue });
            case 'wysiwyg':
                return this.renderWysiwygField(fieldConfig, actualValue);
            default:
                console.warn('[TB4] renderField: Unknown field type "' + type + '"');
                return this.renderTextField(fieldConfig, actualValue);
        }
    },

    /**
     * Render field wrapper with label and description
     * @param {Object} config - Field config
     * @param {string} inputHtml - Inner input HTML
     * @returns {string} Complete field HTML
     */
    renderFieldWrapper: function(config, inputHtml) {
        var html = '<div class="tb4-ss-field" data-field="' + this.escapeHtml(config.name) + '">';

        if (config.label) {
            html += '<label class="tb4-ss-field-label" for="tb4-field-' + this.escapeHtml(config.name) + '">' +
                    this.escapeHtml(config.label) + '</label>';
        }

        html += inputHtml;

        if (config.description) {
            html += '<p class="tb4-ss-field-desc">' + this.escapeHtml(config.description) + '</p>';
        }

        html += '</div>';
        return html;
    },

    /**
     * Render text input field
     * @param {Object} config - Field configuration
     * @param {string} value - Current value
     * @returns {string} HTML string
     */
    renderTextField: function(config, value) {
        var inputHtml = '<input type="text"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + this.escapeHtml(value) + '"' +
            ' class="tb4-ss-input"' +
            (config.placeholder ? ' placeholder="' + this.escapeHtml(config.placeholder) + '"' : '') +
            (config.maxlength ? ' maxlength="' + parseInt(config.maxlength, 10) + '"' : '') +
            (config.required ? ' required' : '') +
            '>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render textarea field
     * @param {Object} config - Field configuration
     * @param {string} value - Current value
     * @returns {string} HTML string
     */
    renderTextareaField: function(config, value) {
        var rows = config.rows || 4;
        var inputHtml = '<textarea' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' class="tb4-ss-input tb4-ss-textarea"' +
            ' rows="' + parseInt(rows, 10) + '"' +
            (config.placeholder ? ' placeholder="' + this.escapeHtml(config.placeholder) + '"' : '') +
            (config.maxlength ? ' maxlength="' + parseInt(config.maxlength, 10) + '"' : '') +
            (config.required ? ' required' : '') +
            '>' + this.escapeHtml(value) + '</textarea>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render number input field
     * @param {Object} config - Field configuration
     * @param {number|string} value - Current value
     * @returns {string} HTML string
     */
    renderNumberField: function(config, value) {
        var inputHtml = '<input type="number"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + this.escapeHtml(value) + '"' +
            ' class="tb4-ss-input"' +
            (config.min !== undefined ? ' min="' + parseFloat(config.min) + '"' : '') +
            (config.max !== undefined ? ' max="' + parseFloat(config.max) + '"' : '') +
            (config.step !== undefined ? ' step="' + parseFloat(config.step) + '"' : '') +
            (config.placeholder ? ' placeholder="' + this.escapeHtml(config.placeholder) + '"' : '') +
            (config.required ? ' required' : '') +
            '>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render select dropdown field
     * @param {Object} config - Field configuration
     * @param {string} value - Current value
     * @returns {string} HTML string
     */
    renderSelectField: function(config, value) {
        var self = this;
        var rawOptions = config.options || [];
        
        // Convert options to array format (handles both array and object)
        var options = [];
        if (Array.isArray(rawOptions)) {
            options = rawOptions;
        } else if (typeof rawOptions === 'object') {
            // Convert {key: label} object to [{value: key, label: label}] array
            for (var key in rawOptions) {
                if (rawOptions.hasOwnProperty(key)) {
                    options.push({ value: key, label: rawOptions[key] });
                }
            }
        }

        var optionsHtml = options.map(function(opt) {
            var optValue = typeof opt === 'object' ? opt.value : opt;
            var optLabel = typeof opt === 'object' ? opt.label : opt;
            var selected = String(optValue) === String(value) ? ' selected' : '';
            return '<option value="' + self.escapeHtml(optValue) + '"' + selected + '>' +
                   self.escapeHtml(optLabel) + '</option>';
        }).join('');

        var inputHtml = '<div class="tb4-ss-select-wrapper">' +
            '<select' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' class="tb4-ss-input tb4-ss-select"' +
            (config.required ? ' required' : '') +
            '>' + optionsHtml + '</select>' +
            '<span class="tb4-ss-select-arrow">' + this.icons.chevronDown + '</span>' +
            '</div>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render checkbox field
     * @param {Object} config - Field configuration
     * @param {boolean|string} value - Current value
     * @returns {string} HTML string
     */
    renderCheckboxField: function(config, value) {
        var checked = value === true || value === 'true' || value === '1' || value === 1;

        var inputHtml = '<label class="tb4-ss-checkbox-wrapper">' +
            '<input type="checkbox"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' class="tb4-ss-checkbox-input"' +
            ' value="1"' +
            (checked ? ' checked' : '') +
            '>' +
            '<span class="tb4-ss-checkbox-box">' +
            '<svg class="tb4-ss-checkbox-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">' +
            '<polyline points="20 6 9 17 4 12"></polyline>' +
            '</svg>' +
            '</span>' +
            (config.checkboxLabel ? '<span class="tb4-ss-checkbox-label">' + this.escapeHtml(config.checkboxLabel) + '</span>' : '') +
            '</label>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render Yes/No toggle field
     * @param {Object} config - Field configuration
     * @param {boolean|string} value - Current value
     * @returns {string} HTML string
     */
    renderYesNoField: function(config, value) {
        var isYes = value === true || value === 'true' || value === '1' || value === 1 || value === 'yes';

        var inputHtml = '<div class="tb4-ss-yesno-wrapper">' +
            '<input type="hidden"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + (isYes ? 'yes' : 'no') + '"' +
            ' class="tb4-ss-yesno-value">' +
            '<button type="button" class="tb4-ss-yesno-btn' + (!isYes ? ' active' : '') + '" data-value="no">No</button>' +
            '<button type="button" class="tb4-ss-yesno-btn' + (isYes ? ' active' : '') + '" data-value="yes">Yes</button>' +
            '</div>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render color picker field
     * @param {Object} config - Field configuration
     * @param {string} value - Current value (hex color)
     * @returns {string} HTML string
     */
    renderColorField: function(config, value) {
        var colorValue = value || config.default || '#6366f1';

        var inputHtml = '<div class="tb4-ss-color-wrapper">' +
            '<input type="color"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + this.escapeHtml(colorValue) + '"' +
            ' class="tb4-ss-color-input">' +
            '<input type="text"' +
            ' class="tb4-ss-input tb4-ss-color-text"' +
            ' value="' + this.escapeHtml(colorValue) + '"' +
            ' placeholder="#000000"' +
            ' pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"' +
            ' data-color-text-for="' + this.escapeHtml(config.name) + '">' +
            '<span class="tb4-ss-color-preview" style="background-color: ' + this.escapeHtml(colorValue) + '"></span>' +
            '</div>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render range slider field
     * @param {Object} config - Field configuration
     * @param {number|string} value - Current value
     * @returns {string} HTML string
     */
    renderRangeField: function(config, value) {
        var min = config.min !== undefined ? parseFloat(config.min) : 0;
        var max = config.max !== undefined ? parseFloat(config.max) : 100;
        var step = config.step !== undefined ? parseFloat(config.step) : 1;
        var rangeValue = value !== '' ? parseFloat(value) : min;
        var unit = config.unit || '';

        var inputHtml = '<div class="tb4-ss-range-wrapper">' +
            '<input type="range"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + rangeValue + '"' +
            ' min="' + min + '"' +
            ' max="' + max + '"' +
            ' step="' + step + '"' +
            ' class="tb4-ss-range-input">' +
            '<div class="tb4-ss-range-values">' +
            '<span class="tb4-ss-range-min">' + min + unit + '</span>' +
            '<span class="tb4-ss-range-current">' + rangeValue + unit + '</span>' +
            '<span class="tb4-ss-range-max">' + max + unit + '</span>' +
            '</div>' +
            '</div>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render upload/image field
     * @param {Object} config - Field configuration
     * @param {string} value - Current value (URL)
     * @returns {string} HTML string
     */
    renderUploadField: function(config, value) {
        var hasValue = value && value !== '';
        var accept = config.accept || 'image/*';

        var inputHtml = '<div class="tb4-ss-upload-wrapper">' +
            '<div class="tb4-ss-upload-preview' + (hasValue ? ' has-image' : '') + '"' +
            (hasValue ? ' style="background-image: url(\'' + this.escapeHtml(value) + '\')"' : '') + '>' +
            '<span class="tb4-ss-upload-placeholder">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
            '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>' +
            '<circle cx="8.5" cy="8.5" r="1.5"></circle>' +
            '<polyline points="21 15 16 10 5 21"></polyline>' +
            '</svg>' +
            '<span>Click to upload</span>' +
            '</span>' +
            '</div>' +
            '<input type="file"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '-file"' +
            ' class="tb4-ss-upload-input"' +
            ' accept="' + this.escapeHtml(accept) + '"' +
            ' data-upload-for="' + this.escapeHtml(config.name) + '">' +
            '<input type="hidden"' +
            ' id="tb4-field-' + this.escapeHtml(config.name) + '"' +
            ' name="' + this.escapeHtml(config.name) + '"' +
            ' value="' + this.escapeHtml(value) + '"' +
            ' class="tb4-ss-upload-value">' +
            '<div class="tb4-ss-upload-actions">' +
            '<button type="button" class="tb4-ss-upload-btn tb4-ss-upload-choose">Choose File</button>' +
            (hasValue ? '<button type="button" class="tb4-ss-upload-btn tb4-ss-upload-remove">Remove</button>' : '') +
            '</div>' +
            '</div>';

        return this.renderFieldWrapper(config, inputHtml);
    },

    /**
     * Render multiple fields from an array of configs
     * @param {Array} fields - Array of field configurations
     * @param {Object} values - Object with field values keyed by field name
     * @returns {string} Combined HTML string
     */

    // =========================================================================
    // Advanced Field Renderers (Etap 4)
    // =========================================================================

    /**
     * Render Typography field (complex font controls)
     */
    renderTypographyField: function(config, value) {
        var self = this;
        var data = (typeof value === 'object' && value) ? value : {};
        var prefix = config.name || 'typography';
        
        var fonts = [
            { value: '', label: 'Default' },
            { value: 'Inter, sans-serif', label: 'Inter' },
            { value: 'Arial, sans-serif', label: 'Arial' },
            { value: 'Georgia, serif', label: 'Georgia' },
            { value: 'Times New Roman, serif', label: 'Times New Roman' },
            { value: 'Verdana, sans-serif', label: 'Verdana' },
            { value: 'Roboto, sans-serif', label: 'Roboto' },
            { value: 'Open Sans, sans-serif', label: 'Open Sans' },
            { value: 'Lato, sans-serif', label: 'Lato' },
            { value: 'Montserrat, sans-serif', label: 'Montserrat' }
        ];
        
        var weights = [
            { value: '', label: 'Default' },
            { value: '300', label: 'Light' },
            { value: '400', label: 'Normal' },
            { value: '500', label: 'Medium' },
            { value: '600', label: 'Semi-Bold' },
            { value: '700', label: 'Bold' },
            { value: '800', label: 'Extra Bold' }
        ];
        
        var fontOptions = fonts.map(function(f) {
            var selected = (data.font_family === f.value) ? ' selected' : '';
            return '<option value="' + f.value + '"' + selected + '>' + f.label + '</option>';
        }).join('');
        
        var weightOptions = weights.map(function(w) {
            var selected = (data.font_weight === w.value) ? ' selected' : '';
            return '<option value="' + w.value + '"' + selected + '>' + w.label + '</option>';
        }).join('');
        
        var html = '<div class="tb4-ss-field tb4-ss-field-typography" data-field-name="' + prefix + '">';
        html += '<label class="tb4-ss-label">' + (config.label || 'Typography') + '</label>';
        
        // Font Family
        html += '<div class="tb4-ss-typo-row">';
        html += '<label class="tb4-ss-mini-label">Font Family</label>';
        html += '<select name="' + prefix + '_font_family" class="tb4-ss-select tb4-ss-typo-input">' + fontOptions + '</select>';
        html += '</div>';
        
        // Font Size + Weight row
        html += '<div class="tb4-ss-typo-row tb4-ss-typo-row-2col">';
        html += '<div class="tb4-ss-typo-col">';
        html += '<label class="tb4-ss-mini-label">Size</label>';
        html += '<input type="text" name="' + prefix + '_font_size" value="' + (data.font_size || '') + '" class="tb4-ss-input tb4-ss-typo-input" placeholder="16px">';
        html += '</div>';
        html += '<div class="tb4-ss-typo-col">';
        html += '<label class="tb4-ss-mini-label">Weight</label>';
        html += '<select name="' + prefix + '_font_weight" class="tb4-ss-select tb4-ss-typo-input">' + weightOptions + '</select>';
        html += '</div>';
        html += '</div>';
        
        // Line Height + Letter Spacing row
        html += '<div class="tb4-ss-typo-row tb4-ss-typo-row-2col">';
        html += '<div class="tb4-ss-typo-col">';
        html += '<label class="tb4-ss-mini-label">Line Height</label>';
        html += '<input type="text" name="' + prefix + '_line_height" value="' + (data.line_height || '') + '" class="tb4-ss-input tb4-ss-typo-input" placeholder="1.6">';
        html += '</div>';
        html += '<div class="tb4-ss-typo-col">';
        html += '<label class="tb4-ss-mini-label">Letter Spacing</label>';
        html += '<input type="text" name="' + prefix + '_letter_spacing" value="' + (data.letter_spacing || '') + '" class="tb4-ss-input tb4-ss-typo-input" placeholder="0px">';
        html += '</div>';
        html += '</div>';
        
        // Color - use same structure as renderColorField for consistent event handling
        html += '<div class="tb4-ss-typo-row">';
        html += '<label class="tb4-ss-mini-label">Text Color</label>';
        html += '<div class="tb4-ss-color-wrapper">';
        html += '<input type="color" name="' + prefix + '_color" value="' + (data.color || '#333333') + '" class="tb4-ss-color-input">';
        html += '<input type="text" value="' + (data.color || '#333333') + '" class="tb4-ss-input tb4-ss-color-text" placeholder="#333333" data-color-text-for="' + prefix + '_color">';
        html += '<span class="tb4-ss-color-preview" style="background-color: ' + (data.color || '#333333') + '"></span>';
        html += '</div>';
        html += '</div>';
        
        html += '</div>';
        return html;
    },

    /**
     * Render Spacing field (visual box model)
     */
    renderSpacingField: function(config, value) {
        var self = this;
        var data = (typeof value === 'object' && value) ? value : {};
        var prefix = config.name || 'spacing';
        
        var html = '<div class="tb4-ss-field tb4-ss-field-spacing" data-field-name="' + prefix + '">';
        html += '<label class="tb4-ss-label">' + (config.label || 'Spacing') + '</label>';
        
        // Visual box model
        html += '<div class="tb4-ss-spacing-box">';
        
        // Margin layer (outer)
        html += '<div class="tb4-ss-spacing-margin">';
        html += '<span class="tb4-ss-spacing-label-corner">MARGIN</span>';
        html += '<input type="text" name="' + prefix + '_margin_top" value="' + (data.margin_top || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-top" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_margin_right" value="' + (data.margin_right || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-right" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_margin_bottom" value="' + (data.margin_bottom || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-bottom" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_margin_left" value="' + (data.margin_left || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-left" placeholder="0">';
        
        // Padding layer (inner)
        html += '<div class="tb4-ss-spacing-padding">';
        html += '<span class="tb4-ss-spacing-label-corner">PADDING</span>';
        html += '<input type="text" name="' + prefix + '_padding_top" value="' + (data.padding_top || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-top" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_padding_right" value="' + (data.padding_right || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-right" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_padding_bottom" value="' + (data.padding_bottom || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-bottom" placeholder="0">';
        html += '<input type="text" name="' + prefix + '_padding_left" value="' + (data.padding_left || '') + '" class="tb4-ss-spacing-input tb4-ss-spacing-left" placeholder="0">';
        
        // Content area (center)
        html += '<div class="tb4-ss-spacing-content">CONTENT</div>';
        
        html += '</div>'; // padding
        html += '</div>'; // margin
        html += '</div>'; // spacing-box
        
        html += '</div>';
        return html;
    },

    /**
     * Render Border controls using TB4Builder's professional visual implementation
     * @param {Object} settings - Current module settings
     * @returns {string} HTML string
     */
    renderBorderVisual: function(settings) {
        settings = settings || {};
        var design = settings.design || settings;
        console.log('[TB4-SS] renderBorderVisual called, design:', design);
        console.log('[TB4-SS] TB4Builder available:', typeof TB4Builder !== 'undefined');
        console.log('[TB4-SS] renderBorderSettingsVisual is function:', typeof TB4Builder !== 'undefined' && typeof TB4Builder.renderBorderSettingsVisual === 'function');
        if (typeof TB4Builder !== 'undefined' && typeof TB4Builder.renderBorderSettingsVisual === 'function') {
            var html = TB4Builder.renderBorderSettingsVisual(design);
            console.log('[TB4-SS] Border HTML from TB4Builder:', html ? html.substring(0, 100) + '...' : 'EMPTY');
            return html;
        }
        // Fallback if TB4Builder not available
        console.log('[TB4-SS] Using fallback border field');
        return this.renderFallbackBorderField(design);
    },

    /**
     * Fallback border field if TB4Builder not loaded
     */
    renderFallbackBorderField: function(design) {
        design = design || {};
        var style = design.borderStyle || 'none';
        var color = design.borderColor || '#334155';
        var width = design.borderWidth || 0;
        var radius = design.borderRadius || 0;

        var widthVal = typeof width === 'object' ? (width.top || 0) : width;
        var radiusVal = typeof radius === 'object' ? (radius.topLeft || 0) : radius;

        return '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Border Style</label>' +
            '<select name="borderStyle" class="tb4-ss-select tb4-sidebar-input" data-group="design">' +
                '<option value="none"' + (style === 'none' ? ' selected' : '') + '>None</option>' +
                '<option value="solid"' + (style === 'solid' ? ' selected' : '') + '>Solid</option>' +
                '<option value="dashed"' + (style === 'dashed' ? ' selected' : '') + '>Dashed</option>' +
                '<option value="dotted"' + (style === 'dotted' ? ' selected' : '') + '>Dotted</option>' +
                '<option value="double"' + (style === 'double' ? ' selected' : '') + '>Double</option>' +
            '</select>' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Border Color</label>' +
            '<input type="color" name="borderColor" value="' + color + '" class="tb4-ss-color-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Border Width</label>' +
            '<input type="number" name="borderWidth" value="' + widthVal + '" min="0" max="50" class="tb4-ss-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Border Radius</label>' +
            '<input type="number" name="borderRadius" value="' + radiusVal + '" min="0" max="200" class="tb4-ss-input tb4-sidebar-input" data-group="design">' +
        '</div>';
    },

    /**
     * Render Box Shadow controls using TB4Builder's professional visual implementation
     * @param {Object} settings - Current module settings
     * @returns {string} HTML string
     */
    renderBoxShadowVisual: function(settings) {
        settings = settings || {};
        var design = settings.design || settings;
        console.log('[TB4-SS] renderBoxShadowVisual called, design:', design);
        console.log('[TB4-SS] TB4Builder available:', typeof TB4Builder !== 'undefined');
        console.log('[TB4-SS] renderBoxShadowSettingsVisual is function:', typeof TB4Builder !== 'undefined' && typeof TB4Builder.renderBoxShadowSettingsVisual === 'function');
        if (typeof TB4Builder !== 'undefined' && typeof TB4Builder.renderBoxShadowSettingsVisual === 'function') {
            var html = TB4Builder.renderBoxShadowSettingsVisual(design);
            console.log('[TB4-SS] Shadow HTML from TB4Builder:', html ? html.substring(0, 100) + '...' : 'EMPTY');
            return html;
        }
        // Fallback if TB4Builder not available
        console.log('[TB4-SS] Using fallback shadow field');
        return this.renderFallbackShadowField(design);
    },

    /**
     * Fallback shadow field if TB4Builder not loaded
     */
    renderFallbackShadowField: function(design) {
        design = design || {};
        var enabled = design.boxShadowEnabled || false;
        var h = design.boxShadowH !== undefined ? design.boxShadowH : 0;
        var v = design.boxShadowV !== undefined ? design.boxShadowV : 4;
        var blur = design.boxShadowBlur !== undefined ? design.boxShadowBlur : 10;
        var spread = design.boxShadowSpread !== undefined ? design.boxShadowSpread : 0;
        var color = design.boxShadowColor || 'rgba(0,0,0,0.15)';
        var inset = design.boxShadowInset || false;

        return '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label" style="display:flex;align-items:center;gap:8px">' +
                '<input type="checkbox" name="boxShadowEnabled" ' + (enabled ? 'checked' : '') + ' class="tb4-ss-checkbox-input tb4-sidebar-input" data-group="design">' +
                'Enable Box Shadow' +
            '</label>' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Horizontal Offset</label>' +
            '<input type="range" name="boxShadowH" value="' + h + '" min="-50" max="50" class="tb4-ss-range-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Vertical Offset</label>' +
            '<input type="range" name="boxShadowV" value="' + v + '" min="-50" max="50" class="tb4-ss-range-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Blur</label>' +
            '<input type="range" name="boxShadowBlur" value="' + blur + '" min="0" max="100" class="tb4-ss-range-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Spread</label>' +
            '<input type="range" name="boxShadowSpread" value="' + spread + '" min="-50" max="50" class="tb4-ss-range-input tb4-sidebar-input" data-group="design">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label">Shadow Color</label>' +
            '<input type="text" name="boxShadowColor" value="' + color + '" class="tb4-ss-input tb4-sidebar-input" data-group="design" placeholder="rgba(0,0,0,0.15)">' +
        '</div>' +
        '<div class="tb4-ss-field">' +
            '<label class="tb4-ss-mini-label" style="display:flex;align-items:center;gap:8px">' +
                '<input type="checkbox" name="boxShadowInset" ' + (inset ? 'checked' : '') + ' class="tb4-ss-checkbox-input tb4-sidebar-input" data-group="design">' +
                'Inset Shadow' +
            '</label>' +
        '</div>';
    },

    /**
     * Render Sizing controls
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderSizingField: function(config, values) {
        values = values || {};
        var prefix = config.name || 'sizing';

        var html = '<div class="tb4-ss-field tb4-ss-field-sizing">';

        // Width
        html += '<div class="tb4-ss-sizing-row">';
        html += '<label class="tb4-ss-mini-label">Width</label>';
        html += '<input type="text" name="' + prefix + '_width" value="' + (values.width || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="auto, 100%, 500px">';
        html += '</div>';

        // Min/Max Width
        html += '<div class="tb4-ss-sizing-row tb4-ss-sizing-row-2col">';
        html += '<div class="tb4-ss-sizing-col">';
        html += '<label class="tb4-ss-mini-label">Min Width</label>';
        html += '<input type="text" name="' + prefix + '_min_width" value="' + (values.minWidth || values.min_width || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="0">';
        html += '</div>';
        html += '<div class="tb4-ss-sizing-col">';
        html += '<label class="tb4-ss-mini-label">Max Width</label>';
        html += '<input type="text" name="' + prefix + '_max_width" value="' + (values.maxWidth || values.max_width || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="none">';
        html += '</div>';
        html += '</div>';

        // Height
        html += '<div class="tb4-ss-sizing-row">';
        html += '<label class="tb4-ss-mini-label">Height</label>';
        html += '<input type="text" name="' + prefix + '_height" value="' + (values.height || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="auto, 100%, 300px">';
        html += '</div>';

        // Min/Max Height
        html += '<div class="tb4-ss-sizing-row tb4-ss-sizing-row-2col">';
        html += '<div class="tb4-ss-sizing-col">';
        html += '<label class="tb4-ss-mini-label">Min Height</label>';
        html += '<input type="text" name="' + prefix + '_min_height" value="' + (values.minHeight || values.min_height || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="0">';
        html += '</div>';
        html += '<div class="tb4-ss-sizing-col">';
        html += '<label class="tb4-ss-mini-label">Max Height</label>';
        html += '<input type="text" name="' + prefix + '_max_height" value="' + (values.maxHeight || values.max_height || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="none">';
        html += '</div>';
        html += '</div>';

        // Overflow
        html += '<div class="tb4-ss-sizing-row">';
        html += '<label class="tb4-ss-mini-label">Overflow</label>';
        html += '<select name="' + prefix + '_overflow" class="tb4-ss-select tb4-sidebar-input">';
        var overflow = values.overflow || 'visible';
        html += '<option value="visible"' + (overflow === 'visible' ? ' selected' : '') + '>Visible</option>';
        html += '<option value="hidden"' + (overflow === 'hidden' ? ' selected' : '') + '>Hidden</option>';
        html += '<option value="scroll"' + (overflow === 'scroll' ? ' selected' : '') + '>Scroll</option>';
        html += '<option value="auto"' + (overflow === 'auto' ? ' selected' : '') + '>Auto</option>';
        html += '</select>';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Render Position controls
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderPositionField: function(config, values) {
        values = values || {};
        var prefix = config.name || 'position';

        var html = '<div class="tb4-ss-field tb4-ss-field-position">';

        // Position Type
        html += '<div class="tb4-ss-position-row">';
        html += '<label class="tb4-ss-mini-label">Position</label>';
        html += '<select name="' + prefix + '_type" class="tb4-ss-select tb4-sidebar-input">';
        var posType = values.position || values.positionType || 'relative';
        html += '<option value="relative"' + (posType === 'relative' ? ' selected' : '') + '>Relative</option>';
        html += '<option value="absolute"' + (posType === 'absolute' ? ' selected' : '') + '>Absolute</option>';
        html += '<option value="fixed"' + (posType === 'fixed' ? ' selected' : '') + '>Fixed</option>';
        html += '<option value="sticky"' + (posType === 'sticky' ? ' selected' : '') + '>Sticky</option>';
        html += '<option value="static"' + (posType === 'static' ? ' selected' : '') + '>Static</option>';
        html += '</select>';
        html += '</div>';

        // Offset controls (visual box)
        html += '<div class="tb4-ss-position-offsets">';
        html += '<label class="tb4-ss-mini-label">Offsets</label>';
        html += '<div class="tb4-ss-position-box">';
        html += '<input type="text" name="' + prefix + '_top" value="' + (values.top || values.positionTop || '') + '" class="tb4-ss-position-input tb4-ss-position-top tb4-sidebar-input" placeholder="auto">';
        html += '<input type="text" name="' + prefix + '_right" value="' + (values.right || values.positionRight || '') + '" class="tb4-ss-position-input tb4-ss-position-right tb4-sidebar-input" placeholder="auto">';
        html += '<input type="text" name="' + prefix + '_bottom" value="' + (values.bottom || values.positionBottom || '') + '" class="tb4-ss-position-input tb4-ss-position-bottom tb4-sidebar-input" placeholder="auto">';
        html += '<input type="text" name="' + prefix + '_left" value="' + (values.left || values.positionLeft || '') + '" class="tb4-ss-position-input tb4-ss-position-left tb4-sidebar-input" placeholder="auto">';
        html += '<div class="tb4-ss-position-center">POSITION</div>';
        html += '</div>';
        html += '</div>';

        // Z-Index
        html += '<div class="tb4-ss-position-row">';
        html += '<label class="tb4-ss-mini-label">Z-Index</label>';
        html += '<input type="number" name="' + prefix + '_z_index" value="' + (values.zIndex || values.z_index || '') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="auto">';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Render CSS Filters controls
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderFiltersField: function(config, values) {
        values = values || {};
        var prefix = config.name || 'filters';

        var html = '<div class="tb4-ss-field tb4-ss-field-filters">';

        // Blur
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Blur <span class="tb4-ss-filter-value">' + (values.blur || 0) + 'px</span></label>';
        html += '<input type="range" name="' + prefix + '_blur" value="' + (values.blur || 0) + '" min="0" max="20" step="1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Brightness
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Brightness <span class="tb4-ss-filter-value">' + (values.brightness || 100) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_brightness" value="' + (values.brightness || 100) + '" min="0" max="200" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Contrast
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Contrast <span class="tb4-ss-filter-value">' + (values.contrast || 100) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_contrast" value="' + (values.contrast || 100) + '" min="0" max="200" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Saturate
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Saturation <span class="tb4-ss-filter-value">' + (values.saturate || 100) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_saturate" value="' + (values.saturate || 100) + '" min="0" max="200" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Hue Rotate
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Hue Rotate <span class="tb4-ss-filter-value">' + (values.hueRotate || values.hue_rotate || 0) + '°</span></label>';
        html += '<input type="range" name="' + prefix + '_hue_rotate" value="' + (values.hueRotate || values.hue_rotate || 0) + '" min="0" max="360" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Grayscale
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Grayscale <span class="tb4-ss-filter-value">' + (values.grayscale || 0) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_grayscale" value="' + (values.grayscale || 0) + '" min="0" max="100" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Invert
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Invert <span class="tb4-ss-filter-value">' + (values.invert || 0) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_invert" value="' + (values.invert || 0) + '" min="0" max="100" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Sepia
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Sepia <span class="tb4-ss-filter-value">' + (values.sepia || 0) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_sepia" value="' + (values.sepia || 0) + '" min="0" max="100" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Opacity
        html += '<div class="tb4-ss-filter-row">';
        html += '<label class="tb4-ss-mini-label">Opacity <span class="tb4-ss-filter-value">' + (values.opacity || 100) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_opacity" value="' + (values.opacity || 100) + '" min="0" max="100" step="5" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Render Transform controls
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderTransformField: function(config, values) {
        values = values || {};
        var prefix = config.name || 'transform';

        var html = '<div class="tb4-ss-field tb4-ss-field-transform">';

        // Translate X/Y
        html += '<div class="tb4-ss-transform-section">';
        html += '<label class="tb4-ss-mini-label">Translate</label>';
        html += '<div class="tb4-ss-transform-row tb4-ss-transform-row-2col">';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">X</label>';
        html += '<input type="text" name="' + prefix + '_translate_x" value="' + (values.translateX || values.translate_x || '0') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="0px">';
        html += '</div>';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">Y</label>';
        html += '<input type="text" name="' + prefix + '_translate_y" value="' + (values.translateY || values.translate_y || '0') + '" class="tb4-ss-input tb4-sidebar-input" placeholder="0px">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Scale X/Y
        html += '<div class="tb4-ss-transform-section">';
        html += '<label class="tb4-ss-mini-label">Scale</label>';
        html += '<div class="tb4-ss-transform-row tb4-ss-transform-row-2col">';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">X <span class="tb4-ss-filter-value">' + (values.scaleX || values.scale_x || 1) + '</span></label>';
        html += '<input type="range" name="' + prefix + '_scale_x" value="' + (values.scaleX || values.scale_x || 1) + '" min="0" max="3" step="0.1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">Y <span class="tb4-ss-filter-value">' + (values.scaleY || values.scale_y || 1) + '</span></label>';
        html += '<input type="range" name="' + prefix + '_scale_y" value="' + (values.scaleY || values.scale_y || 1) + '" min="0" max="3" step="0.1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Rotate
        html += '<div class="tb4-ss-transform-section">';
        html += '<label class="tb4-ss-mini-label">Rotate <span class="tb4-ss-filter-value">' + (values.rotate || 0) + '°</span></label>';
        html += '<input type="range" name="' + prefix + '_rotate" value="' + (values.rotate || 0) + '" min="-180" max="180" step="1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Skew X/Y
        html += '<div class="tb4-ss-transform-section">';
        html += '<label class="tb4-ss-mini-label">Skew</label>';
        html += '<div class="tb4-ss-transform-row tb4-ss-transform-row-2col">';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">X <span class="tb4-ss-filter-value">' + (values.skewX || values.skew_x || 0) + '°</span></label>';
        html += '<input type="range" name="' + prefix + '_skew_x" value="' + (values.skewX || values.skew_x || 0) + '" min="-45" max="45" step="1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';
        html += '<div class="tb4-ss-transform-col">';
        html += '<label class="tb4-ss-micro-label">Y <span class="tb4-ss-filter-value">' + (values.skewY || values.skew_y || 0) + '°</span></label>';
        html += '<input type="range" name="' + prefix + '_skew_y" value="' + (values.skewY || values.skew_y || 0) + '" min="-45" max="45" step="1" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Transform Origin
        html += '<div class="tb4-ss-transform-section">';
        html += '<label class="tb4-ss-mini-label">Transform Origin</label>';
        html += '<select name="' + prefix + '_origin" class="tb4-ss-select tb4-sidebar-input">';
        var origin = values.transformOrigin || values.transform_origin || 'center center';
        var origins = [
            { value: 'center center', label: 'Center' },
            { value: 'top left', label: 'Top Left' },
            { value: 'top center', label: 'Top Center' },
            { value: 'top right', label: 'Top Right' },
            { value: 'center left', label: 'Center Left' },
            { value: 'center right', label: 'Center Right' },
            { value: 'bottom left', label: 'Bottom Left' },
            { value: 'bottom center', label: 'Bottom Center' },
            { value: 'bottom right', label: 'Bottom Right' }
        ];
        origins.forEach(function(o) {
            html += '<option value="' + o.value + '"' + (origin === o.value ? ' selected' : '') + '>' + o.label + '</option>';
        });
        html += '</select>';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Render Animation controls (for Design tab)
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderAnimationField: function(config, values) {
        values = values || {};
        var prefix = config.name || 'animation';

        var html = '<div class="tb4-ss-field tb4-ss-field-animation">';

        // Animation Type
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label">Animation Type</label>';
        html += '<select name="' + prefix + '_type" class="tb4-ss-select tb4-sidebar-input">';
        var animType = values.animationType || values.animation_type || values.type || '';
        var types = [
            { value: '', label: 'None' },
            { value: 'fade', label: 'Fade In' },
            { value: 'slide-up', label: 'Slide Up' },
            { value: 'slide-down', label: 'Slide Down' },
            { value: 'slide-left', label: 'Slide Left' },
            { value: 'slide-right', label: 'Slide Right' },
            { value: 'zoom-in', label: 'Zoom In' },
            { value: 'zoom-out', label: 'Zoom Out' },
            { value: 'flip', label: 'Flip' },
            { value: 'bounce', label: 'Bounce' },
            { value: 'rotate', label: 'Rotate In' },
            { value: 'roll', label: 'Roll In' }
        ];
        types.forEach(function(t) {
            html += '<option value="' + t.value + '"' + (animType === t.value ? ' selected' : '') + '>' + t.label + '</option>';
        });
        html += '</select>';
        html += '</div>';

        // Duration
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label">Duration <span class="tb4-ss-filter-value">' + (values.animationDuration || values.animation_duration || values.duration || 500) + 'ms</span></label>';
        html += '<input type="range" name="' + prefix + '_duration" value="' + (values.animationDuration || values.animation_duration || values.duration || 500) + '" min="100" max="2000" step="100" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Delay
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label">Delay <span class="tb4-ss-filter-value">' + (values.animationDelay || values.animation_delay || values.delay || 0) + 'ms</span></label>';
        html += '<input type="range" name="' + prefix + '_delay" value="' + (values.animationDelay || values.animation_delay || values.delay || 0) + '" min="0" max="2000" step="100" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Easing
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label">Easing</label>';
        html += '<select name="' + prefix + '_easing" class="tb4-ss-select tb4-sidebar-input">';
        var easing = values.animationEasing || values.animation_easing || values.easing || 'ease';
        var easings = [
            { value: 'ease', label: 'Ease' },
            { value: 'ease-in', label: 'Ease In' },
            { value: 'ease-out', label: 'Ease Out' },
            { value: 'ease-in-out', label: 'Ease In Out' },
            { value: 'linear', label: 'Linear' },
            { value: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)', label: 'Smooth' },
            { value: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)', label: 'Bounce' }
        ];
        easings.forEach(function(e) {
            html += '<option value="' + e.value + '"' + (easing === e.value ? ' selected' : '') + '>' + e.label + '</option>';
        });
        html += '</select>';
        html += '</div>';

        // Intensity
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label">Intensity <span class="tb4-ss-filter-value">' + (values.animationIntensity || values.animation_intensity || values.intensity || 50) + '%</span></label>';
        html += '<input type="range" name="' + prefix + '_intensity" value="' + (values.animationIntensity || values.animation_intensity || values.intensity || 50) + '" min="0" max="100" step="10" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';

        // Repeat
        html += '<div class="tb4-ss-animation-row">';
        html += '<label class="tb4-ss-mini-label tb4-ss-checkbox-inline">';
        html += '<input type="checkbox" name="' + prefix + '_repeat" ' + (values.animationRepeat || values.animation_repeat || values.repeat ? 'checked' : '') + ' class="tb4-ss-checkbox-input tb4-sidebar-input">';
        html += ' Repeat Animation';
        html += '</label>';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Render enhanced Background controls with tabs (color/gradient/image)
     * @param {Object} config - Field configuration
     * @param {Object} values - Current values
     * @returns {string} HTML string
     */
    renderBackgroundFieldAdvanced: function(config, values) {
        values = values || {};
        var prefix = config.name || 'background';

        var bgType = values.backgroundType || values.background_type || 'color';

        var html = '<div class="tb4-ss-field tb4-ss-field-background">';

        // Tabs for background type
        html += '<div class="tb4-ss-bg-tabs">';
        html += '<button type="button" class="tb4-ss-bg-tab' + (bgType === 'color' ? ' active' : '') + '" data-bg-type="color">Color</button>';
        html += '<button type="button" class="tb4-ss-bg-tab' + (bgType === 'gradient' ? ' active' : '') + '" data-bg-type="gradient">Gradient</button>';
        html += '<button type="button" class="tb4-ss-bg-tab' + (bgType === 'image' ? ' active' : '') + '" data-bg-type="image">Image</button>';
        html += '</div>';
        html += '<input type="hidden" name="' + prefix + '_type" value="' + bgType + '" class="tb4-ss-bg-type-input">';

        // Color panel
        html += '<div class="tb4-ss-bg-panel' + (bgType === 'color' ? ' active' : '') + '" data-bg-panel="color">';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Background Color</label>';
        html += '<div class="tb4-ss-color-wrapper">';
        html += '<input type="color" name="' + prefix + '_color" value="' + (values.backgroundColor || values.background_color || '#ffffff') + '" class="tb4-ss-color-input">';
        html += '<input type="text" value="' + (values.backgroundColor || values.background_color || '#ffffff') + '" class="tb4-ss-input tb4-ss-color-text" placeholder="#ffffff">';
        html += '<span class="tb4-ss-color-preview" style="background-color: ' + (values.backgroundColor || values.background_color || '#ffffff') + '"></span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Gradient panel
        html += '<div class="tb4-ss-bg-panel' + (bgType === 'gradient' ? ' active' : '') + '" data-bg-panel="gradient">';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Gradient Type</label>';
        html += '<select name="' + prefix + '_gradient_type" class="tb4-ss-select tb4-sidebar-input">';
        var gradType = values.gradientType || values.gradient_type || 'linear';
        html += '<option value="linear"' + (gradType === 'linear' ? ' selected' : '') + '>Linear</option>';
        html += '<option value="radial"' + (gradType === 'radial' ? ' selected' : '') + '>Radial</option>';
        html += '</select>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Direction <span class="tb4-ss-filter-value">' + (values.gradientDirection || values.gradient_direction || 180) + '°</span></label>';
        html += '<input type="range" name="' + prefix + '_gradient_direction" value="' + (values.gradientDirection || values.gradient_direction || 180) + '" min="0" max="360" step="15" class="tb4-ss-range-input tb4-sidebar-input">';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Start Color</label>';
        html += '<div class="tb4-ss-color-wrapper">';
        html += '<input type="color" name="' + prefix + '_gradient_start" value="' + (values.gradientStartColor || values.gradient_start_color || '#ffffff') + '" class="tb4-ss-color-input">';
        html += '<input type="text" value="' + (values.gradientStartColor || values.gradient_start_color || '#ffffff') + '" class="tb4-ss-input tb4-ss-color-text">';
        html += '<span class="tb4-ss-color-preview" style="background-color: ' + (values.gradientStartColor || values.gradient_start_color || '#ffffff') + '"></span>';
        html += '</div>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">End Color</label>';
        html += '<div class="tb4-ss-color-wrapper">';
        html += '<input type="color" name="' + prefix + '_gradient_end" value="' + (values.gradientEndColor || values.gradient_end_color || '#000000') + '" class="tb4-ss-color-input">';
        html += '<input type="text" value="' + (values.gradientEndColor || values.gradient_end_color || '#000000') + '" class="tb4-ss-input tb4-ss-color-text">';
        html += '<span class="tb4-ss-color-preview" style="background-color: ' + (values.gradientEndColor || values.gradient_end_color || '#000000') + '"></span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Image panel
        html += '<div class="tb4-ss-bg-panel' + (bgType === 'image' ? ' active' : '') + '" data-bg-panel="image">';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Background Image</label>';
        html += '<div class="tb4-ss-upload-wrapper">';
        html += '<input type="hidden" name="' + prefix + '_image" value="' + (values.backgroundImage || values.background_image || '') + '" class="tb4-ss-upload-value">';
        html += '<div class="tb4-ss-upload-preview"' + (values.backgroundImage || values.background_image ? ' style="background-image: url(' + (values.backgroundImage || values.background_image) + ')"' : '') + '>';
        html += '<span class="tb4-ss-upload-placeholder">' + (values.backgroundImage || values.background_image ? 'Change Image' : 'Select Image') + '</span>';
        html += '</div>';
        html += '<input type="file" accept="image/*" class="tb4-ss-upload-input">';
        html += '</div>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Size</label>';
        html += '<select name="' + prefix + '_size" class="tb4-ss-select tb4-sidebar-input">';
        var bgSize = values.backgroundSize || values.background_size || 'cover';
        html += '<option value="cover"' + (bgSize === 'cover' ? ' selected' : '') + '>Cover</option>';
        html += '<option value="contain"' + (bgSize === 'contain' ? ' selected' : '') + '>Contain</option>';
        html += '<option value="auto"' + (bgSize === 'auto' ? ' selected' : '') + '>Auto</option>';
        html += '<option value="100% 100%"' + (bgSize === '100% 100%' ? ' selected' : '') + '>Stretch</option>';
        html += '</select>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Position</label>';
        html += '<select name="' + prefix + '_position" class="tb4-ss-select tb4-sidebar-input">';
        var bgPos = values.backgroundPosition || values.background_position || 'center center';
        var positions = ['center center', 'top left', 'top center', 'top right', 'center left', 'center right', 'bottom left', 'bottom center', 'bottom right'];
        positions.forEach(function(p) {
            html += '<option value="' + p + '"' + (bgPos === p ? ' selected' : '') + '>' + p.replace(' ', ' / ') + '</option>';
        });
        html += '</select>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label">Repeat</label>';
        html += '<select name="' + prefix + '_repeat" class="tb4-ss-select tb4-sidebar-input">';
        var bgRepeat = values.backgroundRepeat || values.background_repeat || 'no-repeat';
        html += '<option value="no-repeat"' + (bgRepeat === 'no-repeat' ? ' selected' : '') + '>No Repeat</option>';
        html += '<option value="repeat"' + (bgRepeat === 'repeat' ? ' selected' : '') + '>Repeat</option>';
        html += '<option value="repeat-x"' + (bgRepeat === 'repeat-x' ? ' selected' : '') + '>Repeat X</option>';
        html += '<option value="repeat-y"' + (bgRepeat === 'repeat-y' ? ' selected' : '') + '>Repeat Y</option>';
        html += '</select>';
        html += '</div>';
        html += '<div class="tb4-ss-bg-row">';
        html += '<label class="tb4-ss-mini-label tb4-ss-checkbox-inline">';
        html += '<input type="checkbox" name="' + prefix + '_parallax" ' + (values.backgroundParallax || values.background_parallax ? 'checked' : '') + ' class="tb4-ss-checkbox-input tb4-sidebar-input">';
        html += ' Enable Parallax Effect';
        html += '</label>';
        html += '</div>';
        html += '</div>';

        html += '</div>';
        return html;
    },

    /**
     * Initialize background tabs event handlers
     * @param {HTMLElement} container - Container element
     */
    initBackgroundTabs: function(container) {
        var self = this;
        container.querySelectorAll('.tb4-ss-bg-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var bgType = this.getAttribute('data-bg-type');
                var wrapper = this.closest('.tb4-ss-field-background');

                // Update active tab
                wrapper.querySelectorAll('.tb4-ss-bg-tab').forEach(function(t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                // Update hidden input
                wrapper.querySelector('.tb4-ss-bg-type-input').value = bgType;

                // Show/hide panels
                wrapper.querySelectorAll('.tb4-ss-bg-panel').forEach(function(panel) {
                    panel.classList.remove('active');
                });
                wrapper.querySelector('[data-bg-panel="' + bgType + '"]').classList.add('active');

                // Dispatch change event
                self.dispatch('tb4-ss:fieldChange', {
                    name: 'background_type',
                    value: bgType,
                    type: 'select'
                });
            });
        });
    },

    /**
     * Initialize range slider value update handlers
     * @param {HTMLElement} container - Container element
     */
    initRangeValueDisplays: function(container) {
        var self = this;
        container.querySelectorAll('.tb4-ss-range-input').forEach(function(input) {
            // Skip if already has wrapper with value display
            if (input.closest('.tb4-ss-range-wrapper')) return;

            input.addEventListener('input', function() {
                var row = this.closest('.tb4-ss-filter-row, .tb4-ss-transform-section, .tb4-ss-animation-row, .tb4-ss-bg-row, .tb4-ss-transform-col');
                if (row) {
                    var valueDisplay = row.querySelector('.tb4-ss-filter-value');
                    if (valueDisplay) {
                        var unit = '';
                        var name = this.name;
                        if (name.indexOf('blur') !== -1) unit = 'px';
                        else if (name.indexOf('brightness') !== -1 || name.indexOf('contrast') !== -1 || name.indexOf('saturate') !== -1 || name.indexOf('grayscale') !== -1 || name.indexOf('invert') !== -1 || name.indexOf('sepia') !== -1 || name.indexOf('opacity') !== -1 || name.indexOf('intensity') !== -1) unit = '%';
                        else if (name.indexOf('hue') !== -1 || name.indexOf('rotate') !== -1 || name.indexOf('skew') !== -1 || name.indexOf('direction') !== -1) unit = '°';
                        else if (name.indexOf('duration') !== -1 || name.indexOf('delay') !== -1) unit = 'ms';
                        valueDisplay.textContent = this.value + unit;
                    }
                }
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.value,
                    type: 'range'
                });
            });
        });
    },

    /**
     * Initialize TB4Builder controls if available
     * Re-initializes icons and sets up event delegation for professional controls
     */
    initBuilderControls: function() {
        // Re-initialize Lucide icons for new controls
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Shadow preview update listener
        var shadowControls = document.querySelector('[data-shadow-controls]');
        if (shadowControls) {
            var self = this;
            shadowControls.addEventListener('input', function() {
                if (typeof TB4Builder !== 'undefined' && typeof TB4Builder.updateShadowPreview === 'function') {
                    TB4Builder.updateShadowPreview();
                }
            });
            shadowControls.addEventListener('change', function() {
                if (typeof TB4Builder !== 'undefined' && typeof TB4Builder.updateShadowPreview === 'function') {
                    TB4Builder.updateShadowPreview();
                }
            });
        }

        // Enable/disable toggle listener for shadow
        var enableToggle = document.querySelector('[name="boxShadowEnabled"]');
        if (enableToggle) {
            enableToggle.addEventListener('change', function() {
                var controls = document.querySelector('[data-shadow-controls]');
                if (controls) {
                    controls.classList.toggle('tb4-disabled', !this.checked);
                }
                if (typeof TB4Builder !== 'undefined' && typeof TB4Builder.updateShadowPreview === 'function') {
                    TB4Builder.updateShadowPreview();
                }
            });
        }

        // Border color sync
        var borderColor = document.querySelector('[name="borderColor"]');
        var borderColorText = document.querySelector('[name="borderColorText"]');
        if (borderColor && borderColorText) {
            borderColor.addEventListener('input', function() {
                borderColorText.value = this.value;
            });
            borderColorText.addEventListener('input', function() {
                if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(this.value)) {
                    borderColor.value = this.value;
                }
            });
        }
    },

    /**
     * Render WYSIWYG field (rich text editor)
     */
    renderWysiwygField: function(config, value) {
        var self = this;
        var actualValue = value || '';
        var prefix = config.name || 'content';
        
        var html = '<div class="tb4-ss-field tb4-ss-field-wysiwyg" data-field-name="' + prefix + '">';
        html += '<label class="tb4-ss-label">' + (config.label || 'Content') + '</label>';
        
        // Toolbar
        html += '<div class="tb4-ss-wysiwyg-toolbar">';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="bold" title="Bold"><b>B</b></button>';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="italic" title="Italic"><i>I</i></button>';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="underline" title="Underline"><u>U</u></button>';
        html += '<span class="tb4-ss-wysiwyg-sep"></span>';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="insertUnorderedList" title="Bullet List">•</button>';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="insertOrderedList" title="Numbered List">1.</button>';
        html += '<span class="tb4-ss-wysiwyg-sep"></span>';
        html += '<button type="button" class="tb4-ss-wysiwyg-btn" data-cmd="createLink" title="Add Link">🔗</button>';
        html += '</div>';
        
        // Editable area
        html += '<div class="tb4-ss-wysiwyg-editor" contenteditable="true" data-name="' + prefix + '">' + actualValue + '</div>';
        
        // Hidden input for form submission
        html += '<input type="hidden" name="' + prefix + '" value="' + this.escapeHtml(actualValue) + '">';
        
        html += '</div>';
        return html;
    },

        renderFields: function(fields, values) {
        var self = this;
        values = values || {};

        if (!Array.isArray(fields)) {
            console.warn('[TB4] renderFields: Expected array of field configs');
            return '';
        }

        return fields.map(function(field) {
            var value = values[field.name];
            return self.renderField(field, value);
        }).join('');
    },

    /**
     * Initialize field event handlers after rendering
     * Call this after inserting rendered fields into the DOM
     * @param {HTMLElement} container - Container element with rendered fields
     */
    initFieldHandlers: function(container) {
        if (!container) return;
        var self = this;

        // Yes/No toggle handlers
        container.querySelectorAll('.tb4-ss-yesno-wrapper').forEach(function(wrapper) {
            var hiddenInput = wrapper.querySelector('.tb4-ss-yesno-value');
            var buttons = wrapper.querySelectorAll('.tb4-ss-yesno-btn');

            buttons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var value = this.getAttribute('data-value');
                    hiddenInput.value = value;
                    buttons.forEach(function(b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    self.dispatch('tb4-ss:fieldChange', {
                        name: hiddenInput.name,
                        value: value,
                        type: 'yes_no'
                    });
                }.bind(btn));
            });
        });

        // Color field sync handlers - with null checks for standalone color inputs
        container.querySelectorAll('.tb4-ss-color-wrapper').forEach(function(wrapper) {
            var colorInput = wrapper.querySelector('.tb4-ss-color-input');
            var textInput = wrapper.querySelector('.tb4-ss-color-text');
            var preview = wrapper.querySelector('.tb4-ss-color-preview');

            // Skip if colorInput not found
            if (!colorInput) return;

            colorInput.addEventListener('input', function() {
                // Update text input if exists
                if (textInput) textInput.value = this.value;
                // Update preview if exists
                if (preview) preview.style.backgroundColor = this.value;
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.value,
                    type: 'color'
                });
            });

            // Only add text input handler if it exists
            if (textInput) {
                textInput.addEventListener('input', function() {
                    var val = this.value;
                    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(val)) {
                        colorInput.value = val;
                        if (preview) preview.style.backgroundColor = val;
                        self.dispatch('tb4-ss:fieldChange', {
                            name: colorInput.name,
                            value: val,
                            type: 'color'
                        });
                    }
                });
            }
        });

        // Handle standalone color inputs (without wrapper, like in fallback fields)
        container.querySelectorAll('input[type="color"].tb4-sidebar-input').forEach(function(colorInput) {
            // Skip if already handled via wrapper
            if (colorInput.closest('.tb4-ss-color-wrapper')) return;
            
            colorInput.addEventListener('input', function() {
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.value,
                    type: 'color'
                });
            });
        });

        // Range slider value display
        container.querySelectorAll('.tb4-ss-range-wrapper').forEach(function(wrapper) {
            var rangeInput = wrapper.querySelector('.tb4-ss-range-input');
            var currentDisplay = wrapper.querySelector('.tb4-ss-range-current');

            rangeInput.addEventListener('input', function() {
                var unit = currentDisplay.textContent.replace(/[\d.-]/g, '');
                currentDisplay.textContent = this.value + unit;
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.value,
                    type: 'range'
                });
            });
        });

        // Upload field handlers
        container.querySelectorAll('.tb4-ss-upload-wrapper').forEach(function(wrapper) {
            var fileInput = wrapper.querySelector('.tb4-ss-upload-input');
            var hiddenInput = wrapper.querySelector('.tb4-ss-upload-value');
            var preview = wrapper.querySelector('.tb4-ss-upload-preview');
            var chooseBtn = wrapper.querySelector('.tb4-ss-upload-choose');
            var removeBtn = wrapper.querySelector('.tb4-ss-upload-remove');

            // Click preview or choose button to trigger file input
            preview.addEventListener('click', function() { fileInput.click(); });
            if (chooseBtn) {
                chooseBtn.addEventListener('click', function() { fileInput.click(); });
            }

            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.style.backgroundImage = 'url(' + e.target.result + ')';
                        preview.classList.add('has-image');
                        hiddenInput.value = e.target.result;

                        // Add remove button if not exists
                        if (!removeBtn) {
                            var actions = wrapper.querySelector('.tb4-ss-upload-actions');
                            removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'tb4-ss-upload-btn tb4-ss-upload-remove';
                            removeBtn.textContent = 'Remove';
                            actions.appendChild(removeBtn);

                            removeBtn.addEventListener('click', function() {
                                preview.style.backgroundImage = '';
                                preview.classList.remove('has-image');
                                hiddenInput.value = '';
                                fileInput.value = '';
                                this.remove();
                                self.dispatch('tb4-ss:fieldChange', {
                                    name: hiddenInput.name,
                                    value: '',
                                    type: 'upload'
                                });
                            });
                        }

                        self.dispatch('tb4-ss:fieldChange', {
                            name: hiddenInput.name,
                            value: e.target.result,
                            type: 'upload'
                        });
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Remove button handler
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    preview.style.backgroundImage = '';
                    preview.classList.remove('has-image');
                    hiddenInput.value = '';
                    fileInput.value = '';
                    this.remove();
                    self.dispatch('tb4-ss:fieldChange', {
                        name: hiddenInput.name,
                        value: '',
                        type: 'upload'
                    });
                });
            }
        });

        // Generic input change handlers
        container.querySelectorAll('.tb4-ss-input').forEach(function(input) {
            if (input.classList.contains('tb4-ss-color-text')) return; // Already handled

            input.addEventListener('input', function() {
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.value,
                    type: this.type || 'text'
                });
            });
        });

        // Checkbox handlers
        container.querySelectorAll('.tb4-ss-checkbox-input').forEach(function(input) {
            input.addEventListener('change', function() {
                self.dispatch('tb4-ss:fieldChange', {
                    name: this.name,
                    value: this.checked,
                    type: 'checkbox'
                });
            });
        });

        // Box Shadow slider handlers - update preview and value display
        container.querySelectorAll('.tb4-ss-shadow-slider').forEach(function(slider) {
            slider.addEventListener('input', function() {
                var valueDisplay = this.nextElementSibling;
                if (valueDisplay && valueDisplay.classList.contains('tb4-ss-shadow-value')) {
                    valueDisplay.textContent = this.value + 'px';
                }
                self.updateShadowPreview(this.getAttribute('data-preview'));
            });
        });

        // Box Shadow color handler
        container.querySelectorAll('.tb4-ss-shadow-color').forEach(function(colorInput) {
            colorInput.addEventListener('input', function() {
                var hexInput = this.nextElementSibling;
                if (hexInput) hexInput.value = this.value;
                self.updateShadowPreview(this.getAttribute('data-preview'));
            });
        });

        // Box Shadow inset handler
        container.querySelectorAll('.tb4-ss-shadow-inset').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                self.updateShadowPreview(this.getAttribute('data-preview'));
            });
        });

        // Color hex sync (for typography and other fields)
        container.querySelectorAll('.tb4-ss-color-hex').forEach(function(hexInput) {
            var wrapper = hexInput.closest('.tb4-ss-color-wrapper');
            if (!wrapper) return;
            var colorInput = wrapper.querySelector('.tb4-ss-color-input');
            
            hexInput.addEventListener('input', function() {
                var val = this.value;
                if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(val) && colorInput) {
                    colorInput.value = val;
                }
            });
            
            if (colorInput) {
                colorInput.addEventListener('input', function() {
                    hexInput.value = this.value;
                });
            }
        });

        // WYSIWYG toolbar handlers
        container.querySelectorAll('.tb4-ss-wysiwyg-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var cmd = this.getAttribute('data-cmd');
                if (cmd === 'createLink') {
                    var url = prompt('Enter URL:');
                    if (url) document.execCommand(cmd, false, url);
                } else {
                    document.execCommand(cmd, false, null);
                }
                this.classList.toggle('active', document.queryCommandState(cmd));
            });
        });

        // WYSIWYG sync to hidden input
        container.querySelectorAll('.tb4-ss-wysiwyg-editor').forEach(function(editor) {
            var hiddenInput = editor.nextElementSibling;
            editor.addEventListener('input', function() {
                if (hiddenInput) hiddenInput.value = this.innerHTML;
            });
            editor.addEventListener('blur', function() {
                if (hiddenInput) hiddenInput.value = this.innerHTML;
            });
        });
    },

    /**
     * Update box shadow preview based on current slider values
     */
    updateShadowPreview: function(previewId) {
        if (!previewId) return;
        var container = document.getElementById('tb4-settings-sidebar');
        if (!container) return;
        
        var prefix = previewId;
        var x = container.querySelector('[name="' + prefix + '_x"]');
        var y = container.querySelector('[name="' + prefix + '_y"]');
        var blur = container.querySelector('[name="' + prefix + '_blur"]');
        var spread = container.querySelector('[name="' + prefix + '_spread"]');
        var color = container.querySelector('[name="' + prefix + '_color"]');
        var inset = container.querySelector('[name="' + prefix + '_inset"]');
        var preview = container.querySelector('#' + prefix + '-preview');
        
        if (!preview) return;
        
        var xVal = x ? x.value : 0;
        var yVal = y ? y.value : 0;
        var blurVal = blur ? blur.value : 10;
        var spreadVal = spread ? spread.value : 0;
        var colorVal = color ? color.value : '#000000';
        var insetVal = inset && inset.checked ? 'inset ' : '';
        
        preview.style.boxShadow = insetVal + xVal + 'px ' + yVal + 'px ' + blurVal + 'px ' + spreadVal + 'px ' + colorVal;
    },

    // =========================================================================
    // Field Population System
    // =========================================================================

    /**
     * Populate fields based on module configuration and data
     * Called when sidebar opens for a module
     */
    populateFields: function() {
        var self = this;
        var moduleType = this.state.moduleType;
        var moduleData = this.state.moduleData || {};

        // Normalize module type (remove tb4_ prefix if present)
        var normalizedType = (moduleType || '').replace(/^tb4_/, '');

        // Get module config from TB4Builder
        var moduleConfig = this.getModuleConfig(normalizedType);

        console.log('[TB4-SS] populateFields:', {
            moduleType: moduleType,
            normalizedType: normalizedType,
            hasConfig: !!moduleConfig,
            moduleData: moduleData
        });

        // Populate Content tab
        this.populateContentTab(moduleConfig, moduleData);

        // Populate Design tab
        this.populateDesignTab(moduleConfig, moduleData);

        // Populate Advanced tab
        this.populateAdvancedTab(moduleConfig, moduleData);
    },

    /**
     * Get module configuration from TB4Builder or window.TB4
     * Tries both with and without tb4_ prefix for compatibility
     * @param {string} moduleType - The module type (with or without prefix)
     * @returns {Object} Module configuration or empty object
     */
    getModuleConfig: function(moduleType) {
        var config = null;
        var withPrefix = moduleType.indexOf('tb4_') === 0 ? moduleType : 'tb4_' + moduleType;
        var withoutPrefix = moduleType.replace(/^tb4_/, '');
        
        // Try TB4Builder first (both variants)
        if (typeof TB4Builder !== 'undefined' && TB4Builder.config && TB4Builder.config.modules) {
            config = TB4Builder.config.modules[moduleType] ||
                     TB4Builder.config.modules[withPrefix] ||
                     TB4Builder.config.modules[withoutPrefix];
            if (config) return config;
        }
        
        // Try window.TB4.modules (both variants)
        if (typeof window.TB4 !== 'undefined' && window.TB4.modules) {
            config = window.TB4.modules[moduleType] ||
                     window.TB4.modules[withPrefix] ||
                     window.TB4.modules[withoutPrefix];
            if (config) return config;
        }
        
        console.warn('[TB4-SS] Module config not found for:', moduleType, 'tried:', withPrefix, withoutPrefix);
        return {};
    },

    /**
     * Populate the Content tab with module-specific fields
     * @param {Object} moduleConfig - Module configuration
     * @param {Object} moduleData - Current module data
     */
    populateContentTab: function(moduleConfig, moduleData) {
        var self = this;
        var fieldsObj = moduleConfig.fields || {};
        var content = moduleData.content || {};

        // Convert fields object to array with name property
        var fields = this.convertFieldsToArray(fieldsObj);

        // Group fields by their tab/group assignment
        var fieldGroups = this.getFieldsForTab('content', fields, moduleConfig);

        // Populate Text Content toggle group
        var textFields = fieldGroups.text || [];
        if (textFields.length > 0) {
            var textHtml = this.renderFields(textFields, content);
            this.setToggleGroupContent('content-text', textHtml);
            var textContainer = this.getToggleGroupContent('content-text');
            if (textContainer) {
                this.initFieldHandlers(textContainer);
            }
        } else {
            this.setToggleGroupContent('content-text', '<p class="tb4-ss-empty-msg">No text fields available.</p>');
        }

        // Populate Media toggle group
        var mediaFields = fieldGroups.media || [];
        if (mediaFields.length > 0) {
            var mediaHtml = this.renderFields(mediaFields, content);
            this.setToggleGroupContent('content-media', mediaHtml);
            var mediaContainer = this.getToggleGroupContent('content-media');
            if (mediaContainer) {
                this.initFieldHandlers(mediaContainer);
            }
        } else {
            this.setToggleGroupContent('content-media', '<p class="tb4-ss-empty-msg">No media fields available.</p>');
        }

        // Populate Link Settings toggle group
        var linkFields = fieldGroups.link || [];
        if (linkFields.length > 0) {
            var linkHtml = this.renderFields(linkFields, content);
            this.setToggleGroupContent('content-link', linkHtml);
            var linkContainer = this.getToggleGroupContent('content-link');
            if (linkContainer) {
                this.initFieldHandlers(linkContainer);
            }
        } else {
            this.setToggleGroupContent('content-link', '<p class="tb4-ss-empty-msg">No link fields available.</p>');
        }
    },

    /**
     * Populate the Design tab with common styling fields
     * @param {Object} moduleConfig - Module configuration
     * @param {Object} moduleData - Current module data
     */
    populateDesignTab: function(moduleConfig, moduleData) {
        var self = this;
        var settings = moduleData.settings || {};
        var design = moduleData.design || {};

        // Merge settings and design for value lookup
        var values = Object.assign({}, settings, design);

        // Typography - advanced field
        var typographyData = {
            font_family: values.fontFamily || values.font_family || '',
            font_size: values.fontSize || values.font_size || '',
            font_weight: values.fontWeight || values.font_weight || '',
            line_height: values.lineHeight || values.line_height || '',
            letter_spacing: values.letterSpacing || values.letter_spacing || '',
            color: values.textColor || values.text_color || '#333333'
        };
        var typographyHtml = this.renderTypographyField({ name: 'typography', label: 'Text Typography' }, typographyData);
        this.setToggleGroupContent('design-typography', typographyHtml);
        var typographyContainer = this.getToggleGroupContent('design-typography');
        if (typographyContainer) this.initFieldHandlers(typographyContainer);

        // Spacing fields - visual box model
        var spacingData = {
            margin_top: values.marginTop || values.margin_top || '',
            margin_right: values.marginRight || values.margin_right || '',
            margin_bottom: values.marginBottom || values.margin_bottom || '',
            margin_left: values.marginLeft || values.margin_left || '',
            padding_top: values.paddingTop || values.padding_top || '',
            padding_right: values.paddingRight || values.padding_right || '',
            padding_bottom: values.paddingBottom || values.padding_bottom || '',
            padding_left: values.paddingLeft || values.padding_left || ''
        };
        var spacingHtml = this.renderSpacingField({ name: 'module_spacing', label: 'Margin & Padding' }, spacingData);
        this.setToggleGroupContent('design-spacing', spacingHtml);
        var spacingContainer = this.getToggleGroupContent('design-spacing');
        if (spacingContainer) this.initFieldHandlers(spacingContainer);

        // Background - advanced tabbed controls (color/gradient/image)
        var backgroundData = {
            backgroundType: values.backgroundType || values.background_type || 'color',
            backgroundColor: values.backgroundColor || values.background_color || '#ffffff',
            gradientType: values.gradientType || values.gradient_type || 'linear',
            gradientDirection: values.gradientDirection || values.gradient_direction || 180,
            gradientStartColor: values.gradientStartColor || values.gradient_start_color || '#ffffff',
            gradientEndColor: values.gradientEndColor || values.gradient_end_color || '#000000',
            backgroundImage: values.backgroundImage || values.background_image || '',
            backgroundSize: values.backgroundSize || values.background_size || 'cover',
            backgroundPosition: values.backgroundPosition || values.background_position || 'center center',
            backgroundRepeat: values.backgroundRepeat || values.background_repeat || 'no-repeat',
            backgroundParallax: values.backgroundParallax || values.background_parallax || false
        };
        var backgroundHtml = this.renderBackgroundFieldAdvanced({ name: 'background', label: 'Background' }, backgroundData);
        this.setToggleGroupContent('design-background', backgroundHtml);
        var backgroundContainer = this.getToggleGroupContent('design-background');
        if (backgroundContainer) {
            this.initFieldHandlers(backgroundContainer);
            this.initBackgroundTabs(backgroundContainer);
            this.initRangeValueDisplays(backgroundContainer);
        }

        // Border - professional visual controls via TB4Builder
        var borderSettings = {
            borderStyle: values.borderStyle || values.border_style || 'none',
            borderColor: values.borderColor || values.border_color || '#334155',
            borderWidth: values.borderWidth || values.border_width || { top: 0, right: 0, bottom: 0, left: 0 },
            borderRadius: values.borderRadius || values.border_radius || { topLeft: 0, topRight: 0, bottomRight: 0, bottomLeft: 0 }
        };
        // Convert legacy per-corner values to object format
        if (values.borderRadiusTL !== undefined || values.border_radius_tl !== undefined) {
            borderSettings.borderRadius = {
                topLeft: parseInt(values.borderRadiusTL || values.border_radius_tl || 0, 10),
                topRight: parseInt(values.borderRadiusTR || values.border_radius_tr || 0, 10),
                bottomRight: parseInt(values.borderRadiusBR || values.border_radius_br || 0, 10),
                bottomLeft: parseInt(values.borderRadiusBL || values.border_radius_bl || 0, 10)
            };
        }
        var borderHtml = this.renderBorderVisual({ design: borderSettings });
        this.setToggleGroupContent('design-border', borderHtml);
        var borderContainer = this.getToggleGroupContent('design-border');
        if (borderContainer) this.initFieldHandlers(borderContainer);

        // Box Shadow - professional visual controls via TB4Builder
        var shadowSettings = {
            boxShadowEnabled: values.boxShadowEnabled || values.shadowEnabled || values.shadow_enabled || false,
            boxShadowH: values.boxShadowH || values.shadowX || values.shadow_x || 0,
            boxShadowV: values.boxShadowV || values.shadowY || values.shadow_y || 4,
            boxShadowBlur: values.boxShadowBlur || values.shadowBlur || values.shadow_blur || 10,
            boxShadowSpread: values.boxShadowSpread || values.shadowSpread || values.shadow_spread || 0,
            boxShadowColor: values.boxShadowColor || values.shadowColor || values.shadow_color || 'rgba(0,0,0,0.15)',
            boxShadowInset: values.boxShadowInset || values.shadowInset || values.shadow_inset || false
        };
        var shadowHtml = this.renderBoxShadowVisual({ design: shadowSettings });
        this.setToggleGroupContent('design-shadow', shadowHtml);
        var shadowContainer = this.getToggleGroupContent('design-shadow');
        if (shadowContainer) this.initFieldHandlers(shadowContainer);

        // Sizing
        var sizingData = {
            width: values.width || '',
            minWidth: values.minWidth || values.min_width || '',
            maxWidth: values.maxWidth || values.max_width || '',
            height: values.height || '',
            minHeight: values.minHeight || values.min_height || '',
            maxHeight: values.maxHeight || values.max_height || '',
            overflow: values.overflow || 'visible'
        };
        var sizingHtml = this.renderSizingField({ name: 'sizing', label: 'Sizing' }, sizingData);
        this.setToggleGroupContent('design-sizing', sizingHtml);
        var sizingContainer = this.getToggleGroupContent('design-sizing');
        if (sizingContainer) this.initFieldHandlers(sizingContainer);

        // Position
        var positionData = {
            position: values.position || values.positionType || 'relative',
            top: values.positionTop || values.top || '',
            right: values.positionRight || values.right || '',
            bottom: values.positionBottom || values.bottom || '',
            left: values.positionLeft || values.left || '',
            zIndex: values.zIndex || values.z_index || ''
        };
        var positionHtml = this.renderPositionField({ name: 'position', label: 'Position' }, positionData);
        this.setToggleGroupContent('design-position', positionHtml);
        var positionContainer = this.getToggleGroupContent('design-position');
        if (positionContainer) this.initFieldHandlers(positionContainer);

        // Filters
        var filtersData = {
            blur: values.filterBlur || values.filter_blur || 0,
            brightness: values.filterBrightness || values.filter_brightness || 100,
            contrast: values.filterContrast || values.filter_contrast || 100,
            saturate: values.filterSaturate || values.filter_saturate || 100,
            hueRotate: values.filterHueRotate || values.filter_hue_rotate || 0,
            grayscale: values.filterGrayscale || values.filter_grayscale || 0,
            invert: values.filterInvert || values.filter_invert || 0,
            sepia: values.filterSepia || values.filter_sepia || 0,
            opacity: values.filterOpacity || values.filter_opacity || 100
        };
        var filtersHtml = this.renderFiltersField({ name: 'filters', label: 'CSS Filters' }, filtersData);
        this.setToggleGroupContent('design-filters', filtersHtml);
        var filtersContainer = this.getToggleGroupContent('design-filters');
        if (filtersContainer) {
            this.initFieldHandlers(filtersContainer);
            this.initRangeValueDisplays(filtersContainer);
        }

        // Transform
        var transformData = {
            translateX: values.transformTranslateX || values.transform_translate_x || '0',
            translateY: values.transformTranslateY || values.transform_translate_y || '0',
            scaleX: values.transformScaleX || values.transform_scale_x || 1,
            scaleY: values.transformScaleY || values.transform_scale_y || 1,
            rotate: values.transformRotate || values.transform_rotate || 0,
            skewX: values.transformSkewX || values.transform_skew_x || 0,
            skewY: values.transformSkewY || values.transform_skew_y || 0,
            transformOrigin: values.transformOrigin || values.transform_origin || 'center center'
        };
        var transformHtml = this.renderTransformField({ name: 'transform', label: 'Transform' }, transformData);
        this.setToggleGroupContent('design-transform', transformHtml);
        var transformContainer = this.getToggleGroupContent('design-transform');
        if (transformContainer) {
            this.initFieldHandlers(transformContainer);
            this.initRangeValueDisplays(transformContainer);
        }

        // Animation (in Design tab)
        var animationData = {
            animationType: values.animationType || values.animation_type || '',
            animationDuration: values.animationDuration || values.animation_duration || 500,
            animationDelay: values.animationDelay || values.animation_delay || 0,
            animationEasing: values.animationEasing || values.animation_easing || 'ease',
            animationIntensity: values.animationIntensity || values.animation_intensity || 50,
            animationRepeat: values.animationRepeat || values.animation_repeat || false
        };
        var animationHtml = this.renderAnimationField({ name: 'design_animation', label: 'Animation' }, animationData);
        this.setToggleGroupContent('design-animation', animationHtml);
        var animationContainer = this.getToggleGroupContent('design-animation');
        if (animationContainer) {
            this.initFieldHandlers(animationContainer);
            this.initRangeValueDisplays(animationContainer);
        }

        // Initialize TB4Builder controls (icons, event listeners)
        this.initBuilderControls();
    },

    /**
     * Populate the Advanced tab with advanced settings
     * @param {Object} moduleConfig - Module configuration
     * @param {Object} moduleData - Current module data
     */
    populateAdvancedTab: function(moduleConfig, moduleData) {
        var self = this;
        var settings = moduleData.settings || {};
        var advanced = moduleData.advanced || {};
        var values = Object.assign({}, settings, advanced);

        // Animation fields
        var animationFields = [
            { name: 'animationType', type: 'select', label: 'Animation Type', options: [
                { value: '', label: 'None' },
                { value: 'fade', label: 'Fade In' },
                { value: 'slide-up', label: 'Slide Up' },
                { value: 'slide-down', label: 'Slide Down' },
                { value: 'slide-left', label: 'Slide Left' },
                { value: 'slide-right', label: 'Slide Right' },
                { value: 'zoom', label: 'Zoom In' }
            ]},
            { name: 'animationDuration', type: 'text', label: 'Duration', placeholder: 'e.g., 0.3s' },
            { name: 'animationDelay', type: 'text', label: 'Delay', placeholder: 'e.g., 0s' }
        ];
        var animationHtml = this.renderFields(animationFields, values);
        this.setToggleGroupContent('advanced-animation', animationHtml);
        var animationContainer = this.getToggleGroupContent('advanced-animation');
        if (animationContainer) this.initFieldHandlers(animationContainer);

        // Visibility fields
        var visibilityFields = [
            { name: 'hideOnDesktop', type: 'checkbox', label: 'Visibility', checkboxLabel: 'Hide on Desktop' },
            { name: 'hideOnTablet', type: 'checkbox', label: '', checkboxLabel: 'Hide on Tablet' },
            { name: 'hideOnMobile', type: 'checkbox', label: '', checkboxLabel: 'Hide on Mobile' }
        ];
        var visibilityHtml = this.renderFields(visibilityFields, values);
        this.setToggleGroupContent('advanced-visibility', visibilityHtml);
        var visibilityContainer = this.getToggleGroupContent('advanced-visibility');
        if (visibilityContainer) this.initFieldHandlers(visibilityContainer);

        // Custom CSS/ID fields
        var customFields = [
            { name: 'customId', type: 'text', label: 'Custom ID', placeholder: 'my-element-id' },
            { name: 'customClass', type: 'text', label: 'Custom CSS Class', placeholder: 'class1 class2' },
            { name: 'customCss', type: 'textarea', label: 'Custom CSS', placeholder: '.my-class { color: red; }', rows: 4 }
        ];
        var customHtml = this.renderFields(customFields, values);
        this.setToggleGroupContent('advanced-custom', customHtml);
        var customContainer = this.getToggleGroupContent('advanced-custom');
        if (customContainer) this.initFieldHandlers(customContainer);
    },

    /**
     * Convert fields object to array format
     * @param {Object|Array} fields - Fields object or array
     * @returns {Array} Array of field configurations
     */
    convertFieldsToArray: function(fields) {
        if (Array.isArray(fields)) {
            return fields;
        }
        if (typeof fields === 'object' && fields !== null) {
            return Object.entries(fields).map(function(entry) {
                var name = entry[0];
                var config = entry[1];
                return Object.assign({ name: name }, config);
            });
        }
        return [];
    },

    /**
     * Get fields organized by tab group
     * @param {string} tab - Tab name ('content', 'design', 'advanced')
     * @param {Array} fields - Array of field configurations
     * @param {Object} moduleConfig - Module configuration
     * @returns {Object} Fields grouped by toggle group
     */
    getFieldsForTab: function(tab, fields, moduleConfig) {
        var groups = {};

        if (tab === 'content') {
            // Default groupings for content tab
            groups.text = [];
            groups.media = [];
            groups.link = [];

            fields.forEach(function(field) {
                var fieldType = (field.type || '').toLowerCase();
                var fieldName = (field.name || '').toLowerCase();

                // Check if field has explicit group assignment
                if (field.group) {
                    if (!groups[field.group]) groups[field.group] = [];
                    groups[field.group].push(field);
                    return;
                }

                // Auto-categorize by field type or name
                if (fieldType === 'upload' || fieldType === 'image' ||
                    fieldName.indexOf('image') !== -1 || fieldName.indexOf('media') !== -1 ||
                    fieldName.indexOf('photo') !== -1 || fieldName.indexOf('video') !== -1) {
                    groups.media.push(field);
                } else if (fieldName.indexOf('link') !== -1 || fieldName.indexOf('url') !== -1 ||
                           fieldName.indexOf('href') !== -1 || fieldName === 'target' ||
                           fieldName.indexOf('button') !== -1) {
                    groups.link.push(field);
                } else {
                    // Default to text group
                    groups.text.push(field);
                }
            });
        }

        return groups;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.tb4-builder') || window.TB4) {
        TB4SettingsSidebar.init();
        window.TB4SettingsSidebar = TB4SettingsSidebar;
        console.log('[TB4] Settings Sidebar integrated');
    }
});
