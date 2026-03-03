<?php
/**
 * Gallery page (frontend)
 * Variables: $page
 */
$theme = get_active_theme();
$themePath = '/themes/' . $theme;
$layoutFile = CMS_ROOT . '/themes/' . $theme . '/layout.php';

// Gallery data
$pdo = db();
$galleries = [];
$stmt = $pdo->query("SELECT * FROM galleries WHERE status = 'published' ORDER BY sort_order ASC, created_at DESC");
if ($stmt) {
    $galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all gallery images
$galleryImages = [];
$imgStmt = $pdo->query("SELECT gi.*, g.title as gallery_title FROM gallery_images gi 
    LEFT JOIN galleries g ON gi.gallery_id = g.id 
    ORDER BY gi.sort_order ASC, gi.created_at DESC");
if ($imgStmt) {
    foreach ($imgStmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $galleryImages[$img['gallery_id'] ?? 0][] = $img;
    }
}

$title = $page['title'] ?? 'Gallery';

// Use theme layout if available
if (file_exists($layoutFile)) {
    ob_start();
    ?>
    <section class="section gallery-section" style="padding:60px 0;">
        <div class="container">
            <div class="section-header" style="text-align:center;margin-bottom:40px;">
                <h1><?= h($title) ?></h1>
                <?php if (!empty($page['content'])): ?>
                    <div class="page-content"><?= $page['content'] ?></div>
                <?php endif; ?>
            </div>

            <?php if (empty($galleries) && empty($galleryImages)): ?>
                <p style="text-align:center;color:#64748b;">No gallery items yet.</p>
            <?php else: ?>
                <?php foreach ($galleries as $gallery): ?>
                    <div class="gallery-group" style="margin-bottom:48px;">
                        <h2 style="margin-bottom:16px;"><?= h($gallery['title'] ?? '') ?></h2>
                        <?php if (!empty($gallery['description'])): ?>
                            <p style="color:#64748b;margin-bottom:20px;"><?= h($gallery['description']) ?></p>
                        <?php endif; ?>
                        <div class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px;">
                            <?php foreach (($galleryImages[$gallery['id']] ?? []) as $img): ?>
                                <a href="<?= h($img['image_url'] ?? $img['url'] ?? '') ?>" class="gallery-item" data-lightbox style="display:block;border-radius:8px;overflow:hidden;aspect-ratio:4/3;">
                                    <img src="<?= h($img['image_url'] ?? $img['url'] ?? '') ?>" 
                                         alt="<?= h($img['alt_text'] ?? $img['title'] ?? '') ?>"
                                         loading="lazy"
                                         style="width:100%;height:100%;object-fit:cover;">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    // Fallback minimal layout
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . h($title) . '</title></head><body>';
    echo '<div style="max-width:1100px;margin:0 auto;padding:40px 20px;">';
    echo '<h1>' . h($title) . '</h1>';
    echo '<p style="color:#64748b;">Gallery page — activate a theme for full display.</p>';
    echo '</div></body></html>';
}
?>
