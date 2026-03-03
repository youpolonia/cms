<?php
/**
 * Plugin Settings Page — loads plugin's own settings view
 * Variables: $plugin (manifest), $slug, $settingsFile, $success, $error
 */
ob_start();
?>
<div class="admin-page">
    <div class="page-header" style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
        <a href="/admin/plugins" class="btn btn-ghost" style="padding:6px 12px;">← Back</a>
        <div>
            <h1 style="margin:0;font-size:1.5rem;">
                <?= h($plugin['name'] ?? $slug) ?> — Settings
            </h1>
            <?php if (!empty($plugin['version'])): ?>
                <span style="color:#64748b;font-size:0.85rem;">v<?= h($plugin['version']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:6px;margin-bottom:16px;">
            <?= h($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:6px;margin-bottom:16px;">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <div class="plugin-settings-content" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
        <form method="POST" action="/admin/plugins/<?= h($slug) ?>/settings">
            <?= csrf_field() ?>
            <?php
            // Include the plugin's own settings form
            if (isset($settingsFile) && file_exists($settingsFile)) {
                require $settingsFile;
            } else {
                echo '<p style="color:#64748b;">This plugin has no configurable settings.</p>';
            }
            ?>
            <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;">
                <button type="submit" class="btn btn-primary" style="background:#2563eb;color:#fff;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;font-size:0.95rem;">
                    💾 Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
