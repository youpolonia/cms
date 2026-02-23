<?php
$ctaTitle = theme_get('cta.title', 'Ready to Experience Premium WordPress Hosting?');
$ctaDesc = theme_get('cta.description', 'Join thousands of professionals who trust VerdantPress to power their WordPress sites. Get started with a 30-day money-back guarantee.');
$ctaBtnText = theme_get('cta.btn_text', 'Start Your Free Trial');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_btn_text', 'Schedule a Demo');
$ctaSecondaryLink = theme_get('cta.secondary_btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>
<section class="vp-section vp-cta-section" id="cta">
    <div class="vp-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <div class="vp-cta-overlay"></div>
    
    <div class="container">
        <div class="vp-cta-content" data-animate>
            <h2 class="vp-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="vp-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            
            <div class="vp-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="vp-btn vp-btn-primary vp-btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($ctaSecondaryLink) ?>" class="vp-btn vp-btn-secondary vp-btn-lg" data-ts="cta.secondary_btn_text" data-ts-href="cta.secondary_btn_link">
                    <i class="fas fa-calendar"></i>
                    <?= esc($ctaSecondaryText) ?>
                </a>
            </div>
            
            <div class="vp-cta-trust">
                <div class="vp-trust-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>30-Day Money Back</span>
                </div>
                <div class="vp-trust-item">
                    <i class="fas fa-credit-card"></i>
                    <span>No Credit Card Required</span>
                </div>
                <div class="vp-trust-item">
                    <i class="fas fa-clock"></i>
                    <span>Setup in 5 Minutes</span>
                </div>
            </div>
        </div>
    </div>
</section>
