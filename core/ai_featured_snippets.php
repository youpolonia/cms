<?php
/**
 * Featured Snippets Optimizer
 * Optimize content for Google's featured snippets (position zero)
 *
 * Covers snippet types:
 * - Paragraph snippets (40-60 words)
 * - List snippets (ordered/unordered)
 * - Table snippets
 * - Definition snippets
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Snippet type configurations
 */
function ai_snippets_get_types(): array
{
    return [
        'paragraph' => [
            'label' => 'Paragraph Snippet',
            'description' => 'Direct answer in 40-60 words',
            'ideal_length' => [40, 60],
            'triggers' => ['what is', 'who is', 'why is', 'how does', 'define'],
        ],
        'list_ordered' => [
            'label' => 'Numbered List',
            'description' => 'Step-by-step instructions or rankings',
            'min_items' => 3,
            'max_items' => 8,
            'triggers' => ['how to', 'steps to', 'ways to', 'top', 'best'],
        ],
        'list_unordered' => [
            'label' => 'Bullet List',
            'description' => 'Features, benefits, or items',
            'min_items' => 3,
            'max_items' => 8,
            'triggers' => ['types of', 'examples of', 'features of', 'benefits of'],
        ],
        'table' => [
            'label' => 'Table Snippet',
            'description' => 'Comparison or data table',
            'min_rows' => 2,
            'min_cols' => 2,
            'triggers' => ['vs', 'versus', 'comparison', 'compare', 'difference between'],
        ],
        'definition' => [
            'label' => 'Definition Snippet',
            'description' => 'Brief definition (15-30 words)',
            'ideal_length' => [15, 30],
            'triggers' => ['what is', 'define', 'meaning of', 'definition of'],
        ],
    ];
}

/**
 * Detect question patterns that trigger snippets
 *
 * @param string $keyword Target keyword
 * @return array Potential snippet triggers
 */
function ai_snippets_detect_triggers(string $keyword): array
{
    $kwLower = strtolower(trim($keyword));
    $triggers = [];

    $patterns = [
        'what is' => ['paragraph', 'definition'],
        'who is' => ['paragraph'],
        'why' => ['paragraph', 'list_unordered'],
        'how to' => ['list_ordered'],
        'how do' => ['list_ordered', 'paragraph'],
        'how does' => ['paragraph'],
        'steps to' => ['list_ordered'],
        'ways to' => ['list_ordered', 'list_unordered'],
        'best' => ['list_ordered', 'list_unordered'],
        'top' => ['list_ordered'],
        'types of' => ['list_unordered'],
        'examples of' => ['list_unordered'],
        'vs' => ['table'],
        'versus' => ['table'],
        'compare' => ['table'],
        'difference between' => ['table', 'list_unordered'],
        'benefits of' => ['list_unordered'],
        'advantages of' => ['list_unordered'],
        'features of' => ['list_unordered'],
    ];

    foreach ($patterns as $pattern => $types) {
        if (strpos($kwLower, $pattern) !== false) {
            $triggers[] = [
                'pattern' => $pattern,
                'snippet_types' => $types,
                'matched' => true,
            ];
        }
    }

    // If no explicit trigger, suggest based on keyword structure
    if (empty($triggers)) {
        $triggers[] = [
            'pattern' => 'informational query',
            'snippet_types' => ['paragraph', 'list_unordered'],
            'matched' => false,
        ];
    }

    return $triggers;
}

/**
 * Extract paragraph candidates for snippets
 *
 * @param string $html HTML content
 * @return array Paragraph candidates
 */
function ai_snippets_extract_paragraphs(string $html): array
{
    $candidates = [];

    // Extract paragraphs
    preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches);

    foreach ($matches[1] as $index => $para) {
        $text = trim(strip_tags($para));
        $wordCount = str_word_count($text);

        if ($wordCount < 20 || $wordCount > 100) {
            continue;
        }

        // Check if it starts with definition-like patterns
        $isDefinition = preg_match('/^[A-Z][^.]+\s+(is|are|refers to|means|describes)\s+/i', $text);

        // Check sentence structure (good snippets are complete statements)
        $endsWithPeriod = preg_match('/[.!?]$/', $text);

        // Score the paragraph
        $score = 0;

        // Ideal word count for paragraph snippet (40-60)
        if ($wordCount >= 40 && $wordCount <= 60) {
            $score += 30;
        } elseif ($wordCount >= 30 && $wordCount <= 70) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Definition pattern bonus
        if ($isDefinition) {
            $score += 25;
        }

        // Complete sentence bonus
        if ($endsWithPeriod) {
            $score += 15;
        }

        // Position bonus (earlier paragraphs preferred)
        if ($index < 3) {
            $score += 10;
        }

        $candidates[] = [
            'text' => $text,
            'word_count' => $wordCount,
            'position' => $index + 1,
            'is_definition' => $isDefinition,
            'score' => $score,
            'type' => 'paragraph',
        ];
    }

    // Sort by score
    usort($candidates, fn($a, $b) => $b['score'] - $a['score']);

    return array_slice($candidates, 0, 5);
}

