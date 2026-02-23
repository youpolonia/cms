<?php
$testimonialsLabel = theme_get('testimonials.label', 'Community Voices');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Climbers Say');
$testimonialsDesc = theme_get('testimonials.description', 'Join hundreds of climbers who\'ve found their home at Summit Pulse.');

$t1Quote = theme_get('testimonials.item1_quote', 'The auto-belay walls are a game-changer for my solo training sessions. I\'ve improved more in 3 months here than a year at my old gym.');
$t1Name = theme_get('testimonials.item1_name', 'Marcus Chen');
$t1Role = theme_get('testimonials.item1_role', 'Member since 2022');

$t2Quote = theme_get('testimonials.item2_quote', 'My daughter started in the youth program at age 7. The coaches are incredible — patient, encouraging, and really know how to make climbing fun.');
$t2Name = theme_get('testimonials.item2_name', 'Sarah Mitchell');
$t2Role = theme_get('testimonials.item2_role', 'Parent');

$t3Quote = theme_get('testimonials.item3_quote', 'Best bouldering facility in the city, hands down. Fresh routes every week and the community here is so welcoming. Plus, the protein shakes are legit!');
$t3Name = theme_get('testimonials.item3_name', 'Jake Rodriguez');
$t3Role = theme_get('testimonials.item3_role', 'V6 Climber');
?>
<section class="sp-testimonials" id="testimonials">
    <div class="sp-testimonials-container">
        <div class="sp-testimonials-header" data-animate>
            <span class="sp-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="sp-section-divider"></div>
            <h2 class="sp-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="sp-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="sp-testimonials-grid">
            <div class="sp-testimonial-card" data-animate>
                <div class="sp-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="sp-testimonial-text" data-ts="testimonials.item1_quote"><?= esc($t1Quote) ?></p>
                <div class="sp-testimonial-author">
                    <div class="sp-testimonial-avatar">
                        <span><?= substr($t1Name, 0, 1) ?></span>
                    </div>
                    <div class="sp-testimonial-info">
                        <h4 class="sp-testimonial-name" data-ts="testimonials.item1_name"><?= esc($t1Name) ?></h4>
                        <span class="sp-testimonial-role" data-ts="testimonials.item1_role"><?= esc($t1Role) ?></span>
                    </div>
                </div>
                <div class="sp-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas