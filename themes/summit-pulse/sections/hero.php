<?php
$heroBadge = theme_get('hero.badge', 'Boulder • Climb • Conquer');
$heroHeadline = theme_get('hero.headline', 'Reach Your Peak at Summit Pulse');
$heroSubtitle = theme_get('hero.subtitle', 'World-class bouldering walls, auto-belay systems, and expert coaching. Whether you\'re a first-timer or a seasoned climber, find your community here.');
$heroBtnText = theme_get('hero.btn_text', 'Start Climbing Today');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'View Memberships');
$heroBtn2Link = theme_get('hero.btn2_link', '#pricing');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');

$heroStat1 = theme_get('hero.stat1_number', '50+');
$heroStat1Label = theme_get('hero.stat1_label', 'Climbing Routes');
$heroStat2 = theme_get('hero.stat2_number', '12');
$heroStat2Label = theme_get('hero.stat2_label', 'Auto-Belay Walls');
$heroStat3 = theme_get('hero.stat3_number', '500+');
$heroStat3Label = theme_get('hero.stat3_label', 'Active Members');
?>
<section class="sp-hero" id="hero">
    <div class="sp-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="sp-hero-overlay"></div>
    
    <div class="sp-hero-content">
        <div class="sp-hero-container">
            <div class="sp-hero-text" data-animate>
                <span class="sp-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
                <h1 class="sp-hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
                <p class="sp-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
                
                <div class="sp-hero-actions">
                    <a href="<?= esc($heroBtnLink) ?>" class="sp-hero-btn sp-hero-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                        <?= esc($heroBtnText) ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="<?= esc($heroBtn2Link) ?>" class="sp-hero-btn sp-hero-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link">
                        <?= esc($heroBtn2Text) ?>
                    </a>
                </div>
            </div>
            
            <div class="sp-hero-stats" data-animate>
                <div class="sp-hero-stat">
                    <span class="sp-stat-number" data-ts="hero.stat1_number"><?= esc($heroStat1) ?></span>
                    <span class="sp-stat-label" data-ts="hero.stat1_label"><?= esc($heroStat1Label) ?></span>
                </div>
                <div class="sp-hero-stat">
                    <span class="sp-stat-number" data-ts="hero.stat2_number"><?= esc($heroStat2) ?></span>
                    <span class="sp-stat-label" data-ts="hero.stat2_label"><?= esc($heroStat2Label) ?></span>
                </div>
                <div class="sp-hero-stat">
                    <span class="sp-stat-number" data-ts="hero.stat3_number"><?= esc($heroStat3) ?></span>
                    <span class="sp-stat-label" data-ts="hero.stat3_label"><?= esc($heroStat3Label) ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="sp-hero-scroll">
        <span>Scroll to explore</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
