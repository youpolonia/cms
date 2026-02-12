<?php
/**
 * Starter Portfolio — CTA Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$ctaTitle = theme_get('cta.title', "Let's Connect");
$ctaDesc  = theme_get('cta.description', "Interested in working together? Let's make something great.");
?>
<!-- CTA -->
<section class="cta-section">
    <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
    <p class="cta-description" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
    <div class="cta-button-group">
        <a href="/articles" class="btn btn-primary">Read Our Blog <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
