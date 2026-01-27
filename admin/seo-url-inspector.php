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

require_once __DIR__ . '/../core/seo.php';
$settings = seo_get_settings();
$canonicalBase = rtrim($settings['canonical_base_url'] ?? '', '/');
$robotsIndex = ($settings['robots_index'] ?? 'index') === 'noindex' ? 'noindex' : 'index';
$robotsFollow = ($settings['robots_follow'] ?? 'follow') === 'nofollow' ? 'nofollow' : 'follow';

// Get URL parameter if provided
$inspectUrl = isset($_GET['url']) ? trim($_GET['url']) : '';
$inputUrl = $inspectUrl;
$hasInput = ($inputUrl !== '');

$isAbsolute = false;
$isRelative = false;
$isInternal = false;
$suggestedCanonical = '';

if ($hasInput) {
    $isAbsolute = preg_match('~^https?://~i', $inputUrl) === 1;
    $isRelative = !$isAbsolute && str_starts_with($inputUrl, '/');

    if ($canonicalBase !== '') {
        if ($isAbsolute) {
            $isInternal = stripos($inputUrl, $canonicalBase) === 0;
            $suggestedCanonical = $inputUrl;
        } elseif ($isRelative) {
            $isInternal = true;
            $suggestedCanonical = $canonicalBase . $inputUrl;
        }
    }
}

$robotsContent = $robotsIndex . ',' . $robotsFollow;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
    <h1>URL Inspector</h1>
    <p class="muted">Tool under construction. This will analyse SEO signals for any URL.</p>

    <div class="card">
        <form method="get" action="">
            <div class="form-row">
                <label for="url">Enter URL to inspect</label>
                <input type="url" id="url" name="url" class="form-control" placeholder="Enter URL to inspect" value="<?php echo htmlspecialchars($inspectUrl, ENT_QUOTES, 'UTF-8'); ?>" />
            </div>
            <div class="form-actions">
                <button type="submit" class="btn primary">Inspect URL</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Analysis</h2>
        <p class="muted">Computed hints based on your SEO settings.</p>

        <?php if ($hasInput): ?>
            <dl class="seo-inspector">
                <dt>Input URL</dt>
                <dd><?php echo htmlspecialchars($inputUrl, ENT_QUOTES, 'UTF-8'); ?></dd>

                <dt>URL type</dt>
                <dd><?php echo $isAbsolute ? 'absolute' : ($isRelative ? 'relative' : 'unknown'); ?></dd>

                <dt>Internal?</dt>
                <dd>
                    <?php
                    if ($canonicalBase === '') {
                        echo 'unknown (canonical_base_url not configured)';
                    } else {
                        echo $isInternal ? 'yes' : 'no';
                    }
                    ?>
                </dd>

                <dt>Suggested canonical</dt>
                <dd>
                    <?php
                    echo $suggestedCanonical !== ''
                        ? htmlspecialchars($suggestedCanonical, ENT_QUOTES, 'UTF-8')
                        : 'not available';
                    ?>
                </dd>

                <dt>Suggested robots meta</dt>
                <dd><code><?php echo htmlspecialchars($robotsContent, ENT_QUOTES, 'UTF-8'); ?></code></dd>
            </dl>

            <pre class="seo-snippet">&lt;link rel="canonical" href="<?php echo htmlspecialchars($suggestedCanonical !== '' ? $suggestedCanonical : '#', ENT_QUOTES, 'UTF-8'); ?>"&gt;
&lt;meta name="robots" content="<?php echo htmlspecialchars($robotsContent, ENT_QUOTES, 'UTF-8'); ?>"&gt;</pre>
        <?php else: ?>
            <p class="muted">Enter a URL above to see analysis based on your current SEO settings.</p>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
