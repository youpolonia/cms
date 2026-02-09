<?php
/**
 * Page Template — Lens & Light Photography
 * Hero image header + centered content
 */

$_featuredImg = '';
if (!empty($page['featured_image_id'])) {
    try {
        $_pdo = \core\Database::connection();
        $_stmt = $_pdo->prepare("SELECT filename FROM media WHERE id = ? LIMIT 1");
        $_stmt->execute([$page['featured_image_id']]);
        $_row = $_stmt->fetch(PDO::FETCH_ASSOC);
        if ($_row) $_featuredImg = $_row['filename'];
    } catch (\Throwable $e) {}
}
?>

<?php if (!empty($_featuredImg)): ?>
<div class="page-hero" style="background-image: url('/uploads/media/<?= esc($_featuredImg) ?>');">
    <div class="page-hero-content">
        <h1><?= esc($page['title'] ?? 'Untitled') ?></h1>
    </div>
</div>
<?php else: ?>
<div class="page-hero" style="background: #1a1a1a;">
    <div class="page-hero-content">
        <h1><?= esc($page['title'] ?? 'Untitled') ?></h1>
    </div>
</div>
<?php endif; ?>

<div class="page-content">
    <div class="content-body"><?= $page["content"] ?? "" ?></div>
</div>
