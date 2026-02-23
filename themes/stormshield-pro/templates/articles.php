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

<aside class="ssp-sidebar">
    <?php if (!empty($categories)): ?>
    <div class="ssp-sidebar-widget ssp-sidebar-categories">
        <h4 class="ssp-sidebar-title">
            <i class="fas fa-folder-open"></i>
            <span>Categories</span>
        </h4>
        <ul class="ssp-category-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="/articles?category=<?= esc($cat['slug']) ?>" class="ssp-category-link">
                    <span class="ssp-category-name"><?= esc($cat['name']) ?></span>
                    <span class="ssp-category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="ssp-sidebar-widget ssp-sidebar-search">
        <h4 class="ssp-sidebar-title">
            <i class="fas fa-search"></i>
            <span>Search Articles</span>
        </h4>
        <form class="ssp-search-form">
            <input type="text" placeholder="Search..." class="ssp-search-input">
            <button type="submit" class="ssp-search-btn" aria-label="Search">
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
    
    <div class="ssp-sidebar-widget ssp-sidebar-emergency">
        <div class="ssp-emergency-card">
            <div class="ssp-emergency-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h4 class="ssp-emergency-title">Roof Emergency?</h4>
            <p class="ssp-emergency-text">Don't wait — storm damage can lead to costly water damage within hours.</p>
            <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '(555) 911-ROOF'))) ?>" class="ssp-emergency-btn">
                <i class="fas fa-phone-alt"></i>
                <span>Call Now</span>
            </a>
        </div>
    </div>
    
    <div class="ssp-sidebar-widget ssp-sidebar-newsletter">
        <h4 class="ssp-sidebar-title">
            <i class="fas fa-envelope-open-text"></i>
            <span>Storm Alerts</span>
        </h4>
        <p class="ssp-sidebar-text" data-ts="sidebar.newsletter_text"><?= esc(theme_get('sidebar.newsletter_text', 'Get notified about severe weather and roof maintenance tips.')) ?></p>
        <form class="ssp-sidebar-newsletter-form">
            <input type="email" placeholder="Your email" class="ssp-sidebar-input" required>
            <button type="submit" class="ssp-sidebar-btn">Subscribe</button>
        </form>
    </div>
    
    <div class="ssp-sidebar-widget ssp-sidebar-tags">
        <h4 class="ssp-sidebar-title">
            <i class="fas fa-tags"></i>
            <span>Popular Topics</span>
        </h4>
        <div class="ssp-tags-cloud">
            <a href="#" class="ssp-tag">Storm Damage</a>
            <a href="#" class="ssp-tag">Leak Repair</a>
            <a href="#" class="ssp-tag">Shingles</a>
            <a href="#" class="ssp-tag">Emergency</a>
            <a href="#" class="ssp-tag">Insurance</a>
            <a href="#" class="ssp-tag">Maintenance</a>
            <a href="#" class="ssp-tag">Weatherproofing</a>
        </div>
    </div>
</aside>
        </div>
    </div>
</section>