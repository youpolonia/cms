<?php
/**
 * AI Student Material Generator
 * Generate educational materials for teachers
 *
 * Features:
 * - Worksheets generation
 * - Tests with answer keys
 * - Quizzes (multiple choice, true/false)
 * - Dictation texts
 * - Exercise sets
 * - Lesson plans
 * - Difficulty analysis
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/ai_hf.php';

/**
 * Material types
 */
define('MATERIAL_TYPES', [
    'worksheet' => [
        'label' => 'Worksheet',
        'description' => 'Practice exercises and activities',
        'icon' => '📝',
    ],
    'test' => [
        'label' => 'Test',
        'description' => 'Assessment with answer key',
        'icon' => '📋',
    ],
    'quiz' => [
        'label' => 'Quiz',
        'description' => 'Quick knowledge check',
        'icon' => '❓',
    ],
    'dictation' => [
        'label' => 'Dictation',
        'description' => 'Text for dictation practice',
        'icon' => '🎤',
    ],
    'exercises' => [
        'label' => 'Exercise Set',
        'description' => 'Collection of practice problems',
        'icon' => '✏️',
    ],
    'lesson_plan' => [
        'label' => 'Lesson Plan',
        'description' => 'Complete lesson structure',
        'icon' => '📚',
    ],
    'flashcards' => [
        'label' => 'Flashcards',
        'description' => 'Terms and definitions',
        'icon' => '🃏',
    ],
]);

/**
 * Subject areas
 */
define('SUBJECT_AREAS', [
    'math' => 'Mathematics',
    'science' => 'Science',
    'language' => 'Language Arts',
    'history' => 'History',
    'geography' => 'Geography',
    'biology' => 'Biology',
    'chemistry' => 'Chemistry',
    'physics' => 'Physics',
    'literature' => 'Literature',
    'foreign_language' => 'Foreign Language',
    'art' => 'Art',
    'music' => 'Music',
    'pe' => 'Physical Education',
    'it' => 'Computer Science',
    'other' => 'Other',
]);

/**
 * Grade levels
 */
define('GRADE_LEVELS', [
    'k' => 'Kindergarten',
    '1' => '1st Grade',
    '2' => '2nd Grade',
    '3' => '3rd Grade',
    '4' => '4th Grade',
    '5' => '5th Grade',
    '6' => '6th Grade',
    '7' => '7th Grade',
    '8' => '8th Grade',
    '9' => '9th Grade (Freshman)',
    '10' => '10th Grade (Sophomore)',
    '11' => '11th Grade (Junior)',
    '12' => '12th Grade (Senior)',
    'college' => 'College/University',
]);

/**
 * Difficulty levels
 */
define('DIFFICULTY_LEVELS', [
    'easy' => 'Easy',
    'medium' => 'Medium',
    'hard' => 'Hard',
    'mixed' => 'Mixed (Progressive)',
]);

/**
 * Storage directory for generated materials
 */
define('MATERIALS_STORAGE_DIR', CMS_ROOT . '/cms_storage/student_materials');

/**
 * Ensure storage directory exists
 */
function ai_materials_ensure_storage(): bool
{
    if (!is_dir(MATERIALS_STORAGE_DIR)) {
        return mkdir(MATERIALS_STORAGE_DIR, 0755, true);
    }
    return true;
}

/**
 * Get available material types
 */
function ai_materials_get_types(): array
{
    return MATERIAL_TYPES;
}

/**
 * Get available subjects
 */
function ai_materials_get_subjects(): array
{
    return SUBJECT_AREAS;
}

/**
 * Get grade levels
 */
function ai_materials_get_grades(): array
{
    return GRADE_LEVELS;
}

/**
 * Get difficulty levels
 */
function ai_materials_get_difficulties(): array
{
    return DIFFICULTY_LEVELS;
}

/**
 * Build prompt for material generation
 */
