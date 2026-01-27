<?php

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/export_package.php';

cms_session_start('admin');
csrf_boot('admin');
cms_register_error_handlers();

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Not available in production.');
}

cms_require_admin_role();

$exportResult = null;
$exportError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'build_package') {
    csrf_validate_or_403();

    $result = export_build_package();

    if (!$result['ok']) {
        $exportError = (string)$result['error'];
    } else {
        $exportResult = $result;
    }
}

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function format_bytes($bytes) {
    if ($bytes === null) {
        return 'Unknown';
    }
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    if ($bytes < 1048576) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return round($bytes / 1048576, 2) . ' MB';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One-Click Export (PHP Package)</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
    <style>
        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-error { background-color: #fee; border: 1px solid #fcc; color: #c33; }
        .alert-success { background-color: #efe; border: 1px solid #cfc; color: #3c3; }
        .form-group { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        .result-section { margin-top: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 4px; }
        .input-readonly { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5; }
        .help-text { font-size: 0.9em; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>One-Click Export (PHP Package)</h1>

        <p>
            This tool creates a ZIP archive containing the <strong>/public</strong>, <strong>/core</strong>,
            <strong>/modules</strong>, and <strong>/config</strong> directories. The resulting package can be
            deployed to shared hosting environments via FTP.
        </p>

        <p style="color: #d9534f;">
            <strong>Warning:</strong> The export package contains sensitive configuration files (including database credentials).
            Do not share this file publicly or store it in an insecure location.
        </p>

        <?php if ($exportError !== null): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo esc($exportError); ?>
            </div>
        <?php endif; ?>

        <?php if ($exportResult !== null && $exportError === null): ?>
            <div class="alert alert-success">
                <strong>Success:</strong> Export package created successfully.
                Size: <?php echo esc(format_bytes($exportResult['size'])); ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <form method="post" action="">
                <input type="hidden" name="action" value="build_package">
                <?php csrf_field(); ?>

                <button type="submit" class="btn btn-primary">Generate PHP Package (ZIP)</button>

                <p class="help-text">
                    This operation may take several seconds depending on the size of your CMS installation.
                    Only use this tool in development environments (DEV_MODE).
                </p>
            </form>
        </div>

        <?php if ($exportResult !== null && $exportError === null): ?>
            <div class="result-section">
                <h2>Download Package</h2>

                <div class="form-group">
                    <label for="package-url"><strong>Package URL:</strong></label>
                    <input type="text" id="package-url" class="input-readonly" readonly
                           value="<?php echo esc($exportResult['url']); ?>">
                </div>

                <div class="form-group">
                    <a href="<?php echo esc($exportResult['url']); ?>" class="btn btn-primary" download>
                        Download ZIP
                    </a>
                </div>

                <p class="help-text">
                    <strong>File location:</strong> <?php echo esc($exportResult['file']); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
