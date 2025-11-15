<?php
/**
 * Deployment System Admin Interface
 */
require_once __DIR__ . '/../../includes/deployment/ftpmanager.php';
require_once __DIR__ . '/../../includes/deployment/versionmanager.php';
require_once __DIR__ . '/../../includes/deployment/environmentmanager.php';

$ftpManager = new CMS\Deployment\FTPManager();
$versionManager = new CMS\Deployment\VersionManager();
$envManager = new CMS\Deployment\EnvironmentManager();

// Get current deployment status
$versions = $versionManager->getAllVersions();
$currentVersion = $versionManager->getCurrentVersion();
$ftpStatus = $ftpManager->testConnection();
$envStatus = $envManager->validateRequired(['DB_HOST', 'DB_NAME', 'DB_USER']);

?><div class="deployment-container">
    <h2>Deployment System</h2>
    
    <div class="status-panel">
        <div class="status-card <?= $ftpStatus ? 'success' : 'error' ?>">
            <h3>FTP Connection</h3>
            <p><?= $ftpStatus ? 'Connected' : 'Disconnected' ?></p>
        </div>
        
        <div class="status-card <?= empty($envStatus) ? 'success' : 'error' ?>">
            <h3>Environment</h3>
            <p><?= empty($envStatus) ? 'Valid' : 'Missing: ' . implode(', ', $envStatus) ?></p>
        </div>
        
        <div class="status-card info">
            <h3>Current Version</h3>
            <p><?= htmlspecialchars($currentVersion['version'] ?? 'N/A') ?></p>
        </div>
    </div>

    <div class="deployment-actions">
        <section class="version-control">
            <h3>Version Management</h3>
            <div class="version-list">
                <?php foreach ($versions as $version): ?>
                <div class="version-item <?= $version['is_active'] ? 'active' : '' ?>">
                    <span class="version-id"><?= htmlspecialchars($version['version']) ?></span>
                    <span class="version-date"><?= date('Y-m-d H:i', strtotime($version['created_at'])) ?></span>
                    <button class="activate-btn" data-version="<?= htmlspecialchars($version['version']) ?>">
                        <?= $version['is_active'] ? 'Active' : 'Activate' 
?>                    </button>
                </div>
                <?php endforeach;  ?>
            </div>
            <button id="create-version-btn" class="primary-btn">Create New Version</button>
        </section>

        <section class="ftp-settings">
            <h3>FTP Configuration</h3>
            <form id="ftp-config-form">
                <div class="form-group">
                    <label>Host</label>
                    <input type="text" name="host" value="<?= htmlspecialchars($ftpManager->getConfig('host')) ?>">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($ftpManager->getConfig('username')) ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="">
                </div>
                <div class="form-group">
                    <label>Port</label>
                    <input type="number" name="port" value="<?= htmlspecialchars($ftpManager->getConfig('port') ?? '21') ?>">
                </div>
                <button type="submit" class="primary-btn">Save Configuration</button>
            </form>
        </section>
    </div>

    <div class="deployment-log">
        <h3>Deployment Log</h3>
        <div class="log-entries">
            <!-- Log entries will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load deployment.js
    const script = document.createElement('script');
    script.src = '/admin/js/deployment.js';
    document.head.appendChild(script);
});
</script>
