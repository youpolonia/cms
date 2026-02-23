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

<?php
$sidebarAbout = theme_get('sidebar.about', 'Immerse yourself in the tranquil world of traditional Japanese tea ceremony and seasonal delicacies.');
$sidebarQuote = theme_get('sidebar.quote', 'Every bowl of tea is a journey to inner peace.');
?>
<aside class="ch-sidebar">
    <div class="ch-sidebar-card ch-sidebar-categories">
        <h4 class="ch-sidebar-title">
            <i class="fas fa-leaf"></i>
            Categories
        </h4>
        <div class="ch-category-list">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                <a href="/articles?category=<?= esc($cat['slug']) ?>" class="ch-category-item">
                    <span class="ch-category-name"><?= esc($cat['name']) ?></span>
                    <span class="ch-category-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="ch-sidebar-card ch-sidebar-about">
        <h4 class="ch-sidebar-title">
            <i class="fas fa-spa"></i>
            About Chanoyu
        </h4>
        <p class="ch-sidebar-text" data-ts="sidebar.about">
            <?= esc($sidebarAbout) ?>
        </p>
    </div>

    <div class="ch-sidebar-card ch-sidebar-quote">
        <i class="fas fa-quote-left ch-quote-icon"></i>
        <blockquote class="ch-quote-text" data-ts="sidebar.quote">
            <?= esc($sidebarQuote) ?>
        </blockquote>
    </div>

    <div class="ch-sidebar-card ch-sidebar-newsletter">
        <h4 class="ch-sidebar-title">
            <i class="fas fa-envelope-open-text"></i>
            Tea Journal
        </h4>
        <p class="ch-sidebar-text">Subscribe for ceremony insights and seasonal offerings.</p>
        <form class="ch-sidebar-form">
            <input type="email" placeholder="Enter your email" class="ch-sidebar-input" required>
            <button type="submit" class="ch-sidebar-btn">Subscribe</button>
        </form>
    </div>

    <div class="ch-sidebar-card ch-sidebar-social">
        <h4 class="ch-sidebar-title">
            <i class="fas fa-heart"></i>
            Connect
        </h4>
        <div class="ch-sidebar-social-links">
            <?php if (theme_get('footer.facebook')): ?>
            <a href="<?= esc(theme_get('footer.facebook')) ?>" target="_blank" rel="noopener" aria-label="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <?php endif; ?>
            <?php if (theme_get('footer.instagram')): ?>
            <a href="<?= esc(theme_get('footer.instagram')) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <?php endif; ?>
            <?php if (theme_get('footer.linkedin')): ?>
            <a href="<?= esc(theme_get('footer.linkedin')) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                <i class="fab fa-linkedin-in"></i>
            </a>
            <?php endif; ?>
            <?php if (theme_get('footer.youtube')): ?>
            <a href="<?= esc(theme_get('footer.youtube')) ?>" target="_blank" rel="noopener" aria-label="YouTube">
                <i class="fab fa-youtube"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</aside>
        </div>
    </div>
</section>