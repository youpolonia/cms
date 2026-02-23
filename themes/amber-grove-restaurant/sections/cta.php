<?php
$ctaTitle = theme_get('cta.title', 'Secure Your Seat at Our Table');
$ctaDesc = theme_get('cta.description', 'Reservations are limited to ensure an intimate experience. Book your evening of culinary discovery.');
$ctaBtnText = theme_get('cta.btn_text', 'Book Now');
$ctaBtnLink = theme_get('cta.btn_link', '#reservations');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="agr-section agr-section--cta" id="cta">
    <div class="agr-cta-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
    <div class="agr-cta-overlay"></div>
    <div class="container">
        <div class="agr-cta-content" data-animate>
            <h2 class="agr-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="agr-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="agr-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="agr-btn agr-btn--primary agr-btn--large" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?> <i class="fas fa-calendar-check"></i>
                </a>
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '+1-555-123-4567'))) ?>" class="agr-btn agr-btn--outline-light">
                    <i class="fas fa-phone"></i> Call to Inquire
                </a>
            </div>
            <div class="agr-cta-features">
                <div class="agr-cta-feature">
                    <i class="fas fa-clock"></i>
                    <span>Wed–Sun, 5PM–11PM</span>
                </div>
                <div class="agr-cta-feature">
                    <i class="fas fa-user-friends"></i>
                    <span>Private dining for 2–20 guests</span>
                </div>
                <div class="agr-cta-feature">
                    <i class="fas fa-wine-bottle"></i>
                    <span>Sommelier pairing available</span>
                </div>
            </div>
        </div>
    </div>
</section>
