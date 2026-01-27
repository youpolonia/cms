<?php
/**
 * Media Picker - AJAX endpoint for selecting media in editor
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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once CMS_ROOT . '/core/media_library.php';

if (!function_exists('esc')) {
    function esc(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

// Handle AJAX requests
$action = isset($_GET['action']) ? trim($_GET['action']) : 'list';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'list':
            // Get filter parameters
            $type = isset($_GET['type']) ? trim($_GET['type']) : 'all';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            $items = media_library_get_all();

            // Filter by type
            if ($type !== 'all') {
                $items = array_filter($items, function($item) use ($type) {
                    $mime = $item['mime'] ?? '';
                    switch ($type) {
                        case 'image':
                            return strpos($mime, 'image/') === 0;
                        case 'video':
                            return strpos($mime, 'video/') === 0;
                        case 'audio':
                            return strpos($mime, 'audio/') === 0;
                        case 'document':
                            return strpos($mime, 'application/') === 0 || strpos($mime, 'text/') === 0;
                        default:
                            return true;
                    }
                });
                $items = array_values($items);
            }

            // Filter by search term
            if ($search !== '') {
                $searchLower = strtolower($search);
                $items = array_filter($items, function($item) use ($searchLower) {
                    $basename = strtolower($item['basename'] ?? '');
                    $alt = strtolower($item['alt'] ?? '');
                    return strpos($basename, $searchLower) !== false || strpos($alt, $searchLower) !== false;
                });
                $items = array_values($items);
            }

            // Build response with thumbnail URLs
            $response = [];
            foreach ($items as $item) {
                $isImage = isset($item['mime']) && strpos($item['mime'], 'image/') === 0;
                $thumbPath = null;

                if ($isImage) {
                    // Check if thumbnail exists
                    $thumbFile = '/uploads/media/thumbs/' . $item['basename'];
                    $thumbFullPath = CMS_ROOT . $thumbFile;
                    if (file_exists($thumbFullPath)) {
                        $thumbPath = $thumbFile;
                    } else {
                        $thumbPath = $item['path'];
                    }
                }

                $response[] = [
                    'id' => $item['id'],
                    'path' => $item['path'],
                    'basename' => $item['basename'],
                    'size' => $item['size'],
                    'mime' => $item['mime'],
                    'alt' => $item['alt'] ?? '',
                    'thumb' => $thumbPath,
                    'is_image' => $isImage
                ];
            }

            echo json_encode([
                'success' => true,
                'items' => $response,
                'total' => count($response)
            ]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Media picker error: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred']);
}
