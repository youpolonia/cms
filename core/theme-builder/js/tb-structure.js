/**
 * Theme Builder 3.0 - Structure Management Module
 * Contains Section/Row/Column management, History (undo/redo), Save/Load
 * Part of TB 3.0 modularization - ETAP 7
 */

// ═══════════════════════════════════════════════════════════
// HISTORY SYSTEM (UNDO/REDO)
// ═══════════════════════════════════════════════════════════
TB.saveToHistory = function() {
    const snapshot = JSON.stringify(this.content);
    // Remove future states if we're in the middle of history
    if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1);
    }
    this.history.push(snapshot);
    // Limit history size
    if (this.history.length > 50) {
        this.history.shift();
    } else {
        this.historyIndex++;
    }
    this.updateHistoryButtons();
};

TB.undo = function() {
    if (this.historyIndex > 0) {
        this.historyIndex--;
        this.content = JSON.parse(this.history[this.historyIndex]);
        this.renderCanvas();
        this.selectedElement = null;
        this.renderSettings();
        this.updateHistoryButtons();
        this.showToast('Undo', 'info');
    }
};

TB.redo = function() {
    if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.content = JSON.parse(this.history[this.historyIndex]);
        this.renderCanvas();
        this.selectedElement = null;
        this.renderSettings();
        this.updateHistoryButtons();
        this.showToast('Redo', 'info');
    }
};

TB.updateHistoryButtons = function() {
    const undoBtn = document.getElementById('btn-undo');
    const redoBtn = document.getElementById('btn-redo');
    if (undoBtn) undoBtn.disabled = this.historyIndex <= 0;
    if (redoBtn) redoBtn.disabled = this.historyIndex >= this.history.length - 1;
};

// ═══════════════════════════════════════════════════════════
// SECTION MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addSection = function() {
    if (!this.content.sections) this.content.sections = [];
    this.content.sections.push({
        id: 'sec_' + Date.now(),
        settings: {},
        rows: [{
            id: 'row_' + Date.now(),
            columns: [{
                id: 'col_' + Date.now(),
                width: '100%',
                modules: []
            }]
        }]
    });
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Section added', 'success');
};

TB.deleteSection = function(idx) {
    if (confirm('Delete this section and all its content?')) {
        this.content.sections.splice(idx, 1);
        this.selectedElement = null;
        this.saveToHistory();
        this.renderCanvas();
        this.renderSettings();
        this.showToast('Section deleted', 'success');
    }
};

TB.duplicateSection = function(idx) {
    const original = this.content.sections[idx];
    if (!original) return;
    const duplicate = JSON.parse(JSON.stringify(original));
    duplicate.id = 'sec_' + Date.now();
    duplicate.rows.forEach(row => {
        row.id = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        row.columns.forEach(col => {
            col.id = 'col_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            col.modules.forEach(mod => {
                mod.id = 'mod_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            });
        });
    });
    this.content.sections.splice(idx + 1, 0, duplicate);
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Section duplicated', 'success');
};

TB.moveSection = function(idx, dir) {
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= this.content.sections.length) return;
    [this.content.sections[idx], this.content.sections[newIdx]] = 
        [this.content.sections[newIdx], this.content.sections[idx]];
    this.saveToHistory();
    this.renderCanvas();
};

TB.editSection = function(idx) {
    this.selectedElement = { type: 'section', sIdx: idx };
    document.querySelectorAll('.tb-module').forEach(m => m.classList.remove('selected'));
    document.querySelectorAll('.tb-section').forEach(s => s.classList.remove('selected'));
    document.querySelectorAll('.tb-section')[idx]?.classList.add('selected');
    this.renderSectionSettings(idx);
};

TB.updateSectionDesign = function(idx, key, value) {
    const section = this.content.sections[idx];
    if (!section) return;
    if (!section.design) section.design = {};
    section.design[key] = value;
    this.saveToHistory();
    this.renderCanvas();
    this.renderSectionSettings(idx);
};

TB.updateOverlay = function(idx) {
    const colorInput = document.getElementById('overlay-color-' + idx);
    const opacityInput = document.getElementById('overlay-opacity-' + idx);
    if (!colorInput || !opacityInput) return;
    const hex = colorInput.value;
    const opacity = parseInt(opacityInput.value) / 100;
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    const rgba = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity.toFixed(2) + ')';
    this.updateSectionDesign(idx, 'background_overlay', rgba);
};

