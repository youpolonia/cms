<?php
$title = 'Settings: ' . esc($extension['name']);
ob_start();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= esc($extension['name']) ?> Settings</h2>
        <a href="/admin/extensions" class="btn btn-secondary btn-sm">Back to Extensions</a>
    </div>
    <div class="card-body">
        <?php if (empty($settingsFields)): ?>
            <p style="color: var(--text-muted);">This extension has no configurable settings.</p>
        <?php else: ?>
            <form method="post" action="/admin/extensions/<?= (int)$extension['id'] ?>/settings" style="max-width: 600px;">
                <?= csrf_field() ?>

                <?php foreach ($settingsFields as $key => $field): ?>
                    <div class="form-group">
                        <label for="<?= esc($key) ?>"><?= esc($field['label'] ?? $key) ?></label>

                        <?php $value = $currentSettings[$key] ?? ($field['default'] ?? ''); ?>

                        <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                            <textarea id="<?= esc($key) ?>" name="<?= esc($key) ?>" rows="4"><?= esc($value) ?></textarea>
                        <?php elseif (($field['type'] ?? 'text') === 'select'): ?>
                            <select id="<?= esc($key) ?>" name="<?= esc($key) ?>">
                                <?php foreach (($field['options'] ?? []) as $optVal => $optLabel): ?>
                                    <option value="<?= esc($optVal) ?>" <?= $value == $optVal ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (($field['type'] ?? 'text') === 'checkbox'): ?>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: normal;">
                                <input type="checkbox" name="<?= esc($key) ?>" value="1" <?= $value ? 'checked' : '' ?>>
                                <?= esc($field['checkbox_label'] ?? 'Enable') ?>
                            </label>
                        <?php else: ?>
                            <input type="<?= esc($field['type'] ?? 'text') ?>" id="<?= esc($key) ?>" name="<?= esc($key) ?>" value="<?= esc($value) ?>">
                        <?php endif; ?>

                        <?php if (!empty($field['help'])): ?>
                            <p class="form-hint"><?= esc($field['help']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <a href="/admin/extensions" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