/**
 * Extract list candidates for snippets
 *
 * @param string $html HTML content
 * @return array List candidates
 */
function ai_snippets_extract_lists(string $html): array
{
    $candidates = [];

    // Extract ordered lists
    preg_match_all('/<ol[^>]*>(.*?)<\/ol>/is', $html, $olMatches);
    foreach ($olMatches[1] as $list) {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $list, $items);
        $itemTexts = array_map(fn($i) => trim(strip_tags($i)), $items[1]);
        $itemTexts = array_filter($itemTexts);

        if (count($itemTexts) >= 3) {
            $score = min(100, count($itemTexts) * 10 + 30);
            $candidates[] = [
                'items' => array_values($itemTexts),
                'item_count' => count($itemTexts),
                'type' => 'list_ordered',
                'score' => $score,
            ];
        }
    }

    // Extract unordered lists
    preg_match_all('/<ul[^>]*>(.*?)<\/ul>/is', $html, $ulMatches);
    foreach ($ulMatches[1] as $list) {
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $list, $items);
        $itemTexts = array_map(fn($i) => trim(strip_tags($i)), $items[1]);
        $itemTexts = array_filter($itemTexts);

        if (count($itemTexts) >= 3) {
            $score = min(100, count($itemTexts) * 10 + 20);
            $candidates[] = [
                'items' => array_values($itemTexts),
                'item_count' => count($itemTexts),
                'type' => 'list_unordered',
                'score' => $score,
            ];
        }
    }

    usort($candidates, fn($a, $b) => $b['score'] - $a['score']);

    return array_slice($candidates, 0, 3);
}

/**
 * Extract table candidates for snippets
 *
 * @param string $html HTML content
 * @return array Table candidates
 */
function ai_snippets_extract_tables(string $html): array
{
    $candidates = [];

    preg_match_all('/<table[^>]*>(.*?)<\/table>/is', $html, $tables);

    foreach ($tables[1] as $table) {
        // Count rows
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $table, $rows);
        $rowCount = count($rows[1]);

        if ($rowCount < 2) {
            continue;
        }

        // Extract header
        $headers = [];
        if (preg_match('/<thead[^>]*>(.*?)<\/thead>/is', $table, $thead)) {
            preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $thead[1], $ths);
            $headers = array_map(fn($h) => trim(strip_tags($h)), $ths[1]);
        } elseif (preg_match('/<tr[^>]*>(.*?)<\/tr>/is', $table, $firstRow)) {
            preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $firstRow[1], $ths);
            if (!empty($ths[1])) {
                $headers = array_map(fn($h) => trim(strip_tags($h)), $ths[1]);
            }
        }

        $colCount = count($headers) ?: 2;

        $score = 50;
        if ($rowCount >= 3 && $rowCount <= 10) {
            $score += 20;
        }
        if ($colCount >= 2 && $colCount <= 5) {
            $score += 20;
        }
        if (!empty($headers)) {
            $score += 10;
        }

        $candidates[] = [
            'row_count' => $rowCount,
            'col_count' => $colCount,
            'headers' => $headers,
            'type' => 'table',
            'score' => $score,
        ];
    }

    usort($candidates, fn($a, $b) => $b['score'] - $a['score']);

    return array_slice($candidates, 0, 2);
}

/**
 * Check for definition-style content
 *
 * @param string $html HTML content
 * @param string $keyword Target keyword
 * @return array Definition candidates
 */
function ai_snippets_extract_definitions(string $html, string $keyword): array
{
    $candidates = [];
    $kwLower = strtolower($keyword);

    // Pattern: "X is/are ..."
    $patterns = [
        '/\b' . preg_quote($keyword, '/') . '\s+(is|are|refers to|means|can be defined as)\s+([^.]+\.)/i',
        '/<strong>' . preg_quote($keyword, '/') . '<\/strong>\s*[:\-]?\s*([^<.]+\.)/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $match)) {
            $definition = isset($match[2]) ? $match[2] : $match[1];
            $definition = trim(strip_tags($definition));
            $wordCount = str_word_count($definition);

            if ($wordCount >= 10 && $wordCount <= 50) {
                $score = 70;
                if ($wordCount >= 15 && $wordCount <= 30) {
                    $score += 20;
                }

                $candidates[] = [
                    'text' => $definition,
                    'word_count' => $wordCount,
                    'keyword' => $keyword,
                    'type' => 'definition',
                    'score' => $score,
                ];
            }
        }
    }

    return $candidates;
}

