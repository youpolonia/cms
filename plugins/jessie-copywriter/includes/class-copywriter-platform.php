<?php
namespace Plugins\JessieCopywriter;

/**
 * Platform-specific formatting rules for product copy
 * Each platform has unique character limits and structure requirements
 */
class CopywriterPlatform {

    /** Platform configuration: limits, required fields, structure */
    private static array $platforms = [
        'amazon' => [
            'label' => 'Amazon',
            'title_max' => 200,
            'description_max' => 2000,
            'bullet_count' => 5,
            'bullet_max' => 500,
            'has_backend_keywords' => true,
            'search_terms_max' => 250,
            'structure' => "Title (max 200 chars, include main keyword first)\n5 bullet points (each max 500 chars, start with CAPITAL benefit word)\nProduct description (max 2000 chars, HTML allowed: <b>, <br>, <ul>, <li>)\nBackend search terms (max 250 chars, no commas, no brand name, no ASINs)"
        ],
        'ebay' => [
            'label' => 'eBay',
            'title_max' => 80,
            'description_max' => 4000,
            'has_item_specifics' => true,
            'structure' => "Title (max 80 chars, keyword-rich, no special characters like !, *, or ALL CAPS)\nSubtitle (optional, max 55 chars)\nProduct description (HTML formatted, max 4000 chars, clear sections: Features, Specifications, What's Included)\nItem specifics as key-value pairs"
        ],
        'etsy' => [
            'label' => 'Etsy',
            'title_max' => 140,
            'description_max' => 2000,
            'tags_count' => 13,
            'tag_max' => 20,
            'structure' => "Title (max 140 chars, front-load keywords, use natural language)\nProduct description (max 2000 chars, storytelling style, mention materials, dimensions, care instructions)\n13 tags (each max 20 chars, multi-word tags preferred, no single-word tags)\nPersonalization options if applicable"
        ],
        'shopify' => [
            'label' => 'Shopify',
            'title_max' => 255,
            'description_max' => 5000,
            'meta_title_max' => 70,
            'meta_description_max' => 160,
            'structure' => "Product title (clear, descriptive)\nRich HTML description (use headings, bullet points, paragraphs — persuasive and SEO-friendly)\nSEO meta title (max 70 chars)\nSEO meta description (max 160 chars)\nTags (comma-separated, for internal organization)"
        ],
        'google_ads' => [
            'label' => 'Google Ads',
            'headlines_count' => 15,
            'headline_max' => 30,
            'descriptions_count' => 4,
            'description_max' => 90,
            'structure' => "Up to 15 headlines (each max 30 chars, include keywords, CTAs, unique selling points)\n4 descriptions (each max 90 chars, compelling, include CTA)\nDisplay path suggestions (2 paths, max 15 chars each)"
        ],
        'facebook_ads' => [
            'label' => 'Facebook Ads',
            'primary_text_max' => 125,
            'headline_max' => 40,
            'description_max' => 30,
            'long_text_max' => 500,
            'structure' => "Primary text (max 125 chars for optimal display, hook-first)\nHeadline (max 40 chars, clear value proposition)\nDescription (max 30 chars, supporting text)\nLong-form ad copy variant (max 500 chars, storytelling approach with emoji)"
        ],
        'general' => [
            'label' => 'General',
            'title_max' => 255,
            'description_max' => 5000,
            'structure' => "Product title\nProduct description (clear, persuasive, SEO-friendly)\nKey features as bullet points\nSEO meta title and description"
        ]
    ];

    public static function getAll(): array {
        return self::$platforms;
    }

    public static function get(string $platform): ?array {
        return self::$platforms[$platform] ?? null;
    }

    public static function getLabels(): array {
        $labels = [];
        foreach (self::$platforms as $key => $p) {
            $labels[$key] = $p['label'];
        }
        return $labels;
    }

