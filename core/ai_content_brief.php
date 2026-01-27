<?php
/**
 * AI Content Brief Generator
 * Premium feature for generating comprehensive writing briefs
 *
 * Generates briefs with:
 * - Keyword analysis
 * - Word count recommendations
 * - Outline suggestions
 * - Questions to answer
 * - Related keywords
 * - Competitor insights
 * - Style guidelines
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_seo_assistant.php';
require_once CMS_ROOT . '/core/ai_internal_linking.php';

/**
 * Content type configurations
 */
function ai_brief_get_content_types(): array
{
    return [
        'blog_post' => [
            'label' => 'Blog Post',
            'min_words' => 1200,
            'max_words' => 2500,
            'ideal_words' => 1800,
            'headings_min' => 5,
            'headings_max' => 12,
            'paragraphs_per_section' => 3,
            'tone' => 'conversational, engaging',
            'features' => ['intro', 'body', 'conclusion', 'cta'],
        ],
        'pillar_page' => [
            'label' => 'Pillar Page',
            'min_words' => 3000,
            'max_words' => 5000,
            'ideal_words' => 4000,
            'headings_min' => 10,
            'headings_max' => 20,
            'paragraphs_per_section' => 4,
            'tone' => 'authoritative, comprehensive',
            'features' => ['intro', 'toc', 'body', 'faq', 'conclusion'],
        ],
        'product_page' => [
            'label' => 'Product Page',
            'min_words' => 500,
            'max_words' => 1500,
            'ideal_words' => 800,
            'headings_min' => 4,
            'headings_max' => 8,
            'paragraphs_per_section' => 2,
            'tone' => 'persuasive, benefit-focused',
            'features' => ['headline', 'benefits', 'features', 'social_proof', 'cta'],
        ],
        'landing_page' => [
            'label' => 'Landing Page',
            'min_words' => 400,
            'max_words' => 1200,
            'ideal_words' => 700,
            'headings_min' => 3,
            'headings_max' => 6,
            'paragraphs_per_section' => 2,
            'tone' => 'compelling, action-oriented',
            'features' => ['headline', 'value_prop', 'benefits', 'cta'],
        ],
        'how_to_guide' => [
            'label' => 'How-To Guide',
            'min_words' => 1500,
            'max_words' => 3500,
            'ideal_words' => 2200,
            'headings_min' => 8,
            'headings_max' => 15,
            'paragraphs_per_section' => 3,
            'tone' => 'instructional, clear, step-by-step',
            'features' => ['intro', 'prerequisites', 'steps', 'tips', 'conclusion'],
        ],
        'comparison' => [
            'label' => 'Comparison Article',
            'min_words' => 2000,
            'max_words' => 4000,
            'ideal_words' => 2800,
            'headings_min' => 8,
            'headings_max' => 15,
            'paragraphs_per_section' => 3,
            'tone' => 'objective, analytical',
            'features' => ['intro', 'criteria', 'comparisons', 'verdict', 'faq'],
        ],
        'listicle' => [
            'label' => 'Listicle',
            'min_words' => 1500,
            'max_words' => 3000,
            'ideal_words' => 2000,
            'headings_min' => 10,
            'headings_max' => 25,
            'paragraphs_per_section' => 2,
            'tone' => 'engaging, scannable',
            'features' => ['intro', 'list_items', 'conclusion'],
        ],
    ];
}

/**
 * Generate questions people might ask about a topic
 *
 * @param string $keyword Main keyword
 * @return array List of questions
 */
function ai_brief_generate_questions(string $keyword): array
{
    $keyword = trim($keyword);
    if (empty($keyword)) {
        return [];
    }

    $kwLower = strtolower($keyword);

    // Question patterns
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
        "{$keyword} vs alternatives - which is better?",
        "What are {$keyword} best practices?",
        "How to get started with {$keyword}?",
        "What do experts say about {$keyword}?",
        "What are {$keyword} examples?",
    ];

    // Shuffle and return subset
    shuffle($patterns);

    return array_slice($patterns, 0, 8);
}

/**
 * Generate related/LSI keywords
 *
 * @param string $keyword Main keyword
 * @return array Related keywords with relevance
 */
