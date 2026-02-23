<?php
$ctaTitle = theme_get('cta.title', 'Ready to Eliminate Passwords from Your Organization?');
$ctaDesc = theme_get('cta.description', 'Join 500+ enterprises that have modernized their identity infrastructure with Sentinel Shield. Schedule a personalized demo and see how zero-trust authentication can protect your organization.');
$ctaBtnText = theme_get('cta.btn_text', 'Request Enterprise Demo');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="ss-cta-section">
    <div class="ss-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="ss-cta-overlay"></div>
    <div class="container">
        <div class="ss-cta-content" data-animate>
            <div class="ss-cta-text-zone">
                <h2 class="ss-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
                <p class="ss-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
                <div class="ss-cta-features">
                    <div class="ss-cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Free 30-day trial</span>
                    </div>
                    <div class="ss-cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="ss-cta-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Dedicated integration support</span>
                    </div>
                </div>
            </div>
            <div class="ss-cta-action-zone">
                <a href="<?= esc($ctaBtnLink) ?>" class="ss-btn ss-btn-cta" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <span><?= esc($ctaBtnText) ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <p class="ss-cta-note">
                    <i class="fas fa-lock"></i>
                    <span>Enterprise-grade security. SOC 2 Type II certified.</span>
                </p>
            </div>
        </div>
    </div>
</section>
