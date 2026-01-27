<?php
/**
 * Competitor Tracker
 * Track and analyze competitor content for SEO insights
 *
 * Features:
 * - Competitor URL storage
 * - Content metrics comparison
 * - Gap analysis
 * - Opportunity identification
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Storage path for competitor data
define('COMPETITOR_STORAGE_DIR', CMS_ROOT . '/cms_storage/competitors');

/**
 * Ensure storage directory exists
 */
function ai_competitor_ensure_storage(): bool
{
    if (!is_dir(COMPETITOR_STORAGE_DIR)) {
        return mkdir(COMPETITOR_STORAGE_DIR, 0755, true);
    }
    return true;
}

/**
 * Get storage path for a keyword
 *
 * @param string $keyword Target keyword
 * @return string File path
 */
function ai_competitor_get_path(string $keyword): string
{
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($keyword)));
    $slug = trim($slug, '-');
    return COMPETITOR_STORAGE_DIR . '/' . $slug . '.json';
}

/**
 * Save competitor data for a keyword
 *
 * @param string $keyword Target keyword
 * @param array $data Competitor data
 * @return bool Success
 */
function ai_competitor_save(string $keyword, array $data): bool
{
    ai_competitor_ensure_storage();

    $path = ai_competitor_get_path($keyword);
    $data['keyword'] = $keyword;
    $data['updated_at'] = gmdate('Y-m-d H:i:s');

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json) !== false;
}

/**
 * Load competitor data for a keyword
 *
 * @param string $keyword Target keyword
 * @return array|null Competitor data or null
 */
function ai_competitor_load(string $keyword): ?array
{
    $path = ai_competitor_get_path($keyword);

    if (!file_exists($path)) {
        return null;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    return is_array($data) ? $data : null;
}

/**
 * List all tracked keywords
 *
 * @return array List of tracked keywords with metadata
 */
function ai_competitor_list_tracked(): array
{
    ai_competitor_ensure_storage();

    $tracked = [];
    $files = glob(COMPETITOR_STORAGE_DIR . '/*.json');

    foreach ($files as $file) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (is_array($data) && !empty($data['keyword'])) {
            $tracked[] = [
                'keyword' => $data['keyword'],
                'competitors_count' => count($data['competitors'] ?? []),
                'updated_at' => $data['updated_at'] ?? '',
                'our_score' => $data['our_content']['score'] ?? null,
            ];
        }
    }

    // Sort by updated_at descending
    usort($tracked, fn($a, $b) => strcmp($b['updated_at'], $a['updated_at']));

    return $tracked;
}

/**
 * Load all competitor data
 *
 * @return array All competitor data keyed by keyword
 */
function ai_competitor_load_all(): array
{
    ai_competitor_ensure_storage();

    $all = [];
    $files = glob(COMPETITOR_STORAGE_DIR . '/*.json');

    foreach ($files as $file) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (is_array($data) && !empty($data['keyword'])) {
            $all[$data['keyword']] = $data;
        }
    }

    return $all;
}

/**
 * Add a competitor for a keyword
 *
 * @param string $keyword Target keyword
 * @param array $competitor Competitor data (url, title, etc.)
 * @return bool Success
 */
function ai_competitor_add(string $keyword, array $competitor): bool
{
    $data = ai_competitor_load($keyword) ?? [
        'keyword' => $keyword,
        'competitors' => [],
        'our_content' => null,
    ];

    // Check for duplicate URL
    $url = $competitor['url'] ?? '';
    foreach ($data['competitors'] as $existing) {
        if ($existing['url'] === $url) {
            return false; // Already exists
        }
    }

    $competitor['added_at'] = gmdate('Y-m-d H:i:s');
    $data['competitors'][] = $competitor;

    return ai_competitor_save($keyword, $data);
}

/**
 * Remove a competitor
 *
 * @param string $keyword Target keyword
 * @param string $url Competitor URL to remove
 * @return bool Success
 */
