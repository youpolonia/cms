<?php
$ctaTitle = theme_get('cta.title', 'Ready to Start Your Project?');
$ctaDesc = theme_get('cta.description', 'Contact us for a free, no‑obligation site assessment and detailed quote. Our team will review your specifications and provide a comprehensive plan within 48 hours.');
$ctaBtnText = theme_get('cta.btn_text', 'Schedule Your Assessment');
$ctaBtnLink = theme_get('cta.btn_link', '/contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="section cta-section" id="cta">
    <div class="cta-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content" data-animate>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?> <i class="fas fa-calendar-check"></i>
                </a>
                <a href="tel:<?= esc(theme_get('footer.phone', '+15557890123')) ?>" class="btn btn-outline btn-lg">
                    <i class="fas fa-phone"></i> Call Now: <?= esc(theme_get('footer.phone', '+1 (555) 789-0123')) ?>
                </a>
            </div>
            <div class="cta-features">
                <div class="feature">
                    <i class="fas fa-clock"></i>
                    <span>48‑Hour Quote Turnaround</span>
                </div>
                <div class="feature">
                    <i class="fas fa-file-contract"></i>
                    <span>Fixed‑Price Contracts</span>
                </div>
                <div class="feature">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Flexible Scheduling</span>
                </div>
            </div>
        </div>
    </div>
</section>
