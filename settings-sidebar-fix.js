/**
 * TB4 Settings Sidebar - FIXED VERSION
 * Foundation component for Theme Builder 4 module settings
 * @version 1.0.1
 *
 * FIXES:
 * - getModuleConfig() now properly retrieves module fields
 * - Added fallback field generation from moduleData.content keys
 * - Added debug logging to trace configuration lookup
 * - Fixed CSS class toggle (is-open/open, visible/is-visible)
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
        code: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>'
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
            this.createToggleGroup('design-spacing', 'Spacing', this.icons.spacing, false) +
            this.createToggleGroup('design-background', 'Background', this.icons.background, false) +
            this.createToggleGroup('design-border', 'Border', this.icons.border, false) +
            '</div>' +
            '<div class="tb4-ss-panel" data-panel="advanced">' +
            this.createToggleGroup('advanced-animation', 'Animation', this.icons.animation, false) +
            this.createToggleGroup('advanced-visibility', 'Visibility', this.icons.visibility, false) +
            this.createToggleGroup('advanced-custom', 'Custom CSS/ID', this.icons.code, false) +
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
        var externalCss = document.querySelector('link[href*="settings-sidebar.css"]');
        if (externalCss) {
            console.log('[TB4] Settings Sidebar: External CSS detected, skipping style injection');
            return;
        }
        if (document.getElementById('tb4-ss-styles')) return;
        var styles = document.createElement('style');
        styles.id = 'tb4-ss-styles';
        styles.textContent = this.getStyles();
        document.head.appendChild(styles);
    },

    getStyles: function() {
        return '.tb4-ss-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9998;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}' +
            '.tb4-ss-overlay.visible,.tb4-ss-overlay.is-visible{opacity:1;visibility:visible}' +
            '.tb4-settings-sidebar{position:fixed;top:0;right:0;width:360px;max-width:100%;height:100vh;background:#fff;box-shadow:-4px 0 20px rgba(0,0,0,0.15);z-index:9999;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .3s}' +
            '.tb4-settings-sidebar.open,.tb4-settings-sidebar.is-open{transform:translateX(0)}' +
            '.tb4-ss-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-title{display:flex;align-items:center;gap:10px}' +
            '.tb4-ss-title-icon{display:flex;color:#6366f1}' +
            '.tb4-ss-header-title{font-size:16px;font-weight:600;color:#111827}' +
            '.tb4-ss-close{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border:none;background:transparent;border-radius:8px;cursor:pointer;color:#6b7280;transition:background .2s,color .2s}' +
            '.tb4-ss-close:hover{background:#f3f4f6;color:#111827}' +
            '.tb4-ss-tabs{display:flex;border-bottom:1px solid #e5e7eb;background:#fff}' +
            '.tb4-ss-tab{flex:1;padding:12px 16px;border:none;background:transparent;font-size:14px;font-weight:500;color:#6b7280;cursor:pointer;position:relative;transition:color .2s}' +
            '.tb4-ss-tab:hover{color:#111827}' +
            '.tb4-ss-tab.active{color:#6366f1}' +
            '.tb4-ss-tab.active::after{content:"";position:absolute;bottom:-1px;left:0;right:0;height:2px;background:#6366f1}' +
            '.tb4-ss-toolbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-devices{display:flex;gap:4px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:4px}' +
            '.tb4-ss-device-btn{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border:none;background:transparent;border-radius:6px;cursor:pointer;color:#9ca3af;transition:background .2s,color .2s}' +
            '.tb4-ss-device-btn:hover{color:#6b7280}' +
            '.tb4-ss-device-btn.active{background:#6366f1;color:#fff}' +
            '.tb4-ss-hover-toggle{display:flex;align-items:center;gap:8px;cursor:pointer}' +
            '.tb4-ss-hover-toggle input{width:36px;height:20px;-webkit-appearance:none;appearance:none;background:#e5e7eb;border-radius:10px;position:relative;cursor:pointer;transition:background .2s}' +
            '.tb4-ss-hover-toggle input::before{content:"";position:absolute;top:2px;left:2px;width:16px;height:16px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,0.2);transition:transform .2s}' +
            '.tb4-ss-hover-toggle input:checked{background:#6366f1}' +
            '.tb4-ss-hover-toggle input:checked::before{transform:translateX(16px)}' +
            '.tb4-ss-content{flex:1;overflow-y:auto;padding:16px 20px}' +
            '.tb4-ss-panel{display:none}' +
            '.tb4-ss-panel.active{display:block}' +
            '.tb4-ss-toggle-group{border:1px solid #e5e7eb;border-radius:8px;margin-bottom:12px;overflow:hidden}' +
            '.tb4-ss-toggle-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f9fafb;cursor:pointer;user-select:none;transition:background .2s}' +
            '.tb4-ss-toggle-header:hover{background:#f3f4f6}' +
            '.tb4-ss-toggle-title{display:flex;align-items:center;gap:10px}' +
            '.tb4-ss-toggle-icon{display:flex;color:#6b7280}' +
            '.tb4-ss-toggle-title span:last-child{font-size:14px;font-weight:500;color:#374151}' +
            '.tb4-ss-toggle-arrow{display:flex;color:#9ca3af;transform:rotate(-90deg);transition:transform .2s}' +
            '.tb4-ss-toggle-group.is-open .tb4-ss-toggle-arrow{transform:rotate(0deg)}' +
            '.tb4-ss-toggle-body{display:none;padding:16px;border-top:1px solid #e5e7eb}' +
            '.tb4-ss-toggle-group.is-open .tb4-ss-toggle-body{display:block}' +
            '.tb4-ss-footer{display:flex;gap:12px;padding:16px 20px;border-top:1px solid #e5e7eb;background:#f9fafb}' +
            '.tb4-ss-cancel-btn,.tb4-ss-save-btn{flex:1;padding:10px 16px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;transition:background .2s,border-color .2s}' +
            '.tb4-ss-cancel-btn{background:#fff;border:1px solid #e5e7eb;color:#374151}' +
            '.tb4-ss-cancel-btn:hover{background:#f9fafb;border-color:#d1d5db}' +
            '.tb4-ss-save-btn{background:#6366f1;border:1px solid #6366f1;color:#fff}' +
            '.tb4-ss-save-btn:hover{background:#4f46e5;border-color:#4f46e5}' +
            '.tb4-ss-empty-msg{color:#9ca3af;font-size:13px;text-align:center;padding:12px 0}' +
            '.tb4-ss-field{margin-bottom:16px}' +
            '.tb4-ss-field:last-child{margin-bottom:0}' +
            '.tb4-ss-field-label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px}' +
            '.tb4-ss-field-desc{font-size:12px;color:#9ca3af;margin-top:4px}' +
            '.tb4-ss-input{width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;font-size:14px;color:#111827;background:#fff;transition:border-color .2s,box-shadow .2s}' +
            '.tb4-ss-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,0.1)}' +
            '.tb4-ss-input::placeholder{color:#9ca3af}' +
            '.tb4-ss-textarea{min-height:80px;resize:vertical}' +
            '.tb4-ss-select-wrapper{position:relative}' +
            '.tb4-ss-select{padding-right:36px;appearance:none;cursor:pointer}' +
            '.tb4-ss-select-arrow{position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#6b7280}';
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

    escapeHtml: function(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    },

    renderField: function(fieldConfig, value) {
        if (!fieldConfig || !fieldConfig.type) {
            console.warn('[TB4-SS] renderField: Invalid field config', fieldConfig);
            return '';
        }

        var type = fieldConfig.type.toLowerCase();
        var actualValue = (value !== undefined && value !== null) ? value : (fieldConfig.default || '');

        switch (type) {
            case 'text':
                return this.renderTextField(fieldConfig, actualValue);
            case 'textarea':
            case 'wysiwyg':
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
            case 'image':
                return this.renderUploadField(fieldConfig, actualValue);
            default:
                console.warn('[TB4-SS] renderField: Unknown field type "' + type + '", defaulting to text');
                return this.renderTextField(fieldConfig, actualValue);
        }
    },

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

    renderSelectField: function(config, value) {
        var self = this;
        var options = config.options || {};
        var optionsHtml = '';

        // Handle options as object { value: label } or array [{value, label}]
        if (Array.isArray(options)) {
            optionsHtml = options.map(function(opt) {
                var optValue = typeof opt === 'object' ? (opt.value !== undefined ? opt.value : opt) : opt;
                var optLabel = typeof opt === 'object' ? (opt.label || opt.value || opt) : opt;
                var selected = String(optValue) === String(value) ? ' selected' : '';
                return '<option value="' + self.escapeHtml(optValue) + '"' + selected + '>' +
                       self.escapeHtml(optLabel) + '</option>';
            }).join('');
        } else if (typeof options === 'object') {
            // Options is an object like { 'h1': 'H1', 'h2': 'H2' }
            optionsHtml = Object.keys(options).map(function(key) {
                var optValue = key;
                var optLabel = options[key];
                var selected = String(optValue) === String(value) ? ' selected' : '';
                return '<option value="' + self.escapeHtml(optValue) + '"' + selected + '>' +
                       self.escapeHtml(optLabel) + '</option>';
            }).join('');
        }

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

    renderFields: function(fields, values) {
        var self = this;
        values = values || {};

        if (!Array.isArray(fields)) {
            console.warn('[TB4-SS] renderFields: Expected array of field configs');
            return '';
        }

        return fields.map(function(field) {
            var value = values[field.name];
            return self.renderField(field, value);
        }).join('');
    },

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

        // Color field sync handlers
        container.querySelectorAll('.tb4-ss-color-wrapper').forEach(function(wrapper) {
            var colorInput = wrapper.querySelector('.tb4-ss-color-input');
            var textInput = wrapper.querySelector('.tb4-ss-color-text');
            var preview = wrapper.querySelector('.tb4-ss-color-preview');

            if (colorInput) {
                colorInput.addEventListener('input', function() {
                    if (textInput) textInput.value = this.value;
                    if (preview) preview.style.backgroundColor = this.value;
                    self.dispatch('tb4-ss:fieldChange', {
                        name: this.name,
                        value: this.value,
                        type: 'color'
                    });
                });
            }

            if (textInput) {
                textInput.addEventListener('input', function() {
                    var val = this.value;
                    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(val)) {
                        if (colorInput) colorInput.value = val;
                        if (preview) preview.style.backgroundColor = val;
                        self.dispatch('tb4-ss:fieldChange', {
                            name: colorInput ? colorInput.name : '',
                            value: val,
                            type: 'color'
                        });
                    }
                });
            }
        });

        // Range slider value display
        container.querySelectorAll('.tb4-ss-range-wrapper').forEach(function(wrapper) {
            var rangeInput = wrapper.querySelector('.tb4-ss-range-input');
            var currentDisplay = wrapper.querySelector('.tb4-ss-range-current');

            if (rangeInput) {
                rangeInput.addEventListener('input', function() {
                    var unit = currentDisplay ? currentDisplay.textContent.replace(/[\d.-]/g, '') : '';
                    if (currentDisplay) currentDisplay.textContent = this.value + unit;
                    self.dispatch('tb4-ss:fieldChange', {
                        name: this.name,
                        value: this.value,
                        type: 'range'
                    });
                });
            }
        });

        // Upload field handlers
        container.querySelectorAll('.tb4-ss-upload-wrapper').forEach(function(wrapper) {
            var fileInput = wrapper.querySelector('.tb4-ss-upload-input');
            var hiddenInput = wrapper.querySelector('.tb4-ss-upload-value');
            var preview = wrapper.querySelector('.tb4-ss-upload-preview');
            var chooseBtn = wrapper.querySelector('.tb4-ss-upload-choose');
            var removeBtn = wrapper.querySelector('.tb4-ss-upload-remove');

            if (preview && fileInput) {
                preview.addEventListener('click', function() { fileInput.click(); });
            }
            if (chooseBtn && fileInput) {
                chooseBtn.addEventListener('click', function() { fileInput.click(); });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            if (preview) {
                                preview.style.backgroundImage = 'url(' + e.target.result + ')';
                                preview.classList.add('has-image');
                            }
                            if (hiddenInput) hiddenInput.value = e.target.result;
                            self.dispatch('tb4-ss:fieldChange', {
                                name: hiddenInput ? hiddenInput.name : '',
                                value: e.target.result,
                                type: 'upload'
                            });
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    if (preview) {
                        preview.style.backgroundImage = '';
                        preview.classList.remove('has-image');
                    }
                    if (hiddenInput) hiddenInput.value = '';
                    if (fileInput) fileInput.value = '';
                    this.remove();
                    self.dispatch('tb4-ss:fieldChange', {
                        name: hiddenInput ? hiddenInput.name : '',
                        value: '',
                        type: 'upload'
                    });
                });
            }
        });

        // Generic input change handlers
        container.querySelectorAll('.tb4-ss-input').forEach(function(input) {
            if (input.classList.contains('tb4-ss-color-text')) return;

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
    },

    // =========================================================================
    // Field Population System - FIXED VERSION
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

        // Get module config from TB4Builder or window.TB4
        var moduleConfig = this.getModuleConfig(normalizedType);

        console.log('[TB4-SS] populateFields:', {
            moduleType: moduleType,
            normalizedType: normalizedType,
            hasConfig: !!moduleConfig && Object.keys(moduleConfig).length > 0,
            configKeys: Object.keys(moduleConfig),
            hasFields: !!(moduleConfig && moduleConfig.fields),
            fieldsKeys: moduleConfig && moduleConfig.fields ? Object.keys(moduleConfig.fields) : [],
            moduleDataKeys: Object.keys(moduleData),
            contentKeys: moduleData.content ? Object.keys(moduleData.content) : []
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
     * FIXED: Added extensive debug logging and fallback handling
     * @param {string} moduleType - The module type
     * @returns {Object} Module configuration or empty object
     */
    getModuleConfig: function(moduleType) {
        var config = null;

        // Try TB4Builder.config.modules first
        if (typeof TB4Builder !== 'undefined' && TB4Builder.config && TB4Builder.config.modules) {
            config = TB4Builder.config.modules[moduleType];
            console.log('[TB4-SS] getModuleConfig from TB4Builder:', {
                moduleType: moduleType,
                found: !!config,
                hasFields: !!(config && config.fields),
                fieldCount: config && config.fields ? Object.keys(config.fields).length : 0,
                availableModules: Object.keys(TB4Builder.config.modules).slice(0, 15).join(', ')
            });
            if (config && Object.keys(config).length > 0) {
                return config;
            }
        }

        // Fallback to window.TB4.modules
        if (typeof window.TB4 !== 'undefined' && window.TB4.modules) {
            config = window.TB4.modules[moduleType];
            console.log('[TB4-SS] getModuleConfig from window.TB4:', {
                moduleType: moduleType,
                found: !!config,
                hasFields: !!(config && config.fields),
                fieldCount: config && config.fields ? Object.keys(config.fields).length : 0,
                availableModules: Object.keys(window.TB4.modules).slice(0, 15).join(', ')
            });
            if (config && Object.keys(config).length > 0) {
                return config;
            }
        }

        console.warn('[TB4-SS] getModuleConfig: No config found for module type:', moduleType);
        return {};
    },

    /**
     * Generate fallback fields from moduleData.content keys
     * Used when module config has no fields defined
     * @param {Object} content - Module content data
     * @returns {Array} Array of inferred field configurations
     */
    generateFallbackFields: function(content) {
        if (!content || typeof content !== 'object') return [];

        var fields = [];
        var self = this;

        Object.keys(content).forEach(function(key) {
            var value = content[key];
            var field = {
                name: key,
                label: self.formatFieldLabel(key),
                type: self.inferFieldType(key, value)
            };

            // Add default value
            if (value !== undefined && value !== null) {
                field.default = value;
            }

            fields.push(field);
        });

        console.log('[TB4-SS] Generated fallback fields:', fields);
        return fields;
    },

    /**
     * Format a field key into a human-readable label
     * @param {string} key - Field key
     * @returns {string} Formatted label
     */
    formatFieldLabel: function(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    },

    /**
     * Infer field type from key name and value
     * @param {string} key - Field key
     * @param {*} value - Field value
     * @returns {string} Inferred field type
     */
    inferFieldType: function(key, value) {
        var keyLower = key.toLowerCase();

        // Check key name for hints
        if (keyLower.indexOf('color') !== -1 || keyLower.indexOf('colour') !== -1) return 'color';
        if (keyLower.indexOf('image') !== -1 || keyLower.indexOf('photo') !== -1 || keyLower.indexOf('src') !== -1) return 'upload';
        if (keyLower.indexOf('url') !== -1 || keyLower.indexOf('link') !== -1 || keyLower.indexOf('href') !== -1) return 'text';
        if (keyLower.indexOf('content') !== -1 || keyLower.indexOf('text') !== -1 || keyLower.indexOf('description') !== -1) return 'textarea';
        if (keyLower.indexOf('level') !== -1 || keyLower.indexOf('type') !== -1 || keyLower.indexOf('style') !== -1 || keyLower.indexOf('align') !== -1) return 'select';
        if (keyLower.indexOf('show') !== -1 || keyLower.indexOf('enable') !== -1 || keyLower.indexOf('hide') !== -1) return 'yes_no';

        // Check value type
        if (typeof value === 'boolean') return 'yes_no';
        if (typeof value === 'number') return 'number';
        if (typeof value === 'string' && value.length > 100) return 'textarea';

        return 'text';
    },

    /**
     * Populate the Content tab with module-specific fields
     * FIXED: Added fallback field generation from moduleData.content
     * @param {Object} moduleConfig - Module configuration
     * @param {Object} moduleData - Current module data
     */
    populateContentTab: function(moduleConfig, moduleData) {
        var self = this;
        var fieldsObj = moduleConfig.fields || {};
        var content = moduleData.content || {};

        // Convert fields object to array with name property
        var fields = this.convertFieldsToArray(fieldsObj);

        // FALLBACK: If no fields from config, generate from content keys
        if (fields.length === 0 && Object.keys(content).length > 0) {
            console.log('[TB4-SS] No fields in config, generating from content keys');
            fields = this.generateFallbackFields(content);
        }

        console.log('[TB4-SS] populateContentTab:', {
            configFieldCount: Object.keys(fieldsObj).length,
            convertedFieldCount: fields.length,
            contentKeyCount: Object.keys(content).length,
            fields: fields.map(function(f) { return f.name + ':' + f.type; })
        });

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
     */
    populateDesignTab: function(moduleConfig, moduleData) {
        var self = this;
        var settings = moduleData.settings || {};
        var design = moduleData.design || {};
        var values = Object.assign({}, settings, design);

        var typographyFields = [
            { name: 'fontFamily', type: 'select', label: 'Font Family', options: [
                { value: '', label: 'Default' },
                { value: 'Arial, sans-serif', label: 'Arial' },
                { value: 'Georgia, serif', label: 'Georgia' },
                { value: 'Times New Roman, serif', label: 'Times New Roman' },
                { value: 'Verdana, sans-serif', label: 'Verdana' },
                { value: 'Courier New, monospace', label: 'Courier New' }
            ]},
            { name: 'fontSize', type: 'text', label: 'Font Size', placeholder: 'e.g., 16px, 1rem' },
            { name: 'fontWeight', type: 'select', label: 'Font Weight', options: [
                { value: '', label: 'Default' },
                { value: '300', label: 'Light (300)' },
                { value: '400', label: 'Normal (400)' },
                { value: '500', label: 'Medium (500)' },
                { value: '600', label: 'Semi-Bold (600)' },
                { value: '700', label: 'Bold (700)' }
            ]},
            { name: 'textColor', type: 'color', label: 'Text Color', default: '#333333' }
        ];
        var typographyHtml = this.renderFields(typographyFields, values);
        this.setToggleGroupContent('design-typography', typographyHtml);
        var typographyContainer = this.getToggleGroupContent('design-typography');
        if (typographyContainer) this.initFieldHandlers(typographyContainer);

        var spacingFields = [
            { name: 'marginTop', type: 'text', label: 'Margin Top', placeholder: 'e.g., 10px' },
            { name: 'marginBottom', type: 'text', label: 'Margin Bottom', placeholder: 'e.g., 10px' },
            { name: 'paddingTop', type: 'text', label: 'Padding Top', placeholder: 'e.g., 10px' },
            { name: 'paddingBottom', type: 'text', label: 'Padding Bottom', placeholder: 'e.g., 10px' },
            { name: 'paddingLeft', type: 'text', label: 'Padding Left', placeholder: 'e.g., 10px' },
            { name: 'paddingRight', type: 'text', label: 'Padding Right', placeholder: 'e.g., 10px' }
        ];
        var spacingHtml = this.renderFields(spacingFields, values);
        this.setToggleGroupContent('design-spacing', spacingHtml);
        var spacingContainer = this.getToggleGroupContent('design-spacing');
        if (spacingContainer) this.initFieldHandlers(spacingContainer);

        var backgroundFields = [
            { name: 'backgroundColor', type: 'color', label: 'Background Color', default: '#ffffff' },
            { name: 'backgroundImage', type: 'upload', label: 'Background Image' },
            { name: 'backgroundSize', type: 'select', label: 'Background Size', options: [
                { value: '', label: 'Default' },
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'auto', label: 'Auto' }
            ]},
            { name: 'backgroundPosition', type: 'select', label: 'Background Position', options: [
                { value: '', label: 'Default' },
                { value: 'center center', label: 'Center' },
                { value: 'top center', label: 'Top' },
                { value: 'bottom center', label: 'Bottom' }
            ]}
        ];
        var backgroundHtml = this.renderFields(backgroundFields, values);
        this.setToggleGroupContent('design-background', backgroundHtml);
        var backgroundContainer = this.getToggleGroupContent('design-background');
        if (backgroundContainer) this.initFieldHandlers(backgroundContainer);

        var borderFields = [
            { name: 'borderStyle', type: 'select', label: 'Border Style', options: [
                { value: '', label: 'None' },
                { value: 'solid', label: 'Solid' },
                { value: 'dashed', label: 'Dashed' },
                { value: 'dotted', label: 'Dotted' }
            ]},
            { name: 'borderWidth', type: 'text', label: 'Border Width', placeholder: 'e.g., 1px' },
            { name: 'borderColor', type: 'color', label: 'Border Color', default: '#e5e7eb' },
            { name: 'borderRadius', type: 'text', label: 'Border Radius', placeholder: 'e.g., 4px' }
        ];
        var borderHtml = this.renderFields(borderFields, values);
        this.setToggleGroupContent('design-border', borderHtml);
        var borderContainer = this.getToggleGroupContent('design-border');
        if (borderContainer) this.initFieldHandlers(borderContainer);
    },

    /**
     * Populate the Advanced tab with advanced settings
     */
    populateAdvancedTab: function(moduleConfig, moduleData) {
        var self = this;
        var settings = moduleData.settings || {};
        var advanced = moduleData.advanced || {};
        var values = Object.assign({}, settings, advanced);

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

        var visibilityFields = [
            { name: 'hideOnDesktop', type: 'checkbox', label: 'Visibility', checkboxLabel: 'Hide on Desktop' },
            { name: 'hideOnTablet', type: 'checkbox', label: '', checkboxLabel: 'Hide on Tablet' },
            { name: 'hideOnMobile', type: 'checkbox', label: '', checkboxLabel: 'Hide on Mobile' }
        ];
        var visibilityHtml = this.renderFields(visibilityFields, values);
        this.setToggleGroupContent('advanced-visibility', visibilityHtml);
        var visibilityContainer = this.getToggleGroupContent('advanced-visibility');
        if (visibilityContainer) this.initFieldHandlers(visibilityContainer);

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
     */
    getFieldsForTab: function(tab, fields, moduleConfig) {
        var groups = {};

        if (tab === 'content') {
            groups.text = [];
            groups.media = [];
            groups.link = [];

            fields.forEach(function(field) {
                var fieldType = (field.type || '').toLowerCase();
                var fieldName = (field.name || '').toLowerCase();

                if (field.group) {
                    if (!groups[field.group]) groups[field.group] = [];
                    groups[field.group].push(field);
                    return;
                }

                if (fieldType === 'upload' || fieldType === 'image' ||
                    fieldName.indexOf('image') !== -1 || fieldName.indexOf('media') !== -1 ||
                    fieldName.indexOf('photo') !== -1 || fieldName.indexOf('video') !== -1 ||
                    fieldName.indexOf('src') !== -1 || fieldName.indexOf('icon') !== -1) {
                    groups.media.push(field);
                } else if (fieldName.indexOf('link') !== -1 || fieldName.indexOf('url') !== -1 ||
                           fieldName.indexOf('href') !== -1 || fieldName === 'target' ||
                           fieldName.indexOf('button') !== -1) {
                    groups.link.push(field);
                } else {
                    groups.text.push(field);
                }
            });
        }

        return groups;
    }
};

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.tb4-builder') || window.TB4) {
        TB4SettingsSidebar.init();
        window.TB4SettingsSidebar = TB4SettingsSidebar;
        console.log('[TB4] Settings Sidebar v1.0.1 initialized (FIXED)');
    }
});
