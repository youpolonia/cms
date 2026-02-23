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

<aside class="ea-sidebar">
    <?php if (!empty($categories)): ?>
    <div class="ea-sidebar-widget ea-sidebar-categories">
        <h4 class="ea-sidebar-title">
            <i class="fas fa-folder-open"></i>
            Categories
        </h4>
        <ul class="ea-category-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="/articles?category=<?= esc($cat['slug']) ?>" class="ea-category-link">
                    <span class="ea-category-name"><?= esc($cat['name']) ?></span>
                    <span class="ea-category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="ea-sidebar-widget ea-sidebar-search">
        <h4 class="ea-sidebar-title">
            <i class="fas fa-search"></i>
            Search Articles
        </h4>
        <form class="ea-search-form">
            <div class="ea-search-input-wrap">
                <input type="text" placeholder="Search..." class="ea-search-input">
                <button type="submit" class="ea-search-btn">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
    
    <div class="ea-sidebar-widget ea-sidebar-newsletter">
        <div class="ea-sidebar-newsletter-inner">
            <i class="fas fa-chart-line ea-sidebar-newsletter-icon"></i>
            <h4 class="ea-sidebar-title">Analytics Insights</h4>
            <p class="ea-sidebar-newsletter-desc" data-ts="sidebar.newsletter_desc"><?= esc(theme_get('sidebar.newsletter_desc', 'Subscribe to receive weekly e-commerce analytics tips and strategies.')) ?></p>
            <form class="ea-sidebar-newsletter-form">
                <input type="email" placeholder="Your email address" class="ea-sidebar-newsletter-input" required>
                <button type="submit" class="ea-sidebar-newsletter-btn">Subscribe</button>
            </form>
        </div>
    </div>
    
    <div class="ea-sidebar-widget ea-sidebar-tags">
        <h4 class="ea-sidebar-title">
            <i class="fas fa-tags"></i>
            Popular Topics
        </h4>
        <div class="ea-tags-cloud">
            <a href="#" class="ea-tag">E-commerce</a>
            <a href="#" class="ea-tag">Analytics</a>
            <a href="#" class="ea-tag">Shopify</a>
            <a href="#" class="ea-tag">WooCommerce</a>
            <a href="#" class="ea-tag">Dashboards</a>
            <a href="#" class="ea-tag">ROI</a>
            <a href="#" class="ea-tag">Insights</a>
            <a href="#" class="ea-tag">Growth</a>
        </div>
    </div>
</aside>
        </div>
    </div>
</section>