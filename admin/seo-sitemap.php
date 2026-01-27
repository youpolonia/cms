<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/session_boot.php';

cms_session_start('admin');
csrf_boot('admin');

function cms_require_admin_role() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = [];
if (file_exists($seoConfigPath)) {
    $json = file_get_contents($seoConfigPath);
    $settings = json_decode($json, true) ?? [];
}

$base = rtrim($settings['canonical_base_url'] ?? '', '/');

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1>Sitemap.xml Preview</h1>
            <p class="text-muted">Dynamic sitemap generator preview</p>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <?php if (empty($base)): ?>
                <div class="alert alert-warning">
                    <strong>Canonical base URL not configured.</strong>
                    <p>Please configure the canonical base URL in SEO settings to generate a sitemap.</p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Sitemap Preview</h5>
                        <p class="mb-0 text-muted">Public URL: <a href="/public/sitemap.xml.php" target="_blank">/public/sitemap.xml.php</a></p>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3" style="overflow-x: auto;">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"&gt;
  &lt;url&gt;
    &lt;loc&gt;<?php echo esc($base); ?>/&lt;/loc&gt;
  &lt;/url&gt;
  &lt;url&gt;
    &lt;loc&gt;<?php echo esc($base); ?>/articles&lt;/loc&gt;
  &lt;/url&gt;
  &lt;url&gt;
    &lt;loc&gt;<?php echo esc($base); ?>/pages&lt;/loc&gt;
  &lt;/url&gt;
&lt;/urlset&gt;</pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
