<?php
/**
 * Internal Linking Engine
 * Premium SEO feature for intelligent internal link suggestions
 *
 * Features:
 * - Page relationship mapping
 * - Anchor text suggestions
 * - Orphan page detection
 * - Link opportunity scoring
 * - Existing link analysis
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';

/**
 * Storage directory for internal linking data
 */
if (!defined('AI_LINKING_STORAGE_DIR')) {
    define('AI_LINKING_STORAGE_DIR', CMS_ROOT . '/cms_storage/ai-linking');
}

/**
 * Get all pages with their content for analysis
 *
 * @param int $limit Maximum pages to return
 * @return array Array of pages with id, title, slug, content, status
 */
function ai_linking_get_all_pages(int $limit = 1000): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT id, title, slug, content, status, updated_at
            FROM pages
            WHERE status = 'published'
            ORDER BY updated_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $pages = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $pages[] = [
                'id' => (int)$row['id'],
                'title' => (string)$row['title'],
                'slug' => (string)$row['slug'],
                'content' => (string)$row['content'],
                'status' => (string)$row['status'],
                'updated_at' => (string)$row['updated_at'],
            ];
        }

        return $pages;
    } catch (\Exception $e) {
        error_log('ai_linking_get_all_pages: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all articles with their content for analysis
 *
 * @param int $limit Maximum articles to return
 * @return array Array of articles with id, title, slug, content, status, category info
 */
function ai_linking_get_all_articles(int $limit = 1000): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.slug, a.content, a.status, a.updated_at,
                   a.category_id, a.focus_keyword, c.name as category_name
            FROM articles a
            LEFT JOIN article_categories c ON a.category_id = c.id
            WHERE a.status = 'published'
            ORDER BY a.updated_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $articles = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $articles[] = [
                'id' => (int)$row['id'],
                'title' => (string)$row['title'],
                'slug' => (string)$row['slug'],
                'content' => (string)$row['content'],
                'status' => (string)$row['status'],
                'updated_at' => (string)$row['updated_at'],
                'category_id' => $row['category_id'] ? (int)$row['category_id'] : null,
                'category_name' => (string)($row['category_name'] ?? ''),
                'focus_keyword' => (string)($row['focus_keyword'] ?? ''),
                'type' => 'article',
                'url' => '/blog/' . $row['slug'],
            ];
        }

        return $articles;
    } catch (\Exception $e) {
        error_log('ai_linking_get_all_articles: ' . $e->getMessage());
        return [];
    }
}

/**
 * Detect content type from URL/href
 *
 * @param string $href The URL to analyze
 * @return array|null Array with type and slug, or null if invalid
 */
function ai_linking_detect_content_type(string $href): ?array
{
    // Check for article URL patterns
    if (preg_match('#^/blog/([a-z0-9_-]+)/?$#i', $href, $m)) {
        return ['type' => 'article', 'slug' => $m[1]];
    }
    if (preg_match('#^/article/([a-z0-9_-]+)/?$#i', $href, $m)) {
        return ['type' => 'article', 'slug' => $m[1]];
    }
    // Default to page
    $slug = trim($href, '/');
    if ($slug !== '' && strpos($slug, '/') === false) {
        return ['type' => 'page', 'slug' => $slug];
    }
    return null;
}

/**
 * Extract existing internal links from HTML content
 *
 * @param string $html HTML content
 * @param string $baseUrl Base URL of the site (optional)
 * @return array Array of links with href, anchor_text, is_internal
 */
function ai_linking_extract_links(string $html, string $baseUrl = ''): array
{
    $links = [];

    // Match all <a> tags
    preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $href = trim($match[1]);
        $anchorText = trim(strip_tags($match[2]));

        // Skip empty hrefs, anchors, javascript, mailto
        if ($href === '' || strpos($href, '#') === 0 || strpos($href, 'javascript:') === 0 || strpos($href, 'mailto:') === 0) {
            continue;
        }

        // Determine if internal
        $isInternal = false;
        if (strpos($href, '/') === 0 && strpos($href, '//') !== 0) {
            // Relative URL starting with /
            $isInternal = true;
        } elseif ($baseUrl !== '' && strpos($href, $baseUrl) === 0) {
            // Absolute URL matching base
            $isInternal = true;
        }

        $links[] = [
            'href' => $href,
            'anchor_text' => $anchorText,
            'is_internal' => $isInternal,
        ];
    }

    return $links;
}

