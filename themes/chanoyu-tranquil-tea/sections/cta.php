<?php
$ctaTitle = theme_get('cta.title', 'Begin Your Journey to Stillness');
$ctaDesc = theme_get('cta.description', 'Reserve your private tea ceremony and discover the profound elegance of Japanese hospitality in our bamboo sanctuary.');
$ctaBtnText = theme_get('cta.btn_text', 'Book Your Ceremony');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="ch-cta" id="cta">
    <div class="ch-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>')"></div>
    <div class="ch-cta-overlay"></div>
    
    <div class="container">
        <div class="ch-cta-content">
            <div class="ch-cta-icon" data-animate>
                <i class="fas fa-spa"></i>
            </div>
            <h2 class="ch-cta-title" data-ts="cta.title" data-animate>
                <?= esc($ctaTitle) ?>
            </h2>
            <p class="ch-cta-desc" data-ts="cta.description" data-animate>
                <?= esc($ctaDesc) ?>
            </p>
            <div class="ch-cta-actions" data-animate>
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="ch-btn ch-btn-primary ch-btn-large"
                   data-ts="cta.btn_text"
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
