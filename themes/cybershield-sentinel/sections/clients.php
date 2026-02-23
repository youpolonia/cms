<?php
$clientsLabel = theme_get('clients.label', 'Trusted By');
$clientsTitle = theme_get('clients.title', 'Industry Leaders Choose Us');
$clientsDesc = theme_get('clients.description', 'From Fortune 500 enterprises to emerging tech companies, we protect organizations across every sector.');
?>
<section class="csh-clients-section" id="clients">
    <div class="container">
        <div class="csh-section-header" data-animate>
            <span class="csh-section-label" data-ts="clients.label"><?= esc($clientsLabel) ?></span>
            <div class="csh-section-divider"></div>
            <h2 class="csh-section-title" data-ts="clients.title"><?= esc($clientsTitle) ?></h2>
            <p class="csh-section-desc" data-ts="clients.description"><?= esc($clientsDesc) ?></p>
        </div>
        <div class="csh-clients-showcase" data-animate>
            <div class="csh-clients-track">
                <div class="csh-client-logo">
                    <i class="fas fa-building"></i>
                    <span>Enterprise Corp</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-university"></i>
                    <span>Global Bank</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-hospital"></i>
                    <span>HealthTech</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-shopping-cart"></i>
                    <span>RetailMax</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-plane"></i>
                    <span>AeroSys</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-microchip"></i>
                    <span>TechNova</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-broadcast-tower"></i>
                    <span>TeleComm</span>
                </div>
                <div class="csh-client-logo">
                    <i class="fas fa-oil-can"></i>
                    <span>EnergyPro</span>
                </div>
            </div>
        </div>
        <div class="csh-clients-stats" data-animate>
            <div class="csh-client-stat">
                <span class="csh-stat-number">35+</span>
                <span class="csh-stat-text">Industries Served</span>
            </div>
            <div class="csh-client-stat">
                <span class="csh-stat-number">50+</span>
                <span class="csh-stat-text">Countries Protected</span>
            </div>
            <div class="csh-client-stat">
                <span class="csh-stat-number">$2B+</span>
                <span class="csh-stat-text">Assets Secured</span>
            </div>
        </div>
    </div>
</section>
