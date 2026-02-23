<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Reviews');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Clients Say');
$testimonialsDesc = theme_get('testimonials.description', 'Don\'t just take our word for it. Here\'s what homeowners and businesses across Essex have to say about working with Edi\'s Paving.');
?>
<section class="section testimonials-section" id="testimonials">
    <div class="testimonials-bg"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="testimonials.label">
                <i class="fas fa-star"></i>
                <?= esc($testimonialsLabel) ?>
            </span>
            <h2 class="section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card testimonial-featured" data-animate>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote class="testimonial-quote">
                    <i class="fas fa-quote-left quote-icon"></i>
                    "Absolutely outstanding work from start to finish. Edi and his team transformed our tired old driveway into something we're genuinely proud of. Professional, punctual, and the quality is exceptional. Three neighbours have already asked for their details!"
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <span class="author-name">Sarah M.</span>
                        <span class="author-location">Epping, Essex</span>
                    </div>
                </div>
                <span class="testimonial-project">Block Paving Driveway</span>
            </div>
            
            <div class="testimonial-card" data-animate>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote class="testimonial-quote">
                    "Used Edi's for our commercial car park. Great communication, fair pricing, and they worked around our business hours. The finish is superb and it was done faster than quoted."
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <span class="author-name">James T.</span>
                        <span class="author-location">Harlow, Essex</span>
                    </div>
                </div>
                <span class="testimonial-project">Commercial Asphalt</span>
            </div>
            
            <div class="testimonial-card" data-animate>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote class="testimonial-quote">
                    "Our new patio has completely changed how we use our garden. The natural stone is beautiful and the workmanship is faultless. A real family business that takes pride in their work."
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <span class="author-name">David & Claire P.</span>
                        <span class="author-location">Chigwell, Essex</span>
                    </div>
                </div>
                <span class="testimonial-project">Natural Stone Patio</span>
            </div>
            
            <div class="testimonial-card" data-animate>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <blockquote class="testimonial-quote">
                    "Second time using Edi's—this time for our back garden landscaping. They remembered us from 5 years ago! Consistent quality and genuine, trustworthy people."
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <span class="author-name">Robert H.</span>
                        <span class="author-location">Loughton, Essex</span>
                    </div>
                </div>
                <span class="testimonial-project">Garden Landscaping</span>
            </div>
        </div>
        
        <div class="testimonials-trust" data-animate>
            <div class="trust-item">
                <i class="fab fa-instagram"></i>
                <span>Follow us @edispaving</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <i class="fas fa-shield-alt"></i>
                <span>Fully Insured</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <i class="fas fa-certificate"></i>
                <span>Free Written Quotes</span>
            </div>
        </div>
    </div>
</section>