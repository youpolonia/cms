/**
 * TB4 Visual Builder - Field Controls
 * Professional UI Controls for Theme Builder 4.0
 * @version 1.0.0
 *
 * Vanilla JavaScript ES6+ implementation
 * No dependencies required
 */

'use strict';

/* ==========================================================================
   TB4 ICONS - Lucide SVG Icon Library
   ========================================================================== */

const TB4Icons = {
    chevronDown: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>',
    chevronRight: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
    monitor: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>',
    tablet: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>',
    smartphone: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>',
    link: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
    unlink: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18.84 12.25 1.72-1.71h-.02a5.004 5.004 0 0 0-.12-7.07 5.006 5.006 0 0 0-6.95 0l-1.72 1.71"/><path d="m5.17 11.75-1.71 1.71a5.004 5.004 0 0 0 .12 7.07 5.006 5.006 0 0 0 6.95 0l1.71-1.71"/><line x1="8" x2="8" y1="2" y2="5"/><line x1="2" x2="5" y1="8" y2="8"/><line x1="16" x2="16" y1="19" y2="22"/><line x1="19" x2="22" y1="16" y2="16"/></svg>',
    alignLeft: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="15" x2="3" y1="12" y2="12"/><line x1="17" x2="3" y1="18" y2="18"/></svg>',
    alignCenter: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="17" x2="7" y1="12" y2="12"/><line x1="19" x2="5" y1="18" y2="18"/></svg>',
    alignRight: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="21" x2="9" y1="12" y2="12"/><line x1="21" x2="7" y1="18" y2="18"/></svg>',
    alignJustify: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>',
    rotateCcw: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>',
    helpCircle: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>',
    x: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
    check: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
    pipette: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 22 1-1h3l9-9"/><path d="M3 21v-3l9-9"/><path d="m15 6 3.4-3.4a2.1 2.1 0 1 1 3 3L18 9l.4.4a2.1 2.1 0 1 1-3 3l-3.8-3.8a2.1 2.1 0 1 1 3-3l.4.4Z"/></svg>',
    bold: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg>',
    italic: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>',
    underline: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg>',
    type: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/></svg>',
    image: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
    upload: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>',
    trash: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>',

    /**
     * Get icon HTML by name
     * @param {string} name - Icon name
     * @param {number} size - Optional size override
     * @returns {string} SVG HTML string
     */
    get(name, size = null) {
        let svg = this[name] || '';
        if (size && svg) {
            svg = svg.replace(/width="\d+"/, `width="${size}"`);
            svg = svg.replace(/height="\d+"/, `height="${size}"`);
        }
        return svg;
    }
};

/* ==========================================================================
   TB4 TOGGLE - iOS-style Toggle Switch
   ========================================================================== */

