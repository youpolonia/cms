<?php
$title = 'URL Redirects';
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
        <h2 class="card-title">URL Redirects</h2>
        <a href="/admin/urls/create" class="btn btn-primary btn-sm">+ New Redirect</a>
    </div>

    <?php if (empty($redirects)): ?>
        <div class="card-body">
            <p style="color: var(--text-muted);">No redirects found. <a href="/admin/urls/create">Create your first redirect</a>.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Source URL</th>
                    <th>Target URL</th>
                    <th>Code</th>
                    <th>Hits</th>
                    <th>Status</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($redirects as $redirect): ?>
                    <tr>
                        <td><code><?= esc($redirect['source_url']) ?></code></td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">
                            <code><?= esc($redirect['target_url']) ?></code>
                        </td>
                        <td><span class="badge badge-info"><?= (int)$redirect['status_code'] ?></span></td>
                        <td><?= number_format((int)$redirect['hits']) ?></td>
                        <td>
                            <?php if ($redirect['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="/admin/urls/<?= (int)$redirect['id'] ?>/edit" class="btn btn-secondary btn-sm">Edit</a>
                                <form method="post" action="/admin/urls/<?= (int)$redirect['id'] ?>/toggle" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm" style="background: <?= $redirect['is_active'] ? '#fef3c7' : '#d1fae5' ?>; color: <?= $redirect['is_active'] ? '#92400e' : '#065f46' ?>;">
                                        <?= $redirect['is_active'] ? 'Disable' : 'Enable' ?>
                                    </button>
                                </form>
                                <form method="post" action="/admin/urls/<?= (int)$redirect['id'] ?>/delete" onsubmit="return confirm('Delete this redirect?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.badge-info { background: #e0f2fe; color: #0369a1; }
.badge-muted { background: #e2e8f0; color: #64748b; }
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
