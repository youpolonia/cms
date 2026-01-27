<?php
/**
 * Content Quality Analyzer
 * AI Detection, Duplicate Check, Readability Score, Humanize feature
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/ai_content.php';
require_once __DIR__ . '/../core/ai_models.php';

if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$aiConfig = ai_config_load();
$aiConfigured = !empty($aiConfig['api_key']);

// ============================================================================
// QUALITY ANALYSIS FUNCTIONS
// ============================================================================

/**
 * Strip markdown formatting from text for accurate analysis
 */
function strip_markdown(string $text): string {
    // Remove code blocks
    $text = preg_replace('/```[\s\S]*?```/', '', $text);
    $text = preg_replace('/`[^`]+`/', '', $text);
    
    // Remove headings (## Heading -> Heading)
    $text = preg_replace('/^#{1,6}\s*/m', '', $text);
    
    // Remove bold/italic
    $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);
    $text = preg_replace('/\*([^*]+)\*/', '$1', $text);
    $text = preg_replace('/__([^_]+)__/', '$1', $text);
    $text = preg_replace('/_([^_]+)_/', '$1', $text);
    
    // Remove links [text](url) -> text
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
    
    // Remove images ![alt](url)
    $text = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $text);
    
    // Remove blockquotes
    $text = preg_replace('/^>\s*/m', '', $text);
    
    // Remove horizontal rules
    $text = preg_replace('/^[-*_]{3,}\s*$/m', '', $text);
    
    // Remove list markers
    $text = preg_replace('/^[\s]*[-*+]\s+/m', '', $text);
    $text = preg_replace('/^[\s]*\d+\.\s+/m', '', $text);
    
    // Clean up extra whitespace
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    $text = trim($text);
    
    return $text;
}

/**
 * Analyze content for AI patterns (heuristic detection)
 */
function analyze_ai_patterns(string $text): array {
    // Strip markdown before analysis
    $text = strip_markdown($text);
    
    $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $sentences = array_filter(array_map('trim', $sentences));
    $sentenceCount = count($sentences);
    if ($sentenceCount < 3) return ['score' => 0, 'patterns' => [], 'details' => 'Text too short'];
    
    $patterns = [];
    $aiScore = 0;
    
    // 1. Sentence length uniformity (AI tends to write uniform sentences)
    $lengths = array_map(fn($s) => str_word_count($s), $sentences);
    $avgLength = array_sum($lengths) / count($lengths);
    $variance = array_sum(array_map(fn($l) => pow($l - $avgLength, 2), $lengths)) / count($lengths);
    $stdDev = sqrt($variance);
    $uniformityScore = max(0, 100 - ($stdDev * 5));
    if ($uniformityScore > 70) {
        $aiScore += 15;
        $patterns[] = ['type' => 'uniformity', 'severity' => 'medium', 'message' => 'Sentence lengths are very uniform (AI pattern)', 'value' => round($uniformityScore)];
    }
    
    // 2. Overused transition phrases (AI loves these)
    $aiTransitions = ['furthermore', 'moreover', 'additionally', 'in conclusion', 'as a result', 
        'consequently', 'nevertheless', 'on the other hand', 'in other words', 'for instance',
        'it is important to note', 'it is worth mentioning', 'in today\'s world', 'in this article',
        'let\'s dive in', 'let\'s explore', 'without further ado', 'first and foremost'];
    $textLower = strtolower($text);
    $transitionCount = 0;
    $foundTransitions = [];
    foreach ($aiTransitions as $t) {
        $count = substr_count($textLower, $t);
        if ($count > 0) { $transitionCount += $count; $foundTransitions[] = $t; }
    }
    $transitionDensity = ($transitionCount / max(1, $sentenceCount)) * 100;
    if ($transitionDensity > 10) {
        $aiScore += 20;
        $patterns[] = ['type' => 'transitions', 'severity' => 'high', 'message' => 'Overuse of AI-typical transitions', 'found' => $foundTransitions];
    } elseif ($transitionDensity > 5) {
        $aiScore += 10;
        $patterns[] = ['type' => 'transitions', 'severity' => 'medium', 'message' => 'Some AI-typical transitions detected', 'found' => $foundTransitions];
    }
    
    // 3. Vocabulary diversity (Type-Token Ratio)
    $words = preg_split('/\s+/', strtolower(preg_replace('/[^a-zA-Z\s]/', '', $text)));
    $words = array_filter($words, fn($w) => strlen($w) > 2);
    $totalWords = count($words);
    $uniqueWords = count(array_unique($words));
    $ttr = $totalWords > 0 ? ($uniqueWords / $totalWords) * 100 : 0;
    if ($ttr < 40) {
        $aiScore += 15;
        $patterns[] = ['type' => 'vocabulary', 'severity' => 'high', 'message' => 'Low vocabulary diversity (repetitive)', 'value' => round($ttr)];
    } elseif ($ttr < 50) {
        $aiScore += 8;
        $patterns[] = ['type' => 'vocabulary', 'severity' => 'medium', 'message' => 'Below average vocabulary diversity', 'value' => round($ttr)];
    }
    
    // 4. Starting sentences with same words
    $starters = array_map(fn($s) => strtolower(explode(' ', trim($s))[0] ?? ''), $sentences);
    $starterCounts = array_count_values($starters);
    $maxStarterRepeat = max($starterCounts);
    if ($maxStarterRepeat > 3 && ($maxStarterRepeat / $sentenceCount) > 0.2) {
        $aiScore += 10;
        $patterns[] = ['type' => 'starters', 'severity' => 'medium', 'message' => 'Repetitive sentence starters', 'value' => $maxStarterRepeat];
    }
    
    // 5. Perfect structure (AI often has very clean paragraphs)
    $paragraphs = preg_split('/\n\s*\n/', $text);
    $paragraphs = array_filter(array_map('trim', $paragraphs));
    if (count($paragraphs) >= 3) {
        $paraSentences = array_map(fn($p) => count(preg_split('/[.!?]+/', $p, -1, PREG_SPLIT_NO_EMPTY)), $paragraphs);
        $paraVariance = array_sum(array_map(fn($c) => pow($c - (array_sum($paraSentences)/count($paraSentences)), 2), $paraSentences)) / count($paraSentences);
        if ($paraVariance < 1) {
            $aiScore += 10;
            $patterns[] = ['type' => 'structure', 'severity' => 'low', 'message' => 'Very uniform paragraph structure'];
        }
    }
    
    // 6. Filler phrases
    $fillers = ['it is important to', 'it is essential to', 'it is crucial to', 'it is vital to',
        'in order to', 'due to the fact that', 'at the end of the day', 'when it comes to'];
    $fillerCount = 0;
    foreach ($fillers as $f) { $fillerCount += substr_count($textLower, $f); }
    if ($fillerCount > 2) {
        $aiScore += 10;
        $patterns[] = ['type' => 'fillers', 'severity' => 'medium', 'message' => 'Contains filler phrases typical of AI', 'count' => $fillerCount];
    }
    
    $aiScore = min(100, $aiScore);
    $likelihood = $aiScore >= 60 ? 'High' : ($aiScore >= 35 ? 'Medium' : 'Low');
    
    return [
        'score' => $aiScore,
        'likelihood' => $likelihood,
        'patterns' => $patterns,
        'metrics' => [
            'sentence_uniformity' => round($uniformityScore),
            'vocabulary_diversity' => round($ttr),
            'transition_density' => round($transitionDensity, 1),
            'avg_sentence_length' => round($avgLength, 1)
        ]
    ];
}


/**
 * Calculate readability score (Flesch-Kincaid)
 */