class TB4Toggle {
    /**
     * Create a toggle switch
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.value = options.value || false;
        this.label = options.label || '';
        this.disabled = options.disabled || false;
        this.onChange = options.onChange || (() => {});
        this.name = options.name || '';

        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="tb4-toggle" data-field="${this.name}">
                <button type="button" class="tb4-toggle-switch"
                        role="switch"
                        aria-checked="${this.value}"
                        ${this.disabled ? 'disabled' : ''}>
                    <span class="tb4-toggle-track"></span>
                    <span class="tb4-toggle-thumb"></span>
                </button>
                ${this.label ? `<span class="tb4-toggle-label">${this.label}</span>` : ''}
                <input type="hidden" name="${this.name}" value="${this.value ? '1' : '0'}">
            </div>
        `;

        this.switch = this.container.querySelector('.tb4-toggle-switch');
        this.hiddenInput = this.container.querySelector('input[type="hidden"]');
        this.labelEl = this.container.querySelector('.tb4-toggle-label');
    }

    bindEvents() {
        this.switch.addEventListener('click', () => this.toggle());
        this.switch.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                this.toggle();
            }
        });

        if (this.labelEl) {
            this.labelEl.addEventListener('click', () => this.toggle());
        }
    }

    toggle() {
        if (this.disabled) return;
        this.setValue(!this.value);
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.value = Boolean(value);
        this.switch.setAttribute('aria-checked', this.value);
        this.hiddenInput.value = this.value ? '1' : '0';
        this.onChange(this.value);
    }

    setDisabled(disabled) {
        this.disabled = disabled;
        this.switch.disabled = disabled;
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 SELECT - Custom Styled Dropdown
   ========================================================================== */

class TB4Select {
    /**
     * Create a custom select dropdown
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.value = options.value || '';
        this.placeholder = options.placeholder || 'Select...';
        this.options = options.options || [];
        this.groups = options.groups || null;
        this.searchable = options.searchable !== false;
        this.onChange = options.onChange || (() => {});
        this.name = options.name || '';

        this.isOpen = false;
        this.highlightedIndex = -1;
        this.filteredOptions = [...this.options];

        this.render();
        this.bindEvents();
    }

    render() {
        const selectedOption = this.options.find(opt => opt.value === this.value);
        const displayValue = selectedOption ? selectedOption.label : this.placeholder;
        const isPlaceholder = !selectedOption;

        this.container.innerHTML = `
            <div class="tb4-select" data-field="${this.name}">
                <button type="button" class="tb4-select-trigger">
                    <span class="tb4-select-value ${isPlaceholder ? 'placeholder' : ''}">${this.escapeHtml(displayValue)}</span>
                    <span class="tb4-select-arrow">${TB4Icons.chevronDown}</span>
                </button>
                <div class="tb4-select-dropdown">
                    ${this.searchable ? `<input type="text" class="tb4-select-search" placeholder="Search...">` : ''}
                    <div class="tb4-select-options">
                        ${this.renderOptions()}
                    </div>
                </div>
                <input type="hidden" name="${this.name}" value="${this.escapeHtml(this.value)}">
            </div>
        `;

        this.selectEl = this.container.querySelector('.tb4-select');
        this.trigger = this.container.querySelector('.tb4-select-trigger');
        this.dropdown = this.container.querySelector('.tb4-select-dropdown');
        this.valueDisplay = this.container.querySelector('.tb4-select-value');
        this.searchInput = this.container.querySelector('.tb4-select-search');
        this.optionsContainer = this.container.querySelector('.tb4-select-options');
        this.hiddenInput = this.container.querySelector('input[type="hidden"]');
    }

    renderOptions() {
        if (this.groups) {
            return this.groups.map(group => `
                <div class="tb4-select-group">
                    <div class="tb4-select-group-label">${this.escapeHtml(group.label)}</div>
                    ${group.options.map(opt => this.renderOption(opt)).join('')}
                </div>
            `).join('');
        }

        if (this.filteredOptions.length === 0) {
            return '<div class="tb4-select-no-results">No results found</div>';
        }

        return this.filteredOptions.map(opt => this.renderOption(opt)).join('');
    }

    renderOption(opt) {
        const isSelected = opt.value === this.value;
        return `
            <div class="tb4-select-option ${isSelected ? 'selected' : ''}"
                 data-value="${this.escapeHtml(opt.value)}"
                 ${opt.icon ? `style="--option-font: ${opt.value}"` : ''}>
                ${opt.icon ? `<span class="tb4-select-option-icon">${opt.icon}</span>` : ''}
                ${this.escapeHtml(opt.label)}
            </div>
        `;
    }

    bindEvents() {
        // Toggle dropdown
        this.trigger.addEventListener('click', () => this.toggle());

        // Keyboard navigation
        this.trigger.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Option selection
        this.optionsContainer.addEventListener('click', (e) => {
            const option = e.target.closest('.tb4-select-option');
            if (option) {
                this.selectOption(option.dataset.value);
            }
        });

        // Search
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => this.filterOptions(e.target.value));
            this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        }

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });
    }

    handleKeydown(e) {
        const options = this.optionsContainer.querySelectorAll('.tb4-select-option');

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (!this.isOpen) {
                    this.open();
                } else {
                    this.highlightedIndex = Math.min(this.highlightedIndex + 1, options.length - 1);
                    this.updateHighlight(options);
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                this.updateHighlight(options);
                break;
            case 'Enter':
                e.preventDefault();
                if (this.isOpen && this.highlightedIndex >= 0) {
                    const option = options[this.highlightedIndex];
                    if (option) {
                        this.selectOption(option.dataset.value);
                    }
                } else {
                    this.toggle();
                }
                break;
            case 'Escape':
                this.close();
                break;
        }
    }

    updateHighlight(options) {
        options.forEach((opt, i) => {
            opt.classList.toggle('highlighted', i === this.highlightedIndex);
        });

        if (this.highlightedIndex >= 0 && options[this.highlightedIndex]) {
            options[this.highlightedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    filterOptions(query) {
        const lowerQuery = query.toLowerCase();
        this.filteredOptions = this.options.filter(opt =>
            opt.label.toLowerCase().includes(lowerQuery)
        );
        this.optionsContainer.innerHTML = this.renderOptions();
        this.highlightedIndex = -1;
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        this.selectEl.classList.add('open');
        this.highlightedIndex = -1;

        if (this.searchInput) {
            this.searchInput.value = '';
            this.filteredOptions = [...this.options];
            this.optionsContainer.innerHTML = this.renderOptions();
            this.searchInput.focus();
        }

        // Scroll to selected option
        const selected = this.optionsContainer.querySelector('.tb4-select-option.selected');
        if (selected) {
            selected.scrollIntoView({ block: 'nearest' });
        }
    }

    close() {
        this.isOpen = false;
        this.selectEl.classList.remove('open');
        this.trigger.focus();
    }

    selectOption(value) {
        this.value = value;
        const option = this.options.find(opt => opt.value === value);

        if (option) {
            this.valueDisplay.textContent = option.label;
            this.valueDisplay.classList.remove('placeholder');
        }

        this.hiddenInput.value = value;

        // Update selected state
        this.optionsContainer.querySelectorAll('.tb4-select-option').forEach(opt => {
            opt.classList.toggle('selected', opt.dataset.value === value);
        });

        this.close();
        this.onChange(value);
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.selectOption(value);
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 RANGE SLIDER - Custom Styled Slider with Units
   ========================================================================== */

class TB4RangeSlider {
    /**
     * Create a range slider
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.min = options.min ?? 0;
        this.max = options.max ?? 100;
        this.step = options.step ?? 1;
        this.value = options.value ?? this.min;
        this.unit = options.unit || 'px';
        this.showUnit = options.showUnit !== false;
        this.units = options.units || ['px', '%', 'em', 'rem', 'vw', 'vh'];
        this.defaultValue = options.defaultValue ?? this.min;
        this.onChange = options.onChange || (() => {});
        this.name = options.name || '';

        this.isDragging = false;

        this.render();
        this.bindEvents();
        this.updateUI();
    }

    render() {
        const unitOptions = this.units.map(u =>
            `<option value="${u}" ${u === this.unit ? 'selected' : ''}>${u}</option>`
        ).join('');

        this.container.innerHTML = `
            <div class="tb4-range-slider" data-field="${this.name}">
                <div class="tb4-range-track-container">
                    <div class="tb4-range-track">
                        <div class="tb4-range-fill"></div>
                        <div class="tb4-range-thumb" tabindex="0"></div>
                    </div>
                </div>
                <div class="tb4-range-value">
                    <input type="number" class="tb4-range-input"
                           value="${this.value}"
                           min="${this.min}"
                           max="${this.max}"
                           step="${this.step}">
                    ${this.showUnit ? `
                        <select class="tb4-range-unit">
                            ${unitOptions}
                        </select>
                    ` : ''}
                </div>
                <input type="hidden" name="${this.name}" value="${this.value}${this.unit}">
            </div>
        `;

        this.sliderEl = this.container.querySelector('.tb4-range-slider');
        this.track = this.container.querySelector('.tb4-range-track');
        this.fill = this.container.querySelector('.tb4-range-fill');
        this.thumb = this.container.querySelector('.tb4-range-thumb');
        this.input = this.container.querySelector('.tb4-range-input');
        this.unitSelect = this.container.querySelector('.tb4-range-unit');
        this.hiddenInput = this.container.querySelector('input[type="hidden"]');
    }

    bindEvents() {
        // Thumb drag
        this.thumb.addEventListener('mousedown', (e) => this.startDrag(e));
        this.thumb.addEventListener('touchstart', (e) => this.startDrag(e), { passive: false });

        // Track click
        this.track.addEventListener('click', (e) => this.handleTrackClick(e));

        // Input change
        this.input.addEventListener('input', (e) => {
            let val = parseFloat(e.target.value);
            if (!isNaN(val)) {
                val = Math.max(this.min, Math.min(this.max, val));
                this.setValue(val, false);
            }
        });

        // Unit change
        if (this.unitSelect) {
            this.unitSelect.addEventListener('change', (e) => {
                this.unit = e.target.value;
                this.updateHiddenInput();
                this.onChange(this.getValue());
            });
        }

        // Keyboard support
        this.thumb.addEventListener('keydown', (e) => {
            let newValue = this.value;

            switch (e.key) {
                case 'ArrowLeft':
                case 'ArrowDown':
                    e.preventDefault();
                    newValue = Math.max(this.min, this.value - this.step);
                    break;
                case 'ArrowRight':
                case 'ArrowUp':
                    e.preventDefault();
                    newValue = Math.min(this.max, this.value + this.step);
                    break;
                case 'Home':
                    e.preventDefault();
                    newValue = this.min;
                    break;
                case 'End':
                    e.preventDefault();
                    newValue = this.max;
                    break;
            }

            if (newValue !== this.value) {
                this.setValue(newValue);
            }
        });

        // Double-click to reset
        this.thumb.addEventListener('dblclick', () => {
            this.setValue(this.defaultValue);
        });

        // Global mouse/touch events for dragging
        document.addEventListener('mousemove', (e) => this.handleDrag(e));
        document.addEventListener('mouseup', () => this.stopDrag());
        document.addEventListener('touchmove', (e) => this.handleDrag(e), { passive: false });
        document.addEventListener('touchend', () => this.stopDrag());
    }

    startDrag(e) {
        e.preventDefault();
        this.isDragging = true;
        this.thumb.classList.add('dragging');
    }

    handleDrag(e) {
        if (!this.isDragging) return;

        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        const rect = this.track.getBoundingClientRect();
        const percent = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
        const rawValue = this.min + percent * (this.max - this.min);
        const steppedValue = Math.round(rawValue / this.step) * this.step;
        const clampedValue = Math.max(this.min, Math.min(this.max, steppedValue));

        this.setValue(clampedValue);
    }

    stopDrag() {
        if (this.isDragging) {
            this.isDragging = false;
            this.thumb.classList.remove('dragging');
        }
    }

    handleTrackClick(e) {
        if (e.target === this.thumb) return;

        const rect = this.track.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        const rawValue = this.min + percent * (this.max - this.min);
        const steppedValue = Math.round(rawValue / this.step) * this.step;
        const clampedValue = Math.max(this.min, Math.min(this.max, steppedValue));

        this.setValue(clampedValue);
    }

    updateUI() {
        const percent = (this.value - this.min) / (this.max - this.min) * 100;
        this.fill.style.width = `${percent}%`;
        this.thumb.style.left = `${percent}%`;
        this.input.value = this.value;
    }

    updateHiddenInput() {
        this.hiddenInput.value = `${this.value}${this.unit}`;
    }

    getValue() {
        return `${this.value}${this.unit}`;
    }

    getNumericValue() {
        return this.value;
    }

    setValue(value, updateInput = true) {
        this.value = value;
        if (updateInput) {
            this.input.value = value;
        }
        this.updateUI();
        this.updateHiddenInput();
        this.onChange(this.getValue());
    }

    setUnit(unit) {
        this.unit = unit;
        if (this.unitSelect) {
            this.unitSelect.value = unit;
        }
        this.updateHiddenInput();
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 COLOR PICKER - Full-featured Color Picker with Alpha
   ========================================================================== */

class TB4ColorPicker {
    /**
     * Create a color picker
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.value = options.value || '';
        this.showAlpha = options.showAlpha !== false;
        this.swatches = options.swatches || [
            '#ef4444', '#f97316', '#f59e0b', '#eab308',
            '#84cc16', '#22c55e', '#10b981', '#14b8a6',
            '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1',
            '#8b5cf6', '#a855f7', '#d946ef', '#ec4899'
        ];
        this.defaultValue = options.defaultValue || '';
        this.onChange = options.onChange || (() => {});
        this.name = options.name || '';

        this.isOpen = false;

        // Color state
        this.hue = 0;
        this.saturation = 100;
        this.brightness = 100;
        this.alpha = 100;

        this.parseColor(this.value);
        this.render();
        this.bindEvents();
        this.updateUI();
    }

    render() {
        const displayValue = this.value || 'transparent';

        this.container.innerHTML = `
            <div class="tb4-color-picker" data-field="${this.name}">
                <div class="tb4-color-trigger">
                    <div class="tb4-color-swatch"></div>
                    <input type="text" class="tb4-color-value" value="${this.escapeHtml(displayValue)}" readonly>
                    <button type="button" class="tb4-color-clear" title="Clear">${TB4Icons.x}</button>
                </div>
                <div class="tb4-color-dropdown">
                    <div class="tb4-color-preview-large"></div>
                    <div class="tb4-color-spectrum">
                        <div class="tb4-color-spectrum-cursor"></div>
                    </div>
                    <div class="tb4-color-hue-slider">
                        <div class="tb4-color-hue-cursor"></div>
                    </div>
                    ${this.showAlpha ? `
                        <div class="tb4-color-alpha-slider">
                            <div class="tb4-color-alpha-cursor"></div>
                        </div>
                    ` : ''}
                    <div class="tb4-color-inputs">
                        <input type="text" class="tb4-color-hex" placeholder="#000000" value="${this.getHex()}">
                        <div class="tb4-color-rgb">
                            <input type="number" class="tb4-color-r" min="0" max="255" placeholder="R">
                            <input type="number" class="tb4-color-g" min="0" max="255" placeholder="G">
                            <input type="number" class="tb4-color-b" min="0" max="255" placeholder="B">
                        </div>
                        ${this.showAlpha ? `
                            <input type="number" class="tb4-color-opacity" min="0" max="100" placeholder="100" value="${this.alpha}">
                        ` : ''}
                    </div>
                    <div class="tb4-color-swatches">
                        ${this.swatches.map(color => `
                            <div class="tb4-color-swatch-item" style="background: ${color}" data-color="${color}"></div>
                        `).join('')}
                    </div>
                    <div class="tb4-color-actions">
                        <button type="button" class="tb4-color-eyedropper" title="Pick from screen">
                            ${TB4Icons.pipette}
                            <span>Eyedropper</span>
                        </button>
                        <button type="button" class="tb4-color-reset" title="Reset to default">
                            ${TB4Icons.rotateCcw}
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="${this.name}" value="${this.escapeHtml(this.value)}">
            </div>
        `;

        this.pickerEl = this.container.querySelector('.tb4-color-picker');
        this.trigger = this.container.querySelector('.tb4-color-trigger');
        this.swatch = this.container.querySelector('.tb4-color-trigger .tb4-color-swatch');
        this.valueInput = this.container.querySelector('.tb4-color-value');
        this.clearBtn = this.container.querySelector('.tb4-color-clear');
        this.dropdown = this.container.querySelector('.tb4-color-dropdown');
        this.preview = this.container.querySelector('.tb4-color-preview-large');
        this.spectrum = this.container.querySelector('.tb4-color-spectrum');
        this.spectrumCursor = this.container.querySelector('.tb4-color-spectrum-cursor');
        this.hueSlider = this.container.querySelector('.tb4-color-hue-slider');
        this.hueCursor = this.container.querySelector('.tb4-color-hue-cursor');
        this.alphaSlider = this.container.querySelector('.tb4-color-alpha-slider');
        this.alphaCursor = this.container.querySelector('.tb4-color-alpha-cursor');
        this.hexInput = this.container.querySelector('.tb4-color-hex');
        this.rInput = this.container.querySelector('.tb4-color-r');
        this.gInput = this.container.querySelector('.tb4-color-g');
        this.bInput = this.container.querySelector('.tb4-color-b');
        this.opacityInput = this.container.querySelector('.tb4-color-opacity');
        this.swatchItems = this.container.querySelectorAll('.tb4-color-swatch-item');
        this.eyedropperBtn = this.container.querySelector('.tb4-color-eyedropper');
        this.resetBtn = this.container.querySelector('.tb4-color-reset');
        this.hiddenInput = this.container.querySelector('input[type="hidden"]');
    }

    bindEvents() {
        // Toggle dropdown
        this.trigger.addEventListener('click', (e) => {
            if (!e.target.closest('.tb4-color-clear')) {
                this.toggle();
            }
        });

        // Clear button
        this.clearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.clear();
        });

        // Spectrum interaction
        this.spectrum.addEventListener('mousedown', (e) => this.startSpectrumDrag(e));
        this.spectrum.addEventListener('touchstart', (e) => this.startSpectrumDrag(e), { passive: false });

        // Hue slider
        this.hueSlider.addEventListener('mousedown', (e) => this.startHueDrag(e));
        this.hueSlider.addEventListener('touchstart', (e) => this.startHueDrag(e), { passive: false });

        // Alpha slider
        if (this.alphaSlider) {
            this.alphaSlider.addEventListener('mousedown', (e) => this.startAlphaDrag(e));
            this.alphaSlider.addEventListener('touchstart', (e) => this.startAlphaDrag(e), { passive: false });
        }

        // Input changes
        this.hexInput.addEventListener('input', () => this.handleHexInput());
        this.rInput.addEventListener('input', () => this.handleRgbInput());
        this.gInput.addEventListener('input', () => this.handleRgbInput());
        this.bInput.addEventListener('input', () => this.handleRgbInput());
        if (this.opacityInput) {
            this.opacityInput.addEventListener('input', () => this.handleOpacityInput());
        }

        // Swatches
        this.swatchItems.forEach(swatch => {
            swatch.addEventListener('click', () => {
                this.parseColor(swatch.dataset.color);
                this.updateUI();
                this.emitChange();
            });
        });

        // Eyedropper
        this.eyedropperBtn.addEventListener('click', () => this.openEyedropper());

        // Reset
        this.resetBtn.addEventListener('click', () => {
            if (this.defaultValue) {
                this.parseColor(this.defaultValue);
                this.updateUI();
                this.emitChange();
            }
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.close();
            }
        });

        // Global drag events
        document.addEventListener('mousemove', (e) => this.handleDrag(e));
        document.addEventListener('mouseup', () => this.stopDrag());
        document.addEventListener('touchmove', (e) => this.handleDrag(e), { passive: false });
        document.addEventListener('touchend', () => this.stopDrag());
    }

    // Drag state
    startSpectrumDrag(e) {
        e.preventDefault();
        this.isDraggingSpectrum = true;
        this.handleSpectrumMove(e);
    }

    startHueDrag(e) {
        e.preventDefault();
        this.isDraggingHue = true;
        this.handleHueMove(e);
    }

    startAlphaDrag(e) {
        e.preventDefault();
        this.isDraggingAlpha = true;
        this.handleAlphaMove(e);
    }

    handleDrag(e) {
        if (this.isDraggingSpectrum) this.handleSpectrumMove(e);
        if (this.isDraggingHue) this.handleHueMove(e);
        if (this.isDraggingAlpha) this.handleAlphaMove(e);
    }

    stopDrag() {
        this.isDraggingSpectrum = false;
        this.isDraggingHue = false;
        this.isDraggingAlpha = false;
    }

    handleSpectrumMove(e) {
        const rect = this.spectrum.getBoundingClientRect();
        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;

        this.saturation = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
        this.brightness = Math.max(0, Math.min(100, 100 - ((clientY - rect.top) / rect.height) * 100));

        this.updateUI();
        this.emitChange();
    }

    handleHueMove(e) {
        const rect = this.hueSlider.getBoundingClientRect();
        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;

        this.hue = Math.max(0, Math.min(360, ((clientX - rect.left) / rect.width) * 360));

        this.updateUI();
        this.emitChange();
    }

    handleAlphaMove(e) {
        const rect = this.alphaSlider.getBoundingClientRect();
        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;

        this.alpha = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));

        this.updateUI();
        this.emitChange();
    }

    handleHexInput() {
        const hex = this.hexInput.value.replace(/^#/, '');
        if (/^[0-9A-Fa-f]{6}$/.test(hex)) {
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            this.setFromRgb(r, g, b);
            this.updateUI();
            this.emitChange();
        }
    }

    handleRgbInput() {
        const r = parseInt(this.rInput.value) || 0;
        const g = parseInt(this.gInput.value) || 0;
        const b = parseInt(this.bInput.value) || 0;

        if (r >= 0 && r <= 255 && g >= 0 && g <= 255 && b >= 0 && b <= 255) {
            this.setFromRgb(r, g, b);
            this.updateUI();
            this.emitChange();
        }
    }

    handleOpacityInput() {
        this.alpha = Math.max(0, Math.min(100, parseInt(this.opacityInput.value) || 0));
        this.updateUI();
        this.emitChange();
    }

    updateUI() {
        const rgb = this.hsbToRgb(this.hue, this.saturation, this.brightness);
        const hex = this.rgbToHex(rgb.r, rgb.g, rgb.b);
        const colorValue = this.alpha < 100
            ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${this.alpha / 100})`
            : hex;

        // Update swatch
        this.swatch.style.setProperty('--swatch-color', colorValue);

        // Update value input
        this.valueInput.value = colorValue;

        // Update preview
        this.preview.style.setProperty('--preview-color', colorValue);

        // Update spectrum
        this.spectrum.style.setProperty('--spectrum-hue', this.hue);
        this.spectrumCursor.style.left = `${this.saturation}%`;
        this.spectrumCursor.style.top = `${100 - this.brightness}%`;

        // Update hue cursor
        this.hueCursor.style.left = `${(this.hue / 360) * 100}%`;

        // Update alpha slider
        if (this.alphaSlider) {
            this.alphaSlider.style.setProperty('--alpha-color', hex);
            this.alphaCursor.style.left = `${this.alpha}%`;
        }

        // Update inputs
        this.hexInput.value = hex;
        this.rInput.value = rgb.r;
        this.gInput.value = rgb.g;
        this.bInput.value = rgb.b;
        if (this.opacityInput) {
            this.opacityInput.value = Math.round(this.alpha);
        }

        // Update hidden input
        this.value = colorValue;
        this.hiddenInput.value = colorValue;
    }

    parseColor(color) {
        if (!color || color === 'transparent') {
            this.hue = 0;
            this.saturation = 0;
            this.brightness = 100;
            this.alpha = 0;
            return;
        }

        // Parse hex
        if (color.startsWith('#')) {
            const hex = color.slice(1);
            if (hex.length === 6 || hex.length === 8) {
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                const a = hex.length === 8 ? parseInt(hex.substring(6, 8), 16) / 255 * 100 : 100;
                this.setFromRgb(r, g, b);
                this.alpha = a;
            }
            return;
        }

        // Parse rgb/rgba
        const rgbMatch = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
        if (rgbMatch) {
            const r = parseInt(rgbMatch[1]);
            const g = parseInt(rgbMatch[2]);
            const b = parseInt(rgbMatch[3]);
            const a = rgbMatch[4] ? parseFloat(rgbMatch[4]) * 100 : 100;
            this.setFromRgb(r, g, b);
            this.alpha = a;
        }
    }

    setFromRgb(r, g, b) {
        const hsb = this.rgbToHsb(r, g, b);
        this.hue = hsb.h;
        this.saturation = hsb.s;
        this.brightness = hsb.b;
    }

    rgbToHsb(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const d = max - min;

        let h = 0;
        const s = max === 0 ? 0 : (d / max) * 100;
        const v = max * 100;

        if (d !== 0) {
            switch (max) {
                case r:
                    h = ((g - b) / d + (g < b ? 6 : 0)) * 60;
                    break;
                case g:
                    h = ((b - r) / d + 2) * 60;
                    break;
                case b:
                    h = ((r - g) / d + 4) * 60;
                    break;
            }
        }

        return { h, s, b: v };
    }

    hsbToRgb(h, s, b) {
        s /= 100;
        b /= 100;

        const c = b * s;
        const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
        const m = b - c;

        let r = 0, g = 0, bl = 0;

        if (h >= 0 && h < 60) {
            r = c; g = x; bl = 0;
        } else if (h >= 60 && h < 120) {
            r = x; g = c; bl = 0;
        } else if (h >= 120 && h < 180) {
            r = 0; g = c; bl = x;
        } else if (h >= 180 && h < 240) {
            r = 0; g = x; bl = c;
        } else if (h >= 240 && h < 300) {
            r = x; g = 0; bl = c;
        } else {
            r = c; g = 0; bl = x;
        }

        return {
            r: Math.round((r + m) * 255),
            g: Math.round((g + m) * 255),
            b: Math.round((bl + m) * 255)
        };
    }

    rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(x => {
            const hex = x.toString(16);
            return hex.length === 1 ? '0' + hex : hex;
        }).join('');
    }

    getHex() {
        const rgb = this.hsbToRgb(this.hue, this.saturation, this.brightness);
        return this.rgbToHex(rgb.r, rgb.g, rgb.b);
    }

    async openEyedropper() {
        if (!window.EyeDropper) {
            console.warn('EyeDropper API not supported');
            return;
        }

        try {
            const eyeDropper = new window.EyeDropper();
            const result = await eyeDropper.open();
            this.parseColor(result.sRGBHex);
            this.updateUI();
            this.emitChange();
        } catch (e) {
            // User canceled
        }
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        this.pickerEl.classList.add('open');
    }

    close() {
        this.isOpen = false;
        this.pickerEl.classList.remove('open');
    }

    clear() {
        this.value = '';
        this.alpha = 0;
        this.updateUI();
        this.emitChange();
    }

    emitChange() {
        this.onChange(this.value);
    }

    getValue() {
        return this.value;
    }

    setValue(color) {
        this.parseColor(color);
        this.updateUI();
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 BUTTON GROUP - Exclusive Selection Buttons
   ========================================================================== */

class TB4ButtonGroup {
    /**
     * Create a button group
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.value = options.value || '';
        this.options = options.options || [];
        this.onChange = options.onChange || (() => {});
        this.name = options.name || '';

        this.render();
        this.bindEvents();
    }

    render() {
        const buttonsHtml = this.options.map(opt => {
            const isActive = opt.value === this.value;
            return `
                <button type="button"
                        class="tb4-btn-group-item ${isActive ? 'active' : ''}"
                        data-value="${this.escapeHtml(opt.value)}"
                        title="${this.escapeHtml(opt.label || opt.value)}">
                    ${opt.icon || opt.label}
                </button>
            `;
        }).join('');

        this.container.innerHTML = `
            <div class="tb4-btn-group" data-field="${this.name}">
                ${buttonsHtml}
                <input type="hidden" name="${this.name}" value="${this.escapeHtml(this.value)}">
            </div>
        `;

        this.groupEl = this.container.querySelector('.tb4-btn-group');
        this.buttons = this.container.querySelectorAll('.tb4-btn-group-item');
        this.hiddenInput = this.container.querySelector('input[type="hidden"]');
    }

    bindEvents() {
        this.buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                this.setValue(btn.dataset.value);
            });

            btn.addEventListener('keydown', (e) => {
                const btns = Array.from(this.buttons);
                const currentIndex = btns.indexOf(btn);
                let newIndex = currentIndex;

                switch (e.key) {
                    case 'ArrowLeft':
                        e.preventDefault();
                        newIndex = Math.max(0, currentIndex - 1);
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        newIndex = Math.min(btns.length - 1, currentIndex + 1);
                        break;
                }

                if (newIndex !== currentIndex) {
                    btns[newIndex].focus();
                    this.setValue(btns[newIndex].dataset.value);
                }
            });
        });
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.value = value;
        this.hiddenInput.value = value;

        this.buttons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.value === value);
        });

        this.onChange(value);
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 RESPONSIVE TABS - Device Breakpoint Tabs
   ========================================================================== */

class TB4ResponsiveTabs {
    /**
     * Create responsive tabs
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.activeDevice = options.activeDevice || 'desktop';
        this.devices = options.devices || ['desktop', 'tablet', 'mobile'];
        this.hasValues = options.hasValues || {};
        this.onChange = options.onChange || (() => {});

        this.deviceIcons = {
            desktop: TB4Icons.monitor,
            tablet: TB4Icons.tablet,
            mobile: TB4Icons.smartphone
        };

        this.render();
        this.bindEvents();
    }

    render() {
        const tabsHtml = this.devices.map(device => {
            const isActive = device === this.activeDevice;
            const hasValue = this.hasValues[device];
            return `
                <button type="button"
                        class="tb4-resp-tab ${isActive ? 'active' : ''} ${hasValue ? 'has-value' : ''}"
                        data-device="${device}"
                        title="${device.charAt(0).toUpperCase() + device.slice(1)}">
                    ${this.deviceIcons[device]}
                    <span class="tb4-resp-indicator"></span>
                </button>
            `;
        }).join('');

        this.container.innerHTML = `
            <div class="tb4-responsive-tabs">
                ${tabsHtml}
            </div>
        `;

        this.tabsEl = this.container.querySelector('.tb4-responsive-tabs');
        this.tabs = this.container.querySelectorAll('.tb4-resp-tab');
    }

    bindEvents() {
        this.tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                this.setActiveDevice(tab.dataset.device);
            });
        });
    }

    getActiveDevice() {
        return this.activeDevice;
    }

    setActiveDevice(device) {
        this.activeDevice = device;

        this.tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.device === device);
        });

        this.onChange(device);
    }

    setHasValue(device, hasValue) {
        this.hasValues[device] = hasValue;
        const tab = this.container.querySelector(`[data-device="${device}"]`);
        if (tab) {
            tab.classList.toggle('has-value', hasValue);
        }
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 COLLAPSIBLE SECTION - Animated Accordion
   ========================================================================== */

class TB4CollapsibleSection {
    /**
     * Create a collapsible section
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.title = options.title || 'Section';
        this.icon = options.icon || null;
        this.content = options.content || '';
        this.isOpen = options.isOpen !== false;
        this.rememberState = options.rememberState || false;
        this.sectionId = options.sectionId || `tb4-section-${Math.random().toString(36).substr(2, 9)}`;
        this.onToggle = options.onToggle || (() => {});

        if (this.rememberState) {
            const saved = localStorage.getItem(`tb4-section-${this.sectionId}`);
            if (saved !== null) {
                this.isOpen = saved === 'true';
            }
        }

        this.render();
        this.bindEvents();
    }

    render() {
        this.container.innerHTML = `
            <div class="tb4-collapsible ${this.isOpen ? 'open' : ''}" data-section="${this.sectionId}">
                <button type="button" class="tb4-collapsible-header">
                    <span class="tb4-collapsible-arrow">${TB4Icons.chevronRight}</span>
                    ${this.icon ? `<span class="tb4-collapsible-icon">${this.icon}</span>` : ''}
                    <span class="tb4-collapsible-title">${this.escapeHtml(this.title)}</span>
                </button>
                <div class="tb4-collapsible-content">
                    <div class="tb4-collapsible-inner">
                        <div class="tb4-collapsible-body">
                            ${this.content}
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.sectionEl = this.container.querySelector('.tb4-collapsible');
        this.header = this.container.querySelector('.tb4-collapsible-header');
        this.contentEl = this.container.querySelector('.tb4-collapsible-content');
        this.body = this.container.querySelector('.tb4-collapsible-body');
    }

    bindEvents() {
        this.header.addEventListener('click', () => this.toggle());
        this.header.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    toggle() {
        this.isOpen = !this.isOpen;
        this.sectionEl.classList.toggle('open', this.isOpen);

        if (this.rememberState) {
            localStorage.setItem(`tb4-section-${this.sectionId}`, this.isOpen);
        }

        this.onToggle(this.isOpen);
    }

    open() {
        if (!this.isOpen) {
            this.toggle();
        }
    }

    close() {
        if (this.isOpen) {
            this.toggle();
        }
    }

    setContent(content) {
        this.content = content;
        this.body.innerHTML = content;
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 SPACING BOX - Visual Box Model Control
   ========================================================================== */

class TB4SpacingBox {
    /**
     * Create a spacing box control
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.margin = options.margin || { top: 0, right: 0, bottom: 0, left: 0 };
        this.padding = options.padding || { top: 0, right: 0, bottom: 0, left: 0 };
        this.unit = options.unit || 'px';
        this.units = options.units || ['px', '%', 'em', 'rem'];
        this.linked = options.linked !== false;
        this.showResponsive = options.showResponsive !== false;
        this.activeDevice = options.activeDevice || 'desktop';
        this.onChange = options.onChange || (() => {});
        this.name = options.name || 'spacing';

        this.render();
        this.bindEvents();
    }

    render() {
        const unitOptions = this.units.map(u =>
            `<option value="${u}" ${u === this.unit ? 'selected' : ''}>${u}</option>`
        ).join('');

        this.container.innerHTML = `
            <div class="tb4-spacing-box" data-field="${this.name}">
                <div class="tb4-spacing-header">
                    <span class="tb4-spacing-label">Spacing</span>
                    ${this.showResponsive ? `
                        <div class="tb4-responsive-tabs-container"></div>
                    ` : ''}
                </div>
                <div class="tb4-spacing-visual">
                    <div class="tb4-spacing-margin">
                        <span class="tb4-spacing-margin-label">MARGIN</span>
                        <input type="number" class="tb4-spacing-input tb4-margin-top"
                               data-side="margin-top" value="${this.margin.top}" min="0">
                        <input type="number" class="tb4-spacing-input tb4-margin-right"
                               data-side="margin-right" value="${this.margin.right}" min="0">
                        <input type="number" class="tb4-spacing-input tb4-margin-bottom"
                               data-side="margin-bottom" value="${this.margin.bottom}" min="0">
                        <input type="number" class="tb4-spacing-input tb4-margin-left"
                               data-side="margin-left" value="${this.margin.left}" min="0">
                        <div class="tb4-spacing-padding">
                            <span class="tb4-spacing-padding-label">PADDING</span>
                            <input type="number" class="tb4-spacing-input tb4-padding-top"
                                   data-side="padding-top" value="${this.padding.top}" min="0">
                            <input type="number" class="tb4-spacing-input tb4-padding-right"
                                   data-side="padding-right" value="${this.padding.right}" min="0">
                            <input type="number" class="tb4-spacing-input tb4-padding-bottom"
                                   data-side="padding-bottom" value="${this.padding.bottom}" min="0">
                            <input type="number" class="tb4-spacing-input tb4-padding-left"
                                   data-side="padding-left" value="${this.padding.left}" min="0">
                            <div class="tb4-spacing-content">
                                <span>CONTENT</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tb4-spacing-controls">
                    <button type="button" class="tb4-spacing-link ${this.linked ? 'active' : ''}" title="Link all values">
                        ${this.linked ? TB4Icons.link : TB4Icons.unlink}
                    </button>
                    <select class="tb4-spacing-unit">
                        ${unitOptions}
                    </select>
                </div>
                <input type="hidden" name="${this.name}_margin" value="${JSON.stringify(this.margin)}">
                <input type="hidden" name="${this.name}_padding" value="${JSON.stringify(this.padding)}">
            </div>
        `;

        this.spacingEl = this.container.querySelector('.tb4-spacing-box');
        this.inputs = this.container.querySelectorAll('.tb4-spacing-input');
        this.linkBtn = this.container.querySelector('.tb4-spacing-link');
        this.unitSelect = this.container.querySelector('.tb4-spacing-unit');
        this.marginHidden = this.container.querySelector(`input[name="${this.name}_margin"]`);
        this.paddingHidden = this.container.querySelector(`input[name="${this.name}_padding"]`);

        // Initialize responsive tabs if needed
        if (this.showResponsive) {
            const tabsContainer = this.container.querySelector('.tb4-responsive-tabs-container');
            this.responsiveTabs = new TB4ResponsiveTabs(tabsContainer, {
                activeDevice: this.activeDevice,
                onChange: (device) => {
                    this.activeDevice = device;
                    // Could load device-specific values here
                }
            });
        }
    }

    bindEvents() {
        this.inputs.forEach(input => {
            input.addEventListener('input', (e) => this.handleInputChange(e));
            input.addEventListener('focus', (e) => e.target.select());
        });

        this.linkBtn.addEventListener('click', () => this.toggleLink());

        this.unitSelect.addEventListener('change', (e) => {
            this.unit = e.target.value;
            this.emitChange();
        });
    }

    handleInputChange(e) {
        const input = e.target;
        const side = input.dataset.side;
        const value = parseInt(input.value) || 0;
        const [type, position] = side.split('-');

        if (this.linked) {
            // Update all inputs of the same type
            if (type === 'margin') {
                this.margin = { top: value, right: value, bottom: value, left: value };
                this.inputs.forEach(inp => {
                    if (inp.dataset.side.startsWith('margin')) {
                        inp.value = value;
                    }
                });
            } else {
                this.padding = { top: value, right: value, bottom: value, left: value };
                this.inputs.forEach(inp => {
                    if (inp.dataset.side.startsWith('padding')) {
                        inp.value = value;
                    }
                });
            }
        } else {
            if (type === 'margin') {
                this.margin[position] = value;
            } else {
                this.padding[position] = value;
            }
        }

        this.updateHiddenInputs();
        this.emitChange();
    }

    toggleLink() {
        this.linked = !this.linked;
        this.linkBtn.classList.toggle('active', this.linked);
        this.linkBtn.innerHTML = this.linked ? TB4Icons.link : TB4Icons.unlink;
    }

    updateHiddenInputs() {
        this.marginHidden.value = JSON.stringify(this.margin);
        this.paddingHidden.value = JSON.stringify(this.padding);
    }

    emitChange() {
        this.onChange({
            margin: this.margin,
            padding: this.padding,
            unit: this.unit
        });
    }

    getValue() {
        return {
            margin: this.margin,
            padding: this.padding,
            unit: this.unit
        };
    }

    setValue(values) {
        if (values.margin) this.margin = values.margin;
        if (values.padding) this.padding = values.padding;
        if (values.unit) this.unit = values.unit;

        // Update inputs
        this.inputs.forEach(input => {
            const [type, position] = input.dataset.side.split('-');
            if (type === 'margin') {
                input.value = this.margin[position];
            } else {
                input.value = this.padding[position];
            }
        });

        this.unitSelect.value = this.unit;
        this.updateHiddenInputs();
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 TOOLTIP - Hover Tooltip System
   ========================================================================== */

class TB4Tooltip {
    static instance = null;
    static showDelay = 300;
    static hideDelay = 100;

