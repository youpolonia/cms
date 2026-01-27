<?php
/**
 * Content Decay Detector
 * Premium SEO feature for identifying content that needs refreshing
 *
 * Features:
 * - Age-based decay detection
 * - SEO score trend analysis
 * - Outdated content markers
 * - Decay risk scoring
 * - Refresh priority ranking
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_seo_assistant.php';

/**
 * Decay thresholds (configurable)
 */
if (!defined('DECAY_AGE_WARNING_DAYS')) {
    define('DECAY_AGE_WARNING_DAYS', 180); // 6 months
}
if (!defined('DECAY_AGE_CRITICAL_DAYS')) {
    define('DECAY_AGE_CRITICAL_DAYS', 365); // 1 year
}
if (!defined('DECAY_SCORE_DROP_THRESHOLD')) {
    define('DECAY_SCORE_DROP_THRESHOLD', 10); // Points drop to trigger warning
}

/**
 * Get all pages with their update timestamps
 *
 * @return array Pages with metadata
 */
function ai_decay_get_pages(): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->query("
            SELECT id, title, slug, status, content, created_at, updated_at
            FROM pages
            WHERE status IN ('published', 'draft')
            ORDER BY updated_at ASC
        ");

        $pages = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $pages[] = [
                'id' => (int)$row['id'],
                'title' => (string)$row['title'],
                'slug' => (string)$row['slug'],
                'status' => (string)$row['status'],
                'content' => (string)$row['content'],
                'created_at' => (string)$row['created_at'],
                'updated_at' => (string)$row['updated_at'],
            ];
        }

        return $pages;
    } catch (\Exception $e) {
        error_log('ai_decay_get_pages: ' . $e->getMessage());
        return [];
    }
}

/**
 * Calculate content age in days
 *
 * @param string $updatedAt Last update timestamp
 * @return int Age in days
 */
function ai_decay_calculate_age(string $updatedAt): int
{
    if (empty($updatedAt)) {
        return 9999; // Very old if no date
    }

    $updateTime = strtotime($updatedAt);
    if ($updateTime === false) {
        return 9999;
    }

    $now = time();
    $diffSeconds = $now - $updateTime;

    return (int)floor($diffSeconds / 86400);
}

/**
 * Detect outdated markers in content
 *
 * @param string $content HTML content
 * @param string $title Page title
 * @return array Detected markers with severity
 */
function ai_decay_detect_markers(string $content, string $title): array
{
    $markers = [];
    $contentLower = mb_strtolower($content . ' ' . $title, 'UTF-8');
    $currentYear = (int)date('Y');

    // Check for old year references
    for ($year = $currentYear - 5; $year <= $currentYear - 2; $year++) {
        if (strpos($contentLower, (string)$year) !== false) {
            // Check if it's in a date context
            if (preg_match('/\b' . $year . '\b/', $content)) {
                $markers[] = [
                    'type' => 'old_year',
                    'detail' => "References year {$year}",
                    'severity' => $year <= $currentYear - 3 ? 'high' : 'medium',
                ];
                break; // Only report once
            }
        }
    }

    // Check for "last year", "this year" with context suggesting old content
    $timeMarkers = [
        'last year' => 'medium',
        'this year' => 'low',
        'recently updated' => 'low',
        'new for ' . ($currentYear - 1) => 'high',
        'new for ' . ($currentYear - 2) => 'high',
        'updated ' . ($currentYear - 2) => 'high',
        'guide ' . ($currentYear - 2) => 'high',
        'guide ' . ($currentYear - 1) => 'medium',
    ];

    foreach ($timeMarkers as $marker => $severity) {
        if (strpos($contentLower, $marker) !== false) {
            $markers[] = [
                'type' => 'time_reference',
                'detail' => "Contains '{$marker}'",
                'severity' => $severity,
            ];
        }
    }

    // Check for potentially broken external links (basic check)
    preg_match_all('/href=["\']https?:\/\/[^"\']+["\']/', $content, $matches);
    $externalLinkCount = count($matches[0]);
    if ($externalLinkCount > 20) {
        $markers[] = [
            'type' => 'many_external_links',
            'detail' => "{$externalLinkCount} external links (may need verification)",
            'severity' => 'low',
        ];
    }

    // Check for deprecated terms/technologies
    $deprecatedTerms = [
        'flash player' => 'high',
        'internet explorer' => 'medium',
        'windows xp' => 'high',
        'windows 7' => 'medium',
        'php 5' => 'high',
        'jquery 1.' => 'medium',
        'bootstrap 3' => 'medium',
        'http://' => 'low', // Non-HTTPS
    ];

    foreach ($deprecatedTerms as $term => $severity) {
        if (strpos($contentLower, $term) !== false) {
            $markers[] = [
                'type' => 'deprecated_reference',
                'detail' => "References '{$term}'",
                'severity' => $severity,
            ];
        }
    }

    // Check word count (thin content)
    $textContent = strip_tags($content);
    $wordCount = str_word_count($textContent);
    if ($wordCount < 300) {
        $markers[] = [
            'type' => 'thin_content',
            'detail' => "Only {$wordCount} words (recommended: 1000+)",
            'severity' => $wordCount < 100 ? 'high' : 'medium',
        ];
    }

    return $markers;
}

