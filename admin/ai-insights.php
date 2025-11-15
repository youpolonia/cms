<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../includes/ai/AIInsightLogger.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';

// Security check - verify admin role in session
cms_session_start('admin');

if (empty($_SESSION['user'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$user = $_SESSION['user'];
if (!isset($user['roles']) || !in_array('admin_ai_insights', $user['roles'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$logs = AIInsightLogger::getRecentLogs(100);
$stats = AIInsightLogger::getSummaryStats();
$timeline = AIInsightLogger::getTimelineData(7);
$topUsage = AIInsightLogger::getTopUsage(5);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        h2 { color: #444; margin-top: 30px; }
        .panel {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        .timeline-chart {
            display: flex;
            height: 200px;
            align-items: flex-end;
            gap: 10px;
            margin: 20px 0;
        }
        .timeline-bar {
            flex: 1;
            background: #3498db;
            border-radius: 3px 3px 0 0;
            position: relative;
            min-width: 30px;
        }
        .timeline-label {
            position: absolute;
            bottom: -25px;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .usage-list {
            list-style: none;
            padding: 0;
        }
        .usage-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .usage-count {
            font-weight: bold;
            color: #2c3e50;
        }
        .log-entry {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .log-module { color: #2c3e50; }
        .log-action { color: #3498db; }
        .log-timestamp { color: #7f8c8d; font-size: 0.9em; }
        .log-details { margin-top: 10px; }
        pre {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>AI Insights Dashboard</h1>
        
        <div class="panel">
            <h2>Summary Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_actions'] ?? 0 ?></div>
                    <div class="stat-label">Total Actions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['acceptance_rate'] ?? 0 ?>%</div>
                    <div class="stat-label">Acceptance Rate</div>
                </div>
            </div>
            
            <?php if (!empty($stats['type_breakdown'])): ?>
<h3>Action Breakdown</h3>
                <ul class="usage-list">
                    <?php foreach ($stats['type_breakdown'] as $action => $count): ?>
<li class="usage-item">
                            <span><?= htmlspecialchars($action) ?></span>
                            <span class="usage-count"><?= $count ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
<div class="panel">
            <h2>7-Day Activity Timeline</h2>
            <div class="timeline-chart">
                <?php foreach ($timeline as $date => $count): ?>
<div class="timeline-bar" style="height: <?= min(100, ($count / max($timeline)) * 100) ?>%">
                        <div class="timeline-label"><?= date('D', strtotime($date)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
<div class="panel">
            <h2>Top Used Features</h2>
            <ul class="usage-list">
                <?php foreach ($topUsage as $module => $count): ?>
<li class="usage-item">
                        <span><?= htmlspecialchars($module) ?></span>
                        <span class="usage-count"><?= $count ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
<div class="panel">
            <h2>Recent Activity</h2>
            <?php if (empty($logs)): ?>
<p>No AI activity logged yet.</p>
            <?php else: ?>                <?php foreach ($logs as $log): ?>
<div class="log-entry">
                        <div class="log-header">
                            <span class="log-module">Module: <?= htmlspecialchars($log['module']) ?></span>
                            <span class="log-action">Action: <?= htmlspecialchars($log['action']) ?></span>
                            <span class="log-timestamp"><?= htmlspecialchars($log['timestamp']) ?></span>
                        </div>
                        <div class="log-details">
                            <?php if (!empty($log['details'])): ?>
                                <pre><?= htmlspecialchars(json_encode($log['details'], JSON_PRETTY_PRINT)) ?></pre>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>            <?php endif; ?>
        </div>
    </div>
</body>
</html>
