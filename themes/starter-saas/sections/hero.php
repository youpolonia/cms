<?php
/**
 * Starter SaaS — Hero Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroTagline  = theme_get('hero.tagline', get_setting('hero_tagline') ?: 'Build Something Amazing');
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Discover our latest content, explore our pages, and stay up to date with everything new.');
$heroBtnText  = theme_get('hero.btn_text', 'Browse Articles');
$heroBtnLink  = theme_get('hero.btn_link', '/articles');
$heroBadge    = theme_get('hero.badge', get_setting('hero_badge') ?: 'Welcome to ' . get_site_name());
$heroBgImage  = theme_get('hero.bg_image');
?>
<!-- Hero Section -->
<section class="hero"<?php if ($heroBgImage): ?> style="background:url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?> data-ts-bg="hero.bg_image">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                <?= esc($heroBadge) ?>
            </div>
            <h1 data-ts="hero.headline"><?= esc($heroHeadline) ?> <span class="gradient-text" data-ts="hero.tagline"><?= esc($heroTagline) ?></span></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
                <a href="#pages" class="btn btn-glass">Our Pages <i class="fas fa-chevron-down"></i></a>
            </div>

            <?php if (!empty($articles)): ?>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number"><?= count($articles) ?>+</span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= count($pages) ?>+</span>
                    <span class="stat-label">Pages</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= array_sum(array_column($articles, 'views')) ?></span>
                    <span class="stat-label">Total Views</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