function ai_materials_build_prompt(string $type, array $params): string
{
    $topic = $params['topic'] ?? 'General topic';
    $subject = $params['subject'] ?? 'other';
    $grade = $params['grade'] ?? '6';
    $difficulty = $params['difficulty'] ?? 'medium';
    $questionCount = $params['question_count'] ?? 10;
    $includeAnswers = $params['include_answers'] ?? true;
    $language = $params['language'] ?? 'English';
    $additionalInstructions = $params['instructions'] ?? '';

    $subjectLabel = SUBJECT_AREAS[$subject] ?? $subject;
    $gradeLabel = GRADE_LEVELS[$grade] ?? $grade;
    $difficultyLabel = DIFFICULTY_LEVELS[$difficulty] ?? $difficulty;

    $prompt = "You are an experienced teacher creating educational materials.\n\n";
    $prompt .= "CREATE: " . strtoupper($type) . "\n";
    $prompt .= "Topic: {$topic}\n";
    $prompt .= "Subject: {$subjectLabel}\n";
    $prompt .= "Grade Level: {$gradeLabel}\n";
    $prompt .= "Difficulty: {$difficultyLabel}\n";
    $prompt .= "Language: {$language}\n\n";

    switch ($type) {
        case 'worksheet':
            $prompt .= "Create a worksheet with {$questionCount} exercises.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Clear title\n";
            $prompt .= "- Name and date fields\n";
            $prompt .= "- Varied exercise types (fill-in-blank, matching, short answer)\n";
            $prompt .= "- Clear instructions for each section\n";
            if ($includeAnswers) {
                $prompt .= "- Answer key at the end (clearly marked)\n";
            }
            break;

        case 'test':
            $prompt .= "Create a test with {$questionCount} questions.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Test header with name, date, class fields\n";
            $prompt .= "- Point values for each question\n";
            $prompt .= "- Mix of question types:\n";
            $prompt .= "  * Multiple choice (4 options each)\n";
            $prompt .= "  * True/False\n";
            $prompt .= "  * Short answer\n";
            $prompt .= "  * One extended response question\n";
            $prompt .= "- Total points clearly shown\n";
            if ($includeAnswers) {
                $prompt .= "- Complete answer key with point distribution\n";
            }
            break;

        case 'quiz':
            $prompt .= "Create a quick quiz with {$questionCount} questions.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Quiz title\n";
            $prompt .= "- Multiple choice questions (4 options each, one correct)\n";
            $prompt .= "- Questions should be clear and unambiguous\n";
            if ($includeAnswers) {
                $prompt .= "- Answer key at the end\n";
            }
            break;

        case 'dictation':
            $prompt .= "Create a dictation text suitable for the grade level.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Main text (100-200 words depending on grade)\n";
            $prompt .= "- Key vocabulary words highlighted\n";
            $prompt .= "- Suggested pauses marked with //\n";
            $prompt .= "- Teacher notes for pronunciation emphasis\n";
            $prompt .= "- Grading rubric\n";
            break;

        case 'exercises':
            $prompt .= "Create an exercise set with {$questionCount} problems.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Progressive difficulty (start easy, get harder)\n";
            $prompt .= "- Clear numbering\n";
            $prompt .= "- Space for student work indicated by [SPACE]\n";
            $prompt .= "- Example problem with solution at the start\n";
            if ($includeAnswers) {
                $prompt .= "- Complete answer key with step-by-step solutions for complex problems\n";
            }
            break;

        case 'lesson_plan':
            $prompt .= "Create a complete lesson plan.\n";
            $prompt .= "Include:\n";
            $prompt .= "- Learning objectives (3-5 SMART objectives)\n";
            $prompt .= "- Required materials\n";
            $prompt .= "- Time allocation (45-60 minute lesson)\n";
            $prompt .= "- Warm-up activity (5-10 min)\n";
            $prompt .= "- Main instruction (20-25 min)\n";
            $prompt .= "- Guided practice (10-15 min)\n";
            $prompt .= "- Independent practice (10 min)\n";
            $prompt .= "- Closure/Assessment (5 min)\n";
            $prompt .= "- Differentiation strategies\n";
            $prompt .= "- Homework assignment\n";
            $prompt .= "- Extension activities for advanced students\n";
            break;

        case 'flashcards':
            $prompt .= "Create {$questionCount} flashcards.\n";
            $prompt .= "Format each as:\n";
            $prompt .= "CARD [number]:\n";
            $prompt .= "FRONT: [term or question]\n";
            $prompt .= "BACK: [definition or answer]\n";
            $prompt .= "---\n";
            $prompt .= "Include key terms from the topic.\n";
            break;

        default:
            $prompt .= "Create appropriate educational material for the topic.\n";
    }

    if (!empty($additionalInstructions)) {
        $prompt .= "\nAdditional requirements:\n{$additionalInstructions}\n";
    }

    $prompt .= "\nFormat the output clearly with proper sections and formatting.";
    $prompt .= "\nDo not include any meta-commentary, just the material itself.";

    return $prompt;
}

