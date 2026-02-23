<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Outdoor Space?');
$ctaDesc = theme_get('cta.description', 'Schedule your free, no-obligation consultation. We\'ll assess your property, discuss your vision, and provide a detailed quote.');
$ctaBtnText = theme_get('cta.btn_text', 'Schedule Free Consultation');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="tf-section tf-cta" id="cta">
    <div class="tf-cta-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
    <div class="tf-cta-overlay"></div>
    <div class="container">
        <div class="tf-cta-content" data-animate>
            <h2 class="tf-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="tf-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="tf-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="tf-btn tf-btn-primary tf-btn-large" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-calendar-check tf-btn-icon"></i>
                </a>
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '+1234567890'))) ?>" class="tf-btn tf-btn-outline tf-btn-large">
                    <i class="fas fa-phone-alt tf-btn-icon"></i>
                    Call Now: <?= esc(theme_get('header.phone', '(555) 123-4567')) ?>
                </a>
            </div>
            <div class="tf-cta-features">
                <div class="tf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Free On-Site Consultation</span>
                </div>
                <div class="tf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Detailed Written Quote</span>
                </div>
                <div class="tf-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Flexible Financing Options</span>
                </div>
            </div>
        </div>
    </div>
</section>