    /**
     * Initialize tooltip system
     */
    static init() {
        if (TB4Tooltip.instance) return;
        TB4Tooltip.instance = true;

        // Create tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'tb4-tooltip';
        tooltip.setAttribute('role', 'tooltip');
        document.body.appendChild(tooltip);
        TB4Tooltip.element = tooltip;

        TB4Tooltip.showTimeout = null;
        TB4Tooltip.hideTimeout = null;
        TB4Tooltip.currentTrigger = null;

        // Event delegation for [data-tb4-tooltip] elements
        document.addEventListener('mouseenter', TB4Tooltip.handleMouseEnter, true);
        document.addEventListener('mouseleave', TB4Tooltip.handleMouseLeave, true);
        document.addEventListener('focusin', TB4Tooltip.handleFocus);
        document.addEventListener('focusout', TB4Tooltip.handleBlur);
    }

    static handleMouseEnter(e) {
        const trigger = e.target.closest('[data-tb4-tooltip]');
        if (!trigger) return;

        clearTimeout(TB4Tooltip.hideTimeout);
        TB4Tooltip.showTimeout = setTimeout(() => {
            TB4Tooltip.show(trigger);
        }, TB4Tooltip.showDelay);
    }

    static handleMouseLeave(e) {
        const trigger = e.target.closest('[data-tb4-tooltip]');
        if (!trigger) return;

        clearTimeout(TB4Tooltip.showTimeout);
        TB4Tooltip.hideTimeout = setTimeout(() => {
            TB4Tooltip.hide();
        }, TB4Tooltip.hideDelay);
    }

