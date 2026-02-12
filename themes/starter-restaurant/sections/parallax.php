<?php
/**
 * Starter Restaurant — Parallax Divider Section
 * Variables inherited from parent scope: $parallaxQuote, $parallaxCitation, $parallaxBg
 */
?>
<!-- Parallax Divider -->
<section class="parallax-divider" data-ts-bg="parallax.bg_image"<?php if ($parallaxBg): ?> style="background: url(<?= esc($parallaxBg) ?>) center/cover fixed no-repeat"<?php endif; ?>>
    <div class="parallax-overlay"></div>
    <div class="parallax-content">
        <blockquote>
            <p data-ts="parallax.quote"><?= esc($parallaxQuote) ?></p>
        </blockquote>
        <cite data-ts="parallax.citation"><?= esc($parallaxCitation) ?></cite>
    </div>
</section>
