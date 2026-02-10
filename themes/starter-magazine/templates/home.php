<?php
// Featured article
try {
    $pdo = \core\Database::connection();
    $featured = $pdo->query("SELECT * FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $grid = $pdo->query("SELECT * FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 4 OFFSET 1")->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) { $featured = null; $grid = []; }
?>
<?php if ($featured): ?>
<section class="featured-article">
  <a href="/articles/<?= esc($featured['slug']) ?>" class="featured-link">
    <?php if ($featured['featured_image']): ?>
    <img src="<?= esc($featured['featured_image']) ?>" alt="<?= esc($featured['title']) ?>">
    <?php else: ?>
    <div class="featured-placeholder"></div>
    <?php endif; ?>
    <div class="featured-overlay">
      <span class="featured-badge">Featured</span>
      <h2><?= esc($featured['title']) ?></h2>
      <p><?= esc($featured['excerpt'] ?? '') ?></p>
      <span class="featured-date"><?= date('F j, Y', strtotime($featured['created_at'])) ?></span>
    </div>
  </a>
</section>
<?php endif; ?>

<section class="latest-articles">
  <h2 class="section-title" data-ts="articles.title">Latest Stories</h2>
  <div class="articles-grid">
    <?php foreach ($grid as $art): ?>
    <article class="article-card">
      <?php if ($art['featured_image']): ?>
      <a href="/articles/<?= esc($art['slug']) ?>" class="card-image"><img src="<?= esc($art['featured_image']) ?>" alt="<?= esc($art['title']) ?>" loading="lazy"></a>
      <?php endif; ?>
      <div class="card-body">
        <h3><a href="/articles/<?= esc($art['slug']) ?>"><?= esc($art['title']) ?></a></h3>
        <p class="card-excerpt"><?= esc($art['excerpt'] ?? '') ?></p>
        <span class="card-date"><?= date('M j, Y', strtotime($art['created_at'])) ?></span>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($page['content']) && trim(strip_tags($page['content'])) !== ''): ?>
<section class="page-content"><?= $page["content"] ?? "" ?></section>
<?php endif; ?>
