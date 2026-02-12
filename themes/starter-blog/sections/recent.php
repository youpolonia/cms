<?php
/**
 * Starter Blog — Recent Posts Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$articlesLabel = theme_get('articles.label', 'Latest');
$articlesTitle = theme_get('articles.title', 'Recent Posts');
$articlesDesc  = theme_get('articles.description', 'Catch up on our latest articles and stories.');
$remaining = !empty($articles) ? array_slice($articles, 1) : [];
?>
<?php if (!empty($remaining)): ?>
<section class="posts-section" id="latest">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="posts-grid">
            <?php foreach (array_slice($remaining, 0, 6) as $a): ?>
            <div class="post-card">
                <a href="/article/<?= esc($a['slug']) ?>" style="text-decoration:none">
                    <div class="post-card-image">
                        <?php if (!empty($a['featured_image'])): ?>
                        <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>">
                        <?php else: ?>
                        <div style="width:100%;height:100%;background:var(--blog-surface-light);display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-pen-fancy" style="font-size:1.5rem;color:var(--blog-border-light)"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="post-card-body">
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="category-badge"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                    <h3><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                    <p class="excerpt">
                        <?php if (!empty($a['excerpt'])): ?>
                            <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                        <?php else: ?>
                            <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                        <?php endif; ?>
                    </p>
                    <div class="post-meta">
                        <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                        <?php if (!empty($a['views'])): ?>
                        <span><i class="far fa-eye"></i> <?= number_format($a['views']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px">
            <a href="/articles" class="btn btn-outline">View All Articles <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php elseif (empty($articles)): ?>
<section class="posts-section" id="latest">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Blog</span>
            <h2>No articles yet</h2>
            <p>Check back soon — we're working on something great.</p>
        </div>
    </div>
</section>
<?php endif; ?>
