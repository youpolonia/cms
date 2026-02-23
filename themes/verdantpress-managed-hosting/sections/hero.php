<?php
$heroLabel = theme_get('hero.badge', 'Enterprise WordPress Hosting');
$heroHeadline = theme_get('hero.headline', 'Hosting Built for WordPress Professionals');
$heroSubtitle = theme_get('hero.subtitle', 'Automated workflows, staging environments, daily backups, and expert support. Focus on building—we handle the infrastructure.');
$heroBtnText = theme_get('hero.btn_text', 'View Pricing Plans');
$heroBtnLink = theme_get('hero.btn_link', '#pricing');
$heroSecondaryText = theme_get('hero.secondary_btn_text', 'Talk to an Expert');
$heroSecondaryLink = theme_get('hero.secondary_btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');
?>
<section class="vp-hero" id="hero">
    <div class="vp-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="vp-hero-overlay"></div>
    
    <div class="container">
        <div class="vp-hero-content" data-animate>
            <span class="vp-hero-badge" data-ts="hero.badge">
                <i class="fas fa-shield-alt"></i>
                <?= esc($heroLabel) ?>
            </span>
            
            <h1 class="vp-hero-headline" data-ts="hero.headline">
                <?= esc($heroHeadline) ?>
            </h1>
            
            <p class="vp-hero-subtitle" data-ts="hero.subtitle">
                <?= esc($heroSubtitle) ?>
            </p>
            
            <div class="vp-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="vp-btn vp-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($heroSecondaryLink) ?>" class="vp-btn vp-btn-secondary" data-ts="hero.secondary_btn_text" data-ts-href="hero.secondary_btn_link">
                    <i class="fas fa-comments"></i>
                    <?= esc($heroSecondaryText) ?>
                </a>
            </div>
            
            <div class="vp-hero-features">
                <div class="vp-hero-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>99.99% Uptime SLA</span>
                </div>
                <div class="vp-hero-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Daily Backups</span>
                </div>
                <div class="vp-hero-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>24/7 Expert Support</span>
                </div>
                <div class="vp-hero-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Free SSL & CDN</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vp-hero-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V0Z" fill="var(--background)"/>
        </svg>
    </div>
</section>
