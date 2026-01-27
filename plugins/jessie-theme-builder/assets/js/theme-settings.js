/**
 * JTB Theme Settings JavaScript
 * Handles the global theme settings panel
 */

(function() {
    'use strict';

    const ThemeSettings = {
        settings: window.JTB_SETTINGS || {},
        defaults: window.JTB_DEFAULTS || {},
        csrfToken: window.JTB_CSRF_TOKEN || '',
        isDirty: false,

        init() {
            this.bindNavigation();
            this.bindFields();
            this.bindButtons();
            this.bindModals();
            this.bindKeyboardShortcuts();
            this.updateButtonPreview();
        },

        // ========================================
        // Navigation
        // ========================================

        bindNavigation() {
            document.querySelectorAll('.jtb-settings-nav-item a').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const group = link.dataset.group;
                    this.showSection(group);
                });
            });
        },

        showSection(group) {
            // Update nav
            document.querySelectorAll('.jtb-settings-nav-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`.jtb-settings-nav-item a[data-group="${group}"]`)
                ?.closest('.jtb-settings-nav-item')
                ?.classList.add('active');

            // Update sections
            document.querySelectorAll('.jtb-settings-section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(`section-${group}`)?.classList.add('active');
        },

        // ========================================
        // Fields
        // ========================================

        bindFields() {
            // Color fields
            document.querySelectorAll('.jtb-color-input-wrapper').forEach(wrapper => {
                const colorInput = wrapper.querySelector('input[type="color"]');
                const textInput = wrapper.querySelector('.jtb-color-text');

                if (colorInput && textInput) {
                    colorInput.addEventListener('input', () => {
                        textInput.value = colorInput.value;
                        this.onFieldChange(colorInput);
                    });

                    textInput.addEventListener('input', () => {
                        if (/^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
                            colorInput.value = textInput.value;
                            this.onFieldChange(textInput);
                        }
                    });

                    textInput.addEventListener('blur', () => {
                        if (!/^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
                            textInput.value = colorInput.value;
                        }
                    });
                }
            });

            // Range fields
            document.querySelectorAll('.jtb-range-wrapper').forEach(wrapper => {
                const rangeInput = wrapper.querySelector('input[type="range"]');
                const valueDisplay = wrapper.querySelector('.jtb-range-value');

                if (rangeInput && valueDisplay) {
                    rangeInput.addEventListener('input', () => {
                        const unit = valueDisplay.textContent.replace(/[\d.-]/g, '');
                        valueDisplay.textContent = rangeInput.value + unit;
                        this.onFieldChange(rangeInput);
                    });
                }
            });

            // Select fields
            document.querySelectorAll('.jtb-field-select select').forEach(select => {
                select.addEventListener('change', () => {
                    this.onFieldChange(select);
                });
            });

            // Toggle fields
            document.querySelectorAll('.jtb-field-toggle input').forEach(toggle => {
                toggle.addEventListener('change', () => {
                    this.onFieldChange(toggle);
                });
            });

            // Text fields
            document.querySelectorAll('.jtb-field-text input').forEach(input => {
                input.addEventListener('input', () => {
                    this.onFieldChange(input);
                });
            });
        },

        onFieldChange(input) {
            this.isDirty = true;

            const group = input.dataset.group;
            const key = input.dataset.key;

            // Update internal settings
            if (!this.settings[group]) {
                this.settings[group] = {};
            }

            if (input.type === 'checkbox') {
                this.settings[group][key] = input.checked;
            } else {
                this.settings[group][key] = input.value;
            }

            // Update button preview if in buttons group
            if (group === 'buttons') {
                this.updateButtonPreview();
            }
        },

        updateButtonPreview() {
            const preview = document.getElementById('buttonPreview');
            if (!preview) return;

            const buttons = this.settings.buttons || {};

            preview.style.setProperty('--preview-bg', buttons.button_bg_color || '#7c3aed');
            preview.style.setProperty('--preview-text', buttons.button_text_color || '#ffffff');
            preview.style.setProperty('--preview-border-width', (buttons.button_border_width || 0) + 'px');
            preview.style.setProperty('--preview-border-color', buttons.button_border_color || '#7c3aed');
            preview.style.setProperty('--preview-radius', (buttons.button_border_radius || 8) + 'px');
            preview.style.setProperty('--preview-padding',
                (buttons.button_padding_tb || 12) + 'px ' + (buttons.button_padding_lr || 24) + 'px');
            preview.style.setProperty('--preview-font-size', (buttons.button_font_size || 16) + 'px');
            preview.style.setProperty('--preview-font-weight', buttons.button_font_weight || '600');
            preview.style.setProperty('--preview-hover-bg', buttons.button_hover_bg || '#5b21b6');
            preview.style.setProperty('--preview-hover-text', buttons.button_hover_text || '#ffffff');
            preview.style.setProperty('--preview-hover-border', buttons.button_hover_border || '#5b21b6');
        },

        // ========================================
        // Buttons
        // ========================================

        bindButtons() {
            // Save button
            document.getElementById('saveBtn')?.addEventListener('click', () => {
                this.save();
            });

            // Preview button
            document.getElementById('previewBtn')?.addEventListener('click', () => {
                this.togglePreview();
            });

            // Close preview
            document.getElementById('closePreview')?.addEventListener('click', () => {
                this.closePreview();
            });

            // Export button
            document.getElementById('exportBtn')?.addEventListener('click', () => {
                window.location.href = '/admin/jtb/theme-settings?action=export';
            });

            // Import button
            document.getElementById('importBtn')?.addEventListener('click', () => {
                document.getElementById('importModal')?.classList.add('open');
            });

            // Reset all button
            document.getElementById('resetAllBtn')?.addEventListener('click', () => {
                if (confirm('Are you sure you want to reset ALL theme settings to defaults? This cannot be undone.')) {
                    window.location.href = '/admin/jtb/theme-settings?action=reset&csrf_token=' + encodeURIComponent(this.csrfToken);
                }
            });

            // Reset group buttons
            document.querySelectorAll('.reset-group-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const group = btn.dataset.group;
                    if (confirm(`Are you sure you want to reset ${group} settings to defaults?`)) {
                        window.location.href = `/admin/jtb/theme-settings?action=reset&group=${group}&csrf_token=` + encodeURIComponent(this.csrfToken);
                    }
                });
            });
        },

        // ========================================
        // Save
        // ========================================

        async save() {
            const saveBtn = document.getElementById('saveBtn');
            if (!saveBtn) return;

            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Saving...';
            saveBtn.disabled = true;

            try {
                const response = await fetch('/api/jtb/theme-settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(this.settings)
                });

                const result = await response.json();

                if (result.success) {
                    this.isDirty = false;
                    this.showNotification('Settings saved successfully', 'success');

                    // Update preview if open
                    this.updatePreview();
                } else {
                    throw new Error(result.error || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showNotification('Failed to save settings: ' + error.message, 'error');
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        },

        // ========================================
        // Preview
        // ========================================

        togglePreview() {
            const panel = document.getElementById('previewPanel');
            if (!panel) return;

            if (panel.classList.contains('open')) {
                this.closePreview();
            } else {
                panel.classList.add('open');
                this.updatePreview();
            }
        },

        closePreview() {
            document.getElementById('previewPanel')?.classList.remove('open');
        },

        updatePreview() {
            const iframe = document.getElementById('previewFrame');
            if (!iframe) return;

            // Generate preview HTML with current settings
            const previewHtml = this.generatePreviewHtml();

            iframe.srcdoc = previewHtml;
        },

        generatePreviewHtml() {
            const colors = this.settings.colors || {};
            const typography = this.settings.typography || {};
            const buttons = this.settings.buttons || {};

            return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=${encodeURIComponent(typography.body_font || 'Inter')}:wght@400;500;600;700&family=${encodeURIComponent(typography.heading_font || 'Inter')}:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: ${colors.primary_color || '#7c3aed'};
            --text: ${colors.text_color || '#1f2937'};
            --text-light: ${colors.text_light_color || '#6b7280'};
            --heading: ${colors.heading_color || '#111827'};
            --bg: ${colors.background_color || '#ffffff'};
            --link: ${colors.link_color || '#7c3aed'};
        }
        body {
            margin: 0;
            padding: 30px;
            font-family: "${typography.body_font || 'Inter'}", sans-serif;
            font-size: ${typography.body_size || 16}px;
            line-height: ${typography.body_line_height || 1.6};
            color: var(--text);
            background: var(--bg);
        }
        h1, h2, h3 {
            font-family: "${typography.heading_font || 'Inter'}", sans-serif;
            font-weight: ${typography.heading_weight || 700};
            color: var(--heading);
            margin-top: 0;
        }
        h1 { font-size: ${typography.h1_size || 48}px; }
        h2 { font-size: ${typography.h2_size || 36}px; }
        h3 { font-size: ${typography.h3_size || 28}px; }
        p { color: var(--text-light); }
        a { color: var(--link); }
        .button {
            display: inline-block;
            padding: ${buttons.button_padding_tb || 12}px ${buttons.button_padding_lr || 24}px;
            background: ${buttons.button_bg_color || '#7c3aed'};
            color: ${buttons.button_text_color || '#ffffff'};
            border: ${buttons.button_border_width || 0}px solid ${buttons.button_border_color || '#7c3aed'};
            border-radius: ${buttons.button_border_radius || 8}px;
            font-size: ${buttons.button_font_size || 16}px;
            font-weight: ${buttons.button_font_weight || 600};
            text-decoration: none;
            text-transform: ${buttons.button_text_transform || 'none'};
            cursor: pointer;
        }
        .section { margin-bottom: 40px; }
        .card {
            background: ${colors.surface_color || '#f9fafb'};
            border: 1px solid ${colors.border_color || '#e5e7eb'};
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="section">
        <h1>Heading One</h1>
        <h2>Heading Two</h2>
        <h3>Heading Three</h3>
    </div>
    <div class="section">
        <p>This is a paragraph with some <a href="#">link text</a> to show the typography and color settings. The quick brown fox jumps over the lazy dog.</p>
    </div>
    <div class="section">
        <a href="#" class="button">Primary Button</a>
    </div>
    <div class="card">
        <h3>Card Component</h3>
        <p>This card shows the surface and border colors in action.</p>
    </div>
</body>
</html>
            `;
        },

        // ========================================
        // Modals
        // ========================================

        bindModals() {
            document.querySelectorAll('.jtb-modal-close, .jtb-modal-cancel').forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.closest('.jtb-modal-overlay')?.classList.remove('open');
                });
            });

            document.querySelectorAll('.jtb-modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) {
                        overlay.classList.remove('open');
                    }
                });
            });
        },

        // ========================================
        // Keyboard Shortcuts
        // ========================================

        bindKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl+S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    this.save();
                }

                // Escape to close preview/modals
                if (e.key === 'Escape') {
                    this.closePreview();
                    document.querySelectorAll('.jtb-modal-overlay.open').forEach(m => m.classList.remove('open'));
                }
            });

            // Warn before leaving with unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },

        // ========================================
        // Notifications
        // ========================================

        showNotification(message, type = 'info') {
            // Remove existing notification
            document.querySelector('.jtb-notification')?.remove();

            const notification = document.createElement('div');
            notification.className = `jtb-notification ${type}`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Trigger animation
            requestAnimationFrame(() => {
                notification.classList.add('show');
            });

            // Auto-remove
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    };

    // Add notification styles
    const style = document.createElement('style');
    style.textContent = `
        .jtb-notification {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(120%);
            transition: transform 0.3s ease;
        }
        .jtb-notification.show {
            transform: translateX(0);
        }
        .jtb-notification.success {
            background: #10b981;
            color: #ffffff;
        }
        .jtb-notification.error {
            background: #ef4444;
            color: #ffffff;
        }
        .jtb-notification.info {
            background: #3b82f6;
            color: #ffffff;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(style);

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        ThemeSettings.init();
    });

})();
