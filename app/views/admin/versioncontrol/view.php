<?php
$title = 'View Version';
ob_start();
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc($error) ?></div>
<?php endif; ?>

<div style="margin-bottom: 1rem;">
    <a href="/admin/version-control?type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>" class="btn btn-secondary">
        &larr; Back to Version History
    </a>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="card-title">Version Details</h2>
        <div style="display: flex; gap: 0.5rem;">
            <form method="post" action="/admin/version-control/restore" onsubmit="return confirm('Restore this version?');" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc($contentType) ?>">
                <input type="hidden" name="content_id" value="<?= esc($contentId) ?>">
                <input type="hidden" name="version_id" value="<?= esc($versionId) ?>">
                <button type="submit" class="btn btn-primary">Restore This Version</button>
            </form>
            <form method="post" action="/admin/version-control/delete" onsubmit="return confirm('Delete this version permanently?');" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc($contentType) ?>">
                <input type="hidden" name="content_id" value="<?= esc($contentId) ?>">
                <input type="hidden" name="version_id" value="<?= esc($versionId) ?>">
                <button type="submit" class="btn btn-danger">Delete Version</button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Version ID</label>
                <div style="font-family: monospace; font-size: 0.875rem; color: var(--color-text);"><?= esc($versionId) ?></div>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Content Type</label>
                <div style="font-size: 0.875rem; color: var(--color-text);"><?= esc(ucfirst($contentType)) ?></div>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Content ID</label>
                <div style="font-size: 0.875rem; color: var(--color-text);"><?= esc($contentId) ?></div>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Created At</label>
                <div style="font-size: 0.875rem; color: var(--color-text);"><?= esc($version['created_at'] ?? 'Unknown') ?></div>
            </div>
        </div>

        <?php if (!empty($version['comment'])): ?>
        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Comment</label>
            <div style="font-size: 0.875rem; color: var(--color-text); margin-top: 0.25rem; padding: 0.75rem; background: var(--color-bg-tertiary); border-radius: 4px;">
                <?= esc($version['comment']) ?>
            </div>
        </div>
        <?php endif; ?>

        <div>
            <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; display: block;">Version Data</label>
            <pre style="background: var(--color-bg-tertiary); color: var(--color-text); padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.8125rem; line-height: 1.5; margin: 0; max-height: 600px; overflow-y: auto;"><?= esc(json_encode($version['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-muted);">
            <strong>Note:</strong> Restoring a version will create a new version with the restored data. The current state of the content will be preserved in the version history.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
