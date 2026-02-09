<?php
/**
 * Gallery Page Template — Universal (all themes)
 * Renders public galleries with per-gallery display templates:
 * grid | masonry | mosaic | carousel | justified
 * 
 * Shows only galleries matching the active theme.
 * Falls back to all public galleries if none match (user-created galleries).
 * 
 * Available: $page (array), $content (string)
 */

// Load galleries with images
$_galleries = [];
try {
    $_pdo = \core\Database::connection();
    $_activeTheme = function_exists('get_active_theme') ? get_active_theme() : 'default';
    
    // First try galleries for this theme + galleries without a theme (user-created)
    $_galStmt = $_pdo->prepare("
        SELECT * FROM galleries 
        WHERE is_public = 1 AND (theme = ? OR theme IS NULL OR theme = '')
        ORDER BY sort_order ASC, name ASC
    ");
    $_galStmt->execute([$_activeTheme]);
    $_galleries = $_galStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($_galleries as &$_gal) {
        $_imgStmt = $_pdo->prepare("
            SELECT * FROM gallery_images 
            WHERE gallery_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $_imgStmt->execute([$_gal['id']]);
        $_gal['images'] = $_imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($_gal);
} catch (\Throwable $e) {
    // Silently fail — show page content only
}
?>

<!-- Gallery CSS + JS -->
<link rel="stylesheet" href="/public/css/gallery-layouts.css">

<!-- Page Content (from editor) -->
<?php if (!empty($content) && trim(strip_tags($content)) !== ''): ?>
<section class="page-section">
    <div class="container">
        <div class="content-body"><?= $content ?></div>
    </div>
</section>
<?php endif; ?>

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
        <!-- CAROUSEL LAYOUT -->
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
        <!-- GRID / MASONRY / MOSAIC / JUSTIFIED -->
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
            <a href="/admin/galleries" style="display:inline-block;margin-top:16px;padding:10px 24px;background:var(--accent,#3b82f6);color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Manage Galleries</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script src="/public/js/gallery-layouts.js"></script>
