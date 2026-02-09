<?php
try {
    $pdo = \core\Database::connection();
    $allArticles = $pdo->query("SELECT * FROM articles WHERE status='published' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    $allArticles = [];
}
?>
<nav class="breadcrumb">
  <a href="/">Home</a>
  <span class="separator">›</span>
  <span>Articles</span>
</nav>

<h1>All Articles</h1>

<div class="filter-bar">
  <span class="filter-pill active" data-category="all">All</span>
  <span class="filter-pill" data-category="technology">Technology</span>
  <span class="filter-pill" data-category="culture">Culture</span>
  <span class="filter-pill" data-category="science">Science</span>
  <span class="filter-pill" data-category="opinion">Opinion</span>
  <span class="filter-pill" data-category="arts">Arts</span>
</div>

<div class="article-list">
  <?php if (empty($allArticles)): ?>
    <p class="text-muted">No articles published yet. Check back soon!</p>
  <?php else: ?>
    <?php foreach ($allArticles as $art): ?>
    <div class="article-list-item" data-category="<?= esc(strtolower($art['category'] ?? 'general')) ?>">
      <?php if (!empty($art['featured_image'])): ?>
      <a href="/articles/<?= esc($art['slug']) ?>" class="article-list-thumb">
        <img src="<?= esc($art['featured_image']) ?>" alt="<?= esc($art['title']) ?>" loading="lazy">
      </a>
      <?php endif; ?>
      <div class="article-list-body">
        <h3><a href="/articles/<?= esc($art['slug']) ?>"><?= esc($art['title']) ?></a></h3>
        <p class="article-list-excerpt"><?= esc($art['excerpt'] ?? '') ?></p>
        <span class="article-list-meta"><?= date('F j, Y', strtotime($art['created_at'])) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if (!empty($content) && trim(strip_tags($content)) !== ''): ?>
<section class="page-content mt-4"><?= $content ?></section>
<?php endif; ?>