    static handleFocus(e) {
        const trigger = e.target.closest('[data-tb4-tooltip]');
        if (trigger) {
            TB4Tooltip.show(trigger);
        }
    }

    static handleBlur(e) {
        const trigger = e.target.closest('[data-tb4-tooltip]');
        if (trigger) {
            TB4Tooltip.hide();
        }
    }

    static show(trigger) {
        const text = trigger.dataset.tb4Tooltip;
        const position = trigger.dataset.tooltipPosition || 'top';

        if (!text) return;

        TB4Tooltip.element.textContent = text;
        TB4Tooltip.element.setAttribute('data-position', position);
        TB4Tooltip.currentTrigger = trigger;

        // Position tooltip
        const triggerRect = trigger.getBoundingClientRect();
        const tooltipRect = TB4Tooltip.element.getBoundingClientRect();

        let top, left;

        switch (position) {
            case 'top':
                top = triggerRect.top - tooltipRect.height - 8;
                left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
                break;
            case 'bottom':
                top = triggerRect.bottom + 8;
                left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
                break;
            case 'left':
                top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
                left = triggerRect.left - tooltipRect.width - 8;
                break;
            case 'right':
                top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
                left = triggerRect.right + 8;
                break;
        }

        // Keep in viewport
        top = Math.max(8, Math.min(window.innerHeight - tooltipRect.height - 8, top));
        left = Math.max(8, Math.min(window.innerWidth - tooltipRect.width - 8, left));

        TB4Tooltip.element.style.top = `${top}px`;
        TB4Tooltip.element.style.left = `${left}px`;
        TB4Tooltip.element.classList.add('visible');
    }

