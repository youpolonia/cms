<?php
/**
 * Gallery Page Template — Restaurant Theme
 * Elegant gallery with proper hero and restaurant-specific styling
 * 
 * Shows only galleries matching the active theme.
 * Available: $page (array), $content (string)
 */

// Load galleries with images
$_galleries = [];
try {
    $_pdo = \core\Database::connection();
    $_activeTheme = function_exists('get_active_theme') ? get_active_theme() : 'default';
    
    $_galStmt = $_pdo->prepare("
        SELECT * FROM galleries 
        WHERE is_public = 1 AND (theme = ? OR theme IS NULL OR theme = '')
        ORDER BY sort_order ASC, name ASC
    ");
    $_galStmt->execute([$_activeTheme]);
    $_galleries = $_galStmt->fetchAll(\PDO::FETCH_ASSOC);
    
    foreach ($_galleries as &$_gal) {
        $_imgStmt = $_pdo->prepare("
            SELECT * FROM gallery_images 
            WHERE gallery_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $_imgStmt->execute([$_gal['id']]);
        $_gal['images'] = $_imgStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    unset($_gal);
} catch (\Throwable $e) {
    // Silently fail
}

$_totalPhotos = 0;
foreach ($_galleries as $_g) {
    $_totalPhotos += count($_g['images'] ?? []);
}
?>

<link rel="stylesheet" href="/public/css/gallery-layouts.css">

<!-- Gallery Hero -->
<section class="gallery-hero">
    <div class="container">
        <span class="section-label">Gallery</span>
        <div class="ornament"><i class="fas fa-camera"></i></div>
        <h1><?= esc($page['title'] ?? 'Our Gallery') ?></h1>
        <?php if (!empty($page['content']) && trim(strip_tags($page['content'])) !== ''): ?>
        <div class="gallery-hero-desc"><?= $page['content'] ?></div>
        <?php endif; ?>
        <?php if ($_totalPhotos > 0): ?>
        <p class="gallery-hero-stats"><?= $_totalPhotos ?> photos · <?= count($_galleries) ?> <?= count($_galleries) === 1 ? 'collection' : 'collections' ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Galleries -->
<?php if (!empty($_galleries)): ?>
<?php foreach ($_galleries as $_gallery): ?>
<?php if (!empty($_gallery['images'])): ?>
<?php $_template = $_gallery['display_template'] ?? 'grid'; ?>
<section class="gallery-section" id="gallery-<?= esc($_gallery['slug']) ?>">
    <div class="container">
        <div class="gallery-header">
            <h2><?= esc($_gallery['name']) ?></h2>
            <?php if (!empty($_gallery['description'])): ?>
            <p class="gallery-desc"><?= esc($_gallery['description']) ?></p>
            <?php endif; ?>
            <span class="gallery-count"><?= count($_gallery['images']) ?> photos</span>
        </div>

        <?php if ($_template === 'carousel'): ?>
        <div class="gallery-layout-carousel">
            <div class="gallery-carousel-track">
                <?php foreach ($_gallery['images'] as $_img): ?>
                <div class="gallery-item" data-src="/uploads/media/<?= esc($_img['filename']) ?>">
                    <img src="/uploads/media/<?= esc($_img['filename']) ?>" 
                         alt="<?= esc($_img['title'] ?? $_img['original_name'] ?? '') ?>" loading="lazy">
                    <?php if (!empty($_img['title'])): ?>
                    <div class="gallery-caption"><span><?= esc($_img['title']) ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="gallery-carousel-nav">
                <button class="gallery-carousel-btn" data-dir="prev" aria-label="Previous">&#8249;</button>
                <button class="gallery-carousel-btn" data-dir="next" aria-label="Next">&#8250;</button>
            </div>
        </div>

        <?php else: ?>
        <div class="gallery-layout-<?= esc($_template) ?>">
            <?php foreach ($_gallery['images'] as $_img): ?>
            <div class="gallery-item" data-src="/uploads/media/<?= esc($_img['filename']) ?>">
                <img src="/uploads/media/<?= esc($_img['filename']) ?>" 
                     alt="<?= esc($_img['title'] ?? $_img['original_name'] ?? '') ?>" loading="lazy">
                <?php if (!empty($_img['title'])): ?>
                <div class="gallery-caption"><span><?= esc($_img['title']) ?></span></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php endforeach; ?>

<?php else: ?>
<section class="gallery-section">
    <div class="container">
        <div class="gallery-empty-state">
            <div class="icon">📷</div>
            <h2>No Galleries Yet</h2>
            <p>Galleries will appear here once created in the admin panel.</p>
            <?php if (function_exists('cms_is_admin_logged_in') && cms_is_admin_logged_in()): ?>
            <a href="/admin/galleries" class="btn btn-outline" style="margin-top:20px">Manage Galleries</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script src="/public/js/gallery-layouts.js"></script>
