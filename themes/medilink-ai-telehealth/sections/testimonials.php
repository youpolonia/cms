<?php
$tTitle = theme_get('testimonials.title', 'What Our Clients Say About MediLink AI');
$tSubtitle = theme_get('testimonials.subtitle', 'Trusted by industry leaders');
$items = [];
for ($i = 1; $i <= 6; $i++) {
    $q = theme_get("testimonials.item{$i}_quote", '');
    if ($q || $i <= 3) {
        $items[] = [
            'quote'  => $q ?: 'A game-changing solution that streamlined our entire workflow. The ROI has been exceptional.',
            'name'   => theme_get("testimonials.item{$i}_name", 'Client ' . $i),
            'role'   => theme_get("testimonials.item{$i}_role", 'CTO, Tech Corp'),
            'rating' => (int) theme_get("testimonials.item{$i}_rating", '5'),
        ];
    }
}
?>
<section class="mat-testimonials mat-testimonials--cards-minimal" id="testimonials">
  <div class="container">
    <div class="mat-testimonials-header" data-animate="fade-up">
      <h2 class="mat-testimonials-title" data-ts="testimonials.title"><?= esc($tTitle) ?></h2>
      <p class="mat-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc($tSubtitle) ?></p>
    </div>
    <div class="mat-testimonials-grid">
      <?php foreach ($items as $idx => $item): ?>
      <div class="mat-testimonial-card" data-animate="fade-up">
        <div class="mat-testimonial-stars">
          <?php for ($s = 0; $s < $item['rating']; $s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="mat-testimonial-quote">
          <p data-ts="testimonials.item<?= $idx+1 ?>_quote"><?= esc($item['quote']) ?></p>
        </blockquote>
        <div class="mat-testimonial-author-minimal">
          <strong data-ts="testimonials.item<?= $idx+1 ?>_name"><?= esc($item['name']) ?></strong>
          <span data-ts="testimonials.item<?= $idx+1 ?>_role"><?= esc($item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
