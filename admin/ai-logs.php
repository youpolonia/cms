<?php
/**
 * AI Logs Viewer
 * View and analyze AI operations, API calls, and errors
 */

if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) {
        die('Cannot determine CMS_ROOT');
    }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Log file paths
$logFiles = [
    'ai_operations' => CMS_ROOT . '/logs/ai_operations.log',
    'ai_errors' => CMS_ROOT . '/logs/ai_errors.log',
    'hf_api' => CMS_ROOT . '/logs/hf_api.log',
    'ai_seo' => CMS_ROOT . '/logs/ai_seo.log',
    'ai_content' => CMS_ROOT . '/logs/ai_content.log',
];

/**
 * Read log file with pagination
 */
function read_log_file(string $path, int $lines = 100, int $offset = 0): array
{
    if (!file_exists($path)) {
        return ['lines' => [], 'total' => 0, 'exists' => false];
    }

    $allLines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($allLines === false) {
        return ['lines' => [], 'total' => 0, 'exists' => true, 'error' => 'Cannot read file'];
    }

    // Reverse to show newest first
    $allLines = array_reverse($allLines);
    $total = count($allLines);

    $slice = array_slice($allLines, $offset, $lines);

    return [
        'lines' => $slice,
        'total' => $total,
        'exists' => true,
    ];
}

/**
 * Parse log line to extract components
 */
function parse_log_line(string $line): array
{
    // Try to parse common log formats: [YYYY-MM-DD HH:MM:SS] [LEVEL] Message
    $parsed = [
        'raw' => $line,
        'timestamp' => null,
        'level' => 'INFO',
        'message' => $line,
    ];

    // Pattern: [2024-01-01 12:00:00] [ERROR] Message
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s*\[(\w+)\]\s*(.*)$/', $line, $matches)) {
        $parsed['timestamp'] = $matches[1];
        $parsed['level'] = strtoupper($matches[2]);
        $parsed['message'] = $matches[3];
    }
    // Pattern: 2024-01-01 12:00:00 - LEVEL - Message
    elseif (preg_match('/^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s*-\s*(\w+)\s*-\s*(.*)$/', $line, $matches)) {
        $parsed['timestamp'] = $matches[1];
        $parsed['level'] = strtoupper($matches[2]);
        $parsed['message'] = $matches[3];
    }
    // Pattern: [2024-01-01 12:00:00] Message
    elseif (preg_match('/^\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s*(.*)$/', $line, $matches)) {
        $parsed['timestamp'] = $matches[1];
        $parsed['message'] = $matches[2];
    }

    return $parsed;
}

/**
 * Get level badge class
 */
function get_level_badge(string $level): string
{
    return match(strtoupper($level)) {
        'ERROR', 'CRITICAL', 'FATAL' => 'danger',
        'WARNING', 'WARN' => 'warning',
        'SUCCESS', 'OK' => 'success',
        'DEBUG' => 'secondary',
        default => 'info',
    };
}

/**
 * Get log file stats
 */
function get_log_stats(string $path): array
{
    if (!file_exists($path)) {
        return ['exists' => false, 'size' => 0, 'modified' => null, 'lines' => 0];
    }

    $size = filesize($path);
    $modified = filemtime($path);
    $lines = 0;

    $handle = fopen($path, 'r');
    if ($handle) {
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line !== false && trim($line) !== '') {
                $lines++;
            }
        }
        fclose($handle);
    }

    return [
        'exists' => true,
        'size' => $size,
        'size_human' => $size > 1048576 ? round($size / 1048576, 2) . ' MB' : round($size / 1024, 2) . ' KB',
        'modified' => $modified ? date('Y-m-d H:i:s', $modified) : null,
        'lines' => $lines,
    ];
}

// Handle clear log action
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = $_POST['action'] ?? '';
    $logKey = $_POST['log_key'] ?? '';

    if ($action === 'clear' && isset($logFiles[$logKey])) {
        $path = $logFiles[$logKey];
        if (file_exists($path)) {
            file_put_contents($path, '');
            $message = 'Log file cleared successfully.';
            $messageType = 'success';
        }
    }
}

