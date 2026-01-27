<?php
// Verify admin permissions
require_once __DIR__ . '/../includes/auth/admin-check.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

$logFile = __DIR__ . '/../../logs/telemetry.log';
$filterType = $_GET['type'] ?? '';
$validTypes = ['info', 'warn', 'error', 'success', 'usage'];
$entries = [];

// Read and parse log file for dashboard
function getTelemetryStats($logFile) {
    $stats = [
        'total' => 0,
        'types' => array_fill_keys(['info', 'warn', 'error', 'success', 'usage'], 0),
        'modules' => [],
        'hourly' => array_fill(0, 24, ['total' => 0, 'error' => 0, 'warn' => 0])
    ];
    
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice(array_reverse($lines), 0, 1000); // Last 1000 entries
        
        $now = time();
        $oneDayAgo = $now - 86400;
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry || !isset($entry['timestamp'])) continue;
            
            $entryTime = strtotime($entry['timestamp']);
            if ($entryTime < $oneDayAgo) continue;
            
            $stats['total']++;
            $stats['types'][$entry['type']]++;
            
            // Track modules
            if (!empty($entry['context']['module'])) {
                $module = $entry['context']['module'];
                $stats['modules'][$module] = ($stats['modules'][$module] ?? 0) + 1;
            }
            
            // Track hourly
            $hour = (int)date('G', $entryTime);
            $stats['hourly'][$hour]['total']++;
            if ($entry['type'] === 'error') $stats['hourly'][$hour]['error']++;
            if ($entry['type'] === 'warn') $stats['hourly'][$hour]['warn']++;
        }
    }
    return $stats;
}

$stats = getTelemetryStats($logFile);

// Read and parse log file for table
if (file_exists($logFile) && is_readable($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_slice(array_reverse($lines), 0, 200); // Get latest 200
    
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry && is_array($entry)) {
            if (!$filterType || $entry['type'] === $filterType) {
                $entries[] = $entry;
            }
        }
    }
}

// Generate type badge HTML
function getTypeBadge($type) {
    $colors = [
        'info' => 'blue',
        'warn' => 'orange',
        'error' => 'red',
        'success' => 'green',
        'usage' => 'purple'
    ];
    $color = $colors[$type] ?? 'gray';
    return "
<span class='badge {
$color}'>{$type}</span>";
}

// Handle pattern analysis request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analyze_patterns'])) {
    csrf_validate_or_403();
    require_once __DIR__ . '/../includes/system/TelemetryPatternAnalyzer.php';
    $hours = (int)($_POST['hours'] ?? 6);
    $minOccurrences = (int)($_POST['min_occurrences'] ?? 3);
    $patterns = TelemetryPatternAnalyzer::analyzeRecentPatterns($hours, $minOccurrences);
}
?><!DOCTYPE html>
<html>
<head>
    <title>Telemetry Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .badge { 
            padding: 3px 8px; 
            border-radius: 12px; 
            color: white;
            font-size: 0.8em;
        }
        .blue { background: #3498db; }
        .orange { background: #f39c12; }
        .red { background: #e74c3c; }
        .green { background: #2ecc71; }
        .purple { background: #9b59b6; }
        .gray { background: #95a5a6; }
        form { margin-bottom: 20px; }
        select, button, input { padding: 5px 10px; }
        .section-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .pattern-table th {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <h1>Telemetry Logs</h1>
    
    <div class="section-box">
        <h2>📊 Error Pattern Analysis</h2>
        <form method="post">
            <?= csrf_field(); ?> 
            <label for="hours">Analyze last:</label>
            <select name="hours" id="hours">
                <option value="1">1 hour</option>
                <option value="3">3 hours</option>
                <option value="6" selected>6 hours</option>
                <option value="12">12 hours</option>
?>            </select>
            
            <label for="min_occurrences">Minimum occurrences:</label>
            <input type="number" name="min_occurrences" id="min_occurrences" value="3" min="1" max="20">
            
            <button type="submit" name="analyze_patterns">Run Pattern Analysis</button>
        </form>
        
        <?php if (!empty($patterns)): ?>
            <h3>Pattern Analysis Results</h3>
            <table class="pattern-table">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Count</th>
                        <th>Time Range</th>
                        <th>Affected Modules</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patterns as $pattern): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($pattern['example_message'], 0, 80)) ?>...</td>
                            <td><?= $pattern['count'] ?></td>
                            <td><?= htmlspecialchars($pattern['first_occurrence']) ?> to <?= htmlspecialchars($pattern['last_occurrence']) ?></td>
                            <td>
                                <?php if (!empty($pattern['common_context']['module'])): ?>                                    <?= htmlspecialchars(implode(', ', $pattern['common_context']['module']))  ?>
                                <?php else: ?>                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($patterns)): ?>
            <p>No repeating error patterns found.</p>
        <?php endif; ?>
    </div>
    
    <form method="get">
        <label for="type">Filter by type:</label>
        <select name="type" id="type">
            <option value="">All types</option>
            <?php foreach ($validTypes as $type): ?>                <option value="<?= $type ?>" <?= $filterType === $type ? 'selected' : '' ?>>
                    <?= ucfirst($type)  ?>
?>                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply</button>
    </form>

    <?php if (empty($entries)): ?>
        <p>No log entries found<?= $filterType ? " for type '{$filterType}'" : '' ?>.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Context</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['timestamp']) ?></td>
                        <td><?= getTypeBadge($entry['type']) ?></td>
                        <td><?= htmlspecialchars($entry['message']) ?></td>
                        <td>
                            <?php if (!empty($entry['context'])): ?>
                                <pre><?= htmlspecialchars(json_encode($entry['context'], JSON_PRETTY_PRINT)) ?></pre>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
