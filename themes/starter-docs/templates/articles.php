<?php
/**
 * Articles (Index) Template — KnowledgeBase Theme
 * "Guides & Tutorials" — article list with category pills
 */

$articles = $articles ?? [];

// Collect categories
$categories = [];
foreach ($articles as $a) {
    $cat = $a['category'] ?? 'General';
    if (!in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}
?>

<div class="articles-header">
  <h1>Guides &amp; Tutorials</h1>
  <p>Learn how to build, configure, and deploy with step-by-step walkthroughs.</p>
</div>

<?php if (!empty($categories)): ?>
<div class="articles-filter">
  <span class="filter-pill active">All</span>
  <?php foreach ($categories as $cat): ?>
  <span class="filter-pill" data-category="<?= esc($cat) ?>"><?= esc($cat) ?></span>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="articles-list">
  <?php if (!empty($articles)): ?>
    <?php foreach ($articles as $article): ?>
    <a href="/article/<?= esc($article['slug'] ?? '') ?>" class="article-card" data-category="<?= esc($article['category'] ?? 'General') ?>">
      <h2><?= esc($article['title'] ?? 'Untitled') ?></h2>
      <?php if (!empty($article['excerpt'])): ?>
      <p class="article-excerpt"><?= esc($article['excerpt']) ?></p>
      <?php endif; ?>
      <div class="article-meta">
        <?php if (!empty($article['category'])): ?>
        <span class="article-category"><?= esc($article['category']) ?></span>
        <?php endif; ?>
        <?php if (!empty($article['created_at'])): ?>
        <span><?= date('M j, Y', strtotime($article['created_at'])) ?></span>
        <?php endif; ?>
        <span>5 min read</span>
      </div>
    </a>
    <?php endforeach; ?>
  <?php else: ?>
    <div style="text-align:center;padding:48px 0;color:#94a3b8;">
      <p style="font-size:1.1rem;margin-bottom:8px;">No guides yet</p>
      <p style="font-size:0.875rem;">Check back soon for tutorials and documentation.</p>
    </div>
  <?php endif; ?>
</div>

<script>
(function() {
  var pills = document.querySelectorAll('.filter-pill');
  var cards = document.querySelectorAll('.article-card');
  pills.forEach(function(pill) {
    pill.addEventListener('click', function() {
      pills.forEach(function(p) { p.classList.remove('active'); });
      pill.classList.add('active');
      var cat = pill.getAttribute('data-category');
      cards.forEach(function(card) {
        if (!cat) { card.style.display = ''; return; }
        card.style.display = card.getAttribute('data-category') === cat ? '' : 'none';
      });
    });
  });
})();
</script>