/**
 * Extract keywords/topics from page content
 *
 * @param string $title Page title
 * @param string $content HTML content
 * @param int $maxKeywords Maximum keywords to extract
 * @return array Array of keywords sorted by relevance
 */
function ai_linking_extract_keywords(string $title, string $content, int $maxKeywords = 20): array
{
    // Strip HTML and normalize
    $text = strip_tags($content);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    // Also include title words (higher weight)
    $titleLower = mb_strtolower($title, 'UTF-8');
    $titleLower = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $titleLower);

    // Tokenize
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $titleWords = preg_split('/\s+/', $titleLower, -1, PREG_SPLIT_NO_EMPTY);

    // Stopwords (English)
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
                  'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
                  'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
                  'should', 'may', 'might', 'must', 'shall', 'can', 'this', 'that', 'these',
                  'those', 'it', 'its', 'they', 'them', 'their', 'we', 'our', 'you', 'your',
                  'he', 'she', 'him', 'her', 'his', 'i', 'me', 'my', 'not', 'no', 'yes',
                  'all', 'any', 'some', 'more', 'most', 'other', 'such', 'only', 'than',
                  'too', 'very', 'just', 'also', 'now', 'here', 'there', 'when', 'where',
                  'why', 'how', 'what', 'which', 'who', 'whom', 'whose', 'if', 'then',
                  'else', 'so', 'because', 'although', 'while', 'since', 'until', 'unless'];

    // Count word frequencies
    $freq = [];
    foreach ($words as $word) {
        if (mb_strlen($word) < 3 || in_array($word, $stopwords, true)) {
            continue;
        }
        if (!isset($freq[$word])) {
            $freq[$word] = 0;
        }
        $freq[$word]++;
    }

    // Boost title words
    foreach ($titleWords as $word) {
        if (mb_strlen($word) < 3 || in_array($word, $stopwords, true)) {
            continue;
        }
        if (!isset($freq[$word])) {
            $freq[$word] = 0;
        }
        $freq[$word] += 5; // Title words get 5x boost
    }

    // Extract 2-word phrases (bigrams)
    $bigrams = [];
    for ($i = 0; $i < count($words) - 1; $i++) {
        $w1 = $words[$i];
        $w2 = $words[$i + 1];
        if (mb_strlen($w1) >= 3 && mb_strlen($w2) >= 3 &&
            !in_array($w1, $stopwords, true) && !in_array($w2, $stopwords, true)) {
            $bigram = $w1 . ' ' . $w2;
            if (!isset($bigrams[$bigram])) {
                $bigrams[$bigram] = 0;
            }
            $bigrams[$bigram]++;
        }
    }

    // Merge bigrams into freq with higher weight
    foreach ($bigrams as $bigram => $count) {
        if ($count >= 2) { // Only include bigrams that appear 2+ times
            $freq[$bigram] = ($freq[$bigram] ?? 0) + ($count * 2);
        }
    }

    // Sort by frequency
    arsort($freq);

    // Return top keywords
    return array_slice(array_keys($freq), 0, $maxKeywords);
}

/**
 * Calculate relevance score between two pages
 *
 * @param array $sourcePage Source page data
 * @param array $targetPage Target page data
 * @return array Relevance data with score and matching keywords
 */
function ai_linking_calculate_relevance(array $sourcePage, array $targetPage): array
{
    // Don't link to self
    if ($sourcePage['id'] === $targetPage['id']) {
        return ['score' => 0, 'matching_keywords' => []];
    }

    // Extract keywords from both pages
    $sourceKeywords = ai_linking_extract_keywords($sourcePage['title'], $sourcePage['content']);
    $targetKeywords = ai_linking_extract_keywords($targetPage['title'], $targetPage['content']);

    // Find matching keywords
    $matching = array_intersect($sourceKeywords, $targetKeywords);

    // Calculate score based on:
    // 1. Number of matching keywords
    // 2. Position of matches (earlier = more relevant)
    // 3. Title match bonus

    $score = 0;
    $matchDetails = [];

    foreach ($matching as $keyword) {
        $sourcePos = array_search($keyword, $sourceKeywords);
        $targetPos = array_search($keyword, $targetKeywords);

        // Score based on position (top keywords worth more)
        $posScore = (20 - min($sourcePos, 10)) + (20 - min($targetPos, 10));
        $score += $posScore;

        $matchDetails[] = [
            'keyword' => $keyword,
            'source_rank' => $sourcePos + 1,
            'target_rank' => $targetPos + 1,
        ];
    }

    // Title word match bonus
    $sourceTitle = mb_strtolower($sourcePage['title'], 'UTF-8');
    $targetTitle = mb_strtolower($targetPage['title'], 'UTF-8');
    $sourceTitleWords = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $sourceTitle));
    $targetTitleWords = preg_split('/\s+/', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $targetTitle));
    $titleMatches = array_intersect($sourceTitleWords, $targetTitleWords);
    $score += count($titleMatches) * 15;

    // Normalize to 0-100
    $normalizedScore = min(100, (int)($score / 2));

    return [
        'score' => $normalizedScore,
        'matching_keywords' => $matchDetails,
        'title_matches' => array_values($titleMatches),
    ];
}

