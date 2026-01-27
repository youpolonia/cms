<?php
$title = 'Compare Versions';
ob_start();

function renderDiffValue($val, $indent = 0): string {
    $pad = str_repeat('  ', $indent);
    if (is_array($val)) {
        $out = "{\n";
        foreach ($val as $k => $v) {
            $out .= $pad . "  " . esc($k) . ": " . renderDiffValue($v, $indent + 1) . "\n";
        }
        $out .= $pad . "}";
        return $out;
    }
    if ($val === null) return '<span style="color: var(--color-text-muted);">null</span>';
    if (is_bool($val)) return $val ? 'true' : 'false';
    return esc((string)$val);
}
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

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Version Comparison</h2>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Content Type</label>
                <div style="font-size: 0.875rem; color: var(--color-text);"><?= esc(ucfirst($contentType)) ?></div>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Content ID</label>
                <div style="font-size: 0.875rem; color: var(--color-text);"><?= esc($contentId) ?></div>
            </div>
            <div>
                <label style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Comparing</label>
                <div style="font-size: 0.875rem; color: var(--color-text);">
                    <code><?= esc($version1Id) ?></code> vs <code><?= esc($version2Id) ?></code>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($diff)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1rem;">Differences Found</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Field</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diff as $key => $value): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= esc($key) ?></td>
                        <td>
                            <?php if ($value === null): ?>
                                <span class="badge badge-danger">Removed</span>
                            <?php elseif (is_array($value)): ?>
                                <pre style="background: var(--color-bg-tertiary); padding: 0.5rem; border-radius: 4px; margin: 0; font-size: 0.8125rem; overflow-x: auto;"><?= renderDiffValue($value) ?></pre>
                            <?php else: ?>
                                <span style="background: #a6e3a1; color: #1e1e2e; padding: 2px 6px; border-radius: 3px;"><?= esc((string)$value) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div class="empty-state" style="padding: 2rem;">
            <div class="empty-state-icon">&#9989;</div>
            <h3 class="empty-state-title">No Differences</h3>
            <p class="empty-state-description">These two versions have identical data.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <div class="card">
        <div class="card-header" style="background: var(--color-bg-tertiary);">
            <h3 class="card-title" style="font-size: 0.875rem;">
                Version 1: <code><?= esc($version1Id) ?></code>
            </h3>
            <small style="color: var(--color-text-muted);"><?= esc($version1['created_at'] ?? 'Unknown') ?></small>
        </div>
        <div class="card-body" style="padding: 0;">
            <pre style="background: transparent; color: var(--color-text); padding: 1rem; margin: 0; font-size: 0.8125rem; line-height: 1.5; overflow-x: auto; max-height: 500px; overflow-y: auto;"><?= esc(json_encode($version1['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        </div>
        <div class="card-footer" style="padding: 0.75rem 1rem; border-top: 1px solid var(--color-border);">
            <form method="post" action="/admin/version-control/restore" onsubmit="return confirm('Restore Version 1?');" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc($contentType) ?>">
                <input type="hidden" name="content_id" value="<?= esc($contentId) ?>">
                <input type="hidden" name="version_id" value="<?= esc($version1Id) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Restore This Version</button>
            </form>
            <a href="/admin/version-control/view?type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>&version_id=<?= urlencode($version1Id) ?>" class="btn btn-secondary btn-sm">View Full</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="background: var(--color-bg-tertiary);">
            <h3 class="card-title" style="font-size: 0.875rem;">
                Version 2: <code><?= esc($version2Id) ?></code>
            </h3>
            <small style="color: var(--color-text-muted);"><?= esc($version2['created_at'] ?? 'Unknown') ?></small>
        </div>
        <div class="card-body" style="padding: 0;">
            <pre style="background: transparent; color: var(--color-text); padding: 1rem; margin: 0; font-size: 0.8125rem; line-height: 1.5; overflow-x: auto; max-height: 500px; overflow-y: auto;"><?= esc(json_encode($version2['data'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
        </div>
        <div class="card-footer" style="padding: 0.75rem 1rem; border-top: 1px solid var(--color-border);">
            <form method="post" action="/admin/version-control/restore" onsubmit="return confirm('Restore Version 2?');" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc($contentType) ?>">
                <input type="hidden" name="content_id" value="<?= esc($contentId) ?>">
                <input type="hidden" name="version_id" value="<?= esc($version2Id) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Restore This Version</button>
            </form>
            <a href="/admin/version-control/view?type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>&version_id=<?= urlencode($version2Id) ?>" class="btn btn-secondary btn-sm">View Full</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
