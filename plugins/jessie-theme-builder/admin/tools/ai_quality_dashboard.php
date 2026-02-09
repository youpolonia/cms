<?php
/**
 * JTB AI Quality Dashboard
 * READ-ONLY dashboard for monitoring AI layout generation quality
 *
 * @package JessieThemeBuilder
 */

// When loaded via MVC controller, CMS_ROOT is already defined
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(dirname(dirname(__DIR__)))));
    require_once CMS_ROOT . '/config.php';

    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once CMS_ROOT . '/admin/includes/auth.php';

    // Check admin authentication (only when accessed directly)
    if (!AdminAuth::isAuthenticated()) {
        http_response_code(401);
        header('Content-Type: text/plain');
        echo 'Unauthorized. Please log in to admin panel.';
        exit;
    }

    // DEV_MODE gate (only when accessed directly)
    if (!defined('DEV_MODE') || DEV_MODE !== true) {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo 'Access denied. This tool is only available in DEV_MODE.';
        exit;
    }
}

// Method check - GET only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    header('Content-Type: text/plain');
    echo 'Method Not Allowed. Only GET is permitted.';
    exit;
}

// Parameters
$limit = min(max((int)($_GET['limit'] ?? 200), 1), 2000);
$filterStatus = $_GET['status'] ?? null;
$filterAttempt = isset($_GET['attempt']) ? (int)$_GET['attempt'] : null;

// Read log file
$logFile = CMS_ROOT . '/logs/ai-quality.log';
$entries = [];
$hasData = false;

if (file_exists($logFile) && is_readable($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false && count($lines) > 0) {
        $hasData = true;
        $lines = array_slice($lines, -$limit);

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if ($entry === null) continue;

            // Apply filters
            if ($filterStatus !== null && ($entry['status'] ?? '') !== $filterStatus) continue;
            if ($filterAttempt !== null && ($entry['attempt'] ?? 0) !== $filterAttempt) continue;

            $entries[] = $entry;
        }
    }
}

// Calculate statistics
$totalEntries = count($entries);
$sumScore = 0;
$statusCounts = ['REJECT' => 0, 'ACCEPTABLE' => 0, 'GOOD' => 0, 'EXCELLENT' => 0];
$attemptCounts = [1 => 0, 2 => 0, 3 => 0];
$forcedAcceptCount = 0;
$violationCounts = [];
$warningCounts = [];

foreach ($entries as $entry) {
    $sumScore += $entry['score'] ?? 0;

    $status = $entry['status'] ?? 'REJECT';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }

    $attempt = $entry['attempt'] ?? 1;
    if (isset($attemptCounts[$attempt])) {
        $attemptCounts[$attempt]++;
    }

    if (!empty($entry['forced_accept'])) {
        $forcedAcceptCount++;
    }

    foreach ($entry['violations'] ?? [] as $v) {
        $code = explode(':', $v)[0];
        $violationCounts[$code] = ($violationCounts[$code] ?? 0) + 1;
    }

    foreach ($entry['warnings'] ?? [] as $w) {
        $code = explode(':', $w)[0];
        $warningCounts[$code] = ($warningCounts[$code] ?? 0) + 1;
    }
}

$avgScore = $totalEntries > 0 ? round($sumScore / $totalEntries, 2) : 0;

arsort($violationCounts);
arsort($warningCounts);

$topViolations = array_slice($violationCounts, 0, 10, true);
$topWarnings = array_slice($warningCounts, 0, 10, true);

