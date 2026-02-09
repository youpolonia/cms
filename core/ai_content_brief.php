<?php
/**
 * AI Content Brief Generator
 * Generates comprehensive writing briefs using AI (with template fallback)
 *
 * Features:
 * - AI-generated questions (People Also Ask style)
 * - AI-generated LSI keywords
 * - AI-generated content outline
 * - AI-generated title suggestions
 * - Template fallback when AI unavailable
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_content.php';

// ────────────────────────────────────────────────────────────────
// Content type configurations
// ────────────────────────────────────────────────────────────────

function ai_brief_get_content_types(): array
{
    return [
        'blog_post'    => ['label' => 'Blog Post',          'min_words' => 1200, 'max_words' => 2500, 'ideal_words' => 1800, 'headings_min' => 5,  'headings_max' => 12, 'paragraphs_per_section' => 3, 'tone' => 'conversational, engaging'],
        'pillar_page'  => ['label' => 'Pillar Page',        'min_words' => 3000, 'max_words' => 5000, 'ideal_words' => 4000, 'headings_min' => 10, 'headings_max' => 20, 'paragraphs_per_section' => 4, 'tone' => 'authoritative, comprehensive'],
        'product_page' => ['label' => 'Product Page',       'min_words' => 500,  'max_words' => 1500, 'ideal_words' => 800,  'headings_min' => 4,  'headings_max' => 8,  'paragraphs_per_section' => 2, 'tone' => 'persuasive, benefit-focused'],
        'landing_page' => ['label' => 'Landing Page',       'min_words' => 400,  'max_words' => 1200, 'ideal_words' => 700,  'headings_min' => 3,  'headings_max' => 6,  'paragraphs_per_section' => 2, 'tone' => 'compelling, action-oriented'],
        'how_to_guide' => ['label' => 'How-To Guide',       'min_words' => 1500, 'max_words' => 3500, 'ideal_words' => 2200, 'headings_min' => 8,  'headings_max' => 15, 'paragraphs_per_section' => 3, 'tone' => 'instructional, clear'],
        'comparison'   => ['label' => 'Comparison Article', 'min_words' => 2000, 'max_words' => 4000, 'ideal_words' => 2800, 'headings_min' => 8,  'headings_max' => 15, 'paragraphs_per_section' => 3, 'tone' => 'objective, analytical'],
        'listicle'     => ['label' => 'Listicle',           'min_words' => 1500, 'max_words' => 3000, 'ideal_words' => 2000, 'headings_min' => 10, 'headings_max' => 25, 'paragraphs_per_section' => 2, 'tone' => 'engaging, scannable'],
    ];
}

// ────────────────────────────────────────────────────────────────
// Helper: get first available AI provider
// ────────────────────────────────────────────────────────────────

function ai_brief_get_provider(): ?array
{
    $settings = ai_config_load_full();
    if (empty($settings['providers'])) return null;
    foreach ($settings['providers'] as $name => $cfg) {
        if (!empty($cfg['api_key'])) {
            return ['provider' => $name, 'model' => $cfg['default_model'] ?? ''];
        }
    }
    return null;
}

// ────────────────────────────────────────────────────────────────
// AI-powered generators (with template fallback)
// ────────────────────────────────────────────────────────────────

function ai_brief_generate_questions_ai(string $keyword, string $contentType, string $audience): array
{
    $ai = ai_brief_get_provider();
    if (!$ai) return ai_brief_generate_questions_template($keyword);

    $system = "You are an SEO content strategist. Generate questions people ask about topics. Return ONLY a JSON array of strings, no markdown, no explanation.";
    $prompt = "Generate 8 unique questions that people commonly ask about \"{$keyword}\" (for a {$contentType} targeting {$audience}). Include:
- 2 basic/definitional questions
- 2 practical how-to questions  
- 2 comparison/evaluation questions
- 2 advanced/expert questions

Return as JSON array: [\"question 1\", \"question 2\", ...]";

    $result = ai_universal_generate($ai['provider'], $ai['model'], $system, $prompt, ['max_tokens' => 1000, 'temperature' => 0.8]);

    if ($result['ok'] && !empty($result['content'])) {
        $parsed = json_decode(trim($result['content']), true);
        // Try extracting JSON from markdown code blocks
        if (!is_array($parsed)) {
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $result['content'], $m)) {
                $parsed = json_decode($m[1], true);
            }
        }
        if (is_array($parsed) && count($parsed) >= 3) return $parsed;
    }

    return ai_brief_generate_questions_template($keyword);
}

function ai_brief_generate_keywords_ai(string $keyword, string $contentType, string $audience): array
{
    $ai = ai_brief_get_provider();
    if (!$ai) return ai_brief_generate_keywords_template($keyword);

    $system = "You are an SEO keyword researcher. Generate related keywords and LSI terms. Return ONLY valid JSON, no markdown.";
    $prompt = "For the topic \"{$keyword}\" ({$contentType} for {$audience}), generate:
1. \"primary\": the main keyword (string)
2. \"secondary\": 5 long-tail keyword variations (array of strings)
3. \"lsi\": 5 semantically related terms/phrases that aren't just prefix/suffix variations (array of strings)

Return as JSON object: {\"primary\": \"...\", \"secondary\": [...], \"lsi\": [...]}";

    $result = ai_universal_generate($ai['provider'], $ai['model'], $system, $prompt, ['max_tokens' => 800, 'temperature' => 0.7]);

    if ($result['ok'] && !empty($result['content'])) {
        $parsed = json_decode(trim($result['content']), true);
        if (!is_array($parsed)) {
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $result['content'], $m)) {
                $parsed = json_decode($m[1], true);
            }
        }
        if (is_array($parsed) && !empty($parsed['secondary'])) return $parsed;
    }

    return ai_brief_generate_keywords_template($keyword);
}

function ai_brief_generate_titles_ai(string $keyword, string $contentType, string $audience): array
{
    $ai = ai_brief_get_provider();
    if (!$ai) return ai_brief_generate_titles_template($keyword, $contentType);

    $year = date('Y');
    $system = "You are a headline copywriter. Generate compelling, SEO-optimized titles. Return ONLY a JSON array of strings.";
    $prompt = "Generate 5 compelling title options for a {$contentType} about \"{$keyword}\" targeting {$audience} (year: {$year}).

Rules:
- Include the keyword naturally
- Mix styles: question, how-to, numbered, power words
- Keep under 60 characters when possible
- Make them click-worthy but not clickbait

Return as JSON array: [\"title 1\", \"title 2\", ...]";

    $result = ai_universal_generate($ai['provider'], $ai['model'], $system, $prompt, ['max_tokens' => 600, 'temperature' => 0.9]);

    if ($result['ok'] && !empty($result['content'])) {
        $parsed = json_decode(trim($result['content']), true);
        if (!is_array($parsed)) {
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $result['content'], $m)) {
                $parsed = json_decode($m[1], true);
            }
        }
        if (is_array($parsed) && count($parsed) >= 3) return $parsed;
    }

    return ai_brief_generate_titles_template($keyword, $contentType);
}

function ai_brief_generate_outline_ai(string $keyword, string $contentType, string $audience, string $title): array
{
    $ai = ai_brief_get_provider();
    $types = ai_brief_get_content_types();
    $config = $types[$contentType] ?? $types['blog_post'];

    if (!$ai) return ai_brief_generate_outline_template($keyword, $contentType, $title);

    $system = "You are an SEO content architect. Create detailed content outlines. Return ONLY valid JSON, no markdown wrapping.";
    $prompt = "Create a detailed content outline for a {$config['label']} about \"{$keyword}\" targeting {$audience}.

Title: \"{$title}\"
Target word count: {$config['min_words']}-{$config['max_words']}
Tone: {$config['tone']}

Return as JSON array of objects:
[
  {\"level\": \"h2\", \"text\": \"Section Title\", \"notes\": \"What to cover\", \"points\": [\"key point 1\", \"key point 2\"]},
  {\"level\": \"h3\", \"text\": \"Subsection\", \"notes\": \"Details\", \"points\": []},
  ...
]

Include {$config['headings_min']}-{$config['headings_max']} headings. Make headings specific to \"{$keyword}\", not generic.";

    $result = ai_universal_generate($ai['provider'], $ai['model'], $system, $prompt, ['max_tokens' => 2000, 'temperature' => 0.7]);

    if ($result['ok'] && !empty($result['content'])) {
        $parsed = json_decode(trim($result['content']), true);
        if (!is_array($parsed)) {
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $result['content'], $m)) {
                $parsed = json_decode($m[1], true);
            }
        }
        if (is_array($parsed) && count($parsed) >= 3) {
            // Prepend H1
            array_unshift($parsed, [
                'level' => 'h1',
                'text' => $title,
                'notes' => 'Main title — include primary keyword near the beginning',
                'points' => [],
            ]);
            return $parsed;
        }
    }

    return ai_brief_generate_outline_template($keyword, $contentType, $title);
}

// ────────────────────────────────────────────────────────────────
// Template-based fallbacks (original implementation)
// ────────────────────────────────────────────────────────────────

function ai_brief_generate_questions_template(string $keyword): array
{
    $patterns = [
        "What is {$keyword}?",
        "How does {$keyword} work?",
        "Why is {$keyword} important?",
        "What are the benefits of {$keyword}?",
        "How to use {$keyword}?",
        "What are the best {$keyword} options?",
        "How much does {$keyword} cost?",
        "Is {$keyword} worth it?",
        "What are common {$keyword} mistakes?",
        "How to choose the right {$keyword}?",
        "{$keyword} vs alternatives — which is better?",
        "What are {$keyword} best practices?",
        "How to get started with {$keyword}?",
        "What do experts say about {$keyword}?",
        "What are {$keyword} examples?",
    ];
    shuffle($patterns);
    return array_slice($patterns, 0, 8);
}

function ai_brief_generate_keywords_template(string $keyword): array
{
    $prefixes = ['best', 'top', 'how to', 'guide to', 'ultimate', 'free', 'professional'];
    $suffixes = ['guide', 'tips', 'examples', 'tutorial', 'for beginners', 'explained', 'strategies', 'tools'];

    $secondary = [];
    foreach (array_slice($prefixes, 0, 3) as $p) $secondary[] = $p . ' ' . $keyword;
    foreach (array_slice($suffixes, 0, 2) as $s) $secondary[] = $keyword . ' ' . $s;

    return [
        'primary'   => $keyword,
        'secondary' => $secondary,
        'lsi'       => array_map(fn($s) => $keyword . ' ' . $s, array_slice($suffixes, 2, 5)),
    ];
}

function ai_brief_generate_titles_template(string $keyword, string $contentType): array
{
    $kw = ucwords($keyword);
    $year = date('Y');
    $map = [
        'blog_post'    => ["The Ultimate Guide to {$kw} in {$year}", "Everything You Need to Know About {$kw}", "{$kw}: A Complete Beginner's Guide", "Why {$kw} Matters (And How to Get Started)", "The Truth About {$kw}: What Experts Won't Tell You"],
        'how_to_guide' => ["How to {$kw}: Step-by-Step Guide", "How to {$kw} in {$year} (Complete Tutorial)", "{$kw}: A Practical How-To Guide", "The Easy Way to {$kw}", "How to {$kw} Like a Pro"],
        'listicle'     => ["10 Best {$kw} Options in {$year}", "15 {$kw} Tips You Need to Know", "Top 10 {$kw} for Every Budget", "7 {$kw} Mistakes to Avoid", "12 Amazing {$kw} Examples"],
        'comparison'   => ["{$kw} Comparison: Which Is Best?", "{$kw} vs [Alternative]: Complete Comparison", "Best {$kw} Compared: {$year} Guide", "Choosing the Right {$kw}: Detailed Comparison"],
        'pillar_page'  => ["{$kw}: The Definitive Guide", "The Complete {$kw} Resource", "{$kw} 101: Everything You Need to Know", "Mastering {$kw}: The Ultimate Resource"],
    ];
    return $map[$contentType] ?? ["{$kw}: What You Need to Know", "Understanding {$kw}", "A Guide to {$kw}", "{$kw} Explained"];
}

function ai_brief_generate_outline_template(string $keyword, string $contentType, string $title = ''): array
{
    if (empty($title)) $title = ucwords($keyword);
    $outline = [['level' => 'h1', 'text' => $title, 'notes' => 'Main title — include primary keyword', 'points' => []]];

    $sections = match($contentType) {
        'blog_post' => [
            ['h2', "What is {$keyword}?", 'Define the topic, set context'],
            ['h2', "Why {$keyword} Matters", 'Explain importance'],
            ['h2', "Key Benefits of {$keyword}", 'List 3-5 benefits'],
            ['h2', "How to Get Started with {$keyword}", 'Practical steps'],
            ['h2', "Common Mistakes to Avoid", 'Help readers avoid pitfalls'],
            ['h2', "Conclusion", 'Summarize + CTA'],
        ],
        'pillar_page' => [
            ['h2', "Table of Contents", 'Link to sections'],
            ['h2', "Introduction to {$keyword}", 'Overview'],
            ['h2', "Key Concepts and Terminology", 'Definitions'],
            ['h2', "Types of {$keyword}", 'Categories'],
            ['h2', "Best Practices", 'Recommendations'],
            ['h2', "Tools and Resources", 'Helpful tools'],
            ['h2', "Case Studies", 'Examples'],
            ['h2', "FAQ", 'Common questions'],
            ['h2', "Conclusion", 'Summary + CTAs'],
        ],
        'how_to_guide' => [
            ['h2', "Overview", 'What readers will learn'],
            ['h2', "Prerequisites", 'What you need'],
            ['h2', "Step 1: [First Action]", 'Clear step'],
            ['h2', "Step 2: [Second Action]", 'Build on previous'],
            ['h2', "Step 3: [Third Action]", 'Continue'],
            ['h2', "Step 4: [Final Action]", 'Complete'],
            ['h2', "Pro Tips", 'Advanced tips'],
            ['h2', "Troubleshooting", 'Common issues'],
            ['h2', "Conclusion", 'Recap'],
        ],
        'comparison' => [
            ['h2', "Quick Comparison", 'Summary table'],
            ['h2', "What to Look For", 'Criteria'],
            ['h2', "Option 1: [Name]", 'Analysis'],
            ['h2', "Option 2: [Name]", 'Analysis'],
            ['h2', "Head-to-Head Comparison", 'Direct comparison'],
            ['h2', "Which One Should You Choose?", 'Recommendations'],
            ['h2', "FAQ", 'Questions'],
            ['h2', "Verdict", 'Winner'],
        ],
        'listicle' => array_merge(
            [['h2', "Introduction", 'Why this list matters']],
            array_map(fn($i) => ['h2', "{$i}. [Item Name]", 'Description + benefits'], range(1, 10)),
            [['h2', "Conclusion", 'Summary']]
        ),
        default => [
            ['h2', "Introduction", 'Set context'],
            ['h2', "Main Section 1", 'Core content'],
            ['h2', "Main Section 2", 'Supporting content'],
            ['h2', "Main Section 3", 'Additional details'],
            ['h2', "Conclusion", 'CTA'],
        ],
    };

    foreach ($sections as $s) {
        $outline[] = ['level' => $s[0], 'text' => $s[1], 'notes' => $s[2], 'points' => []];
    }

    return $outline;
}

// ────────────────────────────────────────────────────────────────
// Internal links from DB
// ────────────────────────────────────────────────────────────────

function ai_brief_get_internal_links(string $keyword, int $limit = 5): array
{
    $suggestions = [];
    try {
        $pdo = \core\Database::connection();
        $kwLower = '%' . strtolower($keyword) . '%';
        $stmt = $pdo->prepare("SELECT id, title, slug FROM pages WHERE status = 'published' AND (LOWER(title) LIKE ? OR LOWER(content) LIKE ?) LIMIT ?");
        $stmt->execute([$kwLower, $kwLower, $limit]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $suggestions[] = ['title' => $row['title'], 'url' => '/' . $row['slug'], 'anchor_suggestion' => $row['title']];
        }
        $stmt = $pdo->prepare("SELECT id, title, slug FROM articles WHERE status = 'published' AND (LOWER(title) LIKE ? OR LOWER(content) LIKE ?) LIMIT ?");
        $stmt->execute([$kwLower, $kwLower, $limit]);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $suggestions[] = ['title' => $row['title'], 'url' => '/article/' . $row['slug'], 'anchor_suggestion' => $row['title']];
        }
    } catch (\Exception $e) {}
    return array_slice($suggestions, 0, $limit);
}

// ────────────────────────────────────────────────────────────────
// Main generator
// ────────────────────────────────────────────────────────────────

function ai_brief_generate(string $keyword, string $contentType = 'blog_post', array $options = []): array
{
    $keyword = trim($keyword);
    if (empty($keyword)) return ['ok' => false, 'error' => 'Keyword is required'];

    $types = ai_brief_get_content_types();
    if (!isset($types[$contentType])) $contentType = 'blog_post';
    $config = $types[$contentType];

    $audience = $options['audience'] ?? 'general audience';
    $useAi = ($options['use_ai'] ?? true) && ai_brief_get_provider() !== null;

    // Generate all components (AI or template)
    $titles   = $useAi ? ai_brief_generate_titles_ai($keyword, $config['label'], $audience) : ai_brief_generate_titles_template($keyword, $contentType);
    $keywords = $useAi ? ai_brief_generate_keywords_ai($keyword, $config['label'], $audience) : ai_brief_generate_keywords_template($keyword);
    $questions = $useAi ? ai_brief_generate_questions_ai($keyword, $config['label'], $audience) : ai_brief_generate_questions_template($keyword);
    $outline  = $useAi ? ai_brief_generate_outline_ai($keyword, $contentType, $audience, $titles[0] ?? ucwords($keyword)) : ai_brief_generate_outline_template($keyword, $contentType, $titles[0] ?? '');
    $internalLinks = ai_brief_get_internal_links($keyword);

    // Normalize keywords structure
    $primary   = is_string($keywords['primary'] ?? null) ? $keywords['primary'] : $keyword;
    $secondary = is_array($keywords['secondary'] ?? null) ? $keywords['secondary'] : [];
    $lsi       = is_array($keywords['lsi'] ?? null) ? $keywords['lsi'] : [];

    // Normalize outline — ensure each item has points array
    $outline = array_map(function($item) {
        if (!isset($item['points'])) $item['points'] = [];
        if (!isset($item['notes'])) $item['notes'] = '';
        return $item;
    }, $outline);

    // Build the brief
    // Map outline to sections format used by UI
    $sections = [];
    foreach ($outline as $item) {
        if (($item['level'] ?? '') !== 'h1') {
            $sections[] = [
                'heading' => $item['text'] ?? '',
                'description' => $item['notes'] ?? '',
                'points' => $item['points'] ?? [],
            ];
        }
    }

    return [
        'ok' => true,
        'generated_at' => gmdate('Y-m-d H:i:s'),
        'ai_powered' => $useAi,
        'keyword' => $keyword,
        'content_type' => $contentType,
        'content_type_label' => $config['label'],
        'difficulty' => 'Medium', // Could be AI-enhanced later

        'title_suggestions' => $titles,

        'recommended_length' => [
            'min' => $config['min_words'],
            'max' => $config['max_words'],
            'ideal' => $config['ideal_words'],
        ],

        'content_specs' => [
            'word_count' => ['minimum' => $config['min_words'], 'ideal' => $config['ideal_words'], 'maximum' => $config['max_words']],
            'headings' => ['minimum' => $config['headings_min'], 'maximum' => $config['headings_max']],
            'paragraphs_per_section' => $config['paragraphs_per_section'],
        ],

        'style_guidelines' => [
            'tone' => $options['tone'] ?? $config['tone'],
            'audience' => $audience,
            'reading_level' => 'Grade 8-10 (accessible to most readers)',
            'tips' => [
                'Use short paragraphs (2-4 sentences)',
                'Include bullet points for scanability',
                'Add relevant images every 300-400 words',
                'Use transition words between sections',
                'Include data and statistics where possible',
            ],
        ],

        'sections' => $sections,
        'outline' => $outline,
        'questions' => $questions,
        'questions_to_answer' => $questions,

        'keywords' => [
            'primary' => [$primary],
            'secondary' => array_slice($secondary, 0, 5),
            'lsi' => array_slice($lsi, 0, 5),
        ],

        'internal_links' => $internalLinks,
        'external_sources' => [],

        'seo_checklist' => [
            ['task' => 'Include primary keyword in title', 'priority' => 'high'],
            ['task' => 'Include primary keyword in first 100 words', 'priority' => 'high'],
            ['task' => 'Include primary keyword in at least one H2', 'priority' => 'high'],
            ['task' => 'Add meta description with keyword (150-160 chars)', 'priority' => 'high'],
            ['task' => 'Include 2-5 internal links', 'priority' => 'medium'],
            ['task' => 'Add 1-3 external links to authoritative sources', 'priority' => 'medium'],
            ['task' => 'Optimize images with alt text', 'priority' => 'medium'],
            ['task' => 'Use secondary keywords naturally', 'priority' => 'medium'],
            ['task' => 'Include FAQ section for featured snippets', 'priority' => 'low'],
            ['task' => 'Add schema markup if applicable', 'priority' => 'low'],
        ],
    ];
}

// ────────────────────────────────────────────────────────────────
// Markdown export
// ────────────────────────────────────────────────────────────────

function ai_brief_to_markdown(array $brief): string
{
    if (!($brief['ok'] ?? false)) return '# Error: ' . ($brief['error'] ?? 'Unknown');

    $md = "# Content Brief: {$brief['keyword']}\n\n";
    $md .= "**Content Type:** {$brief['content_type_label']}\n";
    $md .= "**Generated:** {$brief['generated_at']}" . ($brief['ai_powered'] ? ' (AI-powered)' : ' (template)') . "\n\n---\n\n";

    $md .= "## Title Suggestions\n\n";
    foreach ($brief['title_suggestions'] as $i => $t) $md .= ($i + 1) . ". {$t}\n";
    $md .= "\n";

    $specs = $brief['content_specs'];
    $md .= "## Content Specifications\n\n";
    $md .= "- **Word Count:** {$specs['word_count']['minimum']}–{$specs['word_count']['maximum']} (ideal: {$specs['word_count']['ideal']})\n";
    $md .= "- **Headings:** {$specs['headings']['minimum']}–{$specs['headings']['maximum']}\n\n";

    $style = $brief['style_guidelines'];
    $md .= "## Style\n\n- **Tone:** {$style['tone']}\n- **Audience:** {$style['audience']}\n\n";

    $md .= "## Content Outline\n\n";
    foreach ($brief['outline'] as $item) {
        $prefix = match($item['level'] ?? 'h2') { 'h1' => '# ', 'h3' => '### ', default => '## ' };
        $md .= "{$prefix}{$item['text']}\n";
        if (!empty($item['notes'])) $md .= "*{$item['notes']}*\n";
        if (!empty($item['points'])) foreach ($item['points'] as $p) $md .= "- {$p}\n";
        $md .= "\n";
    }

    $md .= "## Questions to Answer\n\n";
    foreach ($brief['questions'] as $q) $md .= "- {$q}\n";
    $md .= "\n";

    $kw = $brief['keywords'];
    $md .= "## Keywords\n\n**Primary:** " . implode(', ', $kw['primary']) . "\n**Secondary:** " . implode(', ', $kw['secondary']) . "\n**LSI:** " . implode(', ', $kw['lsi']) . "\n\n";

    if (!empty($brief['internal_links'])) {
        $md .= "## Internal Links\n\n";
        foreach ($brief['internal_links'] as $l) $md .= "- [{$l['title']}]({$l['url']})\n";
        $md .= "\n";
    }

    $md .= "## SEO Checklist\n\n";
    foreach ($brief['seo_checklist'] as $item) $md .= "- [ ] [{$item['priority']}] {$item['task']}\n";

    return $md;
}
