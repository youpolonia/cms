<?php
/**
 * AI Website Optimizer
 * One-click website health analysis and auto-fix
 *
 * Scans all pages and provides:
 * - SEO health score (0-100)
 * - Issue detection (titles, meta, ALT tags, headings, links)
 * - Prioritized recommendations
 * - Auto-fix capabilities
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_hf.php';

// Storage for scan results
define('OPTIMIZER_STORAGE_DIR', CMS_ROOT . '/cms_storage/optimizer');
define('OPTIMIZER_RESULTS_FILE', OPTIMIZER_STORAGE_DIR . '/latest_scan.json');

/**
 * Issue severity levels
 */
define('ISSUE_CRITICAL', 'critical');
define('ISSUE_WARNING', 'warning');
define('ISSUE_INFO', 'info');

/**
 * Ensure storage directory exists
 */
function ai_optimizer_ensure_storage(): bool
{
    if (!is_dir(OPTIMIZER_STORAGE_DIR)) {
        return mkdir(OPTIMIZER_STORAGE_DIR, 0755, true);
    }
    return true;
}

/**
 * Get all pages from database
 *
 * @return array Pages with id, title, slug, content, meta_title, meta_description
 */
function ai_optimizer_get_pages(): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("
            SELECT id, title, slug, content, meta_title, meta_description, status, updated_at
            FROM pages
            WHERE status = 'published'
            ORDER BY id ASC
            LIMIT 500
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('[AI_OPTIMIZER] Database error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Analyze a single page for SEO issues
 *
 * @param array $page Page data
 * @return array Analysis results with issues and score
 */
function ai_optimizer_analyze_page(array $page): array
{
    $issues = [];
    $score = 100;

    $title = trim($page['title'] ?? '');
    $metaTitle = trim($page['meta_title'] ?? '');
    $metaDesc = trim($page['meta_description'] ?? '');
    $content = $page['content'] ?? '';
    $slug = $page['slug'] ?? '';

    // Strip HTML for text analysis
    $textContent = strip_tags($content);
    $wordCount = str_word_count($textContent);

    // ========================================
    // TITLE TAG ANALYSIS
    // ========================================
    $effectiveTitle = !empty($metaTitle) ? $metaTitle : $title;
    $titleLength = mb_strlen($effectiveTitle);

    if (empty($effectiveTitle)) {
        $issues[] = [
            'type' => 'title',
            'severity' => ISSUE_CRITICAL,
            'message' => 'Missing page title',
            'fix_available' => false,
        ];
        $score -= 15;
    } elseif ($titleLength < 30) {
        $issues[] = [
            'type' => 'title',
            'severity' => ISSUE_WARNING,
            'message' => "Title too short ({$titleLength} chars, recommended: 50-60)",
            'current' => $effectiveTitle,
            'fix_available' => true,
            'fix_type' => 'expand_title',
        ];
        $score -= 5;
    } elseif ($titleLength > 60) {
        $issues[] = [
            'type' => 'title',
            'severity' => ISSUE_WARNING,
            'message' => "Title too long ({$titleLength} chars, recommended: 50-60)",
            'current' => $effectiveTitle,
            'fix_available' => true,
            'fix_type' => 'shorten_title',
        ];
        $score -= 5;
    }

    // ========================================
    // META DESCRIPTION ANALYSIS
    // ========================================
    $metaDescLength = mb_strlen($metaDesc);

    if (empty($metaDesc)) {
        $issues[] = [
            'type' => 'meta_description',
            'severity' => ISSUE_CRITICAL,
            'message' => 'Missing meta description',
            'fix_available' => true,
            'fix_type' => 'generate_meta',
        ];
        $score -= 15;
    } elseif ($metaDescLength < 120) {
        $issues[] = [
            'type' => 'meta_description',
            'severity' => ISSUE_WARNING,
            'message' => "Meta description too short ({$metaDescLength} chars, recommended: 150-160)",
            'current' => $metaDesc,
            'fix_available' => true,
            'fix_type' => 'expand_meta',
        ];
        $score -= 5;
    } elseif ($metaDescLength > 160) {
        $issues[] = [
            'type' => 'meta_description',
            'severity' => ISSUE_WARNING,
            'message' => "Meta description too long ({$metaDescLength} chars, recommended: 150-160)",
            'current' => $metaDesc,
            'fix_available' => true,
            'fix_type' => 'shorten_meta',
        ];
        $score -= 3;
    }

    // ========================================
    // HEADING STRUCTURE ANALYSIS
    // ========================================
    preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $content, $h1Matches);
    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $h2Matches);
    preg_match_all('/<h3[^>]*>(.*?)<\/h3>/is', $content, $h3Matches);

    $h1Count = count($h1Matches[0]);
    $h2Count = count($h2Matches[0]);
    $h3Count = count($h3Matches[0]);
    $totalHeadings = $h1Count + $h2Count + $h3Count;

    if ($h1Count === 0) {
        $issues[] = [
            'type' => 'headings',
            'severity' => ISSUE_CRITICAL,
            'message' => 'Missing H1 heading',
            'fix_available' => true,
            'fix_type' => 'add_h1',
        ];
        $score -= 10;
    } elseif ($h1Count > 1) {
        $issues[] = [
            'type' => 'headings',
            'severity' => ISSUE_WARNING,
            'message' => "Multiple H1 headings ({$h1Count} found, should be 1)",
            'fix_available' => false,
        ];
        $score -= 5;
    }

    if ($h2Count === 0 && $wordCount > 300) {
        $issues[] = [
            'type' => 'headings',
            'severity' => ISSUE_WARNING,
            'message' => 'No H2 headings found in long content',
            'fix_available' => false,
        ];
        $score -= 5;
    }

    // ========================================
    // IMAGE ALT TAG ANALYSIS
    // ========================================
    preg_match_all('/<img[^>]*>/i', $content, $imgMatches);
    $imageCount = count($imgMatches[0]);
    $missingAlt = 0;
    $emptyAlt = 0;
    $imagesWithIssues = [];

    foreach ($imgMatches[0] as $img) {
        $hasSrc = preg_match('/src=["\']([^"\']+)["\']/', $img, $srcMatch);
        $hasAlt = preg_match('/alt=["\']([^"\']*)["\']/', $img, $altMatch);

        $src = $hasSrc ? $srcMatch[1] : '';
        $alt = $hasAlt ? $altMatch[1] : null;

        if ($alt === null) {
            $missingAlt++;
            $imagesWithIssues[] = ['src' => $src, 'issue' => 'missing_alt'];
        } elseif (trim($alt) === '') {
            $emptyAlt++;
            $imagesWithIssues[] = ['src' => $src, 'issue' => 'empty_alt'];
        }
    }

    if ($missingAlt > 0) {
        $issues[] = [
            'type' => 'images',
            'severity' => ISSUE_CRITICAL,
            'message' => "{$missingAlt} image(s) missing ALT attribute",
            'images' => array_filter($imagesWithIssues, fn($i) => $i['issue'] === 'missing_alt'),
            'fix_available' => true,
            'fix_type' => 'generate_alt',
        ];
        $score -= min(15, $missingAlt * 3);
    }

    if ($emptyAlt > 0) {
        $issues[] = [
            'type' => 'images',
            'severity' => ISSUE_WARNING,
            'message' => "{$emptyAlt} image(s) with empty ALT attribute",
            'images' => array_filter($imagesWithIssues, fn($i) => $i['issue'] === 'empty_alt'),
            'fix_available' => true,
            'fix_type' => 'generate_alt',
        ];
        $score -= min(10, $emptyAlt * 2);
    }

    // ========================================
    // CONTENT LENGTH ANALYSIS
    // ========================================
    if ($wordCount < 100) {
        $issues[] = [
            'type' => 'content',
            'severity' => ISSUE_WARNING,
            'message' => "Thin content ({$wordCount} words, recommended: 300+)",
            'fix_available' => false,
        ];
        $score -= 10;
    } elseif ($wordCount < 300) {
        $issues[] = [
            'type' => 'content',
            'severity' => ISSUE_INFO,
            'message' => "Short content ({$wordCount} words, recommended: 500+)",
            'fix_available' => false,
        ];
        $score -= 3;
    }

    // ========================================
    // INTERNAL LINKS ANALYSIS
    // ========================================
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $linkMatches);
    $internalLinks = 0;
    $externalLinks = 0;

    foreach ($linkMatches[1] as $href) {
        if (strpos($href, 'http') === 0 && strpos($href, $_SERVER['HTTP_HOST'] ?? '') === false) {
            $externalLinks++;
        } else {
            $internalLinks++;
        }
    }

    if ($internalLinks === 0 && $wordCount > 300) {
        $issues[] = [
            'type' => 'links',
            'severity' => ISSUE_WARNING,
            'message' => 'No internal links found',
            'fix_available' => false,
        ];
        $score -= 5;
    }

    // ========================================
    // URL/SLUG ANALYSIS
    // ========================================
    if (!empty($slug)) {
        if (preg_match('/[A-Z]/', $slug)) {
            $issues[] = [
                'type' => 'url',
                'severity' => ISSUE_WARNING,
                'message' => 'URL contains uppercase letters',
                'current' => $slug,
                'fix_available' => false,
            ];
            $score -= 3;
        }

        if (preg_match('/[_\s]/', $slug)) {
            $issues[] = [
                'type' => 'url',
                'severity' => ISSUE_INFO,
                'message' => 'URL contains underscores or spaces (use hyphens)',
                'current' => $slug,
                'fix_available' => false,
            ];
            $score -= 2;
        }

        if (mb_strlen($slug) > 75) {
            $issues[] = [
                'type' => 'url',
                'severity' => ISSUE_INFO,
                'message' => 'URL is very long (' . mb_strlen($slug) . ' chars)',
                'current' => $slug,
                'fix_available' => false,
            ];
            $score -= 2;
        }
    }

    // Ensure score stays in range
    $score = max(0, min(100, $score));

    return [
        'page_id' => $page['id'],
        'title' => $title,
        'slug' => $slug,
        'score' => $score,
        'issues' => $issues,
        'stats' => [
            'word_count' => $wordCount,
            'image_count' => $imageCount,
            'heading_count' => $totalHeadings,
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
        ],
    ];
}

