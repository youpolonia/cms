<?php
$ctaTitle = theme_get('cta.title', 'Roof Emergency? Don\'t Wait.');
$ctaDesc = theme_get('cta.description', 'Every minute counts when water is getting into your home. Our emergency team is standing by 24/7 to protect your property.');
$ctaBtnText = theme_get('cta.btn_text', 'Call For Immediate Help');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_btn_text', 'Schedule Assessment');
$ctaSecondaryLink = theme_get('cta.secondary_btn_link', '/contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>
<section class="ssp-cta" id="cta">
    <div class="ssp-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <div class="ssp-cta-overlay"></div>
    
    <div class="ssp-cta-container">
        <div class="ssp-cta-content" data-animate>
            <div class="ssp-cta-badge">
                <i class="fas fa-bolt"></i>
                <span>24/7 Emergency Service</span>
            </div>
            
            <h2 class="ssp-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="ssp-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            
            <div class="ssp-cta-actions">
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '(555) 911-ROOF'))) ?>" class="ssp-btn ssp-btn-primary ssp-btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <i class="fas fa-phone-alt"></i>
                    <?= esc($ctaBtnText) ?>
                </a>
                <a href="<?= esc($ctaSecondaryLink) ?>" class="ssp-btn ssp-btn-white ssp-btn-lg" data-ts="cta.secondary_btn_text" data-ts-href="cta.secondary_btn_link">
                    <?= esc($ctaSecondaryText) ?>
                </a>
            </div>
            
            <div class="ssp-cta-features">
                <div class="ssp-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>30-Min Response</span>
                </div>
                <div class="ssp-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Licensed & Insured</span>
                </div>
                <div class="ssp-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Insurance Approved</span>
                </div>
            </div>
        </div>
    </div>
</section>
