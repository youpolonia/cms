<?php
$featuresLabel = theme_get('features.label', 'PLATFORM CAPABILITIES');
$featuresTitle = theme_get('features.title', 'AI‑Driven Endpoint Protection, Redefined');
$featuresDesc = theme_get('features.description', 'Our unified platform combines cutting‑edge machine learning with automated response to stop advanced threats before they impact your business.');
?>
<section class="sc-section sc-section-features" id="features" style="background-color: var(--background);">
    <div class="container">
        <div class="sc-section-header" data-animate>
            <span class="sc-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="sc-section-divider"></div>
            <h2 class="sc-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="sc-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>

        <div class="sc-features-grid">
            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-brain"></i></div>
                <h3 class="sc-feature-title">Behavioral AI Detection</h3>
                <p class="sc-feature-desc">Proprietary machine learning models analyze endpoint behavior in real‑time to identify and block unknown threats without signatures.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> Anomaly‑based threat scoring</li>
                    <li><i class="fas fa-check"></i> Continuous adaptive learning</li>
                    <li><i class="fas fa-check"></i> Low false‑positive rate</li>
                </ul>
            </div>

            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-bug"></i></div>
                <h3 class="sc-feature-title">Zero‑Day Exploit Prevention</h3>
                <p class="sc-feature-desc">Advanced memory protection and exploit mitigation techniques stop previously unseen vulnerabilities from being weaponized.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> ROP/JOP attack prevention</li>
                    <li><i class="fas fa-check"></i> Kernel‑level hardening</li>
                    <li><i class="fas fa-check"></i> Virtual patching</li>
                </ul>
            </div>

            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-robot"></i></div>
                <h3 class="sc-feature-title">Automated Incident Response</h3>
                <p class="sc-feature-desc">Playbook‑driven automation isolates compromised endpoints, contains threats, and initiates remediation—all within seconds.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> Customizable response workflows</li>
                    <li><i class="fas fa-check"></i> Integration with SIEM/SOAR</li>
                    <li><i class="fas fa-check"></i> Forensic evidence collection</li>
                </ul>
            </div>

            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-cloud"></i></div>
                <h3 class="sc-feature-title">Unified Cloud Console</h3>
                <p class="sc-feature-desc">Centralized management dashboard provides visibility across all endpoints, threat intelligence feeds, and compliance reporting.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> Single pane of glass</li>
                    <li><i class="fas fa-check"></i> Real‑time dashboards</li>
                    <li><i class="fas fa-check"></i> Role‑based access control</li>
                </ul>
            </div>

            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-shield-virus"></i></div>
                <h3 class="sc-feature-title">Threat Intelligence Fusion</h3>
                <p class="sc-feature-desc">Correlates internal telemetry with global threat feeds to provide contextualized risk assessment and predictive alerts.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> Multi‑source intelligence</li>
                    <li><i class="fas fa-check"></i> IOC matching & enrichment</li>
                    <li><i class="fas fa-check"></i> Threat actor attribution</li>
                </ul>
            </div>

            <div class="sc-feature-card" data-animate>
                <div class="sc-feature-icon"><i class="fas fa-file-contract"></i></div>
                <h3 class="sc-feature-title">Compliance & Reporting</h3>
                <p class="sc-feature-desc">Pre‑built frameworks and automated reporting for GDPR, HIPAA, PCI‑DSS, NIST, and other regulatory requirements.</p>
                <ul class="sc-feature-list">
                    <li><i class="fas fa-check"></i> Audit‑ready documentation</li>
                    <li><i class="fas fa-check"></i> Continuous compliance monitoring</li>
                    <li><i class="fas fa-check"></i> Executive‑level summaries</li>
                </ul>
            </div>
        </div>
    </div>
</section>
