<?php
$testimonialsLabel = theme_get('testimonials.label', 'Praise');
$testimonialsTitle = theme_get('testimonials.title', 'Guests Speak of Unforgettable Evenings');
$testimonialsDesc = theme_get('testimonials.description', 'The true measure of our craft is found in the experiences shared at our tables.');
?>
<section class="agr-section agr-section--testimonials" id="testimonials">
    <div class="container">
        <div class="agr-section-header" data-animate>
            <span class="agr-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="agr-section-divider"></div>
            <h2 class="agr-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="agr-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="agr-testimonials-grid">
            <div class="agr-testimonial-card" data-animate>
                <div class="agr-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="agr-testimonial-text">“The eight‑course tasting menu was a revelation. Each plate was not just food, but a story—perfectly paced, exquisitely paired. An evening we’ll recount for years.”</p>
                <div class="agr-testimonial-author">
                    <div class="agr-author-avatar">MR</div>
                    <div class="agr-author-info">
                        <h5>Marcus &amp; Rebecca</h5>
                        <span>Anniversary Dinner</span>
                    </div>
                </div>
            </div>
            <div class="agr-testimonial-card" data-animate>
                <div class="agr-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="agr-testimonial-text">“As a sommelier, I’m rarely impressed. Amber Grove’s wine list is a curated masterpiece. The staff’s knowledge elevated our pairing to another level entirely.”</p>
                <div class="agr-testimonial-author">
                    <div class="agr-author-avatar">CS</div>
                    <div class="agr-author-info">
                        <h5>Clara Simmons</h5>
                        <span>Wine Professional</span>
                    </div>
                </div>
            </div>
            <div class="agr-testimonial-card" data-animate>
                <div class="agr-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="agr-testimonial-text">“We hosted a private event in the cellar room. The attention to detail, the custom menu, the seamless service—it was flawless. Our guests are still talking about it.”</p>
                <div class="agr-testimonial-author">
                    <div class="agr-author-avatar">TJ</div>
                    <div class="agr-author-info">
                        <h5>TechJunction Inc.</h5>
                        <span>Corporate Event</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="agr-testimonials-footer" data-animate>
            <div class="agr-rating-badge">
                <div class="agr-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <span>4.9 · 347 reviews</span>
            </div>
            <a href="/page/testimonials" class="agr-btn agr-btn--text">
                Read All Stories <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
</section>
