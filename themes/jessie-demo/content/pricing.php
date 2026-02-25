<?php
/**
 * Pricing page — CMS is free, SaaS tools use credits
 */
?>
<section class="jd-section" style="padding-top: 120px;">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-tags"></i> Pricing</span>
        <h2 class="jd-section-title">Simple, Transparent Pricing</h2>
        <p class="jd-section-desc">The CMS is free and open source. SaaS tools use a credit-based system — pay only for what you use.</p>
    </div>

    <div class="jd-pricing-grid">
        <div class="jd-pricing-card jd-fade-up">
            <h3>Open Source</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>0 <span class="period">/ forever</span></div>
            <p class="jd-pricing-desc">Full CMS with all 18 plugins. Self-hosted, no limits.</p>
            <ul class="jd-pricing-features">
                <li>All 18 plugins included</li>
                <li>AI Theme Builder</li>
                <li>JTB Page Builder (79 modules)</li>
                <li>E-Commerce & Dropshipping</li>
                <li>Multi-tenant architecture</li>
                <li>Unlimited themes</li>
                <li>Full source code access</li>
                <li>Community support</li>
            </ul>
            <a href="https://github.com/youpolonia/cms" class="jd-pricing-btn outline">Download Free</a>
        </div>

        <div class="jd-pricing-card featured jd-fade-up">
            <h3>SaaS Starter</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>29 <span class="period">/ month</span></div>
            <p class="jd-pricing-desc">For businesses using SaaS AI tools. 500 credits/month.</p>
            <ul class="jd-pricing-features">
                <li>Everything in Open Source</li>
                <li>500 AI credits / month</li>
                <li>SEO Writer (5 cr/article)</li>
                <li>AI Copywriter (3 cr/generate)</li>
                <li>Image Studio (1-3 cr/action)</li>
                <li>Social Media Manager</li>
                <li>Email Marketing</li>
                <li>Analytics Dashboard</li>
                <li>Priority support</li>
            </ul>
            <a href="/admin" class="jd-pricing-btn primary">Get Started</a>
        </div>

        <div class="jd-pricing-card jd-fade-up">
            <h3>SaaS Pro</h3>
            <div class="jd-pricing-price"><span class="currency">$</span>99 <span class="period">/ month</span></div>
            <p class="jd-pricing-desc">For agencies & power users. 2000 credits + API access.</p>
            <ul class="jd-pricing-features">
                <li>Everything in Starter</li>
                <li>2,000 AI credits / month</li>
                <li>API access for all tools</li>
                <li>White-label SaaS platform</li>
                <li>Custom branding</li>
                <li>Multi-tenant management</li>
                <li>Advanced analytics</li>
                <li>Dedicated support</li>
                <li>Custom AI model config</li>
            </ul>
            <a href="/admin" class="jd-pricing-btn outline">Contact Sales</a>
        </div>
    </div>

    <!-- Credit costs breakdown -->
    <div style="max-width: 800px; margin: 80px auto 0;">
        <h3 class="jd-fade-up" style="text-align: center; font-size: 1.5rem; margin-bottom: 32px;">Credit Costs per Action</h3>
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
                    <span style="font-weight: 600; color: <?= $item[1] === 'Free' ? 'var(--jd-green)' : 'var(--jd-text)' ?>;"><?= $item[1] === 'Free' ? 'Free' : $item[1] . ' cr' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