function analyze_readability(string $text): array {
    // Strip markdown and HTML before analysis
    $text = strip_markdown($text);
    $text = strip_tags($text);
    
    $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $sentences = array_filter(array_map('trim', $sentences));
    $sentenceCount = count($sentences);
    
    $words = preg_split('/\s+/', $text);
    $words = array_filter($words, fn($w) => preg_match('/[a-zA-Z]/', $w));
    $wordCount = count($words);
    
    if ($wordCount < 10 || $sentenceCount < 1) {
        return ['score' => 0, 'grade' => 'N/A', 'level' => 'Too short'];
    }
    
    // Count syllables (simple approximation)
    $syllableCount = 0;
    foreach ($words as $word) {
        $word = strtolower(preg_replace('/[^a-z]/', '', $word));
        $syllableCount += max(1, preg_match_all('/[aeiouy]+/', $word));
    }
    
    // Flesch Reading Ease
    $avgSentenceLength = $wordCount / $sentenceCount;
    $avgSyllablesPerWord = $syllableCount / $wordCount;
    $fleschScore = 206.835 - (1.015 * $avgSentenceLength) - (84.6 * $avgSyllablesPerWord);
    $fleschScore = max(0, min(100, $fleschScore));
    
    // Flesch-Kincaid Grade Level
    $gradeLevel = (0.39 * $avgSentenceLength) + (11.8 * $avgSyllablesPerWord) - 15.59;
    $gradeLevel = max(1, min(18, $gradeLevel));
    
    // Determine level
    if ($fleschScore >= 80) { $level = 'Very Easy'; $grade = 'A'; }
    elseif ($fleschScore >= 70) { $level = 'Easy'; $grade = 'B+'; }
    elseif ($fleschScore >= 60) { $level = 'Standard'; $grade = 'B'; }
    elseif ($fleschScore >= 50) { $level = 'Moderate'; $grade = 'C+'; }
    elseif ($fleschScore >= 40) { $level = 'Difficult'; $grade = 'C'; }
    elseif ($fleschScore >= 30) { $level = 'Hard'; $grade = 'D'; }
    else { $level = 'Very Hard'; $grade = 'F'; }
    
    // Passive voice detection
    $passivePatterns = '/\b(is|are|was|were|been|being|be)\s+(\w+ed|written|made|done|taken|given|shown|known|seen)\b/i';
    preg_match_all($passivePatterns, $text, $passiveMatches);
    $passiveCount = count($passiveMatches[0]);
    $passivePercent = $sentenceCount > 0 ? round(($passiveCount / $sentenceCount) * 100) : 0;
    
    return [
        'flesch_score' => round($fleschScore),
        'grade_level' => round($gradeLevel, 1),
        'grade' => $grade,
        'level' => $level,
        'metrics' => [
            'word_count' => $wordCount,
            'sentence_count' => $sentenceCount,
            'avg_sentence_length' => round($avgSentenceLength, 1),
            'avg_syllables' => round($avgSyllablesPerWord, 2),
            'passive_voice_percent' => $passivePercent
        ]
    ];
}

/**
 * Check for internal duplicates (compare with existing articles)
 */
function check_internal_duplicates(string $text, int $excludeId = 0): array {
    $db = \core\Database::connection();
    
    // Get phrases from input text (4-6 word chunks)
    $text = strip_markdown($text);
    $words = preg_split('/\s+/', strip_tags($text));
    $phrases = [];
    for ($i = 0; $i < count($words) - 4; $i += 3) {
        $phrase = implode(' ', array_slice($words, $i, 5));
        $phrase = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($phrase));
        if (strlen($phrase) > 20) $phrases[] = $phrase;
    }
    $phrases = array_unique(array_slice($phrases, 0, 50)); // Limit to 50 phrases
    
    // Search in articles
    $duplicates = [];
    $stmt = $db->prepare("SELECT id, title, content, slug FROM articles WHERE id != ? LIMIT 100");
    $stmt->execute([$excludeId]);
    $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    foreach ($articles as $article) {
        $articleContent = strtolower(strip_tags($article['content']));
        $matchCount = 0;
        $matchedPhrases = [];
        
        foreach ($phrases as $phrase) {
            if (strpos($articleContent, $phrase) !== false) {
                $matchCount++;
                if (count($matchedPhrases) < 3) $matchedPhrases[] = $phrase;
            }
        }
        
        if ($matchCount >= 3) {
            $similarity = round(($matchCount / count($phrases)) * 100);
            $duplicates[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'match_count' => $matchCount,
                'similarity' => $similarity,
                'matched_phrases' => $matchedPhrases
            ];
        }
    }
    
    usort($duplicates, fn($a, $b) => $b['similarity'] - $a['similarity']);
    
    $originalityScore = 100;
    if (!empty($duplicates)) {
        $maxSimilarity = $duplicates[0]['similarity'] ?? 0;
        $originalityScore = max(0, 100 - $maxSimilarity);
    }
    
    return [
        'originality_score' => $originalityScore,
        'duplicates' => array_slice($duplicates, 0, 5),
        'is_unique' => empty($duplicates),
        'checked_articles' => count($articles)
    ];
}

/**
 * Analyze keyword density
 */
function analyze_keywords(string $text, string $targetKeywords = ''): array {
    $text = strip_markdown($text);
    $text = strtolower(strip_tags($text));
    $words = preg_split('/\s+/', preg_replace('/[^a-zA-Z0-9\s]/', '', $text));
    $words = array_filter($words, fn($w) => strlen($w) > 2);
    $totalWords = count($words);
    
    if ($totalWords < 10) return ['density' => [], 'total_words' => $totalWords];
    
    // Count all words
    $wordCounts = array_count_values($words);
    arsort($wordCounts);
    
    // Top keywords
    $stopWords = ['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'her', 'was', 'one', 'our', 'out', 'has', 'have', 'been', 'this', 'that', 'with', 'they', 'from', 'will', 'would', 'there', 'their', 'what', 'about', 'which', 'when', 'make', 'like', 'just', 'over', 'such', 'into', 'than', 'them', 'some', 'could', 'other'];
    $topKeywords = [];
    foreach ($wordCounts as $word => $count) {
        if (!in_array($word, $stopWords) && strlen($word) > 3) {
            $density = round(($count / $totalWords) * 100, 2);
            $topKeywords[$word] = ['count' => $count, 'density' => $density];
            if (count($topKeywords) >= 15) break;
        }
    }
    
    // Check target keywords
    $targetAnalysis = [];
    if ($targetKeywords) {
        $targets = array_map('trim', explode(',', strtolower($targetKeywords)));
        foreach ($targets as $target) {
            if (strlen($target) < 2) continue;
            $count = substr_count($text, $target);
            $density = round(($count / $totalWords) * 100, 2);
            $status = $count === 0 ? 'missing' : ($density > 3 ? 'overused' : ($density >= 0.5 ? 'good' : 'low'));
            $targetAnalysis[$target] = ['count' => $count, 'density' => $density, 'status' => $status];
        }
    }
    
    return [
        'total_words' => $totalWords,
        'unique_words' => count(array_unique($words)),
        'top_keywords' => $topKeywords,
        'target_keywords' => $targetAnalysis
    ];
}


/**
 * Complete quality analysis
 */
function analyze_content_quality(string $text, string $targetKeywords = '', int $excludeId = 0): array {
    return [
        'ai_detection' => analyze_ai_patterns($text),
        'readability' => analyze_readability($text),
        'duplicates' => check_internal_duplicates($text, $excludeId),
        'keywords' => analyze_keywords($text, $targetKeywords),
        'analyzed_at' => gmdate('c')
    ];
}

// ============================================================================
// AJAX HANDLERS
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax'])) {
    header('Content-Type: application/json');
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    // ANALYZE CONTENT
    if ($action === 'analyze') {
        $content = trim($_POST['content'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $excludeId = intval($_POST['exclude_id'] ?? 0);
        
        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short (min 50 characters)']);
            exit;
        }
        
        $result = analyze_content_quality($content, $keywords, $excludeId);
        echo json_encode(['ok' => true, 'analysis' => $result]);
        exit;
    }
    
    // QUICK ANALYZE (lighter version for Content Creator)
    if ($action === 'quick_analyze') {
        $content = trim($_POST['content'] ?? '');
        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }
        
        $ai = analyze_ai_patterns($content);
        $read = analyze_readability($content);
        $dup = check_internal_duplicates($content);
        
        echo json_encode([
            'ok' => true,
            'quick' => [
                'ai_score' => $ai['score'],
                'ai_likelihood' => $ai['likelihood'],
                'originality' => $dup['originality_score'],
                'readability_grade' => $read['grade'],
                'readability_level' => $read['level'],
                'word_count' => $read['metrics']['word_count'] ?? 0
            ]
        ]);
        exit;
    }
    
    // HUMANIZE CONTENT
    if ($action === 'humanize') {
        if (!$aiConfigured) {
            echo json_encode(['ok' => false, 'error' => 'AI not configured']);
            exit;
        }
        
        $content = trim($_POST['content'] ?? '');
        $style = $_POST['style'] ?? 'natural';
        $keywords = trim($_POST['keywords'] ?? '');
        $provider = $_POST['provider'] ?? 'openai';
        $selectedModel = $_POST['model'] ?? 'gpt-4.1-mini';

        // Validate provider and model
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-4.1-mini';
        }
        
        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }
        
        $keywordInstruction = $keywords ? "\n\nMUST PRESERVE these exact keywords: {$keywords}" : '';
        
        $systemPrompt = "You are an expert editor who makes AI-written text sound authentically human.

