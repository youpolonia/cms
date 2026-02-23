<?php
$featuresLabel = theme_get('features.label', 'PLATFORM CAPABILITIES');
$featuresTitle = theme_get('features.title', 'Complete Identity Security Platform');
$featuresDesc = theme_get('features.description', 'Everything you need to implement zero-trust identity management across your entire organization.');
?>
<section class="ss-features-section">
    <div class="container">
        <div class="ss-section-header" data-animate>
            <span class="ss-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="ss-section-divider"></div>
            <h2 class="ss-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="ss-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        <div class="ss-features-grid">
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Biometric Authentication</h3>
                <p class="ss-feature-text">Multi-modal biometric verification including fingerprint, facial recognition, iris scanning, and voice authentication. All processing happens on-device for maximum privacy.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> Liveness detection prevents spoofing</li>
                    <li><i class="fas fa-check"></i> FIDO2 and WebAuthn compliant</li>
                    <li><i class="fas fa-check"></i> Works on iOS, Android, Windows, macOS</li>
                </ul>
            </div>
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Universal SSO Integration</h3>
                <p class="ss-feature-text">Single sign-on across all your enterprise applications. Support for SAML 2.0, OAuth 2.0, OpenID Connect, LDAP, and Active Directory with pre-built connectors.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> 200+ pre-built app integrations</li>
                    <li><i class="fas fa-check"></i> Custom SAML connector builder</li>
                    <li><i class="fas fa-check"></i> Session management and timeout policies</li>
                </ul>
            </div>
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Adaptive Risk Engine</h3>
                <p class="ss-feature-text">Machine learning analyzes login behavior, device fingerprints, geolocation, and network context to detect anomalies and enforce step-up authentication when needed.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> Real-time risk scoring (0-100)</li>
                    <li><i class="fas fa-check"></i> Automatic threat response rules</li>
                    <li><i class="fas fa-check"></i> Integration with SIEM platforms</li>
                </ul>
            </div>
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Compliance Automation</h3>
                <p class="ss-feature-text">Automated audit trails, access reports, and compliance dashboards for SOC 2, ISO 27001, HIPAA, PCI-DSS, GDPR, FedRAMP, and NIST frameworks.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> One-click compliance reports</li>
                    <li><i class="fas fa-check"></i> Continuous monitoring and alerting</li>
                    <li><i class="fas fa-check"></i> Tamper-proof audit logs</li>
                </ul>
            </div>
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Privileged Access Management</h3>
                <p class="ss-feature-text">Secure admin and privileged accounts with just-in-time access, session recording, and mandatory multi-factor authentication for sensitive operations.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> Time-limited elevated permissions</li>
                    <li><i class="fas fa-check"></i> Video session recording playback</li>
                    <li><i class="fas fa-check"></i> Approval workflows for critical access</li>
                </ul>
            </div>
            <div class="ss-feature-card" data-animate>
                <div class="ss-feature-icon-wrap">
                    <div class="ss-feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
                <h3 class="ss-feature-title">Mobile Device Management</h3>
                <p class="ss-feature-text">Secure mobile access with device attestation, certificate-based authentication, and remote wipe capabilities for lost or stolen devices.</p>
                <ul class="ss-feature-list">
                    <li><i class="fas fa-check"></i> Device trust verification</li>
                    <li><i class="fas fa-check"></i> Jailbreak/root detection</li>
                    <li><i class="fas fa-check"></i> Geo-fencing and location policies</li>
                </ul>
            </div>
        </div>
        <div class="ss-features-footer" data-animate>
            <div class="ss-integration-logos">
                <span class="ss-integration-label">Integrates with your existing tools:</span>
                <div class="ss-logo-row">
                    <div class="ss-partner-logo">
                        <i class="fab fa-microsoft"></i>
                        <span>Microsoft 365</span>
                    </div>
                    <div class="ss-partner-logo">
                        <i class="fab fa-salesforce"></i>
                        <span>Salesforce</span>
                    </div>
                    <div class="ss-partner-logo">
                        <i class="fab fa-aws"></i>
                        <span>AWS</span>
                    </div>
                    <div class="ss-partner-logo">
                        <i class="fab fa-slack"></i>
                        <span>Slack</span>
                    </div>
                    <div class="ss-partner-logo">
                        <i class="fab fa-google"></i>
                        <span>Google Workspace</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
