<?php
/**
 * Enhanced HTML to JTB Structure Parser
 * Parses mockup HTML and extracts full structure with content and styles
 * 
 * @updated 2026-02-08: Integrated CSS Extractor for style preservation
 */

namespace JessieThemeBuilder;

class HTMLToJTBParser
{
    /**
     * CSS Extractor instance for style extraction
     * @var JTB_CSS_Extractor|null
     */
    private static ?JTB_CSS_Extractor $cssExtractor = null;

    /**
     * Parse full HTML document to JTB structure
     */
    public static function parse(string $html): array
    {
        // Initialize CSS Extractor and extract all styles
        self::$cssExtractor = new JTB_CSS_Extractor();
        self::$cssExtractor->extract($html);

        $result = [
            'header' => null,
            'footer' => null,
            'pages' => [
                'home' => ['sections' => []]
            ],
            'colors' => self::extractColors($html),
            'fonts' => self::extractFonts($html),
            'css_variables' => self::$cssExtractor->getVariables()
        ];

        // Use DOMDocument to parse HTML
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find header
        $headers = $xpath->query('//header');
        if ($headers->length > 0) {
            $result['header'] = self::parseRegion($headers->item(0), $xpath, 'header');
        }

        // Find footer
        $footers = $xpath->query('//footer');
        if ($footers->length > 0) {
            $result['footer'] = self::parseRegion($footers->item(0), $xpath, 'footer');
        }

        // Find main content sections
        $sections = $xpath->query('//main//section | //body/section | //div[contains(@class,"section")]');
        
        // If no sections found, try to find any major content divs
        if ($sections->length === 0) {
            $sections = $xpath->query('//main/div | //body/div[not(self::header) and not(self::footer)]');
        }

        foreach ($sections as $section) {
            $parsed = self::parseSection($section, $xpath);
            if ($parsed) {
                $result['pages']['home']['sections'][] = $parsed;
            }
        }

        return $result;
    }

    /**
     * Parse header/footer region
     * FIXED 2026-02-07: Better detection of logo, menu, buttons in AI-generated headers
     */
    private static function parseRegion(\DOMNode $node, \DOMXPath $xpath, string $type): array
    {
        $section = [
            'type' => 'section',
            'attrs' => [
                '_pattern' => $type,
                'fullwidth' => true,
                'padding' => ['top' => 20, 'bottom' => 20]
            ],
            'children' => []
        ];

        // Extract styles from CSS for header/footer
        if (self::$cssExtractor) {
            $cssAttrs = self::$cssExtractor->mapToSectionAttrs(
                self::$cssExtractor->getRulesFor($type)
            );
            $section['attrs'] = array_merge($section['attrs'], $cssAttrs);
        }

        // Get background from style
        $style = self::getInlineStyle($node);
        if (!empty($style['background-color'])) {
            $section['attrs']['background_color'] = $style['background-color'];
        }

        // Build row with modules
        $row = [
            'type' => 'row',
            'attrs' => ['columns' => '1_4,1_2,1_4'],
            'children' => []
        ];

        // Find logo
        $logoModule = self::findLogo($node, $xpath);
        $logoColumn = [
            'type' => 'column',
            'attrs' => ['width' => '1_4'],
            'children' => $logoModule ? [$logoModule] : []
        ];

        // Find menu/navigation
        $menuModule = self::findMenu($node, $xpath);
        $menuColumn = [
            'type' => 'column',
            'attrs' => ['width' => '1_2'],
            'children' => $menuModule ? [$menuModule] : []
        ];

        // Find CTA button
        $buttons = self::findButtons($node, $xpath);
        $ctaColumn = [
            'type' => 'column',
            'attrs' => ['width' => '1_4'],
            'children' => !empty($buttons) ? [$buttons[0]] : []
        ];

        // Only add columns that have content
        $hasContent = false;
        if (!empty($logoColumn['children'])) {
            $row['children'][] = $logoColumn;
            $hasContent = true;
        }
        if (!empty($menuColumn['children'])) {
            $row['children'][] = $menuColumn;
            $hasContent = true;
        }
        if (!empty($ctaColumn['children'])) {
            $row['children'][] = $ctaColumn;
            $hasContent = true;
        }

        // Fallback: if no specific elements found, try generic module finding
        if (!$hasContent) {
            $fallbackColumn = [
                'type' => 'column',
                'attrs' => ['width' => '1'],
                'children' => array_merge(
                    self::findHeadings($node, $xpath),
                    self::findImages($node, $xpath),
                    self::findButtons($node, $xpath),
                    self::findTexts($node, $xpath)
                )
            ];
            if (!empty($fallbackColumn['children'])) {
                $row['attrs']['columns'] = '1';
                $row['children'][] = $fallbackColumn;
                $hasContent = true;
            }
        }

        // Adjust columns attr based on actual column count
        $colCount = count($row['children']);
        if ($colCount === 1) {
            $row['attrs']['columns'] = '1';
            $row['children'][0]['attrs']['width'] = '1';
        } elseif ($colCount === 2) {
            $row['attrs']['columns'] = '1_2,1_2';
            foreach ($row['children'] as &$col) {
                $col['attrs']['width'] = '1_2';
            }
            unset($col);
        } elseif ($colCount === 3) {
            $row['attrs']['columns'] = '1_4,1_2,1_4';
        }

        if ($hasContent) {
            $section['children'][] = $row;
        }

        return ['sections' => [$section]];
    }

