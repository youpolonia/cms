<?php
/**
 * Core navigation partial template
 * This template is included in the base template
 */

// Get current site ID if multisite is enabled
$currentSiteId = $this->isMultisiteEnabled() ? $this->getCurrentSiteId() : null;

// Define navigation items
$navItems = [
    ['url' => '/', 'label' => 'Home'],
    ['url' => '/page/about', 'label' => 'About'],
    ['url' => '/page/services', 'label' => 'Services'],
    ['url' => '/page/portfolio', 'label' => 'Portfolio'],
    ['url' => '/page/contact', 'label' => 'Contact']
];

// Add site-specific navigation items if available
if ($this->isMultisiteEnabled()) {
    $siteNavItems = $this->siteData('navigation', []);
    if (!empty($siteNavItems)) {
        $navItems = array_merge($navItems, $siteNavItems);
    }
}

?><ul class="main-nav">
    <?php foreach ($navItems as $item): ?>
        <li class="nav-item">
            <a href="<?php echo $item['url']; ?>" class="nav-link">
                <?php echo $item['label'];  ?>
            </a>
        </li>
    <?php endforeach;  ?>    
    <?php if ($this->isMultisiteEnabled()): ?>
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle">
                Sites
            </a>
            <ul class="dropdown-menu">
                <?php 
                // In a real implementation, this would fetch all sites from the SiteManager
                $sites = [
                    ['id' => 'primary', 'name' => 'Primary Site'],
                    ['id' => 'site1', 'name' => 'Site 1'],
                    ['id' => 'site2', 'name' => 'Site 2']
                ];
                
                foreach ($sites as $site): ?>                
                    <li>
                        <a href="/switch-site/<?php echo $site['id']; ?>" class="dropdown-item <?php echo ($currentSiteId === $site['id']) ? 'active' : ''; ?>">
                            <?php echo $site['name'];  ?>
                        </a>
                    </li>
                <?php endforeach;  ?>
            </ul>
        </li>
        <?php endif;  ?>
    <li class="nav-item">
        <a href="/admin" class="nav-link admin-link">
            Admin
        </a>
    </li>
</ul>
