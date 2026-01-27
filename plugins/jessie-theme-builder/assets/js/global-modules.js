/**
 * JTB Global Modules Manager
 * Handles saving and using global/reusable modules
 */

const JTBGlobalModules = {
    currentFilter: 'all',

    /**
     * Initialize global modules manager
     */
    init() {
        this.bindEvents();
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
                this.filterModules(tab.dataset.type);
            });
        });

        // Search
        const search = document.getElementById('moduleSearch');
        if (search) {
            search.addEventListener('input', () => {
                this.searchModules(search.value);
            });
        }
    },

    /**
     * Filter modules by type
     */
    filterModules(type) {
        this.currentFilter = type;

        document.querySelectorAll('.jtb-global-module-card').forEach(card => {
            if (type === 'all' || card.dataset.type === type) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    },

    /**
     * Search modules by name
     */
    searchModules(query) {
        const q = query.toLowerCase();

        document.querySelectorAll('.jtb-global-module-card').forEach(card => {
            const name = card.querySelector('.jtb-module-name').textContent.toLowerCase();
            const type = card.dataset.type.toLowerCase();

            if (name.includes(q) || type.includes(q)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    },

    /**
     * Edit global module (open in editor)
     */
    edit(id) {
        // TODO: Open module in a special editor view
        this.showNotification('Edit feature coming soon', 'info');
    },

    /**
     * Duplicate global module
     */
    async duplicate(id) {
        const name = prompt('Enter name for the duplicate:');
        if (!name) return;

        try {
            // First get the module
            const getResponse = await fetch(`/api/jtb/global-module-get/${id}`, { credentials: 'include' });
            const getResult = await getResponse.json();

            if (!getResult.success) {
                this.showNotification('Failed to load module', 'error');
                return;
            }

            // Save as new
            const response = await fetch('/api/jtb/global-module-save', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    name: name,
                    type: getResult.module.type,
                    content: getResult.module.content,
                    description: getResult.module.description
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Module duplicated', 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                this.showNotification(result.error || 'Failed to duplicate', 'error');
            }
        } catch (error) {
            this.showNotification('Error duplicating module', 'error');
            console.error(error);
        }
    },

    /**
     * Delete global module
     */
    async delete(id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) {
            return;
        }

        try {
            const response = await fetch('/api/jtb/global-module-delete', {
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
                this.showNotification('Module deleted', 'success');
                // Remove card from DOM
                const card = document.querySelector(`.jtb-global-module-card[data-id="${id}"]`);
                if (card) {
                    card.remove();
                }
            } else {
                this.showNotification(result.error || 'Failed to delete', 'error');
            }
        } catch (error) {
            this.showNotification('Error deleting module', 'error');
            console.error(error);
        }
    },

    /**
     * Save module as global (called from page builder)
     */
    async saveAsGlobal(moduleData, name, description = '') {
        if (!name) {
            name = prompt('Enter a name for this global module:');
            if (!name) return;
        }

        try {
            const response = await fetch('/api/jtb/global-module-save', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.JTB_CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    name: name,
                    type: moduleData.type,
                    content: moduleData,
                    description: description
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Module saved to library', 'success');
                return result.module_id;
            } else {
                this.showNotification(result.error || 'Failed to save', 'error');
                return null;
            }
        } catch (error) {
            this.showNotification('Error saving module', 'error');
            console.error(error);
            return null;
        }
    },

    /**
     * Insert global module into page (called from page builder)
     */
    async insertFromLibrary(id, linked = false) {
        try {
            const response = await fetch(`/api/jtb/global-module-get/${id}`, { credentials: 'include' });
            const result = await response.json();

            if (result.success) {
                if (linked) {
                    // Return linked reference
                    return {
                        type: 'global_module',
                        id: 'gm_' + Math.random().toString(36).substr(2, 8),
                        attrs: {
                            global_module_id: id,
                            linked: true
                        },
                        children: []
                    };
                } else {
                    // Return unlinked copy
                    return this.regenerateIds(result.module.content);
                }
            } else {
                this.showNotification('Failed to load module', 'error');
                return null;
            }
        } catch (error) {
            this.showNotification('Error loading module', 'error');
            console.error(error);
            return null;
        }
    },

    /**
     * Regenerate IDs in content
     */
    regenerateIds(element) {
        if (!element) return element;

        // Clone to avoid mutating original
        element = JSON.parse(JSON.stringify(element));

        if (element.id) {
            const prefix = element.id.split('_')[0] || 'el';
            element.id = prefix + '_' + Math.random().toString(36).substr(2, 8);
        }

        if (Array.isArray(element.children)) {
            element.children = element.children.map(child => this.regenerateIds(child));
        }

        return element;
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
    }
};
