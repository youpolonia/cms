<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Form Builder';
$form = $form ?? null;
$formId = $form ? (int)$form['id'] : 0;
$formName = $form ? $form['name'] : '';
$formFields = $form ? json_encode($form['fields'], JSON_UNESCAPED_UNICODE) : '[]';
$formSuccessMessage = $form ? ($form['success_message'] ?? '') : 'Thank you! Your form has been submitted.';
$formRedirectUrl = $form ? ($form['redirect_url'] ?? '') : '';
$formEmailTo = $form ? ($form['email_to'] ?? '') : '';
$formActive = $form ? (int)$form['active'] : 1;
$csrfToken = csrf_token();
?>

<style>
/* ─── Form Builder Layout ─── */
.fb-editor { display:flex; gap:0; min-height:calc(100vh - 140px); margin: -32px -24px; }
.fb-sidebar-left { width:240px; background:var(--bg-primary); border-right:1px solid var(--border); padding:16px; flex-shrink:0; overflow-y:auto; }
.fb-canvas-wrap { flex:1; display:flex; flex-direction:column; background:var(--bg-secondary); }
.fb-sidebar-right { width:320px; background:var(--bg-primary); border-left:1px solid var(--border); padding:0; flex-shrink:0; overflow-y:auto; }

/* ─── Top Bar ─── */
.fb-topbar { display:flex; align-items:center; gap:12px; padding:12px 20px; background:var(--bg-primary); border-bottom:1px solid var(--border); }
.fb-topbar input[type="text"] { flex:1; font-size:18px; font-weight:600; background:transparent; border:1px solid transparent; color:var(--text-primary); padding:6px 10px; border-radius:var(--radius); }
.fb-topbar input[type="text"]:focus { border-color:var(--accent); outline:none; background:var(--bg-tertiary); }

/* ─── Sidebar Field Types ─── */
.fb-sidebar-left h3 { font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin:16px 0 8px; }
.fb-sidebar-left h3:first-child { margin-top:0; }
.fb-type-item { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:var(--radius); cursor:grab; font-size:13px; color:var(--text-secondary); border:1px solid transparent; transition:all .15s; user-select:none; }
.fb-type-item:hover { background:var(--accent-muted); color:var(--text-primary); border-color:var(--accent); }
.fb-type-item.dragging { opacity:0.5; }
.fb-type-icon { font-size:16px; width:24px; text-align:center; flex-shrink:0; }

/* ─── Canvas ─── */
.fb-canvas { flex:1; padding:24px; overflow-y:auto; }
.fb-canvas-inner { max-width:700px; margin:0 auto; min-height:300px; border:2px dashed var(--border); border-radius:var(--radius-lg); padding:16px; transition:border-color .15s; position:relative; }
.fb-canvas-inner.drag-over { border-color:var(--accent); background:var(--accent-muted); }
.fb-canvas-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:250px; color:var(--text-muted); }
.fb-canvas-empty .icon { font-size:48px; margin-bottom:12px; }

/* ─── Canvas Field Items ─── */
.fb-canvas-field { display:flex; align-items:flex-start; gap:8px; padding:12px; margin-bottom:8px; background:var(--bg-primary); border:1px solid var(--border); border-radius:var(--radius); cursor:pointer; transition:all .15s; position:relative; }
.fb-canvas-field:hover { border-color:var(--accent); }
.fb-canvas-field.selected { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-muted); }
.fb-canvas-field.drag-over-above { border-top:3px solid var(--accent); }
.fb-canvas-field.drag-over-below { border-bottom:3px solid var(--accent); }
.fb-canvas-field.dragging { opacity:0.4; }
.fb-drag-handle { cursor:grab; color:var(--text-muted); font-size:14px; padding:4px 2px; flex-shrink:0; user-select:none; }
.fb-drag-handle:active { cursor:grabbing; }
.fb-field-preview { flex:1; min-width:0; }
.fb-field-preview .label { font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:4px; }
.fb-field-preview .label .req { color:var(--danger); }
.fb-field-preview .type-badge { font-size:11px; color:var(--text-muted); background:var(--bg-tertiary); padding:2px 8px; border-radius:10px; display:inline-block; margin-left:6px; }
.fb-field-preview .placeholder { font-size:12px; color:var(--text-muted); margin-top:4px; padding:6px 10px; background:var(--bg-tertiary); border-radius:var(--radius); border:1px solid var(--border); }
.fb-field-delete { color:var(--text-muted); cursor:pointer; font-size:14px; padding:4px; border:none; background:none; border-radius:var(--radius); transition:all .15s; flex-shrink:0; }
.fb-field-delete:hover { color:var(--danger); background:var(--danger-bg); }

