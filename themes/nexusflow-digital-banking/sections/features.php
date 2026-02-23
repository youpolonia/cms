<?php
$featuresLabel = theme_get('features.label', 'POWERFUL FEATURES');
$featuresTitle = theme_get('features.title', 'Designed for the Modern Freelancer');
$featuresDesc = theme_get('features.description', 'Everything you need to manage your finances efficiently, so you can focus on what you do best.');
?>
<section class="nf-features-section" id="features">
    <div class="container">
        <div class="nf-section-header" data-animate>
            <span class="nf-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="nf-section-divider"></div>
            <h2 class="nf-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="nf-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        <div class="nf-features-grid">
            <!-- Feature 1 -->
            <div class="nf-feature-card" data-animate>
                <div class="nf-feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="nf-feature-title">Instant Invoicing</h3>
                <p class="nf-feature-desc">Create and send professional invoices in seconds. Get paid faster with automated reminders and multiple payment options.</p>
                <a href="/features/invoicing" class="nf-feature-link">
                    <span>Learn more</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <!-- Feature 2 -->
            <div class="nf-feature-card" data-animate>
                <div class="nf-feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h3 class="nf-feature-title">Multi-Currency Accounts</h3>
                <p class="