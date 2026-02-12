<?php
/**
 * Starter SaaS — Features Section (Articles)
 * Editable via Theme Studio. data-ts for live preview.
 */
$articlesLabel = theme_get('articles.label', 'Latest Articles');
$articlesTitle = theme_get('articles.title', 'Fresh from the Blog');
$articlesDesc  = theme_get('articles.description', 'Stay informed with our latest articles and insights.');
?>
<!-- Latest Articles -->
<?php if (!empty($articles)): ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="features-grid">
            <?php foreach (array_slice($articles, 0, 6) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <?php if (!empty($a['featured_image'])): ?>
                <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="feature-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <?php endif; ?>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($a['title']) ?></h3>
                <p class="feature-desc">
                    <?php if (!empty($a['excerpt'])): ?>
                        <?= esc(mb_strimwidth(strip_tags($a["excerpt"]), 0, 160, '...')) ?>
                    <?php else: ?>
                        <?= esc(mb_strimwidth(strip_tags($a["content"]), 0, 160, '...')) ?>
                    <?php endif; ?>
                </p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto">
                    <span style="font-size:0.8rem;color:#94a3b8">
                        <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                    </span>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="section-badge" style="margin:0;font-size:0.65rem"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="/articles" class="btn btn-outline">View All Articles <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Articles</span>
            <h2>No articles yet</h2>
            <p>Check back soon for fresh content.</p>
        </div>
    </div>
</section>
<?php endif; ?>
