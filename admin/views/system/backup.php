<?php
require_once __DIR__ . '/../../admin_header.php';
require_once __DIR__ . '/../../../core/csrf.php';

// Check admin authorization
if (!Auth::hasAdminAccess()) {
    header('Location: /admin/login.php');
    exit;
}

$backupManager = new BackupManager('/var/www/html/cms/backups/');
$messages = [];
$downloadLinks = [];

// Handle backup actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        switch ($_POST['action']) {
            case 'export_settings':
                $file = $backupManager->exportSettings();
                if ($file) {
                    $messages[] = ['type' => 'success', 'text' => 'Settings exported successfully'];
                    $downloadLinks[] = ['name' => 'Settings Export', 'path' => $file];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Failed to export settings'];
                }
                break;
                
            case 'export_content':
                $file = $backupManager->exportContent();
                if ($file) {
                    $messages[] = ['type' => 'success', 'text' => 'Content exported successfully'];
                    $downloadLinks[] = ['name' => 'Content Export', 'path' => $file];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Failed to export content'];
                }
                break;

            case 'full_backup':
                $file = $backupManager->generateTimestampedBackup();
                if ($file) {
                    $messages[] = ['type' => 'success', 'text' => 'Full backup created successfully'];
                    $downloadLinks[] = ['name' => 'Full Backup', 'path' => $file];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Failed to create full backup'];
                }
                break;
        }
    } catch (Exception $e) {
        $messages[] = ['type' => 'error', 'text' => 'Backup error: ' . $e->getMessage()];
    }
}

require_once __DIR__ . '/../../admin_footer.php';
require_once __DIR__ . '/../layout.php';

?><div class="admin-container">
    <h1>System Backups</h1>
    
    <?php foreach ($messages as $message): ?>
        <div class="alert alert-<?= $message['type'] ?>">
            <?= htmlspecialchars($message['text'])  ?>
        </div>
    <?php endforeach;  ?>
    <div class="backup-actions">
        <form method="post" class="backup-form">
            <?= csrf_field();  ?>
            <input type="hidden" name="action" value="export_settings">
            <button type="submit" class="btn btn-primary">
                <span class="btn-text">Export Settings</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>
        
        <form method="post" class="backup-form">
            <?= csrf_field();  ?>
            <input type="hidden" name="action" value="export_content">
            <button type="submit" class="btn btn-primary">
                <span class="btn-text">Export Content</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>
        
        <form method="post" class="backup-form">
            <?= csrf_field();  ?>
            <input type="hidden" name="action" value="full_backup">
            <button type="submit" class="btn btn-warning">
                <span class="btn-text">Full Backup</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>
    </div>
    
    <?php if (!empty($downloadLinks)): ?>
        <div class="download-section">
            <h3>Download Backups</h3>
            <ul class="download-list">
                <?php foreach ($downloadLinks as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars($link['path']) ?>" download>
                            <?= htmlspecialchars($link['name'])  ?>
                        </a>
                    </li>
                <?php endforeach;  ?>
            </ul>
        </div>
    <?php endif;  ?>
</div>

<script>
document.querySelectorAll('.backup-form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.spinner-border').classList.remove('d-none');
    });
});
</script>