/**
 * Run full website scan
 *
 * @return array Complete scan results
 */
function ai_optimizer_run_scan(): array
{
    $startTime = microtime(true);
    $pages = ai_optimizer_get_pages();

    if (empty($pages)) {
        return [
            'ok' => false,
            'error' => 'No pages found to analyze',
        ];
    }

    $results = [];
    $totalScore = 0;
    $issuesBySeverity = [
        ISSUE_CRITICAL => 0,
        ISSUE_WARNING => 0,
        ISSUE_INFO => 0,
    ];
    $issuesByType = [];

    foreach ($pages as $page) {
        $analysis = ai_optimizer_analyze_page($page);
        $results[] = $analysis;
        $totalScore += $analysis['score'];

        foreach ($analysis['issues'] as $issue) {
            $issuesBySeverity[$issue['severity']]++;
            $type = $issue['type'];
            if (!isset($issuesByType[$type])) {
                $issuesByType[$type] = 0;
            }
            $issuesByType[$type]++;
        }
    }

    $pageCount = count($pages);
    $avgScore = round($totalScore / $pageCount);
    $scanTime = round(microtime(true) - $startTime, 2);

    // Calculate health grade
    $grade = ai_optimizer_get_grade($avgScore);

    // Generate top recommendations
    $recommendations = ai_optimizer_generate_recommendations($results, $issuesByType);

    $scanData = [
        'ok' => true,
        'scanned_at' => gmdate('Y-m-d H:i:s'),
        'scan_time_seconds' => $scanTime,
        'summary' => [
            'pages_scanned' => $pageCount,
            'average_score' => $avgScore,
            'grade' => $grade,
            'total_issues' => array_sum($issuesBySeverity),
            'critical_issues' => $issuesBySeverity[ISSUE_CRITICAL],
            'warnings' => $issuesBySeverity[ISSUE_WARNING],
            'info' => $issuesBySeverity[ISSUE_INFO],
        ],
        'issues_by_type' => $issuesByType,
        'recommendations' => $recommendations,
        'pages' => $results,
    ];

    // Save results
    ai_optimizer_save_results($scanData);

    return $scanData;
}

