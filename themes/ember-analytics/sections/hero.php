<?php
$heroBadge = theme_get('hero.badge', 'AI-Powered Analytics');
$heroHeadline = theme_get('hero.headline', 'Transform Your E-Commerce Data Into Growth');
$heroSubtitle = theme_get('hero.subtitle', 'Real-time dashboards, predictive insights, and one-click integrations with Shopify and WooCommerce. Make data-driven decisions that drive revenue.');
$heroBtnText = theme_get('hero.btn_text', 'Start Free Trial');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_text', 'Watch Demo');
$heroSecondaryLink = theme_get('hero.secondary_link', '#features');
$heroBgImage = theme_get('hero.bg_image', $themePath . '/assets/hero-bg.jpg');
?>
<section class="ea-hero" id="hero">
    <div class="ea-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <div class="ea-hero-overlay"></div>
    <div class="ea-hero-particles">
        <div class="ea-particle ea-particle-1"></div>
        <div class="ea-particle ea-particle-2"></div>
        <div class="ea-particle ea-particle-3"></div>
        <div class="ea-particle ea-particle-4"></div>
    </div>
    <div class="ea-hero-content">
        <div class="ea-hero-container">
            <div class="ea-hero-text" data-animate>
                <span class="ea-hero-badge" data-ts="hero.badge">
                    <i class="fas fa-bolt"></i>
                    <?= esc($heroBadge) ?>
                </span>
                <h1 class="ea-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
                <p class="ea-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
                <div class="ea-hero-actions">
                    <a href="<?= esc($heroBtnLink) ?>" class="ea-btn ea-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                        <?= esc($heroBtnText) ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="<?= esc($heroSecondaryLink) ?>" class="ea-btn ea-btn-ghost" data-ts="hero.secondary_text" data-ts-href="hero.secondary_link">
                        <i class="fas fa-play-circle"></i>
                        <?= esc($heroSecondaryText) ?>
                    </a>
                </div>
            </div>
            <div class="ea-hero-visual" data-animate>
                <div class="ea-dashboard-preview">
                    <div class="ea-dashboard-header">
                        <div class="ea-dashboard-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <span class="ea-dashboard-title">Analytics Dashboard</span>
                    </div>
                    <div class="ea-dashboard-body">
                        <div class="ea-dashboard-chart">
                            <div class="ea-chart-bar" style="--height: 60%;"></div>
                            <div class="ea-chart-bar" style="--height: 80%;"></div>
                            <div class="ea-chart-bar" style="--height: 45%;"></div>
                            <div class="ea-chart-bar" style="--height: 90%;"></div>
                            <div class="ea-chart-bar" style="--height: 70%;"></div>
                            <div class="ea-chart-bar" style="--height: 95%;"></div>
                        </div>
                        <div class="ea-dashboard-stats">
                            <div class="ea-mini-stat">
                                <span class="ea-mini-stat-value">+47%</span>
                                <span class="ea-mini-stat-label">Revenue</span>
                            </div>
                            <div class="ea-mini-stat">
                                <span class="ea-mini-stat-value">2.4k</span>
                                <span class="ea-mini-stat-label">Orders</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ea-hero-integrations" data-animate>
            <span class="ea-integrations-label">Integrates with:</span>
            <div class="ea-integrations-logos">
                <div class="ea-integration-logo"><i class="fab fa-shopify"></i> Shopify</div>
                <div class="ea-integration-logo"><i class="fab fa-wordpress"></i> WooCommerce</div>
                <div class="ea-integration-logo"><i class="fab fa-stripe"></i> Stripe</div>
                <div class="ea-integration-logo"><i class="fab fa-google"></i> Analytics</div>
            </div>
        </div>
    </div>
</section>
