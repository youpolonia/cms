<?php
$ctaLabel = theme_get('cta.label', 'SECURE YOUR NETWORK');
$ctaTitle = theme_get('cta.title', 'Ready to Eliminate Zero‑Day Threats?');
$ctaDesc = theme_get('cta.description', 'Schedule a personalized demo and see how our AI‑powered platform protects your endpoints in real‑time.');
$ctaBtnText = theme_get('cta.btn_text', 'Request a Demo');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
?>
<section class="sc-section sc-section-cta" id="cta" style="background-color: var(--surface);">
    <div class="sc-cta-bg" style="background-image: url('https://images.pexels.com/photos/5380792/pexels-photo-5380792.jpeg?auto=compress&cs=tinysrgb&h=650&w=940');" data-ts-bg="cta.bg_image"></div>
    <div class="sc-cta-overlay"></div>
    <div class="container">
        <div class="sc-cta-content" data-animate>
            <span class="sc-cta-label" data-ts="cta.label"><?= esc($ctaLabel) ?></span>
            <h2 class="sc-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="sc-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="sc-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="sc-btn sc-btn-primary sc-btn-large" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?> <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/contact" class="sc-btn sc-btn-outline">Talk to an Expert</a>
            </div>
            <div class="sc-cta-assurance">
                <p><i class="fas fa-check-circle"></i> No‑commitment 30‑day pilot</p>
                <p><i class="fas fa-check-circle"></i> SOC2 Type II certified</p>
                <p><i class="fas fa-check-circle"></i> 24/7 dedicated support</p>
            </div>
        </div>
    </div>
</section>
