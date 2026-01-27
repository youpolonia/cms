<?php
/**
 * TB4 Builder - Create Page View
 * Simple form to create a new TB4 page
 */
$title = 'Create TB4 Page';
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">Create New Page</h1>
        <p class="page-description">Start building a new page with the TB4 visual builder</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/tb4" class="btn btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Pages
        </a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="/admin/tb4/create">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="title">Page Title <span class="required">*</span></label>
                <input type="text" class="form-input" id="title" name="title" placeholder="Enter page title" required autofocus>
                <p class="form-hint">The title will be displayed in the page header and browser tab.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="slug">URL Slug</label>
                <div class="input-group">
                    <span class="input-group-text">/</span>
                    <input type="text" class="form-input" id="slug" name="slug" placeholder="auto-generated-from-title">
                </div>
                <p class="form-hint">Leave empty to auto-generate from title. Use lowercase letters, numbers, and hyphens only.</p>
            </div>

            <div class="form-actions">
                <a href="/admin/tb4" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create Page
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: var(--space-6);
}

.page-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text-primary);
}

.page-description {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}

.form-group {
    margin-bottom: var(--space-5);
}

.form-label {
    display: block;
    font-weight: var(--font-medium);
    margin-bottom: var(--space-2);
    color: var(--color-text-primary);
}

.form-label .required {
    color: var(--color-danger, #f87171);
}

.form-hint {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}

.input-group {
    display: flex;
    align-items: stretch;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0 var(--space-3);
    background: var(--color-bg-tertiary);
    border: 1px solid var(--color-border);
    border-right: none;
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    color: var(--color-text-muted);
    font-family: var(--font-mono);
}

.input-group .form-input {
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    flex: 1;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-3);
    padding-top: var(--space-4);
    border-top: 1px solid var(--color-border);
    margin-top: var(--space-6);
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: var(--space-4);
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Auto-generate slug from title
document.getElementById('title')?.addEventListener('input', function(e) {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.dataset.modified) {
        slugInput.value = e.target.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});

// Mark slug as modified if user edits it
document.getElementById('slug')?.addEventListener('input', function(e) {
    e.target.dataset.modified = 'true';
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
