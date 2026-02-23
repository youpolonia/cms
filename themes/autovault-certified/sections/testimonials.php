<?php
$tTitle = theme_get('testimonials.title', 'What Our Clients Say About AutoVault Certified');
$tSubtitle = theme_get('testimonials.subtitle', 'Hear from those who matter most');
$items = [];
for ($i = 1; $i <= 3; $i++) {
    $q = theme_get("testimonials.item{$i}_quote", '');
    $items[] = [
        'quote'  => $q ?: 'An absolutely transformative experience. The results speak for themselves and our team could not be happier with the outcome.',
        'name'   => theme_get("testimonials.item{$i}_name", 'Client ' . $i),
        'role'   => theme_get("testimonials.item{$i}_role", 'Director, Organization'),
        'avatar' => theme_get("testimonials.item{$i}_avatar", ''),
        'rating' => (int) theme_get("testimonials.item{$i}_rating", '5'),
    ];
}
?>
<section class="ac-testimonials ac-testimonials--single-featured" id="testimonials">
  <div class="container">
    <div class="ac-testimonials-header" data-animate="fade-up">
      <h2 class="ac-testimonials-title" data-ts="testimonials.title"><?= esc($tTitle) ?></h2>
      <p class="ac-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc($tSubtitle) ?></p>
    </div>
    <div class="ac-testimonials-featured-layout">
      <div class="ac-testimonial-card ac-testimonial-card--featured" data-animate="fade-up">
        <span class="ac-testimonial-quote-mark">&ldquo;</span>
        <div class="ac-testimonial-stars">
          <?php for ($s = 0; $s < $items[0]['rating']; $s++): ?><i class="fas fa-star"></i><?php endfor; ?>
        </div>
        <blockquote class="ac-testimonial-quote">
          <p data-ts="testimonials.item1_quote"><?= esc($items[0]['quote']) ?></p>
        </blockquote>
        <div class="ac-testimonial-author">
          <?php if ($items[0]['avatar']): ?>
          <img src="<?= esc($items[0]['avatar']) ?>" alt="<?= esc($items[0]['name']) ?>" class="ac-testimonial-avatar" data-ts-bg="testimonials.item1_avatar">
          <?php else: ?>
          <div class="ac-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
          <?php endif; ?>
          <div class="ac-testimonial-author-info">
            <strong data-ts="testimonials.item1_name"><?= esc($items[0]['name']) ?></strong>
            <span data-ts="testimonials.item1_role"><?= esc($items[0]['role']) ?></span>
          </div>
        </div>
      </div>
      <div class="ac-testimonials-secondary">
        <?php for ($i = 1; $i < count($items); $i++): ?>
        <div class="ac-testimonial-card" data-animate="fade-up">
          <div class="ac-testimonial-stars">
            <?php for ($s = 0; $s < $items[$i]['rating']; $s++): ?><i class="fas fa-star"></i><?php endfor; ?>
          </div>
          <blockquote class="ac-testimonial-quote">
            <p data-ts="testimonials.item<?= $i+1 ?>_quote"><?= esc($items[$i]['quote']) ?></p>
          </blockquote>
          <div class="ac-testimonial-author">
            <?php if ($items[$i]['avatar']): ?>
            <img src="<?= esc($items[$i]['avatar']) ?>" alt="<?= esc($items[$i]['name']) ?>" class="ac-testimonial-avatar" data-ts-bg="testimonials.item<?= $i+1 ?>_avatar">
            <?php else: ?>
            <div class="ac-testimonial-avatar-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div class="ac-testimonial-author-info">
              <strong data-ts="testimonials.item<?= $i+1 ?>_name"><?= esc($items[$i]['name']) ?></strong>
              <span data-ts="testimonials.item<?= $i+1 ?>_role"><?= esc($items[$i]['role']) ?></span>
            </div>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>
