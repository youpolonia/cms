<?php
/**
 * System Monitor Dashboard
 * 
 * Displays system diagnostic information with color-coded status indicators
 */

require_once __DIR__ . '/../../../monitor/system_status_helpers.php';

// Establish database connection (assuming this is available in admin context)
$db = $GLOBALS['db_connection'] ?? null;

// Get all diagnostic data
$phpStatus = check_php_version();
$dbStatus = check_db_connection($db);
$schedulerStatus = $db ? get_scheduler_last_run($db) : ['status' => false, 'message' => 'No database connection'];
$alertsStatus = $db ? count_unresolved_alerts($db) : ['status' => false, 'message' => 'No database connection'];
$countsStatus = $db ? get_system_counts($db) : ['status' => false, 'message' => 'No database connection'];

function get_status_color($status) {
    if ($status === false) return 'red';
    if (is_array($status)) return $status['status'] ? 'green' : 'red';
    return $status ? 'green' : 'red';
}

function get_status_icon($status) {
    $color = get_status_color($status);
    return "
<span style='color:
<?php
 $color'>●</span>";
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .dashboard { max-width: 1000px; margin: 0 auto; }
        .status-card { 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            padding: 15px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-header { 
            font-size: 18px; 
            margin-bottom: 10px; 
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .status-item { 
            margin: 8px 0; 
            display: flex; 
            align-items: center;
        }
        .status-icon { margin-right: 10px; font-size: 20px; }
        .status-message { flex-grow: 1; }
        .counts-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .count-item { 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 4px;
            text-align: center;
        }
        .count-value { 
            font-size: 24px; 
            font-weight: bold;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>System Monitor Dashboard</h1>
        
        <!-- PHP Version Status -->
        <div class="status-card">
            <div class="status-header">PHP Environment</div>
            <div class="status-item">
                <span class="status-icon"><?= get_status_icon($phpStatus['status']) ?></span>
                <span class="status-message"><?= htmlspecialchars($phpStatus['message']) ?></span>
                <span>Version: <?= htmlspecialchars($phpStatus['version']) ?></span>
            </div>
        </div>
        
        <!-- Database Status -->
        <div class="status-card">
            <div class="status-header">Database</div>
            <div class="status-item">
                <span class="status-icon"><?= get_status_icon($dbStatus['status']) ?></span>
                <span class="status-message"><?= htmlspecialchars($dbStatus['message']) ?></span>
            </div>
        </div>
        
        <!-- Scheduler Status -->
        <div class="status-card">
            <div class="status-header">Scheduler</div>
            <div class="status-item">
                <span class="status-icon"><?= get_status_icon($schedulerStatus['status']) ?></span>
                <span class="status-message"><?= htmlspecialchars($schedulerStatus['message']) ?></span>
                <?php if ($schedulerStatus['last_run'] ?? false): ?>
                    <span>(Last run: <?= htmlspecialchars($schedulerStatus['last_run']) ?>)</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Alerts Status -->
        <div class="status-card">
            <div class="status-header">Alerts</div>
            <div class="status-item">
                <span class="status-icon"><?= get_status_icon($alertsStatus['status']) ?></span>
                <span class="status-message"><?= htmlspecialchars($alertsStatus['message']) ?></span>
            </div>
        </div>
        
        <!-- System Counts -->
        <div class="status-card">
            <div class="status-header">System Counts</div>
            <?php if ($countsStatus['status'] && !empty($countsStatus['counts'])): ?>
                <div class="counts-grid">
                    <?php foreach ($countsStatus['counts'] as $name => $count): ?>
                        <div class="count-item">
                            <div class="count-label"><?= ucfirst(str_replace('_', ' ', $name)) ?></div>
                            <div class="count-value"><?= $count ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="status-item">
                    <span class="status-icon"><?= get_status_icon(false) ?></span>
                    <span class="status-message"><?= htmlspecialchars($countsStatus['message']) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
