<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Forbidden');
}

require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/core/ai_hf.php';

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$results = null;
$usedAI = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $urlOrText = trim($_POST['url_or_text'] ?? '');
    $analysisDepth = $_POST['analysis_depth'] ?? 'standard';
    $language = $_POST['language'] ?? 'en';
    $keyword = trim($_POST['keyword'] ?? '');

    if ($urlOrText === '') {
        $errors[] = 'Content or URL is required';
    }

    if (strlen($urlOrText) > 10000) {
        $errors[] = 'Content exceeds maximum length of 10000 characters';
    }

    if (strlen($keyword) > 255) {
        $errors[] = 'Keyword exceeds maximum length of 255 characters';
    }

    if (!in_array($analysisDepth, ['basic', 'standard', 'deep'])) {
        $analysisDepth = 'standard';
    }

    if (!in_array($language, ['en', 'pl', 'de', 'fr'])) {
        $language = 'en';
    }

    if (empty($errors)) {
        $contentToAnalyze = $urlOrText;

        if (preg_match('#^https?://#i', $urlOrText)) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (compatible; CMS SEO Auditor/1.0)',
                    'follow_location' => 1,
                    'max_redirects' => 3
                ]
            ]);

            $fetchedContent = @file_get_contents($urlOrText, false, $context);

            if ($fetchedContent === false) {
                $errors[] = 'Failed to fetch content from URL';
            } else {
                $contentToAnalyze = strip_tags($fetchedContent);
            }
        }

        if (empty($errors)) {
            $hfConfig = ai_hf_config_load();

            if (ai_hf_is_configured($hfConfig)) {
                $prompt = "Analyze the following content for SEO quality and provide a JSON response with this exact structure:\n";
                $prompt .= "{\n";
                $prompt .= "  \"score\": <number 0-100>,\n";
                $prompt .= "  \"summary\": \"<brief summary>\",\n";
                $prompt .= "  \"keywords\": [\"keyword1\", \"keyword2\", ...],\n";
                $prompt .= "  \"recommendations\": [\"recommendation1\", \"recommendation2\", ...]\n";
                $prompt .= "}\n\n";
                $prompt .= "Analysis depth: " . $analysisDepth . "\n";
                $prompt .= "Language: " . $language . "\n";

                if ($keyword !== '') {
                    $prompt .= "Target keyword: " . $keyword . "\n";
                }

                $prompt .= "\nContent to analyze:\n" . substr($contentToAnalyze, 0, 5000);

                $aiResponse = ai_hf_infer($hfConfig, $prompt, [
                    'max_new_tokens' => 500,
                    'temperature' => 0.7
                ]);

                if ($aiResponse['ok'] && !empty($aiResponse['json'])) {
                    $aiData = $aiResponse['json'];

                    if (is_array($aiData) && isset($aiData[0]) && is_array($aiData[0])) {
                        $generatedText = $aiData[0]['generated_text'] ?? '';
                    } else {
                        $generatedText = $aiResponse['body'] ?? '';
                    }

                    $jsonMatch = [];
                    if (preg_match('/\{[^}]*"score"[^}]*\}/s', $generatedText, $jsonMatch)) {
                        $parsedJson = @json_decode($jsonMatch[0], true);
                        if (is_array($parsedJson)) {
                            $results = [
                                'score' => isset($parsedJson['score']) ? (int)$parsedJson['score'] : 50,
                                'summary' => $parsedJson['summary'] ?? 'AI analysis completed',
                                'keywords' => $parsedJson['keywords'] ?? [],
                                'recommendations' => $parsedJson['recommendations'] ?? []
                            ];
                            $usedAI = true;
                        }
                    }
                }
            }

            if ($results === null) {
                $textLength = strlen($contentToAnalyze);
                $score = min(100, (int)($textLength / 10));

                $summary = substr($contentToAnalyze, 0, 200);
                if (strlen($contentToAnalyze) > 200) {
                    $summary .= '...';
                }

                $words = preg_split('/\s+/', strtolower($contentToAnalyze));
                $wordCounts = [];
                foreach ($words as $word) {
                    $cleanWord = preg_replace('/[^a-z]/', '', $word);
                    if (strlen($cleanWord) > 3) {
                        if (!isset($wordCounts[$cleanWord])) {
                            $wordCounts[$cleanWord] = 0;
                        }
                        $wordCounts[$cleanWord]++;
                    }
                }

                arsort($wordCounts);
                $topKeywords = array_slice(array_keys($wordCounts), 0, 5);

                $recommendations = [
                    'Ensure content is at least 300 words for better SEO performance',
                    'Include relevant keywords naturally throughout the text',
                    'Add meta descriptions and title tags to improve search visibility'
                ];

                $results = [
                    'score' => $score,
                    'summary' => $summary,
                    'keywords' => $topKeywords,
                    'recommendations' => $recommendations
                ];
                $usedAI = false;
            }
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <h1 class="mt-4">AI Content Insights</h1>
        <p class="text-muted">SEO Auditor - Analyze content or URL for SEO quality</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Validation Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($results !== null): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Analysis Results</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h3>SEO Score: <?= esc($results['score']) ?>/100</h3>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar <?= $results['score'] >= 70 ? 'bg-success' : ($results['score'] >= 40 ? 'bg-warning' : 'bg-danger') ?>"
                                     role="progressbar"
                                     style="width: <?= esc($results['score']) ?>%"
                                     aria-valuenow="<?= esc($results['score']) ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?= esc($results['score']) ?>%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Analysis Method:</strong>
                                <?php if ($usedAI): ?>
                                    <span class="badge bg-success">AI Used</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Fallback Used</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5>Summary</h5>
                        <p><?= esc($results['summary']) ?></p>
                    </div>

                    <?php if (!empty($results['keywords'])): ?>
                        <div class="mb-3">
                            <h5>Keywords</h5>
                            <div>
                                <?php foreach ($results['keywords'] as $kw): ?>
                                    <span class="badge bg-info text-dark me-1"><?= esc($kw) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($results['recommendations'])): ?>
                        <div class="mb-3">
                            <h5>Recommendations</h5>
                            <ul>
                                <?php foreach ($results['recommendations'] as $rec): ?>
                                    <li><?= esc($rec) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Analyze Content</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <?php csrf_field(); ?>

                    <div class="mb-3">
                        <label for="url_or_text" class="form-label">Content or URL <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="url_or_text"
                                  name="url_or_text"
                                  rows="8"
                                  required
                                  placeholder="Enter URL (http://... or https://...) or paste text content directly"><?= isset($_POST['url_or_text']) ? esc($_POST['url_or_text']) : '' ?></textarea>
                        <small class="form-text text-muted">Maximum 10,000 characters</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="analysis_depth" class="form-label">Analysis Depth</label>
                            <select class="form-control" id="analysis_depth" name="analysis_depth">
                                <option value="basic" <?= (isset($_POST['analysis_depth']) && $_POST['analysis_depth'] === 'basic') ? 'selected' : '' ?>>Basic</option>
                                <option value="standard" <?= (!isset($_POST['analysis_depth']) || $_POST['analysis_depth'] === 'standard') ? 'selected' : '' ?>>Standard</option>
                                <option value="deep" <?= (isset($_POST['analysis_depth']) && $_POST['analysis_depth'] === 'deep') ? 'selected' : '' ?>>Deep</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="language" class="form-label">Language</label>
                            <select class="form-control" id="language" name="language">
                                <option value="en" <?= (!isset($_POST['language']) || $_POST['language'] === 'en') ? 'selected' : '' ?>>English</option>
                                <option value="pl" <?= (isset($_POST['language']) && $_POST['language'] === 'pl') ? 'selected' : '' ?>>Polish</option>
                                <option value="de" <?= (isset($_POST['language']) && $_POST['language'] === 'de') ? 'selected' : '' ?>>German</option>
                                <option value="fr" <?= (isset($_POST['language']) && $_POST['language'] === 'fr') ? 'selected' : '' ?>>French</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="keyword" class="form-label">Target Keyword (optional)</label>
                            <input type="text"
                                   class="form-control"
                                   id="keyword"
                                   name="keyword"
                                   maxlength="255"
                                   placeholder="e.g., SEO optimization"
                                   value="<?= isset($_POST['keyword']) ? esc($_POST['keyword']) : '' ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Analyze Content</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
