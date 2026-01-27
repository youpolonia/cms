<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/export_package.php';
require_once CMS_ROOT . '/core/deploy_targets.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo '403 Forbidden - This feature is only available in development mode.';
    exit;
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatBytes($bytes) {
    if ($bytes === null) {
        return 'Unknown';
    }
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

$deployResult = null;
$targetsInfo = deploy_targets_load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'deploy_now') {
        csrf_validate_or_403();

        $targetKey = $_POST['target'] ?? '';
        if (empty($targetKey)) {
            $deployResult = [
                'ok' => false,
                'error' => 'No deploy target selected.'
            ];
        } else {
            $deployResult = deploy_package_to_target($targetKey);
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<style>
    .deploy-container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .deploy-container h1 { margin-bottom: 20px; color: #333; }
    .deploy-container .alert { padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid; }
    .deploy-container .alert-info { background: #d1ecf1; border-color: #0c5460; color: #0c5460; }
    .deploy-container .alert-success { background: #d4edda; border-color: #155724; color: #155724; }
    .deploy-container .alert-error { background: #f8d7da; border-color: #721c24; color: #721c24; }
    .deploy-container .alert-warning { background: #fff3cd; border-color: #856404; color: #856404; }
    .deploy-container .form-group { margin-bottom: 20px; }
    .deploy-container label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
    .deploy-container select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
    .deploy-container button { background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; }
    .deploy-container button:hover { background: #0056b3; }
    .deploy-container .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; font-weight: 500; }
    .deploy-container .back-link:hover { text-decoration: underline; }
    .deploy-container .result-detail { margin-top: 12px; padding: 12px; background: #f8f9fa; border-radius: 4px; }
    .deploy-container .result-detail strong { display: block; margin-bottom: 6px; color: #333; }
    .deploy-container .result-detail p { margin: 6px 0; color: #555; }
    .deploy-container .result-detail a { color: #007bff; word-break: break-all; }
</style>

<div class="admin-content">
    <div class="deploy-container">
        <a href="/admin/index.php" class="back-link">&larr; Back to Dashboard</a>

        <h1>One-Click Deploy</h1>

        <div class="alert alert-info">
            <p><strong>DEV_MODE Only Feature</strong></p>
            <p>This one-click deploy tool packages your CMS and deploys it directly to a configured FTP target.</p>
            <p>Deploy targets are configured in <code>config/deploy_targets.json</code>. You can start by copying <code>config/deploy_targets.json.example</code> and filling in your FTP credentials.</p>
        </div>

        <?php if ($deployResult !== null): ?>
            <?php if ($deployResult['ok']): ?>
                <div class="alert alert-success">
                    <strong>Deployment Successful!</strong>
                    <div class="result-detail">
                        <p><strong>Target:</strong> <?= esc($deployResult['target']) ?></p>
                        <p><strong>Remote Path:</strong> <?= esc($deployResult['remote_path']) ?></p>
                        <p><strong>Package Size:</strong> <?= esc(formatBytes($deployResult['size'])) ?></p>
                        <?php if ($deployResult['package_url']): ?>
                            <p><strong>Package URL:</strong> <a href="<?= esc($deployResult['package_url']) ?>" target="_blank"><?= esc($deployResult['package_url']) ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <strong>Deployment Failed</strong>
                    <p><?= esc($deployResult['error']) ?></p>
                    <?php if (isset($deployResult['target']) && $deployResult['target']): ?>
                        <p><strong>Target:</strong> <?= esc($deployResult['target']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($deployResult['package_url']) && $deployResult['package_url']): ?>
                        <div class="result-detail">
                            <p><strong>Package was built successfully:</strong></p>
                            <p><a href="<?= esc($deployResult['package_url']) ?>" target="_blank"><?= esc($deployResult['package_url']) ?></a></p>
                            <p><strong>Size:</strong> <?= esc(formatBytes($deployResult['size'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$targetsInfo['ok']): ?>
            <div class="alert alert-error">
                <strong>Configuration Error</strong>
                <p><?= esc($targetsInfo['error']) ?></p>
                <p>Check that <code>config/deploy_targets.json</code> exists and is valid JSON. You can use <code>config/deploy_targets.json.example</code> as a starting point.</p>
            </div>
        <?php elseif (empty($targetsInfo['targets'])): ?>
            <div class="alert alert-warning">
                <strong>No Deploy Targets Configured</strong>
                <p>No deploy targets are configured. Please create <code>config/deploy_targets.json</code>.</p>
                <p>Define at least one target in <code>config/deploy_targets.json</code>. Example structure: see <code>config/deploy_targets.json.example</code>.</p>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="deploy_now">

                <div class="form-group">
                    <label for="target">Select Deploy Target:</label>
                    <select name="target" id="target" required>
                        <option value="">-- Select a target --</option>
                        <?php foreach ($targetsInfo['targets'] as $key => $target): ?>
                            <option value="<?= esc($key) ?>">
                                <?= esc($target['label']) ?> (<?= esc($key) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Deploy Latest Package</button>
            </form>

            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #6c757d;">
                <p style="margin: 0; color: #495057; font-size: 14px;">
                    <strong>Note:</strong> This will build a fresh export package containing your <code>public/</code>, <code>core/</code>, <code>modules/</code>, and <code>config/</code> directories, then upload it to the selected FTP target.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
