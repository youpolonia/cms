<?php
/**
 * Theme Builder 3.0 - Global Templates List
 * Header, Footer, Archive, Single, Sidebar, 404 templates
 */
$title = 'Theme Templates';
ob_start();

$totalTemplates = 0;
$activeTemplates = 0;
foreach ($grouped ?? [] as $type => $group) {
    $totalTemplates += count($group['items']);
    $activeTemplates += count(array_filter($group['items'], fn($t) => ($t['is_active'] ?? 0)));
}
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: -4px;">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/>
            </svg>
            Theme Templates
        </h1>
        <p class="page-description">Global templates for headers, footers, archives, and more</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" onclick="openImportModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Import Layout
        </button>
        <a href="/admin/theme-builder" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/>
            </svg>
            Back to Pages
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">🎨</div>
        <div class="stat-value"><?= $totalTemplates ?></div>
        <div class="stat-label">Total Templates</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">✓</div>
        <div class="stat-value"><?= $activeTemplates ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">○</div>
        <div class="stat-value"><?= $totalTemplates - $activeTemplates ?></div>
        <div class="stat-label">Inactive</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">📋</div>
        <div class="stat-value"><?= count($templateTypes ?? []) ?></div>
        <div class="stat-label">Template Types</div>
    </div>
</div>

