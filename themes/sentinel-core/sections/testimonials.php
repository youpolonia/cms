<?php
$testimonialsLabel = theme_get('testimonials.label', 'ENDORSEMENTS');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Security Leaders');
$testimonialsDesc = theme_get('testimonials.description', 'See how enterprises are transforming their endpoint security with our AI‑powered platform.');
?>
<section class="sc-section sc-section-testimonials" id="testimonials" style="background-color: var(--background);">
    <div class="container">
        <div class="sc-section-header" data-animate>
            <span class="sc-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="sc-section-divider"></div>
            <h2 class="sc-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="sc-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>

        <div class="sc-testimonials-slider">
            <div class="sc-testimonial-card" data-animate>
                <div class="sc-testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p class="sc-testimonial-text">“Sentinel Core’s AI detection identified a sophisticated supply‑chain attack that our legacy tools missed. Their automated response contained it before any data exfiltration.”</p>
                <div class="sc-testimonial-author">
                    <div class="sc-author-avatar">
                        <img src="https://images.pexels.com/photos/5380594/pexels-photo-5380594.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Portrait of a cybersecurity professional" loading="lazy">
                    </div>
                    <div class="sc-author-info">
                        <div class="sc-author-name">Alex Rivera</div>
                        <div class="sc-author-role">CISO, FinTech Global</div>
                    </div>
                </div>
            </div>

            <div class="sc-testimonial-card" data-animate>
                <div class="sc-testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p class="sc-testimonial-text">“The zero‑day prevention module has reduced our mean time to detect by 92%. Our SOC team now focuses on strategic threats instead of chasing alerts.”</p>
                <div class="sc-testimonial-author">
                    <div class="sc-author-avatar">
                        <img src="https://images.pexels.com/photos/5473955/pexels-photo-5473955.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Portrait of a technology executive" loading="lazy">
                    </div>
                    <div class="sc-author-info">
                        <div class="sc-author-name">Dr. Maya Chen</div>
                        <div class="sc-author-role">Head of Security, HealthTech Inc.</div>
                    </div>
                </div>
            </div>

            <div class="sc-testimonial-card" data-animate>
                <div class="sc-testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p class="sc-testimonial-text">“Implementation was seamless, and the ROI was evident within the first quarter. Sentinel Core is now the cornerstone of our defense‑in‑depth strategy.”</p>
                <div class="sc-testimonial-author">
                    <div class="sc-author-avatar">
                        <img src="https://images.pexels.com/photos/6963098/pexels-photo-6963098.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Portrait of a cybersecurity expert" loading="lazy">
                    </div>
                    <div class="sc-author-info">
                        <div class="sc-author-name">James Okafor</div>
                        <div class="sc-author-role">VP IT Security, Manufacturing Corp</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sc-testimonials-logos">
            <div class="sc-logo-item"><span>Fortune 500</span></div>
            <div class="sc-logo-item"><span>Global 2000</span></div>
            <div class="sc-logo-item"><span>Enterprise</span></div>
            <div class="sc-logo-item"><span>Government</span></div>
        </div>
    </div>
</section>
