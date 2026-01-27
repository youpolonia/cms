<?php
/**
 * Media Edit - Edit media file metadata
 *
 * @package CMS
 * @subpackage Admin/Media
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

require_once CMS_ROOT . '/core/media_library.php';

if (!function_exists('esc')) {
    function esc(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

function format_filesize(int $bytes): string {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    return round($bytes / 1024, 2) . ' KB';
}

function validate_media_id(string $raw): ?string {
    $raw = trim($raw);
    if (empty($raw)) {
        return null;
    }

    // Prevent path traversal
    if (strpos($raw, '..') !== false) {
        return null;
    }

    // Must start with media/ for our structure
    if (strpos($raw, 'media/') !== 0) {
        return null;
    }

    return $raw;
}

$error_message = null;
$success_message = null;
$media_item = null;

// Get media ID from query string
$media_id = isset($_GET['id']) ? validate_media_id($_GET['id']) : null;

if ($media_id === null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error_message = 'Invalid or missing media ID.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $media_id = isset($_POST['id']) ? validate_media_id($_POST['id']) : null;
    $new_alt = isset($_POST['alt']) ? trim($_POST['alt']) : '';

    if ($media_id === null) {
        $error_message = 'Invalid media ID.';
    } else {
        // Update ALT text using media library function
        if (media_library_update_alt($media_id, $new_alt)) {
            $success_message = 'Media metadata updated successfully.';
        } else {
            $error_message = 'Failed to update media metadata.';
        }
    }
}

// Load media item data
if ($media_id !== null && $error_message === null) {
    $all_items = media_library_get_all();

    foreach ($all_items as $item) {
        if ($item['id'] === $media_id) {
            $media_item = $item;
            break;
        }
    }

    if ($media_item === null) {
        $error_message = 'Media file not found.';
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>
<main class="container">
    <div style="margin-bottom: 20px;">
        <a href="index.php" class="btn btn-secondary">&larr; Back to Media Library</a>
    </div>

    <h1>Edit Media</h1>

    <?php if ($error_message !== null): ?>
        <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
            <strong>Error:</strong> <?php echo esc($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message !== null): ?>
        <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 4px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
            <strong>Success:</strong> <?php echo esc($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($media_item !== null): ?>
        <div class="card">
            <div class="card-body">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <!-- Preview Column -->
                    <div style="flex: 0 0 300px;">
                        <h3 style="margin-top: 0;">Preview</h3>
                        <?php
                        $is_image = isset($media_item['mime']) && strpos($media_item['mime'], 'image/') === 0;
                        ?>
                        <?php if ($is_image): ?>
                            <img src="<?php echo esc($media_item['path']); ?>"
                                 alt="<?php echo esc($media_item['alt'] ?? ''); ?>"
                                 style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                <span style="font-size: 48px;">📄</span>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 15px;">
                            <a href="<?php echo esc($media_item['path']); ?>" target="_blank" class="btn btn-secondary btn-sm">
                                Open in New Tab
                            </a>
                        </div>
                    </div>

                    <!-- Details Column -->
                    <div style="flex: 1; min-width: 300px;">
                        <h3 style="margin-top: 0;">File Details</h3>

                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #ddd; width: 120px;">Filename:</th>
                                <td style="padding: 8px 0; border-bottom: 1px solid #ddd; font-family: monospace;">
                                    <?php echo esc($media_item['basename']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #ddd;">Path:</th>
                                <td style="padding: 8px 0; border-bottom: 1px solid #ddd; font-family: monospace; font-size: 12px; color: #666;">
                                    <?php echo esc($media_item['id']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #ddd;">Size:</th>
                                <td style="padding: 8px 0; border-bottom: 1px solid #ddd;">
                                    <?php echo esc(format_filesize($media_item['size'])); ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #ddd;">MIME Type:</th>
                                <td style="padding: 8px 0; border-bottom: 1px solid #ddd;">
                                    <?php echo esc($media_item['mime']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #ddd;">Last Updated:</th>
                                <td style="padding: 8px 0; border-bottom: 1px solid #ddd;">
                                    <?php if (!empty($media_item['updated'])): ?>
                                        <?php echo esc($media_item['updated']); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>

                        <h3 style="margin-top: 30px;">Edit Metadata</h3>

                        <form method="post" action="edit.php">
                            <input type="hidden" name="id" value="<?php echo esc($media_item['id']); ?>">
                            <?php csrf_field(); ?>

                            <div style="margin-bottom: 20px;">
                                <label for="alt" style="display: block; margin-bottom: 8px; font-weight: bold;">
                                    ALT Text
                                </label>
                                <input type="text"
                                       name="alt"
                                       id="alt"
                                       value="<?php echo esc($media_item['alt'] ?? ''); ?>"
                                       class="form-control"
                                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"
                                       placeholder="Describe the image for accessibility...">
                                <small style="display: block; margin-top: 5px; color: #666;">
                                    Provides text description for screen readers and SEO.
                                </small>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="delete.php?file=<?php echo urlencode($media_item['basename']); ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this file?');">
                                    Delete File
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Info -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 style="margin: 0;">Copy URL</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text"
                           id="media-url"
                           value="<?php echo esc($media_item['path']); ?>"
                           readonly
                           style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" onclick="copyMediaUrl()">
                        Copy URL
                    </button>
                </div>
                <small style="display: block; margin-top: 8px; color: #666;">
                    Use this URL in your content to embed this media file.
                </small>
            </div>
        </div>

        <script>
        function copyMediaUrl() {
            var urlInput = document.getElementById('media-url');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                alert('URL copied to clipboard!');
            } catch (err) {
                alert('Failed to copy URL. Please select and copy manually.');
            }
        }
        </script>
    <?php else: ?>
        <div class="card">
            <div class="card-body" style="padding: 40px; text-align: center;">
                <p style="color: #666;">Media file not found or invalid ID provided.</p>
                <a href="index.php" class="btn btn-primary">Back to Media Library</a>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require_once CMS_ROOT . '/admin/includes/footer.php';