function ai_brief_generate_related_keywords(string $keyword): array
{
    $keyword = trim($keyword);
    if (empty($keyword)) {
        return [];
    }

    $words = explode(' ', strtolower($keyword));
    $related = [];

    // Modifiers to add
    $prefixes = ['best', 'top', 'how to', 'what is', 'guide to', 'complete', 'ultimate', 'free', 'professional'];
    $suffixes = ['guide', 'tips', 'examples', 'tutorial', 'for beginners', 'explained', 'strategies', 'tools', 'software', 'services'];

    // Generate variations
    foreach ($prefixes as $prefix) {
        $related[] = [
            'keyword' => $prefix . ' ' . $keyword,
            'type' => 'long-tail',
            'priority' => 'medium',
        ];
    }

    foreach ($suffixes as $suffix) {
        $related[] = [
            'keyword' => $keyword . ' ' . $suffix,
            'type' => 'long-tail',
            'priority' => 'medium',
        ];
    }

    // Word variations
    $synonymGroups = [
        'guide' => ['tutorial', 'handbook', 'manual', 'walkthrough'],
        'tips' => ['advice', 'tricks', 'hacks', 'strategies'],
        'best' => ['top', 'leading', 'recommended', 'popular'],
        'free' => ['no cost', 'complimentary', 'gratis'],
        'how to' => ['ways to', 'steps to', 'method for'],
    ];

    foreach ($words as $word) {
        if (isset($synonymGroups[$word])) {
            foreach ($synonymGroups[$word] as $syn) {
                $newKw = str_replace($word, $syn, $keyword);
                $related[] = [
                    'keyword' => $newKw,
                    'type' => 'synonym',
                    'priority' => 'low',
                ];
            }
        }
    }

    // Shuffle and limit
    shuffle($related);

    return array_slice($related, 0, 15);
}

/**
 * Generate outline based on content type
 *
 * @param string $keyword Main keyword
 * @param string $contentType Content type key
 * @param string $title Suggested title
 * @return array Outline with headings
 */