TB.setOverlayPreset = function(idx, preset) {
    const presets = {
        'light': 'rgba(255,255,255,0.3)',
        'medium': 'rgba(0,0,0,0.4)',
        'dark': 'rgba(0,0,0,0.7)',
        'none': ''
    };
    this.updateSectionDesign(idx, 'background_overlay', presets[preset] || '');
};

// ═══════════════════════════════════════════════════════════
// ROW MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addRow = function(sectionIdx) {
    const section = this.content.sections[sectionIdx];
    if (!section) return;
    if (!section.rows) section.rows = [];
    section.rows.push({
        id: 'row_' + Date.now(),
        columns: [{
            id: 'col_' + Date.now(),
            width: '100%',
            modules: []
        }]
    });
    this.saveToHistory();
    this.renderCanvas();
};

TB.deleteRow = function(sectionIdx, rowIdx) {
    const section = this.content.sections[sectionIdx];
    if (!section || !section.rows) return;
    if (section.rows.length <= 1) {
        if (!confirm('Delete the last row? This will leave the section empty.')) {
            return;
        }
    }
    section.rows.splice(rowIdx, 1);
    if (section.rows.length === 0) {
        section.rows.push({
            id: 'row_' + Date.now(),
            columns: [{
                id: 'col_' + Date.now(),
                width: '100%',
                modules: []
            }]
        });
    }
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Row deleted', 'success');
};

TB.duplicateRow = function(sectionIdx, rowIdx) {
    const section = this.content.sections[sectionIdx];
    if (!section || !section.rows || !section.rows[rowIdx]) return;
    const original = section.rows[rowIdx];
    const duplicate = JSON.parse(JSON.stringify(original));
    duplicate.id = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    duplicate.columns.forEach(col => {
        col.id = 'col_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        col.modules.forEach(mod => {
            mod.id = 'mod_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        });
    });
    section.rows.splice(rowIdx + 1, 0, duplicate);
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Row duplicated', 'success');
};

TB.moveRow = function(sectionIdx, rowIdx, dir) {
    const section = this.content.sections[sectionIdx];
    if (!section || !section.rows) return;
    const newIdx = rowIdx + dir;
    if (newIdx < 0 || newIdx >= section.rows.length) return;
    [section.rows[rowIdx], section.rows[newIdx]] = [section.rows[newIdx], section.rows[rowIdx]];
    this.saveToHistory();
    this.renderCanvas();
};

// ═══════════════════════════════════════════════════════════
// COLUMN MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addColumn = function(sectionIdx, rowIdx) {
    const row = this.content.sections[sectionIdx]?.rows[rowIdx];
    if (!row) return;
    if (!row.columns) row.columns = [];
    row.columns.push({
        id: 'col_' + Date.now(),
        width: (100 / (row.columns.length + 1)).toFixed(1) + '%',
        modules: []
    });
    // Redistribute widths evenly
    const newWidth = (100 / row.columns.length).toFixed(1) + '%';
    row.columns.forEach(col => col.width = newWidth);
    this.saveToHistory();
    this.renderCanvas();
};

TB.deleteColumn = function(sectionIdx, rowIdx, colIdx) {
    const row = this.content.sections[sectionIdx]?.rows[rowIdx];
    if (!row || !row.columns) return;
    if (row.columns.length <= 1) {
        this.showToast('Cannot delete last column. Delete the row instead.', 'error');
        return;
    }
    row.columns.splice(colIdx, 1);
    // Redistribute widths evenly
    const newWidth = (100 / row.columns.length).toFixed(1) + '%';
    row.columns.forEach(col => col.width = newWidth);
    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Column deleted', 'success');
};

