<?php
/**
 * JTB AI Direct Generator
 *
 * 5-STEP PIPELINE (Divi AI Style):
 * 1. OUTLINE - Creative brief with sections, colors, brand voice
 * 2. WIREFRAME - Pure structure (section → row → column → module type) NO STYLES
 * 3. STYLE - Apply colors, fonts, spacing to wireframe (deterministic)
 * 4. CONTENT - Generate text content for each module
 * 5. IMAGES - Fetch contextual images from Pexels
 *
 * Each step does ONE thing well instead of everything at once.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Direct
{
    // ========================================
    // Constants
    // ========================================

    /** @var int Maximum tokens for outline generation */
    private const MAX_TOKENS_OUTLINE = 2000;

    /** @var int Maximum tokens for wireframe generation */
    private const MAX_TOKENS_WIREFRAME = 3000;

    /** @var int Maximum tokens for content generation */
    private const MAX_TOKENS_CONTENT = 4000;

    /** @var float Temperature for creative generation */
    private const TEMPERATURE_CREATIVE = 0.9;

    /** @var float Temperature for structural generation */
    private const TEMPERATURE_STRUCTURE = 0.3;

    // ========================================
    // Main Generation Method
    // ========================================

    /**
     * Generate complete page layout from prompt
     *
     * 5-STEP DIVI-STYLE PIPELINE:
     * 1. OUTLINE - What sections, what colors, what brand voice
     * 2. WIREFRAME - Pure structure without styles
     * 3. STYLE - Apply design tokens to wireframe
     * 4. CONTENT - Generate text for each module
     * 5. IMAGES - Enrich with Pexels images
     *
     * @param string $prompt User's description of desired page
     * @param array $options Optional parameters
     * @return array Result with keys: ok, layout, error, stats
     */
    public static function generateLayout(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        $stats = ['steps' => []];

        $ai = JTB_AI_Core::getInstance();

        if (!$ai->isConfigured()) {
            return [
                'ok' => false,
                'success' => false, // FIX 2026-02-04: Unified response format
                'layout' => null,
                'error' => 'AI is not configured. Please configure an AI provider in settings.',
                'stats' => ['time_ms' => 0]
            ];
        }

        // ==========================================
        // STEP 1: OUTLINE (Creative Brief)
        // ==========================================
        $step1Start = microtime(true);
        self::log("=== STEP 1: OUTLINE ===");

        $outlineResult = self::step1_generateOutline($prompt, $options, $ai);

        if (!$outlineResult['ok']) {
            return [
                'ok' => false,
                'success' => false, // FIX 2026-02-04: Unified response format
                'layout' => null,
                'error' => 'Step 1 (Outline) failed: ' . ($outlineResult['error'] ?? 'unknown'),
                'stats' => ['time_ms' => self::elapsed($startTime)]
            ];
        }

        $outline = $outlineResult['outline'];
        $stats['steps']['outline'] = [
            'time_ms' => self::elapsed($step1Start),
            'sections_planned' => count($outline['sections'] ?? [])
        ];
        self::log("Outline done: " . count($outline['sections'] ?? []) . " sections planned");

        // ==========================================
        // STEP 2: WIREFRAME (Structure Only)
        // ==========================================
        $step2Start = microtime(true);
        self::log("=== STEP 2: WIREFRAME ===");

        $wireframeResult = self::step2_generateWireframe($outline, $ai);

        if (!$wireframeResult['ok']) {
            return [
                'ok' => false,
                'success' => false, // FIX 2026-02-04: Unified response format
                'layout' => null,
                'error' => 'Step 2 (Wireframe) failed: ' . ($wireframeResult['error'] ?? 'unknown'),
                'stats' => ['time_ms' => self::elapsed($startTime), 'steps' => $stats['steps']]
            ];
        }

        $wireframe = $wireframeResult['wireframe'];
        $stats['steps']['wireframe'] = [
            'time_ms' => self::elapsed($step2Start),
            'sections_generated' => count($wireframe['sections'] ?? [])
        ];
        self::log("Wireframe done: " . count($wireframe['sections'] ?? []) . " sections");

        // ==========================================
        // STEP 3: STYLE (Apply Design Tokens)
        // ==========================================
        $step3Start = microtime(true);
        self::log("=== STEP 3: STYLE ===");

        $styledLayout = self::step3_applyStyles($wireframe, $outline);

        $stats['steps']['style'] = [
            'time_ms' => self::elapsed($step3Start),
            'colors_applied' => true
        ];
        self::log("Styles applied");

        // ==========================================
        // STEP 4: CONTENT (Generate Text)
        // ==========================================
        $step4Start = microtime(true);
        self::log("=== STEP 4: CONTENT ===");

        // ALWAYS apply placeholder content first (guarantees no empty fields)
        $withContent = self::step4_applyPlaceholderContent($styledLayout, $outline);
        self::log("Placeholder content applied");

        // Then try to enhance with AI-generated content
        $contentResult = self::step4_generateContent($withContent, $outline, $prompt, $ai);

        if ($contentResult['ok']) {
            $withContent = $contentResult['layout'];
            self::log("AI content merged successfully");
        } else {
            self::log("AI content failed, using placeholders only");
        }

        $stats['steps']['content'] = [
            'time_ms' => self::elapsed($step4Start),
            'ai_generated' => $contentResult['ok'] ?? false
        ];
        self::log("Content done");

        // ==========================================
        // STEP 5: IMAGES (Pexels Enrichment)
        // ==========================================
        $step5Start = microtime(true);
        self::log("=== STEP 5: IMAGES ===");

        $finalLayout = self::step5_enrichWithImages($withContent, $outline, $prompt, $options);

        $stats['steps']['images'] = [
            'time_ms' => self::elapsed($step5Start)
        ];
        self::log("Images done");

        // ==========================================
        // FINAL: Validate, Normalize and return
        // ==========================================
        $finalLayout = self::validateAndFix($finalLayout);

        // CRITICAL: Normalize all attributes to match JTB expectations
        // This fixes: name mismatches, type conversions, array formats
        $finalLayout = JTB_AI_Normalizer::normalizeLayout($finalLayout);
        self::log("Layout normalized for JTB compatibility");

        // DEBUG: Log structure of first section
        if (!empty($finalLayout['sections'][0])) {
            $firstSection = $finalLayout['sections'][0];
            self::log("DEBUG First Section: type={$firstSection['type']}, section_type=" . ($firstSection['section_type'] ?? 'N/A'));
            self::log("DEBUG First Section attrs keys: " . json_encode(array_keys($firstSection['attrs'] ?? [])));
            if (!empty($firstSection['children'][0])) {
                $firstRow = $firstSection['children'][0];
                $rowColumns = $firstRow['attrs']['columns'] ?? $firstRow['columns'] ?? 'N/A';
                self::log("DEBUG First Row: type={$firstRow['type']}, columns={$rowColumns}");
                self::log("DEBUG First Row children count: " . count($firstRow['children'] ?? []));
                self::log("DEBUG First Row attrs keys: " . json_encode(array_keys($firstRow['attrs'] ?? [])));
            }
        }

        $stats['time_ms'] = self::elapsed($startTime);
        $stats['provider'] = $ai->getProvider();
        $stats['sections_count'] = count($finalLayout['sections'] ?? []);

        self::log("=== COMPLETE === Total time: {$stats['time_ms']}ms");

        // FIX 2026-02-04: Return both 'ok' and 'success' for backward compatibility
        return [
            'ok' => true,
            'success' => true, // Standard API format
            'layout' => $finalLayout,
            'error' => null,
            'stats' => $stats
        ];
    }

    // ========================================
    // STEP 1: OUTLINE
    // ========================================

    /**
     * Step 1: Generate creative brief / outline
     * Determines: sections, color scheme, brand voice, content direction
     */
    private static function step1_generateOutline(string $prompt, array $options, JTB_AI_Core $ai): array
    {
        // Detect industry from prompt text
        $detectedIndustry = self::detectIndustry($prompt);
        $industry = ($detectedIndustry !== 'business') ? $detectedIndustry : ($options['industry'] ?? 'business');
        $style = $options['style'] ?? 'modern';
        $pageType = $options['page_type'] ?? 'landing';

        // Get color hints for this industry
        $colorHints = self::getColorHintsForIndustry($industry, $style);

        // Use JTB_AI_Knowledge for comprehensive system prompt with all module documentation
        $systemPrompt = JTB_AI_Knowledge::getSystemPrompt([
            'industry' => $industry,
            'style' => $style,
            'page_type' => $pageType,
            'color_hints' => $colorHints
        ]);

        $userPrompt = <<<USER
Create a creative brief for this business:

BUSINESS DESCRIPTION:
{$prompt}

REQUIREMENTS:
- Industry: {$industry}
- Visual style: {$style}
- Page type: {$pageType}

IMPORTANT INSTRUCTIONS:
1. Follow the PAGE TYPE guidelines above - choose sections that match the "{$pageType}" page type
2. Create a UNIQUE color scheme that fits both the industry ({$industry}) and style ({$style})
3. Make the page structure SPECIFIC to this business - not generic
4. Use real-sounding headlines based on the business description
5. Choose column layouts that vary - NOT all sections should be 3-column grids

Generate the JSON creative brief now. Output ONLY valid JSON, no explanation.
USER;

        $response = $ai->queryWithRetry($userPrompt, 2, [
            'system_prompt' => $systemPrompt,
            'max_tokens' => self::MAX_TOKENS_OUTLINE,
            'temperature' => self::TEMPERATURE_CREATIVE,
            'json_mode' => true
        ]);

        self::log("Outline response OK: " . ($response['ok'] ? 'YES' : 'NO'));

        if (!$response['ok']) {
            return ['ok' => false, 'error' => $response['error'] ?? 'AI query failed'];
        }

        // DEBUG: Log raw AI response (first 500 chars)
        self::log("DEBUG Raw AI response: " . substr($response['text'] ?? '', 0, 500));

        $outline = self::parseJsonResponse($response['text']);

        // DEBUG: Log color scheme and structure from AI
        if ($outline !== null) {
            if (isset($outline['color_scheme'])) {
                self::log("DEBUG AI Colors: primary=" . ($outline['color_scheme']['primary'] ?? 'N/A') .
                         ", dark=" . ($outline['color_scheme']['dark'] ?? 'N/A') .
                         ", light_bg=" . ($outline['color_scheme']['light_bg'] ?? 'N/A'));
            }
            // Log if AI provided column structure
            foreach ($outline['sections'] ?? [] as $i => $sec) {
                $hasColumns = isset($sec['columns']) && is_array($sec['columns']);
                $type = $sec['type'] ?? 'unknown';
                self::log("DEBUG Section {$i}: type={$type}, has_columns=" . ($hasColumns ? 'YES' : 'NO'));
            }
        }

        if ($outline === null || empty($outline['sections'])) {
            self::log("Outline parse failed, using default");
            $outline = self::getDefaultOutline($prompt, $options);
        }

        return ['ok' => true, 'outline' => $outline];
    }

    // ========================================
    // STEP 2: WIREFRAME (DETERMINISTIC - No AI)
    // ========================================

    /**
     * Step 2: Generate wireframe structure
     * DETERMINISTIC - converts outline sections to JTB structure
     * No AI call - just mapping section types to predefined structures
     * This ensures consistent, predictable module placement
     */
    private static function step2_generateWireframe(array $outline, JTB_AI_Core $ai): array
    {
        // Wireframe is DETERMINISTIC - no AI needed
        // Each section type maps to a predefined structure
        $wireframe = self::getDefaultWireframe($outline);

        self::log("Wireframe generated (deterministic): " . count($wireframe['sections']) . " sections");

        return ['ok' => true, 'wireframe' => $wireframe];
    }

    /**
     * Generate wireframe from outline
     * Uses AI-specified column structure if available, falls back to defaults
     */
    private static function getDefaultWireframe(array $outline): array
    {
        $sections = [];
        $idCounter = 1;

        foreach ($outline['sections'] ?? [] as $sectionPlan) {
            $sectionType = self::normalizeSectionType($sectionPlan['type'] ?? 'content');
            $layout = $sectionPlan['layout'] ?? 'centered';
            $aiColumns = $sectionPlan['columns'] ?? null;

            $section = [
                'type' => 'section',
                'id' => 'section_' . $idCounter++,
                'section_type' => $sectionType,
                'children' => []
            ];

            // Check if AI provided column structure
            if ($aiColumns && is_array($aiColumns) && count($aiColumns) > 0) {
                // Use AI-specified structure
                $contentRow = self::generateRowFromAIColumns($aiColumns, $sectionType, $idCounter);
                $idCounter += 50; // Reserve more IDs for complex structures
                $section['children'][] = $contentRow;
            } else {
                // Fallback to default structure

                // Add header row for sections that need it (NOT hero, cta, about)
                if (!in_array($sectionType, ['hero', 'cta', 'about'])) {
                    $headerRow = [
                        'type' => 'row',
                        'id' => 'row_' . $idCounter++,
                        'columns' => '1',
                        'children' => [
                            [
                                'type' => 'column',
                                'id' => 'col_' . $idCounter++,
                                'children' => [
                                    ['type' => 'heading', 'id' => 'el_' . $idCounter++, 'role' => 'section_title']
                                ]
                            ]
                        ]
                    ];
                    $section['children'][] = $headerRow;
                }

                // Generate content row based on section type and layout
                $contentRow = self::generateRowForSectionType($sectionType, $layout, $idCounter);
                $idCounter += 20; // Reserve IDs
                $section['children'][] = $contentRow;
            }

            $sections[] = $section;
        }

        return ['sections' => $sections];
    }

    /**
     * Generate row from AI-specified column structure
     */
    private static function generateRowFromAIColumns(array $aiColumns, string $sectionType, int &$idCounter): array
    {
        $columns = [];
        $columnWidths = [];

        foreach ($aiColumns as $colSpec) {
            $width = $colSpec['width'] ?? '1';
            $modules = $colSpec['modules'] ?? ['text'];

            // Convert width to column format
            $columnWidths[] = self::normalizeColumnWidth($width);

            // Create column with specified modules
            $colChildren = [];
            foreach ($modules as $moduleType) {
                $colChildren[] = [
                    'type' => $moduleType,
                    'id' => 'el_' . $idCounter++
                ];
            }

            $columns[] = [
                'type' => 'column',
                'id' => 'col_' . $idCounter++,
                'children' => $colChildren
            ];
        }

        return [
            'type' => 'row',
            'id' => 'row_' . $idCounter++,
            'columns' => implode(',', $columnWidths),
            'children' => $columns
        ];
    }

    /**
     * Normalize column width to JTB format
     */
    private static function normalizeColumnWidth(string $width): string
    {
        $widthMap = [
            'full' => '1',
            '1' => '1',
            '1_2' => '1_2',
            '1_3' => '1_3',
            '2_3' => '2_3',
            '1_4' => '1_4',
            '3_4' => '3_4',
            '1_5' => '1_5',
            '2_5' => '2_5',
            '3_5' => '3_5',
            '4_5' => '4_5',
            '1_6' => '1_6',
            '5_6' => '5_6'
        ];

        return $widthMap[$width] ?? '1';
    }

    /**
     * Normalize section type from AI (handles variations like "social proof" -> "stats")
     */
    private static function normalizeSectionType(string $type): string
    {
        $type = strtolower(trim($type));

        $typeMap = [
            'social proof' => 'stats',
            'social-proof' => 'stats',
            'socialproof' => 'stats',
            'trust' => 'stats',
            'numbers' => 'stats',
            'metrics' => 'stats',
            'benefits' => 'features',
            'services' => 'features',
            'offerings' => 'features',
            'what we do' => 'features',
            'reviews' => 'testimonials',
            'quotes' => 'testimonials',
            'clients' => 'testimonials',
            'call to action' => 'cta',
            'call-to-action' => 'cta',
            'final cta' => 'cta',
            'questions' => 'faq',
            'faqs' => 'faq',
            'our team' => 'team',
            'meet the team' => 'team',
            'about us' => 'about',
            'who we are' => 'about',
            'contact us' => 'contact',
            'get in touch' => 'contact',
            'plans' => 'pricing',
            'packages' => 'pricing',
            'how it works' => 'process',
            'our process' => 'process',
            'steps' => 'process',
            'portfolio' => 'gallery',
            'work' => 'gallery',
            'projects' => 'gallery'
        ];

        return $typeMap[$type] ?? $type;
    }

    /**
     * Generate appropriate row structure for section type
     */
    private static function generateRowForSectionType(string $sectionType, string $layout, int &$idCounter): array
    {
        $columnsAttr = self::layoutToColumns($layout);

        switch ($sectionType) {
            case 'hero':
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => $columnsAttr,
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'heading', 'id' => 'el_' . $idCounter++],
                                ['type' => 'text', 'id' => 'el_' . $idCounter++],
                                ['type' => 'button', 'id' => 'el_' . $idCounter++]
                            ]
                        ],
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'image', 'id' => 'el_' . $idCounter++]
                            ]
                        ]
                    ]
                ];

            case 'features':
            case 'benefits':
                $cols = [];
                $colCount = ($layout === 'grid-4') ? 4 : 3;
                for ($i = 0; $i < $colCount; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'blurb', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => ($colCount === 4) ? '1_4,1_4,1_4,1_4' : '1_3,1_3,1_3',
                    'children' => $cols
                ];

            case 'stats':
                $cols = [];
                for ($i = 0; $i < 4; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'number_counter', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_4,1_4,1_4,1_4',
                    'children' => $cols
                ];

            case 'testimonials':
                $cols = [];
                for ($i = 0; $i < 3; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'testimonial', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_3,1_3,1_3',
                    'children' => $cols
                ];

            case 'cta':
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1',
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'heading', 'id' => 'el_' . $idCounter++],
                                ['type' => 'text', 'id' => 'el_' . $idCounter++],
                                ['type' => 'button', 'id' => 'el_' . $idCounter++]
                            ]
                        ]
                    ]
                ];

            case 'pricing':
                $cols = [];
                for ($i = 0; $i < 3; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'pricing_table', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_3,1_3,1_3',
                    'children' => $cols
                ];

            case 'faq':
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1',
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'accordion', 'id' => 'el_' . $idCounter++]
                            ]
                        ]
                    ]
                ];

            case 'team':
                $cols = [];
                for ($i = 0; $i < 4; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'team_member', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_4,1_4,1_4,1_4',
                    'children' => $cols
                ];

            case 'about':
                // Split layout: image + text
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '2_5,3_5',
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'image', 'id' => 'el_' . $idCounter++]
                            ]
                        ],
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'heading', 'id' => 'el_' . $idCounter++],
                                ['type' => 'text', 'id' => 'el_' . $idCounter++],
                                ['type' => 'button', 'id' => 'el_' . $idCounter++]
                            ]
                        ]
                    ]
                ];

            case 'gallery':
            case 'portfolio':
                // Grid of images
                $cols = [];
                for ($i = 0; $i < 3; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'image', 'id' => 'el_' . $idCounter++]
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_3,1_3,1_3',
                    'children' => $cols
                ];

            case 'process':
                // Numbered steps as blurbs
                $cols = [];
                for ($i = 0; $i < 4; $i++) {
                    $cols[] = [
                        'type' => 'column',
                        'id' => 'col_' . $idCounter++,
                        'children' => [
                            ['type' => 'blurb', 'id' => 'el_' . $idCounter++, 'role' => 'step']
                        ]
                    ];
                }
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1_4,1_4,1_4,1_4',
                    'children' => $cols
                ];

            default:
                // Generic content section: just text
                return [
                    'type' => 'row',
                    'id' => 'row_' . $idCounter++,
                    'columns' => '1',
                    'children' => [
                        [
                            'type' => 'column',
                            'id' => 'col_' . $idCounter++,
                            'children' => [
                                ['type' => 'text', 'id' => 'el_' . $idCounter++]
                            ]
                        ]
                    ]
                ];
        }
    }

    /**
     * Convert layout name to columns attribute
     */
    private static function layoutToColumns(string $layout): string
    {
        return match ($layout) {
            'centered' => '1',
            'split-left' => '3_5,2_5',
            'split-right' => '2_5,3_5',
            'grid-2' => '1_2,1_2',
            'grid-3' => '1_3,1_3,1_3',
            'grid-4' => '1_4,1_4,1_4,1_4',
            'asymmetric' => '2_3,1_3',
            default => '1'
        };
    }

    // ========================================
    // STEP 3: STYLE (Deterministic)
    // ========================================

    /**
     * Step 3: Apply styles to wireframe
     * This is DETERMINISTIC - no AI call needed
     * ALL values come from AI-generated outline - NO HARDCODING!
     */
    private static function step3_applyStyles(array $wireframe, array $outline): array
    {
        // ===========================================
        // ALL TOKENS FROM AI OUTLINE - NO HARDCODING!
        // ===========================================

        // Colors from AI
        $colors = $outline['color_scheme'] ?? [];
        $colorsTokens = [
            'primary' => $colors['primary'] ?? '#2563eb',
            'secondary' => $colors['secondary'] ?? '#3b82f6',
            'dark' => $colors['dark'] ?? '#1e3a8a',
            'light_bg' => $colors['light_bg'] ?? '#f8fafc',
            'text' => $colors['text'] ?? '#111827',
            'text_light' => $colors['text_light'] ?? '#6b7280',
            'white' => '#ffffff',
            // Derived colors (standard gray scale)
            'gray_50' => '#f9fafb',
            'gray_100' => '#f3f4f6',
            'gray_200' => '#e5e7eb',
            'gray_300' => '#d1d5db',
            'gray_400' => '#9ca3af',
            'border' => '#e5e7eb',
            'input_border' => '#d1d5db',
            'placeholder' => '#9ca3af',
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#d97706',
            'code_bg' => '#1e293b',
            'code_text' => '#e2e8f0'
        ];

        // Typography from AI
        $typo = $outline['typography'] ?? [];
        $typographyTokens = [
            'h1' => [
                'size' => (int)($typo['h1_size'] ?? 56),
                'weight' => (string)($typo['h1_weight'] ?? '700'),
                'line_height' => (string)($typo['h1_line_height'] ?? '1.1')
            ],
            'h2' => [
                'size' => (int)($typo['h2_size'] ?? 42),
                'weight' => (string)($typo['h2_weight'] ?? '700'),
                'line_height' => (string)($typo['h2_line_height'] ?? '1.2')
            ],
            'h3' => [
                'size' => (int)($typo['h3_size'] ?? 32),
                'size_tablet' => (int)($typo['h3_size_tablet'] ?? 28),
                'size_phone' => (int)($typo['h3_size_phone'] ?? 24),
                'weight' => (string)($typo['h3_weight'] ?? '700'),
                'line_height' => (string)($typo['h3_line_height'] ?? '1.3')
            ],
            'body' => [
                'size' => (int)($typo['body_size'] ?? 18),
                'weight' => (string)($typo['body_weight'] ?? '400'),
                'line_height' => (string)($typo['body_line_height'] ?? '1.7')
            ],
            'small' => [
                'size' => (int)(($typo['body_size'] ?? 18) - 2),
                'weight' => (string)($typo['small_weight'] ?? '400'),
                'line_height' => (string)($typo['small_line_height'] ?? '1.6')
            ],
            'button' => [
                'size' => (int)($typo['button_size'] ?? 16),
                'size_tablet' => (int)($typo['button_size_tablet'] ?? 15),
                'size_phone' => (int)($typo['button_size_phone'] ?? 14),
                'weight' => (string)($typo['button_weight'] ?? '600'),
                'letter_spacing' => (string)($typo['button_letter_spacing'] ?? '0.025em')
            ],
            'code' => [
                'size' => (int)($typo['code_size'] ?? 14),
                'family' => 'monospace'
            ],
            'icon' => [
                'size' => (int)($typo['icon_size'] ?? 20),
                'size_large' => (int)($typo['icon_size_large'] ?? 24),
                'size_xl' => (int)($typo['icon_size_xl'] ?? 48),
                'size_xl_tablet' => (int)($typo['icon_size_xl_tablet'] ?? 40),
                'size_xl_phone' => (int)($typo['icon_size_xl_phone'] ?? 36)
            ],
            'h4' => [
                'size' => (int)($typo['h4_size'] ?? 20),
                'size_tablet' => (int)($typo['h4_size_tablet'] ?? 18),
                'size_phone' => (int)($typo['h4_size_phone'] ?? 17),
                'weight' => (string)($typo['h4_weight'] ?? '600'),
                'line_height' => (string)($typo['h4_line_height'] ?? '1.4')
            ],
            'content' => [
                'size' => (int)($typo['content_size'] ?? 16),
                'size_tablet' => (int)($typo['content_size_tablet'] ?? 15),
                'size_phone' => (int)($typo['content_size_phone'] ?? 14),
                'line_height' => (string)($typo['content_line_height'] ?? '1.6')
            ],
            'counter' => [
                'size' => (int)($typo['counter_size'] ?? 48),
                'size_tablet' => (int)($typo['counter_size_tablet'] ?? 40),
                'size_phone' => (int)($typo['counter_size_phone'] ?? 32),
                'weight' => (string)($typo['counter_weight'] ?? '700'),
                'line_height' => (string)($typo['counter_line_height'] ?? '1.2')
            ],
            'name' => [
                'size' => (int)($typo['name_size'] ?? 18),
                'size_tablet' => (int)($typo['name_size_tablet'] ?? 17),
                'size_phone' => (int)($typo['name_size_phone'] ?? 16),
                'weight' => (string)($typo['name_weight'] ?? '600'),
                'line_height' => (string)($typo['name_line_height'] ?? '1.4')
            ],
            'subtitle' => [
                'size' => (int)($typo['subtitle_size'] ?? 14),
                'size_tablet' => (int)($typo['subtitle_size_tablet'] ?? 13),
                'weight' => (string)($typo['subtitle_weight'] ?? '400')
            ],
            // Font weight aliases (for consistency across modules)
            'weight_light' => (string)($typo['weight_light'] ?? '300'),
            'weight_normal' => (string)($typo['weight_normal'] ?? '400'),
            'weight_medium' => (string)($typo['weight_medium'] ?? '500'),
            'weight_semibold' => (string)($typo['weight_semibold'] ?? '600'),
            'weight_bold' => (string)($typo['weight_bold'] ?? '700'),
            'weight_extrabold' => (string)($typo['weight_extrabold'] ?? '800'),
            // Content weight (for card content, form labels, etc.)
            'content_weight' => (string)($typo['content_weight'] ?? '400'),
            'label_weight' => (string)($typo['label_weight'] ?? '500'),
            'title_weight' => (string)($typo['title_weight'] ?? '600'),
            // Animation/transition durations
            'transition_fast' => (string)($typo['transition_fast'] ?? '200ms'),
            'transition_normal' => (string)($typo['transition_normal'] ?? '300ms'),
            'transition_slow' => (string)($typo['transition_slow'] ?? '500ms'),
            'animation_duration' => (int)($typo['animation_duration'] ?? 2000),
            'animation_duration_short' => (int)($typo['animation_duration_short'] ?? 1500),
            // Slider speeds
            'slider_autoplay' => (int)($typo['slider_autoplay'] ?? 5000),
            'slider_transition' => (int)($typo['slider_transition'] ?? 500)
        ];

        // Spacing from AI
        $spacing = $outline['spacing'] ?? [];
        $sectionPad = (int)($spacing['section_padding'] ?? 100);
        $heroPad = (int)($spacing['hero_padding'] ?? 120);
        $elementGap = (int)($spacing['element_gap'] ?? 24);
        $cardPad = (int)($spacing['card_padding'] ?? 32);

        // Button padding from AI
        $btnPadV = (int)($spacing['button_padding_v'] ?? 16);
        $btnPadH = (int)($spacing['button_padding_h'] ?? 32);
        $btnPadVTablet = (int)($spacing['button_padding_v_tablet'] ?? 14);
        $btnPadHTablet = (int)($spacing['button_padding_h_tablet'] ?? 28);
        $btnPadVPhone = (int)($spacing['button_padding_v_phone'] ?? 12);
        $btnPadHPhone = (int)($spacing['button_padding_h_phone'] ?? 24);

        // Derive more spacing values from base
        $marginSm = (int)($elementGap * 0.5);  // ~12
        $marginMd = $elementGap;               // ~24
        $marginLg = (int)($elementGap * 1.33); // ~32
        $marginXl = (int)($elementGap * 2);    // ~48

        $spacingTokens = [
            'section_padding' => ['top' => $sectionPad, 'right' => 0, 'bottom' => $sectionPad, 'left' => 0],
            'hero_padding' => ['top' => $heroPad, 'right' => 0, 'bottom' => $heroPad, 'left' => 0],
            'element_margin' => ['top' => 0, 'right' => 0, 'bottom' => $elementGap, 'left' => 0],
            'element_gap' => ['bottom' => $elementGap],
            'card_padding' => ['top' => $cardPad, 'right' => (int)($cardPad * 0.75), 'bottom' => $cardPad, 'left' => (int)($cardPad * 0.75)],
            'button_padding' => ['top' => $btnPadV, 'right' => $btnPadH, 'bottom' => $btnPadV, 'left' => $btnPadH],
            'button_padding_tablet' => ['top' => $btnPadVTablet, 'right' => $btnPadHTablet, 'bottom' => $btnPadVTablet, 'left' => $btnPadHTablet],
            'button_padding_phone' => ['top' => $btnPadVPhone, 'right' => $btnPadHPhone, 'bottom' => $btnPadVPhone, 'left' => $btnPadHPhone],
            // Derived margins (from element_gap)
            'margin_sm' => $marginSm,
            'margin_md' => $marginMd,
            'margin_lg' => $marginLg,
            'margin_xl' => $marginXl,
            // Margin presets
            'margin_bottom_sm' => ['top' => 0, 'right' => 0, 'bottom' => $marginSm, 'left' => 0],
            'margin_bottom_md' => ['top' => 0, 'right' => 0, 'bottom' => $marginMd, 'left' => 0],
            'margin_bottom_lg' => ['top' => 0, 'right' => 0, 'bottom' => $marginLg, 'left' => 0],
            'margin_bottom_xl' => ['top' => 0, 'right' => 0, 'bottom' => $marginXl, 'left' => 0],
            // Responsive card padding
            'card_padding_tablet' => ['top' => (int)($cardPad * 0.875), 'right' => (int)($cardPad * 0.625), 'bottom' => (int)($cardPad * 0.875), 'left' => (int)($cardPad * 0.625)],
            'card_padding_phone' => ['top' => (int)($cardPad * 0.75), 'right' => (int)($cardPad * 0.5), 'bottom' => (int)($cardPad * 0.75), 'left' => (int)($cardPad * 0.5)],
            // Compact padding (for headers, items)
            'compact_padding' => ['top' => (int)($cardPad * 0.5), 'right' => (int)($cardPad * 0.75), 'bottom' => (int)($cardPad * 0.5), 'left' => (int)($cardPad * 0.75)],
            'compact_padding_tablet' => ['top' => (int)($cardPad * 0.44), 'right' => (int)($cardPad * 0.625), 'bottom' => (int)($cardPad * 0.44), 'left' => (int)($cardPad * 0.625)],
            'compact_padding_phone' => ['top' => (int)($cardPad * 0.375), 'right' => (int)($cardPad * 0.5), 'bottom' => (int)($cardPad * 0.375), 'left' => (int)($cardPad * 0.5)],
            // CTA large padding (1.875x card_padding)
            'cta_padding' => ['top' => (int)($cardPad * 1.875), 'right' => (int)($cardPad * 1.25), 'bottom' => (int)($cardPad * 1.875), 'left' => (int)($cardPad * 1.25)],
            'cta_padding_tablet' => ['top' => (int)($cardPad * 1.5), 'right' => $cardPad, 'bottom' => (int)($cardPad * 1.5), 'left' => $cardPad],
            'cta_padding_phone' => ['top' => (int)($cardPad * 1.25), 'right' => (int)($cardPad * 0.75), 'bottom' => (int)($cardPad * 1.25), 'left' => (int)($cardPad * 0.75)],
            // Container/row horizontal padding (from element_gap)
            'container_padding' => (int)($elementGap * 0.83),
            'container_padding_tablet' => (int)($elementGap * 0.66),
            'container_padding_phone' => (int)($elementGap * 0.5),
            // Max width (from section_padding * 12.8)
            'max_width' => (int)($sectionPad * 12.8)
        ];

        // Borders from AI
        $borders = $outline['borders'] ?? [];
        $radiusSmall = (int)($borders['radius_small'] ?? 8);
        $radiusMedium = (int)($borders['radius_medium'] ?? 12);
        $radiusLarge = (int)($borders['radius_large'] ?? 16);

        // Border width from AI (default 1px for inputs/dividers)
        $borderWidth = (int)($borders['width'] ?? 1);

        $bordersTokens = [
            'radius_small' => ['top_left' => $radiusSmall, 'top_right' => $radiusSmall, 'bottom_right' => $radiusSmall, 'bottom_left' => $radiusSmall],
            'radius_medium' => ['top_left' => $radiusMedium, 'top_right' => $radiusMedium, 'bottom_right' => $radiusMedium, 'bottom_left' => $radiusMedium],
            'radius_large' => ['top_left' => $radiusLarge, 'top_right' => $radiusLarge, 'bottom_right' => $radiusLarge, 'bottom_left' => $radiusLarge],
            // Border width (for inputs, dividers, cards)
            'width' => $borderWidth,
            'width_zero' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'width_all' => ['top' => $borderWidth, 'right' => $borderWidth, 'bottom' => $borderWidth, 'left' => $borderWidth],
            'width_bottom' => ['top' => 0, 'right' => 0, 'bottom' => $borderWidth, 'left' => 0]
        ];

        // Shadows from AI
        $shadows = $outline['shadows'] ?? [];
        $primaryColor = $colorsTokens['primary'] ?? '#3b82f6';
        $shadowsTokens = [
            'card' => $shadows['card'] ?? '0 4px 20px rgba(0,0,0,0.08)',
            'elevated' => $shadows['elevated'] ?? '0 10px 40px rgba(0,0,0,0.12)',
            'hero_image' => $shadows['hover'] ?? '0 20px 40px rgba(0,0,0,0.15)',
            'button' => $shadows['button'] ?? '0 4px 14px rgba(0,0,0,0.1)',
            'button_hover' => $shadows['button_hover'] ?? '0 6px 20px rgba(0,0,0,0.15)',
            'input_focus' => $shadows['input_focus'] ?? ('0 0 0 3px ' . $primaryColor . '20'),
            'none' => 'none'
        ];

        // Combined tokens - ALL from AI outline
        $tokens = [
            'colors' => $colorsTokens,
            'typography' => $typographyTokens,
            'spacing' => $spacingTokens,
            'borders' => $bordersTokens,
            'shadows' => $shadowsTokens
        ];

        self::log("Tokens from AI: h1_size={$typographyTokens['h1']['size']}, section_pad={$sectionPad}, radius={$radiusMedium}");

        // Get section backgrounds from outline
        $sectionPlans = $outline['sections'] ?? [];

        // Apply styles to each section
        $styledSections = [];
        $sectionIndex = 0;

        foreach ($wireframe['sections'] ?? [] as $section) {
            $sectionType = $section['section_type'] ?? 'content';
            $isLast = ($sectionIndex === count($wireframe['sections']) - 1);

            // Find matching section plan to get background
            $sectionPlan = self::findSectionPlanByIndex($sectionPlans, $sectionIndex);
            $background = $sectionPlan['background'] ?? null;

            // Apply section-level styles with background from AI
            $section['attrs'] = self::getSectionStyles($sectionType, $tokens, $isLast, $background);

            // Apply styles to children recursively
            $section['children'] = self::applyStylesToChildren(
                $section['children'] ?? [],
                $sectionType,
                $tokens
            );

            $styledSections[] = $section;
            $sectionIndex++;
        }

        return ['sections' => $styledSections];
    }

    /**
     * Find section plan by index
     */
    private static function findSectionPlanByIndex(array $plans, int $index): ?array
    {
        return $plans[$index] ?? null;
    }

    /**
     * Get styles for a section based on its type
     * ALL values from tokens (from AI) - NO HARDCODING!
     * Background comes from AI outline section plan
     */
    private static function getSectionStyles(string $sectionType, array $tokens, bool $isLast, ?string $background = null): array
    {
        $colors = $tokens['colors'];
        $spacing = $tokens['spacing'];

        // Resolve background color from AI-specified background type
        $bgColor = self::resolveBackgroundColor($background, $colors, $sectionType, $isLast);

        // Calculate responsive padding from AI tokens (reduce by ~20% for tablet, ~40% for phone)
        $sectionPad = $spacing['section_padding']['top'] ?? 100;
        $heroPad = $spacing['hero_padding']['top'] ?? 120;

        $tabletPad = (int)($sectionPad * 0.8);
        $phonePad = (int)($sectionPad * 0.6);
        $heroTabletPad = (int)($heroPad * 0.67);
        $heroPhonePad = (int)($heroPad * 0.5);

        // Base section styles (common to all)
        $base = [
            'background_type' => 'color',
            'background_color' => $bgColor,
            'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'border_style' => 'solid',
            'border_color' => 'transparent',
            'border_radius' => ['top_left' => 0, 'top_right' => 0, 'bottom_right' => 0, 'bottom_left' => 0],
            'min_height' => 'auto'
        ];

        $containerPad = $spacing['container_padding'] ?? 20;
        $containerPadTablet = $spacing['container_padding_tablet'] ?? 16;
        $containerPadPhone = $spacing['container_padding_phone'] ?? 12;

        // Hero gets more padding
        if ($sectionType === 'hero') {
            return array_merge($base, [
                'padding' => $spacing['hero_padding'],
                'padding__tablet' => ['top' => $heroTabletPad, 'right' => $containerPadTablet, 'bottom' => $heroTabletPad, 'left' => $containerPadTablet],
                'padding__phone' => ['top' => $heroPhonePad, 'right' => $containerPadPhone, 'bottom' => $heroPhonePad, 'left' => $containerPadPhone]
            ]);
        }

        // Stats - slightly less padding
        if ($sectionType === 'stats') {
            $statsPad = (int)($sectionPad * 0.8);
            return array_merge($base, [
                'padding' => ['top' => $statsPad, 'right' => 0, 'bottom' => $statsPad, 'left' => 0],
                'padding__tablet' => ['top' => (int)($statsPad * 0.75), 'right' => $containerPadTablet, 'bottom' => (int)($statsPad * 0.75), 'left' => $containerPadTablet],
                'padding__phone' => ['top' => (int)($statsPad * 0.6), 'right' => $containerPadPhone, 'bottom' => (int)($statsPad * 0.6), 'left' => $containerPadPhone]
            ]);
        }

        // Default - use section_padding from AI
        return array_merge($base, [
            'padding' => $spacing['section_padding'],
            'padding__tablet' => ['top' => $tabletPad, 'right' => $containerPadTablet, 'bottom' => $tabletPad, 'left' => $containerPadTablet],
            'padding__phone' => ['top' => $phonePad, 'right' => $containerPadPhone, 'bottom' => $phonePad, 'left' => $containerPadPhone]
        ]);
    }

    /**
     * Resolve background color from AI specification
     * background can be: "white", "light", "dark", "primary" or null
     * ALL colors from AI tokens - NO HARDCODING!
     */
    private static function resolveBackgroundColor(?string $background, array $colors, string $sectionType, bool $isLast): string
    {
        // If AI specified a background, use it
        if ($background !== null) {
            return match ($background) {
                'dark' => $colors['dark'],
                'primary' => $colors['primary'],
                'light' => $colors['light_bg'],
                'white' => $colors['white'],
                default => $colors['white']
            };
        }

        // Fallback logic based on section type (only if AI didn't specify)
        // CTA and last section default to dark
        if ($sectionType === 'cta' || $isLast) {
            return $colors['dark'];
        }

        // Hero defaults to light background
        if ($sectionType === 'hero') {
            return $colors['light_bg'];
        }

        // Testimonials, stats, faq - light background for contrast
        if (in_array($sectionType, ['testimonials', 'stats', 'faq'])) {
            return $colors['light_bg'];
        }

        // Default white
        return $colors['white'];
    }

    /**
     * Recursively apply styles to children elements
     */
    private static function applyStylesToChildren(array $children, string $sectionType, array $tokens): array
    {
        $styled = [];

        foreach ($children as $child) {
            $type = $child['type'] ?? '';

            if ($type === 'row') {
                // Apply row styles
                $rowStyles = self::getRowStyles($sectionType, $tokens);

                // CRITICAL: Move 'columns' from row level to attrs (builder expects row.attrs.columns)
                if (isset($child['columns'])) {
                    $rowStyles['columns'] = $child['columns'];
                    unset($child['columns']);
                }

                $child['attrs'] = array_merge($child['attrs'] ?? [], $rowStyles);
                $child['children'] = self::applyStylesToChildren($child['children'] ?? [], $sectionType, $tokens);
            } elseif ($type === 'column') {
                // Apply column styles
                $child['attrs'] = array_merge($child['attrs'] ?? [], self::getColumnStyles($sectionType, $tokens));
                $child['children'] = self::applyStylesToChildren($child['children'] ?? [], $sectionType, $tokens);
            } else {
                // Module - MERGE type-specific styles with existing attrs (preserve content!)
                $existingAttrs = $child['attrs'] ?? [];
                $moduleStyles = self::getModuleStyles($type, $sectionType, $tokens);
                // Styles first, then existing attrs (so content from wireframe is preserved)
                $child['attrs'] = array_merge($moduleStyles, $existingAttrs);

                // DEBUG: Log first heading module styles
                static $loggedModule = false;
                if (!$loggedModule && $type === 'heading') {
                    self::log("DEBUG Module heading attrs: " . json_encode(array_keys($child['attrs'])));
                    self::log("DEBUG Module heading font_size: " . ($child['attrs']['font_size'] ?? 'N/A'));
                    self::log("DEBUG Module heading text_color: " . ($child['attrs']['text_color'] ?? 'N/A'));
                    $loggedModule = true;
                }
            }

            $styled[] = $child;
        }

        return $styled;
    }

    /**
     * Get styles for a row based on section type
     * ALL values from AI tokens - NO HARDCODING!
     */
    private static function getRowStyles(string $sectionType, array $tokens): array
    {
        $spacing = $tokens['spacing'];
        $elementGap = $spacing['element_gap']['bottom'] ?? 24;
        $containerPad = $spacing['container_padding'] ?? 20;
        $containerPadTablet = $spacing['container_padding_tablet'] ?? 16;
        $containerPadPhone = $spacing['container_padding_phone'] ?? 12;
        $maxWidth = $spacing['max_width'] ?? 1280;

        $base = [
            'column_gap' => $elementGap,
            'row_gap' => $elementGap,
            'equal_heights' => true,
            'vertical_align' => 'center',
            'max_width' => $maxWidth . 'px',
            'padding' => ['top' => 0, 'right' => $containerPad, 'bottom' => 0, 'left' => $containerPad],
            'padding__tablet' => ['top' => 0, 'right' => $containerPadTablet, 'bottom' => 0, 'left' => $containerPadTablet],
            'padding__phone' => ['top' => 0, 'right' => $containerPadPhone, 'bottom' => 0, 'left' => $containerPadPhone],
            'margin' => ['top' => 0, 'right' => 'auto', 'bottom' => 0, 'left' => 'auto']
        ];

        // Features/pricing/team - slightly more gap between cards
        if (in_array($sectionType, ['features', 'pricing', 'team'])) {
            $base['column_gap'] = (int)($elementGap * 1.33);
            $base['row_gap'] = (int)($elementGap * 1.33);
        }

        // Stats - tighter layout
        if ($sectionType === 'stats') {
            $base['column_gap'] = (int)($elementGap * 0.8);
        }

        // Testimonials - generous spacing
        if ($sectionType === 'testimonials') {
            $base['column_gap'] = (int)($elementGap * 1.25);
        }

        return $base;
    }

    /**
     * Get styles for a column based on section type
     * ALL values from AI tokens - NO HARDCODING!
     */
    private static function getColumnStyles(string $sectionType, array $tokens): array
    {
        $colors = $tokens['colors'];
        $spacing = $tokens['spacing'];

        $base = [
            'vertical_align' => 'center',
            'horizontal_align' => 'left',
            'padding' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'background_color' => 'transparent'
        ];

        // Centered sections
        if (in_array($sectionType, ['cta', 'stats', 'pricing', 'faq'])) {
            $base['horizontal_align'] = 'center';
        }

        // Features/benefits - cards start from top
        if (in_array($sectionType, ['features', 'benefits'])) {
            $base['vertical_align'] = 'top';
        }

        return $base;
    }

    /**
     * Get styles for a module based on its type and section context
     */
    private static function getModuleStyles(string $moduleType, string $sectionType, array $tokens): array
    {
        $colors = $tokens['colors'];
        $typography = $tokens['typography'];
        $spacing = $tokens['spacing'];
        $borders = $tokens['borders'];
        $shadows = $tokens['shadows'];

        // Determine if we're in a dark section
        $isDarkSection = in_array($sectionType, ['cta']);

        // Is this a section title heading?
        $isSectionTitle = ($sectionType !== 'hero' && $moduleType === 'heading');

        switch ($moduleType) {
            case 'heading':
                $level = ($sectionType === 'hero') ? 'h1' : 'h2';
                $typo = $typography[$level];
                $centered = in_array($sectionType, ['cta', 'stats', 'features', 'testimonials', 'pricing', 'faq', 'team']);
                $textColor = $isDarkSection ? $colors['white'] : $colors['text'];
                return [
                    'level' => $level,
                    'font_size' => $typo['size'],
                    'font_size__tablet' => (int)($typo['size'] * 0.85),
                    'font_size__phone' => (int)($typo['size'] * 0.7),
                    'font_weight' => $typo['weight'],
                    'font_family' => 'inherit',
                    'line_height' => $typo['line_height'],
                    'letter_spacing' => '-0.02em',
                    'text_color' => $textColor,
                    'text_align' => $centered ? 'center' : 'left',
                    'text_align__tablet' => 'center',
                    'text_align__phone' => 'center',
                    'margin' => $isSectionTitle
                        ? $spacing['margin_bottom_xl']
                        : $spacing['margin_bottom_md'],
                    'margin__tablet' => $isSectionTitle
                        ? $spacing['margin_bottom_lg']
                        : ['top' => 0, 'right' => 0, 'bottom' => (int)($spacing['margin_md'] * 0.83), 'left' => 0],
                    'margin__phone' => $isSectionTitle
                        ? $spacing['margin_bottom_md']
                        : $spacing['margin_bottom_sm'],
                    // Hover effect - subtle color shift on dark sections
                    'hover_text_color' => $isDarkSection ? $colors['gray_100'] : $colors['primary']
                ];

            case 'text':
                $textColor = $isDarkSection ? $colors['gray_200'] : $colors['text_light'];
                $centered = in_array($sectionType, ['cta', 'hero']);
                return [
                    'font_size' => $typography['body']['size'],
                    'font_size__tablet' => $typography['small']['size'],
                    'font_size__phone' => (int)($typography['small']['size'] - 1),
                    'font_family' => 'inherit',
                    'font_weight' => $typography['body']['weight'],
                    'line_height' => $typography['body']['line_height'],
                    'letter_spacing' => 'normal',
                    'text_color' => $textColor,
                    'text_align' => $centered ? 'center' : 'left',
                    'text_align__tablet' => 'center',
                    'text_align__phone' => 'center',
                    'margin' => $spacing['margin_bottom_lg'],
                    'margin__tablet' => $spacing['margin_bottom_md'],
                    'margin__phone' => ['top' => 0, 'right' => 0, 'bottom' => (int)($spacing['margin_md'] * 0.83), 'left' => 0],
                    // Max width for readability on large screens
                    'max_width' => $centered ? '700px' : '100%'
                ];

            case 'button':
                $bgColor = $isDarkSection ? $colors['white'] : $colors['primary'];
                $textColor = $isDarkSection ? $colors['dark'] : $colors['white'];
                $hoverBg = $isDarkSection ? $colors['gray_100'] : self::adjustColor($colors['primary'], -15);
                $hoverText = $isDarkSection ? $colors['dark'] : $colors['white'];
                return [
                    'background_color' => $bgColor,
                    'text_color' => $textColor,
                    'font_size' => $typography['button']['size'],
                    'font_size__tablet' => $typography['button']['size_tablet'],
                    'font_size__phone' => $typography['button']['size_phone'],
                    'font_weight' => $typography['button']['weight'],
                    'font_family' => 'inherit',
                    'text_transform' => 'none',
                    'letter_spacing' => $typography['button']['letter_spacing'],
                    'padding' => $spacing['button_padding'],
                    'padding__tablet' => $spacing['button_padding_tablet'],
                    'padding__phone' => $spacing['button_padding_phone'],
                    'border_radius' => $borders['radius_small'],
                    'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'border_color' => 'transparent',
                    'border_style' => 'solid',
                    'box_shadow' => $shadows['button'],
                    // Hover states
                    'hover_background_color' => $hoverBg,
                    'hover_text_color' => $hoverText,
                    'hover_box_shadow' => $shadows['button_hover'],
                    'hover_border_color' => 'transparent',
                    // Transition
                    'transition_duration' => $typography['transition_normal'],
                    // Alignment
                    'align' => in_array($sectionType, ['cta']) ? 'center' : 'left',
                    'align__tablet' => 'center',
                    'align__phone' => 'center',
                    // Display
                    'display' => 'inline-block',
                    'margin' => ['top' => (int)($spacing['margin_sm'] * 0.66), 'right' => 0, 'bottom' => 0, 'left' => 0]
                ];

            case 'image':
                $isHero = ($sectionType === 'hero');
                return [
                    'align' => 'center',
                    'width' => 'auto',
                    'max_width' => '100%',
                    'height' => 'auto',
                    'border_radius' => $isHero ? $borders['radius_large'] : $borders['radius_medium'],
                    'box_shadow' => $isHero ? $shadows['hero_image'] : $shadows['card'],
                    // Hover effect
                    'hover_box_shadow' => $shadows['elevated'],
                    'hover_transform' => 'translateY(-4px)',
                    // Transition
                    'transition_duration' => $typography['transition_normal'],
                    // Object fit
                    'object_fit' => 'cover',
                    // Link (empty by default)
                    'link_url' => '',
                    'link_target' => '_self'
                ];

            case 'blurb':
                return [
                    'use_icon' => true,
                    'icon_color' => $colors['primary'],
                    'icon_font_size' => $typography['icon']['size_xl'],
                    'icon_font_size__tablet' => $typography['icon']['size_xl_tablet'],
                    'icon_font_size__phone' => $typography['icon']['size_xl_phone'],
                    'icon_placement' => 'top',
                    'title_font_size' => $typography['h4']['size'],
                    'title_font_size__tablet' => $typography['h4']['size_tablet'],
                    'title_font_size__phone' => $typography['h4']['size_phone'],
                    'title_font_weight' => $typography['h4']['weight'],
                    'title_font_family' => 'inherit',
                    'title_color' => $colors['text'],
                    'title_line_height' => $typography['h4']['line_height'],
                    'title_margin' => ['top' => $spacing['margin_sm'], 'right' => 0, 'bottom' => $spacing['margin_sm'], 'left' => 0],
                    'content_font_size' => $typography['content']['size'],
                    'content_font_size__tablet' => $typography['content']['size_tablet'],
                    'content_font_size__phone' => $typography['content']['size_phone'],
                    'content_color' => $colors['text_light'],
                    'content_line_height' => $typography['content']['line_height'],
                    'text_orientation' => 'center',
                    'padding' => $spacing['card_padding'],
                    'padding__tablet' => $spacing['card_padding_tablet'],
                    'padding__phone' => $spacing['card_padding_phone'],
                    'background_color' => $colors['white'],
                    'border_radius' => $borders['radius_medium'],
                    'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'border_color' => 'transparent',
                    'box_shadow' => $shadows['card'],
                    // Hover effects
                    'hover_box_shadow' => $shadows['elevated'],
                    'hover_background_color' => $colors['white'],
                    'hover_transform' => 'translateY(-4px)',
                    'hover_icon_color' => self::adjustColor($colors['primary'], -10),
                    // Transition
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'number_counter':
                return [
                    'number_font_size' => $typography['counter']['size'],
                    'number_font_size__tablet' => $typography['counter']['size_tablet'],
                    'number_font_size__phone' => $typography['counter']['size_phone'],
                    'number_font_weight' => $typography['counter']['weight'],
                    'number_font_family' => 'inherit',
                    'number_color' => $colors['primary'],
                    'number_line_height' => $typography['counter']['line_height'],
                    'title_font_size' => $typography['content']['size'],
                    'title_font_size__tablet' => $typography['content']['size_tablet'],
                    'title_font_size__phone' => $typography['content']['size_phone'],
                    'title_font_weight' => $typography['label_weight'],
                    'title_color' => $colors['text_light'],
                    'title_line_height' => $typography['h4']['line_height'],
                    'title_margin' => ['top' => (int)($spacing['margin_sm'] * 0.66), 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'text_orientation' => 'center',
                    // Prefix/suffix
                    'prefix_font_size' => (int)($typography['counter']['size'] * 0.66),
                    'suffix_font_size' => (int)($typography['counter']['size'] * 0.66),
                    // Animation
                    'animation_duration' => $typography['animation_duration'],
                    'animation_delay' => 0,
                    // Background (optional card style)
                    'background_color' => 'transparent',
                    'padding' => $spacing['compact_padding'],
                    'border_radius' => $borders['radius_small']
                ];

            case 'testimonial':
                return [
                    'quote_icon' => 'on',
                    'quote_icon_color' => $colors['primary'],
                    'text_orientation' => 'left',
                    'background_color' => $colors['white'],
                    'padding' => $spacing['card_padding'],
                    'padding__tablet' => $spacing['card_padding_tablet'],
                    'padding__phone' => $spacing['card_padding_phone'],
                    'border_radius' => $borders['radius_large'],
                    'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'border_color' => 'transparent',
                    'box_shadow' => $shadows['card'],
                    // Content styles
                    'content_font_size' => $typography['content']['size'],
                    'content_font_size__tablet' => $typography['content']['size_tablet'],
                    'content_font_size__phone' => $typography['content']['size_phone'],
                    'content_color' => $colors['text'],
                    'content_line_height' => $typography['body']['line_height'],
                    'content_font_style' => 'italic',
                    'content_margin' => ['top' => 0, 'right' => 0, 'bottom' => (int)($spacing['margin_md'] * 0.83), 'left' => 0],
                    // Author styles
                    'author_font_size' => $typography['content']['size'],
                    'author_font_weight' => $typography['title_weight'],
                    'author_color' => $colors['text'],
                    // Position/title styles
                    'position_font_size' => $typography['content']['size_phone'],
                    'position_color' => $colors['text_light'],
                    // Portrait styles
                    'portrait_width' => (int)($spacing['margin_xl'] * 1.25),
                    'portrait_border_radius' => '50%',
                    // Hover effects
                    'hover_box_shadow' => $shadows['elevated'],
                    'hover_transform' => 'translateY(-2px)',
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'pricing_table':
                return [
                    // Header
                    'header_bg_color' => $colors['primary'],
                    'header_text_color' => $colors['white'],
                    'header_padding' => $spacing['compact_padding'],
                    'title_font_size' => $typography['h4']['size'],
                    'title_font_weight' => $typography['h4']['weight'],
                    // Price
                    'price_color' => $colors['text'],
                    'price_font_size' => $typography['counter']['size'],
                    'price_font_size__tablet' => $typography['counter']['size_tablet'],
                    'price_font_size__phone' => $typography['counter']['size_phone'],
                    'price_font_weight' => $typography['counter']['weight'],
                    'currency_font_size' => (int)($typography['counter']['size'] * 0.5),
                    'period_font_size' => $typography['content']['size'],
                    'period_color' => $colors['text_light'],
                    // Features
                    'features_color' => $colors['text_light'],
                    'features_font_size' => $typography['content']['size_tablet'],
                    'features_line_height' => '2',
                    'feature_icon_color' => $colors['primary'],
                    // Button
                    'button_bg_color' => $colors['primary'],
                    'button_text_color' => $colors['white'],
                    'button_font_size' => $typography['button']['size'],
                    'button_font_weight' => $typography['button']['weight'],
                    'button_padding' => $spacing['button_padding'],
                    'button_border_radius' => $borders['radius_small'],
                    'button_hover_bg_color' => self::adjustColor($colors['primary'], -15),
                    // Card styling
                    'background_color' => $colors['white'],
                    'border_radius' => $borders['radius_medium'],
                    'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'border_color' => $colors['gray_200'],
                    'box_shadow' => $shadows['card'],
                    'padding' => ['top' => 0, 'right' => 0, 'bottom' => $spacing['margin_lg'], 'left' => 0],
                    // Featured badge
                    'featured' => false,
                    'badge_text' => 'Popular',
                    'badge_bg_color' => $colors['secondary'],
                    'badge_text_color' => $colors['white'],
                    // Hover
                    'hover_box_shadow' => $shadows['elevated'],
                    'hover_transform' => 'translateY(-4px)',
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'team_member':
                return [
                    'text_orientation' => 'center',
                    'header_level' => 'h4',
                    // Name styles
                    'name_font_size' => $typography['name']['size'],
                    'name_font_size__tablet' => $typography['name']['size_tablet'],
                    'name_font_size__phone' => $typography['name']['size_phone'],
                    'name_font_weight' => $typography['name']['weight'],
                    'name_color' => $colors['text'],
                    'name_line_height' => $typography['name']['line_height'],
                    'name_margin' => ['top' => $spacing['margin_sm'], 'right' => 0, 'bottom' => (int)($spacing['margin_sm'] * 0.33), 'left' => 0],
                    // Position styles
                    'position_font_size' => $typography['subtitle']['size'],
                    'position_font_size__tablet' => $typography['subtitle']['size_tablet'],
                    'position_color' => $colors['text_light'],
                    'position_font_weight' => $typography['subtitle']['weight'],
                    // Bio/content styles
                    'content_font_size' => $typography['subtitle']['size'],
                    'content_color' => $colors['text_light'],
                    'content_margin' => ['top' => $spacing['margin_sm'], 'right' => 0, 'bottom' => $spacing['margin_sm'], 'left' => 0],
                    // Image styles
                    'image_width' => (int)($spacing['margin_xl'] * 2.5),
                    'image_height' => (int)($spacing['margin_xl'] * 2.5),
                    'image_border_radius' => '50%',
                    'image_box_shadow' => $shadows['card'],
                    // Social icons
                    'social_icon_size' => $typography['name']['size'],
                    'social_icon_color' => $colors['text_light'],
                    'social_icon_hover_color' => $colors['primary'],
                    'social_icon_spacing' => $spacing['margin_sm'],
                    // Card styling
                    'background_color' => $colors['white'],
                    'padding' => $spacing['card_padding'],
                    'padding__tablet' => $spacing['card_padding_tablet'],
                    'padding__phone' => $spacing['card_padding_phone'],
                    'border_radius' => $borders['radius_medium'],
                    'border_width' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
                    'border_color' => 'transparent',
                    'box_shadow' => $shadows['card'],
                    // Hover
                    'hover_box_shadow' => $shadows['elevated'],
                    'hover_transform' => 'translateY(-4px)',
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'accordion':
                return [
                    // Icon styles
                    'icon_color' => $colors['primary'],
                    'icon_size' => $typography['icon']['size'],
                    'icon_position' => 'right',
                    // Open state
                    'open_title_color' => $colors['primary'],
                    'open_header_bg' => $colors['gray_50'],
                    'open_icon_color' => $colors['primary'],
                    // Closed state
                    'closed_title_color' => $colors['text'],
                    'closed_header_bg' => $colors['white'],
                    // Title styles
                    'title_font_size' => $typography['content']['size'],
                    'title_font_size__tablet' => $typography['content']['size_tablet'],
                    'title_font_size__phone' => $typography['content']['size_phone'],
                    'title_font_weight' => $typography['title_weight'],
                    'title_line_height' => $typography['h4']['line_height'],
                    // Header
                    'header_padding' => $spacing['compact_padding'],
                    'header_padding__tablet' => $spacing['compact_padding_tablet'],
                    'header_padding__phone' => $spacing['compact_padding_phone'],
                    // Content
                    'content_bg' => $colors['white'],
                    'content_color' => $colors['text_light'],
                    'content_font_size' => $typography['content']['size_tablet'],
                    'content_line_height' => $typography['body']['line_height'],
                    'content_padding' => $spacing['compact_padding'],
                    'content_padding__tablet' => $spacing['compact_padding_tablet'],
                    'content_padding__phone' => $spacing['compact_padding_phone'],
                    // Border
                    'border_color' => $colors['gray_200'],
                    'border_width' => $borders['width'],
                    'border_radius' => $borders['radius_small'],
                    // Item spacing
                    'item_margin' => $spacing['margin_bottom_sm'],
                    // Animation
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'divider':
                return [
                    'divider_style' => 'solid',
                    'divider_weight' => $borders['width'],
                    'divider_color' => $colors['gray_200'],
                    'height' => 'auto',
                    'max_width' => '100%',
                    // Positioning
                    'align' => 'center',
                    'margin' => $spacing['margin_bottom_lg'],
                    'margin__tablet' => $spacing['margin_bottom_md'],
                    'margin__phone' => ['top' => 0, 'right' => 0, 'bottom' => (int)($spacing['margin_md'] * 0.83), 'left' => 0]
                ];

            case 'icon':
                return [
                    'icon_color' => $colors['primary'],
                    'icon_size' => $typography['icon']['size_xl'],
                    'icon_size__tablet' => $typography['icon']['size_xl_tablet'],
                    'icon_size__phone' => $typography['icon']['size_xl_phone'],
                    'icon_style' => 'none',
                    // Background (for circle/square styles)
                    'icon_bg_color' => $colors['primary'],
                    'icon_padding' => $spacing['margin_sm'],
                    'icon_border_radius' => '50%',
                    // Hover
                    'hover_icon_color' => self::adjustColor($colors['primary'], -15),
                    'hover_icon_bg_color' => self::adjustColor($colors['primary'], -15),
                    'hover_transform' => 'scale(1.1)',
                    // Alignment
                    'align' => 'center',
                    'margin' => $spacing['margin_bottom_sm'],
                    // Transition
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'cta':
                return [
                    'header_level' => 'h3',
                    'text_orientation' => 'center',
                    'background_color' => $colors['primary'],
                    'background_type' => 'color',
                    // Title
                    'title_font_size' => $typography['h3']['size'],
                    'title_font_size__tablet' => $typography['h3']['size_tablet'],
                    'title_font_size__phone' => $typography['h3']['size_phone'],
                    'title_font_weight' => $typography['h3']['weight'],
                    'title_color' => $colors['white'],
                    'title_line_height' => $typography['h3']['line_height'],
                    'title_margin' => $spacing['margin_bottom_sm'],
                    // Content/description
                    'content_font_size' => $typography['body']['size'],
                    'content_font_size__tablet' => $typography['content']['size'],
                    'content_font_size__phone' => $typography['content']['size_tablet'],
                    'content_color' => $colors['gray_200'],
                    'content_line_height' => $typography['content']['line_height'],
                    'content_max_width' => '600px',
                    'content_margin' => $spacing['margin_bottom_md'],
                    // Button
                    'button_background_color' => $colors['white'],
                    'button_text_color' => $colors['primary'],
                    'button_font_size' => $typography['button']['size'],
                    'button_font_weight' => $typography['button']['weight'],
                    'button_padding' => $spacing['button_padding'],
                    'button_border_radius' => $borders['radius_small'],
                    'button_hover_background_color' => $colors['gray_100'],
                    'button_hover_text_color' => $colors['primary'],
                    'button_box_shadow' => $shadows['card'],
                    // Container
                    'padding' => $spacing['cta_padding'],
                    'padding__tablet' => $spacing['cta_padding_tablet'],
                    'padding__phone' => $spacing['cta_padding_phone'],
                    'border_radius' => $borders['radius_medium'],
                    'box_shadow' => $shadows['elevated'],
                    // Max width for readability
                    'max_width' => '800px'
                ];

            case 'gallery':
                return [
                    // Layout
                    'layout' => 'grid',
                    'columns' => 3,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gutter' => (int)($spacing['margin_md'] * 0.83),
                    'gutter__tablet' => $spacing['margin_sm'],
                    'gutter__phone' => $spacing['margin_sm'],
                    // Image
                    'image_border_radius' => $borders['radius_small']['top_left'],
                    'image_aspect_ratio' => '1:1',
                    'image_object_fit' => 'cover',
                    // Overlay
                    'overlay_color' => 'rgba(0,0,0,0.4)',
                    'overlay_hover_color' => 'rgba(0,0,0,0.6)',
                    'show_overlay_on_hover' => true,
                    // Title on overlay
                    'title_font_size' => $typography['content']['size'],
                    'title_font_size__tablet' => $typography['content']['size_tablet'],
                    'title_font_size__phone' => $typography['content']['size_phone'],
                    'title_font_weight' => $typography['title_weight'],
                    'title_color' => $colors['white'],
                    // Caption
                    'caption_font_size' => $typography['subtitle']['size'],
                    'caption_color' => $colors['gray_200'],
                    // Lightbox
                    'enable_lightbox' => true,
                    'lightbox_bg_color' => 'rgba(0,0,0,0.9)',
                    // Hover effect
                    'hover_transform' => 'scale(1.05)',
                    'hover_box_shadow' => $shadows['elevated'],
                    // Transition
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'video':
                return [
                    // Video settings
                    'autoplay' => false,
                    'loop' => false,
                    'muted' => false,
                    'controls' => true,
                    // Play button
                    'play_icon_color' => $colors['white'],
                    'play_icon_size' => (int)($typography['counter']['size'] * 1.33),
                    'play_icon_size__tablet' => (int)($typography['counter']['size_tablet'] * 1.4),
                    'play_icon_size__phone' => $typography['counter']['size_phone'],
                    'play_icon_bg_color' => 'rgba(0,0,0,0.6)',
                    'play_icon_hover_bg_color' => $colors['primary'],
                    'play_icon_border_radius' => '50%',
                    // Poster overlay
                    'overlay_color' => 'rgba(0,0,0,0.3)',
                    // Container
                    'border_radius' => $borders['radius_medium'],
                    'box_shadow' => $shadows['card'],
                    'aspect_ratio' => '16:9',
                    // Responsive
                    'max_width' => '100%',
                    // Transition
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'tabs':
                return [
                    // Active tab
                    'active_tab_bg' => $colors['primary'],
                    'active_tab_color' => $colors['white'],
                    'active_tab_font_weight' => $typography['title_weight'],
                    // Inactive tab
                    'inactive_tab_bg' => $colors['gray_100'],
                    'inactive_tab_color' => $colors['text'],
                    'inactive_tab_font_weight' => $typography['label_weight'],
                    // Hover
                    'hover_tab_bg' => $colors['gray_200'],
                    'hover_tab_color' => $colors['text'],
                    // Tab styles
                    'tab_font_size' => $typography['content']['size'],
                    'tab_font_size__tablet' => $typography['content']['size_tablet'],
                    'tab_font_size__phone' => $typography['content']['size_phone'],
                    'tab_padding' => ['top' => $spacing['margin_sm'], 'right' => $spacing['margin_md'], 'bottom' => $spacing['margin_sm'], 'left' => $spacing['margin_md']],
                    'tab_padding__tablet' => ['top' => (int)($spacing['margin_sm'] * 0.83), 'right' => (int)($spacing['margin_md'] * 0.83), 'bottom' => (int)($spacing['margin_sm'] * 0.83), 'left' => (int)($spacing['margin_md'] * 0.83)],
                    'tab_padding__phone' => ['top' => (int)($spacing['margin_sm'] * 0.83), 'right' => $spacing['margin_sm'], 'bottom' => (int)($spacing['margin_sm'] * 0.83), 'left' => $spacing['margin_sm']],
                    'tab_border_radius' => $borders['radius_small'],
                    // Content
                    'content_bg' => $colors['white'],
                    'content_color' => $colors['text'],
                    'content_font_size' => $typography['content']['size_tablet'],
                    'content_padding' => $spacing['compact_padding'],
                    'content_padding__tablet' => $spacing['compact_padding_tablet'],
                    'content_padding__phone' => $spacing['compact_padding_phone'],
                    // Border
                    'border_color' => $colors['gray_200'],
                    'border_width' => $borders['width'],
                    // Animation
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'contact_form':
                return [
                    // Input fields
                    'input_bg_color' => $colors['white'],
                    'input_text_color' => $colors['text'],
                    'input_placeholder_color' => $colors['gray_400'],
                    'input_border_color' => $colors['gray_300'],
                    'input_border_width' => $borders['width'],
                    'input_border_radius' => $borders['radius_small']['top_left'],
                    'input_font_size' => $typography['content']['size'],
                    'input_font_size__tablet' => $typography['content']['size_tablet'],
                    'input_font_size__phone' => $typography['content']['size_phone'],
                    'input_padding' => ['top' => $spacing['margin_sm'], 'right' => $spacing['margin_sm'], 'bottom' => $spacing['margin_sm'], 'left' => $spacing['margin_sm']],
                    'input_margin' => $spacing['margin_bottom_sm'],
                    // Focus state
                    'input_focus_border_color' => $colors['primary'],
                    'input_focus_box_shadow' => $shadows['input_focus'],
                    // Label
                    'label_color' => $colors['text'],
                    'label_font_size' => $typography['subtitle']['size'],
                    'label_font_weight' => $typography['label_weight'],
                    'label_margin' => ['top' => 0, 'right' => 0, 'bottom' => (int)($spacing['margin_sm'] * 0.5), 'left' => 0],
                    // Submit button
                    'submit_bg_color' => $colors['primary'],
                    'submit_text_color' => $colors['white'],
                    'submit_font_size' => $typography['button']['size'],
                    'submit_font_weight' => $typography['button']['weight'],
                    'submit_border_radius' => $borders['radius_small']['top_left'],
                    'submit_padding' => $spacing['button_padding'],
                    'submit_hover_bg_color' => self::adjustColor($colors['primary'], -15),
                    'submit_margin' => ['top' => (int)($spacing['margin_sm'] * 0.66), 'right' => 0, 'bottom' => 0, 'left' => 0],
                    // Form layout
                    'field_spacing' => $spacing['margin_sm'],
                    'form_max_width' => '600px',
                    // Success/error messages
                    'success_color' => $colors['success'],
                    'error_color' => $colors['error'],
                    // Transition
                    'transition_duration' => $typography['transition_fast']
                ];

            case 'blog':
                return [
                    // Layout
                    'layout' => 'grid',
                    'columns' => 3,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gap' => (int)($spacing['margin_lg'] * 0.94),
                    'gap__tablet' => $spacing['margin_md'],
                    'gap__phone' => (int)($spacing['margin_md'] * 0.83),
                    // Post card
                    'post_bg' => $colors['white'],
                    'post_border_radius' => $borders['radius_medium']['top_left'],
                    'post_padding' => ['top' => 0, 'right' => 0, 'bottom' => $spacing['margin_md'], 'left' => 0],
                    'post_box_shadow' => $shadows['card'],
                    'post_hover_box_shadow' => $shadows['elevated'],
                    'post_hover_transform' => 'translateY(-4px)',
                    // Image
                    'image_height' => (int)($spacing['margin_xl'] * 4.17),
                    'image_height__tablet' => (int)($spacing['margin_xl'] * 3.75),
                    'image_height__phone' => (int)($spacing['margin_xl'] * 3.33),
                    'image_border_radius' => $borders['radius_medium'],
                    // Title
                    'title_font_size' => $typography['h4']['size'],
                    'title_font_size__tablet' => $typography['h4']['size_tablet'],
                    'title_font_size__phone' => $typography['h4']['size_phone'],
                    'title_font_weight' => $typography['h4']['weight'],
                    'title_color' => $colors['text'],
                    'title_hover_color' => $colors['primary'],
                    'title_line_height' => $typography['h4']['line_height'],
                    'title_margin' => ['top' => $spacing['margin_sm'], 'right' => (int)($spacing['margin_md'] * 0.83), 'bottom' => (int)($spacing['margin_sm'] * 0.66), 'left' => (int)($spacing['margin_md'] * 0.83)],
                    // Meta
                    'meta_font_size' => $typography['subtitle']['size'],
                    'meta_color' => $colors['text_light'],
                    'meta_margin' => ['top' => 0, 'right' => (int)($spacing['margin_md'] * 0.83), 'bottom' => $spacing['margin_sm'], 'left' => (int)($spacing['margin_md'] * 0.83)],
                    'show_date' => true,
                    'show_author' => true,
                    'show_category' => true,
                    // Excerpt
                    'excerpt_font_size' => $typography['content']['size_tablet'],
                    'excerpt_font_size__tablet' => $typography['content']['size_phone'],
                    'excerpt_color' => $colors['text_light'],
                    'excerpt_line_height' => $typography['content']['line_height'],
                    'excerpt_lines' => 3,
                    'excerpt_margin' => ['top' => 0, 'right' => (int)($spacing['margin_md'] * 0.83), 'bottom' => $spacing['margin_sm'], 'left' => (int)($spacing['margin_md'] * 0.83)],
                    // Read more
                    'read_more_color' => $colors['primary'],
                    'read_more_hover_color' => self::adjustColor($colors['primary'], -15),
                    'read_more_font_size' => $typography['subtitle']['size'],
                    'read_more_font_weight' => $typography['title_weight'],
                    // Transition
                    'transition_duration' => $typography['transition_normal']
                ];

            case 'circle_counter':
                return [
                    // Circle
                    'circle_size' => (int)($spacing['margin_xl'] * 3.33),
                    'circle_size__tablet' => (int)($spacing['margin_xl'] * 2.92),
                    'circle_size__phone' => (int)($spacing['margin_xl'] * 2.5),
                    'stroke_width' => (int)($spacing['margin_sm'] * 0.83),
                    'bar_bg_color' => $colors['gray_200'],
                    'circle_color' => $colors['primary'],
                    'circle_bg_color' => 'transparent',
                    // Number
                    'number_font_size' => (int)($typography['counter']['size'] * 0.75),
                    'number_font_size__tablet' => (int)($typography['counter']['size_tablet'] * 0.8),
                    'number_font_size__phone' => (int)($typography['counter']['size_phone'] * 0.875),
                    'number_font_weight' => $typography['counter']['weight'],
                    'number_color' => $colors['text'],
                    'show_percent_sign' => true,
                    // Title
                    'title_font_size' => $typography['content']['size'],
                    'title_font_size__tablet' => $typography['content']['size_tablet'],
                    'title_font_size__phone' => $typography['content']['size_phone'],
                    'title_font_weight' => $typography['label_weight'],
                    'title_color' => $colors['text_light'],
                    'title_margin' => ['top' => $spacing['margin_sm'], 'right' => 0, 'bottom' => 0, 'left' => 0],
                    // Animation
                    'animation_duration' => $typography['animation_duration_short'],
                    'animation_delay' => 0,
                    // Alignment
                    'text_orientation' => 'center'
                ];

            case 'countdown':
                return [
                    // Display options
                    'show_labels' => true,
                    'show_days' => true,
                    'show_hours' => true,
                    'show_minutes' => true,
                    'show_seconds' => true,
                    // Number styles
                    'number_font_size' => $typography['counter']['size'],
                    'number_font_size__tablet' => $typography['counter']['size_tablet'],
                    'number_font_size__phone' => $typography['counter']['size_phone'],
                    'number_font_weight' => $typography['counter']['weight'],
                    'number_color' => $colors['text'],
                    // Label styles
                    'label_font_size' => $typography['subtitle']['size'],
                    'label_font_size__tablet' => $typography['subtitle']['size_tablet'],
                    'label_font_size__phone' => $spacing['margin_sm'],
                    'label_font_weight' => $typography['label_weight'],
                    'label_color' => $colors['text_light'],
                    'label_text_transform' => 'uppercase',
                    'label_letter_spacing' => '0.05em',
                    // Separator
                    'separator_color' => $colors['primary'],
                    'separator_size' => (int)($spacing['margin_sm'] * 0.66),
                    'show_separator' => true,
                    // Unit box
                    'unit_bg_color' => $colors['gray_100'],
                    'unit_padding' => ['top' => (int)($spacing['margin_md'] * 0.83), 'right' => $spacing['margin_md'], 'bottom' => (int)($spacing['margin_md'] * 0.83), 'left' => $spacing['margin_md']],
                    'unit_border_radius' => $borders['radius_medium'],
                    'unit_box_shadow' => $shadows['card'],
                    // Spacing
                    'unit_gap' => $spacing['margin_sm'],
                    'unit_gap__tablet' => $spacing['margin_sm'],
                    'unit_gap__phone' => (int)($spacing['margin_sm'] * 0.66),
                    // Alignment
                    'text_orientation' => 'center',
                    // Expired message
                    'expired_message' => 'Event has started!'
                ];

            case 'social_follow':
            case 'social_icons':
            case 'social-icons':
                return [
                    'icon_color' => $colors['text'],
                    'icon_hover_color' => $colors['primary'],
                    'icon_size' => $typography['icon']['size_large'],
                    'icon_size__tablet' => (int)($typography['icon']['size_large'] * 0.92),
                    'icon_size__phone' => $typography['icon']['size'],
                    // Icon background (for styled icons)
                    'icon_bg_color' => 'transparent',
                    'icon_hover_bg_color' => 'transparent',
                    'icon_padding' => (int)($spacing['margin_sm'] * 0.66),
                    'icon_border_radius' => '50%',
                    // Spacing
                    'icon_spacing' => $spacing['margin_sm'],
                    'icon_spacing__tablet' => $spacing['margin_sm'],
                    'icon_spacing__phone' => (int)($spacing['margin_sm'] * 0.83),
                    // Style
                    'icon_style' => 'simple',
                    // Alignment
                    'align' => 'left',
                    // Transition
                    'transition_duration' => $typography['transition_fast']
                ];

            case 'map':
                return [
                    'zoom' => 14,
                    'height' => (int)($spacing['margin_xl'] * 8.33),
                    'height__tablet' => (int)($spacing['margin_xl'] * 7.3),
                    'height__phone' => (int)($spacing['margin_xl'] * 6.25),
                    // Style
                    'border_radius' => $borders['radius_medium'],
                    'box_shadow' => $shadows['card'],
                    // Marker
                    'marker_color' => $colors['primary'],
                    // Controls
                    'show_zoom_controls' => true,
                    'draggable' => true,
                    // Map type
                    'map_type' => 'roadmap',
                    // Custom styling (for Google Maps)
                    'grayscale' => false,
                    // Responsive
                    'max_width' => '100%'
                ];

            case 'slider':
                return [
                    // Controls
                    'autoplay' => true,
                    'autoplay_speed' => $typography['slider_autoplay'],
                    'loop' => true,
                    'pause_on_hover' => true,
                    // Navigation arrows
                    'arrows' => true,
                    'arrow_color' => $colors['text'],
                    'arrow_bg_color' => 'rgba(255,255,255,0.9)',
                    'arrow_hover_bg_color' => $colors['white'],
                    'arrow_size' => (int)($spacing['margin_xl'] * 0.83),
                    'arrow_border_radius' => '50%',
                    // Dots/pagination
                    'dots' => true,
                    'dot_color' => $colors['text_light'],
                    'dot_active_color' => $colors['primary'],
                    'dot_size' => (int)($spacing['margin_sm'] * 0.83),
                    'dot_spacing' => (int)($spacing['margin_sm'] * 0.66),
                    // Transition
                    'transition_type' => 'slide',
                    'transition_speed' => $typography['slider_transition'],
                    // Container
                    'border_radius' => $borders['radius_medium'],
                    'overflow' => 'hidden'
                ];

            case 'fullwidth_header':
                return [
                    'min_height' => $typography['h1']['size'] * 10,
                    'min_height__tablet' => $typography['h1']['size'] * 8,
                    'min_height__phone' => $typography['h1']['size'] * 6,
                    'background_color' => $colors['dark'],
                    'overlay_color' => 'rgba(0,0,0,0.5)',
                    'title_font_size' => $typography['h1']['size'],
                    'title_font_size__tablet' => (int)($typography['h1']['size'] * 0.75),
                    'title_font_size__phone' => (int)($typography['h1']['size'] * 0.57),
                    'title_font_weight' => $typography['h1']['weight'],
                    'title_color' => $colors['white'],
                    'subtitle_font_size' => (int)($typography['body']['size'] * 1.1),
                    'subtitle_color' => $colors['gray_200'],
                    'button_one_bg' => $colors['primary'],
                    'button_one_color' => $colors['white'],
                    'button_two_bg' => 'transparent',
                    'button_two_color' => $colors['white'],
                    'button_two_border_color' => $colors['white']
                ];

            // =============================================
            // FULLWIDTH MODULES
            // =============================================

            case 'fullwidth_image':
                return [
                    'min_height' => (int)($spacing['margin_xl'] * 8.33),
                    'min_height__tablet' => (int)($spacing['margin_xl'] * 6.25),
                    'min_height__phone' => (int)($spacing['margin_xl'] * 4.17),
                    'overlay_color' => 'rgba(0,0,0,0.3)',
                    'object_fit' => 'cover'
                ];

            case 'fullwidth_slider':
                return [
                    'min_height' => (int)($spacing['margin_xl'] * 10.4),
                    'min_height__tablet' => (int)($spacing['margin_xl'] * 8.33),
                    'min_height__phone' => (int)($spacing['margin_xl'] * 6.25),
                    'autoplay' => true,
                    'arrows' => true,
                    'dots' => true,
                    'arrow_color' => $colors['white'],
                    'dot_color' => $colors['white']
                ];

            case 'fullwidth_slider_item':
                return [
                    'background_color' => $colors['dark'],
                    'overlay_color' => 'rgba(0,0,0,0.4)',
                    'title_color' => $colors['white'],
                    'content_color' => $colors['gray_200'],
                    'button_bg' => $colors['primary'],
                    'button_color' => $colors['white']
                ];

            case 'fullwidth_map':
                return [
                    'height' => (int)($spacing['margin_xl'] * 9.375),
                    'height__tablet' => (int)($spacing['margin_xl'] * 7.3),
                    'height__phone' => (int)($spacing['margin_xl'] * 5.83),
                    'zoom' => 14
                ];

            case 'fullwidth_menu':
                return [
                    'background_color' => $colors['white'],
                    'link_color' => $colors['text'],
                    'link_hover_color' => $colors['primary'],
                    'font_size' => $typography['small']['size']
                ];

            case 'fullwidth_portfolio':
                return [
                    'columns' => 4,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gutter' => 0,
                    'overlay_color' => 'rgba(0,0,0,0.7)',
                    'title_color' => $colors['white']
                ];

            case 'fullwidth_post_slider':
                return [
                    'posts_per_view' => 3,
                    'autoplay' => true,
                    'arrows' => true,
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'meta_color' => $colors['text_light']
                ];

            case 'fullwidth_post_title':
                return [
                    'title_font_size' => $typography['h1']['size'],
                    'title_font_size__tablet' => (int)($typography['h1']['size'] * 0.8),
                    'title_font_size__phone' => (int)($typography['h1']['size'] * 0.65),
                    'title_color' => $colors['text'],
                    'meta_font_size' => $typography['subtitle']['size'],
                    'meta_color' => $colors['text_light'],
                    'text_align' => 'center'
                ];

            case 'fullwidth_code':
                return [
                    'background_color' => $colors['code_bg'],
                    'text_color' => $colors['code_text'],
                    'font_size' => $typography['code']['size'],
                    'font_family' => $typography['code']['family'],
                    'padding' => $spacing['card_padding'],
                    'border_radius' => $borders['radius_medium']
                ];

            // =============================================
            // INTERACTIVE MODULES
            // =============================================

            case 'accordion_item':
                return [
                    'title_font_size' => $typography['body']['size'],
                    'title_font_weight' => $typography['title_weight'],
                    'title_color' => $colors['text'],
                    'content_color' => $colors['text_light'],
                    'open' => false
                ];

            case 'tabs_item':
                return [
                    'title_font_size' => $typography['body']['size'],
                    'content_color' => $colors['text']
                ];

            case 'toggle':
                return [
                    'icon_color' => $colors['primary'],
                    'open_title_color' => $colors['primary'],
                    'open_header_bg' => $colors['light_bg'],
                    'closed_title_color' => $colors['text'],
                    'closed_header_bg' => $colors['white'],
                    'title_font_size' => $typography['body']['size'],
                    'title_font_weight' => '600'
                ];

            case 'slider_item':
                return [
                    'background_color' => $colors['light_bg'],
                    'title_font_size' => $typography['h2']['size'],
                    'title_color' => $colors['text'],
                    'content_color' => $colors['text_light'],
                    'button_bg' => $colors['primary'],
                    'button_color' => $colors['white']
                ];

            // =============================================
            // COUNTER MODULES
            // =============================================

            case 'bar_counter':
                return [
                    'bar_bg_color' => $colors['gray_200'],
                    'bar_color' => $colors['primary'],
                    'bar_height' => $spacing['margin_sm'],
                    'bar_border_radius' => (int)($spacing['margin_sm'] * 0.5),
                    'title_font_size' => $typography['small']['size'],
                    'title_color' => $colors['text'],
                    'percent_font_size' => $typography['small']['size'],
                    'percent_color' => $colors['text_light']
                ];

            case 'bar_counter_item':
                return [
                    'bar_color' => $colors['primary'],
                    'title_color' => $colors['text'],
                    'percent' => 75
                ];

            // =============================================
            // PRICING MODULES
            // =============================================

            case 'pricing_table_item':
                return [
                    'icon_color' => $colors['primary'],
                    'text_color' => $colors['text_light'],
                    'font_size' => $typography['body']['size'] - 2
                ];

            // =============================================
            // FORM MODULES
            // =============================================

            case 'contact_form_field':
                return [
                    'label_color' => $colors['text'],
                    'input_bg_color' => $colors['white'],
                    'input_text_color' => $colors['text'],
                    'input_border_color' => $colors['gray_300'],
                    'input_focus_border_color' => $colors['primary'],
                    'placeholder_color' => $colors['gray_400']
                ];

            case 'login':
            case 'signup':
                return [
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'input_bg_color' => $colors['white'],
                    'input_border_color' => $colors['gray_300'],
                    'input_border_radius' => $borders['radius_small'],
                    'button_bg_color' => $colors['primary'],
                    'button_text_color' => $colors['white'],
                    'button_border_radius' => $borders['radius_small'],
                    'link_color' => $colors['primary']
                ];

            case 'search':
            case 'search-form':
                return [
                    'input_bg_color' => $colors['white'],
                    'input_text_color' => $colors['text'],
                    'input_border_color' => $colors['gray_300'],
                    'input_border_radius' => $borders['radius_small'],
                    'button_bg_color' => $colors['primary'],
                    'button_text_color' => $colors['white'],
                    'button_border_radius' => $borders['radius_small'],
                    'placeholder_color' => $colors['gray_400']
                ];

            // =============================================
            // BLOG/POST MODULES
            // =============================================

            case 'portfolio':
            case 'filterable_portfolio':
                return [
                    'layout' => 'grid',
                    'columns' => 3,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gap' => $spacing['element_gap'],
                    'overlay_color' => 'rgba(0,0,0,0.7)',
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['white'],
                    'category_color' => $colors['gray_200'],
                    'filter_color' => $colors['text'],
                    'filter_active_color' => $colors['primary'],
                    'border_radius' => $borders['radius_medium']
                ];

            case 'post_slider':
                return [
                    'posts_per_view' => 3,
                    'autoplay' => true,
                    'arrows' => true,
                    'dots' => true,
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'meta_font_size' => $typography['subtitle']['size'],
                    'meta_color' => $colors['text_light'],
                    'arrow_color' => $colors['text'],
                    'dot_color' => $colors['primary']
                ];

            case 'post_navigation':
                return [
                    'background_color' => $colors['light_bg'],
                    'title_font_size' => $typography['body']['size'],
                    'title_color' => $colors['text'],
                    'label_color' => $colors['text_light'],
                    'arrow_color' => $colors['primary'],
                    'padding' => $spacing['card_padding'],
                    'border_radius' => $borders['radius_medium']
                ];

            case 'comments':
                return [
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'author_font_size' => $typography['body']['size'],
                    'author_color' => $colors['text'],
                    'date_color' => $colors['text_light'],
                    'content_color' => $colors['text_light'],
                    'avatar_size' => $typography['counter']['size'],
                    'avatar_border_radius' => '50%',
                    'reply_color' => $colors['primary']
                ];

            case 'sidebar':
                return [
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'text_color' => $colors['text_light'],
                    'link_color' => $colors['primary'],
                    'widget_margin' => $spacing['margin_bottom_lg']
                ];

            case 'related-posts':
                return [
                    'columns' => 3,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gap' => $spacing['element_gap'],
                    'title_font_size' => $typography['h3']['size'],
                    'post_title_font_size' => $typography['body']['size'],
                    'post_title_color' => $colors['text'],
                    'meta_color' => $colors['text_light'],
                    'image_border_radius' => $borders['radius_medium']
                ];

            // =============================================
            // THEME MODULES (Dynamic Content)
            // =============================================

            case 'post-title':
                return [
                    'font_size' => $typography['h1']['size'],
                    'font_size__tablet' => (int)($typography['h1']['size'] * 0.8),
                    'font_size__phone' => (int)($typography['h1']['size'] * 0.65),
                    'font_weight' => $typography['h1']['weight'],
                    'text_color' => $colors['text'],
                    'text_align' => 'left',
                    'show_meta' => true,
                    'meta_color' => $colors['text_light']
                ];

            case 'post-content':
                return [
                    'font_size' => $typography['body']['size'],
                    'line_height' => $typography['body']['line_height'],
                    'text_color' => $colors['text'],
                    'link_color' => $colors['primary'],
                    'heading_color' => $colors['text']
                ];

            case 'post-excerpt':
                return [
                    'font_size' => $typography['body']['size'],
                    'text_color' => $colors['text_light'],
                    'line_height' => $typography['body']['line_height']
                ];

            case 'post-meta':
                return [
                    'font_size' => $typography['subtitle']['size'],
                    'text_color' => $colors['text_light'],
                    'link_color' => $colors['primary'],
                    'separator' => '·'
                ];

            case 'featured-image':
                return [
                    'border_radius' => $borders['radius_large'],
                    'box_shadow' => $shadows['card'],
                    'object_fit' => 'cover',
                    'max_height' => (int)($spacing['margin_xl'] * 10.4)
                ];

            case 'author-box':
                return [
                    'background_color' => $colors['light_bg'],
                    'padding' => $spacing['card_padding'],
                    'border_radius' => $borders['radius_medium'],
                    'avatar_size' => (int)($spacing['margin_xl'] * 1.66),
                    'avatar_border_radius' => '50%',
                    'name_font_size' => $typography['h3']['size'],
                    'name_color' => $colors['text'],
                    'bio_font_size' => $typography['small']['size'],
                    'bio_color' => $colors['text_light']
                ];

            case 'archive-title':
                return [
                    'font_size' => $typography['h1']['size'],
                    'font_size__tablet' => (int)($typography['h1']['size'] * 0.8),
                    'font_size__phone' => (int)($typography['h1']['size'] * 0.65),
                    'font_weight' => $typography['h1']['weight'],
                    'text_color' => $colors['text'],
                    'text_align' => 'center',
                    'description_font_size' => $typography['body']['size'],
                    'description_color' => $colors['text_light']
                ];

            case 'archive-posts':
                return [
                    'layout' => 'grid',
                    'columns' => 3,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gap' => $spacing['element_gap'],
                    'post_bg' => $colors['white'],
                    'post_border_radius' => $borders['radius_medium'],
                    'post_box_shadow' => $shadows['card'],
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'excerpt_color' => $colors['text_light']
                ];

            case 'breadcrumbs':
                return [
                    'font_size' => $typography['subtitle']['size'],
                    'text_color' => $colors['text_light'],
                    'link_color' => $colors['primary'],
                    'separator' => '/',
                    'separator_color' => $colors['text_light']
                ];

            case 'site-logo':
                return [
                    'width' => (int)($spacing['margin_xl'] * 3.125),
                    'width__tablet' => (int)($spacing['margin_xl'] * 2.5),
                    'width__phone' => (int)($spacing['margin_xl'] * 2.08)
                ];

            case 'menu':
                return [
                    'font_size' => $typography['small']['size'],
                    'font_weight' => $typography['label_weight'],
                    'link_color' => $colors['text'],
                    'link_hover_color' => $colors['primary'],
                    'link_active_color' => $colors['primary'],
                    'dropdown_bg' => $colors['white'],
                    'dropdown_link_color' => $colors['text'],
                    'dropdown_hover_bg' => $colors['light_bg'],
                    'mobile_bg' => $colors['white'],
                    'hamburger_color' => $colors['text']
                ];

            // =============================================
            // MEDIA MODULES
            // =============================================

            case 'audio':
                return [
                    'player_bg' => $colors['light_bg'],
                    'player_color' => $colors['primary'],
                    'progress_color' => $colors['primary'],
                    'progress_bg' => $colors['gray_200'],
                    'border_radius' => $borders['radius_medium']
                ];

            case 'video_slider':
                return [
                    'autoplay' => false,
                    'arrows' => true,
                    'dots' => true,
                    'arrow_color' => $colors['white'],
                    'dot_color' => $colors['white']
                ];

            case 'video_slider_item':
                return [
                    'overlay_color' => 'rgba(0,0,0,0.4)',
                    'play_icon_color' => $colors['white'],
                    'title_color' => $colors['white']
                ];

            case 'map_pin':
                return [
                    'pin_color' => $colors['primary'],
                    'title_color' => $colors['text'],
                    'content_color' => $colors['text_light']
                ];

            // =============================================
            // E-COMMERCE MODULES
            // =============================================

            case 'shop':
                return [
                    'columns' => 4,
                    'columns__tablet' => 2,
                    'columns__phone' => 1,
                    'gap' => $spacing['element_gap'],
                    'product_bg' => $colors['white'],
                    'product_border_radius' => $borders['radius_medium'],
                    'product_box_shadow' => $shadows['card'],
                    'title_font_size' => $typography['body']['size'],
                    'title_color' => $colors['text'],
                    'price_font_size' => $typography['body']['size'],
                    'price_color' => $colors['primary'],
                    'sale_price_color' => $colors['error'],
                    'button_bg' => $colors['primary'],
                    'button_color' => $colors['white']
                ];

            case 'cart-icon':
                return [
                    'icon_color' => $colors['text'],
                    'icon_size' => $typography['icon']['size_large'],
                    'badge_bg' => $colors['primary'],
                    'badge_color' => $colors['white'],
                    'badge_font_size' => (int)($spacing['margin_sm'] * 0.83)
                ];

            // =============================================
            // MISC MODULES
            // =============================================

            case 'code':
                return [
                    'background_color' => $colors['code_bg'],
                    'text_color' => $colors['code_text'],
                    'font_size' => $typography['code']['size'],
                    'font_family' => $typography['code']['family'],
                    'padding' => $spacing['card_padding'],
                    'border_radius' => $borders['radius_medium'],
                    'line_numbers' => true,
                    'line_number_color' => $colors['text_light']
                ];

            case 'social_follow_item':
                return [
                    'icon_color' => $colors['text'],
                    'icon_hover_color' => $colors['primary'],
                    'icon_size' => $typography['icon']['size']
                ];

            case 'copyright':
                return [
                    'font_size' => $typography['body']['size'] - 4,
                    'text_color' => $colors['text_light'],
                    'link_color' => $colors['primary'],
                    'text_align' => 'center'
                ];

            case 'footer-menu':
                return [
                    'font_size' => $typography['body']['size'] - 2,
                    'link_color' => $colors['text_light'],
                    'link_hover_color' => $colors['primary'],
                    'separator' => '|'
                ];

            case 'footer-info':
                return [
                    'title_font_size' => $typography['h3']['size'],
                    'title_color' => $colors['text'],
                    'text_font_size' => $typography['body']['size'] - 2,
                    'text_color' => $colors['text_light'],
                    'link_color' => $colors['primary']
                ];

            case 'header-button':
                // Smaller button padding for header (60% of compact)
                $headerBtnPadV = (int)($spacing['compact_padding']['top'] * 0.6);
                $headerBtnPadH = (int)($spacing['compact_padding']['right'] * 0.8);
                return [
                    'background_color' => $colors['primary'],
                    'text_color' => $colors['white'],
                    'font_size' => $typography['body']['size'] - 2,
                    'font_weight' => $typography['title_weight'],
                    'padding' => ['top' => $headerBtnPadV, 'right' => $headerBtnPadH, 'bottom' => $headerBtnPadV, 'left' => $headerBtnPadH],
                    'border_radius' => $borders['radius_small'],
                    'hover_background_color' => self::adjustColor($colors['primary'], -15)
                ];

            default:
                return [];
        }
    }

    /**
     * Adjust color brightness (darken/lighten)
     * @param string $hex Hex color
     * @param int $percent Negative = darker, positive = lighter
     */
    private static function adjustColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    // ========================================
    // STEP 4: CONTENT
    // ========================================

    /**
     * Step 4: Generate text content for all modules
     * Uses AI to generate contextual content based on outline
     */
    private static function step4_generateContent(array $layout, array $outline, string $prompt, JTB_AI_Core $ai): array
    {
        // Collect all modules that need content
        $modulesToFill = self::collectModulesNeedingContent($layout);

        if (empty($modulesToFill)) {
            return ['ok' => true, 'layout' => $layout];
        }

        // Detect industry for context
        $industry = self::detectIndustry($prompt);

        // Build content request
        $sectionsInfo = json_encode($outline['sections'] ?? [], JSON_PRETTY_PRINT);
        $modulesJson = json_encode($modulesToFill, JSON_PRETTY_PRINT);

        // Use JTB_AI_Knowledge for comprehensive system prompt
        $systemPrompt = JTB_AI_Knowledge::getContentGenerationPrompt([
            'industry' => $industry,
            'prompt' => $prompt
        ]);

        $userPrompt = <<<USER
## PAGE STRUCTURE
{$sectionsInfo}

## MODULES TO GENERATE CONTENT FOR
Each module has an ID and type. Generate content for ALL of them:

{$modulesJson}

## IMPORTANT
- Match each module_id exactly in your response
- Every module in the list above MUST have content generated
- Do NOT skip any modules
- Content must be SPECIFIC to: {$prompt}

Generate the JSON now.
USER;

        $response = $ai->queryWithRetry($userPrompt, 2, [
            'system_prompt' => $systemPrompt,
            'max_tokens' => self::MAX_TOKENS_CONTENT,
            'temperature' => 0.7,
            'json_mode' => true
        ]);

        self::log("Content response OK: " . ($response['ok'] ? 'YES' : 'NO'));

        if (!$response['ok']) {
            return ['ok' => false, 'error' => $response['error'] ?? 'Content generation failed'];
        }

        $contentData = self::parseJsonResponse($response['text']);

        if ($contentData === null || empty($contentData['modules'])) {
            return ['ok' => false, 'error' => 'Failed to parse content response'];
        }

        // Apply content to layout
        $layout = self::applyContentToLayout($layout, $contentData['modules']);

        return ['ok' => true, 'layout' => $layout];
    }

    /**
     * Collect all modules that need content filled in
     */
    private static function collectModulesNeedingContent(array $layout, string $parentSection = ''): array
    {
        $modules = [];

        foreach ($layout['sections'] ?? [] as $section) {
            $sectionType = $section['section_type'] ?? 'content';
            self::collectModulesFromChildren($section['children'] ?? [], $sectionType, $modules);
        }

        return $modules;
    }

    /**
     * Recursively collect modules from children
     */
    private static function collectModulesFromChildren(array $children, string $sectionType, array &$modules): void
    {
        foreach ($children as $child) {
            $type = $child['type'] ?? '';

            if ($type === 'row' || $type === 'column') {
                self::collectModulesFromChildren($child['children'] ?? [], $sectionType, $modules);
            } elseif (!in_array($type, ['section', 'row', 'column'])) {
                // This is a module
                $modules[] = [
                    'id' => $child['id'] ?? self::generateUniqueId(),
                    'type' => $type,
                    'section_type' => $sectionType
                ];
            }
        }
    }

    /**
     * Apply generated content to layout
     */
    private static function applyContentToLayout(array $layout, array $contentMap): array
    {
        if (!isset($layout['sections'])) {
            return $layout;
        }

        foreach ($layout['sections'] as $i => $section) {
            $layout['sections'][$i]['children'] = self::applyContentToChildren($section['children'] ?? [], $contentMap);
        }

        return $layout;
    }

    /**
     * Recursively apply content to children
     */
    private static function applyContentToChildren(array $children, array $contentMap): array
    {
        $result = [];

        foreach ($children as $child) {
            $type = $child['type'] ?? '';
            $id = $child['id'] ?? '';

            if ($type === 'row' || $type === 'column') {
                $child['children'] = self::applyContentToChildren($child['children'] ?? [], $contentMap);
            } elseif (isset($contentMap[$id])) {
                // Transform content if AI returned wrong format
                $content = self::normalizeContentFields($contentMap[$id], $type);
                // Merge content into attrs
                $child['attrs'] = array_merge($child['attrs'] ?? [], $content);
                self::log("Content applied to {$id} ({$type}): " . json_encode(array_keys($content)));
            }

            $result[] = $child;
        }

        return $result;
    }

    /**
     * Normalize content fields - fix common AI format mistakes
     */
    private static function normalizeContentFields(array $content, string $moduleType): array
    {
        // If AI returned {type: value} instead of {field_name: value}, fix it
        if (isset($content[$moduleType])) {
            // AI returned e.g. {"heading": "text"} instead of {"text": "text"}
            $value = $content[$moduleType];
            unset($content[$moduleType]);

            // If value is array with actual content, extract it
            if (is_array($value)) {
                if (isset($value['text'])) {
                    $value = $value['text'];
                } elseif (isset($value['content'])) {
                    $value = $value['content'];
                } else {
                    // Can't parse, skip
                    return $content;
                }
            }

            switch ($moduleType) {
                case 'heading':
                    $content['text'] = $value;
                    break;
                case 'text':
                    $content['content'] = is_string($value) && strpos($value, '<') === false
                        ? "<p>{$value}</p>"
                        : $value;
                    break;
                case 'button':
                    $content['text'] = $value;
                    break;
                case 'image':
                    $content['src'] = $value;
                    break;
            }
        }

        // Ensure all string fields are actually strings (fix array issues)
        foreach (['text', 'title', 'content', 'src', 'link_url'] as $field) {
            if (isset($content[$field]) && is_array($content[$field])) {
                // Try to extract string from nested structure
                $val = $content[$field];
                if (isset($val['text'])) {
                    $content[$field] = $val['text'];
                } elseif (isset($val['content'])) {
                    $content[$field] = $val['content'];
                } elseif (isset($val[0]) && is_string($val[0])) {
                    $content[$field] = $val[0];
                } else {
                    // Can't fix, remove the field
                    unset($content[$field]);
                }
            }
        }

        // Ensure text module has content wrapped in HTML
        if ($moduleType === 'text' && isset($content['content'])) {
            $c = $content['content'];
            if (is_string($c) && strpos($c, '<') === false) {
                $content['content'] = "<p>{$c}</p>";
            }
        }

        return $content;
    }

    /**
     * Apply placeholder content when AI fails
     */
    private static function step4_applyPlaceholderContent(array $layout, array $outline): array
    {
        $pageTitle = $outline['page_title'] ?? 'Welcome';
        $pageSubtitle = $outline['page_subtitle'] ?? 'Your trusted partner';

        if (!isset($layout['sections'])) {
            return $layout;
        }

        foreach ($layout['sections'] as $i => $section) {
            $sectionType = $section['section_type'] ?? 'content';
            $sectionPlan = self::findSectionPlan($outline, $sectionType);
            $headline = $sectionPlan['headline'] ?? ucfirst($sectionType);

            $layout['sections'][$i]['children'] = self::applyPlaceholderToChildren(
                $section['children'] ?? [],
                $sectionType,
                $pageTitle,
                $headline
            );
        }

        return $layout;
    }

    /**
     * Find section plan from outline
     */
    private static function findSectionPlan(array $outline, string $sectionType): ?array
    {
        foreach ($outline['sections'] ?? [] as $plan) {
            if (($plan['type'] ?? '') === $sectionType) {
                return $plan;
            }
        }
        return null;
    }

    /**
     * Recursively apply placeholder content
     */
    private static function applyPlaceholderToChildren(array $children, string $sectionType, string $pageTitle, string $headline): array
    {
        static $counterIndex = 0;
        static $testimonialIndex = 0;
        static $blurbIndex = 0;

        $stats = [
            ['number' => '500', 'title' => 'Happy Clients', 'suffix' => '+'],
            ['number' => '98', 'title' => 'Success Rate', 'suffix' => '%'],
            ['number' => '10', 'title' => 'Years Experience', 'suffix' => '+'],
            ['number' => '24', 'title' => 'Support', 'suffix' => '/7']
        ];

        $testimonials = [
            ['content' => '<p>Excellent service and results. Highly recommended!</p>', 'author' => 'John Smith', 'job_title' => 'CEO', 'company' => 'TechCorp'],
            ['content' => '<p>Professional team that delivers on their promises.</p>', 'author' => 'Sarah Johnson', 'job_title' => 'Director', 'company' => 'InnovateCo'],
            ['content' => '<p>Outstanding work that exceeded our expectations.</p>', 'author' => 'Michael Brown', 'job_title' => 'Manager', 'company' => 'GlobalTech']
        ];

        $icons = ['star', 'shield', 'zap', 'heart', 'check', 'users'];
        $blurbTitles = ['Quality Service', 'Expert Team', 'Fast Delivery', 'Best Value', 'Trusted Partner', '24/7 Support'];

        $result = [];

        foreach ($children as $child) {
            $type = $child['type'] ?? '';

            if ($type === 'row' || $type === 'column') {
                $child['children'] = self::applyPlaceholderToChildren($child['children'] ?? [], $sectionType, $pageTitle, $headline);
            } else {
                // Ensure attrs exists
                if (!isset($child['attrs'])) {
                    $child['attrs'] = [];
                }

                // Apply placeholder based on module type
                switch ($type) {
                    case 'heading':
                        if ($sectionType === 'hero') {
                            $child['attrs']['text'] = $pageTitle;
                        } else {
                            $child['attrs']['text'] = $headline;
                        }
                        break;

                    case 'text':
                        $child['attrs']['content'] = '<p>Professional services tailored to your needs. We deliver excellence with every project.</p>';
                        break;

                    case 'button':
                        $child['attrs']['text'] = ($sectionType === 'cta') ? 'Get Started Today' : 'Learn More';
                        $child['attrs']['link_url'] = '#';
                        break;

                    case 'number_counter':
                        $stat = $stats[$counterIndex % count($stats)];
                        $child['attrs'] = array_merge($child['attrs'], $stat);
                        $counterIndex++;
                        break;

                    case 'testimonial':
                        $testimonial = $testimonials[$testimonialIndex % count($testimonials)];
                        $child['attrs'] = array_merge($child['attrs'], $testimonial);
                        $testimonialIndex++;
                        break;

                    case 'blurb':
                        $child['attrs']['title'] = $blurbTitles[$blurbIndex % count($blurbTitles)];
                        $child['attrs']['content'] = '<p>Brief description of this feature or benefit.</p>';
                        $child['attrs']['font_icon'] = $icons[$blurbIndex % count($icons)];
                        $blurbIndex++;
                        break;

                    case 'image':
                        $child['attrs']['src'] = '';
                        $child['attrs']['alt'] = 'Image';
                        break;
                }
            }

            $result[] = $child;
        }

        return $result;
    }

    // ========================================
    // STEP 5: IMAGES
    // ========================================

    /**
     * Step 5: Enrich layout with images from Pexels
     */
    private static function step5_enrichWithImages(array $layout, array $outline, string $prompt, array $options): array
    {
        $industry = self::detectIndustry($prompt);
        $businessKeywords = self::extractBusinessKeywords($prompt);

        foreach ($layout['sections'] ?? [] as &$section) {
            $sectionType = $section['section_type'] ?? 'content';
            $section['children'] = self::enrichChildrenWithImages($section['children'] ?? [], $sectionType, $industry, $businessKeywords);
        }

        return $layout;
    }

    /**
     * Recursively enrich children with images
     */
    private static function enrichChildrenWithImages(array $children, string $sectionType, string $industry, string $businessKeywords): array
    {
        foreach ($children as &$child) {
            $type = $child['type'] ?? '';

            if ($type === 'row' || $type === 'column') {
                $child['children'] = self::enrichChildrenWithImages($child['children'] ?? [], $sectionType, $industry, $businessKeywords);
            } elseif ($type === 'image') {
                $child = self::enrichImageModule($child, $sectionType, $industry, $businessKeywords);
            } elseif ($type === 'testimonial') {
                $child = self::enrichTestimonialWithImage($child);
            } elseif ($type === 'team_member') {
                $child = self::enrichTeamMemberWithImage($child);
            }
        }

        return $children;
    }

    /**
     * Enrich image module with Pexels image
     */
    private static function enrichImageModule(array $module, string $sectionType, string $industry, string $businessKeywords): array
    {
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') || !JTB_AI_Pexels::isConfigured()) {
            return $module;
        }

        try {
            $query = $businessKeywords ?: $industry;

            if ($sectionType === 'hero') {
                $result = JTB_AI_Pexels::getHeroImage(['industry' => $industry, 'query' => $query]);
            } else {
                $result = JTB_AI_Pexels::searchPhotos($query, ['per_page' => 1, 'orientation' => 'landscape']);
            }

            if (!empty($result['photos'][0]['src']['large'])) {
                $module['attrs']['src'] = $result['photos'][0]['src']['large'];
                $module['attrs']['alt'] = $result['photos'][0]['alt'] ?? 'Image';
            }
        } catch (\Exception $e) {
            self::log("Pexels error: " . $e->getMessage());
        }

        return $module;
    }

    /**
     * Enrich testimonial with portrait
     */
    private static function enrichTestimonialWithImage(array $module): array
    {
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') || !JTB_AI_Pexels::isConfigured()) {
            return $module;
        }

        try {
            $result = JTB_AI_Pexels::getPersonPhoto(['orientation' => 'square']);

            if (!empty($result['src']['medium'])) {
                $module['attrs']['portrait_url'] = $result['src']['medium'];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return $module;
    }

    /**
     * Enrich team member with photo
     */
    private static function enrichTeamMemberWithImage(array $module): array
    {
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') || !JTB_AI_Pexels::isConfigured()) {
            return $module;
        }

        try {
            $result = JTB_AI_Pexels::getPersonPhoto(['orientation' => 'square']);

            if (!empty($result['src']['medium'])) {
                $module['attrs']['image_url'] = $result['src']['medium'];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return $module;
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Parse JSON response from AI
     */
    private static function parseJsonResponse(string $text): ?array
    {
        $text = trim($text);

        // Remove markdown code blocks
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);

        // Try to extract JSON object
        if (preg_match('/\{[\s\S]*\}/m', $text, $matches)) {
            $json = $matches[0];
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Validate and fix layout structure
     */
    private static function validateAndFix(array $layout): array
    {
        $fixedSections = [];

        foreach ($layout['sections'] ?? [] as $section) {
            if (!isset($section['type'])) {
                $section['type'] = 'section';
            }
            if (!isset($section['id'])) {
                $section['id'] = self::generateUniqueId();
            }
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            // Validate children
            $section['children'] = self::validateChildren($section['children'] ?? []);

            $fixedSections[] = $section;
        }

        return ['sections' => $fixedSections];
    }

    /**
     * Recursively validate children
     */
    private static function validateChildren(array $children): array
    {
        $validated = [];

        foreach ($children as $child) {
            if (!isset($child['type'])) {
                continue;
            }
            if (!isset($child['id'])) {
                $child['id'] = self::generateUniqueId();
            }
            if (!isset($child['attrs'])) {
                $child['attrs'] = [];
            }

            if (isset($child['children'])) {
                $child['children'] = self::validateChildren($child['children']);
            }

            $validated[] = $child;
        }

        return $validated;
    }

    /**
     * Generate unique ID
     */
    private static function generateUniqueId(): string
    {
        return 'jtb_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }

    /**
     * Detect industry from prompt - delegates to JTB_AI_Generator (single source of truth)
     */
    private static function detectIndustry(string $prompt): string
    {
        $result = JTB_AI_Generator::detectIndustry($prompt);
        return !empty($result) ? $result : 'business';
    }

    /**
     * Get color hints for industry
     */
    /**
     * Get color hints for industry - delegates to JTB_AI_Styles (single source of truth)
     */
    private static function getColorHintsForIndustry(string $industry, string $style): array
    {
        // Use JTB_AI_Styles as the SINGLE source of truth for industry colors
        $industryPalette = JTB_AI_Styles::getIndustryColors($industry);
        $colors = [
            $industryPalette['primary'] ?? '#2563eb',
            $industryPalette['secondary'] ?? '#3b82f6',
            $industryPalette['accent'] ?? '#1e3a8a',
        ];

        return [
            'industry_colors' => implode(', ', $colors),
            'hint' => "Use colors from: " . implode(', ', $colors)
        ];
    }

    /**
     * Get default outline for fallback - includes FULL design tokens
     */
    private static function getDefaultOutline(string $prompt, array $options): array
    {
        $industry = self::detectIndustry($prompt);
        $style = $options['style'] ?? 'modern';

        // Extract business name
        $businessName = 'Your Business';
        if (preg_match('/^([^,.\n]{3,30})/i', trim($prompt), $matches)) {
            $businessName = trim($matches[1]);
        }

        // Get style-specific defaults
        $styleDefaults = self::getStyleDefaults($style);

        return [
            'page_title' => $businessName,
            'page_subtitle' => 'Professional services tailored to your needs',
            'brand_voice' => 'professional',

            // Colors - based on industry (single source: JTB_AI_Styles)
            'color_scheme' => JTB_AI_Styles::getIndustryColors($industry),

            // Typography - from style
            'typography' => $styleDefaults['typography'],

            // Spacing - from style
            'spacing' => $styleDefaults['spacing'],

            // Borders - from style
            'borders' => $styleDefaults['borders'],

            // Shadows - from style
            'shadows' => $styleDefaults['shadows'],

            'sections' => [
                ['type' => 'hero', 'purpose' => 'Capture attention', 'layout' => 'split-left', 'headline' => $businessName, 'background' => 'light'],
                ['type' => 'stats', 'purpose' => 'Build credibility', 'layout' => 'grid-4', 'headline' => 'Our Track Record', 'background' => 'white'],
                ['type' => 'features', 'purpose' => 'Show benefits', 'layout' => 'grid-3', 'headline' => 'What We Offer', 'background' => 'white'],
                ['type' => 'testimonials', 'purpose' => 'Social proof', 'layout' => 'grid-3', 'headline' => 'What Our Clients Say', 'background' => 'light'],
                ['type' => 'cta', 'purpose' => 'Convert', 'layout' => 'centered', 'headline' => 'Ready to Get Started?', 'background' => 'dark']
            ]
        ];
    }

    /**
     * Get style-specific design defaults
     */
    private static function getStyleDefaults(string $style): array
    {
        $styles = [
            'modern' => [
                'typography' => [
                    'h1_size' => 56, 'h1_weight' => '700', 'h1_line_height' => '1.1',
                    'h2_size' => 42, 'h2_weight' => '700', 'h2_line_height' => '1.2',
                    'h3_size' => 24, 'h3_weight' => '600',
                    'body_size' => 18, 'body_weight' => '400', 'body_line_height' => '1.7'
                ],
                'spacing' => [
                    'section_padding' => 100, 'hero_padding' => 120,
                    'element_gap' => 24, 'card_padding' => 32
                ],
                'borders' => [
                    'radius_small' => 8, 'radius_medium' => 12, 'radius_large' => 16
                ],
                'shadows' => [
                    'card' => '0 4px 20px rgba(0,0,0,0.08)',
                    'elevated' => '0 10px 40px rgba(0,0,0,0.12)',
                    'hover' => '0 20px 40px rgba(0,0,0,0.15)'
                ]
            ],
            'minimal' => [
                'typography' => [
                    'h1_size' => 48, 'h1_weight' => '600', 'h1_line_height' => '1.2',
                    'h2_size' => 36, 'h2_weight' => '600', 'h2_line_height' => '1.25',
                    'h3_size' => 22, 'h3_weight' => '500',
                    'body_size' => 17, 'body_weight' => '400', 'body_line_height' => '1.8'
                ],
                'spacing' => [
                    'section_padding' => 120, 'hero_padding' => 140,
                    'element_gap' => 20, 'card_padding' => 28
                ],
                'borders' => [
                    'radius_small' => 4, 'radius_medium' => 6, 'radius_large' => 8
                ],
                'shadows' => [
                    'card' => '0 1px 3px rgba(0,0,0,0.04)',
                    'elevated' => '0 4px 12px rgba(0,0,0,0.06)',
                    'hover' => '0 8px 24px rgba(0,0,0,0.08)'
                ]
            ],
            'bold' => [
                'typography' => [
                    'h1_size' => 68, 'h1_weight' => '800', 'h1_line_height' => '1.05',
                    'h2_size' => 48, 'h2_weight' => '700', 'h2_line_height' => '1.15',
                    'h3_size' => 28, 'h3_weight' => '700',
                    'body_size' => 18, 'body_weight' => '400', 'body_line_height' => '1.6'
                ],
                'spacing' => [
                    'section_padding' => 80, 'hero_padding' => 100,
                    'element_gap' => 28, 'card_padding' => 36
                ],
                'borders' => [
                    'radius_small' => 8, 'radius_medium' => 12, 'radius_large' => 16
                ],
                'shadows' => [
                    'card' => '0 8px 30px rgba(0,0,0,0.12)',
                    'elevated' => '0 16px 50px rgba(0,0,0,0.18)',
                    'hover' => '0 24px 60px rgba(0,0,0,0.22)'
                ]
            ],
            'elegant' => [
                'typography' => [
                    'h1_size' => 52, 'h1_weight' => '500', 'h1_line_height' => '1.15',
                    'h2_size' => 40, 'h2_weight' => '500', 'h2_line_height' => '1.2',
                    'h3_size' => 24, 'h3_weight' => '500',
                    'body_size' => 17, 'body_weight' => '400', 'body_line_height' => '1.75'
                ],
                'spacing' => [
                    'section_padding' => 110, 'hero_padding' => 130,
                    'element_gap' => 24, 'card_padding' => 32
                ],
                'borders' => [
                    'radius_small' => 16, 'radius_medium' => 20, 'radius_large' => 24
                ],
                'shadows' => [
                    'card' => '0 4px 16px rgba(0,0,0,0.06)',
                    'elevated' => '0 8px 32px rgba(0,0,0,0.1)',
                    'hover' => '0 12px 40px rgba(0,0,0,0.12)'
                ]
            ]
        ];

        return $styles[$style] ?? $styles['modern'];
    }

    // REMOVED: Local getIndustryColors() - use JTB_AI_Styles::getIndustryColors() as single source of truth

    /**
     * Extract business keywords from prompt
     */
    private static function extractBusinessKeywords(string $prompt): string
    {
        // Extract meaningful words
        $stopWords = ['a', 'an', 'the', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'shall', 'can', 'need', 'dare', 'ought', 'used', 'to', 'of', 'in', 'for', 'on', 'with', 'at', 'by', 'from', 'as', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'between', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'and', 'but', 'if', 'or', 'because', 'until', 'while', 'although', 'i', 'me', 'my', 'we', 'our', 'you', 'your', 'he', 'she', 'it', 'they', 'them', 'what', 'which', 'who', 'this', 'that', 'these', 'those', 'am', 'page', 'website', 'site', 'landing', 'create', 'make', 'build', 'want', 'need', 'company', 'business'];

        $words = preg_split('/\s+/', strtolower($prompt));
        $keywords = [];

        foreach ($words as $word) {
            $word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return implode(' ', array_slice(array_unique($keywords), 0, 5));
    }

    /**
     * Calculate elapsed time in milliseconds
     */
    private static function elapsed(float $startTime): int
    {
        return (int)((microtime(true) - $startTime) * 1000);
    }

    /**
     * Log debug message
     */
    private static function log(string $message): void
    {
        $logFile = '/tmp/jtb_ai_pipeline.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }

    // ========================================
    // Section Generation (for single sections)
    // ========================================

    /**
     * Generate single section from prompt
     */
    public static function generateSection(string $sectionType, string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        $ai = JTB_AI_Core::getInstance();

        if (!$ai->isConfigured()) {
            return [
                'ok' => false,
                'section' => null,
                'error' => 'AI is not configured'
            ];
        }

        // Create mini-outline for this section
        $outline = [
            'page_title' => $prompt,
            'color_scheme' => [
                'primary' => '#2563eb',
                'secondary' => '#3b82f6',
                'dark' => '#1e3a8a',
                'light_bg' => '#f8fafc',
                'text' => '#111827'
            ],
            'sections' => [
                ['type' => $sectionType, 'layout' => 'centered', 'headline' => $prompt]
            ]
        ];

        // Generate wireframe for single section
        $idCounter = 1;
        $row = self::generateRowForSectionType($sectionType, 'centered', $idCounter);

        $wireframe = [
            'sections' => [
                [
                    'type' => 'section',
                    'id' => 'section_1',
                    'section_type' => $sectionType,
                    'children' => [$row]
                ]
            ]
        ];

        // Apply styles
        $styled = self::step3_applyStyles($wireframe, $outline);

        // Apply placeholder content
        $withContent = self::step4_applyPlaceholderContent($styled, $outline);

        // Extract the section
        $section = $withContent['sections'][0] ?? null;

        if (!$section) {
            return [
                'ok' => false,
                'section' => null,
                'error' => 'Failed to generate section'
            ];
        }

        return [
            'ok' => true,
            'section' => $section,
            'error' => null,
            'stats' => [
                'time_ms' => self::elapsed($startTime)
            ]
        ];
    }
}
