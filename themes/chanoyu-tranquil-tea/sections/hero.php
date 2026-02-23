<?php
$heroBadge = theme_get('hero.badge', 'Traditional Japanese Tea House');
$heroHeadline = theme_get('hero.headline', 'The Art of Tea, Perfected');
$heroSubtitle = theme_get('hero.subtitle', 'Experience authentic matcha ceremonies, exquisite wagashi confections, and seasonal kaiseki in our tranquil bamboo garden sanctuary');
$heroBtnText = theme_get('hero.btn_text', 'Reserve Your Ceremony');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_btn_text', 'Explore Menu');
$heroSecondaryLink = theme_get('hero.secondary_btn_link', '#menu');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="ch-hero" id="hero">
    <div class="ch-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="ch-hero-overlay"></div>
    
    <div class="ch-hero-content">
        <div class="ch-hero-inner">
            <span class="ch-hero-badge" data-ts="hero.badge" data-animate>
                <i class="fas fa-leaf"></i>
                <?= esc($heroBadge) ?>
            </span>
            
            <h1 class="ch-hero-headline" data-ts="hero.headline" data-animate>
                <?= esc($heroHeadline) ?>
            </h1>
            
            <p class="ch-hero-subtitle" data-ts="hero.subtitle" data-animate>
                <?= esc($heroSubtitle) ?>
            </p>
            
            <div class="ch-hero-actions" data-animate>
                <a href="<?= esc($heroBtnLink) ?>" 
                   class="ch-btn ch-btn-primary"
                   data-ts="hero.btn_text"
                   data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($heroSecondaryLink) ?>" 
                   class="ch-btn ch-btn-secondary"
                   data-ts="hero.secondary_btn_text"
                   data-ts-href="hero.secondary_btn_link">
                    <?= esc($heroSecondaryText) ?>
                </a>
            </div>

            <div class="ch-hero-stats" data-animate>
                <div class="ch-stat-item">
                    <div class="ch-stat-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <div class="ch-stat-content">
                        <span class="ch-stat-number">20+</span>
                        <span class="ch-stat-label">Years Tradition</span>
                    </div>
                </div>
                <div class="ch-stat-divider"></div>
                <div class="ch-stat-item">
                    <div class="ch-stat-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="ch-stat-content">
                        <span class="ch-stat-number">500+</span>
                        <span class="ch-stat-label">Ceremonies</span>
                    </div>
                </div>
                <div class="ch-stat-divider"></div>
                <div class="ch-stat-item">
                    <div class="ch-stat-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="ch-stat-content">
                        <span class="ch-stat-number">15+</span>
                        <span class="ch-stat-label">Premium Teas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ch-hero-scroll">
        <span>Scroll to discover</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