    static hide() {
        TB4Tooltip.element.classList.remove('visible');
        TB4Tooltip.currentTrigger = null;
    }
}

/* ==========================================================================
   TB4 RESET BUTTON - Reset to Default Value
   ========================================================================== */

class TB4ResetButton {
    /**
     * Create a reset button
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.defaultValue = options.defaultValue;
        this.currentValue = options.currentValue;
        this.onReset = options.onReset || (() => {});

        this.render();
        this.bindEvents();
        this.updateVisibility();
    }

    render() {
        this.container.innerHTML = `
            <button type="button" class="tb4-reset-btn" title="Reset to default" data-tb4-tooltip="Reset to default" data-tooltip-position="left">
                ${TB4Icons.rotateCcw}
            </button>
        `;

        this.button = this.container.querySelector('.tb4-reset-btn');
    }

    bindEvents() {
        this.button.addEventListener('click', () => {
            this.onReset(this.defaultValue);
        });
    }

    updateVisibility() {
        const isDifferent = this.currentValue !== this.defaultValue;
        this.button.classList.toggle('visible', isDifferent);
    }

    setCurrentValue(value) {
        this.currentValue = value;
        this.updateVisibility();
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 FIELDS - Main Controller and Renderer
   ========================================================================== */

const TB4Fields = {
    instances: new Map(),

    /**
     * Initialize all field controls in a container
     * @param {HTMLElement} container - Container element
     */
    initAll(container) {
        console.log('[TB4Fields] initAll called on container:', container?.className || container?.id || 'unknown');

        // Initialize tooltips
        if (typeof TB4Tooltip !== 'undefined') {
            TB4Tooltip.init();
        }

        // Find and initialize all field containers
        const fields = container.querySelectorAll('[data-tb4-field]');
        console.log('[TB4Fields] Found', fields.length, 'fields to initialize');

        fields.forEach((el, i) => {
            console.log('[TB4Fields] Initializing field', i + 1, ':', el.dataset.tb4Field);
            this.init(el);
        });
    },

    /**
     * Initialize a single field
     * @param {HTMLElement} element - Field container element
     */
    init(element) {
        const type = element.dataset.tb4Field;
        const name = element.dataset.fieldName || '';
        const options = this.parseOptions(element);

        let instance;

        switch (type) {
            case 'color':
                instance = new TB4ColorPicker(element, { ...options, name });
                break;
            case 'range':
                instance = new TB4RangeSlider(element, { ...options, name });
                break;
            case 'toggle':
                instance = new TB4Toggle(element, { ...options, name });
                break;
            case 'select':
                instance = new TB4Select(element, { ...options, name });
                break;
            case 'button-group':
                instance = new TB4ButtonGroup(element, { ...options, name });
                break;
            case 'responsive-tabs':
                instance = new TB4ResponsiveTabs(element, options);
                break;
            case 'collapsible':
                instance = new TB4CollapsibleSection(element, options);
                break;
            case 'spacing':
                instance = new TB4SpacingBox(element, { ...options, name });
                break;
            case 'animation':
                instance = new TB4AnimationField(element, { ...options, name });
                break;
            case 'typography':
                instance = new TB4TypographyField(element, { ...options, name });
                break;
            case 'custom-css':
                instance = new TB4CustomCSSField(element, { ...options, name });
                break;
        }

        if (instance) {
            this.instances.set(element, instance);
        }

        return instance;
    },

    /**
     * Parse data attributes to options object
     * @param {HTMLElement} element - Element with data attributes
     * @returns {Object} Options object
     */
    parseOptions(element) {
        const options = {};

        // Parse data-options JSON if present
        if (element.dataset.options) {
            try {
                Object.assign(options, JSON.parse(element.dataset.options));
            } catch (e) {
                console.warn('Failed to parse data-options:', e);
            }
        }

        // Parse individual data attributes
        if (element.dataset.value !== undefined) options.value = element.dataset.value;
        if (element.dataset.min !== undefined) options.min = parseFloat(element.dataset.min);
        if (element.dataset.max !== undefined) options.max = parseFloat(element.dataset.max);
        if (element.dataset.step !== undefined) options.step = parseFloat(element.dataset.step);
        if (element.dataset.unit !== undefined) options.unit = element.dataset.unit;
        if (element.dataset.label !== undefined) options.label = element.dataset.label;
        if (element.dataset.placeholder !== undefined) options.placeholder = element.dataset.placeholder;

        return options;
    },

    /**
     * Get instance by element
     * @param {HTMLElement} element - Field container element
     * @returns {Object} Field instance
     */
    getInstance(element) {
        return this.instances.get(element);
    },

    /**
     * Destroy instance
     * @param {HTMLElement} element - Field container element
     */
    destroy(element) {
        const instance = this.instances.get(element);
        if (instance && instance.destroy) {
            instance.destroy();
        }
        this.instances.delete(element);
    },

    /**
     * Destroy all instances in a container
     * @param {HTMLElement} container - Container element
     */
    destroyAll(container) {
        container.querySelectorAll('[data-tb4-field]').forEach(el => {
            this.destroy(el);
        });
    },

    /* ======================================================================
       RENDER METHODS - Generate HTML for field types
       ====================================================================== */

    /**
     * Render a color picker field
     * @param {string} name - Field name
     * @param {string} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderColorPicker(name, value, field = {}) {
        const options = JSON.stringify({
            value: value || '',
            showAlpha: field.showAlpha !== false,
            swatches: field.swatches || null,
            defaultValue: field.default || ''
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="color" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a range slider field
     * @param {string} name - Field name
     * @param {string|number} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderRangeSlider(name, value, field = {}) {
        const options = JSON.stringify({
            value: parseFloat(value) || field.min || 0,
            min: field.min || 0,
            max: field.max || 100,
            step: field.step || 1,
            unit: field.unit || 'px',
            showUnit: field.showUnit !== false,
            units: field.units || ['px', '%', 'em', 'rem'],
            defaultValue: field.default || field.min || 0
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="range" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a toggle switch field
     * @param {string} name - Field name
     * @param {boolean} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderToggle(name, value, field = {}) {
        const options = JSON.stringify({
            value: Boolean(value),
            label: field.toggleLabel || '',
            disabled: field.disabled || false
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="toggle" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a select dropdown field
     * @param {string} name - Field name
     * @param {string} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderSelect(name, value, field = {}) {
        let selectOptions = [];

        // Handle both array and object formats
        if (Array.isArray(field.options)) {
            selectOptions = field.options;
        } else if (typeof field.options === 'object') {
            selectOptions = Object.entries(field.options).map(([val, label]) => ({
                value: val,
                label: label
            }));
        }

        const options = JSON.stringify({
            value: value || '',
            placeholder: field.placeholder || 'Select...',
            options: selectOptions,
            groups: field.groups || null,
            searchable: field.searchable !== false
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="select" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a button group field
     * @param {string} name - Field name
     * @param {string} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderButtonGroup(name, value, field = {}) {
        const options = JSON.stringify({
            value: value || '',
            options: field.options || []
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="button-group" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render text alignment button group
     * @param {string} name - Field name
     * @param {string} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderTextAlign(name, value, field = {}) {
        return this.renderButtonGroup(name, value, {
            ...field,
            options: [
                { value: 'left', icon: TB4Icons.alignLeft, label: 'Align Left' },
                { value: 'center', icon: TB4Icons.alignCenter, label: 'Align Center' },
                { value: 'right', icon: TB4Icons.alignRight, label: 'Align Right' },
                { value: 'justify', icon: TB4Icons.alignJustify, label: 'Justify' }
            ]
        });
    },

    /**
     * Render a spacing box field
     * @param {string} name - Field name
     * @param {Object} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderSpacingBox(name, value, field = {}) {
        const options = JSON.stringify({
            margin: value?.margin || { top: 0, right: 0, bottom: 0, left: 0 },
            padding: value?.padding || { top: 0, right: 0, bottom: 0, left: 0 },
            unit: value?.unit || 'px',
            linked: field.linked !== false,
            showResponsive: field.showResponsive !== false
        });

        return `
            <div class="tb4-field-wrapper">
                <div data-tb4-field="spacing" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a collapsible section
     * @param {string} title - Section title
     * @param {string} content - Section content HTML
     * @param {Object} options - Section options
     * @returns {string} HTML string
     */
    renderCollapsibleSection(title, content, options = {}) {
        const sectionId = options.sectionId || `section-${Math.random().toString(36).substr(2, 9)}`;
        const isOpen = options.isOpen !== false;
        const icon = options.icon || '';

        return `
            <div data-tb4-field="collapsible"
                 data-options='${JSON.stringify({ title, content, isOpen, icon, sectionId })}'></div>
        `;
    },

    /**
     * Render an animation field
     * @param {string} name - Field name
     * @param {Object} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderAnimationField(name, value, field = {}) {
        const options = JSON.stringify({
            value: value || {
                style: 'none',
                duration: 300,
                delay: 0,
                easing: 'ease',
                iteration: '1',
                direction: 'normal',
                trigger: 'load'
            }
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="animation" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a typography field
     * @param {string} name - Field name
     * @param {Object} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderTypographyField(name, value, field = {}) {
        const defaultValue = {
            fontFamily: 'inherit',
            fontSize: { desktop: '16px', tablet: '', mobile: '' },
            fontWeight: '400',
            fontStyle: 'normal',
            lineHeight: { desktop: '1.5', tablet: '', mobile: '' },
            letterSpacing: { desktop: '0px', tablet: '', mobile: '' },
            textAlign: { desktop: 'left', tablet: '', mobile: '' },
            textTransform: 'none',
            textDecoration: 'none',
            color: { normal: '', hover: '' }
        };

        const options = JSON.stringify({
            value: value || defaultValue
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="typography" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Render a custom CSS field
     * @param {string} name - Field name
     * @param {Object} value - Current value
     * @param {Object} field - Field configuration
     * @returns {string} HTML string
     */
    renderCustomCSSField(name, value, field = {}) {
        const targets = field.targets || [
            { key: 'wrapper', label: 'Wrapper', selector: '.module-wrapper' },
            { key: 'content', label: 'Content', selector: '.module-content' }
        ];

        const options = JSON.stringify({
            value: value || {},
            targets: targets
        });

        return `
            <div class="tb4-field-wrapper">
                ${field.label ? `<label class="tb4-field-label">${this.escapeHtml(field.label)}</label>` : ''}
                <div data-tb4-field="custom-css" data-field-name="${this.escapeHtml(name)}" data-options='${options}'></div>
                ${field.description ? `<p class="tb4-field-description">${this.escapeHtml(field.description)}</p>` : ''}
            </div>
        `;
    },

    /**
     * Escape HTML entities
     * @param {string} str - String to escape
     * @returns {string} Escaped string
     */
    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
};

/* ==========================================================================
   TB4 ANIMATION FIELD - Animation Control Panel
   ========================================================================== */

class TB4AnimationField {
    /**
     * Create an animation field control
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.name = options.name || 'animation';
        this.onChange = options.onChange || (() => {});

        // Animation properties
        this.value = {
            type: options.value?.type || 'none',
            duration: options.value?.duration || 300,
            delay: options.value?.delay || 0,
            easing: options.value?.easing || 'ease',
            iteration: options.value?.iteration || '1',
            direction: options.value?.direction || 'normal',
            trigger: options.value?.trigger || 'load'
        };

        // Available animation styles
        this.animationStyles = [
            { value: 'none', label: 'None' },
            { value: 'fade', label: 'Fade In' },
            { value: 'slide-up', label: 'Slide Up' },
            { value: 'slide-down', label: 'Slide Down' },
            { value: 'slide-left', label: 'Slide Left' },
            { value: 'slide-right', label: 'Slide Right' },
            { value: 'zoom-in', label: 'Zoom In' },
            { value: 'zoom-out', label: 'Zoom Out' },
            { value: 'bounce', label: 'Bounce' },
            { value: 'flip', label: 'Flip' },
            { value: 'rotate', label: 'Rotate' },
            { value: 'pulse', label: 'Pulse' }
        ];

        this.easingOptions = [
            { value: 'ease', label: 'Ease' },
            { value: 'ease-in', label: 'Ease In' },
            { value: 'ease-out', label: 'Ease Out' },
            { value: 'ease-in-out', label: 'Ease In Out' },
            { value: 'linear', label: 'Linear' }
        ];

        this.iterationOptions = [
            { value: '1', label: '1' },
            { value: '2', label: '2' },
            { value: '3', label: '3' },
            { value: 'infinite', label: 'Infinite' }
        ];

        this.directionOptions = [
            { value: 'normal', label: 'Normal' },
            { value: 'reverse', label: 'Reverse' },
            { value: 'alternate', label: 'Alternate' }
        ];

        this.triggerOptions = [
            { value: 'load', label: 'On Load' },
            { value: 'scroll', label: 'On Scroll' },
            { value: 'hover', label: 'On Hover' }
        ];

        this.render();
        this.bindEvents();
    }

    render() {
        const styleOptions = this.animationStyles.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.type ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const easingOpts = this.easingOptions.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.easing ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const iterationOpts = this.iterationOptions.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.iteration ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const directionOpts = this.directionOptions.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.direction ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const triggerOpts = this.triggerOptions.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.trigger ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        this.container.innerHTML = `
            <div class="tb4-animation-field" data-field="${this.name}">
                <div class="tb4-animation-row">
                    <label class="tb4-field-label">Animation Style</label>
                    <select class="tb4-animation-style tb4-select-native">
                        ${styleOptions}
                    </select>
                </div>

                <div class="tb4-animation-row">
                    <label class="tb4-field-label">Duration</label>
                    <div class="tb4-animation-slider-row">
                        <input type="range" class="tb4-animation-duration-slider"
                               min="0" max="3000" step="50" value="${this.value.duration}">
                        <span class="tb4-animation-duration-value">${this.value.duration}ms</span>
                    </div>
                </div>

                <div class="tb4-animation-row">
                    <label class="tb4-field-label">Delay</label>
                    <div class="tb4-animation-slider-row">
                        <input type="range" class="tb4-animation-delay-slider"
                               min="0" max="3000" step="50" value="${this.value.delay}">
                        <span class="tb4-animation-delay-value">${this.value.delay}ms</span>
                    </div>
                </div>

                <div class="tb4-animation-row tb4-animation-row-half">
                    <div class="tb4-animation-half">
                        <label class="tb4-field-label">Easing</label>
                        <select class="tb4-animation-easing tb4-select-native">
                            ${easingOpts}
                        </select>
                    </div>
                    <div class="tb4-animation-half">
                        <label class="tb4-field-label">Iteration</label>
                        <select class="tb4-animation-iteration tb4-select-native">
                            ${iterationOpts}
                        </select>
                    </div>
                </div>

                <div class="tb4-animation-row tb4-animation-row-half">
                    <div class="tb4-animation-half">
                        <label class="tb4-field-label">Direction</label>
                        <select class="tb4-animation-direction tb4-select-native">
                            ${directionOpts}
                        </select>
                    </div>
                    <div class="tb4-animation-half">
                        <label class="tb4-field-label">Trigger</label>
                        <select class="tb4-animation-trigger tb4-select-native">
                            ${triggerOpts}
                        </select>
                    </div>
                </div>

                <div class="tb4-animation-preview-container">
                    <button type="button" class="tb4-animation-preview-btn">
                        ${TB4Icons.get('rotateCcw', 14)} Preview Animation
                    </button>
                    <div class="tb4-animation-preview-box">
                        <div class="tb4-animation-preview-element">Sample</div>
                    </div>
                </div>

                <input type="hidden" name="${this.name}" value='${JSON.stringify(this.value)}'>
            </div>
        `;

        // Cache DOM elements
        this.fieldEl = this.container.querySelector('.tb4-animation-field');
        this.styleSelect = this.container.querySelector('.tb4-animation-style');
        this.durationSlider = this.container.querySelector('.tb4-animation-duration-slider');
        this.durationValue = this.container.querySelector('.tb4-animation-duration-value');
        this.delaySlider = this.container.querySelector('.tb4-animation-delay-slider');
        this.delayValue = this.container.querySelector('.tb4-animation-delay-value');
        this.easingSelect = this.container.querySelector('.tb4-animation-easing');
        this.iterationSelect = this.container.querySelector('.tb4-animation-iteration');
        this.directionSelect = this.container.querySelector('.tb4-animation-direction');
        this.triggerSelect = this.container.querySelector('.tb4-animation-trigger');
        this.previewBtn = this.container.querySelector('.tb4-animation-preview-btn');
        this.previewElement = this.container.querySelector('.tb4-animation-preview-element');
        this.hiddenInput = this.container.querySelector(`input[name="${this.name}"]`);
    }

    bindEvents() {
        // Style change
        this.styleSelect.addEventListener('change', (e) => {
            this.value.type = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Duration slider
        this.durationSlider.addEventListener('input', (e) => {
            this.value.duration = parseInt(e.target.value);
            this.durationValue.textContent = `${this.value.duration}ms`;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Delay slider
        this.delaySlider.addEventListener('input', (e) => {
            this.value.delay = parseInt(e.target.value);
            this.delayValue.textContent = `${this.value.delay}ms`;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Easing change
        this.easingSelect.addEventListener('change', (e) => {
            this.value.easing = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Iteration change
        this.iterationSelect.addEventListener('change', (e) => {
            this.value.iteration = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Direction change
        this.directionSelect.addEventListener('change', (e) => {
            this.value.direction = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Trigger change
        this.triggerSelect.addEventListener('change', (e) => {
            this.value.trigger = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Preview button
        this.previewBtn.addEventListener('click', () => this.playPreview());
    }

    playPreview() {
        if (this.value.type === 'none') return;

        // Remove any existing animation class
        this.previewElement.className = 'tb4-animation-preview-element';

        // Force reflow
        void this.previewElement.offsetWidth;

        // Apply animation styles
        const animationName = `tb4-anim-${this.value.type}`;
        const duration = `${this.value.duration}ms`;
        const delay = `${this.value.delay}ms`;
        const easing = this.value.easing;
        const iteration = this.value.iteration;
        const direction = this.value.direction;

        this.previewElement.style.animation = `${animationName} ${duration} ${easing} ${delay} ${iteration} ${direction}`;
        this.previewElement.classList.add('tb4-animating');

        // Remove animation after completion (for non-infinite)
        if (this.value.iteration !== 'infinite') {
            const totalTime = this.value.duration + this.value.delay;
            const iterations = parseInt(this.value.iteration) || 1;
            setTimeout(() => {
                this.previewElement.style.animation = '';
                this.previewElement.classList.remove('tb4-animating');
            }, totalTime * iterations + 100);
        }
    }

    updateHiddenInput() {
        this.hiddenInput.value = JSON.stringify(this.value);
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.value = { ...this.value, ...value };

        // Update UI elements
        this.styleSelect.value = this.value.type;
        this.durationSlider.value = this.value.duration;
        this.durationValue.textContent = `${this.value.duration}ms`;
        this.delaySlider.value = this.value.delay;
        this.delayValue.textContent = `${this.value.delay}ms`;
        this.easingSelect.value = this.value.easing;
        this.iterationSelect.value = this.value.iteration;
        this.directionSelect.value = this.value.direction;
        this.triggerSelect.value = this.value.trigger;

        this.updateHiddenInput();
    }

    destroy() {
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 TYPOGRAPHY FIELD - Typography Control Panel
   ========================================================================== */

class TB4TypographyField {
    /**
     * Create a typography field control
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.name = options.name || 'typography';
        this.onChange = options.onChange || (() => {});
        this.activeDevice = 'desktop';
        this.hoverState = false;

        // Typography properties with responsive support
        this.value = {
            fontFamily: options.value?.fontFamily || 'inherit',
            fontSize: {
                desktop: options.value?.fontSize?.desktop || '16px',
                tablet: options.value?.fontSize?.tablet || '',
                mobile: options.value?.fontSize?.mobile || ''
            },
            fontWeight: options.value?.fontWeight || '400',
            fontStyle: options.value?.fontStyle || 'normal',
            lineHeight: {
                desktop: options.value?.lineHeight?.desktop || '1.5',
                tablet: options.value?.lineHeight?.tablet || '',
                mobile: options.value?.lineHeight?.mobile || ''
            },
            letterSpacing: {
                desktop: options.value?.letterSpacing?.desktop || '0px',
                tablet: options.value?.letterSpacing?.tablet || '',
                mobile: options.value?.letterSpacing?.mobile || ''
            },
            textAlign: {
                desktop: options.value?.textAlign?.desktop || 'left',
                tablet: options.value?.textAlign?.tablet || '',
                mobile: options.value?.textAlign?.mobile || ''
            },
            textTransform: options.value?.textTransform || 'none',
            textDecoration: options.value?.textDecoration || 'none',
            color: {
                normal: options.value?.color?.normal || '',
                hover: options.value?.color?.hover || ''
            }
        };

        // Font options
        this.fontFamilies = [
            { value: 'inherit', label: 'Inherit' },
            { value: 'system-ui, -apple-system, sans-serif', label: 'System UI' },
            { value: 'Arial, Helvetica, sans-serif', label: 'Arial' },
            { value: 'Georgia, serif', label: 'Georgia' },
            { value: 'Times New Roman, serif', label: 'Times New Roman' },
            { value: 'Verdana, sans-serif', label: 'Verdana' },
            { value: 'Trebuchet MS, sans-serif', label: 'Trebuchet MS' },
            { value: 'Courier New, monospace', label: 'Courier New' },
            { value: 'Roboto, sans-serif', label: 'Roboto' },
            { value: 'Open Sans, sans-serif', label: 'Open Sans' },
            { value: 'Lato, sans-serif', label: 'Lato' },
            { value: 'Montserrat, sans-serif', label: 'Montserrat' },
            { value: 'Poppins, sans-serif', label: 'Poppins' },
            { value: 'Inter, sans-serif', label: 'Inter' },
            { value: 'Playfair Display, serif', label: 'Playfair Display' },
            { value: 'Merriweather, serif', label: 'Merriweather' }
        ];

        this.fontWeights = [
            { value: '100', label: 'Thin (100)' },
            { value: '200', label: 'Extra Light (200)' },
            { value: '300', label: 'Light (300)' },
            { value: '400', label: 'Regular (400)' },
            { value: '500', label: 'Medium (500)' },
            { value: '600', label: 'Semi Bold (600)' },
            { value: '700', label: 'Bold (700)' },
            { value: '800', label: 'Extra Bold (800)' },
            { value: '900', label: 'Black (900)' }
        ];

        this.textTransforms = [
            { value: 'none', label: 'None' },
            { value: 'uppercase', label: 'UPPERCASE' },
            { value: 'lowercase', label: 'lowercase' },
            { value: 'capitalize', label: 'Capitalize' }
        ];

        this.textDecorations = [
            { value: 'none', label: 'None' },
            { value: 'underline', label: 'Underline' },
            { value: 'line-through', label: 'Line Through' }
        ];

        this.render();
        this.bindEvents();
    }

    render() {
        const fontFamilyOpts = this.fontFamilies.map(opt =>
            `<option value="${this.escapeHtml(opt.value)}" ${opt.value === this.value.fontFamily ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const fontWeightOpts = this.fontWeights.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.fontWeight ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const textTransformOpts = this.textTransforms.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.textTransform ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const textDecorationOpts = this.textDecorations.map(opt =>
            `<option value="${opt.value}" ${opt.value === this.value.textDecoration ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        this.container.innerHTML = `
            <div class="tb4-typography-field" data-field="${this.name}">
                <!-- Font Family -->
                <div class="tb4-typo-row">
                    <label class="tb4-field-label">Font Family</label>
                    <select class="tb4-typo-font-family tb4-select-native">
                        ${fontFamilyOpts}
                    </select>
                </div>

                <!-- Font Size with Responsive -->
                <div class="tb4-typo-row">
                    <div class="tb4-typo-row-header">
                        <label class="tb4-field-label">Font Size</label>
                        <div class="tb4-typo-responsive-tabs"></div>
                    </div>
                    ${this.renderFontSizeControl()}
                </div>

                <!-- Font Weight & Style -->
                <div class="tb4-typo-row tb4-typo-row-half">
                    <div class="tb4-typo-half">
                        <label class="tb4-field-label">Font Weight</label>
                        <select class="tb4-typo-font-weight tb4-select-native">
                            ${fontWeightOpts}
                        </select>
                    </div>
                    <div class="tb4-typo-half">
                        <label class="tb4-field-label">Font Style</label>
                        <div class="tb4-typo-font-style-btns">
                            <button type="button" class="tb4-typo-style-btn ${this.value.fontStyle === 'normal' ? 'active' : ''}" data-style="normal" title="Normal">
                                ${TB4Icons.get('type', 14)}
                            </button>
                            <button type="button" class="tb4-typo-style-btn ${this.value.fontStyle === 'italic' ? 'active' : ''}" data-style="italic" title="Italic">
                                ${TB4Icons.get('italic', 14)}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Line Height with Responsive -->
                <div class="tb4-typo-row">
                    <div class="tb4-typo-row-header">
                        <label class="tb4-field-label">Line Height</label>
                        <div class="tb4-typo-responsive-tabs-lh"></div>
                    </div>
                    <div class="tb4-typo-input-unit">
                        <input type="text" class="tb4-typo-line-height" value="${this.value.lineHeight[this.activeDevice] || this.value.lineHeight.desktop}" placeholder="1.5">
                    </div>
                </div>

                <!-- Letter Spacing with Responsive -->
                <div class="tb4-typo-row">
                    <div class="tb4-typo-row-header">
                        <label class="tb4-field-label">Letter Spacing</label>
                        <div class="tb4-typo-responsive-tabs-ls"></div>
                    </div>
                    <div class="tb4-typo-input-unit">
                        <input type="text" class="tb4-typo-letter-spacing" value="${this.value.letterSpacing[this.activeDevice] || this.value.letterSpacing.desktop}" placeholder="0px">
                    </div>
                </div>

                <!-- Text Align with Responsive -->
                <div class="tb4-typo-row">
                    <div class="tb4-typo-row-header">
                        <label class="tb4-field-label">Text Align</label>
                        <div class="tb4-typo-responsive-tabs-ta"></div>
                    </div>
                    <div class="tb4-typo-text-align-btns">
                        <button type="button" class="tb4-typo-align-btn ${this.getCurrentTextAlign() === 'left' ? 'active' : ''}" data-align="left" title="Left">
                            ${TB4Icons.alignLeft}
                        </button>
                        <button type="button" class="tb4-typo-align-btn ${this.getCurrentTextAlign() === 'center' ? 'active' : ''}" data-align="center" title="Center">
                            ${TB4Icons.alignCenter}
                        </button>
                        <button type="button" class="tb4-typo-align-btn ${this.getCurrentTextAlign() === 'right' ? 'active' : ''}" data-align="right" title="Right">
                            ${TB4Icons.alignRight}
                        </button>
                        <button type="button" class="tb4-typo-align-btn ${this.getCurrentTextAlign() === 'justify' ? 'active' : ''}" data-align="justify" title="Justify">
                            ${TB4Icons.alignJustify}
                        </button>
                    </div>
                </div>

                <!-- Text Transform & Decoration -->
                <div class="tb4-typo-row tb4-typo-row-half">
                    <div class="tb4-typo-half">
                        <label class="tb4-field-label">Transform</label>
                        <select class="tb4-typo-text-transform tb4-select-native">
                            ${textTransformOpts}
                        </select>
                    </div>
                    <div class="tb4-typo-half">
                        <label class="tb4-field-label">Decoration</label>
                        <select class="tb4-typo-text-decoration tb4-select-native">
                            ${textDecorationOpts}
                        </select>
                    </div>
                </div>

                <!-- Color with Hover Toggle -->
                <div class="tb4-typo-row">
                    <div class="tb4-typo-row-header">
                        <label class="tb4-field-label">Color</label>
                        <button type="button" class="tb4-typo-hover-toggle ${this.hoverState ? 'active' : ''}" title="Toggle hover state">
                            Hover
                        </button>
                    </div>
                    <div class="tb4-typo-color-picker-container"></div>
                </div>

                <input type="hidden" name="${this.name}" value='${JSON.stringify(this.value)}'>
            </div>
        `;

        // Cache DOM elements
        this.fieldEl = this.container.querySelector('.tb4-typography-field');
        this.fontFamilySelect = this.container.querySelector('.tb4-typo-font-family');
        this.fontWeightSelect = this.container.querySelector('.tb4-typo-font-weight');
        this.fontStyleBtns = this.container.querySelectorAll('.tb4-typo-style-btn');
        this.lineHeightInput = this.container.querySelector('.tb4-typo-line-height');
        this.letterSpacingInput = this.container.querySelector('.tb4-typo-letter-spacing');
        this.textAlignBtns = this.container.querySelectorAll('.tb4-typo-align-btn');
        this.textTransformSelect = this.container.querySelector('.tb4-typo-text-transform');
        this.textDecorationSelect = this.container.querySelector('.tb4-typo-text-decoration');
        this.hoverToggle = this.container.querySelector('.tb4-typo-hover-toggle');
        this.colorPickerContainer = this.container.querySelector('.tb4-typo-color-picker-container');
        this.hiddenInput = this.container.querySelector(`input[name="${this.name}"]`);

        // Initialize responsive tabs for font size
        const fontSizeTabsContainer = this.container.querySelector('.tb4-typo-responsive-tabs');
        this.fontSizeTabs = new TB4ResponsiveTabs(fontSizeTabsContainer, {
            activeDevice: this.activeDevice,
            onChange: (device) => this.switchDevice(device)
        });

        // Initialize color picker
        this.colorPicker = new TB4ColorPicker(this.colorPickerContainer, {
            name: `${this.name}_color`,
            value: this.hoverState ? this.value.color.hover : this.value.color.normal,
            showAlpha: true,
            onChange: (color) => {
                if (this.hoverState) {
                    this.value.color.hover = color;
                } else {
                    this.value.color.normal = color;
                }
                this.updateHiddenInput();
                this.onChange(this.value);
            }
        });
    }

    renderFontSizeControl() {
        const currentValue = this.value.fontSize[this.activeDevice] || this.value.fontSize.desktop;
        return `
            <div class="tb4-typo-font-size-control">
                <input type="text" class="tb4-typo-font-size" value="${currentValue}" placeholder="16px">
            </div>
        `;
    }

    getCurrentTextAlign() {
        return this.value.textAlign[this.activeDevice] || this.value.textAlign.desktop || 'left';
    }

    bindEvents() {
        // Font Family
        this.fontFamilySelect.addEventListener('change', (e) => {
            this.value.fontFamily = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Font Size
        const fontSizeInput = this.container.querySelector('.tb4-typo-font-size');
        if (fontSizeInput) {
            fontSizeInput.addEventListener('input', (e) => {
                this.value.fontSize[this.activeDevice] = e.target.value;
                this.updateHiddenInput();
                this.onChange(this.value);
            });
        }

        // Font Weight
        this.fontWeightSelect.addEventListener('change', (e) => {
            this.value.fontWeight = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Font Style buttons
        this.fontStyleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.fontStyleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.value.fontStyle = btn.dataset.style;
                this.updateHiddenInput();
                this.onChange(this.value);
            });
        });

        // Line Height
        this.lineHeightInput.addEventListener('input', (e) => {
            this.value.lineHeight[this.activeDevice] = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Letter Spacing
        this.letterSpacingInput.addEventListener('input', (e) => {
            this.value.letterSpacing[this.activeDevice] = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Text Align buttons
        this.textAlignBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.textAlignBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.value.textAlign[this.activeDevice] = btn.dataset.align;
                this.updateHiddenInput();
                this.onChange(this.value);
            });
        });

        // Text Transform
        this.textTransformSelect.addEventListener('change', (e) => {
            this.value.textTransform = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Text Decoration
        this.textDecorationSelect.addEventListener('change', (e) => {
            this.value.textDecoration = e.target.value;
            this.updateHiddenInput();
            this.onChange(this.value);
        });

        // Hover Toggle
        this.hoverToggle.addEventListener('click', () => this.toggleHover());
    }

    switchDevice(device) {
        this.activeDevice = device;

        // Update font size input
        const fontSizeInput = this.container.querySelector('.tb4-typo-font-size');
        if (fontSizeInput) {
            fontSizeInput.value = this.value.fontSize[device] || this.value.fontSize.desktop || '';
        }

        // Update line height input
        this.lineHeightInput.value = this.value.lineHeight[device] || this.value.lineHeight.desktop || '';

        // Update letter spacing input
        this.letterSpacingInput.value = this.value.letterSpacing[device] || this.value.letterSpacing.desktop || '';

        // Update text align buttons
        const currentAlign = this.value.textAlign[device] || this.value.textAlign.desktop || 'left';
        this.textAlignBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.align === currentAlign);
        });
    }

    toggleHover() {
        this.hoverState = !this.hoverState;
        this.hoverToggle.classList.toggle('active', this.hoverState);

        // Update color picker value
        const colorValue = this.hoverState ? this.value.color.hover : this.value.color.normal;
        this.colorPicker.setValue(colorValue);
    }

    updateHiddenInput() {
        this.hiddenInput.value = JSON.stringify(this.value);
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.value = { ...this.value, ...value };

        // Update UI elements
        this.fontFamilySelect.value = this.value.fontFamily;
        this.fontWeightSelect.value = this.value.fontWeight;

        this.fontStyleBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.style === this.value.fontStyle);
        });

        const fontSizeInput = this.container.querySelector('.tb4-typo-font-size');
        if (fontSizeInput) {
            fontSizeInput.value = this.value.fontSize[this.activeDevice] || this.value.fontSize.desktop;
        }

        this.lineHeightInput.value = this.value.lineHeight[this.activeDevice] || this.value.lineHeight.desktop;
        this.letterSpacingInput.value = this.value.letterSpacing[this.activeDevice] || this.value.letterSpacing.desktop;

        const currentAlign = this.value.textAlign[this.activeDevice] || this.value.textAlign.desktop;
        this.textAlignBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.align === currentAlign);
        });

        this.textTransformSelect.value = this.value.textTransform;
        this.textDecorationSelect.value = this.value.textDecoration;

        const colorValue = this.hoverState ? this.value.color.hover : this.value.color.normal;
        this.colorPicker.setValue(colorValue);

        this.updateHiddenInput();
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        if (this.colorPicker && this.colorPicker.destroy) {
            this.colorPicker.destroy();
        }
        if (this.fontSizeTabs && this.fontSizeTabs.destroy) {
            this.fontSizeTabs.destroy();
        }
        this.container.innerHTML = '';
    }
}

/* ==========================================================================
   TB4 CUSTOM CSS FIELD - Custom CSS Control Panel
   ========================================================================== */

class TB4CustomCSSField {
    /**
     * Create a custom CSS field control
     * @param {HTMLElement} container - Container element
     * @param {Object} options - Configuration options
     */
    constructor(container, options = {}) {
        this.container = container;
        this.name = options.name || 'custom-css';
        this.onChange = options.onChange || (() => {});

        // CSS targets from module definition
        this.targets = options.targets || [
            { key: 'wrapper', label: 'Wrapper', selector: '.module-wrapper' },
            { key: 'content', label: 'Content', selector: '.module-content' }
        ];

        // Initialize value structure
        this.value = options.value || {};
        this.targets.forEach(target => {
            if (!this.value[target.key]) {
                this.value[target.key] = {
                    normal: { desktop: '', tablet: '', mobile: '' },
                    hover: { desktop: '', tablet: '', mobile: '' }
                };
            }
        });

        this.activeDevice = 'desktop';
        this.activeState = 'normal'; // normal or hover

        this.render();
        this.bindEvents();
    }