TB.setColumnLayout = function(sectionIdx, rowIdx, layout) {
    const row = this.content.sections[sectionIdx]?.rows[rowIdx];
    if (!row) return;

    const layouts = {
        '1': ['100%'],
        '1-1': ['50%', '50%'],
        '1-1-1': ['33.33%', '33.33%', '33.33%'],
        '1-1-1-1': ['25%', '25%', '25%', '25%'],
        '2-1': ['66.66%', '33.33%'],
        '1-2': ['33.33%', '66.66%'],
        '3-1': ['75%', '25%'],
        '1-3': ['25%', '75%'],
        '1-2-1': ['25%', '50%', '25%'],
        '1-1-2': ['25%', '25%', '50%'],
        '2-1-1': ['50%', '25%', '25%']
    };

    const widths = layouts[layout];
    if (!widths) return;

    // Preserve existing modules where possible
    const existingModules = row.columns.map(col => col.modules || []);

    // Create new columns with specified widths
    row.columns = widths.map((width, idx) => ({
        id: 'col_' + Date.now() + '_' + idx,
        width: width,
        modules: existingModules[idx] || []
    }));

    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Layout changed to ' + layout.replace(/-/g, ':'), 'success');
};

// ═══════════════════════════════════════════════════════════
// MODULE MANAGEMENT
// ═══════════════════════════════════════════════════════════
TB.addModule = function(sectionIdx, rowIdx, colIdx, moduleType, insertIdx = null) {
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
    this.showToast('Module added', 'success');
};

TB.removeModule = function(sectionIdx, rowIdx, colIdx, moduleIdx) {
    const modules = this.content.sections[sectionIdx]?.rows[rowIdx]?.columns[colIdx]?.modules;
    if (!modules || !modules[moduleIdx]) return;
    modules.splice(moduleIdx, 1);
    this.selectedElement = null;
    this.saveToHistory();
    this.renderCanvas();
    this.renderSettings();
    this.showToast('Module deleted', 'success');
};

TB.duplicateModule = function(sectionIdx, rowIdx, colIdx, moduleIdx) {
    const modules = this.content.sections[sectionIdx]?.rows[rowIdx]?.columns[colIdx]?.modules;
    if (!modules || !modules[moduleIdx]) return;
    const duplicate = JSON.parse(JSON.stringify(modules[moduleIdx]));
    duplicate.id = 'mod_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    modules.splice(moduleIdx + 1, 0, duplicate);
    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sectionIdx, rowIdx, colIdx, moduleIdx + 1);
    this.showToast('Module duplicated', 'success');
};

TB.moveModule = function(sectionIdx, rowIdx, colIdx, fromIdx, toIdx) {
    const modules = this.content.sections[sectionIdx]?.rows[rowIdx]?.columns[colIdx]?.modules;
    if (!modules) return;
    if (toIdx < 0 || toIdx >= modules.length) return;
    const [module] = modules.splice(fromIdx, 1);
    modules.splice(toIdx, 0, module);
    this.saveToHistory();
    this.renderCanvas();
};

TB.moveModuleToColumn = function(fromPath, toPath) {
    const fromColumn = this.content.sections[fromPath.sIdx]?.rows[fromPath.rIdx]?.columns[fromPath.cIdx];
    const toColumn = this.content.sections[toPath.sIdx]?.rows[toPath.rIdx]?.columns[toPath.cIdx];
    if (!fromColumn || !toColumn) return;

    const [module] = fromColumn.modules.splice(fromPath.mIdx, 1);
    if (!toColumn.modules) toColumn.modules = [];
    toColumn.modules.splice(toPath.insertIdx !== undefined ? toPath.insertIdx : toColumn.modules.length, 0, module);

    this.saveToHistory();
    this.renderCanvas();
    this.showToast('Module moved', 'success');
};

TB.selectModule = function(sIdx, rIdx, cIdx, mIdx, event) {
    // Debug: Log parameters received
    console.log('TB.selectModule called with:', { sIdx, rIdx, cIdx, mIdx, hasEvent: !!event });

    // Stop propagation to prevent canvas click handler from interfering
    if (event) {
        event.stopPropagation();
    }

    // Validate indices before setting selectedElement
    if (sIdx === undefined || rIdx === undefined || cIdx === undefined || mIdx === undefined) {
        console.error('TB.selectModule: Invalid indices!', { sIdx, rIdx, cIdx, mIdx });
        return;
    }

    // Set the selected element to module type
    this.selectedElement = { type: 'module', sIdx, rIdx, cIdx, mIdx };
    console.log('TB.selectModule: selectedElement set to:', this.selectedElement);

    // Clear visual selection from all elements
    document.querySelectorAll('.tb-module').forEach(m => m.classList.remove('selected'));
    document.querySelectorAll('.tb-section').forEach(s => s.classList.remove('selected'));
    document.querySelectorAll('.tb-row').forEach(r => r.classList.remove('selected'));

    // Add selected class to the clicked module
    const moduleEl = document.querySelector('[data-module-path="' + sIdx + '-' + rIdx + '-' + cIdx + '-' + mIdx + '"]');
    if (moduleEl) moduleEl.classList.add('selected');

    // Ensure we have a valid tab and force render the module settings
    if (!this.currentTab) this.currentTab = 'content';

    // Force update the settings panel with module settings
    this.renderSettings(this.currentTab);
};

