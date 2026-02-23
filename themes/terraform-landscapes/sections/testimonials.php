<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Stories');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Clients Say');
$testimonialsDesc = theme_get('testimonials.description', 'Don\'t just take our word for it. Here\'s what homeowners and businesses have to say about their TerraForm Landscapes experience.');
?>
<section class="tf-section tf-testimonials" id="testimonials">
    <div class="container">
        <div class="tf-section-header" data-animate>
            <span class="tf-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="tf-section-divider"></div>
            <h2 class="tf-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="tf-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="tf-testimonials-slider">
            <div class="tf-testimonial-card" data-animate>
                <div class="tf-testimonial-content">
                    <i class="fas fa-quote-left tf-testimonial-quote"></i>
                    <p class="tf-testimonial-text">"TerraForm transformed our backyard into an oasis. The patio and retaining wall are not only beautiful but perfectly functional. Their team was professional, on-time, and exceeded our expectations."</p>
                </div>
                <div class="tf-testimonial-author">
                    <div class="tf-testimonial-author-info">
                        <h4 class="tf-testimonial-author-name">Michael & Sarah Johnson</h4>
                        <span class="tf-testimonial-author-project">Patio & Retaining Wall Project</span>
                    </div>
                    <div class="tf-testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="tf-testimonial-card" data-animate>
                <div class="tf-testimonial-content">
                    <i class="fas fa-quote-left tf-testimonial-quote"></i>
                    <p class="tf-testimonial-text">"Our artificial grass installation looks incredibly realistic and has held up perfectly through all seasons. The crew was efficient, clean, and left our property spotless. Highly recommend!"</p>
                </div>
                <div class="tf-testimonial-author">
                    <div class="tf-testimonial-author-info">
                        <h4 class="tf-testimonial-author-name">David Chen</h4>
                        <span class="tf-testimonial-author-project">Artificial Grass Installation</span>
                    </div>
                    <div class="tf-testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
            <div class="tf-testimonial-card" data-animate>
                <div class="tf-testimonial-content">
                    <i class="fas fa-quote-left tf-testimonial-quote"></i>
                    <p class="tf-testimonial-text">"The fencing and driveway project completely transformed our home's curb appeal. The attention to detail and quality of materials is evident. TerraForm is our go-to for all outdoor projects."</p>
                </div>
                <div class="tf-testimonial-author">
                    <div class="tf-testimonial-author-info">
                        <h4 class="tf-testimonial-author-name">Roberta Williams</h4>
                        <span class="tf-testimonial-author-project">Driveway & Fencing Upgrade</span>
                    </div>
                    <div class="tf-testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="tf-testimonials-footer" data-animate>
            <div class="tf-testimonials-stats">
                <div class="tf-testimonials-stat">
                    <span class="tf-testimonials-stat-number">4.9</span>
                    <span class="tf-testimonials-stat-label">Average Rating</span>
                </div>
                <div class="tf-testimonials-stat">
                    <span class="tf-testimonials-stat-number">98%</span>
                    <span class="tf-testimonials-stat-label">Repeat Clients</span>
                </div>
                <div class="tf-testimonials-stat">
                    <span class="tf-testimonials-stat-number">200+</span>
                    <span class="tf-testimonials-stat-label">5-Star Reviews</span>
                </div>
            </div>
            <a href="/testimonials" class="tf-btn tf-btn-outline">
                Read More Reviews
                <i class="fas fa-comment tf-btn-icon"></i>
            </a>
        </div>
    </div>
</section>
