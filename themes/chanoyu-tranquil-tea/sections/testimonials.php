<?php
$testimonialsLabel = theme_get('testimonials.label', 'Guest Reflections');
$testimonialsTitle = theme_get('testimonials.title', 'Moments of Tranquility Shared');
$testimonialsDesc = theme_get('testimonials.description', 'Discover what our guests experience in the sacred space of tea ceremony.');

$testimonial1 = theme_get('testimonials.testimonial1', 'The matcha ceremony was transcendent. Every gesture held meaning, every moment invited stillness. A truly transformative experience.');
$author1 = theme_get('testimonials.author1', 'Sarah Chen');
$role1 = theme_get('testimonials.role1', 'Cultural Enthusiast');

$testimonial2 = theme_get('testimonials.testimonial2', 'Exquisite kaiseki, impeccable service, and a bamboo garden that whispers serenity. Chanoyu is where time slows and beauty deepens.');
$author2 = theme_get('testimonials.author2', 'James Morrison');
$role2 = theme_get('testimonials.role2', 'Food Critic');

$testimonial3 = theme_get('testimonials.testimonial3', 'The wagashi sweets are edible poetry. Each delicate creation reflects the season with artistry and grace. An unforgettable journey.');
$author3 = theme_get('testimonials.author3', 'Yuki Tanaka');
$role3 = theme_get('testimonials.role3', 'Tea Ceremony Student');
?>
<section class="ch-testimonials" id="testimonials">
    <div class="container">
        <div class="ch-section-header ch-section-header-center">
            <span class="ch-section-label" data-ts="testimonials.label" data-animate>
                <i class="fas fa-quote-right"></i>
                <?= esc($testimonialsLabel) ?>
            </span>
            <div class="ch-section-divider ch-section-divider-center"></div>
            <h2 class="ch-section-title" data-ts="testimonials.title" data-animate>
                <?= esc($testimonialsTitle) ?>
            </h2>
            <p class="ch-section-desc" data-ts="testimonials.description" data-animate>
                <?= esc($testimonialsDesc) ?>
            </p>
        </div>

        <div class="ch-testimonials-grid">
            <div class="ch-testimonial-card" data-animate>
                <div class="ch-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="ch-testimonial-text" data-ts="testimonials.testimonial1">
                    <?= esc($testimonial1) ?>
                </p>
                <div class="ch-testimonial-author">
                    <div class="ch-author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ch-author-info">
                        <h5 class="ch-author-name" data-ts="testimonials.author1">
                            <?= esc($author1) ?>
                        </h5>
                        <span class="ch-author-role" data-ts="testimonials.role1">
                            <?= esc($role1) ?>
                        </span>
                    </div>
                </div>
                <div class="ch-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="ch-testimonial-card" data-animate>
                <div class="ch-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="ch-testimonial-text" data-ts="testimonials.testimonial2">
                    <?= esc($testimonial2) ?>
                </p>
                <div class="ch-testimonial-author">
                    <div class="ch-author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ch-author-info">
                        <h5 class="ch-author-name" data-ts="testimonials.author2">
                            <?= esc($author2) ?>
                        </h5>
                        <span class="ch-author-role" data-ts="testimonials.role2">
                            <?= esc($role2) ?>
                        </span>
                    </div>
                </div>
                <div class="ch-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>

            <div class="ch-testimonial-card" data-animate>
                <div class="ch-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="ch-testimonial-text" data-ts="testimonials.testimonial3">
                    <?= esc($testimonial3) ?>
                </p>
                <div class="ch-testimonial-author">
                    <div class="ch-author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ch-author-info">
                        <h5 class="ch-author-name" data-ts="testimonials.author3">
                            <?= esc($author3) ?>
                        </h5>
                        <span class="ch-author-role" data-ts="testimonials.role3">
                            <?= esc($role3) ?>
                        </span>
                    </div>
                </div>
                <div class="ch-testimonial-stars">
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