function ai_competitor_remove(string $keyword, string $url): bool
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return false;
    }

    $data['competitors'] = array_filter(
        $data['competitors'] ?? [],
        fn($c) => ($c['url'] ?? '') !== $url
    );
    $data['competitors'] = array_values($data['competitors']);

    return ai_competitor_save($keyword, $data);
}

/**
 * Analyze competitor content metrics
 *
 * @param string $content HTML content
 * @param string $keyword Target keyword
 * @return array Content metrics
 */
function ai_competitor_analyze_content(string $content, string $keyword): array
{
    $text = strip_tags($content);
    $wordCount = str_word_count($text);
    $kwLower = strtolower($keyword);
    $textLower = strtolower($text);

    // Keyword density
    $keywordCount = substr_count($textLower, $kwLower);
    $keywordDensity = $wordCount > 0 ? round(($keywordCount / $wordCount) * 100, 2) : 0;

    // Headings
    preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $headings);
    $headingCount = count($headings[0]);
    $headingTexts = array_map(fn($h) => trim(strip_tags($h)), $headings[2]);

    // Keyword in headings
    $keywordInHeadings = 0;
    foreach ($headingTexts as $h) {
        if (stripos($h, $keyword) !== false) {
            $keywordInHeadings++;
        }
    }

    // Images
    preg_match_all('/<img[^>]*>/i', $content, $images);
    $imageCount = count($images[0]);

    // Images with alt containing keyword
    $imagesWithKwAlt = 0;
    foreach ($images[0] as $img) {
        if (preg_match('/alt=["\']([^"\']*)["\']/', $img, $altMatch)) {
            if (stripos($altMatch[1], $keyword) !== false) {
                $imagesWithKwAlt++;
            }
        }
    }

    // Links
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $links);
    $internalLinks = 0;
    $externalLinks = 0;
    foreach ($links[1] as $href) {
        if (strpos($href, 'http') === 0) {
            $externalLinks++;
        } else {
            $internalLinks++;
        }
    }

    // Lists
    $listCount = preg_match_all('/<[ou]l[^>]*>/i', $content);

    // Tables
    $tableCount = preg_match_all('/<table[^>]*>/i', $content);

    // Paragraphs
    $paragraphCount = preg_match_all('/<p[^>]*>/i', $content);

    // Calculate overall score (0-100)
    $score = 50; // Base

    // Word count scoring
    if ($wordCount >= 2000) $score += 15;
    elseif ($wordCount >= 1500) $score += 12;
    elseif ($wordCount >= 1000) $score += 8;
    elseif ($wordCount >= 500) $score += 5;

    // Headings scoring
    if ($headingCount >= 8) $score += 10;
    elseif ($headingCount >= 5) $score += 7;
    elseif ($headingCount >= 3) $score += 4;

    // Keyword optimization
    if ($keywordDensity >= 0.5 && $keywordDensity <= 2.5) $score += 10;
    if ($keywordInHeadings >= 2) $score += 5;

    // Media
    if ($imageCount >= 3) $score += 5;
    if ($listCount >= 2) $score += 5;
    if ($tableCount >= 1) $score += 3;

    // Links
    if ($internalLinks >= 3) $score += 4;
    if ($externalLinks >= 2) $score += 3;

    $score = min(100, $score);

    return [
        'word_count' => $wordCount,
        'heading_count' => $headingCount,
        'headings' => array_slice($headingTexts, 0, 10),
        'keyword_count' => $keywordCount,
        'keyword_density' => $keywordDensity,
        'keyword_in_headings' => $keywordInHeadings,
        'image_count' => $imageCount,
        'images_with_kw_alt' => $imagesWithKwAlt,
        'internal_links' => $internalLinks,
        'external_links' => $externalLinks,
        'list_count' => $listCount,
        'table_count' => $tableCount,
        'paragraph_count' => $paragraphCount,
        'score' => $score,
    ];
}

