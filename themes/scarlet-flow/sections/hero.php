<?php
$heroBadge = theme_get('hero.badge', 'Digital Marketing Agency');
$heroHeadline = theme_get('hero.headline', 'Drive Explosive Growth for Your DTC Brand');
$heroSubtitle = theme_get('hero.subtitle', 'We specialize in SEO, PPC, social media, and conversion optimization that delivers measurable ROI and scales your direct-to-consumer business.');
$heroBtnText = theme_get('hero.btn_text', 'Get Your Free Audit');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="sf-hero">
    <div class="sf-hero__bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="sf-hero__overlay"></div>
    <div class="container">
        <div class="sf-hero__content" data-animate>
            <span class="sf-hero__badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="sf-hero__title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="sf-hero__subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="sf-hero__actions">
                <a href="<?= esc($heroBtnLink) ?>" class="sf-btn sf-btn--primary sf-btn--large" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                </a>
                <a href="#services" class="sf-btn sf-btn--outline sf-btn--large">
                    <span>Our Services</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="sf-hero__stats">
                <div class="sf-hero__stat">
                    <span class="sf-hero__stat-number">+42%</span>
                    <span class="sf-hero__stat-label">Avg. ROAS Increase</span>
                </div>
                <div class="sf-hero__stat">
                    <span class="sf-hero__stat-number">3.2x</span>
                    <span class="sf-hero__stat-label">Conversion Lift</span>
                </div>
                <div class="sf-hero__stat">
                    <span class="sf-hero__stat-number">24/7</span>
                    <span class="sf-hero__stat-label">Campaign Monitoring</span>
                </div>
            </div>
        </div>
    </div>
    <div class="sf-hero__scroll">
        <a href="#services" aria-label="Scroll down">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>