/**
 * Generate anchor text suggestions for linking
 *
 * @param array $targetPage Target page to link to
 * @param array $matchingKeywords Keywords that match between pages
 * @return array Array of anchor text suggestions
 */
function ai_linking_suggest_anchors(array $targetPage, array $matchingKeywords = []): array
{
    $suggestions = [];

    // 1. Page title (always a good anchor)
    $suggestions[] = [
        'text' => $targetPage['title'],
        'type' => 'title',
        'priority' => 1,
    ];

    // 2. Top matching keywords as anchors
    foreach (array_slice($matchingKeywords, 0, 3) as $match) {
        $keyword = $match['keyword'] ?? $match;
        if (is_string($keyword) && mb_strlen($keyword) >= 4) {
            $suggestions[] = [
                'text' => ucfirst($keyword),
                'type' => 'keyword',
                'priority' => 2,
            ];
        }
    }

    // 3. Slug-based anchor (cleaned up)
    $slugWords = explode('-', $targetPage['slug']);
    if (count($slugWords) >= 2 && count($slugWords) <= 5) {
        $slugAnchor = ucfirst(implode(' ', $slugWords));
        $suggestions[] = [
            'text' => $slugAnchor,
            'type' => 'slug',
            'priority' => 3,
        ];
    }

    // 4. Call-to-action style anchors
    $ctaAnchors = [
        'Learn more about ' . mb_strtolower($targetPage['title']),
        'Read our guide on ' . mb_strtolower($targetPage['title']),
        'See also: ' . $targetPage['title'],
    ];
    foreach ($ctaAnchors as $cta) {
        if (mb_strlen($cta) <= 60) {
            $suggestions[] = [
                'text' => $cta,
                'type' => 'cta',
                'priority' => 4,
            ];
        }
    }

    return $suggestions;
}

/**
 * Analyze internal linking for all pages and articles
 *
 * @return array Complete linking analysis with suggestions
 */
