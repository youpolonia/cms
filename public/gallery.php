<?php
/**
 * Public Media Gallery
 *
 * Read-only image gallery that displays uploads from logs/uploads.log
 * Uses thumbnails from /uploads/media/thumbs/ when available.
 * No authentication, no forms, no sessions - fully public.
 */

// Bootstrap
require_once __DIR__ . '/../config.php';

$cmsRoot = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);

if (!function_exists('esc')) {
    function esc($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Read uploads log
$logPath = $cmsRoot . '/logs/uploads.log';
$entries = [];

if (file_exists($logPath) && is_readable($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $data = json_decode($line, true);

        // Only process image entries with valid names
        if (!$data || empty($data['name']) || empty($data['mime'])) {
            continue;
        }

        if (strpos($data['mime'], 'image/') !== 0) {
            continue;
        }

        // Check if original media file exists
        $mediaPath = $cmsRoot . '/uploads/media/' . $data['name'];
        if (!file_exists($mediaPath)) {
            continue;
        }

        // Normalize entry
        $entry = [
            'name' => $data['name'],
            'original' => $data['original'] ?? '',
            'mime' => $data['mime'],
            'ts' => $data['ts'] ?? null,
            'size' => $data['size'] ?? null,
            'thumb' => $data['thumb'] ?? null,
        ];

        // Determine preview URL (thumbnail or original)
        $mediaUrl = '/uploads/media/' . rawurlencode($entry['name']);
        $previewUrl = $mediaUrl;

        if (!empty($entry['thumb'])) {
            $thumbPath = $cmsRoot . '/uploads/media/thumbs/' . $entry['thumb'];
            if (file_exists($thumbPath)) {
                $previewUrl = '/uploads/media/thumbs/' . rawurlencode($entry['thumb']);
            }
        }

        $entry['mediaUrl'] = $mediaUrl;
        $entry['previewUrl'] = $previewUrl;

        $entries[] = $entry;
    }

    // Reverse to show newest first
    $entries = array_reverse($entries);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Gallery</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 2rem 1rem;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
            font-size: 2rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
            font-size: 1.1rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .gallery-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .gallery-item a {
            display: block;
            text-decoration: none;
        }

        .image-wrapper {
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #f0f0f0;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .meta {
            padding: 1rem;
        }

        .filename {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            word-break: break-word;
            font-size: 0.9rem;
        }

        .mime,
        .size,
        .ts {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .mime {
            font-family: monospace;
            background: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem 0.5rem;
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Media Gallery</h1>

        <?php if (empty($entries)): ?>
            <div class="empty-state">
                No media images available yet.
            </div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($entries as $entry): ?>
                    <div class="gallery-item">
                        <a href="<?= esc($entry['mediaUrl']) ?>" target="_blank" rel="noopener">
                            <div class="image-wrapper">
                                <img src="<?= esc($entry['previewUrl']) ?>"
                                     alt="<?= esc($entry['original'] ?: $entry['name']) ?>"
                                     loading="lazy">
                            </div>
                        </a>
                        <div class="meta">
                            <div class="filename"><?= esc($entry['original'] ?: $entry['name']) ?></div>
                            <div class="mime"><?= esc($entry['mime']) ?></div>
                            <?php if (!empty($entry['size'])): ?>
                                <div class="size">
                                    <?= esc(number_format(round($entry['size'] / 1024))) ?> KB
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($entry['ts'])): ?>
                                <div class="ts">
                                    <?= esc(date('Y-m-d H:i', strtotime($entry['ts']))) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
