<?php
$heroBadge = theme_get('hero.badge', 'AI-Powered Banking');
$heroHeadline = theme_get('hero.headline', 'Financial Intelligence for the Independent Economy');
$heroSubtitle = theme_get('hero.subtitle', 'Multi-currency accounts, instant invoicing, and AI-powered expense insights built specifically for freelancers and digital nomads.');
$heroBtnText = theme_get('hero.btn_text', 'Start Free Trial');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryBtnText = theme_get('hero.secondary_btn_text', 'Watch Demo');
$heroSecondaryBtnLink = theme_get('hero.secondary_btn_link', '#features');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
$heroStat1Label = theme_get('hero.stat1_label', 'Active Users');
$heroStat1Value = theme_get('hero.stat1_value', '50K+');
$heroStat2Label = theme_get('hero.stat2_label', 'Transactions/Month');
$heroStat2Value = theme_get('hero.stat2_value', '2M+');
$heroStat3Label = theme_get('hero.stat3_label', 'Countries Supported');
$heroStat3Value = theme_get('hero.stat3_value', '120+');
?>
<section class="vbf-hero" id="hero">
    <div class="vbf-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="vbf-hero-overlay"></div>
    <div class="vbf-hero-grid-pattern"></div>
    
    <div class="vbf-hero-content">
        <div class="vbf-hero-inner">
            <div class="vbf-hero-badge" data-animate>
                <span class="vbf-badge-icon"><i class="fas fa-bolt"></i></span>
                <span data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            </div>
            
            <h1 class="vbf-hero-headline" data-animate data-ts="hero.headline">
                <?= esc($heroHeadline) ?>
            </h1>
            
            <p class="vbf-hero-subtitle" data-animate data-ts="hero.subtitle">
                <?= esc($heroSubtitle) ?>
            </p>
            
            <div class="vbf-hero-actions" data-animate>
                <a href="<?= esc($heroBtnLink) ?>" 
                   class="vbf-hero-btn vbf-hero-btn--primary" 
                   data-ts="hero.btn_text" 
                   data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($heroSecondaryBtnLink) ?>" 
                   class="vbf-hero-btn vbf-hero-btn--secondary" 
                   data-ts="hero.secondary_btn_text" 
                   data-ts-href="hero.secondary_btn_link">
                    <i class="fas fa-play-circle"></i>
                    <?= esc($heroSecondaryBtnText) ?>
                </a>
            </div>
            
            <div class="vbf-hero-stats" data-animate>
                <div class="vbf-hero-stat">
                    <div class="vbf-stat-value" data-ts="hero.stat1_value"><?= esc($heroStat1Value) ?></div>
                    <div class="vbf-stat-label" data-ts="hero.stat1_label"><?= esc($heroStat1Label) ?></div>
                </div>
                <div class="vbf-hero-stat-divider"></div>
                <div class="vbf-hero-stat">
                    <div class="vbf-stat-value" data-ts="hero.stat2_value"><?= esc($heroStat2Value) ?></div>
                    <div class="vbf-stat-label" data-ts="hero.stat2_label"><?= esc($heroStat2Label) ?></div>
                </div>
                <div class="vbf-hero-stat-divider"></div>
                <div class="vbf-hero-stat">
                    <div class="vbf-stat-value" data-ts="hero.stat3_value"><?= esc($heroStat3Value) ?></div>
                    <div class="vbf-stat-label" data-ts="hero.stat3_label"><?= esc($heroStat3Label) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="vbf-hero-scroll-indicator" data-animate>
        <span class="vbf-scroll-text">Scroll to explore</span>
        <div class="vbf-scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>