/**
 * Analyze content for snippet opportunities
 *
 * @param string $html HTML content
 * @param string $keyword Target keyword
 * @return array Complete snippet analysis
 */
function ai_snippets_analyze(string $html, string $keyword = ''): array
{
    if (empty(trim(strip_tags($html)))) {
        return [
            'ok' => false,
            'error' => 'No content to analyze',
        ];
    }

    $triggers = ai_snippets_detect_triggers($keyword);
    $paragraphs = ai_snippets_extract_paragraphs($html);
    $lists = ai_snippets_extract_lists($html);
    $tables = ai_snippets_extract_tables($html);
    $definitions = ai_snippets_extract_definitions($html, $keyword);

    // Determine best opportunities
    $opportunities = [];

    // Best paragraph
    if (!empty($paragraphs)) {
        $best = $paragraphs[0];
        $opportunities[] = [
            'type' => 'paragraph',
            'score' => $best['score'],
            'content' => mb_substr($best['text'], 0, 200) . (mb_strlen($best['text']) > 200 ? '...' : ''),
            'word_count' => $best['word_count'],
            'status' => $best['score'] >= 60 ? 'good' : ($best['score'] >= 40 ? 'fair' : 'needs_work'),
        ];
    }

    // Best list
    if (!empty($lists)) {
        $best = $lists[0];
        $opportunities[] = [
            'type' => $best['type'],
            'score' => $best['score'],
            'item_count' => $best['item_count'],
            'items_preview' => array_slice($best['items'], 0, 3),
            'status' => $best['score'] >= 60 ? 'good' : ($best['score'] >= 40 ? 'fair' : 'needs_work'),
        ];
    }

    // Best table
    if (!empty($tables)) {
        $best = $tables[0];
        $opportunities[] = [
            'type' => 'table',
            'score' => $best['score'],
            'dimensions' => "{$best['row_count']} rows × {$best['col_count']} cols",
            'headers' => $best['headers'],
            'status' => $best['score'] >= 60 ? 'good' : ($best['score'] >= 40 ? 'fair' : 'needs_work'),
        ];
    }

    // Definition
    if (!empty($definitions)) {
        $best = $definitions[0];
        $opportunities[] = [
            'type' => 'definition',
            'score' => $best['score'],
            'content' => $best['text'],
            'word_count' => $best['word_count'],
            'status' => $best['score'] >= 60 ? 'good' : ($best['score'] >= 40 ? 'fair' : 'needs_work'),
        ];
    }

    // Sort opportunities by score
    usort($opportunities, fn($a, $b) => $b['score'] - $a['score']);

    // Calculate overall snippet readiness
    $topScore = !empty($opportunities) ? $opportunities[0]['score'] : 0;
    $hasGoodOpportunity = !empty(array_filter($opportunities, fn($o) => $o['status'] === 'good'));

    $readinessScore = 0;
    if ($topScore >= 70) {
        $readinessScore = 90;
    } elseif ($topScore >= 50) {
        $readinessScore = 70;
    } elseif ($topScore >= 30) {
        $readinessScore = 50;
    } else {
        $readinessScore = 30;
    }

    // Generate recommendations
    $recommendations = ai_snippets_generate_recommendations($paragraphs, $lists, $tables, $definitions, $keyword);

    return [
        'ok' => true,
        'keyword' => $keyword,
        'readiness_score' => $readinessScore,
        'has_good_opportunity' => $hasGoodOpportunity,
        'triggers' => $triggers,
        'opportunities' => $opportunities,
        'candidates' => [
            'paragraphs' => count($paragraphs),
            'lists' => count($lists),
            'tables' => count($tables),
            'definitions' => count($definitions),
        ],
        'recommendations' => $recommendations,
    ];
}

/**
 * Generate snippet optimization recommendations
 *
 * @param array $paragraphs Paragraph candidates
 * @param array $lists List candidates
 * @param array $tables Table candidates
 * @param array $definitions Definition candidates
 * @param string $keyword Target keyword
 * @return array Recommendations
 */
