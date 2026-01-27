/**
 * JTB Template Manager
 * Handles template list, creation, deletion, and duplication
 */

const JTBTemplateManager = {
    currentFilter: 'all',

    /**
     * Initialize template manager
     */
    init() {
        this.bindEvents();
        this.filterTemplates('all');
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Filter tabs
        document.querySelectorAll('.jtb-template-filters .jtb-category-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.jtb-template-filters .jtb-category-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                this.filterTemplates(tab.dataset.type);
            });
        });

        // Toggle groups
        document.querySelectorAll('.jtb-toggle-header').forEach(header => {
            header.addEventListener('click', () => {
                header.parentElement.classList.toggle('open');
            });
        });
    },

    /**
     * Filter templates by type
     */
    filterTemplates(type) {
        this.currentFilter = type;

        document.querySelectorAll('.jtb-template-section').forEach(section => {
            if (type === 'all' || section.dataset.type === type) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        });
    },

    /**
     * Show create template modal
     */
    showCreateModal(type = null) {
        const modal = document.getElementById('createModal');
        modal.style.display = 'flex';

        // Add 'show' class after a brief delay to trigger CSS transition
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });

        // Pre-select type if provided
        if (type) {
            document.getElementById('newTemplateType').value = type;
        }

        // Focus name input
        setTimeout(() => {
            document.getElementById('newTemplateName').focus();
        }, 100);
    },

    /**
     * Hide create template modal
     */
    hideCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('show');

        // Wait for animation then hide
        setTimeout(() => {
            modal.style.display = 'none';
            // Reset form
            document.getElementById('newTemplateName').value = '';
            document.getElementById('newTemplateDefault').checked = false;
        }, 300);
    },

    /**
     * Create new template
     */
    async createTemplate() {
        const name = document.getElementById('newTemplateName').value.trim();
        const type = document.getElementById('newTemplateType').value;
        const isDefault = document.getElementById('newTemplateDefault').checked;

        if (!name) {
            this.showNotification('Please enter a template name', 'error');
            return;
        }

        try {
            const response = await fetch('/api/jtb/template-save', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    name: name,
                    type: type,
                    is_default: isDefault,
                    content: { version: '1.0', content: [] }
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Template created', 'success');
                // Redirect to editor
                window.location.href = '/admin/jtb/template/edit/' + result.template_id;
            } else {
                this.showNotification(result.error || 'Failed to create template', 'error');
            }
        } catch (error) {
            this.showNotification('Error creating template', 'error');
            console.error(error);
        }
    },

    /**
     * Delete template
     */
    async deleteTemplate(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) {
            return;
        }

        try {
            const response = await fetch('/api/jtb/template-delete', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Template deleted', 'success');
                // Remove card from DOM
                const card = document.querySelector(`.jtb-template-card[data-id="${id}"]`);
                if (card) {
                    card.remove();
                }
                // Refresh page to update counts
                setTimeout(() => window.location.reload(), 500);
            } else {
                this.showNotification(result.error || 'Failed to delete template', 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting template', 'error');
            console.error(error);
        }
    },

    /**
     * Duplicate template
     */
    async duplicateTemplate(id) {
        try {
            const response = await fetch('/api/jtb/template-duplicate', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Template duplicated', 'success');
                // Redirect to new template
                window.location.href = '/admin/jtb/template/edit/' + result.template_id;
            } else {
                this.showNotification(result.error || 'Failed to duplicate template', 'error');
            }
        } catch (error) {
            this.showNotification('Error duplicating template', 'error');
            console.error(error);
        }
    },

    /**
     * Set template as default
     */
    async setDefault(id) {
        try {
            const response = await fetch('/api/jtb/template-set-default', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Default template updated', 'success');
                // Refresh page to update badges
                setTimeout(() => window.location.reload(), 500);
            } else {
                this.showNotification(result.error || 'Failed to set default', 'error');
            }
        } catch (error) {
            this.showNotification('Error setting default', 'error');
            console.error(error);
        }
    },

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const container = document.getElementById('notifications');
        const notification = document.createElement('div');
        notification.className = `jtb-notification ${type}`;
        notification.textContent = message;

        container.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove after delay
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};