/**
 * Compare our content with competitors
 *
 * @param array $ourMetrics Our content metrics
 * @param array $competitors Competitor metrics array
 * @return array Comparison results
 */
function ai_competitor_compare(array $ourMetrics, array $competitors): array
{
    if (empty($competitors)) {
        return [
            'gaps' => [],
            'advantages' => [],
            'recommendations' => [],
        ];
    }

    $gaps = [];
    $advantages = [];
    $recommendations = [];

    // Calculate averages
    $avgWordCount = 0;
    $avgHeadings = 0;
    $avgImages = 0;
    $avgScore = 0;

    foreach ($competitors as $c) {
        $metrics = $c['metrics'] ?? [];
        $avgWordCount += $metrics['word_count'] ?? 0;
        $avgHeadings += $metrics['heading_count'] ?? 0;
        $avgImages += $metrics['image_count'] ?? 0;
        $avgScore += $metrics['score'] ?? 0;
    }

    $count = count($competitors);
    $avgWordCount = round($avgWordCount / $count);
    $avgHeadings = round($avgHeadings / $count);
    $avgImages = round($avgImages / $count);
    $avgScore = round($avgScore / $count);

    $ourWordCount = $ourMetrics['word_count'] ?? 0;
    $ourHeadings = $ourMetrics['heading_count'] ?? 0;
    $ourImages = $ourMetrics['image_count'] ?? 0;
    $ourScore = $ourMetrics['score'] ?? 0;

    // Word count comparison
    if ($ourWordCount < $avgWordCount * 0.8) {
        $diff = $avgWordCount - $ourWordCount;
        $gaps[] = [
            'metric' => 'Word Count',
            'our_value' => $ourWordCount,
            'competitor_avg' => $avgWordCount,
            'gap' => $diff,
        ];
        $recommendations[] = [
            'priority' => 'high',
            'issue' => "Content is {$diff} words shorter than competitors",
            'action' => "Expand content to at least {$avgWordCount} words",
        ];
    } elseif ($ourWordCount > $avgWordCount * 1.2) {
        $advantages[] = [
            'metric' => 'Word Count',
            'our_value' => $ourWordCount,
            'competitor_avg' => $avgWordCount,
            'advantage' => $ourWordCount - $avgWordCount,
        ];
    }

    // Headings comparison
    if ($ourHeadings < $avgHeadings * 0.7) {
        $gaps[] = [
            'metric' => 'Headings',
            'our_value' => $ourHeadings,
            'competitor_avg' => $avgHeadings,
            'gap' => $avgHeadings - $ourHeadings,
        ];
        $recommendations[] = [
            'priority' => 'medium',
            'issue' => "Fewer headings than competitors ({$ourHeadings} vs {$avgHeadings})",
            'action' => "Add more H2/H3 headings to improve structure",
        ];
    } elseif ($ourHeadings > $avgHeadings * 1.3) {
        $advantages[] = [
            'metric' => 'Headings',
            'our_value' => $ourHeadings,
            'competitor_avg' => $avgHeadings,
            'advantage' => $ourHeadings - $avgHeadings,
        ];
    }

    // Images comparison
    if ($ourImages < $avgImages * 0.7 && $avgImages > 0) {
        $gaps[] = [
            'metric' => 'Images',
            'our_value' => $ourImages,
            'competitor_avg' => $avgImages,
            'gap' => $avgImages - $ourImages,
        ];
        $recommendations[] = [
            'priority' => 'medium',
            'issue' => "Fewer images than competitors ({$ourImages} vs {$avgImages})",
            'action' => "Add relevant images to improve engagement",
        ];
    }

    // Overall score
    if ($ourScore < $avgScore - 10) {
        $recommendations[] = [
            'priority' => 'high',
            'issue' => "Overall content score below competitor average",
            'action' => "Focus on improving content depth and optimization",
        ];
    } elseif ($ourScore > $avgScore + 10) {
        $advantages[] = [
            'metric' => 'Overall Score',
            'our_value' => $ourScore,
            'competitor_avg' => $avgScore,
            'advantage' => $ourScore - $avgScore,
        ];
    }

    return [
        'gaps' => $gaps,
        'advantages' => $advantages,
        'recommendations' => $recommendations,
        'averages' => [
            'word_count' => $avgWordCount,
            'headings' => $avgHeadings,
            'images' => $avgImages,
            'score' => $avgScore,
        ],
    ];
}

