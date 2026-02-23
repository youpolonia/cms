<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Freelance Finances?');
$ctaDescription = theme_get('cta.description', 'Join 50,000+ freelancers who have already streamlined their financial operations with Velocity Bank. Start your free trial today and experience the future of independent banking.');
$ctaBtnText = theme_get('cta.btn_text', 'Start Free Trial');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_text', 'Schedule Demo');
$ctaSecondaryLink = theme_get('cta.secondary_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="vbf-cta" id="cta">
    <div class="vbf-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <div class="vbf-cta-overlay"></div>
    <div class="vbf-cta-pattern"></div>
    
    <div class="container">
        <div class="vbf-cta-content" data-animate>
            <h2 class="vbf-cta-title" data-ts="cta.title">
                <?= esc($ctaTitle) ?>
            </h2>
            <p class="vbf-cta-description" data-ts="cta.description">
                <?= esc($ctaDescription) ?>
            </p>
            
            <div class="vbf-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="vbf-cta-btn vbf-cta-btn--primary" 
                   data-ts="cta.btn_text" 
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($ctaSecondaryLink) ?>" 
                   class="vbf-cta-btn vbf-cta-btn--secondary" 
                   data-ts="cta.secondary_text" 
                   data-ts-href="cta.secondary_link">
                    <i class="fas fa-calendar"></i>
                    <?= esc($ctaSecondaryText) ?>
                </a>
            </div>
            
            <div class="vbf-cta-features">
                <div class="vbf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>14-day free trial</span>
                </div>
                <div class="vbf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>No credit card required</span>
                </div>
                <div class="vbf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Cancel anytime</span>
                </div>
            </div>
        </div>
    </div>
</section>
