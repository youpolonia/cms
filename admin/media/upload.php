<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/core/automation_rules.php';
require_once __DIR__ . '/../../core/n8n_events.php';

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

function generate_thumbnail($sourcePath, $thumbPath, $extension) {
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }

    if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
        return;
    }

    $source = null;
    if ($extension === 'jpg' || $extension === 'jpeg') {
        $source = @imagecreatefromjpeg($sourcePath);
    } elseif ($extension === 'png') {
        $source = @imagecreatefrompng($sourcePath);
    } elseif ($extension === 'gif') {
        $source = @imagecreatefromgif($sourcePath);
    } elseif ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
        $source = @imagecreatefromwebp($sourcePath);
    }

    if (!$source) {
        return;
    }

    $orig_width = imagesx($source);
    $orig_height = imagesy($source);
    $max_width = 320;

    if ($orig_width <= $max_width) {
        $new_width = $orig_width;
        $new_height = $orig_height;
    } else {
        $new_width = $max_width;
        $new_height = (int)(($max_width / $orig_width) * $orig_height);
    }

    $thumb = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);

    @imagejpeg($thumb, $thumbPath, 85);
    imagedestroy($thumb);
    imagedestroy($source);
}

$upload_error = null;
$upload_success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $max_size = 10 * 1024 * 1024;
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'mp4', 'webm', 'ogg', 'mp3', 'wav'];

    if (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'No file was uploaded or an upload error occurred.';
    } else {
        $upload_dir = __DIR__ . '/../../uploads/media';

        if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
            $upload_error = 'Upload directory is not available. Please contact the administrator.';
            error_log('Media upload failed: upload directory not writable or missing');
        } else {
            $file_size = $_FILES['media_file']['size'];

            if ($file_size > $max_size) {
                $upload_error = 'File is too large. Maximum size is 10 MB.';
            } else {
                $original_name = $_FILES['media_file']['name'];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                if (!in_array($extension, $allowed_extensions)) {
                    $upload_error = 'File type not allowed. Please upload a supported file format.';
                } else {
                    $mime_valid = true;

                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo) {
                            $detected_mime = finfo_file($finfo, $_FILES['media_file']['tmp_name']);
                            finfo_close($finfo);

                            $dangerous_mimes = ['application/x-httpd-php', 'application/x-php', 'text/x-php'];
                            if (in_array($detected_mime, $dangerous_mimes)) {
                                $mime_valid = false;
                            }

                            if ($extension === 'php' || strpos($detected_mime, 'php') !== false) {
                                $mime_valid = false;
                            }
                        }
                    }

                    if (!$mime_valid) {
                        $upload_error = 'File type validation failed. This file cannot be uploaded.';
                    } else {
                        $hash = substr(sha1(uniqid('', true)), 0, 8);
                        $new_filename = date('Ymd_His') . '_' . $hash . '.' . $extension;
                        $target_path = $upload_dir . '/' . $new_filename;

                        if (move_uploaded_file($_FILES['media_file']['tmp_name'], $target_path)) {
                            @chmod($target_path, 0644);

                            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            if (in_array($extension, $image_extensions)) {
                                $thumbs_dir = $upload_dir . '/thumbs';
                                if (!is_dir($thumbs_dir)) {
                                    @mkdir($thumbs_dir, 0755, true);
                                }

                                if (is_dir($thumbs_dir)) {
                                    $thumb_path = $thumbs_dir . '/' . $new_filename;
                                    generate_thumbnail($target_path, $thumb_path, $extension);
                                    @chmod($thumb_path, 0644);
                                }
                            }

                            if (function_exists('n8n_trigger_event')) {
                                try {
                                    $payload = [
                                        'filename'      => $new_filename,
                                        'original_name' => $original_name,
                                        'mime_type'     => isset($detected_mime) ? $detected_mime : null,
                                        'size'          => $file_size,
                                        'url'           => '/uploads/media/' . $new_filename,
                                        'is_image'      => in_array($extension, $image_extensions),
                                        'created_at'    => date('c')
                                    ];
                                    n8n_trigger_event('media.uploaded', $payload);
                                } catch (Throwable $e) {
                                }
                            }

                            automation_rules_handle_event('media.image_uploaded', [
                                'media_id' => null,
                                'filename' => $new_filename,
                                'original' => $original_name,
                                'mime'     => isset($detected_mime) ? $detected_mime : null,
                                'size'     => $file_size,
                                'path'     => '/uploads/media/' . $new_filename
                            ]);

                            $upload_success = 'File uploaded successfully: ' . $new_filename;
                        } else {
                            $upload_error = 'Failed to save the uploaded file. Please try again.';
                            error_log('Media upload failed: move_uploaded_file() failed');
                        }
                    }
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="container">
  <div style="margin-bottom: 20px;">
    <a href="index.php" class="btn btn-secondary">← Back to Media Library</a>
  </div>

  <h1>Upload Media</h1>

  <?php if ($upload_error): ?>
    <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
      <?php echo esc($upload_error); ?>
    </div>
  <?php elseif ($upload_success): ?>
    <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
      <?php echo esc($upload_success); ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 20px;">
          <label for="media_file" style="display: block; margin-bottom: 8px; font-weight: bold;">
            Select File
          </label>
          <input
            type="file"
            name="media_file"
            id="media_file"
            style="display: block; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
            required
          >
          <small class="muted" style="display: block; margin-top: 5px;">
            Supported formats: JPG, PNG, GIF, WEBP, SVG, PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, TXT, MP4, WEBM, OGG, MP3, WAV. Max size: 10 MB.
          </small>
        </div>

        <div style="margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Upload</button>
          <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <div style="margin-top: 20px;">
    <p class="muted">
      <strong>Note:</strong> Files are uploaded to the media library. Thumbnails and AI-generated ALT text will be added in future batches.
    </p>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
