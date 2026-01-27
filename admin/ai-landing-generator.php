<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

// Load AI landing page generator
require_once __DIR__ . '/../core/ai_landing.php';

// Controller: Handle form submission
$spec = null;
$savedPath = null;
$error = null;
$successMessage = null;
$createdPage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : 'generate';

    // Collect form fields
    $goal = isset($_POST['goal']) ? trim((string)$_POST['goal']) : '';
    $audience = isset($_POST['audience']) ? trim((string)$_POST['audience']) : '';
    $offer = isset($_POST['offer']) ? trim((string)$_POST['offer']) : '';
    $language = isset($_POST['language']) ? trim((string)$_POST['language']) : 'en';
    $primaryKeyword = isset($_POST['primary_keyword']) ? trim((string)$_POST['primary_keyword']) : '';
    $tone = isset($_POST['tone']) ? trim((string)$_POST['tone']) : 'professional';

    // Validate required fields
    if ($goal === '' && $offer === '') {
        $error = 'Please provide at least a goal or offer description.';
    } else {
        // Handle different actions
        try {
            if ($action === 'generate_and_create_page') {
                // Generate spec and create CMS page
                $result = ai_landing_generate_and_import([
                    'goal' => $goal,
                    'audience' => $audience,
                    'offer' => $offer,
                    'language' => $language,
                    'primary_keyword' => $primaryKeyword,
                    'tone' => $tone,
                ]);

                $spec = $result['spec'];
                $importResult = $result['import'];

                if ($importResult['ok'] === true) {
                    $createdPage = [
                        'page_id' => $importResult['page_id'],
                        'slug' => $importResult['slug'],
                        'title' => $importResult['title'],
                    ];
                    $successMessage = 'AI landing page created successfully as draft!';
                } else {
                    $error = 'Failed to create CMS page: ' . ($importResult['message'] ?? 'Unknown error');
                }
            } else {
                // Generate spec only
                $spec = ai_landing_generate_spec([
                    'goal' => $goal,
                    'audience' => $audience,
                    'offer' => $offer,
                    'language' => $language,
                    'primary_keyword' => $primaryKeyword,
                    'tone' => $tone,
                ]);

                // Save if requested
                if ($action === 'generate_and_save' && $spec !== null) {
                    $storageDir = CMS_ROOT . '/cms_storage/ai-landing';
                    $savedPath = ai_landing_save_spec($spec, $storageDir);

                    if ($savedPath === null) {
                        $error = 'Failed to save landing page draft. Please check file permissions.';
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[AI_LANDING_ADMIN] Exception: ' . $e->getMessage());
            $error = 'An error occurred while generating the landing page. Please try again.';
        }
    }
}

// Helper function for HTML escaping
function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Load header and navigation
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<div class="container" style="max-width:1200px;margin:24px auto;padding:0 16px">
    <h1>AI Landing Page Generator</h1>
    <p style="color:#666;margin-bottom:24px">
        Generate a complete landing page specification using AI. Describe your goal, audience, and offer,
        then preview or save the generated structure as a draft.
    </p>

    <?php if ($error !== null): ?>
        <div style="padding:12px;background:#fee;border:1px solid #c33;color:#c33;margin-bottom:16px;border-radius:4px">
            <strong>Error:</strong> <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($successMessage !== null): ?>
        <div style="padding:12px;background:#efe;border:1px solid #3c3;color:#060;margin-bottom:16px;border-radius:4px">
            <strong>Success!</strong> <?= esc($successMessage) ?>
            <?php if ($createdPage !== null): ?>
                <div style="margin-top:8px;padding:8px;background:#fff;border:1px solid #3c3;border-radius:4px">
                    <p style="margin:0 0 4px 0"><strong>Page ID:</strong> <?= esc($createdPage['page_id']) ?></p>
                    <p style="margin:0 0 4px 0"><strong>Title:</strong> <?= esc($createdPage['title']) ?></p>
                    <p style="margin:0"><strong>Slug:</strong> <code><?= esc($createdPage['slug']) ?></code></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($savedPath !== null): ?>
        <div style="padding:12px;background:#efe;border:1px solid #3c3;color:#060;margin-bottom:16px;border-radius:4px">
            <strong>Success!</strong> Landing page draft saved to: <code><?= esc($savedPath) ?></code>
        </div>
    <?php endif; ?>

    <form method="post" style="background:#f9f9f9;padding:20px;border:1px solid #ddd;border-radius:6px;margin-bottom:32px">
        <?php csrf_field(); ?>

        <div style="margin-bottom:16px">
            <label style="display:block;font-weight:bold;margin-bottom:6px">Landing Page Goal</label>
            <textarea
                name="goal"
                rows="3"
                placeholder="e.g., Lead generation for our CMS platform"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;font-family:inherit"
            ><?= isset($_POST['goal']) ? esc($_POST['goal']) : '' ?></textarea>
            <small style="color:#666">Describe what you want this landing page to achieve.</small>
        </div>

        <div style="margin-bottom:16px">
            <label style="display:block;font-weight:bold;margin-bottom:6px">Target Audience</label>
            <input
                type="text"
                name="audience"
                value="<?= isset($_POST['audience']) ? esc($_POST['audience']) : '' ?>"
                placeholder="e.g., Small business owners, developers, marketers"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
            />
            <small style="color:#666">Who is this landing page for?</small>
        </div>

        <div style="margin-bottom:16px">
            <label style="display:block;font-weight:bold;margin-bottom:6px">Main Offer/Product</label>
            <textarea
                name="offer"
                rows="3"
                placeholder="e.g., Professional CMS with AI-powered content generation"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;font-family:inherit"
            ><?= isset($_POST['offer']) ? esc($_POST['offer']) : '' ?></textarea>
            <small style="color:#666">What are you offering or selling?</small>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Language</label>
                <select
                    name="language"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                >
                    <option value="en" <?= (!isset($_POST['language']) || $_POST['language'] === 'en') ? 'selected' : '' ?>>English</option>
                    <option value="pl" <?= (isset($_POST['language']) && $_POST['language'] === 'pl') ? 'selected' : '' ?>>Polish</option>
                    <option value="es" <?= (isset($_POST['language']) && $_POST['language'] === 'es') ? 'selected' : '' ?>>Spanish</option>
                    <option value="fr" <?= (isset($_POST['language']) && $_POST['language'] === 'fr') ? 'selected' : '' ?>>French</option>
                    <option value="de" <?= (isset($_POST['language']) && $_POST['language'] === 'de') ? 'selected' : '' ?>>German</option>
                </select>
            </div>

            <div>
                <label style="display:block;font-weight:bold;margin-bottom:6px">Tone</label>
                <select
                    name="tone"
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
                >
                    <option value="professional" <?= (!isset($_POST['tone']) || $_POST['tone'] === 'professional') ? 'selected' : '' ?>>Professional</option>
                    <option value="friendly" <?= (isset($_POST['tone']) && $_POST['tone'] === 'friendly') ? 'selected' : '' ?>>Friendly</option>
                    <option value="formal" <?= (isset($_POST['tone']) && $_POST['tone'] === 'formal') ? 'selected' : '' ?>>Formal</option>
                    <option value="casual" <?= (isset($_POST['tone']) && $_POST['tone'] === 'casual') ? 'selected' : '' ?>>Casual</option>
                    <option value="enthusiastic" <?= (isset($_POST['tone']) && $_POST['tone'] === 'enthusiastic') ? 'selected' : '' ?>>Enthusiastic</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:20px">
            <label style="display:block;font-weight:bold;margin-bottom:6px">Primary Keyword (Optional)</label>
            <input
                type="text"
                name="primary_keyword"
                value="<?= isset($_POST['primary_keyword']) ? esc($_POST['primary_keyword']) : '' ?>"
                placeholder="e.g., content management system"
                style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px"
            />
            <small style="color:#666">Main SEO keyword to target.</small>
        </div>

        <div style="display:flex;gap:12px">
            <button
                type="submit"
                name="action"
                value="generate"
                style="padding:10px 20px;background:#0066cc;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold"
            >
                Generate Landing Spec
            </button>
            <button
                type="submit"
                name="action"
                value="generate_and_save"
                style="padding:10px 20px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold"
            >
                Generate &amp; Save Draft
            </button>
            <button
                type="submit"
                name="action"
                value="generate_and_create_page"
                style="padding:10px 20px;background:#6f42c1;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold"
            >
                Generate &amp; Create CMS Page (Draft)
            </button>
        </div>
    </form>

    <?php if ($spec !== null): ?>
        <div style="background:#fff;padding:24px;border:1px solid #ddd;border-radius:6px;margin-bottom:32px">
            <h2 style="margin-top:0">Generated Landing Page Preview</h2>

            <!-- Meta Information -->
            <div style="background:#f0f8ff;padding:16px;margin-bottom:20px;border-left:4px solid #0066cc">
                <h3 style="margin-top:0;color:#0066cc">Meta Information</h3>
                <p><strong>Title:</strong> <?= esc($spec['meta']['title']) ?></p>
                <p><strong>Slug:</strong> <code><?= esc($spec['meta']['slug']) ?></code></p>
                <p><strong>Meta Title:</strong> <?= esc($spec['meta']['meta_title']) ?></p>
                <p style="margin-bottom:0"><strong>Meta Description:</strong> <?= esc($spec['meta']['meta_description']) ?></p>
            </div>

            <!-- Hero Section -->
            <div style="background:#fff3cd;padding:16px;margin-bottom:20px;border-left:4px solid #ffc107">
                <h3 style="margin-top:0;color:#856404">Hero Section</h3>
                <p><strong>Headline:</strong> <?= esc($spec['hero']['headline']) ?></p>
                <p><strong>Subheadline:</strong> <?= esc($spec['hero']['subheadline']) ?></p>
                <p style="margin-bottom:0"><strong>Hero Image Prompt:</strong> <?= esc($spec['hero']['hero_image_prompt']) ?></p>
            </div>

            <!-- Content Sections -->
            <?php if (!empty($spec['sections'])): ?>
                <div style="background:#d4edda;padding:16px;margin-bottom:20px;border-left:4px solid #28a745">
                    <h3 style="margin-top:0;color:#155724">Content Sections (<?= count($spec['sections']) ?>)</h3>
                    <?php foreach ($spec['sections'] as $index => $section): ?>
                        <div style="background:#fff;padding:12px;margin-bottom:12px;border:1px solid #c3e6cb;border-radius:4px">
                            <p style="margin:0 0 8px 0">
                                <strong>Section <?= $index + 1 ?>:</strong>
                                <span style="display:inline-block;padding:2px 8px;background:#28a745;color:#fff;border-radius:3px;font-size:12px">
                                    <?= esc($section['type']) ?>
                                </span>
                            </p>
                            <p><strong>Heading:</strong> <?= esc($section['heading']) ?></p>
                            <p><strong>Body:</strong> <?= esc($section['body']) ?></p>
                            <?php if ($section['cta_label'] !== null): ?>
                                <p><strong>CTA Label:</strong> <?= esc($section['cta_label']) ?></p>
                            <?php endif; ?>
                            <?php if ($section['cta_url_placeholder'] !== null): ?>
                                <p style="margin-bottom:0"><strong>CTA URL:</strong> <?= esc($section['cta_url_placeholder']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- FAQ Section -->
            <?php if (!empty($spec['faq'])): ?>
                <div style="background:#e7f3ff;padding:16px;margin-bottom:20px;border-left:4px solid #0066cc">
                    <h3 style="margin-top:0;color:#004085">FAQ (<?= count($spec['faq']) ?>)</h3>
                    <?php foreach ($spec['faq'] as $index => $item): ?>
                        <div style="background:#fff;padding:12px;margin-bottom:12px;border:1px solid #b8daff;border-radius:4px">
                            <p style="margin:0 0 6px 0"><strong>Q<?= $index + 1 ?>:</strong> <?= esc($item['question']) ?></p>
                            <p style="margin:0"><strong>A:</strong> <?= esc($item['answer']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (isset($spec['faq'])): ?>
                <div style="background:#e7f3ff;padding:16px;margin-bottom:20px;border-left:4px solid #0066cc">
                    <h3 style="margin-top:0;color:#004085">FAQ</h3>
                    <p style="margin:0;color:#666">No FAQ entries generated.</p>
                </div>
            <?php endif; ?>

            <!-- JSON Preview -->
            <div style="background:#f8f9fa;padding:16px;border:1px solid #ddd;border-radius:4px">
                <h3 style="margin-top:0">JSON Specification</h3>
                <pre style="background:#fff;padding:12px;border:1px solid #ddd;border-radius:4px;overflow-x:auto;font-size:13px;line-height:1.5"><?= esc(json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php';
