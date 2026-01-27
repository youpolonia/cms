<?php
/**
 * Media Library 2.0 - Centralized Media Index
 * JSON-backed media library with ALT text management
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

if (!defined('MEDIA_LIBRARY_INDEX_FILE')) {
    define('MEDIA_LIBRARY_INDEX_FILE', CMS_ROOT . '/config/media_library_index.json');
}

/**
 * Return default empty index structure
 * @return array
 */
function media_library_default_index(): array
{
    return [];
}

/**
 * Load and normalize the media library index from JSON file
 * @return array Associative array keyed by media ID
 */
function media_library_load_index(): array
{
    if (!file_exists(MEDIA_LIBRARY_INDEX_FILE)) {
        return media_library_default_index();
    }

    $contents = @file_get_contents(MEDIA_LIBRARY_INDEX_FILE);
    if ($contents === false || trim($contents) === '') {
        return media_library_default_index();
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return media_library_default_index();
    }

    // Ensure it's an associative array with string keys
    $normalized = [];
    foreach ($decoded as $id => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $normalized[(string)$id] = [
            'id' => isset($entry['id']) ? (string)$entry['id'] : '',
            'path' => isset($entry['path']) ? (string)$entry['path'] : '',
            'basename' => isset($entry['basename']) ? (string)$entry['basename'] : '',
            'size' => isset($entry['size']) ? (int)$entry['size'] : 0,
            'mime' => isset($entry['mime']) ? (string)$entry['mime'] : '',
            'alt' => isset($entry['alt']) ? (string)$entry['alt'] : '',
            'updated' => isset($entry['updated']) && is_string($entry['updated']) && trim($entry['updated']) !== ''
                ? (string)$entry['updated']
                : null
        ];
    }

    return $normalized;
}

/**
 * Save the media library index to JSON file
 * @param array $index Associative array of media items
 * @return bool True on success, false on failure
 */
function media_library_save_index(array $index): bool
{
    try {
        // Normalize the index before saving
        $normalized = [];
        foreach ($index as $id => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $normalized[(string)$id] = [
                'id' => isset($entry['id']) ? (string)$entry['id'] : '',
                'path' => isset($entry['path']) ? (string)$entry['path'] : '',
                'basename' => isset($entry['basename']) ? (string)$entry['basename'] : '',
                'size' => isset($entry['size']) ? (int)$entry['size'] : 0,
                'mime' => isset($entry['mime']) ? (string)$entry['mime'] : '',
                'alt' => isset($entry['alt']) ? (string)$entry['alt'] : '',
                'updated' => isset($entry['updated']) && is_string($entry['updated']) && trim($entry['updated']) !== ''
                    ? (string)$entry['updated']
                    : null
            ];
        }

        $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        // Ensure directory exists
        $dir = dirname(MEDIA_LIBRARY_INDEX_FILE);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $result = @file_put_contents(MEDIA_LIBRARY_INDEX_FILE, $json . PHP_EOL, LOCK_EX);
        return $result !== false;
    } catch (Exception $e) {
        error_log('media_library_save_index: ' . $e->getMessage());
        return false;
    }
}

/**
 * Scan the uploads directory for media files and merge with existing index
 * @return array Associative array of media items keyed by ID
 */
function media_library_scan_files(): array
{
    $uploadsRoot = CMS_ROOT . '/uploads';

    if (!is_dir($uploadsRoot)) {
        return [];
    }

    $index = media_library_load_index();
    $merged = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mov'];

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $fullPath = $file->getPathname();
            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                continue;
            }

            // Build relative path from uploads root
            $relativePath = str_replace($uploadsRoot, '', $fullPath);
            $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

            // Skip thumbnails directory
            if (strpos($relativePath, 'media/thumbs/') === 0) {
                continue;
            }

            // Use relative path as stable ID
            $id = $relativePath;

            // Preserve existing ALT text and updated timestamp if available
            $alt = '';
            $updated = null;
            if (isset($index[$id])) {
                $alt = $index[$id]['alt'] ?? '';
                $updated = $index[$id]['updated'] ?? null;
            }

            // Build web path
            $webPath = '/uploads/' . $relativePath;

            // Get file info
            $size = @filesize($fullPath);
            if ($size === false) {
                $size = 0;
            }

            $mime = 'application/octet-stream';
            if (function_exists('mime_content_type')) {
                $detectedMime = @mime_content_type($fullPath);
                if ($detectedMime !== false) {
                    $mime = $detectedMime;
                }
            }

            $merged[$id] = [
                'id' => $id,
                'path' => $webPath,
                'basename' => basename($fullPath),
                'size' => $size,
                'mime' => $mime,
                'alt' => $alt,
                'updated' => $updated
            ];
        }
    } catch (Exception $e) {
        error_log('media_library_scan_files: ' . $e->getMessage());
    }

    return $merged;
}

/**
 * Get all media items, sorted by basename
 * @return array Indexed array of media items
 */
function media_library_get_all(): array
{
    $items = media_library_scan_files();

    // Convert to indexed array
    $itemsList = array_values($items);

    // Sort by basename (case-insensitive)
    usort($itemsList, function($a, $b) {
        return strcasecmp($a['basename'], $b['basename']);
    });

    return $itemsList;
}

/**
 * Update ALT text for a specific media item
 * @param string $id Media item ID (relative path)
 * @param string $alt New ALT text
 * @return bool True on success, false on failure
 */
function media_library_update_alt(string $id, string $alt): bool
{
    $index = media_library_load_index();

    // If ID not in index, try rebuilding from filesystem
    if (!isset($index[$id])) {
        $scanned = media_library_scan_files();
        if (!isset($scanned[$id])) {
            return false;
        }
        $index = $scanned;
    }

    // Update ALT text and timestamp
    $altText = trim($alt);
    $index[$id]['alt'] = $altText;
    $index[$id]['updated'] = gmdate('c');

    return media_library_save_index($index);
}
