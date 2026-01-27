/**
 * JTB Conditions Builder
 * Visual UI for setting template conditions
 */

const JTBConditionsBuilder = {
    conditions: {
        include: [],
        exclude: []
    },
    currentConditionType: 'include',
    pageTypes: {},

    /**
     * Initialize conditions builder
     */
    init(conditions = [], pageTypes = {}) {
        this.pageTypes = pageTypes;

        // Parse conditions
        if (Array.isArray(conditions)) {
            conditions.forEach(condition => {
                const type = condition.condition_type || 'include';
                if (this.conditions[type]) {
                    this.conditions[type].push({
                        id: condition.id,
                        page_type: condition.page_type,
                        object_id: condition.object_id
                    });
                }
            });
        }

        this.renderConditions();
        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Toggle groups
        document.querySelectorAll('.jtb-conditions-panel .jtb-toggle-header').forEach(header => {
            header.addEventListener('click', () => {
                header.parentElement.classList.toggle('open');
            });
        });
    },

    /**
     * Render all conditions
     */
    renderConditions() {
        this.renderConditionList('include');
        this.renderConditionList('exclude');
    },

    /**
     * Render condition list for a type
     */
    renderConditionList(type) {
        const container = document.getElementById(type + 'Conditions');
        if (!container) return;

        container.innerHTML = '';

        this.conditions[type].forEach((condition, index) => {
            const label = this.getConditionLabel(condition);
            const item = document.createElement('div');
            item.className = 'jtb-condition-item';
            item.innerHTML = `
                <span class="jtb-condition-label">${this.escapeHtml(label)}</span>
                <button class="jtb-condition-remove" onclick="JTBConditionsBuilder.removeCondition('${type}', ${index})">&times;</button>
            `;
            container.appendChild(item);
        });

        if (this.conditions[type].length === 0) {
            container.innerHTML = '<p class="jtb-no-conditions">No conditions set</p>';
        }
    },

    /**
     * Get human-readable label for condition
     */
    getConditionLabel(condition) {
        const pageTypeConfig = this.pageTypes[condition.page_type];
        let label = pageTypeConfig ? pageTypeConfig.label : condition.page_type;

        if (condition.object_id) {
            label += ` #${condition.object_id}`;
        }

        return label;
    },

    /**
     * Add condition
     */
    addCondition(type) {
        this.currentConditionType = type;
        this.showConditionModal();
    },

    /**
     * Show condition picker modal
     */
    showConditionModal() {
        const modal = document.getElementById('conditionModal');
        modal.style.display = 'flex';

        // Reset form
        document.getElementById('conditionPageType').value = '';
        document.getElementById('objectSelectWrapper').style.display = 'none';
        document.getElementById('conditionObjectId').innerHTML = '<option value="">All</option>';
    },

    /**
     * Hide condition picker modal
     */
    hideConditionModal() {
        const modal = document.getElementById('conditionModal');
        modal.style.display = 'none';
    },

    /**
     * Handle page type change
     */
    async onPageTypeChange() {
        const select = document.getElementById('conditionPageType');
        const selectedOption = select.options[select.selectedIndex];
        const hasObjects = selectedOption.dataset.hasObjects === 'true';
        const objectWrapper = document.getElementById('objectSelectWrapper');
        const objectSelect = document.getElementById('conditionObjectId');

        if (hasObjects) {
            objectWrapper.style.display = 'block';

            // Load objects for this type
            try {
                const response = await fetch(`/api/jtb/conditions-objects?type=${select.value}`, { credentials: 'include' });
                const result = await response.json();

                objectSelect.innerHTML = '<option value="">All</option>';

                if (result.success && result.objects) {
                    result.objects.forEach(obj => {
                        const option = document.createElement('option');
                        option.value = obj.id;
                        option.textContent = obj.name;
                        objectSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading objects:', error);
            }
        } else {
            objectWrapper.style.display = 'none';
        }
    },

    /**
     * Save condition from modal
     */
    saveCondition() {
        const pageType = document.getElementById('conditionPageType').value;
        const objectId = document.getElementById('conditionObjectId').value || null;

        if (!pageType) {
            alert('Please select a page type');
            return;
        }

        // Check for duplicate
        const exists = this.conditions[this.currentConditionType].some(c =>
            c.page_type === pageType && c.object_id === objectId
        );

        if (exists) {
            alert('This condition already exists');
            return;
        }

        // Add condition
        this.conditions[this.currentConditionType].push({
            page_type: pageType,
            object_id: objectId ? parseInt(objectId) : null
        });

        this.renderConditions();
        this.hideConditionModal();
    },

    /**
     * Remove condition
     */
    removeCondition(type, index) {
        this.conditions[type].splice(index, 1);
        this.renderConditions();
    },

    /**
     * Get all conditions for saving
     */
    getConditionsData() {
        const conditions = [];

        this.conditions.include.forEach(c => {
            conditions.push({
                type: 'include',
                page_type: c.page_type,
                object_id: c.object_id
            });
        });

        this.conditions.exclude.forEach(c => {
            conditions.push({
                type: 'exclude',
                page_type: c.page_type,
                object_id: c.object_id
            });
        });

        return conditions;
    },

    /**
     * Escape HTML
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};
