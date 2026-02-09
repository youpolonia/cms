<?php
/**
 * Single Article Template — KnowledgeBase Theme
 * Breadcrumb, title, meta, content, feedback
 */
$title = $article['title'] ?? $page['title'] ?? 'Untitled';
$date = $article['created_at'] ?? $page['created_at'] ?? '';
$category = $article['category'] ?? '';
$wordCount = str_word_count(strip_tags($content));
$readTime = max(1, ceil($wordCount / 200));
?>

<nav class="breadcrumb">
  <a href="/">Home</a>
  <span class="sep">›</span>
  <a href="/articles">Guides</a>
  <span class="sep">›</span>
  <span class="current"><?= esc($title) ?></span>
</nav>

<div class="article-header">
  <h1><?= esc($title) ?></h1>
  <div class="article-meta-bar">
    <?php if ($date): ?>
    <span><?= date('M j, Y', strtotime($date)) ?></span>
    <span class="divider">·</span>
    <?php endif; ?>
    <span><?= $readTime ?> min read</span>
    <?php if ($category): ?>
    <span class="divider">·</span>
    <span class="article-category" style="display:inline-block;"><?= esc($category) ?></span>
    <?php endif; ?>
  </div>
</div>

<div class="article-body content-inner">
  <?= $content ?>
</div>

<div class="article-feedback">
  <p>Was this article helpful?</p>
  <div class="feedback-buttons">
    <button class="feedback-btn" onclick="this.textContent='Thanks! 👍'">👍 Yes</button>
    <button class="feedback-btn" onclick="this.textContent='Sorry! We\'ll improve.'">👎 No</button>
  </div>
</div>
