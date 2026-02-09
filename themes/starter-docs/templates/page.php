<?php
/**
 * Page Template — KnowledgeBase Theme
 * Breadcrumb → Title → Content (clean docs style)
 */
$title = $page['title'] ?? 'Untitled';
?>

<nav class="breadcrumb">
  <a href="/">Home</a>
  <span class="sep">›</span>
  <span class="current"><?= esc($title) ?></span>
</nav>

<h1><?= esc($title) ?></h1>

<div class="page-body">
  <?= $content ?>
</div>
