<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// --- Page Logic ---

// Whitelist of viewable log files
$log_directory = __DIR__ . '/../../logs/';
$whitelisted_logs = [
    'app_errors.log',
    'php_errors.log',
    'migrations.log',
    'extensions.log',
];

// Determine the selected log file
$selected_log_file = '';
if (isset($_GET['log_file'])) {
    if (in_array($_GET['log_file'], $whitelisted_logs, true)) {
        $selected_log_file = $_GET['log_file'];
    }
} elseif (!empty($whitelisted_logs)) {
    $selected_log_file = $whitelisted_logs[0];
}

// Variables for display
$log_content = '';
$log_file_size = 0;
$log_file_modified = '';
$error_message = '';
$tail_lines = 500;

if (!empty($selected_log_file)) {
    $log_file_path = $log_directory . $selected_log_file;

    if (file_exists($log_file_path) && is_readable($log_file_path)) {
        $log_file_size = filesize($log_file_path);
        if ($log_file_size > 0) {
            $log_file_modified = date('Y-m-d H:i:s', filemtime($log_file_path));
            
            // Efficiently read the tail of the file
            $file = new SplFileObject($log_file_path, 'r');
            $file->seek(PHP_INT_MAX);
            $last_line = $file->key();
            $lines = new LimitIterator($file, max(0, $last_line - $tail_lines), $last_line);
            $log_content = implode('', iterator_to_array($lines));
        } else {
            $log_content = 'Log file is empty.';
        }
    } else {
        $error_message = 'Log file not found or is not readable: ' . htmlspecialchars($selected_log_file, ENT_QUOTES, 'UTF-8');
    }
} else {
    $error_message = 'No log file selected.';
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="container">
    <h1>Admin Log Viewer</h1>
    <p class="muted">View the last <?php echo $tail_lines; ?> lines of key application logs.</p>
    
    <div class="card">
        <h2>Select Log File</h2>
        <form method="get" action="/admin/logs/">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <select name="log_file" id="log_file" style="flex-grow: 1; padding: 0.5rem;">
                    <?php foreach ($whitelisted_logs as $log): ?>
                        <option value="<?php echo htmlspecialchars($log, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($log === $selected_log_file) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($log, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn primary">View</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <?php if (!empty($selected_log_file)): ?>
            <h2>
                Viewing: <?php echo htmlspecialchars($selected_log_file, ENT_QUOTES, 'UTF-8'); ?>
            </h2>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php else: ?>
                <div style="display: flex; gap: 2rem; margin-bottom: 1rem; color: #6c757d; font-size: 0.9rem;">
                    <span>
                        <strong>Size:</strong> 
                        <?php echo round($log_file_size / 1024, 2); ?> KB
                    </span>
                    <span>
                        <strong>Last Modified:</strong>
                        <?php echo htmlspecialchars($log_file_modified, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <pre style="background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 1rem; border-radius: 4px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($log_content, ENT_QUOTES, 'UTF-8'); ?></pre>
            <?php endif; ?>
        <?php else: ?>
             <h2>No Log File to Display</h2>
             <p class="muted">Please select a log file from the dropdown above and click "View".</p>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
