<?php
/**
 * Starter Portfolio — Testimonials Section (Articles as Blog posts)
 * Editable via Theme Studio. data-ts for live preview.
 */
$articlesLabel = theme_get('articles.label', 'Blog');
$articlesTitle = theme_get('articles.title', 'Latest Posts');
$articlesDesc  = theme_get('articles.description', 'Thoughts, stories, and insights.');
?>
<!-- Articles as "Blog" -->
<?php if (!empty($articles)): ?>
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></div>
        <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
        <p class="section-subtitle" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
    </div>
    <div class="work-grid">
        <?php foreach (array_slice($articles, 0, 4) as $a): ?>
        <a href="/article/<?= esc($a['slug']) ?>" class="work-card" style="text-decoration:none">
            <?php if (!empty($a['featured_image'])): ?>
            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" class="work-card-image">
            <?php else: ?>
            <div style="width:100%;aspect-ratio:16/10;background:var(--color-surface);display:flex;align-items:center;justify-content:center">
                <i class="fas fa-pen-fancy" style="font-size:2rem;color:var(--color-border)"></i>
            </div>
            <?php endif; ?>
            <div class="work-card-content">
                <?php if (!empty($a['category_name'])): ?>
                <div class="work-card-tag"><?= esc($a['category_name']) ?></div>
                <?php endif; ?>
                <div class="work-card-title"><?= esc($a['title']) ?></div>
                <div class="work-card-desc">
                    <?php if (!empty($a['excerpt'])): ?>
                        <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 100, '...')) ?>
                    <?php else: ?>
                        <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 100, '...')) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="work-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:48px">
        <a href="/articles" class="btn btn-outline">View All Posts <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
<?php else: ?>
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label">Blog</div>
        <h2 class="section-title">No posts yet</h2>
        <p class="section-subtitle">Check back soon for new content.</p>
    </div>
</section>
<?php endif; ?>
