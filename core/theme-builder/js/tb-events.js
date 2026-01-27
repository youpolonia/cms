/**
 * Theme Builder 3.0 - Events & Drag/Drop Module
 * Contains all event bindings and drag & drop functionality
 * Part of TB 3.0 modularization - ETAP 8
 */

// ═══════════════════════════════════════════════════════════
// INITIALIZATION
// ═══════════════════════════════════════════════════════════
TB.init = function() {
    // Load Font Awesome icons
    fetch('/assets/fonts/fontawesome/fa-icons.json')
        .then(r => r.json())
        .then(data => { this.fontawesomeIcons = data; })
        .catch(e => console.warn('FA icons not loaded:', e));
    
    this.migrateContent();
    this.renderModulesPanel();
    this.renderCanvas();
    this.bindEvents();
    this.bindDragDropEvents();
    this.saveToHistory();
    this.updateHoverStylesheet();
    
    // Sync status dropdown with hidden input
    const statusSelect = document.getElementById("page-status-select");
    if (statusSelect) {
        statusSelect.addEventListener("change", (e) => {
            document.getElementById("page-status").value = e.target.value;
        });
    }
    
    console.log('TB 3.0: Theme Builder initialized');
};

// ═══════════════════════════════════════════════════════════
// EVENT BINDING
// ═══════════════════════════════════════════════════════════
TB.bindEvents = function() {
    // Viewport buttons - sync with responsive device toggle
    document.querySelectorAll('.tb-viewport-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const viewport = btn.dataset.viewport;
            this.setDevice(viewport);
        });
    });

    // Tab switching
    document.querySelectorAll('.tb-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tb-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            this.currentTab = tab.dataset.tab;
            if (this.selectedElement) this.renderSettings(this.currentTab);
        });
    });

    // Module search
    const moduleSearch = document.getElementById('module-search');
    if (moduleSearch) {
        moduleSearch.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.tb-module-item').forEach(item => {
                const name = item.querySelector('.tb-module-name').textContent.toLowerCase();
                item.style.display = name.includes(query) || item.dataset.module.toLowerCase().includes(query) ? '' : 'none';
            });
            document.querySelectorAll('.tb-module-category').forEach(cat => {
                cat.style.display = cat.querySelectorAll('.tb-module-item[style=""],.tb-module-item:not([style])').length > 0 ? '' : 'none';
            });
        });
    }

    // Undo/Redo buttons
    const undoBtn = document.getElementById('btn-undo');
    const redoBtn = document.getElementById('btn-redo');
    if (undoBtn) undoBtn.addEventListener('click', () => this.undo());
    if (redoBtn) redoBtn.addEventListener('click', () => this.redo());
    // Toolbar buttons
    const previewBtn = document.getElementById('btn-preview');
    const saveBtn = document.getElementById('btn-save');
    const libraryBtn = document.getElementById('btn-load-library');
    const addSectionBtn = document.getElementById('btn-add-section');
    
    if (previewBtn) previewBtn.addEventListener('click', () => this.preview());
    if (saveBtn) saveBtn.addEventListener('click', () => this.save());
    if (libraryBtn) libraryBtn.addEventListener('click', () => this.openLibrary());
    if (addSectionBtn) addSectionBtn.addEventListener('click', () => this.addSection());

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Save: Ctrl/Cmd + S
        if ((e.metaKey || e.ctrlKey) && e.key === 's') {
            e.preventDefault();
            this.save();
        }
        // Undo/Redo: Ctrl/Cmd + Z / Shift + Z
        if ((e.metaKey || e.ctrlKey) && e.key === 'z') {
            e.preventDefault();
            e.shiftKey ? this.redo() : this.undo();
        }
        // Delete selected module
        if ((e.key === 'Delete' || e.key === 'Backspace') && this.selectedElement && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            const { sIdx, rIdx, cIdx, mIdx } = this.selectedElement;
            this.removeModule(sIdx, rIdx, cIdx, mIdx);
        }
        // Escape - deselect
        if (e.key === 'Escape') {
            this.selectedElement = null;
            document.querySelectorAll('.tb-module').forEach(m => m.classList.remove('selected'));
            this.renderSettings();
        }
    });

    // Canvas click - deselect
    const canvasInner = document.getElementById('canvas-inner');
    if (canvasInner) {
        canvasInner.addEventListener('click', (e) => {
            if (e.target.id === 'canvas-inner' || e.target.classList.contains('tb-section') || e.target.classList.contains('tb-row')) {
                this.selectedElement = null;
                document.querySelectorAll('.tb-module').forEach(m => m.classList.remove('selected'));
                this.renderSettings();
            }
        });
    }
};