/**
 * Get competitor analysis summary
 *
 * @param string $keyword Target keyword
 * @return array Analysis summary
 */
function ai_competitor_get_analysis(string $keyword): array
{
    $data = ai_competitor_load($keyword);

    if (!$data || empty($data['competitors'])) {
        return [
            'ok' => false,
            'error' => 'No competitor data found for this keyword',
        ];
    }

    $ourMetrics = $data['our_content']['metrics'] ?? [];
    $competitors = $data['competitors'] ?? [];

    $comparison = ai_competitor_compare($ourMetrics, $competitors);

    // Find best performing competitor
    $bestCompetitor = null;
    $bestScore = 0;
    foreach ($competitors as $c) {
        $score = $c['metrics']['score'] ?? 0;
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestCompetitor = $c;
        }
    }

    return [
        'ok' => true,
        'keyword' => $keyword,
        'our_score' => $ourMetrics['score'] ?? 0,
        'competitor_count' => count($competitors),
        'competitor_avg_score' => $comparison['averages']['score'] ?? 0,
        'best_competitor' => $bestCompetitor,
        'gaps' => $comparison['gaps'],
        'advantages' => $comparison['advantages'],
        'recommendations' => $comparison['recommendations'],
        'averages' => $comparison['averages'],
    ];
}

/**
 * Generate competitor report
 *
 * @param string $keyword Target keyword
 * @return array Detailed report
 */
function ai_competitor_generate_report(string $keyword): array
{
    $data = ai_competitor_load($keyword);

    if (!$data) {
        return [
            'ok' => false,
            'error' => 'No data for this keyword',
        ];
    }

    $analysis = ai_competitor_get_analysis($keyword);

    return [
        'ok' => true,
        'keyword' => $keyword,
        'generated_at' => gmdate('Y-m-d H:i:s'),
        'our_content' => $data['our_content'] ?? null,
        'competitors' => $data['competitors'] ?? [],
        'analysis' => $analysis,
    ];
}

/**
 * Delete competitor tracking for a keyword
 *
 * @param string $keyword Target keyword
 * @return bool Success
 */
function ai_competitor_delete(string $keyword): bool
{
    $path = ai_competitor_get_path($keyword);

    if (file_exists($path)) {
        return unlink($path);
    }

    return false;
}

/**
 * Update our content metrics for a keyword
 *
 * @param string $keyword Target keyword
 * @param string $url Our page URL
 * @param string $content Our page content
 * @return bool Success
 */
function ai_competitor_update_our_content(string $keyword, string $url, string $content): bool
{
    $data = ai_competitor_load($keyword) ?? [
        'keyword' => $keyword,
        'competitors' => [],
    ];

    $metrics = ai_competitor_analyze_content($content, $keyword);

    $data['our_content'] = [
        'url' => $url,
        'metrics' => $metrics,
        'analyzed_at' => gmdate('Y-m-d H:i:s'),
    ];

    return ai_competitor_save($keyword, $data);
}


/**
 * ============================================
 * PRO FEATURES - Advanced Competitor Analysis
 * ============================================
 */

/**
 * Fetch and analyze competitor page content
 * 
 * @param string $url Competitor URL
 * @param string $keyword Target keyword
 * @return array Analysis results
 */
