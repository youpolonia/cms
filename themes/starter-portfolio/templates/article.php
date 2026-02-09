<section class="page-hero">
    <div class="page-hero-meta">
        <a href="/articles" style="color:var(--color-primary)"><i class="fas fa-arrow-left"></i> Blog</a>
        <?php if (!empty($article['category_name'])): ?>
         &nbsp;/&nbsp; <?= esc($article['category_name']) ?>
        <?php endif; ?>
         &nbsp;/&nbsp; <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?>
        <?php if (!empty($article['views'])): ?>
         &nbsp;/&nbsp; <?= number_format($article['views']) ?> views
        <?php endif; ?>
    </div>
    <h1 class="page-hero-title"><?= esc($article['title']) ?></h1>
</section>

<div class="page-content">
    <?php if (!empty($article['featured_image'])): ?>
    <div style="margin-bottom:40px;border-radius:12px;overflow:hidden;border:1px solid var(--color-border)">
        <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" style="width:100%">
    </div>
    <?php endif; ?>

    <?= $article['content'] ?>

    <hr style="border:none;height:1px;background:var(--color-border);margin:48px 0">
    <div style="text-align:center">
        <a href="/articles" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Blog</a>
    </div>
</div>
