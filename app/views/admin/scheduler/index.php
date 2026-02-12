<?php
$title = 'Scheduler';
ob_start();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128337;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="stat-label">Total Jobs</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#9654;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['active'] ?? 0) ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#10074;&#10074;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['disabled'] ?? 0) ?></div>
        <div class="stat-label">Disabled</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">&#10060;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['failed'] ?? 0) ?></div>
        <div class="stat-label">Failed</div>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc($error) ?></div>
<?php endif; ?>

<?php if (empty($tableExists)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">&#9888; Database Table Missing</h2>
    </div>
    <div class="card-body">
        <p style="color: var(--color-text-muted); margin-bottom: 1rem;">The <code>scheduler_jobs</code> table does not exist. Create it using:</p>
        <pre style="background: var(--color-bg-tertiary); padding: 1rem; border-radius: var(--radius-md); overflow-x: auto; font-size: 12px;">CREATE TABLE scheduler_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    job_type VARCHAR(100) NOT NULL DEFAULT 'cron',
    schedule_expression VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    last_run_at DATETIME NULL,
    next_run_at DATETIME NULL,
    last_result TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
    </div>
</div>
<?php else: ?>

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title">Scheduled Jobs</h2>
        <a href="/admin/scheduler/create" class="btn btn-primary btn-sm">+ New Job</a>
    </div>

    <div class="card-body" style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
        <form method="get" action="/admin/scheduler" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="q" value="<?= esc($search ?? '') ?>" placeholder="Search jobs..." class="form-input" style="width: 100%;">
            </div>
            <div>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= ($statusFilter ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="active" <?= ($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="disabled" <?= ($statusFilter ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    <option value="failed" <?= ($statusFilter ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if (!empty($search) || ($statusFilter ?? 'all') !== 'all'): ?>
                <a href="/admin/scheduler" class="btn btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128197;</div>
            <h3 class="empty-state-title">No Jobs Found</h3>
            <p class="empty-state-description">
                <?php if (!empty($search) || ($statusFilter ?? 'all') !== 'all'): ?>
                    No jobs match your search criteria. Try adjusting your filters.
                <?php else: ?>
                    Create your first scheduled job to automate tasks.
                <?php endif; ?>
            </p>
            <a href="/admin/scheduler/create" class="btn btn-primary">+ Create Job</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Name</th><span class="tip"><span class="tip-text">Descriptive name of the scheduled task.</span></span>
                        <th style="width: 100px;">Type</th>
                        <th style="width: 140px;">Schedule</th><span class="tip"><span class="tip-text">How often this task runs (hourly, daily, etc.).</span></span>
                        <th style="width: 100px;">Status</th><span class="tip"><span class="tip-text">Active tasks run on schedule. Paused tasks are skipped.</span></span>
                        <th style="width: 160px;">Last Run</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td style="color: var(--color-text-muted);">#<?= (int)$job['id'] ?></td>
                        <td>
                            <strong><?= esc($job['name']) ?></strong>
                            <?php if (!empty($job['last_result'])): ?>
                                <small style="display: block; color: var(--color-text-muted); margin-top: 2px;">
                                    <?= esc(mb_strlen($job['last_result']) > 50 ? mb_substr($job['last_result'], 0, 50) . '...' : $job['last_result']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code style="font-size: 12px; background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">
                                <?= esc($job['job_type']) ?>
                            </code>
                        </td>
                        <td>
                            <code style="font-size: 12px; background: var(--color-bg-tertiary); padding: 2px 6px; border-radius: 4px;">
                                <?= esc($job['schedule_expression']) ?>
                            </code>
                        </td>
                        <td>
                            <?php if ($job['status'] === 'active'): ?>
                                <span class="badge badge-success badge-dot">Active</span>
                            <?php elseif ($job['status'] === 'failed'): ?>
                                <span class="badge badge-danger badge-dot">Failed</span>
                            <?php else: ?>
                                <span class="badge badge-warning badge-dot">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--color-text-muted); font-size: 13px;">
                            <?= $job['last_run_at'] ? esc($job['last_run_at']) : '<em>Never</em>' ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="/admin/scheduler/<?= $job['id'] ?>/edit" class="btn btn-ghost btn-sm">Edit</a>
                                <form method="POST" action="/admin/scheduler/<?= $job['id'] ?>/run" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-success btn-sm">Run</button>
                                </form>
                                <form method="POST" action="/admin/scheduler/<?= $job['id'] ?>/toggle" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <?= $job['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/scheduler/<?= $job['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Delete this job?')">
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

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="card-footer" style="display: flex; justify-content: center; gap: 0.5rem;">
            <?php $pg = $page ?? 1; $tp = $totalPages ?? 1; $sr = $search ?? ''; $sf = $statusFilter ?? 'all'; ?>
            <?php if ($pg > 1): ?>
                <a href="?page=<?= $pg-1 ?>&q=<?= urlencode($sr) ?>&status=<?= $sf ?>" class="btn btn-ghost btn-sm">&larr; Prev</a>
            <?php endif; ?>
            <span style="padding: 0.5rem 1rem; color: var(--color-text-muted);">
                Page <?= $pg ?> of <?= $tp ?>
            </span>
            <?php if ($pg < $tp): ?>
                <a href="?page=<?= $pg+1 ?>&q=<?= urlencode($sr) ?>&status=<?= $sf ?>" class="btn btn-ghost btn-sm">Next &rarr;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-muted);">
            <strong>Cron Format:</strong> <code>minute hour day month weekday</code> &mdash; 
            Examples: <code>*/15 * * * *</code> (every 15 min), <code>0 2 * * *</code> (daily at 2am), <code>0 0 * * 0</code> (weekly Sunday)
        </p>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
