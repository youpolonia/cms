<?php
/**
 * JTB AI Agent: Architect
 *
 * Converts accepted mockup HTML into JTB JSON structure.
 * Responsibilities:
 * - Parse mockup HTML → extract structure
 * - Map to JTB modules (from Registry - ZERO hardcodes)
 * - Generate skeleton JTB JSON with IDs
 * - Build path_map for content/style agents
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 * @updated 2026-02-05
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Agent_Architect
{
    /**
     * Execute architect agent - alias for generate()
     *
     * @param array $session Session data with mockup_html and structure
     * @return array Result with structure data
     */
    public static function execute(array $session): array
    {
        return self::generate($session);
    }

    /**
     * Generate JTB structure from accepted mockup
     *
     * @param array $session Session data with mockup_html and structure
     * @return array Result with skeleton, path_map, and color_scheme
     */
    public static function generate(array $session): array
    {
        $startTime = microtime(true);

        try {
            // Validate session has required data
            if (empty($session['mockup_html']) || empty($session['structure'])) {
                return [
                    'ok' => false,
                    'error' => 'Missing mockup_html or structure in session'
                ];
            }

            $mockupHtml = $session['mockup_html'];
            $structure = $session['structure'];
            $industry = $session['industry'] ?? 'technology';
            $style = $session['style'] ?? 'modern';
            $pages = $session['pages'] ?? ['home'];

            // Step 1: Extract color scheme from mockup
            $colorScheme = self::extractColorScheme($mockupHtml, $style);

            // Step 2: Parse structure and expand to skeleton
            $skeleton = self::expandToSkeleton($structure, $pages, $industry);

            // Step 3: Generate unique IDs for all elements
            $skeleton = self::assignIds($skeleton);

            // Step 4: Build path_map
            $pathMap = self::buildPathMap($skeleton);

            // Step 5: Extract content hints from mockup HTML
            $contentHints = self::extractContentHints($mockupHtml);

            $duration = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'skeleton' => $skeleton,
                'path_map' => $pathMap,
                'color_scheme' => $colorScheme,
                'content_hints' => $contentHints,
                'stats' => [
                    'time_ms' => $duration,
                    'pages_count' => count($pages),
                    'sections_count' => self::countSections($skeleton),
                    'modules_count' => count($pathMap)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract color scheme from mockup CSS/HTML
     *
     * @param string $mockupHtml Mockup HTML with inline styles
     * @param string $style Style preset name
     * @return array Color scheme
     */
    private static function extractColorScheme(string $mockupHtml, string $style): array
    {
        // Try to extract colors from mockup CSS variables or inline styles
        $colors = [
            'primary' => '#6366f1',
            'secondary' => '#3b82f6',
            'accent' => '#10b981',
            'text' => '#1e293b',
            'text_light' => '#64748b',
            'background' => null,
            'background_alt' => null,
            'heading' => '#0f172a'
        ];

        // Extract from CSS variables in mockup
        if (preg_match_all('/--(?:primary|accent|secondary)(?:-color)?:\s*(#[a-fA-F0-9]{3,8}|rgba?\([^)]+\))/i', $mockupHtml, $matches)) {
            foreach ($matches[0] as $i => $match) {
                if (stripos($match, 'primary') !== false) {
                    $colors['primary'] = $matches[1][$i];
                } elseif (stripos($match, 'secondary') !== false) {
                    $colors['secondary'] = $matches[1][$i];
                } elseif (stripos($match, 'accent') !== false) {
                    $colors['accent'] = $matches[1][$i];
                }
            }
        }

        // Apply style preset defaults if not extracted
        $styleColors = self::getStyleColors($style);
        foreach ($styleColors as $key => $value) {
            if (!isset($colors[$key]) || $colors[$key] === '#6366f1') {
                $colors[$key] = $value;
            }
        }

        return $colors;
    }

    /**
     * Get default colors for style preset
     *
     * @param string $style Style name
     * @return array Colors
     */
    private static function getStyleColors(string $style): array
    {
        $presets = [
            'modern' => [
                'primary' => '#6366f1',
                'secondary' => '#3b82f6',
                'accent' => '#10b981'
            ],
            'minimal' => [
                'primary' => '#18181b',
                'secondary' => '#52525b',
                'accent' => '#3b82f6'
            ],
            'bold' => [
                'primary' => '#dc2626',
                'secondary' => '#f59e0b',
                'accent' => '#10b981'
            ],
            'elegant' => [
                'primary' => '#1e293b',
                'secondary' => '#854d0e',
                'accent' => '#d4af37'
            ],
            'playful' => [
                'primary' => '#ec4899',
                'secondary' => '#8b5cf6',
                'accent' => '#06b6d4'
            ],
            'corporate' => [
                'primary' => '#1e40af',
                'secondary' => '#1e3a8a',
                'accent' => '#059669'
            ],
            'dark' => [
                'primary' => '#8b5cf6',
                'secondary' => '#6366f1',
                'accent' => '#22d3ee',
                'background' => '#0f172a',
                'background_alt' => '#1e293b',
                'text' => '#f8fafc',
                'text_light' => '#cbd5e1'
            ]
        ];

        return $presets[$style] ?? $presets['modern'];
    }

    /**
     * Expand structure.json to full JTB skeleton
     *
     * @param array $structure Structure from mockup agent
     * @param array $pages Pages to generate
     * @return array Full JTB skeleton
     */
    private static function expandToSkeleton(array $structure, array $pages, string $industry = 'general'): array
    {
        $skeleton = [
            'header' => null,
            'footer' => null,
            'pages' => []
        ];

        // Check if structure already has parsed JTB format (from HTMLToJTBParser)
        if (isset($structure['header']['sections'])) {
            // Already in JTB format - ensure _pattern exists
            $headerSections = $structure['header']['sections'];
            foreach ($headerSections as $idx => &$section) {
                if (!isset($section['attrs']['_pattern'])) {
                    $section['attrs']['_pattern'] = 'header';
                }
            }
            unset($section);
            $skeleton['header'] = ['sections' => $headerSections];
        } elseif (!empty($structure['header'])) {
            // Old format with modules array
            $skeleton['header'] = self::expandHeaderFooter($structure['header'], 'header');
        } else {
            // Generate default header
            $skeleton['header'] = self::expandHeaderFooter([
                'modules' => ['site_logo', 'menu', 'button'],
                'layout' => '1_4,1_2,1_4'
            ], 'header');
        }

        // Check footer
        if (isset($structure['footer']['sections'])) {
            // Ensure _pattern exists
            $footerSections = $structure['footer']['sections'];
            foreach ($footerSections as $idx => &$section) {
                if (!isset($section['attrs']['_pattern'])) {
                    $section['attrs']['_pattern'] = 'footer';
                }
            }
            unset($section);
            $skeleton['footer'] = ['sections' => $footerSections];
        } elseif (!empty($structure['footer'])) {
            $skeleton['footer'] = self::expandHeaderFooter($structure['footer'], 'footer');
        } else {
            $skeleton['footer'] = self::expandHeaderFooter([
                'modules' => ['site_logo', 'menu', 'social_icons', 'text'],
                'layout' => '1_4,1_4,1_4,1_4'
            ], 'footer');
        }

        // Check pages - use parsed if available
        $pagesStructure = $structure['pages'] ?? [];
        foreach ($pages as $page) {
            if (isset($pagesStructure[$page]['sections'])) {
                // Already in JTB format - ensure sections have _pattern for styling
                $pageSections = $pagesStructure[$page]['sections'];
                foreach ($pageSections as $idx => &$section) {
                    if (!isset($section['attrs']['_pattern'])) {
                        // Try to infer pattern from section content or use generic name
                        $pattern = self::inferSectionPattern($section, $idx, $page);
                        $section['attrs']['_pattern'] = $pattern;
                    }
                }
                unset($section);
                $skeleton['pages'][$page] = ['sections' => $pageSections];
            } else {
                $pageStructure = $pagesStructure[$page] ?? self::getDefaultPageStructure($page, $industry);
                $skeleton['pages'][$page] = self::expandPage($pageStructure, $page);
            }
        }

        return $skeleton;
    }

    private static function expandHeaderFooter(array $structure, string $type): array
    {
        $modules = $structure['modules'] ?? [];
        $layout = $structure['layout'] ?? ($type === 'header' ? '1_4,1_2,1_4' : '1_4,1_4,1_4,1_4');

        // Create section with row and columns
        $columns = self::parseLayout($layout);
        $columnElements = [];

        $moduleIndex = 0;
        foreach ($columns as $width) {
            $columnModules = [];

            // Distribute modules to columns
            if ($moduleIndex < count($modules)) {
                $module = $modules[$moduleIndex];
                $moduleType = is_array($module) ? ($module['type'] ?? 'text') : $module;
                $columnModules[] = self::createModuleElement($moduleType, $module);
                $moduleIndex++;
            }

            $columnElements[] = [
                'type' => 'column',
                'attrs' => ['width' => $width],
                'children' => $columnModules
            ];
        }

        return [
            'sections' => [
                [
                    'type' => 'section',
                    'attrs' => [
                        '_pattern' => $type,
                        'padding' => ['top' => 20, 'right' => 0, 'bottom' => 20, 'left' => 0],
                        'fullwidth' => true
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'attrs' => ['columns' => $layout],
                            'children' => $columnElements
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Expand page structure to JTB format
     *
     * @param array $pageStructure Page structure from mockup
     * @param string $pageName Page name
     * @return array JTB page structure
     */
    private static function expandPage(array $pageStructure, string $pageName): array
    {
        $sections = $pageStructure['sections'] ?? [];
        $jtbSections = [];

        foreach ($sections as $sectionData) {
            $blueprint = $sectionData['blueprint'] ?? 'content';
            $layout = $sectionData['layout'] ?? '1';
            $modules = $sectionData['modules'] ?? [];
            $sectionAttrs = $sectionData['attrs'] ?? [];

            // Parse layout to columns
            $columns = self::parseLayout($layout);
            $columnElements = [];

            $moduleIndex = 0;
            foreach ($columns as $colIndex => $width) {
                $columnModules = [];

                // Get modules for this column
                $colModules = [];
                foreach ($modules as $module) {
                    if (is_array($module)) {
                        $moduleCol = $module['column'] ?? 0;
                        if ($moduleCol === $colIndex) {
                            $colModules[] = $module;
                        }
                    } else {
                        // Simple module assignment
                        if ($moduleIndex < count($modules)) {
                            $colModules[] = $modules[$moduleIndex];
                            $moduleIndex++;
                        }
                    }
                }

                // If no column assignment, distribute sequentially
                if (empty($colModules) && $moduleIndex < count($modules)) {
                    $modulesPerCol = ceil(count($modules) / count($columns));
                    for ($i = 0; $i < $modulesPerCol && $moduleIndex < count($modules); $i++) {
                        $colModules[] = $modules[$moduleIndex];
                        $moduleIndex++;
                    }
                }

                foreach ($colModules as $module) {
                    $moduleType = is_array($module) ? ($module['type'] ?? 'text') : $module;
                    $columnModules[] = self::createModuleElement($moduleType, $module);
                }

                $columnElements[] = [
                    'type' => 'column',
                    'attrs' => ['width' => $width],
                    'children' => $columnModules
                ];
            }

            // Create section
            $jtbSections[] = [
                'type' => 'section',
                'attrs' => array_merge([
                    '_pattern' => $blueprint,
                    'padding' => self::getSectionPadding($blueprint),
                    'background_color' => self::getSectionBackground($blueprint)
                ], $sectionAttrs),
                'children' => [
                    [
                        'type' => 'row',
                        'attrs' => ['columns' => $layout],
                        'children' => $columnElements
                    ]
                ]
            ];
        }

        return [
            'title' => ucfirst($pageName),
            'sections' => $jtbSections
        ];
    }

    /**
     * Create module element from definition
     *
     * @param string $moduleType Module type slug
     * @param mixed $moduleDef Module definition (string or array)
     * @return array Module element
     */
    private static function createModuleElement(string $moduleType, $moduleDef): array
    {
        // Normalize slug (site-logo → site_logo)
        $moduleType = str_replace('-', '_', $moduleType);

        // Get default attrs from Registry if available
        $defaultAttrs = [];
        if (class_exists('\\JessieThemeBuilder\\JTB_Registry')) {
            $module = \JessieThemeBuilder\JTB_Registry::get($moduleType);
            if ($module) {
                $fields = $module->getFields();
                foreach ($fields as $fieldName => $fieldDef) {
                    if (isset($fieldDef['default'])) {
                        $defaultAttrs[$fieldName] = $fieldDef['default'];
                    }
                }
            }
        }

        // Merge with provided attrs
        $attrs = $defaultAttrs;
        if (is_array($moduleDef) && isset($moduleDef['attrs'])) {
            $attrs = array_merge($attrs, $moduleDef['attrs']);
        }

        // Add role if defined
        if (is_array($moduleDef) && isset($moduleDef['role'])) {
            $attrs['_role'] = $moduleDef['role'];
        }

        return [
            'type' => $moduleType,
            'attrs' => $attrs
        ];
    }

    /**
     * Parse layout string to column widths
     *
     * @param string $layout Layout string (e.g., "1_2,1_2" or "1_3,2_3")
     * @return array Column widths
     */
    private static function parseLayout(string $layout): array
    {
        $parts = explode(',', $layout);
        $columns = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '_') !== false) {
                // Fraction format: 1_2, 1_3, 2_3, etc.
                list($num, $den) = explode('_', $part);
                $columns[] = $part;
            } elseif (is_numeric($part)) {
                // Single column full width
                $columns[] = '1';
            } else {
                $columns[] = '1';
            }
        }

        return $columns ?: ['1'];
    }

    /**
     * Get default section padding by blueprint
     *
     * @param string $blueprint Section blueprint
     * @return array Padding values
     */
    private static function getSectionPadding(string $blueprint): array
    {
        $paddings = [
            'hero' => ['top' => 120, 'right' => 0, 'bottom' => 120, 'left' => 0],
            'features' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'testimonials' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'pricing' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'cta' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0],
            'faq' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'contact' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'team' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'about' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'stats' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0],
            'trust_logos' => ['top' => 40, 'right' => 0, 'bottom' => 40, 'left' => 0],
            'header' => ['top' => 20, 'right' => 0, 'bottom' => 20, 'left' => 0],
            'footer' => ['top' => 60, 'right' => 0, 'bottom' => 30, 'left' => 0]
        ];

        return $paddings[$blueprint] ?? ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0];
    }

    /**
     * Get section background color by blueprint
     *
     * @param string $blueprint Section blueprint
     * @return string|null Background color or null for transparent
     */
    private static function getSectionBackground(string $blueprint): ?string
    {
        // Sections with alternate backgrounds
        $altBackgrounds = ['features', 'testimonials', 'faq', 'stats', 'trust_logos'];

        // All sections should use transparent background
        // Actual colors will be applied by Stylist agent based on theme
        return null;
    }

    /**
     * Assign unique IDs to all elements
     *
     * @param array $skeleton JTB skeleton
     * @return array Skeleton with IDs
     */
    private static function assignIds(array $skeleton): array
    {
        $idCounter = 1000;

        // Process header
        if (!empty($skeleton['header']['sections'])) {
            $skeleton['header']['sections'] = self::assignIdsToSections(
                $skeleton['header']['sections'],
                'hdr',
                $idCounter
            );
        }

        // Process footer
        if (!empty($skeleton['footer']['sections'])) {
            $skeleton['footer']['sections'] = self::assignIdsToSections(
                $skeleton['footer']['sections'],
                'ftr',
                $idCounter
            );
        }

        // Process pages
        foreach ($skeleton['pages'] as $pageName => &$pageData) {
            $prefix = substr($pageName, 0, 3);
            if (!empty($pageData['sections'])) {
                $pageData['sections'] = self::assignIdsToSections(
                    $pageData['sections'],
                    $prefix,
                    $idCounter
                );
            }
        }

        return $skeleton;
    }

    /**
     * Assign IDs to sections recursively
     *
     * @param array $sections Sections array
     * @param string $prefix ID prefix
     * @param int &$counter Counter reference
     * @return array Sections with IDs
     */
    private static function assignIdsToSections(array $sections, string $prefix, int &$counter): array
    {
        foreach ($sections as &$section) {
            $section['id'] = $prefix . '_s_' . $counter++;

            if (!empty($section['children'])) {
                foreach ($section['children'] as &$row) {
                    $row['id'] = $prefix . '_r_' . $counter++;

                    if (!empty($row['children'])) {
                        foreach ($row['children'] as &$column) {
                            $column['id'] = $prefix . '_c_' . $counter++;

                            if (!empty($column['children'])) {
                                foreach ($column['children'] as &$module) {
                                    $module['id'] = $prefix . '_m_' . $counter++;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $sections;
    }

    /**
     * Build path_map from skeleton
     *
     * Path format: "{page}/{section_blueprint}/col{col_index}/{module_type}_{index}"
     *
     * @param array $skeleton JTB skeleton
     * @return array Path map [path => id]
     */
    private static function buildPathMap(array $skeleton): array
    {
        $pathMap = [];

        // Map header
        if (!empty($skeleton['header']['sections'])) {
            self::buildPathMapForSections(
                $skeleton['header']['sections'],
                'header',
                $pathMap
            );
        }

        // Map footer
        if (!empty($skeleton['footer']['sections'])) {
            self::buildPathMapForSections(
                $skeleton['footer']['sections'],
                'footer',
                $pathMap
            );
        }

        // Map pages
        foreach ($skeleton['pages'] as $pageName => $pageData) {
            if (!empty($pageData['sections'])) {
                self::buildPathMapForSections(
                    $pageData['sections'],
                    $pageName,
                    $pathMap
                );
            }
        }

        return $pathMap;
    }

    /**
     * Build path map for sections
     *
     * @param array $sections Sections array
     * @param string $pagePrefix Page/section prefix
     * @param array &$pathMap Path map reference
     */
    private static function buildPathMapForSections(array $sections, string $pagePrefix, array &$pathMap): void
    {
        foreach ($sections as $sectionIndex => $section) {
            $blueprint = $section['attrs']['_pattern'] ?? 'section_' . $sectionIndex;
            $sectionPath = "{$pagePrefix}/{$blueprint}";

            // Add section to map
            $pathMap[$sectionPath] = $section['id'];

            if (!empty($section['children'])) {
                foreach ($section['children'] as $rowIndex => $row) {
                    if (!empty($row['children'])) {
                        foreach ($row['children'] as $colIndex => $column) {
                            $colPath = "{$sectionPath}/col{$colIndex}";

                            // Track module types for indexing
                            $moduleTypeCounts = [];

                            if (!empty($column['children'])) {
                                foreach ($column['children'] as $moduleIndex => $module) {
                                    $moduleType = $module['type'] ?? 'unknown';

                                    // Increment count for this type
                                    if (!isset($moduleTypeCounts[$moduleType])) {
                                        $moduleTypeCounts[$moduleType] = 0;
                                    }
                                    $typeIndex = $moduleTypeCounts[$moduleType]++;

                                    $modulePath = "{$colPath}/{$moduleType}_{$typeIndex}";
                                    $pathMap[$modulePath] = $module['id'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Extract content hints from mockup HTML
     *
     * @param string $mockupHtml Mockup HTML
     * @return array Content hints [path_hint => content]
     */
    private static function extractContentHints(string $mockupHtml): array
    {
        $hints = [];

        // Extract content from data-content attributes
        if (preg_match_all('/data-content="([^"]+)"/i', $mockupHtml, $matches)) {
            foreach ($matches[1] as $content) {
                $hints[] = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
            }
        }

        // Extract headings
        if (preg_match_all('/<h[1-6][^>]*>([^<]+)<\/h[1-6]>/i', $mockupHtml, $matches)) {
            $hints['headings'] = $matches[1];
        }

        // Extract paragraphs
        if (preg_match_all('/<p[^>]*>([^<]+)<\/p>/i', $mockupHtml, $matches)) {
            $hints['paragraphs'] = $matches[1];
        }

        // Extract buttons
        if (preg_match_all('/<button[^>]*>([^<]+)<\/button>/i', $mockupHtml, $matches)) {
            $hints['buttons'] = $matches[1];
        }

        return $hints;
    }

    /**
     * Get default page structure for common pages
     *
     * @param string $pageName Page name
     * @return array Default page structure
     */
    /**
     * Get default page structure for common pages
     * Now industry-aware for different layouts
     *
     * @param string $pageName Page name
     * @param string $industry Industry type
     * @return array Default page structure
     */
    private static function getDefaultPageStructure(string $pageName, string $industry = 'general'): array
    {
        // Get industry-specific home page structure
        if ($pageName === 'home') {
            return self::getIndustryHomeStructure($industry);
        }

        // Other pages use standard structures
        $defaults = [
            'about' => [
                'sections' => [
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 0],
                        ['type' => 'image', 'role' => 'about_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'team', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'team_member', 'column' => 0],
                        ['type' => 'team_member', 'column' => 1],
                        ['type' => 'team_member', 'column' => 2]
                    ]]
                ]
            ],
            'services' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title'],
                        ['type' => 'text', 'role' => 'page_intro']
                    ]],
                    ['blueprint' => 'services', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'service', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 2]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'cta_button']
                    ]]
                ]
            ],
            'contact' => [
                'sections' => [
                    ['blueprint' => 'contact', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'contact_info', 'column' => 0],
                        ['type' => 'contact_form', 'role' => 'form', 'column' => 1]
                    ]],
                    ['blueprint' => 'map', 'layout' => '1', 'modules' => [
                        ['type' => 'map', 'role' => 'location']
                    ]]
                ]
            ],
            'pricing' => [
                'sections' => [
                    ['blueprint' => 'pricing', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'heading', 'role' => 'section_title'],
                        ['type' => 'pricing_table', 'column' => 0],
                        ['type' => 'pricing_table', 'column' => 1],
                        ['type' => 'pricing_table', 'column' => 2]
                    ]],
                    ['blueprint' => 'faq', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'faq_title'],
                        ['type' => 'accordion', 'role' => 'faq_items']
                    ]]
                ]
            ],
            'faq' => [
                'sections' => [
                    ['blueprint' => 'faq', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title'],
                        ['type' => 'text', 'role' => 'page_intro'],
                        ['type' => 'accordion', 'role' => 'faq_items']
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'cta_button']
                    ]]
                ]
            ],
            'blog' => [
                'sections' => [
                    ['blueprint' => 'blog', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title'],
                        ['type' => 'blog', 'role' => 'posts_grid']
                    ]]
                ]
            ],
            'team' => [
                'sections' => [
                    ['blueprint' => 'team', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'page_title'],
                        ['type' => 'text', 'role' => 'page_intro']
                    ]],
                    ['blueprint' => 'team_grid', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'team_member', 'column' => 0],
                        ['type' => 'team_member', 'column' => 1],
                        ['type' => 'team_member', 'column' => 2],
                        ['type' => 'team_member', 'column' => 3]
                    ]]
                ]
            ]
        ];

        return $defaults[$pageName] ?? self::getIndustryHomeStructure($industry);
    }

    /**
     * Get industry-specific home page structure
     * Different industries need different section layouts
     *
     * @param string $industry Industry type
     * @return array Home page structure
     */
    private static function getIndustryHomeStructure(string $industry): array
    {
        $structures = [
            // Technology / SaaS - stats-focused, trust elements
            'technology' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'subheadline', 'column' => 0],
                        ['type' => 'button', 'role' => 'primary_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'trust_logos', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'trust_title'],
                        ['type' => 'gallery', 'role' => 'logo_strip']
                    ]],
                    ['blueprint' => 'stats', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'number_counter', 'column' => 0],
                        ['type' => 'number_counter', 'column' => 1],
                        ['type' => 'number_counter', 'column' => 2],
                        ['type' => 'number_counter', 'column' => 3]
                    ]],
                    ['blueprint' => 'features', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'feature', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'feature', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'feature', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1],
                        ['type' => 'testimonial', 'column' => 2]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'text', 'role' => 'cta_text'],
                        ['type' => 'button', 'role' => 'cta_button']
                    ]]
                ]
            ],

            // Healthcare - trust, team, services focused
            'healthcare' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'subheadline', 'column' => 0],
                        ['type' => 'button', 'role' => 'appointment_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'services', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'blurb', 'role' => 'service', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 2],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 3]
                    ]],
                    ['blueprint' => 'team', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'team_member', 'column' => 0],
                        ['type' => 'team_member', 'column' => 1],
                        ['type' => 'team_member', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1]
                    ]],
                    ['blueprint' => 'contact', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'contact_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'contact_info', 'column' => 0],
                        ['type' => 'contact_form', 'role' => 'form', 'column' => 1]
                    ]]
                ]
            ],

            // Restaurant / Food - visual, menu focused
            'restaurant' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title'],
                        ['type' => 'text', 'role' => 'tagline'],
                        ['type' => 'button', 'role' => 'reservation_cta']
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'image', 'role' => 'chef_image', 'column' => 0],
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 1],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 1]
                    ]],
                    ['blueprint' => 'menu', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'menu_title', 'column' => 0],
                        ['type' => 'pricing_table', 'role' => 'menu_items', 'column' => 0],
                        ['type' => 'pricing_table', 'role' => 'menu_items', 'column' => 1]
                    ]],
                    ['blueprint' => 'gallery', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'gallery_title'],
                        ['type' => 'gallery', 'role' => 'food_gallery']
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1],
                        ['type' => 'testimonial', 'column' => 2]
                    ]],
                    ['blueprint' => 'contact', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'map', 'role' => 'location', 'column' => 0],
                        ['type' => 'heading', 'role' => 'hours_title', 'column' => 1],
                        ['type' => 'text', 'role' => 'hours_info', 'column' => 1],
                        ['type' => 'button', 'role' => 'reservation_cta', 'column' => 1]
                    ]]
                ]
            ],

            // E-commerce / Retail - product focused
            'ecommerce' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'promo_text', 'column' => 0],
                        ['type' => 'button', 'role' => 'shop_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'promo_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'categories', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'blurb', 'role' => 'category', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'category', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'category', 'column' => 2],
                        ['type' => 'blurb', 'role' => 'category', 'column' => 3]
                    ]],
                    ['blueprint' => 'products', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'featured_title'],
                        ['type' => 'shop', 'role' => 'featured_products']
                    ]],
                    ['blueprint' => 'features', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1]
                    ]],
                    ['blueprint' => 'newsletter', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'newsletter_title'],
                        ['type' => 'text', 'role' => 'newsletter_text'],
                        ['type' => 'contact_form', 'role' => 'subscribe_form']
                    ]]
                ]
            ],

            // Real Estate - property focused
            'realestate' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title'],
                        ['type' => 'text', 'role' => 'tagline'],
                        ['type' => 'search', 'role' => 'property_search']
                    ]],
                    ['blueprint' => 'stats', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'number_counter', 'role' => 'stat', 'column' => 0],
                        ['type' => 'number_counter', 'role' => 'stat', 'column' => 1],
                        ['type' => 'number_counter', 'role' => 'stat', 'column' => 2]
                    ]],
                    ['blueprint' => 'properties', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'heading', 'role' => 'featured_title'],
                        ['type' => 'blurb', 'role' => 'property', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'property', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'property', 'column' => 2]
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'image', 'role' => 'agent_image', 'column' => 0],
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 1],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 1],
                        ['type' => 'button', 'role' => 'contact_cta', 'column' => 1]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'cta_button']
                    ]]
                ]
            ],

            // Legal / Professional Services
            'legal' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'tagline', 'column' => 0],
                        ['type' => 'button', 'role' => 'consultation_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'services', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'practice_area', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'practice_area', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'practice_area', 'column' => 2]
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 0],
                        ['type' => 'image', 'role' => 'office_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'team', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'team_member', 'role' => 'attorney', 'column' => 0],
                        ['type' => 'team_member', 'role' => 'attorney', 'column' => 1],
                        ['type' => 'team_member', 'role' => 'attorney', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'testimonial_title'],
                        ['type' => 'slider', 'role' => 'testimonial_slider']
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'text', 'role' => 'cta_text'],
                        ['type' => 'button', 'role' => 'consultation_cta']
                    ]]
                ]
            ],

            // Education
            'education' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'tagline', 'column' => 0],
                        ['type' => 'button', 'role' => 'enroll_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'stats', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'number_counter', 'role' => 'students', 'column' => 0],
                        ['type' => 'number_counter', 'role' => 'courses', 'column' => 1],
                        ['type' => 'number_counter', 'role' => 'instructors', 'column' => 2],
                        ['type' => 'number_counter', 'role' => 'success_rate', 'column' => 3]
                    ]],
                    ['blueprint' => 'courses', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'heading', 'role' => 'courses_title'],
                        ['type' => 'blurb', 'role' => 'course', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'course', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'course', 'column' => 2]
                    ]],
                    ['blueprint' => 'features', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'benefit', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'testimonial', 'column' => 0],
                        ['type' => 'testimonial', 'column' => 1]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'enroll_cta']
                    ]]
                ]
            ],

            // Fitness / Wellness
            'fitness' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title'],
                        ['type' => 'text', 'role' => 'motivation_text'],
                        ['type' => 'button', 'role' => 'join_cta']
                    ]],
                    ['blueprint' => 'features', 'layout' => '1_4,1_4,1_4,1_4', 'modules' => [
                        ['type' => 'blurb', 'role' => 'class', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'class', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'class', 'column' => 2],
                        ['type' => 'blurb', 'role' => 'class', 'column' => 3]
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'image', 'role' => 'gym_image', 'column' => 0],
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 1],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 1]
                    ]],
                    ['blueprint' => 'team', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'team_member', 'role' => 'trainer', 'column' => 0],
                        ['type' => 'team_member', 'role' => 'trainer', 'column' => 1],
                        ['type' => 'team_member', 'role' => 'trainer', 'column' => 2]
                    ]],
                    ['blueprint' => 'pricing', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'pricing_table', 'column' => 0],
                        ['type' => 'pricing_table', 'column' => 1],
                        ['type' => 'pricing_table', 'column' => 2]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'trial_cta']
                    ]]
                ]
            ],

            // Creative / Agency
            'agency' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title'],
                        ['type' => 'text', 'role' => 'tagline']
                    ]],
                    ['blueprint' => 'portfolio', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'work_title'],
                        ['type' => 'portfolio', 'role' => 'featured_work']
                    ]],
                    ['blueprint' => 'services', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'service', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'service', 'column' => 2]
                    ]],
                    ['blueprint' => 'trust_logos', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'clients_title'],
                        ['type' => 'gallery', 'role' => 'client_logos']
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 0],
                        ['type' => 'image', 'role' => 'team_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'button', 'role' => 'contact_cta']
                    ]]
                ]
            ],

            // Non-profit / Charity
            'nonprofit' => [
                'sections' => [
                    ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                        ['type' => 'text', 'role' => 'mission_text', 'column' => 0],
                        ['type' => 'button', 'role' => 'donate_cta', 'column' => 0],
                        ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                    ]],
                    ['blueprint' => 'stats', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'number_counter', 'role' => 'impact_stat', 'column' => 0],
                        ['type' => 'number_counter', 'role' => 'impact_stat', 'column' => 1],
                        ['type' => 'number_counter', 'role' => 'impact_stat', 'column' => 2]
                    ]],
                    ['blueprint' => 'about', 'layout' => '1_2,1_2', 'modules' => [
                        ['type' => 'image', 'role' => 'mission_image', 'column' => 0],
                        ['type' => 'heading', 'role' => 'about_title', 'column' => 1],
                        ['type' => 'text', 'role' => 'about_text', 'column' => 1]
                    ]],
                    ['blueprint' => 'features', 'layout' => '1_3,1_3,1_3', 'modules' => [
                        ['type' => 'blurb', 'role' => 'program', 'column' => 0],
                        ['type' => 'blurb', 'role' => 'program', 'column' => 1],
                        ['type' => 'blurb', 'role' => 'program', 'column' => 2]
                    ]],
                    ['blueprint' => 'testimonials', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'stories_title'],
                        ['type' => 'slider', 'role' => 'success_stories']
                    ]],
                    ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                        ['type' => 'heading', 'role' => 'cta_title'],
                        ['type' => 'text', 'role' => 'cta_text'],
                        ['type' => 'button', 'role' => 'donate_cta']
                    ]]
                ]
            ]
        ];

        // Default/general structure
        $default = [
            'sections' => [
                ['blueprint' => 'hero', 'layout' => '1_2,1_2', 'modules' => [
                    ['type' => 'heading', 'role' => 'h1_title', 'column' => 0],
                    ['type' => 'text', 'role' => 'subheadline', 'column' => 0],
                    ['type' => 'button', 'role' => 'primary_cta', 'column' => 0],
                    ['type' => 'image', 'role' => 'hero_image', 'column' => 1]
                ]],
                ['blueprint' => 'features', 'layout' => '1_3,1_3,1_3', 'modules' => [
                    ['type' => 'blurb', 'role' => 'feature', 'column' => 0],
                    ['type' => 'blurb', 'role' => 'feature', 'column' => 1],
                    ['type' => 'blurb', 'role' => 'feature', 'column' => 2]
                ]],
                ['blueprint' => 'testimonials', 'layout' => '1_2,1_2', 'modules' => [
                    ['type' => 'testimonial', 'column' => 0],
                    ['type' => 'testimonial', 'column' => 1]
                ]],
                ['blueprint' => 'cta', 'layout' => '1', 'modules' => [
                    ['type' => 'heading', 'role' => 'cta_title'],
                    ['type' => 'text', 'role' => 'cta_text'],
                    ['type' => 'button', 'role' => 'cta_button']
                ]]
            ]
        ];

        // Map similar industries
        $industryMap = [
            'saas' => 'technology',
            'software' => 'technology',
            'tech' => 'technology',
            'startup' => 'technology',
            'medical' => 'healthcare',
            'health' => 'healthcare',
            'clinic' => 'healthcare',
            'dental' => 'healthcare',
            'food' => 'restaurant',
            'cafe' => 'restaurant',
            'bar' => 'restaurant',
            'retail' => 'ecommerce',
            'shop' => 'ecommerce',
            'store' => 'ecommerce',
            'property' => 'realestate',
            'realtor' => 'realestate',
            'law' => 'legal',
            'attorney' => 'legal',
            'consulting' => 'legal',
            'finance' => 'legal',
            'accounting' => 'legal',
            'school' => 'education',
            'university' => 'education',
            'training' => 'education',
            'courses' => 'education',
            'gym' => 'fitness',
            'yoga' => 'fitness',
            'wellness' => 'fitness',
            'spa' => 'fitness',
            'creative' => 'agency',
            'design' => 'agency',
            'marketing' => 'agency',
            'digital' => 'agency',
            'charity' => 'nonprofit',
            'ngo' => 'nonprofit',
            'foundation' => 'nonprofit'
        ];

        $normalizedIndustry = strtolower($industry);
        $mappedIndustry = $industryMap[$normalizedIndustry] ?? $normalizedIndustry;

        return $structures[$mappedIndustry] ?? $default;
    }
    private static function countSections(array $skeleton): int
    {
        $count = 0;

        if (!empty($skeleton['header']['sections'])) {
            $count += count($skeleton['header']['sections']);
        }

        if (!empty($skeleton['footer']['sections'])) {
            $count += count($skeleton['footer']['sections']);
        }

        foreach ($skeleton['pages'] as $pageData) {
            if (!empty($pageData['sections'])) {
                $count += count($pageData['sections']);
            }
        }

        return $count;
    }

    /**
     * Infer section pattern from content (for parsed sections without _pattern)
     *
     * @param array $section Section data
     * @param int $index Section index
     * @param string $page Page name
     * @return string Inferred pattern name
     */
    private static function inferSectionPattern(array $section, int $index, string $page): string
    {
        // Check for common patterns based on modules present
        $moduleTypes = [];
        self::collectModuleTypes($section, $moduleTypes);
        
        // Hero detection: first section with heading + button/text
        if ($index === 0 && in_array('heading', $moduleTypes) && 
            (in_array('button', $moduleTypes) || in_array('text', $moduleTypes))) {
            return 'hero';
        }
        
        // Stats detection: multiple number_counter modules
        $counterCount = array_count_values($moduleTypes)['number_counter'] ?? 0;
        if ($counterCount >= 3) {
            return 'stats';
        }
        
        // Features detection: multiple blurbs
        $blurbCount = array_count_values($moduleTypes)['blurb'] ?? 0;
        if ($blurbCount >= 3) {
            return 'features';
        }
        
        // Testimonials detection
        if (in_array('testimonial', $moduleTypes)) {
            return 'testimonials';
        }
        
        // Pricing detection
        if (in_array('pricing_table', $moduleTypes)) {
            return 'pricing';
        }
        
        // Team detection
        if (in_array('team_member', $moduleTypes)) {
            return 'team';
        }
        
        // Contact/Form detection
        if (in_array('contact_form', $moduleTypes)) {
            return 'contact';
        }
        
        // CTA detection: heading + button, usually towards end
        if (in_array('heading', $moduleTypes) && in_array('button', $moduleTypes) && $index > 2) {
            return 'cta';
        }
        
        // Gallery
        if (in_array('gallery', $moduleTypes)) {
            return 'gallery';
        }
        
        // Generic content section
        return "section_{$index}";
    }
    
    /**
     * Recursively collect module types from a section
     */
    private static function collectModuleTypes(array $element, array &$types): void
    {
        if (!empty($element['type']) && !in_array($element['type'], ['section', 'row', 'column'])) {
            $types[] = $element['type'];
        }
        
        foreach ($element['children'] ?? [] as $child) {
            self::collectModuleTypes($child, $types);
        }
    }
}