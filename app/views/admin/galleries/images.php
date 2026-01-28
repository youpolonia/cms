<?php
/**
 * Gallery Images Management - Consistent with CMS Design System
 */
$title = 'Gallery: ' . esc($gallery['name']);
ob_start();
?>

<style>
/* Page Header - Matches CMS standard */
.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}
.gallery-header-content h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}
.gallery-header-content .subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
}
.gallery-header-actions {
    display: flex;
    gap: 0.75rem;
}
.breadcrumb {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.75rem;
}
.breadcrumb a {
    color: var(--accent);
    text-decoration: none;
}
.breadcrumb a:hover {
    text-decoration: underline;
}

/* Stats Bar */
.stats-bar {
    display: flex;
    gap: 2rem;
    padding: 1rem 1.5rem;
    background: var(--bg-tertiary);
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
}
.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: var(--accent-muted);
}
.stat-info {
    display: flex;
    flex-direction: column;
}
.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
}
.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Tabs Navigation */
.gallery-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--border);
}
.gallery-tab {
    padding: 12px 24px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.gallery-tab:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}
.gallery-tab.active {
    background: var(--accent);
    color: white;
}

/* Card - CMS Standard */
.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-tertiary);
}
.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.card-body {
    padding: 1.5rem;
}

/* Upload Zone - Enhanced */
.upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
    position: relative;
    overflow: hidden;
}
.upload-zone::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--accent) 0%, var(--primary-dark) 100%);
    opacity: 0;
    transition: opacity 0.3s;
}
.upload-zone:hover,
.upload-zone.dragover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.15);
}
.upload-zone:hover::before,
.upload-zone.dragover::before {
    opacity: 0.05;
}
.upload-zone-content {
    position: relative;
    z-index: 1;
}
.upload-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: var(--accent-muted);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    transition: transform 0.3s;
}
.upload-zone:hover .upload-icon {
    transform: scale(1.1);
}
.upload-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}
.upload-hint {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0 0 1rem 0;
}
.upload-formats {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.format-badge {
    padding: 4px 10px;
    background: var(--bg-tertiary);
    border-radius: 20px;
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* Preview Grid */
.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.preview-grid:empty {
    display: none;
}
.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--bg-tertiary);
}
.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.preview-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--danger);
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.preview-item:hover .preview-remove {
    opacity: 1;
}

/* Upload Actions */
.upload-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.upload-actions:empty {
    display: none;
}
.file-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    font-size: 0.9rem;
}
.file-count strong {
    color: var(--text-primary);
}

/* Images Grid - Enhanced */
.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
    padding: 1.5rem;
}
.image-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}
.image-card:hover {
    border-color: var(--accent);
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}
.image-card.selected {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px var(--accent-muted);
}
.image-preview {
    position: relative;
    aspect-ratio: 4/3;
    background: var(--bg-tertiary);
    overflow: hidden;
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.image-card:hover .image-preview img {
    transform: scale(1.05);
}
.image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.3s;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 1rem;
    gap: 0.5rem;
}
.image-card:hover .image-overlay {
    opacity: 1;
}
.overlay-btn {
    width: 40px;
    height: 40px;
    border-radius: var(--radius);
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    font-size: 1.1rem;
}
.overlay-btn:hover {
    background: var(--accent);
    border-color: var(--accent);
    transform: scale(1.1);
}
.image-checkbox {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    opacity: 0;
    transition: all 0.2s;
}
.image-card:hover .image-checkbox,
.image-card.selected .image-checkbox {
    opacity: 1;
}
.image-card.selected .image-checkbox {
    background: var(--accent);
    border-color: var(--accent);
}
.image-order {
    position: absolute;
    top: 12px;
    right: 12px;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}
.image-info {
    padding: 1rem;
}
.image-title-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-primary);
    font-size: 0.875rem;
    transition: all 0.2s;
}
.image-title-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-muted);
}
.image-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border);
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    background: var(--bg-tertiary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
}
.empty-state h3 {
    font-size: 1.25rem;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}
.empty-state p {
    color: var(--text-muted);
    margin: 0 0 1.5rem 0;
}

/* Bulk Actions Bar */
.bulk-actions {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease;
    z-index: 100;
}
.bulk-actions.visible {
    transform: translateX(-50%) translateY(0);
}
.bulk-count {
    font-weight: 600;
    color: var(--accent);
}

/* Buttons - CMS Standard */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: var(--radius);
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
}
.btn-primary {
    background: var(--accent);
    color: white;
}
.btn-primary:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
}
.btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border: 1px solid var(--border);
}
.btn-secondary:hover {
    background: var(--bg-primary);
    border-color: var(--accent);
}
.btn-danger {
    background: var(--danger-bg);
    color: var(--danger);
    border: 1px solid var(--danger);
}
.btn-danger:hover {
    background: var(--danger);
    color: white;
}
.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
}
.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    justify-content: center;
}

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.alert-success {
    background: var(--success-bg);
    border: 1px solid var(--success);
    color: var(--success);
}
.alert-error {
    background: var(--danger-bg);
    border: 1px solid var(--danger);
    color: var(--danger);
}

