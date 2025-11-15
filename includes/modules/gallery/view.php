<?php
if (empty($gallery)) {
    exit('Gallery not found');
}

// Process images with EXIF data if handler exists
$processedImages = [];
foreach ($gallery['images'] as $image) {
    $exifData = [];
    if (class_exists('EXIFHandler')) {
        try {
            require_once __DIR__ . '/../../Media/EXIFHandler.php';
            $exifData = EXIFHandler::extract($_SERVER['DOCUMENT_ROOT'] . $image['src']);
            $exifData = EXIFHandler::formatForDisplay($exifData);
        } catch (Exception $e) {
            error_log("EXIF extraction failed: " . $e->getMessage());
        }
    }
    
    $processedImages[] = [
        'src' => $image['src'],
        'alt' => $image['alt'],
        'exif' => $exifData
    ];
}
?><!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($gallery['title']) ?></title>
</head>
<body>
    <main class="container">
        <?= render_back_button('Back to Gallery', '?action=gallery') ?>
        <?= render_heading(htmlspecialchars($gallery['title']), 1) ?>
        <div class="gallery">
            <p class="gallery-meta">Posted on <?= htmlspecialchars($gallery['date']) ?></p>
            <p class="gallery-description"><?= htmlspecialchars($gallery['description']) ?></p>
            <div class="gallery-images">
                <?php foreach ($processedImages as $image): ?> ?>
                    <div class="gallery-image">
                        <img src="<?= htmlspecialchars($image['src']) ?>" ?>
                             alt="<?= htmlspecialchars($image['alt']) ?>">
                        <?php if (!empty($image['exif'])): ?>
                            <div class="gallery-exif">
                                <h3>Image Details</h3>
                                <table>
                                    <?php foreach ($image['exif'] as $section => $data): ?>                                        <?php if (!empty($data)): ?> ?>
                                            <tr>
                                                <th colspan="2"><?= htmlspecialchars(ucfirst($section)) ?></th>
                                            </tr>
                                            <?php foreach ($data as $key => $value): ?> ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($key) ?></td>
                                                    <td><?= htmlspecialchars($value) ?></td>
                                                </tr>
                                            <?php endforeach; ?>                                        <?php endif; ?>                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
