/**
 * JTB Settings Panel JavaScript
 * Settings panel handler
 */

(function() {
    'use strict';

    window.JTB = window.JTB || {};

    // ========================================
    // Settings Namespace
    // ========================================

    JTB.Settings = {
        currentModule: null,
        currentConfig: null,
        currentTab: 'content',
        abortController: null  // For event cleanup
    };

    // ========================================
    // Render Methods
    // ========================================

    JTB.Settings.render = function(moduleConfig, moduleData) {
        const panel = document.querySelector('.jtb-settings-panel');
        if (!panel) return;

        // Debug logging
        JTB.log('Settings Render:', {
            moduleConfig: moduleConfig,
            designFields: moduleConfig.fields?.design,
            contentFields: moduleConfig.fields?.content
        });

        JTB.Settings.currentModule = moduleData;
        JTB.Settings.currentConfig = moduleConfig;
        JTB.Settings.currentTab = 'content';

        let html = '';

        // Header
        html += JTB.Settings.getHeaderHtml(moduleConfig);

        // Tabs
        html += JTB.Settings.getTabsHtml();

        // Tab contents
        html += '<div class="jtb-settings-body" style="flex:1;min-height:0;overflow-y:auto;scrollbar-width:auto;scrollbar-color:#5a5a8c #252540;">';

        // Content tab
        html += '<div class="jtb-tab-content active" data-tab="content">';
        html += JTB.Settings.getTabContentHtml('content', moduleConfig.fields.content || {});
        html += '</div>';

        // Design tab
        html += '<div class="jtb-tab-content" data-tab="design">';
        html += JTB.Settings.getTabContentHtml('design', moduleConfig.fields.design || {});
        html += '</div>';

        // Advanced tab
        html += '<div class="jtb-tab-content" data-tab="advanced">';
        html += JTB.Settings.getTabContentHtml('advanced', moduleConfig.fields.advanced || {});
        html += '</div>';

        html += '</div>';

        panel.innerHTML = html;

        JTB.Settings.bindPanelEvents(panel);
    };

    JTB.Settings.getHeaderHtml = function(config) {
        // Get Feather icon for close button
        const closeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 16) : '×';

        return `
            <div class="jtb-settings-header">
                <span class="jtb-settings-icon">${JTB.getModuleIcon(config.slug)}</span>
                <span class="jtb-settings-title">${config.name} Settings</span>
                <button class="jtb-settings-close">${closeIcon}</button>
            </div>
        `;
    };

    JTB.Settings.getTabsHtml = function() {
        return `
            <div class="jtb-settings-tabs">
                <button class="jtb-tab active" data-tab="content">Content</button>
                <button class="jtb-tab" data-tab="design">Design</button>
                <button class="jtb-tab" data-tab="advanced">Advanced</button>
            </div>
        `;
    };

    JTB.Settings.getTabContentHtml = function(tabName, fields) {
        let html = '';

        for (const fieldName in fields) {
            const field = fields[fieldName];
            const type = field.type || 'text';

            if (type === 'group' && field.toggle) {
                html += JTB.Settings.renderGroup(fieldName, field);
            } else if (type === 'group') {
                html += '<div class="jtb-field-group">';
                if (field.label) {
                    html += `<div class="jtb-group-label">${JTB.Settings.esc(field.label)}</div>`;
                }
                html += JTB.Settings.renderGroupFields(field.fields || {});
                html += '</div>';
            } else {
                html += JTB.Settings.renderField(fieldName, field);
            }
        }

        return html;
    };

    // ========================================
    // Group and Field Rendering
    // ========================================

    // Icon mapping for group headers
    JTB.Settings.groupIcons = {
        // Content tab groups
        'text': 'type',
        'title': 'type',
        'heading': 'type',
        'content': 'file-text',
        'description': 'align-left',
        'body': 'align-left',
        'link': 'link',
        'url': 'link',
        'button': 'square',
        'image': 'image',
        'icon': 'star',
        'media': 'image',
        'video': 'video',
        'audio': 'volume-2',
        'gallery': 'grid',
        'settings': 'settings',
        'options': 'sliders',
        'layout': 'layout',
        'elements': 'layers',
        'items': 'list',

        // Design tab groups
        'typography': 'type',
        'text_typography': 'type',
        'title_typography': 'type',
        'heading_typography': 'type',
        'body_typography': 'type',
        'spacing': 'move',
        'margin': 'move',
        'padding': 'move',
        'sizing': 'maximize-2',
        'border': 'square',
        'border_radius': 'square',
        'background': 'image',
        'box_shadow': 'layers',
        'shadow': 'layers',
        'colors': 'droplet',
        'filters': 'sliders',
        'transform': 'rotate-cw',
        'animation': 'zap',

        // Advanced tab groups
        'visibility': 'eye',
        'css_id': 'hash',
        'css_class': 'code',
        'custom_css': 'code',
        'attributes': 'list',
        'position': 'move',
        'z_index': 'layers',
        'overflow': 'maximize',
        'scroll': 'arrow-down',
        'transitions': 'clock',

        // Default
        'default': 'chevron-right'
    };

    JTB.Settings.getGroupIcon = function(groupName) {
        // Normalize the group name
        const normalized = groupName.toLowerCase().replace(/[\s-]+/g, '_');

        // Check for exact match
        if (JTB.Settings.groupIcons[normalized]) {
            return JTB.Settings.groupIcons[normalized];
        }

        // Check for partial match
        for (const key in JTB.Settings.groupIcons) {
            if (normalized.includes(key) || key.includes(normalized)) {
                return JTB.Settings.groupIcons[key];
            }
        }

        return JTB.Settings.groupIcons['default'];
    };

    JTB.Settings.renderGroup = function(name, group) {
        const iconName = JTB.Settings.getGroupIcon(name);
        const iconSvg = typeof JTB.getFeatherIcon === 'function'
            ? JTB.getFeatherIcon(iconName, 16, 2)
            : '';

        let html = `<div class="jtb-toggle-group" data-group="${JTB.Settings.esc(name)}">`;

        html += `
            <div class="jtb-toggle-header">
                <span class="jtb-toggle-group-icon">${iconSvg}</span>
                <span class="jtb-toggle-label">${JTB.Settings.esc(group.label || name)}</span>
                <span class="jtb-toggle-icon"></span>
            </div>
        `;

        html += '<div class="jtb-toggle-content">';
        html += JTB.Settings.renderGroupFields(group.fields || {});
        html += '</div>';

        html += '</div>';

        return html;
    };

    JTB.Settings.renderGroupFields = function(fields) {
        let html = '';

        for (const fieldName in fields) {
            const field = fields[fieldName];
            html += JTB.Settings.renderField(fieldName, field);
        }

        return html;
    };

    JTB.Settings.renderField = function(name, field) {
        const label = field.label || name.replace(/_/g, ' ');
        const description = field.description || '';
        const responsive = field.responsive || false;
        const hover = field.hover || false;
        const value = JTB.Settings.getValue(name, field.default);

        let conditionAttrs = '';
        if (field.show_if) {
            conditionAttrs += ` data-show-if='${JSON.stringify(field.show_if)}'`;
        }
        if (field.show_if_not) {
            conditionAttrs += ` data-show-if-not='${JSON.stringify(field.show_if_not)}'`;
        }

        // Check if field should be hidden initially
        const isVisible = JTB.Settings.checkConditions(field);
        const styleAttr = isVisible ? '' : ' style="display:none"';

        let html = `<div class="jtb-field" data-field-name="${JTB.Settings.esc(name)}"${conditionAttrs}${styleAttr}>`;

        // Header
        html += '<div class="jtb-field-header">';
        html += `<label class="jtb-field-label">${JTB.Settings.esc(label)}</label>`;

        // Toggles
        if (responsive || hover) {
            html += JTB.Settings.getFieldToggles(responsive, hover);
        }

        html += '</div>';

        // Input
        html += '<div class="jtb-field-input">';
        html += JTB.Fields.render(field.type || 'text', name, field, value);
        html += '</div>';

        // Description
        if (description) {
            html += `<div class="jtb-field-description">${JTB.Settings.esc(description)}</div>`;
        }

        html += '</div>';

        return html;
    };

    JTB.Settings.getFieldToggles = function(responsive, hover) {
        let html = '<div class="jtb-field-toggles">';

        if (responsive) {
            const desktopIcon = JTB.getFeatherIcon ? JTB.getFeatherIcon('monitor', 14, 2) : '🖥️';
            const tabletIcon = JTB.getFeatherIcon ? JTB.getFeatherIcon('tablet', 14, 2) : '📱';
            const phoneIcon = JTB.getFeatherIcon ? JTB.getFeatherIcon('smartphone', 14, 2) : '📲';

            html += `<button type="button" class="jtb-responsive-toggle active" data-device="desktop" title="Desktop">${desktopIcon}</button>`;
            html += `<button type="button" class="jtb-responsive-toggle" data-device="tablet" title="Tablet">${tabletIcon}</button>`;
            html += `<button type="button" class="jtb-responsive-toggle" data-device="phone" title="Phone">${phoneIcon}</button>`;
        }

        if (hover) {
            const eyeIcon = JTB.getFeatherIcon ? JTB.getFeatherIcon('eye', 14, 2) : '👁️';
            html += `<button type="button" class="jtb-hover-toggle" data-state="normal" title="Hover State">${eyeIcon}</button>`;
        }

        html += '</div>';

        return html;
    };

    // ========================================
    // Value Management
    // ========================================

    JTB.Settings.getValue = function(name, defaultValue) {
        if (!JTB.Settings.currentModule || !JTB.Settings.currentModule.attrs) {
            return defaultValue;
        }

        const value = JTB.Settings.currentModule.attrs[name];
        return value !== undefined ? value : defaultValue;
    };

    JTB.Settings.setValue = function(name, value) {
        if (!JTB.Settings.currentModule) {
            JTB.warn('setValue: No currentModule!');
            return;
        }

        if (!JTB.Settings.currentModule.attrs) {
            JTB.Settings.currentModule.attrs = {};
        }

        JTB.Settings.currentModule.attrs[name] = value;

        JTB.log('setValue:', {
            name: name,
            value: value,
            moduleId: JTB.Settings.currentModule.id,
            moduleType: JTB.Settings.currentModule.type
        });

        JTB.markDirty();
        JTB.renderCanvas();
    };

    JTB.Settings.checkConditions = function(field) {
        if (!field) return true;

        const attrs = JTB.Settings.currentModule ? JTB.Settings.currentModule.attrs || {} : {};

        // Check show_if
        if (field.show_if) {
            for (const condField in field.show_if) {
                const condValue = field.show_if[condField];
                const actualValue = attrs[condField] || '';

                if (Array.isArray(condValue)) {
                    if (!condValue.includes(actualValue)) return false;
                } else {
                    if (actualValue != condValue) return false;
                }
            }
        }

        // Check show_if_not
        if (field.show_if_not) {
            for (const condField in field.show_if_not) {
                const condValue = field.show_if_not[condField];
                const actualValue = attrs[condField] || '';

                if (Array.isArray(condValue)) {
                    if (condValue.includes(actualValue)) return false;
                } else {
                    if (actualValue == condValue) return false;
                }
            }
        }

        return true;
    };

    // ========================================
    // Event Bindings
    // ========================================

    JTB.Settings.bindPanelEvents = function(panel) {
        // Cleanup previous event listeners
        if (JTB.Settings.abortController) {
            JTB.Settings.abortController.abort();
        }
        JTB.Settings.abortController = new AbortController();
        const signal = JTB.Settings.abortController.signal;

        // Close button
        const closeBtn = panel.querySelector('.jtb-settings-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => JTB.Settings.close(), { signal });
        }

        // Tab switching
        panel.querySelectorAll('.jtb-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                JTB.Settings.setActiveTab(tab.dataset.tab);
            }, { signal });
        });

        // Toggle groups
        panel.querySelectorAll('.jtb-toggle-header').forEach(header => {
            header.addEventListener('click', () => {
                header.parentElement.classList.toggle('open');
            }, { signal });
        });

        // Responsive toggles
        panel.querySelectorAll('.jtb-responsive-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const fieldEl = btn.closest('.jtb-field');
                const device = btn.dataset.device;

                fieldEl.querySelectorAll('.jtb-responsive-toggle').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                JTB.Settings.switchDeviceInput(fieldEl, device);
            }, { signal });
        });

        // Hover toggles
        panel.querySelectorAll('.jtb-hover-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const isHover = btn.dataset.state === 'normal';
                btn.dataset.state = isHover ? 'hover' : 'normal';
                btn.classList.toggle('active', isHover);

                const fieldEl = btn.closest('.jtb-field');
                JTB.Settings.toggleHoverInput(fieldEl, isHover);
            }, { signal });
        });

        // Bind field events (pass signal for cleanup)
        JTB.Settings.bindFieldEvents(panel, signal);

        // Load dynamic options for select fields (e.g., CMS galleries)
        if (typeof JTB.Fields.loadDynamicOptions === 'function') {
            JTB.Fields.loadDynamicOptions(panel);
        }
    };

    JTB.Settings.bindFieldEvents = function(container, signal) {
        // Get signal from controller if not passed
        const abortSignal = signal || (JTB.Settings.abortController ? JTB.Settings.abortController.signal : undefined);
        const opts = abortSignal ? { signal: abortSignal } : {};

        // Text inputs
        container.querySelectorAll('.jtb-input-text, .jtb-input-url').forEach(input => {
            input.addEventListener('input', () => {
                const name = input.name || input.dataset.field;
                JTB.Settings.setValue(name, input.value);
            }, opts);
        });

        // Textareas
        container.querySelectorAll('.jtb-input-textarea').forEach(textarea => {
            textarea.addEventListener('input', () => {
                const name = textarea.name || textarea.dataset.field;
                JTB.Settings.setValue(name, textarea.value);
            }, opts);
        });

        // Selects
        container.querySelectorAll('.jtb-input-select').forEach(select => {
            select.addEventListener('change', () => {
                const name = select.name || select.dataset.field;
                JTB.Settings.setValue(name, select.value);
                JTB.Settings.refreshConditions();
            }, opts);
        });

        // Toggles
        container.querySelectorAll('.jtb-toggle-switch input').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const name = checkbox.name || checkbox.dataset.field;
                JTB.Settings.setValue(name, checkbox.checked);
                JTB.Settings.refreshConditions();
            }, opts);
        });

        // Range sliders with visual fill
        container.querySelectorAll('.jtb-input-range').forEach(range => {
            const wrapper = range.closest('.jtb-range-wrapper');
            if (!wrapper) return;

            const numberInput = wrapper.querySelector('.jtb-input-number');
            if (!numberInput) return;

            const name = numberInput.name || numberInput.dataset.field;
            const min = parseFloat(wrapper.dataset.min) || parseFloat(range.min) || 0;
            const max = parseFloat(wrapper.dataset.max) || parseFloat(range.max) || 100;

            const updateRangeFill = (val) => {
                const percent = ((val - min) / (max - min)) * 100;
                range.style.background = `linear-gradient(to right, var(--accent) 0%, var(--accent) ${percent}%, var(--bg-tertiary) ${percent}%, var(--bg-tertiary) 100%)`;
            };

            range.addEventListener('input', () => {
                const val = parseFloat(range.value);
                numberInput.value = val;
                updateRangeFill(val);
                JTB.Settings.setValue(name, val);
            }, opts);

            numberInput.addEventListener('input', () => {
                const val = parseFloat(numberInput.value) || 0;
                range.value = val;
                updateRangeFill(val);
                JTB.Settings.setValue(name, val);
            }, opts);

            // Initialize fill on load
            updateRangeFill(parseFloat(range.value));
        });

        // Color pickers (new RGBA version)
        container.querySelectorAll('.jtb-color-wrapper').forEach(wrapper => {
            JTB.Settings.initColorPicker(wrapper);
        });

        // Spacing controls (new box-model style)
        container.querySelectorAll('.jtb-spacing-control').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const inputs = wrapper.querySelectorAll('.jtb-spacing-value');
            const linkBtn = wrapper.querySelector('.jtb-spacing-link-btn');
            const allSlider = wrapper.querySelector('.jtb-spacing-all-slider');
            let linked = false;

            const updateValue = () => {
                const value = {};
                inputs.forEach(input => {
                    value[input.dataset.side] = parseFloat(input.value) || 0;
                });
                hiddenInput.value = JSON.stringify(value);
                JTB.Settings.setValue(name, value);
            };

            // Link button toggle
            if (linkBtn) {
                linkBtn.addEventListener('click', () => {
                    linked = !linked;
                    linkBtn.classList.toggle('linked', linked);

                    const linkIcon = linkBtn.querySelector('.jtb-link-icon');
                    const unlinkIcon = linkBtn.querySelector('.jtb-unlink-icon');

                    if (linked) {
                        if (linkIcon) linkIcon.style.display = 'none';
                        if (unlinkIcon) unlinkIcon.style.display = 'block';
                        // Sync all values to first input's value
                        const firstVal = inputs[0].value;
                        inputs.forEach(input => input.value = firstVal);
                        if (allSlider) allSlider.value = firstVal;
                        updateValue();
                    } else {
                        if (linkIcon) linkIcon.style.display = 'block';
                        if (unlinkIcon) unlinkIcon.style.display = 'none';
                    }
                });
            }

            // Individual inputs
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    if (linked) {
                        inputs.forEach(i => i.value = input.value);
                        if (allSlider) allSlider.value = input.value;
                    }
                    updateValue();
                });

                // Mouse wheel support
                input.addEventListener('wheel', (e) => {
                    e.preventDefault();
                    const delta = e.deltaY > 0 ? -1 : 1;
                    const newVal = Math.max(0, Math.min(parseInt(wrapper.dataset.max) || 100, parseInt(input.value || 0) + delta));
                    input.value = newVal;

                    if (linked) {
                        inputs.forEach(i => i.value = newVal);
                        if (allSlider) allSlider.value = newVal;
                    }
                    updateValue();
                });
            });

            // All-sides slider
            if (allSlider) {
                allSlider.addEventListener('input', () => {
                    const val = allSlider.value;
                    inputs.forEach(input => input.value = val);
                    updateValue();
                });
            }
        });

        // Legacy spacing wrapper support
        container.querySelectorAll('.jtb-spacing-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const inputs = wrapper.querySelectorAll('.jtb-spacing-input input');
            const linkBtn = wrapper.querySelector('.jtb-spacing-link');
            let linked = false;

            if (linkBtn) {
                linkBtn.addEventListener('click', () => {
                    linked = !linked;
                    linkBtn.classList.toggle('linked', linked);
                });
            }

            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    if (linked) {
                        inputs.forEach(i => i.value = input.value);
                    }

                    const value = {};
                    inputs.forEach(i => {
                        value[i.dataset.side] = parseFloat(i.value) || 0;
                    });

                    JTB.Settings.setValue(name, value);
                });
            });
        });

        // Upload fields - use JTB.openMediaGallery
        container.querySelectorAll('.jtb-upload-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const wrapper = btn.closest('.jtb-upload-wrapper');
                const input = wrapper.querySelector('input[type="hidden"]');
                const preview = wrapper.querySelector('.jtb-upload-preview');
                const name = wrapper.dataset.field;

                JTB.openMediaGallery(function(url) {
                    if (url) {
                        input.value = url;
                        preview.innerHTML = `<img src="${url}" alt="">`;

                        // Add remove button if not exists
                        if (!wrapper.querySelector('.jtb-upload-remove')) {
                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'jtb-upload-remove';
                            // Use Feather icon for remove
                            removeBtn.innerHTML = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 14) : '×';
                            removeBtn.addEventListener('click', () => {
                                input.value = '';
                                preview.innerHTML = '<div class="jtb-upload-placeholder">No image selected</div>';
                                removeBtn.remove();
                                JTB.Settings.setValue(name, '');
                            });
                            wrapper.appendChild(removeBtn);
                        }

                        JTB.Settings.setValue(name, url);
                    }
                });
            });
        });

        container.querySelectorAll('.jtb-upload-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const wrapper = btn.closest('.jtb-upload-wrapper');
                const input = wrapper.querySelector('input[type="hidden"]');
                const preview = wrapper.querySelector('.jtb-upload-preview');
                const name = wrapper.dataset.field;

                input.value = '';
                preview.innerHTML = '<div class="jtb-upload-placeholder">No image selected</div>';
                btn.remove();

                JTB.Settings.setValue(name, '');
            });
        });

        // Richtext
        container.querySelectorAll('.jtb-richtext-content').forEach(editor => {
            const wrapper = editor.closest('.jtb-richtext-wrapper');
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const name = wrapper.dataset.field;

            editor.addEventListener('input', () => {
                hiddenInput.value = editor.innerHTML;
                JTB.Settings.setValue(name, editor.innerHTML);
            });

            // Toolbar buttons
            wrapper.querySelectorAll('.jtb-richtext-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const command = btn.dataset.command;

                    if (command === 'createLink') {
                        const url = prompt('Enter URL:');
                        if (url) {
                            document.execCommand(command, false, url);
                        }
                    } else {
                        document.execCommand(command, false, null);
                    }

                    hiddenInput.value = editor.innerHTML;
                    JTB.Settings.setValue(name, editor.innerHTML);
                });
            });
        });

        // Code fields
        container.querySelectorAll('.jtb-code-wrapper').forEach(wrapper => {
            const textarea = wrapper.querySelector('.jtb-input-code');
            const name = wrapper.dataset.field;
            const fullscreenBtn = wrapper.querySelector('.jtb-code-fullscreen');

            textarea.addEventListener('input', () => {
                JTB.Settings.setValue(name, textarea.value);
            });

            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', () => {
                    wrapper.classList.toggle('jtb-code-fullscreen-mode');
                });
            }
        });

        // Date/Datetime fields
        container.querySelectorAll('.jtb-input-date, .jtb-input-datetime').forEach(input => {
            input.addEventListener('change', () => {
                const name = input.name || input.dataset.field;
                JTB.Settings.setValue(name, input.value);
            });
        });

        // Number fields (standalone)
        container.querySelectorAll('.jtb-input-number-field').forEach(input => {
            input.addEventListener('input', () => {
                const name = input.name || input.dataset.field;
                JTB.Settings.setValue(name, parseFloat(input.value) || 0);
            });
        });

        // Gallery fields
        container.querySelectorAll('.jtb-gallery-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const itemsContainer = wrapper.querySelector('.jtb-gallery-items');
            const addBtn = wrapper.querySelector('.jtb-gallery-add');

            // Add images
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    JTB.Settings.openGalleryPicker(wrapper);
                });
            }

            // Remove image
            wrapper.querySelectorAll('.jtb-gallery-remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = btn.closest('.jtb-gallery-item');
                    item.remove();
                    JTB.Settings.updateGalleryValue(wrapper);
                });
            });
        });

        // Repeater fields
        container.querySelectorAll('.jtb-repeater-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const addBtn = wrapper.querySelector('.jtb-repeater-add');
            const itemsContainer = wrapper.querySelector('.jtb-repeater-items');

            // Add item
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const index = itemsContainer.children.length;
                    const config = JTB.Settings.getRepeaterConfig(name);
                    const itemHtml = JTB.Fields.renderRepeaterItem(name, config.fields || {}, {}, index);
                    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);

                    // Bind events for new item
                    const newItem = itemsContainer.lastElementChild;
                    JTB.Settings.bindRepeaterItemEvents(newItem, wrapper);
                });
            }

            // Bind existing items
            wrapper.querySelectorAll('.jtb-repeater-item').forEach(item => {
                JTB.Settings.bindRepeaterItemEvents(item, wrapper);
            });
        });

        // Checkbox fields
        container.querySelectorAll('.jtb-input-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const name = checkbox.name || checkbox.dataset.field;
                JTB.Settings.setValue(name, checkbox.checked);
            });
        });

        // Radio fields
        container.querySelectorAll('.jtb-radio-wrapper input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const name = radio.name || radio.dataset.field;
                JTB.Settings.setValue(name, radio.value);
            });
        });

        // Button group fields
        container.querySelectorAll('.jtb-button-group-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');

            wrapper.querySelectorAll('.jtb-button-group-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    wrapper.querySelectorAll('.jtb-button-group-btn').forEach(b => b.classList.remove('jtb-active'));
                    btn.classList.add('jtb-active');
                    hiddenInput.value = btn.dataset.value;
                    JTB.Settings.setValue(name, btn.dataset.value);
                });
            });
        });

        // Align fields
        container.querySelectorAll('.jtb-align-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');

            wrapper.querySelectorAll('.jtb-align-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    wrapper.querySelectorAll('.jtb-align-btn').forEach(b => b.classList.remove('jtb-active'));
                    btn.classList.add('jtb-active');
                    hiddenInput.value = btn.dataset.value;
                    JTB.Settings.setValue(name, btn.dataset.value);
                });
            });
        });

        // Multi-select fields
        container.querySelectorAll('.jtb-multiselect-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');

            wrapper.querySelectorAll('.jtb-multiselect-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const selected = [];
                    wrapper.querySelectorAll('.jtb-multiselect-checkbox:checked').forEach(cb => {
                        selected.push(cb.value);
                    });
                    hiddenInput.value = JSON.stringify(selected);
                    JTB.Settings.setValue(name, selected);
                });
            });
        });

        // Gradient fields (new style from fields.js)
        container.querySelectorAll('.jtb-gradient-field').forEach(field => {
            if (typeof JTB.Fields !== 'undefined' && typeof JTB.Fields.initGradientField === 'function') {
                JTB.Fields.initGradientField(field.parentElement);
            }
        });

        // Gradient fields (legacy wrapper style)
        container.querySelectorAll('.jtb-gradient-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            JTB.Settings.bindGradientEvents(wrapper, name);
        });

        // Box shadow fields
        container.querySelectorAll('.jtb-box-shadow-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            JTB.Settings.bindBoxShadowEvents(wrapper, name);
        });

        // Border fields
        container.querySelectorAll('.jtb-border-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            JTB.Settings.bindBorderEvents(wrapper, name);
        });

        // Font fields
        container.querySelectorAll('.jtb-font-wrapper').forEach(wrapper => {
            const name = wrapper.dataset.field;
            JTB.Settings.bindFontEvents(wrapper, name);
        });

        // Icon fields
        container.querySelectorAll('.jtb-icon-choose').forEach(btn => {
            btn.addEventListener('click', () => {
                const wrapper = btn.closest('.jtb-icon-wrapper');
                JTB.Settings.openIconPicker(wrapper);
            });
        });
    };

    // ========================================
    // Complex Field Event Handlers
    // ========================================

    JTB.Settings.bindRepeaterItemEvents = function(item, wrapper) {
        const toggleBtn = item.querySelector('.jtb-repeater-toggle');
        const removeBtn = item.querySelector('.jtb-repeater-remove');
        const content = item.querySelector('.jtb-repeater-item-content');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                item.classList.toggle('collapsed');
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                item.remove();
                JTB.Settings.updateRepeaterValue(wrapper);
            });
        }

        // Bind field events in repeater item
        JTB.Settings.bindFieldEvents(content);
    };

    JTB.Settings.updateRepeaterValue = function(wrapper) {
        const name = wrapper.dataset.field;
        const items = [];

        wrapper.querySelectorAll('.jtb-repeater-item').forEach((item, index) => {
            const itemData = {};
            item.querySelectorAll('[name]').forEach(input => {
                const fieldMatch = input.name.match(/\[(\d+)\]\[(\w+)\]/);
                if (fieldMatch) {
                    const fieldName = fieldMatch[2];
                    itemData[fieldName] = input.type === 'checkbox' ? input.checked : input.value;
                }
            });
            items.push(itemData);
        });

        JTB.Settings.setValue(name, items);
    };

    JTB.Settings.getRepeaterConfig = function(name) {
        if (!JTB.Settings.currentConfig) return { fields: {} };

        // Search in all tabs for the field config
        const tabs = ['content', 'design', 'advanced'];
        for (const tab of tabs) {
            const fields = JTB.Settings.currentConfig.fields[tab] || {};
            if (fields[name]) {
                return fields[name];
            }
        }
        return { fields: {} };
    };

    JTB.Settings.updateGalleryValue = function(wrapper) {
        const name = wrapper.dataset.field;
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const images = [];

        wrapper.querySelectorAll('.jtb-gallery-item img').forEach(img => {
            images.push(img.src);
        });

        hiddenInput.value = JSON.stringify(images);
        JTB.Settings.setValue(name, images);
    };

    JTB.Settings.openGalleryPicker = function(wrapper) {
        // Use the full Media Gallery with MULTI-SELECT enabled
        if (typeof JTB.openMediaGallery === 'function') {
            JTB.openMediaGallery(function(urls) {
                // urls is an array in multi-select mode
                if (urls && Array.isArray(urls)) {
                    urls.forEach(url => {
                        JTB.Settings.addImageToGallery(wrapper, url);
                    });
                } else if (urls) {
                    // Fallback for single URL (backwards compatibility)
                    JTB.Settings.addImageToGallery(wrapper, urls);
                }
            }, { multiSelect: true }); // Enable multi-select for gallery fields
        } else {
            // Fallback to simple file input if Media Gallery not available
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.multiple = true;

            input.onchange = (e) => {
                const files = Array.from(e.target.files);
                if (!files.length) return;

                files.forEach(file => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('csrf_token', JTB.config.csrfToken);

                    fetch(JTB.config.apiUrl + '/upload', {
                        method: 'POST',
                        credentials: 'include',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            JTB.Settings.addImageToGallery(wrapper, data.data.url);
                        }
                    });
                });
            };

            input.click();
        }
    };

    // Helper function to add image to gallery
    JTB.Settings.addImageToGallery = function(wrapper, url) {
        const itemsContainer = wrapper.querySelector('.jtb-gallery-items');
        const index = itemsContainer.children.length;

        // Use Feather icon for remove
        const removeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 12) : '×';

        const itemHtml = `
            <div class="jtb-gallery-item" data-index="${index}">
                <img src="${url}" alt="">
                <button type="button" class="jtb-gallery-remove" title="Remove">${removeIcon}</button>
            </div>
        `;
        itemsContainer.insertAdjacentHTML('beforeend', itemHtml);

        // Bind remove event
        const newItem = itemsContainer.lastElementChild;
        newItem.querySelector('.jtb-gallery-remove').addEventListener('click', () => {
            newItem.remove();
            JTB.Settings.updateGalleryValue(wrapper);
        });

        JTB.Settings.updateGalleryValue(wrapper);
    };

    JTB.Settings.bindGradientEvents = function(wrapper, name) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const preview = wrapper.querySelector('.jtb-gradient-preview');
        const typeSelect = wrapper.querySelector('.jtb-gradient-type');
        const angleInput = wrapper.querySelector('.jtb-gradient-angle');
        const angleValue = wrapper.querySelector('.jtb-gradient-angle-value');
        const stopsContainer = wrapper.querySelector('.jtb-gradient-stops');
        const addStopBtn = wrapper.querySelector('.jtb-gradient-add-stop');

        const updateGradient = () => {
            const type = typeSelect.value;
            const angle = angleInput.value;
            const stops = [];

            stopsContainer.querySelectorAll('.jtb-gradient-stop').forEach(stop => {
                const color = stop.querySelector('input[type="color"]').value;
                const position = stop.dataset.position;
                stops.push(`${color} ${position}%`);
            });

            let gradient;
            if (type === 'radial') {
                gradient = `radial-gradient(circle, ${stops.join(', ')})`;
            } else {
                gradient = `linear-gradient(${angle}deg, ${stops.join(', ')})`;
            }

            preview.style.background = gradient;
            hiddenInput.value = gradient;
            JTB.Settings.setValue(name, gradient);
        };

        typeSelect.addEventListener('change', updateGradient);

        angleInput.addEventListener('input', () => {
            angleValue.textContent = angleInput.value + '°';
            updateGradient();
        });

        stopsContainer.querySelectorAll('.jtb-gradient-stop input[type="color"]').forEach(input => {
            input.addEventListener('input', updateGradient);
        });

        if (addStopBtn) {
            addStopBtn.addEventListener('click', () => {
                const position = 50; // Default middle position
                // Use Feather icon for remove
                const removeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 12) : '×';

                const stopHtml = `
                    <div class="jtb-gradient-stop" data-position="${position}">
                        <input type="color" value="#888888">
                        <span>${position}%</span>
                        <button type="button" class="jtb-gradient-stop-remove">${removeIcon}</button>
                    </div>
                `;
                stopsContainer.insertAdjacentHTML('beforeend', stopHtml);

                const newStop = stopsContainer.lastElementChild;
                newStop.querySelector('input[type="color"]').addEventListener('input', updateGradient);
                newStop.querySelector('.jtb-gradient-stop-remove').addEventListener('click', () => {
                    newStop.remove();
                    updateGradient();
                });

                updateGradient();
            });
        }
    };

    JTB.Settings.bindBoxShadowEvents = function(wrapper, name) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"][name="' + name + '"]');
        const preview = wrapper.querySelector('.jtb-shadow-preview-box');
        const insetLabel = wrapper.querySelector('.jtb-shadow-inset-label');

        const controls = {
            h: wrapper.querySelector('.jtb-shadow-h'),
            hNum: wrapper.querySelector('.jtb-shadow-h-num'),
            v: wrapper.querySelector('.jtb-shadow-v'),
            vNum: wrapper.querySelector('.jtb-shadow-v-num'),
            blur: wrapper.querySelector('.jtb-shadow-blur'),
            blurNum: wrapper.querySelector('.jtb-shadow-blur-num'),
            spread: wrapper.querySelector('.jtb-shadow-spread'),
            spreadNum: wrapper.querySelector('.jtb-shadow-spread-num'),
            color: wrapper.querySelector('.jtb-shadow-color'),
            colorInput: wrapper.querySelector('.jtb-shadow-color-input'),
            inset: wrapper.querySelector('.jtb-shadow-inset')
        };

        const updateShadow = () => {
            const values = {
                horizontal: parseInt(controls.h?.value || controls.hNum?.value || 0),
                vertical: parseInt(controls.v?.value || controls.vNum?.value || 0),
                blur: parseInt(controls.blur?.value || controls.blurNum?.value || 0),
                spread: parseInt(controls.spread?.value || controls.spreadNum?.value || 0),
                color: controls.color?.value || 'rgba(0,0,0,0.15)',
                inset: controls.inset?.checked || false
            };

            const shadow = `${values.inset ? 'inset ' : ''}${values.horizontal}px ${values.vertical}px ${values.blur}px ${values.spread}px ${values.color}`;

            if (preview) {
                preview.style.boxShadow = shadow;
            }

            if (insetLabel) {
                insetLabel.textContent = values.inset ? 'Inner shadow' : 'Outer shadow';
            }

            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(values);
            }
            JTB.Settings.setValue(name, values);
        };

        // Sync sliders with number inputs
        const syncPairs = [
            { slider: controls.h, num: controls.hNum },
            { slider: controls.v, num: controls.vNum },
            { slider: controls.blur, num: controls.blurNum },
            { slider: controls.spread, num: controls.spreadNum }
        ];

        syncPairs.forEach(pair => {
            if (pair.slider && pair.num) {
                pair.slider.addEventListener('input', () => {
                    pair.num.value = pair.slider.value;
                    updateShadow();
                });
                pair.num.addEventListener('input', () => {
                    pair.slider.value = pair.num.value;
                    updateShadow();
                });
            }
        });

        // Color input sync
        if (controls.colorInput && controls.color) {
            controls.colorInput.addEventListener('input', () => {
                controls.color.value = controls.colorInput.value;
                updateShadow();
            });
            controls.color.addEventListener('input', updateShadow);
        }

        // Inset toggle
        if (controls.inset) {
            controls.inset.addEventListener('change', updateShadow);
        }
    };

    JTB.Settings.bindBorderEvents = function(wrapper, name) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"][name="' + name + '"]');
        const preview = wrapper.querySelector('.jtb-border-preview-inner');

        const controls = {
            width: wrapper.querySelector('.jtb-border-width'),
            widthSlider: wrapper.querySelector('.jtb-border-width-slider'),
            style: wrapper.querySelector('.jtb-border-style'),
            styleBtns: wrapper.querySelectorAll('.jtb-border-style-btn'),
            color: wrapper.querySelector('.jtb-border-color'),
            colorText: wrapper.querySelector('.jtb-border-color-text'),
            radius: wrapper.querySelector('.jtb-border-radius'),
            radiusSlider: wrapper.querySelector('.jtb-border-radius-slider')
        };

        const updateBorder = () => {
            const values = {
                width: parseInt(controls.width.value) || 0,
                style: controls.style.value || 'solid',
                color: controls.color.value || '#000000',
                radius: parseInt(controls.radius.value) || 0
            };

            if (preview) {
                preview.style.border = `${values.width}px ${values.style} ${values.color}`;
                preview.style.borderRadius = `${values.radius}px`;
            }

            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(values);
            }
            JTB.Settings.setValue(name, values);
        };

        // Width slider sync
        if (controls.widthSlider && controls.width) {
            controls.widthSlider.addEventListener('input', () => {
                controls.width.value = controls.widthSlider.value;
                updateBorder();
            });
            controls.width.addEventListener('input', () => {
                controls.widthSlider.value = controls.width.value;
                updateBorder();
            });
        }

        // Radius slider sync
        if (controls.radiusSlider && controls.radius) {
            controls.radiusSlider.addEventListener('input', () => {
                controls.radius.value = controls.radiusSlider.value;
                updateBorder();
            });
            controls.radius.addEventListener('input', () => {
                controls.radiusSlider.value = controls.radius.value;
                updateBorder();
            });
        }

        // Style buttons
        if (controls.styleBtns.length > 0) {
            controls.styleBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    controls.styleBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    controls.style.value = btn.dataset.style;
                    updateBorder();
                });
            });
        }

        // Color sync
        if (controls.color && controls.colorText) {
            controls.color.addEventListener('input', () => {
                controls.colorText.value = controls.color.value;
                updateBorder();
            });
            controls.colorText.addEventListener('input', () => {
                if (/^#[0-9A-Fa-f]{6}$/.test(controls.colorText.value)) {
                    controls.color.value = controls.colorText.value;
                    updateBorder();
                }
            });
        }
    };

    JTB.Settings.bindFontEvents = function(wrapper, name) {
        const hiddenInput = wrapper.querySelector('input[type="hidden"][name="' + name + '"]');
        const preview = wrapper.querySelector('.jtb-font-preview');

        const controls = {
            family: wrapper.querySelector('.jtb-font-family-select'),
            size: wrapper.querySelector('.jtb-font-size-input'),
            sizeSlider: wrapper.querySelector('.jtb-font-size-slider'),
            weight: wrapper.querySelector('.jtb-font-weight-select'),
            weightBtns: wrapper.querySelectorAll('.jtb-font-weight-btn'),
            style: wrapper.querySelector('.jtb-font-style-select'),
            styleBtns: wrapper.querySelectorAll('.jtb-font-style-btn'),
            lineHeight: wrapper.querySelector('.jtb-line-height-input'),
            lineHeightSlider: wrapper.querySelector('.jtb-line-height-slider'),
            letterSpacing: wrapper.querySelector('.jtb-letter-spacing-input'),
            letterSpacingSlider: wrapper.querySelector('.jtb-letter-spacing-slider')
        };

        const updateFont = () => {
            const values = {
                family: controls.family?.value || 'inherit',
                size: parseInt(controls.size?.value || 16),
                weight: controls.weight?.value || '400',
                style: controls.style?.value || 'normal',
                lineHeight: parseFloat(controls.lineHeight?.value || 1.5),
                letterSpacing: parseFloat(controls.letterSpacing?.value || 0)
            };

            // Update preview
            if (preview) {
                preview.style.fontFamily = values.family;
                preview.style.fontSize = values.size + 'px';
                preview.style.fontWeight = values.weight;
                preview.style.fontStyle = values.style;
                preview.style.lineHeight = values.lineHeight;
                preview.style.letterSpacing = values.letterSpacing + 'px';
            }

            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(values);
            }
            JTB.Settings.setValue(name, values);
        };

        // Family select
        if (controls.family) {
            controls.family.addEventListener('change', updateFont);
        }

        // Size slider sync
        if (controls.sizeSlider && controls.size) {
            controls.sizeSlider.addEventListener('input', () => {
                controls.size.value = controls.sizeSlider.value;
                updateFont();
            });
            controls.size.addEventListener('input', () => {
                controls.sizeSlider.value = controls.size.value;
                updateFont();
            });
        }

        // Weight buttons
        if (controls.weightBtns.length > 0) {
            controls.weightBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    controls.weightBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    if (controls.weight) controls.weight.value = btn.dataset.weight;
                    updateFont();
                });
            });
        }

        // Style buttons
        if (controls.styleBtns.length > 0) {
            controls.styleBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    controls.styleBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    if (controls.style) controls.style.value = btn.dataset.style;
                    updateFont();
                });
            });
        }

        // Line height slider sync
        if (controls.lineHeightSlider && controls.lineHeight) {
            controls.lineHeightSlider.addEventListener('input', () => {
                controls.lineHeight.value = controls.lineHeightSlider.value;
                updateFont();
            });
            controls.lineHeight.addEventListener('input', () => {
                controls.lineHeightSlider.value = controls.lineHeight.value;
                updateFont();
            });
        }

        // Letter spacing slider sync
        if (controls.letterSpacingSlider && controls.letterSpacing) {
            controls.letterSpacingSlider.addEventListener('input', () => {
                controls.letterSpacing.value = controls.letterSpacingSlider.value;
                updateFont();
            });
            controls.letterSpacing.addEventListener('input', () => {
                controls.letterSpacingSlider.value = controls.letterSpacing.value;
                updateFont();
            });
        }
    };

    // ========================================
    // Color Picker with RGBA Support
    // ========================================

    JTB.Settings.initColorPicker = function(wrapper) {
        const name = wrapper.dataset.field;
        const hiddenInput = wrapper.querySelector('.jtb-input-color-value');
        const textInput = wrapper.querySelector('.jtb-input-color-text');
        const preview = wrapper.querySelector('.jtb-color-preview');
        const trigger = wrapper.querySelector('.jtb-color-picker-trigger');
        const popup = wrapper.querySelector('.jtb-color-picker-popup');

        if (!hiddenInput || !popup) return;

        // Current color state
        let currentColor = {
            h: 0, s: 100, l: 50, // HSL
            r: 255, g: 0, b: 0,  // RGB
            a: 1                  // Alpha
        };

        // Parse initial value
        const initialValue = hiddenInput.value || '#000000';
        const parsed = JTB.Fields.parseColor(initialValue);
        currentColor.r = parsed.r;
        currentColor.g = parsed.g;
        currentColor.b = parsed.b;
        currentColor.a = parsed.a;
        const hsl = JTB.Fields.rgbToHsl(parsed.r, parsed.g, parsed.b);
        currentColor.h = hsl.h;
        currentColor.s = hsl.s;
        currentColor.l = hsl.l;

        // Elements
        const saturationArea = popup.querySelector('.jtb-color-picker-saturation');
        const saturationPointer = popup.querySelector('.jtb-saturation-pointer');
        const hueSlider = popup.querySelector('.jtb-color-picker-hue');
        const huePointer = popup.querySelector('.jtb-hue-pointer');
        const alphaSlider = popup.querySelector('.jtb-color-picker-alpha');
        const alphaPointer = popup.querySelector('.jtb-alpha-pointer');
        const alphaGradient = popup.querySelector('.jtb-alpha-gradient');

        const hexInput = popup.querySelector('.jtb-color-hex-input');
        const rInput = popup.querySelector('.jtb-color-r-input');
        const gInput = popup.querySelector('.jtb-color-g-input');
        const bInput = popup.querySelector('.jtb-color-b-input');
        const aInput = popup.querySelector('.jtb-color-a-input');
        const presets = popup.querySelectorAll('.jtb-color-preset');

        // Update functions
        const updateFromHSL = () => {
            const rgb = JTB.Fields.hslToRgb(currentColor.h, currentColor.s, currentColor.l);
            currentColor.r = rgb.r;
            currentColor.g = rgb.g;
            currentColor.b = rgb.b;
            updateUI();
        };

        const updateFromRGB = () => {
            const hsl = JTB.Fields.rgbToHsl(currentColor.r, currentColor.g, currentColor.b);
            currentColor.h = hsl.h;
            currentColor.s = hsl.s;
            currentColor.l = hsl.l;
            updateUI();
        };

        const updateUI = () => {
            const colorValue = JTB.Fields.formatColor(currentColor.r, currentColor.g, currentColor.b, currentColor.a);
            const hexValue = JTB.Fields.rgbToHex(currentColor.r, currentColor.g, currentColor.b);

            // Update hidden input and text input
            hiddenInput.value = colorValue;
            textInput.value = colorValue;

            // Update preview
            preview.style.backgroundColor = colorValue;

            // Update saturation area background (pure hue color)
            const pureHueRgb = JTB.Fields.hslToRgb(currentColor.h, 100, 50);
            const pureHueHex = JTB.Fields.rgbToHex(pureHueRgb.r, pureHueRgb.g, pureHueRgb.b);
            if (saturationArea) {
                saturationArea.style.backgroundColor = pureHueHex;
            }

            // Update saturation pointer position
            if (saturationPointer && saturationArea) {
                const satRect = saturationArea.getBoundingClientRect();
                if (satRect.width > 0) {
                    // S goes left to right (0-100), L inverts (100 at top, 0 at bottom)
                    const x = (currentColor.s / 100) * 100;
                    const y = (1 - currentColor.l / 100) * 100;
                    saturationPointer.style.left = x + '%';
                    saturationPointer.style.top = y + '%';
                }
            }

            // Update hue pointer position
            if (huePointer) {
                const huePercent = (currentColor.h / 360) * 100;
                huePointer.style.left = huePercent + '%';
            }

            // Update alpha pointer position
            if (alphaPointer) {
                const alphaPercent = currentColor.a * 100;
                alphaPointer.style.left = alphaPercent + '%';
            }

            // Update alpha gradient
            if (alphaGradient) {
                alphaGradient.style.setProperty('--alpha-color', hexValue);
            }

            // Update input values
            if (hexInput) hexInput.value = hexValue;
            if (rInput) rInput.value = currentColor.r;
            if (gInput) gInput.value = currentColor.g;
            if (bInput) bInput.value = currentColor.b;
            if (aInput) aInput.value = Math.round(currentColor.a * 100);

            // Trigger change
            JTB.Settings.setValue(name, colorValue);
        };

        // Move popup to body for proper z-index stacking (portal pattern)
        let popupMoved = false;
        const movePopupToBody = () => {
            if (!popupMoved) {
                document.body.appendChild(popup);
                popup.style.position = 'fixed';
                popup.style.zIndex = '99999';
                popupMoved = true;
            }
        };

        const positionPopup = () => {
            const triggerRect = trigger.getBoundingClientRect();
            const popupHeight = popup.offsetHeight || 350;
            const popupWidth = popup.offsetWidth || 260;

            // Position below trigger, aligned to left
            let top = triggerRect.bottom + 8;
            let left = triggerRect.left;

            // If popup would go off-screen bottom, position above trigger
            if (top + popupHeight > window.innerHeight - 10) {
                top = triggerRect.top - popupHeight - 8;
            }

            // If popup would go off-screen right, align to right edge
            if (left + popupWidth > window.innerWidth - 10) {
                left = window.innerWidth - popupWidth - 10;
            }

            // Ensure minimum left position
            if (left < 10) left = 10;

            popup.style.top = top + 'px';
            popup.style.left = left + 'px';
        };

        // Toggle popup
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = popup.style.display !== 'none';

            // Close all other popups
            document.querySelectorAll('.jtb-color-picker-popup').forEach(p => {
                if (p !== popup) p.style.display = 'none';
            });

            if (isOpen) {
                popup.style.display = 'none';
            } else {
                movePopupToBody();
                popup.style.display = 'block';
                positionPopup();
                updateUI(); // Refresh positions when opening
            }
        });

        // Close popup on outside click
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target) && !popup.contains(e.target)) {
                popup.style.display = 'none';
            }
        });

        // Saturation area interaction
        if (saturationArea) {
            const handleSaturation = (e) => {
                const rect = saturationArea.getBoundingClientRect();
                let x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                let y = Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height));

                // x = saturation (0-100), y = inverted lightness
                currentColor.s = x * 100;
                currentColor.l = (1 - y) * 100;
                updateFromHSL();
            };

            saturationArea.addEventListener('mousedown', (e) => {
                handleSaturation(e);

                const onMove = (e) => handleSaturation(e);
                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
        }

        // Hue slider interaction
        if (hueSlider) {
            const handleHue = (e) => {
                const rect = hueSlider.getBoundingClientRect();
                let x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                currentColor.h = x * 360;
                updateFromHSL();
            };

            hueSlider.addEventListener('mousedown', (e) => {
                handleHue(e);

                const onMove = (e) => handleHue(e);
                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
        }

        // Alpha slider interaction
        if (alphaSlider) {
            const handleAlpha = (e) => {
                const rect = alphaSlider.getBoundingClientRect();
                let x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
                currentColor.a = x;
                updateUI();
            };

            alphaSlider.addEventListener('mousedown', (e) => {
                handleAlpha(e);

                const onMove = (e) => handleAlpha(e);
                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
        }

        // Hex input
        if (hexInput) {
            hexInput.addEventListener('input', () => {
                let hex = hexInput.value;
                if (!hex.startsWith('#')) hex = '#' + hex;
                if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
                    const rgb = JTB.Fields.hexToRgb(hex);
                    currentColor.r = rgb.r;
                    currentColor.g = rgb.g;
                    currentColor.b = rgb.b;
                    updateFromRGB();
                }
            });
        }

        // RGB inputs
        [rInput, gInput, bInput].forEach(input => {
            if (input) {
                input.addEventListener('input', () => {
                    currentColor.r = Math.max(0, Math.min(255, parseInt(rInput.value) || 0));
                    currentColor.g = Math.max(0, Math.min(255, parseInt(gInput.value) || 0));
                    currentColor.b = Math.max(0, Math.min(255, parseInt(bInput.value) || 0));
                    updateFromRGB();
                });
            }
        });

        // Alpha input
        if (aInput) {
            aInput.addEventListener('input', () => {
                currentColor.a = Math.max(0, Math.min(100, parseInt(aInput.value) || 0)) / 100;
                updateUI();
            });
        }

        // Text input (hex/rgba)
        textInput.addEventListener('input', () => {
            const value = textInput.value.trim();
            const parsed = JTB.Fields.parseColor(value);
            if (parsed) {
                currentColor.r = parsed.r;
                currentColor.g = parsed.g;
                currentColor.b = parsed.b;
                currentColor.a = parsed.a;
                updateFromRGB();
            }
        });

        // Presets
        presets.forEach(preset => {
            preset.addEventListener('click', () => {
                const color = preset.dataset.color;
                const parsed = JTB.Fields.parseColor(color);
                currentColor.r = parsed.r;
                currentColor.g = parsed.g;
                currentColor.b = parsed.b;
                currentColor.a = parsed.a;
                updateFromRGB();
            });
        });

        // Initial UI update
        updateUI();
    };

    JTB.Settings.openIconPicker = function(wrapper) {
        const name = wrapper.dataset.field;
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const preview = wrapper.querySelector('.jtb-icon-preview');

        // Build category tabs if Feather Icons available
        let categoryTabsHtml = '';
        if (typeof JTB.FeatherIconCategories !== 'undefined') {
            const categories = JTB.getFeatherCategories();
            categoryTabsHtml = `
                <div class="jtb-icon-categories">
                    <button type="button" class="jtb-icon-category-btn active" data-category="all">All</button>
                    ${categories.map(cat => `<button type="button" class="jtb-icon-category-btn" data-category="${cat}">${cat}</button>`).join('')}
                </div>
            `;
        }

        // Create icon picker modal
        // Use Feather icon for close button
        const closeIcon = typeof JTB.getFeatherIcon === 'function' ? JTB.getFeatherIcon('x', 18) : '×';

        const modal = document.createElement('div');
        modal.className = 'jtb-modal jtb-icon-picker-modal';
        modal.innerHTML = `
            <div class="jtb-modal-content">
                <div class="jtb-modal-header">
                    <h3>Choose Icon</h3>
                    <span class="jtb-icon-count">${typeof JTB.FeatherIcons !== 'undefined' ? Object.keys(JTB.FeatherIcons).length : 0} icons</span>
                    <button type="button" class="jtb-modal-close">${closeIcon}</button>
                </div>
                <div class="jtb-modal-body">
                    <input type="text" class="jtb-icon-search" placeholder="Search icons...">
                    ${categoryTabsHtml}
                    <div class="jtb-icon-grid">
                        ${JTB.Settings.getIconsHtml('all')}
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal
        modal.querySelector('.jtb-modal-close').addEventListener('click', () => {
            modal.remove();
        });

        // Click outside to close
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Category switching
        modal.querySelectorAll('.jtb-icon-category-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.dataset.category;

                // Update active state
                modal.querySelectorAll('.jtb-icon-category-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Clear search
                const searchInput = modal.querySelector('.jtb-icon-search');
                searchInput.value = '';

                // Reload icons for category
                const grid = modal.querySelector('.jtb-icon-grid');
                grid.innerHTML = JTB.Settings.getIconsHtml(category);

                // Re-bind click events
                JTB.Settings.bindIconGridEvents(modal, hiddenInput, preview, name);
            });
        });

        // Search
        const searchInput = modal.querySelector('.jtb-icon-search');
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();

            if (typeof JTB.searchFeatherIcons !== 'undefined' && query.length > 0) {
                // Use Feather search
                const matchingIcons = JTB.searchFeatherIcons(query);
                const grid = modal.querySelector('.jtb-icon-grid');

                grid.innerHTML = matchingIcons.map(icon => `
                    <div class="jtb-icon-option" data-icon="${icon}" title="${icon}">
                        ${JTB.getFeatherIcon(icon, 24, 2)}
                        <span class="jtb-icon-name">${icon}</span>
                    </div>
                `).join('');

                // Re-bind click events
                JTB.Settings.bindIconGridEvents(modal, hiddenInput, preview, name);

                // Reset category buttons
                modal.querySelectorAll('.jtb-icon-category-btn').forEach(b => b.classList.remove('active'));
            } else if (query.length === 0) {
                // Reset to all icons
                const activeCategory = modal.querySelector('.jtb-icon-category-btn.active');
                const category = activeCategory ? activeCategory.dataset.category : 'all';
                const grid = modal.querySelector('.jtb-icon-grid');
                grid.innerHTML = JTB.Settings.getIconsHtml(category);
                JTB.Settings.bindIconGridEvents(modal, hiddenInput, preview, name);
            } else {
                // Fallback filtering
                modal.querySelectorAll('.jtb-icon-option').forEach(option => {
                    const iconName = option.dataset.icon.toLowerCase();
                    option.style.display = iconName.includes(query) ? '' : 'none';
                });
            }
        });

        // Bind initial click events
        JTB.Settings.bindIconGridEvents(modal, hiddenInput, preview, name);
    };

    JTB.Settings.bindIconGridEvents = function(modal, hiddenInput, preview, name) {
        modal.querySelectorAll('.jtb-icon-option').forEach(option => {
            option.addEventListener('click', () => {
                const icon = option.dataset.icon;
                hiddenInput.value = icon;

                // Update preview with SVG if Feather available
                if (typeof JTB.getFeatherIcon !== 'undefined') {
                    preview.innerHTML = JTB.getFeatherIcon(icon, 24, 2);
                } else {
                    preview.innerHTML = `<span class="jtb-icon">${icon.charAt(0).toUpperCase()}</span>`;
                }

                JTB.Settings.setValue(name, icon);
                modal.remove();
            });
        });
    };

    JTB.Settings.getIconsHtml = function(selectedCategory) {
        // Use Feather Icons if available
        if (typeof JTB.FeatherIcons !== 'undefined') {
            let icons;
            if (selectedCategory && selectedCategory !== 'all') {
                icons = JTB.getFeatherIconsByCategory(selectedCategory) || [];
            } else {
                icons = JTB.getFeatherIconNames();
            }

            return icons.map(icon => `
                <div class="jtb-icon-option" data-icon="${icon}" title="${icon}">
                    ${JTB.getFeatherIcon(icon, 24, 2)}
                    <span class="jtb-icon-name">${icon}</span>
                </div>
            `).join('');
        }

        // Fallback to basic icons if Feather not loaded
        const icons = [
            'home', 'user', 'settings', 'mail', 'phone',
            'search', 'heart', 'star', 'check', 'x',
            'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down',
            'menu', 'grid', 'list', 'calendar', 'clock'
        ];

        return icons.map(icon => `
            <div class="jtb-icon-option" data-icon="${icon}" title="${icon}">
                <span class="jtb-icon">${icon.charAt(0).toUpperCase()}</span>
                <span class="jtb-icon-name">${icon}</span>
            </div>
        `).join('');
    };

    JTB.Settings.refreshConditions = function() {
        const panel = document.querySelector('.jtb-settings-panel');
        if (!panel) return;

        panel.querySelectorAll('.jtb-field[data-show-if], .jtb-field[data-show-if-not]').forEach(fieldEl => {
            const showIf = fieldEl.dataset.showIf ? JSON.parse(fieldEl.dataset.showIf) : null;
            const showIfNot = fieldEl.dataset.showIfNot ? JSON.parse(fieldEl.dataset.showIfNot) : null;

            let visible = true;

            if (showIf) {
                visible = JTB.Settings.checkConditions({ show_if: showIf });
            }

            if (visible && showIfNot) {
                visible = JTB.Settings.checkConditions({ show_if_not: showIfNot });
            }

            fieldEl.style.display = visible ? '' : 'none';
        });
    };

    // ========================================
    // Responsive/Hover Handling
    // ========================================

    JTB.Settings.switchDeviceInput = function(fieldEl, device) {
        const fieldName = fieldEl.dataset.fieldName;
        const suffix = device === 'desktop' ? '' : '__' + device;
        const fullName = fieldName + suffix;

        const input = fieldEl.querySelector('.jtb-field-input input, .jtb-field-input select, .jtb-field-input textarea');
        if (input) {
            const currentValue = JTB.Settings.getValue(fullName, JTB.Settings.getValue(fieldName, ''));
            input.value = currentValue;
            input.name = fullName;
            input.dataset.field = fullName;
        }
    };

    JTB.Settings.toggleHoverInput = function(fieldEl, isHover) {
        const fieldName = fieldEl.dataset.fieldName;
        const suffix = isHover ? '__hover' : '';
        const fullName = fieldName + suffix;

        const input = fieldEl.querySelector('.jtb-field-input input, .jtb-field-input select');
        if (input) {
            const currentValue = JTB.Settings.getValue(fullName, JTB.Settings.getValue(fieldName, ''));
            input.value = currentValue;
            input.name = fullName;
            input.dataset.field = fullName;
        }
    };

    // ========================================
    // Tab Management
    // ========================================

    JTB.Settings.setActiveTab = function(tabName) {
        JTB.Settings.currentTab = tabName;

        const panel = document.querySelector('.jtb-settings-panel');
        if (!panel) return;

        panel.querySelectorAll('.jtb-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        panel.querySelectorAll('.jtb-tab-content').forEach(content => {
            content.classList.toggle('active', content.dataset.tab === tabName);
        });
    };

    JTB.Settings.close = function() {
        // Cleanup event listeners
        if (JTB.Settings.abortController) {
            JTB.Settings.abortController.abort();
            JTB.Settings.abortController = null;
            JTB.log('Settings panel events cleaned up');
        }

        const panel = document.querySelector('.jtb-settings-panel');
        if (!panel) return;

        JTB.Settings.currentModule = null;
        JTB.Settings.currentConfig = null;

        const emptyIcon = JTB.getFeatherIcon ? JTB.getFeatherIcon('settings', 32, 1.5) : '⚙️';
        panel.innerHTML = `
            <div class="jtb-settings-empty">
                <div class="jtb-empty-icon">${emptyIcon}</div>
                <p>Select a module to edit its settings</p>
            </div>
        `;
    };

    // ========================================
    // Media Library (see media-gallery.js)
    // ========================================
    // The openMediaGallery function is provided by media-gallery.js
    // Upload fields now use JTB.openMediaGallery directly

    // ========================================
    // Utilities
    // ========================================

    JTB.Settings.esc = function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

})();
