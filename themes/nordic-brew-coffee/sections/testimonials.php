<?php
$testimonialsLabel = theme_get('testimonials.label', 'Kind Words');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Guests Say');
$testimonialsDesc = theme_get('testimonials.description', 'Real stories from the people who make our mornings worthwhile.');
$testimonial1Quote = theme_get('testimonials.quote1', 'The best pour-over I\'ve had outside of Scandinavia. The atmosphere is so calming—it\'s become my daily ritual.');
$testimonial1Author = theme_get('testimonials.author1', 'Emma S.');
$testimonial1Role = theme_get('testimonials.role1', 'Architect');
$testimonial2Quote = theme_get('testimonials.quote2', 'Finally, a coffee shop that lets the beans speak for themselves. No gimmicks, just exceptional coffee.');
$testimonial2Author = theme_get('testimonials.author2', 'Marcus L.');
$testimonial2Role = theme_get('testimonials.role2', 'Designer');
$testimonial3Quote = theme_get('testimonials.quote3', 'The cardamom bun alone is worth the visit. Paired with their Ethiopian single-origin? Perfection.');
$testimonial3Author = theme_get('testimonials.author3', 'Sofia K.');
$testimonial3Role = theme_get('testimonials.role3', 'Food Writer');
?>
<section class="section testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card" data-animate>
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p data-ts="testimonials.quote1"><?= esc($testimonial1Quote) ?></p>
                </div>
                <div class="testimonial-author">
                    <span class="author-name" data-ts="testimonials.author1"><?= esc($testimonial1Author) ?></span>
                    <span class="author-role" data-ts="testimonials.role1"><?= esc($testimonial1Role) ?></span>
                </div>
            </div>
            <div class="testimonial-card featured" data-animate>
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p data-ts="testimonials.quote2"><?= esc($testimonial2Quote) ?></p>
                </div>
                <div class="testimonial-author">
                    <span class="author-name" data-ts="testimonials.author2"><?= esc($testimonial2Author) ?></span>
                    <span class="author-role" data-ts="testimonials.role2"><?= esc($testimonial2Role) ?></span>
                </div>
            </div>
            <div class="testimonial-card" data-animate>
                <div class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p data-ts="testimonials.quote3"><?= esc($testimonial3Quote) ?></p>
                </div>
                <div class="testimonial-author">
                    <span class="author-name" data-ts="testimonials.author3"><?= esc($testimonial3Author) ?></span>
                    <span class="author-role" data-ts="testimonials.role3"><?= esc($testimonial3Role) ?></span>
                </div>
            </div>
        </div>
    </div>
</section>
