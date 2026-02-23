<?php
$heroBadge = theme_get('hero.badge', 'EST. 2005');
$heroHeadline = theme_get('hero.headline', 'Precision Paving & Groundwork Contractors');
$heroSubtitle = theme_get('hero.subtitle', 'We deliver durable, code-compliant foundations and surfaces for commercial, industrial, and residential projects.');
$heroBtnText = theme_get('hero.btn_text', 'Request a Project Estimate');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="hero-section" id="hero">
    <div class="hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></div>
        <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                <?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i>
            </a>
            <a href="#projects" class="btn btn-outline btn-lg">
                View Our Work <i class="fas fa-hard-hat"></i>
            </a>
        </div>
        <div class="hero-stats" data-animate>
            <div class="stat">
                <span class="stat-number">500+</span>
                <span class="stat-label">Projects Completed</span>
            </div>
            <div class="stat">
                <span class="stat-number">25</span>
                <span class="stat-label">Years Experience</span>
            </div>
            <div class="stat">
                <span class="stat-number">100%</span>
                <span class="stat-label">Code Compliant</span>
            </div>
            <div class="stat">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Emergency Service</span>
            </div>
        </div>
    </div>
    <a href="#services" class="hero-scroll" aria-label="Scroll down">
        <i class="fas fa-chevron-down"></i>
    </a>
</section>
