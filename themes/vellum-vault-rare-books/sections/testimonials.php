<?php
$tTitle = theme_get('testimonials.title', 'What Our Clients Say About Vellum & Vault');
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
<section class="vvr-testimonials vvr-testimonials--cards-minimal" id="testimonials">
  <div class="container">
    <div class="vvr-testimonials-header" data-animate="fade-up">
      <h2 class="vvr-testimonials-title" data-ts="testimonials.title"><?= esc($tTitle) ?></h2>
      <p class="vvr-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc($tSubtitle) ?></p>
    </div>
    <div class="vvr-testimonials-grid">
      <?php foreach ($items as $idx => $item): ?>
      <div class="vvr-testimonial-card" data-animate="fade-up">
        <div class="vvr-testimonial-stars">
          <?php for ($s = 0; $s < $item['rating']; $s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="vvr-testimonial-quote">
          <p data-ts="testimonials.item<?= $idx+1 ?>_quote"><?= esc($item['quote']) ?></p>
        </blockquote>
        <div class="vvr-testimonial-author-minimal">
          <strong data-ts="testimonials.item<?= $idx+1 ?>_name"><?= esc($item['name']) ?></strong>
          <span data-ts="testimonials.item<?= $idx+1 ?>_role"><?= esc($item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
