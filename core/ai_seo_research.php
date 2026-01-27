<?php
/**
 * AI SEO Research - Keyword & Content Research Engine
 * 
 * Features:
 * - SERP Analysis (Top 20 results)
 * - Keyword Extraction (TF-IDF)
 * - N-gram Analysis (2-3 word phrases)
 * - NLP Terms Detection
 * - Content Brief Generation
 * 
 * Similar to: NeuronWriter, Surfer SEO, Frase
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Storage for research data
define('SEO_RESEARCH_DIR', CMS_ROOT . '/cms_storage/seo-research');

/**
 * Ensure storage directory exists
 */
function seo_research_ensure_storage(): bool
{
    if (!is_dir(SEO_RESEARCH_DIR)) {
        return mkdir(SEO_RESEARCH_DIR, 0755, true);
    }
    return true;
}

/**
 * Search the web using DuckDuckGo (no API key needed)
 * 
 * @param string $keyword Search query
 * @param int $limit Number of results
 * @return array Search results
 */
function seo_research_search_web(string $keyword, int $limit = 20): array
{
    $results = [];
    
    // DuckDuckGo HTML search (scraping)
    $query = urlencode($keyword);
    $url = "https://html.duckduckgo.com/html/?q=" . $query;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 20,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'header' => "Accept: text/html,application/xhtml+xml\r\nAccept-Language: en-US,en;q=0.9\r\n"
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $html = @file_get_contents($url, false, $context);
    
    if ($html === false) {
        return ['error' => 'Failed to fetch search results', 'results' => []];
    }
    
    // Parse DuckDuckGo results
    preg_match_all('/<a[^>]+class="result__a"[^>]+href="([^"]+)"[^>]*>([^<]+)</i', $html, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $i => $resultUrl) {
            if (count($results) >= $limit) break;
            
            // Skip DuckDuckGo redirect URLs, extract actual URL
            if (strpos($resultUrl, 'uddg=') !== false) {
                parse_str(parse_url($resultUrl, PHP_URL_QUERY), $params);
                $resultUrl = urldecode($params['uddg'] ?? $resultUrl);
            }
            
            // Skip non-http URLs and ads
            if (strpos($resultUrl, 'http') !== 0) continue;
            if (strpos($resultUrl, 'duckduckgo.com') !== false) continue;
            
            $results[] = [
                'url' => $resultUrl,
                'title' => strip_tags($matches[2][$i] ?? ''),
                'position' => count($results) + 1
            ];
        }
    }
    
    return ['error' => null, 'results' => $results, 'query' => $keyword];
}

/**
 * Filter out junk headings from competitor pages
 * 
 * @param string $heading Heading text to validate
 * @return bool True if valid heading, false if junk
 */
function seo_research_is_valid_heading(string $heading): bool
{
    $lower = strtolower(trim($heading));
    
    // Too short or too long
    if (strlen($heading) < 5 || strlen($heading) > 100) {
        return false;
    }
    
    // Junk patterns - website UI elements
    $junkPatterns = [
        '/^(leave\s+a?\s*)?(reply|comment|review)/i',
        '/^(recent|latest|related|popular)\s+(posts?|comments?|articles?)/i',
        '/^(categories|tags|archives|sidebar|widget)/i',
        '/^(about|contact)\s*(us|me|the\s+author)?$/i',
        '/^(search|subscribe|newsletter|sign\s*up|log\s*in|register)/i',
        '/^(share|follow|connect)\s*(this|us|on|with)?/i',
        '/^(cancel|submit|send|post|save|delete|edit|update)/i',
        '/^(privacy|terms|cookie|disclaimer|copyright)/i',
        '/^(navigation|menu|footer|header|sidebar)/i',
        '/^(advertisement|sponsored|promo)/i',
        '/^(table\s+of\s+contents?|toc|contents?)$/i',
        '/^(you\s+may\s+also\s+like|see\s+also|more\s+from)/i',
        '/^(next|previous|older|newer)\s+(post|article|page)?/i',
        '/^(page|post)\s*\d+/i',
        '/(cancel\s+reply|leave\s+a\s+comment)/i',
    ];
    
    foreach ($junkPatterns as $pattern) {
        if (preg_match($pattern, $lower)) {
            return false;
        }
    }
    
    // Numbered steps (Step 1:, 1., #1, etc.) - remove number prefix but keep if content is good
    if (preg_match('/^(step\s*)?\d+[\.\:\)\-]\s*/i', $heading)) {
        // Remove the number prefix for checking
        $withoutNumber = preg_replace('/^(step\s*)?\d+[\.\:\)\-]\s*/i', '', $heading);
        if (strlen($withoutNumber) < 5) {
            return false;
        }
    }
    
    // Contains only numbers or special chars
    if (preg_match('/^[\d\s\.\-\:\#]+$/', $heading)) {
        return false;
    }
    
    // Single word (usually not useful as a heading)
    if (!str_contains($heading, ' ') && strlen($heading) < 15) {
        return false;
    }
    
    return true;
}

