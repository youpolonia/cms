<?php
$articlesLabel = theme_get('articles.label', 'Latest Research');
$articlesTitle = theme_get('articles.title', 'Insights from the Genomic Frontier');
$articlesDesc = theme_get('articles.description', 'Stay informed with our latest publications, case studies, and breakthroughs in personalized medicine.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');
?>
<section class="gp-articles">
    <div class="container">
        <div class="gp-articles__header" data-animate>
            <span class="gp-articles__label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <div class="gp-articles__divider"></div>
            <h2 class="gp-articles__title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="gp-articles__desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>

        <?php if (!empty($articles)): ?>
            <div class="gp-articles__grid">
                <?php foreach (array_slice($articles, 0, 3) as $a): ?>
                    <article class="gp-articles__card" data-animate>
                        <?php if (!empty($a['featured_image'])): ?>
                            <a href="/article/<?= esc($a['slug']) ?>" class="gp-articles__card-image">
                                <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                            </a>
                        <?php endif; ?>
                        <div class="gp-articles__card-content">
                            <div class="gp-articles__card-meta">
                                <?php if (!empty($a['category_name'])): ?>
                                    <span class="gp-articles__card-category"><?= esc($a['category_name']) ?></span>
                                <?php endif; ?>
                                <span class="gp-articles__card-date"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            </div>
                            <h3 class="gp-articles__card-title">
                                <a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a>
                            </h3>
                            <p class="gp-articles__card-excerpt">
                                <?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 150, '...')) ?>
                            </p>
                            <a href="/article/<?= esc($a['slug']) ?>" class="gp-articles__card-link">
                                Read More
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="gp-articles__placeholder">
                <p>No articles published yet. Check back soon for the latest genomic research insights.</p>
            </div>
        <?php endif; ?>

        <div class="gp-articles__footer" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" class="gp-articles__btn" data-ts="articles.btn_text" data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
