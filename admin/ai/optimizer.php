<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied.');
}
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';

if (!function_exists('esc')) {
    function esc($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$analysis = null;
$ai_used = false;
$fallback_used = false;
$ai_error = null;
$source_summary = null;
$inputs = [
    'mode' => 'url',
    'url' => '',
    'content' => '',
    'seo' => true,
    'performance' => true,
    'ux' => true,
    'conversion' => true
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['mode'] = trim((string)($_POST['mode'] ?? 'url'));
    $inputs['url'] = trim((string)($_POST['url'] ?? ''));
    $inputs['content'] = trim((string)($_POST['content'] ?? ''));
    $inputs['seo'] = isset($_POST['seo']);
    $inputs['performance'] = isset($_POST['performance']);
    $inputs['ux'] = isset($_POST['ux']);
    $inputs['conversion'] = isset($_POST['conversion']);

    if (!in_array($inputs['mode'], ['url', 'paste'])) {
        $errors[] = 'Invalid mode selected';
    }

    if ($inputs['mode'] === 'url') {
        if ($inputs['url'] === '') {
            $errors[] = 'URL is required when analyzing by URL';
        } elseif (strlen($inputs['url']) > 2048) {
            $errors[] = 'URL is too long (maximum 2048 characters)';
        } elseif (!preg_match('/^https?:\/\//i', $inputs['url'])) {
            $errors[] = 'URL must start with http:// or https://';
        }
    }

    if ($inputs['mode'] === 'paste') {
        if ($inputs['content'] === '') {
            $errors[] = 'Content is required when analyzing by paste';
        } elseif (strlen($inputs['content']) > 20000) {
            $errors[] = 'Content is too long (maximum 20000 characters)';
        }
    }

    if (!$inputs['seo'] && !$inputs['performance'] && !$inputs['ux'] && !$inputs['conversion']) {
        $errors[] = 'At least one focus area must be selected';
    }

    if (empty($errors)) {
        $page_content = null;

        if ($inputs['mode'] === 'url') {
            $ch = curl_init($inputs['url']);
            if ($ch === false) {
                $errors[] = 'Failed to initialize request';
            } else {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; CMS-Optimizer/1.0)');

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                    $errors[] = 'Failed to fetch URL';
                } else {
                    $page_content = substr($response, 0, 20000);
                    $source_summary = 'URL: ' . substr($inputs['url'], 0, 100);
                }
            }
        } else {
            $page_content = substr($inputs['content'], 0, 20000);
            $source_summary = 'Pasted content: ' . substr($page_content, 0, 100) . '...';
        }

        if (empty($errors) && ($page_content === null || trim($page_content) === '')) {
            $errors[] = 'No content to analyze';
        }
    }

    if (empty($errors) && $page_content !== null) {
        $hfConfig = ai_hf_config_load();
        $hfOk = ai_hf_is_configured($hfConfig);

        if ($hfOk) {
            $focusAreas = [];
            if ($inputs['seo']) $focusAreas[] = 'SEO';
            if ($inputs['performance']) $focusAreas[] = 'Performance';
            if ($inputs['ux']) $focusAreas[] = 'UX';
            if ($inputs['conversion']) $focusAreas[] = 'Conversion';

            $promptParts = ['You are a website optimization expert. Analyze the following HTML/text snippet and provide structured recommendations.'];
            $promptParts[] = 'Focus areas: ' . implode(', ', $focusAreas);
            $promptParts[] = 'Content to analyze:';
            $promptParts[] = substr($page_content, 0, 5000);
            $promptParts[] = 'Respond ONLY with valid minified JSON in this exact schema:';
            $promptParts[] = '{"summary":"Brief overview","seo":{"score":0-100,"issues":["..."],"recommendations":["..."]},"ux":{"score":0-100,"issues":["..."],"recommendations":["..."]},"performance":{"score":0-100,"issues":["..."],"recommendations":["..."]},"conversion":{"score":0-100,"issues":["..."],"recommendations":["..."]}}';
            $promptParts[] = 'Requirements: Scores must be integers 0-100. Issues and recommendations must be short, actionable bullet points (2-5 per category). Inactive focus areas should have null or empty arrays. No explanations, no markdown, no extra text.';

            $prompt = implode("\n\n", $promptParts);

            $options = [
                'max_new_tokens' => 512,
                'temperature' => 0.5,
                'top_p' => 0.9
            ];

            $result = ai_hf_infer($hfConfig, $prompt, $options);

            if (!$result['ok'] || $result['status'] < 200 || $result['status'] >= 300) {
                $ai_error = 'AI analysis failed, using fallback';
            } else {
                $aiData = null;
                if (is_array($result['json'])) {
                    $aiData = $result['json'];
                } else {
                    $decoded = @json_decode($result['body'], true);
                    if (is_array($decoded)) {
                        $aiData = $decoded;
                    }
                }

                if ($aiData === null || !is_array($aiData)) {
                    $ai_error = 'AI response was invalid, using fallback';
                } else {
                    $analysis = [
                        'summary' => isset($aiData['summary']) ? trim(substr((string)$aiData['summary'], 0, 500)) : 'Analysis completed',
                        'seo' => null,
                        'ux' => null,
                        'performance' => null,
                        'conversion' => null
                    ];

                    if ($inputs['seo'] && isset($aiData['seo']) && is_array($aiData['seo'])) {
                        $analysis['seo'] = [
                            'score' => isset($aiData['seo']['score']) ? max(0, min(100, (int)$aiData['seo']['score'])) : 50,
                            'issues' => isset($aiData['seo']['issues']) && is_array($aiData['seo']['issues']) ? array_slice($aiData['seo']['issues'], 0, 5) : [],
                            'recommendations' => isset($aiData['seo']['recommendations']) && is_array($aiData['seo']['recommendations']) ? array_slice($aiData['seo']['recommendations'], 0, 5) : []
                        ];
                    }

                    if ($inputs['ux'] && isset($aiData['ux']) && is_array($aiData['ux'])) {
                        $analysis['ux'] = [
                            'score' => isset($aiData['ux']['score']) ? max(0, min(100, (int)$aiData['ux']['score'])) : 50,
                            'issues' => isset($aiData['ux']['issues']) && is_array($aiData['ux']['issues']) ? array_slice($aiData['ux']['issues'], 0, 5) : [],
                            'recommendations' => isset($aiData['ux']['recommendations']) && is_array($aiData['ux']['recommendations']) ? array_slice($aiData['ux']['recommendations'], 0, 5) : []
                        ];
                    }

                    if ($inputs['performance'] && isset($aiData['performance']) && is_array($aiData['performance'])) {
                        $analysis['performance'] = [
                            'score' => isset($aiData['performance']['score']) ? max(0, min(100, (int)$aiData['performance']['score'])) : 50,
                            'issues' => isset($aiData['performance']['issues']) && is_array($aiData['performance']['issues']) ? array_slice($aiData['performance']['issues'], 0, 5) : [],
                            'recommendations' => isset($aiData['performance']['recommendations']) && is_array($aiData['performance']['recommendations']) ? array_slice($aiData['performance']['recommendations'], 0, 5) : []
                        ];
                    }

                    if ($inputs['conversion'] && isset($aiData['conversion']) && is_array($aiData['conversion'])) {
                        $analysis['conversion'] = [
                            'score' => isset($aiData['conversion']['score']) ? max(0, min(100, (int)$aiData['conversion']['score'])) : 50,
                            'issues' => isset($aiData['conversion']['issues']) && is_array($aiData['conversion']['issues']) ? array_slice($aiData['conversion']['issues'], 0, 5) : [],
                            'recommendations' => isset($aiData['conversion']['recommendations']) && is_array($aiData['conversion']['recommendations']) ? array_slice($aiData['conversion']['recommendations'], 0, 5) : []
                        ];
                    }

                    $ai_used = true;
                }
            }
        }

        if (!$hfOk || $ai_error !== null) {
            $fallback_used = true;
            $ai_used = false;

            $contentLen = strlen($page_content);
            $hasTitle = stripos($page_content, '<title') !== false;
            $hasMetaDesc = stripos($page_content, 'name="description"') !== false || stripos($page_content, "name='description'") !== false;
            $hasH1 = stripos($page_content, '<h1') !== false;
            $hasH2 = stripos($page_content, '<h2') !== false;
            $hasP = stripos($page_content, '<p') !== false;
            $hasList = stripos($page_content, '<ul') !== false || stripos($page_content, '<ol') !== false;
            $hasCTAWords = preg_match('/\b(buy|contact|sign up|subscribe|register|join|get started|learn more)\b/i', $page_content);

            $summary = 'Analyzed content (' . ($contentLen < 1000 ? 'short' : ($contentLen < 5000 ? 'medium' : 'long')) . ' length)';
            if ($hasTitle || $hasH1) {
                $summary .= ' with structural elements';
            }

            $analysis = [
                'summary' => $summary,
                'seo' => null,
                'ux' => null,
                'performance' => null,
                'conversion' => null
            ];

            if ($inputs['seo']) {
                $seoScore = 50;
                $seoIssues = [];
                $seoRecs = [];

                if (!$hasTitle) {
                    $seoScore -= 20;
                    $seoIssues[] = 'Missing <title> tag';
                    $seoRecs[] = 'Add a descriptive, unique <title> tag (50-60 characters)';
                } else {
                    $seoScore += 10;
                }

                if (!$hasMetaDesc) {
                    $seoScore -= 15;
                    $seoIssues[] = 'Missing meta description';
                    $seoRecs[] = 'Add a meta description tag (150-160 characters)';
                } else {
                    $seoScore += 10;
                }

                if (!$hasH1) {
                    $seoScore -= 10;
                    $seoIssues[] = 'No <h1> heading found';
                    $seoRecs[] = 'Add exactly one <h1> heading with primary keyword';
                } else {
                    $seoScore += 10;
                }

                if ($contentLen < 300) {
                    $seoScore -= 10;
                    $seoIssues[] = 'Content may be too short';
                    $seoRecs[] = 'Expand content to at least 300 words for better indexing';
                } else {
                    $seoScore += 10;
                }

                if (empty($seoRecs)) {
                    $seoRecs[] = 'Continue monitoring keyword density and readability';
                }

                $analysis['seo'] = [
                    'score' => max(0, min(100, $seoScore)),
                    'issues' => $seoIssues,
                    'recommendations' => $seoRecs
                ];
            }

            if ($inputs['performance']) {
                $perfScore = 70;
                $perfIssues = [];
                $perfRecs = [];

                if ($contentLen > 10000) {
                    $perfScore -= 20;
                    $perfIssues[] = 'Page content is large';
                    $perfRecs[] = 'Consider compressing images and minifying CSS/JS';
                }

                if ($contentLen > 5000) {
                    $perfScore -= 10;
                    $perfIssues[] = 'Content size may affect load time';
                    $perfRecs[] = 'Enable gzip compression on the server';
                }

                if (empty($perfIssues)) {
                    $perfRecs[] = 'Content size is reasonable; monitor actual load times';
                }

                $analysis['performance'] = [
                    'score' => max(0, min(100, $perfScore)),
                    'issues' => $perfIssues,
                    'recommendations' => $perfRecs
                ];
            }

            if ($inputs['ux']) {
                $uxScore = 60;
                $uxIssues = [];
                $uxRecs = [];

                if (!$hasH1 && !$hasH2) {
                    $uxScore -= 15;
                    $uxIssues[] = 'No clear heading hierarchy';
                    $uxRecs[] = 'Use H1, H2, H3 tags to structure content';
                } else {
                    $uxScore += 10;
                }

                if (!$hasP) {
                    $uxScore -= 10;
                    $uxIssues[] = 'Limited paragraph content';
                    $uxRecs[] = 'Add descriptive paragraphs for better readability';
                } else {
                    $uxScore += 10;
                }

                if (!$hasList) {
                    $uxScore -= 5;
                    $uxRecs[] = 'Consider using lists (ul/ol) for scannable content';
                } else {
                    $uxScore += 10;
                }

                if (empty($uxIssues)) {
                    $uxIssues[] = 'Content structure appears reasonable';
                }

                $analysis['ux'] = [
                    'score' => max(0, min(100, $uxScore)),
                    'issues' => $uxIssues,
                    'recommendations' => $uxRecs
                ];
            }

            if ($inputs['conversion']) {
                $convScore = 50;
                $convIssues = [];
                $convRecs = [];

                if (!$hasCTAWords) {
                    $convScore -= 20;
                    $convIssues[] = 'No clear call-to-action detected';
                    $convRecs[] = 'Add prominent CTA buttons (e.g., "Get Started", "Contact Us")';
                } else {
                    $convScore += 20;
                    $convRecs[] = 'CTA language detected; ensure buttons are prominent';
                }

                $convRecs[] = 'Test different CTA placements and wording';
                $convRecs[] = 'Use contrasting colors for CTA buttons';

                $analysis['conversion'] = [
                    'score' => max(0, min(100, $convScore)),
                    'issues' => $convIssues,
                    'recommendations' => $convRecs
                ];
            }
        }
    }
}