<!-- Templates by Type -->
<?php foreach ($grouped as $type => $group): ?>
<div class="card mb-4">
    <div class="card-header template-type-header">
        <div class="template-type-info">
            <span class="template-type-icon"><?= $group['info']['icon'] ?></span>
            <div>
                <h3 class="card-title"><?= esc($group['info']['label']) ?> Templates</h3>
                <p class="template-type-desc"><?= esc($group['info']['description']) ?></p>
            </div>
        </div>
        <a href="/admin/theme-builder/templates/create/<?= esc($type) ?>" class="btn btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New <?= esc($group['info']['label']) ?>
        </a>
    </div>

    <?php if (empty($group['items'])): ?>
    <div class="card-body">
        <div class="empty-state-sm">
            <p class="text-muted">No <?= strtolower($group['info']['label']) ?> templates yet.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Last Updated</th>
                    <th style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($group['items'] as $template): ?>
                <tr>
                    <td>
                        <a href="/admin/theme-builder/templates/<?= (int)$template['id'] ?>/edit" class="table-title">
                            <?= esc($template['name']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-<?= $template['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $template['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted"><?= (int)$template['priority'] ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('M j, Y g:i A', strtotime($template['updated_at'])) ?></span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="/admin/theme-builder/templates/<?= (int)$template['id'] ?>/edit" class="btn btn-ghost btn-icon btn-sm" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <form method="POST" action="/admin/theme-builder/templates/<?= (int)$template['id'] ?>/toggle" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="<?= $template['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <?php if ($template['is_active']): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                    <?php else: ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <form method="POST" action="/admin/theme-builder/templates/<?= (int)$template['id'] ?>/duplicate" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Duplicate">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="/admin/theme-builder/templates/<?= (int)$template['id'] ?>/delete" style="display: inline;"
                                  onsubmit="return confirm('Delete this template?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm btn-danger" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<style>
.template-type-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.template-type-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.template-type-icon {
    font-size: 28px;
    line-height: 1;
}
.template-type-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
}
.card-title {
    margin: 0 0 2px 0;
}
.empty-state-sm {
    text-align: center;
    padding: 30px 20px;
}
.table-title {
    font-weight: 500;
    color: var(--text-primary);
}
.table-title:hover {
    color: var(--accent);
}
.table-actions {
    display: flex;
    gap: 4px;
    opacity: 0.6;
    transition: opacity 0.15s;
}
tr:hover .table-actions {
    opacity: 1;
}
.btn-icon {
    padding: 6px;
}
.btn-danger:hover {
    color: var(--danger) !important;
}
.mb-4 {
    margin-bottom: 24px;
}
.stat-icon.info { background: var(--accent-muted); }
.badge-secondary {
    background: var(--bg-tertiary);
    color: var(--text-muted);
}

/* Import Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.modal-overlay.active {
    display: flex;
}
.modal-box {
    background: var(--bg-secondary);
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    animation: modalSlideIn 0.2s ease-out;
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}
.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    transition: all 0.15s;
}
.modal-close:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}
.modal-body {
    padding: 24px;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
.dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.dropzone:hover, .dropzone.dragover {
    border-color: var(--accent);
    background: rgba(var(--accent-rgb), 0.05);
}
.dropzone-icon {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}
.dropzone-text {
    color: var(--text-muted);
    margin-bottom: 8px;
}
.dropzone-hint {
    font-size: 12px;
    color: var(--text-muted);
    opacity: 0.7;
}
.dropzone-file {
    display: none;
}
.dropzone.has-file .dropzone-icon { display: none; }
.dropzone.has-file .dropzone-text { font-weight: 500; color: var(--success); }
.import-result {
    padding: 16px;
    border-radius: 8px;
    margin-top: 16px;
    display: none;
}
.import-result.success {
    background: rgba(var(--success-rgb), 0.1);
    border: 1px solid var(--success);
    color: var(--success);
    display: block;
}
.import-result.error {
    background: rgba(var(--danger-rgb), 0.1);
    border: 1px solid var(--danger);
    color: var(--danger);
    display: block;
}
.format-info {
    margin-top: 20px;
    padding: 16px;
    background: var(--bg-tertiary);
    border-radius: 8px;
    font-size: 13px;
}
.format-info h4 {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: var(--text-muted);
}
.format-info code {
    display: block;
    white-space: pre;
    font-size: 11px;
    line-height: 1.5;
    color: var(--text-secondary);
}
</style>

<!-- Import Layout Modal -->
<div id="importModal" class="modal-overlay" onclick="if(event.target === this) closeImportModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -4px; margin-right: 8px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import Layout
            </h3>
            <button class="modal-close" onclick="closeImportModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="importForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

                <div id="dropzone" class="dropzone" onclick="document.getElementById('templateFile').click()">
                    <div class="dropzone-icon">📁</div>
                    <div class="dropzone-text">Drop JSON file here or click anywhere to upload</div>
                    <div class="dropzone-hint">Supports header, footer, archive, single, sidebar, 404 templates</div>
                    <input type="file" id="templateFile" name="template_file" accept=".json" class="dropzone-file">
                </div>

                <div id="importResult" class="import-result"></div>

                <div class="format-info">
                    <h4>Expected JSON Format:</h4>
                    <code>{
  "name": "Template Name",
  "type": "header",
  "content": {
    "sections": [...]
  }
}</code>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="importBtn" onclick="uploadTemplate()" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import
            </button>
        </div>
    </div>
</div>

<script>
// Modal functions
function openImportModal() {
    document.getElementById('importModal').classList.add('active');
    resetImportForm();
}

function closeImportModal() {
    document.getElementById('importModal').classList.remove('active');
    resetImportForm();
}

function resetImportForm() {
    const form = document.getElementById('importForm');
    form.reset();
    document.getElementById('dropzone').classList.remove('has-file');
    document.getElementById('dropzone').querySelector('.dropzone-text').textContent = 'Drop JSON file here or click anywhere to upload';
    document.getElementById('importResult').className = 'import-result';
    document.getElementById('importResult').textContent = '';
    document.getElementById('importBtn').disabled = true;
}

// Drag and drop handling
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('templateFile');

['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, e => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });
});

['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    });
});

dropzone.addEventListener('drop', e => {
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].name.endsWith('.json')) {
        fileInput.files = files;
        handleFileSelect(files[0]);
    }
});

fileInput.addEventListener('change', e => {
    if (e.target.files.length > 0) {
        handleFileSelect(e.target.files[0]);
    }
});

function handleFileSelect(file) {
    dropzone.classList.add('has-file');
    dropzone.querySelector('.dropzone-text').textContent = '✓ ' + file.name;
    document.getElementById('importBtn').disabled = false;
    document.getElementById('importResult').className = 'import-result';
    document.getElementById('importResult').textContent = '';
}

// Upload function
async function uploadTemplate() {
    const form = document.getElementById('importForm');
    const formData = new FormData(form);
    const importBtn = document.getElementById('importBtn');
    const resultDiv = document.getElementById('importResult');

    importBtn.disabled = true;
    importBtn.innerHTML = '<span class="spinner"></span> Importing...';

    try {
        const response = await fetch('/admin/theme-builder/templates/upload', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            resultDiv.className = 'import-result success';
            resultDiv.textContent = result.message || 'Template imported successfully!';

            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            resultDiv.className = 'import-result error';
            resultDiv.textContent = result.error || 'Failed to import template';
            importBtn.disabled = false;
            importBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Import';
        }
    } catch (error) {
        resultDiv.className = 'import-result error';
        resultDiv.textContent = 'Network error: ' + error.message;
        importBtn.disabled = false;
        importBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Import';
    }
}

// Close modal on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('importModal').classList.contains('active')) {
        closeImportModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
