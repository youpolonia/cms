<section class="page-header">
    <div class="page-header-content">
        <div class="container">
            <h1 class="page-title"><?= esc($article['title']) ?></h1>
            <div class="breadcrumbs">
                <a href="/">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                <a href="/articles">Articles</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                <span class="breadcrumb-current"><?= esc($article['title']) ?></span>
            </div>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <!-- Article Meta -->
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;flex-wrap:wrap">
            <?php if (!empty($article['category_name'])): ?>
            <span class="section-badge" style="margin:0"><?= esc($article['category_name']) ?></span>
            <?php endif; ?>
            <span style="color:var(--color-text_light, #64748b);font-size:0.875rem">
                <i class="far fa-calendar" style="margin-right:4px"></i>
                <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?>
            </span>
            <?php if (!empty($article['views'])): ?>
            <span style="color:var(--color-text_light, #64748b);font-size:0.875rem">
                <i class="far fa-eye" style="margin-right:4px"></i>
                <?= number_format($article['views']) ?> views
            </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($article['featured_image'])): ?>
        <div style="margin-bottom:40px;border-radius:12px;overflow:hidden">
            <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" style="width:100%">
        </div>
        <?php endif; ?>

        <div class="prose">
            <?= $article['content'] ?>
        </div>

        <div style="margin-top:60px;padding-top:32px;border-top:1px solid var(--color-border, #e2e8f0);text-align:center">
            <a href="/articles" class="btn btn-outline"><i class="fas fa-arrow-left" style="margin-right:8px"></i> Back to Articles</a>
        </div>
    </div>
</section>
