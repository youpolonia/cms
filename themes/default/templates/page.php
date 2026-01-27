<?php
/**
 * Default Theme - Page Template
 * Handles both regular pages and TB pages
 * @var array $page Page data
 * @var bool $isPreview Preview mode flag
 */
$title = $page['title'] ?? 'Page';
$isTbPage = !empty($page['is_tb_page']);

if ($isTbPage): ?>
<!-- TB Page - content controls layout, no wrapper -->
<?= $page['content'] ?? '' ?>
<?php else: ?>
<!-- Regular Page with article wrapper -->
<article class="page-content">
    <div class="container">
        <header class="page-header">
            <h1><?= htmlspecialchars($title) ?></h1>
        </header>
        <div class="page-body">
            <?= $page['content'] ?? '' ?>
        </div>
    </div>
</article>

<style>
.page-content { padding: 60px 0; }
.page-content .container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
.page-header { margin-bottom: 40px; }
.page-header h1 { font-size: 2.5rem; font-weight: 700; color: var(--text); }
.page-body { line-height: 1.8; font-size: 1.1rem; color: var(--text); }
.page-body h2 { margin: 40px 0 20px; font-size: 1.75rem; }
.page-body h3 { margin: 32px 0 16px; font-size: 1.35rem; }
.page-body p { margin-bottom: 20px; }
.page-body img { max-width: 100%; height: auto; border-radius: 8px; margin: 24px 0; }
.page-body a { color: var(--primary); }
</style>
<?php endif;
