<?php
$title = esc($module['name']) . ' - Module Details';
ob_start();

function formatBytes($bytes, $precision = 1) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, $precision) . ' KB';
    return round($bytes / 1048576, $precision) . ' MB';
}

function getFileIcon($type) {
    $icons = [
        'php' => '&#128196;',
        'js' => '&#128312;',
        'css' => '&#127912;',
        'json' => '&#128203;',
        'md' => '&#128220;',
        'txt' => '&#128221;',
        'html' => '&#127760;',
        'vue' => '&#128154;',
        'sql' => '&#128451;',
    ];
    return $icons[$type] ?? '&#128196;';
}
?>

<div style="margin-bottom: 1.5rem;">
    <a href="/admin/modules" class="btn btn-secondary btn-sm">&larr; Back to Modules</a>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
    <div class="stat-card">
        <div class="stat-value" style="font-size: 20px;"><?= esc($module['version']) ?></div>
        <div class="stat-label">Version</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size: 20px;"><?= number_format($module['file_count']) ?></div>
        <div class="stat-label">Files</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size: 20px;"><?= formatBytes($module['size']) ?></div>
        <div class="stat-label">Size</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size: 20px;">
            <?php if ($module['is_core']): ?>
                <span style="color: var(--color-danger);">&#128274;</span>
            <?php elseif ($module['is_enabled']): ?>
                <span style="color: var(--color-success);">&#10003;</span>
            <?php else: ?>
                <span style="color: var(--color-warning);">&#9888;</span>
            <?php endif; ?>
        </div>
        <div class="stat-label">
            <?php if ($module['is_core']): ?>Core Module<?php elseif ($module['is_enabled']): ?>Active<?php else: ?>Disabled<?php endif; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem;">
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Module Information</h2>
                <?php if (!$module['is_core']): ?>
                    <form method="post" action="/admin/modules/<?= esc($module['slug']) ?>/toggle">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm <?= $module['is_enabled'] ? 'btn-danger' : 'btn-primary' ?>">
                            <?= $module['is_enabled'] ? 'Disable Module' : 'Enable Module' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 8px 0; width: 140px; color: var(--color-text-muted);">Name</td>
                        <td style="padding: 8px 0;"><strong><?= esc($module['name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Slug</td>
                        <td style="padding: 8px 0;"><code><?= esc($module['slug']) ?></code></td>
                    </tr>
                    <?php if ($module['description']): ?>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Description</td>
                        <td style="padding: 8px 0;"><?= esc($module['description']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($module['author']): ?>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Author</td>
                        <td style="padding: 8px 0;"><?= esc($module['author']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($module['license']): ?>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">License</td>
                        <td style="padding: 8px 0;"><?= esc($module['license']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Location</td>
                        <td style="padding: 8px 0;"><code style="font-size: 12px;">/modules/<?= esc($module['slug']) ?>/</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Last Modified</td>
                        <td style="padding: 8px 0;"><?= esc($module['modified_at']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--color-text-muted);">Manifest</td>
                        <td style="padding: 8px 0;">
                            <?php if ($module['has_manifest']): ?>
                                <span class="badge badge-success">Present</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($module['routes'])): ?>
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h2 class="card-title">Routes</h2>
            </div>
            <div class="card-body">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($module['routes'] as $route): ?>
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--color-border-light);">
                            <?php if (is_string($route)): ?>
                                <code><?= esc($route) ?></code>
                            <?php elseif (is_array($route) && isset($route['path'])): ?>
                                <span class="badge badge-default" style="font-size: 10px; margin-right: 8px;">
                                    <?= esc($route['method'] ?? 'GET') ?>
                                </span>
                                <code><?= esc($route['path']) ?></code>
                                <?php if (isset($route['handler'])): ?>
                                    <small style="color: var(--color-text-muted); margin-left: 8px;">
                                        &rarr; <?= esc($route['handler']) ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($module['hooks'])): ?>
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h2 class="card-title">Hooks</h2>
            </div>
            <div class="card-body">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($module['hooks'] as $hook): ?>
                        <li style="padding: 8px 0; border-bottom: 1px solid var(--color-border-light);">
                            <?php if (is_array($hook) && isset($hook['name'])): ?>
                                <code><?= esc($hook['name']) ?></code>
                                <?php if (isset($hook['callback'])): ?>
                                    <small style="color: var(--color-text-muted); margin-left: 8px;">
                                        &rarr; <?= esc($hook['callback']) ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <code><?= esc(is_string($hook) ? $hook : json_encode($hook)) ?></code>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h2 class="card-title">Files (<?= count($files) ?>)</h2>
            </div>
            <?php if (empty($files)): ?>
                <div class="card-body">
                    <p style="color: var(--color-text-muted); margin: 0;">No files found.</p>
                </div>
            <?php else: ?>
                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th style="width: 80px;">Type</th>
                                <th style="width: 80px;">Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td>
                                    <span style="margin-right: 6px;"><?= getFileIcon($file['type']) ?></span>
                                    <code style="font-size: 12px;"><?= esc($file['path']) ?></code>
                                </td>
                                <td>
                                    <span class="badge badge-default" style="font-size: 10px;">
                                        <?= esc(strtoupper($file['type'] ?: 'FILE')) ?>
                                    </span>
                                </td>
                                <td style="color: var(--color-text-muted); font-size: 12px;">
                                    <?= formatBytes($file['size']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dependencies</h2>
            </div>
            <div class="card-body">
                <?php if (empty($dependencies['required']) && empty($dependencies['missing'])): ?>
                    <p style="color: var(--color-text-muted); margin: 0; font-size: 14px;">
                        No dependencies declared.
                    </p>
                <?php else: ?>
                    <?php if (!empty($dependencies['required'])): ?>
                        <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--color-text-secondary);">Required</h4>
                        <ul style="list-style: none; padding: 0; margin: 0 0 1rem 0;">
                            <?php foreach ($dependencies['required'] as $dep): ?>
                            <li style="padding: 6px 0; display: flex; align-items: center; justify-content: space-between;">
                                <span>
                                    <code style="font-size: 12px;"><?= esc($dep['name']) ?></code>
                                    <small style="color: var(--color-text-muted);"><?= esc($dep['version']) ?></small>
                                </span>
                                <?php if ($dep['enabled']): ?>
                                    <span class="badge badge-success" style="font-size: 10px;">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="font-size: 10px;">Disabled</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($dependencies['missing'])): ?>
                        <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--color-danger);">Missing</h4>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($dependencies['missing'] as $dep): ?>
                            <li style="padding: 6px 0; display: flex; align-items: center; justify-content: space-between;">
                                <span>
                                    <code style="font-size: 12px;"><?= esc($dep['name']) ?></code>
                                    <small style="color: var(--color-text-muted);"><?= esc($dep['version']) ?></small>
                                </span>
                                <span class="badge badge-danger" style="font-size: 10px;">Not Found</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($module['autoload'])): ?>
        <div class="card" style="margin-top: 1rem;">
            <div class="card-header">
                <h2 class="card-title">Autoload</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($module['autoload']['files'])): ?>
                    <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--color-text-secondary);">Files</h4>
                    <ul style="list-style: none; padding: 0; margin: 0 0 1rem 0;">
                        <?php foreach ((array)$module['autoload']['files'] as $file): ?>
                        <li style="padding: 4px 0;">
                            <code style="font-size: 11px;"><?= esc($file) ?></code>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($module['autoload']['classes'])): ?>
                    <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--color-text-secondary);">Classes</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ((array)$module['autoload']['classes'] as $class => $path): ?>
                        <li style="padding: 4px 0;">
                            <code style="font-size: 11px;"><?= esc($class) ?></code>
                            <br>
                            <small style="color: var(--color-text-muted);">&rarr; <?= esc($path) ?></small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 1rem;">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <?php if (!$module['is_core']): ?>
                    <form method="post" action="/admin/modules/<?= esc($module['slug']) ?>/toggle">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn <?= $module['is_enabled'] ? 'btn-danger' : 'btn-primary' ?>" style="width: 100%;">
                            <?= $module['is_enabled'] ? 'Disable Module' : 'Enable Module' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" style="width: 100%;" disabled>
                        Core Module (Always Active)
                    </button>
                <?php endif; ?>
                <a href="/admin/modules" class="btn btn-secondary" style="width: 100%;">
                    &larr; Back to Modules
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
