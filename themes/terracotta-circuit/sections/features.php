<?php
$featuresLabel = theme_get('features.label', 'Platform Capabilities');
$featuresTitle = theme_get('features.title', 'Built for Modern Logistics');
$featuresDesc = theme_get('features.description', 'Our end-to-end platform combines cutting-edge hardware with intelligent software to deliver a seamless last-mile experience.');
?>
<section class="section features-section" id="features">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        
        <div class="features-showcase">
            <div class="feature-main" data-animate>
                <div class="feature-main-visual">
                    <div class="visual-glow"></div>
                    <div class="visual-icon"><i class="fas fa-brain"></i></div>
                </div>
                <div class="feature-main-content">
                    <h3 data-ts="features.main_title"><?= esc(theme_get('features.main_title', 'AI-Powered Intelligence')) ?></h3>
                    <p data-ts="features.main_desc"><?= esc(theme_get('features.main_desc', 'Our proprietary AI engine processes millions of data points to optimize every delivery. From predicting demand surges to calculating the fastest routes, machine learning powers every decision.')) ?></p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check"></i> Predictive demand forecasting</li>
                        <li><i class="fas fa-check"></i> Dynamic route recalculation</li>
                        <li><i class="fas fa-check"></i> Automated fleet dispatching</li>
                    </ul>
                </div>
            </div>
            
            <div class="features-grid">
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4 data-ts="features.f1_title"><?= esc(theme_get('features.f1_title', 'Autonomous Fleet')) ?></h4>
                    <p data-ts="features.f1_desc"><?= esc(theme_get('features.f1_desc', 'Purpose-built delivery robots with advanced sensors and all-weather capability.')) ?></p>
                </div>
                
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4 data-ts="features.f2_title"><?= esc(theme_get('features.f2_title', 'Real-Time Tracking')) ?></h4>
                    <p data-ts="features.f2_desc"><?= esc(theme_get('features.f2_desc', 'Live GPS tracking and delivery notifications for complete transparency.')) ?></p>
                </div>
                
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <h4 data-ts="features.f3_title"><?= esc(theme_get('features.f3_title', 'Easy Integration')) ?></h4>
                    <p data-ts="features.f3_desc"><?= esc(theme_get('features.f3_desc', 'RESTful APIs and pre-built connectors for Shopify, WooCommerce, and more.')) ?></p>
                </div>
                
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 data-ts="features.f4_title"><?= esc(theme_get('features.f4_title', 'Analytics Dashboard')) ?></h4>
                    <p data-ts="features.f4_desc"><?= esc(theme_get('features.f4_desc', 'Comprehensive insights into delivery performance, costs, and customer satisfaction.')) ?></p>
                </div>
                
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4 data-ts="features.f5_title"><?= esc(theme_get('features.f5_title', 'Secure Delivery')) ?></h4>
                    <p data-ts="features.f5_desc"><?= esc(theme_get('features.f5_desc', 'Tamper-proof compartments with PIN or app-based access for recipients.')) ?></p>
                </div>
                
                <div class="feature-card" data-animate>
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 data-ts="features.f6_title"><?= esc(theme_get('features.f6_title', 'Zero Emissions')) ?></h4>
                    <p data-ts="features.f6_desc"><?= esc(theme_get('features.f6_desc', 'All-electric fleet powered by renewable energy for sustainable operations.')) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