function ai_linking_analyze_all(): array
{
    $pages = ai_linking_get_all_pages();
    $articles = ai_linking_get_all_articles();

    // Add type and url to pages
    foreach ($pages as &$page) {
        $page['type'] = 'page';
        $page['url'] = '/' . ltrim($page['slug'], '/');
    }
    unset($page);

    // Merge all content items
    $allContent = array_merge($pages, $articles);

    if (empty($allContent)) {
        return [
            'ok' => false,
            'error' => 'No content found for analysis.',
        ];
    }

    $analysis = [
        'ok' => true,
        'analyzed_at' => gmdate('Y-m-d H:i:s'),
        'total_pages' => count($pages),
        'total_articles' => count($articles),
        'total_content' => count($allContent),
        'pages' => [],
        'orphan_pages' => [],
        'opportunities' => [],
        'statistics' => [
            'total_internal_links' => 0,
            'total_pages' => count($pages),
            'total_articles' => count($articles),
            'pages_with_no_outgoing' => 0,
            'pages_with_no_incoming' => 0,
            'articles_with_no_outgoing' => 0,
            'articles_with_no_incoming' => 0,
            'avg_outgoing_links' => 0,
            'avg_incoming_links' => 0,
        ],
    ];

    // First pass: extract existing links and keywords
    $contentData = [];
    $incomingLinks = []; // type_id => [list of source content]

    foreach ($allContent as $item) {
        $itemKey = $item['type'] . '_' . $item['id'];
        $links = ai_linking_extract_links($item['content']);
        $internalLinks = array_filter($links, fn($l) => $l['is_internal']);
        $keywords = ai_linking_extract_keywords($item['title'], $item['content']);

        // If article has focus_keyword, boost it
        if (!empty($item['focus_keyword'])) {
            array_unshift($keywords, $item['focus_keyword']);
            $keywords = array_unique($keywords);
        }

        $contentData[$itemKey] = [
            'item' => $item,
            'outgoing_links' => $internalLinks,
            'keywords' => $keywords,
        ];

        $analysis['statistics']['total_internal_links'] += count($internalLinks);

        if (empty($internalLinks)) {
            if ($item['type'] === 'article') {
                $analysis['statistics']['articles_with_no_outgoing']++;
            } else {
                $analysis['statistics']['pages_with_no_outgoing']++;
            }
        }

        // Track incoming links by matching slugs/urls
        foreach ($internalLinks as $link) {
            $href = $link['href'];
            $detected = ai_linking_detect_content_type($href);

            foreach ($allContent as $targetItem) {
                $matchesPage = ($targetItem['type'] === 'page' &&
                    ($targetItem['slug'] === trim($href, '/') || '/' . $targetItem['slug'] === $href));

                $matchesArticle = ($targetItem['type'] === 'article' &&
                    ($detected && $detected['type'] === 'article' && $detected['slug'] === $targetItem['slug']));

                if ($matchesPage || $matchesArticle) {
                    $targetKey = $targetItem['type'] . '_' . $targetItem['id'];
                    if (!isset($incomingLinks[$targetKey])) {
                        $incomingLinks[$targetKey] = [];
                    }
                    $incomingLinks[$targetKey][] = $itemKey;
                    break;
                }
            }
        }
    }

    // Second pass: analyze opportunities and build content summaries
    foreach ($contentData as $itemKey => $data) {
        $item = $data['item'];
        $incomingCount = isset($incomingLinks[$itemKey]) ? count($incomingLinks[$itemKey]) : 0;
        $outgoingCount = count($data['outgoing_links']);

        // Mark orphan content (no incoming links)
        if ($incomingCount === 0) {
            $analysis['orphan_pages'][] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'slug' => $item['slug'],
                'type' => $item['type'],
                'url' => $item['url'],
                'outgoing_links' => $outgoingCount,
            ];
            if ($item['type'] === 'article') {
                $analysis['statistics']['articles_with_no_incoming']++;
            } else {
                $analysis['statistics']['pages_with_no_incoming']++;
            }
        }

        // Find linking opportunities (content that should link to this one)
        $opportunities = [];
        foreach ($contentData as $otherKey => $otherData) {
            if ($otherKey === $itemKey) continue;

            $relevance = ai_linking_calculate_relevance($otherData['item'], $item);

            if ($relevance['score'] >= 30) { // Threshold for suggesting a link
                // Check if link already exists
                $alreadyLinked = false;
                $targetUrlPart = ($item['type'] === 'article') ? '/blog/' . $item['slug'] : '/' . $item['slug'];
                foreach ($otherData['outgoing_links'] as $existingLink) {
                    if (strpos($existingLink['href'], $item['slug']) !== false) {
                        $alreadyLinked = true;
                        break;
                    }
                }

                if (!$alreadyLinked) {
                    $anchors = ai_linking_suggest_anchors($item, $relevance['matching_keywords']);

                    $opportunities[] = [
                        'from_page_id' => $otherData['item']['id'],
                        'from_page_title' => $otherData['item']['title'],
                        'from_type' => $otherData['item']['type'],
                        'from_url' => $otherData['item']['url'],
                        'to_page_id' => $item['id'],
                        'to_page_title' => $item['title'],
                        'to_page_slug' => $item['slug'],
                        'to_type' => $item['type'],
                        'to_url' => $item['url'],
                        'relevance_score' => $relevance['score'],
                        'matching_keywords' => array_slice($relevance['matching_keywords'], 0, 5),
                        'suggested_anchors' => array_slice($anchors, 0, 3),
                    ];
                }
            }
        }

        // Add top opportunities to global list
        usort($opportunities, fn($a, $b) => $b['relevance_score'] - $a['relevance_score']);
        foreach (array_slice($opportunities, 0, 3) as $opp) {
            $analysis['opportunities'][] = $opp;
        }

        // Content summary
        $analysis['pages'][$itemKey] = [
            'id' => $item['id'],
            'title' => $item['title'],
            'slug' => $item['slug'],
            'type' => $item['type'],
            'url' => $item['url'],
            'incoming_links' => $incomingCount,
            'outgoing_links' => $outgoingCount,
            'top_keywords' => array_slice($data['keywords'], 0, 5),
            'link_opportunities' => count($opportunities),
        ];
    }

    // Sort opportunities by score globally
    usort($analysis['opportunities'], fn($a, $b) => $b['relevance_score'] - $a['relevance_score']);
    $analysis['opportunities'] = array_slice($analysis['opportunities'], 0, 50); // Top 50

    // Calculate averages
    $totalContent = count($allContent);
    if ($totalContent > 0) {
        $totalOutgoing = array_sum(array_column($analysis['pages'], 'outgoing_links'));
        $totalIncoming = array_sum(array_column($analysis['pages'], 'incoming_links'));
        $analysis['statistics']['avg_outgoing_links'] = round($totalOutgoing / $totalContent, 1);
        $analysis['statistics']['avg_incoming_links'] = round($totalIncoming / $totalContent, 1);
    }

    return $analysis;
}

