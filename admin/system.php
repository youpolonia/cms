<?php
// admin/system.php - System Information
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/navigation.php';

// Verify admin access
verifyAdminAccess();

// Set page title
$pageTitle = 'System Information';

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


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Admin Panel</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>System Information</h1>
            <div class="admin-user">
                <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                <a href="/admin/logout.php" class="logout">Logout</a>
            </div>
        </header>

        <?php renderAdminNavigation('system'); 

?><main class="admin-content">
            <div class="system-info">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th colspan="2">System Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CMS Version</td>
                            <td><?= defined('CMS_VERSION') ? CMS_VERSION : 'Not defined' ?></td>
                        </tr>
                        <tr>
                            <td>PHP Version</td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td>Memory Limit</td>
                            <td><?= ini_get('memory_limit') ?></td>
                        </tr>
                        <tr>
                            <td>Max Execution Time</td>
                            <td><?= ini_get('max_execution_time') ?> seconds</td>
                        </tr>
                        <tr>
                            <td>DEV_MODE Status</td>
                            <td><?= defined('DEV_MODE') ? (DEV_MODE ? 'Enabled' : 'Disabled') : 'Not defined' ?></td>
                        </tr>
                        <tr>
                            <td>Maintenance Mode</td>
                            <td><?= $maintenanceMode ? 'Enabled' : 'Disabled' ?></td>
                        </tr>
                        <tr>
                            <td>Cache Writable</td>
                            <td><?= $cacheWritable ? 'Yes' : 'No' ?></td>
                        </tr>
                        <tr>
                            <td>Last Error</td>
                            <td><?= htmlspecialchars($lastError) ?></td>
                        </tr>
                        <tr>
                            <td>Database Connection</td>
                            <td><?= $dbConnected ? 'Connected' : 'Failed' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="admin-footer">
            <p>&copy; <?= date('Y') ?> CMS Admin Panel | <?= date('Y-m-d H:i:s') ?></p>
        </footer>
    </div>
</body>
</html>
