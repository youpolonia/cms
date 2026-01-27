<?php
$pageTitle = $page['title'] ?? 'Page';
require_once __DIR__ . '/layouts/header.php';

// Get recent pages for sidebar
$pdo = db();
$stmt = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY updated_at DESC LIMIT 5");
$recentPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>
<article class="single-page sidebar-left-page">
    <div class="page-container">
        <aside class="page-sidebar">
            <div class="sidebar-widget">
                <h3>Recent Pages</h3>
                <ul>
                    <?php foreach ($recentPages as $p): ?>
                    <li><a href="/<?= esc($p['slug']) ?>"><?= esc($p['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
        <main class="page-main">
            <header class="page-header">
                <h1><?= esc($page['title']) ?></h1>
            </header>
            <?php if (!empty($page['featured_image'])): ?>
            <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" class="featured-img">
            <?php endif; ?>
            <div class="page-content">
                <?= $page['content'] ?? '' ?>
            </div>
        </main>
    </div>
</article>

<style>
.sidebar-left-page { padding: 120px 24px 80px; }
.sidebar-left-page .page-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 300px 1fr; gap: 40px; }
.sidebar-left-page .page-main { min-width: 0; }
.sidebar-left-page .page-header h1 { font-size: clamp(1.75rem, 3vw, 2.5rem); margin-bottom: 24px; }
.sidebar-left-page .featured-img { width: 100%; height: auto; border-radius: var(--radius-md); margin-bottom: 30px; }
.sidebar-left-page .page-content { line-height: 1.8; font-size: 1.05rem; }
.sidebar-left-page .page-content h2 { margin: 40px 0 20px; font-size: 1.75rem; }
.sidebar-left-page .page-content p { margin-bottom: 20px; }
.sidebar-left-page .page-content a { color: var(--accent-primary); }
.sidebar-left-page .page-sidebar { position: sticky; top: 100px; height: fit-content; }
.sidebar-left-page .sidebar-widget { background: var(--surface-secondary); border-radius: var(--radius-md); padding: 24px; }
.sidebar-left-page .sidebar-widget h3 { font-size: 1.1rem; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-primary); }
.sidebar-left-page .sidebar-widget ul { list-style: none; padding: 0; margin: 0; }
.sidebar-left-page .sidebar-widget li { padding: 8px 0; border-bottom: 1px solid var(--border-primary); }
.sidebar-left-page .sidebar-widget li:last-child { border-bottom: none; }
.sidebar-left-page .sidebar-widget a { color: var(--text-primary); text-decoration: none; }
.sidebar-left-page .sidebar-widget a:hover { color: var(--accent-primary); }
@media (max-width: 900px) { .sidebar-left-page .page-container { grid-template-columns: 1fr; } .sidebar-left-page .page-sidebar { position: static; order: 2; } }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
