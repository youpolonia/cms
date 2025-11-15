<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/core/helpers.php';

// Parse GET params with bounds
$limit = max(10, min(1000, (int)($_GET['limit'] ?? 200)));
$q = trim((string)($_GET['q'] ?? ''));
$event = trim((string)($_GET['event'] ?? ''));

$logFile = CMS_ROOT . '/logs/extensions.log';
$lines = [];

if (file_exists($logFile) && is_readable($logFile)) {
    $fp = fopen($logFile, 'r');
    if ($fp) {
        // Get file size and start from end
        fseek($fp, 0, SEEK_END);
        $size = ftell($fp);
        
        if ($size > 0) {
            $chunkSize = 8192;
            $position = max(0, $size - $chunkSize);
            $buffer = '';
            $collected = [];
            
            // Read chunks from end until we have enough matching lines
            while ($position >= 0 && count($collected) < $limit * 2) {
                fseek($fp, $position);
                $chunk = fread($fp, min($chunkSize, $size - $position));
                $buffer = $chunk . $buffer;
                
                // Split into lines
                $chunkLines = explode("\n", $buffer);
                
                // Keep last partial line in buffer for next iteration
                if ($position > 0) {
                    $buffer = array_shift($chunkLines);
                } else {
                    $buffer = '';
                }
                
                // Process lines in reverse order (newest first)
                for ($i = count($chunkLines) - 1; $i >= 0; $i--) {
                    $line = trim($chunkLines[$i]);
                    if ($line === '') continue;
                    
                    // Apply string filter first (most efficient)
                    if ($q !== '' && stripos($line, $q) === false) continue;
                    
                    // Try to decode JSON
                    $data = json_decode($line, true);
                    if (!is_array($data)) continue;
                    
                    // Apply event filter
                    if ($event !== '' && ($data['event'] ?? '') !== $event) continue;
                    
                    $collected[] = $data;
                    if (count($collected) >= $limit) break 2;
                }
                
                $position -= $chunkSize;
            }
            
            $lines = array_slice($collected, 0, $limit);
        }
        fclose($fp);
    }
}

$totalShown = count($lines);
?><!DOCTYPE html>
<html>
<head>
    <title>Extension Audit Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .controls { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 4px; }
        .controls input, .controls select { margin: 0 5px; padding: 4px; }
        .controls button { padding: 6px 12px; margin-left: 10px; }
        .meta { color: #666; font-size: 0.9em; margin: 10px 0; }
        .empty { text-align: center; color: #666; padding: 40px; }
        .back-link { margin-bottom: 20px; }
        .ts { white-space: nowrap; font-family: monospace; font-size: 0.85em; }
        .event { font-weight: bold; }
        .error { color: #d32f2f; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="back-link">
        <a href="index.php">&larr; Back to Extensions</a>
    </div>
    
    <h1>Extension Audit Logs</h1>
    
    <div class="controls">
        <form method="GET">
            <label>Filter: <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search logs..."></label>
            <label>Event: 
                <select name="event">
                    <option value="">All Events</option>
                    <option value="extension_install_ok" <?= $event === 'extension_install_ok' ? 'selected' : '' ?>>Install Success</option>
                    <option value="extension_install_failed" <?= $event === 'extension_install_failed' ? 'selected' : '' ?>>Install Failed</option>
                    <option value="extension_uninstall_ok" <?= $event === 'extension_uninstall_ok' ? 'selected' : '' ?>>Uninstall Success</option>
                    <option value="extension_uninstall_failed" <?= $event === 'extension_uninstall_failed' ? 'selected' : '' ?>>Uninstall Failed</option>
                </select>
            </label>
            <label>Limit: <input type="number" name="limit" value="<?= h($limit) ?>" min="10" max="1000" style="width: 80px;"></label>
            <button type="submit">Filter</button>
            <?php if ($q || $event): ?>
                <a href="logs.php" style="margin-left: 10px;">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="meta">
        Showing <?= $totalShown ?> log entries<?= $q ? ' matching "' . h($q) . '"' : '' ?><?= $event ? ' for event "' . h($event) . '"' : '' 
?>    </div>
    
    <?php if ($totalShown === 0): ?>
        <div class="empty">
            <?php if (!file_exists($logFile)): ?>                No audit log file found. Logs will appear here after extension install/uninstall operations.
            <?php else: ?>                No matching log entries found.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>IP</th>
                    <th>Extension/File</th>
                    <th>Size</th>
                    <th>Error</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $entry): ?>
                    <tr>
                        <td class="ts"><?= h($entry['ts'] ?? '') ?></td>
                        <td class="event"><?= h($entry['event'] ?? '') ?></td>
                        <td><?= h($entry['user'] ?? '') ?></td>
                        <td><?= h($entry['ip'] ?? '') ?></td>
                        <td><?= h($entry['slug'] ?? $entry['file'] ?? '') ?></td>
                        <td><?= isset($entry['size']) && $entry['size'] > 0 ? h(number_format($entry['size'])) . ' bytes' : '' ?></td>
                        <td class="error"><?= h($entry['error'] ?? '') ?></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="<?= h($entry['ua'] ?? '') ?>"><?= h($entry['ua'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
