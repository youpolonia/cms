<?php
$clientsLabel = theme_get('clients.label', 'INDUSTRY LEADERS');
$clientsTitle = theme_get('clients.title', 'Trusted by Forward‑Thinking Enterprises');
$clientsDesc = theme_get('clients.description', 'Organizations across sectors rely on Sentinel Core to protect their critical infrastructure and digital assets.');
?>
<section class="sc-section sc-section-clients" id="clients" style="background-color: var(--surface);">
    <div class="container">
        <div class="sc-section-header" data-animate>
            <span class="sc-section-label" data-ts="clients.label"><?= esc($clientsLabel) ?></span>
            <div class="sc-section-divider"></div>
            <h2 class="sc-section-title" data-ts="clients.title"><?= esc($clientsTitle) ?></h2>
            <p class="sc-section-desc" data-ts="clients.description"><?= esc($clientsDesc) ?></p>
        </div>

        <div class="sc-clients-logos">
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">FinServe</span>
                    <span class="sc-logo-sector">Financial Services</span>
                </div>
            </div>
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">MediCore</span>
                    <span class="sc-logo-sector">Healthcare</span>
                </div>
            </div>
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">TechNova</span>
                    <span class="sc-logo-sector">Technology</span>
                </div>
            </div>
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">Global Retail</span>
                    <span class="sc-logo-sector">E‑Commerce</span>
                </div>
            </div>
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">EnergySecure</span>
                    <span class="sc-logo-sector">Energy & Utilities</span>
                </div>
            </div>
            <div class="sc-client-logo" data-animate>
                <div class="sc-logo-inner">
                    <span class="sc-logo-text">GovDefense</span>
                    <span class="sc-logo-sector">Public Sector</span>
                </div>
            </div>
        </div>

        <div class="sc-clients-cta">
            <p>Become part of our security‑first community. <a href="/contact" class="sc-link">Contact our enterprise team <i class="fas fa-arrow-right"></i></a></p>
        </div>
    </div>
</section>
