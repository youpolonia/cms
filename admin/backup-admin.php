<?php
require_once __DIR__ . '/../includes/auth/accesschecker.php';
require_once __DIR__ . '/../core/backupmanager.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

// Initialize dependencies
require_once __DIR__ . '/../core/database.php';
$db = \core\Database::connection();
$roleManager = new RoleManager($db);
$permissionManager = new PermissionManager($db);
$accessChecker = new AccessChecker($db, $roleManager, $permissionManager);

// Check admin access
cms_session_start('admin');
if (!$accessChecker->hasPermission($_SESSION['user_id'], 'backup_manage')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

csrf_boot('admin');

// Get tenant ID from session or configuration
$tenantId = $_SESSION['tenant_id'] ?? 1;
$backupManager = new BackupManager($db, $tenantId, $accessChecker);

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    try {
        if (isset($_POST['create_backup'])) {
            $backupName = $_POST['backup_name'];
            $format = $_POST['backup_format'];
            $result = $backupManager->createBackup($backupName, $format);
            $message = "Backup created successfully: " . basename($result['path']);
            $messageType = 'success';
        } elseif (isset($_POST['restore_backup'])) {
            $backupFile = $_POST['backup_file'];
            $result = $backupManager->restoreBackup($backupFile);
            $message = "Restored " . count($result['restored']) . " items from backup";
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get list of available backups
$backups = $backupManager->listBackups();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Management</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        .backup-container { max-width: 800px; margin: 20px auto; }
        .backup-form { background: #f5f5f5; padding: 20px; border-radius: 5px; }
        .backup-list { margin-top: 30px; }
        .backup-item { padding: 10px; border-bottom: 1px solid #ddd; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="backup-container">
        <h1>Backup Management</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>
        <div class="backup-form">
            <h2>Create New Backup</h2>
            <form method="POST">
                <?= csrf_field(); ?>
                <div>
                    <label for="backup_name">Backup Name:</label>
                    <input type="text" id="backup_name" name="backup_name" required 
                           pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, underscores and hyphens">                </div>
                <div>
                    <label for="backup_format">Format:</label>
                    <select id="backup_format" name="backup_format">
                        <option value="json">JSON</option>
                        <option value="zip">ZIP</option>
                    </select>
                </div>
                <button type="submit" name="create_backup">Create Backup</button>
            </form>
        </div>

        <div class="backup-form">
            <h2>Restore Backup</h2>
            <form method="POST">
                <?= csrf_field(); ?>
                <div>
                    <label for="backup_file">Select Backup:</label>
                    <select id="backup_file" name="backup_file" required>
                        <option value="">-- Select a backup --</option>
                        <?php foreach ($backups as $backup): ?>
                            <option value="<?= htmlspecialchars($backup['path']) ?>">
                                <?= htmlspecialchars($backup['name']) ?> (<?= round($backup['size'] / 1024, 2) ?> KB)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="restore_backup">Restore Backup</button>
            </form>
        </div>

        <div class="backup-list">
            <h2>Available Backups</h2>
            <?php if (empty($backups)): ?>
                <p>No backups available</p>
            <?php else: ?>                <?php foreach ($backups as $backup): ?>
                    <div class="backup-item">
                        <strong><?= htmlspecialchars($backup['name']) ?></strong>
                        <span>Size: <?= round($backup['size'] / 1024, 2) ?> KB</span>
                        <span>Modified: <?= $backup['modified'] ?></span>
                    </div>
                <?php endforeach; ?>            <?php endif; ?>
        </div>
    </div>
</body>
</html>
