<?php
$heroBadge = theme_get('hero.badge', 'Premium Landscaping');
$heroHeadline = theme_get('hero.headline', 'Transform Your Outdoor Space');
$heroSubtitle = theme_get('hero.subtitle', 'Full-service landscape contractor specializing in patios, driveways, retaining walls, fencing, and artificial grass installation.');
$heroBtnText = theme_get('hero.btn_text', 'Get Your Free Quote');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="tf-hero" id="hero">
    <div class="tf-hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="tf-hero-overlay"></div>
    <div class="container">
        <div class="tf-hero-content" data-animate>
            <span class="tf-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="tf-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="tf-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="tf-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="tf-btn tf-btn-primary tf-btn-hero" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right tf-btn-icon"></i>
                </a>
                <a href="#services" class="tf-btn tf-btn-outline tf-btn-hero">
                    View Our Services
                    <i class="fas fa-leaf tf-btn-icon"></i>
                </a>
            </div>
            <div class="tf-hero-stats">
                <div class="tf-hero-stat">
                    <span class="tf-hero-stat-number">15+</span>
                    <span class="tf-hero-stat-label">Years Experience</span>
                </div>
                <div class="tf-hero-stat">
                    <span class="tf-hero-stat-number">500+</span>
                    <span class="tf-hero-stat-label">Projects Completed</span>
                </div>
                <div class="tf-hero-stat">
                    <span class="tf-hero-stat-number">100%</span>
                    <span class="tf-hero-stat-label">Client Satisfaction</span>
                </div>
            </div>
        </div>
    </div>
    <a href="#about" class="tf-hero-scroll" aria-label="Scroll down">
        <i class="fas fa-chevron-down"></i>
    </a>
</section>
