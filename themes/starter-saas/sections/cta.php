<?php
/**
 * Starter SaaS — Call to Action Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Workflow?');
$ctaDesc  = theme_get('cta.description', 'Join 10,000+ teams already using AppFlow. Start your free 14-day trial — no credit card required.');
?>
<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2 data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="/page/pricing" class="btn btn-primary btn-lg">Start Free Trial <i class="fas fa-arrow-right"></i></a>
                <a href="#features" class="btn btn-glass">See Features <i class="fas fa-chevron-up"></i></a>
            </div>
        </div>
    </div>
</section>
