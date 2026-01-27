<?php
/**
 * Content Parser
 * Parses HTML content and converts to JTB structure
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Content_Parser
{
    private static int $idCounter = 0;

    /**
     * Parse HTML content and return JTB structure
     *
     * @param string $html Raw HTML content
     * @param string|null $featuredImage Optional featured image URL
     * @return array JTB content structure
     */
    public static function parse(string $html, ?string $featuredImage = null): array
    {
        self::$idCounter = 0;

        // Sanitize HTML first
        $html = self::sanitizeHtml($html);

        if (empty(trim($html))) {
            return self::getEmptyContent();
        }

        // Check if this is already JTB-structured HTML
        if (self::isJtbContent($html)) {
            return self::parseJtbHtml($html);
        }

        // Parse generic HTML
        $modules = self::parseGenericHtml($html);

        // Add featured image as first module if provided
        if ($featuredImage) {
            array_unshift($modules, self::createImageModule($featuredImage, 'Featured Image'));
        }

        // Wrap modules in section > row > column structure
        return self::wrapInStructure($modules);
    }

    /**
     * Parse HTML content and return only the modules (without structure wrapper)
     * Used by article layouts system
     *
     * @param string $html Raw HTML content
     * @return array Array of JTB modules
     */
    public static function parseToModules(string $html): array
    {
        self::$idCounter = 0;

        // Sanitize HTML first
        $html = self::sanitizeHtml($html);

        if (empty(trim($html))) {
            return [];
        }

        // Check if this is already JTB-structured HTML
        if (self::isJtbContent($html)) {
            // For JTB content, return as single code module
            return [
                [
                    'type' => 'code',
                    'id' => self::generateId('code'),
                    'attrs' => [
                        'raw_content' => $html
                    ],
                    'children' => []
                ]
            ];
        }

        // Parse generic HTML to modules
        $modules = self::parseGenericHtml($html);

        // Merge consecutive text modules
        return self::mergeConsecutiveTextModules($modules);
    }

    /**
     * Parse HTML and return as single Code module
     *
     * @param string $html Raw HTML content
     * @return array JTB content structure with single code module
     */
    public static function parseAsCode(string $html): array
    {
        $html = self::sanitizeHtml($html);

        if (empty(trim($html))) {
            return self::getEmptyContent();
        }

        $codeModule = [
            'type' => 'code',
            'id' => self::generateId('code'),
            'attrs' => [
                'raw_content' => $html
            ],
            'children' => []
        ];

        return self::wrapInStructure([$codeModule]);
    }

    /**
     * Sanitize HTML - remove dangerous elements
     */
    private static function sanitizeHtml(string $html): string
    {
        // Remove script tags
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

        // Remove noscript tags
        $html = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', '', $html);

        // Remove inline event handlers
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove javascript: URLs
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html);

        // Remove style tags (we don't import CSS)
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);

        return trim($html);
    }

    /**
     * Check if HTML has JTB structure (tb-section classes)
     */
    private static function isJtbContent(string $html): bool
    {
        return strpos($html, 'tb-section') !== false ||
               strpos($html, 'jtb-section') !== false;
    }

    /**
     * Parse JTB-structured HTML (for legacy content)
     * This is a simplified version - full reconstruction would be complex
     */
    private static function parseJtbHtml(string $html): array
    {
        // For now, treat as code block - full reconstruction is complex
        return self::parseAsCode($html);
    }

    /**
     * Parse generic HTML into JTB modules
     */
    private static function parseGenericHtml(string $html): array
    {
        $modules = [];

        // Use DOMDocument for parsing
        $dom = new \DOMDocument();

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);

        // Wrap in container and add encoding
        $wrappedHtml = '<?xml encoding="UTF-8"><div>' . $html . '</div>';
        $dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        // Get the wrapper div
        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if (!$wrapper) {
            return $modules;
        }

        // Iterate through child nodes
        foreach ($wrapper->childNodes as $node) {
            $nodeModules = self::nodeToModules($node, $dom);
            $modules = array_merge($modules, $nodeModules);
        }

        // Merge consecutive text modules
        $modules = self::mergeConsecutiveTextModules($modules);

        return $modules;
    }

    /**
     * Convert DOM node to JTB module(s)
     */
    private static function nodeToModules(\DOMNode $node, \DOMDocument $dom): array
    {
        // Skip text nodes that are only whitespace
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim($node->textContent);
            if (empty($text)) {
                return [];
            }
            // Non-empty text node - wrap in paragraph
            return [self::createTextModuleFromHtml('<p>' . htmlspecialchars($text) . '</p>')];
        }

        // Skip non-element nodes
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return [];
        }

        $tagName = strtolower($node->nodeName);

        switch ($tagName) {
            // Headings
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return [self::createHeadingModule($node, $tagName)];

            // Paragraphs
            case 'p':
                $html = self::getInnerHtml($node, $dom);
                if (!empty(trim(strip_tags($html)))) {
                    return [self::createTextModuleFromHtml('<p>' . $html . '</p>')];
                }
                return [];

            // Images
            case 'img':
                $src = $node->getAttribute('src');
                if ($src) {
                    $alt = $node->getAttribute('alt') ?: '';
                    return [self::createImageModule($src, $alt)];
                }
                return [];

            // Lists
            case 'ul':
            case 'ol':
                $html = self::getOuterHtml($node, $dom);
                return [self::createTextModuleFromHtml($html)];

            // Blockquotes
            case 'blockquote':
                $html = self::getOuterHtml($node, $dom);
                return [self::createTextModuleFromHtml($html)];

            // Links - check if it looks like a button
            case 'a':
                if (self::looksLikeButton($node)) {
                    return [self::createButtonModule($node)];
                }
                // Regular link - wrap in paragraph
                $html = self::getOuterHtml($node, $dom);
                return [self::createTextModuleFromHtml('<p>' . $html . '</p>')];

            // Iframes - check for video
            case 'iframe':
                $videoModule = self::createVideoModuleFromIframe($node);
                if ($videoModule) {
                    return [$videoModule];
                }
                // Non-video iframe - use code module
                return [self::createCodeModuleFromNode($node, $dom)];

            // Horizontal rule
            case 'hr':
                return [self::createDividerModule()];

            // Containers - recurse into children
            case 'div':
            case 'section':
            case 'article':
            case 'main':
            case 'aside':
            case 'header':
            case 'footer':
            case 'figure':
                $childModules = [];
                foreach ($node->childNodes as $child) {
                    $childModules = array_merge($childModules, self::nodeToModules($child, $dom));
                }
                return $childModules;

            // Figure with image
            case 'figcaption':
                // Skip - handled by parent figure
                return [];

            // Pre/code blocks
            case 'pre':
            case 'code':
                $html = self::getOuterHtml($node, $dom);
                return [self::createCodeModuleFromHtml($html)];

            // Tables - too complex, use code
            case 'table':
                return [self::createCodeModuleFromNode($node, $dom)];

            // Forms - too complex, use code
            case 'form':
                return [self::createCodeModuleFromNode($node, $dom)];

            // Span, strong, em, etc. - inline elements, wrap in paragraph
            case 'span':
            case 'strong':
            case 'em':
            case 'b':
            case 'i':
            case 'u':
            case 'small':
            case 'mark':
                $html = self::getOuterHtml($node, $dom);
                return [self::createTextModuleFromHtml('<p>' . $html . '</p>')];

            // Break - skip
            case 'br':
                return [];

            // Default - try code module for complex elements
            default:
                $html = self::getOuterHtml($node, $dom);
                if (!empty(trim(strip_tags($html)))) {
                    return [self::createCodeModuleFromHtml($html)];
                }
                return [];
        }
    }

    /**
     * Create heading module
     */
    private static function createHeadingModule(\DOMNode $node, string $level): array
    {
        $text = trim($node->textContent);

        // Convert markdown bold/italic to plain text for headings
        // Remove ** markdown syntax (bold markers)
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);
        // Remove * markdown syntax (italic markers)
        $text = preg_replace('/\*([^*]+)\*/', '$1', $text);
        // Remove __ markdown syntax (bold markers)
        $text = preg_replace('/__([^_]+)__/', '$1', $text);
        // Remove _ markdown syntax (italic markers)
        $text = preg_replace('/_([^_]+)_/', '$1', $text);

        return [
            'type' => 'heading',
            'id' => self::generateId('heading'),
            'attrs' => [
                'text' => $text,
                'level' => $level
            ],
            'children' => []
        ];
    }

    /**
     * Create text module from HTML
     */
    private static function createTextModuleFromHtml(string $html): array
    {
        return [
            'type' => 'text',
            'id' => self::generateId('text'),
            'attrs' => [
                'content' => $html
            ],
            'children' => []
        ];
    }

    /**
     * Create image module
     */
    private static function createImageModule(string $src, string $alt = ''): array
    {
        return [
            'type' => 'image',
            'id' => self::generateId('image'),
            'attrs' => [
                'src' => $src,
                'alt' => $alt,
                'align' => 'center'
            ],
            'children' => []
        ];
    }

    /**
     * Create button module from link
     */
    private static function createButtonModule(\DOMNode $node): array
    {
        $text = trim($node->textContent);
        $href = $node->getAttribute('href') ?: '#';
        $target = $node->getAttribute('target') === '_blank';

        return [
            'type' => 'button',
            'id' => self::generateId('button'),
            'attrs' => [
                'text' => $text,
                'link_url' => $href,
                'link_target' => $target
            ],
            'children' => []
        ];
    }

    /**
     * Create video module from iframe
     */
    private static function createVideoModuleFromIframe(\DOMNode $node): ?array
    {
        $src = $node->getAttribute('src');

        if (!$src) {
            return null;
        }

        // Check for YouTube
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/i', $src, $matches) ||
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/i', $src, $matches)) {
            return [
                'type' => 'video',
                'id' => self::generateId('video'),
                'attrs' => [
                    'video_type' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=' . $matches[1]
                ],
                'children' => []
            ];
        }

        // Check for Vimeo
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/i', $src, $matches)) {
            return [
                'type' => 'video',
                'id' => self::generateId('video'),
                'attrs' => [
                    'video_type' => 'vimeo',
                    'video_url' => 'https://vimeo.com/' . $matches[1]
                ],
                'children' => []
            ];
        }

        return null;
    }

    /**
     * Create divider module
     */
    private static function createDividerModule(): array
    {
        return [
            'type' => 'divider',
            'id' => self::generateId('divider'),
            'attrs' => [],
            'children' => []
        ];
    }

    /**
     * Create code module from HTML string
     */
    private static function createCodeModuleFromHtml(string $html): array
    {
        return [
            'type' => 'code',
            'id' => self::generateId('code'),
            'attrs' => [
                'raw_content' => $html
            ],
            'children' => []
        ];
    }

    /**
     * Create code module from DOM node
     */
    private static function createCodeModuleFromNode(\DOMNode $node, \DOMDocument $dom): array
    {
        $html = self::getOuterHtml($node, $dom);
        return self::createCodeModuleFromHtml($html);
    }

    /**
     * Check if link looks like a button
     */
    private static function looksLikeButton(\DOMNode $node): bool
    {
        $class = strtolower($node->getAttribute('class') ?? '');

        // Check for button-like classes
        $buttonClasses = ['btn', 'button', 'cta', 'action'];
        foreach ($buttonClasses as $btnClass) {
            if (strpos($class, $btnClass) !== false) {
                return true;
            }
        }

        // Check inline style for button-like properties
        $style = strtolower($node->getAttribute('style') ?? '');
        if (strpos($style, 'background') !== false && strpos($style, 'padding') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Get inner HTML of a node
     */
    private static function getInnerHtml(\DOMNode $node, \DOMDocument $dom): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }
        return trim($html);
    }

    /**
     * Get outer HTML of a node
     */
    private static function getOuterHtml(\DOMNode $node, \DOMDocument $dom): string
    {
        return $dom->saveHTML($node);
    }

    /**
     * Merge consecutive text modules into one
     */
    private static function mergeConsecutiveTextModules(array $modules): array
    {
        $result = [];
        $textBuffer = '';

        foreach ($modules as $module) {
            if ($module['type'] === 'text') {
                $textBuffer .= ($module['attrs']['content'] ?? '');
            } else {
                // Flush text buffer
                if (!empty($textBuffer)) {
                    $result[] = self::createTextModuleFromHtml($textBuffer);
                    $textBuffer = '';
                }
                $result[] = $module;
            }
        }

        // Flush remaining text buffer
        if (!empty($textBuffer)) {
            $result[] = self::createTextModuleFromHtml($textBuffer);
        }

        return $result;
    }

    /**
     * Wrap modules in section > row > column structure
     */
    private static function wrapInStructure(array $modules): array
    {
        if (empty($modules)) {
            return self::getEmptyContent();
        }

        return [
            'version' => '1.0',
            'content' => [
                [
                    'type' => 'section',
                    'id' => self::generateId('section'),
                    'attrs' => [
                        'fullwidth' => false,
                        'inner_width' => 1200
                    ],
                    'children' => [
                        [
                            'type' => 'row',
                            'id' => self::generateId('row'),
                            'attrs' => [
                                'columns' => '1',
                                'column_gap' => 30,
                                'equal_heights' => true
                            ],
                            'children' => [
                                [
                                    'type' => 'column',
                                    'id' => self::generateId('column'),
                                    'attrs' => [],
                                    'children' => $modules
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get empty content structure
     */
    private static function getEmptyContent(): array
    {
        return [
            'version' => '1.0',
            'content' => []
        ];
    }

    /**
     * Generate unique ID
     */
    private static function generateId(string $prefix): string
    {
        self::$idCounter++;
        return $prefix . '_import_' . self::$idCounter . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
