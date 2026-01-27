<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/seo.php';

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';

$successMessage = '';
$errors = [];
$settings = seo_get_settings();

// Compute helper values for SEO overview
$canonicalBase = trim((string)($settings['canonical_base_url'] ?? ''));
if ($canonicalBase === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $canonicalBase = $scheme . '://' . $host;
}

$robotsIndex  = (string)($settings['robots_index'] ?? 'index');
$robotsFollow = (string)($settings['robots_follow'] ?? 'follow');

$isNoIndex  = ($robotsIndex === 'noindex');
$isNoFollow = ($robotsFollow === 'nofollow');
$isIndexable = (!$isNoIndex && !$isNoFollow);

$sitemapUrl   = rtrim($canonicalBase, '/') . '/sitemap.php';
$robotsTxtUrl = rtrim($canonicalBase, '/') . '/robots.txt';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Read and sanitize inputs
    $site_name = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
    $meta_description = isset($_POST['meta_description']) ? trim($_POST['meta_description']) : '';
    $meta_keywords = isset($_POST['meta_keywords']) ? trim($_POST['meta_keywords']) : '';
    $robots_index = isset($_POST['robots_index']) ? trim($_POST['robots_index']) : 'index';
    $robots_follow = isset($_POST['robots_follow']) ? trim($_POST['robots_follow']) : 'follow';
    $canonical_base_url = isset($_POST['canonical_base_url']) ? trim($_POST['canonical_base_url']) : '';
    $og_image_url = isset($_POST['og_image_url']) ? trim($_POST['og_image_url']) : '';

    // Validate robots_index
    if (!in_array($robots_index, ['index', 'noindex'], true)) {
        $robots_index = 'index';
    }

    // Validate robots_follow
    if (!in_array($robots_follow, ['follow', 'nofollow'], true)) {
        $robots_follow = 'follow';
    }

    // Validate canonical_base_url
    if ($canonical_base_url !== '' && filter_var($canonical_base_url, FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Canonical Base URL must be a valid URL or left empty.';
    }

    // Validate og_image_url
    if ($og_image_url !== '' && filter_var($og_image_url, FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Default Open Graph Image URL must be a valid URL or left empty.';
    }

    // If no validation errors, save to JSON
    if (empty($errors)) {
        $settings = [
            'site_name'            => $site_name,
            'meta_description'     => $meta_description,
            'meta_keywords'        => $meta_keywords,
            'robots_index'         => $robots_index,
            'robots_follow'        => $robots_follow,
            'canonical_base_url'   => $canonical_base_url,
            'og_image_url'         => $og_image_url,
        ];

        $json = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $result = @file_put_contents($seoConfigPath, $json, LOCK_EX);

        if ($result === false) {
            $errors[] = 'Failed to save SEO settings. Please try again.';
        } else {
            $successMessage = 'SEO settings have been saved.';
        }
    } else {
        // Preserve POST values on validation error
        $settings = [
            'site_name'            => $site_name,
            'meta_description'     => $meta_description,
            'meta_keywords'        => $meta_keywords,
            'robots_index'         => $robots_index,
            'robots_follow'        => $robots_follow,
            'canonical_base_url'   => $canonical_base_url,
            'og_image_url'         => $og_image_url,
        ];
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
    <h1>SEO Settings</h1>
    <p class="muted">Configure default SEO settings for your site.</p>

    <div class="card">
        <h2>SEO Overview</h2>
        <p class="muted">Current SEO configuration status and preview.</p>

        <div>
            <p>
                <strong>Indexable:</strong>
                <?php if ($isIndexable): ?>
                    YES (<?php echo htmlspecialchars($robotsIndex, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($robotsFollow, ENT_QUOTES, 'UTF-8'); ?>)
                <?php else: ?>
                    NO (<?php echo htmlspecialchars($robotsIndex, ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($robotsFollow, ENT_QUOTES, 'UTF-8'); ?>)
                <?php endif; ?>
            </p>

            <p>
                <strong>robots.txt effective rule:</strong>
                <?php if ($isNoIndex): ?>
                    Disallow: /
                <?php else: ?>
                    Allow: /
                <?php endif; ?>
            </p>

            <p>
                <strong>Sitemap URL:</strong>
                <a href="<?php echo htmlspecialchars($sitemapUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                    <?php echo htmlspecialchars($sitemapUrl, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </p>

            <p>
                <strong>robots.txt URL:</strong>
                <a href="<?php echo htmlspecialchars($robotsTxtUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                    <?php echo htmlspecialchars($robotsTxtUrl, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </p>

            <?php
                $configuredBase = trim((string)($settings['canonical_base_url'] ?? ''));
                if ($configuredBase === ''):
            ?>
                <p class="muted" style="color: #c44;">
                    ⚠ Canonical base URL is NOT explicitly configured. Using auto-detected base: <?php echo htmlspecialchars($canonicalBase, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php else: ?>
                <p><strong>Canonical base URL:</strong> <?php echo htmlspecialchars($configuredBase, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="alert success">
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php csrf_field(); ?>

        <!-- Site Identity Section -->
        <div class="card">
            <h2>Site Identity</h2>

            <div class="form-row">
                <label for="site_name">Site Name</label>
                <input type="text" id="site_name" name="site_name" class="form-control" placeholder="Your Site Name" value="<?php echo htmlspecialchars($settings['site_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                <small class="muted">This site name may be used in page titles and social sharing cards.</small>
            </div>

            <div class="form-row">
                <label for="meta_description">Default Meta Description</label>
                <textarea id="meta_description" name="meta_description" class="form-control" rows="3" placeholder="Default description for pages without custom meta descriptions"><?php echo htmlspecialchars($settings['meta_description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                <small class="muted">Used as a fallback &lt;meta name="description"&gt; when no page-specific description is provided.</small>
            </div>

            <div class="form-row">
                <label for="meta_keywords">Default Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3" value="<?php echo htmlspecialchars($settings['meta_keywords'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
        </div>

        <!-- Search Indexing Section -->
        <div class="card">
            <h2>Search Indexing</h2>

            <div class="form-row">
                <label for="robots_index">Robots: Index</label>
                <select id="robots_index" name="robots_index" class="form-control">
                    <option value="index" <?php echo ($settings['robots_index'] === 'index') ? 'selected="selected"' : ''; ?>>Index</option>
                    <option value="noindex" <?php echo ($settings['robots_index'] === 'noindex') ? 'selected="selected"' : ''; ?>>No index</option>
                </select>
            </div>

            <div class="form-row">
                <label for="robots_follow">Robots: Follow</label>
                <select id="robots_follow" name="robots_follow" class="form-control">
                    <option value="follow" <?php echo ($settings['robots_follow'] === 'follow') ? 'selected="selected"' : ''; ?>>Follow</option>
                    <option value="nofollow" <?php echo ($settings['robots_follow'] === 'nofollow') ? 'selected="selected"' : ''; ?>>No follow</option>
                </select>
            </div>
        </div>

        <!-- Canonical & Social Section -->
        <div class="card">
            <h2>Canonical &amp; Social</h2>

            <div class="form-row">
                <label for="canonical_base_url">Canonical Base URL</label>
                <input type="url" id="canonical_base_url" name="canonical_base_url" class="form-control" placeholder="https://example.com" value="<?php echo htmlspecialchars($settings['canonical_base_url'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>

            <div class="form-row">
                <label for="og_image_url">Default Open Graph Image URL</label>
                <input type="url" id="og_image_url" name="og_image_url" class="form-control" placeholder="https://example.com/images/og-default.jpg" value="<?php echo htmlspecialchars($settings['og_image_url'], ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-actions">
            <button type="submit" class="btn primary">Save SEO Settings</button>
        </div>
    </form>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
