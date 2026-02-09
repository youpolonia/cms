<?php
$article = $article ?? $page ?? [];
$articleTitle = $article['title'] ?? 'Article';
$articleSlug = $article['slug'] ?? '';
$articleDate = $article['created_at'] ?? date('Y-m-d');
$articleImage = $article['featured_image'] ?? '';
$articleExcerpt = $article['excerpt'] ?? '';
$authorName = $article['author_name'] ?? $article['author'] ?? 'Editorial Staff';

// Related articles
$related = [];
try {
    $pdo = \core\Database::connection();
    $stmtRelated = $pdo->prepare("SELECT id, slug, title, featured_image, created_at FROM articles WHERE status='published' AND slug != ? ORDER BY created_at DESC LIMIT 3");
    $stmtRelated->execute([$articleSlug]);
    $related = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}
?>
<nav class="breadcrumb">
  <a href="/">Home</a>
  <span class="separator">›</span>
  <a href="/articles">Articles</a>
  <span class="separator">›</span>
  <span><?= esc($articleTitle) ?></span>
</nav>

<article class="single-article">
  <?php if ($articleImage): ?>
  <img src="<?= esc($articleImage) ?>" alt="<?= esc($articleTitle) ?>" class="article-header-image">
  <?php endif; ?>

  <h1><?= esc($articleTitle) ?></h1>

  <div class="article-meta">
    <span class="meta-author"><?= esc($authorName) ?></span>
    <span class="meta-divider">·</span>
    <span class="meta-date"><?= date('F j, Y', strtotime($articleDate)) ?></span>
    <span class="meta-divider">·</span>
    <span class="meta-reading"><?= max(1, round(str_word_count(strip_tags($article['content'] ?? '')) / 200)) ?> min read</span>
  </div>

  <div class="article-content">
    <?= $article["content"] ?? "" ?>
  </div>

  <div class="article-tags">
    <span class="article-tag">Technology</span>
    <span class="article-tag">Culture</span>
    <span class="article-tag">Featured</span>
  </div>
</article>

<?php if (!empty($related)): ?>
<section class="related-articles">
  <h3>You Might Also Like</h3>
  <div class="related-grid">
    <?php foreach ($related as $rel): ?>
    <a href="/articles/<?= esc($rel['slug']) ?>" class="related-card">
      <?php if (!empty($rel['featured_image'])): ?>
      <img src="<?= esc($rel['featured_image']) ?>" alt="<?= esc($rel['title']) ?>" loading="lazy">
      <?php endif; ?>
      <h4><?= esc($rel['title']) ?></h4>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
