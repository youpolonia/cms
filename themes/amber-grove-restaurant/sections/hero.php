<?php
$heroBadge = theme_get('hero.badge', 'EST. 2018');
$heroHeadline = theme_get('hero.headline', 'Where Culinary Art Meets Timeless Elegance');
$heroSubtitle = theme_get('hero.subtitle', 'An intimate dining experience featuring seasonal tasting menus, an award-winning wine cellar, and impeccable service in the heart of the city.');
$heroBtnText = theme_get('hero.btn_text', 'Reserve Your Table');
$heroBtnLink = theme_get('hero.btn_link', '#reservations');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="agr-hero" id="hero">
    <div class="agr-hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="agr-hero-overlay"></div>
    <div class="container">
        <div class="agr-hero-content" data-animate>
            <span class="agr-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="agr-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="agr-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="agr-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="agr-btn agr-btn--primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#menu" class="agr-btn agr-btn--outline">
                    View Our Menu <i class="fas fa-utensils"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="agr-hero-scroll">
        <a href="#about" class="agr-scroll-hint" aria-label="Scroll down">
            <span class="agr-scroll-line"></span>
        </a>
    </div>
</section>
