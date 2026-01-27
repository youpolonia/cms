<?php
$title = 'Security Dashboard';
ob_start();
?>

<style>
.security-legend {
    background: var(--color-bg-tertiary);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 1rem 1.25rem;
    margin-top: 1.5rem;
}
.security-legend h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 0.75rem;
}
.legend-list {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: var(--color-text-muted);
}
.legend-item strong {
    color: var(--color-text-primary);
}
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon <?= $stats['score'] >= 80 ? 'success' : ($stats['score'] >= 50 ? 'warning' : 'danger') ?>">
                &#128737;
            </div>
        </div>
        <div class="stat-value"><?= $stats['score'] ?>%</div>
        <div class="stat-label">Security Score</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#10003;</div>
        </div>
        <div class="stat-value"><?= $stats['passed'] ?></div>
        <div class="stat-label">Checks Passed</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">&#10007;</div>
        </div>
        <div class="stat-value"><?= $stats['failed'] ?></div>
        <div class="stat-label">Issues Found</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon info">&#128270;</div>
        </div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Checks</div>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;">
    <?= esc($success) ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;">
    <?= esc($error) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Security Audit Results</h2>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <?php if ($lastScan): ?>
                <span style="font-size: 0.875rem; color: var(--color-text-muted);">Last scan: <?= esc($lastScan) ?></span>
            <?php endif; ?>
            <form method="post" action="/admin/security/scan" style="display: inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm">Run Scan</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php foreach ($results as $category => $checks): ?>
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--color-text-primary);">
                    <?= esc(ucfirst(str_replace('_', ' ', $category))) ?>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach ($checks as $check => $result): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--color-bg-tertiary); border-radius: 6px;">
                            <span><?= esc(ucfirst(str_replace('_', ' ', $check))) ?></span>
                            <?php if ($result): ?>
                                <span class="badge badge-success badge-dot">Passed</span>
                            <?php else: ?>
                                <span class="badge badge-danger badge-dot">Failed</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="security-legend">
    <h4>Security Checks Explained</h4>
    <div class="legend-list">
        <div class="legend-item"><strong>Min Length:</strong> Password minimum 12 characters</div>
        <div class="legend-item"><strong>Complexity:</strong> Password requires mixed case, numbers, symbols</div>
        <div class="legend-item"><strong>Session Timeout:</strong> Sessions expire within 1 hour</div>
        <div class="legend-item"><strong>Max Size:</strong> File uploads limited to 5MB</div>
        <div class="legend-item"><strong>Malware Scan:</strong> Uploaded files are scanned</div>
        <div class="legend-item"><strong>XSS Protection:</strong> X-XSS-Protection header enabled</div>
        <div class="legend-item"><strong>CSP:</strong> Content-Security-Policy header set</div>
        <div class="legend-item"><strong>HSTS:</strong> Strict-Transport-Security header enabled</div>
        <div class="legend-item"><strong>Parameterized Queries:</strong> SQL injection prevention</div>
        <div class="legend-item"><strong>Error Reporting:</strong> Database errors hidden from users</div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