// ═══════════════════════════════════════════════════════════
// DROP ZONE HANDLERS
// ═══════════════════════════════════════════════════════════
TB.handleMainDropZoneDragOver = function(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
};

TB.handleMainDropZoneDragLeave = function(e) {
    e.currentTarget.classList.remove('drag-over');
};

TB.handleMainDropZoneDrop = function(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    const moduleType = e.dataTransfer.getData('module-type');
    if (moduleType) {
        // Create new section with the module
        const newModule = {
            id: 'mod_' + Date.now(),
            type: moduleType,
            content: this.getDefaultContent(moduleType),
            settings: {},
            design: {}
        };
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
    }
};

TB.handleColumnDrop = function(e, sIdx, rIdx, cIdx) {
    e.preventDefault();
    e.stopPropagation();
    const column = e.currentTarget;
    column.classList.remove('drag-over');
    document.querySelectorAll('.tb-insertion-line').forEach(el => el.remove());

    const moduleType = e.dataTransfer.getData('module-type');
    const moveData = e.dataTransfer.getData('module-move');

    if (moduleType) {
        // Adding new module from panel
        const insertIdx = this.insertionIndex >= 0 ? this.insertionIndex : null;
        this.addModule(sIdx, rIdx, cIdx, moduleType, insertIdx);
    } else if (moveData) {
        // Moving existing module
        const fromPath = JSON.parse(moveData);
        const toPath = { sIdx, rIdx, cIdx, insertIdx: this.insertionIndex >= 0 ? this.insertionIndex : undefined };
        
        // Don't move to same position
        if (fromPath.sIdx === toPath.sIdx && fromPath.rIdx === toPath.rIdx && 
            fromPath.cIdx === toPath.cIdx && fromPath.mIdx === toPath.insertIdx) {
            return;
        }
        
        this.moveModuleToColumn(fromPath, toPath);
    }

    this.insertionIndex = -1;
};

