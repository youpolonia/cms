<?php
$heroBadge = theme_get('hero.badge', '24/7 Emergency Response');
$heroHeadline = theme_get('hero.headline', 'Storm Damage? We\'re On Our Way.');
$heroSubtitle = theme_get('hero.subtitle', 'Fast, reliable emergency roof repairs when you need them most. Same-day callout, professional service, and peace of mind — even in the worst weather.');
$heroBtnText = theme_get('hero.btn_text', 'Request Emergency Service');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_btn_text', 'View Our Services');
$heroSecondaryLink = theme_get('hero.secondary_btn_link', '/services');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');
?>
<section class="ssp-hero" id="hero">
    <div class="ssp-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="ssp-hero-overlay"></div>
    <div class="ssp-hero-particles"></div>
    
    <div class="ssp-hero-content">
        <div class="ssp-hero-inner" data-animate>
            <div class="ssp-hero-badge" data-ts="hero.badge">
                <span class="ssp-badge-pulse"></span>
                <i class="fas fa-bolt"></i>
                <?= esc($heroBadge) ?>
            </div>
            
            <h1 class="ssp-hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            
            <p class="ssp-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            
            <div class="ssp-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="ssp-btn ssp-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <i class="fas fa-phone-alt"></i>
                    <?= esc($heroBtnText) ?>
                </a>
                <a href="<?= esc($heroSecondaryLink) ?>" class="ssp-btn ssp-btn-outline" data-ts="hero.secondary_btn_text" data-ts-href="hero.secondary_btn_link">
                    <?= esc($heroSecondaryText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="ssp-hero-stats" data-animate>
            <div class="ssp-hero-stat">
                <span class="ssp-stat-number">30</span>
                <span class="ssp-stat-unit">min</span>
                <span class="ssp-stat-label">Avg Response</span>
            </div>
            <div class="ssp-hero-stat-divider"></div>
            <div class="ssp-hero-stat">
                <span class="ssp-stat-number">24/7</span>
                <span class="ssp-stat-label">Availability</span>
            </div>
            <div class="ssp-hero-stat-divider"></div>
            <div class="ssp-hero-stat">
                <span class="ssp-stat-number">5K+</span>
                <span class="ssp-stat-label">Roofs Saved</span>
            </div>
        </div>
    </div>
    
    <div class="ssp-hero-scroll">
        <span>Scroll to explore</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