    render() {
        const targetsHtml = this.targets.map(target => this.renderTarget(target.key, target)).join('');

        this.container.innerHTML = `
            <div class="tb4-custom-css-field" data-field="${this.name}">
                <div class="tb4-custom-css-header">
                    <div class="tb4-custom-css-controls">
                        <div class="tb4-custom-css-responsive-tabs"></div>
                        <div class="tb4-custom-css-state-toggle">
                            <button type="button" class="tb4-css-state-btn active" data-state="normal">Normal</button>
                            <button type="button" class="tb4-css-state-btn" data-state="hover">Hover</button>
                        </div>
                    </div>
                </div>
                <div class="tb4-custom-css-targets">
                    ${targetsHtml}
                </div>
                <input type="hidden" name="${this.name}" value='${JSON.stringify(this.value)}'>
            </div>
        `;

        // Cache DOM elements
        this.fieldEl = this.container.querySelector('.tb4-custom-css-field');
        this.targetsContainer = this.container.querySelector('.tb4-custom-css-targets');
        this.stateBtns = this.container.querySelectorAll('.tb4-css-state-btn');
        this.hiddenInput = this.container.querySelector(`input[name="${this.name}"]`);

        // Initialize responsive tabs
        const tabsContainer = this.container.querySelector('.tb4-custom-css-responsive-tabs');
        this.responsiveTabs = new TB4ResponsiveTabs(tabsContainer, {
            activeDevice: this.activeDevice,
            onChange: (device) => {
                this.activeDevice = device;
                this.updateTextareas();
            }
        });
    }