TB.bindDragDropEvents = function() {
    this.bindModulePanelDrag();
};

// ═══════════════════════════════════════════════════════════
// MODULE PANEL DRAG
// ═══════════════════════════════════════════════════════════
TB.bindModulePanelDrag = function() {
    const panel = document.getElementById('modules-panel');
    if (!panel || panel.dataset.dragBound) return;
    
    panel.dataset.dragBound = 'true';

    panel.addEventListener('dragstart', (e) => {
        const item = e.target.closest('.tb-module-item');
        if (!item) return;
        this.draggedModule = item.dataset.module;
        this.draggedElement = null;
        e.dataTransfer.setData('text/plain', item.dataset.module);
        e.dataTransfer.setData('module-type', item.dataset.module);
        e.dataTransfer.effectAllowed = 'copy';
        item.classList.add('dragging');
        document.querySelectorAll('.tb-column').forEach(col => col.classList.add('tb-drop-target'));
        document.querySelectorAll('.tb-drop-zone').forEach(zone => zone.classList.add('tb-drop-target'));
    });

    panel.addEventListener('dragend', (e) => {
        const item = e.target.closest('.tb-module-item');
        if (item) item.classList.remove('dragging');
        setTimeout(() => {
            this.draggedModule = null;
            this.clearDropHighlights();
        }, 100);
    });
};

TB.bindCanvasDragEvents = function() {
    // Canvas drag events are bound inline in renderColumn
    // This function exists for potential future expansion
};

// ═══════════════════════════════════════════════════════════
// MODULE DRAG HANDLERS
// ═══════════════════════════════════════════════════════════
TB.handleModuleDragStart = function(e, sIdx, rIdx, cIdx, mIdx) {
    this.draggedElement = { sIdx, rIdx, cIdx, mIdx };
    this.draggedModule = null;
    e.dataTransfer.setData('application/json', JSON.stringify({ sIdx, rIdx, cIdx, mIdx }));
    e.dataTransfer.setData('module-move', JSON.stringify({ sIdx, rIdx, cIdx, mIdx }));
    e.dataTransfer.effectAllowed = 'move';
    e.target.classList.add('tb-dragging');
    setTimeout(() => {
        document.querySelectorAll('.tb-column').forEach(col => col.classList.add('tb-drop-target'));
    }, 0);
};

TB.handleModuleDragEnd = function(e) {
    e.target.classList.remove('tb-dragging');
    this.draggedElement = null;
    this.clearDropHighlights();
};

// ═══════════════════════════════════════════════════════════
// COLUMN DRAG HANDLERS
// ═══════════════════════════════════════════════════════════
TB.handleColumnDragOver = function(e, sIdx, rIdx, cIdx) {
    e.preventDefault();
    e.dataTransfer.dropEffect = this.draggedModule ? 'copy' : 'move';
    
    const column = e.currentTarget;
    column.classList.add('tb-drag-over');
    
    const modules = column.querySelectorAll('.tb-module');
    const insertionLines = column.querySelectorAll('.tb-insertion-line');
    insertionLines.forEach(line => line.style.display = 'none');
    
    if (modules.length === 0) {
        const topLine = column.querySelector('.tb-insertion-top');
        if (topLine) topLine.style.display = 'block';
        this.insertionIndex = 0;
        return;
    }
    
    const mouseY = e.clientY;
    let insertIdx = modules.length;
    
    for (let i = 0; i < modules.length; i++) {
        const modRect = modules[i].getBoundingClientRect();
        if (mouseY < modRect.top + modRect.height / 2) {
            insertIdx = i;
            break;
        }
    }
    
    const lineToShow = column.querySelector('.tb-insertion-line[data-insert-idx="' + insertIdx + '"]');
    if (lineToShow) lineToShow.style.display = 'block';
    
    this.insertionIndex = insertIdx;
    this.dragOverColumn = { sIdx, rIdx, cIdx };
};

TB.handleColumnDragLeave = function(e) {
    const column = e.currentTarget;
    if (!column.contains(e.relatedTarget)) {
        column.classList.remove('tb-drag-over');
        column.querySelectorAll('.tb-insertion-line').forEach(line => line.style.display = 'none');
    }
};