function ai_competitor_fetch_and_analyze(string $url, string $keyword): array
{
    $result = [
        'success' => false,
        'url' => $url,
        'error' => null,
        'metrics' => []
    ];
    
    // Fetch page content
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (compatible; CMSBot/1.0)',
            'follow_location' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $html = @file_get_contents($url, false, $context);
    
    if ($html === false) {
        $result['error'] = 'Failed to fetch page';
        return $result;
    }
    
    // Extract title
    preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
    $title = isset($titleMatch[1]) ? trim(strip_tags($titleMatch[1])) : '';
    
    // Extract meta description
    preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/is', $html, $descMatch);
    $metaDesc = isset($descMatch[1]) ? trim($descMatch[1]) : '';
    
    // Strip scripts and styles
    $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
    
    // Extract headings
    $headings = [];
    preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $headingMatches);
    if (!empty($headingMatches[2])) {
        foreach ($headingMatches[2] as $i => $h) {
            $level = $headingMatches[1][$i];
            $text = trim(strip_tags($h));
            if (!empty($text)) {
                $headings[] = [
                    'level' => (int)$level,
                    'text' => $text
                ];
            }
        }
    }
    
    // Extract links
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $linkMatches);
    $internalLinks = 0;
    $externalLinks = 0;
    $parsedUrl = parse_url($url);
    $domain = $parsedUrl['host'] ?? '';
    
    foreach ($linkMatches[1] ?? [] as $link) {
        if (strpos($link, 'http') === 0) {
            $linkDomain = parse_url($link, PHP_URL_HOST);
            if ($linkDomain && strpos($linkDomain, $domain) !== false) {
                $internalLinks++;
            } else {
                $externalLinks++;
            }
        } else {
            $internalLinks++;
        }
    }
    
    // Extract images
    preg_match_all('/<img[^>]+>/i', $html, $imgMatches);
    $imageCount = count($imgMatches[0] ?? []);
    $imagesWithAlt = 0;
    foreach ($imgMatches[0] ?? [] as $img) {
        if (preg_match('/alt=["\'][^"\']+["\']/', $img)) {
            $imagesWithAlt++;
        }
    }
    
    // Get text content
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Word count
    $words = str_word_count($text);
    
    // Keyword density
    $keywordLower = strtolower($keyword);
    $textLower = strtolower($text);
    $keywordCount = substr_count($textLower, $keywordLower);
    $keywordDensity = $words > 0 ? round(($keywordCount / $words) * 100, 2) : 0;
    
    // Calculate readability (Flesch-Kincaid approximation)
    $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $sentenceCount = count($sentences);
    $avgWordsPerSentence = $sentenceCount > 0 ? $words / $sentenceCount : 0;
    
    // Simple SEO score calculation
    $seoScore = 0;
    if ($words >= 1000) $seoScore += 20;
    elseif ($words >= 500) $seoScore += 10;
    if (count($headings) >= 5) $seoScore += 15;
    elseif (count($headings) >= 3) $seoScore += 10;
    if ($keywordDensity >= 0.5 && $keywordDensity <= 2.5) $seoScore += 20;
    if (!empty($title) && stripos($title, $keyword) !== false) $seoScore += 15;
    if (!empty($metaDesc) && stripos($metaDesc, $keyword) !== false) $seoScore += 10;
    if ($imageCount > 0) $seoScore += 10;
    if ($imagesWithAlt === $imageCount && $imageCount > 0) $seoScore += 10;
    
    $result['success'] = true;
    $result['metrics'] = [
        'title' => $title,
        'meta_description' => $metaDesc,
        'word_count' => $words,
        'heading_count' => count($headings),
        'headings' => array_column($headings, 'text'),
        'heading_structure' => $headings,
        'internal_links' => $internalLinks,
        'external_links' => $externalLinks,
        'link_count' => $internalLinks + $externalLinks,
        'image_count' => $imageCount,
        'images_with_alt' => $imagesWithAlt,
        'keyword_count' => $keywordCount,
        'keyword_density' => $keywordDensity,
        'avg_words_per_sentence' => round($avgWordsPerSentence, 1),
        'seo_score' => min($seoScore, 100),
        'analyzed_at' => gmdate('Y-m-d H:i:s')
    ];
    
    return $result;
}