    /**
     * Find logo element in header
     */
    private static function findLogo(\DOMNode $node, \DOMXPath $xpath): ?array
    {
        $logoNodes = $xpath->query('.//*[contains(@class,"logo") or contains(@class,"brand") or contains(@class,"site-name")]', $node);
        
        if ($logoNodes->length > 0) {
            $logoNode = $logoNodes->item(0);
            $text = trim($logoNode->textContent);
            
            $imgs = $xpath->query('.//img', $logoNode);
            if ($imgs->length > 0) {
                $img = $imgs->item(0);
                return [
                    'type' => 'site_logo',
                    'attrs' => [
                        'logo' => $img->getAttribute('src') ?: '',
                        'logo_alt' => $img->getAttribute('alt') ?: $text,
                        'logo_url' => '/'
                    ]
                ];
            }
            
            if (!empty($text)) {
                return [
                    'type' => 'site_logo',
                    'attrs' => [
                        'logo' => '',
                        'logo_alt' => $text,
                        'logo_url' => '/',
                        'text_only' => true
                    ]
                ];
            }
        }
        
        return null;
    }

    /**
     * Find menu/navigation in header
     */
    private static function findMenu(\DOMNode $node, \DOMXPath $xpath): ?array
    {
        $navNodes = $xpath->query('.//nav | .//*[contains(@class,"nav") or contains(@class,"menu")]', $node);
        
        if ($navNodes->length > 0) {
            $navNode = $navNodes->item(0);
            $links = $xpath->query('.//a', $navNode);
            $items = [];
            
            foreach ($links as $link) {
                $text = trim($link->textContent);
                $href = $link->getAttribute('href') ?: '#';
                
                if (empty($text) || strlen($text) > 30) continue;
                
                $items[] = [
                    'title' => $text,
                    'url' => $href
                ];
            }
            
            if (!empty($items)) {
                return [
                    'type' => 'menu',
                    'attrs' => [
                        'menu_style' => 'horizontal',
                        'menu_items' => array_slice($items, 0, 8)
                    ]
                ];
            }
        }
        
        return null;
    }

