<?php
/**
 * Gallery Images Management - Modern Dark Theme with Drag & Drop
 */
$title = 'Gallery: ' . esc($gallery['name']);
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/galleries" class="back-link">← Back to Galleries</a>
        <h1>📷 <?= esc($gallery['name']) ?></h1>
        <p class="page-subtitle">Manage images in this gallery</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/edit" class="btn btn-secondary">✏️ Edit Gallery</a>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<!-- Upload Area -->
<div class="card upload-card">
    <div class="card-header">
        <h2 class="card-title">📤 Upload Images</h2>
    </div>
    <div class="card-body">
        <form id="uploadForm" action="/admin/galleries/<?= (int)$gallery['id'] ?>/upload" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="dropzone" id="dropzone">
                <div class="dropzone-content">
                    <div class="dropzone-icon">📷</div>
                    <p class="dropzone-text">Drag & drop images here</p>
                    <p class="dropzone-hint">or click to browse files</p>
                    <input type="file" id="fileInput" name="images[]" multiple accept="image/*" hidden>
                </div>
                <div class="dropzone-preview" id="preview"></div>
            </div>
            <div class="upload-actions" id="uploadActions" style="display: none;">
                <span class="selected-count" id="selectedCount">0 files selected</span>
                <button type="button" class="btn btn-secondary" id="clearBtn">Clear</button>
                <button type="submit" class="btn btn-primary" id="uploadBtn">📤 Upload Images</button>
            </div>
        </form>
    </div>
</div>

<!-- Images Grid -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🖼️ Gallery Images (<?= count($images) ?>)</h2>
        <?php if (!empty($images)): ?>
        <div class="header-actions">
            <button type="button" class="btn btn-secondary btn-sm" id="toggleReorder">
                ↕️ Reorder Mode
            </button>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (empty($images)): ?>
        <div class="empty-state">
            <div class="empty-icon">📷</div>
            <h3>No images yet</h3>
            <p>Upload images using the form above.</p>
        </div>
    <?php else: ?>
        <div class="images-grid" id="imagesGrid">
            <?php foreach ($images as $index => $image): ?>
            <div class="image-card" data-id="<?= (int)$image['id'] ?>">
                <div class="image-preview">
                    <img src="/public/uploads/media/<?= esc($image['filename']) ?>" 
                         alt="<?= esc($image['title'] ?? $image['original_name'] ?? '') ?>"
                         loading="lazy">
                    <div class="image-overlay">
                        <a href="/public/uploads/media/<?= esc($image['filename']) ?>" 
                           target="_blank" class="overlay-btn" title="View Full">
                            🔍
                        </a>
                    </div>
                    <div class="drag-handle" title="Drag to reorder">⋮⋮</div>
                    <span class="image-order"><?= $index + 1 ?></span>
                </div>
                <div class="image-info">
                    <input type="text" class="image-title" 
                           value="<?= esc($image['title'] ?? $image['original_name'] ?? $image['filename']) ?>"
                           data-id="<?= (int)$image['id'] ?>"
                           placeholder="Image title...">
                </div>
                <div class="image-actions">
                    <form method="post" 
                          action="/admin/galleries/<?= (int)$gallery['id'] ?>/images/<?= (int)$image['id'] ?>/delete" 
                          onsubmit="return confirm('Remove this image from gallery?');"
                          class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="action-btn delete" title="Remove">🗑️ Remove</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}
.page-header-content {
    flex: 1;
}
.back-link {
    font-size: 0.875rem;
    color: var(--text-muted);
    text-decoration: none;
    display: inline-block;
    margin-bottom: 0.5rem;
}
.back-link:hover {
    color: var(--accent-color);
}
.page-header h1 {
    font-size: 1.75rem;
    margin: 0 0 0.25rem 0;
}
.page-subtitle {
    color: var(--text-muted);
    margin: 0;
}

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}
.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Card */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}
.card-body {
    padding: 1.5rem;
}

/* Dropzone */
.dropzone {
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg-primary);
}
.dropzone:hover,
.dropzone.dragover {
    border-color: var(--accent-color);
    background: rgba(59, 130, 246, 0.05);
}
.dropzone-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.dropzone-text {
    font-size: 1.125rem;
    font-weight: 500;
    margin: 0;
    color: var(--text-primary);
}
.dropzone-hint {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0.5rem 0 0 0;
}

/* Preview */
.dropzone-preview {
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}
.dropzone-preview.has-files {
    display: grid;
}
.preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-secondary);
}
.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.preview-item .remove-preview {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Upload Actions */
.upload-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}
.selected-count {
    color: var(--text-muted);
    font-size: 0.875rem;
    flex: 1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
.empty-state h3 {
    margin: 0 0 0.5rem 0;
}
.empty-state p {
    color: var(--text-muted);
    margin: 0;
}

/* Images Grid */
.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1.5rem;
}