// ═══════════════════════════════════════════════════════════
// CONTENT MIGRATION
// ═══════════════════════════════════════════════════════════
TB.migrateContent = function() {
    console.log('🔄 migrateContent() called');
    console.log('🔄 BEFORE migration - first module settings:', this.content?.sections?.[0]?.rows?.[0]?.columns?.[0]?.modules?.[0]?.settings);
    
    if (!this.content.sections) return;

    this.content.sections.forEach(section => {
        // If section has no rows, create default structure
        if (!section.rows || section.rows.length === 0) {
            if (section.columns && section.columns.length > 0) {
                section.rows = [{ id: 'row_' + Date.now(), columns: section.columns }];
                delete section.columns;
            } else if (section.modules && section.modules.length > 0) {
                section.rows = [{ id: 'row_' + Date.now(), columns: [{ id: 'col_' + Date.now(), width: '100%', modules: section.modules }] }];
                delete section.modules;
            } else {
                section.rows = [{ id: 'row_' + Date.now(), columns: [{ id: 'col_' + Date.now(), width: '100%', modules: [] }] }];
            }
        }

        // Ensure each row has columns
        section.rows.forEach(row => {
            if (!row.columns || row.columns.length === 0) {
                if (row.modules && row.modules.length > 0) {
                    row.columns = [{ id: 'col_' + Date.now(), width: '100%', modules: row.modules }];
                    delete row.modules;
                } else {
                    row.columns = [{ id: 'col_' + Date.now(), width: '100%', modules: [] }];
                }
            }

            // Ensure each column has modules array and width
            row.columns.forEach(col => {
                if (!col.modules) col.modules = [];
                if (!col.width) col.width = (100 / row.columns.length).toFixed(2) + '%';
                if (!col.id) col.id = 'col_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            });

            if (!row.id) row.id = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        });
    });

    // FIX: Convert settings from array to object for all modules
    let convertedCount = 0;
    this.content.sections.forEach(section => {
        // Fix section settings
        if (!section.settings || Array.isArray(section.settings)) {
            section.settings = {};
        }
        
        section.rows?.forEach(row => {
            row.columns?.forEach(col => {
                col.modules?.forEach(mod => {
                    if (!mod.settings || Array.isArray(mod.settings)) {
                        mod.settings = {};
                        convertedCount++;
                    }
                    if (!mod.design || Array.isArray(mod.design)) {
                        mod.design = {};
                    }
                    if (!mod.content || Array.isArray(mod.content)) {
                        mod.content = {};
                    }
                });
            });
        });
    });

    console.log('🔄 AFTER migration - converted', convertedCount, 'modules');
    console.log('🔄 AFTER migration - first module settings:', this.content?.sections?.[0]?.rows?.[0]?.columns?.[0]?.modules?.[0]?.settings);
    console.log('✅ Content migrated successfully');
};

// ═══════════════════════════════════════════════════════════
// SAVE / LOAD / EXPORT
// ═══════════════════════════════════════════════════════════
TB.save = function() {
    const btn = document.getElementById('btn-save') || document.getElementById('saveBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Saving...';
    }

    // DEBUG: Log first module settings
    const firstMod = this.content?.sections?.[0]?.rows?.[0]?.columns?.[0]?.modules?.[0];
    console.log('💾 SAVE - First module settings:', firstMod?.settings);
    console.log('💾 SAVE - Full content:', JSON.stringify(this.content).substring(0, 1000));

    // Detect if this is a template or page
    const isTemplate = this.templateId !== undefined;
    const endpoint = isTemplate ? '/admin/theme-builder/templates/save' : '/admin/theme-builder/save';
    
    const data = {
        csrf_token: this.csrfToken,
        content: this.content
    };
    
    if (isTemplate) {
        // Template save data
        data.template_id = this.templateId;
        data.type = this.templateType || 'header';
        data.name = document.getElementById('template-name')?.value || 'Untitled Template';
        data.is_active = (document.getElementById('template-status')?.value === 'active') ? 1 : 0;
        data.conditions = this.savedConditions || null;
        data.priority = 0;
    } else {
        // Page save data
        data.page_id = this.pageId;
        data.title = document.getElementById('page-title')?.value || 'Untitled';
        data.slug = document.getElementById('page-slug')?.value || '';
        data.status = document.getElementById('page-status')?.value || 'draft';
    }

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const successMsg = isTemplate ? 'Template saved successfully!' : 'Page saved successfully!';
            this.showToast(successMsg, 'success');
            
            if (isTemplate) {
                // Handle new template ID
                if (data.template_id && !this.templateId) {
                    this.templateId = data.template_id;
                    const newUrl = '/admin/theme-builder/templates/' + data.template_id + '/edit';
                    window.history.replaceState({}, '', newUrl);
                }
            } else {
                // Handle new page ID
                if (data.page_id && !this.pageId) {
                    this.pageId = data.page_id;
                    document.getElementById('page-id').value = data.page_id;
                    const newUrl = '/admin/theme-builder/edit/' + data.page_id;
                    window.history.replaceState({}, '', newUrl);
                }
            }
            
            // Mark as clean after successful save
            this.isDirty = false;
        } else {
            this.showToast('Error: ' + (data.error || 'Failed to save'), 'error');
        }
    })
    .catch(err => {
        console.error('Save error:', err);
        this.showToast('Network error while saving', 'error');
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '💾 Save';
        }
    });
};

TB.exportJSON = function() {
    const data = JSON.stringify(this.content, null, 2);
    const blob = new Blob([data], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'theme-builder-export-' + Date.now() + '.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    this.showToast('Content exported', 'success');
};

TB.importJSON = function() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            try {
                const imported = JSON.parse(ev.target.result);
                if (imported.sections) {
                    if (confirm('This will replace all current content. Continue?')) {
                        this.content = imported;
                        this.saveToHistory();
                        this.renderCanvas();
                        this.showToast('Content imported successfully', 'success');
                    }
                } else {
                    this.showToast('Invalid format: missing sections', 'error');
                }
            } catch (err) {
                this.showToast('Invalid JSON file', 'error');
            }
        };
        reader.readAsText(file);
    };
    input.click();
};

