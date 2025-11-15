<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../includes/security/staticsecurityscanner.php';

$scanner = new StaticSecurityScanner(dirname(__DIR__, 2));
$results = $scanner->scan();
$counts = $scanner->getCounts();

// Handle JSON format
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'counts' => $counts,
        'results' => $results
    ], JSON_PRETTY_PRINT);
    exit;
}

// HTML output
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .summary-box { background: #f9f9f9; border-left: 4px solid #0073aa; padding: 15px; }
        .summary-box.warning { border-left-color: #dc3545; background: #fff5f5; }
        .summary-box.success { border-left-color: #28a745; background: #f0fff4; }
        .summary-box h3 { margin: 0 0 5px 0; font-size: 14px; color: #666; }
        .summary-box .count { font-size: 32px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0073aa; color: white; font-weight: normal; }
        tr:hover { background: #f5f5f5; }
        .snippet { font-family: 'Courier New', monospace; font-size: 12px; max-width: 600px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .path { color: #0073aa; font-size: 12px; }
        .kind { display: inline-block; background: #e7f3ff; padding: 3px 8px; border-radius: 3px; font-size: 11px; color: #004080; }
        .no-issues { color: #28a745; padding: 20px; text-align: center; font-weight: bold; }
        .json-link { float: right; background: #0073aa; color: white; padding: 5px 15px; text-decoration: none; border-radius: 3px; font-size: 14px; }
        .json-link:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Security Audit Report</h1>
        <a href="?format=json" class="json-link">View JSON</a>

        <div class="summary">
            <div class="summary-box <?= $counts['forbidden_calls'] > 0 ? 'warning' : 'success' ?>">
                <h3>Forbidden Calls</h3>
                <div class="count"><?= $counts['forbidden_calls'] ?></div>
            </div>
            <div class="summary-box <?= $counts['autoloaders'] > 0 ? 'warning' : 'success' ?>">
                <h3>Autoloaders</h3>
                <div class="count"><?= $counts['autoloaders'] ?></div>
            </div>
            <div class="summary-box <?= $counts['dynamic_includes'] > 0 ? 'warning' : 'success' ?>">
                <h3>Dynamic Includes</h3>
                <div class="count"><?= $counts['dynamic_includes'] ?></div>
            </div>
            <div class="summary-box <?= $counts['csrf_missing'] > 0 ? 'warning' : 'success' ?>">
                <h3>CSRF Issues</h3>
                <div class="count"><?= $counts['csrf_missing'] ?></div>
            </div>
            <div class="summary-box <?= $counts['public_test_endpoints'] > 0 ? 'warning' : 'success' ?>">
                <h3>Public Test Endpoints</h3>
                <div class="count"><?= $counts['public_test_endpoints'] ?></div>
            </div>
            <div class="summary-box <?= $counts['trailing_php_tag'] > 0 ? 'warning' : 'success' ?>">
                <h3>Trailing PHP Tags</h3>
                <div class="count"><?= $counts['trailing_php_tag'] ?></div>
            </div>
        </div>

        <?php foreach (['forbidden_calls', 'autoloaders', 'dynamic_includes', 'csrf_missing', 'public_test_endpoints', 'trailing_php_tag'] as $category): ?>
            <?php if (count($results[$category]) > 0): ?>
                <h2><?= ucwords(str_replace('_', ' ', $category)) ?> (<?= count($results[$category]) ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th>Line</th>
                            <th>Kind</th>
                            <th>Snippet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results[$category] as $issue): ?>
                            <tr>
                                <td class="path"><?= htmlspecialchars($issue['path']) ?></td>
                                <td><?= $issue['line'] ?></td>
                                <td><span class="kind"><?= htmlspecialchars($issue['kind']) ?></span></td>
                                <td class="snippet"><?= htmlspecialchars($issue['snippet']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (array_sum($counts) === 0): ?>
            <div class="no-issues">✓ No security issues detected</div>
        <?php endif; ?>
    </div>
</body>
</html>
