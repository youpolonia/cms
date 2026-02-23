<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Team Collaboration?');
$ctaDesc = theme_get('cta.description', 'Join 25,000+ engineering teams who ship faster with channels, threads, video calls, and deep dev tool integrations.');
$ctaBtnText = theme_get('cta.btn_text', 'Start Your Free Trial');
$ctaBtnLink = theme_get('cta.btn_link', '#signup');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/cta-bg.jpg');
?>
<section class="sn-section sn-section-cta" id="cta">
    <div class="sn-cta-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
    <div class="sn-cta-overlay"></div>
    <div class="container">
        <div class="sn-cta-content" data-animate>
            <h2 class="sn-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="sn-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="sn-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="sn-btn sn-btn-primary sn-btn-cta" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?> <i class="fas fa-rocket"></i>
                </a>
                <a href="#demo" class="sn-btn sn-btn-outline">
                    <i class="fas fa-calendar-alt"></i> Book a Demo
                </a>
            </div>
            <div class="sn-cta-features">
                <span><i class="fas fa-check-circle"></i> No credit card required</span>
                <span><i class="fas fa-check-circle"></i> Free 14‑day trial</span>
                <span><i class="fas fa-check-circle"></i> Full platform access</span>
            </div>
        </div>
    </div>
</section>
