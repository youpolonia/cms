<?php
/**
 * Starter Restaurant — Articles Section
 * Variables inherited from parent scope: $articlesLabel, $articlesTitle, $articlesDesc, $articlesBtnText, $articlesBtnLink, $articles
 */
?>
<!-- Latest Articles as "News" -->
<?php if (!empty($articles)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="menu-grid">
            <?php foreach (array_slice($articles, 0, 4) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                <div class="menu-card-img">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:220px;object-fit:cover">
                    <?php else: ?>
                    <div class="img-placeholder menu-ph"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="menu-card-tag"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-header">
                        <h3><?= esc($a['title']) ?></h3>
                        <span class="menu-price" style="font-size:0.85rem"><?= date('M j', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                    </div>
                    <p>
                        <?php if (!empty($a['excerpt'])): ?>
                            <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 100, '...')) ?>
                        <?php else: ?>
                            <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 100, '...')) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="menu-cta">
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-outline" data-ts="articles.btn_text" data-ts-href="articles.btn_link"><?= esc($articlesBtnText) ?></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 class="section-title" data-ts="articles.title">No articles yet</h2>
            <p class="section-desc" data-ts="articles.description">Check back soon for news and stories.</p>
        </div>
    </div>
</section>
<?php endif; ?>
