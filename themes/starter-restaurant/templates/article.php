<section class="page-hero">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <h1 class="page-hero-title"><?= esc($article['title']) ?></h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <a href="/articles">Articles</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span><?= esc($article['title']) ?></span>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <!-- Meta -->
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:32px;flex-wrap:wrap;font-size:0.9rem;color:var(--text-muted, #c4a882)">
            <?php if (!empty($article['category_name'])): ?>
            <span style="background:rgba(212,165,116,.1);color:var(--primary, #d4a574);padding:4px 14px;border-radius:4px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em"><?= esc($article['category_name']) ?></span>
            <?php endif; ?>
            <span><i class="far fa-calendar" style="margin-right:4px"></i> <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
            <?php if (!empty($article['views'])): ?>
            <span><i class="far fa-eye" style="margin-right:4px"></i> <?= number_format($article['views']) ?> views</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($article['featured_image'])): ?>
        <div style="margin-bottom:40px;border-radius:8px;overflow:hidden">
            <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" style="width:100%">
        </div>
        <?php endif; ?>

        <div class="prose">
            <?= $article['content'] ?>
        </div>

        <div style="margin-top:60px;padding-top:32px;border-top:1px solid rgba(212,165,116,.1);text-align:center">
            <a href="/articles" class="btn btn-outline"><i class="fas fa-arrow-left" style="margin-right:8px"></i> Back to Articles</a>
        </div>
    </div>
</section>
