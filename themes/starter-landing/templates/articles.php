<?php
/**
 * Articles Listing Template — AppFlow
 * Available: $page, $content, $articles (array), $pagination
 */
?>
<section class="page-hero">
    <div class="container">
        <h1><?= esc($page['title'] ?? 'Blog') ?></h1>
        <p>Insights, tips, and updates from the AppFlow team</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (!empty($content) && trim(strip_tags($content)) !== ''): ?>
        <div class="content-body" style="margin-bottom:48px"><?= $content ?></div>
        <?php endif; ?>

        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="article-card fade-in-up">
                <div class="article-card-image">
                    <?php if (!empty($article['featured_image'])): ?>
                    <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" loading="lazy">
                    <?php else: ?>
                    📝
                    <?php endif; ?>
                </div>
                <div class="article-card-body">
                    <div class="article-card-meta">
                        <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'] ?? 'now')) ?>
                    </div>
                    <h3><a href="/article/<?= esc($article['slug']) ?>"><?= esc($article['title']) ?></a></h3>
                    <?php if (!empty($article['excerpt'])): ?>
                    <p><?= esc($article['excerpt']) ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:80px 0;">
            <p style="font-size:1.1rem;color:#64748b;">No articles yet. Check back soon!</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($pagination)): ?>
        <nav style="display:flex;justify-content:center;gap:8px;padding:40px 0;">
            <?php if ($pagination['current_page'] > 1): ?>
            <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="btn-outline" style="padding:10px 20px;font-size:.9rem;">← Previous</a>
            <?php endif; ?>
            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="btn-primary" style="padding:10px 20px;font-size:.9rem;">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>