FIRST, silently analyze the text to understand:
- What is the topic and purpose?
- What tone is it aiming for (professional, casual, educational)?
- What are the AI-detection red flags (uniform sentences, stiff transitions, repetitive structure)?

THEN, rewrite following these human-writing patterns:

SENTENCE RHYTHM (critical):
- Vary lengths wildly: 6 words. Then maybe twenty-two words with a couple of clauses that explore the idea further. Back to short.
- Never write 3+ sentences of similar length in a row

HUMAN TOUCHES:
- Start some sentences with 'And', 'But', 'So' - humans do this naturally
- Use dashes for asides—like this—instead of always parentheses or commas
- Throw in an occasional rhetorical question. Makes sense, right?
- Add brief opinion markers: 'The truth is...', 'Honestly,...', 'Here's the thing:'

REMOVE AI TELLS:
- 'Furthermore', 'Moreover', 'Additionally' - replace with natural transitions or just start new thought
- 'It's important to note' - delete entirely, just state the thing
- 'In today's world', 'In this article' - cut these
- Perfect parallel structure - break it up

KEEP INTACT:
- All factual information and claims
- Overall structure and ## headings
- Technical terms that need to stay precise
- Any specified keywords{$keywordInstruction}

OUTPUT: Return the full rewritten text with ## headings preserved.";

        $userPrompt = "Rewrite this to sound human and natural while keeping all information:\n\n{$content}";
        
        $result = ai_universal_generate($provider, $selectedModel, $systemPrompt, $userPrompt, [
            'temperature' => 0.7,
            'max_tokens' => 4000
        ]);
        echo json_encode($result);
        exit;
    }

    // IMPROVE READABILITY
    if ($action === 'improve_readability') {
        if (!$aiConfigured) {
            echo json_encode(['ok' => false, 'error' => 'AI not configured']);
            exit;
        }
        
        $content = trim($_POST['content'] ?? '');
        $level = $_POST['level'] ?? 'moderate';
        $keywords = trim($_POST['keywords'] ?? '');
        $provider = $_POST['provider'] ?? 'openai';
        $selectedModel = $_POST['model'] ?? 'gpt-4.1-mini';

        // Validate provider and model
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-4.1-mini';
        }

        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }

        $keywordInstruction = $keywords ? "\n\nPRESERVE THESE KEYWORDS exactly: {$keywords}" : '';

        $systemPrompt = "You are a readability expert. Make text easy to read at 8th-grade level (Flesch 60-70) WITHOUT making it robotic.

FIRST, identify in the text:
- Overly complex sentences (25+ words)
- Jargon or fancy words with simpler alternatives
- Passive voice constructions
- Dense paragraphs that need breaking up

THEN, simplify following these rules:

SENTENCE SIMPLIFICATION:
- Target 12-18 words average, but VARY the lengths (some 8, some 22)
- If a sentence has 2+ commas, consider splitting
- One main idea per sentence
- Active voice: 'The team built X' not 'X was built by the team'

WORD SWAPS (only when meaning stays clear):
utilize → use | implement → set up, start | facilitate → help, allow
comprehensive → complete, full | subsequently → then, next
demonstrate → show | approximately → about, around
methodology → method, approach | acquisition → buying

KEEP NATURAL:
- Don't make ALL sentences short - mix lengths to avoid robotic feel
- Keep some sophisticated words if they're the right word
- Preserve the author's voice and personality
- Keep technical terms that need to stay precise

STRUCTURE:
- Break long paragraphs (5+ sentences) into 2-3 sentence chunks
- Add white space for visual breathing room

OUTPUT: Return full text with ## headings preserved.{$keywordInstruction}";

        $userPrompt = "Simplify this text for easier reading while keeping it natural:\n\n{$content}";

        $result = ai_universal_generate($provider, $selectedModel, $systemPrompt, $userPrompt, [
            'temperature' => 0.6,
            'max_tokens' => 4000
        ]);
        echo json_encode($result);
        exit;
    }

    // MAKE UNIQUE (Improve Originality)
    if ($action === 'make_unique') {
        if (!$aiConfigured) {
            echo json_encode(['ok' => false, 'error' => 'AI not configured']);
            exit;
        }
        
        $content = trim($_POST['content'] ?? '');
        $level = $_POST['level'] ?? 'medium';
        $keywords = trim($_POST['keywords'] ?? '');
        $provider = $_POST['provider'] ?? 'openai';
        $selectedModel = $_POST['model'] ?? 'gpt-4.1-mini';

        // Validate provider and model
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-4.1-mini';
        }

        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }

        $keywordInstruction = $keywords ? "\n\nKEEP THESE KEYWORDS exactly as written: {$keywords}" : '';

        $levelInstructions = [
            'light' => 'Light rewrite: Change word choices and some phrasing while keeping structure similar. Aim for 30-40% different words.',
            'medium' => 'Moderate rewrite: Restructure sentences, reorder information within paragraphs, use different phrasing throughout. Aim for 50-60% different.',
            'heavy' => 'Heavy rewrite: Completely rephrase while preserving meaning. Restructure paragraphs, change examples, reorder sections if it improves flow. Aim for 70%+ different.'
        ];
        $levelInstruction = $levelInstructions[$level] ?? $levelInstructions['medium'];
        
        $systemPrompt = "You are a content editor making text unique while preserving its value.

FIRST, understand the text:
- What are the key points and facts?
- What is the tone and intended audience?
- What makes this content valuable?

THEN, rewrite with this approach:
{$levelInstruction}

UNIQUENESS TECHNIQUES:
- Replace phrases with synonymous alternatives
- Restructure sentence order within paragraphs
- Change passive/active voice mix
- Use different transition words
- Reorder bullet points or list items
- Rephrase examples while keeping their meaning

CRITICAL RULES:
- Every fact must stay accurate
- Don't add information not in original
- Don't remove important points
- Keep the same depth and expertise level
- Preserve ## heading structure (you can rephrase heading text slightly)

AVOID:
- Making text sound more generic or less specific
- Losing the author's voice completely
- Creating awkward phrasing just to be different
- Changing technical terms that need to stay precise{$keywordInstruction}

