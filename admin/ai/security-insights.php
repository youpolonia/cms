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

if (!function_exists('esc')) {
    function esc($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$errors = [];
$result = null;
$inputs = [
    'url' => '',
    'use_ai' => false
];

$hfConfig = ai_hf_config_load();
$hfConfigured = ai_hf_is_configured($hfConfig);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $inputs['url'] = trim((string)($_POST['url'] ?? ''));
    $inputs['use_ai'] = isset($_POST['use_ai']);

    if (strlen($inputs['url']) > 2000) {
        $errors[] = 'URL is too long (max 2000 characters).';
    }

    if (!empty($inputs['url']) && empty($errors)) {
        $normalizedUrl = $inputs['url'];
        if (stripos($normalizedUrl, 'http://') !== 0 && stripos($normalizedUrl, 'https://') !== 0) {
            $normalizedUrl = 'https://' . $normalizedUrl;
        }

        if (!filter_var($normalizedUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Please provide a valid URL (including domain).';
        } else {
            $ch = curl_init($normalizedUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HEADER => true,
                CURLOPT_USERAGENT => 'CMS-AI Security Insights/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2
            ]);

            $raw = curl_exec($ch);
            $info = curl_getinfo($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($raw === false || empty($info['http_code'])) {
                $errors[] = 'Could not fetch the URL. Please verify that the site is reachable.';
            } else {
                $httpCode = (int)($info['http_code'] ?? 0);
                $totalTime = (float)($info['total_time'] ?? 0.0);
                $contentType = (string)($info['content_type'] ?? '');
                $headerSize = (int)($info['header_size'] ?? 0);

                $rawHeaders = substr($raw, 0, $headerSize);
                $body = substr($raw, $headerSize);
                $bodyPreview = substr($body, 0, 15000);

                $headerLines = preg_split('/\r?\n/', $rawHeaders);
                $securityHeaders = [
                    'strict_transport_security' => null,
                    'content_security_policy' => null,
                    'x_frame_options' => null,
                    'x_content_type_options' => null,
                    'referrer_policy' => null,
                    'permissions_policy' => null
                ];

                foreach ($headerLines as $line) {
                    if (stripos($line, 'HTTP/') === 0) {
                        continue;
                    }
                    $parts = explode(':', $line, 2);
                    if (count($parts) === 2) {
                        $headerName = strtolower(trim($parts[0]));
                        $headerValue = trim($parts[1]);

                        if ($headerName === 'strict-transport-security') {
                            $securityHeaders['strict_transport_security'] = $headerValue;
                        } elseif ($headerName === 'content-security-policy') {
                            $securityHeaders['content_security_policy'] = $headerValue;
                        } elseif ($headerName === 'x-frame-options') {
                            $securityHeaders['x_frame_options'] = $headerValue;
                        } elseif ($headerName === 'x-content-type-options') {
                            $securityHeaders['x_content_type_options'] = $headerValue;
                        } elseif ($headerName === 'referrer-policy') {
                            $securityHeaders['referrer_policy'] = $headerValue;
                        } elseif ($headerName === 'permissions-policy') {
                            $securityHeaders['permissions_policy'] = $headerValue;
                        }
                    }
                }

                $hasTls = (stripos($normalizedUrl, 'https://') === 0);
                $hasHsts = $securityHeaders['strict_transport_security'] !== null;

                $securityScore = 40;
                if ($hasTls) {
                    $securityScore += 10;
                }
                if ($hasHsts) {
                    $securityScore += 10;
                }
                if ($securityHeaders['content_security_policy'] !== null) {
                    $securityScore += 10;
                }
                if ($securityHeaders['x_frame_options'] !== null) {
                    $securityScore += 10;
                }
                if ($securityHeaders['x_content_type_options'] !== null) {
                    $securityScore += 10;
                }
                if ($securityHeaders['referrer_policy'] !== null) {
                    $securityScore += 10;
                }
                $securityScore = min($securityScore, 100);

                $performanceScore = 50;
                if ($totalTime <= 0.5) {
                    $performanceScore += 20;
                } elseif ($totalTime <= 1.0) {
                    $performanceScore += 10;
                }
                if (stripos($contentType, 'text/html') !== false && strlen($body) < 500000) {
                    $performanceScore += 10;
                }
                $performanceScore = min($performanceScore, 100);

                $issues = [];
                if ($hasTls && !$hasHsts) {
                    $issues[] = 'HSTS header is missing.';
                }
                if (!$hasTls) {
                    $issues[] = 'Site does not use HTTPS.';
                }
                if ($securityHeaders['content_security_policy'] === null) {
                    $issues[] = 'Content-Security-Policy header is missing.';
                }
                if ($securityHeaders['x_frame_options'] === null) {
                    $issues[] = 'X-Frame-Options header is missing.';
                }
                if ($securityHeaders['x_content_type_options'] === null) {
                    $issues[] = 'X-Content-Type-Options header is missing.';
                }
                if ($securityHeaders['referrer_policy'] === null) {
                    $issues[] = 'Referrer-Policy header is missing.';
                }
                if ($securityHeaders['permissions_policy'] === null) {
                    $issues[] = 'Permissions-Policy header is missing.';
                }

                $recommendations = [];
                if (!$hasTls) {
                    $recommendations[] = 'Enable HTTPS for secure communications.';
                }
                if ($hasTls && !$hasHsts) {
                    $recommendations[] = 'Add Strict-Transport-Security header to enforce HTTPS.';
                }
                if ($securityHeaders['content_security_policy'] === null) {
                    $recommendations[] = 'Implement a Content-Security-Policy to prevent XSS attacks.';
                }
                if ($securityHeaders['x_frame_options'] === null) {
                    $recommendations[] = 'Add X-Frame-Options header to prevent clickjacking.';
                }
                if ($securityHeaders['x_content_type_options'] === null) {
                    $recommendations[] = 'Add X-Content-Type-Options: nosniff header.';
                }
                if ($securityHeaders['referrer_policy'] === null) {
                    $recommendations[] = 'Define a Referrer-Policy to control referrer information.';
                }

                $aiUsed = false;
                $fallbackUsed = false;

                if ($hfConfigured && $inputs['use_ai']) {
                    $headersText = '';
                    foreach ($securityHeaders as $key => $value) {
                        $displayName = str_replace('_', '-', $key);
                        $displayValue = $value ?? 'missing';
                        $headersText .= "$displayName: $displayValue\n";
                    }

                    $bodySnippet = substr($bodyPreview, 0, 2500);

                    $prompt = "Analyze this HTTP response for security and performance. Respond ONLY with valid JSON in this exact format (no markdown, no prose):\n\n";
                    $prompt .= "{\n";
                    $prompt .= '  "security_score": 0-100,' . "\n";
                    $prompt .= '  "performance_score": 0-100,' . "\n";
                    $prompt .= '  "issues": ["..."],' . "\n";
                    $prompt .= '  "recommendations": ["..."],' . "\n";
                    $prompt .= '  "notes": "..."' . "\n";
                    $prompt .= "}\n\n";
                    $prompt .= "URL: $normalizedUrl\n";
                    $prompt .= "HTTP Status: $httpCode\n";
                    $prompt .= "Total Time: " . number_format($totalTime, 3) . " seconds\n";
                    $prompt .= "Content-Type: $contentType\n\n";
                    $prompt .= "Security Headers:\n$headersText\n";
                    $prompt .= "Body Preview (first 2500 chars):\n$bodySnippet\n";

                    $aiOptions = [
                        'max_new_tokens' => 512,
                        'temperature' => 0.4,
                        'top_p' => 0.9
                    ];

                    $aiResult = ai_hf_infer($hfConfig, $prompt, $aiOptions);

                    if (!empty($aiResult)) {
                        $decoded = json_decode($aiResult, true);
                        if (is_array($decoded) && isset($decoded['security_score']) && isset($decoded['performance_score'])) {
                            $aiUsed = true;
                            $securityScore = (int)$decoded['security_score'];
                            $performanceScore = (int)$decoded['performance_score'];
                            if (isset($decoded['issues']) && is_array($decoded['issues'])) {
                                $issues = $decoded['issues'];
                            }
                            if (isset($decoded['recommendations']) && is_array($decoded['recommendations'])) {
                                $recommendations = $decoded['recommendations'];
                            }
                        } else {
                            $fallbackUsed = true;
                        }
                    } else {
                        $fallbackUsed = true;
                    }
                } else {
                    $fallbackUsed = true;
                }

                $result = [
                    'url' => $normalizedUrl,
                    'http_code' => $httpCode,
                    'total_time' => $totalTime,
                    'content_type' => $contentType,
                    'security_headers' => $securityHeaders,
                    'ai_used' => $aiUsed,
                    'fallback_used' => $fallbackUsed,
                    'security_score' => $securityScore,
                    'performance_score' => $performanceScore,
                    'issues' => $issues,
                    'recommendations' => $recommendations,
                    'body_preview' => $bodyPreview,
                    'raw_headers' => $rawHeaders
                ];
            }
        }
    } elseif (empty($inputs['url'])) {
        $errors[] = 'URL is required.';
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>AI Security &amp; Performance Insights</h1>

        <?php if (!$hfConfigured): ?>
            <div style="background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:12px;margin:16px 0;border-radius:4px;">
                <strong>Notice:</strong> Hugging Face is not configured. The tool will use PHP-only analysis.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px;margin:16px 0;border-radius:4px;">
                <strong>Errors:</strong>
                <ul style="margin:8px 0 0 0;padding-left:20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin:16px 0;">
            <?php csrf_field(); ?>

            <div style="margin-bottom:16px;">
                <label for="url" style="display:block;margin-bottom:6px;font-weight:600;">URL to Analyze:</label>
                <input type="text" id="url" name="url" value="<?= esc($inputs['url']) ?>"
                       style="width:100%;max-width:600px;padding:8px;border:1px solid #ccc;border-radius:4px;"
                       placeholder="https://example.com" required>
            </div>

            <?php if ($hfConfigured): ?>
                <div style="margin-bottom:16px;">
                    <label>
                        <input type="checkbox" name="use_ai" <?= $inputs['use_ai'] ? 'checked' : '' ?>>
                        Use AI (Hugging Face) if available
                    </label>
                </div>
            <?php endif; ?>

            <button type="submit" style="background:#007bff;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
                Analyze
            </button>
        </form>

        <?php if ($result !== null): ?>
            <div style="margin-top:24px;">
                <?php if ($result['ai_used']): ?>
                    <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:12px;margin-bottom:16px;border-radius:4px;">
                        <strong>✓</strong> AI analysis used
                    </div>
                <?php elseif ($result['fallback_used']): ?>
                    <div style="background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:12px;margin-bottom:16px;border-radius:4px;">
                        <strong>ℹ</strong> PHP-only fallback used
                    </div>
                <?php endif; ?>

                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Summary</h2>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;width:200px;">URL</td>
                            <td style="padding:8px;border:1px solid #ddd;word-break:break-all;"><?= esc($result['url']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;">HTTP Status</td>
                            <td style="padding:8px;border:1px solid #ddd;"><?= esc($result['http_code']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;">Total Time</td>
                            <td style="padding:8px;border:1px solid #ddd;"><?= number_format($result['total_time'], 3) ?> seconds</td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;">Content-Type</td>
                            <td style="padding:8px;border:1px solid #ddd;"><?= esc($result['content_type']) ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;">Security Score</td>
                            <td style="padding:8px;border:1px solid #ddd;">
                                <strong><?= esc($result['security_score']) ?>/100</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #ddd;font-weight:600;">Performance Score</td>
                            <td style="padding:8px;border:1px solid #ddd;">
                                <strong><?= esc($result['performance_score']) ?>/100</strong>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (!empty($result['issues'])): ?>
                    <div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;">
                        <h2 style="margin-top:0;">Key Issues</h2>
                        <ul style="margin:0;padding-left:20px;">
                            <?php foreach ($result['issues'] as $issue): ?>
                                <li><?= esc($issue) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['recommendations'])): ?>
                    <div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;">
                        <h2 style="margin-top:0;">Recommendations</h2>
                        <ul style="margin:0;padding-left:20px;">
                            <?php foreach ($result['recommendations'] as $rec): ?>
                                <li><?= esc($rec) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;">
                    <h2 style="margin-top:0;">Security Headers</h2>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="padding:8px;border:1px solid #ddd;text-align:left;background:#f8f9fa;">Header Name</th>
                                <th style="padding:8px;border:1px solid #ddd;text-align:left;background:#f8f9fa;">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result['security_headers'] as $key => $value): ?>
                                <tr>
                                    <td style="padding:8px;border:1px solid #ddd;font-family:monospace;">
                                        <?= esc(str_replace('_', '-', ucwords($key, '_'))) ?>
                                    </td>
                                    <td style="padding:8px;border:1px solid #ddd;font-family:monospace;word-break:break-all;">
                                        <?= $value !== null ? esc($value) : '<em style="color:#999;">Missing</em>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <details style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;margin-bottom:20px;">
                    <summary style="cursor:pointer;font-weight:600;margin-bottom:12px;">Raw Headers</summary>
                    <pre style="background:#f8f9fa;padding:12px;border:1px solid #ddd;border-radius:4px;overflow-x:auto;font-size:13px;"><?= esc($result['raw_headers']) ?></pre>
                </details>

                <details style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:4px;">
                    <summary style="cursor:pointer;font-weight:600;margin-bottom:12px;">Body Preview (truncated to 15,000 chars)</summary>
                    <pre style="background:#f8f9fa;padding:12px;border:1px solid #ddd;border-radius:4px;overflow-x:auto;font-size:13px;max-height:400px;overflow-y:auto;"><?= esc($result['body_preview']) ?></pre>
                </details>
            </div>
        <?php endif; ?>
    </div>
</div>