/**
 * Analyze all competitors for a keyword
 * 
 * @param string $keyword Target keyword
 * @return array Results
 */
function ai_competitor_analyze_all(string $keyword): array
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return ['success' => false, 'error' => 'Keyword not found'];
    }
    
    $results = [];
    $competitors = $data['competitors'] ?? [];
    
    foreach ($competitors as $i => $comp) {
        $url = $comp['url'] ?? '';
        if (!$url) continue;
        
        $analysis = ai_competitor_fetch_and_analyze($url, $keyword);
        
        if ($analysis['success']) {
            $data['competitors'][$i]['title'] = $analysis['metrics']['title'] ?: $comp['title'];
            $data['competitors'][$i]['metrics'] = $analysis['metrics'];
            $results[] = ['url' => $url, 'success' => true];
        } else {
            $results[] = ['url' => $url, 'success' => false, 'error' => $analysis['error']];
        }
    }
    
    ai_competitor_save($keyword, $data);
    
    return ['success' => true, 'analyzed' => count($results), 'results' => $results];
}

/**
 * Detect content gaps between our content and competitors
 * 
 * @param string $keyword Target keyword
 * @return array Gap analysis
 */
function ai_competitor_detect_gaps(string $keyword): array
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return ['gaps' => [], 'opportunities' => []];
    }
    
    $gaps = [];
    $competitorTopics = [];
    
    // Collect all headings from competitors
    foreach ($data['competitors'] ?? [] as $comp) {
        $headings = $comp['metrics']['headings'] ?? [];
        foreach ($headings as $h) {
            $topic = strtolower(trim($h));
            if (strlen($topic) > 5) { // Skip very short headings
                $competitorTopics[$topic] = ($competitorTopics[$topic] ?? 0) + 1;
            }
        }
    }
    
    // Get our headings
    $ourHeadings = array_map('strtolower', $data['our_content']['metrics']['headings'] ?? []);
    
    // Find topics we're missing
    foreach ($competitorTopics as $topic => $count) {
        $found = false;
        foreach ($ourHeadings as $ourH) {
            // Fuzzy match - check if similar
            similar_text($topic, $ourH, $similarity);
            if ($similarity > 70) {
                $found = true;
                break;
            }
        }
        
        if (!$found && $count >= 2) {
            $gaps[] = [
                'topic' => $topic,
                'competitors_covering' => $count,
                'priority' => $count >= 3 ? 'high' : 'medium'
            ];
        }
    }
    
    // Sort by priority and count
    usort($gaps, function($a, $b) {
        if ($a['priority'] !== $b['priority']) {
            return $a['priority'] === 'high' ? -1 : 1;
        }
        return $b['competitors_covering'] - $a['competitors_covering'];
    });
    
    // Calculate opportunities
    $opportunities = [];
    $totalCompetitors = count($data['competitors'] ?? []);
    
    if ($totalCompetitors > 0) {
        $avgWords = array_sum(array_map(fn($c) => $c['metrics']['word_count'] ?? 0, $data['competitors'])) / $totalCompetitors;
        $ourWords = $data['our_content']['metrics']['word_count'] ?? 0;
        
        if ($ourWords > $avgWords * 1.2) {
            $opportunities[] = [
                'type' => 'strength',
                'icon' => '💪',
                'message' => 'Your content is ' . round(($ourWords / $avgWords - 1) * 100) . '% longer than average - good for rankings!'
            ];
        } elseif ($ourWords < $avgWords * 0.8) {
            $opportunities[] = [
                'type' => 'weakness',
                'icon' => '📝',
                'message' => 'Consider adding ' . round($avgWords - $ourWords) . ' more words to match competitor average'
            ];
        }
        
        $avgHeadings = array_sum(array_map(fn($c) => $c['metrics']['heading_count'] ?? 0, $data['competitors'])) / $totalCompetitors;
        $ourHeadingsCount = $data['our_content']['metrics']['heading_count'] ?? 0;
        
        if ($ourHeadingsCount < $avgHeadings * 0.7) {
            $opportunities[] = [
                'type' => 'weakness',
                'icon' => '📑',
                'message' => 'Add more headings - competitors average ' . round($avgHeadings) . ' vs your ' . $ourHeadingsCount
            ];
        }
    }
    
    return [
        'gaps' => $gaps,
        'opportunities' => $opportunities,
        'total_competitors' => $totalCompetitors,
        'analyzed_at' => gmdate('Y-m-d H:i:s')
    ];
}

