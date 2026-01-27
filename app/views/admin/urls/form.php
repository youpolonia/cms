<?php
$title = $redirect ? 'Edit Redirect' : 'New Redirect';
$isEdit = $redirect !== null;
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $isEdit ? 'Edit Redirect' : 'Create New Redirect' ?></h2>
        <a href="/admin/urls" class="btn btn-secondary btn-sm">&larr; Back to Redirects</a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? '/admin/urls/' . (int)$redirect['id'] : '/admin/urls/' ?>" style="max-width: 600px;">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="source_url">Source URL *</label>
                <input type="text" id="source_url" name="source_url" required value="<?= esc($redirect['source_url'] ?? '') ?>" placeholder="/old-page">
                <p class="form-hint">The URL path to redirect from (e.g., /old-page)</p>
            </div>

            <div class="form-group">
                <label for="target_url">Target URL *</label>
                <input type="text" id="target_url" name="target_url" required value="<?= esc($redirect['target_url'] ?? '') ?>" placeholder="/new-page or https://example.com">
                <p class="form-hint">The URL to redirect to (relative or absolute)</p>
            </div>

            <div class="form-group">
                <label for="status_code">Redirect Type</label>
                <select id="status_code" name="status_code">
                    <option value="301" <?= ($redirect['status_code'] ?? 301) == 301 ? 'selected' : '' ?>>301 - Permanent (SEO recommended)</option>
                    <option value="302" <?= ($redirect['status_code'] ?? 0) == 302 ? 'selected' : '' ?>>302 - Temporary</option>
                    <option value="307" <?= ($redirect['status_code'] ?? 0) == 307 ? 'selected' : '' ?>>307 - Temporary (strict)</option>
                    <option value="308" <?= ($redirect['status_code'] ?? 0) == 308 ? 'selected' : '' ?>>308 - Permanent (strict)</option>
                </select>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= ($redirect['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Redirect' ?></button>
                <a href="/admin/urls" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
