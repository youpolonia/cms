<?php
/**
 * Daily Diagnostic Report Generator
 * 
 * Generates an HTML report with system diagnostics from:
 * - Telemetry logs
 * - Error patterns
 * - Notification queue
 * - Fallback events
 */

// Configuration
define('REPORT_DIR', __DIR__ . '/../reports/');
define('LOG_DIR', __DIR__ . '/../logs/');
define('REPORT_DATE', date('Y-m-d'));

// Ensure directories exist
if (!file_exists(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}
if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

/**
 * Main report generation function
 */
function generateDiagnosticReport() {
    try {
        $reportData = [
            'summary' => getSystemSummary(),
            'telemetry' => getTelemetryStats(),
            'errorPatterns' => getErrorPatterns(),
            'notifications' => getNotificationStats(),
            'fallbacks' => getFallbackEvents()
        ];

        $htmlReport = generateHtmlReport($reportData);
        $reportSaved = saveReport($htmlReport);
        
        if ($reportSaved) {
            cleanupOldReports();
            
            // Send notification if NotificationManager exists
            if (function_exists('NotificationManager::add')) {
                NotificationManager::add(
                    'info',
                    'New diagnostic report available: diagnostics_' . REPORT_DATE . '.html',
                    'diagnostics',
                    time(),
                    true // mark as unread
                );
            }
        }
        
        logReportStatus($reportSaved);
    } catch (Exception $e) {
        logReportStatus(false, $e->getMessage());
    }
}

/**
 * Get basic system summary
 */
function getSystemSummary() {
    return [
        'generated_at' => date('Y-m-d H:i:s'),
        'status' => 'OK',
        'server' => $_SERVER['SERVER_NAME'] ?? 'CLI'
    ];
}

/**
 * Get telemetry statistics
 */
function getTelemetryStats() {
    $telemetryFile = LOG_DIR . 'telemetry.log';
    $stats = [
        'total_entries' => 0,
        'by_type' => ['error' => 0, 'warning' => 0, 'info' => 0],
        'by_module' => [],
        'available' => false
    ];

    if (!file_exists($telemetryFile)) {
        return $stats;
    }

    try {
        $lines = file($telemetryFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stats['available'] = true;
        $stats['total_entries'] = count($lines);
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;
            
            $type = $entry['type'] ?? 'unknown';
            $module = $entry['module'] ?? 'unknown';
            
            if (isset($stats['by_type'][$type])) {
                $stats['by_type'][$type]++;
            }
            
            if (!isset($stats['by_module'][$module])) {
                $stats['by_module'][$module] = 0;
            }
            $stats['by_module'][$module]++;
        }
    } catch (Exception $e) {
        // Silently continue
    }

    return $stats;
}

/**
 * Get known error patterns
 */
function getErrorPatterns() {
    $patternFile = LOG_DIR . 'known-error-patterns.json';
    $patterns = [
        'available' => false,
        'patterns' => [],
        'count' => 0
    ];

    if (!file_exists($patternFile)) {
        return $patterns;
    }

    try {
        $data = json_decode(file_get_contents($patternFile), true);
        if ($data) {
            $patterns['available'] = true;
            $patterns['patterns'] = array_slice($data, 0, 5);
            $patterns['count'] = count($data);
        }
    } catch (Exception $e) {
        // Silently continue
    }

    return $patterns;
}

/**
 * Get notification statistics (stub - implement based on your NotificationManager)
 */
function getNotificationStats() {
    return [
        'unread_count' => 0,
        'latest' => [],
        'available' => false
    ];
}

/**
 * Get fallback events (stub - implement based on your system)
 */
function getFallbackEvents() {
    $fallbackFile = LOG_DIR . 'ai_last_fallback.json';
    $fallback = [
        'available' => false,
        'last_time' => null,
        'module' => null,
        'reason' => null
    ];

    if (!file_exists($fallbackFile)) {
        return $fallback;
    }

    try {
        $data = json_decode(file_get_contents($fallbackFile), true);
        if ($data) {
            $fallback['available'] = true;
            $fallback['last_time'] = $data['timestamp'] ?? null;
            $fallback['module'] = $data['module'] ?? null;
            $fallback['reason'] = $data['reason'] ?? null;
        }
    } catch (Exception $e) {
        // Silently continue
    }

    return $fallback;
}

/**
 * Generate HTML report
 */
function generateHtmlReport($data) {
    $reportDate = REPORT_DATE;
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Diagnostics Report - {$reportDate}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #444; margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        .info { background-color: #d1ecf1; }
        .unavailable { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Daily Diagnostics Report</h1>
    <p>Generated: {$data['summary']['generated_at']}</p>
    <p>Status: {$data['summary']['status']}</p>
    <p>Server: {$data['summary']['server']}</p>

    <h2>Telemetry Statistics</h2>
HTML;

    if ($data['telemetry']['available']) {
        $html .= <<<HTML
        <table>
            <tr>
                <th>Total Entries</th>
                <td>{$data['telemetry']['total_entries']}</td>
            </tr>
            <tr>
                <th>Errors</th>
                <td>{$data['telemetry']['by_type']['error']}</td>
            </tr>
            <tr>
                <th>Warnings</th>
                <td>{$data['telemetry']['by_type']['warning']}</td>
            </tr>
            <tr>
                <th>Info</th>
                <td>{$data['telemetry']['by_type']['info']}</td>
            </tr>
        </table>

        <h3>By Module</h3>
        <table>
            <tr>
                <th>Module</th>
                <th>Count</th>
            </tr>
HTML;

        foreach ($data['telemetry']['by_module'] as $module => $count) {
            $html .= <<<HTML
            <tr>
                <td>{$module}</td>
                <td>{$count}</td>
            </tr>
HTML;
        }

        $html .= <<<HTML
        </table>
HTML;
    } else {
        $html .= <<<HTML
        <p class="unavailable">Telemetry data not available</p>
HTML;
    }

    $html .= <<<HTML
    <h2>Error Patterns</h2>
HTML;

    if ($data['errorPatterns']['available']) {
        $html .= <<<HTML
        <p>Total patterns: {$data['errorPatterns']['count']}</p>
        <table>
            <tr>
                <th>Pattern</th>
                <th>Count</th>
            </tr>
HTML;

        foreach ($data['errorPatterns']['patterns'] as $pattern) {
            $html .= <<<HTML
            <tr>
                <td>{$pattern['pattern']}</td>
                <td>{$pattern['count']}</td>
            </tr>
HTML;
        }

        $html .= <<<HTML
        </table>
HTML;
    } else {
        $html .= <<<HTML
        <p class="unavailable">Error patterns not available</p>
HTML;
    }

    $html .= <<<HTML
    <h2>Notifications</h2>
HTML;

    if ($data['notifications']['available']) {
        $html .= <<<HTML
        <p>Unread notifications: {$data['notifications']['unread_count']}</p>
        <table>
            <tr>
                <th>Time</th>
                <th>Message</th>
            </tr>
HTML;

        foreach ($data['notifications']['latest'] as $notification) {
            $html .= <<<HTML
            <tr>
                <td>{$notification['time']}</td>
                <td>{$notification['message']}</td>
            </tr>
HTML;
        }

        $html .= <<<HTML
        </table>
HTML;
    } else {
        $html .= <<<HTML
        <p class="unavailable">Notification data not available</p>
HTML;
    }

    $html .= <<<HTML
    <h2>Fallback Events</h2>
HTML;

    if ($data['fallbacks']['available']) {
        $html .= <<<HTML
        <table>
            <tr>
                <th>Last Fallback</th>
                <td>{$data['fallbacks']['last_time']}</td>
            </tr>
            <tr>
                <th>Module</th>
                <td>{$data['fallbacks']['module']}</td>
            </tr>
            <tr>
                <th>Reason</th>
                <td>{$data['fallbacks']['reason']}</td>
            </tr>
        </table>
HTML;
    } else {
        $html .= <<<HTML
        <p class="unavailable">Fallback data not available</p>
HTML;
    }

    $html .= <<<HTML
</body>
</html>
HTML;

    return $html;
}

/**
 * Save report to file
 * @return bool True if report was saved successfully
 */
function saveReport($content) {
    $reportFile = REPORT_DIR . 'diagnostics_' . REPORT_DATE . '.html';
    return file_put_contents($reportFile, $content) !== false;
}

/**
 * Clean up old reports, keeping only the latest 30
 */
function cleanupOldReports() {
    $reportFiles = glob(REPORT_DIR . 'diagnostics_*.html');
    if (count($reportFiles) <= 30) {
        return;
    }

    // Sort by filename (which contains date)
    usort($reportFiles, function($a, $b) {
        return strcmp($a, $b);
    });

    // Keep last 30 files
    $filesToDelete = array_slice($reportFiles, 0, -30);
    
    foreach ($filesToDelete as $file) {
        if (file_exists($file)) {
            unlink($file);
            file_put_contents(
                LOG_DIR . 'report.log',
                date('Y-m-d H:i:s') . ' - DELETED: ' . basename($file) . PHP_EOL,
                FILE_APPEND
            );
        }
    }
}

/**
 * Log report generation status
 */
function logReportStatus($success, $error = null) {
    $logEntry = date('Y-m-d H:i:s') . ' - ' . 
                ($success ? 'SUCCESS' : 'ERROR') . 
                ($error ? ': ' . $error : '') . PHP_EOL;
    
    file_put_contents(LOG_DIR . 'report.log', $logEntry, FILE_APPEND);
}

// Execute report generation
generateDiagnosticReport();
