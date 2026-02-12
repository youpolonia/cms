<?php
$title = 'Version Control';
ob_start();

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

function human_time_diff($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    return floor($diff/86400) . ' days ago';
}
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc($error) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128203;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['version_count'] ?? 0) ?></div>
        <div class="stat-label">Total Versions</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#128230;</div>
        </div>
        <div class="stat-value"><?= formatBytes((int)($stats['total_size'] ?? 0)) ?></div>
        <div class="stat-label">Storage Used</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#128193;</div>
        </div>
        <div class="stat-value"><?= count($stats['content_types'] ?? []) ?></div>
        <div class="stat-label">Content Types</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon info">&#128269;</div>
        </div>
        <div class="stat-value"><?= !empty($versions) ? count($versions) : '&mdash;' ?></div>
        <div class="stat-label">Selected</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; margin-top: 1.5rem;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Version History</h2>
            <?php if ($currentType && $currentContentId): ?>
            <span class="badge badge-primary"><?= esc($currentType) ?> / <?= esc($currentContentId) ?></span>
            <?php endif; ?>
        </div>

        <?php if (!$currentType): ?>
        <div class="card-body">
            <p style="color: var(--color-text-muted); margin-bottom: 1rem;">Select a content type and item from the Content Browser on the right to view its version history.</p>

            <?php if (!empty($contentTypes)): ?>
            <div class="content-tree">
                <?php foreach ($contentTypes as $type => $items): ?>
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; color: var(--color-text); margin-bottom: 0.5rem;">
                        <span style="color: var(--color-primary);">&#128193;</span> <?= esc(ucfirst($type)) ?>
                    </div>
                    <div style="padding-left: 1.5rem;">
                        <?php foreach ($items as $item): ?>
                        <a href="/admin/version-control?type=<?= urlencode($type) ?>&content_id=<?= urlencode($item['id']) ?>"
                           style="display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; background: var(--color-bg-tertiary); border-radius: 4px; text-decoration: none; color: var(--color-text);">
                            <span><?= esc($item['id']) ?></span>
                            <span class="badge badge-default"><?= (int)$item['versions'] ?> versions</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128203;</div>
                <h3 class="empty-state-title">No Versions Found</h3>
                <p class="empty-state-description">No versioned content has been created yet.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif (empty($versions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128203;</div>
            <h3 class="empty-state-title">No Versions</h3>
            <p class="empty-state-description">No versions found for this content.</p>
            <a href="/admin/version-control" class="btn btn-secondary">Back to Browser</a>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Version ID</th><span class="tip"><span class="tip-text">Revision number. Higher = newer.</span></span>
                        <th style="width: 150px;">Created</th>
                        <th>Comment</th><span class="tip"><span class="tip-text">Description of what changed in this version.</span></span>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versions as $version): ?>
                    <tr>
                        <td>
                            <code style="font-size: 12px; background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">
                                <?= esc($version['id']) ?>
                            </code>
                        </td>
                        <td style="color: var(--color-text-muted); font-size: 13px;">
                            <?= date('M j, Y H:i', strtotime($version['created_at'])) ?>
                            <small style="display: block;"><?= human_time_diff(strtotime($version['created_at'])) ?></small>
                        </td>
                        <td style="color: var(--color-text-muted);">
                            <?= !empty($version['comment']) ? esc($version['comment']) : '<em>No comment</em>' ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="/admin/version-control/view?type=<?= urlencode($currentType) ?>&content_id=<?= urlencode($currentContentId) ?>&version_id=<?= urlencode($version['id']) ?>"
                                   class="btn btn-secondary btn-sm">View</a>
                                <form method="post" action="/admin/version-control/restore" onsubmit="return confirm('Restore this version?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="<?= esc($currentType) ?>">
                                    <input type="hidden" name="content_id" value="<?= esc($currentContentId) ?>">
                                    <input type="hidden" name="version_id" value="<?= esc($version['id']) ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Restore</button>
                                </form>
                                <form method="post" action="/admin/version-control/delete" onsubmit="return confirm('Delete this version?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="<?= esc($currentType) ?>">
                                    <input type="hidden" name="content_id" value="<?= esc($currentContentId) ?>">
                                    <input type="hidden" name="version_id" value="<?= esc($version['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($versions) >= 2): ?>
        <div class="card-body" style="border-top: 1px solid var(--color-border);">
            <h4 style="font-size: 0.875rem; margin-bottom: 0.75rem;">Compare Versions</h4>
            <form method="get" action="/admin/version-control/compare" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
                <input type="hidden" name="type" value="<?= esc($currentType) ?>">
                <input type="hidden" name="content_id" value="<?= esc($currentContentId) ?>">
                <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                    <label class="form-label" for="version1">Version 1</label>
                    <select class="form-select" id="version1" name="version1">
                        <?php foreach ($versions as $v): ?>
                        <option value="<?= esc($v['id']) ?>"><?= esc($v['id']) ?> - <?= date('M j H:i', strtotime($v['created_at'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 140px; margin-bottom: 0;">
                    <label class="form-label" for="version2">Version 2</label>
                    <select class="form-select" id="version2" name="version2">
                        <?php $i = 0; foreach ($versions as $v): ?>
                        <option value="<?= esc($v['id']) ?>" <?= $i === 1 ? 'selected' : '' ?>><?= esc($v['id']) ?> - <?= date('M j H:i', strtotime($v['created_at'])) ?></option>
                        <?php $i++; endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Compare</button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Content Browser</h3>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (!empty($contentTypes)): ?>
                <?php foreach ($contentTypes as $type => $items): ?>
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--color-text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--color-primary);">&#128193;</span>
                        <?= esc(ucfirst($type)) ?>
                        <span class="badge badge-default" style="font-weight: normal;"><?= count($items) ?></span>
                    </div>
                    <div style="padding-left: 1rem; font-size: 0.875rem;">
                        <?php foreach ($items as $item): ?>
                        <a href="/admin/version-control?type=<?= urlencode($type) ?>&content_id=<?= urlencode($item['id']) ?>"
                           class="<?= ($currentType === $type && $currentContentId === $item['id']) ? 'active' : '' ?>"
                           style="display: flex; justify-content: space-between; padding: 0.4rem 0.5rem; margin-bottom: 0.25rem; border-radius: 4px; text-decoration: none; color: var(--color-text-muted); <?= ($currentType === $type && $currentContentId === $item['id']) ? 'background: var(--color-primary); color: white;' : '' ?>">
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;"><?= esc($item['id']) ?></span>
                            <span style="font-size: 0.75rem; opacity: 0.8;"><?= (int)$item['versions'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="color: var(--color-text-muted); font-size: 0.875rem; margin: 0;">No versioned content found.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($currentType && $currentContentId): ?>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Purge Old Versions</h3>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/version-control/purge" onsubmit="return confirm('This will permanently delete old versions. Continue?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= esc($currentType) ?>">
                    <input type="hidden" name="content_id" value="<?= esc($currentContentId) ?>">

                    <div class="form-group">
                        <label class="form-label" for="days">Delete versions older than</label>
                        <select class="form-select" id="days" name="days">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger" style="width: 100%;">Purge Old Versions</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body" style="padding: 1rem;">
                <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">About Version Control</h4>
                <p style="font-size: 0.8125rem; color: var(--color-text-muted); margin: 0; line-height: 1.5;">
                    Version control tracks changes to your content over time. You can view, compare, and restore previous versions of any versioned content.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