/**
 * Generate educational material
 *
 * @param string $type Material type
 * @param array $params Generation parameters
 * @return array Result with ok, content, or error
 */
function ai_materials_generate(string $type, array $params = []): array
{
    if (!isset(MATERIAL_TYPES[$type])) {
        return ['ok' => false, 'error' => 'Invalid material type: ' . $type];
    }

    $prompt = ai_materials_build_prompt($type, $params);

    // Estimate tokens based on type
    $maxTokens = match($type) {
        'lesson_plan' => 2000,
        'test' => 1500,
        'worksheet' => 1200,
        'exercises' => 1200,
        'dictation' => 800,
        'quiz' => 1000,
        'flashcards' => 1000,
        default => 1000,
    };

    $result = ai_hf_generate_text($prompt, [
        'params' => [
            'max_new_tokens' => $maxTokens,
            'temperature' => 0.7,
        ]
    ]);

    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
    }

    $content = trim($result['text']);

    // Clean up common AI artifacts
    $content = preg_replace('/^(Here\'s|Here is|I\'ve created|Below is)\s+[^:]+:\s*/i', '', $content);

    return [
        'ok' => true,
        'type' => $type,
        'type_label' => MATERIAL_TYPES[$type]['label'],
        'content' => $content,
        'params' => $params,
        'generated_at' => gmdate('Y-m-d H:i:s'),
    ];
}

/**
 * Save generated material
 *
 * @param array $material Generated material data
 * @return array Result with id or error
 */
function ai_materials_save(array $material): array
{
    ai_materials_ensure_storage();

    $id = uniqid('mat_', true);
    $filename = $id . '.json';
    $filepath = MATERIALS_STORAGE_DIR . '/' . $filename;

    $data = [
        'id' => $id,
        'type' => $material['type'] ?? 'unknown',
        'type_label' => $material['type_label'] ?? '',
        'content' => $material['content'] ?? '',
        'params' => $material['params'] ?? [],
        'created_at' => gmdate('Y-m-d H:i:s'),
    ];

    $saved = file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($saved === false) {
        return ['ok' => false, 'error' => 'Failed to save material'];
    }

    return [
        'ok' => true,
        'id' => $id,
        'filepath' => $filepath,
    ];
}

/**
 * Load saved material
 *
 * @param string $id Material ID
 * @return array|null Material data or null
 */
function ai_materials_load(string $id): ?array
{
    $filepath = MATERIALS_STORAGE_DIR . '/' . $id . '.json';

    if (!file_exists($filepath)) {
        return null;
    }

    $data = json_decode(file_get_contents($filepath), true);
    return is_array($data) ? $data : null;
}

/**
 * List saved materials
 *
 * @param int $limit Max items to return
 * @return array List of materials
 */
function ai_materials_list(int $limit = 50): array
{
    ai_materials_ensure_storage();

    $files = glob(MATERIALS_STORAGE_DIR . '/mat_*.json');
    if (empty($files)) {
        return [];
    }

    // Sort by modification time (newest first)
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

    $materials = [];
    $count = 0;

    foreach ($files as $file) {
        if ($count >= $limit) break;

        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) continue;

        $materials[] = [
            'id' => $data['id'] ?? basename($file, '.json'),
            'type' => $data['type'] ?? 'unknown',
            'type_label' => $data['type_label'] ?? '',
            'topic' => $data['params']['topic'] ?? 'Unknown',
            'grade' => $data['params']['grade'] ?? '',
            'created_at' => $data['created_at'] ?? '',
        ];
        $count++;
    }

    return $materials;
}

/**
 * Delete saved material
 *
 * @param string $id Material ID
 * @return bool Success
 */
function ai_materials_delete(string $id): bool
{
    $filepath = MATERIALS_STORAGE_DIR . '/' . $id . '.json';

    if (!file_exists($filepath)) {
        return false;
    }

    return unlink($filepath);
}

