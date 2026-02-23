<?php
$ctaTitle = theme_get('cta.title', 'Ready to Unlock Your Data\'s Potential?');
$ctaDesc = theme_get('cta.description', 'Start your 14-day free trial today. No credit card required. Connect your store in under 60 seconds.');
$ctaBtnText = theme_get('cta.btn_text', 'Start Free Trial');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_text', 'Schedule a Demo');
$ctaSecondaryLink = theme_get('cta.secondary_link', '#contact');
?>
<section class="ea-cta" id="cta">
    <div class="ea-cta-bg">
        <div class="ea-cta-glow ea-cta-glow-1"></div>
        <div class="ea-cta-glow ea-cta-glow-2"></div>
    </div>
    <div class="ea-cta-container">
        <div class="ea-cta-content" data-animate>
            <h2 class="ea-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="ea-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="ea-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="ea-btn ea-btn-light" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($ctaSecondaryLink) ?>" class="ea-btn ea-btn-outline-light" data-ts="cta.secondary_text" data-ts-href="cta.secondary_link">
                    <i class="fas fa-calendar-alt"></i>
                    <?= esc($ctaSecondaryText) ?>
                </a>
            </div>
            <div class="ea-cta-features">
                <div class="ea-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>14-day free trial</span>
                </div>
                <div class="ea-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>No credit card</span>
                </div>
                <div class="ea-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Cancel anytime</span>
                </div>
            </div>
        </div>
    </div>
</section>
