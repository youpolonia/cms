<?php
/**
 * Gallery Template — AI Generated
 */
$_galleries = [];
try {
    $_pdo = \core\Database::connection();
    $_activeTheme = function_exists('get_active_theme') ? get_active_theme() : 'default';
    $_galStmt = $_pdo->prepare("SELECT * FROM galleries WHERE is_public = 1 AND (theme = ? OR theme IS NULL OR theme = '') ORDER BY sort_order ASC, name ASC");
    $_galStmt->execute([$_activeTheme]);
    $_galleries = $_galStmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($_galleries as &$_gal) {
        $_imgStmt = $_pdo->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC, id ASC");
        $_imgStmt->execute([$_gal['id']]);
        $_gal['images'] = $_imgStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    unset($_gal);
} catch (\Throwable $e) {}

$_totalPhotos = 0;
foreach ($_galleries as $_g) $_totalPhotos += count($_g['images'] ?? []);
?>
<link rel="stylesheet" href="/public/css/gallery-layouts.css">

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title"><?= esc($page['title'] ?? 'Gallery') ?></h1>
        <?php if (!empty($page['content']) && trim(strip_tags($page['content'])) !== ''): ?>
        <div style="max-width:560px;margin:16px auto 0;opacity:0.7;font-size:1.05rem"><?= $page['content'] ?></div>
        <?php endif; ?>
        <?php if ($_totalPhotos > 0): ?>
        <p style="margin-top:16px;font-size:0.72rem;letter-spacing:0.15em;text-transform:uppercase;opacity:0.3"><?= $_totalPhotos ?> photos · <?= count($_galleries) ?> <?= count($_galleries) === 1 ? 'collection' : 'collections' ?></p>
        <?php endif; ?>
    </div>
</section>

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
                    <img src="/uploads/media/<?= esc($_img['filename']) ?>" alt="<?= esc($_img['title'] ?? '') ?>" loading="lazy">
                    <?php if (!empty($_img['title'])): ?><div class="gallery-caption"><span><?= esc($_img['title']) ?></span></div><?php endif; ?>
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
                <img src="/uploads/media/<?= esc($_img['filename']) ?>" alt="<?= esc($_img['title'] ?? '') ?>" loading="lazy">
                <?php if (!empty($_img['title'])): ?><div class="gallery-caption"><span><?= esc($_img['title']) ?></span></div><?php endif; ?>
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
    <div class="container" style="text-align:center;padding:100px 0">
        <p style="opacity:0.4;font-size:3rem;margin-bottom:16px">📷</p>
        <h2 style="font-weight:400;margin-bottom:8px">No Galleries Yet</h2>
        <p style="opacity:0.5">Galleries will appear here once created.</p>
    </div>
</section>
<?php endif; ?>
<script src="/public/js/gallery-layouts.js"></script>