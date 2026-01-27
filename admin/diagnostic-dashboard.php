<?php
/**
 * Diagnostic Dashboard - Visual Summary of Reports
 */

// Check admin permissions
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Configuration
define('REPORTS_DIR', __DIR__ . '/../reports/');
$today = date('Y-m-d');

/**
 * Parse diagnostic report file
 */
function parseReport($file) {
    if (!file_exists($file)) return null;
    
    $content = file_get_contents($file);
    $data = [
        'date' => '',
        'errors' => 0,
        'warnings' => 0,
        'modules' => []
    ];

    // Extract date from filename
    if (preg_match('/diagnostics_(\d{4}-\d{2}-\d{2})\.html$/', $file, $matches)) {
        $data['date'] = $matches[1];
    }

    // Count errors and warnings
    $data['errors'] = substr_count($content, 'class="error"');
    $data['warnings'] = substr_count($content, 'class="warning"');

    // Extract module issues (simplified example)
    if (preg_match_all('/Module: ([^<]+)/', $content, $modules)) {
        $data['modules'] = array_count_values($modules[1]);
    }

    return $data;
}

// Get latest 7 reports
$reports = [];
$files = glob(REPORTS_DIR . 'diagnostics_*.html');
usort($files, function($a, $b) { return strcmp($b, $a); });
$files = array_slice($files, 0, 7);

foreach ($files as $file) {
    $report = parseReport($file);
    if ($report) $reports[] = $report;
}

// Calculate aggregates
$totalErrors = array_sum(array_column($reports, 'errors'));
$totalWarnings = array_sum(array_column($reports, 'warnings'));
$avgErrors = $totalErrors / max(1, count($reports));

// Find most problematic module
$moduleIssues = [];
foreach ($reports as $report) {
    foreach ($report['modules'] as $module => $count) {
        $moduleIssues[$module] = ($moduleIssues[$module] ?? 0) + $count;
    }
}
arsort($moduleIssues);
$topModule = key($moduleIssues) ?: 'None';

// Find max errors for chart scaling
$maxErrors = max(array_merge([1], array_column($reports, 'errors')));
?><!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .dashboard { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .card h2 { margin-top: 0; color: #444; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .stat { padding: 10px; border-radius: 3px; }
        .errors { background-color: #ffebee; color: #c62828; }
        .warnings { background-color: #fff8e1; color: #f57f17; }
        .chart { margin-top: 20px; }
        .bar-container { display: flex; height: 200px; align-items: flex-end; gap: 10px; }
        .bar { flex: 1; display: flex; flex-direction: column; }
        .bar-value { background-color: #42a5f5; transition: height 0.3s; }
        .bar-value.error { background-color: #ef5350; }
        .bar-label { text-align: center; margin-top: 5px; font-size: 12px; }
        .today .bar-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr.today { background-color: #e6f7ff; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Diagnostic Dashboard</h1>
    
    <div class="dashboard">
        <div class="card">
            <h2>Summary</h2>
            <div class="stats">
                <div class="stat errors">
                    <strong>Total Errors:</strong><br>
                    <?= $totalErrors 
?>                </div>
                <div class="stat warnings">
                    <strong>Total Warnings:</strong><br>
                    <?= $totalWarnings 
?>                </div>
                <div class="stat">
                    <strong>Average Errors/Day:</strong><br>
                    <?= round($avgErrors, 1) 
?>                </div>
                <div class="stat">
                    <strong>Problem Module:</strong><br>
                    <?= htmlspecialchars($topModule) 
?>                </div>
            </div>
        </div>

        <div class="card">
            <h2>Errors Last 7 Days</h2>
            <div class="chart">
                <div class="bar-container">
                    <?php foreach ($reports as $report): ?>
                        <div class="bar <?= $report['date'] === $today ? 'today' : '' ?>">
                            <div class="bar-value error" 
                                 style="height: <?= ($report['errors'] / $maxErrors) * 100 ?>%"></div>
                            <div class="bar-label">
                                <?= substr($report['date'], 5) ?><br>
                                <?= $report['errors'] ?> errors
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="grid-column: span 2;">
        <h2>Detailed Report</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Errors</th>
                    <th>Warnings</th>
                    <th>Top Module</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr class="<?= $report['date'] === $today ? 'today' : '' ?>">
                        <td><?= htmlspecialchars($report['date']) ?></td>
                        <td><?= $report['errors'] ?></td>
                        <td><?= $report['warnings'] ?></td>
                        <td><?= htmlspecialchars(key($report['modules']) ?: 'None') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
