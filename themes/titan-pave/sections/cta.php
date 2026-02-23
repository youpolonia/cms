<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Outdoor Space?');
$ctaDesc = theme_get('cta.description', 'Get in touch today for a free, no-obligation quote. We\'ll discuss your project and provide expert advice tailored to your needs and budget.');
$ctaBtnText = theme_get('cta.btn_text', 'Request Free Quote');
$ctaBtnLink = theme_get('cta.btn_link', 'mailto:info@edispaving.co.uk');
$ctaPhone = theme_get('cta.phone', 'Or Call Us Today');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>
<section class="section cta-section" id="contact">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-icon">
                <i class="fas fa-hard-hat"></i>
            </div>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <i class="fas fa-envelope"></i>
                    <span><?= esc($ctaBtnText) ?></span>
                </a>
            </div>
            <div class="cta-contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:info@edispaving.co.uk" data-ts="contact.email"><?= esc(theme_get('contact.email', 'info@edispaving.co.uk')) ?></a>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span data-ts="contact.address"><?= esc(theme_get('contact.address', '70 Broomstick Hall Road, EN9 1LR, Essex')) ?></span>
                </div>
            </div>
            <div class="cta-trust-badges">
                <span><i class="fas fa-shield-alt"></i> Fully Insured</span>
                <span><i class="fas fa-clock"></i> 23+ Years Experience</span>
                <span><i class="fas fa-star"></i> 5-Star Rated</span>
            </div>
        </div>
    </div>
</section>