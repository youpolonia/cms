<?php
$heroBadge = theme_get('hero.badge', '23+ Years of Excellence');
$heroHeadline = theme_get('hero.headline', 'Every Great Project Starts With Edi\'s Paving Contractors');
$heroSubtitle = theme_get('hero.subtitle', 'Professional paving & groundwork specialists serving Essex. Trusted by homeowners and businesses for over two decades.');
$heroBtnText = theme_get('hero.btn_text', 'Get Free Quote');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_text', 'View Our Work');
$heroSecondaryLink = theme_get('hero.secondary_link', '#projects');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');
?>
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    <div class="hero-content">
        <div class="hero-text" data-animate>
            <div class="hero-badge">
                <i class="fas fa-award"></i>
                <span data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            </div>
            <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <span><?= esc($heroBtnText) ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($heroSecondaryLink) ?>" class="btn btn-outline btn-lg" data-ts="hero.secondary_text" data-ts-href="hero.secondary_link">
                    <i class="fas fa-images"></i>
                    <span><?= esc($heroSecondaryText) ?></span>
                </a>
            </div>
        </div>
        <div class="hero-stats" data-animate>
            <div class="stat-item">
                <span class="stat-number" data-ts="hero.stat1_number"><?= esc(theme_get('hero.stat1_number', '23+')) ?></span>
                <span class="stat-label" data-ts="hero.stat1_label"><?= esc(theme_get('hero.stat1_label', 'Years Experience')) ?></span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-ts="hero.stat2_number"><?= esc(theme_get('hero.stat2_number', '500+')) ?></span>
                <span class="stat-label" data-ts="hero.stat2_label"><?= esc(theme_get('hero.stat2_label', 'Projects Completed')) ?></span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-ts="hero.stat3_number"><?= esc(theme_get('hero.stat3_number', '100%')) ?></span>
                <span class="stat-label" data-ts="hero.stat3_label"><?= esc(theme_get('hero.stat3_label', 'Satisfaction')) ?></span>
            </div>
        </div>
    </div>
    <div class="hero-scroll">
        <a href="#about" class="scroll-indicator">
            <span>Scroll</span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>