    /**
     * Parse a content section
     * @updated 2026-02-08: Extract CSS styles and map to JTB attributes
     */
    private static function parseSection(\DOMNode $node, \DOMXPath $xpath): ?array
    {
        $section = [
            'type' => 'section',
            'attrs' => [
                'padding' => ['top' => 80, 'bottom' => 80]
            ],
            'children' => []
        ];

        // Get section class for CSS extraction
        $sectionClass = '';
        if ($node instanceof \DOMElement) {
            $class = $node->getAttribute('class') ?? '';
            // Extract primary class (first one, or one that looks like a section name)
            $classes = preg_split('/\s+/', $class);
            foreach ($classes as $c) {
                // Skip generic classes
                if (in_array($c, ['section', 'container', 'wrapper', 'inner', 'content'])) continue;
                $sectionClass = $c;
                break;
            }
            // Also set _pattern from class
            if (!empty($sectionClass)) {
                $section['attrs']['_pattern'] = $sectionClass;
            }
        }

        // Extract CSS styles for this section
        if (self::$cssExtractor && !empty($sectionClass)) {
            $cssAttrs = self::$cssExtractor->mapToSectionAttrs(
                self::$cssExtractor->getRulesFor('.' . $sectionClass)
            );
            $section['attrs'] = array_merge($section['attrs'], $cssAttrs);
        }

        // Extract inline style as fallback
        $style = self::getInlineStyle($node);
        if (!empty($style['background-color']) && empty($section['attrs']['background_color'])) {
            $section['attrs']['background_type'] = 'color';
            $section['attrs']['background_color'] = $style['background-color'];
        }
        if (!empty($style['background']) && empty($section['attrs']['background_type'])) {
            // Try to parse background shorthand
            $bgAttrs = self::parseBackgroundShorthand($style['background']);
            $section['attrs'] = array_merge($section['attrs'], $bgAttrs);
        }
        if (!empty($style['min-height']) && empty($section['attrs']['min_height'])) {
            $section['attrs']['min_height'] = $style['min-height'];
        }

        // Detect layout - look for flex/grid containers
        $columns = self::detectColumns($node, $xpath);
        
        // If no columns found, try inside container/wrapper divs
        if (count($columns) < 2) {
            $containers = $xpath->query('./div[contains(@class,"container") or contains(@class,"wrapper") or contains(@class,"inner")]', $node);
            foreach ($containers as $container) {
                $columns = self::detectColumns($container, $xpath);
                if (count($columns) >= 2) {
                    break;
                }
            }
        }

        if (count($columns) > 1) {
            // Multi-column layout
            $row = [
                'type' => 'row',
                'attrs' => ['columns' => self::columnsToLayout(count($columns))],
                'children' => []
            ];

            foreach ($columns as $colNode) {
                $column = [
                    'type' => 'column',
                    'attrs' => ['width' => '1_' . count($columns)],
                    'children' => self::parseModules($colNode, $xpath)
                ];
                $row['children'][] = $column;
            }

            $section['children'][] = $row;
        } else {
            // Single column layout
            $modules = self::parseModules($node, $xpath);
            if (!empty($modules)) {
                $row = [
                    'type' => 'row',
                    'attrs' => ['columns' => '1'],
                    'children' => [
                        [
                            'type' => 'column',
                            'attrs' => ['width' => '1'],
                            'children' => $modules
                        ]
                    ]
                ];
                $section['children'][] = $row;
            }
        }

        // Only return if we found content
        if (empty($section['children'])) {
            return null;
        }

        return $section;
    }

    /**
     * Parse background shorthand to JTB attributes
     */
    private static function parseBackgroundShorthand(string $bg): array
    {
        $attrs = [];
        
        if (preg_match('/linear-gradient\s*\([^)]+\)/i', $bg, $m)) {
            $attrs['background_type'] = 'gradient';
            $attrs['background_gradient_type'] = 'linear';
            
            // Extract direction
            if (preg_match('/(\d+)deg/i', $m[0], $deg)) {
                $attrs['background_gradient_direction'] = (int)$deg[1];
            }
            
            // Extract color stops
            preg_match_all('/(#[a-f0-9]{3,8}|rgba?\([^)]+\))\s*(\d+%)?/i', $m[0], $stops, PREG_SET_ORDER);
            if (!empty($stops)) {
                $gradientStops = [];
                $count = count($stops);
                foreach ($stops as $i => $stop) {
                    $gradientStops[] = [
                        'color' => $stop[1],
                        'position' => isset($stop[2]) ? (int)$stop[2] : round($i / max(1, $count - 1) * 100)
                    ];
                }
                $attrs['background_gradient_stops'] = $gradientStops;
            }
        } elseif (preg_match('/url\(["\']?([^"\']+)["\']?\)/i', $bg, $m)) {
            $attrs['background_type'] = 'image';
            $attrs['background_image'] = $m[1];
        } elseif (preg_match('/^(#[a-f0-9]{3,8}|rgba?\([^)]+\)|[a-z]+)$/i', trim($bg))) {
            $attrs['background_type'] = 'color';
            $attrs['background_color'] = trim($bg);
        }
        
        return $attrs;
    }

