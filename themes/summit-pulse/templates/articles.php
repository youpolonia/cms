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
$sidebarAbout = theme_get('sidebar.about', 'Tips, techniques, and stories from the Summit Pulse climbing community. Whether you\'re a beginner or a seasoned boulderer, find inspiration for your next send.');
?>
<aside class="sp-sidebar">
    <!-- Categories Widget -->
    <?php if (!empty($categories)): ?>
    <div class="sp-sidebar-card">
        <h4 class="sp-sidebar-title">
            <i class="fas fa-layer-group"></i>
            Categories
        </h4>
        <div class="sp-sidebar-cats">
            <?php foreach ($categories as $cat): ?>
            <a href="/articles?category=<?= esc($cat['slug']) ?>" class="sp-sidebar-cat">
                <span class="sp-cat-name"><?= esc($cat['name']) ?></span>
                <span class="sp-cat-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- About Widget -->
    <div class="sp-sidebar-card sp-sidebar-about">
        <h4 class="sp-sidebar-title">
            <i class="fas fa-mountain"></i>
            About This Blog
        </h4>
        <p class="sp-sidebar-text" data-ts="sidebar.about"><?= esc($sidebarAbout) ?></p>
    </div>
    
    <!-- Newsletter Widget -->
    <div class="sp-sidebar-card sp-sidebar-newsletter">
        <h4 class="sp-sidebar-title">
            <i class="fas fa-bell"></i>
            Get Updates
        </h4>
        <p class="sp-sidebar-text">Subscribe for climbing tips and event announcements.</p>
        <form class="sp-sidebar-form">
            <input type="email" placeholder="Your email" class="sp-sidebar-input" required>
            <button type="submit" class="sp-sidebar-btn">Subscribe</button>
        </form>
    </div>
    
    <!-- Popular Tags Widget -->
    <div class="sp-sidebar-card">
        <h4 class="sp-sidebar-title">
            <i class="fas fa-tags"></i>
            Popular Topics
        </h4>
        <div class="sp-sidebar-tags">
            <span class="sp-sidebar-tag">Bouldering</span>
            <span class="sp-sidebar-tag">Training</span>
            <span class="sp-sidebar-tag">Technique</span>
            <span class="sp-sidebar-tag">Gear</span>
            <span class="sp-sidebar-tag">Youth</span>
            <span class="sp-sidebar-tag">Nutrition</span>
            <span class="sp-sidebar-tag">Events</span>
        </div>
    </div>
    
    <!-- Social Widget -->
    <div class="sp-sidebar-card sp-sidebar-social">
        <h4 class="sp-sidebar-title">
            <i class="fas fa-share-alt"></i>
            Follow Us
        </h4>
        <div class="sp-sidebar-social-links">
            <?php if (theme_get('footer.facebook')): ?>
            <a href="<?= esc(theme_get('footer.facebook')) ?>" target="_blank" rel="noopener" class="sp-social-link"><i class="fab fa-facebook-f"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.instagram')): ?>
            <a href="<?= esc(theme_get('footer.instagram')) ?>" target="_blank" rel="noopener" class="sp-social-link"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.youtube')): ?>
            <a href="<?= esc(theme_get('footer.youtube')) ?>" target="_blank" rel="noopener" class="sp-social-link"><i class="fab fa-youtube"></i></a>
            <?php endif; ?>
            <?php if (theme_get('footer.linkedin')): ?>
            <a href="<?= esc(theme_get('footer.linkedin')) ?>" target="_blank" rel="noopener" class="sp-social-link"><i class="fab fa-linkedin-in"></i></a>
            <?php endif; ?>
        </div>
    </div>
</aside>

<style>
.sp-sidebar {
    display: flex;
    flex-direction: column;
    gap: 28px;
    position: sticky;
    top: 140px;
}

.sp-sidebar-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius);
    padding: 28px;
    transition: border-color var(--transition-speed) ease;
}

.sp-sidebar-card:hover {
    border-color: var(--color-primary);
}

.sp-sidebar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: var(--font-heading);
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--color-text);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--color-border);
}

.sp-sidebar-title i {
    color: var(--color-primary);
    font-size: 0.85rem;
}

.sp-sidebar-cats {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sp-sidebar-cat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: var(--color-background);
    border-radius: 8px;
    text-decoration: none;
    transition: all var(--transition-speed) ease;
}

.sp-sidebar-cat:hover {
    background: var(--color-primary);
}

.sp-cat-name {
    font-size: 0.9rem;
    color: var(--color-text-muted);
    transition: color var(--transition-speed) ease;
}

.sp-sidebar-cat:hover .sp-cat-name {
    color: var(--color-background);
}

.sp-cat-count {
    font-family: var(--font-heading);
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--color-text-muted);
    background: var(--color-surface);
    padding: 4px 10px;
    border-radius: 12px;
    transition: all var(--transition-speed) ease;
}

.sp-sidebar-cat:hover .sp-cat-count {
    background: rgba(255,255,255,0.2);
    color: var(--color-background);
}

.sp-sidebar-text {
    font-size: 0.95rem;
    line-height: 1.7;
    color: var(--color-text-muted);
}

.sp-sidebar-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 16px;
}

.sp-sidebar-input {
    padding: 14px 16px;
    font-size: 0.9rem;
    color: var(--color-text);
    background: var(--color-background);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    outline: none;
    transition: border-color var(--transition-speed) ease;
}

.sp-sidebar-input:focus {
    border-color: var(--color-primary);
}

.sp-sidebar-btn {
    padding: 14px 20px;
    font-family: var(--font-heading);
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--color-background);
    background: var(--color-primary);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: background var(--transition-speed) ease;
}

.sp-sidebar-btn:hover {
    background: var(--color-accent);
}

.sp-sidebar-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.sp-sidebar-tag {
    display: inline-block;
    padding: 6px 14px;
    font-size: 0.8rem;
    color: var(--color-text-muted);
    background: var(--color-background);
    border-radius: 16px;
    transition: all var(--transition-speed) ease;
    cursor: pointer;
}

.sp-sidebar-tag:hover {
    background: var(--color-primary);
    color: var(--color-background);
}

.sp-sidebar-social-links {
    display: flex;
    gap: 12px;
}

.sp-social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: var(--color-background);
    border-radius: 50%;
    color: var(--color-text-muted);
    text-decoration: none;
    transition: all var(--transition-speed) ease;
}

.sp-social-link:hover {
    background: var(--color-primary);
    color: var(--color-background);
    transform: translateY(-3px);
}

@media (max-width: 1024px) {
    .sp-sidebar {
        position: static;
    }
}
</style>
        </div>
    </div>
</section>