OUTPUT: Return the complete rewritten text.";

        $userPrompt = "Make this content unique while keeping all the information and value:\n\n{$content}";

        $result = ai_universal_generate($provider, $selectedModel, $systemPrompt, $userPrompt, [
            'temperature' => 0.75,
            'max_tokens' => 4000
        ]);
        echo json_encode($result);
        exit;
    }

    // PERFECT CONTENT - All-in-one improvement
    if ($action === 'perfect_content') {
        if (!$aiConfigured) {
            echo json_encode(['ok' => false, 'error' => 'AI not configured']);
            exit;
        }
        
        $content = trim($_POST['content'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $provider = $_POST['provider'] ?? 'openai';
        $selectedModel = $_POST['model'] ?? 'gpt-4.1-mini';

        // Validate provider and model
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-4.1-mini';
        }

        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }

        $keywordInstruction = $keywords ? "\n\nMUST KEEP these keywords: {$keywords}" : '';

        $systemPrompt = "You are a senior editor perfecting content for publication. Your job is to elevate this text while keeping its soul.

STEP 1 - ANALYZE (silently):
- What is the topic and main message?
- What tone is the author going for?
- What are the biggest weaknesses? (AI patterns? Poor readability? Repetition?)
- What are the strengths to preserve?

STEP 2 - PERFECT (apply all that help, skip what doesn't):

HUMANIZE if needed:
- Add sentence length variety (mix 6-word punches with 20-word flows)
- Use contractions naturally (don't, won't, it's)
- Start occasional sentences with And, But, So
- Remove stiff transitions (Furthermore → just start the new thought)
- Add human touches: dashes for asides—like this—or brief rhetorical questions

IMPROVE READABILITY if needed:
- Break sentences over 25 words into two
- Replace jargon with clearer alternatives
- Use active voice when passive sounds clunky
- Break dense paragraphs into smaller chunks

BOOST VOCABULARY DIVERSITY:
- Never use the same adjective twice
- Find fresh synonyms for repeated words
- Vary transition words throughout

REMOVE AI TELLS:
- 'It's important to note' → just state it
- 'In today's world' → delete or be specific
- 'Furthermore/Moreover/Additionally' → vary or cut
- Perfect parallel structures → break them up slightly
- Uniform sentence/paragraph lengths → add variety

PRESERVE:
- All factual information
- The author's voice and personality
- Technical accuracy
- ## heading structure
- Specified keywords{$keywordInstruction}

STEP 3 - OUTPUT:
Return the perfected text. No explanations, just the improved content with ## headings intact.";

        $userPrompt = "Perfect this content - improve whatever needs improving while keeping what works:\n\n{$content}";

        $result = ai_universal_generate($provider, $selectedModel, $systemPrompt, $userPrompt, [
            'temperature' => 0.7,
            'max_tokens' => max(4000, intval(strlen($content) / 3 * 1.5))
        ]);
        echo json_encode($result);
        exit;
    }

    // SAVE TO ARTICLE
    if ($action === 'save_to_article') {
        $articleId = intval($_POST['article_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        
        if (!$articleId) {
            echo json_encode(['ok' => false, 'error' => 'No article selected']);
            exit;
        }
        
        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }
        
        $db = \core\Database::connection();
        
        // Check if article exists
        $stmt = $db->prepare("SELECT id, title FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$article) {
            echo json_encode(['ok' => false, 'error' => 'Article not found']);
            exit;
        }
        
        // Update article content
        $stmt = $db->prepare("UPDATE articles SET content = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$content, $articleId]);
        
        echo json_encode(['ok' => true, 'message' => 'Article updated successfully', 'title' => $article['title']]);
        exit;
    }
    
    // SAVE AS NEW ARTICLE
    if ($action === 'save_as_new') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        if (strlen($title) < 3) {
            echo json_encode(['ok' => false, 'error' => 'Title is required (min 3 characters)']);
            exit;
        }
        
        if (strlen($content) < 50) {
            echo json_encode(['ok' => false, 'error' => 'Content too short']);
            exit;
        }
        
        // Generate slug
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
        if (empty($slug)) {
            $slug = 'article-' . time();
        }
        
        $db = \core\Database::connection();
        
        // Check for duplicate slug
        $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        // Insert new article
        $stmt = $db->prepare("INSERT INTO articles (title, slug, content, status, created_at, updated_at) VALUES (?, ?, ?, 'draft', NOW(), NOW())");
        $stmt->execute([$title, $slug, $content]);
        $newId = $db->lastInsertId();
        
        echo json_encode(['ok' => true, 'message' => 'New article created', 'title' => $title, 'id' => $newId, 'slug' => $slug]);
        exit;
    }
    
    // LOAD ARTICLE FOR ANALYSIS
    if ($action === 'load_article') {
        $articleId = intval($_POST['article_id'] ?? 0);
        if (!$articleId) {
            echo json_encode(['ok' => false, 'error' => 'Invalid article ID']);
            exit;
        }
        
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT id, title, content, slug FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$article) {
            echo json_encode(['ok' => false, 'error' => 'Article not found']);
            exit;
        }
        
        echo json_encode(['ok' => true, 'article' => $article]);
        exit;
    }
    
    // LIST ARTICLES FOR DROPDOWN
    if ($action === 'list_articles') {
        $db = \core\Database::connection();
        $stmt = $db->query("SELECT id, title, status, created_at FROM articles ORDER BY created_at DESC LIMIT 50");
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'articles' => $articles]);
        exit;
    }
    
    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}

