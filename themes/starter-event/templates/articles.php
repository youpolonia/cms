<section class="page-hero">
    <div class="container">
        <h1>Event Blog</h1>
        <p>News, updates, and insights from Summit 2026</p>
    </div>
</section>
<section class="section-articles">
    <div class="container">
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach ($articles as $a): ?>
            <a href="/articles/<?= esc($a['slug']) ?>" class="article-card">
                <div class="article-card-image">📝</div>
                <div class="article-card-body">
                    <div class="article-card-date"><?= date('M j, Y', strtotime($a['created_at'] ?? 'now')) ?></div>
                    <h3 class="article-card-title"><?= esc($a['title']) ?></h3>
                    <p class="article-card-excerpt"><?= esc(mb_substr(strip_tags($a['content'] ?? $a['body'] ?? ''), 0, 120)) ?>...</p>
                    <span class="article-card-link">Read More →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 0;">
            <p style="font-size:1.1rem;color:var(--gray-600);">No articles published yet. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>
