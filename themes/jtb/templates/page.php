<?php
/**
 * JTB Theme — Generic Page Template
 * JTB pages are rendered directly from layout.php (via jtb_pages table).
 * This template handles non-JTB pages (fallback).
 */
$pageTitle   = $page['title'] ?? $title ?? 'Page';
$pageContent = $page['content'] ?? $content ?? '';
$isRich = strlen(strip_tags($pageContent)) !== strlen($pageContent);
?>
<article class="jtb-page-article">
    <h1><?= esc($pageTitle) ?></h1>
    <?php if ($isRich): ?>
        <div class="jtb-page-body"><?= $pageContent ?></div>
    <?php else: ?>
        <div class="jtb-page-body"><p><?= nl2br(esc($pageContent)) ?></p></div>
    <?php endif; ?>
</article>
