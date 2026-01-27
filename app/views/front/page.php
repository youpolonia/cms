<?php
/**
 * Page View - Content Only
 * Rendered INSIDE themes/default/layout.php by render_with_theme()
 * NO header/footer here - layout.php handles that
 */
$pageTitle = $page['title'] ?? 'Page';
$isTbPage = !empty($page['is_tb_page']);

if ($isTbPage): ?>
    <!-- TB Page - content controls layout -->
    <?= $page['content'] ?? '' ?>
<?php else: ?>
    <!-- Regular Page with article wrapper -->
    <article class="single-page">
        <div class="container-narrow">
            <header class="page-header">
                <h1><?= esc($page['title']) ?></h1>
            </header>
            <div class="page-content">
                <?= $page['content'] ?? '' ?>
            </div>
        </div>
    </article>

    <style>
    .container-narrow { max-width: 800px; margin: 0 auto; padding: 0 24px; }
    .single-page { padding: 60px 0 80px; }
    .page-header { text-align: center; margin-bottom: 40px; }
    .page-header h1 { font-size: clamp(2rem, 4vw, 3rem); color: var(--text); }
    .page-content { line-height: 1.8; font-size: 1.05rem; color: var(--text); }
    .page-content h2 { margin: 40px 0 20px; font-size: 1.75rem; color: var(--text); }
    .page-content p { margin-bottom: 20px; }
    .page-content a { color: var(--primary); text-decoration: underline; }
    .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 24px 0; }
    </style>
<?php endif; ?>