// ═══════════════════════════════════════════════════════════
// DROP HANDLERS
// ═══════════════════════════════════════════════════════════
TB.handleColumnDrop = function(e, sIdx, rIdx, cIdx) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('tb-drag-over');
    
    const insertIdx = this.insertionIndex >= 0 ? this.insertionIndex : (this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules?.length || 0);
    
    if (this.draggedModule) {
        this.addModuleToColumn(sIdx, rIdx, cIdx, this.draggedModule, insertIdx);
    } else if (this.draggedElement) {
        const from = this.draggedElement;
        if (from.sIdx === sIdx && from.rIdx === rIdx && from.cIdx === cIdx) {
            // Same column - reorder
            let targetIdx = insertIdx;
            if (from.mIdx < insertIdx) targetIdx--;
            if (from.mIdx !== targetIdx) {
                this.reorderModuleInColumn(sIdx, rIdx, cIdx, from.mIdx, targetIdx);
            }
        } else {
            // Different column - move
            this.moveModuleToColumn(from, { sIdx, rIdx, cIdx, insertIdx });
        }
    }
    
    this.clearDropHighlights();
    this.draggedModule = null;
    this.draggedElement = null;
    this.insertionIndex = -1;
};

TB.handleMainDropZoneDragOver = function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = this.draggedModule ? 'copy' : 'move';
    e.currentTarget.classList.add('drag-over');
};

TB.handleMainDropZoneDragLeave = function(e) {
    e.currentTarget.classList.remove('drag-over');
};

TB.handleMainDropZoneDrop = function(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    if (this.draggedModule) {
        this.addSectionWithModule(this.draggedModule);
    } else if (this.draggedElement) {
        this.addSectionWithExistingModule(this.draggedElement);
    }
    
    this.clearDropHighlights();
    this.draggedModule = null;
    this.draggedElement = null;
};

// ═══════════════════════════════════════════════════════════
// DROP HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════
TB.clearDropHighlights = function() {
    document.querySelectorAll('.tb-column').forEach(col => col.classList.remove('tb-drop-target', 'tb-drag-over'));
    document.querySelectorAll('.tb-drop-zone').forEach(zone => zone.classList.remove('tb-drop-target', 'drag-over'));
    document.querySelectorAll('.tb-insertion-line').forEach(line => line.style.display = 'none');
    document.querySelectorAll('.tb-module').forEach(mod => mod.classList.remove('tb-dragging'));
};

TB.addModuleToColumn = function(sectionIdx, rowIdx, colIdx, moduleType, insertIdx = null) {
    const column = this.content.sections[sectionIdx]?.rows[rowIdx]?.columns[colIdx];
    if (!column) return;
    if (!column.modules) column.modules = [];
    
    const newModule = {
        id: 'mod_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        type: moduleType,
        content: this.getDefaultContent(moduleType),
        settings: {},
        design: {}
    };
    
    if (insertIdx !== null && insertIdx >= 0 && insertIdx <= column.modules.length) {
        column.modules.splice(insertIdx, 0, newModule);
    } else {
        column.modules.push(newModule);
    }
    
    this.saveToHistory();
    this.renderCanvas();
    const newMIdx = insertIdx !== null ? insertIdx : column.modules.length - 1;
    this.selectModule(sectionIdx, rowIdx, colIdx, newMIdx);
    this.showToast('Added ' + moduleType + ' module', 'success');
};

TB.reorderModuleInColumn = function(sIdx, rIdx, cIdx, fromIdx, toIdx) {
    const modules = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules;
    if (!modules) return;
    if (toIdx < 0 || toIdx >= modules.length) return;
    
    const [module] = modules.splice(fromIdx, 1);
    modules.splice(toIdx, 0, module);
    
    this.saveToHistory();
    this.renderCanvas();
};

TB.addSectionWithModule = function(moduleType) {
    const newModule = {
        id: 'mod_' + Date.now(),
        type: moduleType,
        content: this.getDefaultContent(moduleType),
        settings: {},
        design: {}
    };
    
    if (!this.content.sections) this.content.sections = [];
    
    this.content.sections.push({
        id: 'sec_' + Date.now(),
        settings: {},
        rows: [{
            id: 'row_' + Date.now(),
            columns: [{
                id: 'col_' + Date.now(),
                width: '100%',
                modules: [newModule]
            }]
        }]
    });
    
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(this.content.sections.length - 1, 0, 0, 0);
    this.showToast('Module added to new section', 'success');
};

TB.addSectionWithExistingModule = function(fromPath) {
    const fromColumn = this.content.sections[fromPath.sIdx]?.rows[fromPath.rIdx]?.columns[fromPath.cIdx];
    if (!fromColumn || !fromColumn.modules || !fromColumn.modules[fromPath.mIdx]) return;
    
    const [module] = fromColumn.modules.splice(fromPath.mIdx, 1);
    
    if (!this.content.sections) this.content.sections = [];
    
    this.content.sections.push({
        id: 'sec_' + Date.now(),
        settings: {},
        rows: [{
            id: 'row_' + Date.now(),
            columns: [{
                id: 'col_' + Date.now(),
                width: '100%',
                modules: [module]
            }]
        }]
    });
    
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Module moved to new section', 'success');
};

