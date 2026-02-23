<?php
$pricingLabel = theme_get('pricing.label', 'SIMPLE, TRANSPARENT PRICING');
$pricingTitle = theme_get('pricing.title', 'Plans for Every Stage of Your Journey');
$pricingDesc = theme_get('pricing.description', 'Start free, upgrade as you grow. No hidden fees, no long-term contracts.');
?>
<section class="nf-pricing-section" id="pricing">
    <div class="container">
        <div class="nf-section-header" data-animate>
            <span class="nf-section-label" data-ts="pricing.label"><?= esc($pricingLabel) ?></span>
            <div class="nf-section-divider"></div>
            <h2 class="nf-section-title" data-ts="pricing.title"><?= esc($pricingTitle) ?></h2>
            <p class="nf-section-desc" data-ts="pricing.description"><?= esc($pricingDesc) ?></p>
        </div>
        <div class="nf-pricing-grid">
            <!-- Starter Plan -->
            <div class="nf-pricing-card" data-animate>
                <div class="nf-pricing-header">
                    <h3 class="nf-plan-name">Starter</h3>
                    <div class="nf-plan-price">
                        <span class="nf-price-amount">$0</span>
                        <span class="nf-price-period">/month</span>
                    </div>
                    <p class="nf-plan-desc">Perfect for side hustlers</p>
                </div>
                <div class="nf-pricing-features">
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Up to 5 invoices/month</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>2 currency wallets</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Basic expense tracking</span>
                    </div>
                    <div class="nf-feature-item nf-feature-disabled">
                        <i class="fas fa-times"></i>
                        <span>AI categorization</span>
                    </div>
                    <div class="nf-feature-item nf-feature-disabled">
                        <i class="fas fa-times"></i>
                        <span>Tax forecasting</span>
                    </div>
                </div>
                <a href="#signup" class="nf-pricing-btn nf-btn-outline">Get Started Free</a>
            </div>

            <!-- Pro Plan (Featured) -->
            <div class="nf-pricing-card nf-pricing-featured" data-animate>
                <div class="nf-pricing-badge">Most Popular</div>
                <div class="nf-pricing-header">
                    <h3 class="nf-plan-name">Professional</h3>
                    <div class="nf-plan-price">
                        <span class="nf-price-amount">$29</span>
                        <span class="nf-price-period">/month</span>
                    </div>
                    <p class="nf-plan-desc">For full-time freelancers</p>
                </div>
                <div class="nf-pricing-features">
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Unlimited invoices</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>10 currency wallets</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Advanced expense tracking</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>AI categorization</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Tax forecasting</span>
                    </div>
                </div>
                <a href="#signup" class="nf-pricing-btn nf-btn-primary">Start 14-Day Trial</a>
            </div>

            <!-- Business Plan -->
            <div class="nf-pricing-card" data-animate>
                <div class="nf-pricing-header">
                    <h3 class="nf-plan-name">Business</h3>
                    <div class="nf-plan-price">
                        <span class="nf-price-amount">$79</span>
                        <span class="nf-price-period">/month</span>
                    </div>
                    <p class="nf-plan-desc">For agencies & teams</p>
                </div>
                <div class="nf-pricing-features">
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Everything in Pro</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Unlimited team members</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Client portal</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Custom reporting</span>
                    </div>
                    <div class="nf-feature-item">
                        <i class="fas fa-check"></i>
                        <span>Priority support</span>
                    </div>
                </div>
                <a href="#contact" class="nf-pricing-btn nf-btn-outline">Contact Sales</a>
            </div>
        </div>
        <div class="nf-pricing-note" data-animate>
            <p><i class="fas fa-info-circle"></i> All plans include bank-level security, 24/7 customer support, and no transaction fees on local transfers. Cancel anytime.</p>
        </div>
    </div>
</section>