/**
 * Get linking suggestions for a specific page or article
 *
 * @param int $contentId Content ID to get suggestions for
 * @param int $limit Maximum suggestions
 * @param string $type Content type: 'page' or 'article'
 * @return array Linking suggestions
 */
function ai_linking_get_suggestions(int $contentId, int $limit = 10, string $type = 'page'): array
{
    $pages = ai_linking_get_all_pages();
    $articles = ai_linking_get_all_articles();

    // Add type and url to pages
    foreach ($pages as &$page) {
        $page['type'] = 'page';
        $page['url'] = '/' . ltrim($page['slug'], '/');
    }
    unset($page);

    // Merge all content
    $allContent = array_merge($pages, $articles);

    // Find target content
    $targetContent = null;
    foreach ($allContent as $item) {
        if ($item['id'] === $contentId && $item['type'] === $type) {
            $targetContent = $item;
            break;
        }
    }

    if ($targetContent === null) {
        return ['ok' => false, 'error' => ucfirst($type) . ' not found.'];
    }

    $suggestions = [
        'ok' => true,
        'page' => [
            'id' => $targetContent['id'],
            'title' => $targetContent['title'],
            'slug' => $targetContent['slug'],
            'type' => $targetContent['type'],
            'url' => $targetContent['url'],
        ],
        'link_to' => [],   // Content this item should link TO
        'link_from' => [], // Content that should link TO this item
        'existing_links' => [], // Current outgoing internal links
    ];

    // Extract current outgoing links
    $existingLinks = ai_linking_extract_links($targetContent['content']);
    $existingSlugs = [];
    foreach ($existingLinks as $link) {
        if ($link['is_internal']) {
            $detected = ai_linking_detect_content_type($link['href']);
            if ($detected) {
                $existingSlugs[] = $detected['type'] . ':' . $detected['slug'];
            }
            $existingSlugs[] = trim($link['href'], '/');
            
            // Add to existing_links for display
            $suggestions['existing_links'][] = [
                'href' => $link['href'],
                'anchor_text' => $link['anchor_text'],
                'type' => $detected['type'] ?? 'page',
            ];
        }
    }

    // Analyze relevance with all other content
    foreach ($allContent as $otherItem) {
        if ($otherItem['id'] === $contentId && $otherItem['type'] === $type) continue;

        // Check if already linking
        $slugKey = $otherItem['type'] . ':' . $otherItem['slug'];
        $alreadyLinking = in_array($otherItem['slug'], $existingSlugs, true) ||
                          in_array($slugKey, $existingSlugs, true);

        $relevance = ai_linking_calculate_relevance($targetContent, $otherItem);

        if ($relevance['score'] >= 25 && !$alreadyLinking) {
            $anchors = ai_linking_suggest_anchors($otherItem, $relevance['matching_keywords']);

            $suggestions['link_to'][] = [
                'page_id' => $otherItem['id'],
                'title' => $otherItem['title'],
                'slug' => $otherItem['slug'],
                'type' => $otherItem['type'],
                'url' => $otherItem['url'],
                'relevance_score' => $relevance['score'],
                'matching_keywords' => array_column(array_slice($relevance['matching_keywords'], 0, 3), 'keyword'),
                'suggested_anchors' => array_column(array_slice($anchors, 0, 2), 'text'),
            ];
        }

        // Also check reverse (should they link to us?)
        $reverseRelevance = ai_linking_calculate_relevance($otherItem, $targetContent);

        // Check if they already link to us
        $otherLinks = ai_linking_extract_links($otherItem['content']);
        $linksToUs = false;
        foreach ($otherLinks as $link) {
            if ($link['is_internal'] && strpos($link['href'], $targetContent['slug']) !== false) {
                $linksToUs = true;
                break;
            }
        }

        if ($reverseRelevance['score'] >= 25 && !$linksToUs) {
            $suggestions['link_from'][] = [
                'page_id' => $otherItem['id'],
                'title' => $otherItem['title'],
                'slug' => $otherItem['slug'],
                'type' => $otherItem['type'],
                'url' => $otherItem['url'],
                'relevance_score' => $reverseRelevance['score'],
                'matching_keywords' => array_column(array_slice($reverseRelevance['matching_keywords'], 0, 3), 'keyword'),
            ];
        }
    }

    // Sort by relevance score
    usort($suggestions['link_to'], fn($a, $b) => $b['relevance_score'] - $a['relevance_score']);
    usort($suggestions['link_from'], fn($a, $b) => $b['relevance_score'] - $a['relevance_score']);

    // Limit results
    $suggestions['link_to'] = array_slice($suggestions['link_to'], 0, $limit);
    $suggestions['link_from'] = array_slice($suggestions['link_from'], 0, $limit);

    return $suggestions;
}

