<?php
$featuresLabel = theme_get('features.label', 'Our Solutions');
$featuresTitle = theme_get('features.title', 'Comprehensive Security Suite');
$featuresDesc = theme_get('features.description', 'Multi-layered defense strategies powered by cutting-edge technology and expert human analysis.');

$feature1Title = theme_get('features.feature1_title', 'Next-Gen Firewall');
$feature1Desc = theme_get('features.feature1_desc', 'AI-powered threat detection with deep packet inspection, application awareness, and automated response capabilities.');

$feature2Title = theme_get('features.feature2_title', 'Intrusion Detection');
$feature2Desc = theme_get('features.feature2_desc', 'Real-time network monitoring with behavioral analysis to identify and alert on suspicious activities instantly.');

$feature3Title = theme_get('features.feature3_title', 'DDoS Mitigation');
$feature3Desc = theme_get('features.feature3_desc', 'Enterprise-grade protection against volumetric, protocol, and application layer attacks with 99.99% uptime guarantee.');

$feature4Title = theme_get('features.feature4_title', '24/7 SOC Monitoring');
$feature4Desc = theme_get('features.feature4_desc', 'Round-the-clock security operations center staffed by certified analysts providing continuous threat surveillance.');

$feature5Title = theme_get('features.feature5_title', 'Incident Response');
$feature5Desc = theme_get('features.feature5_desc', 'Rapid containment, forensic analysis, and recovery services to minimize damage and restore operations quickly.');

$feature6Title = theme_get('features.feature6_title', 'Compliance Management');
$feature6Desc = theme_get('features.feature6_desc', 'Automated compliance monitoring and reporting for HIPAA, PCI-DSS, SOC 2, GDPR, and industry frameworks.');
?>
<section class="csh-features-section" id="features">
    <div class="container">
        <div class="csh-section-header" data-animate>
            <span class="csh-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="csh-section-divider"></div>
            <h2 class="csh-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="csh-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        <div class="csh-features-grid">
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-fire-alt"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature1_title"><?= esc($feature1Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature1_desc"><?= esc($feature1Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-radar"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature2_title"><?= esc($feature2Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature2_desc"><?= esc($feature2Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-cloud-showers-heavy"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature3_title"><?= esc($feature3Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature3_desc"><?= esc($feature3Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature4_title"><?= esc($feature4Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature4_desc"><?= esc($feature4Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-first-aid"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature5_title"><?= esc($feature5Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature5_desc"><?= esc($feature5Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="csh-feature-card" data-animate>
                <div class="csh-feature-icon-wrap">
                    <div class="csh-feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <h3 class="csh-feature-title" data-ts="features.feature6_title"><?= esc($feature6Title) ?></h3>
                <p class="csh-feature-desc" data-ts="features.feature6_desc"><?= esc($feature6Desc) ?></p>
                <a href="#" class="csh-feature-link">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
