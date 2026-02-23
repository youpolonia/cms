<?php
$heroBadge = theme_get('hero.badge', 'AI-POWERED BANKING');
$heroHeadline = theme_get('hero.headline', 'Banking Built for the Future of Work');
$heroSubtitle = theme_get('hero.subtitle', 'Instant invoicing, multi-currency accounts, and AI expense categorization designed for freelancers and digital nomads.');
$heroBtnText = theme_get('hero.btn_text', 'Start Free Trial');
$heroBtnLink = theme_get('hero.btn_link', '#signup');
$heroBgImage = theme_get('hero.bg_image', '');
?>
<section class="nf-hero-section" id="hero">
    <?php if ($heroBgImage): ?>
    <div class="nf-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="nf-hero-overlay"></div>
    <div class="container">
        <div class="nf-hero-content" data-animate>
            <span class="nf-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <h1 class="nf-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="nf-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="nf-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="nf-hero-btn nf-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#features" class="nf-hero-btn nf-btn-outline">
                    <i class="fas fa-play-circle"></i>
                    <span>See How It Works</span>
                </a>
            </div>
            <div class="nf-hero-stats">
                <div class="nf-hero-stat">
                    <span class="nf-stat-number">24/7</span>
                    <span class="nf-stat-label">Instant Payments</span>
                </div>
                <div class="nf-hero-stat">
                    <span class="nf-stat-number">150+</span>
                    <span class="nf-stat-label">Currencies Supported</span>
                </div>
                <div class="nf-hero-stat">
                    <span class="nf-stat-number">99.9%</span>
                    <span class="nf-stat-label">Uptime SLA</span>
                </div>
            </div>
        </div>
    </div>
    <div class="nf-hero-scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