// AI helper function
function ai_generate_with_system(string $systemPrompt, string $userPrompt, array $config, array $options = []): array {
    $apiKey = $config['api_key'];
    // Allow model override from options, default to gpt-4.1-mini for better quality
    $model = $options['model'] ?? $config['model'] ?? 'gpt-4.1-mini';
    $baseUrl = rtrim(!empty($config['base_url']) ? $config['base_url'] : 'https://api.openai.com/v1', '/');
    
    $maxTokens = $options['max_tokens'] ?? 4000;
    $temperature = $options['temperature'] ?? 0.8;
    $frequencyPenalty = $options['frequency_penalty'] ?? 0.3;
    $presencePenalty = $options['presence_penalty'] ?? 0.1;
    $timeout = $options['timeout'] ?? 120;
    
    // Build payload - newer models (o-series, GPT-5.x, GPT-4.1.x) use max_completion_tokens
    $useNewTokenParam = preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);
    
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ]
    ];
    
    // Newer models don't support temperature, frequency_penalty, presence_penalty
    if ($useNewTokenParam) {
        $payload['max_completion_tokens'] = $maxTokens;
    } else {
        $payload['max_tokens'] = $maxTokens;
        $payload['temperature'] = $temperature;
        $payload['frequency_penalty'] = $frequencyPenalty;
        $payload['presence_penalty'] = $presencePenalty;
    }
    
    $ch = curl_init($baseUrl . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => $timeout
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) return ['ok' => false, 'error' => 'Connection error: ' . $error];
    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        return ['ok' => false, 'error' => $data['error']['message'] ?? 'HTTP ' . $httpCode];
    }
    
    $data = json_decode($response, true);

    // Extract content from various response formats (GPT-4o, GPT-5.x, etc.)
    $content = null;
    if (isset($data['choices'][0]['message']['content'])) {
        $content = $data['choices'][0]['message']['content'];
    } elseif (isset($data['output_text'])) {
        $content = $data['output_text'];
    } elseif (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (isset($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $c) {
                    if (isset($c['text'])) { $content = $c['text']; break 2; }
                }
            }
        }
    } elseif (isset($data['choices'][0]['text'])) {
        $content = $data['choices'][0]['text'];
    }

    return $content ? ['ok' => true, 'content' => $content] : ['ok' => false, 'error' => 'Empty response'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Quality Analyzer - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--accent2:#b4befe;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--teal:#94e2d5;--border:#313244}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.grid-main{display:grid;grid-template-columns:1fr 400px;gap:24px}
@media(max-width:1100px){.grid-main{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group textarea,.form-group input,.form-group select{width:100%;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:inherit;transition:all .2s}
.form-group textarea:focus,.form-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-group textarea{min-height:200px;resize:vertical}
.form-hint{font-size:12px;color:var(--muted);margin-top:4px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s;border:none;font-family:inherit}
.btn-primary{background:var(--accent);color:#1e1e2e}
.btn-primary:hover{background:var(--accent2)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
.btn-success{background:var(--success);color:#1e1e2e}
.btn-warning{background:var(--warning);color:#1e1e2e}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.btn-sm{padding:8px 14px;font-size:13px}
.btn-block{width:100%}
.btn-group{display:flex;gap:10px;flex-wrap:wrap}
.score-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.score-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
.score-card .icon{font-size:32px;margin-bottom:8px}
.score-card .value{font-size:28px;font-weight:700;margin-bottom:4px}
.score-card .label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px}
.score-card.good .value{color:var(--success)}
.score-card.warn .value{color:var(--warning)}
.score-card.bad .value{color:var(--danger)}
.detail-section{background:var(--bg);border:1px solid var(--border);border-radius:12px;margin-bottom:16px;overflow:hidden}
.detail-header{padding:14px 16px;border-bottom:1px solid var(--border);font-weight:600;display:flex;align-items:center;gap:10px;cursor:pointer}
.detail-header:hover{background:var(--bg3)}
.detail-body{padding:16px;display:none}
.detail-body.show{display:block}
.pattern-item{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.pattern-item:last-child{border-bottom:none}
.pattern-icon{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.pattern-icon.high{background:rgba(243,139,168,.2);color:var(--danger)}
.pattern-icon.medium{background:rgba(249,226,175,.2);color:var(--warning)}
.pattern-icon.low{background:rgba(166,227,161,.2);color:var(--success)}
.pattern-text{flex:1;font-size:13px}
.pattern-text strong{color:var(--text)}
.keyword-grid{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.keyword-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg3);border-radius:20px;font-size:12px}
.keyword-tag.good{background:rgba(166,227,161,.2);color:var(--success)}
.keyword-tag.low{background:rgba(249,226,175,.2);color:var(--warning)}
.keyword-tag.missing{background:rgba(243,139,168,.2);color:var(--danger)}
.keyword-tag.overused{background:rgba(203,166,247,.2);color:var(--purple)}
.duplicate-item{padding:12px;background:var(--bg3);border-radius:8px;margin-bottom:8px}
.duplicate-item:last-child{margin-bottom:0}
.duplicate-title{font-weight:500;margin-bottom:4px}
.duplicate-meta{font-size:12px;color:var(--muted)}
.loading{display:inline-block;width:18px;height:18px;border:2px solid var(--bg3);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.result-panel{display:none}
.result-panel.show{display:block}
.humanize-section{margin-top:20px;padding-top:20px;border-top:1px solid var(--border)}
.style-options{display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap}
.style-option{padding:8px 16px;background:var(--bg);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:13px;transition:all .2s}
.style-option:hover{border-color:var(--accent)}
.style-option.active{border-color:var(--accent);background:rgba(137,180,250,.1)}
.tabs{display:flex;gap:4px;margin-bottom:20px;background:var(--bg);padding:4px;border-radius:10px}
.tab{flex:1;padding:10px;text-align:center;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;transition:all .2s;color:var(--text2)}
.tab:hover{background:var(--bg3)}
.tab.active{background:var(--accent);color:#1e1e2e}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📊',
    'title' => 'Content Quality Analyzer',
    'description' => 'AI Detection, Duplicate Check, Readability Score',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent), var(--teal)',
    'actions' => []
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="grid-main">
<div>
<!-- Input Card -->
<div class="card">
<div class="card-head"><span class="card-title">📝 Content to Analyze</span></div>
<div class="card-body">
    <div class="tabs">
        <div class="tab active" data-tab="paste">Paste Content</div>
        <div class="tab" data-tab="article">Load Article</div>
    </div>
    
    <div id="tab-paste">
        <div class="form-group">
            <label>Content</label>
            <textarea id="contentInput" placeholder="Paste your content here for quality analysis..."></textarea>
        </div>
        <div class="form-group">
            <label>🔑 Protected Keywords (from SEO Research)</label>
            <input type="text" id="keywordsInput" placeholder="keyword1, keyword2, phrase to keep...">
            <div class="form-hint">These keywords will be preserved when using AI rewrite tools</div>
        </div>
    </div>
    
    <div id="tab-article" style="display:none">
        <div class="form-group">
            <label>Select Article</label>
            <select id="articleSelect">
                <option value="">-- Select article to analyze --</option>
            </select>
        </div>
        <div id="articlePreview" style="display:none;margin-top:16px">
            <div class="form-group">
                <label>Article Content</label>
                <textarea id="articleContent" readonly style="min-height:150px"></textarea>
            </div>
        </div>
    </div>
    
    <div class="btn-group">
        <button class="btn btn-primary" id="analyzeBtn">🔍 Analyze Quality</button>
        <button class="btn btn-secondary" id="clearBtn">🗑️ Clear</button>
        <button class="btn btn-success" id="saveArticleBtn">💾 Save to Article</button>
    </div>
</div>
</div>

<!-- Results Panel -->
<div class="result-panel" id="resultsPanel">
<div class="card" style="margin-top:20px">
<div class="card-head"><span class="card-title">📊 Analysis Results</span></div>
<div class="card-body">
    
    <!-- Score Cards -->
    <div class="score-cards">
        <div class="score-card" id="aiScoreCard">
            <div class="icon">🤖</div>
            <div class="value" id="aiScoreValue">--</div>
            <div class="label">AI Detection</div>
        </div>
        <div class="score-card" id="origScoreCard">
            <div class="icon">📝</div>
            <div class="value" id="origScoreValue">--</div>
            <div class="label">Originality</div>
        </div>
        <div class="score-card" id="readScoreCard">
            <div class="icon">📖</div>
            <div class="value" id="readScoreValue">--</div>
            <div class="label">Readability</div>
        </div>
    </div>
    
    <!-- AI Detection Details -->
    <div class="detail-section">
        <div class="detail-header" onclick="toggleDetail(this)">🤖 AI Detection Analysis <span style="margin-left:auto;font-size:12px;color:var(--muted)">▼</span></div>
        <div class="detail-body show" id="aiDetails"></div>
    </div>
    
    <!-- Readability Details -->
    <div class="detail-section">
        <div class="detail-header" onclick="toggleDetail(this)">📖 Readability Metrics <span style="margin-left:auto;font-size:12px;color:var(--muted)">▼</span></div>
        <div class="detail-body" id="readDetails"></div>
    </div>
    
    <!-- Duplicate Check -->
    <div class="detail-section">
        <div class="detail-header" onclick="toggleDetail(this)">📝 Duplicate Check <span style="margin-left:auto;font-size:12px;color:var(--muted)">▼</span></div>
        <div class="detail-body" id="dupDetails"></div>
    </div>
    
    <!-- Keyword Analysis -->
    <div class="detail-section">
        <div class="detail-header" onclick="toggleDetail(this)">🔑 Keyword Analysis <span style="margin-left:auto;font-size:12px;color:var(--muted)">▼</span></div>
        <div class="detail-body" id="keywordDetails"></div>
    </div>
    
    <!-- Humanize Section -->
    <div class="humanize-section">
        <h3 style="font-size:15px;margin-bottom:16px">🛠️ Content Improvement Tools</h3>
        
        <!-- Provider & Model Selection -->
        <?= ai_render_dual_selector('ai_provider', 'ai_model', 'openai', 'gpt-4.1-mini') ?>
        
        <!-- PERFECT CONTENT - Main Tool -->
        <div style="margin-bottom:20px;padding:20px;background:linear-gradient(135deg, var(--accent) 0%, #7c3aed 100%);border-radius:12px;border:2px solid var(--accent)">
            <h4 style="font-size:16px;margin-bottom:8px;color:white;display:flex;align-items:center;gap:8px">🎯 Perfect Content <span style="font-size:11px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px">RECOMMENDED</span></h4>
            <p style="font-size:12px;color:rgba(255,255,255,0.9);margin-bottom:12px">All-in-one: Fix AI detection patterns + improve readability + boost engagement. One click solution.</p>
            <button class="btn btn-sm" id="perfectBtn" style="background:white;color:var(--accent);font-weight:600" <?= !$aiConfigured ? 'disabled' : '' ?>>🎯 Perfect My Content</button>
        </div>
        
        <p style="font-size:11px;color:var(--text2);margin-bottom:16px;text-align:center">— or use individual tools below —</p>
        
        <!-- Humanize AI -->
        <div style="margin-bottom:20px;padding:16px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
            <h4 style="font-size:14px;margin-bottom:8px;display:flex;align-items:center;gap:8px">🤖 Bypass AI Detection</h4>
            <p style="font-size:12px;color:var(--text2);margin-bottom:12px">Rewrite to sound more human and natural.</p>
            <div class="style-options" style="margin-bottom:12px">
                <div class="style-option active" data-style="natural">Natural</div>
                <div class="style-option" data-style="professional">Professional</div>
                <div class="style-option" data-style="conversational">Conversational</div>
                <div class="style-option" data-style="academic">Academic</div>
            </div>
            <button class="btn btn-warning btn-sm" id="humanizeBtn" <?= !$aiConfigured ? 'disabled' : '' ?>>🔄 Humanize</button>
        </div>
        
        <!-- Improve Readability -->
        <div style="margin-bottom:20px;padding:16px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
            <h4 style="font-size:14px;margin-bottom:8px;display:flex;align-items:center;gap:8px">📖 Improve Readability</h4>
            <p style="font-size:12px;color:var(--text2);margin-bottom:12px">Shorter sentences, simpler words, better structure. Target: Flesch score 60-70.</p>
            <button class="btn btn-success btn-sm" id="readabilityBtn" <?= !$aiConfigured ? 'disabled' : '' ?>>📖 Improve Readability</button>
        </div>
        
        <!-- Make Unique -->
        <div style="padding:16px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
            <h4 style="font-size:14px;margin-bottom:8px;display:flex;align-items:center;gap:8px">📝 Improve Originality</h4>
            <p style="font-size:12px;color:var(--text2);margin-bottom:12px">Rephrase to make content more unique and avoid duplicates.</p>
            <div class="style-options uniqueness-options" style="margin-bottom:12px">
                <div class="style-option active" data-level="light">Light Rewrite</div>
                <div class="style-option" data-level="medium">Medium Rewrite</div>
                <div class="style-option" data-level="heavy">Heavy Rewrite</div>
            </div>
            <button class="btn btn-primary btn-sm" id="uniqueBtn" <?= !$aiConfigured ? 'disabled' : '' ?>>✨ Make Unique</button>
        </div>
        
        <?php if (!$aiConfigured): ?><p style="font-size:12px;color:var(--warning);margin-top:12px">⚠️ AI not configured - enable in AI Settings</p><?php endif; ?>
    </div>
</div>
</div>
</div>
</div>

<!-- Sidebar -->
<div>
<div class="card">
<div class="card-head"><span class="card-title">💡 Understanding Scores</span></div>
<div class="card-body" style="font-size:13px;color:var(--text2)">
    <p style="margin-bottom:16px"><strong style="color:var(--text)">🤖 AI Detection (0-100)</strong></p>
    <ul style="padding-left:18px;margin-bottom:16px">
        <li><span style="color:var(--success)">0-35:</span> Likely human</li>
        <li><span style="color:var(--warning)">35-60:</span> Mixed signals</li>
        <li><span style="color:var(--danger)">60-100:</span> Likely AI</li>
    </ul>
    
    <p style="margin-bottom:16px"><strong style="color:var(--text)">📝 Originality (0-100)</strong></p>
    <ul style="padding-left:18px;margin-bottom:16px">
        <li><span style="color:var(--success)">90-100:</span> Unique</li>
        <li><span style="color:var(--warning)">70-90:</span> Some overlap</li>
        <li><span style="color:var(--danger)">0-70:</span> High similarity</li>
    </ul>
    
    <p style="margin-bottom:16px"><strong style="color:var(--text)">📖 Readability</strong></p>
    <ul style="padding-left:18px">
        <li><span style="color:var(--success)">A/B:</span> Easy to read</li>
        <li><span style="color:var(--warning)">C:</span> Moderate</li>
        <li><span style="color:var(--danger)">D/F:</span> Difficult</li>
    </ul>
</div>
</div>

<div class="card" style="margin-top:20px">
<div class="card-head"><span class="card-title">🔗 Quick Links</span></div>
<div class="card-body">
    <a href="/admin/ai-content-creator" class="btn btn-secondary btn-block" style="margin-bottom:8px">✍️ Content Creator</a>
    <a href="/admin/ai-seo-research" class="btn btn-secondary btn-block" style="margin-bottom:8px">🔬 SEO Research</a>
    <a href="/admin/articles" class="btn btn-secondary btn-block">📄 Articles</a>
</div>
</div>
</div>
</div>
</div>

<!-- Save to Article Modal -->
<div id="saveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:10000;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:24px;width:100%;max-width:500px;margin:20px">
    <h3 style="margin-bottom:16px;font-size:18px">💾 Save Article</h3>
    
    <!-- Tabs -->
    <div style="display:flex;gap:10px;margin-bottom:20px">
        <button class="btn btn-primary save-tab active" data-mode="new">📝 Create New</button>
        <button class="btn btn-secondary save-tab" data-mode="update">🔄 Update Existing</button>
    </div>
    
    <!-- Create New Panel -->
    <div id="saveNewPanel">
        <div class="form-group">
            <label>Article Title</label>
            <input type="text" id="newArticleTitle" placeholder="Enter article title..." style="width:100%;padding:12px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px">
        </div>
    </div>
    
    <!-- Update Existing Panel -->
    <div id="saveUpdatePanel" style="display:none">
        <div class="form-group">
            <label>Select Article to Update</label>
            <select id="saveArticleSelect" style="width:100%;padding:12px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px">
                <option value="">-- Select article --</option>
            </select>
        </div>
    </div>
    
    <div style="display:flex;gap:10px;margin-top:20px">
        <button class="btn btn-success" id="confirmSaveBtn">💾 Save Article</button>
        <button class="btn btn-secondary" id="cancelSaveBtn">Cancel</button>
    </div>
</div>
</div>

<script>
const csrf = '<?= esc(csrf_token()) ?>';
let currentContent = '';
let selectedStyle = 'natural';
let currentArticleId = null;

// Tab switching
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-paste').style.display = tab.dataset.tab === 'paste' ? 'block' : 'none';
        document.getElementById('tab-article').style.display = tab.dataset.tab === 'article' ? 'block' : 'none';
        if (tab.dataset.tab === 'article') loadArticleList();
    });
});

// Style selection
document.querySelectorAll('.style-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.style-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        selectedStyle = opt.dataset.style;
    });
});

// Toggle detail sections
function toggleDetail(header) {
    const body = header.nextElementSibling;
    body.classList.toggle('show');
    header.querySelector('span:last-child').textContent = body.classList.contains('show') ? '▼' : '▶';
}

// Load article list
async function loadArticleList() {
    const select = document.getElementById('articleSelect');
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'list_articles');
    fd.append('csrf_token', csrf);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.ok) {
        select.innerHTML = '<option value="">-- Select article --</option>' + 
            data.articles.map(a => `<option value="${a.id}">${escHtml(a.title)} (${a.status})</option>`).join('');
    }
}

