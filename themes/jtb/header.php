<?php
/**
 * JTB Theme - Fallback Header
 * Used when no JTB header template is defined
 *
 * @package JTB Theme
 */

defined('CMS_ROOT') or die('Direct access not allowed');

$themeUrl = '/themes/jtb';

// Get site name from settings
$siteName = 'Jessie CMS';
try {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = 'site_name' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($result && !empty($result['value'])) {
        $siteName = $result['value'];
    }
} catch (\Exception $e) {
    // Keep default
}

// Get navigation menu
$menuItems = [];
try {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT title, slug FROM posts WHERE status = 'published' AND type = 'page' ORDER BY created_at ASC LIMIT 5");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    // Empty menu
}
?>
<header class="jtb-default-header" role="banner">
    <div class="container">
        <div class="header-inner">
            <!-- Logo -->
            <a href="/" class="logo" aria-label="<?= htmlspecialchars($siteName) ?> - Home">
                <img src="/public/assets/images/jessie-logo.svg" alt="<?= htmlspecialchars($siteName) ?>" width="40" height="40">
                <span class="site-name"><?= htmlspecialchars($siteName) ?></span>
            </a>

            <!-- Main Navigation -->
            <nav class="nav-main" role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="/" class="<?= (($_SERVER['REQUEST_URI'] ?? '/') === '/' || ($_SERVER['REQUEST_URI'] ?? '/') === '/home') ? 'active' : '' ?>">Home</a></li>
                    <?php foreach ($menuItems as $item): ?>
                    <li><a href="/page/<?= htmlspecialchars($item['slug']) ?>" class="<?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/page/'. $item['slug']) !== false) ? 'active' : '' ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="/blog" class="<?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/blog') !== false) ? 'active' : '' ?>">Blog</a></li>
                </ul>
            </nav>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" aria-label="Toggle mobile menu" aria-expanded="false" aria-controls="mobile-menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>

        <!-- Mobile Menu -->
        <nav id="mobile-menu" class="nav-mobile" role="navigation" aria-label="Mobile navigation" hidden>
            <ul>
                <li><a href="/">Home</a></li>
                <?php foreach ($menuItems as $item): ?>
                <li><a href="/page/<?= htmlspecialchars($item['slug']) ?>"><?= htmlspecialchars($item['title']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="/blog">Blog</a></li>
            </ul>
        </nav>
    </div>
</header>