/**
 * Save linking analysis to storage
 *
 * @param array $analysis Analysis data from ai_linking_analyze_all()
 * @return bool Success
 */
function ai_linking_save_analysis(array $analysis): bool
{
    $dir = AI_LINKING_STORAGE_DIR;
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true)) {
            error_log('ai_linking_save_analysis: Failed to create storage directory');
            return false;
        }
    }

    $path = $dir . '/latest_analysis.json';
    $json = json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        error_log('ai_linking_save_analysis: Failed to encode JSON');
        return false;
    }

    return @file_put_contents($path, $json, LOCK_EX) !== false;
}

/**
 * Load latest linking analysis from storage
 *
 * @return array|null Analysis data or null if not found
 */
function ai_linking_load_analysis(): ?array
{
    $path = AI_LINKING_STORAGE_DIR . '/latest_analysis.json';

    if (!file_exists($path)) {
        return null;
    }

    $json = @file_get_contents($path);
    if ($json === false) {
        return null;
    }

    $data = @json_decode($json, true);
    if (!is_array($data)) {
        return null;
    }

    return $data;
}

/**
 * Get link health score for a page
 *
 * @param int $incomingLinks Number of incoming links
 * @param int $outgoingLinks Number of outgoing links
 * @return array Health assessment
 */
function ai_linking_health_score(int $incomingLinks, int $outgoingLinks): array
{
    $score = 0;
    $issues = [];
    $recommendations = [];

    // Incoming links assessment
    if ($incomingLinks === 0) {
        $score -= 30;
        $issues[] = 'Orphan page: no internal links pointing to this page';
        $recommendations[] = 'Add internal links from related pages to improve discoverability';
    } elseif ($incomingLinks < 3) {
        $score -= 10;
        $issues[] = 'Low incoming links';
        $recommendations[] = 'Consider adding more internal links from relevant pages';
    } else {
        $score += 20;
    }

    // Outgoing links assessment
    if ($outgoingLinks === 0) {
        $score -= 20;
        $issues[] = 'No outgoing internal links';
        $recommendations[] = 'Add links to related content to improve site structure';
    } elseif ($outgoingLinks < 2) {
        $score -= 5;
        $issues[] = 'Few outgoing links';
        $recommendations[] = 'Consider linking to more related pages';
    } elseif ($outgoingLinks > 100) {
        $score -= 15;
        $issues[] = 'Too many outgoing links';
        $recommendations[] = 'Consider reducing link count for better link equity distribution';
    } else {
        $score += 15;
    }

    // Balance check
    if ($incomingLinks > 0 && $outgoingLinks > 0) {
        $ratio = $incomingLinks / $outgoingLinks;
        if ($ratio > 5) {
            $issues[] = 'Imbalanced: many incoming but few outgoing';
            $recommendations[] = 'This is a hub page - consider adding contextual links';
        } elseif ($ratio < 0.2) {
            $issues[] = 'Imbalanced: many outgoing but few incoming';
            $recommendations[] = 'Promote this page with more internal links';
        } else {
            $score += 15;
        }
    }

    // Normalize score to 0-100
    $finalScore = max(0, min(100, 50 + $score));

    $status = 'good';
    if ($finalScore < 40) {
        $status = 'poor';
    } elseif ($finalScore < 70) {
        $status = 'needs_improvement';
    }

    return [
        'score' => $finalScore,
        'status' => $status,
        'issues' => $issues,
        'recommendations' => $recommendations,
    ];
}

