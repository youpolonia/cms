<?php
/**
 * Starter SaaS — Hero Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroTagline  = theme_get('hero.tagline', 'Build Something Amazing');
$heroSubtitle = theme_get('hero.subtitle', 'The all-in-one platform to automate workflows, track performance, and scale your business.');
$heroBtnText  = theme_get('hero.btn_text', 'Start Free Trial');
$heroBtnLink  = theme_get('hero.btn_link', '/page/pricing');
$heroBadge    = theme_get('hero.badge', 'Now in Public Beta');
$heroBgImage  = theme_get('hero.bg_image');
?>
<!-- Hero Section -->
<section class="hero"<?php if ($heroBgImage): ?> style="background:url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?> data-ts-bg="hero.bg_image">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                <span data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            </div>
            <h1 data-ts="hero.headline"><?= esc($heroHeadline) ?> <span class="gradient-text" data-ts="hero.tagline"><?= esc($heroTagline) ?></span></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
                <a href="#features" class="btn btn-glass">See Features <i class="fas fa-chevron-down"></i></a>
            </div>
            <div class="hero-stats">
                <div class="stat"><span class="stat-number">10K+</span><span class="stat-label">Teams</span></div>
                <div class="stat"><span class="stat-number">99.9%</span><span class="stat-label">Uptime</span></div>
                <div class="stat"><span class="stat-number">4.9★</span><span class="stat-label">Rating</span></div>
            </div>
        </div>
    </div>
</section>
