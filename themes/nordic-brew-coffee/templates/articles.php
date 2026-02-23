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
        <form class="search-form" action="/articles" method="get">
            <input type="text" name="q" placeholder="Search articles..." class="search-input">
            <button type="submit" class="search-btn" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <?php if (!empty($categories)): ?>
    <div class="sidebar-categories">
        <h4 class="sidebar-heading">Topics</h4>
        <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="/articles?category=<?= esc($cat['slug']) ?>" class="category-link">
                    <span class="category-name"><?= esc($cat['name']) ?></span>
                    <span class="category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <div class="sidebar-about">
        <h4 class="sidebar-heading">About Our Journal</h4>
        <p data-ts="sidebar.about"><?= esc(theme_get('sidebar.about', 'Stories about coffee origins, brewing techniques, and the people behind your cup.')) ?></p>
    </div>
    <div class="sidebar-newsletter">
        <h4 class="sidebar-heading">Stay Caffeinated</h4>
        <p>Get brewing tips and new arrivals straight to your inbox.</p>
        <form class="newsletter-form">
            <input type="email" placeholder="your@email.com" class="newsletter-input">
            <button type="button" class="btn btn-primary btn-sm">Subscribe</button>
        </form>
    </div>
</aside>
        </div>
    </div>
</section>