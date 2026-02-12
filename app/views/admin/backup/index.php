<?php
$title = 'Backup Manager';
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


<div class="inline-help" id="help-backup">
    <span class="inline-help-icon">💡</span>
    <div><strong>Backups</strong> protect your data. Create a backup before major changes (theme switches, updates). Download and store backups off-server for extra safety. <a href="/admin/docs?section=backup">Read more →</a></div>
    <button class="inline-help-close" onclick="this.closest('.inline-help').style.display='none';localStorage.setItem('help-backup-hidden','1')" title="Dismiss">×</button>
</div>
<script>if(localStorage.getItem('help-backup-hidden'))document.getElementById('help-backup').style.display='none'</script>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128190;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="stat-label">Total Backups</div><span class="tip"><span class="tip-text">All backup files stored on the server.</span></span>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#128230;</div>
        </div>
        <div class="stat-value"><?= formatBytes((int)($stats['totalSize'] ?? 0)) ?></div>
        <div class="stat-label">Total Size</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128451;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['dbBackups'] ?? 0) ?></div>
        <div class="stat-label">Database Backups</div><span class="tip"><span class="tip-text">SQL dumps of the database.</span></span>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#128197;</div>
        </div>
        <div class="stat-value"><?= $stats['lastBackup'] ? human_time_diff(strtotime($stats['lastBackup'])) : 'Never' ?></div>
        <div class="stat-label">Last Backup</div><span class="tip"><span class="tip-text">Time since the most recent backup was created.</span></span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; margin-top: 1.5rem;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Backups</h2>
            <span style="font-size: 0.875rem; color: var(--color-text-muted);"><?= $fileCount ?> files, <?= formatBytes($totalSize) ?> total</span>
        </div>

        <?php if (empty($backups)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128190;</div>
            <h3 class="empty-state-title">No Backups Found</h3>
            <p class="empty-state-description">Create your first backup using the form on the right.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th style="width: 100px;">Type</th>
                        <th style="width: 90px;">Size</th>
                        <th style="width: 70px;">Tables</th>
                        <th style="width: 140px;">Created</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td>
                            <code style="font-size: 12px; background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">
                                <?= esc($backup['filename']) ?>
                            </code>
                            <?php if ($backup['exists']): ?>
                                <span class="badge badge-success badge-dot" style="margin-left: 0.5rem;">OK</span>
                            <?php else: ?>
                                <span class="badge badge-danger badge-dot" style="margin-left: 0.5rem;">Missing</span>
                            <?php endif; ?>
                            <?php if (!empty($backup['notes'])): ?>
                                <small style="display: block; color: var(--color-text-muted); margin-top: 4px;"><?= esc($backup['notes']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-default"><?= esc(ucfirst($backup['type'])) ?></span></td>
                        <td style="color: var(--color-text-muted);"><?= formatBytes((int)$backup['size_bytes']) ?></td>
                        <td style="color: var(--color-text-muted);"><?= (int)$backup['tables_count'] ?></td>
                        <td style="color: var(--color-text-muted); font-size: 13px;"><?= date('M j, Y H:i', strtotime($backup['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if ($backup['exists']): ?>
                                    <a href="/admin/backup/<?= (int)$backup['id'] ?>/download" class="btn btn-primary btn-sm">Download</a>
                                <?php endif; ?>
                                <form method="post" action="/admin/backup/<?= (int)$backup['id'] ?>/delete" onsubmit="return confirm('Delete this backup?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Create Backup</h3>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/backup/">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="type">Backup Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="database">Database Only</option>
                            <option value="files" disabled>Files Only (requires CLI)</option>
                            <option value="full" disabled>Full Backup (requires CLI)</option>
                        </select>
                        <small class="form-hint">Database backups export all tables to SQL.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Notes (optional)</label>
                        <input type="text" class="form-input" id="notes" name="notes" placeholder="e.g., Before update">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Backup</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Cleanup</h3>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/backup/cleanup" onsubmit="return confirm('Delete old backups?');">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="days">Delete backups older than</label>
                        <select class="form-select" id="days" name="days">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                        </select>
                        <small class="form-hint">Permanently removes old backup files.</small>
                    </div>

                    <button type="submit" class="btn btn-danger" style="width: 100%;">Delete Old Backups</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-muted);">
            <strong>Note:</strong> Database backups are stored in <code>/storage/backups/</code>. File and full backups require CLI access which is not available in FTP-only environments.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
