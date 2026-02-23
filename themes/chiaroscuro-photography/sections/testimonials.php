<?php
$tTitle = theme_get('testimonials.title', 'What Our Clients Say About Chiaroscuro Studio');
$tSubtitle = theme_get('testimonials.subtitle', 'Selected words from our portfolio');
$items = [];
for ($i = 1; $i <= 4; $i++) {
    $q = theme_get("testimonials.item{$i}_quote", '');
    if ($q || $i <= 3) {
        $items[] = [
            'quote'  => $q ?: 'Refined, elegant, and meticulously crafted. The result is nothing short of extraordinary.',
            'name'   => theme_get("testimonials.item{$i}_name", 'Client ' . $i),
            'role'   => theme_get("testimonials.item{$i}_role", 'Private Client'),
            'rating' => (int) theme_get("testimonials.item{$i}_rating", '5'),
        ];
    }
}
?>
<section class="cp-testimonials cp-testimonials--minimal-list" id="testimonials">
  <div class="container">
    <div class="cp-testimonials-header" data-animate="fade-up">
      <h2 class="cp-testimonials-title" data-ts="testimonials.title"><?= esc($tTitle) ?></h2>
      <p class="cp-testimonials-subtitle" data-ts="testimonials.subtitle"><?= esc($tSubtitle) ?></p>
    </div>
    <div class="cp-testimonials-list">
      <?php foreach ($items as $idx => $item): ?>
      <div class="cp-testimonial-list-item" data-animate="fade-up">
        <blockquote class="cp-testimonial-quote">
          <p data-ts="testimonials.item<?= $idx+1 ?>_quote"><?= esc($item['quote']) ?></p>
        </blockquote>
        <div class="cp-testimonial-list-meta">
          <strong data-ts="testimonials.item<?= $idx+1 ?>_name"><?= esc($item['name']) ?></strong>
          <span class="cp-testimonial-list-divider">&mdash;</span>
          <span data-ts="testimonials.item<?= $idx+1 ?>_role"><?= esc($item['role']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