.image-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.2s;
}
.image-card:hover {
    border-color: var(--accent-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.image-card.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.image-preview {
    position: relative;
    aspect-ratio: 1;
    background: #1e1e2e;
}
.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.image-card:hover .image-overlay {
    opacity: 1;
}
.overlay-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    text-decoration: none;
    transition: all 0.2s;
}
.overlay-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.drag-handle {
    position: absolute;
    top: 8px;
    left: 8px;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    font-size: 14px;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: grab;
}
.reorder-mode .drag-handle {
    display: flex;
}

.image-order {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-info {
    padding: 0.75rem;
}
.image-title {
    width: 100%;
    padding: 0.5rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 0.8125rem;
}
.image-title:focus {
    outline: none;
    border-color: var(--accent-color);
}

.image-actions {
    padding: 0 0.75rem 0.75rem;
}
.inline-form {
    display: block;
}
.action-btn {
    width: 100%;
    padding: 0.5rem;
    font-size: 0.8125rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
}
.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #ef4444;
}

/* Buttons */
.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
}
.btn-primary {
    background: var(--accent-color);
    color: white;
}
.btn-primary:hover {
    background: #2563eb;
}
.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-primary);
}
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

/* Reorder Mode */
.reorder-mode .image-card {
    cursor: grab;
}
.reorder-mode .image-overlay {
    display: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    .images-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
}
</style>

<script>
// Dropzone functionality
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('preview');
const uploadActions = document.getElementById('uploadActions');
const selectedCount = document.getElementById('selectedCount');
const clearBtn = document.getElementById('clearBtn');
const uploadForm = document.getElementById('uploadForm');

let selectedFiles = [];

dropzone?.addEventListener('click', () => fileInput.click());

dropzone?.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone?.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone?.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

fileInput?.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
    selectedFiles = [...selectedFiles, ...imageFiles];
    updatePreview();
}

function updatePreview() {
    preview.innerHTML = '';
    
    if (selectedFiles.length === 0) {
        preview.classList.remove('has-files');
        uploadActions.style.display = 'none';
        return;
    }
    
    preview.classList.add('has-files');
    uploadActions.style.display = 'flex';
    selectedCount.textContent = `${selectedFiles.length} file${selectedFiles.length !== 1 ? 's' : ''} selected`;
    
    selectedFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'preview-item';
        
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-preview';
        removeBtn.innerHTML = '×';
        removeBtn.type = 'button';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            selectedFiles.splice(index, 1);
            updatePreview();
        };
        
        div.appendChild(img);
        div.appendChild(removeBtn);
        preview.appendChild(div);
    });
}

clearBtn?.addEventListener('click', () => {
    selectedFiles = [];
    fileInput.value = '';
    updatePreview();
});

uploadForm?.addEventListener('submit', (e) => {
    if (selectedFiles.length === 0) {
        e.preventDefault();
        alert('Please select at least one image');
        return;
    }
    
    // Create FormData with files
    const formData = new FormData(uploadForm);
    selectedFiles.forEach(file => {
        formData.append('images[]', file);
    });
    
    // Replace form data
    const newForm = document.createElement('form');
    newForm.method = 'POST';
    newForm.action = uploadForm.action;
    newForm.enctype = 'multipart/form-data';
    
    // Add CSRF
    const csrfInput = uploadForm.querySelector('input[name="csrf_token"]');
    if (csrfInput) {
        newForm.appendChild(csrfInput.cloneNode());
    }
    
    // Add files
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    
    const newFileInput = document.createElement('input');
    newFileInput.type = 'file';
    newFileInput.name = 'images[]';
    newFileInput.multiple = true;
    newFileInput.files = dt.files;
    newForm.appendChild(newFileInput);
    
    document.body.appendChild(newForm);
    newForm.submit();
    
    e.preventDefault();
});

// Reorder mode
const toggleReorder = document.getElementById('toggleReorder');
const imagesGrid = document.getElementById('imagesGrid');
let reorderMode = false;

toggleReorder?.addEventListener('click', () => {
    reorderMode = !reorderMode;
    imagesGrid?.classList.toggle('reorder-mode', reorderMode);
    toggleReorder.textContent = reorderMode ? '✓ Save Order' : '↕️ Reorder Mode';
    
    if (!reorderMode) {
        saveOrder();
    }
});

// Simple drag reorder
let draggedItem = null;

imagesGrid?.addEventListener('dragstart', (e) => {
    if (!reorderMode) return;
    draggedItem = e.target.closest('.image-card');
    draggedItem?.classList.add('dragging');
});

imagesGrid?.addEventListener('dragend', () => {
    draggedItem?.classList.remove('dragging');
    draggedItem = null;
    updateOrderNumbers();
});

imagesGrid?.addEventListener('dragover', (e) => {
    if (!reorderMode || !draggedItem) return;
    e.preventDefault();
    
    const afterElement = getDragAfterElement(imagesGrid, e.clientY);
    if (afterElement) {
        imagesGrid.insertBefore(draggedItem, afterElement);
    } else {
        imagesGrid.appendChild(draggedItem);
    }
});

function getDragAfterElement(container, y) {
    const cards = [...container.querySelectorAll('.image-card:not(.dragging)')];
    
    return cards.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function updateOrderNumbers() {
    document.querySelectorAll('.image-card').forEach((card, index) => {
        const orderSpan = card.querySelector('.image-order');
        if (orderSpan) orderSpan.textContent = index + 1;
    });
}

function saveOrder() {
    const order = [...document.querySelectorAll('.image-card')].map(card => card.dataset.id);
    
    fetch('/admin/galleries/<?= (int)$gallery['id'] ?>/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('[name="csrf_token"]')?.value || ''
        },
        body: JSON.stringify({ order })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            console.log('Order saved');
        }
    }).catch(console.error);
}

// Make cards draggable
document.querySelectorAll('.image-card').forEach(card => {
    card.draggable = true;
});

// Auto-save title on blur
document.querySelectorAll('.image-title').forEach(input => {
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
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
