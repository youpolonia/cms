<?php
/**
 * Media List API Endpoint
 * GET /api/jtb/media-list
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication is checked in router.php

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(100, max(10, intval($_GET['per_page']))) : 30;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // all, image, video, document

// Allowed file extensions by type
$typeExtensions = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
    'video' => ['mp4', 'webm', 'ogg', 'mov'],
    'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']
];

// Build upload directory path
$uploadDir = CMS_ROOT . '/uploads/jtb';

// Check if upload directory exists
if (!is_dir($uploadDir)) {
    echo json_encode([
        'success' => true,
        'data' => [
            'files' => [],
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => 0
        ]
    ]);
    exit;
}

// Collect all files recursively
$files = [];
$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($uploadDir, \RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $extension = strtolower($file->getExtension());
        $filename = $file->getFilename();

        // Filter by type
        if ($type !== 'all') {
            if (!isset($typeExtensions[$type]) || !in_array($extension, $typeExtensions[$type])) {
                continue;
            }
        }

        // Filter by search
        if ($search !== '' && stripos($filename, $search) === false) {
            continue;
        }

        // Get relative path from uploads directory
        $relativePath = str_replace(CMS_ROOT, '', $file->getPathname());
        $relativePath = str_replace('\\', '/', $relativePath); // Windows compatibility

        // Determine file type
        $fileType = 'document';
        foreach ($typeExtensions as $t => $exts) {
            if (in_array($extension, $exts)) {
                $fileType = $t;
                break;
            }
        }

        // Get image dimensions
        $width = 0;
        $height = 0;
        if ($fileType === 'image' && $extension !== 'svg') {
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $files[] = [
            'url' => $relativePath,
            'filename' => $filename,
            'size' => $file->getSize(),
            'type' => $fileType,
            'mime' => $mimeType,
            'width' => $width,
            'height' => $height,
            'modified' => $file->getMTime()
        ];
    }
}

// Sort by modified date (newest first)
usort($files, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

// Pagination
$total = count($files);
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;
$files = array_slice($files, $offset, $perPage);

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'files' => $files,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages
    ]
]);
