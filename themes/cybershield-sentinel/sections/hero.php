<?php
$heroBadge = theme_get('hero.badge', 'Enterprise Security Solutions');
$heroHeadline = theme_get('hero.headline', 'Fortify Your Digital Infrastructure');
$heroSubtitle = theme_get('hero.subtitle', 'Next-generation firewall protection, advanced intrusion detection, DDoS mitigation, and 24/7 Security Operations Center monitoring to defend your enterprise against evolving cyber threats.');
$heroBtnText = theme_get('hero.btn_text', 'Request Security Assessment');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_btn_text', 'View Our Solutions');
$heroSecondaryLink = theme_get('hero.secondary_btn_link', '#services');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');
?>
<section class="csh-hero">
    <div class="csh-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="csh-hero-overlay"></div>
    <div class="csh-hero-grid-pattern"></div>
    <div class="csh-hero-content">
        <div class="container">
            <div class="csh-hero-inner" data-animate>
                <div class="csh-hero-badge" data-ts="hero.badge">
                    <i class="fas fa-shield-alt"></i>
                    <?= esc($heroBadge) ?>
                </div>
                <h1 class="csh-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
                <p class="csh-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
                <div class="csh-hero-actions">
                    <a href="<?= esc($heroBtnLink) ?>" class="csh-btn csh-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                        <i class="fas fa-lock"></i>
                        <?= esc($heroBtnText) ?>
                    </a>
                    <a href="<?= esc($heroSecondaryLink) ?>" class="csh-btn csh-btn-outline" data-ts="hero.secondary_btn_text" data-ts-href="hero.secondary_btn_link">
                        <?= esc($heroSecondaryText) ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="csh-hero-trust">
                    <div class="csh-trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>SOC 2 Type II Certified</span>
                    </div>
                    <div class="csh-trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>24/7 Monitoring</span>
                    </div>
                    <div class="csh-trust-item">
                        <i class="fas fa-check-circle"></i>
                        <span>99.99% Uptime SLA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="csh-hero-scroll">
        <span>Explore</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