/**
 * Clean heading text - remove numbering but keep content
 * 
 * @param string $heading Raw heading text
 * @return string Cleaned heading
 */
function seo_research_clean_heading(string $heading): string
{
    // Remove step numbers (Step 1:, 1., #1, etc.)
    $clean = preg_replace('/^(step\s*)?\#?\d+[\.\:\)\-]\s*/i', '', $heading);
    
    // Remove trailing junk
    $clean = preg_replace('/\s*[\(\[].*[\)\]]$/', '', $clean);
    
    return trim($clean);
}

/**
 * Fetch and extract text content from URL
 * 
 * @param string $url URL to fetch
 * @return array Extracted content
 */
function seo_research_fetch_content(string $url): array
{
    $result = [
        'success' => false,
        'url' => $url,
        'title' => '',
        'headings' => [],
        'text' => '',
        'word_count' => 0,
        'error' => null
    ];
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'header' => "Accept: text/html,application/xhtml+xml\r\nAccept-Language: en-US,en;q=0.9\r\n",
            'follow_location' => true,
            'max_redirects' => 3
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
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
        $result['title'] = trim(strip_tags(html_entity_decode($m[1])));
    }
    
    // Extract headings
    preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $headingMatches);
    foreach ($headingMatches[2] ?? [] as $i => $h) {
        $text = trim(strip_tags(html_entity_decode($h)));
        if (!empty($text) && strlen($text) > 2) {
            // Filter out junk headings
            if (seo_research_is_valid_heading($text)) {
                $cleanText = seo_research_clean_heading($text);
                if (strlen($cleanText) >= 5) {
                    $result['headings'][] = [
                        'level' => (int)$headingMatches[1][$i],
                        'text' => $cleanText
                    ];
                }
            }
        }
    }
    
    // Remove scripts, styles, nav, footer, etc.
    $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
    $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
    $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
    $html = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $html);
    $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
    
    // Get main content
    $text = strip_tags($html);
    $text = html_entity_decode($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    $result['text'] = $text;
    $result['word_count'] = str_word_count($text);
    $result['success'] = true;
    
    return $result;
}

/**
 * Extract keywords using TF-IDF-like scoring
 * 
 * @param array $documents Array of text content from multiple pages
 * @param string $targetKeyword The main keyword
 * @return array Extracted keywords with scores
 */
