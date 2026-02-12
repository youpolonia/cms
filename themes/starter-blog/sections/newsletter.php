<?php
/**
 * Starter Blog — Newsletter Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$ctaTitle = theme_get('cta.title', 'Stay Updated');
$ctaDesc  = theme_get('cta.description', 'Subscribe to get notified about new articles and updates.');
?>
<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <h2 data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;max-width:450px;margin:0 auto">
                <a href="/articles" class="btn btn-primary btn-lg">Browse All Articles <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
