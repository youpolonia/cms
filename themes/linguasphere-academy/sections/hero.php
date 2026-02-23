<?php
$heroBgImage = 'https://images.pexels.com/photos/5905741/pexels-photo-5905741.jpeg?auto=compress&cs=tinysrgb&h=650&w=940';
$heroHeadline = theme_get('hero.headline', 'Speak Fluently, Live Globally');
$heroSubtitle = theme_get('hero.subtitle', 'Master English, Spanish, French, or Mandarin through immersive conversation and cultural exchange. Join a community where language comes alive.');
$heroBtnText = theme_get('hero.btn_text', 'Start Your Journey');
$heroBtnLink = theme_get('hero.btn_link', '#enroll');
$heroBadge = theme_get('hero.badge', 'New: Cultural Exchange Program');
?>
<section class="lsa-hero" id="hero">
    <div class="lsa-hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="lsa-hero-overlay"></div>
    <div class="container">
        <div class="lsa-hero-content" data-animate>
            <span class="lsa-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="lsa-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="lsa-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="lsa-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="lsa-btn lsa-btn-primary lsa-btn-hero" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right lsa-btn-icon"></i>
                </a>
                <a href="#methodology" class="lsa-btn lsa-btn-outline">
                    <i class="fas fa-play-circle"></i>
                    How It Works
                </a>
            </div>
            <div class="lsa-hero-stats">
                <div class="lsa-stat">
                    <span class="lsa-stat-number">4</span>
                    <span class="lsa-stat-label">Languages</span>
                </div>
                <div class="lsa-stat">
                    <span class="lsa-stat-number">500+</span>
                    <span class="lsa-stat-label">Active Students</span>
                </div>
                <div class="lsa-stat">
                    <span class="lsa-stat-number">98%</span>
                    <span class="lsa-stat-label">Satisfaction Rate</span>
                </div>
                <div class="lsa-stat">
                    <span class="lsa-stat-number">24/7</span>
                    <span class="lsa-stat-label">Live Practice</span>
                </div>
            </div>
        </div>
    </div>
    <div class="lsa-hero-scroll">
        <a href="#features" aria-label="Scroll down">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>
