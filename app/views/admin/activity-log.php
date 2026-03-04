<?php
/**
 * Admin Activity Log View
 */
$pageTitle = 'Activity Log';
require CMS_ROOT . '/app/views/admin/layouts/header.php';
?>

<div class="content-header">
    <h1>📋 Activity Log</h1>
    <p class="text-muted">Audit trail of admin actions (<?= number_format($total) ?> total)</p>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label small">Category</label>
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= h($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>><?= h(ucfirst($cat)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label small">Search</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= h($search) ?>" placeholder="Search logs...">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if ($selectedCategory || $search): ?>
                <a href="/admin/activity-log" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Log Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:150px;">When</th>
                    <th style="width:100px;">Admin</th>
                    <th style="width:100px;">Category</th>
                    <th>Description</th>
                    <th style="width:120px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-muted small"><?= date('M j, H:i', strtotime($log['created_at'])) ?></td>
                        <td><span class="badge bg-secondary"><?= h($log['admin_username'] ?: 'System') ?></span></td>
                        <td><span class="badge bg-info text-dark"><?= h($log['category']) ?></span></td>
                        <td><?= h($log['description']) ?></td>
                        <td class="text-muted small"><?= h($log['ip_address'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <div>
                <?php if ($currentPage > 1): ?><a href="?page=<?= $currentPage - 1 ?>&category=<?= h($selectedCategory) ?>&q=<?= h($search) ?>" class="btn btn-sm btn-outline-primary">← Prev</a><?php endif; ?>
                <?php if ($currentPage < $totalPages): ?><a href="?page=<?= $currentPage + 1 ?>&category=<?= h($selectedCategory) ?>&q=<?= h($search) ?>" class="btn btn-sm btn-outline-primary">Next →</a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Cleanup -->
<div class="card mt-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span class="text-muted">Cleanup old entries</span>
        <form method="post" action="/admin/activity-log/clear" class="d-flex gap-2 align-items-center">
            <?= csrf_field() ?>
            <span class="text-muted small">Delete entries older than</span>
            <select name="days" class="form-select form-select-sm" style="width:auto;">
                <option value="30">30 days</option>
                <option value="60">60 days</option>
                <option value="90" selected>90 days</option>
                <option value="180">180 days</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete old log entries?')">Clean Up</button>
        </form>
    </div>
</div>

<?php require CMS_ROOT . '/app/views/admin/layouts/footer.php'; ?>
