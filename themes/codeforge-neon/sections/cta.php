<?php
$ctaTitle = theme_get('cta.title', 'Ready to Build Something Amazing?');
$ctaDesc = theme_get('cta.description', "Stop dreaming about your app and start building it. Your first project is free—no credit card, no catches.");
$ctaBtnText = theme_get('cta.btn_text', 'Start Building Now');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="section cta-section" id="cta">
    <?php if ($ctaBgImage): ?>
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="cta-overlay"></div>
    <div class="cta-grid-pattern"></div>
    <div class="cta-glow left"></div>
    <div class="cta-glow right"></div>
    
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-badge">
                <i class="fas fa-gift"></i>
                <span>Free to start • No credit card required</span>
            </div>
            
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            
            <div class="cta-form">
                <div class="input-group">
                    <input type="email" placeholder="Enter your email" class="cta-input">
                    <a href="<?= esc($ctaBtnLink) ?>" 
                       class="btn btn-primary btn-lg"
                       data-ts="cta.btn_text"
                       data-ts-href="cta.btn_link">
                        <?= esc($ctaBtnText) ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="cta-features">
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Unlimited projects</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Export your code</span>
                </div>
                <div class="cta-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Community support</span>
                </div>
            </div>
        </div>
    </div>
</section>
