<?php
/**
 * E-E-A-T Scorer
 * Experience, Expertise, Authoritativeness, Trustworthiness analysis
 * Based on Google's Search Quality Evaluator Guidelines
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Calculate E-E-A-T score for content
 *
 * @param string $content The content to analyze
 * @param array $options Additional options (author_info, citations, etc.)
 * @return array E-E-A-T scores and recommendations
 */
function ai_eeat_score(string $content, array $options = []): array {
    $scores = [
        'experience' => 0,
        'expertise' => 0,
        'authoritativeness' => 0,
        'trustworthiness' => 0,
        'overall' => 0
    ];

    $recommendations = [];
    $wordCount = str_word_count(strip_tags($content));

    // Experience Score (0-100)
    // Checks for first-person narrative, personal anecdotes, real examples
    $experienceIndicators = [
        '/\b(I|we|my|our)\s+(have|had|did|tried|tested|used|experienced|found|discovered)\b/i',
        '/\b(in my experience|from my experience|I personally|we personally)\b/i',
        '/\b(years of|months of|decades of)\s+(experience|practice|work)\b/i',
        '/\b(real-world|hands-on|practical|firsthand)\b/i'
    ];

    $experienceScore = 30; // Base score
    foreach ($experienceIndicators as $pattern) {
        if (preg_match($pattern, $content)) {
            $experienceScore += 15;
        }
    }

    // Check for case studies, examples
    if (preg_match('/\b(case study|example|instance|scenario)\b/i', $content)) {
        $experienceScore += 10;
    }

    $scores['experience'] = min(100, $experienceScore);

    if ($scores['experience'] < 50) {
        $recommendations[] = 'Add personal experience and real-world examples to improve Experience score';
    }

    // Expertise Score (0-100)
    // Checks for technical depth, specific details, data
    $expertiseScore = 30; // Base score

    // Technical terms density
    $technicalPatterns = [
        '/\b(according to|research shows|studies indicate|data suggests)\b/i',
        '/\b(specifically|technically|precisely|in particular)\b/i',
        '/\b(algorithm|methodology|framework|analysis|optimization)\b/i',
        '/\d+(\.\d+)?%/', // Percentages
        '/\d{4}/', // Years
    ];

    foreach ($technicalPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $expertiseScore += 10;
        }
    }

    // Word count bonus for comprehensive content
    if ($wordCount > 1000) $expertiseScore += 10;
    if ($wordCount > 2000) $expertiseScore += 10;

    // Check for author credentials in options
    if (!empty($options['author_credentials'])) {
        $expertiseScore += 15;
    }

    $scores['expertise'] = min(100, $expertiseScore);

    if ($scores['expertise'] < 50) {
        $recommendations[] = 'Include more specific data, statistics, and technical details to improve Expertise score';
    }

    // Authoritativeness Score (0-100)
    // Checks for citations, references, links to authoritative sources
    $authoritativenessScore = 30; // Base score

    // Citations and references
    $citationPatterns = [
        '/\b(according to|cited by|referenced in|as stated by)\b/i',
        '/\b(source:|reference:|citation:)\b/i',
        '/<a\s+[^>]*href=["\'][^"\']+["\']/i', // Links
        '/\[\d+\]/', // Citation markers [1], [2], etc.
    ];

    foreach ($citationPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $authoritativenessScore += 12;
        }
    }

    // External citations count from options
    if (!empty($options['citation_count']) && $options['citation_count'] > 0) {
        $authoritativenessScore += min(20, $options['citation_count'] * 5);
    }

    // Backlinks from options
    if (!empty($options['backlink_count']) && $options['backlink_count'] > 0) {
        $authoritativenessScore += min(15, $options['backlink_count'] * 3);
    }

    $scores['authoritativeness'] = min(100, $authoritativenessScore);

    if ($scores['authoritativeness'] < 50) {
        $recommendations[] = 'Add citations to authoritative sources and expert references to improve Authoritativeness score';
    }

    // Trustworthiness Score (0-100)
    // Checks for transparency, accuracy indicators, contact info
    $trustworthinessScore = 40; // Base score

    // Transparency indicators
    $trustPatterns = [
        '/\b(disclaimer|disclosure|affiliate|sponsored)\b/i',
        '/\b(updated|last modified|reviewed on|fact-checked)\b/i',
        '/\b(contact us|about us|our team|meet the author)\b/i',
        '/\b(privacy policy|terms of service|editorial policy)\b/i',
    ];

    foreach ($trustPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $trustworthinessScore += 10;
        }
    }

    // Check for balanced perspective
    if (preg_match('/\b(however|on the other hand|alternatively|conversely)\b/i', $content)) {
        $trustworthinessScore += 10;
    }

    // SSL and security from options
    if (!empty($options['has_ssl']) && $options['has_ssl']) {
        $trustworthinessScore += 10;
    }

    $scores['trustworthiness'] = min(100, $trustworthinessScore);

    if ($scores['trustworthiness'] < 50) {
        $recommendations[] = 'Add transparency elements like author bio, update dates, and disclosures to improve Trustworthiness score';
    }

    // Calculate overall score (weighted average)
    $scores['overall'] = round(
        ($scores['experience'] * 0.20) +
        ($scores['expertise'] * 0.30) +
        ($scores['authoritativeness'] * 0.25) +
        ($scores['trustworthiness'] * 0.25)
    );

    return [
        'scores' => $scores,
        'recommendations' => $recommendations,
        'grade' => ai_eeat_get_grade($scores['overall']),
        'word_count' => $wordCount,
        'analysis_timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Get letter grade from numeric score
 *
 * @param int $score Numeric score 0-100
 * @return string Letter grade A+ to F
 */
function ai_eeat_get_grade(int $score): string {
    if ($score >= 95) return 'A+';
    if ($score >= 90) return 'A';
    if ($score >= 85) return 'A-';
    if ($score >= 80) return 'B+';
    if ($score >= 75) return 'B';
    if ($score >= 70) return 'B-';
    if ($score >= 65) return 'C+';
    if ($score >= 60) return 'C';
    if ($score >= 55) return 'C-';
    if ($score >= 50) return 'D+';
    if ($score >= 45) return 'D';
    if ($score >= 40) return 'D-';
    return 'F';
}

/**
 * Analyze author E-E-A-T signals
 *
 * @param array $authorInfo Author information array
 * @return array Author E-E-A-T analysis
 */
function ai_eeat_analyze_author(array $authorInfo): array {
    $signals = [
        'has_bio' => !empty($authorInfo['bio']),
        'has_credentials' => !empty($authorInfo['credentials']),
        'has_social_profiles' => !empty($authorInfo['social_links']),
        'has_photo' => !empty($authorInfo['photo']),
        'has_contact' => !empty($authorInfo['email']) || !empty($authorInfo['contact_page']),
        'has_publications' => !empty($authorInfo['publications']),
    ];

    $score = 0;
    $recommendations = [];

    if ($signals['has_bio']) {
        $score += 20;
    } else {
        $recommendations[] = 'Add an author biography';
    }

    if ($signals['has_credentials']) {
        $score += 25;
    } else {
        $recommendations[] = 'Include author credentials and qualifications';
    }

    if ($signals['has_social_profiles']) {
        $score += 15;
    } else {
        $recommendations[] = 'Link to author social media profiles';
    }

    if ($signals['has_photo']) {
        $score += 15;
    } else {
        $recommendations[] = 'Add an author photo';
    }

    if ($signals['has_contact']) {
        $score += 10;
    } else {
        $recommendations[] = 'Provide author contact information';
    }

    if ($signals['has_publications']) {
        $score += 15;
    } else {
        $recommendations[] = 'List other publications or works by the author';
    }

    return [
        'score' => $score,
        'signals' => $signals,
        'recommendations' => $recommendations,
        'grade' => ai_eeat_get_grade($score)
    ];
}

/**
 * Get E-E-A-T improvement suggestions for specific content type
 *
 * @param string $contentType Type of content (article, product, medical, financial, etc.)
 * @return array Suggestions specific to content type
 */
function ai_eeat_get_suggestions(string $contentType): array {
    $suggestions = [
        'article' => [
            'Include author byline with credentials',
            'Add publication and last updated dates',
            'Cite authoritative sources',
            'Include relevant statistics and data',
            'Add internal links to related content'
        ],
        'product' => [
            'Include real user reviews and ratings',
            'Show product testing methodology',
            'Display trust badges and certifications',
            'Provide clear return/refund policies',
            'Include comparison with alternatives'
        ],
        'medical' => [
            'Have content reviewed by medical professionals',
            'Cite peer-reviewed studies',
            'Include medical disclaimers',
            'Update content regularly with latest research',
            'Display author medical credentials prominently'
        ],
        'financial' => [
            'Include financial advisor credentials',
            'Add regulatory disclaimers',
            'Cite official financial sources',
            'Show real performance data',
            'Update with current market information'
        ],
        'legal' => [
            'Display attorney credentials and bar memberships',
            'Include jurisdiction-specific disclaimers',
            'Cite relevant laws and regulations',
            'Recommend consulting with legal professionals',
            'Keep content updated with legal changes'
        ],
        'news' => [
            'Include bylines with journalist credentials',
            'Cite primary sources',
            'Distinguish between news and opinion',
            'Show publication date and updates',
            'Link to related coverage'
        ]
    ];

    return $suggestions[$contentType] ?? $suggestions['article'];
}

/**
 * Calculate page-level E-E-A-T score
 *
 * @param int $pageId Page ID to analyze
 * @return array|null Page E-E-A-T analysis or null if page not found
 */
function ai_eeat_analyze_page(int $pageId): ?array {
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT title, content, meta_description, author_id, updated_at FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            return null;
        }

        // Get author info if available
        $authorInfo = [];
        if (!empty($page['author_id'])) {
            $authorStmt = $pdo->prepare("SELECT username, email FROM admins WHERE id = ?");
            $authorStmt->execute([$page['author_id']]);
            $authorInfo = $authorStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        }

        $options = [
            'author_credentials' => !empty($authorInfo),
            'has_ssl' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ];

        $contentAnalysis = ai_eeat_score($page['content'] ?? '', $options);

        return [
            'page_id' => $pageId,
            'title' => $page['title'],
            'content_analysis' => $contentAnalysis,
            'author_info' => $authorInfo,
            'last_updated' => $page['updated_at'],
            'freshness_score' => ai_eeat_calculate_freshness($page['updated_at'])
        ];

    } catch (\Exception $e) {
        error_log('[ai_eeat_analyze_page] Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Calculate content freshness score
 *
 * @param string $updatedAt Last update timestamp
 * @return int Freshness score 0-100
 */
function ai_eeat_calculate_freshness(string $updatedAt): int {
    $updated = strtotime($updatedAt);
    $now = time();
    $daysSinceUpdate = ($now - $updated) / 86400;

    if ($daysSinceUpdate <= 7) return 100;
    if ($daysSinceUpdate <= 30) return 90;
    if ($daysSinceUpdate <= 90) return 75;
    if ($daysSinceUpdate <= 180) return 60;
    if ($daysSinceUpdate <= 365) return 40;
    return 20;
}

/**
 * Get E-E-A-T summary for multiple pages
 *
 * @param int $limit Number of pages to analyze
 * @return array Summary of E-E-A-T scores across pages
 */
function ai_eeat_get_summary(int $limit = 50): array {
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE status = 'published' ORDER BY updated_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $scores = [
            'experience' => [],
            'expertise' => [],
            'authoritativeness' => [],
            'trustworthiness' => [],
            'overall' => []
        ];

        $lowScorePages = [];

        foreach ($pages as $page) {
            $analysis = ai_eeat_score($page['content'] ?? '');

            foreach ($scores as $key => $arr) {
                $scores[$key][] = $analysis['scores'][$key];
            }

            if ($analysis['scores']['overall'] < 50) {
                $lowScorePages[] = [
                    'id' => $page['id'],
                    'title' => $page['title'],
                    'score' => $analysis['scores']['overall'],
                    'recommendations' => $analysis['recommendations']
                ];
            }
        }

        // Calculate averages
        $averages = [];
        foreach ($scores as $key => $arr) {
            $averages[$key] = count($arr) > 0 ? round(array_sum($arr) / count($arr)) : 0;
        }

        return [
            'pages_analyzed' => count($pages),
            'average_scores' => $averages,
            'overall_grade' => ai_eeat_get_grade($averages['overall']),
            'low_score_pages' => array_slice($lowScorePages, 0, 10),
            'analysis_date' => date('Y-m-d H:i:s')
        ];

    } catch (\Exception $e) {
        error_log('[ai_eeat_get_summary] Error: ' . $e->getMessage());
        return [
            'pages_analyzed' => 0,
            'average_scores' => ['experience' => 0, 'expertise' => 0, 'authoritativeness' => 0, 'trustworthiness' => 0, 'overall' => 0],
            'overall_grade' => 'N/A',
            'low_score_pages' => [],
            'error' => 'Failed to analyze pages'
        ];
    }
}