/**
 * Get SEO score history for a page
 *
 * @param int $pageId Page ID
 * @return array Score history sorted by date
 */
function ai_decay_get_score_history(int $pageId): array
{
    $reports = ai_seo_assistant_list_reports();
    $history = [];

    foreach ($reports as $report) {
        if (!isset($report['page_id']) || (int)$report['page_id'] !== $pageId) {
            continue;
        }

        $score = null;
        if (isset($report['health_score']) && $report['health_score'] !== null) {
            $score = (int)$report['health_score'];
        } elseif (isset($report['content_score']) && $report['content_score'] !== null) {
            $score = (int)$report['content_score'];
        }

        if ($score !== null) {
            $history[] = [
                'date' => $report['created_at'] ?? '',
                'score' => $score,
                'report_id' => $report['id'] ?? '',
            ];
        }
    }

    // Sort by date ascending
    usort($history, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    return $history;
}

/**
 * Analyze score trend
 *
 * @param array $history Score history from ai_decay_get_score_history()
 * @return array Trend analysis
 */
function ai_decay_analyze_trend(array $history): array
{
    if (count($history) < 2) {
        return [
            'trend' => 'unknown',
            'change' => 0,
            'first_score' => $history[0]['score'] ?? null,
            'last_score' => $history[0]['score'] ?? null,
        ];
    }

    $firstScore = $history[0]['score'];
    $lastScore = $history[count($history) - 1]['score'];
    $change = $lastScore - $firstScore;

    $trend = 'stable';
    if ($change <= -DECAY_SCORE_DROP_THRESHOLD) {
        $trend = 'declining';
    } elseif ($change >= DECAY_SCORE_DROP_THRESHOLD) {
        $trend = 'improving';
    }

    return [
        'trend' => $trend,
        'change' => $change,
        'first_score' => $firstScore,
        'last_score' => $lastScore,
        'data_points' => count($history),
    ];
}

/**
 * Calculate decay risk score (0-100)
 *
 * @param int $ageDays Content age in days
 * @param array $markers Detected decay markers
 * @param array $trend Score trend analysis
 * @param int|null $currentScore Current SEO score
 * @return array Risk assessment
 */
function ai_decay_calculate_risk(int $ageDays, array $markers, array $trend, ?int $currentScore): array
{
    $riskScore = 0;
    $factors = [];

    // Age factor (0-40 points)
    if ($ageDays >= DECAY_AGE_CRITICAL_DAYS) {
        $riskScore += 40;
        $factors[] = 'Content over 1 year old';
    } elseif ($ageDays >= DECAY_AGE_WARNING_DAYS) {
        $riskScore += 25;
        $factors[] = 'Content over 6 months old';
    } elseif ($ageDays >= 90) {
        $riskScore += 10;
        $factors[] = 'Content over 3 months old';
    }

    // Marker factor (0-30 points)
    $highMarkers = 0;
    $mediumMarkers = 0;
    foreach ($markers as $marker) {
        if ($marker['severity'] === 'high') {
            $highMarkers++;
        } elseif ($marker['severity'] === 'medium') {
            $mediumMarkers++;
        }
    }
    $markerPoints = min(30, ($highMarkers * 10) + ($mediumMarkers * 5));
    $riskScore += $markerPoints;
    if ($highMarkers > 0) {
        $factors[] = "{$highMarkers} high-severity markers detected";
    }
    if ($mediumMarkers > 0) {
        $factors[] = "{$mediumMarkers} medium-severity markers detected";
    }

    // Trend factor (0-20 points)
    if ($trend['trend'] === 'declining') {
        $declinePoints = min(20, abs($trend['change']));
        $riskScore += $declinePoints;
        $factors[] = "SEO score declining ({$trend['change']} points)";
    }

    // Current score factor (0-10 points)
    if ($currentScore !== null && $currentScore < 50) {
        $riskScore += 10;
        $factors[] = 'Current SEO score below 50';
    } elseif ($currentScore !== null && $currentScore < 70) {
        $riskScore += 5;
        $factors[] = 'Current SEO score below 70';
    }

    // Normalize to 0-100
    $riskScore = min(100, $riskScore);

    // Determine risk level
    $riskLevel = 'low';
    if ($riskScore >= 70) {
        $riskLevel = 'critical';
    } elseif ($riskScore >= 50) {
        $riskLevel = 'high';
    } elseif ($riskScore >= 30) {
        $riskLevel = 'medium';
    }

    return [
        'score' => $riskScore,
        'level' => $riskLevel,
        'factors' => $factors,
    ];
}

/**
 * Generate refresh recommendations
 *
 * @param array $markers Detected markers
 * @param array $trend Score trend
 * @param int $ageDays Content age
 * @return array Recommendations
 */
function ai_decay_get_recommendations(array $markers, array $trend, int $ageDays): array
{
    $recommendations = [];

    // Age-based recommendations
    if ($ageDays >= DECAY_AGE_CRITICAL_DAYS) {
        $recommendations[] = [
            'priority' => 'high',
            'action' => 'Full content review and update',
            'reason' => 'Content is over 1 year old and may contain outdated information',
        ];
    } elseif ($ageDays >= DECAY_AGE_WARNING_DAYS) {
        $recommendations[] = [
            'priority' => 'medium',
            'action' => 'Review and refresh content',
            'reason' => 'Content is over 6 months old',
        ];
    }

    // Marker-based recommendations
    foreach ($markers as $marker) {
        switch ($marker['type']) {
            case 'old_year':
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 'Update year references',
                    'reason' => $marker['detail'],
                ];
                break;
            case 'deprecated_reference':
                $recommendations[] = [
                    'priority' => $marker['severity'] === 'high' ? 'high' : 'medium',
                    'action' => 'Update deprecated technology references',
                    'reason' => $marker['detail'],
                ];
                break;
            case 'thin_content':
                $recommendations[] = [
                    'priority' => 'high',
                    'action' => 'Expand content with more detail',
                    'reason' => $marker['detail'],
                ];
                break;
            case 'many_external_links':
                $recommendations[] = [
                    'priority' => 'low',
                    'action' => 'Verify external links are still valid',
                    'reason' => $marker['detail'],
                ];
                break;
        }
    }

    // Trend-based recommendations
    if ($trend['trend'] === 'declining') {
        $recommendations[] = [
            'priority' => 'high',
            'action' => 'Investigate and address SEO score decline',
            'reason' => "Score dropped by {$trend['change']} points",
        ];
    }

    // Deduplicate by action
    $seen = [];
    $unique = [];
    foreach ($recommendations as $rec) {
        if (!in_array($rec['action'], $seen, true)) {
            $seen[] = $rec['action'];
            $unique[] = $rec;
        }
    }

    // Sort by priority
    usort($unique, function($a, $b) {
        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        return ($order[$a['priority']] ?? 3) - ($order[$b['priority']] ?? 3);
    });

    return $unique;
}

