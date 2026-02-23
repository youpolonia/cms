<?php
$heroBadge = theme_get('hero.badge', '23+ Years of Excellence');
$heroHeadline = theme_get('hero.headline', 'Every Great Project Starts With Edi\'s Paving Contractors');
$heroSubtitle = theme_get('hero.subtitle', 'Professional paving and groundwork solutions for commercial and residential clients across Essex');
$heroBtnText = theme_get('hero.btn_text', 'Get Free Quote');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-paving.jpg');
?>
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="hero-overlay"></div>
    <div class="hero-geometric">
        <svg viewBox="0 0 100 100" preserveAspectRatio="none">
            <polygon points="0,100 100,60 100,100" fill="var(--color-background)"/>
        </svg>
    </div>
    <div class="hero-content">
        <div class="hero-text" data-animate>
            <div class="hero-badge" data-ts="hero.badge">
                <i class="fas fa-hard-hat"></i>
                <?= esc($heroBadge) ?>
            </div>
            <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" 
                   class="btn btn-primary btn-lg"
                   data-ts="hero.btn_text"
                   data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#projects" class="btn btn-outline btn-lg">
                    View Our Work
                    <i class="fas fa-images"></i>
                </a>
            </div>
        </div>
        <div class="hero-stats" data-animate>
            <div class="stat-item">
                <span class="stat-number">23+</span>
                <span class="stat-label">Years Experience</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Projects Completed</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">Satisfaction</span>
            </div>
        </div>
    </div>
    <div class="hero-scroll">
        <a href="#about" class="scroll-indicator">
            <span>Scroll</span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>