function seo_research_extract_keywords(array $documents, string $targetKeyword): array
{
    $allWords = [];
    $docCount = count($documents);
    $docFrequency = []; // How many docs contain each word
    $termFrequency = []; // Total frequency across all docs
    
    // Stopwords to exclude
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
        'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been', 'be',
        'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
        'may', 'might', 'must', 'shall', 'can', 'need', 'dare', 'ought', 'used',
        'it', 'its', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'we', 'they',
        'what', 'which', 'who', 'whom', 'when', 'where', 'why', 'how', 'all', 'each',
        'every', 'both', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
        'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'also',
        'now', 'here', 'there', 'then', 'once', 'if', 'about', 'into', 'through',
        'during', 'before', 'after', 'above', 'below', 'between', 'under', 'again',
        'further', 'while', 'your', 'our', 'their', 'my', 'his', 'her', 'out', 'up',
        'any', 'get', 'got', 'one', 'two', 'first', 'new', 'like', 'make', 'made',
        'know', 'see', 'look', 'think', 'come', 'want', 'use', 'find', 'give', 'tell',
        'work', 'because', 'way', 'even', 'back', 'well', 'being', 'over', 'years',
        'many', 'still', 'take', 'us', 'amp', 'nbsp', 'http', 'https', 'www', 'com'
    ];
    
    foreach ($documents as $docIndex => $doc) {
        $text = strtolower($doc['text'] ?? '');
        
        // Extract words (only letters, min 3 chars)
        preg_match_all('/\b([a-z]{3,})\b/', $text, $matches);
        $words = $matches[1] ?? [];
        
        $docWords = [];
        foreach ($words as $word) {
            if (in_array($word, $stopwords)) continue;
            if (is_numeric($word)) continue;
            
            $docWords[$word] = true;
            $termFrequency[$word] = ($termFrequency[$word] ?? 0) + 1;
        }
        
        // Count document frequency
        foreach (array_keys($docWords) as $word) {
            $docFrequency[$word] = ($docFrequency[$word] ?? 0) + 1;
        }
    }
    
    // Calculate TF-IDF-like score
    $keywords = [];
    foreach ($termFrequency as $word => $tf) {
        $df = $docFrequency[$word] ?? 1;
        
        // Words appearing in more documents are more important for SEO
        // But not if they appear in ALL documents (too common)
        $coverage = $df / $docCount;
        
        if ($coverage >= 0.3 && $coverage <= 0.95) {
            // Good coverage - appears in 30-95% of top results
            $score = $tf * (1 + $coverage);
        } elseif ($coverage > 0.95) {
            // Too common
            $score = $tf * 0.5;
        } else {
            // Too rare
            $score = $tf * $coverage;
        }
        
        $keywords[$word] = [
            'word' => $word,
            'frequency' => $tf,
            'doc_frequency' => $df,
            'coverage' => round($coverage * 100, 1),
            'score' => round($score, 2)
        ];
    }
    
    // Sort by score
    uasort($keywords, fn($a, $b) => $b['score'] <=> $a['score']);
    
    return array_slice($keywords, 0, 100);
}

/**
 * Extract n-grams (phrases) from documents
 * 
 * @param array $documents Array of text content
 * @param int $n N-gram size (2 or 3)
 * @return array Phrases with frequencies
 */
