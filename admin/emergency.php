<?php
/**
 * Emergency Mode Control Panel
 * Only accessible by super admins
 */

require_once __DIR__.'/../includes/security/emergency_mode.php';
require_once __DIR__.'/../core/security/Auth.php';
require_once __DIR__.'/../core/csrf.php';

csrf_boot();


require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
// Verify super admin access
if (!\core\Security\Auth::isSuperAdmin()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$action = $_POST['action'] ?? '';
$message = '';
$isEmergency = isEmergencyModeActive();

// Handle form submission
if ($action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        if ($action === 'enable' && !$isEmergency) {
            if (enableEmergencyMode()) {
                $message = 'Emergency mode activated successfully';
                error_log("[Emergency] Enabled by admin at " . date('Y-m-d H:i:s'), 3, __DIR__.'/../logs/security.log');
            } else {
                throw new Exception('Failed to activate emergency mode');
            }
        } elseif ($action === 'disable' && $isEmergency) {
            if (disableEmergencyMode()) {
                $message = 'Emergency mode deactivated successfully';
                error_log("[Emergency] Disabled by admin at " . date('Y-m-d H:i:s'), 3, __DIR__.'/../logs/security.log');
            } else {
                throw new Exception('Failed to deactivate emergency mode');
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = 'Database error';
        error_log("[Emergency] Error: " . $e->getMessage(), 3, __DIR__.'/../logs/security.log');
    }
}

// Render admin interface
?><!DOCTYPE html>
<html>
<head>
    <title>Emergency Mode Control</title>
    <link rel="stylesheet" href="/admin/assets/css/main.css">
</head>
<body>
    <div class="admin-container">
        <h1>Emergency Mode Control</h1>
        
        <?php if ($message): ?>
        <div class="alert alert-<?= strpos($message, 'Error') !== false ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($message) 
?>        </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <p>Current status: <strong><?= $isEmergency ? 'ACTIVE' : 'INACTIVE' ?></strong></p>
                <?php if ($isEmergency): ?>
                    <p>Activated at: <?= date('Y-m-d H:i:s', getEmergencyModeActivationTime()) ?></p>
                <?php endif; ?>
                <form method="post">
                    <?= csrf_field(); ?>
                    <?php if (!$isEmergency): ?>
                        <button type="submit" name="action" value="enable" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to activate emergency mode?')">
                            Activate Emergency Mode
                        </button>
                    <?php else: ?>
                        <button type="submit" name="action" value="disable" class="btn btn-success"
                            onclick="return confirm('Are you sure you want to deactivate emergency mode?')">
                            Deactivate Emergency Mode
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
