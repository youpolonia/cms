<?php
$ctaTitle = theme_get('cta.title', 'Secure Your Enterprise Today');
$ctaDescription = theme_get('cta.description', 'Schedule a comprehensive security assessment with our experts. Identify vulnerabilities before attackers do.');
$ctaBtnText = theme_get('cta.btn_text', 'Get Your Free Assessment');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaSecondaryText = theme_get('cta.secondary_text', 'Or call us: 1-800-SECURE');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="csh-cta-section" id="cta">
    <?php if ($ctaBgImage): ?>
    <div class="csh-cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="csh-cta-overlay"></div>
    <div class="csh-cta-pattern">
        <svg viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="ctaGrid" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="currentColor" stroke-width="0.5" opacity="0.1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#ctaGrid)"/>
        </svg>
    </div>
    <div class="container">
        <div class="csh-cta-content" data-animate>
            <div class="csh-cta-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 class="csh-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="csh-cta-desc" data-ts="cta.description"><?= esc($ctaDescription) ?></p>
            <div class="csh-cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="csh-btn csh-btn-accent" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <i class="fas fa-lock"></i>
                    <?= esc($ctaBtnText) ?>
                </a>
            </div>
            <p class="csh-cta-secondary" data-ts="cta.secondary_text"><?= esc($ctaSecondaryText) ?></p>
        </div>
    </div>
</section>
