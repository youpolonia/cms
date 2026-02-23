<?php
$heroBadge = theme_get('hero.badge', 'AI-Powered Logistics');
$heroHeadline = theme_get('hero.headline', 'Autonomous Delivery for the Modern City');
$heroSubtitle = theme_get('hero.subtitle', 'Our fleet of intelligent robots and AI route optimization delivers your packages faster, greener, and more reliably than ever before.');
$heroBtnText = theme_get('hero.btn_text', 'Schedule Demo');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_text', 'View Fleet');
$heroSecondaryLink = theme_get('hero.secondary_link', '#features');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="hero-overlay"></div>
    <div class="hero-grid-pattern"></div>
    
    <div class="hero-content">
        <div class="container">
            <div class="hero-layout">
                <div class="hero-text" data-animate>
                    <div class="hero-badge">
                        <span class="badge-icon"><i class="fas fa-microchip"></i></span>
                        <span class="badge-text" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
                        <span class="badge-pulse"></span>
                    </div>
                    <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
                    <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
                    <div class="hero-actions">
                        <a href="<?= esc($heroBtnLink) ?>" 
                           class="btn btn-primary btn-lg"
                           data-ts="hero.btn_text"
                           data-ts-href="hero.btn_link">
                            <span><?= esc($heroBtnText) ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="<?= esc($heroSecondaryLink) ?>" 
                           class="btn btn-outline btn-lg"
                           data-ts="hero.secondary_text"
                           data-ts-href="hero.secondary_link">
                            <span><?= esc($heroSecondaryText) ?></span>
                        </a>
                    </div>
                </div>
                <div class="hero-visual" data-animate>
                    <div class="visual-ring visual-ring-1"></div>
                    <div class="visual-ring visual-ring-2"></div>
                    <div class="visual-ring visual-ring-3"></div>
                    <div class="visual-bot">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="visual-dots">
                        <span class="dot dot-1"></span>
                        <span class="dot dot-2"></span>
                        <span class="dot dot-3"></span>
                        <span class="dot dot-4"></span>
                    </div>
                </div>
            </div>
            
            <div class="hero-metrics" data-animate>
                <div class="metric">
                    <span class="metric-value">99.8%</span>
                    <span class="metric-label">On-Time Delivery</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric">
                    <span class="metric-value">2hr</span>
                    <span class="metric-label">Avg. Delivery Time</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric">
                    <span class="metric-value">0</span>
                    <span class="metric-label">Carbon Emissions</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll">
        <span>Scroll to explore</span>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>
