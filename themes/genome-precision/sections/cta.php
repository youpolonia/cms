<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Health Journey?');
$ctaDesc = theme_get('cta.description', 'Schedule a consultation with our genomic specialists to discover how personalized medicine can optimize your health outcomes.');
$ctaBtnText = theme_get('cta.btn_text', 'Book Your Consultation');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="gp-cta" id="cta">
    <?php if ($ctaBgImage): ?>
        <div class="gp-cta__bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="gp-cta__overlay"></div>
    <div class="container">
        <div class="gp-cta__content" data-animate>
            <h2 class="gp-cta__title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="gp-cta__desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="gp-cta__actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="gp-cta__btn gp-cta__btn--primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-calendar-check"></i>
                </a>
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '+1-555-123-4567'))) ?>" class="gp-cta__btn gp-cta__btn--secondary">
                    <i class="fas fa-phone-alt"></i>
                    Call Now
                </a>
            </div>
            <div class="gp-cta__assurance">
                <div class="gp-cta__assurance-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>HIPAA Compliant & Secure</span>
                </div>
                <div class="gp-cta__assurance-item">
                    <i class="fas fa-user-md"></i>
                    <span>Board-Certified Geneticists</span>
                </div>
                <div class="gp-cta__assurance-item">
                    <i class="fas fa-clock"></i>
                    <span>Results in 2-4 Weeks</span>
                </div>
            </div>
        </div>
    </div>
</section>
