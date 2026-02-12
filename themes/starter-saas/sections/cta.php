<?php
/**
 * Starter SaaS — Call to Action Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$ctaTitle = theme_get('cta.title', 'Stay in the Loop');
$ctaDesc  = theme_get('cta.description', 'Subscribe to get the latest updates, articles, and news delivered to your inbox.');
?>
<!-- Newsletter CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2 data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="/articles" class="btn btn-primary btn-lg">Browse Articles <i class="fas fa-arrow-right"></i></a>
                <a href="/" class="btn btn-glass">Back to Top <i class="fas fa-chevron-up"></i></a>
            </div>
        </div>
    </div>
</section>