/* Responsive */
@media (max-width: 768px) {
    .gallery-header {
        flex-direction: column;
        gap: 1rem;
    }
    .stats-bar {
        flex-wrap: wrap;
        gap: 1rem;
    }
    .images-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}
</style>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="/admin">Dashboard</a> /
    <a href="/admin/galleries">Galleries</a> /
    <?= esc($gallery['name']) ?>
</div>

<!-- Page Header -->
<div class="gallery-header">
    <div class="gallery-header-content">
        <h1>📷 <?= esc($gallery['name']) ?></h1>
        <p class="subtitle">Manage and organize images in this gallery</p>
    </div>
    <div class="gallery-header-actions">
        <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/edit" class="btn btn-secondary">
            ✏️ Edit Gallery
        </a>
        <a href="/admin/galleries" class="btn btn-secondary">
            ← Back
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">✓ <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">⚠ <?= esc($error) ?></div>
<?php endif; ?>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="stat-icon">🖼️</div>
        <div class="stat-info">
            <span class="stat-value"><?= count($images) ?></span>
            <span class="stat-label">Total Images</span>
        </div>
    </div>
    <div class="stat-item">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
            <span class="stat-value"><?= $gallery['is_public'] ? 'Public' : 'Private' ?></span>
            <span class="stat-label">Visibility</span>
        </div>
    </div>
    <div class="stat-item">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
            <span class="stat-value"><?= date('M j, Y', strtotime($gallery['created_at'] ?? 'now')) ?></span>
            <span class="stat-label">Created</span>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="gallery-tabs">
    <button class="gallery-tab active" onclick="showTab('upload')">📤 Upload</button>
    <button class="gallery-tab" onclick="showTab('library')">🖼️ Images (<?= count($images) ?>)</button>
    <button class="gallery-tab" onclick="showTab('settings')">⚙️ Settings</button>
</div>

<!-- Upload Panel -->
<div id="tabUpload" class="tab-panel">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📤 Upload Images</h2>
            <span style="color: var(--text-muted); font-size: 0.85rem;">Max 10MB per file</span>
        </div>
        <div class="card-body">
            <form id="uploadForm" action="/admin/galleries/<?= (int)$gallery['id'] ?>/upload" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="upload-zone" id="dropzone">
                    <div class="upload-zone-content">
                        <div class="upload-icon">📷</div>
                        <h3 class="upload-title">Drag & drop images here</h3>
                        <p class="upload-hint">or click to browse your computer</p>
                        <div class="upload-formats">
                            <span class="format-badge">JPG</span>
                            <span class="format-badge">PNG</span>
                            <span class="format-badge">GIF</span>
                            <span class="format-badge">WebP</span>
                            <span class="format-badge">SVG</span>
                        </div>
                    </div>
                    <input type="file" id="fileInput" name="images[]" multiple accept="image/*" hidden>
                </div>

                <div class="preview-grid" id="previewGrid"></div>

                <div class="upload-actions" id="uploadActions" style="display: none;">
                    <div class="file-count">
                        <span id="selectedCount">0</span> files selected
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button" class="btn btn-secondary" id="clearBtn">Clear All</button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">📤 Upload Images</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Library Panel -->
<div id="tabLibrary" class="tab-panel" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🖼️ Gallery Images</h2>
            <?php if (!empty($images)): ?>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" id="selectAllBtn">☑️ Select All</button>
                <button type="button" class="btn btn-secondary btn-sm" id="reorderBtn">↕️ Reorder</button>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($images)): ?>
            <div class="empty-state">
                <div class="empty-icon">📷</div>
                <h3>No images yet</h3>
                <p>Upload some images to get started with your gallery.</p>
                <button class="btn btn-primary" onclick="showTab('upload')">📤 Upload Images</button>
            </div>
        <?php else: ?>
            <div class="images-grid" id="imagesGrid">
                <?php foreach ($images as $index => $image): ?>
                <div class="image-card" data-id="<?= (int)$image['id'] ?>">
                    <div class="image-preview">
                        <img src="/uploads/media/<?= esc($image['filename']) ?>"
                             alt="<?= esc($image['title'] ?? '') ?>"
                             loading="lazy">
                        <div class="image-checkbox" onclick="toggleSelect(this)">✓</div>
                        <span class="image-order">#<?= $index + 1 ?></span>
                        <div class="image-overlay">
                            <a href="/uploads/media/<?= esc($image['filename']) ?>"
                               target="_blank" class="overlay-btn" title="View Full Size">🔍</a>
                            <button type="button" class="overlay-btn" title="Edit"
                                    onclick="editImage(<?= (int)$image['id'] ?>)">✏️</button>
                            <form method="post" style="margin:0"
                                  action="/admin/galleries/<?= (int)$gallery['id'] ?>/images/<?= (int)$image['id'] ?>/delete"
                                  onsubmit="return confirm('Remove this image?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="overlay-btn" title="Delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                    <div class="image-info">
                        <input type="text" class="image-title-input"
                               value="<?= esc($image['title'] ?? $image['original_name'] ?? '') ?>"
                               data-id="<?= (int)$image['id'] ?>"
                               placeholder="Image title...">
                        <div class="image-meta">
                            <span><?= esc($image['mime_type'] ?? 'image') ?></span>
                            <span><?= isset($image['file_size']) ? round($image['file_size'] / 1024) . ' KB' : '' ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Settings Panel -->
