<?php
$heroLabel = theme_get('hero.badge', 'ENTERPRISE SECURITY');
$heroHeadline = theme_get('hero.headline', 'Passwordless Authentication for the Modern Enterprise');
$heroSubtitle = theme_get('hero.subtitle', 'Deploy zero-trust identity management with biometric verification, SSO integration, and real-time compliance monitoring across your entire organization.');
$heroBtnText = theme_get('hero.btn_text', 'Request Demo');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryBtn = theme_get('hero.secondary_btn_text', 'View Documentation');
$heroSecondaryLink = theme_get('hero.secondary_btn_link', '/services');
$heroBgImage = theme_get('hero.bg_image', '');
?>
<section class="ss-hero">
    <div class="ss-hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>')"></div>
    <div class="ss-hero-overlay"></div>
    <div class="ss-hero-content">
        <div class="container">
            <div class="ss-hero-inner">
                <div class="ss-hero-text-zone">
                    <span class="ss-hero-badge" data-ts="hero.badge" data-animate><?= esc($heroLabel) ?></span>
                    <h1 class="ss-hero-headline" data-ts="hero.headline" data-animate><?= esc($heroHeadline) ?></h1>
                    <p class="ss-hero-subtitle" data-ts="hero.subtitle" data-animate><?= esc($heroSubtitle) ?></p>
                    <div class="ss-hero-actions" data-animate>
                        <a href="<?= esc($heroBtnLink) ?>" class="ss-btn ss-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                            <span><?= esc($heroBtnText) ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="<?= esc($heroSecondaryLink) ?>" class="ss-btn ss-btn-ghost" data-ts="hero.secondary_btn_text" data-ts-href="hero.secondary_btn_link">
                            <i class="fas fa-book-open"></i>
                            <span><?= esc($heroSecondaryBtn) ?></span>
                        </a>
                    </div>
                    <div class="ss-hero-trust-row" data-animate>
                        <div class="ss-trust-stat">
                            <i class="fas fa-building"></i>
                            <span>500+ Enterprises</span>
                        </div>
                        <div class="ss-trust-stat">
                            <i class="fas fa-globe"></i>
                            <span>120 Countries</span>
                        </div>
                        <div class="ss-trust-stat">
                            <i class="fas fa-certificate"></i>
                            <span>99.99% Uptime SLA</span>
                        </div>
                    </div>
                </div>
                <div class="ss-hero-visual-zone" data-animate>
                    <div class="ss-security-dashboard">
                        <div class="ss-dashboard-header">
                            <div class="ss-dash-status">
                                <span class="ss-status-dot"></span>
                                <span>All Systems Secure</span>
                            </div>
                            <div class="ss-dash-time">
                                <i class="fas fa-clock"></i>
                                <span>Real-time Monitoring</span>
                            </div>
                        </div>
                        <div class="ss-dashboard-metrics">
                            <div class="ss-metric-card">
                                <div class="ss-metric-icon">
                                    <i class="fas fa-fingerprint"></i>
                                </div>
                                <div class="ss-metric-data">
                                    <span class="ss-metric-value">12,847</span>
                                    <span class="ss-metric-label">Biometric Logins Today</span>
                                </div>
                            </div>
                            <div class="ss-metric-card">
                                <div class="ss-metric-icon">
                                    <i class="fas fa-shield-check"></i>
                                </div>
                                <div class="ss-metric-data">
                                    <span class="ss-metric-value">100%</span>
                                    <span class="ss-metric-label">Threat Prevention</span>
                                </div>
                            </div>
                            <div class="ss-metric-card">
                                <div class="ss-metric-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="ss-metric-data">
                                    <span class="ss-metric-value">2.4s</span>
                                    <span class="ss-metric-label">Avg. Auth Time</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
