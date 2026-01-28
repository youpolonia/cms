<?php
/**
 * Gallery Form - Consistent with CMS Design System
 */
$title = $gallery ? 'Edit Gallery' : 'New Gallery';
$isEdit = $gallery !== null;
ob_start();
?>

<style>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}
.page-header-content h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}
.page-header-content .subtitle {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
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

/* Form Layout */
.form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

/* Card */
.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
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

/* Form Groups */
.form-group {
    margin-bottom: 1.5rem;
}
.form-group:last-child {
    margin-bottom: 0;
}
.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}
.form-group label .required {
    color: var(--danger);
}
.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
}

/* Form Controls */
.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-primary);
    font-size: 0.9rem;
    transition: all 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-muted);
}
.form-control::placeholder {
    color: var(--text-muted);
}
textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Input with Prefix */
.input-with-prefix {
    display: flex;
    align-items: stretch;
}
.input-prefix {
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-right: none;
    border-radius: var(--radius) 0 0 var(--radius);
    color: var(--text-muted);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}
.input-with-prefix .form-control {
    border-radius: 0 var(--radius) var(--radius) 0;
}

/* Toggle Options (Visibility) */
.toggle-options {
    display: flex;
    gap: 0.75rem;
}
.toggle-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.25rem 1rem;
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s;
}
.toggle-option:hover {
    border-color: var(--accent);
    background: var(--accent-muted);
}
.toggle-option.active {
    border-color: var(--accent);
    background: var(--accent-muted);
}
.toggle-option input {
    display: none;
}
.toggle-icon {
    font-size: 1.75rem;
}
.toggle-text {
    font-weight: 500;
    font-size: 0.9rem;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Info Card */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.info-item {
    padding: 1rem;
    background: var(--bg-tertiary);
    border-radius: var(--radius);
}
.info-label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}
.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}

/* Quick Actions */
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.action-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.2s;
}
.action-link:hover {
    border-color: var(--accent);
    background: var(--accent-muted);
    transform: translateX(4px);
}
.action-link .icon {
    font-size: 1.5rem;
}
.action-link .text {
    flex: 1;
}
.action-link .text strong {
    display: block;
    margin-bottom: 0.125rem;
}
.action-link .text span {
    font-size: 0.8rem;
    color: var(--text-muted);
}
.action-link .arrow {
    color: var(--text-muted);
}