<div id="tabSettings" class="tab-panel" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">⚙️ Gallery Settings</h2>
        </div>
        <div class="card-body">
            <p style="color: var(--text-muted);">
                Configure gallery display options and behavior.
                <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/edit" style="color: var(--accent);">
                    Edit full gallery settings →
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions" id="bulkActions">
    <span><span class="bulk-count" id="bulkCount">0</span> selected</span>
    <button class="btn btn-secondary btn-sm" onclick="deselectAll()">Deselect All</button>
    <button class="btn btn-danger btn-sm" onclick="bulkDelete()">🗑️ Delete Selected</button>
</div>

<script>
// Tab switching
function showTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.gallery-tab').forEach(t => t.classList.remove('active'));

    document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).style.display = 'block';
    event.target.classList.add('active');
}

// Dropzone
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const previewGrid = document.getElementById('previewGrid');
const uploadActions = document.getElementById('uploadActions');
const selectedCount = document.getElementById('selectedCount');

let selectedFiles = [];

dropzone?.addEventListener('click', () => fileInput.click());
dropzone?.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
dropzone?.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});
fileInput?.addEventListener('change', (e) => handleFiles(e.target.files));

function handleFiles(files) {
    const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
    selectedFiles = [...selectedFiles, ...imageFiles];
    updatePreview();
}

function updatePreview() {
    previewGrid.innerHTML = '';

    if (selectedFiles.length === 0) {
        uploadActions.style.display = 'none';
        return;
    }

    uploadActions.style.display = 'flex';
    selectedCount.textContent = selectedFiles.length;

    selectedFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `
            <img src="${URL.createObjectURL(file)}" alt="">
            <button type="button" class="preview-remove" onclick="removeFile(${index})">×</button>
        `;
        previewGrid.appendChild(div);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
}

document.getElementById('clearBtn')?.addEventListener('click', () => {
    selectedFiles = [];
    fileInput.value = '';
    updatePreview();
});

// Image selection
let selectedImages = new Set();

function toggleSelect(checkbox) {
    const card = checkbox.closest('.image-card');
    const id = card.dataset.id;

    if (selectedImages.has(id)) {
        selectedImages.delete(id);
        card.classList.remove('selected');
    } else {
        selectedImages.add(id);
        card.classList.add('selected');
    }

    updateBulkActions();
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulkActions');
    document.getElementById('bulkCount').textContent = selectedImages.size;
    bulkActions.classList.toggle('visible', selectedImages.size > 0);
}

function deselectAll() {
    selectedImages.clear();
    document.querySelectorAll('.image-card').forEach(c => c.classList.remove('selected'));
    updateBulkActions();
}

document.getElementById('selectAllBtn')?.addEventListener('click', function() {
    const cards = document.querySelectorAll('.image-card');
    if (selectedImages.size === cards.length) {
        deselectAll();
        this.textContent = '☑️ Select All';
    } else {
        cards.forEach(card => {
            selectedImages.add(card.dataset.id);
            card.classList.add('selected');
        });
        updateBulkActions();
        this.textContent = '☐ Deselect All';
    }
});

function bulkDelete() {
    if (!confirm(`Delete ${selectedImages.size} selected images?`)) return;

    // TODO: Implement bulk delete API
    alert('Bulk delete coming soon!');
}

// Auto-save titles
document.querySelectorAll('.image-title-input').forEach(input => {
    input.addEventListener('blur', function() {
        const id = this.dataset.id;
        const title = this.value;

        fetch('/admin/galleries/<?= (int)$gallery['id'] ?>/images/' + id + '/title', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('[name="csrf_token"]')?.value || ''
            },
            body: JSON.stringify({ title })
        }).catch(console.error);
    });
});

// Form submit handler
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert('Please select at least one image');
        return;
    }

    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
