<?php
$ctaTitle = theme_get('cta.title', 'Reserve Your Seat at the Counter');
$ctaDesc = theme_get('cta.description', 'With only eight seats per evening, our omakase experience books weeks in advance. Secure your place for an evening of culinary artistry and intimate elegance.');
$ctaBtnText = theme_get('cta.btn_text', 'Make a Reservation');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>
<section class="kno-section kno-cta" id="cta">
    <div class="kno-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <div class="kno-cta-overlay"></div>
    
    <div class="container">
        <div class="kno-cta-content" data-animate>
            <h2 class="kno-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="kno-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            
            <div class="kno-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="kno-btn kno-btn-primary kno-btn-large" 
                   data-ts="cta.btn_text" 
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="kno-cta-details">
                <div class="kno-cta-detail-item">
                    <i class="fas fa-clock"></i>
                    <span>Tuesday–Saturday, 7:00 PM & 9:30 PM</span>
                </div>
                <div class="kno-cta-detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>27 Berkeley Square, Mayfair, London W1J 6EL</span>
                </div>
                <div class="kno-cta-detail-item">
                    <i class="fas fa-phone"></i>
                    <span>+44 20 7499 8888</span>
                </div>
            </div>
        </div>
    </div>
</section>
