<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

require_once __DIR__ . '/../../core/n8n_events.php';

require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

$delete_error = null;
$delete_success = null;
$mode = null;
$filename = null;

function validate_and_normalize_filename($raw_input) {
    if (!is_string($raw_input)) {
        return null;
    }

    $raw_input = trim($raw_input);
    $filename = basename($raw_input);

    if (empty($filename)) {
        return null;
    }

    if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        return null;
    }

    if ($filename[0] === '.') {
        return null;
    }

    if ($filename === '.htaccess') {
        return null;
    }

    if (!preg_match('/^[A-Za-z0-9._-]+$/i', $filename)) {
        return null;
    }

    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $filename = validate_and_normalize_filename($_POST['file'] ?? '');

    if ($filename === null) {
        $delete_error = 'Invalid filename.';
        $mode = 'result';
    } else {
        $media_dir = rtrim(CMS_ROOT, '/') . '/uploads/media';
        $full_path = $media_dir . '/' . $filename;

        if (!is_dir($media_dir)) {
            $delete_error = 'Media directory does not exist.';
            $mode = 'result';
        } elseif (dirname($full_path) !== $media_dir) {
            $delete_error = 'Invalid file path.';
            $mode = 'result';
        } elseif (!is_file($full_path)) {
            $delete_error = 'File does not exist.';
            $mode = 'result';
        } else {
            if (@unlink($full_path)) {
                $thumb_dir = $media_dir . '/thumbs';
                $thumb_path = $thumb_dir . '/' . $filename;
                $meta_path = $media_dir . '/' . $filename . '.meta.json';

                $had_thumb = (is_dir($thumb_dir) && is_file($thumb_path));
                $had_meta = is_file($meta_path);

                if ($had_thumb) {
                    @unlink($thumb_path);
                }

                if ($had_meta) {
                    @unlink($meta_path);
                }

                if (function_exists('n8n_trigger_event')) {
                    try {
                        $payload = [
                            'filename'      => $filename,
                            'url'           => '/uploads/media/' . $filename,
                            'had_thumbnail' => $had_thumb,
                            'had_alt_meta'  => $had_meta,
                            'deleted_at'    => date('c')
                        ];
                        n8n_trigger_event('media.deleted', $payload);
                    } catch (Throwable $e) {
                    }
                }

                $delete_success = 'File deleted successfully.';
                $mode = 'result';
            } else {
                $delete_error = 'Failed to delete file.';
                $mode = 'result';
            }
        }
    }
} else {
    $filename = validate_and_normalize_filename($_GET['file'] ?? '');

    if ($filename === null) {
        $delete_error = 'Invalid or missing filename.';
        $mode = 'error';
    } else {
        $mode = 'confirm';
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="container">
  <div style="margin-bottom: 20px;">
    <a href="index.php" class="btn btn-secondary">← Back to Media Library</a>
  </div>

  <h1>Delete Media</h1>

  <?php if ($delete_error !== null): ?>
    <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
      <strong>Error:</strong> <?php echo esc($delete_error); ?>
    </div>
  <?php endif; ?>

  <?php if ($delete_success !== null): ?>
    <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
      <strong>Success:</strong> <?php echo esc($delete_success); ?>
    </div>
  <?php endif; ?>

  <?php if ($mode === 'confirm'): ?>
    <div class="card">
      <div class="card-body">
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this file?</p>
        <p><strong>Filename:</strong> <code style="background-color: #f4f4f4; padding: 2px 6px; border-radius: 3px;"><?php echo esc($filename); ?></code></p>

        <form method="POST" action="delete.php" style="margin-top: 20px;">
          <input type="hidden" name="file" value="<?php echo esc($filename); ?>">
          <?php csrf_field(); ?>
          <button type="submit" class="btn btn-danger" style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Yes, delete file</button>
          <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($mode === 'result' || $mode === 'error'): ?>
    <div class="card">
      <div class="card-body">
        <p><a href="index.php" class="btn btn-primary">Back to Media Library</a></p>
      </div>
    </div>
  <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
