<?php
$featuresLabel = theme_get('features.label', 'Platform Features');
$featuresTitle = theme_get('features.title', 'Everything You Need to Succeed with WordPress');
$featuresDesc = theme_get('features.description', 'Our managed hosting platform includes all the tools and features professional developers and agencies need to build, deploy, and scale WordPress sites.');

$feature1Title = theme_get('features.feature1_title', 'Automated Updates');
$feature1Desc = theme_get('features.feature1_desc', 'WordPress core, plugins, and themes updated automatically with smart rollback protection.');
$feature1Icon = theme_get('features.feature1_icon', 'fa-sync-alt');

$feature2Title = theme_get('features.feature2_title', 'Staging Environments');
$feature2Desc = theme_get('features.feature2_desc', 'Test changes in isolated staging environments before pushing to production with one click.');
$feature2Icon = theme_get('features.feature2_icon', 'fa-flask');

$feature3Title = theme_get('features.feature3_title', 'Daily Backups');
$feature3Desc = theme_get('features.feature3_desc', 'Automated daily backups with 30-day retention. Restore your site to any point in seconds.');
$feature3Icon = theme_get('features.feature3_icon', 'fa-database');

$feature4Title = theme_get('features.feature4_title', 'Expert Support');
$feature4Desc = theme_get('features.feature4_desc', 'WordPress experts available 24/7 via live chat, email, and phone. Average response time under 2 minutes.');
$feature4Icon = theme_get('features.feature4_icon', 'fa-headset');

$feature5Title = theme_get('features.feature5_title', 'Performance Optimization');
$feature5Desc = theme_get('features.feature5_desc', 'Built-in caching, CDN integration, and image optimization deliver blazing-fast page loads.');
$feature5Icon = theme_get('features.feature5_icon', 'fa-rocket');

$feature6Title = theme_get('features.feature6_title', 'Security Hardening');
$feature6Desc = theme_get('features.feature6_desc', 'Enterprise-grade security with firewall, malware scanning, DDoS protection, and free SSL certificates.');
$feature6Icon = theme_get('features.feature6_icon', 'fa-lock');
?>
<section class="vp-section vp-features-section" id="features">
    <div class="container">
        <div class="vp-section-header" data-animate>
            <span class="vp-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="vp-section-divider"></div>
            <h2 class="vp-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="vp-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        
        <div class="vp-features-grid">
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature1Icon) ?>" data-ts="features.feature1_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature1_title"><?= esc($feature1Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature1_desc"><?= esc($feature1Desc) ?></p>
            </div>
            
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature2Icon) ?>" data-ts="features.feature2_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature2_title"><?= esc($feature2Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature2_desc"><?= esc($feature2Desc) ?></p>
            </div>
            
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature3Icon) ?>" data-ts="features.feature3_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature3_title"><?= esc($feature3Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature3_desc"><?= esc($feature3Desc) ?></p>
            </div>
            
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature4Icon) ?>" data-ts="features.feature4_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature4_title"><?= esc($feature4Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature4_desc"><?= esc($feature4Desc) ?></p>
            </div>
            
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature5Icon) ?>" data-ts="features.feature5_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature5_title"><?= esc($feature5Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature5_desc"><?= esc($feature5Desc) ?></p>
            </div>
            
            <div class="vp-feature-card" data-animate>
                <div class="vp-feature-icon">
                    <i class="fas <?= esc($feature6Icon) ?>" data-ts="features.feature6_icon"></i>
                </div>
                <h3 class="vp-feature-title" data-ts="features.feature6_title"><?= esc($feature6Title) ?></h3>
                <p class="vp-feature-desc" data-ts="features.feature6_desc"><?= esc($feature6Desc) ?></p>
            </div>
        </div>
    </div>
</section>