TB.preview = function() {
    const slug = document.getElementById('page-slug')?.value;
    const pageId = this.pageId;
    
    if (!slug && !pageId) {
        this.showToast('Save the page first to preview', 'warning');
        return;
    }
    
    // Send current content to session via POST, then open preview
    this.showToast('Preparing preview...', 'info');
    
    fetch('/admin/theme-builder/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
        },
        body: JSON.stringify({
            csrf_token: this.csrfToken,
            content: this.content,
            page_id: pageId || 0
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.preview_url) {
            // Open preview URL (uses session data)
            window.open(data.preview_url, '_blank');
        } else if (data.html) {
            // Fallback: open blob with rendered HTML
            const blob = new Blob([data.html], { type: 'text/html' });
            window.open(URL.createObjectURL(blob), '_blank');
        } else if (slug) {
            // Final fallback: direct URL
            window.open('/preview/tb/' + (pageId || 0) + '?session=1', '_blank');
        } else {
            this.showToast('Preview failed', 'error');
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        // Fallback on error
        if (slug) {
            window.open('/preview/tb/' + (pageId || 0), '_blank');
        } else {
            this.showToast('Preview failed: ' + error.message, 'error');
        }
    });
};

// ═══════════════════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ═══════════════════════════════════════════════════════════
TB.showToast = function(message, type = 'info') {
    // Remove existing toasts
    document.querySelectorAll('.tb-toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = 'tb-toast tb-toast-' + type;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = '<span class="tb-toast-icon">' + (icons[type] || icons.info) + '</span><span class="tb-toast-message">' + this.escapeHtml(message) + '</span>';
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto-remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

// ═══════════════════════════════════════════════════════════
// RESPONSIVE DEVICE SYSTEM
// ═══════════════════════════════════════════════════════════
TB.setDevice = function(device) {
    if (!['desktop', 'tablet', 'mobile'].includes(device)) return;
    this.currentDevice = device;

    // Update device toggle buttons
    document.querySelectorAll('.tb-device-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.device === device);
    });

    // Sync with canvas viewport buttons
    document.querySelectorAll('.tb-viewport-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.viewport === device);
    });

    // Update canvas class
    const canvasInner = document.getElementById('canvas-inner');
    if (canvasInner) {
        canvasInner.className = 'tb-canvas-inner ' + device;
    }

    // Re-render settings panel to show values for current device
    this.renderSettings();
};

TB.getResponsivePropertyKey = function(property) {
    if (this.currentDevice === 'desktop') return property;
    return property + '_' + this.currentDevice;
};

TB.getResponsiveValue = function(settings, property) {
    if (!settings) return '';
    if (this.currentDevice === 'desktop') {
        return settings[property] || '';
    }
    const deviceKey = property + '_' + this.currentDevice;
    const deviceValue = settings[deviceKey];
    if (deviceValue !== undefined && deviceValue !== '') {
        return deviceValue;
    }
    // Inherit from desktop
    return settings[property] || '';
};

TB.getDeviceSpecificValue = function(settings, property) {
    if (!settings) return '';
    if (this.currentDevice === 'desktop') {
        return settings[property] || '';
    }
    const deviceKey = property + '_' + this.currentDevice;
    return settings[deviceKey] || '';
};

TB.hasResponsiveValues = function(settings, property) {
    if (!settings) return false;
    const tabletKey = property + '_tablet';
    const mobileKey = property + '_mobile';
    return (settings[tabletKey] !== undefined && settings[tabletKey] !== '') ||
           (settings[mobileKey] !== undefined && settings[mobileKey] !== '');
};

TB.updateResponsiveValue = function(sIdx, rIdx, cIdx, mIdx, property, value) {
    const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
    if (!mod) return;
    if (!mod.settings || Array.isArray(mod.settings)) mod.settings = {};

    const key = this.getResponsivePropertyKey(property);
    mod.settings[key] = value;

    this.saveToHistory();
    this.renderCanvas();
    this.selectModule(sIdx, rIdx, cIdx, mIdx);
};

