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

/* Template Picker */
.template-picker {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.75rem;
}
@media (max-width: 1200px) { .template-picker { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 640px) { .template-picker { grid-template-columns: repeat(2, 1fr); } }

.template-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0.75rem;
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.template-option:hover {
    border-color: var(--accent);
    background: var(--accent-muted);
}
.template-option.active {
    border-color: var(--accent);
    background: var(--accent-muted);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.template-option input { display: none; }

/* Mini preview blocks */
.template-preview {
    width: 100%;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    background: var(--bg-primary);
    padding: 4px;
}
.tp-blocks {
    width: 100%;
    height: 100%;
    display: grid;
    gap: 2px;
}
/* Grid preview */
.template-preview-grid .tp-blocks {
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(2, 1fr);
}
.template-preview-grid .tp-blocks::before,
.template-preview-grid .tp-blocks::after { content: ''; }
.template-preview-grid .tp-blocks,
.template-preview-grid .tp-blocks::before,
.template-preview-grid .tp-blocks::after { background: var(--accent); border-radius: 2px; opacity: 0.6; }
/* hacky but visual: use box shadows for grid squares */
.template-preview-grid .tp-blocks {
    background: none;
}
.template-preview-grid .tp-blocks::before { display: none; }
.template-preview-grid .tp-blocks::after { display: none; }

/* Masonry preview */
.template-preview-masonry .tp-blocks {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}
/* Mosaic preview */
.template-preview-mosaic .tp-blocks {
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(2, 1fr);
}
/* Carousel preview */
.template-preview-carousel .tp-blocks {
    display: flex;
    gap: 3px;
    overflow: hidden;
}
/* Justified preview */
.template-preview-justified .tp-blocks {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

.template-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-primary);
}
.template-desc {
    font-size: 0.7rem;
    color: var(--text-muted);
    line-height: 1.3;
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
                        <label for="name">Gallery Name<span class="tip"><span class="tip-text">Display name of this image gallery.</span></span> <span class="required">*</span></label>
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
                        <label for="description">Description</label><span class="tip"><span class="tip-text">Optional description shown on the gallery page.</span></span>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Brief description of this gallery for SEO and visitors..."
                                  class="form-control"><?= esc($gallery['description'] ?? '') ?></textarea>
                        <p class="form-hint">Shown on the gallery page and used for SEO meta description.</p>
                    </div>
                </div>
            </div>

            <!-- Display Template -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">🎨 Gallery Template</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Layout Style</label>
                        <p class="form-hint" style="margin-top:0;margin-bottom:1rem">Choose how images are displayed on the gallery page.</p>
                        <div class="template-picker">
                            <?php
                            $currentTemplate = $gallery['display_template'] ?? 'grid';
                            $templates = [
                                'grid' => ['icon' => '⊞', 'name' => 'Grid', 'desc' => 'Uniform grid with equal-sized images'],
                                'masonry' => ['icon' => '▥', 'name' => 'Masonry', 'desc' => 'Pinterest-style staggered layout'],
                                'mosaic' => ['icon' => '◫', 'name' => 'Mosaic', 'desc' => 'Featured large + mixed sizes'],
                                'carousel' => ['icon' => '◁▷', 'name' => 'Carousel', 'desc' => 'Horizontal scrolling slider'],
                                'justified' => ['icon' => '☰', 'name' => 'Justified', 'desc' => 'Flickr-style equal-height rows'],
                            ];
                            foreach ($templates as $key => $tpl): ?>
                            <label class="template-option <?= $currentTemplate === $key ? 'active' : '' ?>">
                                <input type="radio" name="display_template" value="<?= $key ?>" <?= $currentTemplate === $key ? 'checked' : '' ?>>
                                <div class="template-preview template-preview-<?= $key ?>">
                                    <div class="tp-blocks"></div>
                                </div>
                                <span class="template-name"><?= $tpl['name'] ?></span>
                                <span class="template-desc"><?= $tpl['desc'] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
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
                        <div class="form-group"></div>
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

// Template picker
document.querySelectorAll('.template-option input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.template-option').forEach(opt => opt.classList.remove('active'));
        this.closest('.template-option').classList.add('active');
    });
});

// Draw mini preview blocks
document.querySelectorAll('.template-preview').forEach(preview => {
    const blocks = preview.querySelector('.tp-blocks');
    const type = preview.className.replace('template-preview ', '').replace('template-preview-', '');

    // Generate colored blocks for visual preview
    const colors = ['#89b4fa', '#a6e3a1', '#f9e2af', '#f38ba8', '#cba6f7', '#89dceb'];

    if (type.includes('grid')) {
        for (let i = 0; i < 6; i++) {
            const b = document.createElement('div');
            b.style.cssText = 'background:' + colors[i] + ';border-radius:2px;opacity:0.7';
            blocks.appendChild(b);
        }
    } else if (type.includes('masonry')) {
        const heights = [24, 18, 30, 16, 26, 20];
        for (let i = 0; i < 6; i++) {
            const b = document.createElement('div');
            b.style.cssText = 'width:30%;height:' + heights[i] + 'px;background:' + colors[i] + ';border-radius:2px;opacity:0.7;flex-shrink:0';
            blocks.appendChild(b);
        }
    } else if (type.includes('mosaic')) {
        // Big + small blocks
        const b1 = document.createElement('div');
        b1.style.cssText = 'grid-column:span 2;grid-row:span 2;background:' + colors[0] + ';border-radius:2px;opacity:0.7';
        blocks.appendChild(b1);
        for (let i = 1; i < 4; i++) {
            const b = document.createElement('div');
            b.style.cssText = 'background:' + colors[i] + ';border-radius:2px;opacity:0.7';
            blocks.appendChild(b);
        }
    } else if (type.includes('carousel')) {
        for (let i = 0; i < 4; i++) {
            const b = document.createElement('div');
            b.style.cssText = 'min-width:35%;height:100%;background:' + colors[i] + ';border-radius:3px;opacity:0.7;flex-shrink:0';
            blocks.appendChild(b);
        }
    } else if (type.includes('justified')) {
        const widths = [40, 25, 35, 30, 35, 35];
        for (let i = 0; i < 6; i++) {
            const b = document.createElement('div');
            b.style.cssText = 'width:' + widths[i] + '%;height:22px;background:' + colors[i] + ';border-radius:2px;opacity:0.7;flex-shrink:0;flex-grow:1';
            blocks.appendChild(b);
        }
    }
});

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