/**
 * Analyze difficulty of generated content
 *
 * @param string $content Content to analyze
 * @return array Difficulty metrics
 */
function ai_materials_analyze_difficulty(string $content): array
{
    // Word count
    $wordCount = str_word_count($content);

    // Sentence count
    $sentenceCount = max(1, preg_match_all('/[.!?]+/', $content, $m));

    // Average words per sentence
    $avgWordsPerSentence = round($wordCount / $sentenceCount, 1);

    // Syllable estimation (rough)
    $syllables = 0;
    $words = str_word_count($content, 1);
    foreach ($words as $word) {
        $syllables += max(1, preg_match_all('/[aeiouy]+/i', $word, $m));
    }
    $avgSyllablesPerWord = $wordCount > 0 ? round($syllables / $wordCount, 1) : 0;

    // Flesch-Kincaid Grade Level (approximation)
    $fkGrade = 0.39 * $avgWordsPerSentence + 11.8 * $avgSyllablesPerWord - 15.59;
    $fkGrade = max(1, min(18, round($fkGrade, 1)));

    // Determine difficulty label
    $difficultyLabel = match(true) {
        $fkGrade <= 4 => 'Elementary',
        $fkGrade <= 6 => 'Middle School (Easy)',
        $fkGrade <= 8 => 'Middle School',
        $fkGrade <= 10 => 'High School (Easy)',
        $fkGrade <= 12 => 'High School',
        default => 'College Level',
    };

    return [
        'word_count' => $wordCount,
        'sentence_count' => $sentenceCount,
        'avg_words_per_sentence' => $avgWordsPerSentence,
        'avg_syllables_per_word' => $avgSyllablesPerWord,
        'flesch_kincaid_grade' => $fkGrade,
        'difficulty_label' => $difficultyLabel,
    ];
}

/**
 * Generate material as HTML for printing
 *
 * @param array $material Material data
 * @return string HTML content
 */
function ai_materials_to_html(array $material): string
{
    $type = $material['type'] ?? 'Material';
    $typeLabel = $material['type_label'] ?? ucfirst($type);
    $topic = $material['params']['topic'] ?? 'Educational Material';
    $grade = GRADE_LEVELS[$material['params']['grade'] ?? ''] ?? '';
    $content = $material['content'] ?? '';

    // Convert markdown-like formatting to HTML
    $content = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
    $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
    $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
    $content = preg_replace('/^(#+)\s*(.+)$/m', '<h3>$2</h3>', $content);

    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($topic) . ' - ' . htmlspecialchars($typeLabel) . '</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        h1 { border-bottom: 2px solid #333; padding-bottom: 10px; }
        h2, h3 { color: #333; }
        .header { margin-bottom: 30px; }
        .meta { color: #666; font-size: 0.9em; }
        .content { margin-top: 20px; }
        .answer-key { margin-top: 40px; border-top: 2px dashed #ccc; padding-top: 20px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($topic) . '</h1>
        <p class="meta">' . htmlspecialchars($typeLabel);

    if ($grade) {
        $html .= ' | ' . htmlspecialchars($grade);
    }

    $html .= '</p>
        <p>Name: _________________________ Date: _____________</p>
    </div>
    <div class="content">
        ' . $content . '
    </div>
    <div class="no-print" style="margin-top: 40px; padding: 10px; background: #f0f0f0;">
        <button onclick="window.print()">Print</button>
    </div>
</body>
</html>';

    return $html;
}

/**
 * Get statistics about generated materials
 */
function ai_materials_get_stats(): array
{
    ai_materials_ensure_storage();

    $files = glob(MATERIALS_STORAGE_DIR . '/mat_*.json');

    $stats = [
        'total' => count($files),
        'by_type' => [],
        'by_subject' => [],
        'by_grade' => [],
    ];

    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) continue;

        $type = $data['type'] ?? 'unknown';
        $subject = $data['params']['subject'] ?? 'other';
        $grade = $data['params']['grade'] ?? 'unknown';

        $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        $stats['by_subject'][$subject] = ($stats['by_subject'][$subject] ?? 0) + 1;
        $stats['by_grade'][$grade] = ($stats['by_grade'][$grade] ?? 0) + 1;
    }

    return $stats;
}
