<?php
/**
 * Starter Business — Hero Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroTagline  = theme_get('hero.tagline', get_setting('hero_tagline') ?: 'Your Trusted Partner');
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Delivering excellence through innovation, expertise, and dedication to our clients.');
$heroBtnText  = theme_get('hero.btn_text', 'Our Services');
$heroBtnLink  = theme_get('hero.btn_link', '#services');
$heroBgImage  = theme_get('hero.bg_image');
$heroBadge    = theme_get('hero.badge', get_setting('hero_badge') ?: 'Welcome to ' . get_site_name());
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image"<?php if ($heroBgImage): ?> style="background: url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?>></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-bolt"></i>
                <?= esc($heroBadge) ?>
            </div>
            <h1 class="hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?> — <span class="text-gradient" data-ts="hero.tagline"><?= esc($heroTagline) ?></span></h1>
            <p class="hero-desc" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
                <a href="/articles" class="btn btn-outline btn-lg">Read Insights</a>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number"><?= count($pages) ?>+</div>
                    <div class="stat-label">Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($articles) ?>+</div>
                    <div class="stat-label">Articles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= array_sum(array_column($articles, 'views')) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>
