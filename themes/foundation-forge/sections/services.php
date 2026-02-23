<?php
$servicesLabel = theme_get('services.label', 'WHAT WE DO');
$servicesTitle = theme_get('services.title', 'Comprehensive Groundwork & Paving Services');
$servicesDesc = theme_get('services.description', 'From initial site preparation to final surface finishing, we provide end-to-end solutions built on precision engineering and quality materials.');
?>
<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h3 class="service-title">Asphalt Paving</h3>
                <p class="service-desc">Commercial parking lots, driveways, and roadways with durable, smooth-finish asphalt.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> ADA-compliant</li>
                    <li><i class="fas fa-check"></i> Sealcoating</li>
                    <li><i class="fas fa-check"></i> Line Striping</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="service-title">Concrete Foundations</h3>
                <p class="service-desc">Structural slabs, footings, and walls engineered for maximum load-bearing capacity.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Reinforced Design</li>
                    <li><i class="fas fa-check"></i> Moisture Control</li>
                    <li><i class="fas fa-check"></i> Insulated Options</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-truck-pickup"></i>
                </div>
                <h3 class="service-title">Site Excavation</h3>
                <p class="service-desc">Precise grading, trenching, and earthmoving to prepare your site for construction.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> GPS Guided</li>
                    <li><i class="fas fa-check"></i> Erosion Control</li>
                    <li><i class="fas fa-check"></i> Soil Stabilization</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-water"></i>
                </div>
                <h3 class="service-title">Drainage Solutions</h3>
                <p class="service-desc">French drains, culverts, and stormwater systems to protect your property from water damage.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Permeable Pavers</li>
                    <li><i class="fas fa-check"></i> Catch Basins</li>
                    <li><i class="fas fa-check"></i> Retention Ponds</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-parking"></i>
                </div>
                <h3 class="service-title">Parking Lot Construction</h3>
                <p class="service-desc">Turnkey design and installation of commercial parking facilities with optimal traffic flow.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> ADA Compliance</li>
                    <li><i class="fas fa-check"></i> Lighting & Signage</li>
                    <li><i class="fas fa-check"></i> Landscaping Integration</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3 class="service-title">Pavement Repair & Maintenance</h3>
                <p class="service-desc">Crack sealing, pothole repair, and resurfacing to extend pavement lifespan.</p>
                <ul class="service-features">
                    <li><i class="fas fa-check"></i> Infrared Repair</li>
                    <li><i class="fas fa-check"></i> Preventive Programs</li>
                    <li><i class="fas fa-check"></i> 24/7 Emergency</li>
                </ul>
            </div>
        </div>
    </div>
</section>