function seo_research_extract_ngrams(array $documents, int $n = 2): array
{
    $ngrams = [];
    $docCount = count($documents);
    $docFrequency = [];
    
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
        'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be', 'it', 'this', 'that'];
    
    foreach ($documents as $doc) {
        $text = strtolower($doc['text'] ?? '');
        
        // Get words
        preg_match_all('/\b([a-z]{2,})\b/', $text, $matches);
        $words = $matches[1] ?? [];
        
        $docNgrams = [];
        
        for ($i = 0; $i <= count($words) - $n; $i++) {
            $phrase = array_slice($words, $i, $n);
            
            // Skip if starts or ends with stopword
            if (in_array($phrase[0], $stopwords) || in_array($phrase[$n-1], $stopwords)) {
                continue;
            }
            
            $phraseStr = implode(' ', $phrase);
            
            // Skip very short phrases
            if (strlen($phraseStr) < 5) continue;
            
            $ngrams[$phraseStr] = ($ngrams[$phraseStr] ?? 0) + 1;
            $docNgrams[$phraseStr] = true;
        }
        
        foreach (array_keys($docNgrams) as $phrase) {
            $docFrequency[$phrase] = ($docFrequency[$phrase] ?? 0) + 1;
        }
    }
    
    // Filter and score
    $result = [];
    foreach ($ngrams as $phrase => $freq) {
        if ($freq < 3) continue; // Must appear at least 3 times
        
        $df = $docFrequency[$phrase] ?? 1;
        $coverage = $df / $docCount;
        
        // Phrases appearing in multiple docs are valuable
        if ($coverage >= 0.2) {
            $result[$phrase] = [
                'phrase' => $phrase,
                'frequency' => $freq,
                'doc_frequency' => $df,
                'coverage' => round($coverage * 100, 1),
                'score' => round($freq * (1 + $coverage), 2)
            ];
        }
    }
    
    uasort($result, fn($a, $b) => $b['score'] <=> $a['score']);
    
    return array_slice($result, 0, 50);
}

/**
 * Extract common headings/topics from competitors
 * 
 * @param array $documents Array with headings
 * @return array Common headings
 */
function seo_research_extract_headings(array $documents): array
{
    $headings = [];
    $docCount = count($documents);
    
    foreach ($documents as $doc) {
        $seen = [];
        foreach ($doc['headings'] ?? [] as $h) {
            // Clean heading - remove step numbers but keep content
            $cleanText = seo_research_clean_heading($h['text']);
            $text = strtolower(trim($cleanText));
            
            // Skip very short or generic headings
            if (strlen($text) < 5 || isset($seen[$text])) continue;
            // Skip common generic/junk headings
            $junkPatterns = [
                'contact', 'contact us', 'about', 'about us', 'categories', 'tags', 
                'related', 'comments', 'leave a reply', 'leave a comment', 'cancel reply',
                'share', 'follow us', 'subscribe', 'newsletter', 'sidebar', 'footer', 
                'header', 'menu', 'search', 'login', 'register', 'cart', 'checkout', 
                'privacy', 'terms', 'sitemap', 'archives', 'recent posts', 'popular posts',
                'recent comments', 'related posts', 'you may also like', 'see also',
                'table of contents', 'contents', 'navigation', 'advertisement',
                'sponsored', 'next post', 'previous post', 'older posts', 'newer posts'
            ];
            if (in_array($text, $junkPatterns)) continue;
            // Skip headings with "reply" or "comment" in them
            if (preg_match('/(reply|comment|cancel|submit)/i', $text)) continue;
            
            $seen[$text] = true;
            
            if (!isset($headings[$text])) {
                $headings[$text] = [
                    'text' => $cleanText, // Use cleaned text
                    'level' => $h['level'],
                    'count' => 0
                ];
            }
            $headings[$text]['count']++;
        }
    }
    
    // Include all headings (removed >= 2 filter)
    $result = $headings;
    
    // Sort by count (most common first)
    uasort($result, fn($a, $b) => $b['count'] <=> $a['count']);
    
    return array_slice($result, 0, 30);
}


/**
 * Calculate recommended word count based on competitors
 * 
 * @param array $documents Analyzed documents
 * @return array Word count recommendations
 */
function seo_research_word_count_analysis(array $documents): array
{
    $wordCounts = array_filter(array_column($documents, 'word_count'));
    
    if (empty($wordCounts)) {
        return ['min' => 1000, 'avg' => 1500, 'max' => 2000, 'recommended' => 1500];
    }
    
    sort($wordCounts);
    $count = count($wordCounts);
    
    return [
        'min' => min($wordCounts),
        'max' => max($wordCounts),
        'avg' => round(array_sum($wordCounts) / $count),
        'median' => $wordCounts[floor($count / 2)],
        'recommended' => round(array_sum($wordCounts) / $count * 1.1), // 10% more than average
        'top3_avg' => round(array_sum(array_slice($wordCounts, -3)) / 3)
    ];
}

