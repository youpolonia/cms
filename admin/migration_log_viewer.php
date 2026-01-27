<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/includes/auth.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
csrf_boot('admin');

$ROOT_DIR = dirname(__DIR__);
$LOG_FILE = $ROOT_DIR . '/includes/migrations/migrations_log.json';

// Download branch
if (isset($_GET['download']) && $_GET['download'] == '1') {
    if (function_exists('ob_get_length') && ob_get_length()) { @ob_clean(); }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="migrations_log.json"');
    if (is_file($LOG_FILE) && is_readable($LOG_FILE)) {
        readfile($LOG_FILE);
    } else {
        echo '[]';
    }
    exit;
}

// Clear-log branch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_log'])) {
    csrf_validate_or_403();
    @file_put_contents($LOG_FILE, '[]', LOCK_EX);
    header('Location: migration_log_viewer.php?cleared=1');
    exit;
}

// Robust log load
$migrations = []; // Always initialize as an array

if (is_file($LOG_FILE) && is_readable($LOG_FILE)) {
    $raw = @file_get_contents($LOG_FILE);
    if ($raw !== false) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            foreach ($json as $item) {
                if (is_string($item)) {
                    $migrations[] = ['date'=>'', 'filename'=>$item, 'status'=>'success', 'notes'=>''];
                } elseif (is_array($item)) {
                    $migrations[] = [
                        'date'     => (string)($item['date'] ?? $item['ts'] ?? $item['time'] ?? ''),
                        'filename' => (string)($item['filename'] ?? $item['file'] ?? ''),
                        'status'   => (string)($item['status'] ?? 'success'),
                        'notes'    => (string)($item['notes'] ?? ''),
                    ];
                }
            }
        }
    }
}

// Guarantee array (protect against accidental null)
if (!is_array($migrations)) { $migrations = []; }

// Safe sort
if (count($migrations) > 1) {
    // Ensure migrations is always an array
    $migrations = is_array($migrations) ? $migrations : [];
    if (!empty($migrations)) {
        @usort($migrations, function(array $a, array $b): int {
            // Convert dates to timestamps for proper comparison
            $aTime = strtotime($a['date'] ?? '') ?: 0;
            $bTime = strtotime($b['date'] ?? '') ?: 0;
            
            // Primary sort: date descending (newest first)
            if ($aTime !== $bTime) {
                return $bTime <=> $aTime;
            }
            
            // Secondary sort: filename ascending
            return strcmp((string)($a['filename'] ?? ''), (string)($b['filename'] ?? ''));
        });
    }
}

// Filter logic
$filter = isset($_GET['status']) ? strtolower(trim((string)$_GET['status'])) : '';
if ($filter === 'success' || $filter === 'fail' || $filter === 'failure') {
    $want = ($filter === 'success') ? 'success' : 'fail';
    $migrations = array_values(array_filter($migrations, fn($m) => strtolower($m['status'] ?? '') === $want));
}

$pageTitle = "Migration Log Viewer";
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1200px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
        .controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .btn { padding: 0.6rem 1.2rem; border: none; border-radius: 5px; color: #fff; text-decoration: none; cursor: pointer; font-size: 0.9rem; }
        .btn-primary { background-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
        .filter-form { display: flex; gap: 0.5rem; align-items: center; }
        #status-filter { padding: 0.5rem; border-radius: 5px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem 1rem; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .status-success, .success { color: #155724; background-color: #d4edda; }
        .status-fail, .status-failure, .fail, .failure { color: #721c24; background-color: #f8d7da; }
        .notes { max-width: 400px; word-wrap: break-word; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .empty-log { text-align: center; padding: 2rem; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

        <?php if (isset($_GET['cleared']) && $_GET['cleared'] == '1'): ?>
            <div class="alert alert-success">Log file has been cleared successfully.</div>
        <?php endif; ?>
        <div class="controls">
            <div>
                <a href="migration_log_viewer.php?download=1" class="btn btn-primary">Download Log</a>
            </div>
            <form method="POST" action="migration_log_viewer.php" onsubmit="return confirm('Are you sure you want to clear the entire migration log? This cannot be undone.');" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <button type="submit" name="clear_log" class="btn btn-danger">Clear Log</button>
            </form>
        </div>

        <div class="controls">
             <form method="GET" action="migration_log_viewer.php" class="filter-form">
                <label for="status-filter">Filter by status:</label>
                <select name="status" id="status-filter" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="success" <?php echo ($filter === 'success') ? 'selected' : ''; ?>>Success</option>
                    <option value="fail" <?php echo ($filter === 'fail' || $filter === 'failure') ? 'selected' : ''; ?>>Fail</option>
                </select>
            </form>
        </div>

        <?php if (empty($migrations)): ?>
            <div class="empty-log">The migration log is empty.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($migrations as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m['date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($m['filename'] ?? ''); ?></td>
                            <?php $st = strtolower($m['status'] ?? ''); ?>
                            <td class="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ucfirst($st)) ?></td>
                            <td class="notes"><?php echo htmlspecialchars($m['notes'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
