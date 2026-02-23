<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Freelance Finances?');
$ctaDesc = theme_get('cta.description', 'Join 85,000+ freelancers who trust NexusFlow with their banking. Get started in minutes.');
$ctaBtnText = theme_get('cta.btn_text', 'Open Your Free Account');
$ctaBtnLink = theme_get('cta.btn_link', '#signup');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="nf-cta-section" id="cta">
    <?php if ($ctaBgImage): ?>
    <div class="nf-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="nf-cta-overlay"></div>
    <div class="container">
        <div class="nf-cta-content" data-animate>
            <h2 class="nf-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="nf-cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="nf-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="nf-cta-btn nf-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/demo" class="nf-cta-btn nf-btn-outline">
                    <i class="fas fa-play-circle"></i>
                    <span>Watch Demo</span>
                </a>
            </div>
            <div class="nf-cta-features">
                <div class="nf-cta-feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Bank-level security</span>
                </div>
                <div class="nf-cta-feature">
                    <i class="fas fa-clock"></i>
                    <span>No setup fees</span>
                </div>
                <div class="nf-cta-feature">
                    <i class="fas fa-headset"></i>
                    <span>24/7 support</span>
                </div>
            </div>
        </div>
    </div>
</section>
