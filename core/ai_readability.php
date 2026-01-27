<?php
/**
 * Readability Analyzer
 * Premium SEO feature for content readability assessment
 *
 * Implements multiple readability formulas:
 * - Flesch-Kincaid Reading Ease
 * - Flesch-Kincaid Grade Level
 * - Gunning Fog Index
 * - SMOG Index
 * - Coleman-Liau Index
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Count syllables in a word (English)
 *
 * @param string $word Single word
 * @return int Syllable count
 */
function ai_readability_count_syllables(string $word): int
{
    $word = strtolower(trim($word));
    $word = preg_replace('/[^a-z]/', '', $word);

    if (strlen($word) === 0) {
        return 0;
    }

    if (strlen($word) <= 3) {
        return 1;
    }

    // Common exceptions
    $exceptions = [
        'simile' => 3, 'area' => 3, 'idea' => 3, 'real' => 2,
        'create' => 2, 'being' => 2, 'business' => 2, 'every' => 2
    ];

    if (isset($exceptions[$word])) {
        return $exceptions[$word];
    }

    // Remove silent e at end
    $word = preg_replace('/e$/', '', $word);

    // Count vowel groups
    preg_match_all('/[aeiouy]+/', $word, $matches);
    $count = count($matches[0]);

    // Adjust for common patterns
    if (preg_match('/le$/', $word) && strlen($word) > 2) {
        $count++;
    }

    // Handle -ed endings
    if (preg_match('/[^aeiou]ed$/', $word)) {
        $count--;
    }

    return max(1, $count);
}

/**
 * Check if a word is complex (3+ syllables)
 *
 * @param string $word Single word
 * @return bool True if complex
 */
function ai_readability_is_complex_word(string $word): bool
{
    // Exclude common suffixes that add syllables but don't add complexity
    $word = preg_replace('/(ing|ed|es|ly)$/i', '', $word);
    return ai_readability_count_syllables($word) >= 3;
}

/**
 * Detect passive voice in a sentence
 *
 * @param string $sentence Single sentence
 * @return bool True if passive voice detected
 */
function ai_readability_is_passive(string $sentence): bool
{
    $sentence = strtolower($sentence);

    // Pattern: be verb + past participle
    $beVerbs = '(is|are|was|were|been|being|be|am)';
    $pastParticiple = '\w+ed\b'; // Simplified pattern

    // Common passive constructions
    $patterns = [
        '/' . $beVerbs . '\s+\w+\s*' . $pastParticiple . '/',
        '/' . $beVerbs . '\s+' . $pastParticiple . '/',
        '/has been\s+\w+ed/',
        '/have been\s+\w+ed/',
        '/had been\s+\w+ed/',
        '/will be\s+\w+ed/',
        '/being\s+\w+ed/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $sentence)) {
            return true;
        }
    }

    return false;
}

/**
 * Split text into sentences
 *
 * @param string $text Plain text
 * @return array Array of sentences
 */
function ai_readability_split_sentences(string $text): array
{
    // Normalize whitespace
    $text = preg_replace('/\s+/', ' ', trim($text));

    // Split on sentence-ending punctuation
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    // Filter out very short "sentences" (likely abbreviations)
    $sentences = array_filter($sentences, function($s) {
        return str_word_count($s) >= 2;
    });

    return array_values($sentences);
}

/**
 * Split text into words
 *
 * @param string $text Plain text
 * @return array Array of words
 */
function ai_readability_split_words(string $text): array
{
    // Remove punctuation and split
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    return array_values($words);
}

/**
 * Calculate Flesch-Kincaid Reading Ease
 * Higher = easier (0-100 scale, 60-70 is ideal for web)
 *
 * @param int $totalWords Total word count
 * @param int $totalSentences Total sentence count
 * @param int $totalSyllables Total syllable count
 * @return float Reading ease score
 */
