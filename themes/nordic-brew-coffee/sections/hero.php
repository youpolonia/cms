<?php
$heroBadge = theme_get('hero.badge', 'Single-Origin Specialty');
$heroHeadline = theme_get('hero.headline', 'Coffee, Simplified');
$heroSubtitle = theme_get('hero.subtitle', 'Third-wave pour-over bar with Scandinavian soul. Every cup tells the story of its origin.');
$heroBtnText = theme_get('hero.btn_text', 'View Our Menu');
$heroBtnLink = theme_get('hero.btn_link', '#menu');
$heroBtn2Text = theme_get('hero.btn2_text', 'Our Story');
$heroBtn2Link = theme_get('hero.btn2_link', '#about');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" data-animate>
        <span class="hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
        <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
            <a href="<?= esc($heroBtn2Link) ?>" class="btn btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a>
        </div>
    </div>
    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>
