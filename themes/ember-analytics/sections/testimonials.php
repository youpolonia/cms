<?php
$testimonialsLabel = theme_get('testimonials.label', 'Success Stories');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Customers Say');
$testimonialsDesc = theme_get('testimonials.description', 'Join thousands of e-commerce brands that have transformed their business with Ember Analytics.');
$testimonial1Quote = theme_get('testimonials.quote1', 'Ember Analytics helped us identify our most profitable customer segments. We increased our ROI by 52% in just three months.');
$testimonial1Name = theme_get('testimonials.name1', 'Sarah Mitchell');
$testimonial1Role = theme_get('testimonials.role1', 'CEO, StyleHouse Boutique');
$testimonial2Quote = theme_get('testimonials.quote2', 'The predictive insights feature is a game-changer. We now know exactly when to launch promotions for maximum impact.');
$testimonial2Name = theme_get('testimonials.name2', 'Marcus Chen');
$testimonial2Role = theme_get('testimonials.role2', 'Director of Growth, TechGear Pro');
$testimonial3Quote = theme_get('testimonials.quote3', 'Integration was seamless—literally one click for Shopify. The dashboards are intuitive and the support team is exceptional.');
$testimonial3Name = theme_get('testimonials.name3', 'Emily Rodriguez');
$testimonial3Role = theme_get('testimonials.role3', 'Founder, Organic Beauty Co.');
?>
<section class="ea-testimonials" id="testimonials">
    <div class="ea-testimonials-container">
        <div class="ea-testimonials-header" data-animate>
            <span class="ea-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <h2 class="ea-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="ea-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="ea-testimonials-grid">
            <div class="ea-testimonial-card ea-testimonial-featured" data-animate>
                <div class="ea-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="ea-testimonial-quote" data-ts="testimonials.quote1"><?= esc($testimonial1Quote) ?></blockquote>
                <div class="ea-testimonial-author">
                    <div class="ea-testimonial-avatar">
                        <span><?= esc(substr($testimonial1Name, 0, 1)) ?></span>
                    </div>
                    <div class="ea-testimonial-info">
                        <strong class="ea-testimonial-name" data-ts="testimonials.name1"><?= esc($testimonial1Name) ?></strong>
                        <span class="ea-testimonial-role" data-ts="testimonials.role1"><?= esc($testimonial1Role) ?></span>
                    </div>
                </div>
                <div class="ea-testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <div class="ea-testimonial-card" data-animate>
                <div class="ea-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="ea-testimonial-quote" data-ts="testimonials.quote2"><?= esc($testimonial2Quote) ?></blockquote>
                <div class="ea-testimonial-author">
                    <div class="ea-testimonial-avatar">
                        <span><?= esc(substr($testimonial2Name, 0, 1)) ?></span>
                    </div>
                    <div class="ea-testimonial-info">
                        <strong class="ea-testimonial-name" data-ts="testimonials.name2"><?= esc($testimonial2Name) ?></strong>
                        <span class="ea-testimonial-role" data-ts="testimonials.role2"><?= esc($testimonial2Role) ?></span>
                    </div>
                </div>
            </div>
            <div class="ea-testimonial-card" data-animate>
                <div class="ea-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="ea-testimonial-quote" data-ts="testimonials.quote3"><?= esc($testimonial3Quote) ?></blockquote>
                <div class="ea-testimonial-author">
                    <div class="ea-testimonial-avatar">
                        <span><?= esc(substr($testimonial3Name, 0, 1)) ?></span>
                    </div>
                    <div class="ea-testimonial-info">
                        <strong class="ea-testimonial-name" data-ts="testimonials.name3"><?= esc($testimonial3Name) ?></strong>
                        <span class="ea-testimonial-role" data-ts="testimonials.role3"><?= esc($testimonial3Role) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