function ai_brief_generate_outline(string $keyword, string $contentType, string $title = ''): array
{
    $types = ai_brief_get_content_types();
    $config = $types[$contentType] ?? $types['blog_post'];

    $outline = [];

    // Title
    if (empty($title)) {
        $title = ucwords($keyword);
    }

    $outline[] = [
        'level' => 'h1',
        'text' => $title,
        'notes' => 'Main title - include primary keyword near the beginning',
    ];

    // Generate based on content type
    switch ($contentType) {
        case 'blog_post':
            $outline[] = ['level' => 'h2', 'text' => "What is {$keyword}?", 'notes' => 'Define the topic, set context'];
            $outline[] = ['level' => 'h2', 'text' => "Why {$keyword} Matters", 'notes' => 'Explain importance and relevance'];
            $outline[] = ['level' => 'h2', 'text' => "Key Benefits of {$keyword}", 'notes' => 'List 3-5 main benefits'];
            $outline[] = ['level' => 'h3', 'text' => 'Benefit 1: [Specific Benefit]', 'notes' => 'Expand on first benefit'];
            $outline[] = ['level' => 'h3', 'text' => 'Benefit 2: [Specific Benefit]', 'notes' => 'Expand on second benefit'];
            $outline[] = ['level' => 'h2', 'text' => "How to Get Started with {$keyword}", 'notes' => 'Practical steps or tips'];
            $outline[] = ['level' => 'h2', 'text' => "Common Mistakes to Avoid", 'notes' => 'Help readers avoid pitfalls'];
            $outline[] = ['level' => 'h2', 'text' => "Conclusion", 'notes' => 'Summarize key points, include CTA'];
            break;

        case 'pillar_page':
            $outline[] = ['level' => 'h2', 'text' => "Table of Contents", 'notes' => 'Link to all major sections'];
            $outline[] = ['level' => 'h2', 'text' => "Introduction to {$keyword}", 'notes' => 'Comprehensive overview'];
            $outline[] = ['level' => 'h2', 'text' => "History and Background", 'notes' => 'Context and evolution'];
            $outline[] = ['level' => 'h2', 'text' => "Key Concepts and Terminology", 'notes' => 'Define important terms'];
            $outline[] = ['level' => 'h2', 'text' => "Types of {$keyword}", 'notes' => 'Categories or variations'];
            $outline[] = ['level' => 'h3', 'text' => 'Type 1: [Name]', 'notes' => 'Detail each type'];
            $outline[] = ['level' => 'h3', 'text' => 'Type 2: [Name]', 'notes' => 'Detail each type'];
            $outline[] = ['level' => 'h2', 'text' => "Best Practices", 'notes' => 'Expert recommendations'];
            $outline[] = ['level' => 'h2', 'text' => "Tools and Resources", 'notes' => 'Helpful tools list'];
            $outline[] = ['level' => 'h2', 'text' => "Case Studies", 'notes' => 'Real-world examples'];
            $outline[] = ['level' => 'h2', 'text' => "Frequently Asked Questions", 'notes' => 'FAQ section for SEO'];
            $outline[] = ['level' => 'h2', 'text' => "Conclusion and Next Steps", 'notes' => 'Summary with CTAs'];
            break;

        case 'how_to_guide':
            $outline[] = ['level' => 'h2', 'text' => "Overview", 'notes' => 'What readers will learn'];
            $outline[] = ['level' => 'h2', 'text' => "Prerequisites", 'notes' => 'What you need before starting'];
            $outline[] = ['level' => 'h2', 'text' => "Step 1: [First Action]", 'notes' => 'Clear, actionable step'];
            $outline[] = ['level' => 'h2', 'text' => "Step 2: [Second Action]", 'notes' => 'Build on previous step'];
            $outline[] = ['level' => 'h2', 'text' => "Step 3: [Third Action]", 'notes' => 'Continue progression'];
            $outline[] = ['level' => 'h2', 'text' => "Step 4: [Fourth Action]", 'notes' => 'Nearly complete'];
            $outline[] = ['level' => 'h2', 'text' => "Step 5: [Final Action]", 'notes' => 'Complete the process'];
            $outline[] = ['level' => 'h2', 'text' => "Pro Tips", 'notes' => 'Advanced recommendations'];
            $outline[] = ['level' => 'h2', 'text' => "Troubleshooting Common Issues", 'notes' => 'Help with problems'];
            $outline[] = ['level' => 'h2', 'text' => "Conclusion", 'notes' => 'Recap and next steps'];
            break;

        case 'comparison':
            $outline[] = ['level' => 'h2', 'text' => "Quick Comparison Overview", 'notes' => 'Summary table'];
            $outline[] = ['level' => 'h2', 'text' => "What to Look For", 'notes' => 'Evaluation criteria'];
            $outline[] = ['level' => 'h2', 'text' => "Option 1: [Name] Review", 'notes' => 'Detailed analysis'];
            $outline[] = ['level' => 'h3', 'text' => 'Pros', 'notes' => 'Advantages list'];
            $outline[] = ['level' => 'h3', 'text' => 'Cons', 'notes' => 'Disadvantages list'];
            $outline[] = ['level' => 'h2', 'text' => "Option 2: [Name] Review", 'notes' => 'Detailed analysis'];
            $outline[] = ['level' => 'h3', 'text' => 'Pros', 'notes' => 'Advantages list'];
            $outline[] = ['level' => 'h3', 'text' => 'Cons', 'notes' => 'Disadvantages list'];
            $outline[] = ['level' => 'h2', 'text' => "Head-to-Head Comparison", 'notes' => 'Direct feature comparison'];
            $outline[] = ['level' => 'h2', 'text' => "Which One Should You Choose?", 'notes' => 'Recommendations by use case'];
            $outline[] = ['level' => 'h2', 'text' => "FAQ", 'notes' => 'Common questions'];
            $outline[] = ['level' => 'h2', 'text' => "Final Verdict", 'notes' => 'Conclusion with winner'];
            break;

        case 'listicle':
            $outline[] = ['level' => 'h2', 'text' => "Introduction", 'notes' => 'Why this list matters'];
            for ($i = 1; $i <= 10; $i++) {
                $outline[] = ['level' => 'h2', 'text' => "{$i}. [Item Name]", 'notes' => 'Description, benefits, example'];
            }
            $outline[] = ['level' => 'h2', 'text' => "Honorable Mentions", 'notes' => 'Additional options'];
            $outline[] = ['level' => 'h2', 'text' => "Conclusion", 'notes' => 'Summary and recommendation'];
            break;

        case 'product_page':
            $outline[] = ['level' => 'h2', 'text' => "Product Overview", 'notes' => 'Key value proposition'];
            $outline[] = ['level' => 'h2', 'text' => "Key Features", 'notes' => 'Main features list'];
            $outline[] = ['level' => 'h2', 'text' => "Benefits", 'notes' => 'What users gain'];
            $outline[] = ['level' => 'h2', 'text' => "How It Works", 'notes' => 'Simple explanation'];
            $outline[] = ['level' => 'h2', 'text' => "Specifications", 'notes' => 'Technical details'];
            $outline[] = ['level' => 'h2', 'text' => "Customer Reviews", 'notes' => 'Social proof'];
            $outline[] = ['level' => 'h2', 'text' => "Pricing", 'notes' => 'Clear pricing info'];
            break;

        case 'landing_page':
            $outline[] = ['level' => 'h2', 'text' => "[Compelling Headline]", 'notes' => 'Grab attention immediately'];
            $outline[] = ['level' => 'h2', 'text' => "The Problem", 'notes' => 'Pain point identification'];
            $outline[] = ['level' => 'h2', 'text' => "The Solution", 'notes' => 'Your offering'];
            $outline[] = ['level' => 'h2', 'text' => "Key Benefits", 'notes' => '3-5 benefit bullets'];
            $outline[] = ['level' => 'h2', 'text' => "Social Proof", 'notes' => 'Testimonials, logos'];
            $outline[] = ['level' => 'h2', 'text' => "Call to Action", 'notes' => 'Clear next step'];
            break;

        default:
            $outline[] = ['level' => 'h2', 'text' => "Introduction", 'notes' => 'Set the context'];
            $outline[] = ['level' => 'h2', 'text' => "Main Section 1", 'notes' => 'Core content'];
            $outline[] = ['level' => 'h2', 'text' => "Main Section 2", 'notes' => 'Supporting content'];
            $outline[] = ['level' => 'h2', 'text' => "Main Section 3", 'notes' => 'Additional details'];
            $outline[] = ['level' => 'h2', 'text' => "Conclusion", 'notes' => 'Wrap up with CTA'];
    }

    return $outline;
}