    /**
     * Detect column layout in a section
     * FIXED 2026-02-07: Better detection of flex/grid layouts and column patterns
     */
    private static function detectColumns(\DOMNode $node, \DOMXPath $xpath): array
    {
        // Method 1: Look for flex/grid container (parent node style)
        $style = $node instanceof \DOMElement ? ($node->getAttribute('style') ?? '') : '';
        $class = $node instanceof \DOMElement ? ($node->getAttribute('class') ?? '') : '';
        
        $isFlexContainer = preg_match('/display\s*:\s*(flex|grid)/i', $style) 
            || preg_match('/\b(flex|grid|row|columns?)\b/i', $class);
        
        // Method 2: Find column-like children
        $columnPatterns = [
            'col', 'column', 'grid', 'cell',
            'card', 'item', 'box', 'block', 'panel',
            'feature', 'service', 'benefit', 'advantage',
            'team', 'member', 'staff', 'person',
            'pricing', 'plan', 'package', 'tier',
            'testimonial', 'review', 'quote',
            'stat', 'counter', 'metric', 'number',
            'step', 'process', 'timeline',
            'logo', 'partner', 'client', 'brand',
            'hero-content', 'hero-image', 'hero-text',
            'content', 'media', 'image', 'text', 'info',
            'left', 'right', 'primary', 'secondary'
        ];
        $patternQuery = [];
        foreach ($columnPatterns as $pattern) {
            $patternQuery[] = "contains(@class,'{$pattern}')";
        }
        
        $patternQuery[] = 'contains(@style,"flex")';
        $patternQuery[] = 'contains(@style,"width")';
        
        $query = './*[' . implode(' or ', $patternQuery) . ']';
        $children = $xpath->query($query, $node);
        
        if ($children->length >= 2 && $children->length <= 8) {
            $cols = [];
            foreach ($children as $child) {
                $cols[] = $child;
            }
            return $cols;
        }
        
        // Method 3: Check for wrapper div with columns
        $wrapperQuery = './div[contains(@class,"row") or contains(@class,"flex") or contains(@class,"grid") or contains(@class,"container") or contains(@class,"wrapper") or contains(@style,"flex") or contains(@style,"grid")]';
        $wrappers = $xpath->query($wrapperQuery, $node);
        
        if ($wrappers->length > 0) {
            foreach ($wrappers as $wrapper) {
                $wrapperCols = self::detectColumns($wrapper, $xpath);
                if (count($wrapperCols) >= 2) {
                    return $wrapperCols;
                }
            }
        }

        // Method 4: Direct children divs
        $directDivs = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'div') {
                $directDivs[] = $child;
            }
        }

        if (count($directDivs) >= 2 && count($directDivs) <= 8) {
            $hasContent = 0;
            foreach ($directDivs as $div) {
                if ($div->childNodes->length > 0) {
                    $hasContent++;
                }
            }
            if ($hasContent >= 2) {
                return $directDivs;
            }
        }
        
        // Method 5: If parent is flex/grid container
        if ($isFlexContainer) {
            $allChildren = [];
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $allChildren[] = $child;
                }
            }
            if (count($allChildren) >= 2 && count($allChildren) <= 8) {
                return $allChildren;
            }
        }

        return [];
    }

    /**
     * Parse all modules from a node
     */
    private static function parseModules(\DOMNode $node, \DOMXPath $xpath): array
    {
        $modules = [];
        $modules = array_merge($modules, self::findHeadings($node, $xpath));
        $modules = array_merge($modules, self::findTexts($node, $xpath));
        $modules = array_merge($modules, self::findImages($node, $xpath));
        $modules = array_merge($modules, self::findButtons($node, $xpath));
        $modules = array_merge($modules, self::findIcons($node, $xpath));
        return $modules;
    }

    /**
     * Find heading elements
     * @updated 2026-02-08: Extract CSS styles for headings
     */
    private static function findHeadings(\DOMNode $node, \DOMXPath $xpath): array
    {
        $modules = [];
        $headings = $xpath->query('.//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6', $node);

        foreach ($headings as $h) {
            $level = (int) substr($h->nodeName, 1);
            $text = trim($h->textContent);
            
            if (empty($text)) continue;

            $style = self::getInlineStyle($h);
            
            $module = [
                'type' => 'heading',
                'attrs' => [
                    'title' => $text,
                    'header_level' => 'h' . $level,
                    'text_align' => $style['text-align'] ?? 'left'
                ]
            ];

            // Get class for CSS extraction
            if ($h instanceof \DOMElement && self::$cssExtractor) {
                $class = $h->getAttribute('class') ?? '';
                if (!empty($class)) {
                    $classes = preg_split('/\s+/', $class);
                    foreach ($classes as $c) {
                        $cssAttrs = self::$cssExtractor->mapToModuleAttrs(
                            self::$cssExtractor->getRulesFor('.' . $c)
                        );
                        $module['attrs'] = array_merge($module['attrs'], $cssAttrs);
                    }
                }
                // Also get h1, h2, etc styles
                $cssAttrs = self::$cssExtractor->mapToModuleAttrs(
                    self::$cssExtractor->getRulesFor('h' . $level)
                );
                $module['attrs'] = array_merge($module['attrs'], $cssAttrs);
            }

            // Inline styles override
            if (!empty($style['font-size'])) {
                $module['attrs']['title_font_size'] = $style['font-size'];
            }
            if (!empty($style['color'])) {
                $module['attrs']['title_text_color'] = $style['color'];
            }

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Find text/paragraph elements
     */
    private static function findTexts(\DOMNode $node, \DOMXPath $xpath): array
    {
        $modules = [];
        $paragraphs = $xpath->query('.//p', $node);

        foreach ($paragraphs as $p) {
            $text = trim($p->textContent);
            
            if (empty($text) || strlen($text) < 10) continue;

            $style = self::getInlineStyle($p);
            
            $module = [
                'type' => 'text',
                'attrs' => [
                    'content' => '<p>' . htmlspecialchars($text) . '</p>',
                    'text_align' => $style['text-align'] ?? 'left'
                ]
            ];

            if (!empty($style['color'])) {
                $module['attrs']['text_color'] = $style['color'];
            }

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Find image elements
     */
    private static function findImages(\DOMNode $node, \DOMXPath $xpath): array
    {
        $modules = [];
        $images = $xpath->query('.//img', $node);

        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');
            
            if (empty($src)) continue;

            $module = [
                'type' => 'image',
                'attrs' => [
                    'src' => $src,
                    'alt' => $alt ?: 'Image',
                    'show_in_lightbox' => false
                ]
            ];

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Find button/link elements
     * @updated 2026-02-08: Extract CSS styles for buttons
     */
    private static function findButtons(\DOMNode $node, \DOMXPath $xpath): array
    {
        $modules = [];
        $buttons = $xpath->query('.//button | .//a[contains(@class,"btn") or contains(@class,"button") or contains(@style,"padding")]', $node);

        foreach ($buttons as $btn) {
            $text = trim($btn->textContent);
            $href = $btn->getAttribute('href') ?: '#';
            
            if (empty($text) || strlen($text) > 50) continue;

            $style = self::getInlineStyle($btn);
            
            $module = [
                'type' => 'button',
                'attrs' => [
                    'button_text' => $text,
                    'button_url' => $href,
                    'button_style' => 'solid'
                ]
            ];

            // Extract CSS styles for button
            if ($btn instanceof \DOMElement && self::$cssExtractor) {
                $class = $btn->getAttribute('class') ?? '';
                if (!empty($class)) {
                    $classes = preg_split('/\s+/', $class);
                    foreach ($classes as $c) {
                        $cssProps = self::$cssExtractor->getRulesFor('.' . $c);
                        if (!empty($cssProps['background-color']) || !empty($cssProps['background'])) {
                            $module['attrs']['button_bg_color'] = $cssProps['background-color'] ?? $cssProps['background'];
                        }
                        if (!empty($cssProps['color'])) {
                            $module['attrs']['button_text_color'] = $cssProps['color'];
                        }
                        if (!empty($cssProps['border-radius'])) {
                            $module['attrs']['button_border_radius'] = $cssProps['border-radius'];
                        }
                    }
                }
            }

            // Inline styles override
            if (!empty($style['background-color']) || !empty($style['background'])) {
                $module['attrs']['button_bg_color'] = $style['background-color'] ?? $style['background'];
            }
            if (!empty($style['color'])) {
                $module['attrs']['button_text_color'] = $style['color'];
            }

            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Find icon elements
     */
    private static function findIcons(\DOMNode $node, \DOMXPath $xpath): array
    {
        return [];
    }

    /**
     * Parse inline style attribute to array
     */
    private static function getInlineStyle(\DOMNode $node): array
    {
        if (!$node instanceof \DOMElement) {
            return [];
        }

        $styleAttr = $node->getAttribute('style');
        if (empty($styleAttr)) {
            return [];
        }

        $styles = [];
        $parts = explode(';', $styleAttr);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            $kv = explode(':', $part, 2);
            if (count($kv) === 2) {
                $key = trim($kv[0]);
                $value = trim($kv[1]);
                $styles[$key] = $value;
            }
        }

        return $styles;
    }

    /**
     * Extract color palette from CSS
     */
    private static function extractColors(string $html): array
    {
        $colors = [
            'primary' => '#6366f1',
            'secondary' => '#3b82f6',
            'text' => '#1e293b',
            'background' => '#ffffff'
        ];

        if (preg_match('/--primary[^:]*:\s*(#[a-fA-F0-9]{3,8})/i', $html, $m)) {
            $colors['primary'] = $m[1];
        }
        if (preg_match('/--secondary[^:]*:\s*(#[a-fA-F0-9]{3,8})/i', $html, $m)) {
            $colors['secondary'] = $m[1];
        }

        preg_match_all('/(?:background-color|background|color):\s*(#[a-fA-F0-9]{3,8})/i', $html, $matches);
        if (!empty($matches[1])) {
            $counts = array_count_values($matches[1]);
            arsort($counts);
            $topColors = array_keys(array_slice($counts, 0, 4));
            if (isset($topColors[0])) $colors['primary'] = $topColors[0];
            if (isset($topColors[1])) $colors['secondary'] = $topColors[1];
        }

        return $colors;
    }

    /**
     * Extract fonts from CSS
     */
    private static function extractFonts(string $html): array
    {
        $fonts = ['heading' => 'Inter', 'body' => 'Inter'];

        if (preg_match('/fonts\.googleapis\.com\/css2\?family=([^&"\']+)/i', $html, $m)) {
            $fontStr = urldecode($m[1]);
            $fontParts = explode(':', $fontStr);
            $fonts['heading'] = str_replace('+', ' ', $fontParts[0]);
            $fonts['body'] = $fonts['heading'];
        }

        return $fonts;
    }

    /**
     * Convert column count to JTB layout string
     */
    private static function columnsToLayout(int $count): string
    {
        $layouts = [
            2 => '1_2,1_2',
            3 => '1_3,1_3,1_3',
            4 => '1_4,1_4,1_4,1_4',
            5 => '1_5,1_5,1_5,1_5,1_5',
            6 => '1_6,1_6,1_6,1_6,1_6,1_6'
        ];

        return $layouts[$count] ?? '1';
    }
}
