<section class="page-content">
    <div class="container-narrow">
        <article class="page-article">
            <!-- Back link -->
            <div style="margin-bottom:24px">
                <a href="/articles" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back to Articles</a>
            </div>

            <!-- Meta -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
                <?php if (!empty($article['category_name'])): ?>
                <span class="category-badge"><?= esc($article['category_name']) ?></span>
                <?php endif; ?>
                <span style="color:var(--blog-text-muted);font-size:0.85rem">
                    <i class="far fa-calendar" style="margin-right:4px"></i>
                    <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?>
                </span>
                <?php if (!empty($article['views'])): ?>
                <span style="color:var(--blog-text-muted);font-size:0.85rem">
                    <i class="far fa-eye" style="margin-right:4px"></i>
                    <?= number_format($article['views']) ?> views
                </span>
                <?php endif; ?>
            </div>

            <h1><?= esc($article['title']) ?></h1>

            <?php if (!empty($article['featured_image'])): ?>
            <div class="page-featured-image">
                <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>">
            </div>
            <?php endif; ?>

            <div class="content-body">
                <?= $article['content'] ?>
            </div>

            <!-- Bottom nav -->
            <div style="margin-top:60px;padding-top:32px;border-top:1px solid var(--blog-border);text-align:center">
                <a href="/articles" class="btn btn-outline">← All Articles</a>
            </div>
        </article>
    </div>
</section>
