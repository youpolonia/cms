<?php
/**
 * Starter Restaurant — Home Template
 * 
 * All editable content reads from theme_get() (Theme Studio customizations).
 * data-ts attributes provide live preview bindings.
 */

// Pre-load customized values with sensible defaults
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Experience exceptional cuisine in an unforgettable atmosphere.');
$heroBtnText  = theme_get('hero.btn_text', 'Our Stories');
$heroBtnLink  = theme_get('hero.btn_link', '/articles');
$heroBgImage  = theme_get('hero.bg_image');
$heroBadge    = get_setting('hero_badge') ?: 'Welcome';

$aboutLabel   = theme_get('about.label', 'About Us');
$aboutTitle   = theme_get('about.title', get_site_name());
$aboutDesc    = theme_get('about.description', get_setting('about_text') ?: 'Welcome to our establishment. We bring you the finest experiences with passion and dedication.');
$aboutImage   = theme_get('about.image');

$pagesLabel   = theme_get('pages.label', 'Explore');
$pagesTitle   = theme_get('pages.title', 'Our Pages');
$pagesDesc    = theme_get('pages.description', 'Discover everything we have to offer.');

$articlesLabel   = theme_get('articles.label', 'News & Stories');
$articlesTitle   = theme_get('articles.title', 'Latest Articles');
$articlesDesc    = theme_get('articles.description', 'Stay up to date with our latest news and stories.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');

$parallaxQuote    = theme_get('parallax.quote', get_setting('quote_text') ?: 'Every great experience begins with passion and dedication.');
$parallaxCitation = theme_get('parallax.citation', get_site_name());
$parallaxBg       = theme_get('parallax.bg_image');
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image"<?php if ($heroBgImage): ?> style="background: url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?>></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-badge"><?= esc($heroBadge) ?></div>
        <h1 class="hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
            <a href="#pages" class="btn btn-outline">Explore</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-images">
                <div class="about-img-main" data-ts-bg="about.image"<?php if ($aboutImage): ?> style="background: url(<?= esc($aboutImage) ?>) center/cover no-repeat"<?php endif; ?>>
                    <?php if (!$aboutImage): ?>
                    <div class="img-placeholder"><i class="fas fa-utensils"></i></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="about-content">
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-lead" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <?php if (!empty($pages)): ?>
                        <?php foreach (array_slice($pages, 0, 3) as $p): ?>
                        <div class="feature">
                            <i class="fas fa-star"></i>
                            <div>
                                <h4><?= esc($p['title']) ?></h4>
                                <p><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 80, '...')) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pages as Menu Categories -->
<?php if (!empty($pages)): ?>
<section class="section menu-section" id="pages">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 class="section-title" data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p class="section-desc" data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="menu-grid">
            <?php foreach ($pages as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                <div class="menu-card-img">
                    <?php if (!empty($p['featured_image'])): ?>
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:220px;object-fit:cover">
                    <?php else: ?>
                    <div class="img-placeholder menu-ph"><i class="fas fa-file-alt"></i></div>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-header">
                        <h3><?= esc($p['title']) ?></h3>
                    </div>
                    <p><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Articles as "News" -->
<?php if (!empty($articles)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="menu-grid">
            <?php foreach (array_slice($articles, 0, 4) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                <div class="menu-card-img">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:220px;object-fit:cover">
                    <?php else: ?>
                    <div class="img-placeholder menu-ph"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="menu-card-tag"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-header">
                        <h3><?= esc($a['title']) ?></h3>
                        <span class="menu-price" style="font-size:0.85rem"><?= date('M j', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                    </div>
                    <p>
                        <?php if (!empty($a['excerpt'])): ?>
                            <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 100, '...')) ?>
                        <?php else: ?>
                            <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 100, '...')) ?>
                        <?php endif; ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="menu-cta">
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-outline" data-ts="articles.btn_text" data-ts-href="articles.btn_link"><?= esc($articlesBtnText) ?></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 class="section-title" data-ts="articles.title">No articles yet</h2>
            <p class="section-desc" data-ts="articles.description">Check back soon for news and stories.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Parallax Divider -->
<section class="parallax-divider" data-ts-bg="parallax.bg_image"<?php if ($parallaxBg): ?> style="background: url(<?= esc($parallaxBg) ?>) center/cover fixed no-repeat"<?php endif; ?>>
    <div class="parallax-overlay"></div>
    <div class="parallax-content">
        <blockquote>
            <p data-ts="parallax.quote"><?= esc($parallaxQuote) ?></p>
        </blockquote>
        <cite data-ts="parallax.citation"><?= esc($parallaxCitation) ?></cite>
    </div>
</section>
