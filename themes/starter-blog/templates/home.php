<!-- Blog Hero -->
<section class="blog-hero">
    <h1><?= esc(get_site_name()) ?></h1>
    <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc(get_setting('hero_subtitle') ?: 'Stories, thoughts, and ideas worth sharing.') ?></p>
    <div class="hero-actions">
        <a href="/articles" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">Browse Articles <i class="fas fa-arrow-right"></i></a>
        <a href="#latest" class="btn btn-outline">Latest Posts</a>
    </div>
</section>

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

<!-- Posts Grid -->
<?php $remaining = array_slice($articles, 1); ?>
<?php if (!empty($remaining)): ?>
<section class="posts-section" id="latest">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Latest</span>
            <h2>Recent Posts</h2>
            <p>Catch up on our latest articles and stories.</p>
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
<?php endif; ?>
<?php else: ?>
<!-- No Articles State -->
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

<!-- Pages Section -->
<?php if (!empty($pages)): ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Explore</span>
            <h2>Our Pages</h2>
            <p>Discover more content across our site.</p>
        </div>
        <div class="categories-grid">
            <?php $pageIcons = ['fas fa-file-alt', 'fas fa-info-circle', 'fas fa-bookmark', 'fas fa-star', 'fas fa-heart', 'fas fa-folder']; ?>
            <?php foreach ($pages as $i => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="category-card">
                <div class="cat-icon">
                    <i class="<?= $pageIcons[$i % count($pageIcons)] ?>"></i>
                </div>
                <h3><?= esc($p['title']) ?></h3>
                <span class="cat-count"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 50, '...')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <h2>Stay <span class="gradient-text">Updated</span></h2>
            <p>Subscribe to get notified about new articles and updates.</p>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;max-width:450px;margin:0 auto">
                <a href="/articles" class="btn btn-primary btn-lg">Browse All Articles <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
