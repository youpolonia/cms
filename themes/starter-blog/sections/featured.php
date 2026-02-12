<?php
/**
 * Starter Blog — Featured Post Section
 */
?>
<!-- Featured Post (first article) -->
<?php if (!empty($articles)): ?>
<?php $featured = $articles[0]; ?>
<section class="featured-post">
    <div class="container">
        <a href="/article/<?= esc($featured['slug']) ?>" class="featured-card" style="text-decoration:none">
            <div class="featured-image">
                <?php if (!empty($featured['featured_image'])): ?>
                <img src="<?= esc($featured['featured_image']) ?>" alt="<?= esc($featured['title']) ?>">
                <?php else: ?>
                <div style="width:100%;height:100%;background:var(--blog-surface-light);display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-newspaper" style="font-size:3rem;color:var(--blog-border-light)"></i>
                </div>
                <?php endif; ?>
                <?php if (!empty($featured['category_name'])): ?>
                <span class="category-badge"><?= esc($featured['category_name']) ?></span>
                <?php endif; ?>
            </div>
            <div class="featured-body">
                <span class="featured-label"><i class="fas fa-star"></i> Featured</span>
                <h2><span style="color:var(--blog-text)"><?= esc($featured['title']) ?></span></h2>
                <p class="featured-excerpt">
                    <?php if (!empty($featured['excerpt'])): ?>
                        <?= esc(mb_strimwidth(strip_tags($featured['excerpt']), 0, 200, '...')) ?>
                    <?php else: ?>
                        <?= esc(mb_strimwidth(strip_tags($featured['content']), 0, 200, '...')) ?>
                    <?php endif; ?>
                </p>
                <div class="post-meta">
                    <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($featured['published_at'] ?? $featured['created_at'])) ?></span>
                    <?php if (!empty($featured['views'])): ?>
                    <span><i class="far fa-eye"></i> <?= number_format($featured['views']) ?> views</span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    </div>
</section>
<?php endif; ?>
