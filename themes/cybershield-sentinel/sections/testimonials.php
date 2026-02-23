<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Testimonials');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Security Leaders');
$testimonialsDesc = theme_get('testimonials.description', 'See why enterprises choose us as their cybersecurity partner.');

$testimonial1Quote = theme_get('testimonials.quote1', 'CyberShield Sentinel transformed our security posture. Their SOC team detected and neutralized a sophisticated APT attack within minutes of initial breach attempt.');
$testimonial1Author = theme_get('testimonials.author1', 'Marcus Chen');
$testimonial1Role = theme_get('testimonials.role1', 'CISO, Global Financial Services');

$testimonial2Quote = theme_get('testimonials.quote2', 'The next-gen firewall implementation reduced our attack surface by 78%. Their intrusion detection system has caught threats our previous vendor missed entirely.');
$testimonial2Author = theme_get('testimonials.author2', 'Dr. Sarah Mitchell');
$testimonial2Role = theme_get('testimonials.role2', 'VP of IT Security, Healthcare Corp');

$testimonial3Quote = theme_get('testimonials.quote3', 'During a major DDoS attack, their mitigation services kept our e-commerce platform running without a single second of downtime. Exceptional response.');
$testimonial3Author = theme_get('testimonials.author3', 'James Rodriguez');
$testimonial3Role = theme_get('testimonials.role3', 'CTO, Retail Enterprise');
?>
<section class="csh-testimonials-section" id="testimonials">
    <div class="csh-testimonials-bg"></div>
    <div class="container">
        <div class="csh-section-header" data-animate>
            <span class="csh-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="csh-section-divider"></div>
            <h2 class="csh-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="csh-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="csh-testimonials-grid">
            <div class="csh-testimonial-card csh-testimonial-featured" data-animate>
                <div class="csh-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="csh-testimonial-quote" data-ts="testimonials.quote1"><?= esc($testimonial1Quote) ?></blockquote>
                <div class="csh-testimonial-author">
                    <div class="csh-author-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="csh-author-info">
                        <cite class="csh-author-name" data-ts="testimonials.author1"><?= esc($testimonial1Author) ?></cite>
                        <span class="csh-author-role" data-ts="testimonials.role1"><?= esc($testimonial1Role) ?></span>
                    </div>
                </div>
                <div class="csh-testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <div class="csh-testimonial-card" data-animate>
                <div class="csh-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="csh-testimonial-quote" data-ts="testimonials.quote2"><?= esc($testimonial2Quote) ?></blockquote>
                <div class="csh-testimonial-author">
                    <div class="csh-author-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="csh-author-info">
                        <cite class="csh-author-name" data-ts="testimonials.author2"><?= esc($testimonial2Author) ?></cite>
                        <span class="csh-author-role" data-ts="testimonials.role2"><?= esc($testimonial2Role) ?></span>
                    </div>
                </div>
            </div>
            <div class="csh-testimonial-card" data-animate>
                <div class="csh-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="csh-testimonial-quote" data-ts="testimonials.quote3"><?= esc($testimonial3Quote) ?></blockquote>
                <div class="csh-testimonial-author">
                    <div class="csh-author-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="csh-author-info">
                        <cite class="csh-author-name" data-ts="testimonials.author3"><?= esc($testimonial3Author) ?></cite>
                        <span class="csh-author-role" data-ts="testimonials.role3"><?= esc($testimonial3Role) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
