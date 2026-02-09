<?php
/**
 * Articles Template — Lens & Light Photography
 * Photo-blog grid: large images dominate, minimal text
 */

$_articles = [];
try {
    $_pdo = \core\Database::connection();
    $_stmt = $_pdo->query("
        SELECT a.*, 
            (SELECT filename FROM media WHERE id = a.featured_image_id LIMIT 1) as featured_image
        FROM articles a 
        WHERE a.status = 'published' 
        ORDER BY a.published_at DESC
    ");
    $_articles = $_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}
?>

<section class="articles-section">
    <div class="section-header fade-in">
        <span class="section-label">Journal</span>
        <h2 class="section-title">Photo Journal</h2>
    </div>

    <?php if (!empty($_articles)): ?>
    <div class="articles-grid">
        <?php foreach ($_articles as $_a): ?>
        <a href="/article/<?= esc($_a['slug']) ?>" class="article-card fade-in">
            <div class="article-card-image">
                <?php if (!empty($_a['featured_image'])): ?>
                <img src="/uploads/media/<?= esc($_a['featured_image']) ?>" alt="<?= esc($_a['title']) ?>" loading="lazy">
                <?php else: ?>
                <div style="width:100%;height:100%;background:#1a1a1a;"></div>
                <?php endif; ?>
            </div>
            <div class="article-card-meta"><?= date('M j, Y', strtotime($_a['published_at'])) ?></div>
            <h3><?= esc($_a['title']) ?></h3>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:120px 24px;color:#888;">
        <p style="font-family:'Space Mono',monospace;letter-spacing:2px;text-transform:uppercase;font-size:0.85rem;">No entries yet</p>
    </div>
    <?php endif; ?>
</section>