/* Cover Image Selector */
.cover-selector {
    border: 2px dashed var(--border);
    border-radius: var(--radius-lg);
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg-tertiary);
}
.cover-selector:hover {
    border-color: var(--accent);
    background: var(--accent-muted);
}
.cover-selector .icon {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
}
.cover-selector p {
    margin: 0;
    color: var(--text-muted);
}
.cover-preview {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    aspect-ratio: 16/9;
}
.cover-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cover-preview .remove-cover {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--danger);
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.form-actions-left {
    display: flex;
    gap: 0.75rem;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
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
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
}
.btn-danger:hover {
    background: var(--danger);
    color: white;
}
.btn-lg {
    padding: 1rem 2rem;
    font-size: 1rem;
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

/* SEO Preview */
.seo-preview {
    background: white;
    border-radius: var(--radius);
    padding: 1rem;
    margin-top: 1rem;
}
.seo-preview-title {
    color: #1a0dab;
    font-size: 1.1rem;
    margin: 0 0 0.25rem 0;
    text-decoration: underline;
}
.seo-preview-url {
    color: #006621;
    font-size: 0.85rem;
    margin: 0 0 0.25rem 0;
}
.seo-preview-desc {
    color: #545454;
    font-size: 0.85rem;
    margin: 0;
    line-height: 1.4;
}
</style>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="/admin">Dashboard</a> /
    <a href="/admin/galleries">Galleries</a> /
    <?= $isEdit ? esc($gallery['name']) : 'New Gallery' ?>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <h1><?= $isEdit ? '✏️ Edit Gallery' : '➕ Create New Gallery' ?></h1>
        <p class="subtitle"><?= $isEdit ? 'Update gallery settings and metadata' : 'Set up a new photo gallery' ?></p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">✓ <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">⚠ <?= esc($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? '/admin/galleries/' . (int)$gallery['id'] : '/admin/galleries' ?>">
    <?= csrf_field() ?>

    <div class="form-grid">
        <!-- Main Column -->
        <div class="main-column">
            <!-- Basic Info -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">📝 Basic Information</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Gallery Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required
                               value="<?= esc($gallery['name'] ?? '') ?>"
                               placeholder="e.g., Summer Vacation 2025, Product Photos"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="slug">URL Slug</label>
                        <div class="input-with-prefix">
                            <span class="input-prefix">/gallery/</span>
                            <input type="text" id="slug" name="slug"
                                   value="<?= esc($gallery['slug'] ?? '') ?>"
                                   placeholder="auto-generated"
                                   class="form-control">
                        </div>
                        <p class="form-hint">Leave empty to auto-generate from name. Only lowercase letters, numbers, and hyphens.</p>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Brief description of this gallery for SEO and visitors..."
                                  class="form-control"><?= esc($gallery['description'] ?? '') ?></textarea>
                        <p class="form-hint">Shown on the gallery page and used for SEO meta description.</p>
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">⚙️ Display Settings</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order"
                                   value="<?= (int)($gallery['sort_order'] ?? 0) ?>"
                                   min="0" step="1"
                                   class="form-control">
                            <p class="form-hint">Lower numbers appear first in gallery list.</p>
                        </div>

                        <div class="form-group">
                            <label for="columns">Grid Columns</label>
                            <select id="columns" name="columns" class="form-control">
                                <option value="2" <?= ($gallery['columns'] ?? 3) == 2 ? 'selected' : '' ?>>2 Columns</option>
                                <option value="3" <?= ($gallery['columns'] ?? 3) == 3 ? 'selected' : '' ?>>3 Columns</option>
                                <option value="4" <?= ($gallery['columns'] ?? 3) == 4 ? 'selected' : '' ?>>4 Columns</option>
                                <option value="5" <?= ($gallery['columns'] ?? 3) == 5 ? 'selected' : '' ?>>5 Columns</option>
                            </select>
                            <p class="form-hint">Number of columns on desktop view.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Visibility</label>
                        <div class="toggle-options">
                            <label class="toggle-option <?= ($gallery['is_public'] ?? 1) ? 'active' : '' ?>">
                                <input type="radio" name="is_public" value="1" <?= ($gallery['is_public'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-icon">🌐</span>
                                <span class="toggle-text">Public</span>
                            </label>
                            <label class="toggle-option <?= !($gallery['is_public'] ?? 1) ? 'active' : '' ?>">
                                <input type="radio" name="is_public" value="0" <?= !($gallery['is_public'] ?? 1) ? 'checked' : '' ?>>
                                <span class="toggle-icon">🔒</span>
                                <span class="toggle-text">Private</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Preview -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔍 SEO Preview</h2>
                </div>
                <div class="card-body">
                    <div class="seo-preview">
                        <h3 class="seo-preview-title" id="seoTitle"><?= esc($gallery['name'] ?? 'Gallery Title') ?></h3>
                        <p class="seo-preview-url"><?= rtrim($_SERVER['HTTP_HOST'] ?? 'yoursite.com', '/') ?>/gallery/<span id="seoSlug"><?= esc($gallery['slug'] ?? 'gallery-slug') ?></span></p>
                        <p class="seo-preview-desc" id="seoDesc"><?= esc($gallery['description'] ?? 'Gallery description will appear here...') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar-column">
            <?php if ($isEdit): ?>
            <!-- Gallery Info -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">ℹ️ Gallery Info</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">ID</span>
                            <span class="info-value">#<?= (int)$gallery['id'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Images</span>
                            <span class="info-value"><?= (int)($gallery['image_count'] ?? 0) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value"><?= date('M j, Y', strtotime($gallery['created_at'] ?? 'now')) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Updated</span>
                            <span class="info-value"><?= date('M j, Y', strtotime($gallery['updated_at'] ?? 'now')) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">⚡ Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/images" class="action-link">
                            <span class="icon">📷</span>
                            <span class="text">
                                <strong>Manage Images</strong>
                                <span>Upload and organize photos</span>
                            </span>
                            <span class="arrow">→</span>
                        </a>
                        <a href="/gallery/<?= esc($gallery['slug']) ?>" target="_blank" class="action-link">
                            <span class="icon">👁️</span>
                            <span class="text">
                                <strong>View Gallery</strong>
                                <span>Open public gallery page</span>
                            </span>
                            <span class="arrow">→</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cover Image -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🖼️ Cover Image</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($gallery['cover_image'])): ?>
                    <div class="cover-preview">
                        <img src="/uploads/media/<?= esc($gallery['cover_image']) ?>" alt="Cover">
                        <button type="button" class="remove-cover" onclick="removeCover()">×</button>
                    </div>
                    <input type="hidden" name="cover_image" id="coverImage" value="<?= esc($gallery['cover_image']) ?>">
                    <?php else: ?>
                    <div class="cover-selector" onclick="selectCover()">
                        <div class="icon">🖼️</div>
                        <p>Click to select cover image</p>
                    </div>
                    <input type="hidden" name="cover_image" id="coverImage" value="">
                    <?php endif; ?>
                    <p class="form-hint" style="margin-top: 0.75rem;">
                        Used as thumbnail in gallery list. First image used if not set.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <div class="form-actions-left">
            <a href="/admin/galleries" class="btn btn-secondary">← Cancel</a>
            <?php if ($isEdit): ?>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">🗑️ Delete Gallery</button>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">
            <?= $isEdit ? '💾 Save Changes' : '✨ Create Gallery' ?>
        </button>
    </div>
</form>

<?php if ($isEdit): ?>
<form id="deleteForm" method="post" action="/admin/galleries/<?= (int)$gallery['id'] ?>/delete" style="display: none;">
    <?= csrf_field() ?>
</form>
<?php endif; ?>

<script>
// Auto-generate slug from name
document.getElementById('name')?.addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.manual) {
        slugInput.value = slug;
    }

    // Update SEO preview
    document.getElementById('seoTitle').textContent = this.value || 'Gallery Title';
    document.getElementById('seoSlug').textContent = slug || 'gallery-slug';
});

document.getElementById('slug')?.addEventListener('input', function() {
    this.dataset.manual = this.value ? 'true' : '';
    document.getElementById('seoSlug').textContent = this.value || 'gallery-slug';
});

document.getElementById('description')?.addEventListener('input', function() {
    document.getElementById('seoDesc').textContent = this.value || 'Gallery description will appear here...';
});

// Toggle options
document.querySelectorAll('.toggle-option input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.toggle-option').forEach(opt => opt.classList.remove('active'));
        this.closest('.toggle-option').classList.add('active');
    });
});

// Delete confirmation
function confirmDelete() {
    if (confirm('Are you sure you want to delete this gallery? This cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Cover image (placeholder - would need media browser integration)
function selectCover() {
    alert('Media browser integration coming soon!');
}

function removeCover() {
    document.getElementById('coverImage').value = '';
    location.reload();
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
