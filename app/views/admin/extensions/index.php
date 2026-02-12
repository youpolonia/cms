<?php
$title = 'Extensions';
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
        <h2 class="card-title">Installed Extensions</h2>
    </div>

    <?php if (empty($extensions)): ?>
        <div class="card-body">
            <p style="color: var(--text-muted);">No extensions installed. Upload extensions to /extensions/ directory via FTP.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Extension <span class="tip"><span class="tip-text">Third-party add-on that extends CMS functionality.</span></span></th>
                    <th>Version</th>
                    <th>Author</th>
                    <th>Status <span class="tip"><span class="tip-text">Active extensions are loaded on every page.</span></span></th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($extensions as $ext): ?>
                    <tr>
                        <td>
                            <strong><?= esc($ext['name']) ?></strong>
                            <?php if (!$ext['dir_exists']): ?>
                                <span class="badge badge-danger" style="margin-left: 0.5rem;">Missing</span>
                            <?php endif; ?>
                            <?php if ($ext['description']): ?>
                                <br><small style="color: var(--text-muted);"><?= esc($ext['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= esc($ext['version']) ?></code></td>
                        <td>
                            <?php if ($ext['author_url']): ?>
                                <a href="<?= esc($ext['author_url']) ?>" target="_blank"><?= esc($ext['author']) ?></a>
                            <?php else: ?>
                                <?= esc($ext['author'] ?: '—') ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ext['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <form method="post" action="/admin/extensions/<?= (int)$ext['id'] ?>/toggle" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm" style="background: <?= $ext['is_active'] ? '#fef3c7' : '#d1fae5' ?>; color: <?= $ext['is_active'] ? '#92400e' : '#065f46' ?>;">
                                        <?= $ext['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                                <a href="/admin/extensions/<?= (int)$ext['id'] ?>/settings" class="btn btn-secondary btn-sm">Settings</a>
                                <form method="post" action="/admin/extensions/<?= (int)$ext['id'] ?>/uninstall" onsubmit="return confirm('Uninstall this extension?');" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">Uninstall</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if (!empty($available)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Available Extensions</h2>
        <span style="font-size: 0.875rem; color: var(--text-muted);">Found in /extensions/ directory</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Extension</th><span class="tip"><span class="tip-text">Third-party add-on extending CMS functionality.</span></span>
                <th>Version</th>
                <th>Author</th>
                <th style="width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($available as $ext): ?>
                <tr>
                    <td>
                        <strong><?= esc($ext['name']) ?></strong>
                        <?php if ($ext['description']): ?>
                            <br><small style="color: var(--text-muted);"><?= esc($ext['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><code><?= esc($ext['version']) ?></code></td>
                    <td><?= esc($ext['author'] ?: '—') ?></td>
                    <td>
                        <form method="post" action="/admin/extensions/install/">
                            <?= csrf_field() ?>
                            <input type="hidden" name="directory" value="<?= esc($ext['directory']) ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Install</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--text-muted);">
            <strong>How to add extensions:</strong> Upload extension folder to <code>/extensions/</code> via FTP. Each extension must contain an <code>extension.json</code> manifest file.
        </p>
    </div>
</div>

<style>
.badge-success { background: #d1fae5; color: #065f46; }
.badge-muted { background: #e2e8f0; color: #64748b; }
.badge-danger { background: #fee2e2; color: #991b1b; }
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
