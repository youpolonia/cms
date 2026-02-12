<?php
/**
 * Starter Business — Newsletter CTA Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$ctaTitle = theme_get('cta.title', 'Stay Ahead of the Curve');
$ctaDesc  = theme_get('cta.description', 'Get the latest insights, articles, and updates delivered to your inbox.');
?>
<!-- Newsletter CTA -->
<section class="section newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <div class="newsletter-content">
                <h2 class="newsletter-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
                <p class="newsletter-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            </div>
            <div class="newsletter-form">
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    <a href="/articles" class="btn btn-primary btn-lg">Browse Articles <i class="fas fa-arrow-right"></i></a>
                    <a href="/" class="btn btn-outline btn-lg" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2)">Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
