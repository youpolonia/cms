<section class="article-hero">
    <div class="article-hero-inner">
        <div class="article-hero-meta">
            <?php if (!empty($page['author'])): ?><?= esc($page['author']) ?> · <?php endif; ?>
            <?= date('F j, Y', strtotime($page['created_at'] ?? 'now')) ?>
        </div>
        <h1><?= esc($page['title'] ?? 'Article') ?></h1>
    </div>
</section>
<section class="article-content">
    <div class="container">
        <div class="content-body"><?= $content ?></div>
    </div>
</section>

<?php if (!empty($related_articles)): ?>
<section class="related-posts">
    <div class="container">
        <h2>More From the Blog</h2>
        <div class="articles-grid">
            <?php foreach (array_slice($related_articles, 0, 3) as $a): ?>
            <a href="/articles/<?= esc($a['slug']) ?>" class="article-card">
                <div class="article-card-image">📝</div>
                <div class="article-card-body">
                    <div class="article-card-date"><?= date('M j, Y', strtotime($a['created_at'] ?? 'now')) ?></div>
                    <h3 class="article-card-title"><?= esc($a['title']) ?></h3>
                    <p class="article-card-excerpt"><?= esc(mb_substr(strip_tags($a['content'] ?? $a['body'] ?? ''), 0, 100)) ?>...</p>
                    <span class="article-card-link">Read More →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
