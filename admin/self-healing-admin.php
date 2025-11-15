<?php
require_once __DIR__ . '/../core/csrf.php';
// Verify admin permissions
require_once __DIR__ . '/../includes/auth/admin-check.php';

$logFile = __DIR__ . '/../../logs/self-healing.log';
$entries = [];
$now = time();
$oneDayAgo = $now - 86400;

// Read and parse log file
if (file_exists($logFile) && is_readable($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_slice(array_reverse($lines), 0, 200); // Get latest 200
    
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry && is_array($entry)) {
            $entry['is_recent'] = (strtotime($entry['timestamp']) > $oneDayAgo);
            $entries[] = $entry;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    csrf_validate_or_403();

    $settingsFile = __DIR__ . '/../../config/settings.ini';
    if (file_exists($settingsFile)) {
        // Avoid parsing external INI for env; use safe defaults or central config
        $settings = []; // parse_ini_file removed
        
        if (isset($_POST['enable_ai'])) {
            $settings['ai']['disabled'] = false;
        }
        if (isset($_POST['enable_scheduler'])) {
            $settings['scheduler']['disabled'] = false;
        }
        
        // Write updated settings back
        $content = '';
        foreach ($settings as $section => $values) {
            $content .= "[$section]\n";
            foreach ($values as $key => $value) {
                $content .= "$key = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
            }
            $content .= "\n";
        }
        
        file_put_contents($settingsFile, $content);
        $success = "Settings updated successfully";
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Self-Healing Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .recent { background-color: #fffde7; }
        .action-badge { 
            padding: 3px 8px; 
            border-radius: 12px; 
            color: white;
            font-size: 0.8em;
        }
        .ai { background: #9b59b6; }
        .scheduler { background: #3498db; }
        form { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        button { padding: 5px 10px; margin-right: 10px; }
        .success { color: green; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Self-Healing Actions</h1>
    
    <?php if (isset($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <h3>Manual Override</h3>
        <button type="submit" name="enable_ai">Re-enable AI</button>
        <button type="submit" name="enable_scheduler">Re-enable Scheduler</button>
    </form>
    
    <?php if (empty($entries)): ?>
        <p>No self-healing actions logged.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Trigger Reason</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr class="<?= $entry['is_recent'] ? 'recent' : '' ?>">
                        <td><?= htmlspecialchars($entry['timestamp']) ?></td>
                        <td>
                            <span class="action-badge <?= strpos($entry['action'], 'ai') !== false ? 'ai' : 'scheduler' ?>">
                                <?= htmlspecialchars($entry['action']) 
?>                            </span>
                        </td>
                        <td><?= htmlspecialchars($entry['reason']) ?></td>
                        <td>
                            <?php if (!empty($entry['details'])): ?>
                                <pre><?= htmlspecialchars(json_encode($entry['details'], JSON_PRETTY_PRINT)) ?></pre>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
