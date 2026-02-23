<?php
$servicesLabel = theme_get('services.label', 'What We Offer');
$servicesTitle = theme_get('services.title', 'Full-Service Digital Marketing Solutions');
$servicesDesc = theme_get('services.description', 'We provide end-to-end marketing strategies tailored for DTC brands looking to scale efficiently and maximize customer lifetime value.');
?>
<section class="sf-section sf-services" id="services">
    <div class="container">
        <div class="sf-section__header" data-animate>
            <span class="sf-section__label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="sf-section__divider"></div>
            <h2 class="sf-section__title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="sf-section__desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="sf-services__grid">
            <div class="sf-service-card" data-animate>
                <div class="sf-service-card__icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="sf-service-card__title">SEO & Content Strategy</h3>
                <p class="sf-service-card__desc">Comprehensive keyword research, on-page optimization, and content creation that ranks and converts.</p>
                <ul class="sf-service-card__features">
                    <li>Technical SEO Audits</li>
                    <li>Content Planning & Production</li>
                    <li>Link Building & Authority</li>
                    <li>Local SEO</li>
                </ul>
                <a href="/services#seo" class="sf-service-card__link">
                    <span>Learn More</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="sf-service-card" data-animate>
                <div class="sf-service-card__icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="sf-service-card__title">PPC & Paid Advertising</h3>
                <p class="sf-service-card__desc">Data-driven Google Ads, Facebook/Instagram, and TikTok campaigns focused on profitable acquisition.</p>
                <ul class="sf-service-card__features">
                    <li>Campaign Strategy & Setup</li>
                    <li>Audience Targeting</li>
                    <li>ROAS Optimization</li>
                    <li>Performance Reporting</li>
                </ul>
                <a href="/services#ppc" class="sf-service-card__link">
                    <span>Learn More</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="sf-service-card" data-animate>
                <div class="sf-service-card__icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="sf-service-card__title">Social Media Management</h3>
                <p class="sf-service-card__desc">Build brand loyalty and drive engagement through strategic content, community management, and influencer partnerships.</p>
                <ul class="sf-service-card__features">
                    <li>Content Calendar & Creation</li>
                    <li>Community Engagement</li>
                    <li>Influencer Collaborations</li>
                    <li>Social Advertising</li>
                </ul>
                <a href="/services#social" class="sf-service-card__link">
                    <span>Learn More</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="sf-service-card" data-animate>
                <div class="sf-service-card__icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h3 class="sf-service-card__title">Conversion Rate Optimization</h3>
                <p class="sf-service-card__desc">Systematically improve your website's performance through A/B testing, user research, and data analysis.</p>
                <ul class="sf-service-card__features">
                    <li>User Experience Audits</li>
                    <li>A/B & Multivariate Testing</li>
                    <li>Heatmap & Session Recording</li>
                    <li>Checkout Optimization</li>
                </ul>
                <a href="/services#conversion" class="sf-service-card__link">
                    <span>Learn More</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