// ═══════════════════════════════════════════════════════════
// VIDEO EMBED HELPER
// ═══════════════════════════════════════════════════════════
TB.getVideoEmbedUrl = function(url) {
    if (!url) return '';
    // YouTube
    const ytMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) return 'https://www.youtube.com/embed/' + ytMatch[1];
    // Vimeo
    const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
    if (vimeoMatch) return 'https://player.vimeo.com/video/' + vimeoMatch[1];
    // Direct video URL (MP4, etc.)
    if (url.match(/\.(mp4|webm|ogg)(\?.*)?$/i)) return url;
    return url;
};

// ═══════════════════════════════════════════════════════════
// MEDIA GALLERY INTEGRATION
// ═══════════════════════════════════════════════════════════
TB.openMediaGallery = function(callback) {
    // Check if media gallery modal exists
    let modal = document.getElementById('tb-media-modal');
    if (!modal) {
        // Create simple URL input fallback
        const url = prompt('Enter image URL:');
        if (url && callback) callback(url);
        return;
    }
    
    // Store callback for when image is selected
    this.mediaGalleryCallback = callback;
    modal.classList.add('active');
};

TB.closeMediaGallery = function() {
    const modal = document.getElementById('tb-media-modal');
    if (modal) modal.classList.remove('active');
    this.mediaGalleryCallback = null;
};

TB.selectMediaImage = function(url) {
    if (this.mediaGalleryCallback) {
        this.mediaGalleryCallback(url);
    }
    this.closeMediaGallery();
};

// ═══════════════════════════════════════════════════════════
// MEDIA GALLERY TABS & EVENTS
// ═══════════════════════════════════════════════════════════

TB.initMediaGalleryEvents = function() {
    // Tab switching
    document.querySelectorAll('.tb-media-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            this.switchMediaTab(tabName);
        });
    });

    // Media item selection in library
    document.querySelectorAll('.tb-media-item').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
            this.selectedMediaUrl = item.getAttribute('data-url');
            document.getElementById('tb-media-select-btn').disabled = false;
        });
    });

    // File upload handling
    const uploadInput = document.getElementById('tb-media-upload');
    if (uploadInput) {
        uploadInput.addEventListener('change', (e) => this.handleMediaUpload(e));
    }

    // Drag and drop
    const uploadArea = document.getElementById('tb-upload-area');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--tb-accent)';
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '';
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '';
            if (e.dataTransfer.files.length) {
                this.handleMediaUpload({ target: { files: e.dataTransfer.files } });
            }
        });
    }

    // Stock search enter key
    const stockInput = document.getElementById('tb-stock-search-input');
    if (stockInput) {
        stockInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.searchStockPhotos();
        });
    }
};

TB.switchMediaTab = function(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tb-media-tab').forEach(tab => {
        tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName);
    });
    // Update tab content
    document.querySelectorAll('.tb-media-tab-content').forEach(content => {
        content.classList.toggle('active', content.id === 'tb-media-tab-' + tabName);
    });
    // Reset selection when switching tabs
    this.selectedMediaUrl = null;
    document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
    const selectBtn = document.getElementById('tb-media-select-btn');
    if (selectBtn) selectBtn.disabled = true;
};

TB.selectMediaFromGallery = function() {
    if (this.selectedMediaUrl && this.mediaGalleryCallback) {
        this.mediaGalleryCallback(this.selectedMediaUrl);
    }
    this.closeMediaGallery();
};

TB.handleMediaUpload = async function(e) {
    const file = e.target.files?.[0];
    if (!file) return;

    const progress = document.getElementById('tb-upload-progress');
    const bar = document.getElementById('tb-upload-bar');
    if (progress) progress.style.display = 'block';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', this.csrfToken);

    try {
        const response = await fetch('/admin/media/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            body: formData
        });
        const data = await response.json();
        
        if (data.success && data.url) {
            if (bar) bar.style.width = '100%';
            setTimeout(() => {
                if (this.mediaGalleryCallback) {
                    this.mediaGalleryCallback(data.url);
                }
                this.closeMediaGallery();
            }, 500);
        } else {
            this.showToast(data.error || 'Upload failed', 'error');
        }
    } catch (err) {
        console.error('Upload error:', err);
        this.showToast('Upload failed', 'error');
    } finally {
        if (progress) progress.style.display = 'none';
        if (bar) bar.style.width = '0%';
    }
};