function ai_readability_flesch_ease(int $totalWords, int $totalSentences, int $totalSyllables): float
{
    if ($totalWords === 0 || $totalSentences === 0) {
        return 0;
    }

    $avgSentenceLength = $totalWords / $totalSentences;
    $avgSyllablesPerWord = $totalSyllables / $totalWords;

    $score = 206.835 - (1.015 * $avgSentenceLength) - (84.6 * $avgSyllablesPerWord);

    return max(0, min(100, round($score, 1)));
}

/**
 * Calculate Flesch-Kincaid Grade Level
 * Returns US school grade level needed to understand text
 *
 * @param int $totalWords Total word count
 * @param int $totalSentences Total sentence count
 * @param int $totalSyllables Total syllable count
 * @return float Grade level (0-18+)
 */
function ai_readability_flesch_grade(int $totalWords, int $totalSentences, int $totalSyllables): float
{
    if ($totalWords === 0 || $totalSentences === 0) {
        return 0;
    }

    $avgSentenceLength = $totalWords / $totalSentences;
    $avgSyllablesPerWord = $totalSyllables / $totalWords;

    $grade = (0.39 * $avgSentenceLength) + (11.8 * $avgSyllablesPerWord) - 15.59;

    return max(0, round($grade, 1));
}

/**
 * Calculate Gunning Fog Index
 * Estimates years of formal education needed
 *
 * @param int $totalWords Total word count
 * @param int $totalSentences Total sentence count
 * @param int $complexWords Number of complex words (3+ syllables)
 * @return float Fog index
 */
function ai_readability_gunning_fog(int $totalWords, int $totalSentences, int $complexWords): float
{
    if ($totalWords === 0 || $totalSentences === 0) {
        return 0;
    }

    $avgSentenceLength = $totalWords / $totalSentences;
    $percentComplex = ($complexWords / $totalWords) * 100;

    $fog = 0.4 * ($avgSentenceLength + $percentComplex);

    return max(0, round($fog, 1));
}

/**
 * Calculate SMOG Index
 * Simple Measure of Gobbledygook
 *
 * @param int $complexWords Number of complex words
 * @param int $totalSentences Total sentence count
 * @return float SMOG index
 */
function ai_readability_smog(int $complexWords, int $totalSentences): float
{
    if ($totalSentences === 0) {
        return 0;
    }

    // SMOG formula requires at least 30 sentences for accuracy
    // For shorter texts, we extrapolate
    $sentenceFactor = max(30, $totalSentences);
    $adjustedComplex = ($complexWords / $totalSentences) * $sentenceFactor;

    $smog = 1.0430 * sqrt($adjustedComplex) + 3.1291;

    return max(0, round($smog, 1));
}

/**
 * Calculate Coleman-Liau Index
 * Based on characters instead of syllables
 *
 * @param int $totalCharacters Total character count (letters only)
 * @param int $totalWords Total word count
 * @param int $totalSentences Total sentence count
 * @return float Coleman-Liau index
 */
function ai_readability_coleman_liau(int $totalCharacters, int $totalWords, int $totalSentences): float
{
    if ($totalWords === 0) {
        return 0;
    }

    // L = avg letters per 100 words
    $L = ($totalCharacters / $totalWords) * 100;

    // S = avg sentences per 100 words
    $S = ($totalSentences / $totalWords) * 100;

    $cli = (0.0588 * $L) - (0.296 * $S) - 15.8;

    return max(0, round($cli, 1));
}

/**
 * Get reading ease interpretation
 *
 * @param float $score Flesch Reading Ease score
 * @return array Interpretation with label, description, audience
 */
