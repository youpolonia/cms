<?php
/**
 * Single Article Template — Lens & Light Photography
 * Full-bleed header image, narrow content column, dramatic typography
 */

$_featuredImg = '';
if (!empty($article['featured_image_id'])) {
    try {
        $_pdo = \core\Database::connection();
        $_stmt = $_pdo->prepare("SELECT filename FROM media WHERE id = ? LIMIT 1");
        $_stmt->execute([$article['featured_image_id']]);
        $_row = $_stmt->fetch(PDO::FETCH_ASSOC);
        if ($_row) $_featuredImg = $_row['filename'];
    } catch (\Throwable $e) {}
}

$_pubDate = !empty($article['published_at']) ? date('F j, Y', strtotime($article['published_at'])) : '';
?>

<?php if (!empty($_featuredImg)): ?>
<div class="article-hero" style="background-image: url('/uploads/media/<?= esc($_featuredImg) ?>');">
    <div class="article-hero-content">
        <?php if ($_pubDate): ?>
        <span class="article-meta"><?= $_pubDate ?></span>
        <?php endif; ?>
        <h1><?= esc($article['title'] ?? 'Untitled') ?></h1>
    </div>
</div>
<?php else: ?>
<div class="article-hero" style="background:#1a1a1a;">
    <div class="article-hero-content">
        <?php if ($_pubDate): ?>
        <span class="article-meta"><?= $_pubDate ?></span>
        <?php endif; ?>
        <h1><?= esc($article['title'] ?? 'Untitled') ?></h1>
    </div>
</div>
<?php endif; ?>

<article class="article-body">
    <?= $article["content"] ?? "" ?>
</article>

<nav class="article-nav">
    <a href="/journal">← Back to Journal</a>
    <a href="/">Home</a>
</nav>
