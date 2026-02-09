<?php
$pageTitle = $page['title'] ?? 'Page';
$pageSlug = $page['slug'] ?? '';
?>
<nav class="breadcrumb">
  <a href="/">Home</a>
  <span class="separator">›</span>
  <span><?= esc($pageTitle) ?></span>
</nav>

<article class="page-content">
  <h1><?= esc($pageTitle) ?></h1>
  <div class="page-body">
    <?= $page["content"] ?? "" ?>
  </div>
</article>