TB.searchStockPhotos = async function() {
    const query = document.getElementById('tb-stock-search-input')?.value;
    if (!query) return;

    const results = document.getElementById('tb-stock-results');
    if (results) results.innerHTML = '<div class="tb-stock-loading"><p>🔍 Searching Pexels...</p></div>';

    try {
        const response = await fetch('/api/stock-images.php?q=' + encodeURIComponent(query), {
            headers: { 'X-CSRF-TOKEN': this.csrfToken }
        });
        const data = await response.json();
        
        if (data.error) {
            results.innerHTML = '<div class="tb-stock-loading"><p style="color:var(--tb-danger)">' + data.error + '</p></div>';
            return;
        }
        
        if (data.images?.length) {
            let html = '<div class="tb-media-grid">';
            data.images.forEach(img => {
                html += '<div class="tb-media-item" data-url="' + img.url + '"><img src="' + img.preview + '" alt="' + (img.alt || '') + '"><div class="tb-stock-credit">' + (img.photographer || 'Pexels') + '</div></div>';
            });
            html += '</div>';
            if (data.note) {
                html += '<p style="font-size:11px;color:var(--tb-text-muted);text-align:center;margin-top:8px">' + data.note + '</p>';
            }
            results.innerHTML = html;
            
            // Add click handlers to new items
            results.querySelectorAll('.tb-media-item').forEach(item => {
                item.addEventListener('click', () => {
                    document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
                    item.classList.add('selected');
                    this.selectedMediaUrl = item.getAttribute('data-url');
                    document.getElementById('tb-media-select-btn').disabled = false;
                });
            });
        } else {
            results.innerHTML = '<div class="tb-stock-loading"><p>No photos found. Try different keywords.</p></div>';
        }
    } catch (err) {
        console.error('Stock search error:', err);
        results.innerHTML = '<div class="tb-stock-loading"><p style="color:var(--tb-danger)">Search failed. Please try again.</p></div>';
    }
};

TB.generateAiImage = async function() {
    // Prevent multiple clicks
    if (this._aiGenerating) return;
    
    const prompt = document.getElementById('tb-ai-image-prompt')?.value;
    if (!prompt) {
        this.showToast('Please enter a description', 'error');
        return;
    }

    const style = document.getElementById('tb-ai-image-style')?.value || 'photorealistic';
    const size = document.getElementById('tb-ai-image-size')?.value || '1024x1024';
    const preview = document.getElementById('tb-ai-gen-preview');
    const genBtn = document.querySelector('#tb-media-tab-ai .tb-btn-ai');
    
    // Lock
    this._aiGenerating = true;
    
    if (genBtn) {
        genBtn.disabled = true;
        genBtn.innerHTML = '✨ Generating...';
    }
    if (preview) preview.innerHTML = '<div class="tb-ai-gen-status"><p>✨ Generating image...</p><p style="font-size:0.75rem;color:var(--tb-text-muted)">This may take 10-30 seconds</p></div>';

    try {
        const response = await fetch('/api/ai-generate-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                prompt: prompt,
                style: style,
                size: size
            })
        });
        const data = await response.json();
        
        if (data.success && data.url) {
            preview.innerHTML = '<div class="tb-media-item selected" data-url="' + data.url + '" style="max-width:300px;margin:0 auto"><img src="' + data.url + '" alt="AI Generated"></div>';
            this.selectedMediaUrl = data.url;
            document.getElementById('tb-media-select-btn').disabled = false;
        } else {
            preview.innerHTML = '<div class="tb-ai-gen-status"><p style="color:var(--tb-danger)">' + (data.error || 'Generation failed') + '</p></div>';
        }
    } catch (err) {
        console.error('AI generate error:', err);
        preview.innerHTML = '<div class="tb-ai-gen-status"><p style="color:var(--tb-danger)">Generation failed. Please try again.</p></div>';
    } finally {
        // Unlock
        this._aiGenerating = false;
        if (genBtn) {
            genBtn.disabled = false;
            genBtn.innerHTML = '✨ Generate';
        }
    }
};

// ═══════════════════════════════════════════════════════════
// END OF TB-STRUCTURE.JS
// ═══════════════════════════════════════════════════════════
console.log('TB 3.0: tb-structure.js loaded');
