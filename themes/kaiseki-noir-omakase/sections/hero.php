<?php
$heroBadge = theme_get('hero.badge', 'London • Mayfair');
$heroHeadline = theme_get('hero.headline', 'An Omakase Experience Beyond Tradition');
$heroSubtitle = theme_get('hero.subtitle', 'Surrender to the artistry of our master chefs as they craft an intimate, multi-course journey through the seasons of Japan. Each dish is a meditation on flavor, precision, and the finest ingredients sourced daily.');
$heroBtnText = theme_get('hero.btn_text', 'Reserve Your Experience');
$heroBtnLink = theme_get('hero.btn_link', '#reservations');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');
?>
<section class="kno-hero" id="hero">
    <div class="kno-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="kno-hero-overlay"></div>
    
    <div class="kno-hero-content">
        <div class="kno-hero-inner">
            <span class="kno-hero-badge" data-ts="hero.badge" data-animate><?= esc($heroBadge) ?></span>
            
            <h1 class="kno-hero-headline" data-ts="hero.headline" data-animate>
                <?= esc($heroHeadline) ?>
            </h1>
            
            <p class="kno-hero-subtitle" data-ts="hero.subtitle" data-animate>
                <?= esc($heroSubtitle) ?>
            </p>
            
            <div class="kno-hero-actions" data-animate>
                <a href="<?= esc($heroBtnLink) ?>" 
                   class="kno-btn kno-btn-primary" 
                   data-ts="hero.btn_text" 
                   data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="kno-hero-stats" data-animate>
                <div class="kno-stat-item">
                    <div class="kno-stat-number">12</div>
                    <div class="kno-stat-label">Course Tasting</div>
                </div>
                <div class="kno-stat-divider"></div>
                <div class="kno-stat-item">
                    <div class="kno-stat-number">8</div>
                    <div class="kno-stat-label">Seats Only</div>
                </div>
                <div class="kno-stat-divider"></div>
                <div class="kno-stat-item">
                    <div class="kno-stat-number">2★</div>
                    <div class="kno-stat-label">Michelin Guide</div>
                </div>
            </div>
        </div>
    </div>

    <div class="kno-hero-scroll">
        <span class="kno-scroll-text">Discover</span>
        <div class="kno-scroll-indicator"></div>
    </div>
</section>
