<?php
$heroHeadline = theme_get('hero.headline', 'Decoding Health Through Genomic Precision');
$heroSubtitle = theme_get('hero.subtitle', 'We analyze your unique genetic blueprint to deliver personalized treatment plans, transforming healthcare one genome at a time.');
$heroBtnText = theme_get('hero.btn_text', 'Explore Our Services');
$heroBtnLink = theme_get('hero.btn_link', '#services');
$heroBgImage = theme_get('hero.bg_image', '');
?>
<section class="gp-hero" id="hero">
    <?php if ($heroBgImage): ?>
        <div class="gp-hero__bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="gp-hero__overlay"></div>
    <div class="container">
        <div class="gp-hero__content" data-animate>
            <span class="gp-hero__badge" data-ts="hero.badge">Pioneering Personalized Medicine</span>
            <h1 class="gp-hero__headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="gp-hero__subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="gp-hero__actions">
                <a href="<?= esc($heroBtnLink) ?>" class="gp-hero__btn gp-hero__btn--primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="gp-hero__btn gp-hero__btn--secondary">
                    Learn More
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
            <div class="gp-hero__stats">
                <div class="gp-hero__stat">
                    <span class="gp-hero__stat-number">99.8%</span>
                    <span class="gp-hero__stat-label">Accuracy Rate</span>
                </div>
                <div class="gp-hero__stat">
                    <span class="gp-hero__stat-number">10,000+</span>
                    <span class="gp-hero__stat-label">Genomes Analyzed</span>
                </div>
                <div class="gp-hero__stat">
                    <span class="gp-hero__stat-number">50+</span>
                    <span class="gp-hero__stat-label">Research Partners</span>
                </div>
            </div>
        </div>
    </div>
    <div class="gp-hero__scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</section>
