<?php
/**
 * Jessie Theme - Single Article Template
 * Individual blog post/article view
 *
 * @var array $article Article data array
 */
?>
<article class="article-single">
    <div class="container">
        <a href="/blog" class="back-link">← Back to Blog</a>

        <header class="article-header">
            <?php if (!empty($article['category_name'])): ?>
            <span class="category-badge"><?= htmlspecialchars($article['category_name']) ?></span>
            <?php endif; ?>
            <h1><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
                <span class="date"><?= date('F j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
                <span class="views"><?= (int)($article['views'] ?? 0) ?> views</span>
            </div>
        </header>

        <?php if (!empty($article['featured_image'])): ?>
        <div class="featured-image">
            <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="article-content">
            <?= $article['content'] ?>
        </div>

        <footer class="article-footer">
            <a href="/blog" class="btn btn-secondary">← Back to Blog</a>
        </footer>
    </div>
</article>