// Load selected article
document.getElementById('articleSelect').addEventListener('change', async (e) => {
    const id = e.target.value;
    if (!id) { 
        document.getElementById('articlePreview').style.display = 'none';
        currentArticleId = null;
        return; 
    }
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'load_article');
    fd.append('csrf_token', csrf);
    fd.append('article_id', id);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.ok) {
        document.getElementById('articleContent').value = stripHtml(data.article.content);
        document.getElementById('articlePreview').style.display = 'block';
        currentArticleId = parseInt(id);
    }
});

// Analyze button
document.getElementById('analyzeBtn').addEventListener('click', async () => {
    const activeTab = document.querySelector('.tab.active').dataset.tab;
    let content = activeTab === 'paste' 
        ? document.getElementById('contentInput').value 
        : document.getElementById('articleContent').value;
    
    content = content.trim();
    if (content.length < 50) { alert('Content too short (min 50 characters)'); return; }
    
    currentContent = content;
    const keywords = document.getElementById('keywordsInput').value;
    const btn = document.getElementById('analyzeBtn');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Analyzing...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'analyze');
    fd.append('csrf_token', csrf);
    fd.append('content', content);
    fd.append('keywords', keywords);
    // Pass exclude_id if we're analyzing a loaded article
    if (currentArticleId) {
        fd.append('exclude_id', currentArticleId);
    }
    
    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.ok) {
            displayResults(data.analysis);
            document.getElementById('resultsPanel').classList.add('show');
        } else {
            alert(data.error || 'Analysis failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '🔍 Analyze Quality';
});

