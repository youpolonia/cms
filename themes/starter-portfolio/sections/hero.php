<?php
/**
 * Starter Portfolio — Hero Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroTagline  = theme_get('hero.tagline', get_setting('hero_tagline') ?: 'Creative Portfolio');
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Explore our projects, read our blog, and discover what we do best.');
$heroBtnText  = theme_get('hero.btn_text', 'View Projects');
$heroBtnLink  = theme_get('hero.btn_link', '#projects');
$heroLabel    = theme_get('hero.label', get_setting('hero_label') ?: 'Welcome');
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-label">
        <span data-ts="hero.label"><?= esc($heroLabel) ?></span>
    </div>
    <h1 class="hero-title">
        <span class="text-stroke" data-ts="hero.headline"><?= esc($heroHeadline) ?></span><br>
        <span class="text-gradient" data-ts="hero.tagline"><?= esc($heroTagline) ?></span>
    </h1>
    <p class="hero-description" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
    <div class="hero-cta-group">
        <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
        <a href="/articles" class="btn btn-outline">Read Blog</a>
    </div>
    <div class="hero-scroll-indicator">
        <div class="scroll-line"></div>
        <span>Scroll</span>
    </div>
</section>