/**
 * Calculate Share of Voice
 * 
 * @param string $keyword Target keyword
 * @return array SOV data
 */
function ai_competitor_calculate_sov(string $keyword): array
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return ['our_share' => 0, 'breakdown' => []];
    }
    
    $ourScore = $data['our_content']['metrics']['seo_score'] ?? 0;
    $breakdown = [
        ['name' => 'You', 'score' => $ourScore, 'is_us' => true]
    ];
    
    $totalScore = $ourScore;
    
    foreach ($data['competitors'] ?? [] as $comp) {
        $score = $comp['metrics']['seo_score'] ?? 50;
        $totalScore += $score;
        $breakdown[] = [
            'name' => $comp['title'] ?? parse_url($comp['url'], PHP_URL_HOST),
            'score' => $score,
            'url' => $comp['url'],
            'is_us' => false
        ];
    }
    
    // Calculate percentages
    foreach ($breakdown as &$item) {
        $item['share'] = $totalScore > 0 ? round(($item['score'] / $totalScore) * 100, 1) : 0;
    }
    
    // Sort by share descending
    usort($breakdown, fn($a, $b) => $b['share'] - $a['share']);
    
    return [
        'our_share' => $totalScore > 0 ? round(($ourScore / $totalScore) * 100, 1) : 0,
        'total_score' => $totalScore,
        'breakdown' => $breakdown,
        'calculated_at' => gmdate('Y-m-d H:i:s')
    ];
}

/**
 * Export competitor data to CSV
 * 
 * @param string $keyword Target keyword
 * @return string CSV content
 */
function ai_competitor_export_csv(string $keyword): string
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return '';
    }
    
    $csv = [];
    $csv[] = ['Keyword', 'Competitor', 'URL', 'Word Count', 'Headings', 'Links', 'SEO Score', 'Analyzed At'];
    
    // Add our content
    if (!empty($data['our_content'])) {
        $m = $data['our_content']['metrics'] ?? [];
        $csv[] = [
            $keyword,
            'YOUR CONTENT',
            $data['our_content']['url'] ?? '',
            $m['word_count'] ?? '',
            $m['heading_count'] ?? '',
            $m['link_count'] ?? '',
            $m['seo_score'] ?? '',
            $m['analyzed_at'] ?? ''
        ];
    }
    
    // Add competitors
    foreach ($data['competitors'] ?? [] as $comp) {
        $m = $comp['metrics'] ?? [];
        $csv[] = [
            $keyword,
            $comp['title'] ?? '',
            $comp['url'] ?? '',
            $m['word_count'] ?? '',
            $m['heading_count'] ?? '',
            $m['link_count'] ?? '',
            $m['seo_score'] ?? '',
            $m['analyzed_at'] ?? ''
        ];
    }
    
    // Convert to CSV string
    $output = '';
    foreach ($csv as $row) {
        $output .= implode(',', array_map(function($cell) {
            return '"' . str_replace('"', '""', $cell) . '"';
        }, $row)) . "\n";
    }
    
    return $output;
}

/**
 * Get alerts for competitor changes
 * 
 * @param string $keyword Target keyword (optional, all if empty)
 * @return array Alerts
 */
function ai_competitor_get_alerts(string $keyword = ''): array
{
    $alertsFile = COMPETITOR_STORAGE_DIR . '/alerts.json';
    
    if (!file_exists($alertsFile)) {
        return [];
    }
    
    $alerts = json_decode(file_get_contents($alertsFile), true) ?? [];
    
    if ($keyword) {
        return array_filter($alerts, fn($a) => $a['keyword'] === $keyword);
    }
    
    return array_slice($alerts, 0, 50); // Last 50 alerts
}

