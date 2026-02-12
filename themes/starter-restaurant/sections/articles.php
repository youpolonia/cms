<?php
/**
 * Starter Restaurant — From Our Kitchen (articles section)
 * Photo-forward article cards for homepage
 * Variables inherited from parent scope
 */
?>
<!-- From Our Kitchen (Articles) -->
<?php if (!empty($articles)): ?>
<section class="section">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="card-grid cols-4">
            <?php foreach (array_slice($articles, 0, 4) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="card-standard" data-animate>
                <div class="card-img">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="img-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="card-tag"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="card-meta">
                        <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                    </div>
                    <h3><?= esc($a['title']) ?></h3>
                    <p>
                        <?php if (!empty($a['excerpt'])): ?>
                            <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 130, '...')) ?>
                        <?php else: ?>
                            <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 130, '...')) ?>
                        <?php endif; ?>
                    </p>
                    <div class="card-footer">
                        <span class="card-link">Read More <i class="fas fa-arrow-right" style="font-size:0.65rem"></i></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="menu-cta" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-outline" data-ts="articles.btn_text" data-ts-href="articles.btn_link"><i class="fas fa-book-open"></i> <?= esc($articlesBtnText) ?></a>
        </div>
    </div>
</section>
<?php endif; ?>
