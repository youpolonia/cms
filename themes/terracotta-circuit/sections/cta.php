<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Delivery Operations?');
$ctaDesc = theme_get('cta.description', 'Join hundreds of businesses already using autonomous delivery to delight customers and cut costs. Schedule a demo to see our fleet in action.');
$ctaBtnText = theme_get('cta.btn_text', 'Schedule Demo');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_text', 'Contact Sales');
$ctaSecondaryLink = theme_get('cta.secondary_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="section cta-section" id="cta">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="cta-overlay"></div>
    <div class="cta-circuit-lines"></div>
    
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-badge">
                <i class="fas fa-rocket"></i>
                <span>Get Started Today</span>
            </div>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="btn btn-primary btn-lg"
                   data-ts="cta.btn_text"
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= esc($ctaSecondaryLink) ?>" 
                   class="btn btn-outline-light btn-lg"
                   data-ts="cta.secondary_text"
                   data-ts-href="cta.secondary_link">
                    <?= esc($ctaSecondaryText) ?>
                </a>
            </div>
            <div class="cta-trust">
                <div class="trust-item">
                    <i class="fas fa-check-circle"></i>
                    <span>No setup fees</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Launch in 2 weeks</span>
                </div>
                <div class="trust-item">
                    <i class="fas fa-check-circle"></i>
                    <span>24/7 support</span>
                </div>
            </div>
        </div>
    </div>
</section>