/**
 * Get internal linking suggestions for a brief
 *
 * @param string $keyword Target keyword
 * @param int $limit Max suggestions
 * @return array Internal link suggestions
 */
function ai_brief_get_internal_links(string $keyword, int $limit = 5): array
{
    $suggestions = [];

    try {
        $pdo = \core\Database::connection();
        $kwLower = '%' . strtolower($keyword) . '%';

        $stmt = $pdo->prepare("
            SELECT id, title, slug
            FROM pages
            WHERE status = 'published'
            AND (LOWER(title) LIKE ? OR LOWER(content) LIKE ?)
            LIMIT ?
        ");
        $stmt->execute([$kwLower, $kwLower, $limit]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $suggestions[] = [
                'title' => $row['title'],
                'url' => '/' . $row['slug'],
                'anchor_suggestion' => $row['title'],
            ];
        }
    } catch (\Exception $e) {
        error_log('ai_brief_get_internal_links: ' . $e->getMessage());
    }

    return $suggestions;
}

/**
 * Generate title suggestions
 *
 * @param string $keyword Main keyword
 * @param string $contentType Content type
 * @return array Title suggestions
 */
function ai_brief_generate_titles(string $keyword, string $contentType): array
{
    $kw = ucwords($keyword);
    $kwLower = strtolower($keyword);
    $year = date('Y');

    $titles = [];

    switch ($contentType) {
        case 'blog_post':
            $titles = [
                "The Ultimate Guide to {$kw} in {$year}",
                "Everything You Need to Know About {$kw}",
                "{$kw}: A Complete Beginner's Guide",
                "Why {$kw} Matters (And How to Get Started)",
                "The Truth About {$kw}: What Experts Won't Tell You",
            ];
            break;

        case 'how_to_guide':
            $titles = [
                "How to {$kw}: Step-by-Step Guide",
                "How to {$kw} in {$year} (Complete Tutorial)",
                "{$kw}: A Practical How-To Guide",
                "The Easy Way to {$kw}",
                "How to {$kw} Like a Pro",
            ];
            break;

        case 'listicle':
            $titles = [
                "10 Best {$kw} Options in {$year}",
                "15 {$kw} Tips You Need to Know",
                "Top 10 {$kw} for Every Budget",
                "7 {$kw} Mistakes to Avoid",
                "12 Amazing {$kw} Examples",
            ];
            break;

        case 'comparison':
            $titles = [
                "{$kw} Comparison: Which Is Best?",
                "{$kw} vs [Alternative]: Complete Comparison",
                "Best {$kw} Compared: {$year} Guide",
                "Choosing the Right {$kw}: A Detailed Comparison",
            ];
            break;

        case 'pillar_page':
            $titles = [
                "{$kw}: The Definitive Guide",
                "The Complete {$kw} Resource",
                "{$kw} 101: Everything You Need to Know",
                "Mastering {$kw}: The Ultimate Resource",
            ];
            break;

        default:
            $titles = [
                "{$kw}: What You Need to Know",
                "Understanding {$kw}",
                "A Guide to {$kw}",
                "{$kw} Explained",
            ];
    }

    return $titles;
}

/**
 * Generate a complete content brief
 *
 * @param string $keyword Target keyword
 * @param string $contentType Content type key
 * @param array $options Additional options
 * @return array Complete brief
 */
function ai_brief_generate(string $keyword, string $contentType = 'blog_post', array $options = []): array
{
    $keyword = trim($keyword);

    if (empty($keyword)) {
        return [
            'ok' => false,
            'error' => 'Keyword is required',
        ];
    }

    $types = ai_brief_get_content_types();
    if (!isset($types[$contentType])) {
        $contentType = 'blog_post';
    }

    $config = $types[$contentType];
    $targetAudience = $options['audience'] ?? 'general audience';
    $tone = $options['tone'] ?? $config['tone'];

    // Generate all components
    $titles = ai_brief_generate_titles($keyword, $contentType);
    $outline = ai_brief_generate_outline($keyword, $contentType, $titles[0] ?? '');
    $questions = ai_brief_generate_questions($keyword);
    $relatedKeywords = ai_brief_generate_related_keywords($keyword);
    $internalLinks = ai_brief_get_internal_links($keyword);

    // Build the brief
    $brief = [
        'ok' => true,
        'generated_at' => gmdate('Y-m-d H:i:s'),
        'keyword' => $keyword,
        'content_type' => $contentType,
        'content_type_label' => $config['label'],

        'title_suggestions' => $titles,

        'content_specs' => [
            'word_count' => [
                'minimum' => $config['min_words'],
                'ideal' => $config['ideal_words'],
                'maximum' => $config['max_words'],
            ],
            'headings' => [
                'minimum' => $config['headings_min'],
                'maximum' => $config['headings_max'],
            ],
            'paragraphs_per_section' => $config['paragraphs_per_section'],
        ],

        'style_guidelines' => [
            'tone' => $tone,
            'audience' => $targetAudience,
            'reading_level' => 'Grade 8-10 (accessible to most readers)',
            'tips' => [
                'Use short paragraphs (2-4 sentences)',
                'Include bullet points for scanability',
                'Add relevant images every 300-400 words',
                'Use transition words between sections',
                'Include data and statistics where possible',
            ],
        ],

        'outline' => $outline,

        'questions_to_answer' => $questions,

        'keywords' => [
            'primary' => $keyword,
            'secondary' => array_slice(array_column(array_filter($relatedKeywords, fn($k) => $k['type'] === 'long-tail'), 'keyword'), 0, 5),
            'lsi' => array_slice(array_column(array_filter($relatedKeywords, fn($k) => $k['type'] === 'synonym'), 'keyword'), 0, 5),
        ],

        'internal_links' => $internalLinks,

        'seo_checklist' => [
            ['task' => 'Include primary keyword in title', 'priority' => 'high'],
            ['task' => 'Include primary keyword in first 100 words', 'priority' => 'high'],
            ['task' => 'Include primary keyword in at least one H2', 'priority' => 'high'],
            ['task' => 'Add meta description with keyword (150-160 chars)', 'priority' => 'high'],
            ['task' => 'Include 2-5 internal links', 'priority' => 'medium'],
            ['task' => 'Add 1-3 external links to authoritative sources', 'priority' => 'medium'],
            ['task' => 'Optimize images with alt text', 'priority' => 'medium'],
            ['task' => 'Use secondary keywords naturally throughout', 'priority' => 'medium'],
            ['task' => 'Include FAQ section for featured snippet potential', 'priority' => 'low'],
            ['task' => 'Add schema markup if applicable', 'priority' => 'low'],
        ],

        'competitor_analysis_tips' => [
            'Search for "' . $keyword . '" and analyze top 5 results',
            'Note their word count, headings, and content structure',
            'Identify gaps in competitor content you can fill',
            'Find unique angles they haven\'t covered',
            'Check their internal/external linking patterns',
        ],
    ];

    return $brief;
}

/**
 * Export brief to markdown format
 *
 * @param array $brief Generated brief
 * @return string Markdown content
 */
function ai_brief_to_markdown(array $brief): string
{
    if (!$brief['ok']) {
        return '# Error: ' . ($brief['error'] ?? 'Unknown error');
    }

    $md = "# Content Brief: {$brief['keyword']}\n\n";
    $md .= "**Content Type:** {$brief['content_type_label']}\n";
    $md .= "**Generated:** {$brief['generated_at']}\n\n";

    $md .= "---\n\n";

    // Title Suggestions
    $md .= "## Title Suggestions\n\n";
    foreach ($brief['title_suggestions'] as $i => $title) {
        $md .= ($i + 1) . ". {$title}\n";
    }
    $md .= "\n";

    // Content Specs
    $md .= "## Content Specifications\n\n";
    $specs = $brief['content_specs'];
    $md .= "- **Word Count:** {$specs['word_count']['minimum']} - {$specs['word_count']['maximum']} (ideal: {$specs['word_count']['ideal']})\n";
    $md .= "- **Headings:** {$specs['headings']['minimum']} - {$specs['headings']['maximum']}\n";
    $md .= "- **Paragraphs per section:** {$specs['paragraphs_per_section']}\n\n";

    // Style Guidelines
    $md .= "## Style Guidelines\n\n";
    $style = $brief['style_guidelines'];
    $md .= "- **Tone:** {$style['tone']}\n";
    $md .= "- **Target Audience:** {$style['audience']}\n";
    $md .= "- **Reading Level:** {$style['reading_level']}\n\n";
    $md .= "### Writing Tips\n\n";
    foreach ($style['tips'] as $tip) {
        $md .= "- {$tip}\n";
    }
    $md .= "\n";

    // Outline
    $md .= "## Content Outline\n\n";
    foreach ($brief['outline'] as $item) {
        $prefix = $item['level'] === 'h1' ? '# ' : ($item['level'] === 'h2' ? '## ' : '### ');
        $md .= "{$prefix}{$item['text']}\n";
        if (!empty($item['notes'])) {
            $md .= "*{$item['notes']}*\n";
        }
        $md .= "\n";
    }

    // Questions
    $md .= "## Questions to Answer\n\n";
    foreach ($brief['questions_to_answer'] as $q) {
        $md .= "- {$q}\n";
    }
    $md .= "\n";

    // Keywords
    $md .= "## Keywords\n\n";
    $kw = $brief['keywords'];
    $md .= "**Primary:** {$kw['primary']}\n\n";
    $md .= "**Secondary:** " . implode(', ', $kw['secondary']) . "\n\n";
    $md .= "**LSI/Related:** " . implode(', ', $kw['lsi']) . "\n\n";

    // Internal Links
    if (!empty($brief['internal_links'])) {
        $md .= "## Suggested Internal Links\n\n";
        foreach ($brief['internal_links'] as $link) {
            $md .= "- [{$link['title']}]({$link['url']})\n";
        }
        $md .= "\n";
    }

    // SEO Checklist
    $md .= "## SEO Checklist\n\n";
    foreach ($brief['seo_checklist'] as $item) {
        $priority = strtoupper($item['priority']);
        $md .= "- [ ] [{$priority}] {$item['task']}\n";
    }
    $md .= "\n";

    return $md;
}