function ai_readability_interpret_ease(float $score): array
{
    if ($score >= 90) {
        return [
            'label' => 'Very Easy',
            'grade' => '5th grade',
            'description' => 'Easily understood by an average 11-year-old',
            'audience' => 'General public, children',
            'color' => 'success'
        ];
    } elseif ($score >= 80) {
        return [
            'label' => 'Easy',
            'grade' => '6th grade',
            'description' => 'Conversational English for consumers',
            'audience' => 'General public',
            'color' => 'success'
        ];
    } elseif ($score >= 70) {
        return [
            'label' => 'Fairly Easy',
            'grade' => '7th grade',
            'description' => 'Easily understood by 13-15 year-olds',
            'audience' => 'Teenagers, general audience',
            'color' => 'success'
        ];
    } elseif ($score >= 60) {
        return [
            'label' => 'Standard',
            'grade' => '8th-9th grade',
            'description' => 'Plain English, easily understood by most adults',
            'audience' => 'Most web content target',
            'color' => 'primary'
        ];
    } elseif ($score >= 50) {
        return [
            'label' => 'Fairly Difficult',
            'grade' => '10th-12th grade',
            'description' => 'High school level',
            'audience' => 'High school graduates',
            'color' => 'warning'
        ];
    } elseif ($score >= 30) {
        return [
            'label' => 'Difficult',
            'grade' => 'College',
            'description' => 'Best understood by college graduates',
            'audience' => 'Professionals, academics',
            'color' => 'warning'
        ];
    } else {
        return [
            'label' => 'Very Difficult',
            'grade' => 'College graduate',
            'description' => 'Best understood by university graduates',
            'audience' => 'Specialists, experts',
            'color' => 'danger'
        ];
    }
}

/**
 * Analyze text readability
 *
 * @param string $html HTML content
 * @return array Complete readability analysis
 */
