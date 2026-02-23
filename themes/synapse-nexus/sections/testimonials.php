<?php
$testimonialsLabel = theme_get('testimonials.label', 'VOICES FROM TEAMS');
$testimonialsTitle = theme_get('testimonials.title', 'Loved by Engineering Leaders');
$testimonialsDesc = theme_get('testimonials.description', 'See how remote‑first teams are transforming their collaboration and shipping faster.');
?>
<section class="sn-section sn-section-testimonials" id="testimonials">
    <div class="container">
        <div class="sn-section-header" data-animate>
            <span class="sn-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="sn-section-divider"></div>
            <h2 class="sn-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="sn-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="sn-testimonials-slider">
            <div class="sn-testimonials-track">
                <div class="sn-testimonial-card" data-animate>
                    <div class="sn-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="sn-testimonial-text">“The channel‑thread model finally makes sense for technical discussions. We’ve cut meeting time by 40% and decisions happen where the code lives.”</p>
                    <div class="sn-testimonial-author">
                        <img src="<?= $themePath ?>/assets/avatar-1.jpg" alt="Alex Chen" class="sn-testimonial-avatar">
                        <div class="sn-testimonial-info">
                            <h4>Alex Chen</h4>
                            <span>CTO, TechFlow Inc.</span>
                        </div>
                    </div>
                </div>
                <div class="sn-testimonial-card" data-animate>
                    <div class="sn-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="sn-testimonial-text">“With 500+ integrations, we connected our entire stack—GitHub, Jira, Figma—into one hub. Our remote team feels more aligned than when we were in‑office.”</p>
                    <div class="sn-testimonial-author">
                        <img src="<?= $themePath ?>/assets/avatar-2.jpg" alt="Maya Rodriguez" class="sn-testimonial-avatar">
                        <div class="sn-testimonial-info">
                            <h4>Maya Rodriguez</h4>
                            <span>Engineering Lead, RemoteFirst</span>
                        </div>
                    </div>
                </div>
                <div class="sn-testimonial-card" data-animate>
                    <div class="sn-testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="sn-testimonial-text">“Video calls with built‑in collaborative coding are a game‑changer for pair programming across time zones. It feels like we’re sitting side‑by‑side.”</p>
                    <div class="sn-testimonial-author">
                        <img src="<?= $themePath ?>/assets/avatar-3.jpg" alt="David Park" class="sn-testimonial-avatar">
                        <div class="sn-testimonial-info">
                            <h4>David Park</h4>
                            <span>Senior Dev, Horizon Labs</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sn-testimonials-nav">
                <button class="sn-testimonial-prev" aria-label="Previous testimonial"><i class="fas fa-chevron-left"></i></button>
                <button class="sn-testimonial-next" aria-label="Next testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="sn-testimonials-logos">
            <div class="sn-logo-item"><span>GitHub</span></div>
            <div class="sn-logo-item"><span>Figma</span></div>
            <div class="sn-logo-item"><span>Vercel</span></div>
            <div class="sn-logo-item"><span>Jira</span></div>
            <div class="sn-logo-item"><span>Slack</span></div>
        </div>
    </div>
</section>