/**
 * Analyze all pages for content decay
 *
 * @return array Complete decay analysis
 */
function ai_decay_analyze_all(): array
{
    $pages = ai_decay_get_pages();

    if (empty($pages)) {
        return [
            'ok' => false,
            'error' => 'No pages found for analysis.',
        ];
    }

    $analysis = [
        'ok' => true,
        'analyzed_at' => gmdate('Y-m-d H:i:s'),
        'total_pages' => count($pages),
        'pages' => [],
        'statistics' => [
            'critical_decay' => 0,
            'high_decay' => 0,
            'medium_decay' => 0,
            'low_decay' => 0,
            'avg_age_days' => 0,
            'oldest_page_days' => 0,
            'pages_over_1_year' => 0,
            'pages_over_6_months' => 0,
        ],
    ];

    $totalAge = 0;

    foreach ($pages as $page) {
        $ageDays = ai_decay_calculate_age($page['updated_at']);
        $markers = ai_decay_detect_markers($page['content'], $page['title']);
        $scoreHistory = ai_decay_get_score_history($page['id']);
        $trend = ai_decay_analyze_trend($scoreHistory);

        $currentScore = null;
        if (!empty($scoreHistory)) {
            $currentScore = $scoreHistory[count($scoreHistory) - 1]['score'];
        }

        $risk = ai_decay_calculate_risk($ageDays, $markers, $trend, $currentScore);
        $recommendations = ai_decay_get_recommendations($markers, $trend, $ageDays);

        $analysis['pages'][] = [
            'id' => $page['id'],
            'title' => $page['title'],
            'slug' => $page['slug'],
            'status' => $page['status'],
            'age_days' => $ageDays,
            'updated_at' => $page['updated_at'],
            'current_score' => $currentScore,
            'score_trend' => $trend['trend'],
            'score_change' => $trend['change'],
            'decay_risk' => $risk['score'],
            'decay_level' => $risk['level'],
            'risk_factors' => $risk['factors'],
            'markers' => $markers,
            'recommendations' => $recommendations,
        ];

        // Update statistics
        $totalAge += $ageDays;
        if ($risk['level'] === 'critical') {
            $analysis['statistics']['critical_decay']++;
        } elseif ($risk['level'] === 'high') {
            $analysis['statistics']['high_decay']++;
        } elseif ($risk['level'] === 'medium') {
            $analysis['statistics']['medium_decay']++;
        } else {
            $analysis['statistics']['low_decay']++;
        }

        if ($ageDays >= DECAY_AGE_CRITICAL_DAYS) {
            $analysis['statistics']['pages_over_1_year']++;
        } elseif ($ageDays >= DECAY_AGE_WARNING_DAYS) {
            $analysis['statistics']['pages_over_6_months']++;
        }

        if ($ageDays > $analysis['statistics']['oldest_page_days']) {
            $analysis['statistics']['oldest_page_days'] = $ageDays;
        }
    }

    // Calculate averages
    if (count($pages) > 0) {
        $analysis['statistics']['avg_age_days'] = (int)round($totalAge / count($pages));
    }

    // Sort by decay risk descending
    usort($analysis['pages'], fn($a, $b) => $b['decay_risk'] - $a['decay_risk']);

    return $analysis;
}

/**
 * Get pages needing immediate attention
 *
 * @param int $limit Maximum pages to return
 * @return array High-priority pages
 */
function ai_decay_get_priority_pages(int $limit = 10): array
{
    $analysis = ai_decay_analyze_all();

    if (!$analysis['ok']) {
        return [];
    }

    $priority = array_filter($analysis['pages'], fn($p) => $p['decay_level'] === 'critical' || $p['decay_level'] === 'high');

    return array_slice($priority, 0, $limit);
}

/**
 * Format age for display
 *
 * @param int $days Age in days
 * @return string Human-readable age
 */
function ai_decay_format_age(int $days): string
{
    if ($days >= 365) {
        $years = floor($days / 365);
        $months = floor(($days % 365) / 30);
        return $years . ' year' . ($years > 1 ? 's' : '') . ($months > 0 ? ", {$months} mo" : '');
    } elseif ($days >= 30) {
        $months = floor($days / 30);
        return $months . ' month' . ($months > 1 ? 's' : '');
    } else {
        return $days . ' day' . ($days !== 1 ? 's' : '');
    }
}