// ═══════════════════════════════════════════════════════════
// HOVER STATE SYSTEM
// ═══════════════════════════════════════════════════════════
TB.updateHoverStylesheet = function() {
    let styleEl = document.getElementById('tb-hover-styles');
    if (!styleEl) {
        styleEl = document.createElement('style');
        styleEl.id = 'tb-hover-styles';
        document.head.appendChild(styleEl);
    }
    
    let css = '';
    
    // Generate hover styles for all modules
    if (this.content.sections) {
        this.content.sections.forEach((section, sIdx) => {
            if (section.rows) {
                section.rows.forEach((row, rIdx) => {
                    if (row.columns) {
                        row.columns.forEach((col, cIdx) => {
                            if (col.modules) {
                                col.modules.forEach((mod, mIdx) => {
                                    const hoverStyles = this.generateModuleHoverCSS(mod, sIdx, rIdx, cIdx, mIdx);
                                    if (hoverStyles) css += hoverStyles;
                                });
                            }
                        });
                    }
                });
            }
        });
    }
    
    console.log('🎨 Hover CSS generated:', css || '(empty)');
    styleEl.textContent = css;
};

TB.generateModuleHoverCSS = function(mod, sIdx, rIdx, cIdx, mIdx) {
    const settings = mod.settings || {};
    
    // Check if hover is enabled
    if (!settings.hover_enabled) return '';
    
    const selector = '.tb-module[data-module-path="' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx + '"]';
    const duration = settings.hover_transition_duration || '0.3';
    const easing = settings.hover_transition_easing || 'ease';
    
    let css = '';
    
    // Base transition on module
    css += selector + ' { transition: all ' + duration + 's ' + easing + '; }\n';
    
    // Hover state
    css += selector + ':hover {';
    
    if (settings.background_color_hover) css += 'background-color:' + settings.background_color_hover + '!important;';
    if (settings.text_color_hover) css += 'color:' + settings.text_color_hover + '!important;';
    if (settings.border_color_hover) css += 'border-color:' + settings.border_color_hover + '!important;';
    if (settings.opacity_hover && settings.opacity_hover !== '1') css += 'opacity:' + settings.opacity_hover + '!important;';
    
    // Transform
    let transform = [];
    if (settings.transform_scale_x_hover && settings.transform_scale_x_hover !== '100') {
        transform.push('scaleX(' + (parseFloat(settings.transform_scale_x_hover) / 100) + ')');
    }
    if (settings.transform_scale_y_hover && settings.transform_scale_y_hover !== '100') {
        transform.push('scaleY(' + (parseFloat(settings.transform_scale_y_hover) / 100) + ')');
    }
    if (settings.transform_translate_y_hover && settings.transform_translate_y_hover !== '0') {
        transform.push('translateY(' + settings.transform_translate_y_hover + 'px)');
    }
    if (transform.length > 0) {
        css += 'transform:' + transform.join(' ') + '!important;';
    }
    
    // Box shadow
    if (settings.box_shadow_hover_enabled) {
        const shV = settings.box_shadow_hover_vertical || '8';
        const shBlur = settings.box_shadow_hover_blur || '20';
        const shColor = settings.box_shadow_hover_color || 'rgba(0,0,0,0.2)';
        css += 'box-shadow:0 ' + shV + 'px ' + shBlur + 'px ' + shColor + '!important;';
    }
    
    css += '}\n';
    
    return css;
};

// ═══════════════════════════════════════════════════════════
// STATE TOGGLE (Normal/Hover)
// ═══════════════════════════════════════════════════════════
TB.setState = function(state) {
    if (!['normal', 'hover'].includes(state)) return;
    this.currentState = state;
    
    // Update state toggle buttons
    document.querySelectorAll('.tb-state-btn').forEach(btn => {
        btn.classList.toggle('active', btn.textContent.toLowerCase().includes(state));
    });
    
    // Re-render settings to show correct state values
    if (this.selectedElement) {
        this.renderSettings();
    }
};

TB.toggleHoverPreview = function(active) {
    this.hoverPreviewActive = active;
    const canvas = document.getElementById('canvas-inner');
    if (canvas) {
        canvas.classList.toggle('tb-hover-preview', active);
    }
};

// ═══════════════════════════════════════════════════════════
// END OF TB-EVENTS.JS
// ═══════════════════════════════════════════════════════════
console.log('TB 3.0: tb-events.js loaded');
