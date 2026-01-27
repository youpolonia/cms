<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

cms_session_start('admin');

require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

csrf_boot();

// Load SEO settings for preview
$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = file_exists($seoConfigPath)
    ? json_decode(@file_get_contents($seoConfigPath), true)
    : [];

if (!is_array($settings)) {
    $settings = [];
}

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<main class="container">
    <h1>Robots.txt Preview</h1>
    <p class="muted">This file is generated dynamically using your SEO settings.</p>

    <div class="card">
        <h2>Preview</h2>
        <pre class="robots-preview">User-agent: *
<?php echo ($settings['robots_index'] ?? 'index') === 'noindex' ? "Disallow: /\n" : "Allow: /\n"; ?>
<?php if (($settings['robots_follow'] ?? 'follow') === 'nofollow') echo "Crawl-delay: 999\n"; ?>
<?php
$base = rtrim($settings['canonical_base_url'] ?? '', '/');
if ($base !== '') {
    echo 'Sitemap: ' . esc($base . '/sitemap.xml') . "\n";
}
?>
</pre>
    </div>

    <div class="card">
        <h2>Endpoint</h2>
        <p>Robots.txt is served from:</p>
        <code>/robots.txt</code>
        <p class="muted">Configure SEO settings in the main SEO settings page to control robots.txt output.</p>
    </div>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