/**
 * Get grade based on score
 */
function ai_optimizer_get_grade(int $score): array
{
    if ($score >= 90) {
        return ['letter' => 'A+', 'label' => 'Excellent', 'color' => 'success'];
    } elseif ($score >= 80) {
        return ['letter' => 'A', 'label' => 'Very Good', 'color' => 'success'];
    } elseif ($score >= 70) {
        return ['letter' => 'B', 'label' => 'Good', 'color' => 'primary'];
    } elseif ($score >= 60) {
        return ['letter' => 'C', 'label' => 'Needs Work', 'color' => 'warning'];
    } elseif ($score >= 50) {
        return ['letter' => 'D', 'label' => 'Poor', 'color' => 'warning'];
    } else {
        return ['letter' => 'F', 'label' => 'Critical', 'color' => 'danger'];
    }
}

/**
 * Generate prioritized recommendations
 */
function ai_optimizer_generate_recommendations(array $pageResults, array $issuesByType): array
{
    $recommendations = [];

    // Critical: Missing meta descriptions
    $missingMeta = 0;
    foreach ($pageResults as $page) {
        foreach ($page['issues'] as $issue) {
            if ($issue['type'] === 'meta_description' && strpos($issue['message'], 'Missing') !== false) {
                $missingMeta++;
            }
        }
    }
    if ($missingMeta > 0) {
        $recommendations[] = [
            'priority' => 'critical',
            'type' => 'meta_description',
            'title' => 'Add Missing Meta Descriptions',
            'description' => "{$missingMeta} page(s) have no meta description. This hurts CTR in search results.",
            'action' => 'Generate meta descriptions using AI',
            'fix_available' => true,
            'affected_count' => $missingMeta,
        ];
    }

    // Critical: Missing ALT tags
    if (isset($issuesByType['images']) && $issuesByType['images'] > 0) {
        $recommendations[] = [
            'priority' => 'critical',
            'type' => 'images',
            'title' => 'Fix Image ALT Tags',
            'description' => 'Images without ALT tags hurt accessibility and SEO.',
            'action' => 'Generate ALT tags using AI',
            'fix_available' => true,
            'affected_count' => $issuesByType['images'],
        ];
    }

    // High: Missing H1
    $missingH1 = 0;
    foreach ($pageResults as $page) {
        foreach ($page['issues'] as $issue) {
            if ($issue['type'] === 'headings' && strpos($issue['message'], 'Missing H1') !== false) {
                $missingH1++;
            }
        }
    }
    if ($missingH1 > 0) {
        $recommendations[] = [
            'priority' => 'high',
            'type' => 'headings',
            'title' => 'Add H1 Headings',
            'description' => "{$missingH1} page(s) are missing the main H1 heading.",
            'action' => 'Add H1 heading to each page',
            'fix_available' => true,
            'affected_count' => $missingH1,
        ];
    }

    // Medium: Title issues
    if (isset($issuesByType['title']) && $issuesByType['title'] > 0) {
        $recommendations[] = [
            'priority' => 'medium',
            'type' => 'title',
            'title' => 'Optimize Page Titles',
            'description' => 'Some page titles are too short or too long for optimal SEO.',
            'action' => 'Adjust titles to 50-60 characters',
            'fix_available' => true,
            'affected_count' => $issuesByType['title'],
        ];
    }

    // Medium: Thin content
    $thinContent = 0;
    foreach ($pageResults as $page) {
        if ($page['stats']['word_count'] < 300) {
            $thinContent++;
        }
    }
    if ($thinContent > 0) {
        $recommendations[] = [
            'priority' => 'medium',
            'type' => 'content',
            'title' => 'Expand Thin Content',
            'description' => "{$thinContent} page(s) have less than 300 words.",
            'action' => 'Add more valuable content to these pages',
            'fix_available' => false,
            'affected_count' => $thinContent,
        ];
    }

    // Low: Internal links
    if (isset($issuesByType['links']) && $issuesByType['links'] > 0) {
        $recommendations[] = [
            'priority' => 'low',
            'type' => 'links',
            'title' => 'Add Internal Links',
            'description' => 'Some pages lack internal links to other content.',
            'action' => 'Add relevant internal links',
            'fix_available' => false,
            'affected_count' => $issuesByType['links'],
        ];
    }

    // Sort by priority
    usort($recommendations, function($a, $b) {
        $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        return ($order[$a['priority']] ?? 4) - ($order[$b['priority']] ?? 4);
    });

    return $recommendations;
}