// Display results
function displayResults(analysis) {
    const ai = analysis.ai_detection;
    const read = analysis.readability;
    const dup = analysis.duplicates;
    const kw = analysis.keywords;
    
    // Score cards
    const aiCard = document.getElementById('aiScoreCard');
    const aiValue = document.getElementById('aiScoreValue');
    aiValue.textContent = ai.score + '%';
    aiCard.className = 'score-card ' + (ai.score >= 60 ? 'bad' : ai.score >= 35 ? 'warn' : 'good');
    
    const origCard = document.getElementById('origScoreCard');
    const origValue = document.getElementById('origScoreValue');
    origValue.textContent = dup.originality_score + '%';
    origCard.className = 'score-card ' + (dup.originality_score >= 90 ? 'good' : dup.originality_score >= 70 ? 'warn' : 'bad');
    
    const readCard = document.getElementById('readScoreCard');
    const readValue = document.getElementById('readScoreValue');
    readValue.textContent = read.grade;
    readCard.className = 'score-card ' + (['A', 'B+', 'B'].includes(read.grade) ? 'good' : ['C+', 'C'].includes(read.grade) ? 'warn' : 'bad');
    
    // AI Details
    let aiHtml = `<div style="margin-bottom:12px"><strong>AI Likelihood:</strong> <span style="color:var(${ai.score >= 60 ? '--danger' : ai.score >= 35 ? '--warning' : '--success'})">${ai.likelihood}</span></div>`;
    if (ai.patterns.length > 0) {
        aiHtml += '<div style="font-weight:500;margin-bottom:8px">Detected Patterns:</div>';
        ai.patterns.forEach(p => {
            aiHtml += `<div class="pattern-item">
                <div class="pattern-icon ${p.severity}">${p.severity === 'high' ? '⚠️' : p.severity === 'medium' ? '⚡' : '💡'}</div>
                <div class="pattern-text">${escHtml(p.message)}${p.value ? ` (${p.value})` : ''}${p.found ? `<br><small style="color:var(--muted)">Found: ${p.found.join(', ')}</small>` : ''}</div>
            </div>`;
        });
    } else {
        aiHtml += '<p style="color:var(--success)">✅ No significant AI patterns detected</p>';
    }
    aiHtml += `<div style="margin-top:12px;font-size:12px;color:var(--muted)">
        Metrics: Uniformity ${ai.metrics.sentence_uniformity}% | Vocabulary ${ai.metrics.vocabulary_diversity}% | Transitions ${ai.metrics.transition_density}%
    </div>`;
    document.getElementById('aiDetails').innerHTML = aiHtml;
    
    // Readability Details
    let readHtml = `
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:12px">
            <div><strong>Flesch Score:</strong> ${read.flesch_score}/100</div>
            <div><strong>Grade Level:</strong> ${read.grade_level}</div>
            <div><strong>Level:</strong> ${read.level}</div>
            <div><strong>Words:</strong> ${read.metrics.word_count}</div>
            <div><strong>Sentences:</strong> ${read.metrics.sentence_count}</div>
            <div><strong>Avg Sentence:</strong> ${read.metrics.avg_sentence_length} words</div>
            <div><strong>Passive Voice:</strong> ${read.metrics.passive_voice_percent}%</div>
        </div>
    `;
    document.getElementById('readDetails').innerHTML = readHtml;
    
    // Duplicate Details
    let dupHtml = `<div style="margin-bottom:12px"><strong>Checked:</strong> ${dup.checked_articles} articles</div>`;
    if (dup.duplicates.length > 0) {
        dupHtml += '<div style="font-weight:500;margin-bottom:8px">Similar Content Found:</div>';
        dup.duplicates.forEach(d => {
            dupHtml += `<div class="duplicate-item">
                <div class="duplicate-title">${escHtml(d.title)}</div>
                <div class="duplicate-meta">${d.similarity}% similar | ${d.match_count} matching phrases</div>
            </div>`;
        });
    } else {
        dupHtml += '<p style="color:var(--success)">✅ No duplicates found - content is unique!</p>';
    }
    document.getElementById('dupDetails').innerHTML = dupHtml;
    
    // Keyword Details
    let kwHtml = `<div style="margin-bottom:12px"><strong>Total Words:</strong> ${kw.total_words} | <strong>Unique:</strong> ${kw.unique_words}</div>`;
    
    if (Object.keys(kw.target_keywords).length > 0) {
        kwHtml += '<div style="font-weight:500;margin-bottom:8px">Target Keywords:</div><div class="keyword-grid">';
        for (const [word, data] of Object.entries(kw.target_keywords)) {
            kwHtml += `<span class="keyword-tag ${data.status}">${escHtml(word)} <small>${data.count}x (${data.density}%)</small></span>`;
        }
        kwHtml += '</div>';
    }
    
    kwHtml += '<div style="font-weight:500;margin:16px 0 8px">Top Keywords:</div><div class="keyword-grid">';
    for (const [word, data] of Object.entries(kw.top_keywords)) {
        kwHtml += `<span class="keyword-tag">${escHtml(word)} <small>${data.count}x</small></span>`;
    }
    kwHtml += '</div>';
    document.getElementById('keywordDetails').innerHTML = kwHtml;
}