/**
 * Generate content brief from research
 * 
 * @param string $keyword Main keyword
 * @param array $keywords Extracted keywords
 * @param array $phrases Extracted phrases
 * @param array $headings Common headings
 * @param array $wordCountAnalysis Word count analysis
 * @return array Content brief
 */
function seo_research_generate_brief(
    string $keyword,
    array $keywords,
    array $phrases,
    array $headings,
    array $wordCountAnalysis
): array {
    // Categorize keywords
    $mustUse = [];
    $shouldUse = [];
    $niceToHave = [];
    
    $i = 0;
    foreach ($keywords as $kw) {
        if ($i < 10 || $kw['coverage'] >= 70) {
            $mustUse[] = $kw['word'];
        } elseif ($i < 30 || $kw['coverage'] >= 50) {
            $shouldUse[] = $kw['word'];
        } else {
            $niceToHave[] = $kw['word'];
        }
        $i++;
    }
    
    // Get top phrases
    $topPhrases = array_slice(array_column($phrases, 'phrase'), 0, 15);
    
    // Get recommended headings
    $recommendedHeadings = [];
    foreach (array_slice($headings, 0, 10) as $h) {
        $recommendedHeadings[] = $h['text'];
    }
    
    return [
        'keyword' => $keyword,
        'word_count' => [
            'minimum' => $wordCountAnalysis['avg'],
            'recommended' => $wordCountAnalysis['recommended'],
            'optimal' => $wordCountAnalysis['top3_avg']
        ],
        'keywords' => [
            'must_use' => $mustUse,
            'should_use' => $shouldUse,
            'nice_to_have' => array_slice($niceToHave, 0, 20)
        ],
        'phrases' => $topPhrases,
        'recommended_headings' => $recommendedHeadings,
        'structure' => [
            'h2_count' => max(5, count($recommendedHeadings)),
            'paragraphs' => ceil($wordCountAnalysis['recommended'] / 150),
            'images' => ceil($wordCountAnalysis['recommended'] / 500)
        ]
    ];
}

/**
 * Run full research for a keyword
 * 
 * @param string $keyword Search keyword
 * @param int $limit Number of results to analyze
 * @return array Complete research data
 */
function seo_research_run(string $keyword, int $limit = 15): array
{
    seo_research_ensure_storage();
    
    $result = [
        'keyword' => $keyword,
        'status' => 'running',
        'started_at' => gmdate('Y-m-d H:i:s'),
        'serp_results' => [],
        'analyzed_pages' => [],
        'keywords' => [],
        'phrases_2gram' => [],
        'phrases_3gram' => [],
        'headings' => [],
        'word_count_analysis' => [],
        'brief' => [],
        'errors' => []
    ];
    
    // Step 1: Search the web
    $searchResults = seo_research_search_web($keyword, $limit);
    
    if ($searchResults['error']) {
        $result['status'] = 'error';
        $result['errors'][] = $searchResults['error'];
        return $result;
    }
    
    $result['serp_results'] = $searchResults['results'];
    
    // Step 2: Fetch and analyze each page
    $documents = [];
    foreach ($searchResults['results'] as $serp) {
        $content = seo_research_fetch_content($serp['url']);
        
        if ($content['success']) {
            $content['position'] = $serp['position'];
            $documents[] = $content;
            $result['analyzed_pages'][] = [
                'url' => $serp['url'],
                'title' => $content['title'],
                'word_count' => $content['word_count'],
                'headings_count' => count($content['headings']),
                'position' => $serp['position']
            ];
        } else {
            $result['errors'][] = "Failed to fetch: " . $serp['url'];
        }
    }
    
    if (empty($documents)) {
        $result['status'] = 'error';
        $result['errors'][] = 'No pages could be analyzed';
        return $result;
    }
    
    // Step 3: Extract keywords
    $result['keywords'] = seo_research_extract_keywords($documents, $keyword);
    
    // Step 4: Extract n-grams
    $result['phrases_2gram'] = seo_research_extract_ngrams($documents, 2);
    $result['phrases_3gram'] = seo_research_extract_ngrams($documents, 3);
    
    // Step 5: Extract headings
    $result['headings'] = seo_research_extract_headings($documents);
    
    // Step 6: Word count analysis
    $result['word_count_analysis'] = seo_research_word_count_analysis($documents);
    
    // Step 7: Generate brief
    $result['brief'] = seo_research_generate_brief(
        $keyword,
        $result['keywords'],
        $result['phrases_2gram'],
        $result['headings'],
        $result['word_count_analysis']
    );
    
    $result['status'] = 'completed';
    $result['completed_at'] = gmdate('Y-m-d H:i:s');
    $result['pages_analyzed'] = count($documents);
    
    // Save research
    seo_research_save($keyword, $result);
    
    return $result;
}