    renderTarget(key, config) {
        const currentValue = this.value[key]?.[this.activeState]?.[this.activeDevice] || '';

        return `
            <div class="tb4-css-target" data-target="${key}">
                <div class="tb4-css-target-header">
                    <span class="tb4-css-target-label">${this.escapeHtml(config.label)}</span>
                    <code class="tb4-css-target-selector">${this.escapeHtml(config.selector)}</code>
                </div>
                <div class="tb4-css-target-body">
                    <textarea class="tb4-css-textarea"
                              data-target="${key}"
                              placeholder="/* Add your CSS here */
color: #333;
background: #fff;"
                              spellcheck="false">${this.escapeHtml(currentValue)}</textarea>
                </div>
            </div>
        `;
    }

    bindEvents() {
        // State toggle buttons
        this.stateBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.stateBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.activeState = btn.dataset.state;
                this.updateTextareas();
            });
        });

        // Textarea changes
        this.container.querySelectorAll('.tb4-css-textarea').forEach(textarea => {
            textarea.addEventListener('input', (e) => {
                const targetKey = e.target.dataset.target;
                if (!this.value[targetKey]) {
                    this.value[targetKey] = {
                        normal: { desktop: '', tablet: '', mobile: '' },
                        hover: { desktop: '', tablet: '', mobile: '' }
                    };
                }
                this.value[targetKey][this.activeState][this.activeDevice] = e.target.value;
                this.updateHiddenInput();
                this.onChange(this.value);
            });

            // Basic syntax highlighting effect via CSS class
            textarea.addEventListener('focus', () => {
                textarea.classList.add('tb4-css-editing');
            });
            textarea.addEventListener('blur', () => {
                textarea.classList.remove('tb4-css-editing');
            });

            // Tab key support for indentation
            textarea.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    textarea.value = textarea.value.substring(0, start) + '    ' + textarea.value.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + 4;

                    // Trigger input event
                    textarea.dispatchEvent(new Event('input'));
                }
            });
        });
    }

