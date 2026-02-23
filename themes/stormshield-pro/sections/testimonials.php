<?php
$testimonialsLabel = theme_get('testimonials.label', 'Customer Stories');
$testimonialsTitle = theme_get('testimonials.title', 'What Homeowners Say');
$testimonialsDesc = theme_get('testimonials.description', 'Real feedback from families we\'ve helped protect during their most stressful moments.');

$testimonials = [
    [
        'quote' => theme_get('testimonials.quote1', 'A tree branch tore through our roof during a thunderstorm at 2 AM. StormShield Pro had someone at our door within 45 minutes. They tarped the damage and came back the next day for full repairs. Absolute lifesavers!'),
        'name' => theme_get('testimonials.name1', 'Jennifer M.'),
        'role' => theme_get('testimonials.role1', 'Homeowner, Oakdale'),
        'ts_quote' => 'testimonials.quote1',
        'ts_name' => 'testimonials.name1',
        'ts_role' => 'testimonials.role1'
    ],
    [
        'quote' => theme_get('testimonials.quote2', 'After the hailstorm destroyed half my shingles, I called three companies. StormShield was the only one who could come same-day. Professional, fast, and they even helped with my insurance claim.'),
        'name' => theme_get('testimonials.name2', 'Robert T.'),
        'role' => theme_get('testimonials.role2', 'Homeowner, Cedar Heights'),
        'ts_quote' => 'testimonials.quote2',
        'ts_name' => 'testimonials.name2',
        'ts_role' => 'testimonials.role2'
    ],
    [
        'quote' => theme_get('testimonials.quote3', 'I noticed a leak during a heavy rainstorm and panicked. Their team found the source, fixed it temporarily, and scheduled a permanent repair — all in one afternoon. Can\'t recommend them enough!'),
        'name' => theme_get('testimonials.name3', 'Maria S.'),
        'role' => theme_get('testimonials.role3', 'Homeowner, Riverside'),
        'ts_quote' => 'testimonials.quote3',
        'ts_name' => 'testimonials.name3',
        'ts_role' => 'testimonials.role3'
    ]
];
?>
<section class="ssp-testimonials" id="testimonials">
    <div class="ssp-testimonials-container">
        <div class="ssp-section-header" data-animate>
            <span class="ssp-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="ssp-section-divider">
                <span></span>
                <i class="fas fa-quote-right"></i>
                <span></span>
            </div>
            <h2 class="ssp-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="ssp-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="ssp-testimonials-grid">
            <?php foreach ($testimonials as $index => $testimonial): ?>
            <div class="ssp-testimonial-card" data-animate style="--delay: <?= $index * 0.15 ?>s;">
                <div class="ssp-testimonial-rating">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <blockquote class="ssp-testimonial-quote" data-ts="<?= $testimonial['ts_quote'] ?>">
                    <?= esc($testimonial['quote']) ?>
                </blockquote>
                <div class="ssp-testimonial-author">
                    <div class="ssp-author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ssp-author-info">
                        <span class="ssp-author-name" data-ts="<?= $testimonial['ts_name'] ?>"><?= esc($testimonial['name']) ?></span>
                        <span class="ssp-author-role" data-ts="<?= $testimonial['ts_role'] ?>"><?= esc($testimonial['role']) ?></span>
                    </div>
                </div>
                <div class="ssp-testimonial-accent"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
