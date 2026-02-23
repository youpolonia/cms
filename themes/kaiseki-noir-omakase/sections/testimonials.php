<?php
$testimonialsLabel = theme_get('testimonials.label', 'Guest Reflections');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Guests Say');
$testimonialsDesc = theme_get('testimonials.description', 'The omakase experience transcends dining—it becomes a cherished memory, a story told with reverence.');
?>
<section class="kno-section kno-testimonials" id="testimonials">
    <div class="container">
        <div class="kno-section-header kno-section-header-center" data-animate>
            <span class="kno-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="kno-section-divider"></div>
            <h2 class="kno-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="kno-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>

        <div class="kno-testimonials-grid">
            <div class="kno-testimonial-card" data-animate>
                <div class="kno-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="kno-testimonial-text">"An experience that borders on the spiritual. Every course was a revelation, every moment deliberately crafted. The chef's mastery is evident in the quietest details."</p>
                <div class="kno-testimonial-author">
                    <div class="kno-author-info">
                        <div class="kno-author-name">James Whitmore</div>
                        <div class="kno-author-title">Food Critic, The Telegraph</div>
                    </div>
                </div>
                <div class="kno-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="kno-testimonial-card" data-animate>
                <div class="kno-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="kno-testimonial-text">"Having dined at some of Tokyo's finest establishments, I can say with certainty that Kaiseki Noir stands among them. The precision, the respect for ingredients—it's pure omakase."</p>
                <div class="kno-testimonial-author">
                    <div class="kno-author-info">
                        <div class="kno-author-name">Yuki Tanaka</div>
                        <div class="kno-author-title">Culinary Consultant</div>
                    </div>
                </div>
                <div class="kno-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="kno-testimonial-card" data-animate>
                <div class="kno-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="kno-testimonial-text">"We celebrated our anniversary here and it exceeded every expectation. The intimacy of the counter, the theater of the preparation, the exquisite flavors—unforgettable."</p>
                <div class="kno-testimonial-author">
                    <div class="kno-author-info">
                        <div class="kno-author-name">Sarah & Marcus Chen</div>
                        <div class="kno-author-title">London</div>
                    </div>
                </div>
                <div class="kno-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>
