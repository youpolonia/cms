<?php
/**
 * Pricing page — commercial CMS product
 */
?>
<section class="jd-section" style="padding-top: 120px;">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-tags"></i> Pricing</span>
        <h2 class="jd-section-title">Choose Your Plan</h2>
        <p class="jd-section-desc">Self-hosted CMS with everything included. Pick the plan that fits your business.</p>
    </div>

    <div class="jd-pricing-grid">
        <div class="jd-pricing-card jd-fade-up">
            <h3>Starter</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>49 <span class="period">/ month</span></div>
            <p class="jd-pricing-desc">For small businesses and personal projects.</p>
            <ul class="jd-pricing-features">
                <li>All 18 plugins</li>
                <li>AI Theme Builder</li>
                <li>JTB Page Builder (79 modules)</li>
                <li>E-Commerce & Shop</li>
                <li>1 site license</li>
                <li>Email support</li>
                <li>Updates for 1 year</li>
            </ul>
            <a href="/demo-about" class="jd-pricing-btn outline">Get Started</a>
        </div>

        <div class="jd-pricing-card featured jd-fade-up">
            <h3>Business</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>99 <span class="period">/ month</span></div>
            <p class="jd-pricing-desc">For growing businesses. Includes SaaS tools & AI credits.</p>
            <ul class="jd-pricing-features">
                <li>Everything in Starter</li>
                <li>6 SaaS AI Tools</li>
                <li>500 AI credits / month</li>
                <li>Dropshipping module</li>
                <li>3 site licenses</li>
                <li>Priority support</li>
                <li>Multi-tenant ready</li>
            </ul>
            <a href="/demo-about" class="jd-pricing-btn primary">Get Started</a>
        </div>

        <div class="jd-pricing-card jd-fade-up">
            <h3>Agency</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>249 <span class="period">/ month</span></div>
            <p class="jd-pricing-desc">For agencies & power users. Unlimited sites + white-label.</p>
            <ul class="jd-pricing-features">
                <li>Everything in Business</li>
                <li>2,000 AI credits / month</li>
                <li>Unlimited site licenses</li>
                <li>White-label SaaS platform</li>
                <li>Custom branding</li>
                <li>API access for all tools</li>
                <li>Dedicated support</li>
                <li>Custom AI model config</li>
            </ul>
            <a href="/demo-about" class="jd-pricing-btn outline">Contact Sales</a>
        </div>
    </div>

    <!-- Credit costs breakdown -->
    <div style="max-width: 800px; margin: 80px auto 0;">
        <h3 class="jd-fade-up" style="text-align: center; font-size: 1.5rem; margin-bottom: 32px;">SaaS AI Credit Costs</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;" class="jd-fade-up">
            <?php
            $costs = [
                ['SEO Writer', [['Generate article', '5'], ['Keyword research', '2'], ['SEO score', 'Free']]],
                ['AI Copywriter', [['Generate copy', '3'], ['Rewrite/optimize', '2'], ['Brand analysis', '1']]],
                ['Image Studio', [['Remove background', '1'], ['ALT text generation', '1'], ['Image enhance', '2'], ['AI generate image', '3']]],
                ['Social Media', [['AI content gen', '2'], ['Hashtag research', '2'], ['Scheduling', 'Free']]],
                ['Email Marketing', [['AI email gen', '3'], ['Campaign send', 'Free'], ['A/B test', 'Free']]],
                ['Analytics', [['AI insights', '5'], ['Tracking', 'Free'], ['Reports', 'Free']]],
            ];
            foreach ($costs as $c): ?>
            <div style="background: var(--jd-surface); border: 1px solid var(--jd-border); border-radius: var(--jd-radius); padding: 20px;">
                <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--jd-purple-light);"><?= $c[0] ?></h4>
                <?php foreach ($c[1] as $item): ?>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <span style="color: var(--jd-text-muted);"><?= $item[0] ?></span>
                    <span style="font-weight: 600; color: <?= $item[1] === 'Free' ? 'var(--jd-green)' : 'var(--jd-text)' ?>;"><?= $item[1] === 'Free' ? 'Included' : $item[1] . ' cr' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
