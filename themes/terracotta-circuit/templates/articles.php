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

<aside class="articles-sidebar">
    <div class="sidebar-search">
        <form class="search-form">
            <input type="text" placeholder="Search articles..." class="search-input">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
        </form>
    </div>
    
    <?php if (!empty($categories)): ?>
    <div class="sidebar-widget categories-widget">
        <div class="widget-header">
            <i class="fas fa-folder-open"></i>
            <h4>Categories</h4>
        </div>
        <div class="category-list">
            <?php foreach ($categories as $cat): ?>
            <a href="/articles?category=<?= esc($cat['slug']) ?>" class="category-item">
                <span class="cat-name"><?= esc($cat['name']) ?></span>
                <span class="cat-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="sidebar-widget newsletter-widget">
        <div class="widget-icon">
            <i class="fas fa-paper-plane"></i>
        </div>
        <h4>Stay Updated</h4>
        <p data-ts="sidebar.newsletter_text"><?= esc(theme_get('sidebar.newsletter_text', 'Get the latest insights on autonomous delivery and logistics innovation.')) ?></p>
        <form class="newsletter-form">
            <input type="email" placeholder="Enter your email" class="newsletter-input">
            <button type="button" class="btn btn-primary btn-block">Subscribe</button>
        </form>
    </div>
    
    <div class="sidebar-widget tags-widget">
        <div class="widget-header">
            <i class="fas fa-tags"></i>
            <h4>Popular Topics</h4>
        </div>
        <div class="tags-cloud">
            <a href="#" class="tag">Autonomous Robots</a>
            <a href="#" class="tag">AI Routing</a>
            <a href="#" class="tag">Last-Mile</a>
            <a href="#" class="tag">E-commerce</a>
            <a href="#" class="tag">Urban Logistics</a>
            <a href="#" class="tag">Sustainability</a>
        </div>
    </div>
    
    <div class="sidebar-widget quote-widget">
        <div class="quote-icon"><i class="fas fa-robot"></i></div>
        <blockquote data-ts="sidebar.quote">
            <?= esc(theme_get('sidebar.quote', 'The future of delivery is autonomous, sustainable, and arriving at your doorstep.')) ?>
        </blockquote>
    </div>
</aside>
        </div>
    </div>
</section>