<?php
/**
 * User Activity Log Viewer
 *
 * Displays audit trail of user actions (last 200 entries)
 */

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// Build log file path
$logPath = CMS_ROOT . '/logs/user_activity.log';

// Read log entries
$entries = [];
if (file_exists($logPath) && is_readable($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        // Get last 200 lines
        $lines = array_slice($lines, -200);

        // Parse JSONL
        foreach (array_reverse($lines) as $line) {
            $entry = json_decode($line, true);
            if ($entry !== null && is_array($entry)) {
                $entries[] = $entry;
            }
        }
    }
}

// Helper function for safe output
function esc($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Helper to format details
function formatDetails($details) {
    if (empty($details) || !is_array($details)) {
        return '-';
    }

    $parts = [];
    foreach ($details as $key => $value) {
        if (is_scalar($value)) {
            $parts[] = esc($key) . ': ' . esc($value);
        }
    }

    return implode(', ', $parts) ?: '-';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Log - CMS Admin</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
    <style>
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .activity-table th {
            background: #f5f5f5;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        .activity-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        .activity-table tr:hover {
            background: #f9f9f9;
        }
        .action-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .action-create { background: #e3f2fd; color: #1976d2; }
        .action-update { background: #fff3e0; color: #f57c00; }
        .action-delete { background: #ffebee; color: #c62828; }
        .action-reset { background: #f3e5f5; color: #7b1fa2; }
        .action-profile { background: #e8f5e9; color: #388e3c; }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
    <?php require_once CMS_ROOT . '/admin/includes/navigation.php'; ?>

    <div class="admin-container">
        <div class="dashboard-card">
            <div class="card-header">
                <h2>User Activity Log</h2>
                <p>Audit trail of user actions (last 200 entries)</p>
            </div>

            <?php if (empty($entries)): ?>
                <div class="empty-state">
                    <p>No activity logged yet.</p>
                </div>
            <?php else: ?>
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <?php
                                $action = $entry['action'] ?? 'unknown';
                                $actionClass = 'action-badge';
                                if (strpos($action, 'create') !== false) {
                                    $actionClass .= ' action-create';
                                } elseif (strpos($action, 'update') !== false) {
                                    $actionClass .= ' action-update';
                                } elseif (strpos($action, 'delete') !== false) {
                                    $actionClass .= ' action-delete';
                                } elseif (strpos($action, 'reset') !== false || strpos($action, 'password') !== false) {
                                    $actionClass .= ' action-reset';
                                } elseif (strpos($action, 'profile') !== false) {
                                    $actionClass .= ' action-profile';
                                }
                            ?>
                            <tr>
                                <td><?= esc($entry['ts'] ?? '') ?></td>
                                <td>
                                    <?= esc($entry['username'] ?? 'unknown') ?>
                                    <?php if (isset($entry['user_id'])): ?>
                                        <small style="color: #666;">(ID: <?= esc($entry['user_id']) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?= $actionClass ?>">
                                        <?= esc($action) ?>
                                    </span>
                                </td>
                                <td><?= esc($entry['ip'] ?? 'unknown') ?></td>
                                <td><?= formatDetails($entry['details'] ?? []) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