/* Half-width fields in canvas */
.fb-canvas-field[data-width="half"] { display:inline-flex; width:calc(50% - 4px); vertical-align:top; }

/* ─── Right Sidebar Properties ─── */
.fb-props-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px 20px; color:var(--text-muted); text-align:center; }
.fb-props-empty .icon { font-size:36px; margin-bottom:12px; }
.fb-props-header { padding:14px 16px; border-bottom:1px solid var(--border); font-weight:600; font-size:14px; display:flex; align-items:center; justify-content:space-between; }
.fb-props-body { padding:16px; }
.fb-props-body .form-group { margin-bottom:14px; }
.fb-props-body .form-label { font-size:12px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px; display:block; }
.fb-props-body .form-input, .fb-props-body .form-select, .fb-props-body .form-textarea { font-size:13px; padding:8px 10px; }

/* Toggle switch */
.fb-toggle { display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; }
.fb-toggle input { display:none; }
.fb-toggle-track { width:36px; height:20px; border-radius:10px; background:var(--bg-tertiary); border:1px solid var(--border); position:relative; transition:all .15s; flex-shrink:0; }
.fb-toggle-track::after { content:''; position:absolute; left:2px; top:2px; width:14px; height:14px; border-radius:50%; background:var(--text-muted); transition:all .15s; }
.fb-toggle input:checked + .fb-toggle-track { background:var(--accent); border-color:var(--accent); }
.fb-toggle input:checked + .fb-toggle-track::after { left:18px; background:#fff; }

/* Options editor */
.fb-options-list { display:flex; flex-direction:column; gap:4px; }
.fb-option-row { display:flex; gap:4px; align-items:center; }
.fb-option-row input { flex:1; font-size:12px; padding:6px 8px; }
.fb-option-row button { width:28px; height:28px; border:none; border-radius:var(--radius); cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.fb-option-remove { background:var(--danger-bg); color:var(--danger); }
.fb-option-remove:hover { background:rgba(243,139,168,0.35); }
.fb-option-add { background:var(--accent-muted); color:var(--accent); font-size:12px; padding:6px 12px; width:auto; }
.fb-option-add:hover { background:rgba(137,180,250,0.25); }

/* ─── Bottom Settings ─── */
.fb-settings { padding:20px; border-top:1px solid var(--border); background:var(--bg-primary); }
.fb-settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.fb-settings-grid .full { grid-column:1 / -1; }

/* ─── Width selector ─── */
.fb-width-btns { display:flex; gap:4px; }
.fb-width-btn { padding:4px 12px; font-size:12px; border:1px solid var(--border); background:var(--bg-tertiary); border-radius:var(--radius); cursor:pointer; color:var(--text-secondary); transition:all .15s; }
.fb-width-btn.active { background:var(--accent); color:#fff; border-color:var(--accent); }
</style>

<div class="fb-editor">
    <!-- LEFT SIDEBAR: Field Types -->
    <div class="fb-sidebar-left">
        <h3>Input Fields</h3>
        <div class="fb-type-item" draggable="true" data-type="text"><span class="fb-type-icon">📝</span> Text</div>
        <div class="fb-type-item" draggable="true" data-type="email"><span class="fb-type-icon">✉️</span> Email</div>
        <div class="fb-type-item" draggable="true" data-type="phone"><span class="fb-type-icon">📞</span> Phone</div>
        <div class="fb-type-item" draggable="true" data-type="number"><span class="fb-type-icon">🔢</span> Number</div>
        <div class="fb-type-item" draggable="true" data-type="textarea"><span class="fb-type-icon">📄</span> Textarea</div>

        <h3>Choice Fields</h3>
        <div class="fb-type-item" draggable="true" data-type="select"><span class="fb-type-icon">📋</span> Select</div>
        <div class="fb-type-item" draggable="true" data-type="radio"><span class="fb-type-icon">🔘</span> Radio</div>
        <div class="fb-type-item" draggable="true" data-type="checkbox"><span class="fb-type-icon">☑️</span> Checkbox</div>
        <div class="fb-type-item" draggable="true" data-type="checkbox_group"><span class="fb-type-icon">☑️</span> Checkbox Group</div>

        <h3>Date & Time</h3>
        <div class="fb-type-item" draggable="true" data-type="date"><span class="fb-type-icon">📅</span> Date</div>
        <div class="fb-type-item" draggable="true" data-type="time"><span class="fb-type-icon">⏰</span> Time</div>

        <h3>Special</h3>
        <div class="fb-type-item" draggable="true" data-type="file"><span class="fb-type-icon">📎</span> File Upload</div>
        <div class="fb-type-item" draggable="true" data-type="hidden"><span class="fb-type-icon">🔒</span> Hidden</div>

        <h3>Layout</h3>
        <div class="fb-type-item" draggable="true" data-type="heading"><span class="fb-type-icon">📌</span> Heading</div>
        <div class="fb-type-item" draggable="true" data-type="paragraph"><span class="fb-type-icon">📝</span> Paragraph</div>
    </div>

    <!-- CENTER: Canvas -->
    <div class="fb-canvas-wrap">
        <div class="fb-topbar">
            <input type="text" id="fb-form-name" placeholder="Form Name" value="<?= h($formName) ?>">
            <button class="btn btn-secondary btn-sm" id="fb-preview-btn" title="Preview">👁️ Preview</button>
            <button class="btn btn-primary btn-sm" id="fb-save-btn">💾 Save</button>
        </div>

        <div class="fb-canvas">
            <div class="fb-canvas-inner" id="fb-canvas">
                <div class="fb-canvas-empty" id="fb-empty-msg">
                    <div class="icon">📋</div>
                    <div style="font-size:16px;font-weight:600;margin-bottom:4px;">Drag fields here</div>
                    <div style="font-size:13px;">Drop field types from the left sidebar to build your form</div>
                </div>
            </div>
        </div>

        <!-- Bottom Settings -->
        <div class="fb-settings">
            <div style="font-weight:600;font-size:14px;margin-bottom:12px;">⚙️ Form Settings</div>
            <div class="fb-settings-grid">
                <div class="form-group">
                    <label class="form-label">Success Message</label>
                    <textarea id="fb-success-msg" class="form-textarea" rows="2"><?= h($formSuccessMessage) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Redirect URL (optional)</label>
                    <input type="text" id="fb-redirect-url" class="form-input" placeholder="https://..." value="<?= h($formRedirectUrl) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Notifications To</label>
                    <input type="email" id="fb-email-to" class="form-input" placeholder="admin@example.com" value="<?= h($formEmailTo) ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;">
                    <label class="fb-toggle">
                        <input type="checkbox" id="fb-active" <?= $formActive ? 'checked' : '' ?>>
                        <span class="fb-toggle-track"></span>
                        Form Active
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDEBAR: Properties -->
    <div class="fb-sidebar-right">
        <div id="fb-props-empty" class="fb-props-empty">
            <div class="icon">🖱️</div>
            <div style="font-weight:600;margin-bottom:4px;">No field selected</div>
            <div style="font-size:13px;">Click a field in the canvas to edit its properties</div>
        </div>
        <div id="fb-props-panel" style="display:none;">
            <div class="fb-props-header">
                <span>Field Properties</span>
                <span id="fb-props-type" style="font-size:12px;color:var(--text-muted);font-weight:400;"></span>
            </div>
            <div class="fb-props-body">
                <div class="form-group">
                    <label class="form-label">Label</label>
                    <input type="text" id="fp-label" class="form-input" placeholder="Field label">
                </div>
                <div class="form-group">
                    <label class="form-label">Name (auto)</label>
                    <input type="text" id="fp-name" class="form-input" placeholder="field_name">
                </div>
                <div class="form-group" id="fp-placeholder-wrap">
                    <label class="form-label">Placeholder</label>
                    <input type="text" id="fp-placeholder" class="form-input" placeholder="Placeholder text">
                </div>
                <div class="form-group">
                    <label class="form-label">Width</label>
                    <div class="fb-width-btns">
                        <button class="fb-width-btn active" data-width="full">Full</button>
                        <button class="fb-width-btn" data-width="half">Half</button>
                    </div>
                </div>
                <div class="form-group" id="fp-required-wrap">
                    <label class="fb-toggle">
                        <input type="checkbox" id="fp-required">
                        <span class="fb-toggle-track"></span>
                        Required
                    </label>
                </div>
                <div class="form-group" id="fp-options-wrap" style="display:none;">
                    <label class="form-label">Options</label>
                    <div class="fb-options-list" id="fp-options-list"></div>
                    <button class="fb-option-add" id="fp-add-option" style="margin-top:6px;">+ Add Option</button>
                </div>
                <div class="form-group" id="fp-helptext-wrap">
                    <label class="form-label">Help Text</label>
                    <input type="text" id="fp-helptext" class="form-input" placeholder="Optional help text">
                </div>
                <div class="form-group" id="fp-validation-wrap" style="display:none;">
                    <label class="form-label">Validation</label>
                    <div id="fp-validation-fields"></div>
                </div>
                <div class="form-group" id="fp-default-wrap" style="display:none;">
                    <label class="form-label">Default Value</label>
                    <input type="text" id="fp-default" class="form-input" placeholder="Default value">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="fb-preview-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;">
    <div style="background:var(--bg-primary);border-radius:var(--radius-lg);max-width:700px;width:90%;max-height:90vh;overflow-y:auto;padding:24px;position:relative;">
        <button onclick="document.getElementById('fb-preview-modal').style.display='none'" style="position:absolute;top:12px;right:12px;background:none;border:none;color:var(--text-muted);font-size:20px;cursor:pointer;">&times;</button>
        <h2 style="margin-bottom:16px;">Form Preview</h2>
        <div id="fb-preview-content"></div>
    </div>
</div>

<script>
(function() {
    'use strict';

    /* ═══════════════════════════════════════════════════
     *  STATE
     * ═══════════════════════════════════════════════════ */
    const FORM_ID = <?= $formId ?>;
    const CSRF = '<?= h($csrfToken) ?>';
    let fields = <?= $formFields ?>;
    let selectedIdx = -1;
    let dragSrcIdx = -1;
    let isDraggingType = false;

    const canvas = document.getElementById('fb-canvas');
    const emptyMsg = document.getElementById('fb-empty-msg');

    /* ═══════════════════════════════════════════════════
     *  FIELD DEFAULTS
     * ═══════════════════════════════════════════════════ */
    function fieldDefaults(type) {
        const labels = {
            text:'Text Field', email:'Email', phone:'Phone', number:'Number',
            textarea:'Message', select:'Select', radio:'Radio Group',
            checkbox:'Checkbox', checkbox_group:'Checkbox Group',
            date:'Date', time:'Time', file:'File Upload', hidden:'Hidden',
            heading:'Section Heading', paragraph:'Description text'
        };
        const base = {
            type: type,
            label: labels[type] || 'Field',
            name: '',
            required: false,
            placeholder: '',
            options: [],
            validation: {},
            width: 'full',
            helpText: ''
        };
        if (['select','radio','checkbox_group'].includes(type)) {
            base.options = ['Option 1','Option 2','Option 3'];
        }
        if (type === 'hidden') {
            base.defaultValue = '';
        }
        base.name = slugify(base.label);
        return base;
    }

    function slugify(str) {
        return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'field';
    }

    function ensureUniqueName(name, exceptIdx) {
        const existing = fields.map((f,i) => i !== exceptIdx ? f.name : null).filter(Boolean);
        if (!existing.includes(name)) return name;
        let i = 2;
        while (existing.includes(name + '_' + i)) i++;
        return name + '_' + i;
    }

    /* ═══════════════════════════════════════════════════
     *  RENDER CANVAS
     * ═══════════════════════════════════════════════════ */
    function renderCanvas() {
        // Remove existing field elements (keep emptyMsg)
        canvas.querySelectorAll('.fb-canvas-field').forEach(el => el.remove());
        emptyMsg.style.display = fields.length ? 'none' : 'flex';

        fields.forEach((f, idx) => {
            const el = document.createElement('div');
            el.className = 'fb-canvas-field' + (idx === selectedIdx ? ' selected' : '');
            el.setAttribute('data-idx', idx);
            el.setAttribute('data-width', f.width || 'full');
            el.draggable = true;

            const typeIcons = {
                text:'📝', email:'✉️', phone:'📞', number:'🔢', textarea:'📄',
                select:'📋', radio:'🔘', checkbox:'☑️', checkbox_group:'☑️',
                date:'📅', time:'⏰', file:'📎', hidden:'🔒',
                heading:'📌', paragraph:'📝'
            };

            let preview = '';
            if (f.type === 'heading') {
                preview = '<div style="font-size:16px;font-weight:700;">' + esc(f.label) + '</div>';
            } else if (f.type === 'paragraph') {
                preview = '<div style="font-size:13px;color:var(--text-muted);">' + esc(f.label) + '</div>';
            } else {
                const req = f.required ? '<span class="req">*</span>' : '';
                const ph = f.placeholder ? '<div class="placeholder">' + esc(f.placeholder) + '</div>' : '';
                preview = '<div class="label">' + esc(f.label) + req + ' <span class="type-badge">' + (typeIcons[f.type]||'') + ' ' + f.type + '</span></div>' + ph;
            }

            el.innerHTML = '<div class="fb-drag-handle" title="Drag to reorder">⠿</div>' +
                '<div class="fb-field-preview">' + preview + '</div>' +
                '<button class="fb-field-delete" title="Delete field" data-idx="' + idx + '">&times;</button>';

            canvas.appendChild(el);
        });

        bindCanvasEvents();
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    /* ═══════════════════════════════════════════════════
     *  CANVAS EVENTS
     * ═══════════════════════════════════════════════════ */
    function bindCanvasEvents() {
        // Click to select
        canvas.querySelectorAll('.fb-canvas-field').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target.closest('.fb-field-delete')) return;
                selectedIdx = parseInt(el.getAttribute('data-idx'));
                renderCanvas();
                showProperties(selectedIdx);
            });
        });

        // Delete buttons
        canvas.querySelectorAll('.fb-field-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-idx'));
                fields.splice(idx, 1);
                if (selectedIdx === idx) { selectedIdx = -1; hideProperties(); }
                else if (selectedIdx > idx) selectedIdx--;
                renderCanvas();
            });
        });

        // Drag reorder within canvas
        canvas.querySelectorAll('.fb-canvas-field').forEach(el => {
            el.addEventListener('dragstart', (e) => {
                if (isDraggingType) return;
                dragSrcIdx = parseInt(el.getAttribute('data-idx'));
                el.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', 'reorder');
            });

            el.addEventListener('dragend', () => {
                el.classList.remove('dragging');
                canvas.querySelectorAll('.fb-canvas-field').forEach(c => {
                    c.classList.remove('drag-over-above', 'drag-over-below');
                });
                dragSrcIdx = -1;
            });

            el.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = isDraggingType ? 'copy' : 'move';
                const rect = el.getBoundingClientRect();
                const mid = rect.top + rect.height / 2;
                el.classList.remove('drag-over-above', 'drag-over-below');
                if (e.clientY < mid) el.classList.add('drag-over-above');
                else el.classList.add('drag-over-below');
            });

            el.addEventListener('dragleave', () => {
                el.classList.remove('drag-over-above', 'drag-over-below');
            });

            el.addEventListener('drop', (e) => {
                e.preventDefault();
                const rect = el.getBoundingClientRect();
                const mid = rect.top + rect.height / 2;
                const targetIdx = parseInt(el.getAttribute('data-idx'));
                const insertBefore = e.clientY < mid;

                el.classList.remove('drag-over-above', 'drag-over-below');

                if (isDraggingType) {
                    // New field from sidebar
                    const type = e.dataTransfer.getData('text/plain');
                    if (type && type !== 'reorder') {
                        const f = fieldDefaults(type);
                        f.name = ensureUniqueName(f.name, -1);
                        const insertAt = insertBefore ? targetIdx : targetIdx + 1;
                        fields.splice(insertAt, 0, f);
                        selectedIdx = insertAt;
                        renderCanvas();
                        showProperties(selectedIdx);
                    }
                } else if (dragSrcIdx >= 0 && dragSrcIdx !== targetIdx) {
                    // Reorder
                    const moved = fields.splice(dragSrcIdx, 1)[0];
                    let insertAt = insertBefore ? targetIdx : targetIdx + 1;
                    if (dragSrcIdx < targetIdx) insertAt--;
                    fields.splice(insertAt, 0, moved);
                    if (selectedIdx === dragSrcIdx) selectedIdx = insertAt;
                    else if (selectedIdx > dragSrcIdx && selectedIdx <= insertAt) selectedIdx--;
                    else if (selectedIdx < dragSrcIdx && selectedIdx >= insertAt) selectedIdx++;
                    renderCanvas();
                    showProperties(selectedIdx);
                }
                isDraggingType = false;
            });
        });
    }

    /* ═══════════════════════════════════════════════════
     *  SIDEBAR DRAG → CANVAS
     * ═══════════════════════════════════════════════════ */
    document.querySelectorAll('.fb-type-item').forEach(item => {
        item.addEventListener('dragstart', (e) => {
            isDraggingType = true;
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', item.getAttribute('data-type'));
            item.classList.add('dragging');
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            isDraggingType = false;
        });
    });

    // Canvas drop zone (for empty canvas or drop at end)
    canvas.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!e.target.closest('.fb-canvas-field')) {
            canvas.querySelector('.fb-canvas-inner')?.classList.add('drag-over');
        }
    });
    canvas.addEventListener('dragleave', (e) => {
        if (!canvas.contains(e.relatedTarget)) {
            canvas.querySelector('.fb-canvas-inner')?.classList.remove('drag-over');
        }
    });
    canvas.addEventListener('drop', (e) => {
        canvas.querySelector('.fb-canvas-inner')?.classList.remove('drag-over');
        if (e.target.closest('.fb-canvas-field')) return; // handled by field drop

        e.preventDefault();
        const type = e.dataTransfer.getData('text/plain');
        if (type && type !== 'reorder') {
            const f = fieldDefaults(type);
            f.name = ensureUniqueName(f.name, -1);
            fields.push(f);
            selectedIdx = fields.length - 1;
            renderCanvas();
            showProperties(selectedIdx);
        }
        isDraggingType = false;
    });

    /* ═══════════════════════════════════════════════════
     *  PROPERTIES PANEL
     * ═══════════════════════════════════════════════════ */
    const propsEmpty = document.getElementById('fb-props-empty');
    const propsPanel = document.getElementById('fb-props-panel');

    function hideProperties() {
        propsEmpty.style.display = 'flex';
        propsPanel.style.display = 'none';
    }

    function showProperties(idx) {
        if (idx < 0 || idx >= fields.length) { hideProperties(); return; }

        const f = fields[idx];
        propsEmpty.style.display = 'none';
        propsPanel.style.display = 'block';

        document.getElementById('fb-props-type').textContent = f.type;
        document.getElementById('fp-label').value = f.label || '';
        document.getElementById('fp-name').value = f.name || '';
        document.getElementById('fp-placeholder').value = f.placeholder || '';
        document.getElementById('fp-required').checked = !!f.required;
        document.getElementById('fp-helptext').value = f.helpText || '';

        // Width buttons
        document.querySelectorAll('.fb-width-btn').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-width') === (f.width || 'full'));
        });

        // Show/hide sections based on type
        const hasPlaceholder = !['checkbox','checkbox_group','radio','file','hidden','heading','paragraph'].includes(f.type);
        const hasRequired = !['heading','paragraph'].includes(f.type);
        const hasOptions = ['select','radio','checkbox_group'].includes(f.type);
        const hasDefault = f.type === 'hidden';
        const hasValidation = ['number','file'].includes(f.type);

        document.getElementById('fp-placeholder-wrap').style.display = hasPlaceholder ? '' : 'none';
        document.getElementById('fp-required-wrap').style.display = hasRequired ? '' : 'none';
        document.getElementById('fp-options-wrap').style.display = hasOptions ? '' : 'none';
        document.getElementById('fp-helptext-wrap').style.display = !['heading','paragraph','hidden'].includes(f.type) ? '' : 'none';
        document.getElementById('fp-default-wrap').style.display = hasDefault ? '' : 'none';
        document.getElementById('fp-validation-wrap').style.display = hasValidation ? '' : 'none';

        if (hasDefault) {
            document.getElementById('fp-default').value = f.defaultValue || '';
        }

        if (hasOptions) {
            renderOptions(f.options || []);
        }

        if (hasValidation) {
            renderValidation(f);
        }
    }

    function renderOptions(options) {
        const list = document.getElementById('fp-options-list');
        list.innerHTML = '';
        options.forEach((opt, i) => {
            const row = document.createElement('div');
            row.className = 'fb-option-row';
            row.innerHTML = '<input type="text" class="form-input" value="' + esc(opt) + '" data-opt-idx="' + i + '">' +
                '<button class="fb-option-remove" data-opt-idx="' + i + '">&times;</button>';
            list.appendChild(row);
        });

        // Bind option events
        list.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', () => {
                if (selectedIdx < 0) return;
                const i = parseInt(inp.getAttribute('data-opt-idx'));
                fields[selectedIdx].options[i] = inp.value;
                renderCanvas();
            });
        });
        list.querySelectorAll('.fb-option-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                if (selectedIdx < 0) return;
                const i = parseInt(btn.getAttribute('data-opt-idx'));
                fields[selectedIdx].options.splice(i, 1);
                renderOptions(fields[selectedIdx].options);
                renderCanvas();
            });
        });
    }

    document.getElementById('fp-add-option').addEventListener('click', () => {
        if (selectedIdx < 0) return;
        if (!fields[selectedIdx].options) fields[selectedIdx].options = [];
        fields[selectedIdx].options.push('Option ' + (fields[selectedIdx].options.length + 1));
        renderOptions(fields[selectedIdx].options);
        renderCanvas();
    });

    function renderValidation(f) {
        const container = document.getElementById('fp-validation-fields');
        container.innerHTML = '';

        if (f.type === 'number') {
            container.innerHTML = '<div style="display:flex;gap:8px;">' +
                '<div class="form-group" style="flex:1;margin:0;"><label class="form-label">Min</label>' +
                '<input type="number" id="fp-val-min" class="form-input" value="' + (f.validation?.min ?? '') + '"></div>' +
                '<div class="form-group" style="flex:1;margin:0;"><label class="form-label">Max</label>' +
                '<input type="number" id="fp-val-max" class="form-input" value="' + (f.validation?.max ?? '') + '"></div></div>';

            container.querySelector('#fp-val-min')?.addEventListener('input', (e) => {
                if (selectedIdx < 0) return;
                if (!fields[selectedIdx].validation) fields[selectedIdx].validation = {};
                fields[selectedIdx].validation.min = e.target.value ? parseInt(e.target.value) : undefined;
            });
            container.querySelector('#fp-val-max')?.addEventListener('input', (e) => {
                if (selectedIdx < 0) return;
                if (!fields[selectedIdx].validation) fields[selectedIdx].validation = {};
                fields[selectedIdx].validation.max = e.target.value ? parseInt(e.target.value) : undefined;
            });
        } else if (f.type === 'file') {
            container.innerHTML = '<div class="form-group" style="margin:0 0 8px;"><label class="form-label">Accept (MIME)</label>' +
                '<input type="text" id="fp-val-accept" class="form-input" placeholder="image/*,.pdf" value="' + esc(f.validation?.accept || '') + '"></div>' +
                '<div class="form-group" style="margin:0;"><label class="form-label">Max Size (MB)</label>' +
                '<input type="number" id="fp-val-maxsize" class="form-input" value="' + (f.validation?.maxSize ?? 5) + '"></div>';

            container.querySelector('#fp-val-accept')?.addEventListener('input', (e) => {
                if (selectedIdx < 0) return;
                if (!fields[selectedIdx].validation) fields[selectedIdx].validation = {};
                fields[selectedIdx].validation.accept = e.target.value;
            });
            container.querySelector('#fp-val-maxsize')?.addEventListener('input', (e) => {
                if (selectedIdx < 0) return;
                if (!fields[selectedIdx].validation) fields[selectedIdx].validation = {};
                fields[selectedIdx].validation.maxSize = e.target.value ? parseInt(e.target.value) : 5;
            });
        }
    }

    // Bind property inputs
    document.getElementById('fp-label').addEventListener('input', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].label = e.target.value;
        // Auto-generate name from label if name looks auto-generated
        const autoName = slugify(e.target.value);
        const curName = fields[selectedIdx].name;
        if (!curName || curName === slugify(fields[selectedIdx].label || '')) {
            fields[selectedIdx].name = ensureUniqueName(autoName, selectedIdx);
            document.getElementById('fp-name').value = fields[selectedIdx].name;
        }
        renderCanvas();
    });
    document.getElementById('fp-name').addEventListener('input', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].name = slugify(e.target.value);
        e.target.value = fields[selectedIdx].name;
    });
    document.getElementById('fp-placeholder').addEventListener('input', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].placeholder = e.target.value;
        renderCanvas();
    });
    document.getElementById('fp-required').addEventListener('change', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].required = e.target.checked;
        renderCanvas();
    });
    document.getElementById('fp-helptext').addEventListener('input', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].helpText = e.target.value;
    });
    document.getElementById('fp-default')?.addEventListener('input', (e) => {
        if (selectedIdx < 0) return;
        fields[selectedIdx].defaultValue = e.target.value;
    });

    // Width buttons
    document.querySelectorAll('.fb-width-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (selectedIdx < 0) return;
            fields[selectedIdx].width = btn.getAttribute('data-width');
            document.querySelectorAll('.fb-width-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderCanvas();
        });
    });

    /* ═══════════════════════════════════════════════════
     *  SAVE
     * ═══════════════════════════════════════════════════ */
    document.getElementById('fb-save-btn').addEventListener('click', async () => {
        const btn = document.getElementById('fb-save-btn');
        const name = document.getElementById('fb-form-name').value.trim();
        if (!name) { alert('Please enter a form name.'); return; }

        btn.disabled = true;
        btn.textContent = '⏳ Saving...';

        const payload = {
            name: name,
            fields: fields,
            success_message: document.getElementById('fb-success-msg').value,
            redirect_url: document.getElementById('fb-redirect-url').value,
            email_to: document.getElementById('fb-email-to').value,
            active: document.getElementById('fb-active').checked ? 1 : 0,
            csrf_token: CSRF
        };

        const url = FORM_ID
            ? '/admin/form-builder/update/' + FORM_ID
            : '/admin/form-builder/store';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (data.success) {
                btn.textContent = '✅ Saved!';
                setTimeout(() => { btn.textContent = '💾 Save'; btn.disabled = false; }, 1500);
                if (!FORM_ID && data.id) {
                    window.location.href = '/admin/form-builder/edit/' + data.id;
                }
            } else {
                alert(data.message || 'Save failed.');
                btn.textContent = '💾 Save';
                btn.disabled = false;
            }
        } catch (err) {
            alert('Network error: ' + err.message);
            btn.textContent = '💾 Save';
            btn.disabled = false;
        }
    });

    /* ═══════════════════════════════════════════════════
     *  PREVIEW
     * ═══════════════════════════════════════════════════ */
    document.getElementById('fb-preview-btn').addEventListener('click', () => {
        const modal = document.getElementById('fb-preview-modal');
        const content = document.getElementById('fb-preview-content');

        let html = '<form onsubmit="event.preventDefault();alert(\'This is a preview — submissions are disabled.\');"><div style="display:flex;flex-wrap:wrap;gap:12px;">';
        fields.forEach(f => {
            const w = f.width === 'half' ? 'calc(50% - 6px)' : '100%';
            const req = f.required ? ' <span style="color:var(--danger);">*</span>' : '';
            const ra = f.required ? ' required' : '';

            if (f.type === 'heading') {
                html += '<div style="width:100%;"><h3>' + esc(f.label) + '</h3></div>';
                return;
            }
            if (f.type === 'paragraph') {
                html += '<div style="width:100%;color:var(--text-muted);">' + esc(f.label) + '</div>';
                return;
            }
            if (f.type === 'hidden') return;

            html += '<div style="width:' + w + ';margin-bottom:8px;">';
            html += '<label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">' + esc(f.label) + req + '</label>';

            switch(f.type) {
                case 'textarea':
                    html += '<textarea class="form-textarea" rows="3" placeholder="' + esc(f.placeholder||'') + '"' + ra + '></textarea>';
                    break;
                case 'select':
                    html += '<select class="form-select"' + ra + '><option value="">— Select —</option>';
                    (f.options||[]).forEach(o => { html += '<option>' + esc(o) + '</option>'; });
                    html += '</select>';
                    break;
                case 'radio':
                    (f.options||[]).forEach(o => {
                        html += '<label style="display:block;margin:4px 0;font-size:13px;"><input type="radio" name="' + esc(f.name) + '"> ' + esc(o) + '</label>';
                    });
                    break;
                case 'checkbox':
                    html += '<label style="font-size:13px;"><input type="checkbox"> ' + esc(f.label) + '</label>';
                    break;
                case 'checkbox_group':
                    (f.options||[]).forEach(o => {
                        html += '<label style="display:block;margin:4px 0;font-size:13px;"><input type="checkbox"> ' + esc(o) + '</label>';
                    });
                    break;
                default:
                    const inputType = {email:'email',phone:'tel',number:'number',date:'date',time:'time',file:'file'}[f.type] || 'text';
                    html += '<input type="' + inputType + '" class="form-input" placeholder="' + esc(f.placeholder||'') + '"' + ra + '>';
            }

            if (f.helpText) html += '<small style="display:block;color:var(--text-muted);font-size:12px;margin-top:2px;">' + esc(f.helpText) + '</small>';
            html += '</div>';
        });
        html += '</div><div style="margin-top:16px;"><button type="submit" class="btn btn-primary">Submit</button></div></form>';

        content.innerHTML = html;
        modal.style.display = 'flex';
    });

    // Close preview on backdrop click
    document.getElementById('fb-preview-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) e.target.style.display = 'none';
    });

    /* Init */
    renderCanvas();
})();
</script>
