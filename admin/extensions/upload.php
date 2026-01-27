<?php
require_once __DIR__ . '/../../includes/ext_zip_normalizer.php';
require_once __DIR__ . '/../../includes/ext_zip_bootstrap.php';
require_once __DIR__ . '/../../includes/ext_zip_safety.php';
require_once __DIR__ . '/../includes/flashmessage.php';
require_once __DIR__ . '/extensioninstaller.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/rate_limit.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
csrf_boot('admin');

if (!function_exists('ext_audit_log')) {
    function ext_audit_log(string $event, array $data = []): void {
        $root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__, 2);
        $logDir = $root . '/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
        $path = $logDir . '/extensions.log';
        if (file_exists($path) && filesize($path) > 1_000_000) {
            @rename($path, $path . '.' . gmdate('Ymd_His'));
        }
        $payload = [
            'ts'   => gmdate('c'),
            'event'=> $event,
        ] + $data;
        $line = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($line !== false) { @file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX); }
    }
}

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }

$EXT_DIR = CMS_ROOT . '/extensions';
$STAGING_DIR = CMS_ROOT . '/uploads/tmp';
$installer = new ExtensionInstaller($EXT_DIR, $STAGING_DIR);
$messages = FlashMessage::get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure log directory exists
    $logDir = CMS_ROOT . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/upload_debug.log';
    
    // Set explicit error log
    ini_set('error_log', $logFile);
    error_log("=== DEBUG: Starting upload process ===");
    error_log("DEBUG: Received POST upload request");
    csrf_validate_or_403();
    error_log("DEBUG: CSRF validation passed");
    error_log("DEBUG: Files received: " . print_r($_FILES, true));

    // optional rate-limit: 3 uploads / 10 min per IP
    if (!defined('DEV_MODE') || !DEV_MODE) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        list($ok,$rem,$rst) = rl_allow("ext-upload:$ip", 3, 600);
        if (!$ok) {
            if (!headers_sent()) header('HTTP/1.1 429 Too Many Requests');
            FlashMessage::add('Too many uploads. Please try again later.', FlashMessage::TYPE_ERROR);
            header('Location: upload.php', true, 303);
            exit;
        }
    }

    error_log("DEBUG: Checking uploaded files");
    if ((!isset($_FILES['extension']) || !is_array($_FILES['extension'])) &&
        (!isset($_FILES['extension_zip']) || !is_array($_FILES['extension_zip']))) {
        error_log("DEBUG: No valid file upload found");
        FlashMessage::add('No file uploaded.', FlashMessage::TYPE_ERROR);
        header('Location: upload.php', true, 303);
        exit;
    }

    $f = $_FILES['extension'] ?? $_FILES['extension_zip'];
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        FlashMessage::add('Upload error code: ' . (int)$f['error'], FlashMessage::TYPE_ERROR);
        header('Location: upload.php', true, 303);
        exit;
    }

    // enforce .zip extension (case-insensitive)
    $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        FlashMessage::add('Only .zip files are allowed.', FlashMessage::TYPE_ERROR);
        header('Location: upload.php', true, 303);
        exit;
    }

    // size guard (max 2 MB to match PHP default on this host)
    $maxBytes = 2 * 1024 * 1024;
    if (($f['size'] ?? 0) > $maxBytes) {
        FlashMessage::add('File too large (max 2 MB).', FlashMessage::TYPE_ERROR);
        header('Location: upload.php', true, 303);
        exit;
    }

    // basic MIME check if available
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $fi ? finfo_file($fi, $f['tmp_name']) : '';
        if ($fi) finfo_close($fi);
        $allowed = ['application/zip','application/x-zip-compressed','application/octet-stream'];
        if ($mime && !in_array($mime, $allowed, true)) {
            FlashMessage::add('Invalid ZIP MIME type.', FlashMessage::TYPE_ERROR);
            header('Location: upload.php', true, 303);
            exit;
        }
    }

    // Use secure extraction - let ext_zip_extract_safe handle all validation
    error_log("DEBUG: Starting ZIP processing");
    if (isset($_FILES['extension']) || isset($_FILES['extension_zip'])) {
        $ok = false;
        $err = null;
        $slugLog = null;
        $fileData = $_FILES['extension'] ?? $_FILES['extension_zip'];
        
        // Minimal slug extraction (no security validation - that's done in ext_zip_extract_safe)
        if (isset($fileData['tmp_name']) && is_file($fileData['tmp_name'])) {
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if (@$zip->open($fileData['tmp_name']) === true) {
                    // Quick slug extraction - no validation, ext_zip_extract_safe handles all security
                    $manifest = $zip->getFromName('extension.json');
                    if ($manifest !== false) {
                        $j = json_decode($manifest, true);
                        if (is_array($j) && isset($j['slug'])) {
                            $slugLog = (string)$j['slug'];
                        }
                    }
                    $zip->close();
                }
            }
        }
        
        // Use secure extraction - this handles ALL validation including security and slug verification
        if ($slugLog !== null) {
            list($ok, $err) = ext_zip_extract_safe(
                $fileData['tmp_name'], 
                $slugLog, 
                $EXT_DIR, 
                $STAGING_DIR, 
                ['MAX_FILES' => 500, 'MAX_TOTAL_BYTES' => 8 * 1024 * 1024]
            );
            
            if (!$ok) {
                FlashMessage::add("Upload failed: $err", FlashMessage::TYPE_ERROR);
                header('Location: upload.php', true, 303);
                exit;
            }
        } else {
            // No slug found - let ext_zip_extract_safe handle this with proper error
            FlashMessage::add("Upload failed: missing_manifest", FlashMessage::TYPE_ERROR);
            header('Location: upload.php', true, 303);
            exit;
        }
        
        // Success message
        if ($ok && $slugLog !== null) {
            FlashMessage::add("Extension '{$slugLog}' installed successfully", FlashMessage::TYPE_SUCCESS);
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $user = '';
        if (isset($_SESSION['user']['username'])) $user = (string)$_SESSION['user']['username'];
        elseif (isset($_SESSION['admin_username'])) $user = (string)$_SESSION['admin_username'];
        elseif (!empty($_SESSION['admin_logged_in'])) $user = 'admin';

        $data = [
            'ip'   => $ip,
            'user' => $user,
            'file' => $fileData['name'] ?? '',
            'size' => (int)($fileData['size'] ?? 0),
            'ua'   => $ua,
            'error'=> $err,
        ];
        if ($slugLog !== null) { $data['slug'] = $slugLog; }

        ext_audit_log($ok ? 'extension_install_ok' : 'extension_install_failed', $data);
error_log("EXT_INSTALL_FAIL: " . json_encode(["tmp"=>$fileData["tmp_name"]??null, "size"=>$fileData["size"]??null, "post_slug"=>$_POST["__ext_norm_slug"]??null]));
    }
    header('Location: upload.php', true, 303);
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title>Extension Upload</title>
    <style>
        .flash-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .flash-success {
            background-color: #d4edda;
            color: #155724;
        }
        .flash-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Upload Extension</h1>
    <p><a href="index.php">Back to Extensions</a></p>

    <?php foreach ($messages as $message): ?>
        <div class="flash-message flash-<?= $message['type'] ?>">
            <?= htmlspecialchars($message['message']) 
?>        </div>
    <?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="2000000">
        <input type="file" name="extension" accept=".zip" required>
        <button type="submit">Upload Extension</button>
    </form>
</body>
</html>