?>
<main class="container">
    <h1>AI Website Optimizer</h1>

    <?php if (!empty($errors)): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
            <strong>Error:</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($ai_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
            <strong>✓ AI Analysis</strong> — Analysis completed using Hugging Face AI.
        </div>
    <?php elseif ($fallback_used): ?>
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; color: #856404;">
            <strong>Notice:</strong> Using heuristic analysis. For AI-powered insights, configure Hugging Face in <a href="/admin/hf-settings.php" style="color: #856404; text-decoration: underline;">settings</a>.
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 800px;">
        <?php csrf_field(); ?>

        <div style="margin-bottom: 1rem;">
            <label><strong>Analysis Mode</strong></label>
            <div style="margin-top: 0.5rem;">
                <label style="display: inline-block; margin-right: 1.5rem;">
                    <input type="radio" name="mode" value="url"<?= $inputs['mode'] === 'url' ? ' checked' : '' ?> onchange="document.getElementById('url').disabled = !this.checked; document.getElementById('content').disabled = this.checked;">
                    Analyze URL
                </label>
                <label style="display: inline-block;">
                    <input type="radio" name="mode" value="paste"<?= $inputs['mode'] === 'paste' ? ' checked' : '' ?> onchange="document.getElementById('content').disabled = !this.checked; document.getElementById('url').disabled = this.checked;">
                    Paste Content
                </label>
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="url"><strong>URL</strong></label>
            <input type="text" id="url" name="url" value="<?= esc($inputs['url']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"<?= $inputs['mode'] !== 'url' ? ' disabled' : '' ?> maxlength="2048" placeholder="https://example.com">
            <small style="color: #666;">Enter the URL to analyze (used when "Analyze URL" is selected)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="content"><strong>Content</strong></label>
            <textarea id="content" name="content" rows="8" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"<?= $inputs['mode'] !== 'paste' ? ' disabled' : '' ?> maxlength="20000" placeholder="Paste HTML or text content here..."><?= esc($inputs['content']) ?></textarea>
            <small style="color: #666;">Paste HTML or text content (used when "Paste Content" is selected, max 20000 characters)</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <label><strong>Focus Areas</strong> (select at least one)</label>
            <div style="margin-top: 0.5rem;">
                <label style="display: block; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="seo"<?= $inputs['seo'] ? ' checked' : '' ?>>
                    SEO &amp; Content
                </label>
                <label style="display: block; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="performance"<?= $inputs['performance'] ? ' checked' : '' ?>>
                    Performance
                </label>
                <label style="display: block; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="ux"<?= $inputs['ux'] ? ' checked' : '' ?>>
                    UX &amp; Readability
                </label>
                <label style="display: block; margin-bottom: 0.25rem;">
                    <input type="checkbox" name="conversion"<?= $inputs['conversion'] ? ' checked' : '' ?>>
                    Conversion &amp; CTA
                </label>
            </div>
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Analyze Page</button>
    </form>

    <?php if ($analysis !== null): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; border: 1px solid #ccc; background: #f9f9f9; border-radius: 4px;">
            <h2>Analysis Results</h2>

            <?php if ($source_summary !== null): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 4px;">
                    <strong>Source:</strong> <?= esc($source_summary) ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 0.5rem;">Summary</h3>
                <p style="margin: 0;"><?= esc($analysis['summary']) ?></p>
            </div>

            <?php
            $areas = [
                'seo' => 'SEO &amp; Content',
                'performance' => 'Performance',
                'ux' => 'UX &amp; Readability',
                'conversion' => 'Conversion &amp; CTA'
            ];

            foreach ($areas as $key => $label):
                if ($analysis[$key] === null):
            ?>
                    <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f0f0f0; border: 1px solid #d0d0d0; border-radius: 4px;">
                        <h3 style="margin: 0 0 0.5rem 0; color: #666;"><?= $label ?></h3>
                        <p style="margin: 0; color: #666; font-style: italic;">Not analyzed (focus disabled)</p>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #ccc; border-radius: 4px; background: white;">
                        <h3 style="margin: 0 0 0.5rem 0;"><?= $label ?></h3>
                        <div style="margin-bottom: 1rem;">
                            <strong>Score:</strong>
                            <span style="font-size: 1.5rem; font-weight: bold; color: <?= $analysis[$key]['score'] >= 70 ? '#28a745' : ($analysis[$key]['score'] >= 40 ? '#ffc107' : '#dc3545') ?>">
                                <?= (int)$analysis[$key]['score'] ?>/100
                            </span>
                        </div>

                        <?php if (!empty($analysis[$key]['issues'])): ?>
                            <div style="margin-bottom: 1rem;">
                                <strong>Issues:</strong>
                                <ul style="margin: 0.5rem 0 0 1.5rem;">
                                    <?php foreach ($analysis[$key]['issues'] as $issue): ?>
                                        <li><?= esc($issue) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($analysis[$key]['recommendations'])): ?>
                            <div>
                                <strong>Recommendations:</strong>
                                <ul style="margin: 0.5rem 0 0 1.5rem;">
                                    <?php foreach ($analysis[$key]['recommendations'] as $rec): ?>
                                        <li><?= esc($rec) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php
                endif;
            endforeach;
            ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
