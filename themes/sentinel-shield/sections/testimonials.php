<?php
$testimonialsLabel = theme_get('testimonials.label', 'CLIENT SUCCESS STORIES');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Security Leaders Worldwide');
$testimonialsDesc = theme_get('testimonials.description', 'See how organizations across regulated industries are modernizing their identity infrastructure with Sentinel Shield.');
?>
<section class="ss-testimonials-section">
    <div class="container">
        <div class="ss-section-header" data-animate>
            <span class="ss-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="ss-section-divider"></div>
            <h2 class="ss-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="ss-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="ss-testimonials-showcase">
            <div class="ss-testimonial-featured" data-animate>
                <div class="ss-testimonial-quote-mark">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="ss-testimonial-text">
                    "Sentinel Shield transformed our security posture. We eliminated 100% of password-related breaches and reduced our mean time to authenticate by 73%. The compliance reporting alone saved our team 400+ hours during our SOC 2 audit."
                </blockquote>
                <div class="ss-testimonial-author">
                    <div class="ss-author-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="ss-author-info">
                        <span class="ss-author-name">Sarah Chen</span>
                        <span class="ss-author-title">Chief Security Officer, FinTech Global</span>
                    </div>
                </div>
                <div class="ss-testimonial-metrics">
                    <div class="ss-metric-pill">
                        <i class="fas fa-check-circle"></i>
                        <span>Zero Breaches</span>
                    </div>
                    <div class="ss-metric-pill">
                        <i class="fas fa-clock"></i>
                        <span>73% Faster Auth</span>
                    </div>
                    <div class="ss-metric-pill">
                        <i class="fas fa-file-alt"></i>
                        <span>400+ Hours Saved</span>
                    </div>
                </div>
            </div>
            <div class="ss-testimonials-grid">
                <div class="ss-testimonial-card" data-animate>
                    <div class="ss-card-header">
                        <i class="fas fa-hospital"></i>
                        <span class="ss-industry-tag">Healthcare</span>
                    </div>
                    <p class="ss-testimonial-excerpt">
                        "HIPAA compliance reporting is now automated. Our audit prep time went from 3 weeks to 2 days. The biometric authentication gives our clinicians secure instant access to patient records."
                    </p>
                    <div class="ss-testimonial-footer">
                        <div class="ss-author-compact">
                            <span class="ss-author-name">Dr. Michael Torres</span>
                            <span class="ss-author-role">CTO, Regional Medical Center</span>
                        </div>
                        <div class="ss-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="ss-testimonial-card" data-animate>
                    <div class="ss-card-header">
                        <i class="fas fa-university"></i>
                        <span class="ss-industry-tag">Financial Services</span>
                    </div>
                    <p class="ss-testimonial-excerpt">
                        "Our regulatory compliance burden decreased dramatically. Real-time audit logs and zero-trust architecture helped us pass our PCI-DSS assessment with flying colors. Support tickets dropped 84%."
                    </p>
                    <div class="ss-testimonial-footer">
                        <div class="ss-author-compact">
                            <span class="ss-author-name">Jennifer Park</span>
                            <span class="ss-author-role">VP Security, National Bank</span>
                        </div>
                        <div class="ss-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="ss-testimonial-card" data-animate>
                    <div class="ss-card-header">
                        <i class="fas fa-landmark"></i>
                        <span class="ss-industry-tag">Government</span>
                    </div>
                    <p class="ss-testimonial-excerpt">
                        "We needed FedRAMP-compliant identity management for 15,000 users across multiple agencies. Sentinel Shield delivered. The SSO integration was seamless and the biometric layer adds critical security."
                    </p>
                    <div class="ss-testimonial-footer">
                        <div class="ss-author-compact">
                            <span class="ss-author-name">Robert Harrison</span>
                            <span class="ss-author-role">Director IT Security, State Agency</span>
                        </div>
                        <div class="ss-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
