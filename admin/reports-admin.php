<?php
/**
 * Diagnostic Reports Admin Interface
 * 
 * Shows all available diagnostic reports with basic file info
 */

// Check admin permissions
if (!isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Configuration
define('REPORTS_DIR', __DIR__ . '/../reports/');
define('LOG_DIR', __DIR__ . '/../logs/');
$today = date('Y-m-d');

// Log access if logging enabled
if (file_exists(LOG_DIR)) {
    $logEntry = sprintf(
        "[%s] %s accessed reports admin from %s\n",
        date('Y-m-d H:i:s'),
        $_SESSION['user']['username'] ?? 'unknown',
        $_SERVER['REMOTE_ADDR']
    );
    file_put_contents(LOG_DIR . 'report-access.log', $logEntry, FILE_APPEND);
}

// Get all report files
$reports = [];
$files = glob(REPORTS_DIR . 'diagnostics_*.html');

foreach ($files as $file) {
    // Extract date from filename
    if (preg_match('/diagnostics_(\d{4}-\d{2}-\d{2})\.html$/', $file, $matches)) {
        $date = $matches[1];
        $size = filesize($file) / 1024; // KB
        $reports[] = [
            'date' => $date,
            'file' => basename($file),
            'size' => round($size, 1),
            'is_today' => ($date === $today)
        ];
    }
}

// Sort by date descending
usort($reports, function($a, $b) {
    return strcmp($b['date'], $a['date']);
});

?><!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Reports</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr.today { background-color: #e6f7ff; font-weight: bold; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .no-reports { margin-top: 20px; color: #666; }
    </style>
</head>
<body>
    <h1>Diagnostic Reports</h1>
    
    <?php if (empty($reports)): ?>
        <p class="no-reports">No diagnostic reports available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Report</th>
                    <th>Size (KB)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr class="<?= $report['is_today'] ? 'today' : '' ?>">
                        <td><?= htmlspecialchars($report['date']) ?></td>
                        <td>
                            <a href="/reports/<?= htmlspecialchars($report['file']) ?>" target="_blank">
                                View Report
                            </a>
                        </td>
                        <td><?= htmlspecialchars($report['size']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
