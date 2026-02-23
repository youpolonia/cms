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

<aside class="vp-sidebar">
    <?php if (!empty($categories)): ?>
    <div class="vp-sidebar-widget vp-categories-widget">
        <h4 class="vp-widget-title">Browse Topics</h4>
        <div class="vp-category-list">
            <?php foreach ($categories as $cat): ?>
            <a href="/articles?category=<?= esc($cat['slug']) ?>" class="vp-category-item">
                <span class="vp-category-name"><?= esc($cat['name']) ?></span>
                <span class="vp-category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="vp-sidebar-widget vp-about-widget">
        <h4 class="vp-widget-title">About Our Blog</h4>
        <p data-ts="sidebar.about"><?= esc(theme_get('sidebar.about', 'Expert insights on WordPress hosting, performance optimization, security best practices, and scaling strategies for growing businesses.')) ?></p>
    </div>
    
    <div class="vp-sidebar-widget vp-newsletter-widget">
        <div class="vp-newsletter-icon">
            <i class="fas fa-envelope"></i>
        </div>
        <h4 class="vp-widget-title">Newsletter</h4>
        <p>Get weekly WordPress tips and hosting insights delivered straight to your inbox.</p>
        <form class="vp-sidebar-newsletter-form">
            <input type="email" placeholder="Your email address" class="vp-sidebar-input" required>
            <button type="submit" class="vp-sidebar-submit">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
    
    <div class="vp-sidebar-widget vp-tip-widget">
        <div class="vp-tip-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <h4 class="vp-widget-title">Pro Tip</h4>
        <blockquote data-ts="sidebar.quote">
            <?= esc(theme_get('sidebar.quote', 'Enable object caching and use a CDN to reduce server load by up to 70% during traffic spikes.')) ?>
        </blockquote>
    </div>
</aside>
        </div>
    </div>
</section>