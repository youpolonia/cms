<?php
/**
 * Media API Endpoint
 * File upload and management
 */

function handle_media(string $method, ?string $id, ?string $action): void
{
    switch ($method) {
        case 'GET':
            if ($id) {
                get_media($id);
            } else {
                list_media();
            }
            break;

        case 'POST':
            upload_media();
            break;

        case 'DELETE':
            if (!$id) api_error('Media ID required', 400);
            delete_media($id);
            break;

        default:
            api_error('Method not allowed', 405);
    }
}

function list_media(): void
{
    require_once CMS_ROOT . '/core/media_library.php';

    $index = media_library_load_index();

    $type = $_GET['type'] ?? null;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));

    $items = array_values($index);

    // Filter by type
    if ($type) {
        $items = array_filter($items, fn($i) => strpos($i['mime'] ?? '', $type) === 0);
        $items = array_values($items);
    }

    $total = count($items);
    $offset = ($page - 1) * $limit;
    $items = array_slice($items, $offset, $limit);

    api_response([
        'items' => $items,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit),
        ],
    ]);
}

function get_media(string $id): void
{
    require_once CMS_ROOT . '/core/media_library.php';

    $index = media_library_load_index();

    if (!isset($index[$id])) {
        api_error('Media not found', 404);
    }

    api_response($index[$id]);
}

function upload_media(): void
{
    if (empty($_FILES['file'])) {
        api_error('No file uploaded', 400);
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        api_error('Upload error: ' . $file['error'], 400);
    }

    // Validate
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp',
                     'application/pdf', 'video/mp4', 'audio/mpeg'];
    $maxSize = 50 * 1024 * 1024; // 50MB

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        api_error('File type not allowed: ' . $mime, 400);
    }

    if ($file['size'] > $maxSize) {
        api_error('File too large. Max: 50MB', 400);
    }

    // Generate safe filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('media_') . '.' . strtolower($ext);
    $uploadDir = CMS_ROOT . '/uploads/' . date('Y/m');

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filepath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        api_error('Failed to save file', 500);
    }

    // Add to media library
    require_once CMS_ROOT . '/core/media_library.php';

    $relativePath = date('Y/m') . '/' . $filename;
    $id = media_library_add_entry([
        'path' => $relativePath,
        'basename' => $filename,
        'original_name' => $file['name'],
        'size' => $file['size'],
        'mime' => $mime,
        'alt' => '',
    ]);

    api_response([
        'id' => $id,
        'path' => $relativePath,
        'url' => '/uploads/' . $relativePath,
        'mime' => $mime,
        'size' => $file['size'],
        'message' => 'File uploaded successfully',
    ], 201);
}

function delete_media(string $id): void
{
    require_once CMS_ROOT . '/core/media_library.php';

    $index = media_library_load_index();

    if (!isset($index[$id])) {
        api_error('Media not found', 404);
    }

    $filepath = CMS_ROOT . '/uploads/' . $index[$id]['path'];

    if (file_exists($filepath)) {
        unlink($filepath);
    }

    unset($index[$id]);
    media_library_save_index($index);

    api_response(['message' => 'Media deleted successfully']);
}