/**
 * Save scan results to file
 */
function ai_optimizer_save_results(array $data): bool
{
    ai_optimizer_ensure_storage();
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(OPTIMIZER_RESULTS_FILE, $json) !== false;
}

/**
 * Load latest scan results
 */
function ai_optimizer_load_results(): ?array
{
    if (!file_exists(OPTIMIZER_RESULTS_FILE)) {
        return null;
    }

    $json = file_get_contents(OPTIMIZER_RESULTS_FILE);
    $data = json_decode($json, true);

    return is_array($data) ? $data : null;
}

/**
 * Auto-fix: Generate meta description using AI
 */
function ai_optimizer_fix_meta_description(int $pageId): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$page) {
            return ['ok' => false, 'error' => 'Page not found'];
        }

        $title = $page['title'];
        $content = strip_tags($page['content']);
        $excerpt = mb_substr($content, 0, 500);

        $prompt = "Write a compelling meta description (150-160 characters) for this page.
Title: {$title}
Content excerpt: {$excerpt}

Requirements:
- Exactly 150-160 characters
- Include a call to action
- Be descriptive and engaging
- Do NOT use quotes around the result
- Return ONLY the meta description text, nothing else.";

        $result = ai_hf_generate_text($prompt, ['params' => ['max_new_tokens' => 100, 'temperature' => 0.7]]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $metaDesc = trim($result['text']);
        $metaDesc = trim($metaDesc, '"\'');

        // Truncate if too long
        if (mb_strlen($metaDesc) > 160) {
            $metaDesc = mb_substr($metaDesc, 0, 157) . '...';
        }

        // Update database
        $updateStmt = $pdo->prepare("UPDATE pages SET meta_description = ? WHERE id = ?");
        $updateStmt->execute([$metaDesc, $pageId]);

        return [
            'ok' => true,
            'page_id' => $pageId,
            'meta_description' => $metaDesc,
        ];

    } catch (Exception $e) {
        error_log('[AI_OPTIMIZER] Fix meta description error: ' . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Auto-fix: Generate ALT tag for image using AI
 */
function ai_optimizer_fix_alt_tag(int $pageId, string $imageSrc): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$page) {
            return ['ok' => false, 'error' => 'Page not found'];
        }

        // Extract filename for context
        $filename = basename($imageSrc);
        $filename = preg_replace('/\.[^.]+$/', '', $filename); // Remove extension
        $filename = str_replace(['-', '_'], ' ', $filename);

        $prompt = "Generate a descriptive, SEO-friendly ALT tag for an image.
Page title: {$page['title']}
Image filename: {$filename}

Requirements:
- 5-15 words
- Descriptive and specific
- Include relevant keywords naturally
- Do NOT start with 'Image of' or 'Picture of'
- Return ONLY the ALT text, nothing else.";

        $result = ai_hf_generate_text($prompt, ['params' => ['max_new_tokens' => 50, 'temperature' => 0.7]]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $altText = trim($result['text']);
        $altText = trim($altText, '"\'');

        // Update content - add ALT to image
        $content = $page['content'];
        $escapedSrc = preg_quote($imageSrc, '/');

        // Pattern to find the image tag
        $pattern = '/(<img[^>]*src=["\']' . $escapedSrc . '["\'])([^>]*)(\/?>)/i';

        // Check if ALT already exists
        if (preg_match('/(<img[^>]*src=["\']' . $escapedSrc . '["\'][^>]*alt=["\'])([^"\']*)(["\']\s*\/?>)/i', $content)) {
            // Replace existing empty ALT
            $content = preg_replace(
                '/(<img[^>]*src=["\']' . $escapedSrc . '["\'][^>]*alt=["\'])([^"\']*)(["\']\s*\/?>)/i',
                '${1}' . htmlspecialchars($altText, ENT_QUOTES) . '${3}',
                $content
            );
        } else {
            // Add ALT attribute
            $content = preg_replace(
                $pattern,
                '${1} alt="' . htmlspecialchars($altText, ENT_QUOTES) . '"${2}${3}',
                $content
            );
        }

        // Update database
        $updateStmt = $pdo->prepare("UPDATE pages SET content = ? WHERE id = ?");
        $updateStmt->execute([$content, $pageId]);

        return [
            'ok' => true,
            'page_id' => $pageId,
            'image_src' => $imageSrc,
            'alt_text' => $altText,
        ];

    } catch (Exception $e) {
        error_log('[AI_OPTIMIZER] Fix ALT tag error: ' . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Bulk fix: Generate all missing meta descriptions
 */
function ai_optimizer_fix_all_meta(): array
{
    $results = ai_optimizer_load_results();
    if (!$results || empty($results['pages'])) {
        return ['ok' => false, 'error' => 'No scan results found. Run a scan first.'];
    }

    $fixed = 0;
    $failed = 0;
    $errors = [];

    foreach ($results['pages'] as $page) {
        foreach ($page['issues'] as $issue) {
            if ($issue['type'] === 'meta_description' && strpos($issue['message'], 'Missing') !== false) {
                $result = ai_optimizer_fix_meta_description($page['page_id']);
                if ($result['ok']) {
                    $fixed++;
                } else {
                    $failed++;
                    $errors[] = "Page {$page['page_id']}: " . ($result['error'] ?? 'Unknown error');
                }
                // Rate limiting
                usleep(500000); // 0.5 second delay between API calls
            }
        }
    }

    return [
        'ok' => true,
        'fixed' => $fixed,
        'failed' => $failed,
        'errors' => $errors,
    ];
}

/**
 * Get pages with specific issue type
 */
function ai_optimizer_get_pages_with_issue(string $issueType): array
{
    $results = ai_optimizer_load_results();
    if (!$results || empty($results['pages'])) {
        return [];
    }

    $pagesWithIssue = [];

    foreach ($results['pages'] as $page) {
        foreach ($page['issues'] as $issue) {
            if ($issue['type'] === $issueType) {
                $pagesWithIssue[] = [
                    'page_id' => $page['page_id'],
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'score' => $page['score'],
                    'issue' => $issue,
                ];
            }
        }
    }

    return $pagesWithIssue;
}

/**
 * Get scan age (how old is the last scan)
 */
function ai_optimizer_get_scan_age(): ?string
{
    $results = ai_optimizer_load_results();
    if (!$results || empty($results['scanned_at'])) {
        return null;
    }

    $scannedAt = strtotime($results['scanned_at']);
    $now = time();
    $diff = $now - $scannedAt;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return "{$mins} minute(s) ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "{$hours} hour(s) ago";
    } else {
        $days = floor($diff / 86400);
        return "{$days} day(s) ago";
    }
}
