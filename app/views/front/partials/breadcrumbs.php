<?php
/**
 * Breadcrumb Navigation
 * Required: $breadcrumbs (array of ['label' => string, 'url' => string|null])
 * Last item is current page (no link)
 */
$breadcrumbs = $breadcrumbs ?? [];
if (empty($breadcrumbs)) return;
?>
<nav aria-label="Breadcrumb" class="breadcrumbs" style="font-size:0.85rem;color:var(--text-secondary,#64748b);margin-bottom:20px;">
    <ol style="list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:4px;align-items:center;" itemscope itemtype="https://schema.org/BreadcrumbList">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" style="display:inline-flex;align-items:center;gap:4px;">
                <?php if ($i > 0): ?>
                    <span style="color:var(--border,#cbd5e1);">›</span>
                <?php endif; ?>
                <?php if ($i < count($breadcrumbs) - 1 && !empty($crumb['url'])): ?>
                    <a href="<?= h($crumb['url']) ?>" itemprop="item" style="color:var(--accent,#6366f1);text-decoration:none;">
                        <span itemprop="name"><?= h($crumb['label']) ?></span>
                    </a>
                <?php else: ?>
                    <span itemprop="name" style="color:var(--text,#334155);font-weight:500;"><?= h($crumb['label']) ?></span>
                <?php endif; ?>
                <meta itemprop="position" content="<?= $i + 1 ?>">
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
