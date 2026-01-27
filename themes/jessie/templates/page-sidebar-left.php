<?php
/**
 * Jessie Theme - Left Sidebar Page Template
 * Left sidebar (250px) + main content area
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */

// Get sidebar content if available
$sidebarContent = $page['sidebar_content'] ?? '';
$sidebarWidgets = $page['sidebar_widgets'] ?? [];
?>
<article class="sidebar-left-page">
    <div class="container">
        <div class="page-layout">
            <aside class="page-sidebar">
                <?php if (!empty($sidebarContent)): ?>
                <div class="sidebar-content">
                    <?= $sidebarContent ?>
                </div>
                <?php else: ?>
                <div class="sidebar-widget">
                    <h3>Navigation</h3>
                    <ul class="sidebar-nav">
                        <li><a href="/">Home</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/page/about">About</a></li>
                        <li><a href="/page/contact">Contact</a></li>
                    </ul>
                </div>
                <?php if (!empty($page['related_pages'])): ?>
                <div class="sidebar-widget">
                    <h3>Related Pages</h3>
                    <ul class="sidebar-nav">
                        <?php foreach ($page['related_pages'] as $relPage): ?>
                        <li><a href="/page/<?= htmlspecialchars($relPage['slug']) ?>"><?= htmlspecialchars($relPage['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </aside>

            <main class="page-main">
                <header class="page-header">
                    <?php if (!empty($page['title'])): ?>
                    <h1><?= htmlspecialchars($page['title']) ?></h1>
                    <?php endif; ?>
                </header>

                <?php if (!empty($page['featured_image'])): ?>
                <img src="<?= htmlspecialchars($page['featured_image']) ?>" alt="<?= htmlspecialchars($page['title'] ?? '') ?>" class="featured-img">
                <?php endif; ?>

                <div class="content-body">
                    <?= $page['content'] ?? $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>
</article>
