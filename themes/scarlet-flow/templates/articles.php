<?php
/**
 * Articles List Template — AI Generated
 */
?>
<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Articles</h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span>Articles</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="articles-layout">
            <div>
                <?php if (!empty($articles)): ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $a): ?>
                    <a href="/article/<?= esc($a['slug']) ?>" class="article-card" data-animate>
                        <div class="article-card-img">
                            <?php if (!empty($a['featured_image'])): ?>
                            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="img-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="article-card-tag"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="article-card-body">
                            <span class="article-card-date"><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            <h3><?= esc($a['title']) ?></h3>
                            <p><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 130, '...')) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                    <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div style="text-align:center;padding:60px 0">
                    <h2>No articles yet</h2>
                    <p style="opacity:0.6;margin-top:8px">Check back soon for new content.</p>
                </div>
                <?php endif; ?>
            </div>

<aside class="sf-sidebar">
    <div class="sf-sidebar__search">
        <form action="/articles" method="GET" class="sf-sidebar__search-form">
            <input type="text" name="q" placeholder="Search articles..." class="sf-sidebar__search-input">
            <button type="submit" class="sf-sidebar__search-btn" aria-label="Search">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <?php if (!empty($categories)): ?>
    <div class="sf-sidebar__block">
        <h4 class="sf-sidebar__title">Categories</h4>
        <div class="sf-sidebar__categories">
            <?php foreach ($categories as $cat): ?>
            <a href="/articles?category=<?= esc($cat['slug']) ?>" class="sf-sidebar__category">
                <span class="sf-sidebar__category-name"><?= esc($cat['name']) ?></span>
                <span class="sf-sidebar__category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="sf-sidebar__block">
        <h4 class="sf-sidebar__title">About Our Blog</h4>
        <p class="sf-sidebar__about" data-ts="sidebar.about">
            <?= esc(theme_get('sidebar.about', 'Expert insights on digital marketing trends, SEO strategies, PPC optimization, and conversion rate optimization for direct-to-consumer brands.')) ?>
        </p>
    </div>
    <div class="sf-sidebar__block">
        <h4 class="sf-sidebar__title">Popular Tags</h4>
        <div class="sf-sidebar__tags">
            <a href="/articles?tag=seo" class="sf-sidebar__tag">SEO</a>
            <a href="/articles?tag=ppc" class="sf-sidebar__tag">PPC</a>
            <a href="/articles?tag=social-media" class="sf-sidebar__tag">Social Media</a>
            <a href="/articles?tag=conversion" class="sf-sidebar__tag">Conversion</a>
            <a href="/articles?tag=analytics" class="sf-sidebar__tag">Analytics</a>
            <a href="/articles?tag=dtc" class="sf-sidebar__tag">DTC</a>
            <a href="/articles?tag=content" class="sf-sidebar__tag">Content</a>
            <a href="/articles?tag=roi" class="sf-sidebar__tag">ROI</a>
        </div>
    </div>
    <div class="sf-sidebar__block sf-sidebar__block--highlight">
        <div class="sf-sidebar__tip">
            <i class="fas fa-lightbulb"></i>
            <h5>Pro Tip</h5>
            <p data-ts="sidebar.tip"><?= esc(theme_get('sidebar.tip', 'Focus on landing page speed—every 1-second delay can reduce conversions by 7%.')) ?></p>
        </div>
    </div>
</aside>
        </div>
    </div>
</section>