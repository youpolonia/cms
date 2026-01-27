<?php
// admin/system.php - System Information
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/includes/security.php';

// Verify admin access
verifyAdminAccess();

// Check maintenance mode
$maintenanceMode = file_exists(__DIR__.'/../../maintenance.flag');

// Check cache writable
$cacheWritable = is_writable(__DIR__.'/../../cache/');

// Get last error if exists
$lastError = '';
$errorLogPath = __DIR__.'/../../logs/error.log';
if (file_exists($errorLogPath)) {
    $errorLog = file($errorLogPath, FILE_IGNORE_NEW_LINES);
    $lastError = end($errorLog) ?: 'No errors logged';
}

// Test DB connection
$dbConnected = false;
try {
    $pdo = \core\Database::connection();
    $dbConnected = true;
} catch (PDOException $e) {
    error_log($e->getMessage());
    $dbConnected = false;
}

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Information - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.card-title{font-size:15px;font-weight:600}
.card-body{padding:0}
table{width:100%;border-collapse:collapse}
th,td{padding:14px 20px;text-align:left;border-bottom:1px solid var(--border)}
th{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg)}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(137,180,250,.03)}
.status{display:inline-flex;padding:4px 12px;border-radius:6px;font-size:12px;font-weight:500}
.status-success{background:rgba(166,227,161,.2);color:var(--success)}
.status-danger{background:rgba(243,139,168,.2);color:var(--danger)}
.status-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.error-log{font-family:monospace;font-size:12px;background:var(--bg);padding:10px 14px;border-radius:8px;word-break:break-all;max-width:400px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🖥️',
    'title' => 'System Information',
    'description' => 'Server and CMS status overview',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--purple), var(--danger-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="card">
<div class="card-head"><span>📊</span><span class="card-title">System Status</span></div>
<div class="card-body">
<table>
<tbody>
<tr>
    <td>CMS Version</td>
    <td><?= defined('CMS_VERSION') ? esc(CMS_VERSION) : '<span class="status status-warning">Not defined</span>' ?></td>
</tr>
<tr>
    <td>PHP Version</td>
    <td><?= esc(phpversion()) ?></td>
</tr>
<tr>
    <td>Memory Limit</td>
    <td><?= esc(ini_get('memory_limit')) ?></td>
</tr>
<tr>
    <td>Max Execution Time</td>
    <td><?= esc(ini_get('max_execution_time')) ?> seconds</td>
</tr>
<tr>
    <td>DEV_MODE Status</td>
    <td>
        <?php if (defined('DEV_MODE')): ?>
            <span class="status <?= DEV_MODE ? 'status-warning' : 'status-success' ?>"><?= DEV_MODE ? 'Enabled' : 'Disabled' ?></span>
        <?php else: ?>
            <span class="status status-warning">Not defined</span>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td>Maintenance Mode</td>
    <td><span class="status <?= $maintenanceMode ? 'status-warning' : 'status-success' ?>"><?= $maintenanceMode ? 'Enabled' : 'Disabled' ?></span></td>
</tr>
<tr>
    <td>Cache Writable</td>
    <td><span class="status <?= $cacheWritable ? 'status-success' : 'status-danger' ?>"><?= $cacheWritable ? 'Yes' : 'No' ?></span></td>
</tr>
<tr>
    <td>Database Connection</td>
    <td><span class="status <?= $dbConnected ? 'status-success' : 'status-danger' ?>"><?= $dbConnected ? 'Connected' : 'Failed' ?></span></td>
</tr>
<tr>
    <td>Last Error</td>
    <td><div class="error-log"><?= esc($lastError) ?></div></td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
</body>
</html>
