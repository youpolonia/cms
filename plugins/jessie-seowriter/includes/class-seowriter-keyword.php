<?php
namespace Plugins\JessieSeoWriter;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * SEO Writer — Keyword Research via AI
 * Generates primary, long-tail, LSI and question keywords
 */
class SeoWriterKeyword {

    /**
     * Research keywords for a given seed keyword
     * @return array{success:bool, keywords:array, error?:string}
     */
    public function research(string $seedKeyword, string $language = 'en', string $niche = ''): array {
        $seedKeyword = trim($seedKeyword);
        if ($seedKeyword === '') {
            return ['success' => false, 'error' => 'Keyword is required'];
        }

        $nicheCtx = $niche ? " in the niche of \"$niche\"" : '';
        $langCtx = $language !== 'en' ? " Respond in language code: $language." : '';

        $prompt = <<<PROMPT
You are an expert SEO keyword researcher. Analyze the seed keyword "{$seedKeyword}"{$nicheCtx} and return a JSON object with exactly these keys:

1. "primary" — array of 5 high-volume primary keywords related to the seed keyword. Each item: {"keyword": "...", "volume": estimated monthly search volume as integer, "difficulty": 1-100 integer, "intent": "informational|commercial|transactional|navigational"}

2. "long_tail" — array of 8 long-tail keyword phrases (3-6 words). Each item: {"keyword": "...", "volume": estimated monthly volume integer, "difficulty": 1-100 integer}

3. "lsi" — array of 8 LSI (Latent Semantic Indexing) keywords. Each item: {"keyword": "...", "relevance": 1-100 integer}

4. "questions" — array of 8 question-based keywords people search for. Each item: {"keyword": "...", "volume": estimated monthly volume integer}

Volume estimates should be realistic. Difficulty 1=very easy, 100=very hard.{$langCtx}

Return ONLY valid JSON, no markdown fences, no explanation.
PROMPT;

        $result = ai_content_generate(['topic' => $prompt, 'language' => $language]);

        if (!($result['ok'] ?? false) || empty($result['content'])) {
            return ['success' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $content = $result['content'];
        // Strip markdown code fences if present
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```\s*$/', '', $content);

        $parsed = json_decode(trim($content), true);
        if (!is_array($parsed)) {
            return ['success' => false, 'error' => 'Failed to parse AI response as JSON', 'raw' => $content];
        }

        // Normalize structure
        $keywords = [
            'primary'   => $this->normalizeKeywords($parsed['primary'] ?? [], 'primary'),
            'long_tail' => $this->normalizeKeywords($parsed['long_tail'] ?? [], 'long_tail'),
            'lsi'       => $this->normalizeLsi($parsed['lsi'] ?? []),
            'questions' => $this->normalizeQuestions($parsed['questions'] ?? []),
        ];

        return ['success' => true, 'keywords' => $keywords, 'seed' => $seedKeyword];
    }

    private function normalizeKeywords(array $items, string $type): array {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['keyword'])) continue;
            $out[] = [
                'keyword'    => (string)$item['keyword'],
                'volume'     => max(0, (int)($item['volume'] ?? 0)),
                'difficulty' => max(1, min(100, (int)($item['difficulty'] ?? 50))),
                'intent'     => $item['intent'] ?? ($type === 'primary' ? 'informational' : ''),
                'type'       => $type,
            ];
        }
        return $out;
    }

    private function normalizeLsi(array $items): array {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['keyword'])) continue;
            $out[] = [
                'keyword'   => (string)$item['keyword'],
                'relevance' => max(1, min(100, (int)($item['relevance'] ?? 50))),
                'type'      => 'lsi',
            ];
        }
        return $out;
    }

    private function normalizeQuestions(array $items): array {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['keyword'])) continue;
            $out[] = [
                'keyword' => (string)$item['keyword'],
                'volume'  => max(0, (int)($item['volume'] ?? 0)),
                'type'    => 'question',
            ];
        }
        return $out;
    }
}