// Get selected log
$selectedLog = $_GET['log'] ?? 'ai_operations';
if (!isset($logFiles[$selectedLog])) {
    $selectedLog = 'ai_operations';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$logData = read_log_file($logFiles[$selectedLog], $perPage, $offset);
$totalPages = ceil(($logData['total'] ?? 0) / $perPage);

// Get stats for all logs
$allStats = [];
foreach ($logFiles as $key => $path) {
    $allStats[$key] = get_log_stats($path);
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <div>
                <h1 class="mb-0">AI Logs</h1>
                <p class="text-muted mb-0">Monitor AI operations, API calls, and errors</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Log Files Overview -->
        <div class="row mb-4">
            <?php foreach ($logFiles as $key => $path):
                $stats = $allStats[$key];
                $isActive = ($key === $selectedLog);
            ?>
                <div class="col-md-4 col-lg-2 mb-3">
                    <a href="?log=<?= esc($key) ?>" class="text-decoration-none">
                        <div class="card h-100 <?= $isActive ? 'border-primary' : '' ?>">
                            <div class="card-body text-center py-3">
                                <div class="h5 mb-1 <?= $isActive ? 'text-primary' : '' ?>">
                                    <?= ucwords(str_replace('_', ' ', $key)) ?>
                                </div>
                                <?php if ($stats['exists']): ?>
                                    <small class="text-muted d-block"><?= number_format($stats['lines']) ?> entries</small>
                                    <small class="text-muted"><?= esc($stats['size_human']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">No log file</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Selected Log Viewer -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?= ucwords(str_replace('_', ' ', $selectedLog)) ?> Log
                    <?php if ($logData['exists'] ?? false): ?>
                        <span class="badge bg-secondary"><?= number_format($logData['total']) ?> entries</span>
                    <?php endif; ?>
                </h5>
                <?php if ($logData['exists'] ?? false): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Clear this log file?');">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="log_key" value="<?= esc($selectedLog) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Clear Log</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!($logData['exists'] ?? false)): ?>
                    <div class="alert alert-info mb-0">
                        <strong>Log file not found.</strong><br>
                        Path: <code><?= esc($logFiles[$selectedLog]) ?></code><br>
                        The log file will be created when AI operations are performed.
                    </div>
                <?php elseif (empty($logData['lines'])): ?>
                    <div class="alert alert-info mb-0">
                        Log file is empty.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Timestamp</th>
                                    <th style="width: 80px;">Level</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logData['lines'] as $line):
                                    $parsed = parse_log_line($line);
                                ?>
                                    <tr>
                                        <td class="text-muted">
                                            <?= $parsed['timestamp'] ? esc($parsed['timestamp']) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= get_level_badge($parsed['level']) ?>">
                                                <?= esc($parsed['level']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code style="font-size: 0.8rem; word-break: break-all;">
                                                <?= esc($parsed['message']) ?>
                                            </code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?log=<?= esc($selectedLog) ?>&page=<?= $page - 1 ?>">Prev</a>
                                    </li>
                                <?php endif; ?>

                                <li class="page-item disabled">
                                    <span class="page-link">Page <?= $page ?> of <?= $totalPages ?></span>
                                </li>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?log=<?= esc($selectedLog) ?>&page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Log File Info -->
        <?php if ($logData['exists'] ?? false): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">File Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width: 150px;">Path:</th>
                            <td><code><?= esc($logFiles[$selectedLog]) ?></code></td>
                        </tr>
                        <tr>
                            <th>Size:</th>
                            <td><?= esc($allStats[$selectedLog]['size_human']) ?></td>
                        </tr>
                        <tr>
                            <th>Last Modified:</th>
                            <td><?= esc($allStats[$selectedLog]['modified'] ?? 'Unknown') ?></td>
                        </tr>
                        <tr>
                            <th>Total Entries:</th>
                            <td><?= number_format($allStats[$selectedLog]['lines']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