/**
 * Add an alert
 * 
 * @param string $keyword Keyword
 * @param string $type Alert type
 * @param string $message Alert message
 * @param array $data Additional data
 * @return bool Success
 */
function ai_competitor_add_alert(string $keyword, string $type, string $message, array $data = []): bool
{
    ai_competitor_ensure_storage();
    
    $alertsFile = COMPETITOR_STORAGE_DIR . '/alerts.json';
    $alerts = [];
    
    if (file_exists($alertsFile)) {
        $alerts = json_decode(file_get_contents($alertsFile), true) ?? [];
    }
    
    array_unshift($alerts, [
        'id' => uniqid('alert_'),
        'keyword' => $keyword,
        'type' => $type,
        'message' => $message,
        'data' => $data,
        'read' => false,
        'created_at' => gmdate('Y-m-d H:i:s')
    ]);
    
    // Keep only last 100 alerts
    $alerts = array_slice($alerts, 0, 100);
    
    return file_put_contents($alertsFile, json_encode($alerts, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Get competitor comparison table data
 * 
 * @param string $keyword Target keyword
 * @return array Comparison data
 */
function ai_competitor_comparison_table(string $keyword): array
{
    $data = ai_competitor_load($keyword);
    if (!$data) {
        return ['rows' => [], 'summary' => []];
    }
    
    $rows = [];
    
    // Add our content first
    if (!empty($data['our_content'])) {
        $m = $data['our_content']['metrics'] ?? [];
        $rows[] = [
            'name' => 'YOUR CONTENT',
            'url' => $data['our_content']['url'] ?? '',
            'is_us' => true,
            'word_count' => $m['word_count'] ?? 0,
            'heading_count' => $m['heading_count'] ?? 0,
            'link_count' => $m['link_count'] ?? 0,
            'image_count' => $m['image_count'] ?? 0,
            'keyword_density' => $m['keyword_density'] ?? 0,
            'seo_score' => $m['seo_score'] ?? 0
        ];
    }
    
    // Add competitors
    foreach ($data['competitors'] ?? [] as $comp) {
        $m = $comp['metrics'] ?? [];
        $rows[] = [
            'name' => $comp['title'] ?? parse_url($comp['url'], PHP_URL_HOST),
            'url' => $comp['url'] ?? '',
            'is_us' => false,
            'word_count' => $m['word_count'] ?? 0,
            'heading_count' => $m['heading_count'] ?? 0,
            'link_count' => $m['link_count'] ?? 0,
            'image_count' => $m['image_count'] ?? 0,
            'keyword_density' => $m['keyword_density'] ?? 0,
            'seo_score' => $m['seo_score'] ?? 0
        ];
    }
    
    // Sort by SEO score
    usort($rows, fn($a, $b) => $b['seo_score'] - $a['seo_score']);
    
    // Add rank
    foreach ($rows as $i => &$row) {
        $row['rank'] = $i + 1;
    }
    
    // Calculate summary/averages
    $competitors = array_filter($rows, fn($r) => !$r['is_us']);
    $compCount = count($competitors);
    
    $summary = [
        'avg_word_count' => $compCount > 0 ? round(array_sum(array_column($competitors, 'word_count')) / $compCount) : 0,
        'avg_headings' => $compCount > 0 ? round(array_sum(array_column($competitors, 'heading_count')) / $compCount, 1) : 0,
        'avg_links' => $compCount > 0 ? round(array_sum(array_column($competitors, 'link_count')) / $compCount) : 0,
        'avg_seo_score' => $compCount > 0 ? round(array_sum(array_column($competitors, 'seo_score')) / $compCount, 1) : 0,
        'total_competitors' => $compCount
    ];
    
    return ['rows' => $rows, 'summary' => $summary];
}
