<?php
/**
 * Media Browser API
 * Endpoint do przeglądania istniejących mediów z CMS
 *
 * @package JessieThemeBuilder
 * @since 1.1.0
 * @date 2026-02-03
 */

namespace JessieThemeBuilder;

// Security check
if (!defined('JTB_API_LOADED')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // Parameters
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(100, max(10, (int) ($_GET['per_page'] ?? 24)));
    $type = $_GET['type'] ?? 'all'; // all, image, video, audio, document
    $search = trim($_GET['search'] ?? '');
    $orderBy = in_array($_GET['order_by'] ?? 'date', ['date', 'name', 'size']) ? $_GET['order_by'] : 'date';
    $orderDir = strtoupper($_GET['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    // Build query
    $where = ['1=1'];
    $params = [];

    // Type filter
    $typeMap = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        'video' => ['video/mp4', 'video/webm', 'video/ogg'],
        'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    if ($type !== 'all' && isset($typeMap[$type])) {
        $placeholders = implode(',', array_fill(0, count($typeMap[$type]), '?'));
        $where[] = "mime_type IN ($placeholders)";
        $params = array_merge($params, $typeMap[$type]);
    }

    // Search filter
    if ($search !== '') {
        $where[] = "(filename LIKE ? OR alt_text LIKE ? OR title LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $whereClause = implode(' AND ', $where);

    // Order
    $orderMap = [
        'date' => 'created_at',
        'name' => 'filename',
        'size' => 'file_size',
    ];
    $orderColumn = $orderMap[$orderBy] ?? 'created_at';

    // Get database connection
    global $db;
    if (!$db) {
        // Try to get connection from CMS
        if (class_exists('\\CMS\\Core\\Database')) {
            $db = \CMS\Core\Database::getInstance()->getConnection();
        } elseif (function_exists('get_db')) {
            $db = get_db();
        } else {
            throw new \Exception('Database connection not available');
        }
    }

    // Check if media table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'media'");
    if ($tableCheck->rowCount() === 0) {
        // Try 'files' table as alternative
        $tableCheck = $db->query("SHOW TABLES LIKE 'files'");
        if ($tableCheck->rowCount() === 0) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => [],
                    'pagination' => [
                        'page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'total_pages' => 0,
                        'has_more' => false,
                    ],
                ],
                'message' => 'Media table not found'
            ]);
            exit;
        }
        $tableName = 'files';
    } else {
        $tableName = 'media';
    }

    // Adjust column names based on table structure
    $columns = getTableColumns($db, $tableName);

    // Map expected columns to actual columns
    $filenameCol = in_array('filename', $columns) ? 'filename' : (in_array('name', $columns) ? 'name' : 'filename');
    $filepathCol = in_array('filepath', $columns) ? 'filepath' : (in_array('path', $columns) ? 'path' : 'filepath');
    $mimeCol = in_array('mime_type', $columns) ? 'mime_type' : (in_array('mime', $columns) ? 'mime' : 'mime_type');
    $sizeCol = in_array('file_size', $columns) ? 'file_size' : (in_array('size', $columns) ? 'size' : 'file_size');
    $altCol = in_array('alt_text', $columns) ? 'alt_text' : (in_array('alt', $columns) ? 'alt' : 'alt_text');
    $titleCol = in_array('title', $columns) ? 'title' : $filenameCol;
    $dateCol = in_array('created_at', $columns) ? 'created_at' : (in_array('uploaded_at', $columns) ? 'uploaded_at' : 'created_at');
    $widthCol = in_array('width', $columns) ? 'width' : null;
    $heightCol = in_array('height', $columns) ? 'height' : null;

    // Update order column
    $orderMap = [
        'date' => $dateCol,
        'name' => $filenameCol,
        'size' => $sizeCol,
    ];
    $orderColumn = $orderMap[$orderBy] ?? $dateCol;

    // Update where clause for actual column names
    $whereClause = str_replace('filename', $filenameCol, $whereClause);
    $whereClause = str_replace('mime_type', $mimeCol, $whereClause);
    $whereClause = str_replace('alt_text', $altCol, $whereClause);

    // Count total
    $countSql = "SELECT COUNT(*) as total FROM $tableName WHERE $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = (int) $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

    // Calculate pagination
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
    $offset = ($page - 1) * $perPage;

    // Build select columns
    $selectCols = [
        'id',
        "$filenameCol as filename",
        "$filepathCol as filepath",
        "$mimeCol as mime_type",
        "$sizeCol as file_size",
    ];

    if ($widthCol) $selectCols[] = "$widthCol as width";
    if ($heightCol) $selectCols[] = "$heightCol as height";
    if ($altCol !== $filenameCol) $selectCols[] = "$altCol as alt_text";
    if ($titleCol !== $filenameCol) $selectCols[] = "$titleCol as title";
    $selectCols[] = "$dateCol as created_at";

    $selectStr = implode(', ', $selectCols);

    // Fetch items
    $sql = "SELECT $selectStr
            FROM $tableName
            WHERE $whereClause
            ORDER BY $orderColumn $orderDir
            LIMIT ? OFFSET ?";

    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Format items
    $baseUrl = rtrim($_ENV['APP_URL'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/');
    $formatted = [];

    foreach ($items as $item) {
        $filepath = $item['filepath'] ?? '';
        $filename = $item['filename'] ?? basename($filepath);
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $mimeType = $item['mime_type'] ?? '';

        // Build URL
        $url = $baseUrl . '/uploads/' . ltrim($filepath, '/');

        // Build thumbnail URL (if exists)
        $thumbPath = 'thumbnails/' . pathinfo($filepath, PATHINFO_FILENAME) . '_thumb.' . $ext;
        $thumbnail = $baseUrl . '/uploads/' . $thumbPath;

        // Fallback to original if thumbnail doesn't exist
        if (!file_exists(CMS_ROOT . '/uploads/' . $thumbPath)) {
            $thumbnail = $url;
        }

        $formatted[] = [
            'id' => (int) $item['id'],
            'filename' => $filename,
            'url' => $url,
            'thumbnail' => $thumbnail,
            'mime_type' => $mimeType,
            'file_size' => (int) ($item['file_size'] ?? 0),
            'file_size_formatted' => formatFileSize((int) ($item['file_size'] ?? 0)),
            'width' => isset($item['width']) ? (int) $item['width'] : null,
            'height' => isset($item['height']) ? (int) $item['height'] : null,
            'alt' => $item['alt_text'] ?? '',
            'title' => $item['title'] ?? $filename,
            'date' => $item['created_at'] ?? '',
            'type' => explode('/', $mimeType)[0] ?? 'other',
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $formatted,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
            ],
        ],
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load media library: ' . $e->getMessage(),
    ]);
}

/**
 * Get table columns
 */
function getTableColumns(\PDO $db, string $table): array
{
    $stmt = $db->query("DESCRIBE $table");
    $columns = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

/**
 * Format file size to human readable
 */
function formatFileSize(int $bytes): string
{
    if ($bytes === 0) return '0 B';

    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;

    while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
        $bytes /= 1024;
        $unitIndex++;
    }

    return round($bytes, 1) . ' ' . $units[$unitIndex];
}
