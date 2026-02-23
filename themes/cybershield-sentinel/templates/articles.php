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

<aside class="csh-articles-sidebar">
    <?php if (!empty($categories)): ?>
    <div class="csh-sidebar-widget csh-categories-widget">
        <div class="csh-widget-header">
            <i class="fas fa-folder-open"></i>
            <h4 class="csh-widget-title">Categories</h4>
        </div>
        <div class="csh-category-list">
            <?php foreach ($categories as $cat): ?>
            <a href="/articles?category=<?= esc($cat['slug']) ?>" class="csh-category-item">
                <span class="csh-cat-name"><?= esc($cat['name']) ?></span>
                <span class="csh-cat-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="csh-sidebar-widget csh-search-widget">
        <div class="csh-widget-header">
            <i class="fas fa-search"></i>
            <h4 class="csh-widget-title">Search</h4>
        </div>
        <form class="csh-search-form">
            <input type="text" placeholder="Search articles..." class="csh-search-input">
            <button type="submit" class="csh-search-btn">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
    
    <div class="csh-sidebar-widget csh-about-widget">
        <div class="csh-widget-header">
            <i class="fas fa-info-circle"></i>
            <h4 class="csh-widget-title">About This Blog</h4>
        </div>
        <p class="csh-about-text" data-ts="sidebar.about"><?= esc(theme_get('sidebar.about', 'Expert insights on cybersecurity trends, threat intelligence, and best practices for protecting your digital assets.')) ?></p>
    </div>
    
    <div class="csh-sidebar-widget csh-newsletter-widget">
        <div class="csh-newsletter-bg">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="csh-widget-content">
            <h4 class="csh-widget-title">Threat Intelligence</h4>
            <p>Get weekly security updates delivered to your inbox.</p>
            <form class="csh-sidebar-newsletter">
                <input type="email" placeholder="Your email" class="csh-sidebar-email">
                <button type="button" class="csh-sidebar-subscribe">Subscribe</button>
            </form>
        </div>
    </div>
    
    <div class="csh-sidebar-widget csh-tags-widget">
        <div class="csh-widget-header">
            <i class="fas fa-tags"></i>
            <h4 class="csh-widget-title">Popular Topics</h4>
        </div>
        <div class="csh-tags-cloud">
            <span class="csh-tag">Firewall</span>
            <span class="csh-tag">DDoS</span>
            <span class="csh-tag">SOC</span>
            <span class="csh-tag">Intrusion Detection</span>
            <span class="csh-tag">Zero Trust</span>
            <span class="csh-tag">Threat Intel</span>
            <span class="csh-tag">Compliance</span>
        </div>
    </div>
</aside>
        </div>
    </div>
</section>