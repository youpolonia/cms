<?php
/**
 * Page Template — AI Generated
 * Smart rendering: rich AI content (starts with <style, <section, <!--rich-->) renders full-width.
 * Simple HTML content renders in container-narrow prose wrapper.
 */
$_pageContent = $page['content'] ?? '';
$_contentTrimmed = ltrim($_pageContent);
$_isRichContent = (
    str_starts_with($_contentTrimmed, '<style') ||
    str_starts_with($_contentTrimmed, '<section') ||
    str_starts_with($_contentTrimmed, '<!--rich-->')
);

// Rich AI content includes its own hero section, so skip the default hero
if (!$_isRichContent):
?>
<section class="page-hero"<?php if (!empty($page['featured_image'])): ?> style="background:url(<?= esc($page['featured_image']) ?>) center/cover no-repeat"<?php endif; ?>>
    <?php if (!empty($page['featured_image'])): ?>
    <div class="page-hero-overlay"></div>
    <?php endif; ?>
    <div class="container">
        <h1 class="page-hero-title" data-page-id="<?= (int)($page['id'] ?? 0) ?>" data-page-field="title"><?= esc($page['title']) ?></h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span><?= esc($page['title']) ?></span>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <div class="prose" data-page-id="<?= (int)($page['id'] ?? 0) ?>" data-page-field="content">
            <?= $_pageContent ?>
        </div>
    </div>
</section>
<?php else: ?>
<div data-page-id="<?= (int)($page['id'] ?? 0) ?>" data-page-field="content">
<?= $_pageContent ?>
</div>
<?php endif; ?>