    updateTextareas() {
        this.container.querySelectorAll('.tb4-css-textarea').forEach(textarea => {
            const targetKey = textarea.dataset.target;
            const value = this.value[targetKey]?.[this.activeState]?.[this.activeDevice] || '';
            textarea.value = value;
        });
    }

    updateHiddenInput() {
        this.hiddenInput.value = JSON.stringify(this.value);
    }

    getValue() {
        return this.value;
    }

    setValue(value) {
        this.value = value;
        this.updateTextareas();
        this.updateHiddenInput();
    }

    escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    destroy() {
        if (this.responsiveTabs && this.responsiveTabs.destroy) {
            this.responsiveTabs.destroy();
        }
        this.container.innerHTML = '';
    }
}

// Make available globally
window.TB4Icons = TB4Icons;
window.TB4Toggle = TB4Toggle;
window.TB4Select = TB4Select;
window.TB4RangeSlider = TB4RangeSlider;
window.TB4ColorPicker = TB4ColorPicker;
window.TB4ButtonGroup = TB4ButtonGroup;
window.TB4ResponsiveTabs = TB4ResponsiveTabs;
window.TB4CollapsibleSection = TB4CollapsibleSection;
window.TB4SpacingBox = TB4SpacingBox;
window.TB4Tooltip = TB4Tooltip;
window.TB4ResetButton = TB4ResetButton;
window.TB4AnimationField = TB4AnimationField;
window.TB4TypographyField = TB4TypographyField;
window.TB4CustomCSSField = TB4CustomCSSField;
window.TB4Fields = TB4Fields;
