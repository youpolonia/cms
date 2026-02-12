<?php
/**
 * Starter Business — Testimonials Section (Articles as Insights)
 * Editable via Theme Studio. data-ts for live preview.
 */
$articlesLabel = theme_get('articles.label', 'Insights');
$articlesTitle = theme_get('articles.title', 'Latest Articles');
$articlesDesc  = theme_get('articles.description', 'Stay informed with our latest business insights and thought leadership.');
?>
<!-- Articles as "Insights" -->
<?php if (!empty($articles)): ?>
<section class="section testimonials-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="services-grid">
            <?php foreach (array_slice($articles, 0, 6) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="service-card" style="text-decoration:none">
                <?php if (!empty($a['featured_image'])): ?>
                <div style="margin:-40px -32px 24px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="service-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <?php endif; ?>
                <h3 class="service-title"><?= esc($a['title']) ?></h3>
                <p class="service-desc">
                    <?php if (!empty($a['excerpt'])): ?>
                        <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                    <?php else: ?>
                        <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                    <?php endif; ?>
                </p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                    <span style="font-size:0.8rem;color:var(--color-text_light, #64748b)"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="section-badge" style="margin:0;font-size:0.7rem"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="/articles" class="btn btn-primary">View All Articles <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Insights</span>
            <h2 class="section-title">No articles yet</h2>
            <p class="section-desc">Check back soon for business insights and articles.</p>
        </div>
    </div>
</section>
<?php endif; ?>
