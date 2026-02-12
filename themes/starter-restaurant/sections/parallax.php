<?php
/**
 * Starter Restaurant — Parallax Quote Section
 * Enhanced with decorative elements
 * Variables inherited from parent scope
 */
?>
<!-- Parallax Quote -->
<section class="parallax-section" <?php if ($parallaxBg): ?>style="background: url(<?= esc($parallaxBg) ?>) center/cover fixed no-repeat"<?php endif; ?> data-ts-bg="parallax.bg_image">
    <div class="parallax-overlay"></div>
    <div class="container" style="position:relative;z-index:2">
        <div class="parallax-content" data-animate>
            <div class="parallax-icon"><i class="fas fa-quote-left"></i></div>
            <div class="ornament" style="margin-bottom:32px">
                <i class="fas fa-diamond"></i>
            </div>
            <p class="parallax-quote" data-ts="parallax.quote"><?= esc($parallaxQuote) ?></p>
            <div class="ornament" style="margin-bottom:24px">
                <i class="fas fa-diamond"></i>
            </div>
            <cite class="parallax-cite" data-ts="parallax.citation">&mdash; <?= esc($parallaxCitation) ?></cite>
        </div>
    </div>
</section>
