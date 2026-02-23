<?php
$testimonialsLabel = theme_get('testimonials.label', 'CLIENT SUCCESS STORIES');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Top Freelancers');
$testimonialsDesc = theme_get('testimonials.description', 'See how independent professionals across industries are streamlining their finances with our platform.');
?>
<section class="nf-testimonials-section" id="testimonials">
    <div class="container">
        <div class="nf-section-header" data-animate>
            <span class="nf-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="nf-section-divider"></div>
            <h2 class="nf-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="nf-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="nf-testimonials-slider">
            <div class="nf-testimonials-track">
                <!-- Testimonial 1 -->
                <div class="nf-testimonial-card" data-animate>
                    <div class="nf-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="nf-testimonial-text">"NexusFlow cut my invoicing time by 80%. The AI expense categorization alone saves me 10 hours a month on bookkeeping."</p>
                    <div class="nf-testimonial-author">
                        <div class="nf-author-avatar">
                            <img src="<?= $themePath ?>/assets/avatar-1.jpg" alt="Alex Chen" loading="lazy">
                        </div>
                        <div class="nf-author-info">
                            <h4 class="nf-author-name">Alex Chen</h4>
                            <span class="nf-author-role">UX Designer & Consultant</span>
                        </div>
                    </div>
                </div>
                <!-- Testimonial 2 -->
                <div class="nf-testimonial-card" data-animate>
                    <div class="nf-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="nf-testimonial-text">"As a digital nomad, managing multiple currencies was a nightmare. Now I get paid in euros, dollars, and pounds seamlessly."</p>
                    <div class="nf-testimonial-author">
                        <div class="nf-author-avatar">
                            <img src="<?= $themePath ?>/assets/avatar-2.jpg" alt="Maya Rodriguez" loading="lazy">
                        </div>
                        <div class="nf-author-info">
                            <h4 class="nf-author-name">Maya Rodriguez</h4>
                            <span class="nf-author-role">Content Strategist</span>
                        </div>
                    </div>
                </div>
                <!-- Testimonial 3 -->
                <div class="nf-testimonial-card" data-animate>
                    <div class="nf-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="nf-testimonial-text">"The tax forecasting feature predicted my quarterly payments perfectly. I avoided penalties and saved $2,400 in unexpected fees."</p>
                    <div class="nf-testimonial-author">
                        <div class="nf-author-avatar">
                            <img src="<?= $themePath ?>/assets/avatar-3.jpg" alt="James Okafor" loading="lazy">
                        </div>
                        <div class="nf-author-info">
                            <h4 class="nf-author-name">James Okafor</h4>
                            <span class="nf-author-role">Software Developer</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nf-testimonials-controls">
                <button class="nf-slider-prev" aria-label="Previous testimonial">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="nf-slider-next" aria-label="Next testimonial">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="nf-testimonials-logos" data-animate>
            <span>Featured in:</span>
            <div class="nf-logos-grid">
                <div class="nf-logo-item">Forbes</div>
                <div class="nf-logo-item">TechCrunch</div>
                <div class="nf-logo-item">Freelancers Union</div>
                <div class="nf-logo-item">Remote.co</div>
            </div>
        </div>
    </div>
</section>
