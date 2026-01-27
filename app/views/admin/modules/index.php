<?php
$title = 'Modules';
ob_start();

function formatBytes($bytes, $precision = 1) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, $precision) . ' KB';
    return round($bytes / 1048576, $precision) . ' MB';
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128230;</div>
        </div>
        <div class="stat-value"><?= (int)$stats['total'] ?></div>
        <div class="stat-label">Total Modules</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#10003;</div>
        </div>
        <div class="stat-value"><?= (int)$stats['enabled'] ?></div>
        <div class="stat-label">Active Modules</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#9888;</div>
        </div>
        <div class="stat-value"><?= (int)$stats['disabled'] ?></div>
        <div class="stat-label">Disabled Modules</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">&#128274;</div>
        </div>
        <div class="stat-value"><?= (int)$stats['core'] ?></div>
        <div class="stat-label">Core Modules</div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title">Module Manager</h2>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <form method="post" action="/admin/modules/refresh" style="display: inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-secondary btn-sm">
                    &#8635; Refresh
                </button>
            </form>
        </div>
    </div>

    <div class="card-body" style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
        <form method="get" action="/admin/modules" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search modules..." class="form-input" style="width: 100%;">
            </div>
            <div>
                <select name="filter" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Modules (<?= $stats['total'] ?>)</option>
                    <option value="enabled" <?= $filter === 'enabled' ? 'selected' : '' ?>>Active (<?= $stats['enabled'] ?>)</option>
                    <option value="disabled" <?= $filter === 'disabled' ? 'selected' : '' ?>>Disabled (<?= $stats['disabled'] ?>)</option>
                    <option value="core" <?= $filter === 'core' ? 'selected' : '' ?>>Core (<?= $stats['core'] ?>)</option>
                    <option value="third-party" <?= $filter === 'third-party' ? 'selected' : '' ?>>Third-Party (<?= $stats['third_party'] ?>)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if ($filter !== 'all' || !empty($search)): ?>
                <a href="/admin/modules" class="btn btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($modules)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128230;</div>
            <h3 class="empty-state-title">No Modules Found</h3>
            <p class="empty-state-description">
                <?php if (!empty($search) || $filter !== 'all'): ?>
                    No modules match your search criteria. Try adjusting your filters.
                <?php else: ?>
                    No modules are installed. Upload modules to <code>/modules/</code> via FTP.
                <?php endif; ?>
            </p>
            <?php if (!empty($search) || $filter !== 'all'): ?>
                <a href="/admin/modules" class="btn btn-primary">View All Modules</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form id="bulk-form" method="post" action="/admin/modules/bulk">
            <?= csrf_field() ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" title="Select all">
                            </th>
                            <th>Module</th>
                            <th style="width: 100px;">Version</th>
                            <th style="width: 100px;">Files</th>
                            <th style="width: 100px;">Size</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $module):
                            $isEnabled = $state[$module['slug']]['enabled'] ?? true;
                            $isCore = in_array($module['slug'], $coreModules);
                            $effectivelyEnabled = $isCore || $isEnabled;
                        ?>
                        <tr>
                            <td>
                                <?php if (!$isCore): ?>
                                    <input type="checkbox" name="modules[]" value="<?= esc($module['slug']) ?>" class="module-checkbox">
                                <?php else: ?>
                                    <input type="checkbox" disabled title="Core modules cannot be modified">
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                    <div style="width: 40px; height: 40px; background: var(--color-bg-tertiary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                        <?php if ($isCore): ?>&#128274;<?php elseif ($module['has_manifest']): ?>&#128230;<?php else: ?>&#128193;<?php endif; ?>
                                    </div>
                                    <div>
                                        <strong style="display: block; margin-bottom: 2px;">
                                            <a href="/admin/modules/<?= esc($module['slug']) ?>" style="color: inherit; text-decoration: none;">
                                                <?= esc($module['name']) ?>
                                            </a>
                                        </strong>
                                        <?php if ($isCore): ?>
                                            <span class="badge badge-danger" style="font-size: 10px; padding: 2px 6px;">CORE</span>
                                        <?php endif; ?>
                                        <?php if (!$module['has_manifest']): ?>
                                            <span class="badge badge-warning" style="font-size: 10px; padding: 2px 6px;">No Manifest</span>
                                        <?php endif; ?>
                                        <?php if ($module['description']): ?>
                                            <small style="color: var(--color-text-muted); display: block; margin-top: 4px; line-height: 1.4;">
                                                <?= esc(mb_strlen($module['description']) > 80 ? mb_substr($module['description'], 0, 80) . '...' : $module['description']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="font-size: 12px; background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">
                                    <?= esc($module['version']) ?>
                                </code>
                            </td>
                            <td style="color: var(--color-text-muted);">
                                <?= number_format($module['file_count']) ?>
                            </td>
                            <td style="color: var(--color-text-muted);">
                                <?= formatBytes($module['size']) ?>
                            </td>
                            <td>
                                <?php if ($isCore): ?>
                                    <span class="badge badge-default badge-dot">Required</span>
                                <?php elseif ($effectivelyEnabled): ?>
                                    <span class="badge badge-success badge-dot">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-warning badge-dot">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="/admin/modules/<?= esc($module['slug']) ?>" class="btn btn-ghost btn-sm" title="View Details">
                                        Details
                                    </a>
                                    <?php if (!$isCore): ?>
                                        <form method="post" action="/admin/modules/<?= esc($module['slug']) ?>/toggle" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm <?= $effectivelyEnabled ? 'btn-secondary' : 'btn-primary' ?>">
                                                <?= $effectivelyEnabled ? 'Disable' : 'Enable' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled title="Core modules are always active">
                                            Required
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer" style="display: flex; align-items: center; gap: 1rem;">
                <span style="color: var(--color-text-muted); font-size: 13px;">
                    <span id="selected-count">0</span> selected
                </span>
                <select name="action" class="form-select" style="width: auto;">
                    <option value="">Bulk Actions</option>
                    <option value="enable">Enable Selected</option>
                    <option value="disable">Disable Selected</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirmBulk()">Apply</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-muted);">
            <strong>Module Installation:</strong> Upload module folders to <code>/modules/</code> via FTP. Each module should include a <code>manifest.json</code> file with metadata. Core modules (admin, auth, content) cannot be disabled.
        </p>
    </div>
</div>

<script>
document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
    updateSelectedCount();
});

document.querySelectorAll('.module-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const count = document.querySelectorAll('.module-checkbox:checked').length;
    document.getElementById('selected-count').textContent = count;
}

function confirmBulk() {
    const action = document.querySelector('select[name="action"]').value;
    const count = document.querySelectorAll('.module-checkbox:checked').length;

    if (!action) {
        alert('Please select an action.');
        return false;
    }

    if (count === 0) {
        alert('Please select at least one module.');
        return false;
    }

    return confirm(`Are you sure you want to ${action} ${count} module(s)?`);
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
