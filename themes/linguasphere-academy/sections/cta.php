<?php
$ctaTitle = theme_get('cta.title', 'Start Speaking Confidently Today');
$ctaDesc = theme_get('cta.description', 'Join thousands who have transformed their language skills through immersive conversation. Your first cultural exchange session is waiting.');
$ctaBtnText = theme_get('cta.btn_text', 'Claim Your Free Trial Session');
$ctaBtnLink = theme_get('cta.btn_link', '/trial');
$ctaBgImage = 'https://images.pexels.com/photos/4261787/pexels-photo-4261787.jpeg?auto=compress&cs=tinysrgb&h=650&w=940';
?>
<section class="lsa-section lsa-cta" id="cta">
    <div class="lsa-cta-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
    <div class="lsa-cta-overlay"></div>
    <div class="container">
        <div class="lsa-cta-content" data-animate>
            <h2 class="lsa-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="lsa-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="lsa-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="lsa-btn lsa-btn-primary lsa-btn-cta" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-graduation-cap"></i>
                </a>
                <a href="/pricing" class="lsa-btn lsa-btn-outline-light">
                    Compare Plans
                </a>
            </div>
            <div class="lsa-cta-features">
                <div class="lsa-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>No credit card required</span>
                </div>
                <div class="lsa-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>7-day full access trial</span>
                </div>
                <div class="lsa-cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Cancel anytime</span>
                </div>
            </div>
        </div>
    </div>
</section>