function ai_readability_analyze(string $html): array
{
    // Strip HTML and normalize
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/', ' ', trim($text));

    if (empty($text)) {
        return [
            'ok' => false,
            'error' => 'No text content to analyze'
        ];
    }

    // Basic counts
    $sentences = ai_readability_split_sentences($text);
    $words = ai_readability_split_words($text);

    $totalSentences = count($sentences);
    $totalWords = count($words);

    if ($totalWords < 10) {
        return [
            'ok' => false,
            'error' => 'Not enough text for accurate analysis (minimum 10 words)'
        ];
    }

    // Count syllables and complex words
    $totalSyllables = 0;
    $complexWords = 0;
    $complexWordList = [];
    $wordLengths = [];

    foreach ($words as $word) {
        $syllables = ai_readability_count_syllables($word);
        $totalSyllables += $syllables;
        $wordLengths[] = strlen($word);

        if (ai_readability_is_complex_word($word) && strlen($word) >= 6) {
            $complexWords++;
            $wordLower = strtolower($word);
            if (!isset($complexWordList[$wordLower])) {
                $complexWordList[$wordLower] = 0;
            }
            $complexWordList[$wordLower]++;
        }
    }

    // Character count (letters only)
    $totalCharacters = strlen(preg_replace('/[^a-zA-Z]/', '', $text));

    // Sentence lengths
    $sentenceLengths = array_map(function($s) {
        return str_word_count($s);
    }, $sentences);

    $avgSentenceLength = $totalWords / max(1, $totalSentences);
    $maxSentenceLength = !empty($sentenceLengths) ? max($sentenceLengths) : 0;
    $minSentenceLength = !empty($sentenceLengths) ? min($sentenceLengths) : 0;

    // Passive voice detection
    $passiveSentences = 0;
    $passiveExamples = [];
    foreach ($sentences as $sentence) {
        if (ai_readability_is_passive($sentence)) {
            $passiveSentences++;
            if (count($passiveExamples) < 3) {
                $passiveExamples[] = mb_substr($sentence, 0, 80) . (mb_strlen($sentence) > 80 ? '...' : '');
            }
        }
    }
    $passivePercentage = $totalSentences > 0 ? round(($passiveSentences / $totalSentences) * 100, 1) : 0;

    // Calculate readability scores
    $fleschEase = ai_readability_flesch_ease($totalWords, $totalSentences, $totalSyllables);
    $fleschGrade = ai_readability_flesch_grade($totalWords, $totalSentences, $totalSyllables);
    $gunningFog = ai_readability_gunning_fog($totalWords, $totalSentences, $complexWords);
    $smogIndex = ai_readability_smog($complexWords, $totalSentences);
    $colemanLiau = ai_readability_coleman_liau($totalCharacters, $totalWords, $totalSentences);

    // Average grade level
    $avgGradeLevel = round(($fleschGrade + $gunningFog + $smogIndex + $colemanLiau) / 4, 1);

    // Interpretation
    $interpretation = ai_readability_interpret_ease($fleschEase);

    // Sort complex words by frequency
    arsort($complexWordList);
    $topComplexWords = array_slice(array_keys($complexWordList), 0, 10);

    // Generate recommendations
    $recommendations = [];

    if ($fleschEase < 60) {
        $recommendations[] = [
            'priority' => 'high',
            'issue' => 'Content is too difficult to read',
            'suggestion' => 'Simplify vocabulary and shorten sentences for better engagement'
        ];
    }

    if ($avgSentenceLength > 20) {
        $recommendations[] = [
            'priority' => 'high',
            'issue' => 'Sentences are too long (avg: ' . round($avgSentenceLength) . ' words)',
            'suggestion' => 'Break long sentences into shorter ones (aim for 15-20 words)'
        ];
    }

    if ($passivePercentage > 15) {
        $recommendations[] = [
            'priority' => 'medium',
            'issue' => 'Too much passive voice (' . $passivePercentage . '%)',
            'suggestion' => 'Convert passive sentences to active voice for clarity'
        ];
    }

    if (($complexWords / max(1, $totalWords)) > 0.15) {
        $recommendations[] = [
            'priority' => 'medium',
            'issue' => 'Too many complex words',
            'suggestion' => 'Replace complex words with simpler alternatives'
        ];
    }

    if ($maxSentenceLength > 40) {
        $recommendations[] = [
            'priority' => 'low',
            'issue' => 'Very long sentence detected (' . $maxSentenceLength . ' words)',
            'suggestion' => 'Consider breaking the longest sentence into smaller parts'
        ];
    }

    // Calculate overall readability score (0-100)
    $readabilityScore = (int)round($fleschEase);

    return [
        'ok' => true,
        'score' => $readabilityScore,
        'interpretation' => $interpretation,
        'statistics' => [
            'total_words' => $totalWords,
            'total_sentences' => $totalSentences,
            'total_syllables' => $totalSyllables,
            'total_characters' => $totalCharacters,
            'complex_words' => $complexWords,
            'complex_word_percentage' => round(($complexWords / max(1, $totalWords)) * 100, 1),
            'avg_sentence_length' => round($avgSentenceLength, 1),
            'max_sentence_length' => $maxSentenceLength,
            'min_sentence_length' => $minSentenceLength,
            'avg_word_length' => round(array_sum($wordLengths) / max(1, count($wordLengths)), 1),
            'avg_syllables_per_word' => round($totalSyllables / max(1, $totalWords), 2),
            'passive_sentences' => $passiveSentences,
            'passive_percentage' => $passivePercentage,
        ],
        'formulas' => [
            'flesch_reading_ease' => $fleschEase,
            'flesch_kincaid_grade' => $fleschGrade,
            'gunning_fog_index' => $gunningFog,
            'smog_index' => $smogIndex,
            'coleman_liau_index' => $colemanLiau,
            'average_grade_level' => $avgGradeLevel,
        ],
        'complex_words' => $topComplexWords,
        'passive_examples' => $passiveExamples,
        'recommendations' => $recommendations,
    ];
}

/**
 * Get readability grade label
 *
 * @param float $grade Grade level number
 * @return string Human-readable grade
 */
function ai_readability_grade_label(float $grade): string
{
    if ($grade <= 5) {
        return '5th grade or below';
    } elseif ($grade <= 8) {
        return round($grade) . 'th grade';
    } elseif ($grade <= 12) {
        return 'High school';
    } elseif ($grade <= 16) {
        return 'College level';
    } else {
        return 'Graduate level';
    }
}
