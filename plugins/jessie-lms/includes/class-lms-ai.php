<?php
declare(strict_types=1);

class LmsAI
{
    /**
     * Generate a course outline from a topic.
     */
    public static function generateOutline(string $topic, string $difficulty = 'beginner', int $lessonCount = 8, string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Create a structured course outline.\n\n"
            . "Topic: {$topic}\nDifficulty: {$difficulty}\nNumber of lessons: {$lessonCount}\nLanguage: {$language}\n\n"
            . "Return JSON:\n{\"title\": \"Course Title\", \"description\": \"2-3 sentence description\", \"short_description\": \"1 sentence\", \"sections\": [{\"name\": \"Section Name\", \"lessons\": [{\"title\": \"Lesson Title\", \"type\": \"text|video|quiz\", \"duration_minutes\": 15, \"summary\": \"brief description\"}]}]}\n"
            . "Group lessons into 2-4 sections. Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 1000, 'temperature' => 0.5]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate lesson content from title/outline.
     */
    public static function generateLessonContent(string $title, string $courseTitle, string $difficulty = 'beginner', string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $prompt = "Write educational lesson content.\n\nCourse: {$courseTitle}\nLesson: {$title}\nDifficulty: {$difficulty}\nLanguage: {$language}\n\n"
            . "Requirements:\n- Use HTML (<h2>, <p>, <ul>, <pre><code>, <blockquote>)\n- Include learning objectives at top\n- Clear explanations with examples\n- Key takeaways at bottom\n- 600-1000 words\n\n"
            . "Return JSON: {\"content_html\": \"<h2>...\", \"quiz_questions\": [{\"question\": \"...\", \"options\": [\"A\",\"B\",\"C\",\"D\"], \"correct\": 0}]}\n"
            . "Include 2-3 quiz questions. Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.4]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    /**
     * Generate quiz questions for a lesson.
     */
    public static function generateQuiz(string $lessonContent, int $numQuestions = 5, string $language = 'en'): array
    {
        if (!function_exists('ai_universal_generate')) { require_once CMS_ROOT . '/core/ai_content.php'; }

        $text = strip_tags($lessonContent);
        if (strlen($text) > 3000) $text = substr($text, 0, 3000);

        $prompt = "Generate {$numQuestions} quiz questions based on this lesson content.\n\n{$text}\n\nLanguage: {$language}\n\n"
            . "Return JSON: {\"questions\": [{\"question\": \"...\", \"options\": [\"A\",\"B\",\"C\",\"D\"], \"correct\": 0, \"explanation\": \"why\"}]}\n"
            . "correct = index of correct option (0-3). Return ONLY valid JSON.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 1000, 'temperature' => 0.3]);
        $data = self::parseJson($response);
        return $data ? ['ok' => true, 'questions' => $data['questions'] ?? []] : ['ok' => false, 'error' => 'AI generation failed'];
    }

    private static function parseJson(string $response): ?array
    {
        $response = trim($response);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) $response = $m[1];
        $data = json_decode($response, true);
        if ($data) return $data;
        if (preg_match('/\{[\s\S]*\}/', $response, $m)) return json_decode($m[0], true);
        return null;
    }
}