    /**
     * Build platform-specific prompt instructions
     */
    public static function buildPromptInstructions(string $platform): string {
        $p = self::$platforms[$platform] ?? self::$platforms['general'];
        $instructions = "FORMAT OUTPUT FOR: {$p['label']}\n\n";
        $instructions .= "STRUCTURE REQUIREMENTS:\n{$p['structure']}\n\n";
        $instructions .= "CHARACTER LIMITS:\n";

        if (isset($p['title_max']))       $instructions .= "- Title: max {$p['title_max']} characters\n";
        if (isset($p['headline_max']))     $instructions .= "- Headline: max {$p['headline_max']} characters\n";
        if (isset($p['description_max']))  $instructions .= "- Description: max {$p['description_max']} characters\n";
        if (isset($p['bullet_count']))     $instructions .= "- Bullet points: exactly {$p['bullet_count']}\n";
        if (isset($p['bullet_max']))       $instructions .= "- Each bullet: max {$p['bullet_max']} characters\n";
        if (isset($p['tags_count']))       $instructions .= "- Tags: exactly {$p['tags_count']}, each max {$p['tag_max']} chars\n";
        if (isset($p['headlines_count']))  $instructions .= "- Headlines: up to {$p['headlines_count']}, each max {$p['headline_max']} chars\n";
        if (isset($p['descriptions_count'])) $instructions .= "- Descriptions: {$p['descriptions_count']}, each max {$p['description_max']} chars\n";
        if (isset($p['meta_title_max']))   $instructions .= "- Meta title: max {$p['meta_title_max']} chars\n";
        if (isset($p['meta_description_max'])) $instructions .= "- Meta description: max {$p['meta_description_max']} chars\n";
        if (isset($p['primary_text_max'])) $instructions .= "- Primary text: max {$p['primary_text_max']} chars\n";

        return $instructions;
    }

    /**
     * Parse AI response into structured platform-specific sections
     */
    public static function parseResponse(string $raw, string $platform): array {
        $result = [
            'title' => '',
            'description' => '',
            'bullet_points' => '',
            'meta_title' => '',
            'meta_description' => '',
            'tags' => '',
            'raw' => $raw
        ];

        // Extract sections using markdown-style headers or labels
        $lines = explode("\n", $raw);
        $currentSection = '';
        $sectionContent = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $lower = strtolower($trimmed);

            // Detect section headers
            if (preg_match('/^#{1,3}\s*(.+)/i', $trimmed, $m)) {
                if ($currentSection && !empty($sectionContent)) {
                    self::assignSection($result, $currentSection, implode("\n", $sectionContent));
                    $sectionContent = [];
                }
                $currentSection = strtolower(trim($m[1], '* '));
                continue;
            }

            if (preg_match('/^\*\*(.+?)\*\*:?\s*(.*)/i', $trimmed, $m)) {
                if ($currentSection && !empty($sectionContent)) {
                    self::assignSection($result, $currentSection, implode("\n", $sectionContent));
                    $sectionContent = [];
                }
                $currentSection = strtolower(trim($m[1], '* '));
                if (!empty($m[2])) {
                    $sectionContent[] = $m[2];
                }
                continue;
            }

            if (str_starts_with($lower, 'title:') || str_starts_with($lower, 'product title:')) {
                if ($currentSection && !empty($sectionContent)) {
                    self::assignSection($result, $currentSection, implode("\n", $sectionContent));
                    $sectionContent = [];
                }
                $currentSection = 'title';
                $val = trim(preg_replace('/^(product )?title:\s*/i', '', $trimmed));
                if ($val) $sectionContent[] = $val;
                continue;
            }

            $sectionContent[] = $line;
        }

        if ($currentSection && !empty($sectionContent)) {
            self::assignSection($result, $currentSection, implode("\n", $sectionContent));
        }

        // Fallback: if no title parsed, use first non-empty line
        if (empty($result['title']) && !empty($lines)) {
            foreach ($lines as $l) {
                $l = trim($l, "# *\t\n\r");
                if (!empty($l)) { $result['title'] = $l; break; }
            }
        }

        // Fallback: if no description, use everything
        if (empty($result['description'])) {
            $result['description'] = $raw;
        }

        return $result;
    }

    private static function assignSection(array &$result, string $section, string $content): void {
        $content = trim($content);
        $section = preg_replace('/[^a-z_ ]/', '', $section);

        if (str_contains($section, 'title') && !str_contains($section, 'meta') && !str_contains($section, 'seo')) {
            if (empty($result['title'])) $result['title'] = $content;
        } elseif (str_contains($section, 'meta title') || str_contains($section, 'seo title')) {
            $result['meta_title'] = $content;
        } elseif (str_contains($section, 'meta description') || str_contains($section, 'seo description')) {
            $result['meta_description'] = $content;
        } elseif (str_contains($section, 'bullet') || str_contains($section, 'key feature') || str_contains($section, 'highlight')) {
            $result['bullet_points'] = $content;
        } elseif (str_contains($section, 'tag') || str_contains($section, 'keyword') || str_contains($section, 'search term')) {
            $result['tags'] = $content;
        } elseif (str_contains($section, 'description') || str_contains($section, 'copy') || str_contains($section, 'body') || str_contains($section, 'text') || str_contains($section, 'primary') || str_contains($section, 'headline')) {
            $result['description'] .= ($result['description'] ? "\n\n" : '') . $content;
        }
    }
}
