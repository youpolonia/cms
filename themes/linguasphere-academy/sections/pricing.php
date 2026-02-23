<?php
$pricingLabel = theme_get('pricing.label', 'Investment');
$pricingTitle = theme_get('pricing.title', 'Flexible Learning Plans');
$pricingDesc = theme_get('pricing.description', 'Choose a plan that fits your goals and schedule. All plans include live conversation practice, cultural modules, and progress tracking.');
?>
<section class="lsa-section lsa-pricing" id="pricing">
    <div class="container">
        <div class="lsa-section-header" data-animate>
            <span class="lsa-section-label" data-ts="pricing.label"><?= esc($pricingLabel) ?></span>
            <div class="lsa-section-divider"></div>
            <h2 class="lsa-section-title" data-ts="pricing.title"><?= esc($pricingTitle) ?></h2>
            <p class="lsa-section-desc" data-ts="pricing.description"><?= esc($pricingDesc) ?></p>
        </div>

        <div class="lsa-pricing-grid">
            <div class="lsa-pricing-card" data-animate>
                <div class="lsa-pricing-header">
                    <h3 class="lsa-pricing-title">Conversation Starter</h3>
                    <div class="lsa-pricing-price">
                        <span class="lsa-price-currency">$</span>
                        <span class="lsa-price-amount">79</span>
                        <span class="lsa-price-period">/month</span>
                    </div>
                    <p class="lsa-pricing-subtitle">Perfect for beginners</p>
                </div>
                <ul class="lsa-pricing-features">
                    <li><i class="fas fa-check lsa-feature-check"></i> 8 live group sessions monthly</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Basic cultural modules</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Vocabulary builder access</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Weekly progress report</li>
                    <li><i class="fas fa-times lsa-feature-missing"></i> One-on-one tutoring</li>
                </ul>
                <a href="#enroll" class="lsa-btn lsa-btn-outline lsa-pricing-btn">Start Learning</a>
                <div class="lsa-pricing-badge">Most Popular</div>
            </div>

            <div class="lsa-pricing-card lsa-pricing-featured" data-animate>
                <div class="lsa-pricing-header">
                    <h3 class="lsa-pricing-title">Fluency Builder</h3>
                    <div class="lsa-pricing-price">
                        <span class="lsa-price-currency">$</span>
                        <span class="lsa-price-amount">149</span>
                        <span class="lsa-price-period">/month</span>
                    </div>
                    <p class="lsa-pricing-subtitle">Our recommended plan</p>
                </div>
                <ul class="lsa-pricing-features">
                    <li><i class="fas fa-check lsa-feature-check"></i> 12 live group sessions monthly</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> 2 private tutoring sessions</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Advanced cultural immersion</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Pronunciation analysis</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Daily practice exercises</li>
                </ul>
                <a href="#enroll" class="lsa-btn lsa-btn-primary lsa-pricing-btn">Get Fluent Faster</a>
                <div class="lsa-pricing-badge">Best Value</div>
            </div>

            <div class="lsa-pricing-card" data-animate>
                <div class="lsa-pricing-header">
                    <h3 class="lsa-pricing-title">Global Immersion</h3>
                    <div class="lsa-pricing-price">
                        <span class="lsa-price-currency">$</span>
                        <span class="lsa-price-amount">299</span>
                        <span class="lsa-price-period">/month</span>
                    </div>
                    <p class="lsa-pricing-subtitle">For serious learners</p>
                </div>
                <ul class="lsa-pricing-features">
                    <li><i class="fas fa-check lsa-feature-check"></i> Unlimited live sessions</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> 8 private tutoring sessions</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Cultural exchange partner</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Business language focus</li>
                    <li><i class="fas fa-check lsa-feature-check"></i> Certification preparation</li>
                </ul>
                <a href="#enroll" class="lsa-btn lsa-btn-outline lsa-pricing-btn">Go Global</a>
            </div>
        </div>

        <div class="lsa-pricing-note" data-animate>
            <p><i class="fas fa-info-circle"></i> All plans include a 14-day money-back guarantee. Need a custom plan? <a href="/contact">Contact our advisors</a>.</p>
        </div>
    </div>
</section>
