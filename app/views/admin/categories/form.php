<?php
/**
 * Category Form - Modern Dark Theme
 */
$title = $category ? 'Edit Category' : 'New Category';
$isEdit = $category !== null;
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/categories" class="back-link">← Back to Categories</a>
        <h1><?= $isEdit ? '✏️ Edit Category' : '➕ New Category' ?></h1>
        <p class="page-subtitle"><?= $isEdit ? 'Update category details' : 'Create a new category to organize your content' ?></p>
    </div>
</div>

<?php
$flashSuccess = \Core\Session::getFlash('success');
$flashError = \Core\Session::getFlash('error');
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= esc($flashSuccess) ?></div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-error"><?= esc($flashError) ?></div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? '/admin/categories/' . (int)$category['id'] : '/admin/categories/' ?>">
    <?= csrf_field() ?>

    <div class="form-layout">
        <!-- Main Settings -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📁 Category Details</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Category Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?= esc($category['name'] ?? '') ?>" 
                           placeholder="e.g., Tutorials, News, AI Tools"
                           class="form-control">
                    <p class="form-hint">Choose a clear, descriptive name</p>
                </div>

                <div class="form-group">
                    <label for="slug">URL Slug</label>
                    <div class="input-with-prefix">
                        <span class="input-prefix">/category/</span>
                        <input type="text" id="slug" name="slug" 
                               value="<?= esc($category['slug'] ?? '') ?>" 
                               placeholder="auto-generated"
                               class="form-control">
                    </div>
                    <p class="form-hint">Leave empty to auto-generate from name</p>
                </div>

                <div class="form-group">
                    <label for="description">Description</label><span class="tip"><span class="tip-text">Optional description shown on category archive pages.</span></span>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Brief description of this category..."
                              class="form-control"><?= esc($category['description'] ?? '') ?></textarea>
                    <p class="form-hint">Shown on category archive pages and SEO</p>
                </div>
            </div>
        </div>

        <!-- Hierarchy & Order -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🗂️ Organization</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="parent_id">Parent Category<span class="tip"><span class="tip-text">Nest under another category to create hierarchy.</span></span></label><span class="tip"><span class="tip-text">Nest this under another category to create a hierarchy.</span></span>
                        <select id="parent_id" name="parent_id" class="form-control">
                            <option value="">📁 None (Top Level)</option>
                            <?php foreach ($parents as $parent): ?>
                                <?php if (!$isEdit || $parent['id'] != $category['id']): ?>
                                    <option value="<?= (int)$parent['id'] ?>" 
                                            <?= ($category['parent_id'] ?? '') == $parent['id'] ? 'selected' : '' ?>>
                                        📂 <?= esc($parent['name']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Make this a subcategory of another</p>
                    </div>

                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" 
                               value="<?= (int)($category['sort_order'] ?? 0) ?>" 
                               min="0" step="1"
                               class="form-control">
                        <p class="form-hint">Lower numbers appear first</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <!-- Info Card (Edit only) -->
        <div class="card info-card">
            <div class="card-header">
                <h2 class="card-title">ℹ️ Information</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">ID</span>
                    <span class="info-value">#<?= (int)$category['id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created</span>
                    <span class="info-value"><?= date('M j, Y', strtotime($category['created_at'])) ?></span>
                </div>
                <?php if (!empty($category['updated_at'])): ?>
                <div class="info-row">
                    <span class="info-label">Updated</span>
                    <span class="info-value"><?= date('M j, Y g:i A', strtotime($category['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Form Actions -->
    <div class="form-actions-bar">
        <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <?= $isEdit ? '💾 Save Changes' : '✨ Create Category' ?>
        </button>
    </div>
</form>

<style>
/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
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

/* Form Layout */
.form-layout {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 700px;
}

/* Card */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.card-title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}
.card-body {
    padding: 1.5rem;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}
.form-group:last-child {
    margin-bottom: 0;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
}
.required {
    color: #ef4444;
}
.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.form-control::placeholder {
    color: var(--text-muted);
}
textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
.form-hint {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin: 0.375rem 0 0 0;
}

/* Input with prefix */
.input-with-prefix {
    display: flex;
    align-items: center;
}
.input-prefix {
    padding: 0.625rem 0.875rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 8px 0 0 8px;
    color: var(--text-muted);
    font-size: 0.875rem;
    white-space: nowrap;
}
.input-with-prefix .form-control {
    border-radius: 0 8px 8px 0;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Info Card */
.info-card .card-body {
    padding: 1rem 1.5rem;
}
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}
.info-row:last-child {
    border-bottom: none;
}
.info-label {
    color: var(--text-muted);
    font-size: 0.875rem;
}
.info-value {
    font-weight: 500;
    color: var(--text-primary);
}

/* Form Actions Bar */
.form-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
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
    transform: translateY(-1px);
}
.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-primary);
}
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');

nameInput?.addEventListener('input', function() {
    if (!slugInput.dataset.manual) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
});

slugInput?.addEventListener('input', function() {
    this.dataset.manual = 'true';
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