// Reverse entries for display (newest first)
$entries = array_reverse($entries);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JTB AI Quality Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; line-height: 1.5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { margin-bottom: 20px; color: #1a1a2e; }
        h2 { margin: 20px 0 10px; color: #16213e; font-size: 1.2rem; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card-label { font-size: 0.8rem; color: #666; text-transform: uppercase; }
        .card-value { font-size: 1.8rem; font-weight: bold; color: #1a1a2e; }
        .card-value.green { color: #10b981; }
        .card-value.yellow { color: #f59e0b; }
        .card-value.red { color: #ef4444; }
        .card-value.blue { color: #3b82f6; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 800px) { .grid-2 { grid-template-columns: 1fr; } }
        .list { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .list-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .list-item:last-child { border-bottom: none; }
        .list-item .code { font-family: monospace; font-size: 0.9rem; }
        .list-item .count { font-weight: bold; color: #666; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1a1a2e; color: #fff; font-weight: 500; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: #f9fafb; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-reject { background: #fef2f2; color: #dc2626; }
        .badge-acceptable { background: #fffbeb; color: #d97706; }
        .badge-good { background: #ecfdf5; color: #059669; }
        .badge-excellent { background: #eff6ff; color: #2563eb; }
        .badge-forced { background: #fef3c7; color: #92400e; }
        .no-data { text-align: center; padding: 60px 20px; background: #fff; border-radius: 8px; color: #666; }
        .filters { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .filters label { font-size: 0.9rem; color: #666; }
        .filters select, .filters input { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; }
        .filters button { padding: 6px 15px; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .filters button:hover { background: #2563eb; }
        details { margin-top: 5px; }
        details summary { cursor: pointer; font-size: 0.8rem; color: #3b82f6; }
        details pre { margin-top: 5px; font-size: 0.75rem; background: #f5f5f5; padding: 8px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>JTB AI Quality Dashboard</h1>

    <?php if (!$hasData): ?>
    <div class="no-data">
        <h2>No data yet</h2>
        <p>AI quality logs will appear here once layouts are generated using <code>generateWithValidation()</code>.</p>
    </div>
    <?php else: ?>

    <form method="get" class="filters">
        <label>Limit:</label>
        <input type="number" name="limit" value="<?= htmlspecialchars($limit) ?>" min="1" max="2000" style="width:80px">

        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="REJECT" <?= $filterStatus === 'REJECT' ? 'selected' : '' ?>>REJECT</option>
            <option value="ACCEPTABLE" <?= $filterStatus === 'ACCEPTABLE' ? 'selected' : '' ?>>ACCEPTABLE</option>
            <option value="GOOD" <?= $filterStatus === 'GOOD' ? 'selected' : '' ?>>GOOD</option>
            <option value="EXCELLENT" <?= $filterStatus === 'EXCELLENT' ? 'selected' : '' ?>>EXCELLENT</option>
        </select>

        <label>Attempt:</label>
        <select name="attempt">
            <option value="">All</option>
            <option value="1" <?= $filterAttempt === 1 ? 'selected' : '' ?>>1</option>
            <option value="2" <?= $filterAttempt === 2 ? 'selected' : '' ?>>2</option>
            <option value="3" <?= $filterAttempt === 3 ? 'selected' : '' ?>>3</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <h2>Summary</h2>
    <div class="cards">
        <div class="card">
            <div class="card-label">Total Entries</div>
            <div class="card-value"><?= $totalEntries ?></div>
        </div>
        <div class="card">
            <div class="card-label">Avg Score</div>
            <div class="card-value <?= $avgScore >= 16 ? 'green' : ($avgScore >= 11 ? 'yellow' : 'red') ?>"><?= $avgScore ?></div>
        </div>
        <div class="card">
            <div class="card-label">EXCELLENT</div>
            <div class="card-value blue"><?= $statusCounts['EXCELLENT'] ?></div>
        </div>
        <div class="card">
            <div class="card-label">GOOD</div>
            <div class="card-value green"><?= $statusCounts['GOOD'] ?></div>
        </div>
        <div class="card">
            <div class="card-label">ACCEPTABLE</div>
            <div class="card-value yellow"><?= $statusCounts['ACCEPTABLE'] ?></div>
        </div>
        <div class="card">
            <div class="card-label">REJECT</div>
            <div class="card-value red"><?= $statusCounts['REJECT'] ?></div>
        </div>
        <div class="card">
            <div class="card-label">Attempt 1</div>
            <div class="card-value"><?= $attemptCounts[1] ?></div>
        </div>
        <div class="card">
            <div class="card-label">Attempt 2</div>
            <div class="card-value"><?= $attemptCounts[2] ?></div>
        </div>
        <div class="card">
            <div class="card-label">Attempt 3</div>
            <div class="card-value"><?= $attemptCounts[3] ?></div>
        </div>
        <div class="card">
            <div class="card-label">Forced Accept</div>
            <div class="card-value <?= $forcedAcceptCount > 0 ? 'yellow' : '' ?>"><?= $forcedAcceptCount ?></div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <h2>Top 10 Violations</h2>
            <div class="list">
                <?php if (empty($topViolations)): ?>
                <div class="list-item"><span class="code">None</span></div>
                <?php else: ?>
                <?php foreach ($topViolations as $code => $count): ?>
                <div class="list-item">
                    <span class="code"><?= htmlspecialchars($code) ?></span>
                    <span class="count"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <h2>Top 10 Warnings</h2>
            <div class="list">
                <?php if (empty($topWarnings)): ?>
                <div class="list-item"><span class="code">None</span></div>
                <?php else: ?>
                <?php foreach ($topWarnings as $code => $count): ?>
                <div class="list-item">
                    <span class="code"><?= htmlspecialchars($code) ?></span>
                    <span class="count"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <h2>Recent Entries (<?= count($entries) ?>)</h2>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Attempt</th>
                <th>Score</th>
                <th>Status</th>
                <th>Forced</th>
                <th>Violations</th>
                <th>Warnings</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($entries, 0, 100) as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['timestamp'] ?? '-') ?></td>
                <td><?= htmlspecialchars($entry['attempt'] ?? '-') ?></td>
                <td><strong><?= htmlspecialchars($entry['score'] ?? 0) ?></strong></td>
                <td>
                    <?php
                    $st = $entry['status'] ?? 'REJECT';
                    $badgeClass = match($st) {
                        'EXCELLENT' => 'badge-excellent',
                        'GOOD' => 'badge-good',
                        'ACCEPTABLE' => 'badge-acceptable',
                        default => 'badge-reject'
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($st) ?></span>
                </td>
                <td>
                    <?php if (!empty($entry['forced_accept'])): ?>
                    <span class="badge badge-forced">YES</span>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
                <td><?= count($entry['violations'] ?? []) ?></td>
                <td><?= count($entry['warnings'] ?? []) ?></td>
                <td>
                    <details>
                        <summary>View</summary>
                        <pre><?= htmlspecialchars(json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </details>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>
</div>
</body>
</html>
