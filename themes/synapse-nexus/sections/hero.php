<?php
$heroBadge = theme_get('hero.badge', '✨ New Feature');
$heroHeadline = theme_get('hero.headline', 'Where Teams Connect & Code Together');
$heroSubtitle = theme_get('hero.subtitle', 'A modern communication platform with channels, threads, video calls, and 500+ integrations. Built for remote‑first engineering teams.');
$heroBtnPrimaryText = theme_get('hero.btn_primary_text', 'Start Free Trial');
$heroBtnPrimaryLink = theme_get('hero.btn_primary_link', '#signup');
$heroBtnSecondaryText = theme_get('hero.btn_secondary_text', 'Watch Demo');
$heroBtnSecondaryLink = theme_get('hero.btn_secondary_link', '#demo');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="sn-hero" id="hero">
    <div class="sn-hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="sn-hero-overlay"></div>
    <div class="container">
        <div class="sn-hero-content" data-animate>
            <span class="sn-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="sn-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="sn-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="sn-hero-actions">
                <a href="<?= esc($heroBtnPrimaryLink) ?>" class="sn-btn sn-btn-primary sn-btn-hero" data-ts="hero.btn_primary_text" data-ts-href="hero.btn_primary_link">
                    <?= esc($heroBtnPrimaryText) ?> <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($heroBtnSecondaryLink) ?>" class="sn-btn sn-btn-secondary sn-btn-hero" data-ts="hero.btn_secondary_text" data-ts-href="hero.btn_secondary_link">
                    <i class="fas fa-play-circle"></i> <?= esc($heroBtnSecondaryText) ?>
                </a>
            </div>
            <div class="sn-hero-stats">
                <div class="sn-hero-stat">
                    <span class="sn-hero-stat-number">500+</span>
                    <span class="sn-hero-stat-label">Integrations</span>
                </div>
                <div class="sn-hero-stat">
                    <span class="sn-hero-stat-number">99.9%</span>
                    <span class="sn-hero-stat-label">Uptime SLA</span>
                </div>
                <div class="sn-hero-stat">
                    <span class="sn-hero-stat-number">∞</span>
                    <span class="sn-hero-stat-label">Channels & Threads</span>
                </div>
            </div>
        </div>
    </div>
    <div class="sn-hero-scroll-hint">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
