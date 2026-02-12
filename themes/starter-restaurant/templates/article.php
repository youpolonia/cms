<?php
/**
 * Starter Restaurant — Article Template
 * Premium reading experience with meta bar
 */
?>
<section class="page-hero">
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
        <!-- Article Meta -->
        <div class="article-meta-bar">
            <?php if (!empty($article['category_name'])): ?>
            <span class="article-category-badge"><?= esc($article['category_name']) ?></span>
            <?php endif; ?>
            <span><i class="far fa-calendar"></i> <?= date('F j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
            <?php if (!empty($article['views'])): ?>
            <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?> views</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($article['featured_image'])): ?>
        <div class="article-featured-img">
            <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="prose">
            <?= $article['content'] ?>
        </div>

        <div class="article-back-link">
            <a href="/articles" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Articles</a>
        </div>
    </div>
</section>
