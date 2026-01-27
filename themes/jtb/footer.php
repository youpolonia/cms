<?php
/**
 * JTB Theme - Fallback Footer
 * Used when no JTB footer template is defined
 *
 * @package JTB Theme
 */

defined('CMS_ROOT') or die('Direct access not allowed');

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
?>
<footer class="jtb-default-footer" role="contentinfo">
    <div class="container">
        <div class="footer-content">
            <div class="footer-brand">
                <p class="footer-logo"><?= htmlspecialchars($siteName) ?></p>
                <p class="footer-tagline">Built with Jessie Theme Builder</p>
            </div>

            <nav class="footer-nav" aria-label="Footer navigation">
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="/page/about">About</a></li>
                    <li><a href="/page/contact">Contact</a></li>
                </ul>
            </nav>
        </div>

        <div class="footer-bottom">
            <p class="copyright">&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All rights reserved.</p>
            <p class="powered-by">Powered by <a href="https://jessie-cms.com" target="_blank" rel="noopener">Jessie CMS</a> with <strong>JTB Theme Builder</strong></p>
        </div>
    </div>
</footer>
