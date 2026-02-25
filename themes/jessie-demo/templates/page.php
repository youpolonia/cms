<?php
/**
 * Generic page template — with custom content file support
 * If a matching PHP file exists in ../content/{slug}.php, it's included instead
 */
$pageTitle = $page['title'] ?? $title ?? 'Page';
$pageSlug = $page['slug'] ?? '';
$pageContent = $page['content'] ?? $content ?? '';

// Check for custom content file (e.g., content/demo-features.php)
$contentFile = __DIR__ . '/../content/' . basename($pageSlug) . '.php';
if ($pageSlug && file_exists($contentFile)) {
    require $contentFile;
    return;
}

// Default: render DB content
$isRich = strlen(strip_tags($pageContent)) !== strlen($pageContent);
?>
<div class="jd-page">
    <h1><?= esc($pageTitle) ?></h1>
    <?php if ($isRich): ?>
        <div class="jd-page-content"><?= $pageContent ?></div>
    <?php else: ?>
        <div class="jd-page-content"><p><?= nl2br(esc($pageContent)) ?></p></div>
    <?php endif; ?>
</div>
