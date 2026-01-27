<?php
/**
 * Plugins Management View
 * @var array $installed - installed plugins
 * @var array $available - available plugins from /plugins folder
 * @var string $pluginsDir - path to plugins directory
 * @var string|null $success - success message
 * @var string|null $error - error message
 */

$title = 'Plugins';
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin'],
    ['label' => 'Plugins']
];

ob_start();
?>

<style>
.plugins-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.plugins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.plugin-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
}

.plugin-card:hover {
    border-color: var(--accent);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.plugin-card.installed {
    border-left: 3px solid var(--success);
}

.plugin-card.disabled {
    opacity: 0.6;
    border-left: 3px solid var(--warning);
}

.plugin-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.plugin-icon {
    width: 48px;
    height: 48px;
    background: var(--bg-tertiary);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.plugin-info {
    flex: 1;
}

.plugin-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: var(--text-primary);
}

.plugin-version {
    font-size: 12px;
    color: var(--text-secondary);
}

.plugin-description {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 16px;
    line-height: 1.5;
}

.plugin-meta {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: var(--text-muted);
    margin-bottom: 16px;
}

.plugin-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.plugin-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.plugin-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
}

.plugin-status.active {
    background: rgba(34, 197, 94, 0.1);
    color: var(--success);
}

.plugin-status.inactive {
    background: rgba(234, 179, 8, 0.1);
    color: var(--warning);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.empty-state h3 {
    margin-bottom: 8px;
    color: var(--text-primary);
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<div class="plugins-header">
    <div>
        <h1>Plugins</h1>
        <p class="text-muted">Manage your CMS plugins</p>
    </div>
    <div class="header-actions">
        <a href="/admin/plugins/upload" class="btn btn-primary">
            <span>📦</span> Upload Plugin
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars(is_array($success) ? implode(', ', $success) : (string)$success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : (string)$error) ?></div>
<?php endif; ?>

<!-- Installed Plugins -->
<?php if (!empty($installed)): ?>
<div class="section-title">Installed Plugins (<?= count($installed) ?>)</div>
<div class="plugins-grid" style="margin-bottom: 40px;">
    <?php foreach ($installed as $slug => $plugin): 
        $manifest = $available[$slug] ?? [];
        $isEnabled = $plugin['enabled'] ?? true;
    ?>
    <div class="plugin-card installed <?= $isEnabled ? '' : 'disabled' ?>">
        <div class="plugin-header">
            <div class="plugin-icon"><?= $manifest['icon'] ?? '🔌' ?></div>
            <div class="plugin-info">
                <h3 class="plugin-name"><?= htmlspecialchars($plugin['name'] ?? $slug) ?></h3>
                <span class="plugin-version">v<?= htmlspecialchars($plugin['version'] ?? '1.0.0') ?></span>
            </div>
            <span class="plugin-status <?= $isEnabled ? 'active' : 'inactive' ?>">
                <?= $isEnabled ? '● Active' : '○ Inactive' ?>
            </span>
        </div>
        
        <p class="plugin-description"><?= htmlspecialchars($manifest['description'] ?? 'No description available') ?></p>
        
        <div class="plugin-meta">
            <span>📅 <?= htmlspecialchars($plugin['installed_at'] ?? 'Unknown') ?></span>
            <?php if (isset($manifest['author'])): ?>
                <span>👤 <?= htmlspecialchars($manifest['author']) ?></span>
            <?php endif; ?>
        </div>
        
        <div class="plugin-actions">
            <form method="post" action="/admin/plugins/0/toggle" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="plugin" value="<?= htmlspecialchars($slug) ?>">
                <button type="submit" class="btn btn-sm <?= $isEnabled ? 'btn-warning' : 'btn-success' ?>">
                    <?= $isEnabled ? '⏸️ Disable' : '▶️ Enable' ?>
                </button>
            </form>
            
            <?php if (file_exists($pluginsDir . '/' . $slug . '/settings.php')): ?>
                <a href="/admin/plugins/<?= urlencode($slug) ?>/settings" class="btn btn-sm btn-secondary">
                    ⚙️ Settings
                </a>
            <?php endif; ?>
            
            <form method="post" action="/admin/plugins/0/uninstall" style="display:inline" 
                  onsubmit="return confirm('Are you sure you want to uninstall this plugin?')">
                <?= csrf_field() ?>
                <input type="hidden" name="plugin" value="<?= htmlspecialchars($slug) ?>">
                <button type="submit" class="btn btn-sm btn-danger">🗑️ Uninstall</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Available Plugins -->
<?php 
$notInstalled = array_diff_key($available, $installed);
if (!empty($notInstalled)): 
?>
<div class="section-title">Available Plugins (<?= count($notInstalled) ?>)</div>
<div class="plugins-grid">
    <?php foreach ($notInstalled as $slug => $manifest): ?>
    <div class="plugin-card">
        <div class="plugin-header">
            <div class="plugin-icon"><?= $manifest['icon'] ?? '📦' ?></div>
            <div class="plugin-info">
                <h3 class="plugin-name"><?= htmlspecialchars($manifest['name'] ?? $slug) ?></h3>
                <span class="plugin-version">v<?= htmlspecialchars($manifest['version'] ?? '1.0.0') ?></span>
            </div>
        </div>
        
        <p class="plugin-description"><?= htmlspecialchars($manifest['description'] ?? 'No description available') ?></p>
        
        <div class="plugin-meta">
            <?php if (isset($manifest['author'])): ?>
                <span>👤 <?= htmlspecialchars($manifest['author']) ?></span>
            <?php endif; ?>
            <?php if (isset($manifest['requires'])): ?>
                <span>📋 Requires: <?= htmlspecialchars(is_array($manifest['requires']) ? 'PHP ' . ($manifest['requires']['php'] ?? '8.1+') : (string)$manifest['requires']) ?></span>
            <?php endif; ?>
        </div>
        
        <div class="plugin-actions">
            <form method="post" action="/admin/plugins/install">
                <?= csrf_field() ?>
                <input type="hidden" name="plugin" value="<?= htmlspecialchars($slug) ?>">
                <button type="submit" class="btn btn-sm btn-primary">📥 Install</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($installed) && empty($available)): ?>
<div class="empty-state">
    <h3>No Plugins Found</h3>
    <p>Upload your first plugin or create one in the <code>/plugins</code> directory.</p>
    <a href="/admin/plugins/upload" class="btn btn-primary" style="margin-top: 16px;">
        📦 Upload Plugin
    </a>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
