<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Feedback');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Customers Say');
$testimonialsDesc = theme_get('testimonials.description', 'Don\'t just take our word for it. Here\'s what our satisfied clients have to say about working with Edi\'s Paving Contractors.');

$testimonials = [
    [
        'quote' => theme_get('testimonials.quote1', 'Edi and his team did an outstanding job on our driveway. From start to finish, the work was professional and the quality is exceptional. Highly recommend!'),
        'name' => theme_get('testimonials.name1', 'James & Sarah Mitchell'),
        'location' => theme_get('testimonials.location1', 'Epping, Essex'),
        'rating' => 5
    ],
    [
        'quote' => theme_get('testimonials.quote2', 'We had our entire garden transformed including a new patio and pathways. The attention to detail was incredible. It looks better than we imagined!'),
        'name' => theme_get('testimonials.name2', 'David Thompson'),
        'location' => theme_get('testimonials.location2', 'Harlow, Essex'),
        'rating' => 5
    ],
    [
        'quote' => theme_get('testimonials.quote3', 'Used Edi\'s for our commercial car park. Great communication throughout, completed on time, and very competitive pricing. Will definitely use again.'),
        'name' => theme_get('testimonials.name3', 'Premier Properties Ltd'),
        'location' => theme_get('testimonials.location3', 'Chelmsford, Essex'),
        'rating' => 5
    ]
];
?>
<section class="section testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <h2 class="section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $index => $t): ?>
            <div class="testimonial-card" data-animate style="--delay: <?= $index * 0.15 ?>s">
                <div class="testimonial-rating">
                    <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <blockquote class="testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p data-ts="testimonials.quote<?= $index + 1 ?>"><?= esc($t['quote']) ?></p>
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <strong data-ts="testimonials.name<?= $index + 1 ?>"><?= esc($t['name']) ?></strong>
                        <span data-ts="testimonials.location<?= $index + 1 ?>"><?= esc($t['location']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>