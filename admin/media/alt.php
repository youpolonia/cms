<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

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

function is_valid_image_extension($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
}

$alt_error = null;
$alt_success = null;
$current_alt = null;
$filename = null;
$mode = null;

$media_dir = rtrim(CMS_ROOT, '/') . '/uploads/media';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $filename = validate_and_normalize_filename($_POST['file'] ?? '');

    if ($filename === null) {
        $alt_error = 'Invalid filename.';
        $mode = 'error';
    } else {
        $fullPath = $media_dir . '/' . $filename;

        if (!is_dir($media_dir)) {
            $alt_error = 'Media directory does not exist.';
            $mode = 'error';
        } elseif (dirname($fullPath) !== $media_dir) {
            $alt_error = 'Invalid file path.';
            $mode = 'error';
        } elseif (!is_file($fullPath)) {
            $alt_error = 'File does not exist.';
            $mode = 'error';
        } elseif (!is_valid_image_extension($filename)) {
            $alt_error = 'File is not a valid image.';
            $mode = 'error';
        } else {
            require_once CMS_ROOT . '/core/ai_hf.php';
            $config = ai_hf_config_load();

            if (!ai_hf_is_configured($config)) {
                $alt_error = 'Hugging Face is not configured.';
                $mode = 'error';
            } else {
                $prompt = 'Generate one short, SEO-friendly ALT text (max 80 characters) for an image file named "' . basename($filename) . '". Do not include the words "image" or "picture".';

                $options = [
                    'max_new_tokens' => 64,
                    'temperature' => 0.7
                ];

                $result = ai_hf_infer($config, $prompt, $options);

                if (!$result['ok'] || !isset($result['body']) || trim($result['body']) === '') {
                    $errorMsg = isset($result['error']) ? $result['error'] : 'AI generation failed.';
                    $alt_error = 'Failed to generate ALT text: ' . $errorMsg;
                    $mode = 'error';
                } else {
                    $alt = trim($result['body']);

                    if (strlen($alt) > 160) {
                        $alt = substr($alt, 0, 160);
                    }

                    if (trim($alt) === '') {
                        $alt_error = 'Generated ALT text is empty.';
                        $mode = 'error';
                    } else {
                        $metaPath = $fullPath . '.meta.json';
                        $metaData = [
                            'alt' => $alt,
                            'generated_at' => date('c'),
                            'provider' => 'huggingface'
                        ];

                        $jsonData = json_encode($metaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                        if ($jsonData === false) {
                            $alt_error = 'Failed to encode metadata.';
                            $mode = 'error';
                        } else {
                            $writeResult = @file_put_contents($metaPath, $jsonData . "\n", LOCK_EX);
                            if ($writeResult === false) {
                                $alt_error = 'Failed to save metadata file.';
                                $mode = 'error';
                            } else {
                                @chmod($metaPath, 0644);
                                $alt_success = 'ALT text generated successfully.';
                                $current_alt = $alt;
                                $mode = 'ready';
                            }
                        }
                    }
                }
            }
        }
    }
} else {
    $filename = validate_and_normalize_filename($_GET['file'] ?? '');

    if ($filename === null) {
        $alt_error = 'Invalid or missing filename.';
        $mode = 'error';
    } else {
        $fullPath = $media_dir . '/' . $filename;

        if (!is_dir($media_dir)) {
            $alt_error = 'Media directory does not exist.';
            $mode = 'error';
        } elseif (dirname($fullPath) !== $media_dir) {
            $alt_error = 'Invalid file path.';
            $mode = 'error';
        } elseif (!is_file($fullPath)) {
            $alt_error = 'File does not exist.';
            $mode = 'error';
        } elseif (!is_valid_image_extension($filename)) {
            $alt_error = 'File is not a valid image.';
            $mode = 'error';
        } else {
            $metaPath = $fullPath . '.meta.json';
            if (file_exists($metaPath)) {
                $metaJson = @file_get_contents($metaPath);
                if ($metaJson !== false) {
                    $metaData = @json_decode($metaJson, true);
                    if (is_array($metaData) && isset($metaData['alt'])) {
                        $current_alt = (string)$metaData['alt'];
                    }
                }
            }
            $mode = 'ready';
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

  <h1>Generate ALT Text</h1>

  <?php if ($alt_error !== null): ?>
    <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
      <strong>Error:</strong> <?php echo esc($alt_error); ?>
    </div>
  <?php endif; ?>

  <?php if ($alt_success !== null): ?>
    <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
      <strong>Success:</strong> <?php echo esc($alt_success); ?>
    </div>
  <?php endif; ?>

  <?php if ($mode === 'ready'): ?>
    <div class="card">
      <div class="card-body">
        <h2>Generate ALT for:</h2>
        <p><code style="background-color: #f4f4f4; padding: 4px 8px; border-radius: 3px; font-family: monospace;"><?php echo esc($filename); ?></code></p>

        <?php if ($current_alt !== null): ?>
          <div style="margin-top: 20px; margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #007bff; border-radius: 4px;">
            <h3 style="margin-top: 0; font-size: 1.1em;">Current ALT text:</h3>
            <p style="margin: 0; font-family: monospace; white-space: pre-wrap;"><?php echo esc($current_alt); ?></p>
          </div>
        <?php endif; ?>

        <form method="POST" action="alt.php" style="margin-top: 20px;">
          <input type="hidden" name="file" value="<?php echo esc($filename); ?>">
          <?php csrf_field(); ?>
          <button type="submit" class="btn btn-primary" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Generate ALT with AI</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($mode === 'error'): ?>
    <div class="card">
      <div class="card-body">
        <p><a href="index.php" class="btn btn-primary">Back to Media Library</a></p>
      </div>
    </div>
  <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php';
