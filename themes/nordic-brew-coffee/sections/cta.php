<?php
$ctaTitle = theme_get('cta.title', 'Start Your Morning Right');
$ctaDesc = theme_get('cta.description', 'Order ahead and skip the line. Your perfectly brewed cup will be waiting.');
$ctaBtnText = theme_get('cta.btn_text', 'Order for Pickup');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBtn2Text = theme_get('cta.btn2_text', 'View Hours');
$ctaBtn2Link = theme_get('cta.btn2_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="section cta-section" id="cta">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content" data-animate>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc($ctaBtnText) ?></a>
                <a href="<?= esc($ctaBtn2Link) ?>" class="btn btn-outline btn-lg" data-ts="cta.btn2_text" data-ts-href="cta.btn2_link"><?= esc($ctaBtn2Text) ?></a>
            </div>
        </div>
    </div>
</section>