function ai_snippets_generate_recommendations(array $paragraphs, array $lists, array $tables, array $definitions, string $keyword): array
{
    $recommendations = [];

    // Paragraph recommendations
    if (empty($paragraphs)) {
        $recommendations[] = [
            'type' => 'paragraph',
            'priority' => 'high',
            'issue' => 'No snippet-worthy paragraphs found',
            'action' => 'Add a 40-60 word paragraph that directly answers "What is [topic]?"',
        ];
    } else {
        $best = $paragraphs[0];
        if ($best['word_count'] < 40) {
            $recommendations[] = [
                'type' => 'paragraph',
                'priority' => 'medium',
                'issue' => 'Best paragraph is too short (' . $best['word_count'] . ' words)',
                'action' => 'Expand to 40-60 words for optimal snippet length',
            ];
        } elseif ($best['word_count'] > 60) {
            $recommendations[] = [
                'type' => 'paragraph',
                'priority' => 'medium',
                'issue' => 'Best paragraph is too long (' . $best['word_count'] . ' words)',
                'action' => 'Condense to 40-60 words for optimal snippet length',
            ];
        }
        if (!$best['is_definition']) {
            $recommendations[] = [
                'type' => 'paragraph',
                'priority' => 'low',
                'issue' => 'No definition-style paragraph',
                'action' => 'Start a paragraph with "[Topic] is..." for better snippet eligibility',
            ];
        }
    }

    // List recommendations
    if (empty($lists)) {
        $recommendations[] = [
            'type' => 'list',
            'priority' => 'medium',
            'issue' => 'No lists found in content',
            'action' => 'Add a numbered or bulleted list with 3-8 items',
        ];
    } else {
        $best = $lists[0];
        if ($best['item_count'] < 3) {
            $recommendations[] = [
                'type' => 'list',
                'priority' => 'low',
                'issue' => 'List has too few items',
                'action' => 'Expand list to at least 3-5 items',
            ];
        }
    }

    // Table recommendations for comparison keywords
    $kwLower = strtolower($keyword);
    $isComparison = strpos($kwLower, 'vs') !== false ||
                    strpos($kwLower, 'compare') !== false ||
                    strpos($kwLower, 'difference') !== false;

    if ($isComparison && empty($tables)) {
        $recommendations[] = [
            'type' => 'table',
            'priority' => 'high',
            'issue' => 'Comparison keyword but no table found',
            'action' => 'Add a comparison table with clear headers',
        ];
    }

    // Keyword placement
    if (!empty($keyword)) {
        $recommendations[] = [
            'type' => 'general',
            'priority' => 'high',
            'issue' => 'Keyword placement',
            'action' => "Ensure '{$keyword}' appears in the first paragraph and at least one H2",
        ];
    }

    // Sort by priority
    usort($recommendations, function($a, $b) {
        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        return ($order[$a['priority']] ?? 3) - ($order[$b['priority']] ?? 3);
    });

    return $recommendations;
}

/**
 * Generate snippet-optimized content template
 *
 * @param string $keyword Target keyword
 * @param string $type Snippet type to optimize for
 * @return string Template/suggestion
 */
function ai_snippets_generate_template(string $keyword, string $type = 'paragraph'): string
{
    $kw = ucwords($keyword);
    $kwLower = strtolower($keyword);

    switch ($type) {
        case 'paragraph':
            return "{$kw} is [definition in 40-60 words]. It [key benefit/purpose]. [Supporting detail]. This makes it [value proposition/conclusion].";

        case 'list_ordered':
            return "How to {$kwLower}:\n1. [First step]\n2. [Second step]\n3. [Third step]\n4. [Fourth step]\n5. [Fifth step]";

        case 'list_unordered':
            return "Key features of {$kwLower}:\n• [Feature 1]\n• [Feature 2]\n• [Feature 3]\n• [Feature 4]\n• [Feature 5]";

        case 'table':
            return "| Feature | Option A | Option B |\n|---------|----------|----------|\n| [Criterion 1] | [Value] | [Value] |\n| [Criterion 2] | [Value] | [Value] |\n| [Criterion 3] | [Value] | [Value] |";

        case 'definition':
            return "{$kw} is [15-30 word definition that directly answers what it is, includes key characteristic, and mentions primary use/benefit].";

        default:
            return '';
    }
}

/**
 * Get readiness label
 *
 * @param int $score Readiness score
 * @return array Label and color
 */
function ai_snippets_get_readiness_label(int $score): array
{
    if ($score >= 80) {
        return ['label' => 'Snippet Ready', 'color' => 'success'];
    } elseif ($score >= 60) {
        return ['label' => 'Good Potential', 'color' => 'primary'];
    } elseif ($score >= 40) {
        return ['label' => 'Needs Work', 'color' => 'warning'];
    } else {
        return ['label' => 'Not Optimized', 'color' => 'danger'];
    }
}
