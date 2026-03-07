<?php
/**
 * JTB Theme — Articles listing
 */
$articles = $articles ?? [];
$categoryName = $categoryName ?? '';
?>
<div class="jtb-articles-listing">
    <?php if ($categoryName): ?>
        <h1>Category: <?= esc($categoryName) ?></h1>
    <?php else: ?>
        <h1>Articles</h1>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
        <p>No articles found.</p>
    <?php else: ?>
        <div class="jtb-articles-grid">
        <?php foreach ($articles as $article): ?>
            <article class="jtb-article-card">
                <?php if (!empty($article['featured_image'])): ?>
                    <a href="/article/<?= esc($article['slug']) ?>">
                        <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
                    </a>
                <?php endif; ?>
                <div class="jtb-article-card-body">
                    <h2><a href="/article/<?= esc($article['slug']) ?>"><?= esc($article['title']) ?></a></h2>
                    <?php if (!empty($article['excerpt'])): ?>
                        <p><?= esc($article['excerpt']) ?></p>
                    <?php endif; ?>
                    <time datetime="<?= esc($article['created_at'] ?? '') ?>"><?= date('M j, Y', strtotime($article['created_at'] ?? 'now')) ?></time>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
