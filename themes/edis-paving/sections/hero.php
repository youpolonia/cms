<?php
$heroBadge = theme_get('hero.badge', 'Established 2001 • Family Owned');
$heroHeadline = theme_get('hero.headline', 'Paving Excellence for Over 23 Years');
$heroSubtitle = theme_get('hero.subtitle', 'Premium driveways, patios, and groundworks by Essex\'s most trusted family contractors. Quality craftsmanship that stands the test of time.');
$heroBtnText = theme_get('hero.btn_text', 'Get Your Free Quote');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-paving.jpg');
?>
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    
    <div class="hero-content">
        <div class="hero-text" data-animate>
            <span class="hero-badge" data-ts="hero.badge">
                <i class="fas fa-award"></i>
                <?= esc($heroBadge) ?>
            </span>
            <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" 
                   class="btn btn-primary btn-lg"
                   data-ts="hero.btn_text"
                   data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#projects" class="btn btn-outline">
                    View Our Work
                    <i class="fas fa-images"></i>
                </a>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">23+</span>
                    <span class="stat-label">Years Experience</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Projects Completed</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Satisfaction</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll">
        <span>Scroll to explore</span>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>