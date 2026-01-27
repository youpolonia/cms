<?php
/**
 * Core navigation partial template
 * Variables available: $navItems, $currentSiteId, $isMultisite, $sites
 */

// Default navigation items if not provided
$navItems = $navItems ?? [
    ['url' => '/', 'label' => 'Home'],
    ['url' => '/page/about', 'label' => 'About'],
    ['url' => '/page/services', 'label' => 'Services'],
    ['url' => '/page/portfolio', 'label' => 'Portfolio'],
    ['url' => '/page/contact', 'label' => 'Contact']
];

// Check if multisite is enabled
$isMultisite = $isMultisite ?? false;
$currentSiteId = $currentSiteId ?? null;

?><ul class="main-nav">
    <?php foreach ($navItems as $item): ?>
        <li class="nav-item">
            <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" class="nav-link">
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </li>
    <?php endforeach; ?>
    <?php if ($isMultisite): ?>
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle">
                Sites
            </a>
            <ul class="dropdown-menu">
                <?php
                // Sites should be passed as a variable
                $sites = $sites ?? [
                    ['id' => 'primary', 'name' => 'Primary Site'],
                    ['id' => 'site1', 'name' => 'Site 1'],
                    ['id' => 'site2', 'name' => 'Site 2']
                ];

                foreach ($sites as $site): ?>
                    <li>
                        <a href="/switch-site/<?= htmlspecialchars($site['id'], ENT_QUOTES, 'UTF-8') ?>" class="dropdown-item <?= ($currentSiteId === $site['id']) ? 'active' : '' ?>">
                            <?= htmlspecialchars($site['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
    <?php endif; ?>
    <li class="nav-item">
        <a href="/admin" class="nav-link admin-link">
            Admin
        </a>
    </li>
</ul>
