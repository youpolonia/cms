<?php
$ctaTitle = theme_get('cta.title', 'Ready to Secure Your Legal Strategy?');
$ctaDesc = theme_get('cta.description', 'Contact us for a confidential consultation. Our team will assess your situation and outline a clear path forward.');
$ctaBtnText = theme_get('cta.btn_text', 'Contact Our Office');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="llg-section llg-cta-section" id="cta">
    <?php if ($ctaBgImage): ?>
    <div class="llg-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="llg-cta-overlay"></div>
    <div class="container">
        <div class="llg-cta-content" data-animate>
            <h2 class="llg-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="llg-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="llg-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="llg-btn llg-btn--primary llg-btn--large" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                </a>
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '+1-555-123-4567'))) ?>" class="llg-btn llg-btn--outline llg-btn--large">
                    <i class="fas fa-phone"></i>
                    <span>Call Now</span>
                </a>
            </div>
            <div class="llg-cta-info">
                <div class="llg-cta-info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Response Time</h4>
                        <p>We respond to inquiries within 24 hours.</p>
                    </div>
                </div>
                <div class="llg-cta-info-item">
                    <i class="fas fa-lock"></i>
                    <div>
                        <h4>Confidentiality Guaranteed</h4>
                        <p>All consultations are protected by attorney-client privilege.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
