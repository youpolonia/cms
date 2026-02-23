<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Space?');
$ctaDesc = theme_get('cta.description', 'Get in touch today for a free, no-obligation quote. Our team is ready to discuss your project and bring your vision to life.');
$ctaBtnText = theme_get('cta.btn_text', 'Request Free Quote');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="section cta-section" id="cta">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-text">
                <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
                <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            </div>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="btn btn-primary btn-lg"
                   data-ts="cta.btn_text"
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div class="cta-contact">
                    <a href="mailto:info@edispaving.co.uk" class="cta-email">
                        <i class="fas fa-envelope"></i>
                        info@edispaving.co.uk
                    </a>
                </div>
            </div>
        </div>
        <div class="cta-features" data-animate>
            <div class="cta-feature">
                <i class="fas fa-check-circle"></i>
                <span>Free Quotes</span>
            </div>
            <div class="cta-feature">
                <i class="fas fa-check-circle"></i>
                <span>Fully Insured</span>
            </div>
            <div class="cta-feature">
                <i class="fas fa-check-circle"></i>
                <span>Quality Guaranteed</span>
            </div>
        </div>
    </div>
</section>