// Humanize button
document.getElementById('humanizeBtn').addEventListener('click', async () => {
    if (!currentContent) { alert('Analyze content first'); return; }
    
    const btn = document.getElementById('humanizeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Working...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'humanize');
    fd.append('csrf_token', csrf);
    fd.append('content', currentContent);
    fd.append('style', selectedStyle);
    fd.append('keywords', document.getElementById('keywordsInput').value);
    fd.append('provider', document.querySelector('[name="ai_provider"]')?.value || 'openai');
    fd.append('model', document.querySelector('[name="ai_model"]')?.value || 'gpt-4.1-mini');
    
    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.ok) {
            document.getElementById('contentInput').value = data.content;
            document.querySelector('.tab[data-tab="paste"]').click();
            currentContent = data.content;
            // Auto re-analyze
            btn.disabled = false;
            btn.innerHTML = '🔄 Humanize';
            document.getElementById('analyzeBtn').click();
            return;
        } else {
            alert(data.error || 'Humanization failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '🔄 Humanize';
});

// Improve Readability button
document.getElementById('readabilityBtn').addEventListener('click', async () => {
    if (!currentContent) { alert('Analyze content first'); return; }
    
    const btn = document.getElementById('readabilityBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Improving...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'improve_readability');
    fd.append('csrf_token', csrf);
    fd.append('content', currentContent);
    fd.append('keywords', document.getElementById('keywordsInput').value);
    fd.append('provider', document.querySelector('[name="ai_provider"]')?.value || 'openai');
    fd.append('model', document.querySelector('[name="ai_model"]')?.value || 'gpt-4.1-mini');

    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.ok) {
            document.getElementById('contentInput').value = data.content;
            document.querySelector('.tab[data-tab="paste"]').click();
            currentContent = data.content;
            // Auto re-analyze
            btn.disabled = false;
            btn.innerHTML = '📖 Improve Readability';
            document.getElementById('analyzeBtn').click();
            return;
        } else {
            alert(data.error || 'Readability improvement failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '📖 Improve Readability';
});

// Uniqueness options selection
let selectedUniqueness = 'medium';
document.querySelectorAll('.uniqueness-options .style-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.uniqueness-options .style-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        selectedUniqueness = opt.dataset.level;
    });
});

// Make Unique button
// Perfect Content - All-in-one
document.getElementById('perfectBtn').addEventListener('click', async () => {
    if (!currentContent) { alert('Analyze content first'); return; }
    
    const btn = document.getElementById('perfectBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Perfecting...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'perfect_content');
    fd.append('csrf_token', csrf);
    fd.append('content', currentContent);
    fd.append('keywords', document.getElementById('keywordsInput').value);
    fd.append('provider', document.querySelector('[name="ai_provider"]')?.value || 'openai');
    fd.append('model', document.querySelector('[name="ai_model"]')?.value || 'gpt-4.1-mini');

    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.ok) {
            document.getElementById('contentInput').value = data.content;
            document.querySelector('.tab[data-tab="paste"]').click();
            currentContent = data.content;
            // Auto re-analyze
            btn.disabled = false;
            btn.innerHTML = '🎯 Perfect My Content';
            document.getElementById('analyzeBtn').click();
            return;
        } else {
            alert(data.error || 'Content perfection failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '🎯 Perfect My Content';
});

document.getElementById('uniqueBtn').addEventListener('click', async () => {
    if (!currentContent) { alert('Analyze content first'); return; }
    
    const btn = document.getElementById('uniqueBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Rewriting...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'make_unique');
    fd.append('csrf_token', csrf);
    fd.append('content', currentContent);
    fd.append('level', selectedUniqueness);
    fd.append('keywords', document.getElementById('keywordsInput').value);
    fd.append('provider', document.querySelector('[name="ai_provider"]')?.value || 'openai');
    fd.append('model', document.querySelector('[name="ai_model"]')?.value || 'gpt-4.1-mini');

    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.ok) {
            document.getElementById('contentInput').value = data.content;
            document.querySelector('.tab[data-tab="paste"]').click();
            currentContent = data.content;
            // Auto re-analyze
            btn.disabled = false;
            btn.innerHTML = '✨ Make Unique';
            document.getElementById('analyzeBtn').click();
            return;
        } else {
            alert(data.error || 'Uniqueness improvement failed');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '✨ Make Unique';
});

// Clear button
document.getElementById('clearBtn').addEventListener('click', () => {
    document.getElementById('contentInput').value = '';
    document.getElementById('keywordsInput').value = '';
    document.getElementById('resultsPanel').classList.remove('show');
    currentContent = '';
    currentArticleId = null;
});

// Save mode state
let saveMode = 'new';

// Tab switching
document.querySelectorAll('.save-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.save-tab').forEach(t => {
            t.classList.remove('active');
            t.classList.remove('btn-primary');
            t.classList.add('btn-secondary');
        });
        tab.classList.add('active');
        tab.classList.remove('btn-secondary');
        tab.classList.add('btn-primary');
        
        saveMode = tab.dataset.mode;
        document.getElementById('saveNewPanel').style.display = saveMode === 'new' ? 'block' : 'none';
        document.getElementById('saveUpdatePanel').style.display = saveMode === 'update' ? 'block' : 'none';
    });
});

// Save to Article - Open Modal
document.getElementById('saveArticleBtn').addEventListener('click', async () => {
    if (!currentContent) { 
        alert('No content to save. Generate or paste content first.'); 
        return; 
    }
    
    // Load articles list for update mode
    const select = document.getElementById('saveArticleSelect');
    select.innerHTML = '<option value="">Loading...</option>';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'list_articles');
    fd.append('csrf_token', csrf);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.ok) {
        select.innerHTML = '<option value="">-- Select article --</option>' + 
            data.articles.map(a => `<option value="${a.id}" ${a.id === currentArticleId ? 'selected' : ''}>${escHtml(a.title)} (${a.status})</option>`).join('');
    }
    
    document.getElementById('saveModal').style.display = 'flex';
});

// Cancel Save Modal
document.getElementById('cancelSaveBtn').addEventListener('click', () => {
    document.getElementById('saveModal').style.display = 'none';
});

// Confirm Save - handles both new and update
document.getElementById('confirmSaveBtn').addEventListener('click', async () => {
    const btn = document.getElementById('confirmSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Saving...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('csrf_token', csrf);
    fd.append('content', currentContent);
    
    if (saveMode === 'new') {
        const title = document.getElementById('newArticleTitle').value.trim();
        if (title.length < 3) {
            alert('Please enter a title (min 3 characters)');
            btn.disabled = false;
            btn.innerHTML = '💾 Save Article';
            return;
        }
        fd.append('action', 'save_as_new');
        fd.append('title', title);
    } else {
        const articleId = document.getElementById('saveArticleSelect').value;
        if (!articleId) {
            alert('Please select an article');
            btn.disabled = false;
            btn.innerHTML = '💾 Save Article';
            return;
        }
        fd.append('action', 'save_to_article');
        fd.append('article_id', articleId);
    }
    
    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.ok) {
            if (saveMode === 'new') {
                alert('✅ New article "' + data.title + '" created as draft!');
                currentArticleId = parseInt(data.id);
                document.getElementById('newArticleTitle').value = '';
            } else {
                alert('✅ Article "' + data.title + '" updated!');
                currentArticleId = parseInt(document.getElementById('saveArticleSelect').value);
            }
            document.getElementById('saveModal').style.display = 'none';
        } else {
            alert(data.error || 'Failed to save');
        }
    } catch (err) {
        alert('Error: ' + err.message);
    }
    
    btn.disabled = false;
    btn.innerHTML = '💾 Save Article';
});

// Helpers
function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

// Auto-load content and keywords from Content Creator (via sessionStorage)
const storedContent = sessionStorage.getItem('quality_check_content');
const storedKeywords = sessionStorage.getItem('quality_check_keywords');
if (storedContent) {
    document.getElementById('contentInput').value = storedContent;
    sessionStorage.removeItem('quality_check_content');
}
if (storedKeywords) {
    document.getElementById('keywordsInput').value = storedKeywords;
    sessionStorage.removeItem('quality_check_keywords');
}
if (storedContent) {
    // Auto-trigger analysis
    setTimeout(() => document.getElementById('analyzeBtn').click(), 500);
}
</script>
</body>
</html>