/**
 * Save research data
 */
function seo_research_save(string $keyword, array $data): bool
{
    seo_research_ensure_storage();
    
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($keyword)));
    $path = SEO_RESEARCH_DIR . '/' . $slug . '.json';
    
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Load research data
 */
function seo_research_load(string $keyword): ?array
{
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($keyword)));
    $path = SEO_RESEARCH_DIR . '/' . $slug . '.json';
    
    if (!file_exists($path)) {
        return null;
    }
    
    return json_decode(file_get_contents($path), true);
}

/**
 * List all research
 */
function seo_research_list(): array
{
    seo_research_ensure_storage();
    
    $files = glob(SEO_RESEARCH_DIR . '/*.json');
    $list = [];
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $list[] = [
                'keyword' => $data['keyword'] ?? basename($file, '.json'),
                'status' => $data['status'] ?? 'unknown',
                'pages_analyzed' => $data['pages_analyzed'] ?? 0,
                'keywords_found' => count($data['keywords'] ?? []),
                'completed_at' => $data['completed_at'] ?? null
            ];
        }
    }
    
    // Sort by date
    usort($list, fn($a, $b) => strcmp($b['completed_at'] ?? '', $a['completed_at'] ?? ''));
    
    return $list;
}

/**
 * Delete research
 */
function seo_research_delete(string $keyword): bool
{
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($keyword)));
    $path = SEO_RESEARCH_DIR . '/' . $slug . '.json';
    
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

/**
 * Generate prompt for AI content generation based on brief
 */
function seo_research_generate_prompt(array $brief): string
{
    $prompt = "Write a comprehensive article about \"{$brief['keyword']}\".\n\n";
    $prompt .= "TARGET LENGTH: {$brief['word_count']['recommended']} words (minimum {$brief['word_count']['minimum']})\n\n";
    
    $prompt .= "MUST USE these keywords (include each at least once):\n";
    $prompt .= implode(', ', $brief['keywords']['must_use']) . "\n\n";
    
    $prompt .= "SHOULD USE these keywords:\n";
    $prompt .= implode(', ', $brief['keywords']['should_use']) . "\n\n";
    
    $prompt .= "USE these phrases naturally:\n";
    $prompt .= implode(', ', $brief['phrases']) . "\n\n";
    
    $prompt .= "RECOMMENDED HEADINGS (H2):\n";
    foreach ($brief['recommended_headings'] as $h) {
        $prompt .= "- " . $h . "\n";
    }
    
    $prompt .= "\nSTRUCTURE:\n";
    $prompt .= "- Include {$brief['structure']['h2_count']} main sections (H2)\n";
    $prompt .= "- Write approximately {$brief['structure']['paragraphs']} paragraphs\n";
    $prompt .= "- Suggest {$brief['structure']['images']} image placements\n";
    
    return $prompt;
}
