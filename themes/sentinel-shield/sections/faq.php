<?php
$faqLabel = theme_get('faq.label', 'FREQUENTLY ASKED QUESTIONS');
$faqTitle = theme_get('faq.title', 'Everything You Need to Know About Zero-Trust Security');
$faqDesc = theme_get('faq.description', 'Get answers to common questions about passwordless authentication, compliance, and deployment.');
?>
<section class="ss-faq-section">
    <div class="container">
        <div class="ss-section-header" data-animate>
            <span class="ss-section-label" data-ts="faq.label"><?= esc($faqLabel) ?></span>
            <div class="ss-section-divider"></div>
            <h2 class="ss-section-title" data-ts="faq.title"><?= esc($faqTitle) ?></h2>
            <p class="ss-section-desc" data-ts="faq.description"><?= esc($faqDesc) ?></p>
        </div>
        <div class="ss-faq-layout">
            <div class="ss-faq-list">
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-shield-alt"></i>
                        <h3>What is zero-trust identity management?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>Zero-trust is a security framework that eliminates implicit trust. Every user, device, and application must be continuously verified before accessing resources—regardless of network location. Our platform enforces this through multi-factor authentication, biometric verification, device fingerprinting, and behavior analysis.</p>
                    </div>
                </div>
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-fingerprint"></i>
                        <h3>How does passwordless authentication work?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>Users authenticate via biometric factors (fingerprint, facial recognition), hardware tokens (YubiKey), or cryptographic keys stored on their devices. This eliminates password vulnerabilities like phishing, credential stuffing, and brute force attacks. Authentication happens in seconds with enterprise-grade security.</p>
                    </div>
                </div>
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>Which compliance frameworks do you support?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>Sentinel Shield provides automated compliance reporting for SOC 2, ISO 27001, GDPR, HIPAA, PCI-DSS, FedRAMP, and NIST 800-53. Our platform generates audit-ready logs, access reports, and security posture dashboards in real-time. All data is encrypted at rest and in transit.</p>
                    </div>
                </div>
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-plug"></i>
                        <h3>How long does integration take?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>Most organizations complete SSO integration in 2-5 business days. Our platform supports SAML 2.0, OAuth 2.0, OpenID Connect, and LDAP. We provide dedicated integration engineers, comprehensive documentation, and pre-built connectors for popular enterprise applications like Office 365, Salesforce, Workday, and ServiceNow.</p>
                    </div>
                </div>
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-mobile-alt"></i>
                        <h3>What biometric methods are supported?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>We support fingerprint scanning, facial recognition (including liveness detection), iris scanning, and voice recognition. All biometric data is processed locally on the user's device and never transmitted to our servers. We use cryptographic hashes for verification, ensuring maximum privacy and security.</p>
                    </div>
                </div>
                <div class="ss-faq-item" data-animate>
                    <div class="ss-faq-question">
                        <i class="fas fa-chart-line"></i>
                        <h3>What reporting capabilities are included?</h3>
                        <i class="fas fa-chevron-down ss-faq-toggle"></i>
                    </div>
                    <div class="ss-faq-answer">
                        <p>Our dashboard provides real-time visibility into authentication events, failed login attempts, device inventory, access patterns, risk scores, and compliance status. Export audit logs in CSV, JSON, or SIEM-compatible formats. Set up automated alerts for suspicious activity, policy violations, and security incidents.</p>
                    </div>
                </div>
            </div>
            <div class="ss-faq-sidebar" data-animate>
                <div class="ss-faq-cta-box">
                    <div class="ss-faq-cta-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h4>Still Have Questions?</h4>
                    <p>Our security architects are available to discuss your specific requirements and design a custom deployment plan.</p>
                    <a href="#contact" class="ss-btn ss-btn-primary">
                        <span>Schedule Consultation</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="ss-faq-resources">
                    <h4>Helpful Resources</h4>
                    <a href="/services" class="ss-resource-link">
                        <i class="fas fa-book"></i>
                        <span>Technical Documentation</span>
                    </a>
                    <a href="/services" class="ss-resource-link">
                        <i class="fas fa-download"></i>
                        <span>Security Whitepaper</span>
                    </a>
                    <a href="/articles" class="ss-resource-link">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Implementation Guide</span>
                    </a>
                    <a href="/services" class="ss-resource-link">
                        <i class="fas fa-video"></i>
                        <span>Product Demo Video</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
