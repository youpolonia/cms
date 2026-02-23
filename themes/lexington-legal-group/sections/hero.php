<?php
$heroBadge = theme_get('hero.badge', 'EST. 1994');
$heroHeadline = theme_get('hero.headline', 'Strategic Counsel for Complex Business Challenges');
$heroSubtitle = theme_get('hero.subtitle', 'A premier law firm with over 30 years of experience in corporate law, mergers & acquisitions, and intellectual property protection.');
$heroBtnText = theme_get('hero.btn_text', 'Schedule a Consultation');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', '');
?>
<section class="llg-hero" id="hero">
    <?php if ($heroBgImage): ?>
    <div class="llg-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="llg-hero-overlay"></div>
    <div class="container">
        <div class="llg-hero-content" data-animate>
            <span class="llg-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="llg-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="llg-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="llg-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="llg-btn llg-btn--primary llg-btn--large" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                </a>
                <a href="#services" class="llg-btn llg-btn--outline llg-btn--large">
                    <span>Our Services</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="llg-hero-stats">
                <div class="llg-stat">
                    <span class="llg-stat-number">30+</span>
                    <span class="llg-stat-label">Years Experience</span>
                </div>
                <div class="llg-stat">
                    <span class="llg-stat-number">500+</span>
                    <span class="llg-stat-label">Cases Resolved</span>
                </div>
                <div class="llg-stat">
                    <span class="llg-stat-number">98%</span>
                    <span class="llg-stat-label">Client Satisfaction</span>
                </div>
            </div>
        </div>
    </div>
    <a href="#about" class="llg-hero-scroll" aria-label="Scroll down">
        <i class="fas fa-chevron-down"></i>
    </a>
</section>
