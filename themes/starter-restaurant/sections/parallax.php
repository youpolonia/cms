<?php
/**
 * Starter Restaurant — Parallax Divider Section
 * Variables inherited from parent scope: $parallaxQuote, $parallaxCitation, $parallaxBg
 */
?>
<!-- Parallax Quote -->
<section class="section parallax-section" <?php if ($parallaxBg): ?>style="background: url(<?= esc($parallaxBg) ?>) center/cover fixed no-repeat"<?php endif; ?> data-ts-bg="parallax.bg_image">
    <div class="parallax-overlay"></div>
    <div class="container" style="position:relative;z-index:2">
        <div class="parallax-content">
            <i class="fas fa-quote-left" style="font-size:2.5rem;opacity:0.3;margin-bottom:1.5rem;color:var(--primary, #d4a574)"></i>
            <blockquote class="parallax-quote" data-ts="parallax.quote"><?= esc($parallaxQuote) ?></blockquote>
            <cite class="parallax-cite" data-ts="parallax.citation">&mdash; <?= esc($parallaxCitation) ?></cite>
        </div>
    </div>
</section>
