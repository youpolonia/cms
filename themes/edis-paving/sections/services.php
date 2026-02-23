<?php
$servicesLabel = theme_get('services.label', 'What We Do');
$servicesTitle = theme_get('services.title', 'Complete Outdoor Solutions');
$servicesDesc = theme_get('services.description', 'From elegant driveways to commercial groundworks, we deliver end-to-end paving and landscaping services with unmatched craftsmanship.');
?>
<section class="section services-section" id="services">
    <div class="services-bg"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="services.label">
                <i class="fas fa-hard-hat"></i>
                <?= esc($servicesLabel) ?>
            </span>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        
        <div class="services-grid">
            <div class="service-card service-featured" data-animate>
                <div class="service-icon">
                    <i class="fas fa-th-large"></i>
                </div>
                <div class="service-content">
                    <h3>Block Paving</h3>
                    <p>Beautiful, durable block paving driveways and patios in a wide range of styles, colours and patterns. Built to last with proper foundations and drainage.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Permeable options available</li>
                        <li><i class="fas fa-check"></i> 10-year guarantee</li>
                    </ul>
                </div>
                <span class="service-number">01</span>
            </div>
            
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-road"></i>
                </div>
                <div class="service-content">
                    <h3>Asphalt Driveways</h3>
                    <p>Professional tarmac and asphalt installations for homes and businesses. Cost-effective, hard-wearing and ready to use within days.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Quick installation</li>
                        <li><i class="fas fa-check"></i> Low maintenance</li>
                    </ul>
                </div>
                <span class="service-number">02</span>
            </div>
            
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-border-all"></i>
                </div>
                <div class="service-content">
                    <h3>Patios & Terraces</h3>
                    <p>Stunning outdoor living spaces using natural stone, porcelain or composite materials. Designed for both beauty and practicality.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Natural stone specialists</li>
                        <li><i class="fas fa-check"></i> Bespoke designs</li>
                    </ul>
                </div>
                <span class="service-number">03</span>
            </div>
            
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-digging"></i>
                </div>
                <div class="service-content">
                    <h3>Groundworks</h3>
                    <p>Complete groundworks including excavation, foundations, drainage systems, and site preparation for construction projects.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Drainage solutions</li>
                        <li><i class="fas fa-check"></i> Foundation work</li>
                    </ul>
                </div>
                <span class="service-number">04</span>
            </div>
            
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="service-content">
                    <h3>Landscaping</h3>
                    <p>Transform your garden with professional landscaping services including turfing, planting, fencing, and complete garden redesigns.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Full garden design</li>
                        <li><i class="fas fa-check"></i> Artificial grass</li>
                    </ul>
                </div>
                <span class="service-number">05</span>
            </div>
            
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="service-content">
                    <h3>Commercial Projects</h3>
                    <p>Large-scale paving and groundworks for businesses, car parks, retail premises and industrial sites throughout Essex and beyond.</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Project management</li>
                        <li><i class="fas fa-check"></i> Health & safety compliant</li>
                    </ul>
                </div>
                <span class="service-number">06</span>
            </div>
        </div>
    </div>
</section>