/**
 * Apply a link suggestion to page or article content
 * Finds the best occurrence of anchor text and wraps it with a link
 *
 * @param int $fromId Source content ID
 * @param int $toId Target content ID
 * @param string $anchorText Text to use as anchor (optional, will find best match)
 * @param string $fromType Source content type: 'page' or 'article'
 * @param string $toType Target content type: 'page' or 'article'
 * @return array Result with ok, message
 */
function ai_linking_apply_link(int $fromId, int $toId, string $anchorText, string $fromType = 'page', string $toType = 'page'): array
{
    try {
        $pdo = \core\Database::connection();

        // Get source content
        if ($fromType === 'article') {
            $stmt = $pdo->prepare("SELECT id, title, content FROM articles WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE id = ?");
        }
        $stmt->execute([$fromId]);
        $fromContent = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fromContent) {
            return ['ok' => false, 'error' => 'Source ' . $fromType . ' not found'];
        }

        // Get target content
        if ($toType === 'article') {
            $stmt = $pdo->prepare("SELECT id, slug, title, content FROM articles WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT id, slug, title, content FROM pages WHERE id = ?");
        }
        $stmt->execute([$toId]);
        $toContent = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$toContent) {
            return ['ok' => false, 'error' => 'Target ' . $toType . ' not found'];
        }

        $content = $fromContent['content'];
        $targetUrl = ($toType === 'article') ? '/blog/' . $toContent['slug'] : '/' . $toContent['slug'];

        // Check if link already exists
        if (stripos($content, 'href="' . $targetUrl . '"') !== false ||
            stripos($content, "href='" . $targetUrl . "'") !== false) {
            return ['ok' => false, 'error' => 'Link to this ' . $toType . ' already exists'];
        }

        // Build list of potential anchors to try (in order of preference)
        $anchorsToTry = [];

        // 1. User-provided anchor
        if (!empty(trim($anchorText))) {
            $anchorsToTry[] = trim($anchorText);
        }

        // 2. Target title
        $anchorsToTry[] = $toContent['title'];

        // 3. Find MATCHING KEYWORDS between both content items (this is key!)
        $sourceKeywords = ai_linking_extract_keywords($fromContent['title'], $fromContent['content'], 30);
        $targetKeywords = ai_linking_extract_keywords($toContent['title'], $toContent['content'], 30);
        $matchingKeywords = array_intersect($sourceKeywords, $targetKeywords);

        // Add matching keywords - these SHOULD exist in source content
        foreach ($matchingKeywords as $keyword) {
            if (mb_strlen($keyword) >= 4) {
                $anchorsToTry[] = $keyword;
            }
        }

        // 4. Words from target title (2+ words combinations)
        $titleWords = preg_split('/\s+/', $toContent['title']);
        if (count($titleWords) >= 2) {
            for ($i = 0; $i < count($titleWords) - 1; $i++) {
                $anchorsToTry[] = $titleWords[$i] . ' ' . $titleWords[$i + 1];
            }
        }

        // 5. Individual significant words from title (4+ chars)
        foreach ($titleWords as $word) {
            if (mb_strlen($word) >= 4) {
                $anchorsToTry[] = $word;
            }
        }

        // 6. Slug-based anchor
        $slugWords = explode('-', $toContent['slug']);
        if (count($slugWords) >= 2) {
            $anchorsToTry[] = implode(' ', $slugWords);
        }
        foreach ($slugWords as $sw) {
            if (mb_strlen($sw) >= 4) {
                $anchorsToTry[] = $sw;
            }
        }

        // Remove duplicates
        $anchorsToTry = array_unique($anchorsToTry);

        // Try each anchor - look in PLAIN TEXT content (strip HTML first for searching)
        $plainContent = strip_tags($content);
        $foundAnchor = null;
        $foundPos = false;

        foreach ($anchorsToTry as $tryAnchor) {
            if (mb_strlen($tryAnchor) < 3) continue;

            // Search in plain text first to find if word exists
            $plainPos = mb_stripos($plainContent, $tryAnchor);
            if ($plainPos === false) continue;

            // Now find in actual HTML content
            $pos = stripos($content, $tryAnchor);

            if ($pos !== false) {
                // Check if NOT already inside a link tag
                $before = substr($content, 0, $pos);
                $lastAOpen = strrpos($before, '<a ');
                $lastAClose = strrpos($before, '</a>');

                // Also check we're not inside a tag attribute
                $lastTagOpen = strrpos($before, '<');
                $lastTagClose = strrpos($before, '>');

                $insideLink = ($lastAOpen !== false && ($lastAClose === false || $lastAClose < $lastAOpen));
                $insideTag = ($lastTagOpen !== false && ($lastTagClose === false || $lastTagClose < $lastTagOpen));

                if (!$insideLink && !$insideTag) {
                    $foundAnchor = $tryAnchor;
                    $foundPos = $pos;
                    break;
                }
            }
        }

        if ($foundPos === false) {
            // Nothing found - offer to append link at end
            $linkHtml = '<a href="' . htmlspecialchars($targetUrl) . '">' . htmlspecialchars($toContent['title']) . '</a>';

            return [
                'ok' => false,
                'error' => 'No suitable anchor text found. Content may not have overlapping text.',
                'suggestion' => 'Copy and paste this link into the content manually.',
                'link_html' => $linkHtml,
                'tried' => array_slice($anchorsToTry, 0, 10) // Debug info
            ];
        }

        // Get the actual text with correct case from content
        $actualAnchor = substr($content, $foundPos, strlen($foundAnchor));

        // Create the link
        $link = '<a href="' . htmlspecialchars($targetUrl) . '">' . $actualAnchor . '</a>';

        // Replace first occurrence
        $newContent = substr($content, 0, $foundPos) . $link . substr($content, $foundPos + strlen($foundAnchor));

        // Update database
        if ($fromType === 'article') {
            $stmt = $pdo->prepare("UPDATE articles SET content = ?, updated_at = NOW() WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE pages SET content = ?, updated_at = NOW() WHERE id = ?");
        }
        $stmt->execute([$newContent, $fromId]);

        return [
            'ok' => true,
            'message' => 'Link added successfully',
            'anchor' => $actualAnchor,
            'url' => $targetUrl
        ];

    } catch (\Exception $e) {
        error_log('ai_linking_apply_link: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get a specific page by ID
 */
function ai_linking_get_page(int $pageId): ?array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, title, slug, content FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Remove an internal link from content
 *
 * @param int $fromId Source content ID
 * @param string $targetUrl URL of the link to remove (e.g., /blog/my-article or /my-page)
 * @param string $fromType Content type: 'page' or 'article'
 * @return array Result with ok status
 */
function ai_linking_remove_link(int $fromId, string $targetUrl, string $fromType = 'page'): array
{
    try {
        $pdo = \core\Database::connection();

        // Get source content
        if ($fromType === 'article') {
            $stmt = $pdo->prepare("SELECT id, title, content FROM articles WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE id = ?");
        }
        $stmt->execute([$fromId]);
        $fromContent = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fromContent) {
            return ['ok' => false, 'error' => 'Source ' . $fromType . ' not found'];
        }

        $content = $fromContent['content'];
        $targetUrl = trim($targetUrl);
        
        // Count links before removal
        $linksBefore = preg_match_all('/<a\s[^>]*href=["\'][^"\']*["\'][^>]*>/i', $content);

        // Pattern to match link with this URL (handles both single and double quotes)
        // Captures: full <a>...</a> tag with this href
        $pattern = '/<a\s+[^>]*href=["\']' . preg_quote($targetUrl, '/') . '["\'][^>]*>(.*?)<\/a>/is';
        
        // Replace link with just its anchor text (unlink but keep text)
        $newContent = preg_replace($pattern, '$1', $content);
        
        // Check if any replacement was made
        $linksAfter = preg_match_all('/<a\s[^>]*href=["\'][^"\']*["\'][^>]*>/i', $newContent);
        
        if ($newContent === $content || $linksBefore === $linksAfter) {
            return ['ok' => false, 'error' => 'Link not found in content'];
        }

        // Update database
        if ($fromType === 'article') {
            $stmt = $pdo->prepare("UPDATE articles SET content = ?, updated_at = NOW() WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE pages SET content = ?, updated_at = NOW() WHERE id = ?");
        }
        $stmt->execute([$newContent, $fromId]);

        return [
            'ok' => true,
            'message' => 'Link removed successfully',
            'removed_url' => $targetUrl
        ];

    } catch (\Exception $e) {
        error_log('ai_linking_remove_link: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}
