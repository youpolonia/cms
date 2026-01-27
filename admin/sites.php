<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/core/sites.php';
cms_session_start('admin');
csrf_boot('admin');
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}
cms_require_admin_role();
$config = sites_config_load();
$sites = sites_get_all();
$defaultSite = sites_get_default();
$currentHost = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $currentHost = (string)$_SERVER['HTTP_HOST'];
} elseif (isset($_SERVER['SERVER_NAME'])) {
    $currentHost = (string)$_SERVER['SERVER_NAME'];
}
$currentSite = sites_resolve_current();
require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>
<div class="container" style="margin:24px auto;max-width:1200px">
    <h1>Sites</h1>
    <p style="margin-bottom:24px;color:#666">
        This is the registry for multi-tenant and multi-domain support. Currently read-only.
    </p>
    <?php if (count($sites) === 0): ?>
        <div style="padding:12px 16px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;margin-bottom:24px">
            <strong>Warning:</strong> No sites defined in config/sites.json.
        </div>
    <?php else: ?>
        <div style="padding:12px 16px;background:#d1ecf1;border:1px solid #17a2b8;border-radius:4px;margin-bottom:24px">
            <div><strong>Total sites:</strong> <?= count($sites) ?></div>
            <?php if ($defaultSite !== null): ?>
                <div><strong>Default site:</strong> <?= esc($defaultSite['id']) ?> (<?= esc($defaultSite['name']) ?>)</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div style="padding:12px 16px;background:#e7f3ff;border:1px solid #0056b3;border-radius:4px;margin-bottom:24px">
        <div style="font-weight:600;margin-bottom:8px">Current Request</div>
        <div><strong>Host:</strong> <?= $currentHost !== '' ? esc($currentHost) : 'unknown' ?></div>
        <?php if ($currentSite !== null): ?>
            <div><strong>Matched site ID:</strong> <?= esc($currentSite['id']) ?></div>
            <div><strong>Matched site name:</strong> <?= esc($currentSite['name']) ?></div>
        <?php else: ?>
            <div style="color:#666;margin-top:4px">No matching site resolved for this host.</div>
        <?php endif; ?>
    </div>
    <table style="width:100%;border-collapse:collapse;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1)">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:2px solid #dee2e6">
                <th style="padding:12px;text-align:left;font-weight:600">ID</th>
                <th style="padding:12px;text-align:left;font-weight:600">Name</th>
                <th style="padding:12px;text-align:left;font-weight:600">Domain</th>
                <th style="padding:12px;text-align:left;font-weight:600">Locale</th>
                <th style="padding:12px;text-align:left;font-weight:600">Active</th>
                <th style="padding:12px;text-align:left;font-weight:600">Default</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sites) === 0): ?>
                <tr>
                    <td colspan="6" style="padding:24px;text-align:center;color:#999">No sites defined.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($sites as $site): ?>
                    <tr style="border-bottom:1px solid #dee2e6">
                        <td style="padding:12px"><?= esc($site['id']) ?></td>
                        <td style="padding:12px"><?= esc($site['name']) ?></td>
                        <td style="padding:12px"><?= esc($site['domain']) ?></td>
                        <td style="padding:12px"><?= esc($site['locale']) ?></td>
                        <td style="padding:12px"><?= $site['active'] ? 'Yes' : 'No' ?></td>
                        <td style="padding:12px"><?= ($defaultSite !== null && $defaultSite['id'] === $site['id']) ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
