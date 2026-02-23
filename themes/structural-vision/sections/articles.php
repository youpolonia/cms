<?php
$articlesLabel = theme_get('articles.label', 'Latest News');
$articlesTitle = theme_get('articles.title', 'From Our Journal');
$articlesDesc = theme_get('articles.description', 'Tips, advice and updates from the Edi\'s Paving team');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');
?>
<section class="section articles-section" id="articles">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="articles.label">
                <i class="fas fa-newspaper"></i>
                <?= esc($articlesLabel) ?>
            </span>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach (array_slice($articles, 0, 3) as $index => $a): ?>
            <article class="article-card" data-animate style="--delay: <?= $index * 0.1 ?>s">
                <a href="/article/<?= esc($a['slug']) ?>" class="article-image">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="article-placeholder">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="article-category"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </a>
                <div class="article-content">
                    <div class="article-meta">
                        <span class="article-date">
                            <i class="far fa-calendar"></i>
                            <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                        </span>
                    </div>
                    <h3 class="article-title">
                        <a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a>
                    </h3>
                    <p class="article-excerpt">
                        <?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 120, '...')) ?>
                    </p>
                    <a href="/article/<?= esc($a['slug']) ?>" class="article-link">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="articles-cta" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" 
               class="btn btn-outline"
               data-ts="articles.btn_text"
               data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php else: ?>
        <div class="articles-empty" data-animate>
            <i class="fas fa-newspaper"></i>
            <p>Articles coming soon</p>
        </div>
        <?php endif; ?>
    </div>
</section>
