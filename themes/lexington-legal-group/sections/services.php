<?php
$servicesLabel = theme_get('services.label', 'Practice Areas');
$servicesTitle = theme_get('services.title', 'Specialized Legal Services');
$servicesDesc = theme_get('services.description', 'We provide comprehensive legal solutions tailored to the complex needs of modern businesses and intellectual property holders.');
?>
<section class="llg-section llg-services" id="services">
    <div class="container">
        <div class="llg-section-header" data-animate>
            <span class="llg-section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="llg-section-divider"></div>
            <h2 class="llg-section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="llg-section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="llg-services-grid">
            <div class="llg-service-card" data-animate>
                <div class="llg-service-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="llg-service-title">Corporate Law</h3>
                <p class="llg-service-desc">Entity formation, governance, compliance, shareholder agreements, and corporate restructuring for businesses of all sizes.</p>
                <ul class="llg-service-list">
                    <li>Mergers & Acquisitions</li>
                    <li>Corporate Governance</li>
                    <li>Contract Negotiation</li>
                    <li>Regulatory Compliance</li>
                </ul>
                <a href="/services#corporate" class="llg-service-link">
                    <span>Explore</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="llg-service-card" data-animate>
                <div class="llg-service-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="llg-service-title">Mergers & Acquisitions</h3>
                <p class="llg-service-desc">End-to-end guidance through due diligence, valuation, negotiation, and integration for successful transactions.</p>
                <ul class="llg-service-list">
                    <li>Due Diligence</li>
                    <li>Transaction Structuring</li>
                    <li>Post-Merger Integration</li>
                    <li>Cross-Border Deals</li>
                </ul>
                <a href="/services#m-a" class="llg-service-link">
                    <span>Explore</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="llg-service-card" data-animate>
                <div class="llg-service-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="llg-service-title">Intellectual Property</h3>
                <p class="llg-service-desc">Protection and enforcement of patents, trademarks, copyrights, and trade secrets for innovators and creators.</p>
                <ul class="llg-service-list">
                    <li>Patent Prosecution</li>
                    <li>Trademark Registration</li>
                    <li>IP Licensing</li>
                    <li>Infringement Litigation</li>
                </ul>
                <a href="/services#ip" class="llg-service-link">
                    <span>Explore</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="llg-services-cta" data-animate>
            <p>Need specialized counsel? Our team can tailor a strategy for your unique situation.</p>
            <a href="#contact" class="llg-btn llg-btn--primary">Discuss Your Case</a>
        </div>
    </div>
</section>
